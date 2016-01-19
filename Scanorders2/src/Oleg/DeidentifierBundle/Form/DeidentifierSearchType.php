<?php

namespace Oleg\DeidentifierBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class DeidentifierSearchType extends AbstractType
{

    private $params;

    public function __construct( $params=null )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {                              

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
            'attr' => array('class'=>'form-control form-control-modif accession-mask'), //submit-on-enter-field
        ));

        $builder->add('institution', 'entity', array(
            'label' => 'Organizational Group (Institutional PHI Scope):',
            'mapped' => false,
            'property' => 'getNodeNameWithRoot',
            'required' => true,
            'multiple' => false,
            'empty_value' => false,
            'class' => 'OlegUserdirectoryBundle:Institution',
            'choices' => $this->params['permittedInstitutions'],
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

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection' => false,
        ));
    }

    public function getName()
    {
        return null;
        return 'deidentifier_search_box';
    }
}
