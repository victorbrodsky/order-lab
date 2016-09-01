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
 * @ORM\Entity(repositoryClass="Oleg\UserdirectoryBundle\Repository\DocumentRepository")
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
     * @ORM\ManyToOne(targetEntity="DocumentTypeList")
     * @ORM\JoinColumn(name="type_id", referencedColumnName="id", nullable=true)
     */
    private $type;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="creator_id", referencedColumnName="id", nullable=true)
     */
    protected $creator;

    /**
     * @var \DateTime
     * @ORM\Column(name="createdate", type="datetime", nullable=true)
     */
    private $createdate;

    /**
     * Image title
     * @ORM\Column(type="string", nullable=true)
     */
    private $title;

    /**
     * Unique ID of the file. For example, google drive file id
     * @ORM\Column(type="string", nullable=true)
     */
    private $uniqueid;

//    /**
//     * @ORM\ManyToOne(targetEntity="GeneralEntity", cascade={"persist","remove"})
//     * @ORM\JoinColumn(name="useObject_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
//     */
//    private $useObject;

    //Fields specifying a subject entity
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $entityNamespace;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $entityName;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $entityId;



    public function __construct($creator=null) {
        $this->setCreator($creator);
        $this->setCreatedate(new \DateTime());
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
     * @param mixed $uniqueid
     */
    public function setUniqueid($uniqueid)
    {
        $this->uniqueid = $uniqueid;
    }

    /**
     * @return mixed
     */
    public function getUniqueid()
    {
        return $this->uniqueid;
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

    public function setCreatedate($date)
    {
        $this->createdate = $date;  //new \DateTime();
    }
    public function getCreatedate()
    {
        return $this->createdate;
    }

    /**
     * @param mixed $creator
     */
    public function setCreator($creator)
    {
        $this->creator = $creator;
    }

    /**
     * @return mixed
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }




    /**
     * @return mixed
     */
    public function getEntityNamespace()
    {
        return $this->entityNamespace;
    }

    /**
     * @param mixed $entityNamespace
     */
    public function setEntityNamespace($entityNamespace)
    {
        $this->entityNamespace = $entityNamespace;
    }

    /**
     * @return mixed
     */
    public function getEntityName()
    {
        return $this->entityName;
    }

    /**
     * @param mixed $entityName
     */
    public function setEntityName($entityName)
    {
        $this->entityName = $entityName;
    }

    /**
     * @return mixed
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     * @param mixed $entityId
     */
    public function setEntityId($entityId)
    {
        $this->entityId = $entityId;
    }

    //Util method
    public function isOrphan()
    {
        if( !$this->getEntityName() && !$this->getEntityNamespace() && !$this->getEntityId() ) {
            return true;
        }
        return false;
    }
    public function createUseObject($object)
    {
        $this->setObject($object);
    }
    public function setObject($object) {
        $class = new \ReflectionClass($object);
        $className = $class->getShortName();
        $classNamespace = $class->getNamespaceName();

        if( $className && !$this->getEntityName() ) {
            $this->setEntityName($className);
        }

        if( $classNamespace && !$this->getEntityNamespace() ) {
            $this->setEntityNamespace($classNamespace);
        }

        if( !$this->getEntityId() && $object->getId() ) {
            $this->setEntityId($object->getId());
        }
    }
    public function clearUseObject()
    {
        $this->setEntityName(NULL);
        $this->setEntityNamespace(NULL);
        $this->setEntityId(NULL);
    }
//    /**
//     * @return mixed
//     */
//    public function getUseObject()
//    {
//        return $this->useObject;
//    }
//
//    /**
//     * @param mixed $useObject
//     */
//    public function setUseObject($useObject)
//    {
//        $this->useObject = $useObject;
//    }
//
//    public function createUseObject($object)
//    {
//        $useObject = $this->getUseObject();
//        if( !$useObject ) {
//            $useObject = new GeneralEntity();
//        }
//        $useObject->setObject($object);
//
//        $this->setUseObject($useObject);
//
//        return $useObject;
//    }



    public function __toString() {
        return $this->getRelativeUploadFullPath();
    }

//    public function getTestPath() {
//        return $this->getAbsoluteUploadFullPath();
//    }

    //get server path to delete file: /var/www/test/folder/images/image_name.jpeg
    public function getServerPath()
    {
        //echo "getcwd=".getcwd()."<br>"; //getcwd()=C:\Program Files (x86)\Aperio\Spectrum\htdocs\order\scanorder\Scanorders2
        return $this->getUploadDirectory().'/'.$this->getUniquename();
    }

    //use for command console to get a full absolute server path
    //example: C:\Users\ch3\Documents\MyDocs\WCMC\ORDER\scanorder\Scanorders2/web/Uploaded/fellapp/documents/56fbf9e8867c3.jpg
    public function getFullServerPath()
    {
        //echo "getcwd=".getcwd()."<br>"; //getcwd()=C:\Program Files (x86)\Aperio\Spectrum\htdocs\order\scanorder\Scanorders2
        $fullPath = getcwd() . "/web/" . $this->getUploadDirectory().'/'.$this->getUniquename();
        //$fullPath = realpath($fullPath);
        return $fullPath;
    }

    public function getAbsoluteUploadFullPath()
    {
        return "http://" . $_SERVER['SERVER_NAME'] . "/order/" . $this->getUploadDirectory().'/'.$this->getUniquename();

        //return $this->getUploadDirectory().'/'.$this->getUniquename();
        //return "http://collage.med.cornell.edu/".$this->getRelativeUploadFullPath();
    }

//    public function getCommandAbsoluteUploadFullPath()
//    {
//        //return $this->container->get('kernel')->getRootDir() . '/../web/' . $this->getUploadDirectory().'/'.$this->getUniquename();
//        return realpath($this->container->get('kernel')->getRootDir() . "/../web/" . $this->getServerPath());
//    }

    public function getRelativeUploadFullPath()
    {
        return $this->getPrefixPath().$this->getUploadDirectory().'/'.$this->getUniquename();
    }

    protected function getPrefixPath() {
        return '../../../../order/';
    }

    public function getFileSystemPath() {
        //echo "getcwd=".getcwd()."<br>";
        return getcwd() . "\\" . $this->getServerPath();
    }

    public function getSizeStr()
    {
//        $size = $this->size;
//        if( $size && $size != 0 ) {
//            $size = $size/1000000;
//            $size = round($size, 1);
//            $size = $size . " MiB";
//        }
        return $this->Size($this->size);
    }


    public function Size( $size )
    {
        //$bytes = sprintf('%u', filesize($path));
        $bytes = $size;

        if ($bytes > 0)
        {
            $unit = intval(log($bytes, 1024));
            $units = array('B', 'KiB', 'MiB', 'GB');

            if (array_key_exists($unit, $units) === true)
            {
                return sprintf('%d %s', $bytes / pow(1024, $unit), $units[$unit]);
            }
        }

        return $bytes;
    }

    public function getWidthByHeight($newHeight) {
        list($originalWidth, $originalHeight) = getimagesize($this->getServerPath());
        if( $originalHeight ) {
            $ratio = $originalWidth / $originalHeight;
        } else {
            $ratio = 1;
        }
        $newWidth = $newHeight * $ratio;
        return $newWidth;
    }

    public function getFullDescriptionStr() {
        return "Document: id=".$this->getId().", originalname=".$this->getOriginalnameClean().", uniquename=".$this->getUniquename()."<br>";
    }

    //replace commas and dots in the filename with "_"
    public function getOriginalnameClean()
    {
        $filename = $this->getOriginalname();

        //remove commas
        $filename = str_replace(",", "_", $filename);

        $dotscount = substr_count($filename, '.');
        if ($dotscount > 1) {
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            if ($ext) {
                $filename = str_replace("." . $ext, "", $filename);
                $filename = str_replace(".", "_", $filename);
                $filename = $filename . "." . $ext;
            }
        }

        return $filename;
    }

}