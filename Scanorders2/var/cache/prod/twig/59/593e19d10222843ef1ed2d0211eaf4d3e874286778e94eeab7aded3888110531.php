<?php

/* OlegUserdirectoryBundle:Default:main-common-home.html.twig */
class __TwigTemplate_a92cce77b61110c30dff46354f3d56b80604e4044abdbbba780436ed4209a94b extends Twig_Template
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
            echo "    <link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : null), "html", null, true);
            echo "\" />
    ";
            // asset "2dfeabf_1"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("_assetic_2dfeabf_1") : $this->env->getExtension('Symfony\Bridge\Twig\Extension\AssetExtension')->getAssetUrl("css/2dfeabf_part_1_bootstrap.min_2.css");
            echo "    <link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : null), "html", null, true);
            echo "\" />
    ";
            // asset "2dfeabf_2"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("_assetic_2dfeabf_2") : $this->env->getExtension('Symfony\Bridge\Twig\Extension\AssetExtension')->getAssetUrl("css/2dfeabf_form_2.css");
            echo "    <link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : null), "html", null, true);
            echo "\" />
    ";
        } else {
            // asset "2dfeabf"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("_assetic_2dfeabf") : $this->env->getExtension('Symfony\Bridge\Twig\Extension\AssetExtension')->getAssetUrl("css/2dfeabf.css");
            echo "    <link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : null), "html", null, true);
            echo "\" />
    ";
        }
        unset($context["asset_url"]);
        // line 29
        echo "
    <title>O R D E R</title>

    <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge,chrome=1\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">

    ";
        // line 35
        $context["usermacros"] = $this->loadTemplate("OlegUserdirectoryBundle::Default/usermacros.html.twig", "OlegUserdirectoryBundle:Default:main-common-home.html.twig", 35);
        // line 36
        echo "    ";
        echo $context["usermacros"]->getnonLiveSiteRedirect();
        echo "

    <link rel=\"icon\" type=\"image/x-icon\" href=\"";
        // line 38
        echo twig_escape_filter($this->env, $this->env->getExtension('Symfony\Bridge\Twig\Extension\AssetExtension')->getAssetUrl("glyphicons-157-show-thumbnails.png"), "html", null, true);
        echo "\" />

</head>


<body>


<div class=\"container\">

    <div class=\"text-center\">

        ";
        // line 50
        echo $context["usermacros"]->getnonLiveSiteWarning();
        echo "
        ";
        // line 51
        echo $context["usermacros"]->getbrowserCheck();
        echo "

        <h2 class=\"alert alert-info well-lg safary-fix\" align=\"center\">O R D E R</h2>

        <br>

        ";
        // line 57
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["app"]) ? $context["app"] : null), "session", array()), "flashbag", array()), "get", array(0 => "warning"), "method"));
        foreach ($context['_seq'] as $context["_key"] => $context["flashMessage"]) {
            // line 58
            echo "            <div style=\"height: 1%;\">&nbsp;</div>
            <p class=\"alert alert-danger center-block\" align=\"middle\" style=\"width: 70%;\">";
            // line 59
            echo $context["flashMessage"];
            echo "</p>
        ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['flashMessage'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 61
        echo "
        <br>

        ";
        // line 65
        echo "        ";
        echo twig_escape_filter($this->env, (isset($context["mainhome_title"]) ? $context["mainhome_title"] : null), "html", null, true);
        echo "

        <br><br>

        The following sites are available:

        <br><br>

        <p><a href=\"";
        // line 73
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("scan_home");
        echo "\">Glass Slide Scan Orders</a></p>


        <p><a href=\"";
        // line 76
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("employees_home");
        echo "\">Employee Directory</a></p>


        <p><a href=\"";
        // line 79
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("fellapp_home");
        echo "\">Fellowship Applications</a></p>


        <p><a href=\"";
        // line 82
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("deidentifier_home");
        echo "\">Deidentifier</a></p>


        <p><a href=\"";
        // line 85
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("vacreq_home");
        echo "\">Vacation Request</a></p>


        <p><a href=\"";
        // line 88
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("calllog_home");
        echo "\">Call Log Book</a></p>


        <p><a href=\"";
        // line 91
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("translationalresearch_home");
        echo "\">HemePath Translational Research</a></p>


        ";
        // line 94
        $this->loadTemplate("OlegUserdirectoryBundle::Default/footer.html.twig", "OlegUserdirectoryBundle:Default:main-common-home.html.twig", 94)->display($context);
        // line 95
        echo "
    </div> <!-- /text-center -->

</div> <!-- /container -->

</body>

</html>
";
    }

    public function getTemplateName()
    {
        return "OlegUserdirectoryBundle:Default:main-common-home.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  178 => 95,  176 => 94,  170 => 91,  164 => 88,  158 => 85,  152 => 82,  146 => 79,  140 => 76,  134 => 73,  122 => 65,  117 => 61,  109 => 59,  106 => 58,  102 => 57,  93 => 51,  89 => 50,  74 => 38,  68 => 36,  66 => 35,  58 => 29,  32 => 27,  28 => 23,  19 => 16,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "OlegUserdirectoryBundle:Default:main-common-home.html.twig", "C:\\Users\\ch3\\Documents\\MyDocs\\WCMC\\ORDER\\scanorder\\Scanorders2\\src\\Oleg\\UserdirectoryBundle\\Resources\\views\\Default\\main-common-home.html.twig");
    }
}
