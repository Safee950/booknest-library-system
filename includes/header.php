<?php
if (!isset($pageTitle)) { $pageTitle = "BookNest"; }
if (!isset($activePage)) { $activePage = ""; }

function navLink($label, $file, $activePage) {
    $class = ($activePage == $file) ? "nav-link active" : "nav-link";
    echo '<li class="nav-item"><a class="' . $class . '" href="' . $file . '">' . $label . '</a></li>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo e($pageTitle); ?> - BookNest Library</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="css/style.css" rel="stylesheet">
</head>
<body>
  <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
      <a class="navbar-brand" href="<?php echo isset($_SESSION['user_id']) ? 'dashboard.php' : 'index.php'; ?>"><i class="bi bi-book-half"></i> BookNest</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="menu">
        <?php if (isset($_SESSION['user_id'])) { ?>
          <ul class="navbar-nav me-auto">
            <?php
            navLink("Dashboard", "dashboard.php", $activePage);
            navLink("Books", "books.php", $activePage);
            if (isAdmin()) {
                navLink("Add Book", "add-book.php", $activePage);
                navLink("Members", "members.php", $activePage);
                navLink("Issue / Return", "issue-book.php", $activePage);
                navLink("Fines", "fines.php", $activePage);
            } else {
                navLink("My Fines", "fines.php", $activePage);
            }
            ?>
          </ul>
          <div class="dropdown">
            <a class="btn btn-light btn-sm dropdown-toggle" href="#" data-bs-toggle="dropdown">
              <i class="bi bi-person-circle"></i> <?php echo e($_SESSION['user_name']); ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="profile.php">My Profile</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="logout.php">Logout</a></li>
            </ul>
          </div>
        <?php } else { ?>
          <ul class="navbar-nav ms-auto">
            <?php
            navLink("Home", "index.php", $activePage);
            navLink("Login", "login.php", $activePage);
            navLink("Register", "register.php", $activePage);
            ?>
          </ul>
        <?php } ?>
      </div>
    </div>
  </nav>
  <?php showMessage(); ?>
