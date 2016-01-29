<?php

namespace Oleg\OrderformBundle\Form;

//use Oleg\UserdirectoryBundle\Form\InstitutionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

class PerSiteSettingsType extends AbstractType
{

    protected $user;
    protected $roleAdmin;
    protected $params;

    public function __construct( $user, $roleAdmin, $params )
    {
        $this->user = $user;
        $this->roleAdmin = $roleAdmin;
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        if( $this->roleAdmin ) {

            $builder->add( 'permittedInstitutionalPHIScope', 'entity', array(
                'class' => 'OlegUserdirectoryBundle:Institution',
                //'property' => 'name',
                'property' => 'getTreeName',
                'label'=>'Order data visible to members of (Institutional PHI Scope):',
                'required' => false,
                'multiple' => true,
                'attr' => array('class'=>'combobox combobox-width'),
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('list')
                        ->leftJoin("list.types","institutiontype")
                        //->where("(list.type = :typedef OR list.type = :typeadd) AND institutiontype.name = :medicalInstitution")
                        ->where("list.type = :typedef OR list.type = :typeadd")
                        ->orderBy("list.orderinlist","ASC")
                        ->setParameters( array(
                            'typedef' => 'default',
                            'typeadd' => 'user-added',
                            //'medicalInstitution' => 'Medical'
                        ));
                },
            ));

//            $builder->add('defaultInstitution', new InstitutionType(), array(
//                'required' => false,
//                'label' => false    //'Institution:'
//            ));

//            $builder->add( 'scanOrdersServicesScope', null, array(
//                'label'=>'Service(s) Scope:',
//                'required'=>false,
//                'multiple' => true,
//                'attr' => array('class'=>'combobox combobox-width')
//            ));
            //service. User should be able to add institution to administrative or appointment titles
//            $builder->add('scanOrdersServicesScope', 'employees_custom_selector', array(
//                'label' => "Service(s) Scope:",
//                'required' => false,
//                'attr' => array('class' => 'combobox combobox-width ajax-combobox-service', 'type' => 'hidden'),
//                'classtype' => 'service'
//            ));

//            $builder->add( 'chiefServices', null, array(
//                'label'=>'Chief of the following Service(s) for Scope:',
//                'required'=>false,
//                'multiple' => true,
//                'attr' => array('class'=>'combobox combobox-width')
//            ));


        } //roleAdmin


        $builder->add('tooltip', 'checkbox', array(
            'required' => false,
            'label' => 'Show tool tips for locked fields:',
            'attr' => array('class'=>'form-control', 'style'=>'margin:0')
        ));


        //ScanOrdersServicesScope
        $builder->add( 'scanOrdersServicesScope', 'entity', array(
            'class' => 'OlegUserdirectoryBundle:Institution',
            //'property' => 'name',
            'property' => 'getTreeName',
            'label'=>'Service(s) Scope:',
            'required'=> false,
            'multiple' => true,
            //'empty_value' => false,
            'attr' => array('class' => 'combobox combobox-width'),
            'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('list')
                        ->leftJoin("list.parent","department")
                        ->where("(list.type = :typedef OR list.type = :typeadd) AND department.name = :pname")
                        ->orderBy("list.orderinlist","ASC")
                        ->setParameters( array(
                            'typedef' => 'default',
                            'typeadd' => 'user-added',
                            'pname' => 'Pathology and Laboratory Medicine'
                            //'medicalInstitution' => 'Medical'
                        ));
                },
        ));

        //chiefServices
        $builder->add( 'chiefServices', 'entity', array(
            'class' => 'OlegUserdirectoryBundle:Institution',
            //'property' => 'name',
            'property' => 'getTreeName',
            'label'=>'Chief of the following Service(s) for Scope:',
            'required'=> false,
            'multiple' => true,
            //'empty_value' => false,
            'attr' => array('class' => 'combobox combobox-width'),
            'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('list')
                        ->leftJoin("list.parent","department")
                        ->where("(list.type = :typedef OR list.type = :typeadd) AND department.name = :pname")
                        ->orderBy("list.orderinlist","ASC")
                        ->setParameters( array(
                            'typedef' => 'default',
                            'typeadd' => 'user-added',
                            'pname' => 'Pathology and Laboratory Medicine'
                            //'medicalInstitution' => 'Medical'
                        ));
                },
        ));

        if( array_key_exists('em', $this->params) ) {
            //defaultInstitution
            $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                $title = $event->getData();
                $form = $event->getForm();

                $label = null;
                if( $title ) {
                    $institution = $title->getDefaultInstitution();
                    if( $institution ) {
                        $label = $this->params['em']->getRepository('OlegUserdirectoryBundle:Institution')->getLevelLabels($institution);
                    }
                }
                if( !$label ) {
                    $label = $this->params['em']->getRepository('OlegUserdirectoryBundle:Institution')->getLevelLabels(null);
                }

                $form->add('defaultInstitution', 'employees_custom_selector', array(
                    'label' => 'Default ' . $label . ':',
                    'read_only' => !$this->roleAdmin,
                    'required' => false,
                    'attr' => array(
                        'class' => 'ajax-combobox-compositetree',
                        'type' => 'hidden',
                        'data-compositetree-bundlename' => 'UserdirectoryBundle',
                        'data-compositetree-classname' => 'Institution',
                        'data-label-prefix' => 'Default'
                    ),
                    'classtype' => 'institution'
                ));
            });
        }


//        $builder->add('institution', 'employees_custom_selector', array(
//            'label' => 'Default Institution:',
//            'attr' => array('class' => 'ajax-combobox-compositetree combobox-without-add', 'type' => 'hidden'),
//            'required' => false,
//            'classtype' => 'institution'
//        ));

        //department. User should be able to add institution to administrative or appointment titles
//        $builder->add('defaultDepartment', 'employees_custom_selector', array(
//            'label' => "Default Department:",
//            'required' => false,
//            'attr' => array('class' => 'ajax-combobox-department combobox-without-add', 'type' => 'hidden'),
//            'classtype' => 'department'
//        ));
//
//        //division. User should be able to add institution to administrative or appointment titles
//        $builder->add('defaultDivision', 'employees_custom_selector', array(
//            'label' => "Default Division:",
//            'required' => false,
//            'attr' => array('class' => 'ajax-combobox-division combobox-without-add', 'type' => 'hidden'),
//            'classtype' => 'division'
//        ));
//
//        $builder->add( 'defaultService', 'employees_custom_selector', array(
//            'label'=>'Default Service:',
//            'required'=>false,
//            'attr' => array('class' => 'ajax-combobox-service combobox-without-add', 'type' => 'hidden'),
//            'classtype' => 'service'
//        ));

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
