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

//        $builder->add('source', 'entity', array(
//            'class' => 'OlegUserdirectoryBundle:SourceSystemList',
//            'label' => 'Lab Order Source:',
//            'required' => false,
//            'attr' => array('class' => 'combobox combobox-width'),
//            'query_builder' => function(EntityRepository $er) {
//                    return $er->createQueryBuilder('list')
//                        ->where("list.name = 'WCMC Epic Ambulatory EMR' OR list.name = 'NYH Paper Requisition'")
//                        ->orderBy("list.orderinlist","ASC");
//                },
//        ));

//        //OrderInfo: source, type, id
//        $sources = array('WCMC Epic Ambulatory EMR','NYH Paper Requisition');
//        $params = array('name'=>'Lab','dataClass'=>'Oleg\OrderformBundle\Entity\GeneralOrder','typename'=>'laborder','sources'=>$sources);
//        $builder->add('orderinfo', new GeneralOrderType($params, null), array(
//            'data_class' => 'Oleg\OrderformBundle\Entity\GeneralOrder',
//            'label' => false,
//        ));

//        //Lab Order source location
//        $builder->add('sourceLocation', 'entity', array(
//            'class' => 'OlegUserdirectoryBundle:Location',
//            'label' => 'Lab Order Source Location:',
//            'required' => false,
//            'multiple' => false,
//            'attr' => array('class' => 'combobox combobox-width'),
//            'query_builder' => function(EntityRepository $er) {
//                    return $er->createQueryBuilder('list')
//                        //->leftJoin("list.locationTypes", "locationTypes")
//                        //->where("locationTypes.name = 'Medical Office'")
//                        ->orderBy("list.orderinlist","ASC");
//                },
//        ));

//        //Lab Order track locations
//        $builder->add('trackLocations', 'entity', array(
//            'class' => 'OlegUserdirectoryBundle:Location',
//            'label' => 'Lab Order Tracking Location(s):',
//            'required' => false,
//            'multiple' => true,
//            'attr' => array('class' => 'combobox combobox-width'),
//            'query_builder' => function(EntityRepository $er) {
//                    return $er->createQueryBuilder('list')
//                        //->leftJoin("list.locationTypes", "locationTypes")
//                        //->where("locationTypes.name = 'Medical Office'")
//                        ->orderBy("list.orderinlist","ASC");
//                },
//        ));

        //Requisition Form Image container
        $params = array('labelPrefix'=>'Requisition Form Image');
        $builder->add('documentContainer', new DocumentContainerType($params), array(
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\DocumentContainer',
            'label' => false
        ));




//        $builder->add('others', new ArrayFieldType(), array(
//            'data_class' => 'Oleg\OrderformBundle\Entity\LabOrder',
//            'label' => false,
//			'attr' => array('style'=>'display:none;')
//        ));


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
