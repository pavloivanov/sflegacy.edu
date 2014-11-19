<?php
class DbManager {

    private $mysql;


    public function __construct() {
        $this->mysql = new PDO(
            'mysql:host=10.25.9.208;dbname=pivanov_portal_usage', 'epam', 'malls4you',
            array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\'')
        );
    }


    public function find($table) {
        $query = "SELECT * FROM $table";
        $sth = $this->mysql->prepare($query);
        $sth->execute();

        return $sth->fetchAll(PDO::FETCH_ASSOC);
    }


    public function add($table, $parameters) {
        $placeholders = $this->generatePlaceholders($parameters);
        $query = "INSERT INTO $table SET $placeholders";

        $sth = $this->mysql->prepare($query);

        return ($sth->execute($parameters)) ? $this->mysql->lastInsertId() : false;
    }


    public function upsert($table, $data) {
        $sql = 'INSERT INTO ' . $table . ' (hits, url) VALUES ';
        $insertQuery = array();
        $insertData = array();
        $n = 0;
        foreach ($data as $row) {
            $insertQuery[] = '(:hits' . $n . ', :url' . $n . ')';
            $insertData['hits' . $n] = $row['hits'];
            $insertData['url' . $n] = $row['url'];
            $n++;
        }

        if (!empty($insertQuery)) {
            $sql .= implode(', ', $insertQuery);
            $sql .= "ON DUPLICATE KEY UPDATE hits = hits + VALUES(hits)";
            $sth = $this->mysql->prepare($sql);
            $result = $sth->execute($insertData);

            if (!$result) {
                throw new Exception("Error Data wasn't inserted");
            }

            return $result;
        }
    }


    public function clearTable($table) {
        $query = "DELETE FROM $table WHERE 1";
        $sth = $this->mysql->prepare($query);

        return $sth->execute();
    }


    private function generatePlaceholders($data) {
        $placeholders = '';
        foreach ($data as $key => $value) {
            $placeholders .= $key . ' = :' .$key . ', ';
        }

        return substr($placeholders, 0, -2);
    }



    /**
     * TEMPORARY
     */
    public function executeQuery($query)
    {
        $sth = $this->mysql->prepare($query);
        $sth->execute();

        return $sth->fetchAll(PDO::FETCH_ASSOC);
    }


    function beginTransaction()
    {
        $this->mysql->beginTransaction();
    }

    function commit()
    {
        $this->mysql->commit();
    }

    function rollBack()
    {
        $this->mysql->rollback();
    }

}