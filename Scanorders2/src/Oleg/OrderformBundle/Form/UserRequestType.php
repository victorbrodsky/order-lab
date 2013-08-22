<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oleg\OrderformBundle\Helper\FormHelper;

class UserRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {     
        $helper = new FormHelper();
        
        $builder->add( 'cwid', 'text', array(
                'label'=>'CWID:',
                'max_length'=>'10',
                'required'=> false,
        ));
        
        $builder->add('request', 'textarea', array(
            'label'=>'Request:',
            'max_length'=>'1000',
            'required'=> false,
        ));

        $builder->add( 'pathologyService', 'choice', array(
            'label' => 'Pathology Service:',
            'max_length'=>200,
            'choices' => $helper->getPathologyService(),
            'required'=>false,
            'attr' => array('class' => 'combobox'),
        ));

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\UserRequest'
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_userrequesttype';
    }
}
