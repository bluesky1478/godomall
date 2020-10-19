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
use Framework\Application\Bootstrap\Log;
use Framework\Utility\ArrayUtils;
use Framework\Utility\SkinUtils;
use Logger;

/**
 * Class 상품판매 분석 통계
 * @package Bundle\Component\GoodsStatistics
 * @author  yjwee
 */
class GoodsSaleStatistics extends \Component\MemberStatistics\AbstractStatistics
{
    /**
     * @inheritDoc
     */
    public function __construct()
    {
        parent::__construct();
        $this->tableFunctionName = 'tableGoodsSaleStatistics';
    }

    /**
     * 분석현황 총계 데이터 조회
     *
     * @param array $requestParams
     *
     * @return array|object
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
        $query = 'SELECT ' . array_shift($arrQuery) . ' FROM ' . DB_GOODS_SALE_STATISTICS . implode(' ', $arrQuery);
        $resultSet = $this->db->query_fetch($query, $arrBind, false);
        unset($arrBind, $arrWhere, $arrQuery);

        return $resultSet;
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
    public function getGoodsSaleRankList(array $requestParams, $offset = 0, $limit = 20)
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

        $this->db->strField = 'sno, imageStorage, imagePath, imageName, goodsNo, goodsNm, companyNm, cateCd, SUM(totalPrice) AS totalPrice, SUM(pcPrice) AS pcPrice, SUM(mobilePrice) AS mobilePrice
, SUM(totalOrderGoodsCount) AS totalOrderGoodsCount, SUM(pcOrderGoodsCount) AS pcOrderGoodsCount, SUM(mobileOrderGoodsCount) AS mobileOrderGoodsCount
, SUM(totalOrderCount) AS totalOrderCount, SUM(pcOrderCount) AS pcOrderCount, SUM(mobileOrderCount) AS mobileOrderCount, regDt, modDt';
        $this->db->strWhere = implode(' AND ', $arrWhere);
        $this->db->strGroup = 'goodsNo';
        $this->db->strOrder = 'totalPrice DESC';
        if (is_null($offset) === false && is_null($limit) === false) {
            $this->db->strLimit = ($offset - 1) * $limit . ', ' . $limit;
        }

        $arrQuery = $this->db->query_complete();
        $query = 'SELECT ' . array_shift($arrQuery) . ' FROM ' . DB_GOODS_SALE_STATISTICS . implode(' ', $arrQuery);
        $resultSet = $this->db->query_fetch($query, $arrBind);

        $result = [];
        foreach ($resultSet as $key => $value) {
            $result[] = new GoodsSaleStatisticsVO($value);
        }

        unset($arrBind, $arrWhere, $arrQuery, $resultSet);

        return $result;
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

    /**
     * 스케쥴 실행 시 동작할 함수
     *
     * @return mixed
     */
    public function scheduleStatistics()
    {
        Logger::info(__METHOD__);
        $count = $this->db->getCount(DB_GOODS_SALE_STATISTICS, 'sno', 'WHERE regDt LIKE concat(\'' . date('Y-m-d') . '\',\'%\')');
        if (isset($count) === false || $count < 1) {
            $categoryOrderList = $this->listsByOrder();
            if (is_null($categoryOrderList) === false && count($categoryOrderList) > 0) {
                $list = $this->statisticsOrder($categoryOrderList);
                $this->inserts($list);
            }
        }
    }

    /**
     * 스케쥴 실행 시 동작할 함수
     *
     * @return mixed
     */
    public function listsByOrder()
    {
        $arrBind = $arrWhere = [];

        $this->db->strField = 'gi.imageName, g.imageStorage, g.imagePath, og.goodsNo, og.goodsNm, og.cateCd, og.orderNo, og.orderStatus, og.taxSupplyGoodsPrice, og.taxVatGoodsPrice, og.taxFreeGoodsPrice, og.goodsCnt, o.orderTypeFl, oi.orderName, sm.companyNm';
        $this->db->strWhere = 'og.paymentDt >= (NOW()-INTERVAL 1 DAY) AND og.orderStatus=\'p1\' AND og.cateCd !=\'\'';
        $this->db->strWhere .= ' AND (gi.imageKind = \'List\' OR gi.imageKind IS NULL)';
        $this->db->strJoin = 'JOIN ' . DB_ORDER . ' AS o ON og.orderNo = o.orderNo';
        $this->db->strJoin .= ' JOIN ' . DB_ORDER_INFO . ' AS oi ON og.orderNo = oi.orderNo AND oi.orderInfoCd = 1';
        $this->db->strJoin .= ' JOIN ' . DB_GOODS . ' AS g ON og.goodsNo = g.goodsNo';
        $this->db->strJoin .= ' LEFT JOIN ' . DB_GOODS_IMAGE . ' AS gi ON og.goodsNo = gi.goodsNo';
        $this->db->strJoin .= ' LEFT JOIN ' . DB_SCM_MANAGE . ' AS sm ON og.scmNo = sm.scmNo';
        $this->db->strOrder = 'og.regDt DESC';
        $arrQuery = $this->db->query_complete();
        $query = 'SELECT ' . array_shift($arrQuery) . ' FROM ' . DB_ORDER_GOODS . ' AS og ' . implode(' ', $arrQuery);
        $resultSet = $this->db->query_fetch($query, $arrBind, true);
        \Logger::channel(Log::CHANNEL_SCHEDULER)->info($query, $resultSet);
        $result = [];

        /*
         * 조회된 데이터를 상품번호별로 배열에 저장
         */
        foreach ($resultSet as $index => $item) {
            $result[$item['goodsNo']][] = new GoodsOrderVO($item);
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

        foreach ($list as $goodsNo => $arrValue) {
            $vo = new GoodsSaleStatisticsVO();
            $arrValueCount = count($arrValue);
            $vo->setSno($lastSno);
            $lastSno++;
            $vo->setGoodsNo($goodsNo);

            if ($arrValueCount > 0) {
                /** @var GoodsOrderVO $firstVO */
                $firstVO = $arrValue[0];
                $vo->setGoodsNm($firstVO->getGoodsNm());
                $vo->setCateCd($firstVO->getCateCd());
                $vo->setImageName($firstVO->getImageName());
                $vo->setImagePath($firstVO->getImagePath());
                $vo->setImageStorage($firstVO->getImageStorage());
                $vo->setCompanyNm($firstVO->getCompanyNm());

                $isMobile = $firstVO->getOrderTypeFl() === 'mobile';
                if ($isMobile) {
                    $vo->setMobileOrderCount($arrValueCount);
                } else {
                    $vo->setPcOrderCount($arrValueCount);
                }

                /**
                 * 상품 PC, 모바일 구매금액 구매수량 설정
                 * @var GoodsOrderVO $item
                 */
                foreach ($arrValue as $index => $item) {
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
     * 테이블의 마지막 일련번호를 반환하는 함수
     *
     * @return mixed
     */
    public function lastSno()
    {
        $result = $this->db->query_fetch('SELECT sno FROM ' . DB_GOODS_SALE_STATISTICS . ' ORDER BY sno DESC LIMIT 1', null, false);
        Logger::debug(__METHOD__, $result);

        return $result['sno'];
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
        $this->db->setMultipleInsertDb(DB_GOODS_SALE_STATISTICS, array_keys($tableModel), $array);
    }

    /**
     * @inheritdoc
     *
     * @param $list
     *
     * @return string
     */
    public function makeTable($list)
    {
        $htmlList = [];

        if ($list > 0) {
            $idx = 1;
            $imageSize = 40;
            /**
             * @var GoodsSaleStatisticsVO $vo
             */
            foreach ($list as $key => $vo) {
                $htmlList[] = '<tr class="nowrap text-right">';
                $htmlList[] = '<td class="text-center">' . $idx++ . '</td>';
                try {
                    $imageTag = SkinUtils::makeGoodsImageTag($vo->getGoodsNo(), $vo->getImageName(), $vo->getImagePath(), $vo->getImageStorage(), $imageSize, $vo->getGoodsNm(), '_blank');
                } catch (Exception $e) {
                    Logger::error($e->getMessage() . ', ' . $e->getFile() . ', ' . $e->getLine());

                    $imageTag = SkinUtils::makeImageTag(SkinUtils::noImageStorageUrl($imageSize));
                }
                $htmlList[] = '<td class="text-center">' . $imageTag . '</td>';
                $htmlList[] = '<td class="text-center">' . gd_remove_tag($vo->getGoodsNm()) . '</td>';
                $htmlList[] = '<td class="text-center right-line1">' . $vo->getCompanyNm() . '</td>';
                $htmlList[] = '<td class="font-num point1 total-price right-line1">' . $vo->getTotalPrice(true) . '</td>';
                $htmlList[] = '<td class="font-num">' . $vo->getPcPrice(true) . '</td>';
                $htmlList[] = '<td class="font-num right-line1">' . $vo->getMobilePrice(true) . '</td>';
                $htmlList[] = '<td class="font-num point1 right-line1">' . $vo->getTotalOrderGoodsCount() . '</td>';
                $htmlList[] = '<td class="font-num">' . $vo->getPcOrderGoodsCount() . '</td>';
                $htmlList[] = '<td class="font-num right-line1">' . $vo->getMobileOrderGoodsCount() . '</td>';
                $htmlList[] = '<td class="font-num point1 right-line1">' . $vo->getTotalOrderCount() . '</td>';
                $htmlList[] = '<td class="font-num">' . $vo->getPcOrderCount() . '</td>';
                $htmlList[] = '<td class="font-num">' . $vo->getMobileOrderCount() . '</td>';
                $htmlList[] = '</tr>';
            }
        } else {
            return '<tr><td class="no-data" colspan="12">' . __('통계 정보가 없습니다.') . '</td></tr>';
        }

        return join('', $htmlList);
    }

    /**
     * 테이블 count 결과 반환
     *
     * @return mixed
     */
    public function getCount()
    {
        return $this->db->getCount(DB_GOODS_SALE_STATISTICS, 'sno');
    }

    /**
     * @inheritdoc
     * @deprecated
     */
    public function statisticsData(array $list)
    {
    }

    /**
     * @inheritdoc
     * @deprecated
     */
    public function getStatisticsList(array $params, $offset = 0, $limit = 20)
    {
    }

    /**
     * @inheritdoc
     * @deprecated
     */
    public function getChartJsonData($vo)
    {
    }
}
