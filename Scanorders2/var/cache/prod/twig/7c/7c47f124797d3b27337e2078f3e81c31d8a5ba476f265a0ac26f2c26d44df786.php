<?php

/* OlegUserdirectoryBundle:Admin:index.html.twig */
class __TwigTemplate_cfb345660ebed545054391a4c31da5f5dbefa7b68e30cc7bc9a27902b6277072 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        // line 17
        $this->parent = $this->loadTemplate("OlegUserdirectoryBundle::Default/base.html.twig", "OlegUserdirectoryBundle:Admin:index.html.twig", 17);
        $this->blocks = array(
            'title' => array($this, 'block_title'),
            'content' => array($this, 'block_content'),
        );
    }

    protected function doGetParent(array $context)
    {
        return "OlegUserdirectoryBundle::Default/base.html.twig";
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $this->parent->display($context, array_merge($this->blocks, $blocks));
    }

    // line 20
    public function block_title($context, array $blocks = array())
    {
        // line 21
        echo "    ";
        echo twig_escape_filter($this->env, (isset($context["listmanager_title"]) ? $context["listmanager_title"] : null), "html", null, true);
        echo "
";
    }

    // line 24
    public function block_content($context, array $blocks = array())
    {
        // line 25
        echo "
    <h3 class=\"text-info\">";
        // line 26
        echo twig_escape_filter($this->env, (isset($context["listmanager_title"]) ? $context["listmanager_title"] : null), "html", null, true);
        echo "</h3>

    <br><br>

    <p>
        <a href=\"";
        // line 31
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("platformlistmanager-list");
        echo "\">Platform List Manager Root List</a>
    </p>

    <p>
        <a href=\"";
        // line 35
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("sites-list");
        echo "\">Sites</a>
    </p>

    <p>
        <a href=\"";
        // line 39
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("sourcesystems-list");
        echo "\">Source Systems</a>
    </p>

    <p>
        <a href=\"";
        // line 43
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("role-list");
        echo "\">Roles</a>
    </p>

    <p>
        <a href=\"";
        // line 47
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("roleattributes-list");
        echo "\">Role Attributes</a>
    </p>

    <p>
        <a href=\"";
        // line 51
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("permission-list");
        echo "\">Permissions List</a>
    </p>

    <p>
        <a href=\"";
        // line 55
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("permissionobject-list");
        echo "\">Permission Objects List</a>
    </p>

    <p>
        <a href=\"";
        // line 59
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("permissionaction-list");
        echo "\">Permission Actions List</a>
    </p>

    ";
        // line 63
        echo "        ";
        // line 64
        echo "    ";
        // line 65
        echo "
    <hr>
    <h4 class=\"text-info\">Institution Hierarchy</h4>
    <p>
        <a href=\"";
        // line 69
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("institutiontypes-list");
        echo "\">Institution Types</a>
    </p>
    <p>
        <a href=\"";
        // line 72
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("positiontypes-list");
        echo "\">Position Types</a>
    </p>
    <p>
        <a href=\"";
        // line 75
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("organizationalgrouptypes-list");
        echo "\">Organizational Group Types</a>
    </p>
    ";
        // line 78
        echo "        ";
        // line 79
        echo "    ";
        // line 80
        echo "    <p>
        <a href=\"";
        // line 81
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("institutions-list");
        echo "\">Flat Institution Tree</a>
    </p>
    ";
        // line 84
        echo "        ";
        // line 85
        echo "    ";
        // line 86
        echo "    <p>
        <a href=\"";
        // line 87
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("collaborationtypes-list");
        echo "\">Collaboration Types</a>
    </p>


    <hr>
    <h4 class=\"text-info\">Comment Type Hierarchy</h4>
    <p>
        <a href=\"";
        // line 94
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("commentgrouptypes-list");
        echo "\">Profile Comment Group Types</a>
    </p>
    ";
        // line 97
        echo "        ";
        // line 98
        echo "    ";
        // line 99
        echo "    <p>
        <a href=\"";
        // line 100
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("commenttypes-list");
        echo "\">Flat Comment Types Tree</a>
    </p>

    <hr>
    <h4 class=\"text-info\">Form Hierarchy</h4>
    <p>
        <a href=\"";
        // line 106
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("formnodes-list");
        echo "\">Flat Form Tree</a>
    </p>
    <p>
        <a href=\"";
        // line 109
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("objecttypes-list");
        echo "\">Object Types</a>
    </p>
    <p>
        <a href=\"";
        // line 112
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("objecttypetexts-list");
        echo "\">Object Type Texts</a>
    </p>
    <p>
        <a href=\"";
        // line 115
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("objecttypestrings-list");
        echo "\">Object Type String</a>
    </p>
    <p>
        <a href=\"";
        // line 118
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("objecttypedropdowns-list");
        echo "\">Object Type Dropdown</a>
    </p>
    <p>
        <a href=\"";
        // line 121
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("objecttypedatetimes-list");
        echo "\">Object Type DateTime</a>
    </p>
    <p>
        <a href=\"";
        // line 124
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("objecttypecheckboxs-list");
        echo "\">Object Type Checkbox</a>
    </p>
    <p>
        <a href=\"";
        // line 127
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("objecttyperadiobuttons-list");
        echo "\">Object Type Radio Button</a>
    </p>

    <hr>

    <p>
        <a href=\"";
        // line 133
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("weekdays-list");
        echo "\">Days of the Week</a>
    </p>
    <p>
        <a href=\"";
        // line 136
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("months-list");
        echo "\">Months</a>
    </p>
    <p>
        <a href=\"";
        // line 139
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("sexes-list");
        echo "\">Genders</a>
    </p>
    <p>
        <a href=\"";
        // line 142
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("lifeforms-list");
        echo "\">Life Forms</a>
    </p>

    <p>
        <a href=\"";
        // line 146
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("languages-list");
        echo "\">Languages</a>
    </p>

    <p>
        <a href=\"";
        // line 150
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("locales-list");
        echo "\">Locales</a>
    </p>

    <p>
        <a href=\"";
        // line 154
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("cities-list");
        echo "\">Cities</a>
    </p>

    <p>
        <a href=\"";
        // line 158
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("countries-list");
        echo "\">Countries</a>
    </p>

    <p>
        <a href=\"";
        // line 162
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("states-list");
        echo "\">States</a>
    </p>

    <p>
        <a href=\"";
        // line 166
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("boardcertifications-list");
        echo "\">Board Certified Specialties</a>
    </p>
    <p>
        <a href=\"";
        // line 169
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("certifyingboardorganizations-list");
        echo "\">Certifying Board Organizations</a>
    </p>


    <p>
        <a href=\"";
        // line 174
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("employmenttypes-list");
        echo "\">Employment Types</a>
    </p>

    <p>
        <a href=\"";
        // line 178
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("employmentterminations-list");
        echo "\">Employment Termination Reasons</a>
    </p>

    <p>
        <a href=\"";
        // line 182
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("loggereventtypes-list");
        echo "\">Event Log Types</a>
    </p>

    <p>
        <a href=\"";
        // line 186
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("eventobjecttypes-list");
        echo "\">Event Log Object Types</a>
    </p>

    <p>
        <a href=\"";
        // line 190
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("usernametypes-list");
        echo "\">Primary Public User ID Types</a>
    </p>

    <p>
        <a href=\"";
        // line 194
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("userwrappers-list");
        echo "\">User Wrappers</a>
    </p>

    <p>
        <a href=\"";
        // line 198
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("documenttypes-list");
        echo "\">Document Types</a>
    </p>

    <p>
        <a href=\"";
        // line 202
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("linktypes-list");
        echo "\">Link Types</a>
    </p>

    <p>
        <a href=\"";
        // line 206
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("identifiers-list");
        echo "\">Identifier Types</a>
    </p>

    <p>
        <a href=\"";
        // line 210
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("residencytracks-list");
        echo "\">Residency Tracks</a>
    </p>

    <p>
        <a href=\"";
        // line 214
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("fellowshiptypes-list");
        echo "\">Fellowship Types</a>
    </p>

    <p>
        <a href=\"";
        // line 218
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("employees_researchlabs_pathaction_list");
        echo "\">Research Labs</a>
    </p>

    <p>
        <a href=\"";
        // line 222
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("employees_buildings_pathaction_list");
        echo "\">Buildings</a>
    </p>

    <p>
        <a href=\"";
        // line 226
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("locationtypes-list");
        echo "\">Location Types</a>
    </p>

    <p>
        <a href=\"";
        // line 230
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("locationprivacy-list");
        echo "\">Location Privacy Types</a>
    </p>

    <p>
        <a href=\"";
        // line 234
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("employees_locations_pathaction_list");
        echo "\">Locations</a>
    </p>

    <p>
        <a href=\"";
        // line 238
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("spotpurposes-list");
        echo "\">Location Spot Purposes</a>
    </p>

    <p>
        <a href=\"";
        // line 242
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("equipmenttypes-list");
        echo "\">Equipment Types</a>
    </p>

    <p>
        <a href=\"";
        // line 246
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("equipments-list");
        echo "\">Equipment</a>
    </p>

    <p>
        <a href=\"";
        // line 250
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("rooms-list");
        echo "\">Rooms</a>
    </p>

    <p>
        <a href=\"";
        // line 254
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("suites-list");
        echo "\">Suites</a>
    </p>

    <p>
        <a href=\"";
        // line 258
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("floors-list");
        echo "\">Floors</a>
    </p>

    <p>
        <a href=\"";
        // line 262
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("mailboxes-list");
        echo "\">Mailboxes</a>
    </p>

    <p>
        <a href=\"";
        // line 266
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("efforts-list");
        echo "\">Percent Effort</a>
    </p>

    <p>
        <a href=\"";
        // line 270
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("admintitles-list");
        echo "\">Administrative Titles</a>
    </p>

    <p>
        <a href=\"";
        // line 274
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("apptitles-list");
        echo "\">Academic Appointment Titles</a>
    </p>
    <p>
        <a href=\"";
        // line 277
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("positiontracktypes-list");
        echo "\">Academic Position Track Type List</a>
    </p>

    <p>
        <a href=\"";
        // line 281
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("medicaltitles-list");
        echo "\">Medical Titles</a>
    </p>

    <p>
        <a href=\"";
        // line 285
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("medicalspecialties-list");
        echo "\">Medical Specialties</a>
    </p>

    <p>
        <a href=\"";
        // line 289
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("medicalstatuses-list");
        echo "\">Medical License Statuses</a>
    </p>


    <p>
        <a href=\"";
        // line 294
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("importances-list");
        echo "\">Ranks of Importance</a>
    </p>

    <p>
        <a href=\"";
        // line 298
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("authorshiproles-list");
        echo "\">Authorship Roles</a>
    </p>

    <p>
        <a href=\"";
        // line 302
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("organizations-list");
        echo "\">Lecture Venues</a>
    </p>

    <p>
        <a href=\"";
        // line 306
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("fellappstatuses-list");
        echo "\">Fellowship Application Statuses</a>
    </p>

    <p>
        <a href=\"";
        // line 310
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("fellappranks-list");
        echo "\">Fellowship Application Interview Ranks</a>
    </p>

    <p>
        <a href=\"";
        // line 314
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("fellapplanguageproficiency-list");
        echo "\">Fellowship Application Language Proficiencies</a>
    </p>

    <p>
        <a href=\"";
        // line 318
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("healthcareproviderspecialty-list");
        echo "\">Healthcare Provider Specialties</a>
    </p>

    <hr>
    <h4 class=\"text-info\">Transfusion Medicine</h4>
    <p>
        <a href=\"";
        // line 324
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("bloodproducttransfusions-list");
        echo "\">Blood Product Transfused List</a>
    </p>
    <p>
        <a href=\"";
        // line 327
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("transfusionreactiontypes-list");
        echo "\">Transfusion Reaction Type</a>
    </p>
    <p>
        <a href=\"";
        // line 330
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("bloodtypes-list");
        echo "\">Blood Type List</a>
    </p>
    <p>
        <a href=\"";
        // line 333
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("transfusionantibodyscreenresults-list");
        echo "\">Transfusion Antibody Screen Results List</a>
    </p>
    <p>
        <a href=\"";
        // line 336
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("transfusioncrossmatchresults-list");
        echo "\">Transfusion Crossmatch Results List</a>
    </p>
    <p>
        <a href=\"";
        // line 339
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("transfusiondatresults-list");
        echo "\">Transfusion DAT Results List</a>
    </p>
    <p>
        <a href=\"";
        // line 342
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("transfusionhemolysischeckresults-list");
        echo "\">Transfusion Hemolysis Check Results List</a>
    </p>
    <p>
        <a href=\"";
        // line 345
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("complexplateletsummaryantibodies-list");
        echo "\">Complex Platelet Summary Antibodies List</a>
    </p>
    <p>
        <a href=\"";
        // line 348
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("cciunitplateletcountdefaultvalues-list");
        echo "\">CCI Unit Platelet Count Default Value List</a>
    </p>
    <p>
        <a href=\"";
        // line 351
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("cciplatelettypetransfuseds-list");
        echo "\">CCI Platelet Type Transfused List</a>
    </p>
    <p>
        <a href=\"";
        // line 354
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("platelettransfusionproductreceivings-list");
        echo "\">Platelet Transfusion Product Receiving List</a>
    </p>
    <p>
        <a href=\"";
        // line 357
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("transfusionproductstatus-list");
        echo "\">Transfusion Product Status List</a>
    </p>
    <p>
        <a href=\"";
        // line 360
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("clericalerrors-list");
        echo "\">Clerical Error List</a>
    </p>
    <p>
        <a href=\"";
        // line 363
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("labresultnames-list");
        echo "\">Lab Result Names</a>
    </p>
    <p>
        <a href=\"";
        // line 366
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("labresultunitsmeasures-list");
        echo "\">Lab Result Units of Measure List</a>
    </p>
    <p>
        <a href=\"";
        // line 369
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("labresultflags-list");
        echo "\">Lab Result Flag List</a>
    </p>
    <p>
        <a href=\"";
        // line 372
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("pathologyresultsignatories-list");
        echo "\">Pathology Result Signatories List</a>
    </p>


    ";
        // line 377
        echo "    <hr>
    <h4 class=\"text-info\">Education</h4>
    <p>
        <a href=\"";
        // line 380
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("trainingtypes-list");
        echo "\">Training Types</a>
    </p>
    <p>
        <a href=\"";
        // line 383
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("completionreasons-list");
        echo "\">Training Completion Reasons</a>
    </p>

    <p>
        <a href=\"";
        // line 387
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("trainingdegrees-list");
        echo "\">Academic Degrees</a>
    </p>

    <p>
        <a href=\"";
        // line 391
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("trainingmajors-list");
        echo "\">Academic Majors</a>
    </p>

    <p>
        <a href=\"";
        // line 395
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("trainingminors-list");
        echo "\">Academic Minors</a>
    </p>

    <p>
        <a href=\"";
        // line 399
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("traininghonors-list");
        echo "\">Academic Honors</a>
    </p>
    <hr>

    <p>
        <a href=\"";
        // line 404
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("fellowshiptitles-list");
        echo "\">Professional Fellowship Titles</a>
    </p>

    <p>
        <a href=\"";
        // line 408
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("residencyspecialtys-list");
        echo "\">Residency Specialties</a>
        <a href=\"";
        // line 409
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("generate_residencyspecialties");
        echo "\" style=\"margin-left: 5px;\">
            <span class=\"glyphicon glyphicon-repeat\" aria-hidden=\"true\"></span>
        </a>
    </p>

    <p>
        <a href=\"";
        // line 415
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("fellowshipsubspecialtys-list");
        echo "\">Fellowship Subspecialties</a>
    </p>

    <p>
        <a href=\"";
        // line 419
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("joblists-list");
        echo "\">Job or Experience Titles</a>
    </p>

    ";
        // line 423
        echo "    <hr>
    <h4 class=\"text-info\">Grants</h4>
    <p>
        <a href=\"";
        // line 426
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("employees_grants_pathaction_list");
        echo "\">Grants</a>
    </p>
    <p>
        <a href=\"";
        // line 429
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("sourceorganizations-list");
        echo "\">Grant Source Organizations (Sponsors)</a>
    </p>

    <hr>
    <h4 class=\"text-info\">Business/Vacation Request</h4>
    <p>
        <a href=\"";
        // line 435
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("vacreqrequesttypes-list");
        echo "\">Business/Vacation Request Types</a>
    </p>


    <br>
    <hr>
    <h4 class=\"text-info\">Preset Form Node Tree</h4>
    <p>
        <a general-data-confirm=\"Are you sure?\" href=\"";
        // line 443
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("employees_generate_form_node_tree");
        echo "\">Preset Form Node Tree</a>
    </p>
    <p>
        <a general-data-confirm=\"Are you sure?\" href=\"";
        // line 446
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("employees_generate_test_form_node_tree");
        echo "\">Preset Test Form Node Tree</a>
    </p>

    <br>
    <hr>
    <h4 class=\"text-info\">Populate Lists</h4>

    <p>
        <a general-data-confirm=\"Are you sure?\"
            href=\"";
        // line 455
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("generate_country_city");
        echo "\">
            1) Populate Country and City Lists
        </a>
    </p>


    ";
        // line 461
        if ( !array_key_exists("environment", $context)) {
            // line 462
            echo "        ";
            $context["environment"] = "dev";
            // line 463
            echo "    ";
        }
        // line 464
        echo "
    <!--show this only for dev. Don't show for live-->
    ";
        // line 467
        echo "    ";
        if ($this->env->getExtension('Symfony\Bridge\Twig\Extension\SecurityExtension')->isGranted("ROLE_PLATFORM_DEPUTY_ADMIN")) {
            // line 468
            echo "        <p>
            <a  general-data-confirm=\"Are you sure? This action will overwrite all values on all lists with defaults.\"
               href=\"";
            // line 470
            echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("user_generate_all");
            echo "\">2) Populate All Lists With Default Values</a>
        </p>

        <p>
            <a general-data-confirm=\"Are you sure? This action will try to synchronise DB with the source code changes.\"
               href=\"";
            // line 475
            echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("user_sync_db");
            echo "\">3) Synchronise DB with the source code changes
            </a>
        </p>

        <hr>

        <p>
            <a general-data-confirm=\"Are you sure? This action will populate new users from excel sheet.\"
               href=\"";
            // line 483
            echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("generate_users");
            echo "\">Populate Users From Excel (optional). <strong>This should be run only once on the system initialization.</strong>
            </a>
        </p>

        <hr>
        ";
            // line 489
            echo "            ";
            // line 490
            echo "               ";
            // line 491
            echo "            ";
            // line 492
            echo "        ";
            // line 493
            echo "        <p>
            <a general-data-confirm=\"Are you sure? This action will generate metaphone key for all empty patients last, first, middle names.\"
               href=\"";
            // line 495
            echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("user_generate-patient-metaphone-name");
            echo "\">Generate metaphone keys for all empty patients last, first, middle names (optional).
            </a>
        </p>

    ";
        }
        // line 500
        echo "
    <br>

    <br>

";
    }

    public function getTemplateName()
    {
        return "OlegUserdirectoryBundle:Admin:index.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  878 => 500,  870 => 495,  866 => 493,  864 => 492,  862 => 491,  860 => 490,  858 => 489,  850 => 483,  839 => 475,  831 => 470,  827 => 468,  824 => 467,  820 => 464,  817 => 463,  814 => 462,  812 => 461,  803 => 455,  791 => 446,  785 => 443,  774 => 435,  765 => 429,  759 => 426,  754 => 423,  748 => 419,  741 => 415,  732 => 409,  728 => 408,  721 => 404,  713 => 399,  706 => 395,  699 => 391,  692 => 387,  685 => 383,  679 => 380,  674 => 377,  667 => 372,  661 => 369,  655 => 366,  649 => 363,  643 => 360,  637 => 357,  631 => 354,  625 => 351,  619 => 348,  613 => 345,  607 => 342,  601 => 339,  595 => 336,  589 => 333,  583 => 330,  577 => 327,  571 => 324,  562 => 318,  555 => 314,  548 => 310,  541 => 306,  534 => 302,  527 => 298,  520 => 294,  512 => 289,  505 => 285,  498 => 281,  491 => 277,  485 => 274,  478 => 270,  471 => 266,  464 => 262,  457 => 258,  450 => 254,  443 => 250,  436 => 246,  429 => 242,  422 => 238,  415 => 234,  408 => 230,  401 => 226,  394 => 222,  387 => 218,  380 => 214,  373 => 210,  366 => 206,  359 => 202,  352 => 198,  345 => 194,  338 => 190,  331 => 186,  324 => 182,  317 => 178,  310 => 174,  302 => 169,  296 => 166,  289 => 162,  282 => 158,  275 => 154,  268 => 150,  261 => 146,  254 => 142,  248 => 139,  242 => 136,  236 => 133,  227 => 127,  221 => 124,  215 => 121,  209 => 118,  203 => 115,  197 => 112,  191 => 109,  185 => 106,  176 => 100,  173 => 99,  171 => 98,  169 => 97,  164 => 94,  154 => 87,  151 => 86,  149 => 85,  147 => 84,  142 => 81,  139 => 80,  137 => 79,  135 => 78,  130 => 75,  124 => 72,  118 => 69,  112 => 65,  110 => 64,  108 => 63,  102 => 59,  95 => 55,  88 => 51,  81 => 47,  74 => 43,  67 => 39,  60 => 35,  53 => 31,  45 => 26,  42 => 25,  39 => 24,  32 => 21,  29 => 20,  11 => 17,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "OlegUserdirectoryBundle:Admin:index.html.twig", "C:\\Users\\ch3\\Documents\\MyDocs\\WCMC\\ORDER\\scanorder\\Scanorders2\\src\\Oleg\\UserdirectoryBundle/Resources/views/Admin/index.html.twig");
    }
}
