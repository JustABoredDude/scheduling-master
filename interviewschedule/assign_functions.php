<?php
require_once 'db.php';

function getInterviewers($conn) {
    $sql = "SELECT * FROM interviewers";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getInterviews($conn) {
    $sql = "SELECT 
                interviews.id, 
                applicants.name AS applicant, 
                applicants.email AS applicant_email, 
                interviewers.name AS interviewer, 
                interviewers.department AS interviewer_department, 
                interviews.scheduled_time 
            FROM interviews
            JOIN applicants ON interviews.applicant_id = applicants.id
            JOIN interviewers ON interviews.interviewer_id = interviewers.id";
    
    $result = mysqli_query($conn, $sql);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}
?>
