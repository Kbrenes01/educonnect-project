<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Extension\CoreExtension;
use Twig\Extension\SandboxExtension;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;

/* roleActions.twig.html */
class __TwigTemplate_0fbccf820f8abaa0d33f2b7bace5e0fd extends Template
{
    private $source;
    private $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = [
        ];
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 10
        yield "
<h2>
    ";
        // line 12
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Actions"), "html", null, true);
        yield ": ";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()(CoreExtension::getAttribute($this->env, $this->source, ($context["role"] ?? null), "name", [], "any", false, false, false, 12)), "html", null, true);
        yield "
</h2>

";
        // line 15
        if ((($context["actionCount"] ?? null) < 1)) {
            // line 16
            yield "<div class=\"warning\">
    ";
            // line 17
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("There are no records to display."), "html", null, true);
            yield "
</div>
";
        } else {
            // line 20
            yield "<div class=\"column-2 mb-8\">
";
            // line 21
            $context['_parent'] = $context;
            $context['_seq'] = CoreExtension::ensureTraversable(($context["actions"] ?? null));
            foreach ($context['_seq'] as $context["moduleName"] => $context["moduleActions"]) {
                // line 22
                yield "    <div class=\"column-no-break\">
        <h5 class=\"border-0 mb-1\">
            ";
                // line 24
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()($context["moduleName"]), "html", null, true);
                yield "
        </h5>

        <ul>
        ";
                // line 28
                $context['_parent'] = $context;
                $context['_seq'] = CoreExtension::ensureTraversable($context["moduleActions"]);
                foreach ($context['_seq'] as $context["_key"] => $context["action"]) {
                    // line 29
                    yield "            <li>
                <span title=\"";
                    // line 30
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()(CoreExtension::getAttribute($this->env, $this->source, $context["action"], "description", [], "any", false, false, false, 30)), "html", null, true);
                    yield "\">";
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()(CoreExtension::getAttribute($this->env, $this->source, $context["action"], "name", [], "any", false, false, false, 30)), "html", null, true);
                    yield "</span>
            </li>
        ";
                }
                $_parent = $context['_parent'];
                unset($context['_seq'], $context['_iterated'], $context['_key'], $context['action'], $context['_parent'], $context['loop']);
                $context = array_intersect_key($context, $_parent) + $_parent;
                // line 33
                yield "        </ul>
    </div>
";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['moduleName'], $context['moduleActions'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 36
            yield "</div>
";
        }
        return; yield '';
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName()
    {
        return "roleActions.twig.html";
    }

    /**
     * @codeCoverageIgnore
     */
    public function isTraitable()
    {
        return false;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getDebugInfo()
    {
        return array (  105 => 36,  97 => 33,  86 => 30,  83 => 29,  79 => 28,  72 => 24,  68 => 22,  64 => 21,  61 => 20,  55 => 17,  52 => 16,  50 => 15,  42 => 12,  38 => 10,);
    }

    public function getSourceContext()
    {
        return new Source("", "roleActions.twig.html", "C:\\xampp\\htdocs\\educonnect\\modules\\User Admin\\templates\\roleActions.twig.html");
    }
}
