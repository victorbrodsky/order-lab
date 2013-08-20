<?php

/* OlegOrderformBundle:Block:new.html_origmodified.twig */
class __TwigTemplate_0863ba9f722145dac68dc1f62d0f47aa extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = $this->env->loadTemplate("OlegOrderformBundle::Default/base.html.twig");

        $this->blocks = array(
            'content' => array($this, 'block_content'),
            'body' => array($this, 'block_body'),
        );
    }

    protected function doGetParent(array $context)
    {
        return "OlegOrderformBundle::Default/base.html.twig";
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $this->parent->display($context, array_merge($this->blocks, $blocks));
    }

    // line 4
    public function block_content($context, array $blocks = array())
    {
        // line 5
        $this->displayBlock('body', $context, $blocks);
    }

    public function block_body($context, array $blocks = array())
    {
        // line 6
        echo "<h1>Block creation</h1>

    <form action=\"";
        // line 8
        echo $this->env->getExtension('routing')->getPath("block_create");
        echo "\" method=\"post\" ";
        echo $this->env->getExtension('form')->renderer->searchAndRenderBlock((isset($context["form"]) ? $context["form"] : $this->getContext($context, "form")), 'enctype');
;
        echo ">
        ";
        // line 9
        echo $this->env->getExtension('form')->renderer->searchAndRenderBlock((isset($context["form"]) ? $context["form"] : $this->getContext($context, "form")), 'widget');
        echo "
        
        
        <h3>Slides:</h3>
        <ul class=\"tags\">
            ";
        // line 15
        echo "            ";
        $context['_parent'] = (array) $context;
        $context['_seq'] = twig_ensure_traversable($this->getAttribute((isset($context["form"]) ? $context["form"] : $this->getContext($context, "form")), "slide"));
        foreach ($context['_seq'] as $context["_key"] => $context["slide"]) {
            // line 16
            echo "                <li>";
            echo $this->env->getExtension('form')->renderer->searchAndRenderBlock($this->getAttribute((isset($context["slide"]) ? $context["slide"] : $this->getContext($context, "slide")), "id"), 'row');
            echo "</li>
            ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['slide'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 18
        echo "        </ul>
        
        
        <ul class=\"tags\" data-prototype=\"";
        // line 21
        echo twig_escape_filter($this->env, $this->env->getExtension('form')->renderer->searchAndRenderBlock($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["form"]) ? $context["form"] : $this->getContext($context, "form")), "slide"), "vars"), "prototype"), 'widget'));
        echo "\">
            ffffffffffffffffff
        </ul>
               
        <p>
            <button type=\"submit\">Create</button>
        </p>
    </form>

        <ul class=\"record_actions\">
    <li>
        <a href=\"";
        // line 32
        echo $this->env->getExtension('routing')->getPath("block");
        echo "\">
            Back to the list
        </a>
    </li>
</ul>
";
    }

    public function getTemplateName()
    {
        return "OlegOrderformBundle:Block:new.html_origmodified.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  90 => 32,  76 => 21,  291 => 61,  288 => 60,  279 => 43,  276 => 42,  273 => 40,  262 => 28,  257 => 27,  243 => 79,  225 => 75,  222 => 74,  218 => 72,  180 => 71,  172 => 63,  170 => 60,  159 => 54,  150 => 51,  134 => 40,  81 => 25,  63 => 25,  77 => 27,  58 => 19,  59 => 6,  53 => 18,  23 => 1,  480 => 162,  474 => 161,  469 => 158,  461 => 155,  457 => 153,  453 => 151,  444 => 149,  440 => 148,  437 => 147,  435 => 146,  430 => 144,  427 => 143,  423 => 142,  413 => 134,  409 => 132,  407 => 131,  402 => 130,  398 => 129,  393 => 126,  387 => 122,  384 => 121,  381 => 120,  379 => 119,  374 => 116,  368 => 112,  365 => 111,  362 => 110,  360 => 109,  355 => 106,  341 => 105,  337 => 103,  322 => 101,  314 => 99,  312 => 98,  309 => 97,  305 => 95,  298 => 91,  294 => 90,  285 => 89,  283 => 55,  278 => 86,  268 => 85,  264 => 84,  258 => 81,  252 => 80,  247 => 78,  241 => 77,  235 => 74,  229 => 78,  224 => 71,  220 => 70,  214 => 69,  208 => 68,  169 => 60,  143 => 49,  140 => 55,  132 => 51,  128 => 49,  119 => 42,  107 => 36,  71 => 18,  177 => 65,  165 => 64,  160 => 61,  135 => 47,  126 => 45,  114 => 42,  84 => 37,  70 => 25,  67 => 23,  61 => 19,  38 => 6,  94 => 22,  89 => 36,  85 => 25,  75 => 22,  68 => 14,  56 => 9,  87 => 20,  21 => 2,  26 => 6,  93 => 28,  88 => 6,  78 => 24,  46 => 13,  27 => 4,  44 => 15,  31 => 6,  28 => 5,  201 => 92,  196 => 90,  183 => 70,  171 => 61,  166 => 71,  163 => 70,  158 => 67,  156 => 58,  151 => 57,  142 => 59,  138 => 57,  136 => 44,  121 => 27,  117 => 25,  105 => 40,  91 => 17,  62 => 16,  49 => 9,  24 => 1,  25 => 3,  19 => 1,  79 => 35,  72 => 16,  69 => 11,  47 => 15,  40 => 9,  37 => 10,  22 => 2,  246 => 80,  157 => 56,  145 => 46,  139 => 50,  131 => 42,  123 => 31,  120 => 40,  115 => 43,  111 => 37,  108 => 37,  101 => 32,  98 => 31,  96 => 38,  83 => 25,  74 => 14,  66 => 20,  55 => 15,  52 => 18,  50 => 10,  43 => 10,  41 => 7,  35 => 7,  32 => 5,  29 => 4,  209 => 82,  203 => 78,  199 => 67,  193 => 73,  189 => 71,  187 => 84,  182 => 66,  176 => 65,  173 => 74,  168 => 66,  164 => 56,  162 => 55,  154 => 54,  149 => 51,  147 => 50,  144 => 53,  141 => 51,  133 => 55,  130 => 41,  125 => 44,  122 => 43,  116 => 36,  112 => 42,  109 => 41,  106 => 36,  103 => 24,  99 => 21,  95 => 20,  92 => 33,  86 => 28,  82 => 31,  80 => 19,  73 => 19,  64 => 22,  60 => 6,  57 => 15,  54 => 16,  51 => 14,  48 => 16,  45 => 16,  42 => 8,  39 => 10,  36 => 5,  33 => 7,  30 => 7,);
    }
}
