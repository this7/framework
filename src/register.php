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
use \this7\config\config;

class register extends interior {
    /**
     * 系统启动状态
     *
     * @var bool
     */
    protected $booted = false;

    /**
     * 获取驱动列表
     * @var array
     */
    public $drives = array();

    /**
     * 记录器
     * @var array
     */
    public $logger = array();

    /**
     * 延时加载
     */
    public $delays = array();

    /**
     * 命名空间
     */

    public $namespace = 'this7';

    /**
     * 类名称
     * @var string
     */
    public $class = '';

    /**
     * 绑定驱动.
     */
    public function bindDrives() {
        foreach ($this->drives as $key => $build) {
            if (config::dispose($build, $key) && C($key, 'defer')) {
                #延迟加载服务
                $this->delays[$key] = $key;
            } else {
                $this->class = $key;
                $this->register(new driver($this), $key);
            }
        }
    }

    /**
     * 创建驱动.
     *
     * @param string $name  驱动
     * @param bool   $force 是否单例
     *
     * @return object
     */
    public function make($name, $force = false) {
        if (isset($this->delays[$name])) {
            $this->register(new $this->delays[$name]($this), $name);
            unset($this->delays[$name]);
        }
        return parent::make($name, $force);
    }

    /**
     * 驱动注册.
     *
     * @param $driver 驱动名
     *
     * @param $key 键名
     *
     * @return object
     */
    public function register($driver, $key) {
        #获取驱动已经注册过时直接返回
        if (isset($this->logger[$key])) {
            return $this->logger[$key];
        }
        #如果是字符串则重新注册
        if (is_string($driver)) {
            $driver = new $driver($this);
        }
        $driver->register($this);
        #记录驱动
        $this->logger[$key] = $driver;
    }

    /**
     * 获取已经注册驱动.
     *
     * @param $driver 驱动吗
     *
     * @return mixed
     */
    protected function getFactory($driver) {
        $class = is_object($driver) ? get_class($driver) : $driver;
        foreach ($this->logger as $value) {
            if ($value instanceof $class) {
                return $value;
            }
        }
    }
}