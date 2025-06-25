# Veterinarian Registration System

This document explains the secure veterinarian registration process implemented in the Pet Management System.

## Overview

To ensure that only legitimate veterinarians can register as vets in the system, we've implemented a registration code system. Each veterinarian must provide a valid registration code during the sign-up process.

## How It Works

1. **Admin generates unique vet codes**
   - Only the admin user can generate and manage vet registration codes
   - Each code is unique and can only be used once
   - Codes follow the format `VET-XXXXXXXX` where X is an alphanumeric character

2. **Veterinarian registration process**
   - When a user selects "Veterinarian" as their account type during registration
   - They must provide a valid, unused registration code
   - The system validates the code before completing registration
   - Once used, a code cannot be used again

3. **Code management**
   - Admin can generate new codes as needed
   - Admin can view all codes, their status, and which vet used each code
   - Admin can delete unused codes

## Admin Instructions

### Accessing the Vet Code Management

1. Log in as the admin user
2. Go to the Dashboard
3. In the Admin Tools section, click on "Manage Vet Codes"

### Generating New Codes

1. On the Manage Vet Codes page, use the "Generate New Veterinarian Codes" form
2. Enter an optional description (e.g., "For Dr. Smith's clinic")
3. Select how many codes to generate (1-10)
4. Click "Generate Code(s)"

### Distributing Codes

Distribute the generated codes to veterinarians through secure channels:
- Direct email to verified veterinary clinics
- In-person at professional veterinary events
- Through veterinary associations

**Important:** Never share vet codes publicly or through unsecured channels.

## Security Considerations

- Each code can only be used once
- Codes are stored securely in the database
- The system tracks which user used each code and when
- Only the admin can generate and manage codes
- Failed registration attempts with invalid codes are logged

## Database Structure

The vet codes are stored in the `vet_codes` table with the following structure:

```sql
CREATE TABLE vet_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) NOT NULL UNIQUE,
    description VARCHAR(255),
    is_used TINYINT(1) DEFAULT 0,
    used_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    used_at TIMESTAMP NULL,
    FOREIGN KEY (used_by) REFERENCES users(id) ON DELETE SET NULL
);
```
