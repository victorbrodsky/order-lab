<?php

/* OlegUserdirectoryBundle::Default/footer.html.twig */
class __TwigTemplate_bcb7867db3775012dcbea39ad708d22d4d239a7fce4e5139612f85b776d741f2 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
            'footer' => array($this, 'block_footer'),
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 16
        echo "

";
        // line 18
        $this->displayBlock('footer', $context, $blocks);
        // line 49
        echo "
";
    }

    // line 18
    public function block_footer($context, array $blocks = array())
    {
        // line 19
        echo "
    <div class=\"footer\">

        ";
        // line 23
        echo "        ";
        // line 24
        echo "        ";
        // line 25
        echo "        ";
        // line 26
        echo "        ";
        // line 27
        echo "
        <a href=\"";
        // line 28
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("main_common_home");
        echo "\" target=\"_blank\">O R D E R</a>
        &copy; ";
        // line 29
        echo twig_escape_filter($this->env, twig_date_format_filter($this->env, "now", "Y"), "html", null, true);
        echo "
        ";
        // line 30
        if (((isset($context["institution_url"]) ? $context["institution_url"] : null) && (isset($context["institution_name"]) ? $context["institution_name"] : null))) {
            // line 31
            echo "            <a href=\"";
            echo twig_escape_filter($this->env, (isset($context["institution_url"]) ? $context["institution_url"] : null), "html", null, true);
            echo "\" target=\"_blank\">";
            echo twig_escape_filter($this->env, (isset($context["institution_name"]) ? $context["institution_name"] : null), "html", null, true);
            echo "</a>.
        ";
        } else {
            // line 33
            echo "            <a href=\"";
            echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("employees_siteparameters");
            echo "\" target=\"_blank\">[Add Your Institution's Name]</a>.
        ";
        }
        // line 35
        echo "
        <br><br>

        ";
        // line 38
        if (((((isset($context["department_url"]) ? $context["department_url"] : null) && (isset($context["department_name"]) ? $context["department_name"] : null)) && (isset($context["subinstitution_url"]) ? $context["subinstitution_url"] : null)) && (isset($context["subinstitution_name"]) ? $context["subinstitution_name"] : null))) {
            // line 39
            echo "            Instance for <a href=\"";
            echo twig_escape_filter($this->env, (isset($context["department_url"]) ? $context["department_url"] : null), "html", null, true);
            echo "\" target=\"_blank\">";
            echo twig_escape_filter($this->env, (isset($context["department_name"]) ? $context["department_name"] : null), "html", null, true);
            echo "</a> at
            <a href=\"";
            // line 40
            echo twig_escape_filter($this->env, (isset($context["subinstitution_url"]) ? $context["subinstitution_url"] : null), "html", null, true);
            echo "\" target=\"_blank\">";
            echo twig_escape_filter($this->env, (isset($context["subinstitution_name"]) ? $context["subinstitution_name"] : null), "html", null, true);
            echo "</a>.
        ";
        } else {
            // line 42
            echo "            Instance for <a href=\"";
            echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("employees_siteparameters");
            echo "\" target=\"_blank\">[Add Your Department's Name]</a> at
            <a href=\"";
            // line 43
            echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("employees_siteparameters");
            echo "\" target=\"_blank\">[Add Your Organization's Name]</a>.
        ";
        }
        // line 45
        echo "
    </div>

";
    }

    public function getTemplateName()
    {
        return "OlegUserdirectoryBundle::Default/footer.html.twig";
    }

    public function getDebugInfo()
    {
        return array (  105 => 45,  100 => 43,  95 => 42,  88 => 40,  81 => 39,  79 => 38,  74 => 35,  68 => 33,  60 => 31,  58 => 30,  54 => 29,  50 => 28,  47 => 27,  45 => 26,  43 => 25,  41 => 24,  39 => 23,  34 => 19,  31 => 18,  26 => 49,  24 => 18,  20 => 16,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "OlegUserdirectoryBundle::Default/footer.html.twig", "C:\\Users\\ch3\\Documents\\MyDocs\\WCMC\\ORDER\\scanorder\\Scanorders2\\src\\Oleg\\UserdirectoryBundle/Resources/views/Default/footer.html.twig");
    }
}
