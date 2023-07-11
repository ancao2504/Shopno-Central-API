<?php

// autoload_real.php @generated by Composer

class ComposerAutoloaderInit29cad6c2bfb925f40d93bf7ac282ea89
{
    private static $loader;

    public static function loadClassLoader($class)
    {
        if ('Composer\Autoload\ClassLoader' === $class) {
            require __DIR__ . '/ClassLoader.php';
        }
    }

    /**
     * @return \Composer\Autoload\ClassLoader
     */
    public static function getLoader()
    {
        if (null !== self::$loader) {
            return self::$loader;
        }

        require __DIR__ . '/platform_check.php';

        spl_autoload_register(array('ComposerAutoloaderInit29cad6c2bfb925f40d93bf7ac282ea89', 'loadClassLoader'), true, true);
        self::$loader = $loader = new \Composer\Autoload\ClassLoader(\dirname(__DIR__));
        spl_autoload_unregister(array('ComposerAutoloaderInit29cad6c2bfb925f40d93bf7ac282ea89', 'loadClassLoader'));

        require __DIR__ . '/autoload_static.php';
        call_user_func(\Composer\Autoload\ComposerStaticInit29cad6c2bfb925f40d93bf7ac282ea89::getInitializer($loader));

        $loader->register(true);

        return $loader;
    }
}
