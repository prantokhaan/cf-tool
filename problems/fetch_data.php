<?php
header('Content-Type: application/json');

function fetchContests() {
    $url = 'https://codeforces.com/api/contest.list?gym=false';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $json = curl_exec($ch);
    if(curl_errno($ch)) {
        error_log("Curl error fetching contests data: " . curl_error($ch));
        die(json_encode(['error' => 'Error fetching contests data.']));
    }
    curl_close($ch);
    $data = json_decode($json, true);
    if ($data['status'] !== 'OK') {
        error_log("Error in contests data: " . $data['comment']);
        die(json_encode(['error' => 'Error in contests data: ' . $data['comment']]));
    }
    return $data['result'];
}

function fetchProblems() {
    $url = 'https://codeforces.com/api/problemset.problems';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $json = curl_exec($ch);
    if(curl_errno($ch)) {
        error_log("Curl error fetching problems data: " . curl_error($ch));
        die(json_encode(['error' => 'Error fetching problems data.']));
    }
    curl_close($ch);
    $data = json_decode($json, true);
    if ($data['status'] !== 'OK') {
        error_log("Error in problems data: " . $data['comment']);
        die(json_encode(['error' => 'Error in problems data: ' . $data['comment']]));
    }
    return $data['result']['problems'];
}

$contests = fetchContests();
$problems = fetchProblems();

$current_time = time();

$filtered_contests = array_filter($contests, function($contest) use ($current_time) {
    return $contest['phase'] != 'BEFORE';
});

$contest_problems = [];

foreach ($filtered_contests as $contest) {
    $contest_problems[$contest['id']] = [
        'contest' => $contest,
        'problems' => []
    ];
}

foreach ($problems as $problem) {
    if (isset($contest_problems[$problem['contestId']])) {
        $contest_problems[$problem['contestId']]['problems'][] = $problem;
    }
}

echo json_encode(array_values($contest_problems));
?>
