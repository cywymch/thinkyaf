<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInite96da58894c08cebdef2ba7623799c2f
{
    public static $files = array (
        '33197a0023ced5fbf8f861d1c4ca048d' => __DIR__ . '/..' . '/topthink/think-orm/src/config.php',
    );

    public static $prefixLengthsPsr4 = array (
        't' => 
        array (
            'think\\' => 6,
        ),
        'p' => 
        array (
            'phpspider\\' => 10,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'think\\' => 
        array (
            0 => __DIR__ . '/..' . '/topthink/think-orm/src',
        ),
        'phpspider\\' => 
        array (
            0 => __DIR__ . '/..' . '/owner888/phpspider',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInite96da58894c08cebdef2ba7623799c2f::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInite96da58894c08cebdef2ba7623799c2f::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
