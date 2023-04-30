<?php
// login.php

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

// Получаем данные о пользователе из тела запроса в формате JSON
$data = json_decode(file_get_contents("php://input"), true);
// Экранируем специальные символы в данных о пользователе
$username = $conn->real_escape_string($data["username"]);
$password = $conn->real_escape_string($data["password"]);
// Формируем SQL запрос для поиска пользователя в базе данных по имени
$sql = "SELECT * FROM users WHERE username = '$username'";
// Выполняем SQL запрос и получаем результат
$result = $conn->query($sql);
// Если результат содержит хотя бы одну строку (найден пользователь)
if ($result->num_rows > 0) {
  // Получаем первую строку результата в виде ассоциативного массива
  $row = $result->fetch_assoc();
  // Проверяем совпадение пароля с хешем пароля из базы данных с помощью функции password_verify
  if ($password == $row["password"]) {
    // Если пароль совпадает с хешем
    // Создаем сессию для пользователя
    session_start();
    $_SESSION["username"] = $row["username"];
    $_SESSION["status"] = $row["status"];
    // Формируем ответ в формате JSON с данными о пользователе и признаком успешного входа
    $response = array(
      "success" => true,
      "username" => $row["username"],
      "status" => $row["status"]
    );
  } else {
    // Если пароль не совпадает с хешем
    // Формируем ответ в формате JSON с сообщением об ошибке и признаком неуспешного входа
    $response = array(
      "success" => false,
      "error" => "Неверный пароль."
    );
  }
} else {
  // Если результат не содержит ни одной строки (не найден пользователь)
  // Формируем ответ в формате JSON с сообщением об ошибке и признаком неуспешного входа
  $response = array(
    "success" => false,
    "error" => "Введенный Вами пользователь не существует. Проверьте правильность введенного логина."
  );
}
// Закрываем соединение с базой данных
$conn->close();
// Устанавливаем заголовок для передачи данных в формате JSON
header("Content-Type: application/json");
// Выводим ответ в формате JSON
echo json_encode($response);
?>
