<?php

class Parser {

    /**
     * @property string $fileName
     */
    protected $fileName;

    /**
     * @property resouce $handle
     */
    protected $handle;

    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * @param string $part
     */
    public function __construct($part)
    {
        $this->fileName = $this->prepareFileName($part);
        $this->openFile();
    }


    public function __destruct()
    {
        fclose($this->handle);
    }


    public function openFile()
    {
        $this->handle = fopen($this->fileName, 'r');

        if ($this->handle === false) {
            throw new Exception("Error: File" . $this->fileName . " can't be opened\n");
        }

        // Skip header
        $needle = "         Hits            KBytes      URL\n";
        do {
            $row = fgets($this->handle);
        } while (false === strpos($row, $needle));
    }


    /**
     * @param string
     * @return array
     */
    public function parseRow($row) {
        return preg_split('/\s+/', $row);
    }


   /**
    * @param string $part
    * @return string
    */
    protected function prepareFileName($part)
    {
        return 'web/uploads/statistic_pages/Usage Statistics for LFVSF2010000427 - ' . $part . ' - URL.html';
    }

    /**
     * @return array $parseRow
     */
    public function getRow()
    {
        $row = fgets($this->handle);

        if (strpos($row, "</font></pre>\n") !== false) {
            return false;
        }

        return $this->parseRow($row);
    }

}