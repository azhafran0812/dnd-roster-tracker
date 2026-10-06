<?php

session_start();

if (isset($_GET['email'])) {
    
    echo "<script>
            alert('Email is verified! Please log-in.');
            window.location.href = 'index.php';
          </script>";
} else {
    echo "Akses tidak valid.";
}
?>