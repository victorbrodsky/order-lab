<?php

namespace Oleg\UserdirectoryBundle\Form;



use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;


class EmploymentStatusType extends AbstractType
{

    protected $params;
    protected $entity;

    public function __construct( $params=null, $entity = null )
    {
        $this->params = $params;
        $this->entity = $entity;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        if( $this->params['currentUser'] == true ) {
            $readonly = true;
        } else {
            $readonly = false;
        }

        $builder->add('hireDate',null,array(
            'read_only' => $readonly,
            'label'=>"Date of Hire:",
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM-dd-yyyy',
            'attr' => array('class' => 'datepicker form-control')
        ));

        $builder->add('employmentType',null,array(
            'read_only' => $readonly,
            'label'=>"Employee Type:",
            'required' => false,
            'attr' => array('class' => 'combobox combobox-width')
        ));

        $builder->add('terminationDate',null,array(
            'read_only' => $readonly,
            'label'=>"End of Employment Date:",
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM-dd-yyyy',
            'attr' => array('class' => 'datepicker form-control user-expired-end-date')
        ));

        if( $readonly ) {
            $attr = array('class'=>'combobox combobox-width', 'readonly'=>'readonly');
        } else {
            $attr = array('class'=>'combobox combobox-width');
        }
        $builder->add( 'terminationType', 'entity', array(
            'disabled' => ($this->params['read_only'] ? true : false),
            'class' => 'OlegUserdirectoryBundle:EmploymentTerminationType',
            'property' => 'name',
            'label'=>'Type of End of Employment:',
            'required'=> false,
            'multiple' => false,
            'attr' => $attr,
            'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('list')
                        ->where("list.type = :typedef OR list.type = :typeadd")
                        ->orderBy("list.orderinlist","ASC")
                        ->setParameters( array(
                            'typedef' => 'default',
                            'typeadd' => 'user-added',
                        ));
                },
        ));

        //do not show reason for user himself
        if( $this->params['currentUser'] == false ) {
            $builder->add('terminationReason', null, array(
                'label' => 'Reason for End of Employment:',
                'attr' => array('class'=>'textarea form-control')
            ));
        }


    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\EmploymentStatus',
        ));
    }

    public function getName()
    {
        return 'oleg_userdirectorybundle_employmentstatus';
    }
}
