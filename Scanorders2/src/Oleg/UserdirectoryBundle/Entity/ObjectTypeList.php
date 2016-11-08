<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_objectTypeList")
 */
class ObjectTypeList extends ListAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="ObjectTypeList", mappedBy="original")
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="ObjectTypeList", inversedBy="synonyms")
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id")
     **/
    protected $original;


    //use entityNamespace, entityName and entityId to link this object type to
    // a specific object type implementation (i.e. ObjectTypeFormText), where the values will be stored.



//    //Fields specifying a subject entity
//    /**
//     * i.e. "Oleg\OrderformBundle\Entity"
//     * @ORM\Column(type="string", nullable=true)
//     */
//    private $entityNamespace;
//    /**
//     * i.e. "Patient"
//     * @ORM\Column(type="string", nullable=true)
//     */
//    private $entityName;




//    /**
//     * @return mixed
//     */
//    public function getEntityNamespace()
//    {
//        return $this->entityNamespace;
//    }
//
//    /**
//     * @param mixed $entityNamespace
//     */
//    public function setEntityNamespace($entityNamespace)
//    {
//        //remove "Proxies\__CG__\" if $entityNamespace="Proxies\__CG__\Oleg\UserdirectoryBundle\Entity"
//        $proxyStr = "Proxies\__CG__\\";
//        //$proxyStr = "Oleg\UserdirectoryBundle\\";
//        //echo "proxyStr=".$proxyStr."<br>";
//        if( strpos($entityNamespace, $proxyStr) !== false ) {
//            //echo "remove=".$proxyStr."<br>";
//            $entityNamespace = str_replace($proxyStr, "", $entityNamespace);
//        }
//        //exit("entityNamespace=".$entityNamespace);
//
//        $this->entityNamespace = $entityNamespace;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getEntityName()
//    {
//        return $this->entityName;
//    }
//
//    /**
//     * @param mixed $entityName
//     */
//    public function setEntityName($entityName)
//    {
//        $this->entityName = $entityName;
//    }


}