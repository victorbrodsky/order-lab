<?php

/* OlegOrderformBundle:Default:fields.html.twig */
class __TwigTemplate_d346eeb2359f13635fd7759e7edddafe extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
            'choice_widget_collapsed' => array($this, 'block_choice_widget_collapsed'),
            'choice_widget_options' => array($this, 'block_choice_widget_options'),
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 4
        echo "           
";
        // line 5
        $this->displayBlock('choice_widget_collapsed', $context, $blocks);
        // line 23
        echo "
";
        // line 24
        $this->displayBlock('choice_widget_options', $context, $blocks);
        // line 39
        echo "               
                 ";
    }

    // line 5
    public function block_choice_widget_collapsed($context, array $blocks = array())
    {
        // line 6
        ob_start();
        // line 7
        echo "    <select class=\"combobox\" ";
        $this->displayBlock("widget_attributes", $context, $blocks);
        if ((isset($context["multiple"]) ? $context["multiple"] : null)) {
            echo " multiple=\"multiple\"";
        }
        echo ">
        ";
        // line 8
        if ((!(null === (isset($context["empty_value"]) ? $context["empty_value"] : null)))) {
            // line 9
            echo "            <option value=\"\" ";
            if ((isset($context["required"]) ? $context["required"] : null)) {
                echo " disabled=\"disabled\"";
                if (twig_test_empty((isset($context["value"]) ? $context["value"] : null))) {
                    echo " selected=\"selected\"";
                }
            }
            echo ">";
            echo twig_escape_filter($this->env, $this->env->getExtension('translator')->trans((isset($context["empty_value"]) ? $context["empty_value"] : null), array(), (isset($context["translation_domain"]) ? $context["translation_domain"] : null)), "html", null, true);
            echo "</option>
        ";
        }
        // line 11
        echo "        ";
        if ((twig_length_filter($this->env, (isset($context["preferred_choices"]) ? $context["preferred_choices"] : null)) > 0)) {
            // line 12
            echo "            ";
            $context["options"] = (isset($context["preferred_choices"]) ? $context["preferred_choices"] : null);
            // line 13
            echo "            ";
            $this->displayBlock("choice_widget_options", $context, $blocks);
            echo "
            ";
            // line 14
            if (((twig_length_filter($this->env, (isset($context["choices"]) ? $context["choices"] : null)) > 0) && (!(null === (isset($context["separator"]) ? $context["separator"] : null))))) {
                // line 15
                echo "                <option disabled=\"disabled\">";
                echo twig_escape_filter($this->env, (isset($context["separator"]) ? $context["separator"] : null), "html", null, true);
                echo "</option>
            ";
            }
            // line 17
            echo "        ";
        }
        // line 18
        echo "        ";
        $context["options"] = (isset($context["choices"]) ? $context["choices"] : null);
        // line 19
        echo "        ";
        $this->displayBlock("choice_widget_options", $context, $blocks);
        echo "
    </select>
";
        echo trim(preg_replace('/>\s+</', '><', ob_get_clean()));
    }

    // line 24
    public function block_choice_widget_options($context, array $blocks = array())
    {
        // line 25
        ob_start();
        // line 26
        echo "    ";
        $context['_parent'] = (array) $context;
        $context['_seq'] = twig_ensure_traversable((isset($context["options"]) ? $context["options"] : null));
        $context['loop'] = array(
          'parent' => $context['_parent'],
          'index0' => 0,
          'index'  => 1,
          'first'  => true,
        );
        if (is_array($context['_seq']) || (is_object($context['_seq']) && $context['_seq'] instanceof Countable)) {
            $length = count($context['_seq']);
            $context['loop']['revindex0'] = $length - 1;
            $context['loop']['revindex'] = $length;
            $context['loop']['length'] = $length;
            $context['loop']['last'] = 1 === $length;
        }
        foreach ($context['_seq'] as $context["group_label"] => $context["choice"]) {
            // line 27
            echo "        ";
            if (twig_test_iterable((isset($context["choice"]) ? $context["choice"] : null))) {
                // line 28
                echo "            <optgroup label=\"";
                echo twig_escape_filter($this->env, $this->env->getExtension('translator')->trans((isset($context["group_label"]) ? $context["group_label"] : null), array(), (isset($context["translation_domain"]) ? $context["translation_domain"] : null)), "html", null, true);
                echo "\">
                ";
                // line 29
                $context["options"] = (isset($context["choice"]) ? $context["choice"] : null);
                // line 30
                echo "                ";
                $this->displayBlock("choice_widget_options", $context, $blocks);
                echo "
            </optgroup>
        ";
            } else {
                // line 33
                echo "            ";
                echo twig_escape_filter($this->env, $this->env->getExtension('translator')->trans($this->getAttribute((isset($context["choice"]) ? $context["choice"] : null), "label"), array(), (isset($context["translation_domain"]) ? $context["translation_domain"] : null)), "html", null, true);
                echo "
            <option value=\"";
                // line 34
                echo twig_escape_filter($this->env, $this->getAttribute((isset($context["choice"]) ? $context["choice"] : null), "label"), "html", null, true);
                echo "\"";
                if ($this->env->getExtension('form')->isSelectedChoice((isset($context["choice"]) ? $context["choice"] : null), (isset($context["value"]) ? $context["value"] : null))) {
                    echo " selected=\"selected\"";
                }
                echo ">";
                echo twig_escape_filter($this->env, $this->env->getExtension('translator')->trans($this->getAttribute((isset($context["choice"]) ? $context["choice"] : null), "label"), array(), (isset($context["translation_domain"]) ? $context["translation_domain"] : null)), "html", null, true);
                echo "</option>   
        ";
            }
            // line 36
            echo "    ";
            ++$context['loop']['index0'];
            ++$context['loop']['index'];
            $context['loop']['first'] = false;
            if (isset($context['loop']['length'])) {
                --$context['loop']['revindex0'];
                --$context['loop']['revindex'];
                $context['loop']['last'] = 0 === $context['loop']['revindex0'];
            }
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['group_label'], $context['choice'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        echo trim(preg_replace('/>\s+</', '><', ob_get_clean()));
    }

    public function getTemplateName()
    {
        return "OlegOrderformBundle:Default:fields.html.twig";
    }

    public function getDebugInfo()
    {
        return array (  83 => 17,  152 => 49,  145 => 46,  131 => 30,  129 => 29,  124 => 28,  65 => 13,  120 => 53,  20 => 1,  90 => 32,  76 => 21,  291 => 61,  288 => 60,  279 => 43,  276 => 42,  273 => 40,  262 => 28,  257 => 27,  246 => 80,  243 => 79,  225 => 75,  222 => 74,  218 => 72,  180 => 71,  172 => 63,  170 => 60,  159 => 54,  150 => 48,  134 => 40,  81 => 25,  63 => 12,  96 => 38,  77 => 15,  58 => 19,  52 => 18,  59 => 6,  53 => 18,  23 => 38,  480 => 162,  474 => 161,  469 => 158,  461 => 155,  457 => 153,  453 => 151,  444 => 149,  440 => 148,  437 => 147,  435 => 146,  430 => 144,  427 => 143,  423 => 142,  413 => 134,  409 => 132,  407 => 131,  402 => 130,  398 => 129,  393 => 126,  387 => 122,  384 => 121,  381 => 120,  379 => 119,  374 => 116,  368 => 112,  365 => 111,  362 => 110,  360 => 109,  355 => 106,  341 => 105,  337 => 103,  322 => 101,  314 => 99,  312 => 98,  309 => 97,  305 => 95,  298 => 91,  294 => 90,  285 => 89,  283 => 55,  278 => 86,  268 => 85,  264 => 84,  258 => 81,  252 => 80,  247 => 78,  241 => 77,  235 => 74,  229 => 78,  224 => 71,  220 => 70,  214 => 69,  208 => 68,  169 => 60,  143 => 34,  140 => 55,  132 => 51,  128 => 49,  119 => 42,  111 => 37,  107 => 36,  71 => 18,  177 => 65,  165 => 64,  160 => 61,  139 => 50,  135 => 47,  126 => 41,  114 => 42,  84 => 18,  70 => 13,  67 => 12,  61 => 21,  47 => 15,  38 => 4,  94 => 22,  89 => 19,  85 => 25,  79 => 16,  75 => 14,  68 => 14,  56 => 10,  50 => 10,  29 => 24,  87 => 33,  72 => 31,  55 => 15,  21 => 4,  26 => 23,  98 => 24,  93 => 28,  88 => 6,  78 => 29,  46 => 7,  27 => 4,  40 => 5,  44 => 15,  35 => 3,  31 => 39,  43 => 6,  41 => 7,  28 => 3,  201 => 92,  196 => 90,  183 => 70,  171 => 61,  166 => 71,  163 => 70,  158 => 50,  156 => 58,  151 => 57,  142 => 59,  138 => 33,  136 => 44,  123 => 31,  121 => 27,  117 => 25,  115 => 43,  105 => 40,  101 => 25,  91 => 17,  69 => 11,  66 => 20,  62 => 16,  49 => 8,  24 => 5,  32 => 5,  25 => 3,  22 => 2,  19 => 1,  209 => 82,  203 => 78,  199 => 67,  193 => 73,  189 => 71,  187 => 84,  182 => 66,  176 => 65,  173 => 74,  168 => 66,  164 => 56,  162 => 55,  154 => 36,  149 => 51,  147 => 50,  144 => 53,  141 => 45,  133 => 55,  130 => 41,  125 => 44,  122 => 43,  116 => 51,  112 => 43,  109 => 41,  106 => 27,  103 => 26,  99 => 21,  95 => 20,  92 => 20,  86 => 18,  82 => 31,  80 => 19,  73 => 27,  64 => 11,  60 => 11,  57 => 19,  54 => 16,  51 => 9,  48 => 16,  45 => 16,  42 => 8,  39 => 6,  36 => 5,  33 => 2,  30 => 1,);
    }
}
