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

namespace Oleg\CallLogBundle\Util;


class CallLogUtilForm
{
    protected $em;
    protected $sc;
    protected $container;

    public function __construct( $em, $sc, $container ) {
        $this->em = $em;
        $this->sc = $sc;
        $this->container = $container;
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

    public function getEncounterPatientInfoHtml( $encounter, $status )
    {
        $userServiceUtil = $this->container->get('user_service_utility');
        $status = "valid";
        $space = "&nbsp;";
        $tabspace = $space . $space . $space;

        $html = $this->getTrSection("Encounter Info");
        //$html = "<p>" . "<i>" . "Encounter Info" . "</i>" . "</p>";

        //$html .= "<p>" . $tabspace . "Encounter Number: " . $encounter->obtainEncounterNumber() . "</p>";
        $html .= $this->getTrField("Encounter Number",$encounter->obtainEncounterNumber());

        $date = $encounter->obtainValidField('date');
        $encounterDateStr = $userServiceUtil->getSeparateDateTimeTzStr($date->getField(), $date->getTime(), $date->getTimezone(), true, false);
        //$html .= "<p>" . $tabspace . "Encounter Date: " . $encounterDateStr . "</p>";
        $html .= $this->getTrField("Encounter Date",$encounterDateStr);

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
            $html .= $this->getTrField("Referring Provider ", $referringProviderInfo);
        }

        //Location
        $location = $encounter->obtainLocationInfo();
        if( $location ) {
            $html .= $this->getTrField("Encounter Location ", $location);
        }

        //Update Patient Info
        $lastname = $encounter->obtainValidField('patlastname');
        $firstname = $encounter->obtainValidField('patfirstname');
        $middlename = $encounter->obtainValidField('patmiddlename');
        $suffix = $encounter->obtainValidField('patsuffix');
        $sex = $encounter->obtainValidField('patsex');
        if( $lastname || $firstname || $middlename || $suffix || $sex ) {
            $html .= $this->getTrSection("Update Patient Info");
            $html .= $this->getTrField("Patient's Last Name (at the time of encounter) ", $lastname);
            $html .= $this->getTrField("Patient's First Name (at the time of encounter) ", $firstname);
            $html .= $this->getTrField("Patient's Middle Name (at the time of encounter) ", $middlename);
            $html .= $this->getTrField("Patient's Suffix (at the time of encounter) ", $suffix);
            $html .= $this->getTrField("Patient's Gender (at the time of encounter) ", $sex);
        }

        $html =
            '<br><p>'.
            '<table class="table">'.
            $html.
            '</table>'.
            '</p><br>';

        return $html;
    }

    public function getEntryHtml( $message, $status ) {
        $userServiceUtil = $this->container->get('user_service_utility');
        $space = "&nbsp;";
        $tabspace = $space . $space . $space;

        //$html = "<p>"."<i>"."Entry"."</i>"."</p>";
        $html = $this->getTrSection("Entry");

        $messageCategory = $message->getMessageCategory();
        if( $messageCategory ) {
            $html .= $this->getTrField("Message Type ", $messageCategory->getTreeName());
        }

        $messageStatus = $message->getMessageStatus();
        $html .= $this->getTrField("Message Status ", $messageStatus);

        $version = $message->getVersion();
        $html .= $this->getTrField("Message Version ", $version);

        $html =
            '<br><p>'.
            '<table class="table">'.
            $html.
            '</table>'.
            '</p><br>';

        return $html;
    }

}