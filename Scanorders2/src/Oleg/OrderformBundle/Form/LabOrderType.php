<?php

namespace Oleg\OrderformBundle\Form;

use Oleg\UserdirectoryBundle\Form\DocumentContainerType;
use Oleg\UserdirectoryBundle\Form\DocumentType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class LabOrderType extends AbstractType
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

//        $builder->add( 'labTestType', 'custom_selector', array(
//            'label' => 'Laboratory Test ID Type:',
//            'required' => false,
//            'attr' => array('class' => 'combobox combobox-width combobox-educational-courseTitle', 'type' => 'hidden'),
//            'classtype' => 'projectTitle'
//        ));
        $builder->add('labTestType', 'employees_custom_selector', array(
            'label' => "Laboratory Test Type:",
            'required' => false,
            'attr' => array('class' => 'combobox combobox-width ajax-combobox-labtesttype', 'type' => 'hidden'),
            'classtype' => 'labtesttype'
        ));

        $builder->add('labTestId', 'text', array(
            'required'=>false,
            'label'=>'Laboratory Test ID:',
            'attr' => array('class'=>'form-control'),
        ));

        $builder->add('labTestTitle', 'text', array(
            'required'=>false,
            'label'=>'Laboratory Test Title:',
            'attr' => array('class'=>'form-control'),
        ));


    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\LabOrder',
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_labordertype';
    }
}
