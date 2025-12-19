<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: right; font-size: 10px; }
        th { background-color: #f8f9fa; text-align: center; text-transform: uppercase; }
        .date-col { text-align: left; font-weight: bold; }
        .total-row { background-color: #e9ecef; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Earnings Breakdown Report</h2>
        <p>Type: {{ ucfirst($filter) }} | Generated: {{ date('Y-m-d H:i') }}</p>
    </div>
    <table>
        <thead>
            <tr>
                <th class="date-col">Period</th>
                <th>Total Collected</th>
                @foreach($types as $type)
                    <th>{{ strtoupper($type->name) }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($data as $row)
                <tr>
                    <td class="date-col">{{ $row['date'] }}</td>
                    <td>₱{{ number_format($row['total_amount'], 2) }}</td>
                    @foreach($types as $type)
                        <td>₱{{ number_format($row[$type->name] ?? 0, 2) }}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
