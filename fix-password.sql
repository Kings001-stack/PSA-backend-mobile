UPDATE users 
SET password = '$2y$10$J98VQnVyaftQfENtWTvLBehq0M3FCVQSCfCMV86Ejx9SteFHPYSUC'
WHERE email = 'admin@1.test';

SELECT id, name, email, SUBSTRING(password, 1, 30) as password_start 
FROM users 
WHERE email = 'admin@1.test';
