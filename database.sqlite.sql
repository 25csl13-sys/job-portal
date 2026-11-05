CREATE TABLE users (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name TEXT NOT NULL,
  email TEXT NOT NULL UNIQUE,
  password TEXT NOT NULL,
  phone TEXT,
  role TEXT NOT NULL CHECK(role IN ('seeker','employer','admin')),
  status TEXT NOT NULL DEFAULT 'active',
  created_at TEXT DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE employer_profiles (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER NOT NULL UNIQUE REFERENCES users(id) ON DELETE CASCADE,
  company_name TEXT NOT NULL,
  website TEXT,
  location TEXT,
  description TEXT,
  approval_status TEXT NOT NULL DEFAULT 'pending' CHECK(approval_status IN ('pending','approved','rejected'))
);

CREATE TABLE seeker_profiles (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER NOT NULL UNIQUE REFERENCES users(id) ON DELETE CASCADE,
  qualification TEXT,
  skills TEXT,
  experience TEXT
);

CREATE TABLE jobs (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  employer_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  title TEXT NOT NULL,
  description TEXT NOT NULL,
  location TEXT NOT NULL,
  salary TEXT,
  job_type TEXT NOT NULL CHECK(job_type IN ('Full-time','Part-time','Internship')),
  skills TEXT,
  posted_date TEXT NOT NULL,
  last_date TEXT NOT NULL,
  status TEXT NOT NULL DEFAULT 'open' CHECK(status IN ('open','closed'))
);

CREATE TABLE applications (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  job_id INTEGER NOT NULL REFERENCES jobs(id) ON DELETE CASCADE,
  seeker_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  cover_note TEXT,
  applied_date TEXT NOT NULL,
  status TEXT NOT NULL DEFAULT 'Applied' CHECK(status IN ('Applied','Shortlisted','Rejected')),
  UNIQUE(job_id,seeker_id)
);

INSERT INTO users (id,name,email,password,phone,role,status) VALUES
(1,'Site Admin','admin@jobportal.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','9000000001','admin','active'),
(2,'Priya Nair','hr@technova.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','9000000002','employer','active'),
(3,'Rahul Mehta','hr@greenleaf.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','9000000003','employer','active'),
(4,'Sana Khan','hr@pixelworks.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','9000000004','employer','active'),
(5,'Arjun Rao','arjun@mail.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','9000000005','seeker','active'),
(6,'Divya Iyer','divya@mail.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','9000000006','seeker','active'),
(7,'Karthik S','karthik@mail.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','9000000007','seeker','active');

INSERT INTO employer_profiles (user_id,company_name,website,location,description,approval_status) VALUES
(2,'TechNova Solutions','https://technova.example','Bengaluru','Software services company building web and mobile products.','approved'),
(3,'GreenLeaf Foods','https://greenleaf.example','Chennai','Organic food brand with its own retail outlets.','approved'),
(4,'PixelWorks Studio','https://pixelworks.example','Hyderabad','Design and animation studio.','pending');

INSERT INTO seeker_profiles (user_id,qualification,skills,experience) VALUES
(5,'B.E. Computer Science','HTML, CSS, JavaScript, PHP, MySQL','Fresher'),
(6,'B.Sc. Information Technology','Java, Servlets, MySQL','1 year'),
(7,'MBA Marketing','SEO, Excel, Communication','2 years');

INSERT INTO jobs (employer_id,title,description,location,salary,job_type,skills,posted_date,last_date,status) VALUES
(2,'PHP Developer','Build and maintain PHP and MySQL web applications.','Bengaluru','4 - 6 LPA','Full-time','PHP, MySQL, JavaScript',date('now'),date('now','+30 days'),'open'),
(2,'Frontend Intern','Work on HTML, CSS and JavaScript interfaces.','Bengaluru','15,000 / month','Internship','HTML, CSS, JavaScript',date('now'),date('now','+20 days'),'open'),
(2,'Java Servlet Developer','Develop backend modules using Servlets and JSP.','Remote','5 - 7 LPA','Full-time','Java, Servlets, JSP, MySQL',date('now'),date('now','+25 days'),'open'),
(3,'Digital Marketing Executive','Plan campaigns and manage social media.','Chennai','3 - 5 LPA','Full-time','SEO, Social media, Excel',date('now'),date('now','+28 days'),'open');
