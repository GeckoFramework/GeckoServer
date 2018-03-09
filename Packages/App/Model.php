<?php

namespace App;

abstract class Model
{
    function __construct()
    {
        Kernel::implementComponents($this, 'App\Interfaces\Model');
        Kernel::implementComponents($this, 'App\Interfaces\Database');
    }

    public function get()
    {
        $agents = $this->Database->query('SELECT * FROM ' . static::table);
        return $agents;
    }

    public function create($values)
    {
        if (is_array($values) && count($values) > 0) {
            $columns = array_keys($values);
            $queryColumns = implode(", ", $columns);
            $queryValues = ":" . implode(", :", $columns);
            $query = 'INSERT INTO ' . static::table . ' (' . $queryColumns . ') VALUES (' . $queryValues . ')';
            return $this->Database->query($query, $values);
        }
    }

    public function update($values, $where)
    {
        if (is_array($values) && count($values) > 0) {
            $setFields = "";
            foreach($values as $chiave => $valore){
                $setFields .= $chiave . " = :" . $chiave . ",";
            }
            $setFields = rtrim($setFields, ",");
            if(array_key_exists('where', $where) && array_key_exists('arguments', $where) && is_array($where['arguments']) ) {
                $values = array_merge($values, $where['arguments']);
            }
            $query = 'UPDATE ' . static::table . ' SET ' . $setFields . ' WHERE ' . $where['where'];
            return $this->Database->query($query, $values);
        }
    }

    public function delete($where)
    {
        $values = [];
        if(array_key_exists('where', $where) && array_key_exists('arguments', $where) && is_array($where['arguments']) ) {
            $values = $where['arguments'];
        }
        $query = 'DELETE FROM ' . static::table . ' WHERE ' . $where['where'];
        return $this->Database->query($query, $values);
    }
}