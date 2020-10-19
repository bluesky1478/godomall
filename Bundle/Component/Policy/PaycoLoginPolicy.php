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

use Component\Godo\GodoPaycoServerApi;
use Framework\Object\StorageInterface;

/**
 * 페이코 아이디 로그인 정책 관리
 * @package Bundle\Component\Policy
 * @author  yjwee
 */
class PaycoLoginPolicy extends \Component\Policy\Policy
{
    const KEY = 'member.paycoLogin';
    const PAYCO = 'payco';
    protected $currentPolicy;

    public function __construct(StorageInterface $storage = null)
    {
        parent::__construct($storage);
        $this->currentPolicy = $this->getValue(self::KEY);
    }

    public function save($policy)
    {
        $GodoPaycoServerApi = new GodoPaycoServerApi();
        $this->currentPolicy['useFl'] = $policy['useFl'];
        if ($policy['useFl'] == 'y' && \Session::has(GodoPaycoServerApi::SESSION_PAYCO_SERVICE_CODE)) {
            $sessionCode = \Session::get(GodoPaycoServerApi::SESSION_PAYCO_SERVICE_CODE);
            $this->currentPolicy['clientId'] = $sessionCode['clientId'];
            $this->currentPolicy['clientSecret'] = $sessionCode['clientSecret'];
            $this->currentPolicy['serviceName'] = $sessionCode['serviceName'];
            $this->currentPolicy['serviceURL'] = $sessionCode['serviceURL'];
            $this->currentPolicy['consumerName'] = $sessionCode['consumerName'];
        }
        $GodoPaycoServerApi->modifyServiceCode($this->currentPolicy['useFl']);

        // 페이코 아이디 로그인 사용시 회원가입 설정값, 항목 설정값 저장
        if($policy['useFl'] == 'y') {
            $this->currentPolicy['simpleLoginFl'] = $policy['simpleLoginFl'];                  // 간편or일반 회원가입 선택
            $this->currentPolicy['baseInfo'] = gd_isset($policy['baseInfo'],'y');              // 기본정보
            $this->currentPolicy['supplementInfo'] = gd_isset($policy['supplementInfo'], 'n'); // 부가정보
            $this->currentPolicy['additionalInfo'] = gd_isset($policy['additionalInfo'],'n');  // 추가 정보
        }

        $globalConfigPolicy = [];
        if($policy['mallSno'] > 1) {
            $this->setValue(self::KEY, $this->currentPolicy); // useFl, simpleLoginFl(공통설정값) 변경시 es_config update
                $globalConfigPolicy['baseInfo'] = gd_isset($policy['baseInfo'],'y');
                $globalConfigPolicy['supplementInfo'] = gd_isset($policy['supplementInfo'], 'n');
                $globalConfigPolicy['additionalInfo'] = gd_isset($policy['additionalInfo'],'n');
            return $this->setValue(self::KEY, $globalConfigPolicy, $policy['mallSno']);
        } else {
            return $this->setValue(self::KEY, $this->currentPolicy);
        }

    }

    public function usePaycoLogin()
    {
        return $this->currentPolicy['useFl'] == 'y';
    }
}
