# POS on XAMPP (Windows)

## 1. Install XAMPP
Download from https://www.apachefriends.org (pick the newest PHP 8.x installer; the Windows builds I could find
ship PHP 8.2 with MariaDB 10.4). Install to `C:\xampp` (not Program Files). Allow Apache and MySQL through the
firewall on **private** networks only.

## 2. Copy the app
Copy the `applause_infotech` folder into `C:\xampp\htdocs\` so you end up with:

    C:\xampp\htdocs\applause_infotech\index.html
    C:\xampp\htdocs\applause_infotech\phpfiles\config.php

## 3. Start services
XAMPP Control Panel -> **Start** Apache and MySQL.

## 4. Create the database and import your data
Open **Command Prompt** (not PowerShell, it does not support `<`):

    cd C:\xampp\mysql\bin
    mysql -u root -e "CREATE DATABASE busmas_database_iyonudrinks CHARACTER SET latin1 COLLATE latin1_swedish_ci"
    mysql -u root busmas_database_iyonudrinks < "C:\path\to\busmas_database_iyonudrinks.sql"

The import takes a few minutes (about 51 MB, ~130 tables). Use latin1 for the database so the monthly tables the
app creates later match your existing ones.

phpMyAdmin instead? Its upload limit is 40 MB and the dump is 51 MB. Raise `upload_max_filesize` and
`post_max_size` in `C:\xampp\php\php.ini`, restart Apache, then import.

## 5. Open it
`http://localhost/applause_infotech/`

Other PCs on the same network: `http://<this-PC's-IP>/applause_infotech/` (all addresses are relative now).

## 6. If something is off
* Database port or password differs (e.g. 3307/3308, or root has a password): edit `phpfiles/config.php`.
* Blank screen / errors: look at `phpfiles/php-error.log` (warnings are logged there, never shown in responses).
* Linux/macOS XAMPP: table names are case-sensitive there; add `lower_case_table_names=1` to MySQL's config
  before importing (not needed on Windows).

## Quick test after setup
Log in -> open Inventory -> make one small sale -> open the Sales report -> use Back - Up (downloads a .sql file).

## What was changed
* `connectdatabase.php` (included by every endpoint): reads `config.php`, sets timezone Africa/Lagos *before* any
  date() call, keeps old mysqli behaviour (PHP 8.1+ throws exceptions otherwise, which breaks reports for months
  that have no table), hides warnings from responses and logs them.
* All `mysqli_num_rows` / `mysqli_fetch_array` calls go through safe helpers (PHP 8 throws a TypeError if a query
  failed, PHP 7 only warned). `die(mysql_error())` -> `die(mysqli_error($conn))` (the old function no longer exists).
* A few string-in-arithmetic spots wrapped in `floatval()` (PHP 8 throws on `"" - 5`).
* `backup.php` rewritten: it pointed at a different database on port 3308, and did not escape quotes, so backups
  with an apostrophe in a name could not be restored. Output is still a downloadable .sql.
* `datasynch.php` (Update Server): hard-coded remote login removed; now **off** until configured in `config.php`.
* Frontend: the three different folder names and the `http://localhost` addresses baked into the build now all point
  to `/applause_infotech/phpfiles/`. The main script was renamed (`main.c8712686...`) so browsers do not reuse the old one.
* 8 unused/duplicate scripts moved to `phpfiles/_unused/` (blocked from the web).

## Spotted but NOT changed (your call)
1. `updatesupplyrecords.php`: the stock UPDATE is missing a comma (`UnitLeft = X  TrackingNo = Y`), so editing a
   supply record logs the change but never adjusts stock. Fix: add `,` before `TrackingNo`.
2. `getmystaffsalesrecords.php` line ~129: `$Credit += cond ? $Credit + x : 0` double-counts credit totals.
3. Passwords are reversibly "encrypted" with a key written in the code, and `getaccess.php` returns the decrypted
   password to the browser. Anyone who can reach the server can read any user's password.
4. Every query is built by string concatenation (SQL injection), and every endpoint sends
   `Access-Control-Allow-Origin: *`.
5. The old sync login (host `www.db4free.net`, user `applause`) sat in plain text in the old scripts. Change that
   password if it is still in use.
