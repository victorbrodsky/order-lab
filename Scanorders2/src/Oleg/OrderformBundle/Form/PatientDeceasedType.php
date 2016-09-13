<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class PatientDeceasedType extends AbstractType
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

        $builder->add('deceased', 'checkbox', array(
            'label'     => 'Deceased:',
            'required'  => false,
        ));

        $builder->add('deathdate','datetime',array(
            'widget' => 'single_text',
            'format' => 'MM/dd/yyyy',
            'attr' => array('class' => 'datepicker form-control', 'style'=>'margin-top: 0;'),
            'required' => false,
            'label'=>'Date of Death:',
        ));
//        $builder->add('deathdate', 'date', array(
//            'input'  => 'datetime',
//            'widget' => 'single_text',
//            'label'=>'Date of Death:',
//            'attr' => array('class' => 'datepicker form-control', 'style'=>'margin-top: 0;'),
//        ));

        $builder->add('deathtime', 'time', array(
            'input'  => 'datetime',
            'widget' => 'choice',
            'label'=>'Time of Death:'
        ));

        //other fields from abstract
        $builder->add('others', new ArrayFieldType($this->params), array(
            'data_class' => 'Oleg\OrderformBundle\Entity\PatientDeceased',
            'label' => false,
			'attr' => array('style'=>'display:none;')
        ));

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\PatientDeceased',
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_patientdeceasedtype';
    }
}
