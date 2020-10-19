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

namespace Bundle\Component\Worker;


use Framework\Utility\NumberUtils;
use Framework\Utility\StringUtils;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use UserFilePath;

/**
 * Class NaverDbUrl
 *
 * @package Bundle\Component\Worker
 * @author  yjwee <yeongjong.wee@godo.co.kr>
 */
class NaverDbUrl extends \Component\Worker\AbstractDbUrl
{
    protected $salesIndexDbUrl, $salesDate, $db, $salesPath;
    protected $salesHeader = ['mall_id', 'sale_count', 'sale_price', 'order_count', 'dt'];

    /**
     * DbUrl 정책 호출
     *
     */
    protected function loadConfig()
    {
        $componentDbUrl = \App::load('Component\\Marketing\\DBUrl');
        $this->config = $componentDbUrl->getConfig('naver', 'config');
    }

    /**
     * EP 3.0 사용 여부
     *
     * @return bool
     */
    protected function useNaverEP3(): bool
    {
        if (!key_exists('naverVersion', $this->config)) {
            $this->loadConfig();
        }

        return $this->config['naverVersion'] == '3';
    }

    /**
     * DbUrl 사용함 상태 확인
     *
     * @return bool
     */
    protected function notUseDbUrl(): bool
    {
        if (!key_exists('naverFl', $this->config)) {
            $this->loadConfig();
        }

        return $this->config['naverFl'] != 'y';
    }

    /**
     * 상품 시작과 종료 번호를 조회
     *
     * @param array $params
     *
     * @return array
     */
    protected function selectStartWithEndGoodsNo(array $params = []): array
    {
        $this->goodsWheres = [];
        $this->goodsWheres[] = 'g.goodsDisplayFl = \'y\'';
        $this->goodsWheres[] = 'g.delFl = \'n\'';
        $this->goodsWheres[] = 'g.applyFl = \'y\'';
        $this->goodsWheres[] = 'NOT(g.stockFl = \'y\' AND g.totalStock = 0)';
        $this->goodsWheres[] = 'g.soldOutFl = \'n\'';
        $this->goodsWheres[] = '(UNIX_TIMESTAMP(g.goodsOpenDt) IS NULL  OR UNIX_TIMESTAMP(g.goodsOpenDt) = 0 OR UNIX_TIMESTAMP(g.goodsOpenDt) < UNIX_TIMESTAMP())';
        $this->goodsWheres[] = " g.naverFl = 'y'";
        $wheres = $this->goodsWheres;
        if (key_exists('where', $params) && count($params['where']) > 0) {
            $wheres = array_merge($wheres, $params['where']);
        }
        $strSQL = ' SELECT min(g.goodsNo) AS startGoodsNo,max(g.goodsNo) AS endGoodsNo FROM ' . DB_GOODS . ' as g  WHERE ' . implode(' AND ', $wheres);

        $db = \App::getInstance('DB');
        $resultSet = $db->query_fetch($strSQL, null, false);

        return $resultSet;
    }

    /**
     * countGoods
     *
     * @param array $params
     *
     * @return int
     */
    protected function countGoods(array $params = []): int
    {
        $this->goodsWheres = [];
        $this->goodsWheres[] = 'g.goodsDisplayFl = \'y\'';
        $this->goodsWheres[] = 'g.delFl = \'n\'';
        $this->goodsWheres[] = 'g.applyFl = \'y\'';
        $this->goodsWheres[] = 'NOT(g.stockFl = \'y\' AND g.totalStock = 0)';
        $this->goodsWheres[] = 'g.soldOutFl = \'n\'';
        $this->goodsWheres[] = '(UNIX_TIMESTAMP(g.goodsOpenDt) IS NULL  OR UNIX_TIMESTAMP(g.goodsOpenDt) = 0 OR UNIX_TIMESTAMP(g.goodsOpenDt) < UNIX_TIMESTAMP())';
        $this->goodsWheres[] = " g.naverFl = 'y'";
        $wheres = $this->goodsWheres;
        if (key_exists('where', $params) && count($params['where']) > 0) {
            $wheres = array_merge($wheres, $params['where']);
        }
        $strSQL = ' SELECT COUNT(*) AS cnt FROM ' . DB_GOODS . ' as g  WHERE ' . implode(' AND ', $wheres);

        $db = \App::getInstance('DB');
        $resultSet = $db->query_fetch($strSQL, null, false);

        return $resultSet['cnt'];
    }

    /**
     * makeDbUrl
     *
     * @param \Generator $goodsGenerator
     * @param int        $pageNumber
     *
     * @return bool
     */
    protected function makeDbUrl(\Generator $goodsGenerator, int $pageNumber): bool
    {
        $this->totalDbUrlPage++;

        $goodsGenerator->rewind();
        while ($goodsGenerator->valid()) {
            if ($this->greaterThanMaxCount()) {
                break;
            }
            $goods = $goodsGenerator->current();
            $goodsGenerator->next();
            if (empty($goods['goodsPriceString']) === false) {
                continue;
            }
            if ($goods['goodsNmPartner']) {
                $goods['goodsNm'] = $goods['goodsNmPartner'];
            }

            $cateListCd = [];
            $cateListNm = [];
            if ($goods['cateCd']) {
                if (empty($this->categoryStorage[$goods['cateCd']]) === true) {
                    $cateList = $this->componentCategory->getCategoriesPosition($goods['cateCd'])[0];
                    $this->categoryStorage[$goods['cateCd']] = $cateList;
                }
                $cateList = $this->categoryStorage[$goods['cateCd']];

                if ($cateList) {
                    $cateListCd = array_keys($cateList);
                    $cateListNm = array_values($cateList);
                }
            }
            $goodsPrice = $goods['goodsPrice'];

            //타임세일 판매가 추가
            if ($this->config['dcTimeSale'] == 'y' && $goodsPrice > 0 && gd_is_plus_shop(PLUSSHOP_CODE_TIMESALE) === true) {
                $timeSale = \App::load('\\Component\\Promotion\\TimeSale');
                $timeSaleInfo = $timeSale->getGoodsTimeSale($goods['goodsNo']);
                if ($timeSaleInfo) {
                    $truncPolicy = gd_policy('basic.trunc.goods'); // 절사 내용
                    $goodsPrice = gd_number_figure($goodsPrice - (($timeSaleInfo['benefit'] / 100) * $goodsPrice), $truncPolicy['unitPrecision'], $truncPolicy['unitRound']);
                    $goods['goodsPrice'] = $goodsPrice;
                }
            }

            if ($this->policy['']['dcGoods'] == 'y' && $goods['goodsPrice'] > 0 && $goods['goodsDiscountFl'] == 'y') {
                $goodsPrice = $goodsPrice - $this->getGoodsDcPrice($goods);
            }
            $deliveryPrice = $this->setDeliveryPrice($goods);
            if (!is_numeric($deliveryPrice)) {
                continue;
            }
            $couponPrice = StringUtils::strIsSet($this->setGoodsCoupon($goods), 0);
            if ($this->config['dcCoupon'] == 'y' && $couponPrice > 0 && $goodsPrice - $couponPrice >= 0) {
                $goodsPrice = $goodsPrice - $couponPrice;
            } else {
                if ($goodsPrice - $couponPrice < 0) {
                    $goodsPrice = 0;
                }
                $couponPrice = 0;
            }

            $goodsImageSrc = $this->getGoodsImageSrc($goods);
            if (empty($goodsImageSrc)) {
                continue;
            }
            $goodsMustInfo = $this->getGoodsMustInfo($goods);
            $installationCosts = $goodsMustInfo[0];
            $deliveryGrade = $goodsMustInfo[1];
            $deliveryDetail = $goodsMustInfo[2];
            $this->writeDbUrl('<<<begin>>>');
            $this->writeDbUrl('<<<mapid>>>' . $goods['goodsNo']); // [필수] 쇼핑몰 상품ID
            $this->selectCategoryBrand($goods);
            $goods['brandNm'] = $this->brandStorage[$goods['brandCd']];
            $goods['goodsNm'] = $this->replaceGoodsName($goods);
            $this->writeDbUrl('<<<pname>>>' . StringUtils::htmlSpecialCharsStripSlashes($goods['goodsNm'])); // [필수] 상품명
            $eventDesc = $this->getEventDescription($goods);
            $this->writeDbUrl('<<<price>>>' . NumberUtils::moneyFormat($goodsPrice, false)); // [필수] 판매가격
            $this->writeDbUrl('<<<pgurl>>>' . 'http://' . $this->policy['basic']['info']['mallDomain'] . '/goods/goods_view.php?goodsNo=' . $goods['goodsNo'] . '&inflow=naver'); // [필수] 상품의 상세페이지 주소
            $this->writeDbUrl('<<<igurl>>>' . $goodsImageSrc); // [필수] 이미지 URL
            for ($i = 0; $i < 4; $i++) {
                $this->writeDbUrl('<<<caid' . ($i + 1) . '>>>' . StringUtils::strIsSet($cateListCd[$i])); // [필수] 대분류 카테고리 코드
            }
            unset($cateListCd);
            for ($i = 0; $i < 4; $i++) {
                $this->writeDbUrl('<<<cate' . ($i + 1) . '>>>' . StringUtils::strIsSet($cateListNm[$i])); // [필수] 대분류 카테고리 코드
            }
            unset($cateListNm);
            if (StringUtils::strIsSet($goods['goodsModelNo'])) {
                $this->writeDbUrl('<<<model>>>' . StringUtils::strIsSet($goods['goodsModelNo'])); // [선택] 모델명
            }
            if (StringUtils::strIsSet($goods['brandNm'])) {
                $this->writeDbUrl('<<<brand>>>' . StringUtils::strIsSet($goods['brandNm'])); // [선택] 브랜드
            }
            if (StringUtils::strIsSet($goods['makerNm'])) {
                $this->writeDbUrl('<<<maker>>>' . StringUtils::strIsSet($goods['makerNm'])); // [선택] 메이커
            }
            if (StringUtils::strIsSet($goods['originNm'])) {
                $this->writeDbUrl('<<<origi>>>' . StringUtils::strIsSet($goods['originNm'])); // [선택] 원산지
            }
            if (StringUtils::strIsSet($eventDesc)) {
                $this->writeDbUrl('<<<event>>>' . StringUtils::strIsSet(StringUtils::htmlSpecialCharsStripSlashes($eventDesc))); // 이벤트문구
            }
            if (StringUtils::strIsSet($couponPrice)) {
                $this->writeDbUrl('<<<coupo>>>' . NumberUtils::moneyFormat($couponPrice, false) . '원'); // [선택] 쿠폰
            }
            if (StringUtils::strIsSet($this->config['nv_pcard'])) {
                $this->writeDbUrl('<<<pcard>>>' . $this->config['nv_pcard']); // [선택] 무이자
            }
            $this->writeDbUrl('<<<point>>>' . StringUtils::strIsSet($this->setGoodsMileage($goods))); // [선택] 마일리지
            if (($reviewCnt = $this->getNaverReviewCount($goods)) > 0) {
                $this->writeDbUrl('<<<revct>>>' . $reviewCnt);
            }
            $this->writeDbUrl('<<<deliv>>>' . StringUtils::strIsSet($deliveryPrice)); // [선택] 배송비
            if (StringUtils::strIsSet($installationCosts)) {
                $this->writeDbUrl('<<<insco>>>' . StringUtils::strIsSet($installationCosts)); // 추가설치비용
            }
            if (StringUtils::strIsSet($deliveryGrade)) {
                $this->writeDbUrl('<<<dlvga>>>' . StringUtils::strIsSet($deliveryGrade)); // [선택] 배송/설치비용
            }
            if (StringUtils::strIsSet($deliveryDetail)) {
                $this->writeDbUrl('<<<dlvdt>>>' . StringUtils::strIsSet(StringUtils::htmlSpecialCharsStripSlashes($deliveryDetail))); // [선택] 배송/설치비용 사유
            }
            $this->writeDbUrl('<<<ftend>>>');
            $this->totalDbUrlData++;
        }

        return true;
    }

    protected function getGoodsMustInfo(array $goods): array
    {
        $installationCosts = $deliveryGrade = $deliveryDetail = '';

        $goods['goodsMustInfo'] = json_decode($goods['goodsMustInfo'], true);
        foreach ($goods['goodsMustInfo'] as $mustKey => $mustValue) {
            if ($mustValue['step0']['infoTitle'] == '배송 · 설치비용' && $deliveryDetail == '') {
                $deliveryGrade = "Y";
                $deliveryDetail = $mustValue['step0']['infoValue'];
            }

            if ($mustValue['step0']['infoTitle'] == '추가설치비용') {
                $installationCosts = "Y";
            }
        }

        return [
            $installationCosts,
            $deliveryGrade,
            $deliveryDetail,
        ];
    }

    /**
     * 설정에 따른 이벤트 설명을 반환
     *
     * @param array $goods
     *
     * @return string
     */
    protected function getEventDescription(array $goods): string
    {
        $eventDesc = '';
        if ($this->config['naverEventCommon'] == 'y') {
            $eventDesc = $this->config['naverEventDescription'];
        }
        if ($this->config['naverEventGoods'] == 'y' && StringUtils::strIsSet($goods['eventDescription'])) {
            $eventDesc .= $goods['eventDescription'];
        }

        return $eventDesc;
    }

    /**
     * 상품명 반환 설정에 따라 메이커, 브랜드, 상품번호 를 포함
     *
     * @param array $goods
     *
     * @return string
     */
    protected function replaceGoodsName(array $goods): string
    {
        $goodsName = $goods['goodsNm'];
        if ($this->config['goodsHead']) {
            $goodsName = str_replace(
                    [
                        '{_maker}',
                        '{_brand}',
                        '{_goodsNo}',
                    ], [
                    $goods['makerNm'],
                    $goods['brandNm'],
                    $goods['goodsNo'],
                ], $this->config['goodsHead']
                ) . ' ' . $goodsName;
        }

        return $goodsName;
    }


    /**
     * getNaverReviewCount
     *
     * @param array $data
     *
     * @return int
     */
    protected function getNaverReviewCount(array $data): int
    {
        if ($this->config['naverReviewChannel'] == 'plusReview') {
            $reviewCnt = $data['plusReviewCnt'];
        } else if ($this->config['naverReviewChannel'] == 'both') {
            $reviewCnt = $data['plusReviewCnt'] + $data['reviewCnt'];
        } else {
            $reviewCnt = $data['reviewCnt'];
        }

        // 크리마리뷰 카운팅 사용
        $crema = \App::load('Component\\Service\\Crema');
        if ($crema->getUseEpFl()) {
            $reviewCnt = $data['cremaReviewCnt'];
        }

        return $reviewCnt;
    }

    /**
     * salesIndexInit
     *
     * 네이버 쇼핑 판매지수 EP 파일 생성(+헤더 작성)
     *
     * @param null $date
     * @return bool
     * @throws \Exception
     */
    public function salesIndexInit($date = null)
    {
        if ($this->db === null) {
            $this->db = \App::load('DB');
        }
        $logger = \App::getInstance('logger')->channel('naverEP');
        $resolver = \App::getInstance('user.path');

        $dbUrlResolver = $resolver->data('dburl', 'naver', 'naver_sales_index.tsv');
        $this->salesPath = $dbUrlResolver->getRealPath();
        $logger->info(__METHOD__ . ' - ' . $this->salesPath);

        // 파일 초기화
        if (is_file($this->salesPath)) {
            unlink($this->salesPath);
            $logger->info(__METHOD__ . ' - success reset data');
        }

        $handler = new StreamHandler($this->salesPath, Logger::INFO, false, 0777);
        $formatter = new LineFormatter("%message%\r\n");
        $formatter->allowInlineLineBreaks(false);
        $formatter->ignoreEmptyContextAndExtra(false);
        $handler->setFormatter($formatter);
        $this->salesIndexDbUrl = new Logger('dbUrl');
        $this->salesIndexDbUrl->pushHandler($handler);
        if(!preg_match("/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/", $date) ){
            $this->salesDate = date('Y-m-d', strtotime('-1 day'));
        } else {
            $this->salesDate = $date;
        }
        $this->wirteSalesIndexDbUrl(implode(chr(9), $this->salesHeader));
        $logger->info(__METHOD__ . ' - salesDate: ' . $this->salesDate);
        return true;
    }

    /**
     * setSalesIndexData
     *
     * 네이버 쇼핑 판매지수 EP 데이터 추출
     *
     * @return bool
     */
    public function setSalesIndexData()
    {
        $logger = \App::getInstance('logger')->channel('naverEP');
        $logger->info(__CLASS__ . ', load');
        $arrBind = [];
        $strSQL = "
        SELECT 
            og.orderNo, og.goodsNo, SUM(og.goodsCnt) AS goodsCnt, SUM(og.goodsHandleCnt) AS goodsHandleCnt, SUM(og.goodsPrice) AS goodsPrice, SUM(refundPrice) AS refundPrice, 
            LEFT(og.regDt, 10) as regDt, LEFT(MAX(og.handleDt), 10) as handleDt, 
            (SELECT SUM(g.goodsCnt) - SUM(IF(og.orderStatus in (?, ?), g.goodsCnt, 0)) FROM " . DB_ORDER_GOODS . " g WHERE g.orderNo=og.orderNo AND inflow = ?) AS allCnt
            FROM
            (
                SELECT 
                g.orderNo, g.goodsNo, g.orderStatus, g.goodsCnt, IF(g.orderStatus IN (?, ?), g.goodsCnt, 0) AS goodsHandleCnt, g.goodsCnt * g.goodsPrice AS goodsPrice, 
                IF(g.orderStatus IN (?, ?), g.goodsCnt * g.goodsPrice, 0) AS refundPrice, g.regDt, g.modDt as handleDt
                FROM (
                    SELECT og.orderNo, og.goodsNo, og.orderStatus, og.goodsCnt, og.goodsPrice, og.regDt, IF(o.orderChannelFl = 'naverpay', oh.regDt, oh.modDt) as modDt, og.sno
                    FROM " . DB_ORDER_GOODS . " og
                    LEFT JOIN " . DB_ORDER_HANDLE . " oh ON og.orderNo=oh.orderNo AND og.handleSno=oh.sno
                    LEFT JOIN " . DB_ORDER . " o ON og.orderNo=o.orderNo
                    WHERE 
                        og.inflow = ? 
                        AND og.regDt BETWEEN ? AND ? 
                        AND LEFT(og.orderStatus, 1) NOT IN (?, ?, ?) 
                
                    UNION
                
                    SELECT og.orderNo, og.goodsNo, og.orderStatus, og.goodsCnt, og.goodsPrice, og.regDt, IF(o.orderChannelFl = 'naverpay', oh.regDt, oh.modDt) as modDt, oh.sno
                    FROM " . DB_ORDER_GOODS . " og
                    LEFT JOIN es_orderHandle oh ON og.orderNo=oh.orderNo AND og.handleSno=oh.sno
                    LEFT JOIN " . DB_ORDER . " o ON og.orderNo=o.orderNo 
                    WHERE 
                        og.inflow = ? 
                        AND IF(o.orderChannelFl = 'naverpay', oh.regDt, oh.modDt) BETWEEN ? AND ? 
                        AND og.orderStatus IN (?, ?)
                ) AS g
        ) AS og
        GROUP BY og.orderNo, og.goodsNo";

        $this->db->bind_param_push($arrBind, 's', 'e5');
        $this->db->bind_param_push($arrBind, 's', 'r3');
        $this->db->bind_param_push($arrBind, 's', 'naver');
        $this->db->bind_param_push($arrBind, 's', 'e5');
        $this->db->bind_param_push($arrBind, 's', 'r3');
        $this->db->bind_param_push($arrBind, 's', 'e5');
        $this->db->bind_param_push($arrBind, 's', 'r3');
        $this->db->bind_param_push($arrBind, 's', 'naver');
        $this->db->bind_param_push($arrBind, 's', $this->salesDate . ' 00:00:00');
        $this->db->bind_param_push($arrBind, 's', $this->salesDate . ' 23:59:59');
        $this->db->bind_param_push($arrBind, 's', 'f');
        $this->db->bind_param_push($arrBind, 's', 'c');
        $this->db->bind_param_push($arrBind, 's', 'o');
        $this->db->bind_param_push($arrBind, 's', 'naver');
        $this->db->bind_param_push($arrBind, 's', $this->salesDate . ' 00:00:00');
        $this->db->bind_param_push($arrBind, 's', $this->salesDate . ' 23:59:59');
        $this->db->bind_param_push($arrBind, 's', 'e5');
        $this->db->bind_param_push($arrBind, 's', 'r3');

        $getData = $this->db->query_fetch($strSQL, $arrBind);

        if(count($getData) > 0) {
            $data = [];

            foreach ($getData as $val) {
                if ($val['regDt'] == $val['handleDt']) {
                    $goodsPrice = $val['goodsPrice'] - $val['refundPrice'];
                    $goodsCnt = $val['goodsCnt'] - $val['goodsHandleCnt'];
                    if ($goodsPrice > 0) {
                        $orderCnt = 1;
                    }
                } else {
                    if ($val['regDt'] == $this->salesDate) {
                        $goodsPrice = $val['goodsPrice'];
                        $goodsCnt = $val['goodsCnt'];
                        $orderCnt = 1;
                    } elseif ($val['handleDt'] == $this->salesDate) {
                        $goodsPrice = -$val['refundPrice'];
                        $goodsCnt = -$val['goodsHandleCnt'];
                        if ($val['allCnt'] == 0) {
                            $orderCnt = -1;
                        }
                    }
                }
                $data[$val['goodsNo']]['goodsPrice'] += $goodsPrice;
                $data[$val['goodsNo']]['goodsCnt'] += $goodsCnt;
                $data[$val['goodsNo']]['orderCnt'] += $orderCnt;
            }

            foreach ($data as $key => $value) {
                $result = $key . chr(9);
                $result .= gd_isset($value['goodsCnt'], 0) . chr(9);
                $result .= gd_isset($value['goodsPrice'], 0) . chr(9);
                $result .= gd_isset($value['orderCnt'], 0) . chr(9);
                $result .= $this->salesDate;
                $this->wirteSalesIndexDbUrl($result);
            }
            $logger->info(__METHOD__ . ' - success write data');
        } else {
            // 2019-01-24 @안종태 https://nhnent.dooray.com/project/posts/2399135713968015201
            // unlink($this->salesPath);
            $logger->info(__METHOD__ . ' - empty data : ' . $this->salesPath);
        }
        return true;
    }

    /**
     * wirteSalesIndexDbUrl
     *
     * 네이버 쇼핑 판매지수 EP 데이터 입력
     *
     * @param $contents
     */
    public function wirteSalesIndexDbUrl($contents)
    {
        $this->salesIndexDbUrl->info($contents);
    }

    /**
     * goodsReviewCnt
     *
     * 네이버쇼핑 EP 리뷰 수 카운팅
     *
     * @param $goodsNo
     * @return int
     */
    public function goodsReviewCnt($goodsNo) {

        $reviewCount = 0;
        if(empty($goodsNo)) {
            return $reviewCount;
        }

        switch ($this->config['naverReviewChannel']) {
            case "plusReview":
                $reviewCount = $this->getReviewCounting(DB_PLUS_REVIEW_ARTICLE, $goodsNo, ['applyFl' => 'y']);
                break;
            case "board":
                $reviewCount = $this->getReviewCounting(DB_BD_ . 'goodsreview', $goodsNo, ['isDelete' => 'n', 'isSecret' => 'n']);
                break;
            default:
                $reviewCount = $this->getReviewCounting(DB_PLUS_REVIEW_ARTICLE, $goodsNo, ['applyFl' => 'y']) + $this->getReviewCounting(DB_BD_ . 'goodsreview', $goodsNo, ['isDelete' => 'n', 'isSecret' => 'n']);
                break;
        }
        return $reviewCount;
    }

    /**
     * getReviewCounting
     *
     * 네이버페이 리뷰 카운팅 데이터 추출
     *
     * @param $tableName
     * @param $goodsNo
     * @param $addWhere
     * @return mixed
     */
    public function getReviewCounting($tableName, $goodsNo, $addWhere) {
        if($this->db === null) {
            $this->db = \App::load('DB');
        }
        $arrBind = $arrWhere = $arrOrWhere = [];

        $this->db->strField = 'COUNT(*) AS cnt';
        $arrWhere[] = "goodsNo = ?";
        $this->db->bind_param_push($arrBind, 'i', $goodsNo);

        if(empty($addWhere) === false) {
            foreach ($addWhere as $key => $value) {
                $arrWhere[] = $key . " = ? ";
                $this->db->bind_param_push($arrBind, 's', $value);
            }
        }

        $arrOrWhere[] = "(channel != ? AND memNo > 0)";
        $this->db->bind_param_push($arrBind, 's', 'naverpay');
        if($tableName == DB_BD_ . 'goodsreview') {
            $arrOrWhere[] = "(channel IS NULL AND memNo > 0)";
        }

        $arrOrWhere[] = "(channel = ?)";
        $this->db->bind_param_push($arrBind, 's', 'naverpay');
        $this->db->strWhere = implode(' AND ', $arrWhere) . " AND (" . implode(' OR ', $arrOrWhere) . ")";

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . $tableName . implode(' ', $query);

        return $this->db->query_fetch($strSQL, $arrBind, false)['cnt'];
    }
}
