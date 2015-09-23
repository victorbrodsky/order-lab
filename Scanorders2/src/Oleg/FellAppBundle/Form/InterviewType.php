<?php

namespace Oleg\FellAppBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

class InterviewType extends AbstractType
{

    protected $params;
    protected $rolePlatformAdmin;

    public function __construct( $params=null )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('interviewer', 'entity', array(
            'class' => 'OlegUserdirectoryBundle:User',
            'label' => "Interviewer:",
            'required' => false,
            'attr' => array('class' => 'combobox combobox-width'),
            'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('user')
                        ->leftJoin("user.infos", "infos")
                        ->leftJoin("user.employmentStatus", "employmentStatus")
                        ->leftJoin("employmentStatus.employmentType", "employmentType")
                        ->where("infos.lastName NOT LIKE 'test%' AND (employmentType.name != 'Pathology Fellowship Applicant' OR employmentType IS NULL)");
                        //->where('u.roles LIKE :role1 OR u.roles LIKE :role2')
                        //->setParameters(array('role1' => '%' . 'ROLE_FELLAPP_DIRECTOR' . '%', 'role2' => '%' . 'ROLE_FELLAPP_INTERVIEWER' . '%'));
                },
        ));

        $builder->add('interviewDate','date',array(
            'widget' => 'single_text',
            'label' => "Interview Date:",
            'format' => 'MM/dd/yyyy',
            'attr' => array('class' => 'datepicker form-control interview-interviewDate'),
            'required' => false,
        ));

        $builder->add('startTime', 'time', array(
            'input'  => 'datetime',
            'widget' => 'choice',
            'label'=>'Start Time:'
        ));

        $builder->add('endTime', 'time', array(
            'input'  => 'datetime',
            'widget' => 'choice',
            'label'=>'End Time:'
        ));

        $builder->add('academicRank',null, array(
            'label' => 'Academic Rank:',
            'required' => false,
            'attr' => array('class' => 'combobox combobox-width interview-rank interview-academicRank'),
        ));

        $builder->add('personalityRank',null, array(
            'label' => 'Personality Rank:',
            'required' => false,
            'attr' => array('class' => 'combobox combobox-width interview-rank interview-personalityRank'),
        ));

        $builder->add('potentialRank',null, array(
            'label' => 'Potential Rank:',
            'required' => false,
            'attr' => array('class' => 'combobox combobox-width interview-rank interview-potentialRank'),
        ));

        $builder->add('totalRank','text', array(
            'label' => 'Total Rank:',
            'required' => false,
            'read_only' => true,
            'attr' => array('class' => 'form-control interview-totalRank'),
        ));

        $builder->add('comment',null,array(
            'required' => false,
            'label'=>"Comments:",
            'attr' => array('class'=>'textarea form-control')
        ));

        $builder->add('languageProficiency',null, array(
            'label' => 'Language Proficiency:',
            'required' => false,
            'attr' => array('class' => 'combobox combobox-width interview-languageProficiency'),
        ));

//        $builder->add('location',null, array(
//            'label' => 'Location:',
//            'required' => false,
//            'attr' => array('class' => 'combobox combobox-width interview-location'),
//        ));
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $interview = $event->getData();
            $form = $event->getForm();

            $options = array(
                'label' => 'Interview Location:',
                'required' => false,
                'attr' => array('class' => 'combobox combobox-width interview-location'),
            );

            //$officeLocation = null;
            if( $interview && $interview->getInterviewer() ) {

                $officeLocation = $interview->getInterviewer()->getMainLocation();

                if( $officeLocation ) {
                    $options['data'] = $officeLocation;
                }

            }
            //echo "officeLocation=".$officeLocation."<br>";

            $form->add('location',null,$options);

//            $form->add('location',null, array(
//                'label' => 'Location:',
//                'required' => false,
//                'data' => $officeLocation,
//                'attr' => array('class' => 'combobox combobox-width interview-location'),
//            ));

        });

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\FellAppBundle\Entity\Interview',
        ));
    }

    public function getName()
    {
        return 'oleg_fellappbundle_interview';
    }
}
