<?php

namespace Oleg\UserdirectoryBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;


class CommentSubtypeType extends AbstractType
{

    protected $params;

    public function __construct( $params )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $attr = array('class' => 'ajax-combobox-commentsubtype', 'type' => 'hidden');
        if( $this->params['read_only'] ) {
            $attr['readonly'] = 'readonly';
        }
        $builder->add('name', 'employees_custom_selector', array(
            'label' => "Comment Name:",
            'required' => false,
            'attr' => $attr,
            'classtype' => 'commentSubType'
        ));

//        $builder->add('name', null, array(
//            'label' => "Comment Name:",
//            'required' => false,
//            'attr' => array('class'=>'combobox combobox-width')
//        ));


//        $builder->add('parent', new CommentTypeType($this->params), array(
//            'data_class' => 'Oleg\UserdirectoryBundle\Entity\CommentTypeList',
//            'label' => false,
//            'required' => false
//        ));


    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\CommentSubTypeList',
        ));
    }

    public function getName()
    {
        return 'oleg_userdirectorybundle_commentsubtype';
    }
}
