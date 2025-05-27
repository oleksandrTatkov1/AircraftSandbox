<?php
namespace PHP\Clases;
use Exception;
use PDO;
use PDOException;

class News {
    public $id;
    public $imagePath;
    public $description;
    public $sliderId;
    
    public function __construct($imagePath = '', $description = '', $sliderId = null, $id = null) {
        $this->id = $id;
        $this->imagePath = $imagePath;
        $this->description = $description;
        $this->sliderId = $sliderId;
    }

    private function connectDB($dbFile) {
        return new PDO("sqlite:" . $this->$dbFile);
    }

    public function saveToDB($dbFile) {
        $db = $this->connectDB($dbFile);
        $stmt = $db->prepare("INSERT INTO News (ImagePath, Description, SliderId) VALUES (:imagePath, :description, :sliderId)");
        $stmt->bindParam(':imagePath', $this->imagePath);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':sliderId', $this->sliderId);
        $stmt->execute();
        $this->id = $db->lastInsertId();
    }

    public function loadFromDB($id, $dbFile) {
        $db = $this->connectDB($dbFile);
        $stmt = $db->prepare("SELECT * FROM News WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            $this->id = $data['id'];
            $this->imagePath = $data['ImagePath'];
            $this->description = $data['Description'];
            $this->sliderId = $data['SliderId'];
            return true;
        }

        return false;
    }

    public function updateFromDB($dbFile) {
        if (!$this->id) return false;

        $db = $this->connectDB($dbFile);
        $stmt = $db->prepare("UPDATE News SET ImagePath = :imagePath, Description = :description, SliderId = :sliderId WHERE id = :id");
        $stmt->bindParam(':imagePath', $this->imagePath);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':sliderId', $this->sliderId);
        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function renderNewsItem() {
        // Render news item with fixed image size and rounded corners
        $imgTag = $this->imagePath 
            ? '<img src="' . htmlspecialchars($this->imagePath) . '" alt="News Image" ' 
              . 'width="270" height="200" style="object-fit:cover; border-radius:12px;"/>' 
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
