<?php

namespace Oleg\UserdirectoryBundle\Form;



use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;


class ResearchLabType extends AbstractType
{

    protected $params;
    protected $entity;

    public function __construct( $params=null, $entity = null )
    {
        $this->params = $params;
        $this->entity = $entity;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('foundedDate',null,array(
            'label'=>"Founded on:",
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM-dd-yyyy',
            'attr' => array('class' => 'datepicker form-control')
        ));

        $builder->add('dissolvedDate',null,array(
            'label'=>"Dissolved on:",
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM-dd-yyyy',
            'attr' => array('class' => 'datepicker form-control')
        ));

        $builder->add('comment', null, array(
            'label' => 'Comment:',
            'attr' => array('class'=>'textarea form-control')
        ));

        $builder->add('location', null, array(
            'label' => "Location's Name:",
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('researchPI', 'checkbox', array(
            'required' => false,
            'label' => 'Principal Investigator of this Lab:',
            'attr' => array('class'=>'form-control', 'style'=>'margin:0')
        ));

        $builder->add('researchLabTitle', 'employees_custom_selector', array(
            'label' => "Research Lab Title:",
            'required' => false,
            'attr' => array('class' => 'combobox combobox-width ajax-combobox-researchlabtitle', 'type' => 'hidden'),
            'classtype' => 'researchLabTitle'
        ));

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\ResearchLab',
        ));
    }

    public function getName()
    {
        return 'oleg_userdirectorybundle_researchlab';
    }
}
