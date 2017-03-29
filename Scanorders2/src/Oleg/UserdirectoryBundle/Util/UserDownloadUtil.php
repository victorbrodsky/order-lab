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

    public function createUserListExcel( $users ) {

        $author = $this->sc->getToken()->getUser();

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
            )
        );
        //$ews->getDefaultStyle()->applyFromArray($style);
        $ews->getParent()->getDefaultStyle()->applyFromArray($style);

        $size = 15;

        $nameHeader = $this->getBoldItalicText("Name",$size);
        $ews->setCellValue('A1', $nameHeader); // Sets cell 'a1' to value 'ID

        $titleHeader = $this->getBoldItalicText("Title",$size);
        $ews->setCellValue('B1', $titleHeader);

        $phoneHeader = $this->getBoldItalicText("Phone",$size);
        $ews->setCellValue('C1', $phoneHeader);

        $roomHeader = $this->getBoldItalicText("Room",$size);
        $ews->setCellValue('D1', $roomHeader);

        $emailHeader = $this->getBoldItalicText("Email",$size);
        $ews->setCellValue('E1', $emailHeader);

        //echo "Users=".count($users)."<br>";

        $row = 3;
        foreach( $users as $user ) {

            if( !$user ) {
                continue;
            }

            $this->createRowUser($user,$ews,$row);

            $assistantsRes = $user->getAssistants();
            $assistants = $assistantsRes['entities'];
            if( count($assistants) > 0 ) {
                foreach( $assistants as $assistant ) {
                    $row = $row + 1;
                    $this->createRowUser($assistant,$ews,$row,"    ",false);
                }
            }

            $row = $row + 1;
        }

        //exit("ids=".$fellappids);


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


        return $ea;

    }

    public function createRowUser( $user, $ews, $row, $prefix="", $bold=true ) {
        if( !$user ) {
            return;
        }

        //Name
        $userName = $user->getUsernameOptimal();

        if( $bold ) {
            //Oleg Ivanov, MD => <strong>Ivanov</strong> Oleg, MD
            //Oleg Ivanov, MD => <strong>Ivanov</strong>, Dr. Oleg
            $userName = str_replace(",", "", $userName); //Oleg Ivanov MD
            $userNameArr = explode(" ", $userName);
            if (count($userNameArr) >= 2) {
                $userFirstname = $userNameArr[0];
                $userFamilyname = $userNameArr[1];
                $userDegree = null;
                if (count($userNameArr) == 3) {
                    $userDegree = $userNameArr[2];
                }

                $userName = $this->getBoldText($userFamilyname);

                $userName->createText(" " . $userFirstname);

                if ($userDegree) {
                    $userName->createText(", " . $userDegree);
                }

            }
        }
        if( $prefix ) {
            $userName = $prefix.$userName;
        }

        $ews->setCellValue('A'.$row, $userName);

        //Title
        $ews->setCellValue('B'.$row, $user->getId());

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
        $ews->setCellValue('D'.$row, $user->getUsernameOptimal());

        //Email
        $ews->setCellValue('E'.$row, $user->getSingleEmail());
    }

    public function getBoldText( $text, $size=null ) {
        $richText = new \PHPExcel_RichText();
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

}