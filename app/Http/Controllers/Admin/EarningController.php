<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PercentageBreakdowns;
use App\Models\User;
use App\Models\PercentageType; // Added for clarity
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request; // <--- THIS WAS MISSING

class EarningController extends Controller
{
    public function index(Request $request)
    {
        // Get the filter type from the request (default to 'daily')
        $filter = $request->query('filter', 'daily');

        // 1. Total Collected
        $totalCollected = User::where('transaction_status', 'paid')->sum('total_payment');

        // 2. Get all distinct percentage types for the table headers
        $percentageTypes = PercentageType::all();

        // 3. Determine the date grouping based on filter
        // SQL DATE_FORMAT: %u is week, %Y-%m is month, %Y-%m-%d is daily
        $dateGroup = match($filter) {
            'weekly'  => DB::raw("DATE_FORMAT(percentage_breakdowns.created_at, 'Week %u - %Y') as date"),
            'monthly' => DB::raw("DATE_FORMAT(percentage_breakdowns.created_at, '%M %Y') as date"),
            default   => DB::raw("DATE(percentage_breakdowns.created_at) as date"),
        };

        // 4. Fetch data grouped by the chosen period and Type
        $rawBreakdown = PercentageBreakdowns::whereHas('user', function($q) {
                $q->where('transaction_status', 'paid');
            })
            ->join('percentage_types', 'percentage_breakdowns.percentage_type_id', '=', 'percentage_types.id')
            ->select(
                $dateGroup,
                'percentage_types.name as type_name',
                DB::raw('SUM(total_earning) as total')
            )
            ->groupBy('date', 'type_name')
            ->orderBy('date', 'desc')
            ->get();

        // 5. Pivot the data: Group by date string so one row = one period
        $tableData = $rawBreakdown->groupBy('date')->map(function ($items, $date) {
            $row = ['date' => $date];
            $rowTotal = 0;
            foreach ($items as $item) {
                $row[$item->type_name] = (float)$item->total;
                $rowTotal += (float)$item->total;
            }
            $row['total_amount'] = $rowTotal;
            return $row;
        })->values();

        return Inertia::render('dashboard/Earning', [
            'totalCollected' => (float)$totalCollected,
            'percentageTypes' => $percentageTypes,
            'tableData' => $tableData,
            'currentFilter' => $filter
        ]);
    }
}
