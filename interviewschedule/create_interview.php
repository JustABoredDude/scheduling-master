<?php
// create_interview.php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $interviewer_id = $_POST['interviewer'];
    $applicant_id = $_POST['applicant'];
    $scheduled_time = $_POST['scheduled_time'];

    $sql = "INSERT INTO interviews (interviewer_id, applicant_id, scheduled_time) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $interviewer_id, $applicant_id, $scheduled_time);

    if ($stmt->execute()) {
        // Redirect to index.php after successful insertion
        header("Location: index.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}
?>