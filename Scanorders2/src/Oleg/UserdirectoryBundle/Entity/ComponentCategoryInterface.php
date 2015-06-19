<?php
/**
 * Created by PhpStorm.
 * User: DevServer
 * Date: 3/12/15
 * Time: 3:23 PM
 */

namespace Oleg\UserdirectoryBundle\Entity;


interface ComponentCategoryInterface {

    // Composites' methods
    public function addChild( $component );

    public function removeChild( $component );

    public function getChild( $index );

    public function getChildren();

    public function getParent();

    public function setLevel($level);
    public function getLevel();

    public function setRgt($component);
    public function getRgt();

    public function setLft($component);
    public function getLft();
} 