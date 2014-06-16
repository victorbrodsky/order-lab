<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FilterSlideReturnRequestType extends AbstractType
{

    protected $status;

    public function __construct( $status = null )
    {
        $this->status = $status;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        if( $this->status == 'all' ) {
            $choices = array(   'all' => 'All',
                                'active' => 'Active',
                                'returned' => 'Returned',
                                'Checked: Not Received' => 'Checked: Not Received',
                                'Checked: Previously Returned' =>
                                'Checked: Previously Returned',
                                'Checked: Some Returned' => 'Checked: Some Returned',
                                'cancel' => 'Canceled'
                            );
        } else {
            $choices = array(
                                'active' => 'Active',
                                'returned' => 'Returned',
                                'all' => 'All',
                                'Checked: Not Received' => 'Checked: Not Received',
                                'Checked: Previously Returned' =>
                                'Checked: Previously Returned',
                                'Checked: Some Returned' => 'Checked: Some Returned',
                                'cancel' => 'Canceled'
                            );
        }

        $builder->add('filter', 'choice',
            array(
                //'mapped' => false,
                'label' => false,
                //'preferred_choices' => array($this->status),
                'attr' => array('class' => 'combobox combobox-width'),
                'choices' => $choices
            )
        );
        
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        //$resolver->setDefaults(array(
            //'data_class' => 'Oleg\OrderformBundle\Entity\Scan'
        //));
        $resolver->setDefaults(array(
            'csrf_protection' => false,
        ));
    }

    public function getName()
    {
        return 'filter_search_box';
    }
}
