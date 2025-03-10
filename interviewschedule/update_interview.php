<?php
require_once 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $scheduled_time = $_POST['scheduled_time'];
    $program_id = intval($_POST['program']);
    $interviewer_id = intval($_POST['interviewer']);

    $sql = "UPDATE interviews SET scheduled_time = ?, program_id = ?, interviewer_id = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("siii", $scheduled_time, $program_id, $interviewer_id, $id);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "Error updating interview: " . $conn->error;
    }
}
?>
