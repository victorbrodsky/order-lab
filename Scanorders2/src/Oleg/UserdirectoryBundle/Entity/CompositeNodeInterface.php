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
 * Created by PhpStorm.
 * User: DevServer
 * Date: 3/12/15
 * Time: 3:23 PM
 */

namespace Oleg\UserdirectoryBundle\Entity;


interface CompositeNodeInterface {

    // Composites' methods
    public function addChild( $component );

    public function removeChild( $component );

    public function getChild( $index ); //get child by position index

    public function getChildren();

    public function getParent();
    public function setParent(CompositeNodeInterface $parent = null);

    public function setLevel($level);
    public function getLevel();

    public function setRgt($component);
    public function getRgt();

    public function setLft($component);
    public function getLft();

    public function setRoot($root);
    public function getRoot();
} 