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

namespace Bundle\Controller\Admin\Member;


use Component\Mall\Mall;
use Component\Policy\JoinItemPolicy;
use Component\Policy\SnsLoginPolicy;
use Component\SiteLink\SecureSocketLayer;
use Framework\Debug\Exception\AlertBackException;
use Framework\Utility\ComponentUtils;
use Framework\Utility\StringUtils;

/**
 * Class SnsLoginConfigController
 * @package Bundle\Controller\Admin\Member
 * @author  yjwee
 */
class SnsLoginConfigController extends \Controller\Admin\Controller
{
    public function index()
    {
        $request = \App::getInstance('request');
        $globals = \App::getInstance('globals');
        /** @var array 유효한 OAuth 리디렉션 URI(리디렉션 URI에 Strict 모드 사용 시 필수) */
        $validOAuthRedirectURIs = [
            '/member/facebook/login_callback.php',
            '/member/facebook/join_callback.php',
            '/member/facebook/re_authentication_callback.php',
            '/member/facebook/connect_callback.php',
        ];

        /** @var \Bundle\Controller\Admin\Controller $this */
        try {
            $this->callMenu('member', 'sns', 'sns');

            $mobilePolicy = ComponentUtils::getPolicy('mobile.config');
            StringUtils::strIsSet($mobilePolicy['mobileShopFl'], 'n');
            $snsLoginPolicy = \Component\Policy\SnsLoginPolicy::getInstance();
            $snsLoginUse = $snsLoginPolicy->getSnsLoginUse();

            $mallSno = $request->get()->get('mallSno', DEFAULT_MALL_NUMBER);
            $this->setData('mallInputDisp', (int) $mallSno !== 1);
            $mall = \App::load(Mall::class);
            $globalMall = $mall->getUseGlobalMall();
            /**
             * 도메인 주소에 페이스북 리턴 uri 를 추가 해주는 함수
             *
             * @param string $url 도메인
             * @param string $prefix
             *
             * @return array 페이스북 리턴 uri 가 추가된 url 배열
             */
            $appendUris = function (string $url, $prefix = 'http://') use ($validOAuthRedirectURIs): array {
                $urls = [];
                foreach ($validOAuthRedirectURIs as $redirectURIs) {
                    $urls[] = $prefix . $url . $redirectURIs;
                }

                return $urls;
            };

            $mallRedirectUrls = [[]];
            $mallDomain = $globals->get('gSite.basic.info.mallDomain');

            // 보안서버 설정 조회
            $secureSocketLayer = \App::load(SecureSocketLayer::class);
            $getSsl = function ($position) use ($secureSocketLayer) {
                $ssl = $secureSocketLayer->getSsl(
                    [
                        'sslConfigUse'      => 'y',
                        'sslConfigType'     => 'godo',
                        'sslConfigPosition' => $position,
                        'sslConfigStatus'   => 'used',
                    ]
                );
                $result = [];
                foreach ($ssl as $item) {
                    $result[$item['sslConfigMallFl']][] = $item;
                }

                return $result;
            };

            $pcSsl = $getSsl('pc');
            $mobileSsl = $getSsl('mobile');

            // 기준몰 주소를 기반으로 해외몰 임시도메인 접근 주소 추가
            $appendGlobal = function ($domain, $prefix = null) use (&$mallRedirectUrls, $globalMall, $appendUris) {
                foreach ($globalMall as $item) {
                    $mallRedirectUrls[] = $appendUris($domain . '/' . $item['domainFl'], $prefix);
                }
            };

            // 기준상점 유료보안서버 사용하는 경우
            // 기준상점 유료보안서버 사용안하는 경우
            // 해외상점 연결도메인이 유료보안서버 도메인인 경우
            // 해외상점 연결도메인이 유료보안서버 도메인 아닌 경우
            // 해외상점 연결도메인이 없는 경우
            // 해외상점 연결도메인이 없는데 유료보안서버 사용하는 경우

            // PC 보안서버 주소 추가
            foreach ($pcSsl['kr'] as $ssl) {
                $mallRedirectUrls[] = $appendUris($ssl['sslConfigDomain'], 'https://');
                $appendGlobal($ssl['sslConfigDomain'], 'https://');
            }
            // 모바일 보안서버 주소 추가
            foreach ($mobileSsl['kr'] as $ssl) {
                $mallRedirectUrls[] = $appendUris($ssl['sslConfigDomain'], 'https://');
                $appendGlobal($ssl['sslConfigDomain'], 'https://');
            }
            $hasSslKrPc = \count($pcSsl['kr']) > 0;
            // PC 기준몰 보안서버 주소 체크
            if (!$hasSslKrPc) {
                $mallRedirectUrls[] = $appendUris($mallDomain);
                $appendGlobal($mallDomain);
            }
            $hasSslKrMobile = \count($mobileSsl['kr']) > 0;
            // 모바일 기준몰 보안서버 주소 체크
            if (!$hasSslKrMobile) {
                $mallRedirectUrls[] = $appendUris($mallDomain, 'http://m.');
                $appendGlobal($mallDomain, 'http://m.');
            }

            // 해외상점 주소 추가
            foreach ($globalMall as $mall) {
                if (($connectUrls = json_decode($mall['connectDomain'], true)) !== null) {
                    foreach ($connectUrls['connect'] as $connectUrl) {
                        $hasSslPc = $hasSslMobile = false;
                        // 연결도메인이 ssl 설정에 존재하는 경우 https 로 출력
                        foreach ($pcSsl[$mall['domainFl']] as $index => $item) {
                            $hasSslPc = $connectUrl === str_replace('www.', '', $item['sslConfigDomain']);
                            if ($hasSslPc) {
                                $mallRedirectUrls[] = $appendUris($item['sslConfigDomain'], 'https://');
                                break;
                            }
                        }
                        foreach ($mobileSsl[$mall['domainFl']] as $index => $item) {
                            $hasSslMobile = $connectUrl === $item['sslConfigDomain'];
                            if ($hasSslMobile) {
                                $mallRedirectUrls[] = $appendUris($item['sslConfigDomain'], 'https://');
                                break;
                            }
                        }
                        // 연결도메인이 ssl 이 아닌 경우 추가
                        if (!$hasSslPc) {
                            $mallRedirectUrls[] = $appendUris($connectUrl);
                        }
                        if (!$hasSslMobile) {
                            $mallRedirectUrls[] = $appendUris($connectUrl, 'http://m.');
                        }
                    }
                }
            }

            $mallRedirectUrls = array_merge(...$mallRedirectUrls);
            $this->setData('mallRedirectUrls', $mallRedirectUrls);

            $countMallList = \count($globalMall);
            if ($countMallList > 1) {
                $this->setData('mallCnt', $countMallList);
                $this->setData('mallList', $globalMall);
                $this->setData('mallSno', $mallSno);
                if ($mallSno > 1) {
                    $defaultData = gd_policy('basic.info', DEFAULT_MALL_NUMBER);
                    foreach ($defaultData as $key => $value) {
                        if (\in_array($key, Mall::GLOBAL_MALL_BASE_INFO, true) === true) {
                            $data[$key] = $value;
                        }
                    }

                    $disabled = ' disabled = "disabled"';
                    $readonly = ' readonly = "readonly"';
                    $this->setData('disabled', $disabled);
                    $this->setData('readonly', $readonly);
                }
            }
            if ($mallSno > 1) {
                $policy = gd_policy(SnsLoginPolicy::KEY, $mallSno);
            } else {
                $policy = gd_policy(SnsLoginPolicy::KEY);
            }
            $checked = [];
            if (empty($policy['simpleLoginFl']) === true) { // 신규 설치시 디폴트값 설정
                $checked['simpleLoginFl']['y'] = 'checked="checked"';
            } else {
                $checked['simpleLoginFl'][$policy['simpleLoginFl']] = 'checked="checked"';
            }

            foreach ($snsLoginUse as $index => $item) {
                StringUtils::strIsSet($item, 'n');
                $checked['snsLoginUse'][$index][$item] = 'checked="checked"';
            }
            if ($snsLoginPolicy->useGodoAppId()) {
                $checked['useGodoAppId']['y'] = 'checked="checked"';
            }
            $appId = $snsLoginPolicy->getAppId();
            if ($appId[SnsLoginPolicy::FACEBOOK] === 'godo') {
                $appId[SnsLoginPolicy::FACEBOOK] = '';    // 고객 요청으로 인해 공백처리
            }
            $appSecret = $snsLoginPolicy->getAppSecret();
            if ($appSecret[SnsLoginPolicy::FACEBOOK] === 'emptyAppSecret') {
                $appSecret[SnsLoginPolicy::FACEBOOK] = '';    // 고객 요청으로 인해 공백처리
            }

            //회원가입항목정보
            $policyService = \App::load(JoinItemPolicy::class);
            $joinItemPolicy = $policyService->getJoinPolicyDisplay($mallSno);
            $policy['items'] = $joinItemPolicy;

            $this->setData('data', $policy);
            $this->setData('checked', $checked);
            $this->setData('appId', $appId);
            $this->setData('appSecret', $appSecret);
            $this->setData('godoAppIdFl', $snsLoginPolicy->getGodoAppId());
        } catch (\Throwable $e) {
            throw new AlertBackException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
