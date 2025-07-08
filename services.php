<?php
// Set page title
$page_title = "Our Services";

// Include header
include 'includes/header.php';
?>

<div class="container" style="max-width: 800px; margin: 0 auto; padding: 2rem 0;">
    <div class="card" style="margin-bottom: 2rem;">
        <h1 style="font-size: 2rem; font-weight: bold; color: #1e40af; margin-bottom: 1.5rem; text-align: center;">Our Services</h1>
        
        <div style="margin-bottom: 2rem;">
            <img src="images/services-banner.jpg" alt="Pet Services" style="width: 100%; border-radius: 0.5rem; margin-bottom: 1.5rem;" onerror="this.src='https://via.placeholder.com/800x400?text=Pet+Services'">
            
            <p style="margin-bottom: 2rem; line-height: 1.6; text-align: center; font-size: 1.125rem;">
                Pet Management System offers a comprehensive suite of tools and services designed to streamline 
                pet healthcare management for both pet owners and veterinarians.
            </p>
        </div>
        
        <!-- For Pet Owners Section -->
        <div style="margin-bottom: 3rem;">
            <h2 style="font-size: 1.5rem; font-weight: bold; color: #1e40af; margin-bottom: 1.5rem; padding-bottom: 0.5rem; border-bottom: 2px solid #e5e7eb;">
                <i class="fas fa-user mr-2"></i> For Pet Owners
            </h2>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 1.5rem;">
                <div style="border: 1px solid #e5e7eb; border-radius: 0.5rem; padding: 1.5rem; transition: transform 0.3s ease, box-shadow 0.3s ease;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 10px 15px -3px rgba(0, 0, 0, 0.1)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                    <div style="font-size: 2.5rem; color: #1e40af; margin-bottom: 1rem; text-align: center;">
                        <i class="fas fa-paw"></i>
                    </div>
                    <h3 style="font-weight: bold; margin-bottom: 0.75rem; text-align: center;">Pet Profile Management</h3>
                    <p style="color: #4b5563; line-height: 1.6;">
                        Create and manage detailed profiles for all your pets, including photos, breed information, 
                        weight tracking, and special notes. Keep all your pet's important information in one place.
                    </p>
                </div>
                
                <div style="border: 1px solid #e5e7eb; border-radius: 0.5rem; padding: 1.5rem; transition: transform 0.3s ease, box-shadow 0.3s ease;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 10px 15px -3px rgba(0, 0, 0, 0.1)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                    <div style="font-size: 2.5rem; color: #1e40af; margin-bottom: 1rem; text-align: center;">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <h3 style="font-weight: bold; margin-bottom: 0.75rem; text-align: center;">Appointment Scheduling</h3>
                    <p style="color: #4b5563; line-height: 1.6;">
                        Easily schedule veterinary appointments for your pets. Choose your preferred date and time, 
                        specify the reason for the visit, and receive confirmation from your veterinarian.
                    </p>
                </div>
                
                <div style="border: 1px solid #e5e7eb; border-radius: 0.5rem; padding: 1.5rem; transition: transform 0.3s ease, box-shadow 0.3s ease;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 10px 15px -3px rgba(0, 0, 0, 0.1)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                    <div style="font-size: 2.5rem; color: #1e40af; margin-bottom: 1rem; text-align: center;">
                        <i class="fas fa-bell"></i>
                    </div>
                    <h3 style="font-weight: bold; margin-bottom: 0.75rem; text-align: center;">Vaccination Reminders</h3>
                    <p style="color: #4b5563; line-height: 1.6;">
                        Never miss an important vaccination again. Set up customized reminders for each pet's 
                        vaccinations and receive notifications when they're due.
                    </p>
                </div>
                
                <div style="border: 1px solid #e5e7eb; border-radius: 0.5rem; padding: 1.5rem; transition: transform 0.3s ease, box-shadow 0.3s ease;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 10px 15px -3px rgba(0, 0, 0, 0.1)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                    <div style="font-size: 2.5rem; color: #1e40af; margin-bottom: 1rem; text-align: center;">
                        <i class="fas fa-pills"></i>
                    </div>
                    <h3 style="font-weight: bold; margin-bottom: 0.75rem; text-align: center;">Medication Tracking</h3>
                    <p style="color: #4b5563; line-height: 1.6;">
                        Keep track of your pet's medications, including dosage information, frequency, and duration. 
                        Ensure your pet receives the right medication at the right time.
                    </p>
                </div>
            </div>
        </div>
        
        <!-- For Veterinarians Section -->
        <div style="margin-bottom: 3rem;">
            <h2 style="font-size: 1.5rem; font-weight: bold; color: #1e40af; margin-bottom: 1.5rem; padding-bottom: 0.5rem; border-bottom: 2px solid #e5e7eb;">
                <i class="fas fa-user-md mr-2"></i> For Veterinarians
            </h2>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 1.5rem;">
                <div style="border: 1px solid #e5e7eb; border-radius: 0.5rem; padding: 1.5rem; transition: transform 0.3s ease, box-shadow 0.3s ease;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 10px 15px -3px rgba(0, 0, 0, 0.1)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                    <div style="font-size: 2.5rem; color: #1e40af; margin-bottom: 1rem; text-align: center;">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <h3 style="font-weight: bold; margin-bottom: 0.75rem; text-align: center;">Patient Management</h3>
                    <p style="color: #4b5563; line-height: 1.6;">
                        Access comprehensive patient records, including medical history, vaccination records, 
                        and owner information. Easily manage your patient database and provide better care.
                    </p>
                </div>
                
                <div style="border: 1px solid #e5e7eb; border-radius: 0.5rem; padding: 1.5rem; transition: transform 0.3s ease, box-shadow 0.3s ease;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 10px 15px -3px rgba(0, 0, 0, 0.1)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                    <div style="font-size: 2.5rem; color: #1e40af; margin-bottom: 1rem; text-align: center;">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <h3 style="font-weight: bold; margin-bottom: 0.75rem; text-align: center;">Appointment Management</h3>
                    <p style="color: #4b5563; line-height: 1.6;">
                        View and manage appointment requests from pet owners. Approve, reschedule, or mark 
                        appointments as completed. Keep track of your daily schedule.
                    </p>
                </div>
                
                <div style="border: 1px solid #e5e7eb; border-radius: 0.5rem; padding: 1.5rem; transition: transform 0.3s ease, box-shadow 0.3s ease;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 10px 15px -3px rgba(0, 0, 0, 0.1)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                    <div style="font-size: 2.5rem; color: #1e40af; margin-bottom: 1rem; text-align: center;">
                        <i class="fas fa-notes-medical"></i>
                    </div>
                    <h3 style="font-weight: bold; margin-bottom: 0.75rem; text-align: center;">Health Record Management</h3>
                    <p style="color: #4b5563; line-height: 1.6;">
                        Create and manage detailed health records for your patients. Document diagnoses, 
                        treatments, and follow-up recommendations in a structured format.
                    </p>
                </div>
                
                <div style="border: 1px solid #e5e7eb; border-radius: 0.5rem; padding: 1.5rem; transition: transform 0.3s ease, box-shadow 0.3s ease;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 10px 15px -3px rgba(0, 0, 0, 0.1)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                    <div style="font-size: 2.5rem; color: #1e40af; margin-bottom: 1rem; text-align: center;">
                        <i class="fas fa-comment-medical"></i>
                    </div>
                    <h3 style="font-weight: bold; margin-bottom: 0.75rem; text-align: center;">Owner Communication</h3>
                    <p style="color: #4b5563; line-height: 1.6;">
                        Communicate directly with pet owners through our secure messaging system. Share 
                        important information and answer questions efficiently.
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Premium Features Section -->
        <div>
            <h2 style="font-size: 1.5rem; font-weight: bold; color: #1e40af; margin-bottom: 1.5rem; padding-bottom: 0.5rem; border-bottom: 2px solid #e5e7eb;">
                <i class="fas fa-star mr-2"></i> Premium Features
            </h2>
            
            <div style="background-color: #f3f4f6; border-radius: 0.5rem; padding: 1.5rem; margin-bottom: 1.5rem;">
                <h3 style="font-weight: bold; margin-bottom: 1rem; color: #1e40af;">Coming Soon</h3>
                <ul style="list-style-type: none; padding: 0;">
                    <li style="margin-bottom: 0.75rem; display: flex; align-items: center;">
                        <i class="fas fa-check-circle" style="color: #10b981; margin-right: 0.5rem;"></i>
                        <span>Advanced analytics and reporting for veterinary practices</span>
                    </li>
                    <li style="margin-bottom: 0.75rem; display: flex; align-items: center;">
                        <i class="fas fa-check-circle" style="color: #10b981; margin-right: 0.5rem;"></i>
                        <span>Integrated telehealth consultations</span>
                    </li>
                    <li style="margin-bottom: 0.75rem; display: flex; align-items: center;">
                        <i class="fas fa-check-circle" style="color: #10b981; margin-right: 0.5rem;"></i>
                        <span>Mobile app for iOS and Android</span>
                    </li>
                    <li style="margin-bottom: 0.75rem; display: flex; align-items: center;">
                        <i class="fas fa-check-circle" style="color: #10b981; margin-right: 0.5rem;"></i>
                        <span>Integration with popular practice management software</span>
                    </li>
                    <li style="display: flex; align-items: center;">
                        <i class="fas fa-check-circle" style="color: #10b981; margin-right: 0.5rem;"></i>
                        <span>Pet community forums and resources</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    
    <div style="text-align: center; margin-top: 2rem;">
        <a href="register.php" class="btn btn-primary" style="padding: 0.75rem 1.5rem; font-size: 1.125rem; margin-right: 1rem;">Sign Up Now</a>
        <a href="contact.php" class="btn btn-secondary" style="padding: 0.75rem 1.5rem; font-size: 1.125rem;">Contact Us</a>
    </div>
</div>

<?php
// Include footer
include 'includes/footer.php';
?>
