# Altar Server Month 2026 — Feedback System

A working PHP + MySQL feedback system for the Altar Server Month 2026 event
at St. Peregrine Quasi Parish (August 29–30, 2026). Built with plain HTML,
CSS, vanilla JavaScript, PHP, and MySQL — no frameworks — so it runs on
XAMPP and any standard PHP/MySQL hosting.

This README has been verified against a real, running instance of the
system (PHP 8.3 + MariaDB) — every item in the testing checklist below was
actually executed, not just described.

---

## A. System Overview

Altar servers open a public link (`feedback.php`) on their phone or laptop
— no account, no login — fill out a 14-question form about their
experience, and submit. PHP validates the data on the server and saves it
into a MySQL database. An administrator logs into a separate, session-
protected area (`admin/`) to view statistics, search/filter responses,
read individual submissions, and delete test or inappropriate entries.

## B. Architecture

```
Participant
     ↓
Feedback Page (feedback.php + script.js)
     ↓  (fetch POST, validated client-side for UX)
PHP Backend (submit_feedback.php — validates again, server-side)
     ↓  (PDO prepared statement)
MySQL Database (altar_server_feedback.feedback)
     ↓
Admin Dashboard (admin/dashboard.php — reads via PDO, session-gated)
```

## C. Database Design

**Database:** `altar_server_feedback`

**Table `feedback`** — one row per submission. `name`, `next_event`, and
`message_to_organizers` are nullable (optional questions); everything else
is `NOT NULL` because the form requires it. `submitted_at` defaults to
the current timestamp automatically.

**Table `admin_users`** — holds the admin login. `password_hash` stores a
bcrypt hash from PHP's `password_hash()`, never a plain password.

Full column list is in [`database/altar_server_feedback.sql`](database/altar_server_feedback.sql).

## D. Complete File Structure

```
altar-server-feedback/
│
├── index.php                   Landing page → links to feedback.php
├── feedback.php                Public 14-question feedback form
├── submit_feedback.php         Validates + saves a submission (returns JSON)
├── success.php                 "Thank you" page after submitting
│
├── config/
│   └── database.php            Single source of DB connection settings
│
├── assets/
│   ├── css/style.css           All styling (public + admin)
│   └── js/script.js            Public form validation/submission logic
│
├── admin/
│   ├── login.php                Admin login form
│   ├── authenticate.php         Checks credentials, starts session
│   ├── dashboard.php             Stats, chart, search/filter, table
│   ├── view_feedback.php         Full detail view of one response
│   ├── delete_feedback.php       Deletes one response (POST + CSRF only)
│   ├── logout.php                Destroys the session
│   └── includes/
│       ├── auth_check.php        Session gate + CSRF helpers
│       └── admin_header.php      Shared admin nav bar
│
└── database/
    └── altar_server_feedback.sql Full DB schema + seeded admin account
```

## E. & F. Complete Code

Every file listed above contains complete, working code — no
`// add your code here` placeholders. Open any file directly to review it.

---

## G. Installation Instructions (XAMPP)

1. **Install/start XAMPP.** Download from apachefriends.org if you don't
   have it yet.
2. **Start Apache and MySQL** from the XAMPP Control Panel.
3. **Copy the project folder** so it sits at:
   `C:\xampp\htdocs\altar-server-feedback\`
4. **Import the database.** Open `http://localhost/phpmyadmin`, click
   **Import**, choose `database/altar_server_feedback.sql`, click **Go**.
   This creates the database, both tables, and one admin account.
5. **Open the site:** `http://localhost/altar-server-feedback/`
6. **Test the feedback form** by filling it out and submitting.
7. **Open the admin page:** `http://localhost/altar-server-feedback/admin/login.php`
   - Username: `admin`
   - Password: `AltarServer2026!`
   - **Change this password after your first login** (see the SQL file's
     comments for how to generate a new hash).

---

## H. Testing Checklist

Every item below was actually run against a live PHP + MariaDB instance
during development, using real HTTP requests — not just eyeballed in code.

- [x] **Feedback submission** — a fully-filled form saves correctly and returns `{"success":true}`
- [x] **Required fields** — submitting with fields missing returns a per-field error list and saves nothing
- [x] **Rating validation** — an out-of-range rating (e.g. `9`) is rejected server-side even though the UI only offers 1–5
- [x] **Database insertion** — submitted rows appear correctly in `feedback` with the right values
- [x] **Optional fields** — a submission with a blank name and blank optional questions still succeeds and stores `NULL`
- [x] **Duplicate-submission cooldown** — an instant repeat submission from the same session is blocked with a friendly message
- [x] **SQL injection attempt** — a payload like `Test'; DROP TABLE feedback; --` is stored as harmless literal text; the table is untouched
- [x] **XSS attempt** — a payload like `<script>alert(1)</script>` is stored, then rendered as escaped text (`&lt;script&gt;...`) in both the admin table and detail view, never as live HTML
- [x] **Admin login** — correct credentials log the admin in and redirect to the dashboard; wrong credentials redirect back with an error
- [x] **Session protection** — visiting `admin/dashboard.php` without logging in redirects to `login.php`; after `logout.php`, the same page redirects again
- [x] **Dashboard statistics** — total count, average rating, "would join again" count, and unique parish count were checked by hand against the seeded test data and matched exactly
- [x] **Search** — searching by a partial parish/vicariate name returns only the matching row(s)
- [x] **Rating filter** — filtering by "5 stars" returns only 5-star rows
- [x] **View individual feedback** — opens the correct row's full detail page
- [x] **Delete protection** — a `GET` request to `delete_feedback.php?id=…` does **not** delete anything; a `POST` without a CSRF token is rejected with `403`; a `POST` with a valid CSRF token deletes the correct row
- [x] **Logout** — destroys the session so the dashboard becomes inaccessible again
- [ ] **Mobile responsiveness** — the CSS includes mobile-first rules (stacked schedule cards, full-width tap targets, responsive table scroll), but actual on-device visual testing wasn't performed in this environment — please check on a real phone before the event.

---

## I. Online Hosting Instructions

When you're ready to move off XAMPP onto real hosting:

1. **Update `config/database.php` only** — this is the one file with
   credentials in it:
   ```php
   $DB_HOST = "your-host-provided-hostname";
   $DB_NAME = "your-hosting-db-name";
   $DB_USER = "your-hosting-db-username";
   $DB_PASS = "your-hosting-db-password";
   ```
2. **Set `APP_DEBUG` to `false`** in the same file, so raw PHP/MySQL errors
   are never shown to visitors.
3. **Import `database/altar_server_feedback.sql`** through your host's
   database tool (often also phpMyAdmin, or a web-based equivalent).
4. **Upload all project files** via FTP/SFTP or your host's file manager,
   keeping the same folder structure.
5. **Change the default admin password** before going live — see the
   comment block above the `INSERT INTO admin_users` line in the SQL file
   for how to generate a new bcrypt hash.
6. **Visit your live domain** and repeat the testing checklist above.

---

## Beginner-Friendly Explanations

**`config/database.php`** — Opens one shared connection to MySQL using
PDO. Every other PHP file that needs the database does
`require_once 'config/database.php';` and then uses the `$pdo` variable it
creates. Keeping credentials in exactly one file means moving to a new
host only requires editing this one place.

**`submit_feedback.php`** — Runs when the feedback form is submitted. It
reads each posted field with `$_POST['field_name']`, checks it against
rules (not empty, not too long, rating between 1–5, choice fields match an
allowed list), and if anything is wrong, sends back JSON describing which
fields failed. If everything passes, it uses a **prepared statement** —
`$pdo->prepare($sql)` then `$stmt->execute([...])` — to insert the row.
Prepared statements keep the submitted text completely separate from the
SQL command itself, so even a value like `'; DROP TABLE feedback; --`
is treated as plain data, not as a command.

**How PHP receives form data** — The JavaScript in `script.js` uses
`fetch()` to POST the form's data to `submit_feedback.php`. PHP
automatically fills the `$_POST` superglobal array with whatever was sent,
keyed by each input's `name` attribute (e.g. `name="parish_vicariate"`
becomes `$_POST['parish_vicariate']`).

**How PHP saves data into MySQL** — After validation passes,
`submit_feedback.php` builds an `INSERT` statement with named placeholders
like `:parish_vicariate`, then calls `$stmt->execute()` with an array
matching each placeholder to its real value. PDO handles safely inserting
that data into the database.

**How the admin dashboard retrieves feedback** — `dashboard.php` runs
`SELECT` queries against the `feedback` table (again using PDO, with
placeholders for anything coming from the search box or filter dropdown),
then loops over the results with a PHP `foreach` to build the HTML table
row by row.

**How sessions protect the admin page** — When the admin logs in
successfully, `authenticate.php` stores their ID in `$_SESSION['admin_id']`.
Every protected admin page starts by including `includes/auth_check.php`,
which checks: "is `$_SESSION['admin_id']` set?" If not, it immediately
redirects to `login.php` and stops the rest of the page from running —
so there's no way to see feedback data without being logged in.

**How the public feedback page works** — `feedback.php` is just HTML and
CSS with a `<form>`; it doesn't touch the database directly. When
submitted, `script.js` intercepts the form, validates it in the browser
for a nicer experience, then sends the data to `submit_feedback.php` via
`fetch()`. The *real* validation and saving happens there, on the server,
which is the only place that can be trusted.
