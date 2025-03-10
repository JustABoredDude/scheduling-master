<?php
require_once 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $scheduled_time = $_POST['scheduled_time'];
    $program_id = intval($_POST['program']);
    $interviewer_id = intval($_POST['interviewer']);
    $current_status = $_POST['current_status'];

    $new_status = ($current_status === 'cancelled') ? 'scheduled' : $current_status;

    $sql = "UPDATE interviews SET scheduled_time = ?, program_id = ?, interviewer_id = ?, status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sissi", $scheduled_time, $program_id, $interviewer_id, $new_status, $id);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "Error updating interview: " . $conn->error;
    }
}
?>
