<?php
session_start();
include "../config/db.php";

// Authorization Guard
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success = "";
$error = "";

// Handle Update Profile logic when the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    
    // Validate unique email address configuration
    $check = $conn->query("SELECT id FROM users WHERE email='$email' AND id != '$user_id'");
    if ($check && $check->num_rows > 0) {
        $error = "This email parameter is already assigned to another profile account.";
    } else {
        $conn->query("UPDATE users SET first_name='$first_name', last_name='$last_name', email='$email' WHERE id='$user_id'");
        $success = "Your profile information parameters have been updated successfully!";
    }
}

// Fetch current user details safely to populate dashboard rows
$query = $conn->query("SELECT email, role, first_name, last_name, profile_image FROM users WHERE id = '$user_id'");
$user = $query->fetch_assoc();

// Dynamic Fallback Name configuration setup
$username_fallback = explode('@', $user['email'])[0];
$full_display_name = !empty($user['first_name']) ? htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) : ucfirst($username_fallback);
$initials = !empty($user['first_name']) ? strtoupper(substr($user['first_name'], 0, 1)) : strtoupper(substr($username_fallback, 0, 1));

// Fetch Real-time request metrics summaries
$total_requests = 0;
$completed_requests = 0;
$pending_requests = 0;

$count_query = $conn->query("SELECT COUNT(*) as total, SUM(CASE WHEN status='completed' THEN 1 ELSE 0 END) as done FROM requests WHERE user_id = '$user_id'");
if($count_query) {
    $metrics = $count_query->fetch_assoc();
    $total_requests = $metrics['total'] ?? 0;
    $completed_requests = $metrics['done'] ?? 0;
    $pending_requests = $total_requests - $completed_requests;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Profile - Maintenance System</title>
    <style>
        :root {
            --theme-teal: #007a87;
            --theme-blue: #049fd9;
            --header-gradient: linear-gradient(135deg, #0d4b4d 0%, #115e59 40%, #007a87 100%);
            --dark-surface: #0f172a;
            --text-main: #1e2937;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
            --radius-lg: 16px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, Roboto, sans-serif; }
        body { background-color: #f1f5f9; color: var(--text-main); min-height: 100vh; padding-bottom: 60px; }

        /* ================= TOP UTILITY CONTROL HEADER BAR ================= */
        .top-utility-bar {
            background: #ffffff;
            border-bottom: 1px solid var(--border-color);
            padding: 16px 6%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .brand-logo-space { font-size: 18px; font-weight: 800; color: var(--theme-teal); }
        
        .right-nav-actions .back-action-btn { 
            text-decoration: none; 
            color: var(--theme-teal); 
            font-size: 14px; 
            font-weight: 700; 
            transition: var(--transition); 
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .right-nav-actions .back-action-btn:hover { color: #006670; text-decoration: underline; }

        /* ================= HERO METRICS PANEL ================= */
        .hero-banner-container {
            background: var(--header-gradient);
            padding: 40px 6%;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
            border-bottom: 4px solid #22c55e;
        }

        .left-profile-identity { display: flex; align-items: center; gap: 30px; z-index: 2; }
        
        .avatar-outer-ring {
            width: 110px;
            height: 110px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.15);
            padding: 6px;
            position: relative;
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.2);
        }
        .avatar-inner-canvas {
            width: 100%; height: 100%; border-radius: 50%; background: #ffffff;
            display: flex; align-items: center; justify-content: center;
            overflow: hidden; color: var(--theme-teal); font-size: 36px; font-weight: 700;
        }
        .avatar-inner-canvas img { width: 100%; height: 100%; object-fit: cover; }

        .identity-meta h2 { font-size: 30px; font-weight: 700; letter-spacing: -0.5px; margin-bottom: 4px; }
        .identity-meta p { font-size: 14px; color: rgba(255, 255, 255, 0.75); font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px; }

        .right-metrics-counter-box { display: flex; gap: 40px; z-index: 2; border-left: 1px solid rgba(255,255,255,0.15); padding-left: 40px; }
        .metric-counter-item { text-align: left; }
        .metric-value-row { display: flex; align-items: center; gap: 10px; margin-bottom: 2px; }
        .metric-value-row span.counter-number { font-size: 36px; font-weight: 700; color: #ffffff; }
        .metric-counter-item p.counter-label { font-size: 13px; color: rgba(255, 255, 255, 0.7); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }

        /* ================= PROFILE SETTINGS CONTENT SECTION ================= */
        .main-workspace { padding: 40px 6%; max-width: 800px; margin: 0 auto; }
        
        .form-card {
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 35px;
            box-shadow: 0 4px 6px -1px rgba(15, 23, 42, 0.02);
        }

        .section-headline { font-size: 20px; font-weight: 800; color: var(--dark-surface); margin-bottom: 20px; letter-spacing: -0.5px; }
        
        .grid-split { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .form-group { margin-bottom: 24px; }
        .form-group label { display: block; font-size: 13px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; }
        
        .form-group input {
            width: 100%;
            padding: 14px 18px;
            font-size: 15px;
            font-weight: 500;
            color: var(--dark-surface);
            background: #ffffff;
            border: 1.5px solid var(--border-color);
            border-radius: 12px;
            outline: none;
            transition: var(--transition);
        }
        .form-group input:focus {
            border-color: var(--theme-teal);
            box-shadow: 0 0 0 4px rgba(0, 122, 135, 0.1);
        }
        .form-group input:disabled { background: #f1f5f9; color: #64748b; cursor: not-allowed; }

        /* Notification Alerts Layout */
        .alert-box { display: flex; align-items: center; gap: 12px; padding: 16px; border-radius: 12px; font-size: 14px; font-weight: 600; margin-bottom: 25px; }
        .alert-box.error { background: #fef2f2; color: #991b1b; border-left: 5px solid #ef4444; }
        .alert-box.success { background: #f0fdf4; color: #166534; border-left: 5px solid #22c55e; }

        /* Form Submissions Links */
        .action-row { display: flex; align-items: center; justify-content: space-between; margin-top: 10px; }
        .submit-btn {
            padding: 14px 28px;
            background: var(--theme-teal);
            color: #ffffff;
            border: none;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: var(--transition);
            box-shadow: 0 4px 12px rgba(0, 122, 135, 0.15);
        }
        .submit-btn:hover { background: #006670; transform: translateY(-1px); }
        
        .back-link { text-decoration: none; color: var(--text-muted); font-size: 14px; font-weight: 600; transition: var(--transition); }
        .back-link:hover { color: var(--theme-teal); }

        @media (max-width: 992px) {
            .hero-banner-container { flex-direction: column; align-items: flex-start; gap: 30px; }
            .right-metrics-counter-box { border-left: none; padding-left: 0; width: 100%; justify-content: space-between; border-top: 1px solid rgba(255,255,255,0.15); padding-top: 20px; }
        }
        @media (max-width: 600px) {
            .right-metrics-counter-box { flex-direction: column; gap: 20px; }
            .grid-split { grid-template-columns: 1fr; gap: 0; }
            .left-profile-identity { flex-direction: column; text-align: center; width: 100%; }
        }
    </style>
</head>
<body>

    <header class="top-utility-bar">
        <div class="brand-logo-space">
            Repairs & Maintenance Management System
        </div>
        <nav class="right-nav-actions">
            <a href="dashboard.php" class="back-action-btn">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" style="transform: rotate(180deg);"><polyline points="9 18 15 12 9 6"></polyline></svg>
                Go back to the Dashboard
            </a>
        </nav>
    </header>

    <section class="hero-banner-container">
        <div class="left-profile-identity">
            <div class="avatar-outer-ring">
                <div class="avatar-inner-canvas">
                    <?php if(!empty($user['profile_image'])): ?>
                        <img src="../uploads/profiles/<?php echo htmlspecialchars($user['profile_image']); ?>" alt="Profile avatar">
                    <?php else: ?>
                        <?php echo $initials; ?>
                    <?php endif; ?>
                </div>
            </div>
            <div class="identity-meta">
                <h2><?php echo $full_display_name; ?></h2>
                <p><?php echo ucfirst(htmlspecialchars($user['role'] ?? 'User')); ?></p>
            </div>
        </div>

        <div class="right-metrics-counter-box">
            <div class="metric-counter-item">
                <div class="metric-value-row">
                    <span class="counter-number"><?php echo $total_requests; ?></span>
                </div>
                <p class="counter-label">Total Logs Raised</p>
            </div>
            <div class="metric-counter-item">
                <div class="metric-value-row">
                    <span class="counter-number" style="color: #22c55e;"><?php echo $completed_requests; ?></span>
                </div>
                <p class="counter-label">Completed Tasks</p>
            </div>
            <div class="metric-counter-item">
                <div class="metric-value-row">
                    <span class="counter-number" style="color: #eab308;"><?php echo $pending_requests; ?></span>
                </div>
                <p class="counter-label">Open Operations</p>
            </div>
        </div>
    </section>

    <main class="main-workspace">
        <div class="form-card">
            <h2 class="section-headline">Basic Information Parameters</h2>

            <?php if ($error != ""): ?>
                <div class="alert-box error">⚠️ <?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($success != ""): ?>
                <div class="alert-box success">✅ <?php echo $success; ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="grid-split">
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name" placeholder="John" value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" placeholder="Doe" value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="john.doe@domain.com" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>

                <div class="form-group">
                    <label>Assigned Operational Role</label>
                    <input type="text" value="<?php echo htmlspecialchars(ucfirst($user['role'])); ?>" disabled>
                </div>

                <div class="action-row">
                    <a href="dashboard.php" class="back-link">← Go back to the Dashboard</a>
                    <button type="submit" class="submit-btn">Save Information</button>
                </div>
            </form>
        </div>
    </main>

</body>
</html>