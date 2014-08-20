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

//        if( $this->mapper['className'] == "Status" ) {
//            $builder
//                ->add('action',null,array(
//                    'label'=>'Action:',
//                    'attr' => array('class' => 'combobox combobox-width')
//                ));
//        }

        if( $this->mapper['className'] == "Roles" ) {
            $builder
                ->add('alias',null,array(
                    'label'=>'Alias:',
                    'attr' => array('class' => 'combobox combobox-width')
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
