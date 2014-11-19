<?php

class Processor {

    /**
     * @property object $parser
     */
    protected $parser;


    /**
     * @propetry object $dbManager
     */
    protected $dbManager;


    /**
     * @property array $data
     */
    protected $data = array();


    /**
     * @param object @parser
     * @param object $dbManager
     */
    public function __construct(Parser $parser, $dbManager)
    {
        $this->parser = $parser;
        $this->dbManager = $dbManager;
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


    /**
     * @param string $dataRow
     */
    protected function saveToDb()
    {
        try {
            $this->dbManager->beginTransaction();
            $this->dbManager->upsert('statistics', $this->data);
            $this->data = array();
            $this->dbManager->commit();
        } catch (Exception $e) {
            $this->dbManager->rollBack();
            echo 'Caught exception: ' . $e->getMessage() . "\n";
            exit;
        }
        
        
    }


    protected function addToBatch($dataRow)
    {
        $this->data[] = $dataRow;
        if (count($this->data) >= 500) {
            $this->saveToDb($this->data);
        }
    }

}