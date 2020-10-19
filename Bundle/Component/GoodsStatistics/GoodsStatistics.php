<?php

/**
 * 상품분석(Goods Statistics) Class
 *
 * @author    su
 * @version   1.0
 * @since     1.0
 * @copyright ⓒ 2016, NHN godo: Corp.
 */
namespace Bundle\Component\GoodsStatistics;

use Component\Database\DBTableField;
use Component\Mall\Mall;
use DateTime;

class GoodsStatistics
{
    protected $db;
    protected $orderGoodsPolicy;         // 상품통계 기본 설정 ( 주문 결제완료일 )

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
            $this->orderGoodsPolicy['statisticsDate'] = $submitDate->modify('-1 day');
        } else {
            $submitDate = new DateTime();
            $this->orderGoodsPolicy['statisticsDate'] = $submitDate->modify('-1 day');
        }

        // 실시간 처리 제한 시간
        $this->orderGoodsPolicy['realStatisticsHour'] = 2; // 통계가 2시간 이상 전의 시간이면 처리
    }

    /**
     * getOrderGoodsInfo
     * 주문 상품 정보 출력
     *
     * @param array       $orderGoods      mallSno / paymentDt
     * @param string      $orderGoodsField 출력할 필드명 (기본 null)
     * @param array       $arrBind         bind 처리 배열 (기본 null)
     *
     * @return array 주문 상품 정보
     *
     * @author su
     */
    public function getOrderGoodsInfo($orderGoods = null, $orderGoodsField = null, $arrBind = null)
    {
        if (is_null($arrBind)) {
            // $arrBind = array();
        }

        if (isset($orderGoods['mallSno'])) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND og.mallSno = ? ';
                $this->db->bind_param_push($arrBind, 'i', $orderGoods['mallSno']);
            } else {
                $this->db->strWhere = ' og.mallSno = ? ';
                $this->db->bind_param_push($arrBind, 'i', $orderGoods['mallSno']);
            }
        }
        if (isset($orderGoods['goodsType'])) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND og.goodsType = ? ';
                $this->db->bind_param_push($arrBind, 's', $orderGoods['goodsType']);
            } else {
                $this->db->strWhere = ' og.goodsType = ? ';
                $this->db->bind_param_push($arrBind, 's', $orderGoods['goodsType']);
            }
        }
        if (isset($orderGoods['paymentDt'])) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND DATE_FORMAT(og.paymentDt, "%Y%m%d") = ? ';
                $this->db->bind_param_push($arrBind, 's', $orderGoods['paymentDt']);
            } else {
                $this->db->strWhere = ' DATE_FORMAT(og.paymentDt, "%Y%m%d") = ? ';
                $this->db->bind_param_push($arrBind, 's', $orderGoods['paymentDt']);
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
        if ($orderGoodsField) {
            $this->db->strField = $orderGoodsField;
        }
        $this->db->strOrder = 'og.goodsNo asc, og.orderNo asc';
        $this->db->strJoin = 'LEFT JOIN ' . DB_ORDER . ' as o ON og.orderNo = o.orderNo';
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_GOODS . ' as og ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);

        return gd_htmlspecialchars_stripslashes($getData);
    }

    /**
     * getLinkMainStatisticsInfo
     * 주문된 메인 상품 분류 정보 출력
     *
     * @param array       $linkMainKey     themeSno / themeNm
     * @param string      $linkMainField   출력할 필드명 (기본 null)
     * @param array       $arrBind         bind 처리 배열 (기본 null)
     *
     * @return array 주문 상품 정보
     *
     * @author su
     */
    public function getLinkMainStatisticsInfo($linkMainKey = null, $linkMainField = null, $arrBind = null)
    {
        if (is_null($arrBind)) {
            // $arrBind = array();
        }

        if (empty($linkMainKey['themeSno']) === false) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND lms.themeSno = ? ';
                $this->db->bind_param_push($arrBind, 'i', $linkMainKey['themeSno']);
            } else {
                $this->db->strWhere = ' lms.themeSno = ? ';
                $this->db->bind_param_push($arrBind, 'i', $linkMainKey['themeSno']);
            }
        }
        if (empty($linkMainKey['themeNm']) === false) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND lms.themeNm = ? ';
                $this->db->bind_param_push($arrBind, 's', $linkMainKey['themeNm']);
            } else {
                $this->db->strWhere = ' lms.themeNm = ? ';
                $this->db->bind_param_push($arrBind, 's', $linkMainKey['themeNm']);
            }
        }
        if (empty($linkMainKey['themeDevice']) === false) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND lms.themeDevice = ? ';
                $this->db->bind_param_push($arrBind, 's', $linkMainKey['themeDevice']);
            } else {
                $this->db->strWhere = ' lms.themeDevice = ? ';
                $this->db->bind_param_push($arrBind, 's', $linkMainKey['themeDevice']);
            }
        }
        if ($linkMainField) {
            $this->db->strField = $linkMainField;
        } else {
            $this->db->strField = '*';
        }
        $this->db->strOrder = 'lms.themeSno desc, lms.themeNm desc';
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_LINK_MAIN_STATISTICS . ' as lms ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);

        return gd_htmlspecialchars_stripslashes($getData);
    }

    /**
     * @param $searchGoodsNm
     *
     * @return array
     */
    public function getGoodsInfo($searchGoodsNm)
    {
        $strSQL = 'SELECT goodsNo FROM ' . DB_GOODS . ' WHERE goodsNm LIKE ?';
        $this->db->bind_param_push($arrBind, 's', '%' . $searchGoodsNm . '%');
        $getData = $this->db->query_fetch($strSQL, $arrBind);
        unset($arrBind);

        $getData = array_column($getData, 'goodsNo');
        return $getData;
    }

    /**
     * getGoodsStatisticsInfo
     * 상품 통계정보 출력
     * 아래 getGoodsCategoryStatisticsInfo 와 동일한 데이터 구조이나 상품의 대표 카테고리 데이터가 저장됨
     * 수정시 아래 method 도 같이 수정 필요
     *
     * @param array       $goodsDay             goodsYMD / mallSno / goodsNo / cateCd / orderTypeFl / searchType
     * @param string      $goodsDayField        출력할 필드명 (기본 null)
     * @param array       $arrBind              bind 처리 배열 (기본 null)
     * @param bool|string $dataArray            return 값을 배열처리 (기본값 false)
     *
     * @return array 상품 정보
     *
     * @author su
     */
    public function getGoodsStatisticsInfo($goodsDay = null, $goodsDayField = null, $arrBind = null, $dataArray = false)
    {
        if (is_null($arrBind)) {
            // $arrBind = array();
        }
        if (is_array($goodsDay['goodsYMD'])) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND gs.goodsYMD BETWEEN ? AND ? ';
                $this->db->bind_param_push($arrBind, 'i', $goodsDay['goodsYMD'][0]);
                $this->db->bind_param_push($arrBind, 'i', $goodsDay['goodsYMD'][1]);
            } else {
                $this->db->strWhere = ' gs.goodsYMD BETWEEN ? AND ? ';
                $this->db->bind_param_push($arrBind, 'i', $goodsDay['goodsYMD'][0]);
                $this->db->bind_param_push($arrBind, 'i', $goodsDay['goodsYMD'][1]);
            }
        } else {
            if ($goodsDay['goodsYMD']) {
                if ($this->db->strWhere) {
                    $this->db->strWhere = $this->db->strWhere . ' AND gs.goodsYMD = ? ';
                    $this->db->bind_param_push($arrBind, 'i', $goodsDay['goodsYMD']);
                } else {
                    $this->db->strWhere = ' gs.goodsYMD = ? ';
                    $this->db->bind_param_push($arrBind, 'i', $goodsDay['goodsYMD']);
                }
            }
        }
        if (empty($goodsDay['mallSno']) === false) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND gs.mallSno = ? ';
                $this->db->bind_param_push($arrBind, 'i', $goodsDay['mallSno']);
            } else {
                $this->db->strWhere = ' gs.mallSno = ? ';
                $this->db->bind_param_push($arrBind, 'i', $goodsDay['mallSno']);
            }
        }
        if (empty($goodsDay['goodsNo']) === false) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND gs.goodsNo = ? ';
                $this->db->bind_param_push($arrBind, 'i', $goodsDay['goodsNo']);
            } else {
                $this->db->strWhere = ' gs.goodsNo = ? ';
                $this->db->bind_param_push($arrBind, 'i', $goodsDay['goodsNo']);
            }
        }
        if (empty($goodsDay['optionSno']) === false) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND gs.optionSno = ? ';
                $this->db->bind_param_push($arrBind, 'i', $goodsDay['optionSno']);
            } else {
                $this->db->strWhere = ' gs.optionSno = ? ';
                $this->db->bind_param_push($arrBind, 'i', $goodsDay['optionSno']);
            }
        }
        if (empty($goodsDay['goodsNm']) === false) {
            $goodsNoArr = $this->getGoodsInfo($goodsDay['goodsNm']);
            if (count($goodsNoArr) > 0) {
                foreach ($goodsNoArr as $val) {
                    $goodsNoWhere[] = 'gs.goodsNo = ?';
                    $this->db->bind_param_push($arrBind, 'i', $val);
                }
                if ($this->db->strWhere) {
                    $this->db->strWhere = $this->db->strWhere . ' AND (' . implode(' or ', $goodsNoWhere) . ')';
                } else {
                    $this->db->strWhere = ' (' . implode(' or ', $goodsNoWhere) . ')';
                }
            }
        }
        if (empty($goodsDay['orderTypeFl']) === false) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND gs.orderTypeFl = ? ';
                $this->db->bind_param_push($arrBind, 's', $goodsDay['orderTypeFl']);
            } else {
                $this->db->strWhere = ' gs.orderTypeFl = ? ';
                $this->db->bind_param_push($arrBind, 's', $goodsDay['orderTypeFl']);
            }
        }
        if (empty($goodsDay['cateCd']) === false) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND gs.cateCd like ?';
                $this->db->bind_param_push($arrBind, 's', $goodsDay['cateCd'].'%');
            } else {
                $this->db->strWhere = ' gs.cateCd like ?';
                $this->db->bind_param_push($arrBind, 's', $goodsDay['cateCd'].'%');
            }
        }
        if (empty($goodsDay['cateCdEqual']) === false) {
            if ($goodsDay['cateCdEqual'] == 'noCate') {
                $goodsDay['cateCdEqual'] = '';
            }
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND gs.cateCd = ? ';
                $this->db->bind_param_push($arrBind, 's', $goodsDay['cateCdEqual']);
            } else {
                $this->db->strWhere = ' gs.cateCd = ? ';
                $this->db->bind_param_push($arrBind, 's', $goodsDay['cateCdEqual']);
            }
        }
        if (empty($goodsDay['noCategoryFl']) === false) {
            if ($goodsDay['noCategoryFl'] === 'n') {
                if ($this->db->strWhere) {
                    $this->db->strWhere = $this->db->strWhere . ' AND gs.cateCd != "" ';
                } else {
                    $this->db->strWhere = ' gs.cateCd != "" ';
                }
            }
        }
        if (empty($goodsDay['sort']) === false) {
            $this->db->strOrder = $goodsDay['sort'];
        }
        if (is_array($goodsDay['limit'])) {
            $this->db->strLimit = '?, ?';
            $this->db->bind_param_push($arrBind, 'i', $goodsDay['limit'][0]);
            $this->db->bind_param_push($arrBind, 'i', $goodsDay['limit'][1]);
        }

        if ($goodsDayField) {
            $this->db->strField = $goodsDayField;
        }
        if ($goodsDay['searchType'] == 'cate') {
            $this->db->strJoin = 'LEFT JOIN ' . DB_CATEGORY_GOODS . ' as cg ON gs.cateCd = cg.cateCd';
        }
        //        else if ($goodsDay['searchType'] == 'goods') {
        //            $this->db->strJoin = 'LEFT JOIN ' . DB_GOODS . ' as g ON gs.goodsNo = g.goodsNo LEFT JOIN ' . DB_GOODS_IMAGE . ' as gi ON gs.goodsNo = gi.goodsNo AND gi.imageKind = "list"';
        //        }
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_GOODS_STATISTICS . ' as gs ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);
        unset($arrBind);

        foreach ($getData as $key => $val) {
            $arrBind = [];
            $this->db->strField = 'g.goodsNm, g.imageStorage, g.imagePath, gi.imageName';
            $this->db->strWhere = ' g.goodsNo = ? ';
            $this->db->bind_param_push($arrBind, 'i', $val['goodsNo']);
            $this->db->strJoin = 'LEFT JOIN ' . DB_GOODS_IMAGE . ' as gi ON g.goodsNo = gi.goodsNo AND gi.imageKind = "list"';
            $query = $this->db->query_complete();
            $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_GOODS . ' as g ' . implode(' ', $query);
            $getGoodsData = $this->db->query_fetch($strSQL, $arrBind, false);
            if (count($getGoodsData) < 1) {
                $getData[$key]['goodsNo'] = '-';
                $getData[$key]['goodsNm'] = '삭제상품';
                $getData[$key]['imageStorage'] = 'local';
                $getData[$key]['imagePath'] = '';
                $getData[$key]['imageName'] = '';
            } else {
                $getData[$key]['goodsNm'] = $getGoodsData['goodsNm'];
                $getData[$key]['imageStorage'] = $getGoodsData['imageStorage'];
                $getData[$key]['imagePath'] = $getGoodsData['imagePath'];
                $getData[$key]['imageName'] = $getGoodsData['imageName'];
            }
            unset($arrBind);
        }

        return $getData;
    }

    /**
     * getGoodsOptionStatisticsInfo
     * 상품 통계정보 출력 (옵션별)
     * 아래 getGoodsCategoryStatisticsInfo 와 동일한 데이터 구조이나 상품의 대표 카테고리 데이터가 저장됨
     * 수정시 아래 method 도 같이 수정 필요
     *
     * @param array       $goodsDay             goodsYMD / mallSno / goodsNo / cateCd / orderTypeFl / searchType
     * @param string      $goodsDayField        출력할 필드명 (기본 null)
     * @param array       $arrBind              bind 처리 배열 (기본 null)
     * @param bool|string $dataArray            return 값을 배열처리 (기본값 false)
     *
     * @return array 상품 정보
     *
     * @author su
     */
    public function getGoodsOptionStatisticsInfo($goodsDay = null, $goodsDayField = null, $arrBind = null, $dataArray = false)
    {
        if (is_null($arrBind)) {
            // $arrBind = array();
        }
        if (is_array($goodsDay['goodsYMD'])) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND gs.goodsYMD BETWEEN ? AND ? ';
                $this->db->bind_param_push($arrBind, 'i', $goodsDay['goodsYMD'][0]);
                $this->db->bind_param_push($arrBind, 'i', $goodsDay['goodsYMD'][1]);
            } else {
                $this->db->strWhere = ' gs.goodsYMD BETWEEN ? AND ? ';
                $this->db->bind_param_push($arrBind, 'i', $goodsDay['goodsYMD'][0]);
                $this->db->bind_param_push($arrBind, 'i', $goodsDay['goodsYMD'][1]);
            }
        } else {
            if ($goodsDay['goodsYMD']) {
                if ($this->db->strWhere) {
                    $this->db->strWhere = $this->db->strWhere . ' AND gs.goodsYMD = ? ';
                    $this->db->bind_param_push($arrBind, 'i', $goodsDay['goodsYMD']);
                } else {
                    $this->db->strWhere = ' gs.goodsYMD = ? ';
                    $this->db->bind_param_push($arrBind, 'i', $goodsDay['goodsYMD']);
                }
            }
        }
        if (empty($goodsDay['mallSno']) === false) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND gs.mallSno = ? ';
                $this->db->bind_param_push($arrBind, 'i', $goodsDay['mallSno']);
            } else {
                $this->db->strWhere = ' gs.mallSno = ? ';
                $this->db->bind_param_push($arrBind, 'i', $goodsDay['mallSno']);
            }
        }
        if (empty($goodsDay['goodsNo']) === false) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND gs.goodsNo = ? ';
                $this->db->bind_param_push($arrBind, 'i', $goodsDay['goodsNo']);
            } else {
                $this->db->strWhere = ' gs.goodsNo = ? ';
                $this->db->bind_param_push($arrBind, 'i', $goodsDay['goodsNo']);
            }
        }
        if (empty($goodsDay['optionSno']) === false) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND gs.optionSno = ? ';
                $this->db->bind_param_push($arrBind, 'i', $goodsDay['optionSno']);
            } else {
                $this->db->strWhere = ' gs.optionSno = ? ';
                $this->db->bind_param_push($arrBind, 'i', $goodsDay['optionSno']);
            }
        }
        if (empty($goodsDay['goodsNm']) === false) {
            $goodsNoArr = $this->getGoodsInfo($goodsDay['goodsNm']);
            if (count($goodsNoArr) > 0) {
                foreach ($goodsNoArr as $val) {
                    $goodsNoWhere[] = 'gs.goodsNo = ?';
                    $this->db->bind_param_push($arrBind, 'i', $val);
                }
                if ($this->db->strWhere) {
                    $this->db->strWhere = $this->db->strWhere . ' AND (' . implode(' or ', $goodsNoWhere) . ')';
                } else {
                    $this->db->strWhere = ' (' . implode(' or ', $goodsNoWhere) . ')';
                }
            }
        }
        if (empty($goodsDay['orderTypeFl']) === false) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND gs.orderTypeFl = ? ';
                $this->db->bind_param_push($arrBind, 's', $goodsDay['orderTypeFl']);
            } else {
                $this->db->strWhere = ' gs.orderTypeFl = ? ';
                $this->db->bind_param_push($arrBind, 's', $goodsDay['orderTypeFl']);
            }
        }
        if (empty($goodsDay['cateCd']) === false) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND gs.cateCd like ?';
                $this->db->bind_param_push($arrBind, 's', $goodsDay['cateCd'].'%');
            } else {
                $this->db->strWhere = ' gs.cateCd like ?';
                $this->db->bind_param_push($arrBind, 's', $goodsDay['cateCd'].'%');
            }
        }
        if (empty($goodsDay['cateCdEqual']) === false) {
            if ($goodsDay['cateCdEqual'] == 'noCate') {
                $goodsDay['cateCdEqual'] = '';
            }
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND gs.cateCd = ? ';
                $this->db->bind_param_push($arrBind, 's', $goodsDay['cateCdEqual']);
            } else {
                $this->db->strWhere = ' gs.cateCd = ? ';
                $this->db->bind_param_push($arrBind, 's', $goodsDay['cateCdEqual']);
            }
        }
        if (empty($goodsDay['noCategoryFl']) === false) {
            if ($goodsDay['noCategoryFl'] === 'n') {
                if ($this->db->strWhere) {
                    $this->db->strWhere = $this->db->strWhere . ' AND gs.cateCd != "" ';
                } else {
                    $this->db->strWhere = ' gs.cateCd != "" ';
                }
            }
        }
        if (empty($goodsDay['sort']) === false) {
            $this->db->strOrder = $goodsDay['sort'];
        }
        if (is_array($goodsDay['limit'])) {
            $this->db->strLimit = '?, ?';
            $this->db->bind_param_push($arrBind, 'i', $goodsDay['limit'][0]);
            $this->db->bind_param_push($arrBind, 'i', $goodsDay['limit'][1]);
        }

        if ($goodsDayField) {
            $this->db->strField = $goodsDayField;
        }
        if ($goodsDay['searchType'] == 'cate') {
            $this->db->strJoin = 'LEFT JOIN ' . DB_CATEGORY_GOODS . ' as cg ON gs.cateCd = cg.cateCd';
        }
        //        else if ($goodsDay['searchType'] == 'goods') {
        //            $this->db->strJoin = 'LEFT JOIN ' . DB_GOODS . ' as g ON gs.goodsNo = g.goodsNo LEFT JOIN ' . DB_GOODS_IMAGE . ' as gi ON gs.goodsNo = gi.goodsNo AND gi.imageKind = "list"';
        //        }
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_GOODS_OPTION_STATISTICS . ' as gs ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);
        unset($arrBind);

        foreach ($getData as $key => $val) {
            $arrBind = [];
            $this->db->strField = 'g.goodsNm, g.imageStorage, g.imagePath, gi.imageName';
            $this->db->strWhere = ' g.goodsNo = ? ';
            $this->db->bind_param_push($arrBind, 'i', $val['goodsNo']);
            $this->db->strJoin = 'LEFT JOIN ' . DB_GOODS_IMAGE . ' as gi ON g.goodsNo = gi.goodsNo AND gi.imageKind = "list"';
            $query = $this->db->query_complete();
            $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_GOODS . ' as g ' . implode(' ', $query);
            $getGoodsData = $this->db->query_fetch($strSQL, $arrBind, false);
            if (count($getGoodsData) < 1) {
                $getData[$key]['goodsNo'] = '-';
                $getData[$key]['goodsNm'] = '삭제상품';
                $getData[$key]['imageStorage'] = 'local';
                $getData[$key]['imagePath'] = '';
                $getData[$key]['imageName'] = '';
            } else {
                $getData[$key]['goodsNm'] = $getGoodsData['goodsNm'];
                $getData[$key]['imageStorage'] = $getGoodsData['imageStorage'];
                $getData[$key]['imagePath'] = $getGoodsData['imagePath'];
                $getData[$key]['imageName'] = $getGoodsData['imageName'];
            }
            unset($arrBind);
        }

        return $getData;
    }

    /**
     * getGoodsCategoryStatisticsInfo
     * 상품 카테고리 통계정보 출력
     * 위 getGoodsStatisticsInfo 와 동일한 데이터 구조이나 상품의 전체 카테고리 데이터가 저장됨
     * 수정시 위 method 도 같이 수정 필요
     *
     * @param array       $goodsDay             goodsYMD / mallSno / goodsNo / cateCd / orderTypeFl / searchType
     * @param string      $goodsDayField        출력할 필드명 (기본 null)
     * @param array       $arrBind              bind 처리 배열 (기본 null)
     * @param bool|string $dataArray            return 값을 배열처리 (기본값 false)
     *
     * @return array 상품 정보
     *
     * @author su
     */
    public function getGoodsCategoryStatisticsInfo($goodsDay = null, $goodsDayField = null, $arrBind = null, $dataArray = false)
    {
        if (is_null($arrBind)) {
            // $arrBind = array();
        }
        if (is_array($goodsDay['goodsYMD'])) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND gcs.goodsYMD BETWEEN ? AND ? ';
                $this->db->bind_param_push($arrBind, 'i', $goodsDay['goodsYMD'][0]);
                $this->db->bind_param_push($arrBind, 'i', $goodsDay['goodsYMD'][1]);
            } else {
                $this->db->strWhere = ' gcs.goodsYMD BETWEEN ? AND ? ';
                $this->db->bind_param_push($arrBind, 'i', $goodsDay['goodsYMD'][0]);
                $this->db->bind_param_push($arrBind, 'i', $goodsDay['goodsYMD'][1]);
            }
        } else {
            if ($goodsDay['goodsYMD']) {
                if ($this->db->strWhere) {
                    $this->db->strWhere = $this->db->strWhere . ' AND gcs.goodsYMD = ? ';
                    $this->db->bind_param_push($arrBind, 'i', $goodsDay['goodsYMD']);
                } else {
                    $this->db->strWhere = ' gcs.goodsYMD = ? ';
                    $this->db->bind_param_push($arrBind, 'i', $goodsDay['goodsYMD']);
                }
            }
        }
        if (empty($goodsDay['mallSno']) === false) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND gcs.mallSno = ? ';
                $this->db->bind_param_push($arrBind, 'i', $goodsDay['mallSno']);
            } else {
                $this->db->strWhere = ' gcs.mallSno = ? ';
                $this->db->bind_param_push($arrBind, 'i', $goodsDay['mallSno']);
            }
        }
        if (empty($goodsDay['goodsNo']) === false) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND gcs.goodsNo = ? ';
                $this->db->bind_param_push($arrBind, 'i', $goodsDay['goodsNo']);
            } else {
                $this->db->strWhere = ' gcs.goodsNo = ? ';
                $this->db->bind_param_push($arrBind, 'i', $goodsDay['goodsNo']);
            }
        }
        if (empty($goodsDay['optionSno']) === false) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND gcs.optionSno = ? ';
                $this->db->bind_param_push($arrBind, 'i', $goodsDay['optionSno']);
            } else {
                $this->db->strWhere = ' gcs.optionSno = ? ';
                $this->db->bind_param_push($arrBind, 'i', $goodsDay['optionSno']);
            }
        }
        if (empty($goodsDay['goodsNm']) === false) {
            $goodsNoArr = $this->getGoodsInfo($goodsDay['goodsNm']);
            if (count($goodsNoArr) > 0) {
                foreach ($goodsNoArr as $val) {
                    $goodsNoWhere[] = 'gcs.goodsNo = ?';
                    $this->db->bind_param_push($arrBind, 'i', $val);
                }
                if ($this->db->strWhere) {
                    $this->db->strWhere = $this->db->strWhere . ' AND (' . implode(' or ', $goodsNoWhere) . ')';
                } else {
                    $this->db->strWhere = ' (' . implode(' or ', $goodsNoWhere) . ')';
                }
            }
        }
        if (empty($goodsDay['orderTypeFl']) === false) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND gcs.orderTypeFl = ? ';
                $this->db->bind_param_push($arrBind, 's', $goodsDay['orderTypeFl']);
            } else {
                $this->db->strWhere = ' gcs.orderTypeFl = ? ';
                $this->db->bind_param_push($arrBind, 's', $goodsDay['orderTypeFl']);
            }
        }
        if (empty($goodsDay['cateCd']) === false) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND gcs.cateCd like ? ';
                $this->db->bind_param_push($arrBind, 's', $goodsDay['cateCd'].'%');
            } else {
                $this->db->strWhere = ' gcs.cateCd like ?';
                $this->db->bind_param_push($arrBind, 's', $goodsDay['cateCd'].'%');
            }
        }
        if (empty($goodsDay['cateCdEqual']) === false) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND gcs.cateCd = ? ';
                $this->db->bind_param_push($arrBind, 's', $goodsDay['cateCdEqual']);
            } else {
                $this->db->strWhere = ' gcs.cateCd = ? ';
                $this->db->bind_param_push($arrBind, 's', $goodsDay['cateCdEqual']);
            }
        }
        if (empty($goodsDay['noCategoryFl']) === false) {
            if ($goodsDay['noCategoryFl'] === 'n') {
                if ($this->db->strWhere) {
                    $this->db->strWhere = $this->db->strWhere . ' AND gcs.cateCd != "" ';
                } else {
                    $this->db->strWhere = ' gcs.cateCd != "" ';
                }
            }
        }
        if (empty($goodsDay['sort']) === false) {
            $this->db->strOrder = $goodsDay['sort'];
        }
        if (is_array($goodsDay['limit'])) {
            $this->db->strLimit = '?, ?';
            $this->db->bind_param_push($arrBind, 'i', $goodsDay['limit'][0]);
            $this->db->bind_param_push($arrBind, 'i', $goodsDay['limit'][1]);
        }

        if ($goodsDayField) {
            $this->db->strField = $goodsDayField;
        }
        if ($goodsDay['searchType'] == 'cate') {
            $this->db->strJoin = 'LEFT JOIN ' . DB_CATEGORY_GOODS . ' as cg ON gcs.cateCd = cg.cateCd';
        }
        //        else if ($goodsDay['searchType'] == 'goods') {
        //            $this->db->strJoin = 'LEFT JOIN ' . DB_GOODS . ' as g ON gcs.goodsNo = g.goodsNo LEFT JOIN ' . DB_GOODS_IMAGE . ' as gi ON gcs.goodsNo = gi.goodsNo AND gi.imageKind = "list"';
        //        }
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_GOODS_CATEGORY_STATISTICS . ' as gcs ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);
        unset($arrBind);

        foreach ($getData as $key => $val) {
            $arrBind = [];
            $this->db->strField = 'g.goodsNm, g.imageStorage, g.imagePath, gi.imageName';
            $this->db->strWhere = ' g.goodsNo = ? ';
            $this->db->bind_param_push($arrBind, 'i', $val['goodsNo']);
            $this->db->strJoin = 'LEFT JOIN ' . DB_GOODS_IMAGE . ' as gi ON g.goodsNo = gi.goodsNo AND gi.imageKind = "list"';
            $query = $this->db->query_complete();
            $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_GOODS . ' as g ' . implode(' ', $query);
            $getGoodsData = $this->db->query_fetch($strSQL, $arrBind, false);
            if (count($getGoodsData) < 1) {
                $getData[$key]['goodsNo'] = '-';
                $getData[$key]['goodsNm'] = '삭제상품';
                $getData[$key]['imageStorage'] = 'local';
                $getData[$key]['imagePath'] = '';
                $getData[$key]['imageName'] = '';
            } else {
                $getData[$key]['goodsNm'] = $getGoodsData['goodsNm'];
                $getData[$key]['imageStorage'] = $getGoodsData['imageStorage'];
                $getData[$key]['imagePath'] = $getGoodsData['imagePath'];
                $getData[$key]['imageName'] = $getGoodsData['imageName'];
            }
            unset($arrBind);
        }

        return $getData;
    }

    /**
     * getGoodsOptionCategoryStatisticsInfo
     * 상품 카테고리 통계정보 출력
     * 위 getGoodsStatisticsInfo 와 동일한 데이터 구조이나 상품의 전체 카테고리 데이터가 저장됨
     * 수정시 위 method 도 같이 수정 필요
     *
     * @param array       $goodsDay             goodsYMD / mallSno / goodsNo / cateCd / orderTypeFl / searchType
     * @param string      $goodsDayField        출력할 필드명 (기본 null)
     * @param array       $arrBind              bind 처리 배열 (기본 null)
     * @param bool|string $dataArray            return 값을 배열처리 (기본값 false)
     *
     * @return array 상품 정보
     *
     * @author su
     */
    public function getGoodsOptionCategoryStatisticsInfo($goodsDay = null, $goodsDayField = null, $arrBind = null, $dataArray = false)
    {
        if (is_null($arrBind)) {
            // $arrBind = array();
        }
        if (is_array($goodsDay['goodsYMD'])) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND gcs.goodsYMD BETWEEN ? AND ? ';
                $this->db->bind_param_push($arrBind, 'i', $goodsDay['goodsYMD'][0]);
                $this->db->bind_param_push($arrBind, 'i', $goodsDay['goodsYMD'][1]);
            } else {
                $this->db->strWhere = ' gcs.goodsYMD BETWEEN ? AND ? ';
                $this->db->bind_param_push($arrBind, 'i', $goodsDay['goodsYMD'][0]);
                $this->db->bind_param_push($arrBind, 'i', $goodsDay['goodsYMD'][1]);
            }
        } else {
            if ($goodsDay['goodsYMD']) {
                if ($this->db->strWhere) {
                    $this->db->strWhere = $this->db->strWhere . ' AND gcs.goodsYMD = ? ';
                    $this->db->bind_param_push($arrBind, 'i', $goodsDay['goodsYMD']);
                } else {
                    $this->db->strWhere = ' gcs.goodsYMD = ? ';
                    $this->db->bind_param_push($arrBind, 'i', $goodsDay['goodsYMD']);
                }
            }
        }
        if (empty($goodsDay['mallSno']) === false) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND gcs.mallSno = ? ';
                $this->db->bind_param_push($arrBind, 'i', $goodsDay['mallSno']);
            } else {
                $this->db->strWhere = ' gcs.mallSno = ? ';
                $this->db->bind_param_push($arrBind, 'i', $goodsDay['mallSno']);
            }
        }
        if (empty($goodsDay['goodsNo']) === false) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND gcs.goodsNo = ? ';
                $this->db->bind_param_push($arrBind, 'i', $goodsDay['goodsNo']);
            } else {
                $this->db->strWhere = ' gcs.goodsNo = ? ';
                $this->db->bind_param_push($arrBind, 'i', $goodsDay['goodsNo']);
            }
        }
        if (empty($goodsDay['optionSno']) === false) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND gcs.optionSno = ? ';
                $this->db->bind_param_push($arrBind, 'i', $goodsDay['optionSno']);
            } else {
                $this->db->strWhere = ' gcs.optionSno = ? ';
                $this->db->bind_param_push($arrBind, 'i', $goodsDay['optionSno']);
            }
        }
        if (empty($goodsDay['goodsNm']) === false) {
            $goodsNoArr = $this->getGoodsInfo($goodsDay['goodsNm']);
            if (count($goodsNoArr) > 0) {
                foreach ($goodsNoArr as $val) {
                    $goodsNoWhere[] = 'gcs.goodsNo = ?';
                    $this->db->bind_param_push($arrBind, 'i', $val);
                }
                if ($this->db->strWhere) {
                    $this->db->strWhere = $this->db->strWhere . ' AND (' . implode(' or ', $goodsNoWhere) . ')';
                } else {
                    $this->db->strWhere = ' (' . implode(' or ', $goodsNoWhere) . ')';
                }
            }
        }
        if (empty($goodsDay['orderTypeFl']) === false) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND gcs.orderTypeFl = ? ';
                $this->db->bind_param_push($arrBind, 's', $goodsDay['orderTypeFl']);
            } else {
                $this->db->strWhere = ' gcs.orderTypeFl = ? ';
                $this->db->bind_param_push($arrBind, 's', $goodsDay['orderTypeFl']);
            }
        }
        if (empty($goodsDay['cateCd']) === false) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND gcs.cateCd like ? ';
                $this->db->bind_param_push($arrBind, 's', $goodsDay['cateCd'].'%');
            } else {
                $this->db->strWhere = ' gcs.cateCd like ?';
                $this->db->bind_param_push($arrBind, 's', $goodsDay['cateCd'].'%');
            }
        }
        if (empty($goodsDay['cateCdEqual']) === false) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND gcs.cateCd = ? ';
                $this->db->bind_param_push($arrBind, 's', $goodsDay['cateCdEqual']);
            } else {
                $this->db->strWhere = ' gcs.cateCd = ? ';
                $this->db->bind_param_push($arrBind, 's', $goodsDay['cateCdEqual']);
            }
        }
        if (empty($goodsDay['noCategoryFl']) === false) {
            if ($goodsDay['noCategoryFl'] === 'n') {
                if ($this->db->strWhere) {
                    $this->db->strWhere = $this->db->strWhere . ' AND gcs.cateCd != "" ';
                } else {
                    $this->db->strWhere = ' gcs.cateCd != "" ';
                }
            }
        }
        if (empty($goodsDay['sort']) === false) {
            $this->db->strOrder = $goodsDay['sort'];
        }
        if (is_array($goodsDay['limit'])) {
            $this->db->strLimit = '?, ?';
            $this->db->bind_param_push($arrBind, 'i', $goodsDay['limit'][0]);
            $this->db->bind_param_push($arrBind, 'i', $goodsDay['limit'][1]);
        }

        if ($goodsDayField) {
            $this->db->strField = $goodsDayField;
        }
        if ($goodsDay['searchType'] == 'cate') {
            $this->db->strJoin = 'LEFT JOIN ' . DB_CATEGORY_GOODS . ' as cg ON gcs.cateCd = cg.cateCd';
        }
        //        else if ($goodsDay['searchType'] == 'goods') {
        //            $this->db->strJoin = 'LEFT JOIN ' . DB_GOODS . ' as g ON gcs.goodsNo = g.goodsNo LEFT JOIN ' . DB_GOODS_IMAGE . ' as gi ON gcs.goodsNo = gi.goodsNo AND gi.imageKind = "list"';
        //        }
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_GOODS_OPTION_CATEGORY_STATISTICS . ' as gcs ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);
        unset($arrBind);

        foreach ($getData as $key => $val) {
            $arrBind = [];
            $this->db->strField = 'g.goodsNm, g.imageStorage, g.imagePath, gi.imageName';
            $this->db->strWhere = ' g.goodsNo = ? ';
            $this->db->bind_param_push($arrBind, 'i', $val['goodsNo']);
            $this->db->strJoin = 'LEFT JOIN ' . DB_GOODS_IMAGE . ' as gi ON g.goodsNo = gi.goodsNo AND gi.imageKind = "list"';
            $query = $this->db->query_complete();
            $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_GOODS . ' as g ' . implode(' ', $query);
            $getGoodsData = $this->db->query_fetch($strSQL, $arrBind, false);
            if (count($getGoodsData) < 1) {
                $getData[$key]['goodsNo'] = '-';
                $getData[$key]['goodsNm'] = '삭제상품';
                $getData[$key]['imageStorage'] = 'local';
                $getData[$key]['imagePath'] = '';
                $getData[$key]['imageName'] = '';
            } else {
                $getData[$key]['goodsNm'] = $getGoodsData['goodsNm'];
                $getData[$key]['imageStorage'] = $getGoodsData['imageStorage'];
                $getData[$key]['imagePath'] = $getGoodsData['imagePath'];
                $getData[$key]['imageName'] = $getGoodsData['imageName'];
            }
            unset($arrBind);
        }

        return $getData;
    }

    /**
     * getGoodsMainStatisticsInfo
     * 상품 메인 통계정보 출력
     *
     * @param array       $goodsDay             goodsYMD / mallSno / goodsNo / cateCd / orderTypeFl / searchType
     * @param string      $goodsDayField        출력할 필드명 (기본 null)
     * @param array       $arrBind              bind 처리 배열 (기본 null)
     *
     * @return array 상품 정보
     *
     * @author su
     */
    public function getGoodsMainStatisticsInfo($goodsDay = null, $goodsDayField = null, $arrBind = null)
    {
        if (is_null($arrBind)) {
            // $arrBind = array();
        }
        if (is_array($goodsDay['goodsYMD'])) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND gms.goodsYMD BETWEEN ? AND ? ';
                $this->db->bind_param_push($arrBind, 'i', $goodsDay['goodsYMD'][0]);
                $this->db->bind_param_push($arrBind, 'i', $goodsDay['goodsYMD'][1]);
            } else {
                $this->db->strWhere = ' gms.goodsYMD BETWEEN ? AND ? ';
                $this->db->bind_param_push($arrBind, 'i', $goodsDay['goodsYMD'][0]);
                $this->db->bind_param_push($arrBind, 'i', $goodsDay['goodsYMD'][1]);
            }
        } else {
            if ($goodsDay['goodsYMD']) {
                if ($this->db->strWhere) {
                    $this->db->strWhere = $this->db->strWhere . ' AND gms.goodsYMD = ? ';
                    $this->db->bind_param_push($arrBind, 'i', $goodsDay['goodsYMD']);
                } else {
                    $this->db->strWhere = ' gms.goodsYMD = ? ';
                    $this->db->bind_param_push($arrBind, 'i', $goodsDay['goodsYMD']);
                }
            }
        }
        if (empty($goodsDay['mallSno']) === false) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND gms.mallSno = ? ';
                $this->db->bind_param_push($arrBind, 'i', $goodsDay['mallSno']);
            } else {
                $this->db->strWhere = ' gms.mallSno = ? ';
                $this->db->bind_param_push($arrBind, 'i', $goodsDay['mallSno']);
            }
        }
        if (empty($goodsDay['goodsNo']) === false) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND gms.goodsNo = ? ';
                $this->db->bind_param_push($arrBind, 'i', $goodsDay['goodsNo']);
            } else {
                $this->db->strWhere = ' gms.goodsNo = ? ';
                $this->db->bind_param_push($arrBind, 'i', $goodsDay['goodsNo']);
            }
        }
        if (empty($goodsDay['optionSno']) === false) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND gms.optionSno = ? ';
                $this->db->bind_param_push($arrBind, 'i', $goodsDay['optionSno']);
            } else {
                $this->db->strWhere = ' gms.optionSno = ? ';
                $this->db->bind_param_push($arrBind, 'i', $goodsDay['optionSno']);
            }
        }
        if (empty($goodsDay['goodsNm']) === false) {
            $goodsNoArr = $this->getGoodsInfo($goodsDay['goodsNm']);
            if (count($goodsNoArr) > 0) {
                foreach ($goodsNoArr as $val) {
                    $goodsNoWhere[] = 'gms.goodsNo = ?';
                    $this->db->bind_param_push($arrBind, 'i', $val);
                }
                if ($this->db->strWhere) {
                    $this->db->strWhere = $this->db->strWhere . ' AND (' . implode(' or ', $goodsNoWhere) . ')';
                } else {
                    $this->db->strWhere = ' (' . implode(' or ', $goodsNoWhere) . ')';
                }
            }
        }
        if (empty($goodsDay['orderTypeFl']) === false) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND gms.orderTypeFl = ? ';
                $this->db->bind_param_push($arrBind, 's', $goodsDay['orderTypeFl']);
            } else {
                $this->db->strWhere = ' gms.orderTypeFl = ? ';
                $this->db->bind_param_push($arrBind, 's', $goodsDay['orderTypeFl']);
            }
        }
        if (empty($goodsDay['cateCd']) === false) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND gms.cateCd like ? ';
                $this->db->bind_param_push($arrBind, 's', $goodsDay['cateCd'].'%');
            } else {
                $this->db->strWhere = ' gms.cateCd like ?';
                $this->db->bind_param_push($arrBind, 's', $goodsDay['cateCd'].'%');
            }
        }
        if (empty($goodsDay['cateCdEqual']) === false) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND gms.cateCd = ? ';
                $this->db->bind_param_push($arrBind, 's', $goodsDay['cateCdEqual']);
            } else {
                $this->db->strWhere = ' gms.cateCd = ? ';
                $this->db->bind_param_push($arrBind, 's', $goodsDay['cateCdEqual']);
            }
        }
        if (empty($goodsDay['themeDevice']) === false) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND gms.themeDevice = ? ';
                $this->db->bind_param_push($arrBind, 's', $goodsDay['themeDevice']);
            } else {
                $this->db->strWhere = ' gms.themeDevice = ? ';
                $this->db->bind_param_push($arrBind, 's', $goodsDay['themeDevice']);
            }
        }
        if (empty($goodsDay['themeNm']) === false) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND gms.themeNm = ? ';
                $this->db->bind_param_push($arrBind, 's', $goodsDay['themeNm']);
            } else {
                $this->db->strWhere = ' gms.themeNm = ? ';
                $this->db->bind_param_push($arrBind, 's', $goodsDay['themeNm']);
            }
        }
        if (empty($goodsDay['themeSno']) === false) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND gms.themeSno = ? ';
                $this->db->bind_param_push($arrBind, 'i', $goodsDay['themeSno']);
            } else {
                $this->db->strWhere = ' gms.themeSno = ? ';
                $this->db->bind_param_push($arrBind, 'i', $goodsDay['themeSno']);
            }
        }
        if (empty($goodsDay['sort']) === false) {
            $this->db->strOrder = $goodsDay['sort'];
        }
        if (is_array($goodsDay['limit'])) {
            $this->db->strLimit = '?, ?';
            $this->db->bind_param_push($arrBind, 'i', $goodsDay['limit'][0]);
            $this->db->bind_param_push($arrBind, 'i', $goodsDay['limit'][1]);
        }

        if ($goodsDayField) {
            $this->db->strField = $goodsDayField;
        }
        if ($goodsDay['searchType'] == 'cate') {
            $this->db->strJoin = 'LEFT JOIN ' . DB_CATEGORY_GOODS . ' as cg ON gms.cateCd = cg.cateCd';
        }
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_GOODS_MAIN_STATISTICS . ' as gms ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);
        unset($arrBind);

        foreach ($getData as $key => $val) {
            $arrBind = [];
            $this->db->strField = 'g.goodsNm, g.imageStorage, g.imagePath, gi.imageName';
            $this->db->strWhere = ' g.goodsNo = ? ';
            $this->db->bind_param_push($arrBind, 'i', $val['goodsNo']);
            $this->db->strJoin = 'LEFT JOIN ' . DB_GOODS_IMAGE . ' as gi ON g.goodsNo = gi.goodsNo AND gi.imageKind = "list"';
            $query = $this->db->query_complete();
            $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_GOODS . ' as g ' . implode(' ', $query);
            $getGoodsData = $this->db->query_fetch($strSQL, $arrBind, false);
            if (count($getGoodsData) < 1) {
                $getData[$key]['goodsNo'] = '-';
                $getData[$key]['goodsNm'] = '삭제상품';
                $getData[$key]['imageStorage'] = 'local';
                $getData[$key]['imagePath'] = '';
                $getData[$key]['imageName'] = '';
            } else {
                $getData[$key]['goodsNm'] = $getGoodsData['goodsNm'];
                $getData[$key]['imageStorage'] = $getGoodsData['imageStorage'];
                $getData[$key]['imagePath'] = $getGoodsData['imagePath'];
                $getData[$key]['imageName'] = $getGoodsData['imageName'];
            }
            unset($arrBind);
        }

        return $getData;
    }

    /**
     * getSearchWordStatisticsInfo
     * 검색어 통계정보 출력
     *
     * @param array       $searchWord             regDt / mallSno / goodsNo / cateCd / orderTypeFl / searchType
     * @param string      $searchWordField        출력할 필드명 (기본 null)
     * @param array       $arrBind                bind 처리 배열 (기본 null)
     * @param bool|string $dataArray              return 값을 배열처리 (기본값 false)
     *
     * @return array 상품 정보
     *
     * @author su
     */
    public function getSearchWordStatisticsInfo($searchWord = null, $searchWordField = null, $arrBind = null, $dataArray = false, $isGenerator = false)
    {
        if (is_null($arrBind)) {
            // $arrBind = array();
        }
        if (is_array($searchWord['regDt'])) {
            $startDate = new DateTime($searchWord['regDt'][0]);
            $endDate = new DateTime($searchWord['regDt'][1]);
            $searchDate[0] = $startDate->format('Y-m-d 00:00:00');
            $searchDate[1] = $endDate->format('Y-m-d 23:59:59');
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND sws.regDt BETWEEN ? AND ? ';
                $this->db->bind_param_push($arrBind, 's', $searchDate[0]);
                $this->db->bind_param_push($arrBind, 's', $searchDate[1]);
            } else {
                $this->db->strWhere = ' sws.regDt BETWEEN ? AND ? ';
                $this->db->bind_param_push($arrBind, 's', $searchDate[0]);
                $this->db->bind_param_push($arrBind, 's', $searchDate[1]);
            }
        } else {
            if ($searchWord['regDt']) {
                $startDate = new DateTime($searchWord['regDt'][0]);
                $searchDate[0] = $startDate->format('Y-m-d 00:00:00');
                $searchDate[1] = $startDate->format('Y-m-d 23:59:59');
                if ($this->db->strWhere) {
                    $this->db->strWhere = $this->db->strWhere . ' AND sws.regDt BETWEEN ? AND ? ';
                    $this->db->bind_param_push($arrBind, 's', $searchDate[0]);
                    $this->db->bind_param_push($arrBind, 's', $searchDate[1]);
                } else {
                    $this->db->strWhere = ' sws.regDt BETWEEN ? AND ? ';
                    $this->db->bind_param_push($arrBind, 's', $searchDate[0]);
                    $this->db->bind_param_push($arrBind, 's', $searchDate[1]);
                }
            }
        }
        if (isset($searchWord['mallSno'])) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND sws.mallSno = ? ';
                $this->db->bind_param_push($arrBind, 'i', $searchWord['mallSno']);
            } else {
                $this->db->strWhere = ' sws.mallSno = ? ';
                $this->db->bind_param_push($arrBind, 'i', $searchWord['mallSno']);
            }
        }
        if (isset($searchWord['device'])) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND sws.os = ? ';
                $this->db->bind_param_push($arrBind, 's', $searchWord['device']);
            } else {
                $this->db->strWhere = ' sws.os = ? ';
                $this->db->bind_param_push($arrBind, 's', $searchWord['device']);
            }
        }
        if (isset($searchWord['keyword'])) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND sws.keyword LIKE ? ';
                $this->db->bind_param_push($arrBind, 's', '%' . $searchWord['keyword'] . '%');
            } else {
                $this->db->strWhere = ' sws.keyword = ? ';
                $this->db->bind_param_push($arrBind, 's', '%' . $searchWord['keyword'] . '%');
            }
        }
        if ($searchWord['searchType'] == 'goodsNm') {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND sws.resultCount > 0 ';
            } else {
                $this->db->strWhere = ' sws.resultCount > 0 ';
            }
        }

        if ($searchWordField) {
            $this->db->strField = $searchWordField;
        }
        $query = $this->db->query_complete();

        if($isGenerator) {
            $strCountSQL = 'SELECT count(*) as cnt FROM ' . DB_SEARCH_WORD_STATISTICS . ' as sws '.$query['where'];
            $totalNum = $this->db->query_fetch($strCountSQL, $arrBind,false)['cnt'];

            return $this->getSearchWordStatisticsInfoGenerator($totalNum, $query, $arrBind);
        } else {
            $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_SEARCH_WORD_STATISTICS . ' as sws ' . implode(' ', $query);
            $getData = $this->db->query_fetch($strSQL, $arrBind);

            if (count($getData) == 1 && $dataArray === false) {
                return $getData[0];
            }

            return $getData;
        }
    }

    public function getSearchWordStatisticsInfoGenerator($totalNum, $query, $arrBind)
    {
        $pageLimit = "10000";

        if ($pageLimit >= $totalNum) $pageNum = 0;
        else $pageNum = ceil($totalNum / $pageLimit) - 1;

        $strField =   array_shift($query);
        for ($i = 0; $i <= $pageNum; $i++) {
            $strLimit = " LIMIT ".(($i * $pageLimit)) . "," . $pageLimit;
            $strSQL = 'SELECT ' . $strField . ' FROM ' . DB_SEARCH_WORD_STATISTICS . ' as sws ' . implode(' ', $query).$strLimit;
            $tmpData =  $this->db->query_fetch_generator($strSQL, $arrBind);
            foreach($tmpData as $k => $v) {
                yield $v;
            }
            unset($tmpData);
        }
    }

    /**
     * getGoodsCartStatisticsInfo
     * 장바구니 상품 정보 출력
     *
     * @param array       $cartData     mallSno / cartYMD
     * @param string      $cartField    출력할 필드명 (기본 null)
     * @param array       $arrBind      bind 처리 배열 (기본 null)
     *
     * @return array 장바구니 통계 상품 정보
     *
     * @author su
     */
    public function getGoodsCartStatisticsInfo($cartData = null, $cartField = null, $arrBind = null)
    {
        if (is_null($arrBind)) {
            // $arrBind = array();
        }

        if (isset($cartData['mallSno'])) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND cs.mallSno = ? ';
                $this->db->bind_param_push($arrBind, 'i', $cartData['mallSno']);
            } else {
                $this->db->strWhere = ' cs.mallSno = ? ';
                $this->db->bind_param_push($arrBind, 'i', $cartData['mallSno']);
            }
        }
        if (is_array($cartData['cartYMD'])) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND (DATE_FORMAT(cs.regDt, "%Y%m%d") BETWEEN ? AND ?) ';
                $this->db->bind_param_push($arrBind, 'i', $cartData['cartYMD'][0]);
                $this->db->bind_param_push($arrBind, 'i', $cartData['cartYMD'][1]);
            } else {
                $this->db->strWhere = ' (DATE_FORMAT(cs.regDt, "%Y%m%d") BETWEEN ? AND ?) ';
                $this->db->bind_param_push($arrBind, 'i', $cartData['cartYMD'][0]);
                $this->db->bind_param_push($arrBind, 'i', $cartData['cartYMD'][1]);
            }
        }
        if (isset($cartData['orderFl'])) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND cs.orderFl = ? ';
                $this->db->bind_param_push($arrBind, 's', $cartData['orderFl']);
            } else {
                $this->db->strWhere = ' cs.orderFl = ? ';
                $this->db->bind_param_push($arrBind, 's', $cartData['orderFl']);
            }
        }
        if (isset($cartData['goodsSellFl'])) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND g.goodsSellFl = ? ';
                $this->db->bind_param_push($arrBind, 's', $cartData['goodsSellFl']);
            } else {
                $this->db->strWhere = ' g.goodsSellFl = ? ';
                $this->db->bind_param_push($arrBind, 's', $cartData['goodsSellFl']);
            }
        }
        if (isset($cartData['soldOutFl'])) {
            if ($cartData['soldOutFl'] == 'y') {
                $soldOutCheck = 'g.stockFl = "y" AND g.totalStock <= 0';
            } else if ($cartData['soldOutFl'] == 'n') {
                $soldOutCheck = 'g.stockFl = "y" AND g.totalStock > 0';
            }
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND ((g.soldOutFl = ?) OR (' . $soldOutCheck . '))';
                $this->db->bind_param_push($arrBind, 's', $cartData['soldOutFl']);
            } else {
                $this->db->strWhere = ' ((g.soldOutFl = ?) OR (' . $soldOutCheck . ')) ';
                $this->db->bind_param_push($arrBind, 's', $cartData['soldOutFl']);
            }
        }
        // 키워드 검색
        if ($cartData['key'] && $cartData['keyword']) {
            $fieldTypeGoods = DBTableField::getFieldTypes('tableGoods');
            if ($cartData['key'] == 'all') {
                $tmpWhere = array('goodsNm', 'goodsNo', 'goodsCd', 'goodsSearchWord', 'goodsModelNo');
                foreach ($tmpWhere as $keyNm) {
                    $arrWhereAll[] = '(g.' . $keyNm . ' LIKE concat(\'%\',?,\'%\'))';
                    $this->db->bind_param_push($arrBind, $fieldTypeGoods[$keyNm], $cartData['keyword']);
                }
                $addWhere = '(' . implode(' OR ', $arrWhereAll) . ')';
                unset($tmpWhere);
            } else {
                $addWhere = 'g.' . $cartData['key'] . ' LIKE concat(\'%\',?,\'%\')';
                $this->db->bind_param_push($arrBind, $fieldTypeGoods[$cartData['key']], $cartData['keyword']);
            }

            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND ' . $addWhere;
            } else {
                $this->db->strWhere = $addWhere;
            }
        }

        if ($cartField) {
            $this->db->strField = $cartField;
        }
        $this->db->strOrder = 'cs.cartSno asc';
        $this->db->strJoin = 'LEFT JOIN ' . DB_GOODS . ' as g ON g.goodsNo = cs.goodsNo LEFT JOIN ' . DB_GOODS_IMAGE . ' as gi ON cs.goodsNo = gi.goodsNo AND gi.imageKind = "list"';
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_CART_STATISTICS . ' as cs ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);

        return gd_htmlspecialchars_stripslashes($getData);
    }

    /**
     * getGoodsWishStatisticsInfo
     * 장바구니 상품 정보 출력
     *
     * @param array       $wishData     mallSno / wishYMD
     * @param string      $wishField    출력할 필드명 (기본 null)
     * @param array       $arrBind      bind 처리 배열 (기본 null)
     *
     * @return array 관심상품 통계 상품 정보
     *
     * @author su
     */
    public function getGoodsWishStatisticsInfo($wishData = null, $wishField = null, $arrBind = null)
    {
        if (is_null($arrBind)) {
            // $arrBind = array();
        }

        if (isset($wishData['mallSno'])) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND ws.mallSno = ? ';
                $this->db->bind_param_push($arrBind, 'i', $wishData['mallSno']);
            } else {
                $this->db->strWhere = ' ws.mallSno = ? ';
                $this->db->bind_param_push($arrBind, 'i', $wishData['mallSno']);
            }
        }
        if (is_array($wishData['wishYMD'])) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND (DATE_FORMAT(ws.regDt, "%Y%m%d") BETWEEN ? AND ?) ';
                $this->db->bind_param_push($arrBind, 'i', $wishData['wishYMD'][0]);
                $this->db->bind_param_push($arrBind, 'i', $wishData['wishYMD'][1]);
            } else {
                $this->db->strWhere = ' (DATE_FORMAT(ws.regDt, "%Y%m%d") BETWEEN ? AND ?) ';
                $this->db->bind_param_push($arrBind, 'i', $wishData['wishYMD'][0]);
                $this->db->bind_param_push($arrBind, 'i', $wishData['wishYMD'][1]);
            }
        }
        if (isset($wishData['goodsSellFl'])) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND g.goodsSellFl = ? ';
                $this->db->bind_param_push($arrBind, 's', $wishData['goodsSellFl']);
            } else {
                $this->db->strWhere = ' g.goodsSellFl = ? ';
                $this->db->bind_param_push($arrBind, 's', $wishData['goodsSellFl']);
            }
        }
        if (isset($wishData['soldOutFl'])) {
            if ($wishData['soldOutFl'] == 'y') {
                $soldOutCheck = 'g.stockFl = "y" AND g.totalStock <= 0';
            } else if ($wishData['soldOutFl'] == 'n') {
                $soldOutCheck = 'g.stockFl = "y" AND g.totalStock > 0';
            }
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND ((g.soldOutFl = ?) OR (' . $soldOutCheck . '))';
                $this->db->bind_param_push($arrBind, 's', $wishData['soldOutFl']);
            } else {
                $this->db->strWhere = ' ((g.soldOutFl = ?) OR (' . $soldOutCheck . ')) ';
                $this->db->bind_param_push($arrBind, 's', $wishData['soldOutFl']);
            }
        }
        // 키워드 검색
        if ($wishData['key'] && $wishData['keyword']) {
            $fieldTypeGoods = DBTableField::getFieldTypes('tableGoods');
            if ($wishData['key'] == 'all') {
                $tmpWhere = array('goodsNm', 'goodsNo', 'goodsCd', 'goodsSearchWord', 'goodsModelNo');
                foreach ($tmpWhere as $keyNm) {
                    $arrWhereAll[] = '(g.' . $keyNm . ' LIKE concat(\'%\',?,\'%\'))';
                    $this->db->bind_param_push($arrBind, $fieldTypeGoods[$keyNm], $wishData['keyword']);
                }
                $addWhere = '(' . implode(' OR ', $arrWhereAll) . ')';
                unset($tmpWhere);
            } else {
                $addWhere = 'g.' . $wishData['key'] . ' LIKE concat(\'%\',?,\'%\')';
                $this->db->bind_param_push($arrBind, $fieldTypeGoods[$wishData['key']], $wishData['keyword']);
            }

            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND ' . $addWhere;
            } else {
                $this->db->strWhere = $addWhere;
            }
        }

        if ($wishField) {
            $this->db->strField = $wishField;
        }
        $this->db->strOrder = 'ws.wishSno asc';
        $this->db->strJoin = 'LEFT JOIN ' . DB_GOODS . ' as g ON g.goodsNo = ws.goodsNo LEFT JOIN ' . DB_GOODS_IMAGE . ' as gi ON ws.goodsNo = gi.goodsNo AND gi.imageKind = "list"';
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_WISH_STATISTICS . ' as ws ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);

        return gd_htmlspecialchars_stripslashes($getData);
    }

    /**
     * 상품분석 > 카테고리 분석
     * getCategoryStatistics
     *
     * @param $searchData   goodsYMD / mallSno / goodsNo / cateCd / orderTypeFl
     *
     * @return array
     * @throws \Exception
     */
    public function getCategoryStatistics($searchData)
    {
        $sDate = new DateTime($searchData['goodsYMD'][0]);
        $eDate = new DateTime($searchData['goodsYMD'][1]);
        $dateDiff = date_diff($sDate, $eDate);
        if ($dateDiff->days > 90) {
            throw new \Exception(__('검색 가능 일은 최대 90일 입니다.'));
        }
        if ($searchData['goodsYMD'][0] > $searchData['goodsYMD'][1]) {
            throw new \Exception(__('시작일이 종료일보다 클 수 없습니다.'));
        }

        $todayDate = new DateTime();
        // 오늘 검색에 따른 당일 통계
        if ($eDate->format('Ymd') >= $todayDate->format('Ymd')) {
            if ($searchData['mallSno'] != 'all') {
                $goodsDay['mallSno'] = $searchData['mallSno'];
            }
            $goodsDay['sort'] = 'gs.regDt desc';
            $goodsDay['searchType'] = 'cate';
            $goodsDay['limit'][0] = 0;
            $goodsDay['limit'][1] = 1;

            $field = 'gs.regDt';

            $goodsData = $this->getGoodsStatisticsInfo($goodsDay, $field);
            $lastGoodsStatisticsTime = new DateTime($goodsData[0]['regDt']);
            if ($lastGoodsStatisticsTime->diff($todayDate)->d > 0) {
                $this->orderGoodsPolicy['statisticsDate'] = $lastGoodsStatisticsTime;
                $realTimeKey = true;
                $this->setGoodsStatistics($goodsDay['mallSno'], $realTimeKey);
            } else {
                if ($lastGoodsStatisticsTime->diff($todayDate)->h >= $this->orderGoodsPolicy['realStatisticsHour']) {
                    $this->orderGoodsPolicy['statisticsDate'] = $lastGoodsStatisticsTime;
                    $realTimeKey = true;
                    $this->setGoodsStatistics($goodsDay['mallSno'], $realTimeKey);
                }
            }
            unset($goodsDay);
        }

        if ($searchData['mallSno'] != 'all') {
            $goodsDay['mallSno'] = $searchData['mallSno'];
        }
        $goodsDay['goodsYMD'][0] = $sDate->format('Ymd');
        $goodsDay['goodsYMD'][1] = $eDate->format('Ymd');
        rsort($searchData['cateCd']);
        $goodsDay['searchType'] = 'cate';
        if ($searchData['underCategoryFl'] == 'y') {
            $goodsDay['cateCd'] = $searchData['cateCd'][0];
        } else {
            $goodsDay['cateCdEqual'] = $searchData['cateCd'][0];
        }
        $goodsDay['noCategoryFl'] = $searchData['noCategoryFl'];

        if ($searchData['superCategoryFl'] === 'y') {
            $goodsDay['sort'] = 'gs.cateCd asc';
            $getField[] = 'gs.goodsYMD, gs.mallSno, gs.orderTypeFl, gs.cateCd, gs.orderCnt, gs.goodsCnt, gs.goodsPrice';
            $getField[] = 'cg.cateNm';
            $field = implode(', ', $getField);
            $goodsData = $this->getGoodsStatisticsInfo($goodsDay, $field, null, true);
        } else {
            $goodsDay['sort'] = 'gcs.cateCd asc';
            $getField[] = 'gcs.goodsYMD, gcs.mallSno, gcs.orderTypeFl, gcs.cateCd, gcs.orderCnt, gcs.goodsCnt, gcs.goodsPrice';
            $getField[] = 'cg.cateNm';
            $field = implode(', ', $getField);
            $goodsData = $this->getGoodsCategoryStatisticsInfo($goodsDay, $field, null, true);
        }

        $categoryData = [];
        foreach ($goodsData as $key => $val) {
            $categoryData[$val['cateCd']]['cateNm'] = $val['cateNm'];
            $categoryData[$val['cateCd']]['price'] += $val['goodsPrice'] * $val['goodsCnt'];
            $categoryData[$val['cateCd']][$val['orderTypeFl']]['price'] += $val['goodsPrice'] * $val['goodsCnt'];
            $categoryData[$val['cateCd']][$val['orderTypeFl']]['orderCnt'] += $val['orderCnt'];
            $categoryData[$val['cateCd']][$val['orderTypeFl']]['goodsCnt'] += $val['goodsCnt'];
        }

        // 매출이 큰 순으로 정렬
        foreach ($categoryData as $key => $val) {
            $sortCategory[$key] = $val['price'];
        }
        arsort($sortCategory, SORT_NUMERIC);

        foreach ($sortCategory as $sortKey => $sortVal) {
            $categoryData[$sortKey]['cateCd'] = $sortKey;
            $categoryStatistics[] = $categoryData[$sortKey];
        }

        return $categoryStatistics;
    }

    /**
     * 상품분석 > 판매상품 분석
     * getSaleGoodsStatistics
     *
     * @param $searchData   goodsYMD / mallSno / goodsNo / cateCd / orderTypeFl
     *
     * @return array
     * @throws \Exception
     */
    public function getSaleGoodsStatistics($searchData)
    {
        $sDate = new DateTime($searchData['goodsYMD'][0]);
        $eDate = new DateTime($searchData['goodsYMD'][1]);
        $dateDiff = date_diff($sDate, $eDate);
        if ($dateDiff->days > 90) {
            throw new \Exception(__('검색 가능 일은 최대 90일 입니다.'));
        }
        if ($searchData['goodsYMD'][0] > $searchData['goodsYMD'][1]) {
            throw new \Exception(__('시작일이 종료일보다 클 수 없습니다.'));
        }

        $todayDate = new DateTime();
        // 오늘 검색에 따른 당일 통계
        if ($eDate->format('Ymd') >= $todayDate->format('Ymd')) {
            if ($searchData['mallSno'] != 'all') {
                $goodsDay['mallSno'] = $searchData['mallSno'];
            }
            $goodsDay['sort'] = 'gs.regDt desc';
            $goodsDay['searchType'] = 'goods';
            $goodsDay['limit'][0] = 0;
            $goodsDay['limit'][1] = 1;

            $field = 'gs.regDt';

            $goodsData = $this->getGoodsStatisticsInfo($goodsDay, $field);
            $lastGoodsStatisticsTime = new DateTime($goodsData[0]['regDt']);
            if ($lastGoodsStatisticsTime->diff($todayDate)->d > 0) {
                $this->orderGoodsPolicy['statisticsDate'] = $lastGoodsStatisticsTime;
                $realTimeKey = true;
                $this->setGoodsStatistics($goodsDay['mallSno'], $realTimeKey);
            } else {
                if ($lastGoodsStatisticsTime->diff($todayDate)->h >= $this->orderGoodsPolicy['realStatisticsHour']) {
                    $this->orderGoodsPolicy['statisticsDate'] = $lastGoodsStatisticsTime;
                    $realTimeKey = true;
                    $this->setGoodsStatistics($goodsDay['mallSno'], $realTimeKey);
                }
            }
            unset($goodsDay);
        }

        if ($searchData['mallSno'] != 'all') {
            $goodsDay['mallSno'] = $searchData['mallSno'];
        }
        $goodsDay['goodsYMD'][0] = $sDate->format('Ymd');
        $goodsDay['goodsYMD'][1] = $eDate->format('Ymd');
        rsort($searchData['cateCd']);
        $goodsDay['goodsNm'] = $searchData['goodsNm'];
        $goodsDay['searchType'] = 'goods';
        if ($searchData['underCategoryFl'] == 'y') {
            $goodsDay['cateCd'] = $searchData['cateCd'][0];
        } else {
            $goodsDay['cateCdEqual'] = $searchData['cateCd'][0];
        }
        $goodsDay['noCategoryFl'] = $searchData['noCategoryFl'];
        if ($searchData['superCategoryFl'] === 'y') {
            $goodsDay['sort'] = 'gs.goodsNo asc';
            $getField[] = 'gs.goodsYMD, gs.mallSno, gs.goodsNo, gs.orderTypeFl, gs.cateCd, gs.orderCnt, gs.goodsCnt, gs.goodsPrice';
            //$getField[] = 'g.goodsNm, g.imageStorage, g.imagePath';
            //$getField[] = 'gi.imageName';
            $field = implode(', ', $getField);
            $goodsData = $this->getGoodsStatisticsInfo($goodsDay, $field, null, true);
        } else {
            $goodsDay['sort'] = 'gcs.goodsNo asc';
            $getField[] = 'gcs.goodsYMD, gcs.mallSno, gcs.goodsNo, gcs.orderTypeFl, gcs.cateCd, gcs.orderCnt, gcs.goodsCnt, gcs.goodsPrice';
            $field = implode(', ', $getField);
            $goodsData = $this->getGoodsCategoryStatisticsInfo($goodsDay, $field, null, true);
        }

        $returnGoodsData = [];
        foreach ($goodsData as $key => $val) {
            $returnGoodsData[$val['goodsNo']]['imageStorage'] = $val['imageStorage'];
            $returnGoodsData[$val['goodsNo']]['imagePath'] = $val['imagePath'];
            $returnGoodsData[$val['goodsNo']]['imageName'] = $val['imageName'];
            $returnGoodsData[$val['goodsNo']]['goodsNm'] = $val['goodsNm'];
            $returnGoodsData[$val['goodsNo']]['price'] += $val['goodsPrice'] * $val['goodsCnt'];
            $returnGoodsData[$val['goodsNo']][$val['orderTypeFl']]['price'] += $val['goodsPrice'] * $val['goodsCnt'];
            $returnGoodsData[$val['goodsNo']][$val['orderTypeFl']]['orderCnt'] += $val['orderCnt'];
            $returnGoodsData[$val['goodsNo']][$val['orderTypeFl']]['goodsCnt'] += $val['goodsCnt'];
        }

        // 매출이 큰 순으로 정렬
        foreach ($returnGoodsData as $key => $val) {
            $sortGoods[$key] = $val['price'];
        }
        arsort($sortGoods, SORT_NUMERIC);

        foreach ($sortGoods as $sortKey => $sortVal) {
            $returnGoodsData[$sortKey]['goodsNo'] = $sortKey;
            $goodsStatistics[] = $returnGoodsData[$sortKey];
        }

        return $goodsStatistics;
    }

    /**
     * 상품분석 > 판매상품 분석
     * getSaleGoodsStatistics
     *
     * @param $searchData   goodsYMD / mallSno / goodsNo / cateCd / orderTypeFl
     *
     * @return array
     * @throws \Exception
     */
    public function getSaleGoodsOptionStatistics($searchData)
    {
        $sDate = new DateTime($searchData['goodsYMD'][0]);
        $eDate = new DateTime($searchData['goodsYMD'][1]);
        $dateDiff = date_diff($sDate, $eDate);
        if ($dateDiff->days > 90) {
            throw new \Exception(__('검색 가능 일은 최대 90일 입니다.'));
        }
        if ($searchData['goodsYMD'][0] > $searchData['goodsYMD'][1]) {
            throw new \Exception(__('시작일이 종료일보다 클 수 없습니다.'));
        }

        $todayDate = new DateTime();
        // 오늘 검색에 따른 당일 통계
        if ($eDate->format('Ymd') >= $todayDate->format('Ymd')) {
            if ($searchData['mallSno'] != 'all') {
                $goodsDay['mallSno'] = $searchData['mallSno'];
            }
            $goodsDay['sort'] = 'gs.regDt desc';
            $goodsDay['searchType'] = 'goods';
            $goodsDay['limit'][0] = 0;
            $goodsDay['limit'][1] = 1;

            $field = 'gs.regDt';

            $goodsData = $this->getGoodsStatisticsInfo($goodsDay, $field);
            $lastGoodsStatisticsTime = new DateTime($goodsData[0]['regDt']);
            if ($lastGoodsStatisticsTime->diff($todayDate)->d > 0) {
                $this->orderGoodsPolicy['statisticsDate'] = $lastGoodsStatisticsTime;
                $realTimeKey = true;
                $this->setGoodsStatistics($goodsDay['mallSno'], $realTimeKey);
            } else {
                if ($lastGoodsStatisticsTime->diff($todayDate)->h >= $this->orderGoodsPolicy['realStatisticsHour']) {
                    $this->orderGoodsPolicy['statisticsDate'] = $lastGoodsStatisticsTime;
                    $realTimeKey = true;
                    $this->setGoodsStatistics($goodsDay['mallSno'], $realTimeKey);
                }
            }
            unset($goodsDay);
        }

        if ($searchData['mallSno'] != 'all') {
            $goodsDay['mallSno'] = $searchData['mallSno'];
        }
        $goodsDay['goodsYMD'][0] = $sDate->format('Ymd');
        $goodsDay['goodsYMD'][1] = $eDate->format('Ymd');
        rsort($searchData['cateCd']);
        $goodsDay['goodsNm'] = $searchData['goodsNm'];
        $goodsDay['searchType'] = 'goods';

        if ($searchData['underCategoryFl'] == 'y') {
            $goodsDay['cateCd'] = $searchData['cateCd'][0];
        } else {
            $goodsDay['cateCdEqual'] = $searchData['cateCd'][0];
        }
        $goodsDay['noCategoryFl'] = $searchData['noCategoryFl'];
        if ($searchData['superCategoryFl'] === 'y') {
            $goodsDay['sort'] = 'gs.goodsNo asc';
            $getField[] = 'gs.goodsYMD, gs.mallSno, gs.goodsNo, gs.orderTypeFl, gs.cateCd, gs.orderCnt, gs.goodsCnt, gs.goodsPrice, gs.optionSno, gs.optionInfo, gs.optionPrice';
            //$getField[] = 'g.goodsNm, g.imageStorage, g.imagePath';
            //$getField[] = 'gi.imageName';
            $field = implode(', ', $getField);
            $goodsData = $this->getGoodsOptionStatisticsInfo($goodsDay, $field, null, true);
        } else {
            $goodsDay['sort'] = 'gcs.goodsNo asc';
            $getField[] = 'gcs.goodsYMD, gcs.mallSno, gcs.goodsNo, gcs.orderTypeFl, gcs.cateCd, gcs.orderCnt, gcs.goodsCnt, gcs.goodsPrice, gcs.optionSno, gcs.optionInfo, gcs.optionPrice';
            $field = implode(', ', $getField);
            $goodsData = $this->getGoodsOptionCategoryStatisticsInfo($goodsDay, $field, null, true);
        }

        $returnGoodsData = [];
        foreach ($goodsData as $key => $val) {
            $goodsKey = $val['goodsNo'] . '_' . $val['optionSno'];
            $returnGoodsData[$goodsKey]['goodsNo'] = $val['goodsNo'];
            $returnGoodsData[$goodsKey]['optionSno'] = $val['optionSno'];
            $returnGoodsData[$goodsKey]['imageStorage'] = $val['imageStorage'];
            $returnGoodsData[$goodsKey]['imagePath'] = $val['imagePath'];
            $returnGoodsData[$goodsKey]['imageName'] = $val['imageName'];
            $returnGoodsData[$goodsKey]['goodsNm'] = $val['goodsNm'];
            $returnGoodsData[$goodsKey]['optionInfo'] = $val['optionInfo'];
            $returnGoodsData[$goodsKey]['price'] += ($val['goodsPrice'] + $val['optionPrice']) * $val['goodsCnt'];
            $returnGoodsData[$goodsKey][$val['orderTypeFl']]['price'] += ($val['goodsPrice'] + $val['optionPrice']) * $val['goodsCnt'];
            $returnGoodsData[$goodsKey][$val['orderTypeFl']]['orderCnt'] += $val['orderCnt'];
            $returnGoodsData[$goodsKey][$val['orderTypeFl']]['goodsCnt'] += $val['goodsCnt'];
        }

        // 매출이 큰 순으로 정렬
        foreach ($returnGoodsData as $key => $val) {
            $sortGoods[$key] = $val['price'];
        }
        arsort($sortGoods, SORT_NUMERIC);

        foreach ($sortGoods as $sortKey => $sortVal) {
            $goodsStatistics[] = $returnGoodsData[$sortKey];
        }

        return $goodsStatistics;
    }

    /**
     * 메인분석 > 메인분류 셀렉박스
     * getLinkMainStatistics
     *
     * @return array
     * @throws \Exception
     */
    public function getLinkMainStatistics()
    {
        $linkMainData = $this->getLinkMainStatisticsInfo();

        $returnLinkMainData = [];
        foreach ($linkMainData as $key => $val) {
            $returnLinkMainData[$val['themeDevice']][$val['themeSno']] = $val['themeNm'];
        }

        return $returnLinkMainData;
    }

    /**
     * 메인분석 > 메인분류별 현황
     * getMainStatistics
     *
     * @param $searchData   goodsYMD / mallSno / goodsNo / cateCd / orderTypeFl
     *
     * @return array
     * @throws \Exception
     */
    public function getMainStatistics($searchData)
    {
        $sDate = new DateTime($searchData['goodsYMD'][0]);
        $eDate = new DateTime($searchData['goodsYMD'][1]);
        $dateDiff = date_diff($sDate, $eDate);
        if ($dateDiff->days > 90) {
            throw new \Exception(__('검색 가능 일은 최대 90일 입니다.'));
        }
        if ($searchData['goodsYMD'][0] > $searchData['goodsYMD'][1]) {
            throw new \Exception(__('시작일이 종료일보다 클 수 없습니다.'));
        }

        $todayDate = new DateTime();
        // 오늘 검색에 따른 당일 통계
        if ($eDate->format('Ymd') >= $todayDate->format('Ymd')) {
            if ($searchData['mallSno'] != 'all') {
                $goodsDay['mallSno'] = $searchData['mallSno'];
            }
            $goodsDay['sort'] = 'gs.regDt desc';
            $goodsDay['searchType'] = 'goods';
            $goodsDay['limit'][0] = 0;
            $goodsDay['limit'][1] = 1;

            $field = 'gs.regDt';

            $goodsData = $this->getGoodsStatisticsInfo($goodsDay, $field);
            $lastGoodsStatisticsTime = new DateTime($goodsData[0]['regDt']);
            if ($lastGoodsStatisticsTime->diff($todayDate)->d > 0) {
                $this->orderGoodsPolicy['statisticsDate'] = $lastGoodsStatisticsTime;
                $realTimeKey = true;
                $this->setGoodsStatistics($goodsDay['mallSno'], $realTimeKey);
            } else {
                if ($lastGoodsStatisticsTime->diff($todayDate)->h >= $this->orderGoodsPolicy['realStatisticsHour']) {
                    $this->orderGoodsPolicy['statisticsDate'] = $lastGoodsStatisticsTime;
                    $realTimeKey = true;
                    $this->setGoodsStatistics($goodsDay['mallSno'], $realTimeKey);
                }
            }
            unset($goodsDay);
        }

        if ($searchData['mallSno'] != 'all') {
            $goodsDay['mallSno'] = $searchData['mallSno'];
        }
        $goodsDay['goodsYMD'][0] = $sDate->format('Ymd');
        $goodsDay['goodsYMD'][1] = $eDate->format('Ymd');
        $goodsDay['themeDevice'] = $searchData['deviceFl'];
        $goodsDay['themeSno'] = $searchData['mainChannelFl'];
        $goodsDay['goodsNm'] = $searchData['goodsNm'];

        $goodsDay['sort'] = 'gms.goodsNo asc';
        $getField[] = 'gms.goodsYMD, gms.themeSno, gms.themeNm, gms.themeDevice, gms.mallSno, gms.goodsNo, gms.orderTypeFl, gms.cateCd, gms.orderCnt, gms.goodsCnt, gms.goodsPrice,  gms.optionPrice';
        $field = implode(', ', $getField);
        $goodsData = $this->getGoodsMainStatisticsInfo($goodsDay, $field, null);

        $returnGoodsData = [];
        foreach ($goodsData as $key => $val) {
            $goodsKey = $val['themeSno'];
            $returnGoodsData[$goodsKey]['themeNm'] = $val['themeNm'];
            $returnGoodsData[$goodsKey]['goodsNo'] = $val['goodsNo'];
            $returnGoodsData[$goodsKey]['goodsNm'] = $val['goodsNm'];
            $returnGoodsData[$goodsKey]['price'] += $val['goodsPrice'] * $val['goodsCnt'];
            $returnGoodsData[$goodsKey][$val['orderTypeFl']]['price'] += $val['goodsPrice'] * $val['goodsCnt'];
            $returnGoodsData[$goodsKey][$val['orderTypeFl']]['orderCnt'] += $val['orderCnt'];
            $returnGoodsData[$goodsKey][$val['orderTypeFl']]['goodsCnt'] += $val['goodsCnt'];
        }

        // 매출이 큰 순으로 정렬
        foreach ($returnGoodsData as $key => $val) {
            $sortGoods[$key] = $val['price'];
        }
        arsort($sortGoods, SORT_NUMERIC);

        foreach ($sortGoods as $sortKey => $sortVal) {
            $goodsStatistics[] = $returnGoodsData[$sortKey];
        }

        return $goodsStatistics;
    }

    /**
     * 메인분석 > 상품별 현황
     * getMainGoodsStatistics
     *
     * @param $searchData   goodsYMD / mallSno / goodsNo / cateCd / orderTypeFl
     *
     * @return array
     * @throws \Exception
     */
    public function getMainGoodsStatistics($searchData)
    {
        $sDate = new DateTime($searchData['goodsYMD'][0]);
        $eDate = new DateTime($searchData['goodsYMD'][1]);
        $dateDiff = date_diff($sDate, $eDate);
        if ($dateDiff->days > 90) {
            throw new \Exception(__('검색 가능 일은 최대 90일 입니다.'));
        }
        if ($searchData['goodsYMD'][0] > $searchData['goodsYMD'][1]) {
            throw new \Exception(__('시작일이 종료일보다 클 수 없습니다.'));
        }

        $todayDate = new DateTime();
        // 오늘 검색에 따른 당일 통계
        if ($eDate->format('Ymd') >= $todayDate->format('Ymd')) {
            if ($searchData['mallSno'] != 'all') {
                $goodsDay['mallSno'] = $searchData['mallSno'];
            }
            $goodsDay['sort'] = 'gs.regDt desc';
            $goodsDay['searchType'] = 'goods';
            $goodsDay['limit'][0] = 0;
            $goodsDay['limit'][1] = 1;

            $field = 'gs.regDt';

            $goodsData = $this->getGoodsStatisticsInfo($goodsDay, $field);
            $lastGoodsStatisticsTime = new DateTime($goodsData[0]['regDt']);
            if ($lastGoodsStatisticsTime->diff($todayDate)->d > 0) {
                $this->orderGoodsPolicy['statisticsDate'] = $lastGoodsStatisticsTime;
                $realTimeKey = true;
                $this->setGoodsStatistics($goodsDay['mallSno'], $realTimeKey);
            } else {
                if ($lastGoodsStatisticsTime->diff($todayDate)->h >= $this->orderGoodsPolicy['realStatisticsHour']) {
                    $this->orderGoodsPolicy['statisticsDate'] = $lastGoodsStatisticsTime;
                    $realTimeKey = true;
                    $this->setGoodsStatistics($goodsDay['mallSno'], $realTimeKey);
                }
            }
            unset($goodsDay);
        }

        if ($searchData['mallSno'] != 'all') {
            $goodsDay['mallSno'] = $searchData['mallSno'];
        }
        $goodsDay['goodsYMD'][0] = $sDate->format('Ymd');
        $goodsDay['goodsYMD'][1] = $eDate->format('Ymd');
        $goodsDay['themeDevice'] = $searchData['deviceFl'];
        $goodsDay['themeSno'] = $searchData['mainChannelFl'];
        $goodsDay['goodsNm'] = $searchData['goodsNm'];

        $goodsDay['sort'] = 'gms.goodsNo asc';
        $getField[] = 'gms.goodsYMD, gms.themeSno, gms.themeNm, gms.themeDevice, gms.mallSno, gms.goodsNo, gms.orderTypeFl, gms.cateCd, gms.orderCnt, gms.goodsCnt, gms.goodsPrice';
        $field = implode(', ', $getField);
        $goodsData = $this->getGoodsMainStatisticsInfo($goodsDay, $field, null);

        $returnGoodsData = [];
        foreach ($goodsData as $key => $val) {
            $goodsKey = $val['themeSno'] . '_' . $val['goodsNo'];
            $returnGoodsData[$goodsKey]['themeSno'] = $val['themeSno'];
            $returnGoodsData[$goodsKey]['themeNm'] = $val['themeNm'];
            $returnGoodsData[$goodsKey]['goodsNo'] = $val['goodsNo'];
            $returnGoodsData[$goodsKey]['imageStorage'] = $val['imageStorage'];
            $returnGoodsData[$goodsKey]['imagePath'] = $val['imagePath'];
            $returnGoodsData[$goodsKey]['imageName'] = $val['imageName'];
            $returnGoodsData[$goodsKey]['goodsNm'] = $val['goodsNm'];
            $returnGoodsData[$goodsKey]['price'] += $val['goodsPrice'] * $val['goodsCnt'];
            $returnGoodsData[$goodsKey][$val['orderTypeFl']]['price'] += $val['goodsPrice'] * $val['goodsCnt'];
            $returnGoodsData[$goodsKey][$val['orderTypeFl']]['orderCnt'] += $val['orderCnt'];
            $returnGoodsData[$goodsKey][$val['orderTypeFl']]['goodsCnt'] += $val['goodsCnt'];
        }

        // 매출이 큰 순으로 정렬
        foreach ($returnGoodsData as $key => $val) {
            $sortGoods[$key] = $val['price'];
        }
        arsort($sortGoods, SORT_NUMERIC);

        foreach ($sortGoods as $sortKey => $sortVal) {
            $goodsStatistics[] = $returnGoodsData[$sortKey];
        }

        return $goodsStatistics;
    }

    /**
     * 상품분석 > 검색어 분석
     * getSearchWordStatistics
     *
     * @param $searchData   goodsYMD / mallSno / goodsNo / cateCd / orderTypeFl
     *
     * @return array
     * @throws \Exception
     */
    public function getSearchWordStatistics($searchData)
    {
        $sDate = new DateTime($searchData['regDt'][0]);
        $eDate = new DateTime($searchData['regDt'][1]);
        $dateDiff = date_diff($sDate, $eDate);
        if ($dateDiff->days > 90) {
            throw new \Exception(__('검색 가능 일은 최대 90일 입니다.'));
        }
        if ($searchData['regDt'][0] > $searchData['regDt'][1]) {
            throw new \Exception(__('시작일이 종료일보다 클 수 없습니다.'));
        }

        if ($searchData['mallSno'] != 'all') {
            $searchWord['mallSno'] = $searchData['mallSno'];
        }
        if ($searchData['searchDevice'] != 'all') {
            $searchWord['device'] = $searchData['searchDevice'];
        }
        if (empty($searchData['keyword']) === false) {
            $searchWord['keyword'] = $searchData['keyword'];
        }
        if (empty($searchData['searchType']) === false) {
            $searchWord['searchType'] = $searchData['searchType'];
        }

        $searchWord['regDt'][0] = $sDate->format('Ymd');
        $searchWord['regDt'][1] = $eDate->format('Ymd');

        $searchWordData = $this->getSearchWordStatisticsInfo($searchWord, null, null, true, true);

        $returnSearchWordData = [];
        foreach ($searchWordData as $key => $val) {
            $keyword = trim($val['keyword']);
            $returnSearchWordData[$keyword]['searchCount'] += 1;
        }

        // 검색이 많은 순으로 정렬
        foreach ($returnSearchWordData as $key => $val) {
            $sortSearchWord[$key] = $val['searchCount'];
        }
        arsort($sortSearchWord, SORT_NUMERIC);

        foreach ($sortSearchWord as $sortKey => $sortVal) {
            $returnSearchWordData[$sortKey]['keyword'] = $sortKey;
            $searchWordStatistics[] = $returnSearchWordData[$sortKey];
        }

        return $searchWordStatistics;
    }

    /**
     * 상품분석 > 장바구니 분석
     * getGoodsCartStatistics
     *
     * @param $searchData   cartYMD / mallSno / goodsNo / ...
     *
     * @return array
     * @throws \Exception
     */
    public function getGoodsCartStatistics($searchData)
    {
        $sDate = new DateTime($searchData['cartYMD'][0]);
        $eDate = new DateTime($searchData['cartYMD'][1]);
        $dateDiff = date_diff($sDate, $eDate);
        if ($dateDiff->days > 90) {
            throw new \Exception(__('검색 가능 일은 최대 90일 입니다.'));
        }
        if ($searchData['cartYMD'][0] > $searchData['cartYMD'][1]) {
            throw new \Exception(__('시작일이 종료일보다 클 수 없습니다.'));
        }
        if ($searchData['mallSno'] != 'all') {
            $cartData['mallSno'] = $searchData['mallSno'];
        }

        $cartData['cartYMD'][0] = $sDate->format('Ymd');
        $cartData['cartYMD'][1] = $eDate->format('Ymd');
        if ($searchData['orderFl']) {
            $cartData['orderFl'] = $searchData['orderFl']; // 구매여부
        }
        if ($searchData['goodsSellFl']) {
            $cartData['goodsSellFl'] = $searchData['goodsSellFl']; // 판매상태
        }
        if ($searchData['soldOutFl']) {
            $cartData['soldOutFl'] = $searchData['soldOutFl']; // 품절상태
        }
        $cartData['key'] = $searchData['key']; // 품절상태
        if ($searchData['keyword']) {
            $cartData['keyword'] = $searchData['keyword']; // 품절상태
        }

        $getField[] = 'cs.siteKey, cs.memNo, cs.goodsNo';
        $getField[] = 'g.goodsNm, g.imageStorage, g.imagePath, g.goodsPrice, g.stockFl, g.totalStock, g.soldOutFl, DATE_FORMAT(g.regDt, "%Y-%m-%d") as regDtFormat';
        $getField[] = 'gi.imageName';
        $field = implode(', ', $getField);

        $goodsData = $this->getGoodsCartStatisticsInfo($cartData, $field, null, true);

        $returnGoodsData = [];
        $tmpSiteKey = '';
        $i = 0;
        foreach ($goodsData as $key => $val) {
            $returnGoodsData[$val['goodsNo']]['cnt'] += 1;
            $returnGoodsData[$val['goodsNo']]['stockFl'] = $val['stockFl'];
            $returnGoodsData[$val['goodsNo']]['totalStock'] = $val['totalStock'];
            $returnGoodsData[$val['goodsNo']]['soldOutFl'] = $val['soldOutFl'];
            $returnGoodsData[$val['goodsNo']]['imageStorage'] = $val['imageStorage'];
            $returnGoodsData[$val['goodsNo']]['imagePath'] = $val['imagePath'];
            $returnGoodsData[$val['goodsNo']]['imageName'] = $val['imageName'];
            $returnGoodsData[$val['goodsNo']]['goodsNm'] = $val['goodsNm'];
            $returnGoodsData[$val['goodsNo']]['price'] = $val['goodsPrice'];
            if ($val['memNo'] == 0) {
                if ($tmpSiteKey != $val['siteKey']) {
                    $memNo = 'no' . $i;
                }
            } else {
                $memNo = $val['memNo'];
            }
            $tmpSiteKey = $val['siteKey'];
            $returnGoodsData[$val['goodsNo']]['memNo'][] = $memNo;
            $returnGoodsData[$val['goodsNo']]['memNo'] = array_unique($returnGoodsData[$val['goodsNo']]['memNo']);
            $returnGoodsData[$val['goodsNo']]['regDt'] = $val['regDtFormat'];
            $i++;
        }

        // 많이 담긴 순으로 정렬
        foreach ($returnGoodsData as $key => $val) {
            $sortGoods[$key] = $val['cnt'];
        }
        arsort($sortGoods, SORT_NUMERIC);

        foreach ($sortGoods as $sortKey => $sortVal) {
            $returnGoodsData[$sortKey]['goodsNo'] = $sortKey;
            $goodsStatistics[] = $returnGoodsData[$sortKey];
        }

        return $goodsStatistics;
    }

    /**
     * 상품분석 > 장바구니 분석 > 회원리스트
     * getGoodsCartMemberStatistics
     *
     * @param $searchData   cartYMD / mallSno / goodsNo / page / pageNum
     *
     * @return array
     * @throws \Exception
     */
    public function getGoodsCartMemberStatistics($searchData)
    {
        $sDate = new DateTime($searchData['cartYMD'][0]);
        $eDate = new DateTime($searchData['cartYMD'][1]);
        $dateDiff = date_diff($sDate, $eDate);
        if ($dateDiff->days > 90) {
            throw new \Exception(__('검색 가능 일은 최대 90일 입니다.'));
        }
        if ($searchData['cartYMD'][0] > $searchData['cartYMD'][1]) {
            throw new \Exception(__('시작일이 종료일보다 클 수 없습니다.'));
        }
        if ($searchData['mallSno'] != 'all') {
            $cartData['mallSno'] = $searchData['mallSno'];
        }

        $cartData['cartYMD'][0] = $sDate->format('Ymd');
        $cartData['cartYMD'][1] = $eDate->format('Ymd');
        $cartData['goodsNo'] = $searchData['goodsNo']; // 장바구니에 담긴 상품고유번호

        $arrBind = [];
        if (isset($cartData['mallSno'])) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND cs.mallSno = ? ';
                $this->db->bind_param_push($arrBind, 'i', $cartData['mallSno']);
            } else {
                $this->db->strWhere = ' cs.mallSno = ? ';
                $this->db->bind_param_push($arrBind, 'i', $cartData['mallSno']);
            }
        }
        if (is_array($cartData['cartYMD'])) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND (DATE_FORMAT(cs.regDt, "%Y%m%d") BETWEEN ? AND ?) ';
                $this->db->bind_param_push($arrBind, 'i', $cartData['cartYMD'][0]);
                $this->db->bind_param_push($arrBind, 'i', $cartData['cartYMD'][1]);
            } else {
                $this->db->strWhere = ' (DATE_FORMAT(cs.regDt, "%Y%m%d") BETWEEN ? AND ?) ';
                $this->db->bind_param_push($arrBind, 'i', $cartData['cartYMD'][0]);
                $this->db->bind_param_push($arrBind, 'i', $cartData['cartYMD'][1]);
            }
        }
        if (isset($cartData['goodsNo'])) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND cs.goodsNo = ? ';
                $this->db->bind_param_push($arrBind, 'i', $cartData['goodsNo']);
            } else {
                $this->db->strWhere = ' cs.goodsNo = ? ';
                $this->db->bind_param_push($arrBind, 'i', $cartData['goodsNo']);
            }
        }
        if ($this->db->strWhere) {
            $this->db->strWhere = $this->db->strWhere . ' AND cs.memNo > 0 ';
        } else {
            $this->db->strWhere = ' cs.memNo > 0 ';
        }

        $strSQL = 'SELECT COUNT(distinct(cs.memNo)) AS cnt FROM ' . DB_CART_STATISTICS .' as cs WHERE ' . $this->db->strWhere;
        $res = $this->db->query_fetch($strSQL, $arrBind, false);

        gd_isset($searchData['page'], 1);
        gd_isset($searchData['pageNum'], 10);

        $page = \App::load('\\Component\\Page\\Page', $searchData['page']);
        $page->page['list'] = $searchData['pageNum']; // 페이지당 리스트 수
        $page->recode['total'] = $res['cnt']; // 전체 레코드 수
        $page->setPage();
        $page->setUrl(\Request::getQueryString());

        $getField[] = 'cs.memNo, cs.goodsNo, DATE_FORMAT(MAX(cs.regDt), "%Y-%m-%d") as regDtFormat';
        $getField[] = 'm.memId, m.memNm, m.saleAmt, m.loginCnt, DATE_FORMAT(m.approvalDt, "%Y-%m-%d") as approvalDtFormat, DATE_FORMAT(m.lastLoginDt, "%Y-%m-%d") as lastLoginDtFormat';
        $this->db->strField = implode(', ', $getField);
        $this->db->strOrder = 'cs.cartSno desc';
        $this->db->strJoin = 'LEFT JOIN ' . DB_MEMBER . ' as m ON m.memNo = cs.memNo';
        $this->db->strGroup = 'cs.memNo';
        $this->db->strLimit = $page->recode['start'] . ',' . $searchData['pageNum'];

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_CART_STATISTICS . ' as cs ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);

        $page->recode['amount'] = count($getData);
        $page->setPage();

        return gd_htmlspecialchars_stripslashes($getData);
    }


    /**
     * 상품분석 > 관심상품 분석
     * getGoodsWishStatistics
     *
     * @param $searchData   wishYMD / mallSno / goodsNo / ...
     *
     * @return array
     * @throws \Exception
     */
    public function getGoodsWishStatistics($searchData)
    {
        $sDate = new DateTime($searchData['wishYMD'][0]);
        $eDate = new DateTime($searchData['wishYMD'][1]);
        $dateDiff = date_diff($sDate, $eDate);
        if ($dateDiff->days > 90) {
            throw new \Exception(__('검색 가능 일은 최대 90일 입니다.'));
        }
        if ($searchData['wishYMD'][0] > $searchData['wishYMD'][1]) {
            throw new \Exception(__('시작일이 종료일보다 클 수 없습니다.'));
        }
        if ($searchData['mallSno'] != 'all') {
            $wishData['mallSno'] = $searchData['mallSno'];
        }

        $wishData['wishYMD'][0] = $sDate->format('Ymd');
        $wishData['wishYMD'][1] = $eDate->format('Ymd');
        $wishData['directCart'] = 'n'; // 바로 주문이 아닌 것
        if ($searchData['goodsSellFl']) {
            $wishData['goodsSellFl'] = $searchData['goodsSellFl']; // 판매상태
        }
        if ($searchData['soldOutFl']) {
            $wishData['soldOutFl'] = $searchData['soldOutFl']; // 품절상태
        }
        $wishData['key'] = $searchData['key']; // 품절상태
        if ($searchData['keyword']) {
            $wishData['keyword'] = $searchData['keyword']; // 품절상태
        }

        $getField[] = 'ws.memNo, ws.goodsNo';
        $getField[] = 'g.goodsNm, g.imageStorage, g.imagePath, g.goodsPrice, g.stockFl, g.totalStock, g.soldOutFl, DATE_FORMAT(g.regDt, "%Y-%m-%d") as regDtFormat';
        $getField[] = 'gi.imageName';
        $field = implode(', ', $getField);

        $goodsData = $this->getGoodsWishStatisticsInfo($wishData, $field, null, true);

        $returnGoodsData = [];
        $i = 0;
        foreach ($goodsData as $key => $val) {
            $returnGoodsData[$val['goodsNo']]['cnt'] += 1;
            $returnGoodsData[$val['goodsNo']]['stockFl'] = $val['stockFl'];
            $returnGoodsData[$val['goodsNo']]['totalStock'] = $val['totalStock'];
            $returnGoodsData[$val['goodsNo']]['soldOutFl'] = $val['soldOutFl'];
            $returnGoodsData[$val['goodsNo']]['imageStorage'] = $val['imageStorage'];
            $returnGoodsData[$val['goodsNo']]['imagePath'] = $val['imagePath'];
            $returnGoodsData[$val['goodsNo']]['imageName'] = $val['imageName'];
            $returnGoodsData[$val['goodsNo']]['goodsNm'] = $val['goodsNm'];
            $returnGoodsData[$val['goodsNo']]['price'] = $val['goodsPrice'];
            $returnGoodsData[$val['goodsNo']]['memNo'][] = $val['memNo'];
            $returnGoodsData[$val['goodsNo']]['memNo'] = array_unique($returnGoodsData[$val['goodsNo']]['memNo']);
            $returnGoodsData[$val['goodsNo']]['regDt'] = $val['regDtFormat'];
            $i++;
        }

        // 매출이 큰 순으로 정렬
        foreach ($returnGoodsData as $key => $val) {
            $sortGoods[$key] = $val['cnt'];
        }
        arsort($sortGoods, SORT_NUMERIC);

        foreach ($sortGoods as $sortKey => $sortVal) {
            $returnGoodsData[$sortKey]['goodsNo'] = $sortKey;
            $goodsStatistics[] = $returnGoodsData[$sortKey];
        }

        return $goodsStatistics;
    }

    /**
     * 상품분석 > 관심상품 분석 > 회원리스트
     * getGoodsWishMemberStatistics
     *
     * @param $searchData   wishYMD / mallSno / goodsNo / page / pageNum
     *
     * @return array
     * @throws \Exception
     */
    public function getGoodsWishMemberStatistics($searchData)
    {
        $sDate = new DateTime($searchData['wishYMD'][0]);
        $eDate = new DateTime($searchData['wishYMD'][1]);
        $dateDiff = date_diff($sDate, $eDate);
        if ($dateDiff->days > 90) {
            throw new \Exception(__('검색 가능 일은 최대 90일 입니다.'));
        }
        if ($searchData['wishYMD'][0] > $searchData['wishYMD'][1]) {
            throw new \Exception(__('시작일이 종료일보다 클 수 없습니다.'));
        }
        if ($searchData['mallSno'] != 'all') {
            $wishData['mallSno'] = $searchData['mallSno'];
        }

        $wishData['wishYMD'][0] = $sDate->format('Ymd');
        $wishData['wishYMD'][1] = $eDate->format('Ymd');
        $wishData['goodsNo'] = $searchData['goodsNo']; // 장바구니에 담긴 상품고유번호

        $arrBind = [];
        if (isset($wishData['mallSno'])) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND ws.mallSno = ? ';
                $this->db->bind_param_push($arrBind, 'i', $wishData['mallSno']);
            } else {
                $this->db->strWhere = ' ws.mallSno = ? ';
                $this->db->bind_param_push($arrBind, 'i', $wishData['mallSno']);
            }
        }
        if (is_array($wishData['wishYMD'])) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND (DATE_FORMAT(ws.regDt, "%Y%m%d") BETWEEN ? AND ?) ';
                $this->db->bind_param_push($arrBind, 'i', $wishData['wishYMD'][0]);
                $this->db->bind_param_push($arrBind, 'i', $wishData['wishYMD'][1]);
            } else {
                $this->db->strWhere = ' (DATE_FORMAT(ws.regDt, "%Y%m%d") BETWEEN ? AND ?) ';
                $this->db->bind_param_push($arrBind, 'i', $wishData['wishYMD'][0]);
                $this->db->bind_param_push($arrBind, 'i', $wishData['wishYMD'][1]);
            }
        }
        if (isset($wishData['goodsNo'])) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND ws.goodsNo = ? ';
                $this->db->bind_param_push($arrBind, 'i', $wishData['goodsNo']);
            } else {
                $this->db->strWhere = ' ws.goodsNo = ? ';
                $this->db->bind_param_push($arrBind, 'i', $wishData['goodsNo']);
            }
        }
        if ($this->db->strWhere) {
            $this->db->strWhere = $this->db->strWhere . ' AND ws.memNo > 0 ';
        } else {
            $this->db->strWhere = ' ws.memNo > 0 ';
        }

        $strSQL = 'SELECT COUNT(distinct(ws.memNo)) AS cnt FROM ' . DB_WISH_STATISTICS .' as ws WHERE ' . $this->db->strWhere;
        $res = $this->db->query_fetch($strSQL, $arrBind, false);

        gd_isset($searchData['page'], 1);
        gd_isset($searchData['pageNum'], 10);
        $page = \App::load('\\Component\\Page\\Page', $searchData['page']);
        $page->page['list'] = $searchData['pageNum']; // 페이지당 리스트 수
        $page->recode['total'] = $res['cnt']; // 전체 레코드 수
        $page->setPage();
        $page->setUrl(\Request::getQueryString());

        $getField[] = 'ws.memNo, ws.goodsNo, DATE_FORMAT(MAX(ws.regDt), "%Y-%m-%d") as regDtFormat';
        $getField[] = 'm.memId, m.memNm, m.saleAmt, m.loginCnt, DATE_FORMAT(m.approvalDt, "%Y-%m-%d") as approvalDtFormat, DATE_FORMAT(m.lastLoginDt, "%Y-%m-%d") as lastLoginDtFormat';
        $this->db->strField = implode(', ', $getField);
        $this->db->strOrder = 'ws.wishSno desc';
        $this->db->strJoin = 'LEFT JOIN ' . DB_MEMBER . ' as m ON m.memNo = ws.memNo';
        $this->db->strGroup = 'ws.memNo';
        $this->db->strLimit = $page->recode['start'] . ',' . $searchData['pageNum'];

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_WISH_STATISTICS . ' as ws ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);

        $page->recode['amount'] = count($getData);
        $page->setPage();

        return gd_htmlspecialchars_stripslashes($getData);
    }

    /**
     * 상품분석 > 장바구니 분석 > 저장
     * setCartStatistics
     *
     * @param $cartData   cartSno / siteKey / mallSno / memNo / goodsNo / goodsCnt / optionSno / addGoodsNo / addGoodsCnt / optionText / orderFl
     *
     * @return array
     * @throws \Exception
     */
    public function setCartStatistics($cartData)
    {
        if ($cartData['cartSno'] > 0) {
            $checkSQL= "SELECT cartSno FROM " . DB_CART_STATISTICS . " WHERE `cartSno`=?";
            $this->db->bind_param_push($arrBindCheck, 'i', $cartData['cartSno']);
            $checkData = $this->db->query_fetch($checkSQL, $arrBindCheck);
            if (count($checkData) > 0) {
                $arrBind = [];
                $strSQL = "UPDATE " . DB_CART_STATISTICS . " SET `mallSno` = ?, `siteKey` = ?, `memNo` = ?, `goodsNo` = ?, `goodsCnt` = ?, `optionSno` = ?, `addGoodsNo` = ?, `addGoodsCnt` = ?, `optionText` = ?, `orderFl` = ?, `regDt`=now() WHERE `cartSno`=?";
                $this->db->bind_param_push($arrBind, 'i', $cartData['mallSno']);
                $this->db->bind_param_push($arrBind, 's', $cartData['siteKey']);
                $this->db->bind_param_push($arrBind, 'i', $cartData['memNo']);
                $this->db->bind_param_push($arrBind, 'i', $cartData['goodsNo']);
                $this->db->bind_param_push($arrBind, 'i', $cartData['goodsCnt']);
                $this->db->bind_param_push($arrBind, 'i', $cartData['optionSno']);
                $this->db->bind_param_push($arrBind, 's', $cartData['addGoodsNo']);
                $this->db->bind_param_push($arrBind, 's', $cartData['addGoodsCnt']);
                $this->db->bind_param_push($arrBind, 's', $cartData['optionText']);
                $this->db->bind_param_push($arrBind, 's', $cartData['orderFl']);
                $this->db->bind_param_push($arrBind, 'i', $cartData['cartSno']);
                $this->db->bind_query($strSQL, $arrBind);
                unset($arrBind);
            } else {
                $arrBind = [];
                $strSQL = "INSERT INTO " . DB_CART_STATISTICS . " SET `cartSno`=?, `mallSno` = ?, `siteKey` = ?, `memNo` = ?, `goodsNo` = ?, `goodsCnt` = ?, `optionSno` = ?, `addGoodsNo` = ?, `addGoodsCnt` = ?, `optionText` = ?, `orderFl` = ?, `regDt`=now()";
                $this->db->bind_param_push($arrBind, 'i', $cartData['cartSno']);
                $this->db->bind_param_push($arrBind, 'i', $cartData['mallSno']);
                $this->db->bind_param_push($arrBind, 's', $cartData['siteKey']);
                $this->db->bind_param_push($arrBind, 'i', $cartData['memNo']);
                $this->db->bind_param_push($arrBind, 'i', $cartData['goodsNo']);
                $this->db->bind_param_push($arrBind, 'i', $cartData['goodsCnt']);
                $this->db->bind_param_push($arrBind, 'i', $cartData['optionSno']);
                $this->db->bind_param_push($arrBind, 's', $cartData['addGoodsNo']);
                $this->db->bind_param_push($arrBind, 's', $cartData['addGoodsCnt']);
                $this->db->bind_param_push($arrBind, 's', $cartData['optionText']);
                $this->db->bind_param_push($arrBind, 's', $cartData['orderFl']);
                $this->db->bind_query($strSQL, $arrBind);
                unset($arrBind);
            }
        }
    }

    /**
     * 상품분석 > 장바구니 분석 > 회원 로그인 시 장바구니 업데이트
     * setCartMemberUpdateStatistics
     *
     * @param $cartData   siteKey / memNo
     *
     * @return array
     * @throws \Exception
     */
    public function setCartMemberUpdateStatistics($cartData)
    {
        if ($cartData['memNo'] > 0) {
            $arrBind = [];
            $strSQL = "UPDATE " . DB_CART_STATISTICS . " SET `memNo` = ?, `modDt`=now() WHERE `siteKey` = ?";
            $this->db->bind_param_push($arrBind, 'i', $cartData['memNo']);
            $this->db->bind_param_push($arrBind, 's', $cartData['siteKey']);
            $this->db->bind_query($strSQL, $arrBind);
            unset($arrBind);
        }
    }

    /**
     * 상품분석 > 장바구니 분석 > 주문
     * setCartOrderStatistics
     *
     * @param mixed  $cartSno 장바구니 SNO
     * @param string $orderNo 업데이트 할 주문번호
     *
     * @return array
     * @throws \Exception
     */
    public function setCartOrderStatistics($cartSno, $orderNo)
    {
        if (empty($cartSno) === false) {
            if (is_array($cartSno)) {
                $tmpWhere = [];
                foreach ($cartSno as $sno) {
                    $tmpWhere[] = $this->db->escape($sno);
                }
                $arrWhere[] = 'cartSno IN (' . implode(' , ', $tmpWhere) . ')';
                unset($tmpWhere);
            } elseif (is_numeric($cartSno)) {
                $arrWhere[] = 'cartSno = ' . $cartSno . '';
            }

            $arrBind = [
                'ss',
                $orderNo,
                'y',
            ];
            $this->db->set_update_db(DB_CART_STATISTICS, 'orderNo = ?, orderFl = ?', implode(' AND ', $arrWhere), $arrBind);
        }
    }

    /**
     * 상품분석 > 관심상품 분석 > 저장
     * setWishStatistics
     *
     * @param $wishData   wishSno / mallSno / memNo / goodsNo / goodsCnt / optionSno / addGoodsNo / addGoodsCnt / optionText
     *
     * @return array
     * @throws \Exception
     */
    public function setWishStatistics($wishData)
    {
        if ($wishData['wishSno'] > 0) {
            $arrBind = [];
            $strSQL = "INSERT INTO " . DB_WISH_STATISTICS . " SET `wishSno`=?, `mallSno` = ?, `memNo` = ?, `goodsNo` = ?, `goodsCnt` = ?, `optionSno` = ?, `addGoodsNo` = ?, `addGoodsCnt` = ?, `optionText` = ?, `regDt`=now()";
            $this->db->bind_param_push($arrBind, 'i', $wishData['wishSno']);
            $this->db->bind_param_push($arrBind, 'i', $wishData['mallSno']);
            $this->db->bind_param_push($arrBind, 'i', $wishData['memNo']);
            $this->db->bind_param_push($arrBind, 'i', $wishData['goodsNo']);
            $this->db->bind_param_push($arrBind, 'i', $wishData['goodsCnt']);
            $this->db->bind_param_push($arrBind, 'i', $wishData['optionSno']);
            $this->db->bind_param_push($arrBind, 's', $wishData['addGoodsNo']);
            $this->db->bind_param_push($arrBind, 's', $wishData['addGoodsCnt']);
            $this->db->bind_param_push($arrBind, 's', $wishData['optionText']);
            $this->db->bind_query($strSQL, $arrBind);
            unset($arrBind);
        }
    }

    /**
     * 일별 상품통계 정리
     *
     * @param null $mallSno
     * @param bool $realTimeKey
     *
     * @return bool
     * @throws \Exception
     */
    public function setGoodsStatistics($mallSno = null, $realTimeKey = false)
    {
        if ($realTimeKey) {
            $orderGoods['paymentDtOver'] = $this->orderGoodsPolicy['statisticsDate']->format('Y-m-d H:i:s');
        } else {
            $orderGoods['paymentDt'] = $this->orderGoodsPolicy['statisticsDate']->format('Ymd');
        }
        if ($mallSno > 0) {
            $orderGoods['mallSno'] = $mallSno;
        }
        $orderGoods['goodsType'] = 'goods'; // 추가상품은 제외
        // 주문상품 data 에 주문정보 data 를 join 해서 가져온다.
        $orderGoodsArr = $this->getOrderGoodsInfo(
            $orderGoods,
            'o.mallSno, o.orderTypeFl, o.orderChannelFl,' .
            'og.sno, og.orderNo, og.goodsType, og.cateCd, og.cateAllCd, og.goodsNo, og.paymentDt, og.statisticsGoodsFl, ' .
            'og.goodsCnt, og.goodsPrice, ' .
            'og.optionSno, og.optionInfo, og.optionPrice, ' .
            'og.linkMainTheme'
        );
        unset($orderGoods);

        $goodsStatistics = [];
        $orderNoDiff = [];
        $goodsOptionStatistics = [];
        $orderNoOptionDiff = [];
        $goodsMainStatistics = [];
        foreach ($orderGoodsArr as $orderGoodsKey => $orderGoodsVal) {
            $paymentDt = new DateTime($orderGoodsVal['paymentDt']);
            if ($orderGoodsVal['statisticsGoodsFl'] == 'y') {
                continue;
            }
            if ($orderGoodsVal['orderChannelFl'] == 'etc') {
                continue;
            }

            // 상품별 통계
            if (!$goodsStatistics[$paymentDt->format('Ymd')][$orderGoodsVal['goodsNo']][$orderGoodsVal['orderTypeFl']]['orderCnt']) {
                $goodsStatistics[$paymentDt->format('Ymd')][$orderGoodsVal['goodsNo']][$orderGoodsVal['orderTypeFl']]['orderCnt'] = 0;
            }
            if (!$goodsStatistics[$paymentDt->format('Ymd')][$orderGoodsVal['goodsNo']][$orderGoodsVal['orderTypeFl']]['orderNo']) {
                $goodsStatistics[$paymentDt->format('Ymd')][$orderGoodsVal['goodsNo']][$orderGoodsVal['orderTypeFl']]['orderNo'] = $orderGoodsVal['orderNo'];
            }
            $nowGoodsOrderNo = $goodsStatistics[$paymentDt->format('Ymd')][$orderGoodsVal['goodsNo']][$orderGoodsVal['orderTypeFl']]['orderNo'];
            $prevGoodsOrderNo = $orderNoDiff[$paymentDt->format('Ymd')][$orderGoodsVal['goodsNo']][$orderGoodsVal['orderTypeFl']]['orderNo'];
            if ($nowGoodsOrderNo == $prevGoodsOrderNo) {
                $goodsStatistics[$paymentDt->format('Ymd')][$orderGoodsVal['goodsNo']][$orderGoodsVal['orderTypeFl']]['orderCnt'];
            } else {
                $goodsStatistics[$paymentDt->format('Ymd')][$orderGoodsVal['goodsNo']][$orderGoodsVal['orderTypeFl']]['orderCnt'] += 1;
            }
            $goodsStatistics[$paymentDt->format('Ymd')][$orderGoodsVal['goodsNo']][$orderGoodsVal['orderTypeFl']] = [
                'cateCd' => $orderGoodsVal['cateCd'],
                'cateAllCd' => $orderGoodsVal['cateAllCd'],
                'orderCnt' => $goodsStatistics[$paymentDt->format('Ymd')][$orderGoodsVal['goodsNo']][$orderGoodsVal['orderTypeFl']]['orderCnt'],
                'goodsCnt' => $goodsStatistics[$paymentDt->format('Ymd')][$orderGoodsVal['goodsNo']][$orderGoodsVal['orderTypeFl']]['goodsCnt'] + $orderGoodsVal['goodsCnt'],
                'goodsPrice' => $orderGoodsVal['goodsPrice'],
                'optionPrice' => $orderGoodsVal['optionPrice'],
                'optionInfo' => $orderGoodsVal['optionInfo'],
                'mallSno' => $orderGoodsVal['mallSno'],
            ];
            $orderNoDiff[$paymentDt->format('Ymd')][$orderGoodsVal['goodsNo']][$orderGoodsVal['orderTypeFl']]['orderNo'] = $orderGoodsVal['orderNo'];

            // 옵션별 통계
            if (!$goodsOptionStatistics[$paymentDt->format('Ymd')][$orderGoodsVal['goodsNo']][$orderGoodsVal['optionSno']][$orderGoodsVal['orderTypeFl']]['orderCnt']) {
                $goodsOptionStatistics[$paymentDt->format('Ymd')][$orderGoodsVal['goodsNo']][$orderGoodsVal['optionSno']][$orderGoodsVal['orderTypeFl']]['orderCnt'] = 0;
            }
            if (!$goodsOptionStatistics[$paymentDt->format('Ymd')][$orderGoodsVal['goodsNo']][$orderGoodsVal['optionSno']][$orderGoodsVal['orderTypeFl']]['orderNo']) {
                $goodsOptionStatistics[$paymentDt->format('Ymd')][$orderGoodsVal['goodsNo']][$orderGoodsVal['optionSno']][$orderGoodsVal['orderTypeFl']]['orderNo'] = $orderGoodsVal['orderNo'];
            }
            $nowGoodsOrderNo = $goodsOptionStatistics[$paymentDt->format('Ymd')][$orderGoodsVal['goodsNo']][$orderGoodsVal['optionSno']][$orderGoodsVal['orderTypeFl']]['orderNo'];
            $prevGoodsOrderNo = $orderNoOptionDiff[$paymentDt->format('Ymd')][$orderGoodsVal['goodsNo']][$orderGoodsVal['optionSno']][$orderGoodsVal['orderTypeFl']]['orderNo'];
            if ($nowGoodsOrderNo == $prevGoodsOrderNo) {
                $goodsOptionStatistics[$paymentDt->format('Ymd')][$orderGoodsVal['goodsNo']][$orderGoodsVal['optionSno']][$orderGoodsVal['orderTypeFl']]['orderCnt'];
            } else {
                $goodsOptionStatistics[$paymentDt->format('Ymd')][$orderGoodsVal['goodsNo']][$orderGoodsVal['optionSno']][$orderGoodsVal['orderTypeFl']]['orderCnt'] += 1;
            }
            $goodsOptionStatistics[$paymentDt->format('Ymd')][$orderGoodsVal['goodsNo']][$orderGoodsVal['optionSno']][$orderGoodsVal['orderTypeFl']] = [
                'cateCd' => $orderGoodsVal['cateCd'],
                'cateAllCd' => $orderGoodsVal['cateAllCd'],
                'orderCnt' => $goodsOptionStatistics[$paymentDt->format('Ymd')][$orderGoodsVal['goodsNo']][$orderGoodsVal['optionSno']][$orderGoodsVal['orderTypeFl']]['orderCnt'],
                'goodsCnt' => $goodsOptionStatistics[$paymentDt->format('Ymd')][$orderGoodsVal['goodsNo']][$orderGoodsVal['optionSno']][$orderGoodsVal['orderTypeFl']]['goodsCnt'] + $orderGoodsVal['goodsCnt'],
                'goodsPrice' => $orderGoodsVal['goodsPrice'],
                'optionPrice' => $orderGoodsVal['optionPrice'],
                'optionInfo' => $orderGoodsVal['optionInfo'],
                'mallSno' => $orderGoodsVal['mallSno'],
            ];
            $orderNoOptionDiff[$paymentDt->format('Ymd')][$orderGoodsVal['goodsNo']][$orderGoodsVal['optionSno']][$orderGoodsVal['orderTypeFl']]['orderNo'] = $orderGoodsVal['orderNo'];

            // 메인 분류 통계 용
            $orderNoMainDiff = [];
            if (empty($orderGoodsVal['linkMainTheme']) === false) {
                // 메인 분류 종류 저장
                $linkMainArr = explode(STR_DIVISION, $orderGoodsVal['linkMainTheme']);
                if ($linkMainArr[0] > 0) {
                    if (!$goodsMainStatistics[$paymentDt->format('Ymd')][$orderGoodsVal['goodsNo']][$orderGoodsVal['orderTypeFl']][$orderGoodsVal['linkMainTheme']]['orderCnt']) {
                        $goodsMainStatistics[$paymentDt->format('Ymd')][$orderGoodsVal['goodsNo']][$orderGoodsVal['orderTypeFl']][$orderGoodsVal['linkMainTheme']]['orderCnt'] = 0;
                    }
                    if (!$goodsMainStatistics[$paymentDt->format('Ymd')][$orderGoodsVal['goodsNo']][$orderGoodsVal['orderTypeFl']][$orderGoodsVal['linkMainTheme']]['orderNo']) {
                        $goodsMainStatistics[$paymentDt->format('Ymd')][$orderGoodsVal['goodsNo']][$orderGoodsVal['orderTypeFl']][$orderGoodsVal['linkMainTheme']]['orderNo'] = $orderGoodsVal['orderNo'];
                    }
                    $nowMainGoodsOrderNo = $goodsMainStatistics[$paymentDt->format('Ymd')][$orderGoodsVal['goodsNo']][$orderGoodsVal['orderTypeFl']][$orderGoodsVal['linkMainTheme']]['orderNo'];
                    $prevMainGoodsOrderNo = $orderNoMainDiff[$paymentDt->format('Ymd')][$orderGoodsVal['goodsNo']][$orderGoodsVal['orderTypeFl']][$orderGoodsVal['linkMainTheme']]['orderNo'];
                    if ($nowMainGoodsOrderNo == $prevMainGoodsOrderNo) {
                        $goodsMainStatistics[$paymentDt->format('Ymd')][$orderGoodsVal['goodsNo']][$orderGoodsVal['orderTypeFl']][$orderGoodsVal['linkMainTheme']]['orderCnt'];
                    } else {
                        $goodsMainStatistics[$paymentDt->format('Ymd')][$orderGoodsVal['goodsNo']][$orderGoodsVal['orderTypeFl']][$orderGoodsVal['linkMainTheme']]['orderCnt'] += 1;
                    }
                    $goodsMainStatistics[$paymentDt->format('Ymd')][$orderGoodsVal['goodsNo']][$orderGoodsVal['orderTypeFl']][$orderGoodsVal['linkMainTheme']] = [
                        'cateCd' => $orderGoodsVal['cateCd'],
                        'cateAllCd' => $orderGoodsVal['cateAllCd'],
                        'orderCnt' => $goodsMainStatistics[$paymentDt->format('Ymd')][$orderGoodsVal['goodsNo']][$orderGoodsVal['orderTypeFl']][$orderGoodsVal['linkMainTheme']]['orderCnt'],
                        'goodsCnt' => $goodsMainStatistics[$paymentDt->format('Ymd')][$orderGoodsVal['goodsNo']][$orderGoodsVal['orderTypeFl']][$orderGoodsVal['linkMainTheme']]['goodsCnt'] + $orderGoodsVal['goodsCnt'],
                        'goodsPrice' => $orderGoodsVal['goodsPrice'],
                        'mallSno' => $orderGoodsVal['mallSno'],
                    ];
                    $orderNoMainDiff[$paymentDt->format('Ymd')][$orderGoodsVal['goodsNo']][$orderGoodsVal['orderTypeFl']][$orderGoodsVal['linkMainTheme']]['orderNo'] = $orderGoodsVal['orderNo'];
                }
            }

            $arrBind = [];
            $strSQL = "UPDATE " . DB_ORDER_GOODS . " SET `statisticsGoodsFl` = ? WHERE `sno` = ?";
            $this->db->bind_param_push($arrBind, 's', 'y');
            $this->db->bind_param_push($arrBind, 'i', $orderGoodsVal['sno']);
            $this->db->bind_query($strSQL, $arrBind);
            unset($arrBind);
        }

        // 상품별 통계
        foreach ($goodsStatistics as $dateKey => $dateVal) {
            $orderGoodsDay['goodsYMD'] = $dateKey;
            foreach ($dateVal as $goodsKey => $goodsVal) {
                foreach ($goodsVal as $key => $val) {
                    // 대표 카테고리
                    // 중복 체크 하여 이미 값이 있으면 continue
                    $goodsData['goodsYMD'] = $orderGoodsDay['goodsYMD'];
                    $goodsData['mallSno'] = $val['mallSno'];
                    $goodsData['goodsNo'] = $goodsKey;
                    if (empty($val['cateCd']) === true) {
                        $cateCd = 'noCate';
                    } else {
                        $cateCd = $val['cateCd'];
                    }
                    $goodsData['cateCdEqual'] = $cateCd;
                    $goodsData['orderTypeFl'] = $key;
                    $goodsYMD = $this->getGoodsStatisticsInfo($goodsData, 'gs.goodsYMD', null, true);
                    unset($goodsData);

                    if (count($goodsYMD) > 0) {
                        $arrBind = [];
                        $strSQL = "UPDATE " . DB_GOODS_STATISTICS . " SET `orderCnt` = `orderCnt` + ?, `goodsCnt` = `goodsCnt` + ?, `goodsPrice` = ?, `modDt` = now() WHERE `goodsYMD` = ? AND `mallSno` = ? AND `goodsNo` = ? AND `orderTypeFl` = ? AND `cateCd` = ?";
                        $this->db->bind_param_push($arrBind, 'i', $val['orderCnt']);
                        $this->db->bind_param_push($arrBind, 'i', $val['goodsCnt']);
                        $this->db->bind_param_push($arrBind, 'd', $val['goodsPrice']);
                        $this->db->bind_param_push($arrBind, 'i', $orderGoodsDay['goodsYMD']);
                        $this->db->bind_param_push($arrBind, 'i', $val['mallSno']);
                        $this->db->bind_param_push($arrBind, 'i', $goodsKey);
                        $this->db->bind_param_push($arrBind, 's', $key);
                        $this->db->bind_param_push($arrBind, 's', $val['cateCd']);
                        $this->db->bind_query($strSQL, $arrBind);
                        unset($arrBind);
                    } else {
                        $arrBind = [];
                        $strSQL = "INSERT INTO " . DB_GOODS_STATISTICS . " SET `goodsYMD`=?, `mallSno` = ?, `goodsNo` = ?, `orderTypeFl` = ?, `cateCd` = ?, `orderCnt` = ?, `goodsCnt` = ?, `goodsPrice` = ?, `regDt`=now()";
                        $this->db->bind_param_push($arrBind, 'i', $orderGoodsDay['goodsYMD']);
                        $this->db->bind_param_push($arrBind, 'i', $val['mallSno']);
                        $this->db->bind_param_push($arrBind, 'i', $goodsKey);
                        $this->db->bind_param_push($arrBind, 's', $key);
                        $this->db->bind_param_push($arrBind, 's', $val['cateCd']);
                        $this->db->bind_param_push($arrBind, 'i', $val['orderCnt']);
                        $this->db->bind_param_push($arrBind, 'i', $val['goodsCnt']);
                        $this->db->bind_param_push($arrBind, 'd', $val['goodsPrice']);
                        $this->db->bind_query($strSQL, $arrBind);
                        unset($arrBind);
                    }

                    // 하위 카테고리들
                    $cateAllCd = json_decode($val['cateAllCd'], true);
                    if (count($cateAllCd) > 0) {
                        foreach ($cateAllCd as $cateAllKey => $cateAllVal) {
                            if ($cateAllVal['cateLinkFl'] !== 'y') {
                                continue;
                            }
                            // 중복 체크 하여 이미 값이 있으면 continue
                            $goodsData['goodsYMD'] = $orderGoodsDay['goodsYMD'];
                            $goodsData['mallSno'] = $val['mallSno'];
                            $goodsData['goodsNo'] = $goodsKey;
                            $goodsData['cateCdEqual'] = $cateAllVal['cateCd'];
                            $goodsData['orderTypeFl'] = $key;
                            $goodsYMD = $this->getGoodsCategoryStatisticsInfo($goodsData, 'gcs.goodsYMD', null, true);
                            unset($goodsData);

                            if (count($goodsYMD) > 0) {
                                $arrBind = [];
                                $strSQL = "UPDATE " . DB_GOODS_CATEGORY_STATISTICS . " SET `orderCnt` = `orderCnt` + ?, `goodsCnt` = `goodsCnt` + ?, `goodsPrice` = ?, `modDt` = now() WHERE `goodsYMD` = ? AND `mallSno` = ? AND `goodsNo` = ? AND `orderTypeFl` = ? AND `cateCd` = ?";
                                $this->db->bind_param_push($arrBind, 'i', $val['orderCnt']);
                                $this->db->bind_param_push($arrBind, 'i', $val['goodsCnt']);
                                $this->db->bind_param_push($arrBind, 'd', $val['goodsPrice']);
                                $this->db->bind_param_push($arrBind, 'i', $orderGoodsDay['goodsYMD']);
                                $this->db->bind_param_push($arrBind, 'i', $val['mallSno']);
                                $this->db->bind_param_push($arrBind, 'i', $goodsKey);
                                $this->db->bind_param_push($arrBind, 's', $key);
                                $this->db->bind_param_push($arrBind, 's', $cateAllVal['cateCd']);
                                $this->db->bind_query($strSQL, $arrBind);
                                unset($arrBind);
                            } else {
                                $arrBind = [];
                                $strSQL = "INSERT INTO " . DB_GOODS_CATEGORY_STATISTICS . " SET `goodsYMD`=?, `mallSno` = ?, `goodsNo` = ?, `orderTypeFl` = ?, `cateCd` = ?, `orderCnt` = ?, `goodsCnt` = ?, `goodsPrice` = ?, `regDt`=now()";
                                $this->db->bind_param_push($arrBind, 'i', $orderGoodsDay['goodsYMD']);
                                $this->db->bind_param_push($arrBind, 'i', $val['mallSno']);
                                $this->db->bind_param_push($arrBind, 'i', $goodsKey);
                                $this->db->bind_param_push($arrBind, 's', $key);
                                $this->db->bind_param_push($arrBind, 's', $cateAllVal['cateCd']);
                                $this->db->bind_param_push($arrBind, 'i', $val['orderCnt']);
                                $this->db->bind_param_push($arrBind, 'i', $val['goodsCnt']);
                                $this->db->bind_param_push($arrBind, 'd', $val['goodsPrice']);
                                $this->db->bind_query($strSQL, $arrBind);
                                unset($arrBind);
                            }
                        }
                    }
                }
            }
        }

        // 옵션별 통계
        foreach ($goodsOptionStatistics as $dateKey => $dateVal) {
            $orderGoodsDay['goodsYMD'] = $dateKey;
            foreach ($dateVal as $goodsKey => $goodsVal) {
                foreach ($goodsVal as $optionKey => $optionVal) {
                    foreach ($optionVal as $key => $val) {
                        // 대표 카테고리
                        // 중복 체크 하여 이미 값이 있으면 continue
                        $goodsData['goodsYMD'] = $orderGoodsDay['goodsYMD'];
                        $goodsData['mallSno'] = $val['mallSno'];
                        $goodsData['goodsNo'] = $goodsKey;
                        $goodsData['optionSno'] = $optionKey;
                        if (empty($val['cateCd']) === true) {
                            $cateCd = 'noCate';
                        } else {
                            $cateCd = $val['cateCd'];
                        }
                        $goodsData['cateCdEqual'] = $cateCd;
                        $goodsData['orderTypeFl'] = $key;
                        $goodsYMD = $this->getGoodsOptionStatisticsInfo($goodsData, 'gs.goodsYMD', null, true);
                        unset($goodsData);

                        if (count($goodsYMD) > 0) {
                            $arrBind = [];
                            $strSQL = "UPDATE " . DB_GOODS_OPTION_STATISTICS . " SET `orderCnt` = `orderCnt` + ?, `goodsCnt` = `goodsCnt` + ?, `goodsPrice` = ?, `optionPrice` = ?, `modDt` = now() WHERE `goodsYMD` = ? AND `mallSno` = ? AND `goodsNo` = ? AND `optionSno` = ? AND `orderTypeFl` = ? AND `cateCd` = ?";
                            $this->db->bind_param_push($arrBind, 'i', $val['orderCnt']);
                            $this->db->bind_param_push($arrBind, 'i', $val['goodsCnt']);
                            $this->db->bind_param_push($arrBind, 'd', $val['goodsPrice']);
                            $this->db->bind_param_push($arrBind, 'd', $val['optionPrice']);
                            $this->db->bind_param_push($arrBind, 'i', $orderGoodsDay['goodsYMD']);
                            $this->db->bind_param_push($arrBind, 'i', $val['mallSno']);
                            $this->db->bind_param_push($arrBind, 'i', $goodsKey);
                            $this->db->bind_param_push($arrBind, 'i', $optionKey);
                            $this->db->bind_param_push($arrBind, 's', $key);
                            $this->db->bind_param_push($arrBind, 's', $val['cateCd']);
                            $this->db->bind_query($strSQL, $arrBind);
                            unset($arrBind);
                        } else {
                            $arrBind = [];
                            $strSQL = "INSERT INTO " . DB_GOODS_OPTION_STATISTICS . " SET `goodsYMD`=?, `mallSno` = ?, `goodsNo` = ?, `optionSno` = ?, `orderTypeFl` = ?, `cateCd` = ?, `orderCnt` = ?, `goodsCnt` = ?, `goodsPrice` = ?, `optionInfo` = ?, `optionPrice` = ?, `regDt`=now()";
                            $this->db->bind_param_push($arrBind, 'i', $orderGoodsDay['goodsYMD']);
                            $this->db->bind_param_push($arrBind, 'i', $val['mallSno']);
                            $this->db->bind_param_push($arrBind, 'i', $goodsKey);
                            $this->db->bind_param_push($arrBind, 'i', $optionKey);
                            $this->db->bind_param_push($arrBind, 's', $key);
                            $this->db->bind_param_push($arrBind, 's', $val['cateCd']);
                            $this->db->bind_param_push($arrBind, 'i', $val['orderCnt']);
                            $this->db->bind_param_push($arrBind, 'i', $val['goodsCnt']);
                            $this->db->bind_param_push($arrBind, 'd', $val['goodsPrice']);
                            $this->db->bind_param_push($arrBind, 's', $val['optionInfo']);
                            $this->db->bind_param_push($arrBind, 'd', $val['optionPrice']);
                            $this->db->bind_query($strSQL, $arrBind);
                            unset($arrBind);
                        }

                        // 하위 카테고리들
                        $cateAllCd = json_decode($val['cateAllCd'], true);
                        if (count($cateAllCd) > 0) {
                            foreach ($cateAllCd as $cateAllKey => $cateAllVal) {
                                if ($cateAllVal['cateLinkFl'] !== 'y') {
                                    continue;
                                }
                                // 중복 체크 하여 이미 값이 있으면 continue
                                $goodsData['goodsYMD'] = $orderGoodsDay['goodsYMD'];
                                $goodsData['mallSno'] = $val['mallSno'];
                                $goodsData['goodsNo'] = $goodsKey;
                                $goodsData['optionSno'] = $optionKey;
                                $goodsData['cateCdEqual'] = $cateAllVal['cateCd'];
                                $goodsData['orderTypeFl'] = $key;
                                $goodsYMD = $this->getGoodsOptionCategoryStatisticsInfo($goodsData, 'gcs.goodsYMD', null, true);
                                unset($goodsData);

                                if (count($goodsYMD) > 0) {
                                    $arrBind = [];
                                    $strSQL = "UPDATE " . DB_GOODS_OPTION_CATEGORY_STATISTICS . " SET `orderCnt` = `orderCnt` + ?, `goodsCnt` = `goodsCnt` + ?, `goodsPrice` = ?, `optionPrice` = ?, `modDt` = now() WHERE `goodsYMD` = ? AND `mallSno` = ? AND `goodsNo` = ? AND `optionSno` = ? AND `orderTypeFl` = ? AND `cateCd` = ?";
                                    $this->db->bind_param_push($arrBind, 'i', $val['orderCnt']);
                                    $this->db->bind_param_push($arrBind, 'i', $val['goodsCnt']);
                                    $this->db->bind_param_push($arrBind, 'd', $val['goodsPrice']);
                                    $this->db->bind_param_push($arrBind, 'd', $val['optionPrice']);
                                    $this->db->bind_param_push($arrBind, 'i', $orderGoodsDay['goodsYMD']);
                                    $this->db->bind_param_push($arrBind, 'i', $val['mallSno']);
                                    $this->db->bind_param_push($arrBind, 'i', $goodsKey);
                                    $this->db->bind_param_push($arrBind, 'i', $optionKey);
                                    $this->db->bind_param_push($arrBind, 's', $key);
                                    $this->db->bind_param_push($arrBind, 's', $cateAllVal['cateCd']);
                                    $this->db->bind_query($strSQL, $arrBind);
                                    unset($arrBind);
                                } else {
                                    $arrBind = [];
                                    $strSQL = "INSERT INTO " . DB_GOODS_OPTION_CATEGORY_STATISTICS . " SET `goodsYMD`=?, `mallSno` = ?, `goodsNo` = ?, `optionSno` = ?, `orderTypeFl` = ?, `cateCd` = ?, `orderCnt` = ?, `goodsCnt` = ?, `goodsPrice` = ?, `optionInfo` = ?, `optionPrice` = ?, `regDt`=now()";
                                    $this->db->bind_param_push($arrBind, 'i', $orderGoodsDay['goodsYMD']);
                                    $this->db->bind_param_push($arrBind, 'i', $val['mallSno']);
                                    $this->db->bind_param_push($arrBind, 'i', $goodsKey);
                                    $this->db->bind_param_push($arrBind, 'i', $optionKey);
                                    $this->db->bind_param_push($arrBind, 's', $key);
                                    $this->db->bind_param_push($arrBind, 's', $cateAllVal['cateCd']);
                                    $this->db->bind_param_push($arrBind, 'i', $val['orderCnt']);
                                    $this->db->bind_param_push($arrBind, 'i', $val['goodsCnt']);
                                    $this->db->bind_param_push($arrBind, 'd', $val['goodsPrice']);
                                    $this->db->bind_param_push($arrBind, 's', $val['optionInfo']);
                                    $this->db->bind_param_push($arrBind, 'd', $val['optionPrice']);
                                    $this->db->bind_query($strSQL, $arrBind);
                                    unset($arrBind);
                                }
                            }
                        }
                    }
                }
            }
        }

        // 메인분류 통계
        foreach ($goodsMainStatistics as $dateKey => $dateVal) {
            $orderGoodsDay['goodsYMD'] = $dateKey;
            foreach ($dateVal as $goodsKey => $goodsVal) {
                foreach ($goodsVal as $orderTypeKey => $orderTypeVal) {
                    foreach ($orderTypeVal as $key => $val) {
                        if (empty($key) === false) {
                            // 메인 분류 종류 저장
                            $linkMainArr = explode(STR_DIVISION, $key);
                            if ($linkMainArr[0] > 0) {
                                $linkMainKey['themeSno'] = $linkMainArr[0];
                                $linkMainKey['themeNm'] = $linkMainArr[1];
                                $linkMainKey['themeDevice'] = $linkMainArr[2];
                                $linkMainData = $this->getLinkMainStatisticsInfo($linkMainKey);
                                unset($linkMainKey);
                                if (count($linkMainData) > 0) {
                                    // 이미 등록된 메인분류
                                } else {
                                    $arrBind = [];
                                    $strSQL = "INSERT INTO " . DB_LINK_MAIN_STATISTICS . " SET `themeSno`=?, `themeNm` = ?, `themeDevice` = ?, `regDt`=now()";
                                    $this->db->bind_param_push($arrBind, 'i', $linkMainArr[0]);
                                    $this->db->bind_param_push($arrBind, 's', $linkMainArr[1]);
                                    $this->db->bind_param_push($arrBind, 's', $linkMainArr[2]);
                                    $this->db->bind_query($strSQL, $arrBind);
                                    unset($arrBind);
                                }
                                // 메인 분류 데이터 저장
                                // 중복 체크 하여 이미 값이 있으면 continue
                                $goodsMainKey['goodsYMD'] = $orderGoodsDay['goodsYMD'];
                                $goodsMainKey['mallSno'] = $val['mallSno'];
                                $goodsMainKey['goodsNo'] = $goodsKey;
                                $goodsMainKey['orderTypeFl'] = $orderTypeKey;
                                $goodsMainKey['themeSno'] = $linkMainArr[0];
                                $goodsMainKey['themeNm'] = $linkMainArr[1];
                                $goodsMainKey['themeDevice'] = $linkMainArr[2];
                                $goodsMainData = $this->getGoodsMainStatisticsInfo($goodsMainKey, 'gms.goodsYMD', null);
                                unset($goodsMainKey);
                                if (count($goodsMainData) > 0) {
                                    $arrBind = [];
                                    $strSQL = "UPDATE " . DB_GOODS_MAIN_STATISTICS . " SET `orderCnt` = `orderCnt` + ?, `goodsCnt` = `goodsCnt` + ?, `goodsPrice` = ?, `modDt` = now() WHERE `goodsYMD` = ? AND `mallSno` = ? AND `goodsNo` = ? AND `orderTypeFl` = ? AND `themeSno` = ? AND `themeNm` = ? AND `themeDevice` = ?";
                                    $this->db->bind_param_push($arrBind, 'i', $val['orderCnt']);
                                    $this->db->bind_param_push($arrBind, 'i', $val['goodsCnt']);
                                    $this->db->bind_param_push($arrBind, 'd', $val['goodsPrice']);
                                    $this->db->bind_param_push($arrBind, 'i', $orderGoodsDay['goodsYMD']);
                                    $this->db->bind_param_push($arrBind, 'i', $val['mallSno']);
                                    $this->db->bind_param_push($arrBind, 'i', $goodsKey);
                                    $this->db->bind_param_push($arrBind, 's', $orderTypeKey);
                                    $this->db->bind_param_push($arrBind, 'i', $linkMainArr[0]);
                                    $this->db->bind_param_push($arrBind, 's', $linkMainArr[1]);
                                    $this->db->bind_param_push($arrBind, 's', $linkMainArr[2]);
                                    $this->db->bind_query($strSQL, $arrBind);
                                    unset($arrBind);
                                } else {
                                    $arrBind = [];
                                    $strSQL = "INSERT INTO " . DB_GOODS_MAIN_STATISTICS . " SET `goodsYMD`=?, `mallSno` = ?, `goodsNo` = ?, `orderTypeFl` = ?, `themeSno` = ?, `themeNm` = ?, `themeDevice` = ?, `cateCd` = ?, `orderCnt` = ?, `goodsCnt` = ?, `goodsPrice` = ?, `regDt`=now()";
                                    $this->db->bind_param_push($arrBind, 'i', $orderGoodsDay['goodsYMD']);
                                    $this->db->bind_param_push($arrBind, 'i', $val['mallSno']);
                                    $this->db->bind_param_push($arrBind, 'i', $goodsKey);
                                    $this->db->bind_param_push($arrBind, 's', $orderTypeKey);
                                    $this->db->bind_param_push($arrBind, 'i', $linkMainArr[0]);
                                    $this->db->bind_param_push($arrBind, 's', $linkMainArr[1]);
                                    $this->db->bind_param_push($arrBind, 's', $linkMainArr[2]);
                                    $this->db->bind_param_push($arrBind, 's', $val['cateCd']);
                                    $this->db->bind_param_push($arrBind, 'i', $val['orderCnt']);
                                    $this->db->bind_param_push($arrBind, 'i', $val['goodsCnt']);
                                    $this->db->bind_param_push($arrBind, 'd', $val['goodsPrice']);
                                    $this->db->bind_query($strSQL, $arrBind);
                                    unset($arrBind);
                                }
                            }
                        }
                    }
                }
            }
        }

        return true;
    }
}
