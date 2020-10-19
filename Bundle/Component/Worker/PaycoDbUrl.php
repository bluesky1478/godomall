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


use Framework\Utility\ComponentUtils;
use Framework\Utility\NumberUtils;
use Framework\Utility\StringUtils;

/**
 * Class PaycoDbUrl
 *
 * @package Bundle\Component\Worker
 * @author  <kookoo135@godo.co.kr>
 */
class PaycoDbUrl extends \Component\Worker\AbstractDbUrl
{
    /** @var array $header payco dburl 3.0 header */
    //@formatter:off
    protected $header = ['id','title','price_pc','price_mobile','normal_price','link','image_link','add_image_link','category_name1','category_name2','category_name3','category_name4','naver_category','naver_product_id','condition','import_flag','parallel_import','order_made','product_flag','adult','model_number','brand','maker','origin','event_words','coupon','interest_free_event',' point','installation_costs','search_tag','minimum_purchase_quantity','review_count','shipping','delivery_grade','delivery_detail','attribute','age_group','gender'];
    //@formatter:on
    /** @var array $mobileConfig mobile.config policy */
    protected $mobileConfig = [];
    /** @var null|\Bundle\Component\Goods\DefineGoods */
    private $_defineGoods = null;

    /**
     * init
     *
     * @throws \Exception
     */
    protected function init()
    {
        parent::init();
        $this->mobileConfig = ComponentUtils::getPolicy('mobile.config');
        $paycoHeader = $this->header;
        if ($this->mobileConfig['mobileShopFl'] == 'y') {
            array_splice($paycoHeader, 5, 0, 'mobile_link');
        }
        $this->writeDbUrl(implode(chr(9), $paycoHeader));
    }


    protected function loadConfig()
    {
        $componentDbUrl = \App::load('Component\\Marketing\\DBUrl');
        $this->config = $componentDbUrl->getConfig('naver', 'config');
        $this->paycoConfig = $componentDbUrl->getConfig('payco', 'config');
        $this->config['paycoFl'] = $this->paycoConfig['paycoFl'];
        $this->_defineGoods = \App::load('Component\\Goods\\DefineGoods');
    }

    /**
     * DbUrl 사용함 상태 확인
     *
     * @return bool
     */
    protected function notUseDbUrl(): bool
    {
        if (!key_exists('paycoFl', $this->config)) {
            $this->loadConfig();
        }

        return $this->config['paycoFl'] != 'y';
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
        $this->goodsWheres[] = " g.paycoFl = 'y'";
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
        $this->goodsWheres[] = " g.paycoFl = 'y'";
        $wheres = $this->goodsWheres;
        if (key_exists('where', $params) && count($params['where']) > 0) {
            $wheres = array_merge($wheres, $params['where']);
        }
        $strSQL = ' SELECT COUNT(*) AS cnt FROM ' . DB_GOODS . ' as g  WHERE ' . implode(' AND ', $wheres);

        $db = \App::getInstance('DB');
        $resultSet = $db->query_fetch($strSQL, null, false);

        return $resultSet['cnt'];
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
     * getPaycoReviewCount
     *
     * @param array $data
     *
     * @return int
     */
    protected function getPaycoReviewCount(array $data): int
    {
        if ($this->config['naverReviewChannel'] == 'plusReview') {
            $reviewCnt = $data['plusReviewCnt'];
        } else if ($this->config['naverReviewChannel'] == 'both') {
            $reviewCnt = $data['plusReviewCnt'] + $data['reviewCnt'];
        } else {
            $reviewCnt = $data['reviewCnt'];
        }

        return $reviewCnt;
    }

    /**
     * selectGoods
     *
     * @param int $goodsNo
     *
     * @return \Generator
     */
    protected function selectGoodsGenerator(int $goodsNo): \Generator
    {
        $db = \App::getInstance('DB');
        $db->strField = $this->getFieldsGoods() . ",GROUP_CONCAT( gi.imageNo SEPARATOR '^|^') AS imageNo,GROUP_CONCAT( gi.imageKind SEPARATOR '^|^') AS imageKind,GROUP_CONCAT( gi.imageName SEPARATOR '^|^') AS imageName";
        $db->strWhere = implode(' AND ', $this->goodsWheres) . " AND g.goodsNo between " . ($goodsNo - $this->limit) . " AND " . $goodsNo;
        //        $db->strWhere = implode(' AND ', $this->goodsWheres);
        $db->strOrder = 'g.goodsNo DESC';
        $db->strGroup = "g.goodsNo";
        $db->strJoin = 'INNER JOIN es_goodsImage gi on gi.goodsNo = g.goodsNo AND  gi.imageKind IN ("magnify", "detail") AND gi.imageNo < 10';
        //        $db->strLimit = ($goodsNo * $this->offset) . ', ' . $this->offset;
        $this->goodsQuery = $db->query_complete();
        $strSQL = 'SELECT ' . array_shift($this->goodsQuery) . ' FROM ' . DB_GOODS . ' as g' . implode(' ', $this->goodsQuery);

        return $db->query_fetch_generator($strSQL);
    }

    /**
     * makeDbUrl
     *
     * @param \Generator $goodsGenerator
     * @param int        $pageNumber
     *
     * @return bool
     * @throws \Exception
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
            if ($goods['goodsNmPartner']) {
                $goods['goodsNm'] = $goods['goodsNmPartner'];
            }
            if (StringUtils::strIsSet($goods['salesUnit'], 0) > $goods['minOrderCnt']) {
                $goods['minOrderCnt'] = $goods['salesUnit'];
            }

            $cateListNm = [];
            if ($goods['cateCd']) {
                if (empty($this->categoryStorage[$goods['cateCd']]) === true) {
                    $cateList = $this->componentCategory->getCategoriesPosition($goods['cateCd'])[0];
                    $this->categoryStorage[$goods['cateCd']] = $cateList;

                }
                $cateList = $this->categoryStorage[$goods['cateCd']];
                if ($cateList) {
                    $cateListNm = array_values($cateList);
                }
                unset($cateList);
            }

            $minOrderCnt = StringUtils::strIsSet($goods['minOrderCnt'], 1);
            $goodsPrice = $goods['goodsPrice'];
            if ($this->config['dcGoods'] == 'y' && $goods['goodsPrice'] > 0 && $goods['goodsDiscountFl'] == 'y') {
                $goodsPrice = $goodsPrice - $this->getGoodsDcPrice($goods);
            }

            $mileage = $this->setGoodsMileage($goods);
            $goodsPrice = $goodsPrice * $minOrderCnt;
            $deliveryPrice = $this->setDeliveryPrice($goods);

            $couponPrice = StringUtils::strIsSet($this->setGoodsCoupon($goods), 0);

            if ($this->config['dcCoupon'] == 'y' && $couponPrice > 0 && $goodsPrice - $couponPrice >= 0) {
                $goodsPrice = $goodsPrice - $couponPrice;
            } else {
                if ($goodsPrice - $couponPrice < 0) {
                    $goodsPrice = 0;
                }
                $couponPrice = 0;
            }

            $goodsImages = $this->getGoodsImageSrcWithAddImageSrc($goods);
            $goodsImageSrc = $goodsImages[0];
            $goodsAddImageSrc = $goodsImages[1];

            if (empty($goodsImageSrc) || !is_numeric($deliveryPrice) || $goodsPrice <= 0 || empty($goods['goodsPriceString']) === false) {
                continue;
            }

            $goodsMustInfo = $this->getGoodsMustInfo($goods);
            $installationCosts = $goodsMustInfo[0];
            $deliveryGrade = $goodsMustInfo[1];
            $deliveryDetail = $goodsMustInfo[2];

            $result = '';
            $result .= $goods['goodsNo'] . chr(9);
            $this->selectCategoryBrand($goods);
            $goods['brandNm'] = $this->brandStorage[$goods['brandCd']];
            $goods['goodsNm'] = $this->replaceGoodsName($goods);
            $result .= StringUtils::htmlSpecialCharsStripSlashes($goods['goodsNm']) . chr(9);

            $eventDesc = $this->getEventDescription($goods);

            $result .= NumberUtils::moneyFormat($goodsPrice, false) . chr(9);
            $result .= NumberUtils::moneyFormat($goodsPrice, false) . chr(9);
            $result .= NumberUtils::moneyFormat($goods['fixedPrice'] * $minOrderCnt, false) . chr(9);
            $result .= 'http://' . $this->policy['basic']['info']['mallDomain'] . '/goods/goods_view.php?goodsNo=' . $goods['goodsNo'] . chr(9);
            if ($this->mobileConfig['mobileShopFl'] == 'y') {
                $result .= 'http://m.' . str_replace('www.', '', $this->policy['basic']['info']['mallDomain']) . '/goods/goods_view.php?goodsNo=' . $goods['goodsNo'] . chr(9);
            }

            $result .= $goodsImageSrc . chr(9); // [필수] 이미지 URL
            $result .= $goodsAddImageSrc . chr(9); // [필수] 이미지 URL

            for ($i = 0; $i < 4; $i++) {
                $result .= StringUtils::strIsSet($cateListNm[$i]) . chr(9); // [필수] 대분류 카테고리 코드
            }
            unset($cateListNm);

            switch ($goods['naverImportFlag']) {
                case 'f':
                    $importFlag = "Y";
                    $parallelImport = "";
                    $orderMade = "";
                    break;
                case 'd':
                    $importFlag = "";
                    $parallelImport = "Y";
                    $orderMade = "";
                    break;
                case 'o':
                    $importFlag = "";
                    $parallelImport = "";
                    $orderMade = "Y";
                    break;
                default:
                    $importFlag = "";
                    $parallelImport = "";
                    $orderMade = "";
                    break;
            }

            $onlyAdultFl = $this->getOnlyAdultFl($goods);

            $result .= StringUtils::strIsSet($goods['naverCategory']) . chr(9); // 네이버카테고리
            $result .= StringUtils::strIsSet($goods['naverProductId']) . chr(9); // 가격비교페이지ID
            $result .= $this->_defineGoods->getGoodsStateList()[$goods['goodsState']] . chr(9); // 상품상태
            $result .= $importFlag . chr(9); // 해외구매대행여부
            $result .= $parallelImport . chr(9); // 병행수입여부
            $result .= $orderMade . chr(9); // 주문제작상품여부
            $result .= $this->_defineGoods->getGoodsSellType()[$goods['naverProductFlag']] . chr(9); // 판매방식구분
            $result .= $onlyAdultFl . chr(9); // 미성년자구매불가상품여부

            $result .= StringUtils::strIsSet($goods['goodsModelNo']) . chr(9); // [선택] 모델명
            $result .= StringUtils::strIsSet($goods['brandNm']) . chr(9); // [선택] 모델명
            $result .= StringUtils::strIsSet($goods['makerNm']) . chr(9); // [선택] 모델명
            $result .= StringUtils::strIsSet($goods['originNm']) . chr(9); // [선택] 모델명
            $result .= $eventDesc . chr(9); // [선택] 이벤트문구

            if ($couponPrice > 0) {
                $result .= NumberUtils::moneyFormat($couponPrice, false) . '원' . chr(9); // [선택] 쿠폰
            } else {
                $result .= '' . chr(9); // [선택] 쿠폰
            }
            $result .= $this->config['nv_pcard'] . chr(9); // [선택] 무이자
            if ($mileage > 0) {
                $result .= '쇼핑몰자체포인트^' . $mileage . chr(9); // [선택] 마일리지
            } else {
                $result .= '' . chr(9); // [선택] 마일리지
            }
            $result .= StringUtils::strIsSet($installationCosts) . chr(9); //추가설치비용
            $result .= StringUtils::strIsSet($goods['goodsSearchWord']) . chr(9); //기본정보-검색키워드 항목 사용
            $result .= $minOrderCnt . chr(9); //판매정보-구매수량설정 항목 사용
            $result .= StringUtils::strIsSet($this->getPaycoReviewCount($goods), 0) . chr(9);

            $result .= StringUtils::strIsSet($deliveryPrice) . chr(9); // [선택] 배송비
            $result .= StringUtils::strIsSet($deliveryGrade) . chr(9); // 배송 · 설치비용
            $result .= StringUtils::strIsSet($deliveryDetail) . chr(9); // 배송 · 설치비용 내용
            $result .= StringUtils::strIsSet($goods['naverAttribute']) . chr(9); //추가 : 입력기능 추가
            $result .= $this->_defineGoods->getGoodsAgeType()[StringUtils::strIsSet($goods['naverAgeGroup'], 'a')] . chr(9); //(주 사용 연령대)
            $result .= $this->_defineGoods->getGoodsGenderType()[$goods['naverGender']]; //(주 사용 성별)
            $this->writeDbUrl($result);
            $this->totalDbUrlData++;
        }

        return true;
    }

    /**
     * 이미지 경로, 추가 이미지 경로 반환
     *
     * @param array $goods
     *
     * @return array
     * @throws \Exception
     */
    protected function getGoodsImageSrcWithAddImageSrc($goods)
    {
        $addImage = [];
        $addImageSrc = $imageSrc = "";
        if ($goods['imageName']) {
            $names = explode(STR_DIVISION, $goods['imageName']);
            $kinds = explode(STR_DIVISION, $goods['imageKind']);
            $numbers = explode(STR_DIVISION, $goods['imageNo']);

            foreach ($names as $imageNameKey => $imageName) {
                $image = $this->getGoodsImage($imageName, $goods['imagePath'], $goods['imageStorage']);
                if ($kinds[$imageNameKey] == 'magnify' && $numbers[$imageNameKey] == 0) {
                    $imageSrc = $image;
                } else {
                    $addImage[] = $image;
                }
                unset($image);
            }
            unset($names, $kinds, $numbers);

            if (empty($imageSrc) === true) {
                $imageSrc = $addImage[0];
                unset($addImage[0]);
            }

            if ($addImage) {
                $addImageSrc = implode("|", array_slice($addImage, 0, 10));
            }
        }

        unset($addImage);

        return [
            $imageSrc,
            $addImageSrc,
        ];
    }

    /**
     * getOnlyAdultFl
     *
     * @param array $goods
     *
     * @return string
     */
    protected function getOnlyAdultFl(array $goods)
    {
        $flag = "";
        if ($goods['onlyAdultFl'] == 'y') {
            $flag = "Y";
        }

        return $flag;
    }
}
