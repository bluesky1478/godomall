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
namespace Bundle\Controller\Admin\Member;

use App;

class TestMemberOrderPriceController extends \Controller\Admin\Controller
{

    /**
     * 회원 리스트 페이지
     * [관리자 모드] 회원 리스트 페이지
     *
     * @author sunny, artherot
     * @version 1.0
     * @since 1.0
     * @copyright ⓒ 2016, NHN godo: Corp.
     */
    public function index()
    {

        // --- 모듈 호출
        $db = App::getInstance('DB');
        $order = \App::load('\\Component\\Order\\OrderAdmin');

        // --- 목록
        $strSQL = 'SELECT memNo FROM ' . DB_MEMBER;
        $result = $db->query($strSQL);
        while ($data = $db->fetch($result)) {
            // 회원 구매금액 갱신
            echo $data['memNo'] . '<br>';
            $order->setOrderPriceMember(null, $data['memNo']);

            // 마지막 주문일자 갱신
            $strSQL = 'SELECT regDt FROM ' . DB_ORDER . ' WHERE memNo = ' . $data['memNo'] . ' ORDER BY orderNo DESC LIMIT 0,1';
            $getData = $db->query_fetch($strSQL, null, false);

            if (empty($getData) === false) {
                $db->set_update_db_query(DB_MEMBER, 'lastSaleDt = \'' . $getData['regDt'] . '\'', 'memNo = \'' . $db->escape($data['memNo']) . '\'');
            }
        }
    }
}
