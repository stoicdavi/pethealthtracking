<?php
session_start();
require_once 'includes/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];
$error = '';

// Check if pet ID is provided
if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$pet_id = intval($_GET['id']);

// Get pet details based on user type
if ($user_type == 'owner') {
    // For pet owners, check if they own this pet
    $pet_query = "SELECT p.*, u.username as owner_name 
                 FROM pets p 
                 JOIN users u ON p.owner_id = u.id 
                 WHERE p.id = $pet_id AND p.owner_id = $user_id";
} else if ($user_type == 'veterinary') {
    // For vets, allow access to any pet
    $pet_query = "SELECT p.*, u.username as owner_name 
                 FROM pets p 
                 JOIN users u ON p.owner_id = u.id 
                 WHERE p.id = $pet_id";
} else {
    header("Location: dashboard.php");
    exit();
}

$pet_result = $conn->query($pet_query);

if ($pet_result->num_rows == 0) {
    header("Location: dashboard.php");
    exit();
}

$pet = $pet_result->fetch_assoc();

// Get health records for this pet
$records_query = "SELECT hr.*, u.username as vet_name 
                 FROM health_records hr 
                 JOIN users u ON hr.vet_id = u.id 
                 WHERE hr.pet_id = $pet_id 
                 ORDER BY hr.record_date DESC";
$records_result = $conn->query($records_query);

// Get appointment history for this pet
$appointments_query = "SELECT a.*, u.username as vet_name 
                      FROM appointments a 
                      LEFT JOIN users u ON a.vet_id = u.id 
                      WHERE a.pet_id = $pet_id 
                      ORDER BY a.appointment_date DESC";
$appointments_result = $conn->query($appointments_query);

// Get vaccination reminders for this pet
$reminders_query = "SELECT * FROM reminders 
                   WHERE pet_id = $pet_id 
                   ORDER BY reminder_date DESC";
$reminders_result = $conn->query($reminders_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pet Health History - Pet Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body class="bg-gray-100 min-h-screen">

  <?php include 'includes/header.php'; ?>
    <main class="container mx-auto p-4 mt-8">
        <div class="max-w-4xl mx-auto">
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-2xl font-bold text-blue-600">Pet Health History</h2>
                    <?php if ($user_type == 'veterinary'): ?>
                        <a href="add_health_record.php?pet_id=<?php echo $pet_id; ?>" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg">
                            Add Health Record
                        </a>
                    <?php endif; ?>
                </div>
                
                <div class="mb-6 p-4 bg-blue-50 rounded-lg">
                    <h3 class="font-bold text-lg mb-2">Pet Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p><strong>Name:</strong> <?php echo htmlspecialchars($pet['name']); ?></p>
                            <p><strong>Species:</strong> <?php echo htmlspecialchars($pet['species']); ?></p>
                            <p><strong>Breed:</strong> <?php echo htmlspecialchars($pet['breed'] ?? 'Not specified'); ?></p>
                        </div>
                        <div>
                            <p><strong>Gender:</strong> <?php echo ucfirst(htmlspecialchars($pet['gender'])); ?></p>
                            <p><strong>Birth Date:</strong> <?php echo $pet['date_of_birth'] ? date('M d, Y', strtotime($pet['date_of_birth'])) : 'Not specified'; ?></p>
                            <p><strong>Weight:</strong> <?php echo $pet['weight'] ? htmlspecialchars($pet['weight']) . ' kg' : 'Not specified'; ?></p>
                        </div>
                    </div>
                    <p class="mt-2"><strong>Owner:</strong> <?php echo htmlspecialchars($pet['owner_name']); ?></p>
                </div>
                
                <!-- Tabs for different sections -->
                <div class="mb-6">
                    <div class="border-b border-gray-200">
                        <nav class="-mb-px flex" aria-label="Tabs">
                            <button class="tab-button active-tab border-blue-500 text-blue-600 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm" data-tab="health-records">
                                Health Records
                            </button>
                            <button class="tab-button text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 border-transparent font-medium text-sm ml-8" data-tab="appointments">
                                Appointment History
                            </button>
                            <button class="tab-button text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 border-transparent font-medium text-sm ml-8" data-tab="vaccinations">
                                Vaccination Records
                            </button>
                        </nav>
                    </div>
                </div>
                
                <!-- Health Records Tab -->
                <div id="health-records" class="tab-content">
                    <?php if ($records_result->num_rows > 0): ?>
                        <div class="space-y-6">
                            <?php while ($record = $records_result->fetch_assoc()): ?>
                                <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50">
                                    <div class="flex justify-between items-start">
                                        <h4 class="font-bold text-lg"><?php echo htmlspecialchars($record['diagnosis']); ?></h4>
                                        <div class="text-sm text-gray-500">
                                            <?php echo date('M d, Y', strtotime($record['record_date'])); ?>
                                        </div>
                                    </div>
                                    <p class="text-sm text-gray-500 mb-2">Veterinarian: <?php echo htmlspecialchars($record['vet_name']); ?></p>
                                    
                                    <?php if (!empty($record['treatment'])): ?>
                                        <div class="mt-3">
                                            <h5 class="font-semibold text-blue-600">Treatment</h5>
                                            <p><?php echo nl2br(htmlspecialchars($record['treatment'])); ?></p>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($record['medications'])): ?>
                                        <div class="mt-3">
                                            <h5 class="font-semibold text-blue-600">Medications</h5>
                                            <p><?php echo nl2br(htmlspecialchars($record['medications'])); ?></p>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($record['notes'])): ?>
                                        <div class="mt-3">
                                            <h5 class="font-semibold text-blue-600">Notes</h5>
                                            <p><?php echo nl2br(htmlspecialchars($record['notes'])); ?></p>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($record['follow_up_date'])): ?>
                                        <div class="mt-3">
                                            <h5 class="font-semibold text-blue-600">Follow-up Date</h5>
                                            <p><?php echo date('M d, Y', strtotime($record['follow_up_date'])); ?></p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-8">
                            <p class="text-gray-500">No health records found for this pet.</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Appointments Tab -->
                <div id="appointments" class="tab-content hidden">
                    <?php if ($appointments_result->num_rows > 0): ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white">
                                <thead>
                                    <tr>
                                        <th class="py-2 px-4 border-b text-left">Date</th>
                                        <th class="py-2 px-4 border-b text-left">Reason</th>
                                        <th class="py-2 px-4 border-b text-left">Status</th>
                                        <th class="py-2 px-4 border-b text-left">Veterinarian</th>
                                        <th class="py-2 px-4 border-b text-left">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($appointment = $appointments_result->fetch_assoc()): ?>
                                        <tr>
                                            <td class="py-2 px-4 border-b"><?php echo date('M d, Y H:i', strtotime($appointment['appointment_date'])); ?></td>
                                            <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($appointment['reason']); ?></td>
                                            <td class="py-2 px-4 border-b">
                                                <?php 
                                                $status_color = '';
                                                switch($appointment['status']) {
                                                    case 'pending': $status_color = 'bg-yellow-100 text-yellow-800'; break;
                                                    case 'approved': $status_color = 'bg-blue-100 text-blue-800'; break;
                                                    case 'completed': $status_color = 'bg-green-100 text-green-800'; break;
                                                    case 'cancelled': $status_color = 'bg-red-100 text-red-800'; break;
                                                }
                                                ?>
                                                <span class="px-2 py-1 rounded-full text-xs <?php echo $status_color; ?>">
                                                    <?php echo ucfirst($appointment['status']); ?>
                                                </span>
                                            </td>
                                            <td class="py-2 px-4 border-b"><?php echo $appointment['vet_name'] ? htmlspecialchars($appointment['vet_name']) : 'Not assigned'; ?></td>
                                            <td class="py-2 px-4 border-b">
                                                <a href="view_appointment.php?id=<?php echo $appointment['id']; ?>" class="text-xs bg-blue-500 hover:bg-blue-600 text-white px-2 py-1 rounded">View Details</a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-8">
                            <p class="text-gray-500">No appointment history found for this pet.</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Vaccinations Tab -->
                <div id="vaccinations" class="tab-content hidden">
                    <?php if ($reminders_result->num_rows > 0): ?>
                        <div class="space-y-4">
                            <?php while ($reminder = $reminders_result->fetch_assoc()): ?>
                                <div class="border-l-4 <?php echo $reminder['is_completed'] ? 'border-green-500 bg-green-50' : 'border-yellow-500 bg-yellow-50'; ?> p-4">
                                    <div class="flex justify-between">
                                        <h4 class="font-bold"><?php echo htmlspecialchars($reminder['title']); ?></h4>
                                        <span class="text-sm"><?php echo date('M d, Y', strtotime($reminder['reminder_date'])); ?></span>
                                    </div>
                                    <?php if ($reminder['description']): ?>
                                        <p class="mt-1"><?php echo htmlspecialchars($reminder['description']); ?></p>
                                    <?php endif; ?>
                                    <p class="text-sm mt-1">
                                        Status: 
                                        <span class="font-medium <?php echo $reminder['is_completed'] ? 'text-green-600' : 'text-yellow-600'; ?>">
                                            <?php echo $reminder['is_completed'] ? 'Completed' : 'Pending'; ?>
                                        </span>
                                    </p>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-8">
                            <p class="text-gray-500">No vaccination records found for this pet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="text-center">
                <a href="dashboard.php" class="text-blue-600 hover:underline">Back to Dashboard</a>
            </div>
        </div>
    </main>

    <footer class="bg-blue-600 text-white p-4 mt-8">
        <div class="container mx-auto text-center">
            <p>&copy; <?php echo date('Y'); ?> Pet Management System</p>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tabButtons = document.querySelectorAll('.tab-button');
            const tabContents = document.querySelectorAll('.tab-content');
            
            tabButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Remove active class from all buttons
                    tabButtons.forEach(btn => {
                        btn.classList.remove('active-tab', 'border-blue-500', 'text-blue-600');
                        btn.classList.add('text-gray-500', 'border-transparent');
                    });
                    
                    // Add active class to clicked button
                    this.classList.add('active-tab', 'border-blue-500', 'text-blue-600');
                    this.classList.remove('text-gray-500', 'border-transparent');
                    
                    // Hide all tab contents
                    tabContents.forEach(content => {
                        content.classList.add('hidden');
                    });
                    
                    // Show the selected tab content
                    const tabId = this.getAttribute('data-tab');
                    document.getElementById(tabId).classList.remove('hidden');
                });
            });
        });
    </script>
</body>
</html>
