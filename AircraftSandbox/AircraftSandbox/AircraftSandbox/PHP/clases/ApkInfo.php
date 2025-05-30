<?php
namespace PHP\Clases;

require_once __DIR__ . '/../utils/firebasePublisher.php';
use PHP\Utils\FirebasePublisher;
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
    public $category;
    public $apkLink;

    private $firebase;

    public function __construct($authToken = null) {
        $this->id = null;
        $this->author = '';
        $this->size = '';
        $this->addedBy = '';
        $this->date = '';
        $this->downloads = 0;
        $this->imagePath = '';
        $this->description = '';
        $this->category = '';
        $this->apkLink = '';

        $this->firebase = new FirebasePublisher($authToken);
    }

    private function sanitizeKey($key) {
        return str_replace(['@', '.', ' '], ['_at_', '_dot_', '_'], (string)$key);
    }

    public function loadFromDB($id) {
        if (empty($id) || !is_string($id)) {
            throw new Exception("Invalid APK ID.");
        }

        $safeId = $this->sanitizeKey($id);
        $data = $this->firebase->getAll("apkInfo/{$safeId}");

        if (!$data || !is_array($data)) {
            throw new Exception("APK not found.");
        }

        $this->id          = (int)$id;
        $this->author      = $data['author'] ?? '';
        $this->size        = $data['size'] ?? '';
        $this->addedBy     = $data['addedBy'] ?? '';
        $this->date        = $data['date'] ?? '';
        $this->downloads   = (int)($data['downloads'] ?? 0);
        $this->imagePath   = $data['imagePath'] ?? '';
        $this->description = $data['description'] ?? '';
        $this->category    = $data['category'] ?? '';
        $this->apkLink     = $data['apkLink'] ?? '';

        return true;
    }

    public function saveToDB() {
        if (empty($this->id)) {
            throw new Exception("ID must be provided for saving.");
        }

        $safeId = $this->sanitizeKey($this->id);
        $data = [
            'author'      => $this->author,
            'size'        => $this->size,
            'addedBy'     => $this->addedBy,
            'date'        => $this->date,
            'downloads'   => $this->downloads,
            'imagePath'   => $this->imagePath,
            'description' => $this->description,
            'category'    => $this->category,
            'apkLink'     => $this->apkLink
        ];

        $this->firebase->publish("apkInfo/{$safeId}", $data);
        return true;
    }

    public function updateToDB() {
        return $this->saveToDB(); // same logic
    }

    public function deleteFromDB() {
        if (empty($this->id)) {
            throw new Exception("ID must be provided for deletion.");
        }

        $safeId = $this->sanitizeKey($this->id);
        $this->firebase->publish("apkInfo/{$safeId}", null);
        return true;
    }

    public static function renderCardById($firebase, $id) {
        $safeId = str_replace(['@', '.', ' '], ['_at_', '_dot_', '_'], (string)$id);
        $apk = $firebase->getAll("apkInfo/{$safeId}");

        if (!$apk) return '';

        $title    = htmlspecialchars($apk['description'] ?? '', ENT_QUOTES, 'UTF-8');
        $category = htmlspecialchars($apk['category'] ?? '', ENT_QUOTES, 'UTF-8');
        $apkId    = htmlspecialchars($id, ENT_QUOTES, 'UTF-8');

        return "
        <div class=\"apk-card {$category} fade-in\" data-id=\"{$apkId}\">
            <div class=\"apk-card__overlay\">
                <span class=\"apk-card__title\">{$title}</span>
            </div>
        </div>";
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