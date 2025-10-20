-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 20, 2025 at 09:34 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `osfms`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(255) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fees`
--

CREATE TABLE `fees` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `term` varchar(50) NOT NULL,
  `amount_due` decimal(10,2) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `amount_paid` decimal(10,2) DEFAULT 0.00,
  `total_amount` varchar(100) DEFAULT 'N/A',
  `due_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(20) DEFAULT 'Pending',
  `term_id` int(11) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT 'N/A'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `fees`
--

INSERT INTO `fees` (`id`, `student_id`, `term`, `amount_due`, `description`, `amount_paid`, `total_amount`, `due_date`, `created_at`, `status`, `term_id`, `payment_method`) VALUES
(9, 1, '', 10000.00, '', 0.00, 'N/A', NULL, '2025-10-09 23:18:19', 'pending', NULL, 'N/A'),
(10, 2, '', 15000.00, '', 0.00, 'N/A', NULL, '2025-10-09 23:20:23', 'paid', NULL, 'N/A'),
(11, 1, '', 5000.00, '', 0.00, 'N/A', NULL, '2025-10-09 23:21:08', 'paid', NULL, 'N/A'),
(12, 3, '', 10000.00, '', 0.00, 'N/A', NULL, '2025-10-09 23:21:59', 'paid', NULL, 'N/A'),
(13, 3, '', 5000.00, '', 0.00, 'N/A', NULL, '2025-10-09 23:22:17', 'paid', NULL, 'N/A'),
(14, 28, '', 2000.00, '', 0.00, 'N/A', NULL, '2025-10-13 15:45:47', 'paid', NULL, 'N/A'),
(15, 31, '', 20000.00, '', 0.00, 'N/A', NULL, '2025-10-13 22:54:54', 'Pending', NULL, 'N/A'),
(16, 31, '', 1000.00, '', 0.00, 'N/A', NULL, '2025-10-13 22:56:31', 'Pending', NULL, 'N/A'),
(17, 31, '', 2000.00, 'kindly pay your fees to avoid inconviences', 0.00, 'N/A', NULL, '2025-10-14 00:22:47', 'Pending', NULL, 'N/A'),
(18, 29, '', 20000.00, '', 0.00, 'N/A', NULL, '2025-10-14 07:04:58', 'Pending', NULL, 'N/A'),
(19, 32, '', 20000.00, '', 0.00, 'N/A', NULL, '2025-10-18 17:22:50', 'paid', NULL, 'N/A');

-- --------------------------------------------------------

--
-- Table structure for table `fee_structure`
--

CREATE TABLE `fee_structure` (
  `id` int(11) NOT NULL,
  `class` varchar(50) NOT NULL,
  `term` varchar(50) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `year` year(4) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `id` int(11) NOT NULL,
  `invoice_ref` varchar(50) NOT NULL,
  `student_id` int(11) NOT NULL,
  `amount_due` decimal(10,2) NOT NULL,
  `is_paid` tinyint(1) NOT NULL DEFAULT 0,
  `due_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(100) DEFAULT NULL,
  `message` text NOT NULL,
  `role` enum('student','parent','all') DEFAULT 'all',
  `type` enum('payment','reminder','general') DEFAULT 'general',
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `parent_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `parents`
--

CREATE TABLE `parents` (
  `parent_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `address` varchar(200) DEFAULT NULL,
  `parent_national_id` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `parents`
--

INSERT INTO `parents` (`parent_id`, `full_name`, `email`, `phone`, `password`, `created_at`, `address`, `parent_national_id`) VALUES
(13, '', '', '0790876656', '', '2025-10-09 21:54:57', 'kimbo', ''),
(20, '', 'fear@gmail.com', '0790876656', '$2y$10$B.z.yEEnVN4sw3rSyOs.BelLj6zLo.o39sWIr.kdbY4zxNcMRMn2W', '2025-10-09 22:22:33', 'nairobi', ''),
(27, '', 'kun@gmail.com', '0707654148', '$2y$10$1x8hLj5Qvj49UFbVT0lhoud34AXsVkc0Bfsn3N6d5LfiezKbKa2yi', '2025-10-13 12:38:32', 'ruiru', ''),
(29, '', 'manu@gmail.com', '0888009996', '$2y$10$kgFnpaQfIcKdbsNBaN.m..NJEhdU2Ce4dFedB9/f3X3dIhxrPSidi', '2025-10-13 14:32:47', 'ruiru', '40404040');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `user_id`, `token`, `expires_at`) VALUES
(4, 2, '0b71a67abc713a7da0dae0794a4d94a81ebbbc9b8fd83e9caf1b61b927d32497', '2025-10-13 22:35:44');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `fee_id` int(11) DEFAULT NULL,
  `amount_paid` decimal(10,2) DEFAULT NULL,
  `payment_method` varchar(50) NOT NULL DEFAULT 'N/A',
  `method` varchar(50) DEFAULT NULL,
  `payment_date` datetime DEFAULT current_timestamp(),
  `transaction_ref` varchar(50) DEFAULT NULL,
  `status` enum('Paid','Pending','Failed') DEFAULT 'Paid',
  `reference` varchar(100) DEFAULT NULL,
  `term_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `student_id`, `fee_id`, `amount_paid`, `payment_method`, `method`, `payment_date`, `transaction_ref`, `status`, `reference`, `term_id`) VALUES
(1, 2, NULL, 2000.00, 'N/A', NULL, '2025-10-13 16:46:50', NULL, 'Paid', NULL, NULL),
(2, 1, NULL, 2000.00, 'N/A', NULL, '2025-10-13 16:47:11', NULL, 'Paid', NULL, NULL),
(3, 2, NULL, 6000.00, 'N/A', NULL, '2025-10-18 10:26:17', NULL, 'Paid', NULL, NULL),
(4, 1, NULL, 8000.00, 'N/A', NULL, '2025-10-18 10:26:38', NULL, 'Paid', NULL, NULL),
(5, 31, NULL, 2000.00, 'N/A', NULL, '2025-10-19 11:14:20', NULL, 'Paid', NULL, NULL),
(6, 2, NULL, 1000.00, 'N/A', NULL, '2025-10-19 11:22:47', NULL, 'Paid', NULL, NULL),
(7, 27, NULL, 10000.00, 'N/A', NULL, '2025-10-20 12:29:39', NULL, 'Paid', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `receipts`
--

CREATE TABLE `receipts` (
  `id` int(11) NOT NULL,
  `payment_id` int(11) NOT NULL,
  `receipt_number` varchar(100) NOT NULL,
  `issued_by` varchar(100) DEFAULT NULL,
  `issued_date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `role`
--

CREATE TABLE `role` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `role`
--

INSERT INTO `role` (`id`, `name`) VALUES
(1, 'admin'),
(2, 'student'),
(3, 'parent');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `admission_no` varchar(50) DEFAULT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `class` varchar(50) DEFAULT NULL,
  `date_registered` datetime DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `balance` decimal(10,2) DEFAULT 0.00,
  `parent_national_id` bigint(20) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `admission_no`, `full_name`, `email`, `class`, `date_registered`, `created_at`, `balance`, `parent_national_id`, `parent_id`) VALUES
(1, '120319', 'Benard', NULL, 'form 2', '2025-10-09 15:56:37', '2025-10-09 22:56:37', 0.00, 40404040, 29),
(2, '120319', 'Davi', NULL, 'form 2', '2025-10-09 16:19:54', '2025-10-09 23:19:54', 0.00, 40404040, 29),
(3, '120938', 'feii', NULL, 'form 3', '2025-10-09 16:21:41', '2025-10-09 23:21:41', 0.00, 40404040, NULL),
(27, '1209878', 'Davis', 'davis@gmail.com', 'form 2', '2025-10-13 08:44:43', '2025-10-13 15:44:43', 20000.00, NULL, 27),
(28, '12980', 'Jane', 'jane@gmail.com', 'form 2', '2025-10-13 08:45:12', '2025-10-13 15:45:12', 0.00, NULL, NULL),
(29, '1234567', 'Alice', 'alice@gmail.com', 'Form 3', '2025-10-13 08:57:05', '2025-10-13 15:57:05', 0.00, NULL, NULL),
(31, '1222222', 'jrmanu', 'jrmanu@gmail.com', 'form 2', '2025-10-13 14:46:10', '2025-10-13 21:46:10', 20000.00, 40404040, 6),
(32, '5467854', 'lonah', 'lonah@gmail.com', 'Form 4', '2025-10-18 10:22:26', '2025-10-18 17:22:26', 0.00, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `student_parent`
--

CREATE TABLE `student_parent` (
  `student_id` int(11) NOT NULL,
  `parent_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `terms`
--

CREATE TABLE `terms` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `payment_id` int(11) NOT NULL,
  `transaction_type` enum('credit','debit') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','parent','student') NOT NULL DEFAULT 'student',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `username`, `password`, `role`, `created_at`) VALUES
(1, 'Benard', 'ben@gmailcom', 'ben', '$2y$10$GXKX7CjTPYAfLuFQMrCis.4LSqAvEXy/u4UvTxFHLO59gwDjtAHOS', 'admin', '2025-10-04 19:24:17'),
(2, 'John', 'john@gmail.com', 'John', '$2y$10$BfAHsF8uyOpioHoavjKZF.dMleOqbrLFoKvdsd1wi.7oA8QDik0EK', 'admin', '2025-10-04 20:04:06'),
(3, 'blondy', 'blondy@gmailcom', 'Ochieng', '$2y$10$C1vff5oPptdYthWuFNHrpOS8end4fHnP/P8YTUB9NnWbFW7zdCBOq', 'parent', '2025-10-04 20:26:32'),
(4, 'lonah', 'lonah@gmail.com', 'lonah@123', '$2y$10$nQJMHhq3/ViUJU5IEH/M6uXJT9hMmz3479NEiB224gDEvlzTZGaYO', 'student', '2025-10-06 06:29:37'),
(5, 'lakwena', 'lakwena@gmailcom', 'Alice', '$2y$10$UlTq/ZKAqVFHE5kag1ss6uLA.yd14bHP98gkeT33XlrdJNYUy5E8S', 'student', '2025-10-06 22:14:05'),
(6, 'sai', 'sai@gmail.com', 'kunn', '$2y$10$cWJeQtebhSSSjdzrHNOVHOqssaaMZyUQbZJzD3fqnoayI1EYFVzLe', 'parent', '2025-10-06 22:18:49'),
(9, 'faith', 'faith@gmail.com', 'feii', '$2y$10$AKvQJGKh/c/RHOJiRtcGAuhsSsXOK8WySqwg6pwmR0dY52x276wGu', 'student', '2025-10-08 04:08:30'),
(10, 'vivian', 'vivian@gmailcom', 'vivi', '$2y$10$UPaTze0pVl6CFDSBS99VGu7VIwTTK6b3Q5nDkMrU7OUalreszAo.a', 'parent', '2025-10-08 04:10:38'),
(13, '', 'Getrude@gmail.com', 'GG', '$2y$10$BQiJlEH2WI9d/Z2uiS/UguU4LFJpM6lBdxAZEc4LFPuIDyY9mrcCC', 'parent', '2025-10-10 04:54:57'),
(20, 'fear', 'fear@gmail.com', 'fear', '$2y$10$I.PEVPfPjl72.re7/K9POOPu5I9Fc1YzYv8j/sYZPBFUfKQIzgtLa', 'parent', '2025-10-10 05:22:33'),
(21, '', 'web@gmail.com', 'web', '$2y$10$m3vExnv1HVxQXEKIvMpVDOpb5.i8CrSD.LW4kAQFyrW2oZCSdS586', 'student', '2025-10-10 05:25:08'),
(22, '', 'minor@gmail.com', 'minor', '$2y$10$V4XIy4F8hpR51oGBXva2CujqvikxzoGQxvnLZG7YhlEa3MXEoQ2iW', 'parent', '2025-10-10 05:45:52'),
(23, '', 'minordd@gmail.com', 'dd', '$2y$10$EPntxBPQ17M6Cdghwy7VAOyxfofj9JUpwkR5ZJSqPnxlNsHdxi5DK', 'parent', '2025-10-10 05:49:02'),
(26, '', 'beneee@gmailcom', 'bbbb', '$2y$10$y1HhatZn8fdA9yK1HpD8yuahqbvezr7xTrrLoTRfZmkBkaD9k8cpG', 'student', '2025-10-10 05:57:50'),
(27, 'kun', 'kun@gmail.com', 'saii', '$2y$10$lx5cB9rdCZtwXJbG1Usnv.kqzG//dXvOaukazlnu8.lMMTkrZnZli', 'parent', '2025-10-13 19:38:32'),
(29, 'manu', 'manu@gmail.com', 'manu', '$2y$10$kgFnpaQfIcKdbsNBaN.m..NJEhdU2Ce4dFedB9/f3X3dIhxrPSidi', 'parent', '2025-10-13 21:32:47'),
(31, 'jrmanu', 'jrmanu@gmail.com', 'jrmanu', '$2y$10$qiDxRFJgV4nSfU0m3R4WJ.9BbZce/k7S.B3qwABETtmdBEaMpwVVm', 'student', '2025-10-13 21:46:10');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `fees`
--
ALTER TABLE `fees`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `term_id` (`term_id`);

--
-- Indexes for table `fee_structure`
--
ALTER TABLE `fee_structure`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoice_ref` (`invoice_ref`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `parents`
--
ALTER TABLE `parents`
  ADD PRIMARY KEY (`parent_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `term_id` (`term_id`);

--
-- Indexes for table `receipts`
--
ALTER TABLE `receipts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `receipt_number` (`receipt_number`),
  ADD KEY `payment_id` (`payment_id`);

--
-- Indexes for table `role`
--
ALTER TABLE `role`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_students_parent_id` (`parent_id`);

--
-- Indexes for table `student_parent`
--
ALTER TABLE `student_parent`
  ADD PRIMARY KEY (`student_id`,`parent_id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indexes for table `terms`
--
ALTER TABLE `terms`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payment_id` (`payment_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fees`
--
ALTER TABLE `fees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `fee_structure`
--
ALTER TABLE `fee_structure`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `parents`
--
ALTER TABLE `parents`
  MODIFY `parent_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `receipts`
--
ALTER TABLE `receipts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `role`
--
ALTER TABLE `role`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `terms`
--
ALTER TABLE `terms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `fees`
--
ALTER TABLE `fees`
  ADD CONSTRAINT `fees_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fees_ibfk_2` FOREIGN KEY (`term_id`) REFERENCES `terms` (`id`);

--
-- Constraints for table `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`),
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`term_id`) REFERENCES `terms` (`id`);

--
-- Constraints for table `receipts`
--
ALTER TABLE `receipts`
  ADD CONSTRAINT `receipts_ibfk_1` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `fk_students_parent` FOREIGN KEY (`parent_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `student_parent`
--
ALTER TABLE `student_parent`
  ADD CONSTRAINT `student_parent_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `student_parent_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `parents` (`parent_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
