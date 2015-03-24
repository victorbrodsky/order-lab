<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class PatientTypeType extends AbstractType
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


        $builder->add('field', 'entity', array(
            'class' => 'OlegOrderformBundle:PatientTypeList',
            'label' => 'Patient Type',
            'required' => false,
            'attr' => array('class' => 'combobox combobox-width'),
            'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('list')
                        //->leftJoin("list.locationTypes", "locationTypes")
                        //->where("locationTypes.name = 'Patient Contact Information'")
                        ->orderBy("list.orderinlist","ASC");
                },
        ));

//        $builder->add( 'sources', null, array(
//            'label' => 'Patient Type Source(s)',
//            'required' => false,
//            'attr' => array('class' => 'combobox combobox-width')
//        ));

        //other fields from abstract
        $builder->add('others', new ArrayFieldType(), array(
            'data_class' => 'Oleg\OrderformBundle\Entity\PatientType',
            'label' => false,
			'attr' => array('style'=>'display:none;')
        ));

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\PatientType',
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_patienttypetype';
    }
}
