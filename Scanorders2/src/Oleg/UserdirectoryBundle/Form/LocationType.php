<?php

namespace Oleg\UserdirectoryBundle\Form;



use Oleg\UserdirectoryBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

use Oleg\UserdirectoryBundle\Entity\Location;

class LocationType extends AbstractType
{

    protected $params;
    protected $entity;

    public function __construct( $params=null, $entity = null )
    {
        $this->params = $params;
        $this->entity = $entity;

        if( !array_key_exists('institution', $this->params) ) {
            $this->params['institution'] = true;
        }

        if( !array_key_exists('complexLocation', $this->params) ) {
            $this->params['complexLocation'] = true;
        }

    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        if (strpos($this->params['cycle'], '_standalone') === false) {
            $standalone = false;
        } else {
            $standalone = true;
        }

        $builder->add('id', 'hidden', array(
            'label' => false,
            'attr' => array('class' => 'user-object-id-field')
        ));

        $builder->add('name', null, array(
            'label' => "* Location's Name:",
            'attr' => array('class' => 'form-control user-location-name-field')
        ));

        $builder->add('phone', null, array(
            'label' => 'Phone Number:',
            'attr' => array('class' => 'form-control')
        ));

        $builder->add('email',null,array(
            'label'=>'E-Mail:',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('building', 'employees_custom_selector', array(
            'label' => 'Building:',
            'attr' => array('class' => 'ajax-combobox-building', 'type' => 'hidden'),
            'required' => false,
            'classtype' => 'building'
        ));

        $builder->add('geoLocation', new GeoLocationType($this->params), array(
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\GeoLocation',
            'label' => false,
            'required' => false
        ));

        $builder->add('comment', 'textarea', array(
            'max_length'=>5000,
            'required'=>false,
            'label'=>'Comment:',
            'attr' => array('class'=>'textarea form-control'),
        ));

        //locationTypes
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


        //complexLocation
        if( $this->params['complexLocation'] ) {
            //echo "show complex location<br>";

            $builder->add('name', null, array(
                'label' => "* Location's Name:",
                'attr' => array('class' => 'form-control user-location-name-field', 'required' => 'required')
            ));

            if( $this->params['cycle'] != "new_standalone" ) {
                $baseUserAttr = new Location();
                $builder->add('status', 'choice', array(
                    'disabled' => ($this->params['read_only'] ? true : false),
                    'choices' => array(
                        $baseUserAttr::STATUS_UNVERIFIED => $baseUserAttr->getStatusStrByStatus($baseUserAttr::STATUS_UNVERIFIED),
                        $baseUserAttr::STATUS_VERIFIED => $baseUserAttr->getStatusStrByStatus($baseUserAttr::STATUS_VERIFIED)
                    ),
                    'label' => "Status:",
                    'required' => true,
                    'attr' => array('class' => 'combobox combobox-width'),
                ));
            }

            $builder->add('pager', null, array(
                'label' => 'Pager Number:',
                'attr' => array('class' => 'form-control')
            ));

            $builder->add('mobile', null, array(
                'label' => 'Mobile Number:',
                'attr' => array('class' => 'form-control')
            ));

            $builder->add('ic', null, array(
                'label' => 'Intercom (IC):',
                'attr' => array('class' => 'form-control')
            ));

            $builder->add('fax', null, array(
                'label' => 'Fax:',
                'attr' => array('class' => 'form-control')
            ));

            $builder->add('floor', 'employees_custom_selector', array(
                'label' => 'Floor:',
                'attr' => array('class' => 'ajax-combobox-floor', 'type' => 'hidden'),
                'required' => false,
                'classtype' => 'floor'
            ));

            $builder->add('suite', 'employees_custom_selector', array(
                'label' => 'Suite:',
                'attr' => array('class' => 'ajax-combobox-suite', 'type' => 'hidden'),
                'required' => false,
                'classtype' => 'suite'
            ));

            $builder->add('mailbox', 'employees_custom_selector', array(
                'label' => 'Mailbox:',
                'attr' => array('class' => 'ajax-combobox-mailbox', 'type' => 'hidden'),
                'required' => false,
                'classtype' => 'mailbox'
            ));

            $builder->add('room', 'employees_custom_selector', array(
                'label' => 'Room Number:',
                'attr' => array('class' => 'ajax-combobox-room', 'type' => 'hidden'),
                'required' => false,
                'classtype' => 'room'
            ));

            $builder->add('associatedCode',null,array(
                'label'=>'Associated NYPH Code:',
                'attr' => array('class'=>'form-control')
            ));

            //In Locations, show the CLIA, and PFI fields only to Administrators and the user himself.
            if( $this->params['admin'] || $this->params['currentUser'] ) {
                $builder->add('associatedClia',null,array(
                    'label'=>'Associated Clinical Laboratory Improvement Amendments (CLIA) Number:',
                    'attr' => array('class'=>'form-control')
                ));

                $builder->add('associatedCliaExpDate', 'date', array(
                    'label' => "Associated CLIA Expiration Date:",
                    'widget' => 'single_text',
                    'required' => false,
                    'format' => 'MM/dd/yyyy',
                    'attr' => array('class' => 'datepicker form-control allow-future-date'),
                ));

                $builder->add('associatedPfi',null,array(
                    'label'=>'Associated NY Permanent Facility Identifier (PFI) Number:',
                    'attr' => array('class'=>'form-control')
                ));
            }

            //assistant
            if( $this->params['cycle'] != "new_standalone" ) {
                $builder->add( 'assistant', 'entity', array(
                    'class' => 'OlegUserdirectoryBundle:User',
                    'label'=> "Assistant(s):",
                    'required'=> false,
                    'multiple' => true,
                    'attr' => array('class'=>'combobox combobox-width'),
                    'query_builder' => function(EntityRepository $er) {
                        if( array_key_exists('subjectUser', $this->params) ) {
                            return $er->createQueryBuilder('list')
                                ->leftJoin("list.employmentStatus", "employmentStatus")
                                ->leftJoin("employmentStatus.employmentType", "employmentType")
                                ->where("list.id != :userid AND (employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL)")
                                ->leftJoin("list.infos", "infos")
                                ->orderBy("infos.displayName","ASC")
                                ->setParameters( array('userid' => $this->params['subjectUser']->getId()) );
                        } else {
                            return $er->createQueryBuilder('list')
                                ->leftJoin("list.infos", "infos")
                                ->orderBy("infos.displayName","ASC");
                        }
                    },
                ));
            }
        }


        ///////////////////////// tree node /////////////////////////
        if( $this->params['institution'] ) {
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
                    'label' => $label,
                    'required' => false,
                    'attr' => array(
                        'class' => 'ajax-combobox-compositetree',
                        'type' => 'hidden',
                        'data-compositetree-bundlename' => 'UserdirectoryBundle',
                        'data-compositetree-classname' => 'Institution'
                    ),
                    'classtype' => 'institution'
                ));
            });
        }
        ///////////////////////// EOF tree node /////////////////////////


        //Privacy
        $arrayOptions = array(
            'class' => 'OlegUserdirectoryBundle:LocationPrivacyList',
            'label' => "Location Privacy (who can see this contact info):",
            'multiple' => false,
            'attr' => array('class'=>'combobox combobox-width'),
            'required' => true,
        );

        //get default privacy
        if( $this->params['cycle'] == "new_standalone" ) {
            $defaultPrivacy = $this->params['em']->getRepository('OlegUserdirectoryBundle:LocationPrivacyList')->findOneByName("Anyone can see this contact information");
            $arrayOptions['data'] = $defaultPrivacy;
        }

        $builder->add('privacy','entity',$arrayOptions);


        //add user (Inhabitant) for all stand alone location management by LocationController
        if( $standalone ) {
            //user
            $builder->add('user', 'employees_custom_selector', array(
                'label'=> "Inhabitant / Contact:",
                'attr' => array('class' => 'combobox combobox-width combobox-without-add ajax-combobox-locationusers', 'type' => 'hidden'),
                'required' => false,
                //'multiple' => false,
                'classtype' => 'locationusers'
            ));
//            $builder->add( 'user', 'entity', array(
//                'class' => 'OlegUserdirectoryBundle:User',
//                'label'=> "Inhabitant / Contact:",
//                'required'=> false,
//                'multiple' => false,
//                'attr' => array('class'=>'combobox combobox-width'),
//                'query_builder' => function(EntityRepository $er) {
//                        $list = $er->createQueryBuilder('list')
//                            ->select()
//                            ->leftJoin("list.infos", "infos")
//                            ->orderBy("infos.displayName","ASC");
//                        return $list;
//                    },
//            ));

//            $builder->add('removable','checkbox',array(
//                'label' => "Removable:",
//            ));
        }

        //Consider stanAlone for all cycles with _standalone, except new_standalone. Cycle new_standalone is exception because we don't show list attributes in creation page
        if( $standalone && strpos($this->params['cycle'],'new') === false ) {
            //list attributes
            $params = array();
            $mapper = array();
            $params['user'] = $this->params['user'];
            $params['cycle'] = $this->params['cycle'];
            $params['standalone'] = true;
            $mapper['className'] = "Location";
            $mapper['bundleName'] = "OlegUserdirectoryBundle";

            $builder->add('list', new ListType($params, $mapper), array(
                'data_class' => 'Oleg\UserdirectoryBundle\Entity\Location',
                'label' => false
            ));
        }



    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\Location',
        ));
    }

    public function getName()
    {
        return 'oleg_userdirectorybundle_location';
    }

}
