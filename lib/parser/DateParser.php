<?php
class Dateparser
{

    /**
    * @param string $begin  (format: 'month year')
    * @param string $end (format: 'month year')
    */
    public function buildPartsOfFileNames($begin, $end)
    {

        $months = array();
        for ($i=1; $i <= 12; ++$i) {
            $months[] = date('F', mktime(0, 0, 0, $i, 1, 2000));
        }

        $beginArr = explode(' ', $begin, 2);
        $endArr = explode(' ', $end, 2);

        $errorMessage = "Arguments passed in wrong format. Right example:\n 'September 2013', August 2014";
        if (2 != count($beginArr) &&  2 != count($endArr)) {
            throw new Exception($errorMessage, 1);
            exit(1);
        }

        $beginMonth = $beginArr[0];
        $beginYear  = $beginArr[1];
        $endMonth   = $endArr[0];
        $endYear    = $endArr[1];

        $partsOfFilenames = array();

        for($i = (array_search($beginMonth, $months)); $i < count($months); $i++) {
            $partsOfFilenames[] = $months[$i] . ' ' . $beginYear;
            if ($months[$i] == $endMonth && $beginYear == $endYear) {
                break;
            }
            if ($months[$i] === 'December') {
                $i = -1;
                $beginYear++;
            }
        }

        return $partsOfFilenames;
    }

}