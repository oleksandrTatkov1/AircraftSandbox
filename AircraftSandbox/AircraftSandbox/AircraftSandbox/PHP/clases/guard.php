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
        // Если в $this->user['ip'] нет, то пытаемся получить «реальный» из запросов
        $ip = $this->user['ip'] ?? getRealClientIp();

        $isValid = filter_var($ip, FILTER_VALIDATE_IP) !== false;

        // Для локальной разработки можно подменять IPv6/локальный IPv4 на внешний
        if ($ip === '::1' || $ip === '127.0.0.1') {
            $ip = '8.8.8.8';
        }

        return "[Блок 1] IP-адрес $ip є " . ($isValid ? 'валідною' : 'невалідною');
    }


    /**
     * Возвращает "реальный" IP клиента, учитывая заголовки прокси.
     * @return string|null
     */
    function getRealClientIp(): ?string {
        // Список заголовков, в которых могут прилетать IP
        $headers = [
            'HTTP_X_FORWARDED_FOR',
            'HTTP_CLIENT_IP',
            'HTTP_X_REAL_IP',
            'HTTP_CF_CONNECTING_IP', // Cloudflare
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED'
        ];

        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                // Может быть список через запятую
                $ips = explode(',', $_SERVER[$header]);
                foreach ($ips as $ip) {
                    $ip = trim($ip);
                    // Берём первый публичный IPv4/IPv6
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                        return $ip;
                    }
                }
            }
        }

        // Фолбэк — REMOTE_ADDR (может быть локалхост или адрес прокси)
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        return $ip;
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