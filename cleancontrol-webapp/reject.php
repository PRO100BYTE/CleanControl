<?php
// reject.php

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
// Формируем SQL запрос для обновления данных об отчете в базе данных (установка свойства rejected в true) с использованием подготовленных выражений для защиты от SQL-инъекций
$sql = "UPDATE reports SET rejected = true WHERE id = ?";
// Подготавливаем SQL запрос и получаем объект подготовленного выражения
$stmt = $conn->prepare($sql);
// Привязываем параметры к подготовленному выражению
$stmt->bind_param("i", $id);
// Выполняем подготовленное выражение и получаем результат
$result = $stmt->execute();
// Если результат успешный (данные об отчете обновлены в базе данных)
if ($result) {
  // Формируем ответ в формате JSON с признаком успешного отклонения уборки
  $response = array(
    "success" => true
  );
  // Формируем SQL запрос для добавления записи о действии пользователя в таблицу logs в базе данных с использованием подготовленных выражений для защиты от SQL-инъекций
  $sql = "INSERT INTO logs (user, action, report) VALUES (?, ?, ?)";
  // Подготавливаем SQL запрос и получаем объект подготовленного выражения
  $stmt = $conn->prepare($sql);
  // Привязываем параметры к подготовленному выражению
  $action = "Отклонил уборку";
  $stmt->bind_param("ssi", $_SESSION["username"], $action, $id);
  // Выполняем подготовленное выражение и получаем результат
  $result = $stmt->execute();
} else {
  // Если результат неуспешный (данные об отчете не обновлены в базе данных)
  // Формируем ответ в формате JSON с сообщением об ошибке и признаком неуспешного отклонения уборки
  $response = array(
    "success" => false,
    "error" => "Не удалось обновить данные об отчете в базе данных."
  );
}
// Закрываем соединение с базой данных
$conn->close();
// Устанавливаем заголовок для передачи данных в формате JSON
header("Content-Type: application/json");
// Выводим ответ в формате JSON
echo json_encode($response);
?>
