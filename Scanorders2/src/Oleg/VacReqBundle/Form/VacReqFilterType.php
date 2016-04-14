<?php

namespace Oleg\VacReqBundle\Form;

use Doctrine\ORM\EntityRepository;
use Oleg\UserdirectoryBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class VacReqFilterType extends AbstractType
{

    private $params;


    public function __construct( $params=null )
    {
        $this->params = $params;
    }

    //Start Date, Start Time, End Date, End Time, User [Select2 dropdown), Event Type [Entity Updated], [Free Text Search value for Event column] [Filter Button]
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('user', 'entity', array(
            'class' => 'OlegUserdirectoryBundle:User',
            'property' => 'getUserNameStr',
            'label' => false,
            'required' => false,
            'multiple' => false,
            'attr' => array('class' => 'combobox', 'placeholder' => 'Faculty Name or CWID'),
            'choices' => $this->params['filterUsers'],
        ));

//        $builder->add('cwid', 'text', array(
//            'required' => false,
//            'label' => false,
//            'attr' => array('class' => 'form-control form-control-modif limit-font-size submit-on-enter-field'),
//        ));

//        $builder->add('search', 'text', array(
//            //'placeholder' => 'Search',
//            'max_length' => 200,
//            'required' => false,
//            'label' => false,
//            'attr' => array('class' => 'form-control form-control-modif limit-font-size submit-on-enter-field'),
//        ));

        $builder->add('startdate', 'date', array(
            'label' => false, //'Start Date/Time:',
            'required' => false,
            'widget' => 'single_text',
            'format' => 'MM/dd/yyyy',
            'attr' => array('class' => 'form-control datetimepicker', 'placeholder' => 'Start Date')
        ));

        $builder->add('enddate', 'date', array(
            'label' => false, //'End Date/Time:',
            'required' => false,
            'widget' => 'single_text',
            'format' => 'MM/dd/yyyy',
            'attr' => array('class' => 'form-control datetimepicker', 'placeholder' => 'End Date')
        ));

//        $builder->add('year', 'text', array(
//            'required' => false,
//            'label' => false,
//            'attr' => array('class' => 'form-control form-control-modif limit-font-size submit-on-enter-field', 'placeholder' => 'Year'),
//        ));

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
