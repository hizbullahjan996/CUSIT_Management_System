CREATE DATABASE IF NOT EXISTS cusit_smart_campus CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cusit_smart_campus;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS announcements;
DROP TABLE IF EXISTS fyp_milestones;
DROP TABLE IF EXISTS fyp_members;
DROP TABLE IF EXISTS fyp_groups;
DROP TABLE IF EXISTS event_registrations;
DROP TABLE IF EXISTS events;
DROP TABLE IF EXISTS complaints;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin', 'student', 'faculty') NOT NULL,
  student_id VARCHAR(50) NULL,
  department VARCHAR(100) NULL,
  semester VARCHAR(50) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE complaints (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  category VARCHAR(100) NOT NULL,
  priority ENUM('low', 'medium', 'high', 'urgent') NOT NULL DEFAULT 'medium',
  title VARCHAR(200) NOT NULL,
  description TEXT NOT NULL,
  status ENUM('submitted', 'under_review', 'in_progress', 'resolved', 'rejected') NOT NULL DEFAULT 'submitted',
  admin_response TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL,
  CONSTRAINT fk_complaints_student FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE events (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(200) NOT NULL,
  description TEXT NOT NULL,
  event_date DATE NOT NULL,
  event_time TIME NOT NULL,
  venue VARCHAR(150) NOT NULL,
  total_seats INT NOT NULL,
  status ENUM('active', 'closed', 'cancelled') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE event_registrations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  event_id INT NOT NULL,
  student_id INT NOT NULL,
  registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_event_reg_event FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
  CONSTRAINT fk_event_reg_student FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
  UNIQUE KEY unique_event_registration (event_id, student_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE fyp_groups (
  id INT AUTO_INCREMENT PRIMARY KEY,
  group_name VARCHAR(150) NOT NULL,
  leader_id INT NOT NULL,
  project_title VARCHAR(200) NOT NULL,
  project_description TEXT NOT NULL,
  supervisor VARCHAR(150) NULL,
  status ENUM('pending', 'approved', 'rejected', 'in_progress', 'completed') NOT NULL DEFAULT 'pending',
  admin_remarks TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_fyp_groups_leader FOREIGN KEY (leader_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE fyp_members (
  id INT AUTO_INCREMENT PRIMARY KEY,
  group_id INT NOT NULL,
  student_name VARCHAR(100) NOT NULL,
  student_reg_no VARCHAR(100) NOT NULL,
  role VARCHAR(50) NOT NULL,
  CONSTRAINT fk_fyp_members_group FOREIGN KEY (group_id) REFERENCES fyp_groups(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE fyp_milestones (
  id INT AUTO_INCREMENT PRIMARY KEY,
  group_id INT NOT NULL,
  milestone_title VARCHAR(150) NOT NULL,
  status ENUM('pending', 'in_progress', 'completed') NOT NULL DEFAULT 'pending',
  deadline DATE NULL,
  CONSTRAINT fk_fyp_milestones_group FOREIGN KEY (group_id) REFERENCES fyp_groups(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE announcements (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(200) NOT NULL,
  message TEXT NOT NULL,
  priority ENUM('normal', 'important', 'urgent') NOT NULL DEFAULT 'normal',
  created_by INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_announcements_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE notifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  message TEXT NOT NULL,
  is_read TINYINT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO users (name, email, password, role, student_id, department, semester) VALUES
('CUSIT Admin', 'admin@cusit.edu.pk', '$2y$10$ntvD9Qr3bhomFLOtC5zFAe6WYKvtW6a4YmElS/jnYNVDJwMqYYFAa', 'admin', NULL, 'Administration', NULL),
('Dr. Ayesha Noor', 'faculty@cusit.edu.pk', '$2y$10$z1BQvblvWjyhjHVOt5dJYOYWlkRYl00P1V3iiukDvKYWzJmP4mZ/G', 'faculty', NULL, 'Computer Science', NULL),
('Alex Rivera', 'student@cusit.edu.pk', '$2y$10$VOULir2/kb1Uk88mDwJ4ueqfSKZFbEd65hdE6P4BryZVlQxWQNwYK', 'student', 'CUSIT-CS-001', 'Computer Science', '6th'),
('Sara Khan', 'sara@cusit.edu.pk', '$2y$10$VOULir2/kb1Uk88mDwJ4ueqfSKZFbEd65hdE6P4BryZVlQxWQNwYK', 'student', 'CUSIT-SE-017', 'Software Engineering', '7th');

INSERT INTO complaints (student_id, category, priority, title, description, status, admin_response, updated_at) VALUES
(2, 'IT', 'high', 'Compiler Lab Performance Issue', 'Lab 3 systems become very slow during compiler practical sessions.', 'in_progress', 'IT support is diagnosing the affected workstations.', NOW()),
(2, 'Facilities', 'medium', 'Projector Needs Maintenance', 'Projector brightness in Room B-204 is too low for presentations.', 'submitted', NULL, NULL),
(3, 'Security', 'urgent', 'Server Room Access Concern', 'An unknown person attempted entry near the server room after hours.', 'under_review', 'Security team has reviewed CCTV footage and escalated it.', NOW()),
(3, 'Academic', 'low', 'Updated Timetable Request', 'Need the latest elective timetable for final project planning.', 'resolved', 'Updated timetable was published in the portal announcements.', NOW());

INSERT INTO events (title, description, event_date, event_time, venue, total_seats, status) VALUES
('CUSIT Skillathon 2026', 'Campus-wide innovation sprint for developers, designers, and startup teams.', DATE_ADD(CURDATE(), INTERVAL 5 DAY), '10:00:00', 'Main Auditorium', 120, 'active'),
('AI Research Meetup', 'Faculty and student research showcase with lightning talks and demos.', DATE_ADD(CURDATE(), INTERVAL 12 DAY), '14:00:00', 'Seminar Hall A', 60, 'active'),
('Career Prep Bootcamp', 'Resume clinics, mock interviews, and portfolio reviews for final-year students.', DATE_ADD(CURDATE(), INTERVAL 20 DAY), '11:30:00', 'Innovation Lab', 45, 'active');

INSERT INTO event_registrations (event_id, student_id) VALUES
(1, 2),
(2, 2),
(1, 3);

INSERT INTO fyp_groups (group_name, leader_id, project_title, project_description, supervisor, status, admin_remarks) VALUES
('Neural Navigators', 2, 'Smart Campus Complaint Intelligence', 'A smart complaint workflow that predicts urgency and routes complaints to the right department.', 'Dr. Hina Ahmed', 'approved', 'Proposal approved. Continue with the working prototype.'),
('Quantum Coders', 3, 'IoT Energy Monitor', 'A connected system to analyze electricity usage in labs and classrooms.', NULL, 'pending', NULL);

INSERT INTO fyp_members (group_id, student_name, student_reg_no, role) VALUES
(1, 'Alex Rivera', 'CUSIT-CS-001', 'Leader'),
(1, 'Sara Khan', 'CUSIT-SE-017', 'Member'),
(2, 'Sara Khan', 'CUSIT-SE-017', 'Leader');

INSERT INTO fyp_milestones (group_id, milestone_title, status, deadline) VALUES
(1, 'Proposal Defense', 'completed', DATE_SUB(CURDATE(), INTERVAL 10 DAY)),
(1, 'Prototype Demo', 'in_progress', DATE_ADD(CURDATE(), INTERVAL 15 DAY)),
(1, 'Final Documentation', 'pending', DATE_ADD(CURDATE(), INTERVAL 60 DAY)),
(2, 'Proposal Defense', 'pending', DATE_ADD(CURDATE(), INTERVAL 7 DAY));

INSERT INTO announcements (title, message, priority, created_by) VALUES
('Skillathon Registration Open', 'Students can now register for the CUSIT Skillathon through the Events section.', 'important', 1),
('Library Hours Extended', 'The central library will remain open until 9 PM during project week.', 'normal', 1),
('Transport Route Update', 'Evening shuttle timing has been revised for the Gulbahar route.', 'urgent', 1);

INSERT INTO notifications (user_id, message) VALUES
(2, 'Your FYP proposal has been approved and a supervisor has been assigned.'),
(2, 'You are registered for AI Research Meetup.'),
(3, 'Your complaint status has been updated to under review.');
