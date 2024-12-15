<?php
session_start();

$message = '';  // Inicializar el mensaje

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/user.php';

use config\Database;
use models\User;

// Crear instancia de la base de datos y del modelo User
$database = new Database();
$db = $database->getConnection();
$userModel = new User($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $message = "Por favor, ingresa tu correo y contraseña.";
    } else {
        $user = $userModel->findByEmail($email);

        if ($user) {
            if (password_verify($password, $user['password'])) {
                // Guardar los datos del usuario en la sesión
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['username'] = $user['username'];  // Guardamos el username en la sesión

                // Redirección por rol
                switch (strtolower($user['role'])) {
                    case 'admin':
                        header("Location: ../home/dashboard.php");
                        break;
                    case 'escritor':
                        header("Location: ../home/index.php");
                        break;
                    case 'suscriptor':
                        header("Location: ../home/suscriptor.php");
                        break;
                    default:
                        $message = "Rol no válido.";
                }
                exit;
            } else {
                $message = "Correo o contraseña incorrectos.";
            }
        } else {
            $message = "Correo o contraseña incorrectos.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión</title>
    <link rel="stylesheet" href="../../styles/form.css">
</head>
<body>
    <div class="container">
        <h1>Iniciar sesión</h1>
        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="email">Correo electrónico:</label>
                <input type="email" name="email" id="email" required>
            </div>
            <div class="form-group">
                <label for="password">Contraseña:</label>
                <input type="password" name="password" id="password" required>
            </div>
            <button type="submit">Iniciar sesión</button>
        </form>

        <?php if (!empty($message)): ?>
            <div class="message">
                <p><?php echo htmlspecialchars($message); ?></p>
            </div>
        <?php endif; ?>

        <p>¿No tienes cuenta? <a href="register.php">Regístrate aquí</a>.</p>
    </div>
</body>
</html>
