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

namespace Oleg\UserdirectoryBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

//TODO: turn it to BaseCompositeNode
/**
 * @ORM\Entity()
 * @ORM\Table(name="user_commentTypeList")
 */

/**
 * Use Composite pattern:
 * The composite pattern describes that a group of objects is to be treated in the same
 * way as a single instance of an object. The intent of a composite is to "compose" objects into tree structures
 * to represent part-whole hierarchies. Implementing the composite pattern lets clients treat individual objects
 * and compositions uniformly.
 * Use Doctrine Extension Tree for tree manipulation.
 *
 * @Gedmo\Tree(type="nested")
 * @ORM\Entity(repositoryClass="Oleg\UserdirectoryBundle\Repository\TreeRepository")
 * @ORM\Table(
 *  name="user_commentTypeList",
 *  indexes={
 *      @ORM\Index( name="commentTypeList_name_idx", columns={"name"} ),
 *  }
 * )
 */
class CommentTypeList extends BaseCompositeNode
{

    /**
     * @ORM\OneToMany(targetEntity="CommentTypeList", mappedBy="original")
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="CommentTypeList", inversedBy="synonyms")
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id")
     **/
    protected $original;

    /**
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="CommentTypeList", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     **/
    protected $parent;

    /**
     * @ORM\OneToMany(targetEntity="CommentTypeList", mappedBy="parent", cascade={"persist","remove"})
     * @ORM\OrderBy({"lft" = "ASC"})
     **/
    protected $children;

    /**
     * Organizational Group Types
     * level int in OrganizationalGroupType corresponds to this level integer: 1-Comment Category, 2-Comment Name
     * For example, OrganizationalGroupType with level=1, set this level to 1.
     *
     * @ORM\ManyToOne(targetEntity="CommentGroupType", cascade={"persist"})
     */
    private $organizationalGroupType;



    public function __construct($author=null) {
        parent::__construct($author);
    }



    /**
     * @param mixed $organizationalGroupType
     */
    public function setOrganizationalGroupType($organizationalGroupType)
    {
        $this->organizationalGroupType = $organizationalGroupType;
        $this->setLevel($organizationalGroupType->getLevel());
    }

    /**
     * @return mixed
     */
    public function getOrganizationalGroupType()
    {
        return $this->organizationalGroupType;
    }

    /**
     * Overwrite base setParent method: adjust this organizationalGroupType according to the first parent child
     * @param mixed $parent
     */
    public function setParent(CompositeNodeInterface $parent = null)
    {
        $this->parent = $parent;

        //change organizationalGroupType of this entity to the first child organizationalGroupType of the parent
        if( $parent && count($parent->getChildren()) > 0 ) {
            $firstSiblingOrgGroupType = $parent->getChildren()->first()->getOrganizationalGroupType();
            $this->setOrganizationalGroupType($firstSiblingOrgGroupType);
        }
    }


    public function __toString()
    {
        if( $this->getAbbreviation() && $this->getAbbreviation() != "" ) {
            return $this->getAbbreviation()."";
        }

        return $this->getName()."";
    }

    public function getClassName()
    {
        return "CommentType";
    }

}