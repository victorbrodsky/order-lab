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

    public function createUserListExcel() {

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

        //align all cells to left
        $style = array(
            'alignment' => array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
            )
        );
        //$ews->getDefaultStyle()->applyFromArray($style);
        $ews->getParent()->getDefaultStyle()->applyFromArray($style);

        $ews->setCellValue('A1', 'Name'); // Sets cell 'a1' to value 'ID
        $ews->setCellValue('B1', 'Title');
        $ews->setCellValue('C1', 'Phone');
        $ews->setCellValue('D1', 'Room');
        $ews->setCellValue('E1', 'Email');

        $users = $this->em->getRepository('OlegUserdirectoryBundle:User')->findAll();
        //echo "Users=".count($users)."<br>";

        $row = 2;
        foreach( $users as $user ) {

            if( !$user ) {
                continue;
            }

            $ews->setCellValue('A'.$row, $user->getUsernameOptimal());
            //$ews->setCellValue('B'.$row, $user->getUsernameOptimal());
            //$ews->setCellValue('C'.$row, $user->getAllPhones());
            //$ews->setCellValue('B'.$row, $user->getUsernameOptimal());
            //$ews->setCellValue('B'.$row, $user->getAllEmail());

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

}