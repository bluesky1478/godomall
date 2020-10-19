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
namespace Bundle\Controller\Admin\Order;

use Exception;
use Framework\Utility\ArrayUtils;
use Globals;
use App;

/**
 * 현금영수증 개별 발급 요청 페이지
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class CashReceiptRegisterController extends \Controller\Admin\Controller
{

    /**
     * index
     *
     * @throws \Exception
     */
    public function index()
    {
        try {
            // --- 메뉴 설정
            $this->callMenu('order', 'cashReceipt', 'register');

            // --- PG 설정 불러오기
            $pgConf = gd_pgs();
            if (empty($pgConf['pgName'])) {
                $pgConf['pgNm'] = '';
            } else {
                $pgConf['pgNm'] = Globals::get('gPg.' . $pgConf['pgName']);
            }

            // --- 상품 과세 / 비과세 설정 config 불러오기
            $tax = gd_policy('goods.tax');

            // --- 메일도메인
            $emailDomain = gd_array_change_key_value(gd_code('01004'));
            $emailDomain = array_merge(['self' => __('직접입력')], $emailDomain);

            // --- 관리자 디자인 템플릿
            $this->setData('pgConf', $pgConf);
            $this->setData('tax', $tax);
            $this->setData('emailDomain', $emailDomain);

        } catch (Exception $e) {
            // echo ($e->getMessage());
        }
    }
}
