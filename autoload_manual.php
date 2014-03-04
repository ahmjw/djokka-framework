<?php

/**
 * Call closure autoloader to load Djokka Framework library.
 * This code is modified from the URL {@link http://www.php-fig.org/psr/psr-0/}.
 */
spl_autoload_register(function($className) {
	$className = ltrim($className, '\\');
    $fileName  = __DIR__ . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR;
    $namespace = '';
    if ($lastNsPos = strrpos($className, '\\')) {
        $namespace = substr($className, 0, $lastNsPos);
        $className = substr($className, $lastNsPos + 1);
        $fileName  .= str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
    }
    $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

    require $fileName;
});