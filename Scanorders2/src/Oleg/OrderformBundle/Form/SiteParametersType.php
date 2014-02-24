<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SiteParametersType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('maxIdleTime',null,array(
            'label'=>'Max Idle Time (min):',
            //'attr' => array('class'=>'form-control','style'=>'width:100px')
        ));

        $builder->add('environment','choice',array(
            'label'=>'Environment:',
            'choices' => array("live"=>"live", "dev"=>"dev"),
            //'attr' => array('class'=>'form-control','style'=>'width:100px')
        ));
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\SiteParameters'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'oleg_orderformbundle_siteparameters';
    }
}
