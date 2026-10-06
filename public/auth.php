<?php
require 'config.php';

$appUrl = 'https://dnd-roster-tracker.onrender.com/index.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    if ($action === 'register') {
        $url = "https://identitytoolkit.googleapis.com/v1/accounts:signUp?key=" . FIREBASE_API_KEY;
        $data = ['email' => $email, 'password' => $password, 'returnSecureToken' => true];
        $response = firebase_request($url, 'POST', $data);

        if (isset($response['error'])) {
            $_SESSION['error'] = str_replace('_', ' ', $response['error']['message']);
        } else {
            $idToken = $response['idToken'];
            $uid = $response['localId'];

            
            $verifyUrl = "https://identitytoolkit.googleapis.com/v1/accounts:sendOobCode?key=" . FIREBASE_API_KEY;
            $verifyData = [
                'requestType' => 'VERIFY_EMAIL',
                'idToken' => $idToken,
                'continueUrl' => $appUrl . "/verify.php?uid=" . $uid . "&email=" . urlencode($email)
            ];
            firebase_request($verifyUrl, 'POST', $verifyData);

            
            $dbUrl = FIREBASE_DB_URL . "users/" . $uid . ".json?auth=" . $idToken;
            $userData = [
                'email' => $email,
                'is_verified' => false
            ];
            firebase_request($dbUrl, 'PUT', $userData);

            $_SESSION['message'] = "Registration successful! Check your email to verify.";
        }
    } elseif ($action === 'login') {
        $url = "https://identitytoolkit.googleapis.com/v1/accounts:signInWithPassword?key=" . FIREBASE_API_KEY;
        $data = ['email' => $email, 'password' => $password, 'returnSecureToken' => true];
        $response = firebase_request($url, 'POST', $data);

        if (isset($response['error'])) {
            $errMsg = $response['error']['message'];

            
            if ($errMsg === 'EMAIL_NOT_FOUND') {
                $_SESSION['error'] = "Akun tidak ditemukan di guild.";
            } elseif ($errMsg === 'INVALID_PASSWORD' || $errMsg === 'INVALID_LOGIN_CREDENTIALS') {
                $_SESSION['error'] = "Password salah.";
            } else {
                $_SESSION['error'] = "Login Error: " . $errMsg;
            }
        } else {
            $idToken = $response['idToken'];
            $uid = $response['localId'];

            
            $lookupUrl = "https://identitytoolkit.googleapis.com/v1/accounts:lookup?key=" . FIREBASE_API_KEY;
            $lookupResponse = firebase_request($lookupUrl, 'POST', ['idToken' => $idToken]);

            $isVerified = false;
            if (isset($lookupResponse['users'][0]['emailVerified'])) {
                $isVerified = $lookupResponse['users'][0]['emailVerified'];
            }

            if ($isVerified) {
                
                $dbUrl = FIREBASE_DB_URL . "users/" . $uid . ".json?auth=" . $idToken;
                firebase_request($dbUrl, 'PATCH', ['is_verified' => true]);

                
                $_SESSION['idToken'] = $idToken;
                $_SESSION['localId'] = $uid;
                $_SESSION['email'] = $response['email'];
            } else {
                
                $_SESSION['error'] = "Email belum diverifikasi. Silakan cek kotak masuk atau folder spam Anda.";
            }
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