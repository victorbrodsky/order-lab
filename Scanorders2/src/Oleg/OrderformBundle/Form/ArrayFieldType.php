<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;



class ArrayFieldType extends AbstractType
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
        $builder->add('id', 'hidden');

        if( $this->params && $this->params['cycle'] == "show") {
            $builder->add('creationdate');
            $builder->add('provider');
        }

        if( $this->params && array_key_exists('datastructure', $this->params) && $this->params['datastructure'] == 'datastructure-patient') {

            $builder->add('provider','hidden');
            $builder->add('source','hidden');

            //$builder->add('provider');
            //$builder->add('source');

//            $user = null;
//            if( $this->params['container'] ) {
//                $user = $this->params['container']->get('security.context')->getToken()->getUser();
//            }
//            $builder->add('provider','hidden',array(
//                'empty_data'  => $user
//            ));
//
//            $source = null;
//            if( $this->params['em'] && $this->params['sitename'] ) {
//                $securityUtil = $this->params['container']->get('order_security_utility');
//                $source = $securityUtil->getDefaultSourceSystem($this->params['sitename']);
//            }
//            $builder->add('source','hidden',array(
//                'empty_data'  => $source
//            ));

            //$builder->add('source','hidden');

            $builder->add('status', 'choice', array(
                'choices'   => array(
                    'valid' => 'valid',
                    'invalid' => 'invalid'
                ),
                'label' => "Status:",
                'required' => true,
                'attr' => array('class' => 'combobox combobox-no-width other-status'),
            ));

            ////////////////// pre set newly added fields: provider, source, status='invalid' //////////////////
            if(1) {
                $builder->get('provider')
                    ->addModelTransformer(new CallbackTransformer(
                        //original from DB to form: Object to Id
                            function ($originalData) {
                                //echo "originalData=".$originalData."<br>";
                                if (is_object($originalData) && $originalData->getId()) { //object
                                    return $originalData->getId();
                                }
                                return $originalData; //id
                            },
                            //reverse from form to DB: Id to Object
                            function ($submittedData) {
                                //echo "submittedData=".$submittedData."<br>";
                                if (is_object($submittedData) && $submittedData->getId()) { //object
                                    return $submittedData;
                                }
                                if ($submittedData) { //id
                                    $submittedObject = $this->params['em']->getRepository('OlegUserdirectoryBundle:User')->find($submittedData);
                                    return $submittedObject;
                                }
                                return null;
                            }
                        )
                    );

                $builder->get('source')
                    ->addModelTransformer(new CallbackTransformer(
                        //original from DB to form: Object to Id
                            function ($originalData) {
                                //echo "originalData=".$originalData."<br>";
                                if (is_object($originalData) && $originalData->getId()) { //object
                                    return $originalData->getId();
                                }
                                return $originalData; //id
                            },
                            //reverse from form to DB: Id to Object
                            function ($submittedData) {
                                //echo "submittedData=".$submittedData."<br>";
                                if (is_object($submittedData) && $submittedData->getId()) { //object
                                    return $submittedData;
                                }
                                if ($submittedData) { //id
                                    $submittedObject = $this->params['em']->getRepository('OlegUserdirectoryBundle:SourceSystemList')->find($submittedData);
                                    return $submittedObject;
                                }
                                return null;
                            }
                        )
                    );
            }
            if(1) {
                $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                    $otherObject = $event->getData();
                    $form = $event->getForm();

                    //echo "otherObject:<br>";
                    //print_r($otherObject);
                    //echo "<br>";

                    if ($otherObject) {
                        //echo "form provider=(" . $otherObject['provider'] . ")!!!!!!!!!<br>";
                        if( !$otherObject['id'] && !$otherObject['provider'] && $this->params['container'] ) {
                            $user = $this->params['container']->get('security.context')->getToken()->getUser();
                            //echo $otherObject['id'] . ": set provider=" . $user . " !!!!!!!!!<br>";
                            $otherObject['provider'] = $user->getId();
                        }

                        if (!$otherObject['id'] && !$otherObject['source'] && $this->params['em'] && $this->params['sitename']) {
                            //get source
                            $securityUtil = $this->params['container']->get('order_security_utility');
                            $source = $securityUtil->getDefaultSourceSystem($this->params['sitename']);
                            //echo $otherObject['id'] . ": set source=" . $source . "<br>";
                            $otherObject['source'] = $source->getId();
                        }

                        $event->setData($otherObject);

                    }
                });
            }

            if(0) {
                $formProviderModifier = function (FormInterface $form, $provider = null) {
                    echo "formProviderModifier provider=(ID#" . $provider->getId() . "): " . $provider . "<br>";
//                    $form->add('provider', 'hidden', array(
//                        'empty_data' => $provider
//                    ));
                    $form->add('provider', 'entity', array(
                        'class'       => 'OlegUserdirectoryBundle:User',    //'AppBundle:Position',
                        'data'     => $provider->getId(),
                        'empty_data' => $provider->getId()
                    ));
                };
                $builder->get('provider')->addEventListener(
                    FormEvents::POST_SUBMIT,
                    function (FormEvent $event) use ($formProviderModifier) {
                        // It's important here to fetch $event->getForm()->getData(), as
                        // $event->getData() will get you the client data (that is, the ID)
                        $thisProvider = $event->getForm()->getData();
                        //echo "provider=".$provider."<br>";

                        $provider = null;
                        if ($this->params['container'] && !$thisProvider) {
                            //echo "FormEvent provider=".$thisProvider."<br>";
                            $provider = $this->params['container']->get('security.context')->getToken()->getUser();
                            $formProviderModifier($event->getForm()->getParent(), $provider);
                        }

                        // since we've added the listener to the child, we'll have to pass on
                        // the parent to the callback functions!
                        //$formProviderModifier($event->getForm()->getParent(), $provider);
                    }
                );

                $formSourceModifier = function (FormInterface $form, $source = null) {
                    $form->add('source', 'hidden', array(
                        'empty_data' => $source
                    ));
                };
                $builder->get('source')->addEventListener(
                    FormEvents::POST_SUBMIT,
                    function (FormEvent $event) use ($formSourceModifier) {
                        // It's important here to fetch $event->getForm()->getData(), as
                        // $event->getData() will get you the client data (that is, the ID)
                        $thisSource = $event->getForm()->getData();
                        //echo "source=".$source."<br>";

                        $source = null;
                        if ($this->params['sitename'] && !$thisSource) {
                            //echo "FormEvent source=".$thisSource."<br>";
                            $securityUtil = $this->params['container']->get('order_security_utility');
                            $source = $securityUtil->getDefaultSourceSystem($this->params['sitename']);
                        }

                        // since we've added the listener to the child, we'll have to pass on
                        // the parent to the callback functions!
                        $formSourceModifier($event->getForm()->getParent(), $source);
                    }
                );
            }
            ////////////////// EOF pre set newly added fields: provider, source, status='invalid' //////////////////

        }

    }

//    public function setFormViewTransformer($originalData) {
//        //echo "originalData=".$originalData."<br>";
//        if (is_object($originalData) && $originalData->getId()) { //object
//            return $originalData->getId();
//        }
//        return $originalData; //id
//    }
//    public function setSubmitTransformer($submittedData,$bunldeName) {
//        //echo "submittedData=".$submittedData."<br>";
//        if (is_object($submittedData) && $submittedData->getId()) { //object
//            return $submittedData;
//        }
//        if ($submittedData) { //id
//            $submittedObject = $this->params['em']->getRepository($bunldeName)->find($submittedData);
//            return $submittedObject;
//        }
//        return null;
//    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'inherit_data' => true
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_arrayfieldtype';
    }
}
