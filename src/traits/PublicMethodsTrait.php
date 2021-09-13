<?php

namespace App\Traits;

trait PublicMethodsTrait
{
    /**
     * |--------------------------------------------------------
     * |                    PUBLIC METHODS
     * |--------------------------------------------------------
     */
    public function select($fields)
    {
        if (is_array($fields)) {
            $this->selectedFields = implode(",", $fields); // convert to string
        } else {
            $this->selectedFields = $fields;
        }

        return $this;
    }

    public function get($tableName, $where = [])
    {
        return $this->action("SELECT {$this->selectedFields}", $tableName, $where);
    }

    public function delete($tableName, $where = [])
    {
        return $this->action("DELETE", $tableName, $where);
    }

    public function insert($tableName, $data)
    {
        $columns = implode(',', array_keys($data));
        $values = array_values($data);

        $questionMarks = str_split(str_repeat("?", count($data)));
        $bindings = implode(",", $questionMarks);

        $sql = "INSERT INTO {$tableName} ({$columns}) VALUES ({$bindings}) ";

        return $this->query($sql, $values, true);
    }

    public function update($tableName, $data, $where = [])
    {
        $sql = "UPDATE {$tableName} SET ";

        $i = 0;
        foreach ($data as $key => $value) {
            $lastIndex = count($data) - 1;
            $qoma = $lastIndex !== $i ? "," : "";

            $sql .= " {$key} = ?{$qoma}";
            $i++;
        }

        $whereActions = $this->where($sql, $where);
        $query = $whereActions[0]; // updated sql
        $params = $whereActions[1]; // where claues data
        $setData = array_values($data); // set data

        $values = array_merge($setData, $params);

        return $this->query($query, $values, true);
    }
}
