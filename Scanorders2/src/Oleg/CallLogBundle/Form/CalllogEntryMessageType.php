<?php

namespace Oleg\CallLogBundle\Form;

use Doctrine\Common\Collections\ArrayCollection;
use Oleg\UserdirectoryBundle\Form\FormNode\FormNodeType;
use Oleg\UserdirectoryBundle\Form\InstitutionType;
use Oleg\UserdirectoryBundle\Form\FormNode\MessageCategoryFormNodeType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Doctrine\ORM\EntityRepository;


class CalllogEntryMessageType extends AbstractType
{

    protected $entity;
    protected $params;

    public function __construct( $params=null, $entity=null )
    {
        if( $params ) $this->params = $params;
        if( $entity ) $this->entity = $entity;
    }
        
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {


        $builder->add('addPatientToList', 'checkbox', array(
            'label' => 'Add patient to the list:',
            'required' => false,
            'attr' => array('class' => 'form-control')
        ));


        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $message = $event->getData();
            $form = $event->getForm();

            $label = 'List Title:';

            $patientLists = $this->params['em']->getRepository('OlegOrderformBundle:PatientListHierarchy')->findAll();
            if( count($patientLists) > 0 ) {
                $patientListId = $patientLists[0]->getId();
            } else {
                $patientListId = null;
            }

            if( $this->params['cycle'] != "new" && $message ) {
                $calllogEntryMessage = $message->getCalllogEntryMessage();
                if( $message->getId() && $message->getCalllogEntryMessage() ) {
                    $patientListHierarchyNode = $this->params['em']->getRepository('OlegOrderformBundle:PatientListHierarchy')->findBy(array(
                        'entityNamespace' => $calllogEntryMessage->getEntityNamespace(),
                        'entityName' => $calllogEntryMessage->getEntityName(),
                        'entityId' => $calllogEntryMessage->getEntityId(),
                    ));
                    if( $patientListHierarchyNode ) {
                        $patientListId = $patientListHierarchyNode->getId();
                    }
                }
            }

            $form->add('patientList', 'employees_custom_selector', array(
                'label' => $label,
                'required' => true,
                'data' => $patientListId,
                'attr' => array(
                    'class' => 'ajax-combobox-compositetree show-as-single-node ajax-combobox-patientList', //show-as-single-node data-compositetree-exclusion-all-others
                    'type' => 'hidden',
                    'data-compositetree-bundlename' => 'OrderformBundle',
                    'data-compositetree-classname' => 'PatientListHierarchy',
                    'data-label-prefix' => '',
                    'data-compositetree-types' => 'default,user-added',
                ),
                'classtype' => 'patientList'
            ));
        });


    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\CalllogEntryMessage'
        ));
    }

    public function getName()
    {
        return 'oleg_calllogformbundle_calllogentrymessagetype';
    }




    //BELOW NOT USED
    public function addFormNodes( $form, $formHolder, $params ) {

        if( !$formHolder ) {
            return $form;
        }

        $rootFormNode = $formHolder->getFormNode();
        if( !$rootFormNode ) {
            return $form;
        }

        $form = $this->addFormNodeRecursively($form,$rootFormNode,$params);

        return $form;
    }


    public function addFormNodeRecursively( $form, $formNode, $params ) {

        //echo "formNode=".$formNode."<br>";
        $children = $formNode->getChildren();
        if( $children ) {

            foreach( $children as $childFormNode ) {
                $this->addFormNodeByType($form,$childFormNode,$params);
                $this->addFormNodeRecursively($form,$childFormNode,$params);
            }

        } else {
            $this->addFormNodeByType($form,$formNode,$params);
        }

    }

    public function addFormNodeByType( $form, $formNode, $params ) {

        $formNodeType = $formNode->getObjectType()."";
        //echo "formNodeType=".$formNodeType."<br>";

        if( $formNodeType == "Form" ) {
            echo "added Form <br>";
            $form->add('formFormNode',null,array(
                'label' => $formNode->getName()."",
                'mapped' => false
            ));
        }

        if( $formNodeType == "Form Section" ) {
            echo "added Section <br>";
            $form->add('sectionFormNode',null,array(
                'label' => $formNode->getName()."",
                'mapped' => false
            ));
        }

        if( $formNodeType == "Form Field - Free Text" ) {
            echo "added text <br>";
            $form->add('formNode','text',array(
                'label' => $formNode->getName()."",
                'mapped' => false,
                'attr' => array('class' => 'form-control textarea')
            ));
        }

    }

}
