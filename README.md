# EduGen — Smart School ERP

**Smart School . Connected Future**

EduGen is a comprehensive, multi-tenant School ERP system built with PHP. It provides end-to-end school management including academics, attendance, exams, fees, payroll, timetable, homework, library, transport, hostel, inventory, communication, and more.

---

## 🚀 Features

- **Multi-School SaaS**: Super Admin can manage multiple schools from one dashboard
- **Role-Based Access**: Super Admin, School Admin, Principal, Teacher, Staff, Student, Parent
- **Academic Management**: Classes, Sections, Subjects, Academic Years
- **Student Management**: Admissions, Profiles, Documents, Parent Linking
- **Staff/HR Management**: Staff Profiles, Designations, Departments
- **Attendance**: Student & Staff attendance with calendar views
- **Timetable**: Period management, Class & Teacher timetable views
- **Exams & Results**: Exam scheduling, Marks entry, Grade management
- **Homework**: Assignment creation, Multi-section support, Submissions
- **Fees Management**: Fee structures, Collection, Payment tracking
- **Payroll**: Salary structures, Monthly payslip generation, Payment recording (Bank/Cash/Cheque/UPI)
- **Library, Transport, Hostel, Inventory, Visitors, Certificates, Reports** modules
- **Communication**: Notices & Broadcasts
- **Dynamic Currency**: Configurable per-school currency symbol
- **Dark Mode**: Toggle between light and dark themes
- **Responsive**: Works on desktop, tablet, and mobile browsers

---

## 🛠 Tech Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.1+ (Vanilla MVC) |
| Database | MySQL 5.7+ / MariaDB 10.3+ |
| Frontend | HTML5, Vanilla CSS, JavaScript |
| Icons | Bootstrap Icons |
| Server | Apache with mod_rewrite |

---

## ⚡ Quick Start

### Prerequisites
- PHP 8.1+
- MySQL 5.7+
- Apache with `mod_rewrite`

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/featuregen/FGSCL.git
   cd FGSCL
   ```

2. **Configure environment**
   ```bash
   cp .env.example .env
   # Edit .env with your database credentials and app URL
   ```

3. **Create the database**
   ```sql
   CREATE DATABASE edugen_erp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

4. **Run migrations**
   ```bash
   php database/migrate.php
   ```

5. **Point your web server** to the `public/` directory, or configure `.htaccess` accordingly.

6. **Visit the app** and create your Super Admin account through the setup wizard.

---

## 📁 Project Structure

```
├── app/
│   ├── Controllers/       # Route controllers
│   ├── Helpers/            # Database, Session, Response, Validator
│   └── Models/             # Data models (if applicable)
├── config/
│   ├── app.php             # App constants & env loading
│   └── database.php        # Database connection
├── database/
│   └── migrations/         # SQL migration files
├── public/
│   ├── index.php           # Front controller (entry point)
│   ├── assets/             # CSS, JS, Images
│   └── uploads/            # User-uploaded files
├── storage/
│   ├── logs/               # Application logs
│   ├── cache/              # Cache files
│   └── exports/            # Generated exports
├── views/
│   ├── layouts/            # Header, Footer templates
│   ├── partials/           # Sidebar, Navbar
│   └── [module]/           # Module-specific views
├── .env.example            # Environment template
├── .htaccess               # Apache rewrite rules
└── composer.json           # PHP dependencies
```

---

## 🔐 Default Roles & Permissions

| Role | Access Level |
|---|---|
| Super Admin | Full platform access, manage schools |
| School Admin | Full school access |
| Principal | Academic oversight |
| Teacher/Staff | Class & subject-specific access |
| Student | View own data, submit homework |
| Parent | View child's data |

---

## 📄 License

Proprietary — © 2026 FeatureGen. All rights reserved.

---

## 🤝 Support

For support, contact [info@featuregen.com](mailto:info@featuregen.com)
