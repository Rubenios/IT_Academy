<?php

$speed_start = microtime(true); //Проверяем скорость выполнения скрипта

define('MESSAGES_PER_PAGE', 3); //Задаем константой количество отображаемых сообщений на одной странице
define('NEIGHBOR_PAGES', 2); //Задаем количество отображаемых ссылок для перехода по страницам с сообщениями

spl_autoload_register(function ($classbook) { //Используем функцию для подключения нужным нам файлов с классами, расположенных по указанному адресу
    include $classbook . '.php';
});

$controller = new ControllerBook();
$controller->run();

$speed_end = microtime(true);
$speed = $speed_end - $speed_start;
echo "<p>Script executed in <b>$speed</b> seconds</p>";