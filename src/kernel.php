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
use \this7\config\config as This7Config;

/**
 * 框架基础配置文件
 */
class kernel extends register {

    /**
     * 框架启动器
     * @return [type] [description]
     */
    public function start() {
        #设置配置文件
        This7Config::defineConst();
        #获取驱动列表
        $this->drives = $this->getDeviceList(VENDOR_DIR . DS . 'this7');
        #自动加载类
        spl_autoload_register(array($this, 'autoload'));
        #设置静态化
        staticize::setApplication($this);
        #绑定核心驱动
        $this->bindDrives();
        #启动路由
        routes::start();
    }

    public function autoload($class) {
        #通过外观类加载系统服务
        $file  = str_replace('\\', '/', $class);
        $build = basename($file);
        if (isset($this->drives[$build])) {
            $name = $this->setConnector('server/connector', $build);
            $name = '\\server\\connector\\' . $name;
            return class_alias($name, $class);
        }
    }

    /**
     * 创建链接文件
     * @param string $value [description]
     */
    public function setConnector($dir = '', $class) {
        $name      = $class . md5($class);
        $path      = ROOT_DIR . DS . $dir . DS . $name . '.php';
        $connector = <<<CON
<?php
namespace server\\connector;
use \\this7\\framework\\staticize;

class $name extends staticize{
    public static function getAppAccessor() {
        return '$class';
    }
}
CON;
        if (!is_file($path)) {
            to_mkdir($path, $connector, true, true);
        }
        return $name;
    }

    /**
     * 获取驱动列表.
     *
     * @param string $dir 驱动目录
     *
     * @return array 驱动列表
     */
    public function getDeviceList($dir) {
        $path = array('.', '..', '.htaccess', '.DS_Store', 'controllers', 'config', 'framework');
        $ext  = array("php", "html", "htm");
        $list = array();
        if (is_dir($dir)) {
            if ($handle = opendir($dir)) {
                while (($file = readdir($handle)) !== false) {
                    if (!in_array($file, $path) && !in_array(pathinfo($file, PATHINFO_EXTENSION), $ext)) {
                        $list[$file] = VENDOR_DIR . DS . FRAMEWORK . DS . $file;
                    }
                }
                closedir($handle);
            }
        }
        return $list;
    }

}