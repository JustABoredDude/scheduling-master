<?php
require_once 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $interviewer_name = $_POST['interviewer_name'];
    $interviewer_email = $_POST['interviewer_email'];
    $program_id = intval($_POST['program']);
    $applicant_name = $_POST['applicant_name'];
    $applicant_email = $_POST['applicant_email'];
    $scheduled_time = $_POST['scheduled_time'];

    // Validate scheduled time
    if (DateTime::createFromFormat('Y-m-d\TH:i', $scheduled_time) === false) {
        $error = "Invalid date and time format.";
    } else {
        // Check if interviewer exists
        $stmt = $conn->prepare("SELECT id, name FROM interviewers WHERE email = ?");
        $stmt->bind_param("s", $interviewer_email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $interviewer_id = $row['id'];
            // Update interviewer name if it has changed
            if ($row['name'] !== $interviewer_name) {
                $stmt = $conn->prepare("UPDATE interviewers SET name = ? WHERE id = ?");
                $stmt->bind_param("si", $interviewer_name, $interviewer_id);
                $stmt->execute();
            }
        } else {
            // Insert new interviewer
            $stmt = $conn->prepare("INSERT INTO interviewers (name, email, program_id) VALUES (?, ?, ?)");
            $stmt->bind_param("ssi", $interviewer_name, $interviewer_email, $program_id);
            $stmt->execute();
            $interviewer_id = $conn->insert_id;
        }

        // Check if applicant exists
        $stmt = $conn->prepare("SELECT id, name FROM applicants WHERE email = ?");
        $stmt->bind_param("s", $applicant_email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $applicant_id = $row['id'];
            // Update applicant name if it has changed
            if ($row['name'] !== $applicant_name) {
                $stmt = $conn->prepare("UPDATE applicants SET name = ? WHERE id = ?");
                $stmt->bind_param("si", $applicant_name, $applicant_id);
                $stmt->execute();
            }
        } else {
            // Insert new applicant
            $stmt = $conn->prepare("INSERT INTO applicants (name, email) VALUES (?, ?)");
            $stmt->bind_param("ss", $applicant_name, $applicant_email);
            $stmt->execute();
            $applicant_id = $conn->insert_id;
        }

        // Insert new interview
        $stmt = $conn->prepare("INSERT INTO interviews (applicant_id, interviewer_id, program_id, scheduled_time, status) 
                               VALUES (?, ?, ?, ?, 'scheduled')");
        $stmt->bind_param("iiis", $applicant_id, $interviewer_id, $program_id, $scheduled_time);

        if ($stmt->execute()) {
            $newId = mysqli_insert_id($conn);
            // Redirect to index.php
            header("Location: index.php");
            exit();
        } else {
            $error = "Error adding interview: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Interviewer & Applicant</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white shadow-md rounded-lg p-6 w-96 border border-gray-200">
        
        <!-- Header -->
        <div class="bg-green-100 text-center py-2 rounded-t-lg">
            <h2 class="text-lg font-semibold text-black">New Interview</h2>
        </div>

        <?php if(isset($error)): ?>
            <div class="bg-red-100 text-red-700 p-2 my-2 rounded">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form action="add_interview.php" method="POST" class="space-y-4 mt-4">
            
              <!-- Applicant -->
              <div>
                <label for="applicant_name" class="block text-sm font-medium text-gray-700">Applicant Name</label>
                <input type="text" id="applicant_name" name="applicant_name" required 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 focus:outline-none focus:ring focus:ring-green-300">
            </div>

            <div>
                <label for="applicant_email" class="block text-sm font-medium text-gray-700">Applicant Email</label>
                <input type="email" id="applicant_email" name="applicant_email" required 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 focus:outline-none focus:ring focus:ring-green-300">
            </div>
            <div>
                <label for="scheduled_time" class="block text-sm font-medium text-gray-700">Date & Time</label>
                <input type="datetime-local" id="scheduled_time" name="scheduled_time" required 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 focus:outline-none focus:ring focus:ring-green-300">
            </div>
            <div>
                <label for="program" class="block text-sm font-medium text-gray-700">Program</label>
                <select id="program" name="program" required 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 focus:outline-none focus:ring focus:ring-green-300">
                    <option value="" disabled selected></option> <!-- Empty default option -->
                    <option value="1">COT</option>
                    <option value="2">COED</option>
                    <option value="3">CBA</option>
                </select>
            </div>
            <div>
                <label for="interviewer_name" class="block text-sm font-medium text-gray-700">Interviewer Name</label>
                <input type="text" id="interviewer_name" name="interviewer_name" required 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 focus:outline-none focus:ring focus:ring-green-300">
            </div>

            <div>
                <label for="interviewer_email" class="block text-sm font-medium text-gray-700">Interviewer Email</label>
                <input type="email" id="interviewer_email" name="interviewer_email" required 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 focus:outline-none focus:ring focus:ring-green-300">
            </div>
     

            <!-- Scheduled Time -->
       

            <!-- Buttons -->
            <div class="flex justify-between mt-4">
                <a href="index.php" class="px-4 py-2 bg-red-100 text-red-700 rounded-md hover:bg-red-200 font-semibold">Cancel</a>
                <button type="submit" class="px-4 py-2 bg-green-700 text-white rounded-md hover:bg-green-800 font-semibold">Confirm</button>
            </div>
        </form>
    </div>
</body>
</html>