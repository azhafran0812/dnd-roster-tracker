<?php
require 'config.php';

if (!isset($_SESSION['idToken']) || !isset($_SESSION['localId'])) {
    header("Location: index.php");
    exit;
}

$token = $_SESSION['idToken'];
$uid = $_SESSION['localId']; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $data = [
        'name' => trim($_POST['name']),
        'race' => trim($_POST['race']),
        'charClass' => trim($_POST['charClass']),
        'level' => (int) $_POST['level'],
        'status' => $_POST['status']
    ];

    if ($id) {
        
        $url = FIREBASE_DB_URL . "characters/" . $uid . "/" . $id . ".json?auth=" . $token;
        firebase_request($url, 'PATCH', $data);
    } else {
        
        $url = FIREBASE_DB_URL . "characters/" . $uid . ".json?auth=" . $token;
        firebase_request($url, 'POST', $data);
    }
    header("Location: index.php");
    exit;
}


if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    
    $url = FIREBASE_DB_URL . "characters/" . $uid . "/" . $id . ".json?auth=" . $token;
    firebase_request($url, 'DELETE');
    header("Location: index.php");
    exit;
}
?>