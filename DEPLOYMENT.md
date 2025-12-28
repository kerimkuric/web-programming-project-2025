# Deployment Guide

This guide will help you deploy the Library Management System to a hosting platform.

## Pre-Deployment Checklist

### 1. Git Workflow (IMPORTANT - Do this FIRST!)

Before deploying, you MUST commit and push your code:

```bash
# Check what files have changed
git status

# Add all changes
git add .

# Commit with a message
git commit -m "Complete MVC refactoring and prepare for deployment"

# Push to your repository
git push origin main
# (or git push origin master if your main branch is called master)
```

**Why?** Most hosting platforms (Heroku, DigitalOcean, AWS) deploy directly from your Git repository. They need the latest code!

---

## Deployment Options

### Option 1: Heroku (Recommended - Easiest)

Heroku is the easiest platform for PHP applications.

#### Prerequisites:
- Heroku account (free tier available): https://www.heroku.com
- Heroku CLI installed: https://devcenter.heroku.com/articles/heroku-cli

#### Steps:

1. **Login to Heroku:**
   ```bash
   heroku login
   ```

2. **Create a Heroku app:**
   ```bash
   heroku create your-app-name
   # Example: heroku create library-management-2025
   ```

3. **Add MySQL Database (ClearDB or JawsDB):**
   ```bash
   # Option A: ClearDB (Free tier available)
   heroku addons:create cleardb:ignite
   
   # Option B: JawsDB (Free tier available)
   heroku addons:create jawsdb:kitefin
   ```

4. **Get Database URL:**
   ```bash
   heroku config:get CLEARDB_DATABASE_URL
   # or
   heroku config:get JAWSDB_URL
   ```

5. **Set Environment Variables:**
   ```bash
   heroku config:set JWT_SECRET="your-super-secret-jwt-key-change-this"
   heroku config:set APP_ENV="production"
   ```

6. **Deploy:**
   ```bash
   git push heroku main
   # or git push heroku master
   ```

7. **Open your app:**
   ```bash
   heroku open
   ```

#### Important Notes:
- Your app URL will be: `https://your-app-name.herokuapp.com`
- Update `frontend/utils/constants.js` with your Heroku URL
- The database will be automatically configured via the addon

---

### Option 2: DigitalOcean App Platform

#### Prerequisites:
- DigitalOcean account: https://www.digitalocean.com
- Credit card (they have a free trial)

#### Steps:

1. **Go to DigitalOcean App Platform**
2. **Create New App** → Connect your GitHub repository
3. **Configure:**
   - **Backend:**
     - Build Command: `composer install`
     - Run Command: `php -S 0.0.0.0:8080 -t backend backend/index.php`
     - Environment Variables:
       - `DB_HOST` (from database)
       - `DB_NAME`
       - `DB_USER`
       - `DB_PASSWORD`
       - `JWT_SECRET`
   - **Frontend:**
     - Build Command: (none needed for static files)
     - Output Directory: `frontend`
   - **Database:**
     - Add MySQL database
     - Note the connection details

4. **Deploy** - DigitalOcean will automatically deploy from your Git repo

---

### Option 3: AWS (More Complex)

For AWS, you would typically use:
- **EC2** for the server
- **RDS** for MySQL database
- **S3 + CloudFront** for frontend (or serve from EC2)

This is more complex and requires AWS knowledge.

---

## Post-Deployment Steps

### 1. Update Frontend Constants

After deployment, update `frontend/utils/constants.js`:

```javascript
let Constants = {
    PROJECT_BASE_URL: "https://your-app-name.herokuapp.com/api/",
    // or your actual deployed URL
    USER_ROLE: "user",
    ADMIN_ROLE: "admin"
}
```

### 2. Initialize Database

Run your database schema:
- Connect to your hosted database
- Import `database/library_schema.sql`

Or use the setup script if your hosting allows:
```bash
php backend/setup.php
```

### 3. Test Your Deployment

1. Visit your frontend URL
2. Try registering a new user
3. Test login
4. Verify all features work

### 4. Update README.md

Add your live link to README.md:
```markdown
## Live Application

**Deployed URL:** https://your-app-name.herokuapp.com

**API Documentation:** https://your-app-name.herokuapp.com/api/docs
```

---

## Troubleshooting

### Common Issues:

1. **CORS Errors:**
   - Check that CORS headers are set in `backend/index.php`
   - Verify frontend URL matches backend allowed origins

2. **Database Connection:**
   - Verify environment variables are set correctly
   - Check database credentials

3. **404 Errors:**
   - Verify `.htaccess` file is present
   - Check routing configuration

4. **Environment Variables:**
   - Make sure all required env vars are set on your hosting platform

---

## Security Checklist

Before going live:

- [ ] Change JWT_SECRET to a strong random string
- [ ] Use environment variables for all sensitive data
- [ ] Enable HTTPS (most platforms do this automatically)
- [ ] Review CORS settings
- [ ] Disable error display in production (update Config.php)
- [ ] Use strong database passwords

---

## Need Help?

If you encounter issues:
1. Check hosting platform logs
2. Verify all environment variables are set
3. Test API endpoints directly
4. Check browser console for frontend errors

