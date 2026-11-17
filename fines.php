<?php
require_once "includes/auth.php";
checkLogin();

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    checkAdmin();
    $fineId = (int) $_POST['fine_id'];
    $stmt = $conn->prepare("UPDATE fines SET status = 'paid' WHERE id = ?");
    $stmt->bind_param("i", $fineId);
    $stmt->execute();
    setMessage("success", "Fine marked as paid.");
    header("Location: fines.php");
    exit;
}

$sql = "SELECT f.id, f.amount, f.status, ib.due_date, ib.return_date, b.title, u.name
        FROM fines f
        JOIN issued_books ib ON f.issue_id = ib.id
        JOIN books b ON ib.book_id = b.id
        JOIN users u ON ib.user_id = u.id";

if (isAdmin()) {
    $result = $conn->query($sql . " ORDER BY f.id DESC");
} else {
    $stmt = $conn->prepare($sql . " WHERE ib.user_id = ? ORDER BY f.id DESC");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
}

$fines = [];
$total = 0;
$paid = 0;
while ($row = $result->fetch_assoc()) {
    $fines[] = $row;
    $total += $row['amount'];
    if ($row['status'] == 'paid') {
        $paid += $row['amount'];
    }
}
$pending = $total - $paid;

$pageTitle = "Fines";
$activePage = "fines.php";
include "includes/header.php";
?>
  <div class="container mt-4">
    <h3 class="mb-4"><?php echo isAdmin() ? "Fines" : "My Fines"; ?></h3>
    <div class="row g-3 mb-4">
      <div class="col-md-4"><div class="alert alert-danger mb-0"><strong>Total Fine:</strong> Rs. <?php echo $total; ?></div></div>
      <div class="col-md-4"><div class="alert alert-success mb-0"><strong>Paid:</strong> Rs. <?php echo $paid; ?></div></div>
      <div class="col-md-4"><div class="alert alert-warning mb-0"><strong>Pending:</strong> Rs. <?php echo $pending; ?></div></div>
    </div>

    <div class="card shadow-sm">
      <div class="table-responsive">
        <table class="table table-striped align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Member</th><th>Book</th><th>Due Date</th><th>Returned On</th><th>Fine</th><th>Status</th>
              <?php if (isAdmin()) { echo "<th>Action</th>"; } ?>
            </tr>
          </thead>
          <tbody>
            <?php if (count($fines) == 0) { ?>
              <tr><td colspan="7" class="text-center text-muted">No fines found.</td></tr>
            <?php } ?>
            <?php foreach ($fines as $f) { ?>
              <tr>
                <td><?php echo e($f['name']); ?></td>
                <td><?php echo e($f['title']); ?></td>
                <td><?php echo e($f['due_date']); ?></td>
                <td><?php echo e($f['return_date']); ?></td>
                <td>Rs. <?php echo e($f['amount']); ?></td>
                <td>
                  <?php if ($f['status'] == 'paid') { ?>
                    <span class="badge bg-success">Paid</span>
                  <?php } else { ?>
                    <span class="badge bg-danger">Unpaid</span>
                  <?php } ?>
                </td>
                <?php if (isAdmin()) { ?>
                  <td>
                    <?php if ($f['status'] == 'unpaid') { ?>
                      <form method="POST" action="fines.php">
                        <input type="hidden" name="fine_id" value="<?php echo $f['id']; ?>">
                        <button type="submit" class="btn btn-sm btn-success">Mark Paid</button>
                      </form>
                    <?php } else { ?>
                      <button class="btn btn-sm btn-secondary" disabled>Done</button>
                    <?php } ?>
                  </td>
                <?php } ?>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
<?php include "includes/footer.php"; ?>
