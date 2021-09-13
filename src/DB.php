<?php

namespace App;

use App\Traits\AccessorsTrait;
use App\Traits\PublicMethodsTrait;

class DB
{
    use AccessorsTrait, PublicMethodsTrait;

    private static $instance = null;

    private
        $pdo,
        $query,
        $results,
        $count = 0,
        $error = false;

    private $selectedFields = "*";

    /**
     * Constructor
     */
    public function __construct()
    {
        try {
            $this->pdo = new \PDO(
                "mysql:host=" . Config::get("host") . ";dbname=" . Config::get("database"),
                Config::get("user"),
                Config::get("password")
            );
        } catch (\Exception $e) {
            die($e->getMessage());
        }
    }

    /**
     * Get database instance
     * @return DB
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new DB;
            // something added
        }

        return self::$instance;
    }

    /**
     * Execute an SQL statament along with data/parameters
     * @param string SQL statement
     * @param array Array of parameters
     * @return DB|bool
     */
    private function query($sql, $params = [], bool $set = false)
    {
        if ($this->query = $this->pdo->prepare($sql)) {
            foreach ($params as $index => $param) {
                $this->query->bindValue(($index + 1), $param);
            }

            if ($this->query->execute()) {
                // If insert OR update
                if ($set) {
                    return true;
                }

                $this->results = $this->query->fetchAll(\PDO::FETCH_OBJ);
                $this->count = $this->query->rowCount();
            } else {
                $this->error = true;
            }
        }

        return $this;
    }

    /**
     * Perform a specific action (GET, DELETE etc..)
     * @param string $action
     * @param string $tableName
     * @param array $where
     */
    private function action($action, $tableName, $where = [])
    {
        $sql = "{$action} FROM {$tableName}";

        // If conditions are passed to the method
        $whereActions = $this->where($sql, $where);
        $query = $whereActions[0];
        $params = $whereActions[1];

        if (!$this->query($query, $params)->error()) {
            return $this;
        }

        return false;
    }

    /**
     * Appends the `WHERE` clause to the query and return updated query
     * @param string $sql
     * @param array $sql
     */
    private function where($sql, $where = [])
    {
        $params = [];

        if (count($where)) {
            // If array id 2D (multiple conditions are passed)
            if (is_array($where[0])) {
                foreach ($where as $index => $w) {
                    $sql = $this->doWhereLogic($w, $params, $sql, $index);
                }
            } else {
                $sql = $this->doWhereLogic($where, $params, $sql);
            }
        }

        return [$sql, $params];
    }

    /**
     * Perform some where logic
     * @param array $w
     * @param array $params
     * @param string $sql
     * @param null|int $index
     * @return string $sql
     */
    private function doWhereLogic($w, &$params, $sql, $index = null)
    {
        $operators = ['=', ">", "<", ">=", "=<"];

        $column = $w[0];
        $operator = $w[1];
        $value = $w[2];

        array_push($params, $value);

        if (in_array($operator, $operators)) {
            // Check whether 2D array (multiple conditions) or not
            $clauseKeyword = !is_null($index)
                ? ($index === 0  ? "WHERE" : " AND")
                : "WHERE";

            $sql .= " {$clauseKeyword} {$column} {$operator} ?";
        }

        return $sql;
    }
}
