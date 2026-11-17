<?php
require_once "includes/auth.php";
checkAdmin();

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $action = $_POST['action'] ?? "";

    if ($action == "issue") {
        $userId = (int) $_POST['user_id'];
        $bookId = (int) $_POST['book_id'];
        $issueDate = $_POST['issue_date'];
        $dueDate = $_POST['due_date'];

        $stmt = $conn->prepare("SELECT available_copies FROM books WHERE id = ?");
        $stmt->bind_param("i", $bookId);
        $stmt->execute();
        $book = $stmt->get_result()->fetch_assoc();

        if ($userId == 0 || $bookId == 0 || $issueDate == "" || $dueDate == "") {
            setMessage("danger", "Please fill all the fields.");
        } elseif ($dueDate <= $issueDate) {
            setMessage("danger", "Due date must be after the issue date.");
        } elseif (!$book || $book['available_copies'] < 1) {
            setMessage("danger", "No copy of this book is available.");
        } else {
            $stmt = $conn->prepare("INSERT INTO issued_books (book_id, user_id, issue_date, due_date) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiss", $bookId, $userId, $issueDate, $dueDate);
            $stmt->execute();

            $stmt = $conn->prepare("UPDATE books SET available_copies = available_copies - 1 WHERE id = ?");
            $stmt->bind_param("i", $bookId);
            $stmt->execute();

            setMessage("success", "Book issued successfully.");
        }
    }

    if ($action == "return") {
        $issueId = (int) $_POST['issue_id'];

        $stmt = $conn->prepare("SELECT * FROM issued_books WHERE id = ? AND status = 'issued'");
        $stmt->bind_param("i", $issueId);
        $stmt->execute();
        $issue = $stmt->get_result()->fetch_assoc();

        if (!$issue) {
            setMessage("danger", "Please select a valid issued book.");
        } else {
            $today = date("Y-m-d");

            $stmt = $conn->prepare("UPDATE issued_books SET return_date = ?, status = 'returned' WHERE id = ?");
            $stmt->bind_param("si", $today, $issueId);
            $stmt->execute();

            $stmt = $conn->prepare("UPDATE books SET available_copies = available_copies + 1 WHERE id = ?");
            $stmt->bind_param("i", $issue['book_id']);
            $stmt->execute();

            $lateDays = (strtotime($today) - strtotime($issue['due_date'])) / 86400;
            if ($lateDays > 0) {
                $fine = $lateDays * FINE_PER_DAY;
                $stmt = $conn->prepare("INSERT INTO fines (issue_id, amount) VALUES (?, ?)");
                $stmt->bind_param("id", $issueId, $fine);
                $stmt->execute();
                setMessage("warning", "Book returned $lateDays day(s) late. Fine: Rs. $fine");
            } else {
                setMessage("success", "Book returned successfully.");
            }
        }
    }

    header("Location: issue-book.php");
    exit;
}

$students = $conn->query("SELECT id, name FROM users WHERE role = 'student' ORDER BY name");
$availableBooks = $conn->query("SELECT id, title FROM books WHERE available_copies > 0 ORDER BY title");
$issuedList = $conn->query("SELECT ib.id, ib.due_date, b.title, u.name
                            FROM issued_books ib
                            JOIN books b ON ib.book_id = b.id
                            JOIN users u ON ib.user_id = u.id
                            WHERE ib.status = 'issued' ORDER BY ib.due_date");

$pageTitle = "Issue / Return";
$activePage = "issue-book.php";
include "includes/header.php";
?>
  <div class="container mt-4">
    <h3 class="mb-4">Issue / Return Books</h3>
    <ul class="nav nav-tabs mb-3">
      <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#issueTab">Issue Book</button></li>
      <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#returnTab">Return Book</button></li>
    </ul>

    <div class="tab-content">
      <div class="tab-pane fade show active" id="issueTab">
        <div class="card shadow-sm">
          <div class="card-body">
            <form method="POST" action="issue-book.php">
              <input type="hidden" name="action" value="issue">
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">Member</label>
                  <select name="user_id" class="form-select">
                    <option value="">Select member</option>
                    <?php while ($s = $students->fetch_assoc()) { ?>
                      <option value="<?php echo $s['id']; ?>"><?php echo e($s['name']); ?></option>
                    <?php } ?>
                  </select>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Book</label>
                  <select name="book_id" class="form-select">
                    <option value="">Select book</option>
                    <?php while ($b = $availableBooks->fetch_assoc()) { ?>
                      <option value="<?php echo $b['id']; ?>"><?php echo e($b['title']); ?></option>
                    <?php } ?>
                  </select>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Issue Date</label>
                  <input type="date" name="issue_date" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Due Date</label>
                  <input type="date" name="due_date" class="form-control" value="<?php echo date('Y-m-d', strtotime('+14 days')); ?>">
                </div>
              </div>
              <button type="submit" class="btn btn-success">Issue Book</button>
            </form>
          </div>
        </div>
      </div>

      <div class="tab-pane fade" id="returnTab">
        <div class="card shadow-sm">
          <div class="card-body">
            <form method="POST" action="issue-book.php">
              <input type="hidden" name="action" value="return">
              <div class="mb-3">
                <label class="form-label">Issued Book</label>
                <select name="issue_id" class="form-select">
                  <option value="">Select issued book</option>
                  <?php while ($i = $issuedList->fetch_assoc()) { ?>
                    <option value="<?php echo $i['id']; ?>">
                      <?php echo e($i['title'] . " - " . $i['name'] . " (due " . $i['due_date'] . ")"); ?>
                    </option>
                  <?php } ?>
                </select>
              </div>
              <p class="text-muted">Fine is Rs. <?php echo FINE_PER_DAY; ?> for every late day.</p>
              <button type="submit" class="btn btn-primary">Return Book</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
<?php include "includes/footer.php"; ?>
