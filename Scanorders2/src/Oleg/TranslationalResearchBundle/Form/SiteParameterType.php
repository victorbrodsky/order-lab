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
            'label' => "Email Notification Body:",
            'required' => false,
            'attr' => array('class' => 'textarea form-control')
        ));

//        $builder->add('transresLogo', null, array(
//            'label' => "Logo:",
//            'required' => false,
//            'attr' => array('class' => 'textarea form-control')
//        ));
//        $builder->add('transresLogo', null, array(
//            //'entry_type' => DocumentType::class,
//            'label' => 'Logo:',
//            'required' => false,
//        ));
        $builder->add('transresLogo', DocumentType::class, array(
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\Document',
            'label' => false
        ));
//        $builder->add('transresLogo', DocumentType::class, array(
//            'form_custom_value' => $this->params,
//            'required' => false,
//            'label' => false
//        ));


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
