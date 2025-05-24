<?php
header('Content-Type: application/json; charset=utf-8');
session_start();

try {
    // 1) Подключаем SQLite
    $dbFile = realpath(__DIR__ . '/../../sqlite/users.db');
    if (!$dbFile) {
        throw new Exception('База не найдена');
    }
    $db = new PDO("sqlite:$dbFile");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 2) Авторизация
    if (!isset($_SESSION['user_login'])) {
        echo json_encode(['success' => false, 'message' => 'Пользователь не авторизован']);
        exit;
    }
    $userLogin = $_SESSION['user_login'];

    // 3) Читаем вход
    $postId = isset($_POST['postId']) ? (int)$_POST['postId'] : 0;
    $action = $_POST['action'] ?? '';
    if ($postId <= 0 || !in_array($action, ['like','dislike'], true)) {
        throw new Exception('Неверные параметры');
    }
    $new = $action === 'like' ? 1 : -1;

    // 4) Узнаём текущую реакцию (false если нет)
    $stmt = $db->prepare("SELECT Reaction FROM UserInfo WHERE UserLogin = :u AND PostId = :p");
    $stmt->execute([':u'=>$userLogin, ':p'=>$postId]);
    $current = $stmt->fetchColumn(); // может быть false, '0', '1', '-1'
    if ($current !== false) {
        $current = (int)$current;
    }

    // 5) Вычисляем итоговую реакцию
    if ($current === false || $current === 0) {
        // ставим вперый раз или после снятия
        $updated = $new;
    } elseif ($current === $new) {
        // снимаем свою же реакцию
        $updated = 0;
    } else {
        // меняем с +1 на -1 или наоборот
        $updated = $new;
    }

    $db->beginTransaction();

    // a) Обновляем UserInfo
    if ($current === false) {
        // ещё не было записи — INSERT
        $stmt = $db->prepare("
            INSERT INTO UserInfo (UserLogin, PostId, Reaction)
            VALUES (:u, :p, :r)
        ");
        $stmt->execute([':u'=>$userLogin,':p'=>$postId,':r'=>$updated]);
    } else {
        // уже была — UPDATE
        $stmt = $db->prepare("
            UPDATE UserInfo
            SET Reaction = :r
            WHERE UserLogin = :u
            AND PostId   = :p
        ");
        $stmt->execute([':r'=>$updated,':u'=>$userLogin,':p'=>$postId]);
    }

    // b) Вычисляем дельты для счётчиков
    $deltaLike    = ($updated ===  1 ? 1 : 0) - ($current ===  1 ? 1 : 0);
    $deltaDislike = ($updated === -1 ? 1 : 0) - ($current === -1 ? 1 : 0);

    // c) Одним запросом инкрементим/декрементим 
    $stmt = $db->prepare("
        UPDATE Post
        SET LikesCount    = LikesCount    + :dL,
            DislikesCount = DislikesCount + :dD
        WHERE Id = :p
    ");
    $stmt->execute([
        ':dL' => $deltaLike,
        ':dD' => $deltaDislike,
        ':p'  => $postId
    ]);

    $db->commit();


    // 7) Возвращаем актуальные данные
    $stmt = $db->prepare("SELECT LikesCount, DislikesCount FROM Post WHERE Id = :p");
    $stmt->execute([':p'=>$postId]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success'       => true,
        'likesCount'    => (int)$post['LikesCount'],
        'dislikesCount' => (int)$post['DislikesCount'],
        'userReaction'  => $updated
    ]);
    exit;

} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    echo json_encode(['success'=>false,'message'=>'Ошибка сервера: '.$e->getMessage()]);
    exit;
}
