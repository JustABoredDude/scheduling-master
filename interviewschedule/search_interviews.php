<?php
require_once 'db.php';
require_once 'functions.php';

$search = $_GET['search'] ?? '';

// Fetch interviews based on the search term
$interviews = getInterviews($conn, null, 'scheduled', $search);

// Return the results as JSON
echo json_encode([
    'success' => true,
    'interviews' => $interviews
]);
?>