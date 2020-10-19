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
 * FacebookRedirectLoginHelper 클래스의 Wrapping 클래스
 *
 * 고도몰5에서는 $_GET 변수를 unset 하기때문에 만든 클래스
 *
 * @package Component\Facebook
 * @author  yjwee
 */
class FacebookRedirectLoginHelper extends \Facebook\Helpers\FacebookRedirectLoginHelper
{
    /**
     * @inheritDoc
     */
    protected function getCode()
    {
        return $this->getInput('code');
    }

    /**
     * @inheritDoc
     */
    protected function getState()
    {
        return $this->getInput('state');
    }

    /**
     * @inheritDoc
     */
    public function getErrorCode()
    {
        return $this->getInput('error_code');
    }

    /**
     * @inheritDoc
     */
    public function getError()
    {
        return $this->getInput('error');
    }

    /**
     * @inheritDoc
     */
    public function getErrorReason()
    {
        return $this->getInput('error_reason');
    }

    /**
     * @inheritDoc
     */
    public function getErrorDescription()
    {
        return $this->getInput('error_description');
    }

    /**
     * Returns a value from a GET param.
     *
     * @param string $key
     *
     * @return string|null
     */
    private function getInput($key)
    {
        return \Request::get()->get($key, null);
    }

    /**
     * 페이스북 로그인 url 반환
     *
     * @param        $redirectUrl
     * @param array  $scope
     * @param string $separator
     *
     * @return string
     */
    public function getLoginUrlPopup($redirectUrl, array $scope = [], $separator = '&')
    {
        $state = $this->pseudoRandomStringGenerator->getPseudoRandomString(static::CSRF_LENGTH);
        $this->persistentDataHandler->set('state', $state);

        return $this->oAuth2Client->getAuthorizationUrl($redirectUrl, $state, $scope, ['display' => 'popup'], $separator);
    }

    /**
     * 페이스북 재인증 url 반환
     *
     * @param        $redirectUrl
     * @param array  $scope
     * @param string $separator
     *
     * @return string
     */
    public function getReAuthenticationUrlPopup($redirectUrl, array $scope = [], $separator = '&')
    {
        $params = [
            'auth_type' => 'reauthenticate',
            'display'   => 'popup',
        ];

        $state = $this->pseudoRandomStringGenerator->getPseudoRandomString(static::CSRF_LENGTH);
        $this->persistentDataHandler->set('state', $state);

        return $this->oAuth2Client->getAuthorizationUrl($redirectUrl, $state, $scope, $params, $separator);
    }
}
