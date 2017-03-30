<?php
require_once "config_class.php";
require_once "checkvalid_class.php";
require_once "database_class.php";
abstract class GlobalClass
{
    private $db;
    private $table_name;
    protected $config;
    protected $valid;

    protected function __construct($table_name, $db)
    {
        $this->db = $db; /*объект класса DataBase*/
        $this->table_name = $table_name;
        $this->config = new Config();
        $this->valid = new CheckValid();
    }

    /*Добавление новой записи*/
    protected function add($new_values)
    {
        return $this->db->insert($this->table_name, $new_values);
    }

    protected function edit($id, $upd_fields)
    {
        return $this->db->updateOnID($this->table_name, $id, $upd_fields);
    }

    /*Удаление записи по id*/
    public function delete($id)
    {
        return $this->db->deleteOnID($this->table_name, $id);
    }

    public function deleteAll()
    {
        return $this->db->deleteAll($this->table_name);
    }

    protected function getField($field_out, $field_in, $value_in)
    {
        return $this->db->getField($this->table_name, $field_out, $field_in, $value_in);
    }

    protected function getFieldOnID($id, $field)
    {
        return $this->db->getFieldOnID($this->table_name, $id, $field);
    }

    protected function setFieldOnID($id, $field, $value)
    {
        return $this->db->setFieldOnID($this->table_name, $id, $field, $value);
    }

    /*Получить запись по id*/
    public function get($id)
    {
        return $this->db->getElementOnID($this->table_name, $id);
    }

    /*Получает все записи из таблицы*/
    public function getAll($order = "", $up = true)
    {
        return $this->db->getAll($this->table_name, $order, $up);
    }
/*Получает все записи по определенному полю*/
    protected function getAllOnField($field, $value, $order = "", $up = true)
    {
        return $this->db->getAllOnField($this->table_name, $field, $value, $order, $up);
    }
/*Получаем определенное число случайных элементов любых записей случайным образом*/
    public function getRandomElement($count)
    {
        return $this->db->getRandomElement($this->table_name, $count);
    }
/*id последней вставленной записи*/
    public function getLastID()
    {
        return $this->db->getLastID($this->table_name);
    }
/*Количество элементов в данной таблице*/
    public function getCount()
    {
        return $this->db->getCount($this->table_name);
    }
/*По некоему полю проверить существует ли значение данноего поля*/
    protected function isExists($field, $value)
    {
        return $this->db->isExists($this->table_name, $field, $value);
    }
}