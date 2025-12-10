<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bd_tienda";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) { die("Error de conexión: ".$conn->connect_error); }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.html");
    exit();
}

$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$pass = isset($_POST['password']) ? $_POST['password'] : '';

$stmt = $conn->prepare("SELECT id, correo, `contraseña`, nombre FROM usuarios WHERE correo = ? LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows === 1) {
    $user = $result->fetch_assoc();
    if (password_verify($pass, $user['contraseña'])) {
        $_SESSION['usuario'] = $user['nombre'];
        $_SESSION['correo'] = $user['correo'];
        $_SESSION['email'] = $user['correo'];
        header("Location: index.php");
        exit();
    }
}

echo "<script>alert('Correo o contraseña incorrectos');window.location.href='login.html';</script>";
exit();
?>