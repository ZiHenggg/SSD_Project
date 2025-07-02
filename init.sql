-- RUN THE FOLLOWING ON TERMINAL TO ENSURE DB VALUES ARE UPDARED
-- Get-Content .\init.sql | docker exec -i mysql-ssd-db mysql -u root -proot ssddb
-- This line is to check that all tables and data are in place:
-- docker exec -it mysql-ssd-db mysql -u root -proot

-- Create the database (if not already selected)
DROP DATABASE IF EXISTS ssddb;
CREATE DATABASE ssddb;
USE ssddb;
SET FOREIGN_KEY_CHECKS = 0;

-- Drop existing tables if they exist
DROP TABLE IF EXISTS modules;
DROP TABLE IF EXISTS labGroups;
DROP TABLE IF EXISTS reply;
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS groupJoinRequests;
DROP TABLE IF EXISTS groupMembers;
DROP TABLE IF EXISTS `groups`;
DROP TABLE IF EXISTS studentStats;
DROP TABLE IF EXISTS students;
-- Drop existing foreign key constraints
-- Note: Foreign key constraints are automatically dropped when the table is dropped.
-- If you need to drop specific foreign keys without dropping the table, you can use:
-- ALTER TABLE table_name DROP FOREIGN KEY constraint_name;
-- Drop existing tables if they exist

-- modules table
CREATE TABLE modules (
  moduleCode VARCHAR(20) PRIMARY KEY,
  moduleName VARCHAR(255) NOT NULL
);

-- labGroups table
CREATE TABLE labGroups (
  labGroupCode VARCHAR(10),
  moduleCode VARCHAR(20),
  PRIMARY KEY (labGroupCode, moduleCode), -- composite key = moduleCode + labGroupCode
  FOREIGN KEY (moduleCode) REFERENCES modules (moduleCode) ON DELETE RESTRICT
);

-- students table (MODIFIED to support OTP & email verification)
CREATE TABLE students (
  studentId INT PRIMARY KEY CHECK (studentId BETWEEN 1000000 AND 9999999),
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  google2fa_secret VARCHAR(255) DEFAULT NULL,
  is_2fa_enabled BOOLEAN NOT NULL DEFAULT FALSE,
  otp_code VARCHAR(6) DEFAULT NULL,
  otp_expiry DATETIME DEFAULT NULL,
  email_verified BOOLEAN NOT NULL DEFAULT FALSE
);



-- studentStats table (shared PK with students)
CREATE TABLE studentStats (
  studentId INT PRIMARY KEY,
  totalReviews INT NOT NULL DEFAULT 0,
  averageRating DECIMAL(3,2) NOT NULL DEFAULT 0.00,
  FOREIGN KEY (studentId) REFERENCES students(studentId) ON DELETE CASCADE
);

-- group table [2024/25 T3] ICT2216-P1-G1
CREATE TABLE `groups` (
  groupId INT AUTO_INCREMENT PRIMARY KEY,
  groupName VARCHAR(255) NOT NULL,
  acadYear VARCHAR(20) NOT NULL,
  trimester VARCHAR(10) NOT NULL,
  moduleCode VARCHAR(20) NOT NULL,
  labGroupCode VARCHAR (10) NULL,
  groupNumber INT NOT NULL,
  noOfMembers INT NOT NULL DEFAULT 0,
  maxGroupSize INT NOT NULL,
  groupStatus ENUM('active', 'inactive', 'closed') NOT NULL, 
  FOREIGN KEY (moduleCode) REFERENCES modules (moduleCode) ON DELETE RESTRICT,
  FOREIGN KEY (labGroupCode, moduleCode) REFERENCES labGroups (labGroupCode, moduleCode) ON DELETE RESTRICT
);

-- groupMembers table
CREATE TABLE groupMembers (
  groupMembersId INT AUTO_INCREMENT PRIMARY KEY,
  groupId INT NOT NULL,
  studentId INT NOT NULL,
  role ENUM('admin', 'member') NOT NULL,
  FOREIGN KEY (groupId) REFERENCES `groups`(groupId) ON DELETE CASCADE,
  FOREIGN KEY (studentId) REFERENCES students(studentId) ON DELETE CASCADE
);

-- groupJoinRequests table
CREATE TABLE groupJoinRequests (
  requestId INT AUTO_INCREMENT PRIMARY KEY,
  groupId INT NOT NULL,
  requesterId INT NOT NULL,
  joinStatus ENUM('pending', 'accepted', 'rejected') NOT NULL DEFAULT 'pending',
  requestedAt DATETIME  NOT NULL DEFAULT CURRENT_TIMESTAMP,
  reviewedAt DATETIME  NULL,
  reviewedBy INT NULL,
  FOREIGN KEY (groupId) REFERENCES `groups`(groupId) ON DELETE CASCADE,
  FOREIGN KEY (requesterId) REFERENCES students(studentId) ON DELETE CASCADE,
  FOREIGN KEY (reviewedBy) REFERENCES students(studentId) ON DELETE SET NULL
);

-- reviews table
CREATE TABLE reviews (
  reviewId INT AUTO_INCREMENT PRIMARY KEY,
  reviewerId INT NOT NULL,
  revieweeId INT NOT NULL,
  groupMembersId INT NOT NULL,
  reviewRating INT NOT NULL,
  description TEXT NOT NULL,
  reviewDate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (reviewerId) REFERENCES students(studentId),
  FOREIGN KEY (revieweeId) REFERENCES students(studentId),
  FOREIGN KEY (groupMembersId) REFERENCES groupMembers(groupMembersId)
);

-- reply table
CREATE TABLE reply (
  replyId INT AUTO_INCREMENT PRIMARY KEY,
  reviewId INT NOT NULL UNIQUE,
  responderId INT NOT NULL,
  justification TEXT NOT NULL,
  replyDate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (reviewId) REFERENCES reviews(reviewId),
  FOREIGN KEY (responderId) REFERENCES students(studentId)
);

-- Now insert sample data
INSERT INTO modules (moduleCode, moduleName) VALUES 
('ICT2216', 'Secure Software Development'), 
('ICT2114', 'Integrative Team Project');

INSERT INTO labGroups (labGroupCode, moduleCode) VALUES
('P1', 'ICT2216'), 
('P2', 'ICT2216'), 
('P1', 'ICT2114'), 
('P2', 'ICT2114');

INSERT INTO students (studentId, name, email, password) VALUES
(1000001, 'Alice Tan1', 'alice@example.com', 'pass123'),
(1000002, 'Bob Lee1', 'bob@example.com', 'pass123'),
(1000003, 'Charlie Lim1', 'charlie@example.com', 'pass123'),
(1000004, 'David Wong1', 'david@example.com', 'pass123');

INSERT INTO studentStats (studentId, totalReviews, averageRating) VALUES
(1000001, 2, 3.50),
(1000002, 1, 4.00),
(1000003, 1, 4.00),
(1000004, 0, 0.00);

INSERT INTO `groups` (groupName, acadYear, trimester, moduleCode, labGroupCode, groupNumber, noOfMembers, maxGroupSize, groupStatus) VALUES
('[2024/25 T3] ICT2216-P1-G5', '2024/25', '3', 'ICT2216', 'P1', 5, 3, 7, 'active'),
('[2024/25 T3] ICT2114-P2-G1', '2024/25', '3', 'ICT2114', 'P2', 1, 3, 5, 'active');

INSERT INTO groupMembers (groupId, studentId, role) VALUES
(1, 1000001, 'admin'),
(1, 1000002, 'member'),
(1, 1000004, 'member'),
(2, 1000003, 'admin'),
(2, 1000001, 'member'),
(2, 1000004, 'member');

INSERT INTO groupJoinRequests (groupId, requesterId) VALUES
(2, 1000002),
(1, 1000003);

INSERT INTO reviews (reviewerId, revieweeId, groupMembersId, reviewRating, description) VALUES
(1000001, 1000002, 1, 4, 'Great teamwork and communication!'),
(1000001, 1000003, 4, 4, 'Fun teammate'),
(1000002, 1000001, 2, 4, 'Good teammate, very helpful!'),
(1000003, 1000001, 3, 3, 'Excellent contributions to the project!');

INSERT INTO reply (reviewId, responderId, justification) VALUES
(1, 1000002, 'Thank you! I really appreciated the project.'),
(4, 1000001, 'Thanks!');

SET FOREIGN_KEY_CHECKS = 1;

-- 👇 Additional seed data for CI login/2FA Playwright tests

-- Add test users for login scenarios (with and without 2FA)
INSERT INTO students (studentId, name, email, password, google2fa_secret, is_2fa_enabled, email_verified) VALUES
(1234567, 'CI Test User 1', '1234567@sit.singaporetech.edu.sg',
 '$2y$10$vVaVplDWvIr5n1QMbROa7Ocbv6mCIcfRNd2ZzUn7qlmiXExEVntv2', NULL, FALSE, TRUE), -- No 2FA
(7654321, 'CI Test User 2', '7654321@sit.singaporetech.edu.sg',
 '$2y$10$vVaVplDWvIr5n1QMbROa7Ocbv6mCIcfRNd2ZzUn7qlmiXExEVntv2', 'TESTSECRET', TRUE, TRUE); -- With 2FA

-- Add their stats
INSERT INTO studentStats (studentId) VALUES (1234567), (7654321);
