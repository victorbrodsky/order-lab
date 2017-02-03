<?php

namespace Oleg\CallLogBundle\Form;

use Doctrine\ORM\EntityRepository;
use Oleg\UserdirectoryBundle\Entity\User;
use Oleg\UserdirectoryBundle\Form\LoggerFilterType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CalllogLoggerFilterType extends LoggerFilterType
{

    public function addOptionalFields( $builder ) {

        //Capacity
        if( $this->params['sitename'] == "calllog" ) {
            $capacities = array(
                "Submitter" => "Submitter",
                "Attending" => "Attending"
            );
            $builder->add('capacity', 'choice', array(
                'label' => false,
                'required'=> false,
                'choices' => $capacities,
                'attr' => array('class' => 'combobox', 'placeholder' => 'Capacity'),
            ));
        }

    }

}
