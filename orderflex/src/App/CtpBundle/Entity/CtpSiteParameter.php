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


namespace App\CtpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

//#[ORM\Table(name: 'ctp_siteParameter')]
//#[ORM\Entity]
class CtpSiteParameter {

    //#[ORM\Id]
    //#[ORM\Column(type: 'integer')]
    //#[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    /*
        A- left upper corner Navbar logo image (Dropzone field) - field name - Site logo image

        B- left upper corner text next to logo in the same rectangle ("Weill Cornell Medicine") - separate this field into two fields in site settings page:

        "Site logo text top line": "Weill Cornell"

        "Site logo text bottom line": "Medicine"

        since the two lines are of two different colors and are on two different lines.

        C- left upper corner text next to logo in the same rectangle top line font color (font color for the words "Weill Cornell") - field name: Site logo text top line color

        D- left upper corner text next to logo in the same rectangle bottom line font color (font color for the word "Medicine") - field name: Site logo text bottom line color

        E- left upper corner text next to logo in the same rectangle background color (background color for the DIV with "Weill Cornell Medicine and logo image") - field name: Site logo background color

        F- Navbar site title ("Center for Translational Pathology") - field name: Site title

        G- Navbar button title for "Path2path Dashboard" - field name: Navigation bar applications button title

        H- Footer institution link text (you may have it already)

        I- Footer institution link URL (you may have it already)

        J- Footer department link text (you may have it already)

        K- Footer department link URL (you may have it already)
     */

    //A- left upper corner Navbar logo image (Dropzone field) - field name - Site logo image
//    #[ORM\JoinTable(name: 'ctp_ctpsiteparameters_ctplogo')]
//    #[ORM\JoinColumn(name: 'ctpsiteparameter_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
//    #[ORM\InverseJoinColumn(name: 'ctplogo_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
//    #[ORM\ManyToMany(targetEntity: 'App\UserdirectoryBundle\Entity\Document', cascade: ['persist', 'remove'])]
//    #[ORM\OrderBy(['createdate' => 'DESC'])]
//    private $ctpLogos;


    public function __construct() {

    }



    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }



}

