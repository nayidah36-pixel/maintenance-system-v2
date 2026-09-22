<?php
session_start();
include "../config/db.php";

// Protect admin access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "admin") {
    header("Location: ../auth/login.php");
    exit();
}

// Pull requests that contain active text in the tech_notes field
$reports_query = $conn->query("
    SELECT r.*, t.name AS tech_name 
    FROM requests r
    INNER JOIN technicians t ON r.technician_id = t.id
    WHERE r.tech_notes IS NOT NULL AND r.tech_notes != ''
    ORDER BY r.id DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Technician Progress Reports</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f9; margin: 0; padding: 30px; }
        .container { background: white; padding: 25px; border-radius: 10px; max-width: 1000px; margin: 0 auto; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        h2 { margin-top: 0; color: #0f172a; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 14px; text-align: left; border-bottom: 1px solid #e2e8f0; font-size: 15px; }
        th { background: #f8fafc; color: #475569; font-weight: 600; }
        .status-badge { font-weight: bold; padding: 4px 8px; border-radius: 4px; font-size: 13px; }
        .notes-td { background: #fafafa; font-style: italic; color: #334155; max-width: 350px; word-wrap: break-word; }
        .back-link { display: inline-block; margin-bottom: 15px; color: #4f46e5; text-decoration: none; font-weight: 600; }
    </style>
</head>
<body>

<div class="container">
    <a href="dashboard.php" class="back-link">&larr; Back to Admin Dashboard</a>
    <h2>Technician Progress Reports Logs</h2>

    <table>
        <thead>
            <tr>
                <th>Task ID</th>
                <th>Task Title</th>
                <th>Technician</th>
                <th>Admin View Status</th>
                <th>Submitted Work Report</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            if ($reports_query && $reports_query->num_rows > 0) {
                while($report = $reports_query->fetch_assoc()) { 
            ?>
                <tr>
                    <td>#<?php echo $report['id']; ?></td>
                    <td><b><?php echo htmlspecialchars($report['title']); ?></b></td>
                    <td><?php echo htmlspecialchars($report['tech_name']); ?></td>
                    <td>
                        <span class="status-badge" style="color: #4f46e5;">
                            <?php echo htmlspecialchars($report['status']); ?>
                        </span>
                    </td>
                    <td class="notes-td">
                        "<?php echo htmlspecialchars($report['tech_notes']); ?>"
                    </td>
                </tr>
            <?php 
                }
            } else {
                echo "<tr><td colspan='5' style='text-align:center; color:#64748b; padding: 30px;'>No progress updates or technician reports found in the system.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

</body>
</html>