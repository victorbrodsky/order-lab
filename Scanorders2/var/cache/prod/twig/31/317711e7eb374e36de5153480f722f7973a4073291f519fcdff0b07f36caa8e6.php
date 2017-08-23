<?php

/* OlegUserdirectoryBundle:AccessRequest:access_request_management.html.twig */
class __TwigTemplate_4d85d10cf9f8413379653496c83d7e1f5b15a69a244657005d0dd08f958633ff extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->blocks = array(
            'title' => array($this, 'block_title'),
            'content' => array($this, 'block_content'),
            'additionalcss' => array($this, 'block_additionalcss'),
        );
    }

    protected function doGetParent(array $context)
    {
        // line 35
        return $this->loadTemplate((isset($context["extendStr"]) ? $context["extendStr"] : null), "OlegUserdirectoryBundle:AccessRequest:access_request_management.html.twig", 35);
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 19
        if (((isset($context["sitename"]) ? $context["sitename"] : null) == "employees")) {
            // line 20
            $context["extendStr"] = "OlegUserdirectoryBundle::Default/base.html.twig";
        } elseif ((        // line 21
(isset($context["sitename"]) ? $context["sitename"] : null) == "fellapp")) {
            // line 22
            $context["extendStr"] = "OlegFellAppBundle::Default/base.html.twig";
        } elseif ((        // line 23
(isset($context["sitename"]) ? $context["sitename"] : null) == "deidentifier")) {
            // line 24
            $context["extendStr"] = "OlegDeidentifierBundle::Default/base.html.twig";
        } elseif ((        // line 25
(isset($context["sitename"]) ? $context["sitename"] : null) == "scan")) {
            // line 26
            $context["extendStr"] = "OlegOrderformBundle::Default/base.html.twig";
        } elseif ((        // line 27
(isset($context["sitename"]) ? $context["sitename"] : null) == "vacreq")) {
            // line 28
            $context["extendStr"] = "OlegVacReqBundle::Default/base.html.twig";
        } elseif ((        // line 29
(isset($context["sitename"]) ? $context["sitename"] : null) == "calllog")) {
            // line 30
            $context["extendStr"] = "OlegCallLogBundle::Default/base.html.twig";
        } elseif ((        // line 31
(isset($context["sitename"]) ? $context["sitename"] : null) == "translationalresearch")) {
            // line 32
            $context["extendStr"] = "OlegTranslationalResearchBundle::Default/base.html.twig";
        }
        // line 39
        if (array_key_exists("accreq", $context)) {
            // line 40
            if (($this->getAttribute((isset($context["accreq"]) ? $context["accreq"] : null), "status", array()) == 0)) {
                // line 42
                $context["titleStr"] = (("This user has an active request to access the " . (isset($context["sitenamefull"]) ? $context["sitenamefull"] : null)) . " site. You can update the role and other details below.");
            } elseif (($this->getAttribute(            // line 43
(isset($context["accreq"]) ? $context["accreq"] : null), "status", array()) == 1)) {
                // line 45
                $context["titleStr"] = (("This user has declined to request access to the " . (isset($context["sitenamefull"]) ? $context["sitenamefull"] : null)) . " site. You can update the role and other details below.");
            } elseif (($this->getAttribute(            // line 46
(isset($context["accreq"]) ? $context["accreq"] : null), "status", array()) == 2)) {
                // line 48
                $context["titleStr"] = (("This user is already authorized to access the " . (isset($context["sitenamefull"]) ? $context["sitenamefull"] : null)) . " site. You can update the role and other details below.");
            } else {
                // line 50
                $context["titleStr"] = ("Specify role and accessible data for the user being authorized to access the " . (isset($context["sitenamefull"]) ? $context["sitenamefull"] : null));
            }
        } else {
            // line 53
            $context["titleStr"] = ("Specify role and accessible data for the user being authorized to access the " . (isset($context["sitenamefull"]) ? $context["sitenamefull"] : null));
        }
        // line 35
        $this->getParent($context)->display($context, array_merge($this->blocks, $blocks));
    }

    // line 56
    public function block_title($context, array $blocks = array())
    {
        // line 57
        echo "    ";
        echo twig_escape_filter($this->env, (isset($context["titleStr"]) ? $context["titleStr"] : null), "html", null, true);
        echo "
";
    }

    // line 62
    public function block_content($context, array $blocks = array())
    {
        // line 63
        echo "
    ";
        // line 64
        $context["formmacros"] = $this->loadTemplate("OlegOrderformBundle::Default/formmacros.html.twig", "OlegUserdirectoryBundle:AccessRequest:access_request_management.html.twig", 64);
        // line 65
        echo "    ";
        $context["userform"] = $this->loadTemplate("OlegUserdirectoryBundle::Default/userformmacros.html.twig", "OlegUserdirectoryBundle:AccessRequest:access_request_management.html.twig", 65);
        // line 66
        echo "
    <h3 class=\"text-info\">
        ";
        // line 68
        echo twig_escape_filter($this->env, (isset($context["titleStr"]) ? $context["titleStr"] : null), "html", null, true);
        echo "
    </h3>

    <br>

    ";
        // line 73
        echo $context["userform"]->getsnapshot_steve((isset($context["entity"]) ? $context["entity"] : null), (isset($context["sitenameshowuser"]) ? $context["sitenameshowuser"] : null), "edit");
        echo "

    <hr>

    ";
        // line 77
        if (array_key_exists("accreq", $context)) {
            // line 78
            echo "
        ";
            // line 79
            echo $context["formmacros"]->getsimplefield("Request ID:", $this->getAttribute((isset($context["accreq"]) ? $context["accreq"] : null), "id", array()), "", "disabled");
            echo "
        ";
            // line 80
            echo $context["formmacros"]->getsimplefield("Request Date:", twig_date_format_filter($this->env, $this->getAttribute((isset($context["accreq"]) ? $context["accreq"] : null), "createdate", array()), "Y-m-d H:i:s"), "", "disabled");
            echo "
        ";
            // line 81
            echo $context["formmacros"]->getsimplefield("Request Status:", twig_capitalize_string_filter($this->env, $this->getAttribute((isset($context["accreq"]) ? $context["accreq"] : null), "getStatusStr", array())), "", "disabled");
            echo "
        ";
            // line 83
            echo "        ";
            // line 84
            echo "        ";
            // line 85
            echo "        ";
            // line 86
            echo "        ";
            // line 87
            echo "        ";
            echo $context["formmacros"]->getsimplefield("Last Login:", twig_date_format_filter($this->env, $this->getAttribute((isset($context["entity"]) ? $context["entity"] : null), "lastLogin", array()), "Y-m-d H:i"), "", "disabled");
            echo "
        ";
            // line 88
            echo $context["formmacros"]->getsimplefield("Status Updated On:", twig_date_format_filter($this->env, $this->getAttribute((isset($context["accreq"]) ? $context["accreq"] : null), "updatedate", array()), "Y-m-d H:i"), "", "disabled");
            echo "

        ";
            // line 90
            $context["updatedby"] = (((("<a href=\"" . $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath(((isset($context["sitenameshowuser"]) ? $context["sitenameshowuser"] : null) . "_showuser"), array("id" => $this->getAttribute($this->getAttribute((isset($context["accreq"]) ? $context["accreq"] : null), "updatedby", array()), "id", array())))) . "\">") . $this->getAttribute((isset($context["accreq"]) ? $context["accreq"] : null), "updatedby", array())) . "</a>");
            // line 91
            echo "        ";
            echo $context["formmacros"]->getsimplefield("Status Updated By:", (isset($context["updatedby"]) ? $context["updatedby"] : null), "", "disabled");
            echo "

        <hr>
            ";
            // line 95
            echo "            <p>Access Request Details</p>
            ";
            // line 96
            echo $context["formmacros"]->getsimplefield("First Name:", $this->getAttribute((isset($context["accreq"]) ? $context["accreq"] : null), "firstName", array()), "", "disabled");
            echo "
            ";
            // line 97
            echo $context["formmacros"]->getsimplefield("Last Name:", $this->getAttribute((isset($context["accreq"]) ? $context["accreq"] : null), "lastName", array()), "", "disabled");
            echo "
            ";
            // line 98
            echo $context["formmacros"]->getsimplefield("Email:", $this->getAttribute((isset($context["accreq"]) ? $context["accreq"] : null), "email", array()), "", "disabled");
            echo "
            ";
            // line 99
            echo $context["formmacros"]->getsimplefield("Phone Number:", $this->getAttribute((isset($context["accreq"]) ? $context["accreq"] : null), "phone", array()), "", "disabled");
            echo "
            ";
            // line 100
            echo $context["formmacros"]->getsimplefield("Job title:", $this->getAttribute((isset($context["accreq"]) ? $context["accreq"] : null), "job", array()), "", "disabled");
            echo "
            ";
            // line 101
            echo $context["formmacros"]->getsimplefield("Organizational Group:", $this->getAttribute((isset($context["accreq"]) ? $context["accreq"] : null), "organizationalGroup", array()), "", "disabled");
            echo "
            ";
            // line 102
            echo $context["formmacros"]->getsimplefield("Reason for access request:", $this->getAttribute((isset($context["accreq"]) ? $context["accreq"] : null), "reason", array()), "", "disabled");
            echo "
            ";
            // line 103
            echo $context["formmacros"]->getsimplefield("Access permissions similar to (user name):", $this->getAttribute((isset($context["accreq"]) ? $context["accreq"] : null), "similaruser", array()), "", "disabled");
            echo "
            ";
            // line 104
            echo $context["formmacros"]->getsimplefield("Reference Name:", $this->getAttribute((isset($context["accreq"]) ? $context["accreq"] : null), "referencename", array()), "", "disabled");
            echo "
            ";
            // line 105
            echo $context["formmacros"]->getsimplefield("Reference Email:", $this->getAttribute((isset($context["accreq"]) ? $context["accreq"] : null), "referenceemail", array()), "", "disabled");
            echo "
            ";
            // line 106
            echo $context["formmacros"]->getsimplefield("Reference Phone Number:", $this->getAttribute((isset($context["accreq"]) ? $context["accreq"] : null), "referencephone", array()), "", "disabled");
            echo "
        <hr>
    ";
        }
        // line 109
        echo "
    ";
        // line 111
        echo "    ";
        if (((isset($context["routenameshort"]) ? $context["routenameshort"] : null) == "accessrequest_management")) {
            // line 112
            echo "        ";
            $context["actionPath"] = $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath(((isset($context["sitename"]) ? $context["sitename"] : null) . "_accessrequest_management_submit"), array("id" => $this->getAttribute((isset($context["accreq"]) ? $context["accreq"] : null), "id", array())));
            // line 113
            echo "    ";
        } else {
            // line 114
            echo "        ";
            $context["actionPath"] = $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath(((isset($context["sitename"]) ? $context["sitename"] : null) . "_authorization_user_management_submit"), array("id" => $this->getAttribute((isset($context["entity"]) ? $context["entity"] : null), "id", array())));
            // line 115
            echo "    ";
        }
        // line 116
        echo "

    <form id=\"accessrequest_management\" action=\"";
        // line 118
        echo twig_escape_filter($this->env, (isset($context["actionPath"]) ? $context["actionPath"] : null), "html", null, true);
        echo "\" method=\"POST\">

        ";
        // line 121
        echo "            ";
        // line 122
        echo "                ";
        // line 123
        echo "            ";
        // line 124
        echo "        ";
        // line 125
        echo "            ";
        echo $context["formmacros"]->getfield($this->getAttribute($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "perSiteSettings", array()), "permittedInstitutionalPHIScope", array()));
        echo "
        ";
        // line 127
        echo "
        ";
        // line 128
        echo $context["formmacros"]->getfield($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "roles", array()));
        echo "

        ";
        // line 130
        echo $context["formmacros"]->getcheckbox($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "emailNotification", array()));
        echo "

        <hr>

        <br>

        <div class=\"row\">

            ";
        // line 139
        echo "            ";
        if (((isset($context["routenameshort"]) ? $context["routenameshort"] : null) == "accessrequest_management")) {
            // line 140
            echo "                <div class=\"col-xs-6\" align=\"right\">
                    ";
            // line 142
            echo "                    <button name=\"accessrequest-approve\" type='submit' class=\"btn btn-info btn-sm\">Update</button>
                </div>

                <div class=\"col-xs-6\" align=\"left\">
                    ";
            // line 147
            echo "                    <button
                        name=\"accessrequest-decline\" type='submit' class=\"btn btn-danger btn-sm\"
                        onclick=\"return confirm('You are about to stop ";
            // line 149
            echo twig_escape_filter($this->env, (isset($context["entity"]) ? $context["entity"] : null), "html", null, true);
            echo " from being able to log in and use the ";
            echo twig_escape_filter($this->env, (isset($context["sitenamefull"]) ? $context["sitenamefull"] : null), "html", null, true);
            echo " site')\"
                    >Revoke</button>
                </div>
            ";
        } else {
            // line 153
            echo "
                ";
            // line 154
            $context["updateBtnName"] = "Authorize User's Access";
            // line 155
            echo "                ";
            if ((twig_length_filter($this->env, (isset($context["siteRoles"]) ? $context["siteRoles"] : null)) > 0)) {
                // line 156
                echo "                    ";
                $context["updateBtnName"] = "Update";
                // line 157
                echo "                ";
            }
            // line 158
            echo "
                <div class=\"col-xs-6\" align=\"right\">
                    <button name=\"accessrequest-approve\" type='submit' class=\"btn btn-info btn-sm\">";
            // line 160
            echo twig_escape_filter($this->env, (isset($context["updateBtnName"]) ? $context["updateBtnName"] : null), "html", null, true);
            echo "</button>
                </div>

                <div class=\"col-xs-6\" align=\"left\">
                    <a class=\"btn btn-danger btn-sm\" href=\"";
            // line 164
            echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath(((isset($context["sitename"]) ? $context["sitename"] : null) . "_authorized_users"));
            echo "\">Cancel</a>
                </div>
            ";
        }
        // line 167
        echo "
        </div>

    </form>



";
    }

    // line 177
    public function block_additionalcss($context, array $blocks = array())
    {
        // line 178
        echo "    ";
        if (isset($context['assetic']['debug']) && $context['assetic']['debug']) {
            // asset "3a5881f_0"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("_assetic_3a5881f_0") : $this->env->getExtension('Symfony\Bridge\Twig\Extension\AssetExtension')->getAssetUrl("css/3a5881f_steve-snapshot_1.css");
            // line 181
            echo "        <link rel=\"stylesheet\" href=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : null), "html", null, true);
            echo "\" />
    ";
        } else {
            // asset "3a5881f"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("_assetic_3a5881f") : $this->env->getExtension('Symfony\Bridge\Twig\Extension\AssetExtension')->getAssetUrl("css/3a5881f.css");
            echo "        <link rel=\"stylesheet\" href=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : null), "html", null, true);
            echo "\" />
    ";
        }
        unset($context["asset_url"]);
    }

    public function getTemplateName()
    {
        return "OlegUserdirectoryBundle:AccessRequest:access_request_management.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  348 => 181,  343 => 178,  340 => 177,  329 => 167,  323 => 164,  316 => 160,  312 => 158,  309 => 157,  306 => 156,  303 => 155,  301 => 154,  298 => 153,  289 => 149,  285 => 147,  279 => 142,  276 => 140,  273 => 139,  262 => 130,  257 => 128,  254 => 127,  249 => 125,  247 => 124,  245 => 123,  243 => 122,  241 => 121,  236 => 118,  232 => 116,  229 => 115,  226 => 114,  223 => 113,  220 => 112,  217 => 111,  214 => 109,  208 => 106,  204 => 105,  200 => 104,  196 => 103,  192 => 102,  188 => 101,  184 => 100,  180 => 99,  176 => 98,  172 => 97,  168 => 96,  165 => 95,  158 => 91,  156 => 90,  151 => 88,  146 => 87,  144 => 86,  142 => 85,  140 => 84,  138 => 83,  134 => 81,  130 => 80,  126 => 79,  123 => 78,  121 => 77,  114 => 73,  106 => 68,  102 => 66,  99 => 65,  97 => 64,  94 => 63,  91 => 62,  84 => 57,  81 => 56,  77 => 35,  74 => 53,  70 => 50,  67 => 48,  65 => 46,  63 => 45,  61 => 43,  59 => 42,  57 => 40,  55 => 39,  52 => 32,  50 => 31,  48 => 30,  46 => 29,  44 => 28,  42 => 27,  40 => 26,  38 => 25,  36 => 24,  34 => 23,  32 => 22,  30 => 21,  28 => 20,  26 => 19,  20 => 35,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "OlegUserdirectoryBundle:AccessRequest:access_request_management.html.twig", "C:\\Users\\ch3\\Documents\\MyDocs\\WCMC\\ORDER\\scanorder\\Scanorders2\\src\\Oleg\\UserdirectoryBundle/Resources/views/AccessRequest/access_request_management.html.twig");
    }
}
