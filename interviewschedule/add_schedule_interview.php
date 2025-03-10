<?php
session_start();
require_once 'db.php';



$error = '';
$showSummary = false;
$interviewer = [];
$applicant = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['interviewer_name'])) {
        // Handle adding interviewer and applicant
        $interviewer_name = $_POST['interviewer_name'];
        $interviewer_email = $_POST['interviewer_email'];
        $department = $_POST['department'];
        $applicant_name = $_POST['applicant_name'];
        $applicant_email = $_POST['applicant_email'];

        // Check if interviewer exists
        $stmt = $conn->prepare("SELECT id, department FROM interviewers WHERE email = ?");
        $stmt->bind_param("s", $interviewer_email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $interviewer_id = $row['id'];
        } else {
            // Insert interviewer
            $stmt = $conn->prepare("INSERT INTO interviewers (name, email, department) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $interviewer_name, $interviewer_email, $department);
            if ($stmt->execute()) {
                $interviewer_id = $conn->insert_id;
            } else {
                $error = "Error adding interviewer: " . $stmt->error;
            }
        }

        // Check if applicant exists
        if (empty($error)) {
            $stmt = $conn->prepare("SELECT id FROM applicants WHERE email = ?");
            $stmt->bind_param("s", $applicant_email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $applicant_id = $row['id'];
            } else {
                // Insert applicant
                $stmt = $conn->prepare("INSERT INTO applicants (name, email) VALUES (?, ?)");
                $stmt->bind_param("ss", $applicant_name, $applicant_email);
                if ($stmt->execute()) {
                    $applicant_id = $conn->insert_id;
                } else {
                    $error = "Error adding applicant: " . $stmt->error;
                }
            }
        }

        if (empty($error)) {
            $_SESSION['interviewer_id'] = $interviewer_id;
            $_SESSION['applicant_id'] = $applicant_id;
        }
    } elseif (isset($_POST['scheduled_time'])) {
        // Handle scheduling the interview
        $interviewer_id = $_SESSION['interviewer_id'];
        $applicant_id = $_SESSION['applicant_id'];
        $scheduled_time = $_POST['scheduled_time'];

        // Fetch Interviewer Info
        $stmt = $conn->prepare("SELECT name, email, department FROM interviewers WHERE id = ?");
        $stmt->bind_param("i", $interviewer_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $interviewer = $result->fetch_assoc();

        // Fetch Applicant Info
        $stmt = $conn->prepare("SELECT name, email FROM applicants WHERE id = ?");
        $stmt->bind_param("i", $applicant_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $applicant = $result->fetch_assoc();

        // Insert the interview schedule
        $stmt = $conn->prepare("INSERT INTO interviews (interviewer_id, applicant_id, scheduled_time) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $interviewer_id, $applicant_id, $scheduled_time);

        if ($stmt->execute()) {
            $showSummary = true;
        } else {
            $error = "Error scheduling interview: " . $stmt->error;
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Interview</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Manage Interview</h2>

        <?php if (!empty($error)): ?>
            <div class="error-message"><?= $error ?></div>
        <?php endif; ?>

        <!-- Step 1: Add Interviewer & Applicant -->
        <form method="POST" <?= isset($_SESSION['interviewer_id']) ? 'style="display:none;"' : '' ?>>
            <h3>Interviewer</h3>
            <label for="interviewer_name">Name:</label>
            <input type="text" name="interviewer_name" required>
            <label for="interviewer_email">Email:</label>
            <input type="email" name="interviewer_email" required>
            <label for="department">Department:</label>
            <select name="department" required>
                <option value="CABA">CABA</option>
                <option value="CEIT">CEIT</option>
                <option value="COED">COED</option>
                <option value="CAS">CAS</option>
            </select>

            <h3>Applicant</h3>
            <label for="applicant_name">Name:</label>
            <input type="text" name="applicant_name" required>
            <label for="applicant_email">Email:</label>
            <input type="email" name="applicant_email" required>

            <button type="submit">Next</button>
        </form>

        <!-- Step 2: Schedule Interview -->
        <?php if (isset($_SESSION['interviewer_id']) && !$showSummary): ?>
            <form method="POST">
                <label for="scheduled_time">Select Date & Time:</label>
                <input type="datetime-local" name="scheduled_time" required>
                <button type="submit">Confirm</button>
            </form>
        <?php endif; ?>

        <!-- Step 3: Summary -->
        <?php if ($showSummary): ?>
            <div class="summary">
                <h3>Interview Scheduled</h3>
                <p><strong>Applicant Name:</strong> <?= htmlspecialchars($applicant['name']) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($applicant['email']) ?></p>
                <p><strong>Date:</strong> <?= date('Y-m-d', strtotime($scheduled_time)) ?></p>
                <p><strong>Time:</strong> <?= date('h:i A', strtotime($scheduled_time)) ?></p>
                <p><strong>Interviewer:</strong> <?= htmlspecialchars($interviewer['name']) ?></p>
                <p><strong>Department:</strong> <?= htmlspecialchars($interviewer['department']) ?></p>

                <button onclick="window.location.href='index.php'">Go Back</button>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
