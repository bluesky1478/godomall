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
 * FacebookUrlDetectionHandler 클래스의 Wrapping 클래스
 *
 * 고도몰5에서는 $_SERVER 변수를 unset 하기때문에 만든 클래스
 *
 * @package Component\Facebook
 * @author  yjwee
 */
class FacebookUrlDetectionHandler extends \Facebook\Url\FacebookUrlDetectionHandler
{
    /**
     * @inheritDoc
     */
    protected function getServerVar($key)
    {
        return \Request::server()->get($key, '');
    }

}
