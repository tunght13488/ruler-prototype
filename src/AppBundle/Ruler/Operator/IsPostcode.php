<?php

namespace AppBundle\Ruler\Operator;

use Ruler\Context;
use Ruler\Operator\VariableOperator;
use Ruler\Proposition;

/**
 * Class IsPostcode.
 */
class IsPostcode extends VariableOperator implements Proposition
{
    /**
     * Evaluate the Proposition with the given Context.
     *
     * @param Context $context Context with which to evaluate this Proposition
     *
     * @return boolean
     */
    public function evaluate(Context $context)
    {
        /** @var \Ruler\RuleBuilder\Variable $left */
        list($left) = $this->getOperands();
        $value = $left->prepareValue($context)->getValue();

        if ($value === null) {
            return false;
        }

        return preg_match('/^\d{4}$/', trim($value)) === 1;
    }

    /**
     * @return string
     */
    protected function getOperandCardinality()
    {
        return static::UNARY;
    }
}
