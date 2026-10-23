-- ===========================================================================
-- Retail POS - database schema (fresh install)
-- Run once on a new server:   mysql -u root < schema.sql
-- (or phpMyAdmin -> Import). It creates the database "retail_pos".
--
-- Month-by-month tables (e.g. Oct_2026_salesrecords, Oct_2026_receiptdetails,
-- Oct_2026_allgoodssupplied, Oct_2026_price_change_track, Oct_2026_expenditures,
-- Oct_2026_invoice_picture) are created automatically by the app the first time
-- they are needed, so they are not listed here.
--
-- First login:  username ADMIN   password ChangeMe123   (change it straight away)
-- ===========================================================================

CREATE DATABASE IF NOT EXISTS retail_pos CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE retail_pos;

-- Items in stock -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS allgoodsproperties (
  SN INT NOT NULL AUTO_INCREMENT,
  GoodID VARCHAR(100) NOT NULL,
  Goodname VARCHAR(150) NOT NULL,
  CurrentCostPrice VARCHAR(100) DEFAULT '0',
  Soldin VARCHAR(100) DEFAULT NULL,
  UnitLeft DOUBLE NOT NULL DEFAULT 0,
  ConverterRate VARCHAR(100) DEFAULT '1',
  UnitSellingPrice1 VARCHAR(100) DEFAULT '0',
  UnitSellingPrice2 VARCHAR(100) DEFAULT '0',
  UnitSellingPrice3 VARCHAR(100) DEFAULT '0',
  TrackingNo INT NOT NULL DEFAULT 1,
  minimumexpirydays VARCHAR(50) DEFAULT '0',
  minimumquantity VARCHAR(50) DEFAULT '0',
  PRIMARY KEY (SN),
  UNIQUE KEY uq_goodid (GoodID),
  UNIQUE KEY uq_goodname (Goodname)
) ENGINE=InnoDB;

-- Staff logins ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS staffrecord (
  SN INT NOT NULL AUTO_INCREMENT,
  Username VARCHAR(100) NOT NULL,
  Surname VARCHAR(100) DEFAULT NULL,
  Firstname VARCHAR(100) DEFAULT NULL,
  Middlename VARCHAR(100) DEFAULT NULL,
  Gateway VARCHAR(255) DEFAULT NULL,
  Address VARCHAR(500) DEFAULT NULL,
  PhoneNumber VARCHAR(100) DEFAULT NULL,
  Email VARCHAR(200) DEFAULT NULL,
  Category VARCHAR(50) DEFAULT 'Regular',
  DateRegistered VARCHAR(50) DEFAULT NULL,
  LastLogin VARCHAR(50) DEFAULT NULL,
  PRIMARY KEY (SN),
  UNIQUE KEY uq_username (Username)
) ENGINE=InnoDB;

-- What each staff member may open (1 = allowed, 0 = not) -----------------------
-- NS make sales, MR own report, SI supply in, CP change price, ASt add staff,
-- VS view staff, CPw change own password, SP supply report, SR sales report,
-- GS goods summary, IV inventory, CR customers, VD debts, CF cash flow,
-- GH item history, US update server
CREATE TABLE IF NOT EXISTS permission (
  SN INT NOT NULL AUTO_INCREMENT,
  ID VARCHAR(100) NOT NULL,
  NS VARCHAR(5) NOT NULL DEFAULT '1',
  MR VARCHAR(5) NOT NULL DEFAULT '1',
  SI VARCHAR(5) NOT NULL DEFAULT '0',
  CP VARCHAR(5) NOT NULL DEFAULT '0',
  ASt VARCHAR(5) NOT NULL DEFAULT '0',
  VS VARCHAR(5) NOT NULL DEFAULT '0',
  CPw VARCHAR(5) NOT NULL DEFAULT '1',
  SP VARCHAR(5) NOT NULL DEFAULT '0',
  SR VARCHAR(5) NOT NULL DEFAULT '0',
  GS VARCHAR(5) NOT NULL DEFAULT '0',
  IV VARCHAR(5) NOT NULL DEFAULT '0',
  CR VARCHAR(5) NOT NULL DEFAULT '0',
  VD VARCHAR(5) NOT NULL DEFAULT '0',
  CF VARCHAR(5) NOT NULL DEFAULT '0',
  GH VARCHAR(5) NOT NULL DEFAULT '0',
  US VARCHAR(5) NOT NULL DEFAULT '0',
  PRIMARY KEY (SN),
  UNIQUE KEY uq_perm_id (ID)
) ENGINE=InnoDB;

-- Registered customers -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS customerrecords (
  SN INT NOT NULL AUTO_INCREMENT,
  CustomerID VARCHAR(100) NOT NULL,
  BusinessName VARCHAR(200) DEFAULT NULL,
  Address VARCHAR(500) DEFAULT NULL,
  PhoneNumber VARCHAR(100) DEFAULT NULL,
  DateRegistered VARCHAR(50) DEFAULT NULL,
  PRIMARY KEY (SN),
  UNIQUE KEY uq_customerid (CustomerID)
) ENGINE=InnoDB;

-- Sales on credit and their repayments ---------------------------------------------
CREATE TABLE IF NOT EXISTS debtreceiptdetails (
  SN INT NOT NULL AUTO_INCREMENT,
  ReceiptNumber VARCHAR(100) NOT NULL,
  Payable VARCHAR(100) DEFAULT '0',
  Discount VARCHAR(100) DEFAULT '0',
  Paid VARCHAR(1000) DEFAULT '0',
  PaymentMode VARCHAR(100) DEFAULT '0',
  DatePaid VARCHAR(100) DEFAULT '0',
  Customer VARCHAR(100) DEFAULT NULL,
  Phone VARCHAR(100) DEFAULT NULL,
  Staff VARCHAR(100) DEFAULT NULL,
  Profit VARCHAR(100) DEFAULT '0',
  LastPaymentDate VARCHAR(100) DEFAULT NULL,
  PRIMARY KEY (SN),
  KEY idx_debt_receipt (ReceiptNumber)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS debtpaymentrecords (
  SN INT NOT NULL AUTO_INCREMENT,
  PaymentID VARCHAR(100) NOT NULL,
  ReceiptNumber VARCHAR(100) NOT NULL,
  AmountPaid VARCHAR(100) DEFAULT '0',
  PaymentMode VARCHAR(100) DEFAULT NULL,
  StaffPaid VARCHAR(100) DEFAULT NULL,
  DatePaid VARCHAR(100) DEFAULT NULL,
  PRIMARY KEY (SN),
  KEY idx_pay_receipt (ReceiptNumber),
  KEY idx_pay_id (PaymentID)
) ENGINE=InnoDB;

-- Expiry dates of supplied goods ------------------------------------------------------
CREATE TABLE IF NOT EXISTS expirydates (
  SN INT NOT NULL AUTO_INCREMENT,
  GoodsID VARCHAR(100) NOT NULL,
  Supplieddate VARCHAR(100) DEFAULT NULL,
  Expirydate VARCHAR(100) DEFAULT NULL,
  ExpiryStatus VARCHAR(20) NOT NULL DEFAULT 'Active',
  dateignored VARCHAR(100) DEFAULT NULL,
  staffwhoignored VARCHAR(100) DEFAULT NULL,
  PRIMARY KEY (SN),
  KEY idx_exp_goods (GoodsID)
) ENGINE=InnoDB;

-- Activity log ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS logrecords (
  SN INT NOT NULL AUTO_INCREMENT,
  Date VARCHAR(100) DEFAULT NULL,
  EventID VARCHAR(100) DEFAULT NULL,
  Event TEXT,
  PRIMARY KEY (SN)
) ENGINE=InnoDB;

-- Last "Update Server" run per report (only used when online sync is switched on) -----------
CREATE TABLE IF NOT EXISTS updatetrack (
  SN INT NOT NULL AUTO_INCREMENT,
  reportname VARCHAR(100) NOT NULL,
  lastupdate VARCHAR(100) DEFAULT NULL,
  Staff VARCHAR(100) DEFAULT NULL,
  PRIMARY KEY (SN),
  UNIQUE KEY uq_report (reportname)
) ENGINE=InnoDB;
INSERT IGNORE INTO updatetrack (reportname) VALUES ('Sales Report'), ('Supply Report'), ('Inventory'), ('Debtors Report');

-- First administrator -------------------------------------------------------------------------
INSERT IGNORE INTO staffrecord (Username, Surname, Firstname, Middlename, Gateway, Address, PhoneNumber, Email, Category, DateRegistered, LastLogin)
VALUES ('ADMIN', 'Administrator', 'System', '', 'ttjMza60mL-UhWk=', '', '', '', 'Admin', 'Jan 01, 2026 12:00 am', 'Jan 01, 2026 12:00 am');
INSERT IGNORE INTO permission (ID, NS, MR, SI, CP, ASt, VS, CPw, SP, SR, GS, IV, CR, VD, CF, GH, US)
VALUES ('ADMIN','1','1','1','1','1','1','1','1','1','1','1','1','1','1','1','1');
