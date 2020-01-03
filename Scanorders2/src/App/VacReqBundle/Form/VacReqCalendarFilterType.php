<?php
/**
 * Copyright (c) 2017 Cornell University
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

namespace App\VacReqBundle\Form;

use Doctrine\ORM\EntityRepository;
use App\UserdirectoryBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VacReqCalendarFilterType extends AbstractType
{

    private $params;


    public function formConstructor( $params=null )
    {
        $this->params = $params;
    }

    //Start Date, Start Time, End Date, End Time, User [Select2 dropdown), Event Type [Entity Updated], [Free Text Search value for Event column] [Filter Button]
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value']);

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
                'choices' => $this->params['organizationalInstitutions'],
                //'choices_as_values' => true,
            );

            if( $this->params['groupId'] ) {
                $groupParams['data'] = $this->params['groupId'];
            }

            //Institutional Group name - ApproverName
            $builder->add('organizationalInstitutions', ChoiceType::class, $groupParams); //flipped

            $builder->get('organizationalInstitutions')
                ->addModelTransformer(new CallbackTransformer(
                    //original from DB to form: institutionObject to institutionId
                        function ($originalInstitution) {
                            //echo "originalInstitution=" . $originalInstitution . "<br>";
                            if (is_object($originalInstitution) && $originalInstitution->getId()) { //object
                                return $originalInstitution->getId();
                            }
                            return $originalInstitution; //id
                        },
                        //reverse from form to DB: institutionId to institutionObject
                        function ($submittedInstitutionObject) {
                            //echo "submittedInstitutionObject=" . $submittedInstitutionObject . "<br>";
                            if ($submittedInstitutionObject) { //id
                                $institutionObject = $this->params['em']->getRepository('AppUserdirectoryBundle:Institution')->find($submittedInstitutionObject);
                                return $institutionObject;
                            }
                            return null;
                        }
                    )
                );

        }//if

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection' => false,
            'form_custom_value' => null
        ));
    }

    public function getBlockPrefix()
    {
        return 'filter';
    }

}
