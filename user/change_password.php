<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    $query = $conn->query("SELECT password FROM users WHERE id = '$user_id'");
    $user = $query->fetch_assoc();

    if ($current_password !== $user['password']) {
        $error = "The current password string entered does not match our database records.";
    } elseif ($new_password !== $confirm_password) {
        $error = "Confirm validation password mismatches your new password configuration.";
    } elseif (strlen($new_password) < 6) {
        $error = "Security threshold: New password must be at least 6 characters long.";
    } else {
        $conn->query("UPDATE users SET password='$new_password' WHERE id='$user_id'");
        $success = "Your account access password has been updated successfully!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - RMS Portal</title>
    <style>
        :root {
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --dark-bg: #090d16;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --border-color: rgba(255, 255, 255, 0.08);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* NEW CONCEPT: Full moving mesh background container matrix */
        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--dark-bg);
            background-image: 
                radial-gradient(at 0% 0%, rgba(79, 70, 229, 0.15) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(124, 58, 237, 0.12) 0px, transparent 50%),
                radial-gradient(at 50% 0%, rgba(13, 148, 136, 0.08) 0px, transparent 50%);
            color: var(--text-main);
            height: 100vh;
            width: 100vw;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
        }

        /* NEW CONCEPT: Floating Translucent Minimalist Dashboard Card Panel */
        .glass-card {
            width: 100%;
            max-width: 460px;
            background: rgba(15, 23, 42, 0.65);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--border-color);
            border-radius: 24px;
            padding: 45px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            position: relative;
        }

        /* Sleek decorative element dot */
        .card-decorator {
            position: absolute;
            top: 25px;
            right: 30px;
            width: 8px;
            height: 8px;
            background: #22c55e;
            border-radius: 50%;
            box-shadow: 0 0 12px #22c55e;
        }

        .card-header {
            margin-bottom: 35px;
        }

        .card-header h2 {
            font-size: 28px;
            font-weight: 800;
            letter-spacing: -0.5px;
            margin-bottom: 8px;
            background: linear-gradient(to right, #ffffff, #cbd5e1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .card-header p {
            color: var(--text-muted);
            font-size: 15px;
            line-height: 1.5;
        }

        /* Transparent Input Groups UI blocks */
        .input-group {
            margin-bottom: 24px;
        }

        .input-group label {
            display: block;
            font-size: 13px;
            font-weight: 700;
            color: #cbd5e1;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 10px;
        }

        .input-group input {
            width: 100%;
            padding: 16px 18px;
            font-size: 15px;
            border: 1px solid var(--border-color);
            background: rgba(255, 255, 255, 0.03);
            border-radius: 14px;
            outline: none;
            color: white;
            transition: var(--transition);
        }

        .input-group input:focus {
            background: rgba(255, 255, 255, 0.05);
            border-color: rgba(99, 102, 241, 0.4);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15);
        }

        /* Notification Blocks */
        .alert-box {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 18px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 25px;
            border: 1px solid transparent;
        }
        .alert-box.error {
            background-color: rgba(239, 68, 68, 0.1);
            border-color: rgba(239, 68, 68, 0.2);
            color: #fca5a5;
        }
        .alert-box.success {
            background-color: rgba(34, 197, 94, 0.1);
            border-color: rgba(34, 197, 94, 0.2);
            color: #bbf7d0;
        }

        /* Solid High-tech Action Control */
        button.btn-submit {
            width: 100%;
            padding: 16px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 14px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: var(--transition);
            box-shadow: 0 4px 14px rgba(79, 70, 229, 0.3);
            margin-top: 5px;
        }

        button.btn-submit:hover {
            background: var(--primary-hover);
            box-shadow: 0 6px 20px rgba(79, 70, 229, 0.45);
            transform: translateY(-1px);
        }

        button.btn-submit:active {
            transform: translateY(0);
        }

        .card-footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid var(--border-color);
            text-align: center;
        }

        .card-footer a {
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .card-footer a:hover {
            color: white;
        }
    </style>
</head>
<body>

<div class="glass-card">
    <div class="card-decorator"></div>
    
    <div class="card-header">
        <h2>Change Password</h2>
        <p>Update your credentials parameters securely.</p>
    </div>

    <?php if ($error != "") { ?>
        <div class="alert-box error">
            <span>⚠️</span> <?php echo $error; ?>
        </div>
    <?php } ?>

    <?php if ($success != "") { ?>
        <div class="alert-box success">
            <span>✅</span> <?php echo $success; ?>
        </div>
    <?php } ?>

    <form method="POST">
        <div class="input-group">
            <label for="current_password">Current Password</label>
            <input type="password" id="current_password" name="current_password" placeholder="••••••••" required>
        </div>

        <div class="input-group">
            <label for="new_password">New Password</label>
            <input type="password" id="new_password" name="new_password" placeholder="Minimum 6 characters" required>
        </div>

        <div class="input-group">
            <label for="confirm_password">Confirm New Password</label>
            <input type="password" id="confirm_password" name="confirm_password" placeholder="••••••••" required>
        </div>

        <button type="submit" class="btn-submit">Apply Security Parameters</button>
    </form>
    
    <div class="card-footer">
        <a href="dashboard.php">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" style="transform: scaleX(-1);"><polyline points="9 18 15 12 9 6"></polyline></svg>
            Go back to the Dashboard
        </a>
    </div>
</div>

</body>
</html>