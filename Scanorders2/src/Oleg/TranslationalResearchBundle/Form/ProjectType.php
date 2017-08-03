<?php

namespace Oleg\TranslationalResearchBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProjectType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //$builder->add('createDate')->add('updateDate')->add('status')->add('title')->add('irbNumber')->add('startDate')->add('expirationDate')
        //->add('funded')->add('fundedAccountNumber')->add('description')->add('budgetSummary')->add('totalCost')->add('projectType')
        //->add('biostatisticalComment')->add('administratorComment')->add('primaryReviewerComment')->add('submitter')->add('updateUser')
        //->add('principalInvestigators')->add('coInvestigators')->add('pathologists')->add('irbSubmitter')->add('contact');

        $builder->add('createDate','date',array(
            'widget' => 'single_text',
            'label' => "Create Date:",
            'read_only' => true,
            'format' => 'MM/dd/yyyy',
            'attr' => array('class' => 'datepicker form-control'),
            'required' => false,
        ));

        $builder->add('updateDate','date',array(
            'widget' => 'single_text',
            'label' => "Update Date:",
            'read_only' => true,
            'format' => 'MM/dd/yyyy',
            'attr' => array('class' => 'datepicker form-control'),
            'required' => false,
        ));

//        $builder->add( 'submitter', null, array(
//            'label'=> "Created By:",
//            'read_only' => true,
//        ));

        $builder->add( 'updateUser', null, array(
            'label'=> "Updated By:",
            'read_only' => true,
        ));


        $builder->add('status',null, array(
            'label' => 'Status:',
            'read_only' => true,
            'required' => false,
            'attr' => array('class' => 'form-control'),
        ));

        $builder->add('title',null,array(
            'required' => false,
            'label'=>"Project Title:",
            'attr' => array('class'=>'textarea form-control')
        ));

        $builder->add('irbNumber',null, array(
            'label' => 'IRB Number:',
            'read_only' => true,
            'required' => false,
            'attr' => array('class' => 'form-control'),
        ));

        $builder->add('startDate','date',array(
            'widget' => 'single_text',
            'label' => "Project Start Date:",
            'read_only' => true,
            'format' => 'MM/dd/yyyy',
            'attr' => array('class' => 'datepicker form-control'),
            'required' => false,
        ));

        $builder->add('expirationDate','date',array(
            'widget' => 'single_text',
            'label' => "Project Expiration Date:",
            'read_only' => true,
            'format' => 'MM/dd/yyyy',
            'attr' => array('class' => 'datepicker form-control'),
            'required' => false,
        ));

        $builder->add('funded','checkbox',array(
            'required' => false,
            'label'=>"Is this Research Project Funded:",
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('fundedAccountNumber',null, array(
            'label' => 'If funded, please provide account number:',
            'required' => false,
            'attr' => array('class' => 'form-control'),
        ));

        $descriptionLabel =
            "Please provide a brief description of the project to include background information,
            purpose and objective, and a methodology section stating a justification for
            the size and scope of the project (<250 words). The breadth of information
            should be adequate for a scientific committee to understand and assess the value of the research.";
        $builder->add('description',null,array(
            'label' => $descriptionLabel,
            'attr' => array('class'=>'textarea form-control')
        ));

        $builder->add('budgetSummary',null,array(
            'label' => "Provide a Detailed Budget Outline/Summary:",
            'attr' => array('class'=>'textarea form-control')
        ));

        $builder->add('totalCost',null, array(
            'label' => 'Estimated Total Costs:',
            'required' => false,
            'attr' => array('class' => 'form-control'),
        ));

        $builder->add('projectType',null, array(
            'label' => 'Project Type:',
            'required' => false,
            'attr' => array('class' => 'form-control'),
        ));

        //->add('biostatisticalComment')->add('administratorComment')->add('primaryReviewerComment')->add('submitter')->add('updateUser')
        $builder->add('biostatisticalComment',null,array(
            'label' => "Biostatistical Comment:",
            'attr' => array('class'=>'textarea form-control')
        ));

        $builder->add('administratorComment',null,array(
            'label' => "Administrator Comment:",
            'attr' => array('class'=>'textarea form-control')
        ));

        $builder->add('primaryReviewerComment',null,array(
            'label' => "Primary Reviewer Comment:",
            'attr' => array('class'=>'textarea form-control')
        ));

        $builder->add( 'principalInvestigators', 'entity', array(
            'class' => 'OlegUserdirectoryBundle:User',
            'label'=> "Submitter:",
            'required'=> false,
            'multiple' => true,
            'attr' => array('class'=>'combobox combobox-width'),
            'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('list')
                    ->leftJoin("list.employmentStatus", "employmentStatus")
                    ->leftJoin("employmentStatus.employmentType", "employmentType")
                    ->where("employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL")
                    ->leftJoin("list.infos", "infos")
                    ->orderBy("infos.displayName","ASC");
            },
        ));



    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\TranslationalResearchBundle\Entity\Project'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'oleg_translationalresearchbundle_project';
    }


}
