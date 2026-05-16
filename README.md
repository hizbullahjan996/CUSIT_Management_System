# CUSIT Smart Campus Portal

CUSIT Smart Campus Portal for Skillathon 2026, built with PHP, PDO, and MySQL/MariaDB for XAMPP localhost. The project keeps the completed UI in place and connects it to authentication, dashboards, complaints, events, FYP management, announcements, and notifications.

## Requirements

- XAMPP with Apache and MySQL enabled
- PHP from XAMPP
- MySQL/MariaDB
- Browser access to `http://localhost/ParticipantName-WebDev/`

## XAMPP Setup

1. Place the project in `C:\xampp\htdocs\ParticipantName-WebDev`.
2. Start Apache and MySQL from XAMPP Control Panel.
3. Open phpMyAdmin at `http://localhost/phpmyadmin`.
4. Import [database.sql](c:/xampp/htdocs/ParticipantName-WebDev/database.sql).
5. Open `http://localhost/ParticipantName-WebDev/`.

## Demo Logins

Admin:

- Email: `admin@cusit.edu.pk`
- Password: `admin123`

Student:

- Email: `student@cusit.edu.pk`
- Password: `student123`

Faculty:

- Email: `faculty@cusit.edu.pk`
- Password: `faculty123`

Additional sample student:

- Email: `sara@cusit.edu.pk`
- Password: `student123`

## Modules

- Authentication: secure login, student registration, logout, sessions, CSRF, and role-based redirects.
- Admin dashboard: totals for students, complaints, events, registrations, FYP groups, and announcements with recent activity.
- Student dashboard: personal complaint stats, registered events, FYP status, announcements, and notifications.
- Faculty dashboard: role-based login with campus announcements, notifications, and recent FYP visibility.
- Complaints: student submission and tracking, admin review, status updates, and response workflow.
- Events: admin create/edit/delete, student registration, duplicate prevention, and seat availability checks.
- FYP: group creation, member management, milestone tracking, supervisor assignment, and approval workflow.
- Announcements: admin publishing with priorities and student feed display.
- Notifications: complaint updates, event registration alerts, FYP status alerts, and urgent announcement alerts.

## Folder Structure

```text
ParticipantName-WebDev/
|-- index.php
|-- login.php
|-- register.php
|-- logout.php
|-- database.sql
|-- README.md
|-- config/
|   `-- db.php
|-- includes/
|   |-- auth.php
|   |-- functions.php
|   |-- header.php
|   `-- footer.php
|-- admin/
|   |-- dashboard.php
|   |-- complaints.php
|   |-- events.php
|   |-- fyp.php
|   `-- announcements.php
|-- student/
|   |-- dashboard.php
|   |-- complaints.php
|   |-- events.php
|   |-- fyp.php
|   `-- announcements.php
|-- faculty/
|   |-- dashboard.php
|   `-- announcements.php
`-- assets/
    |-- css/
    |-- js/
    `-- images/
```

## Demo Flow

1. Import `database.sql`.
2. Log in as admin and review dashboard, complaints, events, FYP groups, and announcements.
3. Log in as student and submit a complaint, register for an event, and review FYP status.
4. Update a complaint or FYP status as admin to see notifications appear for the student.

## Database Connection

The app uses PDO with these defaults in [config/db.php](c:/xampp/htdocs/ParticipantName-WebDev/config/db.php):

- Host: `localhost`
- Username: `root`
- Password: empty
- Database: `cusit_smart_campus`
"# CUSIT_Management_System" 
