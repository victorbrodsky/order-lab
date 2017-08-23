<?php

/* OlegUserdirectoryBundle:AccessRequest:request_confirmation.html.twig */
class __TwigTemplate_fe5926ead95b66b90ddd15bc6f5e611b6ce74219962a39eeb81b16b8b0c104b4 extends Twig_Template
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
            // asset "2dfeabf_0"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("_assetic_2dfeabf_0") : $this->env->getExtension('Symfony\Bridge\Twig\Extension\AssetExtension')->getAssetUrl("css/2dfeabf_part_1_bootstrap-theme.min_1.css");
            // line 27
            echo "        <link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : null), "html", null, true);
            echo "\" />
    ";
            // asset "2dfeabf_1"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("_assetic_2dfeabf_1") : $this->env->getExtension('Symfony\Bridge\Twig\Extension\AssetExtension')->getAssetUrl("css/2dfeabf_part_1_bootstrap.min_2.css");
            echo "        <link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : null), "html", null, true);
            echo "\" />
    ";
            // asset "2dfeabf_2"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("_assetic_2dfeabf_2") : $this->env->getExtension('Symfony\Bridge\Twig\Extension\AssetExtension')->getAssetUrl("css/2dfeabf_form_2.css");
            echo "        <link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : null), "html", null, true);
            echo "\" />
    ";
        } else {
            // asset "2dfeabf"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("_assetic_2dfeabf") : $this->env->getExtension('Symfony\Bridge\Twig\Extension\AssetExtension')->getAssetUrl("css/2dfeabf.css");
            echo "        <link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : null), "html", null, true);
            echo "\" />
    ";
        }
        unset($context["asset_url"]);
        // line 29
        echo "
    <title>Access Request Confirmation</title>

    <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge,chrome=1\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">

    <link rel=\"icon\" type=\"image/x-icon\" href=\"";
        // line 35
        echo twig_escape_filter($this->env, $this->env->getExtension('Symfony\Bridge\Twig\Extension\AssetExtension')->getAssetUrl("glyphicons-157-show-thumbnails.png"), "html", null, true);
        echo "\" />

</head>


<body>

    <div class=\"container\">

        <div class=\"text-center\">

            ";
        // line 46
        $this->loadTemplate("OlegUserdirectoryBundle:Default:navbar.html.twig", "OlegUserdirectoryBundle:AccessRequest:request_confirmation.html.twig", 46)->display(array_merge($context, array("minimum" => true)));
        // line 47
        echo "
            <h2 class=\"alert alert-info well-lg safary-fix\" align=\"center\">";
        // line 48
        echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute((isset($context["app"]) ? $context["app"] : null), "user", array()), "getUserNameStr", array(), "method"), "html", null, true);
        echo ": Access Request Confirmation</h2>

            <br>

            <br><br><br>

            <p>
                <h3 class=\"text-info text-center\">";
        // line 55
        echo twig_escape_filter($this->env, (isset($context["text"]) ? $context["text"] : null), "html", null, true);
        echo "</h3>
            </p>

            <br><br><br>

            <br>
            <p>
            <a href=\"";
        // line 62
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("main_common_home");
        echo "\">Return to Main Home Page</a>
            </p>


            ";
        // line 67
        echo "                ";
        // line 68
        echo "                ";
        // line 69
        echo "                    ";
        // line 70
        echo "                ";
        // line 71
        echo "            ";
        // line 72
        echo "

            ";
        // line 74
        $this->loadTemplate("OlegUserdirectoryBundle::Default/footer.html.twig", "OlegUserdirectoryBundle:AccessRequest:request_confirmation.html.twig", 74)->display($context);
        // line 75
        echo "
        </div> <!-- /text-center -->
    </div> <!-- /container -->


    ";
        // line 80
        if (isset($context['assetic']['debug']) && $context['assetic']['debug']) {
            // asset "3c1e24d_0"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("_assetic_3c1e24d_0") : $this->env->getExtension('Symfony\Bridge\Twig\Extension\AssetExtension')->getAssetUrl("js/3c1e24d_jquery-1.11.0.min_1.js");
            // line 84
            echo "    <script type=\"text/javascript\" src=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : null), "html", null, true);
            echo "\"></script>
    ";
            // asset "3c1e24d_1"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("_assetic_3c1e24d_1") : $this->env->getExtension('Symfony\Bridge\Twig\Extension\AssetExtension')->getAssetUrl("js/3c1e24d_part_2_bootstrap.min_1.js");
            echo "    <script type=\"text/javascript\" src=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : null), "html", null, true);
            echo "\"></script>
    ";
        } else {
            // asset "3c1e24d"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("_assetic_3c1e24d") : $this->env->getExtension('Symfony\Bridge\Twig\Extension\AssetExtension')->getAssetUrl("js/3c1e24d.js");
            echo "    <script type=\"text/javascript\" src=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : null), "html", null, true);
            echo "\"></script>
    ";
        }
        unset($context["asset_url"]);
        // line 86
        echo "
</body>

</html>";
    }

    public function getTemplateName()
    {
        return "OlegUserdirectoryBundle:AccessRequest:request_confirmation.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  159 => 86,  139 => 84,  135 => 80,  128 => 75,  126 => 74,  122 => 72,  120 => 71,  118 => 70,  116 => 69,  114 => 68,  112 => 67,  105 => 62,  95 => 55,  85 => 48,  82 => 47,  80 => 46,  66 => 35,  58 => 29,  32 => 27,  28 => 23,  19 => 16,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "OlegUserdirectoryBundle:AccessRequest:request_confirmation.html.twig", "C:\\Users\\ch3\\Documents\\MyDocs\\WCMC\\ORDER\\scanorder\\Scanorders2\\src\\Oleg\\UserdirectoryBundle/Resources/views/AccessRequest/request_confirmation.html.twig");
    }
}
