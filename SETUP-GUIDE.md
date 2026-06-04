# Smart Study Planner — Complete Setup Guide
### Step-by-Step Installation from Zero to Running

---

## REQUIREMENTS
- Windows 10 or 11
- Internet connection (for XAMPP download)
- Google Chrome or any browser
- VS Code (optional, for viewing/editing code)

---

---

# PART 1 — INSTALL XAMPP

### Step 1 — Download XAMPP
1. Open your browser (Chrome, Edge, etc.)
2. Go to: **https://www.apachefriends.org**
3. Click the big **"Download"** button for Windows
4. Wait for the file to download (about 160MB)
   - File name will be something like: `xampp-windows-x64-8.2.x-installer.exe`

### Step 2 — Run the Installer
1. Go to your Downloads folder
2. Double-click the downloaded installer file
3. If Windows asks **"Do you want to allow this app to make changes?"** → click **Yes**
4. If a warning appears about antivirus → click **OK**
5. Click **Next** on the first screen
6. On the components screen → leave everything as default → click **Next**
7. Installation folder should be **C:\xampp** → DO NOT change this → click **Next**
8. Language: English → click **Next**
9. Uncheck "Learn more about Bitnami" → click **Next**
10. Click **Next** to begin installation
11. Wait for installation to finish (2-5 minutes)
12. When done, make sure **"Do you want to start the Control Panel now?"** is ticked
13. Click **Finish**

---

# PART 2 — START APACHE AND MYSQL

### Step 3 — Open XAMPP Control Panel
1. XAMPP Control Panel will open automatically after install
2. If it doesn't open, go to **C:\xampp** and double-click **xampp-control.exe**
3. If Windows asks to run as administrator → click **Yes**

### Step 4 — Start the Servers
1. Find the row that says **Apache** → click the **Start** button next to it
   - Wait 3-5 seconds
   - Apache row turns **green** and shows port **80, 443**
2. Find the row that says **MySQL** → click the **Start** button next to it
   - Wait 3-5 seconds
   - MySQL row turns **green** and shows port **3306**

> ✅ Both Apache and MySQL must be GREEN before continuing

### Step 5 — Verify XAMPP is Working
1. Open your browser
2. Go to: **http://localhost**
3. You should see the XAMPP welcome page
4. If you see it → XAMPP is working correctly ✅

---

# PART 3 — SET UP THE DATABASE

### Step 6 — Open phpMyAdmin
1. Open your browser
2. Go to: **http://localhost/phpmyadmin**
3. phpMyAdmin will open (it's the database manager)

### Step 7 — Import the Database
1. In phpMyAdmin, click **Import** in the top menu bar
2. Under **"File to import"**, click the **Choose File** button
3. A file picker window opens
4. In the address bar of the file picker, type:
   ```
   C:\Users\PC\smart-study-planner\database
   ```
   Then press **Enter**
5. Select the file **schema.sql** → click **Open**
6. You will see `schema.sql` shown next to the Choose File button
7. Scroll all the way down to the bottom of the page
8. Click the **Import** button (grey button at bottom left)
9. Wait a few seconds

### Step 8 — Verify the Database was Created
1. Look at the left sidebar in phpMyAdmin
2. You should now see **smart_study_planner** in the list
3. Click on it to expand it
4. You should see 4 tables inside:
   - users
   - subjects
   - tasks
   - schedule
5. If all 4 tables are there → database is ready ✅

---

# PART 4 — COPY PROJECT FILES

### Step 9 — Copy Files to XAMPP
1. Open **File Explorer** (press Windows key + E)
2. In the address bar, type:
   ```
   C:\xampp\htdocs
   ```
   Press **Enter**
3. Inside the htdocs folder, **right-click** → **New** → **Folder**
4. Name the new folder: `smart-study-planner`
5. Press Enter

6. Open a **second File Explorer window**
7. In the address bar, type:
   ```
   C:\Users\PC\smart-study-planner
   ```
   Press **Enter**
8. Press **Ctrl + A** to select all files and folders
9. Press **Ctrl + C** to copy

10. Go back to the first File Explorer window
11. Double-click the `smart-study-planner` folder inside htdocs to open it
12. Press **Ctrl + V** to paste all files

13. Wait for all files to copy (it's fast, only a few seconds)

### Step 10 — Verify the Files are in the Right Place
After copying, the folder should look like this:
```
C:\xampp\htdocs\smart-study-planner\
    ├── index.html
    ├── login.html
    ├── signup.html
    ├── dashboard.php
    ├── addsubject.php
    ├── addtask.php
    ├── schedule.php
    ├── profile.php
    ├── api\
    │     ├── login.php
    │     ├── register.php
    │     ├── logout.php
    │     ├── subjects.php
    │     ├── tasks.php
    │     ├── schedule.php
    │     └── profile.php
    ├── includes\
    │     ├── db.php
    │     ├── auth.php
    │     ├── functions.php
    │     ├── header.php
    │     └── footer.php
    ├── assets\
    │     ├── css\style.css
    │     └── js\main.js
    └── database\
          └── schema.sql
```

---

# PART 5 — OPEN AND USE THE PROJECT

### Step 11 — Open the Project in Browser
1. Open your browser (Chrome recommended)
2. Go to:
   ```
   http://localhost/smart-study-planner/index.html
   ```
3. You should see the **Smart Study Planner landing page** ✅

### Step 12 — Create Your Account
1. Click **"Sign Up Free"** or go to:
   ```
   http://localhost/smart-study-planner/signup.html
   ```
2. Enter your:
   - Full Name
   - Email Address
   - Password (minimum 6 characters)
   - Confirm Password
3. Click **"Create Account"**
4. You will see a success message

### Step 13 — Log In
1. You will be redirected to the login page automatically
   Or go to: `http://localhost/smart-study-planner/login.html`
2. Enter your email and password
3. Click **"Log In"**
4. You will be taken to the **Dashboard** ✅

---

# PART 6 — USING THE SYSTEM

### Dashboard — `http://localhost/smart-study-planner/dashboard.php`
- See total subjects, tasks, completed tasks, study sessions
- View your overall task progress bar
- See recent tasks
- See today's study schedule

### Subjects — `http://localhost/smart-study-planner/addsubject.php`
- Add new subjects with a name, description, and color
- View all your subjects
- Delete subjects

### Tasks — `http://localhost/smart-study-planner/addtask.php`
- Add new tasks with title, description, subject, due date, and priority
- Filter tasks by: All / Pending / In Progress / Completed
- Change task status using the dropdown
- Delete tasks

### Schedule — `http://localhost/smart-study-planner/schedule.php`
- Add weekly study sessions with a title, subject, day, start and end time
- View your full weekly timetable grouped by day
- Delete schedule entries

### Profile — `http://localhost/smart-study-planner/profile.php`
- View your stats (subjects, tasks, completed, sessions)
- Edit your name
- Change your password

---

# PART 7 — OPENING IN VS CODE

### Step 14 — Open Project in VS Code
1. Open **VS Code**
2. Click **File** → **Open Folder**
3. Navigate to `C:\xampp\htdocs\smart-study-planner`
4. Click **Select Folder**
5. All project files appear in the left panel
6. You can now view and edit any file

---

# TROUBLESHOOTING

### Problem: Apache won't start (Port 80 busy)
**Solution:**
1. In XAMPP Control Panel, click **Config** next to Apache
2. Click **httpd.conf**
3. Find the line: `Listen 80`
4. Change it to: `Listen 8080`
5. Save the file
6. Now start Apache
7. Access your project at: `http://localhost:8080/smart-study-planner/index.html`

### Problem: MySQL won't start (Port 3306 busy)
**Solution:**
1. Open Task Manager (Ctrl + Shift + Esc)
2. Look for **mysqld.exe** in processes
3. Right-click it → End Task
4. Try starting MySQL in XAMPP again

### Problem: Page shows "Database connection failed"
**Solution:**
1. Make sure MySQL is running (green in XAMPP)
2. Open `C:\xampp\htdocs\smart-study-planner\includes\db.php`
3. Check these values:
   ```
   DB_HOST = localhost
   DB_USER = root
   DB_PASS = (leave empty, no password)
   DB_NAME = smart_study_planner
   ```

### Problem: "Page not found" or 404 error
**Solution:**
1. Make sure Apache is running (green in XAMPP)
2. Make sure files are in `C:\xampp\htdocs\smart-study-planner\`
3. Check the URL is exactly: `http://localhost/smart-study-planner/`

### Problem: Login redirects back to login page
**Solution:**
1. The database might not be imported
2. Go to phpMyAdmin → check if `smart_study_planner` database exists
3. If not, repeat Step 7 (Import the Database)

---

# EVERY TIME YOU WANT TO USE THE PROJECT

You must do these steps every time you restart your computer:

1. Open **XAMPP Control Panel** (from desktop shortcut or `C:\xampp\xampp-control.exe`)
2. Click **Start** next to **Apache** → wait for green
3. Click **Start** next to **MySQL** → wait for green
4. Open browser → go to `http://localhost/smart-study-planner/index.html`

---

# ALL PAGE URLS

| Page         | URL                                                      |
|--------------|----------------------------------------------------------|
| Landing Page | http://localhost/smart-study-planner/index.html          |
| Sign Up      | http://localhost/smart-study-planner/signup.html         |
| Login        | http://localhost/smart-study-planner/login.html          |
| Dashboard    | http://localhost/smart-study-planner/dashboard.php       |
| Subjects     | http://localhost/smart-study-planner/addsubject.php      |
| Tasks        | http://localhost/smart-study-planner/addtask.php         |
| Schedule     | http://localhost/smart-study-planner/schedule.php        |
| Profile      | http://localhost/smart-study-planner/profile.php         |
| phpMyAdmin   | http://localhost/phpmyadmin                              |

---

*Smart Study Planner — Built with PHP 8, MySQL, Bootstrap 5*
