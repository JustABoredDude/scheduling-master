<?php
session_start();
require_once 'db.php';
require_once 'functions.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$interview_id = $_GET['id'];

// Fetch interview details
$sql = "SELECT i.id, i.interviewer_id, i.applicant_id, i.scheduled_time, 
               i.status, ir.name AS interviewer_name, a.name AS applicant_name, 
               i.program_id, p.name AS program_name
        FROM interviews i
        JOIN interviewers ir ON i.interviewer_id = ir.id
        JOIN applicants a ON i.applicant_id = a.id
        JOIN programs p ON i.program_id = p.id
        WHERE i.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $interview_id);
$stmt->execute();
$result = $stmt->get_result();
$interview = $result->fetch_assoc();

if (!$interview) {
    header("Location: index.php");
    exit();
}

$interviewers = getInterviewers($conn);
$programs = getPrograms($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Interview</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <!-- Edit Interview Card -->
    <div class="bg-white w-96 p-6 rounded-lg shadow-md">
        <!-- Header -->
        <div class="bg-green-100 p-3 rounded-md text-center">
            <h1 class="text-lg font-bold">Edit Interview</h1>
        </div>

        <!-- Edit Form -->
        <form id="editForm" action="update_interview.php" method="POST" class="mt-4">
            <input type="hidden" name="id" value="<?= $interview['id'] ?>">

            <!-- Applicant Name -->
            <div>
                <label class="block font-semibold text-gray-700">Applicant Name:</label>
                <input type="text" value="<?= htmlspecialchars($interview['applicant_name']); ?>" class="w-full p-2 border rounded-md bg-gray-50" readonly>
            </div>

            <!-- Date & Time -->
            <div class="mt-3">
                <label class="block font-semibold text-gray-700">Date & Time:</label>
                <input type="datetime-local" name="scheduled_time" value="<?= date('Y-m-d\TH:i', strtotime($interview['scheduled_time'])) ?>" class="w-full p-2 border rounded-md" required>
            </div>

            <!-- Program -->
            <div class="mt-3">
                <label class="block font-semibold text-gray-700">Program:</label>
                <select name="program" class="w-full p-2 border rounded-md bg-white" required>
                    <option value="" hidden>Select a Program</option>
                    <?php foreach ($programs as $program): ?>
                        <option value="<?= $program['id'] ?>" <?= ($program['id'] == $interview['program_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($program['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Interviewer -->
            <div class="mt-3">
                <label class="block font-semibold text-gray-700">Interviewer:</label>
                <select name="interviewer" class="w-full p-2 border rounded-md bg-white" required>
                    <?php foreach ($interviewers as $interviewer): ?>
                        <option value="<?= $interviewer['id'] ?>" <?= ($interviewer['id'] == $interview['interviewer_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($interviewer['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Buttons -->
            <div class="flex justify-between mt-6">
                <a href="index.php" class="bg-red-200 text-red-800 px-4 py-2 rounded-md font-semibold hover:bg-red-300">Cancel</a>
                <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded-md font-semibold hover:bg-green-600">Confirm</button>
            </div>
        </form>
    </div>

    <script>
        document.querySelector("form").addEventListener("submit", function(event) {
            event.preventDefault(); // Prevent default form submission

            const formData = new FormData(this);

            fetch("update_interview.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                if (data.trim() === "success") {
                    alert("Interview updated successfully!");
                    window.location.href = "index.php"; // Redirect to index.php
                } else {
                    alert("Error: " + data);
                }
            })
            .catch(error => {
                console.error("Error:", error);
                alert("An error occurred while updating the interview.");
            });
        });
    </script>
</body>
</html>