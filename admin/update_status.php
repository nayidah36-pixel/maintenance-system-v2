<?php
session_start();
include "../config/db.php";

// Protect admin access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "admin") {
    header("Location: ../auth/login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$id = intval($_GET['id']);

// FIXED: Using CONCAT to safely merge your first_name and last_name database columns!
$query = $conn->query("
    SELECT r.*, CONCAT(t.first_name, ' ', t.last_name) AS tech_name 
    FROM requests r 
    LEFT JOIN technicians t ON r.technician_id = t.id 
    WHERE r.id = $id
");
$request = $query->fetch_assoc();

if (!$request) {
    header("Location: dashboard.php");
    exit();
}

// Form submission processing
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $status = $_POST['status'];

    // Update both status tracking columns inside the request row and drop the unread flag
    $conn->query("
        UPDATE requests 
        SET status = '$status',
            technician_status = '$status',
            notification = 0
        WHERE id = $id
    ");

    $tech_id = $request['technician_id'];
    if (!empty($tech_id)) {
        if ($status == "Completed") {
            // Release technician back to 'Available' once the task is closed
            $conn->query("UPDATE technicians SET availability_status = 'Available' WHERE id = $tech_id");
        } else {
            // Keep them marked as 'Busy' if status is Pending or In Progress
            $conn->query("UPDATE technicians SET availability_status = 'Busy' WHERE id = $tech_id");
        }
    }

    header("Location: dashboard.php?msg=Status saved successfully");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Request Status</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f9; padding: 20px; }
        .box { background: white; padding: 25px; max-width: 450px; margin: 50px auto; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.08); }
        h2 { margin-top: 0; color: #0f172a; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px; }
        .details { margin: 15px 0; font-size: 15px; color: #334155; line-height: 1.6; }
        .tech-box { background: #eef2f3; padding: 12px; border-radius: 6px; margin: 15px 0; border-left: 4px solid #4f46e5; }
        .tech-box strong { font-size: 13px; color: #4f46e5; display: block; margin-bottom: 4px; }
        .tech-box p { margin: 0; font-size: 14px; color: #475569; font-style: italic; }
        select, button { width: 100%; padding: 12px; margin-top: 10px; box-sizing: border-box; border-radius: 6px; font-size: 15px; }
        select { border: 1px solid #cbd5e1; background: #f8fafc; outline: none; }
        button { background: black; color: white; border: none; font-weight: bold; cursor: pointer; margin-top: 20px; }
        button:hover { background: #222; }
        .back { display: block; text-align: center; margin-top: 15px; color: #4f46e5; text-decoration: none; font-size: 14px; }
    </style>
</head>
<body>

<div class="box">
    <h2>Update Status</h2>
    
    <div class="details">
        <p><b>Request Title:</b> <?php echo htmlspecialchars($request['title']); ?></p>
        <p><b>Assigned Tech:</b> <?php echo htmlspecialchars($request['tech_name'] ?? 'None Assigned'); ?></p>
        <p><b>Current Status:</b> <span style="font-weight:bold; color:#4f46e5;"><?php echo htmlspecialchars($request['status']); ?></span></p>
    </div>

    <div class="tech-box">
        <strong>Technician Live Response:</strong>
        <p><?php echo !empty($request['tech_notes']) ? htmlspecialchars($request['tech_notes']) : "No progress notes uploaded yet."; ?></p>
    </div>

    <form method="POST">
        <label for="status"><b>Modify Job Status:</b></label>
        <select name="status" id="status">
            <option value="Pending" <?php if($request['status'] == "Pending") echo "selected"; ?>>Pending</option>
            <option value="In Progress" <?php if($request['status'] == "In Progress") echo "selected"; ?>>In Progress</option>
            <option value="Completed" <?php if($request['status'] == "Completed") echo "selected"; ?>>Completed</option>
        </select>

        <button type="submit">Save Status Update</button>
    </form>

    <a href="dashboard.php" class="back">&larr; Return to Dashboard</a>
</div>

</body>
</html>