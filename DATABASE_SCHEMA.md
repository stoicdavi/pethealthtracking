# Pet Management System Database Schema

This document provides a detailed overview of the database schema used in the Pet Management System.

## Tables

### users
Stores user account information for both pet owners and veterinarians.

```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20) NULL,
    user_type ENUM('owner', 'veterinary') NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)
```

### pets
Stores information about pets registered in the system.

```sql
CREATE TABLE pets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    owner_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    species VARCHAR(50) NOT NULL,
    breed VARCHAR(100),
    date_of_birth DATE,
    gender ENUM('male', 'female', 'unknown') NOT NULL,
    weight DECIMAL(5,2),
    notes TEXT,
    image_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE
)
```

### health_records
Stores health records for pets.

```sql
CREATE TABLE health_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pet_id INT NOT NULL,
    record_type VARCHAR(50) NOT NULL,
    record_date DATE NOT NULL,
    description TEXT,
    vet_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pet_id) REFERENCES pets(id) ON DELETE CASCADE,
    FOREIGN KEY (vet_id) REFERENCES users(id) ON DELETE SET NULL
)
```

### appointments
Stores veterinary appointment information.

```sql
CREATE TABLE appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pet_id INT NOT NULL,
    vet_id INT,
    appointment_date DATE NOT NULL,
    appointment_time TIME,
    reason VARCHAR(100) NOT NULL,
    notes TEXT,
    status ENUM('pending', 'approved', 'confirmed', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pet_id) REFERENCES pets(id) ON DELETE CASCADE,
    FOREIGN KEY (vet_id) REFERENCES users(id) ON DELETE SET NULL
)
```

### reminders
Stores vaccination and medication reminders.

```sql
CREATE TABLE reminders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pet_id INT NOT NULL,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    reminder_date DATE NOT NULL,
    is_completed TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pet_id) REFERENCES pets(id) ON DELETE CASCADE
)
```

### medications
Stores medication information for pets.

```sql
CREATE TABLE medications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pet_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    dosage VARCHAR(50) NOT NULL,
    frequency VARCHAR(50) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pet_id) REFERENCES pets(id) ON DELETE CASCADE
)
```

### vaccinations
Stores vaccination records for pets.

```sql
CREATE TABLE vaccinations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pet_id INT NOT NULL,
    vaccine_name VARCHAR(100) NOT NULL,
    vaccination_date DATE NOT NULL,
    expiration_date DATE,
    administered_by INT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pet_id) REFERENCES pets(id) ON DELETE CASCADE,
    FOREIGN KEY (administered_by) REFERENCES users(id) ON DELETE SET NULL
)
```

### notifications
Stores user notifications.

```sql
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)
```

### settings
Stores user preferences and settings.

```sql
CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    notification_preferences JSON,
    theme VARCHAR(20) DEFAULT 'light',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)
```

### vet_codes
Stores veterinarian registration codes.

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
)
```

## Entity Relationship Diagram

```
users 1 --- * pets
users 1 --- * health_records (as vet_id)
users 1 --- * appointments (as vet_id)
users 1 --- * vaccinations (as administered_by)
users 1 --- * notifications
users 1 --- * settings
users 1 --- 0..1 vet_codes (as used_by)

pets 1 --- * health_records
pets 1 --- * appointments
pets 1 --- * reminders
pets 1 --- * medications
pets 1 --- * vaccinations
```

## Default Data

The setup script creates the following default users:

1. **Admin User**
   - Username: admin
   - Password: admin123
   - Email: admin@petmanagement.com
   - Type: veterinary
   - Status: active

2. **Pet Owner User**
   - Username: owner
   - Password: owner123
   - Email: owner@petmanagement.com
   - Type: owner
   - Status: active

3. **Initial Vet Codes**
   - Three random vet codes are generated during setup
   - Format: VET-XXXXXXXX (where X is an alphanumeric character)
   - These codes can be used for veterinarian registration
