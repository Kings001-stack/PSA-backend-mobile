-- ============================================================================
-- Add Test Patients to PrimeChem Pharmacy System
-- ============================================================================
-- Purpose: Add 5 realistic test patients with phone numbers and refill data
-- Tenant: 1 (Primary test tenant)
-- Usage: Run this SQL directly in your database, OR use the PHP version below
-- ============================================================================

-- Add John Smith (Patient with 2 refills)
INSERT INTO users (name, email, password, role, tenant_id, phone, account_status, created_at, updated_at)
VALUES (
    'John Smith',
    'john.smith@example.com',
    '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY6M.KOwEiJGTYy', -- password: 'password'
    'user',
    1,
    '555-0101',
    'active',
    NOW(),
    NOW()
)
ON DUPLICATE KEY UPDATE updated_at = NOW();

-- Add Sarah Johnson (Patient with 1 refill)
INSERT INTO users (name, email, password, role, tenant_id, phone, account_status, created_at, updated_at)
VALUES (
    'Sarah Johnson',
    'sarah.j@example.com',
    '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY6M.KOwEiJGTYy', -- password: 'password'
    'user',
    1,
    '555-0102',
    'active',
    NOW(),
    NOW()
)
ON DUPLICATE KEY UPDATE updated_at = NOW();

-- Add Michael Brown (Patient with 1 refill)
INSERT INTO users (name, email, password, role, tenant_id, phone, account_status, created_at, updated_at)
VALUES (
    'Michael Brown',
    'michael.b@example.com',
    '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY6M.KOwEiJGTYy', -- password: 'password'
    'user',
    1,
    '555-0103',
    'active',
    NOW(),
    NOW()
)
ON DUPLICATE KEY UPDATE updated_at = NOW();

-- Add Emily Davis (Patient with 1 refill)
INSERT INTO users (name, email, password, role, tenant_id, phone, account_status, created_at, updated_at)
VALUES (
    'Emily Davis',
    'emily.davis@example.com',
    '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY6M.KOwEiJGTYy', -- password: 'password'
    'user',
    1,
    '555-0104',
    'active',
    NOW(),
    NOW()
)
ON DUPLICATE KEY UPDATE updated_at = NOW();

-- Add James Wilson (Patient with no refills yet)
INSERT INTO users (name, email, password, role, tenant_id, phone, account_status, created_at, updated_at)
VALUES (
    'James Wilson',
    'james.w@example.com',
    '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY6M.KOwEiJGTYy', -- password: 'password'
    'user',
    1,
    '555-0105',
    'active',
    NOW(),
    NOW()
)
ON DUPLICATE KEY UPDATE updated_at = NOW();

-- ============================================================================
-- Verification Query
-- ============================================================================
-- Run this to verify patients were added:

SELECT 
    id,
    name,
    email,
    role,
    tenant_id,
    phone,
    account_status,
    created_at
FROM users
WHERE role = 'user' 
  AND tenant_id = 1
ORDER BY id;

-- Expected Result: 6 users
-- - Staff User (staff@1.test)
-- - John Smith (john.smith@example.com)
-- - Sarah Johnson (sarah.j@example.com)
-- - Michael Brown (michael.b@example.com)
-- - Emily Davis (emily.davis@example.com)
-- - James Wilson (james.w@example.com)

-- ============================================================================
-- Optional: Add Sample Refill Requests
-- ============================================================================
-- NOTE: Replace user_id and medication_id with actual IDs from your database

-- John Smith - Pending Refill
INSERT INTO refill_requests (tenant_id, user_id, medication_id, quantity, status, notes, created_at, updated_at)
SELECT 
    1,
    id,
    1, -- Replace with actual medication_id
    30,
    'pending',
    'Please refill my medication as soon as possible',
    NOW(),
    NOW()
FROM users 
WHERE email = 'john.smith@example.com'
LIMIT 1;

-- John Smith - Approved Refill (5 days ago)
INSERT INTO refill_requests (tenant_id, user_id, medication_id, quantity, status, notes, created_at, updated_at)
SELECT 
    1,
    id,
    2, -- Replace with actual medication_id
    60,
    'approved',
    'Regular monthly refill',
    DATE_SUB(NOW(), INTERVAL 5 DAY),
    DATE_SUB(NOW(), INTERVAL 5 DAY)
FROM users 
WHERE email = 'john.smith@example.com'
LIMIT 1;

-- Sarah Johnson - Pending Refill
INSERT INTO refill_requests (tenant_id, user_id, medication_id, quantity, status, notes, created_at, updated_at)
SELECT 
    1,
    id,
    1, -- Replace with actual medication_id
    30,
    'pending',
    'Running low on medication',
    NOW(),
    NOW()
FROM users 
WHERE email = 'sarah.j@example.com'
LIMIT 1;

-- Michael Brown - Ready for Pickup (2 days ago)
INSERT INTO refill_requests (tenant_id, user_id, medication_id, quantity, status, notes, is_urgent, created_at, updated_at)
SELECT 
    1,
    id,
    3, -- Replace with actual medication_id
    90,
    'ready_for_pickup',
    'Need this urgently',
    1,
    DATE_SUB(NOW(), INTERVAL 2 DAY),
    DATE_SUB(NOW(), INTERVAL 2 DAY)
FROM users 
WHERE email = 'michael.b@example.com'
LIMIT 1;

-- Emily Davis - Collected (10 days ago)
INSERT INTO refill_requests (tenant_id, user_id, medication_id, quantity, status, notes, created_at, updated_at)
SELECT 
    1,
    id,
    2, -- Replace with actual medication_id
    30,
    'collected',
    'Thank you for the service',
    DATE_SUB(NOW(), INTERVAL 10 DAY),
    DATE_SUB(NOW(), INTERVAL 10 DAY)
FROM users 
WHERE email = 'emily.davis@example.com'
LIMIT 1;

-- ============================================================================
-- Clean Up (Use with caution - this will DELETE the test patients!)
-- ============================================================================
-- Uncomment to remove test patients:

/*
DELETE FROM users 
WHERE email IN (
    'john.smith@example.com',
    'sarah.j@example.com',
    'michael.b@example.com',
    'emily.davis@example.com',
    'james.w@example.com'
);
*/

-- ============================================================================
-- Notes
-- ============================================================================
-- All passwords are: 'password'
-- All patients belong to tenant_id = 1
-- All patients have role = 'user' (not 'pharmacist')
-- Pharmacists logged into Tenant 1 will see these patients
-- Super admins will see these patients + pharmacist accounts
