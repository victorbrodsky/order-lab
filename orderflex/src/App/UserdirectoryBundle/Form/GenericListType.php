<?php
/**
 * Copyright (c) 2017 Cornell University
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

namespace App\UserdirectoryBundle\Form;



use App\TranslationalResearchBundle\Entity\AntibodyCategoryTagList;
use App\TranslationalResearchBundle\Entity\AntibodyLabList;
use App\TranslationalResearchBundle\Entity\AntibodyPanelList;
use App\UserdirectoryBundle\Entity\AuthServerNetworkList;
use App\UserdirectoryBundle\Entity\CollaborationTypeList; //process.py script: replaced namespace by ::class: added use line for classname=CollaborationTypeList
use App\UserdirectoryBundle\Entity\HostedUserGroupList;
use App\UserdirectoryBundle\Entity\PermissionObjectList; //process.py script: replaced namespace by ::class: added use line for classname=PermissionObjectList
use App\UserdirectoryBundle\Entity\PermissionActionList; //process.py script: replaced namespace by ::class: added use line for classname=PermissionActionList
use App\UserdirectoryBundle\Entity\RoleAttributeList; //process.py script: replaced namespace by ::class: added use line for classname=RoleAttributeList
use App\UserdirectoryBundle\Entity\FellowshipSubspecialty; //process.py script: replaced namespace by ::class: added use line for classname=FellowshipSubspecialty
use App\UserdirectoryBundle\Entity\ResidencySpecialty; //process.py script: replaced namespace by ::class: added use line for classname=ResidencySpecialty
use App\UserdirectoryBundle\Entity\ResidencyTrackList; //process.py script: replaced namespace by ::class: added use line for classname=ResidencyTrackList
use App\UserdirectoryBundle\Entity\TransferStatusList;
use App\UserdirectoryBundle\Entity\User; //process.py script: replaced namespace by ::class: added use line for classname=User
use App\TranslationalResearchBundle\Entity\SpecialtyList; //process.py script: replaced namespace by ::class: added use line for classname=SpecialtyList
use App\TranslationalResearchBundle\Entity\WorkQueueList; //process.py script: replaced namespace by ::class: added use line for classname=WorkQueueList
use App\UserdirectoryBundle\Entity\SuiteList; //process.py script: replaced namespace by ::class: added use line for classname=SuiteList
use App\UserdirectoryBundle\Entity\RoomList; //process.py script: replaced namespace by ::class: added use line for classname=RoomList
use App\UserdirectoryBundle\Entity\BuildingList; //process.py script: replaced namespace by ::class: added use line for classname=BuildingList
use App\UserdirectoryBundle\Entity\FloorList; //process.py script: replaced namespace by ::class: added use line for classname=FloorList
use App\OrderformBundle\Entity\MessageTagTypesList; //process.py script: replaced namespace by ::class: added use line for classname=MessageTagTypesList
use App\DashboardBundle\Entity\VisualizationList; //process.py script: replaced namespace by ::class: added use line for classname=VisualizationList
use App\DashboardBundle\Entity\ChartTypeList; //process.py script: replaced namespace by ::class: added use line for classname=ChartTypeList
use App\DashboardBundle\Entity\DataSourceList; //process.py script: replaced namespace by ::class: added use line for classname=DataSourceList
use App\DashboardBundle\Entity\UpdateFrequencyList; //process.py script: replaced namespace by ::class: added use line for classname=UpdateFrequencyList
use App\UserdirectoryBundle\Entity\Roles; //process.py script: replaced namespace by ::class: added use line for classname=Roles
use App\DashboardBundle\Entity\ChartList;
use App\DashboardBundle\Entity\TopicList;
use App\TranslationalResearchBundle\Form\PriceType;
use App\TranslationalResearchBundle\Form\VisualInfoType;
use App\UserdirectoryBundle\Entity\SiteList;
use App\UserdirectoryBundle\Form\CustomType\CustomSelectorType;
use App\UserdirectoryBundle\Form\DataTransformer\DayMonthDateTransformer;
use App\VacReqBundle\Entity\VacReqApprovalTypeList;
use App\VacReqBundle\Entity\VacReqHolidayList;
use Doctrine\ORM\EntityRepository;
use App\TranslationalResearchBundle\Entity\RequestCategoryTypeList;
use App\UserdirectoryBundle\Entity\CompositeNodeInterface;
use App\UserdirectoryBundle\Entity\FormNode;
use App\UserdirectoryBundle\Entity\Institution;
use App\UserdirectoryBundle\Entity\PlatformListManagerRootList;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GenericListType extends AbstractType
{

    protected $params;
    protected $mapper;
    protected $where;


    public function formConstructor( $params, $mapper )
    {
        $this->params = $params;
        $this->mapper = $mapper;

        if( !array_key_exists('parentClassName', $this->mapper) ) {
            $this->mapper['parentClassName'] = $this->mapper['className'];
        }

    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options   
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value'],$options['form_custom_value_mapper']);

        //ListType($this->params, $this->mapper)
        $builder->add('list', ListType::class, array(
            'form_custom_value' => $this->params,
            'form_custom_value_mapper' => $this->mapper,
            'data_class' => $this->mapper['fullClassName'],
            'label' => false
        ));


        //tree classes: BaseCompositeNode (Institution, MessageCatergory, CommentTypeList), FellowshipSubspecialty
        if( method_exists($this->params['entity'],'getParent') ) {
            //echo "cycle=".$this->params['cycle']."<br>";
            if( $this->params['cycle'] == "show" ) {
                $attr = array('class' => 'combobox combobox-width', 'readonly'=>'readonly');
            } else {
                $attr = array('class' => 'combobox combobox-width');
            }
            $builder->add('parent',null,array(
                'label' => $this->mapper['parentClassName'].' (Parent):',
                'choice_label' => 'getTreeName',
                //'attr' => array('class' => 'combobox combobox-width')
                'attr' => $attr
            ));

        }

        //TODO: implement date transformer when year is not set (similar to publicationDate)
//        if( strtolower($this->mapper['className']) == strtolower("FellowshipSubspecialty") ) {
//            $builder->add('seasonYearStart',CustomSelectorType::class,array(
//                'label'=>'Application season start date (MM/DD) when the default fellowship application year for this fellowship changes to the following year (i.e. April 1st):',
//                'attr' => array('class'=>'form-control'),
//                //'attr' => array('class'=>'datepicker form-control datepicker-day-month allow-future-date'),
//                'classtype' => 'day_month_date_only'
//            ));
//            $builder->add('seasonYearEnd',CustomSelectorType::class,array(
//                'label'=>'Application season end date (MM/DD) when the default fellowship application year for this fellowship changes to the following year (i.e. March 31):',
//                'attr' => array('class'=>'form-control'),
//                //'attr' => array('class'=>'datepicker form-control datepicker-day-month allow-future-date'),
//                'classtype' => 'day_month_date_only'
//            ));
//        }
//        if( strtolower($this->mapper['className']) == strtolower("ResidencySpecialty") ) {
//            $builder->add('seasonYearStart',CustomSelectorType::class,array(
//                'label'=>'Application season start date (MM/DD) when the default residency application year for this residency changes to the following year (i.e. April 1st):',
//                'attr' => array('class'=>'form-control'),
//                //'attr' => array('class'=>'datepicker form-control datepicker-day-month allow-future-date'),
//                'classtype' => 'day_month_date_only'
//            ));
//            $builder->add('seasonYearEnd',CustomSelectorType::class,array(
//                'label'=>'Application season end date (MM/DD) when the default residency application year for this residency changes to the following year (i.e. March 31):',
//                'attr' => array('class'=>'form-control'),
//                //'attr' => array('class'=>'datepicker form-control datepicker-day-month allow-future-date'),
//                'classtype' => 'day_month_date_only'
//            ));
//        }

        $specialtyName = NULL;
        if( strtolower($this->mapper['className']) == strtolower("FellowshipSubspecialty") ) {
            $specialtyName = "fellowship";
        }
//        if( strtolower($this->mapper['className']) == strtolower("ResidencySpecialty") ) {
//            $specialtyName = "residency";
//        }
        if( strtolower($this->mapper['className']) == strtolower("ResidencyTrackList") ) {
            $specialtyName = "residency";

            $builder->add('duration',null,array(
                'label' => "Expected Duration (in years):",
                'required' => false,
                'attr' => array('class'=>'form-control'),
            ));
        }
//        if( 0 && $specialtyName ) {
//            $builder->add('seasonYearStart',CustomSelectorType::class,array(
//                'label'=>"Application season start date (MM/DD) when the default $specialtyName application year for this $specialtyName changes to the following year (i.e. April 1st):",
//                //'attr' => array('class'=>'form-control'),
//                //'attr' => array('class'=>'datepicker form-control datepicker-day-month allow-future-date'),
//                'widget' => 'choice',
//                'required' => false,
//                'classtype' => 'day_month_date_only'
//            ));
//            $builder->add('seasonYearEnd',CustomSelectorType::class,array(
//                'label'=>"Application season end date (MM/DD) when the default $specialtyName application year for this $specialtyName changes to the following year (i.e. March 31):",
//                //'attr' => array('class'=>'form-control'),
//                //'attr' => array('class'=>'datepicker form-control datepicker-day-month allow-future-date'),
//                'widget' => 'choice',
//                'required' => false,
//                'classtype' => 'day_month_date_only'
//            ));
//        }
        if( $specialtyName ) {
            $builder
                ->add(
                    $builder->create('seasonYearStart', null, [
                        'label'=>"Application season start date (MM/DD) when the default $specialtyName application year for this $specialtyName changes to the following year (i.e. April 1st):",
                        //'widget' => 'choice',
                        'required' => false,
                    ])
                        ->addViewTransformer(new DayMonthDateTransformer())
                );

            $builder
                ->add(
                    $builder->create('seasonYearEnd', null, [
                        'label'=>"Application season end date (MM/DD) when the default $specialtyName application year for this $specialtyName changes to the following year (i.e. March 31):",
                        //'widget' => 'choice',
                        'required' => false,
                    ])
                        ->addViewTransformer(new DayMonthDateTransformer())
                );
        }

        //TODO: make it as institutional tree?
        if( method_exists($this->params['entity'],'getInstitution') ) {

            $this->where = "list.type = :typedef OR list.type = :typeadd";

            //FellowshipSubspecialty
            if( strtolower($this->mapper['className']) == strtolower("FellowshipSubspecialty") ) {
                $this->where = "(list.type = :typedef OR list.type = :typeadd) AND list.level=1";
            }

            //echo "show institution<br>";

//            $builder->add('institution',EntityType::class,array(
//                'class' => 'AppUserdirectoryBundle:Institution',
//                'label' => "Institution:",
//                'choice_label' => "getTreeName",
//                'multiple' => false,
//                'attr' => array('class'=>'combobox combobox-width'),
//                'required' => false,
//            ));

            $builder->add( 'institution', EntityType::class, array(
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
                'class' => Institution::class,
                'choice_label' => 'getTreeName',
                'label'=>'Institution:',
                'required'=> false,
                'multiple' => false,
                'attr' => array('class'=>'combobox combobox-width'),
                'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('list')
                            ->leftJoin("list.children","children")
                            //->where("(list.type = :typedef OR list.type = :typeadd) AND list.level=1")
                            ->where($this->where)
                            ->orderBy("list.orderinlist","ASC")
                            ->setParameters( array(
                                'typedef' => 'default',
                                'typeadd' => 'user-added',
                            ));
                    },
            ));

//            ///////////////////////// tree node /////////////////////////
//            $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
//                $title = $event->getData();
//                $form = $event->getForm();
//
//                echo "2 show institution<br>";
//
//                $label = null;
//                if( $title ) {
//                    $institution = $title->getInstitution();
//                    if( $institution ) {
//                        $label = $this->params['em']->getRepository('AppUserdirectoryBundle:Institution')->getLevelLabels($institution) . ":";
//                    }
//                }
//                if( !$label ) {
//                    $label = $this->params['em']->getRepository('AppUserdirectoryBundle:Institution')->getLevelLabels(null) . ":";
//                }
//                echo "label=".$label."<br>";
//
//                $form->add('institution', 'employees_custom_selector', array(
//                    'label' => $label,
//                    'required' => false,
//                    //'attr' => array('class' => 'ajax-combobox-institution', 'type' => 'hidden'),
//                    'attr' => array(
//                        'class' => 'ajax-combobox-compositetree',
//                        'type' => 'hidden',
//                        'data-compositetree-bundlename' => 'UserdirectoryBundle',
//                        'data-compositetree-classname' => 'Institution'
//                    ),
//                    'classtype' => 'institution'
//                ));
//            });
//            ///////////////////////// EOF tree node /////////////////////////

        } //getInstitution

        if( method_exists($this->params['entity'],'getRoot') ) {
            $builder->add( 'root', null, array(
                'label'=>'Root:',
                //'disabled' => true,
                'attr' => array('class'=>'form-control', 'readonly'=>true),
            ));
        }
        if( method_exists($this->params['entity'],'getLft') ) {
            $builder->add( 'lft', null, array(
                'label'=>'Left:',
                //'disabled' => true,
                'attr' => array('class'=>'form-control', 'readonly'=>true),
            ));
        }
        if( method_exists($this->params['entity'],'getRgt') ) {
            $builder->add( 'rgt', null, array(
                'label'=>'Right:',
                //'disabled' => true,
                'attr' => array('class'=>'form-control', 'readonly'=>true),
            ));
        }

        if( method_exists($this->params['entity'],'getAccessionListTypes') ) {
            $builder->add( 'accessionListTypes', null, array(
                'label'=>'Accession List Types:',
                'attr' => array('class'=>'combobox combobox-width'),
            ));
        }


        if( method_exists($this->params['entity'],'getInstitutions') ) {
            //echo "add institutions <br>";
            $builder->add( 'institutions', EntityType::class, array(
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
                'class' => Institution::class,
                'choice_label' => 'getTreeName',
                'label'=>'Institutions:',
                'required'=> false,
                'multiple' => true,
                'attr' => array('class'=>'combobox combobox-width'),
                'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('list')
                            ->leftJoin("list.children","children")
                            ->where("list.type = :typedef OR list.type = :typeadd")
                            ->orderBy("list.orderinlist","ASC")
                            ->setParameters( array(
                                'typedef' => 'default',
                                'typeadd' => 'user-added',
                            ));
                    },
            ));
        }

        //Show Collaborations in the Institution object
        if( method_exists($this->params['entity'],'getCollaborations') ) {


            ///////////////////////// tree node /////////////////////////
            $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                $title = $event->getData();
                $form = $event->getForm();

                //check if this Institution is under "All Collaborations" tree
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
                $allCollaborationInst = $this->params['em']->getRepository(Institution::class)->findOneByAbbreviation("All Collaborations");
                if( $title->getRoot() != $allCollaborationInst->getRoot() ) {
                    return;
                }

                //echo "show Collaboration institutions<br>";

                $form->add( 'collaborationInstitutions', EntityType::class, array(
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
                    'class' => Institution::class,
                    'choice_label' => 'getTreeName',
                    'label'=>'Institutions:',
                    'required'=> false,
                    'multiple' => true,
                    'attr' => array('class'=>'combobox combobox-width'),
                    'query_builder' => function(EntityRepository $er) {
                            return $er->createQueryBuilder('list')
                                ->leftJoin("list.children","children")
                                ->where("list.type = :typedef OR list.type = :typeadd")
                                ->orderBy("list.orderinlist","ASC")
                                ->setParameters( array(
                                    'typedef' => 'default',
                                    'typeadd' => 'user-added',
                                ));
                        },
                ));

                $form->add( 'collaborationType', EntityType::class, array(
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:CollaborationTypeList'] by [CollaborationTypeList::class]
                    'class' => CollaborationTypeList::class,
                    'choice_label' => 'name',
                    'label'=>'Collaboration Type:',
                    'required'=> false,
                    'multiple' => false,
                    'attr' => array('class'=>'combobox combobox-width'),
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

            });
            ///////////////////////// EOF tree node /////////////////////////

//            $builder->add( 'collaborations', EntityType::class, array(
//                'class' => 'AppUserdirectoryBundle:Collaboration',
//                //'disabled' => true,
//                //'choice_label' => 'getTreeName',
//                'label'=>'Collaborations:',
//                'required'=> false,
//                'multiple' => true,
//                'attr' => array('class'=>'combobox combobox-width'),
//                'query_builder' => function(EntityRepository $er) {
//                        return $er->createQueryBuilder('list')
//                            //->leftJoin("list.children","children")
//                            ->where("list.type = :typedef OR list.type = :typeadd")
//                            ->orderBy("list.orderinlist","ASC")
//                            ->setParameters( array(
//                                'typedef' => 'default',
//                                'typeadd' => 'user-added',
//                            ));
//                    },
//            ));
        }

        //Collaboration
//        if( method_exists($this->params['entity'],'getCollaborationType') ) {
//            //echo "add institutions <br>";
//            $builder->add( 'collaborationType', EntityType::class, array(
//                'class' => 'AppUserdirectoryBundle:CollaborationTypeList',
//                'choice_label' => 'name',
//                'label'=>'Collaboration Type:',
//                'required'=> false,
//                'multiple' => false,
//                'attr' => array('class'=>'combobox combobox-width'),
//                'query_builder' => function(EntityRepository $er) {
//                        return $er->createQueryBuilder('list')
//                            ->where("list.type = :typedef OR list.type = :typeadd")
//                            ->orderBy("list.orderinlist","ASC")
//                            ->setParameters( array(
//                                'typedef' => 'default',
//                                'typeadd' => 'user-added',
//                            ));
//                    },
//            ));
//        }

        //tree: add group title
        if( method_exists($this->params['entity'],'getOrganizationalGroupType') ) {
            $builder->add('organizationalGroupType',null,array(
                'label' => 'Organizational Group Type:',
                'attr' => array('class' => 'combobox combobox-width')
            ));
        }

        //types
        if( method_exists($this->params['entity'],'getTypes') ) {
            //echo "cycle=".$this->params['cycle']."<br>";
            if( $this->params['cycle'] == "show" ) {
                $attr = array('class' => 'combobox combobox-width', 'readonly'=>'readonly');
            } else {
                $attr = array('class' => 'combobox combobox-width');
            }
            $builder->add('types',null,array(
                'label' => $this->mapper['className'].' Type(s):',
                'attr' => $attr
            ));
        }

        //url
        if( method_exists($this->params['entity'],'getUrl') ) {
            $builder->add('url',null,array(
                'label' => 'Url (only ending url: http://127.0.0.1/dashboards/chart/1 => chart):',
                'attr' => array('class' => 'form-control')
            ));
        }
        if( method_exists($this->params['entity'],'getExclusivelySites') ) {
            $builder->add('exclusivelySites',EntityType::class,array(
                'class' => SiteList::class,
                'label' => 'Apply Url exclusively to Site(s):',
                'multiple' => true,
                'attr' => array('class'=>'combobox combobox-width'),
                'required' => false,
            ));
        }

        //PermissionList
        if( strtolower($this->mapper['className']) == strtolower("PermissionList") ) {
            $builder->add('permissionObjectList',EntityType::class,array(
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:PermissionObjectList'] by [PermissionObjectList::class]
                'class' => PermissionObjectList::class,
                'label' => "Object:",
                'multiple' => false,
                'required' => false,
                'attr' => array('class'=>'combobox'),
            ));
            $builder->add('permissionActionList',EntityType::class,array(
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:PermissionActionList'] by [PermissionActionList::class]
                'class' => PermissionActionList::class,
                'label' => "Action:",
                'multiple' => false,
                'required' => false,
                'attr' => array('class'=>'combobox'),
            ));
        }

        //Roles
        if( strtolower($this->mapper['className']) == strtolower("Roles") ) {

            $builder->add('alias',null,array(
                'label'=>'Alias:',
                'attr' => array('class' => 'form-control')
            ));
            $builder->add('attributes',EntityType::class,array(
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:RoleAttributeList'] by [RoleAttributeList::class]
                'class' => RoleAttributeList::class,
                'label' => "Attribute(s):",
                'multiple' => true,
                'attr' => array('class'=>'combobox combobox-width'),
                'required' => false,
            ));

            //permissions: show list of
            $builder->add('permissions', CollectionType::class, array(
                'entry_type' => PermissionType::class,
                'label' => false,
                'required' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__permissions__',
            ));

//            $builder->add('sites',EntityType::class,array(
//                'class' => SiteList::class,
//                'label' => "Site(s):",
//                'multiple' => true,
//                'attr' => array('class'=>'combobox combobox-width'),
//                'required' => false,
//            ));

            $builder->add('level',null,array(
                'label' => "Level:",
                'attr' => array('class'=>'form-control'),
                'required' => false,
            ));

            $builder->add('fellowshipSubspecialty',EntityType::class,array(
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:FellowshipSubspecialty'] by [FellowshipSubspecialty::class]
                'class' => FellowshipSubspecialty::class,
                'label' => "Fellowship Subspecialty:",
                'multiple' => false,
                'required' => false,
                'attr' => array('class'=>'combobox combobox-width')
            ));

            $builder->add('residencySubspecialty',EntityType::class,array(
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:ResidencySpecialty'] by [ResidencySpecialty::class]
                'class' => ResidencySpecialty::class,
                'label' => "Residency Specialty (Old, To be removed):",
                'multiple' => false,
                'required' => false,
                'attr' => array('class'=>'combobox combobox-width')
            ));

            $builder->add('residencyTrack',EntityType::class,array(
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:ResidencyTrackList'] by [ResidencyTrackList::class]
                'class' => ResidencyTrackList::class,
                'label' => "Residency Track:",
                'multiple' => false,
                'required' => false,
                'attr' => array('class'=>'combobox combobox-width')
            ));
        }

        if( method_exists($this->params['entity'],'getSites') ) {
            $builder->add('sites',EntityType::class,array(
                'class' => SiteList::class,
                'label' => "Site(s):",
                'multiple' => true,
                'attr' => array('class'=>'combobox combobox-width'),
                'required' => false,
            ));
        }

        //Role Attributes
        if( strtolower($this->mapper['className']) == strtolower("RoleAttributeList") || strtolower($this->mapper['className']) == strtolower("FellAppRank") ) {
            $builder->add('value',null,array(
                'label'=>'Value:',
                'attr' => array('class' => 'form-control')
            ));
        }

        //nativeName for Language List
        if( strtolower($this->mapper['className']) == strtolower("LanguageList") ) {
            $builder->add('nativeName',null,array(
                'label'=>'Native Name:',
                'attr' => array('class' => 'form-control')
            ));
        }

        //level for OrganizationalGroupType
        if(
            strtolower($this->mapper['className']) == strtolower("OrganizationalGroupType") ||
            strtolower($this->mapper['className']) == strtolower("MessageTypeClassifiers") ||
            strtolower($this->mapper['className']) == strtolower("CommentGroupType") ||
            strtolower($this->mapper['className']) == strtolower("ResearchGroupType") ||
            strtolower($this->mapper['className']) == strtolower("CourseGroupType") ||
            strtolower($this->mapper['className']) == strtolower("PatientListHierarchyGroupType") ||
            strtolower($this->mapper['className']) == strtolower("AccessionListHierarchyGroupType") ||
            strtolower($this->mapper['className']) == strtolower("TopicList") ||
            strtolower($this->mapper['className']) == strtolower("ChartTypeList")
        ) {
//        if( method_exists($this->params['entity'],'getLevel') ) {
            $builder->add('level',null,array(
                'label'=>'Default Tree Level Association:',
                'attr' => array('class' => 'form-control')
            ));
        }

        //fields for Tree implements CompositeNodeInterface
        if( $this->params['entity'] instanceof CompositeNodeInterface ) {
            //always read only - do not allow to change level
            $builder->add('level',null,array(
                'label'=>'Level:',
                //'disabled' => true,
                'attr' => array('class' => 'form-control')
            ));
            //always read only - do not allow to change parent
            $builder->add('parent',null,array(
                'label' => $this->mapper['parentClassName'].' (Parent):',
                'choice_label' => 'getTreeName',
                'attr' => array('class' => 'combobox combobox-width', 'readonly'=>'readonly')
            ));
        }

        //PlatformListManagerRootList
        if( $this->params['entity'] instanceof PlatformListManagerRootList ) {
            $builder->add('listName',null,array(
                'label'=>'Database Entity Name:',
                'attr' => array('class'=>'form-control')
            ));
            $builder->add('listRootName',null,array(
                'label'=>'Root Name:',
                'attr' => array('class'=>'form-control')
            ));
        }

        //FormNode Holder
        if( method_exists($this->params['entity'],'getFormNodes') ) {
            $builder->add( 'formNodes', EntityType::class, array(
                'class' => FormNode::class,
                'choice_label' => 'getTreeNameObjectType',
                'label'=>'Form Node(s):',
                'required'=> false,
                'multiple' => true,
                'attr' => array('class'=>'combobox combobox-width'),
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
        }

        //FormNode
        if( $this->params['entity'] instanceof FormNode ) {
            $builder->add('showLabel', CheckboxType::class, array(
                'label' => 'Show Field Label:',
                'required' => false,
                'attr' => array('class'=>'form-control', 'style'=>'margin:0')
            ));
            $builder->add('placeholder', TextType::class, array(
                'label' => 'Placeholder:',
                'required' => false,
                'attr' => array('class'=>'form-control')
            ));
            $builder->add('visible', CheckboxType::class, array(
                'label' => 'Visible:',
                'required' => false,
                'attr' => array('class'=>'form-control', 'style'=>'margin:0')
            ));
            $builder->add('required', CheckboxType::class, array(
                'label' => 'Required:',
                'required' => false,
                'attr' => array('class'=>'form-control', 'style'=>'margin:0')
            ));
        }

        //ReceivedValueEntity
        if( method_exists($this->params['entity'],'getReceivedValueEntityName') ) {
            $builder->add('receivedValueEntityNamespace',null,array(
                'label' => "Received Value Entity Namespace:",
                'required' => false,
                'attr' => array('class'=>'form-control'),
            ));
            $builder->add('receivedValueEntityName',null,array(
                'label' => "Received Value Entity Name:",
                'required' => false,
                'attr' => array('class'=>'form-control'),
            ));
            $builder->add('receivedValueEntityId',null,array(
                'label' => "Received Value Entity Id:",
                'required' => false,
                'attr' => array('class'=>'form-control'),
            ));
        }

        if( strtolower($this->mapper['className']) == strtolower("UserWrapper") ) {
            $builder->add( 'user', EntityType::class, array(
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:User'] by [User::class]
                'class' => User::class,
                'label'=>'Linked User:',
                'required'=> false,
                'multiple' => false,
                'attr' => array('class'=>'combobox combobox-width'),
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('list')
                        ->leftJoin("list.infos", "infos")
                        ->leftJoin("list.employmentStatus", "employmentStatus")
                        ->leftJoin("employmentStatus.employmentType", "employmentType")
                        ->where("employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL")
                        ->orderBy("infos.displayName","ASC");
                },
            ));

//            $builder->add('referringProviderSpecialty', 'custom_selector', array(
//                'label' => 'Referring Provider Specialty:',
//                'attr' => array('class' => 'combobox combobox-width ajax-combobox-referringProviderSpecialty'),
//                'required' => false,
//                'classtype' => 'referringProviderSpecialty'
//            ));
            $builder->add('userWrapperSpecialty', null, array(
                'label' => 'User Specialty:',
                'attr' => array('class'=>'combobox')
            ));

            $builder->add('userWrapperPhone', null, array(
                'label' => 'User Phone Number:',
                'attr' => array('class'=>'form-control')
            ));

            $builder->add('userWrapperEmail', null, array(
                'label' => 'User E-Mail:',
                'attr' => array('class'=>'form-control')
            ));
        }

        //RequestCategoryTypeList
        if( $this->params['entity'] instanceof RequestCategoryTypeList ) {
            $builder->add('section', TextType::class, array(
                'label' => 'Section:',
                'required' => false,
                'attr' => array('class'=>'form-control')
            ));
            $builder->add('productId', TextType::class, array(
                'label' => 'Product ID:',
                'required' => false,
                'attr' => array('class'=>'form-control')
            ));

            //Default fee
            $builder->add('fee', TextType::class, array(
                'label' => 'Fee per unit for initial quantity ($):',
                'required' => false,
                'attr' => array('class'=>'form-control currency-mask-without-prefix')
            ));
            $builder->add('feeAdditionalItem', TextType::class, array(
                'label' => 'Fee per additional item ($):',
                'required' => false,
                'attr' => array('class'=>'form-control currency-mask-without-prefix')
            ));

            $builder->add('initialQuantity', TextType::class, array(
                'label' => 'Initial Quantity:',
                'required' => false,
                'attr' => array('class'=>'form-control digit-mask')
            ));
//            if(0) {
//                if (
//                    $this->params['SecurityAuthChecker']->isGranted('ROLE_TRANSRES_ADMIN') ||
//                    $this->params['SecurityAuthChecker']->isGranted('ROLE_TRANSRES_TECHNICIAN')
//                ) {
//                    $builder->add('internalFee', TextType::class, array(
//                        'label' => 'Internal Fee for one ($):',
//                        'required' => false,
//                        'attr' => array('class' => 'form-control')
//                    ));
//                    $builder->add('internalFeeAdditionalItem', TextType::class, array(
//                        'label' => 'Internal fee per additional item ($):',
//                        'required' => false,
//                        'attr' => array('class' => 'form-control')
//                    ));
//                }
//            }
            $builder->add('prices', CollectionType::class, array(
                'entry_type' => PriceType::class,
                'label' => false,
                'required' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__prices__',
            ));

            $builder->add('feeUnit', TextType::class, array(
                'label' => 'Fee Unit:',
                'required' => false,
                'attr' => array('class'=>'form-control')
            ));
            $builder->add( 'projectSpecialties', EntityType::class, array(
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:SpecialtyList'] by [SpecialtyList::class]
                'class' => SpecialtyList::class,
                //'choice_label' => 'FullName',
                //'label'=>'Project Specialty(s):',
                'label'=>'Hide this orderable for the work requests that belong to project requests of this type:',
                'required'=> false,
                'multiple' => true,
                'attr' => array('class'=>'combobox combobox-width'),
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('list')
                        ->where("(list.type = :typedef OR list.type = :typeadd)")
                        ->orderBy("list.orderinlist","ASC")
                        ->setParameters( array(
                            'typedef' => 'default',
                            'typeadd' => 'user-added',
                        ));
                },
            ));

            $builder->add( 'workQueues', EntityType::class, array(
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:WorkQueueList'] by [WorkQueueList::class]
                'class' => WorkQueueList::class,
                'label'=>'Work Queues:',
                'required'=> false,
                'multiple' => true,
                'attr' => array('class'=>'combobox'),
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('list')
                        ->where("(list.type = :typedef OR list.type = :typeadd)")
                        ->orderBy("list.orderinlist","ASC")
                        ->setParameters( array(
                            'typedef' => 'default',
                            'typeadd' => 'user-added',
                        ));
                },
            ));
        }

        if( strtolower($this->mapper['className']) == strtolower("SiteList") ) {
            $builder->add('selfSignUp',null,array(
                'label' => "Self Sign Up:",
                'required' => false,
                'attr' => array('class'=>'form-control'),
            ));
            $builder->add('lowestRoles',null,array(
                'label' => "Most Basic Roles:",
                'required' => false,
                'attr' => array('class'=>'combobox'),
            ));
            $builder->add('accessibility',null,array(
                'label' => "Accessibility:",
                'required' => false,
                'attr' => array('class'=>'form-control'),
            ));
            $builder->add('showLinkHomePage',null,array(
                'label' => "Show Link On Home Page:",
                'required' => false,
                'attr' => array('class'=>'form-control'),
            ));
            $builder->add('showLinkNavbar',null,array(
                'label' => "Show Link in Navbar:",
                'required' => false,
                'attr' => array('class'=>'form-control'),
            ));
            $builder->add('fromEmail',null,array(
                'label' => "Emails sent by this site will appear to come from the following address:",
                'required' => false,
                'attr' => array('class'=>'form-control'),
            ));

            $builder->add('requireVerifyMobilePhone',null,array(
                'label' => "Require and Verify Mobile Number during Access Requests and Account Requests:",
                'required' => false,
                'attr' => array('class'=>'form-control'),
            ));
            $builder->add('requireMobilePhoneToLogin',null,array(
                'label' => "Only allow log in if the primary mobile number is verified and ask to verify:",
                'required' => false,
                'attr' => array('class'=>'form-control'),
            ));

            $builder->add('noteOnLoginPage', null, array(
                'label' => 'Note at the top of the log in page:',
                'attr' => array('class' => 'form-control textarea')
            ));

            $builder->add('documents', CollectionType::class, array(
                'entry_type' => DocumentType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__documentsid__',
            ));
        }


        ///////////////// Many To Many relationship /////////////////

        //not editable: suites, rooms
        if( strtolower($this->mapper['className']) == strtolower("Department") ) {
            $builder->add('suites', EntityType::class, array(
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:SuiteList'] by [SuiteList::class]
                'class' => SuiteList::class,
                'choice_label' => 'FullName',
                'label'=>'Suite(s):',
                'required'=> false,
                'multiple' => true,
                //'disabled' => true,
                'attr' => array('class' => 'combobox combobox-width', 'readonly'=>true)
            ));

            $builder->add('rooms', EntityType::class, array(
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:RoomList'] by [RoomList::class]
                'class' => RoomList::class,
                'choice_label' => 'FullName',
                'label'=>'Room(s):',
                'required'=> false,
                'multiple' => true,
                //'disabled' => true,
                'attr' => array('class' => 'combobox combobox-width', 'readonly'=>true)
            ));
        }

        //Floor:
        //not editable: suites, rooms
        if( strtolower($this->mapper['className']) == strtolower("FloorList") ) {
            $builder->add('suites', EntityType::class, array(
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:SuiteList'] by [SuiteList::class]
                'class' => SuiteList::class,
                'choice_label' => 'FullName',
                'label'=>'Suite(s):',
                'required'=> false,
                'multiple' => true,
                //'disabled' => true,
                'attr' => array('class' => 'combobox combobox-width', 'readonly'=>true)
            ));

            $builder->add('rooms', EntityType::class, array(
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:RoomList'] by [RoomList::class]
                'class' => RoomList::class,
                'choice_label' => 'FullName',
                'label'=>'Room(s):',
                'required'=> false,
                'multiple' => true,
                //'disabled' => true,
                'attr' => array('class' => 'combobox combobox-width', 'readonly'=>true)
            ));
        }


        //Suite: buildings, floors
        if( strtolower($this->mapper['className']) == strtolower("SuiteList") ) {
            $builder->add('buildings', EntityType::class, array(
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:BuildingList'] by [BuildingList::class]
                'class' => BuildingList::class,
                'label'=>'Building(s):',
                'required'=> false,
                'multiple' => true,
                'attr' => array('class' => 'combobox combobox-width')
            ));

//            $builder->add('departments', EntityType::class, array(
//                'class' => 'AppUserdirectoryBundle:Department',
//                'label'=>'Department(s):',
//                'required'=> false,
//                'multiple' => true,
//                //'by_reference' => false,
//                'attr' => array('class' => 'combobox combobox-width')
//            ));

            $builder->add('floors', EntityType::class, array(
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:FloorList'] by [FloorList::class]
                'class' => FloorList::class,
                'label'=>'Floor(s):',
                'required'=> false,
                'multiple' => true,
                'attr' => array('class' => 'combobox combobox-width')
            ));
        }

        //Room: buildings, suite
        if( strtolower($this->mapper['className']) == strtolower("RoomList") ) {
            $builder->add('buildings', EntityType::class, array(
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:BuildingList'] by [BuildingList::class]
                'class' => BuildingList::class,
                'label'=>'Building(s):',
                'required'=> false,
                'multiple' => true,
                //'by_reference' => false,
                'attr' => array('class' => 'combobox combobox-width')
            ));

//            $builder->add('departments', EntityType::class, array(
//                'class' => 'AppUserdirectoryBundle:Department',
//                'label'=>'Department(s):',
//                'required'=> false,
//                'multiple' => true,
//                'attr' => array('class' => 'combobox combobox-width')
//            ));

            $builder->add('suites', EntityType::class, array(
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:SuiteList'] by [SuiteList::class]
                'class' => SuiteList::class,
                'choice_label' => 'FullName',
                'label'=>'Suite(s):',
                'required'=> false,
                'multiple' => true,
                'attr' => array('class' => 'combobox combobox-width')
            ));

            $builder->add('floors', EntityType::class, array(
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:FloorList'] by [FloorList::class]
                'class' => FloorList::class,
                'label'=>'Floor(s):',
                'required'=> false,
                'multiple' => true,
                'attr' => array('class' => 'combobox combobox-width')
            ));
        }

        ///////////////// EOF Many To Many relationship /////////////////


        if( strtolower($this->mapper['className']) == strtolower("AntibodyList") ) {
            $builder->add('category',null,array(
                'label' => "Category:",
                'required' => false,
                'attr' => array('class'=>'form-control', 'maxlength'=>"255"),
            ));

//            $builder->add('categoryTags', EntityType::class, array(
//                'class' => AntibodyCategoryTagList::class,
//                //'choice_label' => 'Antibody Category Tag(s)',
//                'label'=>'Antibody Category Tag(s):',
//                'required'=> false,
//                'multiple' => true,
//                'attr' => array('class' => 'combobox combobox-width')
//            ));
            $builder->add('categoryTags', EntityType::class, array(
                'class' => AntibodyCategoryTagList::class,
                //'choice_label' => 'getTreeName',
                'label'=>'Antibody Category Tag(s):',
                'required'=> false,
                'multiple' => true,
                'attr' => array('class'=>'combobox combobox-width'),
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('list')
                        ->where("(list.type = :typedef OR list.type = :typeadd)")
                        ->orderBy("list.orderinlist","ASC")
                        ->setParameters( array(
                            'typedef' => 'default',
                            'typeadd' => 'user-added',
                        ));
                },
            ));

            $builder->add('antibodyLabs', EntityType::class, array(
                'class' => AntibodyLabList::class,
                //'choice_label' => 'getTreeName',
                'label'=>'Lab offering the Antibody:',
                'required'=> false,
                'multiple' => true,
                'attr' => array('class'=>'combobox combobox-width'),
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('list')
                        ->where("(list.type = :typedef OR list.type = :typeadd)")
                        ->orderBy("list.orderinlist","ASC")
                        ->setParameters( array(
                            'typedef' => 'default',
                            'typeadd' => 'user-added',
                        ));
                },
            ));
            $builder->add('antibodyPanels', EntityType::class, array(
                'class' => AntibodyPanelList::class,
                //'choice_label' => 'getTreeName',
                'label'=>'Antibody Panel(s):',
                'required'=> false,
                'multiple' => true,
                'attr' => array('class'=>'combobox combobox-width'),
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('list')
                        ->where("(list.type = :typedef OR list.type = :typeadd)")
                        ->orderBy("list.orderinlist","ASC")
                        ->setParameters( array(
                            'typedef' => 'default',
                            'typeadd' => 'user-added',
                        ));
                },
            ));

            $builder->add('altname',null,array(
                'label' => "Alternative Name:",
                'required' => false,
                'attr' => array('class'=>'form-control', 'maxlength'=>"255"),
            ));

            $builder->add('company',null,array(
                'label' => "Company:",
                'required' => false,
                'attr' => array('class'=>'form-control'),
            ));

            $builder->add('catalog',null,array(
                'label' => "Catalog:",
                'required' => false,
                'attr' => array('class'=>'form-control'),
            ));

            $builder->add('lot',null,array(
                'label' => "Lot:",
                'required' => false,
                'attr' => array('class'=>'form-control'),
            ));

            $builder->add('igconcentration',null,array(
                'label' => "ig Concentration:",
                'required' => false,
                'attr' => array('class'=>'form-control'),
            ));

            $builder->add('clone',null,array(
                'label' => "Clone:",
                'required' => false,
                'attr' => array('class'=>'form-control'),
            ));

            $builder->add('host',null,array(
                'label' => "Host:",
                'required' => false,
                'attr' => array('class'=>'form-control'),
            ));

            $builder->add('reactivity',null,array(
                'label' => "Reactivity:",
                'required' => false,
                'attr' => array('class'=>'form-control'),
            ));

            $builder->add('control',null,array(
                'label' => "Control:",
                'required' => false,
                'attr' => array('class'=>'form-control'),
            ));

            $builder->add('protocol',null,array(
                'label' => "Protocol:",
                'required' => false,
                'attr' => array('class'=>'form-control'),
            ));

            $builder->add('retrieval',null,array(
                'label' => "Retrieval:",
                'required' => false,
                'attr' => array('class'=>'form-control'),
            ));

            $builder->add('dilution',null,array(
                'label' => "Dilution:",
                'required' => false,
                'attr' => array('class'=>'form-control'),
            ));

            $builder->add('storage',null,array(
                'label' => "Storage:",
                'required' => false,
                'attr' => array('class'=>'form-control'),
            ));

            $builder->add('comment',null,array(
                'label' => "Comment:",
                'required' => false,
                'attr' => array('class'=>'form-control textarea'),
            ));

            $builder->add('comment1',null,array(
                'label' => "Additional Comment 1:",
                'required' => false,
                'attr' => array('class'=>'form-control textarea'),
            ));

            $builder->add('comment2',null,array(
                'label' => "Additional Comment 2:",
                'required' => false,
                'attr' => array('class'=>'form-control textarea'),
            ));

            $builder->add('datasheet',null,array(
                'label' => "Datasheet:",
                'required' => false,
                'attr' => array('class'=>'form-control textarea'),
            ));

//            $builder->add('pdf',null,array(
//                'label' => "Pdf link:",
//                'required' => false,
//                'attr' => array('class'=>'form-control'),
//            ));

            $builder->add('documents', CollectionType::class, array(
                'entry_type' => DocumentType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__documentsid__',
            ));


            $builder->add('inventory',null,array(
                'label' => "Inventory Stock:",
                'required' => false,
                'attr' => array('class'=>'form-control'),
            ));
            $builder->add('unitPrice',null,array(
                'label' => "Unit Price:",
                'required' => false,
                'attr' => array('class'=>'form-control'),
            ));
            $builder->add('tissueType',null,array(
                'label' => "Tissue Type:",
                'required' => false,
                'attr' => array('class'=>'form-control'),
            ));
            $builder->add('visualInfos', CollectionType::class, array(
                'entry_type' => VisualInfoType::class,
                'label' => false,
                'required' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__visualinfos__',
            ));
            $builder->add('openToPublic', null, array(
                'label' => "Open to public:",
                'required' => false,
                'attr' => array('class' => 'form-control'),
            ));
        }

        if( strtolower($this->mapper['className']) == strtolower("AntibodyCategoryTagList") ) {
            $builder->add('openToPublic', null, array(
                'label' => "Open to public:",
                'required' => false,
                'attr' => array('class' => 'form-control'),
            ));
        }

        if( method_exists($this->params['entity'],'getTagTypes') ) {
            $builder->add('tagTypes', EntityType::class, array(
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:MessageTagTypesList'] by [MessageTagTypesList::class]
                'class' => MessageTagTypesList::class,
                'label'=>'Tag Type(s):',
                'required'=> false,
                'multiple' => true,
                'attr' => array('class' => 'combobox combobox-width')
            ));
        }

        if( strtolower($this->mapper['className']) == strtolower("SpecialtyList") ) {

//            $builder->add('shortname',null,array(
//                'label' => "Short name used in the project OID (i.e. 'APCP' in 'APCP1234'):",
//                'required' => false,
//                'attr' => array('class'=>'form-control'),
//            ));

            $builder->add('rolename',null,array(
                'label' => "Role postfix (i.e. 'APCP' in 'ROLE_TRANSRES_ADMIN_APCP'):",
                'required' => false,
                'attr' => array('class'=>'form-control'),
            ));

            $builder->add('friendlyname',null,array(
                'label' => "Project specialty shown to users as a user friendly name (i.e. 'AP/CP' in 'New AP/CP Project'):",
                'required' => false,
                'attr' => array('class'=>'form-control'),
            ));
        }

        //Dashboard CharList
        if( $this->params['entity'] instanceof ChartList ) {
//            //accessRoles
//            //$rolesWhere = "(list.type = :typedef OR list.type = :typeadd)";
//            $builder->add( 'accessRoles', EntityType::class, array(
//                'class' => 'AppUserdirectoryBundle:Roles',
//                //'choice_label' => 'getTreeName',
//                'label'=>'Accessible to users with the following roles:',
//                'choice_label' => 'getAlias',
//                'required'=> false,
//                'multiple' => true,
//                'attr' => array('class'=>'combobox combobox-width'),
//                'query_builder' => function(EntityRepository $er) {
//                    return $er->createQueryBuilder('list')
//                        ->leftJoin("list.sites","sites")
//                        //->where("(list.type = :typedef OR list.type = :typeadd) AND list.level=1")
//                        ->where("(list.type = :typedef OR list.type = :typeadd)")
//                        ->andWhere("sites.abbreviation = :siteAbbreviation")
//                        ->orderBy("list.orderinlist","ASC")
//                        ->setParameters( array(
//                            'typedef' => 'default',
//                            'typeadd' => 'user-added',
//                            'siteAbbreviation' => 'dashboard'
//                        ));
//                },
//            ));
//
//            $builder->add( 'denyRoles', EntityType::class, array(
//                'class' => 'AppUserdirectoryBundle:Roles',
//                //'choice_label' => 'getTreeName',
//                'label'=>'Deny access to users with the following roles:',
//                'choice_label' => 'getAlias',
//                'required'=> false,
//                'multiple' => true,
//                'attr' => array('class'=>'combobox combobox-width'),
//                'query_builder' => function(EntityRepository $er) {
//                    return $er->createQueryBuilder('list')
//                        ->leftJoin("list.sites","sites")
//                        ->where("(list.type = :typedef OR list.type = :typeadd)")
//                        ->andWhere("sites.abbreviation = :siteAbbreviation")
//                        ->orderBy("list.orderinlist","ASC")
//                        ->setParameters( array(
//                            'typedef' => 'default',
//                            'typeadd' => 'user-added',
//                            'siteAbbreviation' => 'dashboard'
//                        ));
//                },
//            ));
//
////            $builder->add( 'denyUsers', EntityType::class, array(
////                'class' => 'AppUserdirectoryBundle:User',
////                'label'=> "Deny access to the following users:",
////                'required'=> false,
////                'multiple' => false,
////                'attr' => array('class'=>'combobox combobox-width'),
////                'query_builder' => $this->params['transresUtil']->userQueryBuilder()
////            ));
//            $builder->add( 'denyUsers', EntityType::class, array(
//                'class' => 'AppUserdirectoryBundle:User',
//                'label'=> "Deny access to the following users:",
//                'required'=> false,
//                'multiple' => true,
//                'attr' => array('class'=>'combobox combobox-width'),
//                'query_builder' => function(EntityRepository $er) {
//                    return $er->createQueryBuilder('list')
//                        ->leftJoin("list.employmentStatus", "employmentStatus")
//                        ->leftJoin("employmentStatus.employmentType", "employmentType")
//                        ->where("employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL")
//                        //->andWhere("list.roles LIKE '%ROLE_TRANSRES_%'")
//                        ->leftJoin("list.infos", "infos")
//                        ->orderBy("infos.displayName","ASC");
//                },
//            ));
//
//            $builder->add( 'downloadRoles', EntityType::class, array(
//                'class' => 'AppUserdirectoryBundle:Roles',
//                //'choice_label' => 'getTreeName',
//                'label'=>'Data can be downloaded by users with the following roles:',
//                'choice_label' => 'getAlias',
//                'required'=> false,
//                'multiple' => true,
//                'attr' => array('class'=>'combobox combobox-width'),
//                'query_builder' => function(EntityRepository $er) {
//                    return $er->createQueryBuilder('list')
//                        ->leftJoin("list.sites","sites")
//                        ->where("(list.type = :typedef OR list.type = :typeadd)")
//                        ->andWhere("sites.abbreviation = :siteAbbreviation")
//                        ->orderBy("list.orderinlist","ASC")
//                        ->setParameters( array(
//                            'typedef' => 'default',
//                            'typeadd' => 'user-added',
//                            'siteAbbreviation' => 'dashboard'
//                        ));
//                },
//            ));

            $builder->add('width',null,array(
                'label' => "Default Image Width in Pixels:",
                'required' => false,
                'attr' => array('class'=>'form-control'),
            ));

            $builder->add('height',null,array(
                'label' => "Default Image Height In Pixels:",
                'required' => false,
                'attr' => array('class'=>'form-control'),
            ));

            $builder->add('chartTitle',null,array(
                'label' => "Display Chart Title:",
                'required' => false,
                'attr' => array('class'=>'form-control'),
            ));
            
//            $builder->add( 'institutions', EntityType::class, array(
//                'class' => 'AppUserdirectoryBundle:Institution',
//                'choice_label' => 'getTreeName',
//                'label'=>'Associated with the following organizational groups:',
//                'required'=> false,
//                'multiple' => true,
//                'attr' => array('class'=>'combobox combobox-width'),
//                'query_builder' => function(EntityRepository $er) {
//                    return $er->createQueryBuilder('list')
//                        ->leftJoin("list.children","children")
//                        ->where("list.type = :typedef OR list.type = :typeadd")
//                        //->orderBy("list.orderinlist, list.level","ASC")
//                        ->orderBy("list.orderinlist","ASC")
//                        ->setParameters( array(
//                            'typedef' => 'default',
//                            'typeadd' => 'user-added',
//                        ));
//                },
//            ));

            $builder->add( 'topics', EntityType::class, array(
        //process.py script: replaced namespace by ::class: ['AppDashboardBundle:TopicList'] by [TopicList::class]
                'class' => TopicList::class,
                'choice_label' => 'getTreeName',
                'label'=>'Associated Dashboard Topics:',
                'required'=> false,
                'multiple' => true,
                'attr' => array('class'=>'combobox combobox-width'),
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('list')
                        ->leftJoin("list.children","children")
                        ->where("(list.type = :typedef OR list.type = :typeadd) AND list.level>0")
                        ->orderBy("list.orderinlist","ASC")
                        ->setParameters( array(
                            'typedef' => 'default',
                            'typeadd' => 'user-added',
                        ));
                },
            ));

            $builder->add( 'visualization', EntityType::class, array(
                'class' => VisualizationList::class,
                //'choice_label' => 'getTreeName',
                'label'=>'Dashboard Visualization Method:',
                'required'=> false,
                'multiple' => false,
                'attr' => array('class'=>'combobox combobox-width'),
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

            $builder->add( 'chartTypes', EntityType::class, array(
                'class' => ChartTypeList::class,
                //'choice_label' => 'getTreeName',
                'label'=>'Dashboard Chart Types:',
                'required'=> false,
                'multiple' => true,
                'attr' => array('class'=>'combobox combobox-width'),
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

            $builder->add( 'dataSource', EntityType::class, array(
                'class' => DataSourceList::class,
                //'choice_label' => 'getTreeName',
                'label'=>'Dashboard Data Source:',
                'required'=> false,
                'multiple' => false,
                'attr' => array('class'=>'combobox combobox-width'),
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

            $builder->add( 'updateFrequency', EntityType::class, array(
        //process.py script: replaced namespace by ::class: ['AppDashboardBundle:UpdateFrequencyList'] by [UpdateFrequencyList::class]
                'class' => UpdateFrequencyList::class,
                //'choice_label' => 'getTreeName',
                'label'=>'Dashboard Update Frequency:',
                'required'=> false,
                'multiple' => false,
                'attr' => array('class'=>'combobox combobox-width'),
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

            $builder->add('imagePath',null,array(
                'label' => "Path to pre-generated default image for this chart:",
                'required' => false,
                'attr' => array('class'=>'textarea form-control'),
            ));

            $builder->add('imageDate',null,array(
                'label' => "Timestamp for the pre-generated default image for this chart:",
                'required' => false,
                'widget' => 'single_text',
                'format' => 'MM/dd/yyyy',
                'html5' => false,
                'attr' => array('class'=>'datepicker form-control allow-future-date'),
            ));

            $builder->add('chartComment',null,array(
                'label' => "Chart Comment:",
                'required' => false,
                'attr' => array('class'=>'textarea form-control'),
            ));

//            $builder->add( 'requester', EntityType::class, array(
//                'class' => 'AppUserdirectoryBundle:User',
//                'label'=> "Requested by:",
//                'required'=> false,
//                'multiple' => true,
//                'attr' => array('class'=>'combobox combobox-width'),
//                'query_builder' => function(EntityRepository $er) {
//                    return $er->createQueryBuilder('list')
//                        ->leftJoin("list.employmentStatus", "employmentStatus")
//                        ->leftJoin("employmentStatus.employmentType", "employmentType")
//                        ->where("employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL")
//                        ->leftJoin("list.infos", "infos")
//                        ->orderBy("infos.displayName","ASC");
//                },
//            ));
//
//            $builder->add('requestedDate',null,array(
//                'label' => "Requested on:",
//                'required' => false,
//                'widget' => 'single_text',
//                'format' => 'MM/dd/yyyy',
//                'attr' => array('class'=>'datepicker form-control allow-future-date'),
//            ));

            $this->commonChartFields($builder);


        } //if ChartList

        if( $this->params['entity'] instanceof TopicList ) {
            $builder->add( 'charts', EntityType::class, array(
        //process.py script: replaced namespace by ::class: ['AppDashboardBundle:ChartList'] by [ChartList::class]
                'class' => ChartList::class,
                //'choice_label' => 'getTreeName',
                'label'=>'Associated Dashboard Charts:',
                'required'=> false,
                'multiple' => true,
                'attr' => array('class'=>'combobox combobox-width'),
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

            $builder->add('topicComment',null,array(
                'label' => "Topic Comment:",
                'required' => false,
                'attr' => array('class'=>'textarea form-control'),
            ));

            $builder->add('publicAccess',null,array(
                'label' => "Enable public access without requiring log in:",
                'required' => false,
                'attr' => array('class'=>'form-control'),
            ));

            $this->commonChartFields($builder);
        } //if TopicList

        if( $this->params['entity'] instanceof VacReqApprovalTypeList ) {
            //vacationAccruedDaysPerMonth
            $builder->add('vacationAccruedDaysPerMonth',null,array(
                'label' => "Vacation days accrued per month (i.e. faculty = 2, fellows = 1.666666667):",
                'required' => false,
                'attr' => array('class'=>'form-control'),
            ));
            //maxVacationDays
            $builder->add('maxVacationDays',null,array(
                'label' => "Maximum number vacation days per year (i.e. for faculty 12*2=24):",
                'required' => false,
                'attr' => array('class'=>'form-control'),
            ));
            //maxCarryOverVacationDays
            $builder->add('maxCarryOverVacationDays',null,array(
                'label' => "Maximum number of carry over vacation days per year (i.e. for faculty 10 days):",
                'required' => false,
                'attr' => array('class'=>'form-control'),
            ));
            //noteForVacationDays
            $builder->add('noteForVacationDays',null,array(
                'label' => "Note for vacation days (header on the new time away request page):",
                'required' => false,
                'attr' => array('class'=>'form-control'),
            ));
            //noteForCarryOverDays
            $builder->add('noteForCarryOverDays',null,array(
                'label' => "Note for carry over vacation days (header on the new carry over request page):",
                'required' => false,
                'attr' => array('class'=>'form-control'),
            ));
            //allowCarryOver
            $builder->add('allowCarryOver',null,array(
                'label' => "Allow to request carry over of unused vacation days to the following year:",
                'required' => false,
                'attr' => array('class'=>'form-control'),
            ));
        }//VacReqApprovalTypeList

        if( $this->params['entity'] instanceof VacReqHolidayList ) {
            if (method_exists($this->params['entity'], 'getHolidayDate')) {
//            $builder->add('holidayDate',null,array(
//                'label' => "Holiday Date:",
//                'required' => false,
//                'attr' => array('class'=>'form-control'),
//            ));
                $builder->add('holidayDate', null, array(
                    'label' => "Holiday Date:",
                    'required' => false,
                    'widget' => 'single_text',
                    'format' => 'MM/dd/yyyy',
                    'html5' => false,
                    'attr' => array('class' => 'datepicker form-control allow-future-date'),
                ));
            }
            if (method_exists($this->params['entity'], 'getHolidayName')) {
                $builder->add('holidayName', null, array(
                    'label' => "Holiday Name:",
                    'required' => false,
                    'attr' => array('class' => 'form-control'),
                ));
            }
            if (method_exists($this->params['entity'], 'getCountry')) {
                $builder->add('country', null, array(
                    'label' => "Country:",
                    'required' => false,
                    'attr' => array('class' => 'form-control'),
                ));
            }
            if (method_exists($this->params['entity'], 'getObserved')) {
                $builder->add('observed', null, array(
                    'label' => "Observed:",
                    'required' => false,
                    'attr' => array('class' => 'form-control'),
                ));
            }
        }

        if( $this->params['entity'] instanceof AuthServerNetworkList ) {
            //hostedUserGroup (ManyToMany) is the Tenant ID (i.e. 'c/wcm/pathology' or 'c/lmh/pathology')
//            $builder->add('hostedUserGroups', CollectionType::class, array(
//                'entry_type' => HostedUserGroupType::class,
//                'label' => false,
//                'required' => false,
//                'allow_add' => true,
//                'allow_delete' => true,
//                'by_reference' => false,
//                'prototype' => true,
//                'prototype_name' => '__hostedusergroups__',
//            ));

            $this->hostedGroupHoldersFields($builder);
        }

        if( strtolower($this->mapper['className']) == strtolower("InterfaceTransferList") ) {
            $builder->add('transferStatus', EntityType::class, array(
                'class' => TransferStatusList::class,
                //'choice_label' => 'getTreeName',
                'label'=>'Interface transfer status:',
                'required'=> false,
                'multiple' => false,
                'attr' => array('class'=>'combobox'),
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

            $builder->add('transferSource', null, array(
                'label' => "Interface Transfer Source:",
                'required' => false,
                'attr' => array('class' => 'form-control'),
            ));

            $builder->add('transferDestination', null, array(
                'label' => "Interface Transfer Destination:",
                'required' => false,
                'attr' => array('class' => 'form-control'),
            ));

            $builder->add('sshUsername', null, array(
                'label' => "SSH username:",
                'required' => false,
                'attr' => array('class' => 'form-control'),
            ));

            $builder->add('sshPassword', null, array(
                'label' => "SSH password/key:",
                'required' => false,
                'attr' => array('class' => 'textarea form-control'),
            ));

            $builder->add('remoteCertificate', null, array(
                'label' => "Absolute path to the remote server certificate for curl:",
                'required' => false,
                'attr' => array('class' => 'textarea form-control'),
            ));

        }

        //SAML SamlConfig List
        if( strtolower($this->mapper['className']) == strtolower("SamlConfig") ) {
            $builder->add('client', null, array(
                'label' => "Client:",
                'required' => false,
                'attr' => array('class' => 'form-control'),
            ));
            $builder->add('idpEntityId', null, array(
                'label' => "IDP Entity Id:",
                'required' => false,
                'attr' => array('class' => 'form-control textarea'),
            ));
            $builder->add('idpSsoUrl', null, array(
                'label' => "IDP Sso Url:",
                'required' => false,
                'attr' => array('class' => 'form-control textarea'),
            ));
            $builder->add('idpSloUrl', null, array(
                'label' => "IDP Slo Url:",
                'required' => false,
                'attr' => array('class' => 'form-control textarea'),
            ));
            $builder->add('idpCert', null, array(
                'label' => "IDP Cert:",
                'required' => false,
                'attr' => array('class' => 'form-control textarea'),
            ));
            $builder->add('spEntityId', null, array(
                'label' => "SP Entity Id:",
                'required' => false,
                'attr' => array('class' => 'form-control textarea'),
            ));
            $builder->add('spAcsUrl', null, array(
                'label' => "SP Acs Url:",
                'required' => false,
                'attr' => array('class' => 'form-control textarea'),
            ));
            $builder->add('spSloUrl', null, array(
                'label' => "SP Slo Url:",
                'required' => false,
                'attr' => array('class' => 'form-control textarea'),
            ));
            $builder->add('spPrivateKey', null, array(
                'label' => "SP Private Key:",
                'required' => false,
                'attr' => array('class' => 'form-control textarea'),
            ));
            $builder->add('identifierAttribute', null, array(
                'label' => "Identifier Attribute:",
                'required' => false,
                'attr' => array('class' => 'form-control'),
            ));

            $builder->add('autoCreate', null, array(
                'label' => "autoCreate:",
                'required' => false,
                'attr' => array('class' => 'form-control'),
            ));

            //JSON
            $builder->add('attributeMapping', TextType::class, array(
                'label' => "attributeMapping:",
                'required' => false,
                'attr' => array('class' => 'form-control textarea'),
            ));
        }

    }

    public function hostedGroupHoldersFields($builder) {

//        $builder->add( 'hostedUserGroups', EntityType::class, array(
//            'class' => HostedUserGroupList::class,
//            //'choice_label' => 'getTreeName',
//            'label'=>'Hosted User Group Type(s):',
//            'required'=> false,
//            'multiple' => true,
//            //'multiple' => false,
//            'attr' => array('class'=>'combobox combobox-width'),
//            'query_builder' => function(EntityRepository $er) {
//                return $er->createQueryBuilder('list')
//                    ->where("list.type = :typedef OR list.type = :typeadd")
//                    ->orderBy("list.orderinlist","ASC")
//                    ->setParameters( array(
//                        'typedef' => 'default',
//                        'typeadd' => 'user-added',
//                    ));
//            },
//        ));

        $builder->add('hostedGroupHolders', CollectionType::class, array(
            'entry_type' => HostedGroupHolderType::class,
            'label' => false,
            'required' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__hostedgroupholders__',
        ));

        //Similar to BaseCommentsType
        ///////////////////////// tree node /////////////////////////
//        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
//            $hostedUserGroup = $event->getData();
//            $form = $event->getForm();

//            $label = null;
//            $mapper = array(
//                'prefix' => "App",
//                'className' => "HostedUserGroupList",
//                'bundleName' => "UserdirectoryBundle",
//                'fullClassName' => "App\\UserdirectoryBundle\\Entity\\HostedUserGroupList",
//                'entityNamespace' => "App\\UserdirectoryBundle\\Entity",
//                'organizationalGroupType' => NULL
//            );
//            if( $hostedUserGroup ) {
//                $commentType = $hostedUserGroup->getCommentType();
//                if( $commentType ) {
//                    $label = $this->params['em']->getRepository(HostedUserGroupList::class)->getLevelLabels($commentType,$mapper) . ":";
//                }
//            }
//            if( !$label ) {
//                $label = $this->params['em']->getRepository(HostedUserGroupList::class)->getLevelLabels(null,$mapper) . ":";
//            }

//            $form->add('hostedUserGroups', CustomSelectorType::class, array( //'employees_custom_selector'
//                'label' => $label,
//                'required' => false,
//                'attr' => array(
//                    'class' => 'ajax-combobox-compositetree',
//                    'type' => 'hidden',
//                    'data-compositetree-bundlename' => 'UserdirectoryBundle',
//                    'data-compositetree-classname' => 'HostedUserGroupList'
//                ),
//                'classtype' => 'hostedusergroup' //define it in CustomSelectorType
//            ));

//            $form->add('hostedUserGroups', CollectionType::class, array(
//                'entry_type' => HostedUserGroupType::class,
//                'entry_options' => array(
//                    //'form_custom_value' => $this->params
//                ),
//                'label' => 'Hosted User Group Type(s):',
//                'allow_add' => true,
//                'allow_delete' => true,
//                'required' => false,
//                'by_reference' => false,
//                'prototype' => true,
//                'prototype_name' => '__hostedusergroups__',
//            ));
//        });
        ///////////////////////// EOF tree node /////////////////////////
    }

    public function commonChartFields($builder) {
        $builder->add('favoriteUsers', EntityType::class, array(
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:User'] by [User::class]
            'class' => User::class,
            'label'=> "Favorited by the following users:",
            'required'=> false,
            'multiple' => true,
            'attr' => array('class'=>'combobox combobox-width'),
            'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('list')
                    ->leftJoin("list.employmentStatus", "employmentStatus")
                    ->leftJoin("employmentStatus.employmentType", "employmentType")
                    ->where("employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL")
                    //->andWhere("list.roles LIKE '%ROLE_TRANSRES_%'")
                    ->leftJoin("list.infos", "infos")
                    ->orderBy("infos.displayName","ASC");
            },
        ));

        $builder->add('institutions', EntityType::class, array(
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
            'class' => Institution::class,
            'choice_label' => 'getTreeName',
            'label'=>'Associated with the following organizational groups:',
            'required'=> false,
            'multiple' => true,
            'attr' => array('class'=>'combobox combobox-width'),
            'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('list')
                    ->leftJoin("list.children","children")
                    ->where("list.type = :typedef OR list.type = :typeadd")
                    //->orderBy("list.orderinlist, list.level","ASC")
                    ->orderBy("list.orderinlist","ASC")
                    ->setParameters( array(
                        'typedef' => 'default',
                        'typeadd' => 'user-added',
                    ));
            },
        ));

        //accessRoles
        //$rolesWhere = "(list.type = :typedef OR list.type = :typeadd)";
        $builder->add('accessRoles', EntityType::class, array(
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Roles'] by [Roles::class]
            'class' => Roles::class,
            //'choice_label' => 'getTreeName',
            'label'=>'Accessible to users with the following roles:',
            'choice_label' => 'getAlias',
            'required'=> false,
            'multiple' => true,
            'attr' => array('class'=>'combobox combobox-width'),
            'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('list')
                    ->leftJoin("list.sites","sites")
                    //->where("(list.type = :typedef OR list.type = :typeadd) AND list.level=1")
                    ->where("(list.type = :typedef OR list.type = :typeadd)")
                    ->andWhere("sites.abbreviation = :siteAbbreviation")
                    ->orderBy("list.orderinlist","ASC")
                    ->setParameters( array(
                        'typedef' => 'default',
                        'typeadd' => 'user-added',
                        'siteAbbreviation' => 'dashboard'
                    ));
            },
        ));

        $builder->add('denyRoles', EntityType::class, array(
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Roles'] by [Roles::class]
            'class' => Roles::class,
            //'choice_label' => 'getTreeName',
            'label'=>'Deny access to users with the following roles:',
            'choice_label' => 'getAlias',
            'required'=> false,
            'multiple' => true,
            'attr' => array('class'=>'combobox combobox-width'),
            'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('list')
                    ->leftJoin("list.sites","sites")
                    ->where("(list.type = :typedef OR list.type = :typeadd)")
                    ->andWhere("sites.abbreviation = :siteAbbreviation")
                    ->orderBy("list.orderinlist","ASC")
                    ->setParameters( array(
                        'typedef' => 'default',
                        'typeadd' => 'user-added',
                        'siteAbbreviation' => 'dashboard'
                    ));
            },
        ));

//            $builder->add( 'denyUsers', EntityType::class, array(
//                'class' => 'AppUserdirectoryBundle:User',
//                'label'=> "Deny access to the following users:",
//                'required'=> false,
//                'multiple' => false,
//                'attr' => array('class'=>'combobox combobox-width'),
//                'query_builder' => $this->params['transresUtil']->userQueryBuilder()
//            ));
        $builder->add('denyUsers', EntityType::class, array(
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:User'] by [User::class]
            'class' => User::class,
            'label'=> "Deny access to the following users:",
            'required'=> false,
            'multiple' => true,
            'attr' => array('class'=>'combobox combobox-width'),
            'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('list')
                    ->leftJoin("list.employmentStatus", "employmentStatus")
                    ->leftJoin("employmentStatus.employmentType", "employmentType")
                    ->where("employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL")
                    //->andWhere("list.roles LIKE '%ROLE_TRANSRES_%'")
                    ->leftJoin("list.infos", "infos")
                    ->orderBy("infos.displayName","ASC");
            },
        ));

        $builder->add('downloadRoles', EntityType::class, array(
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Roles'] by [Roles::class]
            'class' => Roles::class,
            //'choice_label' => 'getTreeName',
            'label'=>'Data can be downloaded by users with the following roles:',
            'choice_label' => 'getAlias',
            'required'=> false,
            'multiple' => true,
            'attr' => array('class'=>'combobox combobox-width'),
            'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('list')
                    ->leftJoin("list.sites","sites")
                    ->where("(list.type = :typedef OR list.type = :typeadd)")
                    ->andWhere("sites.abbreviation = :siteAbbreviation")
                    ->orderBy("list.orderinlist","ASC")
                    ->setParameters( array(
                        'typedef' => 'default',
                        'typeadd' => 'user-added',
                        'siteAbbreviation' => 'dashboard'
                    ));
            },
        ));

        $builder->add('requester', EntityType::class, array(
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:User'] by [User::class]
            'class' => User::class,
            'label'=> "Requested by:",
            'required'=> false,
            'multiple' => false,
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

        $builder->add('requestedDate',null,array(
            'label' => "Requested on:",
            'required' => false,
            'widget' => 'single_text',
            'format' => 'MM/dd/yyyy',
            'html5' => false,
            'attr' => array('class'=>'datepicker form-control allow-future-date'),
        ));
    }


    /**
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => null,   //$this->mapper['fullClassName'],
            'form_custom_value' => null,
            'form_custom_value_mapper' => null
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix(): string
    {
        $prefix = NULL;

        if( isset($this->mapper['className']) ) {
            //echo "className=".$this->mapper['className']."<br>";
            $prefix = strtolower($this->mapper['className']);
        }

        if( !$prefix ) {
            $prefix = 'genericlist';
        }
        return 'oleg_userdirectorybundle_'.$prefix;
    }
}
