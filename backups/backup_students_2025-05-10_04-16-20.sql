CREATE TABLE `students` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `course` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO students VALUES ('8', 'DAYANANDA THOKCHOM', 'dayananda@gamil.com', '9863032932', 'cse', '2025-05-10 02:38:48');
INSERT INTO students VALUES ('9', 'ABINASH HEISHNAM', 'abinash@gmail.com', '987654098', 'cse', '2025-05-10 02:45:13');
INSERT INTO students VALUES ('10', 'VIKTAR LEIMAPOKPAM', 'victro@gmail.com', '9876501233', 'cse', '2025-05-10 03:17:03');
INSERT INTO students VALUES ('11', 'BORNISON OKRAM', 'bornison@gmail.com', '293746501', 'cse', '2025-05-10 04:05:52');
INSERT INTO students VALUES ('12', 'JOYCHAND YUMLEMBAM', 'joychand@gmail.com', '1029384756', 'ME', '2025-05-10 04:06:43');
INSERT INTO students VALUES ('13', 'ROHIT ANGOM', 'ROG@gmail.com', '1029387456', 'CIVIL', '2025-05-10 04:07:57');
INSERT INTO students VALUES ('14', 'RICKY WAHENGBAM', 'ricky@gmail.com', '1092384756', 'EE', '2025-05-10 04:08:51');
INSERT INTO students VALUES ('15', 'SACHIN ASEM', 'sachin@gmail.com', '', 'CSE', '2025-05-10 04:10:16');
INSERT INTO students VALUES ('16', 'LAMJINGBA ', 'lamjing@gmil.com', '', 'CSE', '2025-05-10 04:12:03');
INSERT INTO students VALUES ('17', 'DHANACHAND', 'dha@gmail.com', '', '2198374654', '2025-05-10 04:13:06');
INSERT INTO students VALUES ('18', 'BIRJIT KHAIDEM', 'BIR@gamcil.com', '', 'ME', '2025-05-10 04:14:37');
