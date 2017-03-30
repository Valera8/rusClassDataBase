<?php
require_once "global_class.php";
class User extends GlobalClass
{
    public function __construct($db)
    {
        parent::__construct("users", $db);
    }
/*Добавление нового пользователя*/
    public function addUser($login, $password, $regdate)
    {
        if (!$this->checkValid($login, $password, $regdate)) return false;
        return $this->add(array("login" => $login, "password" => $password, "regdate" => $regdate));
    }
/*Редактирование юзера*/
    public function editUser($id, $login, $password, $regdate)
    {
        if (!$this->checkValid($login, $password, $regdate)) return false;
        return $this->edit($id, array("login" => $login, "password" => $password, "regdate" => $regdate));
    }
/*Проверить на существование логина*/
    public function isExists($login) /*-------------Добавил сам: $field, -------------------------------*/
    {
        return $this->isExists("login", $login);
    }
/*Получение всех данных пользователей по логину*/
    public function getUserOnLogin($login)
    {
        $id = $this->getField("id", "login", $login);
        return $this->get($id);
    }
/*Проверить все данные на корректность*/
    private function checkValid($login, $password, $regdate)
    {
        if (!$this->valid->validLogin($login)) return false;
        if (!$this->valid->validHash($password)) return false;
        if (!$this->valid->validTimeStamp($regdate)) return false;
        return true;
    }
}