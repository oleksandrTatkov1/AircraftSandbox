<?php
namespace PHP\Clases;

require_once __DIR__ . '/../utils/firebasePublisher.php';
use PHP\Utils\FirebasePublisher;
use Exception;

class News {
    public $id;
    public $imagePath;
    public $description;
    public $sliderId;

    private $firebase;

    public function __construct($imagePath = '', $description = '', $sliderId = null, $id = null, $authToken = null) {
        $this->id = $id;
        $this->imagePath = $imagePath;
        $this->description = $description;
        $this->sliderId = $sliderId;

        $this->firebase = new FirebasePublisher($authToken);
    }

    private function sanitizeKey($key) {
        return str_replace(['@', '.', ' '], ['_at_', '_dot_', '_'], (string)$key);
    }

    public function saveToDB() {
        if (empty($this->id)) {
            throw new Exception("ID must be provided to save news.");
        }

        $safeId = $this->sanitizeKey($this->id);

        $data = [
            'imagePath'   => $this->imagePath,
            'description' => $this->description,
            'sliderId'    => $this->sliderId
        ];

        $this->firebase->publish("news/{$safeId}", $data);
        return true;
    }

    public function loadFromDB($id) {
        if (empty($id)) {
            throw new Exception("News ID must be provided.");
        }

        $safeId = $this->sanitizeKey($id);
        $data = $this->firebase->getAll("news/{$safeId}");

        if (!$data || !is_array($data)) {
            throw new Exception("News not found.");
        }

        $this->id          = $id;
        $this->imagePath   = $data['imagePath'] ?? '';
        $this->description = $data['description'] ?? '';
        $this->sliderId    = $data['sliderId'] ?? null;

        return true;
    }

    public function updateFromDB() {
        return $this->saveToDB(); // логіка ідентична
    }

    public function deleteFromDB() {
        if (empty($this->id)) {
            throw new Exception("ID must be provided for deletion.");
        }

        $safeId = $this->sanitizeKey($this->id);
        $this->firebase->publish("news/{$safeId}", null);
        return true;
    }

    public function renderNewsItem() {
        $imgTag = $this->imagePath 
            ? '<img src="' . htmlspecialchars($this->imagePath) . '" alt="News Image" width="270" height="200" style="object-fit:cover; border-radius:12px;" />' 
            : '';
        $desc = htmlspecialchars($this->description);

        return <<<HTML
            <div class="swiper-slide">
                <div class="slider__item">
                    <div class="containerImg">
                        {$imgTag}
                    </div>
                    <div class="containerTitle">
                        <h2 class="containerTitle__text">{$desc}</h2>
                    </div>
                </div>
            </div>
        HTML;
    }
}