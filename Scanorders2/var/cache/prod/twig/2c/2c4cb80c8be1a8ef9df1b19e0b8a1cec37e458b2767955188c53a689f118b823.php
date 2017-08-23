<?php

/* OlegUserdirectoryBundle:Admin:hierarchy-index.html.twig */
class __TwigTemplate_39b3598369e48af27198cbe3fa39dcb49e95dc13ffa74b7796aca2bd85ea635c extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        // line 17
        $this->parent = $this->loadTemplate("OlegUserdirectoryBundle::Default/base.html.twig", "OlegUserdirectoryBundle:Admin:hierarchy-index.html.twig", 17);
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
        echo "    Hierarchy Manager
";
    }

    // line 23
    public function block_content($context, array $blocks = array())
    {
        // line 24
        echo "
    <h3 class=\"text-info\">Hierarchy Manager</h3>

    <br>

    ";
        // line 30
        echo "        ";
        // line 31
        echo "    ";
        // line 32
        echo "
    ";
        // line 34
        echo "        ";
        // line 35
        echo "    ";
        // line 36
        echo "
    ";
        // line 38
        echo "
    <p>
        <a href=\"";
        // line 40
        echo twig_escape_filter($this->env, $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("employees_tree_institutiontree_list", (isset($context["filters"]) ? $context["filters"] : null)), "html", null, true);
        echo "\">Institution Tree Management</a>
    </p>

    <p>
        <a href=\"";
        // line 44
        echo twig_escape_filter($this->env, $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("employees_tree_commenttree_list", (isset($context["filters"]) ? $context["filters"] : null)), "html", null, true);
        echo "\">Comment Types Tree Management</a>
    </p>

    <p>
        <a href=\"";
        // line 48
        echo twig_escape_filter($this->env, $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("employees_tree_formnode_list", (isset($context["filters"]) ? $context["filters"] : null)), "html", null, true);
        echo "\">Form Tree Management</a>
    </p>

    <p>
        <a href=\"";
        // line 52
        echo twig_escape_filter($this->env, $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("employees_tree_messagecategories_list", (isset($context["filters"]) ? $context["filters"] : null)), "html", null, true);
        echo "\">Message Categories Management</a>
    </p>

    <br>

";
    }

    public function getTemplateName()
    {
        return "OlegUserdirectoryBundle:Admin:hierarchy-index.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  86 => 52,  79 => 48,  72 => 44,  65 => 40,  61 => 38,  58 => 36,  56 => 35,  54 => 34,  51 => 32,  49 => 31,  47 => 30,  40 => 24,  37 => 23,  32 => 20,  29 => 19,  11 => 17,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "OlegUserdirectoryBundle:Admin:hierarchy-index.html.twig", "C:\\Users\\ch3\\Documents\\MyDocs\\WCMC\\ORDER\\scanorder\\Scanorders2\\src\\Oleg\\UserdirectoryBundle/Resources/views/Admin/hierarchy-index.html.twig");
    }
}
