<?php

namespace Oleg\VacReqBundle\Form;

use Doctrine\ORM\EntityRepository;
use Oleg\UserdirectoryBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class VacReqFilterType extends AbstractType
{

    private $params;


    public function __construct( $params=null )
    {
        $this->params = $params;
    }

    //Start Date, Start Time, End Date, End Time, User [Select2 dropdown), Event Type [Entity Updated], [Free Text Search value for Event column] [Filter Button]
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        //visible only for my request and incoming requests for SUPERVISOR users
        if( $this->params['routeName'] == 'vacreq_myrequests' || $this->params['supervisor'] ) {
            $builder->add('requestType', 'entity', array(
                'class' => 'OlegVacReqBundle:VacReqRequestTypeList',
                'property' => 'name',
                'label' => false,
                'required' => true,
                'multiple' => false,
                'attr' => array('class' => 'combobox combobox-width', 'placeholder' => 'Request Type'),
                //'choices' => $this->params['filterUsers'],
            ));
        }

        if ($this->params['filterShowUser']) {
            $builder->add('user', 'entity', array(
                'class' => 'OlegUserdirectoryBundle:User',
                'property' => 'getUserNameStr',
                'label' => false,
                'required' => false,
                'multiple' => false,
                'attr' => array('class' => 'combobox', 'placeholder' => 'Person Away - Name or CWID)'),
                'choices' => $this->params['filterUsers'],
            ));

            $builder->add('submitter', 'entity', array(
                'class' => 'OlegUserdirectoryBundle:User',
                'property' => 'getUserNameStr',
                'label' => false,
                'required' => false,
                'multiple' => false,
                'attr' => array('class' => 'combobox', 'placeholder' => 'Submitter - Name or CWID'),
                'choices' => $this->params['filterUsers'],
            ));

            $this->addGroup($builder);
        } else {
//            $builder->add('requestType', 'entity', array(
//                'class' => 'OlegVacReqBundle:VacReqRequestTypeList',
//                'property' => 'name',
//                'label' => false,
//                'required' => true,
//                'multiple' => false,
//                'attr' => array('class' => 'combobox combobox-width', 'placeholder' => 'Request Type'),
//                //'choices' => $this->params['filterUsers'],
//            ));
        }

        if( $this->params['requestTypeAbbreviation'] == "business-vacation" ) {
            $builder->add('academicYear', 'datetime', array(
                'label' => false,
                'widget' => 'single_text',
                'required' => false,
                'format' => 'yyyy',
                'attr' => array('class' => 'datepicker-only-year form-control', 'placeholder' => 'Academic Year', 'title' => $this->params['academicYearTooltip'], 'data-toggle' => 'tooltip'),
            ));
        }

//        $builder->add('cwid', 'text', array(
//            'required' => false,
//            'label' => false,
//            'attr' => array('class' => 'form-control form-control-modif limit-font-size submit-on-enter-field'),
//        ));

//        $builder->add('search', 'text', array(
//            //'placeholder' => 'Search',
//            'max_length' => 200,
//            'required' => false,
//            'label' => false,
//            'attr' => array('class' => 'form-control form-control-modif limit-font-size submit-on-enter-field'),
//        ));

        $builder->add('startdate', 'date', array(
            'label' => false, //'Start Date/Time:',
            'required' => false,
            'widget' => 'single_text',
            'format' => 'MM/dd/yyyy',
            'attr' => array('class' => 'form-control datetimepicker', 'placeholder' => 'Start Date', 'title'=>'Start Date of Request Submission', 'data-toggle'=>'tooltip')
        ));

        $builder->add('enddate', 'date', array(
            'label' => false, //'End Date/Time:',
            'required' => false,
            'widget' => 'single_text',
            'format' => 'MM/dd/yyyy',
            'attr' => array('class' => 'form-control datetimepicker', 'placeholder' => 'End Date', 'title'=>'End Date of Request Submission', 'data-toggle'=>'tooltip')
        ));

//        $builder->add('year', 'text', array(
//            'required' => false,
//            'label' => false,
//            'attr' => array('class' => 'form-control form-control-modif limit-font-size submit-on-enter-field', 'placeholder' => 'Year'),
//        ));

        if( $this->params['requestTypeAbbreviation'] == "business-vacation" ) {
            $builder->add('vacationRequest', 'checkbox', array(
                'label' => 'Vacation Requests',
                'required' => false,
            ));
            $builder->add('businessRequest', 'checkbox', array(
                'label' => 'Business Travel Requests',
                'required' => false,
            ));
        }

//        $builder->add('completed', 'checkbox', array(
//            'label' => 'Completed Requests',
//            'required' => false,
//        ));
        $builder->add('pending', 'checkbox', array(
            'label' => 'Pending Requests',
            'required' => false,
        ));
        $builder->add('approved', 'checkbox', array(
            'label' => 'Approved Requests',
            'required' => false,
        ));
        $builder->add('rejected', 'checkbox', array(
            'label' => 'Rejected Requests',
            'required' => false,
        ));


    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection' => false,
        ));
    }

    public function getName()
    {
        return 'filter';
    }

    public function addGroup($builder) {

        if( count($this->params['organizationalInstitutions']) > 1 ) {

            //Institutional Group name - ApproverName
//            $builder->add('organizationalInstitutions', 'choice', array(
//                //'class' => 'OlegUserdirectoryBundle:Institution',
//                //'property' => 'getUserNameStr',
//                'label' => false,
//                'required' => false,
//                //'multiple' => false,
//                'attr' => array('class' => 'combobox', 'placeholder' => 'Group'),
//                'choices' => $this->params['organizationalInstitutions'],
//            ));
            $builder->add('organizationalInstitutions', 'choice', array(
                'label' => false,   //"Organizational Group:",
                'required' => false,
                'multiple' => false,
                'attr' => array('class' => 'combobox combobox-width', 'placeholder' => 'Organizational Group'),
                'choices' => $this->params['organizationalInstitutions'],
            ));
            $builder->get('organizationalInstitutions')
                ->addModelTransformer(new CallbackTransformer(
                    //original from DB to form: institutionObject to institutionId
                        function ($originalInstitution) {
                            //echo "originalInstitution=".$originalInstitution."<br>";
                            if (is_object($originalInstitution) && $originalInstitution->getId()) { //object
                                return $originalInstitution->getId();
                            }
                            return $originalInstitution; //id
                        },
                        //reverse from form to DB: institutionId to institutionObject
                        function ($submittedInstitutionObject) {
                            //echo "submittedInstitutionObject=".$submittedInstitutionObject."<br>";
                            if ($submittedInstitutionObject) { //id
                                $institutionObject = $this->params['em']->getRepository('OlegUserdirectoryBundle:Institution')->find($submittedInstitutionObject);
                                return $institutionObject;
                            }
                            return null;
                        }
                    )
                );

        }//if

    }//addGroup
}
