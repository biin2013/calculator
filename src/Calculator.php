<?php

namespace Biin2013\Calculator;

use Exception;

class Calculator
{
    private array $numberStack = [];
    private array $operatorStack = [];
    private string $formula = '';
    private array $replaces;
    private array $priority = [
        '(' => 0,
        '+' => 1,
        '-' => 1,
        '*' => 2,
        '/' => 2,
        '^' => 3
    ];
    private string $replaceChar = 'a';

    /**
     * Formula constructor.
     * @param string $formula
     * @param array $replaces
     */
    public function __construct(string $formula = '', array $replaces = [])
    {
        $this->setFormula($formula);
        $this->setReplaces($replaces);
    }

    /**
     * @param string $formula
     * @return Calculator
     */
    public function setFormula(string $formula): Calculator
    {
        if (empty($formula)) {
            $this->formula = '';
            return $this;
        }
        $this->formula = $this->removeFormulaRedundantParenthesis(
            $this->fillToFullFormula(
                $this->resolveStartWithMinus($formula)
            )
        );

        return $this;
    }

    /**
     * @param array $replaces
     * @return Calculator
     */
    public function setReplaces(array $replaces): Calculator
    {
        $this->replaces = array_change_key_case($replaces);

        return $this;
    }

    /**
     * @param string $formula
     * @param array $replaces
     * @return Calculator
     */
    public static function make(string $formula = '', array $replaces = []): Calculator
    {
        return new self($formula, $replaces);
    }

    /**
     * new instance
     * @param string|null $formula
     * @return Calculator
     */
    private function newInstance(string $formula): Calculator
    {
        return new self($formula, $this->replaces);
    }

    /**
     * resolve start with minus
     * @param string $str
     * @return string
     */
    private function resolveStartWithMinus(string $str): string
    {
        return substr($str, 0, 1) == '-' ? '0' . $str : $str;
    }

    /**
     * fill to full formula
     * @param string $str
     * @return string
     */
    private function fillToFullFormula(string $str): string
    {
        $pattern = [
            '/(\d+)([a-zA-Z]+)/',
            '/([a-zA-Z]+)(\d+)/'
        ];
        if (strpos($str, '(') !== false) {
            array_push($pattern, '/([\d|a-zA-Z]+)(\()/', '/(\))([\d|a-zA-Z]+)/');
        }

        return preg_replace($pattern, '$1*$2', $str);
    }

    /**
     * @param string $formula
     * @return string
     */
    private function removeFormulaRedundantParenthesis(string $formula): string
    {
        return preg_replace_callback('/(\([\d|a-zA-Z]+\))/', function ($matches) {
            return substr($matches[0], 1, -1);
        }, $formula);
    }

    /**
     * calculate formula
     * @param string|null $formula
     * @param array|null $replaces
     * @return float
     * @throws Exception
     */
    public function calculate(string $formula = null, array $replaces = null): float
    {
        if (!is_null($formula)) {
            $this->setFormula($formula);
        }
        if (!is_null($replaces)) {
            $this->setReplaces($replaces);
        }

        // calculate parenthesis and exponent
        $this->calculateParenthesisAndExponent();

        $length = strlen($this->formula);
        $start = 0;
        $index = 1;
        while ($index < $length) {
            if (array_key_exists($this->formula[$index], $this->priority)) {
                $number = substr($this->formula, $start, $index - $start);
                $operator = $this->formula[$index];
                $this->calculateFormula($number, $operator);
                $start = $index + 1;
            }
            $index++;
        }
        // push last number
        array_push(
            $this->numberStack,
            substr($this->formula, $start, $index - $start)
        );
        $this->calculateLastResult();
        $this->orderCalculateResult();

        return array_pop($this->numberStack);
    }

    /**
     * calculate parenthesis and exponent
     * @throws Exception
     */
    private function calculateParenthesisAndExponent()
    {
        if (strpos($this->formula, '(') !== false) {
            preg_match_all('/\([^()]+\)/', $this->formula, $subs);
            $replaces = [];
            foreach ($subs[0] as $sub) {
                $result = $this->newInstance(substr($sub, 1, -1))
                    ->calculate();
                if ($result < 0) {
                    $replaceChar = $this->generateReplaceChar();
                    $this->mergeReplaces($replaceChar, $result);
                    $replaces[] = $replaceChar;
                } else {
                    $replaces[] = $result;
                }
            }
            $this->formula = str_replace($subs[0], $replaces, $this->formula);
        }

        if (strpos($this->formula, '(') !== false) {
            $this->calculateParenthesisAndExponent();
        }

        if (strpos($this->formula, '^') !== false) {
            $this->calculateExponent();
        }
    }

    /**
     * @throws Exception
     */
    private function calculateExponent()
    {
        preg_match_all('/([0-9.|a-zA-Z]+)\^(\d)/', $this->formula, $matches);
        $replaces = [];
        foreach ($matches[1] as $i => $exp) {
            $result = $this->calculateResult($exp, '^', $matches[2][$i]);
            if ($result < 0) {
                $replaceChar = $this->generateReplaceChar();
                $this->mergeReplaces($replaceChar, $result);
                $replaces[$i] = $replaceChar;
            } else {
                $replaces[$i] = $result;
            }
        }
        $this->formula = str_replace($matches[0], $replaces, $this->formula);
    }

    /**
     * calculate formula
     * @param mixed $number
     * @param string $operator
     * @throws Exception
     */
    private function calculateFormula($number, string $operator)
    {
        array_push($this->numberStack, $number);
        if (!empty($this->operatorStack)) {
            $this->calculatePreviousResult($operator);
        }

        array_push($this->operatorStack, $operator);
    }

    /**
     * get operator priority
     * @param string $operator
     * @return int|mixed
     * @throws Exception
     */
    private function getOperatorPriority(string $operator)
    {
        if (array_key_exists($operator, $this->priority)) {
            return $this->priority[$operator];
        }

        throw new Exception('operator illegal');
    }

    /**
     * resolve operator priority
     * @param string $operator
     * @return int
     * @throws Exception
     */
    private function resolveOperatorPriority(string $operator): int
    {
        $operatorPriority = $this->getOperatorPriority($operator);
        $lastOperator = end($this->operatorStack);
        $lastOperatorPriority = $this->getOperatorPriority($lastOperator);

        return $operatorPriority > $lastOperatorPriority
            ? 1
            : ($operatorPriority < $lastOperatorPriority ? -1 : 0);
    }

    /**
     * calculate previous result
     * @param $operator
     * @throws Exception
     */
    private function calculatePreviousResult($operator)
    {
        if (empty($this->operatorStack)
            || $this->resolveOperatorPriority($operator) == 1
        ) {
            return;
        }

        $number2 = array_pop($this->numberStack);
        $number1 = array_pop($this->numberStack);
        $operator1 = array_pop($this->operatorStack);

        array_push(
            $this->numberStack,
            $this->calculateResult($number1, $operator1, $number2)
        );

        $this->calculatePreviousResult($operator);
    }

    /**
     * calculate result
     * @param mixed $number1
     * @param string $operator
     * @param mixed $number2
     * @return float
     * @throws Exception
     */
    private function calculateResult($number1, string $operator, $number2)
    {
        if (!is_numeric($number1)) {
            if (!array_key_exists($number1, $this->replaces)) {
                throw new Exception("replace[$number1] not found");
            }
            $number1 = $this->replaces[$number1];
        }
        if (!is_numeric($number2)) {
            if (!array_key_exists($number2, $this->replaces)) {
                throw new Exception("replace[$number2] not found");
            }
            $number2 = $this->replaces[$number2];
        }
        switch ($operator) {
            case '+':
                return $number1 + $number2;
            case '-':
                return $number1 - $number2;
            case '*':
                return $number1 * $number2;
            case '/':
                return $number1 / $number2;
            case '^':
                return pow($number1, $number2);
            default:
                throw new Exception("operator[{$operator}] not support");
        }
    }

    /**
     * order calculate result
     * @throws Exception
     */
    private function orderCalculateResult()
    {
        if (empty($this->operatorStack)) {
            return;
        }
        $number1 = array_shift($this->numberStack);
        $number2 = array_shift($this->numberStack);
        $operator = array_shift($this->operatorStack);

        array_unshift(
            $this->numberStack,
            $this->calculateResult($number1, $operator, $number2)
        );

        $this->orderCalculateResult();
    }

    /**
     * calculate last result
     * @throws Exception
     */
    private function calculateLastResult()
    {
        if (empty($this->operatorStack) || count($this->numberStack) < 2) {
            return;
        }
        if ($this->getOperatorPriority(end($this->operatorStack)) > 1) {
            $number2 = array_pop($this->numberStack);
            $number1 = array_pop($this->numberStack);
            $operator = array_pop($this->operatorStack);

            array_push(
                $this->numberStack,
                $this->calculateResult($number1, $operator, $number2)
            );
        }
    }

    /**
     * generate replace char
     * @return string
     */
    private function generateReplaceChar(): string
    {
        // cr: custom replace
        return 'cr' . $this->replaceChar++;
    }

    /**
     * merge to replaces
     * @param string $replaceChar
     * @param float $result
     * @throws Exception
     */
    private function mergeReplaces(string $replaceChar, float $result)
    {
        if (array_key_exists($replaceChar, $this->replaces)) {
            throw new Exception("replace char[$replaceChar] exist");
        }

        $this->replaces[$replaceChar] = $result;
    }

    /**
     * @return array
     */
    public function getAvailableOperators(): array
    {
        return array_keys($this->priority);
    }
}