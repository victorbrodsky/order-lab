<?php

namespace Oleg\UserdirectoryBundle\Form;



use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
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

    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $standAloneLocation = false;
        if( strpos($this->params['cicle'],'_standalone') !== false && strpos($this->params['cicle'],'new') === false ) {
            $standAloneLocation = true;
        }

        $builder->add('id','hidden',array(
            'label'=>false,
            'attr' => array('class'=>'user-object-id-field')
        ));

        $builder->add('name',null,array(
            'label'=>"* Location's Name:",
            'attr' => array('class'=>'form-control user-location-name-field', 'required'=>'required')
        ));

        $builder->add('phone',null,array(
            'label'=>'Phone Number:',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('pager',null,array(
            'label'=>'Pager Number:',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('mobile',null,array(
            'label'=>'Mobile Number:',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('ic',null,array(
            'label'=>'Intercom (IC):',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('fax',null,array(
            'label'=>'Fax:',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('email',null,array(
            'label'=>'E-Mail:',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('room',null,array(
            'label'=>'Room Number:',
            'attr' => array('class'=>'form-control')
        ));

//        $builder->add('building', new BuildingType($this->params), array(
//            'data_class' => 'Oleg\UserdirectoryBundle\Entity\BuildingList',
//            'label' => false,
//            'required' => false,
//        ));
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

        $builder->add('floor',null,array(
            'label'=>'Floor:',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('suit',null,array(
            'label'=>'Suite:',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('mailbox',null,array(
            'label'=>'Mailbox:',
            'attr' => array('class'=>'form-control')
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
                'format' => 'MM-dd-yyyy',
                'attr' => array('class' => 'datepicker form-control allow-future-date'),
            ));

            $builder->add('associatedPfi',null,array(
                'label'=>'Associated NY Permanent Facility Identifier (PFI) Number:',
                'attr' => array('class'=>'form-control')
            ));
        }

        $builder->add('comment', 'textarea', array(
            'max_length'=>5000,
            'required'=>false,
            'label'=>'Comment:',
            'attr' => array('class'=>'textarea form-control'),
        ));

        //assistant
        if( $this->params['cicle'] != "new_standalone" ) {
            $builder->add('assistant','entity',array(
                'class' => 'OlegUserdirectoryBundle:User',
                'label' => "Assistant(s):",
                'multiple' => true,
                'attr' => array('class'=>'combobox combobox-width'),
                'required' => false
            ));
        }

        if( $this->params['cicle'] != "new_standalone" ) {
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

        if( $this->params['cicle'] != "show_standalone" ) {
            $builder->add('locationType','entity',array(
                'class' => 'OlegUserdirectoryBundle:LocationTypeList',
                'label' => "Location Type:",
                'multiple' => false,
                'attr' => array('class'=>'combobox combobox-width'),
                'required' => false
            ));
        }

        //institution. User should be able to add institution to administrative or appointment titles
        $builder->add('institution', 'employees_custom_selector', array(
            'label' => 'Institution:',
            'attr' => array('class' => 'ajax-combobox-institution', 'type' => 'hidden'),
            'required' => false,
            'classtype' => 'institution'
        ));

        //department. User should be able to add institution to administrative or appointment titles
        $builder->add('department', 'employees_custom_selector', array(
            'label' => "Department:",
            'required' => false,
            'attr' => array('class' => 'combobox combobox-width ajax-combobox-department', 'type' => 'hidden'),
            'classtype' => 'department'
        ));

        //division. User should be able to add institution to administrative or appointment titles
        $builder->add('division', 'employees_custom_selector', array(
            'label' => "Division:",
            'required' => false,
            'attr' => array('class' => 'combobox combobox-width ajax-combobox-division', 'type' => 'hidden'),
            'classtype' => 'division'
        ));

        //service. User should be able to add institution to administrative or appointment titles
        $builder->add('service', 'employees_custom_selector', array(
            'label' => "Service:",
            'required' => false,
            'attr' => array('class' => 'combobox combobox-width ajax-combobox-service', 'type' => 'hidden'),
            'classtype' => 'service'
        ));


        //Privacy
        $arrayOptions = array(
            'class' => 'OlegUserdirectoryBundle:LocationPrivacyList',
            'label' => "Location Privacy (who can see this contact info):",
            'multiple' => false,
            'attr' => array('class'=>'combobox combobox-width'),
            'required' => true,
        );

        //get default privacy
        if( $this->params['cicle'] == "new_standalone" ) {
            $defaultPrivacy = $this->params['em']->getRepository('OlegUserdirectoryBundle:LocationPrivacyList')->findOneByName("Anyone can see this contact information");
            $arrayOptions['data'] = $defaultPrivacy;
        }

        $builder->add('privacy','entity',$arrayOptions);


        //add user and list properties for stand alone location managemenet by LocationController
        if( $standAloneLocation ) {
            //user
            $builder->add('user','entity',array(
                'class' => 'OlegUserdirectoryBundle:User',
                'label' => "Inhabitant:",
                'multiple' => false,
                'attr' => array('class'=>'combobox combobox-width'),
                'required' => false
            ));

            //list attributes
            $params = array();
            $mapper = array();
            $params['user'] = $this->params['user'];
            $params['cicle'] = $this->params['cicle'];
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
