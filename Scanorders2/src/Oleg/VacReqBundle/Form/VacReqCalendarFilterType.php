<?php

namespace Oleg\VacReqBundle\Form;

use Doctrine\ORM\EntityRepository;
use Oleg\UserdirectoryBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class VacReqCalendarFilterType extends AbstractType
{

    private $params;


    public function __construct( $params=null )
    {
        $this->params = $params;
    }

    //Start Date, Start Time, End Date, End Time, User [Select2 dropdown), Event Type [Entity Updated], [Free Text Search value for Event column] [Filter Button]
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        if( count($this->params['organizationalInstitutions']) > 1 || $this->params['supervisor'] ) {

            if( count($this->params['organizationalInstitutions']) == 1 ) {
                $required = true;
            } else {
                $required = false;
            }

            $groupParams = array('label' => false,   //"Organizational Group:",
                'required' => $required,
                'multiple' => false,
                'attr' => array('class' => 'combobox combobox-width organizationalInstitutions', 'placeholder' => 'Organizational Group'),
                'choices' => $this->params['organizationalInstitutions']
            );

            if( $this->params['groupId'] ) {
                $groupParams['data'] = $this->params['groupId'];
            }

            //Institutional Group name - ApproverName
            $builder->add('organizationalInstitutions', 'choice', $groupParams);
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

}
