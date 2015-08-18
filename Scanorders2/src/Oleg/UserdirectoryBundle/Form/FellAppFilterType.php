<?php

namespace Oleg\UserdirectoryBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FellAppFilterType extends AbstractType
{

    private $params;

    public function __construct( $params=null )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('startDate', 'datetime', array(
            'label' => false, //'Start Date',
            'widget' => 'single_text',
            //'placeholder' => 'Start Date',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'attr' => array('class' => 'datepicker form-control'),
        ));

//        $builder->add('endDate', 'datetime', array(
//            'label' => 'End Date:',
//            'widget' => 'single_text',
//            'required' => false,
//            'format' => 'MM/dd/yyyy',
//            'attr' => array('class' => 'datepicker form-control'),
//        ));

        $builder->add('filter', 'entity', array(
            'class' => 'OlegUserdirectoryBundle:FellowshipSubspecialty',
            //'placeholder' => 'Fellowship Type',
            'property' => 'name',
            'label' => false,
            'required'=> false,
            'multiple' => false,
            'attr' => array('class' => 'combobox fellapp-fellowshipSubspecialty-filter'),
            'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('list')
                        ->where("list.type = :typedef OR list.type = :typeadd")
                        ->orderBy("list.orderinlist","ASC")
                        ->setParameters( array(
                            'typedef' => 'default',
                            'typeadd' => 'user-added',
                        ));
                },
        ));
        
        $builder->add('search', 'text', array(
            //'placeholder' => 'Search',
            'max_length'=>200,
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
