<?php
session_start();
include "../config/db.php";

$error = "";

// LOGIN LOGIC
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password.";
    } else {

        // 1. Check the main users table
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']    = $user['id'];
            $_SESSION['role']       = $user['role'];
            $_SESSION['admin_name'] = $user['first_name'];

            if ($user['role'] === "admin") {
                header("Location: ../admin/dashboard.php");
            } else {
                header("Location: ../user/dashboard.php");
            }
            exit();

        } else {
            // 2. Fallback check: technicians table
            $stmt_tech = $conn->prepare("SELECT * FROM technicians WHERE email = ?");
            $stmt_tech->bind_param("s", $email);
            $stmt_tech->execute();
            $tech = $stmt_tech->get_result()->fetch_assoc();
            $stmt_tech->close();

            if ($tech && password_verify($password, $tech['password'])) {
                $_SESSION['user_id'] = $tech['id'];
                $_SESSION['role']    = 'technician';

                header("Location: ../technician/dashboard.php");
                exit();
            } else {
                $error = "Invalid email or password.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Repairs & Maintenance System</title>

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
            background-color: white;
            color: var(--text-main);
            height: 100vh;
            width: 100vw;
            overflow: hidden;
        }

        .login-container {
            display: flex;
            width: 100vw;
            height: 100vh;
            background: white;
            overflow: hidden;
        }

        .login-form-side {
            flex: 1;
            padding: 60px 10%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            background: white;
        }

        .brand-logo {
            font-size: 20px;
            font-weight: 800;
            color: var(--primary);
            letter-spacing: -0.5px;
        }

        .form-wrapper {
            max-width: 440px;
            width: 100%;
            margin: 0 auto;
        }

        .form-header h2 {
            font-size: 34px;
            font-weight: 800;
            color: var(--dark-bg);
            margin-bottom: 10px;
            letter-spacing: -1px;
        }

        .form-header p {
            color: var(--text-muted);
            font-size: 16px;
            margin-bottom: 35px;
        }

        .input-group {
            margin-bottom: 24px;
            position: relative;
        }

        .input-group label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #475569;
            margin-bottom: 8px;
        }

        .input-group input {
            width: 100%;
            padding: 16px 18px;
            font-size: 15px;
            border: 1px solid var(--border-color);
            background: #f8fafc;
            border-radius: 12px;
            outline: none;
            color: var(--text-main);
            transition: var(--transition);
        }

        .input-group input:focus {
            background: white;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
        }

        .error-box {
            background-color: #fef2f2;
            border-left: 4px solid #ef4444;
            color: #991b1b;
            padding: 14px 18px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        button.btn-submit {
            width: 100%;
            padding: 16px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 10px 20px -5px rgba(79, 70, 229, 0.3);
            transition: var(--transition);
            margin-top: 15px;
        }

        button.btn-submit:hover {
            background: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 15px 25px -5px rgba(79, 70, 229, 0.4);
        }

        button.btn-submit:active {
            transform: translateY(0);
        }

        .form-footer {
            font-size: 14px;
            color: var(--text-muted);
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid var(--border-color);
            padding-top: 25px;
        }

        .form-footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }

        .form-footer a:hover {
            text-decoration: underline;
        }

        .login-visual-side {
            flex: 1;
            position: relative;
            background-image: url('https://images.unsplash.com/photo-1581094288338-2314dddb7ece?auto=format&fit=crop&w=1200&q=80');
            background-size: cover;
            background-position: center;
        }

        .login-visual-side::after {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.8), rgba(30, 27, 75, 0.85));
        }

        .visual-overlay-content {
            position: absolute;
            bottom: 10%;
            left: 12%;
            right: 12%;
            color: white;
            z-index: 2;
        }

        .visual-overlay-content h3 {
            font-size: 32px;
            font-weight: 800;
            margin-bottom: 16px;
            line-height: 1.3;
            background: linear-gradient(to right, #38bdf8, #818cf8, #f472b6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 0px 4px 20px rgba(129, 140, 248, 0.15);
        }

        .visual-overlay-content p {
            font-size: 16px;
            color: rgba(241, 245, 249, 0.9);
            line-height: 1.7;
        }

        @media (max-width: 960px) {
            .login-visual-side { display: none; }
            .login-form-side { padding: 40px 8%; }
            .form-wrapper { max-width: 100%; }
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="login-form-side">
        <div class="brand-logo">RMS Portal</div>
        
        <div class="form-wrapper">
            <div class="form-header">
                <h2>Welcome Back 👋</h2>
                <p>Please enter your account authorization parameters.</p>
            </div>

            <?php if ($error != "") { ?>
                <div class="error-box">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php } ?>

            <form method="POST">
                <div class="input-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="name@company.com" required autocomplete="email">
                </div>

                <div class="input-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="••••••••" required>
                </div>

                <button type="submit" class="btn-submit">Sign In to Dashboard</button>
            </form>
        </div>

        <div class="form-footer">
            <span>Maintenance System &copy; 2026</span>
            <a href="../index.php">Back to home</a>
        </div>
    </div>

    <div class="login-visual-side">
        <div class="visual-overlay-content">
            <h3>Managing Real Work, Tracking True Results.</h3>
            <p>From wall painting surface updates and plumbing structural layout leak fixes to emergency electrical fault dispatch updates—log in to handle requests fast.</p>
        </div>
    </div>
</div>

</body>
</html>