<?php
namespace PHP\Clases;

use PDO;
use Exception;

class ApkInfo {
    public $id;
    public $author;
    public $size;
    public $addedBy;
    public $date;
    public $downloads;
    public $imagePath;
    public $description;
    public $category; // нове поле

    public function __construct() {
        $this->id = 0;
        $this->author = '';
        $this->size = '';
        $this->addedBy = '';
        $this->date = '';
        $this->downloads = 0;
        $this->imagePath = '';
        $this->description = '';
        $this->category = '';
    }

    public function loadFromDB($db, $id) {
    if (!is_numeric($id)) {
        throw new Exception("Invalid APK ID.");
    }

    $stmt = $db->prepare("SELECT * FROM ApkInfo WHERE Id = :id"); // <<< тут ВЕЛИКА LITERA
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    $apk = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$apk) {
        throw new Exception("APK not found.");
    }

    // 👇 обов'язково так само з великої букви:
    $this->id          = $apk['Id'];
    $this->author      = $apk['Author'];
    $this->size        = $apk['Size'];
    $this->addedBy     = $apk['AddedBy'];
    $this->date        = $apk['Date'];
    $this->downloads   = $apk['Downloads'];
    $this->imagePath   = $apk['ImagePath'];
    $this->description = $apk['Description'];
    $this->category    = $apk['Category'];

    return true;
}

    public static function renderCardById($db, $id) {
    $stmt = $db->prepare("SELECT * FROM ApkInfo WHERE Id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    $apk = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$apk) return '';

    $title    = htmlspecialchars($apk['Description'], ENT_QUOTES, 'UTF-8');
    $category = htmlspecialchars($apk['Category'], ENT_QUOTES, 'UTF-8');
    $apkId    = (int)$apk['Id']; // ❗ важливо

    return "
    <div class=\"apk-card {$category} fade-in\" data-id=\"{$apkId}\">
        <div class=\"apk-card__overlay\">
            <span class=\"apk-card__title\">{$title}</span>
        </div>
    </div>";
}

    public function saveToDB($db) {
        $stmt = $db->prepare("INSERT INTO ApkInfo (author, size, added_by, date, downloads, image_path, description, category)
                              VALUES (:author, :size, :added_by, :date, :downloads, :image_path, :description, :category)");
        if (!$stmt) {
            throw new Exception("Failed to prepare insert statement.");
        }

        $stmt->bindParam(':author', $this->author);
        $stmt->bindParam(':size', $this->size);
        $stmt->bindParam(':added_by', $this->addedBy);
        $stmt->bindParam(':date', $this->date);
        $stmt->bindParam(':downloads', $this->downloads);
        $stmt->bindParam(':image_path', $this->imagePath);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':category', $this->category);

        if (!$stmt->execute()) {
            throw new Exception("Failed to insert APK.");
        }

        $this->id = (int)$db->lastInsertId();
        return true;
    }

    public function updateToDB($db) {
        if ($this->id <= 0) {
            throw new Exception("Invalid APK ID for update.");
        }

        $stmt = $db->prepare("UPDATE ApkInfo SET author = :author, size = :size, added_by = :added_by, date = :date,
                              downloads = :downloads, image_path = :image_path, description = :description, category = :category
                              WHERE id = :id");
        $stmt->bindParam(':author', $this->author);
        $stmt->bindParam(':size', $this->size);
        $stmt->bindParam(':added_by', $this->addedBy);
        $stmt->bindParam(':date', $this->date);
        $stmt->bindParam(':downloads', $this->downloads);
        $stmt->bindParam(':image_path', $this->imagePath);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':category', $this->category);
        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            throw new Exception("Failed to update APK.");
        }

        return true;
    }

    public function deleteFromDB($db) {
        if ($this->id <= 0) {
            throw new Exception("Invalid APK ID for deletion.");
        }

        $stmt = $db->prepare("DELETE FROM ApkInfo WHERE id = :id");
        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
        if (!$stmt->execute()) {
            throw new Exception("Failed to delete APK.");
        }

        return true;
    }

    public function createApkCard() {
        $escapedAuthor      = htmlspecialchars($this->author, ENT_QUOTES, 'UTF-8');
        $escapedSize        = htmlspecialchars($this->size, ENT_QUOTES, 'UTF-8');
        $escapedAddedBy     = htmlspecialchars($this->addedBy, ENT_QUOTES, 'UTF-8');
        $escapedDate        = htmlspecialchars($this->date, ENT_QUOTES, 'UTF-8');
        $escapedDownloads   = (int)$this->downloads;
        $escapedImagePath   = htmlspecialchars($this->imagePath, ENT_QUOTES, 'UTF-8');
        $escapedDescription = nl2br(htmlspecialchars($this->description, ENT_QUOTES, 'UTF-8'));

        return "
        <div class=\"apk-card {$this->category}\" id=\"apk-{$this->id}\">
            <img src=\"{$escapedImagePath}\" alt=\"APK Image\" class=\"apk-card__image\">
            <div class=\"apk-card__info\">
                <h4 class=\"apk-card__title\">Автор: {$escapedAuthor}</h4>
                <p><strong>Розмір:</strong> {$escapedSize}</p>
                <p><strong>Додав:</strong> {$escapedAddedBy}</p>
                <p><strong>Дата:</strong> {$escapedDate}</p>
                <p><strong>Завантажень:</strong> {$escapedDownloads}</p>
                <p class=\"apk-card__description\">{$escapedDescription}</p>
            </div>
        </div>";
    }
}
?>