<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Winner Orders Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .header h1 {
            color: #333;
            margin: 0;
            font-size: 24px;
        }
        .header h2 {
            color: #666;
            margin: 10px 0 0 0;
            font-size: 18px;
        }
        .winner-info {
            background-color: #f8f9fa;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            border-left: 4px solid #28a745;
        }
        .winner-info h3 {
            margin: 0 0 10px 0;
            color: #28a745;
            font-size: 16px;
        }
        .winner-stats {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .stat-box {
            background-color: #e9ecef;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
            flex: 1;
            margin: 0 5px;
        }
        .stat-box h4 {
            margin: 0 0 5px 0;
            color: #495057;
            font-size: 14px;
        }
        .stat-box .value {
            font-size: 18px;
            font-weight: bold;
            color: #28a745;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #495057;
        }
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $settings->title ?? 'Prize Bond Booking System' }}</h1>
        <h2>Winner Orders Report</h2>
    </div>

    <div class="winner-info">
        <h3>Winner Information</h3>
        <table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
            <tr>
                <td style="text-align: center; padding: 10px; border: 1px solid #ddd; width: 33%;">
                    <strong>Game Category</strong><br>
                    <span style="font-size: 18px; color: #28a745; font-weight: bold;">{{ $categoryName }}</span>
                </td>
                <td style="text-align: center; padding: 10px; border: 1px solid #ddd; width: 33%;">
                    <strong>Winning Number</strong><br>
                    <span style="font-size: 18px; color: #28a745; font-weight: bold;">{{ $winningNumber }}</span>
                </td>
                <td style="text-align: center; padding: 10px; border: 1px solid #ddd; width: 34%;">
                    <strong>Total Winners</strong><br>
                    <span style="font-size: 18px; color: #28a745; font-weight: bold;">{{ $totalWinners }}</span>
                </td>
            </tr>
        </table>
    </div>

    @if($orders->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Phone</th>
                    <th>Game Name</th>
                    <th>Bond Name</th>
                    <th>RTTP</th>
                    <th>First</th>
                    <th>Second</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($orders as $order)
                    <tr>
                        <td>{{ $order->id }}</td>
                        <td>{{ $order->username }}</td>
                        <td>{{ $order->user_phone }}</td>
                        <td>{{ $order->game_name }}</td>
                        <td>{{ $order->bond_name }}</td>
                        <td><strong>{{ $order->rttp }}</strong></td>
                        <td>{{ $order->first }}</td>
                        <td>{{ $order->second }}</td>
                        <td><strong>{{ number_format((float)$order->first + (float)$order->second, 2) }}</strong></td>
                        <td>
                            @if($order->status == 'pending')
                                <span style="color: #ffc107;">Pending</span>
                            @elseif($order->status == 'approved')
                                <span style="color: #28a745;">Approved</span>
                            @elseif($order->status == 'OK')
                                <span style="color: #28a745;">OK</span>
                            @elseif($order->status == 'first_win')
                                <span style="color: #007bff; font-weight: bold;">First Win</span>
                            @elseif($order->status == 'second_win')
                                <span style="color: #007bff; font-weight: bold;">Second Win</span>
                            @elseif($order->status == 'rejected')
                                <span style="color: #dc3545;">Rejected</span>
                            @else
                                <span style="color: #666;">{{ $order->status }}</span>
                            @endif
                        </td>
                        <td>{{ date('Y-m-d H:i', strtotime($order->created_at)) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div style="text-align: center; padding: 40px; color: #666;">
            <h3>No Winners Found</h3>
            <p>No orders found for winning number: <strong>{{ $winningNumber }}</strong></p>
        </div>
    @endif

    <div class="footer">
        <p>Generated on {{ date('Y-m-d H:i:s') }} | {{ $settings->title ?? 'Prize Bond Booking System' }}</p>
    </div>
</body>
</html>
