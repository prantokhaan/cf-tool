<?php
include '../database/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $timeToSolve = $_POST['timeToSolve'];
    $howSolved = $_POST['howSolved'];

    $stmt = $conn->prepare("UPDATE solvedProblems SET timeToSolve = ?, howSolved = ? WHERE id = ?");
    $stmt->bind_param('ssi', $timeToSolve, $howSolved, $id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update problem']);
    }

    $stmt->close();
    $conn->close();
} else {
    $id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM solvedProblems WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $problem = $result->fetch_assoc();

    echo json_encode($problem);

    $stmt->close();
    $conn->close();
}
?>
