<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Breast Cancer Prediction</title>
    <link rel="stylesheet" href="css/home.css">
    <link rel="stylesheet" href="css/userNavbar.css">
</head>
<body>

    <nav class="navbar" id="home-navbar">
        <div class="navbar-container">
            <h1 class="logo">Breast Cancer Prediction</h1>
            <ul class="nav-links">
                <li><a href="#home">Home</a></li>
                <li><a href="#about">About</a></li>
                <li><a href="register.php">Register</a></li>
                <li><a href="login.php">Login</a></li>
            </ul>
        </div>
    </nav>

    <br><br>
    <section id="home" class="section">
        <h2>Welcome to Breast Cancer Prediction System</h2>
        <p>Our platform offers personalized breast cancer risk assessment and recommendations for prevention strategies.</p>
        <p>Explore our services and take proactive steps towards breast cancer prevention.</p><br>
        <a href="register.php" class="btn">Get Started</a>
    </section><br><br>

    <section id="about" class="section">
        <h2>About Breast Cancer Prediction</h2>
        <p>Our platform aims to empower individuals with information about breast cancer risk factors and prevention strategies.</p>
        <p>We provide personalized risk assessments based on medical history and lifestyle factors, helping users make informed decisions about their health.</p>
        <p>Join us in the fight against breast cancer by spreading awareness and taking proactive steps towards prevention.</p>
    </section><br><br>

    <!-- Smooth scrolling JavaScript -->
    <script>
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();

                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    </script>

</body>
</html>
