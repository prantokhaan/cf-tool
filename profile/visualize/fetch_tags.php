<?php
header('Content-Type: application/json');

$handle = $_GET['handle'];
$url = "https://codeforces.com/api/user.status?handle={$handle}";

$response = file_get_contents($url);
$data = json_decode($response, true);

if ($data['status'] === 'OK') {
    $tagCounts = [];

    foreach ($data['result'] as $submission) {
        if ($submission['verdict'] === 'OK') {
            $problem = $submission['problem'];
            if (isset($problem['tags'])) {
                foreach ($problem['tags'] as $tag) {
                    if (!isset($tagCounts[$tag])) {
                        $tagCounts[$tag] = 0;
                    }
                    $tagCounts[$tag]++;
                }
            }
        }
    }

    arsort($tagCounts); // Sort tags by count in descending order
    echo json_encode($tagCounts);
} else {
    echo json_encode([]);
}
?>
