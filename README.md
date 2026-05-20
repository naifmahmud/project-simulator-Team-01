## TRAVEL GUIDE" 

# Travel Guide Web Application

A modern PHP MVC web application for managing travel destinations, built with procedural mysqli and prepared statements for security.

## Features

- **User Authentication**: Secure login with password hashing and "Remember Me" functionality
- **Role-Based Access**: Admin, Scout, and User roles with different permissions
- **Account Verification**: Admin approval workflow for new accounts
- **Wishlist System**: Users can save their favorite travel destinations
- **Post Management**: Scouts can submit travel requests, admins can approve/reject
- **Profile Management**: Users can update their profile and change passwords
- **Secure Database**: Procedural mysqli with prepared statements to prevent SQL injection
- **CSRF Protection**: Built-in CSRF token validation
- **XSS Prevention**: All output is properly escaped

## Project Structure

```
testing/
├── config/
│   ├── database.php      # Database connection and initialization
│   ├── csrf.php          # CSRF protection functions
│   └── session.php       # Session management helpers
├── controllers/
│   ├── AuthController.php    # Authentication logic
│   ├── ProfileController.php # Profile management
│   ├── HomeController.php    # Home page logic
│   ├── WishlistController.php # Wishlist operations
│   └── BrowseController.php  # Browse posts logic
├── models/
│   ├── User.php        # User database operations
│   ├── Post.php        # Post database operations
│   └── Wishlist.php    # Wishlist database operations
├── views/
│   ├── login.php           # Login form
│   ├── register.php        # Registration form
│   ├── home.php            # Home page
│   ├── profile.php         # Profile page
│   ├── wishlist.php        # Wishlist page
│   ├── browse.php          # Browse posts page
│   └── verification-notice.php # Verification notice
├── api/
│   └── wishlist.php    # Wishlist API endpoints
├── uploads/            # User uploaded files (profile pictures)
├── index.php           # Front controller
├── style.css           # Main stylesheet
├── database.sql        # Database schema
└── README.md           # This file
```

## Installation

### Prerequisites

- XAMPP or similar local server environment
- PHP 7.4 or higher
- MySQL 5.7 or higher

### Setup Steps

1. **Copy the project folder** to `C:\xampp\htdocs\testing\`

2. **Start XAMPP services**
   - Start Apache
   - Start MySQL

3. **Import the database**
   - Open phpMyAdmin: `http://localhost/phpmyadmin`
   - Click **Import** tab
   - Choose `database.sql` file
   - Click **Go**

4. **Access the application**
   - Open: `http://localhost/testing/`

5. **Default Admin Credentials**
   - Email: `admin@travelguide.com`
   - Password: `admin123`

## User Roles

### Admin
- View and verify user accounts
- Approve/reject travel post requests
- Access admin dashboard

### Scout
- Submit travel destination requests
- View own submitted requests
- Access scout dashboard

### User (General)
- Browse approved travel posts
- Add posts to wishlist
- Manage profile
- Access home page

## Security Features

- **Password Hashing**: Uses `password_hash()` and `password_verify()`
- **Procedural MySQLi**: All database queries use prepared statements
- **CSRF Protection**: Tokens generated and validated for forms
- **XSS Prevention**: All output escaped with `htmlspecialchars()`
- **Session Security**: Secure session handling with regeneration
- **Input Validation**: Server-side validation for all user inputs
- **File Upload Security**: MIME type and size validation for images

## API Endpoints

### Wishlist API

**Add to Wishlist**
- Endpoint: `POST /api/wishlist.php`
- Body: `{ "post_id": 123 }`
- Response: `{ "success": true, "message": "Added to wishlist" }`

**Remove from Wishlist**
- Endpoint: `DELETE /api/wishlist.php`
- Body: `{ "post_id": 123 }`
- Response: `{ "success": true, "message": "Removed from wishlist" }`

## Git Commands

```bash
# Create feature branch
git checkout -b feature/task1-23-51470-1

# Add all files
git add .

# Commit changes
git commit -m "Add registration form and model"
git commit -m "Implement Remember Me cookie logic"
git commit -m "Build wishlist AJAX endpoints"

# Push to remote
git push -u origin feature/task1-23-51470-1

# Create pull request
gh pr create --title "Task 1: User Authentication & Wishlist" --body "Implementation of user authentication, profile management, and wishlist functionality"
```

## Development Notes

- All database queries use procedural mysqli with prepared statements
- Never concatenate SQL strings
- Always validate input on both client and server side
- Use `htmlspecialchars()` for all output
- Store profile pictures in `uploads/` directory
- Session timeout is handled by PHP's default settings
- Remember Me cookies expire after 30 days

