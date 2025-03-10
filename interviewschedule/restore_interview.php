<?php
require_once 'db.php';

$response = ['success' => false];

if (isset($_GET['id'])) {
    $interviewId = intval($_GET['id']);
    
    $stmt = $conn->prepare("UPDATE interviews SET status = 'scheduled' WHERE id = ?");
    $stmt->bind_param("i", $interviewId);

    if ($stmt->execute()) {
        $response['success'] = true;
    } else {
        $response['error'] = "Failed to restore interview.";
    }

    $stmt->close();
}

echo json_encode($response);
?>
