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
 * @link      http://www.godo.co.kr
 */

namespace Bundle\Component\Order;

use Component\Database\DBTableField;
use Component\Member\Manager;
use Framework\Debug\Exception\AlertBackException;
use Framework\Utility\ArrayUtils;
use Framework\Utility\DateTimeUtils;
use Globals;
use Request;

/**
 * Class OrderStatics
 *
 * @package Order
 * @author  Jong-tae Ahn <qnibus@godo.co.kr>
 */
class OrderStatics extends \Component\Order\OrderAdmin
{
    /**
     * @var array
     */
    public $ages = [
        10,
        20,
        30,
        40,
        50,
        60,
        70,
        80,
        // 알 수 없는 경우
    ];

    /**
     * @var array
     */
    public $areas = [];

    public function __construct()
    {
        $this->areas = [
            __('강원'),
            __('경기'),
            __('경상남도'),
            __('경상북도'),
            __('광주'),
            __('대구'),
            __('대전'),
            __('부산'),
            __('서울'),
            __('세종'),
            __('울산'),
            __('인천'),
            __('전라남도'),
            __('전라북도'),
            __('제주'),
            __('충청남도'),
            __('충청북도'),
        ];
        parent::__construct();
    }

    /**
     * 매출분석 > 매출통계
     *
     * @param array   $searchData 검색데이터
     * @param string  $groupType  탭구분
     * @param boolean $useScmFl   SCM 사용여부
     *
     * @return array 매출 통계 정보
     * @throws AlertBackException
     */
    public function getStatisticsSales($searchData, $groupType = 'day', $useScmFl = false)
    {
        // 요청 들어온 검색 데이터 초기화
        $this->search['periodFl'] = gd_isset($searchData['periodFl'], 6);
        $this->search['treatDate'][0] = gd_isset($searchData['treatDate'][0], $searchData['periodFl'] == -1 ? $searchData['treatDate'][0] : date('Y-m-d', strtotime('-' . $searchData['periodFl'] . ' day')));
        $this->search['treatDate'][1] = gd_isset($searchData['treatDate'][1], $searchData['periodFl'] == -1 ? $searchData['treatDate'][1] : date('Y-m-d'));

        // 공급사
        if ($useScmFl) {
            $this->search['scmFl'] = gd_isset($searchData['scmFl'], '0');
            $this->search['scmNo'] = gd_isset($searchData['scmNo'], DEFAULT_CODE_SCMNO);
            $this->search['scmNoNm'] = gd_isset($searchData['scmNoNm']);
        }

        // 최대 3개월 넘지 않도록 처리
        $intervalDay = DateTimeUtils::intervalDay($searchData['treatDate'][0], $searchData['treatDate'][1]);
        if ($groupType == 'month') {
            $maxSearchDate = 360;
            $maxMessage = __('최대 12개월까지 조회할 수 있습니다.');
        } else {
            $maxSearchDate = 90;
            $maxMessage = __('최대 3개월까지 조회할 수 있습니다.');
        }
        if ($intervalDay > $maxSearchDate) {
            throw new AlertBackException($maxMessage);
        }
        $this->search['interval'] = $intervalDay;
        $getData['search'] = gd_htmlspecialchars($this->search);

        // UI 체크박스 설정
        $this->checked['scmFl'][$this->search['scmFl']] = 'checked="checked"';
        $this->checked['periodFl'][$searchData['periodFl']] = 'active';
        $getData['checked'] = gd_htmlspecialchars($this->checked);

        // 주문유형별 데이터 수집
        $arrOrderTypeFl = [
            'pc',
            'mobile',
            'write',
        ];
        foreach ($arrOrderTypeFl as $orderTypeFl) {
            // 공급사로 로그인하는 경우 매출통계가 처리 방식이 달라짐
            if (Manager::isProvider()) {
                $order = $this->getStaticsProviderSalesData($orderTypeFl, $groupType, $useScmFl, false);
                $refund = $this->getStaticsProviderSalesData($orderTypeFl, $groupType, $useScmFl, true);
            } else {
                $order = $this->getStaticsSalesData($orderTypeFl, $groupType, $useScmFl, false);
                $refund = $this->getStaticsSalesData($orderTypeFl, $groupType, $useScmFl, true);
            }
            $getData['payment']['order'][$orderTypeFl] = $order;
            $getData['payment']['refund'][$orderTypeFl] = $refund;
        }

        return $getData;
    }

    /**
     * 매출분석 > 매출통계
     *
     * @param string  $typeFl    pc|mobile|write
     * @param string  $groupType day|month|hour|member|tax|week
     * @param boolean $useScmFl
     * @param boolean $isRefund
     *
     * @return array 쿼리 데이터
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public function getStaticsSalesData($typeFl, $groupType, $useScmFl = false, $isRefund = false)
    {
        // 공통 처리일자 검색
        $arrBind = [];
        if (isset($this->search['periodFl']) && $this->search['treatDate'][0] && $this->search['treatDate'][1]) {
            $arrWhere[] = 'og.paymentDt BETWEEN ? AND ?';

            // 타입이 월별인 경우 1일과 마지막 일자로 변경해야 한다.
            if ($groupType == 'month') {
                $this->search['treatDate'][0] = date('Y-m-01', strtotime($this->search['treatDate'][0]));
                $this->search['treatDate'][1] = date('Y-m-t', strtotime($this->search['treatDate'][1]));
            }
            $this->db->bind_param_push($arrBind, 's', $this->search['treatDate'][0] . ' 00:00:00');
            $this->db->bind_param_push($arrBind, 's', $this->search['treatDate'][1] . ' 23:59:59');
        }

        // 공통 허용 주문상태 설정
        $arrWhere[] = 'LEFT(og.orderStatus, 1) IN (\'' . implode('\', \'', $this->statusStatisticsCd02) . '\')';

        // 공급사 선택
        if (Manager::isProvider()) {
            $arrWhere[] = 'og.scmNo = ?';
            $this->db->bind_param_push($arrBind, 'i', \Session::get('manager.scmNo'));
        } else {
            if ($useScmFl) {
                if ($this->search['scmFl'] == '1') {
                    if (is_array($this->search['scmNo'])) {
                        foreach ($this->search['scmNo'] as $val) {
                            $tmpWhere[] = 'og.scmNo = ?';
                            $this->db->bind_param_push($arrBind, 's', $val);
                        }
                        $arrWhere[] = '(' . implode(' OR ', $tmpWhere) . ')';
                        unset($tmpWhere);
                    } else if ($this->search['scmNo'] > 1) {
                        $arrWhere[] = 'og.scmNo = ?';
                        $this->db->bind_param_push($arrBind, 'i', $this->search['scmNo']);
                    }
                } elseif ($this->search['scmFl'] == '0') {
                    $arrWhere[] = 'og.scmNo = ' . DEFAULT_CODE_SCMNO;
                }
            }
        }

        // 쿼리 그룹설정
        $groupTypeFl = false;
        $tmpOtherGroup = '';
        switch ($groupType) {
            case 'day':
                $tmpGroup = 'DATE_FORMAT(og.paymentDt, \'%Y-%m-%d\')';
                break;

            case 'month':
                $tmpGroup = 'DATE_FORMAT(og.paymentDt, \'%Y-%m\')';
                break;

            case 'hour':
                $tmpGroup = 'DATE_FORMAT(og.paymentDt, \'%H\')';
                break;

            case 'week':
                $tmpGroup = 'DATE_FORMAT(og.paymentDt, \'%w\')';
                break;

            case 'commission':
                $tmpGroup = 'DATE_FORMAT(og.paymentDt, \'%Y-%m-%d\')';
                break;

            case 'member':
                $groupTypeFl = true;
                $tmpGroup = 'DATE_FORMAT(og.paymentDt, \'%Y-%m-%d\')';
                $tmpOtherGroup = ', category';
                break;

            case 'tax':
                $groupTypeFl = true;
                $tmpGroup = 'DATE_FORMAT(og.paymentDt, \'%Y-%m-%d\')';
                $tmpOtherGroup = ', category';
                break;
        }

        // 유형 선택
        $arrWhere[] = 'o.orderTypeFl = ?';
        $this->db->bind_param_push($arrBind, 's', $typeFl);

        // 환불완료 건만 (네이버페이의 경우 handleCompleteFl이 'n'으로 되어 있어 임시로 쿼리 조정
        if ($isRefund) {
            $arrWhere[] = '((oh.handleCompleteFl = \'y\' AND oh.handleMode = \'r\' AND og.orderStatus = \'r3\') OR (o.orderChannelFl=\'naverpay\' AND og.orderStatus = \'r3\'))';
        }

        // 주문상품 추출
        $tmpFieldItem = [
            $tmpGroup . ' AS paymentDt',
            'SUM((og.goodsPrice + og.optionPrice + og.optionTextPrice) * og.goodsCnt) AS price',
            'SUM(og.goodsDcPrice + og.memberDcPrice + og.memberOverlapDcPrice + og.couponGoodsDcPrice + og.divisionCouponOrderDcPrice) AS dcPrice',
        ];

        // 회원/비회원 분류
        if ($groupType == 'member') {
            $tmpFieldItem[] = 'IF(o.memNo=0, 0, 1) AS category';
        } elseif ($groupType == 'tax') {
            $tmpFieldItem[] = 'IF(LEFT(og.goodsTaxInfo, 1)=\'f\', 0, 1) AS category';
        }

        $this->db->strJoin = 'LEFT JOIN ' . DB_ORDER_HANDLE . ' oh ON og.handleSno = oh.sno LEFT JOIN ' . DB_ORDER . ' o ON og.orderNo = o.orderNo';
        $this->db->strField = implode(', ', $tmpFieldItem);
        $this->db->strOrder = 'paymentDt ASC';
        $this->db->strGroup = $tmpGroup . $tmpOtherGroup;
        $this->db->strWhere = implode(' AND ', $arrWhere);

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_GOODS . ' og ' . implode(' ', $query);
        $goodsData = $this->db->query_fetch($strSQL, $arrBind);

        // 추가주문상품 추출
        $tmpFieldItem = [
            $tmpGroup . ' AS paymentDt',
            'SUM(oa.goodsPrice * oa.goodsCnt) AS price',
            'SUM(oa.addMemberDcPrice + oa.addMemberOverlapDcPrice + oa.addCouponGoodsDcPrice + oa.divisionAddCouponOrderDcPrice) AS dcPrice',
        ];

        // 회원/비회원 분류
        if ($groupType == 'member') {
            $tmpFieldItem[] = 'IF(o.memNo=0, 0, 1) AS category';
        } elseif ($groupType == 'tax') {
            $tmpFieldItem[] = 'IF(LEFT(oa.goodsTaxInfo, 1)=\'f\', 0, 1) AS category';
        }

        $this->db->strJoin = 'LEFT JOIN ' . DB_ORDER_GOODS . ' og ON og.orderNo = oa.orderNo AND og.orderCd = oa.orderCd LEFT JOIN ' . DB_ORDER_HANDLE . ' oh ON og.handleSno = oh.sno LEFT JOIN ' . DB_ORDER . ' o ON og.orderNo = o.orderNo';
        $this->db->strField = implode(', ', $tmpFieldItem);
        $this->db->strOrder = 'paymentDt ASC';
        $this->db->strGroup = $tmpGroup . $tmpOtherGroup;
        $this->db->strWhere = implode(' AND ', $arrWhere);

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_ADD_GOODS . ' oa ' . implode(' ', $query);
        $addGoodsData = $this->db->query_fetch($strSQL, $arrBind);

        // 주문배송비 추출
        $tmpFieldItem = [
            $tmpGroup . ' AS paymentDt',
//            'od.deliveryCharge AS price',// 2016-11-24 배송비가 네이버페이인 경우 0원이 되는 케이스로 인해 배송비 누락현상 발생
            '(od.deliveryPolicyCharge + od.deliveryAreaCharge) AS price',
            'od.divisionDeliveryCharge AS dcPrice',
        ];

        // 회원/비회원 분류
        if ($groupType == 'member') {
            $tmpFieldItem[] = 'IF(o.memNo=0, 0, 1) AS category';
        } elseif ($groupType == 'tax') {
            $tmpFieldItem[] = 'IF(LEFT(od.deliveryTaxInfo, 1)=\'f\', 0, 1) AS category';
        }

        // 공급사 접속시
        if (Manager::isProvider()) {
            $arrWhere[] = 'od.scmNo = ?';
            $this->db->bind_param_push($arrBind, 'i', \Session::get('manager.scmNo'));
        }

        $this->db->strJoin = 'LEFT JOIN ' . DB_ORDER_GOODS . ' og ON og.orderNo = od.orderNo AND og.orderDeliverySno = od.sno LEFT JOIN ' . DB_ORDER_HANDLE . ' oh ON og.handleSno = oh.sno LEFT JOIN ' . DB_ORDER . ' o ON og.orderNo = o.orderNo';
        $this->db->strField = implode(', ', $tmpFieldItem);
        $this->db->strOrder = 'paymentDt ASC';
        $this->db->strGroup = 'od.sno';
        $this->db->strWhere = implode(' AND ', $arrWhere);

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_DELIVERY . ' od ' . implode(' ', $query);
        $deliveryData = $this->db->query_fetch($strSQL, $arrBind);
        unset($arrWhere, $arrJoin, $arrBind, $tmpFieldItem);

        $getData = [];
        if ($groupTypeFl) {
            $totalGoodsPrice = [];
            $totalGoodsDcPrice = [];
            $totalGoodsSettlePrice = [];
            $totalDeliveryPrice = [];
            $totalDeliveryDcPrice = [];
            $totalDeliverySettlePrice = [];
            $totalSettlePrice = [];
        } else {
            $totalGoodsPrice = 0;
            $totalGoodsDcPrice = 0;
            $totalGoodsSettlePrice = 0;
            $totalDeliveryPrice = 0;
            $totalDeliveryDcPrice = 0;
            $totalDeliverySettlePrice = 0;
            $totalSettlePrice = 0;
        }

        // 상품금액
        foreach ($goodsData as $val) {
            // 결제완료일
            $paymentDt = substr($val['paymentDt'], 0, 10);

            // 금액 계산
            $price = $val['price'];
            $dcPrice = $val['dcPrice'];
            $settlePrice = ($price - $dcPrice);

            // 반환 데이터 설정
            if ($groupTypeFl) {
                $getData[$paymentDt][$val['category']] = [
                    'goodsPrice'       => $price,
                    'goodsDcPrice'     => $dcPrice,
                    'goodsSettlePrice' => $settlePrice,
                    'settlePrice'      => $settlePrice,
                ];

                $totalGoodsPrice[$val['category']] += $price;
                $totalGoodsDcPrice[$val['category']] += $dcPrice;
                $totalGoodsSettlePrice[$val['category']] += $settlePrice;
                $totalSettlePrice[$val['category']] += $settlePrice;
            } else {
                $getData[$paymentDt] = [
                    'goodsPrice'       => $price,
                    'goodsDcPrice'     => $dcPrice,
                    'goodsSettlePrice' => $settlePrice,
                    'settlePrice'      => $settlePrice,
                ];

                $totalGoodsPrice += $price;
                $totalGoodsDcPrice += $dcPrice;
                $totalGoodsSettlePrice += $settlePrice;
                $totalSettlePrice += $settlePrice;
            }
        }

        // 추가상품 금액
        foreach ($addGoodsData as $val) {
            // 결제완료일
            $paymentDt = substr($val['paymentDt'], 0, 10);

            // 금액계산
            $price = $val['price'];
            $dcPrice = $val['dcPrice'];
            $settlePrice = ($price - $dcPrice);

            // 반환 데이터 설정
            if ($groupTypeFl) {
                $getData[$paymentDt][$val['category']]['goodsPrice'] += $price;
                $getData[$paymentDt][$val['category']]['goodsDcPrice'] += $dcPrice;
                $getData[$paymentDt][$val['category']]['goodsSettlePrice'] += $settlePrice;
                $getData[$paymentDt][$val['category']]['settlePrice'] += $settlePrice;

                $totalGoodsPrice[$val['category']] += $price;
                $totalGoodsDcPrice[$val['category']] += $dcPrice;
                $totalGoodsSettlePrice[$val['category']] += $settlePrice;
                $totalSettlePrice[$val['category']] += $settlePrice;
            } else {
                $getData[$paymentDt]['goodsPrice'] += $price;
                $getData[$paymentDt]['goodsDcPrice'] += $dcPrice;
                $getData[$paymentDt]['goodsSettlePrice'] += $settlePrice;
                $getData[$paymentDt]['settlePrice'] += $settlePrice;

                $totalGoodsPrice += $price;
                $totalGoodsDcPrice += $dcPrice;
                $totalGoodsSettlePrice += $settlePrice;
                $totalSettlePrice += $settlePrice;
            }
        }

        // 배송비 금액
        foreach ($deliveryData as $val) {
            // 결제완료일
            $paymentDt = substr($val['paymentDt'], 0, 10);

            // 금액계산
            $price = $val['price'];
            $dcPrice = $val['dcPrice'];
            $settlePrice = ($price - $dcPrice);

            // 반환 데이터 설정
            if ($groupTypeFl) {
                $getData[$paymentDt][$val['category']]['deliveryPrice'] += $price;
                $getData[$paymentDt][$val['category']]['deliveryDcPrice'] += $dcPrice;
                $getData[$paymentDt][$val['category']]['deliverySettlePrice'] += $settlePrice;
                $getData[$paymentDt][$val['category']]['settlePrice'] += $settlePrice;

                $totalDeliveryPrice[$val['category']] += $price;
                $totalDeliveryDcPrice[$val['category']] += $dcPrice;
                $totalDeliverySettlePrice[$val['category']] += $settlePrice;
                $totalSettlePrice[$val['category']] += $settlePrice;
            } else {
                $getData[$paymentDt]['deliveryPrice'] += $price;
                $getData[$paymentDt]['deliveryDcPrice'] += $dcPrice;
                $getData[$paymentDt]['deliverySettlePrice'] += $settlePrice;
                $getData[$paymentDt]['settlePrice'] += $settlePrice;

                $totalDeliveryPrice += $price;
                $totalDeliveryDcPrice += $dcPrice;
                $totalDeliverySettlePrice += $settlePrice;
                $totalSettlePrice += $settlePrice;
            }
        }

        if ($groupTypeFl) {
            foreach ([
                         0,
                         1,
                     ] as $val) {
                $getData['total'][$val]['goodsPrice'] = $totalGoodsPrice[$val];
                $getData['total'][$val]['goodsDcPrice'] = $totalGoodsDcPrice[$val];
                $getData['total'][$val]['goodsSettlePrice'] = $totalGoodsSettlePrice[$val];
                $getData['total'][$val]['deliveryPrice'] = $totalDeliveryPrice[$val];
                $getData['total'][$val]['deliveryDcPrice'] = $totalDeliveryDcPrice[$val];
                $getData['total'][$val]['deliverySettlePrice'] = $totalDeliverySettlePrice[$val];
                $getData['total'][$val]['settlePrice'] = $totalSettlePrice[$val];
            }
        } else {
            $getData['total']['goodsPrice'] = $totalGoodsPrice;
            $getData['total']['goodsDcPrice'] = $totalGoodsDcPrice;
            $getData['total']['goodsSettlePrice'] = $totalGoodsSettlePrice;
            $getData['total']['deliveryPrice'] = $totalDeliveryPrice;
            $getData['total']['deliveryDcPrice'] = $totalDeliveryDcPrice;
            $getData['total']['deliverySettlePrice'] = $totalDeliverySettlePrice;
            $getData['total']['settlePrice'] = $totalSettlePrice;
        }

        return $getData;
    }

    /**
     * 매출분석 > 매출통계 (공급사용)
     *
     * @param string  $typeFl    pc|mobile|write
     * @param string  $groupType day|month|hour|member|tax|week
     * @param boolean $useScmFl
     * @param boolean $isRefund
     *
     * @return array 쿼리 데이터
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public function getStaticsProviderSalesData($typeFl, $groupType, $useScmFl = false, $isRefund = false)
    {
        // 공통 처리일자 검색
        $arrBind = [];
        if (isset($this->search['periodFl']) && $this->search['treatDate'][0] && $this->search['treatDate'][1]) {
            $arrWhere[] = 'og.paymentDt BETWEEN ? AND ?';

            // 타입이 월별인 경우 1일과 마지막 일자로 변경해야 한다.
            if ($groupType == 'month') {
                $this->search['treatDate'][0] = date('Y-m-01', strtotime($this->search['treatDate'][0]));
                $this->search['treatDate'][1] = date('Y-m-t', strtotime($this->search['treatDate'][1]));
            }
            $this->db->bind_param_push($arrBind, 's', $this->search['treatDate'][0] . ' 00:00:00');
            $this->db->bind_param_push($arrBind, 's', $this->search['treatDate'][1] . ' 23:59:59');
        }

        // 공통 허용 주문상태 설정
        $arrWhere[] = 'LEFT(og.orderStatus, 1) IN (\'' . implode('\', \'', $this->statusStatisticsCd02) . '\')';

        // 공급사 선택
        $arrWhere[] = 'og.scmNo = ?';
        $this->db->bind_param_push($arrBind, 'i', \Session::get('manager.scmNo'));

        // 쿼리 그룹설정
        $groupTypeFl = false;
        $tmpOtherGroup = '';
        switch ($groupType) {
            case 'day':
                $tmpGroup = 'DATE_FORMAT(og.paymentDt, \'%Y-%m-%d\')';
                break;

            case 'month':
                $tmpGroup = 'DATE_FORMAT(og.paymentDt, \'%Y-%m\')';
                break;

            case 'hour':
                $tmpGroup = 'DATE_FORMAT(og.paymentDt, \'%H\')';
                break;

            case 'week':
                $tmpGroup = 'DATE_FORMAT(og.paymentDt, \'%w\')';
                break;

            case 'commission':
                $tmpGroup = 'DATE_FORMAT(og.paymentDt, \'%Y-%m-%d\')';
                break;

            case 'member':
                $groupTypeFl = true;
                $tmpGroup = 'DATE_FORMAT(og.paymentDt, \'%Y-%m-%d\')';
                $tmpOtherGroup = ', category';
                break;

            case 'tax':
                $groupTypeFl = true;
                $tmpGroup = 'DATE_FORMAT(og.paymentDt, \'%Y-%m-%d\')';
                $tmpOtherGroup = ', category';
                break;
        }

        // 유형 선택
        $arrWhere[] = 'o.orderTypeFl = ?';
        $this->db->bind_param_push($arrBind, 's', $typeFl);

        // 환불완료 건만
        if ($isRefund) {
            $arrWhere[] = '((oh.handleCompleteFl = \'y\' AND oh.handleMode = \'r\' AND og.orderStatus = \'r3\') OR (o.orderChannelFl=\'naverpay\' AND og.orderStatus = \'r3\'))';
        }

        // 주문 상품 + 추가상품 금액 추출
        $tmpFieldItem = [
            $tmpGroup . ' AS paymentDt',
            'SUM(((og.goodsPrice + og.optionPrice + og.optionTextPrice) * og.goodsCnt) + og.addGoodsPrice) AS price',
        ];

        // 회원/비회원 분류
        if ($groupType == 'member') {
            $tmpFieldItem[] = 'IF(o.memNo=0, 0, 1) AS category';
        } elseif ($groupType == 'tax') {
            $tmpFieldItem[] = 'IF(LEFT(og.goodsTaxInfo, 1)=\'f\', 0, 1) AS category';
        }

        $this->db->strJoin = 'LEFT JOIN ' . DB_ORDER_HANDLE . ' oh ON og.handleSno = oh.sno LEFT JOIN ' . DB_ORDER . ' o ON og.orderNo = o.orderNo';
        $this->db->strField = implode(', ', $tmpFieldItem);
        $this->db->strOrder = 'paymentDt ASC';
        $this->db->strGroup = $tmpGroup . $tmpOtherGroup;
        $this->db->strWhere = implode(' AND ', $arrWhere);

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_GOODS . ' og ' . implode(' ', $query);
        $goodsData = $this->db->query_fetch($strSQL, $arrBind);

        // 회원/비회원 분류
        if ($groupType == 'member') {
            $tmpFieldItem[] = 'IF(o.memNo=0, 0, 1) AS category';
        } elseif ($groupType == 'tax') {
            $tmpFieldItem[] = 'IF(LEFT(oa.goodsTaxInfo, 1)=\'f\', 0, 1) AS category';
        }

        // 주문배송비 추출
        $tmpFieldItem = [
            $tmpGroup . ' AS paymentDt',
            //            'od.deliveryCharge AS price',// 2016-11-24 배송비가 네이버페이인 경우 0원이 되는 케이스로 인해 배송비 누락현상 발생
            '(od.deliveryPolicyCharge + od.deliveryAreaCharge) AS price',
            'od.divisionDeliveryCharge AS dcPrice',
            'od.scmNo',
        ];

        // 회원/비회원 분류
        if ($groupType == 'member') {
            $tmpFieldItem[] = 'IF(o.memNo=0, 0, 1) AS category';
        } elseif ($groupType == 'tax') {
            $tmpFieldItem[] = 'IF(LEFT(od.deliveryTaxInfo, 1)=\'f\', 0, 1) AS category';
        }

        // 공급사 접속시
        if (Manager::isProvider()) {
            $arrWhere[] = 'od.scmNo = ?';
            $this->db->bind_param_push($arrBind, 'i', \Session::get('manager.scmNo'));
        }

        $this->db->strJoin = 'LEFT JOIN ' . DB_ORDER_GOODS . ' og ON og.orderNo = od.orderNo LEFT JOIN ' . DB_ORDER_HANDLE . ' oh ON og.handleSno = oh.sno LEFT JOIN ' . DB_ORDER . ' o ON od.orderNo = o.orderNo';
        $this->db->strField = implode(', ', $tmpFieldItem);
        $this->db->strOrder = 'paymentDt ASC';
        $this->db->strGroup = 'og.orderDeliverySno';
        $this->db->strWhere = implode(' AND ', $arrWhere);

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_DELIVERY . ' od ' . implode(' ', $query);
        $deliveryData = $this->db->query_fetch($strSQL, $arrBind);
        unset($arrWhere, $arrJoin, $arrBind, $tmpFieldItem);

        $getData = [];
        if ($groupTypeFl) {
            $totalGoodsPrice = [];
            $totalDeliveryPrice = [];
            $totalSettlePrice = [];
        } else {
            $totalGoodsPrice = 0;
            $totalDeliveryPrice = 0;
            $totalSettlePrice = 0;
        }

        // 상품금액
        foreach ($goodsData as $val) {
            // 결제완료일
            $paymentDt = substr($val['paymentDt'], 0, 10);

            // 금액 계산
            $price = $val['price'];
            $settlePrice = $price;

            // 반환 데이터 설정
            if ($groupTypeFl) {
                $getData[$paymentDt][$val['category']] = [
                    'goodsPrice'  => $price,
                    'settlePrice' => $settlePrice,
                ];

                $totalGoodsPrice[$val['category']] += $price;
                $totalSettlePrice[$val['category']] += $settlePrice;
            } else {
                $getData[$paymentDt] = [
                    'goodsPrice'  => $price,
                    'settlePrice' => $settlePrice,
                ];

                $totalGoodsPrice += $price;
                $totalSettlePrice += $settlePrice;
            }
        }

        // 배송비 금액
        foreach ($deliveryData as $val) {
            // 결제완료일
            $paymentDt = substr($val['paymentDt'], 0, 10);

            // 금액계산
            $price = $val['price'];
            $settlePrice = $price;

            // 반환 데이터 설정
            if ($groupTypeFl) {
                $getData[$paymentDt][$val['category']]['deliveryPrice'] += $price;
                $getData[$paymentDt][$val['category']]['settlePrice'] += $settlePrice;

                $totalDeliveryPrice[$val['category']] += $price;
                $totalSettlePrice[$val['category']] += $settlePrice;
            } else {
                $getData[$paymentDt]['deliveryPrice'] += $price;
                $getData[$paymentDt]['settlePrice'] += $settlePrice;

                $totalDeliveryPrice += $price;
                $totalSettlePrice += $settlePrice;
            }
        }

        if ($groupTypeFl) {
            foreach ([
                         0,
                         1,
                     ] as $val) {
                $getData['total'][$val]['goodsPrice'] = $totalGoodsPrice[$val];
                $getData['total'][$val]['deliveryPrice'] = $totalDeliveryPrice[$val];
                $getData['total'][$val]['settlePrice'] = $totalSettlePrice[$val];
            }
        } else {
            $getData['total']['goodsPrice'] = $totalGoodsPrice;
            $getData['total']['deliveryPrice'] = $totalDeliveryPrice;
            $getData['total']['settlePrice'] = $totalSettlePrice;
        }

        return $getData;
    }

    /**
     * 매출분석 > 결제수단 분석
     *
     * @param array  $searchData 검색데이터
     * @param string $groupType  day|dayofweek|month|hour
     *
     * @return array 결제수단 분석 정보
     * @throws AlertBackException
     */
    public function getStatisticsSalesSettle($searchData, $groupType = 'day')
    {
        // 초기화
        $getData = [];

        // 요청 들어온 검색 데이터 초기화
        $this->search['periodFl'] = gd_isset($searchData['periodFl'], $groupType == 'month' ? 30 : 7);
        $this->search['treatDate'][0] = gd_isset($searchData['treatDate'][0], date('Y-m-d', strtotime('-' . $searchData['periodFl'] . ' day')));
        $this->search['treatDate'][1] = gd_isset($searchData['treatDate'][1], date('Y-m-d'));

        // 최대 3개월 넘지 않도록 처리
        $intervalDay = DateTimeUtils::intervalDay($searchData['treatDate'][0], $searchData['treatDate'][1]);
        if ($groupType == 'month') {
            $maxSearchDate = 360;
            $maxMessage = __('최대 12개월까지 조회할 수 있습니다.');
        } else {
            $maxSearchDate = 90;
            $maxMessage = __('최대 3개월까지 조회할 수 있습니다.');
        }
        if ($intervalDay > $maxSearchDate) {
            throw new AlertBackException($maxMessage);
        }
        $this->search['interval'] = $intervalDay;
        $getData['search'] = gd_htmlspecialchars($this->search);

        // UI 체크박스 설정
        $this->checked['periodFl'][$searchData['periodFl']] = 'active';
        $getData['checked'] = gd_htmlspecialchars($this->checked);

        // 공통 처리일자 검색
        $arrBind = [];
        if (isset($this->search['periodFl']) && $this->search['treatDate'][0] && $this->search['treatDate'][1]) {
            $arrWhere[] = 'og.paymentDt BETWEEN ? AND ?';

            // 타입이 월별인 경우 1일과 마지막 일자로 변경해야 한다.
            if ($groupType == 'month') {
                $this->search['treatDate'][0] = date('Y-m-01', strtotime($this->search['treatDate'][0]));
                $this->search['treatDate'][1] = date('Y-m-t', strtotime($this->search['treatDate'][1]));
            }
            $this->db->bind_param_push($arrBind, 's', $this->search['treatDate'][0] . ' 00:00:00');
            $this->db->bind_param_push($arrBind, 's', $this->search['treatDate'][1] . ' 23:59:59');
        }

        // 공통 허용 주문상태 설정
        $arrWhere[] = 'LEFT(og.orderStatus, 1) IN (\'' . implode('\', \'', $this->statusStatisticsCd02) . '\')';

        // 공급사 접속시
        if (Manager::isProvider()) {
            $arrWhere[] = 'og.scmNo = ?';
            $this->db->bind_param_push($arrBind, 'i', \Session::get('manager.scmNo'));
        }

        // 쿼리 그룹설정
        switch ($groupType) {
            case 'day':
                $tmpGroup = 'DATE_FORMAT(og.paymentDt, \'%Y-%m-%d\')';
                break;

            case 'month':
                $tmpGroup = 'DATE_FORMAT(og.paymentDt, \'%Y-%m\')';
                break;

            case 'hour':
                $tmpGroup = 'DATE_FORMAT(og.paymentDt, \'%H\')';
                break;

            case 'week':
                $tmpGroup = 'DATE_FORMAT(og.paymentDt, \'%w\')';
                break;
        }

        // 쿼리 필드
        $tmpFieldItem = [
            $tmpGroup . ' AS paymentDt',
            'o.settleKind AS settleKind',
            'SUM(IF(o.orderTypeFl=\'pc\', ((og.goodsPrice + og.optionPrice + og.optionTextPrice) * og.goodsCnt) - (og.goodsDcPrice + og.memberDcPrice + og.memberOverlapDcPrice + og.couponGoodsDcPrice + og.divisionCouponOrderDcPrice), 0)) AS pcPrice',
            'SUM(IF(o.orderTypeFl=\'pc\' AND og.orderStatus=\'r3\' AND oh.handleCompleteFl = \'y\' AND oh.handleMode = \'r\', ((og.goodsPrice + og.optionPrice + og.optionTextPrice) * og.goodsCnt) - (og.goodsDcPrice + og.memberDcPrice + og.memberOverlapDcPrice + og.couponGoodsDcPrice + og.divisionCouponOrderDcPrice), 0)) AS pcRefundPrice',
            'SUM(IF(o.orderTypeFl=\'mobile\', ((og.goodsPrice + og.optionPrice + og.optionTextPrice) * og.goodsCnt) - (og.goodsDcPrice + og.memberDcPrice + og.memberOverlapDcPrice + og.couponGoodsDcPrice + og.divisionCouponOrderDcPrice), 0)) AS mobilePrice',
            'SUM(IF(o.orderTypeFl=\'mobile\' AND og.orderStatus=\'r3\' AND oh.handleCompleteFl = \'y\' AND oh.handleMode = \'r\', ((og.goodsPrice + og.optionPrice + og.optionTextPrice) * og.goodsCnt) - (og.goodsDcPrice + og.memberDcPrice + og.memberOverlapDcPrice + og.couponGoodsDcPrice + og.divisionCouponOrderDcPrice), 0)) AS mobileRefundPrice',
            'SUM(IF(o.orderTypeFl=\'write\', ((og.goodsPrice + og.optionPrice + og.optionTextPrice) * og.goodsCnt) - (og.goodsDcPrice + og.memberDcPrice + og.memberOverlapDcPrice + og.couponGoodsDcPrice + og.divisionCouponOrderDcPrice), 0)) AS writePrice',
            'SUM(IF(o.orderTypeFl=\'write\' AND og.orderStatus=\'r3\' AND oh.handleCompleteFl = \'y\' AND oh.handleMode = \'r\', ((og.goodsPrice + og.optionPrice + og.optionTextPrice) * og.goodsCnt) - (og.goodsDcPrice + og.memberDcPrice + og.memberOverlapDcPrice + og.couponGoodsDcPrice + og.divisionCouponOrderDcPrice), 0)) AS writeRefundPrice',
        ];

        $this->db->strField = implode(', ', $tmpFieldItem);
        $this->db->strJoin = 'LEFT JOIN ' . DB_ORDER . ' o ON o.orderNo = og.orderNo LEFT JOIN ' . DB_ORDER_HANDLE . ' oh ON og.handleSno = oh.sno';
        $this->db->strWhere = implode(' AND ', $arrWhere);
        $this->db->strOrder = 'paymentDt ASC';
        $this->db->strGroup = 'paymentDt, settleKind';

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_GOODS . ' og ' . implode(' ', $query);
        $orderData = $this->db->query_fetch($strSQL, $arrBind);

        // 추가주문상품 추출
        $tmpFieldItem = [
            $tmpGroup . ' AS paymentDt',
            'o.settleKind AS settleKind',
            'SUM(IF(o.orderTypeFl=\'pc\', (oa.goodsPrice * oa.goodsCnt) - (oa.addMemberDcPrice + oa.addMemberOverlapDcPrice + oa.addCouponGoodsDcPrice + oa.divisionAddCouponOrderDcPrice), 0)) AS pcPrice',
            'SUM(IF(o.orderTypeFl=\'pc\' AND og.orderStatus=\'r3\' AND oh.handleCompleteFl = \'y\' AND oh.handleMode = \'r\', (oa.goodsPrice * oa.goodsCnt) - (oa.addMemberDcPrice + oa.addMemberOverlapDcPrice + oa.addCouponGoodsDcPrice + oa.divisionAddCouponOrderDcPrice), 0)) AS pcRefundPrice',
            'SUM(IF(o.orderTypeFl=\'mobile\', (oa.goodsPrice * oa.goodsCnt) - (oa.addMemberDcPrice + oa.addMemberOverlapDcPrice + oa.addCouponGoodsDcPrice + oa.divisionAddCouponOrderDcPrice), 0)) AS mobilePrice',
            'SUM(IF(o.orderTypeFl=\'mobile\' AND og.orderStatus=\'r3\' AND oh.handleCompleteFl = \'y\' AND oh.handleMode = \'r\', (oa.goodsPrice * oa.goodsCnt) - (oa.addMemberDcPrice + oa.addMemberOverlapDcPrice + oa.addCouponGoodsDcPrice + oa.divisionAddCouponOrderDcPrice), 0)) AS mobileRefundPrice',
            'SUM(IF(o.orderTypeFl=\'write\', (oa.goodsPrice * oa.goodsCnt) - (oa.addMemberDcPrice + oa.addMemberOverlapDcPrice + oa.addCouponGoodsDcPrice + oa.divisionAddCouponOrderDcPrice), 0)) AS writePrice',
            'SUM(IF(o.orderTypeFl=\'write\' AND og.orderStatus=\'r3\' AND oh.handleCompleteFl = \'y\' AND oh.handleMode = \'r\', (oa.goodsPrice * oa.goodsCnt) - (oa.addMemberDcPrice + oa.addMemberOverlapDcPrice + oa.addCouponGoodsDcPrice + oa.divisionAddCouponOrderDcPrice), 0)) AS writeRefundPrice',
        ];

        // 회원/비회원 분류
        if ($groupType == 'member') {
            $tmpFieldItem[] = 'IF(o.memNo=0, 0, 1) AS category';
        } elseif ($groupType == 'tax') {
            $tmpFieldItem[] = 'IF(LEFT(oa.goodsTaxInfo, 1)=\'f\', 0, 1) AS category';
        }

        $this->db->strJoin = 'LEFT JOIN ' . DB_ORDER_GOODS . ' og ON og.orderNo = oa.orderNo AND og.orderCd = oa.orderCd LEFT JOIN ' . DB_ORDER_HANDLE . ' oh ON og.handleSno = oh.sno LEFT JOIN ' . DB_ORDER . ' o ON og.orderNo = o.orderNo LEFT OUTER JOIN ' . DB_MEMBER . ' AS m ON o.memNo = m.memNo';
        $this->db->strField = implode(', ', $tmpFieldItem);
        $this->db->strWhere = implode(' AND ', $arrWhere);
        $this->db->strOrder = 'paymentDt ASC';
        $this->db->strGroup = 'paymentDt, settleKind';
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_ADD_GOODS . ' oa ' . implode(' ', $query);
        $addGoodsData = $this->db->query_fetch($strSQL, $arrBind);

        // 주문배송비 추출
        $tmpFieldItem = [
            $tmpGroup . ' AS paymentDt',
            'o.settleKind AS settleKind',
            'IF(o.orderTypeFl=\'pc\', od.deliveryCharge - od.divisionDeliveryCharge, 0) AS pcPrice',
            'IF(o.orderTypeFl=\'pc\' AND og.orderStatus=\'r3\' AND oh.handleCompleteFl = \'y\' AND oh.handleMode = \'r\', od.deliveryCharge - od.divisionDeliveryCharge, 0) AS pcRefundPrice',
            'IF(o.orderTypeFl=\'mobile\', od.deliveryCharge - od.divisionDeliveryCharge, 0) AS mobilePrice',
            'IF(o.orderTypeFl=\'mobile\' AND og.orderStatus=\'r3\' AND oh.handleCompleteFl = \'y\' AND oh.handleMode = \'r\', od.deliveryCharge - od.divisionDeliveryCharge, 0) AS mobileRefundPrice',
            'IF(o.orderTypeFl=\'write\', od.deliveryCharge - od.divisionDeliveryCharge, 0) AS writePrice',
            'IF(o.orderTypeFl=\'write\' AND og.orderStatus=\'r3\' AND oh.handleCompleteFl = \'y\' AND oh.handleMode = \'r\', od.deliveryCharge - od.divisionDeliveryCharge, 0) AS writeRefundPrice',
        ];

        // 회원/비회원 분류
        if ($groupType == 'member') {
            $tmpFieldItem[] = 'IF(o.memNo=0, 0, 1) AS category';
        } elseif ($groupType == 'tax') {
            $tmpFieldItem[] = 'IF(LEFT(od.deliveryTaxInfo, 1)=\'f\', 0, 1) AS category';
        }

        // 공급사 접속시
        if (Manager::isProvider()) {
            $arrWhere[] = 'od.scmNo = ?';
            $this->db->bind_param_push($arrBind, 'i', \Session::get('manager.scmNo'));
        }

        $this->db->strJoin = 'LEFT JOIN ' . DB_ORDER_GOODS . ' og ON og.orderNo = od.orderNo AND og.orderDeliverySno = od.sno LEFT JOIN ' . DB_ORDER_HANDLE . ' oh ON og.handleSno = oh.sno LEFT JOIN ' . DB_ORDER . ' o ON od.orderNo = o.orderNo LEFT OUTER JOIN ' . DB_MEMBER . ' AS m ON o.memNo = m.memNo';
        $this->db->strField = implode(', ', $tmpFieldItem);
        $this->db->strWhere = implode(' AND ', $arrWhere);
        $this->db->strOrder = 'paymentDt ASC';
        $this->db->strGroup = 'og.orderDeliverySno, paymentDt, settleKind';

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_DELIVERY . ' od ' . implode(' ', $query);
        $deliveryData = $this->db->query_fetch($strSQL, $arrBind);
        unset($arrWhere, $arrJoin, $arrBind, $tmpFieldItem);

        // 초기화
        $totalPcPrice = [];
        $totalMobilePrice = [];
        $totalWritePrice = [];
        $totalSettlePrice = [];

        // 상품금액
        foreach ($orderData as $val) {
            // 결제완료일
            $paymentDt = substr($val['paymentDt'], 0, 10);

            // 금액 계산
            $pcPrice = $val['pcPrice'] - $val['pcRefundPrice'];
            $mobilePrice = $val['mobilePrice'] - $val['mobileRefundPrice'];
            $writePrice = $val['writePrice'] - $val['writeRefundPrice'];
            $settlePrice = ($pcPrice + $mobilePrice + $writePrice);

            $totalPcPrice[$val['settleKind']] += $pcPrice;
            $totalMobilePrice[$val['settleKind']] += $mobilePrice;
            $totalWritePrice[$val['settleKind']] += $writePrice;
            $totalSettlePrice[$val['settleKind']] += $settlePrice;

            // 반환 데이터 설정
            $getData['payment'][$paymentDt][$val['settleKind']] = [
                'pcPrice'     => $pcPrice,
                'mobilePrice' => $mobilePrice,
                'writePrice'  => $writePrice,
                'settlePrice' => $settlePrice,
            ];
        }

        // 추가상품 금액 계산
        foreach ($addGoodsData as $aVal) {
            $pcPrice = $aVal['pcPrice'] - $aVal['pcRefundPrice'];
            $mobilePrice = $aVal['mobilePrice'] - $aVal['mobileRefundPrice'];
            $writePrice = $aVal['writePrice'] - $aVal['writeRefundPrice'];
            $settlePrice = ($pcPrice + $mobilePrice + $writePrice);

            $totalPcPrice[$aVal['settleKind']] += $pcPrice;
            $totalMobilePrice[$aVal['settleKind']] += $mobilePrice;
            $totalWritePrice[$aVal['settleKind']] += $writePrice;
            $totalSettlePrice[$aVal['settleKind']] += $settlePrice;

            // 주문상품 반환 데이터 설정
            $getData['payment'][$aVal['paymentDt']][$aVal['settleKind']]['pcPrice'] += $pcPrice;
            $getData['payment'][$aVal['paymentDt']][$aVal['settleKind']]['mobilePrice'] += $mobilePrice;
            $getData['payment'][$aVal['paymentDt']][$aVal['settleKind']]['writePrice'] += $writePrice;
            $getData['payment'][$aVal['paymentDt']][$aVal['settleKind']]['settlePrice'] += $settlePrice;
        }

        // 배송비 금액 계산
        foreach ($deliveryData as $dVal) {
            $pcPrice = $dVal['pcPrice'] - $dVal['pcRefundPrice'];
            $mobilePrice = $dVal['mobilePrice'] - $dVal['mobileRefundPrice'];
            $writePrice = $dVal['writePrice'] - $dVal['writeRefundPrice'];
            $settlePrice = ($pcPrice + $mobilePrice + $writePrice);

            $totalPcPrice[$dVal['settleKind']] += $pcPrice;
            $totalMobilePrice[$dVal['settleKind']] += $mobilePrice;
            $totalWritePrice[$dVal['settleKind']] += $writePrice;
            $totalSettlePrice[$dVal['settleKind']] += $settlePrice;

            // 주문상품 반환 데이터 설정
            $getData['payment'][$dVal['paymentDt']][$dVal['settleKind']]['pcPrice'] += $pcPrice;
            $getData['payment'][$dVal['paymentDt']][$dVal['settleKind']]['mobilePrice'] += $mobilePrice;
            $getData['payment'][$dVal['paymentDt']][$dVal['settleKind']]['writePrice'] += $writePrice;
            $getData['payment'][$dVal['paymentDt']][$dVal['settleKind']]['settlePrice'] += $settlePrice;
        }

        // 결제수단별 총합계 산출
        foreach ($this->getSettleKind() as $settleKind => $val) {
            $getData['payment']['total'][$settleKind]['pcPrice'] = $totalPcPrice[$settleKind];
            $getData['payment']['total'][$settleKind]['mobilePrice'] = $totalMobilePrice[$settleKind];
            $getData['payment']['total'][$settleKind]['writePrice'] = $totalWritePrice[$settleKind];
            $getData['payment']['total'][$settleKind]['settlePrice'] = $totalSettlePrice[$settleKind];
        }

        return $getData;
    }

    /**
     * 매출분석 > 연령별 분석
     * TODO 현재 환불금액을 빼서 보여주지 않고 있어 해당 부분 작업이 필요하며, 연령별/지역별/결제수단별 모두 해당됩니다.
     *
     * @param array  $searchData 검색데이터
     * @param string $groupType  day|dayofweek|month|hour
     *
     * @return array 연령별 분석 정보
     * @throws AlertBackException
     */
    public function getStatisticsSalesAge($searchData, $groupType = 'day')
    {
        // 초기화
        $getData = [];

        // 요청 들어온 검색 데이터 초기화
        $this->search['periodFl'] = gd_isset($searchData['periodFl'], $groupType == 'month' ? 30 : 7);
        $this->search['treatDate'][0] = gd_isset($searchData['treatDate'][0], date('Y-m-d', strtotime('-' . $searchData['periodFl'] . ' day')));
        $this->search['treatDate'][1] = gd_isset($searchData['treatDate'][1], date('Y-m-d'));

        // 최대 3개월 넘지 않도록 처리
        $intervalDay = DateTimeUtils::intervalDay($searchData['treatDate'][0], $searchData['treatDate'][1]);
        if ($groupType == 'month') {
            $maxSearchDate = 360;
            $maxMessage = __('최대 12개월까지 조회할 수 있습니다.');
        } else {
            $maxSearchDate = 90;
            $maxMessage = __('최대 3개월까지 조회할 수 있습니다.');
        }
        if ($intervalDay > $maxSearchDate) {
            throw new AlertBackException($maxMessage);
        }
        $this->search['interval'] = $intervalDay;
        $getData['search'] = gd_htmlspecialchars($this->search);

        // UI 체크박스 설정
        $this->checked['periodFl'][$searchData['periodFl']] = 'active';
        $getData['checked'] = gd_htmlspecialchars($this->checked);

        // 공통 처리일자 검색
        $arrBind = [];
        if (isset($this->search['periodFl']) && $this->search['treatDate'][0] && $this->search['treatDate'][1]) {
            $arrWhere[] = 'og.paymentDt BETWEEN ? AND ?';

            // 타입이 월별인 경우 1일과 마지막 일자로 변경해야 한다.
            if ($groupType == 'month') {
                $this->search['treatDate'][0] = date('Y-m-01', strtotime($this->search['treatDate'][0]));
                $this->search['treatDate'][1] = date('Y-m-t', strtotime($this->search['treatDate'][1]));
            }
            $this->db->bind_param_push($arrBind, 's', $this->search['treatDate'][0] . ' 00:00:00');
            $this->db->bind_param_push($arrBind, 's', $this->search['treatDate'][1] . ' 23:59:59');
        }

        // 공통 허용 주문상태 설정
        $arrWhere[] = 'LEFT(og.orderStatus, 1) IN (\'' . implode('\', \'', $this->statusStatisticsCd02) . '\')';

        // 공급사 접속시
        if (Manager::isProvider()) {
            $arrWhere[] = 'og.scmNo = ?';
            $this->db->bind_param_push($arrBind, 'i', \Session::get('manager.scmNo'));
        }

        // 쿼리 그룹설정
        switch ($groupType) {
            case 'day':
                $tmpGroup = 'DATE_FORMAT(og.paymentDt, \'%Y-%m-%d\')';
                break;

            case 'month':
                $tmpGroup = 'DATE_FORMAT(og.paymentDt, \'%Y-%m\')';
                break;

            case 'hour':
                $tmpGroup = 'DATE_FORMAT(og.paymentDt, \'%H\')';
                break;

            case 'week':
                $tmpGroup = 'DATE_FORMAT(og.paymentDt, \'%w\')';
                break;
        }

        // 쿼리 필드
        $tmpFieldItem = [
            $tmpGroup . ' AS paymentDt',
            'IF(m.birthDt=\'0000-00-00\', ' . ArrayUtils::last($this->ages) . ', IFNULL(FLOOR((DATE_FORMAT(NOW(), \'%Y\') - LEFT(m.birthDt, 4)) / 10) * 10, ' . ArrayUtils::last($this->ages) . ')) AS age',
            'SUM(IF(o.orderTypeFl=\'pc\', ((og.goodsPrice + og.optionPrice + og.optionTextPrice) * og.goodsCnt) - (og.goodsDcPrice + og.memberDcPrice + og.memberOverlapDcPrice + og.couponGoodsDcPrice + og.divisionCouponOrderDcPrice), 0)) AS pcPrice',
            'SUM(IF(o.orderTypeFl=\'pc\' AND og.orderStatus=\'r3\' AND oh.handleCompleteFl = \'y\' AND oh.handleMode = \'r\', ((og.goodsPrice + og.optionPrice + og.optionTextPrice) * og.goodsCnt) - (og.goodsDcPrice + og.memberDcPrice + og.memberOverlapDcPrice + og.couponGoodsDcPrice + og.divisionCouponOrderDcPrice), 0)) AS pcRefundPrice',
            'SUM(IF(o.orderTypeFl=\'mobile\', ((og.goodsPrice + og.optionPrice + og.optionTextPrice) * og.goodsCnt) - (og.goodsDcPrice + og.memberDcPrice + og.memberOverlapDcPrice + og.couponGoodsDcPrice + og.divisionCouponOrderDcPrice), 0)) AS mobilePrice',
            'SUM(IF(o.orderTypeFl=\'mobile\' AND og.orderStatus=\'r3\' AND oh.handleCompleteFl = \'y\' AND oh.handleMode = \'r\', ((og.goodsPrice + og.optionPrice + og.optionTextPrice) * og.goodsCnt) - (og.goodsDcPrice + og.memberDcPrice + og.memberOverlapDcPrice + og.couponGoodsDcPrice + og.divisionCouponOrderDcPrice), 0)) AS mobileRefundPrice',
            'SUM(IF(o.orderTypeFl=\'write\', ((og.goodsPrice + og.optionPrice + og.optionTextPrice) * og.goodsCnt) - (og.goodsDcPrice + og.memberDcPrice + og.memberOverlapDcPrice + og.couponGoodsDcPrice + og.divisionCouponOrderDcPrice), 0)) AS writePrice',
            'SUM(IF(o.orderTypeFl=\'write\' AND og.orderStatus=\'r3\' AND oh.handleCompleteFl = \'y\' AND oh.handleMode = \'r\', ((og.goodsPrice + og.optionPrice + og.optionTextPrice) * og.goodsCnt) - (og.goodsDcPrice + og.memberDcPrice + og.memberOverlapDcPrice + og.couponGoodsDcPrice + og.divisionCouponOrderDcPrice), 0)) AS writeRefundPrice',
        ];

        $this->db->strField = implode(', ', $tmpFieldItem);
        $this->db->strJoin = 'LEFT JOIN ' . DB_ORDER . ' o ON o.orderNo = og.orderNo LEFT JOIN ' . DB_ORDER_HANDLE . ' oh ON og.handleSno = oh.sno LEFT OUTER JOIN ' . DB_MEMBER . ' AS m ON o.memNo = m.memNo';
        $this->db->strWhere = implode(' AND ', $arrWhere);
        $this->db->strOrder = 'paymentDt ASC';
        $this->db->strGroup = 'paymentDt, age';

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_GOODS . ' og ' . implode(' ', $query);
        $orderData = $this->db->query_fetch($strSQL, $arrBind);

        // 추가주문상품 추출
        // 0~9세로 생일이 추출되는 경우 금액이 안맞을 수 있다.
        $tmpFieldItem = [
            $tmpGroup . ' AS paymentDt',
            'IF(m.birthDt=\'0000-00-00\', ' . ArrayUtils::last($this->ages) . ', IFNULL(FLOOR((DATE_FORMAT(NOW(), \'%Y\') - LEFT(m.birthDt, 4)) / 10) * 10, ' . ArrayUtils::last($this->ages) . ')) AS age',
            'SUM(IF(o.orderTypeFl=\'pc\', (oa.goodsPrice * oa.goodsCnt) - (oa.addMemberDcPrice + oa.addMemberOverlapDcPrice + oa.addCouponGoodsDcPrice + oa.divisionAddCouponOrderDcPrice), 0)) AS pcPrice',
            'SUM(IF(o.orderTypeFl=\'pc\' AND og.orderStatus=\'r3\' AND oh.handleCompleteFl = \'y\' AND oh.handleMode = \'r\', (oa.goodsPrice * oa.goodsCnt) - (oa.addMemberDcPrice + oa.addMemberOverlapDcPrice + oa.addCouponGoodsDcPrice + oa.divisionAddCouponOrderDcPrice), 0)) AS pcRefundPrice',
            'SUM(IF(o.orderTypeFl=\'mobile\', (oa.goodsPrice * oa.goodsCnt) - (oa.addMemberDcPrice + oa.addMemberOverlapDcPrice + oa.addCouponGoodsDcPrice + oa.divisionAddCouponOrderDcPrice), 0)) AS mobilePrice',
            'SUM(IF(o.orderTypeFl=\'mobile\' AND og.orderStatus=\'r3\' AND oh.handleCompleteFl = \'y\' AND oh.handleMode = \'r\', (oa.goodsPrice * oa.goodsCnt) - (oa.addMemberDcPrice + oa.addMemberOverlapDcPrice + oa.addCouponGoodsDcPrice + oa.divisionAddCouponOrderDcPrice), 0)) AS mobileRefundPrice',
            'SUM(IF(o.orderTypeFl=\'write\', (oa.goodsPrice * oa.goodsCnt) - (oa.addMemberDcPrice + oa.addMemberOverlapDcPrice + oa.addCouponGoodsDcPrice + oa.divisionAddCouponOrderDcPrice), 0)) AS writePrice',
            'SUM(IF(o.orderTypeFl=\'write\' AND og.orderStatus=\'r3\' AND oh.handleCompleteFl = \'y\' AND oh.handleMode = \'r\', (oa.goodsPrice * oa.goodsCnt) - (oa.addMemberDcPrice + oa.addMemberOverlapDcPrice + oa.addCouponGoodsDcPrice + oa.divisionAddCouponOrderDcPrice), 0)) AS writeRefundPrice',
        ];

        // 회원/비회원 분류
        if ($groupType == 'member') {
            $tmpFieldItem[] = 'IF(o.memNo=0, 0, 1) AS category';
        } elseif ($groupType == 'tax') {
            $tmpFieldItem[] = 'IF(LEFT(oa.goodsTaxInfo, 1)=\'f\', 0, 1) AS category';
        }

        $this->db->strJoin = 'LEFT JOIN ' . DB_ORDER_GOODS . ' og ON og.orderNo = oa.orderNo AND og.orderCd = oa.orderCd LEFT JOIN ' . DB_ORDER_HANDLE . ' oh ON og.handleSno = oh.sno LEFT JOIN ' . DB_ORDER . ' o ON og.orderNo = o.orderNo LEFT OUTER JOIN ' . DB_MEMBER . ' AS m ON o.memNo = m.memNo';
        $this->db->strField = implode(', ', $tmpFieldItem);
        $this->db->strWhere = implode(' AND ', $arrWhere);
        $this->db->strOrder = 'paymentDt ASC';
        $this->db->strGroup = 'paymentDt, age';
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_ADD_GOODS . ' oa ' . implode(' ', $query);
        $addGoodsData = $this->db->query_fetch($strSQL, $arrBind);

        // 주문배송비 추출
        $tmpFieldItem = [
            $tmpGroup . ' AS paymentDt',
            'IF(m.birthDt=\'0000-00-00\', ' . ArrayUtils::last($this->ages) . ', IFNULL(FLOOR((DATE_FORMAT(NOW(), \'%Y\') - LEFT(m.birthDt, 4)) / 10) * 10, ' . ArrayUtils::last($this->ages) . ')) AS age',
            'IF(o.orderTypeFl=\'pc\', od.deliveryCharge - od.divisionDeliveryCharge, 0) AS pcPrice',
            'IF(o.orderTypeFl=\'pc\' AND og.orderStatus=\'r3\' AND oh.handleCompleteFl = \'y\' AND oh.handleMode = \'r\', od.deliveryCharge - od.divisionDeliveryCharge, 0) AS pcRefundPrice',
            'IF(o.orderTypeFl=\'mobile\', od.deliveryCharge - od.divisionDeliveryCharge, 0) AS mobilePrice',
            'IF(o.orderTypeFl=\'mobile\' AND og.orderStatus=\'r3\' AND oh.handleCompleteFl = \'y\' AND oh.handleMode = \'r\', od.deliveryCharge - od.divisionDeliveryCharge, 0) AS mobileRefundPrice',
            'IF(o.orderTypeFl=\'write\', od.deliveryCharge - od.divisionDeliveryCharge, 0) AS writePrice',
            'IF(o.orderTypeFl=\'write\' AND og.orderStatus=\'r3\' AND oh.handleCompleteFl = \'y\' AND oh.handleMode = \'r\', od.deliveryCharge - od.divisionDeliveryCharge, 0) AS writeRefundPrice',
        ];

        // 회원/비회원 분류
        if ($groupType == 'member') {
            $tmpFieldItem[] = 'IF(o.memNo=0, 0, 1) AS category';
        } elseif ($groupType == 'tax') {
            $tmpFieldItem[] = 'IF(LEFT(od.deliveryTaxInfo, 1)=\'f\', 0, 1) AS category';
        }

        // 공급사 접속시
        if (Manager::isProvider()) {
            $arrWhere[] = 'od.scmNo = ?';
            $this->db->bind_param_push($arrBind, 'i', \Session::get('manager.scmNo'));
        }

        $this->db->strJoin = 'JOIN ' . DB_ORDER_GOODS . ' og ON og.orderNo = od.orderNo AND og.orderDeliverySno = od.sno LEFT JOIN ' . DB_ORDER_HANDLE . ' oh ON og.handleSno = oh.sno LEFT JOIN ' . DB_ORDER . ' o ON od.orderNo = o.orderNo LEFT OUTER JOIN ' . DB_MEMBER . ' AS m ON o.memNo = m.memNo';
        $this->db->strField = implode(', ', $tmpFieldItem);
        $this->db->strWhere = implode(' AND ', $arrWhere);
        $this->db->strOrder = 'paymentDt ASC';
        $this->db->strGroup = 'og.orderDeliverySno, paymentDt, age';

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_DELIVERY . ' od ' . implode(' ', $query);
        $deliveryData = $this->db->query_fetch($strSQL, $arrBind);
        unset($arrWhere, $arrJoin, $arrBind, $tmpFieldItem);

        // 초기화
        $totalPcPrice = [];
        $totalMobilePrice = [];
        $totalWritePrice = [];
        $totalSettlePrice = [];

        // 상품금액
        foreach ($orderData as $val) {
            // 결제완료일
            $paymentDt = substr($val['paymentDt'], 0, 10);

            // 주문상품 금액 계산
            $pcPrice = $val['pcPrice'] - $val['pcRefundPrice'];
            $mobilePrice = $val['mobilePrice'] - $val['mobileRefundPrice'];
            $writePrice = $val['writePrice'] - $val['writeRefundPrice'];
            $settlePrice = ($pcPrice + $mobilePrice + $writePrice);

            $totalPcPrice[$val['age']] += $pcPrice;
            $totalMobilePrice[$val['age']] += $mobilePrice;
            $totalWritePrice[$val['age']] += $writePrice;
            $totalSettlePrice[$val['age']] += $settlePrice;

            // 주문상품 반환 데이터 설정
            $getData['payment'][$paymentDt][$val['age']] = [
                'pcPrice'     => $pcPrice,
                'mobilePrice' => $mobilePrice,
                'writePrice'  => $writePrice,
                'settlePrice' => $settlePrice,
            ];
        }

        // 추가상품 금액 계산
        foreach ($addGoodsData as $aVal) {
            $pcPrice = $aVal['pcPrice'] - $aVal['pcRefundPrice'];
            $mobilePrice = $aVal['mobilePrice'] - $aVal['mobileRefundPrice'];
            $writePrice = $aVal['writePrice'] - $aVal['writeRefundPrice'];
            $settlePrice = ($pcPrice + $mobilePrice + $writePrice);

            $totalPcPrice[$aVal['age']] += $pcPrice;
            $totalMobilePrice[$aVal['age']] += $mobilePrice;
            $totalWritePrice[$aVal['age']] += $writePrice;
            $totalSettlePrice[$aVal['age']] += $settlePrice;

            // 주문상품 반환 데이터 설정
            $getData['payment'][$aVal['paymentDt']][$aVal['age']]['pcPrice'] += $pcPrice;
            $getData['payment'][$aVal['paymentDt']][$aVal['age']]['mobilePrice'] += $mobilePrice;
            $getData['payment'][$aVal['paymentDt']][$aVal['age']]['writePrice'] += $writePrice;
            $getData['payment'][$aVal['paymentDt']][$aVal['age']]['settlePrice'] += $settlePrice;
        }

        // 배송비 금액 계산
        if (empty($deliveryData) === false) {
            foreach ($deliveryData as $dVal) {
                $pcPrice = $dVal['pcPrice'] - $dVal['pcRefundPrice'];
                $mobilePrice = $dVal['mobilePrice'] - $dVal['mobileRefundPrice'];
                $writePrice = $dVal['writePrice'] - $dVal['writeRefundPrice'];
                $settlePrice = ($pcPrice + $mobilePrice + $writePrice);

                $totalPcPrice[$dVal['age']] += $pcPrice;
                $totalMobilePrice[$dVal['age']] += $mobilePrice;
                $totalWritePrice[$dVal['age']] += $writePrice;
                $totalSettlePrice[$dVal['age']] += $settlePrice;

                // 주문상품 반환 데이터 설정
                $getData['payment'][$dVal['paymentDt']][$dVal['age']]['pcPrice'] += $pcPrice;
                $getData['payment'][$dVal['paymentDt']][$dVal['age']]['mobilePrice'] += $mobilePrice;
                $getData['payment'][$dVal['paymentDt']][$dVal['age']]['writePrice'] += $writePrice;
                $getData['payment'][$dVal['paymentDt']][$dVal['age']]['settlePrice'] += $settlePrice;
            }
        }

        // 나이별 총합계 산출
        foreach ($this->ages as $age) {
            $getData['payment']['total'][$age]['pcPrice'] = $totalPcPrice[$age];
            $getData['payment']['total'][$age]['mobilePrice'] = $totalMobilePrice[$age];
            $getData['payment']['total'][$age]['writePrice'] = $totalWritePrice[$age];
            $getData['payment']['total'][$age]['settlePrice'] = $totalSettlePrice[$age];
        }

        return $getData;
    }

    /**
     * 매출분석 > 지역별 분석
     *
     * @param array  $searchData 검색데이터
     * @param string $groupType  탭분류
     *
     * @return array 지역별 분석 정보
     * @throws AlertBackException
     */
    public function getStatisticsSalesArea($searchData, $groupType = 'day')
    {
        // 초기화
        $getData = [];

        // 요청 들어온 검색 데이터 초기화
        $this->search['periodFl'] = gd_isset($searchData['periodFl'], $groupType == 'month' ? 30 : 7);
        $this->search['treatDate'][0] = gd_isset($searchData['treatDate'][0], date('Y-m-d', strtotime('-' . $searchData['periodFl'] . ' day')));
        $this->search['treatDate'][1] = gd_isset($searchData['treatDate'][1], date('Y-m-d'));

        // 최대 3개월 넘지 않도록 처리
        $intervalDay = DateTimeUtils::intervalDay($searchData['treatDate'][0], $searchData['treatDate'][1]);
        if ($groupType == 'month') {
            $maxSearchDate = 360;
            $maxMessage = __('최대 12개월까지 조회할 수 있습니다.');
        } else {
            $maxSearchDate = 90;
            $maxMessage = __('최대 3개월까지 조회할 수 있습니다.');
        }
        if ($intervalDay > $maxSearchDate) {
            throw new AlertBackException($maxMessage);
        }
        $this->search['interval'] = $intervalDay;
        $getData['search'] = gd_htmlspecialchars($this->search);

        // UI 체크박스 설정
        $this->checked['periodFl'][$searchData['periodFl']] = 'active';
        $getData['checked'] = gd_htmlspecialchars($this->checked);

        // 공통 처리일자 검색
        $arrBind = [];
        if (isset($this->search['periodFl']) && $this->search['treatDate'][0] && $this->search['treatDate'][1]) {
            $arrWhere[] = 'og.paymentDt BETWEEN ? AND ?';

            // 타입이 월별인 경우 1일과 마지막 일자로 변경해야 한다.
            if ($groupType == 'month') {
                $this->search['treatDate'][0] = date('Y-m-01', strtotime($this->search['treatDate'][0]));
                $this->search['treatDate'][1] = date('Y-m-t', strtotime($this->search['treatDate'][1]));
            }
            $this->db->bind_param_push($arrBind, 's', $this->search['treatDate'][0] . ' 00:00:00');
            $this->db->bind_param_push($arrBind, 's', $this->search['treatDate'][1] . ' 23:59:59');
        }

        // 공통 허용 주문상태 설정
        $arrWhere[] = 'LEFT(og.orderStatus, 1) IN (\'' . implode('\', \'', $this->statusStatisticsCd02) . '\')';

        // 공급사 접속시
        if (Manager::isProvider()) {
            $arrWhere[] = 'og.scmNo = ?';
            $this->db->bind_param_push($arrBind, 'i', \Session::get('manager.scmNo'));
        }

        // 쿼리 그룹설정
        switch ($groupType) {
            case 'day':
                $tmpGroup = 'DATE_FORMAT(og.paymentDt, \'%Y-%m-%d\')';
                break;

            case 'month':
                $tmpGroup = 'DATE_FORMAT(og.paymentDt, \'%Y-%m\')';
                break;

            case 'hour':
                $tmpGroup = 'DATE_FORMAT(og.paymentDt, \'%H\')';
                break;

            case 'week':
                $tmpGroup = 'DATE_FORMAT(og.paymentDt, \'%w\')';
                break;
        }

        // 쿼리 필드
        $tmpFieldItem = [
            $tmpGroup . ' AS paymentDt',
            'IF(INSTR(oi.receiverAddress, \' \') = 5, LEFT(oi.receiverAddress, 4), LEFT(oi.receiverAddress, 2)) AS area',
            'SUM(IF(o.orderTypeFl=\'pc\', ((og.goodsPrice + og.optionPrice + og.optionTextPrice) * og.goodsCnt) - (og.goodsDcPrice + og.memberDcPrice + og.memberOverlapDcPrice + og.couponGoodsDcPrice + og.divisionCouponOrderDcPrice), 0)) AS pcPrice',
            'SUM(IF(o.orderTypeFl=\'pc\' AND og.orderStatus=\'r3\' AND oh.handleCompleteFl = \'y\' AND oh.handleMode = \'r\', ((og.goodsPrice + og.optionPrice + og.optionTextPrice) * og.goodsCnt) - (og.goodsDcPrice + og.memberDcPrice + og.memberOverlapDcPrice + og.couponGoodsDcPrice + og.divisionCouponOrderDcPrice), 0)) AS pcRefundPrice',
            'SUM(IF(o.orderTypeFl=\'mobile\', ((og.goodsPrice + og.optionPrice + og.optionTextPrice) * og.goodsCnt) - (og.goodsDcPrice + og.memberDcPrice + og.memberOverlapDcPrice + og.couponGoodsDcPrice + og.divisionCouponOrderDcPrice), 0)) AS mobilePrice',
            'SUM(IF(o.orderTypeFl=\'mobile\' AND og.orderStatus=\'r3\' AND oh.handleCompleteFl = \'y\' AND oh.handleMode = \'r\', ((og.goodsPrice + og.optionPrice + og.optionTextPrice) * og.goodsCnt) - (og.goodsDcPrice + og.memberDcPrice + og.memberOverlapDcPrice + og.couponGoodsDcPrice + og.divisionCouponOrderDcPrice), 0)) AS mobileRefundPrice',
            'SUM(IF(o.orderTypeFl=\'write\', ((og.goodsPrice + og.optionPrice + og.optionTextPrice) * og.goodsCnt) - (og.goodsDcPrice + og.memberDcPrice + og.memberOverlapDcPrice + og.couponGoodsDcPrice + og.divisionCouponOrderDcPrice), 0)) AS writePrice',
            'SUM(IF(o.orderTypeFl=\'write\' AND og.orderStatus=\'r3\' AND oh.handleCompleteFl = \'y\' AND oh.handleMode = \'r\', ((og.goodsPrice + og.optionPrice + og.optionTextPrice) * og.goodsCnt) - (og.goodsDcPrice + og.memberDcPrice + og.memberOverlapDcPrice + og.couponGoodsDcPrice + og.divisionCouponOrderDcPrice), 0)) AS writeRefundPrice',
        ];

        $this->db->strField = implode(', ', $tmpFieldItem);
        $this->db->strJoin = 'LEFT JOIN ' . DB_ORDER . ' o ON o.orderNo = og.orderNo LEFT JOIN ' . DB_ORDER_HANDLE . ' oh ON og.handleSno = oh.sno JOIN ' . DB_ORDER_INFO . ' AS oi ON oi.orderNo = o.orderNo AND oi.orderInfoCd = 1';
        $this->db->strWhere = implode(' AND ', $arrWhere);
        $this->db->strOrder = 'paymentDt ASC';
        $this->db->strGroup = 'paymentDt, area';

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_GOODS . ' og ' . implode(' ', $query);
        $orderData = $this->db->query_fetch($strSQL, $arrBind);

        // 추가주문상품 추출
        $tmpFieldItem = [
            $tmpGroup . ' AS paymentDt',
            'IF(INSTR(oi.receiverAddress, \' \') = 5, LEFT(oi.receiverAddress, 4), LEFT(oi.receiverAddress, 2)) AS area',
            'SUM(IF(o.orderTypeFl=\'pc\', (oa.goodsPrice * oa.goodsCnt) - (oa.addMemberDcPrice + oa.addMemberOverlapDcPrice + oa.addCouponGoodsDcPrice + oa.divisionAddCouponOrderDcPrice), 0)) AS pcPrice',
            'SUM(IF(o.orderTypeFl=\'pc\' AND og.orderStatus=\'r3\' AND oh.handleCompleteFl = \'y\' AND oh.handleMode = \'r\', (oa.goodsPrice * oa.goodsCnt) - (oa.addMemberDcPrice + oa.addMemberOverlapDcPrice + oa.addCouponGoodsDcPrice + oa.divisionAddCouponOrderDcPrice), 0)) AS pcRefundPrice',
            'SUM(IF(o.orderTypeFl=\'mobile\', (oa.goodsPrice * oa.goodsCnt) - (oa.addMemberDcPrice + oa.addMemberOverlapDcPrice + oa.addCouponGoodsDcPrice + oa.divisionAddCouponOrderDcPrice), 0)) AS mobilePrice',
            'SUM(IF(o.orderTypeFl=\'mobile\' AND og.orderStatus=\'r3\' AND oh.handleCompleteFl = \'y\' AND oh.handleMode = \'r\', (oa.goodsPrice * oa.goodsCnt) - (oa.addMemberDcPrice + oa.addMemberOverlapDcPrice + oa.addCouponGoodsDcPrice + oa.divisionAddCouponOrderDcPrice), 0)) AS mobileRefundPrice',
            'SUM(IF(o.orderTypeFl=\'write\', (oa.goodsPrice * oa.goodsCnt) - (oa.addMemberDcPrice + oa.addMemberOverlapDcPrice + oa.addCouponGoodsDcPrice + oa.divisionAddCouponOrderDcPrice), 0)) AS writePrice',
            'SUM(IF(o.orderTypeFl=\'write\' AND og.orderStatus=\'r3\' AND oh.handleCompleteFl = \'y\' AND oh.handleMode = \'r\', (oa.goodsPrice * oa.goodsCnt) - (oa.addMemberDcPrice + oa.addMemberOverlapDcPrice + oa.addCouponGoodsDcPrice + oa.divisionAddCouponOrderDcPrice), 0)) AS writeRefundPrice',
        ];

        // 회원/비회원 분류
        if ($groupType == 'member') {
            $tmpFieldItem[] = 'IF(o.memNo=0, 0, 1) AS category';
        } elseif ($groupType == 'tax') {
            $tmpFieldItem[] = 'IF(LEFT(oa.goodsTaxInfo, 1)=\'f\', 0, 1) AS category';
        }

        $this->db->strJoin = 'LEFT JOIN ' . DB_ORDER_GOODS . ' og ON og.orderNo = oa.orderNo AND og.orderCd = oa.orderCd LEFT JOIN ' . DB_ORDER_HANDLE . ' oh ON og.handleSno = oh.sno LEFT JOIN ' . DB_ORDER . ' o ON og.orderNo = o.orderNo LEFT OUTER JOIN ' . DB_MEMBER . ' AS m ON o.memNo = m.memNo JOIN ' . DB_ORDER_INFO . ' AS oi ON oi.orderNo = o.orderNo AND oi.orderInfoCd = 1';
        $this->db->strField = implode(', ', $tmpFieldItem);
        $this->db->strWhere = implode(' AND ', $arrWhere);
        $this->db->strOrder = 'paymentDt ASC';
        $this->db->strGroup = 'paymentDt, area';
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_ADD_GOODS . ' oa ' . implode(' ', $query);
        $addGoodsData = $this->db->query_fetch($strSQL, $arrBind);

        // 주문배송비 추출
        $tmpFieldItem = [
            $tmpGroup . ' AS paymentDt',
            'IF(INSTR(oi.receiverAddress, \' \') = 5, LEFT(oi.receiverAddress, 4), LEFT(oi.receiverAddress, 2)) AS area',
            'IF(o.orderTypeFl=\'pc\', od.deliveryCharge - od.divisionDeliveryCharge, 0) AS pcPrice',
            'IF(o.orderTypeFl=\'pc\' AND og.orderStatus=\'r3\' AND oh.handleCompleteFl = \'y\' AND oh.handleMode = \'r\', od.deliveryCharge - od.divisionDeliveryCharge, 0) AS pcRefundPrice',
            'IF(o.orderTypeFl=\'mobile\', od.deliveryCharge - od.divisionDeliveryCharge, 0) AS mobilePrice',
            'IF(o.orderTypeFl=\'mobile\' AND og.orderStatus=\'r3\' AND oh.handleCompleteFl = \'y\' AND oh.handleMode = \'r\', od.deliveryCharge - od.divisionDeliveryCharge, 0) AS mobileRefundPrice',
            'IF(o.orderTypeFl=\'write\', od.deliveryCharge - od.divisionDeliveryCharge, 0) AS writePrice',
            'IF(o.orderTypeFl=\'write\' AND og.orderStatus=\'r3\' AND oh.handleCompleteFl = \'y\' AND oh.handleMode = \'r\', od.deliveryCharge - od.divisionDeliveryCharge, 0) AS writeRefundPrice',
        ];

        // 회원/비회원 분류
        if ($groupType == 'member') {
            $tmpFieldItem[] = 'IF(o.memNo=0, 0, 1) AS category';
        } elseif ($groupType == 'tax') {
            $tmpFieldItem[] = 'IF(LEFT(od.deliveryTaxInfo, 1)=\'f\', 0, 1) AS category';
        }

        // 공급사 접속시
        if (Manager::isProvider()) {
            $arrWhere[] = 'od.scmNo = ?';
            $this->db->bind_param_push($arrBind, 'i', \Session::get('manager.scmNo'));
        }

        $this->db->strJoin = 'JOIN ' . DB_ORDER_GOODS . ' og ON og.orderNo = od.orderNo AND og.orderDeliverySno = od.sno LEFT JOIN ' . DB_ORDER_HANDLE . ' oh ON og.handleSno = oh.sno LEFT JOIN ' . DB_ORDER . ' o ON od.orderNo = o.orderNo LEFT OUTER JOIN ' . DB_MEMBER . ' AS m ON o.memNo = m.memNo JOIN ' . DB_ORDER_INFO . ' AS oi ON oi.orderNo = o.orderNo AND oi.orderInfoCd = 1';
        $this->db->strField = implode(', ', $tmpFieldItem);
        $this->db->strWhere = implode(' AND ', $arrWhere);
        $this->db->strOrder = 'paymentDt ASC';
        $this->db->strGroup = 'og.orderDeliverySno, paymentDt, area';

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_DELIVERY . ' od ' . implode(' ', $query);
        $deliveryData = $this->db->query_fetch($strSQL, $arrBind);
        unset($arrWhere, $arrJoin, $arrBind, $tmpFieldItem);

        // 초기화
        $totalPcPrice = [];
        $totalMobilePrice = [];
        $totalWritePrice = [];
        $totalSettlePrice = [];

        // 상품금액
        foreach ($orderData as $val) {
            // 결제완료일
            $paymentDt = substr($val['paymentDt'], 0, 10);

            // 금액 계산
            $pcPrice = $val['pcPrice'] - $val['pcRefundPrice'];
            $mobilePrice = $val['mobilePrice'] - $val['mobileRefundPrice'];
            $writePrice = $val['writePrice'] - $val['writeRefundPrice'];
            $settlePrice = ($pcPrice + $mobilePrice + $writePrice);

            // 반환 데이터 설정
            $getData['payment'][$paymentDt][$val['area']] = [
                'pcPrice'     => $pcPrice,
                'mobilePrice' => $mobilePrice,
                'writePrice'  => $writePrice,
                'settlePrice' => $settlePrice,
            ];

            $totalPcPrice[$val['area']] += $pcPrice;
            $totalMobilePrice[$val['area']] += $mobilePrice;
            $totalWritePrice[$val['area']] += $writePrice;
            $totalSettlePrice[$val['area']] += $settlePrice;
        }

        // 추가상품 금액 계산
        foreach ($addGoodsData as $aVal) {
            $pcPrice = $aVal['pcPrice'] - $aVal['pcRefundPrice'];
            $mobilePrice = $aVal['mobilePrice'] - $aVal['mobileRefundPrice'];
            $writePrice = $aVal['writePrice'] - $aVal['writeRefundPrice'];
            $settlePrice = ($pcPrice + $mobilePrice + $writePrice);

            $totalPcPrice[$aVal['area']] += $pcPrice;
            $totalMobilePrice[$aVal['area']] += $mobilePrice;
            $totalWritePrice[$aVal['area']] += $writePrice;
            $totalSettlePrice[$aVal['area']] += $settlePrice;

            // 주문상품 반환 데이터 설정
            $getData['payment'][$aVal['paymentDt']][$aVal['area']]['pcPrice'] += $pcPrice;
            $getData['payment'][$aVal['paymentDt']][$aVal['area']]['mobilePrice'] += $mobilePrice;
            $getData['payment'][$aVal['paymentDt']][$aVal['area']]['writePrice'] += $writePrice;
            $getData['payment'][$aVal['paymentDt']][$aVal['area']]['settlePrice'] += $settlePrice;
        }

        // 배송비 금액 계산
        if (empty($deliveryData) === false) {
            foreach ($deliveryData as $dVal) {
                $pcPrice = $dVal['pcPrice'] - $dVal['pcRefundPrice'];
                $mobilePrice = $dVal['mobilePrice'] - $dVal['mobileRefundPrice'];
                $writePrice = $dVal['writePrice'] - $dVal['writeRefundPrice'];
                $settlePrice = ($pcPrice + $mobilePrice + $writePrice);

                $totalPcPrice[$dVal['area']] += $pcPrice;
                $totalMobilePrice[$dVal['area']] += $mobilePrice;
                $totalWritePrice[$dVal['area']] += $writePrice;
                $totalSettlePrice[$dVal['area']] += $settlePrice;

                // 주문상품 반환 데이터 설정
                $getData['payment'][$dVal['paymentDt']][$dVal['area']]['pcPrice'] += $pcPrice;
                $getData['payment'][$dVal['paymentDt']][$dVal['area']]['mobilePrice'] += $mobilePrice;
                $getData['payment'][$dVal['paymentDt']][$dVal['area']]['writePrice'] += $writePrice;
                $getData['payment'][$dVal['paymentDt']][$dVal['area']]['settlePrice'] += $settlePrice;
            }
        }

        // 나이별 총합계 산출
        foreach ($this->areas as $area) {
            $getData['payment']['total'][$area]['pcPrice'] = $totalPcPrice[$area];
            $getData['payment']['total'][$area]['mobilePrice'] = $totalMobilePrice[$area];
            $getData['payment']['total'][$area]['writePrice'] = $totalWritePrice[$area];
            $getData['payment']['total'][$area]['settlePrice'] = $totalSettlePrice[$area];
        }

        return $getData;
    }

    /**
     * 매출분석 > 성별분석
     *
     * @return array 성별 분석 정보
     * @deprecated
     */
    public function getStatisticsSalesSex()
    {
        $tmpField = DBTableField::setTableField('tableOrder', null, null, 'o');

        // 기본값 설정
        $this->search['year'] = Request::get()->get('year', date('Y'));
        $this->search['month'] = sprintf('%02d', Request::get()->get('month', date('m')));

        // 검색 일자
        $strDate = $this->search['year'] . $this->search['month'];

        // 쿼리문 생성 및 데이타 호출
        $this->db->strField = 'SUM(o.settlePrice) AS sumSettlePrice, COUNT(*) AS settleCnt, m.sexFl, date_format(o.paymentDt, \'%Y-%m-%d\') as paymentDt';
        $this->db->strJoin = DB_ORDER_GOODS . ' AS og LEFT JOIN ' . DB_ORDER . ' AS o ON o.orderNo = og.orderNo LEFT OUTER JOIN ' . DB_MEMBER . ' AS m ON o.memNo = m.memNo';
        $this->db->strOrder = 'o.paymentDt ASC';
        $this->db->strWhere = 'date_format(o.paymentDt, \'%Y%m\') = \'' . $strDate . '\'
                AND LEFT(og.orderStatus, 1) IN (\'' . implode('\', \'', $this->statusStatisticsCd02) . '\')';
        $this->db->strGroup = 'm.sexFl, o.paymentDt';

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . array_shift($query) . ' ' . implode(' ', $query);
        $result = $this->db->query($strSQL);

        $getData = [];
        while ($data = $this->db->fetch($result)) {
            // 결제 건수 및 결제 금액
            $getData['payment'][$data['paymentDt']][$data['sexFl']]['cnt'] = gd_isset($getData['payment'][$data['paymentDt']][$data['sexFl']]['cnt']) + $data['settleCnt'];
            $getData['payment'][$data['paymentDt']][$data['sexFl']]['settlePrice'] = gd_isset($getData['payment'][$data['paymentDt']][$data['sexFl']]['settlePrice']) + $data['sumSettlePrice'];

            $getData['payment'][$data['sexFl']]['totalCnt'] = gd_isset($getData['payment'][$data['sexFl']]['totalCnt']) + $data['settleCnt'];
            $getData['payment'][$data['sexFl']]['totalSettlePrice'] = gd_isset($getData['payment'][$data['sexFl']]['totalSettlePrice']) + $data['sumSettlePrice'];
        }

        $getData['last'] = date('t', strtotime($strDate . '01'));
        $getData['search'] = gd_htmlspecialchars($this->search);

        return $getData;
    }

    /**
     * 주문분석 > 주문통계
     *
     * @param array  $searchData 검색데이터
     * @param string $groupType  탭구분
     *
     * @return array 매출 통계 정보
     * @throws AlertBackException
     */
    public function getStatisticsOrder($searchData, $groupType = 'day', $useScmFl = false)
    {
        // 요청 들어온 검색 데이터 초기화
        $this->search['periodFl'] = gd_isset($searchData['periodFl'], 7);
        $this->search['treatDate'][0] = gd_isset($searchData['treatDate'][0], $searchData['periodFl'] == -1 ? $searchData['treatDate'][0] : date('Y-m-d', strtotime('-' . $searchData['periodFl'] . ' day')));
        $this->search['treatDate'][1] = gd_isset($searchData['treatDate'][1], $searchData['periodFl'] == -1 ? $searchData['treatDate'][1] : date('Y-m-d'));

        // 공급사
        if ($useScmFl) {
            $this->search['scmFl'] = gd_isset($searchData['scmFl'], '0');
            $this->search['scmNo'] = gd_isset($searchData['scmNo'], DEFAULT_CODE_SCMNO);
            $this->search['scmNoNm'] = gd_isset($searchData['scmNoNm']);
        }

        // 최대 3개월 넘지 않도록 처리
        $intervalDay = DateTimeUtils::intervalDay($searchData['treatDate'][0], $searchData['treatDate'][1]);
        if ($groupType == 'month') {
            $maxSearchDate = 360;
            $maxMessage = __('최대 12개월까지 조회할 수 있습니다.');
        } else {
            $maxSearchDate = 90;
            $maxMessage = __('최대 3개월까지 조회할 수 있습니다.');
        }

        if ($intervalDay > $maxSearchDate) {
            throw new AlertBackException($maxMessage);
        }

        $this->search['interval'] = $intervalDay;
        $getData['search'] = gd_htmlspecialchars($this->search);

        // UI 체크박스 설정
        $this->checked['scmFl'][$this->search['scmFl']] = 'checked="checked"';
        $this->checked['periodFl'][$searchData['periodFl']] = 'active';
        $getData['checked'] = gd_htmlspecialchars($this->checked);

        // 주문유형별 데이터 수집
        $arrOrderTypeFl = [
            'pc',
            'mobile',
            'write',
        ];
        foreach ($arrOrderTypeFl as $orderTypeFl) {
            if ($groupType == 'goods') {
                $order = $this->getStatisticsOrderGoodsData($orderTypeFl, $groupType, $useScmFl);
                foreach ($order as $key => $val) {
                    // 상품명 설정
                    if (!empty($val['goodsNm'])) {
                        $getData['payment'][$key]['name'] = $val['goodsNm'];
                    }

                    // 통계정보 설정
                    if ($key == 'total') {
                        $getData['total'][$orderTypeFl] = $val;
                    } else {
                        unset($val['goodsNm']);
                        $getData['payment'][$key][$orderTypeFl] = $val;
                    }
                }
            } else {
                $order = $this->getStaticsOrderData($orderTypeFl, $groupType, $useScmFl);
                $getData['payment'][$orderTypeFl] = $order;
            }
        }

        return $getData;
    }

    /**
     * 주문분석 > 주문통계
     *
     * @param string  $typeFl    pc|mobile|write
     * @param string  $groupType day|month|hour|member|tax|week
     * @param boolean $useScmFl
     *
     * @return array 쿼리 데이터
     *
     * @author   Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public function getStaticsOrderData($typeFl, $groupType, $useScmFl = false)
    {
        // 공통 처리일자 검색
        $arrBind = [];
        if (isset($this->search['periodFl']) && $this->search['treatDate'][0] && $this->search['treatDate'][1]) {
            $arrWhere[] = 'og.paymentDt BETWEEN ? AND ?';

            // 타입이 월별인 경우 1일과 마지막 일자로 변경해야 한다.
            if ($groupType == 'month') {
                $this->search['treatDate'][0] = date('Y-m-01', strtotime($this->search['treatDate'][0]));
                $this->search['treatDate'][1] = date('Y-m-t', strtotime($this->search['treatDate'][1]));
            }
            $this->db->bind_param_push($arrBind, 's', $this->search['treatDate'][0] . ' 00:00:00');
            $this->db->bind_param_push($arrBind, 's', $this->search['treatDate'][1] . ' 23:59:59');
        }

        // 공통 허용 주문상태 설정
        $arrWhere[] = 'LEFT(og.orderStatus, 1) IN (\'' . implode('\', \'', $this->statusStatisticsCd02) . '\')';

        // 공급사 선택
        // 공급사 접속시
        if (Manager::isProvider()) {
            $arrWhere[] = 'og.scmNo = ?';
            $this->db->bind_param_push($arrBind, 'i', \Session::get('manager.scmNo'));
        } else {
            if ($useScmFl) {
                if ($this->search['scmFl'] == '1') {
                    if (is_array($this->search['scmNo'])) {
                        foreach ($this->search['scmNo'] as $val) {
                            $tmpWhere[] = 'og.scmNo = ?';
                            $this->db->bind_param_push($arrBind, 's', $val);
                        }
                        $arrWhere[] = '(' . implode(' OR ', $tmpWhere) . ')';
                        unset($tmpWhere);
                    } else if ($this->search['scmNo'] > 1) {
                        $arrWhere[] = 'og.scmNo = ?';
                        $this->db->bind_param_push($arrBind, 'i', $this->search['scmNo']);
                    }
                } elseif ($this->search['scmFl'] == '0') {
                    $arrWhere[] = 'og.scmNo = ' . DEFAULT_CODE_SCMNO;
                }
            }
        }

        // 쿼리 그룹설정
        $groupTypeFl = false;
        $tmpOtherGroup = '';
        switch ($groupType) {
            case 'day':
                $tmpGroup = 'DATE_FORMAT(og.paymentDt, \'%Y-%m-%d\')';
                break;

            case 'month':
                $tmpGroup = 'DATE_FORMAT(og.paymentDt, \'%Y-%m\')';
                break;

            case 'hour':
                $tmpGroup = 'DATE_FORMAT(og.paymentDt, \'%H\')';
                break;

            case 'week':
                $tmpGroup = 'DATE_FORMAT(og.paymentDt, \'%w\')';
                break;

            case 'member':
                $groupTypeFl = true;
                $tmpGroup = 'DATE_FORMAT(og.paymentDt, \'%Y-%m-%d\')';
                $tmpOtherGroup = ', category';
                break;
        }

        // 유형 선택 (이로 인해서 PC/모바일/수기의 회원 건수가 별도로 합산되어 처리)
        $arrWhere[] = 'o.orderTypeFl = ?';
        $this->db->bind_param_push($arrBind, 's', $typeFl);

        // 구매자수 추출
        $tmpFieldItem = [
            $tmpGroup . ' AS paymentDt',
            'IF(o.memNo > 0, COUNT(DISTINCT o.memNo), COUNT(o.memNo)) AS memberCnt',
            'COUNT(DISTINCT og.orderNo) AS orderCnt',
            'SUM(og.goodsCnt + og.addGoodsCnt) AS goodsCnt',
            'SUM(((og.goodsPrice + og.optionPrice + og.optionTextPrice) * og.goodsCnt) + og.addGoodsPrice) AS goodsPrice',
        ];

        // 회원/비회원 분류
        if ($groupType == 'member') {
            $tmpFieldItem[] = 'IF(o.memNo=0, 0, 1) AS category';
        } elseif ($groupType == 'goods') {
            $tmpFieldItem[] = 'IF(LEFT(og.goodsTaxInfo, 1)=\'f\', 0, 1) AS category';
        }

        $this->db->strJoin = 'LEFT JOIN ' . DB_ORDER_HANDLE . ' oh ON og.handleSno = oh.sno LEFT JOIN ' . DB_ORDER . ' o ON og.orderNo = o.orderNo';
        $this->db->strField = implode(', ', $tmpFieldItem);
        $this->db->strOrder = 'paymentDt ASC';
        $this->db->strGroup = $tmpGroup . $tmpOtherGroup;
        $this->db->strWhere = implode(' AND ', $arrWhere);

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_GOODS . ' og ' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $arrBind);
        unset($arrWhere, $arrJoin, $arrBind, $tmpFieldItem);

        $getData = [];
        if ($groupTypeFl) {
            $totalMemberCount = [];
            $totalNoMemberCount = [];
            $totalOrderCount = [];
            $totalGoodsCount = [];
            $totalGoodsPrice = [];
        } else {
            $totalMemberCount = 0;
            $totalNoMemberCount = 0;
            $totalOrderCount = 0;
            $totalGoodsCount = 0;
            $totalGoodsPrice = 0;
        }

        // 상품금액
        foreach ($data as $val) {
            // 결제완료일
            $paymentDt = substr($val['paymentDt'], 0, 10);

            // 금액 계산
            $memberCnt = $val['memberCnt'];
            $noMemberCnt = $val['noMemberCnt'];
            $orderCnt = $val['orderCnt'];
            $goodsCnt = $val['goodsCnt'];
            $goodsPrice = $val['goodsPrice'];

            // 반환 데이터 설정
            if ($groupTypeFl) {
                $getData[$paymentDt][$val['category']] = [
                    'memberCnt'   => $memberCnt,
                    'noMemberCnt' => $noMemberCnt,
                    'orderCnt'    => $orderCnt,
                    'goodsCnt'    => $goodsCnt,
                    'goodsPrice'  => $goodsPrice,
                ];

                $totalMemberCount[$val['category']] += $memberCnt;
                $totalNoMemberCount[$val['category']] += $noMemberCnt;
                $totalOrderCount[$val['category']] += $orderCnt;
                $totalGoodsCount[$val['category']] += $goodsCnt;
                $totalGoodsPrice[$val['category']] += $goodsPrice;
            } else {
                $getData[$paymentDt] = [
                    'memberCnt'   => $memberCnt,
                    'noMemberCnt' => $noMemberCnt,
                    'orderCnt'    => $orderCnt,
                    'goodsCnt'    => $goodsCnt,
                    'goodsPrice'  => $goodsPrice,
                ];

                $totalMemberCount += $memberCnt;
                $totalNoMemberCount += $noMemberCnt;
                $totalOrderCount += $orderCnt;
                $totalGoodsCount += $goodsCnt;
                $totalGoodsPrice += $goodsPrice;
            }
        }

        if ($groupTypeFl) {
            foreach ([
                         0,
                         1,
                     ] as $val) {
                $getData['total'][$val]['memberCnt'] = $totalMemberCount[$val];
                $getData['total'][$val]['noMemberCnt'] = $totalNoMemberCount[$val];
                $getData['total'][$val]['orderCnt'] = $totalOrderCount[$val];
                $getData['total'][$val]['goodsCnt'] = $totalGoodsCount[$val];
                $getData['total'][$val]['goodsPrice'] = $totalGoodsPrice[$val];
            }
        } else {
            $getData['total']['memberCnt'] = $totalMemberCount;
            $getData['total']['noMemberCnt'] = $totalNoMemberCount;
            $getData['total']['orderCnt'] = $totalOrderCount;
            $getData['total']['goodsCnt'] = $totalGoodsCount;
            $getData['total']['goodsPrice'] = $totalGoodsPrice;
        }

        return $getData;
    }

    /**
     * 주문분석 > 주문통계 > 상품별 주문현황 데이터 추출
     *
     * @param string  $typeFl pc|mobile|write
     *
     * @param string  $groupType
     * @param boolean $useScmFl
     *
     * @return array 쿼리 데이터
     *
     * @author   Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public function getStatisticsOrderGoodsData($typeFl, $groupType = 'goods', $useScmFl = false)
    {
        // 공통 처리일자 검색
        $arrBind = [];
        if (isset($this->search['periodFl']) && $this->search['treatDate'][0] && $this->search['treatDate'][1]) {
            $arrWhere[] = 'og.paymentDt BETWEEN ? AND ?';
            $this->db->bind_param_push($arrBind, 's', $this->search['treatDate'][0] . ' 00:00:00');
            $this->db->bind_param_push($arrBind, 's', $this->search['treatDate'][1] . ' 23:59:59');
        }

        // 공통 허용 주문상태 설정
        $arrWhere[] = 'LEFT(og.orderStatus, 1) IN (\'' . implode('\', \'', $this->statusStatisticsCd02) . '\')';

        // 공급사 선택
        // 공급사 접속시
        if (Manager::isProvider()) {
            $arrWhere[] = 'og.scmNo = ?';
            $this->db->bind_param_push($arrBind, 'i', \Session::get('manager.scmNo'));
        } else {
            if ($useScmFl) {
                if ($this->search['scmFl'] == '1') {
                    if (is_array($this->search['scmNo'])) {
                        foreach ($this->search['scmNo'] as $val) {
                            $tmpWhere[] = 'og.scmNo = ?';
                            $this->db->bind_param_push($arrBind, 's', $val);
                        }
                        $arrWhere[] = '(' . implode(' OR ', $tmpWhere) . ')';
                        unset($tmpWhere);
                    } else if ($this->search['scmNo'] > 1) {
                        $arrWhere[] = 'og.scmNo = ?';
                        $this->db->bind_param_push($arrBind, 'i', $this->search['scmNo']);
                    }
                } elseif ($this->search['scmFl'] == '0') {
                    $arrWhere[] = 'og.scmNo = ' . DEFAULT_CODE_SCMNO;
                }
            }
        }

        // 유형 선택
        $arrWhere[] = 'o.orderTypeFl = ?';
        $this->db->bind_param_push($arrBind, 's', $typeFl);

        // 추가상품 제외
        $arrWhere[] = 'og.goodsType != \'addGoods\'';

        // 구매자수 추출
        $tmpFieldItem = [
            'g.goodsNm AS goodsNm',
            'g.goodsNo AS goodsNo',
            'IF(o.memNo > 0, COUNT(DISTINCT o.memNo), COUNT(o.memNo)) AS memberCnt',
            'COUNT(DISTINCT og.orderNo) AS orderCnt',
            'SUM(og.goodsCnt + og.addGoodsCnt) AS goodsCnt',
            'SUM(((og.goodsPrice + og.optionPrice + og.optionTextPrice) * og.goodsCnt) + og.addGoodsPrice) AS goodsPrice',
        ];

        $this->db->strJoin = 'LEFT JOIN ' . DB_ORDER_HANDLE . ' oh ON og.handleSno = oh.sno LEFT JOIN ' . DB_ORDER . ' o ON og.orderNo = o.orderNo LEFT JOIN ' . DB_GOODS . ' g ON g.goodsNo = og.goodsNo';
        $this->db->strField = implode(', ', $tmpFieldItem);
        $this->db->strOrder = 'goodsNm ASC';
        $this->db->strGroup = 'g.goodsNo';
        $this->db->strWhere = implode(' AND ', $arrWhere);

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_GOODS . ' og ' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $arrBind);
        unset($arrWhere, $arrJoin, $arrBind, $tmpFieldItem);

        $totalMemberCount = 0;
        $totalOrderCount = 0;
        $totalGoodsCount = 0;
        $totalGoodsPrice = 0;

        // 상품금액
        foreach ($data as $val) {
            // 금액 계산
            $memberCnt = $val['memberCnt'];
            $orderCnt = $val['orderCnt'];
            $goodsCnt = $val['goodsCnt'];
            $goodsPrice = $val['goodsPrice'];

            // 반환 데이터 설정
            $getData[$val['goodsNo']] = [
                'memberCnt'  => $memberCnt,
                'orderCnt'   => $orderCnt,
                'goodsCnt'   => $goodsCnt,
                'goodsPrice' => $goodsPrice,
                'goodsNm'    => strip_tags(html_entity_decode($val['goodsNm'])),
            ];

            $totalMemberCount += $memberCnt;
            $totalOrderCount += $orderCnt;
            $totalGoodsCount += $goodsCnt;
            $totalGoodsPrice += $goodsPrice;
        }

        $getData['total']['memberCnt'] = $totalMemberCount;
        $getData['total']['orderCnt'] = $totalOrderCount;
        $getData['total']['goodsCnt'] = $totalGoodsCount;
        $getData['total']['goodsPrice'] = $totalGoodsPrice;

        return $getData;
    }

    /**
     * 주문분석 > 지역별통계
     *
     * @param array  $searchData 검색데이터
     * @param string $groupType  탭구분
     *
     * @return array 매출 통계 정보
     * @throws AlertBackException
     */
    public function getStatisticsOrderArea($searchData, $groupType = 'day')
    {
        // 요청 들어온 검색 데이터 초기화
        $this->search['periodFl'] = gd_isset($searchData['periodFl'], 7);
        $this->search['treatDate'][0] = gd_isset($searchData['treatDate'][0], $searchData['periodFl'] == -1 ? $searchData['treatDate'][0] : date('Y-m-d', strtotime('-' . $searchData['periodFl'] . ' day')));
        $this->search['treatDate'][1] = gd_isset($searchData['treatDate'][1], $searchData['periodFl'] == -1 ? $searchData['treatDate'][1] : date('Y-m-d'));

        // 최대 3개월 넘지 않도록 처리
        $intervalDay = DateTimeUtils::intervalDay($searchData['treatDate'][0], $searchData['treatDate'][1]);
        if ($groupType == 'month') {
            $maxSearchDate = 360;
            $maxMessage = __('최대 12개월까지 조회할 수 있습니다.');
        } else {
            $maxSearchDate = 90;
            $maxMessage = __('최대 3개월까지 조회할 수 있습니다.');
        }
        if ($intervalDay > $maxSearchDate) {
            throw new AlertBackException($maxMessage);
        }
        $this->search['interval'] = $intervalDay;
        $getData['search'] = gd_htmlspecialchars($this->search);

        // UI 체크박스 설정
        $this->checked['scmFl'][$this->search['scmFl']] = 'checked="checked"';
        $this->checked['periodFl'][$searchData['periodFl']] = 'active';
        $getData['checked'] = gd_htmlspecialchars($this->checked);

        // 주문유형별 데이터 수집
        $arrOrderTypeFl = [
            'pc',
            'mobile',
            'write',
        ];
        foreach ($arrOrderTypeFl as $orderTypeFl) {
            $order = $this->getStatisticsOrderAreaData($orderTypeFl, $groupType);
            $getData['payment'][$orderTypeFl] = $order;
        }

        return $getData;
    }

    /**
     * 주문분석 > 지역별 분석
     *
     * @param string $typeFl    결제채널
     * @param string $groupType 탭분류
     *
     * @return array 지역별 분석 정보
     */
    public function getStatisticsOrderAreaData($typeFl, $groupType = 'day')
    {
        // 공통 처리일자 검색
        $arrBind = [];
        if (isset($this->search['periodFl']) && $this->search['treatDate'][0] && $this->search['treatDate'][1]) {
            $arrWhere[] = 'og.paymentDt BETWEEN ? AND ?';

            // 타입이 월별인 경우 1일과 마지막 일자로 변경해야 한다.
            if ($groupType == 'month') {
                $this->search['treatDate'][0] = date('Y-m-01', strtotime($this->search['treatDate'][0]));
                $this->search['treatDate'][1] = date('Y-m-t', strtotime($this->search['treatDate'][1]));
            }
            $this->db->bind_param_push($arrBind, 's', $this->search['treatDate'][0] . ' 00:00:00');
            $this->db->bind_param_push($arrBind, 's', $this->search['treatDate'][1] . ' 23:59:59');
        }

        // 공통 허용 주문상태 설정
        $arrWhere[] = 'LEFT(og.orderStatus, 1) IN (\'' . implode('\', \'', $this->statusStatisticsCd02) . '\')';

        // 쿼리 그룹설정
        switch ($groupType) {
            case 'day':
                $tmpGroup = 'DATE_FORMAT(og.paymentDt, \'%Y-%m-%d\')';
                break;

            case 'month':
                $tmpGroup = 'DATE_FORMAT(og.paymentDt, \'%Y-%m\')';
                break;

            case 'hour':
                $tmpGroup = 'DATE_FORMAT(og.paymentDt, \'%H\')';
                break;

            case 'week':
                $tmpGroup = 'DATE_FORMAT(og.paymentDt, \'%w\')';
                break;
        }

        // 유형 선택
        $arrWhere[] = 'o.orderTypeFl = ?';
        $this->db->bind_param_push($arrBind, 's', $typeFl);

        // 구매자수 추출
        $tmpFieldItem = [
            $tmpGroup . ' AS paymentDt',
            'IF(INSTR(oi.receiverAddress, \' \') = 5, LEFT(oi.receiverAddress, 4), LEFT(oi.receiverAddress, 2)) AS area',
            //            'COUNT(DISTINCT o.orderIp) AS noMemberCnt',
            'IF(o.memNo > 0, COUNT(DISTINCT o.memNo), COUNT(o.memNo)) AS memberCnt',
            'COUNT(DISTINCT og.orderNo) AS orderCnt',
            'SUM(og.goodsCnt + og.addGoodsCnt) AS goodsCnt',
            'SUM(((og.goodsPrice + og.optionPrice + og.optionTextPrice) * og.goodsCnt) + og.addGoodsPrice) AS goodsPrice',
        ];

        $this->db->strJoin = 'LEFT JOIN ' . DB_ORDER . ' o ON og.orderNo = o.orderNo JOIN ' . DB_ORDER_INFO . ' AS oi ON oi.orderNo = o.orderNo AND oi.orderInfoCd = 1';
        $this->db->strField = implode(', ', $tmpFieldItem);
        $this->db->strOrder = 'paymentDt ASC';
        $this->db->strGroup = $tmpGroup . ', area';
        $this->db->strWhere = implode(' AND ', $arrWhere);

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_GOODS . ' og ' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $arrBind);
        unset($arrWhere, $arrJoin, $arrBind, $tmpFieldItem);

        $getData = [];
        $totalMemberCount = [];
        $totalOrderCount = [];
        $totalGoodsCount = [];
        $totalGoodsPrice = [];

        // 상품금액
        foreach ($data as $val) {
            // 결제완료일
            $paymentDt = substr($val['paymentDt'], 0, 10);

            // 금액 계산
            $memberCnt = $val['memberCnt'];
            $orderCnt = $val['orderCnt'];
            $goodsCnt = $val['goodsCnt'];
            $goodsPrice = $val['goodsPrice'];

            // 반환 데이터 설정
            $getData[$paymentDt][$val['area']] = [
                'memberCnt'  => $memberCnt,
                'orderCnt'   => $orderCnt,
                'goodsCnt'   => $goodsCnt,
                'goodsPrice' => $goodsPrice,
            ];

            $totalMemberCount[$val['area']] += $memberCnt;
            $totalOrderCount[$val['area']] += $orderCnt;
            $totalGoodsCount[$val['area']] += $goodsCnt;
            $totalGoodsPrice[$val['area']] += $goodsPrice;
        }

        foreach ($this->areas as $area) {
            $getData['total'][$area]['memberCnt'] = $totalMemberCount[$area];
            $getData['total'][$area]['orderCnt'] = $totalOrderCount[$area];
            $getData['total'][$area]['goodsCnt'] = $totalGoodsCount[$area];
            $getData['total'][$area]['goodsPrice'] = $totalGoodsPrice[$area];
        }

        return $getData;
    }

    /**
     * 주문분석 > 연령별통계
     *
     * @param array  $searchData 검색데이터
     * @param string $groupType  탭구분
     *
     * @return array 매출 통계 정보
     * @throws AlertBackException
     */
    public function getStatisticsOrderAge($searchData, $groupType = 'day')
    {
        // 요청 들어온 검색 데이터 초기화
        $this->search['periodFl'] = gd_isset($searchData['periodFl'], 7);
        $this->search['treatDate'][0] = gd_isset($searchData['treatDate'][0], $searchData['periodFl'] == -1 ? $searchData['treatDate'][0] : date('Y-m-d', strtotime('-' . $searchData['periodFl'] . ' day')));
        $this->search['treatDate'][1] = gd_isset($searchData['treatDate'][1], $searchData['periodFl'] == -1 ? $searchData['treatDate'][1] : date('Y-m-d'));

        // 최대 3개월 넘지 않도록 처리
        $intervalDay = DateTimeUtils::intervalDay($searchData['treatDate'][0], $searchData['treatDate'][1]);
        if ($groupType == 'month') {
            $maxSearchDate = 360;
            $maxMessage = __('최대 12개월까지 조회할 수 있습니다.');
        } else {
            $maxSearchDate = 90;
            $maxMessage = __('최대 3개월까지 조회할 수 있습니다.');
        }
        if ($intervalDay > $maxSearchDate) {
            throw new AlertBackException($maxMessage);
        }
        $this->search['interval'] = $intervalDay;
        $getData['search'] = gd_htmlspecialchars($this->search);

        // UI 체크박스 설정
        $this->checked['scmFl'][$this->search['scmFl']] = 'checked="checked"';
        $this->checked['periodFl'][$searchData['periodFl']] = 'active';
        $getData['checked'] = gd_htmlspecialchars($this->checked);

        // 주문유형별 데이터 수집
        $arrOrderTypeFl = [
            'pc',
            'mobile',
            'write',
        ];
        foreach ($arrOrderTypeFl as $orderTypeFl) {
            $order = $this->getStatisticsOrderAgeData($orderTypeFl, $groupType);
            $getData['payment'][$orderTypeFl] = $order;
        }

        return $getData;
    }

    /**
     * 주문분석 > 연령별 분석
     *
     * @param string $typeFl    결제채널
     * @param string $groupType 탭분류
     *
     * @return array 지역별 분석 정보
     */
    public function getStatisticsOrderAgeData($typeFl, $groupType = 'day')
    {
        // 공통 처리일자 검색
        $arrBind = [];
        if (isset($this->search['periodFl']) && $this->search['treatDate'][0] && $this->search['treatDate'][1]) {
            $arrWhere[] = 'og.paymentDt BETWEEN ? AND ?';

            // 타입이 월별인 경우 1일과 마지막 일자로 변경해야 한다.
            if ($groupType == 'month') {
                $this->search['treatDate'][0] = date('Y-m-01', strtotime($this->search['treatDate'][0]));
                $this->search['treatDate'][1] = date('Y-m-t', strtotime($this->search['treatDate'][1]));
            }
            $this->db->bind_param_push($arrBind, 's', $this->search['treatDate'][0] . ' 00:00:00');
            $this->db->bind_param_push($arrBind, 's', $this->search['treatDate'][1] . ' 23:59:59');
        }

        // 공통 허용 주문상태 설정
        $arrWhere[] = 'LEFT(og.orderStatus, 1) IN (\'' . implode('\', \'', $this->statusStatisticsCd02) . '\')';

        // 쿼리 그룹설정
        switch ($groupType) {
            case 'day':
                $tmpGroup = 'DATE_FORMAT(og.paymentDt, \'%Y-%m-%d\')';
                break;

            case 'month':
                $tmpGroup = 'DATE_FORMAT(og.paymentDt, \'%Y-%m\')';
                break;

            case 'hour':
                $tmpGroup = 'DATE_FORMAT(og.paymentDt, \'%H\')';
                break;

            case 'week':
                $tmpGroup = 'DATE_FORMAT(og.paymentDt, \'%w\')';
                break;
        }

        // 유형 선택
        $arrWhere[] = 'o.orderTypeFl = ?';
        $this->db->bind_param_push($arrBind, 's', $typeFl);

        // 구매자수 추출
        $tmpFieldItem = [
            $tmpGroup . ' AS paymentDt',
            'IF(m.birthDt=\'0000-00-00\', ' . ArrayUtils::last($this->ages) . ', IFNULL(FLOOR((DATE_FORMAT(NOW(), \'%Y\') - LEFT(m.birthDt, 4)) / 10) * 10, ' . ArrayUtils::last($this->ages) . ')) AS age',
            //            'COUNT(DISTINCT o.orderIp) AS noMemberCnt',
            'IF(o.memNo > 0, COUNT(DISTINCT o.memNo), COUNT(o.memNo)) AS memberCnt',
            'COUNT(DISTINCT og.orderNo) AS orderCnt',
            'SUM(og.goodsCnt + og.addGoodsCnt) AS goodsCnt',
            'SUM(((og.goodsPrice + og.optionPrice + og.optionTextPrice) * og.goodsCnt) + og.addGoodsPrice) AS goodsPrice',
        ];

        $this->db->strJoin = 'LEFT JOIN ' . DB_ORDER . ' o ON og.orderNo = o.orderNo LEFT OUTER JOIN ' . DB_MEMBER . ' AS m ON o.memNo = m.memNo';
        $this->db->strField = implode(', ', $tmpFieldItem);
        $this->db->strOrder = 'paymentDt ASC';
        $this->db->strGroup = $tmpGroup . ', age';
        $this->db->strWhere = implode(' AND ', $arrWhere);

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_GOODS . ' og ' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $arrBind);
        unset($arrWhere, $arrJoin, $arrBind, $tmpFieldItem);

        $getData = [];
        $totalMemberCount = [];
        $totalOrderCount = [];
        $totalGoodsCount = [];
        $totalGoodsPrice = [];

        // 상품금액
        foreach ($data as $val) {
            // 결제완료일
            $paymentDt = substr($val['paymentDt'], 0, 10);

            // 금액 계산
            $memberCnt = $val['memberCnt'];
            $orderCnt = $val['orderCnt'];
            $goodsCnt = $val['goodsCnt'];
            $goodsPrice = $val['goodsPrice'];

            // 반환 데이터 설정
            $getData[$paymentDt][$val['age']] = [
                'memberCnt'  => $memberCnt,
                'orderCnt'   => $orderCnt,
                'goodsCnt'   => $goodsCnt,
                'goodsPrice' => $goodsPrice,
            ];

            $totalMemberCount[$val['age']] += $memberCnt;
            $totalOrderCount[$val['age']] += $orderCnt;
            $totalGoodsCount[$val['age']] += $goodsCnt;
            $totalGoodsPrice[$val['age']] += $goodsPrice;
        }

        foreach ($this->ages as $age) {
            $getData['total'][$age]['memberCnt'] = $totalMemberCount[$age];
            $getData['total'][$age]['orderCnt'] = $totalOrderCount[$age];
            $getData['total'][$age]['goodsCnt'] = $totalGoodsCount[$age];
            $getData['total'][$age]['goodsPrice'] = $totalGoodsPrice[$age];
        }

        return $getData;
    }

    /**
     * 주문분석 > 주문상태별통계
     *
     * @param array  $searchData 검색데이터
     * @param string $groupType  탭구분
     *
     * @return array 매출 통계 정보
     * @throws AlertBackException
     */
    public function getStatisticsOrderStatus($searchData, $groupType = 'day')
    {
        // 요청 들어온 검색 데이터 초기화
        $this->search['periodFl'] = gd_isset($searchData['periodFl'], 7);
        $this->search['treatDate'][0] = gd_isset($searchData['treatDate'][0], $searchData['periodFl'] == -1 ? $searchData['treatDate'][0] : date('Y-m-d', strtotime('-' . $searchData['periodFl'] . ' day')));
        $this->search['treatDate'][1] = gd_isset($searchData['treatDate'][1], $searchData['periodFl'] == -1 ? $searchData['treatDate'][1] : date('Y-m-d'));

        // 최대 3개월 넘지 않도록 처리
        $intervalDay = DateTimeUtils::intervalDay($searchData['treatDate'][0], $searchData['treatDate'][1]);
        if ($groupType == 'month') {
            $maxSearchDate = 360;
            $maxMessage = __('최대 12개월까지 조회할 수 있습니다.');
        } else {
            $maxSearchDate = 90;
            $maxMessage = __('최대 3개월까지 조회할 수 있습니다.');
        }
        if ($intervalDay > $maxSearchDate) {
            throw new AlertBackException($maxMessage);
        }
        $this->search['interval'] = $intervalDay;
        $getData['search'] = gd_htmlspecialchars($this->search);

        // UI 체크박스 설정
        $this->checked['scmFl'][$this->search['scmFl']] = 'checked="checked"';
        $this->checked['periodFl'][$searchData['periodFl']] = 'active';
        $getData['checked'] = gd_htmlspecialchars($this->checked);

        // 주문유형별 데이터 수집
        $arrOrderTypeFl = [
            'pc',
            'mobile',
            'write',
        ];
        foreach ($arrOrderTypeFl as $orderTypeFl) {
            $order = $this->getStatisticsOrderStatusData($orderTypeFl, $groupType);
            $getData['payment'][$orderTypeFl] = $order;
        }

        return $getData;
    }

    /**
     * 주문분석 > 주문상태별 분석
     *
     * @param string $typeFl    결제채널
     * @param string $groupType 탭분류
     *
     * @return array 지역별 분석 정보
     */
    public function getStatisticsOrderStatusData($typeFl, $groupType = 'day')
    {
        // 공통 처리일자 검색
        $arrBind = [];
        if (isset($this->search['periodFl']) && $this->search['treatDate'][0] && $this->search['treatDate'][1]) {
            $arrWhere[] = 'og.paymentDt BETWEEN ? AND ?';

            // 타입이 월별인 경우 1일과 마지막 일자로 변경해야 한다.
            if ($groupType == 'month') {
                $this->search['treatDate'][0] = date('Y-m-01', strtotime($this->search['treatDate'][0]));
                $this->search['treatDate'][1] = date('Y-m-t', strtotime($this->search['treatDate'][1]));
            }
            $this->db->bind_param_push($arrBind, 's', $this->search['treatDate'][0] . ' 00:00:00');
            $this->db->bind_param_push($arrBind, 's', $this->search['treatDate'][1] . ' 23:59:59');
        }

        // 공통 허용 주문상태 설정
        $arrWhere[] = 'LEFT(og.orderStatus, 1) IN (\'' . implode('\', \'', $this->statusStatisticsCd02) . '\')';

        // 쿼리 그룹설정
        switch ($groupType) {
            case 'day':
                $tmpGroup = 'DATE_FORMAT(og.paymentDt, \'%Y-%m-%d\')';
                break;

            case 'month':
                $tmpGroup = 'DATE_FORMAT(og.paymentDt, \'%Y-%m\')';
                break;

            case 'hour':
                $tmpGroup = 'DATE_FORMAT(og.paymentDt, \'%H\')';
                break;

            case 'week':
                $tmpGroup = 'DATE_FORMAT(og.paymentDt, \'%w\')';
                break;
        }

        // 유형 선택
        $arrWhere[] = 'o.orderTypeFl = ?';
        $this->db->bind_param_push($arrBind, 's', $typeFl);

        // 구매자수 추출
        $tmpFieldItem = [
            $tmpGroup . ' AS paymentDt',
            'IF(m.birthDt=\'0000-00-00\', ' . ArrayUtils::last($this->ages) . ', IFNULL(FLOOR((DATE_FORMAT(NOW(), \'%Y\') - LEFT(m.birthDt, 4)) / 10) * 10, ' . ArrayUtils::last($this->ages) . ')) AS age',
            //            'COUNT(DISTINCT o.orderIp) AS noMemberCnt',
            'IF(o.memNo > 0, COUNT(DISTINCT o.memNo), COUNT(o.memNo)) AS memberCnt',
            'COUNT(DISTINCT og.orderNo) AS orderCnt',
            'SUM(og.goodsCnt + og.addGoodsCnt) AS goodsCnt',
            'SUM(((og.goodsPrice + og.optionPrice + og.optionTextPrice) * og.goodsCnt) + og.addGoodsPrice) AS goodsPrice',
        ];

        $this->db->strJoin = 'LEFT JOIN ' . DB_ORDER . ' o ON og.orderNo = o.orderNo LEFT OUTER JOIN ' . DB_MEMBER . ' AS m ON o.memNo = m.memNo';
        $this->db->strField = implode(', ', $tmpFieldItem);
        $this->db->strOrder = 'paymentDt ASC';
        $this->db->strGroup = $tmpGroup . ', age';
        $this->db->strWhere = implode(' AND ', $arrWhere);

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_GOODS . ' og ' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $arrBind);
        unset($arrWhere, $arrJoin, $arrBind, $tmpFieldItem);

        $getData = [];
        $totalMemberCount = [];
        $totalOrderCount = [];
        $totalGoodsCount = [];
        $totalGoodsPrice = [];

        // 상품금액
        foreach ($data as $val) {
            // 결제완료일
            $paymentDt = substr($val['paymentDt'], 0, 10);

            // 금액 계산
            $memberCnt = $val['memberCnt'];
            $orderCnt = $val['orderCnt'];
            $goodsCnt = $val['goodsCnt'];
            $goodsPrice = $val['goodsPrice'];

            // 반환 데이터 설정
            $getData[$paymentDt][$val['age']] = [
                'memberCnt'  => $memberCnt,
                'orderCnt'   => $orderCnt,
                'goodsCnt'   => $goodsCnt,
                'goodsPrice' => $goodsPrice,
            ];

            $totalMemberCount[$val['age']] += $memberCnt;
            $totalOrderCount[$val['age']] += $orderCnt;
            $totalGoodsCount[$val['age']] += $goodsCnt;
            $totalGoodsPrice[$val['age']] += $goodsPrice;
        }

        foreach ($this->ages as $age) {
            $getData['total'][$age]['memberCnt'] = $totalMemberCount[$age];
            $getData['total'][$age]['orderCnt'] = $totalOrderCount[$age];
            $getData['total'][$age]['goodsCnt'] = $totalGoodsCount[$age];
            $getData['total'][$age]['goodsPrice'] = $totalGoodsPrice[$age];
        }

        return $getData;
    }

    /**
     * 관리자 메인 상단 주문현황 조회 함수
     *
     * @param integer $period      검색 기간
     * @param array   $orderStatus 설정에 저장된 주문상태
     *
     * @return array
     */
    public function getOrderPresentation($period, array $orderStatus, $orderCountFl = 'goods')
    {
        $result = [];
        if (empty($orderStatus)) {
            return $result;
        }

        $eachOrderStatus = $this->getEachOrderStatusAdmin($orderStatus, $period, null,$orderCountFl);
        foreach ($eachOrderStatus as $status => $val) {
            if (in_array($status, $orderStatus)) {
                if ($this->notAllowOrderStatusByProvider($status)) {
                    continue;
                }
                // 이미지 출력을 위한 클래스 설정 (사용자 정의 상태의 경우 이미지를 1번 이미지로 강제 대체)
                $val['imageClass'] = $status;
                $val['codeStep'] = substr($status, -1);
                if ($status == 'b2') {
                    $val['name'] = __('반품 반송중');
                } else if ($status == 'e2') {
                    $val['name'] = __('교환 반송중');
                }

                // 검색 조건 설정
                $queryString = '?treatDate[]=' . date('Y-m-d', strtotime('-' . $period . ' day')) . '&treatDate[]=' . date('Y-m-d');
                if ($status == 'er') {
                    $queryString .= '&view=exchange&detailSearch=y&searchFl=y&orderStatus[]=e&treatDateFl=ouh.regDt';
                } else if ($status == 'br') {
                    $queryString .= '&view=back&detailSearch=y&searchFl=y&orderStatus[]=b&treatDateFl=ouh.regDt';
                } else if ($status == 'rr') {
                    $queryString .= '&view=refund&detailSearch=y&searchFl=y&orderStatus[]=r&treatDateFl=ouh.regDt';
                } else {
                    if($orderCountFl == 'order'){
                        if (in_array(substr($status,0, 1), ['','o'])) {
                            $view = 'order';
                        } elseif (in_array(substr($status,0, 1), ['p','g','d','s'])) {
                            $view = 'orderGoodsSimple';
                        }
                    }else{
                        $view = 'orderGoods';
                    }
                    $queryString .= '&view='.$view.'&detailSearch=y&searchFl=y&orderStatus[]=' . $status;
                }

                $val = $this->initLinkByOrderStatus($status, $val);

                // 링크조함
                $val['link'] .= $queryString;
                $result[] = $val;
            }
        }

        return $result;
    }

    /**
     * 관리자 메인 주문관리, 메인 상단 주문현황의 주문상태별 링크 설정 함수
     *
     * @param $status
     * @param $val
     *
     * @return mixed
     */
    public function initLinkByOrderStatus($status, $val)
    {
        $prefix = '/order/';
        if (Manager::isProvider()) {
            $prefix = '/provider/order/';
        }
        // 링크설정
        switch (substr($status, 0, 1)) {
            case 'o':
                $val['link'] = $prefix . 'order_list_order.php';
                if ($val['codeStep'] > 1) {
                    $val['imageClass'] = substr($status, 0, 1) . '1';
                }
                break;
            case 'p':
                $val['link'] = $prefix . 'order_list_pay.php';
                if ($val['codeStep'] > 1) {
                    $val['imageClass'] = substr($status, 0, 1) . '1';
                }
                break;
            case 'g':
                $val['link'] = $prefix . 'order_list_goods.php';
                if ($val['codeStep'] > 4) {
                    $val['imageClass'] = substr($status, 0, 1) . '1';
                }
                break;
            case 'd':
                if ($status == 'd1') {
                    $val['link'] = $prefix . 'order_list_delivery.php';
                } else {
                    $val['link'] = $prefix . 'order_list_delivery_ok.php';
                }
                if ($val['codeStep'] > 2) {
                    $val['imageClass'] = substr($status, 0, 1) . '1';
                }
                break;
            case 's':
                $val['link'] = $prefix . 'order_list_settle.php';
                if ($val['codeStep'] > 1) {
                    $val['imageClass'] = substr($status, 0, 1) . '1';
                }
                break;
            case 'f':
                $val['link'] = $prefix . 'order_list_fail.php';
                if ($val['codeStep'] > 3) {
                    $val['imageClass'] = substr($status, 0, 1) . '1';
                }
                break;
            case 'c':
                $val['link'] = $prefix . 'order_list_cancel.php';
                if ($val['codeStep'] > 4) {
                    $val['imageClass'] = substr($status, 0, 1) . '1';
                }
                break;
            case 'r':
                $val['link'] = $prefix . 'order_list_refund.php';
                if (substr($status, 1, 1) === 'r') {
                    $val['link'] = $prefix . 'order_list_user_exchange.php';
                }
                if ($val['codeStep'] > 3) {
                    $val['imageClass'] = substr($status, 0, 1) . '1';
                }
                break;
            case 'b':
                $val['link'] = $prefix . 'order_list_back.php';
                if (substr($status, 1, 1) === 'r') {
                    $val['link'] = $prefix . 'order_list_user_exchange.php';
                }
                if ($val['codeStep'] > 4) {
                    $val['imageClass'] = substr($status, 0, 1) . '1';
                }
                break;
            case 'e':
                $val['link'] = $prefix . 'order_list_exchange.php';
                if (substr($status, 1, 1) === 'r') {
                    $val['link'] = $prefix . 'order_list_user_exchange.php';
                }
                if ($val['codeStep'] > 5) {
                    $val['imageClass'] = substr($status, 0, 1) . '1';
                }
                break;
            case 'z':
                $val['link'] = $prefix . 'order_list_exchange_add.php';
                if (substr($status, 1, 1) === 'r') {
                    $val['link'] = $prefix . 'order_list_user_exchange.php';
                }
                if ($val['codeStep'] > 5) {
                    $val['imageClass'] = substr($status, 0, 1) . '1';
                }
                break;
        }

        return $val;
    }

    /**
     * 공급사 로그인 경우 특정 주문상태를 필터링 하기 위해 체크하는 함수
     *
     * @param string $orderStatus 체크하려는 주문상태
     *
     * @return bool 체크하려는 주문상태가 공급사에서 허용된 상태인지 체크
     */
    public function notAllowOrderStatusByProvider($orderStatus)
    {
        $notAllowStatus = explode(',', 'o,c');
        $isNotAllow = Manager::isProvider() && in_array(substr($orderStatus, 0, 1), $notAllowStatus);

        return $isNotAllow;
    }

    /**
     * 주문현황 데이터 중 1이상인 데이터가 있는지 체크
     *
     * @param       $period
     * @param array $orderStatus
     *
     * @return bool
     */
    public function checkOrderPresentationCount($period, array $orderStatus)
    {
        $isNew = false;
        $presentation = $this->getOrderPresentation($period, $orderStatus);
        foreach ($presentation as $index => $item) {
            if ($item['count'] > 0) {
                $isNew = true;
            }
        }

        return $isNew;
    }

    // 특정시간대의 주문 카운트
    public function getOrderCountByBetween($sDate, $eDate)
    {
        $newCntQuery = "SELECT count(orderNo)  FROM " . DB_ORDER . " WHERE orderStatus NOT IN ('f1', 'f2', 'f3') AND regDt BETWEEN '" . $sDate . "' AND '" . $eDate . "';";

        if ($this->db->fetch($newCntQuery, 'row')[0]) {
            return $this->db->fetch($newCntQuery, 'row')[0];
        } else {
            return 0;
        }
    }
}
