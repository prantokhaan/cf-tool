<?php
header('Content-Type: application/json');

$handle = $_GET['handle'];
$url = "https://codeforces.com/api/user.status?handle={$handle}";

$response = file_get_contents($url);
$data = json_decode($response, true);

if ($data['status'] === 'OK') {
    $solvedProblems = [];

    foreach ($data['result'] as $submission) {
        if ($submission['verdict'] === 'OK') {
            $problem = $submission['problem'];
            if (isset($problem['index'])) {
                $index = $problem['index'];
                if (!isset($solvedProblems[$index])) {
                    $solvedProblems[$index] = 0;
                }
                $solvedProblems[$index]++;
            }
        }
    }

    ksort($solvedProblems);
    echo json_encode($solvedProblems);
} else {
    echo json_encode([]);
}
