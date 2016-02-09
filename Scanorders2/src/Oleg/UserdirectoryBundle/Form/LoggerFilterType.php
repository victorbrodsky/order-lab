<?php

namespace Oleg\UserdirectoryBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class LoggerFilterType extends AbstractType
{

    private $params;

    public function __construct( $params=null )
    {
        $this->params = $params;
    }

    //Start Date, Start Time, End Date, End Time, User [Select2 dropdown), Event Type [Entity Updated], [Free Text Search value for Event column] [Filter Button]
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

//        $builder->add('creationdate', 'datetime', array(
//            'label' => false, //'Start Date',
//            'widget' => 'single_text',
//            //'placeholder' => 'Start Date',
//            'required' => false,
//            //'format' => 'MM/dd/yyyy',
//            'format' => 'yyyy',
//            //'attr' => array('class' => 'datepicker form-control'),
//            //'attr' => array('class' => 'datepicker-only-year form-control'),
//            'attr' => array('class'=>'datepicker-only-year form-control', 'title'=>'Start Year', 'data-toggle'=>'tooltip'),
//        ));

        $builder->add('user', 'entity', array(
            'class' => 'OlegUserdirectoryBundle:User',
            //'placeholder' => 'Fellowship Type',
            'property' => 'getUserNameStr',
            'label' => false,
            'required'=> false,
            'multiple' => true,
            'attr' => array('class' => 'combobox'),
            'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('list')
                        ->leftJoin("list.infos","infos")
                        ->where("list.keytype IS NOT NULL")
                        ->orderBy("infos.lastName","ASC");
                },
        ));

        //Event Type
        $builder->add('eventType', 'entity', array(
            'class' => 'OlegUserdirectoryBundle:EventTypeList',
            //'placeholder' => 'Fellowship Type',
            'property' => 'name',
            'label' => false,
            'required'=> false,
            'multiple' => true,
            'attr' => array('class' => 'combobox'),
            'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('list')
                        ->where("list.type = :typedef OR list.type = :typeadd")
                        ->orderBy("list.name","ASC")
                        ->setParameters( array(
                            'typedef' => 'default',
                            'typeadd' => 'user-added',
                        ));
                },
        ));

//        $builder->add('filter', 'choice', array(
//            'label' => false,
//            'required'=> false,
//            //'multiple' => false,
//            'choices' => $this->params['fellTypes'],
//            'attr' => array('class' => 'combobox combobox-width fellapp-fellowshipSubspecialty-filter'),
//        ));
        
        $builder->add('search', 'text', array(
            //'placeholder' => 'Search',
            'max_length'=>200,
            'required'=>false,
            'label' => false,
            'attr' => array('class'=>'form-control form-control-modif limit-font-size submit-on-enter-field'),
        ));


        $builder->add('startdate','datetime',array(
            'label' => false, //'Start Date/Time:',
            'required'=>false,
            'widget' => 'single_text',
            'format' => 'MM/dd/yyyy H:m',
            'attr' => array('class'=>'form-control datetimepicker', 'placeholder' => 'Start Date/Time')
        ));

        $builder->add('enddate','datetime',array(
            'label' => false, //'End Date/Time:',
            'required'=>false,
            'widget' => 'single_text',
            'format' => 'MM/dd/yyyy H:m',
            'attr' => array('class'=>'form-control datetimepicker', 'placeholder' => 'End Date/Time')
        ));

        $builder->add('ip', 'text', array(
            //'placeholder' => 'Search',
            'required'=>false,
            'label' => false,
            'attr' => array('class'=>'form-control form-control-modif limit-font-size submit-on-enter-field'),
        ));

        $builder->add('roles', 'entity', array(
            'class' => 'OlegUserdirectoryBundle:Roles',
            'property' => 'alias',
            'label' => false,
            'required'=> false,
            'multiple' => true,
            'attr' => array('class' => 'combobox combobox-width'),
            'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('list')
                        ->where("list.type = :typedef OR list.type = :typeadd")
                        ->orderBy("list.name","ASC")
                        ->setParameters( array(
                            'typedef' => 'default',
                            'typeadd' => 'user-added',
                        ));
                },
        ));

        $builder->add('object', 'text', array(
            'required'=>false,
            'label' => false,
            'attr' => array('class'=>'form-control form-control-modif limit-font-size submit-on-enter-field'),
        ));

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection' => false,
        ));
    }

    public function getName()
    {
        return 'filter';
    }
}
