# Digital Identity Card System

A simple PHP + MySQL digital identity card management system built for XAMPP.

## Features
- Public landing page with registration form
- Admin approval workflow
- Approved digital identity cards with QR code
- Profile image upload support
- MySQL-backed data storage

## Initial local admin account
- Username: admin
- Password: admin123

Change the initial password before making the application publicly accessible.

## Run locally
1. Start Apache and MySQL in XAMPP.
2. Place this folder in `C:\xampp\htdocs\digital_identity`.
3. Open `http://localhost/digital_identity/` in your browser.
4. Open `http://localhost/digital_identity/admin/login.php` to access the admin panel.

The app automatically creates the `digital_identity` database and required tables on first load.
