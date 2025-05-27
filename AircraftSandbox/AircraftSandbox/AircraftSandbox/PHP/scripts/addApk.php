<?php
require_once __DIR__ . '/../clases/ApkInfo.php';

use PHP\Clases\ApkInfo;

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Метод не підтримується.");
    }

    $dbFile = __DIR__ . '/../../sqlite/users.db';
    $db = new PDO("sqlite:$dbFile");

    // Перевірка наявності та зчитування полів
    $required = ['apkAuthor', 'apkSize', 'apkAddedBy', 'apkDate', 'apkDownloads', 'apkDesc', 'apkType', 'apkLink'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Відсутнє поле: $field");
        }
    }

    // Обробка зображення
    if (!isset($_FILES['apkImage']) || $_FILES['apkImage']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("Помилка при завантаженні зображення.");
    }

    $imageName = basename($_FILES['apkImage']['name']);
    $targetDir = __DIR__ . '/../../img/APK/';
    $targetPath = $targetDir . $imageName;

    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    if (!move_uploaded_file($_FILES['apkImage']['tmp_name'], $targetPath)) {
        throw new Exception("Не вдалося перемістити файл.");
    }

    // Запис у БД
    $apk = new ApkInfo();
    $apk->author = $_POST['apkAuthor'];
    $apk->size = $_POST['apkSize'];
    $apk->addedBy = $_POST['apkAddedBy'];
    $apk->date = $_POST['apkDate'];
    $apk->downloads = (int)$_POST['apkDownloads'];
    $apk->description = $_POST['apkDesc'];
    $apk->category = $_POST['apkType'];
    $apk->imagePath = 'img/APK/' . $imageName;
    $apk->apkLink = $_POST['apkLink'];

    $stmt = $db->prepare("INSERT INTO ApkInfo (Author, Size, AddedBy, Date, Downloads, ImagePath, Description, Category, ApkLink)
                          VALUES (:author, :size, :addedBy, :date, :downloads, :imagePath, :description, :category, :apkLink)");

    $stmt->bindParam(':author', $apk->author);
    $stmt->bindParam(':size', $apk->size);
    $stmt->bindParam(':addedBy', $apk->addedBy);
    $stmt->bindParam(':date', $apk->date);
    $stmt->bindParam(':downloads', $apk->downloads, PDO::PARAM_INT);
    $stmt->bindParam(':imagePath', $apk->imagePath);
    $stmt->bindParam(':description', $apk->description);
    $stmt->bindParam(':category', $apk->category);
    $stmt->bindParam(':apkLink', $apk->apkLink);

    if (!$stmt->execute()) {
        throw new Exception("Помилка під час збереження у базу даних.");
    }

    echo "✅ APK успішно додано.";
} catch (Throwable $e) {
    echo "❌ Помилка: " . $e->getMessage();
}