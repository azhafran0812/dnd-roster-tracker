<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    $url = "";
    if ($action === 'register') {
        $url = "https://identitytoolkit.googleapis.com/v1/accounts:signUp?key=" . FIREBASE_API_KEY;
    } elseif ($action === 'login') {
        $url = "https://identitytoolkit.googleapis.com/v1/accounts:signInWithPassword?key=" . FIREBASE_API_KEY;
    }

    $data = [
        'email' => $email,
        'password' => $password,
        'returnSecureToken' => true
    ];

    $response = firebase_request($url, 'POST', $data);

    if (isset($response['error'])) {
        $_SESSION['error'] = str_replace('_', ' ', $response['error']['message']);
    } else {
        $_SESSION['idToken'] = $response['idToken'];
        $_SESSION['localId'] = $response['localId'];
        $_SESSION['email'] = $response['email'];
        
        if ($action === 'register') {
            $_SESSION['message'] = "Registration successful! Welcome to the realm.";
        }
    }
    
    header("Location: index.php");
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header("Location: index.php");
    exit;
}
?>