<?php
/**
 * Created by PhpStorm.
 * User: oli2002 Oleg Ivanov
 * Date: 1/17/2023
 * Time: 3:18 PM
 */

namespace App\VacReqBundle\Util;


use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Yasumi\Yasumi;


class VacReqCalendarUtil
{
    protected $em;
    protected $security;
    protected $container;


    public function __construct( EntityManagerInterface $em, Security $security, ContainerInterface $container ) {

        $this->em = $em;
        $this->security = $security;
        $this->container = $container;
    }


    public function getHolidaysPerYear( $country, $year ) {
        $holidays = Yasumi::create('USA', $year);
        //dump($holidays);
        return $holidays;
    }

    public function getHolidaysRangeYears( $country, $startYear, $endYear ) {
        $res = array();
        foreach (range($startYear, $endYear) as $year) {
            //echo $year;
            $holidays = Yasumi::create($country, $year);
            $res[$year] = $holidays;
        }
        return $res;
    }

    //add the retrieved US holiday titles and dates for the next 20 years from the downloaded file
    // to the Platform List Manager into a new Platform list manager list titled “Holidays”
    // Title: [holiday title],
    // New “Date” Attribute for each item in this list: [date],
    // a New “Country” attribute for each item in this list, set to [US] by default for imported values) and
    // a new “Observed By” field empty for now but showing all organizational groups in a Select2 drop down menu.
    public function processHolidaysRangeYears( $country, $startYear, $endYear ) {
        $res = array();
        $count = 0;
        foreach( range($startYear, $endYear) as $year ) {
            //echo $year;
            $holidays = Yasumi::create($country, $year);
            //dump($holidays);
            //exit('111');
            //$res[$year] = $holidays;
            //$holidays = $allHolidays['holidays'];
            foreach($holidays as $holiday) {
                $res[] = $holiday.": ".$holiday->getName();

                //Store in VacReqHolidayList
                $thisHoliday = $this->findHolidayDay($holiday->getName(), $holiday);
                if( $thisHoliday ) {
                    //update
                }

                

                $count++;
            }
            //exit();
        }

        //$res = implode("<br>",$res);

        $res = "Updated $country holidays from $startYear to $endYear. Total updated holiday days is $count.";

        return $res;
    }

    public function findHolidayDay( $name, $date ) {
        $repository = $this->em->getRepository('AppVacReqBundle:VacReqHolidayList');
        $dql = $repository->createQueryBuilder('holidays');

        $dql->where("holidays.holidayName = :holidayName");
        $dql->andWhere("holidays.holidayDate = :holidayDate");

        $query = $this->em->createQuery($dql);

        $query->setParameter('holidayName', $name);
        $query->setParameter('holidayDate', $date);

        //echo "dql=".$dql."<br>";

        $holiday = $query->getResult();

        return $holiday;
    }


}