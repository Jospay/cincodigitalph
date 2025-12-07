<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User; // <-- CHANGED: Import the User model

class PaymentController extends Controller
{
    public function success(Request $request)
    {
        $sessionId = $request->query('id');

        if (!$sessionId) {
            return redirect('/register')->with('error', 'Payment session ID missing.');
        }

        // --- Core Logic: Locate the registration using the session ID in the 'users' table ---
        // CHANGED: Querying the User model instead of Registration
        $user = User::where('paymongo_checkout_session_id', $sessionId)
            ->select('team_name') // Select the team_name column
            ->first();

        // Check if a user/registration was found
        if (!$user) {
            return redirect('/register')->with('error', 'Registration not found for this session ID.');
        }

        // Pass the session ID and the team name to the Vue component
        return Inertia::render('auth/payment/Success', [
            'sessionId' => $sessionId,
            'teamName' => $user->team_name, // CHANGED: Access team_name via the $user object
        ]);
    }
}
