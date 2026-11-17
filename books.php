<?php
require_once "includes/auth.php";
checkLogin();

$search = trim($_GET['q'] ?? "");

if ($search != "") {
    $like = "%" . $search . "%";
    $stmt = $conn->prepare("SELECT * FROM books WHERE title LIKE ? OR author LIKE ? OR category LIKE ? ORDER BY title");
    $stmt->bind_param("sss", $like, $like, $like);
} else {
    $stmt = $conn->prepare("SELECT * FROM books ORDER BY title");
}
$stmt->execute();
$books = $stmt->get_result();

$pageTitle = "Books";
$activePage = "books.php";
include "includes/header.php";
?>
  <div class="container mt-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
      <h3>All Books</h3>
      <?php if (isAdmin()) { ?>
        <a href="add-book.php" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Add Book</a>
      <?php } ?>
    </div>

    <form method="GET" action="books.php" class="row mb-3">
      <div class="col-md-6 mb-2">
        <input type="text" name="q" class="form-control" placeholder="Search by title, author or category..." value="<?php echo e($search); ?>">
      </div>
      <div class="col-md-6 mb-2">
        <button type="submit" class="btn btn-primary">Search</button>
        <a href="books.php" class="btn btn-secondary">Clear</a>
      </div>
    </form>

    <div class="card shadow-sm">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>#</th><th>Title</th><th>Author</th><th>Category</th><th>Copies Left</th><th>Status</th>
              <?php if (isAdmin()) { echo "<th>Actions</th>"; } ?>
            </tr>
          </thead>
          <tbody>
            <?php if ($books->num_rows == 0) { ?>
              <tr><td colspan="7" class="text-center text-muted">No books found.</td></tr>
            <?php } ?>
            <?php while ($book = $books->fetch_assoc()) { ?>
              <tr>
                <td><?php echo $book['id']; ?></td>
                <td><?php echo e($book['title']); ?></td>
                <td><?php echo e($book['author']); ?></td>
                <td><?php echo e($book['category']); ?></td>
                <td><?php echo $book['available_copies'] . " / " . $book['total_copies']; ?></td>
                <td>
                  <?php if ($book['available_copies'] > 0) { ?>
                    <span class="badge bg-success">Available</span>
                  <?php } else { ?>
                    <span class="badge bg-warning text-dark">All Issued</span>
                  <?php } ?>
                </td>
                <?php if (isAdmin()) { ?>
                  <td>
                    <a href="edit-book.php?id=<?php echo $book['id']; ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                    <form method="POST" action="delete-book.php" class="d-inline" onsubmit="return confirmDelete('this book')">
                      <input type="hidden" name="id" value="<?php echo $book['id']; ?>">
                      <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                    </form>
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
