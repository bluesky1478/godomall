<?php
/**
 * ScmAdmin Class
 *
 * @author    su
 * @version   1.0
 * @since     1.0
 * @copyright ⓒ 2016, NHN godo: Corp.
 */

namespace Bundle\Component\Scm;

use Component\Member\Manager;
use Component\Order\OrderAdmin;
use Component\Category\Category;
use Component\Database\DBTableField;
use Component\Validator\Validator;
use Framework\Security\XXTEA;
use Framework\Utility\ArrayUtils;
use Framework\Utility\HttpUtils;
use Framework\Utility\NumberUtils;
use Framework\Utility\StringUtils;
use Globals;
use Request;
use Session;

class ScmAdjust
{
    public $scmAdjustState = [];
    public $scmAdjustKind = [];
    public $scmAdjustType = [];
    public $truncPolicy = [];
    /**
     * @var \Framework\Database\DBTool null|object 데이터베이스 인스턴스(싱글턴)
     */
    protected $db;
    /**
     * @var array 쿼리 조건 바인딩
     */
    protected $arrBind = [];
    /**
     * @var array 리스트 검색 조건
     */
    protected $arrWhere = [];
    /**
     * @var array 체크박스 체크 조건
     */
    protected $checked = [];
    /**
     * @var array 검색
     */
    protected $search = [];
    protected $order;
    /**
     * @var array 세금계산서 설정 값
     */
    private $taxConf;

    /**
     * 생성자
     *
     * @author su
     */
    public function __construct()
    {
        if (!is_object($this->db)) {
            $this->db = \App::load('DB');
        }

        // 세금계산서 설정
        if (!is_array($this->taxConf)) {
            $this->taxConf = gd_policy('order.taxInvoice');
        }
        $this->order = new OrderAdmin();

        $this->scmAdjustState = [
            '1'  => __('정산요청'),
            '10' => __('정산확정'),
            //            '20' => __('세금계산서'),
            '30' => __('지급완료'),
            '40' => __('이월'),
            '50' => __('보류'),
            '-1' => __('반려'),
        ];
        $this->scmAdjustKind = [
            'm' => __('수기'),
            'a' => __('일반'),
        ];
        $this->scmAdjustType = [
            'o'  => __('주문상품'),
            'd'  => __('배송비'),
            'oa' => __('정산후환불') . '(' . __('주문상품') . ')',
            'da' => __('정산후환불') . '(' . __('배송비') . ')',
        ];
        $this->truncPolicy = Globals::get('gTrunc.scm_calculate');
    }

    /**
     * 정산 정보 출력
     *
     * 완성된 쿼리문은 $db->strField , $db->strJoin , $db->strWhere , $db->strGroup , $db->strOrder , $db->strLimit 멤버 변수를
     * 이용할수 있습니다.
     *
     * @param string      $scmAdjustNo    정산 고유 번호 (기본 null)
     * @param string      $scmAdjustField 출력할 필드명 (기본 null)
     * @param array       $arrBind        bind 처리 배열 (기본 null)
     * @param bool|string $dataArray      return 값을 배열처리 (기본값 false)
     *
     * @return array 정산 정보
     *
     * @author su
     */
    public function getScmAdjustInfo($scmAdjustNo = null, $scmAdjustField = null, $arrBind = null, $dataArray = false)
    {
        if (is_null($arrBind)) {
            // $arrBind = array();
        }
        if ($scmAdjustNo) {
            if ($this->db->strWhere) {
                $this->db->strWhere = " sa.scmAdjustNo = ? AND " . $this->db->strWhere;
            } else {
                $this->db->strWhere = " sa.scmAdjustNo = ?";
            }
            $this->db->bind_param_push($arrBind, 'i', $scmAdjustNo);
        }
        if ($scmAdjustField) {
            if ($this->db->strField) {
                $this->db->strField = $scmAdjustField . ', ' . $this->db->strField;
            } else {
                $this->db->strField = $scmAdjustField;
            }
        }
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_SCM_ADJUST . ' as sa ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);

        if (count($getData) == 1 && $dataArray === false) {
            return gd_htmlspecialchars_stripslashes($getData[0]);
        }

        return gd_htmlspecialchars_stripslashes($getData);
    }

    /**
     * 정산 세금계산서 정보 출력
     *
     * 완성된 쿼리문은 $db->strField , $db->strJoin , $db->strWhere , $db->strGroup , $db->strOrder , $db->strLimit 멤버 변수를
     * 이용할수 있습니다.
     *
     * @param string      $scmAdjustTaxBillNo    세금계산서 고유 번호 (기본 null)
     * @param string      $scmAdjustTaxBillField 출력할 필드명 (기본 null)
     * @param array       $arrBind               bind 처리 배열 (기본 null)
     * @param bool|string $dataArray             return 값을 배열처리 (기본값 false)
     *
     * @return array 정산 정보
     *
     * @author su
     */
    public function getTaxBillInfo($scmAdjustTaxBillNo = null, $scmAdjustTaxBillField = null, $arrBind = null, $dataArray = false)
    {
        if (is_null($arrBind)) {
            // $arrBind = array();
        }
        if ($scmAdjustTaxBillNo) {
            if ($this->db->strWhere) {
                $this->db->strWhere = " satb.scmAdjustTaxBillNo = ? AND " . $this->db->strWhere;
            } else {
                $this->db->strWhere = " satb.scmAdjustTaxBillNo = ?";
            }
            $this->db->bind_param_push($arrBind, 'i', $scmAdjustTaxBillNo);
        }
        if ($scmAdjustTaxBillField) {
            if ($this->db->strField) {
                $this->db->strField = $scmAdjustTaxBillField . ', ' . $this->db->strField;
            } else {
                $this->db->strField = $scmAdjustTaxBillField;
            }
        }
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_SCM_ADJUST_TAXBILL . ' as satb ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);

        if (count($getData) == 1 && $dataArray === false) {
            return gd_htmlspecialchars_stripslashes($getData[0]);
        }

        return gd_htmlspecialchars_stripslashes($getData);
    }

    /**
     * 정산 로그 정보 출력
     *
     * 완성된 쿼리문은 $db->strField , $db->strJoin , $db->strWhere , $db->strGroup , $db->strOrder , $db->strLimit 멤버 변수를
     * 이용할수 있습니다.
     *
     * @param integer     $scmAdjustNo       정산 고유 번호 (기본 null)
     * @param string      $scmAdjustLogField 출력할 필드명 (기본 null)
     * @param array       $arrBind           bind 처리 배열 (기본 null)
     * @param bool|string $dataArray         return 값을 배열처리 (기본값 false)
     *
     * @return array 정산 정보
     *
     * @author su
     */
    public function getScmAdjustLogInfo($scmAdjustNo = null, $scmAdjustLogField = null, $arrBind = null, $dataArray = false)
    {
        if ($scmAdjustNo) {
            if ($this->db->strWhere) {
                $this->db->strWhere = " sa.scmAdjustNo = ? AND " . $this->db->strWhere;
            } else {
                $this->db->strWhere = " sa.scmAdjustNo = ?";
            }
            $this->db->bind_param_push($arrBind, 'i', $scmAdjustNo);
        }
        if ($scmAdjustLogField) {
            if ($this->db->strField) {
                $this->db->strField = $scmAdjustLogField . ', ' . $this->db->strField;
            } else {
                $this->db->strField = $scmAdjustLogField;
            }
        }
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_SCM_ADJUST_LOG . ' as sa ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);

        if (count($getData) == 1 && $dataArray === false) {
            return gd_htmlspecialchars_stripslashes($getData[0]);
        }

        return gd_htmlspecialchars_stripslashes($getData);
    }

    /**
     * 배송비 정보 출력
     *
     * 완성된 쿼리문은 $db->strField , $db->strJoin , $db->strWhere , $db->strGroup , $db->strOrder , $db->strLimit 멤버 변수를
     * 이용할수 있습니다.
     *
     * @param string      $orderDeliveryNo    배송비 고유 번호 (기본 null)
     * @param string      $orderDeliveryField 출력할 필드명 (기본 null)
     * @param array       $arrBind            bind 처리 배열 (기본 null)
     * @param bool|string $dataArray          return 값을 배열처리 (기본값 false)
     *
     * @return array 배송비 정보
     *
     * @author su
     */
    public function getOrderDeliveryInfo($orderDeliveryNo = null, $orderDeliveryField = null, $arrBind = null, $dataArray = false)
    {
        if (is_null($arrBind)) {
            // $arrBind = array();
        }
        if ($orderDeliveryNo) {
            if ($this->db->strWhere) {
                $this->db->strWhere = " od.sno = ? AND " . $this->db->strWhere;
            } else {
                $this->db->strWhere = " od.sno = ?";
            }
            $this->db->bind_param_push($arrBind, 'i', $orderDeliveryNo);
        }
        if ($orderDeliveryField) {
            if ($this->db->strField) {
                $this->db->strField = $orderDeliveryField . ', ' . $this->db->strField;
            } else {
                $this->db->strField = $orderDeliveryField;
            }
        }
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_DELIVERY . ' as od ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);

        if (count($getData) == 1 && $dataArray === false) {
            return gd_htmlspecialchars_stripslashes($getData[0]);
        }

        return gd_htmlspecialchars_stripslashes($getData);
    }

    /**
     * 관리자 공급사 주문상품 정산 요청 공급사별 Sort
     */
    public function getScmSortOrderGoods($orderGoodsNoArr)
    {
        if (!$orderGoodsNoArr) {
            throw new \Exception(__('정산 요청 할 주문 상품을 선택해 주세요.'));
        }
        $scmOrderGoods = [];
        foreach ($orderGoodsNoArr as $goodsKey => $goodsVal) {
            $orderGoodsAllData = $this->getOrderAllGoods($goodsVal);
            $scmOrderGoods[$orderGoodsAllData['scmNo']][] = $orderGoodsAllData;
        }

        return $scmOrderGoods;
    }

    /**
     * 관리자 공급사 배송비 정산 요청 공급사별 Sort
     */
    public function getScmSortOrderDelivery($orderDeliveryNoArr)
    {
        if (!$orderDeliveryNoArr) {
            throw new \Exception(__('정산 요청 할 배송을 선택해 주세요.'));
        }
        $scmOrderDelivery = [];
        foreach ($orderDeliveryNoArr as $deliveryKey => $deliveryVal) {
            $orderDeliveryAllData = $this->getOrderDeliveryInfo($deliveryVal, 'sno,orderNo,scmNo,commission,scmAdjustNo,scmAdjustAfterNo,deliverySno,deliveryCharge');
            $scmOrderDelivery[$orderDeliveryAllData['scmNo']][] = $orderDeliveryAllData;
        }

        return $scmOrderDelivery;
    }

    /**
     * 관리자 공급사 주문상품과 주문상품의 추가상품 데이터
     */
    public function getOrderAllGoods($orderGoodsNo)
    {
        if (!$orderGoodsNo) {
            throw new \Exception(__('정산 요청 할 주문 상품을 선택해 주세요.'));
        }
        $params = [
            'sno',
            'orderCd',
            'orderNo',
            'orderGroupCd',
            'orderStatus',
            'scmNo',
            'commission',
            'scmAdjustNo',
            'scmAdjustAfterNo',
            'goodsNo',
            'goodsCnt',
            'goodsPrice',
            'optionPrice',
            'optionTextPrice',
            'timeSalePrice',
        ];
        $orderGoodsData = $this->order->getOrderGoods(null, $orderGoodsNo, null, $params, null);
        $orderGoodsAllData = [];
        $orderGoodsAllData['sno'] = $orderGoodsData[0]['sno'];
        $orderGoodsAllData['orderCd'] = $orderGoodsData[0]['orderCd'];
        $orderGoodsAllData['orderNo'] = $orderGoodsData[0]['orderNo'];
        $orderGoodsAllData['orderGroupCd'] = $orderGoodsData[0]['orderGroupCd'];
        $orderGoodsAllData['orderStatus'] = $orderGoodsData[0]['orderStatus'];
        $orderGoodsAllData['scmNo'] = $orderGoodsData[0]['scmNo'];
        $orderGoodsAllData['commission'] = $orderGoodsData[0]['commission'];
        $orderGoodsAllData['scmAdjustNo'] = $orderGoodsData[0]['scmAdjustNo'];
        $orderGoodsAllData['scmAdjustAfterNo'] = $orderGoodsData[0]['scmAdjustAfterNo'];
        $orderGoodsAllData['goodsNo'] = $orderGoodsData[0]['goodsNo'];
        $orderGoodsAllData['goodsCnt'] = $orderGoodsData[0]['goodsCnt'];
        $orderGoodsAllData['goodsPrice'] = $orderGoodsData[0]['goodsPrice'];
        $orderGoodsAllData['optionPrice'] = $orderGoodsData[0]['optionPrice'];
        $orderGoodsAllData['optionTextPrice'] = $orderGoodsData[0]['optionTextPrice'];
        $orderGoodsAllData['timeSalePrice'] = $orderGoodsData[0]['timeSalePrice'];
        $orderAddGoodsData = $this->order->getOrderAddGoods(
            $orderGoodsData[0]['orderNo'], $orderGoodsData[0]['orderCd'], [
                'commission',
                'goodsCnt',
                'goodsPrice',
                'scmAdjustNo',
                'scmAdjustAfterNo',
            ]
        );
        if ($orderAddGoodsData) {
            $orderGoodsAllData['addGoods'] = $orderAddGoodsData;
        }

        return $orderGoodsAllData;
    }

    public function getScmAdjustStateCheck($changeState, $nowState)
    {
        $checkState = false;
        if ($nowState == '1') {
            if ($changeState == '10' || $changeState == '40' || $changeState == '50' || $changeState == '-1') {
                $checkState = true;
            }
        } else if ($nowState == '10') {
            //            if ($changeState == '20') {
            if ($changeState == '30') {
                $checkState = true;
            }
            //        } else if ($nowState == '20') {
            //            if ($changeState == '30') {
            //                $checkState = true;
            //            }
        } else if ($nowState == '30') {
            // 지급완료 후 상태변경 안됨
        } else if ($nowState == '40') {
            if ($changeState == '10') {
                $checkState = true;
            }
        } else if ($nowState == '50') {
            if ($changeState == '10') {
                $checkState = true;
            }
        }

        return $checkState;
    }

    /**
     * 공급사의 정산 처리 중인 건
     *
     * @param integer $scmNo 공급사 고유 번호
     *
     * @return array $scmAdjustStateCountArr 공급사 정산처리 상태 / 갯수
     *
     * @author su
     */
    public function getScmAdjustStateCount($scmNo)
    {
        $this->db->strWhere = " sa.scmNo = ? ";
        $this->db->strField = " sa.scmAdjustState , count(sa.scmNo) as count ";
        $this->db->strGroup = " sa.scmAdjustState ";
        $this->db->bind_param_push($arrBind, 'i', $scmNo);
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_SCM_ADJUST . ' as sa ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);

        $scmAdjustStateCountArr = [];
        foreach ($getData as $key => $val) {
            $scmAdjustStateCountArr[$key]['name'] = $this->scmAdjustState[$val['scmAdjustState']];
            $scmAdjustStateCountArr[$key]['count'] = $val['count'];
        }

        return $scmAdjustStateCountArr;
    }

    /**
     * 공급사 정산 리스트
     *
     * @author su
     */
    public function getScmAdjustList()
    {
        $getValue = Request::get()->toArray();

        $this->setScmAdjustListSearch();

        gd_isset($getValue['page'], 1);
        gd_isset($getValue['pageNum'], 10);

        $page = \App::load('\\Component\\Page\\Page', $getValue['page']);
        $page->page['list'] = $getValue['pageNum'];
        if (Manager::isProvider()) {
            // 공급사로 로그인한 경우 기존 scm에 값 설정
            $scmQuery = ' AND sa.scmNo = ' . Session::get('manager.scmNo');
        }
        list($page->recode['amount']) = $this->db->fetch('SELECT count(scmAdjustNo) FROM ' . DB_SCM_ADJUST . ' as sa WHERE sa.scmAdjustNo > 0' . $scmQuery, 'array');
        $page->setPage();
        $page->setUrl(\Request::getQueryString());

        $this->db->strField = "sa.*, sal.managerScmNo, sal.managerId, sal.managerNm,m.isDelete ";
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
        $this->db->strJoin = ' LEFT JOIN ' . DB_SCM_ADJUST_LOG . ' as sal ON sa.scmAdjustNo = sal.scmAdjustNo AND sa.scmAdjustState = sal.scmAdjustState LEFT JOIN ' . DB_MANAGER . ' as m ON m.sno = sal.managerNo ';
        $this->db->strOrder = $this->search['sort'];
        $this->db->strLimit = $page->recode['start'] . ',' . $getValue['pageNum'];

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_SCM_ADJUST . ' as sa ' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $this->arrBind);
        Manager::displayListData($data);

        // 검색 레코드 수
        $table = DB_SCM_ADJUST . ' as sa';
        $page->recode['total'] = $this->db->query_count($query, $table, $this->arrBind);
        $page->setPage();

        $getData['data'] = gd_htmlspecialchars_stripslashes(gd_isset($data));
        $getData['sort'] = $getValue['sort'];
        $getData['search'] = gd_htmlspecialchars($this->search);
        $getData['checked'] = $this->checked;

        return $getData;
    }


    /**
     * 공급사 정산 리스트 다운로드
     *
     * @author su
     */
    public function getScmAdjustListExcel($getValue)
    {
        $this->setScmAdjustListSearch($getValue);

        if ($getValue['chk'] && is_array($getValue['chk'])) {
            $this->arrWhere[] = 'sa.scmAdjustNo IN (' . implode(',', $getValue['chk']) . ')';
        }

        $this->db->strField = "sa.*, sal.managerScmNo, sal.managerId, sal.managerNm";
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
        $this->db->strJoin = 'LEFT JOIN ' . DB_SCM_ADJUST_LOG . ' as sal ON sa.scmAdjustNo = sal.scmAdjustNo AND sa.scmAdjustState = sal.scmAdjustState';
        $this->db->strOrder = $this->search['sort'];

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_SCM_ADJUST . ' as sa ' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $this->arrBind);

        $convertData = $this->convertScmAdjustArrData($data);

        foreach ($data as $key => $val) {
            $searchData['scmAdjustType'] = $val['scmAdjustType'];
            if ($val['scmAdjustType'] == 'o' || $val['scmAdjustType'] == 'oa') {
                $scmAdjustGoodsNoArr = ArrayUtils::objectToArray(json_decode($val['orderGoodsNo']));
                $data[$key]['info'] = $this->getScmAdjustOrderDetailList($scmAdjustGoodsNoArr['goods']);
            } else if ($val['scmAdjustType'] == 'd' || $val['scmAdjustType'] == 'da') {
                $scmAdjustDeliveryNoArr = ArrayUtils::objectToArray(json_decode($val['orderDeliveryNo']));
                $data[$key]['info'] = $this->getScmAdjustDeliveryDetailList($scmAdjustDeliveryNoArr['delivery']);
            }
        }

        return ['data' => $data, 'convertData' => $convertData];
    }

    /**
     * 공급사 정산 통합리스트 합계
     *
     * @author su
     */
    public function getScmAdjustTotal(&$getValue)
    {
        $this->_setScmAdjustTotalSearch($getValue);

        if ($getValue['chkScm'] && is_array($getValue['chkScm'])) {
            $this->arrWhere[] = 'sa.scmNo IN (' . implode(',', $getValue['chkScm']) . ')';
        }

        // 정산 매출
        $this->db->strField = "sa.*";
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_SCM_ADJUST . ' as sa ' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $this->arrBind);
        unset($this->arrBind);
        unset($this->arrWhere);

        // 공급사 갯수 (중복제거)
        $scmNo = array_unique(array_column($data, 'scmNo'));
        $getScmAdjustTotal['summary']['count']['scm'] = count($scmNo);
        // 정산 상태별 갯수
        $scmAdjustState = array_column($data, 'scmAdjustState');
        $countStep = array_count_values($scmAdjustState);
        // -1=> 반려 갯수
        $getScmAdjustTotal['summary']['count']['-1'] = $countStep[-1];
        // 1=> 정산요청 갯수
        $getScmAdjustTotal['summary']['count']['1'] = $countStep[1];
        // 10=> 정산확정 갯수
        $getScmAdjustTotal['summary']['count']['10'] = $countStep[10];
        // 30=> 지급완료 갯수
        $getScmAdjustTotal['summary']['count']['30'] = $countStep[30];

        // 정산 매출 금액
        $getScmAdjustTotal['summary']['price']['total'] = 0;
        // 정산 수수료 금액
        $getScmAdjustTotal['summary']['price']['commission'] = 0;
        // 정산 금액
        $getScmAdjustTotal['summary']['price']['adjust'] = 0;
        // 정산후 환불 매출 금액
        $getScmAdjustTotal['summary']['price']['refundTotal'] = 0;
        // 정산후 환불 수수료 금액
        $getScmAdjustTotal['summary']['price']['refundCommission'] = 0;
        // 정산후 환불 정산 금액
        $getScmAdjustTotal['summary']['price']['refundAdjust'] = 0;

        foreach ($data as $key => $val) {
            if ($val['scmAdjustState'] == '10' || $val['scmAdjustState'] == '30') {
                if ($val['scmAdjustType'] == 'o' || $val['scmAdjustType'] == 'd') {
                    $getScmAdjustTotal['summary']['price']['total'] += $val['scmAdjustTotalPrice'];
                    $getScmAdjustTotal['summary']['price']['commission'] += $val['scmAdjustCommissionPrice'];
                    $getScmAdjustTotal['summary']['price']['adjust'] += $val['scmAdjustPrice'];
                } else if ($val['scmAdjustType'] == 'oa' || $val['scmAdjustType'] == 'da') {
                    $getScmAdjustTotal['summary']['price']['refundTotal'] += $val['scmAdjustTotalPrice'];
                    $getScmAdjustTotal['summary']['price']['refundCommission'] += $val['scmAdjustCommissionPrice'];
                    $getScmAdjustTotal['summary']['price']['refundAdjust'] += $val['scmAdjustPrice'];
                }
            }
            if ($val['scmNo'] > 0) {
                $scm = new Scm();
                $getScmAdjustTotal['list'][$val['scmNo']]['scm']['scmNo'] = $val['scmNo'];
                $scmData = $scm->getScmInfo($val['scmNo'], 'companyNm, scmCode, scmType');
                $getScmAdjustTotal['list'][$val['scmNo']]['scm']['scmName'] = $scmData['companyNm'];
                $getScmAdjustTotal['list'][$val['scmNo']]['scm']['scmCode'] = $scmData['scmCode'];
                $getScmAdjustTotal['list'][$val['scmNo']]['scm']['scmType'] = $scmData['scmType'];

                if ($val['scmAdjustState'] == '1') {
                    if ($val['scmAdjustType'] == 'o') {
                        $getScmAdjustTotal['list'][$val['scmNo']]['1']['order']['total'] += $val['scmAdjustTotalPrice'];
                        $getScmAdjustTotal['list'][$val['scmNo']]['1']['order']['commission'] += $val['scmAdjustCommissionPrice'];
                        $getScmAdjustTotal['list'][$val['scmNo']]['1']['order']['adjust'] += $val['scmAdjustPrice'];
                        $getScmAdjustTotal['list'][$val['scmNo']]['1']['order']['ea'] += 1;
                    } else if ($val['scmAdjustType'] == 'd') {
                        $getScmAdjustTotal['list'][$val['scmNo']]['1']['delivery']['total'] += $val['scmAdjustTotalPrice'];
                        $getScmAdjustTotal['list'][$val['scmNo']]['1']['delivery']['commission'] += $val['scmAdjustCommissionPrice'];
                        $getScmAdjustTotal['list'][$val['scmNo']]['1']['delivery']['adjust'] += $val['scmAdjustPrice'];
                        $getScmAdjustTotal['list'][$val['scmNo']]['1']['delivery']['ea'] += 1;
                    } else if ($val['scmAdjustType'] == 'oa') {
                        $getScmAdjustTotal['list'][$val['scmNo']]['1']['orderAfter']['total'] += $val['scmAdjustTotalPrice'];
                        $getScmAdjustTotal['list'][$val['scmNo']]['1']['orderAfter']['commission'] += $val['scmAdjustCommissionPrice'];
                        $getScmAdjustTotal['list'][$val['scmNo']]['1']['orderAfter']['adjust'] += $val['scmAdjustPrice'];
                        $getScmAdjustTotal['list'][$val['scmNo']]['1']['orderAfter']['ea'] += 1;
                    } else if ($val['scmAdjustType'] == 'da') {
                        $getScmAdjustTotal['list'][$val['scmNo']]['1']['deliveryAfter']['total'] += $val['scmAdjustTotalPrice'];
                        $getScmAdjustTotal['list'][$val['scmNo']]['1']['deliveryAfter']['commission'] += $val['scmAdjustCommissionPrice'];
                        $getScmAdjustTotal['list'][$val['scmNo']]['1']['deliveryAfter']['adjust'] += $val['scmAdjustPrice'];
                        $getScmAdjustTotal['list'][$val['scmNo']]['1']['deliveryAfter']['ea'] += 1;
                    }
                }
                if ($val['scmAdjustState'] == '10') {
                    if ($val['scmAdjustType'] == 'o') {
                        $getScmAdjustTotal['list'][$val['scmNo']]['10']['order']['total'] += $val['scmAdjustTotalPrice'];
                        $getScmAdjustTotal['list'][$val['scmNo']]['10']['order']['commission'] += $val['scmAdjustCommissionPrice'];
                        $getScmAdjustTotal['list'][$val['scmNo']]['10']['order']['adjust'] += $val['scmAdjustPrice'];
                        $getScmAdjustTotal['list'][$val['scmNo']]['10']['order']['ea'] += 1;
                    } else if ($val['scmAdjustType'] == 'd') {
                        $getScmAdjustTotal['list'][$val['scmNo']]['10']['delivery']['total'] += $val['scmAdjustTotalPrice'];
                        $getScmAdjustTotal['list'][$val['scmNo']]['10']['delivery']['commission'] += $val['scmAdjustCommissionPrice'];
                        $getScmAdjustTotal['list'][$val['scmNo']]['10']['delivery']['adjust'] += $val['scmAdjustPrice'];
                        $getScmAdjustTotal['list'][$val['scmNo']]['10']['delivery']['ea'] += 1;
                    } else if ($val['scmAdjustType'] == 'oa') {
                        $getScmAdjustTotal['list'][$val['scmNo']]['10']['orderAfter']['total'] += $val['scmAdjustTotalPrice'];
                        $getScmAdjustTotal['list'][$val['scmNo']]['10']['orderAfter']['commission'] += $val['scmAdjustCommissionPrice'];
                        $getScmAdjustTotal['list'][$val['scmNo']]['10']['orderAfter']['adjust'] += $val['scmAdjustPrice'];
                        $getScmAdjustTotal['list'][$val['scmNo']]['10']['orderAfter']['ea'] += 1;
                    } else if ($val['scmAdjustType'] == 'da') {
                        $getScmAdjustTotal['list'][$val['scmNo']]['10']['deliveryAfter']['total'] += $val['scmAdjustTotalPrice'];
                        $getScmAdjustTotal['list'][$val['scmNo']]['10']['deliveryAfter']['commission'] += $val['scmAdjustCommissionPrice'];
                        $getScmAdjustTotal['list'][$val['scmNo']]['10']['deliveryAfter']['adjust'] += $val['scmAdjustPrice'];
                        $getScmAdjustTotal['list'][$val['scmNo']]['10']['deliveryAfter']['ea'] += 1;
                    }
                }
                if ($val['scmAdjustState'] == '30') {
                    if ($val['scmAdjustType'] == 'o') {
                        $getScmAdjustTotal['list'][$val['scmNo']]['30']['order']['total'] += $val['scmAdjustTotalPrice'];
                        $getScmAdjustTotal['list'][$val['scmNo']]['30']['order']['commission'] += $val['scmAdjustCommissionPrice'];
                        $getScmAdjustTotal['list'][$val['scmNo']]['30']['order']['adjust'] += $val['scmAdjustPrice'];
                        $getScmAdjustTotal['list'][$val['scmNo']]['30']['order']['ea'] += 1;
                    } else if ($val['scmAdjustType'] == 'd') {
                        $getScmAdjustTotal['list'][$val['scmNo']]['30']['delivery']['total'] += $val['scmAdjustTotalPrice'];
                        $getScmAdjustTotal['list'][$val['scmNo']]['30']['delivery']['commission'] += $val['scmAdjustCommissionPrice'];
                        $getScmAdjustTotal['list'][$val['scmNo']]['30']['delivery']['adjust'] += $val['scmAdjustPrice'];
                        $getScmAdjustTotal['list'][$val['scmNo']]['30']['delivery']['ea'] += 1;
                    } else if ($val['scmAdjustType'] == 'oa') {
                        $getScmAdjustTotal['list'][$val['scmNo']]['30']['orderAfter']['total'] += $val['scmAdjustTotalPrice'];
                        $getScmAdjustTotal['list'][$val['scmNo']]['30']['orderAfter']['commission'] += $val['scmAdjustCommissionPrice'];
                        $getScmAdjustTotal['list'][$val['scmNo']]['30']['orderAfter']['adjust'] += $val['scmAdjustPrice'];
                        $getScmAdjustTotal['list'][$val['scmNo']]['30']['orderAfter']['ea'] += 1;
                    } else if ($val['scmAdjustType'] == 'da') {
                        $getScmAdjustTotal['list'][$val['scmNo']]['30']['deliveryAfter']['total'] += $val['scmAdjustTotalPrice'];
                        $getScmAdjustTotal['list'][$val['scmNo']]['30']['deliveryAfter']['commission'] += $val['scmAdjustCommissionPrice'];
                        $getScmAdjustTotal['list'][$val['scmNo']]['30']['deliveryAfter']['adjust'] += $val['scmAdjustPrice'];
                        $getScmAdjustTotal['list'][$val['scmNo']]['30']['deliveryAfter']['ea'] += 1;
                    }
                }
            }
        }

        $getData['data'] = gd_htmlspecialchars_stripslashes(gd_isset($getScmAdjustTotal));
        $getData['search'] = gd_htmlspecialchars($this->search);
        $getData['checked'] = $this->checked;

        return $getData;
    }

    /**
     * 공급사 정산 리스트
     *
     * @author su
     */
    public function getScmAdjustDetailList($scmAdjustNo)
    {
        $searchData = [
            'scmAdjustNo' => $scmAdjustNo,
            'periodFl'    => -1,
            'treatDate[]' => '',
            'treatDate[]' => '',
        ];
        $scmAdjustData = $this->getScmAdjustInfo($scmAdjustNo, 'sa.scmNo, sa.scmAdjustKind, sa.scmAdjustType, sa.scmAdjustPrice, sa.orderGoodsNo, sa.orderDeliveryNo, sa.scmAdjustState, sa.scmAdjustCode, sa.regDt, sa.modDt, sa.scmAdjustCode');
        $searchData['scmAdjustType'] = $scmAdjustData['scmAdjustType'];
        if ($scmAdjustData['scmAdjustType'] == 'o' || $scmAdjustData['scmAdjustType'] == 'oa') {
            $scmAdjustGoodsNoArr = ArrayUtils::objectToArray(json_decode($scmAdjustData['orderGoodsNo']));
            $getData = $this->getScmAdjustOrderDetailList($scmAdjustGoodsNoArr['goods']);
        } else if ($scmAdjustData['scmAdjustType'] == 'd' || $scmAdjustData['scmAdjustType'] == 'da') {
            $scmAdjustDeliveryNoArr = ArrayUtils::objectToArray(json_decode($scmAdjustData['orderDeliveryNo']));
            $getData = $this->getScmAdjustDeliveryDetailList($scmAdjustDeliveryNoArr['delivery']);
        }
        $getData['scmAdjustData'] = $scmAdjustData;

        return $getData;
    }

    /**
     * 관리자 공급사 정산 처리 상세 주문상품 리스트
     *
     * @param array $scmAdjustGoodsNoArr 정산처리된 주문상품 고유번호
     *
     * @return array 정산 처리 상세 주문상품 리스트 정보
     */
    public function getScmAdjustOrderDetailList($scmAdjustGoodsNoArr)
    {
        // 사용 필드
        $arrIncludeOg = [
            'goodsNo',
            'scmNo',
            'commission',
            'orderNo',
            'orderCd',
            'handleSno',
            'orderStatus',
            'goodsNm',
            'goodsCnt',
            'goodsPrice',
            'optionInfo',
            'optionPrice',
            'optionTextInfo',
            'optionTextPrice',
            'addGoodsCnt',
            'addGoodsPrice',
            'timeSalePrice',
            'invoiceNo',
            'invoiceCompanySno',
        ];
        $arrIncludeO = [
            'orderNo',
            'orderGoodsCnt',
            'memNo',
            'settlePrice',
            'settleKind',
            'orderTypeFl',
        ];
        $arrIncludeOi = [
            'orderName',
            'receiverName',
        ];
        $arrIncludeM = [
            'memId',
        ];
        $arrIncludeSm = [
            'scmNo',
            'companyNm',
            'scmAdjustCode',
        ];

        $tmpField[] = DBTableField::setTableField('tableOrder', $arrIncludeO, null, 'o');
        $tmpField[] = DBTableField::setTableField('tableOrderGoods', $arrIncludeOg, null, 'og');
        $tmpField[] = DBTableField::setTableField('tableOrderInfo', $arrIncludeOi, null, 'oi');
        $tmpField[] = DBTableField::setTableField('tableMember', $arrIncludeM, null, 'm');
        $tmpField[] = DBTableField::setTableField('tableScmManage', $arrIncludeSm, null, 'sm');

        // join 문
        $join[] = ' LEFT JOIN ' . DB_ORDER . ' o ON o.orderNo = og.orderNo ';
        $join[] = ' LEFT JOIN ' . DB_ORDER_INFO . ' oi ON og.orderNo = oi.orderNo AND oi.orderInfoCd = 1 ';
        $join[] = ' LEFT JOIN ' . DB_MEMBER . ' m ON o.memNo = m.memNo ';
        $join[] = ' LEFT JOIN ' . DB_SCM_MANAGE . ' sm ON og.scmNo = sm.scmNo ';

        // 쿼리용 필드 합침
        $tmpKey = array_keys($tmpField);
        $arrField = [];
        foreach ($tmpKey as $key) {
            $arrField = array_merge($arrField, $tmpField[$key]);
        }
        unset($tmpField, $tmpKey);

        // 현 페이지 결과
        $this->db->strField = 'og.sno, o.regDt, ' . implode(', ', $arrField) . ', LEFT(og.orderStatus, 1) as statusMode';
        $this->db->strJoin = implode('', $join);
        $this->db->strWhere = 'og.sno IN (\'' . implode('\',\'', $scmAdjustGoodsNoArr) . '\')';
        $this->db->strOrder = 'og.regDt desc, og.scmNo asc, og.orderCd asc';

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_GOODS . ' og ' . implode(' ', $query);
        $tmp = $this->db->query_fetch($strSQL, $this->arrBind);

        if (gd_isset($tmp)) {
            // 결제방법과 처리 상태 설정
            foreach ($tmp as $key => &$val) {
                $val['orderGoodsSno'] = $val['sno'];

                // 상품명처리
                $val['goodsNm'] = htmlentities(StringUtils::stripOnlyTags($val['goodsNm']));

                // 옵션처리
                $options = json_decode(gd_htmlspecialchars_stripslashes($val['optionInfo']));
                $val['optionInfo'] = $options;

                // 텍스트옵션
                $textOptions = json_decode(gd_htmlspecialchars_stripslashes($val['optionTextInfo']));
                $val['optionTextInfo'] = $textOptions;

                // 추가상품
                $val['addGoods'] = $this->order->getOrderAddGoods(
                    $val['orderNo'],
                    $val['orderCd'],
                    [
                        'sno',
                        'commission',
                        'addGoodsNo',
                        'goodsNm',
                        'goodsCnt',
                        'goodsPrice',
                        'optionNm',
                        'goodsImage',
                    ]
                );

                // 추가상품 수량 (테이블 UI 처리에 필요)
                $val['addGoodsCnt'] = empty($val['addGoods']) ? 0 : count($val['addGoods']);

                // 주문 상태명 설정
                $val['beforeStatusStr'] = $this->order->getOrderStatusAdmin($val['beforeStatus']);
                $val['settleKindStr'] = $this->order->printSettleKind($val['settleKind']);
                $val['orderStatusStr'] = $this->order->getOrderStatusAdmin($val['orderStatus']);

                // 본사 타임세일 적용시 (판매가 = 판매가 + 타임세일)
                if ($val['timeSalePrice'] > 0) {
                    $val['goodsPrice'] = $val['goodsPrice'] + $val['timeSalePrice'];
                }

                $goodsPrice = ($val['goodsPrice'] + $val['optionPrice'] + $val['optionTextPrice']) * $val['goodsCnt'];
                $goodsAdjustCommission = NumberUtils::getNumberFigure($goodsPrice * $val['commission'] / 100, $this->truncPolicy['unitPrecision'], $this->truncPolicy['unitRound']);
                $goodsAdjustPrice = $goodsPrice - $goodsAdjustCommission;

                $tmp[$key]['goodsAdjustCommission'] = $goodsAdjustCommission;
                $tmp[$key]['goodsAdjustPrice'] = $goodsAdjustPrice;

                if ($val['addGoods']) {
                    $totalAddGoodsAdjustPrice = 0;
                    foreach ($val['addGoods'] as $addKey => $addVal) {
                        $addGoodsPrice = $addVal['goodsPrice'] * $addVal['goodsCnt'];
                        $addGoodsAdjustCommission = NumberUtils::getNumberFigure($addVal['goodsPrice'] * $addVal['goodsCnt'] * $addVal['commission'] / 100, $this->truncPolicy['unitPrecision'], $this->truncPolicy['unitRound']);
                        $addGoodsAdjustPrice = $addGoodsPrice - $addGoodsAdjustCommission;
                        $tmp[$key]['addGoods'][$addKey]['addGoodsAdjustPrice'] = $addGoodsAdjustPrice;
                        $tmp[$key]['addGoods'][$addKey]['addGoodsAdjustCommission'] = $addGoodsAdjustCommission;
                        $totalAddGoodsAdjustPrice += $addGoodsAdjustPrice;
                    }
                }
                $totalAdjustPrice = $goodsAdjustPrice + $totalAddGoodsAdjustPrice;
                $totalSettlePrice = (($val['goodsPrice'] + $val['optionPrice'] + $val['optionTextPrice']) * $val['goodsCnt']) + $val['addGoodsPrice'];

                $tmp[$key]['totalSettlePrice'] = $totalSettlePrice;
                $tmp[$key]['totalAdjustPrice'] = $totalAdjustPrice;
            }

            // 각 데이터 배열화
            $getData['data'] = gd_isset($tmp);
        }

        return $getData;
    }

    /**
     * 관리자 공급사 정산 처리 상세 배송비 리스트
     *
     * @param array $scmAdjustDeliveryNoArr 정산처리된 배송비 고유번호
     *
     * @return array 정산 처리 상세 배송비 리스트 정보
     */
    public function getScmAdjustDeliveryDetailList($scmAdjustDeliveryNoArr)
    {
        // 사용 필드
        $arrIncludeOd = [
            'sno',
            'commission',
            'deliverySno',
            'deliveryCharge',
        ];
        $arrIncludeOg = [
            'orderNo',
            'goodsNo',
            'scmNo',
            'orderNo',
            'orderCd',
            'handleSno',
            'orderStatus',
            'orderDeliverySno',
        ];
        $arrIncludeO = [
            'orderNo',
            'memNo',
            'settleKind',
            'orderTypeFl',
        ];
        $arrIncludeOi = [
            'orderName',
        ];
        $arrIncludeM = [
            'memId',
        ];
        $arrIncludeSm = [
            'scmNo',
            'companyNm',
        ];

        $tmpField[] = DBTableField::setTableField('tableOrder', $arrIncludeO, null, 'o');
        $tmpField[] = DBTableField::setTableField('tableOrderGoods', $arrIncludeOg, null, 'og');
        $tmpField[] = DBTableField::setTableField('tableOrderInfo', $arrIncludeOi, null, 'oi');
        $tmpField[] = DBTableField::setTableField('tableMember', $arrIncludeM, null, 'm');
        $tmpField[] = DBTableField::setTableField('tableScmManage', $arrIncludeSm, null, 'sm');
        $tmpField[] = DBTableField::setTableField('tableOrderDelivery', $arrIncludeOd, null, 'od');

        // join 문
        $join[] = ' LEFT JOIN ' . DB_ORDER . ' o ON o.orderNo = od.orderNo ';
        $join[] = ' LEFT JOIN ' . DB_ORDER_GOODS . ' og ON og.orderNo = od.orderNo AND og.orderDeliverySno = od.sno ';
        $join[] = ' LEFT JOIN ' . DB_ORDER_INFO . ' oi ON od.orderNo = oi.orderNo AND oi.orderInfoCd = 1 ';
        $join[] = ' LEFT JOIN ' . DB_MEMBER . ' m ON o.memNo = m.memNo ';
        $join[] = ' LEFT JOIN ' . DB_SCM_MANAGE . ' sm ON od.scmNo = sm.scmNo ';

        // 쿼리용 필드 합침
        $tmpKey = array_keys($tmpField);
        $arrField = [];
        foreach ($tmpKey as $key) {
            $arrField = array_merge($arrField, $tmpField[$key]);
        }
        unset($tmpField, $tmpKey);

        // 현 페이지 결과
        $this->db->strField = 'od.sno, ' . implode(', ', $arrField) . ', LEFT(og.orderStatus, 1) as statusMode, o.regDt ';
        $this->db->strJoin = implode('', $join);
        $this->db->strWhere = 'od.sno IN (\'' . implode('\',\'', $scmAdjustDeliveryNoArr) . '\')';
        $this->db->strGroup = ' od.sno ';
        $this->db->strOrder = 'od.regDt desc, od.scmNo asc, og.orderCd asc';

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_DELIVERY . ' od ' . implode(' ', $query);
        $tmp = $this->db->query_fetch($strSQL, $this->arrBind);

        if (gd_isset($tmp)) {
            // 결제방법과 처리 상태 설정
            foreach ($tmp as $key => $val) {
                $tmp[$key]['beforeStatusStr'] = $this->order->getOrderStatusAdmin($val['beforeStatus']);
                $tmp[$key]['settleKindStr'] = $this->order->printSettleKind($val['settleKind']);
                $tmp[$key]['orderStatusStr'] = $this->order->getOrderStatusAdmin($val['orderStatus']);

                $deliveryAdjustCommission = NumberUtils::getNumberFigure($val['deliveryCharge'] * $val['commission'] / 100, $this->truncPolicy['unitPrecision'], $this->truncPolicy['unitRound']);
                $deliveryAdjustPrice = $val['deliveryCharge'] - $deliveryAdjustCommission;

                $totalAdjustPrice = $deliveryAdjustPrice + $deliveryAdjustCommission;

                $tmp[$key]['deliveryAdjustCommission'] = $deliveryAdjustCommission;
                $tmp[$key]['deliveryAdjustPrice'] = $deliveryAdjustPrice;
                $tmp[$key]['totalAdjustPrice'] = $totalAdjustPrice;
            }

            // 각 데이터 배열화
            $getData['data'] = gd_isset($tmp);
        }

        return $getData;
    }

    /**
     * 배열 공급사 정산 데이터를 검사한 별도의 Array 생성
     *
     * @param array $scmAdjustArrData 공급사 정산의 리스트
     *
     * @return array $getScmAdjustCheckArrData 공급사 정산의 리스트의 유효성 검사
     *
     * @author su
     */
    public function getScmAdjustCheckArrData($scmAdjustArrData)
    {
        $getScmAdjustCheckArrData = [];
        foreach ($scmAdjustArrData as $key => $val) {
            json_decode($val['orderGoodsNo']);
            if ($val['orderGoodsNo']) {
                foreach ($this->scmAdjustType as $typeKey => $typeVal) {
                    if ($typeKey == $val['scmAdjustType']) {
                        $convertScmAdjustArrData[$key]['scmAdjustType'] = $typeVal;
                        break;
                    }
                }
            }
            if ($val['scmAdjustKind']) {
                foreach ($this->scmAdjustKind as $kindKey => $kindVal) {
                    if ($kindKey == $val['scmAdjustKind']) {
                        $convertScmAdjustArrData[$key]['scmAdjustKind'] = $kindVal;
                        break;
                    }
                }
            }
            if ($val['scmAdjustState']) {
                foreach ($this->scmAdjustState as $stateKey => $stateVal) {
                    if ($stateKey == $val['scmAdjustState']) {
                        $convertScmAdjustArrData[$key]['scmAdjustState'] = $stateVal;
                        break;
                    }
                }
            }
        }

        return $getScmAdjustCheckArrData;
    }

    /**
     * AdjustOrder 주문상품 정산 리스트
     *
     * @param string  $searchData   검색 데이타
     * @param integer $searchPeriod 기본 조회 기간 (기본 7일)
     *
     * @return array 주문 리스트 정보
     */
    public function getScmAdjustOrderList($searchData, $searchPeriod = 6, $pageFl = true)
    {
        // --- 검색 설정
        $this->_setscmAdjustOrderSearch($searchData, $searchPeriod);

        if ($searchData['orderGoodsNo'] && is_array($searchData['orderGoodsNo'])) {
            $this->arrWhere[] = 'og.sno IN (' . implode(',', $searchData['orderGoodsNo']) . ')';
        }

        // --- 페이지 기본설정
        gd_isset($searchData['page'], 1);
        gd_isset($searchData['pageNum'], 20);

        $page = \App::load('\\Component\\Page\\Page', $searchData['page']);
        $page->page['list'] = $searchData['pageNum']; // 페이지당 리스트 수
        $page->setPage();
        $page->setUrl(\Request::getQueryString());

        // --- 정렬 설정
        $orderSort = gd_isset($searchData['sort'], 'og.regDt desc, og.scmNo asc, og.orderCd asc');

        // 사용 필드
        $arrIncludeOg = [
            'goodsType',
            'parentGoodsNo',
            'goodsNo',
            'scmNo',
            'commission',
            'orderNo',
            'orderCd',
            'handleSno',
            'orderStatus',
            'goodsNm',
            'goodsCnt',
            'goodsPrice',
            'optionInfo',
            'optionPrice',
            'optionTextInfo',
            'optionTextPrice',
            'addGoodsCnt',
            'addGoodsPrice',
            'timeSalePrice',
        ];
        $arrIncludeO = [
            'orderNo',
            'orderGoodsCnt',
            'memNo',
            'settlePrice',
            'settleKind',
            'orderTypeFl',
        ];
        $arrIncludeOi = [
            'orderName',
        ];
        $arrIncludeM = [
            'memId',
        ];
        $arrIncludeSm = [
            'scmNo',
            'companyNm',
        ];

        $tmpField[] = DBTableField::setTableField('tableOrder', $arrIncludeO, null, 'o');
        $tmpField[] = DBTableField::setTableField('tableOrderGoods', $arrIncludeOg, null, 'og');
        $tmpField[] = DBTableField::setTableField('tableOrderInfo', $arrIncludeOi, null, 'oi');
        $tmpField[] = DBTableField::setTableField('tableMember', $arrIncludeM, null, 'm');
        $tmpField[] = DBTableField::setTableField('tableScmManage', $arrIncludeSm, null, 'sm');

        // join 문
        $join[] = ' LEFT JOIN ' . DB_ORDER . ' o ON o.orderNo = og.orderNo ';
        $join[] = ' LEFT JOIN ' . DB_ORDER_INFO . ' oi ON og.orderNo = oi.orderNo AND oi.orderInfoCd = 1 ';
        $join[] = ' LEFT JOIN ' . DB_MEMBER . ' m ON o.memNo = m.memNo ';
        $join[] = ' LEFT JOIN ' . DB_SCM_MANAGE . ' sm ON og.scmNo = sm.scmNo ';
        $join[] = ' LEFT JOIN ' . DB_MANAGER . ' as mg ON mg.scmNo=sm.scmNo and isDelete = "n" and mg.isSuper="y" ';

        // 쿼리용 필드 합침
        $tmpKey = array_keys($tmpField);
        $arrField = [];
        foreach ($tmpKey as $key) {
            $arrField = array_merge($arrField, $tmpField[$key]);
        }
        unset($tmpField, $tmpKey);

        $this->arrWhere[] = 'og.scmAdjustNo < ?';
        $this->db->bind_param_push($this->arrBind, 'i', 1);

        // 현 페이지 결과
        $this->db->strField = 'og.sno, o.regDt, mg.managerId, ' . implode(', ', $arrField) . ', LEFT(og.orderStatus, 1) as statusMode';
        $this->db->strJoin = implode('', $join);
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
        $this->db->strOrder = $orderSort;
        if ($pageFl) $this->db->strLimit = $page->recode['start'] . ',' . $searchData['pageNum'];

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_GOODS . ' og ' . implode(' ', $query);
        $tmp = $this->db->query_fetch($strSQL, $this->arrBind);

        // 검색 레코드 수
        $table = DB_ORDER_GOODS . ' as og';
        $page->recode['total'] = $this->db->query_count($query, $table, $this->arrBind);
        // 공급사 관리자인 경우 - @todo 컴포넌트에서 세션 및 REQUEST 를 직접 받아 처리하는 것이 맞는지 ......
        if (Manager::isProvider()) {
            $addProviderQuery = ' AND og.scmNo = ' . Session::get('manager.scmNo');
        }
        $total = $this->db->fetch('SELECT count(*) as total FROM ' . DB_ORDER_GOODS . ' og WHERE og.scmAdjustNo < 1 ' . $addProviderQuery . ' AND  (og.orderStatus LIKE \'' . $this->arrBind[1] . '\')');
        $page->recode['amount'] = $total['total'];
        $page->setPage();

        if (gd_isset($tmp)) {
            // 결제방법과 처리 상태 설정
            foreach ($tmp as $key => &$val) {
                // 옵션처리
                $options = json_decode(gd_htmlspecialchars_stripslashes($val['optionInfo']));
                $val['optionInfo'] = $options;

                // 텍스트옵션
                $textOptions = json_decode(gd_htmlspecialchars_stripslashes($val['optionTextInfo']));
                $val['optionTextInfo'] = $textOptions;

                // 추가상품
                $val['addGoods'] = $this->order->getOrderAddGoods(
                    $val['orderNo'],
                    $val['orderCd'],
                    [
                        'sno',
                        'commission',
                        'addGoodsNo',
                        'goodsNm',
                        'goodsCnt',
                        'goodsPrice',
                        'optionNm',
                        'goodsImage',
                    ]
                );

                // 추가상품 수량 (테이블 UI 처리에 필요)
                $val['addGoodsCnt'] = empty($val['addGoods']) ? 0 : count($val['addGoods']);

                // 주문 상태명 설정
                $val['beforeStatusStr'] = $this->order->getOrderStatusAdmin($val['beforeStatus']);
                $val['settleKindStr'] = $this->order->printSettleKind($val['settleKind']);
                $val['orderStatusStr'] = $this->order->getOrderStatusAdmin($val['orderStatus']);

                // 본사 타임세일 적용시 (판매가 = 판매가 + 타임세일)
                if ($val['timeSalePrice'] > 0) {
                    $val['goodsPrice'] = $val['goodsPrice'] + $val['timeSalePrice'];
                }

                $goodsPrice = ($val['goodsPrice'] + $val['optionPrice'] + $val['optionTextPrice']) * $val['goodsCnt'];
                $goodsAdjustCommission = NumberUtils::getNumberFigure($goodsPrice * $val['commission'] / 100, $this->truncPolicy['unitPrecision'], $this->truncPolicy['unitRound']);
                $goodsAdjustPrice = $goodsPrice - $goodsAdjustCommission;

                $tmp[$key]['goodsAdjustCommission'] = $goodsAdjustCommission;
                $tmp[$key]['goodsAdjustPrice'] = $goodsAdjustPrice;

                $totalAddGoodsAdjustPrice = 0;
                if ($val['addGoods']) {
                    foreach ($val['addGoods'] as $addKey => $addVal) {
                        $addGoodsPrice = $addVal['goodsPrice'] * $addVal['goodsCnt'];
                        $addGoodsAdjustCommission = NumberUtils::getNumberFigure($addVal['goodsPrice'] * $addVal['goodsCnt'] * $addVal['commission'] / 100, $this->truncPolicy['unitPrecision'], $this->truncPolicy['unitRound']);
                        $addGoodsAdjustPrice = $addGoodsPrice - $addGoodsAdjustCommission;
                        $tmp[$key]['addGoods'][$addKey]['addGoodsAdjustPrice'] = $addGoodsAdjustPrice;
                        $tmp[$key]['addGoods'][$addKey]['addGoodsAdjustCommission'] = $addGoodsAdjustCommission;
                        $totalAddGoodsAdjustPrice += $addGoodsAdjustPrice;
                    }
                }
                $totalAdjustPrice = $goodsAdjustPrice + $totalAddGoodsAdjustPrice;
                $totalSettlePrice = (($val['goodsPrice'] + $val['optionPrice'] + $val['optionTextPrice']) * $val['goodsCnt']) + $val['addGoodsPrice'];

                $tmp[$key]['totalSettlePrice'] = $totalSettlePrice;
                $tmp[$key]['totalAdjustPrice'] = $totalAdjustPrice;
            }

            // 각 데이터 배열화
            $getData['data'] = gd_isset($tmp);
        }

        $getData['search'] = gd_htmlspecialchars($this->search);
        $getData['checked'] = $this->checked;

        return $getData;
    }

    /**
     * AdjustDelivery 배송비 정산 리스트
     *
     * @param string  $searchData   검색 데이타
     * @param integer $searchPeriod 기본 조회 기간
     *
     * @return array 배송비 리스트 정보
     */
    public function getScmAdjustDeliveryList($searchData, $searchPeriod = 6, $pageFl = true)
    {
        // --- 검색 설정
        $this->_setscmAdjustDeliverySearch($searchData, $searchPeriod);

        if ($searchData['orderDeliveryNo'] && is_array($searchData['orderDeliveryNo'])) {
            $this->arrWhere[] = 'od.sno IN (' . implode(',', $searchData['orderDeliveryNo']) . ')';
        }

        // --- 페이지 기본설정
        gd_isset($searchData['page'], 1);
        gd_isset($searchData['pageNum'], 20);

        $page = \App::load('\\Component\\Page\\Page', $searchData['page']);
        $page->page['list'] = $searchData['pageNum']; // 페이지당 리스트 수
        $page->setPage();
        $page->setUrl(\Request::getQueryString());

        // --- 정렬 설정
        $orderSort = gd_isset($searchData['sort'], 'og.regDt desc, og.scmNo asc, og.orderCd asc');

        // 사용 필드
        $arrIncludeOd = [
            'sno',
            'commission',
            'deliverySno',
            'deliveryCharge',
        ];
        $arrIncludeOg = [
            'orderNo',
            'goodsNo',
            'scmNo',
            'orderNo',
            'orderCd',
            'handleSno',
            'orderStatus',
            'orderDeliverySno',
        ];
        $arrIncludeO = [
            'orderNo',
            'mallSno',
            'memNo',
            'settleKind',
            'orderTypeFl',
        ];
        $arrIncludeOi = [
            'orderName',
        ];
        $arrIncludeM = [
            'memId',
        ];
        $arrIncludeSm = [
            'scmNo',
            'companyNm',
        ];

        $tmpField[] = DBTableField::setTableField('tableOrder', $arrIncludeO, null, 'o');
        $tmpField[] = DBTableField::setTableField('tableOrderGoods', $arrIncludeOg, null, 'og');
        $tmpField[] = DBTableField::setTableField('tableOrderInfo', $arrIncludeOi, null, 'oi');
        $tmpField[] = DBTableField::setTableField('tableMember', $arrIncludeM, null, 'm');
        $tmpField[] = DBTableField::setTableField('tableScmManage', $arrIncludeSm, null, 'sm');
        $tmpField[] = DBTableField::setTableField('tableOrderDelivery', $arrIncludeOd, null, 'od');

        // join 문
        $join[] = ' LEFT JOIN ' . DB_ORDER . ' o ON o.orderNo = od.orderNo ';
        $join[] = ' LEFT JOIN ' . DB_ORDER_GOODS . ' og ON og.orderNo = od.orderNo AND og.orderDeliverySno = od.sno ';
        $join[] = ' LEFT JOIN ' . DB_ORDER_INFO . ' oi ON od.orderNo = oi.orderNo AND oi.orderInfoCd = 1 ';
        $join[] = ' LEFT JOIN ' . DB_MEMBER . ' m ON o.memNo = m.memNo ';
        $join[] = ' LEFT JOIN ' . DB_SCM_MANAGE . ' sm ON od.scmNo = sm.scmNo ';
        $join[] = ' LEFT JOIN ' . DB_MANAGER . ' as mg ON mg.scmNo=sm.scmNo and isDelete = "n" and mg.isSuper="y" ';

        // 쿼리용 필드 합침
        $tmpKey = array_keys($tmpField);
        $arrField = [];
        foreach ($tmpKey as $key) {
            $arrField = array_merge($arrField, $tmpField[$key]);
        }
        unset($tmpField, $tmpKey);

        $this->arrWhere[] = 'od.scmAdjustNo < ? AND od.deliveryCharge > ? AND o.mallSno = ' . DEFAULT_MALL_NUMBER;
        $this->db->bind_param_push($this->arrBind, 'i', 1);
        $this->db->bind_param_push($this->arrBind, 'i', 0);

        // 현 페이지 결과
        $this->db->strField = 'od.sno,mg.managerId, ' . implode(', ', $arrField) . ', LEFT(og.orderStatus, 1) as statusMode, o.regDt ';
        $this->db->strJoin = implode('', $join);
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
        $this->db->strGroup = ' od.sno ';
        $this->db->strOrder = $orderSort;
        if ($pageFl) $this->db->strLimit = $page->recode['start'] . ',' . $searchData['pageNum'];

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_DELIVERY . ' od ' . implode(' ', $query);
        $tmp = $this->db->query_fetch($strSQL, $this->arrBind);

        // 검색 레코드 수
        $table = DB_ORDER_DELIVERY . ' as od';
        $page->recode['total'] = $this->db->query_count($query, $table, $this->arrBind);

        // 공급사 관리자인 경우 - @todo 컴포넌트에서 세션 및 REQUEST 를 직접 받아 처리하는 것이 맞는지 ......
        if (Manager::isProvider()) {
            $addProviderQuery = ' AND od.scmNo = ' . Session::get('manager.scmNo');
        }
        $total = $this->db->fetch('select count(rowCount) as total from (SELECT count(od.sno) as rowCount FROM es_orderDelivery as od LEFT JOIN ' . DB_ORDER . ' o ON o.orderNo = od.orderNo LEFT JOIN es_orderGoods as og ON og.orderDeliverySno = od.sno WHERE o.mallSno = ' . DEFAULT_MALL_NUMBER . ' AND od.scmAdjustNo < 1 AND od.deliveryCharge > 0 ' . $addProviderQuery . ' AND (og.orderStatus LIKE \'' . $this->arrBind[1] . '\') group by og.orderDeliverySno) as deliveryCount');
        $page->recode['amount'] = $total['total'];
        $page->setPage();

        if (gd_isset($tmp)) {
            // 결제방법과 처리 상태 설정
            foreach ($tmp as $key => $val) {
                $tmp[$key]['beforeStatusStr'] = $this->order->getOrderStatusAdmin($val['beforeStatus']);
                $tmp[$key]['settleKindStr'] = $this->order->printSettleKind($val['settleKind']);
                $tmp[$key]['orderStatusStr'] = $this->order->getOrderStatusAdmin($val['orderStatus']);

                $deliveryAdjustCommission = NumberUtils::getNumberFigure($val['deliveryCharge'] * $val['commission'] / 100, $this->truncPolicy['unitPrecision'], $this->truncPolicy['unitRound']);
                $deliveryAdjustPrice = $val['deliveryCharge'] - $deliveryAdjustCommission;

                $totalAdjustPrice = $deliveryAdjustPrice + $deliveryAdjustCommission;

                $tmp[$key]['deliveryAdjustCommission'] = $deliveryAdjustCommission;
                $tmp[$key]['deliveryAdjustPrice'] = $deliveryAdjustPrice;
                $tmp[$key]['totalAdjustPrice'] = $totalAdjustPrice;
            }

            // 각 데이터 배열화
            $getData['data'] = gd_isset($tmp);
        }

        $getData['search'] = gd_htmlspecialchars($this->search);
        $getData['checked'] = $this->checked;

        return $getData;
    }

    /**
     * AdjustAfterOrder 정산 후 주문상품 정산 리스트
     *
     * @param string  $searchData   검색 데이타
     * @param integer $searchPeriod 기본 조회 기간 (기본 7일)
     *
     * @return array 주문 리스트 정보
     */
    public function getScmAdjustAfterOrderList($searchData, $searchPeriod = 6, $pageFl = true)
    {
        // --- 검색 설정
        $this->_setScmAdjustOrderSearch($searchData, $searchPeriod);

        if ($searchData['orderGoodsNo'] && is_array($searchData['orderGoodsNo'])) {
            $this->arrWhere[] = 'og.sno IN (' . implode(',', $searchData['orderGoodsNo']) . ')';
        }

        // --- 페이지 기본설정
        gd_isset($searchData['page'], 1);
        gd_isset($searchData['pageNum'], 20);

        $page = \App::load('\\Component\\Page\\Page', $searchData['page']);
        $page->page['list'] = $searchData['pageNum']; // 페이지당 리스트 수
        $page->setPage();
        $page->setUrl(\Request::getQueryString());

        // --- 정렬 설정
        $orderSort = gd_isset($searchData['sort'], 'og.regDt desc, og.scmNo asc, og.orderCd asc');

        // 사용 필드
        $arrIncludeOg = [
            'goodsNo',
            'scmNo',
            'commission',
            'orderNo',
            'orderCd',
            'handleSno',
            'orderStatus',
            'goodsNm',
            'goodsCnt',
            'goodsPrice',
            'optionInfo',
            'optionPrice',
            'optionTextInfo',
            'optionTextPrice',
            'addGoodsCnt',
            'addGoodsPrice',
            'timeSalePrice',
        ];
        $arrIncludeO = [
            'orderNo',
            'orderGoodsCnt',
            'memNo',
            'settlePrice',
            'settleKind',
            'orderTypeFl',
        ];
        $arrIncludeOi = [
            'orderName',
        ];
        $arrIncludeM = [
            'memId',
        ];
        $arrIncludeSm = [
            'scmNo',
            'companyNm',
        ];

        $tmpField[] = DBTableField::setTableField('tableOrder', $arrIncludeO, null, 'o');
        $tmpField[] = DBTableField::setTableField('tableOrderGoods', $arrIncludeOg, null, 'og');
        $tmpField[] = DBTableField::setTableField('tableOrderInfo', $arrIncludeOi, null, 'oi');
        $tmpField[] = DBTableField::setTableField('tableMember', $arrIncludeM, null, 'm');
        $tmpField[] = DBTableField::setTableField('tableScmManage', $arrIncludeSm, null, 'sm');

        // join 문
        $join[] = ' LEFT JOIN ' . DB_ORDER . ' o ON o.orderNo = og.orderNo ';
        $join[] = ' LEFT JOIN ' . DB_ORDER_INFO . ' oi ON og.orderNo = oi.orderNo AND oi.orderInfoCd = 1 ';
        $join[] = ' LEFT JOIN ' . DB_MEMBER . ' m ON o.memNo = m.memNo ';
        $join[] = ' LEFT JOIN ' . DB_SCM_MANAGE . ' sm ON og.scmNo = sm.scmNo ';
        $join[] = ' LEFT JOIN ' . DB_MANAGER . ' as mg ON mg.scmNo=sm.scmNo and isDelete = "n" and mg.isSuper="y" ';

        // 쿼리용 필드 합침
        $tmpKey = array_keys($tmpField);
        $arrField = [];
        foreach ($tmpKey as $key) {
            $arrField = array_merge($arrField, $tmpField[$key]);
        }
        unset($tmpField, $tmpKey);

        $this->arrWhere[] = 'og.scmAdjustNo > ? AND og.scmAdjustAfterNo < ?';
        $this->db->bind_param_push($this->arrBind, 'i', 0);
        $this->db->bind_param_push($this->arrBind, 'i', 1);

        // 현 페이지 결과
        $this->db->strField = 'og.sno, o.regDt, mg.managerId,' . implode(', ', $arrField) . ', LEFT(og.orderStatus, 1) as statusMode';
        $this->db->strJoin = implode('', $join);
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
        $this->db->strOrder = $orderSort;
        if ($pageFl) $this->db->strLimit = $page->recode['start'] . ',' . $searchData['pageNum'];

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_GOODS . ' og ' . implode(' ', $query);
        $tmp = $this->db->query_fetch($strSQL, $this->arrBind);

        // 검색 레코드 수
        $table = DB_ORDER_GOODS . ' as og';
        $page->recode['total'] = $this->db->query_count($query, $table, $this->arrBind);
        // 공급사 관리자인 경우 - @todo 컴포넌트에서 세션 및 REQUEST 를 직접 받아 처리하는 것이 맞는지 ......
        if (Manager::isProvider()) {
            $addProviderQuery = ' AND og.scmNo = ' . Session::get('manager.scmNo');
        }
        $total = $this->db->fetch('SELECT count(*) as total FROM ' . DB_ORDER_GOODS . ' og WHERE og.scmAdjustNo > 0 AND og.scmAdjustAfterNo < 1 ' . $addProviderQuery . ' AND (og.orderStatus LIKE \'' . $this->arrBind[1] . '\')');
        $page->recode['amount'] = $total['total'];
        $page->setPage();

        if (gd_isset($tmp)) {
            // 결제방법과 처리 상태 설정
            foreach ($tmp as $key => &$val) {
                // 옵션처리
                $options = json_decode(gd_htmlspecialchars_stripslashes($val['optionInfo']));
                $val['optionInfo'] = $options;

                // 텍스트옵션
                $textOptions = json_decode(gd_htmlspecialchars_stripslashes($val['optionTextInfo']));
                $val['optionTextInfo'] = $textOptions;

                // 추가상품
                $val['addGoods'] = $this->order->getOrderAddGoods(
                    $val['orderNo'],
                    $val['orderCd'],
                    [
                        'sno',
                        'commission',
                        'addGoodsNo',
                        'goodsNm',
                        'goodsCnt',
                        'goodsPrice',
                        'optionNm',
                        'goodsImage',
                    ]
                );

                // 추가상품 수량 (테이블 UI 처리에 필요)
                $val['addGoodsCnt'] = empty($val['addGoods']) ? 0 : count($val['addGoods']);

                // 주문 상태명 설정
                $val['beforeStatusStr'] = $this->order->getOrderStatusAdmin($val['beforeStatus']);
                $val['settleKindStr'] = $this->order->printSettleKind($val['settleKind']);
                $val['orderStatusStr'] = $this->order->getOrderStatusAdmin($val['orderStatus']);

                // 본사 타임세일 적용시 (판매가 = 판매가 + 타임세일)
                if ($val['timeSalePrice'] > 0) {
                    $val['goodsPrice'] = $val['goodsPrice'] + $val['timeSalePrice'];
                }

                $goodsPrice = ($val['goodsPrice'] + $val['optionPrice'] + $val['optionTextPrice']) * $val['goodsCnt'];
                $goodsAdjustCommission = NumberUtils::getNumberFigure($goodsPrice * $val['commission'] / 100, $this->truncPolicy['unitPrecision'], $this->truncPolicy['unitRound']);
                $goodsAdjustPrice = $goodsPrice - $goodsAdjustCommission;

                $tmp[$key]['goodsAdjustCommission'] = $goodsAdjustCommission;
                $tmp[$key]['goodsAdjustPrice'] = $goodsAdjustPrice;

                $totalAddGoodsAdjustPrice = 0;
                if ($val['addGoods']) {
                    foreach ($val['addGoods'] as $addKey => $addVal) {
                        $addGoodsPrice = $addVal['goodsPrice'] * $addVal['goodsCnt'];
                        $addGoodsAdjustCommission = NumberUtils::getNumberFigure($addVal['goodsPrice'] * $addVal['goodsCnt'] * $addVal['commission'] / 100, $this->truncPolicy['unitPrecision'], $this->truncPolicy['unitRound']);
                        $addGoodsAdjustPrice = $addGoodsPrice - $addGoodsAdjustCommission;
                        $tmp[$key]['addGoods'][$addKey]['addGoodsAdjustPrice'] = $addGoodsAdjustPrice;
                        $tmp[$key]['addGoods'][$addKey]['addGoodsAdjustCommission'] = $addGoodsAdjustCommission;
                        $totalAddGoodsAdjustPrice += $addGoodsAdjustPrice;
                    }
                }
                $totalAdjustPrice = $goodsAdjustPrice + $totalAddGoodsAdjustPrice;
                $totalSettlePrice = (($val['goodsPrice'] + $val['optionPrice'] + $val['optionTextPrice']) * $val['goodsCnt']) + $val['addGoodsPrice'];

                $tmp[$key]['totalSettlePrice'] = $totalSettlePrice;
                $tmp[$key]['totalAdjustPrice'] = $totalAdjustPrice;
            }

            // 각 데이터 배열화
            $getData['data'] = gd_isset($tmp);
        }

        $getData['search'] = gd_htmlspecialchars($this->search);
        $getData['checked'] = $this->checked;

        return $getData;
    }

    /**
     * AdjustAfterDelivery 정산 후 배송비 정산 리스트
     *
     * @param string  $searchData   검색 데이타
     * @param integer $searchPeriod 기본 조회 기간
     *
     * @return array 정산 후 배송비 리스트 정보
     */
    public function getScmAdjustAfterDeliveryList($searchData, $searchPeriod = 6, $pageFl = true)
    {
        // --- 검색 설정
        $this->_setscmAdjustDeliverySearch($searchData, $searchPeriod);

        if ($searchData['orderDeliveryNo'] && is_array($searchData['orderDeliveryNo'])) {
            $this->arrWhere[] = 'od.sno IN (' . implode(',', $searchData['orderDeliveryNo']) . ')';
        }

        // --- 페이지 기본설정
        gd_isset($searchData['page'], 1);
        gd_isset($searchData['pageNum'], 20);


        $page = \App::load('\\Component\\Page\\Page', $searchData['page']);
        $page->page['list'] = $searchData['pageNum']; // 페이지당 리스트 수
        $page->setPage();
        $page->setUrl(\Request::getQueryString());

        // --- 정렬 설정
        $orderSort = gd_isset($searchData['sort'], 'og.regDt desc, og.scmNo asc, og.orderCd asc');

        // 사용 필드
        $arrIncludeOd = [
            'sno',
            'commission',
            'deliverySno',
            'deliveryCharge',
        ];
        $arrIncludeOg = [
            'orderNo',
            'goodsNo',
            'scmNo',
            'orderNo',
            'orderCd',
            'handleSno',
            'orderStatus',
            'orderDeliverySno',
        ];
        $arrIncludeO = [
            'orderNo',
            'mallSno',
            'memNo',
            'settleKind',
            'orderTypeFl',
        ];
        $arrIncludeOi = [
            'orderName',
        ];
        $arrIncludeM = [
            'memId',
        ];
        $arrIncludeSm = [
            'scmNo',
            'companyNm',
        ];

        $tmpField[] = DBTableField::setTableField('tableOrder', $arrIncludeO, null, 'o');
        $tmpField[] = DBTableField::setTableField('tableOrderGoods', $arrIncludeOg, null, 'og');
        $tmpField[] = DBTableField::setTableField('tableOrderInfo', $arrIncludeOi, null, 'oi');
        $tmpField[] = DBTableField::setTableField('tableMember', $arrIncludeM, null, 'm');
        $tmpField[] = DBTableField::setTableField('tableScmManage', $arrIncludeSm, null, 'sm');
        $tmpField[] = DBTableField::setTableField('tableOrderDelivery', $arrIncludeOd, null, 'od');

        // join 문
        $join[] = ' LEFT JOIN ' . DB_ORDER . ' o ON o.orderNo = od.orderNo ';
        $join[] = ' LEFT JOIN ' . DB_ORDER_GOODS . ' og ON og.orderNo = od.orderNo AND og.orderDeliverySno = od.sno ';
        $join[] = ' LEFT JOIN ' . DB_ORDER_INFO . ' oi ON od.orderNo = oi.orderNo AND oi.orderInfoCd = 1 ';
        $join[] = ' LEFT JOIN ' . DB_MEMBER . ' m ON o.memNo = m.memNo ';
        $join[] = ' LEFT JOIN ' . DB_SCM_MANAGE . ' sm ON od.scmNo = sm.scmNo ';
        $join[] = ' LEFT JOIN ' . DB_MANAGER . ' as mg ON mg.scmNo=sm.scmNo and isDelete = "n" and mg.isSuper="y" ';

        // 쿼리용 필드 합침
        $tmpKey = array_keys($tmpField);
        $arrField = [];
        foreach ($tmpKey as $key) {
            $arrField = array_merge($arrField, $tmpField[$key]);
        }
        unset($tmpField, $tmpKey);

        $this->arrWhere[] = 'od.scmAdjustNo > ? AND od.scmAdjustAfterNo < ? AND od.deliveryCharge > ? AND o.mallSno = ' . DEFAULT_MALL_NUMBER;
        $this->db->bind_param_push($this->arrBind, 'i', 0);
        $this->db->bind_param_push($this->arrBind, 'i', 1);
        $this->db->bind_param_push($this->arrBind, 'i', 0);

        // 현 페이지 결과
        $this->db->strField = 'od.sno,mg.managerId, ' . implode(', ', $arrField) . ', LEFT(og.orderStatus, 1) as statusMode, o.regDt ';
        $this->db->strJoin = implode('', $join);
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
        $this->db->strGroup = ' od.sno ';
        $this->db->strOrder = $orderSort;
        if ($pageFl) $this->db->strLimit = $page->recode['start'] . ',' . $searchData['pageNum'];

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_DELIVERY . ' od ' . implode(' ', $query);
        $tmp = $this->db->query_fetch($strSQL, $this->arrBind);

        // 검색 레코드 수
        $table = DB_ORDER_DELIVERY . ' as od';
        $page->recode['total'] = $this->db->query_count($query, $table, $this->arrBind);
        // 공급사 관리자인 경우 - @todo 컴포넌트에서 세션 및 REQUEST 를 직접 받아 처리하는 것이 맞는지 ......
        if (Manager::isProvider()) {
            $addProviderQuery = ' AND od.scmNo = ' . Session::get('manager.scmNo');
        }
        $total = $this->db->fetch('select count(rowCount) as total from (SELECT count(od.sno) as rowCount FROM es_orderDelivery as od LEFT JOIN ' . DB_ORDER . ' as o ON o.orderNo = od.orderNo LEFT JOIN es_orderGoods as og ON og.orderDeliverySno = od.sno WHERE o.mallSno = ' . DEFAULT_MALL_NUMBER . ' AND od.scmAdjustNo > 0 AND od.scmAdjustAfterNo < 1 AND od.deliveryCharge > 0 ' . $addProviderQuery . ' AND (og.orderStatus LIKE \'' . $this->arrBind[1] . '\') group by og.orderDeliverySno) as deliveryCount');
        $page->recode['amount'] = $total['total'];
        $page->setPage();

        if (gd_isset($tmp)) {
            // 결제방법과 처리 상태 설정
            foreach ($tmp as $key => $val) {
                $tmp[$key]['beforeStatusStr'] = $this->order->getOrderStatusAdmin($val['beforeStatus']);
                $tmp[$key]['settleKindStr'] = $this->order->printSettleKind($val['settleKind']);
                $tmp[$key]['orderStatusStr'] = $this->order->getOrderStatusAdmin($val['orderStatus']);

                $deliveryAdjustCommission = NumberUtils::getNumberFigure($val['deliveryCharge'] * $val['commission'] / 100, $this->truncPolicy['unitPrecision'], $this->truncPolicy['unitRound']);
                $deliveryAdjustPrice = $val['deliveryCharge'] - $deliveryAdjustCommission;

                $totalAdjustPrice = $deliveryAdjustPrice + $deliveryAdjustCommission;

                $tmp[$key]['deliveryAdjustCommission'] = $deliveryAdjustCommission;
                $tmp[$key]['deliveryAdjustPrice'] = $deliveryAdjustPrice;
                $tmp[$key]['totalAdjustPrice'] = $totalAdjustPrice;
            }

            // 각 데이터 배열화
            $getData['data'] = gd_isset($tmp);
        }

        $getData['search'] = gd_htmlspecialchars($this->search);
        $getData['checked'] = $this->checked;

        return $getData;
    }

    /**
     * 배열 공급사 정산 데이터를 한글로 변경한 별도의 Array 생성
     *
     * @param array $scmAdjustArrData 공급사 정산의 리스트
     *
     * @return array $convertScmAdjustArrData 공급사 정산의 리스트의 표시용 한글 변경
     *
     * @author su
     */
    public function convertScmAdjustArrData($scmAdjustArrData)
    {
        $convertScmAdjustArrData = [];
        $scm = \App::load('\\Component\\Scm\\Scm');
        foreach ($scmAdjustArrData as $key => $val) {
            if ($val['scmNo'] > 0) {
                $scmNm = $scm->getScmInfo($val['scmNo'], 'sm.scmType, sm.companyNm, sm.scmCode, sm.ceoNm, sm.businessNo');
                $convertScmAdjustArrData[$key]['scm']['no'] = $val['scmNo'];
                $convertScmAdjustArrData[$key]['scm']['type'] = $scmNm['scmType'];
                $convertScmAdjustArrData[$key]['scm']['name'] = $scmNm['companyNm'];
                $convertScmAdjustArrData[$key]['scm']['code'] = $scmNm['scmCode'];
                $convertScmAdjustArrData[$key]['scm']['ceo'] = $scmNm['ceoNm'];
                $convertScmAdjustArrData[$key]['scm']['business'] = $scmNm['businessNo'];
            }
            if ($val['scmAdjustType']) {
                foreach ($this->scmAdjustType as $typeKey => $typeVal) {
                    if ($typeKey == $val['scmAdjustType']) {
                        $convertScmAdjustArrData[$key]['scmAdjustType'] = $typeVal;
                        break;
                    }
                }
            }
            if ($val['scmAdjustKind']) {
                foreach ($this->scmAdjustKind as $kindKey => $kindVal) {
                    if ($kindKey == $val['scmAdjustKind']) {
                        $convertScmAdjustArrData[$key]['scmAdjustKind'] = $kindVal;
                        break;
                    }
                }
            }
            if ($val['scmAdjustState']) {
                foreach ($this->scmAdjustState as $stateKey => $stateVal) {
                    if ($stateKey == $val['scmAdjustState']) {
                        $convertScmAdjustArrData[$key]['scmAdjustState'] = $stateVal;
                        break;
                    }
                }
            }
        }

        return $convertScmAdjustArrData;
    }

    /**
     * 배열 공급사 정산 로그 데이터를 한글로 변경한 별도의 Array 생성
     *
     * @param array $scmAdjustLogArrData 공급사 정산로그 리스트
     *
     * @return array $convertScmAdjustLogArrData 공급사 정산로그의 리스트의 표시용 한글 변경
     *
     * @author su
     */
    public function convertScmAdjustLogArrData($scmAdjustLogArrData)
    {
        $convertScmAdjustLogArrData = [];
        foreach ($scmAdjustLogArrData as $key => $val) {
            if ($val['scmAdjustState']) {
                foreach ($this->scmAdjustState as $stateKey => $stateVal) {
                    if ($stateKey == $val['scmAdjustState']) {
                        $convertScmAdjustLogArrData[$key]['scmAdjustState'] = $stateVal;
                        break;
                    }
                }
            }
        }

        return $convertScmAdjustLogArrData;
    }

    /**
     * 관리자 공급사 주문상품 정산 요청 처리
     */
    public function setScmAdjustOrder($orderGoodsNoArr)
    {
        $truncPolicy = Globals::get('gTrunc.scm_calculate');
        $failCount = 0;

        if (!$orderGoodsNoArr) {
            throw new \Exception(__('정산 요청 할 주문 상품을 선택해 주세요.'));
        }
        $scmAdjustData = $this->getScmSortOrderGoods($orderGoodsNoArr);
        foreach ($scmAdjustData as $scmKey => $scmVal) {
            $arrData['scmNo'] = $scmKey;
            $arrData['scmAdjustKind'] = 'a';
            $arrData['scmAdjustType'] = 'o';
            $arrData['scmAdjustTotalPrice'] = 0;
            $arrData['scmAdjustCommissionPrice'] = 0;
            $arrData['scmAdjustPrice'] = 0;
            $arrData['scmAdjustCommissionTaxPrice'] = 0;
            $arrData['scmAdjustCommissionVatPrice'] = 0;
            $arrData['scmAdjustState'] = 1;
            foreach ($scmVal as $goodsKey => $goodsVal) {
                // 중복 요청 방지
                if ($goodsVal['scmAdjustNo'] > 0) {
                    $failCount++;
                    continue;
                }

                // 본사 타임세일 적용시 (판매가 = 판매가 + 타임세일)
                if ($goodsVal['timeSalePrice'] > 0) {
                    $goodsVal['goodsPrice'] = $goodsVal['goodsPrice'] + $goodsVal['timeSalePrice'];
                }

                $goodsNo['goods'][] = $goodsVal['sno'];
                $goodsPrice = $goodsVal['goodsCnt'] * ($goodsVal['goodsPrice'] + $goodsVal['optionPrice'] + $goodsVal['optionTextPrice']); // 상품 주문 금액
                $arrData['scmAdjustTotalPrice'] += $goodsPrice;
                $arrData['scmAdjustCommissionPrice'] += NumberUtils::getNumberFigure($goodsPrice * $goodsVal['commission'] * 1 / 100, $truncPolicy['unitPrecision'], $truncPolicy['unitRound']);
                if ($goodsVal['addGoods']) {
                    foreach ($goodsVal['addGoods'] as $addGoodsKey => $addGoodsVal) {
                        $goodsNo['addGoods'][] = $addGoodsVal['sno'];
                        $addGoodsPrice = $addGoodsVal['goodsPrice'] * $addGoodsVal['goodsCnt'];
                        $arrData['scmAdjustTotalPrice'] += $addGoodsPrice;
                        $arrData['scmAdjustCommissionPrice'] += NumberUtils::getNumberFigure($addGoodsPrice * $addGoodsVal['commission'] * 1 / 100, $truncPolicy['unitPrecision'], $truncPolicy['unitRound']);
                    }
                }
            }

            if (gd_isset($goodsNo['goods']) == false) {
                throw new \Exception(__('선택하신 주문은 이미 정산 요청 되었습니다. 요청상태를 확인해 주세요.'), 500);
            }

            $arrData['orderGoodsNo'] = json_encode($goodsNo);
            $arrData['scmAdjustPrice'] += $arrData['scmAdjustTotalPrice'] - $arrData['scmAdjustCommissionPrice'];
            $arrData['scmAdjustCommissionTaxPrice'] = NumberUtils::getNumberFigure($arrData['scmAdjustCommissionPrice'] / 1.1, $truncPolicy['unitPrecision'], $truncPolicy['unitRound']);
            $arrData['scmAdjustCommissionVatPrice'] = $arrData['scmAdjustCommissionPrice'] - $arrData['scmAdjustCommissionTaxPrice'];
            $arrData['scmAdjustCode'] = $scmKey . '-' . time();
            $arrData['scmAdjustDt'] = date('Y-m-d H:i:s');

            try {
                $this->db->begin_tran();

                //정산 요청 등록
                $arrBind = $this->db->get_binding(DBTableField::tableScmAdjust(), $arrData, 'insert', array_keys($arrData), ['scmAdjustNo']);
                $this->db->set_insert_db(DB_SCM_ADJUST, $arrBind['param'], $arrBind['bind'], 'y');
                $scmAdjustNo = $this->db->insert_id();
                unset($arrData);
                unset($arrBind);

                // 주문상품의 정산 요청 고유번호 저장
                $this->setOrderGoodsScmAdjustNo($goodsNo['goods'], $scmAdjustNo);
                // 추가주문상품의 정산 요청 고유번호 저장
                $this->setOrderAddGoodsScmAdjustNo($goodsNo['addGoods'], $scmAdjustNo);
                unset($goodsNo);

                // 정산 요청 로그 등록
                $this->setScmAdjustLog($scmAdjustNo, 1);
                unset($scmAdjustNo);

                $this->db->commit();

                return $failCount;
            } catch (Exception $e) {
                $this->db->rollback();
                throw new \Exception(__('정산 요청 중 오류가 발생하였습니다. 다시 시도해 주세요.'));
            }
        }
    }

    /**
     * 관리자 공급사 배송비 정산 요청 처리
     */
    public function setScmAdjustDelivery($orderDeliveryNoArr)
    {
        $truncPolicy = Globals::get('gTrunc.scm_calculate');
        $failCount = 0;

        if (!$orderDeliveryNoArr) {
            throw new \Exception(__('정산 요청 할 주문 상품을 선택해 주세요.'));
        }
        $scmAdjustData = $this->getScmSortOrderDelivery($orderDeliveryNoArr);
        foreach ($scmAdjustData as $scmKey => $scmVal) {
            $arrData['scmNo'] = $scmKey;
            $arrData['scmAdjustKind'] = 'a';
            $arrData['scmAdjustType'] = 'd';
            $arrData['scmAdjustTotalPrice'] = 0;
            $arrData['scmAdjustCommissionPrice'] = 0;
            $arrData['scmAdjustPrice'] = 0;
            $arrData['scmAdjustCommissionTaxPrice'] = 0;
            $arrData['scmAdjustCommissionVatPrice'] = 0;
            $arrData['scmAdjustState'] = 1;
            foreach ($scmVal as $deliveryKey => $deliveryVal) {
                // 중복 요청 방지
                if ($deliveryVal['scmAdjustNo'] > 0) {
                    $failCount++;
                    continue;
                }

                $deliveryNo['delivery'][] = $deliveryVal['sno'];
                $arrData['scmAdjustTotalPrice'] += $deliveryVal['deliveryCharge'];
                $arrData['scmAdjustCommissionPrice'] += NumberUtils::getNumberFigure($deliveryVal['deliveryCharge'] * $deliveryVal['commission'] * 1 / 100, $truncPolicy['unitPrecision'], $truncPolicy['unitRound']);
            }

            if (gd_isset($deliveryNo['delivery']) == false) {
                throw new \Exception(__('선택하신 주문은 이미 정산 요청 되었습니다. 요청상태를 확인해 주세요.'), 500);
            }

            $arrData['orderDeliveryNo'] = json_encode($deliveryNo);
            $arrData['scmAdjustPrice'] += $arrData['scmAdjustTotalPrice'] - $arrData['scmAdjustCommissionPrice'];
            $arrData['scmAdjustCommissionTaxPrice'] = NumberUtils::getNumberFigure($arrData['scmAdjustCommissionPrice'] / 1.1, $truncPolicy['unitPrecision'], $truncPolicy['unitRound']);
            $arrData['scmAdjustCommissionVatPrice'] = $arrData['scmAdjustCommissionPrice'] - $arrData['scmAdjustCommissionTaxPrice'];
            $arrData['scmAdjustCode'] = $scmKey . '-' . time();
            $arrData['scmAdjustDt'] = date('Y-m-d H:i:s');

            try {
                $this->db->begin_tran();

                // 정산 요청 등록
                $arrBind = $this->db->get_binding(DBTableField::tableScmAdjust(), $arrData, 'insert', array_keys($arrData), ['scmAdjustNo']);
                $this->db->set_insert_db(DB_SCM_ADJUST, $arrBind['param'], $arrBind['bind'], 'y');
                $scmAdjustNo = $this->db->insert_id();
                unset($arrData);
                unset($arrBind);

                // 정산 요청 배송비 처리
                $this->setOrderDeliveryScmAdjustNo($deliveryNo['delivery'], $scmAdjustNo);
                unset($deliveryNo);

                // 정산 요청 로그 등록
                $this->setScmAdjustLog($scmAdjustNo, 1);
                unset($scmAdjustNo);

                $this->db->commit();

                return $failCount;
            } catch (Exception $e) {
                $this->db->rollback();
                throw new \Exception(__('정산 요청 중 오류가 발생하였습니다. 다시 시도해 주세요.'));
            }
        }
    }

    /**
     * AfterOrder 정산 후 주문상품 정산 요청 처리
     */
    public function setScmAdjustAfterOrder($orderGoodsNoArr)
    {
        $truncPolicy = Globals::get('gTrunc.scm_calculate');
        $failCount = 0;

        if (!$orderGoodsNoArr) {
            throw new \Exception(__('정산 요청 할 주문 상품을 선택해 주세요.'));
        }
        $scmAdjustData = $this->getScmSortOrderGoods($orderGoodsNoArr);
        foreach ($scmAdjustData as $scmKey => $scmVal) {
            $arrData['scmNo'] = $scmKey;
            $arrData['scmAdjustKind'] = 'a';
            $arrData['scmAdjustType'] = 'oa';
            $arrData['scmAdjustTotalPrice'] = 0;
            $arrData['scmAdjustCommissionPrice'] = 0;
            $arrData['scmAdjustPrice'] = 0;
            $arrData['scmAdjustCommissionTaxPrice'] = 0;
            $arrData['scmAdjustCommissionVatPrice'] = 0;
            $arrData['scmAdjustState'] = 1;
            foreach ($scmVal as $goodsKey => $goodsVal) {
                // 중복 요청 방지
                if ($goodsVal['scmAdjustAfterNo'] > 0 ) {
                    $failCount++;
                    continue;
                }

                $goodsNo['goods'][] = $goodsVal['sno'];
                $goodsPrice = $goodsVal['goodsCnt'] * ($goodsVal['goodsPrice'] + $goodsVal['optionPrice'] + $goodsVal['optionTextPrice']); // 상품 주문 금액
                $arrData['scmAdjustTotalPrice'] += $goodsPrice;
                $arrData['scmAdjustCommissionPrice'] += NumberUtils::getNumberFigure($goodsPrice * $goodsVal['commission'] * 1 / 100, $truncPolicy['unitPrecision'], $truncPolicy['unitRound']);
                if ($goodsVal['addGoods']) {
                    foreach ($goodsVal['addGoods'] as $addGoodsKey => $addGoodsVal) {
                        $goodsNo['addGoods'][] = $addGoodsVal['sno'];
                        $addGoodsPrice = $addGoodsVal['goodsPrice'] * $addGoodsVal['goodsCnt'];
                        $arrData['scmAdjustTotalPrice'] += $addGoodsPrice;
                        $arrData['scmAdjustCommissionPrice'] += NumberUtils::getNumberFigure($addGoodsPrice * $addGoodsVal['commission'] * 1 / 100, $truncPolicy['unitPrecision'], $truncPolicy['unitRound']);
                    }
                }
            }

            if (gd_isset($goodsNo['goods']) == false) {
                throw new \Exception(__('선택하신 주문은 이미 정산 요청 되었습니다. 요청상태를 확인해 주세요.'), 500);
            }

            $arrData['orderGoodsNo'] = json_encode($goodsNo);
            $arrData['scmAdjustPrice'] += $arrData['scmAdjustTotalPrice'] - $arrData['scmAdjustCommissionPrice'];
            $arrData['scmAdjustCommissionTaxPrice'] = NumberUtils::getNumberFigure($arrData['scmAdjustCommissionPrice'] / 1.1, $truncPolicy['unitPrecision'], $truncPolicy['unitRound']);
            $arrData['scmAdjustCommissionVatPrice'] = $arrData['scmAdjustCommissionPrice'] - $arrData['scmAdjustCommissionTaxPrice'];
            $arrData['scmAdjustCode'] = $scmKey . '-' . time();
            $arrData['scmAdjustDt'] = date('Y-m-d H:i:s');

            // 정산 후 환불은 마이너스 처리
            $arrData['scmAdjustTotalPrice'] = '-' . $arrData['scmAdjustTotalPrice'];
            $arrData['scmAdjustCommissionPrice'] = '-' . $arrData['scmAdjustCommissionPrice'];
            $arrData['scmAdjustPrice'] = '-' . $arrData['scmAdjustPrice'];
            $arrData['scmAdjustCommissionTaxPrice'] = '-' . $arrData['scmAdjustCommissionTaxPrice'];
            $arrData['scmAdjustCommissionVatPrice'] = '-' . $arrData['scmAdjustCommissionVatPrice'];
            try {
                $this->db->begin_tran();

                //정산 요청 등록
                $arrBind = $this->db->get_binding(DBTableField::tableScmAdjust(), $arrData, 'insert', array_keys($arrData), ['scmAdjustNo']);
                $this->db->set_insert_db(DB_SCM_ADJUST, $arrBind['param'], $arrBind['bind'], 'y');
                $scmAdjustNo = $this->db->insert_id();
                unset($arrData);
                unset($arrBind);

                // 주문상품의 정산 요청 고유번호 저장
                $this->setOrderGoodsScmAdjustAfterNo($goodsNo['goods'], $scmAdjustNo);
                // 추가주문상품의 정산 요청 고유번호 저장
                $this->setOrderAddGoodsScmAdjustAfterNo($goodsNo['addGoods'], $scmAdjustNo);
                unset($goodsNo);

                // 정산 요청 로그 등록
                $this->setScmAdjustLog($scmAdjustNo, 1);
                unset($scmAdjustNo);

                $this->db->commit();

                return $failCount;
            } catch (Exception $e) {
                $this->db->rollback();
                throw new \Exception(__('정산 요청 중 오류가 발생하였습니다. 다시 시도해 주세요.'));
            }
        }
    }

    /**
     * AfterDelivery 정산 후 배송비 정산 요청 처리
     */
    public function setScmAdjustAfterDelivery($orderDeliveryNoArr)
    {
        $truncPolicy = Globals::get('gTrunc.scm_calculate');
        $failCount = 0;

        if (!$orderDeliveryNoArr) {
            throw new \Exception(__('정산 요청 할 주문 상품을 선택해 주세요.'));
        }
        $scmAdjustData = $this->getScmSortOrderDelivery($orderDeliveryNoArr);
        foreach ($scmAdjustData as $scmKey => $scmVal) {
            $arrData['scmNo'] = $scmKey;
            $arrData['scmAdjustKind'] = 'a';
            $arrData['scmAdjustType'] = 'da';
            $arrData['scmAdjustTotalPrice'] = 0;
            $arrData['scmAdjustCommissionPrice'] = 0;
            $arrData['scmAdjustPrice'] = 0;
            $arrData['scmAdjustCommissionTaxPrice'] = 0;
            $arrData['scmAdjustCommissionVatPrice'] = 0;
            $arrData['scmAdjustState'] = 1;
            foreach ($scmVal as $deliveryKey => $deliveryVal) {
                // 중복 요청 방지
                if ($deliveryVal['scmAdjustAfterNo'] > 0) {
                    $failCount++;
                    continue;
                }

                $deliveryNo['delivery'][] = $deliveryVal['sno'];
                $arrData['scmAdjustTotalPrice'] += $deliveryVal['deliveryCharge'];
                $arrData['scmAdjustCommissionPrice'] += NumberUtils::getNumberFigure($deliveryVal['deliveryCharge'] * $deliveryVal['commission'] * 1 / 100, $truncPolicy['unitPrecision'], $truncPolicy['unitRound']);
            }

            if (gd_isset($deliveryNo['delivery']) == false) {
                throw new \Exception(__('선택하신 주문은 이미 정산 요청 되었습니다. 요청상태를 확인해 주세요.'), 500);
            }

            $arrData['orderDeliveryNo'] = json_encode($deliveryNo);
            $arrData['scmAdjustPrice'] += $arrData['scmAdjustTotalPrice'] - $arrData['scmAdjustCommissionPrice'];
            $arrData['scmAdjustCommissionTaxPrice'] = NumberUtils::getNumberFigure($arrData['scmAdjustCommissionPrice'] / 1.1, $truncPolicy['unitPrecision'], $truncPolicy['unitRound']);
            $arrData['scmAdjustCommissionVatPrice'] = $arrData['scmAdjustCommissionPrice'] - $arrData['scmAdjustCommissionTaxPrice'];
            $arrData['scmAdjustCode'] = $scmKey . '-' . time();
            $arrData['scmAdjustDt'] = date('Y-m-d H:i:s');

            // 정산 후 환불은 마이너스 처리
            $arrData['scmAdjustTotalPrice'] = '-' . $arrData['scmAdjustTotalPrice'];
            $arrData['scmAdjustCommissionPrice'] = '-' . $arrData['scmAdjustCommissionPrice'];
            $arrData['scmAdjustPrice'] = '-' . $arrData['scmAdjustPrice'];
            $arrData['scmAdjustCommissionTaxPrice'] = '-' . $arrData['scmAdjustCommissionTaxPrice'];
            $arrData['scmAdjustCommissionVatPrice'] = '-' . $arrData['scmAdjustCommissionVatPrice'];
            try {
                $this->db->begin_tran();

                // 정산 요청 등록
                $arrBind = $this->db->get_binding(DBTableField::tableScmAdjust(), $arrData, 'insert', array_keys($arrData), ['scmAdjustNo']);
                $this->db->set_insert_db(DB_SCM_ADJUST, $arrBind['param'], $arrBind['bind'], 'y');
                $scmAdjustNo = $this->db->insert_id();
                unset($arrData);
                unset($arrBind);

                // 정산 요청 배송비 처리
                $this->setOrderDeliveryScmAdjustAfterNo($deliveryNo['delivery'], $scmAdjustNo);
                unset($deliveryNo);

                // 정산 요청 로그 등록
                $this->setScmAdjustLog($scmAdjustNo, 1);
                unset($scmAdjustNo);

                $this->db->commit();

                return $failCount;
            } catch (Exception $e) {
                $this->db->rollback();
                throw new \Exception(__('정산 요청 중 오류가 발생하였습니다. 다시 시도해 주세요.'));
            }
        }
    }

    /**
     * 관리자 공급사 정산 상태 변경
     *
     * @param array   $scmAdjustArrNo 정산고유번호
     * @param integer $scmAdjustState 변경할 정산상태번호
     * @param string  $scmAdjustMemo  반려시 사유
     *
     * @throws \Exception
     */
    public function setScmAdjustState($scmAdjustArrNo, $scmAdjustState, $scmAdjustMemo = null)
    {
        if (!$scmAdjustArrNo) {
            throw new \Exception(__('변경할 정산건을 선택해 주세요.'));
        }

        try {
            $this->db->begin_tran();
            foreach ($scmAdjustArrNo as $scmAdjustKey => $scmAdjustVal) {
                $scmAdjustData = $this->getScmAdjustInfo($scmAdjustVal, 'scmAdjustType, scmAdjustState, orderGoodsNo, orderDeliveryNo');
                $checkState = $this->getScmAdjustStateCheck($scmAdjustState, $scmAdjustData['scmAdjustState']);
                if (!$checkState) {
                    throw new \Exception(__('변경될 수 없는 정산 상태가 있습니다. 확인해주세요.'));
                } else {
                    // 공급사 정산 상태 변경 처리
                    $arrData['scmAdjustState'] = $scmAdjustState;
                    $arrData['scmAdjustDt'] = date('Y-m-d H:i:s');
                    $arrBind = $this->db->get_binding(DBTableField::tableScmAdjust(), $arrData, 'update', array_keys($arrData), ['scmAdjustNo']);
                    $this->db->set_update_db(DB_SCM_ADJUST, $arrBind['param'], 'scmAdjustNo = ' . $scmAdjustVal, $arrBind['bind'], false);
                    unset($arrData);
                    unset($arrBind);

                    // 공급사 정산 로그 등록
                    $this->setScmAdjustLog($scmAdjustVal, $scmAdjustState, $scmAdjustMemo);

                    // 반려 시 주문상품/추가주문상품/배송비에 있는 정산 고유번호 초기화
                    if ($scmAdjustState < 0) {
                        if ($scmAdjustData['scmAdjustType'] == 'o') { // 주문 상품 정산
                            $goodsNoArr = ArrayUtils::objectToArray(json_decode($scmAdjustData['orderGoodsNo']));
                            // 주문상품의 정산 요청 고유번호 저장
                            $this->setOrderGoodsScmAdjustNo($goodsNoArr['goods'], 0);
                            // 추가주문상품의 정산 요청 고유번호 저장
                            $this->setOrderAddGoodsScmAdjustNo($goodsNoArr['addGoods'], 0);
                            unset($goodsNoArr);
                        } else if ($scmAdjustData['scmAdjustType'] == 'd') { // 배송비 정산
                            $deliveryNoArr = ArrayUtils::objectToArray(json_decode($scmAdjustData['orderDeliveryNo']));
                            // 정산 요청 배송비 처리
                            $this->setOrderDeliveryScmAdjustNo($deliveryNoArr['delivery'], 0);
                            unset($deliveryNoArr);
                        } else if ($scmAdjustData['scmAdjustType'] == 'oa') { // 주문 상품 정산
                            $goodsNoArr = ArrayUtils::objectToArray(json_decode($scmAdjustData['orderGoodsNo']));
                            // 주문상품의 정산 요청 고유번호 저장
                            $this->setOrderGoodsScmAdjustAfterNo($goodsNoArr['goods'], 0);
                            // 추가주문상품의 정산 요청 고유번호 저장
                            $this->setOrderAddGoodsScmAdjustAfterNo($goodsNoArr['addGoods'], 0);
                            unset($goodsNoArr);
                        } else if ($scmAdjustData['scmAdjustType'] == 'da') { // 배송비 정산
                            $deliveryNoArr = ArrayUtils::objectToArray(json_decode($scmAdjustData['orderDeliveryNo']));
                            // 정산 요청 배송비 처리
                            $this->setOrderDeliveryScmAdjustAfterNo($deliveryNoArr['delivery'], 0);
                            unset($deliveryNoArr);
                        }
                    }
                }
                unset($scmAdjustData);
                unset($checkState);
            }
            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollback();
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * 관리자 공급사 수기 정산 등록
     *
     * @param array $scmAdjustManual 수기정산 데이터
     *
     * @throws \Exception
     */
    public function setScmAdjustManual($scmAdjustManual)
    {
        $truncPolicy = Globals::get('gTrunc.scm_calculate');

        // 공급사로 로그인한 경우 기존 scm에 값 설정
        if (Manager::isProvider()) {
            $scmAdjustManual['scmNo'][] = Session::get('manager.scmNo');
        }
        if (!$scmAdjustManual['scmNo']) {
            throw new \Exception(__('등록할 공급사를 선택해 주세요.'));
        }
        if (!$scmAdjustManual['scmAdjustType']) {
            throw new \Exception(__('등록할 정산타입을 선택해 주세요.'));
        }
        if (!$scmAdjustManual['scmAdjustPrice']) {
            throw new \Exception(__('등록할 정산금액 입력해 주세요.'));
        }

        $arrData['scmAdjustKind'] = 'm';
        $arrData['scmAdjustType'] = $scmAdjustManual['scmAdjustType'];
        if ($scmAdjustManual['scmAdjustTotalPriceType'] == 'p') {
            $arrData['scmAdjustTotalPrice'] = $scmAdjustManual['scmAdjustTotalPrice'];
        } else {
            $arrData['scmAdjustTotalPrice'] = '-' . $scmAdjustManual['scmAdjustTotalPrice'];
        }
        $arrData['scmAdjustCommission'] = $scmAdjustManual['scmAdjustCommission'];
        $arrData['scmAdjustCommissionPrice'] = NumberUtils::getNumberFigure($arrData['scmAdjustTotalPrice'] * $scmAdjustManual['scmAdjustCommission'] * 1 / 100, $truncPolicy['unitPrecision'], $truncPolicy['unitRound']);
        $arrData['scmAdjustPrice'] = $arrData['scmAdjustTotalPrice'] - $arrData['scmAdjustCommissionPrice'];
        if ($arrData['scmAdjustCommissionPrice'] > 0) {
            $arrData['scmAdjustCommissionTaxPrice'] = NumberUtils::getNumberFigure($arrData['scmAdjustCommissionPrice'] / 1.1, $truncPolicy['unitPrecision'], $truncPolicy['unitRound']);
            $arrData['scmAdjustCommissionVatPrice'] = $arrData['scmAdjustCommissionPrice'] - $arrData['scmAdjustCommissionTaxPrice'];
        }
        $arrData['scmAdjustState'] = 1;
        $arrData['scmAdjustDt'] = date('Y-m-d H:i:s');

        try {
            $this->db->begin_tran();
            foreach ($scmAdjustManual['scmNo'] as $scmVal) {
                unset($arrData['scmNo']);
                unset($arrData['scmAdjustCode']);
                $arrData['scmNo'] = $scmVal;
                $arrData['scmAdjustCode'] = $scmVal . '-' . time();

                // 정산 요청 등록
                $arrBind = $this->db->get_binding(DBTableField::tableScmAdjust(), $arrData, 'insert', array_keys($arrData), ['scmAdjustNo']);
                $this->db->set_insert_db(DB_SCM_ADJUST, $arrBind['param'], $arrBind['bind'], 'y');
                $scmAdjustNo = $this->db->insert_id();

                // 정산 요청 로그 등록
                $this->setScmAdjustLog($scmAdjustNo, 1, $scmAdjustManual['scmAdjustMemo']);
                unset($scmAdjustNo);
            }
            unset($arrData);
            unset($arrBind);

            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollback();
            throw new \Exception(__('정산 요청 중 오류가 발생하였습니다. 다시 시도해 주세요.'));
        }
    }

    /**
     * Goods 주문상품 정산 요청의 주문상품 테이블에 공급사 정산 요청 정보 저장
     *
     * @param array   $orderGoodsNoArr 정산 처리(요청,반려)한 주문상품의 고유번호
     * @param integer $scmAdjustNo     정산 처리 고유번호
     *
     */
    protected function setOrderGoodsScmAdjustNo($orderGoodsNoArr, $scmAdjustNo)
    {
        if (is_array($orderGoodsNoArr)) {
            $arrDataGoods['scmAdjustNo'] = $scmAdjustNo;
            $arrBindGoods = $this->db->get_binding(DBTableField::tableOrderGoods(), $arrDataGoods, 'update', array_keys($arrDataGoods), ['sno']);
            $this->db->set_update_db(DB_ORDER_GOODS, $arrBindGoods['param'], 'sno IN (\'' . implode('\',\'', $orderGoodsNoArr) . '\')', $arrBindGoods['bind'], false);
            unset($arrDataGoods);
            unset($arrBindGoods);
        }
    }

    /**
     * AddGoods 추가주문상품 정산 요청의 추가주문상품 테이블에 공급사 정산 요청 정보 저장
     *
     * @param array   $orderAddGoodsNoArr 정산 처리(요청,반려)한 주문추가상품의 고유번호
     * @param integer $scmAdjustNo        정산 처리 고유번호
     *
     */
    protected function setOrderAddGoodsScmAdjustNo($orderAddGoodsNoArr, $scmAdjustNo)
    {
        if (is_array($orderAddGoodsNoArr)) {
            $arrDataAddGoods['scmAdjustNo'] = $scmAdjustNo;
            $arrBindAddGoods = $this->db->get_binding(DBTableField::tableOrderAddGoods(), $arrDataAddGoods, 'update', array_keys($arrDataAddGoods), ['sno']);
            $this->db->set_update_db(DB_ORDER_ADD_GOODS, $arrBindAddGoods['param'], 'sno IN (\'' . implode('\',\'', $orderAddGoodsNoArr) . '\')', $arrBindAddGoods['bind'], false);
            unset($arrDataAddGoods);
            unset($arrBindAddGoods);
        }
    }

    /**
     * Delivery 배송비 정산 요청의 주문배송비 테이블에 공급사 정산 요청 정보 저장
     *
     * @param array   $orderDeliveryNoArr 정산 처리(요청,반려)한 주문배송비의 고유번호
     * @param integer $scmAdjustNo        정산 처리 고유번호
     *
     */
    protected function setOrderDeliveryScmAdjustNo($orderDeliveryNoArr, $scmAdjustNo)
    {
        if (is_array($orderDeliveryNoArr)) {
            $arrDataDelivery['scmAdjustNo'] = $scmAdjustNo;
            $arrBindDelivery = $this->db->get_binding(DBTableField::tableOrderDelivery(), $arrDataDelivery, 'update', array_keys($arrDataDelivery), ['sno']);
            $this->db->set_update_db(DB_ORDER_DELIVERY, $arrBindDelivery['param'], 'sno IN (\'' . implode('\',\'', $orderDeliveryNoArr) . '\')', $arrBindDelivery['bind'], false);
            unset($arrDataDelivery);
            unset($arrBindDelivery);
        }
    }

    /**
     * AfterGoods 환불 후 주문상품 정산 요청의 주문상품 테이블에 공급사 환불 후 정산 요청 정보 저장
     *
     * @param array   $orderGoodsNoArr 환불 후 정산 처리(요청,반려)한 주문상품의 고유번호
     * @param integer $scmAdjustNo     환불 후 정산 처리 고유번호
     *
     */
    protected function setOrderGoodsScmAdjustAfterNo($orderGoodsNoArr, $scmAdjustNo)
    {
        if (is_array($orderGoodsNoArr)) {
            $arrDataGoods['scmAdjustAfterNo'] = $scmAdjustNo;
            $arrBindGoods = $this->db->get_binding(DBTableField::tableOrderGoods(), $arrDataGoods, 'update', array_keys($arrDataGoods), ['sno']);
            $this->db->set_update_db(DB_ORDER_GOODS, $arrBindGoods['param'], 'sno IN (\'' . implode('\',\'', $orderGoodsNoArr) . '\')', $arrBindGoods['bind'], false);
            unset($arrDataGoods);
            unset($arrBindGoods);
        }
    }

    /**
     * AfterAddGoods 환불 후 주문상품 정산 요청의 추가주문상품 테이블에 공급사 환불 후 정산 요청 정보 저장
     *
     * @param array   $orderAddGoodsNoArr 환불 후 정산 처리(요청,반려)한 주문추가상품의 고유번호
     * @param integer $scmAdjustNo        환불 후 정산 처리 고유번호
     *
     */
    protected function setOrderAddGoodsScmAdjustAfterNo($orderAddGoodsNoArr, $scmAdjustNo)
    {
        if (is_array($orderAddGoodsNoArr)) {
            $arrDataAddGoods['scmAdjustAfterNo'] = $scmAdjustNo;
            $arrBindAddGoods = $this->db->get_binding(DBTableField::tableOrderAddGoods(), $arrDataAddGoods, 'update', array_keys($arrDataAddGoods), ['sno']);
            $this->db->set_update_db(DB_ORDER_ADD_GOODS, $arrBindAddGoods['param'], 'sno IN (\'' . implode('\',\'', $orderAddGoodsNoArr) . '\')', $arrBindAddGoods['bind'], false);
            unset($arrDataAddGoods);
            unset($arrBindAddGoods);
        }
    }

    /**
     * AfterDelivery 환불 후 배송비 정산 요청의 주문배송비 테이블에 공급사 환불 후 정산 요청 정보 저장
     *
     * @param array   $orderDeliveryNoArr 환불 후 정산 처리(요청,반려)한 주문배송비의 고유번호
     * @param integer $scmAdjustNo        환불 후 정산 처리 고유번호
     *
     */
    protected function setOrderDeliveryScmAdjustAfterNo($orderDeliveryNoArr, $scmAdjustNo)
    {
        if (is_array($orderDeliveryNoArr)) {
            $arrDataDelivery['scmAdjustAfterNo'] = $scmAdjustNo;
            $arrBindDelivery = $this->db->get_binding(DBTableField::tableOrderDelivery(), $arrDataDelivery, 'update', array_keys($arrDataDelivery), ['sno']);
            $this->db->set_update_db(DB_ORDER_DELIVERY, $arrBindDelivery['param'], 'sno IN (\'' . implode('\',\'', $orderDeliveryNoArr) . '\')', $arrBindDelivery['bind'], false);
            unset($arrDataDelivery);
            unset($arrBindDelivery);
        }
    }

    /**
     * 공급사 정산 세금계산서 정보 저장
     *
     * @param array   $scmAdjustNoArr     정산 고유번호
     * @param integer $scmAdjustTaxBillNo 정산 세금계산서 고유번호
     *
     */
    protected function setScmAdjustTaxBill($scmAdjustNoArr, $scmAdjustTaxBillNo)
    {
        if (is_array($scmAdjustNoArr)) {
            $arrDataScmAdjust['scmAdjustTaxBillNo'] = $scmAdjustTaxBillNo;
            $arrBindScmAdjust = $this->db->get_binding(DBTableField::tableScmAdjust(), $arrDataScmAdjust, 'update', array_keys($arrDataScmAdjust), ['scmAdjustNo']);
            $this->db->set_update_db(DB_SCM_ADJUST, $arrBindScmAdjust['param'], 'scmAdjustNo IN (\'' . implode('\',\'', $scmAdjustNoArr) . '\')', $arrBindScmAdjust['bind'], false);
            unset($arrDataScmAdjust);
            unset($arrBindScmAdjust);
        }
    }

    /**
     * Log 정산 로그 처리
     *
     * @param integer $scmAdjustNo    정산 처리 고유번호
     * @param integer $scmAdjustState 정산 처리 코드번호 (요청[1], 반려[-1], 이월[40], 보류[50], 정산확정[10], 지급완료[30])
     * @param string  $scmAdjustMemo  정산 반려 시 메모
     *
     * @throws \Exception
     */
    protected function setScmAdjustLog($scmAdjustNo, $scmAdjustState, $scmAdjustMemo = null)
    {
        if ($scmAdjustNo > 0) {
            // 정산 요청 로그 등록
            $arrLogData['scmAdjustNo'] = $scmAdjustNo;
            $arrLogData['managerScmNo'] = Session::get('manager.scmNo');
            $arrLogData['managerId'] = Session::get('manager.managerId');
            $arrLogData['managerNo'] = Session::get('manager.sno');
            $arrLogData['managerNm'] = Session::get('manager.managerNm');
            if ($scmAdjustState != 'taxBill') {
                $arrLogData['scmAdjustState'] = $scmAdjustState;
            }
            $arrLogData['scmAdjustMemo'] = $scmAdjustMemo;
            $arrLogBind = $this->db->get_binding(DBTableField::tableScmAdjustLog(), $arrLogData, 'insert', array_keys($arrLogData), ['scmAdjustLogNo']);
            $this->db->set_insert_db(DB_SCM_ADJUST_LOG, $arrLogBind['param'], $arrLogBind['bind'], 'y');
            unset($arrLogData);
            unset($arrLogBind);
        } else {
            throw new \Exception(__('정산 정보가 없습니다.'));
        }
    }

    /**
     * 관리자 공급사 리스트를 위한 검색 정보 세팅
     *
     * @param string $searchData 검색 데이타
     */
    protected function setScmAdjustListSearch($searchData = null)
    {
        // 통합 검색
        // --- $searchData trim 처리
        if (isset($searchData)) {
            gd_trim($searchData);
        } else {
            $searchData = gd_trim(Request::get()->toArray());
        }

        // --- 정렬
        $this->search['sortList'] = [
            'sa.regDt desc'       => __('요청일') . '↓',
            'sa.regDt asc'        => __('요청일') . '↑',
            'sa.scmAdjustDt desc' => __('처리일') . '↓',
            'sa.scmAdjustDt asc'  => __('처리일') . '↑',
        ];

        // --- 검색 설정
        $this->search['scmFl'] = gd_isset($searchData['scmFl'], 'all');
        $this->search['scmNo'] = gd_isset($searchData['scmNo']);
        $this->search['scmNoNm'] = gd_isset($searchData['scmNoNm']);
        $this->search['scmAdjustType'] = gd_isset($searchData['scmAdjustType']);
        $this->search['scmAdjustTaxBillNo'] = gd_isset($searchData['taxBillNo']);
        $this->search['scmAdjustKind'] = gd_isset($searchData['scmAdjustKind']);
        $this->search['scmAdjustState'] = gd_isset($searchData['scmAdjustState']);
        $this->search['treatDate'][] = gd_isset($searchData['treatDate'][0], $searchData['periodFl'] == -1 ? $searchData['treatDate'][0] : date('Y-m-d', strtotime('-6 day')));
        $this->search['treatDate'][] = gd_isset($searchData['treatDate'][1], $searchData['periodFl'] == -1 ? $searchData['treatDate'][1] : date('Y-m-d'));
        $this->search['sort'] = gd_isset($searchData['sort'], 'sa.regDt desc');

        // 세금계산서 선택
        if ($this->search['scmAdjustTaxBillNo']) {
            $this->arrWhere[] = 'sa.scmAdjustTaxBillNo = ?';
            $this->db->bind_param_push($this->arrBind, 'i', $this->search['scmAdjustTaxBillNo']);
        }
        // 공급사 선택
        if (Manager::isProvider()) {
            // 공급사로 로그인한 경우 기존 scm에 값 설정
            $this->arrWhere[] = 'sa.scmNo = ' . Session::get('manager.scmNo');
        } else {
            if ($this->search['scmFl'] == '1') {
                if (is_array($this->search['scmNo'])) {
                    foreach ($this->search['scmNo'] as $val) {
                        $tmpWhere[] = 'sa.scmNo = ?';
                        $this->db->bind_param_push($this->arrBind, 's', $val);
                    }
                    $this->arrWhere[] = '(' . implode(' OR ', $tmpWhere) . ')';
                    unset($tmpWhere);
                } else if ($this->search['scmNo'] > 1) {
                    $this->arrWhere[] = 'sa.scmNo = ?';
                    $this->db->bind_param_push($this->arrBind, 'i', $this->search['scmNo']);
                }
            } elseif ($this->search['scmFl'] == '0') {
                $this->arrWhere[] = 'sa.scmNo = 1';
            }
        }
        $this->checked['scmFl'][$this->search['scmFl']] = 'checked="checked"';

        // 요청타입
        if ($this->search['scmAdjustType'][0]) {
            foreach ($this->search['scmAdjustType'] as $val) {
                $tmpWhere[] = 'sa.scmAdjustType = ?';
                $this->db->bind_param_push($this->arrBind, 's', $val);
                $this->checked['scmAdjustType'][$val] = 'checked="checked"';
            }
            $this->arrWhere[] = '(' . implode(' OR ', $tmpWhere) . ')';
            unset($tmpWhere);
        } else {
            $this->checked['scmAdjustType'][''] = 'checked="checked"';
        }

        // 정산타입
        if ($this->search['scmAdjustKind'][0]) {
            foreach ($this->search['scmAdjustKind'] as $val) {
                $tmpWhere[] = 'sa.scmAdjustKind = ?';
                $this->db->bind_param_push($this->arrBind, 's', $val);
                $this->checked['scmAdjustKind'][$val] = 'checked="checked"';
            }
            $this->arrWhere[] = '(' . implode(' OR ', $tmpWhere) . ')';
            unset($tmpWhere);
        } else {
            $this->checked['scmAdjustKind'][''] = 'checked="checked"';
        }

        // 정산상태
        if ($this->search['scmAdjustState'][0]) {
            foreach ($this->search['scmAdjustState'] as $val) {
                $tmpWhere[] = 'sa.scmAdjustState = ?';
                $this->db->bind_param_push($this->arrBind, 'i', $val);
                $this->checked['scmAdjustState'][$val] = 'checked="checked"';
            }
            $this->arrWhere[] = '(' . implode(' OR ', $tmpWhere) . ')';
            unset($tmpWhere);
        } else {
            $this->checked['scmAdjustState'][''] = 'checked="checked"';
        }

        // 처리일자 검색
        if ($this->search['treatDate']) {
            if ($this->search['treatDate'][0] && $this->search['treatDate'][1]) {
                $this->arrWhere[] = '(sa.scmAdjustDt BETWEEN DATE_FORMAT(?,\'%Y-%m-%d 00:00:00\') AND DATE_FORMAT(?,\'%Y-%m-%d 23:59:59\'))';
                $this->db->bind_param_push($this->arrBind, 's', $this->search['treatDate'][0]);
                $this->db->bind_param_push($this->arrBind, 's', $this->search['treatDate'][1]);
            } else if ($this->search['treatDate'][0] && !$this->search['treatDate'][1]) {
                $this->arrWhere[] = '(sa.scmAdjustDt >= DATE_FORMAT(?,\'%Y-%m-%d 00:00:00\')';
                $this->db->bind_param_push($this->arrBind, 's', $this->search['treatDate'][0]);
            } else if (!$this->search['treatDate'][0] && $this->search['treatDate'][1]) {
                $this->arrWhere[] = '(sa.scmAdjustDt <= DATE_FORMAT(?,\'%Y-%m-%d 23:59:59\')';
                $this->db->bind_param_push($this->arrBind, 's', $this->search['treatDate'][1]);
            }
        }

        if (empty($this->arrBind)) {
            $this->arrBind = null;
        }
    }

    /**
     * 관리자 공급사 통합 리스트를 위한 검색 정보 세팅
     *
     * @param string $searchData 검색 데이타
     */
    protected function _setScmAdjustTotalSearch($searchData)
    {
        // --- 검색 설정
        $this->search['scmFl'] = gd_isset($searchData['scmFl'], 'all');
        $this->search['scmNo'] = gd_isset($searchData['scmNo']);
        $this->search['scmNoNm'] = gd_isset($searchData['scmNoNm']);
        $this->search['periodFl'] = gd_isset($searchData['periodFl']);
        $this->search['treatDate'][] = gd_isset($searchData['treatDate'][0], $searchData['periodFl'] == -1 ? $searchData['treatDate'][0] : date('Y-m-d', strtotime('-6 day')));
        $this->search['treatDate'][] = gd_isset($searchData['treatDate'][1], $searchData['periodFl'] == -1 ? $searchData['treatDate'][1] : date('Y-m-d'));

        // 공급사 선택
        if (Manager::isProvider()) {
            // 공급사로 로그인한 경우 기존 scm에 값 설정
            $this->arrWhere[] = 'sa.scmNo = ' . Session::get('manager.scmNo');
        } else {
            if ($this->search['scmFl'] == '1') {
                if (is_array($this->search['scmNo'])) {
                    foreach ($this->search['scmNo'] as $val) {
                        $tmpWhere[] = 'sa.scmNo = ?';
                        $this->db->bind_param_push($this->arrBind, 's', $val);
                    }
                    $this->arrWhere[] = '(' . implode(' OR ', $tmpWhere) . ')';
                    unset($tmpWhere);
                } else if ($this->search['scmNo'] > 1) {
                    $this->arrWhere[] = 'sa.scmNo = ?';
                    $this->db->bind_param_push($this->arrBind, 'i', $this->search['scmNo']);
                }
            } elseif ($this->search['scmFl'] == '0') {
                $this->arrWhere[] = 'sa.scmNo = 1';
            }
        }
        $this->checked['scmFl'][$this->search['scmFl']] = 'checked="checked"';

        // 처리일자 검색
        if ($this->search['treatDate']) {
            if ($this->search['treatDate'][0] && $this->search['treatDate'][1]) {
                $this->arrWhere[] = '(sa.scmAdjustDt BETWEEN DATE_FORMAT(?,\'%Y-%m-%d 00:00:00\') AND DATE_FORMAT(?,\'%Y-%m-%d 23:59:59\'))';
                $this->db->bind_param_push($this->arrBind, 's', $this->search['treatDate'][0]);
                $this->db->bind_param_push($this->arrBind, 's', $this->search['treatDate'][1]);
            } else if ($this->search['treatDate'][0] && !$this->search['treatDate'][1]) {
                $this->arrWhere[] = '(sa.scmAdjustDt >= DATE_FORMAT(?,\'%Y-%m-%d 00:00:00\')';
                $this->db->bind_param_push($this->arrBind, 's', $this->search['treatDate'][0]);
            } else if (!$this->search['treatDate'][0] && $this->search['treatDate'][1]) {
                $this->arrWhere[] = '(sa.scmAdjustDt <= DATE_FORMAT(?,\'%Y-%m-%d 23:59:59\')';
                $this->db->bind_param_push($this->arrBind, 's', $this->search['treatDate'][1]);
            }
        }

        if (empty($this->arrBind)) {
            $this->arrBind = null;
        }
    }

    /**
     * 관리자 공급사 통합 리스트를 위한 검색 정보 세팅
     *
     * @param string $searchData 검색 데이타
     */
    protected function _setTaxBillSearch($searchData)
    {
        // --- 검색 설정
        $this->search['scmFl'] = gd_isset($searchData['scmFl'], 'all');
        $this->search['scmNo'] = gd_isset($searchData['scmNo']);
        $this->search['scmNoNm'] = gd_isset($searchData['scmNoNm']);
        $this->search['treatDate'][] = gd_isset($searchData['treatDate'][0], $searchData['periodFl'] == -1 ? $searchData['treatDate'][0] : date('Y-m-d', strtotime('-6 day')));
        $this->search['treatDate'][] = gd_isset($searchData['treatDate'][1], $searchData['periodFl'] == -1 ? $searchData['treatDate'][1] : date('Y-m-d'));
        $this->search['taxBillType'] = gd_isset($searchData['taxBillType'], '');

        // 공급사 선택
        if (Manager::isProvider()) {
            // 공급사로 로그인한 경우 기존 scm에 값 설정
            $this->arrWhere[] = 'satb.scmNo = ' . Session::get('manager.scmNo');
        } else {
            if ($this->search['scmFl'] == '1') {
                if (is_array($this->search['scmNo'])) {
                    foreach ($this->search['scmNo'] as $val) {
                        $tmpWhere[] = 'satb.scmNo = ?';
                        $this->db->bind_param_push($this->arrBind, 's', $val);
                    }
                    $this->arrWhere[] = '(' . implode(' OR ', $tmpWhere) . ')';
                    unset($tmpWhere);
                } else if ($this->search['scmNo'] > 1) {
                    $this->arrWhere[] = 'satb.scmNo = ?';
                    $this->db->bind_param_push($this->arrBind, 'i', $this->search['scmNo']);
                }
            } elseif ($this->search['scmFl'] == '0') {
                $this->arrWhere[] = 'satb.scmNo = 1';
            }
        }
        $this->checked['scmFl'][$this->search['scmFl']] = 'checked="checked"';

        // 발행일자 검색
        if ($this->search['treatDate']) {
            if ($this->search['treatDate'][0] && $this->search['treatDate'][1]) {
                $this->arrWhere[] = '(satb.regDt BETWEEN DATE_FORMAT(?,\'%Y-%m-%d 00:00:00\') AND DATE_FORMAT(?,\'%Y-%m-%d 23:59:59\'))';
                $this->db->bind_param_push($this->arrBind, 's', $this->search['treatDate'][0]);
                $this->db->bind_param_push($this->arrBind, 's', $this->search['treatDate'][1]);
            } else if ($this->search['treatDate'][0] && !$this->search['treatDate'][1]) {
                $this->arrWhere[] = '(satb.regDt >= DATE_FORMAT(?,\'%Y-%m-%d 00:00:00\')';
                $this->db->bind_param_push($this->arrBind, 's', $this->search['treatDate'][0]);
            } else if (!$this->search['treatDate'][0] && $this->search['treatDate'][1]) {
                $this->arrWhere[] = '(satb.regDt <= DATE_FORMAT(?,\'%Y-%m-%d 23:59:59\')';
                $this->db->bind_param_push($this->arrBind, 's', $this->search['treatDate'][1]);
            }
        }

        // 세금계산서 발행 종류
        if ($this->search['taxBillType'][0]) {
            foreach ($this->search['taxBillType'] as $val) {
                $tmpWhere[] = 'satb.scmAdjustTaxBillType = ?';
                $this->db->bind_param_push($this->arrBind, 's', $val);
                $this->checked['taxBillType'][$val] = 'checked="checked"';
            }
            $this->arrWhere[] = '(' . implode(' OR ', $tmpWhere) . ')';
            unset($tmpWhere);
        } else {
            $this->checked['taxBillType'][''] = 'checked="checked"';
        }

        if (empty($this->arrBind)) {
            $this->arrBind = null;
        }
    }

    /**
     * 관리자 정산 리스트를 위한 검색 정보 세팅
     *
     * @param string     $searchData   검색 데이타
     * @param int|string $searchPeriod 기본 조회 기간
     */
    protected function _setScmAdjustOrderSearch($searchData, $searchPeriod)
    {
        // 통합 검색
        $this->search['combineSearch'] = [
            'o.orderNo'    => __('주문번호'),
            'oi.orderName' => __('주문자명'),
            'og.sno'       => __('상품주문번호'),
        ];

        // !중요! 순서 변경시 하단의 노출항목 조절 필요
        $this->search['combineTreatDate'] = [
            'og.regDt' => __('주문일'),
        ];

        // --- $searchData trim 처리
        if (isset($searchData)) {
            gd_trim($searchData);
        }

        // --- 정렬
        $this->search['sortList'] = [
            'o.regDt desc' => __('주문일') . '↓',
            'o.regDt asc'  => __('주문일') . '↑',
        ];

        // --- 검색 설정
        $this->search['key'] = gd_isset($searchData['key']);
        $this->search['keyword'] = gd_isset($searchData['keyword']);
        $this->search['sort'] = gd_isset($searchData['sort']);
        $this->search['orderStatus'] = gd_isset($searchData['orderStatus']);
        $this->search['treatDateFl'] = gd_isset($searchData['treatDateFl'], 'og.regDt');
        $this->search['treatDate'][] = gd_isset($searchData['treatDate'][0], $searchData['periodFl'] == -1 ? $searchData['treatDate'][0] : date('Y-m-d', strtotime('-' . $searchPeriod . ' day')));
        $this->search['treatDate'][] = gd_isset($searchData['treatDate'][1], $searchData['periodFl'] == -1 ? $searchData['treatDate'][1] : date('Y-m-d'));
        $this->search['scmFl'] = gd_isset($searchData['scmFl'], 'all');
        $this->search['scmNo'] = gd_isset($searchData['scmNo']);
        $this->search['scmNoNm'] = gd_isset($searchData['scmNoNm']);
        $this->search['statusMode'] = gd_isset($searchData['statusMode']); // 없으면 기본 구매확정 단계

        // --- 검색 설정
        $this->checked['scmFl'][$this->search['scmFl']] = 'checked="checked"';

        // 주문 상태 모드가 있는 경우
        if ($this->search['statusMode']) {
            if (is_array($this->search['statusMode'])) {
                $tempStatusWhere = '(';
                foreach ($this->search['statusMode'] as $v) {
                    if ($tempStatusWhere != '(') {
                        $tempStatusWhere .= ' OR ';
                    }
                    $tempStatusWhere .= 'og.orderStatus LIKE ?';
                    $this->db->bind_param_push($this->arrBind, 's', $v . '%');
                }
                $tempStatusWhere .= ')';
                $this->arrWhere[] = $tempStatusWhere;
            } else {
                $this->arrWhere[] = '(og.orderStatus LIKE ?)';
                $this->db->bind_param_push($this->arrBind, 's', $this->search['statusMode'] . '%');
            }
        }

        // 공급사 선택
        if (Manager::isProvider()) {
            // 공급사로 로그인한 경우 기존 scm에 값 설정
            $this->arrWhere[] = 'og.scmNo = ' . Session::get('manager.scmNo');
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
            $this->arrWhere[] = '(' . $this->search['key'] . ' LIKE ?)';
            $this->db->bind_param_push($this->arrBind, 's', '%' . $this->search['keyword'] . '%');
        }

        // 처리일자 검색
        if ($this->search['treatDateFl'] && $searchPeriod != -1 && $this->search['treatDate'][0] && $this->search['treatDate'][1]) {
            $this->arrWhere[] = '(' . $this->search['treatDateFl'] . ' >= ? AND ' . $this->search['treatDateFl'] . ' <= ?)';
            $this->db->bind_param_push($this->arrBind, 's', $this->search['treatDate'][0] . ' 00:00:00');
            $this->db->bind_param_push($this->arrBind, 's', $this->search['treatDate'][1] . ' 23:59:59');
        }

        if (empty($this->arrBind)) {
            $this->arrBind = null;
        }
    }

    /**
     * 관리자 배송비 정산 리스트를 위한 검색 정보 세팅
     *
     * @param string     $searchData   검색 데이타
     * @param int|string $searchPeriod 기본 조회 기간
     */
    protected function _setScmAdjustDeliverySearch($searchData, $searchPeriod)
    {
        // 통합 검색
        $this->search['combineSearch'] = [
            'o.orderNo'      => __('주문번호'),
            'oi.orderName'   => __('주문자명'),
            'od.deliverySno' => __('배송번호'),
        ];

        // !중요! 순서 변경시 하단의 노출항목 조절 필요
        $this->search['combineTreatDate'] = [
            'od.regDt' => __('주문일'),
        ];

        // --- $searchData trim 처리
        if (isset($searchData)) {
            gd_trim($searchData);
        }

        // --- 정렬
        $this->search['sortList'] = [
            'od.regDt desc' => __('주문일') . '↓',
            'od.regDt asc'  => __('주문일') . '↑',
        ];

        // --- 검색 설정
        $this->search['key'] = gd_isset($searchData['key']);
        $this->search['keyword'] = gd_isset($searchData['keyword']);
        $this->search['sort'] = gd_isset($searchData['sort']);
        $this->search['orderStatus'] = gd_isset($searchData['orderStatus']);
        $this->search['treatDateFl'] = gd_isset($searchData['treatDateFl'], 'od.regDt');
        $this->search['treatDate'][] = gd_isset($searchData['treatDate'][0], $searchData['periodFl'] == -1 ? $searchData['treatDate'][0] : date('Y-m-d', strtotime('-' . $searchPeriod . ' day')));
        $this->search['treatDate'][] = gd_isset($searchData['treatDate'][1], $searchData['periodFl'] == -1 ? $searchData['treatDate'][1] : date('Y-m-d'));
        $this->search['scmFl'] = gd_isset($searchData['scmFl'], 'all');
        $this->search['scmNo'] = gd_isset($searchData['scmNo']);
        $this->search['scmNoNm'] = gd_isset($searchData['scmNoNm']);
        $this->search['statusMode'] = gd_isset($searchData['statusMode']); // 없으면 기본 구매확정 단계

        // --- 검색 설정
        $this->checked['scmFl'][$this->search['scmFl']] = 'checked="checked"';

        // 주문 상태 모드가 있는 경우
        if ($this->search['statusMode']) {
            if (is_array($this->search['statusMode'])) {
                $tempStatusWhere = '(';
                foreach ($this->search['statusMode'] as $v) {
                    if ($tempStatusWhere != '(') {
                        $tempStatusWhere .= ' OR ';
                    }
                    $tempStatusWhere .= 'og.orderStatus LIKE ?';
                    $this->db->bind_param_push($this->arrBind, 's', $v . '%');
                }
                $tempStatusWhere .= ')';
                $this->arrWhere[] = $tempStatusWhere;
            } else {
                $this->arrWhere[] = '(og.orderStatus LIKE ?)';
                $this->db->bind_param_push($this->arrBind, 's', $this->search['statusMode'] . '%');
            }
        }

        // 공급사 선택
        if (Manager::isProvider()) {
            // 공급사로 로그인한 경우 기존 scm에 값 설정
            $this->arrWhere[] = 'od.scmNo = ' . Session::get('manager.scmNo');
        } else {
            if ($this->search['scmFl'] == '1') {
                if (is_array($this->search['scmNo'])) {
                    foreach ($this->search['scmNo'] as $val) {
                        $tmpWhere[] = 'od.scmNo = ?';
                        $this->db->bind_param_push($this->arrBind, 's', $val);
                    }
                    $this->arrWhere[] = '(' . implode(' OR ', $tmpWhere) . ')';
                    unset($tmpWhere);
                } else if ($this->search['scmNo'] > 1) {
                    $this->arrWhere[] = 'od.scmNo = ?';
                    $this->db->bind_param_push($this->arrBind, 'i', $this->search['scmNo']);
                }
            } elseif ($this->search['scmFl'] == '0') {
                $this->arrWhere[] = 'od.scmNo = 1';
            }
        }

        // 키워드 검색
        if ($this->search['key'] && $this->search['keyword']) {
            $this->arrWhere[] = '(' . $this->search['key'] . ' LIKE ?)';
            $this->db->bind_param_push($this->arrBind, 's', '%' . $this->search['keyword'] . '%');
        }

        // 처리일자 검색
        if ($this->search['treatDateFl'] && $searchPeriod != -1 && $this->search['treatDate'][0] && $this->search['treatDate'][1]) {
            $this->arrWhere[] = '(' . $this->search['treatDateFl'] . ' >= ? AND ' . $this->search['treatDateFl'] . ' <= ?)';
            $this->db->bind_param_push($this->arrBind, 's', $this->search['treatDate'][0] . ' 00:00:00');
            $this->db->bind_param_push($this->arrBind, 's', $this->search['treatDate'][1] . ' 23:59:59');
        }

        if (empty($this->arrBind)) {
            $this->arrBind = null;
        }
    }

    /**
     * getTaxBillScmList
     * 공급사 정산 공급사별 세금계산서 신청가능 리스트
     *
     * @param $getValue
     *
     * @return mixed
     */
    public function getTaxBillScmList(&$getValue)
    {
        $this->_setScmAdjustTotalSearch($getValue);

        if ($getValue['chkScm'] && is_array($getValue['chkScm'])) {
            $this->arrWhere[] = 'sa.scmNo IN (' . implode(',', $getValue['chkScm']) . ')';
        }

        // 지급완료 만 세금계산서 처리 30
        // 정산확정도 포함할 시 10 추가
        $this->arrWhere[] = 'sa.scmAdjustState = ?'; //        $this->arrWhere[] = 'sa.scmAdjustState IN (?, ?)';
        $this->db->bind_param_push($this->arrBind, 'i', 30);
//        $this->db->bind_param_push($this->arrBind, 'i', 10);

        // 세금계산서 발행 내역이 없는 경우
        $this->arrWhere[] = 'sa.scmAdjustTaxBillNo < ?';
        $this->db->bind_param_push($this->arrBind, 'i', 1);

        // 수수료가 0 이 아닌 것만
        $this->arrWhere[] = 'sa.scmAdjustCommissionPrice != ?';
        $this->db->bind_param_push($this->arrBind, 'i', 0);

        // 정산 매출
        $this->db->strField = "sa.*";
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_SCM_ADJUST . ' as sa ' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $this->arrBind);
        unset($this->arrBind);
        unset($this->arrWhere);

        foreach ($data as $key => $val) {
            if ($val['scmNo'] > 0) {
                $scm = new Scm();
                $getScmAdjustTotal['list'][$val['scmNo']]['scmNo'] = $val['scmNo'];
                $scmData = $scm->getScmInfo($val['scmNo'], 'companyNm, scmType');
                $getScmAdjustTotal['list'][$val['scmNo']]['scmName'] = $scmData['companyNm'];
                $getScmAdjustTotal['list'][$val['scmNo']]['scmType'] = $scmData['scmType'];
                $getScmAdjustTotal['list'][$val['scmNo']]['scmAdjustNo'][] = $val['scmAdjustNo'];
                $getScmAdjustTotal['list'][$val['scmNo']][$val['scmAdjustType']]['totalPrice'] += $val['scmAdjustTotalPrice'];
                $getScmAdjustTotal['list'][$val['scmNo']][$val['scmAdjustType']]['adjustPrice'] += $val['scmAdjustPrice'];
                $getScmAdjustTotal['list'][$val['scmNo']][$val['scmAdjustType']]['taxPrice'] += $val['scmAdjustCommissionTaxPrice'];
                $getScmAdjustTotal['list'][$val['scmNo']][$val['scmAdjustType']]['vatPrice'] += $val['scmAdjustCommissionVatPrice'];
            }
        }

        $getData['data'] = gd_htmlspecialchars_stripslashes(gd_isset($getScmAdjustTotal));
        $getData['search'] = gd_htmlspecialchars($this->search);
        $getData['checked'] = $this->checked;

        return $getData;
    }


    /**
     * 관리자 공급사 정산 공급사별 세금계산서 발급
     *
     * @param array $getValue
     *
     * @throws \Exception
     */
    public function setScmAdjustScmTaxBill($getValue)
    {
        $getData = $this->getTaxBillScmList($getValue);

        try {
            $this->db->begin_tran();
            foreach ($getData['data']['list'] as $scmKey => $scmVal) {
                $scm = new ScmAdmin();
                $scmDataField = 'sm.companyNm, sm.ceoNm, sm.businessNo, sm.service, sm.item, sm.zipcode, sm.zonecode, sm.address, sm.addressSub';
                $scmData = $scm->getScmInfo($scmKey, $scmDataField);
                $arrData['scmNo'] = $scmKey;
                $arrData['scmCompanyNm'] = $scmData['companyNm'];
                $arrData['scmCeoNm'] = $scmData['ceoNm'];
                $arrData['scmBusinessNo'] = $scmData['businessNo'];
                $arrData['scmService'] = $scmData['service'];
                $arrData['scmItem'] = $scmData['item'];
                $arrData['scmZipcode'] = $scmData['zipcode'];
                $arrData['scmZoneCode'] = $scmData['zonecode'];
                $arrData['scmAddress'] = $scmData['address'];
                $arrData['scmAddressSub'] = $scmData['addressSub'];
                $arrData['scmAdjustTaxBillType'] = $getValue['scmAdjustTaxBill'];
                $arrData['scmAdjustTaxBillState'] = 'y';
                $taxPrice = $scmVal['o']['taxPrice'] + $scmVal['d']['taxPrice'] + $scmVal['oa']['taxPrice'] + $scmVal['da']['taxPrice'];
                $vatPrice = $scmVal['o']['vatPrice'] + $scmVal['d']['vatPrice'] + $scmVal['oa']['vatPrice'] + $scmVal['da']['vatPrice'];
                $arrData['scmAdjustTaxPrice'] = $taxPrice;
                $arrData['scmAdjustVatPrice'] = $vatPrice;
                $arrData['scmAdjustTaxBillDt'] = $getValue['taxBillDate'];

                // 세금계산서 등록
                $arrBind = $this->db->get_binding(DBTableField::tableScmAdjustTaxBill(), $arrData, 'insert', array_keys($arrData), ['scmAdjustTaxBillNo']);
                $this->db->set_insert_db(DB_SCM_ADJUST_TAXBILL, $arrBind['param'], $arrBind['bind'], 'y');
                $scmAdjustTaxBillNo = $this->db->insert_id();

                if ($getValue['scmAdjustTaxBill'] == 'godo') {
                    $godoBillConfig = gd_policy('order.taxInvoice');
                    if ($godoBillConfig['taxInvoiceUseFl'] == 'y' && $godoBillConfig['eTaxInvoiceFl'] == 'y') {
                        $godoBillData = $this->setGodoBill($scmAdjustTaxBillNo);
                        if ($godoBillData[0] == 'DONE') {
                            $billMemo = __('전자세금계산서 발행') . ' -- <br/>';
                            $billMemo .= __('고도빌 전송') . ' : ' . $godoBillData[1] . '<br/>';
                            $billMemo .= __('고도빌') . ' CODE : ' . $godoBillData[2] . '<br/>';
                        } else {
                            $billMemo = __('전자세금계산서 발행 실패') . ' -- <br/>';
                            $billMemo .= __('고도빌 전송') . ' : ' . $godoBillData[1] . '<br/>';
                            $billMemo .= __('오류 코드') . ' : ' . $godoBillData[2] . '<br/>';
                            throw new \Exception($billMemo);
                        }
                    } else {
                        throw new \Exception(__('전자세금계산서를 설정하셔야 합니다.'));
                    }
                } else {
                    $billMemo = __('일반세금계산서 발행') . ' -- ';
                }
                // 정산에 세금계산서 정보 등록
                $this->setScmAdjustTaxBill($scmVal['scmAdjustNo'], $scmAdjustTaxBillNo);
                foreach ($scmVal['scmAdjustNo'] as $val) {
                    // 정산 요청 로그 등록
                    $this->setScmAdjustLog($val, 'taxBill', $billMemo);
                }
                unset($arrData);
                unset($arrBind);
                unset($scmAdjustTaxBillNo);
            }
            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollback();
            throw new \Exception(__('정산 세금계산서 처리 중 오류가 발생하였습니다. 다시 시도해 주세요.'));
        }
    }

    /**
     * 공급사 정산 정산별 세금계산서 신청가능 리스트
     *
     * @author su
     */
    public function getTaxBillOrderList(&$getValue)
    {
        $this->_setScmAdjustTotalSearch($getValue);

        gd_isset($getValue['page'], 1);
        gd_isset($getValue['pageNum'], 10);

        $page = \App::load('\\Component\\Page\\Page', $getValue['page']);
        $page->page['list'] = $getValue['pageNum'];

        if ($getValue['chk'] && is_array($getValue['chk'])) {
            $this->arrWhere[] = 'sa.scmAdjustNo IN (' . implode(',', $getValue['chk']) . ')';
        }

        // 지급완료 만 세금계산서 처리 30
        // 정산확정도 포함할 시 10 추가
        $this->arrWhere[] = 'sa.scmAdjustState = ?'; //        $this->arrWhere[] = 'sa.scmAdjustState IN (?, ?)';
        $this->db->bind_param_push($this->arrBind, 'i', 30);
        //        $this->db->bind_param_push($this->arrBind, 'i', 10);

        // 세금계산서 발행 내역이 없는 경우
        $this->arrWhere[] = 'sa.scmAdjustTaxBillNo < ?';
        $this->db->bind_param_push($this->arrBind, 'i', 1);

        // 수수료가 0 이 아닌 것만
        $this->arrWhere[] = 'sa.scmAdjustCommissionPrice != ?';
        $this->db->bind_param_push($this->arrBind, 'i', 0);

        //        list($page->recode['amount']) = $this->db->fetch('SELECT count(scmAdjustNo) FROM ' . DB_SCM_ADJUST . ' as sa WHERE ' . implode(' AND ', gd_isset($this->arrWhere)));

        $page->setPage();
        $page->setUrl(\Request::getQueryString());

        // 정산 매출
        $this->db->strField = "sa.*";
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
        $this->db->strLimit = $page->recode['start'] . ',' . $getValue['pageNum'];
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_SCM_ADJUST . ' as sa ' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $this->arrBind);
        // 검색 레코드 수
        $table = DB_SCM_ADJUST . ' as sa';
        $page->recode['total'] = $this->db->query_count($query, $table, $this->arrBind);
        $page->setPage();
        unset($this->arrBind);
        unset($this->arrWhere);

        $scm = new ScmAdmin();
        $scmDataField = 'sm.scmType, sm.companyNm, sm.ceoNm, sm.businessNo, sm.service, sm.item, sm.zipcode, sm.zonecode, sm.address, sm.addressSub';
        $scmData = $scm->getScmInfo($getValue['scmNo'], $scmDataField);

        $getData['scm'] = $scmData;
        $getData['data'] = gd_htmlspecialchars_stripslashes(gd_isset($data));
        $getData['search'] = gd_htmlspecialchars($this->search);
        $getData['checked'] = $this->checked;

        return $getData;
    }

    /**
     * 관리자 공급사 정산별 세금계산서 발급
     *
     * @param array $getValue
     *
     * @throws \Exception
     */
    public function setScmAdjustOrderTaxBill(&$getValue)
    {
        try {
            $getData = $this->getTaxBillOrderList($getValue);

            $arrData['scmNo'] = $getValue['scmNo'];
            $arrData['scmCompanyNm'] = $getData['scm']['companyNm'];
            $arrData['scmCeoNm'] = $getData['scm']['ceoNm'];
            $arrData['scmBusinessNo'] = $getData['scm']['businessNo'];
            $arrData['scmService'] = $getData['scm']['service'];
            $arrData['scmItem'] = $getData['scm']['item'];
            $arrData['scmZipcode'] = $getData['scm']['zipcode'];
            $arrData['scmZoneCode'] = $getData['scm']['zonecode'];
            $arrData['scmAddress'] = $getData['scm']['address'];
            $arrData['scmAddressSub'] = $getData['scm']['addressSub'];
            $arrData['scmAdjustTaxBillType'] = $getValue['scmAdjustTaxBill'];
            $arrData['scmAdjustTaxBillState'] = 'y';
            foreach ($getData['data'] as $adjustKey => $adjustVal) {
                $arrData['scmAdjustTaxPrice'] += $adjustVal['scmAdjustCommissionTaxPrice'];
                $arrData['scmAdjustVatPrice'] += $adjustVal['scmAdjustCommissionVatPrice'];
            }
            $arrData['scmAdjustTaxBillDt'] = $getValue['taxBillDate'];

            $this->db->begin_tran();
            // 세금계산서 등록
            $arrBind = $this->db->get_binding(DBTableField::tableScmAdjustTaxBill(), $arrData, 'insert', array_keys($arrData), ['scmAdjustTaxBillNo']);
            $this->db->set_insert_db(DB_SCM_ADJUST_TAXBILL, $arrBind['param'], $arrBind['bind'], 'y');
            $scmAdjustTaxBillNo = $this->db->insert_id();

            if ($getValue['scmAdjustTaxBill'] == 'godo') {
                $godoBillConfig = gd_policy('order.taxInvoice');
                if ($godoBillConfig['taxInvoiceUseFl'] == 'y' && $godoBillConfig['eTaxInvoiceFl'] == 'y') {
                    $godoBillData = $this->setGodoBill($scmAdjustTaxBillNo);
                    if ($godoBillData[0] == 'DONE') {
                        $billMemo = __('전자세금계산서 발행') . ' -- <br/>';
                        $billMemo .= __('고도빌') . ' ' . __('전송') . ' : ' . $godoBillData[1] . '<br/>';
                        $billMemo .= __('고도빌') . ' CODE : ' . $godoBillData[2] . '<br/>';
                    } else {
                        $billMemo = __('전자세금계산서 발행 실패') . ' -- <br/>';
                        $billMemo .= __('고도빌') . ' ' . __('전송') . ' : ' . $godoBillData[1] . '<br/>';
                        $billMemo .= __('오류 코드') . ' : ' . $godoBillData[2] . '<br/>';
                        throw new \Exception($billMemo);
                    }
                } else {
                    throw new \Exception(__('전자세금계산서를 설정하셔야 합니다.'));
                }
            } else {
                $billMemo = __('일반세금계산서 발행') . ' -- ';
            }
            // 정산에 세금계산서 정보 등록
            $this->setScmAdjustTaxBill($getValue['chk'], $scmAdjustTaxBillNo);
            foreach ($getValue['chk'] as $val) {
                // 정산 요청 로그 등록
                $this->setScmAdjustLog($val, 'taxBill', $billMemo);
            }
            unset($arrData);
            unset($arrBind);
            unset($scmAdjustTaxBillNo);
            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollback();
            throw new \Exception(__('정산 세금계산서 처리 중 오류가 발생하였습니다. 다시 시도해 주세요.'));
        }
    }

    /**
     * 공급사 정산 세금계산서 발행 리스트
     *
     * @author su
     */
    public function getTaxBillList($getValue)
    {
        $this->_setTaxBillSearch($getValue);

        gd_isset($getValue['page'], 1);
        gd_isset($getValue['pageNum'], 10);

        $page = \App::load('\\Component\\Page\\Page', $getValue['page']);
        $page->page['list'] = $getValue['pageNum'];
        if (Manager::isProvider()) {
            // 공급사로 로그인한 경우 기존 scm에 값 설정
            $scmQuery = ' AND satb.scmNo = ' . Session::get('manager.scmNo');
        }
        list($page->recode['amount']) = $this->db->fetch('SELECT count(scmNo) FROM ' . DB_SCM_ADJUST_TAXBILL . ' as satb WHERE satb.scmNo > 0' . $scmQuery, 'array');
        $page->setPage();
        $page->setUrl(\Request::getQueryString());

        $this->db->strField = "satb.* ";
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
        $this->db->strOrder = $this->search['sort'];
        $this->db->strLimit = $page->recode['start'] . ',' . $getValue['pageNum'];

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_SCM_ADJUST_TAXBILL . ' as satb ' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $this->arrBind);
        // 검색 레코드 수
        $table = DB_SCM_ADJUST_TAXBILL . ' as satb';
        $page->recode['total'] = $this->db->query_count($query, $table, $this->arrBind);
        $page->setPage();

        $getData['data'] = gd_htmlspecialchars_stripslashes(gd_isset($data));
        $getData['sort'] = $getValue['sort'];
        $getData['search'] = gd_htmlspecialchars($this->search);
        $getData['checked'] = $this->checked;

        return $getData;
    }

    /**
     * 공급사 정산 세금계산서 상세보기
     *
     * @author su
     */
    public function getTaxBillView($getValue)
    {
        $this->setScmAdjustListSearch($getValue);

        gd_isset($getValue['page'], 1);
        gd_isset($getValue['pageNum'], 10);

        $page = \App::load('\\Component\\Page\\Page', $getValue['page']);
        $page->page['list'] = $getValue['pageNum'];
        if (Manager::isProvider()) {
            // 공급사로 로그인한 경우 기존 scm에 값 설정
            $scmQuery = ' AND sa.scmNo = ' . Session::get('manager.scmNo');
        }
        list($page->recode['amount']) = $this->db->fetch('SELECT count(scmAdjustNo) FROM ' . DB_SCM_ADJUST . ' as sa WHERE sa.scmAdjustNo > 0' . $scmQuery, 'array');
        $page->setPage();
        $page->setUrl(\Request::getQueryString());

        $this->db->strField = "sa.*, sal.managerScmNo, sal.managerId, sal.managerNm,m.isDelete ";
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
        $this->db->strJoin = ' LEFT JOIN ' . DB_SCM_ADJUST_LOG . ' as sal ON sa.scmAdjustNo = sal.scmAdjustNo AND sa.scmAdjustState = sal.scmAdjustState LEFT JOIN ' . DB_MANAGER . ' as m ON m.sno = sal.managerNo ';
        $this->db->strOrder = $this->search['sort'];
        $this->db->strLimit = $page->recode['start'] . ',' . $getValue['pageNum'];

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_SCM_ADJUST . ' as sa ' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $this->arrBind);
        Manager::displayListData($data);

        // 검색 레코드 수
        $table = DB_SCM_ADJUST . ' as sa';
        $page->recode['total'] = $this->db->query_count($query, $table, $this->arrBind);
        $page->setPage();

        $taxBillDataField = 'satb.scmNo, satb.scmCompanyNm, satb.scmCeoNm, satb.scmBusinessNo, satb.scmService, satb.scmItem, satb.scmZipcode, satb.scmZoneCode, satb.scmAddress, satb.scmAddressSub, satb.scmAdjustTaxBillType, satb.scmAdjustTaxBillState';
        $taxBillData = $this->getTaxBillInfo($getValue['taxBillNo'], $taxBillDataField);

        $getData['taxBill'] = $taxBillData;
        $getData['data'] = gd_htmlspecialchars_stripslashes(gd_isset($data));
        $getData['sort'] = $getValue['sort'];
        $getData['search'] = gd_htmlspecialchars($this->search);
        $getData['checked'] = $this->checked;

        return $getData;
    }

    /**
     * 고도빌 전송 세팅 & 결과
     *
     * @param string $scmAdjustTaxBillNo 정산 세금계산서 고유번호
     *
     * @return array
     */
    protected function setGodoBill($scmAdjustTaxBillNo)
    {
        $taxBillData = $this->getTaxBillInfo($scmAdjustTaxBillNo);

        // 공급받는자 사업자등록번호 구분코드 (필수) 01:사업자등록번호 , 02:주민등록번호
        $arrData['DMDER_BUSNID_TP_CD'] = '01';
        // 공급받는자 사업자등록번호 (필수) 10자리 또는 13자리
        $arrData['DMDER_BUSNID'] = str_replace('-', '', $taxBillData['scmBusinessNo']);
        //공급받는자 종사업장번호	 (필수) 0자리 또는 4자리
        $arrData['DMDER_SUB_BD_NO'] = '';
        // 공급받는자 업태명 최대 20글자
        $arrData['DMDER_BUSNSECT_NM'] = trim(iconv('UTF-8', 'EUC-KR', $taxBillData['scmService']));
        // 공급받는자 종목명 최대 20글자
        $arrData['DMDER_DETAIL_NM'] = trim(iconv('UTF-8', 'EUC-KR', $taxBillData['scmItem']));
        // 공급받는자 대표자명 (필수) 최대 15글자
        $arrData['DMDER_CHIEF_NM'] = trim(iconv('UTF-8', 'EUC-KR', $taxBillData['scmCeoNm']));
        // 공급받는자 상호명 (필수) 최대 35글자
        $arrData['DMDER_TRADE_NM'] = trim(iconv('UTF-8', 'EUC-KR', $taxBillData['scmCompanyNm']));
        // 공급받는자 주소 최대 75글자
        $arrData['DMDER_ADDR'] = trim(iconv('UTF-8', 'EUC-KR', $taxBillData['scmAddress'] . ' ' . $taxBillData['scmAddressSub']));
        // 공급받는자 주담당자 이름 최대 15글자
        $arrData['DMDER_MAIN_TX_OFFCR_NM'] = '';
        // 공급받는자 주담당자 이메일 최대 40글자
        $arrData['DMDER_MAIN_TX_OFFCR_EMAIL_ADDR'] = '';
        // 공급받는자 주담당자 휴대폰번호 xxx-xxxx-xxxx 형식
        $arrData['DMDER_MAIN_TX_OFFCR_MTEL_NO'] = '';
        // 공급받는자 부담당자 이름 최대 15글자
        $arrData['DMDER_SUB_TX_OFFCR_NM'] = '';
        // 공급받는자 부담당자 이메일 최대 40글자
        $arrData['DMDER_SUB_TX_OFFCR_EMAIL_ADDR'] = '';
        // 공급받는자 부담당자 휴대폰번호 xxx-xxxx-xxxx 형식
        $arrData['DMDER_SUB_TX_OFFCR_MTEL_NO'] = '';
        // 작성일자 (필수) yyyymmdd 형식
        $arrData['WRITE_DT'] = gd_date_format('Ymd', $taxBillData['scmAdjustTaxBillDt']);
        // 비고 최대 75글자
        $arrData['ETAXBIL_NOTE'] = '';
        // 전자세금계산서종류코드 01:일반, 02:영세
        $arrData['ETAXBIL_KND_CD'] = '01';
        // 공급가액합계 (필수) 숫자(최대18자리)
        $arrData['SUP_AMT_SM'] = gd_money_format($taxBillData['scmAdjustTaxPrice'], false);
        // 세액합계 (필수) 숫자(최대18자리)
        $arrData['TX_SM'] = gd_money_format($taxBillData['scmAdjustVatPrice'], false);
        // 총금액 (필수) 숫자(최대18자리)
        $arrData['TOT_AMT'] = gd_money_format($taxBillData['scmAdjustTaxPrice'] + $taxBillData['scmAdjustVatPrice'], false);
        // 결제방법별 금액 : 현금 숫자(최대18자리)
        $arrData['PAYMENT_CASH_AMOUNT'] = '';
        // 결제방법별 금액 : 수표 숫자(최대18자리)
        $arrData['PAYMENT_CHECK_AMOUNT'] = '';
        // 결제방법별 금액 : 어음 숫자(최대18자리)
        $arrData['PAYMENT_BILL_AMOUNT'] = '';
        // 결제방법별 금액 : 외상미수금 숫자(최대18자리)
        $arrData['PAYMENT_CREDIT_AMOUNT'] = '';
        // 영수청구 구분코드 (필수) 01:영수,02:청구
        $arrData['RCPT_RQEST_TP_CD'] = '01';
        // 전자계산서유무 FREE (FREE 면 전자계산서 아니면 전자세금계산서입니다)
        $arrData['TAXMODE'] = '';

        $arrData['item'][] = [
            'THNG_PURCHS_DT' => gd_date_format('Ymd', $taxBillData['scmAdjustTaxBillDt']),
            'THNG_SUP_AMT'   => $taxBillData['scmAdjustTaxPrice'],
            'THNG_TX'        => $taxBillData['scmAdjustVatPrice'],
            'THNG_NM'        => trim(iconv('UTF-8', 'EUC-KR//IGNORE', '수수료')),
        ];

        // 고도빌로 전송
        $result = $this->setGodoBillHttp($arrData);

        if (substr($result, 0, 4) == 'DONE') {
            return [
                substr($result, 0, 4),
                __('고도빌 전송 완료'),
                substr($result, 4),
            ];
        } else if (substr($result, 0, 5) == 'ERROR') {
            return [
                substr($result, 0, 5),
                substr($result, 5),
                '',
            ];
        } else {
            return [
                'UNKNOW',
                __('알수없는 오류'),
                '',
            ];
        }
    }

    /**
     * 고도빌 전송
     *
     * @param string $arrData 세금계산서 정보
     */
    protected function setGodoBillHttp($arrData)
    {
        $xxtea = new XXTEA();
        $xxtea->setKey('dusqhdtkdtmd');

        $requestPost = [
            'request' => base64_encode($xxtea->encrypt(serialize($arrData))),
            'id'      => $this->taxConf['godobillSiteId'],
            'api_key' => $this->taxConf['godobillApiKey'],
        ];


        $url = 'https://www.godobill.com:444/gate/add_taxinvoice.php';
        $result = HttpUtils::remotePost($url, $requestPost);


        return $result;
    }
}
