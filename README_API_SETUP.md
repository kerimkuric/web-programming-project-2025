# FlightPHP API Setup Instructions

## Prerequisites

1. **PHP** (version 7.4 or higher) installed on your system
2. **Composer** installed (download from https://getcomposer.org/download/)
3. **Web server** (Apache/Nginx) or PHP built-in server
4. **MySQL** database running

## Setup Steps

### 1. Install Dependencies

Open terminal/command prompt in the project root directory and run:

```bash
composer install
```

This will:
- Create a `vendor/` folder
- Install FlightPHP framework
- Set up autoloading

### 2. Database Setup

Make sure your database is configured in `backend/config/Database.php`:

```php
private static $host = 'localhost';
private static $dbname = 'library_schema';
private static $username = 'root';
private static $password = '';
```

Run the database setup script:
```bash
php backend/setup.php
```

### 3. Start the Server

#### Option A: Using PHP Built-in Server
```bash
cd backend
php -S localhost:8000
```

#### Option B: Using Apache/XAMPP
- Place project in your web server directory (e.g., `htdocs` or `www`)
- Access via: `http://localhost/web-programming-project-2025/backend/`

### 4. Test the API

The API will be available at:
- **Base URL**: `http://localhost:8000/` (if using PHP built-in server)
- **Base URL**: `http://localhost/web-programming-project-2025/backend/` (if using Apache)

## API Endpoints

### Users
- `GET /api/users` - Get all users
- `GET /api/users/{id}` - Get user by ID
- `GET /api/users/email/{email}` - Get user by email
- `POST /api/users` - Create new user
- `PUT /api/users/{id}` - Update user
- `DELETE /api/users/{id}` - Delete user

### Authors
- `GET /api/authors` - Get all authors
- `GET /api/authors/{id}` - Get author by ID
- `GET /api/authors/country/{country}` - Get authors by country
- `POST /api/authors` - Create new author
- `PUT /api/authors/{id}` - Update author
- `DELETE /api/authors/{id}` - Delete author

### Genres
- `GET /api/genres` - Get all genres
- `GET /api/genres/{id}` - Get genre by ID
- `POST /api/genres` - Create new genre
- `PUT /api/genres/{id}` - Update genre
- `DELETE /api/genres/{id}` - Delete genre

### Books
- `GET /api/books` - Get all books
- `GET /api/books/{id}` - Get book by ID
- `GET /api/books/author/{authorId}` - Get books by author
- `GET /api/books/genre/{genreId}` - Get books by genre
- `POST /api/books` - Create new book
- `PUT /api/books/{id}` - Update book
- `DELETE /api/books/{id}` - Delete book

### Borrowings
- `GET /api/borrowings` - Get all borrowings
- `GET /api/borrowings/{id}` - Get borrowing by ID
- `GET /api/borrowings/user/{userId}` - Get borrowings by user
- `GET /api/borrowings/book/{bookId}` - Get borrowings by book
- `GET /api/borrowings/active` - Get active borrowings (not returned)
- `POST /api/borrowings` - Create new borrowing
- `POST /api/borrowings/{id}/return` - Return a book
- `PUT /api/borrowings/{id}` - Update borrowing
- `DELETE /api/borrowings/{id}` - Delete borrowing

## Testing with Postman

### Example: Create a User

1. Open Postman
2. Create a new request
3. Set method to **POST**
4. URL: `http://localhost:8000/api/users`
5. Go to **Body** tab
6. Select **raw** and **JSON** format
7. Paste this JSON:
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "1234567890",
  "password": "password123",
  "is_admin": 0
}
```
8. Click **Send**

### Example: Get All Users

1. Set method to **GET**
2. URL: `http://localhost:8000/api/users`
3. Click **Send**

### Example: Update a User

1. Set method to **PUT**
2. URL: `http://localhost:8000/api/users/1`
3. Body (JSON):
```json
{
  "name": "John Updated",
  "email": "john.updated@example.com"
}
```
4. Click **Send**

### Example: Delete a User

1. Set method to **DELETE**
2. URL: `http://localhost:8000/api/users/1`
3. Click **Send**

## Response Format

All responses are in JSON format:

**Success Response:**
```json
{
  "success": true,
  "data": { ... },
  "message": "Operation successful"
}
```

**Error Response:**
```json
{
  "success": false,
  "message": "Error message here"
}
```

## Troubleshooting

### Issue: "Class not found" errors
- Make sure you ran `composer install`
- Check that `vendor/autoload.php` exists

### Issue: Database connection errors
- Verify database credentials in `backend/config/Database.php`
- Make sure MySQL is running
- Run `php backend/setup.php` to create database

### Issue: 404 Not Found
- Check that `.htaccess` file exists in `backend/` directory
- Verify mod_rewrite is enabled (for Apache)
- Try accessing `http://localhost:8000/index.php/api/users` directly

### Issue: CORS errors (when testing from browser)
- CORS headers are already set in `index.php`
- Make sure you're using the correct base URL

## Project Structure

```
backend/
├── config/
│   └── Database.php          # Database configuration
├── dao/                      # Data Access Objects
│   ├── BaseDao.php
│   ├── UserDao.php
│   ├── AuthorDao.php
│   ├── GenreDao.php
│   ├── BookDao.php
│   └── BorrowingDao.php
├── services/                 # Business Logic Layer
│   ├── BaseService.php
│   ├── UserService.php
│   ├── AuthorService.php
│   ├── GenreService.php
│   ├── BookService.php
│   └── BorrowingService.php
├── routes/                   # API Routes
│   ├── user_routes.php
│   ├── author_routes.php
│   ├── genre_routes.php
│   ├── book_routes.php
│   └── borrowing_routes.php
├── index.php                 # API Entry Point
└── .htaccess                 # URL Rewriting
```

