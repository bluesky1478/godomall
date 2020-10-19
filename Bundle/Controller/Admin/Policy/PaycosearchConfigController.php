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

use Component\Nhn\Paycosearch;
use Component\Nhn\PaycosearchApi;

/**
 * Class PaycosearchConfigController
 * @package Bundle\Controller\Admin\Policy
 * @author  yoonar
 */
class PaycosearchConfigController extends \Controller\Admin\Controller
{
    public function index()
    {
        // 고도 공지서버 모듈 호출
        try {
            $this->callMenu('policy', 'paycoSearch', 'config');

            $paycosearch = new Paycosearch();
            $paycosearchApi = new PaycosearchApi();

            $paycosearchConfig = $paycosearch->getConfig();

            if ($paycosearch->neSearchConfigIsset != 'Y') {
                $paycoSearchSetMode = 'request'; // 초기신청 모드
            } else {
                $goodsSearchCount = $paycosearchApi->paycoSearchDataCountSetting();
                if(($goodsSearchCount < 1 || empty($goodsSearchCount) === true) && empty($goodsSearchCfg['searchRejectMessage']) === true) {
                    $paycoSearchSetMode = 'wait'; // 상품연동 요청 대기
                } else {
                    $paycoSearchSetMode = 'setting'; // 설정 가능 모드
                }
            }

            $paycoSearchPrintMode = $displayUseyn = $disabledUsey = '';
            if ($paycoSearchSetMode == 'wait') { // 상품 연동 요청 대기 & 상품연동 없는 대기 상태
                switch ($paycosearchConfig['searchUse']) {
                    case 'T' :
                        $paycoSearchPrintMode =  'wait-T'; // 설정 대기 - 테스트하기
                        $displayUseyn = "style='display:none'";
                        break;
                    case 'Y' :
                        $paycoSearchPrintMode =  'wait-Y'; // 설정 대기 - 실사용하기
                        $displayUseyn = "style='display:none'";
                        break;
                    case 'N' :
                        $paycoSearchPrintMode =  'wait-N'; // 설정 대기 사용안함
                        //$disabledUsey = "disabled='disabled'"; // 사용 불가 속성
                        break;
                }
            } else if ($paycoSearchSetMode == 'setting') { // 설정 가능 모드 & 상품 연동
                switch ($paycosearchConfig['searchUse']) {
                    case 'T' :
                        $paycoSearchPrintMode =  'setting-T'; // 설정 가능 - 테스트하기
                        break;
                    case 'Y' :
                        $paycoSearchPrintMode =  'setting-Y'; // 설정 가능 - 실사용하기
                        break;
                    case 'N' :
                        $paycoSearchPrintMode =  'setting-N'; // 설정 가능 - 사용안함
                        //$disabledUsey = "disabled='disabled'"; // 사용 불가 속성
                        break;
                }
            }

            $this->setData('config', $paycosearchConfig);
            $this->setData('status', $paycosearchConfig['searchUse']); // 사용 설정 여부
            $this->setData('paycoSearchSetMode', $paycoSearchSetMode); // 설정 대기/가능
            $this->setData('paycoSearchPrintMode', $paycoSearchPrintMode); // 설정 대기/가능
            $this->setData('displayUseyn', $displayUseyn); // 출력 여부
            $this->setData('disabledUsey', $disabledUsey); // 사용 속성
            $this->setData('createType', gd_isset($paycosearchConfig['createType'], 'auto'));
            $this->setData('useCreateTypeFl', $paycosearch->checkFilemtime());
            $this->setData('autocompleteFl', gd_isset($paycosearchConfig['autocomplete'], 'N'));

            if($paycosearchConfig['searchUse']) {
                $checked = array();
                $checked['paycosearchFl'][$paycosearchConfig['searchUse']] = 'checked="checked"';
                $pipDir = 'http://' . $paycosearchConfig['searchDisplayDomain'] . '/partner/paycosearch_all.php';
                $checked['createType'][$paycosearchConfig['createType']] = 'checked="checked"';
                $checked['autocomplete'][$paycosearchConfig['autocomplete'] ?? 'N'] = 'checked="checked"';

                if($paycosearchConfig['searchUse'] === 'N') {
                    $disabled['autocomplete']['Y'] = 'disabled="disabled"';
                }

                $shopData = [
                    'checked' => gd_isset($checked),
                    'disabled' => gd_isset($disabled),
                    'shopName' => $paycosearchConfig['searchShopName'],
                    'dispDomain' => $paycosearchConfig['searchDisplayDomain'],
                    'pipDir' => $pipDir,
                    'pipLink' => '<a href="' . $pipDir . '" target="_blank" class="black">' . $pipDir . '</a> <a href="' . $pipDir . '" class="btn btn-gray p_1_4" target="_blank">미리보기</a>',
                ];
                if($paycosearchConfig['searchUse'] === 'T') { // 테스트

                } else if($paycosearchConfig['searchUse'] === 'Y') { // 실사용

                } else if($paycosearchConfig['searchUse'] === 'N') { // 사용안함
                    $shopData['rejectMsg'] = '중복상점';
                }
            }
            $this->setData('shopData', $shopData);
            $this->setData('makePipFl', $paycosearch->makePipFl());
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
