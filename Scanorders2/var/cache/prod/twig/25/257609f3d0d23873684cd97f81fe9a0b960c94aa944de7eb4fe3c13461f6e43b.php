<?php

/* OlegUserdirectoryBundle:AccessRequest:access_request.html.twig */
class __TwigTemplate_a3046ce44f0a821e79c759f98fe5ca2cbf50fd509d2549327874046b0d6da40e extends Twig_Template
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
            // asset "fe7226e_0"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("_assetic_fe7226e_0") : $this->env->getExtension('Symfony\Bridge\Twig\Extension\AssetExtension')->getAssetUrl("css/fe7226e_part_1_bootstrap-theme.min_1.css");
            // line 28
            echo "        <link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : null), "html", null, true);
            echo "\" />
    ";
            // asset "fe7226e_1"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("_assetic_fe7226e_1") : $this->env->getExtension('Symfony\Bridge\Twig\Extension\AssetExtension')->getAssetUrl("css/fe7226e_part_1_bootstrap.min_2.css");
            echo "        <link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : null), "html", null, true);
            echo "\" />
    ";
            // asset "fe7226e_2"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("_assetic_fe7226e_2") : $this->env->getExtension('Symfony\Bridge\Twig\Extension\AssetExtension')->getAssetUrl("css/fe7226e_select2_2.css");
            echo "        <link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : null), "html", null, true);
            echo "\" />
    ";
            // asset "fe7226e_3"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("_assetic_fe7226e_3") : $this->env->getExtension('Symfony\Bridge\Twig\Extension\AssetExtension')->getAssetUrl("css/fe7226e_form_3.css");
            echo "        <link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : null), "html", null, true);
            echo "\" />
    ";
        } else {
            // asset "fe7226e"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("_assetic_fe7226e") : $this->env->getExtension('Symfony\Bridge\Twig\Extension\AssetExtension')->getAssetUrl("css/fe7226e.css");
            echo "        <link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : null), "html", null, true);
            echo "\" />
    ";
        }
        unset($context["asset_url"]);
        // line 30
        echo "
    <title>";
        // line 31
        echo twig_escape_filter($this->env, (isset($context["sitenamefull"]) ? $context["sitenamefull"] : null), "html", null, true);
        echo " Access Request</title>

    <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge,chrome=1\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">

    <link rel=\"icon\" type=\"image/x-icon\" href=\"";
        // line 36
        echo twig_escape_filter($this->env, $this->env->getExtension('Symfony\Bridge\Twig\Extension\AssetExtension')->getAssetUrl("glyphicons-157-show-thumbnails.png"), "html", null, true);
        echo "\" />

</head>


<body>

    ";
        // line 43
        $context["formmacros"] = $this->loadTemplate("OlegOrderformBundle::Default/formmacros.html.twig", "OlegUserdirectoryBundle:AccessRequest:access_request.html.twig", 43);
        // line 44
        echo "
    <div class=\"container\">

        <div class=\"text-center\">

            ";
        // line 49
        $this->loadTemplate("OlegUserdirectoryBundle:Default:navbar.html.twig", "OlegUserdirectoryBundle:AccessRequest:access_request.html.twig", 49)->display(array_merge($context, array("minimum" => true)));
        // line 50
        echo "
            <h2 class=\"alert alert-info well-lg safary-fix\" align=\"center\">";
        // line 51
        echo twig_escape_filter($this->env, (isset($context["sitenamefull"]) ? $context["sitenamefull"] : null), "html", null, true);
        echo " Access Request</h2>

            ";
        // line 54
        echo "            ";
        // line 55
        echo "                ";
        // line 56
        echo "            ";
        // line 57
        echo "
            <br>

            <p>
            <h3 class=\"text-info\">Welcome to the ";
        // line 61
        echo twig_escape_filter($this->env, (isset($context["sitenamefull"]) ? $context["sitenamefull"] : null), "html", null, true);
        echo "!</h3>
            </p>

            <p>
            <h3 class=\"text-info\">Would you like to receive access to this site?</h3>
            </p>

            <br><br>

            <form action=\"";
        // line 70
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath(((isset($context["sitename"]) ? $context["sitename"] : null) . "_access_request_create"));
        echo "\" method=\"post\">

                <div id=\"confirm-div\">
                <p>
                    <button class=\"btn btn-info\" type=\"button\" onclick=\"showDetails();\">Yes, please!</button>
                    &nbsp;
                    <a class=\"btn btn-info\" href=\"";
        // line 76
        echo twig_escape_filter($this->env, $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("employees_no_thanks_accessrequest", array("sitename" => (isset($context["sitename"]) ? $context["sitename"] : null))), "html", null, true);
        echo "\">No, thanks!</a>
                </p>
                </div>

                ";
        // line 81
        echo "                <div id=\"details-div\" class=\"collapse\">
                    ";
        // line 82
        echo $this->env->getRuntime('Symfony\Bridge\Twig\Form\TwigRenderer')->searchAndRenderBlock((isset($context["form"]) ? $context["form"] : null), 'errors');
        echo "

                    ";
        // line 84
        echo $context["formmacros"]->getfield($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "firstName", array()));
        echo "
                    ";
        // line 85
        echo $context["formmacros"]->getfield($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "lastName", array()));
        echo "
                    ";
        // line 86
        echo $context["formmacros"]->getfield($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "email", array()));
        echo "
                    ";
        // line 87
        echo $context["formmacros"]->getfield($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "phone", array()));
        echo "
                    ";
        // line 88
        echo $context["formmacros"]->getfield($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "job", array()));
        echo "

                    ";
        // line 90
        if ($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "organizationalGroup", array(), "any", true, true)) {
            // line 91
            echo "                        ";
            echo $context["formmacros"]->getfield($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "organizationalGroup", array()));
            echo "
                    ";
        }
        // line 93
        echo "
                    ";
        // line 94
        echo $context["formmacros"]->getfield($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "reason", array()));
        echo "
                    ";
        // line 95
        echo $context["formmacros"]->getfield($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "similaruser", array()));
        echo "

                    <p style=\"padding-top:5px;\">
                    ";
        // line 99
        echo "                        ";
        // line 100
        echo "                            ";
        // line 101
        echo "                                ";
        // line 102
        echo "                            ";
        // line 103
        echo "                        ";
        // line 104
        echo "                        ";
        // line 105
        echo "                        ";
        // line 106
        echo "                    ";
        // line 107
        echo "                    ";
        // line 108
        echo "                        ";
        // line 109
        echo "                            ";
        // line 110
        echo "                        ";
        // line 111
        echo "                        ";
        // line 112
        echo "                            ";
        // line 113
        echo "                        ";
        // line 114
        echo "                    ";
        // line 115
        echo "                        ";
        // line 116
        echo "                            For reference, please provide the name and contact information of your supervisor
                            or of the person who can confirm the validity of your request below.
                        ";
        // line 119
        echo "                    </p>

                    ";
        // line 121
        echo $context["formmacros"]->getfield($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "referencename", array()));
        echo "
                    ";
        // line 122
        echo $context["formmacros"]->getfield($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "referenceemail", array()));
        echo "
                    ";
        // line 123
        echo $context["formmacros"]->getfield($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "referencephone", array()));
        echo "


                    <br>
                    <p>
                        <button class=\"btn btn-info\" type=\"submit\">Submit Access Request</button>
                        &nbsp;
                        <a class=\"btn btn-info\" href=\"";
        // line 130
        echo twig_escape_filter($this->env, $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("employees_no_thanks_accessrequest", array("sitename" => (isset($context["sitename"]) ? $context["sitename"] : null))), "html", null, true);
        echo "\">No, thanks!</a>
                    </p>
                </div>

                ";
        // line 134
        echo $this->env->getRuntime('Symfony\Bridge\Twig\Form\TwigRenderer')->searchAndRenderBlock((isset($context["form"]) ? $context["form"] : null), 'rest');
        echo "

            </form>

            ";
        // line 138
        $this->loadTemplate("OlegUserdirectoryBundle::Default/footer.html.twig", "OlegUserdirectoryBundle:AccessRequest:access_request.html.twig", 138)->display($context);
        // line 139
        echo "
        </div> <!-- /text-center -->

    </div> <!-- /container -->


    ";
        // line 145
        if (isset($context['assetic']['debug']) && $context['assetic']['debug']) {
            // asset "fd317df_0"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("_assetic_fd317df_0") : $this->env->getExtension('Symfony\Bridge\Twig\Extension\AssetExtension')->getAssetUrl("js/fd317df_errorwatch_1.js");
            // line 156
            echo "        <script type=\"text/javascript\" src=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : null), "html", null, true);
            echo "\"></script>
    ";
            // asset "fd317df_1"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("_assetic_fd317df_1") : $this->env->getExtension('Symfony\Bridge\Twig\Extension\AssetExtension')->getAssetUrl("js/fd317df_jquery-1.11.0.min_2.js");
            echo "        <script type=\"text/javascript\" src=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : null), "html", null, true);
            echo "\"></script>
    ";
            // asset "fd317df_2"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("_assetic_fd317df_2") : $this->env->getExtension('Symfony\Bridge\Twig\Extension\AssetExtension')->getAssetUrl("js/fd317df_part_3_bootstrap.min_1.js");
            echo "        <script type=\"text/javascript\" src=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : null), "html", null, true);
            echo "\"></script>
    ";
            // asset "fd317df_3"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("_assetic_fd317df_3") : $this->env->getExtension('Symfony\Bridge\Twig\Extension\AssetExtension')->getAssetUrl("js/fd317df_select2_4.js");
            echo "        <script type=\"text/javascript\" src=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : null), "html", null, true);
            echo "\"></script>
    ";
            // asset "fd317df_4"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("_assetic_fd317df_4") : $this->env->getExtension('Symfony\Bridge\Twig\Extension\AssetExtension')->getAssetUrl("js/fd317df_jquery.inputmask.bundle_5.js");
            echo "        <script type=\"text/javascript\" src=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : null), "html", null, true);
            echo "\"></script>
    ";
            // asset "fd317df_5"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("_assetic_fd317df_5") : $this->env->getExtension('Symfony\Bridge\Twig\Extension\AssetExtension')->getAssetUrl("js/fd317df_user-common_6.js");
            echo "        <script type=\"text/javascript\" src=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : null), "html", null, true);
            echo "\"></script>
    ";
            // asset "fd317df_6"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("_assetic_fd317df_6") : $this->env->getExtension('Symfony\Bridge\Twig\Extension\AssetExtension')->getAssetUrl("js/fd317df_masking_7.js");
            echo "        <script type=\"text/javascript\" src=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : null), "html", null, true);
            echo "\"></script>
    ";
        } else {
            // asset "fd317df"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("_assetic_fd317df") : $this->env->getExtension('Symfony\Bridge\Twig\Extension\AssetExtension')->getAssetUrl("js/fd317df.js");
            echo "        <script type=\"text/javascript\" src=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : null), "html", null, true);
            echo "\"></script>
    ";
        }
        unset($context["asset_url"]);
        // line 158
        echo "


    <script language=\"Javascript\">

        \$(document).ready(function() {

            regularCombobox();
            //customCombobox();
            fieldInputMask();
            expandTextarea();
            initConvertEnterToTab();
            //getComboboxCompositetree();

        });

        function showDetails() {
            \$('#confirm-div').hide();
            \$('#details-div').collapse('show');
        }

    </script>

</body>

</html>
";
    }

    public function getTemplateName()
    {
        return "OlegUserdirectoryBundle:AccessRequest:access_request.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  332 => 158,  282 => 156,  278 => 145,  270 => 139,  268 => 138,  261 => 134,  254 => 130,  244 => 123,  240 => 122,  236 => 121,  232 => 119,  228 => 116,  226 => 115,  224 => 114,  222 => 113,  220 => 112,  218 => 111,  216 => 110,  214 => 109,  212 => 108,  210 => 107,  208 => 106,  206 => 105,  204 => 104,  202 => 103,  200 => 102,  198 => 101,  196 => 100,  194 => 99,  188 => 95,  184 => 94,  181 => 93,  175 => 91,  173 => 90,  168 => 88,  164 => 87,  160 => 86,  156 => 85,  152 => 84,  147 => 82,  144 => 81,  137 => 76,  128 => 70,  116 => 61,  110 => 57,  108 => 56,  106 => 55,  104 => 54,  99 => 51,  96 => 50,  94 => 49,  87 => 44,  85 => 43,  75 => 36,  67 => 31,  64 => 30,  32 => 28,  28 => 23,  19 => 16,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "OlegUserdirectoryBundle:AccessRequest:access_request.html.twig", "C:\\Users\\ch3\\Documents\\MyDocs\\WCMC\\ORDER\\scanorder\\Scanorders2\\src\\Oleg\\UserdirectoryBundle/Resources/views/AccessRequest/access_request.html.twig");
    }
}
