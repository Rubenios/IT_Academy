<?php

class ControllerBook
{
    private $ModelBook;
    private $ViewBook;
    private $pageNumber;
    private $action;

    public function __construct()
    {
        $this->pageNumber = isset($_GET['page']) ? intval($_GET['page']) : 1; //Присваиваем свойству $this->pageNumber значение номера страницы, передаваемого методом GET, иначе присваеваем ему единицу
        if (!empty($_POST)) $this->action = 'writeToFile'; else $this->action = 'readFromFile'; //Определяем действие: если через форму передаются данные методом POST, вызываем метод записи writeToFile, иначе - метод чтения readFromFile
    }

    public function run() //Создаем метод run() в виде try/catch, поскольку используем в своем скрипте вызов исключений Exception
    {
        try {
            $this->ModelBook = new ModelBook($this->action); //Создаем новый объект класса ModelBook
            $this->ModelBook->setMessagesPerPage(MESSAGES_PER_PAGE); //При помощи метода, вызываемого из класса ModelBook передаем объекту константу, определяющую количество сообщения на странице
            $this->ModelBook->runBook($this->pageNumber); //Запускаем метод runBook класса ModelBook, обрабатывающий все методы этого класса

            $this->ViewBook = new ViewBook($this->pageNumber, NEIGHBOR_PAGES, $this->ModelBook->getPagesCount());
            $this->ViewBook->run($this->ModelBook->getPagesMessages(), $this->ModelBook->getFormData(), $this->ModelBook->getError()); //Запускаем метод run класса ViewBook, обрабатывающий все методы этого класса и передаем ему необходимые данные, вычисленные в классе ModelBook
        } catch (Exception $error) //Перехватываем ошибки, возникшие в блоке try
        {
            echo "ERROR! <br />" . $error->getMessage();
        }
    }
}
