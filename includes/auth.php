<?php

session_start();
require_once __DIR__ . "/db.php";

define("FINE_PER_DAY", 10); // Rs10 fine for every late day

function e($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

function setMessage($type, $text) {
    $_SESSION['msg_type'] = $type;
    $_SESSION['msg_text'] = $text;
}

function showMessage() {
    if (isset($_SESSION['msg_text'])) {
        echo '<div class="container mt-3"><div class="alert alert-' . e($_SESSION['msg_type']) . '">'
            . e($_SESSION['msg_text']) . '</div></div>';
        unset($_SESSION['msg_type']);
        unset($_SESSION['msg_text']);
    }
}

//user must be logged in other wise go to login page
function checkLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
}

//only admin can open page
function checkAdmin() {
    checkLogin();
    if ($_SESSION['role'] != 'admin') {
        setMessage("danger", "You are not allowed to open that page.");
        header("Location: dashboard.php");
        exit;
    }
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] == 'admin';
}
