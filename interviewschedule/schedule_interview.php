<?php
session_start();
require_once 'db.php';
require_once 'functions.php';

if (!isset($_GET['interviewer_id']) || !isset($_GET['applicant_id'])) {
    header("Location: add_interview.php");
    exit();
}

$interviewer_id = intval($_GET['interviewer_id']);
$applicant_id = intval($_GET['applicant_id']);

// Get interviewer and applicant details
$stmt = $conn->prepare("SELECT i.name as interviewer_name, i.program_id, p.name as program_name 
                        FROM interviewers i 
                        JOIN programs p ON i.program_id = p.id
                        WHERE i.id = ?");
$stmt->bind_param("i", $interviewer_id);
$stmt->execute();
$interviewer = $stmt->get_result()->fetch_assoc();

$stmt = $conn->prepare("SELECT name as applicant_name FROM applicants WHERE id = ?");
$stmt->bind_param("i", $applicant_id);
$stmt->execute();
$applicant = $stmt->get_result()->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $scheduled_time = $_POST['scheduled_time'];
    
    // Validate scheduled time
    if (DateTime::createFromFormat('Y-m-d\TH:i', $scheduled_time) === false) {
        $error = "Invalid date and time format.";
    } else {
        $program_id = $interviewer['program_id']; // Use the interviewer's program automatically
        
        // Insert new interview
        $stmt = $conn->prepare("INSERT INTO interviews (applicant_id, interviewer_id, program_id, scheduled_time, status) 
                               VALUES (?, ?, ?, ?, 'scheduled')");
        $stmt->bind_param("iiis", $applicant_id, $interviewer_id, $program_id, $scheduled_time);
        
        if ($stmt->execute()) {
            $interview_id = $conn->insert_id;
            header("Location: view_interview.php?id=" . $interview_id);
            exit();
        } else {
            $error = "Error creating interview: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Interview</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white shadow-md rounded-lg p-6 w-96 border border-gray-200">
        <div class="bg-green-100 text-center py-2 rounded-t-lg">
            <h2 class="text-lg font-semibold text-black">Schedule Interview</h2>
        </div>

        <?php if(isset($error)): ?>
            <div class="bg-red-100 text-red-700 p-2 my-2 rounded">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" class="space-y-4 mt-4">
            <div>
                <label for="scheduled_time" class="block text-sm font-medium text-gray-700">Date & Time</label>
                <input type="datetime-local" id="scheduled_time" name="scheduled_time" required class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 focus:outline-none focus:ring focus:ring-green-300">
            </div>

            <div class="flex justify-between mt-4">
                <a href="index.php" class="px-4 py-2 bg-red-100 text-red-700 rounded-md hover:bg-red-200 font-semibold">Cancel</a>
                <button type="submit" class="px-4 py-2 bg-green-700 text-white rounded-md hover:bg-green-800 font-semibold">Confirm</button>
            </div>
        </form>
    </div>
</body>
</html>
