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

use ArrayAccess;
use Closure;
use Exception;
use ReflectionClass;

class interior implements ArrayAccess {
    /**
     * 绑定实例
     * @var array
     */
    public $bindings = array();

    /**
     * 单例驱动
     * @var array
     */
    public $singles = array();

    /**
     * 服务绑定到容器
     * @param  string  $name    工厂驱动名
     * @param  string  $closure 返回工厂驱动的闭包函数
     * @param  boolean $force   是否单例
     * @return null
     */
    public function bind($name, $closure, $force = FALSE) {
        $this->bindings[$name] = compact('closure', 'force');
    }

    /**
     * 注册单例工厂驱动
     * @param $name 服务
     * @param $closure 闭包函数
     */
    public function single($name, $closure) {
        $this->bind($name, $closure, TRUE);
    }

    /**
     * 单例服务
     * @param $name 名称
     * @param $object 对象
     */
    public function instance($name, $object) {
        $this->singles[$name] = $object;
    }

    /**
     * 获取服务实例
     * @param   string   $name  服务名
     * @param   bool     $force 是否单例
     * @return  Object
     * @throws  Exception
     */
    public function make($name, $force = FALSE) {
        if (isset($this->singles[$name])) {
            return $this->singles[$name];
        }
        #获得实现提供者
        $closure = $this->getClosure($name);
        #获取实例
        $object = $this->build($closure, $name);
        #单例绑定
        if (isset($this->bindings[$name]['force']) && $this->bindings[$name]['force'] || $force) {
            $this->singles[$name] = $object;
        }
        return $object;
    }

    /**
     * 获得实例实现
     * @param  string $name 创建实例方式:类名或闭包函数
     * @return mixed
     */
    private function getClosure($name) {
        return isset($this->bindings[$name]) ? $this->bindings[$name]['closure'] : $name;
    }

    /**
     * 生成服务实例
     *
     * @param $className 生成方式 类或闭包函数
     *
     * @return object
     * @throws Exception
     */
    protected function build($className, $key = "") {
        try {
            #匿名函数
            if ($className instanceof Closure) {
                #执行闭包函数-即外部驱动文件
                return $className($this, $key);
            }
            #获取类信息
            $reflector = new ReflectionClass($className);
            #检查类是否可实例化, 排除抽象类abstract和对象接口interface
            if (!$reflector->isInstantiable()) {
                throw new Exception("$className 不能实例化.", ErrorCode::$InstantiationError);
            }
            #获取类的构造函数
            $constructor = $reflector->getConstructor();
            #若无构造函数，直接实例化并返回
            if (is_null($constructor)) {
                return new $className;
            }
            #取构造函数参数,通过 ReflectionParameter 数组返回参数列表
            $parameters = $constructor->getParameters();
            #递归解析构造函数的参数
            $dependencies = $this->getDependencies($parameters);

            #创建一个类的新实例，给出的参数将传递到类的构造函数。
            return $reflector->newInstanceArgs($dependencies);
        } catch (Exception $e) {
            ERRORCODE($e);
        }

    }

    /**
     * 获取依赖构造函数的参数
     * @param  string $parameters 参数列表
     * @return array              根据不同类型返回
     */
    protected function getDependencies($parameters) {
        $dependencies = [];

        #参数列表
        foreach ($parameters as $parameter) {
            #获取参数类型
            $dependency = $parameter->getClass();

            if (is_null($dependency)) {
                #是变量,有默认值则设置默认值
                $dependencies[] = $this->resolveNonClass($parameter);
            } else {
                #是一个类，递归解析
                $dependencies[] = $this->build($dependency->name);
            }
        }

        return $dependencies;
    }

    /**
     * 提取参数默认值
     * @param  Object $parameter 参数
     * @return Object
     */
    protected function resolveNonClass($parameter) {
        try {
            #有默认值则返回默认值
            if ($parameter->isDefaultValueAvailable()) {
                return $parameter->getDefaultValue();
            }
            throw new Exception('参数无默认值，无法实例化', ErrorCode::$InstantiationError);
        } catch (Exception $e) {
            ERRORCODE($e);
        }
    }

    /**
     * 检查一个偏移位置是否存在
     * @param  string $key 键名
     * @return Boolean
     */
    public function offsetExists($key) {

        return isset($this->bindings[$key]);
    }

    /**
     * 获取一个偏移位置的值
     * @param  string $key 键名
     * @return all
     */
    public function offsetGet($key) {
        return $this->make($key);
    }

    /**
     * 为指定索引设定新的值
     * @param  string $key       键名
     * @param  string $value     键值
     */
    public function offsetSet($key, $value) {
        if (!$value instanceof Closure) {
            $value = function () use ($value) {
                return $value;
            };
        }
        $this->bind($key, $value);
    }

    /**
     * 复位一个偏移位置的值
     * @param  string $key       键名
     */
    public function offsetUnset($key) {
        unset($this->bindings[$key], $this->singles[$key]);
    }

    /**
     * 获取键值
     * @param  string $key       键名
     * @return all
     */
    public function __get($key) {
        return $this[$key];
    }

    /**
     * 设置键值
     * @param  string $key       键名
     * @param  string $value     键值
     */
    public function __set($key, $value) {
        $this[$key] = $value;
    }
}