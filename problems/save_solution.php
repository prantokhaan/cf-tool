<?php
session_start();

include '../database/db.php';

$username = $_POST['username']; 
$contestId = $_POST['contestId'];
$index = $_POST['index'];

// Check if the record already exists
$query_check = "SELECT COUNT(*) as count FROM solvedProblems WHERE username = ? AND contestId = ? AND problemIndex = ?";
$stmt_check = $conn->prepare($query_check);
$stmt_check->bind_param('sis', $username, $contestId, $index);
$stmt_check->execute();
$result_check = $stmt_check->get_result();
$row_check = $result_check->fetch_assoc();

if ($row_check['count'] > 0) {
    // Record already exists
    echo "
        <script>
            alert('Record already exists');
            window.location.href = './allProblems.php';
        </script>
    ";
} else {
    // Proceed to insert the record
    $name = $_POST['name'];
    $rating = $_POST['rating'];
    $timeToSolve = $_POST['timeToSolve'];
    $solveMethod = $_POST['solveMethod'];
    $submissionId = $_POST['submissionId'];
    $language = $_POST['language'];
    $problemTags = $_POST['problemTags'];

    $query_insert = "INSERT INTO solvedProblems (username, contestId, problemIndex, problemName, problemRating, submissionId, timeToSolve, language, problemTags, howSolved) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt_insert = $conn->prepare($query_insert);
    $stmt_insert->bind_param('sisssiisss', $username, $contestId, $index, $name, $rating, $submissionId, $timeToSolve, $language, $problemTags, $solveMethod);

    if ($stmt_insert->execute()) {
        echo "
            <script>
                alert('Record added successfully');
                window.location.href = './allProblems.php';
            </script>
        ";
    } else {
        echo "Error: " . $stmt_insert->error;
    }

    $stmt_insert->close();
}

$stmt_check->close();
$conn->close();

exit();
?>
