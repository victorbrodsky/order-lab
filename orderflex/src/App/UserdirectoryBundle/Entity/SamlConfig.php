<?php
/**
 * Created by PhpStorm.
 * User: cinav
 * Date: 10/15/2024
 * Time: 11:22 AM
 */

namespace App\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;


///**
// * @ORM\Entity(repositoryClass="App\UserdirectoryBundle\Util\SamlConfigRepository")
// */
//#[ORM\Table(name: 'user_saml_config')]
//#[ORM\Entity(repositoryClass: 'App\UserdirectoryBundle\Repository\SamlConfigRepository')]
class SamlConfig
{
//    /**
//     * @ORM\Id
//     * @ORM\GeneratedValue
//     * @ORM\Column(type="integer")
//     */
//    private $id;
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

//    /**
//     * @ORM\Column(type="string", length=255)
//     */
    #[ORM\Column(type: 'string', length: 255)]
    private $client;

//    /**
//     * @ORM\Column(type="text")
//     */
    #[ORM\Column(type: 'text')]
    private $idpEntityId;

//    /**
//     * @ORM\Column(type="text")
//     */
    #[ORM\Column(type: 'text')]
    private $idpSsoUrl;

//    /**
//     * @ORM\Column(type="text")
//     */
    #[ORM\Column(type: 'text')]
    private $idpSloUrl;

//    /**
//     * @ORM\Column(type="text")
//     */
    #[ORM\Column(type: 'text')]
    private $idpCert;

//    /**
//     * @ORM\Column(type="text")
//     */
    #[ORM\Column(type: 'text')]
    private $spEntityId;

//    /**
//     * @ORM\Column(type="text")
//     */
    #[ORM\Column(type: 'text')]
    private $spAcsUrl;

//    /**
//     * @ORM\Column(type="text")
//     */
    #[ORM\Column(type: 'text')]
    private $spSloUrl;

//    /**
//     * @ORM\Column(type="text")
//     */
    #[ORM\Column(type: 'text')]
    private $spPrivateKey;

//    /**
//     * @ORM\Column(type="string", length=255)
//     */
    #[ORM\Column(type: 'string', length: 255)]
    private $identifierAttribute;

//    /**
//     * @ORM\Column(type="boolean")
//     */
    #[ORM\Column(type: 'boolean')]
    private $autoCreate;

//    /**
//     * @ORM\Column(type="json")
//     */
    #[ORM\Column(type: 'json')]
    private $attributeMapping;


    // Getters and setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClient(): ?string
    {
        return $this->client;
    }

    public function setClient(string $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function getIdpEntityId(): ?string
    {
        return $this->idpEntityId;
    }

    public function setIdpEntityId(string $idpEntityId): self
    {
        $this->idpEntityId = $idpEntityId;

        return $this;
    }

    public function getIdpSsoUrl(): ?string
    {
        return $this->idpSsoUrl;
    }

    public function setIdpSsoUrl(string $idpSsoUrl): self
    {
        $this->idpSsoUrl = $idpSsoUrl;

        return $this;
    }

    public function getIdpSloUrl(): ?string
    {
        return $this->idpSloUrl;
    }

    public function setIdpSloUrl(string $idpSloUrl): self
    {
        $this->idpSloUrl = $idpSloUrl;

        return $this;
    }

    public function getIdpCert(): ?string
    {
        return $this->idpCert;
    }

    public function setIdpCert(string $idpCert): self
    {
        $this->idpCert = $idpCert;

        return $this;
    }

    public function getSpEntityId(): ?string
    {
        return $this->spEntityId;
    }

    public function setSpEntityId(string $spEntityId): self
    {
        $this->spEntityId = $spEntityId;

        return $this;
    }

    public function getSpAcsUrl(): ?string
    {
        return $this->spAcsUrl;
    }

    public function setSpAcsUrl(string $spAcsUrl): self
    {
        $this->spAcsUrl = $spAcsUrl;

        return $this;
    }

    public function getSpSloUrl(): ?string
    {
        return $this->spSloUrl;
    }

    public function setSpSloUrl(string $spSloUrl): self
    {
        $this->spSloUrl = $spSloUrl;

        return $this;
    }

    public function getSpPrivateKey(): ?string
    {
        return $this->spPrivateKey;
    }

    public function setSpPrivateKey(string $spPrivateKey): self
    {
        $this->spPrivateKey = $spPrivateKey;

        return $this;
    }

    public function getIdentifierAttribute(): ?string
    {
        return $this->identifierAttribute;
    }

    public function setIdentifierAttribute(string $identifierAttribute): self
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

    public function setAttributeMapping(array $attributeMapping): self
    {
        $this->attributeMapping = $attributeMapping;

        return $this;
    }

}