<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: login.html');
    exit();
}

$host = "localhost";
$user = "root";
$pass = "";
$db = "bd_tienda";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
  die("Error de conexión: " . $conn->connect_error);
}

// Obtener datos del usuario
$correo = $_SESSION['correo'] ?? $_SESSION['email'] ?? '';
if (empty($correo)) {
    header('Location: login.html');
    exit();
}
$stmt = $conn->prepare("SELECT id, nombre, correo, avatar FROM usuarios WHERE correo = ?");
$stmt->bind_param("s", $correo);
$stmt->execute();
$result = $stmt->get_result();
$usuarioData = $result->fetch_assoc();

$error = "";
$success = "";

// Procesar cambios de perfil
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'cambiar_avatar') {
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
                        
                        // Actualizar en la BD
                        $updateStmt = $conn->prepare("UPDATE usuarios SET avatar = ? WHERE correo = ?");
                        $updateStmt->bind_param("ss", $avatarPath, $correo);
                        
                        if ($updateStmt->execute()) {
                            $usuarioData['avatar'] = $avatarPath;
                            $success = "Foto de perfil actualizada correctamente.";
                        } else {
                            $error = "Error al guardar la foto en la base de datos.";
                        }
                    } else {
                        $error = 'No se pudo guardar la imagen de perfil.';
                    }
                }
            }
        } else {
            $error = 'Por favor selecciona una imagen.';
        }
    }

    if ($action === 'cambiar_contraseña') {
        $contraseña_actual = $_POST['contraseña_actual'] ?? '';
        $contraseña_nueva = $_POST['contraseña_nueva'] ?? '';
        $confirmar_contraseña = $_POST['confirmar_contraseña'] ?? '';

        if (empty($contraseña_actual) || empty($contraseña_nueva) || empty($confirmar_contraseña)) {
            $error = 'Por favor completa todos los campos.';
        } elseif ($contraseña_nueva !== $confirmar_contraseña) {
            $error = 'Las nuevas contraseñas no coinciden.';
        } elseif (strlen($contraseña_nueva) < 6) {
            $error = 'La nueva contraseña debe tener al menos 6 caracteres.';
        } else {
            // Verificar contraseña actual
            $checkStmt = $conn->prepare("SELECT `contraseña` FROM usuarios WHERE correo = ?");
            $checkStmt->bind_param("s", $correo);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            $userData = $checkResult->fetch_assoc();

            if (password_verify($contraseña_actual, $userData['contraseña'])) {
                $newHash = password_hash($contraseña_nueva, PASSWORD_DEFAULT);
                $updateStmt = $conn->prepare("UPDATE usuarios SET `contraseña` = ? WHERE correo = ?");
                $updateStmt->bind_param("ss", $newHash, $correo);

                if ($updateStmt->execute()) {
                    $success = "Contraseña actualizada correctamente.";
                } else {
                    $error = "Error al actualizar la contraseña.";
                }
            } else {
                $error = "Contraseña actual incorrecta.";
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
    <title>Mi Perfil - Mi Tienda</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .header-top {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: rgba(0,0,0,0.8);
            color: #fff;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 1000;
            backdrop-filter: blur(4px);
        }

        .header-top a {
            color: #fff;
            text-decoration: none;
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid rgba(255,255,255,0.12);
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .header-top a:hover {
            background: rgba(255,255,255,0.1);
        }

        .container {
            max-width: 900px;
            margin: 80px auto 40px;
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 15px 45px rgba(0,0,0,0.3);
            overflow: hidden;
        }

        .profile-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            padding: 40px;
            text-align: center;
        }

        .avatar {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            border: 4px solid #fff;
            overflow: hidden;
        }

        .avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .avatar-placeholder {
            font-size: 60px;
        }

        .profile-header h1 {
            font-size: 32px;
            margin-bottom: 10px;
        }

        .profile-header p {
            font-size: 16px;
            opacity: 0.9;
        }

        .profile-content {
            padding: 40px;
        }

        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            border-bottom: 2px solid #e2e8f0;
        }

        .tab-btn {
            padding: 12px 24px;
            border: none;
            background: none;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            color: #718096;
            border-bottom: 3px solid transparent;
            transition: all 0.3s ease;
        }

        .tab-btn.active {
            color: #667eea;
            border-bottom-color: #667eea;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            color: #4a5568;
            margin-bottom: 8px;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .btn-secondary {
            background: #cbd5e0;
            color: #2d3748;
        }

        .btn-secondary:hover {
            background: #a0aec0;
        }

        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .message.error {
            background: #fed7d7;
            color: #c53030;
            border: 1px solid #fc8181;
        }

        .message.success {
            background: #c6f6d5;
            color: #22543d;
            border: 1px solid #9ae6b4;
        }

        .profile-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        .info-item {
            padding: 15px;
            background: #f7fafc;
            border-radius: 8px;
        }

        .info-item label {
            font-size: 12px;
            color: #718096;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
            display: block;
        }

        .info-item p {
            font-size: 16px;
            color: #2d3748;
            font-weight: 500;
        }

        .file-input-wrapper {
            position: relative;
            display: inline-block;
            width: 100%;
        }

        .file-input-wrapper input[type="file"] {
            display: none;
        }

        .file-input-label {
            display: block;
            padding: 12px 15px;
            background: #f7fafc;
            border: 2px dashed #cbd5e0;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .file-input-label:hover {
            border-color: #667eea;
            background: #edf2f7;
        }

        .preview-img {
            max-width: 200px;
            margin-top: 15px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        @media (max-width: 600px) {
            .profile-info {
                grid-template-columns: 1fr;
            }

            .tabs {
                flex-wrap: wrap;
            }

            .profile-header {
                padding: 20px;
            }

            .avatar {
                width: 120px;
                height: 120px;
            }

            .profile-header h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="header-top">
        <div style="font-weight:600;font-size:16px;">
            <i class="fas fa-user-circle"></i> Mi Perfil
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="index.php"><i class="fas fa-home"></i> Inicio</a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</a>
        </div>
    </div>

    <div class="container">
        <!-- Profile Header -->
        <div class="profile-header">
            <div class="avatar">
                <?php if (!empty($usuarioData['avatar']) && file_exists($usuarioData['avatar'])): ?>
                    <img src="<?php echo htmlspecialchars($usuarioData['avatar']); ?>" alt="Avatar de <?php echo htmlspecialchars($usuarioData['nombre']); ?>">
                <?php else: ?>
                    <div class="avatar-placeholder">
                        <i class="fas fa-user"></i>
                    </div>
                <?php endif; ?>
            </div>
            <h1><?php echo htmlspecialchars($usuarioData['nombre']); ?></h1>
            <p><?php echo htmlspecialchars($usuarioData['correo']); ?></p>
        </div>

        <!-- Profile Content -->
        <div class="profile-content">
            <!-- Mensajes -->
            <?php if (!empty($error)): ?>
                <div class="message error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="message success">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <!-- Tabs -->
            <div class="tabs">
                <button class="tab-btn active" onclick="cambiarTab('info')">
                    <i class="fas fa-user"></i> Información
                </button>
                <button class="tab-btn" onclick="cambiarTab('avatar')">
                    <i class="fas fa-image"></i> Foto de Perfil
                </button>
                <button class="tab-btn" onclick="cambiarTab('contraseña')">
                    <i class="fas fa-lock"></i> Contraseña
                </button>
            </div>

            <!-- Tab: Información -->
            <div id="info" class="tab-content active">
                <div class="profile-info">
                    <div class="info-item">
                        <label>Nombre Completo</label>
                        <p><?php echo htmlspecialchars($usuarioData['nombre']); ?></p>
                    </div>
                    <div class="info-item">
                        <label>Correo Electrónico</label>
                        <p><?php echo htmlspecialchars($usuarioData['correo']); ?></p>
                    </div>
                </div>
                <p style="color: #718096; font-size: 14px;">
                    <i class="fas fa-info-circle"></i> Puedes cambiar tu foto de perfil o contraseña usando las pestañas anteriores.
                </p>
            </div>

            <!-- Tab: Avatar -->
            <div id="avatar" class="tab-content">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="cambiar_avatar">
                    
                    <div class="form-group">
                        <label>Seleccionar Nueva Foto de Perfil</label>
                        <div class="file-input-wrapper">
                            <input type="file" id="avatar-input" name="avatar" accept="image/*" onchange="previewAvatar()">
                            <label for="avatar-input" class="file-input-label">
                                <i class="fas fa-cloud-upload-alt"></i> Haz clic para seleccionar una imagen o arrastra aquí
                            </label>
                        </div>
                        <img id="preview" class="preview-img" style="display: none;">
                    </div>

                    <div style="display: flex; gap: 10px;">
                        <button type="submit" class="btn">
                            <i class="fas fa-save"></i> Guardar Foto
                        </button>
                        <button type="reset" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                    </div>
                </form>
            </div>

            <!-- Tab: Contraseña -->
            <div id="contraseña" class="tab-content">
                <form method="POST">
                    <input type="hidden" name="action" value="cambiar_contraseña">

                    <div class="form-group">
                        <label for="contraseña_actual">Contraseña Actual</label>
                        <input type="password" id="contraseña_actual" name="contraseña_actual" required placeholder="Ingresa tu contraseña actual">
                    </div>

                    <div class="form-group">
                        <label for="contraseña_nueva">Nueva Contraseña</label>
                        <input type="password" id="contraseña_nueva" name="contraseña_nueva" required placeholder="Ingresa tu nueva contraseña (mínimo 6 caracteres)">
                    </div>

                    <div class="form-group">
                        <label for="confirmar_contraseña">Confirmar Nueva Contraseña</label>
                        <input type="password" id="confirmar_contraseña" name="confirmar_contraseña" required placeholder="Repite tu nueva contraseña">
                    </div>

                    <div style="display: flex; gap: 10px;">
                        <button type="submit" class="btn">
                            <i class="fas fa-lock"></i> Cambiar Contraseña
                        </button>
                        <button type="reset" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function cambiarTab(tabName) {
            // Ocultar todos los tabs
            const tabs = document.querySelectorAll('.tab-content');
            tabs.forEach(tab => tab.classList.remove('active'));

            // Desactivar todos los botones
            const botones = document.querySelectorAll('.tab-btn');
            botones.forEach(btn => btn.classList.remove('active'));

            // Activar tab seleccionado
            document.getElementById(tabName).classList.add('active');
            event.target.classList.add('active');
        }

        function previewAvatar() {
            const file = document.getElementById('avatar-input').files[0];
            const preview = document.getElementById('preview');

            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                preview.style.display = 'none';
            }
        }

        // Drag and drop
        const fileInputWrapper = document.querySelector('.file-input-wrapper');
        const fileInput = document.getElementById('avatar-input');

        if (fileInputWrapper) {
            fileInputWrapper.addEventListener('dragover', (e) => {
                e.preventDefault();
                fileInputWrapper.querySelector('.file-input-label').style.borderColor = '#667eea';
                fileInputWrapper.querySelector('.file-input-label').style.background = '#edf2f7';
            });

            fileInputWrapper.addEventListener('dragleave', () => {
                fileInputWrapper.querySelector('.file-input-label').style.borderColor = '#cbd5e0';
                fileInputWrapper.querySelector('.file-input-label').style.background = '#f7fafc';
            });

            fileInputWrapper.addEventListener('drop', (e) => {
                e.preventDefault();
                fileInputWrapper.querySelector('.file-input-label').style.borderColor = '#cbd5e0';
                fileInputWrapper.querySelector('.file-input-label').style.background = '#f7fafc';
                
                if (e.dataTransfer.files.length) {
                    fileInput.files = e.dataTransfer.files;
                    previewAvatar();
                }
            });
        }
    </script>
</body>
</html>
