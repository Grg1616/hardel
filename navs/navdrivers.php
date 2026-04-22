<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Hardel</title>
  <link rel="icon" href="img/logo.png">
  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/boxicons@latest/css/boxicons.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet.locatecontrol@0.84.1/dist/L.Control.Locate.min.css" />
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css"/>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
  :root {
    --nav-width: 68px;
    --first-color-light: #AFA5D9;
    --white-color: #F7F6FB;
    --body-font: 'Nunito', sans-serif;
    --z-fixed: 100;
  }

  *, ::before, ::after {
    box-sizing: border-box;
  }

  body {
    padding-left: var(--nav-width);
    font-family: var(--body-font);
    transition: .5s;
  }

  #hardel-app .l-navbar {
    position: fixed;
    top: 0;
    left: 0;
    width: var(--nav-width);
    height: 100vh;
    background-color: rgb(34, 39, 34);
    padding-top: 1rem;
    z-index: var(--z-fixed);
  }

  #hardel-app .nav {
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
  }

  #hardel-app .nav_link {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: .75rem 0;
    color: var(--first-color-light);
    text-decoration: none;
    transition: .3s;
    margin-bottom: 3rem;
  }

  #hardel-app .nav_link:hover,
  #hardel-app .nav_link.active {
    color: var(--white-color);
  }

  #hardel-app .nav_icon {
    font-size: 1.5rem;
  }

  #hardel-app .active::before {
    content: '';
    position: absolute;
    left: 0;
    width: 2px;
    height: 32px;
    background-color: var(--white-color);
  }

  #hardel-app .nav_logo img {
    width: 40px;
  }

</style>
</head>

<body>
<div id="hardel-app" class="body-pd">

  <div class="l-navbar show" id="nav-bar">
    <nav class="nav">
      <div>
        <a href="driver.php#" class="nav_link nav_logo" data-bs-toggle="popover" data-bs-trigger="hover" data-bs-content="Hardel">
          <img src="img/logo.png" alt="Hardel">
        </a>
        <div class="nav_list mt-4">
          <a href="driver.php" class="nav_link active" data-bs-toggle="popover" data-bs-trigger="hover" data-bs-content="Deliver">
            <i class='bx bx-package nav_icon'></i>
          </a>
          <a href="map.php" class="nav_link" data-bs-toggle="popover" data-bs-trigger="hover" data-bs-content="Map">
            <i class='bx bx-map nav_icon'></i>
          </a>
          <a href="driver_profile.php" class="nav_link" data-bs-toggle="popover" data-bs-trigger="hover" data-bs-content="Profile">
            <i class='bx bx-user-circle nav_icon'></i>
          </a>
        </div>
      </div>
      <a href="logout.php" class="nav_link" data-bs-toggle="popover" data-bs-trigger="hover" data-bs-content="Sign Out">
        <i class='bx bx-log-out nav_icon'></i>
      </a>
    </nav>
  </div>

</div>

<script>
  document.addEventListener("DOMContentLoaded", function () {
    const linkColor = document.querySelectorAll('.nav_link');
    const currentUrl = window.location.href;

    linkColor.forEach(link => {
      if (link.href === currentUrl) {
        link.classList.add('active');
      } else {
        link.classList.remove('active');
      }
    });

    // Initialize all popovers
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.forEach(function (el) {
      new bootstrap.Popover(el);
    });
  });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
