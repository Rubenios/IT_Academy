<?php


class ViewBook
{
    private $pageMessages; //Массив передаем из класса ModelBook через класс ControllerBook
    private $pageNumber; //Номер страницы передаем из класса ControllerBook
    private $neighborPages; //Задаем количество отображаемых ссылок для перехода по страницам
    private $pageCount; //Количество страниц с сообщениями получаем из класса ModelBook
    private $formData; //
    private $error; //Принимаем для отображения ошибки из класса ModelBook


    public function __construct($pageNumber, $neighborPages, $pageCount) //Определяем в кострукторе свойства, значения для которых принимаем из других классов
    {
        $this->pageMessages = array();
        $this->pageNumber = $pageNumber;
        $this->neighborPages = $neighborPages;
        $this->pageCount = $pageCount;
    }

    private function viewHeader() //Выводим HTML-код начала страницы
    {
        echo '<!DOCTYPE html>
            <html>
            <head>
              <meta charset="UTF-8">
              <title>Guest book Shuliak</title>
              <link type="text/css" href="guestbook.css" rel="stylesheet">
            </head>
            <body>
            <h2>Guest Book</h2>
            <div>';
    }

    private function viewMessages() //Функция для вывода сообщений на экран
    {
        foreach ($this->pageMessages as $value) //Получаем массив сообщений для одной страницы из класса ModelBook
        {
            $viewData = explode("\t", $value); //И разбиваем его на отдельные части по разделителям \t
            echo "<p>User <b>$viewData[0]</b> wrote on <i>$viewData[1]</i><br />";
            echo "This message:<br />$viewData[2]</p>";
            echo "<hr />";
        }
    }

    private function viewPageLink() //Функция для вывода ссылок для перехода по страницам
    {
        if ($this->pageCount < 2) //Если страница всего одна - завершаем работу функции, отображение ссылок для перехода не требуется
        {
            return;
        }
        $start = $this->pageNumber - $this->neighborPages;
        if ($start < 1) $start = 1;
        $end = $this->pageNumber + $this->neighborPages;
        if ($end > $this->pageCount) $end = $this->pageCount;
        for ($i = $start; $i <= $end; $i++) {
            if ($i == $this->pageNumber)
                echo "$i &nbsp;";
            else
                echo '<a href="' . basename($_SERVER['PHP_SELF']) . '?page=' . $i . '">' . $i . '</a>&nbsp;&nbsp;';
        }
    }

    private function viewForm() //Здесь задаем HTML код формы, из которой будем отправлять сообщения
    {
        if ($this->error) {
            echo '<div><b>There are some input errors:<br />' . $this->error . '</div>';
        }
        echo '<div><form method="post" action="indexbook.php">
            Username:<br /><input type="text" name="username" maxlength="30" placeholder="Enter your name"><br />
            Message:<br /><textarea name="message" rows="5" cols="25" placeholder="Enter your message"></textarea><br />
            <input type="submit" value="Add!">
            <input type="reset" value="Empty fields!">
            </form></div>';
    }

    private function viewFooter() //Выводим HTML-код окончания страницы
    {
        echo '</div>
              </body>
              </html>';
    }

    public function run($pageMessages, $formData, $error) //Опрделяем здесь созданные выше методы и свойства, необходимые для их работы
    {
        $this->pageMessages = $pageMessages;
        $this->formData = $formData;
        $this->error = $error;
        $this->viewHeader();
        $this->viewMessages();
        $this->viewPageLink();
        $this->viewForm();
        $this->viewFooter();
    }
}
