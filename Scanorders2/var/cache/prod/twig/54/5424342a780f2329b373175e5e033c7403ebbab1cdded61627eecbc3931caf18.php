<?php

/* OlegUserdirectoryBundle::Tree/treemacros.html.twig */
class __TwigTemplate_6bcbb4e8c69a90685fd125b68ab912806af38c1182a718bdbb95431f4c3c65fb extends Twig_Template
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
";
        // line 18
        echo "
";
        // line 68
        echo "

";
        // line 87
        echo "
";
    }

    // line 19
    public function getjstreemacros($__jstreeid__ = null, $__bundleName__ = null, $__entityName__ = null, $__nodeshowpath__ = null, $__search__ = null, $__filterform__ = null, $__routename__ = null, ...$__varargs__)
    {
        $context = $this->env->mergeGlobals(array(
            "jstreeid" => $__jstreeid__,
            "bundleName" => $__bundleName__,
            "entityName" => $__entityName__,
            "nodeshowpath" => $__nodeshowpath__,
            "search" => $__search__,
            "filterform" => $__filterform__,
            "routename" => $__routename__,
            "varargs" => $__varargs__,
        ));

        $blocks = array();

        ob_start();
        try {
            // line 20
            echo "    ";
            // line 21
            echo "
    ";
            // line 22
            if ((((array_key_exists("filterform", $context) && (isset($context["filterform"]) ? $context["filterform"] : null)) && array_key_exists("routename", $context)) && (isset($context["routename"]) ? $context["routename"] : null))) {
                // line 23
                echo "        <p>
        <form action=\"";
                // line 24
                echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath((isset($context["routename"]) ? $context["routename"] : null));
                echo "\" method=\"get\" class=\"well form-search\" name=\"form-search\">
            <div class=\"row\">
                <div class=\"col-xs-3\"></div>

                <div class=\"col-xs-3\" align=\"right\">
                    ";
                // line 29
                echo $this->env->getRuntime('Symfony\Bridge\Twig\Form\TwigRenderer')->searchAndRenderBlock($this->getAttribute((isset($context["filterform"]) ? $context["filterform"] : null), "types", array()), 'widget');
                echo "
                </div>

                <div class=\"col-xs-3\" align=\"left\">
                    <button type=\"submit\" class=\"btn btn-sm btn-default\">Filter</button>
                </div>

                <div class=\"col-xs-3\"></div>
            </div>
            ";
                // line 38
                echo $this->env->getRuntime('Symfony\Bridge\Twig\Form\TwigRenderer')->searchAndRenderBlock((isset($context["filterform"]) ? $context["filterform"] : null), 'rest');
                echo "
        </form>
        </p>
    <br>
    ";
            }
            // line 43
            echo "
    <p>
    <div class=\"row jstree-parent-container\">

        <div class=\"col-xs-3\" align=\"right\">
        </div>

        <div class=\"col-xs-6\" align=\"left\">
            ";
            // line 51
            if (( !array_key_exists("search", $context) || ((isset($context["search"]) ? $context["search"] : null) != "nosearch"))) {
                // line 52
                echo "                <p>
                    <input class=\"form-control form-control-modif not-mapped-simplefield jstree-search\" type=\"search\" placeholder=\"Search\"/>
                </p>
            ";
            }
            // line 56
            echo "            <div
                class=\"";
            // line 57
            echo twig_escape_filter($this->env, (isset($context["jstreeid"]) ? $context["jstreeid"] : null), "html", null, true);
            echo " ";
            echo twig_escape_filter($this->env, (((((isset($context["jstreeid"]) ? $context["jstreeid"] : null) . "-") . (isset($context["bundleName"]) ? $context["bundleName"] : null)) . "-") . (isset($context["entityName"]) ? $context["entityName"] : null)), "html", null, true);
            echo "\"
                data-compositetree-node-showpath=\"";
            // line 58
            echo twig_escape_filter($this->env, (isset($context["nodeshowpath"]) ? $context["nodeshowpath"] : null), "html", null, true);
            echo "\"
            ></div>
        </div>

        <div class=\"col-xs-3\" align=\"right\">
        </div>

    </div>
    </p>
";
        } catch (Exception $e) {
            ob_end_clean();

            throw $e;
        } catch (Throwable $e) {
            ob_end_clean();

            throw $e;
        }

        return ('' === $tmp = ob_get_clean()) ? '' : new Twig_Markup($tmp, $this->env->getCharset());
    }

    // line 70
    public function getcompositeTreeNode($__node__ = null, $__cycle__ = null, $__prototype__ = null, ...$__varargs__)
    {
        $context = $this->env->mergeGlobals(array(
            "node" => $__node__,
            "cycle" => $__cycle__,
            "prototype" => $__prototype__,
            "varargs" => $__varargs__,
        ));

        $blocks = array();

        ob_start();
        try {
            // line 71
            echo "    ";
            $context["treemacros"] = $this;
            // line 72
            echo "    ";
            $context["formmacros"] = $this->loadTemplate("OlegOrderformBundle::Default/formmacros.html.twig", "OlegUserdirectoryBundle::Tree/treemacros.html.twig", 72);
            // line 73
            echo "    <p>
    ";
            // line 74
            echo $this->env->getRuntime('Symfony\Bridge\Twig\Form\TwigRenderer')->searchAndRenderBlock((isset($context["node"]) ? $context["node"] : null), 'errors');
            echo "
    <div class=\"composite-tree-holder\">
        <div class=\"row treenode\">
            <div class=\"col-xs-6\" align=\"right\">
                ";
            // line 78
            echo $this->env->getRuntime('Symfony\Bridge\Twig\Form\TwigRenderer')->searchAndRenderBlock((isset($context["node"]) ? $context["node"] : null), 'label');
            echo "
            </div>
            <div class=\"col-xs-6\" align=\"left\">
                ";
            // line 81
            echo $this->env->getRuntime('Symfony\Bridge\Twig\Form\TwigRenderer')->searchAndRenderBlock((isset($context["node"]) ? $context["node"] : null), 'widget');
            echo "
            </div>
        </div>
    </div>
    </p>
";
        } catch (Exception $e) {
            ob_end_clean();

            throw $e;
        } catch (Throwable $e) {
            ob_end_clean();

            throw $e;
        }

        return ('' === $tmp = ob_get_clean()) ? '' : new Twig_Markup($tmp, $this->env->getCharset());
    }

    // line 88
    public function getcompositeTreeNode_notempty($__node__ = null, $__cycle__ = null, $__prototype__ = null, ...$__varargs__)
    {
        $context = $this->env->mergeGlobals(array(
            "node" => $__node__,
            "cycle" => $__cycle__,
            "prototype" => $__prototype__,
            "varargs" => $__varargs__,
        ));

        $blocks = array();

        ob_start();
        try {
            // line 89
            echo "    ";
            $context["treemacros"] = $this;
            // line 90
            echo "
    ";
            // line 91
            if (twig_in_filter("show", (isset($context["cycle"]) ? $context["cycle"] : null))) {
                // line 92
                echo "        ";
                $context["showFlag"] = true;
                // line 93
                echo "    ";
            } else {
                // line 94
                echo "        ";
                $context["showFlag"] = false;
                // line 95
                echo "    ";
            }
            // line 96
            echo "
    ";
            // line 97
            if (($this->getAttribute($this->getAttribute((isset($context["node"]) ? $context["node"] : null), "vars", array()), "value", array()) ||  !(isset($context["showFlag"]) ? $context["showFlag"] : null))) {
                // line 98
                echo "        ";
                echo $context["treemacros"]->getcompositeTreeNode((isset($context["node"]) ? $context["node"] : null), (isset($context["cycle"]) ? $context["cycle"] : null), (isset($context["prototype"]) ? $context["prototype"] : null));
                echo "
    ";
            } else {
                // line 100
                echo "        ";
                $this->getAttribute((isset($context["node"]) ? $context["node"] : null), "setRendered", array());
                // line 101
                echo "    ";
            }
        } catch (Exception $e) {
            ob_end_clean();

            throw $e;
        } catch (Throwable $e) {
            ob_end_clean();

            throw $e;
        }

        return ('' === $tmp = ob_get_clean()) ? '' : new Twig_Markup($tmp, $this->env->getCharset());
    }

    public function getTemplateName()
    {
        return "OlegUserdirectoryBundle::Tree/treemacros.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  247 => 101,  244 => 100,  238 => 98,  236 => 97,  233 => 96,  230 => 95,  227 => 94,  224 => 93,  221 => 92,  219 => 91,  216 => 90,  213 => 89,  199 => 88,  178 => 81,  172 => 78,  165 => 74,  162 => 73,  159 => 72,  156 => 71,  142 => 70,  117 => 58,  111 => 57,  108 => 56,  102 => 52,  100 => 51,  90 => 43,  82 => 38,  70 => 29,  62 => 24,  59 => 23,  57 => 22,  54 => 21,  52 => 20,  34 => 19,  29 => 87,  25 => 68,  22 => 18,  19 => 16,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "OlegUserdirectoryBundle::Tree/treemacros.html.twig", "C:\\Users\\ch3\\Documents\\MyDocs\\WCMC\\ORDER\\scanorder\\Scanorders2\\src\\Oleg\\UserdirectoryBundle/Resources/views/Tree/treemacros.html.twig");
    }
}
