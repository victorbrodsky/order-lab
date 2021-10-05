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

namespace App\TranslationalResearchBundle\Form;

use App\UserdirectoryBundle\Form\DocumentType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class ProjectChangeStatusConfirmationType extends AbstractType
{

    protected $params;

    public function formConstructor( $params )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $this->formConstructor($options['form_custom_value']);

//        $builder->add('updateState', TextType::class, array(
//            'label' => "Update status",
//            'mapped' => false,
//            'required' => false,
//            'attr' => array('class'=>'form-control'),
//        ));
        $builder->add('updateState',ChoiceType::class, array(
            'label' => 'Status:',
            'required' => false,
//            'choices'  => array(
//                'start' => 'start',
//                'draft' => 'draft',
//                'complete' => 'complete',
//                'irb_review' => 'irb_review',
//                'irb_rejected' => 'irb_rejected',
//                'admin_review' => 'admin_review',
//                'admin_rejected' => 'admin_rejected',
//                'committee_review' => 'committee_review',
//                'committee_rejected' => 'committee_rejected',
//                'final_review' => 'final_review',
//                'approved' => 'approved',
//                'not_approved' => 'not_approved',
//                'closed' => 'closed'
//            ),
            'choices' => $this->params['stateChoiceArr'],
            'data' => $this->params['currentStateId'],
            'attr' => array('class' => 'combobox'),
        ));
        
        $builder->add('updateBtn', SubmitType::class, array(
            'label' => "Update status",
            'attr' => array('class'=>'btn btn-primary'),
        ));

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
        return 'project-change-state-confirmation';
    }

}
