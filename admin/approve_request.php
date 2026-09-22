<?php
session_start();
include "../config/db.php";

// protect admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "admin") {
    header("Location: ../auth/login.php");
    exit();
}

$id = $_GET['id'];

// get technician assigned to this request
$request = $conn->query("
    SELECT technician_id
    FROM requests
    WHERE id='$id'
")->fetch_assoc();

$technician_id = $request['technician_id'];

// approve request
$conn->query("
    UPDATE requests
    SET admin_status='Completed',
        admin_approved_at=NOW()
    WHERE id='$id'
");

// make technician available again
$conn->query("
    UPDATE technicians
    SET availability_status='Available'
    WHERE id='$technician_id'
");

header("Location: dashboard.php");
exit();
?>