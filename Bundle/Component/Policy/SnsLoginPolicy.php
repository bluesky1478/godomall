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

namespace Bundle\Component\Policy;

use Framework\Object\SingletonTrait;
use Framework\Utility\GodoUtils;
use Framework\Utility\StringUtils;

/**
 * 소셜로그인 정책
 * @package Bundle\Component\Policy
 * @author  yjwee
 * @method static SnsLoginPolicy getInstance
 */
class SnsLoginPolicy extends \Component\Policy\Policy
{
    use SingletonTrait;

    const KEY = 'member.snsLogin';
    const FACEBOOK = 'facebook';
    private $currentPolicy;

    /**
     * @inheritDoc
     */
    public function __construct(\Component\Policy\Storage\StorageInterface $storage = null)
    {
        parent::__construct($storage);
    }

    /**
     * sns 로그인 정책 저장함수
     *
     * @return bool
     * @throws \Exception
     */
    public function save()
    {
        $logger = \App::getInstance('logger');
        $request = \App::getInstance('request');
        $logger->info('Call SnsLoginPolicy::save');
        $arrSnsLoginUse = $request->post()->get('snsLoginUse', []);
        $arrAppId = $request->post()->get('appId', []);
        $arrAppSecret = $request->post()->get('appSecret', []);
        $useGodoAppId = $request->post()->get('useGodoAppId', 'n');
        $mallSno = $request->post()->get('mallSno', DEFAULT_MALL_NUMBER);

        if (count($arrSnsLoginUse) < 1) {
            throw new \Exception(__('저장에 실패했습니다.'));
        }
        $this->currentPolicy = $this->getValue(self::KEY);
        foreach ($arrSnsLoginUse as $index => $snsLoginUse) {
            //페이스북 아이디 로그인 사용시 회원가입 설정값, 회원가입 항목 설정값 저장
            $this->currentPolicy[$index]['snsLoginUse'] = $snsLoginUse;
            if ($snsLoginUse === 'y') {
                // 간편설정 사용 시 id, secret 정보가 비활성화 되기 때문에 예외 처리
                if ($useGodoAppId != 'y') {
                    $this->setConfigByUseSns($index, $arrAppId[$index], $arrAppSecret[$index]);
                }
                // 고도 페이스북 정보를 이용하는 것은 이전된 경우에만 사용하도록 한다. 관련 설정도 값이 임의로 입력된 경우(데이터 이전)에만 저장한다.
                if (key_exists('useGodoAppId', $this->currentPolicy[$index])) {
                    $this->currentPolicy[self::FACEBOOK]['useGodoAppId'] = $useGodoAppId;
                }
            }
        }

        $this->currentPolicy['simpleLoginFl'] = $request->post()->get('simpleLoginFl');         // 간편 or 일반 회원가입 선택값
        $globalConfigPolicy = [];
        if($mallSno > 1) {
            $this->setValue(self::KEY, $this->currentPolicy); // useFl, simpleLoginFl(공통설정값) 변경시 es_config update
            $globalConfigPolicy['baseInfo'] = $request->post()->get('baseInfo', 'y');
            $globalConfigPolicy['businessInfo'] = $request->post()->get('businessInfo', 'n');
            $globalConfigPolicy['supplementInfo'] = $request->post()->get('supplementInfo', 'n');
            $globalConfigPolicy['additionalInfo'] = $request->post()->get('additionalInfo', 'n');
            return $this->setValue(self::KEY, $globalConfigPolicy, $mallSno);
        } else {
            $this->currentPolicy['baseInfo'] = $request->post()->get('baseInfo', 'y');              // 기본정보
            $this->currentPolicy['businessInfo'] = $request->post()->get('businessInfo', 'n');      // 사업자정보
            $this->currentPolicy['supplementInfo'] = $request->post()->get('supplementInfo', 'n');  // 부가정보
            $this->currentPolicy['additionalInfo'] = $request->post()->get('additionalInfo', 'n');  // 추가정보
            return $this->setValue(self::KEY, $this->currentPolicy);
        }
    }

    /**
     * 설정이 변경 되는 경우에따라 페이스북의 uuid 값을 지우는 함수
     *
     * @param array $before
     * @param array $policy
     */
    public function removeUUID(array $before, array $policy)
    {
        $logger = \App::getInstance('logger');
        $logger->info('Call removeUUID');
        $logger->info('', $before);
        $logger->info('', $policy);
        $dao = \App::load('Component\\Member\\MemberSnsDAO');
        $appId = $policy[self::FACEBOOK]['appId'];  // 변경될 appId
        $useGodoAppId = $policy[self::FACEBOOK]['useGodoAppId'];    // 변경될 간편설정 여부
        $beforeAppId = $before[self::FACEBOOK]['appId'];  // 기존 appId
        $beforeUseGodoAppId = $before[self::FACEBOOK]['useGodoAppId'];    // 기존 간편설정 여부
        StringUtils::strIsSet($appId, '');
        StringUtils::strIsSet($useGodoAppId, 'n');
        StringUtils::strIsSet($beforeAppId, 'godo');
        StringUtils::strIsSet($beforeUseGodoAppId, 'n');
        $logger->debug(sprintf('appId [%s], useGodoAppId [%s], beforeAppId [%s], beforeUseGodoAppId [%s]', $appId, $useGodoAppId, $beforeAppId, $beforeUseGodoAppId));
        if ($useGodoAppId == 'n' && $beforeUseGodoAppId == 'n' && $appId != $beforeAppId && !empty($appId)) {       // 개별설정이면서 appId 가 변경되는 경우
            $logger->info(sprintf('Use your appId. beforeAppId [%s], appId [%s]', $beforeAppId, $appId));
            $dao->updateUUIDBySnsTypeFl(
                [
                    'uuid'      => '',
                    'snsTypeFl' => self::FACEBOOK,
                ]
            );
        } elseif ($useGodoAppId == 'n' && $beforeUseGodoAppId == 'y' && !empty($appId)) {       // 간편설정 해제 및 appId 가 있는 경우
            $logger->info(sprintf('Use your appId. appId [%s]', $appId));
            $dao->updateUUIDBySnsTypeFl(
                [
                    'uuid'      => '',
                    'snsTypeFl' => self::FACEBOOK,
                ]
            );
        } elseif ($useGodoAppId == 'y' && $beforeUseGodoAppId == 'n' && $beforeAppId != 'godo') {       // 간편설정 설정 및 기존 appId 가 간편설정 임시 appId 가 아닌 경우
            $logger->info(sprintf('Use godo appId. beforeAppId [%s]', $beforeAppId));
            $dao->updateUUIDBySnsTypeFl(
                [
                    'uuid'      => '',
                    'snsTypeFl' => self::FACEBOOK,
                ]
            );
        }
    }

    /**
     * sns 로그인 사용 시 관련 정보를 저장하기 위해 실행되는 함수
     *
     * @param $key
     * @param $appId
     * @param $appSecret
     *
     * @throws \Exception
     */
    protected function setConfigByUseSns($key, $appId, $appSecret)
    {
        if (empty($appId) || empty($appSecret)) {
            throw new \Exception(__('SNS 로그인을 사용하려면 App ID 와 App Secret 를 입력하셔야 합니다.'));
        }
        $this->currentPolicy[$key]['appId'] = $appId;
        $this->currentPolicy[$key]['appSecret'] = $appSecret;
    }

    /**
     * sns 로그인 앱 아이디 반환
     *
     * @param null $key
     *
     * @return array|string
     */
    public function getAppId($key = null)
    {
        $policy = $this->getValue(self::KEY);
        StringUtils::strIsSet($policy[self::FACEBOOK]['appId'], 'godo');
        if ($policy[self::FACEBOOK]['useGodoAppId'] == 'y') {
            $policy[self::FACEBOOK]['appId'] = 'godo';
        }
        $appId = [self::FACEBOOK => $policy[self::FACEBOOK]['appId']];  // 코드 사용시 null 값인 경우 오류가 나기때문에 초기 값을 입력함

        return $key === null ? $appId : $appId[$key];
    }

    /**
     * sns 로그인 앱 비밀코드 반환
     *
     * @param null $key
     *
     * @return array
     */
    public function getAppSecret($key = null)
    {
        $policy = $this->getValue(self::KEY);
        StringUtils::strIsSet($policy[self::FACEBOOK]['appSecret'], 'emptyAppSecret');
        $appSecret = [self::FACEBOOK => $policy[self::FACEBOOK]['appSecret']];  // 코드 사용시 null 값인 경우 오류가 나기때문에 초기 값을 입력함

        return $key === null ? $appSecret : $appSecret[$key];
    }

    /**
     * sns 로그인 사용여부 반환
     *
     * @param null $key
     *
     * @return array
     */
    public function getSnsLoginUse($key = null)
    {
        $policy = $this->getValue(self::KEY);
        $appSecret = [self::FACEBOOK => $policy[self::FACEBOOK]['snsLoginUse']];

        return $key === null ? $appSecret : $appSecret[$key];
    }

    /**
     * 페이스북 플러스샵 사용여부 반환
     *
     * @return bool
     */
    public function useFacebook()
    {
        $policy = $this->getValue(self::KEY);
        $snsLoginUse = $policy[self::FACEBOOK]['snsLoginUse'] === 'y';
        if (GodoUtils::isPlusShop(PLUSSHOP_CODE_SNSLOGIN) === false) {
            $snsLoginUse = false;
        }

        return $snsLoginUse;
    }

    /**
     * 페이스북 로그인 사용 시 고도몰 정보를 사용여부 값을 반환
     *
     * @return string
     */
    public function getGodoAppId()
    {
        $policy = $this->getValue(self::KEY);
        // 2017-01-24 yjwee 이나무에서 이전한 경우에만 존재는 값이어야 합니다. isset 초기화는 하지 않습니다.
        // StringUtils::strIsSet($policy[self::FACEBOOK]['useGodoAppId'], 'n');
        $logger = \App::getInstance('logger');
        $logger->info(sprintf('policy member.snsLogin.facebook.useGodoAppId is [%s]', $policy[self::FACEBOOK]['useGodoAppId']));

        return $policy[self::FACEBOOK]['useGodoAppId'];
    }

    /**
     * 페이스북 로그인 사용 시 고도몰 정보를 사용하는지 여부를 반환
     *
     * @return bool
     */
    public function useGodoAppId()
    {
        return $this->getGodoAppId() == 'y';
    }
}
