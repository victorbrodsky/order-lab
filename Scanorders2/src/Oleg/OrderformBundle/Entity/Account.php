<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Oleg\UserdirectoryBundle\Entity\ListAbstract;

/**
 * @ORM\Entity
 * @ORM\Table(name="scan_account")
 */
class Account extends ListAbstract
{
    /**
     * @ORM\OneToMany(targetEntity="Account", mappedBy="original")
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="Account", inversedBy="synonyms")
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id")
     **/
    protected $original;

    /**
     * @ORM\OneToMany(targetEntity="Message", mappedBy="account")
     */
    protected $message;


    public function __construct() {
        $this->synonyms = new ArrayCollection();
        $this->message = new ArrayCollection();
    }


    /**
     * Add message
     *
     * @param \Oleg\OrderformBundle\Entity\Message $message
     * @return Account
     */
    public function addMessage(\Oleg\OrderformBundle\Entity\Message $message)
    {
        if( !$this->message->contains($message) ) {
            $this->message->add($message);
        }
    }

    /**
     * Remove message
     *
     * @param \Oleg\OrderformBundle\Entity\Message $message
     */
    public function removeMessage(\Oleg\OrderformBundle\Entity\Message $message)
    {
        $this->message->removeElement($message);
    }

    /**
     * Get message
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getMessage()
    {
        return $this->message;
    }
}