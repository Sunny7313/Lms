-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 10, 2025 at 07:36 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

CREATE DATABASE IF NOT EXISTS `library`;
USE `library`;

--
-- Database: `library`
--

-- --------------------------------------------------------

--
-- Table structure for table `books`
--


CREATE TABLE `books` (
  `book_id` varchar(10) NOT NULL,
  `book_name` varchar(255) NOT NULL,
  `author_name` varchar(255) NOT NULL,
  `published_date` date NOT NULL,
  `branch` varchar(255) NOT NULL,
  `category` varchar(255) NOT NULL,
  `book_cover` varchar(255) NOT NULL,
  `borrow_count` int(11) DEFAULT 0,
  `stock` int(11) NOT NULL,
  `rack` varchar(255) NOT NULL,
  `description` varchar(10000) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `books`
--

INSERT INTO `books` (`book_id`, `book_name`, `author_name`, `published_date`, `branch`, `category`, `book_cover`, `borrow_count`, `stock`, `rack`, `description`) VALUES
('CSE101', 'Introduction to Algorithms', 'Thomas H. Cormen', '2009-07-31', 'CSE', 'Algorithms', 'https://m.media-amazon.com/images/I/71kBRLo8VDL.jpg', 0, 10, 'A1', 'Comprehensive book on algorithms.'),
('CSE102', 'Artificial Intelligence: A Modern Approach', 'Stuart Russell, Peter Norvig', '2020-04-28', 'CSE', 'AI', 'https://m.media-amazon.com/images/I/81PY0LKmbHL.jpg', 0, 10, 'A2', 'Standard AI textbook.'),
('CSE103', 'Computer Networking: A Top-Down Approach', 'Kurose & Ross', '2021-03-09', 'CSE', 'Networks', 'https://m.media-amazon.com/images/I/81kOLFGFpRL.jpg', 0, 10, 'A3', 'Covers computer networking concepts.'),
('CSE104', 'Operating System Concepts', 'Abraham Silberschatz', '2018-02-26', 'CSE', 'OS', 'https://m.media-amazon.com/images/I/81wN4y9w4HL.jpg', 0, 10, 'A4', 'Comprehensive guide to OS concepts.'),
('CSE105', 'Database System Concepts', 'Henry F. Korth', '2019-10-14', 'CSE', 'Databases', 'https://m.media-amazon.com/images/I/71bXH4vxHZL.jpg', 0, 10, 'A5', 'Fundamentals of database management.'),
('CSE106', 'Computer Organization and Design', 'David A. Patterson', '2021-01-12', 'CSE', 'Computer Architecture', 'https://m.media-amazon.com/images/I/71JffF3tmxL.jpg', 0, 10, 'A6', 'Concepts of computer organization.'),
('CSE107', 'The Art of Computer Programming', 'Donald Knuth', '2011-07-10', 'CSE', 'Programming', 'https://m.media-amazon.com/images/I/71iC-k1m+yL.jpg', 0, 10, 'A7', 'A deep dive into algorithms and coding.'),
('CSE108', 'Python Crash Course', 'Eric Matthes', '2019-05-03', 'CSE', 'Programming', 'https://m.media-amazon.com/images/I/71Mu9B1g49L.jpg', 0, 10, 'A8', 'Hands-on guide to learning Python.'),
('CSE109', 'Deep Learning', 'Ian Goodfellow', '2016-11-18', 'CSE', 'AI', 'https://m.media-amazon.com/images/I/71tbalAHYCL.jpg', 0, 10, 'A9', 'Fundamentals of deep learning.'),
('CSE110', 'Software Engineering', 'Ian Sommerville', '2020-09-21', 'CSE', 'Software Engineering', 'https://m.media-amazon.com/images/I/81rxSHPuO8L.jpg', 0, 10, 'A10', 'Concepts of software development.'),
('MECH101', 'Engineering Mechanics', 'J.L. Meriam', '2017-05-15', 'MECH', 'Mechanics', 'https://m.media-amazon.com/images/I/81h2txXWYzL.jpg', 0, 10, 'M1', 'Fundamentals of engineering mechanics.'),
('MECH102', 'Machine Design', 'Robert L. Norton', '2020-02-10', 'MECH', 'Design', 'https://m.media-amazon.com/images/I/81Lsz7rdwmL.jpg', 0, 10, 'M2', 'Detailed study on machine design principles.'),
('MECH103', 'Fluid Mechanics', 'Frank M. White', '2018-09-25', 'MECH', 'Fluid Mechanics', 'https://m.media-amazon.com/images/I/81DW0Ob0ySL.jpg', 0, 10, 'M3', 'Comprehensive guide on fluid dynamics.'),
('MECH104', 'Thermodynamics: An Engineering Approach', 'Yunus A. Çengel', '2019-06-21', 'MECH', 'Thermodynamics', 'https://m.media-amazon.com/images/I/81Q2UHdN50L.jpg', 0, 10, 'M4', 'Fundamentals of thermodynamics in engineering.'),
('MECH105', 'Manufacturing Engineering & Technology', 'Serope Kalpakjian', '2021-03-19', 'MECH', 'Manufacturing', 'https://m.media-amazon.com/images/I/71ECWBFF+LL.jpg', 0, 10, 'M5', 'Covers modern manufacturing techniques.'),
('MECH106', 'Kinematics and Dynamics of Machinery', 'Charles E. Wilson', '2016-11-05', 'MECH', 'Dynamics', 'https://m.media-amazon.com/images/I/81LFTqKXNzL.jpg', 0, 10, 'M6', 'Explores the motion of machines and mechanisms.'),
('MECH107', 'Heat and Mass Transfer', 'Yunus A. Çengel', '2020-07-12', 'MECH', 'Heat Transfer', 'https://m.media-amazon.com/images/I/81H5bcPZZgL.jpg', 0, 10, 'M7', 'Concepts of heat and mass transfer.'),
('MECH108', 'Strength of Materials', 'R.K. Bansal', '2017-04-30', 'MECH', 'Materials', 'https://m.media-amazon.com/images/I/71eNy1CnUdL.jpg', 0, 10, 'M8', 'Covers stress, strain, and material properties.'),
('MECH109', 'Introduction to Robotics: Mechanics and Control', 'John J. Craig', '2019-09-15', 'MECH', 'Robotics', 'https://m.media-amazon.com/images/I/81yoa9X65JL.jpg', 0, 10, 'M9', 'Detailed concepts of robotics and automation.'),
('MECH110', 'Engineering Drawing and Graphics', 'K. Venugopal', '2018-08-08', 'MECH', 'Drawing', 'https://m.media-amazon.com/images/I/71ixBZ63DML.jpg', 0, 10, 'M10', 'Fundamentals of technical drawings.'),
('ECE101', 'Electronic Devices and Circuit Theory', 'Robert L. Boylestad', '2017-03-10', 'ECE', 'Electronics', 'https://m.media-amazon.com/images/I/71Fj-MrbP7L.jpg', 0, 10, 'E1', 'Fundamentals of electronic devices and circuits.'),
('ECE102', 'Digital Design', 'M. Morris Mano', '2018-05-22', 'ECE', 'Digital Electronics', 'https://m.media-amazon.com/images/I/71kFsgPXuTL.jpg', 0, 10, 'E2', 'Covers digital logic design principles.'),
('ECE103', 'Microelectronics Circuit Analysis and Design', 'Donald A. Neamen', '2019-07-15', 'ECE', 'Microelectronics', 'https://m.media-amazon.com/images/I/81p7CjyGpPL.jpg', 0, 10, 'E3', 'Comprehensive study of microelectronics circuits.'),
('ECE104', 'Signals and Systems', 'Alan V. Oppenheim', '2016-12-18', 'ECE', 'Signal Processing', 'https://m.media-amazon.com/images/I/81BhFjDGTJL.jpg', 0, 10, 'E4', 'Concepts of signals and systems with applications.'),
('ECE105', 'Communication Systems', 'Simon Haykin', '2020-09-20', 'ECE', 'Communication', 'https://m.media-amazon.com/images/I/71cqCX3ETEL.jpg', 0, 10, 'E5', 'Explores modern communication systems and networks.'),
('ECE106', 'Antenna Theory: Analysis and Design', 'Constantine A. Balanis', '2017-06-11', 'ECE', 'Antenna & Wave Propagation', 'https://m.media-amazon.com/images/I/81C6rfCDy7L.jpg', 0, 10, 'E6', 'In-depth study of antenna design and applications.'),
('ECE107', 'Digital Signal Processing', 'John G. Proakis', '2019-11-08', 'ECE', 'DSP', 'https://m.media-amazon.com/images/I/81em7hz2I1L.jpg', 0, 10, 'E7', 'Introduction to digital signal processing techniques.'),
('ECE108', 'VLSI Design', 'Douglas A. Pucknell', '2021-02-14', 'ECE', 'VLSI', 'https://m.media-amazon.com/images/I/81qA6kZZveL.jpg', 0, 10, 'E8', 'Covers VLSI circuit design and applications.'),
('ECE109', 'Embedded Systems', 'Raj Kamal', '2018-04-25', 'ECE', 'Embedded Systems', 'https://m.media-amazon.com/images/I/71n+KAFvXWL.jpg', 0, 10, 'E9', 'Explains embedded system concepts and architectures.'),
('ECE110', 'Wireless Communications', 'Andrea Goldsmith', '2020-08-05', 'ECE', 'Wireless', 'https://m.media-amazon.com/images/I/81CDcDh7E7L.jpg', 0, 10, 'E10', 'Principles of wireless communication networks.'),
('EEE101', 'Electrical Machines', 'P.S. Bimbhra', '2018-07-15', 'EEE', 'Machines', 'https://m.media-amazon.com/images/I/71w5XJ+K-BL.jpg', 0, 10, 'EE1', 'Covers electrical machine fundamentals and applications.'),
('EEE102', 'Power System Engineering', 'Nagrath & Kothari', '2019-05-22', 'EEE', 'Power Systems', 'https://m.media-amazon.com/images/I/81HC-JBdAEL.jpg', 0, 10, 'EE2', 'Fundamentals of power systems and network analysis.'),
('EEE103', 'Control Systems Engineering', 'Norman S. Nise', '2020-09-10', 'EEE', 'Control Systems', 'https://m.media-amazon.com/images/I/81XpJJm8VLL.jpg', 0, 10, 'EE3', 'Principles of automatic control systems and applications.'),
('EEE104', 'Electric Circuits', 'James W. Nilsson', '2017-03-18', 'EEE', 'Circuits', 'https://m.media-amazon.com/images/I/81u82mT4DtL.jpg', 0, 10, 'EE4', 'Comprehensive introduction to electric circuit theory.'),
('EEE105', 'Power Electronics', 'M.D. Singh & K.B. Khanchandani', '2018-11-05', 'EEE', 'Power Electronics', 'https://m.media-amazon.com/images/I/71IXodMHsML.jpg', 0, 10, 'EE5', 'Covers power semiconductor devices and converters.'),
('EEE106', 'Electrical Measurements & Instrumentation', 'A.K. Sawhney', '2016-06-25', 'EEE', 'Instrumentation', 'https://m.media-amazon.com/images/I/91nVmgIlxnL.jpg', 0, 10, 'EE6', 'Techniques for measuring electrical quantities.'),
('EEE107', 'Renewable Energy Sources', 'G.D. Rai', '2021-02-14', 'EEE', 'Renewable Energy', 'https://m.media-amazon.com/images/I/91k5a9AjtrL.jpg', 0, 10, 'EE7', 'Explains different renewable energy generation methods.'),
('EEE108', 'Electric Power Generation, Transmission and Distribution', 'S.N. Singh', '2019-08-22', 'EEE', 'Power Systems', 'https://m.media-amazon.com/images/I/81zNvVzTKJL.jpg', 0, 10, 'EE8', 'Covers the basics of electric power engineering.'),
('EEE109', 'Electrical & Electronic Materials', 'A.J. Dekker', '2018-12-10', 'EEE', 'Materials', 'https://m.media-amazon.com/images/I/81MvFnspNqL.jpg', 0, 10, 'EE9', 'Discusses electrical properties of different materials.'),
('EEE110', 'High Voltage Engineering', 'M.S. Naidu & V. Kamaraju', '2020-10-05', 'EEE', 'High Voltage', 'https://m.media-amazon.com/images/I/81TZzOW0gML.jpg', 0, 10, 'EE10', 'Concepts of high voltage generation and insulation.'),
('CIV101', 'Building Materials', 'S.K. Duggal', '2018-04-10', 'Civil', 'Materials', 'https://m.media-amazon.com/images/I/71Rl7tnJQ+L.jpg', 0, 10, 'CIV1', 'Comprehensive guide on civil engineering materials.'),
('CIV102', 'Structural Analysis', 'R.C. Hibbeler', '2019-06-15', 'Civil', 'Structures', 'https://m.media-amazon.com/images/I/81c6xM1sIUL.jpg', 0, 10, 'CIV2', 'Fundamentals of structural analysis and mechanics.'),
('CIV103', 'Surveying Vol. 1', 'B.C. Punmia', '2020-08-20', 'Civil', 'Surveying', 'https://m.media-amazon.com/images/I/81phz9w82hL.jpg', 0, 10, 'CIV3', 'Techniques for land surveying and measurement.'),
('CIV104', 'Concrete Technology', 'M.S. Shetty', '2017-03-05', 'Civil', 'Concrete', 'https://m.media-amazon.com/images/I/81YPX8HGcdL.jpg', 0, 10, 'CIV4', 'Covers properties and testing of concrete.'),
('CIV105', 'Geotechnical Engineering', 'C.V. Venkatramaiah', '2018-09-12', 'Civil', 'Geotechnical', 'https://m.media-amazon.com/images/I/81JGOBD2wBL.jpg', 0, 10, 'CIV5', 'Study of soil mechanics and foundation engineering.'),
('CIV106', 'Transportation Engineering', 'C.E.G. Justo & S.K. Khanna', '2019-11-30', 'Civil', 'Transportation', 'https://m.media-amazon.com/images/I/81I4cmB3LfL.jpg', 0, 10, 'CIV6', 'Concepts of highway and traffic engineering.'),
('CIV107', 'Water Resources Engineering', 'Larry W. Mays', '2021-02-10', 'Civil', 'Hydraulics', 'https://m.media-amazon.com/images/I/81EBOk-e2BL.jpg', 0, 10, 'CIV7', 'Water management and hydraulic engineering principles.'),
('CIV108', 'Environmental Engineering', 'Howard S. Peavy', '2016-07-18', 'Civil', 'Environmental', 'https://m.media-amazon.com/images/I/81ZV7dFbpeL.jpg', 0, 10, 'CIV8', 'Concepts of environmental pollution and waste treatment.'),
('CIV109', 'Construction Planning & Management', 'P.S. Gahlot', '2020-10-22', 'Civil', 'Management', 'https://m.media-amazon.com/images/I/81-VbV2TZDL.jpg', 0, 10, 'CIV9', 'Strategies for efficient construction project management.'),
('CIV110', 'Design of Steel Structures', 'N. Subramanian', '2018-05-14', 'Civil', 'Structures', 'https://m.media-amazon.com/images/I/81B-DvCBFyL.jpg', 0, 10, 'CIV10', 'Covers steel design concepts and structural integrity.'),
('MBA101', 'Principles of Management', 'Harold Koontz', '2019-04-10', 'MBA', 'Management', 'https://m.media-amazon.com/images/I/71+X0b6UdJL.jpg', 0, 10, 'MBA1', 'Fundamental principles and concepts of management.'),
('MBA102', 'Marketing Management', 'Philip Kotler', '2020-06-15', 'MBA', 'Marketing', 'https://m.media-amazon.com/images/I/81c6xM1sIUL.jpg', 0, 10, 'MBA2', 'Comprehensive guide on marketing strategies and concepts.'),
('MBA103', 'Financial Management', 'Prasanna Chandra', '2021-08-20', 'MBA', 'Finance', 'https://m.media-amazon.com/images/I/81phz9w82hL.jpg', 0, 10, 'MBA3', 'Key financial decision-making and corporate finance principles.'),
('MBA104', 'Human Resource Management', 'Gary Dessler', '2018-03-05', 'MBA', 'HR', 'https://m.media-amazon.com/images/I/81YPX8HGcdL.jpg', 0, 10, 'MBA4', 'Effective strategies in managing human resources.'),
('MBA105', 'Operations Research', 'J.K. Sharma', '2019-09-12', 'MBA', 'Operations', 'https://m.media-amazon.com/images/I/81JGOBD2wBL.jpg', 0, 10, 'MBA5', 'Mathematical and analytical methods in decision-making.'),
('MBA106', 'Business Analytics', 'James Evans', '2020-11-30', 'MBA', 'Analytics', 'https://m.media-amazon.com/images/I/81I4cmB3LfL.jpg', 0, 10, 'MBA6', 'Understanding business data analysis and decision-making.'),
('MBA107', 'Strategic Management', 'Michael A. Hitt', '2021-02-10', 'MBA', 'Strategy', 'https://m.media-amazon.com/images/I/81EBOk-e2BL.jpg', 0, 10, 'MBA7', 'Strategic planning, implementation, and evaluation.'),
('MBA108', 'Entrepreneurship Development', 'S.S. Khanka', '2016-07-18', 'MBA', 'Entrepreneurship', 'https://m.media-amazon.com/images/I/81ZV7dFbpeL.jpg', 0, 10, 'MBA8', 'Entrepreneurial skills, business planning, and startups.'),
('MBA109', 'International Business', 'Charles W.L. Hill', '2020-10-22', 'MBA', 'International', 'https://m.media-amazon.com/images/I/81-VbV2TZDL.jpg', 0, 10, 'MBA9', 'Global trade, foreign investment, and business strategy.'),
('MBA110', 'Corporate Governance', 'Robert A. Monks', '2018-05-14', 'MBA', 'Corporate', 'https://m.media-amazon.com/images/I/81B-DvCBFyL.jpg', 0, 10, 'MBA10', 'Ethics, responsibilities, and corporate governance policies.'),
('GEN101', 'The Art of Public Speaking', 'Dale Carnegie', '2019-06-10', 'General', 'Communication', 'https://m.media-amazon.com/images/I/71ne+B2oK9L.jpg', 0, 10, 'GEN1', 'A guide to effective communication and public speaking skills.'),
('GEN102', 'Thinking, Fast and Slow', 'Daniel Kahneman', '2011-10-25', 'General', 'Psychology', 'https://m.media-amazon.com/images/I/81FePec3sCL.jpg', 0, 10, 'GEN2', 'Explains cognitive biases and decision-making processes.'),
('GEN103', 'Atomic Habits', 'James Clear', '2018-10-16', 'General', 'Self-Help', 'https://m.media-amazon.com/images/I/91bYsX41DVL.jpg', 0, 10, 'GEN3', 'Strategies for habit formation and personal growth.'),
('GEN104', 'The Power of Now', 'Eckhart Tolle', '1997-08-19', 'General', 'Mindfulness', 'https://m.media-amazon.com/images/I/71t4GuxLCuL.jpg', 0, 10, 'GEN4', 'Teaches mindfulness and living in the present moment.'),
('GEN105', 'How to Win Friends and Influence People', 'Dale Carnegie', '1936-10-01', 'General', 'Personal Development', 'https://m.media-amazon.com/images/I/71wF8RTuGFL.jpg', 0, 10, 'GEN5', 'Essential guide to communication and leadership skills.'),
('GEN106', 'The Lean Startup', 'Eric Ries', '2011-09-13', 'General', 'Entrepreneurship', 'https://m.media-amazon.com/images/I/81-QB7nDh4L.jpg', 0, 10, 'GEN6', 'Build and grow a startup with lean business strategies.'),
('GEN107', 'Zero to One', 'Peter Thiel', '2014-09-16', 'General', 'Business', 'https://m.media-amazon.com/images/I/71m-MxdJ2WL.jpg', 0, 10, 'GEN7', 'Insights on innovation, startups, and building the future.'),
('GEN108', 'Sapiens: A Brief History of Humankind', 'Yuval Noah Harari', '2011-09-04', 'General', 'History', 'https://m.media-amazon.com/images/I/713jIoMO3UL.jpg', 0, 10, 'GEN8', 'Explores the history and impact of Homo sapiens.'),
('GEN109', 'Rich Dad Poor Dad', 'Robert Kiyosaki', '1997-04-01', 'General', 'Finance', 'https://m.media-amazon.com/images/I/81bsw6fnUiL.jpg', 0, 10, 'GEN9', 'Lessons on financial literacy and wealth-building.'),
('GEN110', 'A Brief History of Time', 'Stephen Hawking', '1988-04-01', 'General', 'Science', 'https://m.media-amazon.com/images/I/91ssZL0P0bL.jpg', 0, 10, 'GEN10', 'Explains complex physics concepts in an accessible way.');


CREATE TABLE `book_requests` (
  `request_id` varchar(10) NOT NULL,
  `book_id` varchar(10) NOT NULL,
  `user_id` varchar(10) NOT NULL,
  `status` varchar(50) DEFAULT '',
  `request_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



CREATE TABLE `borrow_list` (
  `borrow_id` varchar(10) NOT NULL,
  `book_id` varchar(10) NOT NULL,
  `member_id` varchar(10) NOT NULL,
  `borrow_date` date NOT NULL,
  `return_date` date NOT NULL,
  `stock` int(11) NOT NULL,
  `status` varchar(50) DEFAULT 'Borrowed'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



CREATE TABLE `fines` (
  `fine_id` varchar(10) NOT NULL,
  `book_id` varchar(10) NOT NULL,
  `fine_amount` decimal(10,2) NOT NULL,
  `due_date` date NOT NULL,
  `pin_number` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `member_list` (
  `pin_number` varchar(10) NOT NULL,
  `email` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) NOT NULL,
  `phone_number` varchar(20) NOT NULL,
  `branch` varchar(255) NOT NULL,
  `section` varchar(255) NOT NULL,
  `year` int(11) NOT NULL,
  `gender` varchar(36) NOT NULL,
  `address` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
--
-- Indexes for table `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`book_id`);

--
-- Indexes for table `book_requests`
--
ALTER TABLE `book_requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `book_id` (`book_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `borrow_list`
--
ALTER TABLE `borrow_list`
  ADD PRIMARY KEY (`borrow_id`),
  ADD KEY `book_id` (`book_id`),
  ADD KEY `member_id` (`member_id`);

--
-- Indexes for table `fines`
--
ALTER TABLE `fines`
  ADD PRIMARY KEY (`fine_id`),
  ADD KEY `pin_number` (`pin_number`),
  ADD KEY `book_id` (`book_id`);

--
-- Indexes for table `member_list`
--
ALTER TABLE `member_list`
  ADD PRIMARY KEY (`pin_number`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `book_requests`
--
ALTER TABLE `book_requests`
  ADD CONSTRAINT `book_requests_ibfk_1` FOREIGN KEY (`book_id`) REFERENCES `books` (`book_id`),
  ADD CONSTRAINT `book_requests_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `member_list` (`pin_number`);

--
-- Constraints for table `borrow_list`
--
ALTER TABLE `borrow_list`
  ADD CONSTRAINT `borrow_list_ibfk_1` FOREIGN KEY (`book_id`) REFERENCES `books` (`book_id`),
  ADD CONSTRAINT `borrow_list_ibfk_2` FOREIGN KEY (`member_id`) REFERENCES `member_list` (`pin_number`);

--
-- Constraints for table `fines`
--
ALTER TABLE `fines`
  ADD CONSTRAINT `fines_ibfk_1` FOREIGN KEY (`pin_number`) REFERENCES `member_list` (`pin_number`),
  ADD CONSTRAINT `fines_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`book_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
