<?php
// export.php

// Проверяем наличие сессии пользователя
session_start();
if (!isset($_SESSION["username"]) || !isset($_SESSION["status"])) {
  die("Доступ запрещен.");
}
// Проверяем статус пользователя (должен быть администратором)
if ($_SESSION["status"] != "admin") {
  die("Доступ запрещен.");
}

// Подключаемся к базе данных mysql
$host = "localhost";
$username = "pro100byte_usr";
$password = "pro100byte_testapp";
$base = "pro100byte";
$conn = new mysqli($host, $username, $password, $base);
// Проверяем наличие ошибок подключения
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Формируем SQL запрос для получения данных об отчетах за последние 14 дней из базы данных
$sql = "SELECT * FROM reports WHERE endTime >= DATE_SUB(NOW(), INTERVAL 14 DAY) ORDER BY id DESC";
// Выполняем SQL запрос и получаем результат
$result = $conn->query($sql);
// Создаем пустую строку для хранения данных об отчетах в виде текста
$text = "";
// Проходим по каждой строке результата в виде ассоциативного массива
while ($row = $result->fetch_assoc()) {
  // Добавляем в строку данные об отчете в виде текста с разделителями табуляции и переносами строк
  $text .= $row["id"] . "\t" . $row["employee"] . "\t" . $row["location"] . "\t" . $row["startTime"] . "\t" . $row["endTime"] . "\t" . $row["photoUrl"] . "\n";
}
// Закрываем соединение с базой данных
$conn->close();
// Генерируем уникальное имя для файла с отчетами в формате txt
$fileName = uniqid() . ".txt";
// Создаем файл с отчетами в папке exports на сервере и записываем в него строку с данными об отчетах
file_put_contents("exports/" . $fileName, $text);
// Формируем ответ в формате JSON с именем файла с отчетами
$response = array(
  "file" => $fileName
);
// Устанавливаем заголовок для передачи данных в формате JSON
header("Content-Type: application/json");
// Выводим ответ в формате JSON
echo json_encode($response);
?>
