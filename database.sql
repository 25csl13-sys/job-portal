-- Online Job Portal System: run this file once in phpMyAdmin or the MySQL console
CREATE DATABASE IF NOT EXISTS job_portal CHARACTER SET utf8mb4;
USE job_portal;

DROP TABLE IF EXISTS applications;
DROP TABLE IF EXISTS jobs;
DROP TABLE IF EXISTS seeker_profiles;
DROP TABLE IF EXISTS employer_profiles;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(120) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  phone VARCHAR(20),
  role ENUM('seeker','employer','admin') NOT NULL,
  status ENUM('active','blocked') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE employer_profiles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL UNIQUE,
  company_name VARCHAR(120) NOT NULL,
  website VARCHAR(150),
  location VARCHAR(100),
  description TEXT,
  approval_status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE seeker_profiles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL UNIQUE,
  qualification VARCHAR(150),
  skills VARCHAR(255),
  experience VARCHAR(100),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE jobs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  employer_id INT NOT NULL,
  title VARCHAR(120) NOT NULL,
  description TEXT NOT NULL,
  location VARCHAR(100) NOT NULL,
  salary VARCHAR(60),
  job_type ENUM('Full-time','Part-time','Internship') NOT NULL,
  skills VARCHAR(255),
  posted_date DATE NOT NULL,
  last_date DATE NOT NULL,
  status ENUM('open','closed') NOT NULL DEFAULT 'open',
  FOREIGN KEY (employer_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE applications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  job_id INT NOT NULL,
  seeker_id INT NOT NULL,
  cover_note TEXT,
  applied_date DATE NOT NULL,
  status ENUM('Applied','Shortlisted','Rejected') NOT NULL DEFAULT 'Applied',
  UNIQUE KEY one_application (job_id, seeker_id),
  FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
  FOREIGN KEY (seeker_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Demo accounts: every password is "password"
INSERT INTO users (id,name,email,password,phone,role) VALUES
(1,'Site Admin','admin@jobportal.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','9000000001','admin'),
(2,'Priya Nair','hr@technova.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','9000000002','employer'),
(3,'Rahul Mehta','hr@greenleaf.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','9000000003','employer'),
(4,'Sana Khan','hr@pixelworks.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','9000000004','employer'),
(5,'Arjun Rao','arjun@mail.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','9000000005','seeker'),
(6,'Divya Iyer','divya@mail.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','9000000006','seeker'),
(7,'Karthik S','karthik@mail.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','9000000007','seeker');

INSERT INTO employer_profiles (user_id,company_name,website,location,description,approval_status) VALUES
(2,'TechNova Solutions','https://technova.example','Bengaluru','Software services company building web and mobile products.','approved'),
(3,'GreenLeaf Foods','https://greenleaf.example','Chennai','Organic food brand with its own retail outlets.','approved'),
(4,'PixelWorks Studio','https://pixelworks.example','Hyderabad','Design and animation studio.','pending');

INSERT INTO seeker_profiles (user_id,qualification,skills,experience) VALUES
(5,'B.E. Computer Science','HTML, CSS, JavaScript, PHP, MySQL','Fresher'),
(6,'B.Sc. Information Technology','Java, Servlets, MySQL','1 year'),
(7,'MBA Marketing','SEO, Excel, Communication','2 years');

INSERT INTO jobs (employer_id,title,description,location,salary,job_type,skills,posted_date,last_date,status) VALUES
(2,'PHP Developer','Build and maintain PHP and MySQL web applications with the product team.','Bengaluru','4 - 6 LPA','Full-time','PHP, MySQL, JavaScript',CURDATE(),DATE_ADD(CURDATE(),INTERVAL 30 DAY),'open'),
(2,'Frontend Intern','Work with senior developers on HTML, CSS and JavaScript interfaces.','Bengaluru','15,000 / month','Internship','HTML, CSS, JavaScript',CURDATE(),DATE_ADD(CURDATE(),INTERVAL 20 DAY),'open'),
(2,'Java Servlet Developer','Develop backend modules using Servlets, JSP and MySQL.','Remote','5 - 7 LPA','Full-time','Java, Servlets, JSP, MySQL',CURDATE(),DATE_ADD(CURDATE(),INTERVAL 25 DAY),'open'),
(2,'QA Tester','Write test cases and test web applications.','Pune','3 - 4 LPA','Full-time','Testing, SQL',CURDATE(),DATE_ADD(CURDATE(),INTERVAL 15 DAY),'open'),
(3,'Digital Marketing Executive','Plan campaigns, manage social media and track results.','Chennai','3 - 5 LPA','Full-time','SEO, Social media, Excel',CURDATE(),DATE_ADD(CURDATE(),INTERVAL 28 DAY),'open'),
(3,'Store Supervisor','Run daily operations at a retail outlet.','Chennai','2.5 - 3.5 LPA','Full-time','Communication, Inventory',CURDATE(),DATE_ADD(CURDATE(),INTERVAL 18 DAY),'open'),
(3,'Weekend Sales Associate','Help customers and handle billing on weekends.','Coimbatore','8,000 / month','Part-time','Communication, Billing',CURDATE(),DATE_ADD(CURDATE(),INTERVAL 12 DAY),'open'),
(3,'Content Writing Intern','Write blogs and product descriptions.','Remote','10,000 / month','Internship','Writing, Research',CURDATE(),DATE_ADD(CURDATE(),INTERVAL 22 DAY),'open');

INSERT INTO applications (job_id,seeker_id,cover_note,applied_date,status) VALUES
(1,5,'I built an event registration and a food delivery app in PHP and MySQL.',CURDATE(),'Applied'),
(2,5,'Keen to start my career in frontend development.',CURDATE(),'Shortlisted'),
(3,6,'I have built a leave management system using Servlets.',CURDATE(),'Applied'),
(5,7,'Two years of marketing experience.',CURDATE(),'Rejected');
