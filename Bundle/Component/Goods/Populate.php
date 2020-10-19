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

namespace Bundle\Component\Goods;

use Component\Mall\Mall;
use Globals;
use app;
use Request;

/**
 *
 * @author  <kookoo135@godo.co.kr>
 */
class Populate
{
    // 디비 접속
    protected $db;
    protected $gGlobal;
    protected $isFront;
    protected $mallSno;
    public $cfg;

    const RENEWAL_SELECT = [
        '1' =>'1시간',
        '2' =>'2시간',
        '3' =>'3시간',
        '6' =>'6시간',
        '9' =>'9시간',
        '12' =>'12시간',
        '24' =>'24시간',
    ];
    const COLLECT_SELECT = [
        '1 HOUR' => '1시간',
        '24 HOUR' => '24시간',
        '3 DAY' => '3일',
        '7 DAY' => '1주일',
        '1 MONTH' => '1개월',
        '2 MONTH' => '2개월',
        '3 MONTH' => '3개월',
        '4 MONTH' => '4개월',
        '5 MONTH' => '5개월',
        '6 MONTH' => '6개월',
        '7 MONTH' => '7개월',
        '8 MONTH' => '8개월',
        '9 MONTH' => '9개월',
        '10 MONTH' => '10개월',
        '11 MONTH' => '11개월',
        '12 MONTH' => '12개월',
    ];
    const LIMIT = 100;

    /**
     * 생성자
     *
     */
    public function __construct($sno=0)
    {
        if($sno == 0) {
            $getValue = Request::get()->toArray();
            $sno = $getValue['sno'];
        }

        if (!is_object($this->db)) {
            $this->db = \App::load('DB');
        }
        $controller = App::getController();
        $this->isFront = $controller->getRootDirecotory() == 'admin' ? 'front' : $controller->getRootDirecotory();

        $strSQL = ' SELECT * FROM ' . DB_POPULATE_THEME . ' WHERE sno = ?';
        $this->db->bind_param_push($arrBind, 's', $sno);
        $res = $this->db->query_fetch($strSQL, $arrBind, false);
        $res['displayField'] = explode(INT_DIVISION, $res['displayField']);
        $res['mobileDisplayField'] = explode(INT_DIVISION, $res['mobileDisplayField']);
        $res['goodsDiscount'] = explode(INT_DIVISION, $res['goodsDiscount']);
        $res['priceStrike'] = explode(INT_DIVISION, $res['priceStrike']);
        $res['displayAddField'] = explode(INT_DIVISION, $res['displayAddField']);
        $res['mobileGoodsDiscount'] = explode(INT_DIVISION, $res['mobileGoodsDiscount']);
        $res['mobilePriceStrike'] = explode(INT_DIVISION, $res['mobilePriceStrike']);
        $res['mobileDisplayAddField'] = explode(INT_DIVISION, $res['mobileDisplayAddField']);
        $this->cfg = gd_htmlspecialchars_stripslashes($res);

        if ($this->isFront == 'front') {
            gd_isset($this->cfg['lineCnt'], 4);
        } else {
            gd_isset($this->cfg['lineCnt'], 2);
        }
        if ($this->isFront == 'mobile' && $this->cfg['same'] == 'n') {
            $this->cfg['useFl'] = $this->cfg['mobileUseFl'];
            $this->cfg['image'] = $this->cfg['mobileImage'];
            $this->cfg['soldOutFl'] = $this->cfg['mobileSoldOutFl'];
            $this->cfg['soldOutDisplayFl'] = $this->cfg['mobileSoldOutDisplayFl'];
            $this->cfg['soldOutIconFl'] = $this->cfg['mobileSoldOutIconFl'];
            $this->cfg['iconFl'] = $this->cfg['mobileIconFl'];
            $this->cfg['displayField'] = $this->cfg['mobileDisplayField'];
            $this->cfg['displayType'] = $this->cfg['mobileDisplayType'];
        }

        $this->mallSno = \SESSION::get(SESSION_GLOBAL_MALL)['sno'] ?? 1;
    }

    public function getGoodsInfo($type = 'rank')
    {
        if(empty($this->cfg['sno']) || $this->cfg['displayFl'] == 'n'){
            return;
        }

        $getValue = Request::get()->toArray();

        if ($type == 'rank') {
            $limit = $this->cfg['rank'];
            $displayCnt = $this->cfg['rank'];
            Request::get()->del('page');
        } else {
            $limit = self::LIMIT;
            $displayCnt = $this->cfg['lineCnt'] * $this->cfg['rowCnt'];
        }

        $pageNum = gd_isset($getValue['pageNum'], $displayCnt);
        $FuncType = 'getGoodsNo' . ucfirst($this->cfg['type']);

        $nowH = date('G');
        $setH = $nowH - ($nowH % $this->cfg['renewal']);
        if ($setH > 0) {
            $setDate = date('Ymd');
            $setHour = $setH;
        } else {
            $setDate = date('Ymd', strtotime('-1 day', time()));
            $setHour = 23 + $setH;
        }

        $goodsNo = $this->$FuncType($setDate, $setHour, $limit * 5);
        foreach($goodsNo as $value) {
            $tmpGoodsNo[] = $value['goodsNo'];
        }
        $goodsNoData = $tmpGoodsNo;
        //$goodsNoData = implode(INT_DIVISION, $goodsNo);

        if (\Request::isMobile() === true) {
            if ($this->cfg['same'] == 'n') {
                $this->cfg['goodsDiscount'] = $this->cfg['mobileGoodsDiscount'];
                $this->cfg['priceStrike'] = $this->cfg['mobilePriceStrike'];
                $this->cfg['displayAddField'] = $this->cfg['mobileDisplayAddField'];
            }
        }

        if($goodsNoData) {
            $goods = \App::load('\\Component\\Goods\\Goods');
            Request::get()->set('goodsNo',explode(INT_DIVISION,$goodsNoData));
            Request::get()->del('keyword');

            $imageType = gd_isset($this->cfg['image'], 'main');
            $soldOutFl = $this->cfg['soldOutFl'] == 'y' ? true : false;
            $brandFl = in_array('brandCd', array_values($this->cfg['displayField'])) ? true : false;
            $couponPriceFl = in_array('coupon', array_values($this->cfg['displayField'])) ? true : false;
            $optionFl = in_array('option', array_values($this->cfg['displayField'])) ? true : false;

            //$mainOrder = "FIELD(g.goodsNo," . str_replace(INT_DIVISION, ",", $goodsNoData) . ")";
            $mainOrder = "FIELD(g.goodsNo," . implode(",", $goodsNoData) . ")";
            if ($this->cfg['soldOutDisplayFl'] == 'n') $mainOrder = "soldOut asc," . $mainOrder;

            $goods->setThemeConfig($this->cfg);
            $goodsData = $goods->getGoodsSearchList($pageNum , $mainOrder, $imageType, $optionFl, $soldOutFl, $brandFl, $couponPriceFl  ,$displayCnt, false, false, $limit, $goodsNoData);
            Request::get()->del('goodsNo');
        }

        $setData = [];
        if ($type == 'rank') {
            foreach ($goodsData['listData'] as $key => $value) {
                $setData[$key]['goodsNo'] = $value['goodsNo'];
                $setData[$key]['goodsNm'] = $value['goodsNm'];
            }

            unset($goodsData);
            return $setData;
        } else {
            return $goodsData;
        }
    }

    private function getRankCondition(){
        $collect['goodsNo'] = explode(INT_DIVISION, $this->cfg['goodsNo']);
        $collect['categoryCd'] = explode(INT_DIVISION, $this->cfg['categoryCd']);
        $collect['brandCd'] = explode(INT_DIVISION, $this->cfg['brandCd']);

        $except['goodsNo'] = explode(INT_DIVISION, $this->cfg['except_goodsNo']);
        $except['categoryCd'] = explode(INT_DIVISION, $this->cfg['except_categoryCd']);
        $except['brandCd'] = explode(INT_DIVISION, $this->cfg['except_brandCd']);

        $result['collect'] = $collect;
        $result['except'] = $except;
        return $result;
    }

    private function getGoodsCategory($goodsNo){
        $strSQL = 'SELECT * FROM '.DB_GOODS_LINK_CATEGORY.' WHERE goodsNo=? AND cateLinkFl=\'y\'';
        $this->db->bind_param_push($arrBind, 'i', $goodsNo);
        $getData = $this->db->query_fetch($strSQL, $arrBind);
        foreach($getData as $goods){
            $categoryCd[] = $goods['cateCd'];
        }

        return $categoryCd;
    }

    private function getGoodsBrand($goodsNo){
        $strSQL = 'SELECT * FROM '.DB_GOODS_LINK_BRAND.' WHERE goodsNo=? AND cateLinkFl=\'y\'';
        $this->db->bind_param_push($arrBind, 'i', $goodsNo);
        $getData = $this->db->query_fetch($strSQL, $arrBind);
        if(!empty($getData)){
            $getData = $getData[0]['cateCd'];
            return $getData;
        }else{
            return;
        }
    }

    private function conditionGoodsFilter($getData){
        $condition = $this->getRankCondition();
        $range = $this->cfg['range'];

        foreach ($getData as $key => $data) {
            //강제 추가된 상품이라면 무조건 추가
            if((!in_array($data['goodsNo'], $condition['collect']['goodsNo']) && $range != 'goods') || $range == 'goods') {

                //상품이 예외에 포함 되어 있으면 포함 안함
                if(in_array($data['goodsNo'], $condition['except']['goodsNo'])){
                    continue;
                }

                //카테고리가 예외에 포함 되어 있으면 포함 안함
                $linkedCate = $this->getGoodsCategory($data['goodsNo']);
                $linked = false;
                foreach($linkedCate as $cate){
                    if($linked === true) continue;
                    if(in_array($cate, $condition['except']['categoryCd']) && !empty($cate)){
                        $linked = true;
                    }
                }
                if($linked === true) continue;

                //브랜드가 예외에 포함 되어 있으면 포함 안함
                $linkedBrand = $this->getGoodsBrand($data['goodsNo']);
                if(in_array($linkedBrand, $condition['except']['brandCd']) && !empty($linkedBrand)){
                    continue;
                }

                //상품 진열 이라면
                if ($range == 'goods') {
                    if (!in_array($data['goodsNo'], $condition['collect']['goodsNo'])) {
                        continue;
                    }
                }

                //카테고리진열이라면
                $linked = false;
                if ($range == 'category') {
                    foreach ($linkedCate as $cate) {
                        if ($linked === true) continue;
                        if (in_array($cate, $condition['collect']['categoryCd']) && !empty($cate)) {
                            $linked = true;
                        }
                    }
                    if ($linked === false) continue;
                }

                //브랜드 진열 이라면
                if ($range == 'brand') {
                    if (!in_array($linkedBrand, $condition['collect']['brandCd']) || empty($linkedBrand)) {
                        continue;
                    }
                }
            }

            $setData[] = $data;
        }

        return $setData;
    }

    /**
     * 상품 클릭수 순위
     * @param $getDate
     * @param $getHour
     * @param $limit
     * @return 0|array
     */
    private function getGoodsNoHit($getDate, $getHour, $limit)
    {
        $getPrevDate = date('Ymd G', strtotime('-' . $this->cfg['collect'], strtotime($getDate . ' ' . sprintf('%02d', $getHour) . ':00:00')));
        $getPrevTime = explode(' ', $getPrevDate);

        $arrBind = $arrWhere = $setData = [];
        $arrWhere[] = '`mallSno` = ?';
        $this->db->bind_param_push($arrBind, 'i', $this->mallSno);
        $arrWhere[] = '(`viewYMD` BETWEEN ? AND ?)';
        $this->db->bind_param_push($arrBind, 'i', str_replace('-', '', $getPrevTime[0]));
        $this->db->bind_param_push($arrBind, 'i', str_replace('-', '', $getDate));

        $this->db->strField = '*';
        $this->db->strWhere = implode(' AND ', gd_isset($arrWhere));
        $this->db->strOrder = '`viewYMD` asc';

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_GOODS_VIEW_STATISTICS . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);

        foreach ($getData as $data) {
            if ($data['viewYMD'] == $getDate) { // 당일 데이터
                foreach (range(0, $getHour) as $keyTime) {
                    if ($keyTime == $getHour) continue;
                    if (empty($data[$keyTime]) === false) {
                        $goodsViewData = json_decode($data[$keyTime]);

                        foreach ($goodsViewData as $goodsNo => $viewCnt) {
                            $setData[str_replace('g', '', $goodsNo)] += $viewCnt;
                        }
                    }
                }
            } else { // 당일 제외 데이터
                $goodsViewData = json_decode($data['total']);

                foreach ($goodsViewData as $goodsNo => $viewCnt) {
                    $setData[str_replace('g', '', $goodsNo)] += $viewCnt;
                }
            }

            unset($goodsViewData);
        }
        arsort($setData);
        $setData = array_slice(array_keys($setData), 0, $limit);
        foreach($setData as $value){
            $tmpData[]['goodsNo'] = $value;
        }
        $setData = $tmpData;
        $setData = $this->conditionGoodsFilter($setData);
        return $setData;
    }

    /**
     * 판매순위
     * @param $getDate
     * @param $getHour
     * @param int $limit
     * @return array
     */
    private function getGoodsNoSell($getDate, $getHour, $limit = self::LIMIT)
    {
        $getPrevDate = date('Ymd G', strtotime('-' . $this->cfg['collect'], strtotime($getDate . ' ' . sprintf('%02d', $getHour) . ':00:00')));
        $getPrevTime = explode(' ', $getPrevDate);

        $condition = $this->getRankCondition();
        $arrBind = $setData = [];
        $addConditions = '';

        //특정 상품이 포함되었을 경우 (=해당 상품의 범위만 통계 데이터에 노출)
        if(!empty($condition['collect']['goodsNo']) && !empty($condition['collect']['goodsNo'][0])){
            $addConditions = ' AND gs.goodsNo IN (' . implode(',', $condition['collect']['goodsNo']) . ') ';
        }

        //상품 매출 순위
        $strSQL = '
            SELECT
                gs.goodsNo,
                g.goodsNm,
                sum(gs.goodsPrice * gs.goodsCnt) as goodsPrice
            FROM
                ' . DB_GOODS_STATISTICS . ' as gs
                LEFT JOIN ' . DB_GOODS . ' as g ON gs.goodsNo = g.goodsNo
            WHERE
                gs.goodsYMD BETWEEN ? AND ?
                AND gs.mallSno = ?
                ' . $addConditions . '
            GROUP BY gs.goodsNo
            ORDER BY goodsPrice desc, g.goodsNm desc
            LIMIT ' . $limit;
        $this->db->bind_param_push($arrBind, 'i', str_replace('-', '', $getPrevTime[0]));
        $this->db->bind_param_push($arrBind, 'i', str_replace('-', '', $getDate));
        $this->db->bind_param_push($arrBind, 'i', $this->mallSno);
        $getData = $this->db->query_fetch($strSQL, $arrBind);
        $setData = $this->conditionGoodsFilter($getData);
        return $setData;
    }

    /**
     * 판매횟수
     * @param $getDate
     * @param $getHour
     * @param int $limit
     * @return array
     */
    private function getGoodsNoSellCnt($getDate, $getHour, $limit = self::LIMIT)
    {
        $getPrevDate = date('Ymd G', strtotime('-' . $this->cfg['collect'], strtotime($getDate . ' ' . sprintf('%02d', $getHour) . ':00:00')));
        $getPrevTime = explode(' ', $getPrevDate);

        $condition = $this->getRankCondition();
        //선택된 상품은 무조건 추가
        if(!empty($condition['collect']['goodsNo']) && !empty($condition['collect']['goodsNo'][0])){
            $addGoodsQuery = ' OR goodsNo IN('.implode(',', $condition['collect']['goodsNo']) .')';
        }

        $arrBind = $setData = [];
        $strSQL = 'SELECT
              gs.goodsNo,
              sum(gs.goodsCnt)
            FROM
              ' . DB_GOODS_STATISTICS . ' as gs
              LEFT JOIN ' . DB_GOODS . ' as g ON gs.goodsNo = g.goodsNo
            WHERE
              (gs.goodsYMD BETWEEN ? AND ?)
        GROUP BY gs.goodsNo
        ORDER BY sum(gs.goodsCnt) DESC
        LIMIT ' . $limit;

        $this->db->bind_param_push($arrBind, 'i', str_replace('-', '', $getPrevTime[0]));
        $this->db->bind_param_push($arrBind, 'i', str_replace('-', '', $getDate));

        $getData = $this->db->query_fetch($strSQL, $arrBind);
        $setData = $this->conditionGoodsFilter($getData);

        return $setData;
    }

    /**
     * 카트 담은 횟수
     * @param $getDate
     * @param $getHour
     * @param int $limit
     * @return array
     */
    private function getGoodsNoCart($getDate, $getHour, $limit = self::LIMIT)
    {
        $getPrevDate = date('Y-m-d G:00:00', strtotime('-' . $this->cfg['collect'], strtotime($getDate . ' ' . sprintf('%02d', $getHour) . ':00:00')));
        $getNowDate = substr($getDate, 0, 4).'-'.substr($getDate, 4, 2).'-'.substr($getDate, 6, 2).' '.date('G:i:s', time());

        $condition = $this->getRankCondition();
        //선택된 상품은 무조건 추가
        if(!empty($condition['collect']['goodsNo']) && !empty($condition['collect']['goodsNo'][0])){
            $addGoodsQuery = ' OR g.goodsNo IN('.implode(',', $condition['collect']['goodsNo']) .')';
        }

        $arrBind = $setData = [];
        $strSQL = 'SELECT
          cs.goodsNo,
          g.goodsNm,
          count(cs.goodsNo) as cnt
        FROM
          '.DB_CART_STATISTICS.' cs
          LEFT JOIN '.DB_GOODS.' g ON g.goodsNo = cs.goodsNo'.$addGoodsQuery.'
        WHERE
          cs.mallSno = ?
          AND (cs.regDt BETWEEN ? AND ?)
        GROUP BY goodsNo
        ORDER BY count(cs.goodsNo) DESC, goodsNm ASC
        LIMIT ' . $limit;

        $this->db->bind_param_push($arrBind, 'i', $this->mallSno);
        $this->db->bind_param_push($arrBind, 's', $getPrevDate);
        $this->db->bind_param_push($arrBind, 's', $getNowDate);

        $getData = $this->db->query_fetch($strSQL, $arrBind);
        $setData = $this->conditionGoodsFilter($getData);
        return $setData;
    }

    /**
     * 상품 찜리스트 담은 횟수
     * @param $getDate
     * @param $getHour
     * @param int $limit
     * @return array
     */
    private function getGoodsNoWishlist($getDate, $getHour, $limit = self::LIMIT)
    {
        $getPrevDate = date('Y-m-d G:00:00', strtotime('-' . $this->cfg['collect'], strtotime($getDate . ' ' . sprintf('%02d', $getHour) . ':00:00')));
        $getNowDate = substr($getDate, 0, 4).'-'.substr($getDate, 4, 2).'-'.substr($getDate, 6, 2).' '.date('G:i:s', time());

        $condition = $this->getRankCondition();
        //선택된 상품은 무조건 추가
        if(!empty($condition['collect']['goodsNo']) && !empty($condition['collect']['goodsNo'][0])){
            $addGoodsQuery = ' OR g.goodsNo IN('.implode(',', $condition['collect']['goodsNo']) .')';
        }

        $arrBind = $setData = [];
        $strSQL = 'SELECT
          ws.goodsNo,
          g.goodsNm,
          count(ws.goodsNo) as cnt
        FROM
          '.DB_WISH_STATISTICS.' ws
          LEFT JOIN '.DB_GOODS.' g ON g.goodsNo = ws.goodsNo'.$addGoodsQuery.'
        WHERE
          ws.mallSno = ?
          AND (ws.regDt BETWEEN ? AND ?)
        GROUP BY goodsNo
        ORDER BY count(ws.goodsNo) DESC, goodsNm ASC
        LIMIT ' . $limit;

        $this->db->bind_param_push($arrBind, 'i', $this->mallSno);
        $this->db->bind_param_push($arrBind, 's', $getPrevDate);
        $this->db->bind_param_push($arrBind, 's', $getNowDate);

        $getData = $this->db->query_fetch($strSQL, $arrBind);
        $setData = $this->conditionGoodsFilter($getData);
        return $setData;
    }

    /**
     * 상품 후기 작성 수
     * @param $getDate
     * @param $getHour
     * @param int $limit
     * @return array
     */
    private function getGoodsNoReview($getDate, $getHour, $limit = self::LIMIT)
    {
        $getPrevDate = date('Y-m-d G:00:00', strtotime('-' . $this->cfg['collect'], strtotime($getDate . ' ' . sprintf('%02d', $getHour) . ':00:00')));
        $getNowDate = substr($getDate, 0, 4).'-'.substr($getDate, 4, 2).'-'.substr($getDate, 6, 2).' '.date('G:i:s', time());

        $condition = $this->getRankCondition();
        //선택된 상품은 무조건 추가
        if(!empty($condition['collect']['goodsNo']) && !empty($condition['collect']['goodsNo'][0])){
            $addGoodsQuery = ' OR g.goodsNo IN('.implode(',', $condition['collect']['goodsNo']) .')';
        }

        $arrBind = $setData = [];
        $strSQL = 'SELECT
        *
        FROM(
        (SELECT
          g.goodsNo,
          g.goodsNm,
          count(bgr.goodsPt) as goodsPt,
          g.reviewCnt
        FROM
          `es_bd_goodsreview` bgr
          LEFT JOIN ' . DB_GOODS . ' g ON g.goodsNo = bgr.goodsNo' . $addGoodsQuery . '
        WHERE
          bgr.regDt BETWEEN ? AND ?
        GROUP BY g.goodsNo
        ) UNION ALL(
        SELECT
          g.goodsNo,
          g.goodsNm,
          count(pra.goodsPt) as goodsPt,
          g.reviewCnt
        FROM
          `es_plusReviewArticle` pra
          LEFT JOIN ' . DB_GOODS . ' g ON g.goodsNo = pra.goodsNo' . $addGoodsQuery . '
        WHERE
          pra.regDt BETWEEN ? AND ?
        GROUP BY g.goodsno
        )
        )tbl
        WHERE goodsPt > 0
        GROUP BY goodsNo
        ORDER BY sum(goodsPt) DESC, goodsNm ASC
        LIMIT ' . $limit;

        $this->db->bind_param_push($arrBind, 's', $getPrevDate);
        $this->db->bind_param_push($arrBind, 's', $getNowDate);
        $this->db->bind_param_push($arrBind, 's', $getPrevDate);
        $this->db->bind_param_push($arrBind, 's', $getNowDate);

        $getData = $this->db->query_fetch($strSQL, $arrBind);
        $setData = $this->conditionGoodsFilter($getData);
        return $setData;
    }

    /**
     * 상품 후기 평점
     * @param $getDate
     * @param $getHour
     * @param int $limit
     * @return array
     */
    private function getGoodsNoScore($getDate, $getHour, $limit = self::LIMIT)
    {
        $getPrevDate = date('Y-m-d G:00:00', strtotime('-' . $this->cfg['collect'], strtotime($getDate . ' ' . sprintf('%02d', $getHour) . ':00:00')));
        $getNowDate = substr($getDate, 0, 4).'-'.substr($getDate, 4, 2).'-'.substr($getDate, 6, 2).' '.date('G:i:s', time());

        $condition = $this->getRankCondition();
        //선택된 상품은 무조건 추가
        if(!empty($condition['collect']['goodsNo']) && !empty($condition['collect']['goodsNo'][0])){
            $addGoodsQuery = ' OR g.goodsNo IN('.implode(',', $condition['collect']['goodsNo']) .')';
        }

        $arrBind = $setData = [];
        $strSQL = 'SELECT
        goodsNo,
        goodsNm,
        sum(goodsPt),
        sum(reviewCnt)
        FROM(
        (SELECT
          g.goodsNo,
          g.goodsNm,
          sum(bgr.goodsPt) as goodsPt,
          count(bgr.goodsPt) as reviewCnt
        FROM
          `es_bd_goodsreview` bgr
          LEFT JOIN ' . DB_GOODS . ' g ON g.goodsNo = bgr.goodsNo' . $addGoodsQuery . '
        WHERE
          bgr.regDt BETWEEN ? AND ?
          AND bgr.isDelete = \'n\'
        GROUP BY g.goodsNo
        ) UNION ALL(
        SELECT
          g.goodsNo,
          g.goodsNm,
          sum(pra.goodsPt) as goodsPt,
          count(pra.goodsPt) as reviewCnt
        FROM
          `es_plusReviewArticle` pra
          LEFT JOIN ' . DB_GOODS . ' g ON g.goodsNo = pra.goodsNo' . $addGoodsQuery . '
        WHERE
          pra.regDt BETWEEN ? AND ?
        GROUP BY g.goodsno
        )
        )tbl
        WHERE goodsNo IS NOT NULL
        GROUP BY goodsNo
        ORDER BY (sum(goodsPt) / sum(reviewCnt)) DESC, reviewCnt DESC, goodsNm ASC
        LIMIT ' . $limit;

        $this->db->bind_param_push($arrBind, 's', $getPrevDate);
        $this->db->bind_param_push($arrBind, 's', $getNowDate);
        $this->db->bind_param_push($arrBind, 's', $getPrevDate);
        $this->db->bind_param_push($arrBind, 's', $getNowDate);

        $getData = $this->db->query_fetch($strSQL, $arrBind);
        $setData = $this->conditionGoodsFilter($getData);
        return $setData;
    }

    public function insertPopulateSettings($data){
        if($this->getTotalPopulateThemeCnt() >= 10){
            return "인기상품 노출 관리는 최대 10개까지만 등록 가능합니다.";
        }
        $arrBind = [];
        $strSQL = 'INSERT INTO '.DB_POPULATE_THEME.' SET
            `populateName` = ?,
            `displayFl` = ?,
            `type` = ?,
            `rank` = ?,
            `renewal` = ?,
            `collect` = ?,
            `range` = ?,
            `goodsNo` = ?,
            `categoryCd` = ?,
            `brandCd` = ?,
            
            `except_goods` = ?,
            `except_category` = ?,
            `except_brand` = ?,
            `except_goodsNo` = ?,
            `except_categoryCd` = ?,
            `except_brandCd` = ?,
            `template` = ?,
            `same` = ?,
            `useFl` = ?,
            `image` = ?,
            
            `soldOutFl` = ?,
            `soldOutDisplayFl` = ?,
            `soldOutIconFl` = ?,
            `iconFl` = ?,
            `displayField` = ?,
            `displayType` = ?,
            `mobileUseFl` = ?,
            `mobileImage` = ?,
            `mobileSoldOutFl` = ?,
            `mobileSoldOutDisplayFl` = ?,
            
            `mobileSoldOutIconFl` = ?,
            `mobileIconFl` = ?,
            `mobileDisplayField` = ?,
            `mobileDisplayType` = ?,
            `goodsDiscount` = ?,
            `priceStrike` = ?,
            `displayAddField` = ?,
            `mobileGoodsDiscount` = ?,
            `mobilePriceStrike` = ?,
            `mobileDisplayAddField` = ?,
            `regdt` = NOW()
        ';

        //제외 카테고리 선택 정리
        $except['goods'] = $except['category'] = $except['brand'] = 'n';
        if(is_array($data['except'])) {
            foreach ($data['except'] as $k => $v) {
                switch($v){
                    case 'goods':
                        $except['goods'] = 'y';
                        break;
                    case 'category':
                        $except['category'] = 'y';
                        break;
                    case 'brand':
                        $except['brand'] = 'y';
                        break;
                }
            }
        }

        //수집 상품 정리
        $collectGoods = '';
        if($data['range'] == 'goods') {
            $collectGoods = implode(INT_DIVISION, $data['collectGoods']);
        }

        //수집 카테고리 정리
        $collectCategory = '';
        if($data['range'] == 'category') {
            $collectCategory = implode(INT_DIVISION, $data['collectCategory']);
        }

        //수집 브랜드 정리
        $collectBrand = '';
        if($data['range'] == 'brand') {
            $collectBrand = implode(INT_DIVISION, $data['collectBrand']);
        }

        //예외 상품 정리
        $exceptGoods = '';
        if($except['goods'] =='y'){
            $exceptGoods = implode(INT_DIVISION, $data[exceptGoods]);
        }

        //예외 카테고리 정리
        $exceptCategory = '';
        if($except['category'] =='y'){
            $exceptCategory = implode(INT_DIVISION, $data[exceptCategory]);
        }

        //예외 브드 정리
        $exceptBrand = '';
        if($except['brand'] =='y'){
            $exceptBrand = implode(INT_DIVISION, $data[exceptBrand]);
        }

        $this->db->bind_param_push($arrBind, 's', $data['displayNm']);
        $this->db->bind_param_push($arrBind, 's', $data['displayFl']);
        $this->db->bind_param_push($arrBind, 's', $data['type']);
        $this->db->bind_param_push($arrBind, 'i', $data['rank']);
        $this->db->bind_param_push($arrBind, 'i', $data['renewal']);
        $this->db->bind_param_push($arrBind, 's', $data['collect']);
        $this->db->bind_param_push($arrBind, 's', $data['range']);
        $this->db->bind_param_push($arrBind, 's', $collectGoods);
        $this->db->bind_param_push($arrBind, 's', $collectCategory);
        $this->db->bind_param_push($arrBind, 's', $collectBrand);

        $this->db->bind_param_push($arrBind, 's', $except['goods']);
        $this->db->bind_param_push($arrBind, 's', $except['category']);
        $this->db->bind_param_push($arrBind, 's', $except['brand']);
        $this->db->bind_param_push($arrBind, 's', $exceptGoods);
        $this->db->bind_param_push($arrBind, 's', $exceptCategory);
        $this->db->bind_param_push($arrBind, 's', $exceptBrand);
        $this->db->bind_param_push($arrBind, 's', $data['template']);
        $this->db->bind_param_push($arrBind, 's', $data['same']);
        $this->db->bind_param_push($arrBind, 's', $data['useFl']);
        $this->db->bind_param_push($arrBind, 's', $data['image']);

        $this->db->bind_param_push($arrBind, 's', $data['soldOutFl']);
        $this->db->bind_param_push($arrBind, 's', $data['soldOutDisplayFl']);
        $this->db->bind_param_push($arrBind, 's', $data['soldOutIconFl']);
        $this->db->bind_param_push($arrBind, 's', $data['iconFl']);
        $this->db->bind_param_push($arrBind, 's', implode(INT_DIVISION, $data['displayField']));
        $this->db->bind_param_push($arrBind, 's', $data['displayType']);
        $this->db->bind_param_push($arrBind, 's', $data['mobileUseFl']);
        $this->db->bind_param_push($arrBind, 's', $data['mobileImage']);
        $this->db->bind_param_push($arrBind, 's', $data['mobileSoldOutFl']);
        $this->db->bind_param_push($arrBind, 's', $data['mobileSoldOutDisplayFl']);

        $this->db->bind_param_push($arrBind, 's', $data['mobileSoldOutIconFl']);
        $this->db->bind_param_push($arrBind, 's', $data['mobileIconFl']);
        $this->db->bind_param_push($arrBind, 's', implode(INT_DIVISION, $data['mobileDisplayField']));
        $this->db->bind_param_push($arrBind, 's', $data['mobileDisplayType']);
        $this->db->bind_param_push($arrBind, 's', @implode(INT_DIVISION, $data['goodsDiscount']));
        $this->db->bind_param_push($arrBind, 's', @implode(INT_DIVISION, $data['priceStrike']));
        $this->db->bind_param_push($arrBind, 's', @implode(INT_DIVISION, $data['displayAddField']));
        $this->db->bind_param_push($arrBind, 's', @implode(INT_DIVISION, $data['mobileGoodsDiscount']));
        $this->db->bind_param_push($arrBind, 's', @implode(INT_DIVISION, $data['mobilePriceStrike']));
        $this->db->bind_param_push($arrBind, 's', @implode(INT_DIVISION, $data['mobileDisplayAddField']));

        $this->db->bind_query($strSQL, $arrBind);
    }

    public function updatePopulateSettings($data){
        $arrBind = [];
        $strSQL = 'UPDATE '.DB_POPULATE_THEME.' SET
            `populateName` = ?,
            `displayFl` = ?,
            `type` = ?,
            `rank` = ?,
            `renewal` = ?,
            `collect` = ?,
            `range` = ?,
            `goodsNo` = ?,
            `categoryCd` = ?,
            `brandCd` = ?,
            
            `except_goods` = ?,
            `except_category` = ?,
            `except_brand` = ?,
            `except_goodsNo` = ?,
            `except_categoryCd` = ?,
            `except_brandCd` = ?,
            `template` = ?,
            `same` = ?,
            `useFl` = ?,
            `image` = ?,
            
            `soldOutFl` = ?,
            `soldOutDisplayFl` = ?,
            `soldOutIconFl` = ?,
            `iconFl` = ?,
            `displayField` = ?,
            `displayType` = ?,
            `mobileUseFl` = ?,
            `mobileImage` = ?,
            `mobileSoldOutFl` = ?,
            `mobileSoldOutDisplayFl` = ?,
            
            `mobileSoldOutIconFl` = ?,
            `mobileIconFl` = ?,
            `mobileDisplayField` = ?,
            `mobileDisplayType` = ?,
            `goodsDiscount` = ?,
            `priceStrike` = ?,
            `displayAddField` = ?,
            `mobileGoodsDiscount` = ?,
            `mobilePriceStrike` = ?,
            `mobileDisplayAddField` = ?,
            `moddt` = NOW()
         WHERE sno = ?
        ';

        //제외 카테고리 선택 정리
        $except['goods'] = $except['category'] = $except['brand'] = 'n';
        if(is_array($data['except'])) {
            foreach ($data['except'] as $k => $v) {
                switch($v){
                    case 'goods':
                        $except['goods'] = 'y';
                        break;
                    case 'category':
                        $except['category'] = 'y';
                        break;
                    case 'brand':
                        $except['brand'] = 'y';
                        break;
                }
            }
        }

        //수집 상품 정리
        $collectGoods = '';
        if($data['range'] == 'goods') {
            $collectGoods = implode(INT_DIVISION, $data['collectGoods']);
        }

        //수집 카테고리 정리
        $collectCategory = '';
        if($data['range'] == 'category') {
            $collectCategory = implode(INT_DIVISION, $data['collectCategory']);
        }

        //수집 브랜드 정리
        $collectBrand = '';
        if($data['range'] == 'brand') {
            $collectBrand = implode(INT_DIVISION, $data['collectBrand']);
        }

        //예외 상품 정리
        $exceptGoods = '';
        if($except['goods'] =='y'){
            $exceptGoods = implode(INT_DIVISION, $data[exceptGoods]);
        }

        //예외 카테고리 정리
        $exceptCategory = '';
        if($except['category'] =='y'){
            $exceptCategory = implode(INT_DIVISION, $data[exceptCategory]);
        }

        //예외 브드 정리
        $exceptBrand = '';
        if($except['brand'] =='y'){
            $exceptBrand = implode(INT_DIVISION, $data[exceptBrand]);
        }

        $this->db->bind_param_push($arrBind, 's', $data['displayNm']);
        $this->db->bind_param_push($arrBind, 's', $data['displayFl']);
        $this->db->bind_param_push($arrBind, 's', $data['type']);
        $this->db->bind_param_push($arrBind, 'i', $data['rank']);
        $this->db->bind_param_push($arrBind, 'i', $data['renewal']);
        $this->db->bind_param_push($arrBind, 's', $data['collect']);
        $this->db->bind_param_push($arrBind, 's', $data['range']);
        $this->db->bind_param_push($arrBind, 's', $collectGoods);
        $this->db->bind_param_push($arrBind, 's', $collectCategory);
        $this->db->bind_param_push($arrBind, 's', $collectBrand);

        $this->db->bind_param_push($arrBind, 's', $except['goods']);
        $this->db->bind_param_push($arrBind, 's', $except['category']);
        $this->db->bind_param_push($arrBind, 's', $except['brand']);
        $this->db->bind_param_push($arrBind, 's', $exceptGoods);
        $this->db->bind_param_push($arrBind, 's', $exceptCategory);
        $this->db->bind_param_push($arrBind, 's', $exceptBrand);
        $this->db->bind_param_push($arrBind, 's', $data['template']);
        $this->db->bind_param_push($arrBind, 's', $data['same']);
        $this->db->bind_param_push($arrBind, 's', $data['useFl']);
        $this->db->bind_param_push($arrBind, 's', $data['image']);

        $this->db->bind_param_push($arrBind, 's', $data['soldOutFl']);
        $this->db->bind_param_push($arrBind, 's', $data['soldOutDisplayFl']);
        $this->db->bind_param_push($arrBind, 's', $data['soldOutIconFl']);
        $this->db->bind_param_push($arrBind, 's', $data['iconFl']);
        $this->db->bind_param_push($arrBind, 's', implode(INT_DIVISION, $data['displayField']));
        $this->db->bind_param_push($arrBind, 's', $data['displayType']);
        $this->db->bind_param_push($arrBind, 's', $data['mobileUseFl']);
        $this->db->bind_param_push($arrBind, 's', $data['mobileImage']);
        $this->db->bind_param_push($arrBind, 's', $data['mobileSoldOutFl']);
        $this->db->bind_param_push($arrBind, 's', $data['mobileSoldOutDisplayFl']);

        $this->db->bind_param_push($arrBind, 's', $data['mobileSoldOutIconFl']);
        $this->db->bind_param_push($arrBind, 's', $data['mobileIconFl']);
        $this->db->bind_param_push($arrBind, 's', implode(INT_DIVISION, $data['mobileDisplayField']));
        $this->db->bind_param_push($arrBind, 's', $data['mobileDisplayType']);
        $this->db->bind_param_push($arrBind, 's', @implode(INT_DIVISION, $data['goodsDiscount']));
        $this->db->bind_param_push($arrBind, 's', @implode(INT_DIVISION, $data['priceStrike']));
        $this->db->bind_param_push($arrBind, 's', @implode(INT_DIVISION, $data['displayAddField']));
        $this->db->bind_param_push($arrBind, 's', @implode(INT_DIVISION, $data['mobileGoodsDiscount']));
        $this->db->bind_param_push($arrBind, 's', @implode(INT_DIVISION, $data['mobilePriceStrike']));
        $this->db->bind_param_push($arrBind, 's', @implode(INT_DIVISION, $data['mobileDisplayAddField']));

        $this->db->bind_param_push($arrBind, 'i', $data['sno']);

        $this->db->bind_query($strSQL, $arrBind);
    }

    public function deletePopulateSettings($data){

        //삭제 코드
        foreach($data['populateSno'] as $value){
            $arrBind = [];
            $strSQL = 'DELETE FROM '.DB_POPULATE_THEME.' WHERE sno = ?';
            $this->db->bind_param_push($arrBind, 'i', $value);

            $this->db->bind_query($strSQL, $arrBind);
            unset($arrBind);
        }
    }
    /**
     * 상품 정보 출력 (상품 리스트)
     *
     * @param int $pageNum 페이지 당 리스트수 (default 10)
     * @return array 상품 정보
     * @throws Exception
     */
    public function getPopulateList($pageNum = 10)
    {
        $getValue = Request::get()->toArray();

        //검색 정리
        $this->search['keyword'] = $getValue['keyword'];
        $this->search['searchDateFl'] = gd_isset($getValue['searchDateFl'], 'regDt');

        $this->search['searchPeriod'] = gd_isset($getValue['searchPeriod'], '-1');
        $this->search['searchTypeFl'] = $getValue['searchTypeFl'];

        $selected['searchDateFl'][$getValue['searchDateFl']] = 'selected';
        $selected['searchTypeFl'][$getValue['searchTypeFl']] = 'selected';

        // --- 정렬 설정
        if (gd_isset($getValue['sort'])) {
            $sort[] = $getValue['sort'];
        } else {

            if ($displayOrder) {
                if (is_array($displayOrder)) $sort[] = implode(",", $displayOrder);
                else $sort[] = $displayOrder;

            } else {
                $sort[] = "sno desc";
            }
        }

        $dbTable = DB_POPULATE_THEME;
        $sort = implode(',', $sort);

        // --- 페이지 기본설정
        gd_isset($getValue['page'], 1);

        // 배수 설정
        $getData['multiple'] = range($displayCnt, $displayCnt * 4, $displayCnt);

        $page = \App::load('\\Component\\Page\\Page', $getValue['page']);
        $page->page['list'] = $pageNum; // 페이지당 리스트 수
        $page->block['cnt'] = Request::isMobile() ? 5 : 10; // 블록당 리스트 개수
        $page->recode['amount'] = $this->db->getCount($dbTable, 'sno'); // 전체 레코드 수
        $page->setPage();
        $page->setUrl(\Request::getQueryString());

        // 조건절 설정
        $this->arrWhere[] = '1 = 1';
        //인기상품 노출명
        if(!empty($this->search['keyword'])){
            $this->arrWhere[] ='`populateName` LIKE concat(\'%\',?,\'%\')';
            $this->db->bind_param_push($this->arrBind, 's', $this->search['keyword']);
        }

        if( $this->search['searchPeriod']  < 0) {
            $this->search['searchDate'][] = gd_isset($getValue['searchDate'][0]);
            $this->search['searchDate'][] = gd_isset($getValue['searchDate'][1]);
        } else {
            $this->search['searchDate'][] = gd_isset($getValue['searchDate'][0], date('Y-m-d', strtotime('-6 day')));
            $this->search['searchDate'][] = gd_isset($getValue['searchDate'][1], date('Y-m-d'));
        }

        //기간검색
        if ($this->search['searchDateFl'] && $this->search['searchDate'][0] && $this->search['searchDate'][1]) {
            $this->arrWhere[] = $this->search['searchDateFl'] . ' BETWEEN ? AND ?';
            $this->db->bind_param_push($this->arrBind, 's', $this->search['searchDate'][0] . ' 00:00:00');
            $this->db->bind_param_push($this->arrBind, 's', $this->search['searchDate'][1] . ' 23:59:59');
        }
        //순위타입
        if (!empty($this->search['searchTypeFl'])) {
            $this->arrWhere[] = '`type` = ?';
            $this->db->bind_param_push($this->arrBind, 's', $this->search['searchTypeFl']);
        }

        // 필드 설정
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
        $this->db->strOrder = $sort;
        $this->db->strLimit = $page->recode['start'] . ',' . $pageNum;

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . $dbTable . implode(' ', $query);

        $data = $this->db->query_fetch($strSQL, $this->arrBind);

        if($data) {
            /* 검색 count 쿼리 */
            $totalCountSQL =  ' SELECT COUNT(gl.goodsNo) AS totalCnt FROM ' . $dbTable . ' as gl '.implode('', $arrJoin).'  WHERE '.implode(' AND ', $this->arrWhere);
            $dataCount = $this->db->query_fetch($totalCountSQL, $this->arrBind,false);
            unset($this->arrBind, $this->arrWhere);

            // 검색 레코드 수
            $page->recode['total'] = $dataCount['totalCnt']; //검색 레코드 수

            $page->setPage();
        }

        unset($this->arrBind, $this->arrWhere);

        // 각 데이터 배열화
        $getData['listData'] = gd_htmlspecialchars_stripslashes(gd_isset($data));
        $getData['listSort'] = $displayOrder;
        $getData['listSearch'] = gd_htmlspecialchars($this->search);
        $getData['listSelected'] = gd_htmlspecialchars($selected);
        unset($this->search);
        return $getData;

    }

    function populateGoods($data){
        $arrOriginalGoodsNo = explode(',', $data['goodsNo']);
        foreach($data['sno'] as $value){
            $strSQL = ' SELECT * FROM ' . DB_POPULATE_THEME . ' WHERE sno = ?';
            $this->db->bind_param_push($arrBind, 's', $value);
            $res = $this->db->query_fetch($strSQL, $arrBind, false);
            unset($arrBind);

            $arrGoodsNo = explode(INT_DIVISION, $res['goodsNo']);
            $arrExceptGoodsNo = explode(INT_DIVISION, $res['except_goodsNo']);
            if($data['batchChange'] == 'y') {
                //포함일 경우
                if($res['range'] != 'all') {
                    foreach ($arrGoodsNo as $value2) {
                        if (!empty($value2)) {
                            //중복 체크
                            if (in_array($value2, $arrOriginalGoodsNo)) {
                                continue;
                            }
                            $newGoodsNo[] = $value2;
                        }
                    }

                    foreach ($arrOriginalGoodsNo as $value2) {
                        if (!empty($value2)) {
                            $newGoodsNo[] = $value2;
                        }
                    }

                    $newGoodsNo = implode(INT_DIVISION, $newGoodsNo);

                    $strSQL = 'UPDATE ' . DB_POPULATE_THEME . ' SET goodsNo = ? WHERE sno = ?';
                    $this->db->bind_param_push($arrBind, 's', $newGoodsNo);
                    $this->db->bind_param_push($arrBind, 's', $value);
                    $this->db->bind_query($strSQL, $arrBind);
                    unset($newGoodsNo);
                    unset($arrBind);
                }

                //예외에서 삭제
                foreach ($arrExceptGoodsNo as $value2) {
                    if(!empty($value2)){
                        if (!in_array($value2, $arrOriginalGoodsNo)) {
                            $newGoodsNo[] = $value2;
                        }
                    }
                }
                $strSQL = 'UPDATE '.DB_POPULATE_THEME.' SET except_goodsNo = ? WHERE sno = ?';
                $newGoodsNo = implode(INT_DIVISION, $newGoodsNo);
                $this->db->bind_param_push($arrBind, 's', $newGoodsNo);
                $this->db->bind_param_push($arrBind, 's', $value);

                $this->db->bind_query($strSQL, $arrBind);
                unset($newGoodsNo);
                unset($arrBind);
            }else{
                //미 포함일 경우

                foreach($arrGoodsNo as $value2) {
                    if(!in_array($value2, $arrOriginalGoodsNo)){
                        $newGoodsNoArr[] = $value2;
                    }
                }
                $newGoodsNo = implode(INT_DIVISION, $newGoodsNoArr);
                unset($newGoodsNoArr);

                $strSQL = 'UPDATE '.DB_POPULATE_THEME.' SET goodsNo = ? WHERE sno = ?';
                $this->db->bind_param_push($arrBind2, 's', $newGoodsNo);
                $this->db->bind_param_push($arrBind2, 's', $value);
                $this->db->bind_query($strSQL, $arrBind2);
                if($res['range'] != 'goods'){
                    //특정상품진열이 아니면 exceptGoodsNo 필드에 삽입(중복 제외)
                    foreach($arrExceptGoodsNo as $value2){
                        if(!empty($value2)){
                            if(in_array($value2, $arrOriginalGoodsNo)){
                                continue;
                            }
                            $newGoodsNoArr[] = $value2;
                        }
                    }
                    foreach($arrOriginalGoodsNo as $value2){
                        if(!empty($value2)) {
                            $newGoodsNoArr[] = $value2;
                        }
                    }

                    $newGoodsNo = implode(INT_DIVISION, $newGoodsNoArr);
                    unset($newGoodsNoArr);
                    $strSQL = 'UPDATE '.DB_POPULATE_THEME.' SET except_goods = \'y\', except_goodsNo = ? WHERE sno = ?';
                    $this->db->bind_param_push($arrBind3, 's', $newGoodsNo);
                    $this->db->bind_param_push($arrBind3, 's', $value);
                    $this->db->bind_query($strSQL, $arrBind3);
                }
            }

            unset($arrBind);
            unset($arrBind2);
            unset($arrBind3);
            unset($newGoodsNo);
            unset($tmp);
        }
    }

    public function wrongParameter(){
        echo '<script type="text/javascript">';
        echo 'alert("잘못된 접근 입니다");';
        echo 'history.go(-1);';
        echo '</script>';
    }

    public function getTotalPopulateThemeCnt(){
        $strSQL = 'SELECT count(*) cnt FROM '.DB_POPULATE_THEME;
        $getData = $this->db->query_fetch($strSQL);
        $cnt = $getData[0]['cnt'];

        return $cnt;
    }

    public function getPopulateData(){
        $arrBind = [];
        $strOrder = "regDt DESC ";
        $strSQL = "SELECT *  FROM " .DB_POPULATE_THEME. " ORDER BY ".$strOrder;
        $getData = $this->db->query_fetch($strSQL, $arrBind['bind']);

        return $getData;
    }
}
