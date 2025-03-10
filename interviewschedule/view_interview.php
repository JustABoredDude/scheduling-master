<?php
require_once 'db.php';
require_once 'functions.php';

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Error: Interview ID is missing.");
}

$id = intval($_GET['id']);
$interview = getInterviewById($conn, $id);

if (!$interview) {
    echo "Error: Interview not found.";
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Interview</title>
    <link rel="stylesheet" href="style.css"> <!-- Connected to Tailwind CSS -->
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen p-6">
    <!-- View Interview Card -->
    <div class="bg-white w-full max-w-md rounded-lg shadow-lg">
        <!-- Header -->
        <div class="bg-green-100 p-4">
            <h1 class="text-xl font-bold text-black text-center">View Interview</h1>
        </div>

        <!-- Interview Details -->
        <div class="p-6 text-gray-800">
            <!-- Applicant Name -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-600 mb-1">Applicant Name:</label>
                <p class="text-gray-800"><?= !empty($interview['applicant_name']) ? htmlspecialchars($interview['applicant_name']) : 'No Name Provided' ?></p>
            </div>

            <!-- Email -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-600 mb-1">Email:</label>
                <p class="text-gray-800"><?= !empty($interview['applicant_email']) ? htmlspecialchars($interview['applicant_email']) : 'No Email Provided' ?></p>
            </div>

            <!-- Date & Time -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-600 mb-1">Date & Time:</label>
                <p class="text-gray-800"><?= date("F j, Y, g:i A", strtotime($interview['scheduled_time'])) ?></p>
            </div>

            <!-- Program -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-600 mb-1">Program:</label>
                <p class="text-gray-800"><?= !empty($interview['program_name']) ? htmlspecialchars($interview['program_name']) : 'No Program Assigned' ?></p>
            </div>

            <!-- Interviewer -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-600 mb-1">Interviewer:</label>
                <p class="text-gray-800"><?= !empty($interview['interviewer_name']) ? htmlspecialchars($interview['interviewer_name']) : 'No Interviewer Assigned' ?></p>
            </div>

            <!-- Status -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-600 mb-1">Status:</label>
                <p class="text-gray-800"><?= htmlspecialchars($interview['status']) ?></p>
            </div>

            <!-- Created At -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-600 mb-1">Created at:</label>
                <p class="text-gray-800"><?= date('F d, Y h:i A', strtotime($interview['created_at'] ?? 'now')) ?></p>
            </div>
        </div>

        <!-- Buttons -->
        <div class="flex justify-between p-6 bg-gray-50 border-t border-gray-200">
            <a href="index.php" class="bg-red-500 text-white px-4 py-2 rounded-md font-semibold hover:bg-red-600 transition duration-300">Back</a>
            <a href="edit_interview.php?id=<?= $id ?>" class="bg-green-500 text-white px-4 py-2 rounded-md font-semibold hover:bg-green-600 transition duration-300">Edit</a>
        </div>
    </div>
</body>
</html>