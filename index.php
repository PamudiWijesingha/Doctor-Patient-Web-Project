<?php
$pageTitle = 'Welcome';
require_once __DIR__ . '/config/session.php';

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
    redirectToDashboard();
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="hero">
    <div class="hero-content">
        <div class="hero-badge">
            <i class="fas fa-shield-alt"></i> Trusted Healthcare Partner
        </div>
        <h1>Your Health, Our Priority</h1>
        <p>Welcome to MediCare Medical Center — where expert doctors meet compassionate care. Book appointments with top specialists, browse doctor profiles, and manage your health journey all in one place.</p>
        <div class="hero-buttons">
            <a href="/PamudiNew/auth/signup.php" class="btn btn-primary btn-lg">
                <i class="fas fa-user-plus"></i> Get Started — Sign Up
            </a>
            <a href="/PamudiNew/auth/login.php" class="btn btn-outline btn-lg">
                <i class="fas fa-sign-in-alt"></i> Login
            </a>
        </div>
    </div>
</section>

<section class="features-section">
    <h2>Why Choose MediCare?</h2>
    <p class="section-subtitle">We provide world-class medical services with a personal touch</p>
    <div class="features-grid">
        <div class="feature-card">
            <div class="feature-icon">
                <i class="fas fa-user-md"></i>
            </div>
            <h3>Expert Doctors</h3>
            <p>Browse our team of highly qualified specialists across multiple medical fields with verified credentials.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon" style="background: rgba(6, 182, 212, 0.15); color: var(--accent-light);">
                <i class="fas fa-calendar-check"></i>
            </div>
            <h3>Easy Appointments</h3>
            <p>Book appointments online in just a few clicks. Choose your preferred doctor, date, and time slot.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon" style="background: rgba(16, 185, 129, 0.15); color: var(--success);">
                <i class="fas fa-star"></i>
            </div>
            <h3>Ratings & Reviews</h3>
            <p>View doctor ratings and qualifications to make informed decisions about your healthcare provider.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon" style="background: rgba(139, 92, 246, 0.15); color: #a78bfa;">
                <i class="fas fa-lock"></i>
            </div>
            <h3>Secure & Private</h3>
            <p>Your health data is protected with industry-standard security. Your privacy is our commitment.</p>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
