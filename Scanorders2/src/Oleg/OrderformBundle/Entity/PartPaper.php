<?php
/**
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 9/10/13
 * Time: 5:46 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

use Oleg\UserdirectoryBundle\Entity\Document;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;


/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="scan_partPaper")
 */
class PartPaper extends PartArrayFieldAbstract
{
//    /**
//     * @ORM\Id
//     * @ORM\Column(type="integer")
//     * @ORM\GeneratedValue(strategy="AUTO")
//     */
//    protected  $id;
//
//    /**
//     * @ORM\Column(type="string", nullable=true)
//     */
//    protected  $name;
//
//    /**
//     * @ORM\Column(type="string", nullable=true)
//     */
//    protected  $path;
//
//    protected  $temp;
//
//    /**
//     * @Assert\File(maxSize="6000000")
//     */
//    protected  $field;

    /**
     * @ORM\ManyToOne(targetEntity="Part", inversedBy="paper", cascade={"persist"})
     * @ORM\JoinColumn(name="part_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    protected $part;

//    /**
//     * @ORM\OneToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\Document")
//     */
//    protected $document;
    /**
     * @ORM\ManyToMany(targetEntity="Oleg\UserdirectoryBundle\Entity\Document")
     * @ORM\JoinTable(name="scan_partpaper_document",
     *      joinColumns={@ORM\JoinColumn(name="partpaper_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="document_id", referencedColumnName="id", unique=true)}
     *      )
     **/
    protected $documents;


    public function __construct( $status = 'valid', $provider = null, $source = null ) {
        parent::__construct($status,$provider,$source);
        $this->documents = new ArrayCollection();
    }


    /**
     * Add document
     *
     * @param \Oleg\OrderformBundle\Entity\Document $document
     * @return PartPaper
     */
    public function addDocument($document)
    {
        if( $document == null ) {
            $document = new Document();
        }

        if( !$this->documents->contains($document) ) {
            $this->documents->add($document);
        }

        return $this;
    }
    /**
     * Remove document
     *
     * @param \Oleg\OrderformBundle\Entity\Document $document
     */
    public function removeDocument($document)
    {
        $this->documents->removeElement($document);
    }

    /**
     * Get documents
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDocuments()
    {
        return $this->documents;
    }

    public function setDocuments($documents)
    {
        //$this->documents = new ArrayCollection();
        return $this->documents = $documents;
    }

    public function __toString() {
        return "Documents count=".count($this->getDocuments());
    }



//    /**
//     * @param mixed $name
//     */
//    public function setName($name)
//    {
//        $this->name = $name;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getName()
//    {
//        return $this->name;
//    }


//    public function getAbsolutePath()
//    {
//        return null === $this->path
//            ? null
//            : $this->getUploadRootDir().'/'.$this->path;
//    }
//
//    public function getWebPath()
//    {
//        return null === $this->path
//            ? null
//            : $this->getUploadDir().'/'.$this->path;
//    }
//
//    protected function getUploadRootDir()
//    {
//        // the absolute directory path where uploaded
//        // documents should be saved
//        return __DIR__.'/'.$this->getPrefixPath().$this->getUploadDir();
//    }
//
//    public function getRelativeUploadFullPath()
//    {
//        // the absolute directory path where uploaded
//        // documents should be saved
//        return $this->getPrefixPath().$this->getUploadDir().'/'.$this->getPath();
//    }
//
//    protected function getPrefixPath() {
//        return '../../../../';
//    }
//
//    protected function getUploadDir()
//    {
//        // get rid of the __DIR__ so it doesn't screw up
//        // when displaying uploaded doc/image in the view.
//        return 'uploads/documents';
//    }
//
//    /**
//     * Get field.
//     *
//     * @return UploadedField
//     */
//    public function getField()
//    {
//        return $this->field;
//    }
//
//    /**
//     * Sets field. UploadedFile
//     *
//     * @param UploadedField $field
//     */
//    public function setField( $field = null )
//    {
//        $this->field = $field;
//        // check if we have an old image path
//        if (isset($this->path)) {
//            // store the old name to delete after the update
//            $this->temp = $this->path;
//            $this->path = null;
//        } else {
//            $this->path = 'initial';
//        }
//    }
//
//    /**
//     * @ORM\PrePersist()
//     * @ORM\PreUpdate()
//     */
//    public function preUpload()
//    {
//        if (null !== $this->getField()) {
//
//            //echo "upload field=".$this->getField()."<br>";
//            //echo "original name=".$this->getField()->getClientOriginalName()."<br>";
//            //exit();
//            $this->name = $this->getField()->getClientOriginalName();
//
//            // do whatever you want to generate a unique name
//            $filename = sha1(uniqid(mt_rand(), true));
//            $this->path = $filename.'.'.$this->getField()->guessExtension();
//            //echo "preUpload path=".$this->path."<br>";
//        }
//    }
//
//    /**
//     * @ORM\PostPersist()
//     * @ORM\PostUpdate()
//     */
//    public function upload()
//    {
//        //echo "upload <br>";
//
//        if (null === $this->getField()) {
//            return;
//        }
//
//        // if there is an error when moving the file, an exception will
//        // be automatically thrown by move(). This will properly prevent
//        // the entity from being persisted to the database on error
//        $this->getField()->move($this->getUploadRootDir(), $this->path);
//
//        // check if we have an old image
//        if (isset($this->temp)) {
//            // delete the old image
//            unlink($this->getUploadRootDir().'/'.$this->temp);
//            // clear the temp image path
//            $this->temp = null;
//        }
//        $this->field = null;
//    }
//
//    /**
//     * @ORM\PostRemove()
//     */
//    public function removeUpload()
//    {
//        if ($field = $this->getAbsolutePath()) {
//            unlink($field);
//        }
//    }
//
//    public function __toString() {
//        //return "Paper: name=".$this->name.",path=".$this->path.",temp=".$this->temp.",field(file) =".$this->field."<br>";
//        return '<a href="../../../../web/uploads/documents/' . $this->path . '" target="_blank">' . $this->name . '</a>';
//        //return '<a href="http://collage.med.cornell.edu/order/scanorder/Scanorders2/web/app_dev.php/uploads/documents/' . $this->path . '" target="_blank">' . $this->name . '</a>';
//    }
//
//
////    Getters and Setters
//
//
//    /**
//     * @param mixed $id
//     */
//    public function setId($id)
//    {
//        $this->id = $id;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getId()
//    {
//        return $this->id;
//    }
//
//    /**
//     * @param mixed $name
//     */
//    public function setName($name)
//    {
//        $this->name = $name;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getName()
//    {
//        return $this->name;
//    }
//
//    /**
//     * @param mixed $path
//     */
//    public function setPath($path)
//    {
//        $this->path = $path;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getPath()
//    {
//        return $this->path;
//    }
//
//    /**
//     * @param mixed $temp
//     */
//    public function setTemp($temp)
//    {
//        $this->temp = $temp;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getTemp()
//    {
//        return $this->temp;
//    }

}