<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class ProcedureLocationType extends AbstractType
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

        //Location Source System - array field location source
        $builder->add('source', 'entity', array(
            'class' => 'OlegUserdirectoryBundle:SourceSystemList',
            'label' => 'Location Source System:',
            'required' => false,
            'data' => null,
            'attr' => array('class' => 'combobox combobox-width'),
            'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('list')
                        ->orderBy("list.orderinlist","ASC");
                },
        ));

        //Location Timestamp - property of location object
        //Location Provider - property of location object
        //Location Role - property of location object (use as a filter)

        $builder->add('field', 'entity', array(
            'class' => 'OlegUserdirectoryBundle:Location',
            'label' => 'Procedure Location',
            'required' => false,
            'attr' => array('class' => 'combobox combobox-width'),
            'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('list')
                        ->leftJoin("list.locationType", "locationType")
                        ->where("locationType.name = 'Inpatient Room'")
                        ->orderBy("list.orderinlist","ASC");
                },
        ));


        $builder->add('others', new ArrayFieldType(), array(
            'data_class' => 'Oleg\OrderformBundle\Entity\ProcedureLocation',
            'label' => false,
			'attr' => array('style'=>'display:none;')
        ));


    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\ProcedureLocation',
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_procedurelocationtype';
    }
}
