<?php

/* OlegOrderformBundle:MultyScanOrder:new_test.html.twig */
class __TwigTemplate_b846fef0ff137248b9f5d917f5f8420c extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = $this->env->loadTemplate("OlegOrderformBundle::Default/base.html.twig");

        $this->blocks = array(
            'content' => array($this, 'block_content'),
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

    // line 5
    public function block_content($context, array $blocks = array())
    {
        // line 6
        echo "    ";
        echo         $this->env->getExtension('form')->renderer->renderBlock((isset($context["form"]) ? $context["form"] : null), 'form_start');
        echo "
    ";
        // line 8
        echo "
    ";
        // line 10
        echo "    <ul id=\"email-fields-list\"
        data-prototype-specimen=\"";
        // line 11
        echo twig_escape_filter($this->env, $this->env->getExtension('form')->renderer->searchAndRenderBlock($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "patient"), "vars"), "prototype"), "children"), "specimen", array(), "array"), "vars"), "prototype"), 'widget'));
        echo "\"
        data-prototype=\"";
        // line 12
        echo twig_escape_filter($this->env, $this->env->getExtension('form')->renderer->searchAndRenderBlock($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "patient"), "vars"), "prototype"), 'widget'));
        echo "\"
            >
        ";
        // line 14
        $context['_parent'] = (array) $context;
        $context['_seq'] = twig_ensure_traversable($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "patient"));
        foreach ($context['_seq'] as $context["_key"] => $context["patient"]) {
            // line 15
            echo "            <li>
                ";
            // line 16
            echo $this->env->getExtension('form')->renderer->searchAndRenderBlock((isset($context["patient"]) ? $context["patient"] : null), 'errors');
            echo "
                ";
            // line 17
            echo $this->env->getExtension('form')->renderer->searchAndRenderBlock((isset($context["patient"]) ? $context["patient"] : null), 'widget');
            echo "

                ";
            // line 19
            $context['_parent'] = (array) $context;
            $context['_seq'] = twig_ensure_traversable($this->getAttribute((isset($context["patient"]) ? $context["patient"] : null), "specimen"));
            foreach ($context['_seq'] as $context["_key"] => $context["specimen"]) {
                // line 20
                echo "                    <li>
                        ";
                // line 21
                echo $this->env->getExtension('form')->renderer->searchAndRenderBlock((isset($context["specimen"]) ? $context["specimen"] : null), 'errors');
                echo "
                        ";
                // line 22
                echo $this->env->getExtension('form')->renderer->searchAndRenderBlock((isset($context["specimen"]) ? $context["specimen"] : null), 'widget');
                echo "
                    </li>
                ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['specimen'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 25
            echo "

            </li>
        ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['patient'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 29
        echo "    </ul>

    <a href=\"#\" id=\"add-another-email\">Add another patient</a>
    <br>
    ";
        // line 34
        echo "
    ";
        // line 36
        echo "    ";
        echo         $this->env->getExtension('form')->renderer->renderBlock((isset($context["form"]) ? $context["form"] : null), 'form_end');
        echo "



";
    }

    public function getTemplateName()
    {
        return "OlegOrderformBundle:MultyScanOrder:new_test.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  848 => 18,  834 => 12,  823 => 11,  817 => 323,  814 => 321,  803 => 316,  790 => 305,  784 => 302,  770 => 294,  759 => 286,  753 => 283,  744 => 277,  738 => 274,  732 => 270,  729 => 268,  723 => 264,  702 => 252,  694 => 247,  688 => 244,  672 => 230,  665 => 229,  660 => 227,  656 => 224,  649 => 223,  646 => 222,  641 => 218,  635 => 217,  632 => 216,  627 => 212,  621 => 211,  618 => 210,  612 => 205,  606 => 204,  604 => 203,  602 => 202,  597 => 199,  591 => 198,  588 => 197,  583 => 194,  579 => 193,  575 => 192,  570 => 191,  566 => 189,  562 => 188,  558 => 187,  554 => 186,  550 => 185,  547 => 184,  529 => 182,  527 => 181,  515 => 177,  509 => 175,  506 => 174,  501 => 173,  498 => 172,  488 => 167,  485 => 166,  467 => 163,  463 => 162,  455 => 159,  449 => 157,  446 => 156,  441 => 155,  438 => 154,  429 => 150,  425 => 149,  421 => 148,  417 => 147,  406 => 144,  392 => 142,  386 => 140,  378 => 137,  372 => 135,  369 => 134,  364 => 133,  361 => 132,  356 => 129,  352 => 128,  348 => 127,  345 => 126,  333 => 124,  331 => 123,  327 => 122,  323 => 121,  317 => 119,  306 => 116,  301 => 113,  297 => 112,  267 => 103,  259 => 100,  242 => 95,  237 => 92,  221 => 88,  213 => 86,  200 => 82,  190 => 78,  118 => 52,  153 => 62,  102 => 34,  100 => 47,  113 => 39,  110 => 50,  97 => 37,  1357 => 388,  1348 => 387,  1346 => 386,  1343 => 385,  1327 => 381,  1320 => 380,  1318 => 379,  1315 => 378,  1292 => 374,  1267 => 373,  1265 => 372,  1262 => 371,  1250 => 366,  1245 => 365,  1243 => 364,  1240 => 363,  1231 => 357,  1225 => 355,  1222 => 354,  1217 => 353,  1215 => 352,  1212 => 351,  1205 => 346,  1196 => 344,  1192 => 343,  1189 => 342,  1186 => 341,  1184 => 340,  1181 => 339,  1173 => 335,  1171 => 334,  1168 => 333,  1162 => 329,  1156 => 327,  1153 => 326,  1151 => 325,  1148 => 324,  1139 => 319,  1137 => 318,  1114 => 317,  1111 => 316,  1108 => 315,  1105 => 314,  1102 => 313,  1099 => 312,  1096 => 311,  1094 => 310,  1091 => 309,  1084 => 305,  1080 => 304,  1075 => 303,  1073 => 302,  1070 => 301,  1063 => 296,  1060 => 295,  1052 => 290,  1049 => 289,  1047 => 288,  1044 => 287,  1036 => 282,  1032 => 281,  1028 => 280,  1025 => 279,  1023 => 278,  1020 => 277,  1012 => 273,  1010 => 269,  1008 => 268,  1005 => 267,  1000 => 263,  978 => 258,  975 => 257,  972 => 256,  969 => 255,  966 => 254,  963 => 253,  960 => 252,  957 => 251,  954 => 250,  951 => 249,  948 => 248,  946 => 247,  943 => 246,  935 => 240,  932 => 239,  930 => 238,  927 => 237,  919 => 233,  916 => 232,  914 => 231,  911 => 230,  899 => 226,  896 => 225,  893 => 224,  890 => 223,  888 => 222,  885 => 221,  877 => 217,  874 => 216,  872 => 215,  869 => 214,  861 => 210,  858 => 209,  856 => 208,  853 => 20,  845 => 203,  842 => 15,  840 => 201,  837 => 13,  829 => 196,  826 => 195,  824 => 194,  821 => 193,  813 => 189,  810 => 188,  808 => 317,  805 => 186,  797 => 182,  794 => 181,  792 => 180,  789 => 179,  781 => 175,  779 => 174,  776 => 297,  768 => 169,  765 => 168,  763 => 167,  760 => 166,  752 => 162,  749 => 161,  747 => 160,  745 => 159,  742 => 158,  735 => 153,  725 => 152,  720 => 151,  717 => 261,  711 => 148,  708 => 255,  706 => 146,  703 => 145,  695 => 139,  693 => 138,  692 => 137,  691 => 136,  690 => 135,  685 => 134,  679 => 132,  676 => 131,  674 => 130,  671 => 129,  662 => 228,  658 => 122,  654 => 121,  650 => 120,  645 => 119,  639 => 117,  636 => 116,  634 => 115,  631 => 114,  615 => 110,  613 => 109,  610 => 108,  594 => 104,  592 => 103,  589 => 102,  572 => 98,  560 => 96,  553 => 93,  551 => 92,  546 => 91,  543 => 90,  525 => 89,  523 => 180,  520 => 87,  511 => 82,  508 => 81,  505 => 80,  499 => 78,  497 => 77,  492 => 168,  489 => 75,  486 => 74,  471 => 72,  459 => 69,  456 => 68,  450 => 64,  442 => 62,  433 => 151,  428 => 59,  426 => 58,  414 => 52,  408 => 50,  405 => 49,  403 => 48,  400 => 47,  390 => 141,  388 => 42,  385 => 41,  377 => 37,  371 => 35,  366 => 33,  363 => 32,  350 => 26,  344 => 24,  342 => 23,  335 => 21,  332 => 20,  316 => 16,  313 => 15,  311 => 14,  308 => 13,  299 => 8,  293 => 111,  290 => 5,  281 => 385,  271 => 104,  266 => 363,  263 => 362,  260 => 360,  255 => 350,  253 => 98,  250 => 97,  248 => 333,  245 => 96,  240 => 323,  238 => 309,  233 => 91,  230 => 300,  227 => 298,  217 => 87,  215 => 277,  212 => 276,  210 => 85,  207 => 266,  204 => 264,  202 => 83,  197 => 246,  194 => 245,  191 => 243,  186 => 236,  184 => 76,  181 => 229,  179 => 74,  174 => 73,  161 => 199,  146 => 62,  104 => 87,  74 => 21,  34 => 6,  83 => 22,  152 => 49,  145 => 46,  131 => 157,  129 => 145,  124 => 129,  65 => 26,  120 => 41,  20 => 2,  90 => 32,  76 => 37,  291 => 61,  288 => 4,  279 => 43,  276 => 378,  273 => 105,  262 => 28,  257 => 27,  246 => 80,  243 => 324,  225 => 89,  222 => 294,  218 => 72,  180 => 71,  172 => 63,  170 => 60,  159 => 193,  150 => 63,  134 => 59,  81 => 34,  63 => 25,  96 => 29,  77 => 32,  58 => 16,  52 => 18,  59 => 30,  53 => 18,  23 => 3,  480 => 162,  474 => 161,  469 => 164,  461 => 70,  457 => 153,  453 => 151,  444 => 149,  440 => 148,  437 => 61,  435 => 146,  430 => 144,  427 => 143,  423 => 57,  413 => 146,  409 => 145,  407 => 131,  402 => 130,  398 => 129,  393 => 126,  387 => 122,  384 => 121,  381 => 120,  379 => 119,  374 => 36,  368 => 34,  365 => 111,  362 => 110,  360 => 109,  355 => 27,  341 => 105,  337 => 22,  322 => 101,  314 => 118,  312 => 98,  309 => 117,  305 => 95,  298 => 91,  294 => 90,  285 => 3,  283 => 107,  278 => 384,  268 => 370,  264 => 84,  258 => 351,  252 => 80,  247 => 78,  241 => 77,  235 => 308,  229 => 90,  224 => 71,  220 => 287,  214 => 69,  208 => 68,  169 => 207,  143 => 58,  140 => 55,  132 => 38,  128 => 56,  119 => 52,  111 => 107,  107 => 48,  71 => 20,  177 => 65,  165 => 64,  160 => 61,  139 => 166,  135 => 39,  126 => 53,  114 => 51,  84 => 26,  70 => 13,  67 => 19,  61 => 2,  47 => 15,  38 => 24,  94 => 57,  89 => 34,  85 => 36,  79 => 32,  75 => 14,  68 => 35,  56 => 32,  50 => 12,  29 => 5,  87 => 25,  72 => 36,  55 => 15,  21 => 4,  26 => 3,  98 => 24,  93 => 35,  88 => 37,  78 => 22,  46 => 12,  27 => 4,  40 => 9,  44 => 9,  35 => 3,  31 => 6,  43 => 6,  41 => 25,  28 => 5,  201 => 92,  196 => 81,  183 => 70,  171 => 72,  166 => 206,  163 => 70,  158 => 65,  156 => 192,  151 => 185,  142 => 61,  138 => 60,  136 => 165,  123 => 31,  121 => 128,  117 => 25,  115 => 40,  105 => 36,  101 => 32,  91 => 56,  69 => 28,  66 => 19,  62 => 17,  49 => 8,  24 => 2,  32 => 5,  25 => 29,  22 => 2,  19 => 1,  209 => 82,  203 => 78,  199 => 262,  193 => 73,  189 => 237,  187 => 84,  182 => 75,  176 => 220,  173 => 74,  168 => 70,  164 => 200,  162 => 66,  154 => 64,  149 => 44,  147 => 50,  144 => 173,  141 => 41,  133 => 48,  130 => 54,  125 => 52,  122 => 53,  116 => 51,  112 => 43,  109 => 102,  106 => 49,  103 => 26,  99 => 68,  95 => 46,  92 => 44,  86 => 41,  82 => 40,  80 => 23,  73 => 30,  64 => 34,  60 => 33,  57 => 10,  54 => 13,  51 => 14,  48 => 27,  45 => 6,  42 => 11,  39 => 10,  36 => 8,  33 => 4,  30 => 3,);
    }
}
