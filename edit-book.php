<?php
require_once "includes/auth.php";
checkAdmin();

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

$stmt = $conn->prepare("SELECT * FROM books WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$book = $stmt->get_result()->fetch_assoc();

if (!$book) {
    setMessage("danger", "Book not found.");
    header("Location: books.php");
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    $isbn = trim($_POST['isbn']);
    $category = trim($_POST['category']);
    $copies = (int) $_POST['copies'];
    $description = trim($_POST['description']);

    $givenOut = $book['total_copies'] - $book['available_copies'];

    if ($title == "") { $errors[] = "Title is required."; }
    if ($author == "") { $errors[] = "Author is required."; }
    if ($category == "") { $errors[] = "Please select a category."; }
    if ($copies < $givenOut) { $errors[] = "Copies cannot be less than $givenOut because those are issued right now."; }

    if (count($errors) == 0) {
        $available = $copies - $givenOut;
        $stmt = $conn->prepare("UPDATE books SET title = ?, author = ?, isbn = ?, category = ?, total_copies = ?, available_copies = ?, description = ? WHERE id = ?");
        $stmt->bind_param("ssssiisi", $title, $author, $isbn, $category, $copies, $available, $description, $id);
        $stmt->execute();

        setMessage("success", "Book updated successfully.");
        header("Location: books.php");
        exit;
    }

    $book['title'] = $title;
    $book['author'] = $author;
    $book['isbn'] = $isbn;
    $book['category'] = $category;
    $book['total_copies'] = $copies;
    $book['description'] = $description;
}

$pageTitle = "Edit Book";
$activePage = "books.php";
include "includes/header.php";
?>
  <div class="container mt-4">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="books.php">Books</a></li>
        <li class="breadcrumb-item active">Edit Book</li>
      </ol>
    </nav>

    <?php if (count($errors) > 0) { ?>
      <div class="alert alert-danger">
        <?php foreach ($errors as $error) { echo e($error) . "<br>"; } ?>
      </div>
    <?php } ?>

    <div class="card shadow-sm">
      <div class="card-header bg-white"><strong>Edit Book</strong></div>
      <div class="card-body">
        <form method="POST" action="edit-book.php">
          <input type="hidden" name="id" value="<?php echo $book['id']; ?>">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Book Title</label>
              <input type="text" name="title" class="form-control" value="<?php echo e($book['title']); ?>">
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Author</label>
              <input type="text" name="author" class="form-control" value="<?php echo e($book['author']); ?>">
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">ISBN</label>
              <input type="text" name="isbn" class="form-control" value="<?php echo e($book['isbn']); ?>">
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">Category</label>
              <select name="category" class="form-select">
                <option value="">Select category</option>
                <?php foreach (["Computer Science", "Mathematics", "Web", "Novels"] as $cat) { ?>
                  <option <?php if ($book['category'] == $cat) { echo "selected"; } ?>><?php echo $cat; ?></option>
                <?php } ?>
              </select>
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">Number of Copies</label>
              <input type="number" name="copies" class="form-control" min="1" value="<?php echo e($book['total_copies']); ?>">
            </div>
            <div class="col-12 mb-3">
              <label class="form-label">Description</label>
              <textarea name="description" class="form-control" rows="3"><?php echo e($book['description']); ?></textarea>
            </div>
          </div>
          <button type="submit" class="btn btn-primary">Update Book</button>
          <a href="books.php" class="btn btn-secondary">Cancel</a>
        </form>
      </div>
    </div>
  </div>
<?php include "includes/footer.php"; ?>
