<?php

class ModelBook
{
    private $formData; //Свойство, получающее данные из массива $_POST
    private $messagesArray; //Массив сообщений, получаемых считыванием из файла
    private $messagesCount; //Число сообщений из массива сообщений $messagesArray
    private $messagesPerPage; //Число сообщений на одной странице (его же задаем константой MESSAGES_PER_PAGE)
    private $pageCount; //Общее число страниц с сообщениями
    private $pageNumber; //Номер текущей страницы
    private $pageMessages; //Массив сообщений на одной странице
    private $action; //Создаем свойство, через которое будем выбирать последовательность вызова методов чтения или записи
    private $error; //Создаем свойство для отображения ошибок при обработке данных из формы

    public function __construct($action) //Присваиваем данные по умолчанию, чтобы скрипт отработал при отсутствии входных данных
    {
        $this->formData['username'] = '';
        $this->formData['message'] = '';
        $this->messagesArray = array();
        $this->messagesCount = 0;
        $this->messagesPerPage = 1;
        $this->pageCount = 1;
        $this->pageNumber = 1;
        $this->pageMessages = array();
        $this->action = $action;
        $this->error = '';
    }

    private function textControl($value, array $minmax) //Функция для проверки введенных пользователем данных
    {
        $this->formData[$value] = $_POST[$value]; //Передаем свойству formData данные, переданные через форму методом $_POST
        $this->formData[$value] = trim($this->formData[$value]); //Обрезаем пробелы
        $data = mb_strlen($this->formData[$value]); //Подсчитываем количество символов
        if ($data < $minmax[0] || $data > $minmax[1]) //Проверяем, укладывается ли длина имени и сообщения в диапазон, заданный в методе writeToFile
            $this->error .= "Maximum length in field \"$value\" must be from $minmax[0] to $minmax[1] characters!<br />";
        if (preg_match('/[^ \-A-Za-zа-яА-Я_0-9]+/u', $this->formData['username']))
            $this->error = "You must use only latin, cyrillic characters, \"space\" and \"_\" symbols in your name!<br />";
        $this->formData[$value] = str_replace("\t", " ", $this->formData[$value]); //Заменяем введенные пользователем символы табуляции на пробел
        $this->formData[$value] = str_replace("\r\n || \n\r || \r || \n", "<br />", $this->formData[$value]); //Заменяем введенные пользователем символы переноса строки и возврата каретки на тег <br />
        //$this->formData[$value] = stripslashes($this->formData[$value]); //Удаляем экранирующие обратные слэши
        //$this->formData[$value] = strip_tags($this->formData[$value]); //Удаляем HTML и PHP теги
        $this->formData[$value] = htmlspecialchars($this->formData[$value], ENT_QUOTES | ENT_HTML5, "UTF-8"); //Преобразуем спецсимволы в HTML сущности
    }

    private function writeToFile() //Функция для записи в файл введенных и отформатированных пользовательских данных
    {
        $this->textControl('username', array(2, 20)); //Передаем данные, полученные в функции textControl
        $this->textControl('message', array(2, 1000));
        if ($this->error) return;
        $s = $this->formData['username'] . "\t" . date('l, j F Y \a\t H:i') . "\t" . $this->formData['message'] . "\n"; //Создаем переменную, в которую помещаем данные из двух полей и дату в одну неразрывную строку через символы табуляции
        file_put_contents("text.txt", $s, FILE_APPEND | LOCK_EX); //И записываем эти данные в текстовый файл
        header('Location: http://' . $_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME']);
        exit;
    }

    private function readFromFile() //Функция для чтения и выборки данных из файла
    {
        $f = "text.txt";
        $n = "Admin \t " . date('l, j F Y \a\t H:i') . " \t Attention! Original file with messages <b>" . $f . "</b> was missing! This is a new created file!\n"; //Создаем переменную, в которую помещаем сообщение по умолчанию, которое выведется если файл для записи будет отсутствовать или будет пустым
        if (!file_exists($f) || !file_get_contents($f)) //Проверяем наличие файла с сообщениями и является ли он пустым
        {
            file_put_contents("text.txt", $n, FILE_APPEND | LOCK_EX); //Если файла или содержимого в нем нет - создаем новый и помещаем в него данные по умолчанию
        } else {
            $this->messagesArray = file($f); //Если файл с сообщениями и содержимым присутствует - читаем его содержимое в массив
        }
        $this->messagesArray = array_reverse($this->messagesArray); //Разворачиваем массив для того, чтобы последний записанный в него элемент считывался первым
        $this->messagesCount = count($this->messagesArray); //Подсчитываем общее количество сообщений
        $this->pageCount = ceil($this->messagesCount / $this->messagesPerPage); //Подсчитываем общее количество страниц для вывода сообщений
        if ($this->pageNumber < 1 || $this->pageNumber > $this->pageCount) {
            throw new Exception("<b>This page does not exist or message file is empty! Please enter correct page number or reload the page!</b>");
        }
        $messagesOffset = $this->messagesPerPage * ($this->pageNumber - 1); //Задаем нужный нам отступ, с которого надо сделать выборку сообщений для отображения на одной странице
        $this->pageMessages = array_slice($this->messagesArray, $messagesOffset, $this->messagesPerPage); //Определяем массив сообщений для вывода на одну страницу
    }

    public function getPagesCount() //Функция для передачи количества страниц в класс ViewBook (поcкольку класс ViewBook пользуется значением данного свойства, а высчитывается оно в классе ModelBook)
    {
        return $this->pageCount;
    }

    public function getPagesMessages() //Функция для передачи массива сообщений для одной страницы в класс ViewBook (поcкольку класс ViewBook пользуется значением данного свойства, а высчитывается оно в классе ModelBook)
    {
        return $this->pageMessages;
    }

    public function getFormData() //Функция для передачи данных вне данного класса
    {
        return $this->formData;
    }

    public function getError() //Функция для передачи данных вне данного класса
    {
        return $this->error;
    }

    public function setMessagesPerPage($messagesPerPage) //Функция для передачи данных вне данного класса
    {
        $this->messagesPerPage = $messagesPerPage;
    }

    public function runBook($pageNumber = 1) //Функция для передачи данных вне данного класса
    {
        $this->pageNumber = $pageNumber;
        $this->{$this->action}();
    }
}