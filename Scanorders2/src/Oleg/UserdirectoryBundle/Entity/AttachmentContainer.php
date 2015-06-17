<?php
/**
 * Created by PhpStorm.
 * User: DevServer
 * Date: 3/26/15
 * Time: 4:00 PM
 */

namespace Oleg\UserdirectoryBundle\Entity;


use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;


/**
 * @ORM\Entity
 * @ORM\Table(name="user_attachmentContainer")
 */
class AttachmentContainer {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\OneToMany(targetEntity="DocumentContainer", mappedBy="attachmentContainer", cascade={"persist","remove"})
     **/
    private $documentContainers;



    public function __construct() {
        $this->documentContainers = new ArrayCollection();
    }



    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }




    public function getDocumentContainers()
    {
        return $this->documentContainers;
    }
    public function addDocumentContainer($item)
    {
        if( $item && !$this->documentContainers->contains($item) ) {
            $this->documentContainers->add($item);
            $item->setAttachmentContainer($this);
        }
    }
    public function removeDocumentContainer($item)
    {
        $this->documentContainers->removeElement($item);
    }



    public function __toString() {
        return "AttachmentContainer:"."documents=".count($this->getDocumentContainers())."<br>";
    }

} 