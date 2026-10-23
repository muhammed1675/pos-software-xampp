# Retail POS - install (Windows / XAMPP)

## 1. Install XAMPP
Install XAMPP (PHP 8.x) to `C:\xampp`. In the XAMPP Control Panel, **Start** Apache and MySQL.

## 2. Copy the app
Copy the `pos` folder to `C:\xampp\htdocs\` so you have `C:\xampp\htdocs\pos\index.php`.

## 3. Create the database
Open **Command Prompt** (not PowerShell):

    cd C:\xampp\mysql\bin
    mysql -u root < "C:\path\to\database\schema.sql"

(phpMyAdmin works too: Import -> choose `schema.sql`.) This creates the database `retail_pos`
with an empty shop and one login.

## 4. Set up this shop  (edit `C:\xampp\htdocs\pos\phpfiles\config.php`)
* `shop_name`, `shop_address`, `shop_phones`, `shop_about` - shown on the login page and printed receipts.
* `dev_name`, `dev_phone`, `dev_email`, `dev_credit` - your own "software by" lines.
* `secret_key` - give every customer their own. Then re-seal the admin password:

      C:\xampp\php\php.exe C:\xampp\htdocs\pos\phpfiles\reset-admin.php YourNewPassword

  Change the key BEFORE adding staff - changing it later makes existing passwords unreadable.
* Different database port/password? Edit `db_port` / `db_pass` in the same file.

## 5. Open it
`http://localhost/pos/`  - first login `ADMIN` / `ChangeMe123` (or the password you set in step 4).
Other PCs on the network: `http://<this-PC's-IP>/pos/`.

## Quick test
Add stock (Supply-In) -> make one sale -> open the Sales report -> Back - Up (downloads a .sql file).

## Notes
* Blank screen / errors: see `phpfiles\php-error.log`.
* New staff start with: make sales, own report, change own password. The admin grants the rest.
* Works on Linux hosting too (table names are lower-case now).
* "Update Server" (online sync) is off until configured in `config.php`.

## Known limitations (fix before selling widely)
1. Passwords are only scrambled with a key, and `getaccess.php` sends the decrypted password to the browser.
2. All queries are built by string concatenation (SQL injection) and every endpoint allows any origin.
3. `updatesupplyrecords.php`: stock UPDATE is missing a comma before `TrackingNo`, so editing a supply record never adjusts stock.
4. `getmystaffsalesrecords.php` (~line 129): credit totals are double-counted.
5. The front end is a compiled build with no source, so UI changes beyond the config text need the original source.
