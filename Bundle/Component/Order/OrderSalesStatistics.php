<?php
/**
 * This is commercial software, only users who have purchased a valid license
 * and accept to the terms of the License Agreement can install and use this
 * program.
 *
 * Do not edit or add to this file if you wish to upgrade Godomall5 to newer
 * versions in the future.
 *
 * @copyright Copyright (c) 2015 GodoSoft.
 * @link http://www.godo.co.kr
 */

namespace Bundle\Component\Order;

use DateTime;
use Framework\Utility\DateTimeUtils;

class OrderSalesStatistics
{
    protected $db;
    protected $orderPolicy;         // 상품통계 기본 설정 ( 주문 결제완료일 )
    protected $orderSalesStatisticsProcess = 'periodic'; // 실시간 OR 주기

    // procedure 파일
    private $procedureName = 'USP__SALES_ANLYTC';
    private $procedureExistFl = false;

    /**
     * GoodsStatistics constructor.
     *
     * @param null $date Y-m-d
     */
    public function __construct($date = null)
    {
        if (!is_object($this->db)) {
            $this->db = \App::load('DB');
        }
        // 주문 결제완료일 - 통계 처리 날짜
        if ($date) {
            $submitDate = new DateTime($date);
            $this->orderPolicy['statisticsDate'] = $submitDate->modify('-1 day');
        } else {
            $submitDate = new DateTime();
            $this->orderPolicy['statisticsDate'] = $submitDate->modify('-1 day');
        }
        // 실시간 처리
        $config = gd_policy('statistics.order');
        if (!$this->isExistOrderSalesStatisticsTuningFile() && gd_isset($config['processSystem']) == 'realTime') {
            $this->orderSalesStatisticsProcess = 'realTime';
        }

        // 실시간 처리 제한 시간
        $this->orderPolicy['realStatisticsHour'] = 2; // 통계가 2시간 이상 전의 시간이면 처리 (주기적)
        $this->orderPolicy['realStatisticsSeconds'] = 30; // 통계가 30초 이상 차이나면 처리 (실시간)
    }

    /**
     * getOrderGoodsInfo
     * 주문 상품 정보 출력
     *
     * @param array $orderGoods paymentDt
     * @param string $orderGoodsField 출력할 필드명 (기본 null)
     * @param array $arrBind bind 처리 배열 (기본 null)
     * @param bool|string $dataArray return 값을 배열처리 (기본값 false)
     *
     * @return array 주문 상품 정보
     *
     * @author su
     */
    public function getOrderGoodsInfo($orderGoods = null, $orderGoodsField = null, $arrBind = null, $dataArray = false)
    {
        if (is_null($arrBind)) {
            // $arrBind = array();
        }

        if (isset($orderGoods['paymentDt'])) {
            $paymentDt = DateTimeUtils::dateFormat('Y-m-d', $orderGoods['paymentDt']);
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND og.paymentDt BETWEEN ? AND ? ';
                $this->db->bind_param_push($arrBind, 's', $paymentDt . ' 00:00:00');
                $this->db->bind_param_push($arrBind, 's', $paymentDt . ' 23:59:59');
            } else {
                $this->db->strWhere = ' og.paymentDt BETWEEN ? AND ? ';
                $this->db->bind_param_push($arrBind, 's', $paymentDt . ' 00:00:00');
                $this->db->bind_param_push($arrBind, 's', $paymentDt . ' 23:59:59');
            }
        }
        if (isset($orderGoods['paymentDtOver'])) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND og.paymentDt >= ? ';
                $this->db->bind_param_push($arrBind, 's', $orderGoods['paymentDtOver']);
            } else {
                $this->db->strWhere = ' og.paymentDt >= ? ';
                $this->db->bind_param_push($arrBind, 's', $orderGoods['paymentDtOver']);
            }
        }
        if (isset($orderGoods['dmChk'])) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND (og.divisionUseDeposit > 0 or og.divisionUseMileage > 0) and og.paymentDt > 0 ';
            } else {
                $this->db->strWhere = ' (og.divisionUseDeposit > 0 or og.divisionUseMileage > 0) and og.paymentDt > 0 ';
            }
        }
        if ($orderGoodsField) {
            $this->db->strField = $orderGoodsField;
        }

        if (isset($orderGoods['dmChk'])) {

        } else {
            $arrJoin[] = 'LEFT JOIN ' . DB_ORDER . ' as o ON og.orderNo = o.orderNo';
            $arrJoin[] = 'LEFT JOIN ' . DB_ORDER_DELIVERY . ' as od ON og.orderDeliverySno = od.sno';
            $arrJoin[] = 'LEFT JOIN ' . DB_ORDER_INFO . ' as oi ON (og.orderNo = oi.orderNo) 
                          AND (CASE WHEN od.orderInfoSno > 0 THEN od.orderInfoSno = oi.sno ELSE oi.orderInfoCd = 1 END)';
            $arrJoin[] = 'LEFT JOIN ' . DB_MEMBER . ' as m ON o.memNo = m.memNo';
            $this->db->strJoin = implode(' ', $arrJoin);
        }

        $this->db->strOrder = 'og.paymentDt asc, og.sno asc';
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_GOODS . ' as og ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);

        return gd_htmlspecialchars_stripslashes($getData);
    }

    /**
     * getOrderDeliveryInfo
     * 주문 배송비 정보 출력
     *
     * @param array $orderDelivery paymentDt
     * @param string $orderDeliveryField 출력할 필드명 (기본 null)
     * @param array $arrBind bind 처리 배열 (기본 null)
     * @param bool|string $dataArray return 값을 배열처리 (기본값 false)
     *
     * @return array 주문 배송비 정보
     *
     * @author su
     */
    public function getOrderDeliveryInfo($orderDelivery = null, $orderDeliveryField = null, $arrBind = null, $dataArray = false)
    {
        if (is_null($arrBind)) {
            // $arrBind = array();
        }

        if (isset($orderDelivery['paymentDt'])) {
            $paymentDt = DateTimeUtils::dateFormat('Y-m-d', $orderDelivery['paymentDt']);
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND og.paymentDt BETWEEN ? AND ? ';
                $this->db->bind_param_push($arrBind, 's', $paymentDt . ' 00:00:00');
                $this->db->bind_param_push($arrBind, 's', $paymentDt . ' 23:59:59');
            } else {
                $this->db->strWhere = ' og.paymentDt BETWEEN ? AND ? ';
                $this->db->bind_param_push($arrBind, 's', $paymentDt . ' 00:00:00');
                $this->db->bind_param_push($arrBind, 's', $paymentDt . ' 23:59:59');
            }
        }
        if (isset($orderDelivery['paymentDtOver'])) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND og.paymentDt >= ? ';
                $this->db->bind_param_push($arrBind, 's', $orderDelivery['paymentDtOver']);
            } else {
                $this->db->strWhere = ' og.paymentDt >= ? ';
                $this->db->bind_param_push($arrBind, 's', $orderDelivery['paymentDtOver']);
            }
        }
        if (isset($orderDelivery['statisticsOrderFl'])) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND od.statisticsOrderFl = ? ';
                $this->db->bind_param_push($arrBind, 's', $orderDelivery['statisticsOrderFl']);
            } else {
                $this->db->strWhere = ' od.statisticsOrderFl = ? ';
                $this->db->bind_param_push($arrBind, 's', $orderDelivery['statisticsOrderFl']);
            }
        }
        if (isset($orderDelivery['dmChk'])) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND (od.divisionDeliveryUseDeposit > 0 or od.divisionDeliveryUseMileage > 0) and og.paymentDt > 0 ';
            } else {
                $this->db->strWhere = ' (od.divisionDeliveryUseDeposit > 0 or od.divisionDeliveryUseMileage > 0) and og.paymentDt > 0 ';
            }

        }
        if ($orderDeliveryField) {
            $this->db->strField = $orderDeliveryField;
        }

        if (isset($orderDelivery['dmChk'])) {
            $arrJoin[] = 'LEFT JOIN ' . DB_ORDER . ' as o ON od.orderNo = o.orderNo';
            $arrJoin[] = 'LEFT JOIN ' . DB_ORDER_GOODS . ' as og ON od.sno = og.orderDeliverySno';
        } else {
            $arrJoin[] = 'LEFT JOIN ' . DB_ORDER . ' as o ON od.orderNo = o.orderNo';
            $arrJoin[] = 'LEFT JOIN ' . DB_ORDER_INFO . ' as oi ON (od.orderNo = oi.orderNo) 
                          AND (CASE WHEN od.orderInfoSno > 0 THEN od.orderInfoSno = oi.sno ELSE oi.orderInfoCd = 1 END)';
            $arrJoin[] = 'LEFT JOIN ' . DB_MEMBER . ' as m ON o.memNo = m.memNo';
            $arrJoin[] = 'LEFT JOIN ' . DB_ORDER_GOODS . ' as og ON od.sno = og.orderDeliverySno';
        }

        $this->db->strOrder = 'og.paymentDt asc, od.sno asc';
        $this->db->strJoin = implode(' ', $arrJoin);
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_DELIVERY . ' as od ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);

        return gd_htmlspecialchars_stripslashes($getData);
    }

    /**
     * getOrderRefundInfo
     * 환불 정보 출력
     *
     * @param array $orderRefund handleDt
     * @param string $orderRefundField 출력할 필드명 (기본 null)
     * @param array $arrBind bind 처리 배열 (기본 null)
     * @param bool|string $dataArray return 값을 배열처리 (기본값 false)
     *
     * @return array 환불 정보
     *
     * @author su
     */
    public function getOrderRefundInfo($orderRefund = null, $orderRefundField = null, $arrBind = null, $dataArray = false)
    {
        if (is_null($arrBind)) {
            // $arrBind = array();
        }

        if (isset($orderRefund['handleDt'])) {
            $handleDt = DateTimeUtils::dateFormat('Y-m-d', $orderRefund['handleDt']);
            $handleDtStart = $handleDt . ' 00:00:00';
            $handleDtEnd = $handleDt . ' 23:59:59';
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND oh.handleDt BETWEEN ? AND ? ';
                $this->db->bind_param_push($arrBind, 's', $handleDtStart);
                $this->db->bind_param_push($arrBind, 's', $handleDtEnd);
            } else {
                $this->db->strWhere = ' oh.handleDt BETWEEN ? AND ? ';
                $this->db->bind_param_push($arrBind, 's', $handleDtStart);
                $this->db->bind_param_push($arrBind, 's', $handleDtEnd);
            }
        }
        if (isset($orderRefund['handleDtOver'])) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND oh.handleDt >= ? ';
                $this->db->bind_param_push($arrBind, 's', $orderRefund['handleDtOver']);
            } else {
                $this->db->strWhere = ' oh.handleDt >= ? ';
                $this->db->bind_param_push($arrBind, 's', $orderRefund['handleDtOver']);
            }
        }
        if (isset($orderRefund['handleMode'])) {
            if (is_array($orderRefund['handleMode'])) {
                $where = 'oh.handleMode IN (';
                foreach ($orderRefund['handleMode'] as $val) {
                    $whereNum[] = '?';
                    $this->db->bind_param_push($arrBind, 's', $val);
                }
                $where .= implode(', ', $whereNum);
                $where .= ')';
                if ($this->db->strWhere) {
                    $this->db->strWhere = $this->db->strWhere . ' AND ' . $where;
                } else {
                    $this->db->strWhere = $where;
                }
            } else {
                if ($this->db->strWhere) {
                    $this->db->strWhere = $this->db->strWhere . ' AND oh.handleMode = ? ';
                    $this->db->bind_param_push($arrBind, 's', $orderRefund['handleMode']);
                } else {
                    $this->db->strWhere = ' oh.handleMode = ? ';
                    $this->db->bind_param_push($arrBind, 's', $orderRefund['handleMode']);
                }
            }
        }
        if (isset($orderRefund['dmChk'])) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND (oh.refundUseDeposit > 0 or oh.refundUseMileage > 0) ';
            } else {
                $this->db->strWhere = ' (oh.refundUseDeposit > 0 or oh.refundUseMileage > 0) ';
            }
        }
        if (isset($orderRefund['orderChannelFl'])) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND o.orderChannelFl = ? ';
                $this->db->bind_param_push($arrBind, 's', $orderRefund['orderChannelFl']);
            } else {
                $this->db->strWhere = ' o.orderChannelFl = ? ';
                $this->db->bind_param_push($arrBind, 's', $orderRefund['orderChannelFl']);
            }
        }
        if ($orderRefundField) {
            $this->db->strField = $orderRefundField;
        }

        if (isset($orderDelivery['dmChk'])) {
            $arrJoin[] = 'LEFT JOIN ' . DB_ORDER . ' as o ON od.orderNo = o.orderNo';
            $arrJoin[] = 'LEFT JOIN ' . DB_ORDER_GOODS . ' as og ON od.sno = og.orderDeliverySno';
        } else {
            $arrJoin[] = 'LEFT JOIN ' . DB_ORDER . ' as o ON oh.orderNo = o.orderNo';
            $arrJoin[] = 'LEFT JOIN ' . DB_MEMBER . ' as m ON o.memNo = m.memNo';
            $arrJoin[] = 'LEFT JOIN ' . DB_ORDER_GOODS . ' as og ON oh.sno = og.handleSno';
            $arrJoin[] = 'LEFT JOIN ' . DB_ORDER_DELIVERY . ' as od ON og.orderDeliverySno = od.sno';
            $arrJoin[] = 'LEFT JOIN ' . DB_ORDER_INFO . ' as oi ON (oh.orderNo = oi.orderNo) 
                          AND (CASE WHEN od.orderInfoSno > 0 THEN od.orderInfoSno = oi.sno ELSE oi.orderInfoCd = 1 END)';
        }

        $this->db->strOrder = 'oh.handleDt asc';
        $this->db->strJoin = implode(' ', $arrJoin);
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_HANDLE . ' as oh ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);

        return gd_htmlspecialchars_stripslashes($getData);
    }

    /**
     * getOrderStatisticsInfo
     * 상품 통계정보 출력
     *
     * @param array $order orderYMD / mallSno / kind / type / scmNo / relationSno / orderHour / orderDevice / orderMemberFl / orderTaxFl / orderAge / orderArea / orderSettleKind
     * @param string $orderField 출력할 필드명 (기본 null)
     * @param array $arrBind bind 처리 배열 (기본 null)
     * @param bool|string $dataArray return 값을 배열처리 (기본값 false)
     * @param bool|string $isGenerator generator 사용 여부 (기본값 false)
     *
     * @return array 상품 정보
     *
     * @author su
     */
    public function getOrderStatisticsInfo($order = null, $orderField = null, $arrBind = null, $dataArray = false, $isGenerator = false)
    {
        if (is_null($arrBind)) {
            // $arrBind = array();
        }
        if (is_array($order['orderYMD'])) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND oss.orderYMD BETWEEN ? AND ? ';
                $this->db->bind_param_push($arrBind, 'i', $order['orderYMD'][0]);
                $this->db->bind_param_push($arrBind, 'i', $order['orderYMD'][1]);
            } else {
                $this->db->strWhere = ' oss.orderYMD BETWEEN ? AND ? ';
                $this->db->bind_param_push($arrBind, 'i', $order['orderYMD'][0]);
                $this->db->bind_param_push($arrBind, 'i', $order['orderYMD'][1]);
            }
        } else {
            if ($order['orderYMD']) {
                if ($this->db->strWhere) {
                    $this->db->strWhere = $this->db->strWhere . ' AND oss.orderYMD = ? ';
                    $this->db->bind_param_push($arrBind, 'i', $order['orderYMD']);
                } else {
                    $this->db->strWhere = ' oss.orderYMD = ? ';
                    $this->db->bind_param_push($arrBind, 'i', $order['orderYMD']);
                }
            }
        }
        if (isset($order['mallSno'])) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND oss.mallSno = ? ';
                $this->db->bind_param_push($arrBind, 'i', $order['mallSno']);
            } else {
                $this->db->strWhere = ' oss.mallSno = ? ';
                $this->db->bind_param_push($arrBind, 'i', $order['mallSno']);
            }
        }
        if (isset($order['kind'])) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND oss.kind = ? ';
                $this->db->bind_param_push($arrBind, 's', $order['kind']);
            } else {
                $this->db->strWhere = ' oss.kind = ? ';
                $this->db->bind_param_push($arrBind, 's', $order['kind']);
            }
        }
        if (isset($order['type'])) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND oss.type = ? ';
                $this->db->bind_param_push($arrBind, 's', $order['type']);
            } else {
                $this->db->strWhere = ' oss.type = ? ';
                $this->db->bind_param_push($arrBind, 's', $order['type']);
            }
        }
        if (isset($order['scmNo'])) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND oss.scmNo = ? ';
                $this->db->bind_param_push($arrBind, 'i', $order['scmNo']);
            } else {
                $this->db->strWhere = ' oss.scmNo = ? ';
                $this->db->bind_param_push($arrBind, 'i', $order['scmNo']);
            }
        }
        if (isset($order['relationSno'])) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND oss.relationSno = ? ';
                $this->db->bind_param_push($arrBind, 'i', $order['relationSno']);
            } else {
                $this->db->strWhere = ' oss.relationSno = ? ';
                $this->db->bind_param_push($arrBind, 'i', $order['relationSno']);
            }
        }
        if (isset($order['orderHour'])) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND oss.orderHour = ? ';
                $this->db->bind_param_push($arrBind, 'i', $order['orderHour']);
            } else {
                $this->db->strWhere = ' oss.orderHour = ? ';
                $this->db->bind_param_push($arrBind, 'i', $order['orderHour']);
            }
        }
        if (isset($order['orderDevice'])) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND oss.orderDevice = ? ';
                $this->db->bind_param_push($arrBind, 's', $order['orderDevice']);
            } else {
                $this->db->strWhere = ' oss.orderDevice = ? ';
                $this->db->bind_param_push($arrBind, 's', $order['orderDevice']);
            }
        }
        if (isset($order['orderMemberFl'])) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND oss.orderMemberFl = ? ';
                $this->db->bind_param_push($arrBind, 's', $order['orderMemberFl']);
            } else {
                $this->db->strWhere = ' oss.orderMemberFl = ? ';
                $this->db->bind_param_push($arrBind, 's', $order['orderMemberFl']);
            }
        }
        if (isset($order['orderTaxFl'])) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND oss.orderTaxFl = ? ';
                $this->db->bind_param_push($arrBind, 's', $order['orderTaxFl']);
            } else {
                $this->db->strWhere = ' oss.orderTaxFl = ? ';
                $this->db->bind_param_push($arrBind, 's', $order['orderTaxFl']);
            }
        }
        if (isset($order['orderAge'])) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND oss.orderAge = ? ';
                $this->db->bind_param_push($arrBind, 'i', $order['orderAge']);
            } else {
                $this->db->strWhere = ' oss.orderAge = ? ';
                $this->db->bind_param_push($arrBind, 'i', $order['orderAge']);
            }
        }
        if (isset($order['orderArea'])) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND oss.orderArea = ? ';
                $this->db->bind_param_push($arrBind, 's', $order['orderArea']);
            } else {
                $this->db->strWhere = ' oss.orderArea = ? ';
                $this->db->bind_param_push($arrBind, 's', $order['orderArea']);
            }
        }
        if (isset($order['orderSettleKind'])) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND oss.orderSettleKind = ? ';
                $this->db->bind_param_push($arrBind, 's', $order['orderSettleKind']);
            } else {
                $this->db->strWhere = ' oss.orderSettleKind = ? ';
                $this->db->bind_param_push($arrBind, 's', $order['orderSettleKind']);
            }
        }
        if (isset($order['purchaseFl']) && $order['purchaseFl'] =='y') {
            $this->db->strWhere = $this->db->strWhere . ' AND oss.purchaseNo > 0';
        }
        if (isset($order['purchaseNo']) && is_array($order['purchaseNo'])) {

            foreach ($order['purchaseNo'] as $val) {
                $tmpWhere[] = 'oss.purchaseNo = ?';
                $this->db->bind_param_push($arrBind, 's', $val);
            }
            $this->db->strWhere  = $this->db->strWhere .' AND (' . implode(' OR ', $tmpWhere) . ')';
            unset($tmpWhere);
        }
        if (isset($order['sort'])) {
            if ($this->db->strOrder) {
                $this->db->strOrder = $this->db->strOrder . ', ' . $order['sort'];
            } else {
                $this->db->strOrder = $order['sort'];
            }
        }
        if (is_array($order['limit'])) {
            $this->db->strLimit = '?, ?';
            $this->db->bind_param_push($arrBind, 'i', $order['limit'][0]);
            $this->db->bind_param_push($arrBind, 'i', $order['limit'][1]);
        }

        if ($orderField) {
            $this->db->strField = $orderField;
        }
        $query = $this->db->query_complete();


        if($isGenerator) {

            $strCountSQL = 'SELECT count(orderYmd) as cnt FROM ' . DB_ORDER_SALES_STATISTICS . ' as oss '.$query['where'];
            $totalNum = $this->db->query_fetch($strCountSQL, $arrBind,false)['cnt'];

            return $this->getOrderStatisticsInfoGenerator($totalNum,$query,$arrBind);

        } else {
            $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_SALES_STATISTICS . ' as oss ' . implode(' ', $query);

            $getData = $this->db->query_fetch($strSQL, $arrBind);

            if (count($getData) == 1 && $dataArray === false) {
                return $getData[0];
            }

            return $getData;
        }
    }


    /**
     * getOrderStatisticsInfoGenerator
     * getOrderStatisticsInfo 에서 generator 사용하여 생성시 사용
     *
     * @param string $totalNum 총 갯수
     * @param string $query 쿼리문
     * @param array $arrBind bind 처리 배열 (기본 null)
     *
     * @return generator object
     *
     * @author su
     */
    public function getOrderStatisticsInfoGenerator($totalNum,$query,$arrBind) {
        $pageLimit = "10000";

        if ($pageLimit >= $totalNum) $pageNum = 0;
        else $pageNum = ceil($totalNum / $pageLimit) - 1;

        $strField =   array_shift($query);
        for ($i = 0; $i <= $pageNum; $i++) {
            $strLimit = " LIMIT ".(($i * $pageLimit)) . "," . $pageLimit;
            $strSQL = 'SELECT ' . $strField . ' FROM ' . DB_ORDER_SALES_STATISTICS . ' as oss ' . implode(' ', $query).$strLimit;
            $tmpData =  $this->db->query_fetch_generator($strSQL, $arrBind);
            foreach($tmpData as $k => $v) {
                yield $v;
            }
            unset($tmpData);
        }
    }

    /**
     * getAreaSimpleName
     * 회원정보를 지역별로 추출
     *
     * @param $area
     *
     * @return mixed
     */
    public function getAreaSimpleName($area)
    {
        $areaName['강원도'] = '강원';
        $areaName['경기도'] = '경기';
        $areaName['경상남도'] = '경남';
        $areaName['경상북도'] = '경북';
        $areaName['광주광역'] = '광주';
        $areaName['대구광역'] = '대구';
        $areaName['대전광역'] = '대전';
        $areaName['부산광역'] = '부산';
        $areaName['서울특별'] = '서울';
        $areaName['세종특별'] = '세종';
        $areaName['울산광역'] = '울산';
        $areaName['인천광역'] = '인천';
        $areaName['전라남도'] = '전남';
        $areaName['전라북도'] = '전북';
        $areaName['제주특별'] = '제주';
        $areaName['충청남도'] = '충남';
        $areaName['충청북도'] = '충북';

        if ($areaName[$area]) {
            $simpleArea = $areaName[$area];
        } else { // 이전 시 주소데이터가 서울,전남 등... 으로 들어간 데이터가 있다.
            $areaArr = explode(' ', $area);
            $simpleArea = $areaArr[0];
        }

        return $simpleArea;
    }

    /**
     * realTimeStatistics
     * 실시간 통계를 위한 오늘 스케줄러 실행
     *
     * @param boolean $compulsion [true - 시간제약없이 강제처리, false - 시간제약 준수]
     *
     * @return void
     */
    public function realTimeStatistics($compulsion=false)
    {
        $setStatisticsFl = false;

        $order['sort'] = 'oss.regDt desc';
        $order['limit'][0] = 0;
        $order['limit'][1] = 1;

        $getField[] = 'oss.regDt';
        $field = implode(', ', $getField);

        $orderData = $this->getOrderStatisticsInfo($order, $field);
        $lastGoodsStatisticsTime = new DateTime($orderData['regDt']);

        if ($this->orderSalesStatisticsProcess == 'realTime') {
            $setStatisticsFl = $this->timeDifferenceInRealTime($lastGoodsStatisticsTime);
        } else {
            $setStatisticsFl = $this->timeDifferenceInPeriodic($lastGoodsStatisticsTime);
        }

        // 시간제약 상관없이 강제실행
        if($compulsion === true){
            $setStatisticsFl = true;
        }

        if($setStatisticsFl === true){
            $this->orderPolicy['statisticsDate'] = $lastGoodsStatisticsTime;
            $this->setOrderSalesStatistics(true);
        }

        unset($order);
    }

    /**
     * 매출통계 - 일별
     * getOrderSalesDay
     *
     * @param $searchData   orderYMD / mallSno / kind / type / scmNo / .... / sort
     *
     * @return array
     * @throws \Exception
     */
    public function getOrderSalesDay($searchData)
    {
        $sDate = new DateTime($searchData['orderYMD'][0]);
        $eDate = new DateTime($searchData['orderYMD'][1]);
        $dateDiff = date_diff($sDate, $eDate);
        if ($dateDiff->days > 90) {
            throw new \Exception(__('검색 가능 일은 최대 90일 입니다.'));
        }
        if ($searchData['orderYMD'][0] > $searchData['orderYMD'][1]) {
            throw new \Exception(__('시작일이 종료일보다 클 수 없습니다.'));
        }

        // 오늘 검색에 따른 당일 통계
        $todayDate = new DateTime();
        if ($eDate->format('Ymd') >= $todayDate->format('Ymd')) {
            $this->realTimeStatistics();
        }

        if ($searchData['mallSno'] != 'all') {
            $order['mallSno'] = $searchData['mallSno'];
        }
        if ($searchData['scmNo'] > 0) {
            $order['scmNo'] = $searchData['scmNo'];
        }

        $order['orderYMD'][0] = $sDate->format('Ymd');
        $order['orderYMD'][1] = $eDate->format('Ymd');
        $order['sort'] = 'oss.orderYMD asc';

        $orderData = $this->getOrderStatisticsInfo($order, null, null, true, true);

        // 일별 매출 통계 데이터 초기화
        $startDate = new DateTime($order['orderYMD'][0]);
        $endDate = new DateTime($order['orderYMD'][1]);
        $diffDay = $endDate->diff($startDate)->days;
        $orderDevice = ['pc', 'mobile', 'write'];
        $returnOrderData = [];
        for ($i = 0; $i <= $diffDay; $i++) {
            $searchDt = new DateTime($order['orderYMD'][0]);
            $searchDt = $searchDt->modify('+' . $i . ' day');
            foreach ($orderDevice as $deviceVal) {
                $returnOrderData[$searchDt->format('Ymd')][$deviceVal]['goodsPrice'] = 0;
                $returnOrderData[$searchDt->format('Ymd')][$deviceVal]['goodsDcPrice'] = 0;
                $returnOrderData[$searchDt->format('Ymd')][$deviceVal]['goodsTotal'] = 0;
                $returnOrderData[$searchDt->format('Ymd')][$deviceVal]['deliveryPrice'] = 0;
                $returnOrderData[$searchDt->format('Ymd')][$deviceVal]['deliveryDcPrice'] = 0;
                $returnOrderData[$searchDt->format('Ymd')][$deviceVal]['deliveryTotal'] = 0;
                $returnOrderData[$searchDt->format('Ymd')][$deviceVal]['refundGoodsPrice'] = 0;
                $returnOrderData[$searchDt->format('Ymd')][$deviceVal]['refundDeliveryPrice'] = 0;
                $returnOrderData[$searchDt->format('Ymd')][$deviceVal]['refundFeePrice'] = 0;
                $returnOrderData[$searchDt->format('Ymd')][$deviceVal]['refundTotal'] = 0;
            }
        }

        foreach ($orderData as $key => $val) {
            $returnOrderData[$val['orderYMD']][$val['orderDevice']]['goodsPrice'] += $val['goodsPrice'];
            $returnOrderData[$val['orderYMD']][$val['orderDevice']]['goodsDcPrice'] += $val['goodsDcPrice'];
            $returnOrderData[$val['orderYMD']][$val['orderDevice']]['goodsTotal'] += ($val['goodsPrice'] - $val['goodsDcPrice']);
            $returnOrderData[$val['orderYMD']][$val['orderDevice']]['deliveryPrice'] += $val['deliveryPrice'];
            $returnOrderData[$val['orderYMD']][$val['orderDevice']]['deliveryDcPrice'] += $val['deliveryDcPrice'];
            $returnOrderData[$val['orderYMD']][$val['orderDevice']]['deliveryTotal'] += ($val['deliveryPrice'] - $val['deliveryDcPrice']);
            $refundGoodsTotal = 0;
            $refundDeliveryTotal = 0;
            if ($val['type'] == 'goods') {
                $refundGoodsTotal = $val['refundGoodsPrice'] + $val['refundUseDeposit'] + $val['refundUseMileage'];
            } else if ($val['type'] == 'delivery') {
                $refundDeliveryTotal = $val['refundDeliveryPrice'] + $val['refundUseDeposit'] + $val['refundUseMileage'];
            }
            $returnOrderData[$val['orderYMD']][$val['orderDevice']]['refundGoodsPrice'] += $refundGoodsTotal;
            $returnOrderData[$val['orderYMD']][$val['orderDevice']]['refundDeliveryPrice'] += $refundDeliveryTotal;
            $returnOrderData[$val['orderYMD']][$val['orderDevice']]['refundFeePrice'] += $val['refundFeePrice'];
            $returnOrderData[$val['orderYMD']][$val['orderDevice']]['refundTotal'] += ($refundGoodsTotal + $refundDeliveryTotal - $val['refundFeePrice']);
            unset($refundGoodsTotal);
            unset($refundDeliveryTotal);
        }

        return $returnOrderData;
    }

    /**
     * 매출통계 - 시간별
     * getOrderSalesDay
     *
     * @param $searchData   orderYMD / mallSno / kind / type / scmNo / .... / sort
     *
     * @return array
     * @throws \Exception
     */
    public function getOrderSalesHour($searchData)
    {
        $sDate = new DateTime($searchData['orderYMD'][0]);
        $eDate = new DateTime($searchData['orderYMD'][1]);
        $dateDiff = date_diff($sDate, $eDate);
        if ($dateDiff->days > 90) {
            throw new \Exception(__('검색 가능 일은 최대 90일 입니다.'));
        }
        if ($searchData['orderYMD'][0] > $searchData['orderYMD'][1]) {
            throw new \Exception(__('시작일이 종료일보다 클 수 없습니다.'));
        }

        // 오늘 검색에 따른 당일 통계
        $todayDate = new DateTime();
        if ($eDate->format('Ymd') >= $todayDate->format('Ymd')) {
            $this->realTimeStatistics();
        }

        if ($searchData['mallSno'] != 'all') {
            $order['mallSno'] = $searchData['mallSno'];
        }
        if ($searchData['scmNo'] > 0) {
            $order['scmNo'] = $searchData['scmNo'];
        }

        $order['orderYMD'][0] = $sDate->format('Ymd');
        $order['orderYMD'][1] = $eDate->format('Ymd');
        $order['sort'] = 'oss.orderYMD asc';

        $orderData = $this->getOrderStatisticsInfo($order, null, null, true, true);

        // 일별 매출 통계 데이터 초기화
        $orderDevice = ['pc', 'mobile', 'write'];
        $returnOrderData = [];
        for ($i = 0; $i <= 23; $i++) {
            $hour = sprintf("%02d", $i);
            foreach ($orderDevice as $deviceVal) {
                $returnOrderData[$hour][$deviceVal]['goodsPrice'] = 0;
                $returnOrderData[$hour][$deviceVal]['goodsDcPrice'] = 0;
                $returnOrderData[$hour][$deviceVal]['goodsTotal'] = 0;
                $returnOrderData[$hour][$deviceVal]['deliveryPrice'] = 0;
                $returnOrderData[$hour][$deviceVal]['deliveryDcPrice'] = 0;
                $returnOrderData[$hour][$deviceVal]['deliveryTotal'] = 0;
                $returnOrderData[$hour][$deviceVal]['refundGoodsPrice'] = 0;
                $returnOrderData[$hour][$deviceVal]['refundDeliveryPrice'] = 0;
                $returnOrderData[$hour][$deviceVal]['refundFeePrice'] = 0;
                $returnOrderData[$hour][$deviceVal]['refundTotal'] = 0;
            }
        }

        foreach ($orderData as $key => $val) {
            $hour = sprintf("%02d", $val['orderHour']);
            $returnOrderData[$hour][$val['orderDevice']]['goodsPrice'] += $val['goodsPrice'];
            $returnOrderData[$hour][$val['orderDevice']]['goodsDcPrice'] += $val['goodsDcPrice'];
            $returnOrderData[$hour][$val['orderDevice']]['goodsTotal'] += ($val['goodsPrice'] - $val['goodsDcPrice']);
            $returnOrderData[$hour][$val['orderDevice']]['deliveryPrice'] += $val['deliveryPrice'];
            $returnOrderData[$hour][$val['orderDevice']]['deliveryDcPrice'] += $val['deliveryDcPrice'];
            $returnOrderData[$hour][$val['orderDevice']]['deliveryTotal'] += ($val['deliveryPrice'] - $val['deliveryDcPrice']);
            $refundGoodsTotal = 0;
            $refundDeliveryTotal = 0;
            if ($val['type'] == 'goods') {
                $refundGoodsTotal = $val['refundGoodsPrice'] + $val['refundUseDeposit'] + $val['refundUseMileage'];
            } else if ($val['type'] == 'delivery') {
                $refundDeliveryTotal = $val['refundDeliveryPrice'] + $val['refundUseDeposit'] + $val['refundUseMileage'];
            }
            $returnOrderData[$hour][$val['orderDevice']]['refundGoodsPrice'] += $refundGoodsTotal;
            $returnOrderData[$hour][$val['orderDevice']]['refundDeliveryPrice'] += $refundDeliveryTotal;
            $returnOrderData[$hour][$val['orderDevice']]['refundFeePrice'] += $val['refundFeePrice'];
            $returnOrderData[$hour][$val['orderDevice']]['refundTotal'] += ($refundGoodsTotal + $refundDeliveryTotal - $val['refundFeePrice']);
            unset($refundGoodsTotal);
            unset($refundDeliveryTotal);
        }

        return $returnOrderData;
    }

    /**
     * 매출통계 - 요일별
     * getOrderSalesWeek
     *
     * @param $searchData   orderYMD / mallSno / kind / type / scmNo / .... / sort
     *
     * @return array
     * @throws \Exception
     */
    public function getOrderSalesWeek($searchData)
    {
        $sDate = new DateTime($searchData['orderYMD'][0]);
        $eDate = new DateTime($searchData['orderYMD'][1]);
        $dateDiff = date_diff($sDate, $eDate);
        if ($dateDiff->days > 90) {
            throw new \Exception(__('검색 가능 일은 최대 90일 입니다.'));
        }
        if ($searchData['orderYMD'][0] > $searchData['orderYMD'][1]) {
            throw new \Exception(__('시작일이 종료일보다 클 수 없습니다.'));
        }

        // 오늘 검색에 따른 당일 통계
        $todayDate = new DateTime();
        if ($eDate->format('Ymd') >= $todayDate->format('Ymd')) {
            $this->realTimeStatistics();
        }

        if ($searchData['mallSno'] != 'all') {
            $order['mallSno'] = $searchData['mallSno'];
        }
        if ($searchData['scmNo'] > 0) {
            $order['scmNo'] = $searchData['scmNo'];
        }

        $order['orderYMD'][0] = $sDate->format('Ymd');
        $order['orderYMD'][1] = $eDate->format('Ymd');
        $order['sort'] = 'oss.orderYMD asc';

        $orderData = $this->getOrderStatisticsInfo($order, null, null, true, true);

        // 일별 매출 통계 데이터 초기화
        $orderDevice = ['pc', 'mobile', 'write'];
        $returnOrderData = [];
        for ($i = 0; $i <= 6; $i++) {
            foreach ($orderDevice as $deviceVal) {
                $returnOrderData[$i][$deviceVal]['goodsPrice'] = 0;
                $returnOrderData[$i][$deviceVal]['goodsDcPrice'] = 0;
                $returnOrderData[$i][$deviceVal]['goodsTotal'] = 0;
                $returnOrderData[$i][$deviceVal]['deliveryPrice'] = 0;
                $returnOrderData[$i][$deviceVal]['deliveryDcPrice'] = 0;
                $returnOrderData[$i][$deviceVal]['deliveryTotal'] = 0;
                $returnOrderData[$i][$deviceVal]['refundGoodsPrice'] = 0;
                $returnOrderData[$i][$deviceVal]['refundDeliveryPrice'] = 0;
                $returnOrderData[$i][$deviceVal]['refundFeePrice'] = 0;
                $returnOrderData[$i][$deviceVal]['refundTotal'] = 0;
            }
        }

        foreach ($orderData as $key => $val) {
            $orderDt = new DateTime($val['orderYMD']);
            $week = $orderDt->format('w');
            $returnOrderData[$week][$val['orderDevice']]['goodsPrice'] += $val['goodsPrice'];
            $returnOrderData[$week][$val['orderDevice']]['goodsDcPrice'] += $val['goodsDcPrice'];
            $returnOrderData[$week][$val['orderDevice']]['goodsTotal'] += ($val['goodsPrice'] - $val['goodsDcPrice']);
            $returnOrderData[$week][$val['orderDevice']]['deliveryPrice'] += $val['deliveryPrice'];
            $returnOrderData[$week][$val['orderDevice']]['deliveryDcPrice'] += $val['deliveryDcPrice'];
            $returnOrderData[$week][$val['orderDevice']]['deliveryTotal'] += ($val['deliveryPrice'] - $val['deliveryDcPrice']);
            $refundGoodsTotal = 0;
            $refundDeliveryTotal = 0;
            if ($val['type'] == 'goods') {
                $refundGoodsTotal = $val['refundGoodsPrice'] + $val['refundUseDeposit'] + $val['refundUseMileage'];
            } else if ($val['type'] == 'delivery') {
                $refundDeliveryTotal = $val['refundDeliveryPrice'] + $val['refundUseDeposit'] + $val['refundUseMileage'];
            }
            $returnOrderData[$week][$val['orderDevice']]['refundGoodsPrice'] += $refundGoodsTotal;
            $returnOrderData[$week][$val['orderDevice']]['refundDeliveryPrice'] += $refundDeliveryTotal;
            $returnOrderData[$week][$val['orderDevice']]['refundFeePrice'] += $val['refundFeePrice'];
            $returnOrderData[$week][$val['orderDevice']]['refundTotal'] += ($refundGoodsTotal + $refundDeliveryTotal - $val['refundFeePrice']);
            unset($refundGoodsTotal);
            unset($refundDeliveryTotal);
        }

        return $returnOrderData;
    }

    /**
     * 매출통계 - 월별
     * getOrderSalesMonth
     *
     * @param $searchData   orderYMD / mallSno / kind / type / scmNo / .... / sort
     *
     * @return array
     * @throws \Exception
     */
    public function getOrderSalesMonth($searchData)
    {
        $sDate = new DateTime($searchData['orderYMD'][0]);
        $eDate = new DateTime($searchData['orderYMD'][1]);
        $dateDiff = date_diff($sDate, $eDate);
        if ($dateDiff->days > 360) {
            throw new \Exception(__('검색 가능 일은 최대 12개월 입니다.'));
        }
        if ($searchData['orderYMD'][0] > $searchData['orderYMD'][1]) {
            throw new \Exception(__('시작일이 종료일보다 클 수 없습니다.'));
        }

        // 오늘 검색에 따른 당일 통계
        $todayDate = new DateTime();
        if ($eDate->format('Ymd') >= $todayDate->format('Ymd')) {
            $this->realTimeStatistics();
        }

        if ($searchData['mallSno'] != 'all') {
            $order['mallSno'] = $searchData['mallSno'];
        }
        if ($searchData['scmNo'] > 0) {
            $order['scmNo'] = $searchData['scmNo'];
        }

        $order['orderYMD'][0] = $sDate->format('Ym') . '01';
        $order['orderYMD'][1] = $eDate->format('Ymt');
        $order['sort'] = 'oss.orderYMD asc';

        $orderData = $this->getOrderStatisticsInfo($order, null, null, true, true);


        // 월별 매출 통계 데이터 초기화
        $searchDt = new DateTime($order['orderYMD'][0]);
        $endDate = new DateTime($order['orderYMD'][1]);
        $orderDevice = ['pc', 'mobile', 'write'];
        $returnOrderData = [];
        for ($i = 0; $i <= 13; $i++) {
            if ($i > 0) {
                $lastDay = $searchDt->format('t');
                $searchDt = $searchDt->modify('+' . $lastDay . ' day');
            }
            if ($searchDt->format('Ym') <= $endDate->format('Ym')) {
                foreach ($orderDevice as $deviceVal) {
                    $returnOrderData[$searchDt->format('Ym')][$deviceVal]['goodsPrice'] = 0;
                    $returnOrderData[$searchDt->format('Ym')][$deviceVal]['goodsDcPrice'] = 0;
                    $returnOrderData[$searchDt->format('Ym')][$deviceVal]['goodsTotal'] = 0;
                    $returnOrderData[$searchDt->format('Ym')][$deviceVal]['deliveryPrice'] = 0;
                    $returnOrderData[$searchDt->format('Ym')][$deviceVal]['deliveryDcPrice'] = 0;
                    $returnOrderData[$searchDt->format('Ym')][$deviceVal]['deliveryTotal'] = 0;
                    $returnOrderData[$searchDt->format('Ym')][$deviceVal]['refundGoodsPrice'] = 0;
                    $returnOrderData[$searchDt->format('Ym')][$deviceVal]['refundDeliveryPrice'] = 0;
                    $returnOrderData[$searchDt->format('Ym')][$deviceVal]['refundFeePrice'] = 0;
                    $returnOrderData[$searchDt->format('Ym')][$deviceVal]['refundTotal'] = 0;
                }
            }
        }

        foreach ($orderData as $key => $val) {
            $orderDt = new DateTime($val['orderYMD']);
            $month = $orderDt->format('Ym');
            $returnOrderData[$month][$val['orderDevice']]['goodsPrice'] += $val['goodsPrice'];
            $returnOrderData[$month][$val['orderDevice']]['goodsDcPrice'] += $val['goodsDcPrice'];
            $returnOrderData[$month][$val['orderDevice']]['goodsTotal'] += ($val['goodsPrice'] - $val['goodsDcPrice']);
            $returnOrderData[$month][$val['orderDevice']]['deliveryPrice'] += $val['deliveryPrice'];
            $returnOrderData[$month][$val['orderDevice']]['deliveryDcPrice'] += $val['deliveryDcPrice'];
            $returnOrderData[$month][$val['orderDevice']]['deliveryTotal'] += ($val['deliveryPrice'] - $val['deliveryDcPrice']);
            $refundGoodsTotal = 0;
            $refundDeliveryTotal = 0;
            if ($val['type'] == 'goods') {
                $refundGoodsTotal = $val['refundGoodsPrice'] + $val['refundUseDeposit'] + $val['refundUseMileage'];
            } else if ($val['type'] == 'delivery') {
                $refundDeliveryTotal = $val['refundDeliveryPrice'] + $val['refundUseDeposit'] + $val['refundUseMileage'];
            }
            $returnOrderData[$month][$val['orderDevice']]['refundGoodsPrice'] += $refundGoodsTotal;
            $returnOrderData[$month][$val['orderDevice']]['refundDeliveryPrice'] += $refundDeliveryTotal;
            $returnOrderData[$month][$val['orderDevice']]['refundFeePrice'] += $val['refundFeePrice'];
            $returnOrderData[$month][$val['orderDevice']]['refundTotal'] += ($refundGoodsTotal + $refundDeliveryTotal - $val['refundFeePrice']);
            unset($refundGoodsTotal);
            unset($refundDeliveryTotal);
        }

        return $returnOrderData;
    }

    /**
     * 매출통계 - 회원별
     * getOrderSalesMember
     *
     * @param $searchData   orderYMD / mallSno / kind / type / scmNo / .... / sort
     *
     * @return array
     * @throws \Exception
     */
    public function getOrderSalesMember($searchData)
    {
        $sDate = new DateTime($searchData['orderYMD'][0]);
        $eDate = new DateTime($searchData['orderYMD'][1]);
        $dateDiff = date_diff($sDate, $eDate);
        if ($dateDiff->days > 90) {
            throw new \Exception(__('검색 가능 일은 최대 90일 입니다.'));
        }
        if ($searchData['orderYMD'][0] > $searchData['orderYMD'][1]) {
            throw new \Exception(__('시작일이 종료일보다 클 수 없습니다.'));
        }

        // 오늘 검색에 따른 당일 통계
        $todayDate = new DateTime();
        if ($eDate->format('Ymd') >= $todayDate->format('Ymd')) {
            $this->realTimeStatistics();
        }

        if ($searchData['mallSno'] != 'all') {
            $order['mallSno'] = $searchData['mallSno'];
        }
        if ($searchData['scmNo'] > 0) {
            $order['scmNo'] = $searchData['scmNo'];
        }

        $order['orderYMD'][0] = $sDate->format('Ymd');
        $order['orderYMD'][1] = $eDate->format('Ymd');
        $order['sort'] = 'oss.orderYMD asc';

        $orderData = $this->getOrderStatisticsInfo($order, null, null, true, true);

        // 일별 매출 통계 데이터 초기화
        $startDate = new DateTime($order['orderYMD'][0]);
        $endDate = new DateTime($order['orderYMD'][1]);
        $diffDay = $endDate->diff($startDate)->days;
        $orderDevice = ['pc', 'mobile', 'write'];
        $returnOrderData = [];
        for ($i = 0; $i <= $diffDay; $i++) {
            $searchDt = new DateTime($order['orderYMD'][0]);
            $searchDt = $searchDt->modify('+' . $i . ' day');
            foreach ($orderDevice as $deviceVal) {
                $returnOrderData[$searchDt->format('Ymd')][$deviceVal]['y']['goodsPrice'] = 0;
                $returnOrderData[$searchDt->format('Ymd')][$deviceVal]['y']['goodsDcPrice'] = 0;
                $returnOrderData[$searchDt->format('Ymd')][$deviceVal]['y']['goodsTotal'] = 0;
                $returnOrderData[$searchDt->format('Ymd')][$deviceVal]['y']['deliveryPrice'] = 0;
                $returnOrderData[$searchDt->format('Ymd')][$deviceVal]['y']['deliveryDcPrice'] = 0;
                $returnOrderData[$searchDt->format('Ymd')][$deviceVal]['y']['deliveryTotal'] = 0;
                $returnOrderData[$searchDt->format('Ymd')][$deviceVal]['y']['refundGoodsPrice'] = 0;
                $returnOrderData[$searchDt->format('Ymd')][$deviceVal]['y']['refundDeliveryPrice'] = 0;
                $returnOrderData[$searchDt->format('Ymd')][$deviceVal]['y']['refundFeePrice'] = 0;
                $returnOrderData[$searchDt->format('Ymd')][$deviceVal]['y']['refundTotal'] = 0;
                $returnOrderData[$searchDt->format('Ymd')][$deviceVal]['n']['goodsPrice'] = 0;
                $returnOrderData[$searchDt->format('Ymd')][$deviceVal]['n']['goodsDcPrice'] = 0;
                $returnOrderData[$searchDt->format('Ymd')][$deviceVal]['n']['goodsTotal'] = 0;
                $returnOrderData[$searchDt->format('Ymd')][$deviceVal]['n']['deliveryPrice'] = 0;
                $returnOrderData[$searchDt->format('Ymd')][$deviceVal]['n']['deliveryDcPrice'] = 0;
                $returnOrderData[$searchDt->format('Ymd')][$deviceVal]['n']['deliveryTotal'] = 0;
                $returnOrderData[$searchDt->format('Ymd')][$deviceVal]['n']['refundGoodsPrice'] = 0;
                $returnOrderData[$searchDt->format('Ymd')][$deviceVal]['n']['refundDeliveryPrice'] = 0;
                $returnOrderData[$searchDt->format('Ymd')][$deviceVal]['n']['refundFeePrice'] = 0;
                $returnOrderData[$searchDt->format('Ymd')][$deviceVal]['n']['refundTotal'] = 0;
            }
        }

        foreach ($orderData as $key => $val) {
            $returnOrderData[$val['orderYMD']][$val['orderDevice']][$val['orderMemberFl']]['goodsPrice'] += $val['goodsPrice'];
            $returnOrderData[$val['orderYMD']][$val['orderDevice']][$val['orderMemberFl']]['goodsDcPrice'] += $val['goodsDcPrice'];
            $returnOrderData[$val['orderYMD']][$val['orderDevice']][$val['orderMemberFl']]['goodsTotal'] += ($val['goodsPrice'] - $val['goodsDcPrice']);
            $returnOrderData[$val['orderYMD']][$val['orderDevice']][$val['orderMemberFl']]['deliveryPrice'] += $val['deliveryPrice'];
            $returnOrderData[$val['orderYMD']][$val['orderDevice']][$val['orderMemberFl']]['deliveryDcPrice'] += $val['deliveryDcPrice'];
            $returnOrderData[$val['orderYMD']][$val['orderDevice']][$val['orderMemberFl']]['deliveryTotal'] += ($val['deliveryPrice'] - $val['deliveryDcPrice']);
            $refundGoodsTotal = 0;
            $refundDeliveryTotal = 0;
            if ($val['type'] == 'goods') {
                $refundGoodsTotal = $val['refundGoodsPrice'] + $val['refundUseDeposit'] + $val['refundUseMileage'];
            } else if ($val['type'] == 'delivery') {
                $refundDeliveryTotal = $val['refundDeliveryPrice'] + $val['refundUseDeposit'] + $val['refundUseMileage'];
            }
            $returnOrderData[$val['orderYMD']][$val['orderDevice']][$val['orderMemberFl']]['refundGoodsPrice'] += $refundGoodsTotal;
            $returnOrderData[$val['orderYMD']][$val['orderDevice']][$val['orderMemberFl']]['refundDeliveryPrice'] += $refundDeliveryTotal;
            $returnOrderData[$val['orderYMD']][$val['orderDevice']][$val['orderMemberFl']]['refundFeePrice'] += $val['refundFeePrice'];
            $returnOrderData[$val['orderYMD']][$val['orderDevice']][$val['orderMemberFl']]['refundTotal'] += ($refundGoodsTotal + $refundDeliveryTotal - $val['refundFeePrice']);
            unset($refundGoodsTotal);
            unset($refundDeliveryTotal);
        }

        return $returnOrderData;
    }

    /**
     * 매출통계 - 과세별
     * getOrderSalesTax
     *
     * @param $searchData   orderYMD / mallSno / kind / type / scmNo / .... / sort
     *
     * @return array
     * @throws \Exception
     */
    public function getOrderSalesTax($searchData)
    {
        $sDate = new DateTime($searchData['orderYMD'][0]);
        $eDate = new DateTime($searchData['orderYMD'][1]);
        $dateDiff = date_diff($sDate, $eDate);
        if ($dateDiff->days > 90) {
            throw new \Exception(__('검색 가능 일은 최대 90일 입니다.'));
        }
        if ($searchData['orderYMD'][0] > $searchData['orderYMD'][1]) {
            throw new \Exception(__('시작일이 종료일보다 클 수 없습니다.'));
        }

        // 오늘 검색에 따른 당일 통계
        $todayDate = new DateTime();
        if ($eDate->format('Ymd') >= $todayDate->format('Ymd')) {
            $this->realTimeStatistics();
        }

        if ($searchData['mallSno'] != 'all') {
            $order['mallSno'] = $searchData['mallSno'];
        }
        if ($searchData['scmNo'] > 0) {
            $order['scmNo'] = $searchData['scmNo'];
        }

        $order['orderYMD'][0] = $sDate->format('Ymd');
        $order['orderYMD'][1] = $eDate->format('Ymd');
        $order['sort'] = 'oss.orderYMD asc';

        $orderData = $this->getOrderStatisticsInfo($order, null, null, true, true);

        // 일별 매출 통계 데이터 초기화
        $startDate = new DateTime($order['orderYMD'][0]);
        $endDate = new DateTime($order['orderYMD'][1]);
        $diffDay = $endDate->diff($startDate)->days;
        $orderDevice = ['pc', 'mobile', 'write'];
        $returnOrderData = [];
        for ($i = 0; $i <= $diffDay; $i++) {
            $searchDt = new DateTime($order['orderYMD'][0]);
            $searchDt = $searchDt->modify('+' . $i . ' day');
            foreach ($orderDevice as $deviceVal) {
                $returnOrderData[$searchDt->format('Ymd')][$deviceVal]['y']['goodsPrice'] = 0;
                $returnOrderData[$searchDt->format('Ymd')][$deviceVal]['y']['goodsDcPrice'] = 0;
                $returnOrderData[$searchDt->format('Ymd')][$deviceVal]['y']['goodsTotal'] = 0;
                $returnOrderData[$searchDt->format('Ymd')][$deviceVal]['y']['deliveryPrice'] = 0;
                $returnOrderData[$searchDt->format('Ymd')][$deviceVal]['y']['deliveryDcPrice'] = 0;
                $returnOrderData[$searchDt->format('Ymd')][$deviceVal]['y']['deliveryTotal'] = 0;
                $returnOrderData[$searchDt->format('Ymd')][$deviceVal]['y']['refundGoodsPrice'] = 0;
                $returnOrderData[$searchDt->format('Ymd')][$deviceVal]['y']['refundDeliveryPrice'] = 0;
                $returnOrderData[$searchDt->format('Ymd')][$deviceVal]['y']['refundFeePrice'] = 0;
                $returnOrderData[$searchDt->format('Ymd')][$deviceVal]['y']['refundTotal'] = 0;
                $returnOrderData[$searchDt->format('Ymd')][$deviceVal]['n']['goodsPrice'] = 0;
                $returnOrderData[$searchDt->format('Ymd')][$deviceVal]['n']['goodsDcPrice'] = 0;
                $returnOrderData[$searchDt->format('Ymd')][$deviceVal]['n']['goodsTotal'] = 0;
                $returnOrderData[$searchDt->format('Ymd')][$deviceVal]['n']['deliveryPrice'] = 0;
                $returnOrderData[$searchDt->format('Ymd')][$deviceVal]['n']['deliveryDcPrice'] = 0;
                $returnOrderData[$searchDt->format('Ymd')][$deviceVal]['n']['deliveryTotal'] = 0;
                $returnOrderData[$searchDt->format('Ymd')][$deviceVal]['n']['refundGoodsPrice'] = 0;
                $returnOrderData[$searchDt->format('Ymd')][$deviceVal]['n']['refundDeliveryPrice'] = 0;
                $returnOrderData[$searchDt->format('Ymd')][$deviceVal]['n']['refundFeePrice'] = 0;
                $returnOrderData[$searchDt->format('Ymd')][$deviceVal]['n']['refundTotal'] = 0;
            }
        }

        foreach ($orderData as $key => $val) {
            $returnOrderData[$val['orderYMD']][$val['orderDevice']][$val['orderTaxFl']]['goodsPrice'] += $val['goodsPrice'];
            $returnOrderData[$val['orderYMD']][$val['orderDevice']][$val['orderTaxFl']]['goodsDcPrice'] += $val['goodsDcPrice'];
            $returnOrderData[$val['orderYMD']][$val['orderDevice']][$val['orderTaxFl']]['goodsTotal'] += ($val['goodsPrice'] - $val['goodsDcPrice']);
            $returnOrderData[$val['orderYMD']][$val['orderDevice']][$val['orderTaxFl']]['deliveryPrice'] += $val['deliveryPrice'];
            $returnOrderData[$val['orderYMD']][$val['orderDevice']][$val['orderTaxFl']]['deliveryDcPrice'] += $val['deliveryDcPrice'];
            $returnOrderData[$val['orderYMD']][$val['orderDevice']][$val['orderTaxFl']]['deliveryTotal'] += ($val['deliveryPrice'] - $val['deliveryDcPrice']);
            $refundGoodsTotal = 0;
            $refundDeliveryTotal = 0;
            if ($val['type'] == 'goods') {
                $refundGoodsTotal = $val['refundGoodsPrice'] + $val['refundUseDeposit'] + $val['refundUseMileage'];
            } else if ($val['type'] == 'delivery') {
                $refundDeliveryTotal = $val['refundDeliveryPrice'] + $val['refundUseDeposit'] + $val['refundUseMileage'];
            }
            $returnOrderData[$val['orderYMD']][$val['orderDevice']][$val['orderTaxFl']]['refundGoodsPrice'] += $refundGoodsTotal;
            $returnOrderData[$val['orderYMD']][$val['orderDevice']][$val['orderTaxFl']]['refundDeliveryPrice'] += $refundDeliveryTotal;
            $returnOrderData[$val['orderYMD']][$val['orderDevice']][$val['orderTaxFl']]['refundFeePrice'] += $val['refundFeePrice'];
            $returnOrderData[$val['orderYMD']][$val['orderDevice']][$val['orderTaxFl']]['refundTotal'] += ($refundGoodsTotal + $refundDeliveryTotal - $val['refundFeePrice']);
            unset($refundGoodsTotal);
            unset($refundDeliveryTotal);
        }

        return $returnOrderData;
    }

    /**
     * 매출통계 - 연령 - 일별
     * getOrderSalesAgeDay
     *
     * @param $searchData   orderYMD / mallSno / kind / type / scmNo / .... / sort
     *
     * @return array
     * @throws \Exception
     */
    public function getOrderSalesAgeDay($searchData)
    {
        // 상점별 고유번호 - 해외상점
        $mallSno = gd_isset($mallSno, DEFAULT_MALL_NUMBER);

        $sDate = new DateTime($searchData['orderYMD'][0]);
        $eDate = new DateTime($searchData['orderYMD'][1]);
        $dateDiff = date_diff($sDate, $eDate);
        if ($dateDiff->days > 90) {
            throw new \Exception(__('검색 가능 일은 최대 90일 입니다.'));
        }
        if ($searchData['orderYMD'][0] > $searchData['orderYMD'][1]) {
            throw new \Exception(__('시작일이 종료일보다 클 수 없습니다.'));
        }

        // 오늘 검색에 따른 당일 통계
        $todayDate = new DateTime();
        if ($eDate->format('Ymd') >= $todayDate->format('Ymd')) {
            $this->realTimeStatistics();
        }

        $order['orderYMD'][0] = $sDate->format('Ymd');
        $order['orderYMD'][1] = $eDate->format('Ymd');
        $order['sort'] = 'oss.orderYMD asc';
        $order['mallSno'] = $mallSno;

        $orderData = $this->getOrderStatisticsInfo($order, null, null, true, true);

        // 일별 매출 통계 데이터 초기화
        $startDate = new DateTime($order['orderYMD'][0]);
        $endDate = new DateTime($order['orderYMD'][1]);
        $diffDay = $endDate->diff($startDate)->days;
        $orderDevice = ['pc', 'mobile', 'write'];
        $returnOrderData = [];
        for ($i = 0; $i <= $diffDay; $i++) {
            $searchDt = new DateTime($order['orderYMD'][0]);
            $searchDt = $searchDt->modify('+' . $i . ' day');
            foreach ($orderDevice as $deviceVal) {
                for ($j = 10; $j <= 70; $j += 10) {
                    $returnOrderData[$searchDt->format('Ymd')][$deviceVal][$j] = 0;
                }
                $returnOrderData[$searchDt->format('Ymd')][$deviceVal]['etc'] = 0;
            }
        }

        foreach ($orderData as $key => $val) {
            if ($val['orderAge'] == 0 || $val['orderAge'] == 127) { // 생일이 없으면 디비필드 tinyint 최대값인 127 값 / 비회원이면 0
                $age = 'etc';
            } else if ($val['orderAge'] < 20) {
                $age = 10;
            } else if ($val['orderAge'] >= 70 && $val['orderAge'] <= 126) { // 70~126 세는 70대
                $age = '70';
            } else {
                $age = $val['orderAge'];
            }
            $goodsTotal = $val['goodsPrice'] - $val['goodsDcPrice'];
            $deliveryTotal = $val['deliveryPrice'] - $val['deliveryDcPrice'];
            $refundGoodsTotal = 0;
            $refundDeliveryTotal = 0;
            if ($val['type'] == 'goods') {
                $refundGoodsTotal = $val['refundGoodsPrice'] + $val['refundUseDeposit'] + $val['refundUseMileage'];
            } else if ($val['type'] == 'delivery') {
                $refundDeliveryTotal = $val['refundDeliveryPrice'] + $val['refundUseDeposit'] + $val['refundUseMileage'];
            }
            $refundTotal = $refundGoodsTotal + $refundDeliveryTotal - $val['refundFeePrice'];
            $returnOrderData[$val['orderYMD']][$val['orderDevice']][$age] += $goodsTotal + $deliveryTotal - $refundTotal;
            unset($goodsTotal);
            unset($deliveryTotal);
            unset($refundGoodsTotal);
            unset($refundDeliveryTotal);
            unset($refundTotal);
        }

        return $returnOrderData;
    }

    /**
     * 매출통계 - 연령 - 시간별
     * getOrderSalesAgeHour
     *
     * @param $searchData   orderYMD / mallSno / kind / type / scmNo / .... / sort
     *
     * @return array
     * @throws \Exception
     */
    public function getOrderSalesAgeHour($searchData)
    {
        // 상점별 고유번호 - 해외상점
        $mallSno = gd_isset($mallSno, DEFAULT_MALL_NUMBER);

        $sDate = new DateTime($searchData['orderYMD'][0]);
        $eDate = new DateTime($searchData['orderYMD'][1]);
        $dateDiff = date_diff($sDate, $eDate);
        if ($dateDiff->days > 90) {
            throw new \Exception(__('검색 가능 일은 최대 90일 입니다.'));
        }
        if ($searchData['orderYMD'][0] > $searchData['orderYMD'][1]) {
            throw new \Exception(__('시작일이 종료일보다 클 수 없습니다.'));
        }

        // 오늘 검색에 따른 당일 통계
        $todayDate = new DateTime();
        if ($eDate->format('Ymd') >= $todayDate->format('Ymd')) {
            $this->realTimeStatistics();
        }

        $order['orderYMD'][0] = $sDate->format('Ymd');
        $order['orderYMD'][1] = $eDate->format('Ymd');
        $order['mallSno'] = $mallSno;
        $order['sort'] = 'oss.orderYMD asc';

        $orderData = $this->getOrderStatisticsInfo($order, null, null, true, true);

        // 시간별 매출 통계 데이터 초기화
        $orderDevice = ['pc', 'mobile', 'write'];
        for ($i = 0; $i <= 23; $i++) {
            $hour = sprintf("%02d", $i);
            foreach ($orderDevice as $deviceVal) {
                for ($j = 10; $j <= 70; $j += 10) {
                    $returnOrderData[$hour][$deviceVal][$j] = 0;
                }
                $returnOrderData[$hour][$deviceVal]['etc'] = 0;
            }
        }

        foreach ($orderData as $key => $val) {
            $hour = sprintf("%02d", $val['orderHour']);
            if ($val['orderAge'] == 0 || $val['orderAge'] == 127) { // 생일이 없으면 디비필드 tinyint 최대값인 127 값 / 비회원이면 0
                $age = 'etc';
            } else if ($val['orderAge'] < 20) {
                $age = 10;
            } else if ($val['orderAge'] >= 70 && $val['orderAge'] <= 126) { // 70~126 세는 70대
                $age = '70';
            } else {
                $age = $val['orderAge'];
            }
            $goodsTotal = $val['goodsPrice'] - $val['goodsDcPrice'];
            $deliveryTotal = $val['deliveryPrice'] - $val['deliveryDcPrice'];
            $refundGoodsTotal = 0;
            $refundDeliveryTotal = 0;
            if ($val['type'] == 'goods') {
                $refundGoodsTotal = $val['refundGoodsPrice'] + $val['refundUseDeposit'] + $val['refundUseMileage'];
            } else if ($val['type'] == 'delivery') {
                $refundDeliveryTotal = $val['refundDeliveryPrice'] + $val['refundUseDeposit'] + $val['refundUseMileage'];
            }
            $refundTotal = $refundGoodsTotal + $refundDeliveryTotal - $val['refundFeePrice'];
            $returnOrderData[$hour][$val['orderDevice']][$age] += $goodsTotal + $deliveryTotal - $refundTotal;
            unset($goodsTotal);
            unset($deliveryTotal);
            unset($refundGoodsTotal);
            unset($refundDeliveryTotal);
            unset($refundTotal);
        }

        return $returnOrderData;
    }

    /**
     * 매출통계 - 연령 - 요일별
     * getOrderSalesAgeWeek
     *
     * @param $searchData   orderYMD / mallSno / kind / type / scmNo / .... / sort
     *
     * @return array
     * @throws \Exception
     */
    public function getOrderSalesAgeWeek($searchData)
    {
        // 상점별 고유번호 - 해외상점
        $mallSno = gd_isset($mallSno, DEFAULT_MALL_NUMBER);

        $sDate = new DateTime($searchData['orderYMD'][0]);
        $eDate = new DateTime($searchData['orderYMD'][1]);
        $dateDiff = date_diff($sDate, $eDate);
        if ($dateDiff->days > 90) {
            throw new \Exception(__('검색 가능 일은 최대 90일 입니다.'));
        }
        if ($searchData['orderYMD'][0] > $searchData['orderYMD'][1]) {
            throw new \Exception(__('시작일이 종료일보다 클 수 없습니다.'));
        }

        // 오늘 검색에 따른 당일 통계
        $todayDate = new DateTime();
        if ($eDate->format('Ymd') >= $todayDate->format('Ymd')) {
            $this->realTimeStatistics();
        }

        $order['orderYMD'][0] = $sDate->format('Ymd');
        $order['orderYMD'][1] = $eDate->format('Ymd');
        $order['mallSno'] = $mallSno;
        $order['sort'] = 'oss.orderYMD asc';

        $orderData = $this->getOrderStatisticsInfo($order, null, null, true, true);

        // 시간별 매출 통계 데이터 초기화
        $orderDevice = ['pc', 'mobile', 'write'];
        for ($i = 0; $i <= 6; $i++) {
            foreach ($orderDevice as $deviceVal) {
                for ($j = 10; $j <= 70; $j += 10) {
                    $returnOrderData[$i][$deviceVal][$j] = 0;
                }
                $returnOrderData[$i][$deviceVal]['etc'] = 0;
            }
        }

        foreach ($orderData as $key => $val) {
            $orderDt = new DateTime($val['orderYMD']);
            $week = $orderDt->format('w');
            if ($val['orderAge'] == 0 || $val['orderAge'] == 127) { // 생일이 없으면 디비필드 tinyint 최대값인 127 값 / 비회원이면 0
                $age = 'etc';
            } else if ($val['orderAge'] < 20) {
                $age = 10;
            } else if ($val['orderAge'] >= 70 && $val['orderAge'] <= 126) { // 70~126 세는 70대
                $age = '70';
            } else {
                $age = $val['orderAge'];
            }
            $goodsTotal = $val['goodsPrice'] - $val['goodsDcPrice'];
            $deliveryTotal = $val['deliveryPrice'] - $val['deliveryDcPrice'];
            $refundGoodsTotal = 0;
            $refundDeliveryTotal = 0;
            if ($val['type'] == 'goods') {
                $refundGoodsTotal = $val['refundGoodsPrice'] + $val['refundUseDeposit'] + $val['refundUseMileage'];
            } else if ($val['type'] == 'delivery') {
                $refundDeliveryTotal = $val['refundDeliveryPrice'] + $val['refundUseDeposit'] + $val['refundUseMileage'];
            }
            $refundTotal = $refundGoodsTotal + $refundDeliveryTotal - $val['refundFeePrice'];
            $returnOrderData[$week][$val['orderDevice']][$age] += $goodsTotal + $deliveryTotal - $refundTotal;
            unset($goodsTotal);
            unset($deliveryTotal);
            unset($refundGoodsTotal);
            unset($refundDeliveryTotal);
            unset($refundTotal);
        }

        return $returnOrderData;
    }

    /**
     * 매출통계 - 연령 - 월별
     * getOrderSalesAgeMonth
     *
     * @param $searchData   orderYMD / mallSno / kind / type / scmNo / .... / sort
     *
     * @return array
     * @throws \Exception
     */
    public function getOrderSalesAgeMonth($searchData)
    {
        // 상점별 고유번호 - 해외상점
        $mallSno = gd_isset($mallSno, DEFAULT_MALL_NUMBER);

        $sDate = new DateTime($searchData['orderYMD'][0]);
        $eDate = new DateTime($searchData['orderYMD'][1]);
        $dateDiff = date_diff($sDate, $eDate);
        if ($dateDiff->days > 360) {
            throw new \Exception(__('검색 가능 일은 최대 12개월 입니다.'));
        }
        if ($searchData['orderYMD'][0] > $searchData['orderYMD'][1]) {
            throw new \Exception(__('시작일이 종료일보다 클 수 없습니다.'));
        }

        // 오늘 검색에 따른 당일 통계
        $todayDate = new DateTime();
        if ($eDate->format('Ymd') >= $todayDate->format('Ymd')) {
            $this->realTimeStatistics();
        }

        $order['orderYMD'][0] = $sDate->format('Ymd');
        $order['orderYMD'][1] = $eDate->format('Ymd');
        $order['mallSno'] = $mallSno;
        $order['sort'] = 'oss.orderYMD asc';

        $orderData = $this->getOrderStatisticsInfo($order, null, null, true, true);

        // 월별 매출 통계 데이터 초기화
        $searchDt = new DateTime($order['orderYMD'][0]);
        $endDate = new DateTime($order['orderYMD'][1]);
        $orderDevice = ['pc', 'mobile', 'write'];
        $returnOrderData = [];
        for ($i = 0; $i <= 13; $i++) {
            if ($i > 0) {
                $lastDay = $searchDt->format('t');
                $searchDt = $searchDt->modify('+' . $lastDay . ' day');
            }
            if ($searchDt->format('Ym') <= $endDate->format('Ym')) {
                foreach ($orderDevice as $deviceVal) {
                    for ($j = 10; $j <= 70; $j += 10) {
                        $returnOrderData[$searchDt->format('Ym')][$deviceVal][$j] = 0;
                    }
                    $returnOrderData[$searchDt->format('Ym')][$deviceVal]['etc'] = 0;
                }
            }
        }

        foreach ($orderData as $key => $val) {
            $orderDt = new DateTime($val['orderYMD']);
            $month = $orderDt->format('Ym');
            if ($val['orderAge'] == 0 || $val['orderAge'] == 127) { // 생일이 없으면 디비필드 tinyint 최대값인 127 값 / 비회원이면 0
                $age = 'etc';
            } else if ($val['orderAge'] < 20) {
                $age = 10;
            } else if ($val['orderAge'] >= 70 && $val['orderAge'] <= 126) { // 70~126 세는 70대
                $age = '70';
            } else {
                $age = $val['orderAge'];
            }
            $goodsTotal = $val['goodsPrice'] - $val['goodsDcPrice'];
            $deliveryTotal = $val['deliveryPrice'] - $val['deliveryDcPrice'];
            $refundGoodsTotal = 0;
            $refundDeliveryTotal = 0;
            if ($val['type'] == 'goods') {
                $refundGoodsTotal = $val['refundGoodsPrice'] + $val['refundUseDeposit'] + $val['refundUseMileage'];
            } else if ($val['type'] == 'delivery') {
                $refundDeliveryTotal = $val['refundDeliveryPrice'] + $val['refundUseDeposit'] + $val['refundUseMileage'];
            }
            $refundTotal = $refundGoodsTotal + $refundDeliveryTotal - $val['refundFeePrice'];
            $returnOrderData[$month][$val['orderDevice']][$age] += $goodsTotal + $deliveryTotal - $refundTotal;
            unset($goodsTotal);
            unset($deliveryTotal);
            unset($refundGoodsTotal);
            unset($refundDeliveryTotal);
            unset($refundTotal);
        }

        return $returnOrderData;
    }

    /**
     * 매출통계 - 결제수단 - 일별
     * getOrderSalesSettleDay
     *
     * @param $searchData   orderYMD / mallSno / kind / type / scmNo / .... / sort
     *
     * @return array
     * @throws \Exception
     */
    public function getOrderSalesSettleDay($searchData)
    {
        $sDate = new DateTime($searchData['orderYMD'][0]);
        $eDate = new DateTime($searchData['orderYMD'][1]);
        $dateDiff = date_diff($sDate, $eDate);
        if ($dateDiff->days > 90) {
            throw new \Exception(__('검색 가능 일은 최대 90일 입니다.'));
        }
        if ($searchData['orderYMD'][0] > $searchData['orderYMD'][1]) {
            throw new \Exception(__('시작일이 종료일보다 클 수 없습니다.'));
        }

        // 오늘 검색에 따른 당일 통계
        $todayDate = new DateTime();
        if ($eDate->format('Ymd') >= $todayDate->format('Ymd')) {
            $this->realTimeStatistics();
        }

        if ($searchData['mallSno'] != 'all') {
            $order['mallSno'] = $searchData['mallSno'];
        }

        $order['orderYMD'][0] = $sDate->format('Ymd');
        $order['orderYMD'][1] = $eDate->format('Ymd');
        $order['sort'] = 'oss.orderYMD asc';

        $orderData = $this->getOrderStatisticsInfo($order, null, null, true, true);

        $settleKind = $this->getOrderSettleKind();
        $matchSettleKind = $this->getMatchSettleKind();

        // 일별 매출 통계 데이터 초기화
        $startDate = new DateTime($order['orderYMD'][0]);
        $endDate = new DateTime($order['orderYMD'][1]);
        $diffDay = $endDate->diff($startDate)->days;
        $orderDevice = ['pc', 'mobile', 'write'];
        $returnOrderData = [];
        for ($i = 0; $i <= $diffDay; $i++) {
            $searchDt = new DateTime($order['orderYMD'][0]);
            $searchDt = $searchDt->modify('+' . $i . ' day');
            foreach ($orderDevice as $deviceVal) {
                foreach ($settleKind as $settleKey => $settleVal) {
                    $returnOrderData[$searchDt->format('Ymd')][$deviceVal][$settleKey] = 0;
                }
                $returnOrderData[$searchDt->format('Ymd')][$deviceVal]['ol'] = 0;
                $returnOrderData[$searchDt->format('Ymd')][$deviceVal]['op'] = 0;
                $returnOrderData[$searchDt->format('Ymd')][$deviceVal]['oj'] = 0;
                $returnOrderData[$searchDt->format('Ymd')][$deviceVal]['ov'] = 0;
                $returnOrderData[$searchDt->format('Ymd')][$deviceVal]['oc'] = 0;
                $returnOrderData[$searchDt->format('Ymd')][$deviceVal]['mp'] = 0;
                $returnOrderData[$searchDt->format('Ymd')][$deviceVal]['mc'] = 0;
            }
        }

        foreach ($orderData as $key => $val) {
            $goodsTotal = $val['goodsPrice'] - $val['goodsDcPrice'] - $val['divisionUseDeposit'] - $val['divisionUseMileage'];
            $deliveryTotal = $val['deliveryPrice'] - $val['deliveryDcPrice'] - $val['divisionDeliveryUseDeposit'] - $val['divisionDeliveryUseMileage'];
            $refundGoodsTotal = $val['refundGoodsPrice'];
            $refundDeliveryTotal = $val['refundDeliveryPrice'];
            $refundTotal = $refundGoodsTotal + $refundDeliveryTotal - $val['refundFeePrice'];
            $returnOrderData[$val['orderYMD']][$val['orderDevice']][$val['orderSettleKind']] += $goodsTotal + $deliveryTotal - $refundTotal;
            // 사용 예치금 / 마일리지가 -(마이너스)도 발생하여 저장됨.
            if ($val['divisionUseDeposit'] > 0 || $val['divisionUseDeposit'] < 0 ) {
                $returnOrderData[$val['orderYMD']][$val['orderDevice']]['gd'] += $val['divisionUseDeposit'];
            }
            if ($val['divisionUseMileage'] > 0 || $val['divisionUseMileage'] < 0) {
                $returnOrderData[$val['orderYMD']][$val['orderDevice']]['gm'] += $val['divisionUseMileage'];
            }
            if ($val['divisionDeliveryUseDeposit'] > 0 || $val['divisionDeliveryUseDeposit'] < 0) {
                $returnOrderData[$val['orderYMD']][$val['orderDevice']]['gd'] += $val['divisionDeliveryUseDeposit'];
            }
            if ($val['divisionDeliveryUseMileage'] > 0 || $val['divisionDeliveryUseMileage'] < 0) {
                $returnOrderData[$val['orderYMD']][$val['orderDevice']]['gm'] += $val['divisionDeliveryUseMileage'];
            }
            if ($val['refundUseDeposit'] > 0 || $val['refundUseDeposit'] < 0) {
                $returnOrderData[$val['orderYMD']][$val['orderDevice']]['gd'] -= $val['refundUseDeposit'];
            }
            if ($val['refundUseMileage'] > 0 || $val['refundUseMileage'] < 0) {
                $returnOrderData[$val['orderYMD']][$val['orderDevice']]['gm'] -= $val['refundUseMileage'];
            }
            // 해외결제중 일부값은 특정값으로 합내서 해당항목을 보여주기위한 추가 처리
            if (isset($matchSettleKind[$val['orderSettleKind']])) {
                $returnOrderData[$val['orderYMD']][$val['orderDevice']][$matchSettleKind[$val['orderSettleKind']]] += $goodsTotal + $deliveryTotal - $refundTotal;
            }
        }

        return $returnOrderData;
    }

    /**
     * 매출통계 - 결제수단 - 시간별
     * getOrderSalesSettleHour
     *
     * @param $searchData   orderYMD / mallSno / kind / type / scmNo / .... / sort
     *
     * @return array
     * @throws \Exception
     */
    public function getOrderSalesSettleHour($searchData)
    {
        $sDate = new DateTime($searchData['orderYMD'][0]);
        $eDate = new DateTime($searchData['orderYMD'][1]);
        $dateDiff = date_diff($sDate, $eDate);
        if ($dateDiff->days > 90) {
            throw new \Exception(__('검색 가능 일은 최대 90일 입니다.'));
        }
        if ($searchData['orderYMD'][0] > $searchData['orderYMD'][1]) {
            throw new \Exception(__('시작일이 종료일보다 클 수 없습니다.'));
        }

        // 오늘 검색에 따른 당일 통계
        $todayDate = new DateTime();
        if ($eDate->format('Ymd') >= $todayDate->format('Ymd')) {
            $this->realTimeStatistics();
        }

        if ($searchData['mallSno'] != 'all') {
            $order['mallSno'] = $searchData['mallSno'];
        }

        $order['orderYMD'][0] = $sDate->format('Ymd');
        $order['orderYMD'][1] = $eDate->format('Ymd');
        $order['sort'] = 'oss.orderYMD asc';

        $orderData = $this->getOrderStatisticsInfo($order, null, null, true, true);

        $settleKind = $this->getOrderSettleKind();
        $matchSettleKind = $this->getMatchSettleKind();

        // 시간 매출 통계 데이터 초기화
        $orderDevice = ['pc', 'mobile', 'write'];
        for ($i = 0; $i <= 23; $i++) {
            $hour = sprintf("%02d", $i);
            foreach ($orderDevice as $deviceVal) {
                foreach ($settleKind as $settleKey => $settleVal) {
                    $returnOrderData[$hour][$deviceVal][$settleKey] = 0;
                }
                $returnOrderData[$hour][$deviceVal]['ol'] = 0;
                $returnOrderData[$hour][$deviceVal]['op'] = 0;
                $returnOrderData[$hour][$deviceVal]['oj'] = 0;
                $returnOrderData[$hour][$deviceVal]['ov'] = 0;
                $returnOrderData[$hour][$deviceVal]['oc'] = 0;
                $returnOrderData[$hour][$deviceVal]['mp'] = 0;
                $returnOrderData[$hour][$deviceVal]['mc'] = 0;
            }
        }

        foreach ($orderData as $key => $val) {
            $hour = sprintf("%02d", $val['orderHour']);
            $goodsTotal = $val['goodsPrice'] - $val['goodsDcPrice'] - $val['divisionUseDeposit'] - $val['divisionUseMileage'];
            $deliveryTotal = $val['deliveryPrice'] - $val['deliveryDcPrice'] - $val['divisionDeliveryUseDeposit'] - $val['divisionDeliveryUseMileage'];
            $refundGoodsTotal = $val['refundGoodsPrice'];
            $refundDeliveryTotal = $val['refundDeliveryPrice'];
            $refundTotal = $refundGoodsTotal + $refundDeliveryTotal - $val['refundFeePrice'];
            $returnOrderData[$hour][$val['orderDevice']][$val['orderSettleKind']] += $goodsTotal + $deliveryTotal - $refundTotal;
            // 사용 예치금 / 마일리지가 -(마이너스)도 발생하여 저장됨.
            if ($val['divisionUseDeposit'] > 0 || $val['divisionUseDeposit'] < 0) {
                $returnOrderData[$hour][$val['orderDevice']]['gd'] += $val['divisionUseDeposit'];
            }
            if ($val['divisionUseMileage'] > 0 || $val['divisionUseMileage'] < 0) {
                $returnOrderData[$hour][$val['orderDevice']]['gm'] += $val['divisionUseMileage'];
            }
            if ($val['divisionDeliveryUseDeposit'] > 0 || $val['divisionDeliveryUseDeposit'] < 0) {
                $returnOrderData[$hour][$val['orderDevice']]['gd'] += $val['divisionDeliveryUseDeposit'];
            }
            if ($val['divisionDeliveryUseMileage'] > 0 || $val['divisionDeliveryUseMileage'] < 0) {
                $returnOrderData[$hour][$val['orderDevice']]['gm'] += $val['divisionDeliveryUseMileage'];
            }
            if ($val['refundUseDeposit'] > 0 || $val['refundUseDeposit'] < 0) {
                $returnOrderData[$hour][$val['orderDevice']]['gd'] -= $val['refundUseDeposit'];
            }
            if ($val['refundUseMileage'] > 0 || $val['refundUseMileage'] < 0) {
                $returnOrderData[$hour][$val['orderDevice']]['gm'] -= $val['refundUseMileage'];
            }
            // 해외결제중 일부값은 특정값으로 합내서 해당항목을 보여주기위한 추가 처리
            if (isset($matchSettleKind[$val['orderSettleKind']])) {
                $returnOrderData[$hour][$val['orderDevice']][$matchSettleKind[$val['orderSettleKind']]] += $goodsTotal + $deliveryTotal - $refundTotal;
            }
        }

        return $returnOrderData;
    }

    /**
     * 매출통계 - 결제수단 - 요일별
     * getOrderSalesSettleWeek
     *
     * @param $searchData   orderYMD / mallSno / kind / type / scmNo / .... / sort
     *
     * @return array
     * @throws \Exception
     */
    public function getOrderSalesSettleWeek($searchData)
    {
        $sDate = new DateTime($searchData['orderYMD'][0]);
        $eDate = new DateTime($searchData['orderYMD'][1]);
        $dateDiff = date_diff($sDate, $eDate);
        if ($dateDiff->days > 90) {
            throw new \Exception(__('검색 가능 일은 최대 90일 입니다.'));
        }
        if ($searchData['orderYMD'][0] > $searchData['orderYMD'][1]) {
            throw new \Exception(__('시작일이 종료일보다 클 수 없습니다.'));
        }

        // 오늘 검색에 따른 당일 통계
        $todayDate = new DateTime();
        if ($eDate->format('Ymd') >= $todayDate->format('Ymd')) {
            $this->realTimeStatistics();
        }

        if ($searchData['mallSno'] != 'all') {
            $order['mallSno'] = $searchData['mallSno'];
        }

        $order['orderYMD'][0] = $sDate->format('Ymd');
        $order['orderYMD'][1] = $eDate->format('Ymd');
        $order['sort'] = 'oss.orderYMD asc';

        $orderData = $this->getOrderStatisticsInfo($order, null, null, true, true);

        $settleKind = $this->getOrderSettleKind();
        $matchSettleKind = $this->getMatchSettleKind();

        // 시간 매출 통계 데이터 초기화
        $orderDevice = ['pc', 'mobile', 'write'];
        for ($i = 0; $i <= 6; $i++) {
            foreach ($orderDevice as $deviceVal) {
                foreach ($settleKind as $settleKey => $settleVal) {
                    $returnOrderData[$i][$deviceVal][$settleKey] = 0;
                }
                $returnOrderData[$i][$deviceVal]['ol'] = 0;
                $returnOrderData[$i][$deviceVal]['op'] = 0;
                $returnOrderData[$i][$deviceVal]['oj'] = 0;
                $returnOrderData[$i][$deviceVal]['ov'] = 0;
                $returnOrderData[$i][$deviceVal]['oc'] = 0;
                $returnOrderData[$i][$deviceVal]['mp'] = 0;
                $returnOrderData[$i][$deviceVal]['mc'] = 0;
            }
        }

        foreach ($orderData as $key => $val) {
            $orderDt = new DateTime($val['orderYMD']);
            $week = $orderDt->format('w');
            $goodsTotal = $val['goodsPrice'] - $val['goodsDcPrice'] - $val['divisionUseDeposit'] - $val['divisionUseMileage'];
            $deliveryTotal = $val['deliveryPrice'] - $val['deliveryDcPrice'] - $val['divisionDeliveryUseDeposit'] - $val['divisionDeliveryUseMileage'];
            $refundGoodsTotal = $val['refundGoodsPrice'];
            $refundDeliveryTotal = $val['refundDeliveryPrice'];
            $refundTotal = $refundGoodsTotal + $refundDeliveryTotal - $val['refundFeePrice'];
            $returnOrderData[$week][$val['orderDevice']][$val['orderSettleKind']] += $goodsTotal + $deliveryTotal - $refundTotal;
            // 사용 예치금 / 마일리지가 -(마이너스)도 발생하여 저장됨.
            if ($val['divisionUseDeposit'] > 0 || $val['divisionUseDeposit'] < 0) {
                $returnOrderData[$week][$val['orderDevice']]['gd'] += $val['divisionUseDeposit'];
            }
            if ($val['divisionUseMileage'] > 0 || $val['divisionUseMileage'] < 0) {
                $returnOrderData[$week][$val['orderDevice']]['gm'] += $val['divisionUseMileage'];
            }
            if ($val['divisionDeliveryUseDeposit'] > 0 || $val['divisionDeliveryUseDeposit'] < 0) {
                $returnOrderData[$week][$val['orderDevice']]['gd'] += $val['divisionDeliveryUseDeposit'];
            }
            if ($val['divisionDeliveryUseMileage'] > 0 || $val['divisionDeliveryUseMileage'] < 0) {
                $returnOrderData[$week][$val['orderDevice']]['gm'] += $val['divisionDeliveryUseMileage'];
            }
            if ($val['refundUseDeposit'] > 0 || $val['refundUseDeposit'] < 0) {
                $returnOrderData[$week][$val['orderDevice']]['gd'] -= $val['refundUseDeposit'];
            }
            if ($val['refundUseMileage'] > 0 || $val['refundUseMileage'] < 0) {
                $returnOrderData[$week][$val['orderDevice']]['gm'] -= $val['refundUseMileage'];
            }
            // 해외결제중 일부값은 특정값으로 합내서 해당항목을 보여주기위한 추가 처리
            if (isset($matchSettleKind[$val['orderSettleKind']])) {
                $returnOrderData[$week][$val['orderDevice']][$matchSettleKind[$val['orderSettleKind']]] += $goodsTotal + $deliveryTotal - $refundTotal;
            }
        }

        return $returnOrderData;
    }

    /**
     * 매출통계 - 결제수단 - 월별
     * getOrderSalesSettleMonth
     *
     * @param $searchData   orderYMD / mallSno / kind / type / scmNo / .... / sort
     *
     * @return array
     * @throws \Exception
     */
    public function getOrderSalesSettleMonth($searchData)
    {
        $sDate = new DateTime($searchData['orderYMD'][0]);
        $eDate = new DateTime($searchData['orderYMD'][1]);
        $dateDiff = date_diff($sDate, $eDate);
        if ($dateDiff->days > 360) {
            throw new \Exception(__('검색 가능 일은 최대 12개월 입니다.'));
        }
        if ($searchData['orderYMD'][0] > $searchData['orderYMD'][1]) {
            throw new \Exception(__('시작일이 종료일보다 클 수 없습니다.'));
        }

        // 오늘 검색에 따른 당일 통계
        $todayDate = new DateTime();
        if ($eDate->format('Ymd') >= $todayDate->format('Ymd')) {
            $this->realTimeStatistics();
        }

        if ($searchData['mallSno'] != 'all') {
            $order['mallSno'] = $searchData['mallSno'];
        }

        $order['orderYMD'][0] = $sDate->format('Ymd');
        $order['orderYMD'][1] = $eDate->format('Ymd');
        $order['sort'] = 'oss.orderYMD asc';

        $orderData = $this->getOrderStatisticsInfo($order, null, null, true,true);

        $settleKind = $this->getOrderSettleKind();
        $matchSettleKind = $this->getMatchSettleKind();

        // 월 매출 통계 데이터 초기화
        $searchDt = new DateTime($order['orderYMD'][0]);
        $endDate = new DateTime($order['orderYMD'][1]);
        $orderDevice = ['pc', 'mobile', 'write'];
        $returnOrderData = [];
        for ($i = 0; $i <= 13; $i++) {
            if ($i > 0) {
                $lastDay = $searchDt->format('t');
                $searchDt = $searchDt->modify('+' . $lastDay . ' day');
            }

            if ($searchDt->format('Ym') <= $endDate->format('Ym')) {
                foreach ($orderDevice as $deviceVal) {
                    foreach ($settleKind as $settleKey => $settleVal) {
                        $returnOrderData[$searchDt->format('Ym')][$deviceVal][$settleKey] = 0;
                    }
                    $returnOrderData[$searchDt->format('Ym')][$deviceVal]['ol'] = 0;
                    $returnOrderData[$searchDt->format('Ym')][$deviceVal]['op'] = 0;
                    $returnOrderData[$searchDt->format('Ym')][$deviceVal]['oj'] = 0;
                    $returnOrderData[$searchDt->format('Ym')][$deviceVal]['ov'] = 0;
                    $returnOrderData[$searchDt->format('Ym')][$deviceVal]['oc'] = 0;
                    $returnOrderData[$searchDt->format('Ym')][$deviceVal]['mp'] = 0;
                    $returnOrderData[$searchDt->format('Ym')][$deviceVal]['mc'] = 0;
                }
            }
        }

        foreach ($orderData as $key => $val) {
            $orderDt = new DateTime($val['orderYMD']);
            $month = $orderDt->format('Ym');
            $goodsTotal = $val['goodsPrice'] - $val['goodsDcPrice'] - $val['divisionUseDeposit'] - $val['divisionUseMileage'];
            $deliveryTotal = $val['deliveryPrice'] - $val['deliveryDcPrice'] - $val['divisionDeliveryUseDeposit'] - $val['divisionDeliveryUseMileage'];
            $refundGoodsTotal = $val['refundGoodsPrice'];
            $refundDeliveryTotal = $val['refundDeliveryPrice'];
            $refundTotal = $refundGoodsTotal + $refundDeliveryTotal - $val['refundFeePrice'];
            $returnOrderData[$month][$val['orderDevice']][$val['orderSettleKind']] += $goodsTotal + $deliveryTotal - $refundTotal;
            // 사용 예치금 / 마일리지가 -(마이너스)도 발생하여 저장됨.
            if ($val['divisionUseDeposit'] > 0 || $val['divisionUseDeposit'] < 0) {
                $returnOrderData[$month][$val['orderDevice']]['gd'] += $val['divisionUseDeposit'];
            }
            if ($val['divisionUseMileage'] > 0 || $val['divisionUseMileage'] < 0) {
                $returnOrderData[$month][$val['orderDevice']]['gm'] += $val['divisionUseMileage'];
            }
            if ($val['divisionDeliveryUseDeposit'] > 0 || $val['divisionDeliveryUseDeposit'] < 0) {
                $returnOrderData[$month][$val['orderDevice']]['gd'] += $val['divisionDeliveryUseDeposit'];
            }
            if ($val['divisionDeliveryUseMileage'] > 0 || $val['divisionDeliveryUseMileage'] < 0) {
                $returnOrderData[$month][$val['orderDevice']]['gm'] += $val['divisionDeliveryUseMileage'];
            }
            if ($val['refundUseDeposit'] > 0 || $val['refundUseDeposit'] < 0) {
                $returnOrderData[$month][$val['orderDevice']]['gd'] -= $val['refundUseDeposit'];
            }
            if ($val['refundUseMileage'] > 0 || $val['refundUseMileage'] < 0) {
                $returnOrderData[$month][$val['orderDevice']]['gm'] -= $val['refundUseMileage'];
            }
            // 해외결제중 일부값은 특정값으로 합내서 해당항목을 보여주기위한 추가 처리
            if (isset($matchSettleKind[$val['orderSettleKind']])) {
                $returnOrderData[$month][$val['orderDevice']][$matchSettleKind[$val['orderSettleKind']]] += $goodsTotal + $deliveryTotal - $refundTotal;
            }
        }

        return $returnOrderData;
    }

    /**
     * 매출통계 - 지역별 - 일별
     * getOrderSalesAreaDay
     *
     * @param $searchData   orderYMD / mallSno / kind / type / scmNo / .... / sort
     *
     * @return array
     * @throws \Exception
     */
    public function getOrderSalesAreaDay($searchData)
    {
        $sDate = new DateTime($searchData['orderYMD'][0]);
        $eDate = new DateTime($searchData['orderYMD'][1]);
        $dateDiff = date_diff($sDate, $eDate);
        if ($dateDiff->days > 90) {
            throw new \Exception(__('검색 가능 일은 최대 90일 입니다.'));
        }
        if ($searchData['orderYMD'][0] > $searchData['orderYMD'][1]) {
            throw new \Exception(__('시작일이 종료일보다 클 수 없습니다.'));
        }

        // 오늘 검색에 따른 당일 통계
        $todayDate = new DateTime();
        if ($eDate->format('Ymd') >= $todayDate->format('Ymd')) {
            $this->realTimeStatistics();
        }

        $order['orderYMD'][0] = $sDate->format('Ymd');
        $order['orderYMD'][1] = $eDate->format('Ymd');
        $order['mallSno'] = $searchData['mallSno'];
        $order['sort'] = 'oss.orderYMD asc';

        $orderData = $this->getOrderStatisticsInfo($order, null, null, true);

        $settleArea = $this->getOrderSettleArea();

        // 일별 매출 통계 데이터 초기화
        $startDate = new DateTime($order['orderYMD'][0]);
        $endDate = new DateTime($order['orderYMD'][1]);
        $diffDay = $endDate->diff($startDate)->days;
        $orderDevice = ['pc', 'mobile', 'write'];
        $returnOrderData = [];
        for ($i = 0; $i <= $diffDay; $i++) {
            $searchDt = new DateTime($order['orderYMD'][0]);
            $searchDt = $searchDt->modify('+' . $i . ' day');
            foreach ($orderDevice as $deviceVal) {
                foreach ($settleArea as $settleKey => $settleVal) {
                    $returnOrderData[$searchDt->format('Ymd')][$deviceVal][$settleVal] = 0;
                }
            }
        }

        foreach ($orderData as $key => $val) {
            $goodsTotal = $val['goodsPrice'] - $val['goodsDcPrice'];
            $deliveryTotal = $val['deliveryPrice'] - $val['deliveryDcPrice'];
            $refundGoodsTotal = 0;
            $refundDeliveryTotal = 0;
            if ($val['type'] == 'goods') {
                $refundGoodsTotal = $val['refundGoodsPrice'] + $val['refundUseDeposit'] + $val['refundUseMileage'];
            } else if ($val['type'] == 'delivery') {
                $refundDeliveryTotal = $val['refundDeliveryPrice'] + $val['refundUseDeposit'] + $val['refundUseMileage'];
            }
            $refundTotal = $refundGoodsTotal + $refundDeliveryTotal - $val['refundFeePrice'];
            $returnOrderData[$val['orderYMD']][$val['orderDevice']][$settleArea[$val['orderArea']]] += $goodsTotal + $deliveryTotal - $refundTotal;
        }

        return $returnOrderData;
    }

    /**
     * 매출통계 - 지역별 - 시간별
     * getOrderSalesAreaHour
     *
     * @param $searchData   orderYMD / mallSno / kind / type / scmNo / .... / sort
     *
     * @return array
     * @throws \Exception
     */
    public function getOrderSalesAreaHour($searchData)
    {
        $sDate = new DateTime($searchData['orderYMD'][0]);
        $eDate = new DateTime($searchData['orderYMD'][1]);
        $dateDiff = date_diff($sDate, $eDate);
        if ($dateDiff->days > 90) {
            throw new \Exception(__('검색 가능 일은 최대 90일 입니다.'));
        }
        if ($searchData['orderYMD'][0] > $searchData['orderYMD'][1]) {
            throw new \Exception(__('시작일이 종료일보다 클 수 없습니다.'));
        }

        // 오늘 검색에 따른 당일 통계
        $todayDate = new DateTime();
        if ($eDate->format('Ymd') >= $todayDate->format('Ymd')) {
            $this->realTimeStatistics();
        }

        $order['orderYMD'][0] = $sDate->format('Ymd');
        $order['orderYMD'][1] = $eDate->format('Ymd');
        $order['mallSno'] = $searchData['mallSno'];
        $order['sort'] = 'oss.orderYMD asc';

        $orderData = $this->getOrderStatisticsInfo($order, null, null, true);

        $settleArea = $this->getOrderSettleArea();

        // 시간 매출 통계 데이터 초기화
        $orderDevice = ['pc', 'mobile', 'write'];
        for ($i = 0; $i <= 23; $i++) {
            $hour = sprintf("%02d", $i);
            foreach ($orderDevice as $deviceVal) {
                foreach ($settleArea as $settleKey => $settleVal) {
                    $returnOrderData[$hour][$deviceVal][$settleVal] = 0;
                }
            }
        }

        foreach ($orderData as $key => $val) {
            $hour = sprintf("%02d", $val['orderHour']);
            $goodsTotal = $val['goodsPrice'] - $val['goodsDcPrice'];
            $deliveryTotal = $val['deliveryPrice'] - $val['deliveryDcPrice'];
            $refundGoodsTotal = 0;
            $refundDeliveryTotal = 0;
            if ($val['type'] == 'goods') {
                $refundGoodsTotal = $val['refundGoodsPrice'] + $val['refundUseDeposit'] + $val['refundUseMileage'];
            } else if ($val['type'] == 'delivery') {
                $refundDeliveryTotal = $val['refundDeliveryPrice'] + $val['refundUseDeposit'] + $val['refundUseMileage'];
            }
            $refundTotal = $refundGoodsTotal + $refundDeliveryTotal - $val['refundFeePrice'];
            $returnOrderData[$hour][$val['orderDevice']][$settleArea[$val['orderArea']]] += $goodsTotal + $deliveryTotal - $refundTotal;
        }

        return $returnOrderData;
    }

    /**
     * 매출통계 - 지역별 - 요일별
     * getOrderSalesAreaWeek
     *
     * @param $searchData   orderYMD / mallSno / kind / type / scmNo / .... / sort
     *
     * @return array
     * @throws \Exception
     */
    public function getOrderSalesAreaWeek($searchData)
    {
        $sDate = new DateTime($searchData['orderYMD'][0]);
        $eDate = new DateTime($searchData['orderYMD'][1]);
        $dateDiff = date_diff($sDate, $eDate);
        if ($dateDiff->days > 90) {
            throw new \Exception(__('검색 가능 일은 최대 90일 입니다.'));
        }
        if ($searchData['orderYMD'][0] > $searchData['orderYMD'][1]) {
            throw new \Exception(__('시작일이 종료일보다 클 수 없습니다.'));
        }

        // 오늘 검색에 따른 당일 통계
        $todayDate = new DateTime();
        if ($eDate->format('Ymd') >= $todayDate->format('Ymd')) {
            $this->realTimeStatistics();
        }

        $order['orderYMD'][0] = $sDate->format('Ymd');
        $order['orderYMD'][1] = $eDate->format('Ymd');
        $order['mallSno'] = $searchData['mallSno'];
        $order['sort'] = 'oss.orderYMD asc';

        $orderData = $this->getOrderStatisticsInfo($order, null, null, true);

        $settleArea = $this->getOrderSettleArea();

        // 요일 매출 통계 데이터 초기화
        $orderDevice = ['pc', 'mobile', 'write'];
        for ($i = 0; $i <= 6; $i++) {
            foreach ($orderDevice as $deviceVal) {
                foreach ($settleArea as $settleKey => $settleVal) {
                    $returnOrderData[$i][$deviceVal][$settleVal] = 0;
                }
            }
        }

        foreach ($orderData as $key => $val) {
            $orderDt = new DateTime($val['orderYMD']);
            $week = $orderDt->format('w');
            $goodsTotal = $val['goodsPrice'] - $val['goodsDcPrice'];
            $deliveryTotal = $val['deliveryPrice'] - $val['deliveryDcPrice'];
            $refundGoodsTotal = 0;
            $refundDeliveryTotal = 0;
            if ($val['type'] == 'goods') {
                $refundGoodsTotal = $val['refundGoodsPrice'] + $val['refundUseDeposit'] + $val['refundUseMileage'];
            } else if ($val['type'] == 'delivery') {
                $refundDeliveryTotal = $val['refundDeliveryPrice'] + $val['refundUseDeposit'] + $val['refundUseMileage'];
            }
            $refundTotal = $refundGoodsTotal + $refundDeliveryTotal - $val['refundFeePrice'];
            $returnOrderData[$week][$val['orderDevice']][$settleArea[$val['orderArea']]] += $goodsTotal + $deliveryTotal - $refundTotal;
        }

        return $returnOrderData;
    }

    /**
     * 매출통계 - 지역별 - 월별
     * getOrderSalesAreaMonth
     *
     * @param $searchData   orderYMD / mallSno / kind / type / scmNo / .... / sort
     *
     * @return array
     * @throws \Exception
     */
    public function getOrderSalesAreaMonth($searchData)
    {
        $sDate = new DateTime($searchData['orderYMD'][0]);
        $eDate = new DateTime($searchData['orderYMD'][1]);
        $dateDiff = date_diff($sDate, $eDate);
        if ($dateDiff->days > 360) {
            throw new \Exception(__('검색 가능 일은 최대 12개월 입니다.'));
        }
        if ($searchData['orderYMD'][0] > $searchData['orderYMD'][1]) {
            throw new \Exception(__('시작일이 종료일보다 클 수 없습니다.'));
        }

        // 오늘 검색에 따른 당일 통계
        $todayDate = new DateTime();
        if ($eDate->format('Ymd') >= $todayDate->format('Ymd')) {
            $this->realTimeStatistics();
        }

        $order['orderYMD'][0] = $sDate->format('Ymd');
        $order['orderYMD'][1] = $eDate->format('Ymd');
        $order['mallSno'] = $searchData['mallSno'];
        $order['sort'] = 'oss.orderYMD asc';

        $orderData = $this->getOrderStatisticsInfo($order, null, null, true,true);

        $settleArea = $this->getOrderSettleArea();

        // 월 매출 통계 데이터 초기화
        $searchDt = new DateTime($order['orderYMD'][0]);
        $endDate = new DateTime($order['orderYMD'][1]);
        $orderDevice = ['pc', 'mobile', 'write'];
        $returnOrderData = [];
        for ($i = 0; $i <= 13; $i++) {
            if ($i > 0) {
                $lastDay = $searchDt->format('t');
                $searchDt = $searchDt->modify('+' . $lastDay . ' day');
            }

            if ($searchDt->format('Ym') <= $endDate->format('Ym')) {
                foreach ($orderDevice as $deviceVal) {
                    foreach ($settleArea as $settleKey => $settleVal) {
                        $returnOrderData[$searchDt->format('Ym')][$deviceVal][$settleVal] = 0;
                    }
                }
            }
        }

        foreach ($orderData as $key => $val) {
            $orderDt = new DateTime($val['orderYMD']);
            $month = $orderDt->format('Ym');
            $goodsTotal = $val['goodsPrice'] - $val['goodsDcPrice'];
            $deliveryTotal = $val['deliveryPrice'] - $val['deliveryDcPrice'];
            $refundGoodsTotal = 0;
            $refundDeliveryTotal = 0;
            if ($val['type'] == 'goods') {
                $refundGoodsTotal = $val['refundGoodsPrice'] + $val['refundUseDeposit'] + $val['refundUseMileage'];
            } else if ($val['type'] == 'delivery') {
                $refundDeliveryTotal = $val['refundDeliveryPrice'] + $val['refundUseDeposit'] + $val['refundUseMileage'];
            }
            $refundTotal = $refundGoodsTotal + $refundDeliveryTotal - $val['refundFeePrice'];
            $returnOrderData[$month][$val['orderDevice']][$settleArea[$val['orderArea']]] += $goodsTotal + $deliveryTotal - $refundTotal;
        }

        return $returnOrderData;
    }

    /**
     * 주문통계 - 일별
     * getOrderDay
     *
     * @param $searchData   orderYMD / mallSno / kind / type / scmNo / .... / sort
     *
     * @return array
     * @throws \Exception
     */
    public function getOrderDay($searchData)
    {
        $sDate = new DateTime($searchData['orderYMD'][0]);
        $eDate = new DateTime($searchData['orderYMD'][1]);
        $dateDiff = date_diff($sDate, $eDate);
        if ($dateDiff->days > 90) {
            throw new \Exception(__('검색 가능 일은 최대 90일 입니다.'));
        }
        if ($searchData['orderYMD'][0] > $searchData['orderYMD'][1]) {
            throw new \Exception(__('시작일이 종료일보다 클 수 없습니다.'));
        }

        // 오늘 검색에 따른 당일 통계
        $todayDate = new DateTime();
        if ($eDate->format('Ymd') >= $todayDate->format('Ymd')) {
            $this->realTimeStatistics();
        }

        if ($searchData['mallSno'] != 'all') {
            $order['mallSno'] = $searchData['mallSno'];
        }
        if ($searchData['scmNo'] > 0) {
            $order['scmNo'] = $searchData['scmNo'];
        }

        $order['orderYMD'][0] = $sDate->format('Ymd');
        $order['orderYMD'][1] = $eDate->format('Ymd');
        $order['kind'] = 'order';
        $order['type'] = 'goods';
        $order['sort'] = 'oss.orderYMD asc, oss.orderIP asc';

        $orderData = $this->getOrderStatisticsInfo($order, null, null, true,true);

        // 일별 매출 통계 데이터 초기화
        $startDate = new DateTime($order['orderYMD'][0]);
        $endDate = new DateTime($order['orderYMD'][1]);
        $diffDay = $endDate->diff($startDate)->days;
        $orderDevice = ['pc', 'mobile', 'write'];
        $returnOrderData = [];
        for ($i = 0; $i <= $diffDay; $i++) {
            $searchDt = new DateTime($order['orderYMD'][0]);
            $searchDt = $searchDt->modify('+' . $i . ' day');
            foreach ($orderDevice as $deviceVal) {
                $returnOrderData[$searchDt->format('Ymd')][$deviceVal]['goodsPrice'] = 0;
                $returnOrderData[$searchDt->format('Ymd')][$deviceVal]['goodsCnt'] = 0;
                $returnOrderData[$searchDt->format('Ymd')][$deviceVal]['orderCnt'] = 0;
                $returnOrderData[$searchDt->format('Ymd')][$deviceVal]['memberCnt'] = 0;
            }
        }

        $tmp = [];
        $i = 0;
        $tmpMemNo = 0;
        foreach ($orderData as $key => $val) {
            if ($val['memNo'] == 0) {
                if ($tmp[$val['orderYMD']][$val['orderDevice']]['orderIP'] != $val['orderIP']) {
                    $memberNo = 'no' . $i;
                    $tmpMemNo = 'no' . $i;
                } else {
                    $memberNo = $tmpMemNo;
                }
            } else {
                $memberNo = $val['memNo'];
            }

            $returnOrderData[$val['orderYMD']][$val['orderDevice']]['goodsPrice'] += $val['goodsPrice'];
            $returnOrderData[$val['orderYMD']][$val['orderDevice']]['goodsCnt'] += $val['goodsCnt'];
            $returnOrderData[$val['orderYMD']][$val['orderDevice']]['orderNo'][] = $val['orderNo'];
            $returnOrderData[$val['orderYMD']][$val['orderDevice']]['memNo'][] = $memberNo;
            $tmp[$val['orderYMD']][$val['orderDevice']]['orderIP'] = $val['orderIP'];
            $i++;
        }

        return $returnOrderData;
    }

    /**
     * 주문통계 - 시간별
     * getOrderHour
     *
     * @param $searchData   orderYMD / mallSno / kind / type / scmNo / .... / sort
     *
     * @return array
     * @throws \Exception
     */
    public function getOrderHour($searchData)
    {
        $sDate = new DateTime($searchData['orderYMD'][0]);
        $eDate = new DateTime($searchData['orderYMD'][1]);
        $dateDiff = date_diff($sDate, $eDate);
        if ($dateDiff->days > 90) {
            throw new \Exception(__('검색 가능 일은 최대 90일 입니다.'));
        }
        if ($searchData['orderYMD'][0] > $searchData['orderYMD'][1]) {
            throw new \Exception(__('시작일이 종료일보다 클 수 없습니다.'));
        }

        // 오늘 검색에 따른 당일 통계
        $todayDate = new DateTime();
        if ($eDate->format('Ymd') >= $todayDate->format('Ymd')) {
            $this->realTimeStatistics();
        }

        if ($searchData['mallSno'] != 'all') {
            $order['mallSno'] = $searchData['mallSno'];
        }
        if ($searchData['scmNo'] > 0) {
            $order['scmNo'] = $searchData['scmNo'];
        }

        $order['orderYMD'][0] = $sDate->format('Ymd');
        $order['orderYMD'][1] = $eDate->format('Ymd');
        $order['kind'] = 'order';
        $order['type'] = 'goods';
        $order['sort'] = 'oss.orderYMD asc, oss.orderIP asc';

        $orderData = $this->getOrderStatisticsInfo($order, null, null, true);

        // 일별 매출 통계 데이터 초기화
        $orderDevice = ['pc', 'mobile', 'write'];
        $returnOrderData = [];
        for ($i = 0; $i <= 23; $i++) {
            $hour = sprintf("%02d", $i);
            foreach ($orderDevice as $deviceVal) {
                $returnOrderData[$hour][$deviceVal]['goodsPrice'] = 0;
                $returnOrderData[$hour][$deviceVal]['goodsCnt'] = 0;
                $returnOrderData[$hour][$deviceVal]['orderCnt'] = 0;
                $returnOrderData[$hour][$deviceVal]['memberCnt'] = 0;
            }
        }

        $tmp = [];
        $i = 0;
        $tmpMemNo = 0;
        foreach ($orderData as $key => $val) {
            $hour = sprintf("%02d", $val['orderHour']);
            if ($val['memNo'] == 0) {
                if ($tmp[$hour][$val['orderDevice']]['orderIP'] != $val['orderIP']) {
                    $memberNo = 'no' . $i;
                    $tmpMemNo = 'no' . $i;
                } else {
                    $memberNo = $tmpMemNo;
                }
            } else {
                $memberNo = $val['memNo'];
            }
            $returnOrderData[$hour][$val['orderDevice']]['goodsPrice'] += $val['goodsPrice'];
            $returnOrderData[$hour][$val['orderDevice']]['goodsCnt'] += $val['goodsCnt'];
            $returnOrderData[$hour][$val['orderDevice']]['orderNo'][] = $val['orderNo'];
            $returnOrderData[$hour][$val['orderDevice']]['memNo'][] = $memberNo;
            $tmp[$hour][$val['orderDevice']]['orderIP'] = $val['orderIP'];
            $i++;
        }

        return $returnOrderData;
    }

    /**
     * 주문통계 - 요일별
     * getOrderWeek
     *
     * @param $searchData   orderYMD / mallSno / kind / type / scmNo / .... / sort
     *
     * @return array
     * @throws \Exception
     */
    public function getOrderWeek($searchData)
    {
        $sDate = new DateTime($searchData['orderYMD'][0]);
        $eDate = new DateTime($searchData['orderYMD'][1]);
        $dateDiff = date_diff($sDate, $eDate);
        if ($dateDiff->days > 90) {
            throw new \Exception(__('검색 가능 일은 최대 90일 입니다.'));
        }
        if ($searchData['orderYMD'][0] > $searchData['orderYMD'][1]) {
            throw new \Exception(__('시작일이 종료일보다 클 수 없습니다.'));
        }

        // 오늘 검색에 따른 당일 통계
        $todayDate = new DateTime();
        if ($eDate->format('Ymd') >= $todayDate->format('Ymd')) {
            $this->realTimeStatistics();
        }

        if ($searchData['mallSno'] != 'all') {
            $order['mallSno'] = $searchData['mallSno'];
        }
        if ($searchData['scmNo'] > 0) {
            $order['scmNo'] = $searchData['scmNo'];
        }

        $order['orderYMD'][0] = $sDate->format('Ymd');
        $order['orderYMD'][1] = $eDate->format('Ymd');
        $order['kind'] = 'order';
        $order['type'] = 'goods';
        $order['sort'] = 'oss.orderYMD asc, oss.orderIP asc';

        $orderData = $this->getOrderStatisticsInfo($order, null, null, true);

        // 일별 매출 통계 데이터 초기화
        $orderDevice = ['pc', 'mobile', 'write'];
        $returnOrderData = [];
        for ($i = 0; $i <= 6; $i++) {
            foreach ($orderDevice as $deviceVal) {
                $returnOrderData[$i][$deviceVal]['goodsPrice'] = 0;
                $returnOrderData[$i][$deviceVal]['goodsCnt'] = 0;
                $returnOrderData[$i][$deviceVal]['orderCnt'] = 0;
                $returnOrderData[$i][$deviceVal]['memberCnt'] = 0;
            }
        }

        $tmp = [];
        $i = 0;
        $tmpMemNo = 0;
        foreach ($orderData as $key => $val) {
            $orderDt = new DateTime($val['orderYMD']);
            $week = $orderDt->format('w');
            if ($val['memNo'] == 0) {
                if ($tmp[$week][$val['orderDevice']]['orderIP'] != $val['orderIP']) {
                    $memberNo = 'no' . $i;
                    $tmpMemNo = 'no' . $i;
                } else {
                    $memberNo = $tmpMemNo;
                }
            } else {
                $memberNo = $val['memNo'];
            }
            $returnOrderData[$week][$val['orderDevice']]['goodsPrice'] += $val['goodsPrice'];
            $returnOrderData[$week][$val['orderDevice']]['goodsCnt'] += $val['goodsCnt'];
            $returnOrderData[$week][$val['orderDevice']]['orderNo'][] = $val['orderNo'];
            $returnOrderData[$week][$val['orderDevice']]['memNo'][] = $memberNo;
            $tmp[$week][$val['orderDevice']]['orderIP'] = $val['orderIP'];
            $i++;
        }

        return $returnOrderData;
    }

    /**
     * 주문통계 - 월별
     * getOrderMonth
     *
     * @param $searchData   orderYMD / mallSno / kind / type / scmNo / .... / sort
     *
     * @return array
     * @throws \Exception
     */
    public function getOrderMonth($searchData)
    {
        $sDate = new DateTime($searchData['orderYMD'][0]);
        $eDate = new DateTime($searchData['orderYMD'][1]);
        $dateDiff = date_diff($sDate, $eDate);
        if ($dateDiff->days > 360) {
            throw new \Exception(__('검색 가능 일은 최대 12개월 입니다.'));
        }
        if ($searchData['orderYMD'][0] > $searchData['orderYMD'][1]) {
            throw new \Exception(__('시작일이 종료일보다 클 수 없습니다.'));
        }

        // 오늘 검색에 따른 당일 통계
        $todayDate = new DateTime();
        if ($eDate->format('Ymd') >= $todayDate->format('Ymd')) {
            $this->realTimeStatistics();
        }

        if ($searchData['mallSno'] != 'all') {
            $order['mallSno'] = $searchData['mallSno'];
        }
        if ($searchData['scmNo'] > 0) {
            $order['scmNo'] = $searchData['scmNo'];
        }

        $order['orderYMD'][0] = $sDate->format('Ym') . '01';
        $order['orderYMD'][1] = $eDate->format('Ymt');
        $order['kind'] = 'order';
        $order['type'] = 'goods';
        $order['sort'] = 'oss.orderYMD asc, oss.orderIP asc';

        $orderData = $this->getOrderStatisticsInfo($order, null, null, true,true);

        // 월별 매출 통계 데이터 초기화
        $searchDt = new DateTime($order['orderYMD'][0]);
        $endDate = new DateTime($order['orderYMD'][1]);
        $orderDevice = ['pc', 'mobile', 'write'];
        $returnOrderData = [];
        for ($i = 0; $i <= 13; $i++) {
            if ($i > 0) {
                $lastDay = $searchDt->format('t');
                $searchDt = $searchDt->modify('+' . $lastDay . ' day');
            }
            if ($searchDt->format('Ym') <= $endDate->format('Ym')) {
                foreach ($orderDevice as $deviceVal) {
                    $returnOrderData[$searchDt->format('Ym')][$deviceVal]['goodsPrice'] = 0;
                    $returnOrderData[$searchDt->format('Ym')][$deviceVal]['goodsCnt'] = 0;
                    $returnOrderData[$searchDt->format('Ym')][$deviceVal]['orderCnt'] = 0;
                    $returnOrderData[$searchDt->format('Ym')][$deviceVal]['memberCnt'] = 0;
                }
            }
        }

        $tmp = [];
        $i = 0;
        $tmpMemNo = 0;
        foreach ($orderData as $key => $val) {
            $orderDt = new DateTime($val['orderYMD']);
            $month = $orderDt->format('Ym');
            if ($val['memNo'] == 0) {
                if ($tmp[$month][$val['orderDevice']]['orderIP'] != $val['orderIP']) {
                    $memberNo = 'no' . $i;
                    $tmpMemNo = 'no' . $i;
                } else {
                    $memberNo = $tmpMemNo;
                }
            } else {
                $memberNo = $val['memNo'];
            }
            $returnOrderData[$month][$val['orderDevice']]['goodsPrice'] += $val['goodsPrice'];
            $returnOrderData[$month][$val['orderDevice']]['goodsCnt'] += $val['goodsCnt'];
            $returnOrderData[$month][$val['orderDevice']]['orderNo'][] = $val['orderNo'];
            $returnOrderData[$month][$val['orderDevice']]['memNo'][] = $memberNo;
            $tmp[$month][$val['orderDevice']]['orderIP'] = $val['orderIP'];
            $i++;
        }

        return $returnOrderData;
    }

    /**
     * 주문통계 - 회원별
     * getOrderMember
     *
     * @param $searchData   orderYMD / mallSno / kind / type / scmNo / .... / sort
     *
     * @return array
     * @throws \Exception
     */
    public function getOrderMember($searchData)
    {
        $sDate = new DateTime($searchData['orderYMD'][0]);
        $eDate = new DateTime($searchData['orderYMD'][1]);
        $dateDiff = date_diff($sDate, $eDate);
        if ($dateDiff->days > 90) {
            throw new \Exception(__('검색 가능 일은 최대 90일 입니다.'));
        }
        if ($searchData['orderYMD'][0] > $searchData['orderYMD'][1]) {
            throw new \Exception(__('시작일이 종료일보다 클 수 없습니다.'));
        }

        // 오늘 검색에 따른 당일 통계
        $todayDate = new DateTime();
        if ($eDate->format('Ymd') >= $todayDate->format('Ymd')) {
            $this->realTimeStatistics();
        }

        if ($searchData['mallSno'] != 'all') {
            $order['mallSno'] = $searchData['mallSno'];
        }
        if ($searchData['scmNo'] > 0) {
            $order['scmNo'] = $searchData['scmNo'];
        }

        $order['orderYMD'][0] = $sDate->format('Ymd');
        $order['orderYMD'][1] = $eDate->format('Ymd');
        $order['kind'] = 'order';
        $order['type'] = 'goods';
        $order['sort'] = 'oss.orderYMD asc';

        $orderData = $this->getOrderStatisticsInfo($order, null, null, true);

        // 일별 매출 통계 데이터 초기화
        $startDate = new DateTime($order['orderYMD'][0]);
        $endDate = new DateTime($order['orderYMD'][1]);
        $diffDay = $endDate->diff($startDate)->days;
        $orderDevice = ['pc', 'mobile', 'write'];
        $returnOrderData = [];
        for ($i = 0; $i <= $diffDay; $i++) {
            $searchDt = new DateTime($order['orderYMD'][0]);
            $searchDt = $searchDt->modify('+' . $i . ' day');
            foreach ($orderDevice as $deviceVal) {
                $returnOrderData[$searchDt->format('Ymd')]['y'][$deviceVal]['goodsPrice'] = 0;
                $returnOrderData[$searchDt->format('Ymd')]['y'][$deviceVal]['goodsCnt'] = 0;
                $returnOrderData[$searchDt->format('Ymd')]['y'][$deviceVal]['orderCnt'] = 0;
                $returnOrderData[$searchDt->format('Ymd')]['y'][$deviceVal]['memberCnt'] = 0;
                $returnOrderData[$searchDt->format('Ymd')]['n'][$deviceVal]['goodsPrice'] = 0;
                $returnOrderData[$searchDt->format('Ymd')]['n'][$deviceVal]['goodsCnt'] = 0;
                $returnOrderData[$searchDt->format('Ymd')]['n'][$deviceVal]['orderCnt'] = 0;
                $returnOrderData[$searchDt->format('Ymd')]['n'][$deviceVal]['memberCnt'] = 0;
            }
        }

        foreach ($orderData as $key => $val) {
            if ($val['orderMemberFl'] == 'y') {
                $memCode = $val['memNo'];
            } else if ($val['orderMemberFl'] == 'n') {
                $memCode = $val['orderIP'];
            }
            $returnOrderData[$val['orderYMD']][$val['orderMemberFl']][$val['orderDevice']]['goodsPrice'] += $val['goodsPrice'];
            $returnOrderData[$val['orderYMD']][$val['orderMemberFl']][$val['orderDevice']]['goodsCnt'] += $val['goodsCnt'];
            $returnOrderData[$val['orderYMD']][$val['orderMemberFl']][$val['orderDevice']]['orderNo'][] = $val['orderNo'];
            $returnOrderData[$val['orderYMD']][$val['orderMemberFl']][$val['orderDevice']]['memNo'][] = $memCode;
        }

        return $returnOrderData;
    }

    /**
     * 주문통계 - 연령 - 일별
     * getOrderAgeDay
     *
     * @param $searchData   orderYMD / mallSno / kind / type / scmNo / .... / sort
     *
     * @return array
     * @throws \Exception
     */
    public function getOrderAgeDay($searchData)
    {
        $sDate = new DateTime($searchData['orderYMD'][0]);
        $eDate = new DateTime($searchData['orderYMD'][1]);
        $dateDiff = date_diff($sDate, $eDate);
        if ($dateDiff->days > 90) {
            throw new \Exception(__('검색 가능 일은 최대 90일 입니다.'));
        }
        if ($searchData['orderYMD'][0] > $searchData['orderYMD'][1]) {
            throw new \Exception(__('시작일이 종료일보다 클 수 없습니다.'));
        }

        // 오늘 검색에 따른 당일 통계
        $todayDate = new DateTime();
        if ($eDate->format('Ymd') >= $todayDate->format('Ymd')) {
            $this->realTimeStatistics();
        }

        if ($searchData['mallSno'] != 'all') {
            $order['mallSno'] = $searchData['mallSno'];
        }

        $order['orderYMD'][0] = $sDate->format('Ymd');
        $order['orderYMD'][1] = $eDate->format('Ymd');
        $order['kind'] = 'order';
        $order['type'] = 'goods';
        $order['sort'] = 'oss.orderYMD asc, oss.orderIP asc';

        $orderData = $this->getOrderStatisticsInfo($order, null, null, true);

        // 일별 매출 통계 데이터 초기화
        $startDate = new DateTime($order['orderYMD'][0]);
        $endDate = new DateTime($order['orderYMD'][1]);
        $diffDay = $endDate->diff($startDate)->days;
        $orderDevice = ['pc', 'mobile', 'write'];
        $returnOrderData = [];
        for ($i = 0; $i <= $diffDay; $i++) {
            $searchDt = new DateTime($order['orderYMD'][0]);
            $searchDt = $searchDt->modify('+' . $i . ' day');
            foreach ($orderDevice as $deviceVal) {
                for ($j = 10; $j <= 70; $j += 10) {
                    $returnOrderData[$searchDt->format('Ymd')][$deviceVal][$j]['goodsPrice'] = 0;
                    $returnOrderData[$searchDt->format('Ymd')][$deviceVal][$j]['goodsCnt'] = 0;
                    $returnOrderData[$searchDt->format('Ymd')][$deviceVal][$j]['orderCnt'] = 0;
                    $returnOrderData[$searchDt->format('Ymd')][$deviceVal][$j]['memberCnt'] = 0;
                }
                $returnOrderData[$searchDt->format('Ymd')][$deviceVal]['etc']['goodsPrice'] = 0;
                $returnOrderData[$searchDt->format('Ymd')][$deviceVal]['etc']['goodsCnt']  = 0;
                $returnOrderData[$searchDt->format('Ymd')][$deviceVal]['etc']['orderCnt']  = 0;
                $returnOrderData[$searchDt->format('Ymd')][$deviceVal]['etc']['memberCnt'] = 0;
            }
        }

        $tmp = [];
        $i = 0;
        $tmpMemNo = 0;
        foreach ($orderData as $key => $val) {
            if ($val['orderAge'] == 0 || $val['orderAge'] == 127) { // 생일이 없으면 디비필드 tinyint 최대값인 127 값 / 비회원이면 0
                $age = 'etc';
            } else if ($val['orderAge'] < 20) {
                $age = 10;
            } else if ($val['orderAge'] >= 70 && $val['orderAge'] <= 126) { // 70~126 세는 70대
                $age = '70';
            } else {
                $age = $val['orderAge'];
            }
            if ($val['memNo'] == 0) {
                if ($tmp[$val['orderYMD']][$val['orderDevice']][$age]['orderIP'] != $val['orderIP']) {
                    $memberNo = 'no' . $i;
                    $tmpMemNo = 'no' . $i;
                } else {
                    $memberNo = $tmpMemNo;
                }
            } else {
                $memberNo = $val['memNo'];
            }
            $returnOrderData[$val['orderYMD']][$val['orderDevice']][$age]['goodsPrice'] += $val['goodsPrice'];
            $returnOrderData[$val['orderYMD']][$val['orderDevice']][$age]['goodsCnt'] += $val['goodsCnt'];
            $returnOrderData[$val['orderYMD']][$val['orderDevice']][$age]['orderNo'][] = $val['orderNo'];
            $returnOrderData[$val['orderYMD']][$val['orderDevice']][$age]['memNo'][] = $memberNo;
            $tmp[$val['orderYMD']][$val['orderDevice']][$age]['orderIP'] = $val['orderIP'];
            $i++;
        }

        return $returnOrderData;
    }

    /**
     * 주문통계 - 연령 - 시간별
     * getOrderAgeHour
     *
     * @param $searchData   orderYMD / mallSno / kind / type / scmNo / .... / sort
     *
     * @return array
     * @throws \Exception
     */
    public function getOrderAgeHour($searchData)
    {
        $sDate = new DateTime($searchData['orderYMD'][0]);
        $eDate = new DateTime($searchData['orderYMD'][1]);
        $dateDiff = date_diff($sDate, $eDate);
        if ($dateDiff->days > 90) {
            throw new \Exception(__('검색 가능 일은 최대 90일 입니다.'));
        }
        if ($searchData['orderYMD'][0] > $searchData['orderYMD'][1]) {
            throw new \Exception(__('시작일이 종료일보다 클 수 없습니다.'));
        }

        // 오늘 검색에 따른 당일 통계
        $todayDate = new DateTime();
        if ($eDate->format('Ymd') >= $todayDate->format('Ymd')) {
            $this->realTimeStatistics();
        }

        if ($searchData['mallSno'] != 'all') {
            $order['mallSno'] = $searchData['mallSno'];
        }

        $order['orderYMD'][0] = $sDate->format('Ymd');
        $order['orderYMD'][1] = $eDate->format('Ymd');
        $order['kind'] = 'order';
        $order['type'] = 'goods';
        $order['sort'] = 'oss.orderYMD asc, oss.orderIP asc';

        $orderData = $this->getOrderStatisticsInfo($order, null, null, true);

        // 시간별 매출 통계 데이터 초기화
        $orderDevice = ['pc', 'mobile', 'write'];
        for ($i = 0; $i <= 23; $i++) {
            $hour = sprintf("%02d", $i);
            foreach ($orderDevice as $deviceVal) {
                for ($j = 10; $j <= 70; $j += 10) {
                    $returnOrderData[$hour][$deviceVal][$j]['goodsPrice'] = 0;
                    $returnOrderData[$hour][$deviceVal][$j]['goodsCnt'] = 0;
                    $returnOrderData[$hour][$deviceVal][$j]['orderCnt'] = 0;
                    $returnOrderData[$hour][$deviceVal][$j]['memberCnt'] = 0;
                }
                $returnOrderData[$hour][$deviceVal]['etc']['goodsPrice'] = 0;
                $returnOrderData[$hour][$deviceVal]['etc']['goodsCnt'] = 0;
                $returnOrderData[$hour][$deviceVal]['etc']['orderCnt'] = 0;
                $returnOrderData[$hour][$deviceVal]['etc']['memberCnt'] = 0;
            }
        }

        $tmp = [];
        $i = 0;
        $tmpMemNo = 0;
        foreach ($orderData as $key => $val) {
            $hour = sprintf("%02d", $val['orderHour']);
            if ($val['orderAge'] == 0 || $val['orderAge'] == 127) { // 생일이 없으면 디비필드 tinyint 최대값인 127 값 / 비회원이면 0
                $age = 'etc';
            } else if ($val['orderAge'] < 20) {
                $age = 10;
            } else if ($val['orderAge'] >= 70 && $val['orderAge'] <= 126) { // 70~126 세는 70대
                $age = '70';
            } else {
                $age = $val['orderAge'];
            }
            if ($val['memNo'] == 0) {
                if ($tmp[$hour][$val['orderDevice']][$age]['orderIP'] != $val['orderIP']) {
                    $memberNo = 'no' . $i;
                    $tmpMemNo = 'no' . $i;
                } else {
                    $memberNo = $tmpMemNo;
                }
            } else {
                $memberNo = $val['memNo'];
            }
            $returnOrderData[$hour][$val['orderDevice']][$age]['goodsPrice'] += $val['goodsPrice'];
            $returnOrderData[$hour][$val['orderDevice']][$age]['goodsCnt'] += $val['goodsCnt'];
            $returnOrderData[$hour][$val['orderDevice']][$age]['orderNo'][] = $val['orderNo'];
            $returnOrderData[$hour][$val['orderDevice']][$age]['memNo'][] = $memberNo;
            $tmp[$hour][$val['orderDevice']][$age]['orderIP'] = $val['orderIP'];
            $i++;
        }

        return $returnOrderData;
    }

    /**
     * 주문통계 - 연령 - 요일별
     * getOrderAgeWeek
     *
     * @param $searchData   orderYMD / mallSno / kind / type / scmNo / .... / sort
     *
     * @return array
     * @throws \Exception
     */
    public function getOrderAgeWeek($searchData)
    {
        $sDate = new DateTime($searchData['orderYMD'][0]);
        $eDate = new DateTime($searchData['orderYMD'][1]);
        $dateDiff = date_diff($sDate, $eDate);
        if ($dateDiff->days > 90) {
            throw new \Exception(__('검색 가능 일은 최대 90일 입니다.'));
        }
        if ($searchData['orderYMD'][0] > $searchData['orderYMD'][1]) {
            throw new \Exception(__('시작일이 종료일보다 클 수 없습니다.'));
        }

        // 오늘 검색에 따른 당일 통계
        $todayDate = new DateTime();
        if ($eDate->format('Ymd') >= $todayDate->format('Ymd')) {
            $this->realTimeStatistics();
        }

        if ($searchData['mallSno'] != 'all') {
            $order['mallSno'] = $searchData['mallSno'];
        }

        $order['orderYMD'][0] = $sDate->format('Ymd');
        $order['orderYMD'][1] = $eDate->format('Ymd');
        $order['kind'] = 'order';
        $order['type'] = 'goods';
        $order['sort'] = 'oss.orderYMD asc, oss.orderIP asc';

        $orderData = $this->getOrderStatisticsInfo($order, null, null, true);

        // 시간별 매출 통계 데이터 초기화
        $orderDevice = ['pc', 'mobile', 'write'];
        for ($i = 0; $i <= 6; $i++) {
            foreach ($orderDevice as $deviceVal) {
                for ($j = 10; $j <= 70; $j += 10) {
                    $returnOrderData[$i][$deviceVal][$j]['goodsPrice'] = 0;
                    $returnOrderData[$i][$deviceVal][$j]['goodsCnt'] = 0;
                    $returnOrderData[$i][$deviceVal][$j]['orderCnt'] = 0;
                    $returnOrderData[$i][$deviceVal][$j]['memberCnt'] = 0;
                }
                $returnOrderData[$i][$deviceVal]['etc']['goodsPrice'] = 0;
                $returnOrderData[$i][$deviceVal]['etc']['goodsCnt'] = 0;
                $returnOrderData[$i][$deviceVal]['etc']['orderCnt'] = 0;
                $returnOrderData[$i][$deviceVal]['etc']['memberCnt'] = 0;
            }
        }

        $tmp = [];
        $i = 0;
        $tmpMemNo = 0;
        foreach ($orderData as $key => $val) {
            $orderDt = new DateTime($val['orderYMD']);
            $week = $orderDt->format('w');
            if ($val['orderAge'] == 0 || $val['orderAge'] == 127) { // 생일이 없으면 디비필드 tinyint 최대값인 127 값 / 비회원이면 0
                $age = 'etc';
            } else if ($val['orderAge'] < 20) {
                $age = 10;
            } else if ($val['orderAge'] >= 70 && $val['orderAge'] <= 126) { // 70~126 세는 70대
                $age = '70';
            } else {
                $age = $val['orderAge'];
            }
            if ($val['memNo'] == 0) {
                if ($tmp[$week][$val['orderDevice']][$age]['orderIP'] != $val['orderIP']) {
                    $memberNo = 'no' . $i;
                    $tmpMemNo = 'no' . $i;
                } else {
                    $memberNo = $tmpMemNo;
                }
            } else {
                $memberNo = $val['memNo'];
            }
            $returnOrderData[$week][$val['orderDevice']][$age]['goodsPrice'] += $val['goodsPrice'];
            $returnOrderData[$week][$val['orderDevice']][$age]['goodsCnt'] += $val['goodsCnt'];
            $returnOrderData[$week][$val['orderDevice']][$age]['orderNo'][] = $val['orderNo'];
            $returnOrderData[$week][$val['orderDevice']][$age]['memNo'][] = $memberNo;
            $tmp[$week][$val['orderDevice']][$age]['orderIP'] = $val['orderIP'];
            $i++;
        }

        return $returnOrderData;
    }

    /**
     * 주문통계 - 연령 - 월별
     * getOrderAgeMonth
     *
     * @param $searchData   orderYMD / mallSno / kind / type / scmNo / .... / sort
     *
     * @return array
     * @throws \Exception
     */
    public function getOrderAgeMonth($searchData)
    {
        $sDate = new DateTime($searchData['orderYMD'][0]);
        $eDate = new DateTime($searchData['orderYMD'][1]);
        $dateDiff = date_diff($sDate, $eDate);
        if ($dateDiff->days > 360) {
            throw new \Exception(__('검색 가능 일은 최대 12개월 입니다.'));
        }
        if ($searchData['orderYMD'][0] > $searchData['orderYMD'][1]) {
            throw new \Exception(__('시작일이 종료일보다 클 수 없습니다.'));
        }

        // 오늘 검색에 따른 당일 통계
        $todayDate = new DateTime();
        if ($eDate->format('Ymd') >= $todayDate->format('Ymd')) {
            $this->realTimeStatistics();
        }

        if ($searchData['mallSno'] != 'all') {
            $order['mallSno'] = $searchData['mallSno'];
        }

        $order['orderYMD'][0] = $sDate->format('Ymd');
        $order['orderYMD'][1] = $eDate->format('Ymd');
        $order['kind'] = 'order';
        $order['type'] = 'goods';
        $order['sort'] = 'oss.orderYMD asc, oss.orderIP asc';

        $orderData = $this->getOrderStatisticsInfo($order, null, null, true,true);

        // 월별 매출 통계 데이터 초기화
        $searchDt = new DateTime($order['orderYMD'][0]);
        $endDate = new DateTime($order['orderYMD'][1]);
        $orderDevice = ['pc', 'mobile', 'write'];
        $returnOrderData = [];
        for ($i = 0; $i <= 13; $i++) {
            if ($i > 0) {
                $lastDay = $searchDt->format('t');
                $searchDt = $searchDt->modify('+' . $lastDay . ' day');
            }
            if ($searchDt->format('Ym') <= $endDate->format('Ym')) {
                foreach ($orderDevice as $deviceVal) {
                    for ($j = 10; $j <= 70; $j += 10) {
                        $returnOrderData[$searchDt->format('Ym')][$deviceVal][$j]['goodsPrice'] = 0;
                        $returnOrderData[$searchDt->format('Ym')][$deviceVal][$j]['goodsCnt'] = 0;
                        $returnOrderData[$searchDt->format('Ym')][$deviceVal][$j]['orderCnt'] = 0;
                        $returnOrderData[$searchDt->format('Ym')][$deviceVal][$j]['memberCnt'] = 0;
                    }
                    $returnOrderData[$searchDt->format('Ym')][$deviceVal]['etc']['goodsPrice'] = 0;
                    $returnOrderData[$searchDt->format('Ym')][$deviceVal]['etc']['goodsCnt'] = 0;
                    $returnOrderData[$searchDt->format('Ym')][$deviceVal]['etc']['orderCnt'] = 0;
                    $returnOrderData[$searchDt->format('Ym')][$deviceVal]['etc']['memberCnt'] = 0;
                }
            }
        }

        $tmp = [];
        $i = 0;
        $tmpMemNo = 0;
        foreach ($orderData as $key => $val) {
            $orderDt = new DateTime($val['orderYMD']);
            $month = $orderDt->format('Ym');
            if ($val['orderAge'] == 0 || $val['orderAge'] == 127) { // 생일이 없으면 디비필드 tinyint 최대값인 127 값 / 비회원이면 0
                $age = 'etc';
            } else if ($val['orderAge'] < 20) {
                $age = 10;
            } else if ($val['orderAge'] >= 70 && $val['orderAge'] <= 126) { // 70~126 세는 70대
                $age = '70';
            } else {
                $age = $val['orderAge'];
            }
            if ($val['memNo'] == 0) {
                if ($tmp[$month][$val['orderDevice']][$age]['orderIP'] != $val['orderIP']) {
                    $memberNo = 'no' . $i;
                    $tmpMemNo = 'no' . $i;
                } else {
                    $memberNo = $tmpMemNo;
                }
            } else {
                $memberNo = $val['memNo'];
            }
            $returnOrderData[$month][$val['orderDevice']][$age]['goodsPrice'] += $val['goodsPrice'];
            $returnOrderData[$month][$val['orderDevice']][$age]['goodsCnt'] += $val['goodsCnt'];
            $returnOrderData[$month][$val['orderDevice']][$age]['orderNo'][] = $val['orderNo'];
            $returnOrderData[$month][$val['orderDevice']][$age]['memNo'][] = $memberNo;
            $tmp[$month][$val['orderDevice']][$age]['orderIP'] = $val['orderIP'];
            $i++;
        }

        return $returnOrderData;
    }

    /**
     * 주문통계 - 지역별 - 일별
     * getOrderAreaDay
     *
     * @param $searchData   orderYMD / mallSno / kind / type / scmNo / .... / sort
     *
     * @return array
     * @throws \Exception
     */
    public function getOrderAreaDay($searchData)
    {
        $sDate = new DateTime($searchData['orderYMD'][0]);
        $eDate = new DateTime($searchData['orderYMD'][1]);
        $dateDiff = date_diff($sDate, $eDate);
        if ($dateDiff->days > 90) {
            throw new \Exception(__('검색 가능 일은 최대 90일 입니다.'));
        }
        if ($searchData['orderYMD'][0] > $searchData['orderYMD'][1]) {
            throw new \Exception(__('시작일이 종료일보다 클 수 없습니다.'));
        }

        // 오늘 검색에 따른 당일 통계
        $todayDate = new DateTime();
        if ($eDate->format('Ymd') >= $todayDate->format('Ymd')) {
            $this->realTimeStatistics();
        }

        $order['orderYMD'][0] = $sDate->format('Ymd');
        $order['orderYMD'][1] = $eDate->format('Ymd');
        $order['kind'] = 'order';
        $order['type'] = 'goods';
        $order['mallSno'] = $searchData['mallSno'];
        $order['sort'] = 'oss.orderYMD asc, oss.orderIP asc';

        $orderData = $this->getOrderStatisticsInfo($order, null, null, true);

        $settleArea = $this->getOrderSettleArea();

        // 일별 매출 통계 데이터 초기화
        $startDate = new DateTime($order['orderYMD'][0]);
        $endDate = new DateTime($order['orderYMD'][1]);
        $diffDay = $endDate->diff($startDate)->days;
        $orderDevice = ['pc', 'mobile', 'write'];
        $returnOrderData = [];
        for ($i = 0; $i <= $diffDay; $i++) {
            $searchDt = new DateTime($order['orderYMD'][0]);
            $searchDt = $searchDt->modify('+' . $i . ' day');
            foreach ($orderDevice as $deviceVal) {
                foreach ($settleArea as $settleKey => $settleVal) {
                    $returnOrderData[$searchDt->format('Ymd')][$deviceVal][$settleVal]['goodsPrice'] = 0;
                    $returnOrderData[$searchDt->format('Ymd')][$deviceVal][$settleVal]['goodsCnt'] = 0;
                    $returnOrderData[$searchDt->format('Ymd')][$deviceVal][$settleVal]['orderCnt'] = 0;
                    $returnOrderData[$searchDt->format('Ymd')][$deviceVal][$settleVal]['memberCnt'] = 0;
                }
            }
        }

        $tmp = [];
        $i = 0;
        $tmpMemNo = 0;
        foreach ($orderData as $key => $val) {
            if ($val['memNo'] == 0) {
                if ($tmp[$val['orderYMD']][$val['orderDevice']][$settleArea[$val['orderArea']]]['orderIP'] != $val['orderIP']) {
                    $memberNo = 'no' . $i;
                    $tmpMemNo = 'no' . $i;
                } else {
                    $memberNo = $tmpMemNo;
                }
            } else {
                $memberNo = $val['memNo'];
            }
            $returnOrderData[$val['orderYMD']][$val['orderDevice']][$settleArea[$val['orderArea']]]['goodsPrice'] += $val['goodsPrice'];
            $returnOrderData[$val['orderYMD']][$val['orderDevice']][$settleArea[$val['orderArea']]]['goodsCnt'] += $val['goodsCnt'];
            $returnOrderData[$val['orderYMD']][$val['orderDevice']][$settleArea[$val['orderArea']]]['orderNo'][] = $val['orderNo'];
            $returnOrderData[$val['orderYMD']][$val['orderDevice']][$settleArea[$val['orderArea']]]['memNo'][] = $memberNo;
            $tmp[$val['orderYMD']][$val['orderDevice']][$settleArea[$val['orderArea']]]['orderIP'] = $val['orderIP'];
            $i++;
        }

        return $returnOrderData;
    }

    /**
     * 주문통계 - 지역별 - 시간별
     * getOrderAreaHour
     *
     * @param $searchData   orderYMD / mallSno / kind / type / scmNo / .... / sort
     *
     * @return array
     * @throws \Exception
     */
    public function getOrderAreaHour($searchData)
    {
        $sDate = new DateTime($searchData['orderYMD'][0]);
        $eDate = new DateTime($searchData['orderYMD'][1]);
        $dateDiff = date_diff($sDate, $eDate);
        if ($dateDiff->days > 90) {
            throw new \Exception(__('검색 가능 일은 최대 90일 입니다.'));
        }
        if ($searchData['orderYMD'][0] > $searchData['orderYMD'][1]) {
            throw new \Exception(__('시작일이 종료일보다 클 수 없습니다.'));
        }

        // 오늘 검색에 따른 당일 통계
        $todayDate = new DateTime();
        if ($eDate->format('Ymd') >= $todayDate->format('Ymd')) {
            $this->realTimeStatistics();
        }

        $order['orderYMD'][0] = $sDate->format('Ymd');
        $order['orderYMD'][1] = $eDate->format('Ymd');
        $order['kind'] = 'order';
        $order['type'] = 'goods';
        $order['mallSno'] = $searchData['mallSno'];
        $order['sort'] = 'oss.orderYMD asc, oss.orderIP asc';

        $orderData = $this->getOrderStatisticsInfo($order, null, null, true);

        $settleArea = $this->getOrderSettleArea();

        // 시간 매출 통계 데이터 초기화
        $orderDevice = ['pc', 'mobile', 'write'];
        for ($i = 0; $i <= 23; $i++) {
            $hour = sprintf("%02d", $i);
            foreach ($orderDevice as $deviceVal) {
                foreach ($settleArea as $settleKey => $settleVal) {
                    $returnOrderData[$hour][$deviceVal][$settleVal]['goodsPrice'] = 0;
                    $returnOrderData[$hour][$deviceVal][$settleVal]['goodsCnt'] = 0;
                    $returnOrderData[$hour][$deviceVal][$settleVal]['orderCnt'] = 0;
                    $returnOrderData[$hour][$deviceVal][$settleVal]['memberCnt'] = 0;
                }
            }
        }

        $tmp = [];
        $i = 0;
        $tmpMemNo = 0;
        foreach ($orderData as $key => $val) {
            $hour = sprintf("%02d", $val['orderHour']);
            if ($val['memNo'] == 0) {
                if ($tmp[$hour][$val['orderDevice']][$settleArea[$val['orderArea']]]['orderIP'] != $val['orderIP']) {
                    $memberNo = 'no' . $i;
                    $tmpMemNo = 'no' . $i;
                } else {
                    $memberNo = $tmpMemNo;
                }
            } else {
                $memberNo = $val['memNo'];
            }
            $returnOrderData[$hour][$val['orderDevice']][$settleArea[$val['orderArea']]]['goodsPrice'] += $val['goodsPrice'];
            $returnOrderData[$hour][$val['orderDevice']][$settleArea[$val['orderArea']]]['goodsCnt'] += $val['goodsCnt'];
            $returnOrderData[$hour][$val['orderDevice']][$settleArea[$val['orderArea']]]['orderNo'][] = $val['orderNo'];
            $returnOrderData[$hour][$val['orderDevice']][$settleArea[$val['orderArea']]]['memNo'][] = $memberNo;
            $tmp[$hour][$val['orderDevice']][$settleArea[$val['orderArea']]]['orderIP'] = $val['orderIP'];
            $i++;
        }

        return $returnOrderData;
    }

    /**
     * 주문통계 - 지역별 - 요일별
     * getOrderAreaWeek
     *
     * @param $searchData   orderYMD / mallSno / kind / type / scmNo / .... / sort
     *
     * @return array
     * @throws \Exception
     */
    public function getOrderAreaWeek($searchData)
    {
        $sDate = new DateTime($searchData['orderYMD'][0]);
        $eDate = new DateTime($searchData['orderYMD'][1]);
        $dateDiff = date_diff($sDate, $eDate);
        if ($dateDiff->days > 90) {
            throw new \Exception(__('검색 가능 일은 최대 90일 입니다.'));
        }
        if ($searchData['orderYMD'][0] > $searchData['orderYMD'][1]) {
            throw new \Exception(__('시작일이 종료일보다 클 수 없습니다.'));
        }

        // 오늘 검색에 따른 당일 통계
        $todayDate = new DateTime();
        if ($eDate->format('Ymd') >= $todayDate->format('Ymd')) {
            $this->realTimeStatistics();
        }

        $order['orderYMD'][0] = $sDate->format('Ymd');
        $order['orderYMD'][1] = $eDate->format('Ymd');
        $order['kind'] = 'order';
        $order['type'] = 'goods';
        $order['mallSno'] = $searchData['mallSno'];
        $order['sort'] = 'oss.orderYMD asc, oss.orderIP asc';

        $orderData = $this->getOrderStatisticsInfo($order, null, null, true);

        $settleArea = $this->getOrderSettleArea();

        // 요일 매출 통계 데이터 초기화
        $orderDevice = ['pc', 'mobile', 'write'];
        for ($i = 0; $i <= 6; $i++) {
            foreach ($orderDevice as $deviceVal) {
                foreach ($settleArea as $settleKey => $settleVal) {
                    $returnOrderData[$i][$deviceVal][$settleVal]['goodsPrice'] = 0;
                    $returnOrderData[$i][$deviceVal][$settleVal]['goodsCnt'] = 0;
                    $returnOrderData[$i][$deviceVal][$settleVal]['orderCnt'] = 0;
                    $returnOrderData[$i][$deviceVal][$settleVal]['memberCnt'] = 0;
                }
            }
        }

        $tmp = [];
        $i = 0;
        $tmpMemNo = 0;
        foreach ($orderData as $key => $val) {
            $orderDt = new DateTime($val['orderYMD']);
            $week = $orderDt->format('w');
            if ($val['memNo'] == 0) {
                if ($tmp[$week][$val['orderDevice']][$settleArea[$val['orderArea']]]['orderIP'] != $val['orderIP']) {
                    $memberNo = 'no' . $i;
                    $tmpMemNo = 'no' . $i;
                } else {
                    $memberNo = $tmpMemNo;
                }
            } else {
                $memberNo = $val['memNo'];
            }
            $returnOrderData[$week][$val['orderDevice']][$settleArea[$val['orderArea']]]['goodsPrice'] += $val['goodsPrice'];
            $returnOrderData[$week][$val['orderDevice']][$settleArea[$val['orderArea']]]['goodsCnt'] += $val['goodsCnt'];
            $returnOrderData[$week][$val['orderDevice']][$settleArea[$val['orderArea']]]['orderNo'][] = $val['orderNo'];
            $returnOrderData[$week][$val['orderDevice']][$settleArea[$val['orderArea']]]['memNo'][] = $memberNo;
            $tmp[$week][$val['orderDevice']][$settleArea[$val['orderArea']]]['orderIP'] = $val['orderIP'];
            $i++;
        }

        return $returnOrderData;
    }

    /**
     * 주문통계 - 지역별 - 월별
     * getOrderAreaMonth
     *
     * @param $searchData   orderYMD / mallSno / kind / type / scmNo / .... / sort
     *
     * @return array
     * @throws \Exception
     */
    public function getOrderAreaMonth($searchData)
    {
        $sDate = new DateTime($searchData['orderYMD'][0]);
        $eDate = new DateTime($searchData['orderYMD'][1]);
        $dateDiff = date_diff($sDate, $eDate);
        if ($dateDiff->days > 360) {
            throw new \Exception(__('검색 가능 일은 최대 12개월 입니다.'));
        }
        if ($searchData['orderYMD'][0] > $searchData['orderYMD'][1]) {
            throw new \Exception(__('시작일이 종료일보다 클 수 없습니다.'));
        }

        // 오늘 검색에 따른 당일 통계
        $todayDate = new DateTime();
        if ($eDate->format('Ymd') >= $todayDate->format('Ymd')) {
            $this->realTimeStatistics();
        }

        $order['orderYMD'][0] = $sDate->format('Ymd');
        $order['orderYMD'][1] = $eDate->format('Ymd');
        $order['kind'] = 'order';
        $order['type'] = 'goods';
        $order['mallSno'] = $searchData['mallSno'];
        $order['sort'] = 'oss.orderYMD asc, oss.orderIP asc';

        $orderData = $this->getOrderStatisticsInfo($order, null, null, true,true);

        $settleArea = $this->getOrderSettleArea();

        // 월 매출 통계 데이터 초기화
        $searchDt = new DateTime($order['orderYMD'][0]);
        $endDate = new DateTime($order['orderYMD'][1]);
        $orderDevice = ['pc', 'mobile', 'write'];
        $returnOrderData = [];
        for ($i = 0; $i <= 13; $i++) {
            if ($i > 0) {
                $lastDay = $searchDt->format('t');
                $searchDt = $searchDt->modify('+' . $lastDay . ' day');
            }

            if ($searchDt->format('Ym') <= $endDate->format('Ym')) {
                foreach ($orderDevice as $deviceVal) {
                    foreach ($settleArea as $settleKey => $settleVal) {
                        $returnOrderData[$searchDt->format('Ym')][$deviceVal][$settleVal]['goodsPrice'] = 0;
                        $returnOrderData[$searchDt->format('Ym')][$deviceVal][$settleVal]['goodsCnt'] = 0;
                        $returnOrderData[$searchDt->format('Ym')][$deviceVal][$settleVal]['orderCnt'] = 0;
                        $returnOrderData[$searchDt->format('Ym')][$deviceVal][$settleVal]['memberCnt'] = 0;
                    }
                }
            }
        }

        $tmp = [];
        $i = 0;
        $tmpMemNo = 0;
        foreach ($orderData as $key => $val) {
            $orderDt = new DateTime($val['orderYMD']);
            $month = $orderDt->format('Ym');
            if ($val['memNo'] == 0) {
                if ($tmp[$month][$val['orderDevice']][$settleArea[$val['orderArea']]]['orderIP'] != $val['orderIP']) {
                    $memberNo = 'no' . $i;
                    $tmpMemNo = 'no' . $i;
                } else {
                    $memberNo = $tmpMemNo;
                }
            } else {
                $memberNo = $val['memNo'];
            }
            $returnOrderData[$month][$val['orderDevice']][$settleArea[$val['orderArea']]]['goodsPrice'] += $val['goodsPrice'];
            $returnOrderData[$month][$val['orderDevice']][$settleArea[$val['orderArea']]]['goodsCnt'] += $val['goodsCnt'];
            $returnOrderData[$month][$val['orderDevice']][$settleArea[$val['orderArea']]]['orderNo'][] = $val['orderNo'];
            $returnOrderData[$month][$val['orderDevice']][$settleArea[$val['orderArea']]]['memNo'][] = $memberNo;
            $tmp[$month][$val['orderDevice']][$settleArea[$val['orderArea']]]['orderIP'] = $val['orderIP'];
            $i++;
        }

        return $returnOrderData;
    }

    /**
     * 주문통계 - 성별 - 일별
     * getOrderGenderDay
     *
     * @param $searchData   orderYMD / mallSno / kind / type / scmNo / .... / sort
     *
     * @return array
     * @throws \Exception
     */
    public function getOrderGenderDay($searchData)
    {
        $sDate = new DateTime($searchData['orderYMD'][0]);
        $eDate = new DateTime($searchData['orderYMD'][1]);
        $dateDiff = date_diff($sDate, $eDate);
        if ($dateDiff->days > 90) {
            throw new \Exception(__('검색 가능 일은 최대 90일 입니다.'));
        }
        if ($searchData['orderYMD'][0] > $searchData['orderYMD'][1]) {
            throw new \Exception(__('시작일이 종료일보다 클 수 없습니다.'));
        }

        // 오늘 검색에 따른 당일 통계
        $todayDate = new DateTime();
        if ($eDate->format('Ymd') >= $todayDate->format('Ymd')) {
            $this->realTimeStatistics();
        }

        if ($searchData['mallSno'] != 'all') {
            $order['mallSno'] = $searchData['mallSno'];
        }

        $order['orderYMD'][0] = $sDate->format('Ymd');
        $order['orderYMD'][1] = $eDate->format('Ymd');
        $order['kind'] = 'order';
        $order['type'] = 'goods';
        $order['sort'] = 'oss.orderYMD asc, oss.orderIP asc';

        $orderData = $this->getOrderStatisticsInfo($order, null, null, true);

        // 일별 매출 통계 데이터 초기화
        $startDate = new DateTime($order['orderYMD'][0]);
        $endDate = new DateTime($order['orderYMD'][1]);
        $diffDay = $endDate->diff($startDate)->days;
        $orderDevice = ['pc', 'mobile', 'write'];
        $genderArr = ['male' => '남자', 'female' => '여자', 'etc' => '성별 미확인'];
        $returnOrderData = [];
        for ($i = 0; $i <= $diffDay; $i++) {
            $searchDt = new DateTime($order['orderYMD'][0]);
            $searchDt = $searchDt->modify('+' . $i . ' day');
            foreach ($orderDevice as $deviceVal) {
                foreach ($genderArr as $genderKey => $genderVal) {
                    $returnOrderData[$searchDt->format('Ymd')][$deviceVal][$genderKey]['goodsPrice'] = 0;
                    $returnOrderData[$searchDt->format('Ymd')][$deviceVal][$genderKey]['goodsCnt'] = 0;
                    $returnOrderData[$searchDt->format('Ymd')][$deviceVal][$genderKey]['orderCnt'] = 0;
                    $returnOrderData[$searchDt->format('Ymd')][$deviceVal][$genderKey]['memberCnt'] = 0;
                }
            }
        }

        $tmp = [];
        $i = 0;
        $tmpMemNo = 0;
        foreach ($orderData as $key => $val) {
            if ($val['memNo'] == 0) {
                if ($tmp[$val['orderYMD']][$val['orderDevice']][$val['orderGender']]['orderIP'] != $val['orderIP']) {
                    $memberNo = 'no' . $i;
                    $tmpMemNo = 'no' . $i;
                } else {
                    $memberNo = $tmpMemNo;
                }
            } else {
                $memberNo = $val['memNo'];
            }
            $returnOrderData[$val['orderYMD']][$val['orderDevice']][$val['orderGender']]['goodsPrice'] += $val['goodsPrice'];
            $returnOrderData[$val['orderYMD']][$val['orderDevice']][$val['orderGender']]['goodsCnt'] += $val['goodsCnt'];
            $returnOrderData[$val['orderYMD']][$val['orderDevice']][$val['orderGender']]['orderNo'][] = $val['orderNo'];
            $returnOrderData[$val['orderYMD']][$val['orderDevice']][$val['orderGender']]['memNo'][] = $memberNo;
            $tmp[$val['orderYMD']][$val['orderDevice']][$val['orderGender']]['orderIP'] = $val['orderIP'];
            $i++;
        }

        return $returnOrderData;
    }

    /**
     * 주문통계 - 성별 - 시간별
     * getOrderGenderHour
     *
     * @param $searchData   orderYMD / mallSno / kind / type / scmNo / .... / sort
     *
     * @return array
     * @throws \Exception
     */
    public function getOrderGenderHour($searchData)
    {
        $sDate = new DateTime($searchData['orderYMD'][0]);
        $eDate = new DateTime($searchData['orderYMD'][1]);
        $dateDiff = date_diff($sDate, $eDate);
        if ($dateDiff->days > 90) {
            throw new \Exception(__('검색 가능 일은 최대 90일 입니다.'));
        }
        if ($searchData['orderYMD'][0] > $searchData['orderYMD'][1]) {
            throw new \Exception(__('시작일이 종료일보다 클 수 없습니다.'));
        }

        // 오늘 검색에 따른 당일 통계
        $todayDate = new DateTime();
        if ($eDate->format('Ymd') >= $todayDate->format('Ymd')) {
            $this->realTimeStatistics();
        }

        if ($searchData['mallSno'] != 'all') {
            $order['mallSno'] = $searchData['mallSno'];
        }

        $order['orderYMD'][0] = $sDate->format('Ymd');
        $order['orderYMD'][1] = $eDate->format('Ymd');
        $order['kind'] = 'order';
        $order['type'] = 'goods';
        $order['sort'] = 'oss.orderYMD asc, oss.orderIP asc';

        $orderData = $this->getOrderStatisticsInfo($order, null, null, true);

        // 시간별 매출 통계 데이터 초기화
        $orderDevice = ['pc', 'mobile', 'write'];
        $genderArr = ['male' => '남자', 'female' => '여자', 'etc' => '성별 미확인'];
        for ($i = 0; $i <= 23; $i++) {
            $hour = sprintf("%02d", $i);
            foreach ($orderDevice as $deviceVal) {
                foreach ($genderArr as $genderKey => $genderVal) {
                    $returnOrderData[$hour][$deviceVal][$genderKey]['goodsPrice'] = 0;
                    $returnOrderData[$hour][$deviceVal][$genderKey]['goodsCnt'] = 0;
                    $returnOrderData[$hour][$deviceVal][$genderKey]['orderCnt'] = 0;
                    $returnOrderData[$hour][$deviceVal][$genderKey]['memberCnt'] = 0;
                }
            }
        }

        $tmp = [];
        $i = 0;
        $tmpMemNo = 0;
        foreach ($orderData as $key => $val) {
            $hour = sprintf("%02d", $val['orderHour']);
            if ($val['memNo'] == 0) {
                if ($tmp[$hour][$val['orderDevice']][$val['orderGender']]['orderIP'] != $val['orderIP']) {
                    $memberNo = 'no' . $i;
                    $tmpMemNo = 'no' . $i;
                } else {
                    $memberNo = $tmpMemNo;
                }
            } else {
                $memberNo = $val['memNo'];
            }
            $returnOrderData[$hour][$val['orderDevice']][$val['orderGender']]['goodsPrice'] += $val['goodsPrice'];
            $returnOrderData[$hour][$val['orderDevice']][$val['orderGender']]['goodsCnt'] += $val['goodsCnt'];
            $returnOrderData[$hour][$val['orderDevice']][$val['orderGender']]['orderNo'][] = $val['orderNo'];
            $returnOrderData[$hour][$val['orderDevice']][$val['orderGender']]['memNo'][] = $memberNo;
            $tmp[$hour][$val['orderDevice']][$val['orderGender']]['orderIP'] = $val['orderIP'];
            $i++;
        }

        return $returnOrderData;
    }

    /**
     * 주문통계 - 성별 - 요일별
     * getOrderGenderWeek
     *
     * @param $searchData   orderYMD / mallSno / kind / type / scmNo / .... / sort
     *
     * @return array
     * @throws \Exception
     */
    public function getOrderGenderWeek($searchData)
    {
        $sDate = new DateTime($searchData['orderYMD'][0]);
        $eDate = new DateTime($searchData['orderYMD'][1]);
        $dateDiff = date_diff($sDate, $eDate);
        if ($dateDiff->days > 90) {
            throw new \Exception(__('검색 가능 일은 최대 90일 입니다.'));
        }
        if ($searchData['orderYMD'][0] > $searchData['orderYMD'][1]) {
            throw new \Exception(__('시작일이 종료일보다 클 수 없습니다.'));
        }

        // 오늘 검색에 따른 당일 통계
        $todayDate = new DateTime();
        if ($eDate->format('Ymd') >= $todayDate->format('Ymd')) {
            $this->realTimeStatistics();
        }

        if ($searchData['mallSno'] != 'all') {
            $order['mallSno'] = $searchData['mallSno'];
        }

        $order['orderYMD'][0] = $sDate->format('Ymd');
        $order['orderYMD'][1] = $eDate->format('Ymd');
        $order['kind'] = 'order';
        $order['type'] = 'goods';
        $order['sort'] = 'oss.orderYMD asc, oss.orderIP asc';

        $orderData = $this->getOrderStatisticsInfo($order, null, null, true);

        // 시간별 매출 통계 데이터 초기화
        $orderDevice = ['pc', 'mobile', 'write'];
        $genderArr = ['male' => '남자', 'female' => '여자', 'etc' => '성별 미확인'];
        for ($i = 0; $i <= 6; $i++) {
            foreach ($orderDevice as $deviceVal) {
                foreach ($genderArr as $genderKey => $genderVal) {
                    $returnOrderData[$i][$deviceVal][$genderKey]['goodsPrice'] = 0;
                    $returnOrderData[$i][$deviceVal][$genderKey]['goodsCnt'] = 0;
                    $returnOrderData[$i][$deviceVal][$genderKey]['orderCnt'] = 0;
                    $returnOrderData[$i][$deviceVal][$genderKey]['memberCnt'] = 0;
                }
            }
        }

        $tmp = [];
        $i = 0;
        $tmpMemNo = 0;
        foreach ($orderData as $key => $val) {
            $orderDt = new DateTime($val['orderYMD']);
            $week = $orderDt->format('w');
            if ($val['memNo'] == 0) {
                if ($tmp[$week][$val['orderDevice']][$val['orderGender']]['orderIP'] != $val['orderIP']) {
                    $memberNo = 'no' . $i;
                    $tmpMemNo = 'no' . $i;
                } else {
                    $memberNo = $tmpMemNo;
                }
            } else {
                $memberNo = $val['memNo'];
            }
            $returnOrderData[$week][$val['orderDevice']][$val['orderGender']]['goodsPrice'] += $val['goodsPrice'];
            $returnOrderData[$week][$val['orderDevice']][$val['orderGender']]['goodsCnt'] += $val['goodsCnt'];
            $returnOrderData[$week][$val['orderDevice']][$val['orderGender']]['orderNo'][] = $val['orderNo'];
            $returnOrderData[$week][$val['orderDevice']][$val['orderGender']]['memNo'][] = $memberNo;
            $tmp[$week][$val['orderDevice']][$val['orderGender']]['orderIP'] = $val['orderIP'];
            $i++;
        }

        return $returnOrderData;
    }

    /**
     * 주문통계 - 성별 - 월별
     * getOrderGenderMonth
     *
     * @param $searchData   orderYMD / mallSno / kind / type / scmNo / .... / sort
     *
     * @return array
     * @throws \Exception
     */
    public function getOrderGenderMonth($searchData)
    {
        $sDate = new DateTime($searchData['orderYMD'][0]);
        $eDate = new DateTime($searchData['orderYMD'][1]);
        $dateDiff = date_diff($sDate, $eDate);
        if ($dateDiff->days > 360) {
            throw new \Exception(__('검색 가능 일은 최대 12개월 입니다.'));
        }
        if ($searchData['orderYMD'][0] > $searchData['orderYMD'][1]) {
            throw new \Exception(__('시작일이 종료일보다 클 수 없습니다.'));
        }

        // 오늘 검색에 따른 당일 통계
        $todayDate = new DateTime();
        if ($eDate->format('Ymd') >= $todayDate->format('Ymd')) {
            $this->realTimeStatistics();
        }

        if ($searchData['mallSno'] != 'all') {
            $order['mallSno'] = $searchData['mallSno'];
        }

        $order['orderYMD'][0] = $sDate->format('Ymd');
        $order['orderYMD'][1] = $eDate->format('Ymd');
        $order['kind'] = 'order';
        $order['type'] = 'goods';
        $order['sort'] = 'oss.orderYMD asc, oss.orderIP asc';

        $orderData = $this->getOrderStatisticsInfo($order, null, null, true,true);

        // 월별 매출 통계 데이터 초기화
        $searchDt = new DateTime($order['orderYMD'][0]);
        $endDate = new DateTime($order['orderYMD'][1]);
        $orderDevice = ['pc', 'mobile', 'write'];
        $genderArr = ['male' => '남자', 'female' => '여자', 'etc' => '성별 미확인'];
        $returnOrderData = [];
        for ($i = 0; $i <= 13; $i++) {
            if ($i > 0) {
                $lastDay = $searchDt->format('t');
                $searchDt = $searchDt->modify('+' . $lastDay . ' day');
            }
            if ($searchDt->format('Ym') <= $endDate->format('Ym')) {
                foreach ($orderDevice as $deviceVal) {
                    foreach ($genderArr as $genderKey => $genderVal) {
                        $returnOrderData[$searchDt->format('Ym')][$deviceVal][$genderKey]['goodsPrice'] = 0;
                        $returnOrderData[$searchDt->format('Ym')][$deviceVal][$genderKey]['goodsCnt'] = 0;
                        $returnOrderData[$searchDt->format('Ym')][$deviceVal][$genderKey]['orderCnt'] = 0;
                        $returnOrderData[$searchDt->format('Ym')][$deviceVal][$genderKey]['memberCnt'] = 0;
                    }
                }
            }
        }

        $tmp = [];
        $i = 0;
        $tmpMemNo = 0;
        foreach ($orderData as $key => $val) {
            $orderDt = new DateTime($val['orderYMD']);
            $month = $orderDt->format('Ym');
            if ($val['memNo'] == 0) {
                if ($tmp[$month][$val['orderDevice']][$val['orderGender']]['orderIP'] != $val['orderIP']) {
                    $memberNo = 'no' . $i;
                    $tmpMemNo = 'no' . $i;
                } else {
                    $memberNo = $tmpMemNo;
                }
            } else {
                $memberNo = $val['memNo'];
            }
            $returnOrderData[$month][$val['orderDevice']][$val['orderGender']]['goodsPrice'] += $val['goodsPrice'];
            $returnOrderData[$month][$val['orderDevice']][$val['orderGender']]['goodsCnt'] += $val['goodsCnt'];
            $returnOrderData[$month][$val['orderDevice']][$val['orderGender']]['orderNo'][] = $val['orderNo'];
            $returnOrderData[$month][$val['orderDevice']][$val['orderGender']]['memNo'][] = $memberNo;
            $tmp[$month][$val['orderDevice']][$val['orderGender']]['orderIP'] = $val['orderIP'];
            $i++;
        }

        return $returnOrderData;
    }

    /**
     * 매출통계 - 메인 탭
     * getOrderSalesDay
     *
     * @param $searchData   orderYMD / mallSno
     *
     * @return array
     * @throws \Exception
     */
    public function getTodayMainTabSales($searchData)
    {
        if ($searchData['mallSno'] == 'all') {
            unset($searchData['mallSno']);
        }
        if ($searchData['scmNo'] == DEFAULT_CODE_SCMNO) {
            unset($searchData['scmNo']);
        }
        $searchData['sort'] = 'oss.orderYMD asc';
        $getField[] = 'sum(oss.goodsPrice) as goodsPrice';
        $getField[] = 'sum(oss.goodsDcPrice) as goodsDcPrice';
        $getField[] = 'sum(oss.deliveryPrice) as deliveryPrice';
        $getField[] = 'sum(oss.deliveryDcPrice) as deliveryDcPrice';
        $getField[] = 'sum(oss.refundGoodsPrice) as refundGoodsPrice';
        $getField[] = 'sum(oss.refundDeliveryPrice) as refundDeliveryPrice';
        $getField[] = 'sum(oss.refundFeePrice) as refundFeePrice';
        $getField[] = 'sum(oss.refundUseDeposit) as refundUseDeposit';
        $getField[] = 'sum(oss.refundUseMileage) as refundUseMileage';
        $field = implode(', ', $getField);
        $todayMainTabSalesArr = $this->getOrderStatisticsInfo($searchData, $field, null, true,true);
        //배열이 아닌경우 배열로 변경
        if(!is_array($todayMainTabSalesArr)) {
            $todayMainTabSalesArr = iterator_to_array($todayMainTabSalesArr);
        }

        $goodsSalesPrice = $todayMainTabSalesArr[0]['goodsPrice'] - $todayMainTabSalesArr[0]['goodsDcPrice'];
        $deliverySalesPrice = $todayMainTabSalesArr[0]['deliveryPrice'] - $todayMainTabSalesArr[0]['deliveryDcPrice'];
        $refundSalesPrice = $todayMainTabSalesArr[0]['refundGoodsPrice'] + $todayMainTabSalesArr[0]['refundUseDeposit'] + $todayMainTabSalesArr[0]['refundUseMileage'] + $todayMainTabSalesArr[0]['refundDeliveryPrice'] - $todayMainTabSalesArr[0]['refundFeePrice'];

        $returnOrderSalesPrice = $goodsSalesPrice + $deliverySalesPrice - $refundSalesPrice;

        return $returnOrderSalesPrice;
    }

    /**
     * 주문통계 - 메인 탭
     * getTodayMainTabOrder
     *
     * @param $searchData   orderYMD / mallSno
     *
     * @return array
     * @throws \Exception
     */
    public function getTodayMainTabOrder($searchData)
    {
        if ($searchData['mallSno'] == 'all') {
            unset($searchData['mallSno']);
        }
        if ($searchData['scmNo'] == DEFAULT_CODE_SCMNO) {
            unset($searchData['scmNo']);
        }
        $searchData['kind'] = 'order';
        $searchData['type'] = 'goods';
        $searchData['sort'] = 'oss.orderYMD asc';
        $getField[] = 'DISTINCT oss.orderNo';
        $field = implode(', ', $getField);
        $todayMainTabOrderArr = $this->getOrderStatisticsInfo($searchData, $field, null, true,true);
        if(is_array($todayMainTabOrderArr)) {
            $returnOrderNoCount = count($todayMainTabOrderArr);
        } else {
            $returnOrderNoCount = 0;
            foreach($todayMainTabOrderArr as $k => $v) {
                $returnOrderNoCount++;
            }
        }

        return $returnOrderNoCount;
    }

    /**
     * 매입처순위분석
     * getGoodsPurchase
     *
     * @param $searchData   orderYMD / mallSno / kind / type / scmNo / .... / sort
     *
     * @return array
     * @throws \Exception
     */
    public function getGoodsPurchase($searchData)
    {
        $sDate = new DateTime($searchData['orderYMD'][0]);
        $eDate = new DateTime($searchData['orderYMD'][1]);
        $dateDiff = date_diff($sDate, $eDate);
        if ($dateDiff->days > 90) {
            throw new \Exception(__('검색 가능 일은 최대 90일 입니다.'));
        }
        if ($searchData['orderYMD'][0] > $searchData['orderYMD'][1]) {
            throw new \Exception(__('시작일이 종료일보다 클 수 없습니다.'));
        }

        // 오늘 검색에 따른 당일 통계
        $todayDate = new DateTime();
        if ($eDate->format('Ymd') >= $todayDate->format('Ymd')) {
            $this->realTimeStatistics();
        }

        $order['orderYMD'][0] = $sDate->format('Ymd');
        $order['orderYMD'][1] = $eDate->format('Ymd');
        $order['kind'] = 'order';
        $order['type'] = 'goods';
        $order['sort'] = 'oss.orderYMD asc';
        $order['purchaseFl'] = "y";
        $order['purchaseNo'] = $searchData['purchaseNo'];

        $orderData = $this->getOrderStatisticsInfo($order, null, null, true);

        $returnOrderData = [];
        $returnTotalData = [];

        if($orderData) {
            //매입처명가져오기
            $strPurchaseSQL = 'SELECT purchaseNo,purchaseNm FROM ' . DB_PURCHASE . ' g  WHERE  purchaseNo IN ("' . implode('","', array_column($orderData, 'purchaseNo')) . '")';
            $tmpPurchaseData = $this->db->query_fetch($strPurchaseSQL);
            $purchaseData = array_combine(array_column($tmpPurchaseData, 'purchaseNo'), array_column($tmpPurchaseData, 'purchaseNm'));

            //매입처별 통계 잡기
            $tmpOrderNo = [];
            foreach ($orderData as $key => $val) {
                $returnOrderData[$val['purchaseNo']]['purchaseNm'] = $purchaseData[$val['purchaseNo']];

                if(!$returnOrderData[$val['purchaseNo']][$val['orderDevice'].'OrderCnt']) {
                    $returnOrderData[$val['purchaseNo']][$val['orderDevice'].'OrderCnt'] = 0;
                }

                //각 매입처별 개별 항목
                $returnOrderData[$val['purchaseNo']][$val['orderDevice'].'GoodsPrice'] += $val['goodsPrice'];
                $returnOrderData[$val['purchaseNo']][$val['orderDevice'].'GoodsCnt'] += $val['goodsCnt'];
                $returnOrderData[$val['purchaseNo']][$val['orderDevice'].'CostPrice'] += $val['costPrice'];
                $val['orderCnt'] = 0;
                if(!in_array($val['orderNo'],$tmpOrderNo[$val['purchaseNo']][$val['orderDevice']])) {
                    $val['orderCnt'] = 1;
                }
                $returnOrderData[$val['purchaseNo']][$val['orderDevice'].'OrderCnt']  += $val['orderCnt'];

                //각 매입처별 합계
                $returnOrderData[$val['purchaseNo']]['totalGoodsPrice'] += $val['goodsPrice'];
                $returnOrderData[$val['purchaseNo']]['totalGoodsCnt'] += $val['goodsCnt'];
                $returnOrderData[$val['purchaseNo']]['totalCostPrice'] += $val['costPrice'];
                $returnOrderData[$val['purchaseNo']]['totalOrderCnt'] += $val['orderCnt'];

                //주문번호 중복체크
                $tmpOrderNo[$val['purchaseNo']][$val['orderDevice']][] = $val['orderNo'];
            }

            //전체 합계 구하기
            $orderDevice = ['pc', 'mobile', 'write'];
            $orderField = ['goodsPrice', 'costPrice', 'goodsCnt','orderCnt'];
            foreach($orderField as $key => $value) {
                foreach($orderDevice as $deviceKey => $deviceValue) {
                    $returnTotalData[$value][$deviceValue] = array_sum(array_column($returnOrderData,  $deviceValue.ucfirst($value)));
                }
                $returnTotalData[$value]['total'] = array_sum($returnTotalData[$value]);
            }

            //전체 상품가격 순 재정렬
            usort($returnOrderData, function ($preData, $nextData) {
                return $preData['totalGoodsPrice'] <= $nextData['totalGoodsPrice'];
            });

        }

        return ['order'=>$returnOrderData,'total'=>$returnTotalData];
    }

    public function getAge($memNo, $birthDay)
    {
        if ($memNo > 0) {
            /** @var DateTime $statisticsDate */
            $statisticsDate = $this->orderPolicy['statisticsDate'];
            $birthDt = new DateTime($birthDay);
            $birthAge = $statisticsDate->diff($birthDt)->y;
            if ($birthAge > 0 && $birthAge < 80) {
                if ($birthAge >= 10) {
                    $age = floor($birthAge / 10);
                } else {
                    $age = 1;
                }
                $age .= '0';
            } else {
                $age = 0;
            }
        } else {
            $age = 0;
        }

        return $age;
    }

    public function getOrderSettleKind()
    {
//        $orderClass = new Order();
//        $settleKind = $orderClass->getSettleKind();
        // 통계 노출 순서를 정하고 페이코/네이버페이를 결제수단으로 체크하기 위하여 별도 생성
        // 결제 수단 추가 되면 여기에 추가해 줘야 함.
        $settleKind = [
            'gb' => '무통장 입금',
            'pc' => '신용카드',
            'pb' => '계좌이체',
            'pv' => '가상계좌',
            'ph' => '휴대폰',
            'gd' => '예치금',
            'gm' => '마일리지',
            'gz' => '전액할인',
            'payco' => '페이코',
            'npay' => '네이버페이',
            'pk' => '카카오페이',
            'ec' => '에스크로<br/>(신용카드)',
            'eb' => '에스크로<br/>(계좌이체)',
            'ev' => '에스크로<br/>(가상계좌)',
            'oa' => 'ALIPAY',
            'ot' => 'TENPAY',
            'ou' => 'UNIONPAY',
            'mp' => 'PAYPAL',
            'mc' => 'VISA / MASTER / JCB / AMEX',
        ];
//        'oj' => 'JCB / AMEX',
//        'op' => 'PAYPAL',
//        'ov' => 'VISA / MASTER',
//        'ol' => 'PAYPAL',
//        'oc' => 'VISA / MASTER / JCB',

        return $settleKind;
    }

    public function getMatchSettleKind()
    {
        // getOrderSettleKind로 가져온 결제 방식기준에서 합산해야할 항목을 매칭하기 위한 배열 mp, mc
        $settleKind = [
            'ol' => 'mp',
            'op' => 'mp',
            'oc' => 'mc',
            'oj' => 'mc',
            'ov' => 'mc',
        ];
        return $settleKind;
    }

    public function getOrderSettleArea()
    {
        $cityArrName = [
            '강원' => 'KW',
            '경기' => 'KG',
            '경남' => 'KN',
            '경북' => 'KB',
            '광주' => 'KJ',
            '대구' => 'DG',
            '대전' => 'DJ',
            '부산' => 'BS',
            '서울' => 'SW',
            '세종' => 'SJ',
            '울산' => 'WS',
            '인천' => 'IC',
            '전남' => 'JN',
            '전북' => 'JB',
            '제주' => 'JJ',
            '충남' => 'CN',
            '충북' => 'CB',
        ];

        return $cityArrName;
    }

    /**
     * setOrderGoodsStatistics
     * 주문 상품 매출 통계 데이터 생성
     *
     * @param bool $realTimeKey
     */
    public function setOrderGoodsStatistics($realTimeKey = false)
    {
        // 판매 상품 데이터 생성
        if ($realTimeKey) {
            $orderGoods['paymentDtOver'] = $this->orderPolicy['statisticsDate']->format('Y-m-d H:i:s');
        } else {
            $orderGoods['paymentDt'] = $this->orderPolicy['statisticsDate']->format('Ymd');
        }
        $orderGoodsField = 'o.mallSno, o.orderTypeFl, o.memNo, o.orderChannelFl, o.settleKind, o.orderIp, ' .
            'TRIM(LEFT(oi.receiverAddress, 4)) as area, ' .
            'm.birthDt, m.sexFl, ' .
            'og.sno, og.scmNo, og.orderNo, og.goodsTaxInfo, og.paymentDt, og.statisticsOrderFl, ' .
            'og.goodsCnt, og.goodsPrice, og.optionPrice, og.optionTextPrice, ' .
            'og.goodsDcPrice, og.enuri, og.memberDcPrice, og.memberOverlapDcPrice, og.couponGoodsDcPrice, og.divisionCouponOrderDcPrice, ' .
            'og.divisionUseDeposit, og.divisionUseMileage, og.purchaseNo, og.costPrice,og.optionCostPrice';
        if (gd_policy('myapp.config')['useMyapp']) {
            $orderGoodsField .= ', og.myappDcPrice';
        }
        $orderGoodsArr = $this->getOrderGoodsInfo($orderGoods, $orderGoodsField);
        unset($orderGoods);

        foreach ($orderGoodsArr as $orderGoodsKey => $orderGoodsVal) {
            if ($orderGoodsVal['statisticsOrderFl'] == 'y') {
                continue;
            }
            if ($orderGoodsVal['orderChannelFl'] == 'etc') {
                continue;
            }
            if (!$orderGoodsVal['mallSno']) {
                $orderGoodsVal['mallSno'] = DEFAULT_MALL_NUMBER;
            }
            if (!$orderGoodsVal['orderIp']) {
                $orderGoodsVal['orderIp'] = '000.000.000.000';
            }
            if (!$orderGoodsVal['orderTypeFl']) {
                $orderGoodsVal['orderTypeFl'] = 'pc';
            }
            if ($orderGoodsVal['orderChannelFl'] == 'naverpay') {
                $orderGoodsVal['settleKind'] = 'npay'; // 디비 필드 5자리라...
            } else if ($orderGoodsVal['orderChannelFl'] == 'payco') {
                $orderGoodsVal['settleKind'] = 'payco';
            } else {
                if (!$orderGoodsVal['settleKind']) {
                    $orderGoodsVal['settleKind'] = 'gb';
                }
            }
            $paymentDt = new DateTime($orderGoodsVal['paymentDt']);
            $goodsStatistics['orderYMD'] = $paymentDt->format('Ymd');
            $goodsStatistics['mallSno'] = $orderGoodsVal['mallSno'];
            $goodsStatistics['purchaseNo'] = $orderGoodsVal['purchaseNo'];
            $goodsStatistics['kind'] = 'order';
            $goodsStatistics['type'] = 'goods';
            $goodsStatistics['scmNo'] = $orderGoodsVal['scmNo'];
            $goodsStatistics['orderIP'] = $orderGoodsVal['orderIp'];
            $goodsStatistics['orderNo'] = $orderGoodsVal['orderNo'];
            $goodsStatistics['relationSno'] = $orderGoodsVal['sno'];
            $goodsStatistics['orderHour'] = $paymentDt->format('H');
            $goodsStatistics['orderDevice'] = $orderGoodsVal['orderTypeFl'];
            $goodsStatistics['memNo'] = $orderGoodsVal['memNo'];
            $goodsStatistics['orderMemberFl'] = $orderGoodsVal['memNo'] > 0 ? 'y' : 'n';
            $goodsTax = explode(STR_DIVISION, $orderGoodsVal['goodsTaxInfo']);
            $goodsStatistics['orderTaxFl'] = $goodsTax[0] == 't' ? 'y' : 'n';
            if ($orderGoodsVal['sexFl'] == 'm') {
                $gender = 'male';
            } else if ($orderGoodsVal['sexFl'] == 'w') {
                $gender = 'female';
            } else {
                $gender = 'etc';
            }
            $goodsStatistics['orderGender'] = $gender;

            $age = $this->getAge($orderGoodsVal['memNo'], $orderGoodsVal['birthDt']);
            $goodsStatistics['orderAge'] = $age;
            $area = $this->getAreaSimpleName($orderGoodsVal['area']);
            $goodsStatistics['orderArea'] = $area;
            $goodsStatistics['orderSettleKind'] = $orderGoodsVal['settleKind'];
            $goodsStatistics['goodsCnt'] = $orderGoodsVal['goodsCnt'];
            $goodsPrice = $orderGoodsVal['goodsPrice'] + $orderGoodsVal['optionPrice'] + $orderGoodsVal['optionTextPrice'];
            $goodsPrice = $goodsPrice * $orderGoodsVal['goodsCnt'];
            $goodsStatistics['goodsPrice'] = $goodsPrice;
            $goodsStatistics['costPrice'] = $orderGoodsVal['goodsCnt'] * ($orderGoodsVal['costPrice'] + $orderGoodsVal['optionCostPrice']);
            $goodsDcPrice = $orderGoodsVal['goodsDcPrice'] + $orderGoodsVal['memberDcPrice'] + $orderGoodsVal['memberOverlapDcPrice'] + $orderGoodsVal['couponGoodsDcPrice'] + $orderGoodsVal['divisionCouponOrderDcPrice'] + $orderGoodsVal['enuri'];
            if (gd_policy('myapp.config')['useMyapp']) {
                $goodsDcPrice += $orderGoodsVal['myappDcPrice'];
            }
            $goodsStatistics['goodsDcPrice'] = $goodsDcPrice;
            $goodsStatistics['divisionUseDeposit'] = $orderGoodsVal['divisionUseDeposit'];
            $goodsStatistics['divisionUseMileage'] = $orderGoodsVal['divisionUseMileage'];

            // 중복 체크 하여 이미 값이 있으면 update
            $order['orderYMD'] = $goodsStatistics['orderYMD'];
            $order['mallSno'] = $goodsStatistics['mallSno'];
            $order['kind'] = $goodsStatistics['kind'];
            $order['type'] = $goodsStatistics['type'];
            $order['scmNo'] = $goodsStatistics['scmNo'];
            $order['relationSno'] = $goodsStatistics['relationSno'];
            $orderYMD = $this->getOrderStatisticsInfo($order, 'oss.orderYMD');
            unset($order);

            if ($orderYMD) {
                $arrBind = [];
                $strSQL = "UPDATE " . DB_ORDER_SALES_STATISTICS . " SET " .
                    "`orderIP` = INET_ATON(?), `orderNo` = ?, `purchaseNo` = ?, `memNo` = ?, `goodsCnt` = ?, " .
                    "`orderHour` = ?, `orderDevice` = ?, `orderMemberFl` = ?, `orderTaxFl` = ?, " .
                    "`orderGender` = ?, `orderAge` = ?, `orderArea` = ?, `orderSettleKind` = ?, " .
                    "`goodsPrice` = ?,`costPrice` = ?, `goodsDcPrice` = ?, `divisionUseDeposit` = ?, `divisionUseMileage` = ?, " .
                    " `modDt` = now() " .
                    "WHERE `orderYMD` = ? AND `mallSno` = ? AND `kind` = ? AND `type` = ? AND `scmNo` = ? AND `relationSno` = ?";
                $this->db->bind_param_push($arrBind, 's', $goodsStatistics['orderIP']);
                $this->db->bind_param_push($arrBind, 's', $goodsStatistics['orderNo']);
                $this->db->bind_param_push($arrBind, 'i', $goodsStatistics['purchaseNo']);
                $this->db->bind_param_push($arrBind, 'i', $goodsStatistics['memNo']);
                $this->db->bind_param_push($arrBind, 'i', $goodsStatistics['goodsCnt']);
                $this->db->bind_param_push($arrBind, 'i', $goodsStatistics['orderHour']);
                $this->db->bind_param_push($arrBind, 's', $goodsStatistics['orderDevice']);
                $this->db->bind_param_push($arrBind, 's', $goodsStatistics['orderMemberFl']);
                $this->db->bind_param_push($arrBind, 's', $goodsStatistics['orderTaxFl']);
                $this->db->bind_param_push($arrBind, 's', $goodsStatistics['orderGender']);
                $this->db->bind_param_push($arrBind, 'i', $goodsStatistics['orderAge']);
                $this->db->bind_param_push($arrBind, 's', $goodsStatistics['orderArea']);
                $this->db->bind_param_push($arrBind, 's', $goodsStatistics['orderSettleKind']);
                $this->db->bind_param_push($arrBind, 'd', $goodsStatistics['goodsPrice']);
                $this->db->bind_param_push($arrBind, 'd', $goodsStatistics['costPrice']);
                $this->db->bind_param_push($arrBind, 'd', $goodsStatistics['goodsDcPrice']);
                $this->db->bind_param_push($arrBind, 'd', $goodsStatistics['divisionUseDeposit']);
                $this->db->bind_param_push($arrBind, 'd', $goodsStatistics['divisionUseMileage']);
                $this->db->bind_param_push($arrBind, 'i', $goodsStatistics['orderYMD']);
                $this->db->bind_param_push($arrBind, 'i', $goodsStatistics['mallSno']);
                $this->db->bind_param_push($arrBind, 's', $goodsStatistics['kind']);
                $this->db->bind_param_push($arrBind, 's', $goodsStatistics['type']);
                $this->db->bind_param_push($arrBind, 'i', $goodsStatistics['scmNo']);
                $this->db->bind_param_push($arrBind, 'i', $goodsStatistics['relationSno']);
                $this->db->bind_query($strSQL, $arrBind);
                unset($arrBind);
            } else {
                $arrBind = [];
                $strSQL = "INSERT INTO " . DB_ORDER_SALES_STATISTICS . " SET " .
                    "`orderYMD` = ?, `mallSno` = ?, `kind` = ?, `type` = ?, `scmNo` = ?, `relationSno` = ?, " .
                    "`orderIP` = INET_ATON(?), `orderNo` = ?, `purchaseNo` = ?, `memNo` = ?, `goodsCnt` = ?, " .
                    "`orderHour` = ?, `orderDevice` = ?, `orderMemberFl` = ?, `orderTaxFl` = ?, " .
                    "`orderGender` = ?, `orderAge` = ?, `orderArea` = ?, `orderSettleKind` = ?, " .
                    "`goodsPrice` = ?,`costPrice` = ?, `goodsDcPrice` = ?, `divisionUseDeposit` = ?, `divisionUseMileage` = ?, `regDt`=now()";
                $this->db->bind_param_push($arrBind, 'i', $goodsStatistics['orderYMD']);
                $this->db->bind_param_push($arrBind, 'i', $goodsStatistics['mallSno']);
                $this->db->bind_param_push($arrBind, 's', $goodsStatistics['kind']);
                $this->db->bind_param_push($arrBind, 's', $goodsStatistics['type']);
                $this->db->bind_param_push($arrBind, 'i', $goodsStatistics['scmNo']);
                $this->db->bind_param_push($arrBind, 'i', $goodsStatistics['relationSno']);
                $this->db->bind_param_push($arrBind, 's', $goodsStatistics['orderIP']);
                $this->db->bind_param_push($arrBind, 's', $goodsStatistics['orderNo']);
                $this->db->bind_param_push($arrBind, 'i', $goodsStatistics['purchaseNo']);
                $this->db->bind_param_push($arrBind, 'i', $goodsStatistics['memNo']);
                $this->db->bind_param_push($arrBind, 'i', $goodsStatistics['goodsCnt']);
                $this->db->bind_param_push($arrBind, 'i', $goodsStatistics['orderHour']);
                $this->db->bind_param_push($arrBind, 's', $goodsStatistics['orderDevice']);
                $this->db->bind_param_push($arrBind, 's', $goodsStatistics['orderMemberFl']);
                $this->db->bind_param_push($arrBind, 's', $goodsStatistics['orderTaxFl']);
                $this->db->bind_param_push($arrBind, 's', $goodsStatistics['orderGender']);
                $this->db->bind_param_push($arrBind, 'i', $goodsStatistics['orderAge']);
                $this->db->bind_param_push($arrBind, 's', $goodsStatistics['orderArea']);
                $this->db->bind_param_push($arrBind, 's', $goodsStatistics['orderSettleKind']);
                $this->db->bind_param_push($arrBind, 'd', $goodsStatistics['goodsPrice']);
                $this->db->bind_param_push($arrBind, 'd', $goodsStatistics['costPrice']);
                $this->db->bind_param_push($arrBind, 'd', $goodsStatistics['goodsDcPrice']);
                $this->db->bind_param_push($arrBind, 'd', $goodsStatistics['divisionUseDeposit']);
                $this->db->bind_param_push($arrBind, 'd', $goodsStatistics['divisionUseMileage']);
                $this->db->bind_query($strSQL, $arrBind);
                unset($arrBind);
            }
            unset($goodsStatistics);

            $arrBind = [];
            $strSQL = "UPDATE " . DB_ORDER_GOODS . " SET `statisticsOrderFl` = ? WHERE `sno` = ?";
            $this->db->bind_param_push($arrBind, 's', 'y');
            $this->db->bind_param_push($arrBind, 'i', $orderGoodsVal['sno']);
            $this->db->bind_query($strSQL, $arrBind);
            unset($arrBind);
        }
    }

    /**
     * setOrderDeliveryStatistics
     * 주문 배송비 매출 통계 데이터 생성
     *
     * @param bool $realTimeKey
     */
    public function setOrderDeliveryStatistics($realTimeKey = false)
    {
        // 판매 배송비 데이터 생성
        if ($realTimeKey) {
            $orderDelivery['paymentDtOver'] = $this->orderPolicy['statisticsDate']->format('Y-m-d H:i:s');
        } else {
            $orderDelivery['paymentDt'] = $this->orderPolicy['statisticsDate']->format('Ymd');
        }
        $orderDeliveryArr = $this->getOrderDeliveryInfo(
            $orderDelivery,
            'o.mallSno, o.orderChannelFl, o.orderTypeFl, o.memNo, o.settleKind, o.orderIp, ' .
            'TRIM(LEFT(oi.receiverAddress, 4)) as area, ' .
            'm.birthDt, m.sexFl, ' .
            'og.paymentDt, ' .
            'od.sno, od.scmNo, od.orderNo, od.deliveryTaxInfo, od.statisticsOrderFl, ' .
            'od.deliveryCharge, od.divisionDeliveryCharge, od.divisionDeliveryUseDeposit, od.divisionDeliveryUseMileage, od.divisionMemberDeliveryDcPrice '
        );
        unset($orderDelivery);

        // @todo 대용량 시 mysql DISTINCT 와 php 중복제거 성능 테스트가 필요함. - 50만 데이터에서는 두 방법 모두 7~8초
        // join 으로 인한 중복 제거
        $orderDeliveryArr = array_map("unserialize", array_unique(array_map("serialize", $orderDeliveryArr)));

        foreach ($orderDeliveryArr as $orderDeliveryKey => $orderDeliveryVal) {
            if ($orderDeliveryVal['statisticsOrderFl'] == 'y') {
                continue;
            }
            if ($orderDeliveryVal['orderChannelFl'] == 'etc') {
                continue;
            }
            if (!$orderDeliveryVal['mallSno']) {
                $orderDeliveryVal['mallSno'] = DEFAULT_MALL_NUMBER;
            }
            if (!$orderDeliveryVal['orderIp']) {
                $orderDeliveryVal['orderIp'] = '000.000.000.000';
            }
            if (!$orderDeliveryVal['orderTypeFl']) {
                $orderDeliveryVal['orderTypeFl'] = 'pc';
            }
            if ($orderDeliveryVal['orderChannelFl'] == 'naverpay') {
                $orderDeliveryVal['settleKind'] = 'npay'; // 디비 필드 5자리라...
            } else if ($orderDeliveryVal['orderChannelFl'] == 'payco') {
                $orderDeliveryVal['settleKind'] = 'payco';
            } else {
                if (!$orderDeliveryVal['settleKind']) {
                    $orderDeliveryVal['settleKind'] = 'gb';
                }
            }
            $paymentDt = new DateTime($orderDeliveryVal['paymentDt']);
            $deliveryStatistics['orderYMD'] = $paymentDt->format('Ymd');
            $deliveryStatistics['mallSno'] = $orderDeliveryVal['mallSno'];
            $deliveryStatistics['kind'] = 'order';
            $deliveryStatistics['type'] = 'delivery';
            $deliveryStatistics['scmNo'] = $orderDeliveryVal['scmNo'];
            $deliveryStatistics['orderIP'] = $orderDeliveryVal['orderIp'];
            $deliveryStatistics['orderNo'] = $orderDeliveryVal['orderNo'];
            $deliveryStatistics['relationSno'] = $orderDeliveryVal['sno'];
            $deliveryStatistics['orderHour'] = $paymentDt->format('H');
            $deliveryStatistics['orderDevice'] = $orderDeliveryVal['orderTypeFl'];
            $deliveryStatistics['memNo'] = $orderDeliveryVal['memNo'];
            $deliveryStatistics['orderMemberFl'] = $orderDeliveryVal['memNo'] > 0 ? 'y' : 'n';
            $deliveryTax = explode(STR_DIVISION, $orderDeliveryVal['deliveryTaxInfo']);
            $deliveryStatistics['orderTaxFl'] = $deliveryTax[0] == 't' ? 'y' : 'n';
            if ($orderDeliveryVal['sexFl'] == 'm') {
                $gender = 'male';
            } else if ($orderDeliveryVal['sexFl'] == 'w') {
                $gender = 'female';
            } else {
                $gender = 'etc';
            }
            $deliveryStatistics['orderGender'] = $gender;
            $age = $this->getAge($orderDeliveryVal['memNo'], $orderDeliveryVal['birthDt']);
            $deliveryStatistics['orderAge'] = $age;
            $area = $this->getAreaSimpleName($orderDeliveryVal['area']);
            $deliveryStatistics['orderArea'] = $area;
            $deliveryStatistics['orderSettleKind'] = $orderDeliveryVal['settleKind'];
            $deliveryStatistics['deliveryPrice'] = $orderDeliveryVal['deliveryCharge'];
            $deliveryStatistics['deliveryDcPrice'] = $orderDeliveryVal['divisionDeliveryCharge'] + $orderDeliveryVal['divisionMemberDeliveryDcPrice'];
            $deliveryStatistics['divisionDeliveryUseDeposit'] = $orderDeliveryVal['divisionDeliveryUseDeposit'];
            $deliveryStatistics['divisionDeliveryUseMileage'] = $orderDeliveryVal['divisionDeliveryUseMileage'];

            // 중복 체크 하여 이미 값이 있으면 continue
            $order['orderYMD'] = $deliveryStatistics['orderYMD'];
            $order['mallSno'] = $deliveryStatistics['mallSno'];
            $order['kind'] = $deliveryStatistics['kind'];
            $order['type'] = $deliveryStatistics['type'];
            $order['scmNo'] = $deliveryStatistics['scmNo'];
            $order['relationSno'] = $deliveryStatistics['relationSno'];
            $orderYMD = $this->getOrderStatisticsInfo($order, 'oss.orderYMD');
            unset($order);

            if ($orderYMD) {
                $arrBind = [];
                $strSQL = "UPDATE " . DB_ORDER_SALES_STATISTICS . " SET " .
                    "`orderIP` = INET_ATON(?), `orderNo` = ?, `memNo` = ?, " .
                    "`orderHour` = ?, `orderDevice` = ?, `orderMemberFl` = ?, `orderTaxFl` = ?, " .
                    "`orderGender` = ?, `orderAge` = ?, `orderArea` = ?, `orderSettleKind` = ?, " .
                    "`deliveryPrice` = ?, `deliveryDcPrice` = ?, `divisionDeliveryUseDeposit` = ?, `divisionDeliveryUseMileage` = ?, " .
                    "`modDt` = now() " .
                    "WHERE `orderYMD` = ? AND `mallSno` = ? AND `kind` = ? AND `type` = ? AND `scmNo` = ? AND `relationSno` = ?";
                $this->db->bind_param_push($arrBind, 's', $deliveryStatistics['orderIP']);
                $this->db->bind_param_push($arrBind, 's', $deliveryStatistics['orderNo']);
                $this->db->bind_param_push($arrBind, 'i', $deliveryStatistics['memNo']);
                $this->db->bind_param_push($arrBind, 'i', $deliveryStatistics['orderHour']);
                $this->db->bind_param_push($arrBind, 's', $deliveryStatistics['orderDevice']);
                $this->db->bind_param_push($arrBind, 's', $deliveryStatistics['orderMemberFl']);
                $this->db->bind_param_push($arrBind, 's', $deliveryStatistics['orderTaxFl']);
                $this->db->bind_param_push($arrBind, 's', $deliveryStatistics['orderGender']);
                $this->db->bind_param_push($arrBind, 'i', $deliveryStatistics['orderAge']);
                $this->db->bind_param_push($arrBind, 's', $deliveryStatistics['orderArea']);
                $this->db->bind_param_push($arrBind, 's', $deliveryStatistics['orderSettleKind']);
                $this->db->bind_param_push($arrBind, 'd', $deliveryStatistics['deliveryPrice']);
                $this->db->bind_param_push($arrBind, 'd', $deliveryStatistics['deliveryDcPrice']);
                $this->db->bind_param_push($arrBind, 'd', $deliveryStatistics['divisionDeliveryUseDeposit']);
                $this->db->bind_param_push($arrBind, 'd', $deliveryStatistics['divisionDeliveryUseMileage']);
                $this->db->bind_param_push($arrBind, 'i', $deliveryStatistics['orderYMD']);
                $this->db->bind_param_push($arrBind, 'i', $deliveryStatistics['mallSno']);
                $this->db->bind_param_push($arrBind, 's', $deliveryStatistics['kind']);
                $this->db->bind_param_push($arrBind, 's', $deliveryStatistics['type']);
                $this->db->bind_param_push($arrBind, 'i', $deliveryStatistics['scmNo']);
                $this->db->bind_param_push($arrBind, 'i', $deliveryStatistics['relationSno']);
                $this->db->bind_query($strSQL, $arrBind);
                unset($arrBind);
            } else {
                $arrBind = [];
                $strSQL = "INSERT INTO " . DB_ORDER_SALES_STATISTICS . " SET " .
                    "`orderYMD` = ?, `mallSno` = ?, `kind` = ?, `type` = ?, `scmNo` = ?, `relationSno` = ?, " .
                    "`orderIP` = INET_ATON(?), `orderNo` = ?, `memNo` = ?, " .
                    "`orderHour` = ?, `orderDevice` = ?, `orderMemberFl` = ?, `orderTaxFl` = ?, " .
                    "`orderGender` = ?, `orderAge` = ?, `orderArea` = ?, `orderSettleKind` = ?, " .
                    "`deliveryPrice` = ?, `deliveryDcPrice` = ?, `divisionDeliveryUseDeposit` = ?, `divisionDeliveryUseMileage` = ?, `regDt`=now()";
                $this->db->bind_param_push($arrBind, 'i', $deliveryStatistics['orderYMD']);
                $this->db->bind_param_push($arrBind, 'i', $deliveryStatistics['mallSno']);
                $this->db->bind_param_push($arrBind, 's', $deliveryStatistics['kind']);
                $this->db->bind_param_push($arrBind, 's', $deliveryStatistics['type']);
                $this->db->bind_param_push($arrBind, 'i', $deliveryStatistics['scmNo']);
                $this->db->bind_param_push($arrBind, 'i', $deliveryStatistics['relationSno']);
                $this->db->bind_param_push($arrBind, 's', $deliveryStatistics['orderIP']);
                $this->db->bind_param_push($arrBind, 's', $deliveryStatistics['orderNo']);
                $this->db->bind_param_push($arrBind, 'i', $deliveryStatistics['memNo']);
                $this->db->bind_param_push($arrBind, 'i', $deliveryStatistics['orderHour']);
                $this->db->bind_param_push($arrBind, 's', $deliveryStatistics['orderDevice']);
                $this->db->bind_param_push($arrBind, 's', $deliveryStatistics['orderMemberFl']);
                $this->db->bind_param_push($arrBind, 's', $deliveryStatistics['orderTaxFl']);
                $this->db->bind_param_push($arrBind, 's', $deliveryStatistics['orderGender']);
                $this->db->bind_param_push($arrBind, 'i', $deliveryStatistics['orderAge']);
                $this->db->bind_param_push($arrBind, 's', $deliveryStatistics['orderArea']);
                $this->db->bind_param_push($arrBind, 's', $deliveryStatistics['orderSettleKind']);
                $this->db->bind_param_push($arrBind, 'd', $deliveryStatistics['deliveryPrice']);
                $this->db->bind_param_push($arrBind, 'd', $deliveryStatistics['deliveryDcPrice']);
                $this->db->bind_param_push($arrBind, 'd', $deliveryStatistics['divisionDeliveryUseDeposit']);
                $this->db->bind_param_push($arrBind, 'd', $deliveryStatistics['divisionDeliveryUseMileage']);
                $this->db->bind_query($strSQL, $arrBind);
                unset($arrBind);
            }
            unset($deliveryStatistics);

            $arrBind = [];
            $strSQL = "UPDATE " . DB_ORDER_DELIVERY . " SET `statisticsOrderFl` = ? WHERE `sno` = ?";
            $this->db->bind_param_push($arrBind, 's', 'y');
            $this->db->bind_param_push($arrBind, 'i', $orderDeliveryVal['sno']);
            $this->db->bind_query($strSQL, $arrBind);
            unset($arrBind);
        }
    }

    /**
     * setRefundStatistics
     * 환불 상품 / 배송비 매출 통계 데이터 생성
     *
     * @param bool $realTimeKey
     */
    public function setRefundStatistics($realTimeKey = false)
    {
        // 환불 데이터 생성
        if ($realTimeKey) {
            $orderRefund['handleDtOver'] = $this->orderPolicy['statisticsDate']->format('Y-m-d H:i:s');
        } else {
            $orderRefund['handleDt'] = $this->orderPolicy['statisticsDate']->format('Ymd');
        }
        $orderRefund['handleMode'] = ['r', 'e'];
        $orderRefundArr = $this->getOrderRefundInfo(
            $orderRefund,
            'o.mallSno, o.orderChannelFl, o.orderTypeFl, o.memNo, o.settleKind, o.orderIp, ' .
            'TRIM(LEFT(oi.receiverAddress, 4)) as area, ' .
            'm.birthDt, m.sexFl, ' .
            'og.scmNo, og.goodsTaxInfo, og.divisionGoodsDeliveryUseDeposit, og.divisionGoodsDeliveryUseMileage, ' .
            'og.divisionUseDeposit, og.divisionUseMileage, ' .
            'od.deliveryTaxInfo, ' .
            'oh.sno, oh.orderNo, oh.handleDt, ' .
            'oh.refundPrice, oh.refundDeliveryCharge, oh.refundCharge, oh.refundUseDeposit, oh.refundUseMileage, oh.refundDeliveryUseDeposit, oh.refundDeliveryUseMileage '
        );
        unset($orderRefund);

        foreach ($orderRefundArr as $orderRefundKey => $orderRefundVal) {
            if($orderRefundVal['orderChannelFl'] == 'etc'){
                continue;
            }
            if (!$orderRefundVal['mallSno']) {
                $orderRefundVal['mallSno'] = DEFAULT_MALL_NUMBER;
            }
            if (!$orderRefundVal['orderIp']) {
                $orderRefundVal['orderIp'] = '000.000.000.000';
            }
            if (!$orderRefundVal['orderTypeFl']) {
                $orderRefundVal['orderTypeFl'] = 'pc';
            }
            if ($orderRefundVal['orderChannelFl'] == 'naverpay') {
                $orderRefundVal['settleKind'] = 'npay'; // 디비 필드 5자리라...
            } else if ($orderRefundVal['orderChannelFl'] == 'payco') {
                $orderRefundVal['settleKind'] = 'payco';
            } else {
                if (!$orderRefundVal['settleKind']) {
                    $orderRefundVal['settleKind'] = 'gb';
                }
            }
            $handleDt = new DateTime($orderRefundVal['handleDt']);
            $refundGoodsStatistics['orderYMD'] = $handleDt->format('Ymd');
            $refundGoodsStatistics['mallSno'] = $orderRefundVal['mallSno'];
            $refundGoodsStatistics['kind'] = 'refund';
            $refundGoodsStatistics['type'] = 'goods';
            $refundGoodsStatistics['scmNo'] = $orderRefundVal['scmNo'];
            $refundGoodsStatistics['orderIP'] = $orderRefundVal['orderIp'];
            $refundGoodsStatistics['orderNo'] = $orderRefundVal['orderNo'];
            $refundGoodsStatistics['relationSno'] = $orderRefundVal['sno'];
            $refundGoodsStatistics['orderHour'] = $handleDt->format('H');
            $refundGoodsStatistics['orderDevice'] = $orderRefundVal['orderTypeFl'];
            $refundGoodsStatistics['memNo'] = $orderRefundVal['memNo'];
            $refundGoodsStatistics['orderMemberFl'] = $orderRefundVal['memNo'] > 0 ? 'y' : 'n';
            if ($orderRefundVal['sexFl'] == 'm') {
                $gender = 'male';
            } else if ($orderRefundVal['sexFl'] == 'w') {
                $gender = 'female';
            } else {
                $gender = 'etc';
            }
            $refundGoodsStatistics['orderGender'] = $gender;
            $age = $this->getAge($orderRefundVal['memNo'], $orderRefundVal['birthDt']);
            $refundGoodsStatistics['orderAge'] = $age;
            $area = $this->getAreaSimpleName($orderRefundVal['area']);
            $refundGoodsStatistics['orderArea'] = $area;
            $refundGoodsStatistics['orderSettleKind'] = $orderRefundVal['settleKind'];
            $refundGoodsStatistics['type'] = 'goods';
            $goodsTax = explode(STR_DIVISION, $orderRefundVal['goodsTaxInfo']);
            $refundGoodsStatistics['orderTaxFl'] = $goodsTax[0] == 't' ? 'y' : 'n';
            $refundGoodsStatistics['refundFeePrice'] = $orderRefundVal['refundCharge'];
            $refundGoodsStatistics['refundUseDeposit'] = $orderRefundVal['divisionUseDeposit'];
            $refundGoodsStatistics['refundUseMileage'] = $orderRefundVal['divisionUseMileage'];

            // 배송비 환불이 있다면
            if ($orderRefundVal['refundDeliveryCharge'] + $orderRefundVal['refundDeliveryUseDeposit'] + $orderRefundVal['refundDeliveryUseMileage'] > 0) {
                // 상품 환불 금액 = 상품환불 금액 - 환불 수수료 ( 전체환불금액 + 환불 수수료 - 배송비 환불금액 )
                $refundGoodsStatistics['refundGoodsPrice'] = $orderRefundVal['refundPrice'] + $orderRefundVal['refundCharge'] - $orderRefundVal['refundDeliveryCharge'];

                // 배송비 환불 데이터
                $handleDt = new DateTime($orderRefundVal['handleDt']);
                $refundDeliveryStatistics['orderYMD'] = $handleDt->format('Ymd');
                $refundDeliveryStatistics['mallSno'] = $orderRefundVal['mallSno'];
                $refundDeliveryStatistics['kind'] = 'refund';
                $refundDeliveryStatistics['type'] = 'delivery';
                $refundDeliveryStatistics['scmNo'] = $orderRefundVal['scmNo'];
                $refundDeliveryStatistics['orderIP'] = $orderRefundVal['orderIp'];
                $refundDeliveryStatistics['orderNo'] = $orderRefundVal['orderNo'];
                $refundDeliveryStatistics['relationSno'] = $orderRefundVal['sno'];
                $refundDeliveryStatistics['orderHour'] = $handleDt->format('H');
                $refundDeliveryStatistics['orderDevice'] = $orderRefundVal['orderTypeFl'];
                $refundDeliveryStatistics['memNo'] = $orderRefundVal['memNo'];
                $refundDeliveryStatistics['orderMemberFl'] = $orderRefundVal['memNo'] > 0 ? 'y' : 'n';
                if ($orderRefundVal['sexFl'] == 'm') {
                    $gender = 'male';
                } else if ($orderRefundVal['sexFl'] == 'w') {
                    $gender = 'female';
                } else {
                    $gender = 'etc';
                }
                $refundDeliveryStatistics['orderGender'] = $gender;
                $age = $this->getAge($orderRefundVal['memNo'], $orderRefundVal['birthDt']);
                $refundDeliveryStatistics['orderAge'] = $age;
                $area = $this->getAreaSimpleName($orderRefundVal['area']);
                $refundDeliveryStatistics['orderArea'] = $area;
                $refundDeliveryStatistics['orderSettleKind'] = $orderRefundVal['settleKind'];
                $deliveryTax = explode(STR_DIVISION, $orderRefundVal['deliveryTaxInfo']);
                $refundDeliveryStatistics['orderTaxFl'] = $deliveryTax[0] == 't' ? 'y' : 'n';
                $refundDeliveryStatistics['refundDeliveryPrice'] = $orderRefundVal['refundDeliveryCharge'];
                if((int)$orderRefundVal['refundDeliveryUseDeposit'] > 0){
                    $refundDeliveryStatistics['refundUseDeposit'] = $orderRefundVal['refundDeliveryUseDeposit'];
                }
                else {
                    $refundDeliveryStatistics['refundUseDeposit'] = $orderRefundVal['divisionGoodsDeliveryUseDeposit']; // 레거시보존
                }
                if((int)$orderRefundVal['refundDeliveryUseMileage'] > 0){
                    $refundDeliveryStatistics['refundUseMileage'] = $orderRefundVal['refundDeliveryUseMileage'];
                }
                else {
                    $refundDeliveryStatistics['refundUseMileage'] = $orderRefundVal['divisionGoodsDeliveryUseMileage']; // 레거시보존
                }

                // 배송비 환불 처리
                // 중복 체크 하여 이미 값이 있으면 update
                $order['orderYMD'] = $refundDeliveryStatistics['orderYMD'];
                $order['mallSno'] = $refundDeliveryStatistics['mallSno'];
                $order['kind'] = $refundDeliveryStatistics['kind'];
                $order['type'] = $refundDeliveryStatistics['type'];
                $order['scmNo'] = $refundDeliveryStatistics['scmNo'];
                $order['relationSno'] = $refundDeliveryStatistics['relationSno'];
                $orderYMD = $this->getOrderStatisticsInfo($order, 'oss.orderYMD');
                unset($order);

                if ($orderYMD) {
                    $arrBind = [];
                    $strSQL = "UPDATE " . DB_ORDER_SALES_STATISTICS . " SET " .
                        "`orderIP` = INET_ATON(?), `orderNo` = ?, `memNo` = ?, " .
                        "`orderHour` = ?, `orderDevice` = ?, `orderMemberFl` = ?, `orderTaxFl` = ?, " .
                        "`orderGender` = ?, `orderAge` = ?, `orderArea` = ?, `orderSettleKind` = ?, " .
                        "`refundDeliveryPrice` = ?, `refundUseDeposit` = ?, `refundUseMileage` = ?, `modDt` = now() " .
                        "WHERE `orderYMD` = ? AND `mallSno` = ? AND `kind` = ? AND `type` = ? AND `scmNo` = ? AND `relationSno` = ?";
                    $this->db->bind_param_push($arrBind, 's', $refundDeliveryStatistics['orderIP']);
                    $this->db->bind_param_push($arrBind, 's', $refundDeliveryStatistics['orderNo']);
                    $this->db->bind_param_push($arrBind, 'i', $refundDeliveryStatistics['memNo']);
                    $this->db->bind_param_push($arrBind, 'i', $refundDeliveryStatistics['orderHour']);
                    $this->db->bind_param_push($arrBind, 's', $refundDeliveryStatistics['orderDevice']);
                    $this->db->bind_param_push($arrBind, 's', $refundDeliveryStatistics['orderMemberFl']);
                    $this->db->bind_param_push($arrBind, 's', $refundDeliveryStatistics['orderTaxFl']);
                    $this->db->bind_param_push($arrBind, 's', $refundDeliveryStatistics['orderGender']);
                    $this->db->bind_param_push($arrBind, 'i', $refundDeliveryStatistics['orderAge']);
                    $this->db->bind_param_push($arrBind, 's', $refundDeliveryStatistics['orderArea']);
                    $this->db->bind_param_push($arrBind, 's', $refundDeliveryStatistics['orderSettleKind']);
                    $this->db->bind_param_push($arrBind, 'd', $refundDeliveryStatistics['refundDeliveryPrice']);
                    $this->db->bind_param_push($arrBind, 'd', $refundDeliveryStatistics['refundUseDeposit']);
                    $this->db->bind_param_push($arrBind, 'd', $refundDeliveryStatistics['refundUseMileage']);
                    $this->db->bind_param_push($arrBind, 'i', $refundDeliveryStatistics['orderYMD']);
                    $this->db->bind_param_push($arrBind, 'i', $refundDeliveryStatistics['mallSno']);
                    $this->db->bind_param_push($arrBind, 's', $refundDeliveryStatistics['kind']);
                    $this->db->bind_param_push($arrBind, 's', $refundDeliveryStatistics['type']);
                    $this->db->bind_param_push($arrBind, 'i', $refundDeliveryStatistics['scmNo']);
                    $this->db->bind_param_push($arrBind, 'i', $refundDeliveryStatistics['relationSno']);
                    $this->db->bind_query($strSQL, $arrBind);
                    unset($arrBind);
                } else {
                    $arrBind = [];
                    $strSQL = "INSERT INTO " . DB_ORDER_SALES_STATISTICS . " SET " .
                        "`orderYMD` = ?, `mallSno` = ?, `kind` = ?, `type` = ?, `scmNo` = ?, `relationSno` = ?, " .
                        "`orderIP` = INET_ATON(?), `orderNo` = ?, `memNo` = ?, " .
                        "`orderHour` = ?, `orderDevice` = ?, `orderMemberFl` = ?, `orderTaxFl` = ?, " .
                        "`orderGender` = ?, `orderAge` = ?, `orderArea` = ?, `orderSettleKind` = ?, " .
                        "`refundDeliveryPrice` = ?, `refundUseDeposit` = ?, `refundUseMileage` = ?, `regDt`=now()";
                    $this->db->bind_param_push($arrBind, 'i', $refundDeliveryStatistics['orderYMD']);
                    $this->db->bind_param_push($arrBind, 'i', $refundDeliveryStatistics['mallSno']);
                    $this->db->bind_param_push($arrBind, 's', $refundDeliveryStatistics['kind']);
                    $this->db->bind_param_push($arrBind, 's', $refundDeliveryStatistics['type']);
                    $this->db->bind_param_push($arrBind, 'i', $refundDeliveryStatistics['scmNo']);
                    $this->db->bind_param_push($arrBind, 'i', $refundDeliveryStatistics['relationSno']);
                    $this->db->bind_param_push($arrBind, 's', $refundDeliveryStatistics['orderIP']);
                    $this->db->bind_param_push($arrBind, 's', $refundDeliveryStatistics['orderNo']);
                    $this->db->bind_param_push($arrBind, 'i', $refundDeliveryStatistics['memNo']);
                    $this->db->bind_param_push($arrBind, 'i', $refundDeliveryStatistics['orderHour']);
                    $this->db->bind_param_push($arrBind, 's', $refundDeliveryStatistics['orderDevice']);
                    $this->db->bind_param_push($arrBind, 's', $refundDeliveryStatistics['orderMemberFl']);
                    $this->db->bind_param_push($arrBind, 's', $refundDeliveryStatistics['orderTaxFl']);
                    $this->db->bind_param_push($arrBind, 's', $refundDeliveryStatistics['orderGender']);
                    $this->db->bind_param_push($arrBind, 'i', $refundDeliveryStatistics['orderAge']);
                    $this->db->bind_param_push($arrBind, 's', $refundDeliveryStatistics['orderArea']);
                    $this->db->bind_param_push($arrBind, 's', $refundDeliveryStatistics['orderSettleKind']);
                    $this->db->bind_param_push($arrBind, 'd', $refundDeliveryStatistics['refundDeliveryPrice']);
                    $this->db->bind_param_push($arrBind, 'd', $refundDeliveryStatistics['refundUseDeposit']);
                    $this->db->bind_param_push($arrBind, 'd', $refundDeliveryStatistics['refundUseMileage']);
                    $this->db->bind_query($strSQL, $arrBind);
                    unset($arrBind);
                }
            } else { // 배송비 환불이 없다면
                $refundGoodsStatistics['refundGoodsPrice'] = $orderRefundVal['refundPrice'] + $orderRefundVal['refundCharge'];
            }

            // 중복 체크 하여 이미 값이 있으면 update
            $order['orderYMD'] = $refundGoodsStatistics['orderYMD'];
            $order['mallSno'] = $refundGoodsStatistics['mallSno'];
            $order['kind'] = $refundGoodsStatistics['kind'];
            $order['type'] = $refundGoodsStatistics['type'];
            $order['scmNo'] = $refundGoodsStatistics['scmNo'];
            $order['relationSno'] = $refundGoodsStatistics['relationSno'];
            $orderYMD = $this->getOrderStatisticsInfo($order, 'oss.orderYMD');
            unset($order);

            if ($orderYMD) {
                $arrBind = [];
                $strSQL = "UPDATE " . DB_ORDER_SALES_STATISTICS . " SET " .
                    "`orderIP` = INET_ATON(?), `orderNo` = ?, `memNo` = ?, " .
                    "`orderHour` = ?, `orderDevice` = ?, `orderMemberFl` = ?, `orderTaxFl` = ?, " .
                    "`orderGender` = ?, `orderAge` = ?, `orderArea` = ?, `orderSettleKind` = ?, " .
                    "`refundGoodsPrice` = ?, `refundFeePrice` = ?, `refundUseDeposit` = ?, `refundUseMileage` = ?, `modDt` = now() " .
                    "WHERE `orderYMD` = ? AND `mallSno` = ? AND `kind` = ? AND `type` = ? AND `scmNo` = ? AND `relationSno` = ?";
                $this->db->bind_param_push($arrBind, 's', $refundGoodsStatistics['orderIP']);
                $this->db->bind_param_push($arrBind, 's', $refundGoodsStatistics['orderNo']);
                $this->db->bind_param_push($arrBind, 'i', $refundGoodsStatistics['memNo']);
                $this->db->bind_param_push($arrBind, 'i', $refundGoodsStatistics['orderHour']);
                $this->db->bind_param_push($arrBind, 's', $refundGoodsStatistics['orderDevice']);
                $this->db->bind_param_push($arrBind, 's', $refundGoodsStatistics['orderMemberFl']);
                $this->db->bind_param_push($arrBind, 's', $refundGoodsStatistics['orderTaxFl']);
                $this->db->bind_param_push($arrBind, 's', $refundGoodsStatistics['orderGender']);
                $this->db->bind_param_push($arrBind, 'i', $refundGoodsStatistics['orderAge']);
                $this->db->bind_param_push($arrBind, 's', $refundGoodsStatistics['orderArea']);
                $this->db->bind_param_push($arrBind, 's', $refundGoodsStatistics['orderSettleKind']);
                $this->db->bind_param_push($arrBind, 'd', $refundGoodsStatistics['refundGoodsPrice']);
                $this->db->bind_param_push($arrBind, 'd', $refundGoodsStatistics['refundFeePrice']);
                $this->db->bind_param_push($arrBind, 'd', $refundGoodsStatistics['refundUseDeposit']);
                $this->db->bind_param_push($arrBind, 'd', $refundGoodsStatistics['refundUseMileage']);
                $this->db->bind_param_push($arrBind, 'i', $refundGoodsStatistics['orderYMD']);
                $this->db->bind_param_push($arrBind, 'i', $refundGoodsStatistics['mallSno']);
                $this->db->bind_param_push($arrBind, 's', $refundGoodsStatistics['kind']);
                $this->db->bind_param_push($arrBind, 's', $refundGoodsStatistics['type']);
                $this->db->bind_param_push($arrBind, 'i', $refundGoodsStatistics['scmNo']);
                $this->db->bind_param_push($arrBind, 'i', $refundGoodsStatistics['relationSno']);
                $this->db->bind_query($strSQL, $arrBind);
                unset($arrBind);
            } else {
                $arrBind = [];
                $strSQL = "INSERT INTO " . DB_ORDER_SALES_STATISTICS . " SET " .
                    "`orderYMD` = ?, `mallSno` = ?, `kind` = ?, `type` = ?, `scmNo` = ?, `relationSno` = ?, " .
                    "`orderIP` = INET_ATON(?), `orderNo` = ?, `memNo` = ?, " .
                    "`orderHour` = ?, `orderDevice` = ?, `orderMemberFl` = ?, `orderTaxFl` = ?, " .
                    "`orderGender` = ?, `orderAge` = ?, `orderArea` = ?, `orderSettleKind` = ?, " .
                    "`refundGoodsPrice` = ?, `refundFeePrice` = ?, `refundUseDeposit` = ?, `refundUseMileage` = ?, `regDt`=now()";
                $this->db->bind_param_push($arrBind, 'i', $refundGoodsStatistics['orderYMD']);
                $this->db->bind_param_push($arrBind, 'i', $refundGoodsStatistics['mallSno']);
                $this->db->bind_param_push($arrBind, 's', $refundGoodsStatistics['kind']);
                $this->db->bind_param_push($arrBind, 's', $refundGoodsStatistics['type']);
                $this->db->bind_param_push($arrBind, 'i', $refundGoodsStatistics['scmNo']);
                $this->db->bind_param_push($arrBind, 'i', $refundGoodsStatistics['relationSno']);
                $this->db->bind_param_push($arrBind, 's', $refundGoodsStatistics['orderIP']);
                $this->db->bind_param_push($arrBind, 's', $refundGoodsStatistics['orderNo']);
                $this->db->bind_param_push($arrBind, 'i', $refundGoodsStatistics['memNo']);
                $this->db->bind_param_push($arrBind, 'i', $refundGoodsStatistics['orderHour']);
                $this->db->bind_param_push($arrBind, 's', $refundGoodsStatistics['orderDevice']);
                $this->db->bind_param_push($arrBind, 's', $refundGoodsStatistics['orderMemberFl']);
                $this->db->bind_param_push($arrBind, 's', $refundGoodsStatistics['orderTaxFl']);
                $this->db->bind_param_push($arrBind, 's', $refundGoodsStatistics['orderGender']);
                $this->db->bind_param_push($arrBind, 'i', $refundGoodsStatistics['orderAge']);
                $this->db->bind_param_push($arrBind, 's', $refundGoodsStatistics['orderArea']);
                $this->db->bind_param_push($arrBind, 's', $refundGoodsStatistics['orderSettleKind']);
                $this->db->bind_param_push($arrBind, 'd', $refundGoodsStatistics['refundGoodsPrice']);
                $this->db->bind_param_push($arrBind, 'd', $refundGoodsStatistics['refundFeePrice']);
                $this->db->bind_param_push($arrBind, 'd', $refundGoodsStatistics['refundUseDeposit']);
                $this->db->bind_param_push($arrBind, 'd', $refundGoodsStatistics['refundUseMileage']);
                $this->db->bind_query($strSQL, $arrBind);
                unset($arrBind);
            }

            unset($refundGoodsStatistics);
            unset($refundDeliveryStatistics);
        }
    }

    public function setDepositMileageStatistics()
    {
        // 판매 상품 데이터 생성
        $orderGoods['dmChk'] = 'y';
        $orderGoodsArr = $this->getOrderGoodsInfo(
            $orderGoods,
            'og.sno, og.mallSno, og.scmNo, og.orderNo, og.paymentDt, ' .
            'og.divisionUseDeposit, og.divisionUseMileage '
        );
        unset($orderGoods);

        foreach ($orderGoodsArr as $key => $val) {
            $arrBind = [];
            $paymentDt = new DateTime($val['paymentDt']);
            $strSQL = "UPDATE " . DB_ORDER_SALES_STATISTICS . " SET " .
                "divisionUseDeposit = ?, divisionUseMileage = ? " .
                "WHERE `orderYMD` = ? AND `mallSno` = ? AND `kind` = ? AND `type` = ? AND `scmNo` = ? AND `relationSno` = ?";
            $this->db->bind_param_push($arrBind, 'd', $val['divisionUseDeposit']);
            $this->db->bind_param_push($arrBind, 'd', $val['divisionUseMileage']);
            $this->db->bind_param_push($arrBind, 'i', $paymentDt->format('Ymd'));
            $this->db->bind_param_push($arrBind, 'i', $val['mallSno']);
            $this->db->bind_param_push($arrBind, 's', 'order');
            $this->db->bind_param_push($arrBind, 's', 'goods');
            $this->db->bind_param_push($arrBind, 'i', $val['scmNo']);
            $this->db->bind_param_push($arrBind, 'i', $val['sno']);
            $this->db->bind_query($strSQL, $arrBind);
            unset($arrBind);
        }

        // 배송비 데이터 생성
        $orderGoods['dmChk'] = 'y';
        $orderGoodsArr = $this->getOrderDeliveryInfo(
            $orderGoods,
            'od.sno, o.mallSno, od.scmNo, od.orderNo, og.paymentDt, ' .
            'od.divisionDeliveryUseDeposit, od.divisionDeliveryUseMileage '
        );
        unset($orderGoods);

        foreach ($orderGoodsArr as $key => $val) {
            $arrBind = [];
            $paymentDt = new DateTime($val['paymentDt']);
            $strSQL = "UPDATE " . DB_ORDER_SALES_STATISTICS . " SET " .
                "`divisionDeliveryUseDeposit` = ?, `divisionDeliveryUseMileage` = ? " .
                "WHERE `orderYMD` = ? AND `mallSno` = ? AND `kind` = ? AND `type` = ? AND `scmNo` = ? AND `relationSno` = ?";
            $this->db->bind_param_push($arrBind, 'd', $val['divisionDeliveryUseDeposit']);
            $this->db->bind_param_push($arrBind, 'd', $val['divisionDeliveryUseMileage']);
            $this->db->bind_param_push($arrBind, 'i', $paymentDt->format('Ymd'));
            $this->db->bind_param_push($arrBind, 'i', $val['mallSno']);
            $this->db->bind_param_push($arrBind, 's', 'order');
            $this->db->bind_param_push($arrBind, 's', 'delivery');
            $this->db->bind_param_push($arrBind, 'i', $val['scmNo']);
            $this->db->bind_param_push($arrBind, 'i', $val['sno']);
            $this->db->bind_query($strSQL, $arrBind);
            unset($arrBind);
        }

        // 환불 데이터 생성
        $orderGoods['dmChk'] = 'y';
        $orderGoodsArr = $this->getOrderRefundInfo(
            $orderGoods,
            'oh.sno, o.mallSno, og.scmNo, og.orderNo, og.paymentDt, ' .
            'oh.refundUseDeposit, oh.refundUseMileage '
        );
        unset($orderGoods);

        foreach ($orderGoodsArr as $key => $val) {
            $arrBind = [];
            $paymentDt = new DateTime($val['paymentDt']);
            $strSQL = "UPDATE " . DB_ORDER_SALES_STATISTICS . " SET " .
                "`refundUseDeposit` = ?, `refundUseMileage` = ? " .
                "WHERE `orderYMD` = ? AND `mallSno` = ? AND `kind` = ? AND `type` = ? AND `scmNo` = ? AND `relationSno` = ?";
            $this->db->bind_param_push($arrBind, 'd', $val['refundUseDeposit']);
            $this->db->bind_param_push($arrBind, 'd', $val['refundUseMileage']);
            $this->db->bind_param_push($arrBind, 'i', $paymentDt->format('Ymd'));
            $this->db->bind_param_push($arrBind, 'i', $val['mallSno']);
            $this->db->bind_param_push($arrBind, 's', 'refund');
            $this->db->bind_param_push($arrBind, 's', 'goods');
            $this->db->bind_param_push($arrBind, 'i', $val['scmNo']);
            $this->db->bind_param_push($arrBind, 'i', $val['sno']);
            $this->db->bind_query($strSQL, $arrBind);
            unset($arrBind);
        }
    }

    public function setOrderNaverPayRefundStatistics()
    {
        $orderRefund['handleDt'] = $this->orderPolicy['statisticsDate']->format('Ymd');
        $orderRefund['orderChannelFl'] = 'naverpay';
        $orderRefundArr = $this->getOrderRefundInfo(
            $orderRefund,
            'o.mallSno, o.orderChannelFl, o.orderTypeFl, o.memNo, o.settleKind, o.orderIp, ' .
            'TRIM(LEFT(oi.receiverAddress, 4)) as area, ' .
            'm.birthDt, ' .
            'og.scmNo, og.goodsTaxInfo, ' .
            'od.deliveryTaxInfo, ' .
            'oh.sno, oh.orderNo, oh.handleDt, ' .
            'oh.refundPrice, oh.refundDeliveryCharge, oh.refundCharge, oh.refundUseDeposit, oh.refundUseMileage '
        );
        unset($orderRefund);

        foreach ($orderRefundArr as $orderRefundKey => $orderRefundVal) {
            if (!$orderRefundVal['mallSno']) {
                $orderRefundVal['mallSno'] = DEFAULT_MALL_NUMBER;
            }
            if (!$orderRefundVal['orderIp']) {
                $orderRefundVal['orderIp'] = '000.000.000.000';
            }
            if (!$orderRefundVal['orderTypeFl']) {
                $orderRefundVal['orderTypeFl'] = 'pc';
            }
            if ($orderRefundVal['orderChannelFl'] == 'naverpay') {
                $orderRefundVal['settleKind'] = 'npay'; // 디비 필드 5자리라...
            } else if ($orderRefundVal['orderChannelFl'] == 'payco') {
                $orderRefundVal['settleKind'] = 'payco';
            } else {
                if (!$orderRefundVal['settleKind']) {
                    $orderRefundVal['settleKind'] = 'gb';
                }
            }
            $handleDt = new DateTime($orderRefundVal['handleDt']);
            $refundGoodsStatistics['orderYMD'] = $handleDt->format('Ymd');
            $refundGoodsStatistics['mallSno'] = $orderRefundVal['mallSno'];
            $refundGoodsStatistics['kind'] = 'refund';
            $refundGoodsStatistics['type'] = 'goods';
            $refundGoodsStatistics['scmNo'] = $orderRefundVal['scmNo'];
            $refundGoodsStatistics['orderIP'] = $orderRefundVal['orderIp'];
            $refundGoodsStatistics['orderNo'] = $orderRefundVal['orderNo'];
            $refundGoodsStatistics['relationSno'] = $orderRefundVal['sno'];
            $refundGoodsStatistics['orderHour'] = $handleDt->format('H');
            $refundGoodsStatistics['orderDevice'] = $orderRefundVal['orderTypeFl'];
            $refundGoodsStatistics['memNo'] = $orderRefundVal['memNo'];
            $refundGoodsStatistics['orderMemberFl'] = $orderRefundVal['memNo'] > 0 ? 'y' : 'n';
            $age = $this->getAge($orderRefundVal['memNo'], $orderRefundVal['birthDt']);
            $refundGoodsStatistics['orderAge'] = $age;
            $area = $this->getAreaSimpleName($orderRefundVal['area']);
            $refundGoodsStatistics['orderArea'] = $area;
            $refundGoodsStatistics['orderSettleKind'] = $orderRefundVal['settleKind'];
            $refundGoodsStatistics['type'] = 'goods';
            $goodsTax = explode(STR_DIVISION, $orderRefundVal['goodsTaxInfo']);
            $refundGoodsStatistics['orderTaxFl'] = $goodsTax[0] == 't' ? 'y' : 'n';
            $refundGoodsStatistics['refundFeePrice'] = $orderRefundVal['refundCharge'];
            $refundGoodsStatistics['refundUseDeposit'] = $orderRefundVal['refundUseDeposit'];
            $refundGoodsStatistics['refundUseMileage'] = $orderRefundVal['refundUseMileage'];

            // 배송비 환불이 있다면
            if ($orderRefundVal['refundDeliveryCharge'] > 0) {
                // 상품 환불 금액 = 상품환불 금액 - 환불 수수료 ( 전체환불금액 + 환불 수수료 - 배송비 환불금액 )
                $refundGoodsStatistics['refundGoodsPrice'] = $orderRefundVal['refundPrice'] + $orderRefundVal['refundCharge'] - $orderRefundVal['refundDeliveryCharge'];

                // 배송비 환불 데이터
                $handleDt = new DateTime($orderRefundVal['handleDt']);
                $refundDeliveryStatistics['orderYMD'] = $handleDt->format('Ymd');
                $refundDeliveryStatistics['mallSno'] = $orderRefundVal['mallSno'];
                $refundDeliveryStatistics['kind'] = 'refund';
                $refundDeliveryStatistics['type'] = 'delivery';
                $refundDeliveryStatistics['scmNo'] = $orderRefundVal['scmNo'];
                $refundDeliveryStatistics['orderIP'] = $orderRefundVal['orderIp'];
                $refundDeliveryStatistics['orderNo'] = $orderRefundVal['orderNo'];
                $refundDeliveryStatistics['relationSno'] = $orderRefundVal['sno'];
                $refundDeliveryStatistics['orderHour'] = $handleDt->format('H');
                $refundDeliveryStatistics['orderDevice'] = $orderRefundVal['orderTypeFl'];
                $refundDeliveryStatistics['memNo'] = $orderRefundVal['memNo'];
                $refundDeliveryStatistics['orderMemberFl'] = $orderRefundVal['memNo'] > 0 ? 'y' : 'n';
                $age = $this->getAge($orderRefundVal['memNo'], $orderRefundVal['birthDt']);
                $refundDeliveryStatistics['orderAge'] = $age;
                $area = $this->getAreaSimpleName($orderRefundVal['area']);
                $refundDeliveryStatistics['orderArea'] = $area;
                $refundDeliveryStatistics['orderSettleKind'] = $orderRefundVal['settleKind'];
                $deliveryTax = explode(STR_DIVISION, $orderRefundVal['deliveryTaxInfo']);
                $refundDeliveryStatistics['orderTaxFl'] = $deliveryTax[0] == 't' ? 'y' : 'n';
                $refundDeliveryStatistics['refundDeliveryPrice'] = $orderRefundVal['refundDeliveryCharge'];

                // 배송비 환불 처리
                // 중복 체크 하여 이미 값이 있으면 update
                $order['orderYMD'] = $refundDeliveryStatistics['orderYMD'];
                $order['mallSno'] = $refundDeliveryStatistics['mallSno'];
                $order['kind'] = $refundDeliveryStatistics['kind'];
                $order['type'] = $refundDeliveryStatistics['type'];
                $order['scmNo'] = $refundDeliveryStatistics['scmNo'];
                $order['relationSno'] = $refundDeliveryStatistics['relationSno'];
                $orderYMD = $this->getOrderStatisticsInfo($order, 'oss.orderYMD');
                unset($order);

                if ($orderYMD) {
                    $arrBind = [];
                    $strSQL = "UPDATE " . DB_ORDER_SALES_STATISTICS . " SET " .
                        "`orderIP` = INET_ATON(?), `orderNo` = ?, `memNo` = ?, " .
                        "`orderHour` = ?, `orderDevice` = ?, `orderMemberFl` = ?, `orderTaxFl` = ?, " .
                        "`orderAge` = ?, `orderArea` = ?, `orderSettleKind` = ?, " .
                        "`refundDeliveryPrice` = ?, `modDt` = now() " .
                        "WHERE `orderYMD` = ? AND `mallSno` = ? AND `kind` = ? AND `type` = ? AND `scmNo` = ? AND `relationSno` = ?";
                    $this->db->bind_param_push($arrBind, 's', $refundDeliveryStatistics['orderIP']);
                    $this->db->bind_param_push($arrBind, 's', $refundDeliveryStatistics['orderNo']);
                    $this->db->bind_param_push($arrBind, 'i', $refundDeliveryStatistics['memNo']);
                    $this->db->bind_param_push($arrBind, 'i', $refundDeliveryStatistics['orderHour']);
                    $this->db->bind_param_push($arrBind, 's', $refundDeliveryStatistics['orderDevice']);
                    $this->db->bind_param_push($arrBind, 's', $refundDeliveryStatistics['orderMemberFl']);
                    $this->db->bind_param_push($arrBind, 's', $refundDeliveryStatistics['orderTaxFl']);
                    $this->db->bind_param_push($arrBind, 'i', $refundDeliveryStatistics['orderAge']);
                    $this->db->bind_param_push($arrBind, 's', $refundDeliveryStatistics['orderArea']);
                    $this->db->bind_param_push($arrBind, 's', $refundDeliveryStatistics['orderSettleKind']);
                    $this->db->bind_param_push($arrBind, 'd', $refundDeliveryStatistics['refundDeliveryPrice']);
                    $this->db->bind_param_push($arrBind, 'i', $refundDeliveryStatistics['orderYMD']);
                    $this->db->bind_param_push($arrBind, 'i', $refundDeliveryStatistics['mallSno']);
                    $this->db->bind_param_push($arrBind, 's', $refundDeliveryStatistics['kind']);
                    $this->db->bind_param_push($arrBind, 's', $refundDeliveryStatistics['type']);
                    $this->db->bind_param_push($arrBind, 'i', $refundDeliveryStatistics['scmNo']);
                    $this->db->bind_param_push($arrBind, 'i', $refundDeliveryStatistics['relationSno']);
                    $this->db->bind_query($strSQL, $arrBind);
                    unset($arrBind);
                } else {
                    $arrBind = [];
                    $strSQL = "INSERT INTO " . DB_ORDER_SALES_STATISTICS . " SET " .
                        "`orderYMD` = ?, `mallSno` = ?, `kind` = ?, `type` = ?, `scmNo` = ?, `relationSno` = ?, " .
                        "`orderIP` = INET_ATON(?), `orderNo` = ?, `memNo` = ?, " .
                        "`orderHour` = ?, `orderDevice` = ?, `orderMemberFl` = ?, `orderTaxFl` = ?, " .
                        "`orderAge` = ?, `orderArea` = ?, `orderSettleKind` = ?, " .
                        "`refundDeliveryPrice` = ?, `regDt`=now()";
                    $this->db->bind_param_push($arrBind, 'i', $refundDeliveryStatistics['orderYMD']);
                    $this->db->bind_param_push($arrBind, 'i', $refundDeliveryStatistics['mallSno']);
                    $this->db->bind_param_push($arrBind, 's', $refundDeliveryStatistics['kind']);
                    $this->db->bind_param_push($arrBind, 's', $refundDeliveryStatistics['type']);
                    $this->db->bind_param_push($arrBind, 'i', $refundDeliveryStatistics['scmNo']);
                    $this->db->bind_param_push($arrBind, 'i', $refundDeliveryStatistics['relationSno']);
                    $this->db->bind_param_push($arrBind, 's', $refundDeliveryStatistics['orderIP']);
                    $this->db->bind_param_push($arrBind, 's', $refundDeliveryStatistics['orderNo']);
                    $this->db->bind_param_push($arrBind, 'i', $refundDeliveryStatistics['memNo']);
                    $this->db->bind_param_push($arrBind, 'i', $refundDeliveryStatistics['orderHour']);
                    $this->db->bind_param_push($arrBind, 's', $refundDeliveryStatistics['orderDevice']);
                    $this->db->bind_param_push($arrBind, 's', $refundDeliveryStatistics['orderMemberFl']);
                    $this->db->bind_param_push($arrBind, 's', $refundDeliveryStatistics['orderTaxFl']);
                    $this->db->bind_param_push($arrBind, 'i', $refundDeliveryStatistics['orderAge']);
                    $this->db->bind_param_push($arrBind, 's', $refundDeliveryStatistics['orderArea']);
                    $this->db->bind_param_push($arrBind, 's', $refundDeliveryStatistics['orderSettleKind']);
                    $this->db->bind_param_push($arrBind, 'd', $refundDeliveryStatistics['refundDeliveryPrice']);
                    $this->db->bind_query($strSQL, $arrBind);
                    unset($arrBind);
                }
            } else { // 배송비 환불이 없다면
                $refundGoodsStatistics['refundGoodsPrice'] = $orderRefundVal['refundPrice'] + $orderRefundVal['refundCharge'];
            }

            // 중복 체크 하여 이미 값이 있으면 update
            $order['orderYMD'] = $refundGoodsStatistics['orderYMD'];
            $order['mallSno'] = $refundGoodsStatistics['mallSno'];
            $order['kind'] = $refundGoodsStatistics['kind'];
            $order['type'] = $refundGoodsStatistics['type'];
            $order['scmNo'] = $refundGoodsStatistics['scmNo'];
            $order['relationSno'] = $refundGoodsStatistics['relationSno'];
            $orderYMD = $this->getOrderStatisticsInfo($order, 'oss.orderYMD');
            unset($order);

            if ($orderYMD) {
                $arrBind = [];
                $strSQL = "UPDATE " . DB_ORDER_SALES_STATISTICS . " SET " .
                    "`orderIP` = INET_ATON(?), `orderNo` = ?, `memNo` = ?, " .
                    "`orderHour` = ?, `orderDevice` = ?, `orderMemberFl` = ?, `orderTaxFl` = ?, " .
                    "`orderAge` = ?, `orderArea` = ?, `orderSettleKind` = ?, " .
                    "`refundGoodsPrice` = ?, `refundFeePrice` = ?, `refundUseDeposit` = ?, `refundUseMileage` = ?, `modDt` = now() " .
                    "WHERE `orderYMD` = ? AND `mallSno` = ? AND `kind` = ? AND `type` = ? AND `scmNo` = ? AND `relationSno` = ?";
                $this->db->bind_param_push($arrBind, 's', $refundGoodsStatistics['orderIP']);
                $this->db->bind_param_push($arrBind, 's', $refundGoodsStatistics['orderNo']);
                $this->db->bind_param_push($arrBind, 'i', $refundGoodsStatistics['memNo']);
                $this->db->bind_param_push($arrBind, 'i', $refundGoodsStatistics['orderHour']);
                $this->db->bind_param_push($arrBind, 's', $refundGoodsStatistics['orderDevice']);
                $this->db->bind_param_push($arrBind, 's', $refundGoodsStatistics['orderMemberFl']);
                $this->db->bind_param_push($arrBind, 's', $refundGoodsStatistics['orderTaxFl']);
                $this->db->bind_param_push($arrBind, 'i', $refundGoodsStatistics['orderAge']);
                $this->db->bind_param_push($arrBind, 's', $refundGoodsStatistics['orderArea']);
                $this->db->bind_param_push($arrBind, 's', $refundGoodsStatistics['orderSettleKind']);
                $this->db->bind_param_push($arrBind, 'd', $refundGoodsStatistics['refundGoodsPrice']);
                $this->db->bind_param_push($arrBind, 'd', $refundGoodsStatistics['refundFeePrice']);
                $this->db->bind_param_push($arrBind, 'd', $refundGoodsStatistics['refundUseDeposit']);
                $this->db->bind_param_push($arrBind, 'd', $refundGoodsStatistics['refundUseMileage']);
                $this->db->bind_param_push($arrBind, 'i', $refundGoodsStatistics['orderYMD']);
                $this->db->bind_param_push($arrBind, 'i', $refundGoodsStatistics['mallSno']);
                $this->db->bind_param_push($arrBind, 's', $refundGoodsStatistics['kind']);
                $this->db->bind_param_push($arrBind, 's', $refundGoodsStatistics['type']);
                $this->db->bind_param_push($arrBind, 'i', $refundGoodsStatistics['scmNo']);
                $this->db->bind_param_push($arrBind, 'i', $refundGoodsStatistics['relationSno']);
                $this->db->bind_query($strSQL, $arrBind);
                unset($arrBind);
            } else {
                $arrBind = [];
                $strSQL = "INSERT INTO " . DB_ORDER_SALES_STATISTICS . " SET " .
                    "`orderYMD` = ?, `mallSno` = ?, `kind` = ?, `type` = ?, `scmNo` = ?, `relationSno` = ?, " .
                    "`orderIP` = INET_ATON(?), `orderNo` = ?, `memNo` = ?, " .
                    "`orderHour` = ?, `orderDevice` = ?, `orderMemberFl` = ?, `orderTaxFl` = ?, " .
                    "`orderAge` = ?, `orderArea` = ?, `orderSettleKind` = ?, " .
                    "`refundGoodsPrice` = ?, `refundFeePrice` = ?, `refundUseDeposit` = ?, `refundUseMileage` = ?, `regDt`=now()";
                $this->db->bind_param_push($arrBind, 'i', $refundGoodsStatistics['orderYMD']);
                $this->db->bind_param_push($arrBind, 'i', $refundGoodsStatistics['mallSno']);
                $this->db->bind_param_push($arrBind, 's', $refundGoodsStatistics['kind']);
                $this->db->bind_param_push($arrBind, 's', $refundGoodsStatistics['type']);
                $this->db->bind_param_push($arrBind, 'i', $refundGoodsStatistics['scmNo']);
                $this->db->bind_param_push($arrBind, 'i', $refundGoodsStatistics['relationSno']);
                $this->db->bind_param_push($arrBind, 's', $refundGoodsStatistics['orderIP']);
                $this->db->bind_param_push($arrBind, 's', $refundGoodsStatistics['orderNo']);
                $this->db->bind_param_push($arrBind, 'i', $refundGoodsStatistics['memNo']);
                $this->db->bind_param_push($arrBind, 'i', $refundGoodsStatistics['orderHour']);
                $this->db->bind_param_push($arrBind, 's', $refundGoodsStatistics['orderDevice']);
                $this->db->bind_param_push($arrBind, 's', $refundGoodsStatistics['orderMemberFl']);
                $this->db->bind_param_push($arrBind, 's', $refundGoodsStatistics['orderTaxFl']);
                $this->db->bind_param_push($arrBind, 'i', $refundGoodsStatistics['orderAge']);
                $this->db->bind_param_push($arrBind, 's', $refundGoodsStatistics['orderArea']);
                $this->db->bind_param_push($arrBind, 's', $refundGoodsStatistics['orderSettleKind']);
                $this->db->bind_param_push($arrBind, 'd', $refundGoodsStatistics['refundGoodsPrice']);
                $this->db->bind_param_push($arrBind, 'd', $refundGoodsStatistics['refundFeePrice']);
                $this->db->bind_param_push($arrBind, 'd', $refundGoodsStatistics['refundUseDeposit']);
                $this->db->bind_param_push($arrBind, 'd', $refundGoodsStatistics['refundUseMileage']);
                $this->db->bind_query($strSQL, $arrBind);
                unset($arrBind);
            }

            unset($refundGoodsStatistics);
            unset($refundDeliveryStatistics);
        }
    }

    /**
     * setOrderSalesStatistics
     * 매출 통계 데이터 생성
     *
     * @param bool $realTimeKey
     * @param $isJob
     * @return bool
     */
    public function setOrderSalesStatistics($realTimeKey = false, $isJob)
    {
        if ($this->orderSalesStatisticsProcess == 'periodic') {
            // 판매 상품 데이터 생성
            $this->setOrderGoodsStatistics($realTimeKey);

            // 판매 배송비 데이터 생성
            $this->setOrderDeliveryStatistics($realTimeKey);

            // 환불 데이터 생성 ( 환불 상품 / 환불 배송비 )
            $this->setRefundStatistics($realTimeKey);
        } else {
            $this->procedureExistFl = $this->isExistOrderSalesProcedure();

            if ($this->procedureExistFl) {
                $this->runOrderSalesProcedure($isJob);
            }
        }
        return true;
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

    /**
     * 매출 통계 프로시저 실행
     *
     * @param $isJob
     */
    public function runOrderSalesProcedure($isJob=false)
    {
        $procedureQuery = 'CALL '.$this->procedureName.'(0,@err)';
        if ($isJob) {
            $procedureQuery = 'CALL '.$this->procedureName.'(1,@err)';
        }
        $this->db->prepare($procedureQuery);
        $this->db->execute();
        $this->db->stmt_close();
    }

    /**
     * 매출 통계 프로시저 파일 유무
     *
     * @return bool
     */
    public function isExistOrderSalesProcedure()
    {
        $procedureExistFl = false;
        $procedureShowQuery = 'SHOW PROCEDURE STATUS';
        $result = $this->db->query_fetch($procedureShowQuery);
        if ($result) {
            $procedureExistFl = true;
        }

        return $procedureExistFl;
    }

    /**
     * 튜닝 파일 존재 유무
     *
     * @return bool
   */
    public function isExistOrderSalesStatisticsTuningFile()
    {
        $tuningFileExistFl = false;
        $tuningFilePath = \UserFilePath::module("Component/Order/OrderSalesStatistics.php")->getPathName();
        if(\FileHandler::isExists($tuningFilePath)) {
            $tuningFileExistFl = true;
        }
        return $tuningFileExistFl;
    }

    /**
     * 실시간 통계일 때 마지막 통계시간과 비교
     *
     * @param $lastGoodsStatisticsTime
     * @return bool
     */
    public function timeDifferenceInRealTime($lastGoodsStatisticsTime) {
        $setStatisticsFl = false;
        $todayDate = new DateTime();

        $diffDays = $lastGoodsStatisticsTime->diff($todayDate)->d;
        $diffHours = $lastGoodsStatisticsTime->diff($todayDate)->h;
        $diffMinutes = $lastGoodsStatisticsTime->diff($todayDate)->i;
        $diffSeconds =  $lastGoodsStatisticsTime->diff($todayDate)->s;

        if ($diffDays > 0 || $diffHours > 0 || $diffMinutes > 0) {
            $setStatisticsFl = true;
        } else {
            if ($diffSeconds >= $this->orderPolicy['realStatisticsSeconds']) {
                $setStatisticsFl = true;
            }
        }
        return $setStatisticsFl;
    }

    /**
     * 주기 통계일 때 마지막 통계시간과 비교
     *
     * @param $lastGoodsStatisticsTime
     * @return bool
     */
    public function timeDifferenceInPeriodic($lastGoodsStatisticsTime) {
        $setStatisticsFl = false;
        $todayDate = new DateTime();

        if ($lastGoodsStatisticsTime->diff($todayDate)->d > 0) {
            $setStatisticsFl = true;
        } else {
            if ($lastGoodsStatisticsTime->diff($todayDate)->h >= $this->orderPolicy['realStatisticsHour']) {
                $setStatisticsFl = true;
            }
        }

        return $setStatisticsFl;
    }

}
