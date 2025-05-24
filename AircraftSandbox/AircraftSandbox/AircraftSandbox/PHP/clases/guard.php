<?php
// guardlogger.php

class GuardLogger {
    private $user;

    public function __construct(array $user) {
        $this->user = $user;
    }

    // === БЛОК 1 ===
    public function logPhoneOperator() {
        $operators = [
            '067' => 'Kyivstar', '068' => 'Kyivstar', '096' => 'Kyivstar',
            '097' => 'Kyivstar', '098' => 'Kyivstar',
            '050' => 'Vodafone', '066' => 'Vodafone', '095' => 'Vodafone',
            '099' => 'Vodafone',
            '063' => 'Lifecell', '073' => 'Lifecell', '093' => 'Lifecell',
        ];

        $phone = $this->user['phone'] ?? '';
        $phone = preg_replace('/[^0-9]/', '', $phone); // Оставляем только цифры

        $phone = preg_replace('/[^0-9]/', '', $phone); // оставляем только цифры

        if (strlen($phone) == 12 && substr($phone, 0, 3) == '380') {
            $prefix = '0' . substr($phone, 3, 2);
        } elseif (strlen($phone) == 10) {
            $prefix = substr($phone, 0, 3);
        } else {
            $prefix = '';
        }


        $operator = $operators[$prefix] ?? 'Невідомий оператор';

        return "[Блок 1] Оператор телефону: $operator (префікс: $prefix)";
    }

    public function logIpValidation() {
        $ip = $this->user['ip'] ?? $_SERVER['REMOTE_ADDR'];

        $isValid = filter_var($ip, FILTER_VALIDATE_IP) !== false;

        if ($ip === '::1' || $ip === '127.0.0.1') {
            // подменяем локальный IP на тестовый внешний IP для геолокации
            $ip = '8.8.8.8';  // Google DNS IP для примера
        }

        return "[Блок 1] IP-адреса $ip є " . ($isValid ? 'валідною' : 'невалідною');
    }

    // === БЛОК 2 ===
    public function logComputerInfo() {
        $os = php_uname();
        return "[Блок 2] ОС користувача: $os";
    }

    public function logBrowserInfo() {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'невідомий агент';
        return "[Блок 2] User-Agent (браузер): $userAgent";
    }

    // === БЛОК 3 ===
    public function logGeoLocation() {
        $ip = $this->user['ip'] ?? $_SERVER['REMOTE_ADDR'];

        // Подмена локального адреса на тестовый публичный
        if ($ip === '::1' || $ip === '127.0.0.1') {
            $ip = '8.8.8.8'; // Google DNS, можно заменить на другой IP
        }

        $url = "http://ip-api.com/json/" . urlencode($ip) . "?fields=status,message,country,regionName,city,lat,lon";
        $response = @file_get_contents($url);

        if ($response === false) {
            return "[Блок 3] Не вдалося отримати геолокацію";
        }

        $data = json_decode($response, true);
        if (!is_array($data) || $data['status'] !== 'success') {
            return "[Блок 3] Геолокація недоступна або невідома";
        }

        return "[Блок 3] Країна: {$data['country']}, Регіон: {$data['regionName']}, Місто: {$data['city']}, Координати: {$data['lat']}, {$data['lon']}";
    }

    // === Загальний метод логування у файл ===
    public function writeLogToFile() {
        $logData = [
            date('[Y-m-d H:i:s]'),
            $this->logPhoneOperator(),
            $this->logIpValidation(),
            $this->logComputerInfo(),
            $this->logBrowserInfo(),
            $this->logGeoLocation()
        ];

        $logText = implode("\n", $logData) . "\n--------------------------\n";

        // Відносний шлях до logs (з кореня сайту)
        $relativePath = __DIR__ . '/../../logs';
        if (!file_exists($relativePath)) {
            mkdir($relativePath, 0777, true);
        }

        $filename = $relativePath . '/auth_log_' . date('Ymd') . '.log';
        file_put_contents($filename, $logText, FILE_APPEND);
    }
}
?>