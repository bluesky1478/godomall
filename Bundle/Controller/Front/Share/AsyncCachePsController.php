<?php
/**
 * This is commercial software, only users who have purchased a valid license
 * and accept to the terms of the License Agreement can install and use this
 * program.
 *
 * Do not edit or add to this file if you wish to upgrade Enamoo S5 to newer
 * versions in the future.
 *
 * @copyright Copyright (c) 2015 GodoSoft.
 * @link      http://www.godo.co.kr
 */

namespace Bundle\Controller\Front\Share;

/**
 * Class AsyncCacheController
 *
 * @package Bundle\Controller\Front\Share\proc
 * @author  Jong-tae Ahn <qnibus@godo.co.kr>
 */
class AsyncCachePsController extends \Controller\Front\SimpleController
{
    /**
     * @inheritdoc
     */
    public function index()
    {
        if (\Request::isAjax()) {
            // initialized
            $data = null;

            switch (\Request::request()->get('mode')) {
                case 'memberInfo':
                    $data = $this->_getMemberInfo();
                    break;

                case 'stockInfo':
                    $data = $this->_getStockInfo();
                    break;

                default:
                    $data = [''];
                    break;
            }

            $this->json($data);
        } else {
            exit;
        }
    }

    /**
     * _getMemberInfo
     * GNB 로그인/로그아웃 영역
     *
     * @return mixed
     */
    protected function _getMemberInfo()
    {
        // 로그인 세션 정보
        $sessionMember = \Session::get('member');
        unset($sessionMember['memPw']);
        $data['member'] = $sessionMember;

        // 비회원 세션 정보
        $data['guest'] = \Session::get('guest');

        // 최근 본 상품 정보
//        $goods = \App::load('\\Component\\Goods\\Goods');
//        if (Cookie::has('todayGoodsNo')) {
//            $todayGoodsNo = json_decode(Cookie::get('todayGoodsNo'));
//            $todayGoodsNo = implode(INT_DIVISION, $todayGoodsNo);
//
//            // 최근 본 상품 진열
//            $goodsData = $goods->goodsDataDisplay('goods', $todayGoodsNo, null, gd_isset($getValue['sort'], 'sort asc'));
//        }
//        $jsonData['todayGoods'] = $goodsData;

        //카트 갯수
//        $cart = \App::load('\\Component\\Cart\\Cart');
//        $jsonData['cart']['cnt'] = $cart->getCartGoodsCnt();

        return $data;
    }

    protected function _getStockInfo()
    {
        // 회원정보
        $data = $this->_getMemberInfo();

        // 상품 구매가능여부 추출
        $arrBind = [];
        $req = \Request::request()->all();
        $this->db = \App::load('DB');
        $goodsSellFl = 'goodsSellFl';
        if (\Request::isMobile()) {
            $goodsSellFl = 'goodsSellMobileFl';
        }
        $strSQL = 'SELECT cateCd, payLimitFl, optionFl, stockFl, minOrderCnt, goodsPermission, goodsPermissionGroup, goodsPermissionPriceStringFl, goodsPriceString, goodsPermissionPriceString, salesStartYmd, salesEndYmd, totalStock, ( if (g.soldOutFl = \'y\' , \'y\', if (g.stockFl = \'y\' AND g.totalStock <= 0, \'y\', \'n\') ) ) as soldOut, ( if (g.' . $goodsSellFl . ' = \'y\', g.' . $goodsSellFl . ', \'n\')  ) as orderPossible FROM ' . DB_GOODS . ' as g WHERE g.delFl = \'n\' AND g.applyFl = \'y\' AND g.goodsNo = ?';
        $this->db->bind_param_push($arrBind, 's', $req['goodsNo']);
        $stockData = $this->db->query_fetch($strSQL, $arrBind, false);
        unset($arrBind);

        // 옵션 체크, 옵션 사용인 경우
        if ($stockData['optionFl'] !== 'y') {
            if($stockData['stockFl'] =='y' && $stockData['minOrderCnt'] > $stockData['totalStock']) {
                $stockData['orderPossible'] = 'n';
            }
        }

        // 결제수한제단 체크 (부하시 제거해도 됨)
        if ($stockData['payLimitFl'] == 'y' && gd_isset($stockData['payLimit'])) {
            // 회원 그룹 설정
            $memberGroup = \App::load('\\Component\\Member\\MemberGroup');
            $stockData['memberDc'] = $memberGroup->getGroupForSale($req['goodsNo'], $stockData['cateCd']);
            $stockData['memberDc']['settleGb'] = Util::matchSettleGbDataToString($stockData['memberDc']['settleGb']);
            $payLimit = array_intersect($stockData['memberDc']['settleGb'], explode(STR_DIVISION, $stockData['payLimit']));

            if(count($payLimit) == 0) {
                $stockData['orderPossible'] = 'n';
            }
        }

        // 구매 가능여부 체크
        if ($stockData['soldOut'] == 'y') {
            $stockData['orderPossible'] = 'n';
        }

        //구매불가 대체 문구 관련
        if($stockData['goodsPermission'] !='all' && (($stockData['goodsPermission'] =='member'  && gd_is_login() === false) || ($stockData['goodsPermission'] =='group'  && !in_array(\Session::get('member.groupSno'),explode(INT_DIVISION,$stockData['goodsPermissionGroup']))))) {
            if($stockData['goodsPermissionPriceStringFl'] =='y' ) $stockData['goodsPriceString'] = $stockData['goodsPermissionPriceString'];
            $stockData['orderPossible'] = 'n';
        }

        if (((gd_isset($stockData['salesStartYmd']) != '' && gd_isset( $stockData['salesEndYmd']) != '') && ($stockData['salesStartYmd'] != '0000-00-00 00:00:00' && $stockData['salesEndYmd'] != '0000-00-00 00:00:00')) && (strtotime($stockData['salesStartYmd']) > time() || strtotime($stockData['salesEndYmd']) < time())) {
            $stockData['orderPossible'] = 'n';
        }

        // 재고 없는 경우 캐시 재 생성
        // 무한정 판매가 아니고, 상품 재고가 0, 품절 상태 품절이 수동인 경우 false
        if ($stockData['orderPossible']  == 'n') {
            $data['result'] = false;
        } else {
            $data['result'] = true;
        }

        // 상품정보
        $data['goods'] = $stockData;

        return $data;
    }
}