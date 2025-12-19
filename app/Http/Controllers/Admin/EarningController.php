<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PercentageBreakdowns;
use App\Models\User;
use App\Models\PercentageType;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class EarningController extends Controller
{
    // Reusable logic to pivot data for both Table and Export
    private function getPivotedData($filter)
    {
        $dateGroup = match($filter) {
            'weekly'  => DB::raw("DATE_FORMAT(percentage_breakdowns.created_at, 'Week %u - %Y') as date"),
            'monthly' => DB::raw("DATE_FORMAT(percentage_breakdowns.created_at, '%M %Y') as date"),
            default   => DB::raw("DATE(percentage_breakdowns.created_at) as date"),
        };

        $rawBreakdown = PercentageBreakdowns::whereHas('user', fn($q) => $q->where('transaction_status', 'paid'))
            ->join('percentage_types', 'percentage_breakdowns.percentage_type_id', '=', 'percentage_types.id')
            ->select($dateGroup, 'percentage_types.name as type_name', DB::raw('SUM(total_earning) as total'))
            ->groupBy('date', 'type_name')
            ->orderBy('date', 'desc')
            ->get();

        return $rawBreakdown->groupBy('date')->map(function ($items, $date) {
            $row = ['date' => $date];
            $rowTotal = 0;
            foreach ($items as $item) {
                $row[$item->type_name] = (float)$item->total;
                $rowTotal += (float)$item->total;
            }
            $row['total_amount'] = $rowTotal;
            return $row;
        })->values();
    }

    public function index(Request $request)
    {
        $filter = $request->query('filter', 'daily');
        $totalCollected = User::where('transaction_status', 'paid')->sum('total_payment');
        $percentageTypes = PercentageType::all();
        $tableData = $this->getPivotedData($filter);

        return Inertia::render('dashboard/Earning', [
            'totalCollected' => (float)$totalCollected,
            'percentageTypes' => $percentageTypes,
            'tableData' => $tableData,
            'currentFilter' => $filter
        ]);
    }

    public function export(Request $request, $format)
    {
        $filter = $request->query('filter', 'daily');
        $data = $this->getPivotedData($filter);
        $types = PercentageType::all();

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('exports.earnings_pdf', compact('data', 'types', 'filter'));
            return $pdf->download("earnings_{$filter}_report.pdf");
        }

        // Anonymous class for Excel/CSV Export
        return Excel::download(new class($data, $types) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
            public function __construct($data, $types) { $this->data = $data; $this->types = $types; }
            public function collection() { return $this->data; }
            public function headings(): array {
                return array_merge(['Period', 'Total'], $this->types->pluck('name')->toArray());
            }
        }, "earnings_{$filter}.{$format}");
    }
}
