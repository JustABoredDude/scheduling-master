<?php
// db.php - Database Connection File
$servername = "localhost";
$username = "root";
$password = ""; // Ensure this matches your database password
$dbname = "interviewsched"; // Ensure this matches your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch interviewers with program names
function getInterviewers($conn) {
    $sql = "SELECT interviewers.id, interviewers.name, interviewers.email, programs.name AS program_name 
            FROM interviewers 
            LEFT JOIN programs ON interviewers.program_id = programs.id";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}
