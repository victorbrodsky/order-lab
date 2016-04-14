<?php

namespace Oleg\VacReqBundle\Form;


use Oleg\UserdirectoryBundle\Form\GeoLocationType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;


class VacReqRequestVacationType extends VacReqRequestBaseType {

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        parent::buildForm($builder,$options);

        $builder->add('numberOfDays', null, array(
            'label' => 'Vacation Days Requested (Please do not include holidays):',
            'attr' => array('class'=>'form-control vacreq-numberOfDays')
        ));

    }



    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\VacReqBundle\Entity\VacReqRequestVacation',
        ));
    }

    public function getName()
    {
        return 'oleg_vacreqbundle_request_vacation';
    }
}
