<?php

/* OlegUserdirectoryBundle::Default/usermacros.html.twig */
class __TwigTemplate_8b33ccaceb5dde159c8b26eb8d78ab7b5960079e340f6a5e8ba9053e7323aad9 extends Twig_Template
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
        // line 56
        echo "

";
        // line 70
        echo "

";
        // line 173
        echo "
";
        // line 195
        echo "
";
        // line 291
        echo "
";
        // line 372
        echo "


";
        // line 406
        echo "
";
        // line 421
        echo "
";
        // line 440
        echo "
";
        // line 452
        echo "

";
        // line 750
        echo "

";
        // line 774
        echo "
";
        // line 818
        echo "

";
        // line 835
        echo "
";
        // line 851
        echo "
";
        // line 905
        echo "

";
        // line 911
        echo "
";
        // line 925
        echo "

";
        // line 980
        echo "
";
        // line 1016
        echo "

";
        // line 1094
        echo "

";
        // line 1196
        echo "
";
        // line 1251
        echo "

";
        // line 1309
        echo "
";
        // line 1353
        echo "
";
        // line 1392
        echo "



";
        // line 1406
        echo "

";
        // line 1775
        echo "

";
        // line 1802
        echo "

";
        // line 1844
        echo "

";
        // line 1879
        echo "

";
        // line 1970
        echo "

";
        // line 2005
        echo "



";
        // line 2118
        echo "

";
        // line 2139
        echo "

";
        // line 2144
        echo "

";
        // line 2251
        echo "
";
        // line 2254
        echo "    ";
        // line 2255
        echo "    ";
        // line 2256
        echo "
    ";
        // line 2258
        echo "        ";
        // line 2259
        echo "    ";
        // line 2260
        echo "        ";
        // line 2261
        echo "    ";
        // line 2262
        echo "        ";
        // line 2263
        echo "    ";
        // line 2264
        echo "        ";
        // line 2265
        echo "    ";
        // line 2266
        echo "        ";
        // line 2267
        echo "    ";
        // line 2268
        echo "        ";
        // line 2269
        echo "    ";
        // line 2270
        echo "        ";
        // line 2271
        echo "    ";
        // line 2272
        echo "
    ";
    }

    // line 17
    public function getnonLiveSiteRedirect(...$__varargs__)
    {
        $context = $this->env->mergeGlobals(array(
            "varargs" => $__varargs__,
        ));

        $blocks = array();

        ob_start();
        try {
            // line 18
            echo "    ";
            if (($this->getAttribute((isset($context["app"]) ? $context["app"] : null), "environment", array()) == "prod")) {
                // line 19
                echo "        ";
                $context["environmentServer"] = $this->getAttribute((isset($context["user_security_utility"]) ? $context["user_security_utility"] : null), "getSiteSettingParameter", array(0 => "environment"), "method");
                // line 20
                echo "        ";
                if (((isset($context["environmentServer"]) ? $context["environmentServer"] : null) != "live")) {
                    // line 21
                    echo "            ";
                    if (((isset($context["environmentServer"]) ? $context["environmentServer"] : null) != "dev")) {
                        // line 22
                        echo "                ";
                        // line 23
                        echo "                ";
                        if ((($this->getAttribute((isset($context["app"]) ? $context["app"] : null), "user", array()) && $this->env->getExtension('Symfony\Bridge\Twig\Extension\SecurityExtension')->isGranted("IS_AUTHENTICATED_FULLY")) &&  !($this->env->getExtension('Symfony\Bridge\Twig\Extension\SecurityExtension')->isGranted("ROLE_PLATFORM_DEPUTY_ADMIN") || $this->env->getExtension('Symfony\Bridge\Twig\Extension\SecurityExtension')->isGranted("ROLE_TESTER")))) {
                            // line 24
                            echo "                    ";
                            if (!twig_in_filter("test", $this->getAttribute($this->getAttribute((isset($context["app"]) ? $context["app"] : null), "user", array()), "primaryPublicUserId", array()))) {
                                // line 25
                                echo "                        <meta http-equiv=\"refresh\" content=\"0; url=";
                                echo twig_escape_filter($this->env, $this->getAttribute((isset($context["user_security_utility"]) ? $context["user_security_utility"] : null), "getSiteSettingParameter", array(0 => "liveSiteRootUrl"), "method"), "html", null, true);
                                echo "\" />
                    ";
                            }
                            // line 27
                            echo "                ";
                        }
                        // line 28
                        echo "            ";
                    }
                    // line 29
                    echo "        ";
                }
                // line 30
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

    // line 32
    public function getnonLiveSiteWarning(...$__varargs__)
    {
        $context = $this->env->mergeGlobals(array(
            "varargs" => $__varargs__,
        ));

        $blocks = array();

        ob_start();
        try {
            // line 33
            echo "    ";
            if (($this->getAttribute((isset($context["user_security_utility"]) ? $context["user_security_utility"] : null), "getSiteSettingParameter", array(0 => "maintenance"), "method") == true)) {
                // line 34
                echo "        <p>
        <div class=\"alert alert-danger text-center\">
        The site is in maintenance mode. You may not be able to log in. Please come back when the updates are complete.
        </div>
        </p>
    ";
            }
            // line 40
            echo "    ";
            if (($this->getAttribute((isset($context["user_security_utility"]) ? $context["user_security_utility"] : null), "getSiteSettingParameter", array(0 => "environment"), "method") != "live")) {
                // line 41
                echo "        <p>
        <div class=\"alert alert-danger text-center\">
            THIS IS A TEST SERVER. USE ONLY FOR TESTING.&nbsp;
            ";
                // line 44
                $context["liveSiteRootUrl"] = $this->getAttribute((isset($context["user_security_utility"]) ? $context["user_security_utility"] : null), "getSiteSettingParameter", array(0 => "liveSiteRootUrl"), "method");
                // line 45
                echo "            ";
                if ((isset($context["liveSiteRootUrl"]) ? $context["liveSiteRootUrl"] : null)) {
                    // line 46
                    echo "                LIVE SITE IS <a href=\"";
                    echo twig_escape_filter($this->env, $this->getAttribute((isset($context["user_security_utility"]) ? $context["user_security_utility"] : null), "getSiteSettingParameter", array(0 => "liveSiteRootUrl"), "method"), "html", null, true);
                    echo "\">HERE</a>.
            ";
                } else {
                    // line 48
                    echo "                ";
                    if ($this->env->getExtension('Symfony\Bridge\Twig\Extension\SecurityExtension')->isGranted("ROLE_PLATFORM_DEPUTY_ADMIN")) {
                        // line 49
                        echo "                    ADD LINK TO THE LIVE SITE <a href=\"";
                        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("employees_siteparameters");
                        echo "\">HERE</a> OR SET THE \"ENVIRONMENT\" TO \"live\".
                ";
                    }
                    // line 51
                    echo "            ";
                }
                // line 52
                echo "        </div>
        </p>
    ";
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

    // line 58
    public function getbrowserCheck(...$__varargs__)
    {
        $context = $this->env->mergeGlobals(array(
            "varargs" => $__varargs__,
        ));

        $blocks = array();

        ob_start();
        try {
            // line 59
            echo "    ";
            // line 60
            echo "    ";
            // line 61
            echo "    ";
            $context["browserCheck"] = $this->getAttribute($this->getAttribute((isset($context["app"]) ? $context["app"] : null), "session", array()), "get", array(0 => "browserWarningInfo"), "method");
            // line 62
            echo "    ";
            if ((isset($context["browserCheck"]) ? $context["browserCheck"] : null)) {
                // line 63
                echo "        <p>
        <div class=\"alert alert-danger\" role=\"alert\">
            ";
                // line 65
                echo (isset($context["browserCheck"]) ? $context["browserCheck"] : null);
                echo "
        </div>
        </p>
    ";
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

    // line 72
    public function getsiteSwitch(...$__varargs__)
    {
        $context = $this->env->mergeGlobals(array(
            "varargs" => $__varargs__,
        ));

        $blocks = array();

        ob_start();
        try {
            // line 73
            echo "
    ";
            // line 74
            $context["usermacros"] = $this;
            // line 75
            echo "
    <li id=\"nav-bar-siteswitch\" class=\"dropdown\">
        ";
            // line 78
            echo "        <a href=\"#\" class=\"dropdown-toggle\" data-toggle=\"dropdown\" style=\"padding-bottom:12px;\">
            <span class=\"glyphicon glyphicon-th\" style=\"font-size:18px;\"></span>
            <b class=\"caret\"></b>
        </a>
        <ul class=\"dropdown-menu\">
            ";
            // line 84
            echo "                ";
            // line 85
            echo "                    ";
            // line 86
            echo "                    ";
            // line 87
            echo "                        ";
            // line 88
            echo "                    ";
            // line 89
            echo "                    ";
            // line 90
            echo "                        ";
            // line 91
            echo "                    ";
            // line 92
            echo "                    ";
            // line 93
            echo "                ";
            // line 94
            echo "            ";
            // line 95
            echo "            ";
            // line 96
            echo "                ";
            // line 97
            echo "                    ";
            // line 98
            echo "                        ";
            // line 99
            echo "                            ";
            // line 100
            echo "                        ";
            // line 101
            echo "                        ";
            // line 102
            echo "                            ";
            // line 103
            echo "                        ";
            // line 104
            echo "                    ";
            // line 105
            echo "                ";
            // line 106
            echo "            ";
            // line 107
            echo "            ";
            // line 108
            echo "                ";
            // line 109
            echo "                    ";
            // line 110
            echo "                        ";
            // line 111
            echo "                            ";
            // line 112
            echo "                        ";
            // line 113
            echo "                        ";
            // line 114
            echo "                            ";
            // line 115
            echo "                        ";
            // line 116
            echo "                    ";
            // line 117
            echo "                ";
            // line 118
            echo "            ";
            // line 119
            echo "            ";
            // line 120
            echo "                ";
            // line 121
            echo "                    ";
            // line 122
            echo "                        ";
            // line 123
            echo "                            ";
            // line 124
            echo "                        ";
            // line 125
            echo "                        ";
            // line 126
            echo "                            ";
            // line 127
            echo "                        ";
            // line 128
            echo "                    ";
            // line 129
            echo "                ";
            // line 130
            echo "            ";
            // line 131
            echo "            ";
            // line 132
            echo "                ";
            // line 133
            echo "                    ";
            // line 134
            echo "                        ";
            // line 135
            echo "                            ";
            // line 136
            echo "                        ";
            // line 137
            echo "                        ";
            // line 138
            echo "                            ";
            // line 139
            echo "                        ";
            // line 140
            echo "                    ";
            // line 141
            echo "                ";
            // line 142
            echo "            ";
            // line 143
            echo "
            ";
            // line 144
            $context["iconScan"] = (("<img src=\"" . $this->env->getExtension('Symfony\Bridge\Twig\Extension\AssetExtension')->getAssetUrl("bundles/oleguserdirectory/form/img/favicon.ico")) . "\" alt=\"Glass Slide Scan Orders\" height=\"18\" width=\"18\">");
            // line 145
            echo "            ";
            echo $context["usermacros"]->getsiteSingleSwitchElement((isset($context["scan_sitename"]) ? $context["scan_sitename"] : null), (isset($context["iconScan"]) ? $context["iconScan"] : null), "Glass Slide Scan Orders");
            echo "

            ";
            // line 147
            $context["iconEmployees"] = (("<img src=\"" . $this->env->getExtension('Symfony\Bridge\Twig\Extension\AssetExtension')->getAssetUrl("bundles/oleguserdirectory/form/img/users-1-64x64.png")) . "\" alt=\"Employee Directory\" height=\"18\" width=\"18\">");
            // line 148
            echo "            ";
            echo $context["usermacros"]->getsiteSingleSwitchElement((isset($context["employees_sitename"]) ? $context["employees_sitename"] : null), (isset($context["iconEmployees"]) ? $context["iconEmployees"] : null), "Employee Directory");
            echo "

            ";
            // line 150
            echo $context["usermacros"]->getsiteSingleSwitchElement((isset($context["fellapp_sitename"]) ? $context["fellapp_sitename"] : null), "<span class=\"glyphicon glyphicon-file\"></span>", "Fellowship Applications");
            echo "
            ";
            // line 151
            echo $context["usermacros"]->getsiteSingleSwitchElement((isset($context["deidentifier_sitename"]) ? $context["deidentifier_sitename"] : null), "<span class=\"glyphicon glyphicon-retweet\"></span>", "Deidentifier");
            echo "
            ";
            // line 152
            echo $context["usermacros"]->getsiteSingleSwitchElement((isset($context["vacreq_sitename"]) ? $context["vacreq_sitename"] : null), "<span class=\"glyphicon glyphicon-plane\"></span>", "Vacation Request");
            echo "
            ";
            // line 153
            echo $context["usermacros"]->getsiteSingleSwitchElement((isset($context["calllog_sitename"]) ? $context["calllog_sitename"] : null), "<span class=\"glyphicon glyphicon-phone-alt\"></span>", "Call Log Book");
            echo "
            ";
            // line 154
            echo $context["usermacros"]->getsiteSingleSwitchElement((isset($context["translationalresearch_sitename"]) ? $context["translationalresearch_sitename"] : null), "<span class=\"glyphicon glyphicon-briefcase\"></span>", "HemePath Translational Research");
            echo "

        </ul>
    </li>
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

    // line 159
    public function getsiteSingleSwitchElement($__sitename__ = null, $__icon__ = null, $__name__ = null, ...$__varargs__)
    {
        $context = $this->env->mergeGlobals(array(
            "sitename" => $__sitename__,
            "icon" => $__icon__,
            "name" => $__name__,
            "varargs" => $__varargs__,
        ));

        $blocks = array();

        ob_start();
        try {
            // line 160
            echo "    <li>
        <a href=\"";
            // line 161
            echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath(((isset($context["sitename"]) ? $context["sitename"] : null) . "_home"));
            echo "\">
            <div class=\"row\" style=\"margin-right: 0px; margin-left: 0px;\">
                <div class=\"col-xs-1\" align='left' style=\"margin-right: 0px; margin-left: -15px;\">
                    ";
            // line 164
            echo (isset($context["icon"]) ? $context["icon"] : null);
            echo "
                </div>
                <div class=\"col-xs-10\" align='left'>
                    <span>";
            // line 167
            echo twig_escape_filter($this->env, (isset($context["name"]) ? $context["name"] : null), "html", null, true);
            echo "</span>
                </div>
            </div>
        </a>
    </li>
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
    public function getfileuploadLabelField($__container__ = null, $__documents__ = null, $__cycle__ = null, $__classname__ = null, $__prototype__ = null, ...$__varargs__)
    {
        $context = $this->env->mergeGlobals(array(
            "container" => $__container__,
            "documents" => $__documents__,
            "cycle" => $__cycle__,
            "classname" => $__classname__,
            "prototype" => $__prototype__,
            "varargs" => $__varargs__,
        ));

        $blocks = array();

        ob_start();
        try {
            // line 176
            echo "
    ";
            // line 177
            $context["usermacros"] = $this;
            // line 178
            echo "    ";
            $context["formmacros"] = $this->loadTemplate("OlegOrderformBundle::Default/formmacros.html.twig", "OlegUserdirectoryBundle::Default/usermacros.html.twig", 178);
            // line 179
            echo "
    <p>
    ";
            // line 182
            echo "    <div class=\"row files-upload-holder\">
        <div class=\"col-xs-6\" align=\"right\">
            <strong>Attached Document(s):</strong>
        </div>
        <div class=\"col-xs-6\" align=\"left\">

            ";
            // line 188
            echo $context["usermacros"]->getdocumentsContainer((isset($context["container"]) ? $context["container"] : null), (isset($context["documents"]) ? $context["documents"] : null), (isset($context["cycle"]) ? $context["cycle"] : null), (isset($context["prototype"]) ? $context["prototype"] : null), 20, "default", "Comment Document");
            echo "

        </div>
    </div>
    </p>

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

    // line 199
    public function getdocumentsContainer($__container__ = null, $__documents__ = null, $__cycle__ = null, $__prototype__ = null, $__documentspercontainer__ = null, $__dropzoneInit__ = null, $__documentType__ = null, ...$__varargs__)
    {
        $context = $this->env->mergeGlobals(array(
            "container" => $__container__,
            "documents" => $__documents__,
            "cycle" => $__cycle__,
            "prototype" => $__prototype__,
            "documentspercontainer" => $__documentspercontainer__,
            "dropzoneInit" => $__dropzoneInit__,
            "documentType" => $__documentType__,
            "varargs" => $__varargs__,
        ));

        $blocks = array();

        ob_start();
        try {
            // line 200
            echo "
    ";
            // line 201
            $context["usermacros"] = $this->loadTemplate("OlegUserdirectoryBundle::Default/usermacros.html.twig", "OlegUserdirectoryBundle::Default/usermacros.html.twig", 201);
            // line 202
            echo "
    ";
            // line 203
            if ((array_key_exists("documents", $context) || ((isset($context["prototype"]) ? $context["prototype"] : null) == "prototype"))) {
                // line 204
                echo "
        ";
                // line 205
                if (twig_in_filter("show", (isset($context["cycle"]) ? $context["cycle"] : null))) {
                    // line 206
                    echo "            ";
                    $context["showFlag"] = true;
                    // line 207
                    echo "        ";
                } else {
                    // line 208
                    echo "            ";
                    $context["showFlag"] = false;
                    // line 209
                    echo "        ";
                }
                // line 210
                echo "
        ";
                // line 211
                if ((( !array_key_exists("documentspercontainer", $context) || ((isset($context["documentspercontainer"]) ? $context["documentspercontainer"] : null) == "")) || ((isset($context["documentspercontainer"]) ? $context["documentspercontainer"] : null) == 0))) {
                    // line 212
                    echo "            ";
                    $context["dz_maxFiles"] = 6;
                    // line 213
                    echo "        ";
                } else {
                    // line 214
                    echo "            ";
                    $context["dz_maxFiles"] = (isset($context["documentspercontainer"]) ? $context["documentspercontainer"] : null);
                    // line 215
                    echo "        ";
                }
                // line 216
                echo "        ";
                // line 217
                echo "
        ";
                // line 218
                if ( !array_key_exists("documentType", $context)) {
                    // line 219
                    echo "            ";
                    $context["dz_documentType"] = "";
                    // line 220
                    echo "        ";
                } else {
                    // line 221
                    echo "            ";
                    $context["dz_documentType"] = (isset($context["documentType"]) ? $context["documentType"] : null);
                    // line 222
                    echo "        ";
                }
                // line 223
                echo "
        ";
                // line 225
                echo "        ";
                if ( !array_key_exists("dropzoneInit", $context)) {
                    // line 226
                    echo "            ";
                    // line 227
                    echo "            ";
                    $context["dropzoneInit"] = "default";
                    // line 228
                    echo "            ";
                    $context["addRemoveLink"] = "default";
                    // line 229
                    echo "        ";
                }
                // line 230
                echo "
        ";
                // line 231
                if (((isset($context["dropzoneInit"]) ? $context["dropzoneInit"] : null) == "inactive")) {
                    // line 232
                    echo "            ";
                    // line 233
                    echo "            ";
                    $context["dropzoneActiveClass"] = "file-upload-dropzone-inactive";
                    // line 234
                    echo "            ";
                    $context["addRemoveLink"] = false;
                    // line 235
                    echo "        ";
                } elseif (((isset($context["dropzoneInit"]) ? $context["dropzoneInit"] : null) == "active")) {
                    // line 236
                    echo "            ";
                    // line 237
                    echo "            ";
                    $context["dropzoneActiveClass"] = "file-upload-dropzone-active";
                    // line 238
                    echo "            ";
                    $context["addRemoveLink"] = true;
                    // line 239
                    echo "        ";
                } elseif (((isset($context["dropzoneInit"]) ? $context["dropzoneInit"] : null) == "default")) {
                    // line 240
                    echo "            ";
                    // line 241
                    echo "            ";
                    if ((isset($context["showFlag"]) ? $context["showFlag"] : null)) {
                        // line 242
                        echo "                ";
                        $context["dropzoneActiveClass"] = "file-upload-dropzone-inactive";
                        // line 243
                        echo "                ";
                        $context["addRemoveLink"] = false;
                        // line 244
                        echo "            ";
                    } else {
                        // line 245
                        echo "                ";
                        $context["dropzoneActiveClass"] = "file-upload-dropzone-active";
                        // line 246
                        echo "                ";
                        $context["addRemoveLink"] = true;
                        // line 247
                        echo "            ";
                    }
                    // line 248
                    echo "        ";
                } else {
                    // line 249
                    echo "            ";
                    // line 250
                    echo "            ";
                    if ((isset($context["showFlag"]) ? $context["showFlag"] : null)) {
                        // line 251
                        echo "                ";
                        $context["dropzoneActiveClass"] = "file-upload-dropzone-inactive";
                        // line 252
                        echo "                ";
                        $context["addRemoveLink"] = false;
                        // line 253
                        echo "            ";
                    } else {
                        // line 254
                        echo "                ";
                        $context["dropzoneActiveClass"] = "file-upload-dropzone-active";
                        // line 255
                        echo "                ";
                        $context["addRemoveLink"] = true;
                        // line 256
                        echo "            ";
                    }
                    // line 257
                    echo "        ";
                }
                // line 258
                echo "

        <div class=\"dropzone file-upload-dropzone ";
                // line 260
                echo twig_escape_filter($this->env, (isset($context["dropzoneActiveClass"]) ? $context["dropzoneActiveClass"] : null), "html", null, true);
                echo "\" style=\"min-height: 150px; margin-bottom: 5px;\">

            ";
                // line 262
                if ( !(isset($context["showFlag"]) ? $context["showFlag"] : null)) {
                    // line 263
                    echo "
                <input type=\"hidden\" id=\"documentcontainer-documentspercontainer\" value=\"";
                    // line 264
                    echo twig_escape_filter($this->env, (isset($context["dz_maxFiles"]) ? $context["dz_maxFiles"] : null), "html", null, true);
                    echo "\">
                <input type=\"hidden\" id=\"documentcontainer-document-type\" value=\"";
                    // line 265
                    echo twig_escape_filter($this->env, (isset($context["dz_documentType"]) ? $context["dz_documentType"] : null), "html", null, true);
                    echo "\">
                <div class=\"dz-message\" style=\"padding-bottom:5px;\"><span>Drag and drop files here to upload or click to select a file (Maximum ";
                    // line 266
                    echo twig_escape_filter($this->env, (isset($context["dz_maxFiles"]) ? $context["dz_maxFiles"] : null), "html", null, true);
                    echo " files, 10 MB each)</span></div>

            ";
                } else {
                    // line 269
                    echo "
                <div class=\"dz-message\" style=\"padding-bottom:5px;\"><span></span></div>

            ";
                }
                // line 273
                echo "
            ";
                // line 274
                echo $context["usermacros"]->getdocument((isset($context["documents"]) ? $context["documents"] : null), (isset($context["cycle"]) ? $context["cycle"] : null), (isset($context["prototype"]) ? $context["prototype"] : null), (isset($context["addRemoveLink"]) ? $context["addRemoveLink"] : null));
                echo "

            ";
                // line 277
                echo "            ";
                if ( !(null === (isset($context["container"]) ? $context["container"] : null))) {
                    // line 278
                    echo "                ";
                    if ( !(isset($context["showFlag"]) ? $context["showFlag"] : null)) {
                        // line 279
                        echo "                    ";
                        if ($this->getAttribute((isset($context["container"]) ? $context["container"] : null), "others", array(), "any", true, true)) {
                            // line 280
                            echo "                        ";
                            echo $this->env->getRuntime('Symfony\Bridge\Twig\Form\TwigRenderer')->searchAndRenderBlock($this->getAttribute((isset($context["container"]) ? $context["container"] : null), "others", array()), 'widget');
                            echo "
                    ";
                        }
                        // line 282
                        echo "                    ";
                        echo $this->env->getRuntime('Symfony\Bridge\Twig\Form\TwigRenderer')->searchAndRenderBlock((isset($context["container"]) ? $context["container"] : null), 'rest');
                        echo "
                ";
                    }
                    // line 284
                    echo "            ";
                }
                // line 285
                echo "
        </div>

    ";
            }
            // line 289
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

    // line 292
    public function getdocument($__documents__ = null, $__cycle__ = null, $__prototype__ = null, $__addRemoveLink__ = null, ...$__varargs__)
    {
        $context = $this->env->mergeGlobals(array(
            "documents" => $__documents__,
            "cycle" => $__cycle__,
            "prototype" => $__prototype__,
            "addRemoveLink" => $__addRemoveLink__,
            "varargs" => $__varargs__,
        ));

        $blocks = array();

        ob_start();
        try {
            // line 293
            echo "    ";
            $context["formmacros"] = $this->loadTemplate("OlegOrderformBundle::Default/formmacros.html.twig", "OlegUserdirectoryBundle::Default/usermacros.html.twig", 293);
            // line 294
            echo "    ";
            $context["usermacros"] = $this->loadTemplate("OlegUserdirectoryBundle::Default/usermacros.html.twig", "OlegUserdirectoryBundle::Default/usermacros.html.twig", 294);
            // line 295
            echo "
    ";
            // line 297
            echo "    ";
            $context["sitename"] = (isset($context["employees_sitename"]) ? $context["employees_sitename"] : null);
            echo " ";
            // line 298
            echo "    ";
            $context["currentPath"] = $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["app"]) ? $context["app"] : null), "request", array()), "attributes", array()), "get", array(0 => "_route"), "method"), $this->getAttribute($this->getAttribute($this->getAttribute((isset($context["app"]) ? $context["app"] : null), "request", array()), "attributes", array()), "get", array(0 => "_route_params"), "method"));
            // line 299
            echo "    ";
            if (twig_in_filter("/scan/", (isset($context["currentPath"]) ? $context["currentPath"] : null))) {
                // line 300
                echo "        ";
                $context["sitename"] = (isset($context["scan_sitename"]) ? $context["scan_sitename"] : null);
                // line 301
                echo "    ";
            }
            // line 302
            echo "    ";
            if (twig_in_filter("/directory/", (isset($context["currentPath"]) ? $context["currentPath"] : null))) {
                // line 303
                echo "        ";
                $context["sitename"] = (isset($context["employees_sitename"]) ? $context["employees_sitename"] : null);
                // line 304
                echo "    ";
            }
            // line 305
            echo "    ";
            if (twig_in_filter("/fellowship-applications/", (isset($context["currentPath"]) ? $context["currentPath"] : null))) {
                // line 306
                echo "        ";
                $context["sitename"] = (isset($context["fellapp_sitename"]) ? $context["fellapp_sitename"] : null);
                // line 307
                echo "    ";
            }
            // line 308
            echo "    ";
            if (twig_in_filter("/deidentifier/", (isset($context["currentPath"]) ? $context["currentPath"] : null))) {
                // line 309
                echo "        ";
                $context["sitename"] = (isset($context["deidentifier_sitename"]) ? $context["deidentifier_sitename"] : null);
                // line 310
                echo "    ";
            }
            // line 311
            echo "    ";
            if (twig_in_filter("/vacation-request/", (isset($context["currentPath"]) ? $context["currentPath"] : null))) {
                // line 312
                echo "        ";
                $context["sitename"] = (isset($context["vacreq_sitename"]) ? $context["vacreq_sitename"] : null);
                // line 313
                echo "    ";
            }
            // line 314
            echo "    ";
            if (twig_in_filter("/call-log-book/", (isset($context["currentPath"]) ? $context["currentPath"] : null))) {
                // line 315
                echo "        ";
                $context["sitename"] = (isset($context["calllog_sitename"]) ? $context["calllog_sitename"] : null);
                // line 316
                echo "    ";
            }
            // line 317
            echo "    ";
            if (twig_in_filter("/translational-research/", (isset($context["currentPath"]) ? $context["currentPath"] : null))) {
                // line 318
                echo "        ";
                $context["sitename"] = (isset($context["translationalresearch_sitename"]) ? $context["translationalresearch_sitename"] : null);
                // line 319
                echo "    ";
            }
            // line 320
            echo "

    ";
            // line 322
            if (twig_in_filter("show", (isset($context["cycle"]) ? $context["cycle"] : null))) {
                // line 323
                echo "        ";
                $context["showFlag"] = true;
                // line 324
                echo "    ";
            } else {
                // line 325
                echo "        ";
                $context["showFlag"] = false;
                // line 326
                echo "    ";
            }
            // line 327
            echo "
    ";
            // line 328
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable((isset($context["documents"]) ? $context["documents"] : null));
            foreach ($context['_seq'] as $context["_key"] => $context["document"]) {
                if ($this->getAttribute($this->getAttribute($context["document"], "vars", array()), "value", array())) {
                    // line 329
                    echo "
        ";
                    // line 331
                    echo "
        <div class=\"dz-preview dz-file-preview\" style=\"width:100%; height:220px; margin-left:1px; margin-right:0px;\">
            <div class=\"dz-details\">
                ";
                    // line 335
                    echo "                <div class=\"dz-size\" data-dz-size><strong>";
                    echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute($this->getAttribute($context["document"], "vars", array()), "value", array()), "getSizeStr", array(), "method"), "html", null, true);
                    echo "</strong></div>
                ";
                    // line 336
                    echo $context["usermacros"]->getshowDocumentAsImage($this->getAttribute($this->getAttribute($context["document"], "vars", array()), "value", array()), "Document", "data-dz-thumbnail");
                    echo "
            </div>
            <div class=\"dz-progress\"><span class=\"dz-upload\" data-dz-uploadprogress></span></div>
            <div class=\"dz-success-mark\"><span>✔</span></div>
            <div class=\"dz-error-mark\"><span>✘</span></div>
            <div class=\"dz-error-message\"><span data-dz-errormessage></span></div>


            ";
                    // line 345
                    echo "            <div class=\"file-upload-showlink\">
                <div style=\"overflow:hidden; white-space:nowrap;\">
                    ";
                    // line 347
                    if ($this->getAttribute($this->getAttribute($this->getAttribute($context["document"], "vars", array()), "value", array()), "id", array())) {
                        // line 348
                        echo "                        <a target=\"_blank\" href=\"";
                        echo twig_escape_filter($this->env, $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath(((isset($context["sitename"]) ? $context["sitename"] : null) . "_file_download"), array("id" => $this->getAttribute($this->getAttribute($this->getAttribute($context["document"], "vars", array()), "value", array()), "id", array()))), "html", null, true);
                        echo "\">";
                        echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute($this->getAttribute($context["document"], "vars", array()), "value", array()), "originalname", array()), "html", null, true);
                        echo " (";
                        echo twig_escape_filter($this->env, twig_date_format_filter($this->env, $this->getAttribute($this->getAttribute($this->getAttribute($context["document"], "vars", array()), "value", array()), "createdate", array()), "m/d/Y"), "html", null, true);
                        echo ")</a>
                    ";
                    }
                    // line 350
                    echo "                </div>
            </div>

            ";
                    // line 353
                    if (((isset($context["addRemoveLink"]) ? $context["addRemoveLink"] : null) || ((isset($context["addRemoveLink"]) ? $context["addRemoveLink"] : null) == "default"))) {
                        // line 354
                        echo "                <a data-dz-remove=\"\" href=\"javascript:void(0);\" class=\"dz-remove\" onclick=\"removeUploadedFile(this)\">Remove file</a>
            ";
                    }
                    // line 356
                    echo "
            ";
                    // line 357
                    echo $context["formmacros"]->getfield($this->getAttribute($context["document"], "id", array()));
                    echo "
            ";
                    // line 358
                    echo $context["formmacros"]->getfield($this->getAttribute($context["document"], "dummyprototypefield", array()));
                    echo "
        </div>

        ";
                    // line 361
                    $this->getAttribute($context["document"], "setRendered", array());
                    // line 362
                    echo "
    ";
                }
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['document'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 364
            echo "
    ";
            // line 366
            echo "    ";
            echo $context["formmacros"]->getfield($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["documents"]) ? $context["documents"] : null), "vars", array()), "prototype", array()), "dummyprototypefield", array()));
            echo "


    ";
            // line 369
            $this->getAttribute((isset($context["documents"]) ? $context["documents"] : null), "setRendered", array());
            // line 370
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

    // line 375
    public function getgetInstitutionalTree($__entity__ = null, $__linktype__ = null, ...$__varargs__)
    {
        $context = $this->env->mergeGlobals(array(
            "entity" => $__entity__,
            "linktype" => $__linktype__,
            "varargs" => $__varargs__,
        ));

        $blocks = array();

        ob_start();
        try {
            // line 376
            echo "
    ";
            // line 377
            $context["usermacros"] = $this;
            // line 378
            echo "
    ";
            // line 379
            if ((array_key_exists("linktype", $context) && ((isset($context["linktype"]) ? $context["linktype"] : null) == "userlink"))) {
                // line 380
                echo "    ";
            } else {
                // line 381
                echo "        ";
                $context["linktype"] = "nodelink";
                // line 382
                echo "    ";
            }
            // line 383
            echo "
    <ol class=\"breadcrumb\">

        ";
            // line 387
            echo "        ";
            if ($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["entity"]) ? $context["entity"] : null), "parent", array(), "any", false, true), "parent", array(), "any", false, true), "parent", array(), "any", true, true)) {
                // line 388
                echo "            <li>";
                echo $context["usermacros"]->gethrefLinkToNode($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["entity"]) ? $context["entity"] : null), "parent", array()), "parent", array()), "parent", array()), (isset($context["linktype"]) ? $context["linktype"] : null));
                echo "</li>
        ";
            }
            // line 390
            echo "        ";
            // line 391
            echo "        ";
            if ($this->getAttribute($this->getAttribute((isset($context["entity"]) ? $context["entity"] : null), "parent", array(), "any", false, true), "parent", array(), "any", true, true)) {
                // line 392
                echo "            <li>";
                echo $context["usermacros"]->gethrefLinkToNode($this->getAttribute($this->getAttribute((isset($context["entity"]) ? $context["entity"] : null), "parent", array()), "parent", array()), (isset($context["linktype"]) ? $context["linktype"] : null));
                echo "</li>
        ";
            }
            // line 394
            echo "        ";
            // line 395
            echo "        ";
            if ($this->getAttribute((isset($context["entity"]) ? $context["entity"] : null), "parent", array(), "any", true, true)) {
                // line 396
                echo "            <li>";
                echo $context["usermacros"]->gethrefLinkToNode($this->getAttribute((isset($context["entity"]) ? $context["entity"] : null), "parent", array()), (isset($context["linktype"]) ? $context["linktype"] : null));
                echo "</li>
        ";
            }
            // line 398
            echo "        ";
            // line 399
            echo "        ";
            if (array_key_exists("entity", $context)) {
                // line 400
                echo "            <li>";
                echo $context["usermacros"]->gethrefLinkToNode((isset($context["entity"]) ? $context["entity"] : null), (isset($context["linktype"]) ? $context["linktype"] : null));
                echo "</li>
        ";
            }
            // line 402
            echo "
    </ol>

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

    // line 407
    public function gethrefLinkToNode($__node__ = null, $__linktype__ = null, ...$__varargs__)
    {
        $context = $this->env->mergeGlobals(array(
            "node" => $__node__,
            "linktype" => $__linktype__,
            "varargs" => $__varargs__,
        ));

        $blocks = array();

        ob_start();
        try {
            // line 408
            echo "    ";
            if (((isset($context["node"]) ? $context["node"] : null) && array_key_exists("node", $context))) {
                // line 409
                echo "        ";
                if ($this->env->getExtension('Symfony\Bridge\Twig\Extension\SecurityExtension')->isGranted("ROLE_USERDIRECTORY_OBSERVER")) {
                    // line 410
                    echo "            ";
                    if (((isset($context["linktype"]) ? $context["linktype"] : null) == "nodelink")) {
                        // line 411
                        echo "                <a href=\"";
                        echo twig_escape_filter($this->env, $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath((twig_lower_filter($this->env, $this->getAttribute((isset($context["node"]) ? $context["node"] : null), "getClassName", array(), "method")) . "s_show"), array("id" => $this->getAttribute((isset($context["node"]) ? $context["node"] : null), "id", array()))), "html", null, true);
                        echo "\">";
                        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["node"]) ? $context["node"] : null), "getOptimalName", array(), "method"), "html", null, true);
                        echo "</a>
            ";
                    }
                    // line 413
                    echo "            ";
                    if (((isset($context["linktype"]) ? $context["linktype"] : null) == "userlink")) {
                        // line 414
                        echo "                <a href=\"";
                        echo twig_escape_filter($this->env, $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath(((isset($context["employees_sitename"]) ? $context["employees_sitename"] : null) . "_search_same_object"), array("tablename" => twig_lower_filter($this->env, $this->getAttribute((isset($context["node"]) ? $context["node"] : null), "getClassName", array(), "method")), "id" => $this->getAttribute((isset($context["node"]) ? $context["node"] : null), "id", array()), "name" => $this->getAttribute((isset($context["node"]) ? $context["node"] : null), "getOptimalName", array(), "method"))), "html", null, true);
                        echo "\">";
                        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["node"]) ? $context["node"] : null), "getOptimalName", array(), "method"), "html", null, true);
                        echo "</a>
            ";
                    }
                    // line 416
                    echo "        ";
                } else {
                    // line 417
                    echo "            ";
                    echo twig_escape_filter($this->env, $this->getAttribute((isset($context["node"]) ? $context["node"] : null), "getOptimalName", array(), "method"), "html", null, true);
                    echo "
        ";
                }
                // line 419
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

    // line 422
    public function getgetNestedTree($__entity__ = null, $__linktype__ = null, ...$__varargs__)
    {
        $context = $this->env->mergeGlobals(array(
            "entity" => $__entity__,
            "linktype" => $__linktype__,
            "varargs" => $__varargs__,
        ));

        $blocks = array();

        ob_start();
        try {
            // line 423
            echo "
    ";
            // line 424
            if ((isset($context["entity"]) ? $context["entity"] : null)) {
                // line 425
                echo "
        ";
                // line 426
                $context["usermacros"] = $this;
                // line 427
                echo "
        ";
                // line 428
                if ((array_key_exists("linktype", $context) && ((isset($context["linktype"]) ? $context["linktype"] : null) == "userlink"))) {
                    // line 429
                    echo "        ";
                } else {
                    // line 430
                    echo "            ";
                    $context["linktype"] = "nodelink";
                    // line 431
                    echo "        ";
                }
                // line 432
                echo "
        <ol class=\"breadcrumb\">
            ";
                // line 434
                echo $context["usermacros"]->getnestedTree((isset($context["entity"]) ? $context["entity"] : null), (isset($context["linktype"]) ? $context["linktype"] : null));
                echo "
        </ol>

    ";
            }
            // line 438
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

    // line 441
    public function getnestedTree($__entity__ = null, $__linktype__ = null, ...$__varargs__)
    {
        $context = $this->env->mergeGlobals(array(
            "entity" => $__entity__,
            "linktype" => $__linktype__,
            "varargs" => $__varargs__,
        ));

        $blocks = array();

        ob_start();
        try {
            // line 442
            echo "
    ";
            // line 443
            $context["usermacros"] = $this;
            // line 444
            echo "
    ";
            // line 445
            if ((((isset($context["entity"]) ? $context["entity"] : null) && $this->getAttribute((isset($context["entity"]) ? $context["entity"] : null), "parent", array())) && ($this->getAttribute((isset($context["entity"]) ? $context["entity"] : null), "id", array()) != $this->getAttribute($this->getAttribute((isset($context["entity"]) ? $context["entity"] : null), "parent", array()), "id", array())))) {
                // line 446
                echo "        ";
                echo $context["usermacros"]->getnestedTree($this->getAttribute((isset($context["entity"]) ? $context["entity"] : null), "parent", array()), (isset($context["linktype"]) ? $context["linktype"] : null));
                echo "
    ";
            }
            // line 448
            echo "
    <li>";
            // line 449
            echo $context["usermacros"]->gethrefLinkToNode((isset($context["entity"]) ? $context["entity"] : null), (isset($context["linktype"]) ? $context["linktype"] : null));
            echo "</li><br>

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

    // line 454
    public function getlocationObject($__field__ = null, $__cycle__ = null, $__classname__ = null, $__prototype__ = null, $__sitename__ = null, $__entity__ = null, $__editable__ = null, ...$__varargs__)
    {
        $context = $this->env->mergeGlobals(array(
            "field" => $__field__,
            "cycle" => $__cycle__,
            "classname" => $__classname__,
            "prototype" => $__prototype__,
            "sitename" => $__sitename__,
            "entity" => $__entity__,
            "editable" => $__editable__,
            "varargs" => $__varargs__,
        ));

        $blocks = array();

        ob_start();
        try {
            // line 455
            echo "    
";
            // line 456
            if ((isset($context["field"]) ? $context["field"] : null)) {
                // line 457
                echo "    
    ";
                // line 458
                $context["formmacros"] = $this->loadTemplate("OlegOrderformBundle::Default/formmacros.html.twig", "OlegUserdirectoryBundle::Default/usermacros.html.twig", 458);
                // line 459
                echo "    ";
                $context["treemacros"] = $this->loadTemplate("OlegUserdirectoryBundle::Tree/treemacros.html.twig", "OlegUserdirectoryBundle::Default/usermacros.html.twig", 459);
                // line 460
                echo "    ";
                $context["usermacros"] = $this;
                // line 461
                echo "
    ";
                // line 462
                if (((isset($context["prototype"]) ? $context["prototype"] : null) == "prototype")) {
                    // line 463
                    echo "        ";
                    $context["formfield"] = $this->getAttribute($this->getAttribute((isset($context["field"]) ? $context["field"] : null), "vars", array()), "prototype", array());
                    // line 464
                    echo "    ";
                } else {
                    // line 465
                    echo "        ";
                    $context["formfield"] = (isset($context["field"]) ? $context["field"] : null);
                    // line 466
                    echo "    ";
                }
                // line 467
                echo "

    ";
                // line 469
                if ((((isset($context["prototype"]) ? $context["prototype"] : null) == "prototype") || ($this->getAttribute($this->getAttribute(                // line 470
(isset($context["formfield"]) ? $context["formfield"] : null), "vars", array()), "value", array()) && (($this->getAttribute($this->getAttribute($this->getAttribute(                // line 472
(isset($context["formfield"]) ? $context["formfield"] : null), "vars", array()), "value", array()), "removable", array()) == true) || (($this->getAttribute($this->getAttribute($this->getAttribute(                // line 473
(isset($context["formfield"]) ? $context["formfield"] : null), "vars", array()), "value", array()), "removable", array()) != true) && ($this->getAttribute($this->getAttribute($this->getAttribute(                // line 474
(isset($context["formfield"]) ? $context["formfield"] : null), "vars", array()), "value", array()), "name", array()) != "Home")))))) {
                    // line 477
                    echo "        ";
                    $context["homeLocation"] = false;
                    // line 478
                    echo "    ";
                } else {
                    // line 479
                    echo "        ";
                    $context["homeLocation"] = true;
                    // line 480
                    echo "    ";
                }
                // line 481
                echo "
    ";
                // line 482
                if ((($this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "vars", array()), "value", array()) && ($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "vars", array()), "value", array()), "status", array()) == twig_constant("Oleg\\UserdirectoryBundle\\Entity\\BaseUserAttributes::STATUS_UNVERIFIED"))) || ((isset($context["prototype"]) ? $context["prototype"] : null) == "prototype"))) {
                    // line 483
                    echo "        ";
                    $context["wellclass"] = "user-alert-warning";
                    // line 484
                    echo "    ";
                } else {
                    // line 485
                    echo "        ";
                    $context["wellclass"] = "";
                    // line 486
                    echo "    ";
                }
                // line 487
                echo "
    ";
                // line 488
                if (twig_in_filter("show", (isset($context["cycle"]) ? $context["cycle"] : null))) {
                    // line 489
                    echo "        ";
                    $context["showFlag"] = true;
                    // line 490
                    echo "    ";
                } else {
                    // line 491
                    echo "        ";
                    $context["showFlag"] = false;
                    // line 492
                    echo "    ";
                }
                // line 493
                echo "
    ";
                // line 495
                echo "    ";
                $context["standAloneLocation"] = false;
                // line 496
                echo "    ";
                if (twig_in_filter("standalone", (isset($context["cycle"]) ? $context["cycle"] : null))) {
                    // line 497
                    echo "        ";
                    $context["wellclass"] = "";
                    // line 498
                    echo "        ";
                    $context["standAloneLocation"] = true;
                    // line 499
                    echo "    ";
                }
                // line 500
                echo "    ";
                if (((isset($context["cycle"]) ? $context["cycle"] : null) == "new_standalone")) {
                    // line 501
                    echo "        ";
                    $context["wellclass"] = "";
                    // line 502
                    echo "    ";
                }
                // line 503
                echo "
    ";
                // line 505
                echo "    ";
                if ((( !(isset($context["standAloneLocation"]) ? $context["standAloneLocation"] : null) || $this->getAttribute($this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "vars", array()), "value", array()), "user", array())) || ((isset($context["cycle"]) ? $context["cycle"] : null) != "show_standalone"))) {
                    // line 506
                    echo "        ";
                    $context["standAloneLocationNotView"] = true;
                    // line 507
                    echo "    ";
                } else {
                    // line 508
                    echo "        ";
                    $context["standAloneLocationNotView"] = false;
                    // line 509
                    echo "    ";
                }
                // line 510
                echo "
    ";
                // line 512
                echo "    ";
                // line 513
                echo "    ";
                // line 514
                echo "    ";
                // line 515
                echo "    ";
                // line 516
                echo "    ";
                // line 517
                echo "    ";
                // line 518
                echo "    ";
                // line 519
                echo "    ";
                // line 520
                echo "
    ";
                // line 521
                if (($this->getAttribute($this->getAttribute((isset($context["app"]) ? $context["app"] : null), "user", array()), "getId", array(), "method") == $this->getAttribute((isset($context["entity"]) ? $context["entity"] : null), "id", array()))) {
                    // line 522
                    echo "        ";
                    $context["subjectuser"] = true;
                    // line 523
                    echo "    ";
                } else {
                    // line 524
                    echo "        ";
                    $context["subjectuser"] = false;
                    // line 525
                    echo "    ";
                }
                // line 526
                echo "
    ";
                // line 527
                if (($this->env->getExtension('Symfony\Bridge\Twig\Extension\SecurityExtension')->isGranted("ROLE_USERDIRECTORY_EDITOR") || $this->env->getExtension('Symfony\Bridge\Twig\Extension\SecurityExtension')->isGranted("ROLE_USERDIRECTORY_ADMIN"))) {
                    // line 528
                    echo "        ";
                    $context["userEditor"] = true;
                    // line 529
                    echo "    ";
                } else {
                    // line 530
                    echo "        ";
                    $context["userEditor"] = false;
                    // line 531
                    echo "    ";
                }
                // line 532
                echo "
    ";
                // line 533
                if (($this->env->getExtension('Symfony\Bridge\Twig\Extension\SecurityExtension')->isGranted("ROLE_SCANORDER_ONCALL_TRAINEE") || $this->env->getExtension('Symfony\Bridge\Twig\Extension\SecurityExtension')->isGranted("ROLE_SCANORDER_ONCALL_ATTENDING"))) {
                    // line 534
                    echo "        ";
                    $context["onCallUser"] = true;
                    // line 535
                    echo "    ";
                } else {
                    // line 536
                    echo "        ";
                    $context["onCallUser"] = false;
                    // line 537
                    echo "    ";
                }
                // line 538
                echo "
    ";
                // line 539
                $context["privacyCase1"] = false;
                // line 540
                echo "    ";
                $context["privacyCase2"] = false;
                // line 541
                echo "    ";
                if (((isset($context["homeLocation"]) ? $context["homeLocation"] : null) && ($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "vars", array()), "value", array()), "privacy", array()) == "Administration can see and edit this contact information"))) {
                    // line 542
                    echo "        ";
                    $context["privacyCase1"] = true;
                    // line 543
                    echo "    ";
                }
                // line 544
                echo "    ";
                if (((isset($context["homeLocation"]) ? $context["homeLocation"] : null) && (($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "vars", array()), "value", array()), "privacy", array()) == "Administration; Those 'on call' can see these phone numbers & email") ||  !$this->getAttribute($this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "vars", array()), "value", array()), "privacy", array())))) {
                    // line 545
                    echo "        ";
                    $context["privacyCase2"] = true;
                    // line 546
                    echo "    ";
                }
                // line 547
                echo "
    ";
                // line 548
                $context["showRegularFields1"] = true;
                // line 549
                echo "    ";
                $context["showRegularFields2"] = true;
                // line 550
                echo "
    ";
                // line 551
                if ((isset($context["privacyCase1"]) ? $context["privacyCase1"] : null)) {
                    // line 552
                    echo "        ";
                    $context["showRegularFields1"] = false;
                    // line 553
                    echo "        ";
                    $context["showRegularFields2"] = false;
                    // line 554
                    echo "
        ";
                    // line 555
                    if (((isset($context["userEditor"]) ? $context["userEditor"] : null) || (isset($context["subjectuser"]) ? $context["subjectuser"] : null))) {
                        // line 556
                        echo "            ";
                        $context["showRegularFields1"] = true;
                        // line 557
                        echo "            ";
                        $context["showRegularFields2"] = true;
                        // line 558
                        echo "        ";
                    }
                    // line 559
                    echo "    ";
                }
                // line 560
                echo "
    ";
                // line 561
                if ((isset($context["privacyCase2"]) ? $context["privacyCase2"] : null)) {
                    // line 562
                    echo "        ";
                    $context["showRegularFields1"] = false;
                    // line 563
                    echo "        ";
                    $context["showRegularFields2"] = false;
                    // line 564
                    echo "
        ";
                    // line 565
                    if ((isset($context["onCallUser"]) ? $context["onCallUser"] : null)) {
                        // line 566
                        echo "            ";
                        $context["showRegularFields2"] = true;
                        // line 567
                        echo "        ";
                    }
                    // line 568
                    echo "
        ";
                    // line 569
                    if (((isset($context["userEditor"]) ? $context["userEditor"] : null) || (isset($context["subjectuser"]) ? $context["subjectuser"] : null))) {
                        // line 570
                        echo "            ";
                        $context["showRegularFields1"] = true;
                        // line 571
                        echo "            ";
                        $context["showRegularFields2"] = true;
                        // line 572
                        echo "        ";
                    }
                    // line 573
                    echo "    ";
                }
                // line 574
                echo "
    ";
                // line 576
                echo "    ";
                if ((isset($context["standAloneLocation"]) ? $context["standAloneLocation"] : null)) {
                    // line 577
                    echo "        ";
                    $context["alertClass"] = "";
                    // line 578
                    echo "    ";
                } else {
                    // line 579
                    echo "        ";
                    $context["alertClass"] = "alert";
                    // line 580
                    echo "    ";
                }
                // line 581
                echo "
    ";
                // line 582
                if (((isset($context["showRegularFields1"]) ? $context["showRegularFields1"] : null) || (isset($context["showRegularFields2"]) ? $context["showRegularFields2"] : null))) {
                    // line 583
                    echo "        <div class=\"user-collection-holder ";
                    echo twig_escape_filter($this->env, (isset($context["alertClass"]) ? $context["alertClass"] : null), "html", null, true);
                    echo " ";
                    echo twig_escape_filter($this->env, (isset($context["classname"]) ? $context["classname"] : null), "html", null, true);
                    echo " ";
                    echo twig_escape_filter($this->env, (isset($context["wellclass"]) ? $context["wellclass"] : null), "html", null, true);
                    echo "\">

            ";
                    // line 585
                    if (( !(isset($context["showFlag"]) ? $context["showFlag"] : null) && (((isset($context["prototype"]) ? $context["prototype"] : null) == "prototype") || ($this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "vars", array()), "value", array()) && ($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "vars", array()), "value", array()), "removable", array()) == true))))) {
                        // line 586
                        echo "                ";
                        if (( !(isset($context["standAloneLocation"]) ? $context["standAloneLocation"] : null) && ((isset($context["cycle"]) ? $context["cycle"] : null) != "new_standalone"))) {
                            // line 587
                            echo "                    <div class=\"text-right\">
                        <button type=\"button\" class=\"btn btn-default btn-sm\" onClick=\"removeExistingObject(this,'";
                            // line 588
                            echo twig_escape_filter($this->env, (isset($context["classname"]) ? $context["classname"] : null), "html", null, true);
                            echo "')\" >
                            <span class=\"glyphicon glyphicon-remove\"></span>
                        </button>
                    </div>
                ";
                        }
                        // line 593
                        echo "            ";
                    }
                    // line 594
                    echo "
            ";
                    // line 595
                    if ((isset($context["showRegularFields1"]) ? $context["showRegularFields1"] : null)) {
                        // line 596
                        echo "                ";
                        echo $context["formmacros"]->getfield($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "id", array()));
                        echo "
                ";
                        // line 597
                        echo $context["usermacros"]->getstatusVerifiedField((isset($context["formfield"]) ? $context["formfield"] : null), (isset($context["cycle"]) ? $context["cycle"] : null));
                        echo "

                ";
                        // line 599
                        if ($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "user", array(), "any", true, true)) {
                            // line 600
                            echo "                    ";
                            // line 601
                            echo "                    ";
                            if ((isset($context["standAloneLocationNotView"]) ? $context["standAloneLocationNotView"] : null)) {
                                // line 602
                                echo "                        ";
                                echo $context["formmacros"]->getfield($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "user", array()));
                                echo "
                    ";
                            }
                            // line 604
                            echo "                ";
                        }
                        // line 605
                        echo "
                ";
                        // line 606
                        if (((isset($context["standAloneLocation"]) ? $context["standAloneLocation"] : null) && $this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "removable", array(), "any", true, true))) {
                            // line 607
                            echo "                    ";
                            echo $context["formmacros"]->getfield($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "removable", array()));
                            echo "
                ";
                        }
                        // line 609
                        echo "
                ";
                        // line 610
                        if (((((isset($context["standAloneLocation"]) ? $context["standAloneLocation"] : null) || ((isset($context["prototype"]) ? $context["prototype"] : null) == "prototype")) || ($this->getAttribute($this->getAttribute(                        // line 611
(isset($context["formfield"]) ? $context["formfield"] : null), "vars", array()), "value", array()) && ($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "vars", array()), "value", array()), "removable", array()) == true))) || (                        // line 612
array_key_exists("editable", $context) && ((isset($context["editable"]) ? $context["editable"] : null) == "editable")))) {
                            // line 614
                            echo "                    ";
                            echo $context["formmacros"]->getfield($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "name", array()));
                            echo "
                ";
                        } else {
                            // line 616
                            echo "                    ";
                            echo $context["formmacros"]->getfield($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "name", array()), "", "readonly");
                            echo "
                ";
                        }
                        // line 618
                        echo "
                ";
                        // line 619
                        if ((((isset($context["homeLocation"]) ? $context["homeLocation"] : null) == true) || (isset($context["standAloneLocation"]) ? $context["standAloneLocation"] : null))) {
                            // line 620
                            echo "                    ";
                            // line 621
                            echo "                    ";
                            if (((                            // line 622
(isset($context["cycle"]) ? $context["cycle"] : null) != "show_user") || (((                            // line 623
(isset($context["cycle"]) ? $context["cycle"] : null) == "show_user") && ((isset($context["userEditor"]) ? $context["userEditor"] : null) || (isset($context["subjectuser"]) ? $context["subjectuser"] : null))) && $this->getAttribute($this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "vars", array()), "value", array()), "user", array())))) {
                                // line 625
                                echo "                        ";
                                if ((((isset($context["cycle"]) ? $context["cycle"] : null) != "show_standalone") || ((((isset($context["cycle"]) ? $context["cycle"] : null) == "show_standalone") && ((isset($context["userEditor"]) ? $context["userEditor"] : null) || (isset($context["subjectuser"]) ? $context["subjectuser"] : null))) && $this->getAttribute($this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "vars", array()), "value", array()), "user", array())))) {
                                    // line 626
                                    echo "                            ";
                                    echo $context["formmacros"]->getfield($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "privacy", array()));
                                    echo "
                        ";
                                } else {
                                    // line 628
                                    echo "                            ";
                                    // line 629
                                    echo "                        ";
                                }
                                // line 630
                                echo "                    ";
                            }
                            // line 631
                            echo "                ";
                        } else {
                            // line 632
                            echo "                    ";
                            $this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "privacy", array()), "setRendered", array());
                            // line 633
                            echo "                ";
                        }
                        // line 634
                        echo "
                ";
                        // line 635
                        if ($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "locationTypes", array(), "any", true, true)) {
                            // line 636
                            echo "                    ";
                            echo $context["formmacros"]->getfield($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "locationTypes", array()));
                            echo "
                ";
                        }
                        // line 638
                        echo "            ";
                    }
                    // line 639
                    echo "
            ";
                    // line 640
                    if ((isset($context["showRegularFields2"]) ? $context["showRegularFields2"] : null)) {
                        // line 641
                        echo "                ";
                        echo $context["usermacros"]->getemailPhoneField($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "phone", array()), (isset($context["cycle"]) ? $context["cycle"] : null), "phone", "");
                        echo "

                ";
                        // line 643
                        if ($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "pager", array(), "any", true, true)) {
                            // line 644
                            echo "                    ";
                            echo $context["usermacros"]->getemailPhoneField_notempty($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "pager", array()), (isset($context["cycle"]) ? $context["cycle"] : null), "phone", "");
                            echo "
                ";
                        }
                        // line 646
                        echo "                ";
                        if ($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "mobile", array(), "any", true, true)) {
                            // line 647
                            echo "                    ";
                            echo $context["usermacros"]->getemailPhoneField_notempty($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "mobile", array()), (isset($context["cycle"]) ? $context["cycle"] : null), "phone", "");
                            echo "
                ";
                        }
                        // line 649
                        echo "
                ";
                        // line 650
                        if ((((isset($context["homeLocation"]) ? $context["homeLocation"] : null) == false) && $this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "ic", array(), "any", true, true))) {
                            // line 651
                            echo "                    ";
                            echo $context["formmacros"]->getfield_notempty($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "ic", array()), (isset($context["cycle"]) ? $context["cycle"] : null));
                            echo "
                ";
                        }
                        // line 653
                        echo "
                ";
                        // line 654
                        if ($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "fax", array(), "any", true, true)) {
                            // line 655
                            echo "                    ";
                            echo $context["formmacros"]->getfield_notempty($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "fax", array()), (isset($context["cycle"]) ? $context["cycle"] : null));
                            echo "
                ";
                        }
                        // line 657
                        echo "
                ";
                        // line 658
                        if ($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "email", array(), "any", true, true)) {
                            // line 659
                            echo "                    ";
                            echo $context["usermacros"]->getemailPhoneField_notempty($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "email", array()), (isset($context["cycle"]) ? $context["cycle"] : null), "email", "");
                            echo "
                ";
                        }
                        // line 661
                        echo "
            ";
                    }
                    // line 663
                    echo "
            ";
                    // line 664
                    if ((isset($context["showRegularFields1"]) ? $context["showRegularFields1"] : null)) {
                        // line 665
                        echo "                ";
                        if (((isset($context["homeLocation"]) ? $context["homeLocation"] : null) == false)) {
                            // line 666
                            echo "
                    ";
                            // line 668
                            echo "                    ";
                            // line 669
                            echo "
                        ";
                            // line 671
                            echo "                        ";
                            if ($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "assistant", array(), "any", true, true)) {
                                // line 672
                                echo "                            ";
                                if ( !(isset($context["showFlag"]) ? $context["showFlag"] : null)) {
                                    // line 673
                                    echo "                                ";
                                    echo $context["formmacros"]->getfield($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "assistant", array()));
                                    echo "
                            ";
                                } else {
                                    // line 675
                                    echo "                                ";
                                    if ((($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "vars", array(), "any", false, true), "value", array(), "any", false, true), "assistant", array(), "any", true, true) && ($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "vars", array()), "value", array()), "assistant", array()) != null)) && (twig_length_filter($this->env, $this->getAttribute($this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "vars", array()), "value", array()), "assistant", array())) > 0))) {
                                        // line 676
                                        echo "                                        <div style=\"text-align: center;\">
                                            <p>
                                                <strong>Assistant(s):</strong>
                                            </p>
                                        </div>
                                        ";
                                        // line 681
                                        $context['_parent'] = $context;
                                        $context['_seq'] = twig_ensure_traversable($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "vars", array()), "value", array()), "assistant", array()));
                                        foreach ($context['_seq'] as $context["_key"] => $context["assistant"]) {
                                            // line 682
                                            echo "                                            ";
                                            echo $context["usermacros"]->getpersonInfo($context["assistant"], (isset($context["cycle"]) ? $context["cycle"] : null), (isset($context["sitename"]) ? $context["sitename"] : null));
                                            echo "
                                        ";
                                        }
                                        $_parent = $context['_parent'];
                                        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['assistant'], $context['_parent'], $context['loop']);
                                        $context = array_intersect_key($context, $_parent) + $_parent;
                                        // line 684
                                        echo "
                                ";
                                    }
                                    // line 686
                                    echo "                            ";
                                }
                                // line 687
                                echo "                        ";
                            }
                            // line 688
                            echo "
                    ";
                            // line 690
                            echo "
                ";
                        }
                        // line 692
                        echo "
                ";
                        // line 693
                        if (((isset($context["homeLocation"]) ? $context["homeLocation"] : null) == false)) {
                            // line 694
                            echo "
                    ";
                            // line 696
                            echo "                    ";
                            if ($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "institution", array(), "any", true, true)) {
                                // line 697
                                echo "                        ";
                                echo $context["treemacros"]->getcompositeTreeNode_notempty($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "institution", array()), (isset($context["cycle"]) ? $context["cycle"] : null), (isset($context["prototype"]) ? $context["prototype"] : null));
                                echo "
                    ";
                            }
                            // line 699
                            echo "
                    ";
                            // line 700
                            if ($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "mailbox", array(), "any", true, true)) {
                                // line 701
                                echo "                        ";
                                echo $context["formmacros"]->getfield_notempty($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "mailbox", array()), (isset($context["cycle"]) ? $context["cycle"] : null));
                                echo "
                    ";
                            }
                            // line 703
                            echo "
                    ";
                            // line 704
                            if ($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "room", array(), "any", true, true)) {
                                // line 705
                                echo "                        ";
                                echo $context["formmacros"]->getfield_notempty($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "room", array()), (isset($context["cycle"]) ? $context["cycle"] : null));
                                echo "
                    ";
                            }
                            // line 707
                            echo "                    ";
                            if ($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "suite", array(), "any", true, true)) {
                                // line 708
                                echo "                        ";
                                echo $context["formmacros"]->getfield_notempty($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "suite", array()), (isset($context["cycle"]) ? $context["cycle"] : null));
                                echo "
                    ";
                            }
                            // line 710
                            echo "                    ";
                            if ($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "floor", array(), "any", true, true)) {
                                // line 711
                                echo "                        ";
                                echo $context["formmacros"]->getfield($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "floor", array()));
                                echo "
                    ";
                            }
                            // line 713
                            echo "                    ";
                            if ($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "floorSide", array(), "any", true, true)) {
                                // line 714
                                echo "                        ";
                                echo $context["formmacros"]->getfield($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "floorSide", array()));
                                echo "
                    ";
                            }
                            // line 716
                            echo "
                    ";
                            // line 717
                            echo $context["formmacros"]->getfield($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "building", array()));
                            echo "

                ";
                        }
                        // line 720
                        echo "
                ";
                        // line 722
                        echo "                ";
                        echo $context["usermacros"]->getgeoLocation((isset($context["field"]) ? $context["field"] : null), (isset($context["cycle"]) ? $context["cycle"] : null), (isset($context["prototype"]) ? $context["prototype"] : null));
                        echo "

                ";
                        // line 724
                        if ((((isset($context["homeLocation"]) ? $context["homeLocation"] : null) == false) && $this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "associatedCode", array(), "any", true, true))) {
                            // line 725
                            echo "                    ";
                            echo $context["formmacros"]->getfield_notempty($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "associatedCode", array()), (isset($context["cycle"]) ? $context["cycle"] : null));
                            echo "
                ";
                        }
                        // line 727
                        echo "
                ";
                        // line 729
                        echo "                ";
                        if ($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "associatedClia", array(), "any", true, true)) {
                            // line 730
                            echo "                    ";
                            echo $context["formmacros"]->getfield_notempty($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "associatedClia", array()), (isset($context["cycle"]) ? $context["cycle"] : null));
                            echo "
                    ";
                            // line 731
                            echo $context["formmacros"]->getfieldDateLabel_notempty($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "associatedCliaExpDate", array()), (isset($context["cycle"]) ? $context["cycle"] : null), "allow-future-date");
                            echo "
                    ";
                            // line 732
                            echo $context["formmacros"]->getfield_notempty($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "associatedPfi", array()), (isset($context["cycle"]) ? $context["cycle"] : null));
                            echo "
                ";
                        }
                        // line 734
                        echo "
                ";
                        // line 735
                        echo $context["formmacros"]->getfield_notempty($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "comment", array()), (isset($context["cycle"]) ? $context["cycle"] : null));
                        echo "
            ";
                    }
                    // line 736
                    echo " ";
                    // line 737
                    echo "
        </div>
    ";
                }
                // line 740
                echo "
    ";
                // line 742
                echo "    ";
                // line 743
                echo "        ";
                // line 744
                echo "    ";
                // line 745
                echo "
    ";
                // line 746
                $this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "setRendered", array());
                // line 747
                echo "    
";
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

    // line 753
    public function getgeoLocation($__entity__ = null, $__cycle__ = null, $__prototype__ = null, ...$__varargs__)
    {
        $context = $this->env->mergeGlobals(array(
            "entity" => $__entity__,
            "cycle" => $__cycle__,
            "prototype" => $__prototype__,
            "varargs" => $__varargs__,
        ));

        $blocks = array();

        ob_start();
        try {
            // line 754
            echo "    ";
            $context["formmacros"] = $this->loadTemplate("OlegOrderformBundle::Default/formmacros.html.twig", "OlegUserdirectoryBundle::Default/usermacros.html.twig", 754);
            // line 755
            echo "
    ";
            // line 756
            if (((isset($context["prototype"]) ? $context["prototype"] : null) == "prototype")) {
                // line 757
                echo "        ";
                $context["formfield"] = $this->getAttribute($this->getAttribute($this->getAttribute((isset($context["entity"]) ? $context["entity"] : null), "vars", array()), "prototype", array()), "geoLocation", array());
                // line 758
                echo "    ";
            } else {
                // line 759
                echo "        ";
                $context["formfield"] = $this->getAttribute((isset($context["entity"]) ? $context["entity"] : null), "geoLocation", array());
                // line 760
                echo "    ";
            }
            // line 761
            echo "
    ";
            // line 762
            echo $context["formmacros"]->getfield_notempty($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "street1", array()), (isset($context["cycle"]) ? $context["cycle"] : null));
            echo "
    ";
            // line 763
            echo $context["formmacros"]->getfield_notempty($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "street2", array()), (isset($context["cycle"]) ? $context["cycle"] : null));
            echo "
    ";
            // line 764
            echo $context["formmacros"]->getfield_notempty($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "city", array()), (isset($context["cycle"]) ? $context["cycle"] : null));
            echo "
    ";
            // line 765
            echo $context["formmacros"]->getfield_notempty($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "state", array()), (isset($context["cycle"]) ? $context["cycle"] : null));
            echo "
    ";
            // line 766
            echo $context["formmacros"]->getfield_notempty($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "zip", array()), (isset($context["cycle"]) ? $context["cycle"] : null));
            echo "

    ";
            // line 768
            if ($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "county", array(), "any", true, true)) {
                // line 769
                echo "        ";
                echo $context["formmacros"]->getfield_notempty($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "county", array()), (isset($context["cycle"]) ? $context["cycle"] : null));
                echo "
    ";
            }
            // line 771
            echo "
    ";
            // line 772
            echo $context["formmacros"]->getfield_notempty($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "country", array()), (isset($context["cycle"]) ? $context["cycle"] : null));
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

    // line 775
    public function getstatusVerifiedField($__entity__ = null, $__cycle__ = null, $__isEntity__ = null, ...$__varargs__)
    {
        $context = $this->env->mergeGlobals(array(
            "entity" => $__entity__,
            "cycle" => $__cycle__,
            "isEntity" => $__isEntity__,
            "varargs" => $__varargs__,
        ));

        $blocks = array();

        ob_start();
        try {
            // line 776
            echo "
    ";
            // line 777
            if ( !array_key_exists("isEntity", $context)) {
                // line 778
                echo "        ";
                $context["isEntity"] = false;
                // line 779
                echo "    ";
            }
            // line 780
            echo "
    ";
            // line 781
            $context["statusValueDefined"] = false;
            // line 782
            echo "
    ";
            // line 783
            if ((isset($context["isEntity"]) ? $context["isEntity"] : null)) {
                // line 784
                echo "        ";
                $context["statusValueDefined"] = true;
                // line 785
                echo "        ";
                $context["statusValue"] = $this->getAttribute((isset($context["entity"]) ? $context["entity"] : null), "status", array());
                // line 786
                echo "    ";
            } else {
                // line 787
                echo "        ";
                if ($this->getAttribute((isset($context["entity"]) ? $context["entity"] : null), "status", array(), "any", true, true)) {
                    // line 788
                    echo "            ";
                    $context["statusValueDefined"] = true;
                    // line 789
                    echo "            ";
                    $context["statusValue"] = $this->getAttribute($this->getAttribute($this->getAttribute((isset($context["entity"]) ? $context["entity"] : null), "status", array()), "vars", array()), "value", array());
                    // line 790
                    echo "        ";
                }
                // line 791
                echo "    ";
            }
            // line 792
            echo "
    ";
            // line 793
            if ((isset($context["statusValueDefined"]) ? $context["statusValueDefined"] : null)) {
                // line 794
                echo "
        ";
                // line 796
                echo "        ";
                if (twig_in_filter("show", (isset($context["cycle"]) ? $context["cycle"] : null))) {
                    // line 797
                    echo "            ";
                    $context["showFlag"] = true;
                    // line 798
                    echo "        ";
                } else {
                    // line 799
                    echo "            ";
                    $context["showFlag"] = false;
                    // line 800
                    echo "        ";
                }
                // line 801
                echo "
        ";
                // line 802
                if (( !(isset($context["showFlag"]) ? $context["showFlag"] : null) || ((isset($context["statusValue"]) ? $context["statusValue"] : null) != twig_constant("Oleg\\UserdirectoryBundle\\Entity\\BaseUserAttributes::STATUS_VERIFIED")))) {
                    // line 803
                    echo "            ";
                    $context["formmacros"] = $this->loadTemplate("OlegOrderformBundle::Default/formmacros.html.twig", "OlegUserdirectoryBundle::Default/usermacros.html.twig", 803);
                    // line 804
                    echo "            ";
                    if ((isset($context["isEntity"]) ? $context["isEntity"] : null)) {
                        // line 805
                        echo "                ";
                        echo $context["formmacros"]->getsimplefield("Status:", $this->getAttribute((isset($context["entity"]) ? $context["entity"] : null), "getStatusStr", array()), "", "disabled");
                        echo "
            ";
                    } else {
                        // line 807
                        echo "                ";
                        echo $context["formmacros"]->getfield($this->getAttribute((isset($context["entity"]) ? $context["entity"] : null), "status", array()));
                        echo "
            ";
                    }
                    // line 809
                    echo "        ";
                } else {
                    // line 810
                    echo "            ";
                    if ( !(isset($context["isEntity"]) ? $context["isEntity"] : null)) {
                        // line 811
                        echo "                ";
                        $this->getAttribute($this->getAttribute((isset($context["entity"]) ? $context["entity"] : null), "status", array()), "setRendered", array());
                        // line 812
                        echo "            ";
                    }
                    // line 813
                    echo "        ";
                }
                // line 814
                echo "
    ";
            }
            // line 816
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

    // line 820
    public function getcheckbox_notempty($__field__ = null, $__value__ = null, $__cycle__ = null, ...$__varargs__)
    {
        $context = $this->env->mergeGlobals(array(
            "field" => $__field__,
            "value" => $__value__,
            "cycle" => $__cycle__,
            "varargs" => $__varargs__,
        ));

        $blocks = array();

        ob_start();
        try {
            // line 821
            echo "    ";
            $context["formmacros"] = $this->loadTemplate("OlegOrderformBundle::Default/formmacros.html.twig", "OlegUserdirectoryBundle::Default/usermacros.html.twig", 821);
            // line 822
            echo "
    ";
            // line 823
            if (twig_in_filter("show", (isset($context["cycle"]) ? $context["cycle"] : null))) {
                // line 824
                echo "        ";
                $context["showFlag"] = true;
                // line 825
                echo "    ";
            } else {
                // line 826
                echo "        ";
                $context["showFlag"] = false;
                // line 827
                echo "    ";
            }
            // line 828
            echo "
    ";
            // line 829
            if ((((isset($context["value"]) ? $context["value"] : null) == 1) ||  !(isset($context["showFlag"]) ? $context["showFlag"] : null))) {
                // line 830
                echo "        ";
                echo $context["formmacros"]->getcheckbox((isset($context["field"]) ? $context["field"] : null));
                echo "
    ";
            } else {
                // line 832
                echo "        ";
                $this->getAttribute((isset($context["field"]) ? $context["field"] : null), "setRendered", array());
                // line 833
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

    // line 836
    public function getemailPhoneField_notempty($__field__ = null, $__cycle__ = null, $__type__ = null, $__label__ = null, ...$__varargs__)
    {
        $context = $this->env->mergeGlobals(array(
            "field" => $__field__,
            "cycle" => $__cycle__,
            "type" => $__type__,
            "label" => $__label__,
            "varargs" => $__varargs__,
        ));

        $blocks = array();

        ob_start();
        try {
            // line 837
            echo "    ";
            $context["formuser"] = $this;
            // line 838
            echo "
    ";
            // line 839
            if (twig_in_filter("show", (isset($context["cycle"]) ? $context["cycle"] : null))) {
                // line 840
                echo "        ";
                $context["showFlag"] = true;
                // line 841
                echo "    ";
            } else {
                // line 842
                echo "        ";
                $context["showFlag"] = false;
                // line 843
                echo "    ";
            }
            // line 844
            echo "
    ";
            // line 845
            if (($this->getAttribute($this->getAttribute((isset($context["field"]) ? $context["field"] : null), "vars", array()), "value", array()) ||  !(isset($context["showFlag"]) ? $context["showFlag"] : null))) {
                // line 846
                echo "        ";
                echo $context["formuser"]->getemailPhoneField((isset($context["field"]) ? $context["field"] : null), (isset($context["cycle"]) ? $context["cycle"] : null), (isset($context["type"]) ? $context["type"] : null), (isset($context["label"]) ? $context["label"] : null));
                echo "
    ";
            } else {
                // line 848
                echo "        ";
                $this->getAttribute((isset($context["field"]) ? $context["field"] : null), "setRendered", array());
                // line 849
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

    // line 852
    public function getemailPhoneField($__field__ = null, $__cycle__ = null, $__type__ = null, $__label__ = null, ...$__varargs__)
    {
        $context = $this->env->mergeGlobals(array(
            "field" => $__field__,
            "cycle" => $__cycle__,
            "type" => $__type__,
            "label" => $__label__,
            "varargs" => $__varargs__,
        ));

        $blocks = array();

        ob_start();
        try {
            // line 853
            echo "    ";
            $context["formmacros"] = $this->loadTemplate("OlegOrderformBundle::Default/formmacros.html.twig", "OlegUserdirectoryBundle::Default/usermacros.html.twig", 853);
            // line 854
            echo "    ";
            $context["usermacros"] = $this;
            // line 855
            echo "
    ";
            // line 856
            if (((isset($context["label"]) ? $context["label"] : null) == "")) {
                // line 857
                echo "        ";
                $context["value"] = $this->getAttribute($this->getAttribute((isset($context["field"]) ? $context["field"] : null), "vars", array()), "value", array());
                // line 858
                echo "    ";
            } else {
                // line 859
                echo "        ";
                $context["value"] = (isset($context["field"]) ? $context["field"] : null);
                // line 860
                echo "    ";
            }
            // line 861
            echo "
    ";
            // line 862
            if (twig_in_filter("show", (isset($context["cycle"]) ? $context["cycle"] : null))) {
                // line 863
                echo "        ";
                $context["showFlag"] = true;
                // line 864
                echo "    ";
            } else {
                // line 865
                echo "        ";
                $context["showFlag"] = false;
                // line 866
                echo "    ";
            }
            // line 867
            echo "
    ";
            // line 868
            if ((isset($context["showFlag"]) ? $context["showFlag"] : null)) {
                // line 869
                echo "        <p>
        <div class=\"row\">
            <div class=\"col-xs-6\" align=\"right\">
                ";
                // line 872
                if (((isset($context["label"]) ? $context["label"] : null) == "")) {
                    // line 873
                    echo "                    ";
                    echo $this->env->getRuntime('Symfony\Bridge\Twig\Form\TwigRenderer')->searchAndRenderBlock((isset($context["field"]) ? $context["field"] : null), 'label');
                    echo "
                ";
                } else {
                    // line 875
                    echo "                    <strong>";
                    echo twig_escape_filter($this->env, (isset($context["label"]) ? $context["label"] : null), "html", null, true);
                    echo "</strong>
                ";
                }
                // line 877
                echo "            </div>
            <div class=\"col-xs-6\" align=\"left\">
                <div class=\"form-control form-control-modif\" disabled>
                    ";
                // line 880
                if (((isset($context["type"]) ? $context["type"] : null) == "email")) {
                    // line 881
                    echo "                        <a href=\"mailto:";
                    echo twig_escape_filter($this->env, (isset($context["value"]) ? $context["value"] : null), "html", null, true);
                    echo "\" target=\"_top\">";
                    echo twig_escape_filter($this->env, (isset($context["value"]) ? $context["value"] : null), "html", null, true);
                    echo "</a>
                    ";
                }
                // line 883
                echo "                    ";
                if (((isset($context["type"]) ? $context["type"] : null) == "phone")) {
                    // line 884
                    echo "                        ";
                    echo $context["usermacros"]->getphoneHref((isset($context["value"]) ? $context["value"] : null));
                    echo "
                    ";
                }
                // line 886
                echo "                    ";
                if (((isset($context["type"]) ? $context["type"] : null) == "link")) {
                    // line 887
                    echo "                        ";
                    if (!twig_in_filter("http", (isset($context["value"]) ? $context["value"] : null))) {
                        // line 888
                        echo "                            ";
                        $context["weburl"] = ("http://" . (isset($context["value"]) ? $context["value"] : null));
                        // line 889
                        echo "                        ";
                    } else {
                        // line 890
                        echo "                            ";
                        $context["weburl"] = (isset($context["value"]) ? $context["value"] : null);
                        // line 891
                        echo "                        ";
                    }
                    // line 892
                    echo "                        <a href=\"";
                    echo twig_escape_filter($this->env, (isset($context["weburl"]) ? $context["weburl"] : null), "html", null, true);
                    echo "\" target=\"_top\">";
                    echo twig_escape_filter($this->env, (isset($context["weburl"]) ? $context["weburl"] : null), "html", null, true);
                    echo "</a>
                    ";
                }
                // line 894
                echo "                </div>
            </div>
        </div>
        </p>
        ";
                // line 898
                if (((isset($context["label"]) ? $context["label"] : null) == "")) {
                    // line 899
                    echo "            ";
                    $this->getAttribute((isset($context["field"]) ? $context["field"] : null), "setRendered", array());
                    // line 900
                    echo "        ";
                }
                // line 901
                echo "    ";
            } else {
                // line 902
                echo "        ";
                echo $context["formmacros"]->getfield((isset($context["field"]) ? $context["field"] : null));
                echo "
    ";
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

    // line 907
    public function getphoneHref($__phone__ = null, ...$__varargs__)
    {
        $context = $this->env->mergeGlobals(array(
            "phone" => $__phone__,
            "varargs" => $__varargs__,
        ));

        $blocks = array();

        ob_start();
        try {
            // line 908
            echo "    ";
            $context["valueclean"] = twig_replace_filter((isset($context["phone"]) ? $context["phone"] : null), array("+" => "", " " => "", ")" => "", "(" => "", "-" => ""));
            // line 909
            echo "    <a href=\"tel:";
            echo twig_escape_filter($this->env, (isset($context["valueclean"]) ? $context["valueclean"] : null), "html", null, true);
            echo "\" target=\"_top\">";
            echo twig_escape_filter($this->env, (isset($context["phone"]) ? $context["phone"] : null), "html", null, true);
            echo "</a>
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

    // line 912
    public function getpersonInfo($__person__ = null, $__cycle__ = null, $__sitename__ = null, ...$__varargs__)
    {
        $context = $this->env->mergeGlobals(array(
            "person" => $__person__,
            "cycle" => $__cycle__,
            "sitename" => $__sitename__,
            "varargs" => $__varargs__,
        ));

        $blocks = array();

        ob_start();
        try {
            // line 913
            echo "    ";
            $context["formmacros"] = $this->loadTemplate("OlegOrderformBundle::Default/formmacros.html.twig", "OlegUserdirectoryBundle::Default/usermacros.html.twig", 913);
            // line 914
            echo "    ";
            $context["usermacros"] = $this;
            // line 915
            echo "    <p>
    <div class=\"well well-sm user-margin-block\">
        ";
            // line 917
            $context["personurl"] = $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath(((isset($context["sitename"]) ? $context["sitename"] : null) . "_showuser"), array("id" => $this->getAttribute((isset($context["person"]) ? $context["person"] : null), "id", array())));
            // line 918
            echo "        ";
            $context["personlink"] = (((("<a href=\"" . (isset($context["personurl"]) ? $context["personurl"] : null)) . "\">") . $this->getAttribute((isset($context["person"]) ? $context["person"] : null), "getUsernameOptimal", array(), "method")) . "</a>");
            // line 919
            echo "        ";
            echo $context["formmacros"]->getsimplefield("Name:", (isset($context["personlink"]) ? $context["personlink"] : null), "", "disabled");
            echo "
        ";
            // line 920
            echo $context["usermacros"]->getemailPhoneField($this->getAttribute((isset($context["person"]) ? $context["person"] : null), "preferredPhone", array()), (isset($context["cycle"]) ? $context["cycle"] : null), "phone", "Preferred Phone Number:");
            echo "
        ";
            // line 921
            echo $context["usermacros"]->getemailPhoneField($this->getAttribute((isset($context["person"]) ? $context["person"] : null), "email", array()), (isset($context["cycle"]) ? $context["cycle"] : null), "email", "Preferred Email:");
            echo "
    </div>
    </p>
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

    // line 927
    public function getlistProperties($__formfield__ = null, ...$__varargs__)
    {
        $context = $this->env->mergeGlobals(array(
            "formfield" => $__formfield__,
            "varargs" => $__varargs__,
        ));

        $blocks = array();

        ob_start();
        try {
            // line 928
            echo "    ";
            $context["formmacros"] = $this->loadTemplate("OlegOrderformBundle::Default/formmacros.html.twig", "OlegUserdirectoryBundle::Default/usermacros.html.twig", 928);
            // line 929
            echo "    <div class=\"well well-sm\">
        <p>
        <h4 class=\"text-muted\" align=\"center\">List properties:</h4>
        </p>

        ";
            // line 934
            if ($this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "list", array(), "any", false, true), "name", array(), "any", true, true)) {
                // line 935
                echo "            ";
                echo $context["formmacros"]->getfield($this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "list", array()), "name", array()));
                echo "
        ";
            }
            // line 937
            echo "
        ";
            // line 938
            if ($this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "list", array(), "any", false, true), "abbreviation", array(), "any", true, true)) {
                // line 939
                echo "            ";
                echo $context["formmacros"]->getfield($this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "list", array()), "abbreviation", array()));
                echo "
        ";
            }
            // line 941
            echo "
        ";
            // line 942
            if ($this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "list", array(), "any", false, true), "shortname", array(), "any", true, true)) {
                // line 943
                echo "            ";
                echo $context["formmacros"]->getfield($this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "list", array()), "shortname", array()));
                echo "
        ";
            }
            // line 945
            echo "
        ";
            // line 946
            if ($this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "list", array(), "any", false, true), "description", array(), "any", true, true)) {
                // line 947
                echo "            ";
                echo $context["formmacros"]->getfield($this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "list", array()), "description", array()));
                echo "
        ";
            }
            // line 949
            echo "
        ";
            // line 950
            if ($this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "list", array(), "any", false, true), "original", array(), "any", true, true)) {
                // line 951
                echo "            ";
                echo $context["formmacros"]->getfield($this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "list", array()), "original", array()));
                echo "
        ";
            }
            // line 953
            echo "
        ";
            // line 954
            if ($this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "list", array(), "any", false, true), "synonyms", array(), "any", true, true)) {
                // line 955
                echo "            ";
                echo $context["formmacros"]->getfield($this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "list", array()), "synonyms", array()));
                echo "
        ";
            }
            // line 957
            echo "
        ";
            // line 958
            if ($this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "list", array(), "any", false, true), "type", array(), "any", true, true)) {
                // line 959
                echo "            ";
                echo $context["formmacros"]->getfield($this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "list", array()), "type", array()));
                echo "
        ";
            }
            // line 961
            echo "
        ";
            // line 962
            if ($this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "list", array(), "any", false, true), "creator", array(), "any", true, true)) {
                // line 963
                echo "            ";
                echo $context["formmacros"]->getfield($this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "list", array()), "creator", array()));
                echo "
        ";
            }
            // line 965
            echo "
        ";
            // line 966
            if ($this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "list", array(), "any", false, true), "createdate", array(), "any", true, true)) {
                // line 967
                echo "            ";
                echo $context["formmacros"]->getfield($this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "list", array()), "createdate", array()));
                echo "
        ";
            }
            // line 969
            echo "
        ";
            // line 970
            if ($this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "list", array(), "any", false, true), "orderinlist", array(), "any", true, true)) {
                // line 971
                echo "            ";
                echo $context["formmacros"]->getfield($this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "list", array()), "orderinlist", array()));
                echo "
        ";
            }
            // line 973
            echo "
        ";
            // line 974
            if ($this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "list", array(), "any", false, true), "updatedby", array(), "any", true, true)) {
                // line 975
                echo "            ";
                echo $context["formmacros"]->getfield($this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "list", array()), "updatedby", array()));
                echo "
            ";
                // line 976
                echo $context["formmacros"]->getfield($this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "list", array()), "updatedon", array()));
                echo "
        ";
            }
            // line 978
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

    // line 981
    public function getbuildingObject($__field__ = null, $__cycle__ = null, $__classname__ = null, $__prototype__ = null, $__sitename__ = null, $__entity__ = null, ...$__varargs__)
    {
        $context = $this->env->mergeGlobals(array(
            "field" => $__field__,
            "cycle" => $__cycle__,
            "classname" => $__classname__,
            "prototype" => $__prototype__,
            "sitename" => $__sitename__,
            "entity" => $__entity__,
            "varargs" => $__varargs__,
        ));

        $blocks = array();

        ob_start();
        try {
            // line 982
            echo "    ";
            $context["formmacros"] = $this->loadTemplate("OlegOrderformBundle::Default/formmacros.html.twig", "OlegUserdirectoryBundle::Default/usermacros.html.twig", 982);
            // line 983
            echo "    ";
            $context["usermacros"] = $this;
            // line 984
            echo "
    ";
            // line 985
            if (((isset($context["prototype"]) ? $context["prototype"] : null) == "prototype")) {
                // line 986
                echo "        ";
                $context["formfield"] = $this->getAttribute($this->getAttribute((isset($context["field"]) ? $context["field"] : null), "vars", array()), "prototype", array());
                // line 987
                echo "    ";
            } else {
                // line 988
                echo "        ";
                $context["formfield"] = (isset($context["field"]) ? $context["field"] : null);
                // line 989
                echo "    ";
            }
            // line 990
            echo "
    ";
            // line 991
            $context["standAloneLocation"] = false;
            // line 992
            echo "    ";
            if (twig_in_filter("standalone", (isset($context["cycle"]) ? $context["cycle"] : null))) {
                // line 993
                echo "        ";
                $context["wellclass"] = "";
                // line 994
                echo "        ";
                $context["standAloneLocation"] = true;
                // line 995
                echo "    ";
            }
            // line 996
            echo "
    ";
            // line 997
            echo $context["formmacros"]->getfield($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "name", array()));
            echo "
    ";
            // line 998
            echo $context["formmacros"]->getfield($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "abbreviation", array()));
            echo "

    ";
            // line 1001
            echo "    ";
            echo $context["usermacros"]->getgeoLocation((isset($context["field"]) ? $context["field"] : null), (isset($context["cycle"]) ? $context["cycle"] : null), (isset($context["prototype"]) ? $context["prototype"] : null));
            echo "

    ";
            // line 1003
            echo $context["formmacros"]->getfield($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "institutions", array()));
            echo "
    ";
            // line 1005
            echo "
    <br>

    ";
            // line 1009
            echo "    ";
            if ((isset($context["standAloneLocation"]) ? $context["standAloneLocation"] : null)) {
                // line 1010
                echo "        ";
                echo $context["usermacros"]->getlistProperties((isset($context["formfield"]) ? $context["formfield"] : null));
                echo "
    ";
            }
            // line 1012
            echo "
    ";
            // line 1013
            $this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "setRendered", array());
            // line 1014
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

    // line 1018
    public function getresearchlabObject($__field__ = null, $__cycle__ = null, $__classname__ = null, $__prototype__ = null, $__sitename__ = null, $__user__ = null, ...$__varargs__)
    {
        $context = $this->env->mergeGlobals(array(
            "field" => $__field__,
            "cycle" => $__cycle__,
            "classname" => $__classname__,
            "prototype" => $__prototype__,
            "sitename" => $__sitename__,
            "user" => $__user__,
            "varargs" => $__varargs__,
        ));

        $blocks = array();

        ob_start();
        try {
            // line 1019
            echo "    ";
            $context["formmacros"] = $this->loadTemplate("OlegOrderformBundle::Default/formmacros.html.twig", "OlegUserdirectoryBundle::Default/usermacros.html.twig", 1019);
            // line 1020
            echo "    ";
            $context["usermacros"] = $this;
            // line 1021
            echo "
    ";
            // line 1022
            if (((isset($context["prototype"]) ? $context["prototype"] : null) == "prototype")) {
                // line 1023
                echo "        ";
                $context["formfield"] = $this->getAttribute($this->getAttribute((isset($context["field"]) ? $context["field"] : null), "vars", array()), "prototype", array());
                // line 1024
                echo "    ";
            } else {
                // line 1025
                echo "        ";
                $context["formfield"] = (isset($context["field"]) ? $context["field"] : null);
                // line 1026
                echo "    ";
            }
            // line 1027
            echo "
    ";
            // line 1028
            $context["standAloneObject"] = false;
            // line 1029
            echo "    ";
            if (twig_in_filter("standalone", (isset($context["cycle"]) ? $context["cycle"] : null))) {
                // line 1030
                echo "        ";
                $context["standAloneObject"] = true;
                // line 1031
                echo "    ";
            }
            // line 1032
            echo "
    <div class=\"user-collection-holder well ";
            // line 1033
            echo twig_escape_filter($this->env, (isset($context["classname"]) ? $context["classname"] : null), "html", null, true);
            echo "\">

        ";
            // line 1035
            if (((isset($context["cycle"]) ? $context["cycle"] : null) != "show_user")) {
                // line 1036
                echo "            <div class=\"text-right\">
                ";
                // line 1038
                echo "                <button type=\"button\" class=\"btn btn-default btn-sm  confirm-delete-with-expired\" onClick=\"removeExistingObject(this,'";
                echo twig_escape_filter($this->env, (isset($context["classname"]) ? $context["classname"] : null), "html", null, true);
                echo "')\" ><span class=\"glyphicon glyphicon-remove\"></span></button>
            </div>
        ";
            }
            // line 1041
            echo "
        ";
            // line 1042
            echo $context["formmacros"]->getfield($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "id", array()));
            echo "

        ";
            // line 1044
            echo $context["formmacros"]->getfield($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "institution", array()));
            echo "

        ";
            // line 1046
            if ($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "name", array(), "any", true, true)) {
                // line 1047
                echo "            ";
                echo $context["formmacros"]->getfield($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "name", array()));
                echo "
        ";
            }
            // line 1049
            echo "
        ";
            // line 1051
            echo "        ";
            if ($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "piDummy", array(), "any", true, true)) {
                // line 1052
                echo "            ";
                echo $context["formmacros"]->getcheckbox($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "piDummy", array()));
                echo "
        ";
            }
            // line 1054
            echo "        ";
            // line 1055
            echo "            ";
            // line 1056
            echo "        ";
            // line 1057
            echo "            ";
            // line 1058
            echo "                ";
            // line 1059
            echo "            ";
            // line 1060
            echo "        ";
            // line 1061
            echo "
        ";
            // line 1062
            if ($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "foundedDate", array(), "any", true, true)) {
                // line 1063
                echo "            ";
                echo $context["formmacros"]->getfieldDateLabel($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "foundedDate", array()), "allow-future-date no-datepicker-events");
                echo "
        ";
            }
            // line 1065
            echo "        ";
            if ($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "dissolvedDate", array(), "any", true, true)) {
                // line 1066
                echo "            ";
                echo $context["formmacros"]->getfieldDateLabel_notempty($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "dissolvedDate", array()), (isset($context["cycle"]) ? $context["cycle"] : null), "allow-future-date no-datepicker-events");
                echo "
        ";
            }
            // line 1068
            echo "
        ";
            // line 1070
            echo "        ";
            if ($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "commentDummy", array(), "any", true, true)) {
                // line 1071
                echo "            ";
                echo $context["formmacros"]->getfield($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "commentDummy", array()));
                echo "
        ";
            }
            // line 1073
            echo "        ";
            // line 1074
            echo "            ";
            // line 1075
            echo "        ";
            // line 1076
            echo "            ";
            // line 1077
            echo "                ";
            // line 1078
            echo "            ";
            // line 1079
            echo "        ";
            // line 1080
            echo "
        ";
            // line 1081
            echo $context["formmacros"]->getfield($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "location", array()));
            echo "
        ";
            // line 1082
            echo $context["usermacros"]->getemailPhoneField_notempty($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "weblink", array()), (isset($context["cycle"]) ? $context["cycle"] : null), "link", "");
            echo "

    </div>

    ";
            // line 1087
            echo "    ";
            if ((isset($context["standAloneObject"]) ? $context["standAloneObject"] : null)) {
                // line 1088
                echo "        ";
                echo $context["usermacros"]->getlistProperties((isset($context["formfield"]) ? $context["formfield"] : null));
                echo "
    ";
            }
            // line 1090
            echo "
    ";
            // line 1091
            $this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "setRendered", array());
            // line 1092
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

    // line 1096
    public function getgrantObject($__field__ = null, $__cycle__ = null, $__classname__ = null, $__prototype__ = null, $__sitename__ = null, $__user__ = null, ...$__varargs__)
    {
        $context = $this->env->mergeGlobals(array(
            "field" => $__field__,
            "cycle" => $__cycle__,
            "classname" => $__classname__,
            "prototype" => $__prototype__,
            "sitename" => $__sitename__,
            "user" => $__user__,
            "varargs" => $__varargs__,
        ));

        $blocks = array();

        ob_start();
        try {
            // line 1097
            echo "    ";
            $context["formmacros"] = $this->loadTemplate("OlegOrderformBundle::Default/formmacros.html.twig", "OlegUserdirectoryBundle::Default/usermacros.html.twig", 1097);
            // line 1098
            echo "    ";
            $context["usermacros"] = $this;
            // line 1099
            echo "
    ";
            // line 1100
            if (((isset($context["prototype"]) ? $context["prototype"] : null) == "prototype")) {
                // line 1101
                echo "        ";
                $context["formfield"] = $this->getAttribute($this->getAttribute((isset($context["field"]) ? $context["field"] : null), "vars", array()), "prototype", array());
                // line 1102
                echo "    ";
            } else {
                // line 1103
                echo "        ";
                $context["formfield"] = (isset($context["field"]) ? $context["field"] : null);
                // line 1104
                echo "    ";
            }
            // line 1105
            echo "
    ";
            // line 1106
            $context["standAloneObject"] = false;
            // line 1107
            echo "    ";
            if (twig_in_filter("standalone", (isset($context["cycle"]) ? $context["cycle"] : null))) {
                // line 1108
                echo "        ";
                $context["standAloneObject"] = true;
                // line 1109
                echo "    ";
            }
            // line 1110
            echo "
    ";
            // line 1111
            if (twig_in_filter("show", (isset($context["cycle"]) ? $context["cycle"] : null))) {
                // line 1112
                echo "        ";
                $context["showFlag"] = true;
                // line 1113
                echo "    ";
            } else {
                // line 1114
                echo "        ";
                $context["showFlag"] = false;
                // line 1115
                echo "    ";
            }
            // line 1116
            echo "
    ";
            // line 1117
            $context["dropzoneInit"] = "inactive";
            // line 1118
            echo "    ";
            if ((isset($context["standAloneObject"]) ? $context["standAloneObject"] : null)) {
                // line 1119
                echo "        ";
                if ((isset($context["showFlag"]) ? $context["showFlag"] : null)) {
                    // line 1120
                    echo "            ";
                    $context["dropzoneInit"] = "inactive";
                    // line 1121
                    echo "        ";
                } else {
                    // line 1122
                    echo "            ";
                    $context["dropzoneInit"] = "active";
                    // line 1123
                    echo "        ";
                }
                // line 1124
                echo "    ";
            }
            // line 1125
            echo "
    <div class=\"user-collection-holder well ";
            // line 1126
            echo twig_escape_filter($this->env, (isset($context["classname"]) ? $context["classname"] : null), "html", null, true);
            echo "\">

        ";
            // line 1128
            if (( !(isset($context["standAloneObject"]) ? $context["standAloneObject"] : null) &&  !(isset($context["showFlag"]) ? $context["showFlag"] : null))) {
                // line 1129
                echo "            <div class=\"text-right\">
                ";
                // line 1131
                echo "                <button type=\"button\" class=\"btn btn-default btn-sm  confirm-delete-with-expired\" onClick=\"removeExistingObject(this,'";
                echo twig_escape_filter($this->env, (isset($context["classname"]) ? $context["classname"] : null), "html", null, true);
                echo "')\" ><span class=\"glyphicon glyphicon-remove\"></span></button>
            </div>
        ";
            }
            // line 1134
            echo "
        ";
            // line 1135
            echo $context["formmacros"]->getfield($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "id", array()));
            echo "

        ";
            // line 1137
            echo $context["formmacros"]->getfield($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "name", array()));
            echo "
        ";
            // line 1138
            echo $context["formmacros"]->getfield($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "sourceOrganization", array()));
            echo "
        ";
            // line 1139
            echo $context["formmacros"]->getfield($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "grantid", array()));
            echo "

        ";
            // line 1141
            if ( !(isset($context["showFlag"]) ? $context["showFlag"] : null)) {
                // line 1142
                echo "            ";
                echo $context["formmacros"]->getfield_notempty($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "grantLink", array()), (isset($context["cycle"]) ? $context["cycle"] : null));
                echo "
        ";
            } else {
                // line 1144
                echo "            ";
                if (($this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "vars", array()), "value", array()) && $this->getAttribute($this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "vars", array()), "value", array()), "grantLink", array()))) {
                    // line 1145
                    echo "                ";
                    echo $context["usermacros"]->gethtmlLink($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "vars", array()), "value", array()), "grantLink", array()), "Link to a page with more information:");
                    echo "
            ";
                }
                // line 1147
                echo "            ";
                $this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "grantLink", array()), "setRendered", array());
                // line 1148
                echo "        ";
            }
            // line 1149
            echo "
        ";
            // line 1150
            if ($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "effortDummy", array(), "any", true, true)) {
                // line 1151
                echo "            ";
                echo $context["formmacros"]->getfield($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "effortDummy", array()));
                echo "
        ";
            }
            // line 1153
            echo "
        ";
            // line 1154
            echo $context["formmacros"]->getfieldDateLabel($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "startDate", array()), "allow-future-date no-datepicker-events");
            echo "
        ";
            // line 1155
            echo $context["formmacros"]->getfieldDateLabel_notempty($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "endDate", array()), (isset($context["cycle"]) ? $context["cycle"] : null), "allow-future-date no-datepicker-events");
            echo "

        ";
            // line 1157
            echo $context["formmacros"]->getfield($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "currentYearDirectCost", array()));
            echo "
        ";
            // line 1158
            echo $context["formmacros"]->getfield($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "currentYearIndirectCost", array()));
            echo "
        ";
            // line 1159
            echo $context["formmacros"]->getfield($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "totalCurrentYearCost", array()));
            echo "
        ";
            // line 1160
            echo $context["formmacros"]->getfield($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "amount", array()));
            echo "
        ";
            // line 1161
            echo $context["formmacros"]->getfield($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "amountLabSpace", array()));
            echo "

        ";
            // line 1163
            if ($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "commentDummy", array(), "any", true, true)) {
                // line 1164
                echo "            ";
                echo $context["formmacros"]->getfield($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "commentDummy", array()));
                echo "
        ";
            }
            // line 1166
            echo "
        ";
            // line 1168
            echo "        ";
            // line 1169
            echo "        ";
            // line 1170
            echo "        ";
            if ($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "attachmentContainer", array(), "any", true, true)) {
                // line 1171
                echo "            ";
                $context["count"] = 0;
                // line 1172
                echo "            ";
                // line 1173
                echo "            ";
                $context['_parent'] = $context;
                $context['_seq'] = twig_ensure_traversable($this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "attachmentContainer", array()), "documentContainers", array()));
                foreach ($context['_seq'] as $context["_key"] => $context["documentContainer"]) {
                    // line 1174
                    echo "                ";
                    $context["count"] = ((isset($context["count"]) ? $context["count"] : null) + 1);
                    // line 1175
                    echo "                ";
                    $context["uniqueId"] = (((isset($context["count"]) ? $context["count"] : null) . "-") . twig_date_format_filter($this->env, "now", "mdYHisu"));
                    // line 1176
                    echo "                ";
                    echo $context["formmacros"]->getfieldDocumentContainer($context["documentContainer"], (isset($context["cycle"]) ? $context["cycle"] : null), ("grant" . (isset($context["uniqueId"]) ? $context["uniqueId"] : null)), "", 20, (isset($context["dropzoneInit"]) ? $context["dropzoneInit"] : null));
                    echo "
            ";
                }
                $_parent = $context['_parent'];
                unset($context['_seq'], $context['_iterated'], $context['_key'], $context['documentContainer'], $context['_parent'], $context['loop']);
                $context = array_intersect_key($context, $_parent) + $_parent;
                // line 1178
                echo "
            ";
                // line 1179
                if (((isset($context["prototype"]) ? $context["prototype"] : null) == "prototype")) {
                    // line 1180
                    echo "                ";
                    $context["uniqueId"] = ("1-" . twig_date_format_filter($this->env, "now", "mdYHisu"));
                    // line 1181
                    echo "                ";
                    echo $context["formmacros"]->getfieldDocumentContainer($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "attachmentContainer", array()), "documentContainers", array()), "vars", array()), "prototype", array()), (isset($context["cycle"]) ? $context["cycle"] : null), ("grant" . (isset($context["uniqueId"]) ? $context["uniqueId"] : null)), "", 20, (isset($context["dropzoneInit"]) ? $context["dropzoneInit"] : null));
                    echo "
            ";
                }
                // line 1183
                echo "
        ";
            }
            // line 1185
            echo "
    </div>

    ";
            // line 1189
            echo "    ";
            if ((isset($context["standAloneObject"]) ? $context["standAloneObject"] : null)) {
                // line 1190
                echo "        ";
                echo $context["usermacros"]->getlistProperties((isset($context["formfield"]) ? $context["formfield"] : null));
                echo "
    ";
            }
            // line 1192
            echo "
    ";
            // line 1193
            $this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "setRendered", array());
            // line 1194
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

    // line 1197
    public function getpublicationObject($__field__ = null, $__cycle__ = null, $__classname__ = null, $__prototype__ = null, $__sitename__ = null, $__entity__ = null, ...$__varargs__)
    {
        $context = $this->env->mergeGlobals(array(
            "field" => $__field__,
            "cycle" => $__cycle__,
            "classname" => $__classname__,
            "prototype" => $__prototype__,
            "sitename" => $__sitename__,
            "entity" => $__entity__,
            "varargs" => $__varargs__,
        ));

        $blocks = array();

        ob_start();
        try {
            // line 1198
            echo "    ";
            $context["formmacros"] = $this->loadTemplate("OlegOrderformBundle::Default/formmacros.html.twig", "OlegUserdirectoryBundle::Default/usermacros.html.twig", 1198);
            // line 1199
            echo "    ";
            $context["usermacros"] = $this;
            // line 1200
            echo "
    ";
            // line 1201
            if (((isset($context["prototype"]) ? $context["prototype"] : null) == "prototype")) {
                // line 1202
                echo "        ";
                $context["formfield"] = $this->getAttribute($this->getAttribute((isset($context["field"]) ? $context["field"] : null), "vars", array()), "prototype", array());
                // line 1203
                echo "    ";
            } else {
                // line 1204
                echo "        ";
                $context["formfield"] = (isset($context["field"]) ? $context["field"] : null);
                // line 1205
                echo "    ";
            }
            // line 1206
            echo "
    ";
            // line 1207
            if (twig_in_filter("show", (isset($context["cycle"]) ? $context["cycle"] : null))) {
                // line 1208
                echo "        ";
                $context["showFlag"] = true;
                // line 1209
                echo "    ";
            } else {
                // line 1210
                echo "        ";
                $context["showFlag"] = false;
                // line 1211
                echo "    ";
            }
            // line 1212
            echo "
    ";
            // line 1213
            $context["wellclass"] = "";
            // line 1214
            echo "
    <div class=\"user-collection-holder alert ";
            // line 1215
            echo twig_escape_filter($this->env, (isset($context["classname"]) ? $context["classname"] : null), "html", null, true);
            echo " ";
            echo twig_escape_filter($this->env, (isset($context["wellclass"]) ? $context["wellclass"] : null), "html", null, true);
            echo "\">

        ";
            // line 1217
            if (((isset($context["cycle"]) ? $context["cycle"] : null) != "show_user")) {
                // line 1218
                echo "            <div class=\"text-right\">
                <button type=\"button\" class=\"btn btn-default btn-sm\" onClick=\"removeExistingObject(this,'";
                // line 1219
                echo twig_escape_filter($this->env, (isset($context["classname"]) ? $context["classname"] : null), "html", null, true);
                echo "')\" >
                    <span class=\"glyphicon glyphicon-remove\"></span>
                </button>
            </div>
        ";
            }
            // line 1224
            echo "
        ";
            // line 1225
            echo $context["formmacros"]->getfield($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "id", array()));
            echo "

        ";
            // line 1227
            echo $context["formmacros"]->getfield($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "citation", array()));
            echo "
        ";
            // line 1228
            echo $context["formmacros"]->getfield($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "pubmedid", array()));
            echo "

        ";
            // line 1230
            if ( !(isset($context["showFlag"]) ? $context["showFlag"] : null)) {
                // line 1231
                echo "            ";
                echo $context["formmacros"]->getfield_notempty($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "link", array()), (isset($context["cycle"]) ? $context["cycle"] : null));
                echo "
        ";
            } else {
                // line 1233
                echo "            ";
                if (($this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "vars", array()), "value", array()) && $this->getAttribute($this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "vars", array()), "value", array()), "link", array()))) {
                    // line 1234
                    echo "                ";
                    echo $context["usermacros"]->gethtmlLink($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "vars", array()), "value", array()), "link", array()), "PubMed or Relevant Link:");
                    echo "
            ";
                }
                // line 1236
                echo "            ";
                $this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "link", array()), "setRendered", array());
                // line 1237
                echo "        ";
            }
            // line 1238
            echo "
        ";
            // line 1239
            echo $context["formmacros"]->getfield($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "importance", array()));
            echo "
        ";
            // line 1240
            echo $context["formmacros"]->getfieldDateLabel($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "publicationDate", array()), "datepicker-only-month-year");
            echo "

        ";
            // line 1242
            if ($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "updatedate", array(), "any", true, true)) {
                // line 1243
                echo "            ";
                echo $context["formmacros"]->getfieldDateLabel($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "updatedate", array()), "allow-future-date");
                echo "
        ";
            }
            // line 1245
            echo "
    </div>

    ";
            // line 1248
            $this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "setRendered", array());
            // line 1249
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

    // line 1253
    public function getbookObject($__field__ = null, $__cycle__ = null, $__classname__ = null, $__prototype__ = null, $__sitename__ = null, $__entity__ = null, ...$__varargs__)
    {
        $context = $this->env->mergeGlobals(array(
            "field" => $__field__,
            "cycle" => $__cycle__,
            "classname" => $__classname__,
            "prototype" => $__prototype__,
            "sitename" => $__sitename__,
            "entity" => $__entity__,
            "varargs" => $__varargs__,
        ));

        $blocks = array();

        ob_start();
        try {
            // line 1254
            echo "    ";
            $context["formmacros"] = $this->loadTemplate("OlegOrderformBundle::Default/formmacros.html.twig", "OlegUserdirectoryBundle::Default/usermacros.html.twig", 1254);
            // line 1255
            echo "    ";
            $context["usermacros"] = $this;
            // line 1256
            echo "
    ";
            // line 1257
            if (((isset($context["prototype"]) ? $context["prototype"] : null) == "prototype")) {
                // line 1258
                echo "        ";
                $context["formfield"] = $this->getAttribute($this->getAttribute((isset($context["field"]) ? $context["field"] : null), "vars", array()), "prototype", array());
                // line 1259
                echo "    ";
            } else {
                // line 1260
                echo "        ";
                $context["formfield"] = (isset($context["field"]) ? $context["field"] : null);
                // line 1261
                echo "    ";
            }
            // line 1262
            echo "
    ";
            // line 1263
            if (twig_in_filter("show", (isset($context["cycle"]) ? $context["cycle"] : null))) {
                // line 1264
                echo "        ";
                $context["showFlag"] = true;
                // line 1265
                echo "    ";
            } else {
                // line 1266
                echo "        ";
                $context["showFlag"] = false;
                // line 1267
                echo "    ";
            }
            // line 1268
            echo "
    ";
            // line 1269
            $context["wellclass"] = "";
            // line 1270
            echo "
    <div class=\"user-collection-holder alert ";
            // line 1271
            echo twig_escape_filter($this->env, (isset($context["classname"]) ? $context["classname"] : null), "html", null, true);
            echo " ";
            echo twig_escape_filter($this->env, (isset($context["wellclass"]) ? $context["wellclass"] : null), "html", null, true);
            echo "\">

        ";
            // line 1273
            if (((isset($context["cycle"]) ? $context["cycle"] : null) != "show_user")) {
                // line 1274
                echo "            <div class=\"text-right\">
                <button type=\"button\" class=\"btn btn-default btn-sm\" onClick=\"removeExistingObject(this,'";
                // line 1275
                echo twig_escape_filter($this->env, (isset($context["classname"]) ? $context["classname"] : null), "html", null, true);
                echo "')\" >
                    <span class=\"glyphicon glyphicon-remove\"></span>
                </button>
            </div>
        ";
            }
            // line 1280
            echo "
        ";
            // line 1281
            echo $context["formmacros"]->getfield($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "id", array()));
            echo "

        ";
            // line 1283
            echo $context["formmacros"]->getfield($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "citation", array()));
            echo "
        ";
            // line 1284
            echo $context["formmacros"]->getfield($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "isbn", array()));
            echo "

        ";
            // line 1286
            if ( !(isset($context["showFlag"]) ? $context["showFlag"] : null)) {
                // line 1287
                echo "            ";
                echo $context["formmacros"]->getfield_notempty($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "link", array()), (isset($context["cycle"]) ? $context["cycle"] : null));
                echo "
        ";
            } else {
                // line 1289
                echo "            ";
                if (($this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "vars", array()), "value", array()) && $this->getAttribute($this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "vars", array()), "value", array()), "link", array()))) {
                    // line 1290
                    echo "                ";
                    echo $context["usermacros"]->gethtmlLink($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "vars", array()), "value", array()), "link", array()), "Relevant Link:");
                    echo "
            ";
                }
                // line 1292
                echo "            ";
                $this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "link", array()), "setRendered", array());
                // line 1293
                echo "        ";
            }
            // line 1294
            echo "
        ";
            // line 1295
            echo $context["formmacros"]->getfield($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "authorshipRole", array()));
            echo "
        ";
            // line 1296
            echo $context["formmacros"]->getfield($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "comment", array()));
            echo "

        ";
            // line 1298
            echo $context["formmacros"]->getfieldDateLabel($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "publicationDate", array()), "datepicker-only-month-year");
            echo "

        ";
            // line 1300
            if ($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "updatedate", array(), "any", true, true)) {
                // line 1301
                echo "            ";
                echo $context["formmacros"]->getfieldDateLabel($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "updatedate", array()), "allow-future-date");
                echo "
        ";
            }
            // line 1303
            echo "
    </div>

    ";
            // line 1306
            $this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "setRendered", array());
            // line 1307
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

    // line 1310
    public function getlectureObject($__field__ = null, $__cycle__ = null, $__classname__ = null, $__prototype__ = null, $__sitename__ = null, $__entity__ = null, ...$__varargs__)
    {
        $context = $this->env->mergeGlobals(array(
            "field" => $__field__,
            "cycle" => $__cycle__,
            "classname" => $__classname__,
            "prototype" => $__prototype__,
            "sitename" => $__sitename__,
            "entity" => $__entity__,
            "varargs" => $__varargs__,
        ));

        $blocks = array();

        ob_start();
        try {
            // line 1311
            echo "    ";
            $context["formmacros"] = $this->loadTemplate("OlegOrderformBundle::Default/formmacros.html.twig", "OlegUserdirectoryBundle::Default/usermacros.html.twig", 1311);
            // line 1312
            echo "    ";
            $context["usermacros"] = $this;
            // line 1313
            echo "
    ";
            // line 1314
            if (((isset($context["prototype"]) ? $context["prototype"] : null) == "prototype")) {
                // line 1315
                echo "        ";
                $context["formfield"] = $this->getAttribute($this->getAttribute((isset($context["field"]) ? $context["field"] : null), "vars", array()), "prototype", array());
                // line 1316
                echo "    ";
            } else {
                // line 1317
                echo "        ";
                $context["formfield"] = (isset($context["field"]) ? $context["field"] : null);
                // line 1318
                echo "    ";
            }
            // line 1319
            echo "
    ";
            // line 1321
            echo "        ";
            // line 1322
            echo "    ";
            // line 1323
            echo "        ";
            // line 1324
            echo "    ";
            // line 1325
            echo "
    ";
            // line 1326
            $context["wellclass"] = "";
            // line 1327
            echo "
    <div class=\"user-collection-holder alert ";
            // line 1328
            echo twig_escape_filter($this->env, (isset($context["classname"]) ? $context["classname"] : null), "html", null, true);
            echo " ";
            echo twig_escape_filter($this->env, (isset($context["wellclass"]) ? $context["wellclass"] : null), "html", null, true);
            echo "\">

        ";
            // line 1330
            if (((isset($context["cycle"]) ? $context["cycle"] : null) != "show_user")) {
                // line 1331
                echo "            <div class=\"text-right\">
                <button type=\"button\" class=\"btn btn-default btn-sm\" onClick=\"removeExistingObject(this,'";
                // line 1332
                echo twig_escape_filter($this->env, (isset($context["classname"]) ? $context["classname"] : null), "html", null, true);
                echo "')\" >
                    <span class=\"glyphicon glyphicon-remove\"></span>
                </button>
            </div>
        ";
            }
            // line 1337
            echo "
        ";
            // line 1338
            echo $context["formmacros"]->getfield($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "id", array()));
            echo "

        ";
            // line 1340
            echo $context["formmacros"]->getfield($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "title", array()));
            echo "
        ";
            // line 1341
            echo $context["formmacros"]->getfieldDateLabel($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "lectureDate", array()), "allow-future-date");
            echo "
        ";
            // line 1342
            echo $context["formmacros"]->getfield($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "organization", array()));
            echo "
        ";
            // line 1343
            echo $context["formmacros"]->getfield($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "city", array()));
            echo "
        ";
            // line 1344
            echo $context["formmacros"]->getfield($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "state", array()));
            echo "
        ";
            // line 1345
            echo $context["formmacros"]->getfield($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "country", array()));
            echo "
        ";
            // line 1346
            echo $context["formmacros"]->getfield($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "importance", array()));
            echo "

    </div>

    ";
            // line 1350
            $this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "setRendered", array());
            // line 1351
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

    // line 1354
    public function getfellowshipApplicationObject($__field__ = null, $__cycle__ = null, $__classname__ = null, $__prototype__ = null, $__sitename__ = null, $__entity__ = null, ...$__varargs__)
    {
        $context = $this->env->mergeGlobals(array(
            "field" => $__field__,
            "cycle" => $__cycle__,
            "classname" => $__classname__,
            "prototype" => $__prototype__,
            "sitename" => $__sitename__,
            "entity" => $__entity__,
            "varargs" => $__varargs__,
        ));

        $blocks = array();

        ob_start();
        try {
            // line 1355
            echo "    ";
            $context["formmacros"] = $this->loadTemplate("OlegOrderformBundle::Default/formmacros.html.twig", "OlegUserdirectoryBundle::Default/usermacros.html.twig", 1355);
            // line 1356
            echo "    ";
            $context["usermacros"] = $this;
            // line 1357
            echo "
    ";
            // line 1358
            if (((isset($context["prototype"]) ? $context["prototype"] : null) == "prototype")) {
                // line 1359
                echo "        ";
                $context["formfield"] = $this->getAttribute($this->getAttribute((isset($context["field"]) ? $context["field"] : null), "vars", array()), "prototype", array());
                // line 1360
                echo "    ";
            } else {
                // line 1361
                echo "        ";
                $context["formfield"] = (isset($context["field"]) ? $context["field"] : null);
                // line 1362
                echo "    ";
            }
            // line 1363
            echo "
    ";
            // line 1364
            if (twig_in_filter("show", (isset($context["cycle"]) ? $context["cycle"] : null))) {
                // line 1365
                echo "        ";
                $context["showFlag"] = true;
                // line 1366
                echo "    ";
            } else {
                // line 1367
                echo "        ";
                $context["showFlag"] = false;
                // line 1368
                echo "    ";
            }
            // line 1369
            echo "
    ";
            // line 1370
            $context["wellclass"] = "";
            // line 1371
            echo "
    <div class=\"user-collection-holder alert ";
            // line 1372
            echo twig_escape_filter($this->env, (isset($context["classname"]) ? $context["classname"] : null), "html", null, true);
            echo " ";
            echo twig_escape_filter($this->env, (isset($context["wellclass"]) ? $context["wellclass"] : null), "html", null, true);
            echo "\">

        ";
            // line 1374
            if (((isset($context["cycle"]) ? $context["cycle"] : null) != "show_user")) {
                // line 1375
                echo "            <div class=\"text-right\">
                <button type=\"button\" class=\"btn btn-default btn-sm\" onClick=\"removeExistingObject(this,'";
                // line 1376
                echo twig_escape_filter($this->env, (isset($context["classname"]) ? $context["classname"] : null), "html", null, true);
                echo "')\" >
                    <span class=\"glyphicon glyphicon-remove\"></span>
                </button>
            </div>
        ";
            }
            // line 1381
            echo "
        ";
            // line 1382
            echo $context["formmacros"]->getfield($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "fellowshipSubspecialty", array()));
            echo "
        ";
            // line 1383
            echo $context["formmacros"]->getfieldDateLabel($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "timestamp", array()), "allow-future-date");
            echo "
        ";
            // line 1384
            echo $context["formmacros"]->getfieldDateLabel($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "startDate", array()), "allow-future-date");
            echo "
        ";
            // line 1385
            echo $context["formmacros"]->getfieldDateLabel($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "endDate", array()), "allow-future-date");
            echo "

    </div>

    ";
            // line 1389
            $this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "setRendered", array());
            // line 1390
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

    // line 1396
    public function gethtmlLink($__linkvalue__ = null, $__label__ = null, ...$__varargs__)
    {
        $context = $this->env->mergeGlobals(array(
            "linkvalue" => $__linkvalue__,
            "label" => $__label__,
            "varargs" => $__varargs__,
        ));

        $blocks = array();

        ob_start();
        try {
            // line 1397
            echo "    ";
            $context["formmacros"] = $this->loadTemplate("OlegOrderformBundle::Default/formmacros.html.twig", "OlegUserdirectoryBundle::Default/usermacros.html.twig", 1397);
            // line 1398
            echo "    ";
            if (twig_in_filter("http", (isset($context["linkvalue"]) ? $context["linkvalue"] : null))) {
                // line 1399
                echo "        ";
                $context["href"] = (isset($context["linkvalue"]) ? $context["linkvalue"] : null);
                // line 1400
                echo "    ";
            } else {
                // line 1401
                echo "        ";
                $context["href"] = ("http://" . (isset($context["linkvalue"]) ? $context["linkvalue"] : null));
                // line 1402
                echo "    ";
            }
            // line 1403
            echo "    ";
            $context["hreflink"] = (((("<a href=\"" . (isset($context["href"]) ? $context["href"] : null)) . "\">") . (isset($context["linkvalue"]) ? $context["linkvalue"] : null)) . "</a>");
            // line 1404
            echo "    ";
            echo $context["formmacros"]->getsimplefield((isset($context["label"]) ? $context["label"] : null), (isset($context["hreflink"]) ? $context["hreflink"] : null), "", "disabled");
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

    // line 1409
    public function getuserTeamAjax($__userid__ = null, $__teamType__ = null, $__title__ = null, $__sitename__ = null, ...$__varargs__)
    {
        $context = $this->env->mergeGlobals(array(
            "userid" => $__userid__,
            "teamType" => $__teamType__,
            "title" => $__title__,
            "sitename" => $__sitename__,
            "varargs" => $__varargs__,
        ));

        $blocks = array();

        ob_start();
        try {
            // line 1410
            echo "    ";
            // line 1411
            echo "    ";
            // line 1412
            echo "    ";
            $context["teamurl"] = $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath(((isset($context["sitename"]) ? $context["sitename"] : null) . "_my_team"), array("userid" => (isset($context["userid"]) ? $context["userid"] : null), "teamType" => (isset($context["teamType"]) ? $context["teamType"] : null)));
            // line 1413
            echo "    ";
            // line 1414
            echo "    ";
            // line 1415
            echo "    ";
            // line 1416
            echo "    <div id=\"userTeamAjaxDetails\">

        <div id=\"userTeamAjaxDetailsPanel-";
            // line 1418
            echo twig_escape_filter($this->env, (isset($context["userid"]) ? $context["userid"] : null), "html", null, true);
            echo "\" class=\"panel panel-primary\">
            <div class=\"panel-heading\">
                <h4 class=\"panel-title text-left\">
                    <a
                            id=\"userTeamAjaxDetailsLink-";
            // line 1422
            echo twig_escape_filter($this->env, (isset($context["userid"]) ? $context["userid"] : null), "html", null, true);
            echo "\"
                            data-toggle=\"collapse\"
                            href=\"#userTeamAjax\"
                            onclick=\"userTeamTwigMacro('";
            // line 1425
            echo twig_escape_filter($this->env, (isset($context["teamurl"]) ? $context["teamurl"] : null), "html", null, true);
            echo "','userTeamAjaxDetailsLink-";
            echo twig_escape_filter($this->env, (isset($context["userid"]) ? $context["userid"] : null), "html", null, true);
            echo "','userTeamAjaxDetails');\"
                    >
                        ";
            // line 1427
            echo twig_escape_filter($this->env, (isset($context["title"]) ? $context["title"] : null), "html", null, true);
            echo "
                    </a>
                </h4>
            </div>
            <div id=\"userTeamAjax\" class=\"panel-collapse collapse in\">
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

    // line 1437
    public function getuserTeam($__user__ = null, $__type__ = null, $__sitename__ = null, $__postData__ = null, $__collapsein__ = null, ...$__varargs__)
    {
        $context = $this->env->mergeGlobals(array(
            "user" => $__user__,
            "type" => $__type__,
            "sitename" => $__sitename__,
            "postData" => $__postData__,
            "collapsein" => $__collapsein__,
            "varargs" => $__varargs__,
        ));

        $blocks = array();

        ob_start();
        try {
            // line 1438
            echo "
    ";
            // line 1439
            $context["usermacros"] = $this->loadTemplate("OlegUserdirectoryBundle::Default/usermacros.html.twig", "OlegUserdirectoryBundle::Default/usermacros.html.twig", 1439);
            // line 1440
            echo "
    ";
            // line 1442
            echo "
    ";
            // line 1443
            if (((isset($context["type"]) ? $context["type"] : null) == "home")) {
                // line 1444
                echo "        ";
                $context["boxnamePrefix"] = "My ";
                // line 1445
                echo "    ";
            } else {
                // line 1446
                echo "        ";
                $context["boxnamePrefix"] = "";
                // line 1447
                echo "    ";
            }
            // line 1448
            echo "
    ";
            // line 1449
            $context["threshold"] = 50;
            // line 1450
            echo "
    ";
            // line 1451
            $context["myreportsShow"] = false;
            // line 1452
            echo "    ";
            $context["mygroupsShow"] = false;
            // line 1453
            echo "    ";
            $context["myservicesShow"] = false;
            // line 1454
            echo "    ";
            $context["mydepartmentsShow"] = false;
            // line 1455
            echo "    ";
            $context["mydivisionsShow"] = false;
            // line 1456
            echo "    ";
            $context["mylabsShow"] = false;
            // line 1457
            echo "    ";
            $context["myassistancesShow"] = false;
            // line 1458
            echo "
    ";
            // line 1460
            echo "    ";
            $context["myreports"] = Symfony\Bridge\Twig\Extension\HttpKernelExtension::controller("OlegUserdirectoryBundle:User:myObjects", array("postData" => (isset($context["postData"]) ? $context["postData"] : null), "subjectUserId" => $this->getAttribute((isset($context["user"]) ? $context["user"] : null), "id", array()), "tablename" => "myreports", "id" => null, "name" => null));
            // line 1461
            echo "    ";
            if ((twig_length_filter($this->env, $this->env->getRuntime('Symfony\Bridge\Twig\Extension\HttpKernelRuntime')->renderFragment((isset($context["myreports"]) ? $context["myreports"] : null))) > (isset($context["threshold"]) ? $context["threshold"] : null))) {
                // line 1462
                echo "        ";
                $context["myreportsShow"] = true;
                echo "     
    ";
            }
            // line 1464
            echo "
    ";
            // line 1466
            echo "    ";
            $context["myassistants"] = Symfony\Bridge\Twig\Extension\HttpKernelExtension::controller("OlegUserdirectoryBundle:User:myObjects", array("postData" => (isset($context["postData"]) ? $context["postData"] : null), "subjectUserId" => $this->getAttribute((isset($context["user"]) ? $context["user"] : null), "id", array()), "tablename" => "assistances", "id" => null, "name" => null));
            // line 1467
            echo "    ";
            if ((twig_length_filter($this->env, $this->env->getRuntime('Symfony\Bridge\Twig\Extension\HttpKernelRuntime')->renderFragment((isset($context["myassistants"]) ? $context["myassistants"] : null))) > (isset($context["threshold"]) ? $context["threshold"] : null))) {
                // line 1468
                echo "        ";
                $context["myassistancesShow"] = true;
                echo " 
    ";
            }
            // line 1470
            echo "
    ";
            // line 1472
            echo "    ";
            $context["titleBosses"] = $this->getAttribute((isset($context["user"]) ? $context["user"] : null), "getBosses", array(), "method");
            // line 1473
            echo "    ";
            $context["mygroupsArr"] = array();
            // line 1474
            echo "    ";
            $context["myTitleBossesArr"] = array();
            // line 1475
            echo "    ";
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable((isset($context["titleBosses"]) ? $context["titleBosses"] : null));
            $context['loop'] = array(
              'parent' => $context['_parent'],
              'index0' => 0,
              'index'  => 1,
              'first'  => true,
            );
            if (is_array($context['_seq']) || (is_object($context['_seq']) && $context['_seq'] instanceof Countable)) {
                $length = count($context['_seq']);
                $context['loop']['revindex0'] = $length - 1;
                $context['loop']['revindex'] = $length;
                $context['loop']['length'] = $length;
                $context['loop']['last'] = 1 === $length;
            }
            foreach ($context['_seq'] as $context["_key"] => $context["title"]) {
                // line 1476
                echo "        ";
                $context['_parent'] = $context;
                $context['_seq'] = twig_ensure_traversable($this->getAttribute($context["title"], "bosses", array(), "array"));
                $context['loop'] = array(
                  'parent' => $context['_parent'],
                  'index0' => 0,
                  'index'  => 1,
                  'first'  => true,
                );
                if (is_array($context['_seq']) || (is_object($context['_seq']) && $context['_seq'] instanceof Countable)) {
                    $length = count($context['_seq']);
                    $context['loop']['revindex0'] = $length - 1;
                    $context['loop']['revindex'] = $length;
                    $context['loop']['length'] = $length;
                    $context['loop']['last'] = 1 === $length;
                }
                foreach ($context['_seq'] as $context["_key"] => $context["myboss"]) {
                    // line 1477
                    echo "            ";
                    if (($context["myboss"] && $this->getAttribute($context["myboss"], "getId", array(), "method"))) {
                        // line 1478
                        echo "                ";
                        $context["table"] = Symfony\Bridge\Twig\Extension\HttpKernelExtension::controller("OlegUserdirectoryBundle:User:myObjects", array("postData" => (isset($context["postData"]) ? $context["postData"] : null), "subjectUserId" => $this->getAttribute((isset($context["user"]) ? $context["user"] : null), "id", array()), "tablename" => "myboss", "id" => $this->getAttribute($context["myboss"], "getId", array(), "method"), "name" => $context["myboss"]));
                        // line 1479
                        echo "                ";
                        $context["element"] = array("table" => (isset($context["table"]) ? $context["table"] : null), "element" => $context["myboss"]);
                        // line 1480
                        echo "                ";
                        $context["mygroupsArr"] = twig_array_merge((isset($context["mygroupsArr"]) ? $context["mygroupsArr"] : null), array($this->getAttribute($context["loop"], "index0", array()) => (isset($context["element"]) ? $context["element"] : null)));
                        // line 1481
                        echo "                ";
                        if ((twig_length_filter($this->env, $this->env->getRuntime('Symfony\Bridge\Twig\Extension\HttpKernelRuntime')->renderFragment((isset($context["table"]) ? $context["table"] : null))) > (isset($context["threshold"]) ? $context["threshold"] : null))) {
                            // line 1482
                            echo "                    ";
                            $context["mygroupsShow"] = true;
                            echo " 
                ";
                        }
                        // line 1484
                        echo "            ";
                    }
                    // line 1485
                    echo "        ";
                    ++$context['loop']['index0'];
                    ++$context['loop']['index'];
                    $context['loop']['first'] = false;
                    if (isset($context['loop']['length'])) {
                        --$context['loop']['revindex0'];
                        --$context['loop']['revindex'];
                        $context['loop']['last'] = 0 === $context['loop']['revindex0'];
                    }
                }
                $_parent = $context['_parent'];
                unset($context['_seq'], $context['_iterated'], $context['_key'], $context['myboss'], $context['_parent'], $context['loop']);
                $context = array_intersect_key($context, $_parent) + $_parent;
                // line 1486
                echo "        ";
                $context["element"] = array("title" => $this->getAttribute($context["title"], "titleobject", array(), "array"), "bosses" => $this->getAttribute($context["title"], "bosses", array(), "array"));
                // line 1487
                echo "        ";
                $context["myTitleBossesArr"] = twig_array_merge((isset($context["myTitleBossesArr"]) ? $context["myTitleBossesArr"] : null), array($this->getAttribute($context["loop"], "index0", array()) => (isset($context["element"]) ? $context["element"] : null)));
                // line 1488
                echo "    ";
                ++$context['loop']['index0'];
                ++$context['loop']['index'];
                $context['loop']['first'] = false;
                if (isset($context['loop']['length'])) {
                    --$context['loop']['revindex0'];
                    --$context['loop']['revindex'];
                    $context['loop']['last'] = 0 === $context['loop']['revindex0'];
                }
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['title'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            echo "  

    ";
            // line 1491
            echo "    ";
            // line 1492
            echo "    ";
            // line 1493
            echo "    ";
            // line 1494
            echo "        ";
            // line 1495
            echo "            ";
            // line 1496
            echo "            ";
            // line 1497
            echo "            ";
            // line 1498
            echo "            ";
            // line 1499
            echo "            ";
            // line 1500
            echo "            ";
            // line 1501
            echo "            ";
            // line 1502
            echo "                ";
            // line 1503
            echo "            ";
            // line 1504
            echo "        ";
            // line 1505
            echo "    ";
            // line 1506
            echo "
    ";
            // line 1508
            echo "    ";
            $context["labs"] = $this->getAttribute((isset($context["user"]) ? $context["user"] : null), "getResearchLabs", array(), "method");
            // line 1509
            echo "    ";
            $context["mylabsArr"] = array();
            // line 1510
            echo "    ";
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable((isset($context["labs"]) ? $context["labs"] : null));
            $context['loop'] = array(
              'parent' => $context['_parent'],
              'index0' => 0,
              'index'  => 1,
              'first'  => true,
            );
            if (is_array($context['_seq']) || (is_object($context['_seq']) && $context['_seq'] instanceof Countable)) {
                $length = count($context['_seq']);
                $context['loop']['revindex0'] = $length - 1;
                $context['loop']['revindex'] = $length;
                $context['loop']['length'] = $length;
                $context['loop']['last'] = 1 === $length;
            }
            foreach ($context['_seq'] as $context["_key"] => $context["lab"]) {
                // line 1511
                echo "        ";
                if (($context["lab"] && $this->getAttribute($context["lab"], "getId", array(), "method"))) {
                    // line 1512
                    echo "            ";
                    $context["table"] = Symfony\Bridge\Twig\Extension\HttpKernelExtension::controller("OlegUserdirectoryBundle:User:myObjects", array("postData" => (isset($context["postData"]) ? $context["postData"] : null), "tablename" => "researchlabs", "id" => $this->getAttribute($context["lab"], "getId", array(), "method"), "name" => $context["lab"]));
                    // line 1513
                    echo "            ";
                    $context["element"] = array("table" => (isset($context["table"]) ? $context["table"] : null), "element" => $context["lab"]);
                    // line 1514
                    echo "            ";
                    $context["mylabsArr"] = twig_array_merge((isset($context["mylabsArr"]) ? $context["mylabsArr"] : null), array($this->getAttribute($context["loop"], "index0", array()) => (isset($context["element"]) ? $context["element"] : null)));
                    // line 1515
                    echo "            ";
                    if ((twig_length_filter($this->env, $this->env->getRuntime('Symfony\Bridge\Twig\Extension\HttpKernelRuntime')->renderFragment((isset($context["table"]) ? $context["table"] : null))) > (isset($context["threshold"]) ? $context["threshold"] : null))) {
                        // line 1516
                        echo "                ";
                        $context["mylabsShow"] = true;
                        // line 1517
                        echo "            ";
                    }
                    // line 1518
                    echo "        ";
                }
                // line 1519
                echo "    ";
                ++$context['loop']['index0'];
                ++$context['loop']['index'];
                $context['loop']['first'] = false;
                if (isset($context['loop']['length'])) {
                    --$context['loop']['revindex0'];
                    --$context['loop']['revindex'];
                    $context['loop']['last'] = 0 === $context['loop']['revindex0'];
                }
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['lab'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 1520
            echo "
    ";
            // line 1522
            echo "    ";
            // line 1523
            echo "    ";
            // line 1524
            echo "
    ";
            // line 1525
            echo " ";
            // line 1526
            echo "    ";
            if ((((((isset($context["myreportsShow"]) ? $context["myreportsShow"] : null) || (isset($context["mygroupsShow"]) ? $context["mygroupsShow"] : null)) || (isset($context["myservicesShow"]) ? $context["myservicesShow"] : null)) || (isset($context["mylabsShow"]) ? $context["mylabsShow"] : null)) || (isset($context["myassistancesShow"]) ? $context["myassistancesShow"] : null))) {
                // line 1527
                echo "        <div class=\"panel panel-primary\">
        <div class=\"panel-heading\">
            <h4 class=\"panel-title\">

                    <div class=\"row\">
                        <div class=\"col-xs-6\" align=\"left\">
                            <a data-toggle=\"collapse\" href=\"#myteam\" style=\"color:#fff;\">";
                // line 1533
                echo twig_escape_filter($this->env, (isset($context["boxnamePrefix"]) ? $context["boxnamePrefix"] : null), "html", null, true);
                echo "Team</a>
                        </div>
                        <div class=\"col-xs-6\" align=\"right\">
                            <button type=\"button\" class=\"btn btn-default btn-xs panel-collapse-btn\" onClick=\"collapseAll(document.getElementById('myteam'))\" >Collapse All</button>
                            <button type=\"button\" class=\"btn btn-default btn-xs panel-collapse-btn\" onClick=\"extendAll(document.getElementById('myteam'))\" >Expand All</button>
                        </div>
                    </div>

            </h4>
        </div>
        <div id=\"myteam\" class=\"panel-collapse collapse ";
                // line 1543
                echo twig_escape_filter($this->env, (isset($context["collapsein"]) ? $context["collapsein"] : null), "html", null, true);
                echo "\">
            <div id=\"myteam-panel-body\" class=\"panel-body\">

                ";
                // line 1547
                echo "                ";
                if ((isset($context["myassistancesShow"]) ? $context["myassistancesShow"] : null)) {
                    // line 1548
                    echo "                    <div class=\"panel panel-info\">
                        <div class=\"panel-heading\">
                            <h4 class=\"panel-title\">
                                <a data-toggle=\"collapse\" href=\"#myassistances\">
                                    ";
                    // line 1552
                    echo twig_escape_filter($this->env, (isset($context["boxnamePrefix"]) ? $context["boxnamePrefix"] : null), "html", null, true);
                    echo "Assistant(s)
                                </a>
                            </h4>
                        </div>
                        <div id=\"myassistances\" class=\"panel-collapse collapse ";
                    // line 1556
                    echo twig_escape_filter($this->env, (isset($context["collapsein"]) ? $context["collapsein"] : null), "html", null, true);
                    echo "\">
                            <div class=\"panel-body\">
                                ";
                    // line 1558
                    echo $this->env->getRuntime('Symfony\Bridge\Twig\Extension\HttpKernelRuntime')->renderFragment((isset($context["myassistants"]) ? $context["myassistants"] : null));
                    echo "
                            </div>
                        </div>
                    </div>
                ";
                }
                // line 1563
                echo "
                ";
                // line 1565
                echo "                ";
                if ((isset($context["myreportsShow"]) ? $context["myreportsShow"] : null)) {
                    // line 1566
                    echo "                    <div class=\"panel panel-info\">
                        <div class=\"panel-heading\">
                            <h4 class=\"panel-title\">
                                <a data-toggle=\"collapse\" href=\"#myreports\">
                                    ";
                    // line 1570
                    echo twig_escape_filter($this->env, (isset($context["boxnamePrefix"]) ? $context["boxnamePrefix"] : null), "html", null, true);
                    echo "Report(s)
                                </a>
                            </h4>
                        </div>
                        <div id=\"myreports\" class=\"panel-collapse collapse ";
                    // line 1574
                    echo twig_escape_filter($this->env, (isset($context["collapsein"]) ? $context["collapsein"] : null), "html", null, true);
                    echo "\">
                            <div class=\"panel-body\">
                                ";
                    // line 1576
                    echo $this->env->getRuntime('Symfony\Bridge\Twig\Extension\HttpKernelRuntime')->renderFragment((isset($context["myreports"]) ? $context["myreports"] : null));
                    echo "
                            </div>
                        </div>
                    </div>
                ";
                }
                // line 1581
                echo "
                ";
                // line 1583
                echo "                ";
                if (array_key_exists("mygroupsArr", $context)) {
                    // line 1584
                    echo "                    ";
                    $context['_parent'] = $context;
                    $context['_seq'] = twig_ensure_traversable((isset($context["mygroupsArr"]) ? $context["mygroupsArr"] : null));
                    foreach ($context['_seq'] as $context["_key"] => $context["mygroup"]) {
                        // line 1585
                        echo "
                        ";
                        // line 1586
                        if ((twig_length_filter($this->env, $this->env->getRuntime('Symfony\Bridge\Twig\Extension\HttpKernelRuntime')->renderFragment($this->getAttribute($context["mygroup"], "table", array(), "array"))) > (isset($context["threshold"]) ? $context["threshold"] : null))) {
                            // line 1587
                            echo "                            <div class=\"panel panel-info\">
                                <div class=\"panel-heading\">
                                    <h4 class=\"panel-title\">
                                        <a data-toggle=\"collapse\" href=\"#mygroups-";
                            // line 1590
                            echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute($context["mygroup"], "element", array(), "array"), "getId", array(), "method"), "html", null, true);
                            echo "\">
                                            ";
                            // line 1591
                            echo twig_escape_filter($this->env, (isset($context["boxnamePrefix"]) ? $context["boxnamePrefix"] : null), "html", null, true);
                            echo "Group of <a href=\"";
                            echo twig_escape_filter($this->env, $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath(((isset($context["sitename"]) ? $context["sitename"] : null) . "_showuser"), array("id" => $this->getAttribute($this->getAttribute($context["mygroup"], "element", array(), "array"), "getId", array(), "method"))), "html", null, true);
                            echo "\">";
                            echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute($context["mygroup"], "element", array(), "array"), "getUsernameOptimal", array(), "method"), "html", null, true);
                            echo "</a>
                                        </a>
                                    </h4>
                                </div>
                                <div id=\"mygroups-";
                            // line 1595
                            echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute($context["mygroup"], "element", array(), "array"), "getId", array(), "method"), "html", null, true);
                            echo "\" class=\"panel-collapse collapse ";
                            echo twig_escape_filter($this->env, (isset($context["collapsein"]) ? $context["collapsein"] : null), "html", null, true);
                            echo "\">
                                    <div class=\"panel-body\">
                                        ";
                            // line 1597
                            echo $this->env->getRuntime('Symfony\Bridge\Twig\Extension\HttpKernelRuntime')->renderFragment($this->getAttribute($context["mygroup"], "table", array(), "array"));
                            echo "
                                    </div>
                                </div>
                            </div>
                        ";
                        }
                        // line 1602
                        echo "
                    ";
                    }
                    $_parent = $context['_parent'];
                    unset($context['_seq'], $context['_iterated'], $context['_key'], $context['mygroup'], $context['_parent'], $context['loop']);
                    $context = array_intersect_key($context, $_parent) + $_parent;
                    // line 1604
                    echo "                ";
                }
                // line 1605
                echo "

                ";
                // line 1610
                echo "                ";
                if (((array_key_exists("myTitleBossesArr", $context) && (twig_length_filter($this->env, (isset($context["myTitleBossesArr"]) ? $context["myTitleBossesArr"] : null)) > 0)) && ((isset($context["type"]) ? $context["type"] : null) == "profile"))) {
                    // line 1611
                    echo "                    ";
                    $context['_parent'] = $context;
                    $context['_seq'] = twig_ensure_traversable((isset($context["myTitleBossesArr"]) ? $context["myTitleBossesArr"] : null));
                    foreach ($context['_seq'] as $context["_key"] => $context["titleBosses"]) {
                        // line 1612
                        echo "
                        ";
                        // line 1613
                        $context["bossArr"] = array();
                        // line 1614
                        echo "                        ";
                        $context["bossidsArr"] = array();
                        // line 1615
                        echo "                        ";
                        $context['_parent'] = $context;
                        $context['_seq'] = twig_ensure_traversable($this->getAttribute($context["titleBosses"], "bosses", array(), "array"));
                        foreach ($context['_seq'] as $context["_key"] => $context["boss"]) {
                            // line 1616
                            echo "                            ";
                            $context["bossHtml"] = (((("<a href=\"" . $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath(((isset($context["sitename"]) ? $context["sitename"] : null) . "_showuser"), array("id" => $this->getAttribute($context["boss"], "getId", array(), "method")))) . "\">") . $this->getAttribute($context["boss"], "getUsernameOptimal", array(), "method")) . "</a>");
                            // line 1617
                            echo "                            ";
                            $context["bossArr"] = twig_array_merge((isset($context["bossArr"]) ? $context["bossArr"] : null), array(0 => (isset($context["bossHtml"]) ? $context["bossHtml"] : null)));
                            // line 1618
                            echo "                            ";
                            $context["bossidsArr"] = twig_array_merge((isset($context["bossidsArr"]) ? $context["bossidsArr"] : null), array(0 => $this->getAttribute($context["boss"], "getId", array())));
                            // line 1619
                            echo "                        ";
                        }
                        $_parent = $context['_parent'];
                        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['boss'], $context['_parent'], $context['loop']);
                        $context = array_intersect_key($context, $_parent) + $_parent;
                        // line 1620
                        echo "
                        ";
                        // line 1621
                        $context["mybosses"] = Symfony\Bridge\Twig\Extension\HttpKernelExtension::controller("OlegUserdirectoryBundle:User:myObjects", array("postData" => (isset($context["postData"]) ? $context["postData"] : null), "tablename" => "mybosses", "id" => (isset($context["bossidsArr"]) ? $context["bossidsArr"] : null), "name" => null));
                        // line 1622
                        echo "
                        ";
                        // line 1623
                        if ((twig_length_filter($this->env, $this->env->getRuntime('Symfony\Bridge\Twig\Extension\HttpKernelRuntime')->renderFragment((isset($context["mybosses"]) ? $context["mybosses"] : null))) > (isset($context["threshold"]) ? $context["threshold"] : null))) {
                            // line 1624
                            echo "
                            <div class=\"panel panel-info\">
                                <div class=\"panel-heading\">
                                    <h4 class=\"panel-title\">
                                        <a data-toggle=\"collapse\" href=\"#mytitlebosses-";
                            // line 1628
                            echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute($context["titleBosses"], "title", array(), "array"), "getId", array(), "method"), "html", null, true);
                            echo "\"
                                                >As a ";
                            // line 1629
                            echo twig_escape_filter($this->env, $this->getAttribute((isset($context["user"]) ? $context["user"] : null), "getTitleAndNameByTitle", array(0 => $this->getAttribute($context["titleBosses"], "title", array(), "array")), "method"), "html", null, true);
                            echo " reports to:</a> ";
                            echo twig_join_filter((isset($context["bossArr"]) ? $context["bossArr"] : null), ",");
                            echo "

                                    </h4>
                                </div>
                                <div id=\"mytitlebosses-";
                            // line 1633
                            echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute($context["titleBosses"], "title", array(), "array"), "getId", array(), "method"), "html", null, true);
                            echo "\" class=\"panel-collapse collapse ";
                            echo twig_escape_filter($this->env, (isset($context["collapsein"]) ? $context["collapsein"] : null), "html", null, true);
                            echo "\">
                                    <div class=\"panel-body\">
                                        ";
                            // line 1635
                            echo $this->env->getRuntime('Symfony\Bridge\Twig\Extension\HttpKernelRuntime')->renderFragment((isset($context["mybosses"]) ? $context["mybosses"] : null));
                            echo "
                                    </div>
                                </div>
                            </div>

                        ";
                        }
                        // line 1641
                        echo "
                    ";
                    }
                    $_parent = $context['_parent'];
                    unset($context['_seq'], $context['_iterated'], $context['_key'], $context['titleBosses'], $context['_parent'], $context['loop']);
                    $context = array_intersect_key($context, $_parent) + $_parent;
                    // line 1643
                    echo "                ";
                }
                // line 1644
                echo "

                ";
                // line 1647
                echo "                ";
                if (array_key_exists("mydepartmentsArr", $context)) {
                    // line 1648
                    echo "                    ";
                    $context['_parent'] = $context;
                    $context['_seq'] = twig_ensure_traversable((isset($context["mydepartmentsArr"]) ? $context["mydepartmentsArr"] : null));
                    foreach ($context['_seq'] as $context["_key"] => $context["mydepartment"]) {
                        // line 1649
                        echo "
                        ";
                        // line 1650
                        if ((twig_length_filter($this->env, $this->env->getRuntime('Symfony\Bridge\Twig\Extension\HttpKernelRuntime')->renderFragment($this->getAttribute($context["mydepartment"], "table", array(), "array"))) > (isset($context["threshold"]) ? $context["threshold"] : null))) {
                            // line 1651
                            echo "                            <div class=\"panel panel-info\">
                                <div class=\"panel-heading\">
                                    <h4 class=\"panel-title\">
                                        <a data-toggle=\"collapse\" href=\"#mydepartments-";
                            // line 1654
                            echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute($context["mydepartment"], "element", array(), "array"), "getId", array(), "method"), "html", null, true);
                            echo "\">
                                            ";
                            // line 1655
                            echo twig_escape_filter($this->env, $this->getAttribute($context["mydepartment"], "element", array(), "array"), "html", null, true);
                            echo " Department
                                        </a>
                                    </h4>
                                </div>
                                <div id=\"mydepartments-";
                            // line 1659
                            echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute($context["mydepartment"], "element", array(), "array"), "getId", array(), "method"), "html", null, true);
                            echo "\" class=\"panel-collapse collapse ";
                            echo twig_escape_filter($this->env, (isset($context["collapsein"]) ? $context["collapsein"] : null), "html", null, true);
                            echo "\">
                                    <div class=\"panel-body\">
                                        ";
                            // line 1661
                            echo $this->env->getRuntime('Symfony\Bridge\Twig\Extension\HttpKernelRuntime')->renderFragment($this->getAttribute($context["mydepartment"], "table", array(), "array"));
                            echo "
                                    </div>
                                </div>
                            </div>
                        ";
                        }
                        // line 1666
                        echo "
                    ";
                    }
                    $_parent = $context['_parent'];
                    unset($context['_seq'], $context['_iterated'], $context['_key'], $context['mydepartment'], $context['_parent'], $context['loop']);
                    $context = array_intersect_key($context, $_parent) + $_parent;
                    // line 1668
                    echo "                ";
                }
                // line 1669
                echo "
                ";
                // line 1671
                echo "                ";
                if (array_key_exists("mydivisionsArr", $context)) {
                    // line 1672
                    echo "                    ";
                    $context['_parent'] = $context;
                    $context['_seq'] = twig_ensure_traversable((isset($context["mydivisionsArr"]) ? $context["mydivisionsArr"] : null));
                    foreach ($context['_seq'] as $context["_key"] => $context["mydivision"]) {
                        // line 1673
                        echo "
                        ";
                        // line 1674
                        if ((twig_length_filter($this->env, $this->env->getRuntime('Symfony\Bridge\Twig\Extension\HttpKernelRuntime')->renderFragment($this->getAttribute($context["mydivision"], "table", array(), "array"))) > (isset($context["threshold"]) ? $context["threshold"] : null))) {
                            // line 1675
                            echo "                            <div class=\"panel panel-info\">
                                <div class=\"panel-heading\">
                                    <h4 class=\"panel-title\">
                                        <a data-toggle=\"collapse\" href=\"#mydivisions-";
                            // line 1678
                            echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute($context["mydivision"], "element", array(), "array"), "getId", array(), "method"), "html", null, true);
                            echo "\">
                                            ";
                            // line 1679
                            echo twig_escape_filter($this->env, $this->getAttribute($context["mydivision"], "element", array(), "array"), "html", null, true);
                            echo " Division
                                        </a>
                                    </h4>
                                </div>
                                <div id=\"mydivisions-";
                            // line 1683
                            echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute($context["mydivision"], "element", array(), "array"), "getId", array(), "method"), "html", null, true);
                            echo "\" class=\"panel-collapse collapse ";
                            echo twig_escape_filter($this->env, (isset($context["collapsein"]) ? $context["collapsein"] : null), "html", null, true);
                            echo "\">
                                    <div class=\"panel-body\">
                                        ";
                            // line 1685
                            echo $this->env->getRuntime('Symfony\Bridge\Twig\Extension\HttpKernelRuntime')->renderFragment($this->getAttribute($context["mydivision"], "table", array(), "array"));
                            echo "
                                    </div>
                                </div>
                            </div>
                        ";
                        }
                        // line 1690
                        echo "
                    ";
                    }
                    $_parent = $context['_parent'];
                    unset($context['_seq'], $context['_iterated'], $context['_key'], $context['mydivision'], $context['_parent'], $context['loop']);
                    $context = array_intersect_key($context, $_parent) + $_parent;
                    // line 1692
                    echo "                ";
                }
                // line 1693
                echo "

                ";
                // line 1696
                echo "                ";
                if (array_key_exists("myservicesArr", $context)) {
                    // line 1697
                    echo "                    ";
                    $context['_parent'] = $context;
                    $context['_seq'] = twig_ensure_traversable((isset($context["myservicesArr"]) ? $context["myservicesArr"] : null));
                    foreach ($context['_seq'] as $context["_key"] => $context["myservice"]) {
                        // line 1698
                        echo "
                        ";
                        // line 1699
                        if ((twig_length_filter($this->env, $this->env->getRuntime('Symfony\Bridge\Twig\Extension\HttpKernelRuntime')->renderFragment($this->getAttribute($context["myservice"], "table", array(), "array"))) > (isset($context["threshold"]) ? $context["threshold"] : null))) {
                            // line 1700
                            echo "                            <div class=\"panel panel-info\">
                                <div class=\"panel-heading\">
                                    <h4 class=\"panel-title\">
                                        <a data-toggle=\"collapse\" href=\"#myservices-";
                            // line 1703
                            echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute($context["myservice"], "element", array(), "array"), "getId", array(), "method"), "html", null, true);
                            echo "\">
                                            ";
                            // line 1704
                            echo twig_escape_filter($this->env, $this->getAttribute($context["myservice"], "element", array(), "array"), "html", null, true);
                            echo " Service
                                        </a>
                                    </h4>
                                </div>
                                <div id=\"myservices-";
                            // line 1708
                            echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute($context["myservice"], "element", array(), "array"), "getId", array(), "method"), "html", null, true);
                            echo "\" class=\"panel-collapse collapse ";
                            echo twig_escape_filter($this->env, (isset($context["collapsein"]) ? $context["collapsein"] : null), "html", null, true);
                            echo "\">
                                    <div class=\"panel-body\">
                                        ";
                            // line 1710
                            echo $this->env->getRuntime('Symfony\Bridge\Twig\Extension\HttpKernelRuntime')->renderFragment($this->getAttribute($context["myservice"], "table", array(), "array"));
                            echo "
                                    </div>
                                </div>
                            </div>
                        ";
                        }
                        // line 1715
                        echo "
                    ";
                    }
                    $_parent = $context['_parent'];
                    unset($context['_seq'], $context['_iterated'], $context['_key'], $context['myservice'], $context['_parent'], $context['loop']);
                    $context = array_intersect_key($context, $_parent) + $_parent;
                    // line 1717
                    echo "                ";
                }
                // line 1718
                echo "

                ";
                // line 1721
                echo "                ";
                if (array_key_exists("mylabsArr", $context)) {
                    // line 1722
                    echo "                    ";
                    $context['_parent'] = $context;
                    $context['_seq'] = twig_ensure_traversable((isset($context["mylabsArr"]) ? $context["mylabsArr"] : null));
                    foreach ($context['_seq'] as $context["_key"] => $context["mylab"]) {
                        // line 1723
                        echo "
                        ";
                        // line 1724
                        if ((twig_length_filter($this->env, $this->env->getRuntime('Symfony\Bridge\Twig\Extension\HttpKernelRuntime')->renderFragment($this->getAttribute($context["mylab"], "table", array(), "array"))) > (isset($context["threshold"]) ? $context["threshold"] : null))) {
                            // line 1725
                            echo "                            <div class=\"panel panel-info\">
                                <div class=\"panel-heading\">
                                    <h4 class=\"panel-title\">
                                        <a data-toggle=\"collapse\" href=\"#mylabs-";
                            // line 1728
                            echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute($context["mylab"], "element", array(), "array"), "getId", array(), "method"), "html", null, true);
                            echo "\">
                                            ";
                            // line 1729
                            echo twig_escape_filter($this->env, $this->getAttribute($context["mylab"], "element", array(), "array"), "html", null, true);
                            echo " Research Lab
                                        </a>
                                    </h4>
                                </div>
                                <div id=\"mylabs-";
                            // line 1733
                            echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute($context["mylab"], "element", array(), "array"), "getId", array(), "method"), "html", null, true);
                            echo "\" class=\"panel-collapse collapse ";
                            echo twig_escape_filter($this->env, (isset($context["collapsein"]) ? $context["collapsein"] : null), "html", null, true);
                            echo "\">
                                    <div class=\"panel-body\">
                                        ";
                            // line 1735
                            echo $this->env->getRuntime('Symfony\Bridge\Twig\Extension\HttpKernelRuntime')->renderFragment($this->getAttribute($context["mylab"], "table", array(), "array"));
                            echo "
                                    </div>
                                </div>
                            </div>
                        ";
                        }
                        // line 1740
                        echo "
                    ";
                    }
                    $_parent = $context['_parent'];
                    unset($context['_seq'], $context['_iterated'], $context['_key'], $context['mylab'], $context['_parent'], $context['loop']);
                    $context = array_intersect_key($context, $_parent) + $_parent;
                    // line 1742
                    echo "                ";
                }
                // line 1743
                echo "

            </div> ";
                // line 1746
                echo "        </div>";
                // line 1747
                echo "        </div>";
                // line 1748
                echo "    ";
            } else {
                // line 1749
                echo "        <div class=\"panel panel-primary\">
            <div class=\"panel-heading\">
                <h4 class=\"panel-title\">
                    <div class=\"row\">
                        <div class=\"col-xs-6\" align=\"left\">
                            <a data-toggle=\"collapse\" href=\"#myteam\" style=\"color:#fff;\">";
                // line 1754
                echo twig_escape_filter($this->env, (isset($context["boxnamePrefix"]) ? $context["boxnamePrefix"] : null), "html", null, true);
                echo "Team</a>
                        </div>
                        <div class=\"col-xs-6\" align=\"right\">
                            ";
                // line 1758
                echo "                            ";
                // line 1759
                echo "                        </div>
                    </div>
                </h4>
            </div>
            <div id=\"myteam\" class=\"panel-collapse collapse in\">
                <div id=\"myteam-panel-body\" class=\"panel-body\">
                    No team members found
                </div> ";
                // line 1767
                echo "            </div>";
                // line 1768
                echo "        </div>";
                // line 1769
                echo "    ";
            }
            // line 1770
            echo "
    ";
            // line 1772
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

    // line 1777
    public function getuserWrapperAjax($__userid__ = null, $__sitename__ = null, $__cycle__ = null, ...$__varargs__)
    {
        $context = $this->env->mergeGlobals(array(
            "userid" => $__userid__,
            "sitename" => $__sitename__,
            "cycle" => $__cycle__,
            "varargs" => $__varargs__,
        ));

        $blocks = array();

        ob_start();
        try {
            // line 1778
            echo "    ";
            // line 1779
            echo "    <div id=\"userWrapperAjaxDetails\">
        <div class=\"panel panel-primary\">
            <div class=\"panel-heading\">
                <h4 class=\"panel-title text-left\">
                    <a
                        id=\"userWrapperAjaxBtn\"
                        data-toggle=\"collapse\"
                        href=\"#userWrapperAjax\"
                        onclick=\"userWrapperAjax(";
            // line 1787
            echo twig_escape_filter($this->env, (isset($context["userid"]) ? $context["userid"] : null), "html", null, true);
            echo ",'userWrapperAjaxBtn', 'userWrapperAjaxDetails', '";
            echo twig_escape_filter($this->env, (isset($context["cycle"]) ? $context["cycle"] : null), "html", null, true);
            echo "');\"
                    >
                        Associated Identities
                    </a>

                </h4>
            </div>
            <div id=\"userWrapper\" class=\"panel-collapse collapse\">
                <div class=\"panel-body\">

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

    // line 1804
    public function getauditLog($__user__ = null, $__type__ = null, $__sitename__ = null, $__postData__ = null, $__collapsein__ = null, ...$__varargs__)
    {
        $context = $this->env->mergeGlobals(array(
            "user" => $__user__,
            "type" => $__type__,
            "sitename" => $__sitename__,
            "postData" => $__postData__,
            "collapsein" => $__collapsein__,
            "varargs" => $__varargs__,
        ));

        $blocks = array();

        ob_start();
        try {
            // line 1805
            echo "
    ";
            // line 1806
            $context["usermacros"] = $this->loadTemplate("OlegUserdirectoryBundle::Default/usermacros.html.twig", "OlegUserdirectoryBundle::Default/usermacros.html.twig", 1806);
            // line 1807
            echo "
    ";
            // line 1809
            echo "    ";
            if (((isset($context["postData"]) ? $context["postData"] : null) && $this->getAttribute((isset($context["postData"]) ? $context["postData"] : null), "sort", array(), "array", true, true))) {
                // line 1810
                echo "        ";
                if ((twig_in_filter("logger.", $this->getAttribute((isset($context["postData"]) ? $context["postData"] : null), "sort", array(), "array")) || twig_in_filter("eventType.", $this->getAttribute((isset($context["postData"]) ? $context["postData"] : null), "sort", array(), "array")))) {
                    // line 1811
                    echo "            ";
                    $context["collapsein"] = "in";
                    // line 1812
                    echo "        ";
                }
                // line 1813
                echo "    ";
            }
            // line 1814
            echo "
    ";
            // line 1815
            $context["auditlog"] = Symfony\Bridge\Twig\Extension\HttpKernelExtension::controller("OlegUserdirectoryBundle:Logger:getAuditLog", array("postData" => (isset($context["postData"]) ? $context["postData"] : null), "id" => $this->getAttribute((isset($context["user"]) ? $context["user"] : null), "id", array()), "onlyheader" => true));
            // line 1816
            echo "
    ";
            // line 1818
            echo "    ";
            // line 1819
            echo "    ";
            if ((array_key_exists("auditlog", $context) && (isset($context["auditlog"]) ? $context["auditlog"] : null))) {
                // line 1820
                echo "        <div class=\"panel panel-info\">
            <div class=\"panel-heading\">
                <h4 class=\"panel-title\">
                    <a data-toggle=\"collapse\" href=\"#auditlog\">
                        Audit Log
                    </a>
                </h4>
            </div>
            <div id=\"auditlog\" class=\"panel-collapse collapse ";
                // line 1828
                echo twig_escape_filter($this->env, (isset($context["collapsein"]) ? $context["collapsein"] : null), "html", null, true);
                echo "\">
                <div class=\"panel-body\">
                    ";
                // line 1830
                echo $this->env->getRuntime('Symfony\Bridge\Twig\Extension\HttpKernelRuntime')->renderFragment((isset($context["auditlog"]) ? $context["auditlog"] : null));
                echo "

                    <br>

                    <div class=\"text-center col-xs-12\">
                        <a href=\"";
                // line 1835
                echo twig_escape_filter($this->env, $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath(((isset($context["sitename"]) ? $context["sitename"] : null) . "_logger_user_all"), array("id" => $this->getAttribute((isset($context["user"]) ? $context["user"] : null), "id", array()))), "html", null, true);
                echo "\" target=\"_blank\">Event Log</a>
                    </div>

                </div>
            </div>
        </div>
    ";
            }
            // line 1842
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

    // line 1846
    public function getgetLoggerTreeByType($__logger__ = null, $__treeType__ = null, $__loggeraction__ = null, ...$__varargs__)
    {
        $context = $this->env->mergeGlobals(array(
            "logger" => $__logger__,
            "treeType" => $__treeType__,
            "loggeraction" => $__loggeraction__,
            "varargs" => $__varargs__,
        ));

        $blocks = array();

        ob_start();
        try {
            // line 1847
            echo "
    ";
            // line 1848
            $context["formmacros"] = $this->loadTemplate("OlegOrderformBundle::Default/formmacros.html.twig", "OlegUserdirectoryBundle::Default/usermacros.html.twig", 1848);
            // line 1849
            echo "
    ";
            // line 1850
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable($this->getAttribute((isset($context["logger"]) ? $context["logger"] : null), "getInstitutionTreesByType", array(0 => (isset($context["treeType"]) ? $context["treeType"] : null)), "method"));
            foreach ($context['_seq'] as $context["_key"] => $context["tree"]) {
                // line 1851
                echo "        <div class=\"well well-sm\">
            <div class=\"text-center col-xs-12\">
                ";
                // line 1853
                if (((isset($context["treeType"]) ? $context["treeType"] : null) == "AdministrativeTitle")) {
                    // line 1854
                    echo "                    Administrative Title
                ";
                } elseif ((                // line 1855
(isset($context["treeType"]) ? $context["treeType"] : null) == "AppointmentTitle")) {
                    // line 1856
                    echo "                    Appointment Title
                ";
                } elseif ((                // line 1857
(isset($context["treeType"]) ? $context["treeType"] : null) == "MedicalTitle")) {
                    // line 1858
                    echo "                    Medical Title
                ";
                } else {
                    // line 1860
                    echo "                    ";
                    echo twig_escape_filter($this->env, (isset($context["treeType"]) ? $context["treeType"] : null), "html", null, true);
                    echo "
                ";
                }
                // line 1862
                echo "            </div>
            ";
                // line 1863
                if ($this->getAttribute($context["tree"], "institution", array())) {
                    // line 1864
                    echo "                ";
                    echo $context["formmacros"]->getsimplefield(((isset($context["loggeraction"]) ? $context["loggeraction"] : null) . " by user from Institution:"), $this->getAttribute($this->getAttribute($context["tree"], "institution", array()), "name", array()), "input", "disabled");
                    echo "
            ";
                }
                // line 1866
                echo "            ";
                // line 1867
                echo "                ";
                // line 1868
                echo "            ";
                // line 1869
                echo "            ";
                // line 1870
                echo "                ";
                // line 1871
                echo "            ";
                // line 1872
                echo "            ";
                // line 1873
                echo "                ";
                // line 1874
                echo "            ";
                // line 1875
                echo "        </div>
    ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['tree'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 1877
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

    // line 1881
    public function getavatarForm($__user__ = null, $__cycle__ = null, ...$__varargs__)
    {
        $context = $this->env->mergeGlobals(array(
            "user" => $__user__,
            "cycle" => $__cycle__,
            "varargs" => $__varargs__,
        ));

        $blocks = array();

        ob_start();
        try {
            // line 1882
            echo "
    ";
            // line 1883
            $context["usermacros"] = $this->loadTemplate("OlegUserdirectoryBundle::Default/usermacros.html.twig", "OlegUserdirectoryBundle::Default/usermacros.html.twig", 1883);
            // line 1884
            echo "
    ";
            // line 1885
            if (twig_in_filter("show", (isset($context["cycle"]) ? $context["cycle"] : null))) {
                // line 1886
                echo "        ";
                $context["showFlag"] = true;
                // line 1887
                echo "    ";
            } else {
                // line 1888
                echo "        ";
                $context["showFlag"] = false;
                // line 1889
                echo "    ";
            }
            // line 1890
            echo "
    ";
            // line 1891
            if ((isset($context["showFlag"]) ? $context["showFlag"] : null)) {
                // line 1892
                echo "
        <div class=\"avatar-view-clean-default\">
            ";
                // line 1894
                if ($this->getAttribute((isset($context["user"]) ? $context["user"] : null), "avatar", array())) {
                    // line 1895
                    echo "                ";
                    echo $context["usermacros"]->getshowDocumentAsImage($this->getAttribute((isset($context["user"]) ? $context["user"] : null), "avatar", array()), "Avatar", "");
                    echo "
            ";
                } else {
                    // line 1897
                    echo "                ";
                    $context["avatarImage"] = $this->env->getExtension('Symfony\Bridge\Twig\Extension\AssetExtension')->getAssetUrl("bundles/oleguserdirectory/fengyuanchen-image-cropper/img/Placeholder-User-Glyph-Icon.png");
                    // line 1898
                    echo "                <img src=\"";
                    echo twig_escape_filter($this->env, (isset($context["avatarImage"]) ? $context["avatarImage"] : null), "html", null, true);
                    echo "\" alt=\"Avatar\" height=\"126\" width=\"116\">
            ";
                }
                // line 1900
                echo "
        </div>

    ";
            } else {
                // line 1904
                echo "
        <div class=\"container\" id=\"crop-avatar\">

            <!-- Current avatar -->
            <div class=\"avatar-view\" title=\"Change the photo\" style=\"height:126px; width:116px;\">
                ";
                // line 1910
                echo "                ";
                if ($this->getAttribute((isset($context["user"]) ? $context["user"] : null), "avatar", array())) {
                    // line 1911
                    echo "                    ";
                    echo $context["usermacros"]->getshowDocumentAsImage($this->getAttribute((isset($context["user"]) ? $context["user"] : null), "avatar", array()), "Avatar", "", "objectFull");
                    echo "
                ";
                } else {
                    // line 1913
                    echo "                    ";
                    $context["avatarImage"] = $this->env->getExtension('Symfony\Bridge\Twig\Extension\AssetExtension')->getAssetUrl("bundles/oleguserdirectory/fengyuanchen-image-cropper/img/Placeholder-User-Glyph-Icon.png");
                    // line 1914
                    echo "                    <img src=\"";
                    echo twig_escape_filter($this->env, (isset($context["avatarImage"]) ? $context["avatarImage"] : null), "html", null, true);
                    echo "\" alt=\"Avatar\" height=\"126\" width=\"116\">
                ";
                }
                // line 1916
                echo "            </div>

            <!-- Cropping modal -->
            <div class=\"modal fade\" id=\"avatar-modal\" aria-hidden=\"true\" aria-labelledby=\"avatar-modal-label\" role=\"dialog\" tabindex=\"-1\">
                <div class=\"modal-dialog modal-lg\">
                    <div class=\"modal-content\">
                        ";
                // line 1923
                echo "                        <form class=\"avatar-form\" action=\"";
                echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("employees_save_avatar");
                echo "\" enctype=\"multipart/form-data\" method=\"post\">
                            <div class=\"modal-header\">
                                <button class=\"close\" data-dismiss=\"modal\" type=\"button\">&times;</button>
                                <h4 class=\"modal-title\" id=\"avatar-modal-label\">Profile Photo</h4>
                            </div>
                            <div class=\"modal-body\">
                                <div class=\"avatar-body\">

                                    <!-- Upload image and data -->
                                    <div class=\"avatar-upload\">
                                        <input class=\"avatar-userid\" name=\"avatar_userid\" type=\"hidden\" value=\"";
                // line 1933
                echo twig_escape_filter($this->env, $this->getAttribute((isset($context["user"]) ? $context["user"] : null), "id", array()), "html", null, true);
                echo "\">
                                        <input class=\"avatar-src\" name=\"avatar_src\" type=\"hidden\">
                                        <input class=\"avatar-data\" name=\"avatar_data\" type=\"hidden\">
                                        <label for=\"avatarInput\">Upload:</label>
                                        <input class=\"avatar-input\" id=\"avatarInput\" name=\"avatar_file\" type=\"file\">
                                    </div>

                                    <!-- Crop and preview -->
                                    <div class=\"row\">
                                        <div class=\"col-md-9\">
                                            <div class=\"avatar-wrapper\"></div>
                                        </div>
                                        <div class=\"col-md-3\">
                                            <div class=\"avatar-preview avatar-preview-default\"></div>
                                            ";
                // line 1948
                echo "                                            ";
                // line 1949
                echo "                                            ";
                // line 1950
                echo "                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class=\"modal-footer\">
                                <button class=\"btn btn-default\" data-dismiss=\"modal\" type=\"button\">Cancel</button>
                                <button class=\"btn btn-primary avatar-save\" type=\"button\">Save</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div><!-- /.modal -->

            <!-- Loading state -->
            <div class=\"loading\" aria-label=\"Loading\" role=\"img\" tabindex=\"-1\"></div>
        </div>

    ";
            }
            // line 1968
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

    // line 1972
    public function getshowAssistantes($__user__ = null, $__sitename__ = null, ...$__varargs__)
    {
        $context = $this->env->mergeGlobals(array(
            "user" => $__user__,
            "sitename" => $__sitename__,
            "varargs" => $__varargs__,
        ));

        $blocks = array();

        ob_start();
        try {
            // line 1973
            echo "
    ";
            // line 1974
            $context["usermacros"] = $this->loadTemplate("OlegUserdirectoryBundle::Default/usermacros.html.twig", "OlegUserdirectoryBundle::Default/usermacros.html.twig", 1974);
            // line 1975
            echo "
    ";
            // line 1976
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable($this->getAttribute((isset($context["user"]) ? $context["user"] : null), "locations", array()));
            foreach ($context['_seq'] as $context["_key"] => $context["location"]) {
                // line 1977
                echo "        ";
                if ($this->getAttribute($context["location"], "assistant", array())) {
                    // line 1978
                    echo "            ";
                    if ((twig_length_filter($this->env, $this->getAttribute($context["location"], "assistant", array())) > 0)) {
                        // line 1979
                        echo "                <p style=\"margin-bottom:0;\"><i>";
                        echo twig_escape_filter($this->env, $this->getAttribute($context["location"], "name", array()), "html", null, true);
                        echo ":</i></p>
            ";
                    }
                    // line 1981
                    echo "            ";
                    $context['_parent'] = $context;
                    $context['_seq'] = twig_ensure_traversable($this->getAttribute($context["location"], "assistant", array()));
                    foreach ($context['_seq'] as $context["_key"] => $context["assistant"]) {
                        // line 1982
                        echo "                <a href=\"";
                        echo twig_escape_filter($this->env, $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath(((isset($context["sitename"]) ? $context["sitename"] : null) . "_showuser"), array("id" => $this->getAttribute($context["assistant"], "id", array()))), "html", null, true);
                        echo "\">";
                        echo twig_escape_filter($this->env, $this->getAttribute($context["assistant"], "getUsernameShortest", array(), "method"), "html", null, true);
                        echo "</a>
                ";
                        // line 1984
                        echo "                ";
                        $context["phones"] = $this->getAttribute($context["assistant"], "getAllPhones", array(), "method");
                        // line 1985
                        echo "                ";
                        if ((twig_length_filter($this->env, (isset($context["phones"]) ? $context["phones"] : null)) > 0)) {
                            // line 1986
                            echo "                    ";
                            $context['_parent'] = $context;
                            $context['_seq'] = twig_ensure_traversable((isset($context["phones"]) ? $context["phones"] : null));
                            foreach ($context['_seq'] as $context["_key"] => $context["phone"]) {
                                // line 1987
                                echo "                        <p style=\"margin-bottom:0;\">";
                                echo twig_escape_filter($this->env, $this->getAttribute($context["phone"], "prefix", array(), "array"), "html", null, true);
                                echo $context["usermacros"]->getphoneHref($this->getAttribute($context["phone"], "phone", array(), "array"));
                                echo "</p>
                    ";
                            }
                            $_parent = $context['_parent'];
                            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['phone'], $context['_parent'], $context['loop']);
                            $context = array_intersect_key($context, $_parent) + $_parent;
                            // line 1989
                            echo "                ";
                        }
                        // line 1990
                        echo "                ";
                        // line 1991
                        echo "                ";
                        $context["emails"] = $this->getAttribute($context["assistant"], "getAllEmail", array(), "method");
                        // line 1992
                        echo "                ";
                        if ((twig_length_filter($this->env, (isset($context["emails"]) ? $context["emails"] : null)) > 0)) {
                            // line 1993
                            echo "                    ";
                            $context['_parent'] = $context;
                            $context['_seq'] = twig_ensure_traversable((isset($context["emails"]) ? $context["emails"] : null));
                            foreach ($context['_seq'] as $context["_key"] => $context["email"]) {
                                // line 1994
                                echo "                        <p style=\"margin-bottom:0;\">";
                                echo twig_escape_filter($this->env, $this->getAttribute($context["email"], "prefix", array(), "array"), "html", null, true);
                                echo "<a href=\"mailto:";
                                echo twig_escape_filter($this->env, $this->getAttribute($context["email"], "email", array(), "array"), "html", null, true);
                                echo "\" target=\"_top\">";
                                echo twig_escape_filter($this->env, $this->getAttribute($context["email"], "email", array(), "array"), "html", null, true);
                                echo "</a></p>
                    ";
                            }
                            $_parent = $context['_parent'];
                            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['email'], $context['_parent'], $context['loop']);
                            $context = array_intersect_key($context, $_parent) + $_parent;
                            // line 1996
                            echo "                ";
                        }
                        // line 1997
                        echo "            ";
                    }
                    $_parent = $context['_parent'];
                    unset($context['_seq'], $context['_iterated'], $context['_key'], $context['assistant'], $context['_parent'], $context['loop']);
                    $context = array_intersect_key($context, $_parent) + $_parent;
                    // line 1998
                    echo "        ";
                }
                // line 1999
                echo "        ";
                if ((twig_length_filter($this->env, $this->getAttribute($context["location"], "assistant", array())) > 0)) {
                    // line 2000
                    echo "            <br>
        ";
                }
                // line 2002
                echo "    ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['location'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 2003
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

    // line 2009
    public function gettrainingObject($__field__ = null, $__cycle__ = null, $__classname__ = null, $__prototype__ = null, $__sitename__ = null, $__entity__ = null, ...$__varargs__)
    {
        $context = $this->env->mergeGlobals(array(
            "field" => $__field__,
            "cycle" => $__cycle__,
            "classname" => $__classname__,
            "prototype" => $__prototype__,
            "sitename" => $__sitename__,
            "entity" => $__entity__,
            "varargs" => $__varargs__,
        ));

        $blocks = array();

        ob_start();
        try {
            // line 2010
            echo "    ";
            $context["formmacros"] = $this->loadTemplate("OlegOrderformBundle::Default/formmacros.html.twig", "OlegUserdirectoryBundle::Default/usermacros.html.twig", 2010);
            // line 2011
            echo "    ";
            $context["usermacros"] = $this;
            // line 2012
            echo "
    ";
            // line 2013
            if (((isset($context["prototype"]) ? $context["prototype"] : null) == "prototype")) {
                // line 2014
                echo "        ";
                $context["formfield"] = $this->getAttribute($this->getAttribute((isset($context["field"]) ? $context["field"] : null), "vars", array()), "prototype", array());
                // line 2015
                echo "    ";
            } else {
                // line 2016
                echo "        ";
                $context["formfield"] = (isset($context["field"]) ? $context["field"] : null);
                // line 2017
                echo "    ";
            }
            // line 2018
            echo "
    ";
            // line 2019
            if ((($this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "vars", array()), "value", array()) && ($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "vars", array()), "value", array()), "status", array()) == twig_constant("Oleg\\UserdirectoryBundle\\Entity\\BaseUserAttributes::STATUS_UNVERIFIED"))) || ((isset($context["prototype"]) ? $context["prototype"] : null) == "prototype"))) {
                // line 2020
                echo "        ";
                $context["wellclass"] = "user-alert-warning";
                // line 2021
                echo "    ";
            } else {
                // line 2022
                echo "        ";
                $context["wellclass"] = "";
                // line 2023
                echo "    ";
            }
            // line 2024
            echo "
    ";
            // line 2025
            if (twig_in_filter("show", (isset($context["cycle"]) ? $context["cycle"] : null))) {
                // line 2026
                echo "        ";
                $context["showFlag"] = true;
                // line 2027
                echo "    ";
            } else {
                // line 2028
                echo "        ";
                $context["showFlag"] = false;
                // line 2029
                echo "    ";
            }
            // line 2030
            echo "

    <div class=\"user-collection-holder alert ";
            // line 2032
            echo twig_escape_filter($this->env, (isset($context["classname"]) ? $context["classname"] : null), "html", null, true);
            echo " ";
            echo twig_escape_filter($this->env, (isset($context["wellclass"]) ? $context["wellclass"] : null), "html", null, true);
            echo "\">

        ";
            // line 2034
            if (((isset($context["cycle"]) ? $context["cycle"] : null) != "show_user")) {
                // line 2035
                echo "            <div class=\"text-right\">
                <button type=\"button\" class=\"btn btn-default btn-sm\" onClick=\"removeExistingObject(this,'";
                // line 2036
                echo twig_escape_filter($this->env, (isset($context["classname"]) ? $context["classname"] : null), "html", null, true);
                echo "')\" >
                    <span class=\"glyphicon glyphicon-remove\"></span>
                </button>
            </div>
        ";
            }
            // line 2041
            echo "
        ";
            // line 2042
            echo $context["formmacros"]->getfield($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "id", array()));
            echo "

        ";
            // line 2044
            echo $context["usermacros"]->getstatusVerifiedField((isset($context["formfield"]) ? $context["formfield"] : null), (isset($context["cycle"]) ? $context["cycle"] : null));
            echo "

        ";
            // line 2046
            echo $context["formmacros"]->getfield_notempty($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "degree", array()), (isset($context["cycle"]) ? $context["cycle"] : null));
            echo "

        ";
            // line 2049
            echo "        ";
            // line 2050
            echo "        ";
            // line 2051
            echo "            ";
            // line 2052
            echo "                ";
            // line 2053
            echo "            ";
            // line 2054
            echo "                ";
            // line 2055
            echo "            ";
            // line 2056
            echo "            ";
            // line 2057
            echo "        ";
            // line 2058
            echo "        ";
            if ( !(isset($context["showFlag"]) ? $context["showFlag"] : null)) {
                // line 2059
                echo "            ";
                echo $context["formmacros"]->getfield($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "appendDegreeToName", array()));
                echo "
        ";
            }
            // line 2061
            echo "
        ";
            // line 2063
            echo "        ";
            if (( !(isset($context["showFlag"]) ? $context["showFlag"] : null) || (((isset($context["showFlag"]) ? $context["showFlag"] : null) && $this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "vars", array()), "value", array())) &&  !$this->getAttribute($this->getAttribute($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "vars", array()), "value", array()), "fellowshipSubspecialty", array())))) {
                // line 2064
                echo "            ";
                echo $context["formmacros"]->getfield_notempty($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "residencySpecialty", array()), (isset($context["cycle"]) ? $context["cycle"] : null));
                echo "
        ";
            }
            // line 2066
            echo "
        ";
            // line 2067
            echo $context["formmacros"]->getfield_notempty($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "fellowshipSubspecialty", array()), (isset($context["cycle"]) ? $context["cycle"] : null));
            echo "

        ";
            // line 2069
            if ($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "institution", array(), "any", true, true)) {
                // line 2070
                echo "            ";
                echo $context["formmacros"]->getfield($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "institution", array()));
                echo "
        ";
            }
            // line 2072
            echo "
        ";
            // line 2073
            echo $context["formmacros"]->getfieldDateLabel_notempty($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "startDate", array()), (isset($context["cycle"]) ? $context["cycle"] : null), "allow-future-date");
            echo "

        ";
            // line 2075
            if ($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "completionDate", array(), "any", true, true)) {
                // line 2076
                echo "            ";
                echo $context["formmacros"]->getfieldDateLabel($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "completionDate", array()), "allow-future-date");
                echo "
        ";
            }
            // line 2078
            echo "
        ";
            // line 2079
            if ($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "completionReason", array(), "any", true, true)) {
                // line 2080
                echo "            ";
                echo $context["formmacros"]->getfield_notempty($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "completionReason", array()), (isset($context["cycle"]) ? $context["cycle"] : null));
                echo "
        ";
            }
            // line 2082
            echo "
        ";
            // line 2083
            if ($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "majors", array(), "any", true, true)) {
                // line 2084
                echo "            ";
                echo $context["formmacros"]->getfield_notempty($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "majors", array()), (isset($context["cycle"]) ? $context["cycle"] : null));
                echo "
        ";
            }
            // line 2086
            echo "
        ";
            // line 2087
            if ($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "minors", array(), "any", true, true)) {
                // line 2088
                echo "            ";
                echo $context["formmacros"]->getfield_notempty($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "minors", array()), (isset($context["cycle"]) ? $context["cycle"] : null));
                echo "
        ";
            }
            // line 2090
            echo "
        ";
            // line 2091
            if ($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "honors", array(), "any", true, true)) {
                // line 2092
                echo "            ";
                echo $context["formmacros"]->getfield_notempty($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "honors", array()), (isset($context["cycle"]) ? $context["cycle"] : null));
                echo "
        ";
            }
            // line 2094
            echo "
        ";
            // line 2095
            if ($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "fellowshipTitle", array(), "any", true, true)) {
                // line 2096
                echo "            ";
                echo $context["formmacros"]->getfield($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "fellowshipTitle", array()));
                echo "
        ";
            }
            // line 2098
            echo "
        ";
            // line 2100
            echo "        ";
            // line 2101
            echo "            ";
            // line 2102
            echo "                ";
            // line 2103
            echo "            ";
            // line 2104
            echo "                ";
            // line 2105
            echo "            ";
            // line 2106
            echo "            ";
            // line 2107
            echo "        ";
            // line 2108
            echo "        ";
            if (( !(isset($context["showFlag"]) ? $context["showFlag"] : null) && $this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "appendFellowshipTitleToName", array(), "any", true, true))) {
                // line 2109
                echo "            ";
                echo $context["formmacros"]->getfield($this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "appendFellowshipTitleToName", array()));
                echo "
        ";
            }
            // line 2111
            echo "
    </div>


    ";
            // line 2115
            $this->getAttribute((isset($context["formfield"]) ? $context["formfield"] : null), "setRendered", array());
            // line 2116
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

    // line 2120
    public function getaddNewObjectBtn($__cycle__ = null, $__classname__ = null, $__title__ = null, $__addClass__ = null, ...$__varargs__)
    {
        $context = $this->env->mergeGlobals(array(
            "cycle" => $__cycle__,
            "classname" => $__classname__,
            "title" => $__title__,
            "addClass" => $__addClass__,
            "varargs" => $__varargs__,
        ));

        $blocks = array();

        ob_start();
        try {
            // line 2121
            echo "    ";
            if (twig_in_filter("show", (isset($context["cycle"]) ? $context["cycle"] : null))) {
                // line 2122
                echo "        ";
                $context["showFlag"] = true;
                // line 2123
                echo "    ";
            } else {
                // line 2124
                echo "        ";
                $context["showFlag"] = false;
                // line 2125
                echo "    ";
            }
            // line 2126
            echo "
    ";
            // line 2127
            if ( !(isset($context["showFlag"]) ? $context["showFlag"] : null)) {
                // line 2128
                echo "
        ";
                // line 2129
                $context["addClassStr"] = "";
                // line 2130
                echo "        ";
                if ((array_key_exists("addClass", $context) && (isset($context["addClass"]) ? $context["addClass"] : null))) {
                    // line 2131
                    echo "            ";
                    $context["addClassStr"] = (isset($context["addClass"]) ? $context["addClass"] : null);
                    // line 2132
                    echo "        ";
                }
                // line 2133
                echo "
        <div style=\"align-content: center;\">
            <button class=\"btn btn-default ";
                // line 2135
                echo twig_escape_filter($this->env, (isset($context["addClassStr"]) ? $context["addClassStr"] : null), "html", null, true);
                echo "\" onClick=\"addNewObject(this,'";
                echo twig_escape_filter($this->env, (isset($context["classname"]) ? $context["classname"] : null), "html", null, true);
                echo "')\" type='button'>";
                echo twig_escape_filter($this->env, (isset($context["title"]) ? $context["title"] : null), "html", null, true);
                echo "</button>
        </div>
    ";
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

    // line 2141
    public function getuserUrlLink(...$__varargs__)
    {
        $context = $this->env->mergeGlobals(array(
            "varargs" => $__varargs__,
        ));

        $blocks = array();

        ob_start();
        try {
            // line 2142
            echo "    <a href=\"";
            echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath(((isset($context["employees_sitename"]) ? $context["employees_sitename"] : null) . "_showuser_notstrict"), array("id" => "user_replacement_id"));
            echo "\" target=\"_blank\">";
            echo "user_replacement_username";
            echo "</a><br>
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

    // line 2146
    public function getshowDocumentAsImage($__documentEntity__ = null, $__alt__ = null, $__atr__ = null, $__objectClass__ = null, $__sitename__ = null, ...$__varargs__)
    {
        $context = $this->env->mergeGlobals(array(
            "documentEntity" => $__documentEntity__,
            "alt" => $__alt__,
            "atr" => $__atr__,
            "objectClass" => $__objectClass__,
            "sitename" => $__sitename__,
            "varargs" => $__varargs__,
        ));

        $blocks = array();

        ob_start();
        try {
            // line 2147
            echo "
    ";
            // line 2148
            if (( !(isset($context["objectClass"]) ? $context["objectClass"] : null) ||  !array_key_exists("objectClass", $context))) {
                // line 2149
                echo "        ";
                // line 2150
                echo "        ";
                $context["objectClass"] = "objectMaxFull";
                // line 2151
                echo "    ";
            }
            // line 2152
            echo "
    ";
            // line 2154
            echo "    ";
            if (( !array_key_exists("sitename", $context) ||  !(isset($context["sitename"]) ? $context["sitename"] : null))) {
                // line 2155
                echo "        ";
                $context["sitename"] = (isset($context["employees_sitename"]) ? $context["employees_sitename"] : null);
                echo " ";
                // line 2156
                echo "        ";
                $context["currentPath"] = $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["app"]) ? $context["app"] : null), "request", array()), "attributes", array()), "get", array(0 => "_route"), "method"), $this->getAttribute($this->getAttribute($this->getAttribute((isset($context["app"]) ? $context["app"] : null), "request", array()), "attributes", array()), "get", array(0 => "_route_params"), "method"));
                // line 2157
                echo "        ";
                if (twig_in_filter("/scan/", (isset($context["currentPath"]) ? $context["currentPath"] : null))) {
                    // line 2158
                    echo "            ";
                    $context["sitename"] = (isset($context["scan_sitename"]) ? $context["scan_sitename"] : null);
                    // line 2159
                    echo "        ";
                }
                // line 2160
                echo "        ";
                if (twig_in_filter("/directory/", (isset($context["currentPath"]) ? $context["currentPath"] : null))) {
                    // line 2161
                    echo "            ";
                    $context["sitename"] = (isset($context["employees_sitename"]) ? $context["employees_sitename"] : null);
                    // line 2162
                    echo "        ";
                }
                // line 2163
                echo "        ";
                if (twig_in_filter("/fellowship-applications/", (isset($context["currentPath"]) ? $context["currentPath"] : null))) {
                    // line 2164
                    echo "            ";
                    $context["sitename"] = (isset($context["fellapp_sitename"]) ? $context["fellapp_sitename"] : null);
                    // line 2165
                    echo "        ";
                }
                // line 2166
                echo "        ";
                if (twig_in_filter("/deidentifier/", (isset($context["currentPath"]) ? $context["currentPath"] : null))) {
                    // line 2167
                    echo "            ";
                    $context["sitename"] = (isset($context["deidentifier_sitename"]) ? $context["deidentifier_sitename"] : null);
                    // line 2168
                    echo "        ";
                }
                // line 2169
                echo "        ";
                if (twig_in_filter("/vacation-request/", (isset($context["currentPath"]) ? $context["currentPath"] : null))) {
                    // line 2170
                    echo "            ";
                    $context["sitename"] = (isset($context["vacreq_sitename"]) ? $context["vacreq_sitename"] : null);
                    // line 2171
                    echo "        ";
                }
                // line 2172
                echo "        ";
                if (twig_in_filter("/call-log-book/", (isset($context["currentPath"]) ? $context["currentPath"] : null))) {
                    // line 2173
                    echo "            ";
                    $context["sitename"] = (isset($context["calllog_sitename"]) ? $context["calllog_sitename"] : null);
                    // line 2174
                    echo "        ";
                }
                // line 2175
                echo "        ";
                if (twig_in_filter("/translational-research/", (isset($context["currentPath"]) ? $context["currentPath"] : null))) {
                    // line 2176
                    echo "            ";
                    $context["sitename"] = (isset($context["translationalresearch_sitename"]) ? $context["translationalresearch_sitename"] : null);
                    // line 2177
                    echo "        ";
                }
                // line 2178
                echo "    ";
            }
            // line 2179
            echo "
    ";
            // line 2180
            if (twig_in_filter(".pdf", $this->getAttribute((isset($context["documentEntity"]) ? $context["documentEntity"] : null), "getAbsoluteUploadFullPath", array()))) {
                // line 2181
                echo "        ";
                // line 2182
                echo "        ";
                // line 2183
                echo "        ";
                // line 2184
                echo "                ";
                // line 2185
                echo "                ";
                // line 2186
                echo "                ";
                // line 2187
                echo "        ";
                // line 2188
                echo "        ";
                // line 2189
                echo "        ";
                // line 2190
                echo "        <a href=\"";
                echo twig_escape_filter($this->env, $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath(((isset($context["sitename"]) ? $context["sitename"] : null) . "_file_view"), array("id" => $this->getAttribute((isset($context["documentEntity"]) ? $context["documentEntity"] : null), "id", array()))), "html", null, true);
                echo "\" target=\"_blank\">
            <object ";
                // line 2191
                echo twig_escape_filter($this->env, (isset($context["atr"]) ? $context["atr"] : null), "html", null, true);
                echo " alt=\"";
                echo twig_escape_filter($this->env, (((isset($context["alt"]) ? $context["alt"] : null) . " ") . $this->getAttribute((isset($context["documentEntity"]) ? $context["documentEntity"] : null), "getOriginalnameClean", array())), "html", null, true);
                echo "\"
                  data=\"";
                // line 2192
                echo twig_escape_filter($this->env, $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath(((isset($context["sitename"]) ? $context["sitename"] : null) . "_file_view"), array("id" => $this->getAttribute((isset($context["documentEntity"]) ? $context["documentEntity"] : null), "id", array()), "viewType" => "snapshot")), "html", null, true);
                echo "\"
                  type=\"application/pdf\"
                  class=\"";
                // line 2194
                echo twig_escape_filter($this->env, (isset($context["objectClass"]) ? $context["objectClass"] : null), "html", null, true);
                echo "\"
            >
            </object>
        </a>
    ";
            } elseif (twig_in_filter(".doc", $this->getAttribute(            // line 2198
(isset($context["documentEntity"]) ? $context["documentEntity"] : null), "getAbsoluteUploadFullPath", array()))) {
                // line 2199
                echo "        ";
                // line 2200
                echo "        ";
                // line 2201
                echo "        ";
                // line 2202
                echo "                ";
                // line 2203
                echo "                ";
                // line 2204
                echo "                ";
                // line 2205
                echo "                ";
                // line 2206
                echo "        ";
                // line 2207
                echo "        ";
                // line 2208
                echo "        ";
                // line 2209
                echo "        <a href=\"";
                echo twig_escape_filter($this->env, $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath(((isset($context["sitename"]) ? $context["sitename"] : null) . "_file_view"), array("id" => $this->getAttribute((isset($context["documentEntity"]) ? $context["documentEntity"] : null), "id", array()))), "html", null, true);
                echo "\" target=\"_blank\">
            <object ";
                // line 2210
                echo twig_escape_filter($this->env, (isset($context["atr"]) ? $context["atr"] : null), "html", null, true);
                echo " alt=\"";
                echo twig_escape_filter($this->env, (((isset($context["alt"]) ? $context["alt"] : null) . " ") . $this->getAttribute((isset($context["documentEntity"]) ? $context["documentEntity"] : null), "getOriginalnameClean", array())), "html", null, true);
                echo "\"
                  data=\"";
                // line 2211
                echo twig_escape_filter($this->env, $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath(((isset($context["sitename"]) ? $context["sitename"] : null) . "_file_view"), array("id" => $this->getAttribute((isset($context["documentEntity"]) ? $context["documentEntity"] : null), "id", array()), "viewType" => "snapshot")), "html", null, true);
                echo "\"
                  type=\"application/msword\"
                  class=\"";
                // line 2213
                echo twig_escape_filter($this->env, (isset($context["objectClass"]) ? $context["objectClass"] : null), "html", null, true);
                echo "\"
            >
            </object>
        </a>
    ";
            } elseif (twig_in_filter(".tif", $this->getAttribute(            // line 2217
(isset($context["documentEntity"]) ? $context["documentEntity"] : null), "getAbsoluteUploadFullPath", array()))) {
                // line 2218
                echo "        <a href=\"";
                echo twig_escape_filter($this->env, $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath(((isset($context["sitename"]) ? $context["sitename"] : null) . "_file_view"), array("id" => $this->getAttribute((isset($context["documentEntity"]) ? $context["documentEntity"] : null), "id", array()))), "html", null, true);
                echo "\" target=\"_blank\">
            ";
                // line 2220
                echo "                              ";
                // line 2221
                echo "                              ";
                // line 2222
                echo "                              ";
                // line 2223
                echo "                              ";
                // line 2224
                echo "            ";
                // line 2225
                echo "            <object ";
                echo twig_escape_filter($this->env, (isset($context["atr"]) ? $context["atr"] : null), "html", null, true);
                echo " alt=\"";
                echo twig_escape_filter($this->env, (((isset($context["alt"]) ? $context["alt"] : null) . " ") . $this->getAttribute((isset($context["documentEntity"]) ? $context["documentEntity"] : null), "getOriginalnameClean", array())), "html", null, true);
                echo "\"
                              data=\"";
                // line 2226
                echo twig_escape_filter($this->env, $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath(((isset($context["sitename"]) ? $context["sitename"] : null) . "_file_view"), array("id" => $this->getAttribute((isset($context["documentEntity"]) ? $context["documentEntity"] : null), "id", array()), "viewType" => "snapshot")), "html", null, true);
                echo "\"
                              type=\"image/tiff\"
                              class=\"";
                // line 2228
                echo twig_escape_filter($this->env, (isset($context["objectClass"]) ? $context["objectClass"] : null), "html", null, true);
                echo "\"
            >
            </object>
        </a>
    ";
            } else {
                // line 2233
                echo "        ";
                // line 2234
                echo "        <a href=\"";
                echo twig_escape_filter($this->env, $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath(((isset($context["sitename"]) ? $context["sitename"] : null) . "_file_view"), array("id" => $this->getAttribute((isset($context["documentEntity"]) ? $context["documentEntity"] : null), "id", array()))), "html", null, true);
                echo "\" target=\"_blank\">
            ";
                // line 2236
                echo "                 ";
                // line 2237
                echo "                 ";
                // line 2238
                echo "                 ";
                // line 2239
                echo "                 ";
                // line 2240
                echo "                 ";
                // line 2241
                echo "            ";
                // line 2242
                echo "            <img ";
                echo twig_escape_filter($this->env, (isset($context["atr"]) ? $context["atr"] : null), "html", null, true);
                echo "
                src=\"";
                // line 2243
                echo twig_escape_filter($this->env, $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath(((isset($context["sitename"]) ? $context["sitename"] : null) . "_file_view"), array("id" => $this->getAttribute((isset($context["documentEntity"]) ? $context["documentEntity"] : null), "id", array()), "viewType" => "snapshot")), "html", null, true);
                echo "\"
                alt=\"";
                // line 2244
                echo twig_escape_filter($this->env, (((isset($context["alt"]) ? $context["alt"] : null) . " ") . $this->getAttribute((isset($context["documentEntity"]) ? $context["documentEntity"] : null), "getOriginalnameClean", array())), "html", null, true);
                echo "\"
                class=\"";
                // line 2245
                echo twig_escape_filter($this->env, (isset($context["objectClass"]) ? $context["objectClass"] : null), "html", null, true);
                echo "\"
            />
        </a>
    ";
            }
            // line 2249
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

    public function getTemplateName()
    {
        return "OlegUserdirectoryBundle::Default/usermacros.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  6462 => 2249,  6455 => 2245,  6451 => 2244,  6447 => 2243,  6442 => 2242,  6440 => 2241,  6438 => 2240,  6436 => 2239,  6434 => 2238,  6432 => 2237,  6430 => 2236,  6425 => 2234,  6423 => 2233,  6415 => 2228,  6410 => 2226,  6403 => 2225,  6401 => 2224,  6399 => 2223,  6397 => 2222,  6395 => 2221,  6393 => 2220,  6388 => 2218,  6386 => 2217,  6379 => 2213,  6374 => 2211,  6368 => 2210,  6363 => 2209,  6361 => 2208,  6359 => 2207,  6357 => 2206,  6355 => 2205,  6353 => 2204,  6351 => 2203,  6349 => 2202,  6347 => 2201,  6345 => 2200,  6343 => 2199,  6341 => 2198,  6334 => 2194,  6329 => 2192,  6323 => 2191,  6318 => 2190,  6316 => 2189,  6314 => 2188,  6312 => 2187,  6310 => 2186,  6308 => 2185,  6306 => 2184,  6304 => 2183,  6302 => 2182,  6300 => 2181,  6298 => 2180,  6295 => 2179,  6292 => 2178,  6289 => 2177,  6286 => 2176,  6283 => 2175,  6280 => 2174,  6277 => 2173,  6274 => 2172,  6271 => 2171,  6268 => 2170,  6265 => 2169,  6262 => 2168,  6259 => 2167,  6256 => 2166,  6253 => 2165,  6250 => 2164,  6247 => 2163,  6244 => 2162,  6241 => 2161,  6238 => 2160,  6235 => 2159,  6232 => 2158,  6229 => 2157,  6226 => 2156,  6222 => 2155,  6219 => 2154,  6216 => 2152,  6213 => 2151,  6210 => 2150,  6208 => 2149,  6206 => 2148,  6203 => 2147,  6187 => 2146,  6167 => 2142,  6156 => 2141,  6133 => 2135,  6129 => 2133,  6126 => 2132,  6123 => 2131,  6120 => 2130,  6118 => 2129,  6115 => 2128,  6113 => 2127,  6110 => 2126,  6107 => 2125,  6104 => 2124,  6101 => 2123,  6098 => 2122,  6095 => 2121,  6080 => 2120,  6064 => 2116,  6062 => 2115,  6056 => 2111,  6050 => 2109,  6047 => 2108,  6045 => 2107,  6043 => 2106,  6041 => 2105,  6039 => 2104,  6037 => 2103,  6035 => 2102,  6033 => 2101,  6031 => 2100,  6028 => 2098,  6022 => 2096,  6020 => 2095,  6017 => 2094,  6011 => 2092,  6009 => 2091,  6006 => 2090,  6000 => 2088,  5998 => 2087,  5995 => 2086,  5989 => 2084,  5987 => 2083,  5984 => 2082,  5978 => 2080,  5976 => 2079,  5973 => 2078,  5967 => 2076,  5965 => 2075,  5960 => 2073,  5957 => 2072,  5951 => 2070,  5949 => 2069,  5944 => 2067,  5941 => 2066,  5935 => 2064,  5932 => 2063,  5929 => 2061,  5923 => 2059,  5920 => 2058,  5918 => 2057,  5916 => 2056,  5914 => 2055,  5912 => 2054,  5910 => 2053,  5908 => 2052,  5906 => 2051,  5904 => 2050,  5902 => 2049,  5897 => 2046,  5892 => 2044,  5887 => 2042,  5884 => 2041,  5876 => 2036,  5873 => 2035,  5871 => 2034,  5864 => 2032,  5860 => 2030,  5857 => 2029,  5854 => 2028,  5851 => 2027,  5848 => 2026,  5846 => 2025,  5843 => 2024,  5840 => 2023,  5837 => 2022,  5834 => 2021,  5831 => 2020,  5829 => 2019,  5826 => 2018,  5823 => 2017,  5820 => 2016,  5817 => 2015,  5814 => 2014,  5812 => 2013,  5809 => 2012,  5806 => 2011,  5803 => 2010,  5786 => 2009,  5770 => 2003,  5764 => 2002,  5760 => 2000,  5757 => 1999,  5754 => 1998,  5748 => 1997,  5745 => 1996,  5732 => 1994,  5727 => 1993,  5724 => 1992,  5721 => 1991,  5719 => 1990,  5716 => 1989,  5706 => 1987,  5701 => 1986,  5698 => 1985,  5695 => 1984,  5688 => 1982,  5683 => 1981,  5677 => 1979,  5674 => 1978,  5671 => 1977,  5667 => 1976,  5664 => 1975,  5662 => 1974,  5659 => 1973,  5646 => 1972,  5630 => 1968,  5610 => 1950,  5608 => 1949,  5606 => 1948,  5589 => 1933,  5575 => 1923,  5567 => 1916,  5561 => 1914,  5558 => 1913,  5552 => 1911,  5549 => 1910,  5542 => 1904,  5536 => 1900,  5530 => 1898,  5527 => 1897,  5521 => 1895,  5519 => 1894,  5515 => 1892,  5513 => 1891,  5510 => 1890,  5507 => 1889,  5504 => 1888,  5501 => 1887,  5498 => 1886,  5496 => 1885,  5493 => 1884,  5491 => 1883,  5488 => 1882,  5475 => 1881,  5459 => 1877,  5452 => 1875,  5450 => 1874,  5448 => 1873,  5446 => 1872,  5444 => 1871,  5442 => 1870,  5440 => 1869,  5438 => 1868,  5436 => 1867,  5434 => 1866,  5428 => 1864,  5426 => 1863,  5423 => 1862,  5417 => 1860,  5413 => 1858,  5411 => 1857,  5408 => 1856,  5406 => 1855,  5403 => 1854,  5401 => 1853,  5397 => 1851,  5393 => 1850,  5390 => 1849,  5388 => 1848,  5385 => 1847,  5371 => 1846,  5355 => 1842,  5345 => 1835,  5337 => 1830,  5332 => 1828,  5322 => 1820,  5319 => 1819,  5317 => 1818,  5314 => 1816,  5312 => 1815,  5309 => 1814,  5306 => 1813,  5303 => 1812,  5300 => 1811,  5297 => 1810,  5294 => 1809,  5291 => 1807,  5289 => 1806,  5286 => 1805,  5270 => 1804,  5238 => 1787,  5228 => 1779,  5226 => 1778,  5212 => 1777,  5196 => 1772,  5193 => 1770,  5190 => 1769,  5188 => 1768,  5186 => 1767,  5177 => 1759,  5175 => 1758,  5169 => 1754,  5162 => 1749,  5159 => 1748,  5157 => 1747,  5155 => 1746,  5151 => 1743,  5148 => 1742,  5141 => 1740,  5133 => 1735,  5126 => 1733,  5119 => 1729,  5115 => 1728,  5110 => 1725,  5108 => 1724,  5105 => 1723,  5100 => 1722,  5097 => 1721,  5093 => 1718,  5090 => 1717,  5083 => 1715,  5075 => 1710,  5068 => 1708,  5061 => 1704,  5057 => 1703,  5052 => 1700,  5050 => 1699,  5047 => 1698,  5042 => 1697,  5039 => 1696,  5035 => 1693,  5032 => 1692,  5025 => 1690,  5017 => 1685,  5010 => 1683,  5003 => 1679,  4999 => 1678,  4994 => 1675,  4992 => 1674,  4989 => 1673,  4984 => 1672,  4981 => 1671,  4978 => 1669,  4975 => 1668,  4968 => 1666,  4960 => 1661,  4953 => 1659,  4946 => 1655,  4942 => 1654,  4937 => 1651,  4935 => 1650,  4932 => 1649,  4927 => 1648,  4924 => 1647,  4920 => 1644,  4917 => 1643,  4910 => 1641,  4901 => 1635,  4894 => 1633,  4885 => 1629,  4881 => 1628,  4875 => 1624,  4873 => 1623,  4870 => 1622,  4868 => 1621,  4865 => 1620,  4859 => 1619,  4856 => 1618,  4853 => 1617,  4850 => 1616,  4845 => 1615,  4842 => 1614,  4840 => 1613,  4837 => 1612,  4832 => 1611,  4829 => 1610,  4825 => 1605,  4822 => 1604,  4815 => 1602,  4807 => 1597,  4800 => 1595,  4789 => 1591,  4785 => 1590,  4780 => 1587,  4778 => 1586,  4775 => 1585,  4770 => 1584,  4767 => 1583,  4764 => 1581,  4756 => 1576,  4751 => 1574,  4744 => 1570,  4738 => 1566,  4735 => 1565,  4732 => 1563,  4724 => 1558,  4719 => 1556,  4712 => 1552,  4706 => 1548,  4703 => 1547,  4697 => 1543,  4684 => 1533,  4676 => 1527,  4673 => 1526,  4671 => 1525,  4668 => 1524,  4666 => 1523,  4664 => 1522,  4661 => 1520,  4647 => 1519,  4644 => 1518,  4641 => 1517,  4638 => 1516,  4635 => 1515,  4632 => 1514,  4629 => 1513,  4626 => 1512,  4623 => 1511,  4605 => 1510,  4602 => 1509,  4599 => 1508,  4596 => 1506,  4594 => 1505,  4592 => 1504,  4590 => 1503,  4588 => 1502,  4586 => 1501,  4584 => 1500,  4582 => 1499,  4580 => 1498,  4578 => 1497,  4576 => 1496,  4574 => 1495,  4572 => 1494,  4570 => 1493,  4568 => 1492,  4566 => 1491,  4549 => 1488,  4546 => 1487,  4543 => 1486,  4529 => 1485,  4526 => 1484,  4520 => 1482,  4517 => 1481,  4514 => 1480,  4511 => 1479,  4508 => 1478,  4505 => 1477,  4487 => 1476,  4469 => 1475,  4466 => 1474,  4463 => 1473,  4460 => 1472,  4457 => 1470,  4451 => 1468,  4448 => 1467,  4445 => 1466,  4442 => 1464,  4436 => 1462,  4433 => 1461,  4430 => 1460,  4427 => 1458,  4424 => 1457,  4421 => 1456,  4418 => 1455,  4415 => 1454,  4412 => 1453,  4409 => 1452,  4407 => 1451,  4404 => 1450,  4402 => 1449,  4399 => 1448,  4396 => 1447,  4393 => 1446,  4390 => 1445,  4387 => 1444,  4385 => 1443,  4382 => 1442,  4379 => 1440,  4377 => 1439,  4374 => 1438,  4358 => 1437,  4333 => 1427,  4326 => 1425,  4320 => 1422,  4313 => 1418,  4309 => 1416,  4307 => 1415,  4305 => 1414,  4303 => 1413,  4300 => 1412,  4298 => 1411,  4296 => 1410,  4281 => 1409,  4263 => 1404,  4260 => 1403,  4257 => 1402,  4254 => 1401,  4251 => 1400,  4248 => 1399,  4245 => 1398,  4242 => 1397,  4229 => 1396,  4213 => 1390,  4211 => 1389,  4204 => 1385,  4200 => 1384,  4196 => 1383,  4192 => 1382,  4189 => 1381,  4181 => 1376,  4178 => 1375,  4176 => 1374,  4169 => 1372,  4166 => 1371,  4164 => 1370,  4161 => 1369,  4158 => 1368,  4155 => 1367,  4152 => 1366,  4149 => 1365,  4147 => 1364,  4144 => 1363,  4141 => 1362,  4138 => 1361,  4135 => 1360,  4132 => 1359,  4130 => 1358,  4127 => 1357,  4124 => 1356,  4121 => 1355,  4104 => 1354,  4088 => 1351,  4086 => 1350,  4079 => 1346,  4075 => 1345,  4071 => 1344,  4067 => 1343,  4063 => 1342,  4059 => 1341,  4055 => 1340,  4050 => 1338,  4047 => 1337,  4039 => 1332,  4036 => 1331,  4034 => 1330,  4027 => 1328,  4024 => 1327,  4022 => 1326,  4019 => 1325,  4017 => 1324,  4015 => 1323,  4013 => 1322,  4011 => 1321,  4008 => 1319,  4005 => 1318,  4002 => 1317,  3999 => 1316,  3996 => 1315,  3994 => 1314,  3991 => 1313,  3988 => 1312,  3985 => 1311,  3968 => 1310,  3952 => 1307,  3950 => 1306,  3945 => 1303,  3939 => 1301,  3937 => 1300,  3932 => 1298,  3927 => 1296,  3923 => 1295,  3920 => 1294,  3917 => 1293,  3914 => 1292,  3908 => 1290,  3905 => 1289,  3899 => 1287,  3897 => 1286,  3892 => 1284,  3888 => 1283,  3883 => 1281,  3880 => 1280,  3872 => 1275,  3869 => 1274,  3867 => 1273,  3860 => 1271,  3857 => 1270,  3855 => 1269,  3852 => 1268,  3849 => 1267,  3846 => 1266,  3843 => 1265,  3840 => 1264,  3838 => 1263,  3835 => 1262,  3832 => 1261,  3829 => 1260,  3826 => 1259,  3823 => 1258,  3821 => 1257,  3818 => 1256,  3815 => 1255,  3812 => 1254,  3795 => 1253,  3779 => 1249,  3777 => 1248,  3772 => 1245,  3766 => 1243,  3764 => 1242,  3759 => 1240,  3755 => 1239,  3752 => 1238,  3749 => 1237,  3746 => 1236,  3740 => 1234,  3737 => 1233,  3731 => 1231,  3729 => 1230,  3724 => 1228,  3720 => 1227,  3715 => 1225,  3712 => 1224,  3704 => 1219,  3701 => 1218,  3699 => 1217,  3692 => 1215,  3689 => 1214,  3687 => 1213,  3684 => 1212,  3681 => 1211,  3678 => 1210,  3675 => 1209,  3672 => 1208,  3670 => 1207,  3667 => 1206,  3664 => 1205,  3661 => 1204,  3658 => 1203,  3655 => 1202,  3653 => 1201,  3650 => 1200,  3647 => 1199,  3644 => 1198,  3627 => 1197,  3611 => 1194,  3609 => 1193,  3606 => 1192,  3600 => 1190,  3597 => 1189,  3592 => 1185,  3588 => 1183,  3582 => 1181,  3579 => 1180,  3577 => 1179,  3574 => 1178,  3565 => 1176,  3562 => 1175,  3559 => 1174,  3554 => 1173,  3552 => 1172,  3549 => 1171,  3546 => 1170,  3544 => 1169,  3542 => 1168,  3539 => 1166,  3533 => 1164,  3531 => 1163,  3526 => 1161,  3522 => 1160,  3518 => 1159,  3514 => 1158,  3510 => 1157,  3505 => 1155,  3501 => 1154,  3498 => 1153,  3492 => 1151,  3490 => 1150,  3487 => 1149,  3484 => 1148,  3481 => 1147,  3475 => 1145,  3472 => 1144,  3466 => 1142,  3464 => 1141,  3459 => 1139,  3455 => 1138,  3451 => 1137,  3446 => 1135,  3443 => 1134,  3436 => 1131,  3433 => 1129,  3431 => 1128,  3426 => 1126,  3423 => 1125,  3420 => 1124,  3417 => 1123,  3414 => 1122,  3411 => 1121,  3408 => 1120,  3405 => 1119,  3402 => 1118,  3400 => 1117,  3397 => 1116,  3394 => 1115,  3391 => 1114,  3388 => 1113,  3385 => 1112,  3383 => 1111,  3380 => 1110,  3377 => 1109,  3374 => 1108,  3371 => 1107,  3369 => 1106,  3366 => 1105,  3363 => 1104,  3360 => 1103,  3357 => 1102,  3354 => 1101,  3352 => 1100,  3349 => 1099,  3346 => 1098,  3343 => 1097,  3326 => 1096,  3310 => 1092,  3308 => 1091,  3305 => 1090,  3299 => 1088,  3296 => 1087,  3289 => 1082,  3285 => 1081,  3282 => 1080,  3280 => 1079,  3278 => 1078,  3276 => 1077,  3274 => 1076,  3272 => 1075,  3270 => 1074,  3268 => 1073,  3262 => 1071,  3259 => 1070,  3256 => 1068,  3250 => 1066,  3247 => 1065,  3241 => 1063,  3239 => 1062,  3236 => 1061,  3234 => 1060,  3232 => 1059,  3230 => 1058,  3228 => 1057,  3226 => 1056,  3224 => 1055,  3222 => 1054,  3216 => 1052,  3213 => 1051,  3210 => 1049,  3204 => 1047,  3202 => 1046,  3197 => 1044,  3192 => 1042,  3189 => 1041,  3182 => 1038,  3179 => 1036,  3177 => 1035,  3172 => 1033,  3169 => 1032,  3166 => 1031,  3163 => 1030,  3160 => 1029,  3158 => 1028,  3155 => 1027,  3152 => 1026,  3149 => 1025,  3146 => 1024,  3143 => 1023,  3141 => 1022,  3138 => 1021,  3135 => 1020,  3132 => 1019,  3115 => 1018,  3099 => 1014,  3097 => 1013,  3094 => 1012,  3088 => 1010,  3085 => 1009,  3080 => 1005,  3076 => 1003,  3070 => 1001,  3065 => 998,  3061 => 997,  3058 => 996,  3055 => 995,  3052 => 994,  3049 => 993,  3046 => 992,  3044 => 991,  3041 => 990,  3038 => 989,  3035 => 988,  3032 => 987,  3029 => 986,  3027 => 985,  3024 => 984,  3021 => 983,  3018 => 982,  3001 => 981,  2985 => 978,  2980 => 976,  2975 => 975,  2973 => 974,  2970 => 973,  2964 => 971,  2962 => 970,  2959 => 969,  2953 => 967,  2951 => 966,  2948 => 965,  2942 => 963,  2940 => 962,  2937 => 961,  2931 => 959,  2929 => 958,  2926 => 957,  2920 => 955,  2918 => 954,  2915 => 953,  2909 => 951,  2907 => 950,  2904 => 949,  2898 => 947,  2896 => 946,  2893 => 945,  2887 => 943,  2885 => 942,  2882 => 941,  2876 => 939,  2874 => 938,  2871 => 937,  2865 => 935,  2863 => 934,  2856 => 929,  2853 => 928,  2841 => 927,  2822 => 921,  2818 => 920,  2813 => 919,  2810 => 918,  2808 => 917,  2804 => 915,  2801 => 914,  2798 => 913,  2784 => 912,  2764 => 909,  2761 => 908,  2749 => 907,  2730 => 902,  2727 => 901,  2724 => 900,  2721 => 899,  2719 => 898,  2713 => 894,  2705 => 892,  2702 => 891,  2699 => 890,  2696 => 889,  2693 => 888,  2690 => 887,  2687 => 886,  2681 => 884,  2678 => 883,  2670 => 881,  2668 => 880,  2663 => 877,  2657 => 875,  2651 => 873,  2649 => 872,  2644 => 869,  2642 => 868,  2639 => 867,  2636 => 866,  2633 => 865,  2630 => 864,  2627 => 863,  2625 => 862,  2622 => 861,  2619 => 860,  2616 => 859,  2613 => 858,  2610 => 857,  2608 => 856,  2605 => 855,  2602 => 854,  2599 => 853,  2584 => 852,  2568 => 849,  2565 => 848,  2559 => 846,  2557 => 845,  2554 => 844,  2551 => 843,  2548 => 842,  2545 => 841,  2542 => 840,  2540 => 839,  2537 => 838,  2534 => 837,  2519 => 836,  2503 => 833,  2500 => 832,  2494 => 830,  2492 => 829,  2489 => 828,  2486 => 827,  2483 => 826,  2480 => 825,  2477 => 824,  2475 => 823,  2472 => 822,  2469 => 821,  2455 => 820,  2439 => 816,  2435 => 814,  2432 => 813,  2429 => 812,  2426 => 811,  2423 => 810,  2420 => 809,  2414 => 807,  2408 => 805,  2405 => 804,  2402 => 803,  2400 => 802,  2397 => 801,  2394 => 800,  2391 => 799,  2388 => 798,  2385 => 797,  2382 => 796,  2379 => 794,  2377 => 793,  2374 => 792,  2371 => 791,  2368 => 790,  2365 => 789,  2362 => 788,  2359 => 787,  2356 => 786,  2353 => 785,  2350 => 784,  2348 => 783,  2345 => 782,  2343 => 781,  2340 => 780,  2337 => 779,  2334 => 778,  2332 => 777,  2329 => 776,  2315 => 775,  2298 => 772,  2295 => 771,  2289 => 769,  2287 => 768,  2282 => 766,  2278 => 765,  2274 => 764,  2270 => 763,  2266 => 762,  2263 => 761,  2260 => 760,  2257 => 759,  2254 => 758,  2251 => 757,  2249 => 756,  2246 => 755,  2243 => 754,  2229 => 753,  2212 => 747,  2210 => 746,  2207 => 745,  2205 => 744,  2203 => 743,  2201 => 742,  2198 => 740,  2193 => 737,  2191 => 736,  2186 => 735,  2183 => 734,  2178 => 732,  2174 => 731,  2169 => 730,  2166 => 729,  2163 => 727,  2157 => 725,  2155 => 724,  2149 => 722,  2146 => 720,  2140 => 717,  2137 => 716,  2131 => 714,  2128 => 713,  2122 => 711,  2119 => 710,  2113 => 708,  2110 => 707,  2104 => 705,  2102 => 704,  2099 => 703,  2093 => 701,  2091 => 700,  2088 => 699,  2082 => 697,  2079 => 696,  2076 => 694,  2074 => 693,  2071 => 692,  2067 => 690,  2064 => 688,  2061 => 687,  2058 => 686,  2054 => 684,  2045 => 682,  2041 => 681,  2034 => 676,  2031 => 675,  2025 => 673,  2022 => 672,  2019 => 671,  2016 => 669,  2014 => 668,  2011 => 666,  2008 => 665,  2006 => 664,  2003 => 663,  1999 => 661,  1993 => 659,  1991 => 658,  1988 => 657,  1982 => 655,  1980 => 654,  1977 => 653,  1971 => 651,  1969 => 650,  1966 => 649,  1960 => 647,  1957 => 646,  1951 => 644,  1949 => 643,  1943 => 641,  1941 => 640,  1938 => 639,  1935 => 638,  1929 => 636,  1927 => 635,  1924 => 634,  1921 => 633,  1918 => 632,  1915 => 631,  1912 => 630,  1909 => 629,  1907 => 628,  1901 => 626,  1898 => 625,  1896 => 623,  1895 => 622,  1893 => 621,  1891 => 620,  1889 => 619,  1886 => 618,  1880 => 616,  1874 => 614,  1872 => 612,  1871 => 611,  1870 => 610,  1867 => 609,  1861 => 607,  1859 => 606,  1856 => 605,  1853 => 604,  1847 => 602,  1844 => 601,  1842 => 600,  1840 => 599,  1835 => 597,  1830 => 596,  1828 => 595,  1825 => 594,  1822 => 593,  1814 => 588,  1811 => 587,  1808 => 586,  1806 => 585,  1796 => 583,  1794 => 582,  1791 => 581,  1788 => 580,  1785 => 579,  1782 => 578,  1779 => 577,  1776 => 576,  1773 => 574,  1770 => 573,  1767 => 572,  1764 => 571,  1761 => 570,  1759 => 569,  1756 => 568,  1753 => 567,  1750 => 566,  1748 => 565,  1745 => 564,  1742 => 563,  1739 => 562,  1737 => 561,  1734 => 560,  1731 => 559,  1728 => 558,  1725 => 557,  1722 => 556,  1720 => 555,  1717 => 554,  1714 => 553,  1711 => 552,  1709 => 551,  1706 => 550,  1703 => 549,  1701 => 548,  1698 => 547,  1695 => 546,  1692 => 545,  1689 => 544,  1686 => 543,  1683 => 542,  1680 => 541,  1677 => 540,  1675 => 539,  1672 => 538,  1669 => 537,  1666 => 536,  1663 => 535,  1660 => 534,  1658 => 533,  1655 => 532,  1652 => 531,  1649 => 530,  1646 => 529,  1643 => 528,  1641 => 527,  1638 => 526,  1635 => 525,  1632 => 524,  1629 => 523,  1626 => 522,  1624 => 521,  1621 => 520,  1619 => 519,  1617 => 518,  1615 => 517,  1613 => 516,  1611 => 515,  1609 => 514,  1607 => 513,  1605 => 512,  1602 => 510,  1599 => 509,  1596 => 508,  1593 => 507,  1590 => 506,  1587 => 505,  1584 => 503,  1581 => 502,  1578 => 501,  1575 => 500,  1572 => 499,  1569 => 498,  1566 => 497,  1563 => 496,  1560 => 495,  1557 => 493,  1554 => 492,  1551 => 491,  1548 => 490,  1545 => 489,  1543 => 488,  1540 => 487,  1537 => 486,  1534 => 485,  1531 => 484,  1528 => 483,  1526 => 482,  1523 => 481,  1520 => 480,  1517 => 479,  1514 => 478,  1511 => 477,  1509 => 474,  1508 => 473,  1507 => 472,  1506 => 470,  1505 => 469,  1501 => 467,  1498 => 466,  1495 => 465,  1492 => 464,  1489 => 463,  1487 => 462,  1484 => 461,  1481 => 460,  1478 => 459,  1476 => 458,  1473 => 457,  1471 => 456,  1468 => 455,  1450 => 454,  1432 => 449,  1429 => 448,  1423 => 446,  1421 => 445,  1418 => 444,  1416 => 443,  1413 => 442,  1400 => 441,  1384 => 438,  1377 => 434,  1373 => 432,  1370 => 431,  1367 => 430,  1364 => 429,  1362 => 428,  1359 => 427,  1357 => 426,  1354 => 425,  1352 => 424,  1349 => 423,  1336 => 422,  1320 => 419,  1314 => 417,  1311 => 416,  1303 => 414,  1300 => 413,  1292 => 411,  1289 => 410,  1286 => 409,  1283 => 408,  1270 => 407,  1252 => 402,  1246 => 400,  1243 => 399,  1241 => 398,  1235 => 396,  1232 => 395,  1230 => 394,  1224 => 392,  1221 => 391,  1219 => 390,  1213 => 388,  1210 => 387,  1205 => 383,  1202 => 382,  1199 => 381,  1196 => 380,  1194 => 379,  1191 => 378,  1189 => 377,  1186 => 376,  1173 => 375,  1157 => 370,  1155 => 369,  1148 => 366,  1145 => 364,  1137 => 362,  1135 => 361,  1129 => 358,  1125 => 357,  1122 => 356,  1118 => 354,  1116 => 353,  1111 => 350,  1101 => 348,  1099 => 347,  1095 => 345,  1084 => 336,  1079 => 335,  1074 => 331,  1071 => 329,  1066 => 328,  1063 => 327,  1060 => 326,  1057 => 325,  1054 => 324,  1051 => 323,  1049 => 322,  1045 => 320,  1042 => 319,  1039 => 318,  1036 => 317,  1033 => 316,  1030 => 315,  1027 => 314,  1024 => 313,  1021 => 312,  1018 => 311,  1015 => 310,  1012 => 309,  1009 => 308,  1006 => 307,  1003 => 306,  1000 => 305,  997 => 304,  994 => 303,  991 => 302,  988 => 301,  985 => 300,  982 => 299,  979 => 298,  975 => 297,  972 => 295,  969 => 294,  966 => 293,  951 => 292,  935 => 289,  929 => 285,  926 => 284,  920 => 282,  914 => 280,  911 => 279,  908 => 278,  905 => 277,  900 => 274,  897 => 273,  891 => 269,  885 => 266,  881 => 265,  877 => 264,  874 => 263,  872 => 262,  867 => 260,  863 => 258,  860 => 257,  857 => 256,  854 => 255,  851 => 254,  848 => 253,  845 => 252,  842 => 251,  839 => 250,  837 => 249,  834 => 248,  831 => 247,  828 => 246,  825 => 245,  822 => 244,  819 => 243,  816 => 242,  813 => 241,  811 => 240,  808 => 239,  805 => 238,  802 => 237,  800 => 236,  797 => 235,  794 => 234,  791 => 233,  789 => 232,  787 => 231,  784 => 230,  781 => 229,  778 => 228,  775 => 227,  773 => 226,  770 => 225,  767 => 223,  764 => 222,  761 => 221,  758 => 220,  755 => 219,  753 => 218,  750 => 217,  748 => 216,  745 => 215,  742 => 214,  739 => 213,  736 => 212,  734 => 211,  731 => 210,  728 => 209,  725 => 208,  722 => 207,  719 => 206,  717 => 205,  714 => 204,  712 => 203,  709 => 202,  707 => 201,  704 => 200,  686 => 199,  664 => 188,  656 => 182,  652 => 179,  649 => 178,  647 => 177,  644 => 176,  628 => 175,  607 => 167,  601 => 164,  595 => 161,  592 => 160,  578 => 159,  558 => 154,  554 => 153,  550 => 152,  546 => 151,  542 => 150,  536 => 148,  534 => 147,  528 => 145,  526 => 144,  523 => 143,  521 => 142,  519 => 141,  517 => 140,  515 => 139,  513 => 138,  511 => 137,  509 => 136,  507 => 135,  505 => 134,  503 => 133,  501 => 132,  499 => 131,  497 => 130,  495 => 129,  493 => 128,  491 => 127,  489 => 126,  487 => 125,  485 => 124,  483 => 123,  481 => 122,  479 => 121,  477 => 120,  475 => 119,  473 => 118,  471 => 117,  469 => 116,  467 => 115,  465 => 114,  463 => 113,  461 => 112,  459 => 111,  457 => 110,  455 => 109,  453 => 108,  451 => 107,  449 => 106,  447 => 105,  445 => 104,  443 => 103,  441 => 102,  439 => 101,  437 => 100,  435 => 99,  433 => 98,  431 => 97,  429 => 96,  427 => 95,  425 => 94,  423 => 93,  421 => 92,  419 => 91,  417 => 90,  415 => 89,  413 => 88,  411 => 87,  409 => 86,  407 => 85,  405 => 84,  398 => 78,  394 => 75,  392 => 74,  389 => 73,  378 => 72,  358 => 65,  354 => 63,  351 => 62,  348 => 61,  346 => 60,  344 => 59,  333 => 58,  315 => 52,  312 => 51,  306 => 49,  303 => 48,  297 => 46,  294 => 45,  292 => 44,  287 => 41,  284 => 40,  276 => 34,  273 => 33,  262 => 32,  246 => 30,  243 => 29,  240 => 28,  237 => 27,  231 => 25,  228 => 24,  225 => 23,  223 => 22,  220 => 21,  217 => 20,  214 => 19,  211 => 18,  200 => 17,  195 => 2272,  193 => 2271,  191 => 2270,  189 => 2269,  187 => 2268,  185 => 2267,  183 => 2266,  181 => 2265,  179 => 2264,  177 => 2263,  175 => 2262,  173 => 2261,  171 => 2260,  169 => 2259,  167 => 2258,  164 => 2256,  162 => 2255,  160 => 2254,  157 => 2251,  153 => 2144,  149 => 2139,  145 => 2118,  139 => 2005,  135 => 1970,  131 => 1879,  127 => 1844,  123 => 1802,  119 => 1775,  115 => 1406,  109 => 1392,  106 => 1353,  103 => 1309,  99 => 1251,  96 => 1196,  92 => 1094,  88 => 1016,  85 => 980,  81 => 925,  78 => 911,  74 => 905,  71 => 851,  68 => 835,  64 => 818,  61 => 774,  57 => 750,  53 => 452,  50 => 440,  47 => 421,  44 => 406,  39 => 372,  36 => 291,  33 => 195,  30 => 173,  26 => 70,  22 => 56,  19 => 16,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "OlegUserdirectoryBundle::Default/usermacros.html.twig", "C:\\Users\\ch3\\Documents\\MyDocs\\WCMC\\ORDER\\scanorder\\Scanorders2\\src\\Oleg\\UserdirectoryBundle/Resources/views/Default/usermacros.html.twig");
    }
}
