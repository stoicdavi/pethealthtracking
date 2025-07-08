<?php
// Set page title
$page_title = "Contact Us";

// Process contact form submission
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $message_text = $_POST['message'] ?? '';
    
    // Simple validation
    if (empty($name) || empty($email) || empty($subject) || empty($message_text)) {
        $error = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        // In a real application, you would send an email here
        // For now, we'll just show a success message
        $message = "Thank you for your message! We'll get back to you soon.";
        
        // Reset form fields after successful submission
        $name = $email = $subject = $message_text = '';
    }
}

// Include header
include 'includes/header.php';
?>

<div class="container" style="max-width: 800px; margin: 0 auto; padding: 2rem 0;">
    <div class="card" style="margin-bottom: 2rem;">
        <h1 style="font-size: 2rem; font-weight: bold; color: #1e40af; margin-bottom: 1.5rem; text-align: center;">Contact Us</h1>
        
        <?php if (!empty($message)): ?>
            <div style="background-color: #d1fae5; border-left: 4px solid #10b981; padding: 1rem; margin-bottom: 1.5rem; border-radius: 0.25rem;">
                <p style="margin: 0; color: #065f46;"><?php echo $message; ?></p>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div style="background-color: #fee2e2; border-left: 4px solid #ef4444; padding: 1rem; margin-bottom: 1.5rem; border-radius: 0.25rem;">
                <p style="margin: 0; color: #991b1b;"><?php echo $error; ?></p>
            </div>
        <?php endif; ?>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
            <div>
                <h2 style="font-size: 1.5rem; font-weight: bold; color: #1e40af; margin-bottom: 1rem;">Get in Touch</h2>
                <p style="margin-bottom: 1.5rem; line-height: 1.6;">
                    Have questions about our Pet Management System? We're here to help! 
                    Fill out the form and our team will get back to you as soon as possible.
                </p>
                
                <div style="margin-bottom: 1.5rem;">
                    <h3 style="font-weight: bold; margin-bottom: 0.5rem;">Contact Information</h3>
                    <ul style="list-style-type: none; padding: 0;">
                        <li style="margin-bottom: 0.75rem; display: flex; align-items: center;">
                            <i class="fas fa-map-marker-alt" style="color: #1e40af; margin-right: 0.75rem; width: 1rem; text-align: center;"></i>
                            <span>123 Pet Street, Veterinary District, 10001</span>
                        </li>
                        <li style="margin-bottom: 0.75rem; display: flex; align-items: center;">
                            <i class="fas fa-phone" style="color: #1e40af; margin-right: 0.75rem; width: 1rem; text-align: center;"></i>
                            <span>(123) 456-7890</span>
                        </li>
                        <li style="margin-bottom: 0.75rem; display: flex; align-items: center;">
                            <i class="fas fa-envelope" style="color: #1e40af; margin-right: 0.75rem; width: 1rem; text-align: center;"></i>
                            <span>info@petmanagementsystem.com</span>
                        </li>
                    </ul>
                </div>
                
                <div>
                    <h3 style="font-weight: bold; margin-bottom: 0.5rem;">Business Hours</h3>
                    <ul style="list-style-type: none; padding: 0;">
                        <li style="margin-bottom: 0.5rem; display: flex; justify-content: space-between;">
                            <span>Monday - Friday:</span>
                            <span>9:00 AM - 6:00 PM</span>
                        </li>
                        <li style="margin-bottom: 0.5rem; display: flex; justify-content: space-between;">
                            <span>Saturday:</span>
                            <span>10:00 AM - 4:00 PM</span>
                        </li>
                        <li style="display: flex; justify-content: space-between;">
                            <span>Sunday:</span>
                            <span>Closed</span>
                        </li>
                    </ul>
                </div>
                
                <div style="margin-top: 1.5rem;">
                    <h3 style="font-weight: bold; margin-bottom: 0.75rem;">Follow Us</h3>
                    <div style="display: flex; gap: 1rem;">
                        <a href="#" style="color: #1e40af; font-size: 1.5rem;"><i class="fab fa-facebook"></i></a>
                        <a href="#" style="color: #1e40af; font-size: 1.5rem;"><i class="fab fa-twitter"></i></a>
                        <a href="#" style="color: #1e40af; font-size: 1.5rem;"><i class="fab fa-instagram"></i></a>
                        <a href="#" style="color: #1e40af; font-size: 1.5rem;"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>
            </div>
            
            <div>
                <form method="post" action="contact.php">
                    <div style="margin-bottom: 1rem;">
                        <label for="name" style="display: block; font-weight: bold; margin-bottom: 0.5rem;">Your Name</label>
                        <input type="text" id="name" name="name" value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">
                    </div>
                    
                    <div style="margin-bottom: 1rem;">
                        <label for="email" style="display: block; font-weight: bold; margin-bottom: 0.5rem;">Email Address</label>
                        <input type="email" id="email" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">
                    </div>
                    
                    <div style="margin-bottom: 1rem;">
                        <label for="subject" style="display: block; font-weight: bold; margin-bottom: 0.5rem;">Subject</label>
                        <input type="text" id="subject" name="subject" value="<?php echo isset($subject) ? htmlspecialchars($subject) : ''; ?>" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">
                    </div>
                    
                    <div style="margin-bottom: 1.5rem;">
                        <label for="message" style="display: block; font-weight: bold; margin-bottom: 0.5rem;">Message</label>
                        <textarea id="message" name="message" rows="5" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem;"><?php echo isset($message_text) ? htmlspecialchars($message_text) : ''; ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.75rem;">Send Message</button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="card">
        <h2 style="font-size: 1.5rem; font-weight: bold; color: #1e40af; margin-bottom: 1rem;">Our Location</h2>
        <div style="width: 100%; height: 400px; background-color: #e5e7eb; border-radius: 0.5rem; overflow: hidden;">
            <!-- Replace with actual Google Maps embed code in a real application -->
             <!-- nairobi map-->
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3976.123456789012!2d36.821946315256!3d-1.2920662356789!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x182f10a123456789%3A0x123456789abcdef0!2sNairobi%20City%20Center%2C%20Nairobi%2C%20Kenya!5e0!3m2!1sen!2sus!4v1612345678901" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                
            
        </div>
    </div>
</div>

<?php
// Include footer
include 'includes/footer.php';
?>
