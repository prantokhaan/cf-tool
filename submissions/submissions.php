<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Codeforces Submissions</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .ok {
            background-color: #d4edda;
        }
        .not-ok {
            background-color: #f8d7da;
        }
    </style>
</head>
<body>
    <h1>Codeforces Submissions for User "vanishedd"</h1>
    <table>
        <thead>
            <tr>
                <th>Submission ID</th>
                <th>Problem</th>
                <th>Language</th>
                <th>Time</th>
                <th>Verdict</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $handle = "vanishedd";
            $apiUrl = "https://codeforces.com/api/user.status?handle=" . urlencode($handle);
            $response = file_get_contents($apiUrl);
            $data = json_decode($response, true);

            if ($data['status'] == 'OK') {
                $submissions = $data['result'];
                foreach ($submissions as $submission) {
                    $verdict = isset($submission['verdict']) ? $submission['verdict'] : 'UNKNOWN';
                    $class = $verdict == 'OK' ? 'ok' : 'not-ok';
                    $problem = $submission['problem'];
                    $problemName = htmlspecialchars($problem['name']);
                    $problemUrl = "https://codeforces.com/contest/" . htmlspecialchars($problem['contestId']) . "/problem/" . htmlspecialchars($problem['index']);
                    $submissionTime = date('Y-m-d H:i:s', $submission['creationTimeSeconds']);
                    
                    echo "<tr class='$class'>";
                    echo "<td>" . htmlspecialchars($submission['id']) . "</td>";
                    echo "<td><a href='$problemUrl' target='_blank'>" . $problemName . "</a></td>";
                    echo "<td>" . htmlspecialchars($submission['programmingLanguage']) . "</td>";
                    echo "<td>" . $submissionTime . "</td>";
                    echo "<td>" . htmlspecialchars($verdict) . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='5'>Failed to fetch submissions from Codeforces API.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</body>
</html>
