<?php
/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 11/9/2016
 * Time: 9:58 AM
 */

namespace Oleg\UserdirectoryBundle\Form;


use Oleg\UserdirectoryBundle\Form\FormNode\FormNodeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;


class MessageCategoryFormNodeType extends FormNodeType
{

    protected $params;

    public function __construct( $params )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {


        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $messageCategory = $event->getData();
            $form = $event->getForm();

            $label = null;
            $mapper = array(
                'prefix' => "Oleg",
                'className' => "MessageCategory",
                'bundleName' => "OrderformBundle",
                'organizationalGroupType' => "MessageTypeClassifiers"
            );

            if ($messageCategory) {
                $label = $this->params['em']->getRepository('OlegOrderformBundle:MessageCategory')->getLevelLabels($messageCategory, $mapper);
            }

            if (!$label) {
                $label = $this->params['em']->getRepository('OlegOrderformBundle:MessageCategory')->getLevelLabels(null, $mapper) . ":";
            }

            //echo "show defaultInstitution label=".$label."<br>";

            $form->add('messageCategory', 'employees_custom_selector', array(
                'label' => $label,
                'required' => false,
                'read_only' => true,
                'attr' => array(
                    'class' => 'ajax-combobox-compositetree combobox-compositetree-read-only-exclusion', //combobox-compositetree-readonly-parent
                    'type' => 'hidden',
                    'data-compositetree-bundlename' => 'OrderformBundle',
                    'data-compositetree-classname' => 'MessageCategory',
                    'data-label-prefix' => '',
                    //'data-readonly-parent-level' => '2', //readonly all children from level 2 up (including this level)
                    'data-read-only-exclusion-level' => '2', //readonly will be disable for all levels after indicated level
                ),
                'classtype' => 'institution'
            ));

            //add form node fields
            $form = $this->addFormNodes($form,$messageCategory,$this->params);

        });


    }



    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\MessageCategory',
            //'csrf_protection' => false
            //'allow_extra_fields' => true
        ));
    }

    public function getName()
    {
        return 'oleg_userdirectorybundle_messagecategoryformnodetype';
    }
}

