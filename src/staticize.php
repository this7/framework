<?php
/**
 * this7 PHP Framework
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright 2016-2018 Yan TianZeng<qinuoyun@qq.com>
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      http://www.ub-7.com
 */
namespace this7\framework;

abstract class staticize {

    protected static $name;

    protected static $app;

    protected static $class;

    public static $resolvedInstance = [];

    public static function getAppRoot() {
        return self::resolveAppInstance(static::getAppAccessor());
    }

    public static function getAppAccessor() {
        throw new \RuntimeException("未找到外观getAppAccessor方法");
    }

    protected static function resolveAppInstance($name) {
        if (is_object($name)) {
            return $name;
        }

        if (isset(static::$resolvedInstance[$name])) {
            return static::$resolvedInstance[$name];
        }

        return static::$resolvedInstance[$name] = static::$app[$name];
    }

    public static function setApplication($app) {
        static::$app = $app;
    }

    public static function __callStatic($method, $args) {
        $instance = static::getAppRoot();
        switch (count($args)) {
        case 0:
            return $instance->$method();

        case 1:
            return $instance->$method($args[0]);

        case 2:
            return $instance->$method($args[0], $args[1]);

        case 3:
            return $instance->$method($args[0], $args[1], $args[2]);

        case 4:
            return $instance->$method($args[0], $args[1], $args[2], $args[3]);

        default:
            return call_user_func_array([$instance, $method], $args);

        }
    }
}