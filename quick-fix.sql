-- Update password for admin@1.test
-- Password: "password" (bcrypt hash with cost=10)
UPDATE users 
SET password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
WHERE email = 'admin@1.test';

SELECT id, name, email, role FROM users WHERE email = 'admin@1.test';
