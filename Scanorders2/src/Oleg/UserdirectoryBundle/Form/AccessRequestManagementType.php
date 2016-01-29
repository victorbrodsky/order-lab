<?php

namespace Oleg\UserdirectoryBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;


class AccessRequestManagementType extends AbstractType
{

    protected $params;
    protected $entity;

    //private $commentData = null;
    //private $effortData = null;

    public function __construct( $params=null, $entity=null )
    {
        $this->params = $params;
        $this->entity = $entity;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('user', new AccessRequestUserType($this->params), array(
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\User',
            'label' => false
        ));


//        $builder->add('role', 'date', array(
//            'label' => "Grant Support Start Date:",
//            'widget' => 'single_text',
//            'required' => false,
//            'format' => 'MM/dd/yyyy',    //'MM/dd/yyyy',
//            'attr' => array('class' => 'datepicker form-control grant-startDate-field'),
//        ));




    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\User',
        ));
    }

    public function getName()
    {
        return 'oleg_userdirectorybundle_accreqmanagementtype';
    }
}
