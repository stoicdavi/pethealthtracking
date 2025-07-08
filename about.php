<?php
// Set page title
$page_title = "About Us";

// Include header
include 'includes/header.php';
?>

<div class="container" style="max-width: 800px; margin: 0 auto; padding: 2rem 0;">
    <div class="card" style="margin-bottom: 2rem;">
        <h1 style="font-size: 2rem; font-weight: bold; color: #1e40af; margin-bottom: 1.5rem; text-align: center;">About Pet Management System</h1>
        
        <div style="margin-bottom: 2rem;">
            <img src="images/about-banner.jpg" alt="Pet Care" style="width: 100%; border-radius: 0.5rem; margin-bottom: 1.5rem;" onerror="this.src='https://via.placeholder.com/800x400?text=Pet+Management+System'">
            
            <h2 style="font-size: 1.5rem; font-weight: bold; color: #1e40af; margin-bottom: 1rem;">Our Mission</h2>
            <p style="margin-bottom: 1rem; line-height: 1.6;">
                At Pet Management System, our mission is to improve the health and wellbeing of pets through better organization, 
                communication, and management of their healthcare needs. We believe that by connecting pet owners and veterinarians 
                through a streamlined digital platform, we can ensure that pets receive the best possible care.
            </p>
            
            <h2 style="font-size: 1.5rem; font-weight: bold; color: #1e40af; margin: 1.5rem 0 1rem;">Our Story</h2>
            <p style="margin-bottom: 1rem; line-height: 1.6;">
                Pet Management System was developed by <strong>Hodhan Abdi</strong> in 2023. As an animal lover and technology enthusiast, Hodhan recognized 
                the need for a better way to manage pet healthcare information. After experiencing the challenges of keeping track 
                of vaccination records, medication schedules, and veterinary appointments for pets, she created 
                a solution that would make pet healthcare management easier for everyone.
            </p>
            <p style="margin-bottom: 1rem; line-height: 1.6;">
                What started as a simple idea has grown into a comprehensive platform that serves thousands of pet owners and 
                veterinarians, helping them collaborate effectively to provide the best care for their animal companions.
            </p>
            
            <h2 style="font-size: 1.5rem; font-weight: bold; color: #1e40af; margin: 1.5rem 0 1rem;">Our Team</h2>
            <p style="margin-bottom: 1rem; line-height: 1.6;">
                Our team is led by Hodhan Abdi, the developer of the Pet Management System. We consist of passionate pet owners, 
                experienced veterinarians, and skilled developers who work together to create a platform that addresses the real needs 
                of both pet owners and veterinary professionals. We're committed to continuously improving our system based on user 
                feedback and the latest developments in veterinary medicine.
            </p>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1.5rem; margin-top: 2rem;">
                <div style="text-align: center;">
                    <div style="width: 150px; height: 150px; border-radius: 50%; background-color: #e5e7eb; margin: 0 auto 1rem; overflow: hidden;">
                        <img src="" alt="Team Member" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.src='https://via.placeholder.com/150?text=Team+Member'">
                    </div>
                    <h3 style="font-weight: bold; margin-bottom: 0.25rem;">Dr. Sarah Johnson</h3>
                    <p style="color: #6b7280; font-size: 0.875rem;">Chief Veterinary Officer</p>
                </div>
                
                <div style="text-align: center;">
                    <div style="width: 150px; height: 150px; border-radius: 50%; background-color: #e5e7eb; margin: 0 auto 1rem; overflow: hidden;">
                        <img src="" alt="Team Member" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.src='https://via.placeholder.com/150?text=Team+Member'">
                    </div>
                    <h3 style="font-weight: bold; margin-bottom: 0.25rem;">Hodhan Abdi</h3>
                    <p style="color: #6b7280; font-size: 0.875rem;">Lead Developer</p>
                </div>
                
                <div style="text-align: center;">
                    <div style="width: 150px; height: 150px; border-radius: 50%; background-color: #e5e7eb; margin: 0 auto 1rem; overflow: hidden;">
                        <img src="" alt="Team Member" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.src='https://via.placeholder.com/150?text=Team+Member'">
                    </div>
                    <h3 style="font-weight: bold; margin-bottom: 0.25rem;">Emily Rodriguez</h3>
                    <p style="color: #6b7280; font-size: 0.875rem;">Customer Success Manager</p>
                </div>
            </div>
        </div>
        
        <h2 style="font-size: 1.5rem; font-weight: bold; color: #1e40af; margin: 1.5rem 0 1rem;">Our Values</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem;">
            <div style="border: 1px solid #e5e7eb; border-radius: 0.5rem; padding: 1.5rem;">
                <div style="font-size: 2rem; color: #1e40af; margin-bottom: 1rem;">
                    <i class="fas fa-heart"></i>
                </div>
                <h3 style="font-weight: bold; margin-bottom: 0.5rem;">Pet-Centered Care</h3>
                <p style="color: #4b5563; line-height: 1.6;">We put the health and wellbeing of pets at the center of everything we do.</p>
            </div>
            
            <div style="border: 1px solid #e5e7eb; border-radius: 0.5rem; padding: 1.5rem;">
                <div style="font-size: 2rem; color: #1e40af; margin-bottom: 1rem;">
                    <i class="fas fa-lock"></i>
                </div>
                <h3 style="font-weight: bold; margin-bottom: 0.5rem;">Privacy & Security</h3>
                <p style="color: #4b5563; line-height: 1.6;">We're committed to protecting the privacy and security of all user data.</p>
            </div>
            
            <div style="border: 1px solid #e5e7eb; border-radius: 0.5rem; padding: 1.5rem;">
                <div style="font-size: 2rem; color: #1e40af; margin-bottom: 1rem;">
                    <i class="fas fa-users"></i>
                </div>
                <h3 style="font-weight: bold; margin-bottom: 0.5rem;">Community</h3>
                <p style="color: #4b5563; line-height: 1.6;">We believe in building a supportive community of pet owners and veterinarians.</p>
            </div>
            
            <div style="border: 1px solid #e5e7eb; border-radius: 0.5rem; padding: 1.5rem;">
                <div style="font-size: 2rem; color: #1e40af; margin-bottom: 1rem;">
                    <i class="fas fa-lightbulb"></i>
                </div>
                <h3 style="font-weight: bold; margin-bottom: 0.5rem;">Innovation</h3>
                <p style="color: #4b5563; line-height: 1.6;">We continuously innovate to provide the best tools for pet healthcare management.</p>
            </div>
        </div>
    </div>
    
    <div style="text-align: center; margin-top: 2rem;">
        <a href="contact.php" class="btn btn-primary" style="padding: 0.75rem 1.5rem; font-size: 1.125rem;">Get in Touch</a>
    </div>
</div>

<?php
// Include footer
include 'includes/footer.php';
?>
