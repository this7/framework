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

class driver {

    /**
     * 应用程序实例
     * @var [type]
     */
    protected $app;

    public function __construct($app) {
        $this->app = $app;
    }

    /**
     * 注册驱动
     * @return [type] [description]
     */
    public function register() {
        $app = $this->app;
        $this->app->single($app->class, function ($app, $class) {
            $class = $app->namespace . '\\' . $class . '\\' . $class;
            return new $class($app);
        }, TRUE);
    }
}