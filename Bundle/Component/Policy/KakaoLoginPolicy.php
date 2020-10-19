<?php
/**
 * Created by PhpStorm.
 * User: godo
 * Date: 2018-08-10
 * Time: 오후 5:34
 */

namespace Bundle\Component\Policy;

use Framework\Object\StorageInterface;
use Component\Godo\GodoKakaoServerApi;
class KakaoLoginPolicy extends \Component\Policy\Policy
{
    const KEY = 'member.kakaoLogin';
    const KAKAO = 'kakao';
    protected $currentPolicy;

    public function __construct(StorageInterface $storage = null)
    {
        parent::__construct($storage);
        $this->currentPolicy = $this->getValue(self::KEY);
    }

    public function save($policy)
    {
        $this->currentPolicy['useFl'] = $policy['useFl'];
        $this->currentPolicy['restApiKey'] = $policy['restApiKey'];
        $this->currentPolicy['adminKey'] = $policy['adminKey'];

        if($policy['useFl'] == 'y') {
            $this->currentPolicy['simpleLoginFl'] = $policy['simpleLoginFl'];
            $this->currentPolicy['baseInfo'] = gd_isset($policy['baseInfo'],'y');
            $this->currentPolicy['supplementInfo'] = gd_isset($policy['supplementInfo'], 'n');
            $this->currentPolicy['additionalInfo'] = gd_isset($policy['additionalInfo'],'n');
            $this->currentPolicy['businessInfo'] = gd_isset($policy['businessInfo'], 'n');
        }
            return $this->setValue(self::KEY, $this->currentPolicy);
    }

    public function useKakaoLogin()
    {
        return $this->currentPolicy['useFl'] == 'y';
    }
}