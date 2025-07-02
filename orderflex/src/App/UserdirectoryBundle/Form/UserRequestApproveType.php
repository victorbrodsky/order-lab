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

namespace App\UserdirectoryBundle\Form;



use App\UserdirectoryBundle\Entity\Institution; //process.py script: replaced namespace by ::class: added use line for classname=Institution
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

class UserRequestApproveType extends AbstractType
{

    protected $params;

    public function formConstructor( $params=null )
    {
        $this->params = $params;
    }

    /**
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value']);

        $builder->add( 'id', HiddenType::class );

        if( $this->params['sitename'] == "scan" ) {
            $builder->add('username', TextType::class, array(
                'label' => false,
                'required' => true,
                'attr' => array('class' => 'username'),
            ));
        }


        if(0) {
            if (array_key_exists('requestedScanOrderInstitutionScope', $this->params)) {
                $requestedScanOrderInstitutionScope = $this->params['requestedScanOrderInstitutionScope'];
            } else {
                $requestedScanOrderInstitutionScope = null;
            }
            //echo "choices=".count($requestedScanOrderInstitutionScope)."<br>";
            $builder->add('requestedScanOrderInstitutionScope', EntityType::class, array(
                'label' => 'Organizational Group:',
                'required' => false,
                'multiple' => false,
                //'empty_value' => false,
                'choice_label' => 'getNodeNameWithRoot',
                //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
                'class' => Institution::class,
                //'choices' => $requestedScanOrderInstitutionScope,
                'attr' => array('class' => 'combobox combobox-width combobox-institution')
            ));
        }

//        $builder->add( 'requestedInstitutionScope', TextType::class, array(
//            'label' => "Organizational Group:",
//            'required'=> false,
//            'attr' => array('class'=>'combobox combobox-width requestedInstitutionScope-select2'),
//        ));

        //dump($this->params['roles']);
        //exit('111');

        $builder->add('roles', ChoiceType::class, array(    //flipped
            'choices' => $this->params['roles'],
            //'choices_as_values' => true,
            'label' => false,   //ucfirst($this->params['sitename']) . ' Role(s):',
            'attr' => array('class' => 'combobox combobox-width'),
            'multiple' => true,
        ));

    }

    /**
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\UserdirectoryBundle\Entity\UserRequest',
            'form_custom_value' => null
        ));
    }

    public function getBlockPrefix(): string
    {
        return 'oleg_userdirectorybundle_userrequesttype';
    }
}
