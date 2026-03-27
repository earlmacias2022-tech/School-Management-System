<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Management System - Attendance Tracking Made Simple</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="./assets/css/landingpage.css">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

</head>

<body>
    <!-- Header -->
    <header>
        <div class="header-container">
            <a href="#" class="logo">
                <div class="logo-icon">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <div class="logo-text">Edu<span>Track</span></div>
            </a>
            <nav>
                <ul class="nav-links">
                    <li><a href="#home">Home</a></li>
                    <li><a href="#features">Features</a></li>
                    <li><a href="#benefits">Benefits</a></li>
                    <li><a href="#contact">Contact</a></li>
                </ul>
                <a href="auth/login.php" class="login-btn">
                    <i class="fas fa-sign-in-alt"></i>
                    Login
                </a>
                <button class="mobile-menu-btn">
                    <i class="fas fa-bars"></i>
                </button>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero" id="home">
        <div class="hero-container">
            <div class="hero-content">
                <div class="hero-badge">
                    <i class="fas fa-star"></i>
                    #1 School Management Solution
                </div>
                <h1 class="hero-title">
                    Smart Attendance<br>
                    <span>Tracking System</span>
                </h1>
                <p class="hero-description">
                    Streamline your school's attendance management with our modern, intuitive platform. Track student presence, generate reports, and save valuable time.
                </p>
                <div class="hero-buttons">
                    <a href="auth/login.php" class="btn-primary">
                        <i class="fas fa-sign-in-alt"></i>
                        Login to System
                    </a>
                    <a href="#features" class="btn-secondary">
                        <i class="fas fa-info-circle"></i>
                        Learn More
                    </a>
                </div>
                <div class="hero-stats">
                    <div class="stat-item">
                        <div class="stat-number">10K+</div>
                        <div class="stat-label">Students Tracked</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">500+</div>
                        <div class="stat-label">Schools</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">99.9%</div>
                        <div class="stat-label">Accuracy</div>
                    </div>
                </div>
            </div>
            <div class="hero-visual">
                <div class="hero-card">
                    <div class="card-header">
                        <div class="card-title">Today's Attendance</div>
                        <div class="card-badge">Live</div>
                    </div>
                    <div class="attendance-table">
                        <div class="table-row">
                            <div class="table-header">Student</div>
                            <div class="table-header">Class</div>
                            <div class="table-header">Status</div>
                        </div>
                        <div class="table-row">
                            <div class="table-cell">Alex Johnson</div>
                            <div class="table-cell">Grade 10-A</div>
                            <div class="status-present">Present</div>
                        </div>
                        <div class="table-row">
                            <div class="table-cell">Sarah Miller</div>
                            <div class="table-cell">Grade 10-A</div>
                            <div class="status-present">Present</div>
                        </div>
                        <div class="table-row">
                            <div class="table-cell">James Wilson</div>
                            <div class="table-cell">Grade 10-B</div>
                            <div class="status-absent">Absent</div>
                        </div>
                        <div class="table-row">
                            <div class="table-cell">Emily Davis</div>
                            <div class="table-cell">Grade 10-A</div>
                            <div class="status-present">Present</div>
                        </div>
                        <div class="table-row">
                            <div class="table-cell">Michael Brown</div>
                            <div class="table-cell">Grade 10-B</div>
                            <div class="status-present">Present</div>
                        </div>
                    </div>
                </div>
                <div class="floating-element floating-element-1">
                    <div class="floating-icon blue">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="floating-text">500+ Students</div>
                </div>
                <div class="floating-element floating-element-2">
                    <div class="floating-icon green">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="floating-text">98% Attendance</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="features">
        <div class="section-header">
            <div class="section-badge">
                <i class="fas fa-rocket"></i>
                Powerful Features
            </div>
            <h2 class="section-title">Everything You Need</h2>
            <p class="section-description">
                Our system comes packed with features designed to make attendance management effortless.
            </p>
        </div>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon blue">
                    <i class="fas fa-fingerprint"></i>
                </div>
                <h3 class="feature-title">Biometric Integration</h3>
                <p class="feature-description">
                    Support for fingerprint and facial recognition attendance marking for accurate tracking and zero proxy attendance.
                </p>
            </div>
            <div class="feature-card">
                <div class="feature-icon green">
                    <i class="fas fa-chart-pie"></i>
                </div>
                <h3 class="feature-title">Advanced Analytics</h3>
                <p class="feature-description">
                    Generate comprehensive reports and visualizations to analyze attendance patterns and trends over time.
                </p>
            </div>
            <div class="feature-card">
                <div class="feature-icon cyan">
                    <i class="fas fa-bell"></i>
                </div>
                <h3 class="feature-title">Instant Notifications</h3>
                <p class="feature-description">
                    Automatic alerts to parents when their child is marked absent, keeping everyone informed in real-time.
                </p>
            </div>
            <div class="feature-card">
                <div class="feature-icon purple">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <h3 class="feature-title">Smart Scheduling</h3>
                <p class="feature-description">
                    Configure class schedules, exam periods, and holidays with our intuitive calendar management system.
                </p>
            </div>
            <div class="feature-card">
                <div class="feature-icon orange">
                    <i class="fas fa-mobile-alt"></i>
                </div>
                <h3 class="feature-title">Mobile Friendly</h3>
                <p class="feature-description">
                    Access the system from any device. Mark attendance on the go with our responsive mobile application.
                </p>
            </div>
            <div class="feature-card">
                <div class="feature-icon pink">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3 class="feature-title">Secure & Reliable</h3>
                <p class="feature-description">
                    Enterprise-grade security with role-based access control keeps your data safe and private.
                </p>
            </div>
        </div>
    </section>

    <!-- Benefits Section -->
    <section class="benefits" id="benefits">
        <div class="benefits-container">
            <div class="benefits-content">
                <div class="section-header" style="text-align: left; margin: 0 0 2rem 0;">
                    <div class="section-badge">Why Choose Us</div>
                    <h2 class="section-title">Transform Your School's Attendance</h2>
                    <p class="section-description">
                        Join hundreds of schools already benefiting from our modern approach to attendance management.
                    </p>
                </div>
                <div class="benefit-item">
                    <div class="benefit-icon-wrapper blue">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="benefit-text">
                        <h4>Save Precious Time</h4>
                        <p>Reduce attendance tracking time by up to 80% with automated workflows.</p>
                    </div>
                </div>
                <div class="benefit-item">
                    <div class="benefit-icon-wrapper green">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="benefit-text">
                        <h4>100% Accurate Records</h4>
                        <p>Eliminate manual errors and maintain precise attendance documentation.</p>
                    </div>
                </div>
                <div class="benefit-item">
                    <div class="benefit-icon-wrapper cyan">
                        <i class="fas fa-hand-holding-usd"></i>
                    </div>
                    <div class="benefit-text">
                        <h4>Cost Effective</h4>
                        <p>Reduce administrative costs while improving overall efficiency and productivity.</p>
                    </div>
                </div>
                <div class="benefit-item">
                    <div class="benefit-icon-wrapper purple">
                        <i class="fas fa-headset"></i>
                    </div>
                    <div class="benefit-text">
                        <h4>24/7 Support</h4>
                        <p>Our dedicated support team is always available to help you succeed.</p>
                    </div>
                </div>
            </div>
            <div class="benefits-visual">
                <div class="benefits-card">
                    <div class="benefits-card-header">
                        <div class="benefits-avatar">JD</div>
                        <div class="benefits-user-info">
                            <h4>John Davidson</h4>
                            <p>Principal, Lincoln High School</p>
                        </div>
                    </div>
                    <div class="chart-container">
                        <div class="chart-bar">
                            <div class="bar bar-1"></div>
                            <div class="bar-label">Mon</div>
                        </div>
                        <div class="chart-bar">
                            <div class="bar bar-2"></div>
                            <div class="bar-label">Tue</div>
                        </div>
                        <div class="chart-bar">
                            <div class="bar bar-3"></div>
                            <div class="bar-label">Wed</div>
                        </div>
                        <div class="chart-bar">
                            <div class="bar bar-4"></div>
                            <div class="bar-label">Thu</div>
                        </div>
                        <div class="chart-bar">
                            <div class="bar bar-5"></div>
                            <div class="bar-label">Fri</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta">
        <div class="cta-container">
            <h2 class="cta-title">Ready to Get Started?</h2>
            <p class="cta-description">
                Join thousands of schools already using our platform to streamline their attendance management.
            </p>
            <a href="auth/login.php" class="cta-button">
                <i class="fas fa-arrow-right"></i>
                Access the System
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer id="contact">
        <div class="footer-container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <a href="#" class="logo">
                        <div class="logo-icon">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <div class="logo-text">Edu<span>Track</span></div>
                    </a>
                    <p>A modern, comprehensive school management system designed to make attendance tracking simple and efficient.</p>
                    <div class="footer-social">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                <div class="footer-column">
                    <h4>Quick Links</h4>
                    <ul class="footer-links">
                        <li><a href="#home">Home</a></li>
                        <li><a href="#features">Features</a></li>
                        <li><a href="#benefits">Benefits</a></li>
                        <li><a href="#contact">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h4>Support</h4>
                    <ul class="footer-links">
                        <li><a href="#">Help Center</a></li>
                        <li><a href="#">Documentation</a></li>
                        <li><a href="#">FAQ</a></li>
                        <li><a href="#">Contact Us</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h4>Contact</h4>
                    <ul class="footer-links">
                        <li><a href="#"><i class="fas fa-envelope"></i> support@edutrack.com</a></li>
                        <li><a href="#"><i class="fas fa-phone"></i> +1 (555) 123-4567</a></li>
                        <li><a href="#"><i class="fas fa-map-marker-alt"></i> 123 Education St.</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2026 EduTrack. All rights reserved.</p>
                <div class="footer-bottom-links">
                    <a href="#">Privacy Policy</a>
                    <a href="#">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Mobile menu toggle
        const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
        const navLinks = document.querySelector('.nav-links');

        mobileMenuBtn.addEventListener('click', () => {
            navLinks.classList.toggle('active');
        });

        // Add mobile styles dynamically
        const style = document.createElement('style');
        style.textContent = `
            @media (max-width: 768px) {
                .nav-links {
                    display: none;
                    position: absolute;
                    top: 100%;
                    left: 0;
                    right: 0;
                    background: white;
                    flex-direction: column;
                    padding: 1rem 2rem;
                    box-shadow: var(--shadow);
                }
                .nav-links.active {
                    display: flex;
                }
                .nav-links li {
                    padding: 0.5rem 0;
                }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>

</html>