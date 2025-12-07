<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\DetailUser;

// Load QR library
if (!class_exists('QRcode')) {
    if (file_exists(base_path('phpqrcode/qrlib.php'))) {
        require_once base_path('phpqrcode/qrlib.php');
    } else {
        Log::error('phpqrcode library not found at: ' . base_path('phpqrcode/qrlib.php'));
    }
}

class RegistrationController extends Controller
{
    // 🔥 Keeping your path structure
    const QR_CODE_PATH = 'qr_image/';
    const SUCCESS_URL = 'http://cinco.test/payment/success';
    const FAILURE_URL = 'http://cinco.test/payment/failure';

    /**
     * Creates a PayMongo Checkout Session and returns its details.
     */
    protected function createPaymongoCheckoutSession(User $user, $amount, $email, $teamName)
    {
        // 💰 CRITICAL: PayMongo amounts must be an INTEGER in centavos.
        $formattedAmount = (int)($amount * 100);

        $teamName = $user->team_name;
        $teamId = $user->id;

        $items = [
            [
                'name' => 'Team Registration - ' . $teamName,
                'amount' => $formattedAmount,
                'currency' => 'PHP',
                'quantity' => 1,
            ]
        ];

        $payload = [
            'data' => [
                'attributes' => [
                    'billing' => [
                        'name' => $teamName,
                        'email' => $email,
                    ],
                    'send_email_receipt' => true,
                    'show_description' => true,
                    'show_line_items' => true,
                    'cancel_url' => self::FAILURE_URL . '?team_id=' . $teamId,
                    'success_url' => self::SUCCESS_URL . '?team_id=' . $teamId,
                    'line_items' => $items,
                    'payment_method_types' => ['card', 'gcash', 'paymaya', 'grab_pay'],
                    'description' => 'Team Registration Payment for ' . $teamName,
                    'statement_descriptor' => 'CINCOREG',
                    'metadata' => [
                        'team_id' => $teamId,
                    ],
                ],
            ],
        ];

        $response = Http::withBasicAuth(env('PAYMONGO_SECRET_KEY'), '')
            ->post('https://api.paymongo.com/v1/checkout_sessions', $payload);

        if ($response->failed()) {
            $errorDetails = $response->json();
            $errorMessage = 'PayMongo API Error: '
                . ($errorDetails['errors'][0]['detail'] ?? 'Unknown API error occurred.');
            throw new \Exception($errorMessage);
        }

        return $response->json()['data'];
    }

    /**
     * Handles the team registration, QR code generation, and payment session creation.
     */
    public function register(Request $request)
    {
        // VALIDATION
        $validator = Validator::make($request->all(), [
            'team.team_name' => 'required|string|max:255|unique:users,team_name',
            'team.total_payment' => 'required|numeric|min:2500',
            'team.additional_shirt_count' => 'required|integer|min:0',
            'team.region' => 'required|string',
            'team.city' => 'required|string',
            'details' => 'required|array|min:5',

            'details.*.fullName' => 'required|string|max:255',
            'details.*.email' => 'required|email|unique:detail_user,email',
            'details.*.mobileNumber' => 'required|string|max:20|unique:detail_user,mobile_number',
            'details.*.accountType' => 'required|in:Player,Shirt',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $teamData = $request->input('team');
        $detailUsers = $request->input('details');
        $captainEmail = $detailUsers[0]['email'] ?? 'unknown@example.com';

        DB::beginTransaction();

        try {
            // 1. CREATE TEAM USER
            $user = User::create([
                'team_name' => $teamData['team_name'],
                'total_payment' => $teamData['total_payment'],
                'additional_shirt_count' => $teamData['additional_shirt_count'],
                'country' => $teamData['country'] ?? 'Philippines',
                'region' => $teamData['region'],
                'province' => $teamData['province'] ?? null,
                'city' => $teamData['city'],
                'barangay' => $teamData['barangay'] ?? null,
                'postal_code' => $teamData['postal_code'] ?? null,
                'paymongo_checkout_session_id' => null,
                // 🔥 FIX: Use the correct column name 'transaction_status'
                'transaction_status' => 'pending_registration',
            ]);

            // 2. PAYMONGO CHECKOUT SESSION CREATION
            $sessionData = $this->createPaymongoCheckoutSession(
                $user,
                $teamData['total_payment'],
                $captainEmail,
                $teamData['team_name']
            );

            $checkoutUrl = $sessionData['attributes']['checkout_url'];

            // 3. UPDATE USER with session ID and status
            $user->update([
                'paymongo_checkout_session_id' => $sessionData['id'],
                // 🔥 FIX: Use the correct column name 'transaction_status'
                'transaction_status' => 'pending_payment',
            ]);

            // 4. QR CODE GENERATION AND DETAILS INSERTION (Keep existing logic)
            if (class_exists('QRcode')) {
                $lastDetail = DetailUser::orderBy('id', 'desc')->lockForUpdate()->first();
                $lastNumber = 0;

                if ($lastDetail && preg_match('/Cinco(\d+)/', $lastDetail->qrcode_img, $matches)) {
                    $lastNumber = (int)$matches[1];
                }

                // FIX: Ensure public_path() is used with your path
                $qrCodeDirectory = public_path(self::QR_CODE_PATH);
                if (!is_dir($qrCodeDirectory)) {
                    // This is where rollback often happens if folder permissions are wrong!
                    mkdir($qrCodeDirectory, 0755, true);
                }

                $detailsToInsert = [];

                foreach ($detailUsers as $detail) {
                    $lastNumber++;
                    $sequential = str_pad($lastNumber, 6, '0', STR_PAD_LEFT);

                    $qrPlain = 'Cinco' . $sequential;
                    $qrImg = $qrPlain . '.png';
                    $qrHashed = Hash::make($qrPlain);

                    $fullPath = $qrCodeDirectory . $qrImg;

                    \QRcode::png($qrHashed, $fullPath, QR_ECLEVEL_L, 10, 2);

                    $detailsToInsert[] = [
                        'user_id' => $user->id,
                        'full_name' => $detail['fullName'],
                        'email' => $detail['email'],
                        'mobile_number' => $detail['mobileNumber'],
                        'account_type' => $detail['accountType'],
                        'qrcode_name' => $qrHashed,
                        'qrcode_img' => $qrImg,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                DetailUser::insert($detailsToInsert);
            }

            // 5. COMMIT TRANSACTION
            DB::commit();

            // 6. RETURN REDIRECT URL
            return response()->json([
                'message' => 'Team registered. Redirecting to payment.',
                'user_id' => $user->id,
                'checkout_url' => $checkoutUrl
            ], 202);

        } catch (\Exception $e) {
            DB::rollback();

            Log::error("Registration Failed: " . $e->getMessage() . " on line " . $e->getLine());

            return response()->json([
                'message' => 'Team registration failed. The transaction was rolled back.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handles the PayMongo success callback to verify and update payment status.
     * This method redirects the user back to the /register page after verification.
     */
    public function handlePaymentSuccess(Request $request)
    {
        $teamId = $request->query('team_id');
        if (!$teamId) {
            return redirect('/register')->with('error', 'Payment verification failed: Missing team ID.');
        }

        // 1. Find the User
        $user = User::find($teamId);

        if (!$user) {
            return redirect('/register')->with('error', 'Payment verification failed: Team not found.');
        }

        $sessionId = $user->paymongo_checkout_session_id;

        if (!$sessionId) {
            Log::error("PayMongo Verification Error: Session ID missing for Team ID {$teamId}.");
            return redirect('/register')->with('error', 'Payment session not recorded. Please register again.');
        }

        try {
            // 2. Fetch PayMongo Session Status
            $response = Http::withBasicAuth(env('PAYMONGO_SECRET_KEY'), '')
                ->get("https://api.paymongo.com/v1/checkout_sessions/{$sessionId}?include=payment_intent");

            if ($response->failed()) {
                throw new \Exception('Failed to fetch PayMongo session details.');
            }

            $sessionData = $response->json()['data'];
            $paymentStatus = $sessionData['attributes']['payment_intent']['attributes']['status'] ?? 'pending';

            // 3. Update User Status Based on Payment Intent Status
            if ($paymentStatus === 'succeeded' || $paymentStatus === 'paid') {
                // 🔥 FIX: Use the correct column name 'transaction_status'
                $user->update(['transaction_status' => 'paid']);
                $message = 'Payment successful! Your team is officially registered.';
            } elseif ($paymentStatus === 'pending') {
                 // 🔥 FIX: Use the correct column name 'transaction_status'
                $user->update(['transaction_status' => 'pending']);
                $message = 'Payment status is still pending. We will notify you when it is confirmed.';
            } else {
                 // 🔥 FIX: Use the correct column name 'transaction_status'
                $user->update(['transaction_status' => 'failed']);
                $message = 'Payment failed or was cancelled. Please try registering again.';
            }

            // CORRECT REDIRECT: Go back to the registration page with the status message
            return redirect('/register')->with('status', $message);

        } catch (\Exception $e) {
            Log::error("PayMongo Verification Error for Team ID {$teamId}: " . $e->getMessage());
            return redirect('/register')->with('error', 'An error occurred during payment verification.');
        }
    }
}
