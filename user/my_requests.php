<?php
session_start();
include "../config/db.php";
include "../config/lang.php"; 

// Protect user profile validation
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch comprehensive user request data streams
$result = $conn->query("
    SELECT * FROM requests 
    WHERE user_id='$user_id' 
    ORDER BY created_at DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('my_logs'); ?> - RMS Portal</title>

    <style>
        :root {
            --primary: #4f46e5;
            --primary-hover: #6366f1;
            --dark-bg: #090d16;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --border-color: rgba(255, 255, 255, 0.08);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            
            /* High-Contrast Glow Color System */
            --glow-pending: #f59e0b;
            --glow-progress: #3b82f6;
            --glow-completed: #10b981;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--dark-bg);
            background-image: 
                radial-gradient(at 0% 0%, rgba(79, 70, 229, 0.12) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(16, 185, 129, 0.06) 0px, transparent 50%),
                radial-gradient(at 50% 0%, rgba(4, 159, 217, 0.08) 0px, transparent 50%);
            color: var(--text-main);
            min-height: 100vh;
            padding-bottom: 60px;
        }

        /* ================= TOP UTILITY CONTROL HEADER BAR ================= */
        .top-utility-bar {
            background: rgba(15, 23, 42, 0.4);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border-color);
            padding: 16px 6%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .brand-logo-space { font-size: 18px; font-weight: 800; color: #38bdf8; letter-spacing: -0.5px; }
        .back-action-btn { 
            text-decoration: none; color: #cbd5e1; font-size: 14px; font-weight: 700; 
            display: flex; align-items: center; gap: 8px; transition: var(--transition);
        }
        .back-action-btn:hover { color: white; transform: translateX(-2px); }

        /* ================= MAIN CONTAINER WORKSPACE ================= */
        .container {
            max-width: 850px;
            width: 100%;
            margin: 50px auto;
            padding: 0 20px;
        }

        .page-header {
            margin-bottom: 35px;
            text-align: center;
        }

        .page-header h2 {
            font-size: 36px;
            font-weight: 800;
            letter-spacing: -1px;
            margin-bottom: 8px;
            background: linear-gradient(to right, #ffffff, #cbd5e1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .page-header p {
            color: var(--text-muted);
            font-size: 16px;
        }

        /* ================= GLASSMORPHIC FILTER TABS ================= */
        .filter-tabs-row {
            display: flex;
            gap: 8px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--border-color);
            padding: 6px;
            border-radius: 16px;
            margin-bottom: 35px;
            overflow-x: auto;
            backdrop-filter: blur(4px);
        }

        .filter-btn {
            padding: 12px 22px;
            font-size: 14px;
            font-weight: 700;
            border: none;
            background: transparent;
            color: var(--text-muted);
            border-radius: 12px;
            cursor: pointer;
            transition: var(--transition);
            white-space: nowrap;
        }

        .filter-btn:hover { color: white; background: rgba(255, 255, 255, 0.02); }
        .filter-btn.active {
            background: rgba(255, 255, 255, 0.08);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        /* ================= GLOWING CARD STACK COMPONENTS ================= */
        .requests-stack-container {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .request-card {
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.3);
            transition: var(--transition);
            animation: cardSlideUp 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }

        .request-card:hover {
            transform: translateY(-3px);
            border-color: rgba(255, 255, 255, 0.15);
            box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.5);
        }

        .card-top-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            margin-bottom: 14px;
        }

        .card-top-header h3 {
            font-size: 20px;
            font-weight: 700;
            color: white;
            letter-spacing: -0.4px;
        }

        .card-description {
            font-size: 15px;
            color: #cbd5e1;
            line-height: 1.7;
            margin-bottom: 24px;
        }

        /* Neon-Glow Badges CSS Framework Fixes */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: 1px solid transparent;
        }

        .status-badge::before {
            content: '';
            width: 6px;
            height: 6px;
            border-radius: 50%;
            display: inline-block;
        }

        /* Match CSS classes safely to stripped-down target classes */
        .status-badge.pending { 
            background: rgba(245, 158, 11, 0.06); color: var(--glow-pending); border-color: rgba(245, 158, 11, 0.2); 
        }
        .status-badge.pending::before { background: var(--glow-pending); box-shadow: 0 0 8px var(--glow-pending); }

        .status-badge.inprogress { 
            background: rgba(59, 130, 246, 0.06); color: var(--glow-progress); border-color: rgba(59, 130, 246, 0.2); 
        }
        .status-badge.inprogress::before { background: var(--glow-progress); box-shadow: 0 0 8px var(--glow-progress); }

        .status-badge.completed { 
            background: rgba(16, 185, 129, 0.06); color: var(--glow-completed); border-color: rgba(16, 185, 129, 0.2); 
        }
        .status-badge.completed::before { background: var(--glow-completed); box-shadow: 0 0 8px var(--glow-completed); }

        /* Meta Details Row */
        .card-meta-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 24px;
            padding-top: 18px;
            border-top: 1px solid var(--border-color);
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            font-weight: 500;
            color: var(--text-muted);
        }

        .meta-item b { color: #f8fafc; font-weight: 600; }

        /* VIBRANT ICON WRAPPERS SHAPES */
        .icon-wrapper {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .icon-location {
            background: rgba(56, 189, 248, 0.1); color: #38bdf8;
            border: 1px solid rgba(56, 189, 248, 0.2); box-shadow: 0 0 10px rgba(56, 189, 248, 0.15);
        }
        .icon-service {
            background: rgba(168, 85, 247, 0.1); color: #c084fc;
            border: 1px solid rgba(168, 85, 247, 0.2); box-shadow: 0 0 10px rgba(168, 85, 247, 0.15);
        }
        .icon-calendar {
            background: rgba(234, 179, 8, 0.1); color: #fde047;
            border: 1px solid rgba(234, 179, 8, 0.2); box-shadow: 0 0 10px rgba(234, 179, 8, 0.15);
        }

        .empty-illustration-state {
            text-align: center; padding: 60px 20px;
            background: rgba(255, 255, 255, 0.01);
            border: 1px dashed var(--border-color); border-radius: 20px;
        }
        .empty-illustration-state svg { color: var(--text-muted); opacity: 0.4; margin-bottom: 15px; }
        .empty-illustration-state h4 { font-size: 18px; font-weight: 700; color: white; margin-bottom: 4px; }
        .empty-illustration-state p { font-size: 14px; color: var(--text-muted); }

        @keyframes cardSlideUp { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
        @media (max-width: 600px) { .card-top-header { flex-direction: column; align-items: flex-start; gap: 12px; } .card-meta-grid { flex-direction: column; gap: 12px; } }
    </style>
</head>
<body>

    <header class="top-utility-bar">
        <div class="brand-logo-space">Repairs & Maintenance Management</div>
        <nav class="right-nav-actions">
            <a href="dashboard.php" class="back-action-btn">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" style="transform: rotate(180deg);"><polyline points="9 18 15 12 9 6"></polyline></svg>
                Go back to the Dashboard
            </a>
        </nav>
    </header>

    <main class="container">
        <div class="page-header">
            <h2><?php echo __('my_logs'); ?></h2>
            <p>Track your submitted facility logs and operational dispatch sequences in real time.</p>
        </div>

        <div class="filter-tabs-row">
            <button class="filter-btn active" onclick="filterLogs('All', this)">All Logs</button>
            <button class="filter-btn" onclick="filterLogs('pending', this)">Pending</button>
            <button class="filter-btn" onclick="filterLogs('inprogress', this)">In Progress</button>
            <button class="filter-btn" onclick="filterLogs('completed', this)">Completed</button>
        </div>

        <div class="requests-stack-container" id="logsContainer">

        <?php if ($result->num_rows == 0) { ?>
            <div class="empty-illustration-state">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                <h4>No Requests Submitted</h4>
                <p>When you file a facility repair issue, your logs will stack here beautifully.</p>
            </div>
        <?php } ?>

        <?php while ($row = $result->fetch_assoc()) { 
            // Fix fallback support for different db column naming setups ('status' vs 'admin_status')
            $raw_status = $row['status'] ?? ($row['admin_status'] ?? 'Pending');
            
            // Clean spaces and lowercase strings to ensure seamless CSS and JS matching
            $cleaned_status = strtolower(str_replace(' ', '', $raw_status));
        ?>
            <div class="request-card" data-status="<?php echo $cleaned_status; ?>">
                <div class="card-top-header">
                    <h3><?php echo htmlspecialchars($row['title'] ?? ($row['service_type'] . ' Repair Request')); ?></h3>
                    <span class="status-badge <?php echo $cleaned_status; ?>">
                        <?php echo htmlspecialchars($raw_status); ?>
                    </span>
                </div>

                <p class="card-description"><?php echo htmlspecialchars($row['description']); ?></p>

                <div class="card-meta-grid">
                    <div class="meta-item">
                        <div class="icon-wrapper icon-location">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                        </div>
                        <span><b>Location:</b> <?php echo htmlspecialchars($row['location']); ?></span>
                    </div>

                    <div class="meta-item">
                        <div class="icon-wrapper icon-service">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path></svg>
                        </div>
                        <span><b>Service:</b> <?php echo htmlspecialchars($row['service_type']); ?></span>
                    </div>

                    <?php if(!empty($row['created_at'])) { ?>
                    <div class="meta-item">
                        <div class="icon-wrapper icon-calendar">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        </div>
                        <span><b>Logged on:</b> <?php echo date("M d, Y", strtotime($row['created_at'])); ?></span>
                    </div>
                    <?php } ?>
                </div>
            </div>
        <?php } ?>

        </div>
    </main>

    <script>
        function filterLogs(targetStatus, buttonElement) {
            document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
            buttonElement.classList.add('active');

            const cards = document.querySelectorAll('.request-card');
            
            cards.forEach(card => {
                const cardStatus = card.getAttribute('data-status');
                
                // Complete clean lowercase comparisons for seamless performance
                if (targetStatus === 'All' || cardStatus === targetStatus) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>