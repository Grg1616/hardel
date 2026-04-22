<?php include 'includes/header.php'?>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Hardel | Hardware Store Delivery System</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap CSS CDN -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
    }
    .hero {
      position: relative;
      background: linear-gradient(to right, #00695c, #26a69a);
      color: white;
      padding: 100px 0;
      text-align: center;
      transition: background-image 1s ease-in-out;
    }
    .hero::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background-size: cover;
      background-position: center;
      z-index: -1; /* Ensure it stays behind text */
    }
    .feature-icon {
      font-size: 2rem;
      color: #00695c;
    }
    .btn-custom {
      background-color: #00695c;
      color: white;
    }
    footer {
      background-color: #f8f9fa;
      padding: 20px 0;
      text-align: center;
    }
  </style>
</head>
<body>
  <!-- Hero Section -->
  <section class="hero">
    <div class="container">
      <h1 class="display-4 fw-bold">Hardel</h1>
      <p class="lead">Fast & Reliable Hardware Store Delivery at Your Doorstep</p>

      <a href="login.php" class="btn btn-lg btn-light mt-3">Get Started</a>
    </div>
  </section>

  <!-- Features Section -->
  <section class="bg py-5">
    <div class="container text-center">
      <h2 class="mb-4">Why Choose Hardel?</h2>
      <div class="row g-4">
        <div class="col-md-4">
          <div class="feature-icon mb-2">🚚</div>
          <h5>Real-Time Tracking</h5>
          <p>Track deliveries in real-time with our integrated map system.</p>
        </div>
        <div class="col-md-4">
          <div class="feature-icon mb-2">📦</div>
          <h5>Smart Order Management</h5>
          <p>Automated and optimized order processing for fast deliveries.</p>
        </div>
        <div class="col-md-4">
          <div class="feature-icon mb-2">📊</div>
          <h5>Analytics Dashboard</h5>
          <p>Visual insights into sales, deliveries, and logistics efficiency.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Call to Action -->
  <section class="text-center py-5 bg-light">
    <div class="container">
      <h3>Start managing your hardware deliveries the smart way!</h3>
      <a href="login.php?signup=true" class="btn btn-custom btn-lg mt-3">Sign Up Now</a>
    </div>
  </section>

  <!-- Footer -->
  <footer>
    <div class="container">
      <p>&copy; <?= date('Y'); ?> Hardel Delivery System. All rights reserved.</p>
    </div>
  </footer>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <!-- JavaScript to change background images -->
  <script>
    const images = [
      'img/bg.jpg',
      'img/bg2.jpg',
      'img/bg3.jpg'
    ];

    let currentImageIndex = 0;

    function changeBackgroundImage() {
      currentImageIndex = (currentImageIndex + 1) % images.length;
      document.querySelector('.hero::before').style.backgroundImage = `url(${images[currentImageIndex]})`;
    }

    setInterval(changeBackgroundImage, 5000); // Change image every 5 seconds
  </script>
</body>
</html>
