<?php
require_once "config_class.php";
require_once "checkvalid_class.php";
/*Класс для базы данных*/
class DataBase
{
    private $config;
    private $mysqli;/*идентификатор соединения*/
    private $valid; /*экземпляр класса CheckValid*/
    public function __construct ()
    {
        $this->config = new Config();
        $this->valid = new CheckValid();
    /* и подключаемся к базе данных*/
        $this->mysqli = new mysqli($this->config->host, $this->config->user, $this->config->password, $this->config->db);
        $this->mysqli->query("SET NAMES 'utf8'");
    }
/*Функция отправляет запросы и возвращает ответы*/
    private function query ($query)
    {
        return $this->mysqli->query ($query);
    }
/*Выборка*/
    private function select ($table_name, $fields, $where = "", $order = "", $up = true, $limit = "")
    {
    /*составляем запрос. Перебираем все поля*/
        for ($i = 0; $i < count($fields); $i++)
        {
        /*При условии, что это не является скобочками, не является звездочкой (все поля) тогда заменяем $fields на `$fields` */
            if ((strpos($fields[$i], "(") === false) && ($fields[$i] != "*")) $fields[$i] = "`" . $fields[$i] . "`";
        }
    /*Превращаем этот массив в строку*/
        $fields = implode(",", $fields);
    /*создаем имя таблицы*/
        $table_name = $this->config->db_prefix . $table_name;
    /*Если сортировка не задана, сортируем по id*/
        if (!$order) $order = "ORDER BY `id`";
        else
        {
            if ($order != "RAND()")
            {
                $order = "ORDER BY `$order`";
                if (!$up) $order .= " DESC";
            }
            /*Если требуются случайные записи:*/
            else $order = "ORDER BY $order";
        }
        if ($limit) $limit = "LIMIT $limit";
        /*Если префикс указан, то запрос такой:*/
        if ($where) $query = "SELECT $fields FROM $table_name WHERE $where $order $limit";
        else $query = "SELECT $fields FROM $table_name $order $limit";
        $result_set = $this->query($query);
        if (!$result_set) return false;
        /*Этот запрос преобразовать в двумерный массив*/
        $i = 0;
        while ($row = $result_set->fetch_assoc())
        {
            $data[$i] = $row;
            $i++;
        }
        $result_set->close();
        return $data; /*Вместо $result_set возвращаем двумерный массив, т.к. с ним проще работать*/
    }
/*Добавлять записи*/
    public function insert ($table_name, $new_values)
    {
        $table_name = $this->config->db_prefix . $table_name;
        $query = "INSERT INTO $table_name (";
        foreach ($new_values as $field => $value) $query .= "`" . $field . "`,";
        $query = substr($query, 0, -1); /*Обрезать последнюю запятую*/
        $query .= ") VALUES (";
        foreach ($new_values as $value) $query .= "'" . addcslashes($value) . "',";
        $query = substr($query, 0, -1);
        $query .= ")";
        return $this->query($query);
    }
/*Обновление записей. $upd_fields - поля, которые обновляем и $where - предикат условия, по которому обновляем*/
    private function update($table_name, $upd_fields, $where)
    {
        $table_name = $this->config->db_prefix . $table_name;
        $query = "UPDATE $table_name SET";
        foreach ($upd_fields as $field => $value) $query .= "`$field` = '" . addcslashes($value) . "',";
       $query = substr($query, 0, -1);
        if ($where)
        {
            $query .= " WHERE $where";
            return $this->query($query);
        }
        else return false;
    }
/*Удаление записи по определенному условию*/
    public function delete ($table_name, $where = "")
    {
        $table_name = $this->config->db_prefix . $table_name;
        if ($where)
        {
            $query = "DELETE FROM $table_name WHERE$where";
        }
        else return false;
    }
/*Очищение таблицы*/
    public function deleteAll ($table_name)
    {
        $table_name = $this->config->db_prefix . $table_name;
        $query = "TRUNCATE TABLE `$table_name`";
    }
/*Возвращать значение поля по заданному значению другого поля в этой же таблице $field_out - поле вернуть, $field_in - поле известно, $value_in - значение этого поля*/
    public function getField ($table_name, $field_out, $field_in, $value_in)
    {
    /*Это поле должно быть уникальным*/
        $data = $this->select ($table_name, array($field_out), "`$field_in` = '" . addcslashes($value_in) . "'"); /* "`$field_in` = '" . addcslashes($value_in) . "'" - это WHERE. $data - двумерный массив со всеми данными*/
        if (count($data) != 1) return false;
        return $data[0][$field_out];
    }
/* Получение поля, зная id*/
    public function getFieldOnID ($table_name, $id, $field_out)
    {
    /*Существует ли данное id в данной таблице*/
        if (!$this->existsID ($table_name, $id))
        {
            return false;
        }
        return$this->getField($table_name, $field_out, "id", $id);
    }
/*Получение всех записей из таблицы*/
    public function getAll($table_name, $order, $up)
    {
        return$this->select($table_name, array("*"), "", $order, $up);
    }
/*Удалить запись по id*/
    public function deleteOnID ($table_name, $id)
    {
        if (!$this->existsID ($table_name, $id))
        {
            return false;
        }
        return $this->delete($table_name, "`id` = '$id");
    }
/*Изменить значение определенного поля*/
    public function setField ($table_name, $field, $value, $field_in, $value_in)
    {
        return $this->update($table_name, array($field => $value), "`$field_in` = '" . addcslashes($value_in) . "' ");
    }
/*То же по id*/
    public function setFieldOnID ($table_name, $id, $field, $value)
    {
        if (!$this->existsID ($table_name, $id))
        {
            return false;
        }
        return $this->setField($table_name, $field, $value, "id", $id);
    }
/*Возвращать запись целиком по id*/
    public function getElementOnID ($table_name, $id)
    {
        if (!$this->existsID ($table_name, $id))
        {
            return false;
        }
        $arr = $this->select($table_name, array("*"), "`id` = '$id'");
        return $arr[0];
    }
/*Возвращать случайные записи в определенном количестве*/
    public function getRandomElements ($table_name, $count)
    {
        return $this->select($table_name, array("*"), "", "RAND()", true, $count);
    }
/*Узнать количество записей в таблице*/
    public function getCount ($table_name)
    {
        $data = $this->select($table_name, array("COUNT(`id`)"));
        return $data[0]["COUNT(`id`)"];
    }
/*Проверка на существование определенной записи в некоторой таблице*/
    public function isExists ($table_name, $field, $value)
    {
        $data = $this->select($table_name, array("id"), "`$field` = '" . addcslashes($value) . "'");
        if (count($data) === 0) return false;
        return true;
    }

    private function existsID ($table_name, $id)
    {
        if (!$this->valid->validID($id)) return false;
    /*Получаем запись*/
        $data = $this->select($table_name, array("id"), "`id` = '" . addcslashes($id) . "'");
        if (count($data) === 0) return false;
        return true;
    }
/*Последний (максимальный) ID в таблице*/
    public function getId ($table_name)
    {
        $data = $this->select($table_name, array("id"));
        $last = array_pop($data);
        return $last["id"];
    }
/*Максимальное значение у заданного поля в заданной табилице*/
    public function getMax ($table_name, $field)
    {
        $data = $this->select($table_name, array($field));
        $max = max ($data);
        return $max["$field"];
    }
/*Уничтожение объекта*/
    public function __destruct()
    {
        if ($this->mysqli) $this->mysqli->close();
    }

}