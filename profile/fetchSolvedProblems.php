<?php
// Database connection details
include '../database/db.php';

$rating = $_GET['rating'];
$username = $_GET['username'];

// Fetch data from the database
if ($rating == 'all') {
    $sql = "SELECT id, contestId, problemIndex, problemName, timeToSolve, howSolved FROM solvedProblems WHERE username = ? ORDER BY id DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
} else {
    $sql = "SELECT id, contestId, problemIndex, problemName, timeToSolve, howSolved FROM solvedProblems WHERE problemRating = ? AND username = ? ORDER BY id DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $rating, $username);
}

$stmt->execute();
$result = $stmt->get_result();

$problems = [];
while ($row = $result->fetch_assoc()) {
    $problems[] = $row;
}

$stmt->close();
$conn->close();

// Return data as JSON
header('Content-Type: application/json');
echo json_encode($problems);
?>
