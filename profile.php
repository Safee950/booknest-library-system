<?php
require_once "includes/auth.php";
checkLogin();

$userId = $_SESSION['user_id'];
$errors = [];

$stmt = $conn->prepare("SELECT name, email, role FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $newPassword = $_POST['new_password'];

    if ($name == "") { $errors[] = "Name is required."; }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = "Please enter a valid email."; }
    if ($newPassword != "" && strlen($newPassword) < 6) { $errors[] = "New password must be at least 6 characters."; }

    if (count($errors) == 0) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $email, $userId);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = "This email is used by another account.";
        }
    }

    if (count($errors) == 0) {
        if ($newPassword != "") {
            $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?");
            $stmt->bind_param("sssi", $name, $email, $hashed, $userId);
        } else {
            $stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
            $stmt->bind_param("ssi", $name, $email, $userId);
        }
        $stmt->execute();

        $_SESSION['user_name'] = $name; // update the name shown in the nevbar
        setMessage("success", "Profile updated.");
        header("Location: profile.php");
        exit;
    }

    $user['name'] = $name;
    $user['email'] = $email;
}

$pageTitle = "My Profile";
$activePage = "profile.php";
include "includes/header.php";
?>
  <div class="container mt-4">
    <h3 class="mb-4">My Profile</h3>

    <?php if (count($errors) > 0) { ?>
      <div class="alert alert-danger">
        <?php foreach ($errors as $error) { echo e($error) . "<br>"; } ?>
      </div>
    <?php } ?>

    <div class="row g-4">
      <div class="col-md-4">
        <div class="card shadow-sm text-center">
          <div class="card-body">
            <i class="bi bi-person-circle profile-icon text-primary"></i>
            <h5 class="mt-2 mb-0"><?php echo e($user['name']); ?></h5>
            <p class="text-muted"><?php echo e(ucfirst($user['role'])); ?></p>
          </div>
        </div>
      </div>
      <div class="col-md-8">
        <div class="card shadow-sm">
          <div class="card-header bg-white"><strong>Edit Details</strong></div>
          <div class="card-body">
            <form method="POST" action="profile.php">
              <div class="mb-3">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" class="form-control" value="<?php echo e($user['name']); ?>">
              </div>
              <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="text" name="email" class="form-control" value="<?php echo e($user['email']); ?>">
              </div>
              <div class="mb-3">
                <label class="form-label">New Password (leave empty to keep the old one)</label>
                <input type="password" name="new_password" class="form-control">
              </div>
              <button type="submit" class="btn btn-primary">Update Profile</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
<?php include "includes/footer.php"; ?>
