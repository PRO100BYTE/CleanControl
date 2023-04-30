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
