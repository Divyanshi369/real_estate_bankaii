# 🏗 Real Estate Project Management System

A role-based project management system built with **Core PHP + MySQL** for managing real estate operations.  
Supports multiple user roles: **Admin, Manager, Supervisor, and Worker**, each with their own dashboards and permissions.

---

## 🚀 Features

- 🔑 **Authentication System**
  - Secure login with `password_hash` & `password_verify`
  - Role-based dashboard redirection
  - Forgot password & password reset flow

- 👥 **Role-Based Access**
  - **Admin**: Manage users, releases, and overall system
  - **Manager**: Project/stock management
  - **Supervisor**: Stock releases with image upload
  - **Worker**: Task tracking

- 🔒 **Security**
  - CSRF protection tokens
  - XSS protection via escaping helper
  - Secure password hashing
  - Prepared statements (SQL Injection protection)
  - `.htaccess` security headers
  - Randomized filenames for uploads

- 📂 **Utilities**
  - File/image upload for stock releases
  - Pagination helper
  - Global `public_url()` helper for asset links
  - Responsive design with Bootstrap

---

## 📂 Project Structure
estate/
├── config/ # Database connection config
├── includes/ # Auth + helper functions
├── public/ # Web root (all accessible files)
│ ├── assets/ # CSS, JS, images
│ ├── admin/ # Admin dashboard
│ ├── manager/ # Manager dashboard
│ ├── supervisor/ # Supervisor dashboard
│ ├── worker/ # Worker dashboard
│ ├── index.php # Landing page
│ └── login.php # Main login page
├── sql/ # Database schema + helpers
│ └── database_schema.sql
└── .htaccess # Security headers + public root
