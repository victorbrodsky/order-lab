<?php

namespace Oleg\UserdirectoryBundle\Entity;


use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity
 * @ORM\Table(name="user_link")
 */
class Link extends ListAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="Link", mappedBy="original", cascade={"persist"})
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="Link", inversedBy="synonyms", cascade={"persist"})
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id", nullable=true)
     **/
    protected $original;



    /**
     * @ORM\ManyToOne(targetEntity="LinkTypeList")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true)
     */
    private $linktype;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $link;







    /**
     * @param mixed $link
     */
    public function setLink($link)
    {
        $this->link = $link;
    }

    /**
     * @return mixed
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @param mixed $linktype
     */
    public function setLinktype($linktype)
    {
        $this->linktype = $linktype;
    }

    /**
     * @return mixed
     */
    public function getLinktype()
    {
        return $this->linktype;
    }




}