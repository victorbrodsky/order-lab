<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class PatientRaceType extends AbstractType
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

        $builder->add( 'field', null, array(
            'label' => 'Race',
            'required' => false,
            'attr' => array('class' => 'combobox combobox-width')
        ));

//        $builder->add('race', 'entity', array(
//            'class' => 'OlegOrderformBundle:RaceList',
//            'label' => 'Race',
//            'required' => false,
//            'attr' => array('class' => 'combobox combobox-width'),
//            'query_builder' => function(EntityRepository $er) {
//                    return $er->createQueryBuilder('list')
//                        ->orderBy("list.orderinlist","ASC");
//                },
//        ));

        //other fields from abstract
        $builder->add('others', new ArrayFieldType(), array(
            'data_class' => 'Oleg\OrderformBundle\Entity\PatientRace',
            'label' => false,
			'attr' => array('style'=>'display:none;')
        ));

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\PatientRace',
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_patientracetype';
    }
}
