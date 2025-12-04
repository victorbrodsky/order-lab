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

namespace App\FellAppBundle\Form;



use App\UserdirectoryBundle\Entity\FellowshipSubspecialty; //process.py script: replaced namespace by ::class: added use line for classname=FellowshipSubspecialty

use App\UserdirectoryBundle\Form\GeoLocationType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\UserdirectoryBundle\Form\CustomType\CustomSelectorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

use App\UserdirectoryBundle\Entity\Training;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FellAppFellowshipApplicationType extends AbstractType
{

    private string $customPrefix;
    protected $params;

    public function formConstructor( $params=null )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->customPrefix = $options['block_prefix'];
        $this->formConstructor($options['form_custom_value']);

        //echo 'isHubServer='.$this->params['isHubServer']."<br>";
        if( $this->params['isHubServer'] ) {
            //echo "define globalfellowshipspecialty<br>";
            $builder->add('globalfellowshipspecialty', CustomSelectorType::class, array(
                'label' => "Global Fellowship Specialty:",
                'required' => false,
                'attr' => array('class' => 'combobox combobox-width ajax-combobox-globalfellowshipspecialty', 'type' => 'hidden'),
                'classtype' => 'globalfellowshipspecialty'
            ));
        } else {
            //echo "define fellowshipsubspecialtytype<br>";
            $builder->add('fellowshipsubspecialtytype', CustomSelectorType::class, array(
                'label' => "Fellowship Specialty:",
                'required' => false,
                'attr' => array('class' => 'combobox combobox-width ajax-combobox-fellowshipsubspecialty', 'type' => 'hidden'),
                'classtype' => 'fellowshipsubspecialty'
            ));
        }

        $builder->add('save', SubmitType::class,
            array(
                'label' => 'Add a New Fellowship Application Type',
                'attr' => array('class'=>'btn btn-primary'),
            )
        );

    }

    public function configureOptions(OptionsResolver $resolver) : void
    {
        $resolver->setDefaults(
            array(
            //'data_class' => 'App\UserdirectoryBundle\Entity\Training',
            'data_class' => null,
            'form_custom_value' => null,
            'block_prefix' => 'oleg_fellappbundle_fellappfellowshipapplicationtype',
            )
        );
    }

    public function getBlockPrefix(): string
    {
        //return 'oleg_fellappbundle_fellappfellowshipapplicationtype';
        return $this->customPrefix ?? 'oleg_fellappbundle_fellappfellowshipapplicationtype';

    }
}
