<?php
require_once "includes/auth.php";
checkAdmin();

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $id = (int) $_POST['id'];

    $stmt = $conn->prepare("SELECT COUNT(*) AS c FROM issued_books WHERE book_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $count = $stmt->get_result()->fetch_assoc()['c'];

    if ($count > 0) {
        setMessage("danger", "This book has issue records, so it cannot be deleted.");
    } else {
        $stmt = $conn->prepare("DELETE FROM books WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        setMessage("success", "Book deleted.");
    }
}

header("Location: books.php");
exit;
