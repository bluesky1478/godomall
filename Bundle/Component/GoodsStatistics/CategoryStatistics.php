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

namespace Bundle\Component\GoodsStatistics;

use Component\Database\DBTableField;
use Component\MemberStatistics\AbstractStatistics;
use Exception;
use Framework\Utility\ArrayUtils;
use Logger;

/**
 * Class 카테고리 분석 통계
 * @package Bundle\Component\GoodsStatistics
 * @author  yjwee
 */
class CategoryStatistics extends \Component\MemberStatistics\AbstractStatistics
{
    /**
     * @inheritDoc
     */
    public function __construct()
    {
        parent::__construct();
        $this->tableFunctionName = 'tableCategoryStatistics';
    }

    /**
     * es_categoryStatistics 테이블 데이터 입력 함수
     *
     * @param CategoryStatisticsVO $vo
     *
     * @return int|string
     */
    public function insert(CategoryStatisticsVO $vo)
    {
        Logger::info(__METHOD__);
        $arrBind = $this->db->get_binding(DBTableField::tableCategoryStatistics(), $vo->toArray(), 'insert');
        $this->db->set_insert_db(DB_CATEGORY_STATISTICS, $arrBind['param'], $arrBind['bind'], 'y');

        return $this->db->insert_id();
    }

    /**
     * 다수 데이터 입력
     * ex) VALUES(), (), ()
     *
     * @param array $array
     *
     * @return mixed
     */
    public function inserts(array $array)
    {
        $tableModel = DBTableField::tableModel($this->tableFunctionName);
        $this->db->setMultipleInsertDb(DB_CATEGORY_STATISTICS, array_keys($tableModel), $array);
    }

    /**
     * es_categoryStatistics 테이블 데이터 수정 함수
     *
     * @param CategoryStatisticsVO $vo
     *
     * @return CategoryStatisticsVO
     */
    public function update(CategoryStatisticsVO $vo)
    {
        Logger::info(__METHOD__);
        $excludeField = 'keyword,memNo,os';
        $arrBind = $this->db->get_binding(DBTableField::tableCategoryStatistics(), $vo->toArray(), 'update', null, explode(',', $excludeField));
        $this->db->bind_param_push($arrBind['bind'], 'i', $vo->getSno());
        $this->db->set_update_db(DB_CATEGORY_STATISTICS, $arrBind['param'], 'sno = ?', $arrBind['bind']);

        return $vo;
    }

    /**
     * es_categoryStatistics 테이블 데이터 조회 함수
     *
     * @param        $sno
     * @param string $column
     *
     * @return array|object
     */
    public function select($sno, $column = '*')
    {
        Logger::info(__METHOD__);
        $arrBind = [];
        $this->db->bind_param_push($arrBind, 'i', $sno);

        return $this->db->query_fetch('SELECT ' . $column . ' FROM ' . DB_CATEGORY_STATISTICS . 'WHERE sno=?', $arrBind, false);
    }

    /**
     * 순위 리스트
     *
     * @param array $requestParams
     * @param int   $offset
     * @param int   $limit
     *
     * @return array
     * @throws Exception
     */
    public function getCategoryOrderRankList(array $requestParams, $offset = 0, $limit = 20)
    {
        return $this->lists($requestParams, $offset, $limit);
    }

    /**
     * 리스트 조회 함수
     *
     * @param array $requestParams
     * @param       $offset
     * @param       $limit
     *
     * @return array
     */
    public function lists(array $requestParams, $offset, $limit)
    {
        $arrBind = $arrWhere = [];

        $requestParams['cateCd'] = ArrayUtils::last($requestParams['cateGoods']);
        $this->db->bindParameter('cateCd', $requestParams, $arrBind, $arrWhere, $this->tableFunctionName);
        $this->db->bindParameterByDateTimeRange('regDt', $requestParams, $arrBind, $arrWhere, $this->tableFunctionName);

        $this->db->strField = 'sno, cateCd, cateNm, SUM(totalPrice) AS totalPrice, SUM(pcPrice) AS pcPrice, SUM(mobilePrice) AS mobilePrice, SUM(totalOrderGoodsCount) AS totalOrderGoodsCount, SUM(pcOrderGoodsCount) AS pcOrderGoodsCount, SUM(mobileOrderGoodsCount) AS mobileOrderGoodsCount, SUM(totalOrderCount) AS totalOrderCount, SUM(pcOrderCount) AS pcOrderCount, SUM(mobileOrderCount) AS mobileOrderCount, regDt, modDt';
        $this->db->strWhere = implode(' AND ', $arrWhere);
        $this->db->strGroup = 'cateCd';
        $this->db->strOrder = 'totalPrice DESC';
        if (is_null($offset) === false && is_null($limit) === false) {
            $this->db->strLimit = ($offset - 1) * $limit . ', ' . $limit;
        }

        $arrQuery = $this->db->query_complete();
        $query = 'SELECT ' . array_shift($arrQuery) . ' FROM ' . DB_CATEGORY_STATISTICS . implode(' ', $arrQuery);
        \Logger::info($query, $arrBind);
        $resultSet = $this->db->query_fetch($query, $arrBind, true);
        $result = [];
        foreach ($resultSet as $key => $value) {
            $result[] = new CategoryStatisticsVO($value);
        }

        unset($arrBind, $arrWhere, $arrQuery, $resultSet);

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function totalStatistics(array $requestParams)
    {
        $arrBind = $arrWhere = [];

        $requestParams['cateCd'] = ArrayUtils::last($requestParams['cateGoods']);
        $this->db->bindParameter('cateCd', $requestParams, $arrBind, $arrWhere, $this->tableFunctionName);
        $this->db->bindParameterByDateTimeRange('regDt', $requestParams, $arrBind, $arrWhere, $this->tableFunctionName);

        $this->db->strField = 'SUM(totalPrice) as totalPrice, SUM(pcPrice) as pcPrice, SUM(mobilePrice) as mobilePrice';
        $this->db->strField .= ', SUM(totalOrderGoodsCount) as totalOrderGoodsCount, SUM(pcOrderGoodsCount) as pcOrderGoodsCount, SUM(mobileOrderGoodsCount) as mobileOrderGoodsCount';
        $this->db->strField .= ',SUM(totalOrderCount) as totalOrderCount, SUM(pcOrderCount) as pcOrderCount, SUM(mobileOrderCount) as mobileOrderCount';
        $this->db->strWhere = implode(' AND ', $arrWhere);

        $arrQuery = $this->db->query_complete();
        $query = 'SELECT ' . array_shift($arrQuery) . ' FROM ' . DB_CATEGORY_STATISTICS . implode(' ', $arrQuery);
        $resultSet = $this->db->query_fetch($query, $arrBind, false);
        unset($arrBind, $arrWhere, $arrQuery);

        return $resultSet;
    }

    /**
     * 통계 데이터를 위해 주문관련 테이블의 정보를 조회하는 함수
     *
     * @return mixed
     */
    public function listsByOrder()
    {
        $arrBind = $arrWhere = [];

        $this->db->strField = 'og.orderNo, og.cateCd, og.orderStatus, og.taxSupplyGoodsPrice, og.taxVatGoodsPrice, og.taxFreeGoodsPrice, og.goodsCnt, o.orderTypeFl, oi.orderName, cg.cateNm';
        $this->db->strWhere = 'og.paymentDt >= (NOW()-INTERVAL 1 DAY) AND og.orderStatus=\'p1\' AND og.cateCd !=\'\'';
        $this->db->strJoin = 'JOIN es_order AS o ON og.orderNo = o.orderNo';
        $this->db->strJoin .= ' JOIN es_orderInfo AS oi ON og.orderNo = oi.orderNo';
        $this->db->strJoin .= ' JOIN es_categoryGoods AS cg ON og.cateCd = cg.cateCd';
        $this->db->strOrder = 'og.regDt DESC';
        $arrQuery = $this->db->query_complete();
        $query = 'SELECT ' . array_shift($arrQuery) . ' FROM ' . DB_ORDER_GOODS . ' AS og ' . implode(' ', $arrQuery);
        $resultSet = $this->db->query_fetch($query, $arrBind, true);
        \Logger::info($query, $resultSet);
        $result = [];
        /*
         * 조회된 데이터를 카테고리번호별로 배열에 저장
         */
        foreach ($resultSet as $key => $value) {
            $result[$value['cateCd']][] = new CategoryOrderVO($value);
        }
        unset($arrBind, $arrWhere, $arrQuery, $resultSet);

        return $result;
    }

    /**
     * 주문관련 테이블 조회 결과를 분석테이블에 입력하기 전 데이터를 가공해주는 함수
     *
     * @param array $list
     *
     * @return mixed
     */
    public function statisticsOrder(array $list)
    {
        $result = [];

        $lastSno = $this->lastSno() + 1;
        /*
         * @var string $cateCd   카테고리 코드
         * @var array  $arrValue 주문상품 정보
         */
        foreach ($list as $cateCd => $arrValue) {
            // 카테고리 분석 VO 생성
            $vo = new CategoryStatisticsVO();
            $arrValueCount = count($arrValue);
            $vo->setSno($lastSno);
            $lastSno++;
            $vo->setCateCd($cateCd);

            // 주문상품 정보가 있을 경우
            if ($arrValueCount > 0) {
                /** @var CategoryOrderVO $firstVO */
                $firstVO = $arrValue[0];
                $vo->setCateNm($firstVO->getCateNm());
                $isMobile = $firstVO->getOrderTypeFl() === 'mobile';
                if ($isMobile) {
                    $vo->setMobileOrderCount($arrValueCount);
                } else {
                    $vo->setPcOrderCount($arrValueCount);
                }

                /**
                 * 카테고리 PC, 모바일 구매금액과 구매수량을 설정
                 * @var CategoryOrderVO $item
                 */
                foreach ($arrValue as $i => $item) {
                    if ($isMobile) {
                        $vo->setMobilePrice($vo->getMobilePrice() + $item->getTaxTotalGoodsPrice());
                        $vo->setMobileOrderGoodsCount($vo->getMobileOrderGoodsCount() + $item->getGoodsCnt());
                    } else {
                        $vo->setPcPrice($vo->getPcPrice() + $item->getTaxTotalGoodsPrice());
                        $vo->setPcOrderGoodsCount($vo->getPcOrderGoodsCount() + $item->getGoodsCnt());
                    }
                }

                // 총매출금액, 총구매수량, 총구매자 수 설정
                $vo->setTotalOrderCount($vo->getPcOrderCount() + $vo->getMobileOrderCount());
                $vo->setTotalPrice($vo->getTotalPrice() + $vo->getPcPrice() + $vo->getMobilePrice());
                $vo->setTotalOrderGoodsCount($vo->getTotalOrderGoodsCount() + $vo->getPcOrderGoodsCount() + $vo->getMobileOrderGoodsCount());
            }

            $result[] = $vo;
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function lastSno()
    {
        $result = $this->db->query_fetch('SELECT sno FROM ' . DB_CATEGORY_STATISTICS . ' ORDER BY sno DESC LIMIT 1', null, false);

        return $result['sno'];
    }

    /**
     * 스케쥴 실행 시 동작할 함수
     *
     * @return mixed
     */
    public function scheduleStatistics()
    {
        $count = $this->db->getCount(DB_CATEGORY_STATISTICS, 'sno', 'WHERE regDt LIKE concat(\'' . date('Y-m-d') . '\',\'%\')');
        if (isset($count) === false || $count < 1) {
            $categoryOrderList = $this->listsByOrder();
            if (is_null($categoryOrderList) === false && count($categoryOrderList) > 0) {
                $list = $this->statisticsOrder($categoryOrderList);
                $this->inserts($list);
            }
        }
    }

    /**
     * @inheritdoc
     *
     * @param object $list
     *
     * @return mixed|string
     */
    public function makeTable($list)
    {
        $htmlList = [];

        if ($list > 0) {
            $idx = 1;
            /**
             * @var CategoryStatisticsVO $vo
             */
            foreach ($list as $key => $vo) {
                $htmlList[] = '<tr class="nowrap text-right">';
                $htmlList[] = '<td class="text-center font-num">' . $idx++ . '</td>';
                $htmlList[] = '<td class="text-center">' . $vo->getCateCd() . '</td>';
                $htmlList[] = '<td class="text-center">' . $vo->getCateNm() . '</td>';
                $htmlList[] = '<td class="font-num">' . $vo->getTotalPrice(true) . '</td>';
                $htmlList[] = '<td class="font-num">' . $vo->getPcPrice(true) . '</td>';
                $htmlList[] = '<td class="font-num">' . $vo->getMobilePrice(true) . '</td>';
                $htmlList[] = '<td class="font-num">' . $vo->getTotalOrderGoodsCount() . '</td>';
                $htmlList[] = '<td class="font-num">' . $vo->getPcOrderGoodsCount() . '</td>';
                $htmlList[] = '<td class="font-num">' . $vo->getMobileOrderGoodsCount() . '</td>';
                $htmlList[] = '<td class="font-num">' . $vo->getTotalOrderCount() . '</td>';
                $htmlList[] = '<td class="font-num">' . $vo->getPcOrderCount() . '</td>';
                $htmlList[] = '<td class="font-num">' . $vo->getMobileOrderCount() . '</td>';
                $htmlList[] = '</tr>';
            }
        } else {
            return '<tr><td class="no-data" colspan="6">' . __('통계 정보가 없습니다.') . '</td></tr>';
        }

        return join('', $htmlList);
    }

    /**
     * limit 해제 한 검색 결과 수
     *
     * @return mixed
     */
    public function foundRows()
    {
        return $this->db->foundRows();
    }

    /** @deprecated */
    public function statisticsData(array $list)
    {
    }

    /** @deprecated */
    public function getStatisticsList(array $params, $offset = 0, $limit = 20)
    {
    }

    /** @deprecated */
    public function getChartJsonData($vo)
    {
    }
}
