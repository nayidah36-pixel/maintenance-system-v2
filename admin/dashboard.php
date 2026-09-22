<?php
session_start();
include "../config/db.php";
 
// Protect admin access guard
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "admin")
{
    header("Location: ../auth/login.php");
    exit();
}
 
$user_id = $_SESSION['user_id'];

// CRITICAL UPDATE: Extract the dynamic first name from session context
$admin_first_name = isset($_SESSION['admin_name']) ? htmlspecialchars(ucfirst(trim($_SESSION['admin_name']))) : 'Admin';
// Get just the first initial for the visual avatar badge
$avatar_initial = !empty($admin_first_name) ? substr($admin_first_name, 0, 1) : 'A';
 
// 1. Metric Aggregations (FIXED: Updated admin_status to status)
$total = $conn->query("SELECT COUNT(*) as total FROM requests")->fetch_assoc()['total'] ?? 0;
$pending = $conn->query("SELECT COUNT(*) as total FROM requests WHERE status='Pending'")->fetch_assoc()['total'] ?? 0;
$in_progress = $conn->query("SELECT COUNT(*) as total FROM requests WHERE status='In Progress'")->fetch_assoc()['total'] ?? 0;
$completed = $conn->query("SELECT COUNT(*) as total FROM requests WHERE status='Completed'")->fetch_assoc()['total'] ?? 0;
 
// 2. Filter & Search captures for Requests Panel (FIXED: Updated admin_status to status)
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'All';
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
 
$sql = "SELECT * FROM requests WHERE 1";
if ($status_filter != "All") { $sql .= " AND status='$status_filter'"; }
if (!empty($search)) { $sql .= " AND (title LIKE '%$search%' OR location LIKE '%$search%')"; }
$sql .= " ORDER BY created_at DESC";
$result_requests = $conn->query($sql);
 
// 3. Fetch Technicians and Availability Status
$result_technicians = $conn->query("SELECT * FROM technicians ORDER BY first_name ASC");
 
// 4. Fetch Technician Reports / Work Logs (FIXED: Changed work_notes to tech_notes)
$result_reports = $conn->query("
    SELECT r.id, r.title, r.location, r.status, r.tech_notes, t.first_name, t.last_name 
    FROM requests r 
    LEFT JOIN technicians t ON r.technician_id = t.id 
    WHERE r.tech_notes IS NOT NULL AND r.tech_notes != '' 
    ORDER BY r.id DESC
");

// 5. NEW: Fetch Alerts for Dashboard view (Requests where notification flag = 1)
$incoming_alerts = $conn->query("
    SELECT r.id, r.title, r.tech_notes, CONCAT(t.first_name, ' ', t.last_name) AS tech_full_name
    FROM requests r
    INNER JOIN technicians t ON r.technician_id = t.id
    WHERE r.notification = 1
    ORDER BY r.id DESC
");
 
$active_tab = (isset($_GET['status']) || isset($_GET['search'])) ? 'requests' : 'dashboard';
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - RMS Portal</title>
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
            
            --glow-blue: #3b82f6;
            --glow-orange: #f59e0b;
            --glow-purple: #a855f7;
            --glow-green: #10b981;
        }
 
        * { margin: 0; padding: 0; box-sizing: border-box; }
 
        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--dark-bg);
            background-image: 
                radial-gradient(at 0% 0%, rgba(79, 70, 229, 0.1) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(16, 185, 129, 0.04) 0px, transparent 50%);
            color: var(--text-main);
            display: flex;
            min-height: 100vh;
        }
 
        /* SIDEBAR INTERFACE */
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
            padding: 14px 16px; font-weight: 600; font-size: 15px; border-radius: 12px; cursor: pointer; transition: var(--transition);
        }
        .menu-link:hover, .menu-link.active { background: rgba(255, 255, 255, 0.05); color: white; }
        .menu-link.active { background: var(--primary); }
        .sidebar-footer a { display: block; text-align: center; text-decoration: none; padding: 12px; border-radius: 10px; color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); background: rgba(239, 68, 68, 0.02); font-weight: 700; }
        .sidebar-footer a:hover { background: #ef4444; color: white; }
 
        /* CONTENT ENGINE CONTAINER */
        .main { margin-left: 280px; width: 100%; padding: 40px 50px; }
        .topbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; }
        .welcome h1 { font-size: 28px; font-weight: 800; letter-spacing: -0.5px; margin-bottom: 4px; }
        .welcome p { color: var(--text-muted); font-size: 15px; }
 
        /* SPA VIEW wrappers */
        .view-panel { display: none; animation: viewFadeIn 0.3s ease; }
        .view-panel.active-panel { display: block; }
 
        /* PROFILE INTERFACES */
        .profile-menu-container { position: relative; }
        .profile-trigger {
            display: flex; align-items: center; gap: 12px; background: rgba(255, 255, 255, 0.03);
            padding: 8px 18px; border-radius: 50px; border: 1px solid var(--border-color); cursor: pointer;
        }
        .avatar-circle { width: 34px; height: 34px; background: linear-gradient(135deg, #6366f1, #4f46e5); color: white; font-weight: 700; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
        .profile-flyout {
            position: absolute; right: 0; top: calc(100% + 12px); width: 220px; background: #0f172a;
            border: 1px solid var(--border-color); border-radius: 14px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.5);
            display: none; flex-direction: column; overflow: hidden; z-index: 150;
        }
        .profile-flyout a { display: flex; align-items: center; gap: 10px; padding: 14px 18px; color: #cbd5e1; text-decoration: none; font-size: 14px; font-weight: 600; }
        .profile-flyout a:hover { background: rgba(255, 255, 255, 0.04); color: white; }
 
        /* METRIC CARDS */
        .cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 24px; margin-bottom: 40px; }
        .card { background: var(--card-surface); border: 1px solid var(--border-color); padding: 25px; border-radius: 20px; transition: var(--transition); }
        .card h3 { font-size: 14px; font-weight: 700; text-transform: uppercase; color: var(--text-muted); margin-bottom: 12px; }
        .card p { font-size: 36px; font-weight: 800; color: white; }
        .card.blue { border-left: 4px solid var(--glow-blue); }
        .card.orange { border-left: 4px solid var(--glow-orange); }
        .card.purple { border-left: 4px solid var(--glow-purple); }
        .card.green { border-left: 4px solid var(--glow-green); }
 
        /* LIVE ALERT BOX STYLING */
        .alert-card-container {
            background: rgba(245, 158, 11, 0.06); border: 1px solid rgba(245, 158, 11, 0.15);
            padding: 24px; border-radius: 20px; margin-bottom: 35px; border-left: 5px solid var(--glow-orange);
        }
        .alert-card-container h2 { font-size: 18px; color: #fbbf24; margin-bottom: 15px; }
        .alert-row { display: flex; justify-content: space-between; align-items: center; background: rgba(0,0,0,0.2); padding: 15px 20px; border-radius: 12px; margin-bottom: 10px; }
        .alert-text { font-size: 14px; color: #e2e8f0; }
        .alert-text em { color: var(--glow-orange); }

        /* FILTER LAYOUT */
        .filters { background: rgba(255, 255, 255, 0.02); border: 1px solid var(--border-color); padding: 16px 20px; border-radius: 16px; margin-bottom: 30px; display: flex; gap: 12px; }
        .filters input, .filters select { background: rgba(0, 0, 0, 0.2); border: 1px solid var(--border-color); padding: 12px 16px; color: white; border-radius: 10px; outline: none; }
        .filters input { flex-grow: 1; }
        .filters button { padding: 12px 24px; border: none; border-radius: 10px; background: var(--primary); color: white; font-weight: 700; cursor: pointer; }
 
        /* REQUEST & RECORD OBJECTS */
        .request-item-card { background: var(--card-surface); border: 1px solid var(--border-color); padding: 30px; border-radius: 20px; margin-bottom: 20px; }
        .request-item-card h3 { font-size: 20px; font-weight: 700; margin-bottom: 6px; }
        .request-item-card p.desc { color: #cbd5e1; font-size: 15px; margin-bottom: 20px; line-height: 1.6; }
        .meta-strip { display: flex; flex-wrap: wrap; gap: 24px; padding-bottom: 18px; margin-bottom: 18px; border-bottom: 1px solid var(--border-color); }
        .meta-strip :nth-child(n) { font-size: 13px; font-weight: 600; color: var(--text-muted); }
        .meta-strip :nth-child(n) b { color: #f8fafc; }
        
        .status { display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; }
        .status::before { content: ''; width: 6px; height: 6px; border-radius: 50%; display: inline-block; }
        .status.Pending, .status.pending { background: rgba(245, 158, 11, 0.08); color: var(--glow-orange); }
        .status.Pending::before { background: var(--glow-orange); }
        .status.InProgress, .status.inprogress, .status.In\ Progress { background: rgba(59, 130, 246, 0.08); color: var(--glow-blue); } 
        .status.InProgress::before, .status.In\ Progress::before { background: var(--glow-blue); }
        .status.Completed, .status.completed { background: rgba(16, 185, 129, 0.08); color: var(--glow-green); } 
        .status.Completed::before { background: var(--glow-green); }
 
        .actions { display: flex; gap: 10px; }
        .actions .btn { text-decoration: none; padding: 10px 18px; font-size: 13px; font-weight: 700; border-radius: 10px; color: white; background: rgba(255,255,255,0.04); border: 1px solid var(--border-color); transition: var(--transition); }
        .actions .btn:hover { background: white; color: var(--dark-bg); }
        .actions .btn-primary { background: var(--primary); border-color: transparent; }
        .actions .btn-primary:hover { background: var(--primary-hover); color: white; }
 
        /* TECHNICIANS CONTAINER GRID Layout */
        .tech-directory-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 24px; }
        .tech-profile-card { background: var(--card-surface); border: 1px solid var(--border-color); border-radius: 20px; padding: 25px; display: flex; flex-direction: column; gap: 16px; }
        .tech-header { display: flex; align-items: center; gap: 14px; }
        .tech-avatar { width: 44px; height: 44px; background: rgba(255,255,255,0.05); border: 1px solid var(--border-color); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px; }
        .tech-name-meta h4 { font-size: 16px; font-weight: 700; color: white; }
        .tech-name-meta p { font-size: 13px; color: var(--text-muted); }
        
        .status-pill { display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 700; padding: 6px 14px; border-radius: 30px; width: fit-content; text-transform: uppercase; }
        .status-pill::before { content: ''; width: 6px; height: 6px; border-radius: 50%; }
        .status-pill.Available, .status-pill.available { background: rgba(16, 185, 129, 0.06); color: var(--glow-green); border: 1px solid rgba(16, 185, 129, 0.15); }
        .status-pill.Available::before { background: var(--glow-green); }
        .status-pill.Busy, .status-pill.busy { background: rgba(239, 68, 68, 0.06); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.15); } 
        .status-pill.Busy::before { background: #ef4444; }
 
        /* REPORT BOX ARCHITECTURE */
        .report-log-card { background: rgba(30, 41, 59, 0.4); border: 1px solid var(--border-color); padding: 24px; border-radius: 16px; margin-bottom: 16px; }
        .report-header { display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 14px; color: var(--text-muted); }
        .report-body { background: rgba(0,0,0,0.2); padding: 16px; border-radius: 10px; font-size: 14px; line-height: 1.5; color: #e2e8f0; border-left: 3px solid var(--primary); }
 
        @keyframes viewFadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>
 
    <nav class="sidebar">
        <div>
            <a href="admin_dashboard.php" class="sidebar-brand">RMS <span>Admin</span></a>
            <div class="menu">
                <div class="menu-link <?php if($active_tab == 'dashboard') echo 'active'; ?>" onclick="switchTab('dashboard', this)">Dashboard</div>
                <div class="menu-link <?php if($active_tab == 'requests') echo 'active'; ?>" onclick="switchTab('requests', this)">Requests</div>
                <div class="menu-link" onclick="switchTab('technicians', this)">Technicians</div>
                <div class="menu-link" onclick="switchTab('reports', this)">Reports</div>
                <div class="menu-link" onclick="window.location.href='profile_settings.php'">Settings</div>
            </div>
        </div>
        <div class="sidebar-footer" style="width:100%;"><a href="../auth/logout.php">Logout</a></div>
    </nav>
 
    <main class="main">
        <div class="topbar">
            <div class="welcome">
                <h1 id="panelTitle">Welcome <?php echo $admin_first_name; ?> 👋</h1>
                <p id="panelSubtitle">Repairs & Maintenance Management Controls Hub</p>
            </div>
            <div class="profile-menu-container">
                <div class="profile-trigger" id="profileTrigger">
                    <div class="avatar-circle"><?php echo $avatar_initial; ?></div>
                    <span style="font-size: 13px; font-weight: 700;"><?php echo $admin_first_name; ?></span>
                </div>
                <div class="profile-flyout" id="profileFlyout">
                    <a href="profile_settings.php">Update Profile</a>
                    <a href="../auth/logout.php" style="color:#ef4444; border-top:1px solid var(--border-color);">Logout</a>
                </div>
            </div>
        </div>
 
        <div id="view-dashboard" class="view-panel <?php if($active_tab == 'dashboard') echo 'active-panel'; ?>">
             
            <?php if ($incoming_alerts && $incoming_alerts->num_rows > 0) { ?>
                <div class="alert-card-container">
                    <h2>⚠️ Unread Technician Status Updates</h2>
                    <?php while($alert = $incoming_alerts->fetch_assoc()) { ?>
                        <div class="alert-row">
                            <div class="alert-text">
                                <strong><?php echo htmlspecialchars($alert['tech_full_name']); ?></strong> submitted an update for task 
                                <b>"<?php echo htmlspecialchars($alert['title']); ?>"</b>: 
                                <em>"<?php echo htmlspecialchars($alert['tech_notes']); ?>"</em>
                            </div>
                            <div class="actions">
                                <a class="btn btn-primary" href="update_status.php?id=<?php echo $alert['id']; ?>">Sync Status</a>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>

            <div class="cards">
                <div class="card blue"><h3>Total Requests</h3><p><?php echo $total; ?></p></div>
                <div class="card orange"><h3>Pending</h3><p><?php echo $pending; ?></p></div>
                <div class="card purple"><h3>In Progress</h3><p><?php echo $in_progress; ?></p></div>
                <div class="card green"><h3>Completed</h3><p><?php echo $completed; ?></p></div>
            </div>
        </div>
 
        <div id="view-requests" class="view-panel <?php if($active_tab == 'requests') echo 'active-panel'; ?>">
            <form method="GET" class="filters">
                <input type="text" name="search" value="<?php echo htmlspecialchars(stripslashes($search)); ?>" placeholder="Search description logs...">
                <select name="status">
                    <option value="All" <?php if($status_filter == 'All') echo 'selected'; ?>>All Statuses</option>
                    <option value="Pending" <?php if($status_filter == 'Pending') echo 'selected'; ?>>Pending</option>
                    <option value="In Progress" <?php if($status_filter == 'In Progress') echo 'selected'; ?>>In Progress</option>
                    <option value="Completed" <?php if($status_filter == 'Completed') echo 'selected'; ?>>Completed</option>
                </select>
                <button type="submit">Filter</button>
            </form>
 
            <?php while ($row = $result_requests->fetch_assoc()) { 
                $tech_status = $row['technician_status'] ?? 'Pending';
                $adm_status = $row['status'] ?? 'Pending'; 
            ?>
                <div class="request-item-card">
                    <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                    <p class="desc"><?php echo htmlspecialchars($row['description']); ?></p>
                    <div class="meta-strip">
                        <div><b>Location:</b> <?php echo htmlspecialchars($row['location']); ?></div>
                        <div><b>Service:</b> <?php echo htmlspecialchars($row['service_type']); ?></div>
                        <div><b>Tech State:</b> <span class="status <?php echo str_replace(' ','',$tech_status); ?>"><?php echo htmlspecialchars($tech_status); ?></span></div>
                        <div><b>Admin Core:</b> <span class="status <?php echo str_replace(' ','',$adm_status); ?>"><?php echo htmlspecialchars($adm_status); ?></span></div>
                    </div>
                    <div class="actions">
                        <a class="btn" href="update_status.php?id=<?php echo $row['id']; ?>">Update Status</a>
                        <a class="btn btn-primary" href="assign_technician.php?id=<?php echo $row['id']; ?>">Assign Tech</a>
                        <a class="btn" href="approve_request.php?id=<?php echo $row['id']; ?>" style="background: rgba(16, 185, 129, 0.2); color: #10b981; border: none;">Approve</a>
                    </div>
                </div>
            <?php } ?>
        </div>
 
        <div id="view-technicians" class="view-panel">
            <div class="tech-directory-grid">
                <?php if($result_technicians && $result_technicians->num_rows > 0) {
                    while($tech = $result_technicians->fetch_assoc()) { 
                        $full_name = $tech['first_name'] . ' ' . $tech['last_name'];
                ?>
                        <div class="tech-profile-card">
                            <div class="tech-header">
                                <div class="tech-avatar">👷‍♂️</div>
                                <div class="tech-name-meta">
                                    <h4><?php echo htmlspecialchars($full_name); ?></h4>
                                    <p><?php echo htmlspecialchars($tech['email']); ?></p>
                                </div>
                            </div>
                            <span class="status-pill <?php echo htmlspecialchars($tech['availability_status']); ?>">
                                <?php echo htmlspecialchars($tech['availability_status']); ?>
                            </span>
                        </div>
                <?php } } else { echo "<p style='color:var(--text-muted);'>No technicians logged inside database entries yet.</p>"; } ?>
            </div>
        </div>
 
        <div id="view-reports" class="view-panel">
            <?php if($result_reports && $result_reports->num_rows > 0) {
                while($report = $result_reports->fetch_assoc()) { 
                    $tech_reporter = !empty($report['first_name']) ? ($report['first_name'] . ' ' . $report['last_name']) : 'Assigned Tech';
            ?>
                    <div class="report-log-card">
                        <div class="report-header">
                            <div><strong>Task:</strong> <?php echo htmlspecialchars($report['title']); ?> (<?php echo htmlspecialchars($report['location']); ?>)</div>
                            <div>Filed By: <strong><?php echo htmlspecialchars($tech_reporter); ?></strong></div>
                        </div>
                        <div class="report-body">
                            <?php echo nl2br(htmlspecialchars($report['tech_notes'])); ?>
                        </div>
                        <div class="actions" style="margin-top: 15px;">
                            <a class="btn btn-primary" href="update_status.php?id=<?php echo $report['id']; ?>" style="font-size: 12px; padding: 6px 14px;">Review & Align Status</a>
                        </div>
                    </div>
            <?php } } else { echo "<p style='color:var(--text-muted);'>No status reports or completion notes submitted yet.</p>"; } ?>
        </div>
    </main>
 
    <script>
        // UPDATED: JavaScript now dynamically passes the PHP variable context safely down into tabs
        const adminFirstName = "<?php echo $admin_first_name; ?>";

        function switchTab(viewId, element) {
            document.querySelectorAll('.menu-link').forEach(link => link.classList.remove('active'));
            document.querySelectorAll('.view-panel').forEach(panel => panel.classList.remove('active-panel'));
            
            element.classList.add('active');
            document.getElementById('view-' + viewId).classList.add('active-panel');
 
            const title = document.getElementById('panelTitle');
            const sub = document.getElementById('panelSubtitle');
            
            // FIXED: Replaced "Welcome Admin" with the dynamic name variable string layout link
            if(viewId === 'dashboard') { title.innerText = "Welcome " + adminFirstName + " 👋"; sub.innerText = "Repairs & Maintenance Management Controls Hub"; }
            if(viewId === 'requests') { title.innerText = "Facility Support Logs"; sub.innerText = "Manage customer problem tickets and work routes"; }
            if(viewId === 'technicians') { title.innerText = "Technician Grid Operations"; sub.innerText = "Live status logs for workforce availability"; }
            if(viewId === 'reports') { title.innerText = "Technician Progress Reports"; sub.innerText = "Review work status documentation filed by field workers"; }
        }
 
        const trigger = document.getElementById('profileTrigger');
        const flyout = document.getElementById('profileFlyout');
        trigger.addEventListener('click', (e) => { e.stopPropagation(); flyout.style.display = flyout.style.display === 'flex' ? 'none' : 'flex'; });
        document.addEventListener('click', () => flyout.style.display = 'none');
    </script>
</body>
</html>