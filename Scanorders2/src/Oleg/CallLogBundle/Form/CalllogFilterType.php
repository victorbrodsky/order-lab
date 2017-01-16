<?php

namespace Oleg\CallLogBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CalllogFilterType extends AbstractType
{

    private $params;

    public function __construct( $params=null )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('startDate', 'datetime', array(
            'label' => false, //'Start Date',
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'attr' => array('class'=>'datepicker form-control submit-on-enter-field', 'placeholder'=>'Start Date'), //'title'=>'Start Year', 'data-toggle'=>'tooltip',
        ));

        $builder->add('endDate', 'datetime', array(
            'label' => false, //'Start Date',
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'attr' => array('class'=>'datepicker form-control submit-on-enter-field', 'placeholder'=>'End Date'), //'title'=>'End Year', 'data-toggle'=>'tooltip',
        ));

        $builder->add('messageCategory', 'choice', array(
            'label' => false,
            'required' => false,
            'choices' => $this->params['messageCategories'],
            'attr' => array('class' => 'combobox submit-on-enter-field', 'placeholder' => "Message Type"),
        ));
        
        $builder->add('search', 'text', array(
            'max_length'=>200,
            'required'=>false,
            'label' => false,
            'attr' => array('class'=>'form-control submit-on-enter-field', 'placeholder' => "MRN or Last Name"),
        ));

        $builder->add('author', 'entity', array(
            'class' => 'OlegUserdirectoryBundle:User',
            'label' => false,
            'required' => false,
            'property' => 'getUsernameOptimal',
            'attr' => array('class' => 'combobox combobox-width', 'placeholder' => "Author"),
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('u')
                    ->leftJoin("u.infos", "infos")
                    ->leftJoin("u.employmentStatus", "employmentStatus")
                    ->leftJoin("employmentStatus.employmentType", "employmentType")
                    ->andWhere("(employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL)")
                    ->andWhere("(u.testingAccount = 0 OR u.testingAccount IS NULL)")
                    ->andWhere("(u.keytype IS NOT NULL AND u.primaryPublicUserId != 'system')")
                    ->orderBy("infos.displayName","ASC");
                //->where('u.roles LIKE :roles OR u=:user')
                //->setParameters(array('roles' => '%' . 'ROLE_SCANORDER_ORDERING_PROVIDER' . '%', 'user' => $this->params['user']));
            },
        ));

        $builder->add('referringProvider', 'entity', array(
            'class' => 'OlegUserdirectoryBundle:User',
            'label' => false,
            'required' => false,
            'property' => 'getUsernameOptimal',
            'attr' => array('class' => 'combobox combobox-width', 'placeholder' => "Referring Provider"),
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('u')
                    ->leftJoin("u.infos", "infos")
                    ->leftJoin("u.employmentStatus", "employmentStatus")
                    ->leftJoin("employmentStatus.employmentType", "employmentType")
                    ->andWhere("(employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL)")
                    ->andWhere("(u.testingAccount = 0 OR u.testingAccount IS NULL)")
                    ->andWhere("(u.keytype IS NOT NULL AND u.primaryPublicUserId != 'system')")
                    ->orderBy("infos.displayName","ASC");
                //->where('u.roles LIKE :roles OR u=:user')
                //->setParameters(array('roles' => '%' . 'ROLE_SCANORDER_ORDERING_PROVIDER' . '%', 'user' => $this->params['user']));
            },
        ));

        $builder->add('specialty', 'entity', array(
            'class' => 'OlegUserdirectoryBundle:HealthcareProviderSpecialtiesList',
            'label' => false,
            'required' => false,
            'attr' => array('class' => 'combobox', 'placeholder' => "Specialty"),
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('u')
                    ->orderBy("u.orderinlist","ASC");
            },
        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection' => false,
        ));
    }

    public function getName()
    {
        return 'filter';
    }
}
