<?php
session_start();

require_once __DIR__ . '/../clases/Post.php';

use PHP\Clases\Post;

// Перевірка авторизації
if (!isset($_SESSION['user_login'])) {
    http_response_code(403);
    exit("Користувач не авторизований.");
}

$login = $_SESSION['user_login'];
$firebase = new \PHP\Utils\FirebasePublisher();
$allPosts = $firebase->getAll('posts');

$html = '';

if (is_array($allPosts)) {
    foreach ($allPosts as $id => $data) {
        if (isset($data['ownerLogin']) && $data['ownerLogin'] === $login) {
            $post = new Post();
            $post->id = $id;
            $post->header = $data['header'] ?? '';
            $post->imagePath = $data['imagePath'] ?? '';
            $post->content = $data['content'] ?? '';
            $post->ownerLogin = $login;

            // Створюємо HTML
            $author = $post->getAuthor();
            $authorName = $author?->Name ?? $post->ownerLogin;
            $authorAvatar = $author?->ImagePath ?: '/AircraftSandbox/AircraftSandbox/AircraftSandbox/AircraftSandbox/img/users/default-avatar.png';

            $html .= '
            <div class="profile-card__post">
                <div class="post__header">
                    <img src="' . htmlspecialchars($authorAvatar, ENT_QUOTES) . '" class="post__avatar" />
                    <span class="post__user">@' . htmlspecialchars($authorName, ENT_QUOTES) . '</span>
                </div>
                <h4 class="post__title">' . htmlspecialchars($post->header, ENT_QUOTES) . '</h4>
                <p class="post__text">' . nl2br(htmlspecialchars($post->content, ENT_QUOTES)) . '</p>';

                if (!empty($post->imagePath)) {
                    $html .= '<div class="post__image-wrapper">
            <img class="post__image" src="' . htmlspecialchars($post->imagePath, ENT_QUOTES) . '" alt="Post image">
          </div>';

                }
                

            $html .= '</div>';
        }
    }
}

echo $html;
