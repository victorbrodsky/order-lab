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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'ctp_siteparameter')]
#[ORM\Entity]
class CtpSiteParameter {

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
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
    #[ORM\JoinTable(name: 'ctp_ctpsiteparameters_ctplogo')]
    #[ORM\JoinColumn(name: 'ctpsiteparameter_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'ctplogo_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToMany(targetEntity: 'App\UserdirectoryBundle\Entity\Document', cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['createdate' => 'DESC'])]
    private $ctpLogos;

    //Is it a footer text?
    // B- left upper corner text next to logo in the same rectangle ("Weill Cornell Medicine") -
    // separate this field into two fields in site settings page:
    //   "Site logo text top line": "Weill Cornell"
    //   "Site logo text bottom line": "Medicine"

    //   "Site logo text top line": "Weill Cornell"
    #[ORM\Column(type: 'string', nullable: true)]
    private $logoTopText;

    //   "Site logo text bottom line": "Medicine"
    #[ORM\Column(type: 'string', nullable: true)]
    private $logoBottomText;

    //C- left upper corner text next to logo in the same rectangle top line font color
    // (font color for the words "Weill Cornell") - field name: Site logo text top line color
    #[ORM\Column(type: 'string', nullable: true)]
    private $logoTopTextColor;

    //D- left upper corner text next to logo in the same rectangle bottom line font color
    // (font color for the word "Medicine") - field name: Site logo text bottom line color
    #[ORM\Column(type: 'string', nullable: true)]
    private $logoBottomTextColor;

    //F- Navbar site title ("Center for Translational Pathology") - field name: Site title
    #[ORM\Column(type: 'string', nullable: true)]
    private $navbarSiteTitle;

    //G- Navbar button title for "Path2path Dashboard" - field name: Navigation bar applications button title
    #[ORM\Column(type: 'string', nullable: true)]
    private $navbarButtonTitle;

    //H- Footer institution link text (you may have it already)
    //I- Footer institution link URL (you may have it already)
    //Combine text and URL: '<a href="https://weillcornell.org/">Weill Cornell Medicine</a>'
    #[ORM\Column(type: 'string', nullable: true)]
    private $footerInstLinkText;

    //I- Footer institution link URL (you may have it already)
    #[ORM\Column(type: 'string', nullable: true)]
    private $footerInstLink;

    //J- Footer department link text (you may have it already)
    //K- Footer department link URL (you may have it already)
    //Combine text and URL: '<a href="{{ path("ctp_home") }}">Center for Translational Pathology</a>'
    #[ORM\Column(type: 'string', nullable: true)]
    private $footerDepLinkText;

    //K- Footer department link URL (you may have it already)
    #[ORM\Column(type: 'string', nullable: true)]
    private $footerDepLink;



    public function __construct() {
        $this->ctpLogos = new ArrayCollection();
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

    /**
     * @return mixed
     */
    public function getCtpLogos()
    {
        return $this->ctpLogos;
    }

    /**
     * @param mixed $ctpLogos
     */
    public function setCtpLogos($ctpLogos)
    {
        $this->ctpLogos = $ctpLogos;
    }

    public function addCtpLogo($item)
    {
        if( $item && !$this->ctpLogos->contains($item) ) {
            $this->ctpLogos->add($item);
            return $this;
        }
        return null;
    }

    public function removeCtpLogo($item)
    {
        $this->ctpLogos->removeElement($item);
    }

    /**
     * @return mixed
     */
    public function getLogoTopText()
    {
        return $this->logoTopText;
    }

    /**
     * @param mixed $logoTopText
     */
    public function setLogoTopText($logoTopText)
    {
        $this->logoTopText = $logoTopText;
    }

    /**
     * @return mixed
     */
    public function getLogoBottomText()
    {
        return $this->logoBottomText;
    }

    /**
     * @param mixed $logoBottomText
     */
    public function setLogoBottomText($logoBottomText)
    {
        $this->logoBottomText = $logoBottomText;
    }

    /**
     * @return mixed
     */
    public function getLogoTopTextColor()
    {
        return $this->logoTopTextColor;
    }

    /**
     * @param mixed $logoTopTextColor
     */
    public function setLogoTopTextColor($logoTopTextColor)
    {
        $this->logoTopTextColor = $logoTopTextColor;
    }

    /**
     * @return mixed
     */
    public function getLogoBottomTextColor()
    {
        return $this->logoBottomTextColor;
    }

    /**
     * @param mixed $logoBottomTextColor
     */
    public function setLogoBottomTextColor($logoBottomTextColor)
    {
        $this->logoBottomTextColor = $logoBottomTextColor;
    }

    /**
     * @return mixed
     */
    public function getNavbarSiteTitle()
    {
        return $this->navbarSiteTitle;
    }

    /**
     * @param mixed $navbarSiteTitle
     */
    public function setNavbarSiteTitle($navbarSiteTitle)
    {
        $this->navbarSiteTitle = $navbarSiteTitle;
    }

    /**
     * @return mixed
     */
    public function getNavbarButtonTitle()
    {
        return $this->navbarButtonTitle;
    }

    /**
     * @param mixed $navbarButtonTitle
     */
    public function setNavbarButtonTitle($navbarButtonTitle)
    {
        $this->navbarButtonTitle = $navbarButtonTitle;
    }

    /**
     * @return mixed
     */
    public function getFooterInstLink()
    {
        return $this->footerInstLink;
    }

    /**
     * @param mixed $footerInstLink
     */
    public function setFooterInstLink($footerInstLink)
    {
        $this->footerInstLink = $footerInstLink;
    }

    /**
     * @return mixed
     */
    public function getFooterDepLink()
    {
        return $this->footerDepLink;
    }

    /**
     * @param mixed $footerDepLink
     */
    public function setFooterDepLink($footerDepLink)
    {
        $this->footerDepLink = $footerDepLink;
    }

    /**
     * @return mixed
     */
    public function getFooterInstLinkText()
    {
        return $this->footerInstLinkText;
    }

    /**
     * @param mixed $footerInstLinkText
     */
    public function setFooterInstLinkText($footerInstLinkText)
    {
        $this->footerInstLinkText = $footerInstLinkText;
    }

    /**
     * @return mixed
     */
    public function getFooterDepLinkText()
    {
        return $this->footerDepLinkText;
    }

    /**
     * @param mixed $footerDepLinkText
     */
    public function setFooterDepLinkText($footerDepLinkText)
    {
        $this->footerDepLinkText = $footerDepLinkText;
    }

    


    public function __toString() {
        return "CtpSiteParameter ID=".$this->getId();
    }


}

