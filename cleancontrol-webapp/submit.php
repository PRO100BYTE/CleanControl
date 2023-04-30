<?php
// submit.php

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

// Получаем данные об отчете из формы
$username = $_POST["username"];
$startTime = $_POST["startTime"];
$lat = $_POST["lat"];
$lng = $_POST["lng"];
$photo = $_FILES["photo"];
// Проверяем наличие файла фотоотчета
if ($photo) {
  // Генерируем уникальное имя для файла фотоотчета
  $photoName = uniqid() . "." . pathinfo($photo["name"], PATHINFO_EXTENSION);
  // Загружаем файл фотоотчета в папку uploads на сервере
  move_uploaded_file($photo["tmp_name"], "uploads/" . $photoName);
  // Формируем URL для доступа к файлу фотоотчета
  $photoUrl = "http://" . $_SERVER["SERVER_NAME"] . "/uploads/" . $photoName;
  // Формируем SQL запрос для вставки данных об отчете в базу данных
  $sql = "INSERT INTO reports (employee, location, startTime, endTime, photoUrl) VALUES ('$username', '$lat,$lng', '$startTime', NOW(), '$photoUrl')";
  // Выполняем SQL запрос и получаем результат
  $result = $conn->query($sql);
  // Если результат успешный (данные об отчете вставлены в базу данных)
  if ($result) {
    // Формируем ответ в формате JSON с признаком успешной отправки отчета
    $response = array(
      "success" => true
    );
  } else {
    // Если результат неуспешный (данные об отчете не вставлены в базу данных)
    // Формируем ответ в формате JSON с сообщением об ошибке и признаком неуспешной отправки отчета
    $response = array(
      "success" => false,
      "error" => "Не удалось сохранить данные об отчете в базе данных."
    );
  }
} else {
  // Если файл фотоотчета отсутствует
  // Формируем ответ в формате JSON с сообщением об ошибке и признаком неуспешной отправки отчета
  $response = array(
    "success" => false,
    "error" => "Не выбран файл фотоотчета."
  );
}
// Закрываем соединение с базой данных
$conn->close();
// Устанавливаем заголовок для передачи данных в формате JSON
header("Content-Type: application/json");
// Выводим ответ в формате JSON
echo json_encode($response);
?>
