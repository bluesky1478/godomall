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

use Component\Policy\Policy;
use Exception;
use Globals;
use Request;

/**
 * 가격/단위 기준설정
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class BaseCurrencyUnitController extends \Controller\Admin\Controller
{
    /**
     * index
     *
     * @throws Except
     */
    public function index()
    {

        // --- 메뉴 설정
        $this->callMenu('policy', 'basic', 'currency');

        // --- 기본 정보
        try {
            // 모듈 설정
            $policy = new Policy();

            // 금액 절사 기준 설정
            $unitType = [
                'goods' => ['name' => __('상품금액')],
                'mileage' => ['name' => __('마일리지')],
                'coupon' => ['name' => __('쿠폰')],
                'member_group' => ['name' => __('회원등급별')],
                'scm_calculate' => ['name' => __('공급사 정산')],
            ];

            // 절사 정보
            //$unitPrecision = ['0.001' => '0.001', '0.01' => '0.01', '0.1' => '0.1', '1' => '1', '10' => '10', '100' => '100', '1000' => '1000'];
            $unitPrecision = ['0.1' => '0.1', '1' => '1', '10' => '10', '100' => '100', '1000' => '1000'];
            $unitRound = ['floor' => __('버림'), 'round' => __('반올림'), 'ceil' => __('올림')];

            // 절사 정보 - 공급사
            $unitPrecisionScm = ['0.1' => '0.1'];
            $unitRoundScm = ['floor' => __('버림')];

            // 저장 정보
            $currency = Globals::get('gCurrency');
            $weight = Globals::get('gWeight');
            $volume = Globals::get('gVolume');
            $trunc = Globals::get('gTrunc');

            $selected['country'][$currency['country']] = 'selected="selected"';

        } catch (Exception $e) {
            echo $e->ectMessage;
        }

        // --- 관리자 디자인 템플릿
        if (Request::get()->get('popupMode')) {
            $this->getView()->setDefine('layout', 'layout_blank.php');
        } else {
            $this->getView()->setDefine('layout', 'layout_basic.php');
            $this->getView()->setDefine('layoutHeader', 'header.php');
            $this->getView()->setDefine('layoutMenu', 'menu.php');
            $this->getView()->setDefine('layoutFooter', 'footer.php');
        }

        $this->getView()->setDefine('layoutContent', Request::getDirectoryUri() . '/' . Request::getFileUri());

        $this->setData('setCountryUnit', $policy->setCountryUnit);
        $this->setData('setWightUnit', $policy->setWightUnit);
        $this->setData('setVolumeUnit', $policy->setVolumeUnit);
        $this->setData('unitType', $unitType);
        $this->setData('unitPrecision', $unitPrecision);
        $this->setData('unitRound', $unitRound);
        $this->setData('unitPrecisionScm', $unitPrecisionScm);
        $this->setData('unitRoundScm', $unitRoundScm);
        $this->setData('currency', $currency);
        $this->setData('weight', $weight);
        $this->setData('volume', $volume);
        $this->setData('trunc', $trunc);
        $this->setData('selected', $selected);
    }
}
