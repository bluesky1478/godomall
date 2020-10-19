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
namespace Bundle\Component\Facebook;

/**
 * FacebookSessionPersistentDataHandler 클래스의 Wrapping 클래스
 *
 * 고도몰5에서는 $_SESSION 변수를 unset 하기때문에 만든 클래스
 *
 * @package Component\Facebook
 * @author  yjwee
 */
class FacebookSessionPersistentDataHandler extends \Facebook\PersistentData\FacebookSessionPersistentDataHandler
{
    /**
     * @inheritDoc
     */
    public function get($key)
    {
        return \Session::get($this->sessionPrefix . $key, null);
    }

    /**
     * @inheritDoc
     */
    public function set($key, $value)
    {
        \Session::set($this->sessionPrefix . $key, $value);
    }

}
