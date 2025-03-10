<?php
session_start();
require_once 'db.php';

if (isset($_GET['id'])) {
    $interview_id = $_GET['id'];

    $stmt = $conn->prepare("UPDATE interviews SET status = 'cancelled' WHERE id = ?");
    $stmt->bind_param("i", $interview_id);
    if ($stmt->execute()) {
        $_SESSION['message'] = "Interview cancelled successfully!";
    } else {
        $_SESSION['error'] = "Failed to cancel the interview.";
    }

    header("Location: index.php");
    exit();
}
?>
