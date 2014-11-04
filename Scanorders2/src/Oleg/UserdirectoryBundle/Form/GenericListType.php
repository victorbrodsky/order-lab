<?php

namespace Oleg\UserdirectoryBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class GenericListType extends AbstractType
{

    protected $params;
    protected $mapper;

    public function __construct( $params, $mapper )
    {
        $this->params = $params;
        $this->mapper = $mapper;
    }

        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        //tree classes
        if( method_exists($this->params['entity'],'getParent') ) {
            $builder->add('parent',null,array(
                'label' => $this->mapper['parentClassName'].':',
                'attr' => array('class' => 'combobox combobox-width', 'readonly'=>'readonly')
            ));
        }

        //research lab ResearchLabTitleList: show a column called "Principal Investigator" and have a dropdown of all users
//        if( strtolower($this->mapper['className']) == strtolower("ResearchLabTitleList") ) {
//            $builder->add('researchlab',null,array(
//                'label' => false,
//                'attr' => array('class' => 'combobox combobox-width', 'readonly'=>'readonly')
//            ));
//        }

        if( strtolower($this->mapper['className']) == strtolower("Roles") ) {
            $builder->add('alias',null,array(
                'label'=>'Alias:',
                'attr' => array('class' => 'form-control')
            ));
            $builder->add('description',null,array(
                'label'=>'Explanation of Capabilities:',
                'attr' => array('class' => 'textarea form-control')
            ));
        }

        $builder->add('list', new ListType($this->params, $this->mapper), array(
            'data_class' => $this->mapper['fullClassName'],
            'label' => false
        ));
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => $this->mapper['fullClassName']
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'oleg_userdirectorybundle_'.strtolower($this->mapper['className']);
    }
}
