<?php
include "../config/db.php";

$request_id = $_GET['id'];

// get request details
$request = $conn->query("
    SELECT * FROM requests WHERE id='$request_id'
")->fetch_assoc();

$service = $request['service_type'];

// FIX: Look inside the brand new 'technicians' table instead of 'users'!
$techs = $conn->query("
    SELECT * FROM technicians 
    WHERE availability_status='Available'
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Assign Technician</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            padding: 30px;
        }

        .box {
            background: white;
            padding: 20px;
            max-width: 500px;
            margin: auto;
            border-radius: 10px;
            box-shadow: 0 3px 8px rgba(0,0,0,0.1);
        }

        select, button {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            box-sizing: border-box;
        }

        button {
            background: #667eea;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
        }
        
        button:hover {
            background: #5a67d8;
        }
    </style>
</head>

<body>

<div class="box">

    <h3>Assign Technician</h3>

    <p><b>Service:</b> <?php echo htmlspecialchars($service); ?></p>

    <form method="POST" action="assign_action.php">

        <input type="hidden" name="request_id" value="<?php echo $request_id; ?>">

        <select name="technician_id" required>

            <option value="">Select Technician</option>

            <?php while ($t = $techs->fetch_assoc()) { ?>
                <option value="<?php echo $t['id']; ?>">
                    <?php echo htmlspecialchars($t['name'] ?? $t['first_name'] ?? 'Technician'); ?> (Available)
                </option>
            <?php } ?>

        </select>

        <button type="submit">Assign</button>

    </form>

</div>

</body>
</html>