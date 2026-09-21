<?php

function parsePromptNumber($str)
{
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

function check($promptName, $startStr, $endStr, $input) {
    echo "Checking Prompt: '$promptName', Start: '$startStr', End: '$endStr', Input: '$input'\n";
    
    // Logic from controller
    $cleanStart = trim(str_ireplace($promptName, '', $startStr));
    $cleanEnd = trim(str_ireplace($promptName, '', $endStr));
    
    echo "  Clean Start: '$cleanStart', Clean End: '$cleanEnd'\n";

    $start = parsePromptNumber($cleanStart);
    $end = parsePromptNumber($cleanEnd);
    
    echo "  Parsed Start: Prefix='{$start['prefix']}', Num={$start['number']}, Suffix='{$start['suffix']}', Pad={$start['padding']}\n";
    echo "  Parsed End:   Num={$end['number']}\n";
    
    $prefix = preg_quote($start['prefix'], '/');
    $suffix = preg_quote($start['suffix'], '/');
    
    $regex = '/^' . $prefix . '(.*)' . $suffix . '$/';
    echo "  Regex: $regex\n";
    
    if (preg_match($regex, $input, $matches)) {
        $numberPart = $matches[1];
        echo "  Matches Regex. Number Part: '$numberPart'\n";
        
        if (is_numeric($numberPart)) {
            $number = (int)$numberPart;
            echo "  Numeric Value: $number\n";
            
            if ($number >= $start['number'] && $number <= $end['number']) {
                $expectedRttp = $start['prefix'] . str_pad($number, $start['padding'], '0', STR_PAD_LEFT) . $start['suffix'];
                echo "  Expected: '$expectedRttp'\n";
                
                if ($input === $expectedRttp) {
                    echo "  RESULT: VALID\n";
                    return true;
                } else {
                    echo "  RESULT: INVALID (Format Mismatch)\n";
                }
            } else {
                echo "  RESULT: INVALID (Out of Range)\n";
            }
        } else {
            echo "  RESULT: INVALID (Not Numeric)\n";
        }
    } else {
        echo "  RESULT: INVALID (Regex Mismatch)\n";
    }
    echo "--------------------------------------------------\n";
    return false;
}

// Scenarios
check("Open", "Open(0)", "Open(9)", "0");      // Expect Fail (User wants "(0)"?) Or does user input "0"? 
                                               // User said "open(0) its mean 0". If input is "0", regex `\(\d\)` fails.
check("Open", "Open(0)", "Open(9)", "(0)");    // Expect Pass

check("0", "0", "9", "0");                     // The "0 pattern"
check("0", "0", "9", "5");                     // The "0 pattern" input 5

check("Single", "0", "9", "0");
check("Single", "0", "9", "5");

// Edge case: Prompt name matches start
check("Start", "Start", "Start", "0"); // Invalid setup but possible

