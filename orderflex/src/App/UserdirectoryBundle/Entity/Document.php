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
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 9/10/14
 * Time: 5:46 PM
 * To change this template use File | Settings | File Templates.
 */

///**
// * Single document implementation:
// * 1) add interface method removeDocument
// * 2) modify setter method (i.e. setTransresLogo): add $transresLogo->createUseObject($this);
// * 3) add in setHolderDocumentsDql: case "AppTranslationalResearchBundle:TransResSiteParameters" => "comment.transresLogo";
// *
// * @ORM\OneToOne(targetEntity="App\UserdirectoryBundle\Entity\Document", cascade={"persist","remove"})
// * @ORM\JoinColumn(name="transresLogo_id", referencedColumnName="id", nullable=true)
// **/
//private $transresLogo;

namespace App\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;


#[ORM\Table(name: 'user_document')]
#[ORM\Entity(repositoryClass: 'App\UserdirectoryBundle\Repository\DocumentRepository')]
#[ORM\HasLifecycleCallbacks]
class Document {


    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private  $id;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Assert\NotBlank]
    private  $originalname;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Assert\NotBlank]
    private  $uniquename;


    #[ORM\Column(type: 'string', nullable: true)]
    private  $uploadDirectory;

    #[ORM\Column(type: 'decimal', nullable: true)]
    private  $size;

    /**
     * @var File  - not a persistent field!
     */
    #[Assert\File(maxSize: '6000000')]
    private  $file;

    /**
     * Used in event type text (i.e. "Fellowship Application Spreadsheet Deleted") and
     * to distinguish "Fellowship Photo" & "Avatar Image" for thumbnails generation
     */
    #[ORM\ManyToOne(targetEntity: 'DocumentTypeList')]
    #[ORM\JoinColumn(name: 'type_id', referencedColumnName: 'id', nullable: true)]
    private $type;

    #[ORM\ManyToOne(targetEntity: 'User')]
    #[ORM\JoinColumn(name: 'creator_id', referencedColumnName: 'id', nullable: true)]
    protected $creator;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'createdate', type: 'datetime', nullable: true)]
    private $createdate;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'externalcreatedate', type: 'datetime', nullable: true)]
    private $externalCreatedate;

    /**
     * Image title
     */
    #[ORM\Column(type: 'string', nullable: true)]
    private $title;

    /**
     * Unique ID of the file. For example, google drive file id
     */
    #[ORM\Column(type: 'string', nullable: true)]
    private $uniqueid;

    //    /**
    //     * @ORM\ManyToOne(targetEntity="GeneralEntity", cascade={"persist","remove"})
    //     * @ORM\JoinColumn(name="useObject_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
    //     */
    //    private $useObject;
    //Fields specifying a subject entity
    #[ORM\Column(type: 'string', nullable: true)]
    private $entityNamespace;

    #[ORM\Column(type: 'string', nullable: true)]
    private $entityName;

    #[ORM\Column(type: 'string', nullable: true)]
    private $entityId;

    /**
     * MD5 hash (checksum to identify duplicate document)
     */
    #[ORM\Column(type: 'string', nullable: true)]
    private $documentHash;

    //private $subdomain = 'order'; //accommodate 'order' subdomain in view.med.cornell.edu/order/, however it should be independent on the url
    private $subdomain = '';




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
    
    public function setFile($file)
    {
        $this->file = $file;
    }
    
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

        //set hash if file exists
        $this->getOrGenerateSetDocumentHash();
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
//        if( $uploadDirectory ) {
//
//        }
        
        $this->uploadDirectory = $uploadDirectory;

        //set hash if file exists
        $this->getOrGenerateSetDocumentHash();
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
     * @return \DateTime
     */
    public function getExternalCreatedate()
    {
        return $this->externalCreatedate;
    }
    /**
     * @param \DateTime $externalCreatedate
     */
    public function setExternalCreatedate($externalCreatedate)
    {
        $this->externalCreatedate = $externalCreatedate;
    }

    //get external createdate or if not exists DB createdate
    public function getExternalOrDbCreatedate() {
        $externalCreatedate = $this->getExternalCreatedate();
        if( $externalCreatedate ) {
            return $externalCreatedate;
        }

        return $this->getCreatedate();
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
    public function getDocumentHash()
    {
        return $this->documentHash;
    }

    /**
     * @param mixed $documentHash
     */
    public function setDocumentHash($documentHash)
    {
        $this->documentHash = $documentHash;
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
        if( $this->getId() ) {
            $id = $this->getId();
        } else {
            $id = "Unknown";
        }
        return "ID:$id: ".$this->getRelativeUploadFullPath();
    }

//    public function getTestPath() {
//        return $this->getAbsoluteUploadFullPath();
//    }

    //get server path to delete file: /var/www/test/folder/images/image_name.jpeg
    public function getServerPath($size=null)
    {
        $uniquename = $this->getUniquename();
        if( $size ) {
            $uniquename = $size . "-" . $uniquename;
        }

        //echo "getcwd=".getcwd()."<br>"; //getcwd()=C:\Program Files (x86)\pacsvendor\pacsname\htdocs\order\scanorder\Scanorders2
        //return $this->getUploadDirectory().'/'.$uniquename;
        return $this->getUploadDirectory().DIRECTORY_SEPARATOR.$uniquename;
    }

    //use for command console to get a full absolute server path
    //example: C:\Users\ch3\Documents\MyDocs\WCMC\ORDER\scanorder\Scanorders2/web/Uploaded/fellapp/documents/56fbf9e8867c3.jpg
    public function getFullServerPath($withRealPath=true)
    {
        if( !$this->getUploadDirectory() ) {
            return NULL;
        }
        if( !$this->getUniquename() ) {
            return NULL;
        }

        //echo "getcwd=".getcwd()."<br>"; //getcwd()=C:\Program Files (x86)\pacsvendor\pacsname\htdocs\order\scanorder\Scanorders2

        //From web getcwd()=C:\Program Files (x86)\pacsvendor\pacsname\htdocs\order\scanorder\Scanorders2\web
        //From console getcwd()=C:\Program Files (x86)\pacsvendor\pacsname\htdocs\order\scanorder\Scanorders2
        $fullPath = getcwd();

        if( strpos((string)$fullPath, 'public') !== false ) {
            //web exists
        } else {
            //web does not exist
            $fullPath = $fullPath.DIRECTORY_SEPARATOR."public";
        }

        $fullPath = $fullPath . DIRECTORY_SEPARATOR . $this->getUploadDirectory() . DIRECTORY_SEPARATOR . $this->getUniquename();

        //$fullPath = getcwd() . "/web/" . $this->getUploadDirectory().'/'.$this->getUniquename();
        //$fullPath = realpath($fullPath);

        if( $fullPath && $withRealPath ) {
            $fullPath = realpath($fullPath);
        }

        return $fullPath;
    }

    //NOT USED: limitation: can not get correct url prefix for multitenancy case 'http://view.online/c/wcm/pathology/' with HaProxy
    //Use $userServiceUtil->getDocumentAbsoluteUrl($document) instead ($userServiceUtil = $this->container->get('user_service_utility');)
    //Return: http://view.online/c/wcm/pathology/Uploaded/directory/avatars/avatar/20240708194741.jpeg
    //$urlPrefix is a prefix of the url, for example: 'http://view.online/c/wcm/pathology'
    public function getAbsoluteUploadFullPath($size=null,$onlyResize=false,$urlPrefix=null)
    {

        //dump($_SERVER);
        //exit('111');

        if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1)) {
            $scheme = 'https';
        } else {
            $scheme = 'http';
        }
        //$scheme = 'http';
        //exit("scheme=".$scheme);

//        $scheme = "http";
//        if( isset($_SERVER['SERVER_PROTOCOL']) && stripos($_SERVER['SERVER_PROTOCOL'],'https') === true ) {
//            $scheme = "https";
//        }

        if (isset($_SERVER['SERVER_NAME'])) {
            $serverName = $_SERVER['SERVER_NAME'];
        } else {
            $serverName = "127.0.0.1";
        }

        //This was added for symfony server when port is not 80
        if( isset($_SERVER['SERVER_PORT']) ) {
            $serverPort = $_SERVER['SERVER_PORT'];
            //echo "serverPort=$serverPort <br>";
            if( $serverPort && $serverPort != '80' ) {
                $serverName = $serverName . ":" . $serverPort;
            }
        }

        //TODO:
        //For multitenancy $serverName should include a tenant url 'c/wcm/pathology':
        //This http://view.online/Uploaded/directory/avatars/avatar/20240708194741.jpeg
        //should be this: http://view.online/c/wcm/pathology/Uploaded/directory/avatars/avatar/20240708194741.jpeg
        //$this->generateUrl('employees_home')
        //$this->get('router')->generate('employees_home')

        $uniquename = $this->getUniquename();
        if( !$uniquename ) {
            //File is erroneous if uniquename is null
            //echo "getAbsoluteUploadFullPath: File is erroneous if uniquename is null <br>";
            return null;
        }

        if ($size) {
            $uniquename = $size . "-" . $uniquename;
        }

        if( $this->subdomain ) {
            $subdomain = $this->subdomain . DIRECTORY_SEPARATOR;
        } else {
            $subdomain = '';
        }

        if( $urlPrefix ) {
            $urlPrefix = rtrim($urlPrefix,'/'); //trim '/' at the end
            $path = $urlPrefix . DIRECTORY_SEPARATOR . $this->getUploadDirectory() . DIRECTORY_SEPARATOR . $uniquename;
        } else {
            $path = $scheme . "://" . $serverName . DIRECTORY_SEPARATOR . $subdomain . $this->getUploadDirectory() . DIRECTORY_SEPARATOR . $uniquename;
        }

        //echo "getAbsoluteUploadFullPath: path=$path <br>";

        if ($onlyResize == false) {
            if ($size) {
                $src = $this->getServerPath($size);
                if (file_exists($src)) {
                    //echo "The file $path exists <br>";
                } else {
                    //echo "The file $path does not exists <br>";
                    //exit("The file $path does not exists");
                    $path = $this->getAbsoluteUploadFullPath();
                }
            } else {
                //echo "Size is null <br>";
            }
        }

        //exit("path=".$path);

        return $path;
        //return $scheme."://" . $serverName . "/order/" . $this->getUploadDirectory().'/'.$this->getUniquename();

        //return $this->getUploadDirectory().'/'.$this->getUniquename();
        //return "http://collage.med.cornell.edu/".$this->getRelativeUploadFullPath();
    }

    public function getSizeBySize($size=null)
    {
        $path = $this->getServerPath($size);
        if( file_exists($path) ) {
           //ok
        } else {
            return null;
        }
        $fileSize = filesize($path);
        return $fileSize;
    }

    public function pathExist() {
        $path = $this->getServerPath();
        if( $path ) {
            if( file_exists($path) ) {
                return true;
            }
        }
        return false;
    }

    public function getAttachmentElementArr() {
        $attachmentElementArr = null;
        $path = $this->getServerPath();
        if( $path && file_exists($path) ) {
            $name = $this->getDescriptiveFilename();
            $attachmentElementArr = array('path'=>$path,'name'=>$name);
        }
        return $attachmentElementArr;
    }
    public function getAttachmentArr() {
        $attachmentElementArr = $this->getAttachmentElementArr();
        if( $attachmentElementArr ) {
            return array($attachmentElementArr);
        }
        return null;
    }

    //TODO: swiftmailer\swiftmailer\lib\classes\Swift\ByteStream\FileByteStream.php Error:
    // Unable to open file for reading => use 'realpath' in email util
    //Accepted path: E:\Program Files (x86)\Aperio\Spectrum\htdocs\order\scanorder\Scanorders2\web\Uploaded\fellapp\documents\filename.pdf
    public function getAttachmentEmailPath()
    {
        //use DIRECTORY_SEPARATOR
        //Accepted path: E:\Program Files (x86)\Aperio\Spectrum\htdocs\order\scanorder\Scanorders2\web\Uploaded\fellapp\documents
        return $this->getFullServerPath();
    }

//    public function getCommandAbsoluteUploadFullPath()
//    {
//        //return $this->container->get('kernel')->getRootDir() . '/../web/' . $this->getUploadDirectory().'/'.$this->getUniquename();
//        return realpath($this->container->get('kernel')->getRootDir() . "/../web/" . $this->getServerPath());
//    }

    public function getRelativeUploadFullPath()
    {
        return $this->getPrefixPath().$this->getUploadDirectory().DIRECTORY_SEPARATOR.$this->getUniquename();
    }

    protected function getPrefixPath() {
        //return '../../../../order/';

        if( $this->subdomain ) {
            $subdomain = $this->subdomain . DIRECTORY_SEPARATOR;
        } else {
            $subdomain = '';
        }

        return '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . $subdomain;
    }

    public function getFileSystemPath($size=null) {
        //echo "getcwd=".getcwd()."<br>";
        return getcwd() . "\\" . $this->getServerPath($size);
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
        return $this->cleanFileName($this->getOriginalname());
    }

    public function setCleanOriginalname( $filename ) {
        $this->setOriginalname( $this->cleanFileName( $filename ) );
    }

    public function cleanFileName( $filename ) {
        if( !$filename ) {
            return $filename;
        }

        $ext = pathinfo($filename, PATHINFO_EXTENSION);

        if ($ext) {
            $filename = str_replace("." . $ext, "", $filename);

            //sanitize http://stackoverflow.com/questions/2021624/string-sanitizer-for-filename
            // Remove anything which isn't a word, whitespace, number
            // or any of the following caracters -_~,;[]().
            // If you don't need to handle multi-byte characters
            // you can use preg_replace rather than mb_ereg_replace
            // Thanks @Lukasz Rysiak!
            $filename = mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '_', $filename);
            // Remove any runs of periods (thanks falstro!)
            $filename = mb_ereg_replace("([\.]{2,})", '_', $filename);

            $filename = str_replace(".", "_", $filename);
            $filename = str_replace("(", "_", $filename);
            $filename = str_replace(")", "_", $filename);
            $filename = str_replace(" ", "_", $filename);

            $filename = $filename . "." . $ext;
        }

        //echo "filename=".$filename."<br>";
        //exit('1');

        return $filename;

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

    public function getExtension()
    {
        $ext = NULL;

        $originalFilename = $this->getOriginalname();
        if( $originalFilename ) {
            $ext = pathinfo($originalFilename, PATHINFO_EXTENSION);
        }
        
        return $ext;
    }

    public function getDescriptiveFilename()
    {
        if( $this->getTitle() ) {
            return $this->getTitle();
        }
        if( $this->getOriginalname() ) {
            return $this->getOriginalname();
        }
        if( $this->getUniqueid() ) {
            return $this->getUniqueid();
        }
        if( $this->getUniquename() ) {
            return $this->getUniquename();
        }
        return $this->getFullDescriptionStr();
    }

    public function getOrGenerateSetDocumentHash() {
        if( $this->getDocumentHash() ) {
            return $this->getDocumentHash();
        }
        $hash = $this->generateDocumentHash();
        if( $hash ) {
            $this->setDocumentHash($hash);
            return $hash;
        }
        return NULL;
    }
    public function generateDocumentHash() {
        //set hash if file exists
        $filename = $this->getFullServerPath();
        if( file_exists($filename) ) {
            $hash = md5_file($filename);
            if( $hash ) {
                return $hash;
            }
        }
//        else {
//            exit("Testing: file does not exists");
//        }

        return NULL;
    }
    
}