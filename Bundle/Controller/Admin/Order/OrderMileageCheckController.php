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

use App;

class OrderMileageCheckController extends \Controller\Admin\Controller
{
    /**
     * 주문 마일리지 오류 체크 처리 페이지
     * [관리자 모드] 주문 마일리지 오류 체크 처리 페이지
     *
     * @author artherot
     * @version 1.0
     * @since 1.0
     * @param array $get
     * @param array $post
     * @param array $files
     * @throws Except
     * @copyright ⓒ 2016, NHN godo: Corp.
     */
    public function index()
    {

        // --- 모듈 호출
        $strSQL = 'SELECT orderNo, totalMinusMileage, totalPlusMileage, plusGoodsMileage, plusAddMileage, plusMemberMileage, plusCouponMileage, mileageGiveExclude FROM ' . DB_ORDER . ' WHERE mileageGiveExclude != \'y\'';
        $getData = App::getInstance('DB')->query_fetch($strSQL);

        $i = 0;
        foreach ($getData as $key => $val) {
            $chkMileage1 = $val['plusGoodsMileage'] + $val['plusAddMileage'] + $val['plusMemberMileage'] + $val['plusCouponMileage'];
            $chkMileage2 = $chkMileage1 - $val['totalMinusMileage'];
            if ($chkMileage2 < 0) {
                $chkMileage2 = 0;
            }
            if ($val['mileageGiveExclude'] == 'n' && $chkMileage1 != $val['totalPlusMileage']) {
                $setData[$i]['orderNo'] = $val['orderNo'];
                $setData[$i]['totalPlusMileage'] = $chkMileage1;
                $i++;
            }
            if ($val['mileageGiveExclude'] == 'x' && $chkMileage2 != $val['totalPlusMileage']) {
                $setData[$i]['orderNo'] = $val['orderNo'];
                $setData[$i]['totalPlusMileage'] = $chkMileage2;
                $i++;
            }
        }

        /* foreach 문 안에 처리할내용이 없어서 전체 주석 처리
        if (empty($setData) === false) {
            // 주문 테이블 갱신
            foreach ($setData as $key => $val) {
                // $db->set_update_db_query(DB_ORDER, 'totalPlusMileage = \''.$val['totalPlusMileage'].'\'', 'orderNo = \''.$val['orderNo'].'\'',null, true);
            }
        } */
    }
}
