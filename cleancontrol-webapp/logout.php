<?php
// logout.php

// Завершаем сессию пользователя
session_start();
session_destroy();
// Формируем ответ в формате JSON с признаком успешного выхода
$response = array(
  "success" => true
);
// Устанавливаем заголовок для передачи данных в формате JSON
header("Content-Type: application/json");
// Выводим ответ в формате JSON
echo json_encode($response);
?>