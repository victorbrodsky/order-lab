<?php

namespace Oleg\VacReqBundle\Form;


use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

use Oleg\VacReqBundle\Form\VacReqRequestBusinessType;


class VacReqRequestType extends AbstractType
{

    protected $params;

    public function __construct( $params )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        //common fields for all request types
        $this->addCommonFields($builder);

        if( $this->params['requestType']->getAbbreviation() == "business-vacation" ) {
            $this->addBusinessVacationFields($builder);
        }

        if( $this->params['requestType']->getAbbreviation() == "carryover" ) {
            $this->addCarryOverFields($builder);
        }


        if( $this->params['requestType']->getAbbreviation() == "carryover" && ($this->params['cycle'] == 'review' || $this->params['cycle'] == 'show') ) {

            //enable status radio only for admin or for reviewer
            $readOnly = true;
            if( $this->params['review'] === true || $this->params['roleAdmin'] || $this->params['roleApprover'] ) {
                $readOnly = false;
            }

            $builder->add('status', 'choice', array(
                //'disabled' => $readOnly,    //($this->params['roleAdmin'] ? false : true),
                'read_only' => $readOnly,
                'choices' => array(
                    //'pending' => 'Pending',
                    'approved' => 'Approved',
                    'rejected' => 'Rejected'
                ),
                'label' => false,   //"Status:",
                'expanded' => true,
                'multiple' => false,
                'required' => true,
                //'data' => 'pending',
                'attr' => array('class' => 'horizontal_type_wide'), //horizontal_type
            ));

        }

        if( $this->params['requestType']->getAbbreviation() == "carryover" ) {
            $builder->add('comment', 'textarea', array(
                'label' => 'Comment:',
                //'read_only' => $readOnly,
                'required' => false,
                'attr' => array('class' => 'textarea form-control'),
            ));
        }

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\VacReqBundle\Entity\VacReqRequest',
            'csrf_protection' => false,
        ));
    }

    public function getName()
    {
        return 'oleg_vacreqbundle_request';
    }

    public function addBusinessVacationFields( $builder ) {
        $builder->add('phone', null, array(
            'label' => "Phone Number for the person away:",
            'attr' => array('class' => 'form-control vacreq-phone'),
            'read_only' => ($this->params['review'] ? true : false)
        ));

        //Business Travel
        $builder->add('requestBusiness', new VacReqRequestBusinessType($this->params), array(
            'data_class' => 'Oleg\VacReqBundle\Entity\VacReqRequestBusiness',
            'label' => false,
            'required' => false,
        ));

        //Business Travel
        $builder->add('requestVacation', new VacReqRequestVacationType($this->params), array(
            'data_class' => 'Oleg\VacReqBundle\Entity\VacReqRequestVacation',
            'label' => false,
            'required' => false,
        ));


        if( $this->params['cycle'] != 'show' && !$this->params['review'] ) {

            //enabled ($readOnly = false) for admin only
            $readOnly = true;
            if( $this->params['roleAdmin'] ) {
                $readOnly = false;
            }

            $builder->add('submitter', 'entity', array(
                'class' => 'OlegUserdirectoryBundle:User',
                'label' => "Request Submitter:",
                'required' => true,
                'multiple' => false,
                //'property' => 'name',
                'attr' => array('class' => 'combobox combobox-width'),
                'read_only' => true,    //$readOnly,   //($this->params['review'] ? true : false),
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('user')
                        ->leftJoin("user.infos","infos")
                        ->leftJoin("user.employmentStatus", "employmentStatus")
                        ->leftJoin("employmentStatus.employmentType", "employmentType")
                        ->andWhere("user.keytype IS NOT NULL AND user.primaryPublicUserId != 'system'")
                        ->andWhere("employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL")
                        ->orderBy("infos.lastName","ASC");
                },
            ));

        }


        //Emergency info
        $attrArr = array();
        if( $this->params['review'] ) {
            $attrArr['disabled'] = 'disabled';
        }

        $attrArr['class'] = 'vacreq-availableViaEmail';
        $builder->add('availableViaEmail', null, array(
            'label' => "Available via E-Mail:",
            'attr' => $attrArr, //array('class' => 'vacreq-availableViaEmail'),
            //'read_only' => ($this->params['review'] ? true : false)
        ));
        $builder->add('availableEmail', null, array(
            'label' => "E-Mail address while away on this trip:",
            'attr' => array('class' => 'form-control vacreq-availableEmail'),
            'read_only' => ($this->params['review'] ? true : false)
        ));

        $attrArr['class'] = 'vacreq-availableViaCellPhone';
        $builder->add('availableViaCellPhone', null, array(
            'label' => "Available via Cell Phone:",
            'attr' => $attrArr, //array('class' => 'vacreq-availableViaCellPhone'),
            //'read_only' => ($this->params['review'] ? true : false)
        ));
        $builder->add('availableCellPhone', null, array(
            'label' => "Cell Phone number while away on this trip:",
            'attr' => array('class' => 'form-control vacreq-availableCellPhone'),
            'read_only' => ($this->params['review'] ? true : false)
        ));

        $attrArr['class'] = 'vacreq-availableViaOther';
        $builder->add('availableViaOther', null, array(
            'label' => "Available via another method:",
            'attr' => $attrArr, //array('class' => 'vacreq-availableViaOther', 'disabled'=>$disableCheckbox),
            //'read_only' => ($this->params['review'] ? true : false)
        ));
        $builder->add('availableOther', null, array(
            'label' => "Other:",
            'attr' => array('class' => 'form-control vacreq-availableOther'),
            'read_only' => ($this->params['review'] ? true : false),
        ));

        $attrArr['class'] = 'vacreq-availableNone';
        $builder->add('availableNone', null, array(
            'label' => "Not Available:",
            'attr' => $attrArr, //array('class' => 'vacreq-availableNone'),
            //'read_only' => ($this->params['review'] ? true : false)
        ));


        $builder->add('firstDayBackInOffice', 'date', array(
            'label' => 'First Day Back in Office:',
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'attr' => array('class' => 'datepicker form-control allow-future-date vacreq-firstDayBackInOffice'),
            'read_only' => ($this->params['review'] ? true : false)
        ));
    }


    public function addCarryOverFields( $builder ) {

        $builder->add('sourceYear', 'choice', array(
            'label' => "Source Academic Year:",
            'attr' => array('class' => 'combobox combobox-width vacreq-sourceYear'),
            'choices' => $this->params['sourceYearRanges']
        ));

        $builder->add('destinationYear', 'choice', array(
            'label' => "Destination Academic Year:",
            'attr' => array('class' => 'combobox combobox-width vacreq-destinationYear'),
            'choices' => $this->params['destinationYearRanges']
        ));

        $builder->add('carryOverDays', null, array(
            'label' => "Number of days to carry over:",
            'attr' => array('class' => 'form-control vacreq-carryOverDays'),
        ));

    }


    public function addCommonFields( $builder ) {

        if( $this->params['cycle'] == 'show' ) {
            //approver
            $builder->add('approver', 'entity', array(
                'class' => 'OlegUserdirectoryBundle:User',
                'label' => "Approver:",
                'required' => false,
                'read_only' => true,
                'attr' => array('class' => 'combobox combobox-width'),
            ));
        }

        if( $this->params['cycle'] != 'show' && !$this->params['review'] ) {
            $builder->add('user', 'entity', array(
                'class' => 'OlegUserdirectoryBundle:User',
                'label' => "Person Away:",
                'required' => true,
                'multiple' => false,
                //'property' => 'name',
                'attr' => array('class' => 'combobox combobox-width'),
                //'read_only' => $readOnly,   //($this->params['review'] ? true : false),
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('user')
                        ->leftJoin("user.infos","infos")
                        ->leftJoin("user.employmentStatus", "employmentStatus")
                        ->leftJoin("employmentStatus.employmentType", "employmentType")
                        ->andWhere("user.keytype IS NOT NULL AND user.primaryPublicUserId != 'system'")
                        ->andWhere("employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL")
                        ->orderBy("infos.lastName","ASC");
                },
            ));
        }


        $requiredInst = false;
        if( count($this->params['organizationalInstitutions']) == 1 ) {
            //echo "set org inst <br>";
            $requiredInst = true;
        }
        //$requiredInst = true;
        $builder->add('institution', 'choice', array(
            'label' => "Organizational Group:",
            'required' => $requiredInst,
            'attr' => array('class' => 'combobox combobox-width vacreq-institution', 'placeholder' => 'Organizational Group'),
            'choices' => $this->params['organizationalInstitutions'],
            'read_only' => ($this->params['review'] ? true : false)
        ));
        $builder->get('institution')
            ->addModelTransformer(new CallbackTransformer(
                //original from DB to form: institutionObject to institutionId
                    function($originalInstitution) {
                        //echo "originalInstitution=".$originalInstitution."<br>";
                        if( is_object($originalInstitution) && $originalInstitution->getId() ) { //object
                            return $originalInstitution->getId();
                        }
                        return $originalInstitution; //id
                    },
                    //reverse from form to DB: institutionId to institutionObject
                    function($submittedInstitutionObject) {
                        //echo "submittedInstitutionObject=".$submittedInstitutionObject."<br>";
                        if( $submittedInstitutionObject ) { //id
                            $institutionObject = $this->params['em']->getRepository('OlegUserdirectoryBundle:Institution')->find($submittedInstitutionObject);
                            return $institutionObject;
                        }
                        return null;
                    }
                )
            );

    }

}
