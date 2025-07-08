<?php
// Database setup script for Pet Management System
// This script will create all necessary tables for the application

// Include database connection
require_once 'includes/db_connect.php';

// Function to execute SQL and handle errors
function execute_sql($conn, $sql, $description) {
    if ($conn->query($sql) === TRUE) {
        echo "<div style='color: #10b981; margin-bottom: 0.5rem;'>✓ $description - Success</div>";
    } else {
        echo "<div style='color: #ef4444; margin-bottom: 0.5rem;'>✗ $description - Error: " . $conn->error . "</div>";
    }
}

// Start with a message
echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Pet Management System - Database Setup</title>
    <link rel='stylesheet' href='css/styles.css'>
    <style>
        body {
            font-family: 'Nunito', sans-serif;
            line-height: 1.6;
            color: #3d405b;
            background-color: #f9f7f7;
            padding: 2rem;
        }
        .setup-container {
            max-width: 800px;
            margin: 0 auto;
            background-color: white;
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
        h1 {
            color: #4d9de0;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        h2 {
            margin-top: 2rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #e5e7eb;
        }
        .progress-bar {
            height: 10px;
            background-color: #e5e7eb;
            border-radius: 5px;
            margin: 2rem 0;
            overflow: hidden;
        }
        .progress-bar-fill {
            height: 100%;
            background-color: #4d9de0;
            border-radius: 5px;
            width: 0%;
            transition: width 0.5s ease;
        }
        .btn {
            display: inline-block;
            background-color: #4d9de0;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            text-decoration: none;
            font-weight: 600;
            margin-top: 1.5rem;
        }
        .btn:hover {
            background-color: #3978b3;
        }
    </style>
</head>
<body>
    <div class='setup-container'>
        <h1>Setting up Pet Management System Database</h1>
        <div class='progress-bar'>
            <div class='progress-bar-fill' id='progress'></div>
        </div>";

// Create users table
$users_sql = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20) NULL,
    user_type ENUM('owner', 'veterinary') NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
execute_sql($conn, $users_sql, "Creating users table");
echo "<script>document.getElementById('progress').style.width = '10%';</script>";

// Create pets table
$pets_sql = "CREATE TABLE IF NOT EXISTS pets (
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
)";
execute_sql($conn, $pets_sql, "Creating pets table");
echo "<script>document.getElementById('progress').style.width = '20%';</script>";

// Create health_records table
$health_records_sql = "CREATE TABLE IF NOT EXISTS health_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pet_id INT NOT NULL,
    record_type VARCHAR(50) NOT NULL,
    record_date DATE NOT NULL,
    description TEXT,
    vet_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pet_id) REFERENCES pets(id) ON DELETE CASCADE,
    FOREIGN KEY (vet_id) REFERENCES users(id) ON DELETE SET NULL
)";
execute_sql($conn, $health_records_sql, "Creating health_records table");
echo "<script>document.getElementById('progress').style.width = '30%';</script>";

// Create appointments table
$appointments_sql = "CREATE TABLE IF NOT EXISTS appointments (
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
)";
execute_sql($conn, $appointments_sql, "Creating appointments table");
echo "<script>document.getElementById('progress').style.width = '40%';</script>";

// Create reminders table
$reminders_sql = "CREATE TABLE IF NOT EXISTS reminders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pet_id INT NOT NULL,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    reminder_date DATE NOT NULL,
    is_completed TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pet_id) REFERENCES pets(id) ON DELETE CASCADE
)";
execute_sql($conn, $reminders_sql, "Creating reminders table");
echo "<script>document.getElementById('progress').style.width = '50%';</script>";

// Create medications table
$medications_sql = "CREATE TABLE IF NOT EXISTS medications (
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
)";
execute_sql($conn, $medications_sql, "Creating medications table");
echo "<script>document.getElementById('progress').style.width = '60%';</script>";

// Create vaccinations table
$vaccinations_sql = "CREATE TABLE IF NOT EXISTS vaccinations (
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
)";
execute_sql($conn, $vaccinations_sql, "Creating vaccinations table");
echo "<script>document.getElementById('progress').style.width = '70%';</script>";

// Create notifications table
$notifications_sql = "CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";
execute_sql($conn, $notifications_sql, "Creating notifications table");
echo "<script>document.getElementById('progress').style.width = '80%';</script>";

// Create settings table
$settings_sql = "CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    notification_preferences JSON,
    theme VARCHAR(20) DEFAULT 'light',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";
execute_sql($conn, $settings_sql, "Creating settings table");
echo "<script>document.getElementById('progress').style.width = '85%';</script>";

// Create vet_codes table
$vet_codes_sql = "CREATE TABLE IF NOT EXISTS vet_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) NOT NULL UNIQUE,
    description VARCHAR(255),
    is_used TINYINT(1) DEFAULT 0,
    used_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    used_at TIMESTAMP NULL,
    FOREIGN KEY (used_by) REFERENCES users(id) ON DELETE SET NULL
)";
execute_sql($conn, $vet_codes_sql, "Creating vet_codes table");
echo "<script>document.getElementById('progress').style.width = '90%';</script>";

// Generate some initial vet codes
$codes = [
    'VET-' . strtoupper(substr(md5(uniqid()), 0, 8)),
    'VET-' . strtoupper(substr(md5(uniqid()), 0, 8)),
    'VET-' . strtoupper(substr(md5(uniqid()), 0, 8))
];

$descriptions = [
    'Initial code 1',
    'Initial code 2',
    'Initial code 3'
];

$insert_sql = "INSERT INTO vet_codes (code, description) VALUES (?, ?)";
$insert_stmt = $conn->prepare($insert_sql);

for ($i = 0; $i < count($codes); $i++) {
    $insert_stmt->bind_param("ss", $codes[$i], $descriptions[$i]);
    $insert_stmt->execute();
}
execute_sql($conn, "SELECT 1", "Generating initial vet codes");

// Create default admin user if not exists
$default_password = password_hash("admin123", PASSWORD_DEFAULT);
$check_admin_sql = "SELECT id FROM users WHERE username = 'admin'";
$result = $conn->query($check_admin_sql);

if ($result->num_rows == 0) {
    $admin_sql = "INSERT INTO users (username, password, email, user_type, is_active) 
                  VALUES ('admin', '$default_password', 'admin@petmanagement.com', 'veterinary', 1)";
    execute_sql($conn, $admin_sql, "Creating default admin user");
    echo "<div style='background-color: #fef3c7; color: #92400e; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;'>
            <strong>Default admin credentials:</strong><br>
            Username: admin<br>
            Password: admin123<br>
            <strong>Please change this password after first login!</strong>
          </div>";
}

// Create default pet owner user if not exists
$default_owner_password = password_hash("owner123", PASSWORD_DEFAULT);
$check_owner_sql = "SELECT id FROM users WHERE username = 'owner'";
$result = $conn->query($check_owner_sql);

if ($result->num_rows == 0) {
    $owner_sql = "INSERT INTO users (username, password, email, user_type, is_active) 
                  VALUES ('owner', '$default_owner_password', 'owner@petmanagement.com', 'owner', 1)";
    execute_sql($conn, $owner_sql, "Creating default owner user");
    echo "<div style='background-color: #fef3c7; color: #92400e; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;'>
            <strong>Default owner credentials:</strong><br>
            Username: owner<br>
            Password: owner123<br>
            <strong>Please change this password after first login!</strong>
          </div>";
}

echo "<script>document.getElementById('progress').style.width = '100%';</script>";

echo "<h2>Database setup completed!</h2>
      <p>All necessary tables have been created and default users have been set up.</p>
      <p>You can now <a href='index.php' class='btn'>return to the homepage</a> and start using the application.</p>
    </div>
    
    <script>
        // Animate progress bar
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                document.getElementById('progress').style.width = '100%';
            }, 500);
        });
    </script>
</body>
</html>";
?>
