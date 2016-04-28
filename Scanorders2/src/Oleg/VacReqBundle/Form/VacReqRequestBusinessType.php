<?php

namespace Oleg\VacReqBundle\Form;


use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;


class VacReqRequestBusinessType extends VacReqRequestBaseType {

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        parent::buildForm($builder,$options);


        $builder->add('expenses', 'text', array(
            'label' => 'Estimated Expenses:',
            'attr' => array('class'=>'form-control vacreq-expenses'),
            'read_only' => ($this->params['review'] ? true : false)
        ));

        $builder->add('description', 'textarea', array(
            'label' => 'Description:',
            'attr' => array('class'=>'textarea form-control vacreq-description'),
            'read_only' => ($this->params['review'] ? true : false)
        ));

        $builder->add('paidByOutsideOrganization', 'checkbox', array(
            'label' => 'Paid by Outside Organization:',
            'required' => false,
            //'attr' => array('class' => 'form-control'),
            'read_only' => ($this->params['review'] ? true : false)
        ));


    }



    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\VacReqBundle\Entity\VacReqRequestBusiness',
        ));
    }

    public function getName()
    {
        return 'oleg_vacreqbundle_request_business';
    }
}
