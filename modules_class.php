<?php
/*Шаблонизатор*/
require_once "config_class.php";
require_once "article_class.php";
require_once "section_class.php";
require_once "user_class.php";
require_once "menu_class.php";
require_once "banner_class.php";
require_once "message_class.php";

abstract class Modules
{
    protected $config;
    protected $article;
    protected $section;
    protected $user;
    protected $menu;
    protected $banner;
    protected $message;
    protected $data; /*Массив с данными*/
    public function __construct($db) /*передаем объект от базы данных*/
    {
        session_start();
    /*Создаем объекты*/
        $this->config = new Config();
        $this->article = new Article($db);
        $this->section = new Section($db);
        $this->user = new User($db);
        $this->menu = new Menu($db);
        $this->banner = new Banner($db);
        $this->message = new Message();
    /*Создадим массив с данными. Все данные обработать проверочной функцией htmlspecialchars */
        $this->data = $this->secureData($_GET);
    }

    public function getContent() /*Это уже для главной страницы*/
    {/*Заменяем*/
        $sr["title"] = $this->getTitle();
        $sr["meta_desc"] = $this->getDescription();
        $sr["meta_key"] = $this->getKeyWords();
        $sr["menu"] = $this->getMenu();
        $sr["auth_user"] = $this->getAuthUser();
        $sr["banners"] = $this->getBanners();
        $sr["top"] = $this->getTop();
        $sr["middle"] = $this->getMiddle();
        $sr["bottom"] = $this->getBottom();
        return $this->getReplaceTemplate($sr, "main"); /* main - название шаблона*/
    }
    abstract protected function getTitle();
    abstract protected function getDescription();
    abstract protected function getKeyWords();
    abstract protected function getMiddle();

    protected function getMenu()
    {
        $menu = $this->menu->getAll();
        $text = ""; /*-----------------------Добавил сам-----------------*/
        for ($i =0; $i < count($menu); $i++)
        {
            $sr["title"] = $menu[$i]["title"];
            $sr["link"] = $menu[$i]["link"];
            $text .= $this->getReplaceTemplate($sr, "menu_item");
        }
        return $text;
    }
    protected function getAuthUser()
    {
        $sr["message_auth"] = "";
        return $this->getReplaceTemplate($sr, "form_auth");
    }
    protected function getBanners()
     {
        $banners = $this->banner->getAll();
        $text = ""; /*-----------------------Добавил сам-----------------*/
        for ($i = 0; $i < count($banners); $i++)
        {
            $sr["code"] = $banners[$i]["code"];
            $text .= $this->getReplaceTemplate($sr, "banner");
        }
        return $text;
    }
    protected function getTop()
    {
        return "";
    }
    protected function getBottom   ()
    {
        return "";
    }
    private function secureData($data)
    {
        foreach($data as $key => $value)
        {  /*Если это массив, то рекурсивно вызвать данную функцию еще раз*/
            if (is_array($value)) $this->secureData($value);
            else $data[$key] = htmlspecialchars($value);
        }
        return $data;
    }
/*Методы шаблонизатора. $name - название шаблона*/
    protected function getTemplate($name)
    {
        $text = file_get_contents($this->config->dir_tmpl . $name . ".tpl");
        return str_replace("%address%", $this->config->address, $text);
    }
    /*Заменять сразу много элементов, получив один масссив*/
        protected function getReplaceTemplate($sr, $template) /*где $sr, $template - массив данных, название шаблона*/
    {
        return $this->getReplaceContent($sr, $this->getTemplate($template));
    }
    private function getReplaceContent($sr, $content)
    {
        $search = array();/*Элементы найти*/
        $replace = array();/*Элементы заменить*/
        $i = 0;
        foreach ($sr as $key => $value)
        {
            $search[$i] = "%$key%";
            $replace[$i] = $value;
            $i++;
        }
        return str_replace($search, $replace, $content);
    }
}