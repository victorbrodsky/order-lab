<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;



class PatientClinicalHistoryType extends AbstractType
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

        $builder->add('field', null, array(
            'label' => "Clinical History",
            'required' => false,
            'attr' => array('class'=>'form-control'),
        ));

        $builder->add('gen', new ArrayFieldType(), array(
            'data_class' => 'Oleg\OrderformBundle\Entity\PatientClinicalHistory'
        ));

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\PatientClinicalHistory',
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_clinicalhistorytype';
    }
}
