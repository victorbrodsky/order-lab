<?php

namespace Oleg\UserdirectoryBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;



class UserWrapperType extends AbstractType
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

        $builder->add('userStr', null, array(
            'label' => 'Original as entered '.$this->params['labelPrefix'],
            'attr' => array('class' => 'form-control'),
        ));

        $builder->add('user', null, array(
            'label' => 'Mapped in DB '.$this->params['labelPrefix'],
            'attr' => array('class' => 'combobox combobox-width'),
        ));


    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\UserWrapper',
        ));
    }

    public function getName()
    {
        return 'oleg_userdirectorybundle_userwrappertype';
    }
}
