<?php

namespace App\TranslationalResearchBundle\Form;


use App\TranslationalResearchBundle\Entity\AntibodyCategoryTagList;
use App\TranslationalResearchBundle\Entity\AntibodyList;
use App\TranslationalResearchBundle\Entity\PriceTypeList;
use App\UserdirectoryBundle\Form\DocumentType;
use App\UserdirectoryBundle\Form\ListType;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class AntibodyType extends AbstractType
{

    protected $params;
    protected $mapper;

    public function formConstructor( $params )
    {
        $this->params = $params;

//        $bundleName = "TranslationalResearchBundle";
//        $className = "AntibodyList";
//        $displayName = "Antibody List";
//        $mapper = array();
//        $mapper['className'] = $className;
//        $mapper['fullClassName'] = "App\\".$bundleName."\\Entity\\".$className;
//        $mapper['entityNamespace'] = "App\\".$bundleName."\\Entity";
//        $mapper['bundleName'] = $bundleName;
//        $mapper['displayName'] = $displayName . ", class: [" . $className . "]";
        $this->mapper = $params['mapper'];
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value']);

        //ListType($this->params, $this->mapper)
        $builder->add('list', ListType::class, array(
            'form_custom_value' => $this->params,
            'form_custom_value_mapper' => $this->mapper,
            'data_class' => $this->mapper['fullClassName'],
            'label' => false
        ));


//        $builder->add('category',null,array(
//            'label' => "Category:",
//            'required' => false,
//            'attr' => array('class'=>'form-control', 'maxlength'=>"255"),
//        ));

        $builder->add('categoryTags', EntityType::class, array(
            'class' => AntibodyCategoryTagList::class,
            //'choice_label' => 'getTreeName',
            'label'=>'Antibody Category Tag(s):',
            'required'=> false,
            'multiple' => true,
            'attr' => array('class'=>'combobox combobox-width'),
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

        $builder->add('altname',null,array(
            'label' => "Alternative Name:",
            'required' => false,
            'attr' => array('class'=>'form-control', 'maxlength'=>"255"),
        ));

        $builder->add('company',null,array(
            'label' => "Company:",
            'required' => false,
            'attr' => array('class'=>'form-control'),
        ));

        $builder->add('catalog',null,array(
            'label' => "Catalog:",
            'required' => false,
            'attr' => array('class'=>'form-control'),
        ));

        $builder->add('lot',null,array(
            'label' => "Lot:",
            'required' => false,
            'attr' => array('class'=>'form-control'),
        ));

        $builder->add('igconcentration',null,array(
            'label' => "ig Concentration:",
            'required' => false,
            'attr' => array('class'=>'form-control'),
        ));

        $builder->add('clone',null,array(
            'label' => "Clone:",
            'required' => false,
            'attr' => array('class'=>'form-control'),
        ));

        $builder->add('host',null,array(
            'label' => "Host:",
            'required' => false,
            'attr' => array('class'=>'form-control'),
        ));

        $builder->add('reactivity',null,array(
            'label' => "Reactivity:",
            'required' => false,
            'attr' => array('class'=>'form-control'),
        ));

        $builder->add('control',null,array(
            'label' => "Control:",
            'required' => false,
            'attr' => array('class'=>'form-control'),
        ));

        $builder->add('protocol',null,array(
            'label' => "Protocol:",
            'required' => false,
            'attr' => array('class'=>'form-control'),
        ));

        $builder->add('retrieval',null,array(
            'label' => "Retrieval:",
            'required' => false,
            'attr' => array('class'=>'form-control'),
        ));

        $builder->add('dilution',null,array(
            'label' => "Dilution:",
            'required' => false,
            'attr' => array('class'=>'form-control'),
        ));

        $builder->add('storage',null,array(
            'label' => "Storage:",
            'required' => false,
            'attr' => array('class'=>'form-control'),
        ));

        $builder->add('comment',null,array(
            'label' => "Comment:",
            'required' => false,
            'attr' => array('class'=>'form-control textarea'),
        ));

        $builder->add('comment1',null,array(
            'label' => "Additional Comment 1:",
            'required' => false,
            'attr' => array('class'=>'form-control textarea'),
        ));

        $builder->add('comment2',null,array(
            'label' => "Additional Comment 2:",
            'required' => false,
            'attr' => array('class'=>'form-control textarea'),
        ));

        $builder->add('datasheet',null,array(
            'label' => "Datasheet:",
            'required' => false,
            'attr' => array('class'=>'form-control textarea'),
        ));

//            $builder->add('pdf',null,array(
//                'label' => "Pdf link:",
//                'required' => false,
//                'attr' => array('class'=>'form-control'),
//            ));

        $builder->add('documents', CollectionType::class, array(
            'entry_type' => DocumentType::class,
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__documentsid__',
        ));


        $builder->add('inventory',null,array(
            'label' => "Inventory Stock:",
            'required' => false,
            'attr' => array('class'=>'form-control'),
        ));
        $builder->add('unitPrice',null,array(
            'label' => "Unit Price:",
            'required' => false,
            'attr' => array('class'=>'form-control'),
        ));
        $builder->add('tissueType',null,array(
            'label' => "Tissue Type:",
            'required' => false,
            'attr' => array('class'=>'form-control'),
        ));
        $builder->add('visualInfos', CollectionType::class, array(
            'entry_type' => VisualInfoType::class,
            'label' => false,
            'required' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__visualinfos__',
        ));
//        $builder->add('openToPublic', null, array(
//            'label' => "Open to public:",
//            'required' => false,
//            'attr' => array('class' => 'form-control'),
//        ));
        $builder->add('openToPublic', CheckboxType::class, array(
            'label' => 'Open to public:',
            'required' => false,
            'attr' => array('style' => 'width: 20px; height: 20px;')
        ));


        //Buttons
        if( $this->params['cycle'] === "new" ) {
            $builder->add('save', SubmitType::class, array(
                'label' => 'Save',
                'attr' => array('class' => 'btn btn-warning')
            ));
        }
        if( $this->params['cycle'] === "edit" ) {
            $builder->add('edit', SubmitType::class, array(
                'label' => 'Update',
                'attr' => array('class' => 'btn btn-warning')
            ));
        }

        $builder->add('associates', EntityType::class, array(
            'class' => AntibodyList::class,
            'label' =>'Associated Antibodies:',
            'required' => false,
            'multiple' => true,
            'by_reference' => false,
            'attr' => array('class'=>'combobox combobox-width'),
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
//        $builder->add('associates', null, array(
//            'label' => 'Associated Antibodies:',
//            'multiple' => true,
//            'required' => false,
//            'attr' => array('class'=>'combobox')
//        ));
//        $builder->add('myAssociates', EntityType::class, array(
//            'class' => AntibodyList::class,
//            'label' => 'Associated Antibodies:', //'My Associates:',
//            'required'=> false,
//            'multiple' => true,
//            'attr' => array('class'=>'combobox combobox-width'),
//            'query_builder' => function(EntityRepository $er) {
//                return $er->createQueryBuilder('list')
//                    ->where("(list.type = :typedef OR list.type = :typeadd)")
//                    ->orderBy("list.orderinlist","ASC")
//                    ->setParameters( array(
//                        'typedef' => 'default',
//                        'typeadd' => 'user-added',
//                    ));
//            },
//        ));
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\TranslationalResearchBundle\Entity\AntibodyList',
            'form_custom_value' => null
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'oleg_translationalresearchbundle_antibody';
    }


}
