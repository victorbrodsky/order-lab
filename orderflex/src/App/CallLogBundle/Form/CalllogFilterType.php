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

namespace App\CallLogBundle\Form;



use App\OrderformBundle\Entity\AccessionType;
use App\OrderformBundle\Entity\CalllogEntryTagsList; //process.py script: replaced namespace by ::class: added use line for classname=CalllogEntryTagsList


use App\UserdirectoryBundle\Entity\HealthcareProviderSpecialtiesList; //process.py script: replaced namespace by ::class: added use line for classname=HealthcareProviderSpecialtiesList


use App\UserdirectoryBundle\Entity\Location; //process.py script: replaced namespace by ::class: added use line for classname=Location


use App\UserdirectoryBundle\Entity\Institution; //process.py script: replaced namespace by ::class: added use line for classname=Institution


use App\OrderformBundle\Entity\PatientListHierarchy; //process.py script: replaced namespace by ::class: added use line for classname=PatientListHierarchy


use App\OrderformBundle\Entity\CalllogTaskTypeList; //process.py script: replaced namespace by ::class: added use line for classname=CalllogTaskTypeList


use App\UserdirectoryBundle\Entity\HealthcareProviderCommunicationList; //process.py script: replaced namespace by ::class: added use line for classname=HealthcareProviderCommunicationList
use App\UserdirectoryBundle\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CalllogFilterType extends AbstractType
{

    private $params;

    public function formConstructor( $params=null )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value']);

        $builder->add('startDate', DateTimeType::class, array(
            'label' => false,
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'html5' => false,
            'attr' => array('class'=>'datepicker form-control submit-on-enter-field', 'placeholder'=>'Start Date'), //'title'=>'Start Year', 'data-toggle'=>'tooltip',
        ));

        $builder->add('endDate', DateTimeType::class, array(
            'label' => false,
            'widget' => 'single_text',
            'required' => false,
            'html5' => false,
            'format' => 'MM/dd/yyyy', //'format' => 'MM/dd/yyyy',
            'attr' => array('class'=>'datepicker form-control submit-on-enter-field', 'placeholder'=>'End Date'), //'title'=>'End Year', 'data-toggle'=>'tooltip',
        ));

        $builder->add('entryTags', EntityType::class, array(
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:CalllogEntryTagsList'] by [CalllogEntryTagsList::class]
            'class' => CalllogEntryTagsList::class,
            'label' => false,
            'required' => false,
            'multiple' => true,
            'attr' => array('class' => 'combobox', 'placeholder' => "Entry Tag(s)"),
        ));
//        $builder->add('entryTags', EntityType::class, array(
//            'class' => 'AppOrderformBundle:MessageTagsList',
//            'label' => false,
//            'required' => false,
//            'multiple' => true,
//            'attr' => array('class' => 'combobox', 'placeholder' => "Entry Tag(s)"),
//            'query_builder' => function (EntityRepository $er) {
//                return $er->createQueryBuilder('u')
//                    ->leftJoin("u.tagTypes", "tagTypes")
//                    ->andWhere("tagTypes.name = :tagType")
//                    ->andWhere("u.type = :typedef OR u.type = :typeadd")
//                    ->orderBy("u.orderinlist","ASC")
//                    ->setParameters( array(
//                        'typedef' => 'default',
//                        'typeadd' => 'user-added',
//                        'tagType' => 'Call Log',
//                    ));
//            },
//        ));

        //echo "def=".$this->params['messageCategoryDefault']."<br>";
        //print_r($this->params['messageCategories']);
        $builder->add('messageCategory', ChoiceType::class, array(
            'label' => false,
            'required' => false,
            'choices' => $this->params['messageCategories'],
            //'choices_as_values' => true,
            'empty_data' => $this->params['messageCategoryType'],
            'attr' => array('class' => 'combobox submit-on-enter-field', 'placeholder' => "Message Type"),
        ));

//        $builder->add('mrntype', 'custom_selector', array(
//            'label'=>'MRN Type:',
//            'required' => true,
//            //'multiple' => false,
//            //'data' => 4,
//            'data' => $this->params['mrntype'],
//            'attr' => array('class' => 'ajax-combobox combobox combobox-width mrntype-combobox mrntype-exception-autogenerated'),
//            'classtype' => 'mrntype'
//        ));
        //echo "form mrntype=".$this->params['mrntype']."<br>";
//        $builder->add('mrntype', EntityType::class, array(
//            'class' => 'AppOrderformBundle:MrnType',
//            'label' => false,
//            //'required' => true,
//            'required' => false,
//            //'mapped' => false,
//            'data' => $this->params['mrntype'],
//            //'data' => 'ssss',
//            //'empty_data' => $this->params['mrntype'],
//            'attr' => array('class' => 'combobox combobox-no-width', 'placeholder' => "MRN Type", 'style'=>'width:50%;'),
//        ));
//        echo "form mrntype=".$this->params['mrntypeDefault']."<br>";
        $builder->add('mrntype', ChoiceType::class, array(
            'label' => false,
            //'required' => true,
            'required' => false,
            'choices' => $this->params['mrntypeChoices'],
            //'choices_as_values' => true,
            'data' => $this->params['mrntypeDefault'],
            //'data' => 'Epic Ambulatory Enterprise ID Number',
            //'empty_data' => $this->params['mrntypeDefault'],
            //'empty_data' => 'Epic Ambulatory Enterprise ID Number',
            'attr' => array('class' => 'combobox combobox-no-width', 'placeholder' => "MRN Type", 'style'=>'width:50%;'),
        ));

        //echo "formtype: search=".$this->params['search']."<br>";
        $builder->add('search', TextType::class, array(
            //'max_length'=>200,
            'required'=>false,
            'label' => false,
            //'data' => $this->params['search'],
            'empty_data' => $this->params['search'],
            //'data' => $this->params['search'],
            'attr' => array('class'=>'form-control submit-on-enter-field', 'placeholder' => "MRN or Last Name, First Name", 'style'=>'width:50%; float:right; height:28px;'),
        ));

        $builder->add('id', TextType::class, array(
            'required'=>false,
            'label' => false,
            'attr' => array('class'=>'form-control submit-on-enter-field', 'placeholder' => "Call Log ID", 'style'=>'height:28px;'),
        ));

        $builder->add('author', EntityType::class, array(
            'class' => User::class,
            'label' => false,
            'required' => false,
            'choice_label' => 'getUsernameOptimal',
            'attr' => array('class' => 'combobox combobox-width', 'placeholder' => "Author"),
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('u')
                    ->leftJoin("u.infos", "infos")
                    ->leftJoin("u.employmentStatus", "employmentStatus")
                    ->leftJoin("employmentStatus.employmentType", "employmentType")
                    ->andWhere("(employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL)")
                    //->andWhere("(employmentType.name NOT LIKE 'Pathology % Applicant' OR employmentType.id IS NULL)")
                    ->andWhere("(u.testingAccount = false OR u.testingAccount IS NULL)")
                    ->andWhere("(u.keytype IS NOT NULL AND u.primaryPublicUserId != 'system')")
                    ->orderBy("infos.displayName","ASC");
                //->where('u.roles LIKE :roles OR u=:user')
                //->setParameters(array('roles' => '%' . 'ROLE_SCANORDER_ORDERING_PROVIDER' . '%', 'user' => $this->params['user']));
            },
        ));

//        $builder->add('referringProvider', EntityType::class, array(
//            'class' => User::class,
//            'label' => false,
//            'required' => false,
//            'choice_label' => 'getUsernameOptimal',
//            'attr' => array('class' => 'combobox combobox-width', 'placeholder' => "Referring Provider"),
//            'query_builder' => function (EntityRepository $er) {
//                return $er->createQueryBuilder('u')
//                    ->leftJoin("u.infos", "infos")
//                    ->leftJoin("u.employmentStatus", "employmentStatus")
//                    ->leftJoin("employmentStatus.employmentType", "employmentType")
//                    ->andWhere("(employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL)")
//                    ->andWhere("(u.testingAccount = 0 OR u.testingAccount IS NULL)")
//                    ->andWhere("(u.keytype IS NOT NULL AND u.primaryPublicUserId != 'system')")
//                    ->orderBy("infos.displayName","ASC");
//                //->where('u.roles LIKE :roles OR u=:user')
//                //->setParameters(array('roles' => '%' . 'ROLE_SCANORDER_ORDERING_PROVIDER' . '%', 'user' => $this->params['user']));
//            },
//        ));
//        $builder->add('referringProvider', 'custom_selector', array(
//            'label' => false,
//            'attr' => array('class' => 'combobox combobox-width ajax-combobox-encounterReferringProvider', 'placeholder' => "Referring Provider"),
//            'required' => false,
//            'classtype' => 'singleUserWrapper'
//            //'classtype' => 'userWrapper'
//        ));
        $builder->add('referringProvider', ChoiceType::class, array(
            'label' => false,
            'required' => false,
            'attr' => array('class' => 'combobox combobox-width', 'placeholder' => "Healthcare Provider"),
            'choices' => $this->params['referringProviders'],
            //'choices_as_values' => true,
        ));

        $builder->add('referringProviderSpecialty', EntityType::class, array(
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:HealthcareProviderSpecialtiesList'] by [HealthcareProviderSpecialtiesList::class]
            'class' => HealthcareProviderSpecialtiesList::class,
            'label' => false,
            'required' => false,
            'attr' => array('class' => 'combobox', 'placeholder' => "Specialty"),
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('u')
                    ->orderBy("u.orderinlist","ASC");
            },
        ));

        $builder->add('encounterLocation', EntityType::class, array(
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Location'] by [Location::class]
            'class' => Location::class,
            'label' => false,
            'required' => false,
            'attr' => array('class' => 'combobox', 'placeholder' => "Location"),
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('u')
                    ->leftJoin("u.locationTypes", "locationTypes")
                    ->where("locationTypes.name='Encounter Location'")
                    ->andWhere("u.type = :typedef OR u.type = :typeadd")
                    ->orderBy("u.name","ASC")
                    ->setParameters( array(
                        'typedef' => 'default',
                        'typeadd' => 'user-added'
                    ));
            },
        ));

        //Institution or Collaboration
        $builder->add('institution', EntityType::class, array(
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
            'class' => Institution::class,
            'label' => false,
            'required' => false,
            'choice_label' => 'getNameShortName',
            'attr' => array('class' => 'combobox', 'placeholder' => "Institution or Collaboration"),
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('list')
                    //->leftJoin("list.locationTypes", "locationTypes")
                    ->where("list.level=0")
                    ->andWhere('list.type = :default')
                    ->setParameters( array('default'=>'default'))
                    ->orderBy("list.orderinlist","ASC");
            },
        ));

        $builder->add('messageStatus', ChoiceType::class, array(
            'label' => false,
            'required' => false,
            'choices' => $this->params['messageStatuses'],
            //'empty_data' => $this->params['messageStatus'],
            //'data' => $this->params['messageStatus'],
            'attr' => array('class' => 'combobox', 'placeholder' => "Message Status"),
            //'choices_as_values' => true,
        ));

        //Patient List
        $builder->add('patientListTitle', EntityType::class, array(
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:PatientListHierarchy'] by [PatientListHierarchy::class]
            'class' => PatientListHierarchy::class,
            'label' => false,
            'required' => false,
            'choice_label' => 'name',    //'getNodeNameWithParent',
            'attr' => array('class' => 'combobox', 'placeholder' => "Patient List"),
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('u')
                    ->where("u.level = 3")
                    ->andWhere("u.type = :typedef OR u.type = :typeadd")
                    ->andWhere("u.parent = :parentPatientListId")
                    ->orderBy("u.orderinlist","ASC")
                    ->setParameters( array(
                        'typedef' => 'default',
                        'typeadd' => 'user-added',
                        'parentPatientListId' => $this->params['parentPatientListId'],
                    ));
            },
        ));

        //Entry Body
        $builder->add('entryBodySearch', TextType::class, array(
            'required'=>false,
            'label' => false,
            'empty_data' => $this->params['entryBodySearch'],
            'attr' => array('class'=>'form-control submit-on-enter-field', 'placeholder' => "Entry Text"),
        ));

        //Attending
        $builder->add('attending', EntityType::class, array(
            'class' => User::class,
            'label' => false,
            'required' => false,
            'choice_label' => 'getUsernameOptimal',
            'attr' => array('class' => 'combobox combobox-width', 'placeholder' => "Attending"),
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('u')
                    ->leftJoin("u.infos", "infos")
                    ->leftJoin("u.employmentStatus", "employmentStatus")
                    ->leftJoin("employmentStatus.employmentType", "employmentType")
                    ->andWhere("(employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL)")
                    //->andWhere("(employmentType.name NOT LIKE 'Pathology % Applicant' OR employmentType.id IS NULL)")
                    ->andWhere("(u.testingAccount = false OR u.testingAccount IS NULL)")
                    ->andWhere("(u.keytype IS NOT NULL AND u.primaryPublicUserId != 'system')")
                    ->orderBy("infos.displayName","ASC");
                //->where('u.roles LIKE :roles OR u=:user')
                //->setParameters(array('roles' => '%' . 'ROLE_SCANORDER_ORDERING_PROVIDER' . '%', 'user' => $this->params['user']));
            },
        ));

        $builder->add('patientPhone', TextType::class, array(
            'required'=>false,
            'label' => false,
            'attr' => array('class'=>'form-control submit-on-enter-field', 'placeholder' => "Patient's Phone Number"),
        ));
        $builder->add('patientEmail', TextType::class, array(
            'required'=>false,
            'label' => false,
            'attr' => array('class'=>'form-control submit-on-enter-field', 'placeholder' => "Patient's Email"),
        ));

        $mateaphoneArr = array(
            'label' => "Search similar-sounding names:",
            'required' => false,
            //'empty_data' => $this->params['metaphone'],
            //'data' => $this->params['metaphone'],
            'attr' => array('class'=>'', 'style'=>'margin:0; width: 20px;')
        );
        if( $this->params['metaphone'] ) {
            $mateaphoneArr['empty_data'] = $this->params['metaphone'];
        }
        $builder->add('metaphone', CheckboxType::class, $mateaphoneArr);

        $sortBys = array(
            'Sort by date of entry creation, latest first' => 'sort-by-creation-date',
            'Sort by date of latest edit, latest first' => 'sort-by-latest-edit-date'
        );
        $builder->add('sortBy', ChoiceType::class, array(
            'label' => false,
            'required' => true,
            'attr' => array('class' => 'combobox', 'placeholder' => "Sort By"),
            'choices' => $sortBys,
            //'choices_as_values' => true,
        ));

        //4 Task filters
        $builder->add('task', ChoiceType::class, array(
            'label' => false,
            //'required' => true,
            'required' => false,
            'attr' => array('class' => 'combobox', 'placeholder' => "Tasks"),
            'choices' => $this->params['tasks'],
            //'choices_as_values' => true,
        ));
        $builder->add('taskType', EntityType::class, array(
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:CalllogTaskTypeList'] by [CalllogTaskTypeList::class]
            'class' => CalllogTaskTypeList::class,
            'label' => false,
            'required' => false,
            //'choice_label' => 'name',
            'attr' => array('class' => 'combobox', 'placeholder' => "Task Type"),
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('u')
                    ->andWhere("u.type = :typedef OR u.type = :typeadd")
                    ->orderBy("u.orderinlist","ASC")
                    ->setParameters( array(
                        'typedef' => 'default',
                        'typeadd' => 'user-added',
                    ));
            },
        ));
        $builder->add('taskUpdatedBy', EntityType::class, array(
            'class' => User::class,
            'label' => false,
            'required' => false,
            'choice_label' => 'getUsernameOptimal',
            'attr' => array('class' => 'combobox combobox-width', 'placeholder' => "Task Status Updated By"),
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('u')
                    ->leftJoin("u.infos", "infos")
                    ->leftJoin("u.employmentStatus", "employmentStatus")
                    ->leftJoin("employmentStatus.employmentType", "employmentType")
                    ->andWhere("(employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL)")
                    //->andWhere("(employmentType.name NOT LIKE 'Pathology % Applicant' OR employmentType.id IS NULL)")
                    ->andWhere("(u.testingAccount = false OR u.testingAccount IS NULL)")
                    ->andWhere("(u.keytype IS NOT NULL AND u.primaryPublicUserId != 'system')")
                    ->orderBy("infos.displayName","ASC");
            },
        ));
        $builder->add('taskAddedBy', EntityType::class, array(
            'class' => User::class,
            'label' => false,
            'required' => false,
            'choice_label' => 'getUsernameOptimal',
            'attr' => array('class' => 'combobox combobox-width', 'placeholder' => "Task Added By"),
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('u')
                    ->leftJoin("u.infos", "infos")
                    ->leftJoin("u.employmentStatus", "employmentStatus")
                    ->leftJoin("employmentStatus.employmentType", "employmentType")
                    ->andWhere("(employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL)")
                    //->andWhere("(employmentType.name NOT LIKE 'Pathology % Applicant' OR employmentType.id IS NULL)")
                    ->andWhere("(u.testingAccount = false OR u.testingAccount IS NULL)")
                    ->andWhere("(u.keytype IS NOT NULL AND u.primaryPublicUserId != 'system')")
                    ->orderBy("infos.displayName","ASC");
            },
        ));

        //Use AppOrderformBundle:CalllogAttachmentTypeList
//        $attachmentTypes = array(
//            'With attachments' => 'With attachments',
//            'Without attachments' => 'Without attachments',
//            'Image' => 'Image',
//            'Document' => 'Document',
//            '' => '',
//            '' => '',
//            '' => '',
//            '' => '',
//            '' => '',
//        );
        $builder->add('attachmentType', ChoiceType::class, array(
            'label' => false,
            'required' => false,
            'attr' => array('class' => 'combobox', 'placeholder' => "Attachment Type"),
            'choices' => $this->params['attachmentTypesChoice'],
            'multiple' => false,
            //'choices_as_values' => true,
        ));
        
        //Accession's Initial Communication (referringProviderCommunicationFilter)
        //echo "defaultCommunication=".$this->params['defaultCommunication']."<br>";
        $builder->add('initialCommunication', EntityType::class, array(
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:HealthcareProviderCommunicationList'] by [HealthcareProviderCommunicationList::class]
            'class' => HealthcareProviderCommunicationList::class,
            'label' => false,
            'required' => false,
            //'mapped' => false,
            'data' => $this->params['defaultCommunication'],
            //'empty_data' => $this->params['defaultCommunication'],
            'attr' => array('class' => 'combobox', 'placeholder' => "Initial Communication"),
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('u')
                    ->orderBy("u.orderinlist","ASC");
            },
        ));
//        //same as mrntype
//        $builder->add('referringProviderCommunicationFilter', ChoiceType::class, array(
//            'label' => false,
//            'required' => false,
//            'choices' => $this->params['referringProviderCommunicationChoices'],
//            'data' => $this->params['defaultCommunication'],
//            'attr' => array('class' => 'combobox combobox-no-width', 'placeholder' => "Initial Communication"),
//        ));
        
        $builder->add( 'accessionType', EntityType::class, array(
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:AccessionType'] by [AccessionType::class]
            'class' => AccessionType::class,
            //'choice_label' => 'name',
            'label' => false,
            'required' => false,
            'multiple' => false,
            //'mapped' => false,
            //'data' => $this->params['defaultAccessionType'],
            'attr' => array('class' => 'combobox combobox-width accessiontype-combobox skip-server-populate accessiontype', 'placeholder' => "Accession Type"),
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

        $builder->add('accessionNumber', null, array(
            'label' => false,
            'required' => false,
            'attr' => array('class' => 'form-control keyfield accession-mask', 'placeholder' => "Accession Number")
        ));       


    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'form_custom_value' => null,
            'csrf_protection' => false,
        ));
    }

    public function getBlockPrefix(): string
    {
        return 'filter';
    }
}
