<?php
/**
 * Created by PhpStorm.
 * User: oli2002 Oleg Ivanov
 * Date: 1/17/2023
 * Time: 3:18 PM
 */

namespace App\VacReqBundle\Util;



use App\UserdirectoryBundle\Entity\Countries; //process.py script: replaced namespace by ::class: added use line for classname=Countries


use App\UserdirectoryBundle\Entity\Institution; //process.py script: replaced namespace by ::class: added use line for classname=Institution

use App\VacReqBundle\Entity\VacReqHolidayList;
use App\VacReqBundle\Entity\VacReqObservedHolidayList;
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

    //Source of Truth for holidays
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

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Countries'] by [Countries::class]
        $countryEntity = $this->em->getRepository(Countries::class)->findOneByAbbreviation($country);
        if( !$countryEntity ) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Countries'] by [Countries::class]
            $countryEntity = $this->em->getRepository(Countries::class)->findOneByName("United States");
        }
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
            $defaultInstitutions = NULL;
            //throw new \Exception( 'Default instance maintained for the following institutions not found in vacreq site settings' );
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

                //unique name year-holidayname (country should be included too)
                $uniqueNameStr = $year."-".$holidayName; //year-holidayName
                //exit("uniqueNameStr=$uniqueNameStr");

                if( $holiday && $holidayName ) {
                    //ok
                } else {
                    //Skip if name or date empty
                    continue;
                }

                //$update = true;
                $update = false;

                //Update if (a) holiday title AND year AND country are the same, but the month OR date are different
                if( $update ) {
                    $thisHoliday = $this->findHolidayDay($holidayName, $holiday, $country, "same-title-year-country");
                    if ($thisHoliday) {
                        //echo "thisHoliday exists: same-title-year-country <br>";
                        //exit();
                        //Update date
                        $holidayDate = \DateTime::createFromFormat('Y-m-d', $holiday . "");
                        //echo "holidayDate=".$holidayDate->format("Y-m-d");
                        //exit();
                        $thisHoliday->setHolidayDate($holidayDate);

                        $res[] = "Updated date (ID " . $thisHoliday->getId() . "): " . $holiday . ": " . $holidayName;
                        $countUpdated++;

                        if (!$testing) {
                            $this->em->flush();
                        }

                        continue;
                    }

                    //Update if (b) holiday date AND country are the same, but the holiday title is different.
                    $thisHoliday = $this->findHolidayDay($holidayName, $holiday, $country, "same-date-country");
                    if ($thisHoliday) {
                        //echo "thisHoliday exists: same-date-country <br>";
                        //exit();
                        //Update title
                        $thisHoliday->setHolidayName($holidayName);

                        $res[] = "Updated name (ID " . $thisHoliday->getId() . "): " . $holiday . ": " . $holidayName;
                        $countUpdated++;

                        if (!$testing) {
                            $this->em->flush();
                        }

                        continue;
                    }
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
                        if( count($thisHoliday->getInstitutions()) == 0 ) {
                            $thisHoliday->setInstitutions($defaultInstitutions);
                            $res[] = "Add institutions to holiday (ID ".$thisHoliday->getId()."): ".$holiday.": ".$holidayName;
                            $countUpdated++;

                            if( !$testing ) {
                                $this->em->flush();
                            }
                        }
                        continue;
                    }

                    if(0) {
                        //remove all inst. Without inst this holiday will apply to all org groups.
                        $thisHoliday->clearInstitutions();
                        $res[] = "Removed all institutions from holiday (ID ".$thisHoliday->getId()."): ".$holiday.": ".$holidayName;
                        $countUpdated++;

                        if( !$testing ) {
                            $this->em->flush();
                        }
                        continue;
                    }

                    if(0) {
                        /////// add special holiday ////////
                        $addedHoliday = $this->addSpecialHolidayDay($holiday, $countryEntity, $defaultInstitutions, $testing);
                        if ($addedHoliday) {
                            $addedHolidayDate = $addedHoliday->getHolidayDate();
                            $addedHolidayDateStr = "N/A";
                            if ($addedHolidayDate) {
                                $addedHolidayDateStr = $addedHolidayDate->format('Y-m-d');
                            }
                            $res[] = "Add additional holiday (ID " . $addedHoliday->getId() . "): " . ": " . $addedHoliday->getName() . ", $addedHolidayDateStr";
                            $countUpdated++;
                        }
                        /////// EOF add special holiday ////////
                        continue;
                    }

                    $res[] = "Skip existing holiday (ID ".$thisHoliday->getId()."): ".$holiday.": ".$holidayName;

                    continue;
                }


                //Store in VacReqHolidayList
                //exit('creating new VacReq HolidayList');
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

                /////// add special holiday ////////
                $addedHoliday = $this->addSpecialHolidayDay($holiday,$countryEntity,$defaultInstitutions,$testing);
                if( $addedHoliday ) {
                    $addedHolidayDate = $addedHoliday->getHolidayDate();
                    $addedHolidayDateStr = "N/A";
                    if( $addedHolidayDate ) {
                        $addedHolidayDateStr = $addedHolidayDate->format('Y-m-d');
                    }
                    $res[] = "Add additional holiday (ID ".$addedHoliday->getId()."): ".": ".$addedHoliday->getName().", $addedHolidayDateStr";
                    $countUpdated++;
                }
                /////// EOF add special holiday ////////

                $countAdded++;
            }
            //exit();
        }//foreach year

        //echo implode("<br>",$res);

        $res = "Updated $country holidays from $startYear to $endYear.".
            " Total added holiday days is $countAdded.".
            " Total updated holiday days is $countUpdated.<br>".
            implode("<br>",$res)
        ;

        return $res;
    }

    //Add special holiday. For now only: Thanksgiving Day => Day After Thanksgiving
    public function addSpecialHolidayDay( $holiday, $countryEntity, $defaultInstitutions, $testing ) {
        $userSecUtil = $this->container->get('user_security_utility');
        $user = $this->security->getUser();

        $holidayName = $holiday->getName();

        if( $holiday && $holidayName ) {
            //ok
        } else {
            //Skip if name or date empty
            return null;
        }

        if( $holidayName == 'Thanksgiving Day' ) {

            $holidayName = 'Day After Thanksgiving';

            $holidayDate = \DateTime::createFromFormat('Y-m-d', $holiday."");
            $holidayDate->modify('+1 day');

            //add only if not weekend
            if( $this->isWeekend($holidayDate) ) {
                //exit('weekend='.$holidayDate->format('D, M d Y'));
                return null;
            } else {
                //exit('not weekend='.$holidayDate->format('D, M d Y'));
            }

            $countryStr = $countryEntity->getAbbreviation();
            $holidayDateStr = $holidayDate->format('Y-m-d');
            //echo "If exists by $holidayName, $holidayDateStr, $countryStr <br>";
            $thisHoliday = $this->findHolidayDay($holidayName,$holidayDateStr,$countryStr,"");
            //echo "thisHoliday=$thisHoliday <br>";

            if( $thisHoliday ) {
                $countryStr = $countryStr." (ID#".$countryEntity->getId().")";
                //echo "Already exists by combination: $holidayName, $holidayDateStr, $countryStr <br>";
                //exit('111');
                return null;
            }

            list($year, $month, $day) = explode('-', $holiday);
            $uniqueNameStr = $year."-".$holidayName; //year-holidayName
            //exit("uniqueNameStr=$uniqueNameStr");

        //process.py script: replaced namespace by ::class: ['AppVacReqBundle:VacReqHolidayList'] by [VacReqHolidayList::class]
            $thisHoliday = $this->em->getRepository(VacReqHolidayList::class)->findOneByName($uniqueNameStr);
            if( $thisHoliday ) {
                //echo "Already exists by $uniqueNameStr <br>";
                //exit('111');
                return null;
            }

            //if( $year == '2023' ) {
            //    exit('111: ' . $holidayName . ", date=" . $holidayDate->format('d-m-Y') . ", country=" . $countryStr);
            //}

            //Store in VacReqHolidayList
            //exit('creating new VacReq HolidayList');
            $holidayEntity = new VacReqHolidayList($user);

            //set default values
            //$nameStr = $holiday.": ".$holidayName;

            $holidayEntity = $userSecUtil->setDefaultList($holidayEntity,0,$user,$uniqueNameStr);
            $holidayEntity->setType('user-added');

            $holidayEntity->setHolidayName($holidayName);

            $holidayEntity->setHolidayDate($holidayDate);
            $holidayEntity->setCountry($countryEntity);
            $holidayEntity->setInstitutions($defaultInstitutions);

            if( !$testing ) {
                $this->em->persist($holidayEntity);
                $this->em->flush();
            }

            return $holidayEntity;
        }

        return null;
    }
    function isWeekend($datetime) {
        return $datetime->format('N') >= 6;
    }

    public function findHolidayDay( $name, $date, $country, $sameStr=NULL ) {
        //process.py script: replaced namespace by ::class: ['AppVacReqBundle:VacReqHolidayList'] by [VacReqHolidayList::class]
        $repository = $this->em->getRepository(VacReqHolidayList::class);
        $dql = $repository->createQueryBuilder('holidays');

        $dql->leftJoin("holidays.country", "country");

        //(a) holiday title AND year AND country are the same, but the month OR date are different
        if( $sameStr == "same-title-year-country" ) {
            //echo "a) same-title-year-country <br>";
            $dql->andWhere("holidays.holidayName = :holidayName");
            $dql->andWhere("country.abbreviation = :country");

            //'Y-m-d' 2022-05-25 => $year = 2022
            list($year, $month, $day) = explode('-', $date);
            //same year
            //Using YEAR from  beberlei/DoctrineExtensions
            //Might use to_char (https://stackoverflow.com/questions/50890053/doctrine2-year-month-day-or-date-format-for-postgresql)
            $dql->andWhere("YEAR(holidays.holidayDate) = :holidayYear");
            //diff month and date
            $dql->andWhere("holidays.holidayDate != :holidayDate");

            $query = $dql->getQuery(); //$query = $this->em->createQuery($dql);

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

            $query = $dql->getQuery(); //$query = $this->em->createQuery($dql);

            $query->setParameter('holidayName', $name);
            $query->setParameter('holidayDate', $date);
            $query->setParameter('country', $country);
        }
        else {
            $dql->andWhere("holidays.holidayName = :holidayName");
            $dql->andWhere("country.abbreviation = :country");
            $dql->andWhere("holidays.holidayDate = :holidayDate");

            $query = $dql->getQuery(); //$query = $this->em->createQuery($dql);

            $query->setParameter('holidayName', $name);
            $query->setParameter('holidayDate', $date);
            $query->setParameter('country', $country);

            //echo "findHolidayDay parameters: $name, $date, $country <br>";
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

    //get holidays in range:
    //1) find holidays in range in list 1 (VacReqHolidayList)
    //2) confirm that the holiday in list 1 are observer in list 2 by comparing HolidayName
    //3) exclude holidays on the weekends
    //$custom = true => return array of date and name of the holidays
    public function getHolidaysInRange( $startDate, $endDate, $institutionId, $custom=false ) {

        //echo "inst: $institutionId, $startDate $endDate <br>";

        if( !$startDate || !$startDate ) {
            return null;
        }

        $weekDayHolidays = array();

        //1) find holidays in range in list 1 (VacReqHolidayList)
        $holidays = $this->getTrueListHolidaysInRange($startDate,$endDate);
        //echo 'getList1HolidaysInRange count='.count($holidays)."<br>";

        //get observed holidays by observed=true and $institutionId
        $observedHolidays = $this->getObservedHolidaysByInstitution($institutionId);

        //2) confirm that the holiday in list 1 are observer in list 2 by comparing HolidayName
        foreach($holidays as $holiday) {
            $holidayName = $holiday->getHolidayName();
            //$observedHoliday = $this->findSimilarObservedHolidays($holidayName,$institutionId);
            $observedHoliday = $this->findSimilarObservedHolidays($holidayName,$observedHolidays);
            if( $observedHoliday ) {
                //3) exclude holidays on the weekends
                $holidayDate = $holiday->getHolidayDate();
                if( $this->isWeekend($holidayDate) == false ) {
                    if( $custom ) {
                        //use replaced holiday name
                        $weekDayHolidays[] = array(
                            'date' => $holiday->getHolidayDate(),
                            'name' => $observedHoliday->getHolidayNameOrShortName()
                        );
                    } else {
                        $weekDayHolidays[] = $holiday;
                    }
                }
            }
        }

        return $weekDayHolidays;
    }

    public function getTrueListHolidaysInRange( $startDate, $endDate ) {
        if( !$startDate || !$startDate ) {
            return null;
        }

        $parameters = array();

        //process.py script: replaced namespace by ::class: ['AppVacReqBundle:VacReqHolidayList'] by [VacReqHolidayList::class]
        $repository = $this->em->getRepository(VacReqHolidayList::class);
        $dql = $repository->createQueryBuilder('holidays');

        $dql->andWhere("holidays.holidayDate >= :startDate AND holidays.holidayDate <= :endDate");
        $parameters['startDate'] = $startDate;
        $parameters['endDate'] = $endDate;

        $dql->andWhere("holidays.type = :typedef OR holidays.type = :typeadd");
        $parameters['typedef'] = 'default';
        $parameters['typeadd'] = 'user-added';

        $dql->orderBy("holidays.holidayDate","ASC");

        $query = $dql->getQuery(); //$query = $this->em->createQuery($dql);

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

    //find ObservedHoliday by similar holidayName: return true for 'Christmas observed' and 'Christmas'
    public function findSimilarObservedHolidays($holidayName,$observedHolidays)
    {
        //echo "holidayName=$holidayName => ";

        //$observedHolidays = $this->getObservedHolidaysByInstitution($institutionId);
        //dump($observedHolidays);
        //exit('222');

        //remove 'Day'
        $holidayName = str_replace('Day','',$holidayName);
        //echo "holidayName=$holidayName => ";

        foreach($observedHolidays as $observedHoliday) {
            $observedHolidayName = $observedHoliday->getHolidayName();
            $observedHolidayName = str_replace('Day','',$observedHolidayName);

            //echo "[$observedHolidayName] ?= [$holidayName] <br>";

            if( $holidayName == $observedHolidayName ) {
                //echo " found exact! <br>";
                return $observedHoliday;
            }
            
            if( $this->findCommonString($holidayName,$observedHolidayName) ) {
                //echo " found similar! <br>";
                return $observedHoliday;
            }
        }

        //echo " not found <br>";
        return false;
    }
    //Found common string with minimum two common words
    //Case2: "Christmas" =? "Christmas observed" => true
    //Case1: "Washington’s Birthday" =? "Dr. Martin Luther King Jr’s Birthday" => false
    //Case1: "New Year’s Day" =? "New Year’s Day observed" => true
    function findCommonString($str1,$str2,$case_sensitive = false)
    {
//        if( $str1 == $str2 ) {
//            return true;
//        }
//        return false;

        $ary1 = explode(' ',$str1);
        $ary2 = explode(' ',$str2);

        if ($case_sensitive)
        {
            $ary1 = array_map('strtolower',$ary1);
            $ary2 = array_map('strtolower',$ary2);
        }

        //Case1: if str1 or str2 is more than 2 words => require 2 minimum words
        if( count($ary1) >= 2 ||  count($ary2) >= 2 ) {
            $resArr = array_intersect($ary1,$ary2);
            if( count($resArr) >= 2 ) {
                return true;
            } else {
                return false;
            }
        }

        //Case2: 1 or 2 words
        return implode(' ',array_intersect($ary1,$ary2));
    }

    public function getObservedHolidaysByInstitution($institutionId) {
        //process.py script: replaced namespace by ::class: ['AppVacReqBundle:VacReqObservedHolidayList'] by [VacReqObservedHolidayList::class]
        $repository = $this->em->getRepository(VacReqObservedHolidayList::class);

        $dql = $repository->createQueryBuilder('list');
        $dql->select('list');

        $dql->where('list.observed = TRUE');

        $dql->andWhere("list.type = :typedef OR list.type = :typeadd");

        $parameters['typedef'] = 'default';
        $parameters['typeadd'] = 'user-added';

        //get holidays where $institutionId is under $institutions
        $default = true; //select if first parameter $institution is children of second parameter 'institutions' of the holiday entity
        if( $institutionId && filter_var($institutionId, FILTER_VALIDATE_INT) !== false ) {
            $dql->leftJoin("list.institutions", "institutions");

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
            $institution = $this->em->getRepository(Institution::class)->find($institutionId);
            //$parentNode, $field, $default=true
            $instStr =
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
                $this->em->getRepository(Institution::class)->
                selectNodesUnderParentNode($institution,"institutions",$default);
            $dql->andWhere($instStr);
        }

        $query = $dql->getQuery(); //$query = $this->em->createQuery($dql);

        if( count($parameters) > 0 ) {
            $query->setParameters($parameters);
        }

        $observedHolidays = $query->getResult();

        //echo "holidays=".count($observedHolidays)."<br>";
        //foreach($observedHolidays as $observedHoliday) {
        //    echo "".$observedHoliday."<br>";
        //}
        //exit('111');

        return $observedHolidays;
    }

    public function getList1HolidaysInRange_ORIG( $startDate, $endDate, $institutionId ) {

        if( !$startDate || !$startDate ) {
            return null;
        }

        $parameters = array();

        //process.py script: replaced namespace by ::class: ['AppVacReqBundle:VacReqHolidayList'] by [VacReqHolidayList::class]
        $repository = $this->em->getRepository(VacReqHolidayList::class);
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

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
            $institution = $this->em->getRepository(Institution::class)->find($institutionId);
            //$parentNode, $field, $default=true
            $instStr =
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
                $this->em->getRepository(Institution::class)->
                selectNodesUnderParentNode($institution,"institutions",$default);

            //If holiday does not have institution => don't select this holiday
            //$instStr = "(institutions IS NULL) OR (institutions IS NOT NULL AND ".$instStr.")";
            //$dql->andWhere("institutions IS NOT NULL");

            //echo "instStr=[$instStr]<br>";

            $dql->andWhere($instStr);
        }

        $query = $dql->getQuery(); //$query = $this->em->createQuery($dql);

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

    public function getOrCreateObservedHoliday( $holiday ) {

        $observedHoliday = $this->findObservedHoliday($holiday);
        if( !$observedHoliday ) {
            $observedHoliday = $this->createObservedHoliday($holiday);
        }

        return $observedHoliday;
    }
    public function findObservedHoliday( $holiday ) {
        $holidayName = $holiday->getHolidayName();
        if( !$holidayName ) {
            return null;
        }

        //remove 'observed' string
        if( str_contains($holidayName,'observed') ) {
            //echo "observed in $holidayName<br>";
            $holidayName = str_replace('observed','',$holidayName);
            $holidayName = trim($holidayName);
        }

        //find already exited by $holidayName
        //process.py script: replaced namespace by ::class: ['AppVacReqBundle:VacReqObservedHolidayList'] by [VacReqObservedHolidayList::class]
        $observedHoliday = $this->em->getRepository(VacReqObservedHolidayList::class)->findOneByName($holidayName);
        if( $observedHoliday ) {
            return $observedHoliday;
        }
        
        return null;
    }
    public function createObservedHoliday( $holiday ) {
        $holidayName = $holiday->getHolidayName();

        if( !$holidayName ) {
            return null;
        }

        //remove 'observed' string
        if( str_contains($holidayName,'observed') ) {
            //echo "observed in $holidayName<br>";
            $holidayName = str_replace('observed','',$holidayName);
            $holidayName = trim($holidayName);
        }

        //create new VacReq Observed HolidayList:
        //copy holidayName => name, holidayName
        //copy country => country
        //copy institutions => institutions

        $userSecUtil = $this->container->get('user_security_utility');
        $user = $this->security->getUser();

        $observedHoliday = new VacReqObservedHolidayList($user);
        $observedHoliday = $userSecUtil->setDefaultList($observedHoliday,0,$user,$holidayName);
        $observedHoliday->setType('user-added');

        $observedHoliday->setHolidayName($holidayName);

        //exception
        if( $holidayName == 'Washington’s Birthday' ) {
            $observedHoliday->setShortname("Presidents' Day");
        }

        //$observedHoliday->setHolidayDate($holiday->getHolidayDate());
        $observedHoliday->setCountry($holiday->getCountry());
        $observedHoliday->setInstitutions($holiday->getInstitutions());

        $this->em->persist($observedHoliday); //testing
        $this->em->flush();

        return $observedHoliday;
    }

    public function getHolidayDate($observedHoliday) {
        $year = new \DateTime();
        $year = $year->format('Y');
        //echo "date=".$year->format('d-m-Y')."<br>";
        $holidayName = $observedHoliday->getName();

        //$observedHoliday = $this->em->getRepository('AppVacReqBundle:VacReqHolidayList')->findOneByHolidayName($holidayName);

        $parameters = array();

        //process.py script: replaced namespace by ::class: ['AppVacReqBundle:VacReqHolidayList'] by [VacReqHolidayList::class]
        $repository = $this->em->getRepository(VacReqHolidayList::class);
        $dql = $repository->createQueryBuilder('holidays');

        $dql->where('holidays.holidayName = :holidayName');
        $parameters['holidayName'] = $holidayName;

        //$dql->andWhere("holidays.holidayDate >= :startDate AND holidays.holidayDate <= :endDate");
        //$parameters['startDate'] = $startDate;
        //$parameters['endDate'] = $endDate;

        $dql->andWhere("YEAR(holidays.holidayDate) = :holidayYear");
        $parameters['holidayYear'] = $year;

        $query = $dql->getQuery(); //$query = $this->em->createQuery($dql);

        if( count($parameters) > 0 ) {
            $query->setParameters($parameters);
        }

        $holidays = $query->getResult();

        if( count($holidays) > 0 ) {
            $holiday = $holidays[0];
            //echo "date=".$holiday->getHolidayDate()->format('d-m-Y')."<br>";

            //return null;
            return $holiday->getHolidayDate();
        }

        return null;
    }

    public function cleanString($string) {
        $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.

        return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
    }

    //get note for holidays
    //Add a sentence into the email notification to the approver upon away request submission
    // IF (and only if) the automatically calculated quantity of total days away was changed by the submitter to a different value.
    //In that case, please add a sentence that says either:
    // (if at least one holiday date was included)
    // "The date range for this away request includes the following holiday(s): [Holiday name] ([Weekday], MM/DD/YYYY).
    // The automatically calculated quantity of business days away was [X],
    // and was manually changed by the submitter to [Y]." or (if no holiday dates were included)
    // "The automatically calculated quantity of business days away was [X], and was manually changed by the submitter to [Y]."
    public function getDaysDifferenceNote( $vacreqRequest )
    {
        $requestType = $vacreqRequest->getRequestType();

        //floating day does not have request type and it does not have holiday note
        if( !$requestType ) {
            return null;
        }

        if ($requestType && $requestType->getAbbreviation() == "carryover") {
            return null;
        }

        $institution = $vacreqRequest->getInstitution();
        $institutionId = null;
        if( $institution ) {
            $institutionId = $institution->getId();
        }

        //$vacreqUtil = $this->container->get('vacreq_util');

        //$break = "<br>";
        $note = '';

        //$custom = true; //use custom holiday object with 'name' and 'date'

        //business request
        if( $vacreqRequest->hasBusinessRequest() ) {
            $note = $this->getSingleDaysDifferenceNote($vacreqRequest->getRequestBusiness(),'business',$institutionId);
        }

        //vacation request
        if( $vacreqRequest->hasVacationRequest() ) {
            $note = $this->getSingleDaysDifferenceNote($vacreqRequest->getRequestVacation(),'vacation',$institutionId);
        }

        return $note;
    }
    //$vacreqRequestSingle (object): $vacreqRequest->getRequestBusiness() or $vacreqRequest->getRequestVacation()-
    //$requestName (string): 'business' or 'vacation'
    //$institutionId (ID): institution (organisational group) ID
    public function getSingleDaysDifferenceNote( $vacreqRequestSingle, $requestName, $institutionId ) {
        $vacreqUtil = $this->container->get('vacreq_util');

        $custom = true;
        $break = "<br>";
        $note = '';

        $requestNumberOfDays = $vacreqRequestSingle->getNumberOfDays();
        $startDate = $vacreqRequestSingle->getStartDate();
        $endDate = $vacreqRequestSingle->getEndDate();
        //echo "startDate=".$startDate->format('d-m-Y H:i:s') . ", endDate=".$endDate->format('d-m-Y H:i:s').$break;

        //$startDate->modify('+1 day');
        $startDate->setTime(00, 00, 00);
        $endDate->setTime(23, 59, 59);

        //echo "startDate=".$startDate->format('d-m-Y H:i:s') . ", endDate=".$endDate->format('d-m-Y H:i:s');

        //$adjustHolidays = true;
        $adjustHolidays = false;

        //count number of vacation days from $startDate and $endDate
        $countedNumberOfDays = $vacreqUtil->getNumberOfWorkingDaysBetweenDates($startDate,$endDate,$adjustHolidays); //getSingleDaysDifferenceNote

        $holidays = $this->getHolidaysInRange($startDate,$endDate,$institutionId,$custom); //getSingleDaysDifferenceNote

        //10 June 2025: IF $adjustHolidays=true, then holiday adjustment is already done in getNumberOfWorkingDaysBetweenDates
        if( $adjustHolidays == false ) {
            $countedNumberOfDays = intval($countedNumberOfDays) - count($holidays);
        }

        //echo $break . $countedNumberOfDays . "?=" . $requestNumberOfDays . $break;

        //if vacation days has been changed by submitter
        if( intval($countedNumberOfDays) != intval($requestNumberOfDays) ) {

            //1) general note because days have been changed
            $note = "The automatically calculated quantity of $requestName days away was $countedNumberOfDays,".
                " and was manually changed by the submitter to $requestNumberOfDays.";

            if( count($holidays) > 0 ) {
                //2) note if days contains holidays
                $holidaysStr = $this->getHolidaysStr($holidays, $custom);
                //The date range for this away request includes the following holiday(s): [Holiday name] ([Weekday], MM/DD/YYYY).
                // The automatically calculated quantity of business days away was [X], and was manually changed by the submitter to [Y].
                $note = "The date range for this away request includes the following holiday(s): ".
                    $holidaysStr . $break . $note;
            }
        }

        return $note;
    }

    public function getHolidaysStr( $holidays, $custom=false ) {
        $holidayStr = "";

        foreach($holidays as $holiday) {
            if( $custom ) {
                $holidayDate = $holiday['date'];
                $holidayName = $holiday['name'];

                $holidayDateStr = "N/A";
                if( $holidayDate ) {
                    $holidayDateStr = $holidayDate->format('D, M d Y'); //format('m/d/Y');
                }

                $holidayStr = $holidayStr . "<br>- " . $holidayName . " on " . $holidayDateStr;
            } else {
                $holidayDate = $holiday->getHolidayDate();
                $holidayDateStr = "N/A";
                if( $holidayDate ) {
                    $holidayDateStr = $holiday->getHolidayDate()->format('D, M d Y'); //format('m/d/Y');
                }
                //$holidayStrArr[] = $holiday->getHolidayNameOrShortName() . " on " . $holidayDateStr; //[Holiday Title] on [Holiday Date]
                $holidayStr = $holidayStr . "<br>- " . $holiday->getHolidayNameOrShortName() . " on " . $holidayDateStr;
            }
        }

        return $holidayStr;
    }
}