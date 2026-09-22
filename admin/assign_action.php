<?php
session_start();
include "../config/db.php";

// Protect admin access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "admin") {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $request_id = intval($_POST['request_id']);
    $technician_id = intval($_POST['technician_id']);

    if ($request_id > 0 && $technician_id > 0) {
        // 1. Assign the tech to the request and update request tracking states
        $conn->query("
            UPDATE requests 
            SET technician_id = $technician_id, 
                status = 'In Progress', 
                technician_status = 'In Progress',
                notification = 0
            WHERE id = $request_id
        ");

        // 2. IMMEDIATELY turn that technician's status to Busy in the technicians table
        $conn->query("
            UPDATE technicians 
            SET availability_status = 'Busy' 
            WHERE id = $technician_id
        ");

        header("Location: dashboard.php?msg=Technician assigned successfully");
        exit();
    } else {
        header("Location: dashboard.php?error=Invalid request or technician selection");
        exit();
    }
} else {
    header("Location: dashboard.php");
    exit();
}
?>