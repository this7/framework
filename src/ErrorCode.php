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

/**
 * 静态错误类
 */
class ErrorCode {
    public static $OK                 = 0;
    public static $FrameSystemError   = -70001; // 框架系统错误
    public static $FrameVariableError = -70002; // 框架变量错误
    public static $InstantiationError = -70003; // 实例化错误
    public static $FileDoesNotExist   = -70004; // 文件不存在
    public static $ClassDoesNotExist  = -70005; // 类不存在
    public static $EncryptAESError    = -70006;
    public static $DecryptAESError    = -70007;
    public static $IllegalBuffer      = -70008;
    public static $EncodeBase64Error  = -70009;
    public static $DecodeBase64Error  = -70010;
    public static $GenReturnXmlError  = -70011;
}