<?php
session_start();
include "../config/db.php";
include "../config/lang.php"; // Multi-language dictionary engine layer

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$service = isset($_GET['service']) ? $_GET['service'] : '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitizing parameters safely
    $title = $conn->real_escape_string($_POST['title']);
    $description = $conn->real_escape_string($_POST['description']);
    $county = $conn->real_escape_string($_POST['county']);
    $area = $conn->real_escape_string($_POST['area']);

    $location = $county . " - " . $area;
    $service_type = $conn->real_escape_string($_POST['service_type']);
    $user_id = $_SESSION['user_id'];

    // Inserts operational request markers
    $conn->query("
        INSERT INTO requests
        (title, description, location, service_type, user_id, admin_status, created_at)
        VALUES
        ('$title', '$description', '$location', '$service_type', '$user_id', 'Pending', NOW())
    ");

    header("Location: my_requests.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File New Repair - RMS Portal</title>
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

        /* Full page moving mesh background matrix container matching your dashboard style */
        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--dark-bg);
            background-image: 
                radial-gradient(at 0% 0%, rgba(79, 70, 229, 0.12) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(56, 189, 248, 0.08) 0px, transparent 50%),
                radial-gradient(at 50% 0%, rgba(16, 185, 129, 0.06) 0px, transparent 50%);
            color: var(--text-main);
            min-height: 100vh;
            width: 100vw;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
        }

        /* Floating Translucent Minimalist Dashboard Form Card Panel */
        .glass-card {
            width: 100%;
            max-width: 540px;
            background: rgba(15, 23, 42, 0.65);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--border-color);
            border-radius: 24px;
            padding: 45px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            position: relative;
        }

        .card-decorator {
            position: absolute;
            top: 25px;
            right: 30px;
            width: 8px;
            height: 8px;
            background: #818cf8;
            border-radius: 50%;
            box-shadow: 0 0 12px #818cf8;
        }

        .card-header {
            margin-bottom: 30px;
            text-align: center;
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
        }

        /* Split layout row grid for location fields */
        .grid-split {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        /* Transparent Input Groups UI blocks */
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 700;
            color: #cbd5e1;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 8px;
        }

        .form-group input, .form-group textarea, .form-group select {
            width: 100%;
            padding: 14px 16px;
            font-size: 15px;
            border: 1px solid var(--border-color);
            background: rgba(255, 255, 255, 0.03);
            border-radius: 12px;
            outline: none;
            color: white;
            transition: var(--transition);
        }

        .form-group select option {
            background: #0f172a;
            color: white;
        }

        .form-group input:focus, .form-group textarea:focus, .form-group select:focus {
            background: rgba(255, 255, 255, 0.05);
            border-color: rgba(99, 102, 241, 0.4);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15);
        }

        /* Readonly design configuration context */
        .form-group input[readonly] {
            background: rgba(255, 255, 255, 0.01);
            color: #38bdf8;
            border-color: rgba(56, 189, 248, 0.15);
            font-weight: 700;
            cursor: not-allowed;
            box-shadow: inset 0 0 10px rgba(56, 189, 248, 0.03);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 110px;
        }

        /* High-tech Action Button */
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

        .card-footer {
            margin-top: 25px;
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

        @media (max-width: 500px) {
            .grid-split { grid-template-columns: 1fr; gap: 0; }
            .glass-card { padding: 30px 20px; }
        }
    </style>
</head>
<body>

<div class="glass-card">
    <div class="card-decorator"></div>
    
    <div class="card-header">
        <h2>File Repair Request</h2>
        <p>Log your facility parameters to schedule an emergency deployment.</p>
    </div>

    <form method="POST">
        <div class="form-group">
            <label>Assigned Service Discipline</label>
            <input 
                type="text" 
                name="service_type"
                value="<?php echo htmlspecialchars($service); ?>"
                readonly
            >
        </div>

        <div class="form-group">
            <label for="title">Problem Summary / Title</label>
            <input 
                type="text" 
                id="title"
                name="title" 
                placeholder="e.g., Short circuit in living room terminal panel"
                required
            >
        </div>

        <div class="form-group">
            <label for="description">Comprehensive Description</label>
            <textarea 
                id="description"
                name="description"
                placeholder="Provide a step-by-step description of the damage or symptoms observed..."
                required
            ></textarea>
        </div>

        <div class="grid-split">
            <div class="form-group">
                <label for="county">County Location</label>
                <select id="county" name="county" required>
                    <option value="" disabled selected>Choose County...</option>
                    <option value="Nairobi">Nairobi</option>
                    <option value="Kiambu">Kiambu</option>
                    <option value="Nakuru">Nakuru</option>
                    <option value="Mombasa">Mombasa</option>
                    <option value="Kisumu">Kisumu</option>
                </select>
            </div>

            <div class="form-group">
                <label for="area">Specific Sub-Area / Estate</label>
                <input 
                    type="text"
                    id="area"
                    name="area"
                    placeholder="e.g., Ruiru Phase 2"
                    required
                >
            </div>
        </div>

        <button type="submit" class="btn-submit">Dispatch Maintenance Log</button>
    </form>
    
    <div class="card-footer">
        <a href="dashboard.php">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" style="transform: scaleX(-1);"><polyline points="9 18 15 12 9 6"></polyline></svg>
            Cancel and Return to Dashboard
        </a>
    </div>
</div>

</body>
</html>