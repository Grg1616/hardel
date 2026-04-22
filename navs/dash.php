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
      --header-height: 3rem;
      --nav-width: 68px;
      --first-color: #4723D9;
      --first-color-light: #AFA5D9;
      --white-color: #F7F6FB;
      --body-font: 'Nunito', sans-serif;
      --normal-font-size: 1rem;
      --z-fixed: 100;
    }
    *, ::before, ::after {
      box-sizing: border-box;
    }
    body {
      position: relative;
      margin: var(--header-height) 0 0 0;
      padding: 0 1rem;
      font-family: var(--body-font);
      font-size: var(--normal-font-size);
      transition: .5s;
    }
    a {
      text-decoration: none;
    }
    .header {
      width: 100%;
      height: var(--header-height);
      position: fixed;
      top: 0;
      left: 0;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 1rem;
      background-color: var(--white-color);
      z-index: var(--z-fixed);
      transition: .5s;
    }
    .header_toggle {
      color: #0b0f2f;
      font-size: 1.5rem;
      cursor: pointer;
    }
    .header_img {
      width: 35px;
      height: 35px;
      display: flex;
      justify-content: center;
      border-radius: 50%;
      overflow: hidden;
    }
    .header_img img {
      width: 40px;
    }
    .l-navbar {
      position: fixed;
      top: 0;
      left: -30%;
      width: var(--nav-width);
      height: 100vh;
      background-color: #0b0f2f;
      padding: .5rem 1rem 0 0;
      transition: .5s;
      z-index: var(--z-fixed);
    }
    .nav {
      height: 100%;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      overflow: hidden;
    }
    .nav_logo, .nav_link {
      display: grid;
      grid-template-columns: max-content max-content;
      align-items: center;
      column-gap: 1rem;
      padding: .5rem 0 .5rem 1.5rem;
    }
    .nav_logo {
      margin-bottom: 2rem;
    }
    .nav_logo-icon {
      font-size: 1.25rem;
      color: var(--white-color);
    }
    .nav_logo-name {
      color: var(--white-color);
      font-weight: 700;
    }
    .nav_link {
      position: relative;
      color: var(--first-color-light);
      margin-bottom: 1.5rem;
      transition: .3s;
    }
    .nav_link:hover {
      color: var(--white-color);
    }
    .nav_icon {
      font-size: 1.25rem;
    }
    .show {
      left: 0;
    }
    .body-pd {
      padding-left: calc(var(--nav-width) + 1rem);
    }
    .active {
      color: var(--white-color);
    }
    .active::before {
      content: '';
      position: absolute;
      left: 0;
      width: 2px;
      height: 32px;
      background-color: var(--white-color);
    }
    .height-100 {
      height: 100vh;
    }
    @media screen and (min-width: 768px) {
      body {
        margin: calc(var(--header-height) + 1rem) 0 0 0;
        padding-left: calc(var(--nav-width) + 2rem);
      }
      .header {
        height: calc(var(--header-height) + 1rem);
        padding: 0 2rem 0 calc(var(--nav-width) + 2rem);
      }
      .header_img {
        width: 40px;
        height: 40px;
      }
      .header_img img {
        width: 45px;
      }
      .l-navbar {
        left: 0;
        padding: 1rem 1rem 0 0;
      }
      .show {
        width: calc(var(--nav-width) + 156px);
      }
      .body-pd {
        padding-left: calc(var(--nav-width) + 188px);
      }
    }
    </style>
</head>
<?php
// Get the current page filename
$current_page = basename($_SERVER['PHP_SELF']);
?>
<body>
<div class="hardel-app">
  <header class="header" id="header">
    <div class="header_toggle"> <i class='bx bx-menu' id="header-toggle"></i> </div>
  </header>

  <div class="l-navbar" id="nav-bar">
    <nav class="nav">
      <div>
        <a href="admindash.php" class="ms-3">
          <i class=""> <img src="img/logo.png" alt="Hardel" width="40px"></i>
          <span class="nav_logo-name">Hardel</span>
        </a>
        <div class="nav_list mt-4">
          <a href="admindash.php" class="nav_link active">
            <i class='bx bx-grid-alt nav_icon'></i>
            <span class="nav_name">Dashboard</span>
          </a>
          <a href="orders.php" class="nav_link">
            <i class='bx bx-cart nav_icon'></i>
            <span class="nav_name">Orders</span>
          </a>
          <a href="addproduct.php" class="nav_link">
            <i class='bx bx-plus nav_icon'></i>
            <span class="nav_name">Add Product</span>
          </a>
          <a href="adddriver.php" class="nav_link">
            <i class='bx bx-user nav_icon'></i>
            <span class="nav_name">Drivers</span>
          </a>
          <a href="adminsettings.php" class="nav_link">
            <i class='bx bx-cog nav_icon'></i>
            <span class="nav_name">Settings</span>
          </a>
        </div>
      </div>
      <a href="logout.php" class="nav_link">
        <i class='bx bx-log-out nav_icon'></i>
        <span class="nav_name">SignOut</span>
      </a>
    </nav>
  </div>
</div>

<script>
  document.addEventListener("DOMContentLoaded", function () {
    const toggleNavbar = (toggleId, navId, bodyClass, headerId) => {
      const toggle = document.getElementById(toggleId);
      const nav = document.getElementById(navId);
      const body = document.querySelector(`.${bodyClass}`);
      const header = document.getElementById(headerId);

      if (toggle && nav && body && header) {
        toggle.addEventListener('click', () => {
          nav.classList.toggle('show');
          toggle.classList.toggle('bx-x');
          body.classList.toggle('body-pd');
          header.classList.toggle('body-pd');
        });
      }
    };

    toggleNavbar('header-toggle', 'nav-bar', 'hardel-app', 'header');

    const linkColor = document.querySelectorAll('.nav_link');
    const currentUrl = window.location.href;
    linkColor.forEach(link => {
      if (link.href === currentUrl) {
        link.classList.add('active');
      } else {
        link.classList.remove('active');
      }
    });
  });
</script>
</body>
</html>
