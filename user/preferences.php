<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$success = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Session state configurations tracking translation choices
    $_SESSION['selected_lang'] = $_POST['language'];
    $success = "Language localization preferences applied successfully!";
}

$current_lang = isset($_SESSION['selected_lang']) ? $_SESSION['selected_lang'] : 'en';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Language Preferences - RMS Portal</title>
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

        /* Full page moving mesh background matrix container */
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

        /* Floating Translucent Card layout */
        .glass-card {
            width: 100%;
            max-width: 480px;
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
            background: #38bdf8;
            border-radius: 50%;
            box-shadow: 0 0 12px #38bdf8;
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

        /* Interactive Premium Custom Radio Option Cards */
        .lang-selector-option {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px;
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            margin-bottom: 16px;
            cursor: pointer;
            transition: var(--transition);
            user-select: none;
        }

        .lang-selector-option:hover {
            background: rgba(255, 255, 255, 0.04);
            border-color: rgba(255, 255, 255, 0.15);
        }

        /* High state active selection class markers */
        .lang-selector-option.active-target {
            border-color: rgba(99, 102, 241, 0.5);
            background: rgba(79, 70, 229, 0.05);
            box-shadow: inset 0 0 12px rgba(79, 70, 229, 0.1);
        }

        .meta-flag-block {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .flag-sphere {
            font-size: 24px;
            display: inline-block;
        }

        .lang-text-details .lang-title-label {
            font-size: 16px;
            font-weight: 700;
            color: white;
            display: block;
            margin-bottom: 2px;
        }

        .lang-text-details .lang-sub-label {
            font-size: 13px;
            color: var(--text-muted);
            display: block;
        }

        /* Custom high tech status checkbox circle marker */
        .custom-radio-indicator {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            border: 2px solid rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
        }

        .lang-selector-option.active-target .custom-radio-indicator {
            border-color: #6366f1;
            background: #4f46e5;
        }

        .custom-radio-indicator::after {
            content: '';
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: white;
            display: none;
        }

        .lang-selector-option.active-target .custom-radio-indicator::after {
            display: block;
        }

        /* Success system alerts display block */
        .alert-box-success {
            background-color: rgba(34, 197, 94, 0.1);
            border: 1px solid rgba(34, 197, 94, 0.2);
            color: #bbf7d0;
            padding: 14px 18px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 25px;
        }

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
        <h2>Language Selection</h2>
        <p>Set your global system translation preferences across portal monitoring blocks.</p>
    </div>

    <?php if ($success != "") { ?>
        <div class="alert-box-success">
            <span>✅</span> <?php echo $success; ?>
        </div>
    <?php } ?>

    <form method="POST">
        
        <label class="lang-option-wrapper">
            <div class="lang-selector-option <?php if($current_lang == 'en') echo 'active-target'; ?>">
                <div class="meta-flag-block">
                    <span class="flag-sphere">🇬🇧</span>
                    <div class="lang-text-details">
                        <span class="lang-title-label">English</span>
                        <span class="lang-sub-label">Default system metric view</span>
                    </div>
                </div>
                <div class="custom-radio-indicator"></div>
                <input type="radio" name="language" value="en" <?php if($current_lang == 'en') echo 'checked'; ?> style="display: none;">
            </div>
        </label>

        <label class="lang-option-wrapper">
            <div class="lang-selector-option <?php if($current_lang == 'sw') echo 'active-target'; ?>">
                <div class="meta-flag-block">
                    <span class="flag-sphere">🇰🇪</span>
                    <div class="lang-text-details">
                        <span class="lang-title-label">Kiswahili</span>
                        <span class="lang-sub-label">Ukurasa uliotafsiriwa kikamilifu</span>
                    </div>
                </div>
                <div class="custom-radio-indicator"></div>
                <input type="radio" name="language" value="sw" <?php if($current_lang == 'sw') echo 'checked'; ?> style="display: none;">
            </div>
        </label>

        <button type="submit" class="btn-submit">Apply Language Preference</button>
    </form>
    
    <div class="card-footer">
        <a href="dashboard.php">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" style="transform: scaleX(-1);"><polyline points="9 18 15 12 9 6"></polyline></svg>
            Go back to the Dashboard
        </a>
    </div>
</div>

<script>
    const setupCardSelections = () => {
        const optionContainers = document.querySelectorAll('.lang-selector-option');
        
        optionContainers.forEach(card => {
            card.addEventListener('click', () => {
                // Remove active classes from all blocks smoothly
                optionContainers.forEach(el => el.classList.remove('active-target'));
                
                // Set current item state active highlights
                card.classList.add('active-target');
            });
        });
    };

    // Instantiate listeners on ready elements loading cycle
    document.addEventListener('DOMContentLoaded', setupCardSelections);
</script>

</body>
</html>