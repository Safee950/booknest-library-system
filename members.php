<?php
require_once "includes/auth.php";
checkAdmin();

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $action = $_POST['action'] ?? "";

    if ($action == "add") {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        if ($name == "" || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 6) {
            setMessage("danger", "Enter a name, a valid email and a password of at least 6 characters.");
        } else {
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                setMessage("danger", "This email is already registered.");
            } else {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $role = "student";
                $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $name, $email, $hashed, $role);
                $stmt->execute();
                setMessage("success", "Member added.");
            }
        }
    }

    if ($action == "delete") {
        $id = (int) $_POST['id'];

        $stmt = $conn->prepare("SELECT COUNT(*) AS c FROM issued_books WHERE user_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $count = $stmt->get_result()->fetch_assoc()['c'];

        if ($count > 0) {
            setMessage("danger", "This member has issue records, so he cannot be removed.");
        } else {
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'student'");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            setMessage("success", "Member removed.");
        }
    }

    header("Location: members.php");
    exit;
}

$members = $conn->query("SELECT u.id, u.name, u.email, u.created_at,
                                (SELECT COUNT(*) FROM issued_books ib WHERE ib.user_id = u.id AND ib.status = 'issued') AS books_now
                         FROM users u WHERE u.role = 'student' ORDER BY u.name");

$pageTitle = "Members";
$activePage = "members.php";
include "includes/header.php";
?>
  <div class="container mt-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
      <h3>Library Members</h3>
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#memberModal"><i class="bi bi-person-plus"></i> Add Member</button>
    </div>

    <div class="row g-3">
      <?php while ($m = $members->fetch_assoc()) { ?>
        <div class="col-sm-6 col-lg-4">
          <div class="card shadow-sm">
            <div class="card-body text-center">
              <i class="bi bi-person-circle icon-big text-secondary"></i>
              <h5 class="mt-2 mb-0"><?php echo e($m['name']); ?></h5>
              <span class="badge bg-info text-dark">Student</span>
              <p class="text-muted mt-2 mb-1"><?php echo e($m['email']); ?></p>
              <p class="mb-2"><small>Books with him: <?php echo $m['books_now']; ?></small></p>
              <form method="POST" action="members.php" onsubmit="return confirmDelete('this member')">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?php echo $m['id']; ?>">
                <button type="submit" class="btn btn-sm btn-outline-danger">Remove</button>
              </form>
            </div>
          </div>
        </div>
      <?php } ?>
    </div>
  </div>

  <div class="modal fade" id="memberModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form method="POST" action="members.php">
          <input type="hidden" name="action" value="add">
          <div class="modal-header">
            <h5 class="modal-title">Add Member</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Name</label>
              <input type="text" name="name" class="form-control">
            </div>
            <div class="mb-3">
              <label class="form-label">Email</label>
              <input type="text" name="email" class="form-control">
            </div>
            <div class="mb-3">
              <label class="form-label">Password</label>
              <input type="password" name="password" class="form-control">
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Save</button>
          </div>
        </form>
      </div>
    </div>
  </div>
<?php include "includes/footer.php"; ?>
