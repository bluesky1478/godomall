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

namespace Bundle\Component\Order;


use Bundle\Component\Naver\NaverPay;
use Component\Database\DBTableField;
use Component\Delivery\Delivery;
use Component\Member\Manager;
use Component\Policy\Policy;
use Framework\Debug\Exception\AlertBackException;
use Framework\Utility\DateTimeUtils;
use Framework\Utility\StringUtils;
use Framework\Utility\ArrayUtils;
use Exception;


class LogisticsOrder extends \Component\Order\OrderAdmin
{
    const LOGISTICS_COMPANY_SNO = 8;    //대한통운 택배사 코드

    protected $orderGridConfigList;
    protected $logisticsLogger;

    protected $orderGoodsOrderBy = 'og.regDt desc, og.scmNo asc, og.orderCd asc';

    /**
     * @var string 주문상품의 기본 테이블 정렬 - 복수배송지 사용시
     */
    protected $orderGoodsMultiShippingOrderBy = 'og.regDt desc, oi.orderInfoCd asc, og.scmNo asc, og.orderCd asc';

    public function __construct()
    {
        parent::__construct();
        $this->logisticsLogger = \Logger::channel('logistics');
    }

    protected function getSelectOrderGridConfigList()
    {
        return ['check' => '선택', 'no' => '번호', 'regDt' => '주문일시', 'orderNo' => '주문번호', 'orderName' => '주문자', 'orderGoodsNo' => '상품주문번호', 'orderGoodsNm' => '주문상품', 'gift' => '사은품', 'processStatus' => '처리상태', 'invoiceNo' => '송장번호', 'scmNm' => '공급사', 'multiShippingCd' => '배송지', 'receiverName' => '수령자', 'packetCode' => '묶음배송코드', 'reservationStatus' => '예약상태', 'reservationCancel' => '예약취소',];
    }

    public function getOrderListForAdmin($searchData, $searchPeriod, $isUserHandle = false)
    {
        $this->orderGridConfigList = $this->getSelectOrderGridConfigList($searchData['orderAdminGridMode']);
        //대한통운과 택배사가 배정안된것
        // --- 검색 설정
        $this->_setSearch($searchData, $searchPeriod, $isUserHandle);

        // 주문번호별로 보기
        $isDisplayOrderGoods = ($this->search['view'] !== 'order');// view모드가 orderGoods & orderGoodsSimple이 아닌 경우 true
        $this->search['searchPeriod'] = gd_isset($searchData['searchPeriod']);

        // --- 페이지 기본설정
        gd_isset($searchData['page'], 1);
        gd_isset($searchData['pageNum'], 20);
        $page = \App::load('\\Component\\Page\\Page', $searchData['page'], 0, 0, $searchData['pageNum']);
        $page->setCache(true)->setUrl(\Request::getQueryString()); // 페이지당 리스트 수

        // 주문상태 정렬 예외 케이스 처리
        if ($searchData['sort'] == 'og.orderStatus asc') {
            $searchData['sort'] = 'case LEFT(og.orderStatus, 1) when \'o\' then \'01\' when \'p\' then \'02\' when \'g\' then \'03\' when \'d\' then \'04\' when \'s\' then \'05\' when \'e\' then \'06\' when \'b\' then \'07\' when \'r\' then \'08\' when \'c\' then \'09\' when \'f\' then \'10\' else \'11\' end';
        } elseif ($searchData['sort'] == 'og.orderStatus desc') {
            $searchData['sort'] = 'case LEFT(og.orderStatus, 1) when \'f\' then \'01\' when \'c\' then \'02\' when \'r\' then \'03\' when \'b\' then \'04\' when \'e\' then \'05\' when \'s\' then \'06\' when \'d\' then \'07\' when \'g\' then \'08\' when \'p\' then \'09\' when \'o\' then \'10\' else \'11\' end';
        }

        //복수배송지 사용시 배송지별 묶음
        if ($this->isUseMultiShipping === true && !$searchData['sort']) {
            // 정렬 설정
            $orderSort = gd_isset($searchData['sort'], $this->orderGoodsMultiShippingOrderBy);
        } else {
            // 정렬 설정
            $orderSort = gd_isset($searchData['sort'], $this->orderGoodsOrderBy);
        }

        //상품준비중 리스트에서 묶음배송 정렬 기준
        if (preg_match("/packetCode/", $orderSort)) {
            if (preg_match("/desc/", $orderSort)) {
                $orderSort = "oi.packetCode desc, og.orderNo desc";
            } else {
                $orderSort = "oi.packetCode desc, og.orderNo asc";
            }
        }
        //복수배송지 사용시 배송지별 묶음
        if ($this->isUseMultiShipping === true) {
            if (!preg_match("/orderInfoCd/", $orderSort)) {
                $orderSort = $orderSort . ", oi.orderInfoCd asc, og.sno asc";
            }
        }

        $arrIncludeOh = ['handleMode', 'beforeStatus', 'refundMethod', 'handleReason', 'handleDetailReason', 'regDt AS handleRegDt', 'handleDt',];
        $arrIncludeOi = ['orderName', 'receiverName', 'orderMemo', 'orderCellPhone', 'packetCode',];

        $arrIncludeLo = ['custId', // 고객사용번호
            'custUseNo',
            'mpckKey', // 합포장키
            'mpckSeq', // 합포장순번
            'reqDvCd',
        ];

        $tmpField[] = ['oh.regDt AS handleRegDt'];
        $tmpField[] = DBTableField::setTableField('tableOrderHandle', $arrIncludeOh, null, 'oh');
        $tmpField[] = DBTableField::setTableField('tableOrderInfo', $arrIncludeOi, null, 'oi');
        $tmpField[] = ['oi.sno AS orderInfoSno'];
        $tmpField[] = DBTableField::setTableField('tableLogisticsOrder', $arrIncludeLo, null, 'lo');
        //        dump($arrIncludeLo);
        // join 문
        $join[] = ' LEFT JOIN ' . DB_ORDER . ' o ON o.orderNo = og.orderNo ';
        $join[] = ' LEFT JOIN ' . DB_ORDER_HANDLE . ' oh ON og.handleSno = oh.sno AND og.orderNo = oh.orderNo ';
        $join[] = ' LEFT JOIN ' . DB_ORDER_DELIVERY . ' od ON og.orderDeliverySno = od.sno ';
        $join[] = ' LEFT JOIN ' . DB_ORDER_INFO . ' oi ON (og.orderNo = oi.orderNo)   
                    AND (CASE WHEN od.orderInfoSno > 0 THEN od.orderInfoSno = oi.sno ELSE oi.orderInfoCd = 1 END)';

        if (($this->search['key'] == 'all' && empty($this->search['keyword']) === false) || $this->search['key'] == 'sm.companyNm' || strpos($orderSort, "sm.companyNm ") !== false) {
            $join[] = ' LEFT JOIN ' . DB_SCM_MANAGE . ' sm ON og.scmNo = sm.scmNo ';

        }

        if ((($this->search['key'] == 'all' && empty($this->search['keyword']) === false) || $this->search['key'] == 'pu.purchaseNm') && gd_is_plus_shop(PLUSSHOP_CODE_PURCHASE) === true && gd_is_provider() === false) {
            $join[] = ' LEFT JOIN ' . DB_PURCHASE . ' pu ON og.purchaseNo = pu.purchaseNo ';
        }

        if (($this->search['key'] == 'all' && empty($this->search['keyword']) === false) || $this->search['key'] == 'm.nickNm' || $this->search['key'] == 'm.memId' || ($this->search['memFl'] == 'y' && empty($this->search['memberGroupNo']) === false)) {
            $join[] = ' LEFT JOIN ' . DB_MEMBER . ' m ON o.memNo = m.memNo AND m.memNo > 0 ';
        }

        //상품 브랜드 코드 검색
        if (empty($this->search['brandCd']) === false || empty($this->search['brandNoneFl']) === false) {
            $join[] = ' LEFT JOIN ' . DB_GOODS . ' as g ON og.goodsNo = g.goodsNo ';
        }

        //택배 예약 상태에 따른 검색
        if ($this->search['invoiceReserveFl']) {
            $join[] = ' LEFT JOIN ' . DB_ORDER_GODO_POST . ' ogp ON ogp.invoiceNo = og.invoiceNo ';
        }

        // 쿠폰검색시만 join
        if ($this->search['couponNo'] > 0) {
            $join[] = ' LEFT JOIN ' . DB_ORDER_COUPON . ' oc ON o.orderNo = oc.orderNo ';
            $join[] = ' LEFT JOIN ' . DB_MEMBER_COUPON . ' mc ON mc.memberCouponNo = oc.memberCouponNo ';
        }

        $join[] = ' LEFT OUTER JOIN ' . DB_LOGISTICS_ORDER . ' AS lo ON lo.orderGoodsNo = og.sno AND lo.current= \'y\' ';

        // 반품/교환/환불신청 사용에 따른 리스트 별도 처리 (조건은 검색 메서드 참고)
        if ($isUserHandle) {

            $arrIncludeOuh = ['sno', 'userHandleMode', 'userHandleFl', 'userHandleGoodsNo', 'userHandleGoodsCnt', 'userHandleReason', 'userHandleDetailReason', 'adminHandleReason',];
            $tmpField[] = ['ouh.regDt AS userHandleRegDt', 'ouh.sno AS userHandleNo'];
            $tmpField[] = DBTableField::setTableField('tableOrderUserHandle', $arrIncludeOuh, null, 'ouh');
            $join[] = ' LEFT JOIN ' . DB_ORDER_USER_HANDLE . ' ouh ON (og.userHandleSno = ouh.sno || (og.sno = ouh.userHandleGoodsNo && left(og.orderStatus, 1) NOT IN (\'' . implode('\',\'', $this->statusUserClaimRequestCode) . '\')))';
        }

        // 쿼리용 필드 합침
        $tmpKey = array_keys($tmpField);
        $arrField = [];
        foreach ($tmpKey as $key) {
            $arrField = array_merge($arrField, $tmpField[$key]);
        }
        unset($tmpField, $tmpKey);

        // 현 페이지 결과
        $this->db->strField = 'og.sno,og.orderNo,og.goodsNo,og.scmNo ,og.mallSno ,og.purchaseNo ,o.memNo ,' . implode(', ', $arrField) . ',og.orderDeliverySno, if(reqDvCd = "01","예약완료","예약전") as reservationStatus';
        // addGoods 필드 변경 처리 (goods와 동일해서)

        $this->db->strJoin = implode('', $join);
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
        $this->db->strOrder = $orderSort;

        $this->db->strLimit = $page->recode['start'] . ',' . $searchData['pageNum'];

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_GOODS . ' og ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $this->arrBind);
        unset($query['order']);
        unset($query['limit']);

        if ($page->hasRecodeCache('total') === false) {
            $strSQL = 'SELECT count(*)  as cnt  FROM ' . DB_ORDER_GOODS . ' og ' . implode(' ', $query);
            $getCount = $this->db->query_fetch($strSQL, $this->arrBind, false);
            $total = $getCount['cnt'];
            $page->recode['total'] = $total;
        }


        $page->setPage();

        $result = $this->setOrderListForAdmin($getData, $isUserHandle, $isDisplayOrderGoods, true, $searchData['statusMode']);

        return $result;
    }

    public function getOrderGroup($data)
    {
        $result = null;
        foreach ($data as $val) {
            $result[$val['orderNo']][] = $val['sno'];
        }

        return $result;
    }


    /**
     * 관리자 주문 리스트를 위한 검색 정보 세팅
     *
     * @param string  $searchData   검색 데이타
     * @param integer $searchPeriod 기본 조회 기간 (삭제예정)
     * @param boolean $isUserHandle
     *
     * @throws AlertBackException
     */
    protected function _setSearch($searchData, $searchPeriod = 7, $isUserHandle = false)
    {
        // 통합 검색
        $this->search['combineSearch'] = ['o.orderNo' => __('주문번호'), 'og.invoiceNo' => __('송장번호'), //            'og.goodsNm' => __('상품명'),
            //            'og.goodsNo' => __('상품코드'),
            //            'og.goodsCd' => __('자체 상품코드'),
            //            'og.goodsModelNo' => __('상품모델명'),
            //            'og.makerNm' => __('제조사'),
            '__disable1' => '==========', 'oi.orderName' => __('주문자명'), 'oi.orderPhone' => __('주문자 전화번호'), 'oi.orderCellPhone' => __('주문자 휴대폰번호'), 'oi.orderEmail' => __('주문자 이메일'), 'oi.receiverName' => __('수령자명'), 'oi.receiverPhone' => __('수령자 전화번호'), 'oi.receiverCellPhone' => __('수령자 휴대폰번호'), 'o.bankSender' => __('입금자명'), '__disable2' => '==========', 'm.memId' => __('아이디'), 'm.nickNm' => __('닉네임'), 'oi.orderName' => __('주문자명'), //            '__disable3' =>'==========',
            //            'sm.companyNm' => __('공급사명'),
        ];

        //        if(gd_is_provider() === false && gd_is_plus_shop(PLUSSHOP_CODE_PURCHASE) === true ) {
        //            $this->search['combineSearch']['pu.purchaseNm'] = __('매입처명');
        //        }

        // !중요! 순서 변경시 하단의 노출항목 조절 필요
        $this->search['combineTreatDate'] = ['og.regDt' => __('주문일'), //            'og.paymentDt' => __('결제확인일'),
            'og.invoiceDt' => __('송장입력일'), 'og.deliveryDt' => __('배송일'), 'og.deliveryCompleteDt' => __('배송완료일'), 'og.finishDt' => __('구매확정일'), //            'og.cancelDt' => __('취소완료일'),
            'oh.regDt.b' => __('반품접수일'), 'oh.handleDt.b' => __('반품완료일'), 'oh.regDt.e' => __('교환접수일'), 'oh.handleDt.e' => __('교환완료일'), 'oh.regDt.r' => __('환불접수일'), 'oh.handleDt.r' => __('환불완료일'), 'oi.packetCode' => __('묶음배송'),];

        // --- $searchData trim 처리
        if (isset($searchData)) {
            gd_trim($searchData);
        }

        // --- 정렬
        $this->search['sortList'] = ['og.regDt desc' => sprintf('%s↓', __('주문번호')), 'og.regDt asc' => sprintf('%s↑', __('주문번호')),'og.orderNo desc' => sprintf('%s↓', __('주문일')), 'og.orderNo asc' => sprintf('%s↑', __('주문일')), 'o.orderGoodsNm desc' => sprintf('%s↓', __('상품명')), 'o.orderGoodsNm asc' => sprintf('%s↑', __('상품명')), 'oi.orderName desc' => sprintf('%s↓', __('주문자')), 'oi.orderName asc' => sprintf('%s↑', __('주문자')), 'o.settlePrice desc' => sprintf('%s↓', __('총 결제금액')), 'o.settlePrice asc' => sprintf('%s↑', __('총 결제금액')), 'oi.receiverName desc' => sprintf('%s↓', __('수령자')), 'oi.receiverName asc' => sprintf('%s↑', __('수령자')), 'sm.companyNm desc' => sprintf('%s↓', __('공급사')), 'sm.companyNm asc' => sprintf('%s↑', __('공급사')), 'og.orderStatus desc' => sprintf('%s↓', __('처리상태')), 'og.orderStatus asc' => sprintf('%s↑', __('처리상태')),];

        // 상품주문번호별 탭을 제외하고는 처리상태 정렬 제거
        if ($isUserHandle === false) {
            unset($this->search['sortList']['og.orderStatus desc'], $this->search['sortList']['og.orderStatus asc']);
        }

        // 상품주문번호별 탭을 제외하고는 처리상태 정렬 제거
        if ($isUserHandle === false) {
            unset($this->search['sortList']['og.orderStatus desc'], $this->search['sortList']['og.orderStatus asc']);
        }

        self::setAddSearchSortList(['paymentDt', 'packetCode']);

        // 검색을 위한 bind 정보
        $fieldTypeGoods = DBTableField::getFieldTypes('tableGoods');

        // 검색기간 설정
        //        $data = gd_policy('order.defaultSearch');
        // CRM관리에서 주문요약 내역 90일 처리
        $thisCallController = \App::getInstance('ControllerNameResolver')->getControllerName();
        if ($thisCallController == 'Controller\Admin\Share\MemberCrmController') {
            $searchPeriod = 90;
        } else {
            //            $searchPeriod = gd_isset($data['searchPeriod'], 6);
        }

        // --- 검색 설정
        $this->search['mallFl'] = gd_isset($searchData['mallFl'], 'all');
        $this->search['exceptOrderStatus'] = gd_isset($searchData['exceptOrderStatus']);    //예외처리할 주문상태
        $this->search['detailSearch'] = gd_isset($searchData['detailSearch']);
        $this->search['statusMode'] = gd_isset($searchData['statusMode']);
        $this->search['key'] = gd_isset($searchData['key']);
        $this->search['keyword'] = gd_isset($searchData['keyword']);
        $this->search['sort'] = gd_isset($searchData['sort']);
        $this->search['orderStatus'] = gd_isset($searchData['orderStatus']);
        $this->search['processStatus'] = gd_isset($searchData['processStatus']);
        $this->search['userHandleMode'] = gd_isset($searchData['userHandleMode']);
        $this->search['userHandleFl'] = gd_isset($searchData['userHandleFl']);
        $this->search['treatDateFl'] = gd_isset($searchData['treatDateFl'], 'og.regDt');
        $this->search['treatDate'][] = gd_isset($searchData['treatDate'][0], date('Y-m-d', strtotime('-' . $searchPeriod . ' day')));
        $this->search['treatDate'][] = gd_isset($searchData['treatDate'][1], date('Y-m-d'));
        $this->search['settleKind'] = gd_isset($searchData['settleKind']);
        $this->search['settlePrice'][] = gd_isset($searchData['settlePrice'][0]);
        $this->search['settlePrice'][] = gd_isset($searchData['settlePrice'][1]);
        $this->search['memFl'] = gd_isset($searchData['memFl']);
        $this->search['memberGroupNo'] = gd_isset($searchData['memberGroupNo']);
        $this->search['memberGroupNoNm'] = gd_isset($searchData['memberGroupNoNm']);
        $this->search['receiptFl'] = gd_isset($searchData['receiptFl']);
        $this->search['userHandleViewFl'] = gd_isset($searchData['userHandleViewFl']);
        $this->search['orderTypeFl'] = gd_isset($searchData['orderTypeFl']);
        $this->search['orderChannelFl'] = gd_isset($searchData['orderChannelFl']);
        $this->search['scmFl'] = gd_isset($searchData['scmFl'], 'all');
        // 공급사 선택 후 공급사가 없는 경우
        if ($searchData['scmNo'] == 0 && $searchData['scmFl'] == 1) {
            $this->search['scmFl'] = 'all';
        }
        $this->search['scmNo'] = gd_isset($searchData['scmNo']);
        $this->search['scmNoNm'] = gd_isset($searchData['scmNoNm']);
        $this->search['scmAdjustNo'] = gd_isset($searchData['scmAdjustNo']);
        $this->search['scmAdjustType'] = gd_isset($searchData['scmAdjustType']);
        $this->search['manualPayment'] = gd_isset($searchData['manualPayment'], '');
        $this->search['invoiceFl'] = gd_isset($searchData['invoiceFl'], '');
        $this->search['firstSaleFl'] = gd_isset($searchData['firstSaleFl'], 'n');
        $this->search['withGiftFl'] = gd_isset($searchData['withGiftFl'], 'n');
        $this->search['withMemoFl'] = gd_isset($searchData['withMemoFl'], 'n');
        $this->search['withAdminMemoFl'] = gd_isset($searchData['withAdminMemoFl'], 'n');
        $this->search['withPacket'] = gd_isset($searchData['withPacket'], 'n');
        $this->search['overDepositDay'] = gd_isset($searchData['overDepositDay']);
        $this->search['invoiceCompanySno'] = gd_isset($searchData['invoiceCompanySno']);
        $this->search['invoiceNoFl'] = gd_isset($searchData['invoiceNoFl']);
        $this->search['underDeliveryDay'] = gd_isset($searchData['underDeliveryDay']);
        $this->search['underDeliveryOrder'] = gd_isset($searchData['underDeliveryOrder'], 'n');
        $this->search['couponNo'] = gd_isset($searchData['couponNo']);
        $this->search['couponNoNm'] = gd_isset($searchData['couponNoNm']);
        $this->search['couponAllFl'] = gd_isset($searchData['couponAllFl']);
        $this->search['eventNo'] = gd_isset($searchData['eventNo']);
        $this->search['eventNoNm'] = gd_isset($searchData['eventNoNm']);
        $this->search['dateSearchFl'] = gd_isset($searchData['dateSearchFl'], 'y');

        $this->search['purchaseNo'] = gd_isset($searchData['purchaseNo']);
        $this->search['purchaseNoNm'] = gd_isset($searchData['purchaseNoNm']);
        $this->search['purchaseNoneFl'] = gd_isset($searchData['purchaseNoneFl']);

        $this->search['brandNoneFl'] = gd_isset($searchData['brandNoneFl']);
        $this->search['brand'] = ArrayUtils::last(gd_isset($searchData['brand']));
        $this->search['brandCd'] = gd_isset($searchData['brandCd']);
        $this->search['brandCdNm'] = gd_isset($searchData['brandCdNm']);
        $this->search['orderNo'] = gd_isset($searchData['orderNo']);
        $this->search['orderMemoCd'] = gd_isset($searchData['orderMemoCd']);
        $this->search['view'] = gd_isset($searchData['view'], 'order');
        $this->search['deliveryMethodFl'] = gd_isset($searchData['deliveryMethodFl'], 'delivery');
        $this->search['reservationStatus'] = gd_isset($searchData['reservationStatus'], 'all');
        $this->search['orderStatusType'] = gd_isset($searchData['orderStatusType']);

        // 처리일자 검색
        if ($this->search['dateSearchFl'] == 'y' && $this->search['treatDateFl'] && isset($searchPeriod) && $searchPeriod != -1 && $this->search['treatDate'][0] && $this->search['treatDate'][1]) {
            switch (substr($this->search['treatDateFl'], -2)) {
                case '.b':
                case '.e':
                case '.r':
                    $this->arrWhere[] = ' oh.handleMode=? ';
                    $this->db->bind_param_push($this->arrBind, 's', substr($this->search['treatDateFl'], -1));
                    break;
            }
            $dateField = str_replace(['Dt.r', 'Dt.b', 'Dt.e'], 'Dt', $this->search['treatDateFl']);

            $this->arrWhere[] = $dateField . ' BETWEEN ? AND ?';
            $this->db->bind_param_push($this->arrBind, 's', $this->search['treatDate'][0] . ' 00:00:00');
            $this->db->bind_param_push($this->arrBind, 's', $this->search['treatDate'][1] . ' 23:59:59');
        }

        $this->arrWhere[] = " invoiceCompanySno IN (0, ".self::LOGISTICS_COMPANY_SNO.") ";

        // 배송정보 검색 (묶음배송)
        if ($this->search['withPacket'] == 'y') {
            $this->arrWhere[] = 'oi.packetCode != \'\'';
        }

        if (DateTimeUtils::intervalDay($this->search['treatDate'][0], $this->search['treatDate'][1]) > 365) {
            throw new AlertBackException(__('1년이상 기간으로 검색하실 수 없습니다.'));
        }

        // 주문/주문상품 탭 설정
        if (in_array($searchData['statusMode'], ['', 'o'])) {
            $this->search['view'] = gd_isset($searchData['view'], 'order');
        } elseif (in_array(substr($searchData['statusMode'], 0, 1), ['p', 'g', 'd', 's'])) {
            $this->search['view'] = gd_isset($searchData['view'], 'orderGoodsSimple');
        } else {
            $this->search['view'] = gd_isset($searchData['view'], 'orderGoods');
        }

        // CRM
        $this->search['memNo'] = gd_isset($searchData['memNo'], null);

        // --- 검색 설정
        $this->checked['purchaseNoneFl'][$this->search['purchaseNoneFl']] = $this->checked['mallFl'][$this->search['mallFl']] = $this->checked['scmFl'][$this->search['scmFl']] = $this->checked['memFl'][$this->search['memFl']] = $this->checked['manualPayment'][$this->search['manualPayment']] = $this->checked['firstSaleFl'][$this->search['firstSaleFl']] = $this->checked['withGiftFl'][$this->search['withGiftFl']] = $this->checked['withMemoFl'][$this->search['withMemoFl']] = $this->checked['withAdminMemoFl'][$this->search['withAdminMemoFl']] = $this->checked['withPacket'][$this->search['withPacket']] = $this->checked['underDeliveryOrder'][$this->search['underDeliveryOrder']] = $this->checked['invoiceNoFl'][$this->search['invoiceNoFl']] = $this->checked['brandNoneFl'][$this->search['brandNoneFl']] = $this->checked['couponAllFl'][$this->search['couponAllFl']] = $this->checked['receiptFl'][$this->search['receiptFl']] = $this->checked['memoType'][$this->search['memoType']] = $this->checked['view'][$this->search['view']] = $this->checked['deliveryMethodFl'][$this->search['deliveryMethodFl']] = $this->checked['reservationStatus'][$this->search['reservationStatus']] = $this->checked['orderStatusType'][$this->search['orderStatusType']] = $this->checked['userHandleViewFl'][$this->search['userHandleViewFl']] = 'checked="checked"';
        $this->checked['periodFl'][$searchPeriod] = 'active';

        if ($this->search['orderNo'] !== null) {
            $this->arrWhere[] = 'o.orderNo = ?';
            $this->db->bind_param_push($this->arrBind, 's', $this->search['orderNo']);
        }

        // 회원 주문인 경우 (CRM 주문조회)
        if ($this->search['memNo'] !== null) {
            $this->arrWhere[] = 'o.memNo = ?';
            $this->db->bind_param_push($this->arrBind, 's', $this->search['memNo']);
        }

        // 멀티상점 선택
        if ($this->search['mallFl'] !== 'all') {
            $this->arrWhere[] = 'o.mallSno = ?';
            $this->db->bind_param_push($this->arrBind, 's', $this->search['mallFl']);
        }

        // 공급사 선택
        if (Manager::isProvider()) {
            // 공급사로 로그인한 경우 기존 scm에 값 설정
            $this->arrWhere[] = 'og.scmNo = ' . Session::get('manager.scmNo');

            // 공급사에서는 입금대기 상태가 보여지면 안된다.
            $this->arrWhere[] = 'LEFT(og.orderStatus, 1) != \'o\'';

            // 공급사에서는 취소상태가 보여지면 안된다.
            $this->arrWhere[] = 'LEFT(og.orderStatus, 1) != \'c\' AND LEFT(og.orderStatus, 1) != \'f\'';
        } else {
            if ($this->search['scmFl'] == '1') {
                if (is_array($this->search['scmNo'])) {
                    foreach ($this->search['scmNo'] as $val) {
                        $tmpWhere[] = 'og.scmNo = ?';
                        $this->db->bind_param_push($this->arrBind, 's', $val);
                    }
                    $this->arrWhere[] = '(' . implode(' OR ', $tmpWhere) . ')';
                    unset($tmpWhere);
                } else if ($this->search['scmNo'] > 1) {
                    $this->arrWhere[] = 'og.scmNo = ?';
                    $this->db->bind_param_push($this->arrBind, 'i', $this->search['scmNo']);
                }
            } elseif ($this->search['scmFl'] == '0') {
                $this->arrWhere[] = 'og.scmNo = 1';
            }
        }

        // 키워드 검색
        if ($this->search['key'] && $this->search['keyword']) {
            $keyword = $this->search['keyword'];
            if ($this->search['key'] == 'all') {
                $tmpWhere = array_keys($this->search['combineSearch']);
                if ($this->getNaverPayConfig('useYn') == 'y') {    //네이버페이 사용할경우 네이버페이 주문번호도 추가 검색
                    $tmpWhere[] = 'o.apiOrderNo';
                }
                array_shift($tmpWhere);
                $arrWhereAll = [];
                foreach ($tmpWhere as $keyNm) {
                    // 전화번호인 경우 -(하이픈)이 없어도 검색되도록 처리
                    if (strpos($keyNm, 'Phone') !== false) {
                        $keyword = StringUtils::numberToPhone($keyword, true);
                    } else {
                        $keyword = $this->search['keyword'];
                    }
                    $arrWhereAll[] = '(' . $keyNm . ' LIKE concat(\'%\',?,\'%\'))';
                    $this->db->bind_param_push($this->arrBind, 's', $keyword);
                }
                $this->arrWhere[] = '(' . implode(' OR ', $arrWhereAll) . ')';
                unset($tmpWhere);
            } else {
                if ($this->search['key'] == 'o.orderNo') {    //네이버페이 사용중이고 주문번호 단일 검색일 경우
                    if ($this->getNaverPayConfig('useYn') == 'y') {
                        $this->arrWhere[] = '(' . $this->search['key'] . ' LIKE concat(\'%\',?,\'%\')  OR apiOrderNo LIKE concat(\'%\',?,\'%\')  )';
                        $this->db->bind_param_push($this->arrBind, 's', $keyword);
                    } else {
                        $this->arrWhere[] = $this->search['key'] . ' LIKE concat(\'%\',?,\'%\')';
                    }
                } else {
                    $this->arrWhere[] = $this->search['key'] . ' LIKE concat(\'%\',?,\'%\')';
                }


                // 전화번호인 경우 -(하이픈)이 없어도 검색되도록 처리
                if (strpos($this->search['key'], 'Phone') !== false) {
                    $keyword = StringUtils::numberToPhone($keyword, true);
                } else {
                    $keyword = $this->search['keyword'];
                }
                $this->db->bind_param_push($this->arrBind, 's', $keyword);
            }
        }

        // 주문유형
        if ($this->search['orderTypeFl'][0]) {
            foreach ($this->search['orderTypeFl'] as $val) {
                $tmpWhere[] = 'o.orderTypeFl = ?';
                $this->db->bind_param_push($this->arrBind, 's', $val);
                $this->checked['orderTypeFl'][$val] = 'checked="checked"';
            }
            $this->arrWhere[] = '(' . implode(' OR ', $tmpWhere) . ')';
            unset($tmpWhere);
        } else {
            $this->checked['orderTypeFl'][''] = 'checked="checked"';
        }

        // 주문채널
        if ($this->search['orderChannelFl'][0]) {
            foreach ($this->search['orderChannelFl'] as $val) {
                $tmpWhere[] = 'o.orderChannelFl = ?';
                $this->db->bind_param_push($this->arrBind, 's', $val);
                $this->checked['orderChannelFl'][$val] = 'checked="checked"';
            }
            $this->arrWhere[] = '(' . implode(' OR ', $tmpWhere) . ')';
            unset($tmpWhere);
        } else {
            $this->checked['orderChannelFl'][''] = 'checked="checked"';
        }

        //배송방식이 택배만 인경우
        if ($this->search['deliveryMethodFl'] == 'delivery') {
            $this->arrWhere[] = "og.deliveryMethodFl = 'delivery'";
        }

        // 결제 방법
        if ($this->search['settleKind'][0]) {
            foreach ($this->search['settleKind'] as $val) {
                if ($val == self::SETTLE_KIND_DEPOSIT) {
                    $tmpWhere[] = 'o.useDeposit > 0';
                } elseif ($val == self::SETTLE_KIND_MILEAGE) {
                    $tmpWhere[] = 'o.useMileage > 0';
                } else {
                    $tmpWhere[] = 'o.settleKind = ?';
                    $this->db->bind_param_push($this->arrBind, 's', $val);
                }
                $this->checked['settleKind'][$val] = 'checked="checked"';
            }
            $this->arrWhere[] = '(' . implode(' OR ', $tmpWhere) . ')';
            unset($tmpWhere);
        } else {
            $this->checked['settleKind'][''] = 'checked="checked"';
        }


        // 회원여부 및 그룹별 검색
        if ($this->search['memFl']) {
            if ($this->search['memFl'] == 'y') {
                // 회원그룹선택
                if (is_array($this->search['memberGroupNo'])) {
                    foreach ($this->search['memberGroupNo'] as $val) {
                        $tmpWhere[] = 'm.groupSno = ?';
                        $this->db->bind_param_push($this->arrBind, 's', $val);
                    }
                    $this->arrWhere[] = '(' . implode(' OR ', $tmpWhere) . ')';
                    unset($tmpWhere);
                } else if ($this->search['memberGroupNo'] > 1) {
                    $this->arrWhere[] = 'm.groupSno = ?';
                    $this->db->bind_param_push($this->arrBind, 'i', $this->search['memberGroupNo']);
                }

                // 회원만
                $this->arrWhere[] = 'o.memNo > 0';
            } elseif ($this->search['memFl'] == 'n') {
                $this->arrWhere[] = 'o.memNo = 0';
            }
        }

        // 첫주문 검색
        if ($this->search['firstSaleFl'] == 'y') {
            $this->arrWhere[] = 'o.firstSaleFl = ?';
            $this->db->bind_param_push($this->arrBind, 's', $this->search['firstSaleFl']);
        }

        // 영수증 검색
        if ($this->search['receiptFl']) {
            $this->arrWhere[] = 'o.receiptFl = ?';
            $this->db->bind_param_push($this->arrBind, 's', $this->search['receiptFl']);
        }

        // 배송정보 검색 (사은품 포함)
        if ($this->search['withGiftFl'] == 'y') {
            $this->arrWhere[] = '(SELECT COUNT(sno) FROM ' . DB_ORDER_GIFT . ' WHERE orderNo = og.orderNo) > 0';
        }

        // 배송정보 검색 (배송메시지 입력)
        if ($this->search['withMemoFl'] == 'y') {
            $this->arrWhere[] = 'oi.orderMemo != \'\'';
        }

        // 상품º주문번호별 메모 (관리자 메모 입력)
        if ($this->search['withAdminMemoFl'] == 'y') {
            if ($this->search['orderMemoCd']) {
                $this->arrWhere[] = 'aogm.memoCd=?';
                $this->db->bind_param_push($this->arrBind, 's', $this->search['orderMemoCd']);
            } else {
                $this->arrWhere[] = 'aogm.memoCd != \'\'';
            }
            //$this->arrWhere[] = 'o.adminMemo != \'\'';
        }

        // 입금경과일
        if ($this->search['overDepositDay'] > 0) {
            $this->arrWhere[] = 'og.orderStatus = \'o1\' AND date_format(og.regDt, \'%Y%m%d\') < ?';
            $this->db->bind_param_push($this->arrBind, 's', date('Ymd', strtotime('-' . $this->search['overDepositDay'] . ' day')) . '000000');
        }


        // 송장번호 검색
        if ($this->search['invoiceCompanySno'] > 0) {
            $this->arrWhere[] = 'og.invoiceCompanySno=?';
            $this->db->bind_param_push($this->arrBind, 's', $this->search['invoiceCompanySno']);
        }

        // 송장번호 유무 체크
        if ($this->search['invoiceNoFl'] === 'y') {
            $this->arrWhere[] = 'og.invoiceNo<>\'\'';
        } elseif ($this->search['invoiceNoFl'] === 'n') {
            $this->arrWhere[] = 'og.invoiceNo=\'\'';
        }

        if ($this->search['couponAllFl'] === 'y') {
            //쿠폰사용 주문 전체 검색
            $this->arrWhere[] = '(o.totalCouponGoodsDcPrice > 0 OR o.totalCouponOrderDcPrice > 0 OR o.totalCouponDeliveryDcPrice > 0)';
        } else {
            // 쿠폰 검색
            if ($this->search['couponNo'] > 0) {
                $this->arrWhere[] = 'mc.couponNo=?';
                $this->db->bind_param_push($this->arrBind, 's', $this->search['couponNo']);
            }
        }

        // 공급사 정산 검색
        if ($this->search['scmAdjustNo']) {
            if ($this->search['scmAdjustType'] == 'oa') {
                $this->arrWhere[] = 'og.scmAdjustAfterNo = ?';
                $this->db->bind_param_push($this->arrBind, 'i', $this->search['scmAdjustNo']);
            } else if ($this->search['scmAdjustType'] == 'o') {
                $this->arrWhere[] = 'og.scmAdjustNo = ?';
                $this->db->bind_param_push($this->arrBind, 'i', $this->search['scmAdjustNo']);
            } else if ($this->search['scmAdjustType'] == 'da') {
                $this->arrWhere[] = 'od.scmAdjustAfterNo = ?';
                $this->db->bind_param_push($this->arrBind, 'i', $this->search['scmAdjustNo']);
            } else if ($this->search['scmAdjustType'] == 'd') {
                $this->arrWhere[] = 'od.scmAdjustNo = ?';
                $this->db->bind_param_push($this->arrBind, 'i', $this->search['scmAdjustNo']);
            }
        }

        // 배송 검색
        if ($this->search['invoiceFl']) {
            if ($this->search['invoiceFl'] == 'y')
                $this->arrWhere[] = 'og.invoiceNo !=""'; else if ($this->search['invoiceFl'] == 'n')
                $this->arrWhere[] = 'og.invoiceNo =""'; else $this->arrWhere[] = 'TRIM(oi.receiverCellPhone) NOT REGEXP \'^([0-9]{3,4})-?([0-9]{3,4})-?([0-9]{4})$\'';

            $this->checked['invoiceFl'][$this->search['invoiceFl']] = 'checked="checked"';
        } else {
            $this->checked['invoiceFl'][''] = 'checked="checked"';
        }

        // 매입처 검색
        if (($this->search['purchaseNo'] && $this->search['purchaseNoNm'])) {
            if (is_array($this->search['purchaseNo'])) {
                foreach ($this->search['purchaseNo'] as $val) {
                    $tmpWhere[] = 'og.purchaseNo = ?';
                    $this->db->bind_param_push($this->arrBind, 's', $val);
                }
                $this->arrWhere[] = '(' . implode(' OR ', $tmpWhere) . ')';
                unset($tmpWhere);
            }
        }

        //매입처 미지정
        if ($this->search['purchaseNoneFl']) {
            $this->arrWhere[] = '(og.purchaseNo IS NULL OR og.purchaseNo  = "" OR og.purchaseNo  <= 0)';
        }

        // 브랜드 검색
        if (($this->search['brandCd'] && $this->search['brandCdNm']) || $this->search['brand']) {
            if (!$this->search['brandCd'] && $this->search['brand'])
                $this->search['brandCd'] = $this->search['brand'];
            $this->arrWhere[] = 'g.brandCd = ?';
            $this->db->bind_param_push($this->arrBind, $fieldTypeGoods['brandCd'], $this->search['brandCd']);
        } else {
            $this->search['brandCd'] = '';
        }

        //브랜드 미지정
        if ($this->search['brandNoneFl']) {
            $this->arrWhere[] = 'g.brandCd  = ""';
        }

        $reservationPossibleStatus = ['p','g']; //예약가능한 주문상태
        $reservationImpossibleStatus = ['d','s','e','r'];   //예약불가능한 주문상태
        if(empty($this->search['orderStatusType'])) {
            $this->search['orderStatusType'] = 'possibleReservation';
            $this->search['orderStatus'] = $reservationPossibleStatus;
        }

        // 주문 상태 모드가 있는 경우
        $status = null;
        if ($this->search['orderStatusType'] == 'delivery') {    //배송
            $reservationType  = 'imPossibleReservation';
            $status = ['d','s'];
        } else if ($this->search['orderStatusType'] == 'exchange') {    //교환
            $reservationType  = 'imPossibleReservation';
            $status = ['e'];
        } else if ($this->search['orderStatusType'] == 'refund') {    //환불
            $reservationType  = 'imPossibleReservation';
            $status = ['r'];
        } else if($this->search['orderStatusType'] == 'possibleReservation'){   //예약가능
            $reservationType  = 'possibleReservation';
            foreach ($this->search['orderStatus'] as $val) {
                $this->checked['orderStatus'][$val] = 'checked';
                $status[] = $val;
            }
        }
        else {   //주문상태 전체
            $reservationType  = 'all';
        }

        if (empty($status) === false) {
            $this->arrWhere[] = $this->buildWhereStatusQuery($status);
        }

        $reservationWhere['possibleReservation']['ready'] = "(lo.reqDvCd IS NULL OR lo.reqDvCd != '01' )   and (invoiceNo = '' or invoiceNo is null)";
        $reservationWhere['possibleReservation']['complete'] = "reqDvCd = '01' ";
        $reservationWhere['possibleReservation']['all'] =  "(invoiceNo = '' or invoiceNo is null)";
        $reservationWhere['imPossibleReservation']['ready'] = " FALSE ";
        $reservationWhere['imPossibleReservation']['complete'] = " reqDvCd = '01' ";
        $reservationWhere['imPossibleReservation']['all'] = " reqDvCd = '01' ";

        $reservationWhere['all']['ready'] = $this->buildWhereStatusQuery($reservationPossibleStatus).' AND '.$reservationWhere['possibleReservation']['ready'];
        $reservationWhere['all']['complete'] ='('. $this->buildWhereStatusQuery(array_merge($reservationPossibleStatus,$reservationImpossibleStatus)).' AND '.$reservationWhere['possibleReservation']['complete']. ") ";
        $reservationWhere['all']['all'] = "(".$this->buildWhereStatusQuery(($reservationPossibleStatus)).' AND ('.$reservationWhere['possibleReservation']['all'].")  OR (reqDvCd = '01'))";

        $this->arrWhere[] = '('.$reservationWhere[$reservationType][$this->search['reservationStatus'] ].')';

        if (empty($this->arrBind)) {
            $this->arrBind = null;
        }
    }

    protected function buildWhereStatusQuery(array $statusList = null)
    {
        if(!$statusList) {
            return null;
        }
        $expendStatus = ['p', 'g', 'd'];//추가 가능한 주문상태
        $execStatus = null;
        $where = null;

        foreach ($statusList as $status) {
            if (in_array($status, $expendStatus)) {
                $sameOrderStatus = $this->getOrderStatusList($status, null, null, 'orderList');
                $sameOrderStatus = array_keys($sameOrderStatus);
                $sameOrderStatusCount = count($sameOrderStatus);
                if ($sameOrderStatusCount > 0) {
                    foreach ($sameOrderStatus as $valStatus) {
                        $execStatus[] = $valStatus;
                    }
                } else {
                    $execStatus[] = $status;
                }
                //                }
                unset($tmpWhere);
            } else {
                if (strlen($status) == 1) {
                    $execStatus[] = $status.'1';
                    $execStatus[] = $status.'2';
                    $execStatus[] = $status.'3';
                } else {
                    $execStatus[] = $status;
                }
            }
        }
        if($execStatus) {
            $where = "og.orderStatus IN ('" . implode("','", $execStatus) . "')";
        }

        return $where;
    }

    /**
     * 일괄 예약
     *
     * @param array  $orderGoodsNoList
     * @param string $mode
     * @param string $viewType
     *
     * @throws Exception
     */
    public function batchReservation(array $orderGoodsNoList, $viewType = 'goods')
    {
        if (count($orderGoodsNoList) > 500) {
            throw new \Exception('한번에 501개 이상 예약할 수 없습니다.');
        }
        $mode = 'reservation';
        $groupReservation = null;
        for($i=0;$i<count($orderGoodsNoList);$i++) {
            $groupKey = (int)($i/10);
            $groupReservation[$groupKey][] = $orderGoodsNoList[$i];
        }
        $this->logisticsLogger->info('__일괄 택배예약[START]__', [__METHOD__]);
        $this->logisticsLogger->info('parameter', [$viewType, $orderGoodsNoList]);
        unset($orderGoodsNoList);
        foreach($groupReservation as $checkList) {
            $orderGoodsNoList = null;
            foreach($checkList as $val) {
                if(strpos($val, INT_DIVISION)!==false) {
                    foreach(explode(INT_DIVISION,$val) as $_val) {
                        $orderGoodsNoList[] = $_val;
                    }
                }
                else {
                    $orderGoodsNoList[] = $val;
                }
            }

            $logisticsSchema = new LogisticsSchema();
            $arrOrderGoodsData = $this->getReservationOrderGoodsData($orderGoodsNoList);
            $arrSchemaData = null;
            $saveReserveData = null;
            foreach ($arrOrderGoodsData as $key => $orderGoodsData) {
                $checkReservation = $this->checkReservation($orderGoodsData, $mode) ;
                if($checkReservation['result'] === false ) {
                    throw new \Exception($orderGoodsData['orderNo'].'주문의 '.$checkReservation['errorMsg']);
                }

                $schema = $logisticsSchema->buildSchemaByOrderGoodsData($orderGoodsData, $mode, $viewType == 'order');
                $schemaCheck = $logisticsSchema->validationSchema('reservation', $schema);
                if ($schemaCheck['result'] === true) {
                    $arrSchemaData[] = $schema;
                } else {
                    $this->logisticsLogger->info('fail reservation end', [$schema['CUST_USE_NO'],$schemaCheck['errorMsg'], $schema]);
                    throw new \Exception($orderGoodsData['orderNo'].'주문의 '. implode("<br>",$schemaCheck['errorMsg']));
                }
                $saveReserveData[$schema['CUST_USE_NO']]['schema'] = $schema;
            }

            $result = $this->sendReservationApi($arrSchemaData);
            foreach ($saveReserveData as $custUseNo=>$val) {
                $this->logisticsLogger->info('custUseNo', [$custUseNo]);
                $parseCustUseNo = $this->parseCustUseNo($custUseNo);
                $isSuccess = $result[$custUseNo]['result'] == 'ok' ? true : false;
                $errorMsg = $result[$custUseNo]['msg'];
                if($isSuccess === false) {
                    $errorOrderNo[] = '['.$parseCustUseNo['orderNo'].']('.$parseCustUseNo['orderGoodsNo'].') 주문 예약이 실패했습니다.('.$errorMsg.')';
                    $errorMsg = $errorMsg ? $errorMsg : '알 수 없는 오류';
                }
                $this->addReservation($custUseNo, $val['schema'],$result[$custUseNo] ,$errorMsg);
            }
        }

        if($errorOrderNo) {
            throw new \Exception(implode('<br>',$errorOrderNo));
        }

        $this->logisticsLogger->info('__일괄 택배예약[END]__');
    }

    public function cancelReservationByOrderGoodsNo($orderGoodsNo)
    {
        if(!$orderGoodsNo) {
            throw new \Exception('empty orderGoodsSno');
        }
        $this->logisticsLogger->info('택배 예약취소[START]__', [__METHOD__]);
        $this->logisticsLogger->info('parameter orderGoodsNo', [$orderGoodsNo]);
        $cancelOrderGoodsDatas = $this->getReservationOrderGoodsData([$orderGoodsNo], true);
        if(empty($cancelOrderGoodsDatas)){
            throw new \Exception('예약취소 가능한 주문이 존재하지 않습니다.');
        }

        foreach($cancelOrderGoodsDatas as $orderGoodsData) {
            $this->cancelReservationByOrderGoodsData($orderGoodsData);
        }
    }

    public function cancelReservationByOrderInfoSno($orderInfoSno)
    {
        if(!$orderInfoSno) {
            throw new \Exception('empty orderInfoSno');
        }
        $this->logisticsLogger->info('택배 예약취소[START]__', [__METHOD__]);
        $this->logisticsLogger->info('parameter orderInfoSno', [$orderInfoSno]);
        $cancelOrderGoodsDatas = $this->getReservationOrderGoodsDataByOrderInfoSno($orderInfoSno, true);
        if(empty($cancelOrderGoodsDatas)){
            throw new \Exception('예약취소 가능한 주문이 존재하지 않습니다.');
        }

        foreach($cancelOrderGoodsDatas as $orderGoodsData) {
            $this->cancelReservationByOrderGoodsData($orderGoodsData);
        }
    }

    public function cancelReservationByMpckKey($mpckKey)
    {
        if(!$mpckKey) {
            throw new \Exception('empty mpckKey');
        }
        $this->logisticsLogger->info('택배 예약취소[START]__', [__METHOD__]);
        $this->logisticsLogger->info('parameter mpckKey', [$mpckKey]);
        $cancelOrderGoodsDatas = $this->getReservationOrderGoodsDataByMpckKey($mpckKey, true);
        //        debug($cancelOrderGoodsDatas,true);
        if(empty($cancelOrderGoodsDatas)){
            throw new \Exception('예약취소 가능한 주문이 존재하지 않습니다.');
        }

        foreach($cancelOrderGoodsDatas as $orderGoodsData) {
            $this->cancelReservationByOrderGoodsData($orderGoodsData);
        }
    }

    protected function cancelReservationByOrderGoodsData($orderGoodsData)
    {
        $this->logisticsLogger->info('cancel custUseNo', [$orderGoodsData['custUseNo']]);
        $checkReservation = $this->checkReservation($orderGoodsData, 'cancel') ;
        if($checkReservation['result'] === false ) {
            throw new \Exception('예약취소할 수 없는 주문입니다.('.$checkReservation['errorMsg'].')');
        }
        $logisticsSchema = new LogisticsSchema();
        $arrSchemaData = null;
        $saveCheck = null;
        $custUseNo = $orderGoodsData['custUseNo'];
        $schema = $logisticsSchema->buildSchemaByOrderGoodsData($orderGoodsData, 'cancel',false);
        $result = $logisticsSchema->validationSchema('reservation', $schema);
        if ($result['result'] === true) {
            $arrSchemaData[] = $schema;
        } else {
            $this->logisticsLogger->info('fail reservation', [$orderGoodsData['custUseNo'],$result['errorMsg'] , $schema]);
            throw new \Exception($result['errorMsg'][0]);
        }
        $result = $this->sendReservationApi($arrSchemaData);
        $code = $result[$arrSchemaData[0]['CUST_USE_NO']]['code'] ;
        $isSuccess = $result[$arrSchemaData[0]['CUST_USE_NO']]['result'] == 'ok'  ? true : false;
        $errorMsg = $result[$arrSchemaData[0]['CUST_USE_NO']]['msg'] ;
        if($code == 'duplicate') {  //결과가 실패더라도 예약이 중복이면 `예약상태로` 업데이트
            $isSuccess = true;
        }
        $this->addReservation($custUseNo, $schema,$result[$arrSchemaData[0]['CUST_USE_NO']], $errorMsg );
        $this->logisticsLogger->info('택배 예약취소[END]__', [__METHOD__]);
        if($isSuccess === false) {
            throw new \Exception($errorMsg);
        }
    }

    /**
     * sendReservationApi
     *
     * @param $data
     *
     * @return array
     * @throws Exception
     */
    protected function sendReservationApi(array $data)
    {
        //일괄전송
        $jsonData = json_encode($data);
        $encryptData = $this->OpenSSLEncrypt($jsonData);
        $encryptText = ['data'=>$encryptData];
        $apiUrl = 'https://cjlogistics.godo.co.kr/api/requestOrder.php';
        $this->logisticsLogger->info('중계서버 예약[parameter]',[$jsonData, $encryptText]);
        // 중계서버로 예약요청
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ch, CURLOPT_TIMEOUT , 3);  //실행시간 타임아웃 3초
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($encryptText));
        $result = curl_exec($ch);

        if(curl_errno($ch)) {
            throw new \Exception(sprintf('통신장애입니다.(code : %s)',curl_errno($ch)));
        }
        curl_close($ch);
        $decodeResult = json_decode($this->OpenSSLDecrypt($result), true);
        if(empty($decodeResult)) {
            throw new \Exception('대한통운 택배 예약하기가 실패하였습니다.(response empty)');
        }
        $this->logisticsLogger->info('중계서버 예약[result]', [$decodeResult,$result,__METHOD__]);

        return $decodeResult;
    }

    public function parseCustUseNo($custUseNo)
    {
        $result = explode('_',$custUseNo);
        return [
            'custId' => $result[0],
            'orderNo' => $result[1],
            'orderGoodsNo' => $result[2],
            'time' => $result[3],
        ];
    }

    /**
     * 예약주문 건당 예약하기
     * errorMsg 가 존재하면 상태값 업데이트 안시킴.
     *
     * @param        $custUseNo
     * @param null   $apiRequestData
     * @param null   $apiResponseData
     * @param string $errorMsg
     *
     * @return mixed
     * @throws Exception
     */
    public function addReservation($custUseNo, $apiRequestData = null,$apiResponseData = null, $errorMsg = '')
    {
        $parseCustUseNo = $this->parseCustUseNo($custUseNo);
        $data['custUseNo'] = $custUseNo;
        $data['orderNo'] = $parseCustUseNo['orderNo'];
        $data['orderGoodsNo'] = $parseCustUseNo['orderGoodsNo'];
        $data['custId'] = $apiRequestData['CUST_ID'];
        if(empty($errorMsg) ){
            $data['mpckKey'] = $apiRequestData['MPCK_KEY'];
            $data['mpckSeq'] = $apiRequestData['MPCK_SEQ'];
            $data['reqDvCd'] = $apiRequestData['REQ_DV_CD'];
        }
        else {
            $data['reqDvCd'] = '00';
        }

        $data['apiRequestData'] = $apiRequestData;
        $data['apiResponseData'] = $apiResponseData;
        $data['errorMsg'] = $errorMsg;
        $data['deliveryStatus'] = 'wait';
        $data['current'] = 'y';
        $this->db->begin_tran();
        try {
            $this->resetReservationByOrderGoodsNo($data['orderGoodsNo']);
            $arrBind = $this->db->get_binding(DBTableField::tableLogisticsOrder(), $data, 'insert', array_keys($data));
            $this->db->set_insert_db(DB_LOGISTICS_ORDER, $arrBind['param'], $arrBind['bind'], 'y');
            $this->db->commit();
        } catch(\Exception $e) {
            $this->db->rollback();
            throw $e;
        }
        return $this->db->insert_id();
    }

    protected function resetReservationByOrderGoodsNo($orderGoodsNo)
    {
        $updateArrBind = null;
        $query = "UPDATE " . DB_LOGISTICS_ORDER . " SET current = 'n' WHERE orderGoodsNo = ?";
        $this->db->bind_param_push($updateArrBind, 'i', $orderGoodsNo);
        $this->db->bind_query($query, $updateArrBind);
    }

    public function syncReservation($orderNo, $orderGoodsNo,  $apiResponseData = null , $errorMsg = '')
    {
        //업데이트
        $arrBind = null;
        $data = null;
        $data['reqDvCd'] = '01';
        $date['syncDate'] = 'now()';
        if($apiResponseData){
            $data['apiResponseData'] = $apiResponseData;
        }
        $data['errorMsg'] = $errorMsg;
        $data['syncStatus'] = 'update';

        $arrBind = $this->db->get_binding(DBTableField::tableLogisticsOrder(), $data,'update',array_keys($data));
        $this->db->bind_param_push($arrBind['bind'], 's', $orderNo);
        $this->db->bind_param_push($arrBind['bind'], 'i', $orderGoodsNo);
        $this->db->set_update_db(DB_LOGISTICS_ORDER, $arrBind['param'],'orderNo = ? AND orderGoodsNo = ?', $arrBind['bind']);
    }

    public function getOrderStatusByLogisticsStatus($crgSt)
    {
        $orderStatus = '';
        switch ($crgSt)
        {
            case '11':
            case '41':
            case '42':
            case '82':
                $orderStatus = 'd1';//배송중
                break;
            case '91':
                $orderStatus = 'd2';    //배송완료
                break;
            case '01':
            case '12':
            case '84':
                $orderStatus = 'g1';  //준비중
                break;
            default :
        }

        return $orderStatus;
    }

    public function syncReservationByMpckKey($mpckKey,  $apiResponseData = null , $errorMsg = '')
    {
        //업데이트
        $arrBind = null;
        $data = null;
        $data['reqDvCd'] = '01';
        $data['CRG_ST'] = $apiResponseData['CRG_ST'];
        $date['syncDate'] = 'now()';
        if($apiResponseData){
            $data['apiResponseData'] = $apiResponseData;
        }
        $data['errorMsg'] = $errorMsg;
        $deliveryStatus = null;
        if($orderStatus = $this->getOrderStatusByLogisticsStatus($apiResponseData['CRG_ST'])) {
            if($orderStatus == 'd1') {
                $deliveryStatus = 'send';
            }
            else if($orderStatus == 'd2') {
                $deliveryStatus = 'complete';
            }
            else {
                $deliveryStatus = 'wait';
            }
        }
        if($deliveryStatus) {
            $data['deliveryStatus'] = $deliveryStatus;
        }

        $arrBind = $this->db->get_binding(DBTableField::tableLogisticsOrder(), $data,'update',array_keys($data));
        $this->db->bind_param_push($arrBind['bind'], 's', $mpckKey);
        $this->db->set_update_db(DB_LOGISTICS_ORDER, $arrBind['param'],'mpckKey = ? ', $arrBind['bind']);
    }


    public function getReservationOrderGoodsData(array $orderGoodsNoList, $isCancel = false)
    {
        $arrIncludeOi = [
            'orderName',
            'receiverName',
            'orderMemo',
            'orderCellPhone',
            'packetCode',
        ];
        $arrIncludeLo = [
            'custUseNo',
            'custId', // 고객사용번호
            'reservationStatus', // 예약상태
            'mpckKey', // 합포장키
            'mpckSeq', // 합포장순번
            'requestData', // 중계서버 통신 전문
            'reqDvCd',
            'if(reqDvCd = "01","예약완료","예약전") as reservationStatus',
        ];
        $arrIncludeOg = [
            'sno',
            'orderNo',
            'apiOrderGoodsNo',
            'commission',
            'goodsType',
            'orderCd',
            'userHandleSno',
            'handleSno',
            'orderStatus',
            'goodsNm',
            'goodsNmStandard',
            'goodsCnt',
            'goodsPrice',
            'optionPrice',
            'optionTextPrice',
            'addGoodsPrice',
            'divisionUseDeposit',
            'divisionUseMileage',
            'divisionCouponOrderDcPrice',
            'goodsDcPrice',
            'memberDcPrice',
            'memberOverlapDcPrice',
            'couponGoodsDcPrice',
            'goodsDeliveryCollectPrice',
            'goodsDeliveryCollectFl',
            'optionInfo',
            'optionTextInfo',
            'invoiceCompanySno',
            'invoiceNo',
            'addGoodsCnt',
            'paymentDt',
            'cancelDt',
            'timeSaleFl',
            'checkoutData',
            'og.regDt',
            'LEFT(og.orderStatus, 1) as statusMode',
            'deliveryMethodFl',
            'goodsCd',
            'taxVatGoodsPrice',
            'hscode',
            'brandCd',
            'goodsModelNo',
            'costPrice',
            'cancelDt',
            'goodsTaxInfo',
            'makerNm',
            'deliveryDt',
            'deliveryCompleteDt',
        ];
        $tmpField[] = DBTableField::setTableField('tableOrderInfo', null, null, 'oi');
        $tmpField[] = DBTableField::setTableField('tableLogisticsOrder', $arrIncludeLo, null, 'lo');;
        $tmpField[] = DBTableField::setTableField('tableOrderGoods', $arrIncludeOg, null, 'og');;
        $tmpField[] = ['oi.sno AS orderInfoSno',];

        // join 문
        $join[] = ' LEFT JOIN ' . DB_ORDER . ' o ON o.orderNo = og.orderNo ';
        $join[] = ' LEFT JOIN ' . DB_ORDER_DELIVERY . ' od ON og.orderDeliverySno = od.sno ';
        $join[] = ' LEFT JOIN ' . DB_GOODS . ' g ON og.goodsNo = g.goodsNo ';
        $join[] = ' LEFT JOIN ' . DB_ORDER_INFO . ' oi ON (og.orderNo = oi.orderNo)   
                    AND (CASE WHEN od.orderInfoSno > 0 THEN od.orderInfoSno = oi.sno ELSE oi.orderInfoCd = 1 END)';
        $join[] = ' LEFT OUTER JOIN ' . DB_LOGISTICS_ORDER. ' AS lo ON lo.orderGoodsNo = og.sno AND lo.current=\'y\' ';

        // 쿼리용 필드 합침
        $tmpKey = array_keys($tmpField);
        $arrField = [];
        foreach ($tmpKey as $key) {
            $arrField = array_merge($arrField, $tmpField[$key]);
        }
        unset($tmpField, $tmpKey);

        // 현 페이지 결과
        $this->db->strField = 'o.orderChannelFl,og.sno,o.memNo,g.goodsNm ,' . implode(', ', $arrField) . ',og.orderDeliverySno';
        // addGoods 필드 변경 처리 (goods와 동일해서)


        $arrBind = null;

        if($isCancel === true){
            $arrWhere[] = "lo.mpckKey IN ('".implode("','",$mpckKeyList)."')";
            $arrWhere[] = "reqDvCd = '01' ";
            $arrWhere[] = "(invoiceNo is null OR invoiceNo = '')";
        }
        else {
            $arrWhere[] = "og.sno IN  (".implode(',',$orderGoodsNoList).")";
        }
        foreach($orderGoodsNoList as $orderGoodsNo) {
            $this->db->bind_param_push($arrBind, 'i', $orderGoodsNo);
        }
        $this->db->strJoin = implode('', $join);
        $this->db->strWhere = implode(' AND ', gd_isset($arrWhere));


        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_GOODS . ' og ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);
        return $getData;
    }



    public function OpenSSLEncrypt($data){
        $secKey = 'eogksxhddns@!dusehd';
        if( $secKey == '' ){
            throw new exception('NONE KEY VALUE');
        }

        $iv = substr(md5($secKey),3,16);

        if( strlen($iv) == 0 || strlen($iv) < 16 ){
            throw new Exception('Encrypt Error::OpenSSLEncrypt',0);
        }

        $EncryptStr = openssl_encrypt($data , 'aes-256-cbc', $secKey, true, $iv);

        return base64_encode($EncryptStr);
    }

    public function OpenSSLDecrypt($data) {
        $secKey = 'eogksxhddns@!dusehd';
        if( $secKey == '' ){
            throw new exception('NONE KEY VALUE');
        }

        $iv = substr(md5($secKey),3,16);

        if( strlen($iv) == 0 || strlen($iv) < 16 ){
            throw new Exception('Encrypt Error::OpenSSLEncrypt',0);
        }
        $DecryptStr = openssl_decrypt(base64_decode($data), 'aes-256-cbc', $secKey, OPENSSL_RAW_DATA, $iv);

        return $DecryptStr;
    }

    /**
     * 예약,예약취소 가능여부 체크
     *
     * @param      $orderGoodsData
     * @param      $mode (reservation:예약, cancel:예약취소)
     *
     * @return array
     */
    public function checkReservation($orderGoodsData, $mode)
    {
        $errorMsg = null;
        if(($orderGoodsData['invoiceCompanySno'] == 0 || $orderGoodsData['invoiceCompanySno'] == self::LOGISTICS_COMPANY_SNO) === false) {
            $errorMsg = sprintf('%s(%s) 대한통운 택배사가 아닌 다른택배사가 지정되어있습니다.(%s)');
        }
        if($mode == 'reservation') {    //예약
            //중복예약 체크
            if (($orderGoodsData['reqDvCd'] == '01')) {
                $errorMsg = sprintf('%s(%s) 이미 예약된 상품주문번호', $orderGoodsData['orderNo'], $orderGoodsData['sno']);
            }

            //주문상태 체크
            $possibleStatus = ['p','g'];
            if(!in_array(substr($orderGoodsData['orderStatus'],0,1),$possibleStatus) && !in_array($orderGoodsData['orderStatus'],$possibleStatus)){
                $errorMsg = sprintf('%s(%s) 불가능한 주문상태(%s)', $orderGoodsData['orderNo'], $orderGoodsData['sno'], $orderGoodsData['orderStatus']);
            }
        }
        else {  //취소
            //예약상태 체크
            if ($orderGoodsData['reqDvCd'] != '01') {
                $errorMsg = sprintf('%s(%s) 중복예약', $orderGoodsData['orderNo'], $orderGoodsData['sno']);
            }
            //송장번호 체크
            if(empty($orderGoodsData['invoiceNo']) === false){
                $errorMsg = sprintf('%s(%s) 취소시킬 송장번호가 없음.', $orderGoodsData['orderNo'], $orderGoodsData['sno']);
            }
        }
        $result = empty($errorMsg) ? true : false;

        return ['result'=>$result, 'errorMsg'=>$errorMsg];
    }

    /**
     * 사용자등록
     *
     * @param array $data
     *
     * @return mixed
     * @throws Exception
     */
    public function sendConfigSaveApi(array $data)
    {
        //일괄전송
        $globals = \App::getInstance('globals');
        $sendData = [
            'custId' => $data['CUST_ID'],
            'registDate'=> date('Y-m-d h:i:s'),
            'shopSno'=>$globals->get('gLicense.godosno'),
            'solution'=>'godomall5',
            'domain'=>URI_HOME,
        ];

        $jsonData = json_encode($sendData);
        $encryptData = $this->OpenSSLEncrypt($jsonData);
        $encryptText = ['data'=>$encryptData];
        $apiUrl = 'https://cjlogistics.godo.co.kr/api/registUser.php';
        $this->logisticsLogger->info('대한통운설정 저장[send api parameter]',[$jsonData, $encryptText,__METHOD__]);
        // 중계서버로 예약요청
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($encryptText));
        $result = curl_exec($ch);
        curl_close($ch);

        $this->logisticsLogger->info('대한통운설정 저장[api result]', [$result,__METHOD__]);

        return $result;
    }

    /**
     * 사용자아이디 검증
     *
     * @param $custId
     *
     * @return mixed
     * @throws Exception
     */
    public function checkCustIdApi($custId)
    {
        $encryptData = $this->OpenSSLEncrypt($custId);
        $sendData = ['custId'=>$encryptData];
        $apiUrl = 'https://cjlogistics.godo.co.kr/api/checkClient.php';
        $this->logisticsLogger->info('사용자아이디 검증[send api parameter]',[$sendData]);
        // 중계서버로 예약요청
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($sendData));
        $result = curl_exec($ch);
        curl_close($ch);
        $this->logisticsLogger->info('사용자아이디 검증[api result]', [$result,__METHOD__]);

        return $result;
    }

    /**
     * 리스트 가공
     *
     * @param        $getData
     * @param bool   $isUserHandle
     * @param bool   $isDisplayOrderGoods
     * @param bool   $setInfoFl
     * @param string $searchStatuscheckedMode
     *
     * @return mixed
     */
    public function setOrderListForAdmin($getData, $isUserHandle = false, $isDisplayOrderGoods = false, $setInfoFl = false, $searchStatuscheckedMode = '')
    {
        $delivery = new Delivery();
        $delivery->setDeliveryMethodCompanySno();
        $orderBasic = gd_policy('order.basic');
        if (($orderBasic['userHandleAdmFl'] == 'y' && $orderBasic['userHandleScmFl'] == 'y') === false) {
            unset($orderBasic['userHandleScmFl']);
        }

        //정보가 없을경우 다시 가져올수 있도록 수정
        if(empty($getData[0]['orderGoodsNm']) === true) $setInfoFl = true;

        if($setInfoFl) {
            // 사용 필드
            $arrIncludeOg = [
                'sno',
                'apiOrderGoodsNo',
                'commission',
                'goodsType',
                'orderCd',
                'userHandleSno',
                'handleSno',
                'orderStatus',
                'goodsNm',
                'goodsNmStandard',
                'goodsCnt',
                'goodsPrice',
                'optionPrice',
                'optionTextPrice',
                'addGoodsPrice',
                'divisionUseDeposit',
                'divisionUseMileage',
                'divisionCouponOrderDcPrice',
                'goodsDcPrice',
                'memberDcPrice',
                'memberOverlapDcPrice',
                'couponGoodsDcPrice',
                'goodsDeliveryCollectPrice',
                'goodsDeliveryCollectFl',
                'optionInfo',
                'optionTextInfo',
                'invoiceCompanySno',
                'invoiceNo',
                'addGoodsCnt',
                'paymentDt',
                'cancelDt',
                'timeSaleFl',
                'checkoutData',
                'og.regDt',
                'LEFT(og.orderStatus, 1) as statusMode',
                'deliveryMethodFl',
                'goodsCd',
                'taxVatGoodsPrice',
                'hscode',
                'brandCd',
                'goodsModelNo',
                'costPrice',
                'cancelDt',
                'goodsTaxInfo',
                'makerNm',
                'deliveryDt',
                'deliveryCompleteDt',
            ];

            $arrIncludeO = [
                'orderNo',
                'apiOrderNo',
                'mallSno',
                'orderGoodsNm',
                'orderGoodsNmStandard',
                'orderGoodsCnt',
                'settlePrice',
                'totalGoodsPrice',
                'settleKind',
                'receiptFl',
                'bankSender',
                'bankAccount',
                'escrowDeliveryFl',
                'orderTypeFl',
                'orderChannelFl',
                'firstSaleFl',
                //'adminMemo',
                'o.memNo AS memNoCheck',
                'LEFT(o.orderStatus, 1) as totalStatus',
                'totalDeliveryCharge',
                'useMileage',
                'useDeposit',
                'totalGoodsDcPrice',
                'totalMemberDcPrice',
                'totalMemberOverlapDcPrice',
                'totalCouponGoodsDcPrice',
                'totalCouponOrderDcPrice',
                'totalMemberDeliveryDcPrice',
                'totalCouponDeliveryDcPrice',
                'totalEnuriDcPrice',
                'currencyPolicy',
                'exchangeRatePolicy',
                'useMileage',
                'useDeposit',
                'multiShippingFl',
                'realTaxSupplyPrice',
                'realTaxVatPrice',
                'realTaxFreePrice',
            ];


            //주문상품정보
            $strField = implode(",",$arrIncludeOg);
            $strSQL = 'SELECT ' . $strField . ' FROM ' . DB_ORDER_GOODS . ' og  WHERE sno IN ("' . implode('","', array_unique(array_column($getData, 'sno'))) . '")'.gd_isset($strGroup,"");
            $tmpOrderGoodsData = $this->db->query_fetch($strSQL, null);
            $orderGoodsData = array_combine(array_column($tmpOrderGoodsData, 'sno'), $tmpOrderGoodsData);

            //주문정보
            $strSQL = 'SELECT ' . implode(",",$arrIncludeO) . ' FROM ' . DB_ORDER . ' o  WHERE o.orderNo IN ("' . implode('","', array_unique(array_column($getData, 'orderNo'))) . '")';
            $tmpOrderData = $this->db->query_fetch($strSQL, null);
            $orderData = array_combine(array_column($tmpOrderData, 'orderNo'), $tmpOrderData);

            //상품정보
            $strField = "g.goodsNo,g.imagePath,g.imageStorage,g.stockFl, gi.imageName";
            $strSQL = 'SELECT ' . $strField . ' FROM ' . DB_GOODS . ' g LEFT JOIN ' . DB_GOODS_IMAGE . ' gi ON gi.goodsNo = g.goodsNo AND gi.imageKind = \'list\' WHERE g.goodsNo IN ("' . implode('","', array_unique(array_column($getData, 'goodsNo'))) . '")';
            $tmpGoodsData = $this->db->query_fetch($strSQL, null);
            $goodsData = array_combine(array_column($tmpGoodsData, 'goodsNo'), $tmpGoodsData);

            //추가상품 정보
            $strField = "addGoodsNo,ag.imagePath AS addImagePath, ag.imageStorage AS addImageStorage, ag.imageNm AS addImageName";
            $strSQL = 'SELECT ' . $strField . ' FROM ' . DB_ADD_GOODS . ' ag  WHERE addGoodsNo IN ("' . implode('","', array_unique(array_column($getData, 'goodsNo'))) . '")';
            $tmpAddGoodsData = $this->db->query_fetch($strSQL, null);
            $addGoodsData = array_combine(array_column($tmpAddGoodsData, 'addGoodsNo'), $tmpAddGoodsData);

            //공급사 정보
            $strScmSQL = 'SELECT scmNo,companyNm FROM ' . DB_SCM_MANAGE . ' g  WHERE scmNo IN ("' . implode('","', array_unique(array_column($getData, 'scmNo'))) . '")';
            $tmpScmData = $this->db->query_fetch($strScmSQL);
            $scmData = array_combine(array_column($tmpScmData, 'scmNo'), array_column($tmpScmData, 'companyNm'));

            //몰정보
            $strMallSQL = 'SELECT domainFl,mallName,sno FROM ' . DB_MALL . ' mm  WHERE sno IN ("' . implode('","', array_unique(array_column($getData, 'mallSno'))) . '")';
            $tmpMallData = $this->db->query_fetch($strMallSQL);
            $mallData = array_combine(array_column($tmpMallData, 'sno'), $tmpMallData);

            //매입처 정보
            if (gd_is_provider() === false && gd_is_plus_shop(PLUSSHOP_CODE_PURCHASE) === true) {
                $strPurchaseSQL = 'SELECT purchaseNo,purchaseNm FROM ' . DB_PURCHASE . ' g  WHERE delFl = "n" AND purchaseNo IN ("' . implode('","', array_unique(array_column($getData, 'purchaseNo'))) . '")';
                $tmpPurchaseData = $this->db->query_fetch($strPurchaseSQL);
                $purchaseData = array_combine(array_column($tmpPurchaseData, 'purchaseNo'), array_column($tmpPurchaseData, 'purchaseNm'));
            }

            //회원정보
            $strField = "memId,nickNm,groupSno,cellPhone,memNo as memNoUnique,cellPhone,groupNm";
            $strSQL = 'SELECT ' . $strField . ' FROM ' . DB_MEMBER . ' m LEFT JOIN ' . DB_MEMBER_GROUP . ' mg ON  m.groupSno = mg.sno  WHERE m.memNo > 0 AND m.memNo IN ("' . implode('","', array_unique(array_column($getData, 'memNo'))) . '")';
            $tmpMemberData = $this->db->query_fetch($strSQL, null);
            $memberData = array_combine(array_column($tmpMemberData, 'memNoUnique'), $tmpMemberData);

            //주문정보 - 배송정보 - 수령자정보
            $strField = "sno, receiverName, receiverZonecode, receiverZipcode, receiverAddress, receiverAddressSub, orderInfoCd, orderNo, orderMemo";
            $infoWhere = '';
            if (Manager::isProvider()) {
                if($isDisplayOrderGoods){
                    //상품주문번호별
                    $infoWhere = ' AND sno IN ("' . implode('","', array_unique(array_column($getData, 'orderInfoSno'))) . '") ';
                }
                else {
                    //주문번호별
                    $strSQL = 'SELECT orderInfoSno, orderNo FROM ' . DB_ORDER_DELIVERY . ' WHERE scmNo = '.Session::get('manager.scmNo').' AND orderNo IN ("' . implode('","', array_unique(array_column($getData, 'orderNo'))) . '")';
                    $tmpAllOrderDeliveryData = $this->db->query_fetch($strSQL, null);
                    $infoWhere = ' AND sno IN ("' . implode('","', array_unique(array_column($tmpAllOrderDeliveryData, 'orderInfoSno'))) . '") ';
                }
            }
            $strSQL = 'SELECT ' . $strField . ' FROM ' . DB_ORDER_INFO . ' WHERE orderNo IN ("' . implode('","', array_unique(array_column($getData, 'orderNo'))) . '") '.$infoWhere.' ORDER BY orderInfoCd ASC';

            $tmpOrderInfoData = $this->db->query_fetch($strSQL, null);
            $orderInfoData = array_combine(array_column($tmpOrderInfoData, 'sno'), $tmpOrderInfoData);
            $orderInfoCountData = array_count_values(array_column($orderInfoData, 'orderNo'));
            $orderMemoData = $orderReceiverNameData = [];
            if(count($orderInfoData) > 0){
                //주문번호별 리스트에서 배송지, 수령자의 메인배송지 정보를 알려줌과 동시에 카운트를 알려주기 위해 처리
                $reverseOrderInfoData = $orderInfoData;
                rsort($reverseOrderInfoData);
                foreach($reverseOrderInfoData as $key => $value){
                    if($value['orderMemo']){
                        $orderMemoData[$value['orderNo']]['orderMemo'] = $value['orderMemo'];
                        $orderMemoData[$value['orderNo']]['orderMemoCount'] += 1;
                    }
                    if($value['receiverName']){
                        $orderReceiverNameData[$value['orderNo']]['receiverName'] = $value['receiverName'];
                        $orderReceiverNameData[$value['orderNo']]['receiverNameCount'] += 1;
                    }
                }
                unset($reverseOrderInfoData, $tmpOrderInfoData);
            }

            //리스트 그리드 항목에 브랜드가 있을경우 브랜드 정보 포함
            if(array_key_exists('brandNm', $this->orderGridConfigList)){
                $brandData = [];
                $brand = \App::load('\\Component\\Category\\Brand');
                $brandOriginalData = $brand->getCategoryData(null, null, 'cateNm');
                if(count($brandOriginalData) > 0){
                    $brandData = array_combine(array_column($brandOriginalData, 'cateCd'), array_column($brandOriginalData, 'cateNm'));
                }
            }
        }

        if (gd_isset($getData)) {
            $giftList = [];
            // 주문번호에 따라 배열 처리
            if($setInfoFl) {
                foreach ($getData as $key => &$val) {
                    //상품정보
                    if($orderData[$val['orderNo']]) $val = $val+$orderData[$val['orderNo']];
                    if($orderGoodsData[$val['sno']]) $val = $val+$orderGoodsData[$val['sno']];
                    if($goodsData[$val['goodsNo']]) $val = $val+$goodsData[$val['goodsNo']];
                    if($addGoodsData[$val['goodsNo']]) $val = $val+$addGoodsData[$val['goodsNo']];
                    if($deliveryData[$val['orderDeliverySno']]) $val = $val+$deliveryData[$val['orderDeliverySno']];
                    if($orderInfoData[$val['orderInfoSno']]) $val = $val+$orderInfoData[$val['orderInfoSno']];

                    if($mallData[$val['mallSno']]) $val = $val+$mallData[$val['mallSno']];
                    if($memberData[$val['memNo']]) $val = $val+$memberData[$val['memNo']];
                    $val['smsCellPhone'] = $val['memNo'] > 0 ? $val['cellPhone'] : $val['receiverCellPhonec'];
                    $val['memNo'] = is_null($val['memNo']) ?  0 : $val['memNoUnique'];

                    $val['companyNm']= $scmData[$val['scmNo']];
                    $val['purchaseNm']= $purchaseData[$val['purchaseNo']];
                    $val['brandNm'] = $brandData[$val['brandCd']];

                    if (empty($val['orderNo']) === false) {
                        // json형태의 경우 json값안에 "이있는경우 stripslashes처리가 되어 json_decode에러가 나므로 json값중 "이 들어갈수있는경우 $aCheckKey에 해당 필드명을 추가해서 처리해주세요
                        $aCheckKey = ['optionTextInfo'];
                        foreach ($val as $k => $v) {
                            if (!in_array($k, $aCheckKey)) {
                                $val[$k] = gd_htmlspecialchars_stripslashes($v);
                            }
                        }
                        if ($orderBasic['userHandleFl'] == 'y' && (!Manager::isProvider() && $orderBasic['userHandleAdmFl'] == 'y') || (Manager::isProvider() && $orderBasic['userHandleScmFl'] == 'y')) {
                            if ($this->search['userHandleViewFl'] != 'y') {
                                if ($isDisplayOrderGoods) {
                                    $val['userHandleInfo'] = $this->getUserHandleInfo($val['orderNo'], $val['sno'], [$this->search['userHandleMode']]);
                                } else {
                                    $val['userHandleInfo'] = $this->getUserHandleInfo($val['orderNo'], null, [$this->search['userHandleMode']]);
                                }

                            }
                        }
                        $data[$val['orderNo']]['goods'][] = $val;

                        // 상품º주문번호별 메모 등록여부 체크
                        $ordGoodsMemoInfo = $this->getAdminOrdGoodsMemoToPrint($val['orderNo']);
                        $data[$val['orderNo']]['adminOrdGoodsMemo'] = $ordGoodsMemoInfo ;
                    }
                }
            } else {
                foreach ($getData as $key => $val) {
                    if (empty($val['orderNo']) === false) {
                        // json형태의 경우 json값안에 "이있는경우 stripslashes처리가 되어 json_decode에러가 나므로 json값중 "이 들어갈수있는경우 $aCheckKey에 해당 필드명을 추가해서 처리해주세요
                        $aCheckKey = ['optionTextInfo'];
                        foreach ($val as $k => $v) {
                            if (!in_array($k, $aCheckKey)) {
                                $val[$k] = gd_htmlspecialchars_stripslashes($v);
                            }
                        }
                        if ($orderBasic['userHandleFl'] == 'y' && (!Manager::isProvider() && $orderBasic['userHandleAdmFl'] == 'y') || (Manager::isProvider() && $orderBasic['userHandleScmFl'] == 'y')) {
                            if ($isDisplayOrderGoods) {
                                $val['userHandleInfo'] = $this->getUserHandleInfo($val['orderNo'], $val['sno'], [$this->search['userHandleMode']]);
                            } else {
                                $val['userHandleInfo'] = $this->getUserHandleInfo($val['orderNo'], null, [$this->search['userHandleMode']]);
                            }
                        }
                        $data[$val['orderNo']]['goods'][] = $val;
                    }
                }
            }


            //복수배송지 사용 여부에 따라 페이지 노출시 scmNo 의 키를 order info sno 로 교체한다.
            $useMultiShippingKey = true;

            // 결제방법과 처리 상태 설정
            foreach ($data as $key => &$val) {
                $orderGoods = $val['goods'];
                unset($val['goods']);
                foreach ($orderGoods as $oKey => &$oVal) {
                    if($oVal['deliveryMethodFl']){
                        $oVal['deliveryMethodFlText'] = $delivery->deliveryMethodList['name'][$oVal['deliveryMethodFl']];
                        $oVal['deliveryMethodFlSno'] = $delivery->deliveryMethodList['sno'][$oVal['deliveryMethodFl']];
                    }
                    // 상품명 태그 제거
                    $oVal['orderGoodsNm'] = StringUtils::stripOnlyTags(html_entity_decode($oVal['orderGoodsNm']));
                    $oVal['goodsNm'] = StringUtils::stripOnlyTags(html_entity_decode($oVal['goodsNm']));

                    // 리스트에서 무조건 해외상점 몰 이름이 한글로 나오도록 강제 변환
                    if ($oVal['mallSno'] > DEFAULT_MALL_NUMBER) {
                        //리스트에 해외몰 주문건에대한 주문상품명르 노출시키기 위해 해외몰 주문상품명유지
                        $oVal['orderGoodsNmGlobal'] = $oVal['orderGoodsNm'];
                        $oVal['goodsNmGlobal'] = $oVal['goodsNm'];

                        if (empty($oVal['orderGoodsNmStandard']) === false) {
                            $oVal['orderGoodsNm'] = StringUtils::stripOnlyTags(html_entity_decode($oVal['orderGoodsNmStandard']));
                        }
                        if (empty($oVal['goodsNmStandard']) === false) {
                            $oVal['goodsNm'] = StringUtils::stripOnlyTags(html_entity_decode($oVal['goodsNmStandard']));
                        }
                    }

                    if(!$isDisplayOrderGoods && $searchStatusMode === 'o') {
                        // 입금대기리스트 > 주문번호별 에서 '주문상품명' 을 입금대기 상태의 주문상품명만 구성
                        $noPay = (int)$oVal['noPay'] - 1;
                        if($noPay > 0){
                            $oVal['orderGoodsNmStandard'] = $oVal['orderGoodsNm'] = $oVal['goodsNm'] . ' 외 ' . $noPay . ' 건';
                        }
                        else {
                            $oVal['orderGoodsNmStandard'] = $oVal['orderGoodsNm'] = $oVal['goodsNm'];
                        }

                        if ($oVal['mallSno'] > DEFAULT_MALL_NUMBER) {
                            if($noPay > 0){
                                $oVal['orderGoodsNmGlobal'] = $oVal['goodsNmGlobal'] . ' ' . __('외') . ' ' . $noPay . ' ' . __('건');
                            }
                            else {
                                $oVal['orderGoodsNmGlobal'] = $oVal['goodsNmGlobal'];
                            }
                        }
                    }

                    //상품진열시에만 실행

                    if($isDisplayOrderGoods) {

                        // 옵션처리
                        // 현재 foreach문의 $data를 할당하면서 이미 gd_htmlspecialchars_stripslashes처리를 하기때문에 여기서는 처리할필요가없음
                        $options = json_decode($oVal['optionInfo'], true);

                        $oVal['optionInfo'] = $options;
                        if ($oVal['orderChannelFl'] == 'naverpay') {
                            $naverPay = new NaverPay();
                            $oVal['checkoutData'] = json_decode($oVal['checkoutData'], true);
                            if ($oVal['checkoutData']['returnData']['ReturnReason']) {
                                $oVal['handleReason'] = $naverPay->getClaimReasonCode($oVal['checkoutData']['returnData']['ReturnReason'], 'back');
                            } else if ($oVal['checkoutData']['exchangeData']['ExchangeReason']) {
                                $oVal['handleReason'] = $naverPay->getClaimReasonCode($oVal['checkoutData']['exchangeData']['ExchangeReason'], 'back');
                            } else if ($oVal['checkoutData']['cancelData']['CancelReason']) {
                                $oVal['handleReason'] = $naverPay->getClaimReasonCode($oVal['checkoutData']['cancelData']['CancelReason'], 'back');
                            }
                        }

                        // 텍스트옵션
                        $textOptions = json_decode($oVal['optionTextInfo'], true);
                        $oVal['optionTextInfo'] = $textOptions;

                        // 배송 택배사 설정
                        $oVal['invoiceCompanyNm'] = $this->getInvoiceCompanyName($oVal['invoiceCompanySno']);
                    }

                    //복수배송지를 사용 중이며 리스트에서 노출시킬 목적으로만 사용중이면 사은품은 단 한번만 저장시킨다.
                    if($useMultiShippingKey === true){
                        if(!$giftList[$key][$oVal['scmNo']]){
                            $oVal['gift'] = $this->getOrderGift($key, $oVal['scmNo'], 40);
                            $giftList[$key][$oVal['scmNo']] = $oVal['gift'];
                        }
                    }
                    else {
                        // 사은품
                        if($giftList[$key]) {
                            $oVal['gift'] = $giftList[$key];
                        }  else {
                            $oVal['gift'] = $this->getOrderGift($key, $oVal['scmNo'], 40);
                            $giftList[$key] = $oVal['gift'];
                        }
                    }

                    // 추가상품
                    $oVal['addGoods'] = $this->getOrderAddGoods(
                        $key,
                        $oVal['orderCd'],
                        [
                            'sno',
                            'addGoodsNo',
                            'goodsNm',
                            'goodsCnt',
                            'goodsPrice',
                            'optionNm',
                            'goodsImage',
                            'addMemberDcPrice',
                            'addMemberOverlapDcPrice',
                            'addCouponGoodsDcPrice',
                            'addGoodsMileage',
                            'addMemberMileage',
                            'addCouponGoodsMileage',
                            'divisionAddUseDeposit',
                            'divisionAddUseMileage',
                            'divisionAddCouponOrderDcPrice',
                        ]
                    );

                    // 추가상품 수량 (테이블 UI 처리에 필요)
                    $oVal['addGoodsCnt'] = empty($oVal['addGoods']) ? 0 : count($oVal['addGoods']);

                    // 주문 상태명 설정
                    $oValOrderStatus = $oVal['orderStatus'];
                    if (gd_isset($oValOrderStatus)) {
                        $oVal['beforeStatusStr'] = $this->getOrderStatusAdmin($oVal['beforeStatus']);
                        $oVal['totalStatusStr'] = $this->getOrderStatusAdmin($oVal['totalStatus']);
                        $oVal['settleKindStr'] = $this->printSettleKind($oVal['settleKind']);
                        $oVal['escrowFl'] = substr($oVal['settleKind'], 0, 1);

                        // 반품/교환/환불신청인 경우 해당 상태를 출력
                        if ($isUserHandle) {
                            $oVal['orderStatusStr'] = $this->getUserHandleMode($oVal['userHandleMode'], $oVal['userHandleFl']);
                        } else {
                            $oVal['orderStatusStr'] = $this->getOrderStatusAdmin($oVal['orderStatus']);
                        }
                    }

                    //총 할인금액
                    $totalDcPriceArray = [
                        $orderData[$oVal['orderNo']]['totalGoodsDcPrice'],
                        $orderData[$oVal['orderNo']]['totalMemberDcPrice'],
                        $orderData[$oVal['orderNo']]['totalMemberOverlapDcPrice'],
                        $orderData[$oVal['orderNo']]['totalCouponGoodsDcPrice'],
                        $orderData[$oVal['orderNo']]['totalCouponOrderDcPrice'],
                        $orderData[$oVal['orderNo']]['totalMemberDeliveryDcPrice'],
                        $orderData[$oVal['orderNo']]['totalCouponDeliveryDcPrice'],
                        $orderData[$oVal['orderNo']]['totalEnuriDcPrice'],
                    ];
                    $oVal['totalDcPrice'] = array_sum($totalDcPriceArray);

                    //총 부가결제 금액
                    $oVal['totalUseAddedPrice'] = $orderData[$oVal['orderNo']]['useMileage']+$orderData[$oVal['orderNo']]['useDeposit'];

                    //총 주문 금액 : 총 상품금액 + 총 배송비 - 총 할인금액
                    $oVal['totalOrderPrice'] = $orderData[$oVal['orderNo']]['totalGoodsPrice'] + $orderData[$oVal['orderNo']]['totalDeliveryCharge'] - $oVal['totalDcPrice'];

                    //총 실 결제금액
                    if($oVal['orderChannelFl'] === 'naverpay'){
                        // 네이버페이 포인트를 사용한 경우 realtax 에 값이 담기지 않아 실금액을 구할 수 없으므로 settlePrice 를 사용한다.
                        $oVal['totalRealSettlePrice'] = $orderData[$oVal['orderNo']]['settlePrice'];
                    }
                    else {
                        $oVal['totalRealSettlePrice'] = $orderData[$oVal['orderNo']]['realTaxSupplyPrice'] + $orderData[$oVal['orderNo']]['realTaxVatPrice'] + $orderData[$oVal['orderNo']]['realTaxFreePrice'];
                    }

                    // 멀티상점 환율 기본 정보
                    $oVal['currencyPolicy'] = json_decode($oVal['currencyPolicy'], true);
                    $oVal['exchangeRatePolicy'] = json_decode($oVal['exchangeRatePolicy'], true);
                    $oVal['currencyIsoCode'] = $oVal['currencyPolicy']['isoCode'];
                    $oVal['exchangeRate'] = $oVal['exchangeRatePolicy']['exchangeRate' . $oVal['currencyPolicy']['isoCode']];

                    //총 배송지 수
                    $oVal['totalOrderInfoCount'] = $orderInfoCountData[$oVal['orderNo']];

                    //총 배송 메모 수 및 첫번째 메모
                    $oVal['multiShippingOrderMemo'] = $orderMemoData[$oVal['orderNo']]['orderMemo'];
                    $oVal['multiShippingOrderMemoCount'] = $orderMemoData[$oVal['orderNo']]['orderMemoCount'];

                    //총 수령자 수 및 첫번째 수령자
                    $oVal['multiShippingReceiverName'] = $orderReceiverNameData[$oVal['orderNo']]['receiverName'];
                    $oVal['multiShippingReceiverNameCount'] = $orderReceiverNameData[$oVal['orderNo']]['receiverNameCount'];

                    // 데이터 SCM/Delivery 3차 배열로 재구성
                    if($useMultiShippingKey === true){
                        //복수배송지를 사용 중이며 리스트에서 노출시킬 목적으로만 사용중이면 주문데이터 배열의 scmNo 를 orderInfoSno로 대체한다.
                        $_mpckInfoSno = ($oVal['mpckKey'] && $oVal['reqDvCd'] != '02') ? $oVal['mpckKey'] : $oVal['orderInfoSno'];
                        $data[$key]['goods'][$_mpckInfoSno][$oVal['orderDeliverySno']][$oKey] = $oVal;
                    }
                    else {
                        $data[$key]['goods'][$oVal['scmNo']][$oVal['deliverySno']][$oKey] = $oVal;
                    }
                    ksort($data[$key]['goods'], SORT_REGULAR  );
                    // 테이블 UI 표현을 위한 변수
                    if (!isset($data[$key]['cnt'])) {
                        $data[$key]['cnt'] = [];
                    }
                    //복수배송지를 사용 중이며 리스트에서 노출시킬 목적으로만 사용중이면 주문데이터 배열의 scmNo 를 orderInfoSno로 대체한다.
                    if($useMultiShippingKey === true){
                        $data[$key]['cnt']['multiShipping'][$_mpckInfoSno] += 1 + $oVal['addGoodsCnt'];
                        $data[$key]['cnt']['delivery'][$oVal['orderDeliverySno']] += 1 + $oVal['addGoodsCnt'];
                    }
                    else {
                        $data[$key]['cnt']['orderInfoSnoCnt'][$oVal['orderInfoSno']] += 1 + $oVal['addGoodsCnt'];
                        $data[$key]['cnt']['delivery'][$oVal['deliverySno']] += 1 + $oVal['addGoodsCnt'];
                        $data[$key]['cnt']['scm'][$oVal['scmNo']] += 1 + $oVal['addGoodsCnt'];
                    }
                    $data[$key]['cnt']['goods']['all'] += 1 + $oVal['addGoodsCnt'];
                    $data[$key]['cnt']['goods']['goods'] += 1;
                    $data[$key]['cnt']['goods']['addGoods'] += $oVal['addGoodsCnt'];


                }

                // 별도의 데이터 추가 실제 총 결제금액 = 주문결제금액 + 배송비
                foreach ($orderGoods as $tKey => $tVal) {
                    $firstKey = $tVal['scmNo'];
                    $secontKey = $tVal['deliverySno'];

                    //복수배송지를 사용 중이며 리스트에서 노출시킬 목적으로만 사용중이면 주문데이터 배열의 scmNo 를 orderInfoSno로 대체한다.
                    if($useMultiShippingKey === true){
                        $firstKey = ($tVal['mpckKey'] && $tVal['reqDvCd'] != '02') ? $tVal['mpckKey'] : $tVal['orderInfoSno'];
                        $secontKey = $tVal['orderDeliverySno'];
                    }
                    $data[$key]['goods'][$firstKey][$secontKey][$tKey]['totalSettlePrice'] = $oVal['settlePrice'];
                }

                if (Manager::isProvider()) {
                    $data[$key] = $this->getProviderTotalPriceList($data[$key], $key);
                }
            }

            // 각 데이터 배열화
            $getData['data'] = gd_isset($data);

            unset($giftList);
        }
        // 사용자 교환/반품/환불 신청 여부
        $getData['isUserHandle'] = $isUserHandle;

        // 검색값 설정
        if (empty($this->search) === false) {
            $getData['search'] = gd_htmlspecialchars($this->search);
        }

        // 체크값 설정
        if (empty($this->checked) === false) {
            $getData['checked'] = $this->checked;
        }

        // 리스트 그리드 항목 설정
        if (empty($this->orderGridConfigList) === false) {
            $getData['orderGridConfigList'] = $this->orderGridConfigList;
        }

        //복수배송지를 사용 중이며 리스트에서 노출시킬 목적으로만 사용중이면 주문데이터 배열의 scmNo 를 orderInfoSno로 대체한다. : true 일시
        $getData['useMultiShippingKey'] = $useMultiShippingKey;

        // 페이지 전체값
        $page = \App::load('\\Component\\Page\\Page');
        $getData['amount'] = $page->recode['total'];
        $checkOrderInfo = null;
        $checkOrderMpckKey = null;
        $tmpReservCheckData = $getData['data'] ;
        foreach ($tmpReservCheckData as $_tmpVal) {
            foreach($_tmpVal['goods'] as $_orderInfo=>$_val) {
                $checkOrderInfo[$_orderInfo]['cancelFl'] = false;
                $checkOrderInfo[$_orderInfo]['reservelFl'] = true;
                $checkOrderInfo[$_orderInfo]['mix'] = false;
                $_cancelReservation = null;
                foreach($_val as $_ktmpVal) {
                    foreach($_ktmpVal as $_tmpVal){
                        if($_tmpVal['mpckKey']){
                            $checkOrderMpckKey[$_tmpVal['mpckKey']][$_tmpVal['orderNo']] = true;
                        }

                        if($_tmpVal['reqDvCd'] == '01') {
                            $checkOrderInfo[$_orderInfo]['reservelFl'] = false;
                        }

                        if($_tmpVal['reqDvCd'] == '01' && empty($_tmpVal['invoiceNo'])) {   //취소가능
                            $checkOrderInfo[$_orderInfo]['cancelFl'] = true;
                        }
                        else {  //취소불가능
                        }
                    }
                }

                if($_cancelReservation['y'] && $_cancelReservation['n']) {
                    $checkOrderInfo[$_orderInfo]['mix'] = true;
                }
            }
        }
        $getData['reservationInfo'] = $checkOrderInfo;
        $getData['reservationMpckKeyInfo'] = $checkOrderMpckKey;

        return $getData;
    }

    public function syncOrderInfo($data)
    {

    }

    /**
     * 예약취소 가능한 주문상품데이터만 가져오기
     *
     * @param      $orderInfoSno
     *
     * @param bool $isCancel
     *
     * @return mixed
     */
    protected function getReservationOrderGoodsDataByOrderInfoSno($orderInfoSno, $isCancel = false)
    {
        $mpckKeyList = null;
        //취소인경우 취소할 합포장키 구하기
        if($isCancel) {
            $arrBind = null;
            $query = "SELECT lo.mpckKey FROM ".DB_LOGISTICS_ORDER." as lo INNER JOIN ".DB_ORDER_GOODS.' as og ON lo.orderGoodsNo = og.sno INNER JOIN ' . DB_ORDER_DELIVERY . ' od ON og.orderDeliverySno = od.sno  LEFT JOIN ' . DB_ORDER_INFO . ' oi ON (og.orderNo = oi.orderNo)   
                    AND (CASE WHEN od.orderInfoSno > 0 THEN od.orderInfoSno = oi.sno ELSE oi.orderInfoCd = 1 END) WHERE oi.sno = ?';
            $this->db->bind_param_push($arrBind, 'i',$orderInfoSno);
            $result = $this->db->query_fetch($query,$arrBind);
            foreach($result as $_val) {
                $mpckKeyList[] = $_val['mpckKey'];
            }
            $mpckKeyList = array_unique($mpckKeyList);
        }
        $arrIncludeOi = [
            'orderName',
            'receiverName',
            'orderMemo',
            'orderCellPhone',
            'packetCode',
        ];
        $arrIncludeLo = [
            'custUseNo',
            'custId', // 고객사용번호
            'reservationStatus', // 예약상태
            'mpckKey', // 합포장키
            'mpckSeq', // 합포장순번
            'requestData', // 중계서버 통신 전문
            'reqDvCd',
            'if(reqDvCd = "01","예약완료","예약전") as reservationStatus',
        ];
        $arrIncludeOg = [
            'sno',
            'orderNo',
            'apiOrderGoodsNo',
            'commission',
            'goodsType',
            'orderCd',
            'userHandleSno',
            'handleSno',
            'orderStatus',
            'goodsNm',
            'goodsNmStandard',
            'goodsCnt',
            'goodsPrice',
            'optionPrice',
            'optionTextPrice',
            'addGoodsPrice',
            'divisionUseDeposit',
            'divisionUseMileage',
            'divisionCouponOrderDcPrice',
            'goodsDcPrice',
            'memberDcPrice',
            'memberOverlapDcPrice',
            'couponGoodsDcPrice',
            'goodsDeliveryCollectPrice',
            'goodsDeliveryCollectFl',
            'optionInfo',
            'optionTextInfo',
            'invoiceCompanySno',
            'invoiceNo',
            'addGoodsCnt',
            'paymentDt',
            'cancelDt',
            'timeSaleFl',
            'checkoutData',
            'og.regDt',
            'LEFT(og.orderStatus, 1) as statusMode',
            'deliveryMethodFl',
            'goodsCd',
            'taxVatGoodsPrice',
            'hscode',
            'brandCd',
            'goodsModelNo',
            'costPrice',
            'cancelDt',
            'goodsTaxInfo',
            'makerNm',
            'deliveryDt',
            'deliveryCompleteDt',
        ];
        $tmpField[] = DBTableField::setTableField('tableOrderInfo', null, null, 'oi');
        $tmpField[] = DBTableField::setTableField('tableLogisticsOrder', $arrIncludeLo, null, 'lo');;
        $tmpField[] = DBTableField::setTableField('tableOrderGoods', $arrIncludeOg, null, 'og');;
        $tmpField[] = ['oi.sno AS orderInfoSno',];

        // join 문
        $join[] = ' LEFT JOIN ' . DB_ORDER . ' o ON o.orderNo = og.orderNo ';
        $join[] = ' LEFT JOIN ' . DB_ORDER_DELIVERY . ' od ON og.orderDeliverySno = od.sno ';
        $join[] = ' LEFT JOIN ' . DB_GOODS . ' g ON og.goodsNo = g.goodsNo ';
        $join[] = ' LEFT JOIN ' . DB_ORDER_INFO . ' oi ON (og.orderNo = oi.orderNo)   
                    AND (CASE WHEN od.orderInfoSno > 0 THEN od.orderInfoSno = oi.sno ELSE oi.orderInfoCd = 1 END)';
        $join[] = ' LEFT OUTER JOIN ' . DB_LOGISTICS_ORDER. ' AS lo ON lo.orderGoodsNo = og.sno AND lo.current = \'y\' ';

        // 쿼리용 필드 합침
        $tmpKey = array_keys($tmpField);
        $arrField = [];
        foreach ($tmpKey as $key) {
            $arrField = array_merge($arrField, $tmpField[$key]);
        }
        unset($tmpField, $tmpKey);

        // 현 페이지 결과
        $this->db->strField = 'o.orderChannelFl,og.sno,o.memNo,g.goodsNm ,' . implode(', ', $arrField) . ',og.orderDeliverySno';
        // addGoods 필드 변경 처리 (goods와 동일해서)


        $arrBind = null;
        if($isCancel) {
            $arrWhere[] = "reqDvCd = '01' ";
            $arrWhere[] = "lo.mpckKey IN ('".implode("','",$mpckKeyList)."')";
            $arrWhere[] = "(invoiceNo is null OR invoiceNo = '')";
        }
        else {
            $arrWhere[] = "oi.sno =  ? ";
            $this->db->bind_param_push($arrBind, 'i', $orderInfoSno);

        }
        $this->db->strJoin = implode('', $join);
        $this->db->strWhere = implode(' AND ', gd_isset($arrWhere));


        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_GOODS . ' og ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);

        return $getData;
    }


    protected function getReservationOrderGoodsDataByMpckKey($mpckKey, $isCancel = false)
    {
        $arrIncludeOi = [
            'orderName',
            'receiverName',
            'orderMemo',
            'orderCellPhone',
            'packetCode',
        ];
        $arrIncludeLo = [
            'custUseNo',
            'custId', // 고객사용번호
            'reservationStatus', // 예약상태
            'mpckKey', // 합포장키
            'mpckSeq', // 합포장순번
            'requestData', // 중계서버 통신 전문
            'reqDvCd',
            'if(reqDvCd = "01","예약완료","예약전") as reservationStatus',
        ];
        $arrIncludeOg = [
            'sno',
            'orderNo',
            'apiOrderGoodsNo',
            'commission',
            'goodsType',
            'orderCd',
            'userHandleSno',
            'handleSno',
            'orderStatus',
            'goodsNm',
            'goodsNmStandard',
            'goodsCnt',
            'goodsPrice',
            'optionPrice',
            'optionTextPrice',
            'addGoodsPrice',
            'divisionUseDeposit',
            'divisionUseMileage',
            'divisionCouponOrderDcPrice',
            'goodsDcPrice',
            'memberDcPrice',
            'memberOverlapDcPrice',
            'couponGoodsDcPrice',
            'goodsDeliveryCollectPrice',
            'goodsDeliveryCollectFl',
            'optionInfo',
            'optionTextInfo',
            'invoiceCompanySno',
            'invoiceNo',
            'addGoodsCnt',
            'paymentDt',
            'cancelDt',
            'timeSaleFl',
            'checkoutData',
            'og.regDt',
            'LEFT(og.orderStatus, 1) as statusMode',
            'deliveryMethodFl',
            'goodsCd',
            'taxVatGoodsPrice',
            'hscode',
            'brandCd',
            'goodsModelNo',
            'costPrice',
            'cancelDt',
            'goodsTaxInfo',
            'makerNm',
            'deliveryDt',
            'deliveryCompleteDt',
        ];
        $tmpField[] = DBTableField::setTableField('tableOrderInfo', null, null, 'oi');
        $tmpField[] = DBTableField::setTableField('tableLogisticsOrder', $arrIncludeLo, null, 'lo');;
        $tmpField[] = DBTableField::setTableField('tableOrderGoods', $arrIncludeOg, null, 'og');;
        $tmpField[] = ['oi.sno AS orderInfoSno',];

        // join 문
        $join[] = ' LEFT JOIN ' . DB_ORDER . ' o ON o.orderNo = og.orderNo ';
        $join[] = ' LEFT JOIN ' . DB_ORDER_DELIVERY . ' od ON og.orderDeliverySno = od.sno ';
        $join[] = ' LEFT JOIN ' . DB_GOODS . ' g ON og.goodsNo = g.goodsNo ';
        $join[] = ' LEFT JOIN ' . DB_ORDER_INFO . ' oi ON (og.orderNo = oi.orderNo)   
                    AND (CASE WHEN od.orderInfoSno > 0 THEN od.orderInfoSno = oi.sno ELSE oi.orderInfoCd = 1 END)';
        $join[] = ' LEFT OUTER JOIN ' . DB_LOGISTICS_ORDER. ' AS lo ON lo.orderGoodsNo = og.sno AND lo.current = \'y\' ';

        // 쿼리용 필드 합침
        $tmpKey = array_keys($tmpField);
        $arrField = [];
        foreach ($tmpKey as $key) {
            $arrField = array_merge($arrField, $tmpField[$key]);
        }
        unset($tmpField, $tmpKey);

        // 현 페이지 결과
        $this->db->strField = 'o.orderChannelFl,og.sno,o.memNo,g.goodsNm ,' . implode(', ', $arrField) . ',og.orderDeliverySno';
        // addGoods 필드 변경 처리 (goods와 동일해서)

        $arrBind = null;
        if($isCancel) {
            $arrWhere[] = "reqDvCd = '01' ";
            $arrWhere[] = "lo.mpckKey = ? ";
            $arrWhere[] = "(invoiceNo is null OR invoiceNo = '')";
            $this->db->bind_param_push($arrBind, 's', $mpckKey);
        }
        else {
            $arrWhere[] = "lo.mpckKey =  ? ";
            $this->db->bind_param_push($arrBind, 's', $mpckKey);

        }
        $this->db->strJoin = implode('', $join);
        $this->db->strWhere = implode(' AND ', gd_isset($arrWhere));


        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_GOODS . ' og ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);

        return $getData;
    }

    /**
     * saveDeveloperInfo 개발소스관리 기술지원 쇼핑몰 개발 담당
     *
     * @param array $getValue
     *
     * @return boolean
     * @throws Exception
     */
    public function saveLogisticsPolicy($getValue)
    {
        $fullAddress = $getValue['SENDR_ADDR'].$getValue['SENDR_DETAIL_ADDR'];
        if(StringUtils::strLength($fullAddress)>100) {
            throw new \Exception('입력된 주소가 너무 길어 대한통운 보내는 사람 주소로 사용할 수 없습니다. 상세주소를 포함하여 100byte 이하로 입력해주세요.');
        }
        $policy = new Policy();
        $result = $policy->setValue('logistics.config', $getValue);

        return $result;
    }
}


