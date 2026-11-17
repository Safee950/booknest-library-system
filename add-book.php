<?php
require_once "includes/auth.php";
checkAdmin();

$errors = [];
$title = $author = $isbn = $category = $description = "";
$copies = 1;

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    $isbn = trim($_POST['isbn']);
    $category = trim($_POST['category']);
    $copies = (int) $_POST['copies'];
    $description = trim($_POST['description']);

    if ($title == "") { $errors[] = "Title is required."; }
    if ($author == "") { $errors[] = "Author is required."; }
    if ($category == "") { $errors[] = "Please select a category."; }
    if ($copies < 1) { $errors[] = "Copies must be at least 1."; }

    if (count($errors) == 0) {
        $stmt = $conn->prepare("INSERT INTO books (title, author, isbn, category, total_copies, available_copies, description) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssiis", $title, $author, $isbn, $category, $copies, $copies, $description);
        $stmt->execute();

        setMessage("success", "Book added successfully.");
        header("Location: books.php");
        exit;
    }
}

$pageTitle = "Add Book";
$activePage = "add-book.php";
include "includes/header.php";
?>
  <div class="container mt-4">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="books.php">Books</a></li>
        <li class="breadcrumb-item active">Add Book</li>
      </ol>
    </nav>

    <?php if (count($errors) > 0) { ?>
      <div class="alert alert-danger">
        <?php foreach ($errors as $error) { echo e($error) . "<br>"; } ?>
      </div>
    <?php } ?>

    <div class="card shadow-sm">
      <div class="card-header bg-white"><strong>Add New Book</strong></div>
      <div class="card-body">
        <form method="POST" action="add-book.php">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Book Title</label>
              <input type="text" name="title" class="form-control" value="<?php echo e($title); ?>">
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Author</label>
              <input type="text" name="author" class="form-control" value="<?php echo e($author); ?>">
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">ISBN</label>
              <input type="text" name="isbn" class="form-control" value="<?php echo e($isbn); ?>">
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">Category</label>
              <select name="category" class="form-select">
                <option value="">Select category</option>
                <?php foreach (["Computer Science", "Mathematics", "Web", "Novels"] as $cat) { ?>
                  <option <?php if ($category == $cat) { echo "selected"; } ?>><?php echo $cat; ?></option>
                <?php } ?>
              </select>
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">Number of Copies</label>
              <input type="number" name="copies" class="form-control" min="1" value="<?php echo e($copies); ?>">
            </div>
            <div class="col-12 mb-3">
              <label class="form-label">Description</label>
              <textarea name="description" class="form-control" rows="3"><?php echo e($description); ?></textarea>
            </div>
          </div>
          <button type="submit" class="btn btn-primary">Save Book</button>
          <a href="books.php" class="btn btn-secondary">Cancel</a>
        </form>
      </div>
    </div>
  </div>
<?php include "includes/footer.php"; ?>
