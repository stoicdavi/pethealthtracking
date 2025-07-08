<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include utility functions
require_once __DIR__ . '/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Pet Management System</title>
    <link rel="stylesheet" href="css/styles.css">
    <!-- Font Awesome for icons - Use local version if available, otherwise fallback to CDN -->
    <?php if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/pet_management_app/vendor/fontawesome/css/all.min.css')): ?>
        <link rel="stylesheet" href="vendor/fontawesome/css/all.min.css">
    <?php else: ?>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <?php endif; ?>
</head>
<body>
    <!-- Fixed Navigation Bar -->
    <nav>
        <div class="container">
            <!-- Top Navigation Bar -->
            <div class="flex justify-between items-center">
                <a href="index.php" class="nav-logo">
                    <i class="fas fa-paw"></i>
                    <span>Pet Management</span>
                </a>
                
                <!-- Desktop Navigation -->
                <div class="nav-links">
                    <a href="index.php">
                        <i class="fas fa-home mr-1"></i> Home
                    </a>
                    
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="dashboard.php">
                            <i class="fas fa-tachometer-alt mr-1"></i> Dashboard
                        </a>
                        
                        <?php if (!is_admin()): ?>
                        <!-- Pets Dropdown -->
                        <div class="dropdown">
                            <div class="dropdown-button">
                                <i class="fas fa-dog mr-1"></i> 
                                <?php echo $_SESSION['user_type'] === 'veterinary' ? 'Patients' : 'Pets'; ?>
                                <i class="fas fa-chevron-down"></i>
                            </div>
                            <div class="dropdown-menu">
                                <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'owner'): ?>
                                    <a href="add_pet.php">
                                        <i class="fas fa-plus mr-2"></i> Add New Pet
                                    </a>
                                <?php endif; ?>
                                <a href="my_pets.php">
                                    <i class="fas fa-list mr-2"></i> 
                                    <?php echo $_SESSION['user_type'] === 'veterinary' ? 'My Patients' : 'My Pets'; ?>
                                </a>
                            </div>
                        </div>
                        
                        <!-- Appointments Dropdown -->
                        <div class="dropdown">
                            <div class="dropdown-button">
                                <i class="fas fa-calendar-alt mr-1"></i> Appointments
                                <i class="fas fa-chevron-down"></i>
                            </div>
                            <div class="dropdown-menu">
                                <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'owner'): ?>
                                    <a href="add_appointment.php">
                                        <i class="fas fa-plus mr-2"></i> Schedule Appointment
                                    </a>
                                <?php endif; ?>
                                <a href="my_appointments.php">
                                    <i class="fas fa-list mr-2"></i> My Appointments
                                </a>
                            </div>
                        </div>
                        
                        <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'owner'): ?>
                            <!-- Health Records Dropdown -->
                            <div class="dropdown">
                                <div class="dropdown-button">
                                    <i class="fas fa-heartbeat mr-1"></i> Health
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                                <div class="dropdown-menu">
                                    <a href="add_reminder.php">
                                        <i class="fas fa-bell mr-2"></i> Add Reminder
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php endif; ?>
                        
                        <?php if (is_admin()): ?>
                        <!-- Admin Links -->
                        <a href="admin/index.php">
                            <i class="fas fa-cogs mr-1"></i> Admin Panel
                        </a>
                        <?php endif; ?>
                        
                        <!-- User Account Dropdown -->
                        <div class="dropdown">
                            <div class="dropdown-button">
                                <i class="fas fa-user-circle mr-1"></i>
                                <?php echo htmlspecialchars($_SESSION['username'] ?? 'Account'); ?>
                                <i class="fas fa-chevron-down"></i>
                            </div>
                            <div class="dropdown-menu">
                                <a href="profile.php">
                                    <i class="fas fa-user mr-2"></i> My Profile
                                </a>
                                <a href="settings.php">
                                    <i class="fas fa-cog mr-2"></i> Settings
                                </a>
                                <div class="dropdown-divider"></div>
                                <a href="logout.php">
                                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="about.php">
                            <i class="fas fa-info-circle mr-1"></i> About
                        </a>
                        <a href="services.php">
                            <i class="fas fa-concierge-bell mr-1"></i> Services
                        </a>
                        <a href="contact.php">
                            <i class="fas fa-envelope mr-1"></i> Contact
                        </a>
                        <a href="login.php">
                            <i class="fas fa-sign-in-alt mr-1"></i> Login
                        </a>
                        <a href="register.php" class="nav-button">
                            <i class="fas fa-user-plus mr-1"></i> Register
                        </a>
                    <?php endif; ?>
                </div>
                
                <!-- Mobile menu button -->
                <button class="mobile-menu-button" id="mobile-menu-button">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            
            <!-- Mobile Menu -->
            <div class="mobile-menu" id="mobile-menu">
                <a href="index.php">
                    <i class="fas fa-home mr-2"></i> Home
                </a>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="dashboard.php">
                        <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
                    </a>
                    
                    <?php if (!is_admin()): ?>
                    <!-- Mobile Pets Section -->
                    <div class="mobile-dropdown">
                        <button class="mobile-dropdown-button">
                            <i class="fas fa-dog mr-2"></i> 
                            <?php echo $_SESSION['user_type'] === 'veterinary' ? 'Patients' : 'Pets'; ?>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="mobile-dropdown-content">
                            <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'owner'): ?>
                                <a href="add_pet.php">
                                    <i class="fas fa-plus mr-2"></i> Add New Pet
                                </a>
                            <?php endif; ?>
                            <a href="my_pets.php">
                                <i class="fas fa-list mr-2"></i> 
                                <?php echo $_SESSION['user_type'] === 'veterinary' ? 'My Patients' : 'My Pets'; ?>
                            </a>
                        </div>
                    </div>
                    
                    <!-- Mobile Appointments Section -->
                    <div class="mobile-dropdown">
                        <button class="mobile-dropdown-button">
                            <i class="fas fa-calendar-alt mr-2"></i> Appointments <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="mobile-dropdown-content">
                            <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'owner'): ?>
                                <a href="add_appointment.php">
                                    <i class="fas fa-plus mr-2"></i> Schedule Appointment
                                </a>
                            <?php endif; ?>
                            <a href="my_appointments.php">
                                <i class="fas fa-list mr-2"></i> My Appointments
                            </a>
                        </div>
                    </div>
                    
                    <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'owner'): ?>
                        <!-- Mobile Health Section -->
                        <div class="mobile-dropdown">
                            <button class="mobile-dropdown-button">
                                <i class="fas fa-heartbeat mr-2"></i> Health <i class="fas fa-chevron-down"></i>
                            </button>
                            <div class="mobile-dropdown-content">
                                <a href="add_reminder.php">
                                    <i class="fas fa-bell mr-2"></i> Add Reminder
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php endif; ?>
                    
                    <?php if (is_admin()): ?>
                    <!-- Admin Links for Mobile -->
                    <a href="admin/index.php">
                        <i class="fas fa-cogs mr-2"></i> Admin Panel
                    </a>
                    <?php endif; ?>
                    
                    <a href="profile.php">
                        <i class="fas fa-user mr-2"></i> My Profile
                    </a>
                    <a href="settings.php">
                        <i class="fas fa-cog mr-2"></i> Settings
                    </a>
                    <a href="logout.php">
                        <i class="fas fa-sign-out-alt mr-2"></i> Logout
                    </a>
                <?php else: ?>
                    <a href="about.php">
                        <i class="fas fa-info-circle mr-2"></i> About
                    </a>
                    <a href="services.php">
                        <i class="fas fa-concierge-bell mr-2"></i> Services
                    </a>
                    <a href="contact.php">
                        <i class="fas fa-envelope mr-2"></i> Contact
                    </a>
                    <a href="login.php">
                        <i class="fas fa-sign-in-alt mr-2"></i> Login
                    </a>
                    <a href="register.php">
                        <i class="fas fa-user-plus mr-2"></i> Register
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    
    <!-- Spacer to prevent content from hiding under fixed navbar -->
    <div class="header-spacer"></div>

    <!-- Flash Messages -->
    <div class="container mt-4">
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="flash-message flash-success" role="alert">
                <p><?php echo $_SESSION['success_message']; ?></p>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="flash-message flash-error" role="alert">
                <p><?php echo $_SESSION['error_message']; ?></p>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>
    </div>
    <div class="container">
        <!-- Main content will be injected here -->
        <?php if (isset($content)) echo $content; ?>
  </body>
  </html>