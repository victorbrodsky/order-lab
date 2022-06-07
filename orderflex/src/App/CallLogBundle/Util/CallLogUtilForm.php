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

/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 7/19/2017
 * Time: 2:10 PM
 */

namespace App\CallLogBundle\Util;


use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;

class CallLogUtilForm
{
    protected $em;
    protected $container;
    protected $security;

    public function __construct( EntityManagerInterface $em, ContainerInterface $container, Security $security ) {
        $this->em = $em;
        $this->container = $container;
        $this->security = $security;
    }


    public function getTable( $html ) {
        $html =
            '<br><p>'.
            //'<div class="watermark-text">OLD VERSION</div>'.
            '<table class="table">'.
            $html.
            '</table>'.
            '</p><br>';

        return $html;
    }
    public function getTrSection( $label ) {
        $html =
            '<tr style="border:none;">' .
            '<td style="border:none;">' . "<i>" . $label . "</i>" . '</td>' .
            '<td style="border:none;"></td>' .
            '</tr style="border:none;">';

        return $html;
    }
    public function getTrField( $label, $value ) {
        $space = "&nbsp;";
        $tabspace = $space . $space . $space;
        $html =
            '<tr style="border:none;">' .
            '<td style="width:20%; border:none;">' . $tabspace.$label . '</td>' .
            '<td style="width:80%; border:none;">' . $value . '</td>' .
            '</tr style="border:none;">';

        return $html;
    }
    public function getTrFieldText( $label, $value ) {
        $space = "&nbsp;";
        $tabspace = $space . $space . $space;
        $html =
            '<tr style="border:none;">' .
            '<td style="width:20%; border:none;">' . $tabspace.$label . '</td>' .
            '<td style="width:80%; border:none;">' . '<textarea>' . $value . '</textarea>' . '</td>' .
            '</tr style="border:none;">';

        return $html;
    }

    public function getEncounterPatientInfoHtml( $encounter, $status=NULL )
    {
        //exit("encounter ID=".$encounter->getId());
        $userServiceUtil = $this->container->get('user_service_utility');

        $panelName = "Encounter Info";
        //$html = $this->getTrSection("Encounter Info");
        $html = "";

        $html .= $this->getTrField("Encounter Number",$encounter->obtainEncounterNumber());

        $date = $encounter->obtainValidField('date');
        if( !$date ) {
            $dates = $encounter->getDate();
            //echo "dates count=".count($dates)."<br>";
            if( count($dates) > 0 ) {
                $date = $dates->first();
            }
        }
        if( $date ) {
            //echo "Encounter date=".$date."<br>";
            $dateField = $date->getField();
            $dateTime = $date->getTime();
            $dateTimezone = $date->getTimezone();
            if( !$dateTimezone ) {
                $dateTimezone = "America/New_York";
            }

            //echo "Encounter dateTimezone=".$dateTimezone."<br>";
            $user = $this->security->getUser();
            $user_tz = $user->getPreferences()->getTimezone();
            if( !$user_tz ) {
                $user_tz = "America/New_York";
            }

            if(0) {
                //show as original submitted timezone
                //$dateField = $date->getField();
                //$dateTime = $date->getTime();
                //$dateTimezone = $date->getTimezone();
                $encounterDateStr = $userServiceUtil->getSeparateDateTimeTzStr($dateField, $dateTime, $dateTimezone, true, false);
            } else {
                //show it in the user's timezone
                //echo "user=$user <br>";
                $userServiceUtil = $this->container->get('user_service_utility');
                //echo "dateTime=" . $dateTime->format("h:i (T)") . "<br>";
                //echo "dateField=" . $dateField->format("m/d/Y (T)") . "<br>";

                //$newDateTime = new \DateTime(null, new \DateTimeZone($user_tz));
                $newDateTime = new \DateTime("now", new \DateTimeZone($user_tz));
                //echo "1newDateTime=".$newDateTime->format("d-m-Y")."<br>";

                //1) construct DateTime with $dateField and $dateTime
                $newDateTime->setDate($dateField->format('Y'), $dateField->format('m'), $dateField->format('d'));
                $newDateTime->setTime($dateTime->format('H'), $dateTime->format('i'));
                $newDateTime->setTimezone(new \DateTimeZone($user_tz));
                //echo "newDateTime=" . $newDateTime->format("m/d/Y H:i (T)") . "<br>";

                //$dateField = new \DateTime($newDateTime->format('Y-m-d H:i'), new \DateTimeZone('UTC') );

                $newDateTime = new \DateTime("now", new \DateTimeZone($user_tz));
                //echo "2newDateTime=".$newDateTime->format("d-m-Y")."<br>";
                //exit ("user_tz=$user_tz <br>"); //testing

                //2) convert to $dateTimezone
                $newDateTimeTz = $userServiceUtil->convertToTimezone($newDateTime, $dateTimezone);
                //echo "dateFieldTz=" . $newDateTimeTz->format("m/d/Y H:i (T)") . "<br>";

                //3) convert to user's tz
                //$dateFieldTz = $userServiceUtil->convertToUserTimezone($newDateTimeTz, $user);
                //echo "dateFieldTz=".$dateFieldTz->format("m/d/Y (T)")."<br>";

                //original tz
                $encounterDateStr = $userServiceUtil->getSeparateDateTimeTzStr($dateField, $dateTime, $dateTimezone, true, false);

                //add user tz
                $tzAbbreviation = (new \DateTime($user_tz))->format('T');
                $encounterDateStr = $encounterDateStr . "; " . $newDateTimeTz->format("m/d/Y") . " at " . $newDateTimeTz->format("h:i a") . " (" . $user_tz . ", " . $tzAbbreviation . ")";
            }

            //$dateTimeTz = $userServiceUtil->convertToUserTimezone($dateTime,$user);
            //echo "dateTimeTz=".$dateTimeTz->format("h:i (T)")."<br>";

            //$user_tz = $user->getPreferences()->getTimezone();
            //echo "user_tz=".$user_tz."<br>";

            //$encounterDateStr = $dateFieldTz->format("m/d/Y") . " at " . $dateTimeTz->format("h:i a") . ", " . $user_tz . " (". $dateFieldTz->format("T") . ")";
            //$encounterDateStr = $dateFieldTz->format("m/d/Y") . " at " . $dateFieldTz->format("h:i a") . ", " . $user_tz . " (". $dateFieldTz->format("T") . ")";

            //show it as the entered timezone
            //$encounterDateStr = $userServiceUtil->getSeparateDateTimeTzStr($dateField, $dateTime, $dateTimezone, true, false);
            //$datetimeTz = $userServiceUtil->convertToTimezone($date,$dateTimezone);
            //$modifiedOnUserTz = $userServiceUtil->convertToUserTimezone($dateField,$user);
            //$modifiedOnUserTzStr = $modifiedOnUserTz->format("m/d/Y h:i (T)");
            //$encounterDateStr = $encounterDateStr . " (" . $modifiedOnUserTzStr . ")";

        } else {
            $dateField = null;
            $dateTime = null;
            $dateTimezone = null;
            $encounterDateStr = null;
        }
        $html .= $this->getTrField("Encounter Date", $encounterDateStr);

        $html .= $this->getTrField("Encounter Status",$encounter->getEncounterStatus());

        $encounterInfoType = $encounter->obtainValidField('encounterInfoTypes');
        $html .= $this->getTrField("Encounter Type",$encounterInfoType);

        $provider = $encounter->getProvider();
        $html .= $this->getTrField("Provider",$provider);

        //attendingPhysicians
        $attendingPhysician = $encounter->obtainAttendingPhysicianInfo();
        if( $attendingPhysician ) {
            $html .= $this->getTrField("Attending Physician", $attendingPhysician);
        }

        //referringProviderInfo
        $referringProviderInfo = $encounter->obtainReferringProviderInfo();
        if( $referringProviderInfo ) {
            $html .= $this->getTrField("Healthcare Provider ", $referringProviderInfo);
        }

        foreach( $encounter->getReferringProviders() as $refProvider ) {
            $communication = $refProvider->getReferringProviderCommunication();
            if( $communication ) {
                $html .= $this->getTrField("Initial Communication ", $communication->getName());
            }
        }

        //Location
        $location = $encounter->obtainLocationInfo();
        if( $location ) {
            $html .= $this->getTrField("Encounter Location ", $location);
        }

        //Update Patient Info
        $lastname = trim((string)$encounter->obtainValidField('patlastname'));
        $firstname = trim((string)$encounter->obtainValidField('patfirstname'));
        $middlename = trim((string)$encounter->obtainValidField('patmiddlename'));
        $suffix = trim((string)$encounter->obtainValidField('patsuffix'));
        $sex = trim((string)$encounter->obtainValidField('patsex'));
        //echo "### [$lastname] || [$firstname] || [$middlename] || [$suffix] || [$sex] <br>";
        if( $lastname || $firstname || $middlename || $suffix || $sex ) {
            $html .= $this->getTrSection("Update Patient Info");
            $html .= $this->getTrField("Patient's Last Name (at the time of encounter) ", $lastname);
            $html .= $this->getTrField("Patient's First Name (at the time of encounter) ", $firstname);
            $html .= $this->getTrField("Patient's Middle Name (at the time of encounter) ", $middlename);
            $html .= $this->getTrField("Patient's Suffix (at the time of encounter) ", $suffix);
            $html .= $this->getTrField("Patient's Gender (at the time of encounter) ", $sex);
        }

//        $html =
//            '<br><p>'.
//            '<table class="table">'.
//            $html.
//            '</table>'.
//            '</p><br>';
//        return $html;

        $html = $this->getTable($html);

        //Put a grey collapsed-by-default accordion around the “Entry” rows
        $panelId = 'calllog-encounter-info-panel';
        $html = $this->wrapInPanel($html,$panelName,$panelId);

        return $html;
    }

    public function getAccessionInfoHtml( $message, $status )
    {
        //$userServiceUtil = $this->container->get('user_service_utility');
        $panelName = "Accession Info";
        $html = $this->getTrSection("Accession Info");

        $accessions = $message->getAccession();

        if( count($accessions) > 0 ) {
            $html = "";
            $accession = $accessions[0];
            $html .= $this->getTrField("Accession Number",$accession->obtainFullObjectName());
            //return $this->getTable($html);
            $html = $this->getTable($html);

            //Put a grey collapsed-by-default accordion around the “Entry” rows
            $panelId = 'calllog-accession-info-panel';
            $html = $this->wrapInPanel($html,$panelName,$panelId);

            return $html;
        }

        return NULL;
    }

    public function getEntryHtml( $message, $status ) {

        $panelName = "Entry Info";

        //$html = $this->getTrSection($panelName);
        $html = "";

        $messageCategory = $message->getMessageCategory();
        if( $messageCategory ) {
            $html .= $this->getTrField("Message Type ", $messageCategory->getTreeName());
        }

        $messageStatus = $message->getMessageStatus();
        $html .= $this->getTrField("Message Status ", $messageStatus);

        $version = $message->getVersion();
        $html .= $this->getTrField("Message Version ", $version);

        $messageTitle = $message->getMessageTitle();
        $html .= $this->getTrField("Form Title ", $messageTitle);

        $formVersionsInfo = $message->getFormVersionsInfo();
        $html .= $this->getTrField("Form(s) ", $formVersionsInfo);

        //Amendment Reason
        $amendmentReason = $message->getAmendmentReason();
        if( $amendmentReason ) {
            $html .= $this->getTrField("Amendment Reason ", $amendmentReason);
        }
//        if( intval($version) > 1 ) {
//            if( $this->entity->getMessageStatus()->getName()."" != "Draft" || ($this->params['cycle'] != "edit" && $this->params['cycle'] != "amend" ) ) {
//                $amendmentReason = $message->getAmendmentReason();
//                if( $amendmentReason ) {
//                    $html .= $this->getTrField("Amendment Reason ", $amendmentReason);
//                }
//            }
//        }

        //Patient List
        $calllogEntryMessage = $message->getCalllogEntryMessage();
        if( $calllogEntryMessage && $calllogEntryMessage->getAddPatientToList() ) {
            $patientLists = $calllogEntryMessage->getPatientLists();
            if( count($patientLists) > 0 ) {
                $html .= $this->getTrSection("Patient List");
                foreach( $patientLists as $patientList ) {
                    $html .= $this->getTrField("List Title ", $patientList->getName());
                }
            }
        }

        //Accession List
        if( $message->getAddAccessionToList() ) {
            $accessionLists = $message->getAccessionLists();
            if (count($accessionLists) > 0) {
                $html .= $this->getTrSection("Accession List");
                foreach ($accessionLists as $accessionList) {
                    $html .= $this->getTrField("List Title ", $accessionList->getName());
                }
            }
        }


//        $html =
//            '<br><p>'.
//            '<table class="table">'.
//            $html.
//            '</table>'.
//            '</p><br>';
//        return $html;

        //return $this->getTable($html);

        $html = $this->getTable($html);

        //append the value from “Message Type” =>
        //if the Message Type is “Note => Encounter Note => Pathology Call Log Entry => Transfusion Medicine => Other”,
        //show the accordion title as “Entry Info (Other: Transfusion Medicine)”
        //message.messageCategory.getNodeNameWithParents
        $callIssue = $message->getMessageCategory()->getNodeNameWithParents();
        if( $callIssue ) {
            $panelName = $panelName . " (" . $callIssue . ")";
        }

        //Put a grey collapsed-by-default accordion around the “Entry” rows
        $panelId = 'calllog-entry-info-panel';
        $html = $this->wrapInPanel($html,$panelName,$panelId);

        return $html;
    }

    public function wrapInPanel( $html, $panelName, $panelId, $panelType='panel-default', $collapseIn='' ) {
        //$panelType='panel-primary';
        $panelHtml = '<div class="panel '.$panelType.'">
            <div class="panel-heading">
                <h4 class="panel-title">
                    <a data-toggle="collapse" href="#'.$panelId.'">
                        '.$panelName.'
                    </a>
                </h4>
            </div>
            <div id="'.$panelId.'" class="panel-collapse collapse '.$collapseIn.'">
                <div class="panel-body">';

        $panelHtml = $panelHtml . $html;

        $panelHtml = $panelHtml .
            '</div> <!-- panel-body -->
          </div> <!-- panel-collapse -->
        </div> <!-- panel panel-primary -->';

        return $panelHtml;
    }

    public function getEntryTagsHtml( $message, $status ) {

        $calllogEntryMessage = $message->getCalllogEntryMessage();
        if( !$calllogEntryMessage ) {
            return null;
        }

        $panelName = "Search aides and time tracking";

        $html = "";

        //$html = $this->getTrSection("Cache (shown only to Administrator)");
        //$html .= $this->getTrFieldText("Cached entry content in XML", $message->getFormnodesCache());
        //$html .= $this->getTrField("Cached patient mrn content", $message->getPatientMrnCache());
        //$html .= $this->getTrField("Cached patient name content", $message->getPatientNameCache());

        //$html .= $this->getTrSection("Search aides and time tracking");

        //Deprecated (entry tags attached to calllogEntryMessage)
        if(0) {
            $entryTags = $calllogEntryMessage->getEntryTags();
            $entryTagsArr = array();
            foreach ($entryTags as $entryTag) {
                $entryTagsArr[] = $entryTag->getName();
            }
            $html .= $this->getTrField("Call Log Entry Tag(s) (Deprecated) ", implode("; ", $entryTagsArr));
        }

        //New entry tags attached to message
        $entryTags = $message->getEntryTags();
        $entryTagsArr = array();
        foreach( $entryTags as $entryTag ) {
            $entryTagsArr[] = $entryTag->getName();
        }
        $html .= $this->getTrField("Call Log Entry Tag(s) ", implode("; ",$entryTagsArr));

        $timeSpentMinutes = $calllogEntryMessage->getTimeSpentMinutes();
        $html .= $this->getTrField("Amount of Time Spent in Minutes ", $timeSpentMinutes);


//        $html =
//            '<br><p>'.
//            '<table class="table">'.
//            $html.
//            '</table>'.
//            '</p><br>';
//        return $html;

        $html = $this->getTable($html);

        //Put a grey collapsed-by-default accordion around the “Entry” rows
        $panelId = 'calllog-search-aides-time-tracking-panel';
        $html = $this->wrapInPanel($html,$panelName,$panelId);

        return $html;
    }

    public function getCalllogAuthorsHtml( $message, $sitename ) {

        $user = $this->security->getUser();

        $messageVersion = intval($message->getVersion());

        if( $messageVersion > 1) {
            $name = "Authors";
        } else {
            $name = "Author";
        }

        $html = $this->getTrSection($name);

        $router = $this->container->get('router');
        $userServiceUtil = $this->container->get('user_service_utility');
        $userSecurityUtil = $this->container->get('user_security_utility');

        //Submitter
        $submitter = $message->getProvider();
        if( $submitter ) {
            $providerUrl = $router->generate($sitename . '_showuser', array('id' => $submitter->getId()), UrlGeneratorInterface::ABSOLUTE_URL);
            $hreflink = '<a target="_blank" href="'.$providerUrl.'">'.$submitter->getUsernameOptimal().'</a>';
            $html .= $this->getTrField("Submitter ", $hreflink);
        }

        //Submitted on
        $html .= $this->getTrField("Submitted on ", $userServiceUtil->getOrderDateStr($message));

        if(0) {
            //Submitter role(s) at submission time
            $firstEditorInfo = $message->getEditorInfos()->first();
            if ($firstEditorInfo) {
                if (count($firstEditorInfo->getModifierRoles()) > 0) {
                    $editorRoles = $userSecurityUtil->getRolesByRoleNames($firstEditorInfo->getModifierRoles());
                    $html .= $this->getTrField("Submitter role(s) at submission time ", $editorRoles);
                } else {
                    $html .= $this->getTrField("Submitter role(s) at submission time ", "No Roles");
                }
            }

            //Message Status
            $messageStatus = $message->getMessageStatus()->getName();
            //$html .= $this->getTrField("Message Status ", $messageStatus);

            //Signed
            $messageSigneeInfo = $message->getSigneeInfo();
            if (strpos((string)$messageStatus, 'Signed') !== false && $messageSigneeInfo) {
                if ($messageSigneeInfo->getModifiedBy()) {
                    $authorHref = $router->generate($sitename . '_showuser', array('id' => $messageSigneeInfo->getModifiedBy()->getId()), UrlGeneratorInterface::ABSOLUTE_URL);
                    $hreflink = '<a target="_blank" href="' . $authorHref . '">' . $messageSigneeInfo->getModifiedBy()->getUsernameOptimal() . '</a>';
                    $html .= $this->getTrField("Signed by ", $hreflink);
                }
                if ($messageSigneeInfo->getModifiedOn()) {
                    $messageModifyDate = $messageSigneeInfo->getModifiedOn();
                    $messageModifyDateTz = $userServiceUtil->convertFromUtcToUserTimezone($messageModifyDate, $user);
                    $signedDate = $messageModifyDateTz->format('m/d/Y') . " at " . $messageModifyDateTz->format('h:i a (T)');
                    $html .= $this->getTrField("Signed on ", $signedDate);
                }
                if (count($messageSigneeInfo->getModifierRoles()) > 0) {
                    $signeeRoles = $userSecurityUtil->getRolesByRoleNames($messageSigneeInfo->getModifierRoles());
                    $html .= $this->getTrField("Signee role(s) at signature time ", $signeeRoles);
                } else {
                    $html .= $this->getTrField("Signee role(s) at signature time ", "No roles");
                }
            }
        }

        //IF "Message Version">1 (2 or more), display the following three fields:
        if( $messageVersion > 1 ) {
            //echo "messageVersion=$messageVersion<br>";
            //echo "count=".count($message->getEditorInfos())."<br>";
            $lastEditorInfo = $message->getEditorInfos()->last();
            if( $lastEditorInfo ) {
                $modifiedBy = $lastEditorInfo->getModifiedBy();
                if( $modifiedBy ) {
                    $authorHref = $router->generate($sitename . '_showuser', array('id' => $modifiedBy->getId()), UrlGeneratorInterface::ABSOLUTE_URL);
                    $hreflink = '<a target="_blank" href="' . $authorHref . '">' . $modifiedBy->getUsernameOptimal() . '</a>';
                    $html .= $this->getTrField("Last edited by ", $hreflink);
                }

                $modifiedOn = $lastEditorInfo->getModifiedOn();
                if( $modifiedOn ) {
                    $modifiedOn = $userServiceUtil->convertFromUtcToUserTimezone($modifiedOn,$user);
                    $editedDate = $modifiedOn->format('m/d/Y') . " at " . $modifiedOn->format('h:i a (T)');
                    $html .= $this->getTrField("Last edited on ", $editedDate);
                }

                $modifierRoles = $lastEditorInfo->getModifierRoles();
                if( count($modifierRoles) > 0 ) {
                    $editorRoles = $userSecurityUtil->getRolesByRoleNames($modifierRoles);
                    $html .= $this->getTrField("Editor role(s) at edit submission time ", $editorRoles);
                } else {
                    $html .= $this->getTrField("Editor role(s) at edit submission time ", "No roles");
                }
            }
        }

//        $html =
//            '<br><hr><p>'.
//            '<table class="table">'.
//            $html.
//            '</table>'.
//            '</p><br>';
//        return $html;

        return $this->getTable($html);
    }

    public function getCalllogAuthorRolesHtml( $message, $sitename ) {
        if( false == $this->security->isGranted("ROLE_CALLLOG_ADMIN") ) {
            return NULL;
        }

        $router = $this->container->get('router');
        $userServiceUtil = $this->container->get('user_service_utility');
        $userSecurityUtil = $this->container->get('user_security_utility');
        $user = $this->security->getUser();

        $html = "";

        //Submitter role(s) at submission time
        $firstEditorInfo = $message->getEditorInfos()->first();
        if( $firstEditorInfo ) {
            if( count($firstEditorInfo->getModifierRoles()) > 0 ) {
                $editorRoles = $userSecurityUtil->getRolesByRoleNames($firstEditorInfo->getModifierRoles());
                $html .= $this->getTrField("Submitter role(s) at submission time: ", $editorRoles);
            } else {
                $html .= $this->getTrField("Submitter role(s) at submission time: ", "No Roles");
            }

//            //Put a grey collapsed-by-default accordion around the “Entry” rows
//            $panelId = 'calllog-submitter-role';
//            $panelName = "Submitter roles at submission time";
//            $html = $this->wrapInPanel($html,$panelName,$panelId);
        }

        //Message Status
        $messageStatus = $message->getMessageStatus()->getName();
        //$html .= $this->getTrField("Message Status ", $messageStatus);

        //Signed
        $messageSigneeInfo = $message->getSigneeInfo();
        if( strpos((string)$messageStatus, 'Signed') !== false && $messageSigneeInfo ) {
            if ($messageSigneeInfo->getModifiedBy()) {
                $authorHref = $router->generate($sitename . '_showuser', array('id' => $messageSigneeInfo->getModifiedBy()->getId()), UrlGeneratorInterface::ABSOLUTE_URL);
                $hreflink = '<a target="_blank" href="' . $authorHref . '">' . $messageSigneeInfo->getModifiedBy()->getUsernameOptimal() . '</a>';
                $html .= $this->getTrField("Signed by ", $hreflink);
            }
            if ($messageSigneeInfo->getModifiedOn() ) {
                $messageModifyDate = $messageSigneeInfo->getModifiedOn();
                $messageModifyDateTz = $userServiceUtil->convertFromUtcToUserTimezone($messageModifyDate,$user);
                $signedDate = $messageModifyDateTz->format('m/d/Y') . " at " . $messageModifyDateTz->format('h:i a (T)');
                $html .= $this->getTrField("Signed on ", $signedDate);
            }
            if (count($messageSigneeInfo->getModifierRoles()) > 0) {
                $signeeRoles = $userSecurityUtil->getRolesByRoleNames($messageSigneeInfo->getModifierRoles());
                $html .= $this->getTrField("Signee role(s) at signature time: ", $signeeRoles);
            } else {
                $html .= $this->getTrField("Signee role(s) at signature time: ", "No roles");
            }
        }

        //Put a grey collapsed-by-default accordion around the “Entry” rows
        $panelId = 'calllog-submitter-role';
        $panelName = "Submitter roles at submission time";
        $html = $this->wrapInPanel($html,$panelName,$panelId);

        return $html;
    }

}