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

namespace Oleg\DeidentifierBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class DeidentifierSearchType extends AbstractType
{

    private $params;

    public function formConstructor( $params=null )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value']);

//        $builder->add('accessiontype', 'choice', array(
//            'label' => 'Accession Number:',
//            'mapped' => false,
//            'required' => true,
//            'attr' => array('class' => 'combobox')
//        ));

        $builder->add('accessionType', 'entity', array(
            'class' => 'OlegOrderformBundle:AccessionType',
            'label'=> "Accession Type:",
            'mapped' => false,
            'required'=> true,
            'multiple' => false,
            'property' => 'name',
            'attr' => array('class'=>'combobox combobox-width accessiontype-combobox skip-server-populate'),
            'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('list')
                        //->where("(list.type = :typedef OR list.type = :typeadd) AND list.name='NYH CoPath Anatomic Pathology Accession Number'")
                        ->where("list.type = :typedef OR list.type = :typeadd")
                        ->orderBy("list.orderinlist","ASC")
                        ->setParameters( array(
                            'typedef' => 'default',
                            'typeadd' => 'user-added',
                        ));
            },
        ));
        
        $builder->add('accessionNumber', 'text', array(
            'label'=>'Accession Number:',
            'mapped' => false,
            'required'=>false,
            'attr' => array('class'=>'form-control accession-mask deidentifier-generate-accessionNumber submit-on-enter-field', ), //submit-on-enter-field
        ));

        //institution
        //echo "defaultInstitution=".$this->params['defaultInstitution']->getId()."<br>";
        if( $this->params['defaultInstitution'] ) {
            $readOnly = true;
        } else {
            $readOnly = false;
        }

        $builder->add('institution', 'entity', array(
            'label' => 'Organizational Group (Institutional PHI Scope):',
            'class' => 'OlegUserdirectoryBundle:Institution',
            'choices' => $this->params['permittedInstitutions'],
            'data' => $this->params['defaultInstitution'],
            'read_only' => $readOnly,
            'mapped' => false,
            'property' => 'getNodeNameWithRoot',
            'required' => true,
            'multiple' => false,
            'empty_value' => false,
            'attr' => array('class' => 'combobox combobox-width combobox-institution')
        ));

//        $builder->add('generate', 'submit', array(
//            'label' => "Generate a New Deidentifier",
//            'attr' => array('class' => 'btn btn-sm btn-primary')
//        ));

//        $builder->add('search', 'submit', array(
//            'label' => "Search",
//            'attr' => array('class' => 'btn btn-sm btn-default')
//        ));
        
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'form_custom_value' => null,
            'csrf_protection' => false,
        ));
    }

    public function getBlockPrefix()
    {
        return null;
        return 'deidentifier_search_box';
    }
}
