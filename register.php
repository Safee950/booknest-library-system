<?php
require_once "includes/auth.php";

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$errors = [];
$name = "";
$email = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    // velidation
    if ($name == "") {
        $errors[] = "Name is required.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email.";
    }
    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    }
    if ($password != $confirm) {
        $errors[] = "Passwords do not match.";
    }

    if (count($errors) == 0) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = "This email is already registered.";
        }
    }

    if (count($errors) == 0) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $role = "student"; //everyone who registers here is a student
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $hashed, $role);
        $stmt->execute();

        setMessage("success", "Account created! Please login.");
        header("Location: login.php");
        exit;
    }
}

$pageTitle = "Register";
$activePage = "register.php";
include "includes/header.php";
?>
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-7 col-lg-6">
        <div class="card form-card shadow">
          <div class="card-body p-4">
            <h3 class="text-center mb-4">Create Account</h3>

            <?php if (count($errors) > 0) { ?>
              <div class="alert alert-danger">
                <?php foreach ($errors as $error) { echo e($error) . "<br>"; } ?>
              </div>
            <?php } ?>

            <form method="POST" action="register.php">
              <div class="mb-3">
                <label for="name" class="form-label">Full Name</label>
                <input type="text" class="form-control" id="name" name="name" value="<?php echo e($name); ?>">
              </div>
              <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="text" class="form-control" id="email" name="email" value="<?php echo e($email); ?>">
              </div>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="password" class="form-label">Password</label>
                  <input type="password" class="form-control" id="password" name="password">
                </div>
                <div class="col-md-6 mb-3">
                  <label for="confirm_password" class="form-label">Confirm Password</label>
                  <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                </div>
              </div>
              <button type="submit" class="btn btn-primary w-100">Register</button>
            </form>
            <p class="text-center mt-3 mb-0">Already have an account? <a href="login.php">Login</a></p>
          </div>
        </div>
      </div>
    </div>
  </div>
<?php include "includes/footer.php"; ?>
