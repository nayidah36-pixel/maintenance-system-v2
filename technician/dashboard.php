<?php
session_start();
include "../config/db.php";

// protect technician access - perfectly matches our unified multi-table login sessions
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "technician") {
    header("Location: ../auth/login.php"); 
    exit();
}

$tech_id = $_SESSION['user_id'];

// 1. FETCH NAME: Pulls first and second names from your technicians table layout
$tech_profile = $conn->query("SELECT * FROM technicians WHERE id='$tech_id'")->fetch_assoc();

// Catch common formatting spellings for separated name columns safely
$first = $tech_profile['first_name'] ?? $tech_profile['firstname'] ?? '';

// Format the name beautifully with standard sentence capitalization
if (!empty($first)) {
    $tech_name = htmlspecialchars(ucfirst(trim($first)));
} else {
    $tech_name = 'Technician';
}

// 2. METRICS: Fetch live metrics for this technician's workspace summary
$total_assigned = $conn->query("SELECT COUNT(*) as total FROM requests WHERE technician_id='$tech_id'")->fetch_assoc()['total'] ?? 0;
$pending_assigned = $conn->query("SELECT COUNT(*) as total FROM requests WHERE technician_id='$tech_id' AND technician_status='Pending'")->fetch_assoc()['total'] ?? 0;
$progress_assigned = $conn->query("SELECT COUNT(*) as total FROM requests WHERE technician_id='$tech_id' AND technician_status='In Progress'")->fetch_assoc()['total'] ?? 0;
$completed_assigned = $conn->query("SELECT COUNT(*) as total FROM requests WHERE technician_id='$tech_id' AND technician_status='Completed'")->fetch_assoc()['total'] ?? 0;

// 3. DATA QUEUE: Fetch assigned requests linked directly to this technician's unique table ID
$result = $conn->query("
    SELECT * FROM requests 
    WHERE technician_id='$tech_id'
    ORDER BY created_at DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Technician Operations Portal - RMS</title>
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

        /* PREMIUM SIDEBAR NAVIGATION INTERFACE */
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
        .menu-link.active { background: var(--primary); color: white; }
        .sidebar-footer a { display: block; text-align: center; text-decoration: none; padding: 12px; border-radius: 10px; color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); background: rgba(239, 68, 68, 0.02); font-weight: 700; }
        .sidebar-footer a:hover { background: #ef4444; color: white; }

        /* MAIN APP SHELL CONTENT */
        .main { margin-left: 280px; width: 100%; padding: 40px 50px; }
        .topbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; }
        .welcome h1 { font-size: 28px; font-weight: 800; letter-spacing: -0.5px; margin-bottom: 4px; }
        .welcome p { color: var(--text-muted); font-size: 15px; }

        /* VIEW ARCHITECTURE WRAPPERS */
        .view-panel { display: none; animation: viewFadeIn 0.3s ease; }
        .view-panel.active-panel { display: block; }

        /* PROFILE FLYOUT DESIGN COMPONENT */
        .profile-menu-container { position: relative; }
        .profile-trigger {
            display: flex; align-items: center; gap: 12px; background: rgba(255, 255, 255, 0.03);
            padding: 8px 18px; border-radius: 50px; border: 1px solid var(--border-color); cursor: pointer;
        }
        .avatar-circle { width: 34px; height: 34px; background: linear-gradient(135deg, #10b981, #059669); color: white; font-weight: 700; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
        
        .profile-flyout {
            position: absolute; right: 0; top: calc(100% + 12px); width: 220px; background: #0f172a;
            border: 1px solid var(--border-color); border-radius: 14px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.5);
            display: none; flex-direction: column; overflow: hidden; z-index: 150;
        }
        .profile-flyout a { display: flex; align-items: center; gap: 10px; padding: 14px 18px; color: #cbd5e1; text-decoration: none; font-size: 14px; font-weight: 600; }
        .profile-flyout a:hover { background: rgba(255, 255, 255, 0.04); color: white; }

        /* OPERATIONS METRIC METERS */
        .cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 24px; margin-bottom: 40px; }
        .card { background: var(--card-surface); border: 1px solid var(--border-color); padding: 25px; border-radius: 20px; transition: var(--transition); }
        .card h3 { font-size: 14px; font-weight: 700; text-transform: uppercase; color: var(--text-muted); margin-bottom: 12px; }
        .card p { font-size: 36px; font-weight: 800; color: white; }
        .card.blue { border-left: 4px solid var(--glow-blue); }
        .card.orange { border-left: 4px solid var(--glow-orange); }
        .card.purple { border-left: 4px solid var(--glow-purple); }
        .card.green { border-left: 4px solid var(--glow-green); }

        /* LIVE JOB RECORD OVERLAYS */
        .request-item-card { background: var(--card-surface); border: 1px solid var(--border-color); padding: 30px; border-radius: 20px; margin-bottom: 25px; }
        .request-item-card h3 { font-size: 20px; font-weight: 700; margin-bottom: 6px; color: white; }
        .request-item-card p.desc { color: #cbd5e1; font-size: 15px; margin-bottom: 20px; line-height: 1.6; }
        
        .meta-strip { display: flex; flex-wrap: wrap; gap: 24px; padding-bottom: 18px; margin-bottom: 18px; border-bottom: 1px solid var(--border-color); }
        .meta-block { font-size: 13px; font-weight: 600; color: var(--text-muted); }
        .meta-block b { color: #f8fafc; }
        
        .status { display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; }
        .status::before { content: ''; width: 6px; height: 6px; border-radius: 50%; display: inline-block; }
        .status.Pending, .status.pending { background: rgba(245, 158, 11, 0.08); color: var(--glow-orange); }
        .status.Pending::before { background: var(--glow-orange); }
        .status.InProgress, .status.inprogress, .status.In\ Progress { background: rgba(59, 130, 246, 0.08); color: var(--glow-blue); } 
        .status.InProgress::before, .status.In\ Progress::before { background: var(--glow-blue); }
        .status.Completed, .status.completed { background: rgba(16, 185, 129, 0.08); color: var(--glow-green); } 
        .status.Completed::before { background: var(--glow-green); }

        /* TECHNICAL FORMS INTERFACE CSS */
        label { display: block; margin-top: 18px; font-weight: 700; font-size: 13px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; }
        select, textarea { background: rgba(0, 0, 0, 0.3); border: 1px solid var(--border-color); padding: 14px 16px; color: white; border-radius: 10px; outline: none; width: 100%; margin-top: 6px; display: block; }
        textarea { height: 90px; font-family: inherit; resize: vertical; line-height: 1.5; }
        
        button { padding: 14px 24px; border: none; border-radius: 10px; background: var(--primary); color: white; font-weight: 700; cursor: pointer; margin-top: 20px; width: 100%; font-size: 14px; letter-spacing: 0.5px; transition: var(--transition); }
        button:hover { background: var(--primary-hover); transform: translateY(-1px); }

        .empty-slate { text-align: center; color: var(--text-muted); padding: 60px 20px; background: rgba(255,255,255,0.01); border: 1px dashed var(--border-color); border-radius: 20px; font-size: 16px; }

        @keyframes viewFadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>

    <nav class="sidebar">
        <div>
            <a href="dashboard.php" class="sidebar-brand">RMS <span>Tech</span></a>
            <div class="menu">
                <div class="menu-link active" id="link-overview" onclick="switchView('overview')">Overview</div>
                <div class="menu-link" id="link-workload" onclick="switchView('workload')">My Workload Queue</div>
                <div class="menu-link" onclick="window.location.href='profile_settings.php'">Settings</div>
            </div>
        </div>
        <div class="sidebar-footer" style="width:100%;">
            <a href="../auth/logout.php">Logout</a>
        </div>
    </nav>

    <main class="main">
        <div class="topbar">
            <div class="welcome">
                <h1 id="panelTitle">Welcome, <?php echo $tech_name; ?> 👋</h1>
                <p id="panelSubtitle">Field Operations Performance Counters & Diagnostics</p>
            </div>
            <div class="profile-menu-container">
                <div class="profile-trigger" id="profileTrigger">
                    <div class="avatar-circle"><?php echo substr($tech_name, 0, 1); ?></div>
                    <span style="font-size: 13px; font-weight: 700;"><?php echo $tech_name; ?>'s Workspace</span>
                </div>
                <div class="profile-flyout" id="profileFlyout">
                    <a href="profile_settings.php">Update Profile</a>
                    <a href="../auth/logout.php" style="color:#ef4444; border-top:1px solid var(--border-color);">Logout</a>
                </div>
            </div>
        </div>

        <?php if (isset($_GET['success'])) { ?>
            <div id="successToast" style="background: rgba(16, 185, 129, 0.12); border: 1px solid #10b981; color: #10b981; padding: 16px 20px; border-radius: 12px; font-weight: 700; margin-bottom: 25px; font-size: 14px; display: flex; align-items: center; gap: 10px; animation: viewFadeIn 0.3s ease;">
                <span style="font-size: 18px;">✅</span> Submitted successfully! Your workload view has been synchronized.
            </div>
            <script>
                setTimeout(() => {
                    const toast = document.getElementById('successToast');
                    if(toast) {
                        toast.style.transition = 'opacity 0.5s ease';
                        toast.style.opacity = '0';
                        setTimeout(() => toast.remove(), 500);
                    }
                }, 4000);
            </script>
        <?php } ?>

        <div id="view-overview" class="view-panel active-panel">
            <div class="cards">
                <div class="card blue"><h3>Total Assigned</h3><p><?php echo $total_assigned; ?></p></div>
                <div class="card orange"><h3>Pending Review</h3><p><?php echo $pending_assigned; ?></p></div>
                <div class="card purple"><h3>In Progress</h3><p><?php echo $progress_assigned; ?></p></div>
                <div class="card green"><h3>Completed Tasks</h3><p><?php echo $completed_assigned; ?></p></div>
            </div>
        </div>

        <div id="view-workload" class="view-panel">
            <?php if ($result->num_rows == 0) { ?>
                <div class="empty-slate">
                    No active service tickets mapped to your operator ID yet. Your workspace is clear!
                </div>
            <?php } ?>

            <?php while ($row = $result->fetch_assoc()) { ?>
                <div class="request-item-card">
                    <h3>Request #<?php echo $row['id']; ?>: <?php echo htmlspecialchars($row['title']); ?></h3>
                    <p class="desc"><?php echo htmlspecialchars($row['description']); ?></p>
                    
                    <div class="meta-strip">
                        <div class="meta-block"><b>Service Line:</b> <?php echo htmlspecialchars($row['service_type']); ?></div>
                        <div class="meta-block"><b>Assigned Location:</b> <?php echo htmlspecialchars($row['location']); ?></div>
                        <div class="meta-block"><b>Current Track:</b> 
                            <span class="status <?php echo str_replace(' ', '', $row['technician_status']); ?>">
                                <?php echo htmlspecialchars($row['technician_status'] ?? 'Pending'); ?>
                            </span>
                        </div>
                    </div>

                    <form method="POST" action="update_status.php">
                        <input type="hidden" name="request_id" value="<?php echo $row['id']; ?>">

                        <label for="status">Update Job State:</label>
                        <select name="status" required>
                            <option value="In Progress" <?php if($row['technician_status'] == 'In Progress') echo 'selected'; ?>>In Progress</option>
                            <option value="Completed" <?php if($row['technician_status'] == 'Completed') echo 'selected'; ?>>Completed</option>
                        </select>

                        <label for="tech_notes">Progress Report / Completion Notes:</label>
                        <textarea name="tech_notes" placeholder="Specify structural components updated, repairs completed, or metrics addressed..." required><?php echo htmlspecialchars($row['tech_notes'] ?? ''); ?></textarea>

                        <button type="submit">Submit Progress Update</button>
                    </form>
                </div>
            <?php } ?>
        </div>
    </main>

    <script>
        // Store technical name variables within local engine scope
        const technicianRealName = "<?php echo $tech_name; ?>";

        // SPA Tab switching controller engine 
        function switchView(viewId) {
            document.querySelectorAll('.menu-link').forEach(link => link.classList.remove('active'));
            document.querySelectorAll('.view-panel').forEach(panel => panel.classList.remove('active-panel'));
            
            document.getElementById('link-' + viewId).classList.add('active');
            document.getElementById('view-' + viewId).classList.add('active-panel');

            const title = document.getElementById('panelTitle');
            const sub = document.getElementById('panelSubtitle');
            
            if(viewId === 'overview') {
                title.innerText = "Welcome, " + technicianRealName + " 👋";
                sub.innerText = "Field Operations Performance Counters & Diagnostics";
            } else if(viewId === 'workload') {
                title.innerText = "My Workload Queue";
                sub.innerText = "Manage active maintenance requests and report diagnostics";
            }
        }

        // URL Hook Interceptor - If success is present, lock user view directly on workload queue
        window.addEventListener('DOMContentLoaded', () => {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('success')) {
                switchView('workload');
            }
        });

        // Profile flying popover overlay toggle listeners
        const trigger = document.getElementById('profileTrigger');
        const flyout = document.getElementById('profileFlyout');
        trigger.addEventListener('click', (e) => {
            e.stopPropagation();
            flyout.style.display = flyout.style.display === 'flex' ? 'none' : 'flex';
        });
        document.addEventListener('click', () => flyout.style.display = 'none');
    </script>
</body>
</html>