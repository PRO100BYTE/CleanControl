<?php
// confirm.php

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

// Получаем данные об id отчета из тела запроса в формате JSON
$data = json_decode(file_get_contents("php://input"), true);
// Экранируем специальные символы в данных об id отчета
$id = $conn->real_escape_string($data["id"]);
// Формируем SQL запрос для обновления статуса отчета в базе данных на подтвержденный
$sql = "UPDATE reports SET confirmed = 1 WHERE id = '$id'";
// Выполняем SQL запрос и получаем результат
$result = $conn->query($sql);
// Если результат успешный (статус отчета обновлен в базе данных)
if ($result) {
  // Формируем ответ в формате JSON с признаком успешного подтверждения уборки
  $response = array(
    "success" => true
  );
} else {
  // Если результат неуспешный (статус отчета не обновлен в базе данных)
  // Формируем ответ в формате JSON с сообщением об ошибке и признаком неуспешного подтверждения уборки
  $response = array(
    "success" => false,
    "error" => "Не удалось обновить статус отчета в базе данных."
  );
}
// Закрываем соединение с базой данных
$conn->close();
// Устанавливаем заголовок для передачи данных в формате JSON
header("Content-Type: application/json");
// Выводим ответ в формате JSON
echo json_encode($response);
?>
