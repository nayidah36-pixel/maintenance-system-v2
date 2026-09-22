<?php
session_start();
include "../config/db.php";

// 1. Secure access control layer - Only logged-in technicians can execute updates
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "technician") {
    header("Location: ../auth/login.php");
    exit();
}

// 2. Validate input variables from the dashboard request card form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['request_id'])) {
    
    $request_id = intval($_POST['request_id']);
    $tech_id = $_SESSION['user_id'];
    
    // Safely capture drop-down status selector values and textareas
    $status = isset($_POST['status']) ? mysqli_real_escape_string($conn, $_POST['status']) : 'In Progress';
    $tech_notes = isset($_POST['tech_notes']) ? mysqli_real_escape_string($conn, trim($_POST['tech_notes'])) : '';

    if (!empty($request_id)) {
        // 3. Database Update execution sequence
        // Updates global status, technician task status tracker, injects technical notes, and sets admin notification alert
        $sql = "UPDATE requests 
                SET status = '$status', 
                    technician_status = '$status', 
                    tech_notes = '$tech_notes',
                    notification = 1 
                WHERE id = $request_id AND technician_id = '$tech_id'";

        if ($conn->query($sql)) {
            // Drop straight back to the same dashboard layout with a successful trigger parameter
            header("Location: dashboard.php?success=1");
            exit();
        } else {
            // Fallback error flag routing if database connection drops
            header("Location: dashboard.php?error=1");
            exit();
        }
    }
} else {
    // If accessed directly via URL parameters instead of POST forms, safely route home
    header("Location: dashboard.php");
    exit();
}