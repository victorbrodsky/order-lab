<?php

namespace Oleg\UserdirectoryBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

use Oleg\UserdirectoryBundle\Entity\Training;

class FellowshipApplicationType extends AbstractType
{

    protected $params;
    protected $entity;

    public function __construct( $params=null, $entity = null )
    {
        $this->params = $params;
        $this->entity = $entity;

        //print_r($this->params);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('fellowshipSubspecialty',null, array(
            'label' => 'Fellowship Type:',
            'required' => false,
            'attr' => array('class' => 'combobox combobox-width'),
        ));

        $builder->add('timestamp','date',array(
            'widget' => 'single_text',
            'label' => "Creation Date:",
            'format' => 'MM/dd/yyyy, H:mm:ss',
            'attr' => array('class' => 'datepicker form-control'),
            'required' => false,
        ));

        $builder->add('startDate','date',array(
            'widget' => 'single_text',
            'label' => "Start Date:",
            'format' => 'MM/dd/yyyy',  //'MM/dd/yyyy, H:mm:ss',
            'attr' => array('class' => 'datepicker form-control'),
            'required' => false,
        ));

        $builder->add('endDate','date',array(
            'widget' => 'single_text',
            'label' => "End Date:",
            'format' => 'MM/dd/yyyy',
            'attr' => array('class' => 'datepicker form-control'),
            'required' => false,
        ));

        $builder->add('user', new UserType($this->params), array(
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\User',
            'label' => false,
            'required' => false,
        ));

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\FellowshipApplication',
        ));
    }

    public function getName()
    {
        return 'oleg_userdirectorybundle_fellowshipapplication';
    }
}
