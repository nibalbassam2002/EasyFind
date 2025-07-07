<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = htmlspecialchars($_POST['id']);
    $userName = htmlspecialchars($_POST['name']);

    setcookie('user_id', $userId, time() + (86400), "/"); 


    $_SESSION['user_id'] = $userId;

    $data = "ID: $userId, Name: $userName\n";

    $filePath = 'users.txt';

    try {
        
        if (file_put_contents($filePath, $data, FILE_APPEND | LOCK_EX) === false) {
            throw new Exception('Could not write to file.');
        }
        echo "Data saved successfully.";
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "Invalid request.";
}