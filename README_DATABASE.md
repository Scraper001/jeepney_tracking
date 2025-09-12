# Database Setup Instructions

## Requirements
- MySQL/MariaDB server
- PHP with mysqli extension

## Database Setup

1. Create the database:
```sql
CREATE DATABASE IF NOT EXISTS jeepney_db;
```

2. Create the users table:
```sql
USE jeepney_db;
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    fname VARCHAR(50) NOT NULL,
    mname VARCHAR(50) NULL,
    lname VARCHAR(50) NOT NULL,
    region VARCHAR(50) NOT NULL,
    province VARCHAR(50) NOT NULL,
    city VARCHAR(50) NOT NULL,
    barangay VARCHAR(50) NOT NULL,
    phone VARCHAR(15) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

3. Update connection settings in `connection/connection.php` if needed:
   - Host: localhost (default)
   - User: root (default)
   - Password: empty (default)
   - Database: jeepney_db

## Features Implemented

### 1. Enhanced Registration Form
- Added email field with validation
- Added phone number field with Philippine format validation
- Added middle name field (optional)
- Removed username field as per requirements
- Updated address structure to match requirements

### 2. Backend Validation
- Complete input validation for all required fields
- Email format validation using filter_var()
- Philippine phone number validation (09xxxxxxxxx format)
- Secure password hashing with password_hash()
- Duplicate email checking
- Prepared statements for SQL injection protection

### 3. Frontend Validation
- Real-time form validation with visual indicators
- Email format validation
- Phone number format validation
- Password strength indicator
- Password confirmation matching
- Required field validation

### 4. SweetAlert Integration
- Placeholder comments for SweetAlert2 implementation
- Sample message formats as specified in requirements
- Currently uses basic alerts for testing (replace with SweetAlert in production)

### 5. Security Features
- Password hashing with PHP's password_hash()
- Prepared SQL statements
- Input sanitization
- Email uniqueness constraint

## Usage

1. Navigate to `register.php`
2. Fill out all required fields
3. Select region, province, city, and barangay
4. Enter a secure password
5. Confirm password and accept terms
6. Click "Create account"

The system will validate all inputs and create the user account if validation passes.