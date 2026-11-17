<?php
require_once "includes/auth.php";
$pageTitle = "Home";
$activePage = "index.php";
include "includes/header.php";
?>
  <section class="hero text-white text-center">
    <div class="container">
      <h1 class="display-4 fw-bold">Welcome to BookNest</h1>
      <p class="lead">A simple way to manage books, members and fines in your library.</p>
      <?php if (isset($_SESSION['user_id'])) { ?>
        <a href="dashboard.php" class="btn btn-warning btn-lg">Go to Dashboard</a>
      <?php } else { ?>
        <a href="register.php" class="btn btn-warning btn-lg me-2">Get Started</a>
        <a href="login.php" class="btn btn-outline-light btn-lg">Login</a>
      <?php } ?>
    </div>
  </section>

  <section class="container my-5">
    <h2 class="text-center mb-4">What you can do</h2>
    <div class="row g-4">
      <div class="col-md-4">
        <div class="card h-100 text-center shadow-sm">
          <div class="card-body">
            <i class="bi bi-journal-bookmark icon-big text-primary"></i>
            <h5 class="card-title mt-2">Manage Books</h5>
            <p class="card-text">Add new books, search them and keep the record updated.</p>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card h-100 text-center shadow-sm">
          <div class="card-body">
            <i class="bi bi-people icon-big text-success"></i>
            <h5 class="card-title mt-2">Manage Members</h5>
            <p class="card-text">Keep the list of students who use the library.</p>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card h-100 text-center shadow-sm">
          <div class="card-body">
            <i class="bi bi-arrow-left-right icon-big text-danger"></i>
            <h5 class="card-title mt-2">Issue and Return</h5>
            <p class="card-text">Issue a book to a member and take it back with fine details.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="bg-light py-5">
    <div class="container text-center">
      <h2>About Us</h2>
      <p class="mx-auto about-text">BookNest is our semester project. It helps a librarian do daily work like issuing books, checking who has what, and calculating fines, all from one place.</p>
    </div>
  </section>
<?php include "includes/footer.php"; ?>
