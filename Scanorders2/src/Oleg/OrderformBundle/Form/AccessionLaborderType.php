<?php

namespace Oleg\OrderformBundle\Form;

use Oleg\UserdirectoryBundle\Form\DocumentType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class AccessionLaborderType extends AbstractType
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

        //order
        $sources = array('WCMC Epic Ambulatory EMR','NYH Paper Requisition');
        $params = array('name'=>'Lab','dataClass'=>'Oleg\OrderformBundle\Entity\GeneralOrder','typename'=>'laborder','sources'=>$sources);
        $builder->add('order', new GeneralOrderType($params, null), array(
            'data_class' => 'Oleg\OrderformBundle\Entity\GeneralOrder',
            'label' => false,
        ));

        $builder->add('sourceLocation', 'entity', array(
            'class' => 'OlegUserdirectoryBundle:Location',
            'label' => 'Lab Order Source Location:',
            'required' => false,
            'multiple' => false,
            'attr' => array('class' => 'combobox combobox-width'),
            'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('list')
                        //->leftJoin("list.locationTypes", "locationTypes")
                        //->where("locationTypes.name = 'Medical Office'")
                        ->orderBy("list.orderinlist","ASC");
                },
        ));

        $builder->add('trackLocations', 'entity', array(
            'class' => 'OlegUserdirectoryBundle:Location',
            'label' => 'Lab Order Tracking Location(s):',
            'required' => false,
            'multiple' => true,
            'attr' => array('class' => 'combobox combobox-width'),
            'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('list')
                        //->leftJoin("list.locationTypes", "locationTypes")
                        //->where("locationTypes.name = 'Medical Office'")
                        ->orderBy("list.orderinlist","ASC");
                },
        ));



//        $builder->add('documentContainer', new DocumentContainerType(), array(
//            'data_class' => 'Oleg\OrderformBundle\Entity\DocumentContainer',
//            'label' => false
//        ));


//        $builder->add('orderinfo', 'entity', array(
//            'class' => 'OlegOrderformBundle:OrderInfo',
//            'label' => 'Lab Order ID:',
//            'required' => false,
//            'attr' => array('class' => 'combobox combobox-width'),
//            'property' => 'oid',
//            'query_builder' => function(EntityRepository $er) {
//                return $er->createQueryBuilder('list')
//                    ->orderBy("list.oid","ASC");
//            },
//        ));

//        $builder->add('documents', 'collection', array(
//            'type' => new DocumentType($this->params),
//            'label' => 'Requisition Form Image(s):',
//            'allow_add' => true,
//            'allow_delete' => true,
//            'required' => false,
//            'by_reference' => false,
//            'prototype' => true,
//            'prototype_name' => '__document__',
//        ));

//        $builder->add('imageTitle', null, array(
//            'label' => "Requisition Form Image Title:",
//            'attr' => array('class' => 'form-control'),
//        ));
//
//        $builder->add('imageComments', null, array(
//            'label' => "Requisition Form Image Comment(s):",
//            'attr' => array('class' => 'combobox combobox-width'),
//        ));
//
//        $builder->add('imageDevice', null, array(
//            'label' => "Requisition Form Image Device:",
//            'attr' => array('class' => 'combobox combobox-width'),
//        ));
//
//        $builder->add('imageDatetime','date',array(
//            'widget' => 'single_text',
//            'format' => 'MM-dd-yyyy',   //used for day dateline (no hours), so we don't need to set view_timezone
//            'attr' => array('class' => 'datepicker form-control scandeadline-mask', 'style'=>'margin-top: 0;'),
//            'required' => false,
//            'label'=>'Requisition Form Image Date & Time:',
//        ));
//
//        $builder->add('imageProvider', null, array(
//            'label' => "Requisition Form Image Scanned By:",
//            'attr' => array('class' => 'combobox combobox-width'),
//        ));



        $builder->add('others', new ArrayFieldType(), array(
            'data_class' => 'Oleg\OrderformBundle\Entity\AccessionLaborder',
            'label' => false,
			'attr' => array('style'=>'display:none;')
        ));


    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\AccessionLaborder',
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_accessionlabordertype';
    }
}
