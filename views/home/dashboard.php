<?php
// Iniciar sesión
session_start();

// Verificar si el usuario ha iniciado sesión y si tiene el rol de 'admin'
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header('Location: ../user/login.php');
    exit();
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/user.php';

use config\Database;
use models\User;

$database = new Database();
$db = $database->getConnection();
$userModel = new User($db);

// Variables para manejar el estado de la acción
$action = isset($_GET['action']) ? $_GET['action'] : 'view';  // 'view' es la acción por defecto

// Crear usuario
if ($action == 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);
    $role = trim($_POST['role']);

    $userModel->username = $username;
    $userModel->email = $email;
    $userModel->password = $password;
    $userModel->role = $role;

    if ($userModel->create()) {
        header("Location: dashboard.php");
        exit();
    } else {
        echo "Hubo un error al crear el usuario.";
    }
}

// Eliminar usuario
if ($action == 'delete' && isset($_GET['id'])) {
    $userId = $_GET['id'];

    if ($userModel->delete($userId)) {
        header("Location: dashboard.php");
        exit();
    } else {
        echo "Hubo un error al eliminar el usuario.";
    }
}

// Editar usuario
if ($action == 'edit' && isset($_GET['id'])) {
    $userId = $_GET['id'];
    $userData = $userModel->readOne($userId);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = trim($_POST['password']) ? password_hash(trim($_POST['password']), PASSWORD_DEFAULT) : $userData['password'];
        $role = trim($_POST['role']);

        $userModel->id = $userId;
        $userModel->username = $username;
        $userModel->email = $email;
        $userModel->password = $password;
        $userModel->role = $role;

        if ($userModel->update()) {
            header("Location: dashboard.php");
            exit();
        } else {
            echo "Hubo un error al actualizar el usuario.";
        }
    }
}

// Listar usuarios
$users = $userModel->readAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Admin</title>
    <link rel="stylesheet" href="../../styles/dashboard.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Dashboard - Administrador</h1>
            <a href="../user/logout.php" class="btn logout-btn">Cerrar sesión</a>
        </header>

        <!-- Formulario de creación de usuario -->
        <?php if ($action == 'create'): ?>
        <section class="form-section">
            <h2>Crear nuevo usuario</h2>
            <form action="dashboard.php?action=create" method="POST">
                <div class="form-group">
                    <label for="username">Nombre:</label>
                    <input type="text" name="username" required>
                </div>
                <div class="form-group">
                    <label for="email">Correo electrónico:</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Contraseña:</label>
                    <input type="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="role">Rol:</label>
                    <select name="role" required>
                        <option value="Admin">Admin</option>
                        <option value="Escritor">Escritor</option>
                        <option value="Suscriptor">Suscriptor</option>
                    </select>
                </div>
                <button type="submit" class="btn submit-btn">Crear Usuario</button>
            </form>
            <br>
            <a href="dashboard.php" class="btn back-btn">Volver al Dashboard</a>
        </section>
        <?php elseif ($action == 'edit' && isset($userData)): ?>

        <!-- Formulario de edición de usuario -->
        <section class="form-section">
            <h2>Editar usuario</h2>
            <form action="dashboard.php?action=edit&id=<?php echo $userId; ?>" method="POST">
                <div class="form-group">
                    <label for="username">Nombre:</label>
                    <input type="text" name="username" value="<?php echo $userData['username']; ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Correo electrónico:</label>
                    <input type="email" name="email" value="<?php echo $userData['email']; ?>" required>
                </div>
                <div class="form-group">
                    <label for="password">Contraseña (dejar en blanco para no cambiar):</label>
                    <input type="password" name="password">
                </div>
                <div class="form-group">
                    <label for="role">Rol:</label>
                    <select name="role" required>
                        <option value="Admin" <?php echo $userData['role'] == 'Admin' ? 'selected' : ''; ?>>Admin</option>
                        <option value="Escritor" <?php echo $userData['role'] == 'Escritor' ? 'selected' : ''; ?>>Escritor</option>
                        <option value="Suscriptor" <?php echo $userData['role'] == 'Suscriptor' ? 'selected' : ''; ?>>Suscriptor</option>
                    </select>
                </div>
                <button type="submit" class="btn submit-btn">Actualizar Usuario</button>
            </form>
            <br>
            <a href="dashboard.php" class="btn back-btn">Volver al Dashboard</a>
        </section>
        <?php else: ?>
        <!-- Enlace para crear nuevo usuario -->
        <section class="user-section">
            <h2>Gestión de usuarios</h2>
            <a href="dashboard.php?action=create" class="btn create-btn">Crear nuevo usuario</a>

            <!-- Tabla de usuarios -->
            <h3>Usuarios Registrados</h3>
            <table class="user-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Rol</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td><?php echo $user['username']; ?></td>
                        <td><?php echo $user['email']; ?></td>
                        <td><?php echo $user['role']; ?></td>
                        <td>
                            <a href="dashboard.php?action=edit&id=<?php echo $user['id']; ?>" class="btn edit-btn">Editar</a>
                            <a href="dashboard.php?action=delete&id=<?php echo $user['id']; ?>" class="btn delete-btn" onclick="return confirm('¿Estás seguro de eliminar a este usuario?')">Eliminar</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
        <?php endif; ?>
    </div>
</body>
</html>