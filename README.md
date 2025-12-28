# Library Management System 📚

Hey there! This is a web application I built for managing library operations. It helps libraries (or anyone really) keep track of books, authors, genres, users, and who's borrowing what.

## What Can It Do?

- **User Accounts**: People can register and login securely
- **Book Management**: Add, edit, and organize books in the library
- **Author & Genre Management**: Keep track of who wrote what and categorize books
- **Borrowing System**: Track which books are borrowed and when they're returned
- **Admin Panel**: Admins get extra powers to manage everything
- **Dashboard**: Nice overview of what's happening in the library

## What I Used to Build This

**Backend (The Brain):**
- PHP with FlightPHP framework
- MySQL database to store everything
- JWT tokens for secure authentication
- RESTful API so the frontend can talk to the backend

**Frontend (What You See):**
- Plain JavaScript (no frameworks, just clean code)
- Bootstrap for making it look nice
- jQuery for some interactive stuff
- Follows MVC pattern with Services

## How to Run It Locally

Want to test it on your computer? Here's how:

### What You Need First
- PHP 7.4 or newer
- MySQL database
- Composer (for PHP packages)
- A web server (or just use PHP's built-in one)

### Setup Steps

1. **Get the code**
   ```bash
   git clone <your-repo-url>
   cd web-programming-project-2025
   ```

2. **Install the PHP packages**
   ```bash
   composer install
   ```

3. **Set up your database**
   - Open `backend/config/Config.php` and put in your database details
   - Create the database by running:
     ```bash
     mysql -u root -p < database/library_schema.sql
     ```
   - Or use the setup script: `php backend/setup.php`

4. **Start the backend server**
   ```bash
   php -S localhost:8080 -t backend backend/index.php
   ```

5. **Configure the frontend**
   - Open `frontend/utils/constants.js`
   - Make sure it says: `PROJECT_BASE_URL: "http://localhost:8080/api/"`

6. **Open it up!**
   - Just open `frontend/index.html` in your browser
   - Or serve it with a simple server if you prefer

## Deployment

So you want to put this online? Cool! Here's the deal:

### First Things First - Git

**You MUST commit and push your code first!** Most hosting platforms pull directly from your Git repository, so they need your latest code.

```bash
# See what changed
git status

# Add everything
git add .

# Commit it
git commit -m "Ready to deploy!"

# Push to GitHub/GitLab/wherever
git push origin main
```

### Deploying to Heroku (Easiest Option)

Heroku is probably the simplest way to get this online. Here's how:

1. **Get Heroku CLI** (if you don't have it)
   - Go to https://devcenter.heroku.com/articles/heroku-cli and install it

2. **Login to Heroku**
   ```bash
   heroku login
   ```

3. **Create your app**
   ```bash
   heroku create your-app-name
   # Pick a cool name! Like "my-library-2025"
   ```

4. **Add a database**
   ```bash
   heroku addons:create cleardb:ignite
   # This gives you a free MySQL database
   ```

5. **Set your secret key**
   ```bash
   heroku config:set JWT_SECRET="make-this-something-random-and-secret"
   heroku config:set APP_ENV="production"
   ```

6. **Deploy!**
   ```bash
   git push heroku main
   ```
   This will take a minute or two. Heroku will build and deploy your app.

7. **Get your URL**
   After it finishes, Heroku will tell you your app URL. It'll be something like:
   `https://your-app-name.herokuapp.com`

8. **Update the frontend**
   - Open `frontend/utils/constants.js`
   - Change the URL to your Heroku URL:
     ```javascript
     PROJECT_BASE_URL: "https://your-app-name.herokuapp.com/api/"
     ```
   - Commit and push again:
     ```bash
     git add frontend/utils/constants.js
     git commit -m "Update for production"
     git push origin main
     git push heroku main
     ```

9. **Set up the database**
   - Get your database connection info:
     ```bash
     heroku config:get CLEARDB_DATABASE_URL
     ```
   - Connect to it (using MySQL Workbench, phpMyAdmin, or command line)
   - Import the schema: `database/library_schema.sql`

10. **Test it!**
    - Visit your Heroku URL
    - Try registering a user
    - See if everything works!

### Other Options

**DigitalOcean** - Also good, a bit more setup but more control
**AWS** - More powerful but more complex (probably overkill for this project)

See `DEPLOYMENT.md` for more details on other platforms.

## API Endpoints

The backend has a bunch of endpoints. Here are the main ones:

**Authentication:**
- `POST /api/auth/register` - Sign up
- `POST /api/auth/login` - Log in

**Books:**
- `GET /api/books` - See all books
- `POST /api/books` - Add a book (admin only)
- `PUT /api/books/{id}` - Update a book (admin only)
- `DELETE /api/books/{id}` - Delete a book (admin only)

**Authors, Genres, Users, Borrowings** - Similar pattern

Check out the Swagger docs at `/public/v1/docs/index.php` for the full API documentation!

## Default Users

After setting up the database, you can:
- Register through the website (normal way)
- Or use the SQL scripts in the root folder to create/update admin users

## Security Stuff

I tried to make this secure:
- Passwords are hashed (never stored in plain text)
- JWT tokens for authentication
- Input validation on both frontend and backend
- Protection against XSS attacks
- SQL injection prevention (using PDO)

## Project Structure

```
├── backend/          # PHP API stuff
│   ├── config/      # Settings
│   ├── dao/         # Database access
│   ├── routes/      # API endpoints
│   ├── services/    # Business logic
│   └── index.php    # Entry point
├── frontend/        # JavaScript frontend
│   ├── services/    # All the logic (MVC pattern)
│   ├── views/       # HTML pages
│   └── utils/       # Helper stuff
└── database/        # SQL schema
```

## Testing

Want to test things? Run:
```bash
php backend/test_all.php
```

## API Documentation

Once running, check out:
- Local: `http://localhost:8080/public/v1/docs/index.php`
- Production: `[your-url]/public/v1/docs/index.php`

## Live Application

**Deployed URL:** [Add your deployed URL here after you deploy!]

Once you deploy, come back and add the link here so people can see your live app!

---

## Notes

- This was built for a web programming course project
- Feel free to use it, modify it, learn from it
- If you find bugs or have suggestions, let me know!

**Happy coding! 🚀**
