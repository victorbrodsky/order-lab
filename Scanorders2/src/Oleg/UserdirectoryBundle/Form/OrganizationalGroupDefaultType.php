<?php

namespace Oleg\UserdirectoryBundle\Form;


use Oleg\UserdirectoryBundle\Util\TimeZoneUtil;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;


class OrganizationalGroupDefaultType extends AbstractType
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

        $builder->add('id','hidden',array('label'=>false));

        $builder->add('primaryPublicUserIdType', 'entity', array(
            'class' => 'OlegUserdirectoryBundle:UsernameType',
            'property' => 'name',
            'label' => "Primary Public User ID Type:",
            'required' => false,
            'multiple' => false,
            'attr' => array('class'=>'combobox combobox-width user-keytype-field'),
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

        $builder->add('email', 'email', array(
            'label' => 'Preferred Email:',
            'attr' => array('class'=>'form-control user-email')
        ));

        $builder->add('roles', 'choice', array(
            'choices' => $this->roles,
            'label' => 'Role(s):',
            'attr' => array('class' => 'combobox combobox-width'),
            'multiple' => true,
        ));

        //timezone
        $tzUtil = new TimeZoneUtil();
        $builder->add( 'timezone', 'choice', array(
            'label' => 'Time Zone:',
            //'label' => $translator->translate('timezone',$formtype,'Time Zone:'),
            'choices' => $tzUtil->tz_list(),
            'required' => true,
            'preferred_choices' => array('America/New_York'),
            'attr' => array('class' => 'combobox combobox-width')
        ));

        $builder->add( 'languages', 'entity', array(
            'class' => 'OlegUserdirectoryBundle:LanguageList',
            'label'=> "Language(s):",
            'required'=> false,
            'multiple' => true,
            'property' => 'fulltitle',
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

        $builder->add( 'locale', 'entity', array(
            'class' => 'OlegUserdirectoryBundle:LocaleList',
            'label'=> "Locale:",
            'required'=> false,
            'multiple' => false,
            'property' => 'fulltitle',
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

        $builder->add( 'showToInstitutions', 'entity', array(
            'class' => 'OlegUserdirectoryBundle:Institution',
            //'property' => 'name',
            'property' => 'getTreeName',
            'label'=>'Only show this profile to members of the following institution(s):',
            'required'=> false,
            'multiple' => true,
            //'empty_value' => false,
            'attr' => array('class' => 'combobox combobox-width user-preferences-showToInstitutions'),
            'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('list')
                    ->where("(list.type = :typedef OR list.type = :typeadd) AND list.level = :level")
                    ->orderBy("list.orderinlist","ASC")
                    ->setParameters( array(
                        'typedef' => 'default',
                        'typeadd' => 'user-added',
                        'level' => 0
                    ));
            },
        ));

        $builder->add('tooltip', 'checkbox', array(
            'required' => false,
            'label' => 'Show tool tips for locked fields:',
            'attr' => array('class'=>'form-control', 'style'=>'margin:0')
        ));

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

        $builder->add('employmentType',null,array(
            'label'=>"Employee Type:",
            'required' => false,
            'attr' => array('class' => 'combobox combobox-width')
        ));

        $builder->add('locationTypes','entity',array(
            'class' => 'OlegUserdirectoryBundle:LocationTypeList',
            'label' => "Location Type:",
            'multiple' => true,
            'attr' => array('class'=>'combobox combobox-width'),
            'required' => false,
            'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('list')
                    ->where('list.type != :disabletype AND list.type != :drafttype')
                    ->orderBy("list.orderinlist","ASC")
                    ->setParameters( array('disabletype'=>'disabled','drafttype'=>'draft')
                    );
            }
        ));

        $builder->add('city', 'employees_custom_selector', array(
            'label' => 'Location City:',
            'required' => false,
            'attr' => array('class' => 'ajax-combobox-city', 'type' => 'hidden'),
            'classtype' => 'city'
        ));

        //state
        $builder->add( 'state', 'entity', array(
            'class' => 'OlegUserdirectoryBundle:States',
            //'property' => 'name',
            'label'=>'Location State:',
            'required'=> false,
            'multiple' => false,
            'data' => $this->params['em']->getRepository('OlegUserdirectoryBundle:States')->findOneByName('New York'),
            'attr' => array('class'=>'combobox combobox-width geo-field-state'),
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

        $builder->add('zip',null,array(
            'label'=>'Zip Code:',
            'attr' => array('class'=>'form-control geo-field-zip')
        ));

        //country
        $builder->add( 'country', 'entity', array(
            'class' => 'OlegUserdirectoryBundle:Countries',
            'property' => 'name',
            'label'=>'Location Country:',
            'required'=> false,
            'multiple' => false,
            'data' => $this->params['em']->getRepository('OlegUserdirectoryBundle:Countries')->findOneByName('United States'),
            'preferred_choices' => $this->params['em']->getRepository('OlegUserdirectoryBundle:Countries')->findByName(array('United States')),
            'attr' => array('class'=>'combobox combobox-width geo-field-country'),
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

        $builder->add( 'state', 'entity', array(
            'class' => 'OlegUserdirectoryBundle:States',
            //'property' => 'name',
            'label'=>'Location State:',
            'required'=> false,
            'multiple' => false,
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


        $builder->add( 'medicalLicenseCountry', 'entity', array(
            'class' => 'OlegUserdirectoryBundle:Countries',
            'property' => 'name',
            'label'=>'Medical License Country:',
            'required'=> false,
            'multiple' => false,
            'data' => $this->params['em']->getRepository('OlegUserdirectoryBundle:Countries')->findOneByName('United States'),
            'preferred_choices' => $this->params['em']->getRepository('OlegUserdirectoryBundle:Countries')->findByName(array('United States')),
            'attr' => array('class'=>'combobox combobox-width geo-field-country'),
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

        $builder->add( 'medicalLicenseState', 'entity', array(
            'class' => 'OlegUserdirectoryBundle:States',
            //'property' => 'name',
            'label'=>'Medical License State:',
            'required'=> false,
            'multiple' => false,
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


        ///////////////////////// tree node /////////////////////////
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $title = $event->getData();
            $form = $event->getForm();

            $label = null;
            if( $title ) {
                $institution = $title->getInstitution();
                if( $institution ) {
                    $label = $this->params['em']->getRepository('OlegUserdirectoryBundle:Institution')->getLevelLabels($institution) . ":";
                }
            }
            if( !$label ) {
                $label = $this->params['em']->getRepository('OlegUserdirectoryBundle:Institution')->getLevelLabels(null) . ":";
            }

            $form->add('institution', 'employees_custom_selector', array(
                'label' => "Target ".$label,
                //'error_bubbling' => true,
                'required' => false,
                'attr' => array(
                    'class' => 'ajax-combobox-compositetree',
                    'type' => 'hidden',
                    'data-compositetree-bundlename' => 'UserdirectoryBundle',
                    'data-compositetree-classname' => 'Institution',
                    'data-label-prefix' => 'Target'
                ),
                'classtype' => 'institution'
            ));

            $form->add('defaultInstitution', 'employees_custom_selector', array(
                'label' => 'Organizational Group ' . $label,
                'required' => false,
                'attr' => array(
                    'class' => 'ajax-combobox-compositetree',
                    'type' => 'hidden',
                    'data-compositetree-bundlename' => 'UserdirectoryBundle',
                    'data-compositetree-classname' => 'Institution',
                    'data-label-prefix' => 'Organizational Group'
                ),
                'classtype' => 'institution'
            ));

            //administrativeTitleInstitution
            $form->add('administrativeTitleInstitution', 'employees_custom_selector', array(
                'label' => "Administrative Title ".$label,
                'required' => false,
                'attr' => array(
                    'class' => 'ajax-combobox-compositetree',
                    'type' => 'hidden',
                    'data-compositetree-bundlename' => 'UserdirectoryBundle',
                    'data-compositetree-classname' => 'Institution',
                    'data-label-prefix' => 'Administrative Title'
                ),
                'classtype' => 'institution'
            ));

            //academicTitleInstitution
            $form->add('academicTitleInstitution', 'employees_custom_selector', array(
                'label' => "Academic Appointment Title ".$label,
                'required' => false,
                'attr' => array(
                    'class' => 'ajax-combobox-compositetree',
                    'type' => 'hidden',
                    'data-compositetree-bundlename' => 'UserdirectoryBundle',
                    'data-compositetree-classname' => 'Institution',
                    'data-label-prefix' => 'Academic Appointment Title'
                ),
                'classtype' => 'institution'
            ));

            //medicalTitleInstitution
            $form->add('medicalTitleInstitution', 'employees_custom_selector', array(
                'label' => "Medical Appointment Title ".$label,
                'required' => false,
                'attr' => array(
                    'class' => 'ajax-combobox-compositetree',
                    'type' => 'hidden',
                    'data-compositetree-bundlename' => 'UserdirectoryBundle',
                    'data-compositetree-classname' => 'Institution',
                    'data-label-prefix' => 'Medical Appointment Title'
                ),
                'classtype' => 'institution'
            ));

            $form->add('locationInstitution', 'employees_custom_selector', array(
                'label' => "Location ".$label,
                'required' => false,
                'attr' => array(
                    'class' => 'ajax-combobox-compositetree',
                    'type' => 'hidden',
                    'data-compositetree-bundlename' => 'UserdirectoryBundle',
                    'data-compositetree-classname' => 'Institution',
                    'data-label-prefix' => 'Location'
                ),
                'classtype' => 'institution'
            ));

        });
        ///////////////////////// EOF tree node /////////////////////////


    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\OrganizationalGroupDefault',
        ));
    }

    public function getName()
    {
        return 'oleg_userdirectorybundle_organizationalgroupdefaults';
    }
}
