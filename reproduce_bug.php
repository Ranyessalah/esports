<?php

require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

echo "Creating ParameterBag...\n";
$bag = new ParameterBag(['foo' => 'bar']);
echo "ParameterBag created.\n";

echo "Defining closure accessing parent::get()...\n";
$closure = function ($name) {
    try {
        return parent::get($name);
    }
    catch (\Throwable $e) {
        echo "Caught: " . $e->getMessage() . "\n";
        return null;
    }
};

echo "Binding closure to ParameterBag instance...\n";
$boundClosure = $closure->bindTo($bag, $bag::class);

echo "Calling closure...\n";
try {
    $boundClosure('foo');
}
catch (\Error $e) {
    echo "Fatal Error Caught: " . $e->getMessage() . "\n";
}

echo "Defining closure accessing \$this->parameters[]...\n";
$closureProtected = function ($name) {
    return $this->parameters[$name] ?? 'default';
};
$boundClosureProtected = $closureProtected->bindTo($bag, $bag::class);
echo "Calling protected closure: " . $boundClosureProtected('foo') . "\n";
