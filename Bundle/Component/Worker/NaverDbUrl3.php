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
 * Class NaverDbUrl3
 *
 * @package Bundle\Component\Worker
 * @author  yjwee <yeongjong.wee@godo.co.kr>
 */
class NaverDbUrl3 extends \Component\Worker\NaverDbUrl
{
    /** @var array $header naver dburl 3.0 header */
    //@formatter:off
    protected $header = ['id','title','price_pc','normal_price','link','image_link','add_image_link','category_name1','category_name2','category_name3','category_name4','naver_category','naver_product_id','condition','import_flag','parallel_import','order_made','product_flag','adult','model_number','brand','maker','origin','event_words','coupon','interest_free_event',' point','installation_costs','search_tag','minimum_purchase_quantity','review_count','shipping','delivery_grade','delivery_detail','attribute','age_group','gender'];
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
        $naverHeader = $this->header;
        if ($this->mobileConfig['mobileShopFl'] == 'y') {
            array_splice($naverHeader, 5, 0, 'mobile_link');
        }

        $this->setNaverGradeCount();

        $this->writeDbUrl(implode(chr(9), $naverHeader));
    }

    /**
     * 등급에 맞는 네이버 EP생성 상품 최대수를 설정한다.
     */
    protected function setNaverGradeCount()
    {
        //등급설정값이 없다면 새싹으로 정의
        if (empty($this->config['naverGrade']) === true)
        {
            $naverGrade = '1';
        } else {
            $naverGrade = $this->config['naverGrade'];
        }

        //등급에 맞는 limit를 가지고 온다.
        $naverMaxLimit = $this->gatGradeMaxLimit($naverGrade);

        //max count를 재정의 한다.
        $this->setMaxCount($naverMaxLimit);
    }

    protected function loadConfig()
    {
        parent::loadConfig();
        $this->_defineGoods = \App::load('Component\\Goods\\DefineGoods');
    }

    /**
     * selectGoods
     *
     * @param int $goodsNo
     * @param int $sgoodsNo start goods no
     *
     * @return \Generator
     */

    protected function selectGoodsGenerator(int $goodsNo, int $sgoodsNo): \Generator
    {
        if (($goodsNo - $this->limit) < $sgoodsNo) {
            $startGoodsNo = $sgoodsNo;
        } else {
            $startGoodsNo = $goodsNo - $this->limit;
        }

        //상품혜택 사용여부
        $goodsBenefitUse = $this->componentBenefit->getConfig();

        $db = \App::getInstance('DB');
        if($goodsBenefitUse == 'y') {
            $db->strField = $this->getFieldsGoods() . ',' . $this->getFieldsGoodsLinkBenefit() . ",GROUP_CONCAT( gi.imageNo SEPARATOR '^|^') AS imageNo,GROUP_CONCAT( gi.imageKind SEPARATOR '^|^') AS imageKind,GROUP_CONCAT( gi.imageName SEPARATOR '^|^') AS imageName";
            $addJoin = ' LEFT JOIN ' . DB_GOODS_LINK_BENEFIT . ' AS gbl ON g.goodsNo = gbl.goodsNo  and ((gbl.benefitUseType=\'periodDiscount\' AND gbl.linkPeriodStart < NOW() AND gbl.linkPeriodEnd > NOW()) OR gbl.benefitUseType=\'nonLimit\' OR gbl.benefitUseType=\'newGoodsDiscount\')';
        }else{
            $db->strField = $this->getFieldsGoods() . ",GROUP_CONCAT( gi.imageNo SEPARATOR '^|^') AS imageNo,GROUP_CONCAT( gi.imageKind SEPARATOR '^|^') AS imageKind,GROUP_CONCAT( gi.imageName SEPARATOR '^|^') AS imageName";
        }
        $db->strWhere = implode(' AND ', $this->goodsWheres) . " AND g.goodsNo between " . $startGoodsNo . " AND " . $goodsNo;
        //        $db->strWhere = implode(' AND ', $this->goodsWheres);
        $db->strOrder = 'g.goodsNo DESC';
        $db->strGroup = "g.goodsNo";

        $db->strJoin = 'INNER JOIN es_goodsImage gi on gi.goodsNo = g.goodsNo AND  gi.imageKind IN ("magnify", "detail") AND gi.imageNo < 10' .$addJoin;
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

        //상품혜택 진행중인 배열
        $goodsBenefitIngData = $this->componentBenefit->goodsBenefitIng();

        while ($goodsGenerator->valid()) {
            if ($this->greaterThanMaxCount()) {
                break;
            }
            $goods = $goodsGenerator->current();
            $goodsGenerator->next();

            //상품 혜택 정보
            $goods = $this->componentBenefit->goodsDataFrontConvert($goods,$goodsBenefitIngData);

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
            $result .= NumberUtils::moneyFormat($goods['fixedPrice'] * $minOrderCnt, false) . chr(9);
            $result .= 'http://' . $this->policy['basic']['info']['mallDomain'] . '/goods/goods_view.php?goodsNo=' . $goods['goodsNo'] . '&inflow=naver' . chr(9); // [필수] 상품의 상세페이지 주소
            if ($this->mobileConfig['mobileShopFl'] == 'y') {
                $result .= 'http://m.' . str_replace('www.', '', $this->policy['basic']['info']['mallDomain']) . '/goods/goods_view.php?goodsNo=' . $goods['goodsNo'] . '&inflow=naver' . chr(9); // [필수] 상품의 모바일페이지 주소
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
            $goods['naverTag'] = implode("|", array_slice(explode("|", $goods['naverTag']), 0, 10));
            $result .= StringUtils::strIsSet($goods['naverTag']) . chr(9); //기본정보-검색키워드 항목 사용
            $result .= $minOrderCnt . chr(9); //판매정보-구매수량설정 항목 사용

            $totalcnt = 0;
            if($this->config['onlyMemberReviewUsed'] === "y") {
                $totalcnt = StringUtils::strIsSet($this->goodsReviewCnt($goods['goodsNo']), 0) . chr(9);
            } else {
                $totalcnt = StringUtils::strIsSet($this->getNaverReviewCount($goods), 0) . chr(9);
            }
            $result .= $totalcnt;

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
