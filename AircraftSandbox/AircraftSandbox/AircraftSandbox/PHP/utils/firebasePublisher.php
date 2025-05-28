<?php
namespace PHP\Utils;

class FirebasePublisher {
    private $firebaseUrl = 'https://aircraft-sandbox-default-rtdb.firebaseio.com/';
    private $authToken;

    public function __construct($authToken = null) {
        $this->authToken = $authToken;
    }

    private function buildUrl($path) {
        $url = $this->firebaseUrl . ltrim($path, '/') . '.json';
        if ($this->authToken) {
            $url .= '?auth=' . $this->authToken;
        }
        return $url;
    }

    public function publish($path, $data) {
        $url = $this->buildUrl($path);
        $jsonData = json_encode($data);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'PUT',        // <--- Добавлено
            CURLOPT_POSTFIELDS => $jsonData,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        ]);
        $response = curl_exec($ch);
        $error = curl_error($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);

        // Отладка
        /*echo "<pre style='background:#eee;padding:10px'>";
        echo "➡️ <b>Firebase publish</b>\n";
        echo "URL: $url\n";
        echo "DATA: $jsonData\n";
        echo "RESPONSE: $response\n";
        echo "cURL ERROR: $error\n";
        echo "INFO: "; print_r($info);
        echo "</pre>";*/

        if ($response === false) {
            throw new \Exception("cURL error: " . $error);
        }

        return json_decode($response, true);
    }


    public function getAll($path) {
        $url = $this->buildUrl($path);
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json']
        ]);
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        // Вывод отладочной информации
        /*echo "<pre style='background:#eef;padding:10px'>";
        echo "➡️ <b>Firebase getAll</b>\n";
        echo "URL: $url\n";
        echo "RESPONSE: $response\n";
        echo "cURL ERROR: $error\n";
        echo "</pre>";*/

        if ($response === false) {
            throw new \Exception("cURL error: " . $error);
        }

        return json_decode($response, true);
    }
}
