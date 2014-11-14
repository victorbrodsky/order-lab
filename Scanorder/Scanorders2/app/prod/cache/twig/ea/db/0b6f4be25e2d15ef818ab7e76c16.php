<?php

/* OlegOrderformBundle:MultyScanOrder:new.html.twig */
class __TwigTemplate_eadb0b6f4be25e2d15ef818ab7e76c16 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = $this->env->loadTemplate("OlegOrderformBundle::Default/base.html.twig");

        $this->blocks = array(
            'content' => array($this, 'block_content'),
        );
    }

    protected function doGetParent(array $context)
    {
        return "OlegOrderformBundle::Default/base.html.twig";
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $this->parent->display($context, array_merge($this->blocks, $blocks));
    }

    // line 6
    public function block_content($context, array $blocks = array())
    {
        echo "  
    
<h3 class=\"text-info\">Clinical Multy Slide Scan Order </h3>

";
        // line 23
        $context["myform"] = $this;
        // line 24
        echo "
<form action=\"";
        // line 25
        echo $this->env->getExtension('routing')->getPath("multy_create");
        echo "\" method=\"post\" ";
        echo $this->env->getExtension('form')->renderer->searchAndRenderBlock((isset($context["form"]) ? $context["form"] : null), 'enctype');
;
        echo ">
";
        // line 27
        echo "
";
        // line 29
        echo "
<div id=\"patient-data\" 
     data-prototype-patient=\"
        ";
        // line 32
        echo twig_escape_filter($this->env, $context["myform"]->getfield($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "patient"), "vars"), "prototype"), "mrn")));
        echo " 
        ";
        // line 33
        echo twig_escape_filter($this->env, $context["myform"]->getfield($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "patient"), "vars"), "prototype"), "name")));
        echo "
        ";
        // line 34
        echo twig_escape_filter($this->env, $context["myform"]->getfield($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "patient"), "vars"), "prototype"), "age")));
        echo "
        ";
        // line 35
        echo twig_escape_filter($this->env, $context["myform"]->getfield($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "patient"), "vars"), "prototype"), "sex")));
        echo "
        ";
        // line 36
        echo twig_escape_filter($this->env, $context["myform"]->getfield($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "patient"), "vars"), "prototype"), "dob")));
        echo "
        ";
        // line 37
        echo twig_escape_filter($this->env, $context["myform"]->getfield($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "patient"), "vars"), "prototype"), "clinicalHistory")));
        echo "  
     \"
     data-prototype-procedure=\"
        ";
        // line 40
        echo twig_escape_filter($this->env, $context["myform"]->getfield($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "patient"), "vars"), "prototype"), "specimen"), "vars"), "prototype"), "proceduretype")));
        echo "
        ";
        // line 41
        echo twig_escape_filter($this->env, $context["myform"]->getfield($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "patient"), "vars"), "prototype"), "specimen"), "vars"), "prototype"), "paper")));
        echo "
     \"
     data-prototype-accession=\"
        ";
        // line 44
        echo twig_escape_filter($this->env, $context["myform"]->getfield($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "patient"), "vars"), "prototype"), "specimen"), "vars"), "prototype"), "accession"), "vars"), "prototype"), "accession")));
        echo "
        ";
        // line 45
        echo twig_escape_filter($this->env, $context["myform"]->getfield($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "patient"), "vars"), "prototype"), "specimen"), "vars"), "prototype"), "accession"), "vars"), "prototype"), "date")));
        echo "       
     \"
     data-prototype-part=\"
        ";
        // line 48
        echo twig_escape_filter($this->env, $context["myform"]->getfield($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "patient"), "vars"), "prototype"), "specimen"), "vars"), "prototype"), "accession"), "vars"), "prototype"), "part"), "vars"), "prototype"), "name")));
        echo "
        ";
        // line 49
        echo twig_escape_filter($this->env, $context["myform"]->getfield($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "patient"), "vars"), "prototype"), "specimen"), "vars"), "prototype"), "accession"), "vars"), "prototype"), "part"), "vars"), "prototype"), "sourceOrgan")));
        echo "
        ";
        // line 50
        echo twig_escape_filter($this->env, $context["myform"]->getfield($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "patient"), "vars"), "prototype"), "specimen"), "vars"), "prototype"), "accession"), "vars"), "prototype"), "part"), "vars"), "prototype"), "description")));
        echo "
        ";
        // line 51
        echo twig_escape_filter($this->env, $context["myform"]->getfield($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "patient"), "vars"), "prototype"), "specimen"), "vars"), "prototype"), "accession"), "vars"), "prototype"), "part"), "vars"), "prototype"), "diagnosis")));
        echo "
        ";
        // line 52
        echo twig_escape_filter($this->env, $context["myform"]->getfield($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "patient"), "vars"), "prototype"), "specimen"), "vars"), "prototype"), "accession"), "vars"), "prototype"), "part"), "vars"), "prototype"), "diffDiagnosis")));
        echo "
        ";
        // line 53
        echo twig_escape_filter($this->env, $context["myform"]->getfield($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "patient"), "vars"), "prototype"), "specimen"), "vars"), "prototype"), "accession"), "vars"), "prototype"), "part"), "vars"), "prototype"), "diseaseType")));
        echo "
     \"
     data-prototype-block=\"
        ";
        // line 56
        echo twig_escape_filter($this->env, $context["myform"]->getfield($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "patient"), "vars"), "prototype"), "specimen"), "vars"), "prototype"), "accession"), "vars"), "prototype"), "part"), "vars"), "prototype"), "block"), "vars"), "prototype"), "name")));
        echo "
     \"
     data-prototype-slide=\"
        ";
        // line 59
        echo twig_escape_filter($this->env, $context["myform"]->getfield($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "patient"), "vars"), "prototype"), "specimen"), "vars"), "prototype"), "accession"), "vars"), "prototype"), "part"), "vars"), "prototype"), "block"), "vars"), "prototype"), "slide"), "vars"), "prototype"), "diagnosis")));
        echo "
        ";
        // line 60
        echo twig_escape_filter($this->env, $context["myform"]->getfield($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "patient"), "vars"), "prototype"), "specimen"), "vars"), "prototype"), "accession"), "vars"), "prototype"), "part"), "vars"), "prototype"), "block"), "vars"), "prototype"), "slide"), "vars"), "prototype"), "microscopicdescr")));
        echo "
        ";
        // line 61
        echo twig_escape_filter($this->env, $context["myform"]->getfield($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "patient"), "vars"), "prototype"), "specimen"), "vars"), "prototype"), "accession"), "vars"), "prototype"), "part"), "vars"), "prototype"), "block"), "vars"), "prototype"), "slide"), "vars"), "prototype"), "specialstain")));
        echo "
        ";
        // line 62
        echo twig_escape_filter($this->env, $context["myform"]->getfield($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "patient"), "vars"), "prototype"), "specimen"), "vars"), "prototype"), "accession"), "vars"), "prototype"), "part"), "vars"), "prototype"), "block"), "vars"), "prototype"), "slide"), "vars"), "prototype"), "relevantscan")));
        echo "
        ";
        // line 63
        echo twig_escape_filter($this->env, $context["myform"]->getfield($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "patient"), "vars"), "prototype"), "specimen"), "vars"), "prototype"), "accession"), "vars"), "prototype"), "part"), "vars"), "prototype"), "block"), "vars"), "prototype"), "slide"), "vars"), "prototype"), "stain"), "name")));
        echo "
        ";
        // line 64
        echo twig_escape_filter($this->env, $context["myform"]->getfield($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "patient"), "vars"), "prototype"), "specimen"), "vars"), "prototype"), "accession"), "vars"), "prototype"), "part"), "vars"), "prototype"), "block"), "vars"), "prototype"), "slide"), "vars"), "prototype"), "scan"), "mag")));
        echo "
        ";
        // line 65
        echo twig_escape_filter($this->env, $context["myform"]->getfield($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "patient"), "vars"), "prototype"), "specimen"), "vars"), "prototype"), "accession"), "vars"), "prototype"), "part"), "vars"), "prototype"), "block"), "vars"), "prototype"), "slide"), "vars"), "prototype"), "scan"), "scanregion")));
        echo "
        ";
        // line 66
        echo twig_escape_filter($this->env, $context["myform"]->getfield($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "patient"), "vars"), "prototype"), "specimen"), "vars"), "prototype"), "accession"), "vars"), "prototype"), "part"), "vars"), "prototype"), "block"), "vars"), "prototype"), "slide"), "vars"), "prototype"), "scan"), "note")));
        echo "
     \" 
>
    ";
        // line 70
        echo "    
    ";
        // line 72
        echo "    ";
        $context["patientCount"] = 0;
        // line 73
        echo "    ";
        $context['_parent'] = (array) $context;
        $context['_seq'] = twig_ensure_traversable($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "patient"));
        foreach ($context['_seq'] as $context["_key"] => $context["patient"]) {
            // line 74
            echo "        ";
            $context["uid"] = (((((((((((isset($context["patientCount"]) ? $context["patientCount"] : null) . "_") . 0) . "_") . 0) . "_") . 0) . "_") . 0) . "_") . 0);
            // line 75
            echo "        ";
            // line 76
            echo "        <div id=\"formpanel_patient_";
            echo twig_escape_filter($this->env, (isset($context["uid"]) ? $context["uid"] : null), "html", null, true);
            echo "\" class=\"panel panel-patient\">
            <div class=\"panel-heading\" align=\"left\">
                <button type=\"button\" class=\"btn btn-mini btn_margin btn-mini\" data-toggle=\"collapse\" data-target=\"#form_body_patient_";
            // line 78
            echo twig_escape_filter($this->env, (isset($context["uid"]) ? $context["uid"] : null), "html", null, true);
            echo "\">
                    +/-
                </button>
                Patient ";
            // line 81
            echo twig_escape_filter($this->env, ((isset($context["patientCount"]) ? $context["patientCount"] : null) + 1), "html", null, true);
            echo "
                ";
            // line 82
            if ((twig_length_filter($this->env, $this->getAttribute((isset($context["form"]) ? $context["form"] : null), "patient")) == ((isset($context["patientCount"]) ? $context["patientCount"] : null) + 1))) {
                // line 83
                echo "                <button id=\"form_add_btn_patient_";
                echo twig_escape_filter($this->env, (isset($context["uid"]) ? $context["uid"] : null), "html", null, true);
                echo "\" type=\"button\" class=\"btn btn-mini btn_margin\" onclick=\"addSameForm('patient', ";
                echo twig_escape_filter($this->env, (isset($context["patientCount"]) ? $context["patientCount"] : null), "html", null, true);
                echo ", 0, 0, 0, 0, 0)\">Add Patient</button>
                ";
            }
            // line 85
            echo "            </div>
            <div id=\"form_body_patient_";
            // line 86
            echo twig_escape_filter($this->env, (isset($context["uid"]) ? $context["uid"] : null), "html", null, true);
            echo "\" class=\"panel-body collapse in\">
                ";
            // line 87
            echo $context["myform"]->getfield($this->getAttribute((isset($context["patient"]) ? $context["patient"] : null), "mrn"));
            echo "
                ";
            // line 88
            echo $context["myform"]->getfield($this->getAttribute((isset($context["patient"]) ? $context["patient"] : null), "name"));
            echo "
                ";
            // line 89
            echo $context["myform"]->getfield($this->getAttribute((isset($context["patient"]) ? $context["patient"] : null), "age"));
            echo "
                ";
            // line 90
            echo $context["myform"]->getfield($this->getAttribute((isset($context["patient"]) ? $context["patient"] : null), "sex"));
            echo "
                ";
            // line 91
            echo $context["myform"]->getfield($this->getAttribute((isset($context["patient"]) ? $context["patient"] : null), "dob"));
            echo "
                ";
            // line 92
            echo $context["myform"]->getfield($this->getAttribute((isset($context["patient"]) ? $context["patient"] : null), "clinicalHistory"));
            echo "

                ";
            // line 95
            echo "                ";
            $context["procedureCount"] = 0;
            // line 96
            echo "                ";
            $context['_parent'] = (array) $context;
            $context['_seq'] = twig_ensure_traversable($this->getAttribute((isset($context["patient"]) ? $context["patient"] : null), "specimen"));
            foreach ($context['_seq'] as $context["_key"] => $context["specimen"]) {
                // line 97
                echo "                    ";
                $context["uid"] = (((((((((((isset($context["patientCount"]) ? $context["patientCount"] : null) . "_") . (isset($context["procedureCount"]) ? $context["procedureCount"] : null)) . "_") . 0) . "_") . 0) . "_") . 0) . "_") . 0);
                // line 98
                echo "                    <div id=\"formpanel_procedure_";
                echo twig_escape_filter($this->env, (isset($context["uid"]) ? $context["uid"] : null), "html", null, true);
                echo "\" class=\"panel panel-procedure\">
                        <div class=\"panel-heading\" align=\"left\">
                            <button id=\"form_body_btn_procedure_";
                // line 100
                echo twig_escape_filter($this->env, (isset($context["procedureCount"]) ? $context["procedureCount"] : null), "html", null, true);
                echo "\" type=\"button\" class=\"btn btn_margin btn-mini\" data-toggle=\"collapse\" data-target=\"#form_body_procedure_";
                echo twig_escape_filter($this->env, (isset($context["uid"]) ? $context["uid"] : null), "html", null, true);
                echo "\">
                                +/-
                            </button>
                            Procedure ";
                // line 103
                echo twig_escape_filter($this->env, ((isset($context["procedureCount"]) ? $context["procedureCount"] : null) + 1), "html", null, true);
                echo "
                            ";
                // line 104
                if ((twig_length_filter($this->env, $this->getAttribute((isset($context["patient"]) ? $context["patient"] : null), "specimen")) == ((isset($context["procedureCount"]) ? $context["procedureCount"] : null) + 1))) {
                    // line 105
                    echo "                            <button id=\"form_add_btn_specimen_";
                    echo twig_escape_filter($this->env, (isset($context["uid"]) ? $context["uid"] : null), "html", null, true);
                    echo "\" type=\"button\" class=\"btn btn-mini btn_margin\" onclick=\"addSameForm('procedure', ";
                    echo twig_escape_filter($this->env, (isset($context["patientCount"]) ? $context["patientCount"] : null), "html", null, true);
                    echo ", ";
                    echo twig_escape_filter($this->env, (isset($context["procedureCount"]) ? $context["procedureCount"] : null), "html", null, true);
                    echo ", 0, 0, 0, 0)\">Add Procedure</button>
                            ";
                }
                // line 107
                echo "                            <button onclick=\"return confirm('Are you sure?');\" id=\"form_body_btn_procedure_";
                echo twig_escape_filter($this->env, (isset($context["procedureCount"]) ? $context["procedureCount"] : null), "html", null, true);
                echo "\" type=\"button\" class=\"btn btn-danger btn_margin btn-mini\" data-toggle=\"collapse\" data-target=\"#form_body_procedure_";
                echo twig_escape_filter($this->env, (isset($context["procedureCount"]) ? $context["procedureCount"] : null), "html", null, true);
                echo "\">
                                Delete This Procedure
                            </button>
                        </div>
                        <div id=\"form_body_procedure_";
                // line 111
                echo twig_escape_filter($this->env, (isset($context["uid"]) ? $context["uid"] : null), "html", null, true);
                echo "\" class=\"panel-body collapse in\">                            
                            ";
                // line 112
                echo $context["myform"]->getfield($this->getAttribute((isset($context["specimen"]) ? $context["specimen"] : null), "proceduretype"));
                echo "
                            ";
                // line 113
                echo $context["myform"]->getfield($this->getAttribute((isset($context["specimen"]) ? $context["specimen"] : null), "paper"));
                echo "

                            ";
                // line 116
                echo "                            ";
                $context["accessionCount"] = 0;
                // line 117
                echo "                            ";
                $context['_parent'] = (array) $context;
                $context['_seq'] = twig_ensure_traversable($this->getAttribute((isset($context["specimen"]) ? $context["specimen"] : null), "accession"));
                foreach ($context['_seq'] as $context["_key"] => $context["accession"]) {
                    // line 118
                    echo "                                ";
                    $context["uid"] = (((((((((((isset($context["patientCount"]) ? $context["patientCount"] : null) . "_") . (isset($context["procedureCount"]) ? $context["procedureCount"] : null)) . "_") . (isset($context["accessionCount"]) ? $context["accessionCount"] : null)) . "_") . 0) . "_") . 0) . "_") . 0);
                    // line 119
                    echo "                                <div id=\"formpanel_accession_";
                    echo twig_escape_filter($this->env, (isset($context["uid"]) ? $context["uid"] : null), "html", null, true);
                    echo "\" class=\"panel panel-accession\">
                                    <div class=\"panel-heading\" align=\"left\">
                                        <a style=\"background-color:white;\" data-toggle=\"collapse\" href=\"#form_body_accession_";
                    // line 121
                    echo twig_escape_filter($this->env, (isset($context["uid"]) ? $context["uid"] : null), "html", null, true);
                    echo "\">+/-</a>
                                        Accession ";
                    // line 122
                    echo twig_escape_filter($this->env, ((isset($context["accessionCount"]) ? $context["accessionCount"] : null) + 1), "html", null, true);
                    echo "
                                        ";
                    // line 123
                    if ((twig_length_filter($this->env, $this->getAttribute((isset($context["specimen"]) ? $context["specimen"] : null), "accession")) == ((isset($context["accessionCount"]) ? $context["accessionCount"] : null) + 1))) {
                        // line 124
                        echo "                                        <button id=\"form_add_btn_accession_";
                        echo twig_escape_filter($this->env, (isset($context["uid"]) ? $context["uid"] : null), "html", null, true);
                        echo "\" type=\"button\" class=\"btn btn-mini btn_margin\" onclick=\"addSameForm('accession', ";
                        echo twig_escape_filter($this->env, (isset($context["patientCount"]) ? $context["patientCount"] : null), "html", null, true);
                        echo ", ";
                        echo twig_escape_filter($this->env, (isset($context["procedureCount"]) ? $context["procedureCount"] : null), "html", null, true);
                        echo ", ";
                        echo twig_escape_filter($this->env, (isset($context["accessionCount"]) ? $context["accessionCount"] : null), "html", null, true);
                        echo ", 0, 0, 0)\">Add Accession</button>
                                        ";
                    }
                    // line 126
                    echo "                                    </div>
                                    <div id=\"form_body_accession_";
                    // line 127
                    echo twig_escape_filter($this->env, (isset($context["uid"]) ? $context["uid"] : null), "html", null, true);
                    echo "\" class=\"panel-body collapse in\">
                                            ";
                    // line 128
                    echo $context["myform"]->getfield($this->getAttribute((isset($context["accession"]) ? $context["accession"] : null), "accession"));
                    echo "
                                            ";
                    // line 129
                    echo $context["myform"]->getfield($this->getAttribute((isset($context["accession"]) ? $context["accession"] : null), "date"));
                    echo "                                       
                                       
                                        ";
                    // line 132
                    echo "                                        ";
                    $context["partCount"] = 0;
                    // line 133
                    echo "                                        ";
                    $context['_parent'] = (array) $context;
                    $context['_seq'] = twig_ensure_traversable($this->getAttribute((isset($context["accession"]) ? $context["accession"] : null), "part"));
                    foreach ($context['_seq'] as $context["_key"] => $context["part"]) {
                        // line 134
                        echo "                                            ";
                        $context["uid"] = (((((((((((isset($context["patientCount"]) ? $context["patientCount"] : null) . "_") . (isset($context["procedureCount"]) ? $context["procedureCount"] : null)) . "_") . (isset($context["accessionCount"]) ? $context["accessionCount"] : null)) . "_") . (isset($context["partCount"]) ? $context["partCount"] : null)) . "_") . 0) . "_") . 0);
                        // line 135
                        echo "                                            <div id=\"formpanel_part_";
                        echo twig_escape_filter($this->env, (isset($context["uid"]) ? $context["uid"] : null), "html", null, true);
                        echo "\" class=\"panel panel-part\">
                                                <div class=\"panel-heading\" align=\"left\">
                                                    <button id=\"form_body_btn_part_";
                        // line 137
                        echo twig_escape_filter($this->env, (isset($context["partCount"]) ? $context["partCount"] : null), "html", null, true);
                        echo "\" type=\"button\" class=\"btn btn_margin btn-mini\" data-toggle=\"collapse\" data-target=\"#form_body_part_";
                        echo twig_escape_filter($this->env, (isset($context["uid"]) ? $context["uid"] : null), "html", null, true);
                        echo "\">
                                                        +/-
                                                    </button>
                                                    Part ";
                        // line 140
                        echo twig_escape_filter($this->env, ((isset($context["partCount"]) ? $context["partCount"] : null) + 1), "html", null, true);
                        echo "
                                                    ";
                        // line 141
                        if ((twig_length_filter($this->env, $this->getAttribute((isset($context["accession"]) ? $context["accession"] : null), "part")) == ((isset($context["partCount"]) ? $context["partCount"] : null) + 1))) {
                            // line 142
                            echo "                                                    <button id=\"form_add_btn_part_";
                            echo twig_escape_filter($this->env, (isset($context["uid"]) ? $context["uid"] : null), "html", null, true);
                            echo "\" type=\"button\" class=\"btn btn-mini btn_margin\" onclick=\"addSameForm('part', ";
                            echo twig_escape_filter($this->env, (isset($context["patientCount"]) ? $context["patientCount"] : null), "html", null, true);
                            echo ", ";
                            echo twig_escape_filter($this->env, (isset($context["procedureCount"]) ? $context["procedureCount"] : null), "html", null, true);
                            echo ", ";
                            echo twig_escape_filter($this->env, (isset($context["accessionCount"]) ? $context["accessionCount"] : null), "html", null, true);
                            echo ", ";
                            echo twig_escape_filter($this->env, (isset($context["partCount"]) ? $context["partCount"] : null), "html", null, true);
                            echo ", 0, 0)\">Add Part</button>
                                                    ";
                        }
                        // line 144
                        echo "                                                </div>
                                                <div id=\"form_body_part_";
                        // line 145
                        echo twig_escape_filter($this->env, (isset($context["uid"]) ? $context["uid"] : null), "html", null, true);
                        echo "\" class=\"panel-body collapse in\">
                                                    ";
                        // line 146
                        echo $context["myform"]->getfield($this->getAttribute((isset($context["part"]) ? $context["part"] : null), "name"));
                        echo "
                                                    ";
                        // line 147
                        echo $context["myform"]->getfield($this->getAttribute((isset($context["part"]) ? $context["part"] : null), "sourceOrgan"));
                        echo "
                                                    ";
                        // line 148
                        echo $context["myform"]->getfield($this->getAttribute((isset($context["part"]) ? $context["part"] : null), "description"));
                        echo "
                                                    ";
                        // line 149
                        echo $context["myform"]->getfield($this->getAttribute((isset($context["part"]) ? $context["part"] : null), "diagnosis"));
                        echo "
                                                    ";
                        // line 150
                        echo $context["myform"]->getfield($this->getAttribute((isset($context["part"]) ? $context["part"] : null), "diffDiagnosis"));
                        echo "
                                                    ";
                        // line 151
                        echo $context["myform"]->getfield($this->getAttribute((isset($context["part"]) ? $context["part"] : null), "diseaseType"));
                        echo "

                                                    ";
                        // line 154
                        echo "                                                    ";
                        $context["blockCount"] = 0;
                        // line 155
                        echo "                                                    ";
                        $context['_parent'] = (array) $context;
                        $context['_seq'] = twig_ensure_traversable($this->getAttribute((isset($context["part"]) ? $context["part"] : null), "block"));
                        foreach ($context['_seq'] as $context["_key"] => $context["block"]) {
                            // line 156
                            echo "                                                        ";
                            $context["uid"] = (((((((((((isset($context["patientCount"]) ? $context["patientCount"] : null) . "_") . (isset($context["procedureCount"]) ? $context["procedureCount"] : null)) . "_") . (isset($context["accessionCount"]) ? $context["accessionCount"] : null)) . "_") . (isset($context["partCount"]) ? $context["partCount"] : null)) . "_") . (isset($context["blockCount"]) ? $context["blockCount"] : null)) . "_") . 0);
                            // line 157
                            echo "                                                        <div id=\"formpanel_block_";
                            echo twig_escape_filter($this->env, (isset($context["uid"]) ? $context["uid"] : null), "html", null, true);
                            echo "\" class=\"panel panel-block\">
                                                                <div class=\"panel-heading\" align=\"left\">
                                                                    <button id=\"form_body_btn_block_";
                            // line 159
                            echo twig_escape_filter($this->env, (isset($context["uid"]) ? $context["uid"] : null), "html", null, true);
                            echo "\" type=\"button\" class=\"btn btn_margin btn-mini\" data-toggle=\"collapse\" data-target=\"#form_body_block_";
                            echo twig_escape_filter($this->env, (isset($context["uid"]) ? $context["uid"] : null), "html", null, true);
                            echo "\">
                                                                        +/-
                                                                    </button>
                                                                    Block ";
                            // line 162
                            echo twig_escape_filter($this->env, ((isset($context["blockCount"]) ? $context["blockCount"] : null) + 1), "html", null, true);
                            echo "
                                                                    ";
                            // line 163
                            if ((twig_length_filter($this->env, $this->getAttribute((isset($context["part"]) ? $context["part"] : null), "block")) == ((isset($context["blockCount"]) ? $context["blockCount"] : null) + 1))) {
                                // line 164
                                echo "                                                                    <button id=\"form_add_btn_block_";
                                echo twig_escape_filter($this->env, (isset($context["uid"]) ? $context["uid"] : null), "html", null, true);
                                echo "\" type=\"button\" class=\"btn btn-mini btn_margin\" onclick=\"addSameForm('block', ";
                                echo twig_escape_filter($this->env, (isset($context["patientCount"]) ? $context["patientCount"] : null), "html", null, true);
                                echo ", ";
                                echo twig_escape_filter($this->env, (isset($context["procedureCount"]) ? $context["procedureCount"] : null), "html", null, true);
                                echo ", ";
                                echo twig_escape_filter($this->env, (isset($context["accessionCount"]) ? $context["accessionCount"] : null), "html", null, true);
                                echo ", ";
                                echo twig_escape_filter($this->env, (isset($context["partCount"]) ? $context["partCount"] : null), "html", null, true);
                                echo ", ";
                                echo twig_escape_filter($this->env, (isset($context["blockCount"]) ? $context["blockCount"] : null), "html", null, true);
                                echo ", 0)\">Add Block</button>
                                                                    ";
                            }
                            // line 166
                            echo "                                                                </div>
                                                                <div id=\"form_body_block_";
                            // line 167
                            echo twig_escape_filter($this->env, (isset($context["uid"]) ? $context["uid"] : null), "html", null, true);
                            echo "\" class=\"panel-body collapse in\">
                                                                    ";
                            // line 168
                            echo $context["myform"]->getfield($this->getAttribute((isset($context["block"]) ? $context["block"] : null), "name"));
                            echo "


                                                                    ";
                            // line 172
                            echo "                                                                    ";
                            $context["slideCount"] = 0;
                            // line 173
                            echo "                                                                    ";
                            $context['_parent'] = (array) $context;
                            $context['_seq'] = twig_ensure_traversable($this->getAttribute((isset($context["block"]) ? $context["block"] : null), "slide"));
                            foreach ($context['_seq'] as $context["_key"] => $context["slide"]) {
                                // line 174
                                echo "                                                                        ";
                                $context["uid"] = (((((((((((isset($context["patientCount"]) ? $context["patientCount"] : null) . "_") . (isset($context["procedureCount"]) ? $context["procedureCount"] : null)) . "_") . (isset($context["accessionCount"]) ? $context["accessionCount"] : null)) . "_") . (isset($context["partCount"]) ? $context["partCount"] : null)) . "_") . (isset($context["blockCount"]) ? $context["blockCount"] : null)) . "_") . (isset($context["slideCount"]) ? $context["slideCount"] : null));
                                // line 175
                                echo "                                                                        <div id=\"formpanel_slide_";
                                echo twig_escape_filter($this->env, (isset($context["uid"]) ? $context["uid"] : null), "html", null, true);
                                echo "\" class=\"panel panel-slide\">
                                                                            <div class=\"panel-heading\" align=\"left\">
                                                                                <button id=\"form_body_btn_slide_";
                                // line 177
                                echo twig_escape_filter($this->env, (isset($context["slideCount"]) ? $context["slideCount"] : null), "html", null, true);
                                echo "\" type=\"button\" class=\"btn btn_margin btn-mini\" data-toggle=\"collapse\" data-target=\"#form_body_slide_";
                                echo twig_escape_filter($this->env, (isset($context["uid"]) ? $context["uid"] : null), "html", null, true);
                                echo "\">
                                                                                    +/-
                                                                                </button>
                                                                                Slide ";
                                // line 180
                                echo twig_escape_filter($this->env, ((isset($context["slideCount"]) ? $context["slideCount"] : null) + 1), "html", null, true);
                                echo "
                                                                                ";
                                // line 181
                                if ((twig_length_filter($this->env, $this->getAttribute((isset($context["block"]) ? $context["block"] : null), "slide")) == ((isset($context["slideCount"]) ? $context["slideCount"] : null) + 1))) {
                                    // line 182
                                    echo "                                                                                <button id=\"form_add_btn_slide_";
                                    echo twig_escape_filter($this->env, (isset($context["uid"]) ? $context["uid"] : null), "html", null, true);
                                    echo "\" type=\"button\" class=\"btn btn-mini btn_margin\" onclick=\"addSameForm('slide', ";
                                    echo twig_escape_filter($this->env, (isset($context["patientCount"]) ? $context["patientCount"] : null), "html", null, true);
                                    echo ", ";
                                    echo twig_escape_filter($this->env, (isset($context["procedureCount"]) ? $context["procedureCount"] : null), "html", null, true);
                                    echo ", ";
                                    echo twig_escape_filter($this->env, (isset($context["accessionCount"]) ? $context["accessionCount"] : null), "html", null, true);
                                    echo ", ";
                                    echo twig_escape_filter($this->env, (isset($context["partCount"]) ? $context["partCount"] : null), "html", null, true);
                                    echo ", ";
                                    echo twig_escape_filter($this->env, (isset($context["blockCount"]) ? $context["blockCount"] : null), "html", null, true);
                                    echo ", ";
                                    echo twig_escape_filter($this->env, (isset($context["slideCount"]) ? $context["slideCount"] : null), "html", null, true);
                                    echo ")\">Add Slide</button>
                                                                                ";
                                }
                                // line 184
                                echo "                                                                            </div>
                                                                            <div id=\"form_body_slide_";
                                // line 185
                                echo twig_escape_filter($this->env, (isset($context["uid"]) ? $context["uid"] : null), "html", null, true);
                                echo "\" class=\"panel-body collapse in\">
                                                                                ";
                                // line 186
                                echo $context["myform"]->getfield($this->getAttribute((isset($context["slide"]) ? $context["slide"] : null), "diagnosis"));
                                echo "
                                                                                ";
                                // line 187
                                echo $context["myform"]->getfield($this->getAttribute((isset($context["slide"]) ? $context["slide"] : null), "microscopicdescr"));
                                echo "
                                                                                ";
                                // line 188
                                echo $context["myform"]->getfield($this->getAttribute((isset($context["slide"]) ? $context["slide"] : null), "specialstain"));
                                echo "
                                                                                ";
                                // line 189
                                echo $context["myform"]->getfield($this->getAttribute((isset($context["slide"]) ? $context["slide"] : null), "relevantscan"));
                                echo "
                                                                                ";
                                // line 191
                                echo "                                                                                ";
                                echo $context["myform"]->getfield($this->getAttribute($this->getAttribute((isset($context["slide"]) ? $context["slide"] : null), "stain"), "name"));
                                echo "
                                                                                ";
                                // line 192
                                echo $context["myform"]->getfield($this->getAttribute($this->getAttribute((isset($context["slide"]) ? $context["slide"] : null), "scan"), "mag"));
                                echo "
                                                                                ";
                                // line 193
                                echo $context["myform"]->getfield($this->getAttribute($this->getAttribute((isset($context["slide"]) ? $context["slide"] : null), "scan"), "scanregion"));
                                echo "
                                                                                ";
                                // line 194
                                echo $context["myform"]->getfield($this->getAttribute($this->getAttribute((isset($context["slide"]) ? $context["slide"] : null), "scan"), "note"));
                                echo "
                                                                            </div>
                                                                    </div> ";
                                // line 197
                                echo "                                                                    ";
                                $context["slideCount"] = ((isset($context["slideCount"]) ? $context["slideCount"] : null) + 1);
                                // line 198
                                echo "                                                                ";
                            }
                            $_parent = $context['_parent'];
                            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['slide'], $context['_parent'], $context['loop']);
                            $context = array_intersect_key($context, $_parent) + $_parent;
                            // line 199
                            echo "

                                                            </div>
                                                            ";
                            // line 202
                            $context["blockCount"] = ((isset($context["blockCount"]) ? $context["blockCount"] : null) + 1);
                            // line 203
                            echo "                                                        </div> ";
                            // line 204
                            echo "                                                    ";
                        }
                        $_parent = $context['_parent'];
                        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['block'], $context['_parent'], $context['loop']);
                        $context = array_intersect_key($context, $_parent) + $_parent;
                        // line 205
                        echo "


                                                </div>
                                            </div> ";
                        // line 210
                        echo "                                            ";
                        $context["partCount"] = ((isset($context["partCount"]) ? $context["partCount"] : null) + 1);
                        // line 211
                        echo "                                        ";
                    }
                    $_parent = $context['_parent'];
                    unset($context['_seq'], $context['_iterated'], $context['_key'], $context['part'], $context['_parent'], $context['loop']);
                    $context = array_intersect_key($context, $_parent) + $_parent;
                    // line 212
                    echo "

                                    </div>
                                </div> ";
                    // line 216
                    echo "                                ";
                    $context["accessionCount"] = ((isset($context["accessionCount"]) ? $context["accessionCount"] : null) + 1);
                    // line 217
                    echo "                            ";
                }
                $_parent = $context['_parent'];
                unset($context['_seq'], $context['_iterated'], $context['_key'], $context['accession'], $context['_parent'], $context['loop']);
                $context = array_intersect_key($context, $_parent) + $_parent;
                // line 218
                echo "

                        </div>
                    </div>";
                // line 222
                echo "                    ";
                $context["procedureCount"] = ((isset($context["procedureCount"]) ? $context["procedureCount"] : null) + 1);
                // line 223
                echo "                ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['specimen'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            echo " ";
            // line 224
            echo "

            </div>";
            // line 227
            echo "        </div> ";
            // line 228
            echo "        ";
            $context["patientCount"] = ((isset($context["patientCount"]) ? $context["patientCount"] : null) + 1);
            // line 229
            echo "    ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['patient'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        echo " ";
        // line 230
        echo "

    <button id=\"next_button\" type=\"button\" class=\"btn\" 
            data-toggle=\"collapse\" data-target=\"#orderinfo_param\">Next
    </button>

    <div id=\"orderinfo_param\" class=\"collapse\">
        <div class=\"row-fluid\">
        <div class=\"span12\">
        <h4>Scan Order Info</h4>   
        </div>
        </div>
        <div class=\"row-fluid\">
        <div class=\"span6\" align=\"right\">
        ";
        // line 244
        echo $this->env->getExtension('form')->renderer->searchAndRenderBlock($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "provider"), 'label');
        echo "
        </div>
        <div class=\"span6\" align=\"left\">
        ";
        // line 247
        echo $this->env->getExtension('form')->renderer->searchAndRenderBlock($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "provider"), 'widget');
        echo " 
        </div>
        </div>
        <div class=\"row-fluid\">
        <div class=\"span6\" align=\"right\">
        ";
        // line 252
        echo $this->env->getExtension('form')->renderer->searchAndRenderBlock($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "pathologyService"), 'label');
        echo "
        </div>
        <div class=\"span6\" align=\"left\">
        ";
        // line 255
        echo $this->env->getExtension('form')->renderer->searchAndRenderBlock($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "pathologyService"), 'widget');
        echo "
        </div>
        </div>

        <div class=\"row-fluid\">
        <div class=\"span6\" align=\"right\">
        ";
        // line 261
        echo $this->env->getExtension('form')->renderer->searchAndRenderBlock($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "priority"), 'label');
        echo "
        </div>
        <div class=\"span6\" align=\"left\">
        ";
        // line 264
        echo $this->env->getExtension('form')->renderer->searchAndRenderBlock($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "priority"), 'widget');
        echo "
        </div>
        </div>
";
        // line 268
        echo "        <div id=\"priority_option\" class=\"collapse\"> 
";
        // line 270
        echo "        <div class=\"well\">

            <div class=\"row-fluid\">              
            <div class=\"span6\" align=\"right\">              
            ";
        // line 274
        echo $this->env->getExtension('form')->renderer->searchAndRenderBlock($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "scandeadline"), 'label');
        echo "
            </div>            
            <div class=\"span6\" align=\"left\">
            ";
        // line 277
        echo $this->env->getExtension('form')->renderer->searchAndRenderBlock($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "scandeadline"), 'widget');
        echo " 
            </div>   
            </div>    

            <div class=\"row-fluid\">              
            <div class=\"span6\" align=\"right\">
            ";
        // line 283
        echo $this->env->getExtension('form')->renderer->searchAndRenderBlock($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "returnoption"), 'label');
        echo "
            </div>            
            <div class=\"span6\" align=\"left\">
            ";
        // line 286
        echo $this->env->getExtension('form')->renderer->searchAndRenderBlock($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "returnoption"), 'widget');
        echo " 
            </div>   
            </div>    
        </div>    
        </div>  

        <div class=\"row-fluid\">
        <div class=\"span6\" align=\"right\">
        ";
        // line 294
        echo $this->env->getExtension('form')->renderer->searchAndRenderBlock($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "slideDelivery"), 'label');
        echo "
        </div>
        <div class=\"span6\" align=\"left\">
        ";
        // line 297
        echo $this->env->getExtension('form')->renderer->searchAndRenderBlock($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "slideDelivery"), 'widget');
        echo "
        </div>
        </div>
        <div class=\"row-fluid\">
        <div class=\"span6\" align=\"right\">
        ";
        // line 302
        echo $this->env->getExtension('form')->renderer->searchAndRenderBlock($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "returnSlide"), 'label');
        echo "
        </div>
        <div class=\"span6\" align=\"left\">
        ";
        // line 305
        echo $this->env->getExtension('form')->renderer->searchAndRenderBlock($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "returnSlide"), 'widget');
        echo "
        </div>
        </div>
        <div class=\"row-fluid\">
        <div class=\"span12\">
            <button class=\"btn_margin_top btn btn-primary btn-success\" type=\"submit\">Submit</button>        
        </div>
        </div> 
    </div>
   
    ";
        // line 316
        echo "    <div data-prototype-orderinfo=\"";
        echo twig_escape_filter($this->env, $this->env->getExtension('form')->renderer->searchAndRenderBlock((isset($context["form"]) ? $context["form"] : null), 'rest'));
        echo "\">
        ";
        // line 317
        echo $this->env->getExtension('form')->renderer->searchAndRenderBlock((isset($context["form"]) ? $context["form"] : null), 'rest');
        echo "
    </div>
    
";
        // line 321
        echo "    
";
        // line 323
        echo "</form>
    
";
    }

    // line 11
    public function getfield($_field = null)
    {
        $context = $this->env->mergeGlobals(array(
            "field" => $_field,
        ));

        $blocks = array();

        ob_start();
        try {
            // line 12
            echo "    <div class=\"row-fluid\">
        ";
            // line 13
            echo $this->env->getExtension('form')->renderer->searchAndRenderBlock((isset($context["field"]) ? $context["field"] : null), 'errors');
            echo "
        <div class=\"span6\" align=\"right\">
            ";
            // line 15
            echo $this->env->getExtension('form')->renderer->searchAndRenderBlock((isset($context["field"]) ? $context["field"] : null), 'label');
            echo "
        </div>
        <div class=\"span6\" align=\"left\">
            ";
            // line 18
            echo $this->env->getExtension('form')->renderer->searchAndRenderBlock((isset($context["field"]) ? $context["field"] : null), 'widget');
            echo "
        </div>
        ";
            // line 20
            echo $this->env->getExtension('form')->renderer->searchAndRenderBlock((isset($context["field"]) ? $context["field"] : null), 'rest');
            echo "
    </div>
";
        } catch (Exception $e) {
            ob_end_clean();

            throw $e;
        }

        return ('' === $tmp = ob_get_clean()) ? '' : new Twig_Markup($tmp, $this->env->getCharset());
    }

    public function getTemplateName()
    {
        return "OlegOrderformBundle:MultyScanOrder:new.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  848 => 18,  834 => 12,  823 => 11,  817 => 323,  814 => 321,  803 => 316,  790 => 305,  784 => 302,  770 => 294,  759 => 286,  753 => 283,  744 => 277,  738 => 274,  732 => 270,  729 => 268,  723 => 264,  702 => 252,  694 => 247,  688 => 244,  672 => 230,  665 => 229,  660 => 227,  656 => 224,  649 => 223,  646 => 222,  641 => 218,  635 => 217,  632 => 216,  627 => 212,  621 => 211,  618 => 210,  612 => 205,  606 => 204,  604 => 203,  602 => 202,  597 => 199,  591 => 198,  588 => 197,  583 => 194,  579 => 193,  575 => 192,  570 => 191,  566 => 189,  562 => 188,  558 => 187,  554 => 186,  550 => 185,  547 => 184,  529 => 182,  527 => 181,  515 => 177,  509 => 175,  506 => 174,  501 => 173,  498 => 172,  488 => 167,  485 => 166,  467 => 163,  463 => 162,  455 => 159,  449 => 157,  446 => 156,  441 => 155,  438 => 154,  429 => 150,  425 => 149,  421 => 148,  417 => 147,  406 => 144,  392 => 142,  386 => 140,  378 => 137,  372 => 135,  369 => 134,  364 => 133,  361 => 132,  356 => 129,  352 => 128,  348 => 127,  345 => 126,  333 => 124,  331 => 123,  327 => 122,  323 => 121,  317 => 119,  306 => 116,  301 => 113,  297 => 112,  267 => 103,  259 => 100,  242 => 95,  237 => 92,  221 => 88,  213 => 86,  200 => 82,  190 => 78,  118 => 52,  153 => 62,  102 => 48,  100 => 47,  113 => 39,  110 => 50,  97 => 37,  1357 => 388,  1348 => 387,  1346 => 386,  1343 => 385,  1327 => 381,  1320 => 380,  1318 => 379,  1315 => 378,  1292 => 374,  1267 => 373,  1265 => 372,  1262 => 371,  1250 => 366,  1245 => 365,  1243 => 364,  1240 => 363,  1231 => 357,  1225 => 355,  1222 => 354,  1217 => 353,  1215 => 352,  1212 => 351,  1205 => 346,  1196 => 344,  1192 => 343,  1189 => 342,  1186 => 341,  1184 => 340,  1181 => 339,  1173 => 335,  1171 => 334,  1168 => 333,  1162 => 329,  1156 => 327,  1153 => 326,  1151 => 325,  1148 => 324,  1139 => 319,  1137 => 318,  1114 => 317,  1111 => 316,  1108 => 315,  1105 => 314,  1102 => 313,  1099 => 312,  1096 => 311,  1094 => 310,  1091 => 309,  1084 => 305,  1080 => 304,  1075 => 303,  1073 => 302,  1070 => 301,  1063 => 296,  1060 => 295,  1052 => 290,  1049 => 289,  1047 => 288,  1044 => 287,  1036 => 282,  1032 => 281,  1028 => 280,  1025 => 279,  1023 => 278,  1020 => 277,  1012 => 273,  1010 => 269,  1008 => 268,  1005 => 267,  1000 => 263,  978 => 258,  975 => 257,  972 => 256,  969 => 255,  966 => 254,  963 => 253,  960 => 252,  957 => 251,  954 => 250,  951 => 249,  948 => 248,  946 => 247,  943 => 246,  935 => 240,  932 => 239,  930 => 238,  927 => 237,  919 => 233,  916 => 232,  914 => 231,  911 => 230,  899 => 226,  896 => 225,  893 => 224,  890 => 223,  888 => 222,  885 => 221,  877 => 217,  874 => 216,  872 => 215,  869 => 214,  861 => 210,  858 => 209,  856 => 208,  853 => 20,  845 => 203,  842 => 15,  840 => 201,  837 => 13,  829 => 196,  826 => 195,  824 => 194,  821 => 193,  813 => 189,  810 => 188,  808 => 317,  805 => 186,  797 => 182,  794 => 181,  792 => 180,  789 => 179,  781 => 175,  779 => 174,  776 => 297,  768 => 169,  765 => 168,  763 => 167,  760 => 166,  752 => 162,  749 => 161,  747 => 160,  745 => 159,  742 => 158,  735 => 153,  725 => 152,  720 => 151,  717 => 261,  711 => 148,  708 => 255,  706 => 146,  703 => 145,  695 => 139,  693 => 138,  692 => 137,  691 => 136,  690 => 135,  685 => 134,  679 => 132,  676 => 131,  674 => 130,  671 => 129,  662 => 228,  658 => 122,  654 => 121,  650 => 120,  645 => 119,  639 => 117,  636 => 116,  634 => 115,  631 => 114,  615 => 110,  613 => 109,  610 => 108,  594 => 104,  592 => 103,  589 => 102,  572 => 98,  560 => 96,  553 => 93,  551 => 92,  546 => 91,  543 => 90,  525 => 89,  523 => 180,  520 => 87,  511 => 82,  508 => 81,  505 => 80,  499 => 78,  497 => 77,  492 => 168,  489 => 75,  486 => 74,  471 => 72,  459 => 69,  456 => 68,  450 => 64,  442 => 62,  433 => 151,  428 => 59,  426 => 58,  414 => 52,  408 => 50,  405 => 49,  403 => 48,  400 => 47,  390 => 141,  388 => 42,  385 => 41,  377 => 37,  371 => 35,  366 => 33,  363 => 32,  350 => 26,  344 => 24,  342 => 23,  335 => 21,  332 => 20,  316 => 16,  313 => 15,  311 => 14,  308 => 13,  299 => 8,  293 => 111,  290 => 5,  281 => 385,  271 => 104,  266 => 363,  263 => 362,  260 => 360,  255 => 350,  253 => 98,  250 => 97,  248 => 333,  245 => 96,  240 => 323,  238 => 309,  233 => 91,  230 => 300,  227 => 298,  217 => 87,  215 => 277,  212 => 276,  210 => 85,  207 => 266,  204 => 264,  202 => 83,  197 => 246,  194 => 245,  191 => 243,  186 => 236,  184 => 76,  181 => 229,  179 => 74,  174 => 73,  161 => 199,  146 => 62,  104 => 87,  74 => 21,  34 => 6,  83 => 22,  152 => 49,  145 => 46,  131 => 157,  129 => 145,  124 => 129,  65 => 26,  120 => 41,  20 => 2,  90 => 32,  76 => 37,  291 => 61,  288 => 4,  279 => 43,  276 => 378,  273 => 105,  262 => 28,  257 => 27,  246 => 80,  243 => 324,  225 => 89,  222 => 294,  218 => 72,  180 => 71,  172 => 63,  170 => 60,  159 => 193,  150 => 63,  134 => 59,  81 => 34,  63 => 25,  96 => 45,  77 => 32,  58 => 14,  52 => 18,  59 => 30,  53 => 18,  23 => 3,  480 => 162,  474 => 161,  469 => 164,  461 => 70,  457 => 153,  453 => 151,  444 => 149,  440 => 148,  437 => 61,  435 => 146,  430 => 144,  427 => 143,  423 => 57,  413 => 146,  409 => 145,  407 => 131,  402 => 130,  398 => 129,  393 => 126,  387 => 122,  384 => 121,  381 => 120,  379 => 119,  374 => 36,  368 => 34,  365 => 111,  362 => 110,  360 => 109,  355 => 27,  341 => 105,  337 => 22,  322 => 101,  314 => 118,  312 => 98,  309 => 117,  305 => 95,  298 => 91,  294 => 90,  285 => 3,  283 => 107,  278 => 384,  268 => 370,  264 => 84,  258 => 351,  252 => 80,  247 => 78,  241 => 77,  235 => 308,  229 => 90,  224 => 71,  220 => 287,  214 => 69,  208 => 68,  169 => 207,  143 => 58,  140 => 55,  132 => 38,  128 => 56,  119 => 52,  111 => 107,  107 => 48,  71 => 19,  177 => 65,  165 => 64,  160 => 61,  139 => 166,  135 => 39,  126 => 53,  114 => 51,  84 => 26,  70 => 13,  67 => 12,  61 => 2,  47 => 15,  38 => 24,  94 => 57,  89 => 34,  85 => 36,  79 => 32,  75 => 14,  68 => 35,  56 => 32,  50 => 12,  29 => 5,  87 => 28,  72 => 36,  55 => 15,  21 => 4,  26 => 3,  98 => 24,  93 => 35,  88 => 37,  78 => 19,  46 => 11,  27 => 4,  40 => 9,  44 => 9,  35 => 3,  31 => 17,  43 => 6,  41 => 25,  28 => 6,  201 => 92,  196 => 81,  183 => 70,  171 => 72,  166 => 206,  163 => 70,  158 => 65,  156 => 192,  151 => 185,  142 => 61,  138 => 60,  136 => 165,  123 => 31,  121 => 128,  117 => 25,  115 => 40,  105 => 48,  101 => 32,  91 => 56,  69 => 28,  66 => 19,  62 => 16,  49 => 8,  24 => 2,  32 => 5,  25 => 29,  22 => 2,  19 => 1,  209 => 82,  203 => 78,  199 => 262,  193 => 73,  189 => 237,  187 => 84,  182 => 75,  176 => 220,  173 => 74,  168 => 70,  164 => 200,  162 => 66,  154 => 64,  149 => 44,  147 => 50,  144 => 173,  141 => 41,  133 => 48,  130 => 54,  125 => 52,  122 => 53,  116 => 51,  112 => 43,  109 => 102,  106 => 49,  103 => 26,  99 => 68,  95 => 46,  92 => 44,  86 => 41,  82 => 40,  80 => 23,  73 => 30,  64 => 34,  60 => 33,  57 => 10,  54 => 13,  51 => 29,  48 => 27,  45 => 6,  42 => 10,  39 => 9,  36 => 23,  33 => 4,  30 => 3,);
    }
}
