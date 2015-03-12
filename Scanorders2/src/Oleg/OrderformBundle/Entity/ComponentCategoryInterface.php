<?php
/**
 * Created by PhpStorm.
 * User: DevServer
 * Date: 3/12/15
 * Time: 3:23 PM
 */

namespace Oleg\OrderformBundle\Entity;


interface ComponentCategoryInterface {

    // Composites' methods
    public function addChild( $component );

    public function removeChild( $component );

    public function getChild( $index );

    public function getChildren();

    public function getParent();

    public function setLevel($level);
    public function getLevel();

    public function setRight($component);
    public function getRight();

    public function setLeft($component);
    public function getLeft();
} 