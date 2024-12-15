<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/post.php';
require_once __DIR__ . '/../../models/comment.php';

use config\Database;
use models\Post;
use models\Comment;

$database = new Database();
$db = $database->getConnection();

$postModel = new Post($db);
$commentModel = new Comment($db);

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $posts = $postModel->readAll(); // Obtener todos los posts

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['subscribe'])) {
        $post_id = $_POST['post_id'];

        // Verificar si ya está suscrito
        if (!$commentModel->isSubscribed()) {
            $commentModel->user_id = $user_id;
            $commentModel->post_id = $post_id;
            $commentModel->created_at = date('Y-m-d H:i:s');
            $commentModel->subscribe();
        }
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['comment'])) {
        $post_id = $_POST['post_id'];
        $content = $_POST['content'];

        // Agregar el comentario
        $commentModel->post_id = $post_id;
        $commentModel->user_id = $user_id;
        $commentModel->content = $content;
        $commentModel->created_at = date('Y-m-d H:i:s');

        if ($commentModel->addComment()) {
            header("Location: suscriptor.php?post_id=" . $post_id); // Redirige al post donde se hizo el comentario
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Suscripción a Posts</title>
    <link rel="stylesheet" href="../../styles/suscriptor.css">
</head>
<body>
    <div class="container">
        <a href="../user/logout.php">Cerrar Sesión</a>
        <h1>Suscripción a Posts</h1>

        <?php if (isset($posts) && count($posts) > 0): ?>
            <h2>Posts Disponibles</h2>
            <ul>
                <?php foreach ($posts as $post): ?>
                    <li>
                        <h3><?php echo htmlspecialchars($post['title']); ?></h3>
                        <p><?php echo htmlspecialchars($post['content']); ?></p>
                        <form action="suscriptor.php" method="POST">
                            <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                            <button type="submit" name="subscribe">Suscribirse</button>
                        </form>

                        <!-- Mostrar comentarios si el usuario está suscrito -->
                        <?php
                        if ($commentModel->isSubscribed()) {
                            $comments = $commentModel->getCommentsByPost();
                            if (count($comments) > 0):
                                echo "<h4>Comentarios:</h4><ul>";
                                foreach ($comments as $comment): ?>
                                    <li><strong><?php echo htmlspecialchars($comment['username']); ?></strong>: 
                                        <?php echo htmlspecialchars($comment['content']); ?> 
                                        <em>(<?php echo $comment['created_at']; ?>)</em></li>
                                <?php endforeach;
                                echo "</ul>";
                            endif;
                            ?>
                            <form action="suscriptor.php" method="POST">
                                <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                <textarea name="content" rows="4" placeholder="Deja tu comentario..."></textarea>
                                <button type="submit" name="comment">Comentar</button>
                            </form>
                        <?php } ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="no-posts">No hay posts disponibles.</p>
        <?php endif; ?>
    </div>
</body>
</html>
