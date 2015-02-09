<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class EncounterNumberType extends AbstractType
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

//        //$builder->add('keytype', 'hidden', array('label'=>false));
//        $builder->add('keytype', 'entity', array(
//            'class' => 'OlegOrderformBundle:EncounterType',
//            'label'=>false,
//            'required' => true,
//            'data' => 1,
//            'attr' => array('style'=>'display:none;'),
//            'query_builder' => function(EntityRepository $er) {
//                    return $er->createQueryBuilder('list')
//                        ->orderBy("list.orderinlist","ASC")
//                        ->setMaxResults(1);
//
//                },
//        ));

//        $builder->add('source', 'entity', array(
//            'class' => 'OlegUserdirectoryBundle:SourceSystemList',
//            'label' => 'Encounter Number Source:',
//            'required' => false,
//            'attr' => array('class' => 'combobox combobox-width'),
//            'query_builder' => function(EntityRepository $er) {
//                    return $er->createQueryBuilder('list')
//                        ->where("list.name = 'WCMC Epic Practice Management'")
//                        ->orderBy("list.orderinlist","ASC");
//
//                },
//        ));

        $builder->add('field', null, array(
            'label'=>'Encounter Number',
            'attr' => array('class'=>'form-control')
        ));


        $builder->add('others', new ArrayFieldType(), array(
            'data_class' => 'Oleg\OrderformBundle\Entity\EncounterNumber',
            'label' => false,
            'attr' => array('style'=>'display:none;')
        ));

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\EncounterNumber',
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_encounternumbertype';
    }
}
