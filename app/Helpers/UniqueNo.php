<?php

namespace App\Helpers;

/**
 * 
 * Used for generation of unique number
 * @author      Onos V. <onos@salescheck.com>
 * @copyright   2023 Sales Check Limited
 * @version			1.0 => Mar 2023
 * @link        salescheck.com
 */
class UniqueNo
{
    protected $baseYear = 2017;

    /**
     * Generate unique no using mysql
     * @param Callable $closure closure to validate the generated seed
     * @param integer $length length of the item no
     * @param boolean $ymd if time will be ymd or yymmdd
     * @param string $prefix the prefix to be added to the no
     * @return string
     */
    public function generate(Callable $closure, $length = 12, $ymd = true, $prefix = ''): string
    {
        $seed = 1;
        while($closure($no = $this->formNo($seed, $length, $ymd, $prefix))) {
            ++$seed;
        }
        return $no;
    }

    /**
     * formation of a no
     * @param integer $seed the item no
     * @param integer $length length of the item no
     * @param boolean $ymd if time will be ymd or yymmdd
     * @param string $prefix the prefix to be added to the no
     * @return string $no the generated no
     */
    protected function formNo($seed, $length, $ymd, $prefix = ''){
        if($ymd){
            $lastYear = $this->baseYear;

            for ($i=0; $i < 10; $i++) {
                $yearCollection[++$lastYear] = $i;
            }

            for ($j=65; $j <= 90; $j++) {
                $yearCollection[++$lastYear] = chr($j);
            }
            
            $lastMonth = 0;

            for ($i=0; $i < 10; $i++) {
                ++$lastMonth; 
                $monthCollection[$lastMonth] = $i;
            }

            $monthCollection[10] = 9; $monthCollection[11] = "A"; $monthCollection[12] = "B";
            
            $yesterday = 0;

            for ($i=0; $i < 10; $i++) {
                ++$yesterday; 
                $dayCollection[$yesterday] = $i;
            }

            for ($j=65; $j <= 90; $j++) {
                ++$i;
                $dayCollection[$i] = chr($j);
            }
            
            $timePart = $yearCollection[date("Y")].$monthCollection[date("n")].$dayCollection[date("j")];
        }
        else {
            $timePart = date("y").date("m").date("d");
        }
        
        if ($diff = $length-strlen($seed)) {
            for ($i=0; $i < $diff; $i++) { 
                $seed = "0".$seed;
            }
        }
        
        $no = $prefix.$timePart.$seed;
        return $no;
    }
		
}
