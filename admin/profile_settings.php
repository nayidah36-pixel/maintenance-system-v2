<?php
session_start();
include "../config/db.php";

// 1. ACCESS CONTROL LAYER - Only allow authenticated administrators
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "admin") {
    header("Location: ../auth/login.php"); 
    exit();
}

$admin_id = $_SESSION['user_id'];
$message = "";
$message_type = ""; 

// Fetch current admin rows first to pull clean records
$admin_profile = $conn->query("SELECT * FROM users WHERE id='$admin_id'")->fetch_assoc();

// 2. FORM ACTION HANDLER 1: UPDATE PROFILE DETAILS (Names & Email)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);

    if (empty($first_name) || empty($last_name) || empty($email)) {
        $message = "All profile fields are required.";
        $message_type = "error";
    } else {
        // Update query mapping to your users table columns
        $update_profile_query = "UPDATE users SET 
            first_name = '$first_name', 
            last_name = '$last_name', 
            email = '$email' 
            WHERE id = '$admin_id'";

        if ($conn->query($update_profile_query)) {
            $message = "Profile details updated successfully!";
            $message_type = "success";
            // Refresh local data array instantly
            $admin_profile = $conn->query("SELECT * FROM users WHERE id='$admin_id'")->fetch_assoc();
        } else {
            $message = "Failed to update profile details due to a database error.";
            $message_type = "error";
        }
    }
}

// 3. FORM ACTION HANDLER 2: SECURE PASSWORD UPDATE ENGINE
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['change_password'])) {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($new_password) || empty($confirm_password)) {
        $message = "Please fill in all password fields.";
        $message_type = "error";
    } elseif ($new_password !== $confirm_password) {
        $message = "New passwords do not match. Please verify.";
        $message_type = "error";
    } elseif (strlen($new_password) < 6) {
        $message = "For safety, passwords must be at least 6 characters long.";
        $message_type = "error";
    } else {
        // Hash the new password securely for our updated login page
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_pass_query = "UPDATE users SET password = '$hashed_password' WHERE id = '$admin_id'";
        
        if ($conn->query($update_pass_query)) {
            $message = "Password updated successfully!";
            $message_type = "success";
        } else {
            $message = "Database synchronization error. Failed to save credentials.";
            $message_type = "error";
        }
    }
}

// 4. PRE-FILL FIELDS FROM DATABASE RECORD
$first = $admin_profile['first_name'] ?? $admin_profile['firstname'] ?? '';
$last = $admin_profile['last_name'] ?? $admin_profile['lastname'] ?? '';
$email_val = $admin_profile['email'] ?? '';

$display_name = !empty($first) ? htmlspecialchars(ucfirst(trim($first))) : 'Admin';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile Settings - RMS</title>
    <style>
        :root {
            --primary: #4f46e5;
            --primary-hover: #6366f1;
            --dark-bg: #090d16;
            --card-surface: rgba(15, 23, 42, 0.6);
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --border-color: rgba(255, 255, 255, 0.08);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--dark-bg);
            background-image: radial-gradient(at 0% 0%, rgba(79, 70, 229, 0.1) 0px, transparent 50%);
            color: var(--text-main);
            display: flex;
            min-height: 100vh;
        }

        /* SIDEBAR NAVIGATION */
        .sidebar {
            width: 280px;
            background: rgba(15, 23, 42, 0.7);
            backdrop-filter: blur(20px);
            border-right: 1px solid var(--border-color);
            display: flex; flex-direction: column; justify-content: space-between;
            padding: 35px 24px; position: fixed; height: 100vh; left: 0; top: 0; z-index: 100;
        }
        .sidebar-brand { font-size: 22px; font-weight: 800; display: flex; align-items: center; gap: 10px; color: white; text-decoration: none; }
        .sidebar-brand span { color: #818cf8; }
        .menu { display: flex; flex-direction: column; gap: 8px; margin-top: 40px; flex-grow: 1; }
        
        .menu-link {
            display: flex; align-items: center; gap: 12px; color: #94a3b8; text-decoration: none;
            padding: 14px 16px; font-weight: 600; font-size: 15px; border-radius: 12px; transition: var(--transition);
        }
        .menu-link:hover, .menu-link.active { background: rgba(255, 255, 255, 0.05); color: white; }
        .menu-link.active { background: var(--primary); color: white; }
        .sidebar-footer a { display: block; text-align: center; text-decoration: none; padding: 12px; border-radius: 10px; color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); font-weight: 700; }
        .sidebar-footer a:hover { background: #ef4444; color: white; }

        /* MAIN APP SHELL */
        .main { margin-left: 280px; width: 100%; padding: 40px 50px; }
        .topbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; }
        .welcome h1 { font-size: 28px; font-weight: 800; letter-spacing: -0.5px; margin-bottom: 4px; }
        .welcome p { color: var(--text-muted); font-size: 15px; }

        /* MULTI-CARD REUSABLE ARCHITECTURE */
        .settings-container { display: flex; flex-direction: column; gap: 30px; max-width: 550px; }
        .settings-card {
            background: var(--card-surface);
            border: 1px solid var(--border-color);
            padding: 35px;
            border-radius: 20px;
            box-shadow: 0 10px 30px -10px rgba(0,0,0,0.3);
        }
        .settings-card h2 { font-size: 20px; font-weight: 700; margin-bottom: 20px; color: white; border-bottom: 1px solid var(--border-color); padding-bottom: 12px; }

        label { display: block; margin-top: 16px; font-weight: 700; font-size: 13px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; }
        input[type="text"], input[type="email"], input[type="password"] { background: rgba(0, 0, 0, 0.3); border: 1px solid var(--border-color); padding: 14px 16px; color: white; border-radius: 10px; outline: none; width: 100%; margin-top: 6px; display: block; font-size: 15px; transition: var(--transition); }
        input:focus { border-color: var(--primary); background: rgba(0, 0, 0, 0.5); }
        
        button { padding: 14px 24px; border: none; border-radius: 10px; background: var(--primary); color: white; font-weight: 700; cursor: pointer; margin-top: 25px; width: 100%; font-size: 14px; letter-spacing: 0.5px; transition: var(--transition); }
        button:hover { background: var(--primary-hover); transform: translateY(-1px); }

        /* NOTIFICATION TOASTS */
        .toast { padding: 16px 20px; border-radius: 12px; font-weight: 700; margin-bottom: 25px; font-size: 14px; display: flex; align-items: center; gap: 10px; animation: formFadeIn 0.3s ease; }
        .toast.success { background: rgba(16, 185, 129, 0.12); border: 1px solid #10b981; color: #10b981; }
        .toast.error { background: rgba(239, 68, 68, 0.12); border: 1px solid #ef4444; color: #ef4444; }

        @keyframes formFadeIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>

    <nav class="sidebar">
        <div>
            <a href="dashboard.php" class="sidebar-brand">RMS <span>Admin</span></a>
            <div class="menu">
                <a href="dashboard.php" class="menu-link">Overview</a>
                <a href="profile_settings.php" class="menu-link active">Settings</a>
            </div>
        </div>
        <div class="sidebar-footer" style="width:100%;">
            <a href="../auth/logout.php">Logout</a>
        </div>
    </nav>

    <main class="main">
        <div class="topbar">
            <div class="welcome">
                <h1>Admin Profile Settings</h1>
                <p>Manage console variables and workspace rules for <?php echo $display_name; ?></p>
            </div>
        </div>

        <?php if (!empty($message)) { ?>
            <div id="actionToast" class="toast <?php echo $message_type; ?>">
                <span><?php echo $message_type == 'success' ? '✅' : '⚠️'; ?></span> <?php echo $message; ?>
            </div>
            <script>
                setTimeout(() => {
                    const toast = document.getElementById('actionToast');
                    if(toast) {
                        toast.style.transition = 'opacity 0.5s ease';
                        toast.style.opacity = '0';
                        setTimeout(() => toast.remove(), 500);
                    }
                }, 4000);
            </script>
        <?php } ?>

        <div class="settings-container">
            
            <div class="settings-card">
                <h2>Update Administration Details</h2>
                <form method="POST" action="profile_settings.php">
                    <input type="hidden" name="update_profile" value="1">

                    <label for="first_name">First Name:</label>
                    <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($first); ?>" required>

                    <label for="last_name">Last Name:</label>
                    <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($last); ?>" required>

                    <label for="email">Email Address:</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email_val); ?>" required>

                    <button type="submit">Save Account Details</button>
                </form>
            </div>

            <div class="settings-card">
                <h2>Change Administration Password</h2>
                <form method="POST" action="profile_settings.php">
                    <input type="hidden" name="change_password" value="1">

                    <label for="new_password">New Password:</label>
                    <input type="password" id="new_password" name="new_password" placeholder="Minimum 6 characters..." required>

                    <label for="confirm_password">Confirm New Password:</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Repeat new security password..." required>

                    <button type="submit">Update Administrator Password</button>
                </form>
            </div>

        </div>
    </main>

</body>
</html>