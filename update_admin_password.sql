UPDATE users SET password_hash='$2y$10$/U/H3TGwRDFk3XEWnYHSz.6RARXYPdI8K36B1YezYVJybRGFUFoJu' WHERE email='admin@test.com';
SELECT id, email, password_hash FROM users WHERE email='admin@test.com';
