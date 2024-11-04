<?php
/**
 * Created by PhpStorm.
 * User: cinav
 * Date: 10/15/2024
 * Time: 11:22 AM
 */

namespace App\Saml\Entity;

use App\UserdirectoryBundle\Entity\ListAbstract;
use Doctrine\ORM\Mapping as ORM;


///**
// * @ORM\Entity(repositoryClass="App\UserdirectoryBundle\Util\SamlConfigRepository")
// */
#[ORM\Table(name: 'saml_config')]
#[ORM\Entity(repositoryClass: 'App\Saml\Repository\SamlConfigRepository')]
class SamlConfig extends ListAbstract
{
//    /**
//     * @ORM\Id
//     * @ORM\GeneratedValue
//     * @ORM\Column(type="integer")
//     */
//    private $id;
//    #[ORM\Id]
//    #[ORM\Column(type: 'integer')]
//    #[ORM\GeneratedValue(strategy: 'AUTO')]
//    private $id;

    #[ORM\OneToMany(targetEntity: 'SamlConfig', mappedBy: 'original', cascade: ['persist'])]
    protected $synonyms;

    #[ORM\ManyToOne(targetEntity: 'SamlConfig', inversedBy: 'synonyms', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'original_id', referencedColumnName: 'id', nullable: true)]
    protected $original;

//    /**
//     * @ORM\Column(type="string", length=255)
//     */
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $client;

//    /**
//     * @ORM\Column(type="text")
//     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $idpEntityId;

//    /**
//     * @ORM\Column(type="text")
//     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $idpSsoUrl;

//    /**
//     * @ORM\Column(type="text")
//     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $idpSloUrl;

//    /**
//     * @ORM\Column(type="text")
//     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $idpCert;

//    /**
//     * @ORM\Column(type="text")
//     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $spEntityId;

//    /**
//     * @ORM\Column(type="text")
//     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $spAcsUrl;

//    /**
//     * @ORM\Column(type="text")
//     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $spSloUrl;

//    /**
//     * @ORM\Column(type="text")
//     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $spPrivateKey;

//    /**
//     * @ORM\Column(type="string", length=255)
//     */
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $identifierAttribute;

//    /**
//     * @ORM\Column(type="boolean")
//     */
    #[ORM\Column(type: 'boolean', nullable: true)]
    private $autoCreate;

//    /**
//     * @ORM\Column(type="json")
//     */
    #[ORM\Column(type: 'array', nullable: true)]
    private $attributeMapping;



    
    public function __construct($author=null) {
        parent::__construct($author);
    }
    
    
    
    // Getters and setters
//    public function getId(): ?int
//    {
//        return $this->id;
//    }

    public function getClient(): ?string
    {
        return $this->client;
    }

    public function setClient( $client ): self
    {
        $this->client = $client;

        return $this;
    }

    public function getIdpEntityId(): ?string
    {
        return $this->idpEntityId;
    }

    public function setIdpEntityId( $idpEntityId )
    {
        $this->idpEntityId = $idpEntityId;
        return $this;
    }

    public function getIdpSsoUrl(): ?string
    {
        return $this->idpSsoUrl;
    }

    public function setIdpSsoUrl( $idpSsoUrl ): self
    {
        $this->idpSsoUrl = $idpSsoUrl;

        return $this;
    }

    public function getIdpSloUrl(): ?string
    {
        return $this->idpSloUrl;
    }

    public function setIdpSloUrl( $idpSloUrl ): self
    {
        $this->idpSloUrl = $idpSloUrl;

        return $this;
    }

    public function getIdpCert(): ?string
    {
        return $this->idpCert;
    }

    public function setIdpCert( $idpCert ): self
    {
        $this->idpCert = $idpCert;

        return $this;
    }

    public function getSpEntityId(): ?string
    {
        return $this->spEntityId;
    }

    public function setSpEntityId( $spEntityId ): self
    {
        $this->spEntityId = $spEntityId;

        return $this;
    }

    public function getSpAcsUrl(): ?string
    {
        return $this->spAcsUrl;
    }

    public function setSpAcsUrl( $spAcsUrl ): self
    {
        $this->spAcsUrl = $spAcsUrl;

        return $this;
    }

    public function getSpSloUrl(): ?string
    {
        return $this->spSloUrl;
    }

    public function setSpSloUrl( $spSloUrl ): self
    {
        $this->spSloUrl = $spSloUrl;

        return $this;
    }

    public function getSpPrivateKey(): ?string
    {
        return $this->spPrivateKey;
    }

    public function setSpPrivateKey( $spPrivateKey ): self
    {
        $this->spPrivateKey = $spPrivateKey;

        return $this;
    }

    public function getIdentifierAttribute(): ?string
    {
        return $this->identifierAttribute;
    }

    public function setIdentifierAttribute( $identifierAttribute ): self
    {
        $this->identifierAttribute = $identifierAttribute;

        return $this;
    }

    public function getAutoCreate(): ?bool
    {
        return $this->autoCreate;
    }

    public function setAutoCreate(bool $autoCreate): self
    {
        $this->autoCreate = $autoCreate;

        return $this;
    }

    public function getAttributeMapping(): ?array
    {
        return $this->attributeMapping;
    }

    public function setAttributeMapping( $attributeMapping ): self
    {
        $this->attributeMapping = $attributeMapping;

        return $this;
    }

}