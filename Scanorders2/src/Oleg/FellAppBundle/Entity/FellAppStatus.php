<?php

namespace Oleg\FellAppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Oleg\UserdirectoryBundle\Entity\ListAbstract;

/**
 * @ORM\Entity
 * @ORM\Table(name="fellapp_fellAppStatus")
 */
class FellAppStatus extends ListAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="FellAppStatus", mappedBy="original", cascade={"persist"})
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="FellAppStatus", inversedBy="synonyms", cascade={"persist"})
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id", nullable=true)
     **/
    protected $original;


    /**
     * action: show this 'action' column in the Action menu and the 'name' column in the Filter menu. (i.e. Rejected)
     * @ORM\Column(type="string", nullable=true)
     */
    protected $action;




    public function __toString() {
        return $this->name;
    }




    /**
     * Set action
     *
     * @param string $action
     * @return FellAppStatus
     */
    public function setAction($action)
    {
        $this->action = $action;
    
        return $this;
    }

    /**
     * Get action
     *
     * @return string 
     */
    public function getAction()
    {
        return $this->action;
    }


}