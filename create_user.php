<?php
// create_user.php
$host = "localhost";
$user = "root";
$pass = "";
$db = "bd_tienda";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
  die("Error de conexión: " . $conn->connect_error);
}

session_start();

// Estructura mínima de la tabla usuarios
// id INT AUTO_INCREMENT PRIMARY KEY
$colResId = @$conn->query("SHOW COLUMNS FROM usuarios LIKE 'id'");
if (!$colResId || $colResId->num_rows === 0) {
  @$conn->query("ALTER TABLE usuarios ADD COLUMN id INT AUTO_INCREMENT PRIMARY KEY FIRST");
}
// nombre VARCHAR(255)
$colResNombre = @$conn->query("SHOW COLUMNS FROM usuarios LIKE 'nombre'");
if (!$colResNombre || $colResNombre->num_rows === 0) {
  @$conn->query("ALTER TABLE usuarios ADD COLUMN nombre VARCHAR(255) AFTER id");
}
// correo VARCHAR(255) UNIQUE
$colResCorreo = @$conn->query("SHOW COLUMNS FROM usuarios LIKE 'correo'");
if (!$colResCorreo || $colResCorreo->num_rows === 0) {
  @$conn->query("ALTER TABLE usuarios ADD COLUMN correo VARCHAR(255) UNIQUE AFTER nombre");
}
// contraseña VARCHAR(255)
$colResCon = @$conn->query("SHOW COLUMNS FROM usuarios LIKE 'contraseña'");
if (!$colResCon || $colResCon->num_rows === 0) {
  @$conn->query("ALTER TABLE usuarios ADD COLUMN `contraseña` VARCHAR(255) AFTER correo");
}
// avatar VARCHAR(255) NULL
$colResAvatar = @$conn->query("SHOW COLUMNS FROM usuarios LIKE 'avatar'");
if (!$colResAvatar || $colResAvatar->num_rows === 0) {
  @$conn->query("ALTER TABLE usuarios ADD COLUMN avatar VARCHAR(255) NULL AFTER `contraseña`");
} 

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $nombre = trim($_POST["nombre"] ?? '');
  $email = trim($_POST["email"] ?? '');
  $password = $_POST["password"] ?? '';

  // Validaciones básicas
  if (empty($nombre) || empty($email) || empty($password)) {
    $error = "Por favor completa todos los campos.";
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = "Email no válido.";
  } elseif (strlen($password) < 6) {
    $error = "La contraseña debe tener al menos 6 caracteres.";
  } else {
    // Manejo de subida de avatar (opcional)
    $avatarPath = null;
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
      if ($_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
        $error = 'Error al subir el archivo.';
      } else {
        $tmp = $_FILES['avatar']['tmp_name'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $ftype = finfo_file($finfo, $tmp);
        finfo_close($finfo);
        $allowed = ['image/jpeg','image/png','image/webp','image/gif'];
        if (!in_array($ftype, $allowed)) {
          $error = 'Tipo de archivo no permitido. Usa JPG, PNG, WEBP o GIF.';
        } elseif ($_FILES['avatar']['size'] > 2 * 1024 * 1024) {
          $error = 'El archivo excede el límite de 2MB.';
        } else {
          $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
          $safe = preg_replace('/[^a-z0-9_-]/i', '', pathinfo($_FILES['avatar']['name'], PATHINFO_FILENAME));
          $newName = $safe . '-' . time() . '.' . $ext;
          $uploadDir = __DIR__ . '/imagenes/avatars';
          if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
          if (move_uploaded_file($tmp, $uploadDir . '/' . $newName)) {
            $avatarPath = 'imagenes/avatars/' . $newName;
          } else {
            $error = 'No se pudo guardar la imagen de perfil.';
          }
        }
      }
    }

    if (empty($error)) {
      // Verificar si el correo ya existe
      $check = $conn->prepare("SELECT id FROM usuarios WHERE correo = ?");
      $check->bind_param("s", $email);
      $check->execute();
      $result = $check->get_result();

      if ($result && $result->num_rows > 0) {
        $error = "Este correo ya está registrado.";
      } else {
        // Crear nuevo usuario
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        
        // Insertar usuario con avatar si se subió uno
        if ($avatarPath) {
          $stmt = $conn->prepare("INSERT INTO usuarios (nombre, correo, `contraseña`, avatar) VALUES (?, ?, ?, ?)");
          $stmt->bind_param("ssss", $nombre, $email, $hashed, $avatarPath);
        } else {
          $stmt = $conn->prepare("INSERT INTO usuarios (nombre, correo, `contraseña`) VALUES (?, ?, ?)");
          $stmt->bind_param("sss", $nombre, $email, $hashed);
        }

        if ($stmt->execute()) {
          // Iniciar sesión automáticamente
          $_SESSION['usuario'] = $nombre;
          $_SESSION['correo'] = $email;
          $_SESSION['email'] = $email;
          header("Location: index.php");
          exit();
        } else {
          $error = "Error al crear el usuario.";
        }
      }
    }
  }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Crear Usuario - Mi Tienda</title>
<style>
  body {
    font-family: 'Segoe UI', Roboto, Arial, sans-serif;
    background: radial-gradient(circle at top left, #0f2027, #203a43, #2c5364);
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    margin: 0;
    color: #fff;
    overflow: hidden;
  }

  .container {
    background: rgba(20, 20, 20, 0.85);
    border: 1px solid rgba(255,255,255,0.15);
    border-radius: 20px;
    padding: 40px 50px;
    box-shadow: 0 0 25px rgba(0, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    width: 360px;
    text-align: center;
  }

  h2 {
    margin-bottom: 20px;
    font-size: 26px;
    color: #00e0ff;
  }

  label {
    display: block;
    text-align: left;
    margin-bottom: 12px;
    font-weight: 500;
    font-size: 14px;
    color: #d1d1d1;
  }

  input {
    width: 100%;
    padding: 10px 12px;
    border: none;
    border-radius: 8px;
    margin-top: 4px;
    margin-bottom: 16px;
    background: rgba(255,255,255,0.1);
    color: #fff;
    font-size: 15px;
    outline: none;
    transition: background 0.3s;
  }

  input:focus {
    background: rgba(255,255,255,0.2);
  }

  button {
    background: linear-gradient(135deg, #00e0ff, #0078ff);
    border: none;
    padding: 12px 18px;
    border-radius: 10px;
    color: #fff;
    font-weight: bold;
    font-size: 15px;
    cursor: pointer;
    width: 100%;
    transition: transform 0.2s, box-shadow 0.3s;
  }

  button:hover {
    transform: translateY(-2px);
    box-shadow: 0 0 15px rgba(0, 224, 255, 0.5);
  }

  p {
    margin-top: 20px;
  }

  a {
    color: #00e0ff;
    text-decoration: none;
    transition: color 0.2s;
  }

  a:hover {
    color: #fff;
  }

  .msg {
    font-size: 14px;
    margin-bottom: 15px;
  }

  .msg.error { color: #ff8080; }
  .msg.success { color: #80ffb3; }
</style>
</head>
<body>
  <div class="container">
    <h2>Crear Usuario</h2>

    <?php if (!empty($error)): ?>
      <p class="msg error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
      <p class="msg success"><?php echo htmlspecialchars($success); ?></p>
    <?php endif; ?>

    <form method="POST" action="create_user.php" enctype="multipart/form-data">
      <label>Nombre:
        <input type="text" name="nombre" required>
      </label>
      <label>Correo:
        <input type="email" name="email" required>
      </label>
      <label>Contraseña:
        <input type="password" name="password" required>
      </label>
      <label>Foto de perfil (opcional):
        <input type="file" name="avatar" accept="image/*">
      </label>
      <button type="submit">Crear usuario</button>
    </form>

    <p><a href="login.html">Volver al login</a></p>
  </div>
</body>
</html>
