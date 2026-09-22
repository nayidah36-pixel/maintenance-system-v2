<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Safely attempt to load database connection
if (file_exists(__DIR__ . '/db.php')) {
    require_once __DIR__ . '/db.php';
} elseif (file_exists(__DIR__ . '/config/db.php')) {
    require_once __DIR__ . '/config/db.php';
} elseif (file_exists(__DIR__ . '/includes/db.php')) {
    require_once __DIR__ . '/includes/db.php';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RMS System - Repairs & Maintenance Management System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f8fafc;
            color: #1e293b;
        }

        /* Navigation */
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 18px 5%;
            background-color: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .logo {
            font-size: 1.6rem;
            font-weight: 800;
            color: #2563eb;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 24px;
        }

        .nav-links a {
            text-decoration: none;
            color: #475569;
            font-weight: 600;
            font-size: 0.95rem;
        }

        .nav-links a:hover {
            color: #2563eb;
        }

        .btn-register {
            background-color: #4f46e5;
            color: #ffffff !important;
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 600;
        }

        /* Hero Banner with Dynamic Rotating Background */
        .hero {
            position: relative;
            height: 520px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            color: #ffffff;
            text-align: center;
            background-image: url('https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=1920&q=80');
            background-size: cover;
            background-position: center;
        }

        .hero-slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-size: cover;
            background-position: center;
            opacity: 0;
            transition: opacity 1.2s ease-in-out;
            z-index: 1;
        }

        .hero-slide.active {
            opacity: 1;
        }

        .hero-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 23, 42, 0.72);
            z-index: 2;
        }

        .hero-content {
            position: relative;
            z-index: 3;
            max-width: 850px;
            padding: 0 20px;
        }

        .hero h1 {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 16px;
        }

        .hero p {
            font-size: 1.25rem;
            color: #cbd5e1;
            margin: 0 auto 36px auto;
            line-height: 1.6;
        }

        .hero-buttons {
            display: flex;
            justify-content: center;
            gap: 16px;
        }

        .btn-primary {
            background-color: #6366f1;
            color: #ffffff;
            padding: 14px 28px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 700;
        }

        .btn-secondary {
            background-color: #2563eb;
            color: #ffffff;
            padding: 14px 28px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 700;
        }

        /* Stats Bar */
        .stats-bar {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            background-color: #ffffff;
            padding: 40px 5%;
            text-align: center;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        .stat-item h2 {
            font-size: 2.2rem;
            color: #1e3a8a;
            font-weight: 800;
        }

        .stat-item p {
            font-size: 0.85rem;
            color: #64748b;
            font-weight: 700;
            text-transform: uppercase;
            margin-top: 4px;
        }

        /* About Section */
        .about-section {
            padding: 80px 5%;
            background-color: #f8fafc;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            align-items: center;
        }

        .about-content h2 {
            font-size: 2.2rem;
            color: #0f172a;
            margin-bottom: 20px;
            font-weight: 800;
        }

        .about-content p {
            color: #475569;
            line-height: 1.7;
            margin-bottom: 16px;
            font-size: 1.05rem;
        }

        .about-image img {
            width: 100%;
            height: 380px;
            object-fit: cover;
            border-radius: 12px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        /* Recent Projects Section - 6 Cards Grid */
        .projects-section {
            padding: 80px 5%;
            background-color: #ffffff;
        }

        .section-header {
            text-align: center;
            margin-bottom: 50px;
        }

        .section-header h2 {
            font-size: 2.2rem;
            color: #0f172a;
            font-weight: 800;
        }

        .section-header p {
            color: #64748b;
            margin-top: 8px;
            font-size: 1.05rem;
        }

        .projects-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 30px;
        }

        .project-card {
            background-color: #f8fafc;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }

        .project-image {
            height: 220px;
            width: 100%;
            object-fit: cover;
        }

        .project-info {
            padding: 24px;
        }

        .project-category {
            font-size: 0.75rem;
            font-weight: 800;
            color: #2563eb;
            text-transform: uppercase;
            margin-bottom: 8px;
            display: block;
        }

        .project-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 12px;
        }

        .project-desc {
            font-size: 0.95rem;
            color: #64748b;
            line-height: 1.5;
        }

        /* Footer */
        .footer {
            background-color: #0f172a;
            color: #94a3b8;
            padding: 60px 5% 30px 5%;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            gap: 40px;
            margin-bottom: 40px;
        }

        .footer-brand h3 {
            color: #ffffff;
            font-size: 1.5rem;
            margin-bottom: 12px;
        }

        .footer-brand p {
            line-height: 1.6;
            max-width: 320px;
        }

        .footer-links h4 {
            color: #ffffff;
            margin-bottom: 16px;
        }

        .footer-links ul {
            list-style: none;
        }

        .footer-links ul li {
            margin-bottom: 10px;
        }

        .footer-links ul li a {
            color: #94a3b8;
            text-decoration: none;
        }

        .footer-bottom {
            text-align: center;
            border-top: 1px solid #1e293b;
            padding-top: 24px;
            font-size: 0.85rem;
        }

        @media (max-width: 768px) {
            .about-section {
                grid-template-columns: 1fr;
            }
            .footer-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

    <!-- Header Navigation -->
    <nav class="navbar">
        <div class="logo">RMS System</div>
        <div class="nav-links">
            <a href="index.php">Home</a>
            <a href="#about">About Us</a>
            <a href="#works">Recent Works</a>
            <a href="auth/login.php">Login</a>
            <a href="auth/register.php" class="btn-register">Register</a>
        </div>
    </nav>

    <!-- Single Hero Section -->
    <section class="hero">
        <div class="hero-slide active" style="background-image: url('https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=1920&q=80');"></div>
        <div class="hero-slide" style="background-image: url('https://images.unsplash.com/photo-1541888946425-d0fbb186a5b3?auto=format&fit=crop&w=1920&q=80');"></div>
        <div class="hero-slide" style="background-image: url('https://images.unsplash.com/photo-1504307651254-35680f356dfd?auto=format&fit=crop&w=1920&q=80');"></div>
        
        <div class="hero-overlay"></div>

        <div class="hero-content">
            <h1>Repairs & Maintenance Management System</h1>
            <p>A smart platform for reporting, tracking, assigning, and managing maintenance requests efficiently.</p>
            <div class="hero-buttons">
                <a href="auth/register.php" class="btn-primary">Get Started</a>
                <a href="auth/login.php" class="btn-secondary">Login to Account</a>
            </div>
        </div>
    </section>

    <!-- Statistics Bar -->
    <section class="stats-bar">
        <div class="stat-item">
            <h2>1,240+</h2>
            <p>Active Clients</p>
        </div>
        <div class="stat-item">
            <h2>4,850+</h2>
            <p>Repairs Completed</p>
        </div>
        <div class="stat-item">
            <h2>85+</h2>
            <p>Expert Technicians</p>
        </div>
        <div class="stat-item">
            <h2>99.4%</h2>
            <p>Satisfaction Rate</p>
        </div>
    </section>

    <!-- About Our System Solutions -->
    <section class="about-section" id="about">
        <div class="about-content">
            <h2>About Our System Solutions</h2>
            <p>The Repairs & Maintenance Management System (RMS) is engineered to bridge the gap between building occupants and maintenance teams. We eliminate messy paperwork and slow request turnarounds.</p>
            <p>Whether it's complex infrastructure overhauls, routine technical checks, or urgent system faults, our automated platform structures assignments clearly so engineers solve problems comprehensively.</p>
        </div>
        <div class="about-image">
            <img src="https://images.unsplash.com/photo-1581092335397-9583fe92d232?auto=format&fit=crop&w=800&q=80" alt="About Repairs & Maintenance System">
        </div>
    </section>

    <!-- Recent Maintenance Projects Section (6 Cards Grid) -->
    <section class="projects-section" id="works">
        <div class="section-header">
            <h2>Recent Maintenance Projects</h2>
            <p>Take a closer look at the diverse physical and operational works handled successfully by our dispatch teams.</p>
        </div>
        
        <div class="projects-grid">
            <div class="project-card">
                <img class="project-image" src="https://images.unsplash.com/photo-1621905251189-08b45d6a269e?auto=format&fit=crop&w=600&q=80" alt="Electrical Panel Inspection">
                <div class="project-info">
                    <span class="project-category">Electrical</span>
                    <h3 class="project-title">Commercial Rewiring & Panel Setup</h3>
                    <p class="project-desc">Full control panel maintenance, wiring testing, and safety system diagnostics completed successfully.</p>
                </div>
            </div>

            <div class="project-card">
                <img class="project-image" src="https://images.unsplash.com/photo-1585704032915-c3400ca199e7?auto=format&fit=crop&w=600&q=80" alt="Plumbing Valves and Pipes">
                <div class="project-info">
                    <span class="project-category">Plumbing</span>
                    <h3 class="project-title">Mainline Valve & Piping Service</h3>
                    <p class="project-desc">Industrial water flow regulation, pipe leak fixes, and pressure valve replacements executed clean.</p>
                </div>
            </div>

            <div class="project-card">
                <img class="project-image" src="https://images.unsplash.com/photo-1621905252507-b35492cc74b4?auto=format&fit=crop&w=600&q=80" alt="HVAC Compressor Service">
                <div class="project-info">
                    <span class="project-category">Appliance Repairs</span>
                    <h3 class="project-title">Centralized HVAC Compressor Service</h3>
                    <p class="project-desc">Comprehensive troubleshooting, structural cooling systems management, and power updates completed.</p>
                </div>
            </div>

            <div class="project-card">
                <img class="project-image" src="https://images.unsplash.com/photo-1581092160607-ee22621dd758?auto=format&fit=crop&w=600&q=80" alt="Structural Hardware Installation">
                <div class="project-info">
                    <span class="project-category">Installations</span>
                    <h3 class="project-title">Fixture Mounting & Structural Fit</h3>
                    <p class="project-desc">High-precision hardware installations and modern framework alignments completed across offices.</p>
                </div>
            </div>

            <div class="project-card">
                <img class="project-image" src="https://images.unsplash.com/photo-1517077304055-6e89abbf09b0?auto=format&fit=crop&w=600&q=80" alt="Circuit Board Calibration">
                <div class="project-info">
                    <span class="project-category">Appliance Repairs</span>
                    <h3 class="project-title">Machinery Circuit Calibration</h3>
                    <p class="project-desc">Circuit re-calibrations and internal structural component updates for heavy commercial utility assets.</p>
                </div>
            </div>

            <div class="project-card">
                <img class="project-image" src="https://images.unsplash.com/photo-1558494949-ef010cbdcc31?auto=format&fit=crop&w=600&q=80" alt="Terminal Component Setup">
                <div class="project-info">
                    <span class="project-category">Installations</span>
                    <h3 class="project-title">Smart Terminal Component Setup</h3>
                    <p class="project-desc">Precision setup of monitoring boards, digital hubs, and connectivity modules for operations tracking.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Single Footer -->
    <footer class="footer">
        <div class="footer-grid">
            <div class="footer-brand">
                <h3>RMS System</h3>
                <p>Simplifying system reporting, infrastructure monitoring, and dispatch services across properties seamlessly.</p>
            </div>
            <div class="footer-links">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="#about">About Us</a></li>
                    <li><a href="#works">Recent Works</a></li>
                    <li><a href="auth/login.php">Login Portal</a></li>
                </ul>
            </div>
            <div class="footer-links">
                <h4>Contact Support</h4>
                <ul>
                    <li>Phone: +254 712 345 678</li>
                    <li>Email: support@mmssystem.com</li>
                    <li>Hours: 24/7 Emergency Dispatch</li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2026 Repairs & Maintenance Management System. All Rights Reserved.</p>
        </div>
    </footer>

    <!-- Background Image Rotation Script -->
    <script>
        const slides = document.querySelectorAll('.hero-slide');
        let currentSlide = 0;

        function changeSlide() {
            slides[currentSlide].classList.remove('active');
            currentSlide = (currentSlide + 1) % slides.length;
            slides[currentSlide].classList.add('active');
        }

        setInterval(changeSlide, 4500);
    </script>
</body>
</html>