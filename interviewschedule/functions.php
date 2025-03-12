<?php
require_once 'db.php';

// Fetch all applicants from the database
function getApplicants($conn) {
    $sql = "SELECT * FROM applicants";
    $result = $conn->query($sql);

    if (!$result) {
        die("Error fetching applicants: " . $conn->error);
    }

    return $result->fetch_all(MYSQLI_ASSOC);
}

// Fetch all programs from the database
function getPrograms($conn) {
    $sql = "SELECT id, name FROM programs";
    $result = $conn->query($sql);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

// Fetch interview by ID
function getInterviewById($conn, $id) {
    $sql = "SELECT 
                i.id, 
                a.name AS applicant_name, 
                a.email AS applicant_email, 
                ir.name AS interviewer_name, 
                p.name AS program_name,  
                i.scheduled_time, 
                i.status, 
                i.created_at
            FROM interviews i
            JOIN applicants a ON i.applicant_id = a.id
            JOIN interviewers ir ON i.interviewer_id = ir.id
            JOIN programs p ON i.program_id = p.id
            WHERE i.id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Fetch interviews based on program, status, and search term
function getInterviews($conn, $program = null, $status = null , $search = '', $sort = 'newest', $interviewer = null) {
    $sql = "SELECT 
                i.id, 
                a.name AS applicant, 
                p.name AS program, 
                v.name AS interviewer, 
                i.scheduled_time, 
                i.status 
            FROM interviews i
            LEFT JOIN applicants a ON i.applicant_id = a.id
            LEFT JOIN interviewers v ON i.interviewer_id = v.id
            LEFT JOIN programs p ON i.program_id = p.id
            WHERE 1=1";
    
    $params = [];
    $types = "";
    
    if ($status === 'trash') {
        $sql .= " AND i.status = 'trash'";
    } elseif ($status && $status !== 'date' && $status !== 'interviewer') {
        if (in_array($status, ['scheduled', 'cancelled'])) {
        $sql .= " AND i.status = ?";
        $params[] = $status;
        $types .= "s";
        }
    } else {
        $sql .= " AND i.status IN ('scheduled', 'cancelled')";
    }

    if ($program) {
        $sql .= " AND p.name = ?";
        $params[] = $program;
        $types .= "s";
    }

    if ($interviewer) {
        $sql .= " AND v.name = ?";
        $params[] = $interviewer;
        $types .= "s";
    }

    if (!empty($search)) {
        $sql .= " AND (a.name LIKE ? OR v.name LIKE ? OR p.name LIKE ?)";
        $searchTerm = "%$search%";
        array_push($params, $searchTerm, $searchTerm, $searchTerm);
        $types .= "sss";
    }

    $stmt = $conn->prepare($sql);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    
    if ($interviewer) {
        usort($interviews, function ($a, $b) {
            return strcmp($a['applicant'], $b['applicant']);
        });
    }

    return $interviews;
}