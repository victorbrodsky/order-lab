<?php

namespace Oleg\CallLogBundle\Form;

use Oleg\OrderformBundle\Form\ArrayFieldType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class PatientMrnType extends AbstractType
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

        $builder->add('keytype', null, array(
            'label'=>'MRN Type:',
            'required'=>false,
            'attr' => array('class' => 'combobox')
        ));

        $builder->add('field', null, array(
            'label'=>'MRN:',
            'required'=>false,
            'attr' => array('class' => 'form-control keyfield patientmrn-mask')
        ));


//        //other fields from abstract
//        $builder->add('others', new ArrayFieldType(), array(
//            'data_class' => 'Oleg\OrderformBundle\Entity\PatientMrn',
//            'label' => false,
//			'attr' => array('style'=>'display:none;')
//        ));

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\PatientMrn',
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_mrntype';
    }
}
