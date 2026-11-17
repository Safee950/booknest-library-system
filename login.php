<?php
require_once "includes/auth.php";

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = "";
$email = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if ($email == "" || $password == "") {
        $error = "Please enter email and password.";
    } else {
        $stmt = $conn->prepare("SELECT id, name, password, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['role'] = $user['role'];
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Wrong email or password.";
        }
    }
}

$pageTitle = "Login";
$activePage = "login.php";
include "includes/header.php";
?>
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-5 col-lg-4">
        <div class="card form-card shadow">
          <div class="card-body p-4">
            <h3 class="text-center mb-4">Login</h3>

            <?php if ($error != "") { ?>
              <div class="alert alert-danger"><?php echo e($error); ?></div>
            <?php } ?>

            <form method="POST" action="login.php">
              <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="text" class="form-control" id="email" name="email" value="<?php echo e($email); ?>">
              </div>
              <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password">
              </div>
              <button type="submit" class="btn btn-primary w-100">Login</button>
            </form>
            <p class="text-center mt-3 mb-0">New here? <a href="register.php">Create an account</a></p>
          </div>
        </div>
      </div>
    </div>
  </div>
<?php include "includes/footer.php"; ?>
