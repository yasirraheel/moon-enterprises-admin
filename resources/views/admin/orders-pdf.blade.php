<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Orders Export - {{ $categoryName }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 14px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 20px;
        }

        .logo {
            max-width: 150px;
            max-height: 60px;
            margin-bottom: 15px;
        }

        .title {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
            margin: 10px 0 5px 0;
        }

        .tagline {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
        }

        .category-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #007bff;
        }

        .category-name {
            font-size: 16px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 5px;
        }

        .total-orders {
            font-size: 12px;
            color: #666;
        }

        .export-info {
            font-size: 9px;
            color: #999;
            text-align: right;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 12px;
        }

        th {
            background-color: #007bff;
            color: white;
            padding: 8px 4px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #0056b3;
        }

        td {
            padding: 6px 4px;
            border: 1px solid #ddd;
            vertical-align: top;
        }

        tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        tr:hover {
            background-color: #e9ecef;
        }

        .status-badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-pending {
            background-color: #ffc107;
            color: #000;
        }

        .status-approved {
            background-color: #28a745;
            color: white;
        }

        .status-ok {
            background-color: #28a745;
            color: white;
        }

        .status-rejected {
            background-color: #dc3545;
            color: white;
        }

        .status-win {
            background-color: #007bff;
            color: white;
        }

        .status-first_win {
            background-color: #007bff;
            color: white;
        }

        .status-second_win {
            background-color: #007bff;
            color: white;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .page-break {
            page-break-before: always;
        }

        .footer {
            position: fixed;
            bottom: 20px;
            left: 20px;
            right: 20px;
            text-align: center;
            font-size: 8px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        @if($settings->logo_light)
            <img src="{{ public_path('img/' . $settings->logo_light) }}" alt="Logo" class="logo">
        @endif
        <div class="title">{{ $settings->name_site ?? 'MOON ENTERPRISES' }}</div>
        <div class="tagline">{{ $settings->tagline ?? 'Prize Bond Booking System' }}</div>
    </div>

    <div class="category-info">
        <div class="category-name">Game Category: {{ $categoryName }}</div>
        <div class="total-orders">Total Orders: {{ $totalOrders }}</div>
    </div>

    <div class="export-info">
        Generated on: {{ date('F j, Y \a\t g:i A') }}
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 15%;">Username</th>
                <th style="width: 18%;">Game Name</th>
                <th style="width: 15%;">Bond Name</th>
                <th style="width: 10%;">RTTP</th>
                <th style="width: 8%;">First</th>
                <th style="width: 8%;">Second</th>
                <th style="width: 10%;">Total</th>
                <th style="width: 8%;">Status</th>
                <th style="width: 6%;">N/P</th>
                <th style="width: 14%;">Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $order)
                <tr>
                    <td>{{ $order->username }}</td>
                    <td>{{ $order->game_name }}</td>
                    <td>{{ $order->bond_name }}</td>
                    <td class="text-center" style="font-size: 18px;">{{ $order->rttp }}</td>
                    <td class="text-right" style="font-size: 18px;">{{ $order->first }}</td>
                    <td class="text-right" style="font-size: 18px;">{{ $order->second }}</td>
                    <td class="text-right" style="font-weight: bold; font-size: 18px;">{{ number_format((float)$order->first + (float)$order->second, 2) }}</td>
                    <td class="text-center">
                        @php
                            $status = $order->status;
                            $statusLabel = $status;
                            if($status == 'first_win') $statusLabel = 'First Win';
                            elseif($status == 'second_win') $statusLabel = 'Second Win';
                            elseif($status == 'rejected') $statusLabel = 'Rejected';
                        @endphp
                        <span class="status-badge status-{{ strtolower($order->status) }}">
                            {{ $statusLabel }}
                        </span>
                    </td>
                    <td class="text-center">
                        @if($order->n_p)
                            <span style="background-color: {{ $order->n_p == 'P' ? '#007bff' : '#6c757d' }}; color: white; padding: 2px 6px; border-radius: 3px; font-size: 8px; font-weight: bold;">
                                {{ $order->n_p }}
                            </span>
                        @else
                            <span style="color: #999;">-</span>
                        @endif
                    </td>
                    <td class="text-center">{{ \Carbon\Carbon::parse($order->created_at)->format('M j, Y') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" class="text-center" style="padding: 20px; color: #666;">
                        No orders found for this category.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>{{ $settings->name_site ?? 'MOON ENTERPRISES' }} - Orders Export | Page <span class="pagenum"></span></p>
    </div>

    <script type="text/php">
        if (isset($pdf)) {
            $text = "Page {PAGE_NUM} of {PAGE_COUNT}";
            $size = 8;
            $font = $fontMetrics->getFont("helvetica");
            $width = $fontMetrics->getTextWidth($text, $font, $size) / 2;
            $x = ($pdf->get_width() - $width) / 2;
            $y = $pdf->get_height() - 30;
            $pdf->text($x, $y, $text, $font, $size);
        }
    </script>
</body>
</html>
