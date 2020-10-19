<?php
/**
 * This is commercial software, only users who have purchased a valid license
 * and accept to the terms of the License Agreement can install and use this
 * program.
 *
 * Do not edit or add to this file if you wish to upgrade Godomall5 to newer
 * versions in the future.
 *
 * @copyright ⓒ 2016, NHN godo: Corp.
 * @link      http://www.godo.co.kr
 */

namespace Bundle\Component\Member\Exception;


use Framework\Debug\Exception\AlertCloseException;
use Framework\Debug\Exception\AlertRedirectException;

/**
 * Class LoginLimitException
 * @package Bundle\Component\Member\Exception
 * @author  yjwee <yeongjong.wee@godo.co.kr>
 */
class LoginLimitException extends \Exception
{
    public function throwException()
    {
        $request = \App::getInstance('request');
        if ($request->isMobile()) {
            throw new AlertRedirectException($this->getMessage(), $this->getCode(), $this, '../../member/login.php', 'parent');
        } else {
            throw new AlertCloseException($this->getMessage(), $this->getCode(), $this);
        }
    }
}
