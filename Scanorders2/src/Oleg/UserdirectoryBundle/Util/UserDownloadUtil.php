<?php
/**
 * Copyright (c) 2017 Cornell University
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

/**
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 10/4/13
 * Time: 12:56 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Oleg\UserdirectoryBundle\Util;


use Oleg\UserdirectoryBundle\Entity\User;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Component\HttpFoundation\RedirectResponse;


class UserDownloadUtil {

    protected $em;
    protected $secTokenStorage;
    protected $container;

    private $cellSize = 10;
    private $cellFont = "Calibri";
    private $headerSize = 11;
    private $headerFont = "Calibri";

    public function __construct( $em, $secTokenStorage, $container ) {
        $this->em = $em;
        $this->secTokenStorage = $secTokenStorage;
        $this->container = $container;
    }


    public function getSections( $users, $administrativeUsers, $locations ) {
        $sections = array();
        //$priority = 0; //"Primary"
        $priority = null;
        foreach( $users as $user ) {
            //echo "<br>user=".$user.":<br>";
            $instResArr = $user->getDeduplicatedInstitutions($priority);

            foreach( $instResArr as $instRes ) {
                //$instName = $instRes[0]['instNameWithRoot'];
                //Do not show (WCM) or (NYP) at all after every section title.
                $instName = $instRes[0]['instName'];
                //Set all uppercase
                $instName = strtoupper($instName);
                //echo "add instName=".$instName."<br>";
                //$sections[$instName][] = $user."";
                $sections[$instName][] = $user;
            }
            //break;
        }

        //testing
        //echo '<br><br>sections:<pre>';
        //print_r($sections);
        //echo  '</pre>';
//        foreach ($sections as $sectionName => $sectionUsers) {
//            echo "<br><br>$sectionName:<br>";
//            foreach( $sectionUsers as $user ) {
//                echo "user=".$user."<br>";
//            }
//        }
//        exit("testing");

        //////////////// reorganize sections ////////////////

        //add users with administrative Title to Administration (WCMC)
        $newSections = array();
        foreach ($sections as $sectionName => $sectionUsers) {
            //exit();
            //$sectionName = $section
            if( $sectionName == "ADMINISTRATION" ) {
    //                echo "<br><br>$sectionName:<pre>";
    //                print_r($sectionUsers);
    //                echo  '</pre>';
                foreach ($administrativeUsers as $administrativeUser) {
                    //echo "administrativeUser=".$administrativeUser."<br>";
                    if( !$this->hasUser($sectionUsers, $administrativeUser) ) {
                        //echo "add administrativeUser=".$administrativeUser."<br>";
                        //$sectionUsers[] = $administrativeUser;
                        array_unshift($sectionUsers, $administrativeUser);
                        //$sections[$sectionName][] = $administrativeUser;
                    }
                }
                $sectionUsers = $this->sortUsersByPosition($sectionUsers,false);
                $newSections[$sectionName] = $sectionUsers;
                //$sections[$sectionName] = $sectionUsers;
            }
        }

        //echo "<br><br>############other sections:#################<br><br>";
        foreach ($sections as $sectionName => $sectionUsers) {
            //echo "############ $sectionName #################<br>";
            //$this->printUsers($sectionUsers);

            if( $sectionName != "ADMINISTRATION" ) {
                $sectionUsers = $this->sortUsersByPosition($sectionUsers,false);
                $newSections[$sectionName] = $sectionUsers;
            }
        }
        //////////////// EOF reorganize sections ////////////////
        //exit();

        ///////////// Add other section (* comment and common locations) as array //////////////
        if( $locations && count($locations)>0 ) { //removed by J.M. request
            //add common $locations
            $locationsArr = array();
            foreach ($locations as $location) {
                $locationsArr[] = array(
                    'A' => $location->getName() . "",
                    'B' => '',
                    'C' => $location->getPhone() . "",
                    'D' => $location->getRoom() . "",
                    'E' => $location->getEmail() . ""
                );
            }
            $newSections["COMMON LOCATIONS"] = $locationsArr;


            $newSections["  "] = array(
                array(
                    'A' => ' *Dial access code 6-6700, 5 digit ID #, call-back extension (must begin with 6-XXXX), pound (#).',
                    'B' => '',
                    'C' => '',
                    'D' => '',
                    'E' => ''
                ),
            );
        }
        ///////////// EOF Add other section (* comment and common locations) as array //////////////


        return $newSections;
    }
    public function hasUser( $users, $subjectUser ) {
        foreach( $users as $user ) {
            if( $user->getId() === $subjectUser->getId() ) {
                return true;
            }
        }
        return false;
    }

    public function createUserListExcel( $sections, $sheetParams ) {

        $ea = $sheetParams['excelBlob'];
        $sheetTitle = $sheetParams['sheetTitle'];
        $footer = $sheetParams['sheetFooter'];
        $sheetName = $sheetParams['sheetName'];
        $sheetIndex = $sheetParams['sheetIndex'];
        $sheetHeaders = $sheetParams['sheetHeaders'];

        $sheetFont = $sheetParams['sheetFont'];
        $sheetSize = $sheetParams['sheetSize'];
        $sheetZoom = $sheetParams['sheetZoom'];
        $sheetHeaderSize = $sheetParams['sheetHeaderSize'];

        $withAssistants = $sheetParams['withAssistants'];

        if( $sheetFont ) {
            $this->cellSize = $sheetFont;
        }
        if( $sheetSize ) {
            $this->cellSize = $sheetSize;
        }
        if( $sheetHeaderSize ) {
            $this->headerSize = $sheetSize;
        }

        $author = $this->secTokenStorage->getToken()->getUser();

        $row = 1;
        //$withheader = false;
        //$headerSize = $this->headerSize;

        if( !$ea ) {
            $ea = new Spreadsheet(); // ea is short for Excel Application

            $ea->getProperties()
                ->setCreator($author . "")
                ->setTitle('User List')
                ->setLastModifiedBy($author . "")
                ->setDescription('User list in Excel format')
                ->setSubject('PHP Excel manipulation')
                ->setKeywords('excel php office phpexcel wcmc')
                ->setCategory('programming');
        }

        if( $sheetIndex == 0 ) {
            $ews = $ea->getSheet($sheetIndex);
        } else {
            $ews = $ea->createSheet($sheetIndex);
        }

        $ews->setTitle($sheetName);
        $ews->getSheetView()->setZoomScale($sheetZoom);

        //align all cells to left
        $styleArray = array(
            'alignment' => array(
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP,
            ),
            'font'  => array(
                //'bold'  => true,
                //'color' => array('rgb' => 'FF0000'),
                'size'  => $this->cellSize,
                'name'  => $this->cellFont
            )
        );
        $ews->getParent()->getDefaultStyle()->applyFromArray($styleArray);

        //set width (from original excel file to make printable)
        $ews->getColumnDimension('A')->setWidth(22.18);
        $ews->getColumnDimension('B')->setWidth(24.36);   //20.36
        $ews->getColumnDimension('C')->setWidth(24.36);   //18.36
        $ews->getColumnDimension('D')->setWidth(14.18);   //8.18
        $ews->getColumnDimension('E')->setWidth(24.64);   //21.64

        //marging
        $ews->getPageMargins()->setTop(1);
        $ews->getPageMargins()->setRight(0.25); //0.75
        $ews->getPageMargins()->setLeft(0);
        $ews->getPageMargins()->setBottom(1);

        //set title
        $ews->getHeaderFooter()->setOddHeader('&C&H'.$sheetTitle);

        //set footer (The code for "left" is &L)
        $ews->getHeaderFooter()->setOddFooter('&L'.$footer);
        $ews->getHeaderFooter()->setEvenFooter('&L'.$footer);


        if( $sheetHeaders && count($sheetHeaders) > 0 ) {

//            $nameHeader = $this->getBoldItalicText("Name", $headerSize);
//            $ews->setCellValue('A1', $nameHeader); // Sets cell 'a1' to value 'ID
//
//            $titleHeader = $this->getBoldItalicText("Title", $headerSize);
//            $ews->setCellValue('B1', $titleHeader);
//
//            $phoneHeader = $this->getBoldItalicText("Phone", $headerSize);
//            $ews->setCellValue('C1', $phoneHeader);
//
//            $roomHeader = $this->getBoldItalicText("Room", $headerSize);
//            $ews->setCellValue('D1', $roomHeader);
//
//            $emailHeader = $this->getBoldItalicText("Email", $headerSize);
//            $ews->setCellValue('E1', $emailHeader);

            //echo "Users=".count($users)."<br>";

            foreach( $sheetHeaders as $sheetHeaderColumn=>$sheetHeaderName ) {
//                $styleArray = array(
//                    'font' => array(
//                        'underline' => \PHPExcel_Style_Font::UNDERLINE_SINGLE
//                    )
//                );
//                $ews->getStyle($sheetHeaderColumn)->applyFromArray($styleArray);
                $nameHeader = $this->getBoldItalicText($sheetHeaderName, $sheetHeaderSize, true);
                $ews->setCellValue($sheetHeaderColumn, $nameHeader);
            }

            $row = $row + 1;
        }

        //$sections = array("WCM"=>$users,"NYP"=>$users);
//        foreach( $sections as $sectionName=>$sectionUsers ) {
//            echo "<br>###### sectionName=".$sectionName."######<br>";
//            $sectionUsersArr = array();
//            foreach( $sectionUsers as $sectionUser ) {
//                echo $sectionUser."<br>";
//                //$sectionUsersArr[] = $sectionUser;
//            }
//        }
//        exit("111");

        foreach( $sections as $sectionName=>$sectionUsers ) {
            $row = $this->addSectionUsersToListExcel($sectionName."", $sectionUsers, $ews, $row, $withAssistants);
        }

        //exit("222");

//        // Auto size columns for each worksheet
//        \PHPExcel_Shared_Font::setAutoSizeMethod(\PHPExcel_Shared_Font::AUTOSIZE_METHOD_EXACT);
//        foreach ($ea->getWorksheetIterator() as $worksheet) {
//
//            $ea->setActiveSheetIndex($ea->getIndex($worksheet));
//            $sheet = $ea->getActiveSheet();
//
//            $cellIterator = $sheet->getRowIterator()->current()->getCellIterator();
//            $cellIterator->setIterateOnlyExistingCells(true);
//            /** @var PHPExcel_Cell $cell */
//            foreach ($cellIterator as $cell) {
//                $sheet->getColumnDimension($cell->getColumn())->setAutoSize(true);
//            }
//        }

        //set sheet params
//        \PHPExcel_Shared_Font::setAutoSizeMethod(\PHPExcel_Shared_Font::AUTOSIZE_METHOD_EXACT);
//        $cellIterator = $ews->getRowIterator()->current()->getCellIterator();
//        $cellIterator->setIterateOnlyExistingCells(true);
//        /** @var PHPExcel_Cell $cell */
//        foreach ($cellIterator as $cell) {
//            $ews->getColumnDimension($cell->getColumn())->setAutoSize(true);
//        }

        //exit("111");
        return $ea;
    }

    public function addSectionUsersToListExcel( $sectionName, $users, $ews, $row, $withAssistants=true ) {

        //section Header
        if(1) {
            //$ews->mergeCells('A' . $row . ':' . 'E' . $row);
            $style = array(
                'alignment' => array(
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, //HORIZONTAL_JUSTIFY   //HORIZONTAL_CENTER,
                ),
                'font'  => array(
                    //'bold'  => true,
                    //'color' => array('rgb' => 'FF0000'),
                    'size'  => $this->headerSize,
                    'name'  => $this->headerFont
                )
            );
            $ews->getStyle('A' . $row . ':' . 'E' . $row)->applyFromArray($style);

            $sectionHeader = $this->getBoldItalicText($sectionName, $this->headerSize);
            $ews->setCellValue('B' . $row, $sectionHeader);
            $row = $row + 1;
        }
        //return $row;

        foreach( $users as $user ) {

            if( !$user ) {
                continue;
            }

            //user
            if( $user instanceof User ) {
                $this->createRowUser($user, $ews, $row, "regular");

                if( $withAssistants ) {
                    //assistants
                    $assistantsRes = $user->getAssistants();
                    $assistants = $assistantsRes['entities'];
                    if (count($assistants) > 0) {
                        foreach ($assistants as $assistant) {
                            $row = $row + 1;
                            $this->createRowUser($assistant, $ews, $row, "assistant");
                        }
                    }
                }
            } else {
                $this->createRowFromCellArray($user, $ews, $row);
            }

            $row = $row + 1;
        }

        $row = $row + 1;

        //exit("ids=".$fellappids);

        return $row;
    }

    public function createRowFromCellArray( $cellArr, $ews, $row ) {
        if( !$cellArr ) {
            return;
        }

        foreach( $cellArr as $cellColumn=>$cellValue ) {
            $ews->setCellValue($cellColumn.$row, $cellValue);
            $ews->getStyle($cellColumn.$row)->getAlignment()->setWrapText(true);
        }
    }

    public function createRowUser( $user, $ews, $row, $type="regular" ) {
        if( !$user ) {
            return;
        }

        //Name
        if( $type == "assistant" ) {
            $userName = $user->getUsernameOptimal();
            $userName = "     ".$userName;
        }

        if( $type == "regular" ) {
            $userName = $this->convertUsernameToBold($user);
        }

        $ews->setCellValue('A'.$row, $userName);
        $ews->getStyle('A'.$row)->getAlignment()->setWrapText(true);

        //Title
        $administrativeTitleNameStr = $this->getUserTitleStr($user);
        $ews->setCellValue('B'.$row, $administrativeTitleNameStr);
        $ews->getStyle('B'.$row)->getAlignment()->setWrapText(true);

        //Phone
//        $phoneStr = "";
//        $phoneArr = array();
//        foreach( $user->getAllPhones() as $phone ) {
//            $comment = "";
//            if( strpos($phone['prefix'], "Pager") !== false ) {
//                $comment = "*";
//            }
//            $phoneArr[] = $phone['prefix'] . $phone['phone'].$comment;
//        }
//        if( count($phoneArr) > 0 ) {
//            $phoneStr = implode(" \n", $phoneArr);
//        }
        $phoneArr = $user->getSinglePhoneAndPager();
        $phoneStr = $phoneArr['phone'];
        $pagerStr = $phoneArr['pager'];
        if( $pagerStr ) {
            $phoneStr = $phoneStr . " / " . $pagerStr . "*";
        }
        $ews->setCellValue('C'.$row, $phoneStr);
        $ews->getStyle('C'.$row)->getAlignment()->setWrapText(true);

        //Room
        $location = $user->getMainLocation();
        if( $location ) {
            $locationStr = $location->getLocationNameNoType();
            $ews->setCellValue('D' . $row, $locationStr);
            $ews->getStyle('D'.$row)->getAlignment()->setWrapText(true);
        }

        //Email
        $ews->setCellValue('E'.$row, $user->getSingleEmail());
        $ews->getStyle('E'.$row)->getAlignment()->setWrapText(true);
    }

    //Oleg Ivanov, MD => <strong>Ivanov</strong>, Dr. Oleg
    public function convertUsernameToBold( $user, $order="familyname" ) {
        //return $userName;

        $userFirstname = $user->getSingleFirstName();
        $userFamilyname = $user->getSingleLastName();
        $userSalutation = $user->getSingleSalutation();;

        if( $order == "familyname" ) {
            //echo "userFamilyname=$userFamilyname<br>";
            $userName = $this->getBoldText($userFamilyname,$this->cellSize);

            if( $userSalutation && $userSalutation == "Dr." ) {
                $userName->createText(", " . $userSalutation);
            }

            $userName->createText(" " . $userFirstname);

            if( $userSalutation && $userSalutation != "Dr." ) {
                $userName->createText(", " . $userSalutation);
            }

        }

        if( $order == "firstname" ) {
            //$userName = new \PHPExcel_RichText();
            $userName = new \PhpOffice\PhpSpreadsheet\RichText\RichText();
            $userName->createTextRun($userFirstname);
            $userName = $this->getBoldText(" " . $userFamilyname, $this->cellSize, $userName);

            if ($userSalutation) {
                $userName->createText(", " . $userSalutation);
            }
        }

        return $userName;
    }

//    public function getRichText( $text, $size=null, $richText=null ) {
//        if( !$text ) {
//            return new \PHPExcel_RichText();
//        }
//        if( !$richText ) {
//            $richText = new \PHPExcel_RichText();
//        }
//        $objBold = $richText->createTextRun($text);
//        if( $size ) {
//            $objBold->getFont()->setSize($size);
//        }
//        return $richText;
//    }

    public function getBoldText( $text, $size=null, $richText=null ) {
        if( !$text ) {
            return new \PhpOffice\PhpSpreadsheet\RichText\RichText();
        }
        if( !$richText ) {
            $richText = new \PhpOffice\PhpSpreadsheet\RichText\RichText();
        }
        $objBold = $richText->createTextRun($text);
        $objBold->getFont()->setBold(true);
        if( $size ) {
            $objBold->getFont()->setSize($size);
        }
        return $richText;
    }

    public function getBoldItalicText( $text, $size=null, $underline=false ) {
        $richText = new \PhpOffice\PhpSpreadsheet\RichText\RichText();
        $objBold = $richText->createTextRun($text);
        $objBold->getFont()->setBold(true);
        $objBold->getFont()->setItalic(true);
        if( $underline ) {
            $objBold->getFont()->setUnderline(true);
        }
        if( $size ) {
            $objBold->getFont()->setSize($size);
        }
        return $richText;
    }

    public function getUserTitleStr($user,$newline=" \n") {
        $titleNameStr = null;
        //first priority is admin title
        $titleNameStr = $user->getUniqueTitlesStr($user->getAdministrativeTitles(),$newline);

        if( !$titleNameStr ) {
            //if admin title is empty, then add appointment and medical titles
            $titleNameStrArr = array();
            $titleNameStrArr[] = $user->getUniqueTitlesStr($user->getAppointmentTitles(),$newline);
            $titleNameStrArr[] = $user->getUniqueTitlesStr($user->getMedicalTitles(),$newline);
            $titleNameStr = implode($newline,$titleNameStrArr);
        }

        //if( !$titleNameStr ) {
            //$titleNameStr = $this->getUserHeaderStr($user);
        //}

        return $titleNameStr;
    }

    public function getUniqueFirstTitleStr( $user, $delimeter="; " ) {
        if( !$user ) {
            return null;
        }
        $titleStr = null;
        $titlesArr = $user->getUniqueTitles($user->getAdministrativeTitles());
        $title = null;
        if( count($titlesArr) > 0 ) {
            $title = $titlesArr[0];
        }
        if( $title ) {
            $titleStr = $title->getInstitution()."";
        }

        if( !$titleStr ) {
            $titleStr = $this->getUserTitleStr($user);
        }

        //Director, Transfusion Medicine and the Cellular Therapy Service
        $maxlen = 45; //53
        if( strlen($titleStr) > $maxlen ) {
            $titleStr = substr($titleStr, 0, $maxlen);
            $titleStr = $titleStr . "...";
        }

        return $titleStr;
    }

//    public function getUserHeaderStr($user) {
//        $titleInfoArr = array();
//        $userSecUtil = $this->container->get('user_security_utility');
//        $headInfos = $userSecUtil->getHeadInfo($user);
//        foreach( $headInfos as $headInfo ) {
//
//            echo '<br><br>headInfo:<pre>';
//            print_r($headInfo);
//            echo  '</pre>';
//            exit();
//
//            //{% for titleInfo in headInfoArr.titleInfo if titleInfo.name is defined %}
//            foreach( $headInfo['titleInfo'] as $titleInfo ) {
//                if( is_array($titleInfo) && array_key_exists('name', $titleInfo) ) {
//                    $titleInfoArr[] = $titleInfo['name'];
//                }
//            }
//        }
//        return implode(" \n",$titleInfoArr);
//    }

    public function sortUsersByTitle( $users ) {
        $newUsers = array();
        foreach( $users as $user ) {
            $administrativeTitleNameStr = $this->getUserTitleStr($user);
            $pos = strpos($administrativeTitleNameStr, "Chairman of ");
            if( $pos !== false ) {
                if( !$this->hasUser($newUsers, $user) ) {
                    //echo "Chairman $user<br>";
                    //$newUsers[] = $user;
                    array_unshift($newUsers, $user);
                }
            }
        }
        foreach( $users as $user ) {
            $administrativeTitleNameStr = $this->getUserTitleStr($user);
            $pos = strpos($administrativeTitleNameStr, "Vice Chairman");
            if( $pos !== false ) {
                if( !$this->hasUser($newUsers, $user) ) {
                    //echo "Vice Chairman $user<br>";
                    //$newUsers[] = $user;
                    array_unshift($newUsers, $user);
                }
            }
        }

        //all others
        foreach( $users as $user ) {
            $administrativeTitleNameStr = $this->getUserTitleStr($user);
            $pos1 = strpos($administrativeTitleNameStr, "Chairman of ");
            $pos2 = strpos($administrativeTitleNameStr, "Vice Chairman");
            if( $pos1 === false && $pos2 === false ) {
                if( !$this->hasUser($newUsers, $user) ) {
                    //echo "other $user<br>";
                    //$newUsers[] = $user;
                    array_unshift($newUsers, $user);
                }
            }
        }

        //exit();

        return $newUsers;
    }

    public function sortUsersByPosition( $users, $reverse=true ) {
        //$leftOverUsers = array();
        //$leftOverUsers = $users;
        $sortedUsers = array();

        foreach( $users as $user ) {
            $leftOverUsers[$user->getId()] = $user;
        }

        //echo "original<br>";
        //$this->printUsers($sortedUsers);

        //"Chairman of "
        foreach( $leftOverUsers as $user ) {
            $administrativeTitleNameStr = $this->getUserTitleStr($user);
            $pos = strpos($administrativeTitleNameStr, "Chairman of ");
            if( $pos !== false ) {
                if( !$this->hasUser($sortedUsers, $user) ) {
                    //$sortedUsers[] = $user;
                    //array_unshift($newUsers, $user);
                    $this->addUserToArray($user,$sortedUsers,$reverse);
                    unset($leftOverUsers[$user->getId()]);
                }
            }
        }

        //"Vice Chairman"
        foreach( $leftOverUsers as $user ) {
            $administrativeTitleNameStr = $this->getUserTitleStr($user);
            $pos = strpos($administrativeTitleNameStr, "Vice Chairman");
            if( $pos !== false ) {
                if( !$this->hasUser($sortedUsers, $user) ) {
                    //$sortedUsers[] = $user;
                    //array_unshift($newUsers, $user);
                    $this->addUserToArray($user,$sortedUsers,$reverse);
                    unset($leftOverUsers[$user->getId()]);
                }
            }
        }

        $this->searchAndAddUserByPosition("Head of Institution", $sortedUsers, $leftOverUsers, $reverse);
        //echo "Head of Institution<br>";
        //$this->printUsers($sortedUsers);
        //$this->printUsers($leftOverUsers);

        $this->searchAndAddUserByPosition("Head of Department", $sortedUsers, $leftOverUsers, $reverse);
        //echo "Head of Department<br>";
        //$this->printUsers($sortedUsers);
        //$this->printUsers($leftOverUsers);

        $this->searchAndAddUserByPosition("Head of Division", $sortedUsers, $leftOverUsers, $reverse);
        //echo "Head of Division<br>";
        //$this->printUsers($sortedUsers);
        //$this->printUsers($leftOverUsers);

        $this->searchAndAddUserByPosition("Head of Service", $sortedUsers, $leftOverUsers, $reverse);
        //echo "Head of Service<br>";
        //$this->printUsers($sortedUsers);
        //$this->printUsers($leftOverUsers);

        //all others
        foreach( $leftOverUsers as $user ) {
            if( !$this->hasUser($sortedUsers, $user) ) {
                //$sortedUsers[] = $user;
                //array_unshift($sortedUsers, $user);
                $this->addUserToArray($user,$sortedUsers,$reverse);
            }
        }

//        echo "<br>final:<br>";
//        $administrativeUserCount = 1;
//        foreach( $sortedUsers as $administrativeUser ) {
//            echo $administrativeUserCount.": adminUser=".$administrativeUser."<br>";
//            $administrativeUserCount++;
//        }
        //exit();

        return $sortedUsers;
    }
    public function searchAndAddUserByPosition( $positionStr, &$sortedUsers, &$leftOverUsers, $reverse ) {
        //echo "########### $positionStr ############<br>";
        //$count = 0;
        foreach( $leftOverUsers as $user ) {
            $add = false;
            if( $this->hasUserPosition($user,$positionStr) ) {
                if( !$this->hasUser($sortedUsers, $user) ) {
                    $add = true;
                }
            }
            if( $add ) {
                //echo $positionStr.": adding ".$user."<br>";
                $this->addUserToArray($user,$sortedUsers,$reverse);
                //$sortedUsers[] = $user;
                //array_unshift($sortedUsers, $user); //Prepend one or more elements to the beginning of an array
                //echo "=>: removing ".$leftOverUsers[$user->getId()]."<br>";
                unset($leftOverUsers[$user->getId()]);
            }
            //$count++;
        }
    }
    public function hasUserPosition( $user, $positionStr ) {
        foreach( $user->getAdministrativeTitles() as $title ) {
            $positions = $title->getUserPositions();
            foreach( $positions as $position ) {
                if( $position->getName()."" == $positionStr ) {
                    return true;
                }
            }
        }
        return false;
    }
    public function addUserToArray($user,&$users,$reverse){
        if( $reverse ) {
            array_unshift($users, $user); //Prepend one or more elements to the beginning of an array
        } else {
            $users[] = $user;
        }
    }
    public function printUsers($users) {
        echo "users:<br>";
        $count = 1;
        foreach( $users as $user ) {
            echo $count.": ".$user."<br>";
            $count++;
        }
        echo "<br>";
    }


    public function getLabelSingleUser( $subjectUser, $newline=null, $fullAddressLocation=false ) {

        //echo "subjectUser=".$subjectUser."<br>";
        if( !$subjectUser ) {
            return null;
        }

        //Title
        $administrativeTitleNameStr = $this->getUniqueFirstTitleStr($subjectUser);
        $administrativeTitleNameStr = trim($administrativeTitleNameStr);
        //echo "title=[$administrativeTitleNameStr]<br>";

        //Room
        $locationStr = null;
        $location = $subjectUser->getMainLocation();
        if( $location ) {
            if( $fullAddressLocation ) {
                $locationStr = $location->getLocationAddress($newline);
            } else {
                $locationStr = $location->getLocationNameNoType();
            }
        }
        //echo "locationStr=[$locationStr]<br>";

        //$nl = "&#13;&#10;";
        //$nl = "\n";
        if( $newline === null ) {
            $newline = "<br>\n";
        }

//        $userEl = array();
//        $userEl['name'] = $subjectUser->getUsernameOptimal();
//        $userEl['title'] = $administrativeTitleNameStr;
//        $userEl['room'] = $locationStr;
//        $userElStr = implode("\n",$userEl);

        $userElStr =    $subjectUser->getUsernameOptimal() . $newline .
                        $administrativeTitleNameStr . $newline .
                        $locationStr;

        //echo "userElStr=[$userElStr]<br>";

        return $userElStr;
    }

}