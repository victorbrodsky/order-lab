<?php

/* OlegUserdirectoryBundle::Default/userformmacros.html.twig */
class __TwigTemplate_9ed11d585cfa6b2039dd84d9f451481603b6ce1b205260f1f1961076a7ce11fe extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 16
        echo "
";
        // line 18
        echo "

";
        // line 171
        echo "



";
        // line 223
        echo "

";
        // line 267
        echo "

";
        // line 292
        echo "
";
        // line 357
        echo "



";
        // line 491
        echo "
";
        // line 493
        echo "    ";
        // line 494
        echo "    ";
        // line 495
        echo "
    ";
        // line 497
        echo "        ";
        // line 498
        echo "    ";
        // line 499
        echo "        ";
        // line 500
        echo "    ";
        // line 501
        echo "
    ";
        // line 503
        echo "
        ";
        // line 505
        echo "            ";
        // line 506
        echo "                ";
        // line 507
        echo "            ";
        // line 508
        echo "        ";
        // line 509
        echo "
        ";
        // line 511
        echo "        ";
        // line 512
        echo "        ";
        // line 513
        echo "        ";
        // line 514
        echo "        ";
        // line 515
        echo "        ";
        // line 516
        echo "        ";
        // line 517
        echo "        ";
        // line 518
        echo "
    ";
        // line 521
        echo "
";
        // line 597
        echo "

";
        // line 747
        echo "
";
        // line 1052
        echo "
";
        // line 1072
        echo "

";
        // line 1082
        echo "



";
        // line 1123
        echo "
";
        // line 1154
        echo "

";
    }

    // line 20
    public function getbaseTitle($__field__ = null, $__cycle__ = null, $__classname__ = null, $__prototype__ = null, $__sitename__ = null, ...$__varargs__)
    {
        $context = $this->env->mergeGlobals(array(
            "field" => $__field__,
            "cycle" => $__cycle__,
            "classname" => $__classname__,
            "prototype" => $__prototype__,
            "sitename" => $__sitename__,
            "varargs" => $__varargs__,
        ));

        $blocks = array();

        ob_start();
        try {
            // line 21
            echo "    ";
            $context["formmacros"] = $this->loadTemplate("OlegOrderformBundle::Default/formmacros.html.twig", "OlegUserdirectoryBundle::Default/userformmacros.html.twig", 21);
            // line 22
            echo "    ";
            $context["usermacros"] = $this->loadTemplate("OlegUserdirectoryBundle::Default/usermacros.html.twig", "OlegUserdirectoryBundle::Default/userformmacros.html.twig", 22);
            // line 23
            echo "    ";
            $context["treemacros"] = $this->loadTemplate("OlegUserdirectoryBundle::Tree/treemacros.html.twig", "OlegUserdirectoryBundle::Default/userformmacros.html.twig", 23);
            // line 24
            echo "    ";
            if (((isset($context["prototype"]) ? $context["prototype"] : null) == "prototype")) {
                // line 25
                echo "        ";
                $context["formfield"] = $this->getAttribute($this->getAttribute((isset($context["field"]) ? $context["field"] : null), "vars", array()), "prototype", array());
                // line 26
                echo "    ";
            } else {
                // line 27
                echo "        ";
                $context["formfield"] = (isset($context["field"]) ? $context["field"] : null);
                // line 28
                echo "    ";
            }
            // line 29
            echo "
    ";
            // line 30
            if ((($this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "vars", array()), "value", array()) && ($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "vars", array()), "value", array()), "status", array()) == 0)) || ((isset($context["prototype"]) ? $context["prototype"] : null) == "prototype"))) {
                // line 31
                echo "        ";
                $context["wellclass"] = "user-alert-warning";
                // line 32
                echo "    ";
            } else {
                // line 33
                echo "        ";
                $context["wellclass"] = "";
                // line 34
                echo "    ";
            }
            // line 35
            echo "
    ";
            // line 37
            echo "    ";
            $context["endDateClass"] = "";
            // line 38
            echo "    ";
            $context["displayNone"] = "";
            // line 39
            echo "    ";
            if ((($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "endDate", array(), "any", true, true) && $this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "vars", array()), "value", array())) && $this->getAttribute($this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "vars", array()), "value", array()), "endDate", array()))) {
                // line 40
                echo "        ";
                $context["endDateClass"] = "collapse-non-empty-enddate";
                // line 41
                echo "        ";
                $context["displayNone"] = "style=display:none";
                // line 42
                echo "    ";
            }
            // line 43
            echo "
    <div class=\"user-collection-holder alert ";
            // line 44
            echo twig_escape_filter($this->env, (isset($context["classname"]) ? $context["classname"] : null), "html", null, true);
            echo " ";
            echo twig_escape_filter($this->env, (isset($context["wellclass"]) ? $context["wellclass"] : null), "html", null, true);
            echo " ";
            echo twig_escape_filter($this->env, (isset($context["endDateClass"]) ? $context["endDateClass"] : null), "html", null, true);
            echo "\" ";
            echo twig_escape_filter($this->env, (isset($context["displayNone"]) ? $context["displayNone"] : null), "html", null, true);
            echo ">
        ";
            // line 45
            if (((isset($context["cycle"]) ? $context["cycle"] : null) != "show_user")) {
                // line 46
                echo "            <div class=\"text-right\">
                <button type=\"button\" class=\"btn btn-default btn-sm confirm-delete-with-expired\" onClick=\"removeExistingObject(this,'";
                // line 47
                echo twig_escape_filter($this->env, (isset($context["classname"]) ? $context["classname"] : null), "html", null, true);
                echo "')\" ><span class=\"glyphicon glyphicon-remove\"></span></button>
            </div>
        ";
            }
            // line 50
            echo "
        ";
            // line 51
            echo $context["formmacros"]->getfield($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "id", array()));
            echo "
        ";
            // line 53
            echo "        ";
            echo $context["usermacros"]->getstatusVerifiedField((isset($context["formfield"]) ? $context["formfield"] : null), (isset($context["cycle"]) ? $context["cycle"] : null));
            echo "

        ";
            // line 55
            if ($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "orderinlist", array(), "any", true, true)) {
                // line 56
                echo "            ";
                echo $context["formmacros"]->getfield($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "orderinlist", array()));
                echo "
        ";
            }
            // line 58
            echo "
        ";
            // line 59
            if ($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "priority", array(), "any", true, true)) {
                // line 60
                echo "            ";
                echo $context["formmacros"]->getfield_notempty($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "priority", array()), (isset($context["cycle"]) ? $context["cycle"] : null));
                echo "
        ";
            }
            // line 62
            echo "
        ";
            // line 63
            echo $context["formmacros"]->getfield($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "name", array()));
            echo "

        ";
            // line 65
            if ($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "specialties", array(), "any", true, true)) {
                // line 66
                echo "            ";
                echo $context["formmacros"]->getfield($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "specialties", array()));
                echo "
        ";
            }
            // line 68
            echo "
        ";
            // line 70
            echo "        ";
            if ($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "boss", array(), "any", true, true)) {
                // line 71
                echo "            ";
                if (((isset($context["cycle"]) ? $context["cycle"] : null) != "show_user")) {
                    // line 72
                    echo "                ";
                    echo $context["formmacros"]->getfield($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "boss", array()));
                    echo "
            ";
                } else {
                    // line 74
                    echo "                ";
                    if ((($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "vars", array(), "any", false, true), "value", array(), "any", false, true), "boss", array(), "any", true, true) && ($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "vars", array()), "value", array()), "boss", array()) != null)) && (twig_length_filter($this->env, $this->getAttribute($this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "vars", array()), "value", array()), "boss", array())) > 0))) {
                        // line 75
                        echo "                    <p>
                        <strong>Reports to:</strong>
                    </p>
                    ";
                        // line 78
                        $context['_parent'] = $context;
                        $context['_seq'] = twig_ensure_traversable($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "vars", array()), "value", array()), "boss", array()));
                        foreach ($context['_seq'] as $context["_key"] => $context["boss"]) {
                            // line 79
                            echo "                        ";
                            echo $context["usermacros"]->getpersonInfo($context["boss"], (isset($context["cycle"]) ? $context["cycle"] : null), (isset($context["sitename"]) ? $context["sitename"] : null));
                            echo "
                    ";
                        }
                        $_parent = $context['_parent'];
                        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['boss'], $context['_parent'], $context['loop']);
                        $context = array_intersect_key($context, $_parent) + $_parent;
                        // line 81
                        echo "                ";
                    }
                    // line 82
                    echo "                ";
                    $this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "boss", array()), "setRendered", array());
                    // line 83
                    echo "            ";
                }
                // line 84
                echo "        ";
            }
            // line 85
            echo "
        ";
            // line 87
            echo "        <p>
        ";
            // line 88
            if ($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "positions", array(), "any", true, true)) {
                // line 89
                echo "            ";
                echo $context["formmacros"]->getfield($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "positions", array()));
                echo "
        ";
            }
            // line 91
            echo "
        ";
            // line 93
            echo "        ";
            if ($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "residencyTrack", array(), "any", true, true)) {
                // line 94
                echo "            <p>
            <div class=\"well appointmenttitle-residencytrack-field\" style=\"display:none; margin-top: 20px;\">
                ";
                // line 96
                echo $context["formmacros"]->getfield($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "residencyTrack", array()));
                echo "
            </div>
            </p>
        ";
            }
            // line 100
            echo "
        ";
            // line 101
            if ($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "fellowshipType", array(), "any", true, true)) {
                // line 102
                echo "            <p>
            <div class=\"well appointmenttitle-fellowshiptype-field\" style=\"display:none; margin-top: 20px;\">
                ";
                // line 104
                echo $context["formmacros"]->getfield($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "fellowshipType", array()));
                echo "
            </div>
            </p>
        ";
            }
            // line 108
            echo "
        ";
            // line 109
            $context["ResidentOrFellowPosition"] = false;
            // line 110
            echo "        ";
            if ($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "positions", array(), "any", true, true)) {
                // line 111
                echo "            ";
                $context['_parent'] = $context;
                $context['_seq'] = twig_ensure_traversable($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "positions", array()));
                foreach ($context['_seq'] as $context["_key"] => $context["position"]) {
                    // line 112
                    echo "                ";
                    if ((($this->getAttribute($context["position"], "name", array()) == "Resident") || ($this->getAttribute($context["position"], "name", array()) == "Fellow"))) {
                        // line 113
                        echo "                    ";
                        $context["ResidentOrFellowPosition"] = true;
                        // line 114
                        echo "                ";
                    }
                    // line 115
                    echo "            ";
                }
                $_parent = $context['_parent'];
                unset($context['_seq'], $context['_iterated'], $context['_key'], $context['position'], $context['_parent'], $context['loop']);
                $context = array_intersect_key($context, $_parent) + $_parent;
                // line 116
                echo "        ";
            }
            // line 117
            echo "
        ";
            // line 119
            echo "        ";
            if (($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "pgystart", array(), "any", true, true) && $this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "pgylevel", array(), "any", true, true))) {
                // line 120
                echo "            ";
                // line 121
                echo "            ";
                if ((((($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "positions", array(), "any", true, true) && $this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "vars", array()), "value", array())) && (isset($context["ResidentOrFellowPosition"]) ? $context["ResidentOrFellowPosition"] : null)) || ((isset($context["cycle"]) ? $context["cycle"] : null) == "create_user")) || ((isset($context["cycle"]) ? $context["cycle"] : null) == "edit_user"))) {
                    // line 122
                    echo "                <p>
                <div class=\"well appointmenttitle-pgy-field\" style=\"display:none\">
                    ";
                    // line 124
                    echo $context["formmacros"]->getfieldDateLabel($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "pgystart", array()), "allow-future-date");
                    echo "
                    ";
                    // line 125
                    echo $context["formmacros"]->getfield($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "pgylevel", array()));
                    echo "
                    ";
                    // line 126
                    echo $context["formmacros"]->getfield($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "pgylevelexpected", array()));
                    echo "
                    <div class=\"row\">
                        <div class=\"col-xs-6\" align=\"right\">
                            <strong></strong>
                        </div>
                        ";
                    // line 131
                    if (((isset($context["cycle"]) ? $context["cycle"] : null) != "show_user")) {
                        // line 132
                        echo "                            <div class=\"col-xs-6\" align=\"left\">
                                <button type=\"button\" class=\"update-pgy-btn\" onclick=\"updatePgy(this)\">Update the academic year and PGY level to current</button>
                            </div>
                        ";
                    }
                    // line 136
                    echo "                    </div>
                </div>
                </p>
            ";
                } else {
                    // line 140
                    echo "                ";
                    $this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "pgystart", array()), "setRendered", array());
                    // line 141
                    echo "                ";
                    $this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "pgylevel", array()), "setRendered", array());
                    // line 142
                    echo "                ";
                    $this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "pgylevelexpected", array()), "setRendered", array());
                    // line 143
                    echo "            ";
                }
                // line 144
                echo "        ";
            }
            // line 145
            echo "
        </p>


        ";
            // line 149
            echo $context["formmacros"]->getfieldDateLabel($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "startDate", array()), "allow-future-date");
            echo "
        ";
            // line 150
            echo $context["formmacros"]->getfieldDateLabel_notempty($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "endDate", array()), (isset($context["cycle"]) ? $context["cycle"] : null), "allow-future-date");
            echo "

        ";
            // line 153
            echo "        ";
            if (($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "effort", array(), "any", true, true) && (((isset($context["cycle"]) ? $context["cycle"] : null) != "show_user") || ($this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "vars", array()), "value", array()) && $this->getAttribute($this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "vars", array()), "value", array()), "effort", array()))))) {
                // line 154
                echo "            ";
                echo $context["formmacros"]->getfield($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "effort", array()));
                echo "
        ";
            } else {
                // line 156
                echo "            ";
                if ($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "effort", array(), "any", true, true)) {
                    // line 157
                    echo "                ";
                    $this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "effort", array()), "setRendered", array());
                    // line 158
                    echo "            ";
                }
                // line 159
                echo "        ";
            }
            // line 160
            echo "
        ";
            // line 161
            if ($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "institution", array(), "any", true, true)) {
                // line 162
                echo "            ";
                echo $context["treemacros"]->getcompositeTreeNode($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "institution", array()), (isset($context["cycle"]) ? $context["cycle"] : null), (isset($context["prototype"]) ? $context["prototype"] : null));
                echo "
        ";
            }
            // line 164
            echo "
        ";
            // line 165
            if ($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "userPositions", array(), "any", true, true)) {
                // line 166
                echo "            ";
                echo $context["formmacros"]->getfield($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "userPositions", array()));
                echo "
        ";
            }
            // line 168
            echo "
    </div>
";
        } catch (Exception $e) {
            ob_end_clean();

            throw $e;
        } catch (Throwable $e) {
            ob_end_clean();

            throw $e;
        }

        return ('' === $tmp = ob_get_clean()) ? '' : new Twig_Markup($tmp, $this->env->getCharset());
    }

    // line 175
    public function getstateLicenses($__field__ = null, $__cycle__ = null, $__classname__ = null, $__prototype__ = null, ...$__varargs__)
    {
        $context = $this->env->mergeGlobals(array(
            "field" => $__field__,
            "cycle" => $__cycle__,
            "classname" => $__classname__,
            "prototype" => $__prototype__,
            "varargs" => $__varargs__,
        ));

        $blocks = array();

        ob_start();
        try {
            // line 176
            echo "    ";
            $context["formmacros"] = $this->loadTemplate("OlegOrderformBundle::Default/formmacros.html.twig", "OlegUserdirectoryBundle::Default/userformmacros.html.twig", 176);
            // line 177
            echo "
    ";
            // line 178
            if (((isset($context["prototype"]) ? $context["prototype"] : null) == "prototype")) {
                // line 179
                echo "        ";
                $context["formfield"] = $this->getAttribute($this->getAttribute((isset($context["field"]) ? $context["field"] : null), "vars", array()), "prototype", array());
                // line 180
                echo "    ";
            } else {
                // line 181
                echo "        ";
                $context["formfield"] = (isset($context["field"]) ? $context["field"] : null);
                // line 182
                echo "    ";
            }
            // line 183
            echo "
    <div class=\"user-collection-holder well ";
            // line 184
            echo twig_escape_filter($this->env, (isset($context["classname"]) ? $context["classname"] : null), "html", null, true);
            echo "\">

        ";
            // line 186
            if (((isset($context["cycle"]) ? $context["cycle"] : null) != "show_user")) {
                // line 187
                echo "            <div class=\"text-right\">
                <button type=\"button\" class=\"btn btn-default btn-sm\" onClick=\"removeExistingObject(this,'";
                // line 188
                echo twig_escape_filter($this->env, (isset($context["classname"]) ? $context["classname"] : null), "html", null, true);
                echo "')\" ><span class=\"glyphicon glyphicon-remove\"></span></button>
            </div>
        ";
            }
            // line 191
            echo "
        ";
            // line 192
            echo $context["formmacros"]->getfield_notempty($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "country", array()), (isset($context["cycle"]) ? $context["cycle"] : null));
            echo "
        ";
            // line 193
            echo $context["formmacros"]->getfield($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "state", array()));
            echo "
        ";
            // line 194
            echo $context["formmacros"]->getfield($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "licenseNumber", array()));
            echo "
        ";
            // line 195
            echo $context["formmacros"]->getfieldDateLabel($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "licenseIssuedDate", array()), "allow-future-date");
            echo "
        ";
            // line 196
            echo $context["formmacros"]->getfieldDateLabel($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "licenseExpirationDate", array()), "allow-future-date");
            echo "
        ";
            // line 197
            echo $context["formmacros"]->getfield($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "active", array()));
            echo "

        ";
            // line 199
            if ($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "attachmentContainer", array(), "any", true, true)) {
                // line 200
                echo "
            ";
                // line 201
                $context["dropzoneInit"] = "default";
                // line 202
                echo "            ";
                // line 203
                echo "                ";
                // line 204
                echo "            ";
                // line 205
                echo "
            ";
                // line 206
                $context["count"] = 0;
                // line 207
                echo "            ";
                $context['_parent'] = $context;
                $context['_seq'] = twig_ensure_traversable($this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "attachmentContainer", array()), "documentContainers", array()));
                foreach ($context['_seq'] as $context["_key"] => $context["documentContainer"]) {
                    // line 208
                    echo "                ";
                    // line 209
                    echo "                ";
                    $context["count"] = ((isset($context["count"]) ? $context["count"] : null) + 1);
                    // line 210
                    echo "                ";
                    $context["uniqueId"] = (((isset($context["count"]) ? $context["count"] : null) . "-") . twig_date_format_filter($this->env, "now", "mdYHisu"));
                    // line 211
                    echo "                ";
                    echo $context["formmacros"]->getfieldDocumentContainer($context["documentContainer"], (isset($context["cycle"]) ? $context["cycle"] : null), ("statelicense" . (isset($context["uniqueId"]) ? $context["uniqueId"] : null)), "", 20, (isset($context["dropzoneInit"]) ? $context["dropzoneInit"] : null));
                    echo "
            ";
                }
                $_parent = $context['_parent'];
                unset($context['_seq'], $context['_iterated'], $context['_key'], $context['documentContainer'], $context['_parent'], $context['loop']);
                $context = array_intersect_key($context, $_parent) + $_parent;
                // line 213
                echo "
            ";
                // line 214
                if (((isset($context["prototype"]) ? $context["prototype"] : null) == "prototype")) {
                    // line 215
                    echo "                ";
                    $context["uniqueId"] = ("1-" . twig_date_format_filter($this->env, "now", "mdYHisu"));
                    // line 216
                    echo "                ";
                    echo $context["formmacros"]->getfieldDocumentContainer($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "attachmentContainer", array()), "documentContainers", array()), "vars", array()), "prototype", array()), (isset($context["cycle"]) ? $context["cycle"] : null), ("statelicense" . (isset($context["uniqueId"]) ? $context["uniqueId"] : null)), "", 20, (isset($context["dropzoneInit"]) ? $context["dropzoneInit"] : null));
                    echo "
            ";
                }
                // line 218
                echo "
        ";
            }
            // line 220
            echo "
    </div>
";
        } catch (Exception $e) {
            ob_end_clean();

            throw $e;
        } catch (Throwable $e) {
            ob_end_clean();

            throw $e;
        }

        return ('' === $tmp = ob_get_clean()) ? '' : new Twig_Markup($tmp, $this->env->getCharset());
    }

    // line 225
    public function getboardCertifications($__field__ = null, $__cycle__ = null, $__classname__ = null, $__prototype__ = null, ...$__varargs__)
    {
        $context = $this->env->mergeGlobals(array(
            "field" => $__field__,
            "cycle" => $__cycle__,
            "classname" => $__classname__,
            "prototype" => $__prototype__,
            "varargs" => $__varargs__,
        ));

        $blocks = array();

        ob_start();
        try {
            // line 226
            echo "    ";
            $context["formmacros"] = $this->loadTemplate("OlegOrderformBundle::Default/formmacros.html.twig", "OlegUserdirectoryBundle::Default/userformmacros.html.twig", 226);
            // line 227
            echo "
    ";
            // line 228
            if (((isset($context["prototype"]) ? $context["prototype"] : null) == "prototype")) {
                // line 229
                echo "        ";
                $context["formfield"] = $this->getAttribute($this->getAttribute((isset($context["field"]) ? $context["field"] : null), "vars", array()), "prototype", array());
                // line 230
                echo "    ";
            } else {
                // line 231
                echo "        ";
                $context["formfield"] = (isset($context["field"]) ? $context["field"] : null);
                // line 232
                echo "    ";
            }
            // line 233
            echo "
    <div class=\"user-collection-holder well ";
            // line 234
            echo twig_escape_filter($this->env, (isset($context["classname"]) ? $context["classname"] : null), "html", null, true);
            echo "\">

        ";
            // line 236
            if (((isset($context["cycle"]) ? $context["cycle"] : null) != "show_user")) {
                // line 237
                echo "            <div class=\"text-right\">
                <button type=\"button\" class=\"btn btn-default btn-sm\" onClick=\"removeExistingObject(this,'";
                // line 238
                echo twig_escape_filter($this->env, (isset($context["classname"]) ? $context["classname"] : null), "html", null, true);
                echo "')\" ><span class=\"glyphicon glyphicon-remove\"></span></button>
            </div>
        ";
            }
            // line 241
            echo "
        ";
            // line 242
            echo $context["formmacros"]->getfield($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "certifyingBoardOrganization", array()));
            echo "
        ";
            // line 243
            echo $context["formmacros"]->getfield($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "specialty", array()));
            echo "
        ";
            // line 244
            echo $context["formmacros"]->getfieldDateLabel($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "issueDate", array()), "allow-future-date");
            echo "
        ";
            // line 245
            echo $context["formmacros"]->getfieldDateLabel($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "expirationDate", array()), "allow-future-date");
            echo "
        ";
            // line 246
            echo $context["formmacros"]->getfieldDateLabel($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "recertificationDate", array()), "allow-future-date");
            echo "

        ";
            // line 248
            if ($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "attachmentContainer", array(), "any", true, true)) {
                // line 249
                echo "
            ";
                // line 250
                $context["dropzoneInit"] = "default";
                // line 251
                echo "            ";
                $context["count"] = 0;
                // line 252
                echo "            ";
                $context['_parent'] = $context;
                $context['_seq'] = twig_ensure_traversable($this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "attachmentContainer", array()), "documentContainers", array()));
                foreach ($context['_seq'] as $context["_key"] => $context["documentContainer"]) {
                    // line 253
                    echo "                ";
                    $context["count"] = ((isset($context["count"]) ? $context["count"] : null) + 1);
                    // line 254
                    echo "                ";
                    $context["uniqueId"] = (((isset($context["count"]) ? $context["count"] : null) . "-") . twig_date_format_filter($this->env, "now", "mdYHisu"));
                    // line 255
                    echo "                ";
                    echo $context["formmacros"]->getfieldDocumentContainer($context["documentContainer"], (isset($context["cycle"]) ? $context["cycle"] : null), ("boardcertification" . (isset($context["uniqueId"]) ? $context["uniqueId"] : null)), "", 20, (isset($context["dropzoneInit"]) ? $context["dropzoneInit"] : null));
                    echo "
            ";
                }
                $_parent = $context['_parent'];
                unset($context['_seq'], $context['_iterated'], $context['_key'], $context['documentContainer'], $context['_parent'], $context['loop']);
                $context = array_intersect_key($context, $_parent) + $_parent;
                // line 257
                echo "
            ";
                // line 258
                if (((isset($context["prototype"]) ? $context["prototype"] : null) == "prototype")) {
                    // line 259
                    echo "                ";
                    $context["uniqueId"] = ("1-" . twig_date_format_filter($this->env, "now", "mdYHisu"));
                    // line 260
                    echo "                ";
                    echo $context["formmacros"]->getfieldDocumentContainer($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "attachmentContainer", array()), "documentContainers", array()), "vars", array()), "prototype", array()), (isset($context["cycle"]) ? $context["cycle"] : null), ("boardcertification" . (isset($context["uniqueId"]) ? $context["uniqueId"] : null)), "", 20, (isset($context["dropzoneInit"]) ? $context["dropzoneInit"] : null));
                    echo "
            ";
                }
                // line 262
                echo "
        ";
            }
            // line 264
            echo "
    </div>
";
        } catch (Exception $e) {
            ob_end_clean();

            throw $e;
        } catch (Throwable $e) {
            ob_end_clean();

            throw $e;
        }

        return ('' === $tmp = ob_get_clean()) ? '' : new Twig_Markup($tmp, $this->env->getCharset());
    }

    // line 269
    public function getcodenyphs($__field__ = null, $__cycle__ = null, $__classname__ = null, $__prototype__ = null, ...$__varargs__)
    {
        $context = $this->env->mergeGlobals(array(
            "field" => $__field__,
            "cycle" => $__cycle__,
            "classname" => $__classname__,
            "prototype" => $__prototype__,
            "varargs" => $__varargs__,
        ));

        $blocks = array();

        ob_start();
        try {
            // line 270
            echo "    ";
            $context["formmacros"] = $this->loadTemplate("OlegOrderformBundle::Default/formmacros.html.twig", "OlegUserdirectoryBundle::Default/userformmacros.html.twig", 270);
            // line 271
            echo "
    ";
            // line 272
            if (((isset($context["prototype"]) ? $context["prototype"] : null) == "prototype")) {
                // line 273
                echo "        ";
                $context["formfield"] = $this->getAttribute($this->getAttribute((isset($context["field"]) ? $context["field"] : null), "vars", array()), "prototype", array());
                // line 274
                echo "    ";
            } else {
                // line 275
                echo "        ";
                $context["formfield"] = (isset($context["field"]) ? $context["field"] : null);
                // line 276
                echo "    ";
            }
            // line 277
            echo "
    <div class=\"well ";
            // line 278
            echo twig_escape_filter($this->env, (isset($context["classname"]) ? $context["classname"] : null), "html", null, true);
            echo "\">

        ";
            // line 280
            if (((isset($context["cycle"]) ? $context["cycle"] : null) != "show_user")) {
                // line 281
                echo "            <div class=\"text-right\">
                <button type=\"button\" class=\"btn btn-default btn-sm\" onClick=\"removeExistingObject(this,'";
                // line 282
                echo twig_escape_filter($this->env, (isset($context["classname"]) ? $context["classname"] : null), "html", null, true);
                echo "')\" ><span class=\"glyphicon glyphicon-remove\"></span></button>
            </div>
        ";
            }
            // line 285
            echo "
        ";
            // line 286
            echo $context["formmacros"]->getfield_notempty($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "field", array()), (isset($context["cycle"]) ? $context["cycle"] : null));
            echo "
        ";
            // line 287
            echo $context["formmacros"]->getfieldDateLabel_notempty($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "startDate", array()), (isset($context["cycle"]) ? $context["cycle"] : null), "allow-future-date");
            echo "
        ";
            // line 288
            echo $context["formmacros"]->getfieldDateLabel_notempty($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "endDate", array()), (isset($context["cycle"]) ? $context["cycle"] : null), "allow-future-date");
            echo "

    </div>
";
        } catch (Exception $e) {
            ob_end_clean();

            throw $e;
        } catch (Throwable $e) {
            ob_end_clean();

            throw $e;
        }

        return ('' === $tmp = ob_get_clean()) ? '' : new Twig_Markup($tmp, $this->env->getCharset());
    }

    // line 293
    public function getemploymentStatuses($__field__ = null, $__cycle__ = null, $__classname__ = null, $__prototype__ = null, ...$__varargs__)
    {
        $context = $this->env->mergeGlobals(array(
            "field" => $__field__,
            "cycle" => $__cycle__,
            "classname" => $__classname__,
            "prototype" => $__prototype__,
            "varargs" => $__varargs__,
        ));

        $blocks = array();

        ob_start();
        try {
            // line 294
            echo "    ";
            $context["formmacros"] = $this->loadTemplate("OlegOrderformBundle::Default/formmacros.html.twig", "OlegUserdirectoryBundle::Default/userformmacros.html.twig", 294);
            // line 295
            echo "    ";
            $context["treemacros"] = $this->loadTemplate("OlegUserdirectoryBundle::Tree/treemacros.html.twig", "OlegUserdirectoryBundle::Default/userformmacros.html.twig", 295);
            // line 296
            echo "
    ";
            // line 297
            if (((isset($context["prototype"]) ? $context["prototype"] : null) == "prototype")) {
                // line 298
                echo "        ";
                $context["formfield"] = $this->getAttribute($this->getAttribute((isset($context["field"]) ? $context["field"] : null), "vars", array()), "prototype", array());
                // line 299
                echo "    ";
            } else {
                // line 300
                echo "        ";
                $context["formfield"] = (isset($context["field"]) ? $context["field"] : null);
                // line 301
                echo "    ";
            }
            // line 302
            echo "
    <div class=\"user-collection-holder well ";
            // line 303
            echo twig_escape_filter($this->env, (isset($context["classname"]) ? $context["classname"] : null), "html", null, true);
            echo "\">

        ";
            // line 305
            if (((isset($context["cycle"]) ? $context["cycle"] : null) != "show_user")) {
                // line 306
                echo "            ";
                if (( !$this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "vars", array()), "value", array()) || ($this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "vars", array()), "value", array()) && ($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "vars", array()), "value", array()), "id", array()) != $this->getAttribute($this->getAttribute((isset($context["app"]) ? $context["app"] : null), "user", array()), "getId", array(), "method"))))) {
                    // line 307
                    echo "                <div class=\"text-right\">
                    <button type=\"button\" class=\"btn btn-default btn-sm btn-remove-minimumone-collection confirm-delete-with-expired\"
                            onClick=\"removeExistingObject(this,'";
                    // line 309
                    echo twig_escape_filter($this->env, (isset($context["classname"]) ? $context["classname"] : null), "html", null, true);
                    echo "')\" ><span class=\"glyphicon glyphicon-remove\"></span></button>
                </div>
            ";
                }
                // line 312
                echo "        ";
            }
            // line 313
            echo "
        ";
            // line 314
            echo $context["formmacros"]->getfieldDateLabel($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "hireDate", array()), "allow-future-date");
            echo "
        ";
            // line 315
            echo $context["formmacros"]->getfield($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "employmentType", array()));
            echo "
        ";
            // line 316
            echo $context["formmacros"]->getfield($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "jobDescriptionSummary", array()));
            echo "
        ";
            // line 317
            echo $context["formmacros"]->getfield($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "jobDescription", array()));
            echo "

        ";
            // line 319
            if ($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "institution", array(), "any", true, true)) {
                // line 320
                echo "            ";
                echo $context["treemacros"]->getcompositeTreeNode($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "institution", array()), (isset($context["cycle"]) ? $context["cycle"] : null), (isset($context["prototype"]) ? $context["prototype"] : null));
                echo "
        ";
            }
            // line 322
            echo "
        ";
            // line 323
            echo $context["formmacros"]->getfieldDateLabel_notempty($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "terminationDate", array()), (isset($context["cycle"]) ? $context["cycle"] : null), "allow-future-date");
            echo "
        ";
            // line 324
            echo $context["formmacros"]->getfield_notempty($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "terminationType", array()), (isset($context["cycle"]) ? $context["cycle"] : null));
            echo "

        ";
            // line 327
            echo "        ";
            if ($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "terminationReason", array(), "any", true, true)) {
                // line 328
                echo "            ";
                echo $context["formmacros"]->getfield_notempty($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "terminationReason", array()), (isset($context["cycle"]) ? $context["cycle"] : null));
                echo "
        ";
            }
            // line 330
            echo "

        ";
            // line 332
            if ($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "attachmentContainer", array(), "any", true, true)) {
                // line 333
                echo "
            ";
                // line 334
                $context["dropzoneInit"] = "default";
                // line 335
                echo "            ";
                if (($this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "vars", array()), "value", array()) && ($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "vars", array()), "value", array()), "id", array()) == $this->getAttribute($this->getAttribute((isset($context["app"]) ? $context["app"] : null), "user", array()), "getId", array(), "method")))) {
                    // line 336
                    echo "                ";
                    $context["dropzoneInit"] = "inactive";
                    // line 337
                    echo "            ";
                }
                // line 338
                echo "
            ";
                // line 339
                $context["count"] = 0;
                // line 340
                echo "            ";
                $context['_parent'] = $context;
                $context['_seq'] = twig_ensure_traversable($this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "attachmentContainer", array()), "documentContainers", array()));
                foreach ($context['_seq'] as $context["_key"] => $context["documentContainer"]) {
                    // line 341
                    echo "                ";
                    // line 342
                    echo "                ";
                    $context["count"] = ((isset($context["count"]) ? $context["count"] : null) + 1);
                    // line 343
                    echo "                ";
                    $context["uniqueId"] = (((isset($context["count"]) ? $context["count"] : null) . "-") . twig_date_format_filter($this->env, "now", "mdYHisu"));
                    // line 344
                    echo "                ";
                    echo $context["formmacros"]->getfieldDocumentContainer($context["documentContainer"], (isset($context["cycle"]) ? $context["cycle"] : null), ("employment" . (isset($context["uniqueId"]) ? $context["uniqueId"] : null)), "", 20, (isset($context["dropzoneInit"]) ? $context["dropzoneInit"] : null));
                    echo "
            ";
                }
                $_parent = $context['_parent'];
                unset($context['_seq'], $context['_iterated'], $context['_key'], $context['documentContainer'], $context['_parent'], $context['loop']);
                $context = array_intersect_key($context, $_parent) + $_parent;
                // line 346
                echo "
            ";
                // line 347
                if (((isset($context["prototype"]) ? $context["prototype"] : null) == "prototype")) {
                    // line 348
                    echo "                ";
                    $context["uniqueId"] = ("1-" . twig_date_format_filter($this->env, "now", "mdYHisu"));
                    // line 349
                    echo "                ";
                    echo $context["formmacros"]->getfieldDocumentContainer($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "attachmentContainer", array()), "documentContainers", array()), "vars", array()), "prototype", array()), (isset($context["cycle"]) ? $context["cycle"] : null), ("employment" . (isset($context["uniqueId"]) ? $context["uniqueId"] : null)), "", 20, (isset($context["dropzoneInit"]) ? $context["dropzoneInit"] : null));
                    echo "
            ";
                }
                // line 351
                echo "
        ";
            }
            // line 353
            echo "

    </div>
";
        } catch (Exception $e) {
            ob_end_clean();

            throw $e;
        } catch (Throwable $e) {
            ob_end_clean();

            throw $e;
        }

        return ('' === $tmp = ob_get_clean()) ? '' : new Twig_Markup($tmp, $this->env->getCharset());
    }

    // line 362
    public function getidentifier($__field__ = null, $__cycle__ = null, $__classname__ = null, $__prototype__ = null, $__isEntity__ = null, ...$__varargs__)
    {
        $context = $this->env->mergeGlobals(array(
            "field" => $__field__,
            "cycle" => $__cycle__,
            "classname" => $__classname__,
            "prototype" => $__prototype__,
            "isEntity" => $__isEntity__,
            "varargs" => $__varargs__,
        ));

        $blocks = array();

        ob_start();
        try {
            // line 363
            echo "    ";
            $context["formmacros"] = $this->loadTemplate("OlegOrderformBundle::Default/formmacros.html.twig", "OlegUserdirectoryBundle::Default/userformmacros.html.twig", 363);
            // line 364
            echo "    ";
            $context["usermacros"] = $this->loadTemplate("OlegUserdirectoryBundle::Default/usermacros.html.twig", "OlegUserdirectoryBundle::Default/userformmacros.html.twig", 364);
            // line 365
            echo "
    ";
            // line 366
            if ( !array_key_exists("isEntity", $context)) {
                // line 367
                echo "        ";
                $context["isEntity"] = false;
                // line 368
                echo "    ";
            }
            // line 369
            echo "
    ";
            // line 370
            if (((isset($context["prototype"]) ? $context["prototype"] : null) == "prototype")) {
                // line 371
                echo "        ";
                $context["formfield"] = $this->getAttribute($this->getAttribute((isset($context["field"]) ? $context["field"] : null), "vars", array()), "prototype", array());
                // line 372
                echo "    ";
            } else {
                // line 373
                echo "        ";
                $context["formfield"] = (isset($context["field"]) ? $context["field"] : null);
                // line 374
                echo "    ";
            }
            // line 375
            echo "
    ";
            // line 376
            if ((isset($context["isEntity"]) ? $context["isEntity"] : null)) {
                // line 377
                echo "        ";
                $context["formfieldValue"] = (isset($context["field"]) ? $context["field"] : null);
                // line 378
                echo "    ";
            } else {
                // line 379
                echo "        ";
                $context["formfieldValue"] = $this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "vars", array()), "value", array());
                // line 380
                echo "    ";
            }
            // line 381
            echo "
    ";
            // line 382
            if ((((isset($context["formfieldValue"]) ? $context["formfieldValue"] : null) && ($this->getAttribute((isset($context["formfieldValue"]) ? $context["formfieldValue"] : null), "status", array()) == 0)) || ((isset($context["prototype"]) ? $context["prototype"] : null) == "prototype"))) {
                // line 383
                echo "        ";
                $context["wellclass"] = "user-alert-warning";
                // line 384
                echo "    ";
            } else {
                // line 385
                echo "        ";
                $context["wellclass"] = "";
                // line 386
                echo "    ";
            }
            // line 387
            echo "
    ";
            // line 388
            $context["showThis"] = false;
            // line 389
            echo "    ";
            if ((isset($context["isEntity"]) ? $context["isEntity"] : null)) {
                // line 390
                echo "        ";
                if (($this->getAttribute((isset($context["formfieldValue"]) ? $context["formfieldValue"] : null), "publiclyVisible", array()) == true)) {
                    // line 391
                    echo "            ";
                    $context["showThis"] = true;
                    // line 392
                    echo "        ";
                }
                // line 393
                echo "    ";
            } else {
                // line 394
                echo "        ";
                $context["showThis"] = true;
                // line 395
                echo "    ";
            }
            // line 396
            echo "
    ";
            // line 397
            if ((isset($context["showThis"]) ? $context["showThis"] : null)) {
                // line 398
                echo "        <div class=\"alert ";
                echo twig_escape_filter($this->env, (isset($context["classname"]) ? $context["classname"] : null), "html", null, true);
                echo " ";
                echo twig_escape_filter($this->env, (isset($context["wellclass"]) ? $context["wellclass"] : null), "html", null, true);
                echo "\">

            ";
                // line 400
                if ((((isset($context["cycle"]) ? $context["cycle"] : null) != "show_user") &&  !(isset($context["isEntity"]) ? $context["isEntity"] : null))) {
                    // line 401
                    echo "                <div class=\"text-right\" style=\"padding-bottom: 10px\">
                    <button type=\"button\" class=\"btn btn-default btn-sm\"
                            onClick=\"removeExistingObject(this,'";
                    // line 403
                    echo twig_escape_filter($this->env, (isset($context["classname"]) ? $context["classname"] : null), "html", null, true);
                    echo "')\" >
                        <span class=\"glyphicon glyphicon-remove\"></span>
                    </button>
                </div>
            ";
                }
                // line 408
                echo "
            ";
                // line 409
                if (( !(isset($context["isEntity"]) ? $context["isEntity"] : null) && $this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "publiclyVisible", array(), "any", true, true))) {
                    // line 410
                    echo "                ";
                    echo $context["formmacros"]->getfield($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "publiclyVisible", array()));
                    echo "
            ";
                }
                // line 412
                echo "
            ";
                // line 413
                echo $context["usermacros"]->getstatusVerifiedField((isset($context["formfield"]) ? $context["formfield"] : null), (isset($context["cycle"]) ? $context["cycle"] : null), (isset($context["isEntity"]) ? $context["isEntity"] : null));
                echo "

            ";
                // line 415
                if ((isset($context["isEntity"]) ? $context["isEntity"] : null)) {
                    // line 416
                    echo "                ";
                    echo $context["formmacros"]->getsimplefield("Identifier Type:", $this->getAttribute((isset($context["formfieldValue"]) ? $context["formfieldValue"] : null), "keytype", array()), "", "disabled");
                    echo "
            ";
                } else {
                    // line 418
                    echo "                ";
                    echo $context["formmacros"]->getfield($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "keytype", array()));
                    echo "
            ";
                }
                // line 420
                echo "
            ";
                // line 421
                if ($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "keytypemrn", array(), "any", true, true)) {
                    // line 422
                    echo "                ";
                    // line 423
                    echo "                ";
                    // line 424
                    echo "                ";
                    if ((((isset($context["formfieldValue"]) ? $context["formfieldValue"] : null) && $this->getAttribute((isset($context["formfieldValue"]) ? $context["formfieldValue"] : null), "keytypemrn", array())) && $this->getAttribute($this->getAttribute((isset($context["formfieldValue"]) ? $context["formfieldValue"] : null), "keytypemrn", array()), "id", array()))) {
                        // line 425
                        echo "                    ";
                        $context["displayNone"] = "";
                        // line 426
                        echo "                    ";
                        // line 427
                        echo "                ";
                    } else {
                        // line 428
                        echo "                    <p></p>
                    ";
                        // line 429
                        $context["displayNone"] = "display:none;";
                        // line 430
                        echo "                ";
                    }
                    // line 431
                    echo "
                <div class=\"identifier-keytypemrn-field-holder\" style=\"";
                    // line 432
                    echo twig_escape_filter($this->env, (isset($context["displayNone"]) ? $context["displayNone"] : null), "html", null, true);
                    echo "\">
                    ";
                    // line 433
                    if ((isset($context["isEntity"]) ? $context["isEntity"] : null)) {
                        // line 434
                        echo "                        ";
                        echo $context["formmacros"]->getsimplefield("MRN Type:", $this->getAttribute((isset($context["formfieldValue"]) ? $context["formfieldValue"] : null), "keytype", array()), "", "disabled");
                        echo "
                    ";
                    } else {
                        // line 436
                        echo "                        ";
                        echo $context["formmacros"]->getfield($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "keytypemrn", array()));
                        echo "
                    ";
                    }
                    // line 438
                    echo "                </div>
            ";
                }
                // line 440
                echo "
            ";
                // line 441
                if (((isset($context["cycle"]) ? $context["cycle"] : null) == "show_user")) {
                    // line 442
                    echo "
                ";
                    // line 443
                    if ((((isset($context["isEntity"]) ? $context["isEntity"] : null) || $this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "link", array(), "any", true, true)) && ((isset($context["formfieldValue"]) ? $context["formfieldValue"] : null) && ($this->getAttribute((isset($context["formfieldValue"]) ? $context["formfieldValue"] : null), "link", array()) != "")))) {
                        // line 444
                        echo "
                    ";
                        // line 445
                        if (twig_in_filter("http", $this->getAttribute((isset($context["formfieldValue"]) ? $context["formfieldValue"] : null), "link", array()))) {
                            // line 446
                            echo "                        ";
                            $context["href"] = $this->getAttribute((isset($context["formfieldValue"]) ? $context["formfieldValue"] : null), "link", array());
                            // line 447
                            echo "                    ";
                        } else {
                            // line 448
                            echo "                        ";
                            $context["href"] = ("http://" . $this->getAttribute((isset($context["formfieldValue"]) ? $context["formfieldValue"] : null), "link", array()));
                            // line 449
                            echo "                    ";
                        }
                        // line 450
                        echo "
                    ";
                        // line 451
                        if ((isset($context["isEntity"]) ? $context["isEntity"] : null)) {
                            // line 452
                            echo "                        ";
                            $context["hreflink"] = (((("<a href=\"" . (isset($context["href"]) ? $context["href"] : null)) . "\">") . $this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "field", array())) . "</a>");
                            // line 453
                            echo "                    ";
                        } else {
                            // line 454
                            echo "                        ";
                            $context["hreflink"] = (((("<a href=\"" . (isset($context["href"]) ? $context["href"] : null)) . "\">") . $this->getAttribute($this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "field", array()), "vars", array()), "value", array())) . "</a>");
                            // line 455
                            echo "                    ";
                        }
                        // line 456
                        echo "
                    ";
                        // line 457
                        echo $context["formmacros"]->getsimplefield("Identifier:", (isset($context["hreflink"]) ? $context["hreflink"] : null), "", "disabled");
                        echo "

                ";
                    } else {
                        // line 460
                        echo "
                    ";
                        // line 462
                        echo "                    ";
                        if ((isset($context["isEntity"]) ? $context["isEntity"] : null)) {
                            // line 463
                            echo "                        ";
                            echo $context["formmacros"]->getsimplefield("Identifier:", $this->getAttribute((isset($context["formfieldValue"]) ? $context["formfieldValue"] : null), "field", array()), "", "disabled");
                            echo "
                     ";
                        } else {
                            // line 465
                            echo "                        ";
                            echo $context["formmacros"]->getfield($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "field", array()));
                            echo "
                    ";
                        }
                        // line 467
                        echo "
                ";
                    }
                    // line 469
                    echo "
            ";
                } else {
                    // line 471
                    echo "                ";
                    echo $context["formmacros"]->getfield($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "field", array()));
                    echo "
            ";
                }
                // line 473
                echo "
            ";
                // line 474
                if ((isset($context["isEntity"]) ? $context["isEntity"] : null)) {
                    // line 475
                    echo "                ";
                    echo $context["formmacros"]->getsimplefield_checkbox("Identifier enables system/service access:", $this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "enableAccess", array()), "", "disabled");
                    echo "
            ";
                } else {
                    // line 477
                    echo "                ";
                    echo $context["formmacros"]->getfield($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "enableAccess", array()));
                    echo "
            ";
                }
                // line 479
                echo "
            ";
                // line 480
                if (($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "link", array(), "any", true, true) && ((isset($context["cycle"]) ? $context["cycle"] : null) != "show_user"))) {
                    // line 481
                    echo "                ";
                    echo $context["formmacros"]->getfield($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "link", array()));
                    echo "
            ";
                }
                // line 483
                echo "
        </div>
    ";
            }
            // line 486
            echo "
    ";
            // line 487
            if ( !(isset($context["isEntity"]) ? $context["isEntity"] : null)) {
                // line 488
                echo "        ";
                $this->getAttribute((isset($context["field"]) ? $context["field"] : null), "setRendered", array());
                // line 489
                echo "    ";
            }
        } catch (Exception $e) {
            ob_end_clean();

            throw $e;
        } catch (Throwable $e) {
            ob_end_clean();

            throw $e;
        }

        return ('' === $tmp = ob_get_clean()) ? '' : new Twig_Markup($tmp, $this->env->getCharset());
    }

    // line 522
    public function getcomments($__field__ = null, $__cycle__ = null, $__classname__ = null, $__prototype__ = null, ...$__varargs__)
    {
        $context = $this->env->mergeGlobals(array(
            "field" => $__field__,
            "cycle" => $__cycle__,
            "classname" => $__classname__,
            "prototype" => $__prototype__,
            "varargs" => $__varargs__,
        ));

        $blocks = array();

        ob_start();
        try {
            // line 523
            echo "    ";
            $context["formmacros"] = $this->loadTemplate("OlegOrderformBundle::Default/formmacros.html.twig", "OlegUserdirectoryBundle::Default/userformmacros.html.twig", 523);
            // line 524
            echo "    ";
            $context["usermacros"] = $this->loadTemplate("OlegUserdirectoryBundle::Default/usermacros.html.twig", "OlegUserdirectoryBundle::Default/userformmacros.html.twig", 524);
            // line 525
            echo "    ";
            $context["treemacros"] = $this->loadTemplate("OlegUserdirectoryBundle::Tree/treemacros.html.twig", "OlegUserdirectoryBundle::Default/userformmacros.html.twig", 525);
            // line 526
            echo "
    ";
            // line 527
            if (((isset($context["prototype"]) ? $context["prototype"] : null) == "prototype")) {
                // line 528
                echo "        ";
                $context["formfield"] = $this->getAttribute($this->getAttribute((isset($context["field"]) ? $context["field"] : null), "vars", array()), "prototype", array());
                // line 529
                echo "    ";
            } else {
                // line 530
                echo "        ";
                $context["formfield"] = (isset($context["field"]) ? $context["field"] : null);
                // line 531
                echo "    ";
            }
            // line 532
            echo "
    ";
            // line 533
            if (twig_in_filter("show", (isset($context["cycle"]) ? $context["cycle"] : null))) {
                // line 534
                echo "        ";
                $context["showFlag"] = true;
                // line 535
                echo "    ";
            } else {
                // line 536
                echo "        ";
                $context["showFlag"] = false;
                // line 537
                echo "    ";
            }
            // line 538
            echo "
    ";
            // line 539
            if ((($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "comment", array()), "vars", array()), "value", array()) != "") ||  !(isset($context["showFlag"]) ? $context["showFlag"] : null))) {
                // line 540
                echo "
        ";
                // line 541
                if ((((isset($context["classname"]) ? $context["classname"] : null) == "user-privatecomments") && (($this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "vars", array()), "value", array()) && ($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "vars", array()), "value", array()), "status", array()) == 0)) || ((isset($context["prototype"]) ? $context["prototype"] : null) == "prototype")))) {
                    // line 542
                    echo "            ";
                    $context["wellclass"] = "user-alert-warning";
                    // line 543
                    echo "        ";
                } else {
                    // line 544
                    echo "            ";
                    $context["wellclass"] = "";
                    // line 545
                    echo "        ";
                }
                // line 546
                echo "
        ";
                // line 548
                echo "        <div class=\"user-collection-holder alert ";
                echo twig_escape_filter($this->env, (isset($context["classname"]) ? $context["classname"] : null), "html", null, true);
                echo " ";
                echo twig_escape_filter($this->env, (isset($context["wellclass"]) ? $context["wellclass"] : null), "html", null, true);
                echo "\">

            ";
                // line 550
                if (((isset($context["cycle"]) ? $context["cycle"] : null) != "show_user")) {
                    // line 551
                    echo "                <div class=\"text-right\" style=\"padding-bottom: 10px\">
                    <button type=\"button\" class=\"btn btn-default btn-sm\" onClick=\"removeExistingObject(this,'";
                    // line 552
                    echo twig_escape_filter($this->env, (isset($context["classname"]) ? $context["classname"] : null), "html", null, true);
                    echo "')\" ><span class=\"glyphicon glyphicon-remove\"></span></button>
                </div>
            ";
                }
                // line 555
                echo "
            ";
                // line 556
                echo $context["formmacros"]->getfield($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "id", array()));
                echo "

            ";
                // line 558
                if ($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "status", array(), "any", true, true)) {
                    // line 559
                    echo "                ";
                    echo $context["usermacros"]->getstatusVerifiedField((isset($context["formfield"]) ? $context["formfield"] : null), (isset($context["cycle"]) ? $context["cycle"] : null));
                    echo "
            ";
                }
                // line 561
                echo "
            ";
                // line 562
                if ($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "commentType", array(), "any", true, true)) {
                    // line 563
                    echo "                ";
                    echo $context["treemacros"]->getcompositeTreeNode($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "commentType", array()), (isset($context["cycle"]) ? $context["cycle"] : null), (isset($context["prototype"]) ? $context["prototype"] : null));
                    echo "
            ";
                }
                // line 565
                echo "
            ";
                // line 566
                echo $context["formmacros"]->getfield($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "comment", array()));
                echo "

            ";
                // line 568
                if (($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "documents", array(), "any", true, true) || ((isset($context["prototype"]) ? $context["prototype"] : null) == "prototype"))) {
                    // line 569
                    echo "                ";
                    echo $context["usermacros"]->getfileuploadLabelField((isset($context["formfield"]) ? $context["formfield"] : null), $this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "documents", array()), (isset($context["cycle"]) ? $context["cycle"] : null), (isset($context["classname"]) ? $context["classname"] : null), (isset($context["prototype"]) ? $context["prototype"] : null));
                    echo "
            ";
                }
                // line 571
                echo "
            ";
                // line 572
                if ((((isset($context["showFlag"]) ? $context["showFlag"] : null) && $this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "vars", array()), "value", array())) && $this->getAttribute($this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "vars", array()), "value", array()), "updateAuthor", array()))) {
                    // line 573
                    echo "                ";
                    $context["authorHref"] = $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath(((isset($context["employees_sitename"]) ? $context["employees_sitename"] : null) . "_showuser"), array("id" => $this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "vars", array()), "value", array()), "updateAuthor", array()), "getId", array(), "method")));
                    // line 574
                    echo "                ";
                    $context["authorAndDate"] = ((((((("Updated by <a href=\"" .                     // line 575
(isset($context["authorHref"]) ? $context["authorHref"] : null)) . "\">") . $this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute(                    // line 576
(isset($context["formfield"]) ? $context["formfield"] : null), "vars", array()), "value", array()), "updateAuthor", array()), "getUsernameOptimal", array(), "method")) . "</a> on ") . twig_date_format_filter($this->env, $this->getAttribute($this->getAttribute($this->getAttribute(                    // line 577
(isset($context["formfield"]) ? $context["formfield"] : null), "vars", array()), "value", array()), "updatedate", array()), "Y-m-d")) . " at ") . twig_date_format_filter($this->env, $this->getAttribute($this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "vars", array()), "value", array()), "updatedate", array()), "h:i a"));
                    // line 579
                    echo "                ";
                    echo $context["formmacros"]->getsimplefield("", (isset($context["authorAndDate"]) ? $context["authorAndDate"] : null), "simple-field", "disabled");
                    echo "
            ";
                }
                // line 581
                echo "
        </div>

    ";
            } else {
                // line 585
                echo "
        ";
                // line 586
                $this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "commentType", array()), "setRendered", array());
                // line 587
                echo "        ";
                // line 588
                echo "        ";
                $this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "comment", array()), "setRendered", array());
                // line 589
                echo "
        ";
                // line 590
                if ($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "status", array(), "any", true, true)) {
                    // line 591
                    echo "            ";
                    $this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "status", array()), "setRendered", array());
                    // line 592
                    echo "        ";
                }
                // line 593
                echo "
    ";
            }
            // line 595
            echo "
";
        } catch (Exception $e) {
            ob_end_clean();

            throw $e;
        } catch (Throwable $e) {
            ob_end_clean();

            throw $e;
        }

        return ('' === $tmp = ob_get_clean()) ? '' : new Twig_Markup($tmp, $this->env->getCharset());
    }

    // line 599
    public function getsnapshot_orig($__user__ = null, $__sitename__ = null, $__cycle__ = null, ...$__varargs__)
    {
        $context = $this->env->mergeGlobals(array(
            "user" => $__user__,
            "sitename" => $__sitename__,
            "cycle" => $__cycle__,
            "varargs" => $__varargs__,
        ));

        $blocks = array();

        ob_start();
        try {
            // line 600
            echo "
    ";
            // line 601
            $context["usermacros"] = $this->loadTemplate("OlegUserdirectoryBundle::Default/usermacros.html.twig", "OlegUserdirectoryBundle::Default/userformmacros.html.twig", 601);
            // line 602
            echo "
    ";
            // line 604
            echo "    ";
            $context["assistExist"] = false;
            // line 605
            echo "    ";
            if ($this->getAttribute((isset($context["user"]) ? $context["user"] : null), "locations", array(), "any", true, true)) {
                // line 606
                echo "        ";
                $context['_parent'] = $context;
                $context['_seq'] = twig_ensure_traversable($this->getAttribute((isset($context["user"]) ? $context["user"] : null), "locations", array()));
                foreach ($context['_seq'] as $context["_key"] => $context["location"]) {
                    // line 607
                    echo "            ";
                    if ((twig_length_filter($this->env, $this->getAttribute($context["location"], "assistant", array())) > 0)) {
                        // line 608
                        echo "                ";
                        $context["assistExist"] = true;
                        // line 609
                        echo "            ";
                    }
                    // line 610
                    echo "        ";
                }
                $_parent = $context['_parent'];
                unset($context['_seq'], $context['_iterated'], $context['_key'], $context['location'], $context['_parent'], $context['loop']);
                $context = array_intersect_key($context, $_parent) + $_parent;
                // line 611
                echo "    ";
            }
            // line 612
            echo "
    ";
            // line 614
            echo "    ";
            $context["showAcademicTitle"] = false;
            // line 615
            echo "    ";
            if ((twig_length_filter($this->env, $this->getAttribute((isset($context["user"]) ? $context["user"] : null), "appointmentTitles", array())) > 0)) {
                // line 616
                echo "        ";
                $context['_parent'] = $context;
                $context['_seq'] = twig_ensure_traversable($this->getAttribute((isset($context["user"]) ? $context["user"] : null), "appointmentTitles", array()));
                foreach ($context['_seq'] as $context["_key"] => $context["appointmentTitles"]) {
                    // line 617
                    echo "            ";
                    if ($this->getAttribute($context["appointmentTitles"], "name", array())) {
                        // line 618
                        echo "                ";
                        $context["showAcademicTitle"] = true;
                        // line 619
                        echo "            ";
                    }
                    // line 620
                    echo "        ";
                }
                $_parent = $context['_parent'];
                unset($context['_seq'], $context['_iterated'], $context['_key'], $context['appointmentTitles'], $context['_parent'], $context['loop']);
                $context = array_intersect_key($context, $_parent) + $_parent;
                // line 621
                echo "
    ";
            }
            // line 623
            echo "
    ";
            // line 625
            echo "    ";
            $context["showIc"] = false;
            // line 626
            echo "    ";
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable($this->getAttribute((isset($context["user"]) ? $context["user"] : null), "locations", array()));
            foreach ($context['_seq'] as $context["_key"] => $context["location"]) {
                // line 627
                echo "        ";
                if ($this->getAttribute($context["location"], "ic", array())) {
                    // line 628
                    echo "            ";
                    $context["showIc"] = true;
                    // line 629
                    echo "        ";
                }
                // line 630
                echo "    ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['location'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 631
            echo "
    <table class=\"table table-condensed text-left\">
    <thead>
        <tr>
            <th>Service(s)</th>
            <th>Name</th>
            <th>Administrative Title(s)</th>

            ";
            // line 639
            if ((isset($context["showAcademicTitle"]) ? $context["showAcademicTitle"] : null)) {
                // line 640
                echo "                <th>Academic Title(s)</th>
            ";
            }
            // line 642
            echo "
            <th>Email</th>
            <th>Phone Number</th>

            ";
            // line 646
            if ((isset($context["showIc"]) ? $context["showIc"] : null)) {
                // line 647
                echo "                <th>IC</th>
            ";
            }
            // line 649
            echo "
            <th>Room Number(s)</th>

            ";
            // line 652
            if ((isset($context["assistExist"]) ? $context["assistExist"] : null)) {
                // line 653
                echo "                <th>Assistant(s)</th>
            ";
            }
            // line 655
            echo "        </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                ";
            // line 661
            echo "                    ";
            // line 662
            echo "                        ";
            // line 663
            echo "                    ";
            // line 664
            echo "                ";
            // line 665
            echo "            </td>
            <td>";
            // line 666
            echo twig_escape_filter($this->env, $this->getAttribute((isset($context["user"]) ? $context["user"] : null), "displayName", array()), "html", null, true);
            echo "</td>
            <td>
                ";
            // line 668
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable($this->getAttribute((isset($context["user"]) ? $context["user"] : null), "administrativeTitles", array()));
            foreach ($context['_seq'] as $context["_key"] => $context["administrativeTitle"]) {
                // line 669
                echo "                    ";
                if ($this->getAttribute($context["administrativeTitle"], "name", array())) {
                    // line 670
                    echo "                        <p>
                        ";
                    // line 671
                    if ($this->env->getExtension('Symfony\Bridge\Twig\Extension\SecurityExtension')->isGranted("ROLE_USERDIRECTORY_OBSERVER")) {
                        // line 672
                        echo "                            <a href=\"";
                        echo twig_escape_filter($this->env, $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath(((isset($context["employees_sitename"]) ? $context["employees_sitename"] : null) . "_search_same_object"), array("tablename" => "administrativeTitle", "id" => $this->getAttribute($this->getAttribute($context["administrativeTitle"], "name", array()), "id", array()), "name" => $this->getAttribute($this->getAttribute($context["administrativeTitle"], "name", array()), "name", array()))), "html", null, true);
                        echo "\">";
                        echo twig_escape_filter($this->env, $this->getAttribute($context["administrativeTitle"], "name", array()), "html", null, true);
                        echo "</a>
                        ";
                    } else {
                        // line 674
                        echo "                            ";
                        echo twig_escape_filter($this->env, $this->getAttribute($context["administrativeTitle"], "name", array()), "html", null, true);
                        echo "
                        ";
                    }
                    // line 676
                    echo "                        </p>
                    ";
                }
                // line 678
                echo "                ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['administrativeTitle'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 679
            echo "            </td>

            ";
            // line 682
            echo "            ";
            if ((isset($context["showAcademicTitle"]) ? $context["showAcademicTitle"] : null)) {
                // line 683
                echo "                <td>
                    ";
                // line 684
                $context['_parent'] = $context;
                $context['_seq'] = twig_ensure_traversable($this->getAttribute((isset($context["user"]) ? $context["user"] : null), "appointmentTitles", array()));
                foreach ($context['_seq'] as $context["_key"] => $context["appointmentTitles"]) {
                    // line 685
                    echo "                        ";
                    if ($this->getAttribute($context["appointmentTitles"], "name", array())) {
                        // line 686
                        echo "                            <p>
                            ";
                        // line 687
                        if ($this->env->getExtension('Symfony\Bridge\Twig\Extension\SecurityExtension')->isGranted("ROLE_USERDIRECTORY_OBSERVER")) {
                            // line 688
                            echo "                                <a href=\"";
                            echo twig_escape_filter($this->env, $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath(((isset($context["employees_sitename"]) ? $context["employees_sitename"] : null) . "_search_same_object"), array("tablename" => "appointmentTitle", "id" => $this->getAttribute($this->getAttribute($context["appointmentTitles"], "name", array()), "id", array()), "name" => $this->getAttribute($this->getAttribute($context["appointmentTitles"], "name", array()), "name", array()))), "html", null, true);
                            echo "\">";
                            echo twig_escape_filter($this->env, $this->getAttribute($context["appointmentTitles"], "name", array()), "html", null, true);
                            echo "</a>
                            ";
                        } else {
                            // line 690
                            echo "                                ";
                            echo twig_escape_filter($this->env, $this->getAttribute($context["appointmentTitles"], "name", array()), "html", null, true);
                            echo "
                            ";
                        }
                        // line 692
                        echo "                            </p>
                        ";
                    }
                    // line 694
                    echo "                    ";
                }
                $_parent = $context['_parent'];
                unset($context['_seq'], $context['_iterated'], $context['_key'], $context['appointmentTitles'], $context['_parent'], $context['loop']);
                $context = array_intersect_key($context, $_parent) + $_parent;
                // line 695
                echo "                </td>
            ";
            }
            // line 697
            echo "
            <td>
                ";
            // line 699
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable($this->getAttribute((isset($context["user"]) ? $context["user"] : null), "getAllEmail", array(), "method"));
            foreach ($context['_seq'] as $context["_key"] => $context["email"]) {
                // line 700
                echo "                    <p>
                        ";
                // line 701
                echo twig_escape_filter($this->env, $this->getAttribute($context["email"], "prefix", array(), "array"), "html", null, true);
                echo "<a href=\"mailto:";
                echo twig_escape_filter($this->env, $this->getAttribute($context["email"], "email", array(), "array"), "html", null, true);
                echo "\" target=\"_top\">";
                echo twig_escape_filter($this->env, $this->getAttribute($context["email"], "email", array(), "array"), "html", null, true);
                echo "</a>
                    </p>
                ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['email'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 704
            echo "            </td>
            <td>
                ";
            // line 706
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable($this->getAttribute((isset($context["user"]) ? $context["user"] : null), "getAllPhones", array(), "method"));
            foreach ($context['_seq'] as $context["_key"] => $context["phone"]) {
                // line 707
                echo "                    <p>";
                echo twig_escape_filter($this->env, $this->getAttribute($context["phone"], "prefix", array(), "array"), "html", null, true);
                echo $context["usermacros"]->getphoneHref($this->getAttribute($context["phone"], "phone", array(), "array"));
                echo "</p>
                ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['phone'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 709
            echo "            </td>


            ";
            // line 712
            if ((isset($context["showIc"]) ? $context["showIc"] : null)) {
                // line 713
                echo "                <td>
                    ";
                // line 714
                $context['_parent'] = $context;
                $context['_seq'] = twig_ensure_traversable($this->getAttribute((isset($context["user"]) ? $context["user"] : null), "locations", array()));
                foreach ($context['_seq'] as $context["_key"] => $context["location"]) {
                    // line 715
                    echo "                        ";
                    if ($this->getAttribute($context["location"], "ic", array())) {
                        // line 716
                        echo "                            ";
                        echo twig_escape_filter($this->env, $this->getAttribute($context["location"], "ic", array()), "html", null, true);
                        echo "<br>
                        ";
                    }
                    // line 718
                    echo "                    ";
                }
                $_parent = $context['_parent'];
                unset($context['_seq'], $context['_iterated'], $context['_key'], $context['location'], $context['_parent'], $context['loop']);
                $context = array_intersect_key($context, $_parent) + $_parent;
                // line 719
                echo "                </td>
            ";
            }
            // line 721
            echo "
            <td>
                ";
            // line 723
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable($this->getAttribute((isset($context["user"]) ? $context["user"] : null), "locations", array()));
            foreach ($context['_seq'] as $context["_key"] => $context["location"]) {
                // line 724
                echo "                    ";
                if ($this->getAttribute($context["location"], "room", array())) {
                    // line 725
                    echo "                        <p>
                        ";
                    // line 726
                    if ($this->env->getExtension('Symfony\Bridge\Twig\Extension\SecurityExtension')->isGranted("ROLE_USERDIRECTORY_OBSERVER")) {
                        // line 727
                        echo "                            <a href=\"";
                        echo twig_escape_filter($this->env, $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath(((isset($context["employees_sitename"]) ? $context["employees_sitename"] : null) . "_search_same_object"), array("tablename" => "room", "id" => $this->getAttribute($this->getAttribute($context["location"], "room", array()), "id", array()), "name" => $this->getAttribute($this->getAttribute($context["location"], "room", array()), "name", array()))), "html", null, true);
                        echo "\">";
                        echo twig_escape_filter($this->env, $this->getAttribute($context["location"], "room", array()), "html", null, true);
                        echo "</a>
                        ";
                    } else {
                        // line 729
                        echo "                            ";
                        echo twig_escape_filter($this->env, $this->getAttribute($context["location"], "name", array()), "html", null, true);
                        echo "
                        ";
                    }
                    // line 731
                    echo "                        </p>
                    ";
                }
                // line 733
                echo "                ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['location'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 734
            echo "            </td>

            ";
            // line 736
            if ((isset($context["assistExist"]) ? $context["assistExist"] : null)) {
                // line 737
                echo "                <td>
                    ";
                // line 738
                echo $context["usermacros"]->getshowAssistantes((isset($context["user"]) ? $context["user"] : null), (isset($context["sitename"]) ? $context["sitename"] : null));
                echo "
                </td>
            ";
            }
            // line 741
            echo "
        </tr>
    </tbody>
    </table>

";
        } catch (Exception $e) {
            ob_end_clean();

            throw $e;
        } catch (Throwable $e) {
            ob_end_clean();

            throw $e;
        }

        return ('' === $tmp = ob_get_clean()) ? '' : new Twig_Markup($tmp, $this->env->getCharset());
    }

    // line 749
    public function getsnapshot_steve($__user__ = null, $__sitename__ = null, $__cycle__ = null, ...$__varargs__)
    {
        $context = $this->env->mergeGlobals(array(
            "user" => $__user__,
            "sitename" => $__sitename__,
            "cycle" => $__cycle__,
            "varargs" => $__varargs__,
        ));

        $blocks = array();

        ob_start();
        try {
            // line 750
            echo "
    ";
            // line 751
            $context["usermacros"] = $this->loadTemplate("OlegUserdirectoryBundle::Default/usermacros.html.twig", "OlegUserdirectoryBundle::Default/userformmacros.html.twig", 751);
            // line 752
            echo "
    <div align=\"center\">

    <div class=\"snapshot-all\">

        ";
            // line 758
            echo "        <div class=\"snapshot\">

            <div class=\"left\">

                <div class=\"image-box\">
                    ";
            // line 764
            echo "                    ";
            // line 765
            echo "                    ";
            if ($this->getAttribute((isset($context["user"]) ? $context["user"] : null), "avatar", array())) {
                // line 766
                echo "                        ";
                // line 767
                echo "                        ";
                echo $context["usermacros"]->getshowDocumentAsImage($this->getAttribute((isset($context["user"]) ? $context["user"] : null), "avatar", array()), "Avatar", "");
                echo "
                    ";
            } else {
                // line 769
                echo "                        ";
                $context["avatarImage"] = $this->env->getExtension('Symfony\Bridge\Twig\Extension\AssetExtension')->getAssetUrl("bundles/oleguserdirectory/fengyuanchen-image-cropper/img/Placeholder-User-Glyph-Icon.png");
                // line 770
                echo "                        <img src=\"";
                echo twig_escape_filter($this->env, (isset($context["avatarImage"]) ? $context["avatarImage"] : null), "html", null, true);
                echo "\" alt=\"Avatar\" style=\"max-width:100%; max-height:100%;\">
                    ";
            }
            // line 772
            echo "                    ";
            // line 773
            echo "                </div>

                <!--<img src=\"profile-pic.jpg\"> -->

                <div class=\"left-text\">

                    ";
            // line 779
            $context["termStr"] = $this->getAttribute((isset($context["user"]) ? $context["user"] : null), "getEmploymentTerminatedStr", array());
            // line 780
            echo "                    ";
            if ((isset($context["termStr"]) ? $context["termStr"] : null)) {
                // line 781
                echo "                        ";
                $context["termStyle"] = "background-color: gray;";
                // line 782
                echo "                    ";
            } else {
                // line 783
                echo "                        ";
                $context["termStyle"] = "";
                // line 784
                echo "                    ";
            }
            // line 785
            echo "
                    <h2 style=\"";
            // line 786
            echo twig_escape_filter($this->env, (isset($context["termStyle"]) ? $context["termStyle"] : null), "html", null, true);
            echo "\">
                        ";
            // line 788
            echo "                        ";
            echo twig_escape_filter($this->env, $this->getAttribute((isset($context["user"]) ? $context["user"] : null), "getUsernameOptimal", array()), "html", null, true);
            echo "
                    </h2>

                    ";
            // line 792
            echo "                    ";
            // line 793
            echo "                    ";
            // line 794
            echo "                    ";
            $context["headInfos"] = $this->getAttribute((isset($context["user_security_utility"]) ? $context["user_security_utility"] : null), "getHeadInfo", array(0 => (isset($context["user"]) ? $context["user"] : null)), "method");
            // line 795
            echo "                    ";
            if ((twig_length_filter($this->env, (isset($context["headInfos"]) ? $context["headInfos"] : null)) > 0)) {
                // line 796
                echo "                        <h4>
                        ";
                // line 797
                $context['_parent'] = $context;
                $context['_seq'] = twig_ensure_traversable((isset($context["headInfos"]) ? $context["headInfos"] : null));
                foreach ($context['_seq'] as $context["_key"] => $context["headInfoArr"]) {
                    // line 798
                    echo "
                            ";
                    // line 799
                    if (($this->getAttribute($context["headInfoArr"], "old", array(), "any", true, true) && $this->getAttribute($context["headInfoArr"], "old", array()))) {
                        // line 800
                        echo "                                ";
                        // line 801
                        echo "                                ";
                        // line 802
                        echo "                                ";
                        $context['_parent'] = $context;
                        $context['_seq'] = twig_ensure_traversable($this->getAttribute($context["headInfoArr"], "titleInfo", array()));
                        foreach ($context['_seq'] as $context["_key"] => $context["titleInfo"]) {
                            if ($this->getAttribute($context["titleInfo"], "name", array(), "any", true, true)) {
                                // line 803
                                echo "                                    <a href=\"";
                                echo twig_escape_filter($this->env, $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath((                                // line 804
(isset($context["employees_sitename"]) ? $context["employees_sitename"] : null) . "_search_same_object"), array("tablename" => $this->getAttribute(                                // line 805
$context["titleInfo"], "tablename", array(), "array"), "id" => $this->getAttribute(                                // line 806
$context["titleInfo"], "id", array(), "array"), "name" => $this->getAttribute(                                // line 807
$context["titleInfo"], "name", array(), "array"))), "html", null, true);
                                // line 809
                                echo "\">";
                                echo $this->getAttribute($context["titleInfo"], "name", array(), "array");
                                echo "
                                    </a>
                                    <br>
                                ";
                            }
                        }
                        $_parent = $context['_parent'];
                        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['titleInfo'], $context['_parent'], $context['loop']);
                        $context = array_intersect_key($context, $_parent) + $_parent;
                        // line 813
                        echo "
                                ";
                        // line 815
                        echo "                                ";
                        $context['_parent'] = $context;
                        $context['_seq'] = twig_ensure_traversable($this->getAttribute($context["headInfoArr"], "instInfo", array()));
                        foreach ($context['_seq'] as $context["_key"] => $context["titleInfo"]) {
                            if ((twig_length_filter($this->env, $context["titleInfo"]) > 0)) {
                                // line 816
                                echo "                                    <a href=\"";
                                echo twig_escape_filter($this->env, $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath((                                // line 817
(isset($context["employees_sitename"]) ? $context["employees_sitename"] : null) . "_search_same_object"), array("tablename" => $this->getAttribute(                                // line 818
$context["titleInfo"], "tablename", array(), "array"), "id" => $this->getAttribute(                                // line 819
$context["titleInfo"], "id", array(), "array"), "name" => $this->getAttribute(                                // line 820
$context["titleInfo"], "name", array(), "array"))), "html", null, true);
                                // line 822
                                echo "\"><small>";
                                echo $this->getAttribute($context["titleInfo"], "name", array(), "array");
                                echo "</small>
                                    </a>
                                    <br>
                                ";
                            }
                        }
                        $_parent = $context['_parent'];
                        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['titleInfo'], $context['_parent'], $context['loop']);
                        $context = array_intersect_key($context, $_parent) + $_parent;
                        // line 826
                        echo "                                <br>

                            ";
                    } else {
                        // line 829
                        echo "                                ";
                        // line 830
                        echo "                                ";
                        if ($this->getAttribute($context["headInfoArr"], "titleInfo", array(), "any", true, true)) {
                            // line 831
                            echo "                                    ";
                            $context['_parent'] = $context;
                            $context['_seq'] = twig_ensure_traversable($this->getAttribute($context["headInfoArr"], "titleInfo", array()));
                            foreach ($context['_seq'] as $context["_key"] => $context["titleInfo"]) {
                                // line 832
                                echo "                                        ";
                                echo $context["titleInfo"];
                                echo "
                                        <br>
                                    ";
                            }
                            $_parent = $context['_parent'];
                            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['titleInfo'], $context['_parent'], $context['loop']);
                            $context = array_intersect_key($context, $_parent) + $_parent;
                            // line 835
                            echo "                                ";
                        }
                        // line 836
                        echo "
                                ";
                        // line 838
                        echo "                                ";
                        if ($this->getAttribute($context["headInfoArr"], "instInfo", array(), "any", true, true)) {
                            // line 839
                            echo "                                    ";
                            $context['_parent'] = $context;
                            $context['_seq'] = twig_ensure_traversable($this->getAttribute($context["headInfoArr"], "instInfo", array()));
                            foreach ($context['_seq'] as $context["_key"] => $context["instInfo"]) {
                                // line 840
                                echo "                                        ";
                                echo $context["instInfo"];
                                echo "
                                        <br>
                                    ";
                            }
                            $_parent = $context['_parent'];
                            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['instInfo'], $context['_parent'], $context['loop']);
                            $context = array_intersect_key($context, $_parent) + $_parent;
                            // line 843
                            echo "                                ";
                        }
                        // line 844
                        echo "                                <br>

                            ";
                    }
                    // line 847
                    echo "
                        ";
                }
                $_parent = $context['_parent'];
                unset($context['_seq'], $context['_iterated'], $context['_key'], $context['headInfoArr'], $context['_parent'], $context['loop']);
                $context = array_intersect_key($context, $_parent) + $_parent;
                // line 849
                echo "                        </h4>
                    ";
            }
            // line 851
            echo "                    ";
            // line 852
            echo "
                </div>
            </div>

            <div class=\"right\">
                <div class=\"right-text\">

                    ";
            // line 859
            if ((isset($context["termStr"]) ? $context["termStr"] : null)) {
                // line 860
                echo "                        <h4>";
                echo twig_escape_filter($this->env, (isset($context["termStr"]) ? $context["termStr"] : null), "html", null, true);
                echo "</h4>
                    ";
            }
            // line 862
            echo "
                    ";
            // line 864
            echo "                    ";
            if (($this->getAttribute((isset($context["user"]) ? $context["user"] : null), "getEmail", array()) || $this->getAttribute((isset($context["user"]) ? $context["user"] : null), "getPreferredPhone", array()))) {
                // line 865
                echo "
                        <div class=\"prefferedinfo\">
                            ";
                // line 867
                echo $this->getAttribute((isset($context["vacreq_util"]) ? $context["vacreq_util"] : null), "getUserAwayInfo", array(0 => (isset($context["user"]) ? $context["user"] : null)), "method");
                echo "
                            <h4>Preferred Contact Info:</h4>
                            <table>
                                ";
                // line 870
                if ($this->getAttribute((isset($context["user"]) ? $context["user"] : null), "getEmail", array())) {
                    // line 871
                    echo "                                    <tr class=\"row-withspace\">
                                        <td class=\"left-column\">email:</td>
                                        <td>
                                            ";
                    // line 875
                    echo "                                            <a href=\"mailto:";
                    echo twig_escape_filter($this->env, $this->getAttribute((isset($context["user"]) ? $context["user"] : null), "getEmail", array()), "html", null, true);
                    echo "\" target=\"_top\">";
                    echo twig_escape_filter($this->env, $this->getAttribute((isset($context["user"]) ? $context["user"] : null), "getEmail", array()), "html", null, true);
                    echo "</a>
                                        </td>
                                    </tr>
                                ";
                }
                // line 879
                echo "                                ";
                if ($this->getAttribute((isset($context["user"]) ? $context["user"] : null), "getPreferredPhone", array())) {
                    // line 880
                    echo "                                    <tr>
                                        <td class=\"left-column\">ph:</td>
                                        <td>
                                            ";
                    // line 884
                    echo "                                            ";
                    echo $context["usermacros"]->getphoneHref($this->getAttribute((isset($context["user"]) ? $context["user"] : null), "getPreferredPhone", array()));
                    echo "
                                        </td>
                                    </tr>
                                ";
                }
                // line 888
                echo "                            </table>
                        </div>

                    ";
            }
            // line 892
            echo "

                    ";
            // line 894
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable($this->getAttribute((isset($context["user"]) ? $context["user"] : null), "getLocations", array()));
            foreach ($context['_seq'] as $context["_key"] => $context["location"]) {
                // line 895
                echo "
                        ";
                // line 896
                if (($this->getAttribute($context["location"], "hasLocationTypeName", array(0 => "Employee Home"), "method") == false)) {
                    // line 897
                    echo "
                            <div class=\"contact\">
                                <h4>";
                    // line 899
                    echo twig_escape_filter($this->env, $this->getAttribute($context["location"], "getLocationTypesStr", array()), "html", null, true);
                    echo ":</h4>
                                <table>
                                    ";
                    // line 901
                    if ($this->getAttribute($context["location"], "room", array())) {
                        // line 902
                        echo "                                        <tr>
                                            <td class=\"left-column\">room:</td>
                                            <td>
                                                ";
                        // line 906
                        echo "                                                <a href=\"";
                        echo twig_escape_filter($this->env, $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath(((isset($context["employees_sitename"]) ? $context["employees_sitename"] : null) . "_search_same_object"), array("tablename" => "room", "id" => $this->getAttribute($this->getAttribute($context["location"], "room", array()), "id", array()), "name" => $this->getAttribute($this->getAttribute($context["location"], "room", array()), "name", array()))), "html", null, true);
                        echo "\">";
                        echo twig_escape_filter($this->env, $this->getAttribute($context["location"], "room", array()), "html", null, true);
                        echo "</a>
                                            </td>
                                        </tr>
                                    ";
                    }
                    // line 910
                    echo "
                                    ";
                    // line 911
                    if ($this->getAttribute($context["location"], "phone", array())) {
                        // line 912
                        echo "                                        <tr>
                                            <td class=\"left-column\">ph:</td>
                                            <td>
                                                ";
                        // line 916
                        echo "                                                ";
                        echo $context["usermacros"]->getphoneHref($this->getAttribute($context["location"], "phone", array()));
                        echo "
                                            </td>
                                        </tr>
                                    ";
                    }
                    // line 920
                    echo "
                                    ";
                    // line 921
                    if ($this->getAttribute($context["location"], "pager", array())) {
                        // line 922
                        echo "                                        <tr>
                                            <td class=\"left-column\">pager:</td>
                                            <td>";
                        // line 924
                        echo twig_escape_filter($this->env, $this->getAttribute($context["location"], "pager", array()), "html", null, true);
                        echo "</td>
                                        </tr>
                                    ";
                    }
                    // line 927
                    echo "
                                    ";
                    // line 928
                    if ($this->getAttribute($context["location"], "ic", array())) {
                        // line 929
                        echo "                                        <tr>
                                            <td class=\"left-column\">i/c:</td>
                                            <td>";
                        // line 931
                        echo twig_escape_filter($this->env, $this->getAttribute($context["location"], "ic", array()), "html", null, true);
                        echo "</td>
                                        </tr>
                                    ";
                    }
                    // line 934
                    echo "
                                    ";
                    // line 935
                    if ($this->getAttribute($context["location"], "fax", array())) {
                        // line 936
                        echo "                                        <tr>
                                            <td class=\"left-column\">fax:</td>
                                            <td>";
                        // line 938
                        echo twig_escape_filter($this->env, $this->getAttribute($context["location"], "fax", array()), "html", null, true);
                        echo "</td>
                                        </tr>
                                    ";
                    }
                    // line 941
                    echo "
                                    ";
                    // line 942
                    if ($this->getAttribute($context["location"], "email", array())) {
                        // line 943
                        echo "                                        <tr>
                                            <td class=\"left-column\">email:</td>
                                            <td>
                                                ";
                        // line 947
                        echo "                                                <a href=\"mailto:";
                        echo twig_escape_filter($this->env, $this->getAttribute($context["location"], "email", array()), "html", null, true);
                        echo "\" target=\"_top\">";
                        echo twig_escape_filter($this->env, $this->getAttribute($context["location"], "email", array()), "html", null, true);
                        echo "</a>
                                            </td>
                                        </tr>
                                    ";
                    }
                    // line 951
                    echo "
                                </table>
                            </div>


                            ";
                    // line 956
                    if ((twig_length_filter($this->env, $this->getAttribute($context["location"], "getAssistant", array())) > 0)) {
                        // line 957
                        echo "                                <div class=\"assistant\">
                                    <h4>Assistant:</h4>

                                    ";
                        // line 960
                        $context['_parent'] = $context;
                        $context['_seq'] = twig_ensure_traversable($this->getAttribute($context["location"], "getAssistant", array()));
                        foreach ($context['_seq'] as $context["_key"] => $context["assistant"]) {
                            // line 961
                            echo "
                                        <table style=\"padding-bottom: 10px;\">
                                            <tr>
                                                <td class=\"left-column\">name:</td>
                                                <td>
                                                    ";
                            // line 967
                            echo "                                                    <a href=\"";
                            echo twig_escape_filter($this->env, $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath(((isset($context["sitename"]) ? $context["sitename"] : null) . "_showuser"), array("id" => $this->getAttribute($context["assistant"], "id", array()))), "html", null, true);
                            echo "\">";
                            echo twig_escape_filter($this->env, $this->getAttribute($context["assistant"], "getUsernameOptimal", array(), "method"), "html", null, true);
                            echo "</a>
                                                </td>
                                            </tr>
                                            ";
                            // line 971
                            echo "                                            <tr>
                                                <td class=\"left-column\" valign=\"top\">ph:</td>
                                                <td>
                                                    ";
                            // line 974
                            $context['_parent'] = $context;
                            $context['_seq'] = twig_ensure_traversable($this->getAttribute($context["assistant"], "getAllPhones", array(), "method"));
                            foreach ($context['_seq'] as $context["_key"] => $context["phone"]) {
                                // line 975
                                echo "                                                        ";
                                echo twig_escape_filter($this->env, $this->getAttribute($context["phone"], "prefix", array(), "array"), "html", null, true);
                                echo $context["usermacros"]->getphoneHref($this->getAttribute($context["phone"], "phone", array(), "array"));
                                echo "<br>
                                                    ";
                            }
                            $_parent = $context['_parent'];
                            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['phone'], $context['_parent'], $context['loop']);
                            $context = array_intersect_key($context, $_parent) + $_parent;
                            // line 977
                            echo "                                                </td>
                                            </tr>

                                            <tr>
                                                <td class=\"left-column\" valign=\"top\">email:</td>
                                                <td>
                                                    ";
                            // line 984
                            echo "                                                    ";
                            // line 985
                            echo "                                                    ";
                            $context['_parent'] = $context;
                            $context['_seq'] = twig_ensure_traversable($this->getAttribute($context["assistant"], "getAllEmail", array(), "method"));
                            foreach ($context['_seq'] as $context["_key"] => $context["email"]) {
                                // line 986
                                echo "                                                        <a href=\"mailto:";
                                echo twig_escape_filter($this->env, $this->getAttribute($context["email"], "email", array(), "array"), "html", null, true);
                                echo "\" target=\"_top\">";
                                echo twig_escape_filter($this->env, $this->getAttribute($context["email"], "email", array(), "array"), "html", null, true);
                                echo "</a><br>
                                                    ";
                            }
                            $_parent = $context['_parent'];
                            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['email'], $context['_parent'], $context['loop']);
                            $context = array_intersect_key($context, $_parent) + $_parent;
                            // line 988
                            echo "                                                </td>
                                            </tr>

                                        </table>

                                    ";
                        }
                        $_parent = $context['_parent'];
                        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['assistant'], $context['_parent'], $context['loop']);
                        $context = array_intersect_key($context, $_parent) + $_parent;
                        // line 994
                        echo "
                                </div>
                            ";
                    }
                    // line 997
                    echo "
                        ";
                }
                // line 998
                echo " ";
                // line 999
                echo "
                    ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['location'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 1001
            echo "
                    ";
            // line 1003
            echo "                    ";
            if (($this->env->getExtension('Symfony\Bridge\Twig\Extension\SecurityExtension')->isGranted("ROLE_USERDIRECTORY_EDITOR") || $this->env->getExtension('Symfony\Bridge\Twig\Extension\SecurityExtension')->isGranted("ROLE_PLATFORM_DEPUTY_ADMIN"))) {
                // line 1004
                echo "                        ";
                $context["userEditor"] = true;
                // line 1005
                echo "                    ";
            } else {
                // line 1006
                echo "                        ";
                $context["userEditor"] = false;
                // line 1007
                echo "                    ";
            }
            // line 1008
            echo "                    ";
            if ((((isset($context["userEditor"]) ? $context["userEditor"] : null) == true) || ($this->getAttribute((isset($context["user"]) ? $context["user"] : null), "id", array()) == $this->getAttribute($this->getAttribute((isset($context["app"]) ? $context["app"] : null), "user", array()), "getId", array(), "method")))) {
                // line 1009
                echo "                        <p>

                        ";
                // line 1011
                if (((isset($context["userEditor"]) ? $context["userEditor"] : null) == true)) {
                    // line 1012
                    echo "                            <a data-toggle=\"tooltip\" title=\"Print Preview Internal Mailing Label\"
                               style=\"color:black;\" target=\"_blank\"
                               href=\"";
                    // line 1014
                    echo twig_escape_filter($this->env, $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("employees_user_label_preview", array("id" => $this->getAttribute((isset($context["user"]) ? $context["user"] : null), "id", array()))), "html", null, true);
                    echo "\"
                            >
                                <span class=\"glyphicon glyphicon-print\" aria-hidden=\"true\"></span>
                            </a>
                        ";
                }
                // line 1019
                echo "
                        <a data-toggle=\"tooltip\" title=\"Edit\" style=\"margin-left: 10px; color:black;\" href=\"";
                // line 1020
                echo twig_escape_filter($this->env, $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath(((isset($context["sitename"]) ? $context["sitename"] : null) . "_user_edit"), array("id" => $this->getAttribute((isset($context["user"]) ? $context["user"] : null), "id", array()))), "html", null, true);
                echo "\">
                            <span class=\"glyphicon glyphicon-edit\" aria-hidden=\"true\"></span>
                        </a>

                        ";
                // line 1024
                if ($this->env->getExtension('Symfony\Bridge\Twig\Extension\SecurityExtension')->isGranted("ROLE_PLATFORM_DEPUTY_ADMIN")) {
                    // line 1025
                    echo "                            <a data-toggle=\"tooltip\" title=\"Impersonate\" style=\"margin-left: 10px; color:black;\" href=\"";
                    echo twig_escape_filter($this->env, $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath(((isset($context["employees_sitename"]) ? $context["employees_sitename"] : null) . "_user_impersonate"), array("id" => $this->getAttribute((isset($context["user"]) ? $context["user"] : null), "id", array()))), "html", null, true);
                    echo "\">
                                <span class=\"glyphicon glyphicon-share\" aria-hidden=\"true\"></span>
                            </a>
                        ";
                }
                // line 1029
                echo "
                        ";
                // line 1030
                if (($this->getAttribute((isset($context["user"]) ? $context["user"] : null), "id", array()) != $this->getAttribute($this->getAttribute((isset($context["app"]) ? $context["app"] : null), "user", array()), "getId", array(), "method"))) {
                    // line 1031
                    echo "                            <a
                                general-data-confirm=\"Are you sure you want to mark user as no longer working here as of yesterday and prevent them from logging in?\"
                                data-toggle=\"tooltip\"
                                title=\"Mark user as no longer working here as of yesterday and prevent them from logging in\"
                                style=\"margin-left: 10px; color:black;\"
                                href=\"";
                    // line 1036
                    echo twig_escape_filter($this->env, $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath(((isset($context["employees_sitename"]) ? $context["employees_sitename"] : null) . "_user_employment_terminate"), array("id" => $this->getAttribute((isset($context["user"]) ? $context["user"] : null), "id", array()))), "html", null, true);
                    echo "\">
                                <span class=\"glyphicon glyphicon-remove\" aria-hidden=\"true\"></span>
                            </a>
                        ";
                }
                // line 1040
                echo "
                        </p>
                    ";
            }
            // line 1043
            echo "
                </div>";
            // line 1045
            echo "            </div> ";
            // line 1046
            echo "        </div>
    </div>

    </div>

";
        } catch (Exception $e) {
            ob_end_clean();

            throw $e;
        } catch (Throwable $e) {
            ob_end_clean();

            throw $e;
        }

        return ('' === $tmp = ob_get_clean()) ? '' : new Twig_Markup($tmp, $this->env->getCharset());
    }

    // line 1053
    public function getsnapshot($__user__ = null, $__sitename__ = null, $__cycle__ = null, ...$__varargs__)
    {
        $context = $this->env->mergeGlobals(array(
            "user" => $__user__,
            "sitename" => $__sitename__,
            "cycle" => $__cycle__,
            "varargs" => $__varargs__,
        ));

        $blocks = array();

        ob_start();
        try {
            // line 1054
            echo "
    ";
            // line 1055
            $context["usermacros"] = $this->loadTemplate("OlegUserdirectoryBundle::Default/usermacros.html.twig", "OlegUserdirectoryBundle::Default/userformmacros.html.twig", 1055);
            // line 1056
            echo "
    <h1>";
            // line 1057
            echo twig_escape_filter($this->env, $this->getAttribute((isset($context["user"]) ? $context["user"] : null), "getUsernameOptimal", array()), "html", null, true);
            echo "</h1>

    ";
            // line 1060
            echo "    <div align=\"center\" style=\"height: 160px;\">
        ";
            // line 1062
            echo "        ";
            if ($this->getAttribute((isset($context["user"]) ? $context["user"] : null), "avatar", array())) {
                // line 1063
                echo "            ";
                echo $context["usermacros"]->getshowDocumentAsImage($this->getAttribute((isset($context["user"]) ? $context["user"] : null), "avatar", array()), "Avatar", "");
                echo "
        ";
            } else {
                // line 1065
                echo "            ";
                $context["avatarImage"] = $this->env->getExtension('Symfony\Bridge\Twig\Extension\AssetExtension')->getAssetUrl("bundles/oleguserdirectory/fengyuanchen-image-cropper/img/Placeholder-User-Glyph-Icon.png");
                // line 1066
                echo "            <img src=\"";
                echo twig_escape_filter($this->env, (isset($context["avatarImage"]) ? $context["avatarImage"] : null), "html", null, true);
                echo "\" alt=\"Avatar\" style=\"max-width:100%; max-height:100%;\">
        ";
            }
            // line 1068
            echo "    </div>


";
        } catch (Exception $e) {
            ob_end_clean();

            throw $e;
        } catch (Throwable $e) {
            ob_end_clean();

            throw $e;
        }

        return ('' === $tmp = ob_get_clean()) ? '' : new Twig_Markup($tmp, $this->env->getCharset());
    }

    // line 1074
    public function getroleAttributes($__roleobjects__ = null, ...$__varargs__)
    {
        $context = $this->env->mergeGlobals(array(
            "roleobjects" => $__roleobjects__,
            "varargs" => $__varargs__,
        ));

        $blocks = array();

        ob_start();
        try {
            // line 1075
            echo "    ";
            $context["formmacros"] = $this->loadTemplate("OlegOrderformBundle::Default/formmacros.html.twig", "OlegUserdirectoryBundle::Default/userformmacros.html.twig", 1075);
            // line 1076
            echo "    ";
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable((isset($context["roleobjects"]) ? $context["roleobjects"] : null));
            foreach ($context['_seq'] as $context["_key"] => $context["role"]) {
                // line 1077
                echo "        ";
                $context['_parent'] = $context;
                $context['_seq'] = twig_ensure_traversable($this->getAttribute($context["role"], "getAttributes", array(), "method"));
                foreach ($context['_seq'] as $context["_key"] => $context["attribute"]) {
                    // line 1078
                    echo "            ";
                    echo $context["formmacros"]->getsimplefield(($this->getAttribute($context["attribute"], "getName", array(), "method") . ":"), $this->getAttribute($context["attribute"], "getValue", array(), "method"), "input", "disabled");
                    echo "
        ";
                }
                $_parent = $context['_parent'];
                unset($context['_seq'], $context['_iterated'], $context['_key'], $context['attribute'], $context['_parent'], $context['loop']);
                $context = array_intersect_key($context, $_parent) + $_parent;
                // line 1080
                echo "    ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['role'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
        } catch (Exception $e) {
            ob_end_clean();

            throw $e;
        } catch (Throwable $e) {
            ob_end_clean();

            throw $e;
        }

        return ('' === $tmp = ob_get_clean()) ? '' : new Twig_Markup($tmp, $this->env->getCharset());
    }

    // line 1086
    public function getpermissionSection($__permissions__ = null, $__cycle__ = null, $__sitename__ = null, $__collapsein__ = null, $__title__ = null, $__usedata__ = null, ...$__varargs__)
    {
        $context = $this->env->mergeGlobals(array(
            "permissions" => $__permissions__,
            "cycle" => $__cycle__,
            "sitename" => $__sitename__,
            "collapsein" => $__collapsein__,
            "title" => $__title__,
            "usedata" => $__usedata__,
            "varargs" => $__varargs__,
        ));

        $blocks = array();

        ob_start();
        try {
            // line 1087
            echo "
    ";
            // line 1088
            $context["userform"] = $this->loadTemplate("OlegUserdirectoryBundle::Default/userformmacros.html.twig", "OlegUserdirectoryBundle::Default/userformmacros.html.twig", 1088);
            // line 1089
            echo "    ";
            $context["usermacros"] = $this->loadTemplate("OlegUserdirectoryBundle::Default/usermacros.html.twig", "OlegUserdirectoryBundle::Default/userformmacros.html.twig", 1089);
            // line 1090
            echo "
    ";
            // line 1091
            if ((isset($context["usedata"]) ? $context["usedata"] : null)) {
                // line 1092
                echo "        <div id=\"form-prototype-data\"
             data-prototype-user-permissions = \"";
                // line 1093
                echo twig_escape_filter($this->env, $context["userform"]->getpermissions((isset($context["permissions"]) ? $context["permissions"] : null), (isset($context["cycle"]) ? $context["cycle"] : null), "user-permissions", "prototype", (isset($context["sitename"]) ? $context["sitename"] : null)));
                echo "\"
        ></div>
    ";
            }
            // line 1096
            echo "
    <div class=\"panel panel-primary\">
        <div class=\"panel-heading\">
            <h4 class=\"panel-title text-left\">
                <a data-toggle=\"collapse\" href=\"#permissions\">
                    ";
            // line 1101
            echo twig_escape_filter($this->env, (isset($context["title"]) ? $context["title"] : null), "html", null, true);
            echo "
                </a>
            </h4>
        </div>
        <div id=\"permissions\" class=\"panel-collapse collapse ";
            // line 1105
            echo twig_escape_filter($this->env, (isset($context["collapsein"]) ? $context["collapsein"] : null), "html", null, true);
            echo "\">
            <div class=\"panel-body\">

                <div class=\"user-permissions-holder\">
                    ";
            // line 1109
            if ((twig_length_filter($this->env, (isset($context["permissions"]) ? $context["permissions"] : null)) > 0)) {
                // line 1110
                echo "                        ";
                $context['_parent'] = $context;
                $context['_seq'] = twig_ensure_traversable((isset($context["permissions"]) ? $context["permissions"] : null));
                foreach ($context['_seq'] as $context["_key"] => $context["permission"]) {
                    // line 1111
                    echo "                            ";
                    echo $context["userform"]->getpermissions($context["permission"], (isset($context["cycle"]) ? $context["cycle"] : null), "user-permissions", "noprototype");
                    echo "
                        ";
                }
                $_parent = $context['_parent'];
                unset($context['_seq'], $context['_iterated'], $context['_key'], $context['permission'], $context['_parent'], $context['loop']);
                $context = array_intersect_key($context, $_parent) + $_parent;
                // line 1113
                echo "                    ";
            } else {
                // line 1114
                echo "                        No specific user permissions are defined for this user.<br>
                    ";
            }
            // line 1116
            echo "                    ";
            echo $context["usermacros"]->getaddNewObjectBtn((isset($context["cycle"]) ? $context["cycle"] : null), "user-permissions", "Add Permission");
            echo "
                </div>

            </div>
        </div>
    </div>
";
        } catch (Exception $e) {
            ob_end_clean();

            throw $e;
        } catch (Throwable $e) {
            ob_end_clean();

            throw $e;
        }

        return ('' === $tmp = ob_get_clean()) ? '' : new Twig_Markup($tmp, $this->env->getCharset());
    }

    // line 1124
    public function getpermissions($__field__ = null, $__cycle__ = null, $__classname__ = null, $__prototype__ = null, ...$__varargs__)
    {
        $context = $this->env->mergeGlobals(array(
            "field" => $__field__,
            "cycle" => $__cycle__,
            "classname" => $__classname__,
            "prototype" => $__prototype__,
            "varargs" => $__varargs__,
        ));

        $blocks = array();

        ob_start();
        try {
            // line 1125
            echo "    ";
            $context["formmacros"] = $this->loadTemplate("OlegOrderformBundle::Default/formmacros.html.twig", "OlegUserdirectoryBundle::Default/userformmacros.html.twig", 1125);
            // line 1126
            echo "
    ";
            // line 1127
            if (twig_in_filter("show", (isset($context["cycle"]) ? $context["cycle"] : null))) {
                // line 1128
                echo "        ";
                $context["showFlag"] = true;
                // line 1129
                echo "    ";
            } else {
                // line 1130
                echo "        ";
                $context["showFlag"] = false;
                // line 1131
                echo "    ";
            }
            // line 1132
            echo "
    ";
            // line 1133
            if (((isset($context["prototype"]) ? $context["prototype"] : null) == "prototype")) {
                // line 1134
                echo "        ";
                $context["formfield"] = $this->getAttribute($this->getAttribute((isset($context["field"]) ? $context["field"] : null), "vars", array()), "prototype", array());
                // line 1135
                echo "    ";
            } else {
                // line 1136
                echo "        ";
                $context["formfield"] = (isset($context["field"]) ? $context["field"] : null);
                // line 1137
                echo "    ";
            }
            // line 1138
            echo "
    <div class=\"well ";
            // line 1139
            echo twig_escape_filter($this->env, (isset($context["classname"]) ? $context["classname"] : null), "html", null, true);
            echo "\">

        ";
            // line 1141
            if ( !(isset($context["showFlag"]) ? $context["showFlag"] : null)) {
                // line 1142
                echo "            <div class=\"text-right\">
                <button type=\"button\" class=\"btn btn-default btn-sm\" onClick=\"removeExistingObject(this,'";
                // line 1143
                echo twig_escape_filter($this->env, (isset($context["classname"]) ? $context["classname"] : null), "html", null, true);
                echo "')\" ><span class=\"glyphicon glyphicon-remove\"></span></button>
            </div>
        ";
            }
            // line 1146
            echo "
        ";
            // line 1148
            echo "        ";
            echo $context["formmacros"]->getfield($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "id", array()));
            echo "
        ";
            // line 1149
            echo $context["formmacros"]->getfield($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "permission", array()));
            echo "
        ";
            // line 1150
            echo $context["formmacros"]->getfield($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "institutions", array()));
            echo "

    </div>
";
        } catch (Exception $e) {
            ob_end_clean();

            throw $e;
        } catch (Throwable $e) {
            ob_end_clean();

            throw $e;
        }

        return ('' === $tmp = ob_get_clean()) ? '' : new Twig_Markup($tmp, $this->env->getCharset());
    }

    public function getTemplateName()
    {
        return "OlegUserdirectoryBundle::Default/userformmacros.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  3072 => 1150,  3068 => 1149,  3063 => 1148,  3060 => 1146,  3054 => 1143,  3051 => 1142,  3049 => 1141,  3044 => 1139,  3041 => 1138,  3038 => 1137,  3035 => 1136,  3032 => 1135,  3029 => 1134,  3027 => 1133,  3024 => 1132,  3021 => 1131,  3018 => 1130,  3015 => 1129,  3012 => 1128,  3010 => 1127,  3007 => 1126,  3004 => 1125,  2989 => 1124,  2966 => 1116,  2962 => 1114,  2959 => 1113,  2950 => 1111,  2945 => 1110,  2943 => 1109,  2936 => 1105,  2929 => 1101,  2922 => 1096,  2916 => 1093,  2913 => 1092,  2911 => 1091,  2908 => 1090,  2905 => 1089,  2903 => 1088,  2900 => 1087,  2883 => 1086,  2864 => 1080,  2855 => 1078,  2850 => 1077,  2845 => 1076,  2842 => 1075,  2830 => 1074,  2812 => 1068,  2806 => 1066,  2803 => 1065,  2797 => 1063,  2794 => 1062,  2791 => 1060,  2786 => 1057,  2783 => 1056,  2781 => 1055,  2778 => 1054,  2764 => 1053,  2744 => 1046,  2742 => 1045,  2739 => 1043,  2734 => 1040,  2727 => 1036,  2720 => 1031,  2718 => 1030,  2715 => 1029,  2707 => 1025,  2705 => 1024,  2698 => 1020,  2695 => 1019,  2687 => 1014,  2683 => 1012,  2681 => 1011,  2677 => 1009,  2674 => 1008,  2671 => 1007,  2668 => 1006,  2665 => 1005,  2662 => 1004,  2659 => 1003,  2656 => 1001,  2649 => 999,  2647 => 998,  2643 => 997,  2638 => 994,  2627 => 988,  2616 => 986,  2611 => 985,  2609 => 984,  2601 => 977,  2591 => 975,  2587 => 974,  2582 => 971,  2573 => 967,  2566 => 961,  2562 => 960,  2557 => 957,  2555 => 956,  2548 => 951,  2538 => 947,  2533 => 943,  2531 => 942,  2528 => 941,  2522 => 938,  2518 => 936,  2516 => 935,  2513 => 934,  2507 => 931,  2503 => 929,  2501 => 928,  2498 => 927,  2492 => 924,  2488 => 922,  2486 => 921,  2483 => 920,  2475 => 916,  2470 => 912,  2468 => 911,  2465 => 910,  2455 => 906,  2450 => 902,  2448 => 901,  2443 => 899,  2439 => 897,  2437 => 896,  2434 => 895,  2430 => 894,  2426 => 892,  2420 => 888,  2412 => 884,  2407 => 880,  2404 => 879,  2394 => 875,  2389 => 871,  2387 => 870,  2381 => 867,  2377 => 865,  2374 => 864,  2371 => 862,  2365 => 860,  2363 => 859,  2354 => 852,  2352 => 851,  2348 => 849,  2341 => 847,  2336 => 844,  2333 => 843,  2323 => 840,  2318 => 839,  2315 => 838,  2312 => 836,  2309 => 835,  2299 => 832,  2294 => 831,  2291 => 830,  2289 => 829,  2284 => 826,  2272 => 822,  2270 => 820,  2269 => 819,  2268 => 818,  2267 => 817,  2265 => 816,  2259 => 815,  2256 => 813,  2244 => 809,  2242 => 807,  2241 => 806,  2240 => 805,  2239 => 804,  2237 => 803,  2231 => 802,  2229 => 801,  2227 => 800,  2225 => 799,  2222 => 798,  2218 => 797,  2215 => 796,  2212 => 795,  2209 => 794,  2207 => 793,  2205 => 792,  2198 => 788,  2194 => 786,  2191 => 785,  2188 => 784,  2185 => 783,  2182 => 782,  2179 => 781,  2176 => 780,  2174 => 779,  2166 => 773,  2164 => 772,  2158 => 770,  2155 => 769,  2149 => 767,  2147 => 766,  2144 => 765,  2142 => 764,  2135 => 758,  2128 => 752,  2126 => 751,  2123 => 750,  2109 => 749,  2089 => 741,  2083 => 738,  2080 => 737,  2078 => 736,  2074 => 734,  2068 => 733,  2064 => 731,  2058 => 729,  2050 => 727,  2048 => 726,  2045 => 725,  2042 => 724,  2038 => 723,  2034 => 721,  2030 => 719,  2024 => 718,  2018 => 716,  2015 => 715,  2011 => 714,  2008 => 713,  2006 => 712,  2001 => 709,  1991 => 707,  1987 => 706,  1983 => 704,  1970 => 701,  1967 => 700,  1963 => 699,  1959 => 697,  1955 => 695,  1949 => 694,  1945 => 692,  1939 => 690,  1931 => 688,  1929 => 687,  1926 => 686,  1923 => 685,  1919 => 684,  1916 => 683,  1913 => 682,  1909 => 679,  1903 => 678,  1899 => 676,  1893 => 674,  1885 => 672,  1883 => 671,  1880 => 670,  1877 => 669,  1873 => 668,  1868 => 666,  1865 => 665,  1863 => 664,  1861 => 663,  1859 => 662,  1857 => 661,  1850 => 655,  1846 => 653,  1844 => 652,  1839 => 649,  1835 => 647,  1833 => 646,  1827 => 642,  1823 => 640,  1821 => 639,  1811 => 631,  1805 => 630,  1802 => 629,  1799 => 628,  1796 => 627,  1791 => 626,  1788 => 625,  1785 => 623,  1781 => 621,  1775 => 620,  1772 => 619,  1769 => 618,  1766 => 617,  1761 => 616,  1758 => 615,  1755 => 614,  1752 => 612,  1749 => 611,  1743 => 610,  1740 => 609,  1737 => 608,  1734 => 607,  1729 => 606,  1726 => 605,  1723 => 604,  1720 => 602,  1718 => 601,  1715 => 600,  1701 => 599,  1685 => 595,  1681 => 593,  1678 => 592,  1675 => 591,  1673 => 590,  1670 => 589,  1667 => 588,  1665 => 587,  1663 => 586,  1660 => 585,  1654 => 581,  1648 => 579,  1646 => 577,  1645 => 576,  1644 => 575,  1642 => 574,  1639 => 573,  1637 => 572,  1634 => 571,  1628 => 569,  1626 => 568,  1621 => 566,  1618 => 565,  1612 => 563,  1610 => 562,  1607 => 561,  1601 => 559,  1599 => 558,  1594 => 556,  1591 => 555,  1585 => 552,  1582 => 551,  1580 => 550,  1572 => 548,  1569 => 546,  1566 => 545,  1563 => 544,  1560 => 543,  1557 => 542,  1555 => 541,  1552 => 540,  1550 => 539,  1547 => 538,  1544 => 537,  1541 => 536,  1538 => 535,  1535 => 534,  1533 => 533,  1530 => 532,  1527 => 531,  1524 => 530,  1521 => 529,  1518 => 528,  1516 => 527,  1513 => 526,  1510 => 525,  1507 => 524,  1504 => 523,  1489 => 522,  1473 => 489,  1470 => 488,  1468 => 487,  1465 => 486,  1460 => 483,  1454 => 481,  1452 => 480,  1449 => 479,  1443 => 477,  1437 => 475,  1435 => 474,  1432 => 473,  1426 => 471,  1422 => 469,  1418 => 467,  1412 => 465,  1406 => 463,  1403 => 462,  1400 => 460,  1394 => 457,  1391 => 456,  1388 => 455,  1385 => 454,  1382 => 453,  1379 => 452,  1377 => 451,  1374 => 450,  1371 => 449,  1368 => 448,  1365 => 447,  1362 => 446,  1360 => 445,  1357 => 444,  1355 => 443,  1352 => 442,  1350 => 441,  1347 => 440,  1343 => 438,  1337 => 436,  1331 => 434,  1329 => 433,  1325 => 432,  1322 => 431,  1319 => 430,  1317 => 429,  1314 => 428,  1311 => 427,  1309 => 426,  1306 => 425,  1303 => 424,  1301 => 423,  1299 => 422,  1297 => 421,  1294 => 420,  1288 => 418,  1282 => 416,  1280 => 415,  1275 => 413,  1272 => 412,  1266 => 410,  1264 => 409,  1261 => 408,  1253 => 403,  1249 => 401,  1247 => 400,  1239 => 398,  1237 => 397,  1234 => 396,  1231 => 395,  1228 => 394,  1225 => 393,  1222 => 392,  1219 => 391,  1216 => 390,  1213 => 389,  1211 => 388,  1208 => 387,  1205 => 386,  1202 => 385,  1199 => 384,  1196 => 383,  1194 => 382,  1191 => 381,  1188 => 380,  1185 => 379,  1182 => 378,  1179 => 377,  1177 => 376,  1174 => 375,  1171 => 374,  1168 => 373,  1165 => 372,  1162 => 371,  1160 => 370,  1157 => 369,  1154 => 368,  1151 => 367,  1149 => 366,  1146 => 365,  1143 => 364,  1140 => 363,  1124 => 362,  1106 => 353,  1102 => 351,  1096 => 349,  1093 => 348,  1091 => 347,  1088 => 346,  1079 => 344,  1076 => 343,  1073 => 342,  1071 => 341,  1066 => 340,  1064 => 339,  1061 => 338,  1058 => 337,  1055 => 336,  1052 => 335,  1050 => 334,  1047 => 333,  1045 => 332,  1041 => 330,  1035 => 328,  1032 => 327,  1027 => 324,  1023 => 323,  1020 => 322,  1014 => 320,  1012 => 319,  1007 => 317,  1003 => 316,  999 => 315,  995 => 314,  992 => 313,  989 => 312,  983 => 309,  979 => 307,  976 => 306,  974 => 305,  969 => 303,  966 => 302,  963 => 301,  960 => 300,  957 => 299,  954 => 298,  952 => 297,  949 => 296,  946 => 295,  943 => 294,  928 => 293,  909 => 288,  905 => 287,  901 => 286,  898 => 285,  892 => 282,  889 => 281,  887 => 280,  882 => 278,  879 => 277,  876 => 276,  873 => 275,  870 => 274,  867 => 273,  865 => 272,  862 => 271,  859 => 270,  844 => 269,  827 => 264,  823 => 262,  817 => 260,  814 => 259,  812 => 258,  809 => 257,  800 => 255,  797 => 254,  794 => 253,  789 => 252,  786 => 251,  784 => 250,  781 => 249,  779 => 248,  774 => 246,  770 => 245,  766 => 244,  762 => 243,  758 => 242,  755 => 241,  749 => 238,  746 => 237,  744 => 236,  739 => 234,  736 => 233,  733 => 232,  730 => 231,  727 => 230,  724 => 229,  722 => 228,  719 => 227,  716 => 226,  701 => 225,  684 => 220,  680 => 218,  674 => 216,  671 => 215,  669 => 214,  666 => 213,  657 => 211,  654 => 210,  651 => 209,  649 => 208,  644 => 207,  642 => 206,  639 => 205,  637 => 204,  635 => 203,  633 => 202,  631 => 201,  628 => 200,  626 => 199,  621 => 197,  617 => 196,  613 => 195,  609 => 194,  605 => 193,  601 => 192,  598 => 191,  592 => 188,  589 => 187,  587 => 186,  582 => 184,  579 => 183,  576 => 182,  573 => 181,  570 => 180,  567 => 179,  565 => 178,  562 => 177,  559 => 176,  544 => 175,  527 => 168,  521 => 166,  519 => 165,  516 => 164,  510 => 162,  508 => 161,  505 => 160,  502 => 159,  499 => 158,  496 => 157,  493 => 156,  487 => 154,  484 => 153,  479 => 150,  475 => 149,  469 => 145,  466 => 144,  463 => 143,  460 => 142,  457 => 141,  454 => 140,  448 => 136,  442 => 132,  440 => 131,  432 => 126,  428 => 125,  424 => 124,  420 => 122,  417 => 121,  415 => 120,  412 => 119,  409 => 117,  406 => 116,  400 => 115,  397 => 114,  394 => 113,  391 => 112,  386 => 111,  383 => 110,  381 => 109,  378 => 108,  371 => 104,  367 => 102,  365 => 101,  362 => 100,  355 => 96,  351 => 94,  348 => 93,  345 => 91,  339 => 89,  337 => 88,  334 => 87,  331 => 85,  328 => 84,  325 => 83,  322 => 82,  319 => 81,  310 => 79,  306 => 78,  301 => 75,  298 => 74,  292 => 72,  289 => 71,  286 => 70,  283 => 68,  277 => 66,  275 => 65,  270 => 63,  267 => 62,  261 => 60,  259 => 59,  256 => 58,  250 => 56,  248 => 55,  242 => 53,  238 => 51,  235 => 50,  229 => 47,  226 => 46,  224 => 45,  214 => 44,  211 => 43,  208 => 42,  205 => 41,  202 => 40,  199 => 39,  196 => 38,  193 => 37,  190 => 35,  187 => 34,  184 => 33,  181 => 32,  178 => 31,  176 => 30,  173 => 29,  170 => 28,  167 => 27,  164 => 26,  161 => 25,  158 => 24,  155 => 23,  152 => 22,  149 => 21,  133 => 20,  127 => 1154,  124 => 1123,  118 => 1082,  114 => 1072,  111 => 1052,  108 => 747,  104 => 597,  101 => 521,  98 => 518,  96 => 517,  94 => 516,  92 => 515,  90 => 514,  88 => 513,  86 => 512,  84 => 511,  81 => 509,  79 => 508,  77 => 507,  75 => 506,  73 => 505,  70 => 503,  67 => 501,  65 => 500,  63 => 499,  61 => 498,  59 => 497,  56 => 495,  54 => 494,  52 => 493,  49 => 491,  43 => 357,  40 => 292,  36 => 267,  32 => 223,  26 => 171,  22 => 18,  19 => 16,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "OlegUserdirectoryBundle::Default/userformmacros.html.twig", "C:\\Users\\ch3\\Documents\\MyDocs\\WCMC\\ORDER\\scanorder\\Scanorders2\\src\\Oleg\\UserdirectoryBundle/Resources/views/Default/userformmacros.html.twig");
    }
}
