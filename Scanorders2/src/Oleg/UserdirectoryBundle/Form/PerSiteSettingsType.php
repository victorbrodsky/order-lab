<?php

namespace Oleg\UserdirectoryBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class PerSiteSettingsType extends AbstractType
{

//    protected $params;
//    protected $entity;
//
//    public function __construct( $params=null, $entity = null )
//    {
//        $this->params = $params;
//        $this->entity = $entity;
//    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {


        $builder->add( 'siteName', 'text', array(
            'label'=>'Site Name:',
            'required'=>false,
            'attr' => array('class'=>'form-control')
        ));

        $builder->add( 'permittedInstitutionalPHIScope', null, array(
            'label'=>'Permitted Institutional PHI Scope:',
            'required'=>false,
            'multiple' => true,
            'attr' => array('class'=>'combobox combobox-width')
        ));

        $builder->add( 'scanOrdersServicesScope', null, array(
            'label'=>'Scan Orders Services Scope:',
            'required'=>false,
            'multiple' => true,
            'attr' => array('class'=>'combobox combobox-width')
        ));

        $builder->add( 'chiefServices', null, array(
            'label'=>'Chief Services:',
            'required'=>false,
            'multiple' => true,
            'attr' => array('class'=>'combobox combobox-width')
        ));

        $builder->add( 'defaultService', null, array(
            'label'=>'Default Services:',
            'required'=>false,
            'attr' => array('class'=>'combobox combobox-width')
        ));


    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\PerSiteSettings',
        ));
    }

    public function getName()
    {
        return 'oleg_userdirectorybundle_persitesettings';
    }
}
