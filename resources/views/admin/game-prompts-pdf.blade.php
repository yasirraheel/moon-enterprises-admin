<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Game Prompt Export - {{ $prompt->prompt }}</title>
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

        .prompt-name {
            font-size: 14px;
            color: #333;
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
            text-align: center;
            font-weight: bold;
            border: 1px solid #0056b3;
        }

        td {
            padding: 6px 4px;
            border: 1px solid #ddd;
            vertical-align: top;
            text-align: center;
        }

        tr:nth-child(even) {
            background-color: #f8f9fa;
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
        @if(isset($settings->logo))
            <img src="{{ public_path('img/'.$settings->logo) }}" class="logo" alt="Logo">
        @else
            <h2>{{ $settings->title ?? 'Moon Enterprises' }}</h2>
        @endif
        <div class="title">Game Prompt Export</div>
        <div class="tagline">Generated on {{ date('d M, Y') }}</div>
    </div>

    <div class="category-info">
        @if($category)
            <div class="category-name">Category: {{ $category->name }}</div>
        @endif
        <div class="prompt-name">Prompt: {{ $prompt->prompt }} ({{ $prompt->number_start }} - {{ $prompt->number_end }})</div>
    </div>

    <div class="export-info">
        Generated on: {{ date('F j, Y \a\t g:i A') }}
    </div>

    @php
        function parsePromptNumberPdf($str) {
            if (preg_match('/^(\D*)(\d+)(\D*)$/', $str, $matches)) {
                return [
                    'prefix' => $matches[1],
                    'number' => (int)$matches[2],
                    'suffix' => $matches[3],
                    'padding' => strlen($matches[2])
                ];
            }
            return [
                'prefix' => '',
                'number' => (int)$str,
                'suffix' => '',
                'padding' => strlen($str)
            ];
        }

        $start = parsePromptNumberPdf($prompt->number_start);
        $end = parsePromptNumberPdf($prompt->number_end);
        $counter = 1;
    @endphp

    <table>
        <thead>
            <tr>
                <th style="width: 10%;">Ser</th>
                <th style="width: 30%;">RTTP</th>
                <th style="width: 30%;">First</th>
                <th style="width: 30%;">Second</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalFirst = 0;
                $totalSecond = 0;
            @endphp
            @for ($i = $start['number']; $i <= $end['number']; $i++)
                @php
                    $rttpValue = $start['prefix'] . str_pad($i, $start['padding'], '0', STR_PAD_LEFT) . $start['suffix'];

                    // Logic from view: only show if exists
                    if (!isset($ordersData[$rttpValue])) {
                        continue;
                    }

                    $firstSum = $ordersData[$rttpValue]['first'];
                    $secondSum = $ordersData[$rttpValue]['second'];

                    $isDuplicate = in_array($rttpValue, $exportedRttps ?? []);

                    // Subtraction logic - subtract but ensure values don't go below zero
                    // Only subtract if NOT duplicate
                    if (!$isDuplicate) {
                        if ($subFirst > 0) {
                            $firstSum = max(0, $firstSum - $subFirst);
                        }
                        if ($subSecond > 0) {
                            $secondSum = max(0, $secondSum - $subSecond);
                        }
                    }

                    $totalFirst += $firstSum;
                    $totalSecond += $secondSum;
                @endphp
                <tr>
                    <td>{{ $counter++ }}</td>
                    <td>
                        {{ $rttpValue }}
                        @if($isDuplicate)
                            <span style="background-color: #ffc107; padding: 2px 4px; border-radius: 4px; font-size: 10px;">Dup</span>
                        @endif
                    </td>
                    <td>{{ $firstSum }}</td>
                    <td>{{ $secondSum }}</td>
                </tr>
            @endfor
            <tr style="background-color: #f8f9fa; font-weight: bold;">
                <td colspan="2" style="text-align: right;">Total:</td>
                <td>{{ $totalFirst }}</td>
                <td>{{ $totalSecond }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        &copy; {{ date('Y') }} {{ $settings->name_site ?? 'MOON ENTERPRISES' }}. All rights reserved.
    </div>
</body>
</html>
