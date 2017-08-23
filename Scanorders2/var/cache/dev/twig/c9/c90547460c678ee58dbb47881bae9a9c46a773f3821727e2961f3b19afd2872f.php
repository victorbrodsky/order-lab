<?php

/* @Twig/layout.html.twig */
class __TwigTemplate_0680f688411cd9ff5ee31c7225e7a0acbcdcf6ea2f79928a3fd54f387d00b4d4 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
            'title' => array($this, 'block_title'),
            'head' => array($this, 'block_head'),
            'body' => array($this, 'block_body'),
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $__internal_c6d0286759219d8ca02e60b5c12852e77024f6cb257ef9a335ba55b2ca66b545 = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_c6d0286759219d8ca02e60b5c12852e77024f6cb257ef9a335ba55b2ca66b545->enter($__internal_c6d0286759219d8ca02e60b5c12852e77024f6cb257ef9a335ba55b2ca66b545_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@Twig/layout.html.twig"));

        $__internal_7c634a7111a89263739ae58019f6215183f6163563df41a93fa73bdd10ee4899 = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_7c634a7111a89263739ae58019f6215183f6163563df41a93fa73bdd10ee4899->enter($__internal_7c634a7111a89263739ae58019f6215183f6163563df41a93fa73bdd10ee4899_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@Twig/layout.html.twig"));

        // line 1
        echo "<!DOCTYPE html>
<html>
    <head>
        <meta charset=\"";
        // line 4
        echo twig_escape_filter($this->env, $this->env->getCharset(), "html", null, true);
        echo "\" />
        <meta name=\"robots\" content=\"noindex,nofollow\" />
        <meta name=\"viewport\" content=\"width=device-width,initial-scale=1\" />
        <title>";
        // line 7
        $this->displayBlock('title', $context, $blocks);
        echo "</title>
        <link rel=\"icon\" type=\"image/png\" href=\"";
        // line 8
        echo twig_include($this->env, $context, "@Twig/images/favicon.png.base64");
        echo "\">
        <style>";
        // line 9
        echo twig_include($this->env, $context, "@Twig/exception.css.twig");
        echo "</style>
        ";
        // line 10
        $this->displayBlock('head', $context, $blocks);
        // line 11
        echo "    </head>
    <body>
        <header>
            <div class=\"container\">
                <h1 class=\"logo\">";
        // line 15
        echo twig_include($this->env, $context, "@Twig/images/symfony-logo.svg");
        echo " Symfony Exception</h1>

                <div class=\"help-link\">
                    <a href=\"https://symfony.com/doc\">
                        <span class=\"icon\">";
        // line 19
        echo twig_include($this->env, $context, "@Twig/images/icon-book.svg");
        echo "</span>
                        <span class=\"hidden-xs-down\">Symfony</span> Docs
                    </a>
                </div>

                <div class=\"help-link\">
                    <a href=\"https://symfony.com/support\">
                        <span class=\"icon\">";
        // line 26
        echo twig_include($this->env, $context, "@Twig/images/icon-support.svg");
        echo "</span>
                        <span class=\"hidden-xs-down\">Symfony</span> Support
                    </a>
                </div>
            </div>
        </header>

        ";
        // line 33
        $this->displayBlock('body', $context, $blocks);
        // line 34
        echo "        ";
        echo twig_include($this->env, $context, "@Twig/base_js.html.twig");
        echo "
    </body>
</html>
";
        
        $__internal_c6d0286759219d8ca02e60b5c12852e77024f6cb257ef9a335ba55b2ca66b545->leave($__internal_c6d0286759219d8ca02e60b5c12852e77024f6cb257ef9a335ba55b2ca66b545_prof);

        
        $__internal_7c634a7111a89263739ae58019f6215183f6163563df41a93fa73bdd10ee4899->leave($__internal_7c634a7111a89263739ae58019f6215183f6163563df41a93fa73bdd10ee4899_prof);

    }

    // line 7
    public function block_title($context, array $blocks = array())
    {
        $__internal_2487e8f1c5a76be8b8dc27835c539cd85d9ff35dd2eab929e5bfadb5768c04ad = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_2487e8f1c5a76be8b8dc27835c539cd85d9ff35dd2eab929e5bfadb5768c04ad->enter($__internal_2487e8f1c5a76be8b8dc27835c539cd85d9ff35dd2eab929e5bfadb5768c04ad_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "title"));

        $__internal_6ba904aa6cb637fc11bddac3f7c6052babc4317b3dfd6d11d5ebb2e656ce57bd = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_6ba904aa6cb637fc11bddac3f7c6052babc4317b3dfd6d11d5ebb2e656ce57bd->enter($__internal_6ba904aa6cb637fc11bddac3f7c6052babc4317b3dfd6d11d5ebb2e656ce57bd_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "title"));

        
        $__internal_6ba904aa6cb637fc11bddac3f7c6052babc4317b3dfd6d11d5ebb2e656ce57bd->leave($__internal_6ba904aa6cb637fc11bddac3f7c6052babc4317b3dfd6d11d5ebb2e656ce57bd_prof);

        
        $__internal_2487e8f1c5a76be8b8dc27835c539cd85d9ff35dd2eab929e5bfadb5768c04ad->leave($__internal_2487e8f1c5a76be8b8dc27835c539cd85d9ff35dd2eab929e5bfadb5768c04ad_prof);

    }

    // line 10
    public function block_head($context, array $blocks = array())
    {
        $__internal_d264231bb963acdac2480ba4ecffaf55f201857b26f030c46e44407c2813dc08 = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_d264231bb963acdac2480ba4ecffaf55f201857b26f030c46e44407c2813dc08->enter($__internal_d264231bb963acdac2480ba4ecffaf55f201857b26f030c46e44407c2813dc08_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "head"));

        $__internal_1ad943919fd2de98b9aff5937ae785f340b9adfb1a2c341eab38c0e1e0bdb74b = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_1ad943919fd2de98b9aff5937ae785f340b9adfb1a2c341eab38c0e1e0bdb74b->enter($__internal_1ad943919fd2de98b9aff5937ae785f340b9adfb1a2c341eab38c0e1e0bdb74b_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "head"));

        
        $__internal_1ad943919fd2de98b9aff5937ae785f340b9adfb1a2c341eab38c0e1e0bdb74b->leave($__internal_1ad943919fd2de98b9aff5937ae785f340b9adfb1a2c341eab38c0e1e0bdb74b_prof);

        
        $__internal_d264231bb963acdac2480ba4ecffaf55f201857b26f030c46e44407c2813dc08->leave($__internal_d264231bb963acdac2480ba4ecffaf55f201857b26f030c46e44407c2813dc08_prof);

    }

    // line 33
    public function block_body($context, array $blocks = array())
    {
        $__internal_b223a60574892fbd9d9fa0d4c8984dc66bdd6b188150ee122482e98f25aabea0 = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_b223a60574892fbd9d9fa0d4c8984dc66bdd6b188150ee122482e98f25aabea0->enter($__internal_b223a60574892fbd9d9fa0d4c8984dc66bdd6b188150ee122482e98f25aabea0_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "body"));

        $__internal_78caaedf9df4f3e6f363e7f23443bfc48f3e18b3a00d0bdbe0ea36fcfb1fbebe = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_78caaedf9df4f3e6f363e7f23443bfc48f3e18b3a00d0bdbe0ea36fcfb1fbebe->enter($__internal_78caaedf9df4f3e6f363e7f23443bfc48f3e18b3a00d0bdbe0ea36fcfb1fbebe_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "body"));

        
        $__internal_78caaedf9df4f3e6f363e7f23443bfc48f3e18b3a00d0bdbe0ea36fcfb1fbebe->leave($__internal_78caaedf9df4f3e6f363e7f23443bfc48f3e18b3a00d0bdbe0ea36fcfb1fbebe_prof);

        
        $__internal_b223a60574892fbd9d9fa0d4c8984dc66bdd6b188150ee122482e98f25aabea0->leave($__internal_b223a60574892fbd9d9fa0d4c8984dc66bdd6b188150ee122482e98f25aabea0_prof);

    }

    public function getTemplateName()
    {
        return "@Twig/layout.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  137 => 33,  120 => 10,  103 => 7,  88 => 34,  86 => 33,  76 => 26,  66 => 19,  59 => 15,  53 => 11,  51 => 10,  47 => 9,  43 => 8,  39 => 7,  33 => 4,  28 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("<!DOCTYPE html>
<html>
    <head>
        <meta charset=\"{{ _charset }}\" />
        <meta name=\"robots\" content=\"noindex,nofollow\" />
        <meta name=\"viewport\" content=\"width=device-width,initial-scale=1\" />
        <title>{% block title %}{% endblock %}</title>
        <link rel=\"icon\" type=\"image/png\" href=\"{{ include('@Twig/images/favicon.png.base64') }}\">
        <style>{{ include('@Twig/exception.css.twig') }}</style>
        {% block head %}{% endblock %}
    </head>
    <body>
        <header>
            <div class=\"container\">
                <h1 class=\"logo\">{{ include('@Twig/images/symfony-logo.svg') }} Symfony Exception</h1>

                <div class=\"help-link\">
                    <a href=\"https://symfony.com/doc\">
                        <span class=\"icon\">{{ include('@Twig/images/icon-book.svg') }}</span>
                        <span class=\"hidden-xs-down\">Symfony</span> Docs
                    </a>
                </div>

                <div class=\"help-link\">
                    <a href=\"https://symfony.com/support\">
                        <span class=\"icon\">{{ include('@Twig/images/icon-support.svg') }}</span>
                        <span class=\"hidden-xs-down\">Symfony</span> Support
                    </a>
                </div>
            </div>
        </header>

        {% block body %}{% endblock %}
        {{ include('@Twig/base_js.html.twig') }}
    </body>
</html>
", "@Twig/layout.html.twig", "C:\\Users\\ch3\\Documents\\MyDocs\\WCMC\\ORDER\\scanorder\\Scanorders2\\vendor\\symfony\\symfony\\src\\Symfony\\Bundle\\TwigBundle\\Resources\\views\\layout.html.twig");
    }
}
