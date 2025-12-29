UPDATE users SET password_hash='$2y$10$DQ8Lfr6OiYpGlM06ppz4sundpWFdJysh5USxkz8.d5zVvTsN5A.wm' WHERE email='user@test.com';
SELECT id, email, password_hash FROM users WHERE email='user@test.com';
