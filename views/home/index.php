<?php
session_start();

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/post.php';
require_once __DIR__ . '/../../models/comment.php';
require_once __DIR__ . '/../../models/user.php';  

use config\Database;
use models\Post;
use models\Comment;
use models\User; 

// Inicializar las variables para evitar errores de "variable indefinida"
$username = ''; 
$posts = [];  // Para almacenar los posts del usuario
$comments = [];  // Para almacenar los comentarios de un post

// Verificar si el usuario está autenticado
if (isset($_SESSION['user_id'])) {
    // Obtener el nombre de usuario desde la base de datos
    $database = new Database();
    $db = $database->getConnection();
    $userModel = new User($db);  // Usar tu modelo de usuario
    $user_id = $_SESSION['user_id'];
    
    // Obtener el nombre del usuario
    $user = $userModel->getUserById($user_id); 
    $username = $user['username']; 

    // Obtener los posts del usuario
    $postModel = new Post($db);
    $posts = $postModel->getPostsByUser($user_id);  

    // Comprobar si se está editando un post
    if (isset($_GET['post_id'])) {
        $post_id = $_GET['post_id'];
        $post = $postModel->readOne($post_id); 

        // Obtener los comentarios del post
        $commentModel = new Comment($db);
        $comments = $commentModel->getComments($post_id); 
    }
} else {
    // Si el usuario no está autenticado, redirigir a login
    header("Location: ../user/login.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido, <?php echo htmlspecialchars($username); ?></title>
    <link rel="stylesheet" href="../../styles/post.css">
</head>
<body>
    <h1>Bienvenido, <?php echo htmlspecialchars($username); ?>!</h1>
    <a href="../user/logout.php">Cerrar sesión</a>

    <!-- Mostrar los posts del usuario -->
    <h2>Mis Posts</h2>
    <?php if (count($posts) > 0): ?>
        <ul>
            <?php foreach ($posts as $post): ?>
                <li>
                    <a href="index.php?post_id=<?php echo $post['id']; ?>"><?php echo htmlspecialchars($post['title']); ?></a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>No tienes posts creados.</p>
    <?php endif; ?>

    <!-- Formulario para crear o editar un post -->
    <h2><?php echo isset($post) ? 'Editar Post' : 'Crear Nuevo Post'; ?></h2>
    <form action="index.php" method="POST">
        <label for="title">Título:</label>
        <input type="text" name="title" id="title" value="<?php echo isset($post) ? htmlspecialchars($post['title']) : ''; ?>" required>
        <br>
        <label for="content">Contenido:</label>
        <textarea name="content" id="content" required><?php echo isset($post) ? htmlspecialchars($post['content']) : ''; ?></textarea>
        <br>
        <button type="submit"><?php echo isset($post) ? 'Actualizar Post' : 'Crear Post'; ?></button>
    </form>

    <?php if (isset($message)): ?>
        <div class="message">
            <p><?php echo $message; ?></p>
        </div>
    <?php endif; ?>

    <!-- Mostrar los comentarios del post -->
    <?php if (isset($post) && !empty($comments)): ?>
        <h3>Comentarios:</h3>
        <ul>
            <?php foreach ($comments as $comment): ?>
                <li>
                    <strong><?php echo htmlspecialchars($comment['username']); ?>:</strong>
                    <p><?php echo htmlspecialchars($comment['content']); ?></p>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php elseif (isset($post)): ?>
        <p>No hay comentarios en este post.</p>
    <?php endif; ?>
</body>
</html>
