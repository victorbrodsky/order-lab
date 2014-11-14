<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class PerSiteSettingsType extends AbstractType
{

    protected $user;
    protected $roleAdmin;

    public function __construct( $user, $roleAdmin )
    {
        $this->user = $user;
        $this->roleAdmin = $roleAdmin;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        if( $this->roleAdmin ) {

            $builder->add( 'permittedInstitutionalPHIScope', 'entity', array(
                'class' => 'OlegUserdirectoryBundle:Institution',
                'property' => 'name',
                'label'=>'Institutional PHI Scope:',
                'required'=> false,
                'multiple' => true,
                'attr' => array('class'=>'combobox combobox-width'),
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('list')
                        ->where("list.type = :typedef OR list.type = :typeadd")
                        ->orderBy("list.orderinlist","ASC")
                        ->setParameters( array(
                            'typedef' => 'default',
                            'typeadd' => 'user-added',
                        ));
                },
            ));

            $builder->add( 'scanOrdersServicesScope', null, array(
                'label'=>'Service(s) Scope:',
                'required'=>false,
                'multiple' => true,
                'attr' => array('class'=>'combobox combobox-width')
            ));
            //service. User should be able to add institution to administrative or appointment titles
//            $builder->add('scanOrdersServicesScope', 'employees_custom_selector', array(
//                'label' => "Service(s) Scope:",
//                'required' => false,
//                'attr' => array('class' => 'combobox combobox-width ajax-combobox-service', 'type' => 'hidden'),
//                'classtype' => 'service'
//            ));

            $builder->add( 'chiefServices', null, array(
                'label'=>'Chief of the following Service(s) for Scope:',
                'required'=>false,
                'multiple' => true,
                'attr' => array('class'=>'combobox combobox-width')
            ));



        }


        $builder->add('tooltip', 'checkbox', array(
            'required' => false,
            'label' => 'Show tool tips for locked fields:',
            'attr' => array('class'=>'form-control', 'style'=>'margin:0')
        ));



        $builder->add( 'defaultInstitution', 'entity', array(
            'class' => 'OlegUserdirectoryBundle:Institution',
            'property' => 'name',
            'label'=>'Default Institution:',
            'required'=> false,
            //'multiple' => false,
            //'empty_value' => false,
            'attr' => array('class' => 'combobox combobox-width combobox-institution ajax-combobox-institution-preset'),
            'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('list')
                        ->where("list.type = :typedef OR list.type = :typeadd")
                        ->orderBy("list.orderinlist","ASC")
                        ->setParameters( array(
                            'typedef' => 'default',
                            'typeadd' => 'user-added',
                        ));
                },
        ));
//        $builder->add('institution', 'employees_custom_selector', array(
//            'label' => 'Default Institution:',
//            'attr' => array('class' => 'ajax-combobox-institution combobox-without-add', 'type' => 'hidden'),
//            'required' => false,
//            'classtype' => 'institution'
//        ));

        //department. User should be able to add institution to administrative or appointment titles
        $builder->add('defaultDepartment', 'employees_custom_selector', array(
            'label' => "Default Department:",
            'required' => false,
            'attr' => array('class' => 'ajax-combobox-department combobox-without-add', 'type' => 'hidden'),
            'classtype' => 'department'
        ));

        //division. User should be able to add institution to administrative or appointment titles
        $builder->add('defaultDivision', 'employees_custom_selector', array(
            'label' => "Default Division:",
            'required' => false,
            'attr' => array('class' => 'ajax-combobox-division combobox-without-add', 'type' => 'hidden'),
            'classtype' => 'division'
        ));

        $builder->add( 'defaultService', 'employees_custom_selector', array(
            'label'=>'Default Service:',
            'required'=>false,
            'attr' => array('class' => 'ajax-combobox-service combobox-without-add', 'type' => 'hidden'),
            'classtype' => 'service'
        ));

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\PerSiteSettings',
            'csrf_protection' => false,
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_persitesettings';
    }
}
