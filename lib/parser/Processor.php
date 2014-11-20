<?php

class Processor {

    /**
     * @property object $parser
     */
    protected $parser;


    /**
     * @propetry object $dbManager
     */
    protected $doctrineConnection;


    /**
     * @property array $data
     */
    protected $data = array();


    /**
     * @param object @parser
     */
    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
        $this->doctrineConnection = Doctrine_Manager::getInstance()->getCurrentConnection();
    }


    public function saveData()
    {
        while (false !== ($parsedRow = $this->parser->getRow())) {
            if (isset($parsedRow[0]) && isset($parsedRow[4])) {
                $this->addToBatch(array('hits' => trim($parsedRow[0]), 'url' => trim($parsedRow[4])));
            }
        }
        if (!empty($this->data)) {
            $this->saveToDb();
        }
    }


    protected function saveToDb()
    {
        try {
            $this->doctrineConnection->beginTransaction();
            $this->upsert('statistics');
            $this->data = array();
            $this->doctrineConnection->commit();
        } catch (Exception $e) {
            $this->doctrineConnection->rollback();
            echo 'Caught exception: ' . $e->getMessage() . "\n";
            exit (1);
        }  
    }
    

    protected function addToBatch($dataRow)
    {
        $this->data[] = $dataRow;
        if (count($this->data) >= 500) {
            $this->saveToDb($this->data);
        }
    }
    
    
    protected function upsert($table) {
        $sql = 'INSERT INTO ' . $table . ' (hits, url) VALUES ';
        $insertQuery = array();
        $insertData = array();
        $n = 0;
        foreach ($this->data as $row) {
            $insertQuery[] = '(:hits' . $n . ', :url' . $n . ')';
            $insertData['hits' . $n] = $row['hits'];
            $insertData['url' . $n] = $row['url'];
            $n++;
        }

        if (!empty($insertQuery)) {
            $sql .= implode(', ', $insertQuery);
            $sql .= "ON DUPLICATE KEY UPDATE hits = hits + VALUES(hits)";
            $result = $this->doctrineConnection->execute($sql, $insertData);

            if (!$result) {
                throw new Exception("Error Data wasn't inserted");
            }

            return $result;
        }
    }

}