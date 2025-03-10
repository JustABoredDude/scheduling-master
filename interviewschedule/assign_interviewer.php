<?php
require_once 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $interviewer_id = $_POST['interviewer_id'];
    $applicant_id = $_POST['applicant_id'];
    $scheduled_time = $_POST['scheduled_time'];

    $stmt = $conn->prepare("INSERT INTO interviews (interviewer_id, applicant_id, scheduled_time) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $interviewer_id, $applicant_id, $scheduled_time);
    $stmt->execute();

    header("Location: index.php");
}
?>