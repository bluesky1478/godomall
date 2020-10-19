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
namespace Bundle\Component\Cart;

use Component\Database\DBTableField;
use Component\Validator\Validator;
use Component\Delivery\OverseasDelivery;
use App;
use Framework\Utility\ArrayUtils;
use Exception;
use Session;

/**
 * 수기주문 전용 장바구니 class
 * 세션 아이디가 없는 경우
 *
 * @package Bundle\Component\Cart
 * @author  Jong-tae Ahn <qnibus@godo.co.kr>
 */
class CartAdmin extends \Component\Cart\Cart
{
    /**
     * 생성자
     *
     * @param mixed $memberNo 회원번호
     * @param boolean $useRealCart 수기주문 시 회원장바구니 상품 적용  ---> admin 에서의 db_cart 사용을 위한 flag 값
     */
    public function __construct($memberNo = 0, $useRealCart=false, $mallSno=null)
    {
        parent::__construct();

        // !중요! 장바구니 구현시 사용되는 테이블 명
        if($useRealCart === true){
            //관리모드 수기주문 - 회원 장바구니 추가시 노출되는 상품을 위해 DB_CART 사용
            $this->tableName = DB_CART;

            //실제 테이블 사용시
            $this->useRealCart = true;
        }
        else {
            //상품추가시엔 DB_CART_WRITE 사용
            $this->tableName = DB_CART_WRITE;
        }

        // 해외배송 기본 정책
        if($mallSno !== null && $mallSno > DEFAULT_MALL_NUMBER){
            $this->isAdminGlobal = true;

            $overseasDelivery = new OverseasDelivery();
            $this->overseasDeliveryPolicy = $overseasDelivery->getBasicData($mallSno, 'mallSno');
        }


        // 수기주문 체크
        $this->isWrite = true;

        // 정해진 회원번호로 처리 (null인 경우 로그인한 회원의 번호로 처리한다)
        if ($memberNo > 0) {
            $this->isLogin = true;

            // 회원 DB 추출
            $member = App::load('\\Component\\Member\\Member');
            $memberInfo = $member->getMember($memberNo, 'memNo');

            // 추출한 데이터로 값 설정
            $this->members['memNo'] = $memberInfo['memNo'];
            $this->members['adultFl'] = $memberInfo['adultFl'];
            $this->members['groupSno'] = $memberInfo['groupSno'];

            // 회원설정
            $this->_memInfo = $member->getMemberInfo($memberNo);

            $this->deliveryFreeByMileage = $this->_memInfo['deliveryFree'];
        } else {
            $this->isLogin = false;

            $this->members = [
                'memNo'    => 0,
                'adultFl'  => 'n',
                'groupSno' => 0,
            ];

            // 회원설정
            $this->_memInfo = false;

            $this->deliveryFreeByMileage = '';
        }
    }

    /**
     * 수기주문용 장바구니 데이터 처리
     *
     * @param array $getData 상품배열
     *
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public function saveInfoCartAdmin($getData)
    {
        foreach ($getData['goodsNo'] as $k => $v) {
            $setData['scmNo'] = $getData['scmNo'][$k];
            $setData['deliveryCollectFl'] = $getData['deliveryCollectFl'][$k];
            $setData['deliveryMethodFl'] = $getData['deliveryMethodFl'][$k];
            $setData['goodsNo'][] = $getData['goodsNo'][$k];
            $setData['goodsCnt'][] = $getData['goodsCnt'][$k];
            $setData['optionSno'][] = $getData['optionSno'][$k];
            if (gd_isset($getData['optionText'][$k])) {
                $setData['optionText'][] = $getData['optionText'][$k];
            }
            if (gd_isset($getData['addGoodsNo'][$k])) {
                $setData['addGoodsNo'][] = $getData['addGoodsNo'][$k];
                $setData['addGoodsCnt'][] = $getData['addGoodsCnt'][$k];
            }
            $setData['set_total_price'] = $getData['setTotalPrice'][$k]+$getData['addGoodsPriceSum'][$k];
            $setData['useBundleGoods'] = $getData['useBundleGoods'];
            $this->saveInfoCart($setData);
            unset($setData);
        }
    }

    /**
     * 수기주문용 장바구니 데이터 처리 - 회원 장바구니 상품 추가
     *
     * @param array $getData 상품배열
     *
     * @author <bumyul2000@godo.co.kr>
     */
    public function saveInfoMemberCartAdmin($getData)
    {
        $this->isWriteMemberCartAdd = true;

        $setData = array();
        $setData['preRealCartSno'] = array_column($getData, 'sno');
        $setData['scmNo'] = array_column($getData, 'scmNo');
        $setData['deliveryCollectFl'] = $getData[0]['deliveryCollectFl'];
        $setData['goodsCnt'] = array_column($getData, 'goodsCnt');
        $setData['goodsNo'] = array_column($getData, 'goodsNo');
        $setData['optionSno'] = array_column($getData, 'optionSno');
        $setData['useBundleGoods'] = array_column($getData, 'useBundleGoods');

        foreach($getData as $key => $value) {
            if (count($value['optionText']) > 0) {
                foreach($value['optionText'] as $optionTextKey => $optionTextValue){
                    $setData['optionText'][$key][$optionTextValue['optionSno']] = $optionTextValue['optionValue'];
                }
            }
            if (count($value['addGoods']) > 0) {
                $setData['addGoodsNo'][$key] = array_column($value['addGoods'], 'addGoodsNo');
                $setData['addGoodsCnt'][$key] = array_column($value['addGoods'], 'addGoodsCnt');
            }
            if (count($value['coupon']) > 0) {
                $couponArr = array();
                foreach($value['coupon'] as $couponKey => $couponValue){
                    $couponArr[] = $couponKey;
                }
                $setData['couponApplyNo'][$key] = implode(INT_DIVISION, $couponArr);
            }
            $setData['set_total_price'][$key] = $value['price']['goodsPriceSubtotal'];
        }

        $this->saveInfoCart($setData);

        unset($setData);

        return $this->isWriteMemberUseCouponCartSnoArr;
    }

    public function changeSelfOrderWriteOption($getData)
    {
        // 상품 텍스트 옵션
        if (gd_isset($getData['optionText'][0]) && empty($getData['optionText'][0]) === false) {
            $optionText = ArrayUtils::removeEmpty($getData['optionText'][0]);
            $optionText = json_encode($optionText, JSON_UNESCAPED_UNICODE);
        } else {
            $optionText = '';
        }

        // 추가 상품
        if (gd_isset($getData['addGoodsNo'][0]) && empty($getData['addGoodsNo'][0]) === false) {
            $addGoodsNo = ArrayUtils::removeEmpty($getData['addGoodsNo'][0]);
            $addGoodsCnt = ArrayUtils::removeEmpty($getData['addGoodsCnt'][0]);
            $addGoodsNo = json_encode($addGoodsNo);
            $addGoodsCnt = json_encode($addGoodsCnt);
        }
        else {
            $addGoodsNo = $addGoodsCnt = '';
        }

        $arrBind = [
            'iisss',
            $getData['optionSno'][0],
            $getData['goodsCnt'][0],
            $addGoodsNo,
            $addGoodsCnt,
            $optionText
        ];
        $arrStr = 'optionSno = ?, goodsCnt = ?, addGoodsNo = ?, addGoodsCnt = ?, optionText = ?';
        if (empty($getData['deliveryCollectFl']) === false && empty($getData['deliveryMethodFl']) === false) {
            $arrBind[0] .= 'ss';
            $arrBind[] = $getData['deliveryCollectFl'];
            $arrBind[] = $getData['deliveryMethodFl'];
            $arrStr .= ', deliveryCollectFl = ?, deliveryMethodFl = ?';
        }
        $arrBind[0] .= 'i';
        $arrBind[] = $getData['cartSno'];

        $this->db->set_update_db($this->tableName, $arrStr, 'sno = ?', $arrBind);

        if (($getData['goodsDeliveryFl'] == 'y' || ($getData['goodsDeliveryFl'] != 'y' && $getData['sameGoodsDeliveryFl'] == 'y')) && empty($getData['deliveryCollectFl']) === false && empty($getData['deliveryMethodFl']) === false) {
            $arrData['deliveryCollectFl'] = gd_isset($getData['deliveryCollectFl']);
            $arrData['deliveryMethodFl'] = gd_isset($getData['deliveryMethodFl']);
            $cartInfo = $this->getCartInfo($getData['sno'], 'mallSno, siteKey, memNo, directCart, goodsNo');

            $arrBind = $this->db->get_binding(DBTableField::getBindField('tableCart', ['deliveryCollectFl', 'deliveryMethodFl']), $arrData, 'update');
            $strWhere = 'mallSno = ? AND siteKey = ? AND memNo = ? AND directCart = ? AND goodsNo = ?';
            $this->db->bind_param_push($arrBind['bind'], 'i', $cartInfo['mallSno']);
            $this->db->bind_param_push($arrBind['bind'], 's', $cartInfo['siteKey']);
            $this->db->bind_param_push($arrBind['bind'], 'i', $cartInfo['memNo']);
            $this->db->bind_param_push($arrBind['bind'], 's', $cartInfo['directCart']);
            $this->db->bind_param_push($arrBind['bind'], 'i', $cartInfo['goodsNo']);

            $this->db->set_update_db($this->tableName, $arrBind['param'], $strWhere, $arrBind['bind']);
        }
    }

    /**
     * goodsViewBenefitOrder
     *
     * @param $getData
     *
     * @return array
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public function goodsViewBenefitOrder($getData)
    {
        //회원 아이디가 있는 경우 해당 정보로 세팅
        if ($getData['memNo']) {
            $member = \App::Load(\Component\Member\Member::class);
            $this->_memInfo = $member->getMemberInfo($getData['memNo']);
        } else {
            $this->_memInfo = null;
        }

        return $this->goodsViewBenefit($getData);
    }

    public function resetMemberCouponOrderWrite()
    {
        $coupon = \App::load('\\Component\\Coupon\\Coupon');
        $coupon->resetMemberCouponStateOrderWrite($this->members['memNo']);
    }

    public function updateOrderWriteRealCouponData($realCartSno, $realCartUseCouponNo)
    {
        if((int)$realCartSno > 0 && trim($realCartUseCouponNo) !== ''){
            $strSQL = "SELECT memberCouponNo FROM ".$this->tableName." WHERE sno = ".$realCartSno." AND memNo = ".$this->members['memNo'] . " LIMIT 1";
            $result = $this->db->query($strSQL);
            $newUpdateCouponNoArr = array();
            if($result){
                while ($cartData = $this->db->fetch($result)) {
                    if($cartData['memberCouponNo']){
                        $ori_memberCouponNoArr = $use_memberCouponNoArr = array();

                        $ori_memberCouponNoArr = explode(INT_DIVISION, $cartData['memberCouponNo']);
                        $use_memberCouponNoArr = explode(INT_DIVISION, $realCartUseCouponNo);
                        $newUpdateCouponNoArr = array_diff($ori_memberCouponNoArr, $use_memberCouponNoArr);
                    }
                }
            }

            $arrBind = [];
            $this->db->bind_param_push($arrBind, 's', implode(INT_DIVISION, $newUpdateCouponNoArr));
            $this->db->bind_param_push($arrBind, 'i', $realCartSno);
            $this->db->bind_param_push($arrBind, 'i', $this->members['memNo']);
            $this->db->set_update_db($this->tableName, 'memberCouponNo = ?', 'sno = ? AND memNo = ?', $arrBind, false);
        }
    }

    /*
     * 회원구분변경, 회원 변경에 따른 cart write 회원 번호 변경
     */
    public function updateCartWriteMemNo()
    {
        $arrBind = [];
        $this->db->bind_param_push($arrBind, 'i', $this->members['memNo']);
        $this->db->bind_param_push($arrBind, 's', '');
        $this->db->bind_param_push($arrBind, 's', $this->siteKey);
        $this->db->set_update_db($this->tableName, 'memNo = ? , memberCouponNo = ?', 'siteKey = ?', $arrBind, false);
    }

    //주문하지 못할 cart 건 미리 삭제
    /*
    public function checkOrderImpossibleClear()
    {
        if ($this->isLogin === true) {
            $arrWhere[] = 'c.memNo = \'' . $this->members['memNo'] . '\' AND  c.siteKey = \'' . $this->siteKey . '\'';
        }
        else {
            $arrWhere[] = 'c.siteKey = \'' . $this->siteKey . '\'';
        }

        $arrExclude['cart'] = [];
        $arrExclude['option'] = [
            'goodsNo',
            'optionNo',
        ];
        $arrExclude['addOptionName'] = [
            'goodsNo',
            'optionCd',
            'mustFl',
        ];
        $arrExclude['addOptionValue'] = [
            'goodsNo',
            'optionCd',
        ];
        $arrInclude['goods'] = [
            'goodsNm',
            'commission',
            'scmNo',
            'purchaseNo',
            'goodsCd',
            'cateCd',
            'goodsOpenDt',
            'goodsState',
            'imageStorage',
            'imagePath',
            'brandCd',
            'makerNm',
            'originNm',
            'goodsModelNo',
            'goodsPermission',
            'goodsPermissionGroup',
            'onlyAdultFl',
            'taxFreeFl',
            'taxPercent',
            'goodsWeight',
            'totalStock',
            'stockFl',
            'soldOutFl',
            'salesUnit',
            'minOrderCnt',
            'maxOrderCnt',
            'salesStartYmd',
            'salesEndYmd',
            'mileageFl',
            'mileageGoods',
            'mileageGoodsUnit',
            'goodsDiscountFl',
            'goodsDiscount',
            'goodsDiscountUnit',
            'payLimitFl',
            'payLimit',
            'goodsPriceString',
            'goodsPrice',
            'fixedPrice',
            'costPrice',
            'optionFl',
            'optionName',
            'optionTextFl',
            'addGoodsFl',
            'addGoods',
            'deliverySno',
            'delFl',
            'hscode',
            'goodsSellFl',
            'goodsSellMobileFl',
            'goodsDisplayFl',
            'goodsDisplayMobileFl'
        ];

        $arrFieldCart = DBTableField::setTableField('tableCart', null, $arrExclude['cart'], 'c');
        $arrFieldGoods = DBTableField::setTableField('tableGoods', $arrInclude['goods'], null, 'g');
        $arrFieldOption = DBTableField::setTableField('tableGoodsOption', null, $arrExclude['option'], 'go');


        // 장바구니 상품 기본 정보
        $strSQL = "SELECT c.sno,
            " . implode(', ', $arrFieldCart) . ", c.regDt,
            " . implode(', ', $arrFieldGoods) . ",
            " . implode(', ', $arrFieldOption) . "
        FROM " . $this->tableName . " c
        INNER JOIN " . DB_GOODS . " g ON c.goodsNo = g.goodsNo
        LEFT JOIN " . DB_GOODS_OPTION . " go ON c.optionSno = go.sno AND c.goodsNo = go.goodsNo
        WHERE " . implode(' AND ', $arrWhere);

        $result = $this->db->query($strSQL);
        $orderImossibleData = array();
        if($result){
            while ($data = $this->db->fetch($result)) {
                $arrBind = [];

                $data = $this->checkOrderPossible($data);
                if($data['orderPossible'] === 'n'){
                    $orderImossibleData[] = $data['orderPossibleMessage'];
                    $arrBind['param'] = 'sno = ?';
                    $this->db->bind_param_push($arrBind['bind'], 'i', $data['sno']);
                    $this->db->set_delete_db($this->tableName, $arrBind['param'], $arrBind['bind']);
                    unset($arrBind);
                }
            }
        }

        return $orderImossibleData;
    }
    */
}
