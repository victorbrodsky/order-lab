<?php
/**
 * Created by PhpStorm.
 * User: oli2002 Oleg Ivanov
 * Date: 1/17/2023
 * Time: 3:18 PM
 */

namespace App\VacReqBundle\Util;


use App\VacReqBundle\Entity\VacReqHolidayList;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
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
        $testing = false;
        //$testing = true;
        $userSecUtil = $this->container->get('user_security_utility');
        $user = $this->security->getUser();

        $countryEntity = $this->em->getRepository('AppUserdirectoryBundle:Countries')->findOneByAbbreviation($country);
        if( !$countryEntity ) {
            throw new \Exception( 'Countries is not found by abbreviation=' . $country );
        }

        //$defaultInstitutions = $this->em->getRepository('AppUserdirectoryBundle:Institution')->findOneByAbbreviation($country);
        $defaultInstitutions = $userSecUtil->getSiteSettingParameter('institutions','vacreq');
        //echo '$defaultInstitutions count='.count($defaultInstitutions)."<br>";
        //foreach($defaultInstitutions as $defaultInstitution) {
        //    echo 'defaultInstitutions='.$defaultInstitution."<br>";
        //}
        if( !$defaultInstitutions || count($defaultInstitutions) == 0 ) {
            throw new \Exception( 'Default instance maintained for the following institutions not found in vacreq site settings' );
        }

        $res = array();
        $countAdded = 0;
        $countUpdated = 0;
        foreach( range($startYear, $endYear) as $year ) {
            //echo $year;
            $holidays = Yasumi::create($country, $year);
            //dump($holidays);
            //exit('111');
            //$res[$year] = $holidays;
            //$holidays = $allHolidays['holidays'];
            foreach($holidays as $holiday) {
                //echo $holiday.": ".$holiday->getName()."<br>";
                //dump($holiday);
                //exit();
                //$res[] = $holiday.": ".$holiday->getName();

                //$holidayDate = \DateTime::createFromFormat('Y-m-d', $holiday."");
                //echo "holidayDate=".$holidayDate->format("Y-m-d");
                //exit();

                $holidayName = $holiday->getName();
                //$holiday = '2021-01-01';
                //2022-05-25 => $year = 2022
                list($year, $month, $day) = explode('-', $holiday);
                $uniqueNameStr = $year."-".$holidayName; //year-holidayName
                //exit("uniqueNameStr=$uniqueNameStr");

                if( $holiday && $holidayName ) {
                    //ok
                } else {
                    //Skip if name or date empty
                    continue;
                }

                //Update if (a) holiday title AND year AND country are the same, but the month OR date are different
                $thisHoliday = $this->findHolidayDay($holidayName,$holiday,$country,"same-title-year-country");
                if( $thisHoliday ) {
                    //echo "thisHoliday exists: same-title-year-country <br>";
                    //exit();
                    //Update date
                    $holidayDate = \DateTime::createFromFormat('Y-m-d', $holiday."");
                    //echo "holidayDate=".$holidayDate->format("Y-m-d");
                    //exit();
                    $thisHoliday->setHolidayDate($holidayDate);

                    $res[] = "Updated date (ID ".$thisHoliday->getId()."): ".$holiday.": ".$holidayName;
                    $countUpdated++;

                    if( !$testing ) {
                        $this->em->flush();
                    }

                    continue;
                }

                //Update if (b) holiday date AND country are the same, but the holiday title is different.
                $thisHoliday = $this->findHolidayDay($holidayName,$holiday,$country,"same-date-country");
                if( $thisHoliday ) {
                    //echo "thisHoliday exists: same-date-country <br>";
                    //exit();
                    //Update title
                    $thisHoliday->setHolidayName($holidayName);

                    $res[] = "Updated name (ID ".$thisHoliday->getId()."): ".$holiday.": ".$holidayName;
                    $countUpdated++;

                    if( !$testing ) {
                        $this->em->flush();
                    }

                    continue;
                }

                $thisHoliday = $this->findHolidayDay($holidayName,$holiday,$country,"");
                //echo "thisHoliday count=".count($thisHoliday)."<br>";
                if( $thisHoliday ) {

                    if(0) {
                        //fix: update title to format: name-year
                        //exit("update uniqueNameStr=$uniqueNameStr");
                        $thisHoliday->setName($uniqueNameStr);
                        if( !$testing ) {
                            $this->em->flush();
                        }
                        continue;
                    }

                    if(0) {
                        if( 1 || count($thisHoliday->getInstitutions()) == 0 ) {
                            $thisHoliday->setInstitutions($defaultInstitutions);
                            $res[] = "Add institutions to holiday (ID ".$thisHoliday->getId()."): ".$holiday.": ".$holidayName;
                            $countUpdated++;

                            if( !$testing ) {
                                $this->em->flush();
                            }
                        }
                        continue;
                    }

                    $res[] = "Skip existing holiday (ID ".$thisHoliday->getId()."): ".$holiday.": ".$holidayName;

                    continue;
                }


                //Store in VacReqHolidayList
                //exit('creating new VacReqHolidayList');
                $holidayEntity = new VacReqHolidayList($user);

                //set default values
                //$nameStr = $holiday.": ".$holidayName;

                $holidayEntity = $userSecUtil->setDefaultList($holidayEntity,0,$user,$uniqueNameStr);
                $holidayEntity->setType('user-added');

                $holidayEntity->setHolidayName($holidayName);
                $holidayDate = \DateTime::createFromFormat('Y-m-d', $holiday."");
                $holidayEntity->setHolidayDate($holidayDate);
                $holidayEntity->setCountry($countryEntity);
                $holidayEntity->setInstitutions($defaultInstitutions);

                $res[] = "Added: ".$holiday.": ".$holidayName;

                if( !$testing ) {
                    $this->em->persist($holidayEntity);
                    $this->em->flush();
                }

                $countAdded++;
            }
            //exit();
        }

        //echo implode("<br>",$res);

        $res = "Updated $country holidays from $startYear to $endYear.".
            " Total added holiday days is $countAdded.".
            " Total updated holiday days is $countUpdated.<br>".
            implode("<br>",$res)
        ;

        return $res;
    }

    public function findHolidayDay( $name, $date, $country, $sameStr=NULL ) {
        $repository = $this->em->getRepository('AppVacReqBundle:VacReqHolidayList');
        $dql = $repository->createQueryBuilder('holidays');

        $dql->leftJoin("holidays.country", "country");

        //(a) holiday title AND year AND country are the same, but the month OR date are different
        if( $sameStr == "same-title-year-country" ) {
            //echo "a) same-title-year-country <br>";
            $dql->andWhere("holidays.holidayName = :holidayName");
            $dql->andWhere("country.abbreviation = :country");

            //2022-05-25 => $year = 2022
            list($year, $month, $day) = explode('-', $date);
            //same year
            //Using YEAR from  beberlei/DoctrineExtensions
            //Might use to_char (https://stackoverflow.com/questions/50890053/doctrine2-year-month-day-or-date-format-for-postgresql)
            $dql->andWhere("YEAR(holidays.holidayDate) = :holidayYear");
            //diff month and date
            $dql->andWhere("holidays.holidayDate != :holidayDate");

            $query = $this->em->createQuery($dql);

            $query->setParameter('holidayName', $name);
            $query->setParameter('holidayYear', $year);
            $query->setParameter('holidayDate', $date);
            $query->setParameter('country', $country);
        }

        //(b) holiday date AND country are the same, but the holiday title is different
        elseif( $sameStr == "same-date-country" ) {
            //echo "b) same-date-country <br>";
            $dql->andWhere("country.abbreviation = :country");
            $dql->andWhere("holidays.holidayDate = :holidayDate");
            $dql->andWhere("holidays.holidayName != :holidayName");

            $query = $this->em->createQuery($dql);

            $query->setParameter('holidayName', $name);
            $query->setParameter('holidayDate', $date);
            $query->setParameter('country', $country);
        }
        else {
            $dql->andWhere("holidays.holidayName = :holidayName");
            $dql->andWhere("country.abbreviation = :country");
            $dql->andWhere("holidays.holidayDate = :holidayDate");

            $query = $this->em->createQuery($dql);

            $query->setParameter('holidayName', $name);
            $query->setParameter('holidayDate', $date);
            $query->setParameter('country', $country);
        }

        //echo "dql=".$dql."<br>";

        $holidays = $query->getResult();

        if( $holidays && count($holidays) > 0 ) {
            return $holidays[0];
        }

        return NULL;
    }

//    public function getHolidayListByYear( $year, $country ) {
//        $repository = $this->em->getRepository('AppVacReqBundle:VacReqHolidayList');
//        $dql = $repository->createQueryBuilder('holidays');
//
//        $dql->leftJoin("holidays.country", "country");
//
//
//
//        return NULL;
//    }

    public function getHolidaysInRange( $startDate, $endDate, $institutionId ) {

        if( !$startDate || !$startDate ) {
            return null;
        }

        $parameters = array();

        $repository = $this->em->getRepository('AppVacReqBundle:VacReqHolidayList');
        $dql = $repository->createQueryBuilder('holidays');

        $dql->where('holidays.observed = true');
        $dql->andWhere("holidays.holidayDate >= :startDate AND holidays.holidayDate <= :endDate");
        $parameters['startDate'] = $startDate;
        $parameters['endDate'] = $endDate;

        //get holidays where $institutionId is under $institutions
        $default = true; //select if first parameter $institution is children of second parameter 'institutions' of the holiday entity
        //$default = false;
        //$institutionId = 0;
        //$institutionId = 1; //WCM
        //$institutionId = 104; //NYP
        //$institutionId = 2035; //Brooklyn Methodist
        //$institutionId = 646; //Vascular
        if( $institutionId ) {
            $dql->leftJoin("holidays.institutions", "institutions");

            //$dql->andWhere("institutions.id IS NOT NULL AND institutions.id = :institutions");
            //$parameters['institutions'] = $institutionId;

            $institution = $this->em->getRepository('AppUserdirectoryBundle:Institution')->find($institutionId);
            //$parentNode, $field, $default=true
            $instStr =
                $this->em->getRepository('AppUserdirectoryBundle:Institution')->
                selectNodesUnderParentNode($institution,"institutions",$default);

            //If holiday does not have institution => don't select this holiday
            //$instStr = "(institutions IS NOT NULL AND ".$instStr.")";
            $dql->andWhere("institutions IS NOT NULL");

            //echo "instStr=[$instStr]<br>";

            $dql->andWhere($instStr);
        }

        $query = $this->em->createQuery($dql);

        //$startDateStr = $startDate->format('Y-m-d');
        //$endDateStr = $endDate->format('Y-m-d');

        if( count($parameters) > 0 ) {
            $query->setParameters($parameters);
        }

        $holidays = $query->getResult();

        //$count = count($holidays);
        //foreach($holidays as $holiday) {
        //    echo $holiday->getString()."<br>";
        //}

        //exit('exit count='.count($holidays));

        return $holidays;
    }

}