<?php

use Biin2013\Calculator\Calculator;
use PHPUnit\Framework\TestCase;

class CalculatorTest extends TestCase
{
    /**
     * @param string $formula
     * @param array $replaces
     * @return Calculator
     */
    private function getCalculator(string $formula = '', array $replaces = []): Calculator
    {
        return Calculator::make($formula, $replaces);
    }

    public function test0()
    {
        $this->assertEquals(200, $this->getCalculator(50 * (4))->calculate());
    }

    public function test01()
    {
        $this->assertEquals(200, $this->getCalculator('a(b)', ['a' => 50, 'b' => 4])->calculate());
    }

    public function test02()
    {
        $this->assertEquals(200, $this->getCalculator('a(bcd)', ['a' => 50, 'bcd' => 4])->calculate());
    }

    public function test03()
    {
        $this->assertEquals(200, $this->getCalculator('a(4)', ['a' => 50])->calculate());
    }

    public function test04()
    {
        $this->assertEquals(2000, $this->getCalculator('a(40)', ['a' => 50])->calculate());
    }

    public function test05()
    {
        $this->assertEquals(800, $this->getCalculator('a(b4)', ['a' => 50, 'b' => 4])->calculate());
    }

    public function test06()
    {
        $this->assertEquals(-200, $this->getCalculator('a(-4)', ['a' => 50])->calculate());
    }

    public function test07()
    {
        $this->assertEquals(10, $this->getCalculator('a', ['a' => 10])->calculate());
    }

    public function test08()
    {
        $this->assertEquals(10, $this->getCalculator(10)->calculate());
    }

    public function test09()
    {
        $this->assertEquals(0, $this->getCalculator('0')->calculate());
    }

    public function test1()
    {
        $this->assertEquals(59, $this->getCalculator('20*3+4-10/2')->calculate());
    }

    public function test2()
    {
        $this->assertEquals(9, $this->getCalculator('2(3+4)-100/20')->calculate());
    }

    public function test3()
    {
        $this->assertEquals(9, $this->getCalculator('2*(3+4)-100/20')->calculate());
    }

    public function test4()
    {
        $this->assertEquals(28, $this->getCalculator('2(3+4*5-8)-100/(20+30)')->calculate());
    }

    public function test5()
    {
        $this->assertEquals(13, $this->getCalculator('(3+4*5-8)-100/(20+30)')->calculate());
    }

    public function test6()
    {
        $this->assertEquals(103, $this->getCalculator('(3+4*5-8)7-100/(20+30)')->calculate());
    }

    public function test7()
    {
        $this->assertEquals(103, $this->getCalculator('(3+4*5-8)7-a/(20+30)', ['a' => 100])->calculate());
    }

    public function test8()
    {
        $this->assertEquals(103, $this->getCalculator('(3+4*5-8)7-ab/(20+30)', ['ab' => 100])->calculate());
    }

    public function test9()
    {
        $this->assertEquals(5143, $this->getCalculator('(3+4*5-8)7^3-ab/(20+30)', ['ab' => 100])->calculate());
    }

    public function test10()
    {
        $this->assertEquals(5143, $this->getCalculator('(y+4*5-8)7^y-ab/(20+30)', ['ab' => 100, 'y' => 3])->calculate());
    }

    public function test11()
    {
        $this->assertEquals(
            -47591627.5888,
            $this->getCalculator(
                '2(3+4)yx4z-(100+34/2)^3x((390-3)*4)/20',
                ['yx' => 5, 'x' => 0.384, 'z' => 38.8334]
            )->calculate());
    }

    public function test12()
    {
        $this->assertEquals(148922, $this->getCalculator('(3*4^2+5)^3+45')->calculate());
    }

    public function test13()
    {
        $this->assertEquals(148922, $this->getCalculator('(3*4^2+5)^3+45')->calculate());
    }

    public function test14()
    {
        $this->assertEquals(-222182611735.27686,
            $this->getCalculator('0.1728x^6-0.4473y^6+29.5777x*y+(0.0744x+3.7265y+0.0107)*(d^2/4-(d/2-t)^2)*l*3.14159*1.05/1000+8.663', [
                'd' => 3,
                'l' => 4,
                't' => 34.94,
                'x' => 34,
                'y' => 89.01
            ])->calculate());
    }

    public function test15()
    {
        $this->assertEquals(84531674226.9099534407027311291842,
            $this->getCalculator('0.1728x^6-0.4473y^6+29.5777x*y+(0.0744x+3.7265y+0.0107)*(d^2/4-(d/2-t)^2)*l*3.14159*1.05/1000+8.663', [
                'd' => 34,
                'l' => 0.893,
                't' => -34.834,
                'x' => 89,
                'y' => 38
            ])->calculate());
    }

    public function test16()
    {
        $this->assertEquals(84531674226.9099534407027311291842,
            $this->getCalculator()->calculate(
                '0.1728x^6-0.4473y^6+29.5777x*y+(0.0744x+3.7265y+0.0107)*(d^2/4-(d/2-t)^2)*l*3.14159*1.05/1000+8.663',
                [
                    'd' => 34,
                    'l' => 0.893,
                    't' => -34.834,
                    'x' => 89,
                    'y' => 38
                ]
            ));
    }

    public function test17()
    {
        $result = $this->getCalculator()
            ->setFormula('0.1728x^6-0.4473y^6+29.5777x*y+(0.0744x+3.7265y+0.0107)*(d^2/4-(d/2-t)^2)*l*3.14159*1.05/1000+8.663')
            ->setReplaces([
                'd' => 34,
                'l' => 0.893,
                't' => -34.834,
                'x' => 89,
                'y' => 38
            ])
            ->calculate();
        $this->assertEquals(84531674226.9099534407027311291842, $result);
    }

    public function test18()
    {
        $result = $this->getCalculator()
            ->calculate(
                '0.1728x^6-0.4473y^6+29.5777x*y+(0.0744x+3.7265y+0.0107)*(d^2/4-(d/2-t)^2)*l*3.14159*1.05/1000+8.663',
                [
                    'd' => 34,
                    'l' => 0.893,
                    't' => -34.834,
                    'x' => 89,
                    'y' => 38
                ]
            );
        $this->assertEquals(84531674226.9099534407027311291842, $result);
    }

    public function test19()
    {
        $result = $this->getCalculator()
            ->calculate(
                '0.0004(0.1728a^6-0.4473b^6+29.5777a*b+(0.0744a+3.7265b+0.0107)*(d^2/4-(d/2-t)^2)*l*3.14159*1.05/1000+8.6633)^2+0.711(0.1728a^6-0.4473b^6+29.5777a*b+(0.0744a+3.7265b+0.0107)*(d^2/4-(d/2-t)^2)*l*3.14159*1.05/1000+8.6633)+8.9577',
                [
                    'a' => 34,
                    'b' => 893,
                    'd' => 34.834,
                    'l' => 89,
                    't' => 38
                ]
            );
        $this->assertEquals(2.0581418010587017E+31, $result);
    }
}