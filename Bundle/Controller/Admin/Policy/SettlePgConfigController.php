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

use Component\Database\DBTableField;
use Component\Payment\PG;
use Component\Payment\CashReceipt;
use Framework\Utility\GodoUtils;
use Framework\Utility\StringUtils;
use Globals;
use Request;
use App;

/**
 * PG 통합 설정
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class SettlePgConfigController extends \Controller\Admin\Controller
{
    /**
     * index
     *
     * @throws Except
     */
    public function index()
    {
        // --- 메뉴 설정
        $this->callMenu('policy', 'settle', 'pg');

        // --- 모듈 호출
        $pgCodeConfig = App::getConfig('payment.pg');
        $eggCodeConfig = App::getConfig('payment.egg');
        $periodCodeConfig = App::getConfig('payment.installment');
        $cashReceipt = new CashReceipt();

        try {
            // --- PG 설정 불러오기
            $pg = new PG();
            $pgConf = gd_pgs();

            // 기본 값을 이니시스로
            $checkConf = false;
            if (empty($pgConf['pgName']) === true) {
                $pgConf['pgName'] = 'kcp';
                $checkConf = true;
            }

            // pg Code
            $pgConf['pgCode'] = $pgConf['pgName'];

            // _GET 으로 pgMode가 있는 경우
            if (Request::get()->has('pgMode')) {
                $pgConf['pgCode'] = StringUtils::xssClean(Request::get()->get('pgMode'));
                if ($pgConf['pgName'] !== $pgConf['pgCode'] || $checkConf === true) {
                    // 기본값 설정
                    $pgCode = $pgConf['pgCode'];
                    unset($pgConf);
                    $pgConf['pgName'] = $pgConf['pgCode'] = $pgCode;
                }
            }

            // 설정값에 기본 값을 채우기
            $pgConf = $pg->setDefaultPgData($pgConf);

            // prefix 값이 있는지 체크
            $tmp = gd_policy('pgPrefix.' . $pgConf['pgCode']);
            if (empty($tmp) === false && empty($tmp['pgId']) === false) {
                $pgApprovalId = $tmp['pgId'];
                $pgConf['pgId'] =$pgApprovalId;
                $pgConf['pgApprovalSetting'] = 'y';
            }
            unset($tmp);

            // 현금영수증 신청 기간
            if (empty($pgConf['cashReceiptPeriod']) === true || $pgConf['cashReceiptPeriod'] > 5) {
                $pgConf['cashReceiptPeriod'] = 3;
            }

            // --- 결제수단 설정 config 불러오기
            $settle = gd_policy('order.settleKind');

            // @formatter:off
            $checked= [];
            $checked['installmentFl'][$pgConf['installmentFl']]=
            $checked['noInterestFl'][$pgConf['noInterestFl']]=
            $checked['pgSkinGb'][$pgConf['pgSkinGb']]=
            $checked['escrowFl'][$pgConf['escrowFl']]=
            $checked['cashReceiptFl'][$pgConf['cashReceiptFl']]=
            $checked['cashReceiptAboveFl'][$pgConf['cashReceiptAboveFl']]=
            $checked['cashReceiptAutoFl'][$pgConf['cashReceiptAutoFl']]=
            $checked['eggDisplayFl'][$pgConf['eggDisplayFl']]=
            $checked['eggDisplayBannerFl'][$pgConf['eggDisplayBannerFl']]=
            $checked['taxFl'][$pgConf['taxFl']]=
            $checked['testFl'][$pgConf['testFl']]=
            $checked['pc'][$settle['pc']['useFl']]=
            $checked['pb'][$settle['pb']['useFl']]=
            $checked['pv'][$settle['pv']['useFl']]=
            $checked['ph'][$settle['ph']['useFl']]=
            $checked['ec'][$settle['ec']['useFl']]=
            $checked['eb'][$settle['eb']['useFl']]=
            $checked['ev'][$settle['ev']['useFl']]= 'checked="checked"';
            // @formatter:on

            // 할부개월수 설정
            $tmpPeroid = explode(':', $pgConf['installmentPeroid']);
            foreach ($tmpPeroid as $key => $val) {
                $checked['installmentPeroid'][$val] = 'checked="checked"';
            }

            // PG 중앙화에 따른 결제 수단 체크
            $disabled = [];
            if (isset($pgConf['pgAutoSetting']) === true) {
                if ($pgConf['pgAutoSetting'] === 'y') {
                    $disabled['pc'][gd_isset($pgConf['disabledSettleKind']['pc'], 'n')]=
                    $disabled['pb'][gd_isset($pgConf['disabledSettleKind']['pb'], 'n')]=
                    $disabled['pv'][gd_isset($pgConf['disabledSettleKind']['pv'], 'n')]=
                    $disabled['ph'][gd_isset($pgConf['disabledSettleKind']['ph'], 'n')]=
                    $disabled['ec'][gd_isset($pgConf['disabledSettleKind']['ec'], 'n')]=
                    $disabled['eb'][gd_isset($pgConf['disabledSettleKind']['eb'], 'n')]=
                    $disabled['ev'][gd_isset($pgConf['disabledSettleKind']['ev'], 'n')]= 'disabled="disabled"';
                }
            } else {
                $pgConf['pgAutoSetting'] = 'n';
            }

            // 쇼핑몰 도메인 설정 여부
            $isGodomallDomain = true;
            if (empty(Globals::get('gMall.mallDomain')) === false) {
                $isGodomallDomain = GodoUtils::isGodomallDomain(Globals::get('gMall.mallDomain'));
            }

            // 가상계좌 입금내역 실시간 통보 URL
            $vBankReturnUrl = 'http://' . Globals::get('gMall.mallDomain') . DS . 'payment' . DS . $pgConf['pgCode'] . '/pg_vbank_return.php';

            if ($pgConf['pgName'] == 'nicepay') {
                $pgConf['pgCancelPassword'] = \Encryptor::decrypt($pgConf['pgCancelPassword']);
            }
        } catch (\Exception $e) {
            // echo ($e->getMessage());
        }

        // --- 관리자 디자인 템플릿
        $this->getView()->setDefine('layoutPgContent', Request::getDirectoryUri() . '/settle_pg_' . $pgConf['pgName'] . '.php');

        $this->setData('gPg', Globals::get('gPg'));
        $this->setData('pgNm', Globals::get('gPg.' . $pgConf['pgCode']));
        $this->setData('settle', $settle);
        $this->setData('pgApprovalId', gd_isset($pgApprovalId));
        $this->setData('data', gd_htmlspecialchars($pgConf));
        $this->setData('pgNointerest', $pgCodeConfig->getPgNointerest()[$pgConf['pgCode']]);
        $this->setData('pgPrefix', $pgCodeConfig->getPgPrefix()[$pgConf['pgCode']]);
        $this->setData('pgPeriod', $periodCodeConfig->getPeriod());
        $this->setData('eggBannerDefault', $eggCodeConfig->getEggBannerDefault());
        $this->setData('checked', $checked);
        $this->setData('disabled', $disabled);
        $this->setData('cashReceiptPeriod', $cashReceipt::CASH_RECEIPT_PERIOD);
        $this->setData('isGodomallDomain', $isGodomallDomain);
        $this->setData('vBankReturnUrl', $vBankReturnUrl);
    }
}
