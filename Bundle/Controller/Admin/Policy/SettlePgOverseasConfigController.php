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
 * @link http://www.godo.co.kr
 */
namespace Bundle\Controller\Admin\Policy;

use App;

/**
 * PG 통합 설정
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class SettlePgOverseasConfigController extends \Controller\Admin\Controller
{
    /**
     * index
     *
     * @throws \Exception
     */
    public function index()
    {
        // --- 메뉴 설정
        $this->callMenu('policy', 'multiMall', 'pgOverseasConfig');

        try {
            // --- PG 설정 불러오기
            $pg = App::load('\\Component\\Payment\\PG');

            // 각 PG 별 설정값
            $pgConf = gd_opgs();
            $pgConf = $pg->setDefaultPgDataOverseas($pgConf);

            // 결제수단 설정 config 불러오기
            $settle = gd_policy('order.settleKind');

            // 해외 PG 결제수단
            $settleKindConf = $pg->setSettleKind4Overseas();
            $settleKindCode = array_values($settleKindConf);

            $checked = [];
            foreach ($settle as $sKey => $sVal) {
                if (in_array($sKey, $settleKindCode)) {
                    // 사용여부
                    $checked[$sKey][$settle[$sKey]['useFl']] = 'checked="checked"';
                }
            }
            $setDefaultPgDataOverseasList = [];
            foreach ($pg->setDefaultPgDataOverseasList() as $pgKey => $pgVal) {
                foreach ($pgVal as $cKey => $cVal) {
                    // 사용여부 (없는 경우 n 처리)
                    if (empty($settle[$settleKindConf[$cKey]]) === true) {
                        $checked[$settleKindConf[$cKey]]['n'] = 'checked="checked"';
                    }

                    // 사용상점
                    foreach ($pgConf[$pgKey][$cKey]['mallFl'] as $mKey => $mVal) {
                        $checked[$settleKindConf[$cKey]]['mallFl'][$mKey][$mVal] = 'checked="checked"';
                    }

                    // 2017-09-29 사이렉스페이 VISA, AMEX 메뉴 숨김처리
                    if (!in_array($cKey, ['vmcard', 'jacard', 'tenpay', 'paypal'])) {
                        $setDefaultPgDataOverseasList[$pgKey][$cKey] = $cVal;
                    }
                }
            }

            // 자동세팅 여부 체크
            $pgAutoSetting = false;
            foreach ($pgConf as $pKey => $pVal) {
                foreach ($pVal as $aVal) {
                    if ($aVal['pgAutoSetting'] === 'y') {
                        $pgAutoSetting = true;
                    }
                }
            }

            // PG사 주소
            $pgSite = [
                'cyrexpay' => [
                    'admin' => 'https://pg.cyrexpay.com/',
                    'site' => 'http://cyrexpay.com/',
                ],
                'allpay' => [
                    'admin' => 'http://www.allpayx.com/bc/',
                    'site' => 'http://www.allpayx.com/',
                ],
                'payletter' => [
                    'admin' => 'https://psp.payletter.com:999/Login/VerifyAdmLogin.aspx',
                    'site' => 'https://www.payletter.com/',
                ],
            ];
        } catch (\Exception $e) {
            // echo ($e->getMessage());
        }

        // --- 관리자 디자인 템플릿
        $this->setData('pgAutoSetting', $pgAutoSetting);
        $this->setData('pgList', $setDefaultPgDataOverseasList);
        $this->setData('settleKindConf', $settleKindConf);
        $this->setData('settleCurrencyConf', $pg->setDefaultPgDataOverseasCurrency()); // 승인 화폐 설정
        $this->setData('pgKeyConf', $pg->setDefaultPgDataOverseasKey());
        $this->setData('useMallConf', $pg->setDefaultPgDataOverseasUseMall());
        $this->setData('data', gd_htmlspecialchars($pgConf));
        $this->setData('pgSite', $pgSite);
        $this->setData('checked', $checked);
    }
}
