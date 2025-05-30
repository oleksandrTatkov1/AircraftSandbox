<?php
session_start();

if (!isset($_SESSION['uid'])) {
    die("Користувач не авторизований. <a href='login_fake.php'>Авторизуватися</a>");
}

$name = $_SESSION['name'] ?? '';
$photo = $_SESSION['photo'] ?? 'uploads/default.png';
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Профіль</title>
</head>
<body>
    <h1>Профіль</h1>
    <form action="save_profile.php" method="post" enctype="multipart/form-data">
        <label>Ім’я:
            <input type="text" name="name" value="<?= htmlspecialchars($name) ?>">
        </label><br><br>

        <label>Фото:
            <input type="file" name="photo">
        </label><br><br>

        <img src="<?= htmlspecialchars($photo) ?>" alt="Фото профілю" width="150"><br><br>

        <button type="submit">Зберегти зміни</button>
    </form>
</body>
</html>
