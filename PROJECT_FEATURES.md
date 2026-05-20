**Features**
*User Authentication: Secure login with password hashing and "Remember Me" functionality
*Role-Based Access: Admin, Scout, and User roles with different permissions
*Account Verification: Admin approval workflow for new accounts
*Wishlist System: Users can save their favorite travel destinations
*Post Management: Scouts can submit travel requests, admins can approve/reject
*Profile Management: Users can update their profile and change passwords
*Secure Database: Procedural mysqli with prepared statements to prevent SQL injection
*CSRF Protection: Built-in CSRF token validation


# Security Features
**Password Hashing: Uses password_hash() and password_verify()
**Procedural MySQLi: All database queries use prepared statements
**CSRF Protection: Tokens generated and validated for forms
**XSS Prevention: All output escaped with htmlspecialchars()
**Session Security: Secure session handling with regeneration
**Input Validation: Server-side validation for all user inputs
**File Upload Security: MIME type and size validation for images
