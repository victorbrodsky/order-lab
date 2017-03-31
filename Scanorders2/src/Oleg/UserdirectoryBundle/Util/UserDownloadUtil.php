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


use Symfony\Component\HttpFoundation\RedirectResponse;


class UserDownloadUtil {

    protected $em;
    protected $sc;
    protected $container;

    public function __construct( $em, $sc, $container ) {
        $this->em = $em;
        $this->sc = $sc;
        $this->container = $container;
    }


    public function getSections( $users, $administrativeUsers ) {
        $sections = array();
        foreach( $users as $user ) {
            $instResArr = $user->getDeduplicatedInstitutions();
//            echo '<pre>';
//            print_r($instResArr);
//            echo  '</pre>';

            foreach( $instResArr as $instRes ) {
//                echo 'instRes:<pre>';
//                print_r($instRes);
//                echo  '</pre>';
                $instName = $instRes[0]['instNameWithRoot'];
                //echo "instName=".$instName."<br>";
                //$sections[$instName][] = $user."";
                $sections[$instName][] = $user;
            }
            //break;
        }

//        echo '<br><br>sections:<pre>';
//        print_r($sections);
//        echo  '</pre>';
//        exit();

        //////////////// reorganize sections ////////////////

        //add users with administrative Title to Administration (WCMC)
        $newSections = array();
        foreach ($sections as $sectionName => $sectionUsers) {
            //exit();
            //$sectionName = $section
            if( $sectionName == "Administration (WCMC)" ) {
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
                $newSections[$sectionName] = $sectionUsers;
                //$sections[$sectionName] = $sectionUsers;
            }
        }
        foreach ($sections as $sectionName => $sectionUsers) {
            if( $sectionName != "Administration (WCMC)" ) {
                $newSections[$sectionName] = $sectionUsers;
            }
        }
        //////////////// EOF reorganize sections ////////////////
        //exit();

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

    public function createUserListExcel( $sections ) {

        $author = $this->sc->getToken()->getUser();

        $row = 1;
        $withheader = false;
        $headerSize = 15;

        $ea = new \PHPExcel(); // ea is short for Excel Application

        $ea->getProperties()
            ->setCreator($author."")
            ->setTitle('User List')
            ->setLastModifiedBy($author."")
            ->setDescription('User list in Excel format')
            ->setSubject('PHP Excel manipulation')
            ->setKeywords('excel php office phpexcel wcmc')
            ->setCategory('programming')
        ;

        $ews = $ea->getSheet(0);
        $ews->setTitle('Users');
        $ews->getSheetView()->setZoomScale(150);

        //align all cells to left
        $style = array(
            'alignment' => array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_TOP,
            )
        );
        //$ews->getDefaultStyle()->applyFromArray($style);
        $ews->getParent()->getDefaultStyle()->applyFromArray($style);

        if( $withheader ) {

            $nameHeader = $this->getBoldItalicText("Name", $headerSize);
            $ews->setCellValue('A1', $nameHeader); // Sets cell 'a1' to value 'ID

            $titleHeader = $this->getBoldItalicText("Title", $headerSize);
            $ews->setCellValue('B1', $titleHeader);

            $phoneHeader = $this->getBoldItalicText("Phone", $headerSize);
            $ews->setCellValue('C1', $phoneHeader);

            $roomHeader = $this->getBoldItalicText("Room", $headerSize);
            $ews->setCellValue('D1', $roomHeader);

            $emailHeader = $this->getBoldItalicText("Email", $headerSize);
            $ews->setCellValue('E1', $emailHeader);

            //echo "Users=".count($users)."<br>";

            $row = 3;
        }

        //$sections = array("WCMC"=>$users,"NYP"=>$users);
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
            $row = $this->addSectionUsersToListExcel($sectionName."", $sectionUsers, $ews, $row);
        }

        //exit("222");

        // Auto size columns for each worksheet
        \PHPExcel_Shared_Font::setAutoSizeMethod(\PHPExcel_Shared_Font::AUTOSIZE_METHOD_EXACT);
        foreach ($ea->getWorksheetIterator() as $worksheet) {

            $ea->setActiveSheetIndex($ea->getIndex($worksheet));

            $sheet = $ea->getActiveSheet();
            $cellIterator = $sheet->getRowIterator()->current()->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(true);
            /** @var PHPExcel_Cell $cell */
            foreach ($cellIterator as $cell) {
                $sheet->getColumnDimension($cell->getColumn())->setAutoSize(true);
            }
        }

        //exit("111");
        return $ea;

    }

    public function addSectionUsersToListExcel( $section, $users, $ews, $row ) {

        //section Header
        if(1) {
            $ews->mergeCells('A' . $row . ':' . 'E' . $row);
            $style = array(
                'alignment' => array(
                    'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                )
            );
            $ews->getStyle('A' . $row . ':' . 'E' . $row)->applyFromArray($style);

            $sectionHeader = $this->getBoldItalicText($section, 15);
            $ews->setCellValue('A' . $row, $sectionHeader);
            $row = $row + 1;
        }
        //return $row;

        foreach( $users as $user ) {

            if( !$user ) {
                continue;
            }

            //user
            $this->createRowUser($user,$ews,$row,"regular");

            //assistants
            $assistantsRes = $user->getAssistants();
            $assistants = $assistantsRes['entities'];
            if( count($assistants) > 0 ) {
                foreach( $assistants as $assistant ) {
                    $row = $row + 1;
                    $this->createRowUser($assistant,$ews,$row,"assistant");
                }
            }

            $row = $row + 1;
        }

        //exit("ids=".$fellappids);

        return $row;
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

        //Title
        $administrativeTitleNameStr = $this->getUserTitleStr($user);
        $ews->setCellValue('B'.$row, $administrativeTitleNameStr);
        $ews->getStyle('B'.$row)->getAlignment()->setWrapText(true);

        //Phone
        $phoneStr = "";
        $phoneArr = array();
        foreach( $user->getAllPhones() as $phone ) {
            $phoneArr[] = $phone['prefix'] . $phone['phone'];
        }
        if( count($phoneArr) > 0 ) {
            $phoneStr = implode(" \n", $phoneArr);
        }
        $ews->setCellValue('C'.$row, $phoneStr);
        $ews->getStyle('C'.$row)->getAlignment()->setWrapText(true);

        //Room
        $location = $user->getMainLocation();
        if( $location ) {
            $locationStr = $location->getLocationNameNoType();
            $ews->setCellValue('D' . $row, $locationStr);
        }

        //Email
        $ews->setCellValue('E'.$row, $user->getSingleEmail());
    }

    //Oleg Ivanov, MD => <strong>Ivanov</strong>, Dr. Oleg
    public function convertUsernameToBold( $user, $order="familyname" ) {
        //return $userName;

        $userFirstname = $user->getSingleFirstName();
        $userFamilyname = $user->getSingleLastName();
        $userSalutation = $user->getSingleSalutation();;

        if( $order == "familyname" ) {
            //echo "userFamilyname=$userFamilyname<br>";
            $userName = $this->getBoldText($userFamilyname);

            if( $userSalutation && $userSalutation == "Dr." ) {
                $userName->createText(", " . $userSalutation);
            }

            $userName->createText(" " . $userFirstname);

            if( $userSalutation && $userSalutation != "Dr." ) {
                $userName->createText(", " . $userSalutation);
            }

        }

        if( $order == "firstname" ) {
            $userName = new \PHPExcel_RichText();
            $userName->createTextRun($userFirstname);
            $userName = $this->getBoldText(" " . $userFamilyname, null, $userName);

            if ($userSalutation) {
                $userName->createText(", " . $userSalutation);
            }
        }

        return $userName;
    }

    public function getRichText( $text, $size=null, $richText=null ) {
        if( !$text ) {
            return new \PHPExcel_RichText();
        }
        if( !$richText ) {
            $richText = new \PHPExcel_RichText();
        }
        $objBold = $richText->createTextRun($text);
        if( $size ) {
            $objBold->getFont()->setSize($size);
        }
        return $richText;
    }

    public function getBoldText( $text, $size=null, $richText=null ) {
        if( !$text ) {
            return new \PHPExcel_RichText();
        }
        if( !$richText ) {
            $richText = new \PHPExcel_RichText();
        }
        $objBold = $richText->createTextRun($text);
        $objBold->getFont()->setBold(true);
        if( $size ) {
            $objBold->getFont()->setSize($size);
        }
        return $richText;
    }

    public function getBoldItalicText( $text, $size=null ) {
        $richText = new \PHPExcel_RichText();
        $objBold = $richText->createTextRun($text);
        $objBold->getFont()->setBold(true);
        $objBold->getFont()->setItalic(true);
        if( $size ) {
            $objBold->getFont()->setSize($size);
        }
        return $richText;
    }

    public function getUserTitleStr($user) {
        $titleNameStr = null;

        //first priority is admin title
        $titleNameStr = $user->getUniqueTitlesStr($user->getAdministrativeTitles()," \n");

        if( !$titleNameStr ) {
            //if admin title is empty, then add appointment and medical titles
            $titleNameStrArr = array();
            $titleNameStrArr[] = $user->getUniqueTitlesStr($user->getAppointmentTitles()," \n");
            $titleNameStrArr[] = $user->getUniqueTitlesStr($user->getMedicalTitles()," \n");
            $titleNameStr = implode(" \n",$titleNameStrArr);
        }

        if( !$titleNameStr ) {
            //$titleNameStr = $this->getUserHeaderStr($user);
        }

        return $titleNameStr;
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

    public function sortUsers( $users ) {
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

}