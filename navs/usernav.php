<style>
  body.hd-body {
    font-family: 'Nunito', sans-serif;
    background-color: #f8f9fa;
    margin: 0;
    padding-top: 70px;
  }

  .hd-navbar {
    background-color: rgb(9, 59, 5);
  }

  .hd-navbar .navbar-brand,
  .hd-navbar .nav-link,
  .hd-navbar .dropdown-toggle,
  .hd-navbar .dropdown-item {
    color: #fff !important;
  }

  .hd-navbar .nav-link:hover,
  .hd-navbar .dropdown-item:hover {
    color:rgb(0, 0, 0) !important;
    background-color: transparent;
    
  }
  .hd-header-img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    overflow: hidden;
  }
  .hd-header-img img {
    width: 100%;
    height: auto;
  }

  .hd-navbar .dropdown-menu {
    background-color: rgb(34, 39, 34);
    border: none;
  }

  .hd-navbar-logo {
    width: 40px;
    height: 40px;
    object-fit: contain;
  }
   .hd-cart-icon {
    font-size: 1.5rem;
    margin-right: 1.5rem;
    color: #fff;
    transition: transform 0.2s;
  }

  .hd-cart-icon:hover {
    color: #000;
    transform: scale(1.1);
  }

  .hd-search-btn {
    background-color: #28a745;
    border-color: #28a745;
    color: white;
  }

  .hd-search-btn:hover {
    background-color: #218838;
    border-color: #1e7e34;
  }
  .hd-cart-icon {
    font-size: 2rem;
    margin-right: 1.5rem;
    color: #fff;
    transition: transform 0.2s;
  }

  .hd-cart-icon:hover {
    color: #000;
    transform: scale(1.4);
  }

  .hd-search-btn {
    background-color: #28a745;
    border-color: #28a745;
    color: white;
  }

  .hd-search-btn:hover {
    background-color: #218838;
    border-color: #1e7e34;
  }
</style>

<body class="hd-body">
  <!-- Top Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark hd-navbar fixed-top shadow-sm">
    <div class="container-fluid px-4">
      <a class="navbar-brand d-flex align-items-center" href="welcome.php">
        <img src="img/logo.png" class="hd-navbar-logo me-2" alt="Hardel Logo">
        Hardel
      </a>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse justify-content-between" id="navbarContent">
        <form class="d-flex mx-auto" style="max-width: 500px;">
          <div class="input-group">
            <input class="form-control" type="search" placeholder="Search..." aria-label="Search">
            <button class="btn hd-search-btn" type="submit">
              <i class='bx bx-search'></i>
            </button>
          </div>
        </form>

        <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
          <!-- Cart Icon -->
          <li class="nav-item">
            <a class="nav-link hd-cart-icon" href="cart.php">
              <i class='bx bx-cart'></i>
            </a>
          </li>

          <!-- User Dropdown -->
          <li class="nav-item dropdown ms-2">
            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
              <div class="hd-header-img me-2">
                <img src="img/user.png" alt="User Icon">
              </div>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
              <li><a class="dropdown-item" href="userpage.php"><i class='bx bx-user'></i> Profile</a></li>
              <li><a class="dropdown-item" href="userorder.php"><i class='bx bx-package'></i> Orders</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="logout.php"><i class='bx bx-log-out'></i> Sign Out</a></li>
            </ul>
          </li>
        </ul>
      </div>
    </div>
  </nav>
