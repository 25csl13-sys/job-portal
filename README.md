# Online Job Portal System

HTML/CSS/JavaScript front end, PHP back end, MySQL database.

## Run it (XAMPP / WAMP)
1. Copy the `job-portal` folder into `htdocs` (XAMPP) or `www` (WAMP).
2. Start Apache and MySQL.
3. Open phpMyAdmin, go to Import, choose `database.sql`, click Go.
4. If your MySQL has a password, or the folder name differs, edit `config.php`.
5. Open http://localhost/job-portal/

## Demo logins (password for all: `password`)
| Role | Email |
|---|---|
| Admin | admin@jobportal.com |
| Employer (approved) | hr@technova.com, hr@greenleaf.com |
| Employer (pending approval) | hr@pixelworks.com |
| Job seeker | arjun@mail.com, divya@mail.com, karthik@mail.com |

If a login fails, open `reset_demo_passwords.php` once in the browser, then delete it.

## Modules
- **Job seeker:** register, log in, profile, search (keyword, location, type), apply, track applications
- **Employer:** post jobs, close/reopen/delete, view applicants, shortlist or reject
- **Admin:** approve or reject employers, block/unblock/delete users, delete listings

## Security used
Hashed passwords, prepared statements, output escaping, session role checks on every protected page, unique key blocking duplicate applications.

## Files
config.php, database.sql, index.php, register.php, login.php, logout.php, job.php, my_applications.php, profile.php,
employer/ (dashboard, post_job, applicants), admin/dashboard.php, includes/ (header, footer), css/style.css
