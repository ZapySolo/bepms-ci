-- phpMyAdmin SQL Dump
-- version 5.0.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Feb 18, 2020 at 12:21 AM
-- Server version: 10.4.11-MariaDB
-- PHP Version: 7.4.2

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bepms`
--

-- --------------------------------------------------------

--
-- Table structure for table `bepms_admins`
--

CREATE TABLE `bepms_admins` (
  `admin_id` int(10) NOT NULL,
  `user_id` int(10) NOT NULL,
  `admin_creation_date` date NOT NULL DEFAULT current_timestamp(),
  `admin_last_login` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `bepms_admins`
--

INSERT INTO `bepms_admins` (`admin_id`, `user_id`, `admin_creation_date`, `admin_last_login`) VALUES
(1, 1, '2020-02-11', '2020-02-11'),
(2, 33, '2020-02-12', '2020-02-12');

-- --------------------------------------------------------

--
-- Table structure for table `bepms_emails`
--

CREATE TABLE `bepms_emails` (
  `id` int(10) NOT NULL,
  `email_day_key` int(10) NOT NULL,
  `email_day_value` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `bepms_email_forgot_tokens`
--

CREATE TABLE `bepms_email_forgot_tokens` (
  `id` int(10) NOT NULL,
  `user_id` int(10) NOT NULL,
  `top_token` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `bepms_notices`
--

CREATE TABLE `bepms_notices` (
  `notice_id` int(10) NOT NULL,
  `to_user_id` int(10) NOT NULL,
  `from_user_id` int(10) NOT NULL,
  `notice_message` mediumtext NOT NULL,
  `notice_level` int(10) NOT NULL,
  `project_id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `bepms_notifications`
--

CREATE TABLE `bepms_notifications` (
  `notification_id` int(10) NOT NULL,
  `to_user_id` int(10) NOT NULL,
  `from_user_id` int(10) DEFAULT NULL,
  `notification_message` mediumtext NOT NULL,
  `notification_checked_status` tinyint(3) NOT NULL DEFAULT 0,
  `notification_creation_date` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `bepms_projects`
--

CREATE TABLE `bepms_projects` (
  `project_id` int(10) NOT NULL,
  `project_name` varchar(255) NOT NULL DEFAULT 'project_',
  `project_status` int(10) NOT NULL DEFAULT 0,
  `project_attachment` varchar(255) DEFAULT NULL,
  `project_code` varchar(255) DEFAULT NULL,
  `system_id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `bepms_projects`
--

INSERT INTO `bepms_projects` (`project_id`, `project_name`, `project_status`, `project_attachment`, `project_code`, `system_id`) VALUES
(1, 'Medical Drone', 0, NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `bepms_project_member_positions`
--

CREATE TABLE `bepms_project_member_positions` (
  `id` int(10) NOT NULL,
  `project_id` int(10) NOT NULL,
  `user_id` int(10) NOT NULL,
  `project_position_id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `bepms_project_member_positions`
--

INSERT INTO `bepms_project_member_positions` (`id`, `project_id`, `user_id`, `project_position_id`) VALUES
(1, 1, 5, 1),
(2, 1, 4, 2),
(3, 1, 3, 3),
(4, 1, 2, 4),
(5, 1, 6, 5),
(6, 1, 7, 5);

-- --------------------------------------------------------

--
-- Table structure for table `bepms_project_positions`
--

CREATE TABLE `bepms_project_positions` (
  `project_position_id` int(10) NOT NULL,
  `project_position_name` varchar(255) NOT NULL,
  `project_position_priority` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `bepms_project_positions`
--

INSERT INTO `bepms_project_positions` (`project_position_id`, `project_position_name`, `project_position_priority`) VALUES
(1, 'hod', 1),
(2, 'pc', 2),
(3, 'guide', 3),
(4, 'leader', 4),
(5, 'member', 5);

-- --------------------------------------------------------

--
-- Table structure for table `bepms_reports`
--

CREATE TABLE `bepms_reports` (
  `report_id` int(10) NOT NULL,
  `project_id` int(10) NOT NULL,
  `report_title` mediumtext NOT NULL,
  `report_description` mediumtext DEFAULT NULL,
  `report_attachment` varchar(255) DEFAULT NULL,
  `report_status_claim` int(10) NOT NULL,
  `report_status_leader` varchar(255) NOT NULL DEFAULT '''sent''',
  `report_status_guide` varchar(255) NOT NULL DEFAULT '''pending''',
  `report_status_pc` varchar(255) NOT NULL DEFAULT '''---''',
  `report_status_hod` varchar(255) NOT NULL DEFAULT '''---''',
  `report_disapproved_reason` mediumtext DEFAULT NULL,
  `report_change_assign` mediumtext DEFAULT NULL,
  `report_creation_date` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `bepms_systems`
--

CREATE TABLE `bepms_systems` (
  `system_id` int(10) NOT NULL,
  `admin_id` int(10) NOT NULL,
  `system_name` varchar(255) NOT NULL,
  `system_creation_date` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `bepms_systems`
--

INSERT INTO `bepms_systems` (`system_id`, `admin_id`, `system_name`, `system_creation_date`) VALUES
(1, 1, 'Computer Department 2020', '2020-02-11'),
(3, 1, 'Computer Department 2021', '2020-02-11'),
(5, 2, 'EXTC Department 2020', '2020-02-12');

-- --------------------------------------------------------

--
-- Table structure for table `bepms_to_dos`
--

CREATE TABLE `bepms_to_dos` (
  `to_do_id` int(10) NOT NULL,
  `project_id` int(10) NOT NULL,
  `to_do_name` mediumtext NOT NULL,
  `to_do_date_assign` date NOT NULL,
  `to_do_status` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `bepms_to_dos`
--

INSERT INTO `bepms_to_dos` (`to_do_id`, `project_id`, `to_do_name`, `to_do_date_assign`, `to_do_status`) VALUES
(1, 1, 'MAKING THE FRAME', '2020-01-25', 'completed'),
(2, 1, 'PROPELLERS ELECTRONIC SPEED CONTROLLER, AND MOTORS', '2020-02-05', 'ongoing'),
(3, 1, 'ASSEMBEL THE MOTOR', '2020-02-07', 'upcoming'),
(4, 1, 'MOUNT THE ELECTRONIC SPEED CONTROLLER', '2020-02-10', 'upcoming'),
(5, 1, 'ADD THE LANDING GEAR, FLIGHT CONTROLLER', '2020-02-15', 'upcoming');

-- --------------------------------------------------------

--
-- Table structure for table `bepms_to_do_members_assign`
--

CREATE TABLE `bepms_to_do_members_assign` (
  `id` int(10) NOT NULL,
  `to_do_id` int(10) NOT NULL,
  `to_do_member_assign_id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `bepms_to_do_members_assign`
--

INSERT INTO `bepms_to_do_members_assign` (`id`, `to_do_id`, `to_do_member_assign_id`) VALUES
(1, 1, 6),
(2, 1, 2);

-- --------------------------------------------------------

--
-- Table structure for table `bepms_usermeta`
--

CREATE TABLE `bepms_usermeta` (
  `umeta_id` int(10) NOT NULL,
  `user_id` int(10) NOT NULL,
  `meta_key` varchar(255) NOT NULL,
  `meta_value` mediumtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `bepms_users`
--

CREATE TABLE `bepms_users` (
  `user_id` int(10) NOT NULL,
  `user_first_name` varchar(255) DEFAULT NULL,
  `user_last_name` varchar(255) DEFAULT NULL,
  `user_email` varchar(255) NOT NULL,
  `user_password` varchar(255) DEFAULT '2ac9a6746aca543af8dff39894cfe8173afba21eb01c6fae33d52947222855ef',
  `user_display_name` varchar(255) NOT NULL,
  `user_creation_date` datetime DEFAULT current_timestamp(),
  `user_profile_image` varchar(255) DEFAULT 'default_avatar.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `bepms_users`
--

INSERT INTO `bepms_users` (`user_id`, `user_first_name`, `user_last_name`, `user_email`, `user_password`, `user_display_name`, `user_creation_date`, `user_profile_image`) VALUES
(1, 'Nikhil', 'Patil', '21nikhilpatil1998@gmail.com', '2ac9a6746aca543af8dff39894cfe8173afba21eb01c6fae33d52947222855ef', 'Nikhil Patil', '2020-02-15 00:00:00', 'default_avatar.png'),
(2, 'LEADER', '', 'leader@gmail.com', '2ac9a6746aca543af8dff39894cfe8173afba21eb01c6fae33d52947222855ef', 'LEADER LEADER', '2020-02-11 00:00:00', 'default_avatar.png'),
(3, 'GUIDE', '', 'guide@gmail.com', '2ac9a6746aca543af8dff39894cfe8173afba21eb01c6fae33d52947222855ef', 'GUIDE GUIDE', '2020-02-11 00:00:00', 'default_avatar.png'),
(4, 'PC', NULL, 'pc@gmail.com', '2ac9a6746aca543af8dff39894cfe8173afba21eb01c6fae33d52947222855ef', 'Project Coordinator', '2020-02-11 00:00:00', 'default_avatar.png'),
(5, 'HOD', NULL, 'hod@gmail.com', '2ac9a6746aca543af8dff39894cfe8173afba21eb01c6fae33d52947222855ef', 'HEAD OF DEPARTMENT', '2020-02-11 00:00:00', 'default_avatar.png'),
(6, 'member1', NULL, 'member1@gmail.com', '2ac9a6746aca543af8dff39894cfe8173afba21eb01c6fae33d52947222855ef', 'MEMBER 1', '2020-02-11 00:00:00', 'default_avatar.png'),
(7, 'member2', NULL, 'member2@gmail.com', '2ac9a6746aca543af8dff39894cfe8173afba21eb01c6fae33d52947222855ef', 'MEMBER 2', '2020-02-11 00:00:00', 'default_avatar.png'),
(33, 'admin', '2', 'admin2@gmail.com', '2ac9a6746aca543af8dff39894cfe8173afba21eb01c6fae33d52947222855ef', 'admin2', '2020-02-12 12:22:41', 'default_avatar.png'),
(34, 'BEPMS', 'SYSTEM', 'support@bepms.com', '2ac9a6746aca543af8dff39894cfe8173afba21eb01c6fae33d52947222855ef', 'BEPMS SUPPORT', '2020-02-12 00:00:00', 'default_avatar.png');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bepms_admins`
--
ALTER TABLE `bepms_admins`
  ADD PRIMARY KEY (`admin_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `bepms_emails`
--
ALTER TABLE `bepms_emails`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bepms_email_forgot_tokens`
--
ALTER TABLE `bepms_email_forgot_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `bepms_notices`
--
ALTER TABLE `bepms_notices`
  ADD PRIMARY KEY (`notice_id`),
  ADD KEY `from_user_id` (`from_user_id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `to_user_id` (`to_user_id`);

--
-- Indexes for table `bepms_notifications`
--
ALTER TABLE `bepms_notifications`
  ADD PRIMARY KEY (`notification_id`);

--
-- Indexes for table `bepms_projects`
--
ALTER TABLE `bepms_projects`
  ADD PRIMARY KEY (`project_id`),
  ADD KEY `system_id` (`system_id`);

--
-- Indexes for table `bepms_project_member_positions`
--
ALTER TABLE `bepms_project_member_positions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `project_position_id` (`project_position_id`);

--
-- Indexes for table `bepms_project_positions`
--
ALTER TABLE `bepms_project_positions`
  ADD PRIMARY KEY (`project_position_id`);

--
-- Indexes for table `bepms_reports`
--
ALTER TABLE `bepms_reports`
  ADD PRIMARY KEY (`report_id`),
  ADD KEY `project_id` (`project_id`);

--
-- Indexes for table `bepms_systems`
--
ALTER TABLE `bepms_systems`
  ADD PRIMARY KEY (`system_id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `bepms_to_dos`
--
ALTER TABLE `bepms_to_dos`
  ADD PRIMARY KEY (`to_do_id`),
  ADD KEY `project_id` (`project_id`);

--
-- Indexes for table `bepms_to_do_members_assign`
--
ALTER TABLE `bepms_to_do_members_assign`
  ADD PRIMARY KEY (`id`),
  ADD KEY `to_do_member_assign_id` (`to_do_member_assign_id`),
  ADD KEY `to_do_id` (`to_do_id`);

--
-- Indexes for table `bepms_usermeta`
--
ALTER TABLE `bepms_usermeta`
  ADD PRIMARY KEY (`umeta_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `bepms_users`
--
ALTER TABLE `bepms_users`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bepms_admins`
--
ALTER TABLE `bepms_admins`
  MODIFY `admin_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `bepms_emails`
--
ALTER TABLE `bepms_emails`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bepms_email_forgot_tokens`
--
ALTER TABLE `bepms_email_forgot_tokens`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bepms_notices`
--
ALTER TABLE `bepms_notices`
  MODIFY `notice_id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bepms_notifications`
--
ALTER TABLE `bepms_notifications`
  MODIFY `notification_id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bepms_projects`
--
ALTER TABLE `bepms_projects`
  MODIFY `project_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `bepms_project_member_positions`
--
ALTER TABLE `bepms_project_member_positions`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `bepms_project_positions`
--
ALTER TABLE `bepms_project_positions`
  MODIFY `project_position_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `bepms_reports`
--
ALTER TABLE `bepms_reports`
  MODIFY `report_id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bepms_systems`
--
ALTER TABLE `bepms_systems`
  MODIFY `system_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `bepms_to_dos`
--
ALTER TABLE `bepms_to_dos`
  MODIFY `to_do_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `bepms_to_do_members_assign`
--
ALTER TABLE `bepms_to_do_members_assign`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `bepms_usermeta`
--
ALTER TABLE `bepms_usermeta`
  MODIFY `umeta_id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bepms_users`
--
ALTER TABLE `bepms_users`
  MODIFY `user_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bepms_admins`
--
ALTER TABLE `bepms_admins`
  ADD CONSTRAINT `bepms_admins_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `bepms_users` (`user_id`);

--
-- Constraints for table `bepms_email_forgot_tokens`
--
ALTER TABLE `bepms_email_forgot_tokens`
  ADD CONSTRAINT `bepms_email_forgot_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `bepms_users` (`user_id`);

--
-- Constraints for table `bepms_notices`
--
ALTER TABLE `bepms_notices`
  ADD CONSTRAINT `bepms_notices_ibfk_1` FOREIGN KEY (`from_user_id`) REFERENCES `bepms_users` (`user_id`),
  ADD CONSTRAINT `bepms_notices_ibfk_2` FOREIGN KEY (`project_id`) REFERENCES `bepms_projects` (`project_id`),
  ADD CONSTRAINT `bepms_notices_ibfk_3` FOREIGN KEY (`to_user_id`) REFERENCES `bepms_users` (`user_id`);

--
-- Constraints for table `bepms_projects`
--
ALTER TABLE `bepms_projects`
  ADD CONSTRAINT `bepms_projects_ibfk_1` FOREIGN KEY (`system_id`) REFERENCES `bepms_systems` (`system_id`);

--
-- Constraints for table `bepms_project_member_positions`
--
ALTER TABLE `bepms_project_member_positions`
  ADD CONSTRAINT `bepms_project_member_positions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `bepms_users` (`user_id`),
  ADD CONSTRAINT `bepms_project_member_positions_ibfk_2` FOREIGN KEY (`project_id`) REFERENCES `bepms_projects` (`project_id`),
  ADD CONSTRAINT `bepms_project_member_positions_ibfk_3` FOREIGN KEY (`project_position_id`) REFERENCES `bepms_project_positions` (`project_position_id`);

--
-- Constraints for table `bepms_reports`
--
ALTER TABLE `bepms_reports`
  ADD CONSTRAINT `bepms_reports_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `bepms_projects` (`project_id`);

--
-- Constraints for table `bepms_systems`
--
ALTER TABLE `bepms_systems`
  ADD CONSTRAINT `bepms_systems_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `bepms_admins` (`admin_id`);

--
-- Constraints for table `bepms_to_dos`
--
ALTER TABLE `bepms_to_dos`
  ADD CONSTRAINT `bepms_to_dos_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `bepms_projects` (`project_id`);

--
-- Constraints for table `bepms_to_do_members_assign`
--
ALTER TABLE `bepms_to_do_members_assign`
  ADD CONSTRAINT `bepms_to_do_members_assign_ibfk_1` FOREIGN KEY (`to_do_member_assign_id`) REFERENCES `bepms_users` (`user_id`),
  ADD CONSTRAINT `bepms_to_do_members_assign_ibfk_2` FOREIGN KEY (`to_do_id`) REFERENCES `bepms_to_dos` (`to_do_id`);

--
-- Constraints for table `bepms_usermeta`
--
ALTER TABLE `bepms_usermeta`
  ADD CONSTRAINT `bepms_usermeta_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `bepms_users` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
