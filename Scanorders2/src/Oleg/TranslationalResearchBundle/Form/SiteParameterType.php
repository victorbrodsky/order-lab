<?php

namespace Oleg\TranslationalResearchBundle\Form;

use Doctrine\ORM\EntityRepository;
use Oleg\UserdirectoryBundle\Form\DocumentType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SiteParameterType extends AbstractType
{

    protected $params;

    public function formConstructor( $params ) {
        $this->params = $params;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value']);

        $builder->add('transresFromHeader', null, array(
            'label' => "Invoice 'From' Address:",
            'required' => false,
            'attr' => array('class' => 'textarea form-control')
        ));

        $builder->add('transresFooter', null, array(
            'label' => "Invoice Footer:",
            'required' => false,
            'attr' => array('class' => 'textarea form-control')
        ));

        $builder->add('transresNotificationEmail', null, array(
            'label' => "Email Notification Body when Invoice PDF is sent to PI:",
            'required' => false,
            'attr' => array('class' => 'textarea form-control')
        ));

        $builder->add('transresNotificationEmailSubject', null, array(
            'label' => "Email Notification Subject when Invoice PDF is sent to PI:",
            'required' => false,
            'attr' => array('class' => 'textarea form-control')
        ));

        $builder->add('invoiceSalesperson', EntityType::class, array(
            'class' => 'OlegUserdirectoryBundle:User',
            'label' => "Invoice Salesperson:",
            //'disabled' => true,
            'required' => false,
            'multiple' => false,
            'attr' => array('class' => 'combobox combobox-width'),
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('list')
                    ->leftJoin("list.employmentStatus", "employmentStatus")
                    ->leftJoin("employmentStatus.employmentType", "employmentType")
                    ->where("employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL")
                    //->andWhere("list.roles LIKE '%ROLE_TRANSRES_%'")
                    ->leftJoin("list.infos", "infos")
                    ->orderBy("infos.displayName", "ASC");
            },
        ));

        $builder->add('transresLogos', CollectionType::class, array(
            'entry_type' => DocumentType::class,
            'label' => 'Logo(s):',
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__documentsid__',
        ));

        $builder->add('requestCompletedNotifiedEmail', null, array(
            'label' => "Email Notification Body to the Request's PI when Request status is changed to 'Completed and Notified':",
            'required' => false,
            'attr' => array('class' => 'textarea form-control')
        ));

        $builder->add('requestCompletedNotifiedEmailSubject', null, array(
            'label' => "Email Notification Subject to the Request's PI when Request status is changed to 'Completed and Notified':",
            'required' => false,
            'attr' => array('class' => 'textarea form-control')
        ));

        $builder->add('accessionType', EntityType::class, array(
            'class' => 'OlegOrderformBundle:AccessionType',
            'label' => "Default Source System for Work Request Deliverables:",
            'required' => false,
            'multiple' => false,
            'choice_label' => 'getOptimalName',
            'attr' => array('class' => 'combobox combobox-width'),
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('list')
                    ->where("list.type = :typedef OR list.type = :typeadd")
                    ->orderBy("list.orderinlist","ASC")
                    ->setParameters( array(
                        'typedef' => 'default',
                        'typeadd' => 'user-added',
                    ));
            },
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

    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\TranslationalResearchBundle\Entity\TransResSiteParameters',
            'form_custom_value' => null
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'oleg_translationalresearchbundle_siteparameter';
    }


}
