<?php
session_start();
require_once 'includes/db_connect.php';

// Check if user is logged in and is a pet owner
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'owner') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Check if appointment ID is provided
if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$appointment_id = intval($_GET['id']);

// Check if appointment exists and belongs to this owner
$check_query = "SELECT a.*, p.name as pet_name, p.id as pet_id 
               FROM appointments a 
               JOIN pets p ON a.pet_id = p.id 
               WHERE a.id = $appointment_id AND p.owner_id = $user_id AND a.status = 'pending'";
$check_result = $conn->query($check_query);

if ($check_result->num_rows == 0) {
    header("Location: dashboard.php");
    exit();
}

$appointment = $check_result->fetch_assoc();

// Get all pets owned by this user for the dropdown
$pets_query = "SELECT id, name FROM pets WHERE owner_id = $user_id";
$pets_result = $conn->query($pets_query);

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pet_id = intval($_POST['pet_id']);
    $appointment_date = $conn->real_escape_string($_POST['appointment_date']);
    $reason = $conn->real_escape_string($_POST['reason']);
    
    // Validate inputs
    if (empty($pet_id) || empty($appointment_date) || empty($reason)) {
        $error = "All fields are required.";
    } else {
        // Update appointment
        $update_query = "UPDATE appointments 
                        SET pet_id = $pet_id, 
                            appointment_date = '$appointment_date', 
                            reason = '$reason' 
                        WHERE id = $appointment_id";
        
        if ($conn->query($update_query) === TRUE) {
            $success = "Appointment updated successfully!";
            // Redirect to view appointment page after 2 seconds
            header("refresh:2;url=view_appointment.php?id=$appointment_id");
        } else {
            $error = "Error: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Appointment - Pet Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body class="bg-gray-100 min-h-screen">
    <nav class="bg-blue-600 text-white p-4 shadow-md">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-2xl font-bold">Pet Management System</h1>
            <div>
                <a href="dashboard.php" class="px-4 py-2 mr-2">Dashboard</a>
                <a href="logout.php" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded">Logout</a>
            </div>
        </div>
    </nav>

    <main class="container mx-auto p-4 mt-8">
        <div class="max-w-md mx-auto bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-2xl font-bold mb-6 text-center text-blue-600">Edit Appointment</h2>
            
            <?php if (!empty($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <form action="edit_appointment.php?id=<?php echo $appointment_id; ?>" method="post" class="space-y-4">
                <div>
                    <label for="pet_id" class="block text-gray-700 mb-1">Select Pet</label>
                    <select id="pet_id" name="pet_id" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <?php while ($pet = $pets_result->fetch_assoc()): ?>
                            <option value="<?php echo $pet['id']; ?>" <?php if ($pet['id'] == $appointment['pet_id']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($pet['name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div>
                    <label for="appointment_date" class="block text-gray-700 mb-1">Appointment Date & Time</label>
                    <input type="datetime-local" id="appointment_date" name="appointment_date" 
                           value="<?php echo date('Y-m-d\TH:i', strtotime($appointment['appointment_date'])); ?>" 
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                    <label for="reason" class="block text-gray-700 mb-1">Reason for Appointment</label>
                    <textarea id="reason" name="reason" rows="4" placeholder="Describe the reason for this appointment" 
                              class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo htmlspecialchars($appointment['reason']); ?></textarea>
                </div>
                
                <div class="flex space-x-4">
                    <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        Update Appointment
                    </button>
                    <a href="view_appointment.php?id=<?php echo $appointment_id; ?>" class="flex-1 bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg text-center">
                        Cancel
                    </a>
                </div>
            </form>
            
            <div class="mt-4 text-center">
                <a href="dashboard.php" class="text-blue-600 hover:underline">Back to Dashboard</a>
            </div>
        </div>
    </main>

    <footer class="bg-blue-600 text-white p-4 mt-8">
        <div class="container mx-auto text-center">
            <p>&copy; <?php echo date('Y'); ?> Pet Management System</p>
        </div>
    </footer>
</body>
</html>
