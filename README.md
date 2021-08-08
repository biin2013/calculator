# calculate-string-formula

calculate string formula

## Install

` composer require biin2013/calculator`

## instance

```php
$calculator = new Calculator('(3+4*5-8)7-100/(20+30)');
// or
$calculator = Calculator::make('(3+4*5-8)7-100/(20+30)');
// or
$calculator = Calculator::make()->setFormula('(3+4*5-8)7-100/(20+30)');

// use replaces;
$calculator = Calculator::make()
    ->setFormula('(y+4*5-8)7^y-ab/(20+30)')
    ->setReplaces(['ab' => 100, 'y' => 3]);
```

## calculator

```php
$calculator->calculate();
// or use parameter
Calculator::make()->calculate(
    '(y+4*5-8)7^y-ab/(20+30)',
    ['ab' => 100, 'y' => 3]
);
```

## example

```php
Calculator::make(50 * (4))->calculate();
Calculator::make('a(4)', ['a' => 50])->calculate();
Calculator::make('a(b4)', ['a' => 50, 'b' => 4])->calculate();
Calculator::make()->calculate('20*3+4-10/2');
Calculator::make('2(3+4*5-8)-100/(20+30)')->calculate();
Calculator::make('(y+4*5-8)7^y-ab/(20+30)', ['ab' => 100, 'y' => 3])->calculate();
Calculator::make()->calculate(
    '2(3+4)yx4z-(100+34/2)^3x((390-3)*4)/20',
    ['yx' => 5, 'x' => 0.384, 'z' => 38.8334]
);
Calculator::make(
    '0.1728x^6-0.4473y^6+29.5777x*y+(0.0744x+3.7265y+0.0107)*(d^2/4-(d/2-t)^2)*l*3.14159*1.05/1000+8.663'
)->calculate(null, [
    'd' => 3,
    'l' => 4,
    't' => 34.94,
    'x' => 34,
    'y' => 89.01
]);
Calculator::make()->calculate();
Calculator::make('', [
    'd' => 34,
    'l' => 0.893,
    't' => -34.834,
    'x' => 89,
    'y' => 38
])->calculate(
    '0.1728x^6-0.4473y^6+29.5777x*y+(0.0744x+3.7265y+0.0107)*(d^2/4-(d/2-t)^2)*l*3.14159*1.05/1000+8.663'
);
```

## See Test for more examples