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

use App\TranslationalResearchBundle\Entity\AntibodyCategoryTagList;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AntibodyFilterType extends AbstractType
{

    protected $params;

    public function formConstructor( $params=null )
    {
        $this->params = $params;
    }

    //Start Date, Start Time, End Date, End Time, User [Select2 dropdown), Event Type [Entity Updated], [Free Text Search value for Event column] [Filter Button]

    /**
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $this->formConstructor($options['form_custom_value']);

        $builder->add('search', TextType::class, array(
            //'placeholder' => 'Search',
            //'max_length' => 200,
            'required' => false,
            'label' => false,
            'attr' => array(
                'class' => 'form-control form-control-modif limit-font-size submit-on-enter-field',
                'placeholder'=>"Search by all fields"
            ),
        ));

        //Filter by Name, Description, Category Tags, Clone, Host, Reactivity, Company
        $builder->add('name', TextType::class, array(
            //'placeholder' => 'Search',
            //'max_length' => 200,
            'required' => false,
            'label' => false,
            'attr' => array('class' => 'form-control submit-on-enter-field', 'placeholder'=>"Name"),
        ));

        $builder->add('description', TextType::class, array(
            //'placeholder' => 'description',
            //'max_length' => 200,
            'required' => false,
            'label' => false,
            'attr' => array('class' => 'form-control submit-on-enter-field', 'placeholder'=>"Description"),
        ));

        ///// Open to Public ////
        //echo "publicPage=".$this->params['publicPage']."<br>";
        if( $this->params['publicPage'] === false ) {
//        $builder->add('public', CheckboxType::class, array(
//            'label' => 'Open to public:',
//            'required' => false,
//            'attr' => array('style' => 'width: 20px; height: 20px;')
//        ));
            $publicTypes = array(
                "Public" => "Public",
                "Private" => "Private",
            );
            $builder->add('public', ChoiceType::class, array(
                'label' => false,
                'choices' => $publicTypes,
                //'data' => array('default','user-added'),
                //'choices_as_values' => true,
                //'multiple' => true,
                'required' => false,
                'attr' => array(
                    'class' => 'combobox',
                    'placeholder' => "Public/Private",
                    //'style' => 'width: 20px; height: 20px;'
                )
            ));
        }
        ///// EOF Open to Public ////

//        $builder->add('categorytags', ChoiceType::class, array(
//            'label' => false, //"Category Tags:",
//            'placeholder' => 'Category Tags',
//            'choices' => array(
//                'Region Of Interest' => 'Region Of Interest',
//                'Whole Slide Image' => 'Whole Slide Image'
//            ),
//            'multiple' => true,
//            'required' => false,
//            'attr' => array('class' => 'combobox submit-on-enter-field', 'placeholder' => "Category Tags")
//        ));
        $builder->add('categorytags', EntityType::class, array(
            'class' => AntibodyCategoryTagList::class,
            //'choice_label' => 'getTreeName',
            'label'=>'Antibody Category Tag(s):',
            'required'=> false,
            'multiple' => true,
            'attr' => array('class'=>'combobox combobox-width', 'placeholder'=>"Category Tag(s)"),
            'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('list')
                    ->where("(list.type = :typedef OR list.type = :typeadd)")
                    ->orderBy("list.orderinlist","ASC")
                    ->setParameters( array(
                        'typedef' => 'default',
                        'typeadd' => 'user-added',
                    ));
            },
        ));

        $builder->add('clone', TextType::class, array(
            //'placeholder' => 'Search',
            //'max_length' => 200,
            'required' => false,
            'label' => false,
            'attr' => array('class' => 'form-control form-control-modif limit-font-size submit-on-enter-field', 'placeholder'=>'Clone'),
        ));

        $builder->add('host', TextType::class, array(
            //'placeholder' => 'Search',
            //'max_length' => 200,
            'required' => false,
            'label' => false,
            'attr' => array('class' => 'form-control form-control-modif limit-font-size submit-on-enter-field', 'placeholder'=>'Host'),
        ));

        $builder->add('reactivity', TextType::class, array(
            //'placeholder' => 'Search',
            //'max_length' => 200,
            'required' => false,
            'label' => false,
            'attr' => array('class' => 'form-control form-control-modif limit-font-size submit-on-enter-field', 'placeholder'=>'Reactivity'),
        ));

        $builder->add('company', TextType::class, array(
            //'placeholder' => 'Search',
            //'max_length' => 200,
            'required' => false,
            'label' => false,
            'attr' => array('class' => 'form-control form-control-modif limit-font-size submit-on-enter-field', 'placeholder'=>'Company'),
        ));


        //Show list type filter
        $types = array(
            "default" => "default",
            "user-added" => "user-added",
            "disabled" => "disabled",
            "draft" => "draft",
            "hidden" => "hidden"
        );
        $builder->add('type', ChoiceType::class, array(
            'label' => false,
            'choices' => $types,
            'data' => array('default','user-added'),
            //'choices_as_values' => true,
            'multiple' => true,
            'required' => false,
            'attr' => array('class' => 'combobox combobox-width select2-list-type', 'placeholder'=>"Type")
        ));

        $builder->add('catalog', TextType::class, array(
            'required' => false,
            'label' => false,
            'attr' => array('class' => 'form-control form-control-modif limit-font-size submit-on-enter-field', 'placeholder'=>'Catalog'),
        ));
        $builder->add('control', TextType::class, array(
            'required' => false,
            'label' => false,
            'attr' => array('class' => 'form-control form-control-modif limit-font-size submit-on-enter-field', 'placeholder'=>'Control'),
        ));
        $builder->add('protocol', TextType::class, array(
            'required' => false,
            'label' => false,
            'attr' => array('class' => 'form-control form-control-modif limit-font-size submit-on-enter-field', 'placeholder'=>'Protocol'),
        ));
        $builder->add('retrieval', TextType::class, array(
            'required' => false,
            'label' => false,
            'attr' => array('class' => 'form-control form-control-modif limit-font-size submit-on-enter-field', 'placeholder'=>'Retrieval'),
        ));
        $builder->add('dilution', TextType::class, array(
            'required' => false,
            'label' => false,
            'attr' => array('class' => 'form-control form-control-modif limit-font-size submit-on-enter-field', 'placeholder'=>'Dilution'),
        ));
        $builder->add('comment', TextType::class, array(
            'required' => false,
            'label' => false,
            'attr' => array('class' => 'form-control form-control-modif limit-font-size submit-on-enter-field', 'placeholder'=>'Comment'),
        ));

        $booleanChoices = array(
            //'Not set' => NULL,
            'Yes' => true,
            'No' => false,
        );

        //Add has document
        $builder->add('document', ChoiceType::class, array(
            'label' => "Has Document(s):",
            'choices' => $booleanChoices,
            'required' => false,
            'attr' => array('class' => 'combobox', 'placeholder'=>'Has Document(s)')
        ));
        //Has Visual Info Images
        $builder->add('visual', ChoiceType::class, array(
            'label' => "Has Visual Image(s):",
            'choices' => $booleanChoices,
            'required' => false,
            'attr' => array('class' => 'combobox', 'placeholder'=>'Has Visual Image(s)')
        ));
        //        //Add has ROI
//        $builder->add('hasRoi', ChoiceType::class, array(
//            'label' => "Has Region of Interest:",
//            'choices' => $this->booleanChoices,
//            'attr' => array('class' => 'form-control')
//        ));
//        //Add has WSI (Whole Slide Image)
//        $builder->add('hasWsi', ChoiceType::class, array(
//            'label' => "Has Whole Slide Image:",
//            'choices' => $this->booleanChoices,
//            'attr' => array('class' => 'form-control')
//        ));

    }

    /**
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'form_custom_value' => null,
            'csrf_protection' => false,
        ));
    }

    public function getBlockPrefix(): string
    {
        return 'filter';
    }
}
