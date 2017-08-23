<?php

/* OlegUserdirectoryBundle:Security:login.html.twig */
class __TwigTemplate_0978b2f132c782c25ec6694909593af0c6d7e2e25b23c92d889cb1d18118159b extends Twig_Template
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
<!DOCTYPE html>

<html>

    <head>

        ";
        // line 23
        if (isset($context['assetic']['debug']) && $context['assetic']['debug']) {
            // asset "ea64be1_0"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("_assetic_ea64be1_0") : $this->env->getExtension('Symfony\Bridge\Twig\Extension\AssetExtension')->getAssetUrl("css/ea64be1_pnotify.custom.min_1.css");
            // line 29
            echo "            <link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : null), "html", null, true);
            echo "\" />
        ";
            // asset "ea64be1_1"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("_assetic_ea64be1_1") : $this->env->getExtension('Symfony\Bridge\Twig\Extension\AssetExtension')->getAssetUrl("css/ea64be1_part_2_bootstrap-theme.min_1.css");
            echo "            <link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : null), "html", null, true);
            echo "\" />
        ";
            // asset "ea64be1_2"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("_assetic_ea64be1_2") : $this->env->getExtension('Symfony\Bridge\Twig\Extension\AssetExtension')->getAssetUrl("css/ea64be1_part_2_bootstrap.min_2.css");
            echo "            <link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : null), "html", null, true);
            echo "\" />
        ";
            // asset "ea64be1_3"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("_assetic_ea64be1_3") : $this->env->getExtension('Symfony\Bridge\Twig\Extension\AssetExtension')->getAssetUrl("css/ea64be1_form_3.css");
            echo "            <link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : null), "html", null, true);
            echo "\" />
        ";
            // asset "ea64be1_4"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("_assetic_ea64be1_4") : $this->env->getExtension('Symfony\Bridge\Twig\Extension\AssetExtension')->getAssetUrl("css/ea64be1_select2_4.css");
            echo "            <link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : null), "html", null, true);
            echo "\" />
        ";
        } else {
            // asset "ea64be1"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("_assetic_ea64be1") : $this->env->getExtension('Symfony\Bridge\Twig\Extension\AssetExtension')->getAssetUrl("css/ea64be1.css");
            echo "            <link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : null), "html", null, true);
            echo "\" />
        ";
        }
        unset($context["asset_url"]);
        // line 31
        echo "

        ";
        // line 34
        echo "        ";
        // line 35
        echo "        ";
        $context["favicon"] = "glyphicons-157-show-thumbnails.png";
        // line 36
        echo "
        ";
        // line 37
        if (((isset($context["sitename"]) ? $context["sitename"] : null) == (isset($context["employees_sitename"]) ? $context["employees_sitename"] : null))) {
            // line 38
            echo "            ";
            $context["title"] = "Employee Directory";
            // line 39
            echo "            ";
            $context["favicon"] = "bundles/oleguserdirectory/form/img/users-1-64x64.png";
            // line 40
            echo "        ";
        }
        // line 41
        echo "
        ";
        // line 42
        if (((isset($context["sitename"]) ? $context["sitename"] : null) == (isset($context["scan_sitename"]) ? $context["scan_sitename"] : null))) {
            // line 43
            echo "            ";
            $context["title"] = "Scan Orders";
            // line 44
            echo "            ";
            $context["favicon"] = "favicon.ico";
            // line 45
            echo "        ";
        }
        // line 46
        echo "
        ";
        // line 47
        if (((isset($context["sitename"]) ? $context["sitename"] : null) == (isset($context["fellapp_sitename"]) ? $context["fellapp_sitename"] : null))) {
            // line 48
            echo "            ";
            $context["title"] = "Fellowship Applications";
            // line 49
            echo "            ";
            $context["favicon"] = "glyphicons-37-file.png";
            // line 50
            echo "        ";
        }
        // line 51
        echo "
        ";
        // line 52
        if (((isset($context["sitename"]) ? $context["sitename"] : null) == (isset($context["deidentifier_sitename"]) ? $context["deidentifier_sitename"] : null))) {
            // line 53
            echo "            ";
            $context["title"] = "Deidentifier";
            // line 54
            echo "            ";
            $context["favicon"] = "glyphicons-81-retweet.png";
            // line 55
            echo "        ";
        }
        // line 56
        echo "
        ";
        // line 57
        if (((isset($context["sitename"]) ? $context["sitename"] : null) == (isset($context["vacreq_sitename"]) ? $context["vacreq_sitename"] : null))) {
            // line 58
            echo "            ";
            $context["title"] = "Vacation Request";
            // line 59
            echo "            ";
            $context["favicon"] = "glyphicons-39-plane.png";
            // line 60
            echo "        ";
        }
        // line 61
        echo "
        ";
        // line 62
        if (((isset($context["sitename"]) ? $context["sitename"] : null) == (isset($context["calllog_sitename"]) ? $context["calllog_sitename"] : null))) {
            // line 63
            echo "            ";
            $context["title"] = "Call Log Book";
            // line 64
            echo "            ";
            $context["favicon"] = "glyphicons-442-phone-alt.png";
            // line 65
            echo "        ";
        }
        // line 66
        echo "
        ";
        // line 67
        if (((isset($context["sitename"]) ? $context["sitename"] : null) == (isset($context["translationalresearch_sitename"]) ? $context["translationalresearch_sitename"] : null))) {
            // line 68
            echo "            ";
            $context["title"] = "HemePath Translational Research";
            // line 69
            echo "            ";
            $context["favicon"] = "glyphicons-342-briefcase.png";
            // line 70
            echo "        ";
        }
        // line 71
        echo "
        <title>";
        // line 72
        echo twig_escape_filter($this->env, (isset($context["title"]) ? $context["title"] : null), "html", null, true);
        echo "</title>

        ";
        // line 75
        echo "
        <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge,chrome=1\">
        <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">

        ";
        // line 80
        echo "        ";
        // line 81
        echo "
        ";
        // line 82
        $context["usermacros"] = $this->loadTemplate("OlegUserdirectoryBundle::Default/usermacros.html.twig", "OlegUserdirectoryBundle:Security:login.html.twig", 82);
        // line 83
        echo "        ";
        echo $context["usermacros"]->getnonLiveSiteRedirect();
        echo "

        <link rel=\"icon\" type=\"image/x-icon\" href=\"";
        // line 85
        echo twig_escape_filter($this->env, $this->env->getExtension('Symfony\Bridge\Twig\Extension\AssetExtension')->getAssetUrl((isset($context["favicon"]) ? $context["favicon"] : null)), "html", null, true);
        echo "\" />

    </head>

    <body>

        <div class=\"container text-center\">

            <input type=\"hidden\" id=\"baseurl\" value=\"";
        // line 93
        echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute((isset($context["app"]) ? $context["app"] : null), "request", array()), "host", array()), "html", null, true);
        echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute((isset($context["app"]) ? $context["app"] : null), "request", array()), "getBaseURL", array(), "method"), "html", null, true);
        echo "\" />

            ";
        // line 95
        $this->loadTemplate("OlegUserdirectoryBundle::Security/login_content.html.twig", "OlegUserdirectoryBundle:Security:login.html.twig", 95)->display(array_merge($context, array("sitename" => (isset($context["sitename"]) ? $context["sitename"] : null), "title" => (isset($context["title"]) ? $context["title"] : null))));
        // line 96
        echo "
            ";
        // line 97
        $this->loadTemplate("OlegUserdirectoryBundle::Default/footer.html.twig", "OlegUserdirectoryBundle:Security:login.html.twig", 97)->display($context);
        // line 98
        echo "
        </div> <!-- /container -->

        ";
        // line 101
        if (isset($context['assetic']['debug']) && $context['assetic']['debug']) {
            // asset "8bafbdf_0"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("_assetic_8bafbdf_0") : $this->env->getExtension('Symfony\Bridge\Twig\Extension\AssetExtension')->getAssetUrl("js/8bafbdf_jquery-1.11.0.min_1.js");
            // line 108
            echo "        <script type=\"text/javascript\" src=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : null), "html", null, true);
            echo "\"></script>
        ";
            // asset "8bafbdf_1"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("_assetic_8bafbdf_1") : $this->env->getExtension('Symfony\Bridge\Twig\Extension\AssetExtension')->getAssetUrl("js/8bafbdf_part_2_bootstrap.min_1.js");
            echo "        <script type=\"text/javascript\" src=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : null), "html", null, true);
            echo "\"></script>
        ";
            // asset "8bafbdf_2"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("_assetic_8bafbdf_2") : $this->env->getExtension('Symfony\Bridge\Twig\Extension\AssetExtension')->getAssetUrl("js/8bafbdf_select2_3.js");
            echo "        <script type=\"text/javascript\" src=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : null), "html", null, true);
            echo "\"></script>
        ";
            // asset "8bafbdf_3"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("_assetic_8bafbdf_3") : $this->env->getExtension('Symfony\Bridge\Twig\Extension\AssetExtension')->getAssetUrl("js/8bafbdf_pnotify.custom.min_4.js");
            echo "        <script type=\"text/javascript\" src=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : null), "html", null, true);
            echo "\"></script>
        ";
            // asset "8bafbdf_4"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("_assetic_8bafbdf_4") : $this->env->getExtension('Symfony\Bridge\Twig\Extension\AssetExtension')->getAssetUrl("js/8bafbdf_user-common_5.js");
            echo "        <script type=\"text/javascript\" src=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : null), "html", null, true);
            echo "\"></script>
        ";
        } else {
            // asset "8bafbdf"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("_assetic_8bafbdf") : $this->env->getExtension('Symfony\Bridge\Twig\Extension\AssetExtension')->getAssetUrl("js/8bafbdf.js");
            echo "        <script type=\"text/javascript\" src=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : null), "html", null, true);
            echo "\"></script>
        ";
        }
        unset($context["asset_url"]);
        // line 110
        echo "

        <script type=\"text/javascript\">
            var _ajaxTimeout = 60000;  //15000 => 15 sec
            //var urlBase = \$(\"#baseurl\").val();
            var url = getCommonBaseUrl(\"setloginvisit/\");\t//urlBase+\"setloginvisit/\";
            //console.log(\"url=\"+url);

            \$(document).ready(function() {

                userPnotifyDisplay();
                regularCombobox();

                \$(\"#login-form\").on(\"submit\", function () {
                    setFullUsername();
                    //alert(\"username=\"+document.getElementById(\"username\").value + \", password=\" + document.getElementById(\"password\").value );
                    return true;
                });

                //cursor would only be moved to the password field if the user name is non-empty
                if( \$('#display-username').val() ) {
                    //console.log('focus password field');
                    \$('#password').focus();
                } else {
                    //console.log('focus username field');
                    \$('#display-username').focus();
                }

                \$.ajax({
                    url: url,
                    type: 'GET',
                    async: true,
                    timeout: _ajaxTimeout,
                    data: { display_height: screen.height, display_width: screen.width },
                    success: function (data) {
                        //console.debug('send browser data ok');
                    },
                    error: function (x , t, m) {
                        if( t === \"timeout\" ) {
                            getAjaxTimeoutMsg();
                        }
                        //console.debug('send browser data error');
                    }
                });

            });

            //append usernametype to username
            function setFullUsername() {

                var usertypeAbbreviation = \$('#usernametypeid_show').select2('val');
                if( !usertypeAbbreviation || usertypeAbbreviation == \"\" || usertypeAbbreviation === null || typeof usertypeAbbreviation === 'object' ) {
                    //return;
                    usertypeAbbreviation = 'wcmc-cwid';
                }

                //console.log(usertypeAbbreviation);
                //alert(usertypeAbbreviation);

                var username = \$('#display-username').val();
                if( !username || username == \"\" ) {
                    return;
                }

                var fullusername = username + \"_@_\" + usertypeAbbreviation;

                //console.log(\"usertypeAbbreviation=\"+usertypeAbbreviation+\", fullusername=\"+fullusername);

                \$('#username').val(fullusername);

                return;
            }






        </script>


    </body>
         
</html>";
    }

    public function getTemplateName()
    {
        return "OlegUserdirectoryBundle:Security:login.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  275 => 110,  237 => 108,  233 => 101,  228 => 98,  226 => 97,  223 => 96,  221 => 95,  215 => 93,  204 => 85,  198 => 83,  196 => 82,  193 => 81,  191 => 80,  185 => 75,  180 => 72,  177 => 71,  174 => 70,  171 => 69,  168 => 68,  166 => 67,  163 => 66,  160 => 65,  157 => 64,  154 => 63,  152 => 62,  149 => 61,  146 => 60,  143 => 59,  140 => 58,  138 => 57,  135 => 56,  132 => 55,  129 => 54,  126 => 53,  124 => 52,  121 => 51,  118 => 50,  115 => 49,  112 => 48,  110 => 47,  107 => 46,  104 => 45,  101 => 44,  98 => 43,  96 => 42,  93 => 41,  90 => 40,  87 => 39,  84 => 38,  82 => 37,  79 => 36,  76 => 35,  74 => 34,  70 => 31,  32 => 29,  28 => 23,  19 => 16,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "OlegUserdirectoryBundle:Security:login.html.twig", "C:\\Users\\ch3\\Documents\\MyDocs\\WCMC\\ORDER\\scanorder\\Scanorders2\\src\\Oleg\\UserdirectoryBundle\\Resources\\views\\Security\\login.html.twig");
    }
}
