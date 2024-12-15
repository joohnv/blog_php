<?php
// Iniciar sesión para redirigir después del registro
session_start();

// Incluir el archivo de autenticación y el modelo de Usuario
require_once __DIR__ . '/../../utils/Auth.php';
require_once __DIR__ . '/../../models/user.php';
require_once __DIR__ . '/../../config/database.php';

use config\Database;
use models\User;
use utils\Auth;

// Conectar a la base de datos
$database = new Database();
$db = $database->getConnection();

// Crear una instancia del modelo de Usuario
$userModel = new User($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener datos
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $role = 'user';

    if (empty($email) || empty($username) || empty($password) || empty($confirm_password)) {
        $error = "Todos los campos son obligatorios.";
    } elseif ($password !== $confirm_password) {
        $error = "Las contraseñas no coinciden.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Validar hash generado
        if ($hashed_password === false) {
            $error = "Error al procesar la contraseña.";
        } else {
            $userModel->username = $username;
            $userModel->email = $email;
            $userModel->password = $hashed_password;
            $userModel->role = $role;

            try {
                $userModel->create();
                header("Location: login.php");
                exit;
            } catch (Exception $e) {
                $error = "Error: " . $e->getMessage();
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
    <title>Registrarse</title>
    <link rel="stylesheet" href="../../styles/form.css">
</head>
<body>
    <div class="container">
        <h1>Registrarse</h1>

        <?php if (isset($error)): ?>
            <div class="error-message">
                <p><?php echo htmlspecialchars($error); ?></p>
            </div>
        <?php endif; ?>

        <form action="register.php" method="POST">
            <div class="form-group">
                <label for="username">Nombre de usuario:</label>
                <input type="text" name="username" id="username" required>
            </div>
            <div class="form-group">
                <label for="email">Correo electrónico:</label>
                <input type="email" name="email" id="email" required>
            </div>
            <div class="form-group">
                <label for="password">Contraseña:</label>
                <input type="password" name="password" id="password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirmar contraseña:</label>
                <input type="password" name="confirm_password" id="confirm_password" required>
            </div>
            <button type="submit">Registrarse</button>
        </form>

        <p>¿Ya tienes cuenta? <a href="login.php">Inicia sesión aquí</a>.</p>
    </div>
</body>
</html>
