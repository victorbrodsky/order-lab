<?php

/* OlegUserdirectoryBundle:Admin:import-users.html.twig */
class __TwigTemplate_1f156d244f35bee0fe04acf4a86f90ae917257fdb4723bafac8ac257534f50f3 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        // line 17
        $this->parent = $this->loadTemplate("OlegUserdirectoryBundle::Default/base.html.twig", "OlegUserdirectoryBundle:Admin:import-users.html.twig", 17);
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

    // line 19
    public function block_title($context, array $blocks = array())
    {
        // line 20
        echo "    ";
        echo twig_escape_filter($this->env, (isset($context["title"]) ? $context["title"] : null), "html", null, true);
        echo "
";
    }

    // line 23
    public function block_content($context, array $blocks = array())
    {
        // line 24
        echo "
    <h3 class=\"text-info\">";
        // line 25
        echo twig_escape_filter($this->env, (isset($context["title"]) ? $context["title"] : null), "html", null, true);
        echo "</h3>

    <br>

    <hr>
    <p>
        <a href=\"";
        // line 31
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("employees_import_users_template_excel");
        echo "\">Download user list template</a>, fill it in, and then upload it via the buttons below to import multiple users in bulk.
    </p>
    <hr>

    <br><br>

    ";
        // line 37
        echo         $this->env->getRuntime('Symfony\Bridge\Twig\Form\TwigRenderer')->renderBlock((isset($context["form"]) ? $context["form"] : null), 'form_start');
        echo "

        <div class=\"row\">
            <div class=\"col-xs-3\"></div>
            <div class=\"col-xs-6\" align=\"center\">
                ";
        // line 42
        echo $this->env->getRuntime('Symfony\Bridge\Twig\Form\TwigRenderer')->searchAndRenderBlock($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "file", array()), 'row');
        echo "
            </div>
            <div class=\"col-xs-3\"></div>
        </div>

        <br><br><br>

        ";
        // line 49
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["formmacros"]) ? $context["formmacros"] : null), "field", array(0 => $this->getAttribute((isset($context["form"]) ? $context["form"] : null), "submit", array())), "method"), "html", null, true);
        echo "

    ";
        // line 51
        echo         $this->env->getRuntime('Symfony\Bridge\Twig\Form\TwigRenderer')->renderBlock((isset($context["form"]) ? $context["form"] : null), 'form_end');
        echo "


";
    }

    public function getTemplateName()
    {
        return "OlegUserdirectoryBundle:Admin:import-users.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  86 => 51,  81 => 49,  71 => 42,  63 => 37,  54 => 31,  45 => 25,  42 => 24,  39 => 23,  32 => 20,  29 => 19,  11 => 17,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "OlegUserdirectoryBundle:Admin:import-users.html.twig", "C:\\Users\\ch3\\Documents\\MyDocs\\WCMC\\ORDER\\scanorder\\Scanorders2\\src\\Oleg\\UserdirectoryBundle/Resources/views/Admin/import-users.html.twig");
    }
}
