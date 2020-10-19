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

namespace Bundle\Component\Order;

use App;
use Component\Godo\NaverPayAPI;
use Component\Naver\NaverPay;
use Component\Database\DBTableField;
use Component\Deposit\Deposit;
use Component\Mileage\Mileage;
use Component\Cart\CartAdmin;
use Component\Delivery\OverseasDelivery;
use Exception;
use Framework\Utility\ArrayUtils;
use Framework\Utility\NumberUtils;
use Globals;
use Session;
use Component\Member\Manager;
use Component\Order\OrderSalesStatistics;
use Component\Sms\Code;

/**
 * 주문 재계산 class
 *
 * @author su
 */
class ReOrderCalculation
{
    /**
     * @var null|object 디비 접속
     */
    protected $db;
    protected $fieldTypes;

    public $truncPolicy;
    public $couponTruncPolicy;
    public $mileageTruncPolicy;

    //교환내 환불수단 정의
    public $exchangeRefundMethodName;

    // 마이앱 사용 여부
    public $myappUseFl;

    public $member;

    //복수배송지가 사용된 주문건인지 체크
    private $multiShippingOrderFl = false;

    // 교환에서 사용되는 부가결제 재분배 여부
    private $addPaymentDivisionFl;
    // 교환에서 사용되는 부가결제 재분배 되어야 할 금액
    private $reDivisionAddPaymentArr = [
        'add' => [
            'mileage' => [],
            'deposit' => [],
        ],
        'cancel' => [
            'mileage' => [],
            'deposit' => [],
        ],
    ];
    private $etcOrderGoodsData = [];
    private $onlyAddOrderGoodsRateArr = [];
    private $onlyCancelOrderGoodsRateArr = [];
    private $cancelDeliveryFl = [];

    /**
     * 생성자
     */
    public function __construct()
    {
        // 데이터베이스
        if (!is_object($this->db)) {
            $this->db = \App::load('DB');
        }
        $this->fieldTypes['order'] = DBTableField::getFieldTypes('tableOrder');
        $this->fieldTypes['orderGoods'] = DBTableField::getFieldTypes('tableOrderGoods');
        $this->fieldTypes['orderDelivery'] = DBTableField::getFieldTypes('tableOrderDelivery');
        $this->fieldTypes['orderCoupon'] = DBTableField::getFieldTypes('tableOrderCoupon');
        $this->fieldTypes['orderGift'] = DBTableField::getFieldTypes('tableOrderGift');
        $this->fieldTypes['orderHandle'] = DBTableField::getFieldTypes('tableOrderHandle');
        $this->fieldTypes['orderInfo'] = DBTableField::getFieldTypes('tableOrderInfo');

        $this->truncPolicy = Globals::get('gTrunc.goods');
        $this->couponTruncPolicy = Globals::get('gTrunc.coupon');
        $this->mileageTruncPolicy = Globals::get('gTrunc.mileage');
        $this->memberGroupPolicy = Globals::get('gTrunc.member_group');

        $this->exchangeRefundMethodName = [
            'bank' => '현금환불',
            'deposit' => '예치금환불',
            'mileage' => '마일리지환불',
            'etc' => '기타환불',
        ];

        $this->myappUseFl = gd_policy('myapp.config')['useMyapp'];
    }

    /**
     * getOrderData
     * 주문시 주문 정보
     * - 마일리지+쿠폰 중복 설정 저장 필요 (기본설정 > 관리정책 > 쇼핑몰 이용 설정)
     *
     * @param string $orderNo
     * @return mixed
     */
    public function getOrderData($orderNo)
    {
        $arrBind = [];

        $this->db->strField = "o.mallSno, o.memNo, o.orderGoodsNm, orderGoodsNmStandard, o.orderChannelFl, o.orderTypeFl, o.receiptFl, o.settlePrice";
        $this->db->strField .= ", o.taxSupplyPrice, o.taxVatPrice, o.taxFreePrice, o.realTaxSupplyPrice, o.realTaxVatPrice, o.realTaxFreePrice";
        $this->db->strField .= ", o.depositPolicy, o.mileagePolicy, o.statusPolicy, o.memberPolicy, o.couponPolicy";
        $this->db->strField .= ", o.useMileage, o.useDeposit, o.totalGoodsPrice, o.totalDeliveryCharge, o.totalDeliveryInsuranceFee";
        $this->db->strField .= ", o.totalGoodsDcPrice, o.totalMemberDcPrice, o.totalMemberOverlapDcPrice, o.totalCouponGoodsDcPrice, o.totalEnuriDcPrice";
        $this->db->strField .= ", o.totalCouponOrderDcPrice, o.totalCouponDeliveryDcPrice, o.totalMemberDeliveryDcPrice";
        $this->db->strField .= ", o.totalMileage, o.totalGoodsMileage, o.totalMemberMileage, o.totalCouponGoodsMileage, o.totalCouponOrderMileage";
        $this->db->strField .= ", o.orderGoodsCnt, o.multiShippingFl, o.orderStatus";
        if ($this->myappUseFl) {
            $this->db->strField .= ", o.totalMyappDcPrice";
        }
        $this->db->strWhere = 'o.orderNo = ?';
        $this->db->bind_param_push($arrBind, $this->fieldTypes['order']['orderNo'], $orderNo);

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER . ' as o ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind, false);
        unset($arrBind);

        return $getData;
    }

    /**
     * getOrderDepositMileageData
     * 현재 주문 정보
     *
     * @param string $orderNo
     * @return mixed
     */
    public function getOrderDepositMileageData($orderNo)
    {
        $arrBind = [];

        $this->db->strField = "o.mallSno, o.memNo, o.useMileage, o.useDeposit";
        $this->db->strWhere = 'o.orderNo = ?';
        $this->db->bind_param_push($arrBind, $this->fieldTypes['order']['orderNo'], $orderNo);

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER . ' as o ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind, false);
        unset($arrBind);

        return $getData;
    }

    /**
     * getOrderOriginalDepositMileageData
     * 백업 주문 정보
     *
     * @param string $orderNo
     * @param string $claimStatus
     * @return mixed
     */
    public function getOrderOriginalDepositMileageData($orderNo, $claimStatus)
    {
        $arrBind = [];

        $this->db->strField = "oo.mallSno, oo.memNo, oo.useMileage, oo.useDeposit";
        $this->db->strWhere = 'oo.orderNo = ? AND oo.claimStatus = ?';
        $this->db->bind_param_push($arrBind, $this->fieldTypes['order']['orderNo'], $orderNo);
        $this->db->bind_param_push($arrBind, 's', $claimStatus);

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_ORIGINAL . ' as oo ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind, false);
        unset($arrBind);

        return $getData;
    }

    /**
     * getOrderInfoData
     *
     * @param $orderNo
     * @param $orderInfoSno
     *
     * @return mixed
     */
    public function getOrderInfoData($orderNo, $orderInfoSno=0)
    {
        $arrBind = [];

        $this->db->strField = "receiverName, receiverCountryCode, receiverPhonePrefixCode, receiverPhonePrefix, receiverPhone, receiverCellPhonePrefixCode";
        $this->db->strField .= ", receiverCellPhonePrefix, receiverCellPhone, receiverZipcode, receiverZonecode, receiverCountry, receiverState, receiverCity";
        $this->db->strField .= ", receiverAddress, receiverAddressSub, sno, orderInfoCd";
        $this->db->strWhere = 'orderNo = ?';
        $this->db->bind_param_push($arrBind, $this->fieldTypes['orderInfo']['orderNo'], $orderNo);
        if($orderInfoSno > 0){
            $this->db->strWhere .= ' AND sno = ?';
            $this->db->bind_param_push($arrBind, 'i', $orderInfoSno);
        }
        // 복수배송지 사용 주문건이 아닐시 orderInfoCd 가 default 인 메인배송지만 뽑아낸다.
        if($this->multiShippingOrderFl !== true){
            $this->db->strWhere .= ' AND orderInfoCd = ?';
            $this->db->bind_param_push($arrBind, 'i', 1);
        }

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_INFO . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind, false);
        unset($arrBind);

        return $getData;
    }

    /**
     * @todo 주문상품 상태에 따른 select 추가 필요 - 취소는 입금대기 상태만 가져옴 / 교환은 입금대기는 안됨
     * getOrderGoodsData
     * 주문시 주문상품 정보
     *
     * @param string $orderNo 주문번호
     * @param string $orderGoodsSno 주문상품번호
     * @param string $orderBy 정렬순서
     *
     * @return array $getData
     */
    public function getOrderGoodsData($orderNo, $orderGoodsSno = null, $orderBy = null)
    {
        $arrBind = [];

        $this->db->strField = "og.*";
        $this->db->strWhere = 'og.orderNo = ?';
        if ($orderGoodsSno) {
            $this->db->strWhere .= ' AND og.sno = ?';
        }
        $this->db->bind_param_push($arrBind, $this->fieldTypes['orderGoods']['orderNo'], $orderNo);
        if ($orderGoodsSno) {
            $this->db->bind_param_push($arrBind, 'i', $orderGoodsSno);
        }
        if($orderBy === null){
            $orderBy = 'og.regDt asc';
        }
        $this->db->strOrder = $orderBy;


        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_GOODS . ' as og ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);
        unset($arrBind);

        return $getData;
    }

    /**
     * getOrderGoodsStatusData
     *
     * @param $orderNo
     * @return mixed
     */
    public function getOrderGoodsStatusData($orderNo)
    {
        $arrBind = [];

        $this->db->strField = "og.sno, og.goodsNm, og.goodsNmStandard, og.orderStatus, og.goodsType, og.goodsNo, og.goodsCnt, og.minusStockFl, og.minusRestoreStockFl";
        $this->db->strWhere = 'og.orderNo = ?';
        $this->db->bind_param_push($arrBind, $this->fieldTypes['orderGoods']['orderNo'], $orderNo);
        $this->db->strOrder = 'og.regDt, og.orderCd asc';

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_GOODS . ' as og ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);
        unset($arrBind);

        return $getData;
    }

    /**
     * getOrderGoodsStatusOriginalData
     *
     * @param $orderNo
     * @return mixed
     */
    public function getOrderGoodsStatusOriginalData($orderNo)
    {
        $arrBind = [];

        $this->db->strField = "ogo.sno, ogo.orderStatus";
        $this->db->strWhere = 'ogo.orderNo = ?';
        $this->db->bind_param_push($arrBind, $this->fieldTypes['orderGoods']['orderNo'], $orderNo);
        $this->db->strOrder = 'ogo.regDt asc';

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_GOODS_ORIGINAL . ' as ogo ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);
        unset($arrBind);

        return $getData;
    }

    /**
     * getOrderGoodsPriceSumData
     * 주문시 주문상품 금액 합계 정보
     *
     * @param string $orderNo 주문번호
     *
     * @return array $getData
     */
    public function getOrderGoodsPriceSumData($orderNo)
    {
        $arrBind = [];

        $this->db->strField = "sum(og.goodsPrice) as goodsPrice, sum(og.taxSupplyGoodsPrice) as taxSupplyGoodsPrice, sum(og.taxVatGoodsPrice) as taxVatGoodsPrice, sum(og.taxFreeGoodsPrice) as taxFreeGoodsPrice";
        $this->db->strField .= ", sum(og.optionPrice) as optionPrice, sum(og.optionTextPrice) as optionTextPrice, sum(og.goodsDcPrice) as goodsDcPrice, sum(og.goodsMileage) as goodsMileage";
        if ($this->myappUseFl) {
            $this->db->strField .= ", sum(og.myappDcPrice) as myappDcPrice";
        }
        $this->db->strWhere = 'og.orderNo = ?';
        $this->db->bind_param_push($arrBind, $this->fieldTypes['orderGoods']['orderNo'], $orderNo);
        $this->db->strOrder = 'og.regDt asc';

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_GOODS . ' as og ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);
        unset($arrBind);

        return $getData;
    }

    /**
     * getOrderDeliveryData
     * 주문시 주문배송비 정보
     *
     * @param $orderNo
     * @param $sno
     */
    public function getOrderDeliveryData($orderNo, $sno = null)
    {
        $arrBind = [];

        $this->db->strField = "od.*";
        $this->db->strWhere = 'od.orderNo = ?';
        $this->db->bind_param_push($arrBind, $this->fieldTypes['orderDelivery']['orderNo'], $orderNo);
        if($sno !== null){
            $this->db->strWhere .= ' AND od.sno = ?';
            $this->db->bind_param_push($arrBind, 'i', $sno);
        }
        $this->db->strOrder = 'od.regDt asc';

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_DELIVERY . ' as od ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);
        unset($arrBind);

        return $getData;
    }

    /**
     * getOrderDeliveryPriceSumData
     * 주문시 주문배송비 금액 합계 정보
     *
     * @param $orderNo
     */
    public function getOrderDeliveryPriceSumData($orderNo)
    {
        $arrBind = [];

        $this->db->strField = "sum(od.deliveryCharge) as deliveryCharge, sum(od.taxSupplyDeliveryCharge) as taxSupplyDeliveryCharge, sum(od.taxVatDeliveryCharge) as taxVatDeliveryCharge, sum(od.taxFreeDeliveryCharge) as taxFreeDeliveryCharge";
        $this->db->strField .= ", sum(od.deliveryPolicyCharge) as deliveryPolicyCharge, sum(od.deliveryAreaCharge) as deliveryAreaCharge, sum(od.deliveryInsuranceFee) as deliveryInsuranceFee";
        $this->db->strWhere = 'od.orderNo = ?';
        $this->db->bind_param_push($arrBind, $this->fieldTypes['orderDelivery']['orderNo'], $orderNo);
        $this->db->strOrder = 'od.regDt asc';

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_DELIVERY . ' as od ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);
        unset($arrBind);

        return $getData;
    }

    /**
     * 현재 주문 쿠폰 정보
     * es_orderCoupon
     *
     * @param $orderNo
     * @param $statusMode
     *
     */
    public function getOrderCouponData($orderNo, $statusMode = '')
    {
        $arrBind = [];

        $this->db->strField = "oc.memberCouponNo, oc.couponNm, oc.couponUseType"; // 쿠폰 정보
        $this->db->strField .= ", oc.couponPrice, oc.couponMileage, oc.minusCouponFl, oc.plusCouponFl"; // 쿠폰 금액 / 지급 여부
        $this->db->strField .= ", oc.minusRestoreCouponFl, oc.plusRestoreCouponFl"; // 쿠폰 복원 여부
        $this->db->strWhere = 'oc.orderNo = ?';
        $this->db->bind_param_push($arrBind, $this->fieldTypes['orderCoupon']['orderNo'], $orderNo);
        $this->db->strOrder = 'oc.regDt asc';

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_COUPON . ' as oc ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);
        unset($arrBind);

        return $getData;
    }

    /**
     * 현재 주문 클레임 정보
     * es_orderHandle
     *
     * @param string $orderNo
     * @param string $orderBy
     * @param string $groupBy
     * @param integer $handleSno
     * @param string $handleMode
     * @param integer $handleGroupCd
     *
     */
    public function getOrderHandleData($orderNo, $orderBy = 'oh.regDt asc', $groupBy = '', $handleSno = 0, $handleMode = '', $handleGroupCd = 0)
    {
        $arrBind = [];

        $this->db->strField = "oh.*"; // 클레임 정보
        $this->db->strWhere = 'oh.orderNo = ?';
        $this->db->bind_param_push($arrBind, $this->fieldTypes['orderHandle']['orderNo'], $orderNo);
        if((int)$handleSno > 0){
            $this->db->strWhere .= ' AND oh.sno = ?';
            $this->db->bind_param_push($arrBind, 'i', $handleSno);
        }
        if(trim($handleMode) !== ''){
            $this->db->strWhere .= ' AND oh.handleMode = ?';
            $this->db->bind_param_push($arrBind, 's', $handleMode);
        }
        if((int)$handleGroupCd > 0){
            $this->db->strWhere .= ' AND oh.handleGroupCd = ?';
            $this->db->bind_param_push($arrBind, 'i', $handleGroupCd);
        }
        $this->db->strOrder = $orderBy;
        if ($groupBy) {
            $this->db->strGroup = $groupBy;
        }

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_HANDLE . ' as oh ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);
        if (count($getData) > 1) {
            foreach ($getData as $key => $value) {
                if (gd_str_length($value['refundAccountNumber']) > 50) {
                    $getData[$key]['refundAccountNumber'] = \Encryptor::decrypt($value['refundAccountNumber']);
                }
            }
        } else {
            if (gd_str_length($getData['refundAccountNumber']) > 50) {
                $getData['refundAccountNumber'] = \Encryptor::decrypt($getData['refundAccountNumber']);
            }
        }
        unset($arrBind);

        return $getData;
    }

    /**
     * getOrderGoodsMaxOrderCd
     * 주문 순서 최대 값
     *
     * @param $orderNo
     * @return integer 주문 순서 최대값
     */
    public function getOrderGoodsMaxOrderCd($orderNo)
    {
        $arrBind = [];

        $this->db->strField = "max(og.orderCd) as maxOrderCd";
        $this->db->strWhere = 'og.orderNo = ?';
        $this->db->bind_param_push($arrBind, $this->fieldTypes['orderGoods']['orderNo'], $orderNo);
        $this->db->strOrder = 'og.regDt asc';

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_GOODS . ' as og ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);
        unset($arrBind);

        return $getData[0]['maxOrderCd'];
    }

    /**
     * getCancelOrderGoodsData
     * 취소할 주문 상품 정보
     *
     * @param string $orderNo
     * @param array $arrOrderGoods 취소할 주문상품 고유번호
     * @return array $getData
     */
    public function getCancelOrderGoodsData($orderNo, $arrOrderGoods)
    {
        $arrBind = [];

        $this->db->strField = "og.handleSno, og.orderStatus, og.orderDeliverySno, og.scmNo, og.goodsDeliveryCollectFl, og.checkoutData"; // 상품정보
        $this->db->strField .= ", og.goodsType, og.goodsNo, og.goodsCnt, og.goodsPrice, og.optionPrice, og.optionTextPrice"; // 상품 가
        $this->db->strField .= ", og.goodsDcPrice, og.memberDcPrice, og.memberOverlapDcPrice, og.couponGoodsDcPrice"; // 상품 할인들
        $this->db->strField .= ", og.divisionUseDeposit, og.divisionUseMileage, og.divisionCouponOrderDcPrice"; // 주문 할인들
        $this->db->strField .= ", og.goodsMileage, og.memberMileage, og.couponGoodsMileage, og.divisionCouponOrderMileage"; // 적립 마일리지
        $this->db->strField .= ", o.totalCouponDeliveryDcPrice, og.totalCouponDeliveryDcPrice, og.totalMemberDeliveryDcPrice, og.divisionCouponOrderMileage"; // 적립 마일리지
        if ($this->myappUseFl) {
            $this->db->strField .= ", og.myappDcPrice"; // 상품 할인들
        }
        $this->db->strWhere = 'og.orderNo = ? AND og.sno IN (?)';
        $this->db->bind_param_push($arrBind, $this->fieldTypes['orderGoods']['orderNo'], $orderNo);
        $this->db->bind_param_push($arrBind, 's', implode(',', $arrOrderGoods));
        $this->db->strJoin = "LEFT JOIN " . DB_ORDER . " as o ON og.orderNo = o.orderNo";
        $this->db->strOrder = 'og.regDt asc';

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_GOODS . ' as og ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);
        unset($arrBind);

        return $getData;
    }

    /**
     * getSelectOrderGoodsCancelData
     * 입금 전 부분 취소 시 취소되는 상품의 취소 예정 금액 계산
     * @param array $orderNo 취소할 주문 고유번호
     * @param array $arrOrderGoods ['sno'] 취소할 주문상품 고유번호
     * @param array $arrOrderGoods ['sno']['cnt'] 취소할 주문상품 수량
     *
     * @return array $cancelData 반환할 주문 상품 정보
     */
    public function getSelectOrderGoodsCancelData($orderNo, $arrOrderGoods)
    {
        // 반환할 주문 상품 정보
        $cancelData = [];
        $cancelOrderGoodsSno = array_keys($arrOrderGoods);

        // 취소 상품의 주문 정보
        $orderAdmin = App::load(\Component\Order\OrderAdmin::class);
        $orderGoodsData = $orderAdmin->getOrderGoodsData($orderNo, $cancelOrderGoodsSno);
        $orderDeliverySno = '';
        foreach ($orderGoodsData as $scmNo => $dataVal) {
            foreach ($dataVal as $goodsData) {
                // 상품 1개의 금액 (판가+옵가+텍옵)
                $goodsPrice = $goodsData['goodsPrice'] + $goodsData['optionPrice'] + $goodsData['optionTextPrice'];

                // 취소 상품 금액 (상품금액에 취소 수량 *)
                $cancelData['cancelGoodsPrice'][$goodsData['sno']] = $goodsPrice * $arrOrderGoods[$goodsData['sno']];

                // 취소 상품 할인 금액 (상품할인에 취소 수량 *)
                $cancelData['cancelGoodsDcPrice'][$goodsData['sno']] = gd_number_figure($goodsData['goodsDcPrice'] * ($arrOrderGoods[$goodsData['sno']] / $goodsData['goodsCnt']), $this->truncPolicy['unitPrecision'], $this->truncPolicy['unitRound']);

                // 취소 상품 에누리 금액 (상품 에누리에 취소 수량 *)
                $cancelData['cancelGoodsEnuriPrice'][$goodsData['sno']] = gd_number_figure($goodsData['enuri'] * ($arrOrderGoods[$goodsData['sno']] / $goodsData['goodsCnt']), $this->truncPolicy['unitPrecision'], $this->truncPolicy['unitRound']);

                // 취소 회원 추가 할인 금액 (회원할인에 취소 수량 *)
                $cancelData['cancelMemberDcPrice'][$goodsData['sno']] = gd_number_figure($goodsData['memberDcPrice'] * ($arrOrderGoods[$goodsData['sno']] / $goodsData['goodsCnt']), $this->memberGroupPolicy['unitPrecision'], $this->memberGroupPolicy['unitRound']);

                // 취소 회원 중복 할인 금액 (회원할인에 취소 수량 *)
                $cancelData['cancelMemberOverlapDcPrice'][$goodsData['sno']] = gd_number_figure($goodsData['memberOverlapDcPrice'] * ($arrOrderGoods[$goodsData['sno']] / $goodsData['goodsCnt']), $this->memberGroupPolicy['unitPrecision'], $this->memberGroupPolicy['unitRound']);

                // 취소 상품 쿠폰 할인 금액 (상품쿠폰에 취소 수량 *)
                $cancelData['cancelGoodsCouponDcPrice'][$goodsData['sno']] = gd_number_figure($goodsData['couponGoodsDcPrice'] * ($arrOrderGoods[$goodsData['sno']] / $goodsData['goodsCnt']), $this->couponTruncPolicy['unitPrecision'], $this->couponTruncPolicy['unitRound']);

                // 취소 마이앱 할인 금액 (마이앱할인에 취소 수량 *)
                if ($this->myappUseFl) {
                    $cancelData['cancelMyappDcPrice'][$goodsData['sno']] = gd_number_figure($goodsData['myappDcPrice'] * ($arrOrderGoods[$goodsData['sno']] / $goodsData['goodsCnt']), $this->truncPolicy['unitPrecision'], $this->truncPolicy['unitRound']);
                }

                if ($goodsData['goodsDeliveryCollectFl'] == 'pre') {// 선불만 처리
                    $cancelData['totalCancelDelivery'][$goodsData['sno']] = $goodsData['orderDeliverySno'];
                    if ($orderDeliverySno != $goodsData['orderDeliverySno']) {
                        // 노출 이름을 $goodsData['goodsNm'] or $goodsData['deliveryMethod']
                        $cancelData['totalCancelDeliveryGoodsName'][$goodsData['orderDeliverySno']] = $goodsData['goodsNm'];// 취소상품의 배송비 조건에 따른 이름
                        $cancelData['totalCancelDeliveryPrice'][$goodsData['orderDeliverySno']] = $goodsData['deliveryPolicyCharge'];// 배송비 금액
                        $cancelData['totalCancelAreaDeliveryPrice'][$goodsData['orderDeliverySno']] = $goodsData['deliveryAreaCharge'];// 지역별 배송비 금액
                        $cancelData['totalCancelOverseaDeliveryPrice'][$goodsData['orderDeliverySno']] = $goodsData['deliveryInsuranceFee'];// 해외 배송비 보험료 금액
                        $deliveryPrice = $goodsData['deliveryPolicyCharge'] + $goodsData['deliveryAreaCharge'] + $goodsData['deliveryInsuranceFee'];
                        $cancelData['totalCancelDeliveryMemberDcPrice'][$goodsData['orderDeliverySno']] = $goodsData['divisionMemberDeliveryDcPrice'];// 취소 회원 배송비 무료 가능 금액
                        $cancelData['totalCancelDeliveryPriceSum'][$goodsData['orderDeliverySno']] = $deliveryPrice;// 배송비 합
                    }
                    $orderDeliverySno = $goodsData['orderDeliverySno'];
                }
            }
        }
        $cancelData['cancelGoodsPriceSum'] = array_sum($cancelData['cancelGoodsPrice']);//취소 상품 금액 합
        $cancelData['cancelGoodsDcPriceSum'] = array_sum($cancelData['cancelGoodsDcPrice']);//취소 상품 할인 합
        $cancelData['cancelGoodsEnuriPriceSum'] = array_sum($cancelData['cancelGoodsEnuriPrice']);//취소 상품 에누리 합
        $cancelData['cancelMemberDcPriceSum'] = array_sum($cancelData['cancelMemberDcPrice']);//취소 회원 추가 할인 합
        $cancelData['cancelMemberOverlapDcPriceSum'] = array_sum($cancelData['cancelMemberOverlapDcPrice']);//취소 회원 중복 할인 합
        $cancelData['cancelGoodsCouponDcPriceSum'] = array_sum($cancelData['cancelGoodsCouponDcPrice']);//취소 상품 쿠폰 할인 합
        if ($this->myappUseFl) {
            $cancelData['cancelMyappDcPriceSum'] = array_sum($cancelData['cancelMyappDcPrice']);//취소 상품 마이앱 할인 합
        }

        // 취소 상품혜택 할인 합
        $cancelData['totalCancelGoodsDcPriceSum'] = $cancelData['cancelGoodsDcPriceSum'] + $cancelData['cancelGoodsEnuriPriceSum'] + $cancelData['cancelMemberDcPriceSum'] + $cancelData['cancelMemberOverlapDcPriceSum'] + $cancelData['cancelGoodsCouponDcPriceSum'];
        if ($this->myappUseFl) {
            $cancelData['totalCancelGoodsDcPriceSum'] += $cancelData['cancelMyappDcPriceSum'];
        }

        // 주문 정보의 데이터
        $orderData = $this->getOrderData($orderNo);
        $cancelData['totalCancelOrderCouponDcPrice'] = $orderData['totalCouponOrderDcPrice'];// 취소 주문 쿠폰 가능 금액
        $cancelData['totalCancelDeliveryCouponDcPrice'] = $orderData['totalCouponDeliveryDcPrice'];// 취소 배송비 쿠폰 가능 금액
        $cancelData['totalCancelDeliveryMemberDcPrice'] = gd_isset(array_sum($cancelData['totalCancelDeliveryMemberDcPrice']), 0);// 취소 회원 배송비 무료 가능 금액

        // 조정 가능 배송비 합
        $cancelData['totalCancelDeliveryPriceFirst'] = array_sum($cancelData['totalCancelDeliveryPriceSum']);

        // 취소 배송비 할인혜택 가능 금액 합
        $cancelData['totalCancelDeliveryDcPrice'] = $cancelData['totalCancelDeliveryCouponDcPrice'] + $cancelData['totalCancelDeliveryMemberDcPrice'];

        $cancelData['totalCancelDepositPrice'] = $orderData['useDeposit'];// 취소 예치금 가능 금액
        $cancelData['totalCancelMileagePrice'] = $orderData['useMileage'];// 취소 마일리지 가능 금액

        // 기본 상품 취소 금액 (취소 상품금액 + 취소 상품할인혜택금액)
        $cancelData['totalCancelGoods'] = $cancelData['cancelGoodsPriceSum'] - $cancelData['totalCancelGoodsDcPriceSum'];
        $cancelData['totalCancelDelivery'] = $cancelData['totalCancelDeliveryPriceFirst'] - $cancelData['totalCancelDeliveryDcPrice'];

        // 남은 결제 예정 금액 (결제 금액 - 기본 취소 금액)
        $cancelData['totalSettlePrice'] = $orderData['settlePrice'] - $cancelData['totalCancelPrice'] - $cancelData['totalCancelDeliveryPriceFirst'] - $cancelData['totalCancelDepositPrice'] - $cancelData['totalCancelMileagePrice'];

        // 변경 전 결제 금액
        $cancelData['settlePrice'] = $orderData['settlePrice'];

        return $cancelData;
    }

    /**
     * getSelectOrderGoodsRefundData
     * 환불 접수된 상품 금액 처리
     *
     * @param string $orderNo
     * @param array $arrRefundOrderGoods
     * @param array $arrEtcOrderGoods
     *
     * @return array
     */
    public function getSelectOrderGoodsRefundData($orderNo, $arrRefundOrderGoods, $arrEtcOrderGoods)
    {
        //환불가능한 총 부가결제금액 정보
        $totalAddPaymentPrice = [
            'goods' => [
                'deposit' => [],
                'mileage' => [],
            ],
            'delivery' => [
                'deposit' => [],
                'mileage' => [],
            ],
        ];

        $refundGoodsSno = [];
        foreach ($arrRefundOrderGoods as $scmNo => $deliveryData) {
            foreach ($deliveryData as $deliveryNo => $goodsData) {
                foreach ($goodsData as $key => $val) {
                    $refundGoodsSno[] = $val['sno'];
                }
            }
        }

        // 남은 주문 상품들의 정보 : 부가결제 금액의 필수 환불 금액을 구하기 위함
        $etcSettlePrice = 0;
        $etcDeliveryPrice = 0;
        $etcDeliverySno = [];
        foreach ($arrEtcOrderGoods as $scmNo => $deliveryData) {
            foreach ($deliveryData as $deliveryNo => $goodsData) {
                foreach ($goodsData as $key => $val) {
                    $totalAddPaymentPrice['goods']['deposit'][$val['sno']] = $val['divisionUseDeposit'];
                    $totalAddPaymentPrice['goods']['mileage'][$val['sno']] = $val['divisionUseMileage'];

                    if(!in_array($val['sno'], $refundGoodsSno)) {
                        $dcSum = [
                            $val['goodsDcPrice'],
                            $val['memberDcPrice'],
                            $val['memberOverlapDcPrice'],
                            $val['couponGoodsDcPrice'],
                            $val['enuri'],
                            $val['divisionCouponOrderDcPrice'],
                        ];
                        if ($this->myappUseFl) {
                            $dcSum[] = $val['myappDcPrice'];
                        }

                        // 환불 후에 남는 주문상품의 결제가.
                        $etcSettlePrice += (($val['goodsPrice'] + $val['optionPrice'] + $val['optionTextPrice']) * $val['goodsCnt']);
                        $etcSettlePrice -= (array_sum($dcSum));
                    }

                    if ($val['goodsDeliveryCollectFl'] == 'pre' && $val['orderDeliverySno']){
                        if(!in_array($val['sno'], $refundGoodsSno)) {
                            $etcDeliverySno[$val['orderDeliverySno']] = $val['orderDeliverySno'];
                        }
                        $totalAddPaymentPrice['delivery']['deposit'][$val['orderDeliverySno']] = $val['divisionDeliveryUseDeposit'];
                        $totalAddPaymentPrice['delivery']['mileage'][$val['orderDeliverySno']] = $val['divisionDeliveryUseMileage'];
                    }
                }
            }
        }

        // 반환할 주문 상품 정보
        $refundData = [];
        $checkOrderDeliverySno = 0;
        foreach ($arrRefundOrderGoods as $scmNo => $deliveryData) {
            foreach ($deliveryData as $deliveryNo => $goodsData) {
                foreach ($goodsData as $key => $val) {
                    $totalAddPaymentPrice['goods']['deposit'][$val['sno']] = $val['divisionUseDeposit'];
                    $totalAddPaymentPrice['goods']['mileage'][$val['sno']] = $val['divisionUseMileage'];

                    $goodsPrice = $val['goodsPrice'] + $val['optionPrice'] + $val['optionTextPrice'];// 상품 1개의 금액 (판가+옵가+텍옵)
                    $goodsCnt = $val['goodsCnt'];// 환불 수량
                    $refundData['refundGoodsPrice'][$val['sno']] = $goodsPrice * $goodsCnt;// 환불 상품 금액 (상품금액에 취소 수량 *)
                    $refundData['refundGoodsDcPrice'][$val['sno']] = $val['goodsDcPrice'];// 환불 상품 할인 금액
                    $refundData['refundMemberDcPrice'][$val['sno']] = $val['memberDcPrice'];// 환불 회원 추가 할인 금액
                    $refundData['refundMemberOverlapDcPrice'][$val['sno']] = $val['memberOverlapDcPrice'];// 환불 회원 중복 할인 금액
                    $refundData['refundGoodsCouponDcPrice'][$val['sno']] = $val['couponGoodsDcPrice'];// 환불 상품 쿠폰 할인 금액
                    $refundData['refundGoodsEnuriDcPrice'][$val['sno']] = $val['enuri'];// 환불 운영자 할인 금액
                    $refundData['refundOrderCouponDcPrice'][$val['sno']] = $val['divisionCouponOrderDcPrice'];// 환불 주문 쿠폰 할인 금액
                    if ($this->myappUseFl) {
                        $refundData['refundMyappDcPrice'][$val['sno']] = $val['myappDcPrice'];// 환불 마이앱 할인 금액
                    }
                    $refundData['refundGoodsDeposit'][$val['sno']] = $val['divisionUseDeposit'];// 환불 상품에 적용된 예치금 금액
                    $refundData['refundGoodsMileage'][$val['sno']] = $val['divisionUseMileage'];// 환불 상품에 적용된 마일리지 금액
                    $refundData['refundGoodsDeliveryDeposit'][$val['sno']] = $val['divisionGoodsDeliveryUseDeposit'];// 환불 배송비에 적용된 예치금 금액을 상품에 안분한 금액
                    $refundData['refundGoodsDeliveryMileage'][$val['sno']] = $val['divisionGoodsDeliveryUseMileage'];// 환불 배송비에 적용된 마일리지 금액을 상품에 안분한 금액
                    $refundData['refundDeliveryInsuranceFee'][$val['sno']] = $val['deliveryInsuranceFee'];// 해외배송비 보험료
                    // 환불 배송비
                    $refundData['refundDeliveryCharge'][$val['orderDeliverySno']] += ($val['refundDeliveryCharge'] + $val['refundDeliveryUseDeposit'] + $val['refundDeliveryUseMileage']);
                    if ($val['goodsDeliveryCollectFl'] == 'pre' && $val['orderDeliverySno'] != $checkOrderDeliverySno) {// 선불만 처리
                        $refundData['totalRefundDelivery'][$val['sno']] = $val['orderDeliverySno'];
                        $refundData['totalRefundHandleSno'][$val['orderDeliverySno']] = $val['handleSno'];
                        $refundData['realDeliveryCharge'][$val['orderDeliverySno']] = $val['realDeliveryCharge'] + $val['divisionDeliveryUseDeposit'] + $val['divisionDeliveryUseMileage'];
                        // 노출 이름을 $val['goodsNm'] or $val['deliveryMethod']
                        $deliveryName = $val['goodsNm'];
                        if($val['multiShippingFl'] === 'y'){
                            if((int)$val['orderInfoCd'] < 2){
                                $multiShippingPrefixName = '(메인) ';
                            }
                            else {
                                $multiShippingPrefixName = '(추가' . ((int)$val['orderInfoCd']-1) . ') ';
                            }

                            $deliveryName = $multiShippingPrefixName . $val['deliveryMethod'];
                        }
                        $refundData['refundDeliveryGoodsName'][$val['orderDeliverySno']] = $deliveryName;// 환불상품의 배송비 조건에 따른 이름
                        $checkOrderDeliverySno = $val['orderDeliverySno'];
                    }
                }
            }
        }
        $refundData['refundGoodsPriceSum'] = array_sum($refundData['refundGoodsPrice']);//환불 상품 금액 합
        $refundData['refundGoodsDcPriceSum'] = array_sum($refundData['refundGoodsDcPrice']);//환불 상품 할인 합
        $refundData['refundMemberDcPriceSum'] = array_sum($refundData['refundMemberDcPrice']);//환불 회원 추가 할인 합
        $refundData['refundMemberOverlapDcPriceSum'] = array_sum($refundData['refundMemberOverlapDcPrice']);//환불 회원 중복 할인 합
        $refundData['refundGoodsCouponDcPriceSum'] = array_sum($refundData['refundGoodsCouponDcPrice']);//환불 상품 쿠폰 할인 합
        $refundData['refundGoodsEnuriDcPriceSum'] = array_sum($refundData['refundGoodsEnuriDcPrice']);//환불 운영자 할인 합
        $refundData['refundOrderCouponDcPriceSum'] = array_sum($refundData['refundOrderCouponDcPrice']);//환불 주문 쿠폰 할인 합
        if ($this->myappUseFl) {
            $refundData['refundMyappDcPriceSum'] = array_sum($refundData['refundMyappDcPrice']);//환불 마이앱 할인 합
        }
        $refundData['refundGoodsDepositSum'] = array_sum($refundData['refundGoodsDeposit']);//환불 상품에 적용된 예치금 금액 합
        $refundData['refundGoodsMileageSum'] = array_sum($refundData['refundGoodsMileage']);//환불 상품에 적용된 마일리지 금액 합
        $refundData['refundGoodsDeliveryDepositSum'] = array_sum($refundData['refundGoodsDeliveryDeposit']);//환불 배송비에 적용된 예치금 금액을 상품에 안분한 금액 합
        $refundData['refundGoodsDeliveryMileageSum'] = array_sum($refundData['refundGoodsDeliveryMileage']);//환불 배송비에 적용된 마일리지 금액을 상품에 안분한 금액 합
        // 환불 상품혜택 할인 합
        $refundData['totalRefundGoodsDcPriceSum'] = $refundData['refundGoodsDcPriceSum'] + $refundData['refundMemberDcPriceSum'] + $refundData['refundMemberOverlapDcPriceSum'] + $refundData['refundGoodsCouponDcPriceSum'] + $refundData['refundGoodsEnuriDcPriceSum'] + $refundData['refundOrderCouponDcPriceSum'];
        if ($this->myappUseFl) {
            $refundData['totalRefundGoodsDcPriceSum'] += $refundData['refundMyappDcPriceSum'];
        }
        // 기본 상품 환불 금액 (환불 상품금액 + 환불 상품할인혜택금액)
        $refundData['totalRefundGoodsPrice'] = $refundData['refundGoodsPriceSum'] - $refundData['totalRefundGoodsDcPriceSum'];

        // 주문 배송비
        $orderDeliveryData = $this->getOrderDeliveryData($orderNo);
        foreach ($orderDeliveryData as $val) {
            $totalAddPaymentPrice['delivery']['deposit'][$val['sno']] = $val['divisionDeliveryUseDeposit'];
            $totalAddPaymentPrice['delivery']['mileage'][$val['sno']] = $val['divisionDeliveryUseMileage'];

            // 배송비
            $deliverySum = $val['deliveryPolicyCharge'] + $val['deliveryAreaCharge'];
            // 배송비 할인 금액
            $deliveryDcSum = $val['divisionDeliveryCharge'] + $val['divisionMemberDeliveryDcPrice'];

            if (in_array($val['sno'], $refundData['totalRefundDelivery'])) {
                $refundData['refundDeliveryHandleSno'][$val['sno']] = $refundData['totalRefundHandleSno'][$val['sno']];
                $refundData['refundPolicyDeliveryPrice'][$val['sno']] = $val['deliveryPolicyCharge'];// 배송비 금액
                $refundData['refundAreaDeliveryPrice'][$val['sno']] = $val['deliveryAreaCharge'];// 지역별 배송비 금액
                $refundData['refundOverseaDeliveryPrice'][$val['sno']] = $val['deliveryInsuranceFee'];// 해외 배송비 보험료 금액
                $refundData['refundDeliveryDeposit'][$val['sno']] = $val['divisionDeliveryUseDeposit'];// 환불 배송비에 적용된 예치금 금액
                $refundData['refundDeliveryMileage'][$val['sno']] = $val['divisionDeliveryUseMileage'];// 환불 배송비에 적용된 마일리지 금액
                $refundData['refundDeliveryCoupon'][$val['sno']] = $val['divisionDeliveryCharge'];// 환불 배송비에 적용된 배송비쿠폰 금액
                $refundData['refundDeliveryMemberDc'][$val['sno']] = $val['divisionMemberDeliveryDcPrice'];// 환불 배송비에 적용된 회원 배송비무료 금액
                $refundData['refundDeliveryPrice'][$val['sno']] = $deliverySum;// 배송비 합
                $refundData['refundDeliveryDcPrice'][$val['sno']] = $deliveryDcSum;// 배송비 할인 합

                // 남은 주문상품과 환불하려는 주문상품의 배송비가 조건별로 묶여 있을 시
                if($etcDeliverySno[$val['sno']]){
                    $refundData['etcSameDeliverySno'][$val['sno']] = $val['sno'];
                    $etcDeliveryPrice += ($deliverySum - $deliveryDcSum);
                }
            }
            else {
                // 남은 주문상품의 배송비
                if($etcDeliverySno[$val['sno']]){
                    $etcDeliveryPrice += ($deliverySum - $deliveryDcSum);
                }
            }
        }

        $refundData['refundPolicyDeliveryPriceSum'] = array_sum($refundData['refundPolicyDeliveryPrice']);//환불 배송비 금액 합
        $refundData['refundAreaDeliveryPriceSum'] = array_sum($refundData['refundAreaDeliveryPrice']);//환불 지역별 배송비 합
        $refundData['refundOverseaDeliveryPriceSum'] = array_sum($refundData['refundOverseaDeliveryPrice']);//환불 해외 배송비 합
        $refundData['refundDeliveryDepositSum'] = array_sum($refundData['refundDeliveryDeposit']);//환불 배송비에 적용된 예치금 금액 합
        $refundData['refundDeliveryMileageSum'] = array_sum($refundData['refundDeliveryMileage']);//환불 배송비에 적용된 마일리지 금액 합
        $refundData['refundDeliveryCouponSum'] = array_sum($refundData['refundDeliveryCoupon']);//환불 배송비에 적용된 배송비쿠폰 합
        $refundData['refundDeliveryMemberDcSum'] = array_sum($refundData['refundDeliveryMemberDc']);//환불 배송비에 적용된 회원 배송비무료 금액

        // 환불 배송비 합 ( 정책배송비 + 지역별 배송비 + 해외배송비 )
        $refundData['refundDeliveryPriceSum'] = $refundData['refundPolicyDeliveryPriceSum'] + $refundData['refundAreaDeliveryPriceSum'] + $refundData['refundOverseaDeliveryPriceSum'];
        // 환불 배송비 할인 합
        $refundData['refundDeliveryDcPriceSum'] = $refundData['refundDeliveryCouponSum'] + $refundData['refundDeliveryMemberDcSum'];
        // 기본 배송비 환불 금액 (환불 배송비금액 + 환불 배송비할인혜택금액 - 이미 처리된 배송비 환불 금액)
        $refundData['totalRefundDeliveryPrice'] = array_sum($refundData['refundDeliveryPrice']);

        // 주문 정보의 데이터
        $orderData = $this->getOrderData($orderNo);
        $refundData['totalRefundGoodsUseDepositPrice'] = array_sum($totalAddPaymentPrice['goods']['deposit']); // 환불 가능 상품 예치금 금액
        $refundData['totalRefundGoodsUseMileagePrice'] = array_sum($totalAddPaymentPrice['goods']['mileage']); // 환불 가능 상품 마일리지 금액
        $refundData['totalRefundDeliveryUseDepositPrice'] = array_sum($totalAddPaymentPrice['delivery']['deposit']); // 환불 가능 배송비 예치금 금액
        $refundData['totalRefundDeliveryUseMileagePrice'] = array_sum($totalAddPaymentPrice['delivery']['mileage']); // 환불 가능 배송비 마일리지 금액

        $refundData['totalRefundMemberDeliveryDcPrice'] = $orderData['totalMemberDeliveryDcPrice'];// 주문 시 적용된 회원 무료 배송비 금액
        $refundData['totalRefundCouponOrderDcPrice'] = $orderData['totalCouponOrderDcPrice'];// 주문 시 적용된 주문 쿠폰 금액
        $refundData['totalRefundCouponDeliveryDcPrice'] = $orderData['totalCouponDeliveryDcPrice'];// 주문 시 적용된 배송비 쿠폰 금액

        // 기본 환불 예정 금액 (상품 환불 금액 + 배송비 환불 금액)
        $refundData['totalRefundPrice'] = $refundData['totalRefundGoodsPrice'] + $refundData['totalRefundDeliveryPrice'];

        // 환불 후에 남는 상품 결제가 (현재 환불하려는 주문상품제외)
        $refundData['etcGoodsSettlePrice'] = $etcSettlePrice;
        // 환불 후에 남는 배송비 결제가 (현재 환불하려는 주문상품제외)
        $refundData['etcDeliverySettlePrice'] = $etcDeliveryPrice;

        return $refundData;
    }

    public function setRefundCompleteOrderGoods($getData, $autoProcess)
    {
        $goods = \App::load(\Component\Goods\Goods::class);
        $orderAdmin = App::load(\Component\Order\OrderAdmin::class);

        // 환불수단에 맞지않는 금액값이 넘어오면 리턴처리
        if ($getData['info']['refundMethod'] == '현금환불') {
            if ($getData['info']['completePgPrice'] > 0 || $getData['info']['completeDepositPrice'] > 0 || $getData['info']['completeMileagePrice'] > 0) {
                throw new Exception(__('현금환불이 아닌 환불 금액정보가있습니다. 다시 확인해주세요.'));
            }
        }
        if ($getData['info']['refundMethod'] == 'PG환불') {
            if ($getData['info']['completeCashPrice'] > 0 || $getData['info']['completeDepositPrice'] > 0 || $getData['info']['completeMileagePrice'] > 0) {
                throw new Exception(__('PG환불이 아닌 환불 금액정보가있습니다. 다시 확인해주세요.'));
            }
        }
        if ($getData['info']['refundMethod'] == '예치금환불') {
            if ($getData['info']['completeCashPrice'] > 0 || $getData['info']['completePgPrice'] > 0 || $getData['info']['completeMileagePrice'] > 0) {
                throw new Exception(__('예치금환불이 아닌 환불 금액정보가있습니다. 다시 확인해주세요.'));
            }
        }
        if ($getData['info']['refundMethod'] == '기타환불') {
            if ($getData['info']['completeCashPrice'] > 0 || $getData['info']['completePgPrice'] > 0 || $getData['info']['completeDepositPrice'] > 0) {
                throw new Exception(__('기타환불이 아닌 환불 금액정보가있습니다. 다시 확인해주세요.'));
            }
        }

        unset($getData['mode']);

        //부가결제 수수료 초기화
        if ($getData['addPaymentChargeUseFl'] !== 'y') {
            unset($getData['refundUseDepositCommissionWithFl'], $getData['refundUseMileageCommissionWithFl']);
            unset($getData['info']['refundUseDepositCommission'], $getData['info']['refundUseMileageCommission']);
        }

        // 환불 상세보기에서의 검색 조건 설정
        $handleSno = null;
        $excludeStatus = null;
        if ($getData['isAll'] != 1 && $getData['handleSno'] != 0) {
            $handleSno = $getData['handleSno'];
        }

        // 접근권한, 형식체크
        if($autoProcess !== true) {
            $returnError = '';
            $returnError = $this->checkRefundCompleteAccess($getData);
            if (trim($returnError) !== '') {
                throw new Exception($returnError);
            }
        }

        // 환불할 주문상품의 데이터
        $orderInfo = $orderAdmin->getOrderView($getData['orderNo'], null, $handleSno, 'r', ['r3']);
        if ($orderInfo['mallSno'] > DEFAULT_MALL_NUMBER && $handleSno !== null) {
            // 해외 상점의 경우 handleSno를 null로 처리해 무조건 부분취소 아닌 전체로 처리되게 한다.
            throw new Exception(__('해외상점 취소/교환/반품/환불은 전체 처리만 가능합니다.'));
        }
        if ((int)$orderInfo['memNo'] < 1 && (int)$getData['info']['completeDepositPrice'] > 0) {
            throw new Exception(__('비회원 주문건은 예치금 환불수단을 사용 할 수 없습니다.'));
        }
        if ($orderInfo['settleKind'] === 'gb' && ((int)$getData['info']['completePgPrice'] > 0 || $getData['info']['refundMethod'] === 'PG환불')) {
            throw new Exception(__('무통장입금 주문건은 PG환불 수단으로 환불을 진행할 수 없습니다.'));
        }

        // 환불할 주문상품을 가공하여 사용할 데이터 조합.
        $orderRefundInfo = $this->getRefundInfoData($getData['orderNo'], $orderInfo, $getData);
        // 환불, 취소, 교환취소외 주문상품데이터 (realtax, 부가결제금액 데이터를 이관할 주문상품)
        $orderEtcInfo = $this->getRefundEtcData($getData, $handleSno, $getData['isAll'], $orderRefundInfo);
        // 환불그룹 코드
        $refundGroupCd = $orderAdmin->getMaxRefundGroupCd($getData['orderNo']);

        // *** 1. 사용할 변수 선언
        $tmpRefundGoodsSettlePrice = $getData['refundGoodsPriceSum']; //환불되는 상품의 실 결제가
        $tmpRefundDeliverySettlePrice = $getData['refundDeliveryPriceSum']; //환불되는 배송의 실 결제가
        $tmpRefundGoodsUseDeposit = $getData['info']['refundGoodsUseDeposit'];
        $tmpRefundDeliveryUseDeposit = $getData['info']['refundDeliveryUseDeposit'];
        $tmpRefundGoodsUseMileage = $getData['info']['refundGoodsUseMileage'];
        $tmpRefundDeliveryUseMileage = $getData['info']['refundDeliveryUseMileage'];

        // 환불주문상품에 취소되는 부가결제 금액을 넣기위해 사용되는 값. (실제계산이랑은 관계없음. 오로지 환불상품에 저장하기 위한 용도)
        $tmpRefundSaveGoodsDeposit = $getData['info']['refundGoodsUseDeposit'];
        $tmpRefundSaveGoodsMileage = $getData['info']['refundGoodsUseMileage'];
        $tmpRefundSaveDeliveryDeposit = $getData['info']['refundDeliveryUseDeposit'];
        $tmpRefundSaveDeliveryMileage = $getData['info']['refundDeliveryUseMileage'];

        // 사용예치금, 사용마일리지 수수료
        $tmpRefundUseDepositCommission = gd_isset($getData['info']['refundUseDepositCommission'], 0);
        $tmpRefundUseMileageCommission = gd_isset($getData['info']['refundUseMileageCommission'], 0);

        // 배송비쪽에 남아있는 부가결제금액 ( 상품쪽에 재안분하여 업데이트하기위함 )
        $totalRestDeliveryDeposit = [];
        $totalRestDeliveryMileage = [];

        //order handle 업데이트 할 데이터
        $tmpUpdateOrderHandleData = [];

        // 토탈 해외배송보험료
        $totalRefundDeliveryInsuranceFee = 0;

        // 실제 환불되는 realtax 금액
        $refundRealTaxData = [
            'realTaxSupplyPrice' => 0,
            'realTaxVatPrice' => 0,
            'realTaxFreePrice' => 0,
        ];
        //남은 주문으로 이관되어야 할 realtax 금액
        $moveRealTaxData = [
            'plus' => [
                'realTaxSupplyPrice' => 0,
                'realTaxVatPrice' => 0,
                'realTaxFreePrice' => 0,
            ],
            'minus' => [
                'price' => 0,
            ],
        ];
        // 남은 주문으로 이관되어야 할 부가결제 금액 정보
        $moveAddPaymentData = [
            'deposit' => 0,
            'mileage' => 0,
        ];

        // *** 2. 데이터 백업
        $backupReturn = $this->setBackupOrderOriginalData($getData['orderNo'], 'r', true);
        if($backupReturn === false){
            throw new Exception(__('주문 백업을 실패하였습니다. 관리자에게 문의하세요.'));
        }
        // *** 3. 환불상품 처리
        $lastHandleSno = 0;
        $refundGoodsIndex = 1;
        $smsCnt = 0;    // 부분 환불 시 sms 일괄 전송을 위한 cnt(sms 개선)

        foreach ($orderInfo['goods'] as $sKey => $sVal) {
            foreach ($sVal as $dKey => $dVal) {
                foreach ($dVal as $gKey => $gVal) {
                    $smsCnt++;
                    $refundData = $getData['refund'][$gVal['handleSno']];
                    // sms 개선(환불 시 금액 전달을 위한 변수, 부분 환불 시 sms 일괄 전송을 위한 cnt)
                    $orderRefundInfo['orderHandleData'][$gVal['handleSno']][0]['refundCompletePrice'] = $getData['check']['totalRefundPrice'];
                    $orderRefundInfo['orderHandleData'][$gVal['handleSno']][0]['smsCnt'] = $smsCnt;
                    // 주문 상태 변경 처리 및 재고 복원여부 처리
                    $isReturnStock = ($getData['returnStockFl'] === 'y') ? true : false;  // 환불상세에서 재고환원 여부에 동의시 처리여부
                    $orderAdmin->updateStatusPreprocess($getData['orderNo'], $orderRefundInfo['orderHandleData'][$gVal['handleSno']], 'r', 'r3', __('일괄'), $isReturnStock, null, null, $autoProcess);

                    // 환불된 상품 상품 테이블 주문상품 갯수 필드 차감 처리 es_goods.orderGoodsCnt
                    $goods->setOrderGoodsCount($gVal['sno'], true, $gVal['goodsNo'], $gVal['goodsCnt']);

                    // 과세, 부가세, 면세의 비율
                    $realTaxRate = $this->getRealTaxRate($gVal['realTaxSupplyGoodsPrice'], $gVal['realTaxVatGoodsPrice'], $gVal['realTaxFreeGoodsPrice']);
                    if (array_sum($realTaxRate) === 0) {
                        // 상품의 realtax 가 존재하지 않아 tax 비율을 구하지 못할 경우 상품의 과세, 비과세 설정에 따라 과세비율 조정
                        if ($gVal['goodsTaxInfo'][0] === 't') {
                            $realTaxRate['vat'] = $gVal['goodsTaxInfo'][1] / 100;
                            $realTaxRate['supply'] = 1 - $realTaxRate['vat'];
                        } else {
                            $realTaxRate['free'] = 1;
                        }
                    }

                    // 주문상품에 실제 남아있는 결제금액 (부가결제 금액도 제외되어 있는 값) : 실제 환불가능한 금액
                    $realSettlePrice = $gVal['realTaxSupplyGoodsPrice'] + $gVal['realTaxVatGoodsPrice'] + $gVal['realTaxFreeGoodsPrice'];

                    if (gd_isset($refundData['refundCharge'], 0) !== 0) {
                        $realSettlePrice -= gd_isset($refundData['refundCharge'], 0);

                        $gVal['realTaxSupplyGoodsPrice'] = NumberUtils::getNumberFigure($realSettlePrice * $realTaxRate['supply'], '0.1', 'round');
                        $gVal['realTaxVatGoodsPrice'] = NumberUtils::getNumberFigure($realSettlePrice * $realTaxRate['vat'], '0.1', 'round');
                        $gVal['realTaxFreeGoodsPrice'] = NumberUtils::getNumberFigure($realSettlePrice * $realTaxRate['free'], '0.1', 'round');
                        list($gVal['realTaxSupplyGoodsPrice'], $gVal['realTaxVatGoodsPrice'], $gVal['realTaxFreeGoodsPrice']) = $this->getRealTaxBalance($realSettlePrice, $gVal['realTaxSupplyGoodsPrice'], $gVal['realTaxVatGoodsPrice'], $gVal['realTaxFreeGoodsPrice']);
                    }

                    // realtax 차감
                    $realtaxMinusType = 'part';
                    if ($tmpRefundGoodsSettlePrice < 0 && $realSettlePrice < 0) {
                        if ($tmpRefundGoodsSettlePrice <= $realSettlePrice) {
                            $realtaxMinusType = 'all';
                        }
                    } else {
                        if ($tmpRefundGoodsSettlePrice >= $realSettlePrice) {
                            $realtaxMinusType = 'all';
                        }
                    }

                    if ($realtaxMinusType === 'all') {
                        // 전액환불 가능한 상태

                        // 실제 환불되는 realtax 금액
                        $refundRealTaxSupplyPrice = $gVal['realTaxSupplyGoodsPrice'];
                        $refundRealTaxVatPrice = $gVal['realTaxVatGoodsPrice'];
                        $refundRealTaxFreePrice = $gVal['realTaxFreeGoodsPrice'];
                        // 실제 환불되는 realtax 총 금액
                        $refundRealTaxData['realTaxSupplyPrice'] += $refundRealTaxSupplyPrice;
                        $refundRealTaxData['realTaxVatPrice'] += $refundRealTaxVatPrice;
                        $refundRealTaxData['realTaxFreePrice'] += $refundRealTaxFreePrice;

                        $tmpRefundGoodsSettlePrice -= $realSettlePrice;
                    } else if ($realtaxMinusType === 'part') {
                        // 일부만 환불 가능한 상태

                        // 실제 환불되는 realtax 금액
                        $refundRealTaxSupplyPrice = NumberUtils::getNumberFigure($tmpRefundGoodsSettlePrice * $realTaxRate['supply'], '0.1', 'round');
                        $refundRealTaxVatPrice = NumberUtils::getNumberFigure($tmpRefundGoodsSettlePrice * $realTaxRate['vat'], '0.1', 'round');
                        $refundRealTaxFreePrice = NumberUtils::getNumberFigure($tmpRefundGoodsSettlePrice * $realTaxRate['free'], '0.1', 'round');
                        list($refundRealTaxSupplyPrice, $refundRealTaxVatPrice, $refundRealTaxFreePrice) = $this->getRealTaxBalance($tmpRefundGoodsSettlePrice, $refundRealTaxSupplyPrice, $refundRealTaxVatPrice, $refundRealTaxFreePrice);

                        // 실제 환불되는 realtax 총 금액
                        $refundRealTaxData['realTaxSupplyPrice'] += $refundRealTaxSupplyPrice;
                        $refundRealTaxData['realTaxVatPrice'] += $refundRealTaxVatPrice;
                        $refundRealTaxData['realTaxFreePrice'] += $refundRealTaxFreePrice;
                        // 남은 주문상품에 이관되어야 할 realtax 금액
                        $moveRealTaxData['plus']['realTaxSupplyPrice'] += ($gVal['realTaxSupplyGoodsPrice'] - $refundRealTaxSupplyPrice);
                        $moveRealTaxData['plus']['realTaxVatPrice'] += ($gVal['realTaxVatGoodsPrice'] - $refundRealTaxVatPrice);
                        $moveRealTaxData['plus']['realTaxFreePrice'] += ($gVal['realTaxFreeGoodsPrice'] - $refundRealTaxFreePrice);

                        $tmpRefundGoodsSettlePrice = 0;
                    } else {
                    }

                    // 부가결제금액 차감 (환불상품은 취소되는 부가결제 값을 저장한다)
                    if ($tmpRefundGoodsUseDeposit >= $gVal['divisionUseDeposit']) {
                        $tmpRefundGoodsUseDeposit -= $gVal['divisionUseDeposit'];
                    } else {
                        $moveAddPaymentData['deposit'] += ($gVal['divisionUseDeposit'] - $tmpRefundGoodsUseDeposit);
                        $tmpRefundGoodsUseDeposit = 0;
                    }

                    if ($tmpRefundGoodsUseMileage >= $gVal['divisionUseMileage']) {
                        $tmpRefundGoodsUseMileage -= $gVal['divisionUseMileage'];
                    } else {
                        $moveAddPaymentData['mileage'] += ($gVal['divisionUseMileage'] - $tmpRefundGoodsUseMileage);
                        $tmpRefundGoodsUseMileage = 0;
                    }

                    // 환불 되는 부가결제 금액을 저장하기 위한 계산식. (실제 금액처리과정과는 별도인 오로지 저장용도) : 남은주문상품에서 빠지는 부가결제금액도 저장해야하기 떄문
                    if (count($orderRefundInfo['refundOrderGoodsSnos']) === $refundGoodsIndex) {
                        $refundGoodsDeposit = $tmpRefundSaveGoodsDeposit;
                        $refundGoodsMileage = $tmpRefundSaveGoodsMileage;
                        $refundDeliveryDeposit = $tmpRefundSaveDeliveryDeposit;
                        $refundDeliveryMileage = $tmpRefundSaveDeliveryMileage;
                    } else {
                        $refundGoodsDeposit = NumberUtils::getNumberFigure((1 / count($orderRefundInfo['refundOrderGoodsSnos'])) * $getData['info']['refundGoodsUseDeposit'], '0.1', 'round');
                        $refundGoodsMileage = NumberUtils::getNumberFigure((1 / count($orderRefundInfo['refundOrderGoodsSnos'])) * $getData['info']['refundGoodsUseMileage'], '0.1', 'round');
                        $refundDeliveryDeposit = NumberUtils::getNumberFigure((1 / count($orderRefundInfo['refundOrderGoodsSnos'])) * $getData['info']['refundDeliveryUseDeposit'], '0.1', 'round');
                        $refundDeliveryMileage = NumberUtils::getNumberFigure((1 / count($orderRefundInfo['refundOrderGoodsSnos'])) * $getData['info']['refundDeliveryUseMileage'], '0.1', 'round');

                        $tmpRefundSaveGoodsDeposit -= $refundGoodsDeposit;
                        $tmpRefundSaveGoodsMileage -= $refundGoodsMileage;
                        $tmpRefundSaveDeliveryDeposit -= $refundDeliveryDeposit;
                        $tmpRefundSaveDeliveryMileage -= $refundDeliveryMileage;
                    }

                    // --- 주문상품정보 업데이트
                    $updateOrderGoodsData = [
                        'realTaxSupplyGoodsPrice' => 0,
                        'realTaxVatGoodsPrice' => 0,
                        'realTaxFreeGoodsPrice' => 0,
                        'divisionUseDeposit' => $refundGoodsDeposit, // 환불주문상품에서 취소되어야 할 상품 예치금
                        'divisionUseMileage' => $refundGoodsMileage, // 환불주문상품에서 취소되어야 할 상품 마일리지
                        'divisionGoodsDeliveryUseDeposit' => $refundDeliveryDeposit, // 환불주문상품에서 취소되어야 할 배송 예치금
                        'divisionGoodsDeliveryUseMileage' => $refundDeliveryMileage, // 환불주문상품에서 취소되어야 할 배송 마일리지
                    ];
                    $this->updateOrderGoods($updateOrderGoodsData, $getData['orderNo'], $gVal['sno']);
                    unset($updateOrderGoodsData);

                    // 사용 예치금, 마일리지 부가결제 수수료
                    $refundUseDepositGoodsCommission = 0;
                    $refundUseMileageGoodsCommission = 0;
                    if ($tmpRefundUseDepositCommission > 0 && $refundGoodsDeposit > 0) {
                        if ($tmpRefundUseDepositCommission >= $refundGoodsDeposit) {
                            $refundUseDepositGoodsCommission = $refundGoodsDeposit;
                            $tmpRefundUseDepositCommission -= $refundGoodsDeposit;
                        } else {
                            $refundUseDepositGoodsCommission = $tmpRefundUseDepositCommission;
                            $tmpRefundUseDepositCommission = 0;
                        }
                    }
                    if ($tmpRefundUseMileageCommission > 0 && $refundGoodsMileage > 0) {
                        if ($tmpRefundUseMileageCommission >= $refundGoodsMileage) {
                            $refundUseMileageGoodsCommission = $refundGoodsMileage;
                            $tmpRefundUseMileageCommission -= $refundGoodsMileage;
                        } else {
                            $refundUseMileageGoodsCommission = $tmpRefundUseMileageCommission;
                            $tmpRefundUseMileageCommission = 0;
                        }
                    }

                    // --- 환불정보를 업데이트 하기위한 데이터 조합
                    $tmpUpdateOrderHandleData[$gVal['handleSno']] = [
                        'refundGroupCd' => $refundGroupCd,
                        'refundGoodsUseDeposit' => $refundGoodsDeposit,
                        'refundGoodsUseMileage' => $refundGoodsMileage,
                        'refundDeliveryUseDeposit' => 0,
                        'refundDeliveryUseMileage' => 0,
                        'refundUseDepositCommission' => $refundUseDepositGoodsCommission,
                        'refundUseMileageCommission' => $refundUseMileageGoodsCommission,
                        'totalRealGoodsMileage' => $gVal['totalRealGoodsMileage'],
                        'totalRealMemberMileage' => $gVal['totalRealMemberMileage'],
                        'totalRealCouponGoodsMileage' => $gVal['totalRealCouponGoodsMileage'],
                        'totalRealDivisionCouponOrderMileage' => $gVal['totalRealDivisionCouponOrderMileage'],
                        'refundRealTaxPrice' => $refundRealTaxSupplyPrice + $refundRealTaxVatPrice + $refundRealTaxFreePrice,
                    ];

                    $lastHandleSno = $gVal['handleSno'];

                    $refundGoodsIndex++;
                }
            }
        }

        // *** 4. 남은상품 처리 - 차액의 realtax, 부가결제금액 들을 재분배
        if (count($orderEtcInfo['goods']) > 0) {
            // 남은 상품이 나눠가져야 할 realtax 금액
            $plusRealTaxSupplyPrice = $moveRealTaxData['plus']['realTaxSupplyPrice'];
            $plusRealTaxVatPrice = $moveRealTaxData['plus']['realTaxVatPrice'];
            $plusRealTaxFreePrice = $moveRealTaxData['plus']['realTaxFreePrice'];
            // 남은 상품이 가져나눠야 할 부가결제 금액
            $plusTotalUseDeposit = $moveAddPaymentData['deposit'];
            $plusTotalUseMileage = $moveAddPaymentData['mileage'];

            //차감이 되지 않은 realtax 금액이 있다면 남은상품에서 차감해줘야 함.
            $minusRealTaxPrice = $tmpRefundGoodsSettlePrice;
            $minusUseDeposit = $tmpRefundGoodsUseDeposit;
            $minusUseMileage = $tmpRefundGoodsUseMileage;

            $index = 1;
            foreach ($orderEtcInfo['goods'] as $key => $value) {
                $divisionMinusSupplyPrice = $divisionMinusVatPrice = $divisionMinusFreePrice = 0;
                if (count($orderEtcInfo['goods']) === $index) {
                    //차감되지 않은 realtax 차감
                    if ($tmpRefundGoodsSettlePrice > 0) {
                        $divisionMinusSupplyPrice = NumberUtils::getNumberFigure(($value['rateTax']['supply'] / 100) * $minusRealTaxPrice, '0.1', 'round');
                        $divisionMinusVatPrice = NumberUtils::getNumberFigure(($value['rateTax']['vat'] / 100) * $minusRealTaxPrice, '0.1', 'round');
                        $divisionMinusFreePrice = NumberUtils::getNumberFigure(($value['rateTax']['free'] / 100) * $minusRealTaxPrice, '0.1', 'round');
                        list($divisionMinusSupplyPrice, $divisionMinusVatPrice, $divisionMinusFreePrice) = $this->getRealTaxBalance($minusRealTaxPrice, $divisionMinusSupplyPrice, $divisionMinusVatPrice, $divisionMinusFreePrice);

                        // 실제 환불되는 realtax 총 금액
                        $refundRealTaxData['realTaxSupplyPrice'] += $divisionMinusSupplyPrice;
                        $refundRealTaxData['realTaxVatPrice'] += $divisionMinusVatPrice;
                        $refundRealTaxData['realTaxFreePrice'] += $divisionMinusFreePrice;
                    }

                    // 업데이트 할 데이터
                    $updateEtcData['realTaxSupplyGoodsPrice'] = $value['info']['realTaxSupplyGoodsPrice'] + $plusRealTaxSupplyPrice - gd_isset($divisionMinusSupplyPrice, 0);
                    $updateEtcData['realTaxVatGoodsPrice'] = $value['info']['realTaxVatGoodsPrice'] + $plusRealTaxVatPrice - gd_isset($divisionMinusVatPrice, 0);
                    $updateEtcData['realTaxFreeGoodsPrice'] = $value['info']['realTaxFreeGoodsPrice'] + $plusRealTaxFreePrice - gd_isset($divisionMinusFreePrice, 0);
                    $updateEtcData['divisionUseDeposit'] = $value['info']['divisionUseDeposit'] + $plusTotalUseDeposit - $minusUseDeposit;
                    $updateEtcData['divisionUseMileage'] = $value['info']['divisionUseMileage'] + $plusTotalUseMileage - $minusUseMileage;
                } else {
                    // realtax 금액 분배
                    $divisionPlusSupplyPrice = NumberUtils::getNumberFigure(($value['rate'] / 100) * $moveRealTaxData['plus']['realTaxSupplyPrice'], '0.1', 'round');
                    $plusRealTaxSupplyPrice -= $divisionPlusSupplyPrice;
                    $divisionPlusVatPrice = NumberUtils::getNumberFigure(($value['rate'] / 100) * $moveRealTaxData['plus']['realTaxVatPrice'], '0.1', 'round');
                    $plusRealTaxVatPrice -= $divisionPlusVatPrice;
                    $divisionPlusFreePrice = NumberUtils::getNumberFigure(($value['rate'] / 100) * $moveRealTaxData['plus']['realTaxFreeGoodsPrice'], '0.1', 'round');
                    $plusRealTaxFreePrice -= $divisionPlusFreePrice;

                    // 부가결제금액 분배
                    list($divisionPlusDeposit, $divisionPlusMileage) = $this->divideAddPaymentPrice($value['rate'], $moveAddPaymentData['deposit'], $moveAddPaymentData['mileage']);
                    $plusTotalUseDeposit -= $divisionPlusDeposit;
                    $plusTotalUseMileage -= $divisionPlusMileage;

                    //차감되지 않은 부가결제금액 차감
                    list($divisionMinusDeposit, $divisionMinusMileage) = $this->divideAddPaymentPrice($value['rate'], $tmpRefundGoodsUseDeposit, $tmpRefundGoodsUseMileage);
                    $minusUseDeposit -= $divisionMinusDeposit;
                    $minusUseMileage -= $divisionMinusMileage;

                    //차감되지 않은 realtax 차감
                    if ($tmpRefundGoodsSettlePrice > 0) {
                        $divisionMinusRealPrice = $this->divideRefundMinusSettlePrice($value['rate'], $tmpRefundGoodsSettlePrice, ($tmpRefundGoodsUseDeposit + $tmpRefundGoodsUseMileage), ($divisionMinusDeposit + $divisionMinusMileage));
                        $minusRealTaxPrice -= $divisionMinusRealPrice;

                        $divisionMinusSupplyPrice = NumberUtils::getNumberFigure(($value['rateTax']['supply'] / 100) * $divisionMinusRealPrice, '0.1', 'round');
                        $divisionMinusVatPrice = NumberUtils::getNumberFigure(($value['rateTax']['vat'] / 100) * $divisionMinusRealPrice, '0.1', 'round');
                        $divisionMinusFreePrice = NumberUtils::getNumberFigure(($value['rateTax']['free'] / 100) * $divisionMinusRealPrice, '0.1', 'round');
                        list($divisionMinusSupplyPrice, $divisionMinusVatPrice, $divisionMinusFreePrice) = $this->getRealTaxBalance($divisionMinusRealPrice, $divisionMinusSupplyPrice, $divisionMinusVatPrice, $divisionMinusFreePrice);

                        // 실제 환불되는 realtax 총 금액
                        $refundRealTaxData['realTaxSupplyPrice'] += $divisionMinusSupplyPrice;
                        $refundRealTaxData['realTaxVatPrice'] += $divisionMinusVatPrice;
                        $refundRealTaxData['realTaxFreePrice'] += $divisionMinusFreePrice;
                    }

                    // 업데이트 할 데이터
                    $updateEtcData['realTaxSupplyGoodsPrice'] = $value['info']['realTaxSupplyGoodsPrice'] + $divisionPlusSupplyPrice - gd_isset($divisionMinusSupplyPrice, 0);
                    $updateEtcData['realTaxVatGoodsPrice'] = $value['info']['realTaxVatGoodsPrice'] + $divisionPlusVatPrice - gd_isset($divisionMinusVatPrice, 0);
                    $updateEtcData['realTaxFreeGoodsPrice'] = $value['info']['realTaxFreeGoodsPrice'] + $divisionPlusFreePrice - gd_isset($divisionMinusFreePrice, 0);
                    $updateEtcData['divisionUseDeposit'] = $value['info']['divisionUseDeposit'] + $divisionPlusDeposit - $divisionMinusDeposit;
                    $updateEtcData['divisionUseMileage'] = $value['info']['divisionUseMileage'] + $divisionPlusMileage - $divisionMinusMileage;
                }

                // --- 환불정보를 업데이트 하기위한 데이터 조합
                $tmpUpdateOrderHandleData[$lastHandleSno]['refundRealTaxPrice'] += (gd_isset($divisionMinusSupplyPrice, 0) + gd_isset($divisionMinusVatPrice, 0) + gd_isset($divisionMinusFreePrice, 0));

                // --- 주문상품정보 업데이트
                $this->updateOrderGoods($updateEtcData, $getData['orderNo'], $value['info']['sno']);

                $index++;

                //debug("남은상품");
                //debug($updateEtcData);

                unset($updateEtcData);
            }
        }

        // *** 5. 환불배송비 처리
        //남은 주문으로 이관되어야 할 realtax 금액
        $moveRealTaxData = [
            'plus' => [
                'realTaxSupplyPrice' => 0,
                'realTaxVatPrice' => 0,
                'realTaxFreePrice' => 0,
            ],
            'minus' => [
                'price' => 0,
            ],
        ];
        // 남은 주문으로 이관되어야 할 부가결제 금액 정보
        $moveAddPaymentData = [
            'deposit' => 0,
            'mileage' => 0,
        ];

        $onlyOverseasDelivery = false;
        $processEnd = [];
        foreach ($orderInfo['goods'] as $sKey => $sVal) {
            foreach ($sVal as $dKey => $dVal) {
                foreach ($dVal as $gKey => $gVal) {

                    if (!in_array($gVal['orderDeliverySno'], $processEnd)) {
                        // 해외배송의 경우 공급사가 다르더라도 배송비조건 일련번호가 같기 때문에 0원으로 오버라이딩 되는 증상이 있다.
                        // 해외상점 주문시 최초 배송비가 들어간 이후에는 처리되지 않도록 강제로 예외처리 (해당 처리 안하면 배송비가 해외상점인 경우 무조건 0원으로 나옴)
                        if ($orderInfo['mallSno'] == DEFAULT_MALL_NUMBER || ($orderInfo['mallSno'] > DEFAULT_MALL_NUMBER && $onlyOverseasDelivery === false)) {
                            $refundDeliveryUseDeposit = 0;
                            $refundDeliveryUseMileage = 0;

                            $gVal['realTaxSupplyDeliveryCharge'] += $moveRealTaxData['plus']['realTaxSupplyPrice'];
                            $gVal['realTaxVatDeliveryCharge'] += $moveRealTaxData['plus']['realTaxVatPrice'];
                            $gVal['realTaxFreeDeliveryCharge'] += $moveRealTaxData['plus']['realTaxFreePrice'];
                            $gVal['divisionDeliveryUseDeposit'] += $moveAddPaymentData['deposit'];
                            $gVal['divisionDeliveryUseMileage'] += $moveAddPaymentData['mileage'];

                            $moveRealTaxData['plus']['realTaxSupplyPrice'] = $moveRealTaxData['plus']['realTaxVatPrice'] = $moveRealTaxData['plus']['realTaxFreePrice'] = 0;
                            $moveAddPaymentData['deposit'] = $moveAddPaymentData['mileage'] = 0;

                            // 실제 남아있는 배송비 (부가결제금액 제외)
                            $realDeliverySettlePrice = $gVal['realTaxSupplyDeliveryCharge'] + $gVal['realTaxVatDeliveryCharge'] + $gVal['realTaxFreeDeliveryCharge'];
                            $deliverySettlePrice = $realDeliverySettlePrice + $gVal['divisionDeliveryUseDeposit'] + $gVal['divisionDeliveryUseMileage'];

                            // 배송비의 과세, 부가세, 면세의 비율
                            $realTaxDeliveryRate = $this->getRealTaxRate($gVal['realTaxSupplyDeliveryCharge'], $gVal['realTaxVatDeliveryCharge'], $gVal['realTaxFreeDeliveryCharge']);
                            if (array_sum($realTaxDeliveryRate) === 0) {
                                if (trim($gVal['deliveryTaxInfo']) !== '') {
                                    $deliveryTaxInfo = explode(STR_DIVISION, $gVal['deliveryTaxInfo']);
                                    if ($deliveryTaxInfo[0] === 't') {
                                        $realTaxDeliveryRate['vat'] = $deliveryTaxInfo[1] / 100;
                                        $realTaxDeliveryRate['supply'] = 1 - $realTaxDeliveryRate['vat'];
                                    } else {
                                        $realTaxDeliveryRate['free'] = 1;
                                    }
                                }
                            }

                            // realtax 계산
                            if ($tmpRefundDeliverySettlePrice > $realDeliverySettlePrice) {
                                // 환불되어야 할 금액이 취소가능 배송비보다 큰 경우 전액취소(환불배송비 입장)

                                // 실제 환불되는 realtax 금액 (환불배송비은 취소 후 남는 값을 저장한다.)
                                $refundDeliveryRealTaxSupplyPrice = 0;
                                $refundDeliveryRealTaxVatPrice = 0;
                                $refundDeliveryRealTaxFreePrice = 0;

                                // 실제 환불되는 realtax 총 금액
                                $refundRealTaxData['realTaxSupplyPrice'] += $gVal['realTaxSupplyDeliveryCharge'];
                                $refundRealTaxData['realTaxVatPrice'] += $gVal['realTaxVatDeliveryCharge'];
                                $refundRealTaxData['realTaxFreePrice'] += $gVal['realTaxFreeDeliveryCharge'];

                                $tmpRefundDeliverySettlePrice -= $realDeliverySettlePrice;
                            } else {
                                // 환불되어야 할 금액이 취소가능 배송비보다 작은경우 부분취소(환불배송비 입장)

                                // 실제 환불되는 realtax 금액
                                $refundDeliveryRealTaxSupplyPrice = NumberUtils::getNumberFigure($tmpRefundDeliverySettlePrice * $realTaxDeliveryRate['supply'], '0.1', 'round');
                                $refundDeliveryRealTaxVatPrice = NumberUtils::getNumberFigure($tmpRefundDeliverySettlePrice * $realTaxDeliveryRate['vat'], '0.1', 'round');
                                $refundDeliveryRealTaxFreePrice = NumberUtils::getNumberFigure($tmpRefundDeliverySettlePrice * $realTaxDeliveryRate['free'], '0.1', 'round');
                                list($refundDeliveryRealTaxSupplyPrice, $refundDeliveryRealTaxVatPrice, $refundDeliveryRealTaxFreePrice) = $this->getRealTaxBalance($tmpRefundDeliverySettlePrice, $refundDeliveryRealTaxSupplyPrice, $refundDeliveryRealTaxVatPrice, $refundDeliveryRealTaxFreePrice);

                                // 실제 환불되는 realtax 총 금액
                                $refundRealTaxData['realTaxSupplyPrice'] += $refundDeliveryRealTaxSupplyPrice;
                                $refundRealTaxData['realTaxVatPrice'] += $refundDeliveryRealTaxVatPrice;
                                $refundRealTaxData['realTaxFreePrice'] += $refundDeliveryRealTaxFreePrice;

                                $refundDeliveryRealTaxSupplyPrice = ($gVal['realTaxSupplyDeliveryCharge'] - $refundDeliveryRealTaxSupplyPrice);
                                $refundDeliveryRealTaxVatPrice = ($gVal['realTaxVatDeliveryCharge'] - $refundDeliveryRealTaxVatPrice);
                                $refundDeliveryRealTaxFreePrice = ($gVal['realTaxFreeDeliveryCharge'] - $refundDeliveryRealTaxFreePrice);

                                $tmpRefundDeliverySettlePrice = 0;
                            }

                            // 부가결제금액 차감 (환불배송비은 취소 후 남는 값을 저장한다.)
                            if ($tmpRefundDeliveryUseDeposit >= $gVal['divisionDeliveryUseDeposit']) {
                                //취소 예치금 금액
                                $refundDeliveryUseDeposit = $gVal['divisionDeliveryUseDeposit'];

                                $restDeliveryUseDeposit = 0;
                                $tmpRefundDeliveryUseDeposit -= $gVal['divisionDeliveryUseDeposit'];
                            } else {
                                //취소 예치금 금액
                                $refundDeliveryUseDeposit = $tmpRefundDeliveryUseDeposit;

                                $restDeliveryUseDeposit = ($gVal['divisionDeliveryUseDeposit'] - $tmpRefundDeliveryUseDeposit);
                                $tmpRefundDeliveryUseDeposit = 0;
                            }
                            if ($tmpRefundDeliveryUseMileage >= $gVal['divisionDeliveryUseMileage']) {
                                //취소 마일리지 금액
                                $refundDeliveryUseMileage = $gVal['divisionDeliveryUseMileage'];

                                $restDeliveryUseMileage = 0;
                                $tmpRefundDeliveryUseMileage -= $gVal['divisionDeliveryUseMileage'];
                            } else {
                                //취소 마일리지 금액
                                $refundDeliveryUseMileage = $tmpRefundDeliveryUseMileage;

                                $restDeliveryUseMileage = ($gVal['divisionDeliveryUseMileage'] - $tmpRefundDeliveryUseMileage);
                                $tmpRefundDeliveryUseMileage = 0;
                            }

                            $totalRestDeliveryDeposit[$gVal['orderDeliverySno']] += $restDeliveryUseDeposit;
                            $totalRestDeliveryMileage[$gVal['orderDeliverySno']] += $restDeliveryUseMileage;

                            $totalRefundDeliveryInsuranceFee += $gVal['deliveryInsuranceFee'];

                            if ($getData['refund'][$gVal['handleSno']]['refundDeliveryCharge'] > 0 && (int)$getData['refund'][$gVal['handleSno']]['refundDeliveryCharge'] === (int)$deliverySettlePrice) {
                                $moveAddPaymentData['deposit'] += gd_isset($restDeliveryUseDeposit, 0);
                                $moveAddPaymentData['mileage'] += gd_isset($restDeliveryUseMileage, 0);
                                $moveRealTaxData['plus']['realTaxSupplyPrice'] += $refundDeliveryRealTaxSupplyPrice;
                                $moveRealTaxData['plus']['realTaxVatPrice'] += $refundDeliveryRealTaxVatPrice;
                                $moveRealTaxData['plus']['realTaxFreePrice'] += $refundDeliveryRealTaxFreePrice;

                                $restDeliveryUseDeposit = $restDeliveryUseMileage = 0;
                                $refundDeliveryRealTaxSupplyPrice = $refundDeliveryRealTaxVatPrice = $refundDeliveryRealTaxFreePrice = 0;
                            } else {
                                //차감할 현금금액이 남았고 차감할 부가결제금액이 남지 않은 상태이거나
                                if ($tmpRefundDeliverySettlePrice > 0 && ($tmpRefundDeliveryUseDeposit + $tmpRefundDeliveryUseMileage) <= 0) {
                                    //남은 부가결제 금액이 차감할 현금금액보다 같거나 큰 경우 남은 상품 혹은 뒤에 처리할 환불상품에 적용
                                    if (($restDeliveryUseDeposit + $restDeliveryUseMileage) >= $tmpRefundDeliverySettlePrice) {
                                        $depositRate = NumberUtils::getNumberFigure($restDeliveryUseDeposit / ($restDeliveryUseDeposit + $restDeliveryUseMileage), '0.001', 'round');
                                        $moveDeposit = NumberUtils::getNumberFigure($tmpRefundDeliverySettlePrice * $depositRate, '0.1', 'round');
                                        $moveMileage = ($restDeliveryUseDeposit + $restDeliveryUseMileage) - gd_isset($moveDeposit, 0);

                                        $moveAddPaymentData['deposit'] += gd_isset($moveDeposit, 0);
                                        $moveAddPaymentData['mileage'] += gd_isset($moveMileage, 0);
                                        $restDeliveryUseDeposit -= $moveDeposit;
                                        $restDeliveryUseMileage -= $moveMileage;
                                    }
                                }
                                //차감할 예치금금액이 남았고 차감할 현금금액이 없는경우
                                if ($tmpRefundDeliverySettlePrice <= 0 && $tmpRefundDeliveryUseDeposit > 0) {
                                    if (($refundDeliveryRealTaxSupplyPrice + $refundDeliveryRealTaxVatPrice + $refundDeliveryRealTaxFreePrice) >= $tmpRefundDeliveryUseDeposit) {
                                        $realTaxDeliveryRate = $this->getRealTaxRate($gVal['realTaxSupplyDeliveryCharge'], $gVal['realTaxVatDeliveryCharge'], $gVal['realTaxFreeDeliveryCharge']);
                                        if (array_sum($realTaxDeliveryRate) === 0) {
                                            if (trim($gVal['deliveryTaxInfo']) !== '') {
                                                $deliveryTaxInfo = explode(STR_DIVISION, $gVal['deliveryTaxInfo']);
                                                if ($deliveryTaxInfo[0] === 't') {
                                                    $realTaxDeliveryRate['vat'] = $deliveryTaxInfo[1] / 100;
                                                    $realTaxDeliveryRate['supply'] = 1 - $realTaxDeliveryRate['vat'];
                                                } else {
                                                    $realTaxDeliveryRate['free'] = 1;
                                                }
                                            }
                                        }

                                        $diffDivisionSupplyPrice = NumberUtils::getNumberFigure($realTaxDeliveryRate['supply'] * $tmpRefundDeliveryUseDeposit, '0.1', 'round');
                                        $diffDivisionVatPrice = NumberUtils::getNumberFigure($realTaxDeliveryRate['vat'] * $tmpRefundDeliveryUseDeposit, '0.1', 'round');
                                        $diffDivisionFreePrice = NumberUtils::getNumberFigure($realTaxDeliveryRate['free'] * $tmpRefundDeliveryUseDeposit, '0.1', 'round');
                                        list($diffDivisionSupplyPrice, $diffDivisionVatPrice, $diffDivisionFreePrice) = $this->getRealTaxBalance($tmpRefundDeliveryUseDeposit, $diffDivisionSupplyPrice, $diffDivisionVatPrice, $diffDivisionFreePrice);

                                        if ($refundDeliveryRealTaxSupplyPrice < $diffDivisionSupplyPrice) {
                                            $diffDivisionSupplyPrice -= 1;
                                            if ($refundDeliveryRealTaxVatPrice >= $refundDeliveryRealTaxFreePrice) {
                                                $diffDivisionVatPrice += 1;
                                            } else {
                                                $diffDivisionFreePrice += 1;
                                            }
                                        }

                                        $moveRealTaxData['plus']['realTaxSupplyPrice'] += $diffDivisionSupplyPrice;
                                        $moveRealTaxData['plus']['realTaxVatPrice'] += $diffDivisionVatPrice;
                                        $moveRealTaxData['plus']['realTaxFreePrice'] += $diffDivisionFreePrice;

                                        $refundDeliveryRealTaxSupplyPrice -= $diffDivisionSupplyPrice;
                                        $refundDeliveryRealTaxVatPrice -= $diffDivisionVatPrice;
                                        $refundDeliveryRealTaxFreePrice -= $diffDivisionFreePrice;
                                    }
                                }
                                if ($tmpRefundDeliverySettlePrice <= 0 && $tmpRefundDeliveryUseMileage > 0) {
                                    if (($refundDeliveryRealTaxSupplyPrice + $refundDeliveryRealTaxVatPrice + $refundDeliveryRealTaxFreePrice) >= $tmpRefundDeliveryUseMileage) {
                                        $realTaxDeliveryRate = $this->getRealTaxRate($gVal['realTaxSupplyDeliveryCharge'], $gVal['realTaxVatDeliveryCharge'], $gVal['realTaxFreeDeliveryCharge']);
                                        if (array_sum($realTaxDeliveryRate) === 0) {
                                            if (trim($gVal['deliveryTaxInfo']) !== '') {
                                                $deliveryTaxInfo = explode(STR_DIVISION, $gVal['deliveryTaxInfo']);
                                                if ($deliveryTaxInfo[0] === 't') {
                                                    $realTaxDeliveryRate['vat'] = $deliveryTaxInfo[1] / 100;
                                                    $realTaxDeliveryRate['supply'] = 1 - $realTaxDeliveryRate['vat'];
                                                } else {
                                                    $realTaxDeliveryRate['free'] = 1;
                                                }
                                            }
                                        }

                                        $diffDivisionSupplyPrice = NumberUtils::getNumberFigure($realTaxDeliveryRate['supply'] * $tmpRefundDeliveryUseMileage, '0.1', 'round');
                                        $diffDivisionVatPrice = NumberUtils::getNumberFigure($realTaxDeliveryRate['vat'] * $tmpRefundDeliveryUseMileage, '0.1', 'round');
                                        $diffDivisionFreePrice = NumberUtils::getNumberFigure($realTaxDeliveryRate['free'] * $tmpRefundDeliveryUseMileage, '0.1', 'round');
                                        list($diffDivisionSupplyPrice, $diffDivisionVatPrice, $diffDivisionFreePrice) = $this->getRealTaxBalance($tmpRefundDeliveryUseMileage, $diffDivisionSupplyPrice, $diffDivisionVatPrice, $diffDivisionFreePrice);

                                        list($diffDivisionSupplyPrice, $diffDivisionVatPrice, $diffDivisionFreePrice) = $this->getRealTaxMaxCheck($refundDeliveryRealTaxSupplyPrice, $refundDeliveryRealTaxVatPrice, $refundDeliveryRealTaxFreePrice, $diffDivisionSupplyPrice, $diffDivisionVatPrice, $diffDivisionFreePrice);

                                        $moveRealTaxData['plus']['realTaxSupplyPrice'] += $diffDivisionSupplyPrice;
                                        $moveRealTaxData['plus']['realTaxVatPrice'] += $diffDivisionVatPrice;
                                        $moveRealTaxData['plus']['realTaxFreePrice'] += $diffDivisionFreePrice;

                                        $refundDeliveryRealTaxSupplyPrice -= $diffDivisionSupplyPrice;
                                        $refundDeliveryRealTaxVatPrice -= $diffDivisionVatPrice;
                                        $refundDeliveryRealTaxFreePrice -= $diffDivisionFreePrice;
                                    }
                                }
                            }

                            // 환불 주문배송 정보 업데이트
                            $updateOrderDeliveryData = [
                                'realTaxSupplyDeliveryCharge' => $refundDeliveryRealTaxSupplyPrice,
                                'realTaxVatDeliveryCharge' => $refundDeliveryRealTaxVatPrice,
                                'realTaxFreeDeliveryCharge' => $refundDeliveryRealTaxFreePrice,
                                'divisionDeliveryUseDeposit' => $restDeliveryUseDeposit, // 남아있어야 할 예치금
                                'divisionDeliveryUseMileage' => $restDeliveryUseMileage, // 남아 있어야 할 마일리지
                                'deliveryInsuranceFee' => 0, //해외배송보험
                            ];
                            //debug("환불배송");
                            //debug($updateOrderDeliveryData);
                            $this->updateOrderDelivery($updateOrderDeliveryData, $getData['orderNo'], $gVal['orderDeliverySno']);

                            // 사용 예치금, 마일리지 부가결제 수수료
                            $refundUseDepositDeliveryCommission = 0;
                            $refundUseMileageDeliveryCommission = 0;
                            if ($tmpRefundUseDepositCommission > 0 && $refundDeliveryUseDeposit > 0) {
                                if ($tmpRefundUseDepositCommission >= $refundDeliveryUseDeposit) {
                                    $refundUseDepositDeliveryCommission = $refundDeliveryUseDeposit;
                                    $tmpRefundUseDepositCommission -= $refundDeliveryUseDeposit;
                                } else {
                                    $refundUseDepositDeliveryCommission = $tmpRefundUseDepositCommission;
                                    $tmpRefundUseDepositCommission = 0;
                                }
                            }
                            if ($tmpRefundUseMileageCommission > 0 && $refundDeliveryUseMileage > 0) {
                                if ($tmpRefundUseMileageCommission >= $refundDeliveryUseMileage) {
                                    $refundUseMileageDeliveryCommission = $refundDeliveryUseMileage;
                                    $tmpRefundUseMileageCommission -= $refundDeliveryUseMileage;
                                } else {
                                    $refundUseMileageDeliveryCommission = $tmpRefundUseMileageCommission;
                                    $tmpRefundUseMileageCommission = 0;
                                }
                            }

                            // 환불정보를 업데이트 하기 위한 데이터 조합
                            $tmpUpdateOrderHandleData[$gVal['handleSno']]['refundDeliveryUseDeposit'] = $refundDeliveryUseDeposit;
                            $tmpUpdateOrderHandleData[$gVal['handleSno']]['refundDeliveryUseMileage'] = $refundDeliveryUseMileage;
                            $tmpUpdateOrderHandleData[$gVal['handleSno']]['refundDeliveryInsuranceFee'] = $gVal['deliveryInsuranceFee'];
                            $tmpUpdateOrderHandleData[$gVal['handleSno']]['refundUseDepositCommission'] += $refundUseDepositDeliveryCommission;
                            $tmpUpdateOrderHandleData[$gVal['handleSno']]['refundUseMileageCommission'] += $refundUseMileageDeliveryCommission;

                            $processEnd[] = $gVal['orderDeliverySno'];

                            // 해외배송비
                            $onlyOverseasDelivery = true;
                        }
                    }
                }
            }
        }

        // *** 6. 남은배송비에 분배처리
        if (count($orderEtcInfo['delivery']) > 0) {
            // 남은 상품이 나눠가져야 할 realtax 금액
            $plusRealTaxSupplyPrice = $moveRealTaxData['plus']['realTaxSupplyPrice'];
            $plusRealTaxVatPrice = $moveRealTaxData['plus']['realTaxVatPrice'];
            $plusRealTaxFreePrice = $moveRealTaxData['plus']['realTaxFreePrice'];
            // 남은 상품이 가져나눠야 할 부가결제 금액
            $plusTotalUseDeposit = $moveAddPaymentData['deposit'];
            $plusTotalUseMileage = $moveAddPaymentData['mileage'];

            //차감이 되지 않은 realtax 금액이 있다면 남은상품에서 차감해줘야 함.
            $minusRealTaxPrice = $tmpRefundDeliverySettlePrice;
            $minusUseDeposit = $tmpRefundDeliveryUseDeposit;
            $minusUseMileage = $tmpRefundDeliveryUseMileage;

            $index = 1;
            foreach ($orderEtcInfo['delivery'] as $key => $value) {
                $divisionMinusSupplyPrice = $divisionMinusVatPrice = $divisionMinusFreePrice = 0;
                if (count($orderEtcInfo['delivery']) === $index) {
                    //차감되지 않은 realtax 차감
                    if ($tmpRefundDeliverySettlePrice > 0) {
                        $divisionMinusSupplyPrice = NumberUtils::getNumberFigure(($value['rateTax']['supply'] / 100) * $minusRealTaxPrice, '0.1', 'round');
                        $divisionMinusVatPrice = NumberUtils::getNumberFigure(($value['rateTax']['vat'] / 100) * $minusRealTaxPrice, '0.1', 'round');
                        $divisionMinusFreePrice = NumberUtils::getNumberFigure(($value['rateTax']['free'] / 100) * $minusRealTaxPrice, '0.1', 'round');
                        list($divisionMinusSupplyPrice, $divisionMinusVatPrice, $divisionMinusFreePrice) = $this->getRealTaxBalance($minusRealTaxPrice, $divisionMinusSupplyPrice, $divisionMinusVatPrice, $divisionMinusFreePrice);

                        // 실제 환불되는 realtax 총 금액
                        $refundRealTaxData['realTaxSupplyPrice'] += $divisionMinusSupplyPrice;
                        $refundRealTaxData['realTaxVatPrice'] += $divisionMinusVatPrice;
                        $refundRealTaxData['realTaxFreePrice'] += $divisionMinusFreePrice;
                    }

                    // 업데이트 할 데이터
                    $updateEtcData['realTaxSupplyDeliveryCharge'] = $value['info']['realTaxSupplyDeliveryCharge'] + $plusRealTaxSupplyPrice - gd_isset($divisionMinusSupplyPrice, 0);
                    $updateEtcData['realTaxVatDeliveryCharge'] = $value['info']['realTaxVatDeliveryCharge'] + $plusRealTaxVatPrice - gd_isset($divisionMinusVatPrice, 0);
                    $updateEtcData['realTaxFreeDeliveryCharge'] = $value['info']['realTaxFreeDeliveryCharge'] + $plusRealTaxFreePrice - gd_isset($divisionMinusFreePrice, 0);
                    $updateEtcData['divisionDeliveryUseDeposit'] = $value['info']['divisionDeliveryUseDeposit'] + $plusTotalUseDeposit - $minusUseDeposit;
                    $updateEtcData['divisionDeliveryUseMileage'] = $value['info']['divisionDeliveryUseMileage'] + $plusTotalUseMileage - $minusUseMileage;
                } else {
                    // realtax 금액 분배
                    $divisionPlusSupplyPrice = NumberUtils::getNumberFigure(($value['rate'] / 100) * $moveRealTaxData['plus']['realTaxSupplyPrice'], '0.1', 'round');
                    $plusRealTaxSupplyPrice -= $divisionPlusSupplyPrice;
                    $divisionPlusVatPrice = NumberUtils::getNumberFigure(($value['rate'] / 100) * $moveRealTaxData['plus']['realTaxVatPrice'], '0.1', 'round');
                    $plusRealTaxVatPrice -= $divisionPlusVatPrice;
                    $divisionPlusFreePrice = NumberUtils::getNumberFigure(($value['rate'] / 100) * $moveRealTaxData['plus']['realTaxFreePrice'], '0.1', 'round');
                    $plusRealTaxFreePrice -= $divisionPlusFreePrice;

                    list($divisionPlusDeposit, $divisionPlusMileage) = $this->divideAddPaymentPrice($value['rate'], $moveAddPaymentData['deposit'], $moveAddPaymentData['mileage']);
                    $plusTotalUseDeposit -= $divisionPlusDeposit;
                    $plusTotalUseMileage -= $divisionPlusMileage;

                    //차감되지 않은 부가결제금액 차감
                    list($divisionMinusDeposit, $divisionMinusMileage) = $this->divideAddPaymentPrice($value['rate'], $tmpRefundDeliveryUseDeposit, $tmpRefundDeliveryUseMileage);
                    $minusUseDeposit -= $divisionMinusDeposit;
                    $minusUseMileage -= $divisionMinusMileage;

                    //차감되지 않은 realtax 차감
                    if ($tmpRefundDeliverySettlePrice > 0) {
                        $divisionMinusRealPrice = $this->divideRefundMinusSettlePrice($value['rate'], $tmpRefundDeliverySettlePrice, ($tmpRefundDeliveryUseDeposit + $tmpRefundDeliveryUseMileage), ($divisionMinusDeposit + $divisionMinusMileage));
                        $minusRealTaxPrice -= $divisionMinusRealPrice;

                        $divisionMinusSupplyPrice = NumberUtils::getNumberFigure(($value['rateTax']['supply'] / 100) * $divisionMinusRealPrice, '0.1', 'round');
                        $divisionMinusVatPrice = NumberUtils::getNumberFigure(($value['rateTax']['vat'] / 100) * $divisionMinusRealPrice, '0.1', 'round');
                        $divisionMinusFreePrice = NumberUtils::getNumberFigure(($value['rateTax']['free'] / 100) * $divisionMinusRealPrice, '0.1', 'round');
                        list($divisionMinusSupplyPrice, $divisionMinusVatPrice, $divisionMinusFreePrice) = $this->getRealTaxBalance($divisionMinusRealPrice, $divisionMinusSupplyPrice, $divisionMinusVatPrice, $divisionMinusFreePrice);

                        // 실제 환불되는 realtax 총 금액
                        $refundRealTaxData['realTaxSupplyPrice'] += $divisionMinusSupplyPrice;
                        $refundRealTaxData['realTaxVatPrice'] += $divisionMinusVatPrice;
                        $refundRealTaxData['realTaxFreePrice'] += $divisionMinusFreePrice;
                    }

                    // 업데이트 할 데이터
                    $updateEtcData['realTaxSupplyDeliveryCharge'] = $value['info']['realTaxSupplyDeliveryCharge'] + $divisionPlusSupplyPrice - gd_isset($divisionMinusSupplyPrice, 0);
                    $updateEtcData['realTaxVatDeliveryCharge'] = $value['info']['realTaxVatDeliveryCharge'] + $divisionPlusVatPrice - gd_isset($divisionMinusVatPrice, 0);
                    $updateEtcData['realTaxFreeDeliveryCharge'] = $value['info']['realTaxFreeDeliveryCharge'] + $divisionPlusFreePrice - gd_isset($divisionMinusFreePrice, 0);
                    $updateEtcData['divisionDeliveryUseDeposit'] = $value['info']['divisionDeliveryUseDeposit'] + $divisionPlusDeposit - $divisionMinusDeposit;
                    $updateEtcData['divisionDeliveryUseMileage'] = $value['info']['divisionDeliveryUseMileage'] + $divisionPlusMileage - $divisionMinusMileage;
                }

                $totalRestDeliveryDeposit[$value['info']['orderDeliverySno']] += $updateEtcData['divisionDeliveryUseDeposit'];
                $totalRestDeliveryMileage[$value['info']['orderDeliverySno']] += $updateEtcData['divisionDeliveryUseMileage'];

                //debug("남은배송");
                //debug($updateEtcData);

                // --- 주문배송정보 업데이트
                $this->updateOrderDelivery($updateEtcData, $getData['orderNo'], $value['info']['orderDeliverySno']);

                $index++;

                unset($updateEtcData);
            }
        }

        // *** 7. 배송비에 할당된 예치금, 마일리지 금액을 주문 상품에 재분배
        if (count($orderEtcInfo['groupDeliveryGoods']) > 0) {
            $tmpTotalRestDeliveryDeposit = $totalRestDeliveryDeposit;
            $tmpTotalRestDeliveryMileage = $totalRestDeliveryMileage;

            foreach ($orderEtcInfo['groupDeliveryGoods'] as $dKey => $dVal) {
                $index = 1;
                foreach ($dVal as $orderGoodsSno => $gVal) {
                    if (count($orderEtcInfo['groupDeliveryGoods'][$dKey]) === $index) {
                        $updateRestOrderGoodsData['divisionGoodsDeliveryUseDeposit'] = $tmpTotalRestDeliveryDeposit[$dKey];
                        $updateRestOrderGoodsData['divisionGoodsDeliveryUseMileage'] = $tmpTotalRestDeliveryMileage[$dKey];

                        $tmpTotalRestDeliveryDeposit[$dKey] = $tmpTotalRestDeliveryMileage[$dKey] = 0;
                    } else {
                        $divisonDeliveryRate = NumberUtils::getNumberFigure(($gVal['goodsSettlePrice'] / $gVal['divisionTotalSettlePrice']), '0.01', 'round');

                        $updateRestOrderGoodsData['divisionGoodsDeliveryUseDeposit'] = NumberUtils::getNumberFigure($tmpTotalRestDeliveryDeposit[$dKey] * $divisonDeliveryRate, '0.1', 'round');
                        $updateRestOrderGoodsData['divisionGoodsDeliveryUseMileage'] = NumberUtils::getNumberFigure($tmpTotalRestDeliveryMileage[$dKey] * $divisonDeliveryRate, '0.1', 'round');

                        $tmpTotalRestDeliveryDeposit[$dKey] -= $updateRestOrderGoodsData['divisionGoodsDeliveryUseDeposit'];
                        $tmpTotalRestDeliveryMileage[$dKey] -= $updateRestOrderGoodsData['divisionGoodsDeliveryUseMileage'];
                    }

                    // --- 주문상품정보 업데이트
                    $this->updateOrderGoods($updateRestOrderGoodsData, $getData['orderNo'], $orderGoodsSno);

                    $index++;
                }
            }
        }

        // *** 8. 환불정보 업데이트
        $restDeliveryMinusUseDeposit = $tmpRefundDeliveryUseDeposit;
        $restDeliveryMinusUseMileage = $tmpRefundDeliveryUseMileage;
        $cntTmpUpdateOrderHandleData = count($tmpUpdateOrderHandleData);
        if ($cntTmpUpdateOrderHandleData > 0) {
            // 남은배송비에서 차감된 마일리지, 예치금을 환불정보에 포함하여 넣어주는 과정 (상품)
            $index = 1;
            $orderHandleDataRate = [];
            $tmpOrderHandleDataRate = 1;
            foreach ($tmpUpdateOrderHandleData as $orderHandleSno => $handleData) {
                if ($cntTmpUpdateOrderHandleData === $index) {
                    $orderHandleDataRate[$orderHandleSno] = $tmpOrderHandleDataRate;
                } else {
                    $orderHandleDataRate[$orderHandleSno] = NumberUtils::getNumberFigure((1 / $cntTmpUpdateOrderHandleData), '0.001', 'round');
                    $tmpOrderHandleDataRate -= $orderHandleDataRate[$orderHandleSno];
                }

                $index++;
            }

            $index = 1;
            foreach ($tmpUpdateOrderHandleData as $orderHandleSno => $handleData) {
                $refundData = $getData['refund'][$orderHandleSno];
                $refundData['handleCompleteFl'] = 'y'; // 환불완료 변경 플래그
                $refundData['handleDt'] = date('Y-m-d H:i:s'); // 환불완료 변경 플래그
                $refundData['refundGroupCd'] = $handleData['refundGroupCd']; // 환불그룹 코드
                // 환불정보 암호화
                foreach ($getData['info'] as $iKey => $iVal) {
                    $refundData[$iKey] = $iVal;
                }
                $refundData['originGiveMileage'] = array_sum([
                    $handleData['totalRealGoodsMileage'],
                    $handleData['totalRealMemberMileage'],
                    $handleData['totalRealCouponGoodsMileage'],
                    $handleData['totalRealDivisionCouponOrderMileage'],
                ]);

                $originalRefundDeliveryUseDeposit = $handleData['refundDeliveryUseDeposit'];
                $originalRefundDeliveryUseMileage = $handleData['refundDeliveryUseMileage'];

                // 금액에서 , 제거
                $refundData['refundGiveMileage'] = intval(str_replace(',', '', $refundData['refundGiveMileage']));
                $refundData['refundCharge'] = intval(str_replace(',', '', $refundData['refundCharge']));

                // 남은배송비에서 차감된 마일리지, 예치금을 환불정보에 포함하여 넣어주는 과정 (상품)
                if ($tmpRefundDeliveryUseDeposit > 0) {
                    if ($cntTmpUpdateOrderHandleData === $index) {
                        $refundUseDepositGap = $restDeliveryMinusUseDeposit;
                        $handleData['refundDeliveryUseDeposit'] += $restDeliveryMinusUseDeposit;
                        $restDeliveryMinusUseDeposit = 0;
                    } else {
                        $restUseDeposit = NumberUtils::getNumberFigure($orderHandleDataRate[$orderHandleSno] * $tmpRefundDeliveryUseDeposit, '0.1', 'round');
                        $refundUseDepositGap = $restUseDeposit;
                        $handleData['refundDeliveryUseDeposit'] += $restUseDeposit;
                        $restDeliveryMinusUseDeposit -= $restUseDeposit;
                    }

                    // 예치금 부가결제 수수료
                    if ($tmpRefundUseDepositCommission > 0) {
                        if ($tmpRefundUseDepositCommission >= $refundUseDepositGap) {
                            $handleData['refundUseDepositCommission'] += $refundUseDepositGap;
                            $tmpRefundUseDepositCommission -= $refundUseDepositGap;
                        } else {
                            $handleData['refundUseDepositCommission'] += $tmpRefundUseDepositCommission;
                            $tmpRefundUseDepositCommission = 0;
                        }
                    }
                }
                if ($tmpRefundDeliveryUseMileage > 0) {
                    if ($cntTmpUpdateOrderHandleData === $index) {
                        $refundUseMileageGap = $restDeliveryMinusUseMileage;
                        $handleData['refundDeliveryUseMileage'] += $restDeliveryMinusUseMileage;
                        $restDeliveryMinusUseMileage = 0;
                    } else {
                        $restUseMileage = NumberUtils::getNumberFigure($orderHandleDataRate[$orderHandleSno] * $tmpRefundDeliveryUseMileage, '0.1', 'round');
                        $refundUseMileageGap = $restUseMileage;
                        $handleData['refundDeliveryUseMileage'] += $restUseMileage;
                        $restDeliveryMinusUseMileage -= $restUseMileage;
                    }

                    // 마일리지 부가결제 수수료
                    if ($tmpRefundUseMileageCommission > 0) {
                        if ($tmpRefundUseMileageCommission >= $refundUseMileageGap) {
                            $handleData['refundUseMileageCommission'] += $refundUseMileageGap;
                            $tmpRefundUseMileageCommission -= $refundUseMileageGap;
                        } else {
                            $handleData['refundUseMileageCommission'] += $tmpRefundUseMileageCommission;
                            $tmpRefundUseMileageCommission = 0;
                        }
                    }
                }

                //상품, 배송비에 할당된 사용 예치금 , 마일리지 (전체)
                $refundData['refundUseDeposit'] = $handleData['refundGoodsUseDeposit'] + $handleData['refundDeliveryUseDeposit'];
                $refundData['refundUseMileage'] = $handleData['refundGoodsUseMileage'] + $handleData['refundDeliveryUseMileage'];

                // 환불된 해외배송 보험료
                $refundData['refundDeliveryInsuranceFee'] = $handleData['refundDeliveryInsuranceFee'];

                //배송비에만 할당된 사용 예치금, 마일리지 환불 금액
                $refundData['refundDeliveryUseDeposit'] = 0;
                $refundData['refundDeliveryUseMileage'] = 0;
                if ((int)$refundData['refundDeliveryCharge'] > 0) {
                    $refundData['refundDeliveryUseDeposit'] = $originalRefundDeliveryUseDeposit + $tmpRefundDeliveryUseDeposit;
                    $refundData['refundDeliveryUseMileage'] = $originalRefundDeliveryUseMileage + $tmpRefundDeliveryUseMileage;
                    $refundData['refundDeliveryCharge'] -= ($refundData['refundDeliveryUseDeposit'] + $refundData['refundDeliveryUseMileage']);
                }

                // 사용된 예치금, 마일리지 수수료
                $refundData['refundUseDepositCommission'] = $handleData['refundUseDepositCommission'];
                $refundData['refundUseMileageCommission'] = $handleData['refundUseMileageCommission'];

                $refundData['refundPrice'] = $handleData['refundRealTaxPrice'] + $refundData['refundDeliveryCharge'];
                if ($refundData['originGiveMileage'] < $refundData['refundGiveMileage']) {
                    throw new Exception(__('적립마일리지는 차감시킬 마일리지보다 클 수 없습니다.('.$refundData['originGiveMileage'].' < '.$refundData['refundGiveMileage'].')'));
                }

                //debug("환불정보");
                //debug($refundData);
                $this->updateOrderHandle($refundData, $orderHandleSno);
                unset($refundData);

                $index++;
            }
            unset($tmpUpdateOrderHandleData);
        }

        // *** 주문서 정보 저장

        // 실환불금액과 사용자 지정 환불금액의 일치 여부 확인
        $refundCashSum = array_sum([
            $getData['info']['completeCashPrice'],
            $getData['info']['completePgPrice'],
            $getData['info']['completeDepositPrice'],
            $getData['info']['completeMileagePrice'],
            gd_isset($getData['info']['refundDeliveryInsuranceFee'], 0),
        ]);

        if ((array_sum($refundRealTaxData) + $totalRefundDeliveryInsuranceFee) != $refundCashSum) {
            throw new Exception(__('실 환불금액과 입력하신 환불 금액 설정 금액이 일치하지 않습니다.'));
        }

        // 상단에서 취소 업데이트된 데이터를 다시 쿼리해 실 복합과세 금액 재 계산 (전체 주문의 실제 남아있는 금액) : 주문업데이트 데이터
        $orderComplexTax = $orderAdmin->getOrderRealComplexTax($getData['orderNo'], null, true);
        $updateOrderData = [
            'realTaxSupplyPrice' => $orderComplexTax['taxSupply'],
            'realTaxVatPrice' => $orderComplexTax['taxVat'],
            'realTaxFreePrice' => $orderComplexTax['taxFree'],
            'adminMemo' => $getData['order']['adminMemo'],
        ];
        $this->updateOrder($getData['orderNo'], 'n', $updateOrderData);

        unset($updateOrderData);

        // *** 부가정보 처리

        //관리자 선택에 따른 쿠폰 복원 처리
        $this->restoreRefundCoupon($getData);

        // 사용된 예치금 일괄 복원
        $this->restoreRefundUseDeposit($getData, $orderInfo, $orderRefundInfo);

        // 사용된 마일리지 일괄 복원
        $this->restoreRefundUseMileage($getData, $orderInfo, $orderRefundInfo);

        // 적립 마일리지 차감
        if($orderInfo['memNo'] != 0) {
            $this->minusRefundGiveMileage($getData, $orderInfo, $orderRefundInfo);
        }

        // 사은품 지급안함 처리
        if ($getData['returnGiftFl'] === 'n') {
            $giftData = [];
            foreach ($getData['returnGift'] as $orderGiftNo => $returnFl) {
                if ($returnFl == 'n') {
                    $giftData[] = $orderGiftNo;
                }
            }
            if(count($giftData) > 0){
                $this->setReturnGift($getData['orderNo'], $giftData, 'r');
            }
            unset($giftData);
        }

        // 환불금액 처리
        $returnError = $this->completeRefundPrice($getData, $orderInfo, $refundRealTaxData);
        if (trim($returnError) !== '') {
            throw new Exception(__($returnError));
        }
    }

    public function setRefundCompleteOrderGoodsNew($getData, $autoProcess)
    {
        $goods = \App::load(\Component\Goods\Goods::class);
        $orderAdmin = App::load(\Component\Order\OrderAdmin::class);

        // 환불수단에 맞지않는 금액값이 넘어오면 리턴처리
        if ($getData['info']['refundMethod'] == '현금환불') {
            if ($getData['info']['completePgPrice'] > 0 || $getData['info']['completeDepositPrice'] > 0 || $getData['info']['completeMileagePrice'] > 0) {
                throw new Exception(__('현금환불이 아닌 환불 금액정보가있습니다. 다시 확인해주세요.'));
            }
        }
        if ($getData['info']['refundMethod'] == 'PG환불') {
            if ($getData['info']['completeCashPrice'] > 0 || $getData['info']['completeDepositPrice'] > 0 || $getData['info']['completeMileagePrice'] > 0) {
                throw new Exception(__('PG환불이 아닌 환불 금액정보가있습니다. 다시 확인해주세요.'));
            }
        }
        if ($getData['info']['refundMethod'] == '예치금환불') {
            if ($getData['info']['completeCashPrice'] > 0 || $getData['info']['completePgPrice'] > 0 || $getData['info']['completeMileagePrice'] > 0) {
                throw new Exception(__('예치금환불이 아닌 환불 금액정보가있습니다. 다시 확인해주세요.'));
            }
        }
        if ($getData['info']['refundMethod'] == '기타환불') {
            if ($getData['info']['completeCashPrice'] > 0 || $getData['info']['completePgPrice'] > 0 || $getData['info']['completeDepositPrice'] > 0) {
                throw new Exception(__('기타환불이 아닌 환불 금액정보가있습니다. 다시 확인해주세요.'));
            }
        }

        unset($getData['mode']);

        //부가결제 수수료 초기화
        if ($getData['addPaymentChargeUseFl'] !== 'y') {
            unset($getData['refundUseDepositCommissionWithFl'], $getData['refundUseMileageCommissionWithFl']);
            unset($getData['refundUseDepositCommission'], $getData['refundUseMileageCommission']);
        }

        // 환불 상세보기에서의 검색 조건 설정
        $handleSno = null;
        $excludeStatus = null;
        if ($getData['isAll'] != 1 && $getData['handleSno'] != 0) {
            $handleSno = $getData['handleSno'];
        }

        // 접근권한, 형식체크
        $returnError = '';
        $returnError = $this->checkRefundCompleteAccess($getData);
        if (trim($returnError) !== '') {
            throw new Exception($returnError);
        }

        // 환불 완료가 아닌 모든상태의 정보를 가져옴
        $orderInfo = $orderAdmin->getOrderView($getData['orderNo'], null, null, null, ['r3', 'e1', 'e2', 'e3', 'e4', 'e5']);

        if ($orderInfo['mallSno'] > DEFAULT_MALL_NUMBER && $handleSno !== null) {
            // 해외 상점의 경우 handleSno를 null로 처리해 무조건 부분취소 아닌 전체로 처리되게 한다.
            throw new Exception(__('해외상점 취소/교환/반품/환불은 전체 처리만 가능합니다.'));
        }
        if ((int)$orderInfo['memNo'] < 1 && (int)$getData['info']['completeDepositPrice'] > 0) {
            throw new Exception(__('비회원 주문건은 예치금 환불수단을 사용 할 수 없습니다.'));
        }
        if ($orderInfo['settleKind'] === 'gb' && ((int)$getData['info']['completePgPrice'] > 0 || $getData['info']['refundMethod'] === 'PG환불')) {
            throw new Exception(__('무통장입금 주문건은 PG환불 수단으로 환불을 진행할 수 없습니다.'));
        }

        // *** 1. 데이터 백업
        $backupReturn = $this->setBackupOrderOriginalData($getData['orderNo'], 'r');
        if($backupReturn === false){
            throw new Exception(__('주문 백업을 실패하였습니다. 관리자에게 문의하세요.'));
        }

        // *** 2. 기존 데이터에 맞게 getData 일부 수정
        $getData['info']['refundGoodsUseDeposit'] = $getData['refundDepositPrice'];
        $getData['info']['refundUseDepositCommission'] = $getData['refundUseDepositCommission'];
        $getData['info']['refundGoodsUseMileage'] = $getData['refundMileagePrice'];
        $getData['info']['refundUseMileageCommission'] = $getData['refundUseMileageCommission'];
        $getData['check']['totalRefundPrice'] = $getData['refundPriceSum'];

        // 이시점에서 divition by zero warning을 막기위해 임시로 error_report값을 변경
        $errorReportingValue = error_reporting();
        error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);

        // *** 3. 환불데이터 만들기
        $orderRefundInfo = array(); // 기존ordeGoods테이블 status값 변경시 임시 데이터 처리
        $orgTotalData = array(); // 원정보(현상태 남아있는 할인정보들)
        $refundData = array(); // 환불요청정보
        $updateData = array(); // 원정보에서 환불요청뺀정보
        $refundTarget = array(); // 환불 대상 ordergoods.sno
        $updateDeliveryData = array(); // 업데이트될 배송정보(es_orderDelivery)
        $refundDeliveryData = array(); // 업데이트될 배송정보(es_orderDelivery) 만들면서 es_orderGoods에 안분값이 들어갈 데이터를 저장하는 용도
        $refundGoodsData = array(); // 환불처리될내용(환불처리될 상품별 정보들)
        $updateGoodsData = array(); // 원정보에서 환불처리될내용 뺀 상품별 정보들(es_orderGoods)
        $updateHandleData = array(); // 업데이트 될 환불처리내용들(es_orderHandle)
        $tempOrderHandle = array(); // es_orderHandle값을 구하기 위한 기준값 기록용
        $updateOrderData = array(); // 업데이트 될 환불처리내용들(es_order)
        $refundEtcInfo = array(); // 환불 기타 정보

        // 계산 기준이 될 정보들 할당
        $orgTotalData['refundPrice'] = $getData['refundGoodsPrice'];
        $orgTotalData['alivePrice'] = $getData['refundAliveGoodsPriceSum'];
        $orgTotalData['totalDeliveryPrice'] = $getData['refundAliveDeliveryPriceSum'];
        $orgTotalData['realTaxSupplyPrice'] = $orderInfo['realTaxSupplyPrice'];
        $orgTotalData['realTaxVatPrice'] = $orderInfo['realTaxVatPrice'];
        $orgTotalData['realTaxFreePrice'] = $orderInfo['realTaxFreePrice'];
        $orgTotalData['deliveryCouponPrice'] = 0;
        foreach ($orderInfo['deliveryPriceInfo'] as $val) {
            $orgTotalData['deliveryCouponPrice'] += $val['iCoupon'];
        }
        if ($getData['info']['refundMethod'] == '현금환불') {
            $orgTotalData['realRefundMethod'] = 'completeCashPrice';
        } elseif ($getData['info']['refundMethod'] == 'PG환불') {
            $orgTotalData['realRefundMethod'] = 'completePgPrice';
        } elseif ($getData['info']['refundMethod'] == '예치금환불') {
            $orgTotalData['realRefundMethod'] = 'completeDepositPrice';
        } elseif ($getData['info']['refundMethod'] == '기타환불') {
            $orgTotalData['realRefundMethod'] = 'completeMileagePrice';
        } elseif ($getData['info']['refundMethod'] == '복합환불') {
            $orgTotalData['realRefundMethod'] = 'all';
        }
        $orgTotalData['totalRefundCharge'] = 0;
        $orgTotalData['totalRefundChargeTaxSupply'] = 0;
        $orgTotalData['totalRefundChargeTaxVat'] = 0;
        $orgTotalData['totalRefundChargeTaxFree'] = 0;

        // 환불대상값 할당
        $refundData['goodsPrice'] = $getData['refundGoodsPrice'];
        // 취소 마일리지
        $refundData['refundGoodsCouponMileage'] = ($getData['refundGoodsCouponMileageFlag'] == 'T') ? $getData['refundGoodsCouponMileage'] : 0;
        $refundData['refundOrderCouponMileage'] = ($getData['refundOrderCouponMileageFlag'] == 'T') ? $getData['refundOrderCouponMileage'] : 0;
        $refundData['refundGroupMileage'] = ($getData['refundGroupMileageFlag'] == 'T') ? $getData['refundGroupMileage'] : 0;

        // 취소 할인
        $refundData['refundGoodsDcPrice'] = ($getData['refundGoodsDcPriceFlag'] == 'T') ? $getData['refundGoodsDcPrice'] : 0;
        $refundData['refundMemberAddDcPrice'] = ($getData['refundMemberAddDcPriceFlag'] == 'T') ? $getData['refundMemberAddDcPrice'] : 0;
        $refundData['refundMemberOverlapDcPrice'] = ($getData['refundMemberOverlapDcPriceFlag'] == 'T') ? $getData['refundMemberOverlapDcPrice'] : 0;
        $refundData['refundEnuriDcPrice'] = ($getData['refundEnuriDcPriceFlag'] == 'T') ? $getData['refundEnuriDcPrice'] : 0;
        if ($this->myappUseFl) {
            $refundData['refundMyappDcPrice'] = ($getData['refundMyappDcPriceFlag'] == 'T') ? $getData['refundMyappDcPrice'] : 0;
        }
        $refundData['refundGoodsCouponDcPrice'] = ($getData['refundGoodsCouponDcPriceFlag'] == 'T') ? $getData['refundGoodsCouponDcPrice'] : 0;
        $refundData['refundOrderCouponDcPrice'] = ($getData['refundOrderCouponDcPriceFlag'] == 'T') ? $getData['refundOrderCouponDcPrice'] : 0;
        $refundData['refundDcSum'] = $refundData['refundGoodsDcPrice'] + $refundData['refundMemberAddDcPrice'] + $refundData['refundMemberOverlapDcPrice'] + $refundData['refundEnuriDcPrice'] + $refundData['refundMyappDcPrice'] + $refundData['refundGoodsCouponDcPrice'] + $refundData['refundOrderCouponDcPrice'];
        // 배송
        $refundData['refundDeliveryCouponDcPrice'] = $getData['refundDeliveryCouponDcPrice'];
        $refundData['refundDeliverySum'] = 0;
        foreach ($getData['aAliveDeliverySno'] as $val) {
            if (gd_isset($getData['refundDeliveryCharge_' . $val . 'Max'], 0) > 0) { // 환불대상
                $refundData['aRefundDelivery'][$val]['iPrice'] = $getData['refundDeliveryCharge_' . $val];
            } else { // 환불대상에 없는 남은 배송비할당
                $refundData['aRefundDelivery'][$val]['iPrice'] = 0;
            }
            $refundData['refundDeliverySum'] += $refundData['aRefundDelivery'][$val]['iPrice'];
        }
        $tempPerDeliveryCoupon = 0;
        $tempDivisionDeliveryCouponSum = 0;
        //배송비의 경우 배송비 쿠폰값이 있으면 취소 되는 쿠폰값을 미리 안분한다
        foreach ($getData['aAliveDeliverySno'] as $val) {
            if ($refundData['aRefundDelivery'][$val]['iPrice'] > 0) {
                $tempPerDeliveryCoupon = $refundData['aRefundDelivery'][$val]['iPrice'] / $refundData['refundDeliverySum'];
                $refundData['aRefundDelivery'][$val]['iCoupon'] = NumberUtils::getNumberFigure($tempPerDeliveryCoupon * $refundData['refundDeliveryCouponDcPrice'], '0.1', 'round');
            } else {
                $refundData['aRefundDelivery'][$val]['iCoupon'] = 0;
            }
            $tempDivisionDeliveryCouponSum += $refundData['aRefundDelivery'][$val]['iCoupon'];
        }
        $refundData['aRefundDelivery'] = $this->refundDivisionCheck($refundData['aRefundDelivery'], $tempDivisionDeliveryCouponSum, $refundData['refundDeliveryCouponDcPrice'], 'iCoupon');

        // 부가결제
        $refundData['refundDepositPrice'] = $getData['refundDepositPrice'];
        $refundData['refundMileagePrice'] = $getData['refundMileagePrice'];
        $refundData['refundUseDepositCommission'] = gd_isset($getData['refundUseDepositCommission'], 0);
        $refundData['refundUseMileageCommission'] = gd_isset($getData['refundUseMileageCommission'], 0);

        // 환불처리정보
        $refundData['info'] = $getData['info'];

        // 업데이트 대상값 할당
        $updateData['goodsPrice'] = $getData['refundAliveGoodsPriceSum'];
        // 취소 마일리지
        $updateData['refundGoodsCouponMileage'] = ($getData['refundGoodsCouponMileageFlag'] == 'T') ? $getData['refundGoodsCouponMileageOrg'] - $getData['refundGoodsCouponMileage'] : $getData['refundGoodsCouponMileageOrg'];
        $updateData['refundOrderCouponMileage'] = ($getData['refundOrderCouponMileageFlag'] == 'T') ? $getData['refundOrderCouponMileageOrg'] - $getData['refundOrderCouponMileage'] : $getData['refundOrderCouponMileageOrg'];
        $updateData['refundGroupMileage'] = ($getData['refundGroupMileageFlag'] == 'T') ? $getData['refundGroupMileageOrg'] - $getData['refundGroupMileage'] : $getData['refundGroupMileageOrg'];
        // 취소 할인
        $updateData['refundGoodsDcPrice'] = ($getData['refundGoodsDcPriceFlag'] == 'T') ? $getData['refundGoodsDcPriceOrg'] - $getData['refundGoodsDcPrice'] : $getData['refundGoodsDcPriceOrg'];
        $updateData['refundMemberAddDcPrice'] = ($getData['refundMemberAddDcPriceFlag'] == 'T') ? $getData['refundMemberAddDcPriceOrg'] - $getData['refundMemberAddDcPrice'] : $getData['refundMemberAddDcPriceOrg'];
        $updateData['refundMemberOverlapDcPrice'] = ($getData['refundMemberOverlapDcPriceFlag'] == 'T') ? $getData['refundMemberOverlapDcPriceOrg'] - $getData['refundMemberOverlapDcPrice'] : $getData['refundMemberOverlapDcPriceOrg'];
        $updateData['refundEnuriDcPrice'] = ($getData['refundEnuriDcPriceFlag'] == 'T') ? $getData['refundEnuriDcPriceOrg'] - $getData['refundEnuriDcPrice'] : $getData['refundEnuriDcPriceOrg'];
        if ($this->myappUseFl) {
            $updateData['refundMyappDcPrice'] = ($getData['refundMyappDcPriceFlag'] == 'T') ? $getData['refundMyappDcPriceOrg'] - $getData['refundMyappDcPrice'] : $getData['refundMyappDcPriceOrg'];
        }
        $updateData['refundGoodsCouponDcPrice'] = ($getData['refundGoodsCouponDcPriceFlag'] == 'T') ? $getData['refundGoodsCouponDcPriceOrg'] - $getData['refundGoodsCouponDcPrice'] : $getData['refundGoodsCouponDcPriceOrg'];
        $updateData['refundOrderCouponDcPrice'] = ($getData['refundOrderCouponDcPriceFlag'] == 'T') ? $getData['refundOrderCouponDcPriceOrg'] - $getData['refundOrderCouponDcPrice'] : $getData['refundOrderCouponDcPriceOrg'];
        $updateData['refundDcSum'] = $updateData['refundGoodsDcPrice'] + $updateData['refundMemberAddDcPrice'] + $updateData['refundMemberOverlapDcPrice'] + $updateData['refundEnuriDcPrice'] + $updateData['refundMyappDcPrice'] + $updateData['refundGoodsCouponDcPrice'] + $updateData['refundOrderCouponDcPrice'];
        // 배송
        $updateData['refundDeliveryCouponDcPrice'] = $orgTotalData['deliveryCouponPrice'] - $getData['refundDeliveryCouponDcPrice'];
        $updateData['refundDeliverySum'] = 0;
        foreach ($getData['aAliveDeliverySno'] as $val) {
            if (gd_isset($getData['refundDeliveryCharge_' . $val . 'Max'], 0) > 0) { // 환불대상
                $updateData['aRefundDelivery'][$val]['iPrice'] = $getData['refundDeliveryCharge_' . $val . 'Max'] - $getData['refundDeliveryCharge_' . $val];
            } else { // 환불대상에 없는 남은 배송비할당
                $updateData['aRefundDelivery'][$val]['iPrice'] = $orderInfo['deliveryPriceInfo'][$val]['iPrice'];
            }
            $updateData['refundDeliverySum'] += $updateData['aRefundDelivery'][$val]['iPrice'];
        }
        $tempDivisionDeliveryCouponSum = 0;
        //배송비의 경우 배송비 쿠폰값이 있으면 취소 되는 쿠폰값을 미리 안분한다
        foreach ($getData['aAliveDeliverySno'] as $val) {
            if ($updateData['aRefundDelivery'][$val]['iPrice'] > 0) {
                $tempPerDeliveryCoupon = 0;
                $tempPerDeliveryCoupon = $updateData['aRefundDelivery'][$val]['iPrice'] / $updateData['refundDeliverySum'];
                $updateData['aRefundDelivery'][$val]['iCoupon'] = NumberUtils::getNumberFigure($tempPerDeliveryCoupon * $updateData['refundDeliveryCouponDcPrice'], '0.1', 'round');
            } else {
                $updateData['aRefundDelivery'][$val]['iCoupon'] = 0;
            }
            $tempDivisionDeliveryCouponSum += $updateData['aRefundDelivery'][$val]['iCoupon'];
        }
        $updateData['aRefundDelivery'] = $this->refundDivisionCheck($updateData['aRefundDelivery'], $tempDivisionDeliveryCouponSum, $updateData['refundDeliveryCouponDcPrice'], 'iCoupon');

        // 부가결제
        $updateData['refundDepositPrice'] = $getData['refundDepositPriceTotal'] - $getData['refundDepositPrice'];
        $updateData['refundMileagePrice'] = $getData['refundMileagePriceTotal'] - $getData['refundMileagePrice'];

        // 환불처리정보
        $updateData['info'] = $getData['info'];

        // 환불그룹 코드
        $refundGroupCd = $orderAdmin->getMaxRefundGroupCd($getData['orderNo']);

        foreach ($getData['refund'] as $handleSno => $aVal) {
            // 환불 처리대상 sno 배열
            $refundTarget[] = $aVal['sno'];

            // 환불 수수료 합계
            $orgTotalData['totalRefundCharge'] += $getData['refundCharge' . $handleSno];

            // orderHandle 공통
            $updateHandleData[$handleSno]['handleCompleteFl'] = 'y'; // 환불완료 변경 플래그
            $updateHandleData[$handleSno]['handleReason'] = $getData['info']['handleReason'];
            $updateHandleData[$handleSno]['handleDetailReason'] = $getData['info']['handleDetailReason'];
            $updateHandleData[$handleSno]['handleDetailReasonShowFl'] = isset($getData['info']['handleDetailReasonShowFl']) ? 'y' : 'n';
            $updateHandleData[$handleSno]['handleDt'] = date('Y-m-d H:i:s'); // 환불완료 변경 플래그
            $updateHandleData[$handleSno]['refundGroupCd'] = $refundGroupCd; // 환불그룹 코드
            $updateHandleData[$handleSno]['refundMethod'] = $getData['info']['refundMethod'];
            $updateHandleData[$handleSno]['refundBankName'] = $getData['info']['refundBankName'];
            $updateHandleData[$handleSno]['refundAccountNumber'] = $getData['info']['refundAccountNumber'];
            $updateHandleData[$handleSno]['refundDepositor'] = $getData['info']['refundDepositor'];
            $updateHandleData[$handleSno]['refundCharge'] = $getData['refundCharge' . $handleSno];
            $updateHandleData[$handleSno]['refundDeliveryInsuranceFee'] = $getData['check']['totalDeliveryInsuranceFee']; // 해외배송 보증금은 무조건 넣어줌
        }

        // $refundData['goodsPrice']에서 환불 수수료 합계를 빼줌
        $refundData['goodsPrice'] -= $orgTotalData['totalRefundCharge'];

        // 배송비
        // es_orderDelivery용
        // 환불금액 조절을하면서 안분금액 차액에대한 기준점이 있어야하는데 우선 배송비에대한 금액을 먼저 계산해서 해당금액을 픽스하고 잔여금액으로 안분처리하도록 한다.
        $tempRefundDivisionDeliveryDepositSum = 0;
        $tempRefundDivisionDeliveryMileageSum = 0;
        $tempRefundDivisionDeliveryPriceSum = 0;
        $tempUpdateDivisionDeliveryDepositSum = 0;
        $tempUpdateDivisionDeliveryMileageSum = 0;
        $tempUpdateDivisionDeliveryPriceSum = 0;
        $tempRefundDeliveryTaxSupplySum = 0;
        $tempRefundDeliveryTaxVatSum = 0;
        $tempRefundDeliveryTaxFreeSum = 0;
        $tempUpdateDeliveryTaxSupplySum = 0;
        $tempUpdateDeliveryTaxVatSum = 0;
        $tempUpdateDeliveryTaxFreeSum = 0;
        foreach ($getData['aAliveDeliverySno'] as $val) {
            // es_orderDelivery 업데이트 될내용
            $tempThisPrice = 0;
            $tempTaxPrice = 0;
            $tempThisPrice = $updateData['aRefundDelivery'][$val]['iPrice'];
            $tempPerPartOrder = 0;
            $tempPerPartOrder = $tempThisPrice / (($updateData['goodsPrice'] - $updateData['refundDcSum']) + $updateData['refundDeliverySum']);

            $updateDeliveryData[$val]['divisionDeliveryUseDeposit'] = NumberUtils::getNumberFigure($tempPerPartOrder * $updateData['refundDepositPrice'], '0.1', 'round'); // 배송비 안분 예치금
            $updateDeliveryData[$val]['divisionDeliveryUseMileage'] = NumberUtils::getNumberFigure($tempPerPartOrder * $updateData['refundMileagePrice'], '0.1', 'round'); // 배송비 안분 적립금
            $updateDeliveryData[$val]['divisionDeliveryCharge'] = $updateData['aRefundDelivery'][$val]['iCoupon']; // 배송비 쿠폰 안분

            $tempOrderHandle[$val]['iUpdateTotal'] = $updateData['aRefundDelivery'][$val]['iPrice'] + $updateData['aRefundDelivery'][$val]['iCoupon'];
            $tempOrderHandle[$val]['iUpdateCount'] = 0;

            $tempTaxPrice = $tempThisPrice - $updateDeliveryData[$val]['divisionDeliveryUseDeposit'] - $updateDeliveryData[$val]['divisionDeliveryUseMileage'];

            if ($orderInfo['deliveryTaxInfo'][$val][0] == 't') { // 배송비 세율적용이면
                $updateDeliveryData[$val]['realTaxSupplyDeliveryCharge'] = NumberUtils::getNumberFigure(($tempTaxPrice / (100 + $orderInfo['deliveryTaxInfo'][$val][1])) * 100, '0.1', 'round'); // 실 배송비 공급가
                $updateDeliveryData[$val]['realTaxVatDeliveryCharge'] = $tempTaxPrice - $updateDeliveryData[$val]['realTaxSupplyDeliveryCharge']; // 실 배송비 부가세
                $updateDeliveryData[$val]['realTaxFreeDeliveryCharge'] = 0; // 실 배송비 비과세
                $updateDeliveryData[$val]['realTaxSum'] = $updateDeliveryData[$val]['realTaxSupplyDeliveryCharge'] + $updateDeliveryData[$val]['realTaxVatDeliveryCharge']; // 실 배송비 합계
            } else { // 비과세면
                $updateDeliveryData[$val]['realTaxSupplyDeliveryCharge'] = 0; // 실 배송비 공급가
                $updateDeliveryData[$val]['realTaxVatDeliveryCharge'] = 0; // 실 배송비 부가세
                $updateDeliveryData[$val]['realTaxFreeDeliveryCharge'] = $tempTaxPrice; // 실 배송비 비과세
                $updateDeliveryData[$val]['realTaxSum'] = $tempTaxPrice; // 실 배송비 합계
            }

            $tempUpdateDivisionDeliveryDepositSum += $updateDeliveryData[$val]['divisionDeliveryUseDeposit'];
            $tempUpdateDivisionDeliveryMileageSum += $updateDeliveryData[$val]['divisionDeliveryUseMileage'];
            $tempUpdateDivisionDeliveryPriceSum += ($updateDeliveryData[$val]['realTaxSupplyDeliveryCharge'] + $updateDeliveryData[$val]['realTaxVatDeliveryCharge'] + $updateDeliveryData[$val]['realTaxFreeDeliveryCharge']);
            $tempUpdateDeliveryTaxSupplySum += $updateDeliveryData[$val]['realTaxSupplyDeliveryCharge'];
            $tempUpdateDeliveryTaxVatSum += $updateDeliveryData[$val]['realTaxVatDeliveryCharge'];
            $tempUpdateDeliveryTaxFreeSum += $updateDeliveryData[$val]['realTaxFreeDeliveryCharge'];

            // 환불대상 es_orderGoods의 배송비 컬럼에 들어갈값들처리
            $tempThisPrice = 0;
            $tempTaxPrice = 0;
            $tempThisPrice = $refundData['aRefundDelivery'][$val]['iPrice'];
            $tempPerPartOrder = 0;
            $tempPerPartOrder = $tempThisPrice / (($refundData['goodsPrice'] - $refundData['refundDcSum']) + $refundData['refundDeliverySum']);

            $refundDeliveryData[$val]['divisionDeliveryUseDeposit'] = NumberUtils::getNumberFigure($tempPerPartOrder * $refundData['refundDepositPrice'], '0.1', 'round'); // 배송비 안분 예치금
            $refundDeliveryData[$val]['divisionDeliveryUseMileage'] = NumberUtils::getNumberFigure($tempPerPartOrder * $refundData['refundMileagePrice'], '0.1', 'round'); // 배송비 안분 적립금
            $refundDeliveryData[$val]['divisionDeliveryCharge'] = $refundData['aRefundDelivery'][$val]['iCoupon']; // 배송비 쿠폰 안분

            $tempOrderHandle[$val]['iRefundTotal'] = $refundData['aRefundDelivery'][$val]['iPrice'] + $refundData['aRefundDelivery'][$val]['iCoupon'];
            $tempOrderHandle[$val]['iRefundCount'] = 0;

            $tempTaxPrice = $tempThisPrice - $refundDeliveryData[$val]['divisionDeliveryUseDeposit'] - $refundDeliveryData[$val]['divisionDeliveryUseMileage'];

            if ($orderInfo['deliveryTaxInfo'][$val][0] == 't') { // 배송비 세율적용이면
                $refundDeliveryData[$val]['realTaxSupplyDeliveryCharge'] = NumberUtils::getNumberFigure(($tempTaxPrice / (100 + $orderInfo['deliveryTaxInfo'][$val][1])) * 100, '0.1', 'round'); // 실 배송비 공급가
                $refundDeliveryData[$val]['realTaxVatDeliveryCharge'] = $tempTaxPrice - $refundDeliveryData[$val]['realTaxSupplyDeliveryCharge']; // 실 배송비 부가세
                $refundDeliveryData[$val]['realTaxFreeDeliveryCharge'] = 0; // 실 배송비 비과세
                $refundDeliveryData[$val]['realTaxSum'] = $refundDeliveryData[$val]['realTaxSupplyDeliveryCharge'] + $refundDeliveryData[$val]['realTaxVatDeliveryCharge']; // 실 배송비 합계
            } else { // 비과세면
                $refundDeliveryData[$val]['realTaxSupplyDeliveryCharge'] = 0; // 실 배송비 공급가
                $refundDeliveryData[$val]['realTaxVatDeliveryCharge'] = 0; // 실 배송비 부가세
                $refundDeliveryData[$val]['realTaxFreeDeliveryCharge'] = $tempTaxPrice; // 실 배송비 비과세
                $refundDeliveryData[$val]['realTaxSum'] = $tempTaxPrice; // 실 배송비 합계
            }

            $tempRefundDivisionDeliveryDepositSum += $refundDeliveryData[$val]['divisionDeliveryUseDeposit'];
            $tempRefundDivisionDeliveryMileageSum += $refundDeliveryData[$val]['divisionDeliveryUseMileage'];
            $tempRefundDivisionDeliveryPriceSum += ($refundDeliveryData[$val]['realTaxSupplyDeliveryCharge'] + $refundDeliveryData[$val]['realTaxVatDeliveryCharge'] + $refundDeliveryData[$val]['realTaxFreeDeliveryCharge']);
            $tempRefundDeliveryTaxSupplySum += $refundDeliveryData[$val]['realTaxSupplyDeliveryCharge'];
            $tempRefundDeliveryTaxVatSum += $refundDeliveryData[$val]['realTaxVatDeliveryCharge'];
            $tempRefundDeliveryTaxFreeSum += $refundDeliveryData[$val]['realTaxFreeDeliveryCharge'];
        }

        // 배송비 update와 refund의 tax값들 합이 $orgTotalData의 realTaxSupplyPrice / realTaxVatPrice / realTaxFreePrice 값을 넘어가는지 체크해서 refundDivisionCheck 로 재분배 해줘야한다
        list($updateDeliveryData, $refundDeliveryData) = $this->refundDeliveryDivisionCheck($orgTotalData, $updateDeliveryData, $refundDeliveryData, $getData);

        // 배송비 임시 합계값들 재계산
        $tempUpdateDivisionDeliveryDepositSum = 0;
        $tempUpdateDivisionDeliveryMileageSum = 0;
        $tempUpdateDivisionDeliveryPriceSum = 0;
        $tempUpdateDeliveryTaxSupplySum = 0;
        $tempUpdateDeliveryTaxVatSum = 0;
        $tempUpdateDeliveryTaxFreeSum = 0;
        $tempRefundDivisionDeliveryDepositSum = 0;
        $tempRefundDivisionDeliveryMileageSum = 0;
        $tempRefundDivisionDeliveryPriceSum = 0;
        $tempRefundDeliveryTaxSupplySum = 0;
        $tempRefundDeliveryTaxVatSum = 0;
        $tempRefundDeliveryTaxFreeSum = 0;
        foreach ($updateDeliveryData as $val) {
            $tempUpdateDivisionDeliveryDepositSum += $val['divisionDeliveryUseDeposit'];
            $tempUpdateDivisionDeliveryMileageSum += $val['divisionDeliveryUseMileage'];
            $tempUpdateDivisionDeliveryPriceSum += ($val['realTaxSupplyDeliveryCharge'] + $val['realTaxVatDeliveryCharge'] + $val['realTaxFreeDeliveryCharge']);
            $tempUpdateDeliveryTaxSupplySum += $val['realTaxSupplyDeliveryCharge'];
            $tempUpdateDeliveryTaxVatSum += $val['realTaxVatDeliveryCharge'];
            $tempUpdateDeliveryTaxFreeSum += $val['realTaxFreeDeliveryCharge'];
        }
        foreach ($refundDeliveryData as $val) {
            $tempRefundDivisionDeliveryDepositSum += $val['divisionDeliveryUseDeposit'];
            $tempRefundDivisionDeliveryMileageSum += $val['divisionDeliveryUseMileage'];
            $tempRefundDivisionDeliveryPriceSum += ($val['realTaxSupplyDeliveryCharge'] + $val['realTaxVatDeliveryCharge'] + $val['realTaxFreeDeliveryCharge']);
            $tempRefundDeliveryTaxSupplySum += $val['realTaxSupplyDeliveryCharge'];
            $tempRefundDeliveryTaxVatSum += $val['realTaxVatDeliveryCharge'];
            $tempRefundDeliveryTaxFreeSum += $val['realTaxFreeDeliveryCharge'];
        }

        // orderHandle과 orderGoods에서 안분될 항목값들을 구하기 위한 delivery기준 퍼센트 구하기 & 환불상품별로 재고 복원 sms발송처리
        $smsCnt = 0;    // 부분 환불 시 sms 일괄 전송을 위한 cnt(sms 개선)
        $refundData['goodsMileageTotalPrice'] = 0; // goodsMileage가있는 상품의 금액 합계
        $reason = ($getData['reason'] ? $getData['reason'] : __('일괄')); // 변경사유
        foreach ($orderInfo['goods'] as $sKey => $sVal) {
            foreach ($sVal as $dKey => $dVal) {
                foreach ($dVal as $gKey => $gVal) {
                    if (in_array($gVal['sno'], $refundTarget)) { // 환불 대상처리
                        $smsCnt++;
                        // sms 개선(환불 시 금액 전달을 위한 변수, 부분 환불 시 sms 일괄 전송을 위한 cnt)
                        $orderRefundInfo['orderHandleData'][$gVal['handleSno']][0] = $gVal;
                        $orderRefundInfo['orderHandleData'][$gVal['handleSno']][0]['refundCompletePrice'] = $getData['check']['totalRefundPrice'];
                        $orderRefundInfo['orderHandleData'][$gVal['handleSno']][0]['smsCnt'] = $smsCnt;

                        // 주문 상태 변경 처리 및 재고 복원여부 처리
                        $isReturnStock = ($getData['returnStockFl'] === 'y') ? true : false;  // 환불상세에서 재고환원 여부에 동의시 처리여부
                        $orderAdmin->updateStatusPreprocess($getData['orderNo'], $orderRefundInfo['orderHandleData'][$gVal['handleSno']], 'r', 'r3', $reason, $isReturnStock, null, null, $autoProcess);

                        // 환불된 상품 상품 테이블 주문상품 갯수 필드 차감 처리 es_goods.orderGoodsCnt
                        $goods->setOrderGoodsCount($gVal['sno'], true, $gVal['goodsNo'], $gVal['goodsCnt']);

                        $tempOrderHandle[$gVal['orderDeliverySno']]['iRefundCount'] += 1;

                        if ($gVal['memberMileage'] > 0 && $gVal['plusMileageFl'] == 'n') {
                            $refundData['memberMileageTotalPrice'] += (($gVal['goodsPrice'] + $gVal['optionPrice'] + $gVal['optionTextPrice']) * $gVal['goodsCnt']);
                        }
                        if ($gVal['couponGoodsMileage'] > 0 && $gVal['couponMileageFl'] == 'n') {
                            $refundData['couponGoodsMileageTotalPrice'] += (($gVal['goodsPrice'] + $gVal['optionPrice'] + $gVal['optionTextPrice']) * $gVal['goodsCnt']);
                        }
                        if ($gVal['divisionCouponOrderMileage'] > 0 && $gVal['couponMileageFl'] == 'n') {
                            $refundData['divisionCouponOrderMileageTotalPrice'] += (($gVal['goodsPrice'] + $gVal['optionPrice'] + $gVal['optionTextPrice']) * $gVal['goodsCnt']);
                        }
                    } else {
                        $tempOrderHandle[$gVal['orderDeliverySno']]['iUpdateCount'] += 1;
                        if ($gVal['memberMileage'] > 0 && $gVal['plusMileageFl'] == 'n') {
                            $updateData['memberMileageTotalPrice'] += (($gVal['goodsPrice'] + $gVal['optionPrice'] + $gVal['optionTextPrice']) * $gVal['goodsCnt']);
                        }
                        if ($gVal['couponGoodsMileage'] > 0 && $gVal['couponMileageFl'] == 'n') {
                            $updateData['couponGoodsMileageTotalPrice'] += (($gVal['goodsPrice'] + $gVal['optionPrice'] + $gVal['optionTextPrice']) * $gVal['goodsCnt']);
                        }
                        if ($gVal['divisionCouponOrderMileage'] > 0 && $gVal['couponMileageFl'] == 'n') {
                            $updateData['divisionCouponOrderMileageTotalPrice'] += (($gVal['goodsPrice'] + $gVal['optionPrice'] + $gVal['optionTextPrice']) * $gVal['goodsCnt']);
                        }
                    }
                }
            }
        }

        // 환불 처리대상과 / 미대상 구분
        $tempRefundDivisionGoodsDcSum = 0;
        $tempRefundDivisionMemberDcSum = 0;
        $tempRefundDivisionMemberOverlapDcSum = 0;
        $tempRefundDivisionEnuriSum = 0;
        $tempRefundDivisionMyappDcSum = 0;
        $tempRefundDivisionCouponGoodsDcSum = 0;
        $tempRefundDivisionCouponOrderDcSum = 0;
        $tempRefundDivisionDepositSum = 0;
        $tempRefundDivisionMileageSum = 0;
        $tempRefundDivisionCouponGoodsMileageSum = 0;
        $tempRefundDivisionCouponOrderMileageSum = 0;
        $tempRefundDivisionMemberMileageSum = 0;
        $tempUpdateDivisionGoodsDcSum = 0;
        $tempUpdateDivisionMemberDcSum = 0;
        $tempUpdateDivisionMemberOverlapDcSum = 0;
        $tempUpdateDivisionEnuriSum = 0;
        $tempUpdateDivisionMyappDcSum = 0;
        $tempUpdateDivisionCouponGoodsDcSum = 0;
        $tempUpdateDivisionCouponOrderDcSum = 0;
        $tempUpdateDivisionDepositSum = 0;
        $tempUpdateDivisionMileageSum = 0;
        $tempUpdateDivisionCouponGoodsMileageSum = 0;
        $tempUpdateDivisionCouponOrderMileageSum = 0;
        $tempUpdateDivisionMemberMileageSum = 0;
        foreach ($orderInfo['goods'] as $sKey => $sVal) {
            foreach ($sVal as $dKey => $dVal) {
                foreach ($dVal as $gKey => $gVal) {
                    $tempPerPart = 0;
                    $tempPrice = 0;
                    if (in_array($gVal['sno'], $refundTarget)) { // 환불 대상처리
						$order = new Order();
						if ($order->isLoggedMemberOrderGoodsCountData($getData['orderNo'], $gVal['sno'])) {
							$aGoodsSno['sno'][0] = $aGoodsSno['GoodsSno'][0] = $gVal['sno'];
							$aGoodsSno['changeStatus'] = '';
							$orderKind['kind'] = '';
							$orderKind['channel'] = '';
							$claimInfo['orderCount'] = $gVal['goodsCnt'];
							$order->setCountOrderGoods($getData['orderNo'], $aGoodsSno, $orderKind, true, $claimInfo);
						}
                        $refundEtcInfo['refundOrderGoodsSnos'][] = $gVal['sno'];

                        // 사용예치금 복원을 위한 order goods sno 지정
                        if ($gVal['minusDepositFl'] == 'y') {
                            if ($refundData['refundDepositPrice'] > 0) {
                                $refundEtcInfo['restoreDepositSnos'][] = $gVal['sno'];
                            }
                        }

                        // 사용마일리지 복원을 위한 order goods sno 지정
                        if ($gVal['minusMileageFl'] == 'y') {
                            if ($refundData['refundMileagePrice'] > 0) {
                                $refundEtcInfo['restoreMileageSnos'][] = $gVal['sno'];
                            }
                        }

                        // 적립마일리지가 있는 경우 환원 및 환불테이블에 들어갈 값 정의
                        if ($gVal['plusMileageFl'] == 'y') {
                            $refundEtcInfo['totalRefundGiveMileage'] += $gVal['goodsMileage'];
                            $refundEtcInfo['giveMileage'][] = $gVal['sno'];
                        }

                        $tempPerPart = ((($gVal['goodsPrice'] + $gVal['optionPrice'] + $gVal['optionTextPrice']) * $gVal['goodsCnt']) - $getData['refundCharge' . $gVal['handleSno']]) / $refundData['goodsPrice'];
                        $tempPerPartWithDelivery = ((($gVal['goodsPrice'] + $gVal['optionPrice'] + $gVal['optionTextPrice']) * $gVal['goodsCnt']) - $getData['refundCharge' . $gVal['handleSno']]) / ($refundData['goodsPrice'] + $refundData['refundDeliverySum']);
                        if ($gVal['memberMileage'] > 0 && $gVal['plusMileageFl'] == 'n') {
                            $tempMemberMileagePerPart = (($gVal['goodsPrice'] + $gVal['optionPrice'] + $gVal['optionTextPrice']) * $gVal['goodsCnt']) / $refundData['memberMileageTotalPrice'];
                        }
                        if ($gVal['couponGoodsMileage'] > 0 && $gVal['couponMileageFl'] == 'n') {
                            $tempCouponGoodsMileagePerPart = (($gVal['goodsPrice'] + $gVal['optionPrice'] + $gVal['optionTextPrice']) * $gVal['goodsCnt']) / $refundData['couponGoodsMileageTotalPrice'];
                        }
                        if ($gVal['divisionCouponOrderMileage'] > 0 && $gVal['couponMileageFl'] == 'n') {
                            $tempDivisionCouponOrderMileagePerPart = (($gVal['goodsPrice'] + $gVal['optionPrice'] + $gVal['optionTextPrice']) * $gVal['goodsCnt']) / $refundData['divisionCouponOrderMileageTotalPrice'];
                        }

                        $refundGoodsData[$gVal['sno']]['goodsPrice'] = $gVal['goodsPrice'];
                        $refundGoodsData[$gVal['sno']]['optionPrice'] = $gVal['optionPrice'];
                        $refundGoodsData[$gVal['sno']]['optionTextPrice'] = $gVal['optionTextPrice'];
                        $refundGoodsData[$gVal['sno']]['goodsCnt'] = $gVal['goodsCnt'];
                        $refundGoodsData[$gVal['sno']]['goodsDcPrice'] = NumberUtils::getNumberFigure($refundData['refundGoodsDcPrice'] * $tempPerPart, '0.1', 'round');
                        $refundGoodsData[$gVal['sno']]['memberDcPrice'] = NumberUtils::getNumberFigure($refundData['refundMemberAddDcPrice'] * $tempPerPart, '0.1', 'round');
                        $refundGoodsData[$gVal['sno']]['memberOverlapDcPrice'] = NumberUtils::getNumberFigure($refundData['refundMemberOverlapDcPrice'] * $tempPerPart, '0.1', 'round');
                        $refundGoodsData[$gVal['sno']]['enuri'] = NumberUtils::getNumberFigure($refundData['refundEnuriDcPrice'] * $tempPerPart, '0.1', 'round');
                        if ($this->myappUseFl) {
                            $refundGoodsData[$gVal['sno']]['myappDcPrice'] = NumberUtils::getNumberFigure($refundData['refundMyappDcPrice'] * $tempPerPart, '0.1', 'round');
                        }
                        $refundGoodsData[$gVal['sno']]['couponGoodsDcPrice'] = NumberUtils::getNumberFigure($refundData['refundGoodsCouponDcPrice'] * $tempPerPart, '0.1', 'round');
                        $refundGoodsData[$gVal['sno']]['divisionCouponOrderDcPrice'] = NumberUtils::getNumberFigure($refundData['refundOrderCouponDcPrice'] * $tempPerPart, '0.1', 'round');

                        $refundGoodsData[$gVal['sno']]['divisionUseDeposit'] = NumberUtils::getNumberFigure($refundData['refundDepositPrice'] * $tempPerPartWithDelivery, '0.1', 'round');
                        $refundGoodsData[$gVal['sno']]['divisionUseMileage'] = NumberUtils::getNumberFigure($refundData['refundMileagePrice'] * $tempPerPartWithDelivery, '0.1', 'round');

                        if ($gVal['memberMileage'] > 0 && $gVal['plusMileageFl'] == 'n') {
                            $refundGoodsData[$gVal['sno']]['memberMileage'] = NumberUtils::getNumberFigure($refundData['refundGroupMileage'] * $tempMemberMileagePerPart, '0.1', 'round');
                        } else {
                            $refundGoodsData[$gVal['sno']]['memberMileage'] = $gVal['memberMileage'];
                        }
                        if ($gVal['couponGoodsMileage'] > 0 && $gVal['couponMileageFl'] == 'n') {
                            $refundGoodsData[$gVal['sno']]['couponGoodsMileage'] = NumberUtils::getNumberFigure($refundData['refundGoodsCouponMileage'] * $tempCouponGoodsMileagePerPart, '0.1', 'round');
                        } else {
                            $refundGoodsData[$gVal['sno']]['couponGoodsMileage'] = $gVal['couponGoodsMileage'];
                        }
                        if ($gVal['divisionCouponOrderMileage'] > 0 && $gVal['couponMileageFl'] == 'n') {
                            $refundGoodsData[$gVal['sno']]['divisionCouponOrderMileage'] = NumberUtils::getNumberFigure($refundData['refundOrderCouponMileage'] * $tempDivisionCouponOrderMileagePerPart, '0.1', 'round');
                        } else {
                            $refundGoodsData[$gVal['sno']]['divisionCouponOrderMileage'] = $gVal['divisionCouponOrderMileage'];
                        }

                        if ($gVal['plusMileageFl'] == 'y') {
                            $refundEtcInfo['totalRefundGiveMileage'] += $refundGoodsData[$gVal['sno']]['couponGoodsMileage'] + $refundGoodsData[$gVal['sno']]['divisionCouponOrderMileage'] + $refundGoodsData[$gVal['sno']]['memberMileage'];
                        }

                        $refundData['refundOrderCouponMileage'] = ($getData['refundOrderCouponMileageFlag'] == 'T') ? $getData['refundOrderCouponMileage'] : 0;
                        $refundData['refundGroupMileage'] = ($getData['refundGroupMileageFlag'] == 'T') ? $getData['refundGroupMileage'] : 0;

                        $tempRefundDivisionGoodsDcSum += $refundGoodsData[$gVal['sno']]['goodsDcPrice'];
                        $tempRefundDivisionMemberDcSum += $refundGoodsData[$gVal['sno']]['memberDcPrice'];
                        $tempRefundDivisionMemberOverlapDcSum += $refundGoodsData[$gVal['sno']]['memberOverlapDcPrice'];
                        $tempRefundDivisionEnuriSum += $refundGoodsData[$gVal['sno']]['enuri'];
                        if ($this->myappUseFl) {
                            $tempRefundDivisionMyappDcSum += $refundGoodsData[$gVal['sno']]['myappDcPrice'];
                        }
                        $tempRefundDivisionCouponGoodsDcSum += $refundGoodsData[$gVal['sno']]['couponGoodsDcPrice'];
                        $tempRefundDivisionCouponOrderDcSum += $refundGoodsData[$gVal['sno']]['divisionCouponOrderDcPrice'];
                        $tempRefundDivisionDepositSum += $refundGoodsData[$gVal['sno']]['divisionUseDeposit'];
                        $tempRefundDivisionMileageSum += $refundGoodsData[$gVal['sno']]['divisionUseMileage'];

                        $tempRefundDivisionCouponGoodsMileageSum += $refundGoodsData[$gVal['sno']]['couponGoodsMileage'];
                        $tempRefundDivisionCouponOrderMileageSum += $refundGoodsData[$gVal['sno']]['divisionCouponOrderMileage'];
                        $tempRefundDivisionMemberMileageSum += $refundGoodsData[$gVal['sno']]['memberMileage'];

                    } else {
                        $tempPerPart = (($gVal['goodsPrice'] + $gVal['optionPrice'] + $gVal['optionTextPrice']) * $gVal['goodsCnt']) / $updateData['goodsPrice'];
                        $tempPerPartWithDelivery = (($gVal['goodsPrice'] + $gVal['optionPrice'] + $gVal['optionTextPrice']) * $gVal['goodsCnt']) / ($updateData['goodsPrice'] + $updateData['refundDeliverySum']);
                        if ($gVal['memberMileage'] > 0 && $gVal['plusMileageFl'] == 'n') {
                            $tempMemberMileagePerPart = (($gVal['goodsPrice'] + $gVal['optionPrice'] + $gVal['optionTextPrice']) * $gVal['goodsCnt']) / $updateData['memberMileageTotalPrice'];
                        }
                        if ($gVal['couponGoodsMileage'] > 0 && $gVal['couponMileageFl'] == 'n') {
                            $tempCouponGoodsMileagePerPart = (($gVal['goodsPrice'] + $gVal['optionPrice'] + $gVal['optionTextPrice']) * $gVal['goodsCnt']) / $updateData['couponGoodsMileageTotalPrice'];
                        }
                        if ($gVal['divisionCouponOrderMileage'] > 0 && $gVal['couponMileageFl'] == 'n') {
                            $tempDivisionCouponOrderMileagePerPart = (($gVal['goodsPrice'] + $gVal['optionPrice'] + $gVal['optionTextPrice']) * $gVal['goodsCnt']) / $updateData['divisionCouponOrderMileageTotalPrice'];
                        }

                        $updateGoodsData[$gVal['sno']]['goodsPrice'] = $gVal['goodsPrice'];
                        $updateGoodsData[$gVal['sno']]['optionPrice'] = $gVal['optionPrice'];
                        $updateGoodsData[$gVal['sno']]['optionTextPrice'] = $gVal['optionTextPrice'];
                        $updateGoodsData[$gVal['sno']]['goodsCnt'] = $gVal['goodsCnt'];
                        $updateGoodsData[$gVal['sno']]['goodsDcPrice'] = NumberUtils::getNumberFigure($updateData['refundGoodsDcPrice'] * $tempPerPart, '0.1', 'round');
                        $updateGoodsData[$gVal['sno']]['memberDcPrice'] = NumberUtils::getNumberFigure($updateData['refundMemberAddDcPrice'] * $tempPerPart, '0.1', 'round');
                        $updateGoodsData[$gVal['sno']]['memberOverlapDcPrice'] = NumberUtils::getNumberFigure($updateData['refundMemberOverlapDcPrice'] * $tempPerPart, '0.1', 'round');
                        $updateGoodsData[$gVal['sno']]['enuri'] = NumberUtils::getNumberFigure($updateData['refundEnuriDcPrice'] * $tempPerPart, '0.1', 'round');
                        if ($this->myappUseFl) {
                            $updateGoodsData[$gVal['sno']]['myappDcPrice'] = NumberUtils::getNumberFigure($updateData['refundMyappDcPrice'] * $tempPerPart, '0.1', 'round');
                        }
                        $updateGoodsData[$gVal['sno']]['couponGoodsDcPrice'] = NumberUtils::getNumberFigure($updateData['refundGoodsCouponDcPrice'] * $tempPerPart, '0.1', 'round');
                        $updateGoodsData[$gVal['sno']]['divisionCouponOrderDcPrice'] = NumberUtils::getNumberFigure($updateData['refundOrderCouponDcPrice'] * $tempPerPart, '0.1', 'round');

                        $updateGoodsData[$gVal['sno']]['divisionUseDeposit'] = NumberUtils::getNumberFigure($updateData['refundDepositPrice'] * $tempPerPartWithDelivery, '0.1', 'round');
                        $updateGoodsData[$gVal['sno']]['divisionUseMileage'] = NumberUtils::getNumberFigure($updateData['refundMileagePrice'] * $tempPerPartWithDelivery, '0.1', 'round');

                        if ($gVal['couponGoodsMileage'] > 0 && $gVal['couponMileageFl'] == 'n') {
                            if ($getData['refundGoodsCouponMileageFlag'] == 'F') {
                                $updateGoodsData[$gVal['sno']]['couponGoodsMileage'] = NumberUtils::getNumberFigure($gVal['couponGoodsMileage']);
                            } else {
                                $updateGoodsData[$gVal['sno']]['couponGoodsMileage'] = NumberUtils::getNumberFigure($updateData['refundGoodsCouponMileage'] * $tempCouponGoodsMileagePerPart, '0.1', 'round');
                            }
                        } else {
                            $updateGoodsData[$gVal['sno']]['couponGoodsMileage'] = $gVal['couponGoodsMileage'];
                        }
                        if ($gVal['divisionCouponOrderMileage'] > 0 && $gVal['couponMileageFl'] == 'n') {
                            if ($getData['refundOrderCouponMileageFlag'] == 'F') {
                                $updateGoodsData[$gVal['sno']]['divisionCouponOrderMileage'] = NumberUtils::getNumberFigure($gVal['divisionCouponOrderMileage']);
                            } else {
                                $updateGoodsData[$gVal['sno']]['divisionCouponOrderMileage'] = NumberUtils::getNumberFigure($updateData['refundOrderCouponMileage'] * $tempDivisionCouponOrderMileagePerPart, '0.1', 'round');
                            }
                        } else {
                            $updateGoodsData[$gVal['sno']]['divisionCouponOrderMileage'] = $gVal['divisionCouponOrderMileage'];
                        }
                        if ($gVal['memberMileage'] > 0 && $gVal['plusMileageFl'] == 'n') {
                            if ($getData['refundGroupMileageFlag'] == 'F') {
                                $updateGoodsData[$gVal['sno']]['memberMileage'] = NumberUtils::getNumberFigure($gVal['memberMileage']);
                            } else {
                                $updateGoodsData[$gVal['sno']]['memberMileage'] = NumberUtils::getNumberFigure($updateData['refundGroupMileage'] * $tempMemberMileagePerPart, '0.1', 'round');
                            }
                        } else {
                            $updateGoodsData[$gVal['sno']]['memberMileage'] = $gVal['memberMileage'];
                        }

                        $tempUpdateDivisionGoodsDcSum += $updateGoodsData[$gVal['sno']]['goodsDcPrice'];
                        $tempUpdateDivisionMemberDcSum += $updateGoodsData[$gVal['sno']]['memberDcPrice'];
                        $tempUpdateDivisionMemberOverlapDcSum += $updateGoodsData[$gVal['sno']]['memberOverlapDcPrice'];
                        $tempUpdateDivisionEnuriSum += $updateGoodsData[$gVal['sno']]['enuri'];
                        if ($this->myappUseFl) {
                            $tempUpdateDivisionMyappDcSum += $updateGoodsData[$gVal['sno']]['myappDcPrice'];
                        }
                        $tempUpdateDivisionCouponGoodsDcSum += $updateGoodsData[$gVal['sno']]['couponGoodsDcPrice'];
                        $tempUpdateDivisionCouponOrderDcSum += $updateGoodsData[$gVal['sno']]['divisionCouponOrderDcPrice'];
                        $tempUpdateDivisionDepositSum += $updateGoodsData[$gVal['sno']]['divisionUseDeposit'];
                        $tempUpdateDivisionMileageSum += $updateGoodsData[$gVal['sno']]['divisionUseMileage'];

                        $tempUpdateDivisionCouponGoodsMileageSum += $updateGoodsData[$gVal['sno']]['couponGoodsMileage'];
                        $tempUpdateDivisionCouponOrderMileageSum += $updateGoodsData[$gVal['sno']]['divisionCouponOrderMileage'];
                        $tempUpdateDivisionMemberMileageSum += $updateGoodsData[$gVal['sno']]['memberMileage'];
                    }
                }
            }
        }

        // 환불 es_orderGoods 안분금액들 맞추기
        $refundGoodsData = $this->refundDivisionCheck($refundGoodsData, $tempRefundDivisionGoodsDcSum, $refundData['refundGoodsDcPrice'], 'goodsDcPrice');
        $refundGoodsData = $this->refundDivisionCheck($refundGoodsData, $tempRefundDivisionMemberDcSum, $refundData['refundMemberAddDcPrice'], 'memberDcPrice');
        $refundGoodsData = $this->refundDivisionCheck($refundGoodsData, $tempRefundDivisionMemberOverlapDcSum, $refundData['refundMemberOverlapDcPrice'], 'memberOverlapDcPrice');
        $refundGoodsData = $this->refundDivisionCheck($refundGoodsData, $tempRefundDivisionEnuriSum, $refundData['refundEnuriDcPrice'], 'enuri');
        if ($this->myappUseFl) {
            $refundGoodsData = $this->refundDivisionCheck($refundGoodsData, $tempRefundDivisionMyappDcSum, $refundData['refundMyappDcPrice'], 'myappDcPrice');
        }
        $refundGoodsData = $this->refundDivisionCheck($refundGoodsData, $tempRefundDivisionCouponGoodsDcSum, $refundData['refundGoodsCouponDcPrice'], 'couponGoodsDcPrice');
        $refundGoodsData = $this->refundDivisionCheck($refundGoodsData, $tempRefundDivisionCouponOrderDcSum, $refundData['refundOrderCouponDcPrice'], 'divisionCouponOrderDcPrice');
        $refundGoodsData = $this->refundDivisionCheck($refundGoodsData, $tempRefundDivisionDepositSum, ($refundData['refundDepositPrice'] - $tempRefundDivisionDeliveryDepositSum), 'divisionUseDeposit');
        $refundGoodsData = $this->refundDivisionCheck($refundGoodsData, $tempRefundDivisionMileageSum, ($refundData['refundMileagePrice'] - $tempRefundDivisionDeliveryMileageSum), 'divisionUseMileage');

        // 업데이트 es_orderGoods 안분금액들 맞추기
        $updateGoodsData = $this->refundDivisionCheck($updateGoodsData, $tempUpdateDivisionGoodsDcSum, $updateData['refundGoodsDcPrice'], 'goodsDcPrice');
        $updateGoodsData = $this->refundDivisionCheck($updateGoodsData, $tempUpdateDivisionMemberDcSum, $updateData['refundMemberAddDcPrice'], 'memberDcPrice');
        $updateGoodsData = $this->refundDivisionCheck($updateGoodsData, $tempUpdateDivisionMemberOverlapDcSum, $updateData['refundMemberOverlapDcPrice'], 'memberOverlapDcPrice');
        $updateGoodsData = $this->refundDivisionCheck($updateGoodsData, $tempUpdateDivisionEnuriSum, $updateData['refundEnuriDcPrice'], 'enuri');
        if ($this->myappUseFl) {
            $updateGoodsData = $this->refundDivisionCheck($updateGoodsData, $tempUpdateDivisionMyappDcSum, $updateData['refundMyappDcPrice'], 'myappDcPrice');
        }
        $updateGoodsData = $this->refundDivisionCheck($updateGoodsData, $tempUpdateDivisionCouponGoodsDcSum, $updateData['refundGoodsCouponDcPrice'], 'couponGoodsDcPrice');
        $updateGoodsData = $this->refundDivisionCheck($updateGoodsData, $tempUpdateDivisionCouponOrderDcSum, $updateData['refundOrderCouponDcPrice'], 'divisionCouponOrderDcPrice');
        $updateGoodsData = $this->refundDivisionCheck($updateGoodsData, $tempUpdateDivisionDepositSum, ($updateData['refundDepositPrice'] - $tempUpdateDivisionDeliveryDepositSum), 'divisionUseDeposit');
        $updateGoodsData = $this->refundDivisionCheck($updateGoodsData, $tempUpdateDivisionMileageSum, ($updateData['refundMileagePrice'] - $tempUpdateDivisionDeliveryMileageSum), 'divisionUseMileage');

        $tempUpdateHandleDivisionDeliveryChargeSum = array();
        $tempUpdateHandleDivisionDeliveryUseDepositSum = array();
        $tempUpdateHandleDivisionDeliveryUseMileageSum = array();
        $tempUpdateHandleDivisionDeliveryCouponSum = array();
        $tempUpdateHandleDivisionUseDepositCommissionSum = 0;
        $tempUpdateHandleDivisionUseMileageCommissionSum = 0;
        $tempUpdateHandleDivisionGiveMileageSum = 0;
        $tempUpdateHandleDivisionCashPriceSum = 0;
        $tempUpdateHandleDivisionPgPriceSum = 0;
        $tempUpdateHandleDivisionDepositPriceSum = 0;
        $tempUpdateHandleDivisionMileagePriceSum = 0;
        $tempUpdateGoodsDivisionDeliveryUseDepositSum = array();
        $tempUpdateGoodsDivisionDeliveryUseMileageSum = array();
        $tempRefundTaxSupplySum = 0;
        $tempRefundTaxVatSum = 0;
        $tempRefundTaxFreeSum = 0;
        $tempUpdateTaxSupplySum = 0;
        $tempUpdateTaxVatSum = 0;
        $tempUpdateTaxFreeSum = 0;
        foreach ($orderInfo['goods'] as $sKey => $sVal) {
            foreach ($sVal as $dKey => $dVal) {
                foreach ($dVal as $gKey => $gVal) {
                    if (in_array($gVal['sno'], $refundTarget)) { // 환불 대상처리
                        $tempPerPart = ((($gVal['goodsPrice'] + $gVal['optionPrice'] + $gVal['optionTextPrice']) * $gVal['goodsCnt']) - $getData['refundCharge' . $gVal['handleSno']]) / $refundData['goodsPrice'];

                        $updateHandleData[$gVal['handleSno']]['refundUseDeposit'] = $refundGoodsData[$gVal['sno']]['divisionUseDeposit'];
                        $updateHandleData[$gVal['handleSno']]['refundUseMileage'] = $refundGoodsData[$gVal['sno']]['divisionUseMileage'];
                        $updateHandleData[$gVal['handleSno']]['refundDeliveryCharge'] = NumberUtils::getNumberFigure(($refundDeliveryData[$gVal['orderDeliverySno']]['realTaxSupplyDeliveryCharge'] + $refundDeliveryData[$gVal['orderDeliverySno']]['realTaxVatDeliveryCharge'] + $refundDeliveryData[$gVal['orderDeliverySno']]['realTaxFreeDeliveryCharge']) / $tempOrderHandle[$gVal['orderDeliverySno']]['iRefundCount'], '0.1', 'round');
                        $updateHandleData[$gVal['handleSno']]['refundDeliveryUseDeposit'] = NumberUtils::getNumberFigure($refundDeliveryData[$gVal['orderDeliverySno']]['divisionDeliveryUseDeposit'] / $tempOrderHandle[$gVal['orderDeliverySno']]['iRefundCount'], '0.1', 'round');
                        $updateHandleData[$gVal['handleSno']]['refundDeliveryUseMileage'] = NumberUtils::getNumberFigure($refundDeliveryData[$gVal['orderDeliverySno']]['divisionDeliveryUseMileage'] / $tempOrderHandle[$gVal['orderDeliverySno']]['iRefundCount'], '0.1', 'round');
                        $updateHandleData[$gVal['handleSno']]['refundDeliveryCoupon'] = NumberUtils::getNumberFigure($refundDeliveryData[$gVal['orderDeliverySno']]['divisionDeliveryCharge'] / $tempOrderHandle[$gVal['orderDeliverySno']]['iRefundCount'], '0.1', 'round');
                        $updateHandleData[$gVal['handleSno']]['refundUseDepositCommission'] = NumberUtils::getNumberFigure($refundData['refundUseDepositCommission'] * $tempPerPart, '0.1', 'round');
                        $updateHandleData[$gVal['handleSno']]['refundUseMileageCommission'] = NumberUtils::getNumberFigure($refundData['refundUseMileageCommission'] * $tempPerPart, '0.1', 'round');
                        $updateHandleData[$gVal['handleSno']]['refundGiveMileage'] = NumberUtils::getNumberFigure(($refundData['refundGoodsCouponMileage'] + $refundData['refundOrderCouponMileage'] + $refundData['refundGroupMileage']) * $tempPerPart, '0.1', 'round'); //todo 마일리지 합계 안분
                        if ($getData['info']['completeCashPrice'] > 0) {
                            $updateHandleData[$gVal['handleSno']]['refundPrice'] += NumberUtils::getNumberFigure($getData['info']['completeCashPrice'] * $tempPerPart, '0.1', 'round');
                            $updateHandleData[$gVal['handleSno']]['completeCashPrice'] = NumberUtils::getNumberFigure($getData['info']['completeCashPrice'] * $tempPerPart, '0.1', 'round');
                        } else {
                            $updateHandleData[$gVal['handleSno']]['completeCashPrice'] = 0;
                        }
                        if ($getData['info']['completePgPrice']) {
                            $updateHandleData[$gVal['handleSno']]['refundPrice'] += NumberUtils::getNumberFigure($getData['info']['completePgPrice'] * $tempPerPart, '0.1', 'round');
                            $updateHandleData[$gVal['handleSno']]['completePgPrice'] = NumberUtils::getNumberFigure($getData['info']['completePgPrice'] * $tempPerPart, '0.1', 'round');
                        } else {
                            $updateHandleData[$gVal['handleSno']]['completePgPrice'] = 0;
                        }
                        if ($getData['info']['completeDepositPrice'] > 0) {
                            $updateHandleData[$gVal['handleSno']]['refundPrice'] += NumberUtils::getNumberFigure($getData['info']['completeDepositPrice'] * $tempPerPart, '0.1', 'round');
                            $updateHandleData[$gVal['handleSno']]['completeDepositPrice'] = NumberUtils::getNumberFigure($getData['info']['completeDepositPrice'] * $tempPerPart, '0.1', 'round');
                        } else {
                            $updateHandleData[$gVal['handleSno']]['completeDepositPrice'] = 0;
                        }
                        if ($getData['info']['completeMileagePrice'] > 0) {
                            $updateHandleData[$gVal['handleSno']]['refundPrice'] += NumberUtils::getNumberFigure($getData['info']['completeMileagePrice'] * $tempPerPart, '0.1', 'round');
                            $updateHandleData[$gVal['handleSno']]['completeMileagePrice'] = NumberUtils::getNumberFigure($getData['info']['completeMileagePrice'] * $tempPerPart, '0.1', 'round');
                        } else {
                            $updateHandleData[$gVal['handleSno']]['completeMileagePrice'] = 0;
                        }
                        $updateHandleData[$gVal['handleSno']]['orderDeliverySno'] = $gVal['orderDeliverySno'];

                        $tempUpdateHandleDivisionDeliveryChargeSum[$gVal['orderDeliverySno']] += $updateHandleData[$gVal['handleSno']]['refundDeliveryCharge'];
                        $tempUpdateHandleDivisionDeliveryUseDepositSum[$gVal['orderDeliverySno']] += $updateHandleData[$gVal['handleSno']]['refundDeliveryUseDeposit'];
                        $tempUpdateHandleDivisionDeliveryUseMileageSum[$gVal['orderDeliverySno']] += $updateHandleData[$gVal['handleSno']]['refundDeliveryUseMileage'];
                        $tempUpdateHandleDivisionDeliveryCouponSum[$gVal['orderDeliverySno']] += $updateHandleData[$gVal['handleSno']]['refundDeliveryCoupon'];
                        $tempUpdateHandleDivisionUseDepositCommissionSum += $updateHandleData[$gVal['handleSno']]['refundUseDepositCommission'];
                        $tempUpdateHandleDivisionUseMileageCommissionSum += $updateHandleData[$gVal['handleSno']]['refundUseMileageCommission'];
                        $tempUpdateHandleDivisionGiveMileageSum += $updateHandleData[$gVal['handleSno']]['refundGiveMileage'];
                        $tempUpdateHandleDivisionCashPriceSum += $updateHandleData[$gVal['handleSno']]['completeCashPrice'];
                        $tempUpdateHandleDivisionPgPriceSum += $updateHandleData[$gVal['handleSno']]['completePgPrice'];
                        $tempUpdateHandleDivisionDepositPriceSum += $updateHandleData[$gVal['handleSno']]['completeDepositPrice'];
                        $tempUpdateHandleDivisionMileagePriceSum += $updateHandleData[$gVal['handleSno']]['completeMileagePrice'];

                        $tempPrice = ($gVal['goodsPrice'] + $gVal['optionPrice'] + $gVal['optionTextPrice']) * $gVal['goodsCnt'] - $getData['refundCharge' . $gVal['handleSno']];
                        $tempPrice -= ($refundGoodsData[$gVal['sno']]['goodsDcPrice'] + $refundGoodsData[$gVal['sno']]['memberDcPrice'] + $refundGoodsData[$gVal['sno']]['memberOverlapDcPrice'] + $refundGoodsData[$gVal['sno']]['enuri'] + $refundGoodsData[$gVal['sno']]['couponGoodsDcPrice'] + $refundGoodsData[$gVal['sno']]['divisionCouponOrderDcPrice'] + $refundGoodsData[$gVal['sno']]['divisionUseDeposit'] + $refundGoodsData[$gVal['sno']]['divisionUseMileage']);
                        if ($this->myappUseFl) {
                            $tempPrice -= $refundGoodsData[$gVal['sno']]['myappDcPrice'];
                        }

                        if ($gVal['goodsTaxInfo'][0] == 't') {
                            $refundGoodsData[$gVal['sno']]['realTaxSupplyGoodsPrice'] = NumberUtils::getNumberFigure(($tempPrice / (100 + $gVal['goodsTaxInfo'][1])) * 100, '0.1', 'round');
                            $refundGoodsData[$gVal['sno']]['realTaxVatGoodsPrice'] = $tempPrice - $refundGoodsData[$gVal['sno']]['realTaxSupplyGoodsPrice'];
                            $refundGoodsData[$gVal['sno']]['realTaxFreeGoodsPrice'] = 0;
                            $refundGoodsData[$gVal['sno']]['taxSupplyGoodsPrice'] = $refundGoodsData[$gVal['sno']]['realTaxSupplyGoodsPrice'];
                            $refundGoodsData[$gVal['sno']]['taxVatGoodsPrice'] = $refundGoodsData[$gVal['sno']]['realTaxVatGoodsPrice'];
                            $refundGoodsData[$gVal['sno']]['TaxFreeGoodsPrice'] = 0;
                            $orgTotalData['totalRefundChargeTaxSupply'] += NumberUtils::getNumberFigure(($getData['refundCharge' . $gVal['handleSno']] / (100 + $gVal['goodsTaxInfo'][1])) * 100, '0.1', 'round');
                            $orgTotalData['totalRefundChargeTaxVat'] += $getData['refundCharge' . $gVal['handleSno']] - NumberUtils::getNumberFigure(($getData['refundCharge' . $gVal['handleSno']] / (100 + $gVal['goodsTaxInfo'][1])) * 100, '0.1', 'round');
                        } else {
                            $refundGoodsData[$gVal['sno']]['realTaxSupplyGoodsPrice'] = 0;
                            $refundGoodsData[$gVal['sno']]['realTaxVatGoodsPrice'] = 0;
                            $refundGoodsData[$gVal['sno']]['realTaxFreeGoodsPrice'] = $tempPrice;
                            $refundGoodsData[$gVal['sno']]['taxSupplyGoodsPrice'] = 0;
                            $refundGoodsData[$gVal['sno']]['taxVatGoodsPrice'] = 0;
                            $refundGoodsData[$gVal['sno']]['taxFreeGoodsPrice'] = $tempPrice;
                            $orgTotalData['totalRefundChargeTaxFree'] += $getData['refundCharge' . $gVal['handleSno']];
                        }

                        $tempRefundTaxSupplySum += (int)$refundGoodsData[$gVal['sno']]['realTaxSupplyGoodsPrice'];
                        $tempRefundTaxVatSum += (int)$refundGoodsData[$gVal['sno']]['realTaxVatGoodsPrice'];
                        $tempRefundTaxFreeSum += (int)$refundGoodsData[$gVal['sno']]['realTaxFreeGoodsPrice'];
                    } else {
                        $updateGoodsData[$gVal['sno']]['divisionGoodsDeliveryUseDeposit'] = NumberUtils::getNumberFigure($updateDeliveryData[$gVal['orderDeliverySno']]['divisionDeliveryUseDeposit'] / $tempOrderHandle[$gVal['orderDeliverySno']]['iUpdateCount'], '0.1', 'round');
                        $updateGoodsData[$gVal['sno']]['divisionGoodsDeliveryUseMileage'] = NumberUtils::getNumberFigure($updateDeliveryData[$gVal['orderDeliverySno']]['divisionDeliveryUseMileage'] / $tempOrderHandle[$gVal['orderDeliverySno']]['iUpdateCount'], '0.1', 'round');
                        $tempUpdateGoodsDivisionDeliveryUseDepositSum[$gVal['orderDeliverySno']] += $updateGoodsData[$gVal['sno']]['divisionGoodsDeliveryUseDeposit'];
                        $tempUpdateGoodsDivisionDeliveryUseMileageSum[$gVal['orderDeliverySno']] += $updateGoodsData[$gVal['sno']]['divisionGoodsDeliveryUseMileage'];

                        $tempPrice = ($gVal['goodsPrice'] + $gVal['optionPrice'] + $gVal['optionTextPrice']) * $gVal['goodsCnt'];
                        $tempPrice -= ($updateGoodsData[$gVal['sno']]['goodsDcPrice'] + $updateGoodsData[$gVal['sno']]['memberDcPrice'] + $updateGoodsData[$gVal['sno']]['memberOverlapDcPrice'] + $updateGoodsData[$gVal['sno']]['enuri'] + $updateGoodsData[$gVal['sno']]['couponGoodsDcPrice'] + $updateGoodsData[$gVal['sno']]['divisionCouponOrderDcPrice'] + $updateGoodsData[$gVal['sno']]['divisionUseDeposit'] + $updateGoodsData[$gVal['sno']]['divisionUseMileage']);
                        if ($this->myappUseFl) {
                            $tempPrice -= $updateGoodsData[$gVal['sno']]['myappDcPrice'];
                        }

                        if ($gVal['goodsTaxInfo'][0] == 't') {
                            $updateGoodsData[$gVal['sno']]['realTaxSupplyGoodsPrice'] = NumberUtils::getNumberFigure(($tempPrice / (100 + $gVal['goodsTaxInfo'][1])) * 100, '0.1', 'round');
                            $updateGoodsData[$gVal['sno']]['realTaxVatGoodsPrice'] = $tempPrice - $updateGoodsData[$gVal['sno']]['realTaxSupplyGoodsPrice'];
                            $updateGoodsData[$gVal['sno']]['realTaxFreeGoodsPrice'] = 0;
                            $updateGoodsData[$gVal['sno']]['taxSupplyGoodsPrice'] = $updateGoodsData[$gVal['sno']]['realTaxSupplyGoodsPrice'];
                            $updateGoodsData[$gVal['sno']]['taxVatGoodsPrice'] = $updateGoodsData[$gVal['sno']]['realTaxVatGoodsPrice'];
                            $updateGoodsData[$gVal['sno']]['taxFreeGoodsPrice'] = 0;
                        } else {
                            $updateGoodsData[$gVal['sno']]['realTaxSupplyGoodsPrice'] = 0;
                            $updateGoodsData[$gVal['sno']]['realTaxVatGoodsPrice'] = 0;
                            $updateGoodsData[$gVal['sno']]['realTaxFreeGoodsPrice'] = $tempPrice;
                            $updateGoodsData[$gVal['sno']]['taxSupplyGoodsPrice'] = 0;
                            $updateGoodsData[$gVal['sno']]['taxVatGoodsPrice'] = 0;
                            $updateGoodsData[$gVal['sno']]['taxFreeGoodsPrice'] = $tempPrice;
                        }

                        $tempUpdateTaxSupplySum += (int)$updateGoodsData[$gVal['sno']]['realTaxSupplyGoodsPrice'];
                        $tempUpdateTaxVatSum += (int)$updateGoodsData[$gVal['sno']]['realTaxVatGoodsPrice'];
                        $tempUpdateTaxFreeSum += (int)$updateGoodsData[$gVal['sno']]['realTaxFreeGoodsPrice'];
                    }
                }
            }
        }

        foreach ($refundDeliveryData as $k => $v) {
            $updateHandleData = $this->refundDivisionCheck($updateHandleData, $tempUpdateHandleDivisionDeliveryChargeSum[$k], ($v['realTaxSupplyDeliveryCharge'] + $v['realTaxVatDeliveryCharge'] + $v['realTaxFreeDeliveryCharge']), 'refundDeliveryCharge', $k);
            $updateHandleData = $this->refundDivisionCheck($updateHandleData, $tempUpdateHandleDivisionDeliveryUseDepositSum[$k], $v['divisionDeliveryUseDeposit'], 'refundDeliveryUseDeposit', $k);
            $updateHandleData = $this->refundDivisionCheck($updateHandleData, $tempUpdateHandleDivisionDeliveryUseMileageSum[$k], $v['divisionDeliveryUseMileage'], 'refundDeliveryUseMileage', $k);
            $updateHandleData = $this->refundDivisionCheck($updateHandleData, $tempUpdateHandleDivisionDeliveryCouponSum[$k], $v['divisionDeliveryCharge'], 'refundDeliveryCoupon', $k);
        }
        foreach ($updateHandleData as $k => $v) {
            unset($updateHandleData[$k]['orderDeliverySno']);
        }
        foreach ($updateDeliveryData as $k => $v) {
            $updateGoodsData = $this->refundDivisionCheck($updateGoodsData, $tempUpdateGoodsDivisionDeliveryUseDepositSum[$k], $v['divisionDeliveryUseDeposit'], 'divisionGoodsDeliveryUseDeposit', $k);
            $updateGoodsData = $this->refundDivisionCheck($updateGoodsData, $tempUpdateGoodsDivisionDeliveryUseMileageSum[$k], $v['divisionDeliveryUseMileage'], 'divisionGoodsDeliveryUseMileage', $k);
        }
        $updateHandleData = $this->refundDivisionCheck($updateHandleData, $tempUpdateHandleDivisionUseDepositCommissionSum, $refundData['refundUseDepositCommission'], 'refundUseDepositCommission');
        $updateHandleData = $this->refundDivisionCheck($updateHandleData, $tempUpdateHandleDivisionUseMileageCommissionSum, $refundData['refundUseMileageCommission'], 'refundUseMileageCommission');
        $updateHandleData = $this->refundDivisionCheck($updateHandleData, $tempUpdateHandleDivisionGiveMileageSum, ($refundData['refundGoodsCouponMileage'] + $refundData['refundOrderCouponMileage'] + $refundData['refundGroupMileage']), 'refundGiveMileage');

        if ($getData['info']['completeCashPrice'] > 0) {
            $updateHandleData = $this->refundDivisionCheck($updateHandleData, $tempUpdateHandleDivisionCashPriceSum, ($getData['info']['completeCashPrice']), 'completeCashPrice');
        }
        if ($getData['info']['completePgPrice']) {
            $updateHandleData = $this->refundDivisionCheck($updateHandleData, $tempUpdateHandleDivisionPgPriceSum, ($getData['info']['completePgPrice']), 'completePgPrice');
        }
        if ($getData['info']['completeDepositPrice'] > 0) {
            $updateHandleData = $this->refundDivisionCheck($updateHandleData, $tempUpdateHandleDivisionDepositPriceSum, ($getData['info']['completeDepositPrice']), 'completeDepositPrice');
        }
        if ($getData['info']['completeMileagePrice'] > 0) {
            $updateHandleData = $this->refundDivisionCheck($updateHandleData, $tempUpdateHandleDivisionMileagePriceSum, ($getData['info']['completeMileagePrice']), 'completeMileagePrice');
        }
        $tempDivisionRefundPrice = $tempUpdateHandleDivisionCashPriceSum + $tempUpdateHandleDivisionPgPriceSum + $tempUpdateHandleDivisionDepositPriceSum + $tempUpdateHandleDivisionMileagePriceSum;
        $tempOrgRefundPrice = $getData['info']['completeCashPrice'] + $getData['info']['completePgPrice'] + $getData['info']['completeDepositPrice'] + $getData['info']['completeMileagePrice'];
        $updateHandleData = $this->refundDivisionCheck($updateHandleData, $tempDivisionRefundPrice, $tempOrgRefundPrice, 'refundPrice');

        foreach ($orderInfo['goods'] as $sKey => $sVal) {
            foreach ($sVal as $dKey => $dVal) {
                foreach ($dVal as $gKey => $gVal) {
                    if (in_array($gVal['sno'], $refundTarget)) { // 환불 대상처리
                        $refundGoodsData[$gVal['sno']]['divisionGoodsDeliveryUseDeposit'] = $updateHandleData[$gVal['handleSno']]['refundDeliveryUseDeposit'];
                        $refundGoodsData[$gVal['sno']]['divisionGoodsDeliveryUseMileage'] = $updateHandleData[$gVal['handleSno']]['refundDeliveryUseMileage'];
                    }
                }
            }
        }

        // 원본 tax금액에서 update와 refund 금액일 비교해서 비율을 맞춘다update쪽을 나중에 맞춘다(최대한 수정안하는방향으로)
        $orgTotalDataCheck = array();
        $orgTotalDataCheck['realTaxSupplyPrice'] = $orgTotalData['realTaxSupplyPrice'] - $orgTotalData['totalRefundChargeTaxSupply'] - $tempUpdateDeliveryTaxSupplySum - $tempRefundDeliveryTaxSupplySum;
        $orgTotalDataCheck['realTaxVatPrice'] = $orgTotalData['realTaxVatPrice'] - $orgTotalData['totalRefundChargeTaxVat'] - $tempUpdateDeliveryTaxVatSum - $tempRefundDeliveryTaxVatSum;
        $orgTotalDataCheck['realTaxFreePrice'] = $orgTotalData['realTaxFreePrice'] - $orgTotalData['totalRefundChargeTaxFree'] - $tempUpdateDeliveryTaxFreeSum - $tempRefundDeliveryTaxFreeSum;
        $tempLeftRefundTaxSupply = 0; // refund쪽에서 금액 맞추고 update쪽에서 남아야할 공급가액
        $tempLeftRefundTaxVat = 0; // refund쪽에서 금액 맞추고 update쪽에서 남아야할 과세금액
        $tempLeftRefundTaxFree = 0; // refund쪽에서 금액 맞추고 update쪽에서 남아야할 비과세금액
        $tempCheckUpdateTaxSupply = 0; // 중간계산시 체크해야할 잔여금액
        $tempCheckUpdateTaxSupplySum = $tempUpdateTaxSupplySum;
        $tempCheckUpdateTaxVat = 0; // 중간계산시 체크해야할 잔여금액
        $tempCheckUpdateTaxVatSum = $tempUpdateTaxVatSum;
        $tempCheckUpdateTaxFree = 0; // 중간계산시 체크해야할 잔여금액
        $tempCheckUpdateTaxFreeSum = $tempUpdateTaxFreeSum;
        $tempCheckRefundTaxSupply = 0; // 중간계산시 체크해야할 잔여금액
        $tempCheckRefundTaxSupplySum = $tempRefundTaxSupplySum;
        $tempCheckRefundTaxSupplyCount = 0;
        $tempCheckRefundTaxVat = 0; // 중간계산시 체크해야할 잔여금액
        $tempCheckRefundTaxVatSum = $tempRefundTaxVatSum;
        $tempCheckRefundTaxVatCount = 0;
        $tempCheckRefundTaxFree = 0; // 중간계산시 체크해야할 잔여금액
        $tempCheckRefundTaxFreeSum = $tempRefundTaxFreeSum;
        $tempCheckRefundTaxFreeCount = 0;

        // 총합이 같으면 각각 값만 맞춰주고 다른경우 아래 방식으로 맞춰준다
        $sumCheckRealTaxPrice = intval($orgTotalDataCheck['realTaxSupplyPrice']) + intval($orgTotalDataCheck['realTaxVatPrice']) + intval($orgTotalDataCheck['realTaxFreePrice']);
        $sumTempRealTaxPrice = intval($tempCheckRefundTaxSupplySum) + intval($tempCheckRefundTaxVatSum) + intval($tempCheckRefundTaxFreeSum);
        if ($sumCheckRealTaxPrice == $sumTempRealTaxPrice) {
            if ($sumCheckRealTaxPrice > 0) {
                $refundGoodsData = $this->refundDivisionCheck($refundGoodsData, $tempCheckRefundTaxSupplySum, $orgTotalDataCheck['realTaxSupplyPrice'], 'realTaxSupplyGoodsPrice');
                $refundGoodsData = $this->refundDivisionCheck($refundGoodsData, $tempCheckRefundTaxVatSum, $orgTotalDataCheck['realTaxVatPrice'], 'realTaxVatGoodsPrice');
                $refundGoodsData = $this->refundDivisionCheck($refundGoodsData, $tempCheckRefundTaxFreeSum, $orgTotalDataCheck['realTaxFreePrice'], 'realTaxFreeGoodsPrice');
            }
        } else {
            // 1. refund쪽에서 먼저 금액을 맞춰준다
            if ($orgTotalDataCheck['realTaxSupplyPrice'] < $tempCheckRefundTaxSupplySum) $tempCheckRefundTaxSupplyCount++; // refund의 공급가가 원금액보다 큰경우
            if ($orgTotalDataCheck['realTaxVatPrice'] < $tempCheckRefundTaxVatSum) $tempCheckRefundTaxVatCount++; // refund의 과세가 원금액보다 큰경우
            if ($orgTotalDataCheck['realTaxFreePrice'] < $tempCheckRefundTaxFreeSum) $tempCheckRefundTaxFreeCount++; // refund의 비과세가 원금액보다 큰경우

            if ($tempCheckRefundTaxSupplyCount == 0 && $tempCheckRefundTaxVatCount == 0 && $tempCheckRefundTaxFreeCount == 0) { // refund의 금액 항목중에 원금액보다 큰경우가 없으면
                $tempLeftRefundTaxSupply = $orgTotalDataCheck['realTaxSupplyPrice'] - $tempCheckRefundTaxSupplySum; // refund쪽에서 금액 맞추고 update쪽에서 남아야할 공급가액
                $tempLeftRefundTaxVat = $orgTotalDataCheck['realTaxVatPrice'] - $tempCheckRefundTaxVatSum; // refund쪽에서 금액 맞추고 update쪽에서 남아야할 과세금액
                $tempLeftRefundTaxFree = $orgTotalDataCheck['realTaxFreePrice'] - $tempCheckRefundTaxFreeSum; // refund쪽에서 금액 맞추고 update쪽에서 남아야할 비과세금액
            } else {
                if ($tempCheckRefundTaxSupplyCount == 0 && $tempCheckRefundTaxVatCount == 0 && $tempCheckRefundTaxFreeCount > 0) { // 1-1. refund의 금액 항목중에 free만 원금액보다 큰경우
                    $tempLeftRefundTaxFree = 0; // refund쪽에서 금액 맞추고 update쪽에서 남아야할 비과세금액
                    $tempCheckRefundTaxFree = $tempCheckRefundTaxFreeSum - $orgTotalDataCheck['realTaxFreePrice'];
                    $refundGoodsData = $this->refundDivisionCheck($refundGoodsData, $tempCheckRefundTaxFreeSum, $orgTotalDataCheck['realTaxFreePrice'], 'realTaxFreeGoodsPrice');

                    if ($tempCheckRefundTaxFree + $tempCheckRefundTaxSupplySum > $orgTotalDataCheck['realTaxSupplyPrice']) { // 다른곳에서 오버된금액이 할당될 기준이 필요하기에 우선적으로 공급가에 밀어넣도록 기준을 잡아줌
                        $tempCheckRefundTaxSupply = $tempCheckRefundTaxFree - ($orgTotalDataCheck['realTaxSupplyPrice'] - $tempCheckRefundTaxSupplySum);
                        $tempLeftRefundTaxSupply = 0;
                        $refundGoodsData = $this->refundDivisionCheck($refundGoodsData, $tempCheckRefundTaxSupplySum, $orgTotalDataCheck['realTaxSupplyPrice'], 'realTaxSupplyGoodsPrice');

                        $tempCheckRefundTaxVat = $tempCheckRefundTaxSupply + $tempCheckRefundTaxVatSum;
                        $tempLeftRefundTaxVat = $orgTotalDataCheck['realTaxVatPrice'] - $tempCheckRefundTaxVat;
                        $refundGoodsData = $this->refundDivisionCheck($refundGoodsData, $tempCheckRefundTaxVatSum, $tempCheckRefundTaxVat, 'realTaxVatGoodsPrice');
                    } else {
                        $tempCheckRefundTaxSupply = $tempCheckRefundTaxFree + $tempCheckRefundTaxSupplySum;
                        $tempLeftRefundTaxSupply = $orgTotalDataCheck['realTaxSupplyPrice'] - $tempCheckRefundTaxSupply;
                        $refundGoodsData = $this->refundDivisionCheck($refundGoodsData, $tempCheckRefundTaxSupplySum, $tempCheckRefundTaxSupply, 'realTaxSupplyGoodsPrice');

                        $tempLeftRefundTaxVat = $orgTotalDataCheck['realTaxVatPrice'] - $tempCheckRefundTaxVatSum;
                    }
                }

                if ($tempCheckRefundTaxSupplyCount > 0 && $tempCheckRefundTaxVatCount == 0 && $tempCheckRefundTaxFreeCount == 0) { // 1-2. refund의 금액 항목중에 supply만 원금액보다 큰경우
                    $tempLeftRefundTaxSupply = 0; // refund쪽에서 금액 맞추고 update쪽에서 남아야할 공급가금액
                    $tempCheckRefundTaxSupply = $tempCheckRefundTaxSupplySum - $orgTotalDataCheck['realTaxSupplyPrice'];
                    $refundGoodsData = $this->refundDivisionCheck($refundGoodsData, $tempCheckRefundTaxSupplySum, $orgTotalDataCheck['realTaxSupplyPrice'], 'realTaxSupplyGoodsPrice');

                    if ($tempCheckRefundTaxSupply + $tempCheckRefundTaxVatSum > $orgTotalDataCheck['realTaxVatPrice']) { // 다른곳에서 오버된금액이 할당될 기준이 필요하기에 우선적으로 부가세에 밀어넣도록 기준을 잡아줌
                        $tempCheckRefundTaxVat = $tempCheckRefundTaxSupply - ($orgTotalDataCheck['realTaxVatPrice'] - $tempCheckRefundTaxVatSum);
                        $tempLeftRefundTaxVat = 0;
                        $refundGoodsData = $this->refundDivisionCheck($refundGoodsData, $tempCheckRefundTaxVatSum, $orgTotalDataCheck['realTaxVatPrice'], 'realTaxVatGoodsPrice');

                        $tempCheckRefundTaxFree = $tempCheckRefundTaxVat + $tempCheckRefundTaxFreeSum;
                        $tempLeftRefundTaxFree = $orgTotalDataCheck['realTaxFreePrice'] - $tempCheckRefundTaxFree;
                        $refundGoodsData = $this->refundDivisionCheck($refundGoodsData, $tempCheckRefundTaxFreeSum, $tempCheckRefundTaxFree, 'realTaxFreeGoodsPrice');
                    } else {
                        $tempCheckRefundTaxVat = $tempCheckRefundTaxSupply + $tempCheckRefundTaxVatSum;
                        $tempLeftRefundTaxVat = $orgTotalDataCheck['realTaxVatPrice'] - $tempCheckRefundTaxVat;
                        $refundGoodsData = $this->refundDivisionCheck($refundGoodsData, $tempCheckRefundTaxVatSum, $tempCheckRefundTaxVat, 'realTaxVatGoodsPrice');

                        $tempLeftRefundTaxFree = $orgTotalDataCheck['realTaxFreePrice'] - $tempCheckRefundTaxFreeSum;
                    }
                }

                if ($tempCheckRefundTaxSupplyCount == 0 && $tempCheckRefundTaxVatCount > 0 && $tempCheckRefundTaxFreeCount == 0) { // 1-3. refund의 금액 항목중에 vat만 원금액보다 큰경우
                    $tempLeftRefundTaxVat = 0; // refund쪽에서 금액 맞추고 update쪽에서 남아야할 과세금액
                    $tempCheckRefundTaxVat = $tempCheckRefundTaxVatSum - $orgTotalDataCheck['realTaxVatPrice'];
                    $refundGoodsData = $this->refundDivisionCheck($refundGoodsData, $tempCheckRefundTaxVatSum, $orgTotalDataCheck['realTaxVatPrice'], 'realTaxVatGoodsPrice');

                    if ($tempCheckRefundTaxVat + $tempCheckRefundTaxSupplySum > $orgTotalDataCheck['realTaxSupplyPrice']) { // 다른곳에서 오버된금액이 할당될 기준이 필요하기에 우선적으로 공급가에 밀어넣도록 기준을 잡아줌
                        $tempCheckRefundTaxSupply = $tempCheckRefundTaxVat - ($orgTotalDataCheck['realTaxSupplyPrice'] - $tempCheckRefundTaxSupplySum);
                        $tempLeftRefundTaxSupply = 0;
                        $refundGoodsData = $this->refundDivisionCheck($refundGoodsData, $tempCheckRefundTaxSupplySum, $orgTotalDataCheck['realTaxSupplyPrice'], 'realTaxSupplyGoodsPrice');

                        $tempCheckRefundTaxFree = $tempCheckRefundTaxSupply + $tempCheckRefundTaxFreeSum;
                        $tempLeftRefundTaxFree = $orgTotalDataCheck['realTaxFreePrice'] - $tempCheckRefundTaxFree;
                        $refundGoodsData = $this->refundDivisionCheck($refundGoodsData, $tempCheckRefundTaxFreeSum, $tempCheckRefundTaxFree, 'realTaxFreeGoodsPrice');
                    } else {
                        $tempCheckRefundTaxSupply = $tempCheckRefundTaxVat + $tempCheckRefundTaxSupplySum;
                        $tempLeftRefundTaxSupply = $orgTotalDataCheck['realTaxSupplyPrice'] - $tempCheckRefundTaxSupply;
                        $refundGoodsData = $this->refundDivisionCheck($refundGoodsData, $tempCheckRefundTaxSupplySum, $tempCheckRefundTaxSupply, 'realTaxSupplyGoodsPrice');

                        $tempLeftRefundTaxFree = $orgTotalDataCheck['realTaxFreePrice'] - $tempCheckRefundTaxFreeSum;
                    }
                }

                if ($tempCheckRefundTaxSupplyCount > 0 && $tempCheckRefundTaxVatCount > 0 && $tempCheckRefundTaxFreeCount == 0) { // 1-4. refund의 금액 항목중에 supply와 vat가 원금액보다 큰경우
                    $tempLeftRefundTaxSupply = 0; // refund쪽에서 금액 맞추고 update쪽에서 남아야할 과세금액
                    $tempLeftRefundTaxVat = 0; // refund쪽에서 금액 맞추고 update쪽에서 남아야할 과세금액
                    $tempCheckRefundTaxSupply = $tempCheckRefundTaxSupplySum - $orgTotalDataCheck['realTaxSupplyPrice'];
                    $tempCheckRefundTaxVat = $tempCheckRefundTaxVatSum - $orgTotalDataCheck['realTaxVatPrice'];
                    $refundGoodsData = $this->refundDivisionCheck($refundGoodsData, $tempCheckRefundTaxSupplySum, $orgTotalDataCheck['realTaxSupplyPrice'], 'realTaxSupplyGoodsPrice');
                    $refundGoodsData = $this->refundDivisionCheck($refundGoodsData, $tempCheckRefundTaxVatSum, $orgTotalDataCheck['realTaxVatPrice'], 'realTaxVatGoodsPrice');

                    $tempCheckRefundTaxFree = $tempCheckRefundTaxSupply + $tempCheckRefundTaxVat + $tempCheckRefundTaxFreeSum;
                    $tempLeftRefundTaxFree = $orgTotalDataCheck['realTaxFreePrice'] - $tempCheckRefundTaxFree;
                    $refundGoodsData = $this->refundDivisionCheck($refundGoodsData, $tempCheckRefundTaxFreeSum, $tempCheckRefundTaxFree, 'realTaxFreeGoodsPrice');
                }

                if ($tempCheckRefundTaxSupplyCount > 0 && $tempCheckRefundTaxVatCount == 0 && $tempCheckRefundTaxFreeCount > 0) { // 1-5. refund의 금액 항목중에 supply와 free가 원금액보다 큰경우
                    $tempLeftRefundTaxSupply = 0; // refund쪽에서 금액 맞추고 update쪽에서 남아야할 과세금액
                    $tempLeftRefundTaxFree = 0; // refund쪽에서 금액 맞추고 update쪽에서 남아야할 과세금액
                    $tempCheckRefundTaxSupply = $tempCheckRefundTaxSupplySum - $orgTotalDataCheck['realTaxSupplyPrice'];
                    $tempCheckRefundTaxFree = $tempCheckRefundTaxFreeSum - $orgTotalDataCheck['realTaxFreePrice'];
                    $refundGoodsData = $this->refundDivisionCheck($refundGoodsData, $tempCheckRefundTaxSupplySum, $orgTotalDataCheck['realTaxSupplyPrice'], 'realTaxSupplyGoodsPrice');
                    $refundGoodsData = $this->refundDivisionCheck($refundGoodsData, $tempCheckRefundTaxFreeSum, $orgTotalDataCheck['realTaxFreePrice'], 'realTaxFreeGoodsPrice');

                    $tempCheckRefundTaxVat = $tempCheckRefundTaxSupply + $tempCheckRefundTaxFree + $tempCheckRefundTaxVatSum;
                    $tempLeftRefundTaxVat = $orgTotalDataCheck['realTaxVatPrice'] - $tempCheckRefundTaxVat;
                    $refundGoodsData = $this->refundDivisionCheck($refundGoodsData, $tempCheckRefundTaxVatSum, $tempCheckRefundTaxVat, 'realTaxVatGoodsPrice');
                }

                if ($tempCheckRefundTaxSupplyCount == 0 && $tempCheckRefundTaxVatCount > 0 && $tempCheckRefundTaxFreeCount > 0) { // 1-6. refund의 금액 항목중에 vat와 free가 원금액보다 큰경우
                    $tempLeftRefundTaxVat = 0; // refund쪽에서 금액 맞추고 update쪽에서 남아야할 과세금액
                    $tempLeftRefundTaxFree = 0; // refund쪽에서 금액 맞추고 update쪽에서 남아야할 과세금액
                    $tempCheckRefundTaxVat = $tempCheckRefundTaxVatSum - $orgTotalDataCheck['realTaxVatPrice'];
                    $tempCheckRefundTaxFree = $tempCheckRefundTaxFreeSum - $orgTotalDataCheck['realTaxFreePrice'];
                    $refundGoodsData = $this->refundDivisionCheck($refundGoodsData, $tempCheckRefundTaxVatSum, $orgTotalDataCheck['realTaxVatPrice'], 'realTaxVatGoodsPrice');
                    $refundGoodsData = $this->refundDivisionCheck($refundGoodsData, $tempCheckRefundTaxFreeSum, $orgTotalDataCheck['realTaxFreePrice'], 'realTaxFreeGoodsPrice');

                    $tempCheckRefundTaxSupply = $tempCheckRefundTaxVat + $tempCheckRefundTaxFree + $tempCheckRefundTaxSupplySum;
                    $tempLeftRefundTaxSupply = $orgTotalDataCheck['realTaxSupplyPrice'] - $tempCheckRefundTaxSupply;
                    $refundGoodsData = $this->refundDivisionCheck($refundGoodsData, $tempCheckRefundTaxSupplySum, $tempCheckRefundTaxSupply, 'realTaxSupplyGoodsPrice');
                }
            }

            // 2. update를 처리해준다
            $updateGoodsData = $this->refundDivisionCheck($updateGoodsData, $tempCheckUpdateTaxSupplySum, $tempLeftRefundTaxSupply, 'realTaxSupplyGoodsPrice');
            $updateGoodsData = $this->refundDivisionCheck($updateGoodsData, $tempCheckUpdateTaxVatSum, $tempLeftRefundTaxVat, 'realTaxVatGoodsPrice');
            $updateGoodsData = $this->refundDivisionCheck($updateGoodsData, $tempCheckUpdateTaxFreeSum, $tempLeftRefundTaxFree, 'realTaxFreeGoodsPrice');
        }

        // 조절된 realtax값
        $refundEtcInfo['realTaxSupplyPrice']  = 0;
        $refundEtcInfo['realTaxVatPrice'] = 0;
        $refundEtcInfo['realTaxFreePrice'] = 0;
        foreach ($refundGoodsData as $k => $v) {
            $refundEtcInfo['realTaxSupplyPrice'] += $v['realTaxSupplyGoodsPrice'];
            $refundEtcInfo['realTaxVatPrice'] += $v['realTaxVatGoodsPrice'];
            $refundEtcInfo['realTaxFreePrice'] += $v['realTaxFreeGoodsPrice'];
        }
        foreach ($refundDeliveryData as $k => $v) {
            $refundEtcInfo['realTaxSupplyPrice'] += $v['realTaxSupplyDeliveryCharge'];
            $refundEtcInfo['realTaxVatPrice'] += $v['realTaxVatDeliveryCharge'];
            $refundEtcInfo['realTaxFreePrice'] += $v['realTaxFreeDeliveryCharge'];
        }
        $updateOrderData['realTaxSupplyPrice'] = 0;
        $updateOrderData['realTaxVatPrice'] = 0;
        $updateOrderData['realTaxFreePrice'] = 0;
        foreach ($updateGoodsData as $k => $v) {
            $updateOrderData['realTaxSupplyPrice'] += $v['realTaxSupplyGoodsPrice'];
            $updateOrderData['realTaxVatPrice'] += $v['realTaxVatGoodsPrice'];
            $updateOrderData['realTaxFreePrice'] += $v['realTaxFreeGoodsPrice'];
        }
        foreach ($updateDeliveryData as $k => $v) {
            $updateOrderData['realTaxSupplyPrice'] += $v['realTaxSupplyDeliveryCharge'];
            $updateOrderData['realTaxVatPrice'] += $v['realTaxVatDeliveryCharge'];
            $updateOrderData['realTaxFreePrice'] += $v['realTaxFreeDeliveryCharge'];
        }

        // 업데이트 직전 시점에서 원래 error_reporting값을 돌림
        error_reporting($errorReportingValue);

        // es_orderGooods업데이트
        foreach ($refundGoodsData as $k => $v) {
            $this->updateOrderGoods($v, $getData['orderNo'], $k);
        }
        foreach ($updateGoodsData as $k => $v) {
            $this->updateOrderGoods($v, $getData['orderNo'], $k);
        }

        //es_orderDelivery업데이트
        foreach ($updateDeliveryData as $k => $v) {
            $this->updateOrderDelivery($v, $getData['orderNo'], $k);
        }

        //es_orderHandle업데이트
        foreach ($updateHandleData as $k => $v) {
            $this->updateOrderHandle($v, $k);
        }

        //es_order업데이트
        $updateOrderData['adminMemo'] = $getData['adminOrderGoodsMemo'];
        $this->updateOrder($getData['orderNo'], 'n', $updateOrderData);

        //관리자 선택에 따른 쿠폰 복원 처리
        $this->restoreRefundCoupon($getData);

        // 사용된 예치금 일괄 복원
        $this->restoreRefundUseDeposit($getData, $orderInfo, $refundEtcInfo);

        // 사용된 마일리지 일괄 복원
        $this->restoreRefundUseMileage($getData, $orderInfo, $refundEtcInfo);

        // 적립 마일리지 차감
        $this->minusRefundGiveMileage($getData, $orderInfo, $refundEtcInfo);

        // 회원정보 - 구매정보에서 환불내역 제거
        $this->updateRefundMemberPrice($getData, $orderInfo, $refundEtcInfo);

        // 환불금액 처리
        $returnError = $this->completeRefundPrice($getData, $orderInfo, $refundEtcInfo);
        if (trim($returnError) !== '') {
            throw new Exception(__($returnError));
        }

        //debug($refundGoodsData);
        //debug($getData);
        //debug($refundEtcInfo);
        //throw new Exception('err');
        // aaa
        //throw new Exception('test');
    }

    /**
     * getRefundEtcData
     * 환불 - 환불상품 외 나머지 상품들에 대해 환불프로세스에서 사용할 데이터조합
     *
     * @param array $getData
     * @param integer $handleSno
     * @param integer $isAll
     * @param array $orderRefundInfo
     *
     * @return array $totalOrderGoodsRateData
     */
    public function getRefundEtcData($getData, $handleSno, $isAll, $orderRefundInfo)
    {
        if(!is_object($orderAdmin)){
            $orderAdmin = App::load(\Component\Order\OrderAdmin::class);
        }

        // 주문전체 데이터를 체크하여 금액기준으로 지분을 설정함. > 부가결제금액 초과 취소로 인한 금액 realtax 갱신을 위함
        $tmpTotalOrderGoodsRateData = $totalOrderGoodsRateData = [
            'goods' => [],
            'delivery' => [],
            'groupDeliveryGoods' => [],
        ];
        $divisionDeliverySettlePrice = [];
        $totalOrderGoodsSettlePriceSum = $totalOrderDeliverySettlePriceSum = 0;
        $totalOrderInfo = $orderAdmin->getOrderView($getData['orderNo'], null);
        $onlyOverseasDelivery = false;
        foreach ($totalOrderInfo['goods'] as $sKey => $sVal) {
            foreach ($sVal as $dKey => $dVal) {
                foreach ($dVal as $gKey => $gVal) {
                    //현재 취소하려는 주문상품 제외
                    if((int)$isAll === 1){
                        if(in_array(substr($gVal['orderStatus'], 0, 1), ['r'])){
                            continue;
                        }
                    }
                    else {
                        if((int)$gVal['handleSno'] === (int)$handleSno){
                            continue;
                        }
                    }
                    // 취소, 교환취소 주문상품 제외
                    if(in_array(substr($gVal['orderStatus'], 0, 1), ['e', 'c'])){
                        continue;
                    }
                    // 환불완료 주문상품 제외
                    if(in_array($gVal['orderStatus'], ['r3'])){
                        continue;
                    }

                    if (count($tmpTotalOrderGoodsRateData['delivery'][$gVal['orderDeliverySno']]) < 1 && in_array($gVal['orderDeliverySno'], $orderRefundInfo['refundOrderDeliverySno']) === false) {
                        if ($totalOrderInfo['mallSno'] == DEFAULT_MALL_NUMBER || ($totalOrderInfo['mallSno'] > DEFAULT_MALL_NUMBER && $onlyOverseasDelivery === false)) {

                            $realDeliverySettlePrice = $gVal['realTaxSupplyDeliveryCharge'] + $gVal['realTaxVatDeliveryCharge'] + $gVal['realTaxFreeDeliveryCharge'];
                            $settleDeliveryPrice = $realDeliverySettlePrice + $gVal['divisionDeliveryUseDeposit'] + $gVal['divisionDeliveryUseMileage'];
                            $tmpTotalOrderGoodsRateData['delivery'][$gVal['orderDeliverySno']] = [
                                'settlePrice' => $settleDeliveryPrice,
                                'realSettlePrice' => $realDeliverySettlePrice,
                                'info' => $gVal,
                            ];

                            $totalOrderDeliverySettlePriceSum += $settleDeliveryPrice;

                            // 해외배송비
                            $onlyOverseasDelivery = true;
                        }
                    }

                    $realGoodsSettlePrice = ($gVal['realTaxSupplyGoodsPrice'] + $gVal['realTaxVatGoodsPrice'] + $gVal['realTaxFreeGoodsPrice']);
                    $goodsSettlePrice = $realGoodsSettlePrice + $gVal['divisionUseDeposit'] + $gVal['divisionUseMileage'];
                    $totalOrderGoodsSettlePriceSum += $goodsSettlePrice;

                    $tmpTotalOrderGoodsRateData['goods'][$gVal['sno']] = [
                        'settlePrice' => $goodsSettlePrice,
                        'realSettlePrice' => $realGoodsSettlePrice,
                        'info' => $gVal,
                    ];

                    // 배송비별로 나뉜 주문상품의 결제가
                    $divisionDeliverySettlePrice[$gVal['orderDeliverySno']] += $goodsSettlePrice;

                    $tmpTotalOrderGoodsRateData['groupDeliveryGoods'][$gVal['orderDeliverySno']][$gVal['sno']] = [
                        'goodsSettlePrice' => $goodsSettlePrice,
                    ];
                }
            }
        }

        if(count($tmpTotalOrderGoodsRateData['groupDeliveryGoods']) > 0){
            foreach($tmpTotalOrderGoodsRateData['groupDeliveryGoods'] as $dKey => $dVal){
                foreach($dVal as $gKey => $gVal){
                    $totalOrderGoodsRateData['groupDeliveryGoods'][$dKey][$gKey]['goodsSettlePrice'] = $gVal['goodsSettlePrice'];
                    $totalOrderGoodsRateData['groupDeliveryGoods'][$dKey][$gKey]['divisionTotalSettlePrice'] = $divisionDeliverySettlePrice[$dKey];
                }
            }
        }

        if(count($tmpTotalOrderGoodsRateData['goods']) > 0){
            $totalRate = 100;
            $index = 1;

            foreach($tmpTotalOrderGoodsRateData['goods'] as $key => $value){
                if($index === count($tmpTotalOrderGoodsRateData['goods'])){
                    $totalOrderGoodsRateData['goods'][$key]['rate'] = $totalRate;
                    $totalOrderGoodsRateData['goods'][$key]['info'] = $value['info'];
                    $totalOrderGoodsRateData['goods'][$key]['settlePrice'] = $value['settlePrice'];
                }
                else {
                    $totalOrderGoodsRateData['goods'][$key]['rate'] = NumberUtils::getNumberFigure(($value['settlePrice'] / $totalOrderGoodsSettlePriceSum) * 100, '0.001', 'round');
                    $totalOrderGoodsRateData['goods'][$key]['info'] = $value['info'];
                    $totalOrderGoodsRateData['goods'][$key]['settlePrice'] = $value['settlePrice'];
                    $totalRate -= $totalOrderGoodsRateData['goods'][$key]['rate'];
                }

                $totalOrderGoodsRateData['goods'][$key]['rateTax']= [
                    'supply' => NumberUtils::getNumberFigure(($value['info']['realTaxSupplyGoodsPrice'] / $value['realSettlePrice']) * 100, '0.001', 'round'),
                    'vat' => NumberUtils::getNumberFigure(($value['info']['realTaxVatGoodsPrice'] / $value['realSettlePrice']) * 100, '0.001', 'round'),
                    'free' => NumberUtils::getNumberFigure(($value['info']['realTaxFreeGoodsPrice'] / $value['realSettlePrice']) * 100, '0.001', 'round'),
                ];

                list($totalOrderGoodsRateData['goods'][$key]['rateTax']['supply'], $totalOrderGoodsRateData['goods'][$key]['rateTax']['vat'], $totalOrderGoodsRateData['goods'][$key]['rateTax']['free']) = $this->getRealTaxBalance(100, $totalOrderGoodsRateData['goods'][$key]['rateTax']['supply'], $totalOrderGoodsRateData['goods'][$key]['rateTax']['vat'], $totalOrderGoodsRateData['goods'][$key]['rateTax']['free']);

                $realTaxRateSum = array_sum($totalOrderGoodsRateData['goods'][$key]['rateTax']);
                if($realTaxRateSum !== 100){
                    if($totalOrderGoodsRateData['goods'][$key]['rateTax']['vat'] > 0){
                        $totalOrderGoodsRateData['goods'][$key]['rateTax']['supply'] = $realTaxRateSum - $totalOrderGoodsRateData['goods'][$key]['rateTax']['vat'] - $totalOrderGoodsRateData['goods'][$key]['rateTax']['free'];
                    }
                    else if($totalOrderGoodsRateData['goods'][$key]['rateTax']['free'] > 0){
                        $totalOrderGoodsRateData['goods'][$key]['rateTax']['free'] = $realTaxRateSum - $totalOrderGoodsRateData['goods'][$key]['rateTax']['supply'] - $totalOrderGoodsRateData['goods'][$key]['rateTax']['vat'];
                    }
                    else if($totalOrderGoodsRateData['goods'][$key]['rateTax']['supply'] > 0){
                        $totalOrderGoodsRateData['goods'][$key]['rateTax']['supply'] = $realTaxRateSum - $totalOrderGoodsRateData['goods'][$key]['rateTax']['vat'] - $totalOrderGoodsRateData['goods'][$key]['rateTax']['free'];
                    }
                }

                $index++;
            }
        }

        if(count($tmpTotalOrderGoodsRateData['delivery']) > 0){
            $totalRate = 100;
            $index = 1;

            foreach($tmpTotalOrderGoodsRateData['delivery'] as $key => $value){
                if($index === count($tmpTotalOrderGoodsRateData['delivery'])){
                    $totalOrderGoodsRateData['delivery'][$key]['rate'] = $totalRate;
                    $totalOrderGoodsRateData['delivery'][$key]['info'] = $value['info'];
                    $totalOrderGoodsRateData['delivery'][$key]['settlePrice'] = $value['settlePrice'];
                }
                else {
                    $totalOrderGoodsRateData['delivery'][$key]['rate'] = NumberUtils::getNumberFigure(($value['settlePrice'] / $totalOrderDeliverySettlePriceSum) * 100, '0.001', 'round');
                    $totalOrderGoodsRateData['delivery'][$key]['info'] = $value['info'];
                    $totalOrderGoodsRateData['delivery'][$key]['settlePrice'] = $value['settlePrice'];
                    $totalRate -= $totalOrderGoodsRateData['delivery'][$key]['rate'];
                }

                $totalOrderGoodsRateData['delivery'][$key]['rateTax']= [
                    'supply' => NumberUtils::getNumberFigure(($value['info']['realTaxSupplyDeliveryCharge'] / $value['realSettlePrice']) * 100, '0.001', 'round'),
                    'vat' => NumberUtils::getNumberFigure(($value['info']['realTaxVatDeliveryCharge'] / $value['realSettlePrice']) * 100, '0.001', 'round'),
                    'free' => NumberUtils::getNumberFigure(($value['info']['realTaxFreeDeliveryCharge'] / $value['realSettlePrice']) * 100, '0.001', 'round'),
                ];

                list($totalOrderGoodsRateData['delivery'][$key]['rateTax']['supply'], $totalOrderGoodsRateData['delivery'][$key]['rateTax']['vat'], $totalOrderGoodsRateData['delivery'][$key]['rateTax']['free']) = $this->getRealTaxBalance(100, $totalOrderGoodsRateData['delivery'][$key]['rateTax']['supply'], $totalOrderGoodsRateData['delivery'][$key]['rateTax']['vat'], $totalOrderGoodsRateData['delivery'][$key]['rateTax']['free']);

                $realTaxRateSum = array_sum($totalOrderGoodsRateData['delivery'][$key]['rateTax']);
                if($realTaxRateSum !== 100){
                    if($totalOrderGoodsRateData['delivery'][$key]['rateTax']['vat'] > 0){
                        $totalOrderGoodsRateData['delivery'][$key]['rateTax']['supply'] = $realTaxRateSum - $totalOrderGoodsRateData['delivery'][$key]['rateTax']['vat'] - $totalOrderGoodsRateData['delivery'][$key]['rateTax']['free'];
                    }
                    else if($totalOrderGoodsRateData['delivery'][$key]['rateTax']['free'] > 0){
                        $totalOrderGoodsRateData['delivery'][$key]['rateTax']['free'] = $realTaxRateSum - $totalOrderGoodsRateData['delivery'][$key]['rateTax']['supply'] - $totalOrderGoodsRateData['delivery'][$key]['rateTax']['vat'];
                    }
                    else if($totalOrderGoodsRateData['delivery'][$key]['rateTax']['supply'] > 0){
                        $totalOrderGoodsRateData['delivery'][$key]['rateTax']['supply'] = $realTaxRateSum - $totalOrderGoodsRateData['delivery'][$key]['rateTax']['vat'] - $totalOrderGoodsRateData['delivery'][$key]['rateTax']['free'];
                    }
                }

                $index++;
            }
        }

        unset($tmpTotalOrderGoodsRateData, $index, $totlaOrderSettlePrice, $totlaOrderSettlePriceSum, $totalRate, $totalOrderInfo);

        return $totalOrderGoodsRateData;
    }

    /**
     * checkRefundCompleteAccess
     * 환불 - 접근권한체크
     *
     * @param array $getData
     *
     * @return string $returnMessage
     */
    public function checkRefundCompleteAccess($getData)
    {
        $returnMessage = '';

        // 운영자 기능권한 처리 (주문 상태 권한) - 관리자페이지에서만
        $thisCallController = \App::getInstance('ControllerNameResolver')->getControllerRootDirectory();
        if ($thisCallController == 'admin' && Session::get('manager.functionAuthState') == 'check' && Session::get('manager.functionAuth.orderState') != 'y') {
            $returnMessage = __('권한이 없습니다. 권한은 대표운영자에게 문의하시기 바랍니다.');
        }
        if (isset($getData['orderNo']) === false) {
            $returnMessage = __('정상적인 접근이 아닙니다.');
        }

        // 핸들 번호가 없는 경우 오류
        if ($getData['handleSno'] == 0) {
            $returnMessage = __('환불/교환/반품번호 형식이 맞지 않습니다. 관리자에게 문의하세요.');
        }

        return $returnMessage;
    }

    /**
     * divideRefundMinusSettlePrice
     * 환불 - 금액 재분배시 부가결제금액에 비례하여 안분기준 변경
     *
     * @param float $rate
     * @param integer $tmpRefundSettlePrice
     * @param integer $tmpRefundAddPaymentPrice
     * @param integer $divisionAddPayment
     *
     * @return integer $divisionMinusRealPrice
     */
    public function divideRefundMinusSettlePrice($rate, $tmpRefundSettlePrice, $tmpRefundAddPaymentPrice, $divisionAddPayment)
    {
        $realSettleCutType = 'round';
        $divisionResult = NumberUtils::getNumberFigure(($rate / 100) * $tmpRefundAddPaymentPrice, '0.01', 'round');
        if($divisionAddPayment > $divisionResult){
            $realSettleCutType = 'floor';
        }
        else if($divisionAddPayment < $divisionResult){
            $realSettleCutType = 'ceil';
        }
        else {}

        $divisionMinusRealPrice = NumberUtils::getNumberFigure(($rate / 100) * $tmpRefundSettlePrice, '0.1', $realSettleCutType);

        return $divisionMinusRealPrice;
    }

    /**
     * divideAddPaymentPrice
     * 환불 - 부가결제금액 재분배시 초과현상을 막기위해 안분기준 변경
     *
     * @param float $rate
     * @param integer $deposit
     * @param integer $mileage
     *
     * @return array [$divisionDeposit, $divisionMileage]
     */
    public function divideAddPaymentPrice($rate, $deposit, $mileage)
    {
        $mileageCutType = 'round';

        $divisionDeposit = $divisionMileage = 0;

        $divisionDeposit = NumberUtils::getNumberFigure(($rate / 100) * $deposit, '0.1', 'round');
        if($deposit > 0 && $mileage > 0){
            $tmpDivisionDeposit = NumberUtils::getNumberFigure(($rate / 100) * $deposit, '0.01', 'round');
            if($divisionDeposit > $tmpDivisionDeposit){
                $mileageCutType = 'floor';
            }
            else if($divisionDeposit < $tmpDivisionDeposit){
                $mileageCutType = 'ceil';
            }
            else{}
        }
        $divisionMileage = NumberUtils::getNumberFigure(($rate / 100) * $mileage, '0.1', $mileageCutType);

        return [$divisionDeposit, $divisionMileage];
    }

    /**
     * getRealTaxBalance
     * 환불 - tax 금액이 기준금액을 초과하지 않도록 체크
     *
     * @param integer $standardPrice
     * @param integer $supplyPrice
     * @param integer $vatPrice
     * @param integer $freePrice
     *
     * @return array [$supplyPrice, $vatPrice, $freePrice]
     */
    public function getRealTaxBalance($standardPrice, $supplyPrice, $vatPrice, $freePrice)
    {
        if($standardPrice !== ($supplyPrice+$vatPrice+$freePrice)){
            if($vatPrice > 0){
                $supplyPrice = $standardPrice - $vatPrice - $freePrice;
            }
            else if($freePrice > 0){
                $freePrice = $standardPrice - $supplyPrice - $vatPrice;
            }
            else if($supplyPrice > 0){
                $supplyPrice = $standardPrice - $vatPrice - $freePrice;
            }
        }

        return [$supplyPrice, $vatPrice, $freePrice];
    }

    /**
     * getRealTaxMaxCheck
     * 환불 - 금액 재분배시 초과되는 금액이 없도록 체크
     *
     * @param integer $maxSupply
     * @param integer $maxVat
     * @param integer $maxFree
     * @param integer $changeSupply
     * @param integer $changeVat
     * @param integer $changeFree
     *
     * @return array [$changeSupply, $changeVat, $changeFree]
     */
    public function getRealTaxMaxCheck($maxSupply, $maxVat, $maxFree, $changeSupply, $changeVat, $changeFree)
    {
        if($maxSupply < $changeSupply){
            $changeSupply -= 1;
            if($maxVat >= $maxFree){
                $changeVat += 1;
            }
            else {
                $changeFree += 1;
            }
        }
        if($maxVat < $changeVat){
            $changeVat -= 1;
            if($maxSupply >= $maxFree){
                $changeSupply += 1;
            }
            else {
                $changeFree += 1;
            }
        }
        if($maxFree < $changeFree){
            $changeFree -= 1;
            if($maxSupply >= $maxVat){
                $changeSupply += 1;
            }
            else {
                $changeVat += 1;
            }
        }

        return [$changeSupply, $changeVat, $changeFree];
    }

    /**
     * getRealTaxRate
     * 환불 - 환불시 금액 재분배를 위해 과세/면세 금액에 대한 비율을 지정
     *
     * @param integer $supplyPrice
     * @param integer $vatPrice
     * @param integer $freePrice
     *
     * @return array $realTaxRate
     */
    public function getRealTaxRate($supplyPrice, $vatPrice, $freePrice)
    {
        $realTaxRate = [
            'supply' => 0,
            'vat' => 0,
            'free' => 0,
        ];
        $totalPrice = $supplyPrice + $vatPrice + $freePrice;

        if((float)$totalPrice > 0) {
            $realTaxRate['supply'] = NumberUtils::getNumberFigure(($supplyPrice / $totalPrice) * 100, '0.001', 'round');
            $realTaxRate['vat'] = NumberUtils::getNumberFigure(($vatPrice / $totalPrice) * 100, '0.001', 'round');
            $realTaxRate['free'] = NumberUtils::getNumberFigure(($freePrice / $totalPrice) * 100, '0.001', 'round');

            list($realTaxRate['supply'], $realTaxRate['vat'], $realTaxRate['free']) = $this->getRealTaxBalance(100, $realTaxRate['supply'], $realTaxRate['vat'], $realTaxRate['free']);

            $realTaxRate['supply'] = $realTaxRate['supply'] / 100;
            $realTaxRate['vat'] = $realTaxRate['vat'] / 100;
            $realTaxRate['free'] = $realTaxRate['free'] / 100;
        }

        return $realTaxRate;
    }

    /**
     * refundDivisionCheck
     * 환불 - 환불시 안분한 금액 합계와 원금 차이가 나는지 비교해 맞춰준다
     *
     * @param array $arrayData 원배열
     * @param integer $divisionSum 안분후 안분된금액들 합계
     * @param integer $orgPrice 안분전 원금액
     * @param string $strText 원배열에서 $v에서 수정되어야할 필드명
     * @param string $checkKey 원배열에서 특정 $k값만 수정되어야 하면 해당$k값을 받는다
     *
     * @return array $arrayData
     */
    public function refundDivisionCheck($arrayData, $divisionSum, $orgPrice, $strText, $checkKey = '')
    {
        if (empty($arrayData)) {
            return $arrayData;
            exit;
        }
        if (empty($divisionSum)) $divisionSum = 0;
        if (empty($orgPrice)) $orgPrice = 0;
        if ($divisionSum != $orgPrice) {
            if ($divisionSum > $orgPrice) {
                $tempDivisionVal = $divisionSum - $orgPrice;
                $tempDivisionValType = 'minus';
            } else {
                $tempDivisionVal = $orgPrice - $divisionSum;
                $tempDivisionValType = 'plus';
            }
            $checkDivisionVal = 0;
            do {
                foreach ($arrayData as $k => $v) {
                    if ($checkKey != '') {
                        if (isset($v['orderDeliverySno']) && $checkKey != '' && $checkKey != $v['orderDeliverySno']) {
                            continue;
                        }
                    }

                    if ($tempDivisionVal > 0) {
                        if ($arrayData[$k][$strText] == 0 && $tempDivisionValType == 'minus') {
                            continue;
                        }
                        if ($tempDivisionValType == 'minus') {
                            $arrayData[$k][$strText] -= 1;
                        } else {
                            $arrayData[$k][$strText] += 1;
                        }
                        $tempDivisionVal -= 1;
                        if ($tempDivisionVal == 0) {
                            $checkDivisionVal = 1;
                            break;
                        }
                    }
                }
            } while ($checkDivisionVal == 0);
        }

        return $arrayData;
    }

    /**
     * refundDeliveryDivisionCheck
     * 환불 - 환불시 배송비에 안분된금액이 기존 tax값이상으로 안분되어있는지 확인해
     *
     * @param array $orgTotalData 원 주문 tax값 정보
     * @param array $updateDeliveryData 업데이트 될 배송비 데이터
     * @param array $refundDeliveryData 환불 될 배송비 데이터
     * @param array $getData 환불창 데이터
     *
     * @return array [$updateDeliveryData, $refundDeliveryData]
     */
    public function refundDeliveryDivisionCheck($orgTotalData, $updateDeliveryData, $refundDeliveryData, $getData)
    {
        $tempLeftDeposit = $getData['refundDepositPrice'];
        $tempLeftMileage = $getData['refundMileagePrice'];
        $tempUpdateDeliveryTaxSupplySum = 0;
        $tempUpdateDeliveryTaxVatSum = 0;
        $tempUpdateDeliveryTaxFreeSum = 0;
        $tempRefundDeliveryTaxSupplySum = 0;
        $tempRefundDeliveryTaxVatSum = 0;
        $tempRefundDeliveryTaxFreeSum = 0;
        foreach ($updateDeliveryData as $v) {
            $tempLeftDeposit -= $v['divisionDeliveryUseDeposit'];
            $tempLeftMileage -= $v['divisionDeliveryUseMileage'];
            $tempUpdateDeliveryTaxSupplySum += $v['realTaxSupplyDeliveryCharge'];
            $tempUpdateDeliveryTaxVatSum += $v['realTaxVatDeliveryCharge'];
            $tempUpdateDeliveryTaxFreeSum += $v['realTaxFreeDeliveryCharge'];
        }
        foreach ($refundDeliveryData as $v) {
            $tempLeftDeposit -= $v['divisionDeliveryUseDeposit'];
            $tempLeftMileage -= $v['divisionDeliveryUseMileage'];
            $tempRefundDeliveryTaxSupplySum += $v['realTaxSupplyDeliveryCharge'];
            $tempRefundDeliveryTaxVatSum += $v['realTaxVatDeliveryCharge'];
            $tempRefundDeliveryTaxFreeSum += $v['realTaxFreeDeliveryCharge'];
        }
        $tempTaxSupplySum = $tempUpdateDeliveryTaxSupplySum + $tempRefundDeliveryTaxSupplySum;
        $tempTaxVatSum = $tempUpdateDeliveryTaxVatSum + $tempRefundDeliveryTaxVatSum;
        $tempTaxFreeSum = $tempUpdateDeliveryTaxFreeSum + $tempRefundDeliveryTaxFreeSum;

        $checkDivisionVal = 1;

        do {
            // free supply vat 순으로 체크하면서 오버되면 재분배
            if ($orgTotalData['realTaxFreePrice'] < $tempTaxFreeSum) { // 환불 업데이트 free 합계값이 오버인경우
                // 오버값
                $tempCheckVal = $tempTaxFreeSum - $orgTotalData['realTaxFreePrice'];

                // 환불부터
                // 환불 재분배
                foreach ($refundDeliveryData as $k => $v) {
                    if ($v['realTaxSum'] == 0 || $v['realTaxFreeDeliveryCharge'] == 0) {
                        continue;
                    }
                    $tempMinusVal = 0;
                    if ($tempCheckVal > 0) { // 오버 금액이 있으면 처리
                        if ($refundDeliveryData[$k]['realTaxFreeDeliveryCharge'] > $tempCheckVal) { //오버금액보다 큰경우 처리
                            $refundDeliveryData[$k]['realTaxFreeDeliveryCharge'] -= $tempCheckVal;
                            $refundDeliveryData[$k]['realTaxSupplyDeliveryCharge'] += $tempCheckVal;
                            $tempCheckVal = 0;
                        } elseif ($refundDeliveryData[$k]['realTaxFreeDeliveryCharge'] < $tempCheckVal) { //오버금액 보다 작은경우처리
                            $tempMinusVal = $tempCheckVal - $refundDeliveryData[$k]['realTaxFreeDeliveryCharge'];
                            $refundDeliveryData[$k]['realTaxFreeDeliveryCharge'] -= $tempMinusVal;
                            $refundDeliveryData[$k]['realTaxSupplyDeliveryCharge'] += $tempMinusVal;
                            $tempCheckVal -= $tempMinusVal;
                            $tempMinusVal = 0;
                        }
                    }
                }

                // 오버값이 남아있으면 업데이트도 처리
                if ($tempCheckVal > 0) {
                    // 환불 재분배
                    foreach ($updateDeliveryData as $k => $v) {
                        if ($v['realTaxSum'] == 0 || $v['realTaxFreeDeliveryCharge'] == 0) {
                            continue;
                        }
                        $tempMinusVal = 0;
                        if ($tempCheckVal > 0) { // 오버 금액이 있으면 처리
                            if ($updateDeliveryData[$k]['realTaxFreeDeliveryCharge'] > $tempCheckVal) { //오버금액보다 큰경우 처리
                                $updateDeliveryData[$k]['realTaxFreeDeliveryCharge'] -= $tempCheckVal;
                                $updateDeliveryData[$k]['realTaxSupplyDeliveryCharge'] += $tempCheckVal;
                                $tempCheckVal = 0;
                            } elseif ($updateDeliveryData[$k]['realTaxFreeDeliveryCharge'] < $tempCheckVal) { //오버금액 보다 작은경우처리
                                $tempMinusVal = $tempCheckVal - $updateDeliveryData[$k]['realTaxFreeDeliveryCharge'];
                                $updateDeliveryData[$k]['realTaxFreeDeliveryCharge'] -= $tempMinusVal;
                                $updateDeliveryData[$k]['realTaxSupplyDeliveryCharge'] += $tempMinusVal;
                                $tempCheckVal -= $tempMinusVal;
                                $tempMinusVal = 0;
                            }
                        }
                    }
                }
            } elseif ($orgTotalData['realTaxSupplyPrice'] < $tempTaxSupplySum) { // 환불 업데이트 supply 합계값이 오버인경우
                // 오버값
                $tempCheckVal = $tempTaxSupplySum - $orgTotalData['realTaxSupplyPrice'];

                // 환불부터
                // 환불 재분배
                foreach ($refundDeliveryData as $k => $v) {
                    if ($v['realTaxSum'] == 0 || $v['realTaxSupplyDeliveryCharge'] == 0) {
                        continue;
                    }
                    $tempMinusVal = 0;
                    if ($tempCheckVal > 0) { // 오버 금액이 있으면 처리
                        if ($refundDeliveryData[$k]['realTaxSupplyDeliveryCharge'] > $tempCheckVal) { //오버금액보다 큰경우 처리
                            $refundDeliveryData[$k]['realTaxSupplyDeliveryCharge'] -= $tempCheckVal;
                            $refundDeliveryData[$k]['realTaxVatDeliveryCharge'] += $tempCheckVal;
                            $tempCheckVal = 0;
                        } elseif ($refundDeliveryData[$k]['realTaxSupplyDeliveryCharge'] < $tempCheckVal) { //오버금액 보다 작은경우처리
                            $tempMinusVal = $tempCheckVal - $refundDeliveryData[$k]['realTaxSupplyDeliveryCharge'];
                            $refundDeliveryData[$k]['realTaxSupplyDeliveryCharge'] -= $tempMinusVal;
                            $refundDeliveryData[$k]['realTaxVatDeliveryCharge'] += $tempMinusVal;
                            $tempCheckVal -= $tempMinusVal;
                            $tempMinusVal = 0;
                        }
                    }
                }


                // 오버값이 남아있으면 업데이트도 처리
                if ($tempCheckVal > 0) {
                    // 환불 재분배
                    foreach ($updateDeliveryData as $k => $v) {
                        if ($v['realTaxSum'] == 0 || $v['realTaxSupplyDeliveryCharge'] == 0) {
                            continue;
                        }
                        $tempMinusVal = 0;
                        if ($tempCheckVal > 0) { // 오버 금액이 있으면 처리
                            if ($updateDeliveryData[$k]['realTaxSupplyDeliveryCharge'] > $tempCheckVal) { //오버금액보다 큰경우 처리
                                $updateDeliveryData[$k]['realTaxSupplyDeliveryCharge'] -= $tempCheckVal;
                                $updateDeliveryData[$k]['realTaxVatDeliveryCharge'] += $tempCheckVal;
                                $tempCheckVal = 0;
                            } elseif ($updateDeliveryData[$k]['realTaxSupplyDeliveryCharge'] < $tempCheckVal) { //오버금액 보다 작은경우처리
                                $tempMinusVal = $tempCheckVal - $updateDeliveryData[$k]['realTaxSupplyDeliveryCharge'];
                                $updateDeliveryData[$k]['realTaxSupplyDeliveryCharge'] -= $tempMinusVal;
                                $updateDeliveryData[$k]['realTaxVatDeliveryCharge'] += $tempMinusVal;
                                $tempCheckVal -= $tempMinusVal;
                                $tempMinusVal = 0;
                            }
                        }
                    }
                }
            } elseif ($orgTotalData['realTaxVatPrice'] < $tempTaxVatSum) { // 환불 업데이트 vat 합계값이 오버인경우
                // 오버값
                $tempCheckVal = $tempTaxVatSum - $orgTotalData['realTaxVatPrice'];
                if ($tempLeftDeposit > 0) { // 잔여 예치금이 있으면 처리
                    $flagDeposit = 'T';
                } else {
                    $flagDeposit = 'F';
                }
                if ($tempLeftMileage > 0) {
                    $flagMileage = 'T';
                } else {
                    $flagMileage = 'F';
                }
                // 환불부터
                // 환불 재분배
                foreach ($refundDeliveryData as $k => $v) {
                    if ($v['realTaxSum'] == 0 || $v['realTaxVatDeliveryCharge'] == 0) {
                        continue;
                    }
                    $tempMinusVal = 0;
                    if ($tempCheckVal > 0) { // 오버 금액이 있으면 처리
                        if ($refundDeliveryData[$k]['realTaxVatDeliveryCharge'] > $tempCheckVal) { //오버금액보다 큰경우 처리
                            $refundDeliveryData[$k]['realTaxVatDeliveryCharge'] -= $tempCheckVal;
                            if ($flagDeposit == 'T') {
                                if ($tempLeftDeposit > $tempCheckVal) {
                                    $refundDeliveryData[$k]['divisionDeliveryUseDeposit'] += $tempCheckVal;
                                    $tempCheckVal = 0;
                                    $tempLeftDeposit -= $tempCheckVal;
                                } else {
                                    $refundDeliveryData[$k]['divisionDeliveryUseDeposit'] += $tempLeftDeposit;
                                    $tempCheckVal -= $tempLeftDeposit;
                                    $tempLeftDeposit = 0;
                                }
                            }
                            if ($flagMileage == 'T') {
                                if ($tempCheckVal > 0) { // 잔여금액있으면
                                    if ($tempLeftMileage > $tempCheckVal) {
                                        $refundDeliveryData[$k]['divisionDeliveryUseMileage'] += $tempCheckVal;
                                        $tempCheckVal = 0;
                                        $tempLeftMileage -= $tempCheckVal;
                                    } else {
                                        $refundDeliveryData[$k]['divisionDeliveryUseMileage'] += $tempLeftMileage;
                                        $tempCheckVal -= $tempLeftMileage;
                                        $tempLeftMileage = 0;
                                    }
                                }
                            }
                        } elseif ($refundDeliveryData[$k]['realTaxVatDeliveryCharge'] < $tempCheckVal) { //오버금액 보다 작은경우처리
                            $tempMinusVal = $tempCheckVal - $refundDeliveryData[$k]['realTaxVatDeliveryCharge'];
                            $refundDeliveryData[$k]['realTaxVatDeliveryCharge'] -= $tempMinusVal;
                            if ($flagDeposit == 'T') {
                                if ($tempLeftDeposit > $tempMinusVal) {
                                    $refundDeliveryData[$k]['divisionDeliveryUseDeposit'] += $tempMinusVal;
                                    $tempMinusVal = 0;
                                    $tempLeftDeposit -= $tempMinusVal;
                                } else {
                                    $refundDeliveryData[$k]['divisionDeliveryUseDeposit'] += $tempLeftDeposit;
                                    $tempMinusVal -= $tempLeftDeposit;
                                    $tempLeftDeposit = 0;
                                }
                            }
                            if ($flagMileage == 'T') {
                                if ($tempMinusVal > 0) { // 잔여금액있으면
                                    if ($tempLeftMileage > $tempMinusVal) {
                                        $refundDeliveryData[$k]['divisionDeliveryUseMileage'] += $tempMinusVal;
                                        $tempMinusVal = 0;
                                        $tempLeftMileage -= $tempCheckVal;
                                    } else {
                                        $refundDeliveryData[$k]['divisionDeliveryUseMileage'] += $tempLeftMileage;
                                        $tempMinusVal -= $tempLeftMileage;
                                        $tempLeftMileage = 0;
                                    }
                                }
                            }
                            $tempCheckVal -= $tempMinusVal;
                            $tempMinusVal = 0;
                        }
                    }
                }


                // 오버값이 남아있으면 업데이트도 처리
                if ($tempCheckVal > 0) {
                    // 환불 재분배
                    foreach ($updateDeliveryData as $k => $v) {
                        if ($v['realTaxSum'] == 0 || $v['realTaxVatDeliveryCharge'] == 0) {
                            continue;
                        }
                        $tempMinusVal = 0;
                        if ($tempCheckVal > 0) { // 오버 금액이 있으면 처리
                            if ($updateDeliveryData[$k]['realTaxVatDeliveryCharge'] > $tempCheckVal) { //오버금액보다 큰경우 처리
                                $updateDeliveryData[$k]['realTaxVatDeliveryCharge'] -= $tempCheckVal;
                                if ($flagDeposit == 'T' && $tempLeftDeposit > 0) {
                                    if ($tempLeftDeposit > $tempCheckVal) {
                                        $refundDeliveryData[$k]['divisionDeliveryUseDeposit'] += $tempCheckVal;
                                        $tempCheckVal = 0;
                                        $tempLeftDeposit -= $tempCheckVal;
                                    } else {
                                        $refundDeliveryData[$k]['divisionDeliveryUseDeposit'] += $tempLeftDeposit;
                                        $tempCheckVal -= $tempLeftDeposit;
                                        $tempLeftDeposit = 0;
                                    }
                                }
                                if ($flagMileage == 'T' && $tempLeftMileage > 0) {
                                    if ($tempCheckVal > 0) { // 잔여금액있으면
                                        if ($tempLeftMileage > $tempCheckVal) {
                                            $refundDeliveryData[$k]['divisionDeliveryUseMileage'] += $tempCheckVal;
                                            $tempCheckVal = 0;
                                            $tempLeftMileage -= $tempCheckVal;
                                        } else {
                                            $refundDeliveryData[$k]['divisionDeliveryUseMileage'] += $tempLeftMileage;
                                            $tempCheckVal -= $tempLeftMileage;
                                            $tempLeftMileage = 0;
                                        }
                                    }
                                }
                            } elseif ($updateDeliveryData[$k]['realTaxVatDeliveryCharge'] < $tempCheckVal) { //오버금액 보다 작은경우처리
                                $tempMinusVal = $tempCheckVal - $updateDeliveryData[$k]['realTaxVatDeliveryCharge'];
                                $updateDeliveryData[$k]['realTaxVatDeliveryCharge'] -= $tempMinusVal;
                                if ($flagDeposit == 'T' && $tempLeftDeposit > 0) {
                                    if ($tempLeftDeposit > $tempMinusVal) {
                                        $refundDeliveryData[$k]['divisionDeliveryUseDeposit'] += $tempMinusVal;
                                        $tempMinusVal = 0;
                                        $tempLeftDeposit -= $tempMinusVal;
                                    } else {
                                        $refundDeliveryData[$k]['divisionDeliveryUseDeposit'] += $tempLeftDeposit;
                                        $tempMinusVal -= $tempLeftDeposit;
                                        $tempLeftDeposit = 0;
                                    }
                                }
                                if ($flagMileage == 'T' && $tempLeftMileage > 0) {
                                    if ($tempMinusVal > 0) { // 잔여금액있으면
                                        if ($tempLeftMileage > $tempMinusVal) {
                                            $refundDeliveryData[$k]['divisionDeliveryUseMileage'] += $tempMinusVal;
                                            $tempMinusVal = 0;
                                            $tempLeftMileage -= $tempCheckVal;
                                        } else {
                                            $refundDeliveryData[$k]['divisionDeliveryUseMileage'] += $tempLeftMileage;
                                            $tempMinusVal -= $tempLeftMileage;
                                            $tempLeftMileage = 0;
                                        }
                                    }
                                }
                                $tempCheckVal -= $tempMinusVal;
                                $tempMinusVal = 0;
                            }
                        }
                    }
                }
            }

            // 위에서 재분배해서 합계 값 재계산
            $tempLeftDeposit = $getData['refundDepositPrice'];
            $tempLeftMileage = $getData['refundMileagePrice'];
            $tempUpdateDeliveryTaxSupplySum = 0;
            $tempUpdateDeliveryTaxVatSum = 0;
            $tempUpdateDeliveryTaxFreeSum = 0;
            $tempRefundDeliveryTaxSupplySum = 0;
            $tempRefundDeliveryTaxVatSum = 0;
            $tempRefundDeliveryTaxFreeSum = 0;
            foreach ($updateDeliveryData as $k => $v) {
                $tempLeftDeposit -= $v['divisionDeliveryUseDeposit'];
                $tempLeftMileage -= $v['divisionDeliveryUseMileage'];
                $tempUpdateDeliveryTaxSupplySum += $v['realTaxSupplyDeliveryCharge'];
                $tempUpdateDeliveryTaxVatSum += $v['realTaxVatDeliveryCharge'];
                $tempUpdateDeliveryTaxFreeSum += $v['realTaxFreeDeliveryCharge'];
                $updateDeliveryData[$k]['realTaxSum'] = $v['realTaxSupplyDeliveryCharge'] + $v['realTaxVatDeliveryCharge'] + $v['realTaxFreeDeliveryCharge'];
            }
            foreach ($refundDeliveryData as $k => $v) {
                $tempLeftDeposit -= $v['divisionDeliveryUseDeposit'];
                $tempLeftMileage -= $v['divisionDeliveryUseMileage'];
                $tempRefundDeliveryTaxSupplySum += $v['realTaxSupplyDeliveryCharge'];
                $tempRefundDeliveryTaxVatSum += $v['realTaxVatDeliveryCharge'];
                $tempRefundDeliveryTaxFreeSum += $v['realTaxFreeDeliveryCharge'];
                $refundDeliveryData[$k]['realTaxSum'] = $v['realTaxSupplyDeliveryCharge'] + $v['realTaxVatDeliveryCharge'] + $v['realTaxFreeDeliveryCharge'];
            }
            $tempTaxSupplySum = $tempUpdateDeliveryTaxSupplySum + $tempRefundDeliveryTaxSupplySum;
            $tempTaxVatSum = $tempUpdateDeliveryTaxVatSum + $tempRefundDeliveryTaxVatSum;
            $tempTaxFreeSum = $tempUpdateDeliveryTaxFreeSum + $tempRefundDeliveryTaxFreeSum;

            if ($orgTotalData['realTaxSupplyPrice'] >= $tempTaxSupplySum && $orgTotalData['realTaxVatPrice'] >= $tempTaxVatSum && $orgTotalData['realTaxFreePrice'] >= $tempTaxFreeSum) {
                $checkDivisionVal = 0;
            }

        } while ($checkDivisionVal == 1);

        return [$updateDeliveryData, $refundDeliveryData];
    }

    /**
     * getRefundInfoData
     * 환불할 주문상품을 가공하여 환불처리에 사용
     *
     * @param string $orderNo
     * @param array $orderInfo
     * @param array $getData
     *
     * @return array $orderRefundInfo
     */
    public function getRefundInfoData($orderNo, $orderInfo, $getData)
    {
        if(!is_object($orderAdmin)){
            $orderAdmin = App::load(\Component\Order\OrderAdmin::class);
        }

        $orderRefundInfo = [
            'refundOrderGoodsSnos' => [],
            'orderHandleData' => [],
            'restoreDepositSnos' => [],
            'restoreMileageSnos' => [],
            'giveMileage' => [],
            'totalRefundGiveMileage' => 0,
            'refundOrderDeliverySno' => [],
            'refundDeliverySettlePrice' => [],
        ];

        $onlyOverseasDelivery = false;
        $processEnd = [];
        foreach ($orderInfo['goods'] as $sKey => $sVal) {
            foreach ($sVal as $dKey => $dVal) {
                foreach ($dVal as $gKey => $gVal) {
                    $orderRefundInfo['orderHandleData'][$gVal['handleSno']] = $orderAdmin->getOrderGoods($orderNo, null, $gVal['handleSno']);

                    // 처리 중인 상품SNO 저장 (하단에 회원 구매결정금액 산정시 필요)
                    $orderRefundInfo['refundOrderGoodsSnos'][] = $gVal['sno'];

                    // 사용예치금 복원을 위한 order goods sno 지정
                    if ($gVal['minusDepositFl'] == 'y') {
                        if (($getData['info']['refundGoodsUseDeposit'] + $getData['info']['refundDeliveryUseDeposit']) > 0) {
                            $orderRefundInfo['restoreDepositSnos'][] = $gVal['sno'];
                        }
                    }

                    // 사용마일리지 복원을 위한 order goods sno 지정
                    if ($gVal['minusMileageFl'] == 'y') {
                        if (($getData['info']['refundGoodsUseMileage'] + $getData['info']['refundDeliveryUseMileage']) > 0) {
                            $orderRefundInfo['restoreMileageSnos'][] = $gVal['sno'];
                        }
                    }

                    // 적립마일리지가 있는 경우 환원 및 환불테이블에 들어갈 값 정의
                    if ($gVal['plusMileageFl'] == 'y') {
                        if ($getData['refund'][$gVal['handleSno']]['refundGiveMileage'] > 0) {
                            $orderRefundInfo['totalRefundGiveMileage'] += $getData['refund'][$gVal['handleSno']]['refundGiveMileage'];
                            $orderRefundInfo['giveMileage'][] = $gVal['sno'];
                        }
                    }

                    // 환불 할 배송비 (첫번째 상품만 배송비 환불금액 입력하면 됨) : 환불할 배송비가 있을경우 작동
                    if (!in_array($gVal['orderDeliverySno'], $processEnd)) {
                        // 해외배송의 경우 공급사가 다르더라도 배송비조건 일련번호가 같기 때문에 0원으로 오버라이딩 되는 증상이 있다.
                        // 해외상점 주문시 최초 배송비가 들어간 이후에는 처리되지 않도록 강제로 예외처리 (해당 처리 안하면 배송비가 해외상점인 경우 무조건 0원으로 나옴)
                        if ($orderInfo['mallSno'] == DEFAULT_MALL_NUMBER || ($orderInfo['mallSno'] > DEFAULT_MALL_NUMBER && $onlyOverseasDelivery === false)) {
                            $orderRefundInfo['refundOrderDeliverySno'][] = $gVal['orderDeliverySno'];
                            $orderRefundInfo['refundDeliverySettlePrice'][$gVal['orderDeliverySno']] = floatval($getData['refund'][$gVal['handleSno']]['refundDeliveryCharge']);

                            // 해외배송비
                            $onlyOverseasDelivery = true;

                            $processEnd[] = $gVal['orderDeliverySno'];
                        }
                    }
                }
            }
        }

        return $orderRefundInfo;
    }

    /**
     * restoreRefundCoupon
     * 환불 - 쿠폰 복원
     *
     * @param array $getData
     *
     * @return void
     */
    public function restoreRefundCoupon($getData)
    {
        if (is_array($getData['returnCoupon'])) {
            foreach ($getData['returnCoupon'] as $key => $val) {
                if ($val == 'y') {
                    $getData['tmp']['memberCouponNo'][] = $key;
                }
            }
        }

        if (empty($getData['tmp']['memberCouponNo']) === false) {
            $coupon = App::load(\Component\Coupon\Coupon::class);
            foreach ($getData['tmp']['memberCouponNo'] as $memberCouponNo) {
                // 쿠폰 복원 처리
                $coupon->setMemberCouponState($memberCouponNo, 'y');

                // 할인쿠폰 테이블 복원 여부 변경
                $orderData['minusCouponFl'] = 'y';
                $orderData['minusRestoreCouponFl'] = 'y';
                $compareField = array_keys($orderData);
                $arrBind = $this->db->get_binding(DBTableField::tableOrderCoupon(), $orderData, 'update', $compareField);
                $this->db->bind_param_push($arrBind['bind'], 'i', $getData['orderNo']);
                $this->db->bind_param_push($arrBind['bind'], 'i', $memberCouponNo);
                $this->db->set_update_db(DB_ORDER_COUPON, $arrBind['param'], 'orderNo = ? AND memberCouponNo = ? AND minusCouponFl = \'y\'', $arrBind['bind']);
                unset($arrBind, $orderData);

                // 적립쿠폰 테이블 복원 여부 변경
                $orderData['plusCouponFl'] = 'y';
                $orderData['plusRestoreCouponFl'] = 'y';
                $compareField = array_keys($orderData);
                $arrBind = $this->db->get_binding(DBTableField::tableOrderCoupon(), $orderData, 'update', $compareField);
                $this->db->bind_param_push($arrBind['bind'], 'i', $getData['orderNo']);
                $this->db->bind_param_push($arrBind['bind'], 'i', $memberCouponNo);
                $this->db->set_update_db(DB_ORDER_COUPON, $arrBind['param'], 'orderNo = ? AND memberCouponNo = ? AND plusCouponFl = \'y\'', $arrBind['bind']);
                unset($arrBind, $orderData);
            }
        }
    }

    /**
     * restoreRefundUseDeposit
     * 환불 - 사용된 예치금 복원
     *
     * @param array $getData
     * @param array $orderInfo
     * @param array $orderRefundInfo
     *
     * @return void
     */
    public function restoreRefundUseDeposit($getData, $orderInfo, $orderRefundInfo)
    {
        // 사용된 예치금 복원 금액 = 상품 예치금 환불금액(상품에 안분된) + 배송비 예치금 환불금액(배송비에 안분된) - 예치금 부가결제 수수료
        $totalRefundUseDeposit = gd_isset($getData['info']['refundGoodsUseDeposit'], 0) + gd_isset($getData['info']['refundDeliveryUseDeposit'], 0) - gd_isset($getData['info']['refundUseDepositCommission'], 0);
        if ($totalRefundUseDeposit > 0) {
            /** @var \Bundle\Component\Deposit\Deposit $deposit */
            $deposit = App::load(\Component\Deposit\Deposit::class);
            if ($deposit->setMemberDeposit($orderInfo['memNo'], $totalRefundUseDeposit, Deposit::REASON_CODE_GROUP . Deposit::REASON_CODE_ORDER_CANCEL, 'o', $orderInfo['orderNo'])) {
                $orderGoodsData['minusDepositFl'] = 'n';
                $orderGoodsData['minusRestoreDepositFl'] = 'y';

                // DB 업데이트
                $compareField = array_keys($orderGoodsData);
                $arrBind = $this->db->get_binding(DBTableField::tableOrderGoods(), $orderGoodsData, 'update', $compareField);
                $this->db->set_update_db(DB_ORDER_GOODS, $arrBind['param'], 'sno IN (\'' . implode('\',\'', $orderRefundInfo['restoreDepositSnos']) . '\')', $arrBind['bind']);
                unset($arrBind, $orderGoodsData);
            }
        }
    }

    /**
     * restoreRefundUseMileage
     * 환불 - 사용된 마일리지 복원
     *
     * @param array $getData
     * @param array $orderInfo
     * @param array $orderRefundInfo
     *
     * @return void
     */
    public function restoreRefundUseMileage($getData, $orderInfo, $orderRefundInfo)
    {
        // 사용된 마일리지 복원 금액 = 상품 마일리지 환불금액(상품에 안분된) + 배송비 마일리지 환불금액(배송비에 안분된) - 마일리지 부가결제 수수료
        $totalRefundUseMileage = gd_isset($getData['info']['refundGoodsUseMileage'], 0) + gd_isset($getData['info']['refundDeliveryUseMileage'], 0) - gd_isset($getData['info']['refundUseMileageCommission'], 0);
        if ($totalRefundUseMileage > 0) {
            /** @var \Bundle\Component\Mileage\Mileage $mileage */
            $mileage = App::load(\Component\Mileage\Mileage::class);
            $mileage->setIsTran(false);
            if ($mileage->setMemberMileage($orderInfo['memNo'], $totalRefundUseMileage, Mileage::REASON_CODE_GROUP . Mileage::REASON_CODE_USE_GOODS_BUY_RESTORE, 'o', $orderInfo['orderNo'])) {
                $orderGoodsData['minusMileageFl'] = 'n';
                $orderGoodsData['minusRestoreMileageFl'] = 'y';

                // DB 업데이트
                $compareField = array_keys($orderGoodsData);
                $arrBind = $this->db->get_binding(DBTableField::tableOrderGoods(), $orderGoodsData, 'update', $compareField);
                $this->db->set_update_db(DB_ORDER_GOODS, $arrBind['param'], 'sno IN (\'' . implode('\',\'', $orderRefundInfo['restoreMileageSnos']) . '\')', $arrBind['bind']);
                unset($arrBind, $orderGoodsData);
            }
        }
    }

    /**
     * minusRefundGiveMileage
     * 환불 - 적립마일리지 일괄 차감
     *
     * @param array $getData
     * @param array $orderInfo
     * @param array $orderRefundInfo
     *
     * @return void
     */
    public function minusRefundGiveMileage($getData ,$orderInfo, $orderRefundInfo)
    {
        // 마일리지 차감 방법
        $member = App::load(\Component\Member\Member::class);
        $memInfo = $member->getMemberId($orderInfo['memNo']);

        // 회원일 경우에만 회원정보 수집
        if(!empty($memInfo['memId'])) {
            $memData = gd_htmlspecialchars($member->getMember($memInfo['memId'], 'memId'));
        }

        // 적립마일리지가 회원보유 마일리지보다 큰 경우 적립마일리지와 보유마일리지의 차액 산출하고 하단에서 차액만 별도로 처리 한다.
        $minusRefundGiveMileage = 0;
        if ($getData['tmp']['refundMinusMileage'] == 'n' && $memData['mileage'] < $orderRefundInfo['totalRefundGiveMileage']) {
            $minusRefundGiveMileage = $orderRefundInfo['totalRefundGiveMileage'] - $memData['mileage'];
            $orderRefundInfo['totalRefundGiveMileage'] = $memData['mileage'];
        }

        // 적립마일리지 일괄 차감
        if ($orderRefundInfo['totalRefundGiveMileage'] > 0) {
            /** @var \Bundle\Component\Mileage\Mileage $mileage */
            $mileage = App::load(\Component\Mileage\Mileage::class);
            $mileage->setIsTran(false);
            if ($mileage->setMemberMileage($orderInfo['memNo'], ($orderRefundInfo['totalRefundGiveMileage'] * -1), Mileage::REASON_CODE_GROUP . Mileage::REASON_CODE_ADD_GOODS_BUY_RESTORE, 'o', $orderInfo['orderNo'])) {
                $orderGoodsData['plusMileageFl'] = 'n';
                $orderGoodsData['plusRestoreMileageFl'] = 'y';

                // DB 업데이트
                $compareField = array_keys($orderGoodsData);
                $arrBind = $this->db->get_binding(DBTableField::tableOrderGoods(), $orderGoodsData, 'update', $compareField);
                $this->db->set_update_db(DB_ORDER_GOODS, $arrBind['param'], 'sno IN (\'' . implode('\',\'', $orderRefundInfo['giveMileage']) . '\')', $arrBind['bind']);
                unset($arrBind, $orderGoodsData);
            }
        }

        // 보유마일리지보다 차감 마일리지가 큰 경우 별도 계산된 차액을 따로 처리한다. (별도의 마일리지로 회원쪽에 들어간다)
        if ($minusRefundGiveMileage > 0) {
            $mileage->setIsTran(false);
            $mileage->setMemberMileage($orderInfo['memNo'], ($minusRefundGiveMileage * -1), Mileage::REASON_CODE_GROUP . Mileage::REASON_CODE_ADD_GOODS_BUY_RESTORE, 'o', $orderInfo['orderNo']);
        }
    }

    /**
     * updateRefundMemberPrice
     * 환불 - 회원정보에서 구매정보 제거
     *
     * @param array $getData
     * @param array $orderInfo
     * @param array $orderRefundInfo
     *
     * @return void
     */
    public function updateRefundMemberPrice($getData, $orderInfo, $orderRefundInfo)
    {
        if(!is_object($orderAdmin)){
            $orderAdmin = App::load(\Component\Order\OrderAdmin::class);
        }

        // 회원정보 구매정보에서 환불내역 제거하는 업데이트
        foreach ($orderRefundInfo['refundOrderGoodsSnos'] as $sno) {
            // 구매확정이 한번이라도 됬어다면
            if ($orderAdmin->isSetOrderStatus($orderInfo['orderNo'], $sno) !== false) {
                $memberUpdateWhereByRefund = 'memNo = \'' . $this->db->escape($orderInfo['memNo']) . '\' AND saleCnt > 0';
                $this->db->set_update_db_query(DB_MEMBER, 'saleAmt = saleAmt - ' . gd_money_format($getData['check']['totalRefundPrice'], false) . ', saleCnt = saleCnt - ' . $orderInfo['cnt']['goods']['goods'], $memberUpdateWhereByRefund);
                break;
            }
        }
    }

    /**
     * completeRefundPrice
     * 환불 - 현금성 환불에 대한 처리
     *
     * @param array $getData
     * @param array $orderInfo
     * @param array $refundRealTaxData
     *
     * @return string
     */
    public function completeRefundPrice($getData, $orderInfo, $refundRealTaxData)
    {
        try {
            // 현금환불 처리
            if ($getData['info']['completeCashPrice'] > 0) {
                $completeCashPrice = gd_money_format($getData['info']['completeCashPrice'], false);
            }

            // 예치금환불 처리
            if ($getData['info']['completeDepositPrice'] !== 0) {
                $completeDepositPrice = gd_money_format($getData['info']['completeDepositPrice'], false);
                /** @var \Bundle\Component\Deposit\Deposit $deposit */
                $deposit = App::load(\Component\Deposit\Deposit::class);
                $deposit->setMemberDeposit($orderInfo['memNo'], $completeDepositPrice, Deposit::REASON_CODE_GROUP . Deposit::REASON_CODE_DEPOSIT_REFUND, 'o', $orderInfo['orderNo'], null, null,$getData['handleSno']);
            }

            // 마일리지(기타) 처리
            if ($getData['info']['completeMileagePrice'] > 0) {
                // 2016-10-10 “여신전문금융업법” 적용으로 현금성이 아닌 마일리지로 환불 시 법령 위반되어 기타환불의 경우 환불완료 처리 시 "현금환불"과 동일하게 자동으로 환불처리가 되지 않도록 변경
                // 페이코일때 상태변경 모듈 실행
                if ($orderInfo['pgName'] == 'payco' && (in_array($orderInfo['settleKind'], ['fv']) === true && $orderInfo['paycoData']['refundUseYn'] != 'Y') && count($getData['refund']) == $orderInfo['orderGoodsCnt']) {
                    $pgConf = gd_pgs('payco');
                    $pgCancel = \App::load('\\Component\\Payment\\Payco\\PgCancel');
                    $pgCancel->setCancelStatus($pgConf, $getData, $orderInfo);
                }
            }

            // 전액할인일 경우 return
            if($orderInfo['mallSno'] == DEFAULT_MALL_NUMBER && in_array($orderInfo['settleKind'], ['gz']) === true) {
                return '';
            }

            // pg환불금액이 0일 경우 환불 불가
            if ($getData['info']['refundMethod'] == 'PG환불' && $getData['info']['completePgPrice'] <= 0) {
                throw new Exception('PG환불 금액이 0인 경우 PG환불이 불가합니다.');
            }

            // PG 취소 처리
            if ($getData['info']['completePgPrice'] > 0) {
                // 주문 데이터
                $tmp = [];
                $tmp['orderNo'] = $orderInfo['orderNo'];
                $tmp['handleSno'] = $getData['handleSno'];
                $tmp['orderName'] = $orderInfo['orderName'];
                $tmp['orderPhone'] = $orderInfo['orderPhone'];
                $tmp['orderCellPhone'] = $orderInfo['orderCellPhone'];
                $tmp['orderEmail'] = $orderInfo['orderEmail'];
                $tmp['settleKind'] = $orderInfo['settleKind'];
                $tmp['pgTid'] = $orderInfo['pgTid'];
                $tmp['pgAppNo'] = $orderInfo['pgAppNo'];
                $tmp['pgAppDt'] = $orderInfo['pgAppDt'];
                $tmp['pgResultCode'] = $orderInfo['pgResultCode'];

                // 주문유형
                $tmp['orderTypeFl'] = $orderInfo['orderTypeFl'];

                // 초기 주문 금액
                $tmp['settlePrice'] = gd_money_format($orderInfo['settlePrice'], false);
                $tmp['taxSupplyPrice'] = gd_money_format($orderInfo['taxSupplyPrice'], false);
                $tmp['taxVatPrice'] = gd_money_format($orderInfo['taxVatPrice'], false);
                $tmp['taxFreePrice'] = gd_money_format($orderInfo['taxFreePrice'], false);

                // PG 취소 금액
                $tmp['cancelPrice'] = gd_money_format($getData['info']['completePgPrice'], false);

                // 환불 금액 (PG 취소 금액 이외의 다른 금액 포함)
                $realTaxPgRate = $this->getRealTaxRate($refundRealTaxData['realTaxSupplyPrice'], $refundRealTaxData['realTaxVatPrice'], $refundRealTaxData['realTaxFreePrice']);

                $realTaxRefundPgSupplyPrice = NumberUtils::getNumberFigure($getData['info']['completePgPrice']*$realTaxPgRate['supply'], '0.1', 'round');
                $realTaxRefundPgVatPrice = NumberUtils::getNumberFigure($getData['info']['completePgPrice']*$realTaxPgRate['vat'], '0.1', 'round');
                $realTaxRefundPgFreePrice = NumberUtils::getNumberFigure($getData['info']['completePgPrice']*$realTaxPgRate['free'], '0.1', 'round');

                list($realTaxRefundPgSupplyPrice, $realTaxRefundPgVatPrice, $realTaxRefundPgFreePrice) = $this->getRealTaxBalance($getData['info']['completePgPrice'], $realTaxRefundPgSupplyPrice, $realTaxRefundPgVatPrice, $realTaxRefundPgFreePrice);


                $tmp['refundPrice'] = gd_money_format($getData['info']['completePgPrice'], false);
                $tmp['refundTaxSupplyPrice'] = gd_money_format($realTaxRefundPgSupplyPrice, false);
                $tmp['refundTaxVatPrice'] = gd_money_format($realTaxRefundPgVatPrice, false);
                $tmp['refundTaxFreePrice'] = gd_money_format($realTaxRefundPgFreePrice, false);

                // 해외PG의 경우 해외PG금액도 셋팅
                if ($orderInfo['mallSno'] > DEFAULT_MALL_NUMBER || $orderInfo['overseasSettlePrice'] > 0) {
                    $tmp['overseasSettlePrice'] = $orderInfo['overseasSettlePrice'];
                }
                $tmp['deliveryInsuranceFee'] = gd_isset($getData['info']['refundDeliveryInsuranceFee'], 0);

                // 간편결제 관련 데이터
                $tmp['pgName'] = $orderInfo['pgName'];
                $tmp['checkoutData'] = $orderInfo['checkoutData'];

                // 에스크로 관련 데이터
                $tmp['escrowDenyFl'] = $orderInfo['escrowDenyFl'];
                $tmp['escrowConfirmFl'] = $orderInfo['escrowConfirmFl'];
                $tmp['escrowDeliveryFl'] = $orderInfo['escrowDeliveryFl'];

                // PG 취소 모듈 실행
                if ($orderInfo['pgName'] == 'payco' && (in_array($orderInfo['settleKind'], ['fv']) === true && $orderInfo['paycoData']['refundUseYn'] != 'Y') && count($getData['refund']) == $orderInfo['orderGoodsCnt']) {
                    $pgConf = gd_pgs('payco');
                    $pgCancel = \App::load('\\Component\\Payment\\Payco\\PgCancel');
                    $result = $pgCancel->setCancelStatus($pgConf, $getData, $orderInfo);
                } else {
                    $pgCancel = \App::load('\\Component\\Payment\\Cancel');
                    $result = $pgCancel->sendPgCancel($getData, $tmp, 'refund');
                }

                // 실패시 롤백 처리
                if ($result !== true) {
                    throw new Exception(__('PG 취소 진행시 오류가 발생이되어, 취소에 실패 하였습니다.') . ' [' . $result . ']');
                }
                else {
                    $arrBind = [];

                    // @yby PG 금액 업데이트
                    if(($orderInfo['pgRealTaxSupplyPrice']+$orderInfo['pgRealTaxVatPrice']+$orderInfo['pgRealTaxFreePrice']) > 1){
                        $orderUpdateData = [
                            'pgRealTaxSupplyPrice' => $orderInfo['pgRealTaxSupplyPrice'] - $realTaxRefundPgSupplyPrice,
                            'pgRealTaxVatPrice' => $orderInfo['pgRealTaxVatPrice'] - $realTaxRefundPgVatPrice,
                            'pgRealTaxFreePrice' => $orderInfo['pgRealTaxFreePrice'] - $realTaxRefundPgFreePrice,
                        ];
                        $arrBind = $this->db->get_binding(DBTableField::tableOrder(), $orderUpdateData, 'update', array_keys($orderUpdateData));
                        $arrWhere = 'orderNo = ?';
                        $this->db->bind_param_push($arrBind['bind'], 's', $orderInfo['orderNo']);
                        $result = $this->db->set_update_db(DB_ORDER, $arrBind['param'], $arrWhere, $arrBind['bind']);
                        if(!$result){
                            throw new Exception(__('환불을 실패하였습니다.[주문서 정보 저장 실패]'));
                        }
                        unset($arrBind, $compareField, $orderUpdateData);
                    }
                }
                unset($tmp);
            }

            return '';
        }
        catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * 입금 전 부분 취소 시 취소되는 상품을 제외하고 남은 상품으로만 주문 시 정책과 할인을 그대로 적용하여 금액 재계산
     *
     * @param array $cancel 취소 key (orderNo, orderGoods)
     * @param array $cancelMsg 취소 메세지 (orderStatus, handleReason, handleDetailReason)
     * @param array $cancelPrice 취소 금액
     * @param array $cancelReturn 주문 시 처리된 것의 복원 (stock, coupon, gift)
     * @param boolean $restoreCouponFl 쿠폰 복원 여부
     *
     * @throws
     *
     * @return boolean true
     */
    public function setCancelOrderGoods($cancel, $cancelMsg, $cancelPrice, $cancelReturn, $restoreCouponFl = true)
    {
        // 취소 key에 따른 기본 취소 금액
        $cancelData = $this->getSelectOrderGoodsCancelData($cancel['orderNo'], $cancel['orderGoods']);

        // sms 개선 부분 취소 시 취소금액 전달을 위한 변수 설정(취소 팝업의 총 취소 금액과 일치하도록)
        $claimPrice['cancelPrice'] = $cancel['cancelPriceBySmsSend'];

        $updateCancelOrderData = [];// 최종 처리된 주문 정보(수정)
        $updateCancelGoodsData = [];// 최종 처리된 주문 상품 정보(수정)
        $updateCancelDeliveryData = [];// 최종 처리된 주문 배송비 정보(수정)
        $insertCancelGoodsData = [];// 최종 처리된 주문 상품 정보(등록)

        // 관리자 주문취소 - 유저모드 마이페이지 주문취소시의 중복 부가결제금액 환불을 방지하기 위함.
        $beforeMinusRestoreMileageFl = true;
        $beforeMinusRestoreDepositFl = true;

        // 주문 정보
        $originOrderData = $this->getOrderData($cancel['orderNo']);
        // 주문상품 정보
        $originOrderGoodsData = $this->getOrderGoodsData($cancel['orderNo']);
        // 주문 배송비 정보
        $originOrderDeliveryData = $this->getOrderDeliveryData($cancel['orderNo']);

        // 주문할인을 안분하기 위해 orderNo 로 현재 주문 상품 정보를 가져옴
        // 주문 상품 정보에서 취소 상품 수량 제거하여 상품 금액 산출
        $goodsTax = [];// 과세/면세
        $goodsPrice = [];// 남은 상품 별 금액 계산
        $goodsDcPrice = [];// 상품 별 할인 합
        $goodsDelivery = [];// 주문배송비 조건마다 주문상품
        $goodsDeliveryPrice = [];// 상품별 같은 배송비마다 상품금액 합
        $iTotalCancelGoodsCount = count($cancel['orderGoods']);
        $iCancelCount = 0;
        $iDivisionCouponOrderDcPrice = 0;
        foreach ($originOrderGoodsData as $key => $val) {
            $singleOrderStatus = substr($val['orderStatus'], 0, 1);
            if (!in_array($singleOrderStatus, ['c', 'e', 'r', 'f'])) {
                if (array_key_exists($val['sno'], $cancel['orderGoods'])) {
                    if($val['minusMileageFl'] === 'y' && $val['minusRestoreMileageFl'] === 'n'){
                        $beforeMinusRestoreMileageFl = false;
                    }
                    if($val['minusDepositFl'] === 'y' && $val['minusRestoreDepositFl'] === 'n'){
                        $beforeMinusRestoreDepositFl = false;
                    }
                    // 주문 상품의 할인 금액 취소
                    $updateCancelGoodsData[$val['sno']]['goodsDcPrice'] = $val['goodsDcPrice'] - gd_isset($cancelData['cancelGoodsDcPrice'][$val['sno']], 0);
                    $updateCancelGoodsData[$val['sno']]['enuri'] = $val['enuri'] - gd_isset($cancelData['cancelGoodsEnuriPrice'][$val['sno']], 0);
                    $updateCancelGoodsData[$val['sno']]['memberDcPrice'] = $val['memberDcPrice'] - gd_isset($cancelData['cancelMemberDcPrice'][$val['sno']], 0);
                    $updateCancelGoodsData[$val['sno']]['memberOverlapDcPrice'] = $val['memberOverlapDcPrice'] - gd_isset($cancelData['cancelMemberOverlapDcPrice'][$val['sno']], 0);
                    $updateCancelGoodsData[$val['sno']]['couponGoodsDcPrice'] = $val['couponGoodsDcPrice'] - gd_isset($cancelData['cancelGoodsCouponDcPrice'][$val['sno']], 0);
                    if ($this->myappUseFl) {
                        $updateCancelGoodsData[$val['sno']]['myappDcPrice'] = $val['myappDcPrice'] - gd_isset($cancelData['cancelMyappDcPrice'][$val['sno']], 0);
                    }

                    // 마일리지 적립은 무조건 취소 갯수 만큼 나눠서 뺌
                    $updateCancelGoodsData[$val['sno']]['goodsMileage'] = $val['goodsMileage'] - gd_number_figure($val['goodsMileage'] * ($cancel['orderGoods'][$val['sno']] / $val['goodsCnt']), $this->mileageTruncPolicy['unitPrecision'], $this->mileageTruncPolicy['unitRound']);
                    $updateCancelGoodsData[$val['sno']]['memberMileage'] = $val['memberMileage'] - gd_number_figure($val['memberMileage'] * ($cancel['orderGoods'][$val['sno']] / $val['goodsCnt']), $this->mileageTruncPolicy['unitPrecision'], $this->mileageTruncPolicy['unitRound']);
                    $updateCancelGoodsData[$val['sno']]['couponGoodsMileage'] = $val['couponGoodsMileage'] - gd_number_figure($val['couponGoodsMileage'] * ($cancel['orderGoods'][$val['sno']] / $val['goodsCnt']), $this->mileageTruncPolicy['unitPrecision'], $this->mileageTruncPolicy['unitRound']);
                    $updateCancelGoodsData[$val['sno']]['divisionCouponOrderMileage'] = $val['divisionCouponOrderMileage'] - gd_number_figure($val['divisionCouponOrderMileage'] * ($cancel['orderGoods'][$val['sno']] / $val['goodsCnt']), $this->mileageTruncPolicy['unitPrecision'], $this->mileageTruncPolicy['unitRound']);

                    // 취소 후 남은 수량
                    $goodsCnt = $val['goodsCnt'] - $cancel['orderGoods'][$val['sno']];
                    // 주문 취소 상품 row 생성
                    if ($goodsCnt == 0) {// 남은 수량이 0 이면 주문 상품 상태를 취소로 변경
                        $updateCancelGoodsData[$val['sno']]['orderStatus'] = $cancelMsg['orderStatus'];
                        $updateCancelGoodsData[$val['sno']]['goodsCnt'] = $cancel['orderGoods'][$val['sno']];
                        // 입금대기 상태에서 취소 시 환불배송비는 0원으로 예외처리
                        $updateCancelGoodsData[$val['sno']]['handleCancelDeliveryPrice'] = ($singleOrderStatus == 'o') ? 0 : ($cancelPrice['deliveryCancel'][$val['orderDeliverySno']] + $cancelPrice['areaDeliveryCancel'][$val['orderDeliverySno']] + $cancelPrice['overseaDeliveryCancel'][$val['orderDeliverySno']]);
                        $updateCancelGoodsData[$val['sno']]['handleCancelDeposit'] = ($iCancelCount == 0) ? $cancelPrice['useDepositCancel'] : 0;
                        $updateCancelGoodsData[$val['sno']]['handleCancelMileage'] = ($iCancelCount == 0) ? $cancelPrice['useMileageCancel'] : 0;
                        $updateCancelGoodsData[$val['sno']]['divisionUseDeposit'] = $cancelPrice['useDepositCancel'];
                        $updateCancelGoodsData[$val['sno']]['divisionUseMileage'] = $cancelPrice['useMileageCancel'];
                        $updateCancelGoodsData[$val['sno']]['divisionCouponOrderDcPrice'] = ($iCancelCount == 0) ? gd_isset($cancelPrice['orderCouponDcCancel'], 0 ) : 0;
                        $updateCancelGoodsData[$val['sno']]['goodsDcPrice'] = gd_isset($cancelData['cancelGoodsDcPrice'][$val['sno']], 0);
                        $updateCancelGoodsData[$val['sno']]['enuri'] = gd_isset($cancelData['cancelGoodsEnuriPrice'][$val['sno']], 0);
                        $updateCancelGoodsData[$val['sno']]['memberDcPrice'] = gd_isset($cancelData['cancelMemberDcPrice'][$val['sno']], 0);
                        $updateCancelGoodsData[$val['sno']]['memberOverlapDcPrice'] = gd_isset($cancelData['cancelMemberOverlapDcPrice'][$val['sno']], 0);
                        $updateCancelGoodsData[$val['sno']]['couponGoodsDcPrice'] = gd_isset($cancelData['cancelGoodsCouponDcPrice'][$val['sno']], 0);
                        if ($this->myappUseFl) {
                            $updateCancelGoodsData[$val['sno']]['myappDcPrice'] = gd_isset($cancelData['cancelMyappDcPrice'][$val['sno']], 0);
                        }

                        /*
                         * 모든 상품의 전체수량 취소시 할인, 적립금액이 업데이트 되지 않는 현상이 있어 0의 값을 더함
                         */

                        // 남은 상품할인 금액
                        $updateCancelOrderData['totalGoodsDcPrice'] += 0;
                        // 남은 운영자할인 금액
                        $updateCancelOrderData['totalEnuriDcPrice'] += 0;
                        // 남은 회원추가할인 금액
                        $updateCancelOrderData['totalMemberDcPrice'] += 0;
                        // 남은 회원중복할인 금액
                        $updateCancelOrderData['totalMemberOverlapDcPrice'] += 0;
                        // 남은 상품쿠폰할인 금액
                        $updateCancelOrderData['totalCouponGoodsDcPrice'] += 0;
                        // 남은 마이앱할인 금액
                        if ($this->myappUseFl) {
                            $updateCancelOrderData['totalMyappDcPrice'] += 0;
                        }
                        // 남은 상품적립 마일리지 금액
                        $updateCancelOrderData['totalGoodsMileage'] += 0;
                        // 남은 회원적립 마일리지 금액
                        $updateCancelOrderData['totalMemberMileage'] += 0;
                        // 남은 상품쿠폰적립 마일리지 금액
                        $updateCancelOrderData['totalCouponGoodsMileage'] += 0;
                        // 남은 주문쿠폰적립 마일리지 금액
                        $updateCancelOrderData['totalCouponOrderMileage'] += 0;

                    } else {// 남은 수량이 0 보다 크면 취소 수량만 주문 상품에 추가하여 취소로 변경
                        $insertCancelGoodsData[$val['sno']]['goodsCnt'] = $cancel['orderGoods'][$val['sno']];
                        $insertCancelGoodsData[$val['sno']]['goodsNo'] = $val['goodsNo'];
                        $insertCancelGoodsData[$val['sno']]['goodsType'] = $val['goodsType'];
                        $insertCancelGoodsData[$val['sno']]['orderStatus'] = $cancelMsg['orderStatus'];
                        $insertCancelGoodsData[$val['sno']]['orderDeliverySno'] = $val['orderDeliverySno'];
                        $insertCancelGoodsData[$val['sno']]['goodsDcPrice'] = gd_isset($cancelData['cancelGoodsDcPrice'][$val['sno']], 0);
                        $insertCancelGoodsData[$val['sno']]['enuri'] = gd_isset($cancelData['cancelGoodsEnuriPrice'][$val['sno']], 0);
                        $insertCancelGoodsData[$val['sno']]['memberDcPrice'] = gd_isset($cancelData['cancelMemberDcPrice'][$val['sno']], 0);
                        $insertCancelGoodsData[$val['sno']]['memberOverlapDcPrice'] = gd_isset($cancelData['cancelMemberOverlapDcPrice'][$val['sno']], 0);
                        $insertCancelGoodsData[$val['sno']]['couponGoodsDcPrice'] = gd_isset($cancelData['cancelGoodsCouponDcPrice'][$val['sno']], 0);
                        if ($this->myappUseFl) {
                            $insertCancelGoodsData[$val['sno']]['myappDcPrice'] = gd_isset($cancelData['cancelMyappDcPrice'][$val['sno']], 0);
                        }
                        $insertCancelGoodsData[$val['sno']]['timeSalePrice'] = 0;
                        $insertCancelGoodsData[$val['sno']]['divisionUseDeposit'] = $cancelPrice['useDepositCancel'];
                        $insertCancelGoodsData[$val['sno']]['divisionUseMileage'] = $cancelPrice['useMileageCancel'];
                        $insertCancelGoodsData[$val['sno']]['divisionCouponOrderDcPrice'] = ($iCancelCount == 0) ? gd_isset($cancelPrice['orderCouponDcCancel'], 0 ) : 0;
                        $insertCancelGoodsData[$val['sno']]['handleCancelDeliveryPrice'] = $cancelPrice['deliveryCancel'][$val['orderDeliverySno']] + $cancelPrice['areaDeliveryCancel'][$val['orderDeliverySno']] + $cancelPrice['overseaDeliveryCancel'][$val['orderDeliverySno']];
                        $insertCancelGoodsData[$val['sno']]['handleCancelDeposit'] = ($iCancelCount == 0) ? gd_isset($cancelPrice['useDepositCancel'], 0 ) : 0;
                        $insertCancelGoodsData[$val['sno']]['handleCancelMileage'] = ($iCancelCount == 0) ? gd_isset($cancelPrice['useMileageCancel'], 0 ) : 0;
                        $insertCancelGoodsData[$val['sno']]['realTaxSupplyGoodsPrice'] = $insertCancelGoodsData[$val['sno']]['realTaxVatGoodsPrice'] = $insertCancelGoodsData[$val['sno']]['realTaxFreeGoodsPrice'] = 0;
                        $updateCancelGoodsData[$val['sno']]['goodsCnt'] = $goodsCnt;

                        // 남은 상품 별 할인 합
                        $goodsDcPrice[$val['sno']] = $updateCancelGoodsData[$val['sno']]['goodsDcPrice'] + $updateCancelGoodsData[$val['sno']]['enuri'] + $updateCancelGoodsData[$val['sno']]['memberDcPrice'] + $updateCancelGoodsData[$val['sno']]['memberOverlapDcPrice'] + $updateCancelGoodsData[$val['sno']]['couponGoodsDcPrice'];
                        if ($this->myappUseFl) {
                            $goodsDcPrice[$val['sno']] += $updateCancelGoodsData[$val['sno']]['myappDcPrice'];
                        }

                        // 과세/면세
                        $goodsTax[$val['sno']] = explode(STR_DIVISION, $val['goodsTaxInfo']);
                        // 남은 상품 별 금액 계산
                        $goodsPrice[$val['sno']] = ($val['goodsPrice'] + $val['optionPrice'] + $val['optionTextPrice']) * $goodsCnt;
                        // 남은 상품할인 금액
                        $updateCancelOrderData['totalGoodsDcPrice'] += $updateCancelGoodsData[$val['sno']]['goodsDcPrice'];
                        // 남은 운영자할인 금액
                        $updateCancelOrderData['totalEnuriDcPrice'] += $updateCancelGoodsData[$val['sno']]['enuri'];
                        // 남은 회원추가할인 금액
                        $updateCancelOrderData['totalMemberDcPrice'] += $updateCancelGoodsData[$val['sno']]['memberDcPrice'];
                        // 남은 회원중복할인 금액
                        $updateCancelOrderData['totalMemberOverlapDcPrice'] += $updateCancelGoodsData[$val['sno']]['memberOverlapDcPrice'];
                        // 남은 상품쿠폰할인 금액
                        $updateCancelOrderData['totalCouponGoodsDcPrice'] += $updateCancelGoodsData[$val['sno']]['couponGoodsDcPrice'];
                        // 남은 마이앱할인 금액
                        if ($this->myappUseFl) {
                            $updateCancelOrderData['totalMyappDcPrice'] += $updateCancelGoodsData[$val['sno']]['myappDcPrice'];
                        }
                        // 남은 상품적립 마일리지 금액
                        $updateCancelOrderData['totalGoodsMileage'] += $updateCancelGoodsData[$val['sno']]['goodsMileage'];
                        // 남은 회원적립 마일리지 금액
                        $updateCancelOrderData['totalMemberMileage'] += $updateCancelGoodsData[$val['sno']]['memberMileage'];
                        // 남은 상품쿠폰적립 마일리지 금액
                        $updateCancelOrderData['totalCouponGoodsMileage'] += $updateCancelGoodsData[$val['sno']]['couponGoodsMileage'];
                        // 남은 주문쿠폰적립 마일리지 금액
                        $updateCancelOrderData['totalCouponOrderMileage'] += $updateCancelGoodsData[$val['sno']]['divisionCouponOrderMileage'];
                    }

                    $iCancelCount++;
                    // todo 취소시도 취소금액에대한 취소 상세정보들 안분처리 필요 현재는 임의로 첫품목에 몰아넣고있음

                } else {
                    // 과세/면세
                    $goodsTax[$val['sno']] = explode(STR_DIVISION, $val['goodsTaxInfo']);
                    // 남은 상품 별 할인 합
                    $goodsDcPrice[$val['sno']] = $val['goodsDcPrice'] + $val['enuri'] + $val['memberDcPrice'] + $val['memberOverlapDcPrice'] + $val['couponGoodsDcPrice'];
                    if ($this->myappUseFl) {
                        $goodsDcPrice[$val['sno']] += $val['myappDcPrice'];
                    }

                    // 남은 상품 별 금액 계산
                    $goodsPrice[$val['sno']] = ($val['goodsPrice'] + $val['optionPrice'] + $val['optionTextPrice']) * $val['goodsCnt'];
                    // 남은 상품할인 금액
                    $updateCancelOrderData['totalGoodsDcPrice'] += $val['goodsDcPrice'];
                    // 남은 운영자할인 금액
                    $updateCancelOrderData['totalEnuriDcPrice'] += $val['enuri'];
                    // 남은 회원추가할인 금액
                    $updateCancelOrderData['totalMemberDcPrice'] += $val['memberDcPrice'];
                    // 남은 회원중복할인 금액
                    $updateCancelOrderData['totalMemberOverlapDcPrice'] += $val['memberOverlapDcPrice'];
                    // 남은 상품쿠폰할인 금액
                    $updateCancelOrderData['totalCouponGoodsDcPrice'] += $val['couponGoodsDcPrice'];
                    // 남은 마이앱할인 금액
                    if ($this->myappUseFl) {
                        $updateCancelOrderData['totalMyappDcPrice'] += $val['myappDcPrice'];
                    }
                    // 남은 상품적립 마일리지 금액
                    $updateCancelOrderData['totalGoodsMileage'] += $val['goodsMileage'];
                    // 남은 회원적립 마일리지 금액
                    $updateCancelOrderData['totalMemberMileage'] += $val['memberMileage'];
                    // 남은 상품쿠폰적립 마일리지 금액
                    $updateCancelOrderData['totalCouponGoodsMileage'] += $val['couponGoodsMileage'];
                    // 남은 주문쿠폰적립 마일리지 금액
                    $updateCancelOrderData['totalCouponOrderMileage'] += $val['divisionCouponOrderMileage'];
                }
                // 남은 상품 총 금액
                $updateCancelOrderData['totalGoodsPrice'] += (int)$goodsPrice[$val['sno']];

                // 주문배송비 조건마다 주문상품
                $goodsDelivery[$val['orderDeliverySno']][] = $val['sno'];
                // 상품별 같은 배송비마다 상품금액 합
                $goodsDeliveryPrice[$val['orderDeliverySno']] += (int)$goodsPrice[$val['sno']];
            }
        }

        // 취소 배송비 적용하여 배송비 금액 산출
        $deliveryTax = [];// 과세/면세
        $deliveryPrice = [];// 남은 배송비 별 금액
        $deliveryDcPrice = [];// 배송비 조건별 총 할인 금액
        $totalDeliveryPrice = 0;
        $totalDeliveryInsuranceFeePrice = 0;

        //debug($originOrderDeliveryData);
        foreach ($originOrderDeliveryData as $key => $val) {
//            if ($val['deliveryCollectFl'] == 'pre') { // 선불만 처리
            if (array_key_exists($val['sno'], $cancelData['totalCancelDeliveryPrice'])) { // 취소 상품의 배송비 조건 만
                $updateCancelDeliveryData[$val['sno']]['deliveryPolicyCharge'] = $val['deliveryPolicyCharge'] - gd_isset($cancelPrice['deliveryCancel'][$val['sno']], 0);
                $updateCancelDeliveryData[$val['sno']]['deliveryAreaCharge'] = $val['deliveryAreaCharge'] - gd_isset($cancelPrice['areaDeliveryCancel'][$val['sno']], 0);
                $updateCancelDeliveryData[$val['sno']]['deliveryInsuranceFee'] = $val['deliveryInsuranceFee'] - gd_isset($cancelPrice['overseaDeliveryCancel'][$val['sno']], 0);
                $updateCancelDeliveryData[$val['sno']]['deliveryPolicyCharge'] = $updateCancelDeliveryData[$val['sno']]['deliveryPolicyCharge'] + gd_isset($cancelPrice['addDelivery'][$val['sno']], 0);

                // 배송비 결제 금액
                $updateCancelDeliveryData[$val['sno']]['deliveryCharge'] = $updateCancelDeliveryData[$val['sno']]['deliveryPolicyCharge'] + $updateCancelDeliveryData[$val['sno']]['deliveryAreaCharge'] + $updateCancelDeliveryData[$val['sno']]['deliveryInsuranceFee'];

                // 남은 배송비 별 금액
                $deliveryPrice[$val['sno']] = $updateCancelDeliveryData[$val['sno']]['deliveryCharge'];
                // 남은 배송비 총 금액
                $totalDeliveryPrice += $updateCancelDeliveryData[$val['sno']]['deliveryCharge'];
                // 남은 해외배송비 수수료 총 금액
                $totalDeliveryInsuranceFeePrice += $updateCancelDeliveryData[$val['sno']]['deliveryInsuranceFee'];
            } else {
                // 남은 배송비 별 금액
                $deliveryPrice[$val['sno']] = $val['deliveryCharge'];
                // 남은 배송비 총 금액
                $totalDeliveryPrice += $val['deliveryCharge'];
                // 남은 해외배송비 수수료 총 금액
                $totalDeliveryInsuranceFeePrice += $val['deliveryInsuranceFee'];
            }
//            }
            $deliveryTax[$val['sno']] = explode(STR_DIVISION, $val['deliveryTaxInfo']);
        }
        // 주문의 총 적립 마일리지금액
        $updateCancelOrderData['totalMileage'] = $updateCancelOrderData['totalGoodsMileage'] + $updateCancelOrderData['totalMemberMileage'] + $updateCancelOrderData['totalCouponGoodsMileage'] + $updateCancelOrderData['totalCouponOrderMileage'];
        // 주문의 총 배송비 금액
        $updateCancelOrderData['totalDeliveryCharge'] = $totalDeliveryPrice;

        // 주문의 총 해외배송비수수료 금액
        $updateCancelOrderData['totalDeliveryInsuranceFee'] = $totalDeliveryInsuranceFeePrice;

        // 취소 후 주문의 총 주문 쿠폰 할인 적용 금액
        $updateCancelOrderData['totalCouponOrderDcPrice'] = $originOrderData['totalCouponOrderDcPrice'] - gd_isset($cancelPrice['orderCouponDcCancel'], 0);

        // 주문 쿠폰 할인 안분 - 안분은 제일 마지막은 전체에서 남은거 뺀 값을 저장
        $countGoods = count($goodsPrice);
        $i = 1;
        $sumGoods = 0;
        foreach ($goodsPrice as $orderGoodsNo => $price) {
            if ($i == $countGoods) {
                $updateCancelGoodsData[$orderGoodsNo]['divisionCouponOrderDcPrice'] = $updateCancelOrderData['totalCouponOrderDcPrice'] - $sumGoods;
            } else {
                $updateCancelGoodsData[$orderGoodsNo]['divisionCouponOrderDcPrice'] = gd_number_figure($updateCancelOrderData['totalCouponOrderDcPrice'] * ($price / $updateCancelOrderData['totalGoodsPrice']), $this->couponTruncPolicy['unitPrecision'], $this->couponTruncPolicy['unitRound']);
                $sumGoods += $updateCancelGoodsData[$orderGoodsNo]['divisionCouponOrderDcPrice'];
            }
            $goodsDcPrice[$orderGoodsNo] += $updateCancelGoodsData[$orderGoodsNo]['divisionCouponOrderDcPrice'];
            $i++;
        }

        // 취소 후 주문의 총 배송비 쿠폰 적용 금액
        $updateCancelOrderData['totalCouponDeliveryDcPrice'] = $originOrderData['totalCouponDeliveryDcPrice'] - gd_isset($cancelPrice['deliveryCouponDcCancel'], 0);

        // 배송비 쿠폰 안분 - 안분은 제일 마지막은 전체에서 남은거 뺀 값을 저장
        $countDelivery = count($deliveryPrice);
        $i = 1;
        $sumDelivery = 0;
        foreach ($deliveryPrice as $deliverySno => $price) {
            if ($i == $countDelivery) {
                $updateCancelDeliveryData[$deliverySno]['divisionDeliveryCharge'] = $updateCancelOrderData['totalCouponDeliveryDcPrice'] - $sumDelivery;
            } else {
                $updateCancelDeliveryData[$deliverySno]['divisionDeliveryCharge'] = gd_number_figure($updateCancelOrderData['totalCouponDeliveryDcPrice'] * ($price / $totalDeliveryPrice), $this->couponTruncPolicy['unitPrecision'], $this->couponTruncPolicy['unitRound']);
                $sumDelivery += $updateCancelDeliveryData[$deliverySno]['divisionDeliveryCharge'];
            }
            $deliveryDcPrice[$deliverySno] += $updateCancelDeliveryData[$deliverySno]['divisionDeliveryCharge'];
            $i++;
        }

        // 회원 배송비 무료금액을 취소 할 경우
        $updateCancelOrderData['totalMemberDeliveryDcPrice'] = $originOrderData['totalMemberDeliveryDcPrice'];
        if(count($originOrderDeliveryData) > 0){
            foreach($originOrderDeliveryData as $key => $value){
                // 회원배송비 무료 취소시
                if (array_key_exists($value['sno'], $cancelData['totalCancelDeliveryPrice']) && $cancelPrice['deliveryMemberDcCancelFl'] === 'a') {
                    // 업데이트될 주문배송테이블의 회원배송비무료 금액 조정
                    $updateCancelDeliveryData[$value['sno']]['divisionMemberDeliveryDcPrice'] = 0;
                    // 업데이트될 주문테이블의 회원배송비무료 금액 조정
                    $updateCancelOrderData['totalMemberDeliveryDcPrice'] -= $value['divisionMemberDeliveryDcPrice'];
                }
                else {
                    // 배송비의 tax / realtax 계산시 DC 금액에 포함
                    $deliveryDcPrice[$value['sno']] += $value['divisionMemberDeliveryDcPrice'];
                }
            }
        }

        // 취소 후 주문의 총 예치금 적용 금액
        $updateCancelOrderData['useDeposit'] = $originOrderData['useDeposit'] - gd_isset($cancelPrice['useDepositCancel'], 0);
        $updateCancelOrderData['useMileage'] = $originOrderData['useMileage'] - gd_isset($cancelPrice['useMileageCancel'], 0);

        // 상품결제금액 (상품가 - 상품할인 - 에누리 - 회원(추가/중복)할인 - 상품쿠폰할인 - 주문쿠폰할인 - 마이앱할인)
        $totalGoodsSalePrice = array_sum($goodsPrice) - array_sum($goodsDcPrice);

        // 예치금이 상품결제금액보다 작을 때는 상품에 예치금 모두 적용
        $goodsMinusDeposit = $totalGoodsSalePrice - $updateCancelOrderData['useDeposit'];
        if ($goodsMinusDeposit > 0) {
            $countGoods = count($goodsPrice);
            $i = 1;
            $sumGoods = 0;
            foreach ($goodsPrice as $orderGoodsNo => $price) {
                $salePrice = $price - $goodsDcPrice[$orderGoodsNo];
                if ($i == $countGoods) {// 예치금 결제 안분 - 안분은 제일 마지막은 전체에서 남은거 뺀 값을 저장
                    $updateCancelGoodsData[$orderGoodsNo]['divisionUseDeposit'] = $updateCancelOrderData['useDeposit'] - $sumGoods;
                } else {
                    $updateCancelGoodsData[$orderGoodsNo]['divisionUseDeposit'] = gd_number_figure($updateCancelOrderData['useDeposit'] * ($salePrice / $totalGoodsSalePrice), $this->truncPolicy['unitPrecision'], $this->truncPolicy['unitRound']);
                    $sumGoods += $updateCancelGoodsData[$orderGoodsNo]['divisionUseDeposit'];
                }
                $goodsDcPrice[$orderGoodsNo] += $updateCancelGoodsData[$orderGoodsNo]['divisionUseDeposit'];
                $i++;
            }
        } else {
            $totalDivisionDeposit = $updateCancelOrderData['useDeposit'] + $goodsMinusDeposit;
            $divisionDeposit = 0;
            foreach ($goodsPrice as $orderGoodsNo => $price) {
                $salePrice = $price - $goodsDcPrice[$orderGoodsNo];
                $updateCancelGoodsData[$orderGoodsNo]['divisionUseDeposit'] = $salePrice;
                $goodsDcPrice[$orderGoodsNo] += $updateCancelGoodsData[$orderGoodsNo]['divisionUseDeposit'];
                $divisionDeposit += $salePrice;
            }
            if ($totalDivisionDeposit != $divisionDeposit) {
                throw new Exception(__('예치금 안분 처리 중 오류가 발생하였습니다.'));
            }
        }
        // 상품에 적용하고 남은 예치금 배송비에 안분 - 안분은 제일 마지막은 전체에서 남은거 뺀 값을 저장
        $countDelivery = count($deliveryPrice);
        $i = 1;
        $sumDelivery = 0;
        foreach ($deliveryPrice as $deliverySno => $price) {
            $thisDeliveryPrice = $price - $deliveryDcPrice[$deliverySno];
            $thisTotalDeliveryPrice = $totalDeliveryPrice - $deliveryDcPrice[$deliverySno];

            if ($goodsMinusDeposit < 0) {
                $totalDivisionDeposit = abs($goodsMinusDeposit);
                if ($i == $countDelivery) {// 마일리지 결제 안분 - 안분은 제일 마지막은 전체에서 남은거 뺀 값을 저장
                    $updateCancelDeliveryData[$deliverySno]['divisionDeliveryUseDeposit'] = $totalDivisionDeposit - $sumDelivery;
                } else {
                    $updateCancelDeliveryData[$deliverySno]['divisionDeliveryUseDeposit'] = gd_number_figure($totalDivisionDeposit * ($thisDeliveryPrice / $thisTotalDeliveryPrice), $this->truncPolicy['unitPrecision'], $this->truncPolicy['unitRound']);
                    $sumDelivery += $updateCancelDeliveryData[$deliverySno]['divisionDeliveryUseDeposit'];
                }
                $i++;
            } else {
                $updateCancelDeliveryData[$deliverySno]['divisionDeliveryUseDeposit'] = 0;
            }
            $deliveryDcPrice[$deliverySno] += $updateCancelDeliveryData[$deliverySno]['divisionDeliveryUseDeposit'];
        }

        // 상품결제금액 (상품가 - 상품할인 - 에누리 - 회원(추가/중복)할인 - 상품쿠폰할인 - 주문쿠폰할인 - 예치금 - 마이앱할인)
        $totalGoodsSalePrice = array_sum($goodsPrice) - array_sum($goodsDcPrice);
        if ($totalGoodsSalePrice < 0) {
            $totalGoodsSalePrice = 0;
        }

        // 마일리지 상품결제금액보다 작을 때는 상품에 마일리지 모두 적용
        $goodsMinusMileage = $totalGoodsSalePrice - $updateCancelOrderData['useMileage'];
        if ($goodsMinusMileage > 0) {
            $countGoods = count($goodsPrice);
            $i = 1;
            $sumGoods = 0;
            foreach ($goodsPrice as $orderGoodsNo => $price) {
                $salePrice = $price - $goodsDcPrice[$orderGoodsNo];
                if ($i == $countGoods) {// 마일리지 결제 안분 - 안분은 제일 마지막은 전체에서 남은거 뺀 값을 저장
                    $updateCancelGoodsData[$orderGoodsNo]['divisionUseMileage'] = $updateCancelOrderData['useMileage'] - $sumGoods;
                } else {
                    $updateCancelGoodsData[$orderGoodsNo]['divisionUseMileage'] = gd_number_figure($updateCancelOrderData['useMileage'] * ($salePrice / $totalGoodsSalePrice), $this->mileageTruncPolicy['unitPrecision'], $this->mileageTruncPolicy['unitRound']);
                    $sumGoods += $updateCancelGoodsData[$orderGoodsNo]['divisionUseMileage'];
                }
                $goodsDcPrice[$orderGoodsNo] += $updateCancelGoodsData[$orderGoodsNo]['divisionUseMileage'];
                $i++;
            }
        } else {
            $totalDivisionMileage = $updateCancelOrderData['useMileage'] + $goodsMinusMileage;
            $divisionMileage = 0;
            foreach ($goodsPrice as $orderGoodsNo => $price) {
                $salePrice = $price - $goodsDcPrice[$orderGoodsNo];
                $updateCancelGoodsData[$orderGoodsNo]['divisionUseMileage'] = $salePrice;
                $goodsDcPrice[$orderGoodsNo] += $updateCancelGoodsData[$orderGoodsNo]['divisionUseMileage'];
                $divisionMileage += $salePrice;
            }
            if ($totalDivisionMileage != $divisionMileage) {
                throw new Exception(__('마일리지 안분 처리 중 오류가 발생하였습니다.'));
            }
        }

        // 상품에 적용하고 남은 마일리지 배송비에 안분 - 안분은 제일 마지막은 전체에서 남은거 뺀 값을 저장
        $countDelivery = count($deliveryPrice);
        $i = 1;
        $sumDelivery = 0;
        foreach ($deliveryPrice as $deliverySno => $price) {
            $thisDeliveryPrice = $price - $deliveryDcPrice[$deliverySno];
            $thisTotalDeliveryPrice = $totalDeliveryPrice - $deliveryDcPrice[$deliverySno];

            if ($goodsMinusMileage < 0) {
                $totalDivisionMileage = abs($goodsMinusMileage);
                if ($i == $countDelivery) {// 마일리지 결제 안분 - 안분은 제일 마지막은 전체에서 남은거 뺀 값을 저장
                    $updateCancelDeliveryData[$deliverySno]['divisionDeliveryUseMileage'] = $totalDivisionMileage - $sumDelivery;
                } else {
                    $updateCancelDeliveryData[$deliverySno]['divisionDeliveryUseMileage'] = gd_number_figure($totalDivisionMileage * ($thisDeliveryPrice / $thisTotalDeliveryPrice), $this->mileageTruncPolicy['unitPrecision'], $this->mileageTruncPolicy['unitRound']);
                    $sumDelivery += $updateCancelDeliveryData[$deliverySno]['divisionDeliveryUseMileage'];
                }
                $i++;
            } else {
                $updateCancelDeliveryData[$deliverySno]['divisionDeliveryUseMileage'] = 0;
            }
            $deliveryDcPrice[$deliverySno] += $updateCancelDeliveryData[$deliverySno]['divisionDeliveryUseMileage'];
        }

        // 배송비에 안분된 예치금 / 마일리지를 해당 배송비 조건의 상품별 안분
        foreach ($goodsDelivery as $orderDeliveryNo => $key) {
            $countDeliveryByGoods = count($goodsDelivery[$orderDeliveryNo]);
            $i = 1;
            $sumDeposit = 0;
            $sumMileage = 0;
            foreach ($key as $orderGoodsNo) {
                if ($updateCancelDeliveryData[$orderDeliveryNo]['divisionDeliveryUseDeposit'] > 0) {
                    if ($i == $countDeliveryByGoods) {
                        $updateCancelGoodsData[$orderGoodsNo]['divisionGoodsDeliveryUseDeposit'] = $updateCancelDeliveryData[$orderDeliveryNo]['divisionDeliveryUseDeposit'] - $sumDeposit;
                    } else {
                        $updateCancelGoodsData[$orderGoodsNo]['divisionGoodsDeliveryUseDeposit'] = gd_number_figure($updateCancelDeliveryData[$orderDeliveryNo]['divisionDeliveryUseDeposit'] * ($goodsPrice[$orderGoodsNo] / $goodsDeliveryPrice[$orderDeliveryNo]), $this->truncPolicy['unitPrecision'], $this->truncPolicy['unitRound']);

                        $sumDeposit += $updateCancelGoodsData[$orderGoodsNo]['divisionGoodsDeliveryUseDeposit'];
                    }
                } else {
                    $updateCancelGoodsData[$orderGoodsNo]['divisionGoodsDeliveryUseDeposit'] = 0;
                }

                if ($updateCancelDeliveryData[$orderDeliveryNo]['divisionDeliveryUseMileage'] > 0) {
                    if ($i == $countDeliveryByGoods) {
                        $updateCancelGoodsData[$orderGoodsNo]['divisionGoodsDeliveryUseMileage'] = $updateCancelDeliveryData[$orderDeliveryNo]['divisionDeliveryUseMileage'] - $sumMileage;
                    } else {
                        $updateCancelGoodsData[$orderGoodsNo]['divisionGoodsDeliveryUseMileage'] = gd_number_figure($updateCancelDeliveryData[$orderDeliveryNo]['divisionDeliveryUseMileage'] * ($goodsPrice[$orderGoodsNo] / $goodsDeliveryPrice[$orderDeliveryNo]), $this->mileageTruncPolicy['unitPrecision'], $this->mileageTruncPolicy['unitRound']);

                        $sumMileage += $updateCancelGoodsData[$orderGoodsNo]['divisionGoodsDeliveryUseMileage'];
                    }
                } else {
                    $updateCancelGoodsData[$orderGoodsNo]['divisionGoodsDeliveryUseMileage'] = 0;
                }
                $i++;
            }
        }

        // 주문 tax / realTax 값 변경
        $updateCancelOrderData['taxSupplyPrice'] = 0;
        $updateCancelOrderData['taxVatPrice'] = 0;
        $updateCancelOrderData['taxFreePrice'] = 0;
        $updateCancelOrderData['realTaxSupplyPrice'] = 0;
        $updateCancelOrderData['realTaxVatPrice'] = 0;
        $updateCancelOrderData['realTaxFreePrice'] = 0;

        // 상품 tax / realTax 값 변경
        foreach ($goodsPrice as $orderGoodsNo => $price) {
            $goodsSalePrice = $price - $goodsDcPrice[$orderGoodsNo];
            // 상품 결제가 체크 (상품금액에서 상품할인을 뺀 금액이 0보다 작을 수 없음)
            if ($goodsSalePrice < 0) {
                throw new Exception(__('총 결제 예정금액이 0보다 작은 경우 취소처리가 불가합니다. <br/><br/>결제금액 정보 수정 후 다시 시도해 주세요.(상품)'));
            }
            $goodsTaxData = NumberUtils::taxAll($goodsSalePrice, $goodsTax[$orderGoodsNo][1], $goodsTax[$orderGoodsNo][0]);
            if ($goodsTax[$orderGoodsNo][0] == 't') {
                $updateCancelGoodsData[$orderGoodsNo]['taxSupplyGoodsPrice'] = $updateCancelGoodsData[$orderGoodsNo]['realTaxSupplyGoodsPrice'] = gd_isset($goodsTaxData['supply'], 0);
                $updateCancelGoodsData[$orderGoodsNo]['taxVatGoodsPrice'] = $updateCancelGoodsData[$orderGoodsNo]['realTaxVatGoodsPrice'] = gd_isset($goodsTaxData['tax'], 0);

                // 주문
                $updateCancelOrderData['taxSupplyPrice'] += $updateCancelGoodsData[$orderGoodsNo]['taxSupplyGoodsPrice'];
                $updateCancelOrderData['realTaxSupplyPrice'] += $updateCancelGoodsData[$orderGoodsNo]['taxSupplyGoodsPrice'];
                $updateCancelOrderData['taxVatPrice'] += $updateCancelGoodsData[$orderGoodsNo]['taxVatGoodsPrice'];
                $updateCancelOrderData['realTaxVatPrice'] += $updateCancelGoodsData[$orderGoodsNo]['taxVatGoodsPrice'];
            } else {
                $updateCancelGoodsData[$orderGoodsNo]['taxFreeGoodsPrice'] = $updateCancelGoodsData[$orderGoodsNo]['realTaxFreeGoodsPrice'] = gd_isset($goodsTaxData['supply'], 0);

                // 주문
                $updateCancelOrderData['taxFreePrice'] += $updateCancelGoodsData[$orderGoodsNo]['taxFreeGoodsPrice'];
                $updateCancelOrderData['realTaxFreePrice'] += $updateCancelGoodsData[$orderGoodsNo]['taxFreeGoodsPrice'];
            }
        }

        // 배송비 tax / realTax 값 변경
        foreach ($deliveryPrice as $orderDeliveryNo => $price) {
            $deliverySalePrice = $price - $deliveryDcPrice[$orderDeliveryNo];
            // 배송비 결제가 체크 (배송비금액에서 배송비할인을 뺀 금액이 0보다 작을 수 없음)
            if ($deliverySalePrice < 0) {
                throw new Exception(__('총 결제 예정금액이 0보다 작은 경우 취소처리가 불가합니다. <br/><br/>결제금액 정보 수정 후 다시 시도해 주세요.(배송비)'));
            }
            $deliveryTaxData = NumberUtils::taxAll($deliverySalePrice, $deliveryTax[$orderDeliveryNo][1], $deliveryTax[$orderDeliveryNo][0]);
            if ($deliveryTax[$orderDeliveryNo][0] == 't') {
                $updateCancelDeliveryData[$orderDeliveryNo]['taxSupplyDeliveryCharge'] = $updateCancelDeliveryData[$orderDeliveryNo]['realTaxSupplyDeliveryCharge'] = gd_isset($deliveryTaxData['supply'], 0);
                $updateCancelDeliveryData[$orderDeliveryNo]['taxVatDeliveryCharge'] = $updateCancelDeliveryData[$orderDeliveryNo]['realTaxVatDeliveryCharge'] = gd_isset($deliveryTaxData['tax'], 0);

                // 주문
                $updateCancelOrderData['taxSupplyPrice'] += $updateCancelDeliveryData[$orderDeliveryNo]['taxSupplyDeliveryCharge'];
                $updateCancelOrderData['realTaxSupplyPrice'] += $updateCancelDeliveryData[$orderDeliveryNo]['taxSupplyDeliveryCharge'];
                $updateCancelOrderData['taxVatPrice'] += $updateCancelDeliveryData[$orderDeliveryNo]['taxVatDeliveryCharge'];
                $updateCancelOrderData['realTaxVatPrice'] += $updateCancelDeliveryData[$orderDeliveryNo]['taxVatDeliveryCharge'];
            } else {
                $updateCancelDeliveryData[$orderDeliveryNo]['taxFreeDeliveryCharge'] = $updateCancelDeliveryData[$orderDeliveryNo]['realTaxFreeDeliveryCharge'] = gd_isset($deliveryTaxData['supply'], 0);

                // 주문
                $updateCancelOrderData['taxFreePrice'] += $updateCancelDeliveryData[$orderDeliveryNo]['taxFreeDeliveryCharge'];
                $updateCancelOrderData['realTaxFreePrice'] += $updateCancelDeliveryData[$orderDeliveryNo]['taxFreeDeliveryCharge'];
            }
        }

        // 남은 상품결제금액 = 상품금액 - (상품할인 + 에누리 + 회원추가할인 + 회원중복할인 + 상품쿠폰할인 + 주문쿠폰할인 + 마이앱할인 + 예치금 + 마일리지)
        $settleGoodsPrice = $updateCancelOrderData['totalGoodsPrice'] - ($updateCancelOrderData['totalGoodsDcPrice'] + $updateCancelOrderData['totalEnuriDcPrice'] + $updateCancelOrderData['totalMemberDcPrice'] + $updateCancelOrderData['totalMemberOverlapDcPrice'] + $updateCancelOrderData['totalCouponGoodsDcPrice'] + $updateCancelOrderData['totalCouponOrderDcPrice'] + $updateCancelOrderData['useDeposit'] + $updateCancelOrderData['useMileage']);
        if ($this->myappUseFl) {
            $settleGoodsPrice -= $updateCancelOrderData['totalMyappDcPrice'];
        }
        // 남은 배송비결제금액 = (배송비금액(기본+지역별) + 해외보험료) - (배송비쿠폰 + 회원배송비무료)
        $settleDeliveryPrice = ($updateCancelOrderData['totalDeliveryCharge'] + $updateCancelOrderData['totalDeliveryInsuranceFee']) - ($updateCancelOrderData['totalCouponDeliveryDcPrice'] + $updateCancelOrderData['totalMemberDeliveryDcPrice']);
        // 남은 결제 금액
        $cancelSettlePrice = $settleGoodsPrice + $settleDeliveryPrice;
        // 결제 금액
        $updateCancelOrderData['settlePrice'] = $cancelSettlePrice;
        // 결제 금액 검사
        if ($cancelPrice['settle'] != $cancelSettlePrice) {
            throw new Exception(__('결제금액이 맞지 않습니다.'));
        }

        // 원본정보 origin 저장하기
        $claimStatus = 'c';
        $count = $this->getOrderOriginalCount($cancel['orderNo'], $claimStatus);
        if ($count < 1) {
            //이전 취소건 존재하지 않을 시 현재 주문정보 백업
            $return = $this->setBackupOrderOriginalData($cancel['orderNo'], $claimStatus, true, true);
        }

        // 주문 update
        $updateCancelOrderData['settlePrice'] = $cancelSettlePrice;

        $taxPrice = $updateCancelOrderData['taxSupplyPrice'] + $updateCancelOrderData['taxVatPrice'] + $updateCancelOrderData['taxFreePrice'];
        $realTaxPrice = $updateCancelOrderData['realTaxSupplyPrice'] + $updateCancelOrderData['realTaxVatPrice'] + $updateCancelOrderData['realTaxFreePrice'];

        if ($updateCancelOrderData['settlePrice'] != $taxPrice) {
            throw new Exception(__('결제금액이 맞지 않습니다.(세금)'));
        }
        if ($updateCancelOrderData['settlePrice'] != $realTaxPrice) {
            throw new Exception(__('결제금액이 맞지 않습니다.(r세금)'));
        }

        // 현금영수증 저장 설정 - 세금계산서는 신청시 금액 안들어감 - 발행시 들어감
        if ($originOrderData['receiptFl'] == 'r') {
            // 주문서 저장 설정
            $receipt['settlePrice'] = $taxPrice;
            $receipt['supplyPrice'] = $updateCancelOrderData['taxSupplyPrice'];
            $receipt['taxPrice'] = $updateCancelOrderData['taxVatPrice'];
            $receipt['freePrice'] = $updateCancelOrderData['taxFreePrice'];

            // TODO: 현금영수증이 발급요청이고 + 주문상태가 입금대기상태의 주문이 있을경우 발급금액 자동 변경 by sueun
            // 해당 주문건의 현금영수증 발행상태 확인(발행요쳥)_전체취소가 아닌경우에만
            if($updateCancelOrderData['settlePrice'] != '0') {
                $orderCashReceiptData = $this->getOrderCashReceiptData($cancel['orderNo']);
            }


            // 현금영수증 저장
            if (empty($orderCashReceiptData) === false) {
                /*$compareField = array_keys($receipt);
                $arrBind = $this->db->get_binding(DBTableField::tableOrderCashReceipt(), $receipt, 'update', $compareField);
                $arrWhere = 'orderNo = ?';
                $this->db->bind_param_push($arrBind['bind'], 's', $cancel['orderNo']);
                $this->db->set_update_db(DB_ORDER_CASH_RECEIPT, $arrBind['param'], $arrWhere, $arrBind['bind']);
                unset($arrBind, $receipt);*/
                $compareField = array_keys($receipt);
                $arrBind = $this->db->get_binding(DBTableField::tableOrderCashReceipt(), $receipt, 'update', $compareField);
                $arrWhere = 'sno = ?';
                $this->db->bind_param_push($arrBind['bind'], 's', $orderCashReceiptData['sno']);
                $this->db->set_update_db(DB_ORDER_CASH_RECEIPT, $arrBind['param'], $arrWhere, $arrBind['bind']);
                unset($arrBind, $receipt);
            }

            // 전체취소의 경우 현금영수증 발행취소 처리
            if($updateCancelOrderData['settlePrice'] == '0'){
                $cashReceipt = \App::load('\\Component\\Payment\\CashReceipt');
                $isStatusFl = $cashReceipt->getCashReceiptData($cancel['orderNo']);

                // 해당 주문건의 현금영수증 발행 상태가 발행요청인경우 발행 상태값이 r임으로 adminChk값 필요함.
                if($isStatusFl['statusFl'] == 'r'){
                    $cashReceipt->sendPgCashReceipt($cancel['orderNo'], 'each', 'cancel', '전체 취소에 의한 발행취소', '', 'y');
                }else{  // 해당 주문건의 현금영수증 발행 상태가 발행완료일때는 발행 상태값이 y임으로 adminChk값 필요 없음.
                    $cashReceipt->sendPgCashReceipt($cancel['orderNo'], 'each', 'cancel', '전체 취소에 의한 발행취소', '', '');
                }
            }
        }

        $minusRestoreDepositFl = 'n';
        $minusRestoreMileageFl = 'n';

        // 취소 처리되는 예치금, 마일리지를 회원에게 돌려줌
        if ($cancelPrice['useDepositCancel'] > 0 && $beforeMinusRestoreDepositFl === false) {
            $deposit = App::load(\Component\Deposit\Deposit::class);
            $deposit->setMemberDeposit($originOrderData['memNo'], $cancelPrice['useDepositCancel'], Deposit::REASON_CODE_GROUP . Deposit::REASON_CODE_ADD_BUY_CANCEL, 'o', $cancel['orderNo'], null, '취소 시 사용 예치금 환불');

            $minusRestoreDepositFl = 'y';
        }

        if ($cancelPrice['useMileageCancel'] > 0 && $beforeMinusRestoreMileageFl === false) {
            $mileage = App::load(\Component\Mileage\Mileage::class);
            $mileage->setIsTran(false);
            $mileage->setMemberMileage($originOrderData['memNo'], $cancelPrice['useMileageCancel'], Mileage::REASON_CODE_GROUP . Mileage::REASON_CODE_ADD_BUY_CANCEL, 'o', $cancel['orderNo'], null, '취소 시 사용 마일리지 환불');

            $minusRestoreMileageFl = 'y';
        }


        $compareField = array_keys($updateCancelOrderData);
        $arrBind = $this->db->get_binding(DBTableField::tableOrder(), $updateCancelOrderData, 'update', $compareField);
        $arrWhere = 'orderNo = ?';
        $this->db->bind_param_push($arrBind['bind'], 's', $cancel['orderNo']);
        $this->db->set_update_db(DB_ORDER, $arrBind['param'], $arrWhere, $arrBind['bind']);
        unset($arrBind, $updateData);

        // 주문상품 update
        foreach ($updateCancelGoodsData as $orderGoodsSno => $val) {
            if (array_key_exists($orderGoodsSno, $cancel['orderGoods'])) {
                //환불 부가결제 금액이 있을때
                if($minusRestoreDepositFl === 'y'){
                    $val['minusRestoreDepositFl'] = 'y';
                }
                if($minusRestoreMileageFl === 'y'){
                    $val['minusRestoreMileageFl'] = 'y';
                }
            }

            $this->updateOrderGoods($val, $cancel['orderNo'], $orderGoodsSno);

			//상품 구매 개수 복원
			$order = new Order();
			if ($order->isLoggedMemberOrderGoodsCountData($cancel['orderNo'], $orderGoodsSno)) {
				$aGoodsSno['sno'][0] = $aGoodsSno['GoodsSno'][0] = $orderGoodsSno;
				$aGoodsSno['changeStatus'] = '';
				$orderKind['kind'] = '';
				$orderKind['channel'] = '';
				$claimInfo['orderCount'] = $cancel['orderGoods'][$orderGoodsSno];
				$order->setCountOrderGoods($cancel['orderNo'], $aGoodsSno, $orderKind, true, $claimInfo);
			}
        }

        // orderCd 최대 값 불러오기
        $orderCd = $this->getOrderGoodsMaxOrderCd($cancel['orderNo']);
        // 취소상품 insert & 취소 메세지 insert
        foreach ($cancel['orderGoods'] as $cancelOrderGoodsSno => $cancelGoodsCnt) {
            $beforeStatus = 'o1';// 입금대기
            $handleData = [
                'orderNo' => $cancel['orderNo'],
                'beforeStatus' => $beforeStatus,
                'handleMode' => $claimStatus,
                'handler' => Session::get('manager.managerId'),
                'handleCompleteFl' => 'y',
                'handleReason' => $cancelMsg['handleReason'],
                'handleDetailReason' => $cancelMsg['handleDetailReason'],
                'handleDetailReasonShowFl' => $cancelMsg['handleDetailReasonShowFl'] ?? 'n',
            ];

            /*
            $handleData['refundPrice'] = $insertCancelGoodsData[$cancelOrderGoodsSno][''];
            $handleData['refundDeliveryUseDeposit'] = $insertCancelGoodsData[$cancelOrderGoodsSno][''];
            $handleData['refundDeliveryUseMileage'] = $insertCancelGoodsData[$cancelOrderGoodsSno][''];
            $handleData['refundDeliveryInsuranceFee'] = $insertCancelGoodsData[$cancelOrderGoodsSno][''];
            $handleData['refundCharge'] = $insertCancelGoodsData[$cancelOrderGoodsSno][''];
            $handleData['refundUseDepositCommission'] = $insertCancelGoodsData[$cancelOrderGoodsSno][''];
            $handleData['refundUseMileageCommission'] = $insertCancelGoodsData[$cancelOrderGoodsSno][''];
            $handleData['refundGiveMileage'] = $insertCancelGoodsData[$cancelOrderGoodsSno][''];
             */
            $handleData['refundUseDeposit'] = (isset($insertCancelGoodsData[$cancelOrderGoodsSno]['handleCancelDeposit'])) ? $insertCancelGoodsData[$cancelOrderGoodsSno]['handleCancelDeposit'] : gd_isset($updateCancelGoodsData[$cancelOrderGoodsSno]['handleCancelDeposit'], 0);
            $handleData['refundUseMileage'] = (isset($insertCancelGoodsData[$cancelOrderGoodsSno]['handleCancelMileage'])) ? $insertCancelGoodsData[$cancelOrderGoodsSno]['handleCancelMileage'] : gd_isset($updateCancelGoodsData[$cancelOrderGoodsSno]['handleCancelMileage'], 0);
            $handleData['refundDeliveryCharge'] = (isset($insertCancelGoodsData[$cancelOrderGoodsSno]['handleCancelDeliveryPrice'])) ? $insertCancelGoodsData[$cancelOrderGoodsSno]['handleCancelDeliveryPrice'] : gd_isset($updateCancelGoodsData[$cancelOrderGoodsSno]['handleCancelDeliveryPrice'], 0);

            //handle 데이터 insert
            $handleSno = $this->setOrderHandle($handleData);

            if (array_key_exists($cancelOrderGoodsSno, $insertCancelGoodsData)) {
                // 부분취소일경우 복사해서 들어가는 정보에는 취소정보가 들어가야해서 orderHandle에 insertCancelGoodsData 정보 넣어줌
                $orderHandle = $insertCancelGoodsData[$cancelOrderGoodsSno];
                $orderHandle['cancelDt'] = date('Y-m-d H:i:s');
                //복수배송지를 사용한 주문건의 일부수량 부분취소 일 경우
                if($originOrderData['multiShippingFl'] === 'y'){
                    //금액이 0 원인 order delivery 생성
                    $insertOrderDeliverySno = $this->copyOrderDeliveryData($cancel['orderNo'], $insertCancelGoodsData['orderDeliverySno'], true);
                    $orderHandle['orderDeliverySno'] = $insertOrderDeliverySno;
                }
                $insertCancelOrderGoodsNo[] = $this->copyOrderGoodsData($cancel['orderNo'], $cancelOrderGoodsSno, $cancelMsg['orderStatus'], $handleSno, $cancelGoodsCnt, null, ++$orderCd, $orderHandle);
            } else {
                $insertCancelOrderGoodsNo[] = $cancelOrderGoodsSno;
                $orderHandle['cancelDt'] = date('Y-m-d H:i:s');
                $orderHandle['handleSno'] = $handleSno;
                $this->updateOrderGoods($orderHandle, $cancel['orderNo'], $cancelOrderGoodsSno);
            }
        }

        foreach ($insertCancelOrderGoodsNo as $key => $orderGoodsNo) {
            // 주문 로그 저장
            $order = App::load(\Component\Order\Order::class);
            $order->orderLog($cancel['orderNo'], $orderGoodsNo, '입금대기(o1)', $order->getOrderStatusAdmin($cancelMsg['orderStatus']) . '(' . $cancelMsg['orderStatus'] . ')', '상품취소');
        }

        //주문취소 상품의 realTax를 초기화 하기 위한 update > 상단에서 주문취소상품을 기준으로 주문상품 복사를 하기때문에 아래에서 update를 한번 더 해줌.
        foreach ($updateCancelGoodsData as $orderGoodsSno => $val) {
            //if (array_key_exists($orderGoodsSno, $cancel['orderGoods'])) {
            if (in_array($orderGoodsSno, $insertCancelOrderGoodsNo)) {
                $val['realTaxSupplyGoodsPrice'] = $val['realTaxVatGoodsPrice'] = $val['realTaxFreeGoodsPrice'] = 0;
            }

            $this->updateOrderGoods($val, $cancel['orderNo'], $orderGoodsSno);
        }

        // 배송비 update
        foreach ($updateCancelDeliveryData as $orderDeliverySno => $val) {
            $this->updateOrderDelivery($val, $cancel['orderNo'], $orderDeliverySno);
        }

        // 재고 처리
        if ($cancelReturn['stockFl'] == 'y') {
            $this->setReturnGoodsStock($cancel['orderNo'], $insertCancelOrderGoodsNo);
        }

        // 사은품 처리
        if ($cancelReturn['giftFl'] == 'n') {
            $this->setReturnGift($cancel['orderNo'], $cancelReturn['gift'], $claimStatus);
        }

        // 쿠폰 처리
        if ($cancelReturn['couponFl'] == 'y') {
            $this->setReturnCoupon($cancel['orderNo'], $cancelReturn['coupon'], $claimStatus, $restoreCouponFl);
        }

        //pay history 로그기록
        if ($cancelSettlePrice > 0) {
            $claimType = 'pc';
            $claimMemo = '부분취소';
        } else {
            $claimType = 'ac';
            $claimMemo = '전체취소';
        }

        // sms 개선(취소 시 sms 발송)
        $ord = \App::load('\\Component\\Order\\Order');
        $ord->sendOrderInfo(Code::CANCEL, 'sms', $cancel['orderNo'], null, $claimPrice);

        $processLogData = $this->getOrderProcessLogArrData($cancel['orderNo'], $claimType, $claimMemo, $updateCancelOrderData);
        $this->setOrderProcessLog($processLogData);

        return true;
    }

    /**
     * getSelectOrderReturnData
     * 주문의 쿠폰 / 사은품 정보 (클레임 처리 시 해당 정보에 따른 복원 여부 처리)
     *
     * @param string $orderNo 주문번호
     * @param string $statusMode 실행모드
     * @param string $exchangeMode 교환모드
     * @param string $sameExchangeOrderGoodsSno 동일상품교환 orderGoodsSno
     *
     * @return mixed
     */
    public function getSelectOrderReturnData($orderNo, $statusMode = '', $exchangeMode = '', $sameExchangeOrderGoodsSno = '')
    {
        if ($statusMode == 'a') { // @todo 상품 추가 시 사은품 추가선택 기능 제공
        } else {
            $returnData['coupon'] = $this->getOrderCouponData($orderNo, $statusMode);
            $order = App::load(\Component\Order\Order::class);
            $returnData['gift'] = $order->getOrderGift($orderNo);
            if ($returnData['gift'] === false) {
                $returnData['gift'] = [];
            }

            // 교환추가상품에 대한 마일리지 적립 여부를 보여줄지에 대한 체크
            if($statusMode === 'e'){
                $returnData['exchangeMileageApplyFl'] = 'y';

                $orderData = $this->getOrderData($orderNo);

                //회원 체크
                if((int)$orderData['memNo'] < 1){
                    $returnData['exchangeMileageApplyFl'] = 'n';
                }

                if($exchangeMode === 'anotherExchange'){
                    // 다른상품 교환시
                    $cartAdmin = new CartAdmin(0, false, $orderData['mallSno']);
                    $cartAdmin->getCartGoodsData(null, null, null, true);

                    // 적립할 마일리지가 없는 상태
                    if((int)$cartAdmin->totalMileage < 1){
                        $returnData['exchangeMileageApplyFl']  = 'n';
                    }
                }
                else {
                    // 같은상품 교환시
                    $orderGoodsSnoArray = [];
                    $orderGoodsSnoArray = explode(INT_DIVISION, $sameExchangeOrderGoodsSno);
                    if(count($orderGoodsSnoArray) > 0){
                        $plusMileageArray = [];
                        foreach($orderGoodsSnoArray as $key => $orderGoodsSno){
                            $orderGoodsData = $order->getOrderGoodsData($orderNo, $orderGoodsSno);
                            foreach ($orderGoodsData as $scmNo => $dataVal) {
                                $plusMileageArray[] = array_sum(array_column($dataVal, 'goodsMileage'));
                                $plusMileageArray[] = array_sum(array_column($dataVal, 'memberMileage'));
                                $plusMileageArray[] = array_sum(array_column($dataVal, 'couponGoodsMileage'));
                            }
                        }
                        if(array_sum($plusMileageArray) < 1){
                            $returnData['exchangeMileageApplyFl']  = 'n';
                        }
                    }
                }
            }
        }

        return $returnData;
    }

    /**
     * setReturnGoodsStock
     * 재고 복원
     *
     * @param string $orderNo 주문번호
     * @param array $orderGoodsNo 복원할 주문 취소된 주문 상품 고유번호
     */
    public function setReturnGoodsStock($orderNo, $orderGoodsNo)
    {
        $order = App::load(\Component\Order\Order::class);
        $order->setGoodsStockRestore($orderNo, $orderGoodsNo);
    }

    /**
     * setReturnCoupon
     * 쿠폰 복원
     *
     * @param string $orderNo 주문번호
     * @param array $memberCoupon 복원 회원 쿠폰 번호
     * @param string $claimStatus 클레임 코드
     * @param boolean $restoreCouponFl 쿠폰 복원 여부
     */
    public function setReturnCoupon($orderNo, $memberCoupon, $claimStatus, $restoreCouponFl = true)
    {
        foreach ($memberCoupon as $memberCouponNo) {
            if ($restoreCouponFl === true) {
                // 주문번호에 따른 회원 쿠폰 번호를 복원 체크 업데이트
                $updateCouponReturn['minusRestoreCouponFl'] = 'y';
                $updateCouponReturn['plusRestoreCouponFl'] = 'y';
                $compareField = array_keys($updateCouponReturn);
                $arrBind = $this->db->get_binding(DBTableField::tableOrderCoupon(), $updateCouponReturn, 'update', $compareField);
                $arrWhere = 'orderNo = ? AND memberCouponNo = ?';
                $this->db->bind_param_push($arrBind['bind'], 's', $orderNo);
                $this->db->bind_param_push($arrBind['bind'], 'i', $memberCouponNo);
                $this->db->set_update_db(DB_ORDER_COUPON, $arrBind['param'], $arrWhere, $arrBind['bind']);
                unset($arrBind, $updateData);

                // 회원 쿠폰을 재발급
                $arrBind = [];
                $this->db->strField = "mc.*";
                $this->db->strWhere = 'mc.memberCouponNo = ?';
                $this->db->bind_param_push($arrBind, 'i', $memberCouponNo);
                $query = $this->db->query_complete();
                $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER_COUPON . ' as mc ' . implode(' ', $query);
                $getData = $this->db->query_fetch($strSQL, $arrBind, false);
                unset($arrBind);

                unset($getData['memberCouponNo']);
                unset($getData['regDt']);
                unset($getData['modDt']);
                unset($getData['memberCouponCartDate']);
                unset($getData['memberCouponUseDate']);
                $getData['memberCouponState'] = 'y';
                $getData['orderWriteCouponState'] = 'y';
                $compareField = array_keys($getData);
                $arrBind = $this->db->get_binding(DBTableField::tableMemberCoupon(), $getData, 'insert', $compareField);

                $this->db->set_insert_db(DB_MEMBER_COUPON, $arrBind['param'], $arrBind['bind'], 'y');
                $insertReturnCoupon = $this->db->insert_id();
            }

            unset($arrBind);
            unset($getData);

            $logger = \App::getInstance('logger');
            $logger->channel('order')->info($claimStatus . '로 인한 쿠폰 복원으로 쿠폰 발급 [' . $orderNo . '=>' . $memberCouponNo . '>' . $insertReturnCoupon . ']');
            unset($insertReturnCoupon);
        }
    }

    /**
     * setReturnGift
     * 사은품 지급 안함
     *
     * @param string $orderNo 주문번호
     * @param array $orderGift 지급 안할 주문사은품 고유 번호
     * @param string $claimStatus 클레임 코드
     */
    public function setReturnGift($orderNo, $orderGift, $claimStatus)
    {
        foreach ($orderGift as $orderGiftNo) {
            $arrBind = [
                'is',
                $orderGiftNo,
                $orderNo,
            ];
            $this->db->set_delete_db(DB_ORDER_GIFT, 'sno = ? AND orderNo = ?', $arrBind);
            unset($arrBind);

            $logger = \App::getInstance('logger');
            $logger->channel('order')->info($claimStatus . '로 인한 사은품 지급안함으로 삭제 [' . $orderNo . '=>' . $orderGiftNo . ']');
        }
    }

    /**
     * setOrderRestore
     * 복원
     *
     * @param string $orderNo
     * @param string $claimStatus
     * @return bool
     * @throws Exception
     */
    public function setOrderRestore($orderNo, $claimStatus)
    {
        $exceptMsg = [];
        // Original 존재 확인
        $count = $this->getOrderOriginalCount($orderNo, $claimStatus);
        if ($count < 1) {
            throw new Exception(__('복원 실패하였습니다.(1)'));
        }

        // 현재 주문상품의 정보
        $orderGoodsData = $this->getOrderGoodsStatusData($orderNo);
        $stockOrderGoodsSno = [];
        foreach ($orderGoodsData as $val) {
            // 복원된 재고를 다시 차감
            if ($val['minusRestoreStockFl'] == 'y' && substr($val['orderStatus'], 0, 1) == $claimStatus) {
                $stockOrderGoodsSno[] = $val['sno'];
            }
            // 로그 데이터
            $data[$val['sno']] = $val['orderStatus'];

			$order = new Order();
			if ($order->isLoggedMemberOrderGoodsCountData($orderNo, $val['sno'])) {
				$aGoodsSno['sno'][0] = $aGoodsSno['GoodsSno'][0] = $val['sno'];
				$aGoodsSno['changeStatus'] = '';
				$orderKind['kind'] = '';
				$orderKind['channel'] = '';
				$claimInfo['orderCount'] = $val['goodsCnt'];
				$order->setCountOrderGoods($orderNo, $aGoodsSno, $orderKind, false);
			}
        }
        $orderGoodsOriginalData = $this->getOrderGoodsStatusOriginalData($orderNo);

        // 복원된 재고를 다시 차감(취소 했던 주문 정보로 복원 진행)
        if (count($stockOrderGoodsSno) > 0) {
            $order = App::load(\Component\Order\Order::class);
            $stockResult = $order->setGoodsStockCutback($orderNo, $stockOrderGoodsSno);
        }
        if (in_array('오류', $stockResult['code']) === true) {
            $getKey = array_search('오류', $stockResult['code']);
            $exceptMsg[] = $stockResult['desc'][$getKey];
        }

        $giftFl = false; // 사은품
        $cashFl = false; // 현금영수증
        if ($claimStatus == 'c') {
            if (is_object($this->member) === false) {
                $this->member = \App::load('\\Component\\Member\\Member');
            }
            $claimStatusName = '취소';
            $giftFl = true; // 사은품
            $cashFl = true; // 현금영수증

            // 취소시 환불한 예치금 마일리지를 다시 차감
            $orderOriginalData = $this->getOrderOriginalDepositMileageData($orderNo, $claimStatus);
            $orderData = $this->getOrderDepositMileageData($orderNo);
            if (empty($orderOriginalData['memNo']) === false) {
                $memInfo = $this->member->getMemberInfo($orderOriginalData['memNo']);
            }

            $diffDeposit = $orderData['useDeposit'] - $orderOriginalData['useDeposit'];
            $diffMileage = $orderData['useMileage'] - $orderOriginalData['useMileage'];
            $tmpDiffDeposit = $diffDeposit > 0 ? $diffDeposit : -$diffDeposit;
            $tmpDiffMileage = $diffMileage > 0 ? $diffMileage : -$diffMileage;

            if (empty($memInfo) === false && $memInfo['deposit'] - $tmpDiffDeposit >= 0) {
                if ($diffDeposit == 0) {}
                else {
                    // 가감 처리
                    $deposit = App::load(\Component\Deposit\Deposit::class);
                    $deposit->setMemberDeposit($orderOriginalData['memNo'], $diffDeposit, Deposit::REASON_CODE_GROUP . Deposit::REASON_CODE_GOODS_BUY, 'o', $orderNo, null, '상품구매 (취소복원)');
                }
            } else {
                if ($diffDeposit == 0) {}
                else {
                    $exceptMsg[] = '고객이 보유한 예치금 부족';
                }
            }

            if (empty($memInfo) === false && $memInfo['mileage'] - $tmpDiffMileage >= 0) {
                if ($diffMileage == 0) {
                } else {
                    // 가감 처리
                    $mileage = App::load(\Component\Mileage\Mileage::class);
                    $mileage->setIsTran(false);
                    $mileage->setMemberMileage($orderOriginalData['memNo'], $diffMileage, Mileage::REASON_CODE_GROUP . Mileage::REASON_CODE_USE_GOODS_BUY, 'o', $orderNo, null, '상품구매 시 마일리지 사용 (취소복원)');
                }
            } else {
                if ($diffMileage == 0) {
                } else {
                    $exceptMsg[] = '고객이 보유한 마일리지 부족';
                }
            }
        }

        if (empty($exceptMsg) === false) {
            $message = '<b>취소복원이 불가합니다. 복원 불가 사유를 확인해주세요.</b><br /><br />';
            $message .= @implode('<br />', $exceptMsg);
            throw new Exception($message);
        }

        // Default 삭제
        $deleteResult = $this->deleteOrderData('default', $orderNo, $giftFl, $cashFl);
        if (!$deleteResult) {
            throw new Exception(__('복원 실패하였습니다.(2)'));
        }
        // Original 로 Default 복원
        $result = $this->restoreOrderOriginalData($orderNo, $claimStatus, $giftFl, $cashFl);
        if (!$result) {
            throw new Exception(__('복원 실패하였습니다.(3)'));
        }
        // Original 삭제
        $deleteOriginalResult = $this->deleteOrderData('original', $orderNo, $giftFl, $cashFl);
        if (!$deleteOriginalResult) {
            throw new Exception(__('복원 실패하였습니다.(4)'));
        }
        // 로그 저장
        foreach ($orderGoodsOriginalData as $key => $val) {
            // 주문 로그 저장
            $order = App::load(\Component\Order\Order::class);
            $order->orderLog($orderNo, $val['sno'], $order->getOrderStatusAdmin($data[$val['sno']]) . '(' . $data[$val['sno']] . ')', $order->getOrderStatusAdmin($val['orderStatus']) . '(' . $val['orderStatus'] . ')', $claimStatusName . '복원');
        }

        return true;
    }

    /**
     * getSelectOrderGoodsAddData
     * 상품 추가 데이터
     *
     * @param array $postValue
     * @return mixed
     */
    public function getSelectOrderGoodsAddData($postValue)
    {
        $orderNo = $postValue['orderNo'];

        // 등록되어 있는 주문 상품 정보
        $orderAdmin = App::load(\Component\Order\OrderAdmin::class);
        $orderGoodsData = $orderAdmin->getOrderGoodsData($orderNo);

        // 입금 후에는 추가 결제 안내 등록
        $settleAfterStatus = ['p', 'g'];

        // 주문 상품별 배송비 조건 추출
        $orderGoodsDelivery = [];
        $settleStatus = false; // 입금 전
        foreach ($orderGoodsData as $scmNo => $orderGoodsData) {
            foreach ($orderGoodsData as $key => $val) {
                $orderGoodsDelivery[$val['deliverySno']]['orderGoodsSno'] = $val['sno'];
                $orderGoodsDelivery[$val['deliverySno']]['orderGoodsNm'] = $val['goodsNm'];
                if ($settleStatus === false && in_array(substr($val['orderStatus'], 0, 1), $settleAfterStatus)) { // 입금 전 후 체크
                    $settleStatus = true;
                }
            }
        }

        // 주문정보
        $originOrderData = $this->getOrderData($orderNo);
        if($originOrderData['multiShippingFl'] === 'y'){
            $this->multiShippingOrderFl = true;
        }
        $originOrderInfoData = $this->getOrderInfoData($orderNo);

        if($this->multiShippingOrderFl === true){
            $mainOrderInfoData = $originOrderInfoData[0];
        }
        else {
            $mainOrderInfoData = $originOrderInfoData;
        }

        // 배송 호출
        $delivery = App::load(\Component\Delivery\DeliveryCart::class);
        $delivery->setDeliveryMethodCompanySno();

        // 배송비 산출을 위한 주소 및 국가 선택
        if ($originOrderData['mallSno'] > DEFAULT_MALL_NUMBER) {
            // 주문서 작성페이지에서 선택된 국가코드
            $address = $mainOrderInfoData['receiverCountryCode'];
        } else {
            // 장바구니내 해외/지역별 배송비 처리를 위한 주소 값
            $address = str_replace(' ', '', $mainOrderInfoData['receiverAddress'] . $mainOrderInfoData['receiverAddressSub']);
        }

        //복수배송지 사용시 카트에 넣을 데이터 조합
        if($this->multiShippingOrderFl === true){
            if(count($originOrderInfoData) > 0){
                foreach($originOrderInfoData as $key => $value){
                    $multiShippingOrderInfo[$value['orderInfoCd']] = $value;
                }
            }

            $orderMultiShipping = App::load('\\Component\\Order\\OrderMultiShipping');
            $setMultiShippingAddPostData = $orderMultiShipping->setMultiShippingClaimAddData($postValue, $multiShippingOrderInfo);
            $resetCart = $orderMultiShipping->resetCart($setMultiShippingAddPostData, true);
            $postValue['cartSno'] = $resetCart['setCartSno'];
            $postValue['orderInfoCdData'] = $resetCart['orderInfoCd'];
            $postValue['orderInfoCdBySno'] = $resetCart['orderInfoCdBySno'];
            $postValue['multiShippingFl'] = 'y';
            $postValue['isAdminMultiShippingFl'] = 'y';
            unset(
                $setMultiShippingAddPostData['memNo'],
                $setMultiShippingAddPostData['cartSno'],
                $setMultiShippingAddPostData['sno'],
                $setMultiShippingAddPostData['priceInfo']
            );
            $postValue = array_merge((array)$postValue, (array)$setMultiShippingAddPostData);
            $address = str_replace(' ', '', $setMultiShippingAddPostData[1]['receiverAddress'] . $setMultiShippingAddPostData[1]['receiverAddressSub']);
        }

        // 추가한 장바구니 불러오기
        $cartAdmin = new CartAdmin(0, false, $originOrderData['mallSno']);
        $cartAdmin->orderGoodsChange = true;
        $cartAddData = $cartAdmin->getCartGoodsData(null, $address, null, true, false, $postValue);

        $sameDelivery = []; // 같은 배송비 조건
        foreach ($cartAddData as $scmNo => $deliveryVal) {
            foreach ($deliveryVal as $deliverySno => $cartGoodsVal) {
                foreach ($cartGoodsVal as $cartGoodsData) {
                    if (array_key_exists($deliverySno, $orderGoodsDelivery)) {
                        $sameDelivery[$cartGoodsData['sno']]['sameOrderGoodsSno'] = $orderGoodsDelivery[$deliverySno]['orderGoodsSno'];
                        $sameDelivery[$cartGoodsData['sno']]['sameOrderGoodsNm'] = $orderGoodsDelivery[$deliverySno]['orderGoodsNm'];
                        $sameDelivery[$cartGoodsData['sno']]['cartGoodsNm'] = $cartGoodsData['goodsNm'];
                        $sameDelivery[$cartGoodsData['sno']]['deliveryNm'] = $cartGoodsData['goodsDeliveryMethod'];
                        $sameDelivery[$cartGoodsData['sno']]['deliveryCharge'] = $cartGoodsData['goodsDeliveryPrice'];
                    }

                    //복수배송지가 사용된 주문건 일 시 totalDelivery 배열이 key 는 order info cd 로 대체된다.
                    if($this->multiShippingOrderFl === true){
                        if((int)$postValue['orderInfoCdBySno'][$cartGoodsData['sno']] < 2){
                            $subjectName = '(메인) ' . $cartGoodsData['goodsDeliveryMethod'];
                        }
                        else {
                            $subjectName = '(추가' . ((int)$postValue['orderInfoCdBySno'][$cartGoodsData['sno']]-1) . ') ' . $cartGoodsData['goodsDeliveryMethod'];
                        }
                        $addData['totalDelivery'][$postValue['orderInfoCdBySno'][$cartGoodsData['sno']]]['deliverySno'] = $deliverySno;
                        $addData['totalDelivery'][$postValue['orderInfoCdBySno'][$cartGoodsData['sno']]]['cartSno'] = $cartGoodsData['sno'];
                        $addData['totalDelivery'][$postValue['orderInfoCdBySno'][$cartGoodsData['sno']]]['subject'] = $subjectName;
                    }
                }

                if($this->multiShippingOrderFl !== true){
                    $addData['totalDelivery'][$deliverySno]['cartSno'] = $cartGoodsVal[0]['sno'];
                    $addData['totalDelivery'][$deliverySno]['subject'] = $cartGoodsVal[0]['goodsNm'];
                    $addData['totalDelivery'][$deliverySno]['deliverySno'] = $deliverySno;
                }
            }
        }

        if($this->multiShippingOrderFl === true){
            foreach ($cartAdmin->totalGoodsMultiDeliveryPolicyCharge as $orderInfoCd => $orderInfoCdVal) {
                foreach($orderInfoCdVal as $deliverySno => $deliveryCharge){
                    $addData['totalDelivery'][$orderInfoCd]['deliveryCharge'] = $deliveryCharge + $cartAdmin->totalGoodsMultiDeliveryAreaPrice[$orderInfoCd][$deliverySno];
                }
            }
        }
        else {
            foreach ($cartAdmin->totalGoodsDeliveryPolicyCharge as $deliverySno => $deliveryCharge) {
                $addData['totalDelivery'][$deliverySno]['deliveryCharge'] = $deliveryCharge + $cartAdmin->totalGoodsDeliveryAreaPrice[$deliverySno];
            }
        }

        $addData['enuriSumPrice'] = (int)array_sum(explode(INT_DIVISION, $postValue['enuri']));
        $addData['totalGoodsPrice'] = $cartAdmin->totalGoodsPrice;
        $addData['totalGoodsDcPrice'] = $cartAdmin->totalGoodsDcPrice;
        $addData['totalGoodsMileage'] = $cartAdmin->totalGoodsMileage;
        $addData['totalDeliveryCharge'] = $cartAdmin->totalDeliveryCharge;
        $addData['totalSettlePrice'] = $cartAdmin->totalSettlePrice - $addData['enuriSumPrice'];
        $addData['giftForData'] = $cartAdmin->giftForData;
        $addData['sameDelivery'] = $sameDelivery;
        $addData['settleStatus'] = $settleStatus;

        return $addData;
    }

    /**
     * 상품 추가 금액 계산
     *
     * @param string $orderNo 주문번호
     * @param array $addData 추가 정보
     * @param boolean $orderGoodsChange 주문상품교환여부
     *
     * @throws
     *
     * @return boolean true
     */
    public function setAddOrderGoods($orderNo, $addData, $orderGoodsChange = false)
    {
        // 원본정보 origin 저장하기
        $claimStatus = 'a';
        $count = $this->getOrderOriginalCount($orderNo, $claimStatus);
        if ($count < 1) {
            //이전 취소건 존재하지 않을 시 현재 주문정보 백업
            $return = $this->setBackupOrderOriginalData($orderNo, $claimStatus, true, true);
        }

        $orderStatus = 'o1'; // 상품 추가시 입금대기 , 교환 추가시는 ? @todo

        $updateAddOrderData = [];// 최종 처리된 주문 정보(수정)
        $insertAddGoodsData = [];// 최종 처리된 주문 상품 정보(등록)
        $insertAddDeliveryData = [];// 최종 처리된 주문 배송비 정보(등록)
        $insertAddGiftData = [];// 최종 처리된 주문 사은품 정보(등록)

        // 주문 정보
        $originOrderData = $this->getOrderData($orderNo);
        $originOrderInfoData = $this->getOrderInfoData($orderNo);

        if($originOrderData['multiShippingFl'] === 'y'){
            $multiShippingOrderInfoSnoArr = [];
            $this->multiShippingOrderFl = true;
            $originMultiOrderInfoData = $this->getOrderInfoData($orderNo);
            $originOrderInfoData = $originMultiOrderInfoData[0];
            if(count($originMultiOrderInfoData) > 0){
                foreach($originMultiOrderInfoData as $key => $value){
                    $multiShippingOrderInfo[$value['orderInfoCd']] = $value;
                    $multiShippingOrderInfoSnoArr[$value['orderInfoCd']] = $value['sno'];
                }
            }

            $orderMultiShipping = App::load('\\Component\\Order\\OrderMultiShipping');
            $setMultiShippingAddPostData = $orderMultiShipping->setMultiShippingClaimAddData($addData, $multiShippingOrderInfo);
            $resetCart = $orderMultiShipping->resetCart($setMultiShippingAddPostData, true);
            $addData['cartSno'] = $resetCart['setCartSno'];
            $addData['orderInfoCdData'] = $resetCart['orderInfoCd'];
            $addData['orderInfoCdBySno'] = $resetCart['orderInfoCdBySno'];
            $addData['multiShippingFl'] = 'y';
            $addData['isAdminMultiShippingFl'] = 'y';
            unset(
                $setMultiShippingAddPostData['memNo'],
                $setMultiShippingAddPostData['cartSno'],
                $setMultiShippingAddPostData['sno'],
                $setMultiShippingAddPostData['priceInfo']
            );
            $addData = array_merge((array)$addData, (array)$setMultiShippingAddPostData);
            unset($originMultiOrderInfoData);
        }

        // 배송 호출
        $delivery = App::load(\Component\Delivery\DeliveryCart::class);
        $delivery->setDeliveryMethodCompanySno();

        // 배송비 산출을 위한 주소 및 국가 선택
        if ($originOrderData['mallSno'] > DEFAULT_MALL_NUMBER) {
            // 주문서 작성페이지에서 선택된 국가코드
            $address = $originOrderInfoData['receiverCountryCode'];
        } else {
            // 장바구니내 해외/지역별 배송비 처리를 위한 주소 값
            $address = str_replace(' ', '', $originOrderInfoData['receiverAddress'] . $originOrderInfoData['receiverAddressSub']);
        }

        // 추가한 장바구니 불러오기
        $cartAdmin = new CartAdmin(0, false, $originOrderData['mallSno']);
        $cartAdmin->orderGoodsChange = $orderGoodsChange;
        $cartAddData = $cartAdmin->getCartGoodsData(null, $address, null, true, false, $addData);

        // orderCd 최대 값 불러오기
        $orderCd = $this->getOrderGoodsMaxOrderCd($orderNo);

        // 해외배송 기본 정책
        $overseasDeliveryPolicy = null;
        $onlyOneOverseasDelivery = false;
        if ($originOrderData['mallSno'] != 1) {
            $overseasDelivery = new OverseasDelivery();
            $overseasDeliveryPolicy = $overseasDelivery->getBasicData($originOrderData['mallSno'], 'mallSno');
        }

        $order = App::load(\Component\Order\Order::class);

        // 주문 정보에 추가할 금액
        $orderUpdatePrice = [];

        if($this->multiShippingOrderFl === true){
            foreach ($cartAdmin->totalGoodsMultiDeliveryPolicyCharge as $key => $val) {
                foreach ($val as $tKey => $tVal) {
                    $deliveryPolicy = $delivery->getDataDeliveryWithGoodsNo([$tKey]);
                    $scmNo = $deliveryPolicy[$tKey]['scmNo'];
                    $goodsData = $cartAddData[$scmNo][$tKey][0];

                    // 배송정책내 부가세율 관련 정보 설정
                    $deliveryTaxFreeFl = $goodsData['goodsDeliveryTaxFreeFl'];
                    $deliveryTaxPercent = $goodsData['goodsDeliveryTaxPercent'];
                    $taxableDeliveryCharge = $cartAdmin->totalGoodsMultiDeliveryPolicyCharge[$key][$tKey] + $cartAdmin->totalGoodsMultiDeliveryAreaPrice[$key][$tKey];

                    // 상단에서 계산된 금액으로 배송비 복합과세 처리
                    $tmpDeliveryTaxPrice = NumberUtils::taxAll($taxableDeliveryCharge, $deliveryTaxPercent, $deliveryTaxFreeFl);

                    // 초기화
                    $taxDeliveryCharge['supply'] = 0;
                    $taxDeliveryCharge['tax'] = 0;
                    $taxDeliveryCharge['free'] = 0;
                    if ($deliveryTaxFreeFl == 't') {
                        // 배송비 과세처리
                        $taxDeliveryCharge['supply'] = $tmpDeliveryTaxPrice['supply'];
                        $taxDeliveryCharge['tax'] = $tmpDeliveryTaxPrice['tax'];
                    } else {
                        // 배송비 면세처리
                        $taxDeliveryCharge['free'] = $tmpDeliveryTaxPrice['supply'];
                    }

                    $deliveryInfo = [
                        'orderNo'                     => $orderNo,
                        'scmNo'                       => $scmNo,
                        'commission'                  => $deliveryPolicy[$tKey]['scmCommissionDelivery'],
                        'deliverySno'                 => $tKey,
                        'deliveryCharge'              => $taxableDeliveryCharge,
                        'taxSupplyDeliveryCharge'     => $taxDeliveryCharge['supply'],
                        'taxVatDeliveryCharge'        => $taxDeliveryCharge['tax'],
                        'taxFreeDeliveryCharge'       => $taxDeliveryCharge['free'],
                        'realTaxSupplyDeliveryCharge' => $taxDeliveryCharge['supply'],
                        'realTaxVatDeliveryCharge'    => $taxDeliveryCharge['tax'],
                        'realTaxFreeDeliveryCharge'   => $taxDeliveryCharge['free'],
                        'deliveryPolicyCharge'        => $cartAdmin->totalGoodsMultiDeliveryPolicyCharge[$key][$tKey],
                        'deliveryAreaCharge'          => $cartAdmin->totalGoodsMultiDeliveryAreaPrice[$key][$tKey],
                        'deliveryFixFl'               => $goodsData['goodsDeliveryFixFl'],
                        'deliveryInsuranceFee'        => 0,
                        'goodsDeliveryFl'             => $goodsData['goodsDeliveryFl'],
                        'deliveryTaxInfo'             => $deliveryTaxFreeFl . STR_DIVISION . $deliveryTaxPercent,
                        'deliveryWeightInfo'          => 0,
                        'deliveryPolicy'              => json_encode($deliveryPolicy[$tKey], JSON_UNESCAPED_UNICODE),
                        'overseasDeliveryPolicy'      => json_encode($overseasDeliveryPolicy, JSON_UNESCAPED_UNICODE),
                        'deliveryCollectFl'           => $goodsData['goodsDeliveryCollectFl'],
                        'deliveryCollectPrice'        => 0,
                        // 배송비조건별인 경우만 금액을 넣는다.
                        'deliveryMethod'              => $goodsData['goodsDeliveryMethod'],
                        'deliveryWholeFreeFl'         => $goodsData['goodsDeliveryWholeFreeFl'],
                        'deliveryWholeFreePrice'      => 0,
                        // 배송비 조건별/상품별에 따라서 금액을 받아온다.
                        'deliveryLog'                 => '',
                        'orderInfoSno'                => $multiShippingOrderInfoSnoArr[$key],
                    ];

                    // 배송비 추가 안함
                    if ($addData['addDeliveryFl'][$key] == 'n') {
                        $deliveryInfo['deliveryCharge'] = 0;
                        $deliveryInfo['taxSupplyDeliveryCharge'] = 0;
                        $deliveryInfo['taxVatDeliveryCharge'] = 0;
                        $deliveryInfo['taxFreeDeliveryCharge'] = 0;
                        $deliveryInfo['realTaxSupplyDeliveryCharge'] = 0;
                        $deliveryInfo['realTaxVatDeliveryCharge'] = 0;
                        $deliveryInfo['realTaxFreeDeliveryCharge'] = 0;
                        $deliveryInfo['deliveryPolicyCharge'] = 0;
                        $deliveryInfo['deliveryAreaCharge'] = 0;
                        $deliveryInfo['divisionDeliveryUseDeposit'] = 0;
                        $deliveryInfo['divisionDeliveryUseMileage'] = 0;
                        $deliveryInfo['divisionDeliveryCharge'] = 0;
                        $deliveryInfo['deliveryInsuranceFee'] = 0;
                        $deliveryInfo['deliveryCollectPrice'] = 0;
                        $deliveryInfo['deliveryWholeFreePrice'] = 0;
                    }
                    else {
                        $orderUpdatePrice['totalDeliveryCharge'] += $taxableDeliveryCharge;
                        $orderUpdatePrice['settlePrice'] += $deliveryInfo['deliveryCharge'];
                        $orderUpdatePrice['taxSupplyPrice'] += $deliveryInfo['taxSupplyDeliveryCharge'];
                        $orderUpdatePrice['taxVatPrice'] += $deliveryInfo['taxVatDeliveryCharge'];
                        $orderUpdatePrice['taxFreePrice'] += $deliveryInfo['taxFreeDeliveryCharge'];
                    }

                    // 정책별 배송 정보 저장
                    $arrBind = $this->db->get_binding(DBTableField::tableOrderDelivery(), $deliveryInfo, 'insert');
                    $this->db->set_insert_db(DB_ORDER_DELIVERY, $arrBind['param'], $arrBind['bind'], 'y', false);
                    $orderMultiDeliverySno[$key][$tKey] = $this->db->insert_id();
                    unset($arrBind);
                }
            }
        }

        $arrOrderGoodsSno = [];
        foreach ($cartAddData as $sKey => $sVal) {
            foreach ($sVal as $dKey => $dVal) {
                $onlyOneDelivery = true;
                $deliveryPolicy = $delivery->getDataDeliveryWithGoodsNo([$dKey]);
                $deliveryMethodFl = '';
                foreach ($dVal as $gKey => $gVal) {
                    $gVal['orderNo'] = $orderNo;
                    $gVal['mallSno'] = $originOrderData['mallSno'];
                    $gVal['orderCd'] = ++$orderCd;
                    $gVal['goodsNm'] = $gVal['goodsNm'];
                    $gVal['goodsType'] = $gVal['goodsType'];
                    $gVal['goodsNmStandard'] = $gVal['goodsNmStandard'];
                    $gVal['orderStatus'] = $orderStatus;
                    $gVal['deliveryMethodFl'] = empty($gVal['deliveryMethodFl']) === true ? 'delivery' : $gVal['deliveryMethodFl']; //배송방식
                    $gVal['goodsDeliveryCollectFl'] = $gVal['goodsDeliveryCollectFl'];
                    // 상품별 배송비조건인 경우 선불/착불 금액 기록 (배송비조건별인 경우 orderDelivery에 저장)
                    // orderDelivery에 각 상품별 선/착불 데이터를 저장하기 애매해서 이와 같이 처리 함
                    if ($gVal['goodsDeliveryFl'] === 'n') {
                        $gVal['goodsDeliveryCollectPrice'] = $gVal['goodsDeliveryCollectFl'] == 'pre' ? $gVal['price']['goodsDeliveryPrice'] : $gVal['price']['goodsDeliveryCollectPrice'];
                    }

                    //조건별 배송비 일때
                    if ($deliveryPolicy[$dKey]['goodsDeliveryFl'] === 'y') {
                        //조건별 배송비 사용 일 경우 배송방식을 모두 변환한다.
                        if (trim($deliveryMethodFl) === '') {
                            $deliveryMethodFl = empty($gVal['deliveryMethodFl']) === true ? 'delivery' : $gVal['deliveryMethodFl']; //배송방식
                        }
                        $gVal['deliveryMethodFl'] = $deliveryMethodFl;
                    } else {
                        $deliveryMethodFl = '';
                    }

                    if ($gVal['deliveryMethodFl'] && $gVal['deliveryMethodFl'] !== 'delivery') {
                        $gVal['invoiceCompanySno'] = $delivery->deliveryMethodList['sno'][$gVal['deliveryMethodFl']];
                    }

                    $gVal['goodsPrice'] = $gVal['price']['goodsPrice'];
                    $gVal['addGoodsCnt'] = count(gd_isset($gVal['addGoods']));
                    // 기존 추가상품의 계산로직 레거시 보장을 위해 0으로 변경 처리
                    $gVal['addGoodsPrice'] = 0;
                    $gVal['optionPrice'] = $gVal['price']['optionPrice'];
                    $gVal['optionCostPrice'] = $gVal['price']['optionCostPrice'];
                    $gVal['optionTextPrice'] = $gVal['price']['optionTextPrice'];
                    $gVal['fixedPrice'] = $gVal['price']['fixedPrice'];
                    $gVal['costPrice'] = $gVal['price']['costPrice'];
                    $gVal['goodsDcPrice'] = $gVal['price']['goodsDcPrice'];
                    $gVal['memberDcPrice'] = 0;
                    if ($this->myappUseFl) {
                        $gVal['myappDcPrice'] = 0;
                    }
                    $gVal['memberMileage'] = 0;
                    $gVal['memberOverlapDcPrice'] = $gVal['price']['goodsMemberOverlapDcPrice'];
                    $gVal['couponGoodsDcPrice'] = $gVal['price']['goodsCouponGoodsDcPrice'];
                    $gVal['goodsMileage'] = $gVal['mileage']['goodsMileage'];
                    $gVal['couponGoodsMileage'] = $gVal['mileage']['couponGoodsMileage'];
                    $gVal['goodsTaxInfo'] = $gVal['taxFreeFl'] . STR_DIVISION . $gVal['taxPercent'];// 상품 세금 정보
                    $gVal['divisionUseDeposit'] = $gVal['price']['divisionUseDeposit'];
                    $gVal['divisionUseMileage'] = $gVal['price']['divisionUseMileage'];
                    $gVal['divisionGoodsDeliveryUseDeposit'] = $gVal['price']['divisionGoodsDeliveryUseDeposit'];
                    $gVal['divisionGoodsDeliveryUseMileage'] = $gVal['price']['divisionGoodsDeliveryUseMileage'];
                    $gVal['divisionCouponOrderDcPrice'] = $gVal['price']['divisionCouponOrderDcPrice'];
                    $gVal['divisionCouponOrderMileage'] = $gVal['price']['divisionCouponOrderMileage'];
                    if ($gVal['hscode']) $gVal['hscode'] = $gVal['hscode'];
                    if ($gVal['timeSaleFl']) $gVal['timeSaleFl'] = 'y';
                    else $gVal['timeSaleFl'] = 'n';

                    $orderUpdatePrice['totalGoodsPrice'] += ($gVal['price']['goodsPrice'] + $gVal['price']['optionPrice'] + $gVal['price']['optionTextPrice']) * $gVal['goodsCnt'];
                    $orderUpdatePrice['totalGoodsDcPrice'] += $gVal['price']['goodsDcPrice'];
                    $orderUpdatePrice['totalGoodsMileage'] += $gVal['price']['goodsMileage'];

                    // 배송비 테이블 데이터 설정으로 foreach구문에서 최초 한번만 실행된다.
                    if ($onlyOneDelivery === true) {
                        // 배송정책내 부가세율 관련 정보 설정
                        $deliveryTaxFreeFl = $gVal['goodsDeliveryTaxFreeFl'];
                        $deliveryTaxPercent = $gVal['goodsDeliveryTaxPercent'];

                        // 배송비 복합과세 처리
                        $deliveryCharge = $cartAdmin->totalGoodsDeliveryPolicyCharge[$dKey] + $cartAdmin->totalGoodsDeliveryAreaPrice[$dKey];
                        $tmpDeliveryTaxPrice = NumberUtils::taxAll($deliveryCharge, $deliveryTaxPercent, $deliveryTaxFreeFl);

                        // 초기화
                        $taxDeliveryCharge['supply'] = 0;
                        $taxDeliveryCharge['tax'] = 0;
                        $taxDeliveryCharge['free'] = 0;
                        if ($deliveryTaxFreeFl == 't') {
                            // 배송비 과세처리
                            $taxDeliveryCharge['supply'] = $tmpDeliveryTaxPrice['supply'];
                            $taxDeliveryCharge['tax'] = $tmpDeliveryTaxPrice['tax'];
                        } else {
                            // 배송비 면세처리
                            $taxDeliveryCharge['free'] = $tmpDeliveryTaxPrice['supply'];
                        }

                        if($this->multiShippingOrderFl !== true){
                            $deliveryInfo = [
                                'orderNo' => $orderNo,
                                'scmNo' => $sKey,
                                'commission' => $deliveryPolicy[$dKey]['scmCommissionDelivery'],
                                'deliverySno' => $dKey,
                                'deliveryCharge' => $deliveryCharge,
                                'taxSupplyDeliveryCharge' => $taxDeliveryCharge['supply'],
                                'taxVatDeliveryCharge' => $taxDeliveryCharge['tax'],
                                'taxFreeDeliveryCharge' => $taxDeliveryCharge['free'],
                                'realTaxSupplyDeliveryCharge' => $taxDeliveryCharge['supply'],
                                'realTaxVatDeliveryCharge' => $taxDeliveryCharge['tax'],
                                'realTaxFreeDeliveryCharge' => $taxDeliveryCharge['free'],
                                'deliveryPolicyCharge' => $cartAdmin->totalGoodsDeliveryPolicyCharge[$dKey],
                                'deliveryAreaCharge' => $cartAdmin->totalGoodsDeliveryAreaPrice[$dKey],
                                'deliveryFixFl' => $gVal['goodsDeliveryFixFl'],
                                'deliveryInsuranceFee' => 0,//$cartAdmin->totalDeliveryInsuranceFee
                                'goodsDeliveryFl' => $gVal['goodsDeliveryFl'],
                                'deliveryTaxInfo' => $deliveryTaxFreeFl . STR_DIVISION . $deliveryTaxPercent,
                                'deliveryWeightInfo' => 0,
                                'deliveryPolicy' => json_encode($deliveryPolicy[$dKey], JSON_UNESCAPED_UNICODE),
                                'overseasDeliveryPolicy' => json_encode($overseasDeliveryPolicy, JSON_UNESCAPED_UNICODE),
                                'deliveryCollectFl' => $gVal['goodsDeliveryCollectFl'],
                                'deliveryCollectPrice' => 0,
                                // 배송비조건별인 경우만 금액을 넣는다.
                                'deliveryMethod' => $gVal['goodsDeliveryMethod'],
                                'deliveryWholeFreeFl' => $gVal['goodsDeliveryWholeFreeFl'],
                                'deliveryWholeFreePrice' => 0,
                                // 배송비 조건별/상품별에 따라서 금액을 받아온다.
                                'deliveryLog' => '',
                            ];

                            // !중요!
                            // 해외배송은 설정에 따라서 무조건 하나의 배송비조건만 가지고 계산된다.
                            // 따라서 공급사의 경우 기본적으로 공급사마다 별도의 배송비조건을 가지게 되기때문에 아래와 같이
                            // 본사/공급사 구분없이 최초 배송비조건만 할당하고 나머지 배송비는 0원으로 처리해 이를 처리한다.
                            if ($originOrderData['mallSno'] != 1 && $onlyOneOverseasDelivery === true) {
                                $deliveryInfo['deliveryCharge'] = 0;
                                $deliveryInfo['taxSupplyDeliveryCharge'] = 0;
                                $deliveryInfo['taxVatDeliveryCharge'] = 0;
                                $deliveryInfo['taxFreeDeliveryCharge'] = 0;
                                $deliveryInfo['realTaxSupplyDeliveryCharge'] = 0;
                                $deliveryInfo['realTaxVatDeliveryCharge'] = 0;
                                $deliveryInfo['realTaxFreeDeliveryCharge'] = 0;
                                $deliveryInfo['deliveryPolicyCharge'] = 0;
                                $deliveryInfo['deliveryAreaCharge'] = 0;
                                $deliveryInfo['divisionDeliveryUseDeposit'] = 0;
                                $deliveryInfo['divisionDeliveryUseMileage'] = 0;
                                $deliveryInfo['divisionDeliveryCharge'] = 0;
                                $deliveryInfo['deliveryInsuranceFee'] = 0;
                                $deliveryInfo['deliveryCollectPrice'] = 0;
                                $deliveryInfo['deliveryWholeFreePrice'] = 0;
                            }

                            // 배송비 추가 안함
                            if ($addData['addDeliveryFl'][$dKey] == 'n') {
                                $deliveryInfo['deliveryCharge'] = 0;
                                $deliveryInfo['taxSupplyDeliveryCharge'] = 0;
                                $deliveryInfo['taxVatDeliveryCharge'] = 0;
                                $deliveryInfo['taxFreeDeliveryCharge'] = 0;
                                $deliveryInfo['realTaxSupplyDeliveryCharge'] = 0;
                                $deliveryInfo['realTaxVatDeliveryCharge'] = 0;
                                $deliveryInfo['realTaxFreeDeliveryCharge'] = 0;
                                $deliveryInfo['deliveryPolicyCharge'] = 0;
                                $deliveryInfo['deliveryAreaCharge'] = 0;
                                $deliveryInfo['divisionDeliveryUseDeposit'] = 0;
                                $deliveryInfo['divisionDeliveryUseMileage'] = 0;
                                $deliveryInfo['divisionDeliveryCharge'] = 0;
                                $deliveryInfo['deliveryInsuranceFee'] = 0;
                                $deliveryInfo['deliveryCollectPrice'] = 0;
                                $deliveryInfo['deliveryWholeFreePrice'] = 0;
                            } else {
                                $orderUpdatePrice['totalDeliveryCharge'] += $deliveryCharge;
                                $orderUpdatePrice['settlePrice'] += $deliveryInfo['deliveryCharge'];
                                $orderUpdatePrice['taxSupplyPrice'] += $deliveryInfo['taxSupplyDeliveryCharge'];
                                $orderUpdatePrice['taxVatPrice'] += $deliveryInfo['taxVatDeliveryCharge'];
                                $orderUpdatePrice['taxFreePrice'] += $deliveryInfo['taxFreeDeliveryCharge'];
                            }

                            // 정책별 배송 정보 저장
                            $arrBind = $this->db->get_binding(DBTableField::tableOrderDelivery(), $deliveryInfo, 'insert');
                            $this->db->set_insert_db(DB_ORDER_DELIVERY, $arrBind['param'], $arrBind['bind'], 'y', false);
                            $orderDeliverySno = $this->db->insert_id();
                            unset($arrBind);
                        }

                        // 한번만 실행
                        $onlyOneDelivery = false;
                        $onlyOneOverseasDelivery = true;
                    }

                    if (empty($orderDeliverySno) === false) {
                        $gVal['orderDeliverySno'] = $orderDeliverySno;
                    } else {
                        $gVal['orderDeliverySno'] = $orderMultiDeliverySno[$addData['orderInfoCdBySno'][$gVal['sno']]][$dKey];
                    }

                    // 옵션 설정
                    if (empty($gVal['option']) === true) {
                        $gVal['optionInfo'] = '';
                    } else {
                        foreach ($gVal['option'] as $oKey => $oVal) {
                            $tmp[] = [
                                $oVal['optionName'],
                                $oVal['optionValue'],
                                $oVal['optionCode'],
                                floatval($oVal['optionPrice']),
                                $oVal['optionDeliveryStr'],
                            ];
                        }
                        $gVal['optionInfo'] = json_encode($tmp, JSON_UNESCAPED_UNICODE);
                        unset($tmp);
                    }

                    // 텍스트 옵션
                    if (empty($gVal['optionText']) === true) {
                        $gVal['optionTextInfo'] = '';
                    } else {
                        foreach ($gVal['optionText'] as $oKey => $oVal) {
                            $tmp[$oVal['optionSno']] = [
                                $oVal['optionName'],
                                $oVal['optionValue'],
                                floatval($oVal['optionTextPrice']),
                            ];
                        }
                        $gVal['optionTextInfo'] = json_encode($tmp, JSON_UNESCAPED_UNICODE);
                        unset($tmp);
                    }
                    // 상품할인정보
                    if (empty($gVal['goodsDiscountInfo']) === true) {
                        $gVal['goodsDiscountInfo'] = '';
                    } else {
                        $gVal['goodsDiscountInfo'] = json_encode($gVal['goodsDiscountInfo'], JSON_UNESCAPED_UNICODE);
                    }
                    // 상품적립정보
                    if (empty($gVal['goodsMileageAddInfo']) === true) {
                        $gVal['goodsMileageAddInfo'] = '';
                    } else {
                        $gVal['goodsMileageAddInfo'] = json_encode($gVal['goodsMileageAddInfo'], JSON_UNESCAPED_UNICODE);
                    }

                    $gVal['enuri'] = $addData['enuri'][$gVal['sno'] . $gVal['goodsNo']];

                    $orderUpdatePrice['settlePrice'] += ($gVal['price']['goodsPriceSubtotal'] - $gVal['enuri']);

                    // 상품의 복합과세 금액 산출 및 주문상품에 저장할 필드 설정
                    $tmpGoodsTaxPrice = NumberUtils::taxAll($gVal['price']['goodsPriceSubtotal'] - $gVal['enuri'], $gVal['taxPercent'], $gVal['taxFreeFl']);
                    if ($gVal['taxFreeFl'] == 't') {
                        $gVal['taxSupplyGoodsPrice'] = $gVal['realTaxSupplyGoodsPrice'] = gd_isset($tmpGoodsTaxPrice['supply'], 0);
                        $gVal['taxVatGoodsPrice'] = $gVal['realTaxVatGoodsPrice'] = gd_isset($tmpGoodsTaxPrice['tax'], 0);
                        $orderUpdatePrice['taxSupplyPrice'] += $gVal['taxSupplyGoodsPrice'];
                        $orderUpdatePrice['taxVatPrice'] += $gVal['taxVatGoodsPrice'];
                    } else {
                        $gVal['taxFreeGoodsPrice'] = $gVal['realTaxFreeGoodsPrice'] = gd_isset($tmpGoodsTaxPrice['supply'], 0);
                        $orderUpdatePrice['taxFreePrice'] += $gVal['taxFreeGoodsPrice'];
                    }

                    if((int)$originOrderData['useMileage'] > 0){
                        $gVal['minusMileageFl'] = 'y';
                        $gVal['minusRestoreMileageFl'] = 'n';
                    }
                    if((int)$originOrderData['useDeposit'] > 0){
                        $gVal['minusDepositFl'] = 'y';
                        $gVal['minusRestoreDepositFl'] = 'n';
                    }

                    // 장바구니 상품 정보 저장
                    $arrBind = $this->db->get_binding(DBTableField::tableOrderGoods(), $gVal, 'insert');
                    $this->db->set_insert_db(DB_ORDER_GOODS, $arrBind['param'], $arrBind['bind'], 'y', false);

                    // 저장된 주문상품(order_goods) SNO 값
                    $arrOrderGoodsSno['sno'][] = $this->db->insert_id();

                    unset($arrBind);

                    // 주문 로그 저장
                    $order->orderLog($gVal['orderNo'], $this->db->insert_id(), null, $order->getOrderStatusAdmin($gVal['orderStatus']) . '(' . $gVal['orderStatus'] . ')', '상품추가');
                }
            }
        }

        // 재고차감 (사은품, 추가상품, 상품)
        // 주문테이블내 저장된 주문상태 정보 가져오기
        $currentStatusPolicy = $order->getOrderCurrentStatusPolicy($orderNo);
        if (in_array('o', $currentStatusPolicy['sminus'])) {
            $order->setGoodsStockCutback($orderNo, $arrOrderGoodsSno['sno']);
        }

        // 주문 금액 업데이트를 위한 주문 상품과 주문 배송비 금액 가져오기
        $changeOrderNm = $this->getOrderNameChange($orderNo, false);
        $updateAddOrderData['orderGoodsNm'] = $changeOrderNm;
        $updateAddOrderData['orderGoodsNmStandard'] = $this->getOrderNameChange($orderNo, true);
        $updateAddOrderData['orderGoodsCnt'] = $originOrderData['orderGoodsCnt'] + count($arrOrderGoodsSno['sno']);
        $updateAddOrderData['settlePrice'] = $originOrderData['settlePrice'] + $orderUpdatePrice['settlePrice'];
        $updateAddOrderData['taxSupplyPrice'] = $originOrderData['taxSupplyPrice'] + $orderUpdatePrice['taxSupplyPrice'];
        $updateAddOrderData['taxVatPrice'] = $originOrderData['taxVatPrice'] + $orderUpdatePrice['taxVatPrice'];
        $updateAddOrderData['taxFreePrice'] = $originOrderData['taxFreePrice'] + $orderUpdatePrice['taxFreePrice'];
        $updateAddOrderData['realTaxSupplyPrice'] = $originOrderData['realTaxSupplyPrice'] + $orderUpdatePrice['taxSupplyPrice'];
        $updateAddOrderData['realTaxVatPrice'] = $originOrderData['realTaxVatPrice'] + $orderUpdatePrice['taxVatPrice'];
        $updateAddOrderData['realTaxFreePrice'] = $originOrderData['realTaxFreePrice'] + $orderUpdatePrice['taxFreePrice'];
        $updateAddOrderData['totalGoodsPrice'] = $originOrderData['totalGoodsPrice'] + $orderUpdatePrice['totalGoodsPrice'];
        $updateAddOrderData['totalDeliveryCharge'] = $originOrderData['totalDeliveryCharge'] + $orderUpdatePrice['totalDeliveryCharge'];
        $updateAddOrderData['totalDeliveryInsuranceFee'] = $originOrderData['totalDeliveryInsuranceFee'] + $orderUpdatePrice['totalDeliveryInsuranceFee'];
        $updateAddOrderData['totalGoodsDcPrice'] = $originOrderData['totalGoodsDcPrice'] + $orderUpdatePrice['totalGoodsDcPrice'];
        $updateAddOrderData['totalMileage'] = $originOrderData['totalMileage'] + $orderUpdatePrice['totalGoodsMileage'];
        $updateAddOrderData['totalGoodsMileage'] = $originOrderData['totalGoodsMileage'] + $orderUpdatePrice['totalGoodsMileage'];
        $updateAddOrderData['totalEnuriDcPrice'] = $originOrderData['totalEnuriDcPrice'] + (int)array_sum($addData['enuri']); //총 운영자 추가 할인 금액

        // 결제 금액 검사
        if ($addData['settle'] != $orderUpdatePrice['settlePrice']) {
            throw new Exception(__('결제금액이 맞지 않습니다.'));
        }

        // 주문 update
        $taxPrice = $updateAddOrderData['taxSupplyPrice'] + $updateAddOrderData['taxVatPrice'] + $updateAddOrderData['taxFreePrice'];
        if ($updateAddOrderData['settlePrice'] != $taxPrice) {
            throw new Exception(__('결제금액이 맞지 않습니다.(세금)'));
        }

        // 현금영수증 저장 설정 - 세금계산서는 신청시 금액 안들어감 - 발행시 들어감
        if ($originOrderData['receiptFl'] == 'r') {
            // 주문서 저장 설정
            $receipt['requestGoodsNm'] = $changeOrderNm;
            $receipt['settlePrice'] = $taxPrice;
            $receipt['supplyPrice'] = $updateAddOrderData['taxSupplyPrice'];
            $receipt['taxPrice'] = $updateAddOrderData['taxVatPrice'];
            $receipt['freePrice'] = $updateAddOrderData['taxFreePrice'];

            // TODO: 현금영수증이 발급요청이고 + 주문상태가 입금대기상태의 주문이 있을경우 발급금액 자동 변경 by sueun
            $orderCashReceiptData = $this->getOrderCashReceiptData($orderNo);
            // 현금영수증 저장
            if(empty($orderCashReceiptData) === false) {
                $compareField = array_keys($receipt);
                $arrBind = $this->db->get_binding(DBTableField::tableOrderCashReceipt(), $receipt, 'update', $compareField);
                $arrWhere = 'orderNo = ?';
                $this->db->bind_param_push($arrBind['bind'], 's', $orderNo);
                $this->db->set_update_db(DB_ORDER_CASH_RECEIPT, $arrBind['param'], $arrWhere, $arrBind['bind']);
                unset($arrBind, $receipt);
            }
        }

        $compareField = array_keys($updateAddOrderData);
        $arrBind = $this->db->get_binding(DBTableField::tableOrder(), $updateAddOrderData, 'update', $compareField);
        $arrWhere = 'orderNo = ?';
        $this->db->bind_param_push($arrBind['bind'], 's', $orderNo);
        $this->db->set_update_db(DB_ORDER, $arrBind['param'], $arrWhere, $arrBind['bind']);
        unset($arrBind, $updateData);

        //pay history 로그기록
        $claimType = 'ag';
        $claimMemo = '상품추가';
        $processLogData = $this->getOrderProcessLogArrData($orderNo, $claimType, $claimMemo, $updateAddOrderData);
        $this->setOrderProcessLog($processLogData);

        return true;
    }

    /**
     * setBackRefundOrderGoods
     * 반품 접수 / 환불 접수 처리
     * @todo 취소 / 추가 / 교환 은 신규로 최종 결제금액이 변경되는데 반품/환불은??
     *
     * @param $arrData
     * @param $changeStatusName
     * @param $isChargeBack
     * @throws Exception
     */
    public function setBackRefundOrderGoods($arrData, $changeStatusName, $isChargeBack = false)
    {
        $dbUrl = \App::load('\\Component\\Marketing\\DBUrl');
        $paycoConfig = $dbUrl->getConfig('payco', 'config');

        // 운영자 기능권한 처리 (주문 상태 권한) - 관리자페이지에서만
        $thisCallController = \App::getInstance('ControllerNameResolver')->getControllerRootDirectory();
        if ($thisCallController == 'admin' && Session::get('manager.functionAuthState') == 'check' && Session::get('manager.functionAuth.orderState') != 'y') {
            throw new Exception(__('권한이 없습니다. 권한은 대표운영자에게 문의하시기 바랍니다.'));
        }
        $bundleData = [];
        $methodType = $changeStatusName;
        $handleData = $arrData[$methodType];
        $handleMode = substr($methodType, 0, 1);// 주문상태 prefix
        $changeStatus = $handleMode . '1'; // 주문 상태 설정

        $orderAdmin = App::load(\Component\Order\OrderAdmin::class);

        // 차지백 서비스로 환불접수하는 경우 현재 상태에 대한 변경 가능 상태 기준표에 환불 추가
        if ($isChargeBack === true) {
            // 현재가 배송 인경우 => 배송, 상품, 반품, 교환
            if (in_array('r', $orderAdmin->statusStandardCode['d']) === false) {
                $orderAdmin->statusStandardCode['d'][] = 'r';
            }

            // 현재가 구매확정 인경우 => 배송, 상품, 반품, 교환
            if (in_array('r', $orderAdmin->statusStandardCode['s']) === false) {
                $orderAdmin->statusStandardCode['s'][] = 'r';
            }

            // 현재가 교환 인경우 => 교환
            if (in_array('r', $orderAdmin->statusStandardCode['e']) === false) {
                $orderAdmin->statusStandardCode['e'][] = 'r';
            }

            // 현재가 교환추가 인경우 => 교환추가
            if (in_array('r', $orderAdmin->statusStandardCode['z']) === false) {
                $orderAdmin->statusStandardCode['z'][] = 'r';
            }
        }

        // 체크된 것과 현재상태에 따른 처리 가능한 주문 상품 상태 배열 처리
        foreach ($orderAdmin->statusStandardCode as $cKey => $cVal) {
            if (in_array($handleMode, $cVal)) {
                $checkCode[] = $cKey;
                continue;
            }
        }

        // 처리 가능한 상태 배열이 있는경우 체크된 상품별 상태에 따라 처리할 데이타 추출
        if (isset($checkCode) && empty($arrData[$methodType]['statusCheck']) === false) {
            foreach ($arrData[$methodType]['statusCheck'] as $key => $val) {
                $chkStatus = $arrData[$methodType]['statusMode'][$key];//현 상품의 주문상태

                // 현재 상태와 동일하지 않은 경우 처리할 수 있도록 걸러냄
                if (in_array(substr($chkStatus, 0, 1), $checkCode) && $chkStatus != $changeStatus) {
                    $bundleData['sno'][] = $val;
                    $bundleData['orderStatus'][] = $chkStatus;
                }
            }
        }

        // 각 상태별 처리 및 취소 테이블 등록
        if (!empty($bundleData)) {
            // 상태 변경 처리
            $funcName = 'statusChangeCode' . strtoupper($handleMode); // 처리할 함수명
            $bundleData['changeStatus'] = $changeStatus;
            $bundleData['reason'] = $arrData[$methodType]['handleReason'] . STR_DIVISION . $arrData[$methodType]['detailReason'];//'주문상세에서'

            /**
             * 주문상세에서 클레임 처리 (환불,반품)
             * 네이버페이는 api로 처리한후 후처리는 스킵 / 중앙서버로 주문동기화처리
             */
            $orderData = $this->getOrderData($arrData['orderNo']);
            if ($orderData['orderChannelFl'] == 'naverpay') {
                if ($handleMode == 'r') { //네이버페이인경우 환불 접수 시 바로 환불완료
                    $bundleData['changeStatus'] = 'r3';
                }
                $naverPay = new NaverPay();
                $naverpayApi = new NaverPayAPI();

                if ($methodType == 'refund' || $methodType == 'back') {
                    $arrData[$methodType]['handleReasonCode'] = $arrData[$methodType]['handleReason'];
                    $arrData[$methodType]['handleReason'] = $naverPay->getClaimReasonCode($arrData[$methodType]['handleReason']);
                }
                for ($i = count($bundleData['sno']) - 1; $i >= 0; $i--) {
                    if ($arrData[$methodType]['goodsType'][$bundleData['sno'][$i]] == 'goods') {
                        $addGoodsList = $orderAdmin->getChildAddGoods($arrData['orderNo'], $bundleData['sno'][$i], ['orderStatus' => ['p1', 'g1', 'g2', 'd1', 'd2']]);
                        foreach ($addGoodsList as $val) {
                            if (in_array($val['sno'], $bundleData['sno']) === false) {
                                throw new Exception(__('추가상품부터 환불/반품/교환 하시기 바랍니다.'));
                                break;
                            }
                        }
                    }
                }

                //추가상품을 위한 순서변경 >추가상품이 있으면 추가상품부터 취소 후 본상품 취소
                for ($i = count($bundleData['sno']) - 1; $i >= 0; $i--) {
                    if ($bundleData['orderStatus'][$i] == $bundleData['changeStatus']) {
                        continue;
                    }

                    $result = $naverpayApi->changeStatus($arrData['orderNo'], $bundleData['sno'][$i], $bundleData['changeStatus'], $arrData);
                    if ($result['error']) {
                        throw new Exception($result['error']);
                    } else {
                        echo 'ok';
                    }
                }

                return true;
            }

            // 주문상태 변경에 따른 콜백 함수 처리
            $orderAdmin->$funcName($arrData['orderNo'], $bundleData, false);

            // 환불/반품/교환 접수 처리
            if ($methodType != 'cancel') {
                $beforeStatus = $bundleData['orderStatus'][0];
                $handleData['handler'] = 'admin';

                foreach ($bundleData['sno'] as $orderGoodsSno) {
                    $newOrderGoodsData = $orderAdmin->setHandleAccept($arrData['orderNo'], [$orderGoodsSno], $handleMode, $handleData, $beforeStatus);

                    // 주문상품이 새로 생성됐을시, 클레임신청 정보 수정
                    if (is_array($newOrderGoodsData) === true && empty($newOrderGoodsData['userHandleGoodsNo']) === false) {
                        $bundleData['userHandleGoodsNo'] = $newOrderGoodsData['userHandleGoodsNo'];
                        $bundleData['orderGoodsNo'] = $orderGoodsSno;
                        $orderAdmin->updateUserHandle($bundleData);
                        unset($newOrderGoodsData);
                    }
                }
            }
        }

        if ($paycoConfig['paycoFl'] == 'y') {
            // 페이코쇼핑 결제데이터 전달
            $payco = \App::load('\\Component\\Payment\\Payco\\Payco');
            $payco->paycoShoppingRequest($arrData['orderNo']);
        }
    }

    /**
     * order handle insert
     *
     * @param array $arrData
     * @return integer $insertSno
     */
    public function setOrderHandle($arrData)
    {
        $arrBind = [];
        if (empty($arrData['refundAccountNumber']) === false) {
            $arrData['refundAccountNumber'] = \Encryptor::encrypt($arrData['refundAccountNumber']);
        }
        $compareField = array_keys($arrData);
        $arrBind = $this->db->get_binding(DBTableField::tableOrderHandle(), $arrData, 'insert', $compareField);
        $this->db->set_insert_db(DB_ORDER_HANDLE, $arrBind['param'], $arrBind['bind'], 'y');
        $insertSno = $this->db->insert_id();
        unset($arrBind);

        return $insertSno;
    }

    /*
     * update order handle
     *
     * @param array $updateData 업데이트될 데이터
     * @param integer $handleSno handle sno
     *
     * @return integer $affectedRows
     */
    public function updateOrderHandle($updateData, $handleSno)
    {
        if (empty($updateData['refundAccountNumber']) === false) {
            $updateData['refundAccountNumber'] = \Encryptor::encrypt($updateData['refundAccountNumber']);
        }
        $compareField = array_keys($updateData);
        $arrBind = $this->db->get_binding(DBTableField::tableOrderHandle(), $updateData, 'update', $compareField);
        $arrWhere = 'sno = ?';
        $this->db->bind_param_push($arrBind['bind'], 'i', $handleSno);
        $affectedRows = $this->db->set_update_db(DB_ORDER_HANDLE, $arrBind['param'], $arrWhere, $arrBind['bind']);
        unset($arrBind, $updateData);

        return $affectedRows;
    }

    /*
     * handle 테이블의 최대 handleGroupCd 조회. 한번의 클레임 처리는 handleGroupCd 로 묶인다.
     *
     * @param string $orderNo 주문 번호
     *
     * @return string $returnType 리턴할 타입
     */
    public function getMaxHandleGroupCd($orderNo, $returnType = '')
    {
        // 쿼리 조건
        $arrBind = [];
        $this->db->strField = 'max(handleGroupCd) as max';
        $this->db->strWhere = 'orderNo = ?';
        $this->db->bind_param_push($arrBind, 's', $orderNo);

        // 쿼리 생성
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_HANDLE . ' ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind, false);
        if (gd_str_length($getData['refundAccountNumber']) > 50) {
            $getData['refundAccountNumber'] = \Encryptor::decrypt($getData['refundAccountNumber']);
        }
        unset($arrBind);

        if (empty($getData['max']) === false) {
            $returnMaxCode = (int)$getData['max'];
        } else {
            $returnMaxCode = 0;
        }

        if ($returnType === 'next') {
            $returnMaxCode += 1;
        }

        return $returnMaxCode;
    }

    /*
     * 맞교환
     *
     * @param array $postData post value
     */
    public function setSameExchangeOrderGoods($postData)
    {
        $order = App::load(\Component\Order\Order::class);
        $goodsObj = App::load(\Component\Goods\Goods::class);
        $currentStatusPolicy = $order->getOrderCurrentStatusPolicy($postData['orderNo']);

        $claimStatus = 'e';

        $tmpOrderGoodsSnoArr = $tmpOrderGoodsCntArr = $orderGoodsData = $orderGoodsCntArr = $orderGoodsSnoArr = $orderGoodsStatusArr = [];
        $minusMileageData = $tmpChangeOptionArr = $addOrderGoodsSnoArr = [];
        $returnGiftFl = false;
        $changeStatus = $this->setAddExchangeOrderGoodsStatus(0);

        $tmpOrderGoodsSnoArr = explode(INT_DIVISION, $postData['orderGoodsSno']);
        $tmpOrderGoodsCntArr = explode(INT_DIVISION, $postData['orderGoodsCnt']);
        $tmpChangeOptionArr = explode(STR_DIVISION, $postData['changeOption']);

        //주문데이터
        $originalOrderData = $this->getOrderData($postData['orderNo']);

        //해당 주문건의 데이터 전체 로드 - array[주문상품sno] 의 형식으로 재배열
        $tmpOrderGoodsData = $order->getOrderGoodsData($postData['orderNo']);
        if (count($tmpOrderGoodsData) > 0) {
            foreach ($tmpOrderGoodsData as $scmNo => $dataVal) {
                foreach ($dataVal as $goodsData) {
                    $orderGoodsData[$goodsData['sno']] = $goodsData;
                }
            }
        }
        // 변경되는 옵션의 정보 key - orderGoodsSNo, value - optionSno
        if(count($tmpChangeOptionArr) > 0){
            foreach($tmpChangeOptionArr as $key => $value){
                list($orderGoodsSno, $optionSno) = explode(INT_DIVISION, $value);
                $changeOptionSnoArr[$orderGoodsSno] = $optionSno;
            }
            unset($tmpChangeOptionArr, $orderGoodsSno, $optionSno);
        }

        //교환전의 주문상품 데이터가 통계처리 되지 않았다면 통계처리
        $isStatistics = true;
        $isStatistics = $this->checkStatistics($orderGoodsData, $tmpOrderGoodsSnoArr);
        if($isStatistics === false){
            $orderSalesStatistics = new OrderSalesStatistics();
            $orderSalesStatistics->realTimeStatistics(true);
        }

        //교환된 주문상품의 상품sno, 상품개수, 상태값 재배열 - array[주문상품sno] 의 형식으로 재배열
        if (count($tmpOrderGoodsSnoArr) > 0) {
            foreach ($tmpOrderGoodsSnoArr as $key => $orderGoodsSno) {
                $orderGoodsSnoArr[$orderGoodsSno] = $orderGoodsSno;
                $orderGoodsCntArr[$orderGoodsSno] = $tmpOrderGoodsCntArr[$key];
                $orderGoodsStatusArr[$orderGoodsSno] = $orderGoodsData[$orderGoodsSno]['orderStatus'];
            }
        }

        //처리할 건이 있는지 확인
        if ($orderGoodsStatusArr > 0) {
            foreach ($orderGoodsStatusArr as $key => $status) {
                $statusPrefix = substr($status, 0, 1);
                if ($statusPrefix == 'e' || $statusPrefix == 'z') {
                    unset($orderGoodsStatusArr[$key], $orderGoodsSnoArr[$key], $orderGoodsCntArr[$key]);
                }
            }
        }
        if (count($orderGoodsSnoArr) < 1) {
            throw new Exception(__('이미 교환 처리 된 주문입니다.'));
        }

        //original data 체크 (같은 status 존재 여부)
        $count = $this->getOrderOriginalCount($postData['orderNo'], $claimStatus);
        if ($count < 1) {
            //이전 교환건 존재하지 않을 시 현재 주문정보 백업
            $this->setBackupOrderOriginalData($postData['orderNo'], $claimStatus, true, false, true);
        }

        // orderCd 최대 값 불러오기
        $orderCd = $this->getOrderGoodsMaxOrderCd($postData['orderNo']);

        //handleGroupCd 최대값 조회 - 다음 사용될 값 리턴
        $nextHandleGroupCd = $this->getMaxHandleGroupCd($postData['orderNo'], 'next');

        foreach ($orderGoodsSnoArr as $key => $orderGoodsSno) {
            $orderGoodsCnt = 0;

            //옵션변경여부
            $optionChangeFl = false;
            $changeOptionData = [];
            if((int)$changeOptionSnoArr[$orderGoodsSno] > 0){
                if((int)$changeOptionSnoArr[$orderGoodsSno] !== (int)$orderGoodsData[$orderGoodsSno]['optionSno']){
                    $optionChangeFl = true;
                    $changeOptionData = $this->getChangeOptionData($changeOptionSnoArr[$orderGoodsSno], $orderGoodsData[$orderGoodsSno]['goodsNo'], $goodsObj);
                }
            }

            //handle 데이터 인서트
            $handleData = [
                'orderNo' => $postData['orderNo'],
                'beforeStatus' => $orderGoodsData[$orderGoodsSno]['orderStatus'],
                'handleMode' => $claimStatus,
                'handler' => 'admin',
                'handleCompleteFl' => 'n',
                'handleReason' => $postData['handleReason'],
                'handleDetailReason' => $postData['handleDetailReason'],
                'handleDetailReasonShowFl' => $postData['handleDetailReasonShowFl'] ?? 'n',
                'handleGroupCd' => $nextHandleGroupCd,
            ];
            $handleSno = $this->setOrderHandle($handleData);

            //교환하려는 주문상품의 전체수량 - 교환처리 할 수량
            $differentGoodsCnt = (int)$orderGoodsData[$orderGoodsSno]['goodsCnt'] - (int)$orderGoodsCntArr[$orderGoodsSno];
            if ($differentGoodsCnt > 0) {
                // --- 일부 수량만 교환일 경우

                //교환취소 상품 생성 - 기존 주문상품건을 기준으로 새로운 주문상품건 생성 (e 값을 가지는 교환 취소 주문상품)
                $newOrderGoodsSno = $this->copyOrderGoodsData($postData['orderNo'], $orderGoodsSno, 'e1', $handleSno, $orderGoodsCntArr[$orderGoodsSno], null, ++$orderCd);

                //기존주문상품 데이터 업데이트 (안분된 금액)
                $updateOrderGoodsData = $this->getOrderGoodsRecalculateData($orderGoodsData[$orderGoodsSno], $differentGoodsCnt);
                $this->updateOrderGoods($updateOrderGoodsData, $postData['orderNo'], $orderGoodsSno);

                //교환취소 주문상품의 안분(통계 환불금액에서 사용)
                $updateOrderGoodsCancelData = $this->getOrderGoodsCancelUpdateData($orderGoodsData[$orderGoodsSno], $updateOrderGoodsData);
                $this->updateOrderGoods($updateOrderGoodsCancelData, $postData['orderNo'], $newOrderGoodsSno);

                $logOrderGoodsSno = $newOrderGoodsSno;
                $orderGoodsCnt++;

                // --- 일부 수량만 교환일 경우
            } else {
                // --- 전체 수량 교환일 경우

                //교환취소 상품 생성 - 기존 주문상품건을 상태값 변동 (e 값을 가지는 교환 취소 주문상품)
                $this->updateOrderGoods(['handleSno' => $handleSno, 'orderStatus' => 'e1'], $postData['orderNo'], $orderGoodsSno);
                $logOrderGoodsSno = $orderGoodsSno;

                // --- 전체 수량 교환일 경우
            }
            //로그 기록 - 교환 취소 상품
            $this->setOrderLog($postData['orderNo'], $logOrderGoodsSno, $orderGoodsData[$orderGoodsSno]['orderStatus'], 'e1', $message = '상세에서 교환접수 (주문상품번호:'.$logOrderGoodsSno.')');

            //교환추가상품 handleSno 인서트
            $handleData['handleMode'] = 'z';
            $handleData['handleReason'] = $handleData['handleDetailReason'] = '교환추가상품';
            $handleAddSno = $this->setOrderHandle($handleData);

            if ($differentGoodsCnt > 0) {
                //일부 수량만 교환일 경우 - 변경하려는 수량으로 새로운 교환추가 주문상품건 생성

                $updateOrderGoodsData = $this->getSameOrderGoodsRecalculateData($postData['orderNo'], $orderGoodsData[$orderGoodsSno], $orderGoodsSno);

                // 상품/회원 마일리지를 지급안함
                if($postData['supplyMileage'] === 'n'){
                    $minusMileageData[$orderGoodsSno]['goodsMileage'] = $updateOrderGoodsData['goodsMileage'] = 0;
                    $minusMileageData[$orderGoodsSno]['memberMileage'] = $updateOrderGoodsData['memberMileage'] = 0;
                }

                // 쿠폰 마일리지를 지급 안함
                if($postData['supplyCouponMileage'] === 'n'){
                    $minusMileageData[$orderGoodsSno]['couponGoodsMileage'] = $updateOrderGoodsData['couponGoodsMileage'] = 0;

                    // 적립될 상품쿠폰 마일리지가 있고, 적립되지 않은 상태일시 리셋 처리
                    if((int)$orderGoodsData[$orderGoodsSno]['couponGoodsMileage'] > 0){
                        $orderProductCouponData = [];
                        $orderProductCouponData = $this->getProductCouponData($postData['orderNo'], $orderGoodsData[$orderGoodsSno]['orderCd']);

                        //상품 쿠폰적립을 하지 않았다면
                        if(count($orderProductCouponData) > 0) {
                            $this->changeProductCouponMileage($postData['orderNo'], $orderGoodsData[$orderGoodsSno]['orderCd'], $orderGoodsData[$orderGoodsSno]['goodsNo'], ((int)$orderGoodsData[$orderGoodsSno]['couponGoodsMileage']-(int)$minusMileageData[$orderGoodsSno]['couponGoodsMileage']));

                            unset($orderProductCouponData);
                        }
                    }
                }

                $updateOrderGoodsData = $this->getAddExchangeOrderGoodsEtcData($updateOrderGoodsData, $changeStatus);
                $updateOrderGoodsData = $this->getAddExchangeOrderGoodsOptionData($updateOrderGoodsData, $optionChangeFl, $changeOptionData);

                // 교환추가상품 생성
                $addOrderGoodsSno = $this->copyOrderGoodsData($postData['orderNo'], $orderGoodsSno, $changeStatus, $handleAddSno, null, null, ++$orderCd, $updateOrderGoodsData);

                // 교환취소상품 마일리지/예치금 초기화 (동일 상품 교환의 경우 교환 완료 시 마일리지/예치금 환불 해주면 안됨)
                $updateOrderGoodsData = [
                    'divisionUseMileage' => 0,
                    'divisionGoodsDeliveryUseMileage' => 0,
                    'divisionUseDeposit' => 0,
                    'divisionGoodsDeliveryUseDeposit' => 0,
                ];
                $this->updateOrderGoods($updateOrderGoodsData, $postData['orderNo'], $logOrderGoodsSno);
                unset($updateOrderGoodsData);
            } else {
                $updateOrderGoodsData = [];

                // 상품/회원 마일리지를 지급안함
                if($postData['supplyMileage'] === 'n'){
                    $minusMileageData[$orderGoodsSno]['goodsMileage'] = $updateOrderGoodsData['goodsMileage'] = 0;
                    $minusMileageData[$orderGoodsSno]['memberMileage'] = $updateOrderGoodsData['memberMileage'] = 0;
                }

                // 쿠폰 마일리지를 지급 안함
                if($postData['supplyCouponMileage'] === 'n'){
                    $minusMileageData[$orderGoodsSno]['couponGoodsMileage'] = $updateOrderGoodsData['couponGoodsMileage'] = 0;

                    // 적립될 상품쿠폰 마일리지가 있고, 적립되지 않은 상태일시 리셋 처리
                    if((int)$orderGoodsData[$orderGoodsSno]['couponGoodsMileage'] > 0){
                        $orderProductCouponData = [];
                        $orderProductCouponData = $this->getProductCouponData($postData['orderNo'], $orderGoodsData[$orderGoodsSno]['orderCd']);
                        //상품 쿠폰적립을 하지 않았다면
                        if(count($orderProductCouponData) > 0) {
                            $this->changeProductCouponMileage($postData['orderNo'], $orderGoodsData[$orderGoodsSno]['orderCd'], $orderGoodsData[$orderGoodsSno]['goodsNo'], 0);

                            unset($orderProductCouponData);
                        }
                    }
                }

                // 교환추가 시 송장정보 초기화
                $updateOrderGoodsData['invoiceNo'] = '';
                $updateOrderGoodsData['invoiceCompanySno'] = '';

                $updateOrderGoodsData = $this->getAddExchangeOrderGoodsEtcData($updateOrderGoodsData, $changeStatus);
                $updateOrderGoodsData = $this->getAddExchangeOrderGoodsOptionData($updateOrderGoodsData, $optionChangeFl, $changeOptionData);

                //전체 수량 교환일 경우 - 전체수량으로 새로운 교환추가 주문상품건 생성
                $addOrderGoodsSno = $this->copyOrderGoodsData($postData['orderNo'], $orderGoodsSno, $changeStatus, $handleAddSno, null, null, ++$orderCd, $updateOrderGoodsData);

                //교환취소상품 realTax 초기화
                $updateOrderGoodsData = [
                    'realTaxSupplyGoodsPrice' => 0,
                    'realTaxVatGoodsPrice' => 0,
                    'realTaxFreeGoodsPrice' => 0,
                    'divisionUseMileage' => 0,
                    'divisionGoodsDeliveryUseMileage' => 0,
                    'divisionUseDeposit' => 0,
                    'divisionGoodsDeliveryUseDeposit' => 0,
                ];
                $this->updateOrderGoods($updateOrderGoodsData, $postData['orderNo'], $orderGoodsSno);
                unset($updateOrderGoodsData);

                //쿠폰마일리지 지급함 일 시
                if($postData['supplyCouponMileage'] === 'y'){
                    //상품 마일리지 지급쿠폰의 orderCd 변환
                    $this->changeProductCouponOrderCd($postData['orderNo'], $orderGoodsData[$orderGoodsSno]['orderCd'], $orderCd);
                }
            }

            $addOrderGoodsSnoArr[] = $addOrderGoodsSno;

            //로그 기록 - 교환 추가 상품
            $this->setOrderLog($postData['orderNo'], $addOrderGoodsSno, $orderGoodsData[$orderGoodsSno]['orderStatus'], $changeStatus, $message = '상세에서 교환추가접수 (주문상품번호:'.$addOrderGoodsSno.')');
        }

        //처리되고 난 후 주문데이터
        $updateOrderData = [];
        $orderData = $this->getOrderData($postData['orderNo']);
        if((int)$originalOrderData['totalMileage'] > 0 && ($postData['supplyMileage'] === 'n' || $postData['supplyCouponMileage'] === 'n')){
            if(count($minusMileageData) > 0){
                $totalMinusMileage = [];
                $updateOrderData['totalMileage'] = $originalOrderData['totalMileage'];

                $totalMinusMileage['goodsMileage'] = (int)array_sum(array_column($minusMileageData, 'goodsMileage'));
                $totalMinusMileage['memberMileage'] = (int)array_sum(array_column($minusMileageData, 'memberMileage'));
                $totalMinusMileage['couponGoodsMileage'] = (int)array_sum(array_column($minusMileageData, 'couponGoodsMileage'));

                $orderData['totalMileage'] -= (int)array_sum($totalMinusMileage);
                $orderData['totalGoodsMileage'] -= (int)$totalMinusMileage['goodsMileage'];
                $orderData['totalMemberMileage'] -= (int)$totalMinusMileage['memberMileage'];
                $orderData['totalCouponGoodsMileage'] -= (int)$totalMinusMileage['couponGoodsMileage'];

                $updateOrderData['totalMileage'] = $orderData['totalMileage'];
                if($postData['supplyMileage'] === 'n'){
                    $updateOrderData['totalGoodsMileage'] = $orderData['totalGoodsMileage'];
                    $updateOrderData['totalMemberMileage'] = $orderData['totalMemberMileage'];
                }
                if($postData['supplyCouponMileage'] === 'n'){
                    $updateOrderData['totalCouponGoodsMileage'] = $orderData['totalCouponGoodsMileage'];
                }
            }
        }

        //주문 데이터 업데이트
        $orderGoodsCnt += (int)$orderData['orderGoodsCnt'];
        $updateOrderData['orderGoodsCnt'] = $orderGoodsCnt;
        $this->updateOrder($postData['orderNo'], 'y', $updateOrderData);

        // 재고차감
        if (in_array('p', $currentStatusPolicy['sminus'])) {
            $order->setGoodsStockCutback($postData['orderNo'], $addOrderGoodsSnoArr);
        }
        //마일리지 지급
        if (in_array('p', $currentStatusPolicy['mplus'])) {
            $order->setPlusMileageVariation($postData['orderNo'], ['sno' => $addOrderGoodsSnoArr, 'changeStatus' => $changeStatus]);
        }
        // 쿠폰 처리
        if ($postData['returnCouponFl'] == 'y') {
            foreach ($postData['returnCoupon'] as $memberCouponNo => $returnFl) {
                if ($returnFl == 'y') {
                    $coupon[] = $memberCouponNo;
                }
            }

            $this->setReturnCoupon($postData['orderNo'], $coupon, $claimStatus);
        }
        // 사은품 지급안함 처리
        if ($postData['returnGiftFl'] === 'n') {
            $giftData = [];
            foreach ($postData['returnGift'] as $orderGiftNo => $returnFl) {
                if ($returnFl == 'n') {
                    $giftData[] = $orderGiftNo;
                }
            }
            if(count($giftData) > 0){
                $this->setReturnGift($postData['orderNo'], $giftData, $claimStatus);
            }
            unset($giftData);
        }

        //pay history 로그기록
        $processLogData = $this->getOrderProcessLogArrData($postData['orderNo'], 'se', '동일상품교환', $orderData);
        $this->setOrderProcessLog($processLogData);

        return true;
    }

    /*
     * 사용자 교환 승인처리
     *
     * @param array $postData post value
     */
    public function setUserSameExchangeOrderGoods($postData)
    {
        $claimStatus = 'e';
        $order = App::load(\Component\Order\Order::class);
        $returnOrderGoodsSno = [];
        $changeStatus = $this->setAddExchangeOrderGoodsStatus(0);

        $orderSalesStatistics = new OrderSalesStatistics();

        foreach ($postData['statusCheck'] as $val) {
            $orderGoodsCnt = 0;
            $tmpOrderGoodsData = $orderGoodsData = $addOrderGoodsSnoArr = [];

            //주문번호, 주문상품번호, user handle sno, 교환된 주문상품갯수, 원래 주문상품 갯수
            list($orderNo, $orderGoodsSno, $userHandleSno, $goodsCnt, $goodsOriginalCnt) = explode(INT_DIVISION, $val);
            $currentStatusPolicy = $order->getOrderCurrentStatusPolicy($orderNo);
            $tmpOrderGoodsData = $order->getOrderGoodsData($orderNo);
            if (count($tmpOrderGoodsData) > 0) {
                foreach ($tmpOrderGoodsData as $scmNo => $dataVal) {
                    foreach ($dataVal as $goodsData) {
                        $orderGoodsData[$goodsData['sno']] = $goodsData;
                    }
                }

                //교환전의 주문상품 데이터가 통계처리 되지 않았다면 통계처리
                $isStatistics = true;
                $isStatistics = $this->checkStatistics($orderGoodsData, [$orderGoodsSno]);
                if($isStatistics === false){
                    $orderSalesStatistics->realTimeStatistics(true);
                }
            }

            //original data 체크 (같은 status 존재 여부)
            $count = $this->getOrderOriginalCount($orderNo, $claimStatus);
            if ($count < 1) {
                //이전 교환건 존재하지 않을 시 현재 주문정보 백업
                $this->setBackupOrderOriginalData($orderNo, $claimStatus, true, false, true);
            }
            // orderCd 최대 값 불러오기
            $orderCd = $this->getOrderGoodsMaxOrderCd($orderNo);

            //handleGroupCd 최대값 조회 - 다음 사용될 값 리턴
            $nextHandleGroupCd = $this->getMaxHandleGroupCd($orderNo, 'next');

            //handle 데이터 인서트
            $handleData = [
                'orderNo' => $orderNo,
                'beforeStatus' => $orderGoodsData[$orderGoodsSno]['orderStatus'],
                'handleMode' => $claimStatus,
                'handler' => 'admin',
                'handleCompleteFl' => 'n',
                'handleReason' => $orderGoodsData[$orderGoodsSno]['userHandleReason'],
                'handleDetailReason' => $orderGoodsData[$orderGoodsSno]['userHandleDetailReason'],
                'handleGroupCd' => $nextHandleGroupCd,
            ];
            $handleSno = $this->setOrderHandle($handleData);

            //교환하려는 주문상품의 전체수량 - 교환처리 할 수량
            $differentGoodsCnt = (int)$goodsOriginalCnt - (int)$goodsCnt;
            if ($differentGoodsCnt > 0) {
                // --- 일부 수량만 교환일 경우

                //교환취소 상품 생성 - 기존 주문상품건을 기준으로 새로운 주문상품건 생성 (e 값을 가지는 교환 취소 주문상품)
                $newOrderGoodsSno = $this->copyOrderGoodsData($orderNo, $orderGoodsSno, 'e1', $handleSno, $goodsCnt, $userHandleSno, ++$orderCd);

                //기존주문상품 데이터 업데이트 (안분된 금액)
                $updateOrderGoodsData = $this->getOrderGoodsRecalculateData($orderGoodsData[$orderGoodsSno], $differentGoodsCnt);
                $updateOrderGoodsData['userHandleSno'] = 0;
                $this->updateOrderGoods($updateOrderGoodsData, $orderNo, $orderGoodsSno);

                //교환취소 주문상품의 안분(통계 환불금액에서 사용)
                $updateOrderGoodsCancelData = $this->getOrderGoodsCancelUpdateData($orderGoodsData[$orderGoodsSno], $updateOrderGoodsData);
                $this->updateOrderGoods($updateOrderGoodsCancelData, $orderNo, $newOrderGoodsSno);

                //user handle 업데이트
                $this->updateOrderUserHandle(['userHandleGoodsNo' => $newOrderGoodsSno], $orderNo, $userHandleSno);

                $logOrderGoodsSno = $newOrderGoodsSno;
                $orderGoodsCnt++;

                // --- 일부 수량만 교환일 경우
            } else {
                // --- 전체 수량 교환일 경우

                //교환취소 상품 생성 - 기존 주문상품건을 상태값 변동 (e 값을 가지는 교환 취소 주문상품)
                $this->updateOrderGoods(['handleSno' => $handleSno, 'orderStatus' => 'e1'], $orderNo, $orderGoodsSno);
                $logOrderGoodsSno = $orderGoodsSno;

                // --- 전체 수량 교환일 경우
            }

            //로그 기록 - 교환 취소 상품
            $this->setOrderLog($orderNo, $logOrderGoodsSno, $orderGoodsData[$orderGoodsSno]['orderStatus'], 'e1', $message = '상세에서 교환접수 (주문상품번호:'.$logOrderGoodsSno.')');

            //SMS 자동발송을 위해 order goods sno 를 담아둔다.
            $returnOrderGoodsSno[] = $logOrderGoodsSno;

            //교환추가상품 handleSno 인서트
            $handleData['handleMode'] = 'z';
            $handleData['handleReason'] = $handleData['handleDetailReason'] = '교환추가상품';
            $handleAddSno = $this->setOrderHandle($handleData);

            if ($differentGoodsCnt > 0) {
                //일부 수량만 교환일 경우 - 변경하려는 수량으로 새로운 교환추가 주문상품건 생성

                $updateOrderGoodsData = $this->getSameOrderGoodsRecalculateData($orderNo, $orderGoodsData[$orderGoodsSno], $orderGoodsSno);
                $updateOrderGoodsData = $this->getAddExchangeOrderGoodsEtcData($updateOrderGoodsData, $changeStatus);
                $addOrderGoodsSno = $this->copyOrderGoodsData($orderNo, $orderGoodsSno, $changeStatus, $handleAddSno, null, null, ++$orderCd, $updateOrderGoodsData);
            } else {
                $updateOrderGoodsData = [];
                $updateOrderGoodsData = $this->getAddExchangeOrderGoodsEtcData($updateOrderGoodsData, $changeStatus);

                //전체 수량 교환일 경우 - 전체수량으로 새로운 교환추가 주문상품건 생성
                $addOrderGoodsSno = $this->copyOrderGoodsData($orderNo, $orderGoodsSno, $changeStatus, $handleAddSno, null, null, ++$orderCd, $updateOrderGoodsData);

                //교환취소상품 realTax 초기화
                $updateOrderGoodsData = [
                    'realTaxSupplyGoodsPrice' => 0,
                    'realTaxVatGoodsPrice' => 0,
                    'realTaxFreeGoodsPrice' => 0,
                ];
                $this->updateOrderGoods($updateOrderGoodsData, $orderNo, $orderGoodsSno);
                unset($updateOrderGoodsData);
            }
            $addOrderGoodsSnoArr[] = $addOrderGoodsSno;

            //로그 기록 - 교환 추가 상품
            $this->setOrderLog($orderNo, $addOrderGoodsSno, $orderGoodsData[$orderGoodsSno]['orderStatus'], $changeStatus, $message = '상세에서 교환추가접수 (주문상품번호:'.$addOrderGoodsSno.')');

            //user handle 업데이트
            $userHandleUpdate = [
                'userHandleFl' => 'y',
                'adminHandleReason' => $postData['adminHandleReason'],
            ];
            $this->updateOrderUserHandle($userHandleUpdate, $orderNo, $userHandleSno);

            //처리되고 난 후 주문데이터
            $orderData = $this->getOrderData($orderNo);

            //주문건 수량, 상태값 업데이트
            $orderGoodsCnt += (int)$orderData['orderGoodsCnt'];
            $this->updateOrder($orderNo, 'y', ['orderGoodsCnt' => $orderGoodsCnt]);

            // 재고차감
            if (in_array('p', $currentStatusPolicy['sminus'])) {
                $order->setGoodsStockCutback($orderNo, $addOrderGoodsSnoArr);
            }

            //pay history 로그기록
            $processLogData = $this->getOrderProcessLogArrData($orderNo, 'se', '동일상품맞교환', $orderData);
            $this->setOrderProcessLog($processLogData);
        }

        return $returnOrderGoodsSno;
    }

    /*
     * 타상품교환
     *
     * @param array $postData post value
     */
    public function setAnotherExchangeOrderGoods($postData)
    {
        // -- 사용될 데이터 선언 시작
        $order = App::load(\Component\Order\Order::class);
        // 주문테이블내 저장된 주문상태 정보 가져오기
        $currentStatusPolicy = $order->getOrderCurrentStatusPolicy($postData['orderNo']);

        $claimStatus = 'e';
        $tmpBeforeOrderGoodsSnoArr = $tmpBeforeOrderGoodsCntArr = $tmpOrderGoodsData = $orderGoodsData = $orderGoodsSnoArr = $orderGoodsCntArr = [];
        $multiShippingOrderInfoSnoArr = $updateEtcOrderGoodsData = $etcUpdateAddPament = [];
        $multiShippingOrderInfoSno = 0;
        $addPaymentDivisionArr = [
            'add' => [
                'price' => [],
                'rate' => [],
            ],
            'cancel' => [
                'price' => [],
                'rate' => [],
            ],
        ];

        $changeStatus = $this->setAddExchangeOrderGoodsStatus($postData['totalChangePrice']);

        //교환된 데이터 정리
        $tmpBeforeOrderGoodsSnoArr = explode(INT_DIVISION, $postData['beforeOrderGoodsSno']);
        $tmpBeforeOrderGoodsCntArr = explode(INT_DIVISION, $postData['beforeOrderGoodsCnt']);

        //해당 주문건의 데이터 전체 로드 - array[주문상품sno] 의 형식으로 재배열
        $tmpOrderGoodsData = $order->getOrderGoodsData($postData['orderNo']);

        if (count($tmpOrderGoodsData) > 0) {
            foreach ($tmpOrderGoodsData as $scmNo => $dataVal) {
                foreach ($dataVal as $goodsData) {
                    $orderGoodsData[$goodsData['sno']] = $goodsData;
                }
            }
        }

        //교환전의 주문상품 데이터가 통계처리 되지 않았다면 통계처리
        $isStatistics = true;
        $isStatistics = $this->checkStatistics($orderGoodsData, $tmpBeforeOrderGoodsSnoArr);
        if($isStatistics === false){
            $orderSalesStatistics = new OrderSalesStatistics();
            $orderSalesStatistics->realTimeStatistics(true);
        }

        //교환된 주문상품의 상품sno, 상품개수 재배열 - array[주문상품sno] 의 형식으로 재배열
        if (count($tmpBeforeOrderGoodsSnoArr) > 0) {
            foreach ($tmpBeforeOrderGoodsSnoArr as $key => $orderGoodsSno) {
                $orderGoodsSnoArr[$orderGoodsSno] = $orderGoodsSno;
                $orderGoodsCntArr[$orderGoodsSno] = $tmpBeforeOrderGoodsCntArr[$key];
                $multiShippingOrderInfoSnoArr[] = $orderGoodsData[$orderGoodsSno]['orderInfoSno'];
            }
        }

        //복수배송지가 사용된 주문건인지 체크
        if(in_array('y', array_column($orderGoodsData, 'multiShippingFl'))){
            $this->multiShippingOrderFl = true;
            $multiShippingOrderInfoSno = $multiShippingOrderInfoSnoArr[0];
            if(count(array_unique($multiShippingOrderInfoSnoArr)) > 1){
                throw new Exception('같은 배송지의 상품만 다른상품교환 처리가 가능합니다.');
            }
        }

        // 교환취소 배송비의 상태
        $this->cancelDeliveryFl = $postData['cancelDeliveryFl'];

        // --- 부가결제 금액 재분배를 위한 계산식 시작 (남은 부가결제 금액 교환추가 상품에 재분배, 교환취소 상품에 빠진만큼 재분배)
        $this->addPaymentDivisionFl = false;
        if((int)$postData['originalCancelDivisionUseMileage'] > 0){
            $this->addPaymentDivisionFl = true;
        }
        if((int)$postData['originalCancelDivisionUseDeposit'] > 0){
            $this->addPaymentDivisionFl = true;
        }

        // 재분배가 필요하다면 로직 실행
        if($this->addPaymentDivisionFl === true){
            // 교환추가상품(배송비)의 결제금액을 구함.
            $tmpOrderData = $this->getOrderData($postData['orderNo']);
            $tmpOrderInfoData = $this->getOrderInfoData($postData['orderNo'], $multiShippingOrderInfoSno);
            if ($tmpOrderData['mallSno'] > DEFAULT_MALL_NUMBER) {
                $address = $tmpOrderInfoData['receiverCountryCode'];
            } else {
                $address = str_replace(' ', '', $tmpOrderInfoData['receiverAddress'] . $tmpOrderInfoData['receiverAddressSub']);
            }

            $overseasDeliveryPolicy = null;
            $onlyOneOverseasDelivery = false;
            $cartAdmin = new CartAdmin(0, false, $tmpOrderData['mallSno']);
            $cartInfo = $cartAdmin->getCartGoodsData(null, $address, null, true);
            foreach ($cartInfo as $sKey => $sVal) {
                foreach ($sVal as $dKey => $dVal) {
                    $onlyOneDelivery = true;
                    foreach ($dVal as $gKey => $gVal) {
                        //cart에서 뽑아오는 추가될 상품이므로 cartWrite 테이블의 sno를 키로 한다.
                        $addPaymentDivisionArr['add']['price'][$gVal['sno']] = $gVal['price']['goodsPriceTotal'] - (int)$postData['enuri'][$gVal['sno'] . $gVal['goodsNo']];
                        $this->onlyAddOrderGoodsRateArr['price'][$dKey][$gVal['sno']] = $addPaymentDivisionArr['add']['price'][$gVal['sno']]; //배송부가결제금액을 안분해서 나눠갖기위한 변수

                        if ($onlyOneDelivery === true) {
                            $cartDeliveryKey = 'd'.$dKey.$gVal['sno'];
                            $deliveryPrice = $cartAdmin->totalGoodsDeliveryPolicyCharge[$dKey] + $cartAdmin->totalGoodsDeliveryAreaPrice[$dKey];
                            if ($tmpOrderData['mallSno'] > DEFAULT_MALL_NUMBER && $onlyOneOverseasDelivery === true) {
                                $deliveryPrice = 0;
                            }
                            // 교환추가 배송비를 '추가안함' 했을시 배송비는 0원
                            if($postData['addDeliveryFl'][$dKey] === 'n'){
                                $deliveryPrice = 0;
                            }

                            $addPaymentDivisionArr['add']['price'][$cartDeliveryKey] = $deliveryPrice;
                            $dKeyArr[$cartDeliveryKey] = $dKey;

                            $onlyOneDelivery = false;
                            $onlyOneOverseasDelivery = true;
                        }
                    }
                }
            }

            // 교환취소 상품의 결제금액을 구함.
            foreach($orderGoodsData as $orderGoodsNo => $data){
                $realTaxGoodsPrice = $data['realTaxSupplyGoodsPrice'] + $data['realTaxVatGoodsPrice'] + $data['realTaxFreeGoodsPrice'];

                if(in_array($orderGoodsNo, $orderGoodsSnoArr)){
                    if((int)$data['goodsCnt'] !== (int)$orderGoodsCntArr[$orderGoodsNo]){
                        // 일부수량 부분취소로 인해 남아있는상품, 교환취소상품의 결제금액을 구함

                        $differentGoodsCnt = (int)$data['goodsCnt'] - (int)$orderGoodsCntArr[$orderGoodsNo];
                        $goodsCntRate = ($differentGoodsCnt / $data['goodsCnt']);

                        //수량에 따라 안분된 총 real 금액 (교환취소상품 로직에서 아래와같이 처리하므로 전체에서 아래 일부의 realtax 금액을 빼준다.)
                        $cancelOrderGoodsSettlePrice = [];
                        $cancelOrderGoodsSettlePrice['vat'] = gd_number_figure($data['realTaxVatGoodsPrice'] * $goodsCntRate, '0.1', 'round');
                        $cancelOrderGoodsSettlePrice['supply'] = gd_number_figure($data['realTaxSupplyGoodsPrice'] * $goodsCntRate, '0.1', 'round');
                        $cancelOrderGoodsSettlePrice['free'] = gd_number_figure($data['realTaxFreeGoodsPrice'] * $goodsCntRate, '0.1', 'round');
                        $totalCancelOrderGoodsSettlePrice = (int)array_sum($cancelOrderGoodsSettlePrice);

                        $addPaymentDivisionArr['cancel']['price'][$orderGoodsNo] = $totalCancelOrderGoodsSettlePrice;
                        $this->onlyCancelOrderGoodsRateArr['price'][$data['orderDeliverySno']][$data['sno']] = $totalCancelOrderGoodsSettlePrice;
                    }
                    else {
                        // 교환취소상품의 결제금액
                        $addPaymentDivisionArr['cancel']['price'][$orderGoodsNo] = $realTaxGoodsPrice;
                        $this->onlyCancelOrderGoodsRateArr['price'][$data['orderDeliverySno']][$data['sno']] = $realTaxGoodsPrice;
                    }
                }
                else {
                    if(!in_array(substr($data['orderStatus'], 0, 1), ['e', 'f', 'r', 'c'])){
                        if($this->cancelDeliveryFl[$data['orderDeliverySno']]){
                            $this->etcOrderGoodsData[$data['orderDeliverySno']][$orderGoodsNo] = $realTaxGoodsPrice;
                        }
                    }
                }
            }

            // 교환취소 배송비의 결제금액을 구함 (배송비를 취소하였을시 취소 부가결제금액에 배송비의 것이 포함되어 있으므로 배분되어야 함)
            foreach($this->cancelDeliveryFl as $orderDeliverySno => $val){
                if($val === 'y'){
                    $orderDeliveryData = $this->getOrderDeliveryData($postData['orderNo'], $orderDeliverySno)[0];
                    $addPaymentDivisionArr['cancel']['price']['d'.$orderDeliveryData['sno']]  = $orderDeliveryData['realTaxSupplyDeliveryCharge'] + $orderDeliveryData['realTaxVatDeliveryCharge'] + $orderDeliveryData['realTaxFreeDeliveryCharge'];
                }
            }

            // 교환추가상품끼리의 비율 구하기!(배송비 제외)
            if(count($this->onlyAddOrderGoodsRateArr['price']) > 0){
                foreach($this->onlyAddOrderGoodsRateArr['price'] as $dKey => $values){
                    $addTotalOrderGoodsRealPrice = array_sum($values);
                    $addOrderGoodsRealPriceCnt = count($values);

                    if($addOrderGoodsRealPriceCnt > 0){
                        $index = 1;
                        foreach($values as $snoKey => $price){
                            if((int)$addOrderGoodsRealPriceCnt === (int)$index){
                                $this->onlyAddOrderGoodsRateArr['rate'][$dKey][$snoKey] = 1 - array_sum($this->onlyAddOrderGoodsRateArr['rate'][$dKey]);
                            }
                            else {
                                $this->onlyAddOrderGoodsRateArr['rate'][$dKey][$snoKey] = gd_number_figure($price/$addTotalOrderGoodsRealPrice, '0.00001', 'round');
                            }

                            $index++;
                        }
                    }
                }
            }
            // 교환취소 상품끼리의 비율 구하기 (배송비 제외)
            if(count($this->onlyCancelOrderGoodsRateArr['price']) > 0){
                foreach($this->onlyCancelOrderGoodsRateArr['price'] as $dKey => $values){
                    $cancelTotalOrderGoodsRealPrice = array_sum($values);
                    $cancelOrderGoodsRealPriceCnt = count($values);

                    if($cancelOrderGoodsRealPriceCnt > 0){
                        $index = 1;
                        foreach($values as $snoKey => $price){
                            if((int)$cancelOrderGoodsRealPriceCnt === (int)$index){
                                $this->onlyCancelOrderGoodsRateArr['rate'][$dKey][$snoKey] = 1 - array_sum($this->onlyCancelOrderGoodsRateArr['rate'][$dKey]);
                            }
                            else {
                                $this->onlyCancelOrderGoodsRateArr['rate'][$dKey][$snoKey] = gd_number_figure($price/$cancelTotalOrderGoodsRealPrice, '0.00001', 'round');
                            }

                            $index++;
                        }
                    }
                }
            }

            // 교환추가상품+배송비의 비율 구하기!
            $addTotalOrderRealPrice = array_sum($addPaymentDivisionArr['add']['price']);
            $addOrderRealPriceCnt = count($addPaymentDivisionArr['add']['price']);
            if($addOrderRealPriceCnt > 0){
                $index = 1;
                foreach($addPaymentDivisionArr['add']['price'] as $key => $realTaxPrice){
                    if((int)$addOrderRealPriceCnt === (int)$index){
                        $addPaymentDivisionArr['add']['rate'][$key] = 1 - array_sum($addPaymentDivisionArr['add']['rate']);
                    }
                    else {
                        $addPaymentDivisionArr['add']['rate'][$key] = gd_number_figure($realTaxPrice/$addTotalOrderRealPrice, '0.00001', 'round');
                    }

                    $index++;
                }
            }
            // 교환취소상품+배송비의 비율 구하기!
            $cencelTotalOrderRealPrice = array_sum($addPaymentDivisionArr['cancel']['price']);
            $cencelOrderRealPriceCnt = count($addPaymentDivisionArr['cancel']['price']);
            if($cencelOrderRealPriceCnt > 0){
                $index = 1;
                foreach($addPaymentDivisionArr['cancel']['price'] as $key => $realTaxPrice){
                    if((int)$cencelOrderRealPriceCnt === (int)$index){
                        $addPaymentDivisionArr['cancel']['rate'][$key] = 1 - array_sum($addPaymentDivisionArr['cancel']['rate']);
                    }
                    else {
                        $addPaymentDivisionArr['cancel']['rate'][$key] = gd_number_figure($realTaxPrice/$cencelTotalOrderRealPrice, '0.00001', 'round');
                    }

                    $index++;
                }
            }

            $addOrderRateCnt = count($addPaymentDivisionArr['add']['rate']);
            //교환추가상품 남은 마일리지 재분배
            $restUseMileage = (int)$postData['originalCancelDivisionUseMileage'] - (int)$postData['cancelDivisionUseMileage'];
            if($addOrderRateCnt > 0){
                $index = 1;
                foreach($addPaymentDivisionArr['add']['rate'] as $key => $rate){
                    if($addOrderRateCnt === $index){
                        $this->reDivisionAddPaymentArr['add']['mileage'][$key] = $restUseMileage - array_sum($this->reDivisionAddPaymentArr['add']['mileage']);
                    }
                    else {
                        $this->reDivisionAddPaymentArr['add']['mileage'][$key] = gd_number_figure($restUseMileage*$rate, '0.1', 'round');
                    }

                    // 배송비에 관한 마일리지 재분배시 주문상품에 들어갈 배송비마일리지 계산
                    if (preg_match('/d/', $key)) {
                        if(count($this->onlyAddOrderGoodsRateArr['rate']) > 0) {
                            foreach ($this->onlyAddOrderGoodsRateArr['rate'] as $dKey => $values) {
                                if($dKeyArr[$key] === $dKey){
                                    $sumMileage = $this->reDivisionAddPaymentArr['add']['mileage'][$key];
                                    $dindex = 1;
                                    foreach($values as $snoKey => $rate){
                                        if((int)count($this->onlyAddOrderGoodsRateArr['rate'][$dKey]) === (int)$dindex){
                                            $this->reDivisionAddPaymentArr['add']['divisionDeliveryGoodsMileage'][$snoKey] = $sumMileage - array_sum($this->reDivisionAddPaymentArr['add']['divisionDeliveryGoodsMileage']);
                                        }
                                        else {
                                            $this->reDivisionAddPaymentArr['add']['divisionDeliveryGoodsMileage'][$snoKey] = gd_number_figure($sumMileage * $rate, '0.1', 'round');
                                        }
                                        $dindex++;
                                    }
                                }
                            }
                        }
                    }

                    $index++;
                }
            }

            $restUseDeposit = (int)$postData['originalCancelDivisionUseDeposit'] - (int)$postData['cancelDivisionUseDeposit'];
            //교환추가상품에 남은 예치금 재분배
            if($addOrderRateCnt > 0){
                $index = 1;
                foreach($addPaymentDivisionArr['add']['rate'] as $key => $rate){
                    if($addOrderRateCnt === $index){
                        $this->reDivisionAddPaymentArr['add']['deposit'][$key] = $restUseDeposit - array_sum($this->reDivisionAddPaymentArr['add']['deposit']);
                    }
                    else {
                        $this->reDivisionAddPaymentArr['add']['deposit'][$key] = gd_number_figure($restUseDeposit*$rate, '0.1', 'round');
                    }

                    // 배송비에 관한 예치금 재분배시 주문상품에 들어갈 배송비예치금 계산
                    if (preg_match('/d/', $key)) {
                        if(count($this->onlyAddOrderGoodsRateArr['rate']) > 0) {
                            foreach ($this->onlyAddOrderGoodsRateArr['rate'] as $dKey => $values) {
                                if($dKeyArr[$key] === $dKey){
                                    $sumDeposit = $this->reDivisionAddPaymentArr['add']['deposit'][$key];
                                    $dindex = 1;
                                    foreach($values as $snoKey => $rate){
                                        if((int)count($this->onlyAddOrderGoodsRateArr['rate'][$dKey]) === (int)$dindex){
                                            $this->reDivisionAddPaymentArr['add']['divisionDeliveryGoodsDeposit'][$snoKey] = $sumDeposit - array_sum($this->reDivisionAddPaymentArr['add']['divisionDeliveryGoodsDeposit']);
                                        }
                                        else {
                                            $this->reDivisionAddPaymentArr['add']['divisionDeliveryGoodsDeposit'][$snoKey] = gd_number_figure($sumDeposit * $rate, '0.1', 'round');
                                        }
                                        $dindex++;
                                    }
                                }
                            }
                        }
                    }

                    $index++;
                }
            }

            // 교환취소 상품에 기존 부가결제금액에서 빼줄 금액
            $cancelOrderRateCnt = count($addPaymentDivisionArr['cancel']['rate']);
            //교환취소상품에 남을 마일리지 재분배
            if($cancelOrderRateCnt > 0){
                $index = 1;
                foreach($addPaymentDivisionArr['cancel']['rate'] as $key => $rate){
                    if($cancelOrderRateCnt === $index){
                        $this->reDivisionAddPaymentArr['cancel']['mileage'][$key] = (int)$postData['cancelDivisionUseMileage'] - array_sum($this->reDivisionAddPaymentArr['cancel']['mileage']);
                    }
                    else {
                        $this->reDivisionAddPaymentArr['cancel']['mileage'][$key] = gd_number_figure((int)$postData['cancelDivisionUseMileage']*$rate, '0.1', 'round');
                    }

                    // 배송비에 관한 마일리지 재분배시 주문상품에 들어갈 배송비마일리지 계산
                    if (preg_match('/d/', $key)) {
                        if(count($this->onlyCancelOrderGoodsRateArr['rate']) > 0) {
                            foreach ($this->onlyCancelOrderGoodsRateArr['rate'] as $dKey => $values) {
                                if($key === 'd'.$dKey){
                                    $sumMileage = $this->reDivisionAddPaymentArr['cancel']['mileage'][$key];
                                    $dindex = 1;
                                    foreach($values as $snoKey => $rate){
                                        if((int)count($this->onlyCancelOrderGoodsRateArr['rate'][$dKey]) === (int)$dindex){
                                            $this->reDivisionAddPaymentArr['cancel']['divisionDeliveryGoodsMileage'][$snoKey] = $sumMileage - array_sum($this->reDivisionAddPaymentArr['cancel']['divisionDeliveryGoodsMileage']);
                                        }
                                        else {
                                            $this->reDivisionAddPaymentArr['cancel']['divisionDeliveryGoodsMileage'][$snoKey] = gd_number_figure($sumMileage * $rate, '0.1', 'round');
                                        }
                                        $dindex++;
                                    }
                                }
                            }
                        }
                    }
                    $index++;
                }
            }
            //교환취소상품에 남을 예치금 재분배
            if($cancelOrderRateCnt > 0){
                $index = 1;
                foreach($addPaymentDivisionArr['cancel']['rate'] as $key => $rate){
                    if($cancelOrderRateCnt === $index){
                        $this->reDivisionAddPaymentArr['cancel']['deposit'][$key] = (int)$postData['cancelDivisionUseDeposit'] - array_sum($this->reDivisionAddPaymentArr['cancel']['deposit']);
                    }
                    else {
                        $this->reDivisionAddPaymentArr['cancel']['deposit'][$key] = gd_number_figure((int)$postData['cancelDivisionUseDeposit']*$rate, '0.1', 'round');
                    }

                    // 배송비에 관한 예치금 재분배시 주문상품에 들어갈 배송비예치금 계산
                    if (preg_match('/d/', $key)) {
                        if(count($this->onlyCancelOrderGoodsRateArr['rate']) > 0) {
                            foreach ($this->onlyCancelOrderGoodsRateArr['rate'] as $dKey => $values) {
                                if($key === 'd'.$dKey){
                                    $sumDeposit = $this->reDivisionAddPaymentArr['cancel']['deposit'][$key];
                                    $dindex = 1;
                                    foreach($values as $snoKey => $rate){
                                        if((int)count($this->onlyCancelOrderGoodsRateArr['rate'][$dKey]) === (int)$dindex){
                                            $this->reDivisionAddPaymentArr['cancel']['divisionDeliveryGoodsDeposit'][$snoKey] = $sumDeposit - array_sum($this->reDivisionAddPaymentArr['cancel']['divisionDeliveryGoodsDeposit']);
                                        }
                                        else {
                                            $this->reDivisionAddPaymentArr['cancel']['divisionDeliveryGoodsDeposit'][$snoKey] = gd_number_figure($sumDeposit * $rate, '0.1', 'round');
                                        }
                                        $dindex++;
                                    }
                                }
                            }
                        }
                    }

                    $index++;
                }
            }

            // 나머지 상품의 부가결제금액 배송비 안분 부분을 처리해주어야함.
            if(count($this->etcOrderGoodsData) > 0){
                $etcOrderGoodsRateData = [];
                foreach($this->etcOrderGoodsData as $orderDeliverySno => $orderGoodsArr){
                    if(count($orderGoodsArr) > 0){
                        $etcTotalRealTaxPrice = array_sum($orderGoodsArr);
                        $etcTotalCount = count($orderGoodsArr);
                        $index = 1;
                        foreach($orderGoodsArr as $orderGoodsNo => $realTaxPrice){
                            if((int)$etcTotalCount === (int)$index){
                                $etcOrderGoodsRateData[$orderDeliverySno][$orderGoodsNo] = 1 - array_sum($etcOrderGoodsRateData[$orderDeliverySno]);
                            }
                            else {
                                $etcOrderGoodsRateData[$orderDeliverySno][$orderGoodsNo] = gd_number_figure($realTaxPrice/$etcTotalRealTaxPrice, '0.00001', 'round');
                            }
                            $index++;
                        }
                    }
                }
            }
        }
        // --- 부가결제 금액 재분배를 위한 계산식 시작 (남은 부가결제 금액 교환추가 상품에 재분배, 교환취소 상품에 빠진만큼 재분배)

        //original data 체크 (같은 status 존재 여부)
        $count = $this->getOrderOriginalCount($postData['orderNo'], $claimStatus);
        if ($count < 1) {
            //이전 교환건 존재하지 않을 시 현재 주문정보 백업
            $this->setBackupOrderOriginalData($postData['orderNo'], $claimStatus, true, false, true);
        }

        // orderCd 최대 값 불러오기
        $orderCd = $this->getOrderGoodsMaxOrderCd($postData['orderNo']);

        //handleGroupCd 최대값 조회 - 다음 사용될 값 리턴
        $nextHandleGroupCd = $this->getMaxHandleGroupCd($postData['orderNo'], 'next');

        //handle 데이터 정의
        $handleData = [
            'orderNo' => $postData['orderNo'],
            'handleMode' => $claimStatus,
            'handler' => 'admin',
            'handleCompleteFl' => 'n',
            'handleReason' => $postData['handleReason'],
            'handleDetailReason' => $postData['handleDetailReason'],
            'handleDetailReasonShowFl' => $postData['handleDetailReasonShowFl'] ?? 'n',
            'handleGroupCd' => $nextHandleGroupCd,
        ];
        // -- 사용될 데이터 선언 끝

        //교환된 상품 처리
        $updateOrderGoodsSnoArr = [];
        foreach ($orderGoodsSnoArr as $key => $orderGoodsSno) {
            //handle 데이터 인서트
            $handleData['beforeStatus'] = $orderGoodsData[$orderGoodsSno]['orderStatus'];
            $handleSno = $this->setOrderHandle($handleData);

            //교환하려는 주문상품의 전체수량 - 교환처리 할 수량
            $differentGoodsCnt = (int)$orderGoodsData[$orderGoodsSno]['goodsCnt'] - (int)$orderGoodsCntArr[$orderGoodsSno];
            if ($differentGoodsCnt > 0) {
                // --- 일부 수량만 교환일 경우

                //교환취소 상품 생성 - 기존 주문상품건을 기준으로 새로운 주문상품건 생성 (e 값을 가지는 교환 취소 주문상품)
                $newOrderGoodsSno = $this->copyOrderGoodsData($postData['orderNo'], $orderGoodsSno, 'e1', $handleSno, $orderGoodsCntArr[$orderGoodsSno], null, ++$orderCd);
                $logOrderGoodsSno = $newOrderGoodsSno;

                //기존주문상품 데이터 업데이트 (안분된 금액)
                $updateOrderGoodsData = $this->getOrderGoodsRecalculateData($orderGoodsData[$orderGoodsSno], $differentGoodsCnt);
                $this->updateOrderGoods($updateOrderGoodsData, $postData['orderNo'], $orderGoodsSno);

                //교환취소 주문상품의 안분(통계 환불금액에서 사용)
                $updateOrderGoodsCancelData = $this->getOrderGoodsCancelUpdateData($orderGoodsData[$orderGoodsSno], $updateOrderGoodsData);
                $this->updateOrderGoods($updateOrderGoodsCancelData, $postData['orderNo'], $newOrderGoodsSno);

                // order update 를 위해 정보를 저장해둠
                $updateOrderGoodsSnoArr[] = [
                    'calculateType' => 'part',
                    'originalOrderGoodsData' => $orderGoodsData[$orderGoodsSno],
                    'changeOrderGoodsSno' => $orderGoodsSno,
                ];
                // --- 일부 수량만 교환일 경우
            }
            else {
                // --- 전체 수량 교환일 경우

                //교환취소 상품 생성 - 기존 주문상품건을 상태값 변동 (e 값을 가지는 교환 취소 주문상품)
                $updateOrderGoodsData = [
                    'realTaxSupplyGoodsPrice' => 0,
                    'realTaxVatGoodsPrice' => 0,
                    'realTaxFreeGoodsPrice' => 0,
                    'handleSno' => $handleSno,
                    'orderStatus' => 'e1',
                ];
                if($this->addPaymentDivisionFl === true){
                    if(is_array($orderGoodsData[$orderGoodsSno]['goodsTaxInfo'])){
                        $goodsTax = $orderGoodsData[$orderGoodsSno]['goodsTaxInfo'];
                    }
                    else {
                        $goodsTax = explode(STR_DIVISION, $orderGoodsData[$orderGoodsSno]['goodsTaxInfo']);
                    }

                    $totalTaxPrice = ($orderGoodsData[$orderGoodsSno]['taxSupplyGoodsPrice'] + $orderGoodsData[$orderGoodsSno]['taxVatGoodsPrice'] + $orderGoodsData[$orderGoodsSno]['taxFreeGoodsPrice']) + ((int)$orderGoodsData[$orderGoodsSno]['divisionUseMileage']-$this->reDivisionAddPaymentArr['cancel']['mileage'][$orderGoodsData[$orderGoodsSno]['sno']]) + ((int)$orderGoodsData[$orderGoodsSno]['divisionUseDeposit'] - $this->reDivisionAddPaymentArr['cancel']['deposit'][$orderGoodsData[$orderGoodsSno]['sno']]);
                    $goodsTaxData = NumberUtils::taxAll($totalTaxPrice, $goodsTax[1], $goodsTax[0]);

                    if ($goodsTax[0] === 't') {
                        $updateOrderGoodsData['taxSupplyGoodsPrice'] = gd_isset($goodsTaxData['supply'], 0);
                        $updateOrderGoodsData['taxVatGoodsPrice'] = gd_isset($goodsTaxData['tax'], 0);
                        $updateOrderGoodsData['taxFreeGoodsPrice'] = 0;
                    }
                    else {
                        $updateOrderGoodsData['taxSupplyGoodsPrice'] = 0;
                        $updateOrderGoodsData['taxVatGoodsPrice'] = 0;
                        $updateOrderGoodsData['taxFreeGoodsPrice'] = gd_isset($goodsTaxData['supply'], 0);
                    }
                    $updateOrderGoodsData['divisionUseMileage'] = (int)$this->reDivisionAddPaymentArr['cancel']['mileage'][$orderGoodsData[$orderGoodsSno]['sno']];
                    $updateOrderGoodsData['divisionUseDeposit'] = (int)$this->reDivisionAddPaymentArr['cancel']['deposit'][$orderGoodsData[$orderGoodsSno]['sno']];
                    if($this->cancelDeliveryFl[$orderGoodsData[$orderGoodsSno]['orderDeliverySno']] === 'y'){
                        $updateOrderGoodsData['divisionGoodsDeliveryUseDeposit'] = $this->reDivisionAddPaymentArr['cancel']['divisionDeliveryGoodsDeposit'][$orderGoodsData[$orderGoodsSno]['sno']];
                        $updateOrderGoodsData['divisionGoodsDeliveryUseMileage'] = $this->reDivisionAddPaymentArr['cancel']['divisionDeliveryGoodsMileage'][$orderGoodsData[$orderGoodsSno]['sno']];
                    }
                    else if($this->cancelDeliveryFl[$orderGoodsData[$orderGoodsSno]['orderDeliverySno']] === 'n'){
                        // 배송비 취소 안함일 시 남은상품에서 안분된 배송비부가결제금액을 나눠가져야 한다.
                        $this->reDivisionAddPaymentArr['etc']['deposit'][$orderGoodsData[$orderGoodsSno]['orderDeliverySno']] += (int)$orderGoodsData[$orderGoodsSno]['divisionGoodsDeliveryUseDeposit'];
                        $this->reDivisionAddPaymentArr['etc']['mileage'][$orderGoodsData[$orderGoodsSno]['orderDeliverySno']] += (int)$orderGoodsData[$orderGoodsSno]['divisionGoodsDeliveryUseMileage'];

                        $updateOrderGoodsData['divisionGoodsDeliveryUseDeposit'] = $updateOrderGoodsData['divisionGoodsDeliveryUseMileage'] = 0;
                    }
                }
                $this->updateOrderGoods($updateOrderGoodsData, $postData['orderNo'], $orderGoodsSno);
                unset($updateOrderGoodsData);

                $logOrderGoodsSno = $orderGoodsSno;

                // order update 를 위해 정보를 저장해둠
                $updateOrderGoodsSnoArr[] = [
                    'calculateType' => 'all',
                    'originalOrderGoodsData' => $orderGoodsData[$orderGoodsSno],
                ];
                // --- 전체 수량 교환일 경우
            }

            //로그 기록 - 교환 취소 상품
            $this->setOrderLog($postData['orderNo'], $logOrderGoodsSno, $orderGoodsData[$orderGoodsSno]['orderStatus'], 'e1', $message = '상세에서 교환접수 (주문상품번호:'.$logOrderGoodsSno.')');
        }

        // 교환추가상품 정보 입력
        $addGroupData = $this->addExchangeOrderGoods($postData, $changeStatus, $handleData, $orderCd, $multiShippingOrderInfoSno);

        foreach ($addGroupData['insertOrderGoodsSnoArr'] as $key => $orderGoodsSno) {
            //로그 기록 - 교환 추가 상품
            $this->setOrderLog($postData['orderNo'], $orderGoodsSno, '', $changeStatus, $message = '상세에서 교환추가접수 (주문상품번호:'.$orderGoodsSno.')');
        }

        //교환 handle 정보 입력
        $this->setOrderExchangeHandle($postData, $nextHandleGroupCd);

        // 변경될 주문서 값 재계산 및 취소 배송비 처리
        $updateOrderData = $this->reCalculateOrderInfo($postData, $updateOrderGoodsSnoArr, $addGroupData);

        // 나머지상품의 부가결제금액 조정
        if(count($this->cancelDeliveryFl)){
            foreach($this->cancelDeliveryFl as $orderDeliverySno => $val){
                if($val === 'y'){
                    foreach($etcOrderGoodsRateData[$orderDeliverySno] as $orderGoodsNo => $rate){
                        $updateEtcOrderGoodsData['reset'][$orderGoodsNo]['mileage'] = 0;
                        $updateEtcOrderGoodsData['reset'][$orderGoodsNo]['deposit'] = 0;
                    }
                }
                else if($val === 'n'){
                    $totalEtcOrderGoodsRateCnt = count($etcOrderGoodsRateData[$orderDeliverySno]);
                    if($totalEtcOrderGoodsRateCnt > 0){
                        // 남은상품에 취소주문상품에 할당된 배송비 예치금 재분배
                        $etcDivisionDeliveryDeposit = $this->reDivisionAddPaymentArr['etc']['deposit'][$orderDeliverySno];
                        $onLayerDeposit = 0;
                        $index = 1;
                        foreach($etcOrderGoodsRateData[$orderDeliverySno] as $orderGoodsNo => $rate){
                            if($totalEtcOrderGoodsRateCnt === $index){
                                $updateEtcOrderGoodsData['add'][$orderGoodsNo]['deposit'] = (int)$etcDivisionDeliveryDeposit - $onLayerDeposit;
                            }
                            else {
                                $updateEtcOrderGoodsData['add'][$orderGoodsNo]['deposit'] = gd_number_figure((int)$etcDivisionDeliveryDeposit*$rate, '0.1', 'round');
                                $onLayerDeposit = $updateEtcOrderGoodsData['add'][$orderGoodsNo]['deposit'];
                            }
                            $index++;
                        }

                        // 남은상품에 취소주문상품에 할당된 배송비 마일리지 재분배
                        $etcDivisionDeliveryMileage = $this->reDivisionAddPaymentArr['etc']['mileage'][$orderDeliverySno];
                        $onLayerMileage = 0;
                        $index = 1;
                        foreach($etcOrderGoodsRateData[$orderDeliverySno] as $orderGoodsNo => $rate){
                            if($totalEtcOrderGoodsRateCnt === $index){
                                $updateEtcOrderGoodsData['add'][$orderGoodsNo]['mileage'] = (int)$etcDivisionDeliveryMileage - $onLayerMileage;
                            }
                            else {
                                $updateEtcOrderGoodsData['add'][$orderGoodsNo]['mileage'] = gd_number_figure((int)$etcDivisionDeliveryMileage*$rate, '0.1', 'round');
                                $onLayerMileage = $updateEtcOrderGoodsData['add'][$orderGoodsNo]['mileage'];
                            }
                            $index++;
                        }
                    }
                }
                else {}
            }
        }

        if(count($updateEtcOrderGoodsData['add']) > 0){
            // 남은상품에 취소주문상품에 할당된 배송비 부가결제금액 재분배 - 배송비 취소안함일시
            $etcUpdateArr = [];
            foreach($updateEtcOrderGoodsData['add'] as $orderGoodsNo => $val){
                $etcUpdateArr['divisionGoodsDeliveryUseMileage'] = (int)$orderGoodsData[$orderGoodsNo]['divisionGoodsDeliveryUseMileage'] + (int)$val['mileage'];
                $etcUpdateArr['divisionGoodsDeliveryUseDeposit'] = (int)$orderGoodsData[$orderGoodsNo]['divisionGoodsDeliveryUseDeposit'] + (int)$val['deposit'];
                $this->updateOrderGoods($etcUpdateArr, $postData['orderNo'], $orderGoodsNo);
            }
        }
        if(count($updateEtcOrderGoodsData['reset']) > 0){
            // 남은상품에 취소주문상품의 할당된 배송비 부가결제금액 초기화 - 배송비 취소일시
            $etcUpdateArr = [];
            foreach($updateEtcOrderGoodsData['reset'] as $orderGoodsNo => $val){
                $etcUpdateArr['divisionGoodsDeliveryUseMileage'] = (int)$val['mileage'];
                $etcUpdateArr['divisionGoodsDeliveryUseDeposit'] = (int)$val['deposit'];
                $this->updateOrderGoods($etcUpdateArr, $postData['orderNo'], $orderGoodsNo);
            }
        }

        // 주문서 업데이트
        $this->updateOrder($postData['orderNo'], 'y', $updateOrderData);

        //처리되고 난 후 주문데이터
        $orderData = $this->getOrderData($postData['orderNo']);

        //pay history 로그기록
        $processLogData = $this->getOrderProcessLogArrData($postData['orderNo'], 'ae', "타상품교환", $orderData);
        $this->setOrderProcessLog($processLogData);

        // 교환추가 상품의 상태값에 따라 재고처리
        if($changeStatus === 'o1'){
            if (in_array('o', $currentStatusPolicy['sminus'])) {
                $order->setGoodsStockCutback($postData['orderNo'], $addGroupData['insertOrderGoodsSnoArr']);
            }
        }
        else if($changeStatus === 'p1'){
            if (in_array('p', $currentStatusPolicy['sminus'])) {
                $order->setGoodsStockCutback($postData['orderNo'], $addGroupData['insertOrderGoodsSnoArr']);
            }
        }
        else {}
        //마일리지 지급
        if (in_array('p', $currentStatusPolicy['mplus'])) {
            $order->setPlusMileageVariation($postData['orderNo'], ['sno' => $addGroupData['insertOrderGoodsSnoArr'], 'changeStatus' => $changeStatus]);
        }

        // 쿠폰 처리
        if ($postData['returnCouponFl'] == 'y') {
            foreach ($postData['returnCoupon'] as $memberCouponNo => $returnFl) {
                if ($returnFl == 'y') {
                    $coupon[] = $memberCouponNo;
                }
            }

            $this->setReturnCoupon($postData['orderNo'], $coupon, $claimStatus);
        }
        // 사은품 지급안함 처리
        if ($postData['returnGiftFl'] === 'n') {
            $giftData = [];
            foreach ($postData['returnGift'] as $orderGiftNo => $returnFl) {
                if ($returnFl == 'n') {
                    $giftData[] = $orderGiftNo;
                }
            }
            if(count($giftData) > 0){
                $this->setReturnGift($postData['orderNo'], $giftData, $claimStatus);
            }
            unset($giftData);
        }

        return true;
    }

    /*
    * 교환 - 새로운 교환추가건을 생성시 안분 데이터 계산
    *
    * @param string $orderNo 주문번호
    * @param array $originalOrderGoodsData 원본데이터
    * @param integer $changeOrderGoodsSno
    *
    * @return array $updateOrderGoodsData
    */
    public function getSameOrderGoodsRecalculateData($orderNo, $originalOrderGoodsData, $changeOrderGoodsSno)
    {
        $changeOrderGoodsData = $this->getOrderGoodsData($orderNo, $changeOrderGoodsSno)[0];
        $updateOrderGoodsData = [];

        $updateOrderGoodsData['taxSupplyGoodsPrice'] = ($originalOrderGoodsData['taxSupplyGoodsPrice'] - $changeOrderGoodsData['taxSupplyGoodsPrice']);
        $updateOrderGoodsData['taxVatGoodsPrice'] = ($originalOrderGoodsData['taxVatGoodsPrice'] - $changeOrderGoodsData['taxVatGoodsPrice']);
        $updateOrderGoodsData['realTaxSupplyGoodsPrice'] = ($originalOrderGoodsData['realTaxSupplyGoodsPrice'] - $changeOrderGoodsData['realTaxSupplyGoodsPrice']);
        $updateOrderGoodsData['realTaxVatGoodsPrice'] = ($originalOrderGoodsData['realTaxVatGoodsPrice'] - $changeOrderGoodsData['realTaxVatGoodsPrice']);
        $updateOrderGoodsData['taxFreeGoodsPrice'] = ($originalOrderGoodsData['taxFreeGoodsPrice'] - $changeOrderGoodsData['taxFreeGoodsPrice']);
        $updateOrderGoodsData['realTaxFreeGoodsPrice'] = ($originalOrderGoodsData['realTaxFreeGoodsPrice'] - $changeOrderGoodsData['realTaxFreeGoodsPrice']);

        //수량에 따라 안분된 상품할인 금액
        $updateOrderGoodsData['goodsDcPrice'] = ($originalOrderGoodsData['goodsDcPrice'] - $changeOrderGoodsData['goodsDcPrice']);
        //수량에 따라 안분된 회원할인 금액
        $updateOrderGoodsData['memberDcPrice'] = ($originalOrderGoodsData['memberDcPrice'] - $changeOrderGoodsData['memberDcPrice']);
        //수량에 따라 안분된 회원 중복 할인 금액
        $updateOrderGoodsData['memberOverlapDcPrice'] = ($originalOrderGoodsData['memberOverlapDcPrice'] - $changeOrderGoodsData['memberOverlapDcPrice']);
        //수량에 따라 안분된 상품 쿠폰 할인 금액
        $updateOrderGoodsData['couponGoodsDcPrice'] = ($originalOrderGoodsData['couponGoodsDcPrice'] - $changeOrderGoodsData['couponGoodsDcPrice']);
        //수량에 따라 안분된 마이앱 금액
        if ($this->myappUseFl) {
            $updateOrderGoodsData['myappDcPrice'] = ($originalOrderGoodsData['myappDcPrice'] - $changeOrderGoodsData['myappDcPrice']);
        }
        //수량에 따라 안분된 사용된 예치금
        $updateOrderGoodsData['divisionUseDeposit'] = ($originalOrderGoodsData['divisionUseDeposit'] - $changeOrderGoodsData['divisionUseDeposit']);
        //수량에 따라 안분된 사용된 마일리지
        $updateOrderGoodsData['divisionUseMileage'] = ($originalOrderGoodsData['divisionUseMileage'] - $changeOrderGoodsData['divisionUseMileage']);
        //수량에 따라 안분된 사용된 예치금 (배송비에 비례에 할당된)
        $updateOrderGoodsData['divisionGoodsDeliveryUseDeposit'] = ($originalOrderGoodsData['divisionGoodsDeliveryUseDeposit'] - $changeOrderGoodsData['divisionGoodsDeliveryUseDeposit']);
        //수량에 따라 안분된 사용된 마일리지 (배송비에 비례에 할당된)
        $updateOrderGoodsData['divisionGoodsDeliveryUseMileage'] = ($originalOrderGoodsData['divisionGoodsDeliveryUseMileage'] - $changeOrderGoodsData['divisionGoodsDeliveryUseMileage']);
        //수량에 따라 안분된 주문 쿠폰 할인 금액
        $updateOrderGoodsData['divisionCouponOrderDcPrice'] = ($originalOrderGoodsData['divisionCouponOrderDcPrice'] - $changeOrderGoodsData['divisionCouponOrderDcPrice']);
        //수량에 따라 안분된 에누리
        $updateOrderGoodsData['enuri'] = ($originalOrderGoodsData['enuri'] - $changeOrderGoodsData['enuri']);
        //수량에 따라 안분된 상품 적립 마일리지
        $updateOrderGoodsData['goodsMileage'] = ($originalOrderGoodsData['goodsMileage'] - $changeOrderGoodsData['goodsMileage']);
        //수량에 따라 안분된 회원 적립 마일리지
        $updateOrderGoodsData['memberMileage'] = ($originalOrderGoodsData['memberMileage'] - $changeOrderGoodsData['memberMileage']);
        //수량에 따라 안분된 상품쿠폰 적립 마일리지
        $updateOrderGoodsData['couponGoodsMileage'] = ($originalOrderGoodsData['couponGoodsMileage'] - $changeOrderGoodsData['couponGoodsMileage']);
        //수량에 따라 안분된 주문쿠폰 적립 마일리지
        $updateOrderGoodsData['divisionCouponOrderMileage'] = ($originalOrderGoodsData['divisionCouponOrderMileage'] - $changeOrderGoodsData['divisionCouponOrderMileage']);

        //수량
        $updateOrderGoodsData['goodsCnt'] = ($originalOrderGoodsData['goodsCnt'] - $changeOrderGoodsData['goodsCnt']);

        return $updateOrderGoodsData;
    }

    /*
    * 교환 - 기존 주문상품 데이터 안분처리
    *
    * @param array $originalOrderGoodsData 원래의 주문상품 정보
    * @param integer $differentGoodsCnt 삽입될 수량
    *
    * @return array $updateOrderGoodsData
    */
    public function getOrderGoodsRecalculateData($originalOrderGoodsData, $differentGoodsCnt)
    {
        $updateOrderGoodsData = [];

        //상품의 tax 정보
        if(is_array($originalOrderGoodsData['goodsTaxInfo'])){
            $goodsTax = $originalOrderGoodsData['goodsTaxInfo'];
        }
        else {
            $goodsTax = explode(STR_DIVISION, $originalOrderGoodsData['goodsTaxInfo']);
        }

        // 수량 비율
        $goodsCntRate = ($differentGoodsCnt / $originalOrderGoodsData['goodsCnt']);

        //총 금액
        $goodsSettlePrice = $originalOrderGoodsData['realTaxSupplyGoodsPrice'] + $originalOrderGoodsData['realTaxVatGoodsPrice'] + $originalOrderGoodsData['realTaxFreeGoodsPrice'];

        //수량에 따라 안분된 총 금액
        $goodsSettlePrice = gd_number_figure($goodsSettlePrice * $goodsCntRate, '0.1', 'round');

        $goodsTaxData = NumberUtils::taxAll($goodsSettlePrice, $goodsTax[1], $goodsTax[0]);

        if ($goodsTax[0] === 't') {
            $updateOrderGoodsData = [
                'taxSupplyGoodsPrice' => gd_isset($goodsTaxData['supply'], 0),
                'taxVatGoodsPrice' => gd_isset($goodsTaxData['tax'], 0),
                'taxFreeGoodsPrice' => 0,
            ];
        }
        else {
            $updateOrderGoodsData = [
                'taxSupplyGoodsPrice' => 0,
                'taxVatGoodsPrice' => 0,
                'taxFreeGoodsPrice' => gd_isset($goodsTaxData['supply'], 0),
            ];
        }
        $updateOrderGoodsData['realTaxSupplyGoodsPrice'] = $updateOrderGoodsData['taxSupplyGoodsPrice'];
        $updateOrderGoodsData['realTaxVatGoodsPrice'] = $updateOrderGoodsData['taxVatGoodsPrice'];
        $updateOrderGoodsData['realTaxFreeGoodsPrice'] = $updateOrderGoodsData['taxFreeGoodsPrice'];

        //수량에 따라 안분된 상품할인 금액
        $updateOrderGoodsData['goodsDcPrice'] = gd_number_figure($originalOrderGoodsData['goodsDcPrice'] * $goodsCntRate, '0.1', 'round');
        //수량에 따라 안분된 회원할인 금액
        $updateOrderGoodsData['memberDcPrice'] = gd_number_figure($originalOrderGoodsData['memberDcPrice'] * $goodsCntRate, '0.1', 'round');
        //수량에 따라 안분된 회원 중복 할인 금액
        $updateOrderGoodsData['memberOverlapDcPrice'] = gd_number_figure($originalOrderGoodsData['memberOverlapDcPrice'] * $goodsCntRate, '0.1', 'round');
        //수량에 따라 안분된 상품 쿠폰 할인 금액
        $updateOrderGoodsData['couponGoodsDcPrice'] = gd_number_figure($originalOrderGoodsData['couponGoodsDcPrice'] * $goodsCntRate, '0.1', 'round');
        //수량에 따라 안분된 마이앱 할인 금액
        if ($this->myappUseFl) {
            $updateOrderGoodsData['myappDcPrice'] = gd_number_figure($originalOrderGoodsData['myappDcPrice'] * $goodsCntRate, '0.1', 'round');
        }
        //수량에 따라 안분된 사용된 예치금
        $updateOrderGoodsData['divisionUseDeposit'] = gd_number_figure($originalOrderGoodsData['divisionUseDeposit'] * $goodsCntRate, '0.1', 'round');
        //수량에 따라 안분된 사용된 마일리지
        $updateOrderGoodsData['divisionUseMileage'] = gd_number_figure($originalOrderGoodsData['divisionUseMileage'] * $goodsCntRate, '0.1', 'round');
        //취소상품과 같은 배송비를 쓰는 나머지 상품이 있다면 계산되어야 함
        if(count($this->etcOrderGoodsData[$originalOrderGoodsData['orderDeliverySno']]) > 0){
            //수량에 따라 안분된 사용된 예치금 (배송비 금액에 비례해 할당된)
            $updateOrderGoodsData['divisionGoodsDeliveryUseDeposit'] = gd_number_figure($originalOrderGoodsData['divisionGoodsDeliveryUseDeposit'] * $goodsCntRate, '0.1', 'round');
            //수량에 따라 안분된 사용된 마일리지 (배송비 금액에 비례해 할당된)
            $updateOrderGoodsData['divisionGoodsDeliveryUseMileage'] = gd_number_figure($originalOrderGoodsData['divisionGoodsDeliveryUseMileage'] * $goodsCntRate, '0.1', 'round');
        }
        else if($this->cancelDeliveryFl[$originalOrderGoodsData['orderDeliverySno']] === 'y'){
            //수량에 따라 안분된 사용된 예치금 (배송비 금액에 비례해 할당된)
            $updateOrderGoodsData['divisionGoodsDeliveryUseDeposit'] = 0;
            //수량에 따라 안분된 사용된 마일리지 (배송비 금액에 비례해 할당된)
            $updateOrderGoodsData['divisionGoodsDeliveryUseMileage'] = 0;
        }
        else {}
        //수량에 따라 안분된 주문 쿠폰 할인 금액
        $updateOrderGoodsData['divisionCouponOrderDcPrice'] = gd_number_figure($originalOrderGoodsData['divisionCouponOrderDcPrice'] * $goodsCntRate, '0.1', 'round');
        //수량에 따라 안분된 에누리
        $updateOrderGoodsData['enuri'] = gd_number_figure($originalOrderGoodsData['enuri'] * $goodsCntRate, '0.1', 'round');

        //수량에 따라 안분된 상품 적립마일리지
        $updateOrderGoodsData['goodsMileage'] = gd_number_figure($originalOrderGoodsData['goodsMileage'] * $goodsCntRate, '0.1', 'round');
        //수량에 따라 안분된 회원 적립마일리지
        $updateOrderGoodsData['memberMileage'] = gd_number_figure($originalOrderGoodsData['memberMileage'] * $goodsCntRate, '0.1', 'round');

        //수량에 따라 안분된 상품쿠폰 적립 마일리지
        $updateOrderGoodsData['couponGoodsMileage'] = gd_number_figure($originalOrderGoodsData['couponGoodsMileage'] * $goodsCntRate, '0.1', 'round');
        //수량에 따라 안분된 주문쿠폰 적립 마일리지
        $updateOrderGoodsData['divisionCouponOrderMileage'] = gd_number_figure($originalOrderGoodsData['divisionCouponOrderMileage'] * $goodsCntRate, '0.1', 'round');

        //수량
        $updateOrderGoodsData['goodsCnt'] = $differentGoodsCnt;

        return $updateOrderGoodsData;
    }

    /*
     * 주문서DB에 들어갈 데이터 계산
     *
     * @param array $postData post data
     * @param array $updateOrderGoodsSnoArr 교환취소 관련 처리된 데이터
     * @param array $addGroupData 추가된 주문상품 sno, 추가된 배송비 sno
     *
     */
    public function reCalculateOrderInfo($postData, $updateOrderGoodsSnoArr, $addGroupData)
    {
        $orderNo = $postData['orderNo'];

        $order = App::load(\Component\Order\Order::class);

        //변경되기 전 전체 주문 데이터
        $originalOrderData = $this->getOrderData($orderNo);
        $orderHandleData = $this->getOrderHandleData($orderNo);
        if(count($orderHandleData) > 0){
            $refundUseDeposit = array_sum(array_column($orderHandleData, 'refundUseDeposit'));
            $refundUseMileage = array_sum(array_column($orderHandleData, 'refundUseMileage'));

            $originalOrderData['useDeposit'] -= gd_isset($refundUseDeposit, 0);
            $originalOrderData['useMileage'] -= gd_isset($refundUseMileage, 0);
        }

        //차감된 상품
        $settlePrice = 0;
        foreach($updateOrderGoodsSnoArr as $key => $dataArray){
            if($dataArray['calculateType'] === 'part'){
                // -- 일부 수량 변경 시
                // -- 변경 전 데이터 - 변경 후 데이터 = 취소데이터 를 차감한다.

                //변경 전 order goods data 원본
                $oriOrderGoodsData = $dataArray['originalOrderGoodsData'];
                //변경 후의 order goods data
                $changeOrderGoodsData = $this->getOrderGoodsData($orderNo, $dataArray['changeOrderGoodsSno'])[0];

                //총 상품금액
                $goodsPrice = 0;
                $goodsPrice += $oriOrderGoodsData['goodsPrice'];
                $goodsPrice += $oriOrderGoodsData['optionPrice'];
                $goodsPrice += $oriOrderGoodsData['optionTextPrice'];
                $goodsPrice = ($oriOrderGoodsData['goodsCnt'] - $changeOrderGoodsData['goodsCnt']) * $goodsPrice;

                //총 상품금액
                $originalOrderData['totalGoodsPrice'] -= $goodsPrice;
                //총 상품 할인 금액
                $originalOrderData['totalGoodsDcPrice'] -= ($oriOrderGoodsData['goodsDcPrice'] - $changeOrderGoodsData['goodsDcPrice']);
                //총 회원 할인 금액
                $originalOrderData['totalMemberDcPrice'] -= ($oriOrderGoodsData['memberDcPrice'] - $changeOrderGoodsData['memberDcPrice']);
                //총 그룹별 회원 중복 할인 금액
                $originalOrderData['totalMemberOverlapDcPrice'] -= ($oriOrderGoodsData['memberOverlapDcPrice'] - $changeOrderGoodsData['memberOverlapDcPrice']);
                //총 상품쿠폰 할인 금액
                $originalOrderData['totalCouponGoodsDcPrice'] -= ($oriOrderGoodsData['couponGoodsDcPrice'] - $changeOrderGoodsData['couponGoodsDcPrice']);
                //총 주문쿠폰 할인 금액
                $originalOrderData['totalCouponOrderDcPrice'] -= ($oriOrderGoodsData['divisionCouponOrderDcPrice'] - $changeOrderGoodsData['divisionCouponOrderDcPrice']);
                if($this->addPaymentDivisionFl === true){
                    //총 주문사용한 마일리지
                    $originalOrderData['useMileage'] -= $this->reDivisionAddPaymentArr['cancel']['mileage'][$oriOrderGoodsData['sno']];
                    //총 주문사용한 예치금
                    $originalOrderData['useDeposit'] -= $this->reDivisionAddPaymentArr['cancel']['deposit'][$oriOrderGoodsData['sno']];
                }
                else {
                    //총 주문사용한 마일리지
                    $originalOrderData['useMileage'] -= ($oriOrderGoodsData['divisionUseMileage'] - $changeOrderGoodsData['divisionUseMileage']);
                    //총 주문사용한 예치금
                    $originalOrderData['useDeposit'] -= ($oriOrderGoodsData['divisionUseDeposit'] - $changeOrderGoodsData['divisionUseDeposit']);
                }
                //총 마이앱 할인 금액
                if ($this->myappUseFl) {
                    $originalOrderData['totalMyappDcPrice'] -= ($oriOrderGoodsData['myappDcPrice'] - $changeOrderGoodsData['myappDcPrice']);
                }
                //총 상품적립 마일리지
                $originalOrderData['totalGoodsMileage'] -= ($oriOrderGoodsData['goodsMileage'] - $changeOrderGoodsData['goodsMileage']);
                //총 회원적립 마일리지
                $originalOrderData['totalMemberMileage'] -= ($oriOrderGoodsData['memberMileage'] - $changeOrderGoodsData['memberMileage']);
                //총 상품쿠폰 적립 마일리지
                $originalOrderData['totalCouponGoodsMileage'] -= ($oriOrderGoodsData['couponGoodsMileage'] - $changeOrderGoodsData['couponGoodsMileage']);
                //총 주문쿠폰 적립 마일리지
                $originalOrderData['totalCouponOrderMileage'] -= ($oriOrderGoodsData['divisionCouponOrderMileage'] - $changeOrderGoodsData['divisionCouponOrderMileage']);
                //총 적립 마일리지
                $originalOrderData['totalMileage'] -= ($oriOrderGoodsData['goodsMileage'] - $changeOrderGoodsData['goodsMileage']) + ($oriOrderGoodsData['memberMileage'] - $changeOrderGoodsData['memberMileage']) + ($oriOrderGoodsData['couponGoodsMileage'] - $changeOrderGoodsData['couponGoodsMileage']) + ($oriOrderGoodsData['divisionCouponOrderMileage'] - $changeOrderGoodsData['divisionCouponOrderMileage']);

                //총 운영자 추가 할인 (에누리)
                $originalOrderData['totalEnuriDcPrice'] -= ($oriOrderGoodsData['enuri'] - $changeOrderGoodsData['enuri']);
                //최초 과세, 부가세, 면세 금액
                $originalOrderData['taxSupplyPrice'] -= ($oriOrderGoodsData['taxSupplyGoodsPrice'] - $changeOrderGoodsData['taxSupplyGoodsPrice']);
                $originalOrderData['taxVatPrice'] -= ($oriOrderGoodsData['taxVatGoodsPrice'] - $changeOrderGoodsData['taxVatGoodsPrice']);
                $originalOrderData['taxFreePrice'] -= ($oriOrderGoodsData['taxFreeGoodsPrice'] - $changeOrderGoodsData['taxFreeGoodsPrice']);
                //실제 총 과세, 부가세, 면세 금액 (환불제외)
                $originalOrderData['realTaxSupplyPrice'] -= ($oriOrderGoodsData['realTaxSupplyGoodsPrice'] - $changeOrderGoodsData['realTaxSupplyGoodsPrice']);
                $originalOrderData['realTaxVatPrice'] -= ($oriOrderGoodsData['realTaxVatGoodsPrice'] - $changeOrderGoodsData['realTaxVatGoodsPrice']);
                $originalOrderData['realTaxFreePrice'] -= ($oriOrderGoodsData['realTaxFreeGoodsPrice'] - $changeOrderGoodsData['realTaxFreeGoodsPrice']);
            }
            else {
                // -- 전체 수량 변경 시

                $oriOrderGoodsData = $dataArray['originalOrderGoodsData'];

                //주문 상품 갯수
                $originalOrderData['orderGoodsCnt'] -= 1;

                //총 상품금액
                $goodsPrice = 0;
                $goodsPrice += $oriOrderGoodsData['goodsPrice'];
                $goodsPrice += $oriOrderGoodsData['optionPrice'];
                $goodsPrice += $oriOrderGoodsData['optionTextPrice'];
                $goodsPrice = $oriOrderGoodsData['goodsCnt'] * $goodsPrice;

                //총 상품금액
                $originalOrderData['totalGoodsPrice'] -= $goodsPrice;
                //총 상품 할인 금액
                $originalOrderData['totalGoodsDcPrice'] -= $oriOrderGoodsData['goodsDcPrice'];
                //총 회원 할인 금액
                $originalOrderData['totalMemberDcPrice'] -= $oriOrderGoodsData['memberDcPrice'];
                //총 그룹별 회원 중복 할인 금액
                $originalOrderData['totalMemberOverlapDcPrice'] -= $oriOrderGoodsData['memberOverlapDcPrice'];
                //총 상품쿠폰 할인 금액
                $originalOrderData['totalCouponGoodsDcPrice'] -= $oriOrderGoodsData['couponGoodsDcPrice'];
                //총 주문쿠폰 할인 금액
                $originalOrderData['totalCouponOrderDcPrice'] -= $oriOrderGoodsData['divisionCouponOrderDcPrice'];
                if($this->addPaymentDivisionFl === true){
                    //총 주문사용한 마일리지
                    $originalOrderData['useMileage'] -= $this->reDivisionAddPaymentArr['cancel']['mileage'][$oriOrderGoodsData['sno']];
                    //총 주문사용한 예치금
                    $originalOrderData['useDeposit'] -= $this->reDivisionAddPaymentArr['cancel']['deposit'][$oriOrderGoodsData['sno']];
                }
                else {
                    //총 주문사용한 마일리지
                    $originalOrderData['useMileage'] -= $oriOrderGoodsData['divisionUseMileage'];
                    //총 주문사용한 예치금
                    $originalOrderData['useDeposit'] -= $oriOrderGoodsData['divisionUseDeposit'];
                }
                //총 마이앱 할인 금액
                if ($this->myappUseFl) {
                    $originalOrderData['totalMyappDcPrice'] -= $oriOrderGoodsData['myappDcPrice'];
                }
                //총 상품적립 마일리지
                $originalOrderData['totalGoodsMileage'] -= $oriOrderGoodsData['goodsMileage'];
                //총 회원적립 마일리지
                $originalOrderData['totalMemberMileage'] -= $oriOrderGoodsData['memberMileage'];
                //총 상품쿠폰 적립 마일리지
                $originalOrderData['totalCouponGoodsMileage'] -= $oriOrderGoodsData['couponGoodsMileage'];
                //총 주문쿠폰 적립 마일리지
                $originalOrderData['totalCouponOrderMileage'] -= $oriOrderGoodsData['divisionCouponOrderMileage'];
                //총 적립 마일리지
                $originalOrderData['totalMileage'] -= ($oriOrderGoodsData['goodsMileage'] + $oriOrderGoodsData['memberMileage'] + $oriOrderGoodsData['couponGoodsMileage'] + $oriOrderGoodsData['divisionCouponOrderMileage']);

                //총 운영자 추가 할인 (에누리)
                $originalOrderData['totalEnuriDcPrice'] -= $oriOrderGoodsData['enuri'];

                //최초 과세, 부가세, 면세 금액
                $originalOrderData['taxSupplyPrice'] -= $oriOrderGoodsData['taxSupplyGoodsPrice'];
                $originalOrderData['taxVatPrice'] -= $oriOrderGoodsData['taxVatGoodsPrice'];
                $originalOrderData['taxFreePrice'] -= $oriOrderGoodsData['taxFreeGoodsPrice'];
                //실제 총 과세, 부가세, 면세 금액 (환불제외)
                $originalOrderData['realTaxSupplyPrice'] -= $oriOrderGoodsData['realTaxSupplyGoodsPrice'];
                $originalOrderData['realTaxVatPrice'] -= $oriOrderGoodsData['realTaxVatGoodsPrice'];
                $originalOrderData['realTaxFreePrice'] -= $oriOrderGoodsData['realTaxFreeGoodsPrice'];
            }
        }

        //취소 배송비 처리
        if(count($postData['cancelDeliveryFl']) > 0){
            foreach($postData['cancelDeliveryFl'] as $orderDeliverySno => $val){
                if($val === 'y'){
                    $orderDeliveryData = $this->getOrderDeliveryData($orderNo, $orderDeliverySno)[0];

                    //해당 배송비에 할당되어있는 예치금, 마일리지, 쿠폰 배송비할인, 회원 배송비 무료 차감
                    if($this->addPaymentDivisionFl === true){
                        $orderDeliveryData['divisionDeliveryUseDeposit'] = $this->reDivisionAddPaymentArr['cancel']['deposit']['d'.$orderDeliveryData['sno']];
                        $orderDeliveryData['divisionDeliveryUseMileage'] = $this->reDivisionAddPaymentArr['cancel']['mileage']['d'.$orderDeliveryData['sno']];
                    }
                    $originalOrderData['useDeposit'] -= $orderDeliveryData['divisionDeliveryUseDeposit'];
                    $originalOrderData['useMileage'] -= $orderDeliveryData['divisionDeliveryUseMileage'];
                    $originalOrderData['totalCouponDeliveryDcPrice'] -= $orderDeliveryData['divisionDeliveryCharge'];
                    $originalOrderData['totalMemberDeliveryDcPrice'] -= $orderDeliveryData['divisionMemberDeliveryDcPrice'];

                    //총 배송비 차감
                    $originalOrderData['totalDeliveryCharge'] -= $orderDeliveryData['deliveryCharge'];

                    $originalOrderData['taxSupplyPrice'] -= $orderDeliveryData['taxSupplyDeliveryCharge'];
                    $originalOrderData['taxVatPrice'] -= $orderDeliveryData['taxVatDeliveryCharge'];
                    $originalOrderData['taxFreePrice'] -= $orderDeliveryData['taxFreeDeliveryCharge'];
                    $originalOrderData['realTaxSupplyPrice'] -= $orderDeliveryData['realTaxSupplyDeliveryCharge'];
                    $originalOrderData['realTaxVatPrice'] -= $orderDeliveryData['realTaxVatDeliveryCharge'];
                    $originalOrderData['realTaxFreePrice'] -= $orderDeliveryData['realTaxFreeDeliveryCharge'];

                    //배송비 취소면 0원처리
                    $updateOrderDeliveryData = $this->getOrderDeliveryResetData();
                    $this->updateOrderDelivery($updateOrderDeliveryData, $orderNo, $orderDeliverySno);

                    //배송비가 차감되었다면 해당 배송비를 가지고있는 주문상품에서도 이 값을 차감해줘야함
                    /*
                    $updateOrderGoodsData = [
                        'divisionGoodsDeliveryUseDeposit' => 0,
                        'divisionGoodsDeliveryUseMileage' => 0,
                    ];
                    $this->updateOrderGoods($updateOrderGoodsData, $orderNo, null, $orderDeliverySno);
                    */
                }
            }
        }

        //배송비추가
        if(count($addGroupData['insertOrderDeliverySnoArr']) > 0){
            foreach($addGroupData['insertOrderDeliverySnoArr'] as $key => $orderDeliverySno){
                $orderDeliveryData = $this->getOrderDeliveryData($orderNo, $orderDeliverySno)[0];

                $originalOrderData['totalDeliveryCharge'] += $orderDeliveryData['deliveryCharge'];

                $originalOrderData['taxSupplyPrice'] += $orderDeliveryData['taxSupplyDeliveryCharge'];
                $originalOrderData['taxVatPrice'] += $orderDeliveryData['taxVatDeliveryCharge'];
                $originalOrderData['taxFreePrice'] += $orderDeliveryData['taxFreeDeliveryCharge'];
                $originalOrderData['realTaxSupplyPrice'] += $orderDeliveryData['realTaxSupplyDeliveryCharge'];
                $originalOrderData['realTaxVatPrice'] += $orderDeliveryData['realTaxVatDeliveryCharge'];
                $originalOrderData['realTaxFreePrice'] += $orderDeliveryData['realTaxFreeDeliveryCharge'];
            }
        }

        //추가된 상품
        foreach($addGroupData['insertOrderGoodsSnoArr'] as $key => $addOrderGoodsSno){
            $tmpAddOrderGoodsData = $order->getOrderGoodsData($orderNo, $addOrderGoodsSno);

            foreach ($tmpAddOrderGoodsData as $scmNo => $dataVal) {
                foreach ($dataVal as $addOrderGoodsData) {
                    //주문 상품 갯수
                    $originalOrderData['orderGoodsCnt'] += 1;

                    //총 상품금액
                    $goodsPrice = 0;
                    $goodsPrice += $addOrderGoodsData['goodsPrice'];
                    $goodsPrice += $addOrderGoodsData['optionPrice'];
                    $goodsPrice += $addOrderGoodsData['optionTextPrice'];
                    $goodsPrice = $addOrderGoodsData['goodsCnt'] * $goodsPrice;

                    //총 상품금액
                    $originalOrderData['totalGoodsPrice'] += $goodsPrice;
                    //총 상품 할인 금액
                    $originalOrderData['totalGoodsDcPrice'] += $addOrderGoodsData['goodsDcPrice'];
                    //총 회원 할인 금액
                    $originalOrderData['totalMemberDcPrice'] += $addOrderGoodsData['memberDcPrice'];
                    //총 그룹별 회원 중복 할인 금액
                    $originalOrderData['totalMemberOverlapDcPrice'] += $addOrderGoodsData['memberOverlapDcPrice'];
                    //총 상품쿠폰 할인 금액
                    $originalOrderData['totalCouponGoodsDcPrice'] += $addOrderGoodsData['couponGoodsDcPrice'];
                    //총 주문쿠폰 할인 금액
                    $originalOrderData['totalCouponOrderDcPrice'] += $addOrderGoodsData['divisionCouponOrderDcPrice'];
                    //총 마이앱 할인 금액
                    if ($this->myappUseFl) {
                        $originalOrderData['totalMyappDcPrice'] += $addOrderGoodsData['myappDcPrice'];
                    }
                    //총 주문사용한 마일리지
                    //$originalOrderData['useMileage'] += $addOrderGoodsData['divisionUseMileage'];
                    //총 주문사용한 예치금
                    //$originalOrderData['useDeposit'] += $addOrderGoodsData['divisionUseDeposit'];
                    //총 주문사용한 마일리지 (배송비에 안분된)
                    //$originalOrderData['useMileage'] += $addOrderGoodsData['divisionGoodsDeliveryUseMileage'];
                    //총 주문사용한 예치금 (배송비에 안분된)
                    //$originalOrderData['useDeposit'] += $addOrderGoodsData['divisionGoodsDeliveryUseDeposit'];
                    //총 상품적립 마일리지
                    $originalOrderData['totalGoodsMileage'] += $addOrderGoodsData['goodsMileage'];
                    //총 적립 마일리지
                    $originalOrderData['totalMileage'] += $addOrderGoodsData['goodsMileage'];
                    //운영자 추가 할인 금액
                    $originalOrderData['totalEnuriDcPrice'] += $addOrderGoodsData['enuri'];

                    //최초 과세, 부가세, 면세 금액
                    $originalOrderData['taxSupplyPrice'] += $addOrderGoodsData['taxSupplyGoodsPrice'];
                    $originalOrderData['taxVatPrice'] += $addOrderGoodsData['taxVatGoodsPrice'];
                    $originalOrderData['taxFreePrice'] += $addOrderGoodsData['taxFreeGoodsPrice'];
                    //실제 총 과세, 부가세, 면세 금액 (환불제외)
                    $originalOrderData['realTaxSupplyPrice'] += $addOrderGoodsData['realTaxSupplyGoodsPrice'];
                    $originalOrderData['realTaxVatPrice'] += $addOrderGoodsData['realTaxVatGoodsPrice'];
                    $originalOrderData['realTaxFreePrice'] += $addOrderGoodsData['realTaxFreeGoodsPrice'];
                }
            }
        }

        $dcSum = array_sum([
            $originalOrderData['totalGoodsDcPrice'],
            $originalOrderData['totalMemberDcPrice'],
            $originalOrderData['totalMemberOverlapDcPrice'],
            $originalOrderData['totalCouponOrderDcPrice'],
            $originalOrderData['totalCouponGoodsDcPrice'],
            $originalOrderData['totalCouponDeliveryDcPrice'],
            $originalOrderData['totalMemberDeliveryDcPrice'],
            $originalOrderData['useDeposit'],
            $originalOrderData['useMileage'],
            $originalOrderData['totalEnuriDcPrice'],
        ]);
        if ($this->myappUseFl) {
            $dcSum += $originalOrderData['totalMyappDcPrice'];
        }
        $settlePrice = $originalOrderData['totalGoodsPrice'] + $originalOrderData['totalDeliveryCharge'] - $dcSum;

        if ($settlePrice < 0) {
            $settlePrice = 0;
        }

        $updateOrderData = [
            'orderGoodsCnt' => $originalOrderData['orderGoodsCnt'], //주문 상품 갯수
            'totalGoodsPrice' => $originalOrderData['totalGoodsPrice'], //총 상품금액
            'totalGoodsDcPrice' => $originalOrderData['totalGoodsDcPrice'], //총 상품 할인 금액
            'totalMemberDcPrice' => $originalOrderData['totalMemberDcPrice'], //총 회원 할인 금액
            'totalMemberOverlapDcPrice' => $originalOrderData['totalMemberOverlapDcPrice'], //총 그룹별 회원 중복 할인 금액
            'totalCouponGoodsDcPrice' => $originalOrderData['totalCouponGoodsDcPrice'], //총 상품쿠폰 할인 금액
            'totalCouponOrderDcPrice' => $originalOrderData['totalCouponOrderDcPrice'], //총 주문쿠폰 할인 금액
            'totalCouponDeliveryDcPrice' => $originalOrderData['totalCouponDeliveryDcPrice'], //총 주문쿠폰 배송비 할인 금액
            'totalMemberDeliveryDcPrice' => $originalOrderData['totalMemberDeliveryDcPrice'], //총 회원 배송비 할인 금액
            'totalEnuriDcPrice' => $originalOrderData['totalEnuriDcPrice'], //총 운영자 추가 할인 금액
            'useMileage' => $originalOrderData['useMileage'], //총 사용 마일리지
            'useDeposit' => $originalOrderData['useDeposit'], //총 사용 예치금
            'totalGoodsMileage' => $originalOrderData['totalGoodsMileage'], //총 상품적립 마일리지
            'totalMemberMileage' => $originalOrderData['totalMemberMileage'], //총 회원적립 마일리지
            'totalCouponGoodsMileage' => $originalOrderData['totalCouponGoodsMileage'], //총 상품쿠폰 적립 마일리지
            'totalCouponOrderMileage' => $originalOrderData['totalCouponOrderMileage'], //총 주문쿠폰 적립 마일리지
            'totalMileage' => $originalOrderData['totalMileage'], //총 적립 마일리지
            'taxSupplyPrice' => $originalOrderData['taxSupplyPrice'], //최초 과세 금액
            'taxVatPrice' => $originalOrderData['taxVatPrice'], //최초 부가세 금액
            'taxFreePrice' => $originalOrderData['taxFreePrice'], //최초 면세 금액
            'realTaxSupplyPrice' => $originalOrderData['realTaxSupplyPrice'], //실제 총 과세 금액 (환불제외)
            'realTaxVatPrice' => $originalOrderData['realTaxVatPrice'], //실제 총 부가세 금액 (환불제외)
            'realTaxFreePrice' => $originalOrderData['realTaxFreePrice'], //실제 총 면세 금액 (환불제외)
            'totalDeliveryCharge' => $originalOrderData['totalDeliveryCharge'], //총 배송비
            'settlePrice' => $settlePrice, //총 주문 금액
        ];

        //총 마이앱 할인 금액
        if ($this->myappUseFl) {
            $updateOrderData['totalMyappDcPrice'] = $originalOrderData['totalMyappDcPrice'];
        }

        return $updateOrderData;
    }

    /*
    * 교환 - 교환 핸들 데이터 DB INSERT
    *
    * @param array $postData
    * @param integer $handleGroupCd 그룹번호
    *
    * @return void
    */
    public function setOrderExchangeHandle($postData, $handleGroupCd)
    {
        $arrBind = [];

        $order = App::load(\Component\Order\Order::class);
        $bank = $order->getBankInfo(null, 'y');
        foreach ($bank as $key => $val) {
            if($val['sno'] === $postData['ehSettleBankAccountInfo']){
                $postData['ehSettleBankAccountInfo'] = $val['bankName'] . ' ' . $val['accountNumber'] . ' ' . $val['depositor'];
                break;
            }
        }

        $updateData = [
            'ehOrderNo' => $postData['orderNo'], // 주문번호
            'ehHandleGroupCd' => $handleGroupCd, // orderhandle의 handleGroupCd
            'ehDifferencePrice' => $postData['totalChangePrice'], // 총차액
            'ehCancelDeliveryPrice' => $postData['cancelDeliveryPrice'], // 취소된 배송비
            'ehAddDeliveryPrice' => $postData['addDeliveryPrice'], // 추가된 배송비
            'ehRefundMethod' => $postData['ehRefundMethod'], // 환불수단
            'ehRefundName' => $postData['ehRefundName'], // 환불예금주
            'ehRefundBankName' => $postData['ehRefundBankName'], // 환불은행명
            'ehRefundBankAccountNumber' => \Encryptor::encrypt($postData['ehRefundBankAccountNumber']), // 환불계좌번호
            'ehSettleName' => $postData['ehSettleName'], // 추가결제 입금자명
            'ehSettleBankAccountInfo' => $postData['ehSettleBankAccountInfo'], // 추가결제 은행정보
            'ehEnuri' => array_sum($postData['enuri']), //에누리
        ];

        $arrBind = $this->db->get_binding(DBTableField::tableOrderExchangeHandle(), $updateData, 'insert');
        $this->db->set_insert_db(DB_ORDER_EXCHANGE_HANDLE, $arrBind['param'], $arrBind['bind'], 'y', false);

        unset($arrBind);
    }

    /*
    * 교환 - 교환 핸들 데이터 반환
    *
    * @param string $orderNo 주문번호
    * @param integer $handleGroupCd 그룹번호
    * @param integer $handleSno handle sno
    *
    * @return array $data
    */
    public function getOrderExchangeHandle($orderNo, $handleGroupCd=null, $handleSno=null)
    {
        $arrBind = [];
        $this->db->bind_param_push($arrBind, 's', $orderNo);
        $where[] = "ehOrderNo = ?";
        if ($handleSno) {
            $this->db->bind_param_push($arrBind, 'i', $handleSno);
            $where[] = "sno = ?";
        }
        if ($handleGroupCd) {
            $this->db->bind_param_push($arrBind, 'i', $handleGroupCd);
            $where[] = "ehHandleGroupCd = ?";
        }

        //최초 주문데이터
        $query = "SELECT * FROM " . DB_ORDER_EXCHANGE_HANDLE . " WHERE " . implode(" AND ", $where);
        $data = $this->db->query_fetch($query, $arrBind);
        foreach ($data as $key => $value) {
            if (gd_str_length($value['ehRefundBankAccountNumber']) > 50) {
                $data[$key]['ehRefundBankAccountNumber'] = \Encryptor::decrypt($value['ehRefundBankAccountNumber']);
            }
        }
        unset($arrBind);

        return $data;
    }

    /*
    * 교환 - 상품추가 데이터를 DB처리
    *
    * @param array $data 포스트데이터
    * @param string $changeStatus 변경될 상태값
    * @param array $handleData handle data
    * @param integer $orderCd orderCd값
    * @param integer $multiShippingOrderInfoSno order info sno - 복수배송지 주문건일때만 사용됨
    *
    * @return integer $insertSno
    */
    public function addExchangeOrderGoods($data, $changeStatus, $handleData = array(), $orderCd=0, $multiShippingOrderInfoSno=0)
    {
        $date = date('Y-m-d H:i:s');
        $orderData = $this->getOrderData($data['orderNo']);
        $orderInfoData = $this->getOrderInfoData($data['orderNo'], $multiShippingOrderInfoSno);

        $insertOrderGoodsSnoArr = [];
        $delivery = App::load(\Component\Delivery\DeliveryCart::class);
        $delivery->setDeliveryMethodCompanySno();

        // 배송비 산출을 위한 주소 및 국가 선택
        if ($orderData['mallSno'] > DEFAULT_MALL_NUMBER) {
            // 주문서 작성페이지에서 선택된 국가코드
            $address = $orderInfoData['receiverCountryCode'];
        } else {
            // 장바구니내 해외/지역별 배송비 처리를 위한 주소 값
            $address = str_replace(' ', '', $orderInfoData['receiverAddress'] . $orderInfoData['receiverAddressSub']);
        }

        // 추가한 장바구니 불러오기
        $cartAdmin = new CartAdmin(0, false, $orderData['mallSno']);
        $cartInfo = $cartAdmin->getCartGoodsData(null, $address, null, true);

        // 해외배송 기본 정책
        $overseasDeliveryPolicy = null;
        $onlyOneOverseasDelivery = false;
        if ($orderData['mallSno'] > DEFAULT_MALL_NUMBER) {
            $overseasDelivery = new OverseasDelivery();
            $overseasDeliveryPolicy = $overseasDelivery->getBasicData($orderData['mallSno'], 'mallSno');
        }

        foreach ($cartInfo as $sKey => $sVal) {
            foreach ($sVal as $dKey => $dVal) {
                $onlyOneDelivery = true;
                $deliveryPolicy = $delivery->getDataDeliveryWithGoodsNo([$dKey]);
                $deliveryMethodFl = '';
                foreach ($dVal as $gKey => $gVal) {
                    $gVal['orderNo'] = $data['orderNo'];
                    $gVal['enuri'] = $data['enuri'][$gVal['sno'] . $gVal['goodsNo']];
                    $gVal['mallSno'] = $orderData['mallSno'];
                    $gVal['orderCd'] = ++$orderCd;
                    $gVal['orderStatus'] = $changeStatus;
                    if($gVal['orderStatus'] === 'p1'){
                        $gVal['paymentDt'] = $date;
                    }
                    $gVal['minusMileageFl'] = 'y';
                    $gVal['minusDepositFl'] = 'y';

                    //교환추가상품 handleSno 인서트
                    $handleData['handleMode'] = 'z';
                    $handleData['beforeStatus'] = '';
                    $handleData['handleReason'] = $handleData['handleDetailReason'] = '교환추가상품';
                    $handleAddSno = $this->setOrderHandle($handleData);
                    $gVal['handleSno'] = $handleAddSno;

                    $gVal['deliveryMethodFl'] = empty($gVal['deliveryMethodFl']) === true ? 'delivery' : $gVal['deliveryMethodFl']; //배송방식
                    $gVal['goodsDeliveryCollectFl'] = $gVal['goodsDeliveryCollectFl'];
                    // 상품별 배송비조건인 경우 선불/착불 금액 기록 (배송비조건별인 경우 orderDelivery에 저장)
                    // orderDelivery에 각 상품별 선/착불 데이터를 저장하기 애매해서 이와 같이 처리 함
                    if ($gVal['goodsDeliveryFl'] === 'n') {
                        $gVal['goodsDeliveryCollectPrice'] = $gVal['goodsDeliveryCollectFl'] == 'pre' ? $gVal['price']['goodsDeliveryPrice'] : $gVal['price']['goodsDeliveryCollectPrice'];
                    }

                    //조건별 배송비 일때
                    if ($deliveryPolicy[$dKey]['goodsDeliveryFl'] === 'y') {
                        //조건별 배송비 사용 일 경우 배송방식을 모두 변환한다.
                        if (trim($deliveryMethodFl) === '') {
                            $deliveryMethodFl = empty($gVal['deliveryMethodFl']) === true ? 'delivery' : $gVal['deliveryMethodFl']; //배송방식
                        }
                        $gVal['deliveryMethodFl'] = $deliveryMethodFl;
                    } else {
                        $deliveryMethodFl = '';
                    }

                    if ($gVal['deliveryMethodFl'] && $gVal['deliveryMethodFl'] !== 'delivery') {
                        $gVal['invoiceCompanySno'] = $delivery->deliveryMethodList['sno'][$gVal['deliveryMethodFl']];
                    }

                    $gVal['goodsPrice'] = $gVal['price']['goodsPrice'];
                    $gVal['addGoodsCnt'] = count(gd_isset($gVal['addGoods']));
                    // 기존 추가상품의 계산로직 레거시 보장을 위해 0으로 변경 처리
                    $gVal['addGoodsPrice'] = 0;
                    $gVal['optionPrice'] = $gVal['price']['optionPrice'];
                    $gVal['optionCostPrice'] = $gVal['price']['optionCostPrice'];
                    $gVal['optionTextPrice'] = $gVal['price']['optionTextPrice'];
                    $gVal['fixedPrice'] = $gVal['price']['fixedPrice'];
                    $gVal['costPrice'] = $gVal['price']['costPrice'];
                    $gVal['goodsDcPrice'] = $gVal['price']['goodsDcPrice'];
                    $gVal['memberDcPrice'] = $gVal['price']['goodsMemberDcPrice'];
                    $gVal['memberMileage'] = $gVal['mileage']['memberMileage'];
                    $gVal['memberOverlapDcPrice'] = $gVal['price']['goodsMemberOverlapDcPrice'];
                    $gVal['couponGoodsDcPrice'] = $gVal['price']['goodsCouponGoodsDcPrice'];
                    if ($this->myappUseFl) {
                        $gVal['myappDcPrice'] = 0;
                    }
                    $gVal['goodsMileage'] = $gVal['mileage']['goodsMileage'];
                    $gVal['couponGoodsMileage'] = $gVal['mileage']['couponGoodsMileage'];
                    $gVal['goodsTaxInfo'] = $gVal['taxFreeFl'] . STR_DIVISION . $gVal['taxPercent'];// 상품 세금 정보
                    if($this->addPaymentDivisionFl === true){
                        $gVal['divisionUseDeposit'] = (int)$this->reDivisionAddPaymentArr['add']['deposit'][$gVal['sno']];
                        $gVal['divisionUseMileage'] = (int)$this->reDivisionAddPaymentArr['add']['mileage'][$gVal['sno']];
                        $gVal['divisionGoodsDeliveryUseDeposit'] = (int)$this->reDivisionAddPaymentArr['add']['divisionDeliveryGoodsDeposit'][$gVal['sno']];
                        $gVal['divisionGoodsDeliveryUseMileage'] = (int)$this->reDivisionAddPaymentArr['add']['divisionDeliveryGoodsMileage'][$gVal['sno']];
                    }
                    else {
                        $gVal['divisionUseDeposit'] = 0;
                        $gVal['divisionUseMileage'] = 0;
                        $gVal['divisionGoodsDeliveryUseDeposit'] = $gVal['price']['divisionGoodsDeliveryUseDeposit'];
                        $gVal['divisionGoodsDeliveryUseMileage'] = $gVal['price']['divisionGoodsDeliveryUseMileage'];
                    }
                    $gVal['divisionCouponOrderDcPrice'] = $gVal['price']['divisionCouponOrderDcPrice'];
                    $gVal['divisionCouponOrderMileage'] = $gVal['price']['divisionCouponOrderMileage'];
                    if ($gVal['hscode']) $gVal['hscode'] = $gVal['hscode'];
                    if ($gVal['timeSaleFl']) {
                        $gVal['timeSaleFl'] = 'y';
                    } else {
                        $gVal['timeSaleFl'] = 'n';
                    }

                    // 배송비 테이블 데이터 설정으로 foreach구문에서 최초 한번만 실행된다.
                    if ($onlyOneDelivery === true) {
                        // 배송정책내 부가세율 관련 정보 설정
                        $deliveryTaxFreeFl = $gVal['goodsDeliveryTaxFreeFl'];
                        $deliveryTaxPercent = $gVal['goodsDeliveryTaxPercent'];

                        // 배송비 복합과세 처리
                        $deliveryPrice = $cartAdmin->totalGoodsDeliveryPolicyCharge[$dKey] + $cartAdmin->totalGoodsDeliveryAreaPrice[$dKey];
                        $taxDeliveryPrice = $deliveryPrice;
                        if($this->addPaymentDivisionFl === true){
                            $cartDeliveryKey = 'd'.$dKey.$gVal['sno'];
                            $divisionDeliveryUseDeposit = (int)$this->reDivisionAddPaymentArr['add']['deposit'][$cartDeliveryKey];
                            $divisionDeliveryUseMileage = (int)$this->reDivisionAddPaymentArr['add']['mileage'][$cartDeliveryKey];
                            $taxDeliveryPrice -= ($divisionDeliveryUseDeposit + $divisionDeliveryUseMileage);
                        }
                        $tmpDeliveryTaxPrice = NumberUtils::taxAll($taxDeliveryPrice, $deliveryTaxPercent, $deliveryTaxFreeFl);

                        // 초기화
                        $taxDeliveryCharge['supply'] = 0;
                        $taxDeliveryCharge['tax'] = 0;
                        $taxDeliveryCharge['free'] = 0;

                        if ($deliveryTaxFreeFl == 't') {
                            // 배송비 과세처리
                            $taxDeliveryCharge['supply'] = $tmpDeliveryTaxPrice['supply'];
                            $taxDeliveryCharge['tax'] = $tmpDeliveryTaxPrice['tax'];
                        } else {
                            // 배송비 면세처리
                            $taxDeliveryCharge['free'] = $tmpDeliveryTaxPrice['supply'];
                        }

                        $deliveryInfo = [
                            'orderNo' => $data['orderNo'],
                            'scmNo' => $sKey,
                            'commission' => $deliveryPolicy[$dKey]['scmCommissionDelivery'],
                            'deliverySno' => $dKey,
                            'deliveryCharge' => $deliveryPrice,
                            'taxSupplyDeliveryCharge' => $taxDeliveryCharge['supply'],
                            'taxVatDeliveryCharge' => $taxDeliveryCharge['tax'],
                            'taxFreeDeliveryCharge' => $taxDeliveryCharge['free'],
                            'realTaxSupplyDeliveryCharge' => $taxDeliveryCharge['supply'],
                            'realTaxVatDeliveryCharge' => $taxDeliveryCharge['tax'],
                            'realTaxFreeDeliveryCharge' => $taxDeliveryCharge['free'],
                            'deliveryPolicyCharge' => $cartAdmin->totalGoodsDeliveryPolicyCharge[$dKey],
                            'deliveryAreaCharge' => $cartAdmin->totalGoodsDeliveryAreaPrice[$dKey],
                            'deliveryFixFl' => $gVal['goodsDeliveryFixFl'],
                            'divisionDeliveryUseDeposit' => gd_isset($divisionDeliveryUseDeposit, 0),
                            'divisionDeliveryUseMileage' => gd_isset($divisionDeliveryUseMileage, 0),
                            'divisionDeliveryCharge' => 0,
                            'deliveryInsuranceFee' => 0,
                            'goodsDeliveryFl' => $gVal['goodsDeliveryFl'],
                            'deliveryTaxInfo' => $deliveryTaxFreeFl . STR_DIVISION . $deliveryTaxPercent,
                            'deliveryWeightInfo' => 0,
                            'deliveryPolicy' => json_encode($deliveryPolicy[$dKey], JSON_UNESCAPED_UNICODE),
                            'overseasDeliveryPolicy' => json_encode($overseasDeliveryPolicy, JSON_UNESCAPED_UNICODE),
                            'deliveryCollectFl' => $gVal['goodsDeliveryCollectFl'],
                            'deliveryCollectPrice' => 0,
                            // 배송비조건별인 경우만 금액을 넣는다.
                            'deliveryMethod' => $gVal['goodsDeliveryMethod'],
                            'deliveryWholeFreeFl' => $gVal['goodsDeliveryWholeFreeFl'],
                            'deliveryWholeFreePrice' => $gVal['price']['goodsDeliveryWholeFreePrice'],
                            // 배송비 조건별/상품별에 따라서 금액을 받아온다.
                            'deliveryLog' => '',
                        ];
                        //복수배송지 사용시 order info sno 이관
                        if($this->multiShippingOrderFl === true){
                            $deliveryInfo['orderInfoSno'] = $multiShippingOrderInfoSno;
                        }

                        //배송비 추가 안함 일시 0 값으로 치환
                        if($data['addDeliveryFl'][$dKey] !== 'y'){
                            $resetOrderDeliveryData = $this->getOrderDeliveryResetData();
                            $deliveryInfo = array_merge((array)$deliveryInfo, (array)$resetOrderDeliveryData);
                        }

                        // !중요!
                        // 해외배송은 설정에 따라서 무조건 하나의 배송비조건만 가지고 계산된다.
                        // 따라서 공급사의 경우 기본적으로 공급사마다 별도의 배송비조건을 가지게 되기때문에 아래와 같이
                        // 본사/공급사 구분없이 최초 배송비조건만 할당하고 나머지 배송비는 0원으로 처리해 이를 처리한다.
                        if ($orderData['mallSno'] > DEFAULT_MALL_NUMBER && $onlyOneOverseasDelivery === true) {
                            $deliveryInfo['deliveryCharge'] = 0;
                            $deliveryInfo['taxSupplyDeliveryCharge'] = 0;
                            $deliveryInfo['taxVatDeliveryCharge'] = 0;
                            $deliveryInfo['taxFreeDeliveryCharge'] = 0;
                            $deliveryInfo['realTaxSupplyDeliveryCharge'] = 0;
                            $deliveryInfo['realTaxVatDeliveryCharge'] = 0;
                            $deliveryInfo['realTaxFreeDeliveryCharge'] = 0;
                            $deliveryInfo['deliveryPolicyCharge'] = 0;
                            $deliveryInfo['deliveryAreaCharge'] = 0;
                            $deliveryInfo['divisionDeliveryUseDeposit'] = 0;
                            $deliveryInfo['divisionDeliveryUseMileage'] = 0;
                            $deliveryInfo['divisionDeliveryCharge'] = 0;
                            $deliveryInfo['deliveryInsuranceFee'] = 0;
                            $deliveryInfo['deliveryCollectPrice'] = 0;
                            $deliveryInfo['deliveryWholeFreePrice'] = 0;
                        }

                        // 정책별 배송 정보 저장
                        $arrBind = $this->db->get_binding(DBTableField::tableOrderDelivery(), $deliveryInfo, 'insert');
                        $this->db->set_insert_db(DB_ORDER_DELIVERY, $arrBind['param'], $arrBind['bind'], 'y', false);
                        $orderDeliverySno = $this->db->insert_id();

                        if($data['addDeliveryFl'][$dKey] === 'y' && $orderDeliverySno >0){
                            $insertOrderDeliverySnoArr[] = $orderDeliverySno;
                        }
                        unset($arrBind);

                        // 한번만 실행
                        $onlyOneDelivery = false;
                        $onlyOneOverseasDelivery = true;
                    }
                    $gVal['orderDeliverySno'] = $orderDeliverySno;

                    // 옵션 설정
                    if (empty($gVal['option']) === true) {
                        $gVal['optionInfo'] = '';
                    } else {
                        foreach ($gVal['option'] as $oKey => $oVal) {
                            $tmp[] = [
                                $oVal['optionName'],
                                $oVal['optionValue'],
                                $oVal['optionCode'],
                                floatval($oVal['optionPrice']),
                                $oVal['optionDeliveryStr'],
                            ];
                        }
                        $gVal['optionInfo'] = json_encode($tmp, JSON_UNESCAPED_UNICODE);
                        unset($tmp);
                    }

                    // 텍스트 옵션
                    if (empty($gVal['optionText']) === true) {
                        $gVal['optionTextInfo'] = '';
                    } else {
                        foreach ($gVal['optionText'] as $oKey => $oVal) {
                            $tmp[$oVal['optionSno']] = [
                                $oVal['optionName'],
                                $oVal['optionValue'],
                                floatval($oVal['optionTextPrice']),
                            ];
                        }
                        $gVal['optionTextInfo'] = json_encode($tmp, JSON_UNESCAPED_UNICODE);
                        unset($tmp);
                    }
                    // 상품할인정보
                    if (empty($gVal['goodsDiscountInfo']) === true) {
                        $gVal['goodsDiscountInfo'] = '';
                    } else {
                        $gVal['goodsDiscountInfo'] = json_encode($gVal['goodsDiscountInfo'], JSON_UNESCAPED_UNICODE);
                    }
                    // 상품적립정보
                    if (empty($gVal['goodsMileageAddInfo']) === true) {
                        $gVal['goodsMileageAddInfo'] = '';
                    } else {
                        $gVal['goodsMileageAddInfo'] = json_encode($gVal['goodsMileageAddInfo'], JSON_UNESCAPED_UNICODE);
                    }

                    // 상품의 복합과세 금액 산출 및 주문상품에 저장할 필드 설정
                    $orderGoodsSettlePrice = $gVal['price']['goodsPriceSubtotal'] - $gVal['enuri'] - $gVal['divisionUseDeposit'] - $gVal['divisionUseMileage'];
                    $tmpGoodsTaxPrice = NumberUtils::taxAll($orderGoodsSettlePrice, $gVal['taxPercent'], $gVal['taxFreeFl']);
                    if ($gVal['taxFreeFl'] == 't') {
                        $gVal['taxSupplyGoodsPrice'] = $gVal['realTaxSupplyGoodsPrice'] = gd_isset($tmpGoodsTaxPrice['supply'], 0);
                        $gVal['taxVatGoodsPrice'] = $gVal['realTaxVatGoodsPrice'] = gd_isset($tmpGoodsTaxPrice['tax'], 0);
                    }
                    else {
                        $gVal['taxFreeGoodsPrice'] = $gVal['realTaxFreeGoodsPrice'] = gd_isset($tmpGoodsTaxPrice['supply'], 0);
                    }

                    // 적립 마일리지에 관한 초기화 처리
                    $gVal = $this->resetMileage($data, $gVal);

                    $arrBind = $this->db->get_binding(DBTableField::tableOrderGoods(), $gVal, 'insert');
                    $this->db->set_insert_db(DB_ORDER_GOODS, $arrBind['param'], $arrBind['bind'], 'y', false);

                    // 저장된 주문상품(order_goods) SNO 값
                    $insertOrderGoodsSnoArr[] = $this->db->insert_id();

                    unset($arrBind);
                }
            }
        }

        $returnData = [
            'insertOrderGoodsSnoArr' => $insertOrderGoodsSnoArr,
            'insertOrderDeliverySnoArr' => $insertOrderDeliverySnoArr,
        ];

        return $returnData;
    }

    public function getReOrderStatus($orderNo)
    {
        $strSQL = 'SELECT orderStatus FROM ' . DB_ORDER_GOODS . ' WHERE orderNo = ? GROUP BY orderStatus ORDER BY orderStatus ASC';
        $arrBind = [
            's',
            $orderNo,
        ];
        $getData = $this->db->query_fetch($strSQL, $arrBind);
        $getDataCnt = count($getData);

        if ($getDataCnt == 1) {
            $orderStatus = $getData[0]['orderStatus'];
        } else {
            $orderStatus = 'o1'; // 주문 코드 (o1 를 기본 값 처리)
            $standardCode = [
                'f',
                'o',
                'c',
                'b',
                'e',
                'r',
                'p',
                'g',
                'd',
                's',
            ]; // 기준 코드임 (실패, 주문, 취소, 반품, 교환, 환불, 입금, 상품, 배송) 순으로 있는 것 기준으로 처리
            $codeOrder = 0; // 코드 순서
            foreach ($getData as $key => $val) {
                // 상태 코드별 수량
                gd_isset($cnt[$val['orderStatus']], 0);
                $cnt[$val['orderStatus']] = $cnt[$val['orderStatus']] + 1;

                // 상태 코드를 설정
                $codePrefix = substr($val['orderStatus'], 0, 1);
                $tmp[$codePrefix][$val['orderStatus']] = true;

                // 기준 코드를 체크를 해서 기준 코드의 키값 보다 코드 순서보다 크면 코드 순서를 기준코드로 사용
                if (array_search($codePrefix, $standardCode) >= $codeOrder) {
                    $codeOrder = array_search($codePrefix, $standardCode);
                }
                arsort($tmp[$standardCode[$codeOrder]]); // 키를 기준으로 내림차순 정렬
                $sortCode = $tmp[$standardCode[$codeOrder]];
                $orderStatus = ArrayUtils::lastKey($sortCode); // 마지막 키가 주문 코드
            }
        }

        return $orderStatus;
    }

    /**
     * 주문 업데이트
     *
     * @param string $orderNo 주문번호
     * @param string $statusUpdateFl 주문 상태 업데이트 여부
     * @param array $updateData 업데이트 될 데이터
     *
     * @return void
     */
    public function updateOrder($orderNo, $statusUpdateFl = 'n', $updateData = [])
    {
        $arrBind = [];
        //status 업데이트시
        if ($statusUpdateFl === 'y') {
            $orderStatus = $this->getReOrderStatus($orderNo);
            $arrBind['param'][] = 'orderStatus = ?';
            $this->db->bind_param_push($arrBind['bind'], 's', $orderStatus);
        }

        if (count($updateData) > 0) {
            foreach ($updateData as $key => $value) {
                $arrBind['param'][] = $key . ' = ?';
                $this->db->bind_param_push($arrBind['bind'], $this->fieldTypes['order'][$key], $value);
            }
        }
        $this->db->bind_param_push($arrBind['bind'], 's', $orderNo);
        $res = $this->db->set_update_db(DB_ORDER, $arrBind['param'], 'orderNo = ?', $arrBind['bind']);

        unset($arrBind);

        return $res;
    }

    /**
     * orderLog 기록
     *
     * @param string $orderNo 주문번호
     * @param string $orderGoodsSno 주문상품번호
     * @param string $beforeStatus 이전 상태값
     * @param string $beforeStatus 변경될 상태값
     * @param string $message 메시지
     *
     * @return void
     */
    public function setOrderLog($orderNo, $orderGoodsSno, $beforeStatus, $afterStatus, $message = '')
    {
        $order = App::load(\Component\Order\Order::class);

        $logCode01 = $order->getOrderStatusAdmin($beforeStatus) . '(' . $beforeStatus . ')';
        $logCode02 = $order->getOrderStatusAdmin($afterStatus) . '(' . $afterStatus . ')';
        $order->orderLog($orderNo, $orderGoodsSno, $logCode01, $logCode02, $message);
    }

    /**
     * 주문상품 신규생성
     *
     * @param string $orderNo 주문번호
     * @param integer $orderGoodsSno 주문상품번호
     * @param string $orderStatus 상태값
     * @param integer $handleSno handle sno
     * @param integer $goodsCnt 재고
     * @param integer $userHandleSno user handle sno
     * @param integer $orderCd orderCd
     * @param array $updateOrderGoodsData
     *
     * @return void
     */
    public function copyOrderGoodsData($orderNo, $orderGoodsSno, $orderStatus, $handleSno = null, $goodsCnt = null, $userHandleSno = null, $orderCd=null, $updateOrderGoodsData=[])
    {
        $orderGoodsData = $this->getOrderGoodsData($orderNo, $orderGoodsSno)[0];
        if(count($updateOrderGoodsData)){
            $orderGoodsData = array_merge((array)$orderGoodsData, (array)$updateOrderGoodsData);
        }
        $orderGoodsData['optionInfo'] = gd_htmlspecialchars_stripslashes($orderGoodsData['optionInfo']);
        $orderGoodsData['orderStatus'] = $orderStatus;
        if ($handleSno) {
            $orderGoodsData['handleSno'] = $handleSno;
        }
        if ($goodsCnt) {
            $orderGoodsData['goodsCnt'] = $goodsCnt;
        }
        if ($userHandleSno !== null) {
            $orderGoodsData['userHandleSno'] = $userHandleSno;
        }
        if ($orderCd !== null) {
            $orderGoodsData['orderCd'] = $orderCd;
        }

        // 주문상품 DB insert 처리
        $insertSno = $this->insertOrderGoods($orderGoodsData);

        return $insertSno;
    }

    /**
     * 주문배송정보 신규생성
     *
     * @param string $orderNo 주문번호
     * @param integer $orderDeliverySno 주문배송번호
     * @param boolean $priceReset 배송비 초기화 여부
     *
     * @return void
     */
    public function copyOrderDeliveryData($orderNo, $orderDeliverySno, $priceReset=false)
    {
        $orderDeliveryData = $this->getOrderDeliveryData($orderNo, $orderDeliverySno)[0];

        //배송비 초기화 여부 (0원 처리)
        if($priceReset === true){
            $orderDeliveryData['deliveryCharge'] = 0;
            $orderDeliveryData['taxSupplyDeliveryCharge'] = 0;
            $orderDeliveryData['taxVatDeliveryCharge'] = 0;
            $orderDeliveryData['taxFreeDeliveryCharge'] = 0;
            $orderDeliveryData['realTaxSupplyDeliveryCharge'] = 0;
            $orderDeliveryData['realTaxVatDeliveryCharge'] = 0;
            $orderDeliveryData['realTaxFreeDeliveryCharge'] = 0;
            $orderDeliveryData['deliveryPolicyCharge'] = 0;
            $orderDeliveryData['deliveryAreaCharge'] = 0;
            $orderDeliveryData['divisionDeliveryUseDeposit'] = 0;
            $orderDeliveryData['divisionDeliveryUseMileage'] = 0;
            $orderDeliveryData['divisionDeliveryCharge'] = 0;
            $orderDeliveryData['deliveryInsuranceFee'] = 0;
            $orderDeliveryData['deliveryCollectPrice'] = 0;
            $orderDeliveryData['deliveryWholeFreePrice'] = 0;
        }

        // 주문배송정보 DB insert 처리
        $insertSno = $this->insertOrderDelivery($orderDeliveryData);

        return $insertSno;
    }

    /*
    * 주문상품 DB INSERT
    *
    * @param array $orderGoodsData
    *
    * @return integer $insertSno
    */
    public function insertOrderGoods($orderGoodsData)
    {
        // 주문상품 DB insert 처리
        $compareField = array_keys(DBTableField::getFieldNames('tableOrderGoods'));
        $arrBind = $this->db->get_binding(DBTableField::tableOrderGoods(), $orderGoodsData, 'insert', $compareField);
        $this->db->set_insert_db_query(DB_ORDER_GOODS, $arrBind['param'], $arrBind['bind'], 'y', date("Y-m-d H:i:s"));
        $insertSno = $this->db->insert_id();

        return $insertSno;
    }

    /*
    * 주문배송정보 DB INSERT
    *
    * @param array $orderDeliveryData
    *
    * @return integer $insertSno
    */
    public function insertOrderDelivery($orderDeliveryData)
    {
        // 주문상품 DB insert 처리
        $compareField = array_keys(DBTableField::getFieldNames('tableOrderDelivery'));
        $arrBind = $this->db->get_binding(DBTableField::tableOrderDelivery(), $orderDeliveryData, 'insert', $compareField);
        $this->db->set_insert_db_query(DB_ORDER_DELIVERY, $arrBind['param'], $arrBind['bind'], 'y', date("Y-m-d H:i:s"));
        $insertSno = $this->db->insert_id();

        return $insertSno;
    }

    /**
     * 주문상품 수정
     *
     * @param array $updateData 업데이트될 데이터
     * @param string $orderNo 주문번호
     * @param integer $orderGoodsSno 주문상품번호
     * @param integer $orderDeliverySno 주문배송번호
     *
     * @return void
     */
    public function updateOrderGoods($updateData, $orderNo, $orderGoodsSno=null, $orderDeliverySno=null)
    {
        $arrWhere = [];
        $compareField = array_keys($updateData);
        $arrBind = $this->db->get_binding(DBTableField::tableOrderGoods(), $updateData, 'update', $compareField);

        $arrWhere[] = 'orderNo = ?';
        $this->db->bind_param_push($arrBind['bind'], 's', $orderNo);
        if($orderGoodsSno !== null){
            $arrWhere[] = 'sno = ?';
            $this->db->bind_param_push($arrBind['bind'], 'i', $orderGoodsSno);
        }
        if($orderDeliverySno !== null){
            $arrWhere[] = 'orderDeliverySno = ?';
            $this->db->bind_param_push($arrBind['bind'], 'i', $orderDeliverySno);
        }
        $res = $this->db->set_update_db(DB_ORDER_GOODS, $arrBind['param'], implode(" AND ", $arrWhere), $arrBind['bind']);
        unset($arrBind, $updateData);

        return $res;
    }

    /**
     * 주문배송비 수정
     *
     * @param array $updateData 업데이트될 데이터
     * @param string $orderNo 주문번호
     * @param string $orderDeliverySno 주문배송비번호
     *
     * @return void
     */
    public function updateOrderDelivery($updateData, $orderNo, $orderDeliverySno)
    {
        $compareField = array_keys($updateData);
        $arrBind = $this->db->get_binding(DBTableField::tableOrderDelivery(), $updateData, 'update', $compareField);
        $arrWhere = 'orderNo = ? AND sno = ?';
        $this->db->bind_param_push($arrBind['bind'], 's', $orderNo);
        $this->db->bind_param_push($arrBind['bind'], 'i', $orderDeliverySno);
        $result = $this->db->set_update_db(DB_ORDER_DELIVERY, $arrBind['param'], $arrWhere, $arrBind['bind']);
        unset($arrBind, $updateData);

        return $result;
    }

    /*
    * 교환취소될 상품의 정보들을 리턴
    *
    * @param array $postData
    *
    * @return array $exchangeData
    */
    public function getSelectOrderBeforeData($postData)
    {
        $order = App::load(\Component\Order\Order::class);
        $tmpBeforeOrderGoodsSnoArr = $tmpBeforeOrderGoodsCntArr = $beforeOrderGoodsSnoArr = $beforeOrderGoodsCnt = [];
        $tmpBeforeOrderGoodsSnoArr = explode(INT_DIVISION, $postData['beforeOrderGoodsSno']);
        $tmpBeforeOrderGoodsCntArr = explode(INT_DIVISION, $postData['beforeOrderGoodsCnt']);

        foreach ($tmpBeforeOrderGoodsSnoArr as $key => $orderGoodsSno) {
            $beforeOrderGoodsSnoArr[$orderGoodsSno] = $orderGoodsSno;
            $beforeOrderGoodsCnt[$orderGoodsSno] = $tmpBeforeOrderGoodsCntArr[$key];
        }

        $orderData = $this->getOrderData($postData['orderNo']);
        if($orderData['multiShippingFl'] === 'y'){
            $this->multiShippingOrderFl = true;
        }

        //환불처리된 배송비
        $refundDeliveryData = $tmpRefundDeliveryData = $arrBind = [];
        $strSQL = 'SELECT og.orderDeliverySno, (oh.refundDeliveryCharge + oh.refundDeliveryUseDeposit + oh.refundDeliveryUseMileage) AS totalRefundDeliveryCharge FROM ';
        $strSQL .= DB_ORDER_GOODS . ' og LEFT JOIN ' . DB_ORDER_HANDLE . ' oh ON og.handleSno = oh.sno ';
        $strSQL .= 'WHERE og.orderNo = ? AND og.orderStatus = \'r3\' AND oh.handleCompleteFl = \'y\'';
        $arrBind = [
            's',
            $postData['orderNo'],
        ];
        $tmpRefundDeliveryData = $this->db->query_fetch($strSQL, $arrBind);
        if(count($tmpRefundDeliveryData) > 0){
            foreach($tmpRefundDeliveryData as $val){
                $refundDeliveryData[$val['orderDeliverySno']] = $val['totalRefundDeliveryCharge'];
            }
        }

        $beforeOrderGoodsData = $order->getOrderGoodsData($postData['orderNo'], $beforeOrderGoodsSnoArr);
        foreach ($beforeOrderGoodsData as $scmNo => $dataVal) {
            foreach ($dataVal as $goodsData) {
                // 수량 비율
                $goodsCntRate = ($beforeOrderGoodsCnt[$goodsData['sno']] / $goodsData['goodsCnt']);

                // 상품 1개의 금액 (판가+옵가+텍옵)
                $goodsPrice = $goodsData['goodsPrice'] + $goodsData['optionPrice'] + $goodsData['optionTextPrice'];
                $goodsPrice *= $beforeOrderGoodsCnt[$goodsData['sno']];

                //교환될 상품의 상품할인
                $goodsDcPrice = gd_number_figure($goodsData['goodsDcPrice'] * $goodsCntRate, '0.1', 'round');
                $tmpExchangeData['goodsDcPrice'][] = $goodsDcPrice;

                //교환될 상품의 회원 추가 할인 금액
                $memberDcPrice = gd_number_figure($goodsData['memberDcPrice'] * $goodsCntRate, '0.1', 'round');
                $tmpExchangeData['memberDcPrice'][] = $memberDcPrice;

                //교환될 상품의 회원 중복 할인 금액
                $memberOverlapDcPrice = gd_number_figure($goodsData['memberOverlapDcPrice'] * $goodsCntRate, '0.1', 'round');
                $tmpExchangeData['memberOverlapDcPrice'][] = $memberOverlapDcPrice;

                //교환될 상품의 쿠폰 할인 금액
                $couponGoodsDcPrice = gd_number_figure($goodsData['couponGoodsDcPrice'] * $goodsCntRate, '0.1', 'round');
                $tmpExchangeData['couponGoodsDcPrice'][] = $couponGoodsDcPrice;

                //교환될 상품의 사용된 예치금
                $divisionUseDeposit = gd_number_figure($goodsData['divisionUseDeposit'] * $goodsCntRate, '0.1', 'round');
                $tmpExchangeData['divisionUseDeposit'][] = $divisionUseDeposit;

                //교환될 상품의 사용된 마일리지
                $divisionUseMileage = gd_number_figure($goodsData['divisionUseMileage'] * $goodsCntRate, '0.1', 'round');
                $tmpExchangeData['divisionUseMileage'][] = $divisionUseMileage;

                //교환될 상품의 사용된 예치금이 배송비로 안분된 금액
                $divisionGoodsDeliveryUseDeposit = gd_number_figure($goodsData['divisionGoodsDeliveryUseDeposit'] * $goodsCntRate, '0.1', 'round');
                $tmpExchangeData['divisionGoodsDeliveryUseDeposit'][] = $divisionGoodsDeliveryUseDeposit;

                //교환될 상품의 사용된 마일리지가 배송비로 안분된 금액
                $divisionGoodsDeliveryUseMileage = gd_number_figure($goodsData['divisionGoodsDeliveryUseMileage'] * $goodsCntRate, '0.1', 'round');
                $tmpExchangeData['divisionGoodsDeliveryUseMileage'][] = $divisionGoodsDeliveryUseMileage;

                //교환될 상품의 주문 쿠폰 할인 금액
                $divisionCouponOrderDcPrice = gd_number_figure($goodsData['divisionCouponOrderDcPrice'] * $goodsCntRate, '0.1', 'round');
                $tmpExchangeData['divisionCouponOrderDcPrice'][] = $divisionCouponOrderDcPrice;

                //교환될 상품의 에누리 금액 (운영자 추가 할인)
                $enuri = gd_number_figure($goodsData['enuri'] * $goodsCntRate, '0.1', 'round');
                $tmpExchangeData['enuri'][] = $enuri;

                //교환될 상품의 마이앱할인
                if ($this->myappUseFl) {
                    $myappDcPrice = gd_number_figure($goodsData['myappDcPrice'] * $goodsCntRate, '0.1', 'round');
                    $tmpExchangeData['myappDcPrice'][] = $myappDcPrice;
                }

                //교환될 상품의 상품금액 - 할인혜택 금액
                $tmpExchangeData['totalGoodsSettlePrice'][] = $goodsPrice - ($goodsDcPrice + $memberDcPrice + $memberOverlapDcPrice + $couponGoodsDcPrice + $divisionCouponOrderDcPrice + $enuri);
                if ($this->myappUseFl) {
                    $tmpExchangeData['totalGoodsSettlePrice'][] -= $myappDcPrice;
                }

                //교환될 상품의 조정가능 배송비 금액
                $tmpExchangeData['deliveryPolicyCharge'] = $goodsData['deliveryPolicyCharge'];
                //교환될 상품의 조정가능 지역별 배송비 금액
                $tmpExchangeData['deliveryAreaCharge'] = $goodsData['deliveryAreaCharge'];
                //교환될 상품의 조정가능 해외 배송비 보험료 금액
                $tmpExchangeData['deliveryInsuranceFee'] = $goodsData['deliveryInsuranceFee'];

                if($this->multiShippingOrderFl === true){
                    $orderInfoData = $this->getOrderInfoData($postData['orderNo'], $goodsData['orderInfoSno']);
                    $subject = ((int)$orderInfoData['orderInfoCd'] === 1) ? '(메인) ' . $goodsData['deliveryMethod'] : '(추가'.((int)$orderInfoData['orderInfoCd']-1).') ' . $goodsData['deliveryMethod'];
                }
                else {
                    $subject = $goodsData['goodsNm'];
                }

                //취소 가능 배송비 (배송비에 부여된 안분된 배송쿠폰할인, 회원 배송비 무료 도 같이 취소된다.)
                $exchangeData['cancelDeliveryList'][$goodsData['orderDeliverySno']] = [
                    'deliveryCharge' => $goodsData['deliveryCharge'] - gd_isset($refundDeliveryData[$goodsData['orderDeliverySno']], 0),
                    'deliveryDcPrice' => $goodsData['divisionDeliveryCharge'] + $goodsData['divisionMemberDeliveryDcPrice'],
                    'divisionDeliveryUseDeposit' => $goodsData['divisionDeliveryUseDeposit'],
                    'divisionDeliveryUseMileage' => $goodsData['divisionDeliveryUseMileage'],
                    'subject' => $subject,
                ];

                if((int)$beforeOrderGoodsCnt[$goodsData['sno']] !== (int)$goodsData['goodsCnt']){
                    // 일부 수량에 대한 교환취소시 남은 금액을 계산하여 정보를 담아놓는다. (남은상품금액에 적용하기 위함)
                    $exchangeData['calculationData'][$goodsData['sno']] = [
                        'goodsDcPrice' => $goodsDcPrice,
                        'memberDcPrice' => $memberDcPrice,
                        'memberOverlapDcPrice' => $memberOverlapDcPrice,
                        'couponGoodsDcPrice' => $couponGoodsDcPrice,
                        'divisionCouponOrderDcPrice' => $divisionCouponOrderDcPrice,
                        'enuri' => $enuri,
                        'divisionUseDeposit' => $divisionUseDeposit,
                        'divisionUseMileage' => $divisionUseMileage,
                        'orderDeliverySno' => $goodsData['orderDeliverySno'],
                    ];
                }
            }
        }
        //교환될 상품의 차감될 상품할인 금액
        $exchangeData['totalGoodsDcPrice'] = array_sum($tmpExchangeData['goodsDcPrice']);
        //교환될 상품의 차감될 회원 추가 할인 금액
        $exchangeData['totalMemberDcPrice'] = array_sum($tmpExchangeData['memberDcPrice']);
        //교환될 상품의 차감될 회원 중복 할인 금액
        $exchangeData['totalMemberOverlapDcPrice'] = array_sum($tmpExchangeData['memberOverlapDcPrice']);
        //교환될 상품의 차감될 회원 쿠폰 할인 금액
        $exchangeData['totalCouponGoodsDcPrice'] = array_sum($tmpExchangeData['couponGoodsDcPrice']);
        //교환될 상품의 차감될 사용된 예치금
        $exchangeData['divisionUseDeposit'] = array_sum($tmpExchangeData['divisionUseDeposit']);
        //교환될 상품의 차감될 사용된 마일리지
        $exchangeData['divisionUseMileage'] = array_sum($tmpExchangeData['divisionUseMileage']);
        //교환될 상품의 차감될 주문 쿠폰 할인 금액
        $exchangeData['divisionCouponOrderDcPrice'] = array_sum($tmpExchangeData['divisionCouponOrderDcPrice']);
        //교환될 상품의 에누리 금액 (운영자 추가 할인)
        $exchangeData['enuri'] = array_sum($tmpExchangeData['enuri']);
        //교환될 상품의 마이앱 할인 금액
        if ($this->myappUseFl) {
            $exchangeData['totalMyappDcPrice'] = array_sum($tmpExchangeData['myappDcPrice']);
        }

        //교환될 상품의 전체 할인 금액
        $exchangeData['totalGoodsDcPriceSum'] = array_sum([
            $exchangeData['totalGoodsDcPrice'],
            $exchangeData['totalMemberDcPrice'],
            $exchangeData['totalMemberOverlapDcPrice'],
            $exchangeData['totalCouponGoodsDcPrice'],
            $exchangeData['divisionCouponOrderDcPrice'],
            $exchangeData['enuri'],
        ]);

        if ($this->myappUseFl) {
            $exchangeData['totalGoodsDcPriceSum'] += $exchangeData['totalMyappDcPrice'];
        }

        //취소 상품 결제금액
        $exchangeData['totalGoodsSettlePrice'] = array_sum($tmpExchangeData['totalGoodsSettlePrice']);
        //교환될 상품의 조정가능 배송비 금액
        $exchangeData['deliveryPolicyCharge'] = $tmpExchangeData['deliveryPolicyCharge'];
        //교환될 상품의 조정가능 지역별 배송비 금액
        $exchangeData['deliveryAreaCharge'] = $tmpExchangeData['deliveryAreaCharge'];
        //교환될 상품의 조정가능 해외 배송비 보험료 금액
        $exchangeData['deliveryInsuranceFee'] = $tmpExchangeData['deliveryInsuranceFee'];

        //취소가능 배송비
        foreach($exchangeData['cancelDeliveryList'] as $orderDeliverySno => $deliveryArray){
            $exchangeData['cancelDeliveryPrice'] += ($deliveryArray['deliveryCharge'] - $deliveryArray['deliveryDcPrice']);
            $exchangeData['cancelDeliveryDcPrice'] += $deliveryArray['deliveryDcPrice'];
            $exchangeData['divisionDeliveryUseDeposit'] += $deliveryArray['divisionDeliveryUseDeposit'];
            $exchangeData['divisionDeliveryUseMileage'] += $deliveryArray['divisionDeliveryUseMileage'];
        }

        //교환될 상품의 총 사용된 예치금
        $exchangeData['totalDivisionUseDeposit'] = $exchangeData['divisionUseDeposit'] + $exchangeData['divisionDeliveryUseDeposit'];
        //교환될 상품의 총 사용된 마일리지
        $exchangeData['totalDivisionUseMileage'] = $exchangeData['divisionUseMileage'] + $exchangeData['divisionDeliveryUseMileage'];

        unset($arrBind, $tmpRefundDeliveryData, $refundDeliveryData);

        return $exchangeData;
    }

    /*
    * 교환추가될 상품의 정보들을 리턴
    *
    * @param array $postData
    *
    * @return array $exchangeData
    */
    public function getSelectOrderAfterData($postData)
    {
        $exchangeData = [];
        $multiShippingOrderInfoSno = 0;

        $orderData = $this->getOrderData($postData['orderNo']);
        if($orderData['multiShippingFl'] === 'y'){
            $this->multiShippingOrderFl = true;

            $order = App::load(\Component\Order\Order::class);
            if(is_array($postData['beforeOrderGoodsSno'])){
                $beforeOrderGoodsSno = $postData['beforeOrderGoodsSno'][0];
            }
            else {
                $beforeOrderGoodsSno = $postData['beforeOrderGoodsSno'];
            }

            $orderGoodsData = $order->getOrderGoodsData($postData['orderNo'], $beforeOrderGoodsSno);
            foreach ($orderGoodsData as $scmNo => $dataVal) {
                foreach ($dataVal as $goodsData) {
                    $multiShippingOrderInfoSno = $goodsData['orderInfoSno'];
                }
            }
        }

        $orderInfoData = $this->getOrderInfoData($postData['orderNo'], $multiShippingOrderInfoSno);

        // 배송비 산출을 위한 주소 및 국가 선택
        if ($orderData['mallSno'] > DEFAULT_MALL_NUMBER) {
            // 주문서 작성페이지에서 선택된 국가코드
            $address = $orderInfoData['receiverCountryCode'];
        } else {
            // 장바구니내 해외/지역별 배송비 처리를 위한 주소 값
            $address = str_replace(' ', '', $orderInfoData['receiverAddress'] . $orderInfoData['receiverAddressSub']);
        }
        $cartAdmin = new CartAdmin(0, false, $orderData['mallSno']);
        $cartAdmin->orderGoodsChange = true;
        $cartData = $cartAdmin->getCartGoodsData(null, $address, null, true);

        foreach ($cartData as $sKey => $sVal) {
            foreach ($sVal as $dKey => $dVal) {
                if($this->multiShippingOrderFl === true){
                    $subject = array_filter(array_column($dVal, 'goodsDeliveryMethod'))[0];
                    $subject = ((int)$orderInfoData['orderInfoCd'] === 1) ? '(메인) ' . $subject : '(추가'.((int)$orderInfoData['orderInfoCd']-1).') ' . $subject;
                }
                else {
                    $subject = array_filter(array_column($dVal, 'goodsNm'))[0];
                }
                $exchangeData['addDeliveryList'][$dKey] = [
                    'deliveryCharge' => $cartAdmin->totalGoodsDeliveryPolicyCharge[$dKey] + $cartAdmin->totalGoodsDeliveryAreaPrice[$dKey],
                    'subject' => $subject,
                ];
            }
        }

        // 추가 할인금액
        $exchangeData['addGoodsDcPrice'] = $cartAdmin->totalGoodsDcPrice + (int)array_sum(explode(INT_DIVISION, $postData['enuri']));
        // 추가 상품 결제금액 - 에누리는 반영이 되어 있지 않으므로 에누리를 -처리 해준다
        $exchangeData['addGoodsSettlePrice'] = $cartAdmin->totalSettlePrice - $cartAdmin->totalDeliveryCharge - (int)array_sum(explode(INT_DIVISION, $postData['enuri']));

        //추가된 배송비
        $exchangeData['addDeliveryPrice'] = $cartAdmin->totalDeliveryCharge;

        return $exchangeData;
    }

    /**
     * getSelectOrderGoodsExchangeData
     * 차액 정보 계산
     * @param string $postData
     *
     * @return array $exchangeData 반환할 주문 상품 정보
     */
    public function getSelectOrderGoodsExchangeData($postData)
    {
        $exchangeData = [];
        //교환된 주문상품 계산정보
        $exchangeData['before'] = $this->getSelectOrderBeforeData($postData);
        //출고될 주문상품 계산정보
        $exchangeData['after'] = $this->getSelectOrderAfterData($postData);

        //최종 결제 예정 금액 : 취소 상품 결제금액 + 추가 상품 결제 금액
        $exchangeData['totalChangePrice'] = $exchangeData['before']['totalGoodsSettlePrice'] + $exchangeData['before']['cancelDeliveryPrice'] - ($exchangeData['before']['totalDivisionUseMileage'] + $exchangeData['before']['totalDivisionUseDeposit']);
        $exchangeData['totalChangePrice'] -= ($exchangeData['after']['addGoodsSettlePrice'] + $exchangeData['after']['addDeliveryPrice']);

        // 부가결제 필수 취소 금액 (UI단에서 변동가능한 금액)
        $totalDivisionAddPayment = $exchangeData['before']['totalDivisionUseMileage'] + $exchangeData['before']['totalDivisionUseDeposit'];
        $totalAddSettlePrice = $exchangeData['after']['addGoodsSettlePrice'] + $exchangeData['after']['addDeliveryPrice'];
        $exchangeData['requireCancelAddPayment'] = ((int)$totalAddSettlePrice < (int)$totalDivisionAddPayment) ? (int)$totalDivisionAddPayment - (int)$totalAddSettlePrice : 0;

        return $exchangeData;
    }

    /*
     *
     * 교환철회
     * @param array $postData 포스트 데이터
     *
     * @return
     */
    public function restoreExchangeCancel($postData)
    {
        $orderGoodsSnoArr = explode(INT_DIVISION, $postData['orderGoodsSno']);
        if (count($orderGoodsSnoArr) > 0) {
            $order = App::load(\Component\Order\Order::class);
            $orderGoodsData = $order->getOrderGoodsData($postData['orderNo']);
            if (count($orderGoodsData) > 0) {
                //original data 체크 (같은 status 존재 여부)
                $count = $this->getOrderOriginalCount($postData['orderNo'], 'e');
                if ($count < 1) {
                    throw new Exception(__('교환철회를 실패하였습니다.[백웝데이터 미존재]'));
                }
                //기존 주문건 삭제
                $deleteResult = $this->deleteOrderData('default', $postData['orderNo'], true, false, true);
                if (!$deleteResult) {
                    throw new Exception(__('교환철회를 실패하였습니다.[기존주문건 삭제 실패]'));
                }
                $result = $this->restoreOrderOriginalData($postData['orderNo'], 'e', true, false, true);
                if (!$result) {
                    throw new Exception(__('교환철회를 실패하였습니다.[주문건 복원 실패]'));
                }
                // 교환철회시 handle 데이터 삭제
                $result = $this->deleteOrderHandleData($postData['orderNo'], 'e');
                if (!$result) {
                    throw new Exception(__('교환철회를 실패하였습니다.[클레임 정보 삭제 실패]'));
                }

                //쿠폰의 orderCd, 적립금액을 복구 (전체를 복구하는것이 아닌 orderCd, 적립금액을 복구. 사유는 plusCouponFl 때문)
                $this->restoreProductCoupon($postData['orderNo']);

                if ($result) {
                    foreach ($orderGoodsData as $scmNo => $dataVal) {
                        foreach ($dataVal as $goodsData) {

                            //유저가 요청한 교환신청 내역이 있을경우 '교환신청' 상태로 업데이트
                            if((int)$goodsData['userHandleSno'] > 0){
                                $updateUserHandleData = [
                                    'userHandleFl' => 'r',
                                ];
                                $this->updateOrderUserHandle($updateUserHandleData, $postData['orderNo'], $goodsData['userHandleSno'], 'e');
                            }

                            $logCode01 = $order->getOrderStatusAdmin($goodsData['orderStatus']) . '(' . $goodsData['orderStatus'] . ')';
                            $logCode02 = $order->getOrderStatusAdmin($goodsData['beforeStatus']) . '(' . $goodsData['beforeStatus'] . ')';
                            $reason = "상품 상세에서 교환철회";
                            $order->orderLog($postData['orderNo'], $goodsData['sno'], $logCode01, $logCode02, $reason);
                        }
                    }
                }
                //최초 주문건 삭제
                $this->deleteOrderData('original', $postData['orderNo'], true, false, true);
            }
        }

        return true;
    }

    /*
     *
     * 상품상세 최초결제정보 노출
     * @param array $data 주문정보
     *
     * @return array $viewPriceArr 주문 결제정보
     */
    public function getOrderViewPriceInfo($data)
    {
        $viewPriceArr = [];
        //클레임 처리된 주문정보가 있다면 최초 주문건은 original 데이터를 가져온다.
        $originalData = $this->getOrderOriginalData($data['orderNo'], ['order', 'orderDelivery']);
        if ($originalData['orderDelivery']['sno']) {
            $totalDeliveryPolicyCharge = $originalData['orderDelivery']['deliveryPolicyCharge'];
            $totalDeliveryAreaCharge = $originalData['orderDelivery']['deliveryAreaCharge'];
        } else {
            $totalDeliveryPolicyCharge = array_sum(array_column($originalData['orderDelivery'], 'deliveryPolicyCharge'));
            $totalDeliveryAreaCharge = array_sum(array_column($originalData['orderDelivery'], 'deliveryAreaCharge'));
        }

        $viewPriceArr = [
            'totalGoodsPrice' => $originalData['order']['totalGoodsPrice'], //상품판매금액
            'totalDeliveryCharge' => $originalData['order']['totalDeliveryCharge'], //총 배송비
            'totalDeliveryPolicyCharge' => $totalDeliveryPolicyCharge, //배송비
            'totalDeliveryAreaCharge' => $totalDeliveryAreaCharge, //지역별 배송비
            'totalGoodsDcPrice' => $originalData['order']['totalGoodsDcPrice'], //총 상품할인
            'totalMemberDcPrice' => $originalData['order']['totalMemberDcPrice'], //총 회원할인
            'totalMemberOverlapDcPrice' => $originalData['order']['totalMemberOverlapDcPrice'], //총 회원중복할인
            'totalCouponOrderDcPrice' => $originalData['order']['totalCouponOrderDcPrice'], //총 주문쿠폰할인
            'totalCouponGoodsDcPrice' => $originalData['order']['totalCouponGoodsDcPrice'], //총 상품쿠폰할인
            'totalCouponDeliveryDcPrice' => $originalData['order']['totalCouponDeliveryDcPrice'], //총 배송쿠폰할인
            'totalMemberDeliveryDcPrice' => $originalData['order']['totalMemberDeliveryDcPrice'], //총 회원배송할인
            'totalEnuriDcPrice' => $originalData['order']['totalEnuriDcPrice'], //총 운영자 추가 할인
            'useDeposit' => $originalData['order']['useDeposit'], //총 예치금 금액
            'useMileage' => $originalData['order']['useMileage'], //총 마일리지 금액
            'totalDeliveryInsuranceFee' => $originalData['order']['totalDeliveryInsuranceFee'], //해외배송 보험료
            'settlePrice' => $originalData['order']['settlePrice'], //실 결제금액
            'overseasSettlePrice' => $originalData['order']['overseasSettlePrice'], //승인금액
            'totalMileage' => $originalData['order']['totalMileage'], //총 적립금액
            'totalGoodsMileage' => $originalData['order']['totalGoodsMileage'],
            'totalMemberMileage' => $originalData['order']['totalMemberMileage'],
            'totalCouponOrderMileage' => $originalData['order']['totalCouponOrderMileage'],
            'mileageGiveExclude' => $originalData['order']['mileageGiveExclude'],
        ];

        //총 마이앱 할인
        if ($this->myappUseFl) {
            $viewPriceArr['totalMyappDcPrice'] = $originalData['order']['totalMyappDcPrice'];
        }

        if ($data['orderChannelFl'] === 'naverpay') {
            $viewPriceArr['checkoutData'] = json_decode($originalData['order']['checkoutData'], true);
        }

        return $viewPriceArr;
    }

    /*
     *
     * 상품상세 최종결제정보 노출 - 다른 클레임처리는 주문건의 값을 변경하지만 환불 클레임은 변경하지 않으므로 계산해 주어야 한다.
     *
     * @param array $data 주문정보
     *
     * @return array $data 주문정보
     */
    public function getOrderViewPriceRefundAdjust($data)
    {
        $refundExcludeOrderData = [
            'totalGoodsPrice' => 0,
            'totalDeliveryCharge' => 0,
            'useDeposit' => 0,
            'useMileage' => 0,
            'totalGoodsDcPrice' => 0,
            'totalMemberDcPrice' => 0,
            'totalMemberOverlapDcPrice' => 0,
            'totalCouponOrderDcPrice' => 0,
            'totalCouponGoodsDcPrice' => 0,
            'totalEnuriDcPrice' => 0,
            'totalCouponDeliveryDcPrice' => 0,
            'totalMemberDeliveryDcPrice' => 0,
            'totalGoodsMileage' => 0,
            'totalMemberMileage' => 0,
            'totalCouponGoodsMileage' => 0,
            'totalCouponOrderMileage' => 0,
        ];
        if ($this->myappUseFl) {
            $refundExcludeOrderData['totalMyappDcPrice'] = 0;
        }
        $exceptHandleMode = [
            'r',
            'e',
            'c',
        ];

        if(count($data['goods']) > 0){
            $deliveryUniqueKey = '';
            foreach ($data['goods'] as $sKey => $sVal) {
                foreach ($sVal as $dKey => $dVal) {
                    foreach ($dVal as $key => $val) {
                        if(!in_array($val['handleMode'], $exceptHandleMode)){
                            $refundExcludeOrderData['totalGoodsPrice'] += (($val['goodsPrice'] + $val['optionPrice'] + $val['optionTextPrice']) * $val['goodsCnt']);
                            $refundExcludeOrderData['useDeposit'] += $val['divisionUseDeposit'];
                            $refundExcludeOrderData['useMileage'] += $val['divisionUseMileage'];
                            $refundExcludeOrderData['totalGoodsDcPrice'] += $val['goodsDcPrice'];
                            $refundExcludeOrderData['totalMemberDcPrice'] += $val['memberDcPrice'];
                            $refundExcludeOrderData['totalMemberOverlapDcPrice'] += $val['memberOverlapDcPrice'];
                            $refundExcludeOrderData['totalCouponOrderDcPrice'] += $val['divisionCouponOrderDcPrice'];
                            $refundExcludeOrderData['totalCouponGoodsDcPrice'] += $val['couponGoodsDcPrice'];
                            $refundExcludeOrderData['totalEnuriDcPrice'] += $val['enuri'];
                            if ($this->myappUseFl) {
                                $refundExcludeOrderData['totalMyappDcPrice'] += $val['myappDcPrice'];
                            }
                            $refundExcludeOrderData['totalGoodsMileage'] += $val['goodsMileage'];
                            $refundExcludeOrderData['totalMemberMileage'] += $val['memberMileage'];
                            $refundExcludeOrderData['totalCouponGoodsMileage'] += $val['couponGoodsMileage'];
                            $refundExcludeOrderData['totalCouponOrderMileage'] += $val['divisionCouponOrderMileage'];

                            $deliveryKeyCheck = $val['deliverySno'] . '-' . $val['orderDeliverySno'];
                            if ($deliveryKeyCheck != $deliveryUniqueKey) {
                                $refundExcludeOrderData['totalDeliveryCharge'] += ($val['realTaxSupplyDeliveryCharge'] + $val['realTaxVatDeliveryCharge'] + $val['realTaxFreeDeliveryCharge'] + $val['divisionDeliveryUseDeposit'] + $val['divisionDeliveryUseMileage'] + $val['divisionMemberDeliveryDcPrice'] + $val['divisionDeliveryCharge']);
                                $refundExcludeOrderData['useDeposit'] += $val['divisionDeliveryUseDeposit'];
                                $refundExcludeOrderData['useMileage'] += $val['divisionDeliveryUseMileage'];
                                $refundExcludeOrderData['totalCouponDeliveryDcPrice'] += $val['divisionDeliveryCharge'];
                                $refundExcludeOrderData['totalMemberDeliveryDcPrice'] += $val['divisionMemberDeliveryDcPrice'];
                            }
                            $deliveryUniqueKey = $deliveryKeyCheck;
                        }
                        else if($val['handleMode'] === 'r'){
                            // 주문상품은 환불되더라도 배송비는 남아있는 경우가 있으므로 따로 금액에 포함시켜준다.
                            $deliveryKeyCheck = $val['deliverySno'] . '-' . $val['orderDeliverySno'];
                            if ($deliveryKeyCheck != $deliveryUniqueKey) {
                                $refundExcludeOrderData['totalDeliveryCharge'] += ($val['realTaxSupplyDeliveryCharge'] + $val['realTaxVatDeliveryCharge'] + $val['realTaxFreeDeliveryCharge'] + $val['divisionDeliveryUseDeposit'] + $val['divisionDeliveryUseMileage'] + $val['divisionMemberDeliveryDcPrice'] + $val['divisionDeliveryCharge']);
                                $refundExcludeOrderData['useDeposit'] += $val['divisionDeliveryUseDeposit'];
                                $refundExcludeOrderData['useMileage'] += $val['divisionDeliveryUseMileage'];
                                $refundExcludeOrderData['totalCouponDeliveryDcPrice'] += $val['divisionDeliveryCharge'];
                                $refundExcludeOrderData['totalMemberDeliveryDcPrice'] += $val['divisionMemberDeliveryDcPrice'];
                            }
                            $deliveryUniqueKey = $deliveryKeyCheck;
                        }
                        else { }
                    }
                }
            }
        }

        $data['totalGoodsPrice'] = $refundExcludeOrderData['totalGoodsPrice'];
        $data['totalDeliveryCharge'] = $refundExcludeOrderData['totalDeliveryCharge'];
        $data['useDeposit'] = $refundExcludeOrderData['useDeposit'];
        $data['useMileage'] = $refundExcludeOrderData['useMileage'];
        $data['totalGoodsDcPrice'] = $refundExcludeOrderData['totalGoodsDcPrice'];
        $data['totalMemberDcPrice'] = $refundExcludeOrderData['totalMemberDcPrice'];
        $data['totalMemberOverlapDcPrice'] = $refundExcludeOrderData['totalMemberOverlapDcPrice'];
        $data['totalCouponOrderDcPrice'] = $refundExcludeOrderData['totalCouponOrderDcPrice'];
        $data['totalCouponGoodsDcPrice'] = $refundExcludeOrderData['totalCouponGoodsDcPrice'];
        $data['totalEnuriDcPrice'] = $refundExcludeOrderData['totalEnuriDcPrice'];
        $data['totalCouponDeliveryDcPrice'] = $refundExcludeOrderData['totalCouponDeliveryDcPrice'];
        $data['totalMemberDeliveryDcPrice'] = $refundExcludeOrderData['totalMemberDeliveryDcPrice'];
        if ($this->myappUseFl) {
            $data['totalMyappDcPrice'] = $refundExcludeOrderData['totalMyappDcPrice'];
        }
        $data['totalGoodsMileage'] = $refundExcludeOrderData['totalGoodsMileage'];
        $data['totalMemberMileage'] = $refundExcludeOrderData['totalMemberMileage'];
        $data['totalCouponGoodsMileage'] = $refundExcludeOrderData['totalCouponGoodsMileage'];
        $data['totalCouponOrderMileage'] = $refundExcludeOrderData['totalCouponOrderMileage'];
        $data['totalMileage'] = $data['totalGoodsMileage'] + $data['totalMemberMileage'] + $data['totalCouponGoodsMileage'] + $data['totalCouponOrderMileage'];

        unset($refundExcludeOrderData);

        return $data;
    }

    /**
     *
     * 최초 주문정보가 있는지 확인
     * @param string $orderNo 주문번호
     * @param string $claimStatus 클레임 상태
     * @param boolean $refundExclude 환불 제외 여부
     *
     * @return integer $cnt 최초 주문정보 개수
     */
    public function getOrderOriginalCount($orderNo, $claimStatus = '', $refundExclude = false)
    {
        $arrWhere = $arrBind = [];
        $arrWhere[] = 'orderNo = ?';
        $this->db->bind_param_push($arrBind, 's', $orderNo);
        if($refundExclude === true){
            $arrWhere[] = 'claimStatus != ?';
            $this->db->bind_param_push($arrBind, 's', 'r');
        }
        if ($claimStatus) {
            $arrWhere[] = 'claimStatus = ?';
            $this->db->bind_param_push($arrBind, 's', $claimStatus);
        }

        $this->db->strField = 'COUNT(*) as cnt';
        $this->db->strWhere = implode(' AND ', gd_isset($arrWhere));

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_ORIGINAL . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $arrBind, false);
        unset($arrWhere, $arrBind);

        return (int)$data['cnt'];
    }

    /**
     * getOrderOriginalData
     * 최초주문정보
     * @param string $orderNo 주문번호
     * @param array $getTypeArr 가져올 데이터 타입 ['order', 'orderGoods', 'orderDelivery', 'orderGift']
     *
     * @return array $orderData 최초 주문, 주문상품, 주문배송 데이터
     */
    public function getOrderOriginalData($orderNo, $getTypeArr = [])
    {
        $claimStatus = '';
        $orderData = [];
        $defaultGetTableName = [
            'order' => DB_ORDER_ORIGINAL, //최초 주문데이터
            'orderGoods' => DB_ORDER_GOODS_ORIGINAL, //최초 주문상품 데이터
            'orderDelivery' => DB_ORDER_DELIVERY_ORIGINAL, //최초 주문 배송 데이터
            'orderGift' => DB_ORDER_GIFT_ORIGINAL, //최초 주문 사은품 데이터
            'orderCashReceipt' => DB_ORDER_CASH_RECEIPT_ORIGINAL, //최초 현금영수증 데이터
        ];
        if (count($getTypeArr) < 1) {
            //orderCashReceipt는 필요시에 넣으면 된다.
            $getTypeArr = ['order', 'orderGoods', 'orderDelivery', 'orderGift', 'orderCashReceipt'];
        }

        foreach ($getTypeArr as $key => $typeName) {
            $addQuery = '';
            if($typeName === 'order'){
                // @yby 환불주문건이 최초뿐 아니라 클레임처리시마다 저장됨으로 인해 순서를 구분하기 위해 들어간 필드
                $addQuery = " ORDER BY claimSort ASC LIMIT 1";
            }
            else {
                if($claimStatus){
                    $addQuery = " AND claimStatus = '".$claimStatus."'";
                }
            }

            $arrBind = [];
            $this->db->bind_param_push($arrBind, 's', $orderNo);
            $queryOrder = "SELECT * FROM " . $defaultGetTableName[$typeName] . " WHERE orderNo = ? " . $addQuery;
            $orderData[$typeName] = $this->db->query_fetch($queryOrder, $arrBind, false);

            if($typeName === 'order'){
                $claimStatus = $orderData[$typeName]['claimStatus'];
            }

            unset($arrBind);
        }

        return $orderData;
    }

    /**
     * setBackupOriginalOrderData
     * 최초주문정보 백업
     * @param string $orderNo 주문번호
     * @param string $claimStatus 클레임 상태
     * @param boolean $giftAction 사은품 백업 처리 여부
     * @param boolean $receiptAction 현금영수증 백업 처리 여부
     * @param boolean $orderStatisticsAction 주문통계 백업 처리 여부
     *
     * @return boolean $return 성공여부
     */
    public function setBackupOrderOriginalData($orderNo, $claimStatus, $giftAction=false, $receiptAction=false, $orderStatisticsAction=false)
    {
        $resultArray = [];

        $maxClaimSort = $this->getOrderOriginalMaxClaimSort($orderNo);

        // 주문백업
        $orderCommonColumns = $this->getCommonField(DB_ORDER, DB_ORDER_ORIGINAL);
        if(count($orderCommonColumns) > 0){
            $orderData = $this->db->query_fetch("SELECT " . implode(", ", $orderCommonColumns) . " FROM " . DB_ORDER . " WHERE orderNo = '" . $orderNo . "'");

            if(count($orderData) > 0){
                foreach($orderData as $key => $value){
                    $arrBind = $this->db->get_binding(DBTableField::tableOrder(), $value, 'insert');
                    $arrBind['param'][] = 'claimStatus';
                    $arrBind['param'][] = 'claimSort';
                    $this->db->bind_param_push($arrBind['bind'], 's', $claimStatus);
                    $this->db->bind_param_push($arrBind['bind'], 'i', $maxClaimSort+1);
                    try {
                        $this->db->set_insert_db_query(DB_ORDER_ORIGINAL, $arrBind['param'], $arrBind['bind'], 'y', $value['regDt']);
                        $resultArray[] = true;
                    } catch (Exception $e) {
                        $resultArray[] = false;
                    }

                    unset($arrBind);
                }
            }
        }

        // 주문상품 백업
        $orderGoodsCommonColumns = $this->getCommonField(DB_ORDER_GOODS, DB_ORDER_GOODS_ORIGINAL);
        if(count($orderGoodsCommonColumns) > 0){
            $orderGoodsData = $this->db->query_fetch("SELECT " . implode(", ", $orderGoodsCommonColumns) . " FROM " . DB_ORDER_GOODS . " WHERE orderNo = '" . $orderNo . "'");

            if(count($orderGoodsData) > 0){
                foreach($orderGoodsData as $key => $value){
                    $arrBind = $this->db->get_binding(DBTableField::tableOrderGoods(), $value, 'insert');
                    $arrBind['param'][] = 'claimStatus';
                    $arrBind['param'][] = 'claimSort';
                    $arrBind['param'][] = 'sno';
                    $this->db->bind_param_push($arrBind['bind'], 's', $claimStatus);
                    $this->db->bind_param_push($arrBind['bind'], 'i', $maxClaimSort+1);
                    $this->db->bind_param_push($arrBind['bind'], 'i', $value['sno']);
                    try {
                        $this->db->set_insert_db_query(DB_ORDER_GOODS_ORIGINAL, $arrBind['param'], $arrBind['bind'], 'y', $value['regDt']);
                        $resultArray[] = true;
                    } catch (Exception $e) {
                        $resultArray[] = false;
                    }

                    unset($arrBind);
                }
            }
        }

        // 주문상품배송 백업
        $orderDeliveryCommonColumns = $this->getCommonField(DB_ORDER_DELIVERY, DB_ORDER_DELIVERY_ORIGINAL);
        if(count($orderDeliveryCommonColumns) > 0){
            $orderDeliveryData = $this->db->query_fetch("SELECT " . implode(", ", $orderDeliveryCommonColumns) . " FROM " . DB_ORDER_DELIVERY . " WHERE orderNo = '" . $orderNo . "'");

            if(count($orderDeliveryData) > 0){
                foreach($orderDeliveryData as $key => $value){
                    $arrBind = $this->db->get_binding(DBTableField::tableOrderDelivery(), $value, 'insert');
                    $arrBind['param'][] = 'claimStatus';
                    $arrBind['param'][] = 'claimSort';
                    $arrBind['param'][] = 'sno';
                    $this->db->bind_param_push($arrBind['bind'], 's', $claimStatus);
                    $this->db->bind_param_push($arrBind['bind'], 'i', $maxClaimSort+1);
                    $this->db->bind_param_push($arrBind['bind'], 'i', $value['sno']);
                    try {
                        $this->db->set_insert_db_query(DB_ORDER_DELIVERY_ORIGINAL, $arrBind['param'], $arrBind['bind'], 'y', $value['regDt']);
                        $resultArray[] = true;
                    } catch (Exception $e) {
                        $resultArray[] = false;
                    }

                    unset($arrBind);
                }
            }
        }

        //order gift table 백업
        if($giftAction === true){
            $orderGiftCommonColumns = $this->getCommonField(DB_ORDER_GIFT, DB_ORDER_GIFT_ORIGINAL);
            if(count($orderGiftCommonColumns) > 0){
                $orderGiftData = $this->db->query_fetch("SELECT " . implode(", ", $orderGiftCommonColumns) . " FROM " . DB_ORDER_GIFT . " WHERE orderNo = '" . $orderNo . "'");

                if(count($orderGiftData) > 0){
                    foreach($orderGiftData as $key => $value){
                        $arrBind = $this->db->get_binding(DBTableField::tableOrderGift(), $value, 'insert');
                        $arrBind['param'][] = 'claimStatus';
                        $arrBind['param'][] = 'claimSort';
                        $arrBind['param'][] = 'sno';
                        $this->db->bind_param_push($arrBind['bind'], 's', $claimStatus);
                        $this->db->bind_param_push($arrBind['bind'], 'i', $maxClaimSort+1);
                        $this->db->bind_param_push($arrBind['bind'], 'i', $value['sno']);
                        try {
                            $this->db->set_insert_db_query(DB_ORDER_GIFT_ORIGINAL, $arrBind['param'], $arrBind['bind'], 'y', $value['regDt']);
                            $resultArray[] = true;
                        } catch (Exception $e) {
                            $resultArray[] = false;
                        }

                        unset($arrBind);
                    }
                }
            }
        }
        // order cash receipt 백업
        if($receiptAction === true){
            $orderCashReceiptCommonColumns = $this->getCommonField(DB_ORDER_CASH_RECEIPT, DB_ORDER_CASH_RECEIPT_ORIGINAL);
            if(count($orderCashReceiptCommonColumns) > 0){
                $orderCashReceiptData = $this->db->query_fetch("SELECT " . implode(", ", $orderCashReceiptCommonColumns) . " FROM " . DB_ORDER_CASH_RECEIPT . " WHERE orderNo = '" . $orderNo . "'");

                if(count($orderCashReceiptData) > 0){
                    foreach($orderCashReceiptData as $key => $value){
                        $arrBind = $this->db->get_binding(DBTableField::tableOrderCashReceipt(), $value, 'insert');
                        $arrBind['param'][] = 'claimStatus';
                        $arrBind['param'][] = 'claimSort';
                        $arrBind['param'][] = 'sno';
                        $this->db->bind_param_push($arrBind['bind'], 's', $claimStatus);
                        $this->db->bind_param_push($arrBind['bind'], 'i', $maxClaimSort+1);
                        $this->db->bind_param_push($arrBind['bind'], 'i', $value['sno']);
                        try {
                            $this->db->set_insert_db_query(DB_ORDER_CASH_RECEIPT_ORIGINAL, $arrBind['param'], $arrBind['bind'], 'y', $value['regDt']);
                            $resultArray[] = true;
                        } catch (Exception $e) {
                            $resultArray[] = false;
                        }

                        unset($arrBind);
                    }
                }
            }
        }
        // order statistics 백업
        if($orderStatisticsAction === true){
            $orderStatisticsCommonColumns = $this->getCommonField(DB_ORDER_SALES_STATISTICS, DB_ORDER_SALES_STATISTICS_ORIGINAL);
            if(count($orderStatisticsCommonColumns) > 0){
                $orderStatisticsData = $this->db->query_fetch("SELECT " . implode(", ", $orderStatisticsCommonColumns) . " FROM " . DB_ORDER_SALES_STATISTICS . " WHERE orderNo = '" . $orderNo . "'");

                if(count($orderStatisticsData) > 0){
                    $fieldTypes = [
                        ['val' => 'orderYMD', 'typ' => 'i', 'def' => null],
                        ['val' => 'mallSno', 'typ' => 'i', 'def' => 1],
                        ['val' => 'kind', 'typ' => 's', 'def' => 'order'],
                        ['val' => 'type', 'typ' => 's', 'def' => 'goods'],
                        ['val' => 'scmNo', 'typ' => 'i', 'def' => 1],
                        ['val' => 'relationSno', 'typ' => 'i', 'def' => null],
                        ['val' => 'orderIP', 'typ' => 's', 'def' => 0],
                        ['val' => 'orderNo', 'typ' => 's', 'def' => 0],
                        ['val' => 'purchaseNo', 'typ' => 'i', 'def' => null],
                        ['val' => 'memNo', 'typ' => 'i', 'def' => 0],
                        ['val' => 'goodsCnt', 'typ' => 'i', 'def' => 0],
                        ['val' => 'orderHour', 'typ' => 'i', 'def' => 24],
                        ['val' => 'orderDevice', 'typ' => 's', 'def' => 'pc'],
                        ['val' => 'orderMemberFl', 'typ' => 's', 'def' => 'y'],
                        ['val' => 'orderTaxFl', 'typ' => 's', 'def' => 'y'],
                        ['val' => 'orderGender', 'typ' => 's', 'def' => 'etc'],
                        ['val' => 'orderAge', 'typ' => 'i', 'def' => 0],
                        ['val' => 'orderArea', 'typ' => 's', 'def' => null],
                        ['val' => 'orderSettleKind', 'typ' => 's', 'def' => null],
                        ['val' => 'goodsPrice', 'typ' => 'd', 'def' => 0],
                        ['val' => 'costPrice', 'typ' => 'd', 'def' => 0],
                        ['val' => 'goodsDcPrice', 'typ' => 'd', 'def' => 0],
                        ['val' => 'divisionUseDeposit', 'typ' => 'd', 'def' => 0],
                        ['val' => 'divisionUseMileage', 'typ' => 'd', 'def' => 0],
                        ['val' => 'deliveryPrice', 'typ' => 'd', 'def' => 0],
                        ['val' => 'deliveryDcPrice', 'typ' => 'd', 'def' => 0],
                        ['val' => 'divisionDeliveryUseDeposit', 'typ' => 'd', 'def' => 0],
                        ['val' => 'divisionDeliveryUseMileage', 'typ' => 'd', 'def' => 0],
                        ['val' => 'refundGoodsPrice', 'typ' => 'd', 'def' => 0],
                        ['val' => 'refundDeliveryPrice', 'typ' => 'd', 'def' => 0],
                        ['val' => 'refundUseDeposit', 'typ' => 'd', 'def' => 0],
                        ['val' => 'refundUseMileage', 'typ' => 'd', 'def' => 0],
                        ['val' => 'refundFeePrice', 'typ' => 'd', 'def' => 0],
                        ['val' => 'claimStatus', 'typ' => 's', 'def' => null],
                        ['val' => 'claimSort', 'typ' => 'i', 'def' => 1],
                    ];

                    foreach($orderStatisticsData as $key => $value){
                        $value['claimStatus'] = $claimStatus;
                        $value['claimSort'] = $maxClaimSort+1;
                        $arrBind = $this->db->get_binding($fieldTypes, $value, 'insert');

                        try {
                            $this->db->set_insert_db_query(DB_ORDER_SALES_STATISTICS_ORIGINAL, $arrBind['param'], $arrBind['bind'], 'y', $value['regDt']);
                            $resultArray[] = true;
                        } catch (Exception $e) {
                            $resultArray[] = false;
                        }

                        unset($arrBind);
                    }
                }
            }
        }

        if(in_array(false, $resultArray)){
            return false;
        }

        return true;
    }

    /**
     *
     * 주문정보 복원
     * @param string $orderNo 주문번호
     * @param string $claimStatus 복구할 모드
     * @param boolean $giftAction 사은품 복원 처리 여부
     * @param boolean $receiptAction 현금영수증 복원 처리 여부
     * @param boolean $orderStatisticsAction 주문통계 복원 처리 여부
     *
     * @return boolean $return 성공여부
     */
    public function restoreOrderOriginalData($orderNo, $claimStatus, $giftAction=false, $receiptAction=false, $orderStatisticsAction=false)
    {
        $resultArray = [];

        $where = " WHERE orderNo = '" . $orderNo . "' AND claimStatus='" . $claimStatus . "' ";

        // 주문복원
        $orderCommonColumns = $this->getCommonField(DB_ORDER, DB_ORDER_ORIGINAL);
        if(count($orderCommonColumns) > 0){
            $orderOriginalData = $this->db->query_fetch("SELECT " . implode(", ", $orderCommonColumns) . " FROM " . DB_ORDER_ORIGINAL . $where);

            if(count($orderOriginalData) > 0){
                foreach($orderOriginalData as $key => $value){
                    $arrBind = $this->db->get_binding(DBTableField::tableOrder(), $value, 'insert');
                    try {
                        $this->db->set_insert_db_query(DB_ORDER, $arrBind['param'], $arrBind['bind'], 'y', $value['regDt']);
                        $resultArray[] = true;
                    } catch (Exception $e) {
                        $resultArray[] = false;
                    }

                    unset($arrBind);
                }
            }
        }

        // 주문상품복원
        $orderGoodsCommonColumns = $this->getCommonField(DB_ORDER_GOODS, DB_ORDER_GOODS_ORIGINAL);
        if(count($orderGoodsCommonColumns) > 0){
            $orderGoodsOriginalData = $this->db->query_fetch("SELECT " . implode(", ", $orderGoodsCommonColumns) . " FROM " . DB_ORDER_GOODS_ORIGINAL . $where);

            if(count($orderGoodsOriginalData) > 0){
                foreach($orderGoodsOriginalData as $key => $value){
                    // JSON 타입이 es_orderGoodsOriginal 테이블에 insert될 때 addslash가 되며
                    // es_orderGoods 테이블에 복원 될 때 addslash가 되고 있어서 임시 방편으로 아래 코드 추가(stripslashes 2회 실행)
                    foreach($value as $key2 => $value2){
                        if($key2 === 'optionInfo' || $key2 === 'cateAllCd'){
                            $value[$key2] = stripslashes(stripslashes($value2));
                        }
                    }

                    $arrBind = $this->db->get_binding(DBTableField::tableOrderGoods(), $value, 'insert');
                    $arrBind['param'][] = 'sno';
                    $this->db->bind_param_push($arrBind['bind'], 'i', $value['sno']);
                    try {
                        $this->db->set_insert_db_query(DB_ORDER_GOODS, $arrBind['param'], $arrBind['bind'], 'y', $value['regDt']);
                        $resultArray[] = true;
                    } catch (Exception $e) {
                        $resultArray[] = false;
                    }

                    unset($arrBind);
                }
            }
        }

        // 주문상품배송 백업
        $orderDeliveryCommonColumns = $this->getCommonField(DB_ORDER_DELIVERY, DB_ORDER_DELIVERY_ORIGINAL);
        if(count($orderDeliveryCommonColumns) > 0){
            $orderDeliveryOriginalData = $this->db->query_fetch("SELECT " . implode(", ", $orderDeliveryCommonColumns) . " FROM " . DB_ORDER_DELIVERY_ORIGINAL . $where);

            if(count($orderDeliveryOriginalData) > 0){
                foreach($orderDeliveryOriginalData as $key => $value){
                    $arrBind = $this->db->get_binding(DBTableField::tableOrderDelivery(), $value, 'insert');
                    $arrBind['param'][] = 'sno';
                    $this->db->bind_param_push($arrBind['bind'], 'i', $value['sno']);
                    try {
                        $this->db->set_insert_db_query(DB_ORDER_DELIVERY, $arrBind['param'], $arrBind['bind'], 'y', $value['regDt']);
                        $resultArray[] = true;
                    } catch (Exception $e) {
                        $resultArray[] = false;
                    }

                    unset($arrBind);
                }
            }
        }

        //order gift table 복구
        if($giftAction === true){
            $orderGiftCommonColumns = $this->getCommonField(DB_ORDER_GIFT, DB_ORDER_GIFT_ORIGINAL);
            if(count($orderGiftCommonColumns) > 0){
                $orderGiftOriginalData = $this->db->query_fetch("SELECT " . implode(", ", $orderGiftCommonColumns) . " FROM " . DB_ORDER_GIFT_ORIGINAL . $where);

                if(count($orderGiftOriginalData) > 0){
                    foreach($orderGiftOriginalData as $key => $value){
                        $arrBind = $this->db->get_binding(DBTableField::tableOrderGift(), $value, 'insert');
                        $arrBind['param'][] = 'sno';
                        $this->db->bind_param_push($arrBind['bind'], 'i', $value['sno']);
                        try {
                            $this->db->set_insert_db_query(DB_ORDER_GIFT, $arrBind['param'], $arrBind['bind'], 'y', $value['regDt']);
                            $resultArray[] = true;
                        } catch (Exception $e) {
                            $resultArray[] = false;
                        }

                        unset($arrBind);
                    }
                }
            }
        }
        //order cash receipt table 복구
        if($receiptAction === true){
            $orderCashReceiptCommonColumns = $this->getCommonField(DB_ORDER_CASH_RECEIPT, DB_ORDER_CASH_RECEIPT_ORIGINAL);
            if(count($orderCashReceiptCommonColumns) > 0){
                $orderCashReceiptOriginalData = $this->db->query_fetch("SELECT " . implode(", ", $orderCashReceiptCommonColumns) . " FROM " . DB_ORDER_CASH_RECEIPT_ORIGINAL . $where);

                if(count($orderCashReceiptOriginalData) > 0){
                    foreach($orderCashReceiptOriginalData as $key => $value){
                        $arrBind = $this->db->get_binding(DBTableField::tableOrderCashReceipt(), $value, 'insert');
                        $arrBind['param'][] = 'sno';
                        $this->db->bind_param_push($arrBind['bind'], 'i', $value['sno']);
                        try {
                            $this->db->set_insert_db_query(DB_ORDER_CASH_RECEIPT, $arrBind['param'], $arrBind['bind'], 'y', $value['regDt']);
                            $resultArray[] = true;
                        } catch (Exception $e) {
                            $resultArray[] = false;
                        }

                        unset($arrBind);
                    }
                }
            }
        }
        //order statistics table 복구
        if($orderStatisticsAction === true){
            $orderStatisticsCommonColumns = $this->getCommonField(DB_ORDER_SALES_STATISTICS, DB_ORDER_SALES_STATISTICS_ORIGINAL);
            if(count($orderStatisticsCommonColumns) > 0){
                $orderStatisticsOriginalData = $this->db->query_fetch("SELECT " . implode(", ", $orderStatisticsCommonColumns) . " FROM " . DB_ORDER_SALES_STATISTICS_ORIGINAL . $where);

                if(count($orderStatisticsOriginalData) > 0){
                    $fieldTypes = [
                        ['val' => 'orderYMD', 'typ' => 'i', 'def' => null],
                        ['val' => 'mallSno', 'typ' => 'i', 'def' => 1],
                        ['val' => 'kind', 'typ' => 's', 'def' => 'order'],
                        ['val' => 'type', 'typ' => 's', 'def' => 'goods'],
                        ['val' => 'scmNo', 'typ' => 'i', 'def' => 1],
                        ['val' => 'relationSno', 'typ' => 'i', 'def' => null],
                        ['val' => 'orderIP', 'typ' => 's', 'def' => 0],
                        ['val' => 'orderNo', 'typ' => 's', 'def' => 0],
                        ['val' => 'purchaseNo', 'typ' => 'i', 'def' => null],
                        ['val' => 'memNo', 'typ' => 'i', 'def' => 0],
                        ['val' => 'goodsCnt', 'typ' => 'i', 'def' => 0],
                        ['val' => 'orderHour', 'typ' => 'i', 'def' => 24],
                        ['val' => 'orderDevice', 'typ' => 's', 'def' => 'pc'],
                        ['val' => 'orderMemberFl', 'typ' => 's', 'def' => 'y'],
                        ['val' => 'orderTaxFl', 'typ' => 's', 'def' => 'y'],
                        ['val' => 'orderGender', 'typ' => 's', 'def' => 'etc'],
                        ['val' => 'orderAge', 'typ' => 'i', 'def' => 0],
                        ['val' => 'orderArea', 'typ' => 's', 'def' => null],
                        ['val' => 'orderSettleKind', 'typ' => 's', 'def' => null],
                        ['val' => 'goodsPrice', 'typ' => 'd', 'def' => 0],
                        ['val' => 'costPrice', 'typ' => 'd', 'def' => 0],
                        ['val' => 'goodsDcPrice', 'typ' => 'd', 'def' => 0],
                        ['val' => 'divisionUseDeposit', 'typ' => 'd', 'def' => 0],
                        ['val' => 'divisionUseMileage', 'typ' => 'd', 'def' => 0],
                        ['val' => 'divisionDeliveryUseDeposit', 'typ' => 'd', 'def' => 0],
                        ['val' => 'divisionDeliveryUseMileage', 'typ' => 'd', 'def' => 0],
                        ['val' => 'deliveryPrice', 'typ' => 'd', 'def' => 0],
                        ['val' => 'deliveryDcPrice', 'typ' => 'd', 'def' => 0],
                        ['val' => 'refundGoodsPrice', 'typ' => 'd', 'def' => 0],
                        ['val' => 'refundDeliveryPrice', 'typ' => 'd', 'def' => 0],
                        ['val' => 'refundUseDeposit', 'typ' => 'd', 'def' => 0],
                        ['val' => 'refundUseMileage', 'typ' => 'd', 'def' => 0],
                        ['val' => 'refundFeePrice', 'typ' => 'd', 'def' => 0],
                    ];
                    foreach($orderStatisticsOriginalData as $key => $value){
                        $arrBind = $this->db->get_binding($fieldTypes, $value, 'insert');
                        $arrBind['param'][] = 'sno';
                        $this->db->bind_param_push($arrBind['bind'], 'i', $value['sno']);
                        try {
                            $this->db->set_insert_db_query(DB_ORDER_SALES_STATISTICS, $arrBind['param'], $arrBind['bind'], 'y', $value['regDt']);
                            $resultArray[] = true;
                        } catch (Exception $e) {
                            $resultArray[] = false;
                        }

                        unset($arrBind);
                    }
                }
            }
        }

        if(in_array(false, $resultArray)){
            return false;
        }

        return true;
    }

    /**
     * getOrderProcessLogArrData
     * 로그기록할 데이터 조합
     * @param string $orderNo 주문번호
     * @param string $type 타입
     * @param string $memo 메모
     * @param array $orderData 주문데이터
     *
     * @return array $processLogData
     */
    public function getOrderProcessLogArrData($orderNo, $type, $memo, $orderData)
    {
        $processLogData = [
            'orderNo' => $orderNo,// 주문번호
            'type' => $type, //타입 (setOrderProcessLog의 payHistoryType 참조)
            'settlePrice' => $orderData['settlePrice'],// 실결제금액
            'totalGoodsPrice' => $orderData['totalGoodsPrice'],// 상품판매금액
            'totalDeliveryCharge' => $orderData['totalDeliveryCharge'],// 배송비 (지역별배송비 포함)
            'totalDeliveryInsuranceFee' => $orderData['totalDeliveryInsuranceFee'],// 해외배송보험료
            'totalGoodsDcPrice' => $orderData['totalGoodsDcPrice'],// 상품할인
            'totalMemberDcPrice' => $orderData['totalMemberDcPrice'],// 회원추가할인(상품)
            'totalMemberOverlapDcPrice' => $orderData['totalMemberOverlapDcPrice'],// 회원중복할인(상품)
            'totalMemberDeliveryDcPrice' => $orderData['totalMemberDeliveryDcPrice'],// 회원할인(배송비)
            'totalCouponGoodsDcPrice' => $orderData['totalCouponGoodsDcPrice'],// 쿠폰할인(상품)
            'totalCouponOrderDcPrice' => $orderData['totalCouponOrderDcPrice'],// 쿠폰할인(주문)
            'totalCouponDeliveryDcPrice' => $orderData['totalCouponDeliveryDcPrice'],// 쿠폰할인(배송비)
            'useDeposit' => $orderData['useDeposit'],// 예치금 useDeposit
            'useMileage' => $orderData['useMileage'],// 마일리지 useMileage
            'totalMileage' => $orderData['totalMileage'],// 총적립금액 totalMileage
            'memo' => $memo, //설명
        ];

        // 마이앱할인(상품)
        if ($this->myappUseFl) {
            $processLogData['totalMyappDcPrice'] = $orderData['totalMyappDcPrice'];
        }

        return $processLogData;
    }

    /**
     * setOrderProcessLog
     * 로그기록
     * @param array $data DB에 담을 데이터 (getOrderProcessLogArrData 참조)
     *
     * @return void
     */
    public function setOrderProcessLog($data)
    {
        $payHistoryType = [
            'fs', //최초결제
            'pc', //부분취소
            'ac', //전체취소
            'ag', //상품추가
            'pr', //부분환불
            'ar', //전체환불
            'rt', //복원
            'se', //동일상품교환
            'ae', //타상품교환
        ];

        if (!in_array($data['type'], $payHistoryType)) {
            return false;
        }

        $compareField = array_keys($data);
        $arrBind = $this->db->get_binding(DBTableField::tableOrderPayHistory2(), $data, 'insert', $compareField);
        $this->db->set_insert_db(DB_ORDER_PAY_HISTORY2, $arrBind['param'], $arrBind['bind'], 'y');
        unset($arrBind);
    }

    /**
     * deleteOrderOriginalData
     * 최종/최초 주문정보 삭제
     * @param string $deleteType 삭제될타입 [default 최종, original 최초]
     * @param string $orderNo 주문번호
     * @param boolean $giftAction 사은품 삭제 처리 여부
     * @param boolean $receiptAction 현금영수증 삭제 처리 여부
     * @param boolean $orderStatisticsAction 주문통계 삭제 처리 여부
     *
     * @return boolean $return 삭제여부
     */
    public function deleteOrderData($deleteType, $orderNo, $giftAction=false, $receiptAction=false, $orderStatisticsAction=false)
    {
        $return = true;
        $arrBind = [];
        $this->db->bind_param_push($arrBind, 's', $orderNo);

        if ($deleteType === 'default') {
            //기존 주문정보 데이터
            $dbOrderTable = DB_ORDER;
            $dbOrderGoodsTable = DB_ORDER_GOODS;
            $dbOrderDeliveryTable = DB_ORDER_DELIVERY;
            if($giftAction === true){
                $dbOrderGiftTable = DB_ORDER_GIFT;
            }
            if($receiptAction === true){
                $dbOrderCashReceiptTable = DB_ORDER_CASH_RECEIPT;
            }
            if($orderStatisticsAction === true){
                $dbOrderStatisticsTable = DB_ORDER_SALES_STATISTICS;
            }
            $where = 'orderNo = ?';
        } else if ($deleteType === 'original') {
            //최초 주문정보 데이터
            $dbOrderTable = DB_ORDER_ORIGINAL;
            $dbOrderGoodsTable = DB_ORDER_GOODS_ORIGINAL;
            $dbOrderDeliveryTable = DB_ORDER_DELIVERY_ORIGINAL;
            if($giftAction === true){
                $dbOrderGiftTable = DB_ORDER_GIFT_ORIGINAL;
            }
            if($receiptAction === true){
                $dbOrderCashReceiptTable = DB_ORDER_CASH_RECEIPT_ORIGINAL;
            }
            if($orderStatisticsAction === true){
                $dbOrderStatisticsTable = DB_ORDER_SALES_STATISTICS_ORIGINAL;
            }
            // 환불의 경우는 복원을 위한 백업이 아닌 로그성 백업이므로 삭제하지 않는다.
            $where = "orderNo = ? AND claimStatus != 'r'";
        } else {
        }

        $orderResult = $this->db->set_delete_db($dbOrderTable, $where, $arrBind);
        $orderGoodsResult = $this->db->set_delete_db($dbOrderGoodsTable, $where, $arrBind);
        $orderDeliveryResult = $this->db->set_delete_db($dbOrderDeliveryTable, $where, $arrBind);
        if($giftAction === true){
            $orderGiftResult = $this->db->set_delete_db($dbOrderGiftTable, $where, $arrBind);
        }
        if($receiptAction === true){
            $orderCashReceiptResult = $this->db->set_delete_db($dbOrderCashReceiptTable, $where, $arrBind);
        }
        if($orderStatisticsAction === true){
            $orderStatisticsResult = $this->db->set_delete_db($dbOrderStatisticsTable, $where, $arrBind);
        }

        // 사은품은 없을 수 있으므로 체크 제외
        if ((!$orderResult) || (!$orderGoodsResult) || (!$orderDeliveryResult)) {
            $return = false;
        }

        return $return;
    }

    /**
     * user handle 수정
     *
     * @param array $updateData 업데이트될 데이터
     * @param string $orderNo 주문번호
     * @param string $sno user handle sno
     * @param string $userHandleMode 처리모드
     *
     * @return void
     */
    public function updateOrderUserHandle($updateData, $orderNo, $sno, $userHandleMode=null)
    {
        if (empty($updateData['userRefundAccountNumber']) === false) {
            $updateData['userRefundAccountNumber'] = \Encryptor::encrypt($updateData['userRefundAccountNumber']);
        }
        // 처리자
        $updateData['managerNo'] = Session::get('manager.sno');
        $compareField = array_keys($updateData);
        $arrBind = $this->db->get_binding(DBTableField::tableOrderUserHandle(), $updateData, 'update', $compareField);
        $arrWhere = 'orderNo = ? AND sno = ?';
        $this->db->bind_param_push($arrBind['bind'], 's', $orderNo);
        $this->db->bind_param_push($arrBind['bind'], 'i', $sno);
        if($userHandleMode !== null){
            $arrWhere .= ' AND userHandleMode = ?';
            $this->db->bind_param_push($arrBind['bind'], 's', $userHandleMode);
        }
        $this->db->set_update_db(DB_ORDER_USER_HANDLE, $arrBind['param'], $arrWhere, $arrBind['bind']);
        unset($arrBind, $updateData);
    }

    /*
     * 상품상세페이지에서의 운영자 추가할인 설정
     *
     * @param array $postData post parameter
     *
     * @return void
     */
    public function setEnuriOrderView($postData)
    {
        if(count($postData['enuri']) > 0) {
            //주문건 업데이트 여부 flag 값
            $orderUpdateCheck = false;

            //기존 주문건 정보
            $orderData = $this->getOrderData($postData['orderNo']);
            if(count($orderData) < 1){
                throw new Exception(__('운영자 추가 할인 실행을 실패하였습니다.[주문 확인 실패]'));
            }

            foreach ($postData['enuri'] as $orderGoodsSno => $enuriValue) {
                $goodsTax = $goodsTaxData = [];
                $orderGoodsSettlePrice = $newOrderGoodsSettlePrice = 0;

                //주문상품정보
                $orderGoodsData = $this->getOrderGoodsData($postData['orderNo'], $orderGoodsSno)[0];
                //입금대기 상태만 정보 변경
                if (substr($orderGoodsData['orderStatus'], 0, 1) !== 'o') {
                    continue;
                }

                //상품의 tax 정보
                $goodsTax = explode(STR_DIVISION, $orderGoodsData['goodsTaxInfo']);
                //상품결제가에서 운영자 추가 할인 금액 차감
                $orderGoodsSettlePrice = $orderGoodsData['taxSupplyGoodsPrice'] + $orderGoodsData['taxVatGoodsPrice'] + $orderGoodsData['taxFreeGoodsPrice'];
                if((int)$orderGoodsSettlePrice+$orderGoodsData['enuri'] < $enuriValue){
                    throw new Exception(__('운영자 추가 할인 실행을 실패하였습니다.[상품결제가보다 금액이 큼]'));
                }
                $newOrderGoodsSettlePrice = ($orderGoodsSettlePrice + $orderGoodsData['enuri']) - (int)$enuriValue;

                //tax price
                $goodsTaxData = NumberUtils::taxAll($newOrderGoodsSettlePrice, $goodsTax[1], $goodsTax[0]);
                if ($goodsTax[0] === 't') {
                    $updateOrderGoodsData = [
                        'taxSupplyGoodsPrice' => gd_isset($goodsTaxData['supply'], 0),
                        'taxVatGoodsPrice' => gd_isset($goodsTaxData['tax'], 0),
                        'realTaxSupplyGoodsPrice' => gd_isset($goodsTaxData['supply'], 0),
                        'realTaxVatGoodsPrice' => gd_isset($goodsTaxData['tax'], 0),
                        'enuri' => $enuriValue,
                    ];
                } else {
                    $updateOrderGoodsData = [
                        'taxFreeGoodsPrice' => gd_isset($goodsTaxData['supply'], 0),
                        'realTaxFreeGoodsPrice' => gd_isset($goodsTaxData['supply'], 0),
                        'enuri' => $enuriValue,
                    ];
                }

                $res = $this->updateOrderGoods($updateOrderGoodsData, $postData['orderNo'], $orderGoodsSno);
                if ($res) {
                    $orderUpdateCheck = true;

                    //업데이트 된 주문상품 데이터의 기존 정보
                    $beforeOrderGoodsData[] = [
                        'taxSupplyGoodsPrice' => $orderGoodsData['taxSupplyGoodsPrice'],
                        'taxVatGoodsPrice' => $orderGoodsData['taxVatGoodsPrice'],
                        'taxFreeGoodsPrice' => $orderGoodsData['taxFreeGoodsPrice'],
                        'enuri' => $orderGoodsData['enuri'],
                        'goodsSettlePrice' => $orderGoodsSettlePrice,
                    ];
                    //업데이트 된 주문상품 데이터의 변경된 정보
                    $afterOrderGoodsData[] = [
                        'taxSupplyGoodsPrice' => gd_isset($updateOrderGoodsData['taxSupplyGoodsPrice'], 0),
                        'taxVatGoodsPrice' => gd_isset($updateOrderGoodsData['taxVatGoodsPrice'], 0),
                        'taxFreeGoodsPrice' => gd_isset($updateOrderGoodsData['taxFreeGoodsPrice'], 0),
                        'enuri' => (int)$enuriValue,
                        'goodsSettlePrice' => $newOrderGoodsSettlePrice,
                    ];
                }
            }


            //주문서 업데이트
            if ($orderUpdateCheck === true) {
                $beforeTaxSupplyGoodsPrice = array_sum(array_column($beforeOrderGoodsData, 'taxSupplyGoodsPrice'));
                $afterTaxSupplyGoodsPrice = array_sum(array_column($afterOrderGoodsData, 'taxSupplyGoodsPrice'));
                $taxSupplyPrice = $orderData['taxSupplyPrice'] - $beforeTaxSupplyGoodsPrice + $afterTaxSupplyGoodsPrice;

                $beforeTaxVatGoodsPrice = array_sum(array_column($beforeOrderGoodsData, 'taxVatGoodsPrice'));
                $afterTaxVatGoodsPrice = array_sum(array_column($afterOrderGoodsData, 'taxVatGoodsPrice'));
                $taxVatPrice = $orderData['taxVatPrice'] - $beforeTaxVatGoodsPrice + $afterTaxVatGoodsPrice;

                $beforeTaxFreeGoodsPrice = array_sum(array_column($beforeOrderGoodsData, 'taxFreeGoodsPrice'));
                $afterTaxFreeGoodsPrice = array_sum(array_column($afterOrderGoodsData, 'taxFreeGoodsPrice'));
                $taxFreePrice = $orderData['taxFreePrice'] - $beforeTaxFreeGoodsPrice + $afterTaxFreeGoodsPrice;

                $beforeEnuri = array_sum(array_column($beforeOrderGoodsData, 'enuri'));
                $afterEnuri = array_sum(array_column($afterOrderGoodsData, 'enuri'));
                $totalEnuriDcPrice = $orderData['totalEnuriDcPrice'] - $beforeEnuri + $afterEnuri;

                $beforeSettlePrice = array_sum(array_column($beforeOrderGoodsData, 'goodsSettlePrice'));
                $afterSettlePrice = array_sum(array_column($afterOrderGoodsData, 'goodsSettlePrice'));
                $settlePrice = $orderData['settlePrice'] - $beforeSettlePrice + $afterSettlePrice;

                // 현금영수증 저장 설정 - 세금계산서는 신청시 금액 안들어감 - 발행시 들어감
                if ($orderData['receiptFl'] == 'r') {
                    // 주문서 저장 설정
                    $receipt['settlePrice'] = $settlePrice;
                    $receipt['supplyPrice'] = $taxSupplyPrice;
                    $receipt['taxPrice'] = $taxVatPrice;
                    $receipt['freePrice'] = $taxFreePrice;

                    // TODO: 현금영수증이 발급요청이고 + 주문상태가 입금대기상태의 주문이 있을경우 발급금액 자동 변경 by sueun
                    $orderCashReceiptData = $this->getOrderCashReceiptData($postData['orderNo']);

                    // 현금영수증 저장
                    if(empty($orderCashReceiptData) === false) {
                        $compareField = array_keys($receipt);
                        $arrBind = $this->db->get_binding(DBTableField::tableOrderCashReceipt(), $receipt, 'update', $compareField);
                        $arrWhere = 'orderNo = ?';
                        $this->db->bind_param_push($arrBind['bind'], 's', $postData['orderNo']);
                        $this->db->set_update_db(DB_ORDER_CASH_RECEIPT, $arrBind['param'], $arrWhere, $arrBind['bind']);
                        unset($arrBind, $receipt);
                    }
                }

                $updateOrderData = [
                    'taxSupplyPrice' => gd_isset($taxSupplyPrice, 0),
                    'taxVatPrice' => gd_isset($taxVatPrice, 0),
                    'taxFreePrice' => gd_isset($taxFreePrice, 0),
                    'realTaxSupplyPrice' => gd_isset($taxSupplyPrice, 0),
                    'realTaxVatPrice' => gd_isset($taxVatPrice, 0),
                    'realTaxFreePrice' => gd_isset($taxFreePrice, 0),
                    'totalEnuriDcPrice' => gd_isset($totalEnuriDcPrice, 0),
                    'settlePrice' => $settlePrice,
                ];
                $updateRes = $this->updateOrder($postData['orderNo'], 'n', $updateOrderData);
                if(!$updateRes){
                    throw new Exception(__('운영자 추가 할인 실행을 실패하였습니다.[주문 업데이트 실패]'));
                }
            }
        }

        $logger = \App::getInstance('logger');
        $logger->channel('order')->info('주문상세페이지에서 운영자추가할인 추가 ; 처리자 - ' . \Session::get('manager.managerId') . ' ; [' . $postData['orderNo'] . ']');

        return true;
    }

    /*
     * 묶음배송 처리
     *
     * @param array $postData post parameter
     *
     * @return boolean
     */
    public function setPacket($postData)
    {
        $orderNoArr = [];

        //묶음배송 코드
        $packetCode = $this->getPacketCode();

        if(trim($postData['orderNoStr']) === ''){
            throw new Exception(__('묶음배송 추가를 실패하였습니다.[주문번호 확인실패]'));
        }

        $orderNoArr = array_values(array_unique(array_filter(explode(INT_DIVISION, $postData['orderNoStr']))));

        if(count($orderNoArr) < 0){
            throw new Exception(__('묶음배송 추가를 실패하였습니다.[주문번호 확인실패]'));
        }

        $arrBind = [];
        $this->db->bind_param_push($arrBind, 's', $packetCode);
        $this->db->set_update_db_query(DB_ORDER_INFO, 'packetCode = ?', "orderNo IN ('".implode("','", $orderNoArr)."')", $arrBind);

        $logger = \App::getInstance('logger');
        $logger->channel('order')->info('묶음배송 처리 ; 처리자 - ' . \Session::get('manager.managerId') . ' ; [' . implode(",", $orderNoArr) . ']');

        return true;
    }

    /*
     * 묶음배송코드 생성
     *
     * @param void
     *
     * @return string $packetCode
     */
    public function getPacketCode()
    {
        $packetCode = '';

        // 0 ~ 999 마이크로초 중 랜덤으로 sleep 처리 (동일 시간에 들어온 경우 중복을 막기 위해서.)
        usleep(mt_rand(0, 999));
        // 0 ~ 99 마이크로초 중 랜덤으로 sleep 처리 (첫번째 sleep 이 또 동일한 경우 중복을 막기 위해서.)
        usleep(mt_rand(0, 99));
        // microtime() 함수의 마이크로 초만 사용
        list($usec) = explode(' ', microtime());
        // 마이크로초을 4자리 정수로 만듬 (마이크로초 뒤 2자리는 거의 0이 나오므로 8자리가 아닌 4자리만 사용함 - 나머지 2자리도 짜름... 너무 길어서.)
        $tmpNo = sprintf('%04d', round($usec * 10000));

        $packetCode = 'P' . date("Ymd") . $tmpNo;

        return $packetCode;
    }

    /*
     * 묶음배송 해지
     *
     * @param array $postData post parameter
     *
     * @return boolean
     */
    public function unsetPacket($postData)
    {
        $orderNoArr = $packetCodeArr = [];

        if(trim($postData['orderNoStr']) === ''){
            throw new Exception(__('묶음배송 해제를 실패하였습니다.[주문번호 확인실패]'));
        }
        $orderNoArr = array_values(array_unique(array_filter(explode(INT_DIVISION, $postData['orderNoStr']))));
        if(count($orderNoArr) < 0){
            throw new Exception(__('묶음배송 해제를 실패하였습니다.[주문번호 확인실패]'));
        }

        $arrBind = [];
        foreach ($orderNoArr as $val) {
            $arrBind['param'][] = '?';
            $this->db->bind_param_push($arrBind['bind'], 's', $val);
        }
        $strSQL = 'SELECT packetCode FROM ' . DB_ORDER_INFO . ' WHERE orderNo IN (' . implode(',',$arrBind['param']) . ')';
        $getData = $this->db->query_fetch($strSQL, $arrBind['bind']);

        if(count($getData) < 1){
            throw new Exception(__('묶음배송 해제를 실패하였습니다.[주문건 미존재]'));
        }
        $arrBind = null;
        unset($arrBind);
        $packetCodeArr = array_column($getData, 'packetCode');
        $packetCodeArr = array_values(array_unique(array_filter($packetCodeArr)));

        //공급사일 경우 다른 공급사의 주문상품이 있는지 확인 후 exception 처리
        if(Manager::isProvider()){
            $scmNo = \Session::get('manager.scmNo');
            $checkUnsetAble = true;
            if(count($packetCodeArr) > 0){
                foreach($packetCodeArr as $key => $packetCode){
                    $tmpPacketOrderNoArr[] = $this->getPacketOrderList($packetCode);
                }
                $packetOrderNoArr = array_unique(array_reduce($tmpPacketOrderNoArr, function ($a, $p) { return array_merge((array)$a, (array)$p); }, array()));

                //주문건의 주문상품을 확인
                if(count($packetOrderNoArr) > 0) {
                    foreach ($packetOrderNoArr as $pKey => $packetOrderNo) {
                        $scmNoArr = $orderGoodsData = [];
                        $orderGoodsData = $this->getOrderGoodsData($packetOrderNo);
                        $scmNoArr = array_unique(array_filter(array_column($orderGoodsData, 'scmNo')));
                        if (count($scmNoArr) > 0) {
                            foreach ($scmNoArr as $sKey => $sVal) {
                                if ((int)$scmNo !== (int)DEFAULT_CODE_SCMNO && (int)$scmNo !== (int)$sVal) {
                                    $checkUnsetAble = false;
                                    break;
                                }
                            }
                        }
                        if ($checkUnsetAble === false) {
                            break;
                        }
                    }
                }
            }
            if($checkUnsetAble === false){
                throw new Exception(__('다른 공급사 주문상품이 존재하여 묶음배송 해제를 할 수 없습니다.'));
            }
        }

        //업데이트
        if(count($packetCodeArr) > 0){
            $arrBind = [];
            $this->db->bind_param_push($arrBind, 's', '');
            $this->db->set_update_db_query(DB_ORDER_INFO, 'packetCode = ?', "packetCode IN ('".implode("','", $packetCodeArr)."')", $arrBind);
            unset($arrBind);
        }
        else {
            throw new Exception(__('묶음배송이 되어있지 않은 주문입니다.'));
        }

        $logger = \App::getInstance('logger');
        $logger->channel('order')->info('묶음배송 해제 ; 처리자 - ' . \Session::get('manager.managerId') . ' ; [' . implode("','", $packetCodeArr) . ']');

        return true;
    }

    /*
     * 한 묶음의 주문정보를 리턴
     *
     * @param string $packetCode
     *
     * @return array $orderList
     */
    public function getPacketOrderList($packetCode)
    {
        $arrBind = $orderList = [];
        $this->db->bind_param_push($arrBind, 's', $packetCode);
        $query = "SELECT * FROM " . DB_ORDER_INFO . " WHERE packetCode = ?";
        $data = $this->db->query_fetch($query, $arrBind, false);
        $orderList = array_values(array_column($data, 'orderNo'));

        return $orderList;
    }

    /*
     * 교환 사유 수정
     *
     * @param array $postValue post parameter
     *
     * @return boolean
     */
    public function updateOrderHandleReason($postValue)
    {
        $updateData = [];
        if($postValue['handleReason']){
            $updateData['handleReason'] = $postValue['handleReason'];
        }
        if($postValue['handleDetailReason']){
            $updateData['handleDetailReason'] = $postValue['handleDetailReason'];
        }
        if($postValue['refundMethod']){
            $updateData['refundMethod'] = $postValue['refundMethod'];
        }
        if($postValue['refundBankName']){
            $updateData['refundBankName'] = $postValue['refundBankName'];
        }
        if($postValue['refundAccountNumber']){
            $updateData['refundAccountNumber'] = $postValue['refundAccountNumber'];
        }
        if($postValue['refundDepositor']){
            $updateData['refundDepositor'] = $postValue['refundDepositor'];
        }
        $res = $this->updateOrderHandle($updateData, $postValue['handleSno']);

        return $res;
    }

    /**
     * getOrderNameChange
     * 주문 상품 대표 이름을 orderGoodsNm / orderGoodsNmStandard
     *
     * @param string $orderNo
     * @param boolean $standardType
     * @return string
     */
    public function getOrderNameChange($orderNo, $standardType = false)
    {
        $getOrderGoodsData = $this->getOrderGoodsStatusData($orderNo);
        if ($standardType === true) {
            $goodsNm = $getOrderGoodsData[0]['goodsNmStandard'];
        } else {
            $goodsNm = $getOrderGoodsData[0]['goodsNm'];
        }
        $countOrderGoods = count($getOrderGoodsData);
        if ($countOrderGoods > 1) {
            $returnOrderGoodsNm = $goodsNm . ' ' . __('외') . ' ' . ($countOrderGoods - 1) . ' ' . __('건');
        } else {
            $returnOrderGoodsNm = $goodsNm;
        }

        return $returnOrderGoodsNm;
    }

    /*
     * getOrderDeliveryResetData
     * 0원 처리 할 주문 배송 데이터
     *
     * @param void
     *
     * @return array $orderDeliveryResetData
     */
    public function getOrderDeliveryResetData()
    {
        $orderDeliveryResetData = [
            'deliveryCharge' => 0,
            'taxSupplyDeliveryCharge' => 0,
            'taxVatDeliveryCharge' => 0,
            'taxFreeDeliveryCharge' => 0,
            'realTaxSupplyDeliveryCharge' => 0,
            'realTaxVatDeliveryCharge' => 0,
            'realTaxFreeDeliveryCharge' => 0,
            'deliveryPolicyCharge' => 0,
            'deliveryAreaCharge' => 0,
            'divisionDeliveryUseDeposit' => 0,
            'divisionDeliveryUseMileage' => 0,
            'divisionDeliveryCharge' => 0,
            'divisionMemberDeliveryDcPrice' => 0,
        ];

        return $orderDeliveryResetData;
    }

    /*
     * 교환시 적립 마일리지 리셋
     *
     * @param array $postData
     * @param array $updateData
     *
     * @return array $updateData
     */
    public function resetMileage($postData, $updateData)
    {
        if($postData['supplyMileage'] === 'n'){
            $updateData['goodsMileage'] = 0;
            $updateData['memberMileage'] = 0;
        }
        if($postData['supplyCouponMileage'] === 'n'){
            $updateData['couponGoodsMileage'] = 0;
        }

        return $updateData;
    }

    public function getProductCouponData($orderNo, $orderCd=null, $defaultWhere=true)
    {
        $arrBind = [];
        $arrWhere = [];
        $this->db->strField = "sno, plusCouponFl, goodsNo, orderCd"; // 쿠폰 정보

        if($defaultWhere === true){
            $arrWhere[] = 'couponMileage > 0';
        }

        $arrWhere[] = "plusCouponFl = ?";
        $this->db->bind_param_push($arrBind, $this->fieldTypes['orderCoupon']['plusCouponFl'], 'n');

        $arrWhere[] = "couponUseType = ?";
        $this->db->bind_param_push($arrBind, $this->fieldTypes['orderCoupon']['couponUseType'], 'product');

        $arrWhere[] = 'orderNo = ?';
        $this->db->bind_param_push($arrBind, $this->fieldTypes['orderCoupon']['orderNo'], $orderNo);

        if($orderCd !== null){
            $arrWhere[] = 'orderCd = ?';
            $this->db->bind_param_push($arrBind, $this->fieldTypes['orderCoupon']['orderCd'], $orderCd);
        }

        $this->db->strWhere = implode(" AND ", $arrWhere);

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_COUPON . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);

        return $getData;
    }

    /*
     * 맞교환시 사용되지 않은 상품적용 마일리지 적립 쿠폰의 orderCd를 변경
     *
     * @string $orderNo
     * @integer $beforeOrderCd
     * @integer $afterOrderCd
     *
     * @return viod
     */
    public function changeProductCouponOrderCd($orderNo, $beforeOrderCd, $afterOrderCd)
    {
        $getData = [];
        $getData = $this->getProductCouponData($orderNo, $beforeOrderCd);

        if(count($getData) > 0){
            foreach($getData as $key => $val){
                //쿠폰데이터 백업
                $couponCnt = $this->getOrderCouponOriginalCount($val['sno']);
                if($couponCnt < 1){
                    $this->setBackupOrderCouponOriginalData($val['sno']);
                }

                //orderCd 변경
                unset($arrBind, $arrWhere);
                $arrBind = [];
                $arrWhere = [];
                $updateData = ['orderCd' => $afterOrderCd];
                $compareField = array_keys($updateData);
                $arrBind = $this->db->get_binding(DBTableField::tableOrderCoupon(), $updateData, 'update', $compareField);

                $arrWhere[] = 'sno = ?';
                $this->db->bind_param_push($arrBind['bind'], 'i', $val['sno']);

                $this->db->set_update_db(DB_ORDER_COUPON, $arrBind['param'], implode(" AND ", $arrWhere), $arrBind['bind']);
            }
        }

        unset($getData);
    }

    /*
     * 맞교환시 사용되지 않은 상품적용 마일리지 적립 쿠폰의 적립금액을 변경
     *
     * @string $orderNo
     * @integer $orderCd
     * @integer $goodsNo
     * @integer $couponMileage
     *
     * @return viod
     */
    public function changeProductCouponMileage($orderNo, $orderCd, $goodsNo, $couponMileage)
    {
        $getData = [];
        $getData = $this->getProductCouponData($orderNo, $orderCd);

        if(count($getData) > 0){
            foreach($getData as $key => $val){
                if((int)$val['goodsNo'] === (int)$goodsNo){
                    //쿠폰데이터 백업
                    $couponCnt = $this->getOrderCouponOriginalCount($val['sno']);
                    if($couponCnt < 1){
                        $this->setBackupOrderCouponOriginalData($val['sno']);
                    }

                    //orderCd 변경
                    unset($arrBind, $arrWhere);
                    $arrBind = [];
                    $arrWhere = [];
                    $updateData = ['couponMileage' => (int)$couponMileage];
                    $compareField = array_keys($updateData);
                    $arrBind = $this->db->get_binding(DBTableField::tableOrderCoupon(), $updateData, 'update', $compareField);

                    $arrWhere[] = 'sno = ?';
                    $this->db->bind_param_push($arrBind['bind'], 'i', $val['sno']);

                    $this->db->set_update_db(DB_ORDER_COUPON, $arrBind['param'], implode(" AND ", $arrWhere), $arrBind['bind']);
                }
            }
        }

        unset($getData);
    }



    /*
     * 교환철회시 사용되지 않은 상품적용 마일리지 적립 쿠폰의 orderCd, 적립금액을 복구
     *
     * @string $orderNo
     *
     * @return viod
     */
    public function restoreProductCoupon($orderNo)
    {
        $getData = [];
        $getData = $this->getProductCouponData($orderNo, null, false);
        if(count($getData) > 0) {
            foreach ($getData as $key => $val) {
                //적립처리 되지 않은 상품적용 쿠폰 검색
                $arrBind = [];
                $arrWhere = [];
                $this->db->strField = "orderCd, couponMileage";

                $arrWhere[] = 'sno = ?';
                $this->db->bind_param_push($arrBind, 'i', $val['sno']);

                $this->db->strWhere = implode(" AND ", $arrWhere);

                $query = $this->db->query_complete();
                $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_COUPON_ORIGINAL . implode(' ', $query);
                $originalCouponData = $this->db->query_fetch($strSQL, $arrBind)[0];

                //교환전 orderCd 및 적립금액 교체
                if(count($originalCouponData) > 0){
                    $arrBind = [];
                    $arrWhere = [];
                    $updateData = [
                        'orderCd' => $originalCouponData['orderCd'],
                        'couponMileage' => $originalCouponData['couponMileage'],
                    ];
                    $compareField = array_keys($updateData);
                    $arrBind = $this->db->get_binding(DBTableField::tableOrderCoupon(), $updateData, 'update', $compareField);

                    $arrWhere[] = 'sno = ?';
                    $this->db->bind_param_push($arrBind['bind'], 'i', $val['sno']);

                    $this->db->set_update_db(DB_ORDER_COUPON, $arrBind['param'], implode(" AND ", $arrWhere), $arrBind['bind']);


                    $this->deleteOriginalOrderCouponData($val['sno']);
                }

                unset($arrBind, $originalCouponData);
            }
        }

        unset($getData);
    }

    /**
     * setBackupOrderCouponOriginalData
     *
     * 최초주문쿠폰정보 백업 (교환시 쿠폰정보의 orderCd 를 교체하기 위해 존재.)
     * 적립되지 않은 상품적용 마일리지 쿠폰정보만 해당
     *
     * @param integer $orderCouponSno order coupon sno
     *
     * @return boolean $orderCouponResult 성공여부
     */
    public function setBackupOrderCouponOriginalData($orderCouponSno)
    {
        //order coupon table 백업
        $orderCouponSQL = "INSERT INTO " . DB_ORDER_COUPON_ORIGINAL . " SELECT * FROM " . DB_ORDER_COUPON . " WHERE sno = " . $orderCouponSno;
        $orderCouponResult = $this->db->query($orderCouponSQL);

        return $orderCouponResult;
    }

    /**
     *
     * 최초 주문쿠폰정보가 있는지 확인 (교환시 쿠폰정보의 orderCd 를 교체하기 위해 존재.)
     * 적립되지 않은 상품적용 마일리지 쿠폰정보만 해당
     *
     * @param integer $orderCouponSno order coupon sno
     *
     * @return integer $cnt 최초 주문쿠폰정보 개수
     */
    public function getOrderCouponOriginalCount($orderCouponSno)
    {
        $cnt = $this->db->getCount(DB_ORDER_COUPON_ORIGINAL, '*', 'WHERE sno = ' . $orderCouponSno);

        return (int)$cnt;
    }

    public function deleteOriginalOrderCouponData($orderCouponSno)
    {
        $arrBind = [];
        $this->db->bind_param_push($arrBind, 'i', $orderCouponSno);

        $result = $this->db->set_delete_db(DB_ORDER_COUPON_ORIGINAL, 'sno = ?', $arrBind);

        return $result;
    }

    /*
     * 교환하려는 주문상품의 데이터가 통계에 잡혀 있는지 확인
     *
     * @param array $orderGoodsData
     * @param array $orderGoodsSnoArr
     *
     * @return boolean $isStatisticsFl
    */
    public function checkStatistics($orderGoodsData, $orderGoodsSnoArr)
    {
        $isStatisticsFl = true;

        if (count($orderGoodsSnoArr) > 0) {
            foreach ($orderGoodsSnoArr as $key => $orderGoodsSno) {
                if($orderGoodsData['statisticsOrderFl'] !== 'y'){
                    $isStatisticsFl = false;
                    break;
                }
            }
        }

        return $isStatisticsFl;
    }

    /*
     * updateExchangeHandlePrice
     * 교환취소 상품의 교환완료시 환불금액을 반환한다. (통계에서 사용)
     *
     * @param string $orderNo
     * @param integer $orderGoodsSno
     *
     * @return array $orderHandleData
     */
    public function getExchangeHandlePrice($orderNo, $orderGoodsSno)
    {
        $orderGoodsData = $orderHandleData = [];
        $refundPrice = $refundDeliveryPrice = 0;
        $refundUseDeposit = $refundUseMileage = $refundDeliveryUseDeposit = $refundDeliveryUseMileage = 0;

        // 주문상품정보
        $orderGoodsData = $this->getOrderGoodsData($orderNo, $orderGoodsSno)[0];
        // 주문상품 클레임 정보
        $orderHandleData = $this->getOrderHandleData($orderNo, 'oh.regDt asc', '', $orderGoodsData['handleSno'])[0];
        // 해당 주문의 가장 첫번째 교환취소상품 클레임 정보
        $orderFirstHandleData = $this->getOrderHandleData($orderNo, 'oh.sno asc', '', 0, 'e', $orderHandleData['handleGroupCd'])[0];
        // 주문상품 교환 클레임 정보
        $orderExchangeHandleData = $this->getOrderExchangeHandle($orderNo, $orderHandleData['handleGroupCd'], null)[0];

        $refundUseDeposit = $orderGoodsData['divisionUseDeposit'];
        $refundUseMileage = $orderGoodsData['divisionUseMileage'];

        // 환불배송금액
        if($orderFirstHandleData['sno'] === $orderHandleData['sno']){
            $refundDeliveryPrice = $orderExchangeHandleData['ehCancelDeliveryPrice'];
            $refundDeliveryUseDeposit = $orderHandleData['refundDeliveryUseDeposit'];
            $refundDeliveryUseMileage = $orderHandleData['refundDeliveryUseMileage'];
            $refundUseDeposit += $refundDeliveryUseDeposit;
            $refundUseMileage += $refundDeliveryUseMileage;
        }

        // 환불금액
        $refundPrice = ($orderGoodsData['goodsPrice'] + $orderGoodsData['optionPrice'] + $orderGoodsData['optionTextPrice']) * $orderGoodsData['goodsCnt'];
        $refundPrice -= ($orderGoodsData['goodsDcPrice'] + $orderGoodsData['memberDcPrice'] + $orderGoodsData['memberOverlapDcPrice'] + $orderGoodsData['couponGoodsDcPrice'] + $orderGoodsData['divisionCouponOrderDcPrice'] + $orderGoodsData['enuri'] + $orderGoodsData['divisionUseDeposit'] + $orderGoodsData['divisionUseMileage']);
        if ($this->myappUseFl) {
            $refundPrice -= $orderGoodsData['myappDcPrice'];
        }
        $refundPrice += $refundDeliveryPrice;

        $returnData = [
            'refundPrice' => $refundPrice, //환불 금액
            'refundDeliveryCharge' => $refundDeliveryPrice, //배송비 환불 금액
            'refundUseDeposit' => $refundUseDeposit, //전체 환불 예치금
            'refundUseMileage' => $refundUseMileage, //전체 환불 마일리지
            'refundDeliveryUseDeposit' => $refundDeliveryUseDeposit, //배송 환불 예치금
            'refundDeliveryUseMileage' => $refundDeliveryUseMileage, //배송 환불 마일리지
        ];

        return $returnData;
    }

    /**
     * deleteOrderHandleData
     * order handle data 삭제
     * @param string $orderNo 주문번호
     * @param array $handleMode 삭제할 handleMode
     *
     * @return boolean $return 삭제여부
     */
    public function deleteOrderHandleData($orderNo, $handleMode)
    {
        $return = true;

        $arrBind = [];
        $where = [];

        $where[] = 'orderNo = ?';
        $this->db->bind_param_push($arrBind, 's', $orderNo);

        if($handleMode === 'e'){
            $where[] = '(handleMode = ? || handleMode = ?)';
            $this->db->bind_param_push($arrBind, 's', 'e');
            $this->db->bind_param_push($arrBind, 's', 'z');
            $result = $this->db->set_delete_db(DB_ORDER_HANDLE, implode(" AND ", $where), $arrBind);

            unset($arrBind, $where);
            $arrBind = [];
            $where = [];
            $where[] = 'ehOrderNo = ?';
            $this->db->bind_param_push($arrBind, 's', $orderNo);
            $this->db->set_delete_db(DB_ORDER_EXCHANGE_HANDLE, implode(" AND ", $where), $arrBind);
        }
        else {
            $where[] = 'handleMode = ?';
            $this->db->bind_param_push($arrBind, 's', $handleMode);
            $result = $this->db->set_delete_db(DB_ORDER_HANDLE, implode(" AND ", $where), $arrBind);
        }

        if(!$result){
            $return = false;
        }

        unset($arrBind, $where);

        return $return;
    }

    public function getOrderGoodsCancelUpdateData($originalData, $updateOrderGoodsData)
    {
        $taxSupplyGoodsPrice = $originalData['realTaxSupplyGoodsPrice'] - $updateOrderGoodsData['realTaxSupplyGoodsPrice'];
        $taxVatGoodsPrice = $originalData['realTaxVatGoodsPrice'] - $updateOrderGoodsData['realTaxVatGoodsPrice'];
        $taxFreeGoodsPrice = $originalData['realTaxFreeGoodsPrice'] - $updateOrderGoodsData['realTaxFreeGoodsPrice'];

        if($this->addPaymentDivisionFl === true){
            if(is_array($originalData['goodsTaxInfo'])){
                $goodsTax = $originalData['goodsTaxInfo'];
            }
            else {
                $goodsTax = explode(STR_DIVISION, $originalData['goodsTaxInfo']);
            }

            $diffMileage = ((int)$originalData['divisionUseMileage'] - (int)$updateOrderGoodsData['divisionUseMileage']) - (int)$this->reDivisionAddPaymentArr['cancel']['mileage'][$originalData['sno']];
            $diffDeposit = ((int)$originalData['divisionUseDeposit'] - (int)$updateOrderGoodsData['divisionUseDeposit']) - (int)$this->reDivisionAddPaymentArr['cancel']['deposit'][$originalData['sno']];

            $totalRealTaxGoodsPrice = $taxSupplyGoodsPrice + $taxVatGoodsPrice + $taxFreeGoodsPrice + $diffMileage + $diffDeposit;
            $goodsTaxData = NumberUtils::taxAll($totalRealTaxGoodsPrice, $goodsTax[1], $goodsTax[0]);

            if ($goodsTax[0] == 't') {
                $taxSupplyGoodsPrice = $goodsTaxData['supply'];
                $taxVatGoodsPrice = $goodsTaxData['tax'];
                $taxFreeGoodsPrice = 0;
            } else {
                $taxSupplyGoodsPrice = 0;
                $taxVatGoodsPrice = 0;
                $taxFreeGoodsPrice = $goodsTaxData['supply'];
            }
        }
        $realTaxSupplyGoodsPrice = 0;
        $realTaxVatGoodsPrice = 0;
        $realTaxFreeGoodsPrice = 0;
        $goodsDcPrice = $originalData['goodsDcPrice'] - $updateOrderGoodsData['goodsDcPrice'];
        $memberDcPrice = $originalData['memberDcPrice'] - $updateOrderGoodsData['memberDcPrice'];
        $memberOverlapDcPrice = $originalData['memberOverlapDcPrice'] - $updateOrderGoodsData['memberOverlapDcPrice'];
        $couponGoodsDcPrice = $originalData['couponGoodsDcPrice'] - $updateOrderGoodsData['couponGoodsDcPrice'];
        if($this->addPaymentDivisionFl === true){
            $divisionUseDeposit = (int)$this->reDivisionAddPaymentArr['cancel']['deposit'][$originalData['sno']];
            $divisionUseMileage = (int)$this->reDivisionAddPaymentArr['cancel']['mileage'][$originalData['sno']];

            if($this->cancelDeliveryFl[$originalData['orderDeliverySno']] === 'y'){
                $divisionGoodsDeliveryUseDeposit = $this->reDivisionAddPaymentArr['cancel']['divisionDeliveryGoodsDeposit'][$originalData['sno']];
                $divisionGoodsDeliveryUseMileage = $this->reDivisionAddPaymentArr['cancel']['divisionDeliveryGoodsMileage'][$originalData['sno']];

            }
            else if($this->cancelDeliveryFl[$originalData['orderDeliverySno']] === 'n'){
                $this->reDivisionAddPaymentArr['etc']['deposit'][$originalData['orderDeliverySno']] += (int)$originalData['divisionGoodsDeliveryUseDeposit'] - $updateOrderGoodsData['divisionGoodsDeliveryUseDeposit'];
                $this->reDivisionAddPaymentArr['etc']['mileage'][$originalData['orderDeliverySno']] += (int)$originalData['divisionGoodsDeliveryUseMileage'] - $updateOrderGoodsData['divisionGoodsDeliveryUseMileage'];
                $divisionGoodsDeliveryUseDeposit = 0;
                $divisionGoodsDeliveryUseMileage = 0;
            }
            else {}
        }
        else {
            $divisionUseDeposit = $originalData['divisionUseDeposit'] - $updateOrderGoodsData['divisionUseDeposit'];
            $divisionUseMileage = $originalData['divisionUseMileage'] - $updateOrderGoodsData['divisionUseMileage'];
            $divisionGoodsDeliveryUseDeposit = $originalData['divisionGoodsDeliveryUseDeposit'] - $updateOrderGoodsData['divisionGoodsDeliveryUseDeposit'];
            $divisionGoodsDeliveryUseMileage = $originalData['divisionGoodsDeliveryUseMileage'] - $updateOrderGoodsData['divisionGoodsDeliveryUseMileage'];
        }
        if ($this->myappUseFl) {
            $myappDcPrice = $originalData['myappDcPrice'] - $updateOrderGoodsData['myappDcPrice'];
        }
        $divisionCouponOrderDcPrice = $originalData['divisionCouponOrderDcPrice'] - $updateOrderGoodsData['divisionCouponOrderDcPrice'];
        $enuri = $originalData['enuri'] - $updateOrderGoodsData['enuri'];
        $goodsMileage = $originalData['goodsMileage'] - $updateOrderGoodsData['goodsMileage'];
        $memberMileage = $originalData['memberMileage'] - $updateOrderGoodsData['memberMileage'];
        $couponGoodsMileage = $originalData['couponGoodsMileage'] - $updateOrderGoodsData['couponGoodsMileage'];
        $divisionCouponOrderMileage = $originalData['divisionCouponOrderMileage'] - $updateOrderGoodsData['divisionCouponOrderMileage'];

        $returnData = [
            'taxSupplyGoodsPrice' => gd_isset($taxSupplyGoodsPrice, 0),
            'taxVatGoodsPrice' => gd_isset($taxVatGoodsPrice, 0),
            'realTaxSupplyGoodsPrice' => gd_isset($realTaxSupplyGoodsPrice, 0),
            'realTaxVatGoodsPrice' => gd_isset($realTaxVatGoodsPrice, 0),
            'taxFreeGoodsPrice' => gd_isset($taxFreeGoodsPrice, 0),
            'realTaxFreeGoodsPrice' => gd_isset($realTaxFreeGoodsPrice, 0),
            'goodsDcPrice' => gd_isset($goodsDcPrice, 0),
            'memberDcPrice' => gd_isset($memberDcPrice, 0),
            'memberOverlapDcPrice' => gd_isset($memberOverlapDcPrice, 0),
            'couponGoodsDcPrice' => gd_isset($couponGoodsDcPrice, 0),
            'divisionUseDeposit' => gd_isset($divisionUseDeposit, 0),
            'divisionUseMileage' => gd_isset($divisionUseMileage, 0),
            'divisionGoodsDeliveryUseDeposit' => gd_isset($divisionGoodsDeliveryUseDeposit, 0),
            'divisionGoodsDeliveryUseMileage' => gd_isset($divisionGoodsDeliveryUseMileage, 0),
            'divisionCouponOrderDcPrice' => gd_isset($divisionCouponOrderDcPrice, 0),
            'enuri' => gd_isset($enuri, 0),
            'goodsMileage' => gd_isset($goodsMileage, 0),
            'memberMileage' => gd_isset($memberMileage, 0),
            'couponGoodsMileage' => gd_isset($couponGoodsMileage, 0),
            'divisionCouponOrderMileage' => gd_isset($divisionCouponOrderMileage, 0),
        ];

        if ($this->myappUseFl) {
            $returnData['myappDcPrice'] = gd_isset($myappDcPrice, 0);
        }

        return $returnData;
    }

    /**
     * getOrderOriginalMaxClaimSort
     *
     * claim sort 최대값
     *
     * @param string $orderNo 주문번호
     * @param string $claimStatus 클레임 상태
     *
     * @return boolean $return 성공여부
     */
    public function getOrderOriginalMaxClaimSort($orderNo)
    {
        $arrBind = [];

        $this->db->strField = "MAX(claimSort) as claimSort";
        $this->db->strWhere = 'orderNo = ?';
        $this->db->bind_param_push($arrBind, $this->fieldTypes['order']['orderNo'], $orderNo);

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_ORIGINAL . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind, false);
        unset($arrBind);

        return gd_isset($getData['claimSort'], 0);
    }

    /**
     * getOrderDepositHandleData
     *
     * 예치금 환불처리시 핸들데이터
     * @param array $handleData 핸들데이터
     *
     * @return array $return 환불처리 핸들데이터
     */
    public function getOrderDepositHandleData($handleData)
    {
        $arrWhere = $arrBind = array();

        $arrWhere[] = 'oh.orderNo = ? ';
        $this->db->bind_param_push($arrBind, 's', $handleData['orderNo']);
        $arrWhere[] = 'oh.refundGroupCd = ? ';
        $this->db->bind_param_push($arrBind, 'i', $handleData['refundGroupCd']);

        $join[] = ' INNER JOIN ' . DB_ORDER_GOODS . ' as og ON oh.sno = og.handleSno ';
        $strField = "og.goodsNm,og.regDt,oh.handleDetailReason,oh.handleReason,oh.orderNo,oh.handleDetailReasonShowFl";
        $strJoin = implode('', $join);
        $strWhere = implode(' AND ', gd_isset($arrWhere));

        $strSQL = 'SELECT ' . $strField . ' FROM ' . DB_ORDER_HANDLE . ' oh ' . $strJoin.' WHERE '.$strWhere.' ';
        $tmp = $this->db->query_fetch($strSQL, $arrBind);

        $goodsNm = array();
        foreach ($tmp as $key => $value) {
            $goodsNm[]	= $value['goodsNm'];
            $data['handleReason']	= $value['handleReason'];
            $data['handleDetailReason']	= str_replace("\\r\\n","<br>",$value['handleDetailReason']);
            $data['handleDetailReasonShowFl']	= $value['handleDetailReasonShowFl'];
            $data['orderNo']	= $value['orderNo'];
            $data['regDt']	= $value['regDt'];
        }

        if(count($goodsNm)> 1){
            $data['goodsNm'] = $goodsNm[0].'외 '.(count($goodsNm)-1).'건';
        }else{
            $data['goodsNm']= $goodsNm[0];
        }

        return $data;
    }

    /**
     * getOrderCashReceiptData
     *
     * 주문건에 해당하는 현금영수증의 발행상태 체크(발행요청)
     * @param string $orderNo 주문번호현금영수증 재발행을 위함 현금영수증 정보 데이터
     *
     * @return mixed
     */
    public function getOrderCashReceiptData($orderNo)
    {
        $arrBind = [];

        $this->db->strField = "ocr.sno, ocr.orderNo, ocr.issueMode, ocr.managerId, ocr.managerNo, ocr.requestNm, ocr.requestGoodsNm, ocr.requestIP, ocr.requestEmail, ocr.requestCellPhone";
        $this->db->strField .= ", ocr.useFl, ocr.certFl, ocr.certNo, ocr.settlePrice, ocr.supplyPrice, ocr.taxPrice, ocr.freePrice, ocr.servicePrice";
        $this->db->strField .= ", ocr.pgName, ocr.pgTid, ocr.pgAppNo, ocr.pgAppDt, ocr.pgAppNoCancel, ocr.statusFl, ocr.reIssueFl, ocr.processDt, ocr.pgLog, ocr.adminMemo, ocr.regDt, ocr.modDt";
        $this->db->strWhere = 'ocr.orderNo = ? ';
        $this->db->strWhere .= ' AND ocr.statusFl = "r" ';
        $this->db->strOrder = 'ocr.regDt DESC LIMIT 1 ';
        $this->db->bind_param_push($arrBind, $this->fieldTypes['order']['orderNo'], $orderNo);

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_CASH_RECEIPT . ' as ocr ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind, false);
        unset($arrBind);

        return $getData;
    }

    /**
     * getCommonField
     *
     * @param string $tableName1
     * @param string $tableName2
     *
     * @return array $commonField 공통컬럼
     */
    public function getCommonField($tableName1, $tableName2)
    {
        $commonField = $table1ColumnsData = $table2ColumnsData = $table1Columns = $table2Columns = [];

        $table1ColumnsData = $this->db->query_fetch("SHOW COLUMNS FROM " . $tableName1);
        $table2ColumnsData = $this->db->query_fetch("SHOW COLUMNS FROM " . $tableName2);
        $table1Columns = array_column($table1ColumnsData, 'Field');
        $table2Columns = array_column($table2ColumnsData, 'Field');
        $commonField = array_intersect($table1Columns, $table2Columns);

        return $commonField;
    }

    /**
     *
     * 환불 클레임 처리건이 있는지에 대한 플래그값
     *
     * @param string $orderNo 주문번호
     *
     * @return boolean
     */
    public function getRefundClaimExistFl($orderNo)
    {
        $arrWhere = $arrBind = [];
        $arrWhere[] = 'orderNo = ?';
        $arrWhere[] = 'claimStatus = ?';
        $this->db->bind_param_push($arrBind, 's', $orderNo);
        $this->db->bind_param_push($arrBind, 's', 'r');

        $this->db->strField = 'COUNT(*) AS cnt';
        $this->db->strWhere = implode(' AND ', gd_isset($arrWhere));

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_ORIGINAL . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $arrBind, false);

        unset($arrWhere, $arrBind, $query, $strSQL);
        if((int)$data['cnt'] > 0){
            return true;
        }
        else {
            return false;
        }
    }

    /**
     *
     * 교환추가 상품의 상태값 지정
     *
     * @param integer $totalChangePrice 총 교환 결제금액
     *
     * @return string $status
     */
    public function setAddExchangeOrderGoodsStatus($totalChangePrice)
    {
        if((int)$totalChangePrice < 0){
            // 교환차익이 발생하여 운영자가 고객에게 금액을 받아야 하는 경우
            $status = 'o1';
        }
        else {
            // 교환차익이 발생하지 않는 경우
            // 교환차익이 발생하여 운영자가 고객에게 금액을 주어야 하는경우
            $status = 'p1';
        }

        return $status;
    }

    /**
     *
     * 교환추가상품에 대한 금액외 업데이트 할 사항들
     *
     * @param array $updateOrderGoodsData 업데이트될 데이터
     * @param string $changeStatus 변경될 상태
     *
     * @return array $updateOrderGoodsData
     */
    public function getAddExchangeOrderGoodsEtcData($updateOrderGoodsData, $changeStatus)
    {
        $updateOrderGoodsData['statisticsGoodsFl'] = $updateOrderGoodsData['statisticsOrderFl'] = '';
        $updateOrderGoodsData['deliveryCompleteDt'] = $updateOrderGoodsData['finishDt'] = $updateOrderGoodsData['paymentDt'] = $updateOrderGoodsData['deliveryDt'] = '';
        $updateOrderGoodsData['minusStockFl'] = $updateOrderGoodsData['minusRestoreStockFl'] = 'n';
        $updateOrderGoodsData['plusMileageFl'] = $updateOrderGoodsData['plusRestoreMileageFl'] = 'n';
        if($changeStatus === 'p1'){
            $updateOrderGoodsData['paymentDt'] = date("Y-m-d H:i:s");
        }

        return $updateOrderGoodsData;
    }

    /*
     * 동일상품교환시 변경될 option 정보 반환
     *
     * @param integer $changeOptionSno 옵션번호
     * @param integer $goodsNo 상품번호
     * @param object $goodsObj 상품 컴포넌트 인스턴스
     *
     */
    public function getChangeOptionData($changeOptionSno, $goodsNo, $goodsObj)
    {
        $changeOptionResultData = $changeOptionData = $tmpGoodsData = $tmpOptionName = $tmpOptionData = [];

        $tmpGoodsData = $goodsObj->getGoodsInfo($goodsNo);
        $tmpOptionName = explode(STR_DIVISION, $tmpGoodsData['optionName']);
        $tmpOptionNameCnt = count($tmpOptionName);
        $tmpOptionData = $goodsObj->getGoodsOptionInfo($changeOptionSno);

        if(count($tmpOptionName) > 0){
            $optIdx = 1;
            foreach($tmpOptionName as $optNmKey => $optNmValue){
                if((int)$tmpOptionNameCnt === $optIdx) {
                    $changeOptionData[] = [
                        $optNmValue,
                        $tmpOptionData['optionValue'.$optIdx],
                        $tmpOptionData['optionCode'],
                        floatval($tmpOptionData['optionPrice']),
                    ];
                }
                else {
                    $changeOptionData[] = [
                        $optNmValue,
                        $tmpOptionData['optionValue'.$optIdx],
                        null,
                        0,
                    ];
                }

                $optIdx++;
            }
        }

        if(count($changeOptionData) > 0){
            $changeOptionResultData = [
                'optionSno' => $changeOptionSno,
                'optionInfo' => json_encode($changeOptionData, JSON_UNESCAPED_UNICODE),
            ];
        }

        unset($changeOptionData, $tmpGoodsData, $tmpOptionName, $tmpOptionData);

        return $changeOptionResultData;
    }

    /*
     * 동일상품교환시 옵션교체
     *
     * @param array $updateOrderGoodsData 업데이트될 데이터
     * @param boolean $optionChangeFl 옵션변경여부
     * @param array $changeOptionData 변경될 옵션데이터
     *
     */
    public function getAddExchangeOrderGoodsOptionData($updateOrderGoodsData, $optionChangeFl, $changeOptionData)
    {
        if($optionChangeFl === true){
            $updateOrderGoodsData['optionSno'] = $changeOptionData['optionSno'];
            $updateOrderGoodsData['optionInfo'] = $this->db->escape($changeOptionData['optionInfo']);
        }

        return $updateOrderGoodsData;
    }
}
