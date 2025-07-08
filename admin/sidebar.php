<div class="sidebar-header">
    <h2>Pet Management</h2>
    <h3>Admin Panel</h3>
</div>

<nav class="sidebar-nav">
    <ul>
        <li class="<?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : ''; ?>">
            <a href="index.php">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li class="<?php echo basename($_SERVER['PHP_SELF']) === 'manage_vet_codes.php' ? 'active' : ''; ?>">
            <a href="manage_vet_codes.php">
                <i class="fas fa-key"></i>
                <span>Vet Codes</span>
            </a>
        </li>
        <li class="<?php echo basename($_SERVER['PHP_SELF']) === 'manage_users.php' ? 'active' : ''; ?>">
            <a href="manage_users.php">
                <i class="fas fa-users-cog"></i>
                <span>Users</span>
            </a>
        </li>
        <li class="<?php echo basename($_SERVER['PHP_SELF']) === 'system_settings.php' ? 'active' : ''; ?>">
            <a href="system_settings.php">
                <i class="fas fa-cogs"></i>
                <span>Settings</span>
            </a>
        </li>
       
    </ul>
</nav>

<div class="sidebar-footer">
    <p>Pet Management System</p>
    <p>Admin Panel v1.0</p>
</div>
