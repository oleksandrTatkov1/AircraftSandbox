<?php
// Шлях до файлу лічильника
$counterFile = "counter.txt";

// Якщо файл існує, читаємо з нього кількість, інакше починаємо з 0
$count = file_exists($counterFile) ? (int)file_get_contents($counterFile) : 0;

// Збільшуємо лічильник на 1
$count++;

// Записуємо нове значення назад у файл
file_put_contents($counterFile, $count);

// Виводимо результат
echo "<p style='text-align:center; font-weight:bold;'>Відвідувань: $count</p>";
?>
