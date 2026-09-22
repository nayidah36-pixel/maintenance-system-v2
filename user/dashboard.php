<?php
session_start();
include "../config/db.php";
 
// Authorization Guard
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}
 
// Fetch Logged-In User Profile Details safely
$user_id = $_SESSION['user_id'];
$query = $conn->query("SELECT email, role FROM users WHERE id = '$user_id'");
$user_profile = $query->fetch_assoc();
 
// Split user email prefix to gracefully extract a welcome name display
$username = explode('@', $user_profile['email'])[0];
$display_name = ucfirst($username);
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - RMS Portal</title>
 
    <style>
        :root {
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --dark-bg: #0f172a;
            --text-main: #1f2937;
            --text-muted: #64748b;
            --bg-light: #f8fafc;
            --border-color: #e2e8f0;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
 
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
 
        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: var(--bg-light);
            color: var(--text-main);
            display: flex;
            min-height: 100vh;
        }
 
        /* SIDEBAR NAVIGATION LAYOUT */
        .sidebar {
            width: 280px;
            background: var(--dark-bg);
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 30px 24px;
            position: fixed;
            height: 100vh;
            left: 0;
            top: 0;
            z-index: 10;
        }
 
        .sidebar-brand {
            font-size: 20px;
            font-weight: 800;
            color: white;
            letter-spacing: -0.5px;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 40px;
        }
 
        .sidebar-brand span {
            color: #818cf8;
        }
 
        .menu-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 8px;
            flex-grow: 1;
        }
 
        .menu-item a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 16px;
            color: #94a3b8;
            text-decoration: none;
            font-weight: 600;
            font-size: 15px;
            border-radius: 10px;
            transition: var(--transition);
        }
 
        .menu-item a:hover,
        .menu-item.active a {
            background: rgba(255, 255, 255, 0.06);
            color: white;
        }
 
        .menu-item.active a {
            background: var(--primary);
        }
 
        /* MAIN CONTENT AREA SPACER */
        .main-content {
            margin-left: 280px;
            flex: 1;
            padding: 40px 50px;
        }
 
        /* TOP HEADER PROFILE BLOCK AREA */
        .top-navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
        }
 
        .user-meta h1 {
            font-size: 32px;
            font-weight: 800;
            color: var(--dark-bg);
            letter-spacing: -0.8px;
        }
 
        .estate-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #e0e7ff;
            color: #4338ca;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 700;
            margin-top: 6px;
        }
 
        /* INTERACTIVE DROPDOWN CONTAINER ARCHITECTURE */
        .profile-menu-container {
            position: relative;
        }
 
        .avatar-interactive-trigger {
            display: flex;
            align-items: center;
            gap: 14px;
            background: white;
            padding: 8px 16px;
            border-radius: 50px;
            border: 1px solid var(--border-color);
            cursor: pointer;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);
            transition: var(--transition);
            user-select: none;
        }
 
        .avatar-interactive-trigger:hover {
            border-color: #cbd5e1;
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05);
        }
 
        .avatar-circle {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            color: white;
            font-weight: 700;
            font-size: 16px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
 
        .trigger-details {
            text-align: left;
        }
 
        .trigger-details .user-name {
            font-size: 14px;
            font-weight: 700;
            color: var(--dark-bg);
        }
 
        .trigger-details .user-role {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
            color: var(--text-muted);
        }
 
        /* THE EXTENSIVE FLYOUT EXPANSION INTERFACE OVERLAY */
        .profile-flyout-menu {
            position: absolute;
            right: 0;
            top: calc(100% + 12px);
            width: 280px;
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 16px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            display: none;
            flex-direction: column;
            overflow: hidden;
            z-index: 50;
            animation: menuFade 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
 
        @keyframes menuFade {
            from { opacity: 0; transform: translateY(-8px); }
            to { opacity: 1; transform: translateY(0); }
        }
 
        .flyout-header {
            padding: 20px;
            background: var(--bg-light);
            border-bottom: 1px solid var(--border-color);
        }
 
        .flyout-header p.email {
            font-size: 14px;
            font-weight: 500;
            color: var(--text-main);
            word-break: break-all;
        }
 
        .flyout-links {
            padding: 8px;
            list-style: none;
        }
 
        .flyout-links li a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 14px;
            color: #475569;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            border-radius: 10px;
            transition: var(--transition);
        }
 
        .flyout-links li a:hover {
            background: var(--bg-light);
            color: var(--primary);
        }
 
        .flyout-links li.logout-separator {
            border-top: 1px solid var(--border-color);
            margin-top: 8px;
            padding-top: 8px;
        }
 
        .flyout-links li.logout-separator a {
            color: #ef4444;
        }
 
        .flyout-links li.logout-separator a:hover {
            background: #fef2f2;
        }
 
        /* MAIN UTILITY DISPATCH GRID */
        .section-title {
            font-size: 22px;
            font-weight: 800;
            color: var(--dark-bg);
            margin-bottom: 24px;
            letter-spacing: -0.5px;
        }
 
        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 24px;
        }
 
        .service-card {
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 16px;
            text-decoration: none;
            color: var(--text-main);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.01);
            transition: var(--transition);
            position: relative;
        }
 
        .card-image-wrapper {
            width: 100%;
            height: 160px;
            overflow: hidden;
            position: relative;
            background: #cbd5e1;
        }
 
        .card-image-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: var(--transition);
        }
 
        .card-image-wrapper::after {
            content: "";
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(to top, rgba(0,0,0,0.4), transparent);
        }
 
        .card-body-content {
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: white;
            flex-grow: 1;
        }
 
        .card-text-block {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
 
        .card-body-content span.card-title {
            font-size: 16px;
            font-weight: 700;
            color: var(--dark-bg);
            transition: var(--transition);
        }
 
        .card-body-content span.card-desc {
            font-size: 13px;
            color: var(--text-muted);
        }
 
        .icon-box {
            width: 42px;
            height: 42px;
            background: var(--bg-light);
            border-radius: 10px;
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
            flex-shrink: 0;
        }
 
        /* High Level Layout Interactions */
        .service-card:hover {
            transform: translateY(-6px);
            border-color: var(--primary);
            box-shadow: 0 20px 25px -5px rgba(79, 70, 229, 0.1), 0 8px 10px -6px rgba(79, 70, 229, 0.1);
        }
 
        .service-card:hover .card-image-wrapper img {
            transform: scale(1.06);
        }
 
        .service-card:hover .icon-box {
            background: var(--primary);
            color: white;
        }
 
        .service-card:hover span.card-title {
            color: var(--primary);
        }
 
        /* Document Scale Mobile Breakpoints adaptation */
        @media (max-width: 1024px) {
            .sidebar { width: 80px; padding: 30px 12px; align-items: center; }
            .sidebar-brand span, .menu-item span { display: none; }
            .main-content { margin-left: 80px; padding: 30px; }
        }
 
        @media (max-width: 680px) {
            body { flex-direction: column; }
            .sidebar { width: 100%; height: auto; position: relative; padding: 20px; }
            .menu-list { flex-direction: row; justify-content: space-around; width: 100%; }
            .main-content { margin-left: 0; padding: 20px; }
            .top-navbar { flex-direction: column; align-items: flex-start; gap: 20px; }
            .profile-menu-container { width: 100%; }
            .avatar-interactive-trigger { width: 100%; justify-content: space-between; }
            .profile-flyout-menu { width: 100%; }
        }
    </style>
</head>
<body>
 
    <nav class="sidebar">
        <div>
            <div class="sidebar-brand">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path></svg>
                <span>RMS Portal</span>
            </div>
            <ul class="menu-list">
                <li class="menu-item active">
                    <a href="dashboard.php">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="9"></rect><rect x="14" y="3" width="7" height="5"></rect><rect x="14" y="12" width="7" height="9"></rect><rect x="3" y="16" width="7" height="5"></rect></svg>
                        <span>Dashboard Overview</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="request_form.php">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"></path></svg>
                        <span>File New Repair</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="my_requests.php">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
                        <span>My Request Logs</span>
                    </a>
                </li>
            </ul>
        </div>
 
        <ul class="menu-list" style="flex-grow: 0;">
            <li class="menu-item">
                <a href="../auth/logout.php" style="color: #ef4444;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"></path></svg>
                    <span>Logout Account</span>
                </a>
            </li>
        </ul>
    </nav>
 
    <main class="main-content">
        
        <div class="top-navbar">
            <div class="user-meta">
                <h1>Welcome, <?php echo $display_name; ?>!</h1>
                <div class="estate-badge">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                    Central Residential Estate Complex
                </div>
            </div>
 
            <div class="profile-menu-container">
                <div class="avatar-interactive-trigger" id="profileMenuTrigger">
                    <div class="avatar-circle">
                        <?php echo strtoupper(substr($username, 0, 1)); ?>
                    </div>
                    <div class="trigger-details">
                        <div class="user-name"><?php echo $display_name; ?></div>
                        <div class="user-role"><?php echo htmlspecialchars($user_profile['role']); ?></div>
                    </div>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="color:var(--text-muted); margin-left: 4px;"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </div>
 
                <div class="profile-flyout-menu" id="profileFlyoutMenu">
                    <div class="flyout-header">
                        <p style="font-size: 11px; text-transform: uppercase; font-weight: 700; color: var(--text-muted); margin-bottom: 2px;">Logged in as</p>
                        <p class="email"><?php echo htmlspecialchars($user_profile['email']); ?></p>
                    </div>
                    <ul class="flyout-links">
                        <li>
                            <a href="profile_settings.php">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                Update Profile
                            </a>
                        </li>
                        <li>
                            <a href="change_password.php">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                                Change Password
                            </a>
                        </li>
                        <li>
                            <a href="preferences.php">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>
                                Language / Language Selection
                            </a>
                        </li>
                        <li>
                            <a href="support.php">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line><circle cx="12" cy="12" r="10"></circle></svg>
                                Helpdesk & Support
                            </a>
                        </li>
                        <li class="logout-separator">
                            <a href="../auth/logout.php">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"></path></svg>
                                Sign Out System
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
 
        <h2 class="section-title">Initiate Specialized Maintenance Dispatch</h2>
        
        <div class="services-grid">
            
            <a href="request_form.php?service=Electrician" class="service-card">
                <div class="card-image-wrapper">
                    <img src="https://images.unsplash.com/photo-1621905251189-08b45d6a269e?auto=format&fit=crop&w=400&q=80" alt="Electrician">
                </div>
                <div class="card-body-content">
                    <div class="card-text-block">
                        <span class="card-title">Electrician Services</span>
                        <span class="card-desc">Grid systems & fault checks</span>
                    </div>
                    <div class="icon-box">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                    </div>
                </div>
            </a>
 
            <a href="request_form.php?service=Plumber" class="service-card">
                <div class="card-image-wrapper">
                    <img src="https://images.unsplash.com/photo-1504328345606-18bbc8c9d7d1?auto=format&fit=crop&w=400&q=80" alt="Plumber">
                </div>
                <div class="card-body-content">
                    <div class="card-text-block">
                        <span class="card-title">Plumbing & Pipelines</span>
                        <span class="card-desc">Leak monitoring & structural pipes</span>
                    </div>
                    <div class="icon-box">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"></path></svg>
                    </div>
                </div>
            </a>
 
            <a href="request_form.php?service=Painter" class="service-card">
                <div class="card-image-wrapper">
                    <img src="https://images.unsplash.com/photo-1562259949-e8e7689d7828?auto=format&fit=crop&w=400&q=80" alt="Painter">
                </div>
                <div class="card-body-content">
                    <div class="card-text-block">
                        <span class="card-title">Wall Painting & Finishes</span>
                        <span class="card-desc">Surface preservation & coats</span>
                    </div>
                    <div class="icon-box">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z"></path></svg>
                    </div>
                </div>
            </a>
 
            <a href="request_form.php?service=Carpenter" class="service-card">
                <div class="card-image-wrapper">
                    <img src="https://images.unsplash.com/photo-1533090161767-e6ffed986c88?auto=format&fit=crop&w=400&q=80" alt="Carpenter">
                </div>
                <div class="card-body-content">
                    <div class="card-text-block">
                        <span class="card-title">Carpentry & Framework</span>
                        <span class="card-desc">Fittings, doors & interior mounts</span>
                    </div>
                    <div class="icon-box">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 10c0-4.418-4.03-8-9-8s-9 3.582-9 8c0 2.18.982 4.166 2.59 5.613l-1.01 3.491a.5.5 0 0 0 .754.546l3.435-1.92A10.155 10.155 0 0 0 11 20c4.97 0 9-3.582 9-8z"></path></svg>
                    </div>
                </div>
            </a>
 
            <a href="request_form.php?service=Appliance Repair" class="service-card">
                <div class="card-image-wrapper">
                    <img src="https://images.unsplash.com/photo-1581092160607-ee22621dd758?auto=format&fit=crop&w=400&q=80" alt="Appliance Repair">
                </div>
                <div class="card-body-content">
                    <div class="card-text-block">
                        <span class="card-title">Appliance Mechanical Fixes</span>
                        <span class="card-desc">Hardware restoration & optimization</span>
                    </div>
                    <div class="icon-box">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path></svg>
                    </div>
                </div>
            </a>
 
            <a href="request_form.php?service=Solar Technician" class="service-card">
                <div class="card-image-wrapper">
                    <img src="https://images.unsplash.com/photo-1509391366360-2e959784a276?auto=format&fit=crop&w=400&q=80" alt="Solar Tech">
                </div>
                <div class="card-body-content">
                    <div class="card-text-block">
                        <span class="card-title">Solar Tech Grid Units</span>
                        <span class="card-desc">Inverters & rooftop system arrays</span>
                    </div>
                    <div class="icon-box">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line></style></svg>
                    </div>
                </div>
            </a>
 
        </div>
    </main>
 
    <script>
        const trigger = document.getElementById('profileMenuTrigger');
        const flyout = document.getElementById('profileFlyoutMenu');
 
        trigger.addEventListener('click', function(e) {
            e.stopPropagation();
            if(flyout.style.display === 'flex') {
                flyout.style.display = 'none';
            } else {
                flyout.style.display = 'flex';
            }
        });
 
        document.addEventListener('click', function() {
            flyout.style.display = 'none';
        });
 
        flyout.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    </script>
 
</body>
</html>

