<?php
// load.php

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

// Формируем SQL запрос для получения данных об отчетах из базы данных
$sql = "SELECT * FROM reports ORDER BY id DESC";
// Выполняем SQL запрос и получаем результат
$result = $conn->query($sql);
// Создаем пустой массив для хранения данных об отчетах
$reports = array();
// Проходим по каждой строке результата в виде ассоциативного массива
while ($row = $result->fetch_assoc()) {
  // Добавляем строку в массив с данными об отчетах
  $reports[] = $row;
}
// Закрываем соединение с базой данных
$conn->close();
// Формируем ответ в формате JSON с данными об отчетах
$response = array(
  "reports" => $reports
);
// Устанавливаем заголовок для передачи данных в формате JSON
header("Content-Type: application/json");
// Выводим ответ в формате JSON
echo json_encode($response);
?>
