<?php
require_once 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $interviewer_name = $_POST['interviewer_name'];
    $interviewer_email = $_POST['interviewer_email'];
    $applicant_name = $_POST['applicant_name'];
    $applicant_email = $_POST['applicant_email'];

    // Insert interviewer
    $stmt = $conn->prepare("INSERT INTO interviewers (name, email) VALUES (?, ?)");
    $stmt->bind_param("ss", $interviewer_name, $interviewer_email);
    $stmt->execute();

    // Insert applicant
    $stmt = $conn->prepare("INSERT INTO applicants (name, email) VALUES (?, ?)");
    $stmt->bind_param("ss", $applicant_name, $applicant_email);
    $stmt->execute();

    header("Location: index.php");
}
?>