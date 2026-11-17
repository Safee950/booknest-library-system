<?php
require_once "includes/auth.php";
checkLogin();

$pageTitle = "Dashboard";
$activePage = "dashboard.php";

if (isAdmin()) {
    $totalBooks = $conn->query("SELECT COUNT(*) AS c FROM books")->fetch_assoc()['c'];
    $totalMembers = $conn->query("SELECT COUNT(*) AS c FROM users WHERE role = 'student'")->fetch_assoc()['c'];
    $totalIssued = $conn->query("SELECT COUNT(*) AS c FROM issued_books WHERE status = 'issued'")->fetch_assoc()['c'];
    $totalOverdue = $conn->query("SELECT COUNT(*) AS c FROM issued_books WHERE status = 'issued' AND due_date < CURDATE()")->fetch_assoc()['c'];

    $recent = $conn->query("SELECT ib.issue_date, ib.due_date, ib.status, b.title, u.name
                            FROM issued_books ib
                            JOIN books b ON ib.book_id = b.id
                            JOIN users u ON ib.user_id = u.id
                            ORDER BY ib.id DESC LIMIT 5");
} else {
    $uid = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT ib.issue_date, ib.due_date, b.title
                            FROM issued_books ib
                            JOIN books b ON ib.book_id = b.id
                            WHERE ib.user_id = ? AND ib.status = 'issued'
                            ORDER BY ib.due_date");
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $myBooks = $stmt->get_result();

    $stmt = $conn->prepare("SELECT COALESCE(SUM(f.amount), 0) AS total
                            FROM fines f
                            JOIN issued_books ib ON f.issue_id = ib.id
                            WHERE ib.user_id = ? AND f.status = 'unpaid'");
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $myPendingFine = $stmt->get_result()->fetch_assoc()['total'];
}

include "includes/header.php";
?>
  <div class="container mt-4">
    <h3 class="mb-4">Welcome, <?php echo e($_SESSION['user_name']); ?></h3>

    <?php if (isAdmin()) { ?>
      <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
          <div class="card text-white bg-primary shadow-sm"><div class="card-body d-flex justify-content-between align-items-center">
            <div><h2 class="mb-0"><?php echo $totalBooks; ?></h2><small>Total Books</small></div><i class="bi bi-book icon-big"></i>
          </div></div>
        </div>
        <div class="col-6 col-lg-3">
          <div class="card text-white bg-success shadow-sm"><div class="card-body d-flex justify-content-between align-items-center">
            <div><h2 class="mb-0"><?php echo $totalMembers; ?></h2><small>Members</small></div><i class="bi bi-people icon-big"></i>
          </div></div>
        </div>
        <div class="col-6 col-lg-3">
          <div class="card text-white bg-warning shadow-sm"><div class="card-body d-flex justify-content-between align-items-center">
            <div><h2 class="mb-0"><?php echo $totalIssued; ?></h2><small>Books Issued</small></div><i class="bi bi-arrow-up-right-circle icon-big"></i>
          </div></div>
        </div>
        <div class="col-6 col-lg-3">
          <div class="card text-white bg-danger shadow-sm"><div class="card-body d-flex justify-content-between align-items-center">
            <div><h2 class="mb-0"><?php echo $totalOverdue; ?></h2><small>Overdue</small></div><i class="bi bi-exclamation-circle icon-big"></i>
          </div></div>
        </div>
      </div>

      <div class="row g-4">
        <div class="col-lg-8">
          <div class="card shadow-sm">
            <div class="card-header bg-white"><strong>Recently Issued Books</strong></div>
            <div class="table-responsive">
              <table class="table table-hover mb-0">
                <thead class="table-light">
                  <tr><th>Book</th><th>Member</th><th>Issue Date</th><th>Status</th></tr>
                </thead>
                <tbody>
                  <?php while ($row = $recent->fetch_assoc()) {
                      if ($row['status'] == 'returned') {
                          $badge = '<span class="badge bg-secondary">Returned</span>';
                      } elseif ($row['due_date'] < date("Y-m-d")) {
                          $badge = '<span class="badge bg-danger">Overdue</span>';
                      } else {
                          $badge = '<span class="badge bg-success">Issued</span>';
                      } ?>
                    <tr>
                      <td><?php echo e($row['title']); ?></td>
                      <td><?php echo e($row['name']); ?></td>
                      <td><?php echo e($row['issue_date']); ?></td>
                      <td><?php echo $badge; ?></td>
                    </tr>
                  <?php } ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        <div class="col-lg-4">
          <div class="card shadow-sm">
            <div class="card-header bg-white"><strong>Quick Actions</strong></div>
            <div class="card-body d-grid gap-2">
              <a href="add-book.php" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Add New Book</a>
              <a href="issue-book.php" class="btn btn-success"><i class="bi bi-arrow-left-right"></i> Issue a Book</a>
              <a href="members.php" class="btn btn-outline-primary"><i class="bi bi-people"></i> View Members</a>
              <a href="fines.php" class="btn btn-outline-danger"><i class="bi bi-cash-coin"></i> Check Fines</a>
            </div>
          </div>
        </div>
      </div>

    <?php } else { ?>
      <div class="row g-4">
        <div class="col-lg-8">
          <div class="card shadow-sm">
            <div class="card-header bg-white"><strong>Books I Have Taken</strong></div>
            <div class="table-responsive">
              <table class="table table-hover mb-0">
                <thead class="table-light">
                  <tr><th>Book</th><th>Issue Date</th><th>Due Date</th><th>Status</th></tr>
                </thead>
                <tbody>
                  <?php if ($myBooks->num_rows == 0) { ?>
                    <tr><td colspan="4" class="text-center text-muted">You have no issued books.</td></tr>
                  <?php } ?>
                  <?php while ($row = $myBooks->fetch_assoc()) {
                      if ($row['due_date'] < date("Y-m-d")) {
                          $badge = '<span class="badge bg-danger">Overdue</span>';
                      } else {
                          $badge = '<span class="badge bg-success">On time</span>';
                      } ?>
                    <tr>
                      <td><?php echo e($row['title']); ?></td>
                      <td><?php echo e($row['issue_date']); ?></td>
                      <td><?php echo e($row['due_date']); ?></td>
                      <td><?php echo $badge; ?></td>
                    </tr>
                  <?php } ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        <div class="col-lg-4">
          <div class="alert alert-warning"><strong>Pending Fine:</strong> Rs. <?php echo e($myPendingFine); ?></div>
          <a href="books.php" class="btn btn-primary w-100">Browse Books</a>
        </div>
      </div>
    <?php } ?>
  </div>
<?php include "includes/footer.php"; ?>
