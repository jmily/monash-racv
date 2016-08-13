<?php
/**
 * Created by PhpStorm.
 * User: emilychen
 * Date: 13/08/2016
 * Time: 8:08 PM
 */

namespace AppBundle\Utils;


class DateHelper
{
    /**
     * @param $dateA
     * @param $dateB
     * @return bool
     */
    public function isDateStrAGreaterThanDateStrB($dateA,$dateB)
    {
        $flag = false;

        $timeA = new \DateTime($dateA);
        $timeB = new \DateTime($dateB);

        if($timeA > $timeB) {
            $flag = true;
        }

        return $flag;
    }

    /**
     * @param $dateA
     * @param $dateB
     * @return bool
     */
    public function isDateStrGreaterThanOrEqualsToDateStrB($dateA,$dateB)
    {
        $flag = false;

        $timeA = new \DateTime($dateA);
        $timeB = new \DateTime($dateB);

        if($timeA >= $timeB) {
            $flag = true;
        }

        return $flag;
    }

    /**
     * @param $dateStrA
     * @param $dateStrB
     * @return string
     */
    public function dateStringAMinusDateStringB($dateStrA,$dateStrB) {
        $dateA = new \DateTime($dateStrA);
        $dateB = new \DateTime($dateStrB);

        $symbol = '-';
        if ($this->isDateStrGreaterThanOrEqualsToDateStrB($dateStrA,$dateStrB)) {
            $symbol = '';
        }

        $interval = $dateA->diff($dateB);
        $diff = $interval->format('%a');

        return $symbol.$diff;
    }


    /**
     * @param $dateStr
     * @param string $monthStr
     * @return string
     */
    public function getDateStrByMonthDiff($dateStr,$monthStr = '')
    {
        $date = new \DateTime($dateStr);
        $date->modify($monthStr);

        return $date->format('Y-m-d H:i:s');
    }

}