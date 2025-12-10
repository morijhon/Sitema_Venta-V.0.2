<?php
session_start();
if (php_sapi_name() !== 'cli' && !empty($_SERVER['REQUEST_URI']) && basename($_SERVER['REQUEST_URI']) === 'session-check.php') {
    $response = array();
    if (isset($_SESSION['usuario'])) { $response['loggedIn'] = true; $response['usuario'] = $_SESSION['usuario']; }
    else { $response['loggedIn'] = false; }
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}
function require_login() {
    if (!isset($_SESSION['usuario'])) {
        header('Location: ../e-comerce/login.html');
        exit();
    }
}
?>