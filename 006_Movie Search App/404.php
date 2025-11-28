<?php
// Set 404 status header
http_response_code(404);

// Get the theme from cookie (matching your main site's theme system)
$theme = isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'dark';
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="<?= $theme ?>">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="author" content="manases">
  <title>404 - Page Not Found | TMDB Explorer</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="shortcut icon" href="images/tmdb-logo.jpg" type="image/x-icon">
  <link rel="icon" href="images/tmdb-logo.jpg" type="image/jpg">
  <!-- Custom CSS -->
  <link href="styles.css" rel="stylesheet">

  <style>
    .error-404 {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .error-content {
      text-align: center;
      color: white;
      max-width: 600px;
      padding: 2rem;
    }

    .error-code {
      font-size: 8rem;
      font-weight: bold;
      margin-bottom: 1rem;
      text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
    }

    .error-message {
      font-size: 1.5rem;
      margin-bottom: 2rem;
      opacity: 0.9;
    }

    .error-description {
      font-size: 1.1rem;
      margin-bottom: 3rem;
      opacity: 0.8;
    }

    .back-buttons {
      display: flex;
      gap: 1rem;
      justify-content: center;
      flex-wrap: wrap;
    }

    .btn-custom {
      padding: 12px 30px;
      font-size: 1.1rem;
      border-radius: 50px;
      transition: all 0.3s ease;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 8px;
    }

    .btn-primary-custom {
      background: rgba(255, 255, 255, 0.2);
      border: 2px solid rgba(255, 255, 255, 0.3);
      color: white;
    }

    .btn-primary-custom:hover {
      background: rgba(255, 255, 255, 0.3);
      border-color: rgba(255, 255, 255, 0.5);
      color: white;
      transform: translateY(-2px);
    }

    .btn-outline-custom {
      background: transparent;
      border: 2px solid rgba(255, 255, 255, 0.5);
      color: white;
    }

    .btn-outline-custom:hover {
      background: white;
      color: #667eea;
      transform: translateY(-2px);
    }

    .floating-elements {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      overflow: hidden;
      pointer-events: none;
    }

    .floating-element {
      position: absolute;
      opacity: 0.1;
      animation: float 6s ease-in-out infinite;
    }

    .floating-element:nth-child(1) {
      top: 10%;
      left: 10%;
      animation-delay: 0s;
    }

    .floating-element:nth-child(2) {
      top: 20%;
      right: 10%;
      animation-delay: 2s;
    }

    .floating-element:nth-child(3) {
      bottom: 20%;
      left: 20%;
      animation-delay: 4s;
    }

    .floating-element:nth-child(4) {
      bottom: 10%;
      right: 20%;
      animation-delay: 1s;
    }

    @keyframes float {

      0%,
      100% {
        transform: translateY(0px) rotate(0deg);
      }

      50% {
        transform: translateY(-20px) rotate(180deg);
      }
    }

    @media (max-width: 768px) {
      .error-code {
        font-size: 6rem;
      }

      .error-message {
        font-size: 1.3rem;
      }

      .error-description {
        font-size: 1rem;
      }

      .back-buttons {
        flex-direction: column;
        align-items: center;
      }

      .btn-custom {
        width: 100%;
        max-width: 300px;
      }
    }
  </style>
</head>

<body class="<?= $theme ?>-mode">
  <div class="error-404">
    <div class="floating-elements">
      <i class="fas fa-film floating-element"></i>
      <i class="fas fa-tv floating-element"></i>
      <i class="fas fa-star floating-element"></i>
      <i class="fas fa-ticket-alt floating-element"></i>
    </div>

    <div class="error-content">
      <div class="error-code">404</div>
      <div class="error-message">Oops! Page Not Found</div>
      <div class="error-description">
        The page you're looking for seems to have vanished into the digital void.
        It might have been moved, deleted, or never existed in the first place.
      </div>

      <div class="back-buttons">
        <a href="/movies/" class="btn btn-custom btn-primary-custom">
          <i class="fas fa-home"></i>
          Back to Home
        </a>
        <a href="javascript:history.back()" class="btn btn-custom btn-outline-custom">
          <i class="fas fa-arrow-left"></i>
          Go Back
        </a>
      </div>

      <div class="mt-4">
        <small class="text-light opacity-75">
          Error Code: 404 | Time: <?= date('Y-m-d H:i:s') ?>
        </small>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    // Add some interactivity
    document.addEventListener('DOMContentLoaded', function() {
      // Auto-redirect to home after 30 seconds (optional)
      // setTimeout(function() {
      //     window.location.href = '/movies/';
      // }, 30000);

      // Log 404 error for analytics (you can implement this)
      console.log('404 Error logged:', {
        url: window.location.href,
        referrer: document.referrer,
        timestamp: new Date().toISOString()
      });
    });
  </script>
</body>

</html>