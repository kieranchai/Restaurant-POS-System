-- ============================================================================
--  Ember - Flame-Grilled Kitchen  |  Restaurant POS / Self-Ordering System
--  SQLite schema + seed data (auto-imported on first run).
--
--  This is the SQLite port of the original MySQL schema. SQLite needs no
--  server, so the whole app runs from a single file with zero configuration.
-- ============================================================================

PRAGMA foreign_keys = OFF;

DROP TABLE IF EXISTS "Payment";
DROP TABLE IF EXISTS "Bill";
DROP TABLE IF EXISTS "Cart";
DROP TABLE IF EXISTS "itemTypeAddOns";
DROP TABLE IF EXISTS "MenuItem";
DROP TABLE IF EXISTS "MenuCategory";
DROP TABLE IF EXISTS "Menu";
DROP TABLE IF EXISTS "Table";
DROP TABLE IF EXISTS "PriceConstants";
DROP TABLE IF EXISTS "Promotions";
DROP TABLE IF EXISTS "member";
DROP TABLE IF EXISTS "User";
DROP TABLE IF EXISTS "Branch";

-- ----------------------------------------------------------------------------
--  Core reference tables
-- ----------------------------------------------------------------------------
CREATE TABLE "Branch" (
  "branchId"        INTEGER PRIMARY KEY,
  "branchName"      TEXT    NOT NULL,
  "branchAddress"   TEXT    NOT NULL,
  "numberOfTables"  INTEGER NOT NULL DEFAULT 0,
  "branchImage"     TEXT    NOT NULL
);

CREATE TABLE "User" (
  "userId"        INTEGER PRIMARY KEY AUTOINCREMENT,
  "userName"      TEXT NOT NULL,
  "userPassword"  TEXT NOT NULL,   -- md5 hash (kept from original logic)
  "userRole"      TEXT NOT NULL    -- 'admin' | 'staff'
);

CREATE TABLE "member" (
  "memberId"         INTEGER PRIMARY KEY AUTOINCREMENT,
  "memberFirstName"  TEXT    NOT NULL,
  "memberLastName"   TEXT    NOT NULL,
  "memberNumber"     TEXT    NOT NULL,   -- phone number used as loyalty id
  "totalPoints"      INTEGER NOT NULL DEFAULT 0
);

CREATE TABLE "Promotions" (
  "promotionId"     INTEGER PRIMARY KEY AUTOINCREMENT,
  "promotionName"   TEXT    NOT NULL,
  "promotionCode"   TEXT    NOT NULL,
  "promotionValue"  NUMERIC NOT NULL     -- negative = discount off the total
);

CREATE TABLE "PriceConstants" (
  "priceConstantsId" INTEGER PRIMARY KEY,   -- 1 = GST rate, 2 = takeaway fee
  "priceModifier"    NUMERIC NOT NULL,
  "description"      TEXT    NOT NULL
);

CREATE TABLE "Table" (
  "tableId"     INTEGER PRIMARY KEY AUTOINCREMENT,
  "tableNo"     INTEGER NOT NULL,
  "isReserved"  INTEGER NOT NULL DEFAULT 0,
  "branchId"    INTEGER NOT NULL
);

-- ----------------------------------------------------------------------------
--  Menu structure
-- ----------------------------------------------------------------------------
CREATE TABLE "Menu" (
  "menuId"    INTEGER PRIMARY KEY,
  "branchId"  INTEGER NOT NULL
);

CREATE TABLE "MenuCategory" (
  "menuCategoryId"    INTEGER PRIMARY KEY,
  "menuId"            INTEGER NOT NULL,
  "menuCategoryName"  TEXT    NOT NULL
);

CREATE TABLE "MenuItem" (
  "menuItemId"           INTEGER PRIMARY KEY AUTOINCREMENT,
  "menuCategoryId"       INTEGER NOT NULL,
  "menuItemName"         TEXT    NOT NULL,
  "price"                NUMERIC NOT NULL,
  "menuItemDescription"  TEXT    NOT NULL,
  "itemTypeId"           INTEGER          -- NULL = no add-ons, 1 = multi, 2 = single
);

CREATE TABLE "itemTypeAddOns" (
  "itemTypeAddOnsId"            INTEGER PRIMARY KEY AUTOINCREMENT,
  "itemTypeId"                  INTEGER NOT NULL,  -- 1 = add-ons (checkbox), 2 = size (radio)
  "itemTypeAddOnsName"          TEXT    NOT NULL,
  "itemTypeAddOnsPriceModifier" NUMERIC NOT NULL
);

-- ----------------------------------------------------------------------------
--  Transactional tables (populated at runtime by the app)
-- ----------------------------------------------------------------------------
CREATE TABLE "Cart" (
  "cartId"      INTEGER PRIMARY KEY AUTOINCREMENT,
  "menuItemId"  INTEGER NOT NULL,
  "tableId"     INTEGER,
  "itemAddOns"  TEXT
);

CREATE TABLE "Bill" (
  "billId"       INTEGER PRIMARY KEY AUTOINCREMENT,
  "menuIds"      TEXT    NOT NULL,
  "totalAmount"  NUMERIC NOT NULL,
  "tableId"      INTEGER,
  "branchId"     INTEGER NOT NULL,
  "customerId"   INTEGER NOT NULL
);

CREATE TABLE "Payment" (
  "paymentId"        INTEGER PRIMARY KEY AUTOINCREMENT,
  "billId"           INTEGER NOT NULL,
  "totalAmount"      NUMERIC NOT NULL,
  "paymentMethod"    TEXT    NOT NULL,
  "paymentDateTime"  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================================
--  SEED DATA
-- ============================================================================

-- Staff accounts (md5 of 'admin1' and 'johnlogin' respectively)
INSERT INTO "User" ("userName", "userPassword", "userRole") VALUES
  ('admin', 'e00cf25ad42683b3df678c61f42c6bda', 'admin'),
  ('staff', 'fe9591841ff157ff6948e4f02422dbd7', 'staff');

-- Pricing constants
INSERT INTO "PriceConstants" ("priceConstantsId", "priceModifier", "description") VALUES
  (1, 0.09, 'GST rate (9%)'),
  (2, 0.30, 'Takeaway packaging fee');

-- Loyalty members
INSERT INTO "member" ("memberFirstName", "memberLastName", "memberNumber", "totalPoints") VALUES
  ('Alex',   'Tan',   '91234567', 25),
  ('Priya',  'Kumar', '98765432', 8),
  ('Marcus', 'Lee',   '90011234', 40);

-- Promotion codes (value is negative so it subtracts from the total)
INSERT INTO "Promotions" ("promotionName", "promotionCode", "promotionValue") VALUES
  ('Welcome Treat',  'WELCOME', -3.00),
  ('Weekend Saver',  'WEEKEND', -5.00),
  ('Student Deal',   'STUDENT', -2.50);

-- Add-on groups (1 = multi-select extras, 2 = single-select size)
INSERT INTO "itemTypeAddOns" ("itemTypeId", "itemTypeAddOnsName", "itemTypeAddOnsPriceModifier") VALUES
  (1, 'Extra Cheese', 1.00),
  (1, 'Smoky Bacon',  1.50),
  (1, 'Jalapenos',    0.80),
  (1, 'Extra Sauce',  0.50),
  (2, 'Regular',      0.00),
  (2, 'Large',        1.50),
  (2, 'Sharing',      3.00);

-- Branches
INSERT INTO "Branch" ("branchId", "branchName", "branchAddress", "numberOfTables", "branchImage") VALUES
  (1, 'Ember Orchard Central', '181 Orchard Road, #01-12',           6, 'orchard.jpg'),
  (2, 'Ember Northpoint',      '930 Yishun Ave 2, #02-20',           6, 'northpoint.jpg'),
  (3, 'Ember Jurong',          '2 Jurong East Central 1, #01-05',    6, 'jcube.jpg'),
  (4, 'Ember Dover',           '12 Dover Crescent, #01-03',          6, 'dover.jpg'),
  (5, 'Ember West Mall',       '1 Bukit Batok Central Link, #03-08', 6, 'westmall.jpg');

-- One menu per branch (menuId = branchId)
INSERT INTO "Menu" ("menuId", "branchId") VALUES
  (1, 1), (2, 2), (3, 3), (4, 4), (5, 5);

-- Menu categories per branch (menuCategoryId = menuId*10 + n)
INSERT INTO "MenuCategory" ("menuCategoryId", "menuId", "menuCategoryName") VALUES
  (11, 1, 'Signature Chicken'), (12, 1, 'Burgers'), (13, 1, 'Sides'), (14, 1, 'Drinks'),
  (21, 2, 'Signature Chicken'), (22, 2, 'Burgers'), (23, 2, 'Sides'), (24, 2, 'Drinks'),
  (31, 3, 'Signature Chicken'), (32, 3, 'Burgers'), (33, 3, 'Sides'), (34, 3, 'Drinks'),
  (41, 4, 'Signature Chicken'), (42, 4, 'Burgers'), (43, 4, 'Sides'), (44, 4, 'Drinks'),
  (51, 5, 'Signature Chicken'), (52, 5, 'Burgers'), (53, 5, 'Sides'), (54, 5, 'Drinks');

-- Menu items for every branch. itemTypeId: NULL = none, 1 = extras, 2 = size.
INSERT INTO "MenuItem" ("menuCategoryId", "menuItemName", "price", "menuItemDescription", "itemTypeId") VALUES
  (11, '3pc Flame-Grilled Chicken', 8.90,  'Three pieces of our signature flame-grilled chicken.', NULL),
  (11, '6pc Sharing Bucket',        15.90, 'Six pieces, perfect for two to share.',                NULL),
  (11, '9pc Family Bucket',         22.90, 'Nine pieces for the whole table.',                     NULL),
  (12, 'Ember Classic Burger',      7.50,  'Grilled chicken fillet, lettuce and house sauce.',     1),
  (12, 'Spicy Zinger Burger',       8.50,  'Crispy spiced fillet with chilli mayo.',               1),
  (12, 'Double Smash Burger',       10.90, 'Two seared patties with melted cheese.',               1),
  (13, 'Seasoned Fries',            3.50,  'Golden fries with our signature seasoning.',           NULL),
  (13, 'Creamy Mash',               3.00,  'Buttery mashed potato with gravy.',                    NULL),
  (13, 'Garden Coleslaw',           2.80,  'Fresh, crunchy and lightly dressed.',                  NULL),
  (14, 'Soft Drink',                2.50,  'Your choice of chilled soft drink.',                   2),
  (14, 'Iced Lemon Tea',            2.80,  'Refreshing house-brewed lemon tea.',                   2),
  (14, 'Fresh Orange Juice',        3.80,  'Cold-pressed, no added sugar.',                        2),
  (21, '3pc Flame-Grilled Chicken', 8.90,  'Three pieces of our signature flame-grilled chicken.', NULL),
  (21, '6pc Sharing Bucket',        15.90, 'Six pieces, perfect for two to share.',                NULL),
  (21, '9pc Family Bucket',         22.90, 'Nine pieces for the whole table.',                     NULL),
  (22, 'Ember Classic Burger',      7.50,  'Grilled chicken fillet, lettuce and house sauce.',     1),
  (22, 'Spicy Zinger Burger',       8.50,  'Crispy spiced fillet with chilli mayo.',               1),
  (22, 'Double Smash Burger',       10.90, 'Two seared patties with melted cheese.',               1),
  (23, 'Seasoned Fries',            3.50,  'Golden fries with our signature seasoning.',           NULL),
  (23, 'Creamy Mash',               3.00,  'Buttery mashed potato with gravy.',                    NULL),
  (23, 'Garden Coleslaw',           2.80,  'Fresh, crunchy and lightly dressed.',                  NULL),
  (24, 'Soft Drink',                2.50,  'Your choice of chilled soft drink.',                   2),
  (24, 'Iced Lemon Tea',            2.80,  'Refreshing house-brewed lemon tea.',                   2),
  (24, 'Fresh Orange Juice',        3.80,  'Cold-pressed, no added sugar.',                        2),
  (31, '3pc Flame-Grilled Chicken', 8.90,  'Three pieces of our signature flame-grilled chicken.', NULL),
  (31, '6pc Sharing Bucket',        15.90, 'Six pieces, perfect for two to share.',                NULL),
  (31, '9pc Family Bucket',         22.90, 'Nine pieces for the whole table.',                     NULL),
  (32, 'Ember Classic Burger',      7.50,  'Grilled chicken fillet, lettuce and house sauce.',     1),
  (32, 'Spicy Zinger Burger',       8.50,  'Crispy spiced fillet with chilli mayo.',               1),
  (32, 'Double Smash Burger',       10.90, 'Two seared patties with melted cheese.',               1),
  (33, 'Seasoned Fries',            3.50,  'Golden fries with our signature seasoning.',           NULL),
  (33, 'Creamy Mash',               3.00,  'Buttery mashed potato with gravy.',                    NULL),
  (33, 'Garden Coleslaw',           2.80,  'Fresh, crunchy and lightly dressed.',                  NULL),
  (34, 'Soft Drink',                2.50,  'Your choice of chilled soft drink.',                   2),
  (34, 'Iced Lemon Tea',            2.80,  'Refreshing house-brewed lemon tea.',                   2),
  (34, 'Fresh Orange Juice',        3.80,  'Cold-pressed, no added sugar.',                        2),
  (41, '3pc Flame-Grilled Chicken', 8.90,  'Three pieces of our signature flame-grilled chicken.', NULL),
  (41, '6pc Sharing Bucket',        15.90, 'Six pieces, perfect for two to share.',                NULL),
  (41, '9pc Family Bucket',         22.90, 'Nine pieces for the whole table.',                     NULL),
  (42, 'Ember Classic Burger',      7.50,  'Grilled chicken fillet, lettuce and house sauce.',     1),
  (42, 'Spicy Zinger Burger',       8.50,  'Crispy spiced fillet with chilli mayo.',               1),
  (42, 'Double Smash Burger',       10.90, 'Two seared patties with melted cheese.',               1),
  (43, 'Seasoned Fries',            3.50,  'Golden fries with our signature seasoning.',           NULL),
  (43, 'Creamy Mash',               3.00,  'Buttery mashed potato with gravy.',                    NULL),
  (43, 'Garden Coleslaw',           2.80,  'Fresh, crunchy and lightly dressed.',                  NULL),
  (44, 'Soft Drink',                2.50,  'Your choice of chilled soft drink.',                   2),
  (44, 'Iced Lemon Tea',            2.80,  'Refreshing house-brewed lemon tea.',                   2),
  (44, 'Fresh Orange Juice',        3.80,  'Cold-pressed, no added sugar.',                        2),
  (51, '3pc Flame-Grilled Chicken', 8.90,  'Three pieces of our signature flame-grilled chicken.', NULL),
  (51, '6pc Sharing Bucket',        15.90, 'Six pieces, perfect for two to share.',                NULL),
  (51, '9pc Family Bucket',         22.90, 'Nine pieces for the whole table.',                     NULL),
  (52, 'Ember Classic Burger',      7.50,  'Grilled chicken fillet, lettuce and house sauce.',     1),
  (52, 'Spicy Zinger Burger',       8.50,  'Crispy spiced fillet with chilli mayo.',               1),
  (52, 'Double Smash Burger',       10.90, 'Two seared patties with melted cheese.',               1),
  (53, 'Seasoned Fries',            3.50,  'Golden fries with our signature seasoning.',           NULL),
  (53, 'Creamy Mash',               3.00,  'Buttery mashed potato with gravy.',                    NULL),
  (53, 'Garden Coleslaw',           2.80,  'Fresh, crunchy and lightly dressed.',                  NULL),
  (54, 'Soft Drink',                2.50,  'Your choice of chilled soft drink.',                   2),
  (54, 'Iced Lemon Tea',            2.80,  'Refreshing house-brewed lemon tea.',                   2),
  (54, 'Fresh Orange Juice',        3.80,  'Cold-pressed, no added sugar.',                        2);

-- Tables per branch (6 each; a few pre-marked as occupied for the demo)
INSERT INTO "Table" ("tableNo", "isReserved", "branchId") VALUES
  (1,0,1),(2,0,1),(3,1,1),(4,0,1),(5,0,1),(6,0,1),
  (1,0,2),(2,0,2),(3,0,2),(4,1,2),(5,0,2),(6,0,2),
  (1,0,3),(2,0,3),(3,0,3),(4,0,3),(5,0,3),(6,0,3),
  (1,0,4),(2,1,4),(3,0,4),(4,0,4),(5,0,4),(6,0,4),
  (1,0,5),(2,0,5),(3,0,5),(4,0,5),(5,1,5),(6,0,5);
