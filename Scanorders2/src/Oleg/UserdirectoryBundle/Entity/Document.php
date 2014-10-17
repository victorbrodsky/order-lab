<?php
/**
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 9/10/14
 * Time: 5:46 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;


/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="user_document")
 */
class Document {


    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private  $id;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Assert\NotBlank
     */
    private  $originalname;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Assert\NotBlank
     */
    private  $uniquename;


    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private  $uploadDirectory;

    /**
     * @ORM\Column(type="decimal", nullable=true)
     */
    private  $size;

    /**
     * @var File  - not a persistent field!
     *
     * @Assert\File(maxSize="6000000")
     */
    private  $file;

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
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param \Oleg\UserdirectoryBundle\Entity\File $file
     */
    public function setFile($file)
    {
        $this->file = $file;
    }

    /**
     * @return \Oleg\UserdirectoryBundle\Entity\File
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param mixed $originalname
     */
    public function setOriginalname($originalname)
    {
        $this->originalname = $originalname;
    }

    /**
     * @return mixed
     */
    public function getOriginalname()
    {
        return $this->originalname;
    }

    /**
     * @param mixed $uniquename
     */
    public function setUniquename($uniquename)
    {
        $this->uniquename = $uniquename;
    }

    /**
     * @return mixed
     */
    public function getUniquename()
    {
        return $this->uniquename;
    }

    /**
     * @param mixed $size
     */
    public function setSize($size)
    {
        $this->size = $size;
    }

    /**
     * @return mixed
     */
    public function getSize()
    {
        return $this->size;
    }




    /**
     * @param mixed $uploadDirectory
     */
    public function setUploadDirectory($uploadDirectory)
    {
        $this->uploadDirectory = $uploadDirectory;
    }

    /**
     * @return mixed
     */
    public function getUploadDirectory()
    {
        return $this->uploadDirectory;
    }


    public function __toString() {
        return $this->getRelativeUploadFullPath();
    }

    public function getAbsoluteUploadFullPath()
    {
        return "http://" . $_SERVER['SERVER_NAME'] . "/order/" . $this->getUploadDirectory().'/'.$this->getUniquename();

        //return $this->getUploadDirectory().'/'.$this->getUniquename();
        //return "http://collage.med.cornell.edu/".$this->getRelativeUploadFullPath();
    }

    public function getRelativeUploadFullPath()
    {
        return $this->getPrefixPath().$this->getUploadDirectory().'/'.$this->getUniquename();
    }

    protected function getPrefixPath() {
        return '../../../../order/';
    }


    public function getSizeStr()
    {
        $size = $this->size;
        if( $size && $size != 0 ) {
            $size = $size/1000000;
            $size = round($size, 1);
            $size = $size . " MiB";
        }
        return $size;
    }


}