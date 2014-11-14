<?php

/* OlegOrderformBundle:Default:collection_widget.html.twig */
class __TwigTemplate_7863ab3d90101bddf40469c29255f2c0 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
            'collection_widget' => array($this, 'block_collection_widget'),
            'collection_item_widget' => array($this, 'block_collection_item_widget'),
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 1
        $this->displayBlock('collection_widget', $context, $blocks);
        // line 38
        echo "
";
        // line 39
        $this->displayBlock('collection_item_widget', $context, $blocks);
    }

    // line 1
    public function block_collection_widget($context, array $blocks = array())
    {
        // line 2
        ob_start();
        // line 3
        echo "    <div class=\"collection\">
        ";
        // line 4
        if (array_key_exists("prototype", $context)) {
            // line 5
            echo "            ";
            $context["attr"] = twig_array_merge((isset($context["attr"]) ? $context["attr"] : null), array("data-prototype" => $this->renderBlock("collection_item_widget", $context, $blocks)));
            // line 6
            echo "        ";
        }
        // line 7
        echo "        <div ";
        $this->displayBlock("widget_container_attributes", $context, $blocks);
        echo ">
            ";
        // line 8
        echo $this->env->getExtension('form')->renderer->searchAndRenderBlock((isset($context["form"]) ? $context["form"] : null), 'errors');
        echo "
            <ul>
            ";
        // line 10
        $context['_parent'] = (array) $context;
        $context['_seq'] = twig_ensure_traversable((isset($context["form"]) ? $context["form"] : null));
        foreach ($context['_seq'] as $context["_key"] => $context["rows"]) {
            // line 11
            echo "                <li>
                ";
            // line 12
            $context["fieldNum"] = 1;
            // line 13
            echo "                ";
            $context['_parent'] = (array) $context;
            $context['_seq'] = twig_ensure_traversable((isset($context["rows"]) ? $context["rows"] : null));
            foreach ($context['_seq'] as $context["_key"] => $context["row"]) {
                // line 14
                echo "                    <div class=\"field";
                echo twig_escape_filter($this->env, (isset($context["fieldNum"]) ? $context["fieldNum"] : null), "html", null, true);
                echo "\">
                    ";
                // line 15
                echo $this->env->getExtension('form')->renderer->searchAndRenderBlock((isset($context["row"]) ? $context["row"] : null), 'label');
                echo "
                    ";
                // line 16
                echo $this->env->getExtension('form')->renderer->searchAndRenderBlock((isset($context["row"]) ? $context["row"] : null), 'widget', array("attr" => array("class" => "test")));
                echo "
                    </div>
                    ";
                // line 18
                $context["fieldNum"] = ((isset($context["fieldNum"]) ? $context["fieldNum"] : null) + 1);
                // line 19
                echo "                ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['row'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 20
            echo "                    <a class=\"remove\" title=\"Remove\" href=\"javascript:void()\">
                        <span>Delete</span>
                    </a>
                    <div class=\"clear\"></div>
                </li>
            ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['rows'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 26
        echo "            </ul>
            ";
        // line 27
        echo $this->env->getExtension('form')->renderer->searchAndRenderBlock((isset($context["form"]) ? $context["form"] : null), 'rest');
        echo "
        </div>
        <div class=\"clear\"></div>
        <a class=\"add\" title=\"Add\" href=\"javascript:void()\">
            <div style=\"display: none;\"></div>
            <span>Add</span>
        </a>
    </div>
    <div class=\"clear\"></div>
";
        echo trim(preg_replace('/>\s+</', '><', ob_get_clean()));
    }

    // line 39
    public function block_collection_item_widget($context, array $blocks = array())
    {
        // line 40
        ob_start();
        // line 41
        echo "    <li>
    ";
        // line 42
        $context["fieldNum"] = 1;
        // line 43
        echo "    ";
        $context['_parent'] = (array) $context;
        $context['_seq'] = twig_ensure_traversable((isset($context["prototype"]) ? $context["prototype"] : null));
        foreach ($context['_seq'] as $context["_key"] => $context["row"]) {
            // line 44
            echo "        <div class=\"field";
            echo twig_escape_filter($this->env, (isset($context["fieldNum"]) ? $context["fieldNum"] : null), "html", null, true);
            echo "\">
        ";
            // line 45
            echo $this->env->getExtension('form')->renderer->searchAndRenderBlock((isset($context["row"]) ? $context["row"] : null), 'label');
            echo "
        ";
            // line 46
            echo $this->env->getExtension('form')->renderer->searchAndRenderBlock((isset($context["row"]) ? $context["row"] : null), 'widget', array("attr" => array("class" => "test")));
            echo "
        </div>
        ";
            // line 48
            $context["fieldNum"] = ((isset($context["fieldNum"]) ? $context["fieldNum"] : null) + 1);
            // line 49
            echo "    ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['row'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 50
        echo "        <a class=\"remove\" title=\"Remove\" href=\"javascript:void()\">
            <span>Delete</span>
        </a>
        <div class=\"clear\"></div>
    </li>
";
        echo trim(preg_replace('/>\s+</', '><', ob_get_clean()));
    }

    public function getTemplateName()
    {
        return "OlegOrderformBundle:Default:collection_widget.html.twig";
    }

    public function getDebugInfo()
    {
        return array (  152 => 49,  145 => 46,  131 => 43,  129 => 42,  124 => 40,  65 => 13,  120 => 53,  20 => 1,  90 => 32,  76 => 21,  291 => 61,  288 => 60,  279 => 43,  276 => 42,  273 => 40,  262 => 28,  257 => 27,  246 => 80,  243 => 79,  225 => 75,  222 => 74,  218 => 72,  180 => 71,  172 => 63,  170 => 60,  159 => 54,  150 => 48,  134 => 40,  81 => 25,  63 => 12,  96 => 38,  77 => 33,  58 => 19,  52 => 18,  59 => 6,  53 => 18,  23 => 38,  480 => 162,  474 => 161,  469 => 158,  461 => 155,  457 => 153,  453 => 151,  444 => 149,  440 => 148,  437 => 147,  435 => 146,  430 => 144,  427 => 143,  423 => 142,  413 => 134,  409 => 132,  407 => 131,  402 => 130,  398 => 129,  393 => 126,  387 => 122,  384 => 121,  381 => 120,  379 => 119,  374 => 116,  368 => 112,  365 => 111,  362 => 110,  360 => 109,  355 => 106,  341 => 105,  337 => 103,  322 => 101,  314 => 99,  312 => 98,  309 => 97,  305 => 95,  298 => 91,  294 => 90,  285 => 89,  283 => 55,  278 => 86,  268 => 85,  264 => 84,  258 => 81,  252 => 80,  247 => 78,  241 => 77,  235 => 74,  229 => 78,  224 => 71,  220 => 70,  214 => 69,  208 => 68,  169 => 60,  143 => 49,  140 => 55,  132 => 51,  128 => 49,  119 => 42,  111 => 37,  107 => 36,  71 => 18,  177 => 65,  165 => 64,  160 => 61,  139 => 50,  135 => 47,  126 => 41,  114 => 42,  84 => 18,  70 => 14,  67 => 24,  61 => 21,  47 => 15,  38 => 4,  94 => 22,  89 => 34,  85 => 25,  79 => 16,  75 => 15,  68 => 14,  56 => 10,  50 => 10,  29 => 4,  87 => 33,  72 => 31,  55 => 15,  21 => 1,  26 => 39,  98 => 38,  93 => 28,  88 => 6,  78 => 29,  46 => 7,  27 => 4,  40 => 5,  44 => 15,  35 => 3,  31 => 4,  43 => 6,  41 => 7,  28 => 3,  201 => 92,  196 => 90,  183 => 70,  171 => 61,  166 => 71,  163 => 70,  158 => 50,  156 => 58,  151 => 57,  142 => 59,  138 => 57,  136 => 44,  123 => 31,  121 => 39,  117 => 25,  115 => 43,  105 => 40,  101 => 39,  91 => 17,  69 => 11,  66 => 20,  62 => 16,  49 => 9,  24 => 3,  32 => 5,  25 => 3,  22 => 2,  19 => 1,  209 => 82,  203 => 78,  199 => 67,  193 => 73,  189 => 71,  187 => 84,  182 => 66,  176 => 65,  173 => 74,  168 => 66,  164 => 56,  162 => 55,  154 => 54,  149 => 51,  147 => 50,  144 => 53,  141 => 45,  133 => 55,  130 => 41,  125 => 44,  122 => 43,  116 => 51,  112 => 43,  109 => 41,  106 => 27,  103 => 26,  99 => 21,  95 => 20,  92 => 20,  86 => 19,  82 => 31,  80 => 19,  73 => 27,  64 => 26,  60 => 11,  57 => 19,  54 => 16,  51 => 8,  48 => 16,  45 => 16,  42 => 8,  39 => 11,  36 => 5,  33 => 2,  30 => 1,);
    }
}
