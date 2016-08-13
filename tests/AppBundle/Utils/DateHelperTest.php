<?php
/**
 * Created by PhpStorm.
 * User: emilychen
 * Date: 13/08/2016
 * Time: 8:13 PM
 */

namespace tests\AppBundle\Utils;


use AppBundle\Utils\DateHelper;

class DateHelperTest extends \PHPUnit_Framework_TestCase
{
    protected $helper;

    public function setUp()
    {
        parent::setUp();
        $this->helper = new DateHelper();
    }

    public function testIsDateStrAGreaterThanDateStrB()
    {
        $dateA = '2016-10-12 13:00:00';
        $dateB = '2016-10-12 10:00:00';


        $dateC = '2016-10-09 00:00:00';
        $dateD = '2016-07-30 00:00:00';

        $dateE = '0000-00-00 00:00:00';
        $dateF = '2016-10-10 00:00:00';

        $this->assertTrue($this->helper->isDateStrAGreaterThanDateStrB($dateA,$dateB));
        $this->assertTrue($this->helper->isDateStrAGreaterThanDateStrB($dateC,$dateD));
        $this->assertTrue($this->helper->isDateStrAGreaterThanDateStrB($dateF,$dateE));


    }

    public function testDateStringAMinusDateStringB()
    {
        $dateA = '2016-10-10 10:00:00';
        $dateB = '2016-10-12 10:00:00';

        $this->assertEquals('-2',$this->helper->dateStringAMinusDateStringB($dateA,$dateB));

        $dateC = '2016-10-10 10:00:01';
        $dateD = '2016-10-12 10:00:00';

        $this->assertEquals('-1',$this->helper->dateStringAMinusDateStringB($dateC,$dateD));

        $dateE = '2016-10-12 10:00:01';
        $dateF = '2016-10-12 10:00:00';

        $this->assertEquals('0',$this->helper->dateStringAMinusDateStringB($dateE,$dateF));

        $dateG = '2016-10-22 10:00:00';
        $dateH = '2016-10-12 10:00:00';

        $this->assertEquals('10',$this->helper->dateStringAMinusDateStringB($dateG,$dateH));
    }

    public function testGetDateStrByMonthDiff()
    {
        $dateA = '2016-10-10 00:00:00';
        $monthStr = '-1 month';

        $this->assertEquals('2016-09-10 00:00:00',$this->helper->getDateStrByMonthDiff($dateA,$monthStr));


        $dateB = '2016-10-10 10:00:01';
        $monthStr = '+1 month';
        $this->assertEquals('2016-11-10 10:00:01',$this->helper->getDateStrByMonthDiff($dateB,$monthStr));
    }

}
