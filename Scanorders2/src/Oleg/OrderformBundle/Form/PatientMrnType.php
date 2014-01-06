<?php

namespace Oleg\OrderformBundle\Form;

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

        $builder->add( 'field', 'text', array(
            'label'=>'MRN',
            'required'=>false,
            'attr' => array(
                'class' => 'form-control keyfield patientmrn-mask',
                //'data-inputmask'=>"'mask':'d999999[9]'",
            )
        ));

        $attr = array('class' => 'combobox combobox-width mrntype-combobox');
        $builder->add('mrntype', 'entity', array(
            'class' => 'OlegOrderformBundle:MrnType',
            'label'=>false, //'MRN Type',
            'required' => true,
            'attr' => $attr,
            'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('s')
                    ->orderBy('s.id', 'ASC');
            },
        ));

        $builder->add('mrnothers', new ArrayFieldType(), array(
            'data_class' => 'Oleg\OrderformBundle\Entity\PatientMrn',
            'label' => false
        ));

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
