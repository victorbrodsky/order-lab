<?php

namespace Oleg\UserdirectoryBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;


class BaseCommentsType extends AbstractType
{

    protected $params;

    public function __construct( $params )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('commentType', 'employees_custom_selector', array(
            'label' => 'Comment Category:',
            'attr' => array('class' => 'ajax-combobox-commenttype', 'type' => 'hidden'),
            'required' => false,
            'classtype' => 'commentType'
        ));

        $builder->add('commentSubType', 'employees_custom_selector', array(
            'label' => "Comment Type:",
            'required' => false,
            'attr' => array('class' => 'combobox combobox-width ajax-combobox-commentsubtype', 'type' => 'hidden'),
            'classtype' => 'commentSubType'
        ));

        $builder->add( 'comment', 'textarea', array(
            'label'=>'Comment:',
            'required'=>false,
            'attr' => array('class' => 'textarea form-control')
        ));

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => $this->params['fullClassName'],
        ));
    }

    public function getName()
    {
        return 'oleg_userdirectorybundle_'.$this->params['formname'];
    }
}
