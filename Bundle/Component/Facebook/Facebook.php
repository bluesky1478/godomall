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

use Component\Policy\SnsLoginPolicy;
use Exception;
use Facebook\Authentication\AccessTokenMetadata;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\GraphNodes\Birthday;

/**
 * Facebook 클래스의 Wrapping 클래스
 *
 * 고도몰5에서는 unset 처리하는 변수에 대한 대응 및 페이스북 데이터 조회
 *
 * @package Component\Facebook
 * @author  yjwee
 */
class Facebook extends \Facebook\Facebook
{
    /** 페이스북 액세스 토큰 세션 키 */
    const SESSION_ACCESS_TOKEN = 'facebook_access_token';
    /** 페이스북 메타데이터 세션 키 */
    const SESSION_METADATA = 'facebook_metadata';
    /** 페이스북 사용자 프로필 정보 세션 키 */
    const SESSION_USER_PROFILE = 'facebook_user_profile';
    /** 페이스북 api 버전(마케팅 api 제외) https://developers.facebook.com/docs/apps/changelog */
    const GRAPH_VERSION = 'v2.9';
    /** @var SnsLoginPolicy 소셜로그인 정책 */
    private $snsLoginPolicy;

    /**
     * @inheritDoc
     */
    public function __construct(array $config = [])
    {
        $this->snsLoginPolicy = \App::load('Component\\Policy\\SnsLoginPolicy');
        $config = array_merge(
            [
                'app_id'                  => $this->snsLoginPolicy->getAppId(SnsLoginPolicy::FACEBOOK),
                'app_secret'              => $this->snsLoginPolicy->getAppSecret(SnsLoginPolicy::FACEBOOK),
                'persistent_data_handler' => new FacebookSessionPersistentDataHandler(),
                'url_detection_handler'   => new FacebookUrlDetectionHandler(),
                'default_graph_version'   => self::GRAPH_VERSION,
            ], $config
        );
        parent::__construct($config);
    }

    /**
     * 세션의 액세스토큰을 사용하여 tokenMetadata 를 세션에 저장하는 함수
     *
     * @throws FacebookSDKException
     * @throws \Exception
     */
    public function setTokenMetadata()
    {
        $session = \App::getInstance('session');
        if ($session->has(self::SESSION_ACCESS_TOKEN) === false) {
            throw new \Exception('NOT FOUND ACCESS_TOKEN');
        }
        $accessToken = $session->get(self::SESSION_ACCESS_TOKEN);
        $tokenMetadata = $this->getOAuth2Client()->debugToken($accessToken);
        $tokenMetadata->validateAppId($this->snsLoginPolicy->getAppId(SnsLoginPolicy::FACEBOOK));
        $tokenMetadata->validateExpiration();
        $session->set(self::SESSION_METADATA, $tokenMetadata);
    }

    /**
     * 고도몰 app id 를 이용하여 중계서버를 이용하는 경우
     * 중계서버의 응답 데이터 체크 및 설정
     *
     * @throws Exception
     */
    public function setTokenMetadataByGodo()
    {
        $session = \App::getInstance('session');
        $request = \App::getInstance('request');
        $logger = \App::getInstance('logger');
        if ($request->request()->has('error')) {
            $logger->error('godo facebook login app callback has error.', $request->request()->all());
            throw new \Exception(__('인증 중 오류가 발생하였습니다.'), 401);
        }
        $meta = ['data' => ['user_id' => $request->request()->get('user_identifier')],];
        $tokenMetadata = new AccessTokenMetadata($meta);
        $logger->info('godo facebook login meta data', $meta);
        $session->set(self::SESSION_METADATA, $tokenMetadata);
    }

    /**
     * 페이스북 응답을 통해 AccessToken 을 검증 후 세션에 저장하는 함수
     *
     * @throws FacebookResponseException
     * @throws FacebookSDKException
     * @throws \Exception
     */
    public function setAccessToken()
    {
        $logger = \App::getInstance('logger');
        $session = \App::getInstance('session');
        $helper = $this->getRedirectLoginHelper();
        try {
            $logger->info(__CLASS__ . ', ' . $this->persistentDataHandler->get('redirectUrl'));
            $accessToken = $helper->getAccessToken($this->persistentDataHandler->get('redirectUrl'));
        } catch (FacebookResponseException $e) {
            $logger->error(__METHOD__ . ', Graph returned an error: ' . $e->getFile() . '[' . $e->getLine() . '], ' . $e->getMessage(), $e->getTrace());
            throw $e;
        } catch (FacebookSDKException $e) {
            $logger->error(__METHOD__ . ', Facebook SDK returned an error: ' . $e->getFile() . '[' . $e->getLine() . '], ' . $e->getMessage(), $e->getTrace());
            throw $e;
        }

        if (!isset($accessToken)) {
            if ($helper->getError()) {
                $logger->error(
                    __METHOD__ . 'HTTP/1.0 401 Unauthorized', [
                        'Error'            => $helper->getError(),
                        'ErrorCode'        => $helper->getErrorCode(),
                        'ErrorReason'      => $helper->getErrorReason(),
                        'ErrorDescription' => $helper->getErrorDescription(),
                    ]
                );
                if ($helper->getErrorReason() == 'user_denied') {
                    throw new \Exception(__('로그인을 취소하셨습니다.'), $helper->getErrorCode());
                }
                throw new \Exception(__('인증 중 오류가 발생하였습니다.'), 401);
            } else {
                $logger->error(__METHOD__ . 'HTTP/1.0 400 Bad Request');
                throw new \Exception(__('잘못된 요청입니다.'), 400);
            }
        }

        $session->set(self::SESSION_ACCESS_TOKEN, $accessToken);
    }

    /**
     * @inheritDoc
     */
    public function getRedirectLoginHelper()
    {
        return new FacebookRedirectLoginHelper(
            $this->getOAuth2Client(),
            $this->persistentDataHandler,
            $this->urlDetectionHandler,
            $this->pseudoRandomStringGenerator
        );
    }

    /**
     * redirect strict 모드사용 시 uri 만 허용되기때문에
     * return url 을 페이스북 프리픽스가 붙은 세션에 저장
     *
     * @return string
     */
    public function getReturnUrl()
    {
        return $this->persistentDataHandler->get('returnUrl');
    }

    /**
     * 페이스북 로그인 URL 반환
     *
     * @param $returnUrl
     *
     * @return string
     */
    public function getLoginUrl($returnUrl)
    {
        $helper = $this->getRedirectLoginHelper();
        $redirectUrl = $this->getUrl() . '/member/facebook/login_callback.php';
        $this->persistentDataHandler->set('returnUrl', $returnUrl);
        $this->persistentDataHandler->set('redirectUrl', $redirectUrl);
        $url = $helper->getLoginUrlPopup($redirectUrl);

        return $url;
    }

    /**
     * 페이스북 callback url 기본
     *
     * @return string http(s)://HTTP_HOST.com
     */
    protected function getUrl()
    {
        $request = \App::getInstance('request');
        $url = $request->getHostNoPort();
        // 마이앱 api로 url 전달할 경우 서브도메인 모바일로 변경
        if ($request->getSubdomain() === 'api' && strpos(\Request::getFullFileUri(), DIR_MYAPP) !== false) {
            $url = str_replace('api.', 'm.', $url);
        }
        $url = $request->getScheme() . '://' . $url;
        if ($request->hasGlobalMallSubDomain()) {
            $url .= '/' . $request->getMallNameByUri();
        }

        return $url;
    }

    /**
     * 고도몰 페이스북 중계서버 로그인 URL 반환
     *
     * @param $returnUrl
     *
     * @return string
     */
    public function getGodoLoginUrl($returnUrl)
    {
        $this->persistentDataHandler->set('returnUrl', $returnUrl);

        return \Component\Godo\GodoFacebookServerApi::getInstance()->getLoginUrlPopup($this->getUrl() . '/member/facebook/login_callback.php');
    }

    /**
     * 고도몰 페이스북 중계서버 재인증 URL 반환
     *
     * @return string
     */
    public function getGodoReAuthenticationUrl()
    {
        $godoFacebookServerApi = \Component\Godo\GodoFacebookServerApi::getInstance();
        $loginUrlPopup = $godoFacebookServerApi->getLoginUrlPopup($this->getUrl() . '/member/facebook/re_authentication_callback.php');

        return $godoFacebookServerApi->getLogoutUrlPopup($loginUrlPopup);
    }

    /**
     * 고도몰 페이스북 중계서버 회원가입 URL 반환
     *
     * @return string
     */
    public function getGodoJoinUrl()
    {
        return \Component\Godo\GodoFacebookServerApi::getInstance()->getLoginUrlPopup($this->getUrl() . '/member/facebook/join_callback.php');
    }

    /**
     * 고도몰 페이스북 중계서버 연결 URL 반환
     *
     * @return string
     */
    public function getGodoConnectUrl()
    {
        $godoFacebookServerApi = \Component\Godo\GodoFacebookServerApi::getInstance();

        return $godoFacebookServerApi->getLoginUrlPopup($this->getUrl() . '/member/facebook/connect_callback.php');
    }

    /**
     * 페이스북 연결 URL 반환
     *
     * @return string
     */
    public function getConnectUrl()
    {
        $helper = $this->getRedirectLoginHelper();
        $redirectUrl = $this->getUrl() . '/member/facebook/connect_callback.php';
        $this->persistentDataHandler->set('redirectUrl', $redirectUrl);
        $url = $helper->getLoginUrlPopup($redirectUrl);

        return $url;
    }

    /**
     * 페이스북 연결해제
     *
     * @return \Facebook\FacebookResponse
     * @throws \Exception
     */
    public function disConnect()
    {
        if ($this->getAccessToken() === false) {
            throw new \Exception(__('연결해제에 필요한 정보가 없습니다.'));
        }
        if ($this->hasMetadata() === false) {
            throw new \Exception(__('연결해제에 필요한 정보가 없습니다.'));
        }
        /** @var \Facebook\Authentication\AccessTokenMetadata $tokenMetadata */
        $tokenMetadata = $this->getFacebookMetadata();
        $response = $this->delete($tokenMetadata->getUserId() . '/permissions', [], $this->getAccessToken());

        return $response;
    }

    /**
     * 세션의 페이스북 접근토큰 반환
     *
     * @return mixed
     */
    protected function getAccessToken()
    {
        return \App::getInstance('session')->get(self::SESSION_ACCESS_TOKEN);
    }

    /**
     * 세션의 페이스북 메타데이터 확인
     *
     * @return boolean
     */
    protected function hasMetadata()
    {
        return \App::getInstance('session')->has(self::SESSION_METADATA);
    }

    /**
     * 세션의 페이스북 메타데이터 정보 반환
     *
     * @param array $default
     *
     * @return mixed
     */
    protected function getFacebookMetadata($default = [])
    {
        return \App::getInstance('session')->get(self::SESSION_METADATA, $default);
    }

    /**
     * 페이스북 회원가입 URL 반환
     *
     * @return string
     */
    public function getJoinUrl()
    {
        $helper = $this->getRedirectLoginHelper();
        $permissions = ['email',];
        $redirectUrl = $this->getUrl() . '/member/facebook/join_callback.php';
        $this->persistentDataHandler->set('redirectUrl', $redirectUrl);
        $url = $helper->getLoginUrlPopup($redirectUrl, $permissions);

        return $url;
    }

    /**
     * 페이스북 재인증 URL 반환
     *
     * @return string
     */
    public function getReAuthenticationUrl()
    {
        $helper = $this->getRedirectLoginHelper();
        $redirectUrl = $this->getUrl() . '/member/facebook/re_authentication_callback.php';
        $this->persistentDataHandler->set('redirectUrl', $redirectUrl);
        $url = $helper->getReAuthenticationUrlPopup($redirectUrl);

        return $url;
    }

    /**
     * 사용자 프로필 조회
     *
     * @return array
     * @throws FacebookSDKException
     */
    public function getUserProfile()
    {
        $endpoint = 'me?fields=birthday,email,name,gender';
        $response = $this->get($endpoint, $this->getAccessToken());
        $user = $response->getGraphUser();
        $this->setUserProfile($user->all());

        return $user->all();
    }

    /**
     * 세션에 페이스북 사용자정보 설정
     *
     * @param $userProfile
     */
    protected function setUserProfile($userProfile)
    {
        \App::getInstance('session')->set(self::SESSION_USER_PROFILE, $userProfile);
    }

    /**
     * 중계서버를 이용한 사용자 프로필 조회
     *
     * @return array
     * @throws FacebookSDKException
     */
    public function getUserProfileByIdentifier()
    {
        $godoConfig = \Component\Godo\GodoFacebookServerApi::getInstance()->getGodoConfig();
        $session = \App::getInstance('session');
        /** @var AccessTokenMetadata $metaData */
        $metaData = $session->get(self::SESSION_METADATA, []);
        $response = $this->get('/' . $metaData->getUserId() . '?fields=birthday,email,name,gender', vsprintf('%s|%s', $godoConfig));
        $user = $response->getGraphUser();
        $this->setUserProfile($user);

        return $user->all();
    }

    /**
     * 페이스북 사용자 정보를 json 변환
     *
     * @param array $profile
     *
     * @return string
     */
    public function toJsonEncode(array $profile)
    {
        if (key_exists('gender', $profile)) {
            if ($profile['gender'] == 'male') {
                $profile['gender'] = 'm';
            } elseif ($profile['gender'] == 'female') {
                $profile['gender'] = 'w';
            }
        }
        if (key_exists('birthday', $profile)) {
            /** @var Birthday $facebookBirthday */
            $facebookBirthday = $profile['birthday'];
            $profile['birthday'] = $facebookBirthday->format('Y-m-d');
        }

        return json_encode($profile);
    }

    /**
     * 페이스북 로그아웃 URL
     *
     * @param string $next 로그아웃 후 이동할 페이지
     *
     * @return bool|string access token 이 없는 경우 false
     * @throws FacebookSDKException
     */
    public function getLogoutUrl($next)
    {
        $logger = \App::getInstance('logger');
        if (\Component\Policy\SnsLoginPolicy::getInstance()->useGodoAppId()) {
            $logoutUrl = \Component\Godo\GodoFacebookServerApi::getInstance()->getLogoutUrlPopup($this->getUrl() . $next);
        } else {
            if (!$this->hasAccessToken()) {
                return false;
            }
            $accessToken = $this->getAccessToken();
            $this->getOAuth2Client()->debugToken($accessToken)->validateExpiration();
            $this->clearSession();
            $logoutUrl = $this->getRedirectLoginHelper()->getLogoutUrl($accessToken, $this->getUrl() . $next);
        }
        $logger->info(sprintf('Return facebook logout url. url is %s', $logoutUrl));

        return $logoutUrl;
    }

    /**
     * 세션에 페이스북 접근토큰 확인
     *
     * @return boolean
     */
    protected function hasAccessToken()
    {
        return \App::getInstance('session')->has(self::SESSION_ACCESS_TOKEN);
    }

    /**
     * 페이스북 관련 세션을 제거
     *
     */
    public function clearSession()
    {
        $logger = \App::getInstance('logger');
        $session = \App::getInstance('session');
        $session->del(self::SESSION_METADATA);
        $logger->info(sprintf('Delete session name %s ', self::SESSION_METADATA));
        $session->del(self::SESSION_ACCESS_TOKEN);
        $logger->info(sprintf('Delete session name %s ', self::SESSION_ACCESS_TOKEN));
        $session->del(self::SESSION_USER_PROFILE);
        $logger->info(sprintf('Delete session name %s ', self::SESSION_USER_PROFILE));
    }
}
