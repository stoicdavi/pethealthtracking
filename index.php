<?php
session_start();
require_once 'includes/db_connect.php';

// Set page title
$page_title = "Home";

// Include header
include 'includes/header.php';
?>

<!-- Hero Section -->
<div class="hero bg-paws">
    <div class="container">
        <div class="flex flex-col md:flex-row items-center">
            <div class="hero-content">
                <h1>Caring for Your Pets Made Simple</h1>
                <p>Keep track of your pet's health records, appointments, and vaccinations all in one place.</p>
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <div class="hero-buttons">
                        <a href="register.php" class="btn btn-light btn-lg">
                            <i class="fas fa-paw mr-2"></i> Get Started
                        </a>
                        <a href="login.php" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-sign-in-alt mr-2"></i> Login
                        </a>
                    </div>
                <?php else: ?>
                    <a href="dashboard.php" class="btn btn-light btn-lg">
                        <i class="fas fa-tachometer-alt mr-2"></i> Go to Dashboard
                    </a>
                <?php endif; ?>
            </div>
            <div class="hero-image">
                <img src="images/hero-pets.png" alt="Happy pets" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1450778869180-41d0601e046e?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80';">
            </div>
        </div>
    </div>
</div>

<!-- Features Section -->
<section class="features-section">
    <div class="container">
        <div class="section-header">
            <i class="fas fa-star"></i>
            <h2>Why Choose Our Pet Management System?</h2>
        </div>
        
        <div class="features-grid">
            <div class="pet-feature-card">
                <div class="pet-feature-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <h3 class="pet-feature-title">Easy Appointment Scheduling</h3>
                <p>Schedule veterinary appointments with just a few clicks. Receive reminders and never miss an important visit.</p>
            </div>
            
            <div class="pet-feature-card">
                <div class="pet-feature-icon">
                    <i class="fas fa-heartbeat"></i>
                </div>
                <h3 class="pet-feature-title">Health Record Management</h3>
                <p>Keep all your pet's health records in one place. Track vaccinations, medications, and treatments.</p>
            </div>
            
            <div class="pet-feature-card">
                <div class="pet-feature-icon">
                    <i class="fas fa-bell"></i>
                </div>
                <h3 class="pet-feature-title">Vaccination Reminders</h3>
                <p>Get timely reminders for upcoming vaccinations and preventive care to keep your pets healthy.</p>
            </div>
        </div>
    </div>
</section>

<!-- How It Works Section -->
<section class="how-it-works-section">
    <div class="container">
        <div class="section-header">
            <i class="fas fa-question-circle"></i>
            <h2>How It Works</h2>
        </div>
        
        <div class="steps-container">
            <div class="step-card">
                <div class="step-number">1</div>
                <div class="step-icon">
                    <i class="fas fa-user-plus"></i>
                </div>
                <h3>Create an Account</h3>
                <p>Sign up as a pet owner or veterinarian to access our platform's features.</p>
            </div>
            
            <div class="step-card">
                <div class="step-number">2</div>
                <div class="step-icon">
                    <i class="fas fa-paw"></i>
                </div>
                <h3>Add Your Pets</h3>
                <p>Create profiles for your pets with their details, photos, and medical history.</p>
            </div>
            
            <div class="step-card">
                <div class="step-number">3</div>
                <div class="step-icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <h3>Schedule Appointments</h3>
                <p>Book appointments with veterinarians and receive confirmation instantly.</p>
            </div>
            
            <div class="step-card">
                <div class="step-number">4</div>
                <div class="step-icon">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <h3>Manage Health Records</h3>
                <p>Keep track of vaccinations, treatments, and health status all in one place.</p>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="testimonials-section">
    <div class="container">
        <div class="section-header">
            <i class="fas fa-quote-left"></i>
            <h2>What Pet Owners Say</h2>
        </div>
        
        <div class="testimonials-grid">
            <div class="testimonial-card">
                <div class="testimonial-content">
                    <p>"This app has made managing my dog's healthcare so much easier! I never miss a vaccination now."</p>
                </div>
                <div class="testimonial-author">
                    <div class="testimonial-avatar">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <div class="testimonial-info">
                        <h4>Sarah Johnson</h4>
                        <p>Dog Owner</p>
                    </div>
                </div>
            </div>
            
            <div class="testimonial-card">
                <div class="testimonial-content">
                    <p>"As a veterinarian, this system helps me keep track of all my patients and their health histories efficiently."</p>
                </div>
                <div class="testimonial-author">
                    <div class="testimonial-avatar">
                        <i class="fas fa-user-md"></i>
                    </div>
                    <div class="testimonial-info">
                        <h4>Dr. Michael Chen</h4>
                        <p>Veterinarian</p>
                    </div>
                </div>
            </div>
            
            <div class="testimonial-card">
                <div class="testimonial-content">
                    <p>"I love how easy it is to schedule appointments and get reminders for my three cats!"</p>
                </div>
                <div class="testimonial-author">
                    <div class="testimonial-avatar">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <div class="testimonial-info">
                        <h4>Emily Rodriguez</h4>
                        <p>Cat Owner</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="cta-section">
    <div class="container">
        <div class="cta-content">
            <h2>Ready to Simplify Pet Care?</h2>
            <p>Join thousands of pet owners who are already using our platform to manage their pets' health.</p>
            <?php if (!isset($_SESSION['user_id'])): ?>
                <div class="cta-buttons">
                    <a href="register.php" class="btn btn-primary btn-lg">Sign Up Now</a>
                    <a href="about.php" class="btn btn-secondary btn-lg">Learn More</a>
                </div>
            <?php else: ?>
                <a href="dashboard.php" class="btn btn-primary btn-lg">Go to Dashboard</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Add CSS for the landing page -->
<style>
/* Hero Section */
.hero {
    background-color: var(--primary-color);
    color: white;
    padding: 5rem 0;
    position: relative;
    overflow: hidden;
    border-radius: 0 0 30% 0;
}

.hero::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100"><path fill="%23ffffff" opacity="0.1" d="M30,20c-5.5,0-10,4.5-10,10s4.5,10,10,10s10-4.5,10-10S35.5,20,30,20z M70,20c-5.5,0-10,4.5-10,10s4.5,10,10,10s10-4.5,10-10S75.5,20,70,20z M20,60c-5.5,0-10,4.5-10,10s4.5,10,10,10s10-4.5,10-10S25.5,60,20,60z M50,60c-5.5,0-10,4.5-10,10s4.5,10,10,10s10-4.5,10-10S55.5,60,50,60z M80,60c-5.5,0-10,4.5-10,10s4.5,10,10,10s10-4.5,10-10S85.5,60,80,60z"/></svg>');
    background-size: 150px;
    z-index: 0;
}

.hero .container {
    position: relative;
    z-index: 1;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.hero-content {
    max-width: 600px;
    margin-right: 2rem;
}

.hero h1 {
    font-size: 3rem;
    font-weight: 700;
    margin-bottom: 1.5rem;
    color: white;
    line-height: 1.2;
}

.hero p {
    font-size: 1.25rem;
    margin-bottom: 2rem;
    opacity: 0.9;
    line-height: 1.6;
}

.hero-buttons {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.btn-light {
    background-color: white;
    color: var(--primary-color);
}

.btn-light:hover {
    background-color: #f8fafc;
    color: var(--primary-dark);
}

.btn-outline-light {
    background-color: transparent;
    color: white;
    border: 2px solid white;
}

.btn-outline-light:hover {
    background-color: rgba(255, 255, 255, 0.1);
}

.hero-image {
    width: 100%;
    max-width: 500px;
    position: relative;
}

.hero-image img {
    width: 100%;
    height: auto;
    border-radius: 1rem;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2), 0 10px 10px -5px rgba(0, 0, 0, 0.1);
    border: 4px solid white;
    transform: rotate(2deg);
}

/* Features Section */
.features-section {
    padding: 5rem 0;
    background-color: var(--light-color);
}

.section-header {
    text-align: center;
    margin-bottom: 3rem;
}

.section-header i {
    font-size: 2rem;
    color: var(--accent-color);
    margin-bottom: 1rem;
    display: block;
}

.section-header h2 {
    font-size: 2.25rem;
    font-weight: 700;
    color: var(--dark-color);
}

.features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
}

/* How It Works Section */
.how-it-works-section {
    padding: 5rem 0;
    background-color: white;
}

.steps-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
    margin-top: 3rem;
}

.step-card {
    background-color: white;
    border-radius: 1rem;
    padding: 2rem;
    text-align: center;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    position: relative;
    transition: transform 0.3s ease;
}

.step-card:hover {
    transform: translateY(-10px);
}

.step-number {
    position: absolute;
    top: -15px;
    left: 50%;
    transform: translateX(-50%);
    background-color: var(--accent-color);
    color: white;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
}

.step-icon {
    font-size: 2.5rem;
    color: var(--primary-color);
    margin-bottom: 1.5rem;
}

.step-card h3 {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 1rem;
}

/* Testimonials Section */
.testimonials-section {
    padding: 5rem 0;
    background-color: var(--light-color);
}

.testimonials-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
}

.testimonial-card {
    background-color: white;
    border-radius: 1rem;
    padding: 2rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
}

.testimonial-content {
    margin-bottom: 1.5rem;
    position: relative;
}

.testimonial-content p {
    font-style: italic;
    color: var(--dark-color);
    line-height: 1.6;
}

.testimonial-author {
    display: flex;
    align-items: center;
}

.testimonial-avatar {
    width: 3rem;
    height: 3rem;
    border-radius: 50%;
    background-color: var(--light-color);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
    color: var(--primary-color);
    font-size: 1.5rem;
}

.testimonial-info h4 {
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.testimonial-info p {
    color: var(--secondary-color);
    font-size: 0.875rem;
}

/* Call to Action Section */
.cta-section {
    padding: 5rem 0;
    background-color: var(--primary-color);
    color: white;
    text-align: center;
    border-radius: 30% 0 0 0;
    position: relative;
    overflow: hidden;
}

.cta-section::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" viewBox="0 0 60 60"><path fill="%23ffffff" opacity="0.05" d="M30,26c-9.4,0-22.5,14.4-22.5,23.5c0,4.1,3.1,6.5,8.4,6.5c5.7,0,9.5-2.9,14.1-2.9c4.6,0,8.4,2.9,14.1,2.9c5.3,0,8.4-2.4,8.4-6.5C52.5,40.4,39.4,26,30,26z M14,3.5c-5.8,0-10.5,4.7-10.5,10.5S8.2,24.5,14,24.5s10.5-4.7,10.5-10.5S19.8,3.5,14,3.5z M24.5,30c0,5.8-4.7,10.5-10.5,10.5S3.5,35.8,3.5,30S8.2,19.5,14,19.5S24.5,24.2,24.5,30z M35.5,33.5c0,5.8-4.7,10.5-10.5,10.5S14.5,39.3,14.5,33.5S19.2,23,25,23S35.5,27.7,35.5,33.5z M46,19.5c0,5.8-4.7,10.5-10.5,10.5S25,25.3,25,19.5S29.7,9,35.5,9S46,13.7,46,19.5z M56.5,29c0,5.8-4.7,10.5-10.5,10.5S35.5,34.8,35.5,29s4.7-10.5,10.5-10.5S56.5,23.2,56.5,29z"/></svg>');
    background-repeat: repeat;
    z-index: 0;
}

.cta-content {
    position: relative;
    z-index: 1;
    max-width: 800px;
    margin: 0 auto;
}

.cta-section h2 {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 1rem;
    color: white;
}

.cta-section p {
    font-size: 1.25rem;
    margin-bottom: 2rem;
    opacity: 0.9;
}

.cta-buttons {
    display: flex;
    justify-content: center;
    gap: 1rem;
    flex-wrap: wrap;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .hero {
        padding: 4rem 0;
        text-align: center;
    }
    
    .hero .container {
        flex-direction: column;
    }
    
    .hero-content {
        margin-right: 0;
        margin-bottom: 2rem;
    }
    
    .hero h1 {
        font-size: 2.25rem;
    }
    
    .hero-buttons {
        justify-content: center;
    }
    
    .features-grid,
    .steps-container,
    .testimonials-grid {
        grid-template-columns: 1fr;
    }
    
    .section-header h2 {
        font-size: 1.75rem;
    }
    
    .cta-section h2 {
        font-size: 2rem;
    }
}
</style>

<?php
// Include footer
include 'includes/footer.php';
?>
