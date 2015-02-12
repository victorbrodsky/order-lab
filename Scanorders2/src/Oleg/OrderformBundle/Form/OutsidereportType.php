<?php

namespace Oleg\OrderformBundle\Form;

use Oleg\UserdirectoryBundle\Form\DocumentType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class OutsidereportType extends AbstractType
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

        $builder->add('source', 'entity', array(
            'class' => 'OlegUserdirectoryBundle:SourceSystemList',
            'label' => 'Outside Report Source:',
            'required' => false,
            'attr' => array('class' => 'combobox combobox-width'),
            'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('list')
                        //->where("list.name = 'WCMC Epic Ambulatory EMR' OR list.name = 'NYH Paper Requisition'")
                        ->orderBy("list.orderinlist","ASC");
                },
        ));

        //order
        $sources = array(); //array('WCMC Epic Ambulatory EMR','Written or oral referral');
        $params = array('name'=>'Outside Report','dataClass'=>'Oleg\OrderformBundle\Entity\GeneralOrder','typename'=>'outsidereportorder','sources'=>$sources);
        $builder->add('order', new GeneralOrderType($params, null), array(
            'data_class' => 'Oleg\OrderformBundle\Entity\GeneralOrder',
            'label' => false,
        ));

        $builder->add('outsideReportType', 'entity', array(
            'label' => 'Outside Report Type:',
            'attr' => array('class' => 'combobox combobox-width'),
            'required' => false,
            'class' => 'OlegOrderformBundle:OutsideReportTypeList',
            'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('list')
                        ->orderBy("list.orderinlist","ASC");
                },
        ));

        $builder->add('outsideReportText', 'textarea', array(
            'max_length'=>10000,
            'required'=>false,
            'label'=>'Outside Report Text:',
            'attr' => array('class'=>'textarea form-control'),
        ));

        $builder->add('issuedonDate', 'date', array(
            'label' => "Outside Report Issued On Date:",
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM-dd-yyyy',   //used for birth day only (no hours), so we don't need to set view_timezone
            'attr' => array('class' => 'datepicker form-control patientdob-mask', 'style'=>'margin-top: 0;'),
        ));
        $builder->add('issuedonTime', 'time', array(
            'input'  => 'datetime',
            'widget' => 'choice',
            'label'=>'Outside Report Issued On Time:'
        ));

        $builder->add('receivedonDate', 'date', array(
            'label' => "Outside Report Received On Date:",
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM-dd-yyyy',   //used for birth day only (no hours), so we don't need to set view_timezone
            'attr' => array('class' => 'datepicker form-control patientdob-mask', 'style'=>'margin-top: 0;'),
        ));
        $builder->add('receivedonTime', 'time', array(
            'input'  => 'datetime',
            'widget' => 'choice',
            'label'=>'Outside Report Received On Time:'
        ));

        $builder->add('location', 'entity', array(
            'class' => 'OlegUserdirectoryBundle:Location',
            'label' => 'Outside Report Location:',
            'required' => false,
            'attr' => array('class' => 'combobox combobox-width'),
            'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('list')
                        //->leftJoin("list.locationTypes", "locationTypes")
                        //->where("locationTypes.name = 'Medical Office'")
                        ->orderBy("list.orderinlist","ASC");
                },
        ));


        $builder->add('others', new ArrayFieldType(), array(
            'data_class' => 'Oleg\OrderformBundle\Entity\OutsideReport',
            'label' => false,
			'attr' => array('style'=>'display:none;')
        ));


    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\OutsideReport',
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_outsidereporttype';
    }
}
