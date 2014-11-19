<?php

class parserLoadstatisticsTask extends sfBaseTask
{
  protected function configure()
  {
    // add your own arguments here
    $this->addArguments(array(
      new sfCommandArgument('begin', sfCommandArgument::REQUIRED, 'Month and year begin'),
      new sfCommandArgument('end', sfCommandArgument::REQUIRED, 'Month and year end'),
    ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
    ));

    $this->namespace        = 'parser';
    $this->name             = 'load-statistics';
    $this->briefDescription = 'The Parser, parses statistics pages and saves it to database';
    $this->detailedDescription = '';
  }
  
  protected function prepareConnection()
  {
      new sfDatabaseManager($this->configuration);
  }

    /**
   * @param string $begin
   * @param string $end
   */
  protected function execute($arguments = array(), $options = array())
  {
      
    $this->prepareConnection();

    $tStart = time();
    echo "start\n";

    // $partsOfFileNames = array(
    //     'September 2013', 'October 2013', 'November 2013', 'May 2014', 'March 2014', 'June 2014',
    //     'July 2014', 'January 2014', 'February 2014', 'December 2013', 'August 2014', 'April 2014'
    //     );

    $dateParser = new DateParser();
    $partsOfFileNames = $dateParser->buildPartsOfFileNames($arguments['begin'], $arguments['end']);

    $db = new DbManager();
    $db->clearTable('statistics');

    foreach ($partsOfFileNames as $partOfFileName) {
        try {
            $parser = new Parser($partOfFileName);
            $processor = new Processor($parser, $db);
            $processor->saveData();
            unset($processor);
            echo "parsed rows " . $parser->getFileName() . "\n";
        } catch (Exception $e) {
            echo $e . "\n";
            exit(1);
        }
    }

    $elapsedTime = time() - $tStart;
    echo "finished\n";
    echo "elapsed " . $elapsedTime . " S\n";
  }


  

}
