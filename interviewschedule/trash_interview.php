<?php
session_start();
require_once 'db.php';

header('Content-Type: application/json'); // Changed the file type into a JSON file

$response = ['success' => false];

if (isset($_GET['id'])) {
    $interviewId = intval($_GET['id']);

    $stmt = $conn->prepare("UPDATE interviews SET status = 'trash' WHERE id = ?");
    $stmt->bind_param("i", $interviewId);

    if ($stmt->execute()) {
        $response['success'] = true;
    } else {
        $response['error'] = "Failed to update interview status.";
    }

    $stmt->close();
} else {
    $response['error'] = "Invalid interview ID.";
}

echo json_encode($response);
?>
