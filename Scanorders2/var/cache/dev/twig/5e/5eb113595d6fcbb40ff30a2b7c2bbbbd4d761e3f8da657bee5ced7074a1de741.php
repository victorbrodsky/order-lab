<?php

/* @Twig/Exception/exception_full.html.twig */
class __TwigTemplate_14277861d04491218b08a2e94f56822240dcabe1db4706dc1a9b06d87aabf817 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        // line 1
        $this->parent = $this->loadTemplate("@Twig/layout.html.twig", "@Twig/Exception/exception_full.html.twig", 1);
        $this->blocks = array(
            'head' => array($this, 'block_head'),
            'title' => array($this, 'block_title'),
            'body' => array($this, 'block_body'),
        );
    }

    protected function doGetParent(array $context)
    {
        return "@Twig/layout.html.twig";
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $__internal_8e787b5acbde8d8c5b018bbea77260c2253d01b0741a11637d7b29995d55b43b = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_8e787b5acbde8d8c5b018bbea77260c2253d01b0741a11637d7b29995d55b43b->enter($__internal_8e787b5acbde8d8c5b018bbea77260c2253d01b0741a11637d7b29995d55b43b_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@Twig/Exception/exception_full.html.twig"));

        $__internal_955ba89f36f4e6f7d2297f934f26cf7485236ebe21ba898a197e85ffdd705f4e = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_955ba89f36f4e6f7d2297f934f26cf7485236ebe21ba898a197e85ffdd705f4e->enter($__internal_955ba89f36f4e6f7d2297f934f26cf7485236ebe21ba898a197e85ffdd705f4e_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@Twig/Exception/exception_full.html.twig"));

        $this->parent->display($context, array_merge($this->blocks, $blocks));
        
        $__internal_8e787b5acbde8d8c5b018bbea77260c2253d01b0741a11637d7b29995d55b43b->leave($__internal_8e787b5acbde8d8c5b018bbea77260c2253d01b0741a11637d7b29995d55b43b_prof);

        
        $__internal_955ba89f36f4e6f7d2297f934f26cf7485236ebe21ba898a197e85ffdd705f4e->leave($__internal_955ba89f36f4e6f7d2297f934f26cf7485236ebe21ba898a197e85ffdd705f4e_prof);

    }

    // line 3
    public function block_head($context, array $blocks = array())
    {
        $__internal_e726e1b79010904930f692e28c02b7717a948d9d8010029ded82e2c8a9a3ad62 = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_e726e1b79010904930f692e28c02b7717a948d9d8010029ded82e2c8a9a3ad62->enter($__internal_e726e1b79010904930f692e28c02b7717a948d9d8010029ded82e2c8a9a3ad62_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "head"));

        $__internal_84133b67d7d77e412b750591abdededc6469a6c14072f4c5901ed22d5fe766b6 = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_84133b67d7d77e412b750591abdededc6469a6c14072f4c5901ed22d5fe766b6->enter($__internal_84133b67d7d77e412b750591abdededc6469a6c14072f4c5901ed22d5fe766b6_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "head"));

        // line 4
        echo "    <style>
        .sf-reset .traces {
            padding-bottom: 14px;
        }
        .sf-reset .traces li {
            font-size: 12px;
            color: #868686;
            padding: 5px 4px;
            list-style-type: decimal;
            margin-left: 20px;
        }
        .sf-reset #logs .traces li.error {
            font-style: normal;
            color: #AA3333;
            background: #f9ecec;
        }
        .sf-reset #logs .traces li.warning {
            font-style: normal;
            background: #ffcc00;
        }
        /* fix for Opera not liking empty <li> */
        .sf-reset .traces li:after {
            content: \"\\00A0\";
        }
        .sf-reset .trace {
            border: 1px solid #D3D3D3;
            padding: 10px;
            overflow: auto;
            margin: 10px 0 20px;
        }
        .sf-reset .block-exception {
            -moz-border-radius: 16px;
            -webkit-border-radius: 16px;
            border-radius: 16px;
            margin-bottom: 20px;
            background-color: #f6f6f6;
            border: 1px solid #dfdfdf;
            padding: 30px 28px;
            word-wrap: break-word;
            overflow: hidden;
        }
        .sf-reset .block-exception div {
            color: #313131;
            font-size: 10px;
        }
        .sf-reset .block-exception-detected .illustration-exception,
        .sf-reset .block-exception-detected .text-exception {
            float: left;
        }
        .sf-reset .block-exception-detected .illustration-exception {
            width: 152px;
        }
        .sf-reset .block-exception-detected .text-exception {
            width: 670px;
            padding: 30px 44px 24px 46px;
            position: relative;
        }
        .sf-reset .text-exception .open-quote,
        .sf-reset .text-exception .close-quote {
            font-family: Arial, Helvetica, sans-serif;
            position: absolute;
            color: #C9C9C9;
            font-size: 8em;
        }
        .sf-reset .open-quote {
            top: 0;
            left: 0;
        }
        .sf-reset .close-quote {
            bottom: -0.5em;
            right: 50px;
        }
        .sf-reset .block-exception p {
            font-family: Arial, Helvetica, sans-serif;
        }
        .sf-reset .block-exception p a,
        .sf-reset .block-exception p a:hover {
            color: #565656;
        }
        .sf-reset .logs h2 {
            float: left;
            width: 654px;
        }
        .sf-reset .error-count, .sf-reset .support {
            float: right;
            width: 170px;
            text-align: right;
        }
        .sf-reset .error-count span {
             display: inline-block;
             background-color: #aacd4e;
             -moz-border-radius: 6px;
             -webkit-border-radius: 6px;
             border-radius: 6px;
             padding: 4px;
             color: white;
             margin-right: 2px;
             font-size: 11px;
             font-weight: bold;
        }

        .sf-reset .support a {
            display: inline-block;
            -moz-border-radius: 6px;
            -webkit-border-radius: 6px;
            border-radius: 6px;
            padding: 4px;
            color: #000000;
            margin-right: 2px;
            font-size: 11px;
            font-weight: bold;
        }

        .sf-reset .toggle {
            vertical-align: middle;
        }
        .sf-reset .linked ul,
        .sf-reset .linked li {
            display: inline;
        }
        .sf-reset #output-content {
            color: #000;
            font-size: 12px;
        }
        .sf-reset #traces-text pre {
            white-space: pre;
            font-size: 12px;
            font-family: monospace;
        }
    </style>
";
        
        $__internal_84133b67d7d77e412b750591abdededc6469a6c14072f4c5901ed22d5fe766b6->leave($__internal_84133b67d7d77e412b750591abdededc6469a6c14072f4c5901ed22d5fe766b6_prof);

        
        $__internal_e726e1b79010904930f692e28c02b7717a948d9d8010029ded82e2c8a9a3ad62->leave($__internal_e726e1b79010904930f692e28c02b7717a948d9d8010029ded82e2c8a9a3ad62_prof);

    }

    // line 136
    public function block_title($context, array $blocks = array())
    {
        $__internal_5592101a393b08b586211ed4936a8c00756b6cdbbad15fdf869a196699b2ee14 = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_5592101a393b08b586211ed4936a8c00756b6cdbbad15fdf869a196699b2ee14->enter($__internal_5592101a393b08b586211ed4936a8c00756b6cdbbad15fdf869a196699b2ee14_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "title"));

        $__internal_ae29cd29d3f38f2f8bbccaf51f7be1690143c4335867743384234ca280f12d24 = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_ae29cd29d3f38f2f8bbccaf51f7be1690143c4335867743384234ca280f12d24->enter($__internal_ae29cd29d3f38f2f8bbccaf51f7be1690143c4335867743384234ca280f12d24_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "title"));

        // line 137
        echo "    ";
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["exception"]) ? $context["exception"] : $this->getContext($context, "exception")), "message", array()), "html", null, true);
        echo " (";
        echo twig_escape_filter($this->env, (isset($context["status_code"]) ? $context["status_code"] : $this->getContext($context, "status_code")), "html", null, true);
        echo " ";
        echo twig_escape_filter($this->env, (isset($context["status_text"]) ? $context["status_text"] : $this->getContext($context, "status_text")), "html", null, true);
        echo ")
";
        
        $__internal_ae29cd29d3f38f2f8bbccaf51f7be1690143c4335867743384234ca280f12d24->leave($__internal_ae29cd29d3f38f2f8bbccaf51f7be1690143c4335867743384234ca280f12d24_prof);

        
        $__internal_5592101a393b08b586211ed4936a8c00756b6cdbbad15fdf869a196699b2ee14->leave($__internal_5592101a393b08b586211ed4936a8c00756b6cdbbad15fdf869a196699b2ee14_prof);

    }

    // line 140
    public function block_body($context, array $blocks = array())
    {
        $__internal_feb02a8a03a2346e00c8998902bdb892a4aeaae8780b2c61dbd8df92b3d07f36 = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_feb02a8a03a2346e00c8998902bdb892a4aeaae8780b2c61dbd8df92b3d07f36->enter($__internal_feb02a8a03a2346e00c8998902bdb892a4aeaae8780b2c61dbd8df92b3d07f36_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "body"));

        $__internal_2c2b6718ccb580bcedb63bba8df7de9acf94c8369e17d08a64d7034a4ddf850e = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_2c2b6718ccb580bcedb63bba8df7de9acf94c8369e17d08a64d7034a4ddf850e->enter($__internal_2c2b6718ccb580bcedb63bba8df7de9acf94c8369e17d08a64d7034a4ddf850e_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "body"));

        // line 141
        echo "    ";
        $this->loadTemplate("@Twig/Exception/exception.html.twig", "@Twig/Exception/exception_full.html.twig", 141)->display($context);
        
        $__internal_2c2b6718ccb580bcedb63bba8df7de9acf94c8369e17d08a64d7034a4ddf850e->leave($__internal_2c2b6718ccb580bcedb63bba8df7de9acf94c8369e17d08a64d7034a4ddf850e_prof);

        
        $__internal_feb02a8a03a2346e00c8998902bdb892a4aeaae8780b2c61dbd8df92b3d07f36->leave($__internal_feb02a8a03a2346e00c8998902bdb892a4aeaae8780b2c61dbd8df92b3d07f36_prof);

    }

    public function getTemplateName()
    {
        return "@Twig/Exception/exception_full.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  226 => 141,  217 => 140,  200 => 137,  191 => 136,  51 => 4,  42 => 3,  11 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("{% extends '@Twig/layout.html.twig' %}

{% block head %}
    <style>
        .sf-reset .traces {
            padding-bottom: 14px;
        }
        .sf-reset .traces li {
            font-size: 12px;
            color: #868686;
            padding: 5px 4px;
            list-style-type: decimal;
            margin-left: 20px;
        }
        .sf-reset #logs .traces li.error {
            font-style: normal;
            color: #AA3333;
            background: #f9ecec;
        }
        .sf-reset #logs .traces li.warning {
            font-style: normal;
            background: #ffcc00;
        }
        /* fix for Opera not liking empty <li> */
        .sf-reset .traces li:after {
            content: \"\\00A0\";
        }
        .sf-reset .trace {
            border: 1px solid #D3D3D3;
            padding: 10px;
            overflow: auto;
            margin: 10px 0 20px;
        }
        .sf-reset .block-exception {
            -moz-border-radius: 16px;
            -webkit-border-radius: 16px;
            border-radius: 16px;
            margin-bottom: 20px;
            background-color: #f6f6f6;
            border: 1px solid #dfdfdf;
            padding: 30px 28px;
            word-wrap: break-word;
            overflow: hidden;
        }
        .sf-reset .block-exception div {
            color: #313131;
            font-size: 10px;
        }
        .sf-reset .block-exception-detected .illustration-exception,
        .sf-reset .block-exception-detected .text-exception {
            float: left;
        }
        .sf-reset .block-exception-detected .illustration-exception {
            width: 152px;
        }
        .sf-reset .block-exception-detected .text-exception {
            width: 670px;
            padding: 30px 44px 24px 46px;
            position: relative;
        }
        .sf-reset .text-exception .open-quote,
        .sf-reset .text-exception .close-quote {
            font-family: Arial, Helvetica, sans-serif;
            position: absolute;
            color: #C9C9C9;
            font-size: 8em;
        }
        .sf-reset .open-quote {
            top: 0;
            left: 0;
        }
        .sf-reset .close-quote {
            bottom: -0.5em;
            right: 50px;
        }
        .sf-reset .block-exception p {
            font-family: Arial, Helvetica, sans-serif;
        }
        .sf-reset .block-exception p a,
        .sf-reset .block-exception p a:hover {
            color: #565656;
        }
        .sf-reset .logs h2 {
            float: left;
            width: 654px;
        }
        .sf-reset .error-count, .sf-reset .support {
            float: right;
            width: 170px;
            text-align: right;
        }
        .sf-reset .error-count span {
             display: inline-block;
             background-color: #aacd4e;
             -moz-border-radius: 6px;
             -webkit-border-radius: 6px;
             border-radius: 6px;
             padding: 4px;
             color: white;
             margin-right: 2px;
             font-size: 11px;
             font-weight: bold;
        }

        .sf-reset .support a {
            display: inline-block;
            -moz-border-radius: 6px;
            -webkit-border-radius: 6px;
            border-radius: 6px;
            padding: 4px;
            color: #000000;
            margin-right: 2px;
            font-size: 11px;
            font-weight: bold;
        }

        .sf-reset .toggle {
            vertical-align: middle;
        }
        .sf-reset .linked ul,
        .sf-reset .linked li {
            display: inline;
        }
        .sf-reset #output-content {
            color: #000;
            font-size: 12px;
        }
        .sf-reset #traces-text pre {
            white-space: pre;
            font-size: 12px;
            font-family: monospace;
        }
    </style>
{% endblock %}

{% block title %}
    {{ exception.message }} ({{ status_code }} {{ status_text }})
{% endblock %}

{% block body %}
    {% include '@Twig/Exception/exception.html.twig' %}
{% endblock %}
", "@Twig/Exception/exception_full.html.twig", "C:\\Users\\ch3\\Documents\\MyDocs\\WCMC\\ORDER\\scanorder\\Scanorders2\\vendor\\symfony\\symfony\\src\\Symfony\\Bundle\\TwigBundle\\Resources\\views\\Exception\\exception_full.html.twig");
    }
}
