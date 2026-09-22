# Repairs & Maintenance Management System

PHP + MySQL web app for managing maintenance requests between users, technicians, and admins.

## Tech Stack
- **Frontend:** HTML5, CSS3, JavaScript (inline in PHP templates)
- **Backend:** PHP 8.1
- **Database:** MySQL
- **Web Server:** Apache
- **Containerization:** Docker
- **Hosting:** Railway (app) + Railway MySQL (database)
- **Version Control:** Git + GitHub

## Live Demo
🔗 https://web-production-82d87.up.railway.app
> **Note:** This app is hosted on Railway's free tier, which uses a shared
> `*.up.railway.app` subdomain. Some browsers may show a generic "unsafe site"
> warning because that shared domain has been flagged in the past by other
> users. This is a known limitation of the free hosting platform and does not
> reflect the security of this application. You can safely proceed via
> "Advanced → Proceed" in the warning screen.

## Local Setup
1. Clone repo
2. Import `maintenance_db.sql` into MySQL
3. Configure `config/db.php`
4. 4. Access via XAMPP at http://localhost/maintenance_system_deploy/