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

use Component\PlusShop\PlusReview\PlusReviewConfig;
use Component\Database\DBTableField;
use Component\ExchangeRate\ExchangeRate;
use Component\Member\Group\Util;
use Component\Validator\Validator;
use Cookie;
use Exception;
use Framework\Utility\ArrayUtils;
use Framework\Utility\SkinUtils;
use Framework\Utility\StringUtils;
use Globals;
use Request;
use Session;
use UserFilePath;
use Framework\Utility\DateTimeUtils;

/**
 * 상품 class
 */
class Goods
{
    const ERROR_VIEW = 'ERROR_VIEW';

    const TEXT_INVALID_ARG = '%s인자가 잘못되었습니다.';

    const TEXT_NOT_EXIST_CATECD = 'NOT_EXIST_CATECD';

    const TEXT_NOT_EXIST_GOODSNO = 'NOT_EXIST_GOODSNO_VIEW';

    const TEXT_NOT_ACCESS_GOODS = 'NOT_ACCESS_CATEGORY';

    /**
     * @var RECENT_KEYWORD_MAX_COUNT 최근검색어 기록 갯수
     */
    const RECENT_KEYWORD_MAX_COUNT = 10;

    protected $db;

    protected $arrBind = [];
    // 리스트 검색관련
    protected $arrWhere = [];
    // 리스트 검색관련
    protected $checked = [];
    // 리스트 검색관련
    protected $search = [];
    // 리스트 검색관련
    protected $useTable = [];

    // 리스트 검색관련 (사용 테이블)
    protected $goodsListField = '';

    // 일반샵과 모바일샵 상품 출력 구분을 위한
    protected $goodsDisplayFl = 'goodsDisplayFl';
    protected $goodsSellFl = 'goodsSellFl';


    protected $goodsStateList = [];
    protected $goodsPermissionList = [];
    protected $goodsColorList = [];
    protected $goodsPayLimit = [];

    protected $goodsImportType = [];
    protected $goodsSellType = [];
    protected $goodsAgeType = [];
    protected $goodsGenderType = [];

    protected $memberGroupInfo = [];
    protected $fixedSales = [];
    protected $trunc;

    public $_memInfo;

    protected $hscode;
    protected $gGlobal;
    protected $themeConfig;

    protected $kcmarkCode;
    protected $kcmarkInfoList = [];
    private $_kcmarkUrl = 'http://www.safetykorea.kr/search/searchPop?certNum=';
    private $_nKcmarkUrl = 'https://rra.go.kr/ko/license/A_c_search.do';
    /**
     * 생성자
     */
    public function __construct()
    {
        if (!is_object($this->db)) {
            $this->db = \App::load('DB');
        }

        // 상품 출력여부 설정
        //&& $_mcfg['mobileShopGoodsFl'] == 'each' 모바일샵 설정 체크 삭제
        if (Request::isMobile()) {
            $this->goodsDisplayFl = 'goodsDisplayMobileFl';
            $this->goodsSellFl = 'goodsSellMobileFl';
        }

        $defineGoods = \App::load('Component\\Goods\\DefineGoods');

        $this->goodsStateList = $defineGoods->getGoodsStateList();
        $this->goodsPermissionList = $defineGoods->getGoodsPermissionList();
        $this->goodsPayLimit = $defineGoods->getGoodsPayLimit();

        $this->goodsImportType = $defineGoods->getGoodsImportType();
        $this->goodsSellType = $defineGoods->getGoodsSellType();
        $this->goodsAgeType = $defineGoods->getGoodsAgeType();
        $this->goodsGenderType = $defineGoods->getGoodsGenderType();
        $this->fixedSales = $defineGoods->getFixedSales();
        $this->fixedOrderCnt = $defineGoods->getFixedOrderCnt();


        $this->hscode = $defineGoods->getHscode();

        $member = \App::Load(\Component\Member\Member::class);
        $this->_memInfo = $member->getMemberInfo();

        $this->trunc = Globals::get('gTrunc.goods');
        $this->gGlobal = Globals::get('gGlobal');

        //상품테이블 분리 관련
        $this->goodsDivisionFl=  gd_policy('goods.config')['divisionFl'] == 'y' ? true : false;
        if($this->goodsDivisionFl) $this->goodsTable = DB_GOODS_SEARCH;
        else $this->goodsTable = DB_GOODS;

        //상품이미지 관련
        $this->goodsImageLazyFl=  gd_policy('goods.display')['imageLazyFl'] == 'y' ? true : false;

        //KC인증마크 관련
        $this->kcmarkCode = $defineGoods->getKcmarkCode();

    }

    /**
     * 상품명 처리 (확장여부에 따른 상품명 처리)
     *
     * @param string $goodsNmTarget 처리할 상품명 (기본 null)
     * @param string $goodsNmOrigin 기본 상품명 (기본 null)
     * @param string $goodsNmFl     확장여부 (기본값 e - 확장)
     *
     * @return string 상품명
     */
    protected function getGoodsName($goodsNmTarget = null, $goodsNmOrigin = null, $goodsNmFl = 'e')
    {
        if(SESSION::has(SESSION_GLOBAL_MALL)) {
            return $goodsNmOrigin;
        }
        // return 할 상품명
        $returnGoodsNm = '';

        // 상품명이 없다면 빈값 return
        if (is_null($goodsNmTarget) && is_null($goodsNmOrigin)) {
            $returnGoodsNm = '';
        } else {
            // 기본 사용시 일반 상품명 처리
            if ($goodsNmFl == 'd') {
                $returnGoodsNm = $goodsNmOrigin;

                // 확장인경우 확장 상품명으로 처리
            } else if ($goodsNmFl == 'e') {
                $returnGoodsNm = gd_isset($goodsNmTarget, $goodsNmOrigin);

                // 혹시라도 확장여부가 없는경우는 일반 상품명으로 처리
            } else {
                $returnGoodsNm = $goodsNmOrigin;
            }
        }
        return  StringUtils::xssClean($returnGoodsNm);

    }

    /**
     * 상품 리스트용 필드
     */
    protected function setGoodsListField()
    {
        $this->goodsListField = 'g.goodsNo, g.cateCd, g.scmNo, g.brandCd, g.goodsNmFl, g.goodsNmMain, g.goodsNmList, g.goodsNm, g.mileageFl, g.goodsPriceString, g.optionName, \'\' as optionValue,g.optionFl,g.minOrderCnt , g.stockFl,g.goodsModelNo,g.onlyAdultFl,g.onlyAdultImageFl,g.orderCnt,
            g.makerNm, g.shortDescription, g.imageStorage, g.imagePath,g.goodsCd,g.soldOutFl,
            ( if (g.soldOutFl = \'y\' , \'y\', if (g.stockFl = \'y\' AND g.totalStock <= 0, \'y\', \'n\') ) ) as soldOut,
            ( if (g.' . $this->goodsDisplayFl . ' = \'y\' , if (g.' . $this->goodsSellFl . ' = \'y\', g.' . $this->goodsSellFl . ', \'n\') , \'n\' ) ) as orderPossible,
            g.goodsPrice, g.fixedPrice, g.mileageGoods, g.mileageGoodsUnit, g.hitCnt, g.goodsDiscountFl, g.goodsDiscount, g.goodsDiscountUnit, g.goodsPermission, g.goodsPermissionGroup, g.goodsPermissionPriceStringFl, g.goodsPermissionPriceString, g.mileageGroup, g.mileageGroupInfo, g.mileageGroupMemberInfo, g.fixedGoodsDiscount, g.goodsDiscountGroup, g.goodsDiscountGroupMemberInfo, g.exceptBenefit, g.exceptBenefitGroup, g.exceptBenefitGroupInfo, g.salesUnit, g.fixedSales, g.fixedOrderCnt,
            g.goodsBenefitSetFl,g.benefitUseType,g.newGoodsRegFl,g.newGoodsDate,g.newGoodsDateFl,g.periodDiscountStart,g.periodDiscountEnd,g.regDt,g.modDt, g.goodsColor
            ';
        // ( if (g.stockFl = \'y\' , if (g.totalStock = 0, \'y\', \'n\') , if (g.soldOutFl = \'y\', \'y\', \'n\') ) ) as soldOut,
    }

    /**
     * 상품 정보 출력
     * 완성된 쿼리문은 $db->strField , $db->strJoin , $db->strWhere , $db->strGroup , $db->strOrder , $db->strLimit 멤버 변수를
     * 이용할수 있습니다.
     *
     * @param string $goodsNo 상품 번호 (기본 null)
     * @param string $goodsField 출력할 필드명 (기본 null)
     * @param array $arrBind bind 처리 배열 (기본 null)
     * @param bool $dataArray return 값을 배열처리 (기본값 false)
     * @param boolean $usePage paging 사용여부 (기본값 false)
     * @return array 상품 정보
     */
    public function getGoodsInfo($goodsNo = null, $goodsField = null, $arrBind = null, $dataArray = false, $usePage = false)
    {
        if (is_null($arrBind)) {
            // $arrBind = array();
        }
        if (isset($goodsNo)) {
            if ($this->db->strWhere) {
                $this->db->strWhere = " g.goodsNo = ? AND " . $this->db->strWhere;
            } else {
                $this->db->strWhere = " g.goodsNo = ?";
            }
            $this->db->bind_param_push($arrBind, 'i', $goodsNo);
        }
        if ($goodsField) {
            if ($this->db->strField) {
                $this->db->strField = $goodsField . ', ' . $this->db->strField;
            } else {
                $this->db->strField = $goodsField;
            }
        }

        /* 검색 count 쿼리 */
        if ($usePage === true) {
            $totalCountSQL =  ' SELECT COUNT(DISTINCT g.goodsNo) AS totalCnt FROM '.DB_GOODS.' as g  WHERE ' . $this->db->strWhere;
            $dataCount = $this->db->slave()->query_fetch($totalCountSQL, $arrBind,false);
            $page = \App::load('\\Component\\Page\\Page');
            $page->recode['total'] = $dataCount['totalCnt']; //검색 레코드 수
            $page->setPage();
        }

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_GOODS . ' g ' . implode(' ', $query);

        $getData = $this->db->slave()->query_fetch($strSQL, $arrBind);

        if (count($getData) == 1 && $dataArray === false) {
            return gd_htmlspecialchars_stripslashes($getData[0]);
        }
        return gd_htmlspecialchars_stripslashes($getData);
    }

    /**
     * 관련상품 정보 출력
     * 완성된 쿼리문은 $db->strField , $db->strJoin , $db->strWhere , $db->strGroup , $db->strOrder , $db->strLimit 멤버 변수를
     * 이용할수 있습니다.
     *
     * @param string $goodsNo 상품 번호 (기본 null)
     * @param string $goodsField 출력할 필드명 (기본 null)
     * @param array $arrBind bind 처리 배열 (기본 null)
     * @param bool $dataArray return 값을 배열처리 (기본값 false)
     * @param string $getMethod 자동 노출인 경우 상품상세 접근한 상품은 제외 (기본 null)
     * @return array 상품 정보
     */
    public function getGoodsAutoRelation($goodsNo = null, $goodsField = null, $arrBind = null, $dataArray = false, $getMethod = null)
    {
        if (is_null($arrBind)) {
            // $arrBind = array();
        }
        if ($goodsNo) {
            if ($this->db->strWhere) {
                $this->db->strWhere = ($getMethod == 'relation_a') ? " g.goodsNo != ? AND " . $this->db->strWhere : " g.goodsNo = ? AND " . $this->db->strWhere;
            } else {
                $this->db->strWhere = ($getMethod == 'relation_a') ? " g.goodsNo != ?" : " g.goodsNo = ?";
            }
            $this->db->bind_param_push($arrBind, 'i', $goodsNo);
        }
        if ($goodsField) {
            if ($this->db->strField) {
                $this->db->strField = $goodsField . ', ' . $this->db->strField;
            } else {
                $this->db->strField = $goodsField;
            }
        }

        $limit = $this->db->strLimit;
        unset($this->db->strLimit);
        unset($this->db->strOrder);

        $query = $this->db->query_complete();
        $strSQL = 'SELECT * FROM (SELECT ' . array_shift($query) . ' FROM ' . DB_GOODS . ' g ' . implode(' ', $query).' ORDER BY g.goodsNo DESC  LIMIT 0,100 ) AS goodsTable ORDER BY rand()   LIMIT '.$limit;

        $getData = $this->db->query_fetch($strSQL, $arrBind);

        if (count($getData) == 1 && $dataArray === false) {
            return gd_htmlspecialchars_stripslashes($getData[0]);
        }

        return gd_htmlspecialchars_stripslashes($getData);
    }

    /**
     * 상품 옵션 정보 출력
     * 완성된 쿼리문은 $db->strField , $db->strJoin , $db->strWhere , $db->strGroup , $db->strOrder , $db->strLimit 멤버 변수를
     * 이용할수 있습니다.
     *
     * @param string $goodsNo    상품 번호 (기본 null)
     * @param string $goodsField 출력할 필드명 (기본 null)
     * @param array  $arrBind    bind 처리 배열 (기본 null)
     * @param string $dataArray  return 값을 배열처리 (기본값 false)
     *
     * @return array 상품 정보
     */
    public function getGoodsOptionInfo($sno = null, $goodsField = null, $arrBind = null, $dataArray = false)
    {
        if (is_null($arrBind)) {
            // $arrBind = array();
        }
        if ($sno) {
            if ($this->db->strWhere) {
                $this->db->strWhere = " go.sno = ? AND " . $this->db->strWhere;
            } else {
                $this->db->strWhere = " go.sno = ?";
            }
            $this->db->bind_param_push($arrBind, 'i', $sno);
        }
        if ($goodsField) {
            if ($this->db->strField) {
                $this->db->strField = $goodsField . ', ' . $this->db->strField;
            } else {
                $this->db->strField = $goodsField;
            }
        }
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_GOODS_OPTION . ' go ' . implode(' ', $query);

        $getData = $this->db->query_fetch($strSQL, $arrBind);

        if (count($getData) == 1 && $dataArray === false) {
            return gd_htmlspecialchars_stripslashes($getData[0]);
        }

        return gd_htmlspecialchars_stripslashes($getData);
    }

    /**
     * 상품의 브랜드 연결 정보 출력
     *
     * @param string $goodsNo 상품 번호
     *
     * @return array 해당 상품에 연결된 브랜드 정보
     */
    public function getGoodsLinkBrand($goodsNo)
    {
        $arrField = DBTableField::setTableField('tableGoodsLinkBrand', null, 'goodsNo');
        $strSQL = "SELECT sno, " . implode(', ', $arrField) . " FROM " . DB_GOODS_LINK_BRAND . " WHERE goodsNo = ? ORDER BY sno ASC";
        $arrBind = ['i', $goodsNo];
        $getData = $this->db->query_fetch($strSQL, $arrBind);
        if (count($getData) > 0) {
            return gd_htmlspecialchars_stripslashes($getData);
        } else {
            return false;
        }
    }

    /**
     * 상품의 카테고리 연결 정보 출력
     *
     * @param string $goodsNo 상품 번호
     *
     * @return array 해당 상품에 연결된 카테고리 정보
     */
    public function getGoodsLinkCategory($goodsNo)
    {
        $arrField = DBTableField::setTableField('tableGoodsLinkCategory', null, 'goodsNo');
        $strSQL = "SELECT sno, " . implode(', ', $arrField) . " FROM " . DB_GOODS_LINK_CATEGORY . " WHERE goodsNo = ? ORDER BY sno ASC";
        $arrBind = ['i', $goodsNo];
        $getData = $this->db->query_fetch($strSQL, $arrBind);
        if (count($getData) > 0) {
            return gd_htmlspecialchars_stripslashes($getData);
        } else {
            return false;
        }
    }

    /**
     * 상품의 추가 정보 출력
     *
     * @param string $goodsNo 상품 번호
     *
     * @return array 해당 상품의 추가 정보
     */
    public function getGoodsAddInfo($goodsNo)
    {
        $arrField = DBTableField::setTableField('tableGoodsAddInfo', null, 'goodsNo');
        $strSQL = "SELECT sno, " . implode(', ', $arrField) . " FROM " . DB_GOODS_ADD_INFO . " WHERE goodsNo = ? ORDER BY sno ASC";
        $arrBind = ['i', $goodsNo];
        $getData = $this->db->query_fetch($strSQL, $arrBind);
        if (count($getData) > 0) {
            return gd_htmlspecialchars_stripslashes($getData);
        } else {
            return false;
        }
    }

    /**
     * 상품의 옵션 정보 출력
     *
     * @param string $goodsNo 상품 번호
     *
     * @return array 해당 상품의 옵션 정보
     */
    public function getGoodsOption($goodsNo, $goodsData = null)
    {
        $arrField = DBTableField::setTableField('tableGoodsOption', null, 'goodsNo');
        $strSQL = "SELECT sno, " . implode(', ', $arrField) . " FROM " . DB_GOODS_OPTION . " WHERE goodsNo = ? ORDER BY optionNo ASC, sno ASC";
        $arrBind = ['i', $goodsNo];
        $getData = $this->db->query_fetch($strSQL, $arrBind);

        if (gd_isset($getData) === null) {
            return false;
        }
        foreach ($arrField as $key => $val) {
            if (substr($val, 0, -1) == 'optionValue') {
                $optVal[substr($val, -1)] = $val;
            }
        }

        foreach ($getData as $key => $val) {
            //고정가 생성으로 상품가격 다시 설정
            //$getData[$key]['goodsPrice'] = $val['optionPrice'] + $goodsData['goodsPrice'];
            //$getData[$key]['fixedPrice'] =$val['optionPrice'] + $goodsData['fixedPrice'];
            //$getData[$key]['costPrice'] = $val['optionPrice'] + $goodsData['costPrice'];

            foreach ($optVal as $oKey => $oVal) {
                $optKey = 'optVal' . $oKey;
                $getData[$optKey][] = $getData[$key][$oVal];
            }
        }


        if (count($getData) > 0) {
            for ($i = 1; $i <= count($optVal); $i++) {
                $optKey = 'optVal' . $i;
                $arrData = array_unique($getData[$optKey]);
                $getData['optVal'][$i] = ArrayUtils::removeEmpty($arrData);
                unset($getData[$optKey]);
            }

            return gd_htmlspecialchars_stripslashes($getData);
        }
    }

    /**
     * 상품의 옵션 정보 출력 - 리스트용
     *
     * @param string $goodsNo 상품 번호
     *
     * @return array 해당 상품의 옵션 정보
     */
    public function getGoodsOptionValue($goodsNo)
    {
        $arrField = DBTableField::setTableField('tableGoodsOption', null, ['goodsNo', 'optionNo']);
        $strSQL = "SELECT " . implode(', ', $arrField) . " FROM " . DB_GOODS_OPTION . " WHERE goodsNo = ? ORDER BY optionNo ASC, sno ASC";
        $arrBind = ['i', $goodsNo];
        $getData = $this->db->query_fetch($strSQL, $arrBind);

        if (gd_isset($getData) === null) {
            return false;
        }
        foreach ($arrField as $key => $val) {
            if (substr($val, 0, -1) == 'optionValue') {
                $optVal[substr($val, -1)] = $val;
            }
        }

        foreach ($getData as $key => &$val) {
            $optionValue = [];
            foreach ($optVal as $oVal) {
                $optionValue[] = $val[$oVal];
                unset($val[$oVal]);
            }
            $optionValue = ArrayUtils::removeEmpty($optionValue);
            $optionValue = implode(STR_DIVISION, $optionValue);
            $val['optionValue'] = $optionValue;
        }

        if (count($getData) > 0) {
            return gd_htmlspecialchars_stripslashes($getData);
        }
    }

    /**
     * 상품 이미지 정보 출력
     *
     * @param string $goodsNo 상품 번호
     *
     * @return array 해당 상품 이미지 정보
     */
    public function getGoodsOptionIcon($goodsNo)
    {
        $arrField = DBTableField::setTableField('tableGoodsOptionIcon', null, 'goodsNo');
        $strSQL = "SELECT sno, " . implode(', ', $arrField) . " FROM " . DB_GOODS_OPTION_ICON . " WHERE goodsNo = ? ORDER BY optionNo ASC";
        $arrBind = ['i', $goodsNo];
        $getData = $this->db->query_fetch($strSQL, $arrBind);
        if (count($getData) > 0) {
            return gd_htmlspecialchars_stripslashes($getData);
        } else {
            return false;
        }
    }

    /**
     * 상품의 텍스트 옵션 정보 출력
     *
     * @param string $goodsNo 상품 번호
     *
     * @return array 해당 상품의 텍스트 옵션 정보
     */
    public function getGoodsOptionText($goodsNo)
    {
        $arrField = DBTableField::setTableField('tableGoodsOptionText', null, 'goodsNo');
        $strSQL = "SELECT sno, " . implode(', ', $arrField) . " FROM " . DB_GOODS_OPTION_TEXT . " WHERE goodsNo = ? ORDER BY sno ASC";
        $arrBind = ['i', $goodsNo];
        $getData = $this->db->query_fetch($strSQL, $arrBind);
        if (count($getData) > 0) {
            return gd_htmlspecialchars_stripslashes($getData);
        } else {
            return false;
        }
    }

    /**
     * 분리형 옵션의 출력 정보
     *
     * @param string $goodsNo   상품 번호
     * @param string $optionVal 옵션 값
     * @param string $optionKey 옵션 키
     * @param string $mileageFl 마일리지 설정
     *
     * @return array 해당 상품의 옵션 정보
     */
    public function getGoodsOptionSelect($goodsNo, $optionVal, $optionKey, $mileageFl)
    {
        // 상품 옵션 where 문
        $arrWhere[] = 'optionViewFl = "y"';
        $arrWhere[] = 'goodsNo = ?';
        $this->db->bind_param_push($arrBind, 'i', $goodsNo);

        // 옵션 값에 따른 where 문
        $optionVal = ArrayUtils::removeEmpty($optionVal);
        if (is_array($optionVal) === false) {
            $optionVal = [$optionVal];
        }
        foreach ($optionVal as $key => $val) {
            // 상품 옵션
            $fieldNm = 'BINARY(optionValue' . ($key + 1).')';
            $arrWhere[] = $fieldNm . ' = ?';
            $this->db->bind_param_push($arrBind, 's', $val);
        }

        // 필드
        $arrField = DBTableField::setTableField('tableGoodsOption', null, 'goodsNo');

        $this->db->strField = implode(', ', $arrField);
        $this->db->strWhere = implode(' AND ', $arrWhere);

        // 상품 옵션 정보
        $query = $this->db->query_complete();
        $strSQL = 'SELECT sno, ' . array_shift($query) . ' FROM ' . DB_GOODS_OPTION . ' ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);
        unset($arrBind);

        $setData['cnt'] = $this->db->num_rows();

        if ($setData['cnt'] == 0) {
            return false;
        } else {
            // 상품 옵션 설정
            $fieldNm = 'optionValue' . ($optionKey + 2);
            $setData['nextOption'] = [];
            //타임세일 할인율 옵션가(분리형) 적용
            $timeSale = \App::load('\\Component\\Promotion\\TimeSale');
            $timeSaleInfo = $timeSale->getGoodsTimeSale($goodsNo);
            foreach ($getData as $key => $val) {
                $setData['nextOption'][] = gd_htmlspecialchars($val[$fieldNm]);
                $setData['stockCnt'][] = $val['stockCnt'];
                $setData['optionSellFl'][] = $val['optionSellFl'];
                $setData['optionDeliveryFl'][] = $val['optionDeliveryFl'];
                $setData['optionViewFl'][] = $val['optionViewFl'];
                $setData['optionSellCode'][] = $val['optionSellCode'];
                $setData['optionDeliveryCode'][] = $val['optionDeliveryCode'];
                $setData['sellStopStock'][] = $val['sellStopStock'];

                if($timeSaleInfo) {
                    $setData['optionPrice'][] = gd_number_figure($val['optionPrice'] - (($timeSaleInfo['benefit'] / 100) * $val['optionPrice']), $this->trunc['unitPrecision'], $this->trunc['unitRound']);
                } else {
                    $setData['optionPrice'][] = $val['optionPrice'];
                }
            }

            $setData['nextOption'] = ArrayUtils::removeEmpty($setData['nextOption']);
            if (empty($setData['nextOption']) === false) {
                // 마지막 옵션은 옵션명 중복되지 않으며, 상위 옵션은 옵션명 중복되어 유니크 처리
                $uniqueNextOption = array_unique($setData['nextOption']);
                if (count($setData['nextOption']) != count($uniqueNextOption)) { // 데이터 유니크 처리
                    $flipNextOption = array_flip($uniqueNextOption);
                    $mapKeys = array_map(function ($name) use ($flipNextOption) { return $flipNextOption[$name]; }, $setData['nextOption']);

                    // 유니크 옵션만 추출
                    $setData['nextOption'] = $uniqueNextOption;
                    for ($i = 0; $i < $setData['cnt']; $i++) {
                        if ($i != $mapKeys[$i]) {
                            // 재고량 : 합산
                            $setData['stockCnt'][ $mapKeys[$i] ] += $setData['stockCnt'][$i];
                            unset($setData['stockCnt'][$i]);

                            // 옵션품절상태 및 옵션품절상태코드 : 옵션 1개라도 정상이면 기준 옵션에 정상 대입
                            if ($setData['optionSellFl'][$i] == 'y') {
                                $setData['optionSellFl'][ $mapKeys[$i] ] = $setData['optionSellFl'][$i];
                                $setData['optionSellCode'][ $mapKeys[$i] ] = $setData['optionSellCode'][$i];
                            }
                            unset($setData['optionSellFl'][$i], $setData['optionSellCode'][$i]);

                            // 옵션배송상태 및 옵션배송상태코드 : 옵션 1개라도 정상이면 기준 옵션에 정상 대입
                            if ($setData['optionDeliveryFl'][$i] == 'normal') {
                                $setData['optionDeliveryFl'][ $mapKeys[$i] ] = $setData['optionDeliveryFl'][$i];
                                $setData['optionDeliveryCode'][ $mapKeys[$i] ] = $setData['optionDeliveryCode'][$i];
                            }
                            unset($setData['optionDeliveryFl'][$i], $setData['optionDeliveryCode'][$i]);

                            // 옵션노출상태 : 옵션 1개라도 노출함이면 기준 옵션에 노출함 대입
                            if ($setData['optionViewFl'][$i] == 'y') {
                                $setData['optionViewFl'][ $mapKeys[$i] ] = $setData['optionViewFl'][$i];
                            }
                            unset($setData['optionViewFl'][$i]);

                            // 기준 옵션 외 제거
                            unset($setData['optionPrice'][$i], $setData['sellStopStock'][$i]);
                        }
                    }
                }
                // 옵션배열 값을 재 정렬
                $setData['nextOption'] = array_values($setData['nextOption']);
                $setData['stockCnt'] = array_values($setData['stockCnt']);
                $setData['optionSellFl'] = array_values($setData['optionSellFl']);
                $setData['optionViewFl'] = array_values($setData['optionViewFl']);
                $setData['optionPrice'] = array_values($setData['optionPrice']);
                $setData['optionSellCode'] = array_values($setData['optionSellCode']);
                $setData['optionDeliveryCode'] = array_values($setData['optionDeliveryCode']);
                $setData['nextKey'] = ($optionKey + 1);

                // 분리형 옵션 Disabled 표기 여부 (src 구버전 레거시 조건)
                $setData['optionDivisionDisabledMark'] = 't';
            } else {
                // 통합 설정인 경우 마일리지 설정
                if ($mileageFl == 'c') {
                    // 상품 관련 마일리지
                    $mileage = gd_policy('mileage.goods');

                    if ($mileage['default']['use'] == 'mileage') {
                        $getData[0]['mileage'] = $mileage['default']['mileage'];
                    } else {
                        $getData[0]['mileage'] = gd_number_figure($getData[0]['optionPrice'] * ($mileage['default']['percent'] / 100), $mileage['default']['unit'], $mileage['default']['upDown']);
                    }
                }

                $setData['optionSno'] = $getData[0]['sno'] . INT_DIVISION . gd_money_format($getData[0]['optionPrice'],false) . INT_DIVISION . $getData[0]['mileage'] . INT_DIVISION . $getData[0]['stockCnt'];
            }
        }
        //판매 중지 수량에 도달하면 품절 처리
        //현재 추가 개발진행 중이므로 수정하지 마세요! 주석 처리된 내용을 수정할 경우 기능이 정상 작동하지 않거나, 추후 기능 배포시 오류의 원인이 될 수 있습니다.
        /*foreach ($setData['sellStopStock'] as $k => $v) {
            if($v >= $setData['stockCnt'][$k]) {
                $setData['stockCnt'][$k] = 0;
                $isSoldOut[$k] = true;
            }
        }*/

        //품절 상태 사유 처리
        $request = \App::getInstance('request');
        $mallSno = $request->get()->get('mallSno', 1);
        $code = \App::load('\\Component\\Code\\Code',$mallSno);
        $reason = $code->getGroupItems('05002');
        foreach ($setData['optionSellFl'] as $k => $v) {
            if($v == 't') {
                //코드 검색
                $setData['stockCnt'][$k] = 0;
                $setData['stockCodeValue'][$k] = $reason[$setData['optionSellCode'][$k]];
            }else if($v == 'n' || $isSoldOut[$k] == true){
                $setData['stockCodeValue']['n'] = $reason['05002002'];
            }

            //품절 여부 확인 하여 품절 표시 처리
            $goods = \App::load('\\Component\\Goods\\GoodsAdmin');
            $goodsData = $goods->getDataGoodsOption(gd_isset($goodsNo), '');
            if($goodsData['data']['stockFl'] == 'y' && $setData['stockCnt'][$k] < 1){
                $setData['stockCodeValue']['n'] = $reason['05002002'];
            }
        }
        if(empty($setData['stockCodeValue'])) $setData['stockCodeValue'][0] = '';

        //배송 상태 사유 처리
        //코드 검색
        $request = \App::getInstance('request');
        $mallSno = $request->get()->get('mallSno', 1);
        $code = \App::load('\\Component\\Code\\Code',$mallSno);
        $reason = $code->getGroupItems('05003');
        foreach ($setData['optionDeliveryFl'] as $k => $v) {
            if($v == 't') {
                $setData['deliveryCodeValue'][$k] = $reason[$setData['optionDeliveryCode'][$k]];
            }
        }
        if(empty($setData['deliveryCodeValue'])) $setData['deliveryCodeValue'][0] = '';

        return gd_htmlspecialchars_stripslashes($setData);
    }

    /**
     * 상품 이미지 정보 출력
     *
     * @param string $goodsNo      상품 번호
     * @param array  $arrImageKind 출력할 이미지 종류
     *
     * @return array 해당 상품 이미지 정보
     */
    public function getGoodsImage($goodsNo, $arrImageKind = null)
    {
        $strWhere = '';
        $bindQuery = $arrBind = null;
        $this->db->bind_param_push($arrBind,'i',$goodsNo);
        if (is_null($arrImageKind) === false) {
            if (is_array($arrImageKind)) {
                foreach($arrImageKind as $val) {
                    $bindQuery[] = '?';
                    $this->db->bind_param_push($arrBind,'s',$val);
                }
                $strWhere = ' AND imageKind IN (' . implode(',', $bindQuery) . ') ';
            } else {
                $strWhere = ' AND imageKind = ? ';
                $this->db->bind_param_push($arrBind,'s',$arrImageKind);
            }
        }

        $arrField = DBTableField::setTableField('tableGoodsImage', null, 'goodsNo');
        $strSQL = "SELECT sno, " . implode(', ', $arrField) . " FROM " . DB_GOODS_IMAGE . " WHERE goodsNo = ? " . $strWhere . " ORDER BY imageKind ASC, imageNo ASC";
        $getData = $this->db->slave()->query_fetch($strSQL, $arrBind);
        if (count($getData) > 0) {
            return gd_htmlspecialchars_stripslashes($getData);
        } else {
            return false;
        }
    }

    /**
     * 메인 상품 진열 및 테마 설정 정보 출력
     *
     * @param string $dataSno 테마 sno
     *
     * @return array 메인 상품 진열 및 테마 설정 정보
     */
    public function getDisplayThemeInfo($dataSno = null, $dataArray = false)
    {
        if (is_null($dataSno)) {
            $strWhere = '1';
            $arrBind = null;
        } else {
            $strWhere = 'sno = ?';
            $this->db->bind_param_push($arrBind, 'i', $dataSno);
        }
        $strSQL = 'SELECT dt.*,dtc.displayType FROM ' . DB_DISPLAY_THEME . ' as dt LEFT JOIN '.DB_DISPLAY_THEME_CONFIG.' AS dtc ON dtc.themeCd = dt.themeCd WHERE ' . $strWhere . ' ORDER BY dt.sno ASC';
        $getData = $this->db->query_fetch($strSQL, $arrBind);

        if (count($getData) == 1 && $dataArray === false) {
            $kindType = $getData[0]['kind'];
            $mobileThemeCd = $getData[0]['mobileThemeCd'];
        }
        else {
            $kindType = $getData['kind'];
            $mobileThemeCd = $getData['mobileThemeCd'];
        }

        //기획전 모바일의 경우 displayType 변경
        if($kindType === 'event' && Request::isMobile() === true && trim($mobileThemeCd) !== ''){
            $eventWhere = 'themeCd = ?';
            $this->db->bind_param_push($arrBind2, 's', $mobileThemeCd);
            $eventSQL = 'SELECT displayType FROM '.DB_DISPLAY_THEME_CONFIG.' WHERE ' . $eventWhere;
            $eventMobileThemeData = $this->db->query_fetch($eventSQL, $arrBind2)[0];

            if (count($getData) == 1 && $dataArray === false) {
                $getData[0]['displayType'] = $eventMobileThemeData['displayType'];
            }
            else {
                $getData['displayType'] = $eventMobileThemeData['displayType'];
            }
        }

        if (count($getData) == 1 && $dataArray === false) {
            return gd_htmlspecialchars_stripslashes($getData[0]);
        }
        if (count($getData) > 0) {
            return gd_htmlspecialchars_stripslashes($getData);
        } else {
            return false;
        }
    }

    public function getDisplayEventThemeInfo($whereArray, $orderby='')
    {
        if(trim($orderby) !== ''){
            $orderby = " ORDER BY " . $orderby;
        }
        $whereArray[] = "kind='event'";
        $strWhere = implode(" AND ", $whereArray);
        $strSQL = "SELECT * FROM " . DB_DISPLAY_THEME . " WHERE " . $strWhere . $orderby;
        $getData = $this->db->query_fetch($strSQL);

        return gd_htmlspecialchars_stripslashes($getData);
    }

    public function getDisplayOtherEventList()
    {
        //다른기획전 보기
        $eventConfig = gd_policy('promotion.event');
        if($eventConfig['otherEventUseFl'] === 'y'){
            if(trim($eventConfig['otherEventDefaultText']) === ''){
                $eventConfig['otherEventDefaultText'] = '다른 기획전 보러가기';
            }
            $getEventTempdata = $getEeventData_ed = $getEeventData_ing = $getEeventData_all = array();
            $nowDate = date("Y-m-d H:i:s");
            $nowDateMtime = strtotime($nowDate);
            if($eventConfig['otherEventSortType'] === 'hand'){
                //수동진열
                foreach($eventConfig['otherEventNo'] as $key => $eventSno){
                    $getEventTempdata[$key] = $this->getDisplayThemeInfo($eventSno);

                    //PC, MOBILE 여부
                    if(Request::isMobile()){
                        if($getEventTempdata[$key]['mobileFl'] !== 'y'){
                            continue;
                        }
                    }
                    else {
                        if($getEventTempdata[$key]['pcFl'] !== 'y'){
                            continue;
                        }
                    }

                    if((int)$getEventTempdata[$key]['sno'] > 0){
                        if ($nowDateMtime > strtotime($getEventTempdata[$key]['displayStartDate']) && $nowDateMtime < strtotime($getEventTempdata[$key]['displayEndDate'])) {
                            $getEeventData_ing[] = $getEventTempdata[$key];
                        }
                        else {
                            $getEeventData_ed[] = $getEventTempdata[$key];
                        }
                        $getEeventData_all[] = $getEventTempdata[$key];
                    }
                }

                if($eventConfig['otherEventDisplayFl'] === 'n') { //미진행 기획전 노출안함
                    $getEeventData = $getEeventData_ing;
                }
                else { //미진행 기획전 노출함
                    if($eventConfig['otherEventBottomFirstFl'] === 'y'){ //미진행 기획전 하단노출
                        $getEeventData = array_merge((array)$getEeventData_ing, (array)$getEeventData_ed);
                    }
                    else {
                        $getEeventData = $getEeventData_all;
                    }
                }
            }
            else {
                //자동진열
                if(count($eventConfig['otherEventExtraNo']) > 0){
                    $eventWhere[] = " sno not in (".implode(",", $eventConfig['otherEventExtraNo']).") ";
                }
                //PC, MOBILE 여부
                if(Request::isMobile()){
                    $eventWhere[] = " mobileFl = 'y' ";
                }
                else {
                    $eventWhere[] = " pcFl = 'y' ";
                }
                if($eventConfig['otherEventDisplayFl'] === 'n') { //미진행 기획전 노출안함
                    $eventWhere[] = " ('".$nowDate."' > displayStartDate && '".$nowDate."' < displayEndDate) ";
                    $getEeventData = $this->getDisplayEventThemeInfo($eventWhere, $eventConfig['otherEventSortTypeTa']);
                }
                else { //미진행 기획전 노출함
                    if($eventConfig['otherEventBottomFirstFl'] === 'y'){ //미진행 기획전을 하단에 노출할 경우
                        //진행중인 기획전
                        $eventWhere[] = " ('".$nowDate."' > displayStartDate && '".$nowDate."' < displayEndDate) ";
                        $getEeventData_ing = $this->getDisplayEventThemeInfo($eventWhere, $eventConfig['otherEventSortTypeTa']);

                        //미진행 기획전
                        array_pop($eventWhere);
                        $eventWhere[] = " ('".$nowDate."' < displayStartDate || '".$nowDate."' > displayEndDate) ";
                        $getEeventData_ed = $this->getDisplayEventThemeInfo($eventWhere, $eventConfig['otherEventSortTypeTa']);
                        $getEeventData = array_merge((array)$getEeventData_ing, (array)$getEeventData_ed);
                    }
                    else {
                        $getEeventData = $this->getDisplayEventThemeInfo($eventWhere, $eventConfig['otherEventSortTypeTa']);
                    }
                }
            }

            array_unshift($getEeventData, array('sno'=>'', 'themeNm' => $eventConfig['otherEventDefaultText']));
            unset($eventWhere, $getEventTempdata, $getEeventData_ed, $getEeventData_ing, $getEeventData_all);
        }
        else {
            $getEeventData = '';
        }

        return $getEeventData;
    }

    /**
     * 모바일샵 메인 상품 진열 정보 출력
     *
     * @param string $dataSno 테마 sno
     *
     * @return array 모바일샵 메인 상품 진열 정보
     */
    public function getDisplayThemeMobileInfo($dataSno = null, $dataArray = false)
    {
        $arrField = DBTableField::setTableField('tableDisplayThemeMobile');
        if (is_null($dataSno)) {
            $strWhere = '1';
            $arrBind = null;
        } else {
            $strWhere = 'sno = ?';
            $this->db->bind_param_push($arrBind, 'i', $dataSno);
        }
        $strSQL = 'SELECT sno, ' . implode(', ', $arrField) . ' FROM ' . DB_DISPLAY_THEME_MOBILE . ' WHERE ' . $strWhere . ' ORDER BY sno ASC';
        $getData = $this->db->query_fetch($strSQL, $arrBind);

        if (count($getData) == 1 && $dataArray === false) {
            return gd_htmlspecialchars_stripslashes($getData[0]);
        }
        if (count($getData) > 0) {
            return gd_htmlspecialchars_stripslashes($getData);
        } else {
            return false;
        }
    }

    /**
     * 레이어로 선택 추가된 상품 정보
     *
     * @param string $getData 상품 코드 정보
     *
     * @return array 상품 정보
     */
    public function getGoodsDataDisplay($getData, $sort = '')
    {
        if (empty($getData)) {
            return false;
        }

        $arrKindCd = explode(INT_DIVISION, $getData);
        $arrBind = [];
        foreach ($arrKindCd as $key => $val) {
            $this->db->bind_param_push($arrBind['bind'], 'i', $val);
            $arrBind['param'][] = '?';
            $arrSort[$val] = $key;
        }

        $this->db->strField = 'g.goodsNo, g.goodsNm, g.imageStorage, g.imagePath, gi.imageName,g.makerNm,g.goodsPrice,g.totalStock,s.companyNm as scmNm,g.stockFl,g.soldOutFl,g.regDt,g.goodsDisplayFl,g.goodsDisplayMobileFl,g.goodsSellFl,g.goodsSellMobileFl,g.goodsBenefitSetFl';
        $join[] = ' LEFT JOIN ' . DB_GOODS_IMAGE . ' gi ON g.goodsNo = gi.goodsNo AND gi.imageKind = \'list\' ';
        $join[] = 'INNER JOIN ' . DB_SCM_MANAGE . ' as s ON s.scmNo = g.scmNo ';
        $this->db->strJoin = implode('', $join);
        if ($sort) $this->db->strOrder = $sort;
        $this->db->strWhere = 'g.goodsNo IN (' . implode(',', $arrBind['param']) . ') AND g.delFl="n"';

        $arrResult = $this->getGoodsInfo(null, null, $arrBind['bind'], true);

        // 원 데이터를 기준으로 재정렬
        if (!$sort) $setData = ArrayUtils::resort($arrResult, $arrSort, 'goodsNo');
        else $setData = $arrResult;

        return $setData;
    }

    /**
     * 상품 코드를 카테고리 코드로 변경
     *
     * @param string $getData  상품 코드 정보
     * @param string $cateMode 카테고리 모드 (category, brand)
     *
     * @return array 카테고리 코드 정보
     */
    public function getGoodsNoToCateCd($getData, $cateMode = 'category')
    {
        if (empty($getData)) {
            return false;
        }

        if ($cateMode == 'category') {
            $cate = \App::load('\\Component\\Category\\Category');
            $dbTable = DB_GOODS_LINK_CATEGORY;
        } else {
            // @todo 브랜드 카테고리 클래스 분리 혹은 extends 필요
            $cate = \App::load('\\Component\\Category\\Category', $cateMode);
            $dbTable = DB_GOODS_LINK_BRAND;
        }

        $arrKindCd = explode(INT_DIVISION, $getData);
        $arrBind = [];
        foreach ($arrKindCd as $key => $val) {
            $this->db->bind_param_push($arrBind['bind'], 'i', $val);
            $arrBind['param'][] = '?';
            $arrSort[$val] = $key;
        }
        $this->db->strField = 'gl.cateCd';
        $this->db->strWhere = 'gl.goodsNo IN (' . implode(',', $arrBind['param']) . ') AND gl.cateLinkFl = \'y\'';
        $this->db->strGroup = 'gl.cateCd';

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . $dbTable . ' gl ' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $arrBind['bind']);

        // $data가 없을경우 10.04.17 수정.
        if (empty($data) === false) {
            foreach ($data as $key => $val) {
                $setData['cateCd'][] = $val['cateCd'];
                $setData['cateNm'][] = gd_htmlspecialchars_decode($cate->getCategoryPosition($val['cateCd']));
            }

            return $setData;
        }

        return null;
    }

    /**
     * 상품 정보 세팅
     *
     * @param string  $getData       상품정보
     * @param string  $imageType     이미지 타입
     * @param boolean $optionFl      옵션 출력 여부 - true or false (기본 false)
     * @param boolean $couponPriceFl 쿠폰가격 출력 여부 - true or false (기본 false)
     * @param integer $viewWidthSize 실제 출력할 이미지 사이즈 (기본 null)
     * @param array   $linkUrl 추적경로
     */
    protected function setGoodsListInfo(&$getData, $imageType, $optionFl = false, $couponPriceFl = false, $viewWidthSize = null, $viewName = null, $brandFl = false, $linkUrl = null)
    {
        $mallBySession = SESSION::get(SESSION_GLOBAL_MALL);

        // 이미지 타입에 따른 상품 이미지 사이즈
        if (empty($viewWidthSize) === true) {
            $imageSize = SkinUtils::getGoodsImageSize($imageType);
        } else {
            $imageSize['size1'] = $viewWidthSize;
        }

        // 세로사이즈고정 체크
        $imageConf = gd_policy('goods.image');
        if ($imageConf['imageType'] != 'fixed' || Request::isMobile()) {
            $imageSize['hsize1'] = '';
        }

        $strSQL = 'SELECT iconNm,iconImage,iconCd FROM ' . DB_MANAGE_GOODS_ICON .' WHERE iconUseFl = "y"';
        $tmpIcon = $this->db->slave()->query_fetch($strSQL);
        foreach($tmpIcon as $k => $v ) {
            $setIcon[$v['iconCd']]['iconImage'] = $v['iconImage'];
            $setIcon[$v['iconCd']]['iconNm'] = $v['iconNm'];
        }

        /* 이미지 설정 리스트이미지효과 사용시 여러이미지 한번에 가져옴*/
        if(gd_is_plus_shop(PLUSSHOP_CODE_LISTMOUSEOVER) === true) {
            $bindQuery = $arrBind = null;
            foreach(array_column($getData, 'goodsNo') as $val){
                $bindQuery[] = '?';
                $this->db->bind_param_push($arrBind,'i',$val);
            }
            $strImageSQL = 'SELECT goodsNo,imageName,imageKind FROM ' . DB_GOODS_IMAGE . ' g  WHERE imageNo= 0  AND goodsNo IN ('.implode(',',$bindQuery).')';

        } else {
            $bindQuery = $arrBind = null;
            $this->db->bind_param_push($arrBind,'s',$imageType);
            foreach(array_column($getData, 'goodsNo') as $val){
                $bindQuery[] = '?';
                $this->db->bind_param_push($arrBind,'i',$val);
            }
            $strImageSQL = 'SELECT goodsNo,imageName,imageKind FROM ' . DB_GOODS_IMAGE . ' g  WHERE imageKind = ?  AND goodsNo IN ('.implode(',',$bindQuery).')';
        }
        //        debug($strImageSQL);
        //        debug($arrBind,true);
        $tmpImageData = $this->db->slave()->query_fetch($strImageSQL,$arrBind);
        foreach($tmpImageData as $k => $v) {
            $imageData[$v['goodsNo']][$v['imageKind']] = stripslashes($v['imageName']);
        }
        unset($tmpImageData);

        if($brandFl) {
            $bindQuery = $arrBind = null;
            foreach(array_column($getData, 'brandCd') as $val){
                $bindQuery[] = '?';
                $this->db->bind_param_push($arrBind,'s',$val);
            }
            $strSQLGlobal = "SELECT cateNm,cateCd FROM ".DB_CATEGORY_BRAND."  WHERE cateCd IN (".implode(",",$bindQuery).")";
            $tmpData = $this->db->query_fetch($strSQLGlobal,$arrBind);
            $brandData = array_combine(array_column($tmpData, 'cateCd'), $tmpData);
        }

        if($mallBySession) {
            $arrFieldGoodsGlobal = DBTableField::setTableField('tableGoodsGlobal',null,['mallSno']);
            $bindQuery = $arrBind = null;
            foreach(array_column($getData, 'goodsNo') as $val){
                $bindQuery[] = '?';
                $this->db->bind_param_push($arrBind,'i',$val);
            }
            $strSQLGlobal = "SELECT gg." . implode(', gg.', $arrFieldGoodsGlobal) . " FROM ".DB_GOODS_GLOBAL." as gg WHERE gg.goodsNo IN (".implode(",",$bindQuery).") AND gg.mallSno = ".$mallBySession['sno'];
            $tmpData = $this->db->query_fetch($strSQLGlobal,$arrBind);
            $globalData = array_combine(array_column($tmpData, 'goodsNo'), $tmpData);

            if($brandFl) {
                //브랜드정보
                $bindQuery = $arrBind = null;
                foreach(array_column($getData, 'brandCd') as $val){
                    $bindQuery[] = '?';
                    $this->db->bind_param_push($arrBind,'s',$val);
                }
                $strSQLGlobal = "SELECT cateNm,cateCd FROM ".DB_CATEGORY_BRAND_GLOBAL."  WHERE cateCd IN (".implode(",",$bindQuery).") AND mallSno = ".$mallBySession['sno'];
                $tmpData = $this->db->query_fetch($strSQLGlobal, $arrBind);
                $brandGlobalData = array_combine(array_column($tmpData, 'cateCd'), $tmpData);
            }
        }

        // 마일리지 처리
        $mileage = gd_mileage_give_info();

        // 쿠폰 설정값 정보
        $couponConfig = gd_policy('coupon.config');

        //품절상품 설정
        if(Request::isMobile()) {
            $soldoutDisplay = gd_policy('soldout.mobile');
        } else {
            $soldoutDisplay = gd_policy('soldout.pc');
        }

        //쿠폰 관련
        if($couponConfig['couponUseType'] == 'y') {
            $coupon = \App::load('\\Component\\Coupon\\Coupon');
            $couponArrData = $coupon->getGoodsCouponDownListAll();
        }

        //상품 가격 노출 관련
        $goodsPriceDisplayFl = gd_policy('goods.display')['priceFl'];

        $GoodsBenefit = \App::load('\\Component\\Goods\\GoodsBenefit');

        // 아이콘 출력 및 옵션 출력 여부
        foreach ($getData as $key => &$val) {

            // 아이콘 테이블 분리로 인한 추가
            $tmpGoodsIcon = [];
            $iconList = $this->getGoodsDetailIcon($val['goodsNo']);
            foreach ($iconList as $iconKey => $iconVal) {
                if ($iconVal['iconKind'] == 'pe') {
                    if (empty($iconVal['goodsIconStartYmd']) === false && empty($iconVal['goodsIconEndYmd']) === false && empty($iconVal['goodsIconCd']) === false && strtotime($iconVal['goodsIconStartYmd']) <= time() && strtotime($iconVal['goodsIconEndYmd']) >= time()) {
                        $tmpGoodsIcon[] = $iconVal['goodsIconCd'];
                    }
                }

                if ($iconVal['iconKind'] == 'un') {
                    $tmpGoodsIcon[] = $iconVal['goodsIconCd'];
                }
            }

            $val['goodsIconCd'] = implode(INT_DIVISION,$tmpGoodsIcon);

            //상품혜택 적용
            $val = $GoodsBenefit->goodsDataFrontConvert($val);

            $setMileageGiveFl = $mileage['give']['giveFl'];
            $setCouponUseType = $couponConfig['couponUseType'];
            $exceptBenefit = explode(STR_DIVISION, $val['exceptBenefit']);
            $exceptBenefitGroupInfo = explode(INT_DIVISION, $val['exceptBenefitGroupInfo']);

            // 제외 혜택 대상 여부
            $exceptBenefitFl = false;
            if ($val['exceptBenefitGroup'] == 'all' || ($val['exceptBenefitGroup'] == 'group' && in_array(Session::get('member.groupSno'), $exceptBenefitGroupInfo) === true)) {
                $exceptBenefitFl = true;
            }

            $val['imageName'] = $imageData[$val['goodsNo']][$imageType];
            if($brandFl && $brandData) {
                $val['brandNm'] = $brandData[$val['brandCd']]['cateNm'];
            }

            // 상품 url 추가
            $val['goodsUrl'] = '../goods/goods_view.php?goodsNo=' . $val['goodsNo'];

            if($mallBySession) {
                if($globalData[$val['goodsNo']]) {
                    $val = array_replace_recursive($val, array_filter(array_map('trim',$globalData[$val['goodsNo']])));
                }

                if($brandFl && $brandGlobalData[$val['brandCd']]) {
                    $val['brandNm'] = $brandGlobalData[$val['brandCd']]['cateNm'];
                }
            }

            // 상품 url 추가
            if (gd_isset($viewName) && $viewName == 'main') {
                $linkUrlVal = htmlentities(urlencode($linkUrl['mainThemeSno'] . STR_DIVISION . $linkUrl['mainThemeNm'] . STR_DIVISION . $linkUrl['mainThemeDevice']));
                $val['goodsUrl'] = '../goods/goods_view.php?goodsNo=' . $val['goodsNo'] . '&mtn=' . $linkUrlVal;
            } else {
                $val['goodsUrl'] = '../goods/goods_view.php?goodsNo=' . $val['goodsNo'];
            }

            $val['oriGoodsPrice'] = $val['goodsPrice'];

            //구매불가 대체 문구 관련
            if($val['goodsPermissionPriceStringFl'] =='y' && $val['goodsPermission'] !='all' && (($val['goodsPermission'] =='member'  && gd_is_login() === false) || ($val['goodsPermission'] =='group'  && !in_array(Session::get('member.groupSno'),explode(INT_DIVISION,$val['goodsPermissionGroup']))))) {
                $val['goodsPriceString'] = $val['goodsPermissionPriceString'];
            }

            /* 타임 세일 관련 */
            $val['timeSaleFl'] = false;
            $val['cssTimeSaleIcon'] = '';
            if (gd_is_plus_shop(PLUSSHOP_CODE_TIMESALE) === true) {
                $strScmSQL = 'SELECT ts.mileageFl as timeSaleMileageFl,ts.couponFl as timeSaleCouponFl,ts.benefit as timeSaleBenefit,ts.sno as timeSaleSno,ts.goodsPriceViewFl as timeSaleGoodsPriceViewFl, ts.endDt as timeSaleEndDt, ts.leftTimeDisplayType, ts.pcDisplayFl as timeSalePC, ts.mobileDisplayFl as timeSaleMobile FROM ' . DB_TIME_SALE .' as ts WHERE FIND_IN_SET('.$val['goodsNo'].', REPLACE(ts.goodsNo,"'.INT_DIVISION.'",",")) AND UNIX_TIMESTAMP(ts.startDt) < UNIX_TIMESTAMP() AND  UNIX_TIMESTAMP(ts.endDt) > UNIX_TIMESTAMP() ';
                $tmpScmData = $this->db->query_fetch($strScmSQL,null,false);

                if($tmpScmData) {
                    //타임세일 노출 여부 (디바이스에 따라)
                    $tmpTimeSaleFl = false;
                    if ($tmpScmData['timeSalePC'] === 'y' && !Request::isMobile()) {
                        $tmpTimeSaleFl = true;
                    }
                    if ($tmpScmData['timeSaleMobile'] === 'y' && Request::isMobile()) {
                        $tmpTimeSaleFl = true;
                    }

                    if ($tmpTimeSaleFl === true) {
                        //PC or Mobile 노출에 따라 타임세일 출력.
                        $val = $val + $tmpScmData;
                        if($val['timeSaleMileageFl'] =='n') $setMileageGiveFl = "n";
                        if($val['timeSaleCouponFl'] =='n') $setCouponUseType  = "n";
                        $val['timeSaleFl'] = true;

                        if($val['goodsPrice'] > 0 ) $val['goodsPrice'] =  gd_number_figure($val['goodsPrice'] - (($val['timeSaleBenefit'] / 100) * $val['goodsPrice']), $this->trunc['unitPrecision'], $this->trunc['unitRound']);

                        //타임 세일 남은 기간 노출 (분 단위)
                        //노출 설정이 되어있는 경우에만 노출 (PC/MOBILE)
                        $val['cssTimeSaleIcon'] = 'time_sale_cost';
                        $tmpLeftTimeDisplayFl = false;
                        if (strpos($val['leftTimeDisplayType'], 'PC') !== FALSE && !Request::isMobile()) {
                            $tmpLeftTimeDisplayFl = true;
                        }
                        if (strpos($val['leftTimeDisplayType'], 'MOBILE') !== FALSE && Request::isMobile()) {
                            $tmpLeftTimeDisplayFl = true;
                        }
                        if ($tmpLeftTimeDisplayFl === true) {
                            $tmpBeforeDay = DateTimeUtils::dateFormat('Y-m-d G:i:s', 'now');
                            $tmpAfterDay = $val['timeSaleEndDt'];
                            $tmpTimeSaleLeftTime = DateTimeUtils::intervalDay($tmpBeforeDay, $tmpAfterDay, 'min');
                            $val['timeSaleLeftTime'] = $tmpTimeSaleLeftTime;

                            //60분 이상일 경우 일 or 시 단위로 표현.
                            $tmpLeftTimeByHour = round($tmpTimeSaleLeftTime / 60);
                            if ($tmpTimeSaleLeftTime >= (60 * 24) || $tmpLeftTimeByHour == 24) { //24시간은 1일료 표현
                                $tmpLeftTime = round($tmpTimeSaleLeftTime / (60 * 24));
                                $val['timeSaleLeftTimeTxt'] = __('%s일 남음', $tmpLeftTime);
                            } else if ($tmpTimeSaleLeftTime >= 60) {
                                $tmpLeftTime = $tmpLeftTimeByHour;
                                $val['timeSaleLeftTimeTxt'] = __('%s시간 남음', $tmpLeftTime);
                            } else {
                                $tmpLeftTime = ($tmpTimeSaleLeftTime == 0) ? 1 : $tmpTimeSaleLeftTime; //0분은 1분으로 표현
                                $val['timeSaleLeftTimeTxt'] = __('%s분 남음', $tmpLeftTime);
                            }

                            //타임세일 남은 기간 문구 노출할 경우, 기존 아이콘 노출 안하도록 css 변경.
                            $val['cssTimeSaleIcon'] = 'time_sale_cost_r';
                        }
                    }
                }
                unset($tmpScmData);
                unset($strScmSQL);
            }

            // 아이콘 테이블 분리로 인한 추가
            unset($tmpGoodsIcon);
            $tmpGoodsIcon = explode(INT_DIVISION, $val['goodsIconCd']);
            if($tmpGoodsIcon) {
                $tmpGoodsIcon = ArrayUtils::removeEmpty($tmpGoodsIcon); // 빈 배열 정리

                foreach($tmpGoodsIcon  as $iKey => $iVal) {
                    if (isset($setIcon[$iVal])) {
                        $icon = UserFilePath::icon('goods_icon', $setIcon[$iVal]['iconImage']);
                        if ($icon->isFile()) {
                            $val['goodsIcon'] .= gd_html_image($icon->www(), $setIcon[$iVal]['iconNm']) . ' ';
                        }
                    }
                }
            }

            $val['goodsBenefitIcon'] = '';
            if(empty($val['goodsBenefitIconCd']) === false) {
                $tmpGoodsBenefitIcon = explode(INT_DIVISION, $val['goodsBenefitIconCd']);
                foreach($tmpGoodsBenefitIcon  as $iKey => $iVal) {
                    if (isset($setIcon[$iVal])) {
                        $icon = UserFilePath::icon('goods_icon', $setIcon[$iVal]['iconImage']);
                        if ($icon->isFile()) {
                            $val['goodsBenefitIcon'] .= gd_html_image($icon->www(), $setIcon[$iVal]['iconNm']) . ' ';
                        }
                    }
                }
            }
            //상품혜택 아이콘 가장먼저 노출
            $val['goodsIcon'] = $val['goodsBenefitIcon'].$val['goodsIcon'];

            // 옵션 출력 및 옵션의 마일리지 처리
            if ($optionFl === true && empty($val['optionName']) === false) {
                $val['optionValue'] = $this->getGoodsOptionValue($val['goodsNo']);
            }

            if($setMileageGiveFl =='y' ) {
                $val['goodsMileageFl'] = 'y';
                //상품 마일리지
                if ($val['mileageFl'] == 'c') {
                    // 마일리지 지급 여부
                    $mileageGiveFl = true;
                    if ($val['mileageGroup'] == 'group') { //마일리지 지급대상(특정회원등급)
                        $mileageGroupInfoData = explode(INT_DIVISION, $val['mileageGroupInfo']);

                        $mileageGiveFl = in_array(Session::get('member.groupSno'), $mileageGroupInfoData);
                    }

                    if ($mileageGiveFl === true) {
                        if ($mileage['give']['giveType'] == 'priceUnit') { // 금액 단위별
                            $mileagePrice = floor($val['goodsPrice'] / $mileage['give']['goodsPriceUnit']);
                            $val['mileageBasicGoods'] = gd_number_figure($mileagePrice * $mileage['give']['goodsMileage'], $mileage['trunc']['unitPrecision'], $mileage['trunc']['unitRound']);
                        } else if ($mileage['give']['giveType'] == 'cntUnit') { // 수량 단위별 (추가상품수량은 제외)
                            $val['mileageBasicGoods'] = gd_number_figure($mileage['give']['cntMileage'], $mileage['trunc']['unitPrecision'], $mileage['trunc']['unitRound']);
                        } else { // 구매금액의 %
                            $mileagePercent = $mileage['give']['goods'] / 100;
                            $val['mileageBasicGoods'] = gd_number_figure($val['goodsPrice'] * $mileagePercent, $mileage['trunc']['unitPrecision'], $mileage['trunc']['unitRound']);
                        }
                    }
                    // 개별 설정인 경우 마일리지 설정
                } else if ($val['mileageFl'] == 'g') {
                    if ($val['mileageGroup'] == 'group') { //마일리지 지급대상(특정회원등급)
                        $mileageGroupMemberInfoData = json_decode($val['mileageGroupMemberInfo'], true);
                        $mileageKey = array_flip($mileageGroupMemberInfoData['groupSno'])[Session::get('member.groupSno')];
                        if ($mileageKey >= 0) {
                            $mileagePercent = $mileageGroupMemberInfoData['mileageGoods'][$mileageKey] / 100;
                            if ($val['mileageGoodsUnit'] === 'percent') {
                                // 상품 마일리지
                                $val['mileageBasicGoods'] = gd_number_figure($val['goodsPrice'] * $mileagePercent, $mileage['trunc']['unitPrecision'], $mileage['trunc']['unitRound']);
                            } else {
                                // 상품 마일리지 (정액인 경우 해당 설정된 금액으로)
                                $val['mileageBasicGoods'] = $mileageGroupMemberInfoData['mileageGoods'][$mileageKey];
                            }
                        }
                    } else {
                        $mileagePercent = $val['mileageGoods'] / 100;

                        // 상품 기본 마일리지 정보
                        if ($val['mileageGoodsUnit'] === 'percent') {
                            $val['mileageBasicGoods'] = gd_number_figure($val['goodsPrice'] * $mileagePercent, $mileage['trunc']['unitPrecision'], $mileage['trunc']['unitRound']);
                        } else {
                            // 정액인 경우 해당 설정된 금액으로
                            $val['mileageBasicGoods'] = gd_number_figure($val['mileageGoods'], $mileage['trunc']['unitPrecision'], $mileage['trunc']['unitRound']);
                        }
                    }

                }

                // 회원 그룹별 추가 마일리지
                if ($this->_memInfo['mileageLine'] <= $val['goodsPrice']) {
                    if (in_array('mileage', $exceptBenefit) === true && $exceptBenefitFl === true) {} else {
                        if ($this->_memInfo['mileageType'] === 'percent') {
                            $memberMileagePercent = $this->_memInfo['mileagePercent'] / 100;
                            $val['mileageBasicMember'] = gd_number_figure($val['goodsPrice'] * $memberMileagePercent, $mileage['trunc']['unitPrecision'], $mileage['trunc']['unitRound']);
                        } else {
                            $val['mileageBasicMember'] = $this->_memInfo['mileagePrice'];
                        }
                    }
                }

                $val['mileageBasic'] = $val['mileageBasicGoods'] + $val['mileageBasicMember'];

            } else {
                $val['goodsMileageFl'] = 'n';

            }

            // 쿠폰가 회원만 노출
            if ($couponConfig['couponDisplayType'] == 'member') {
                if (gd_check_login()) {
                    $couponPriceYN = true;
                } else {
                    $couponPriceYN = false;
                }
            } else {
                $couponPriceYN = true;
            }

            // 혜택제외 체크 (쿠폰)
            $exceptBenefit = explode(STR_DIVISION, $val['exceptBenefit']);
            $exceptBenefitGroupInfo = explode(INT_DIVISION, $val['exceptBenefitGroupInfo']);
            if (in_array('coupon', $exceptBenefit) === true && ($val['exceptBenefitGroup'] == 'all' || ($val['exceptBenefitGroup'] == 'group' && in_array(Session::get('member.groupSno'), $exceptBenefitGroupInfo) === true))) {
                $couponPriceYN = false;
            }

            // 쿠폰 할인 금액
            if ($setCouponUseType == 'y' && $couponPriceYN && $val['goodsPrice'] > 0 && empty($val['goodsPriceString']) === true) {
                // 쿠폰검색에 필요한 전체 카테고리 체크
                $tmpCateArr = $this->getGoodsLinkCategory($val['goodsNo']);
                if (is_array($tmpCateArr)) {
                    $val['cateCdArr'] = array_column($tmpCateArr, 'cateCd');
                }
                unset($tmpCateArr);

                //쿠폰가 계산
                $couponSalePrice = $coupon->getGoodsCouponDownListPrice($val,$couponArrData);
                $val['couponDcPrice'] = $couponSalePrice;
                if ($couponSalePrice) {
                    $val['couponPrice'] = $val['goodsPrice'] - $couponSalePrice;
                    if ($val['couponPrice'] < 0) {
                        $val['couponPrice'] = 0;
                    }
                }
            }

            // 상품 이미지 처리
            if ($val['onlyAdultFl'] == 'y' && gd_check_adult() === false && $val['onlyAdultImageFl'] =='n') {
                if (Request::isMobile()) {
                    $val['goodsImageSrc'] = "/data/icon/goods_icon/only_adult_mobile.png";
                } else {
                    $val['goodsImageSrc'] = "/data/icon/goods_icon/only_adult_pc.png";
                }

                $val['goodsImage'] = SkinUtils::makeImageTag($val['goodsImageSrc'], $imageSize['size1']);
            } else {
                $val['goodsImage'] = gd_html_preview_image($val['imageName'], $val['imagePath'], $val['imageStorage'], $imageSize['size1'], 'goods', $val['goodsNm'], null, false, true, $imageSize['hsize1'],null,$this->goodsImageLazyFl);
                $val['goodsImageSrc'] = SkinUtils::imageViewStorageConfig($val['imageName'], $val['imagePath'], $val['imageStorage'], $imageSize['size1'], 'goods')[0];
            }

            // 상품명
            if (gd_isset($viewName) && $viewName == 'main') {
                $val['goodsNm'] = $this->getGoodsName($val['goodsNmMain'], $val['goodsNm'], $val['goodsNmFl']);
            } else {
                $val['goodsNm'] = $this->getGoodsName($val['goodsNmList'], $val['goodsNm'], $val['goodsNmFl']);
            }

            //기본적으로 가격 노출함
            $val['goodsPriceDisplayFl'] = 'y';

            // 가격 대체 문구가 있는 경우 주문금지
            if (empty($val['goodsPriceString']) === false) {
                $val['orderPossible'] = 'n';
                if($goodsPriceDisplayFl =='n') $val['goodsPriceDisplayFl'] = 'n';
            }

            // 구매 가능여부 체크
            if ($val['soldOut'] == 'y') {
                $val['orderPossible'] = 'n';
                if($goodsPriceDisplayFl =='n' && $soldoutDisplay['soldout_price'] !='price') $val['goodsPriceDisplayFl'] = 'n';
            }

            // 정렬을 위한 필드가 있는 경우 삭제처리
            if (isset($val['sort'])) {
                unset($val['sort']);
            }

            // 재고량 체크
            $val['stockCnt'] = $this->getOptionStock($val['goodsNo'], null, $val['stockFl'], $val['soldOutFl']);

            //할인가 기본 세팅
            $val['goodsDcPrice'] = $this->getGoodsDcPrice($val);

            // 상품혜택관리 치환코드 생성
            $val = $GoodsBenefit->goodsDataFrontReplaceCode($val, 'goodsList');

            // 상품 대표색상 치환코드 추가
            $goodsColorList = $this->getGoodsColorList(true);
            $goodsColorWidth = $imageSize['size1'] - 10;
            $goodsColor = (Request::isMobile()) ? "<div class='color_chip'>" : "<div class='color' style='width: ".$goodsColorWidth."px'>";
            if($val['goodsColor']) $val['goodsColor'] = explode(STR_DIVISION, $val['goodsColor']);

            if(is_array($val['goodsColor'])) {
                foreach(array_unique($val['goodsColor']) as $k => $v) {
                    if (!in_array($v,$goodsColorList) ) {
                        continue;
                    }
                    $goodsColorData = array_flip($goodsColorList)[$v];
                    $goodsColor .= ($v == 'FFFFFF') ? "<div style='background-color:#{$v};' title='{$goodsColorData}'></div>" : "<div style='background-color:#{$v}; border-color:#{$v};' title='{$goodsColorData}'></div>";
                }
                $goodsColor .= "</div>";
                unset($val['goodsColor']);
                $val['goodsColor'] = $goodsColor;
            }

            if (in_array('goodsDiscount', $this->themeConfig['displayField']) === true && empty($val['goodsPriceString']) === true) {
                if (empty($this->themeConfig['goodsDiscount']) === false) {
                    if (in_array('goods', $this->themeConfig['goodsDiscount']) === true) $val['dcPrice'] += $val['goodsDcPrice'];
                    if (in_array('coupon', $this->themeConfig['goodsDiscount']) === true) $val['dcPrice'] += $val['couponDcPrice'];
                }
            }

            if ($val['dcPrice'] >= $val['goodsPrice']) {
                $val['dcPrice'] = 0;
            }

            if (in_array('dcRate', $this->themeConfig['displayAddField']) === true) {
                $val['goodsDcRate'] = round((100 * gd_isset($val['dcPrice'], 0)) / $val['goodsPrice']);
                $val['couponDcRate'] = round((100 * $val['couponDcPrice']) / $val['goodsPrice']);
            }

            try {
                if (($val['onlyAdultFl'] == 'y' && gd_check_adult() === false) === false && $imageData[$val['goodsNo']] && gd_is_plus_shop(PLUSSHOP_CODE_LISTMOUSEOVER) === true) {
                    foreach ($imageData[$val['goodsNo']] as $imageKey => $imageValue) {
                        $retData[] = 'data-image-' . $imageKey . ' = "' . SkinUtils::imageViewStorageConfig($imageValue, $val['imagePath'], $val['imageStorage'], $imageSize['size1'], 'goods')[0] . '"';
                    }
                    $val['goodsData'] = implode($retData, ' ');
                    unset($retData);
                }
            } catch (\Exception $e) {}

            // 필요없는 변수 처리
            unset($val['imageStorage'], $val['imagePath'], $val['imageName'], $val['mileageFl']);
        }
    }


    /**
     * 성인인증했는지 여부
     * @deprecated 2017-05-22 atomyang 상품 외 다른기능에서도 성인인증 여부 확인을 위해 gd_check_adult() 사용 하여야함. 추후 삭제 예정
     *
     *
     * @return bool
     */
    public function isAdultView()
    {
        if ((gd_use_ipin() || gd_use_auth_cellphone()) && (!Session::has('certAdult') && (!Session::has('member') || (Session::has('member') && Session::get('member.adultFl') != 'y')))) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * 프론트 상품 리스트를 위한 검색 정보
     *
     * @param null $getValue
     * @param null $searchTerms
     */
    protected function setSearchGoodsList($getValue = null, $searchTerms = null)
    {
        if (is_null($getValue)) $getValue = Request::get()->toArray();
        $searchTerms = $searchTerms['settings'] == null ? ['goodsNm'] : $searchTerms['settings']; // 통합 검색 조건
        $getValue = $this->getDataSort($getValue);

        // 통합 검색
        $this->search['combineSearch'] = [
            'all'             => __('=통합검색='),
            'goodsNm'         => __('상품명'),
            'goodsNo'         => __('상품코드'),
            'goodsCd'         => __('자체상품코드'),
            'makerNm'         => __('제조사'),
            'originNm'        => __('원산지'),
            'goodsSearchWord' => __('검색키워드'),
        ];

        // 검색을 위한 bind 정보
        $fieldTypeGoods = DBTableField::getFieldTypes('tableGoods');
        $fieldTypeOption = DBTableField::getFieldTypes('tableGoodsOption');
        $fieldTypeLinkC = DBTableField::getFieldTypes('tableGoodsLinkCategory');
        $fieldTypeLinkB = DBTableField::getFieldTypes('tableGoodsLinkBrand');

        // 플러스리뷰 관련 설정
        if (gd_is_plus_shop(PLUSSHOP_CODE_REVIEW) === true) {
            // 예외 상품
            if ($getValue['isPlusReview'] === 'y') {
                $plusReviewConfigClass = new PlusReviewConfig();
                $plusReviewConfig = $plusReviewConfigClass->getConfig();
                if ($plusReviewConfig['exceptGoodsFl'] === 'y' && is_array($plusReviewConfig['exceptGoods'])) {
                    $getValue['exceptGoodsNo'] = $plusReviewConfig['exceptGoods'];
                }
            }
            // 상품 카테고리 검색
            if (empty($getValue['cateGoods']) && empty($getValue['plusReviewCateGoods']) === false) {
                $getValue['cateGoods'] = $getValue['plusReviewCateGoods'];
            }
        }

        // --- 검색 설정
        $this->search['detailSearch'] = gd_isset($getValue['detailSearch']);
        $this->search['key'] = gd_isset($getValue['key'], 'all');
        $this->search['keyword'] = gd_isset(gd_htmlspecialchars_slashes($getValue['keyword'], 'add'));
        $this->search['reSearchKeyword'] = gd_isset(array_values($getValue['reSearchKeyword']));
        $this->search['reSearchKey'] = gd_isset(array_values($getValue['reSearchKey']));
        $this->search['reSearch'] = gd_isset($getValue['reSearch']);

        $this->search['cateGoods'] = ArrayUtils::last(gd_isset($getValue['cateGoods']));
        $this->search['brand'] = gd_isset($getValue['brand']);
        $this->search['quickBrandGoods'] = gd_isset($getValue['quickBrandGoods']);
        $this->search['goodsPrice'][] = gd_isset($getValue['goodsPrice'][0]);
        $this->search['goodsPrice'][] = gd_isset($getValue['goodsPrice'][1]);

        $this->search['goodsColor'] = gd_isset($getValue['goodsColor']);
        $this->search['goodsIcon'] = gd_isset($getValue['goodsIcon']);
        $this->search['freeDelivery'] = gd_isset($getValue['freeDelivery']);
        $this->search['newGoods'] = gd_isset($getValue['newGoods']);
        $this->search['goodsNo'] = gd_isset($getValue['goodsNo']);

        $this->search['exceptGoodsNo'] = gd_isset($getValue['exceptGoodsNo']);
        $this->search['exceptCateCd'] = gd_isset($getValue['exceptCateCd']);
        $this->search['exceptBrandCd'] = gd_isset($getValue['exceptBrandCd']);
        $this->search['exceptScmNo'] = gd_isset($getValue['exceptScmNo']);

        $mallBySession = SESSION::get(SESSION_GLOBAL_MALL);

        // 키워드 검색
        if ($this->search['key'] && $this->search['keyword']) {
            if ($this->search['key'] == 'all') {
                $arrWhereAll = [];
                foreach($searchTerms as $termsVal) {
                    if($termsVal == 'brandNm') {
                        $this->useTable[] = 'glb';
                        if ($mallBySession) {
                            $arrWhereAll[] = 'IFNULL(cbg.cateNm, cb.cateNm) LIKE concat(\'%\',?,\'%\')';
                        } else {
                            $arrWhereAll[] = 'cb.cateNm LIKE concat(\'%\',?,\'%\')';
                        }
                        $this->db->bind_param_push($this->arrBind, 's', $this->search['keyword']);
                    } else if($termsVal == 'goodsNm') {
                        if($mallBySession) {
                            $arrWhereAll[] = 'IF(gg.' . $termsVal . ' = \'\' OR gg.' . $termsVal . ' IS NULL , g.' . $termsVal . ' LIKE concat(\'%\',?,\'%\'), gg.' . $termsVal . ' LIKE concat(\'%\',?,\'%\'))';
                            $this->db->bind_param_push($this->arrBind, $fieldTypeGoods[$termsVal], $this->search['keyword']);
                        } else {
                            $arrWhereAll[] = '(REPLACE(g.' . $termsVal . ', \' \', \'\') LIKE concat(\'%\',?,\'%\') OR g.' . $termsVal . ' LIKE concat(\'%\',?,\'%\'))';
                            $this->db->bind_param_push($this->arrBind, $fieldTypeGoods[$termsVal], $this->search['keyword']);
                        }
                        $this->db->bind_param_push($this->arrBind, $fieldTypeGoods[$termsVal], $this->search['keyword']);
                    } else {
                        $arrWhereAll[] = 'g.' . $termsVal . ' LIKE concat(\'%\',?,\'%\')';
                        $this->db->bind_param_push($this->arrBind, $fieldTypeGoods[$termsVal], $this->search['keyword']);
                    }
                }
                $this->arrWhere[] = '(' . implode(' OR ', $arrWhereAll) . ')';
            } else {
                if($mallBySession && $this->search['key'] =='goodsNm') {
                    $this->arrWhere[] = 'IF(gg.goodsNm <> "",gg.goodsNm,g.goodsNm) LIKE concat(\'%\',?,\'%\')';
                } elseif ($this->search['key'] =='brandNm') {
                    $this->useTable[] = 'glb';
                    $fieldTypeGoods[$this->search['key']] = 's';
                    if ($mallBySession) {
                        $this->arrWhere[] = 'IFNULL(cbg.cateNm, cb.cateNm) LIKE concat(\'%\',?,\'%\')';
                    } else {
                        $this->arrWhere[] = 'cb.cateNm LIKE concat(\'%\',?,\'%\')';
                    }
                } else {
                    $this->arrWhere[] = '(REPLACE(g.' . $this->search['key'] . ', \' \', \'\') LIKE concat(\'%\',?,\'%\') OR g.' . $this->search['key'] . ' LIKE concat(\'%\',?,\'%\'))';
                    $this->db->bind_param_push($this->arrBind, $fieldTypeGoods[$this->search['key']], $this->search['keyword']);
                }
                $this->db->bind_param_push($this->arrBind, $fieldTypeGoods[$this->search['key']], $this->search['keyword']);
            }
        }

        //재검색
        if ($this->search['key'] && $this->search['reSearchKeyword'] && $this->search['reSearch'] == 'y') {

            // 이전 검색어들
            $arrWhereAll = [];
            foreach($this->search['reSearchKey'] as $oldKey => $oldKeyword) {
                if($oldKeyword == 'all') { // 이전 검색어가 통합검색어일때
                    foreach($searchTerms as $termsVal) {
                        if($termsVal == 'brandNm') {
                            $this->useTable[] = 'glb';
                            if ($mallBySession) {
                                $arrWhereAll[] = 'IFNULL(cbg.cateNm, cb.cateNm) LIKE concat(\'%\',?,\'%\')';
                            } else {
                                $arrWhereAll[] = 'cb.cateNm LIKE concat(\'%\',?,\'%\')';
                            }
                            $this->db->bind_param_push($this->arrBind, 's', $this->search['reSearchKeyword'][$oldKey]);
                        } else if($termsVal == 'goodsNm') {
                            if($mallBySession) {
                                $arrWhereAll[] = 'IF(gg.' . $termsVal . ' = \'\' OR gg.' . $termsVal . ' IS NULL , g.' . $termsVal . ' LIKE concat(\'%\',?,\'%\'), gg.' . $termsVal . ' LIKE concat(\'%\',?,\'%\'))';
                                $this->db->bind_param_push($this->arrBind, $fieldTypeGoods[$termsVal], $this->search['keyword']);
                            } else {
                                $arrWhereAll[] = '(REPLACE(g.' . $termsVal . ', \' \', \'\') LIKE concat(\'%\',?,\'%\') OR g.' . $termsVal . ' LIKE concat(\'%\',?,\'%\'))';
                                $this->db->bind_param_push($this->arrBind, $fieldTypeGoods[$termsVal], $this->search['reSearchKeyword'][$oldKey]);
                            }
                            $this->db->bind_param_push($this->arrBind, $fieldTypeGoods[$termsVal], $this->search['reSearchKeyword'][$oldKey]);
                        } else {
                            $arrWhereAll[] = 'g.' . $termsVal . ' LIKE concat(\'%\',?,\'%\')';
                            $this->db->bind_param_push($this->arrBind, $fieldTypeGoods[$termsVal], $this->search['reSearchKeyword'][$oldKey]);
                        }
                    }
                    $this->arrWhere[] = '(' . implode(' OR ', $arrWhereAll) . ')';
                } else {
                    if ($oldKeyword == 'brandNm') {
                        $this->useTable[] = 'glb';
                        if ($mallBySession) {
                            $this->arrWhere[] = 'IFNULL(cbg.cateNm, cb.cateNm) LIKE concat(\'%\',?,\'%\')';
                        } else {
                            $this->arrWhere[] = 'cb.cateNm LIKE concat(\'%\',?,\'%\')';
                        }
                        $this->db->bind_param_push($this->arrBind, 's', $this->search['reSearchKeyword'][$oldKey]);
                    } else {
                        $this->arrWhere[] = '(REPLACE(g.' . $oldKeyword . ', \' \', \'\')  LIKE concat(\'%\',?,\'%\') OR g.' . $oldKeyword . ' LIKE concat(\'%\',?,\'%\'))';
                        $this->db->bind_param_push($this->arrBind, $fieldTypeGoods[$oldKeyword], $this->search['reSearchKeyword'][$oldKey]);
                        $this->db->bind_param_push($this->arrBind, $fieldTypeGoods[$oldKeyword], $this->search['reSearchKeyword'][$oldKey]);
                    }
                }
            }
        } else {
            unset($this->search['reSearchKeyword']);
            unset($this->search['reSearchKey']);
        }

        if ($this->search['goodsNo']) {
            if (is_array($this->search['goodsNo'])) {
                foreach ($this->search['goodsNo'] as $key => $val) {
                    $this->db->bind_param_push($this->arrBind, 'i', $val);
                    $goodsNoTmp[] = '?';
                }
                $this->arrWhere[] =  'g.goodsNo IN (' . implode(',', $goodsNoTmp) . ')';
            } else {
                $this->arrWhere[] = 'g.goodsNo = ?';
                $this->db->bind_param_push($this->arrBind,$fieldTypeGoods['goodsNo'], $this->search['goodsNo']);
            }
        }

        // 카테고리 검색
        if ($this->search['cateGoods']) {
            $this->arrWhere[] = 'glc.cateCd = ?';
            $this->db->bind_param_push($this->arrBind, $fieldTypeLinkC['cateCd'], $this->search['cateGoods']);
            $this->useTable[] = 'glc';
        }
        // 브랜드 검색
        if ($this->search['brand']) {
            if (is_array($this->search['brand'])) {
                $arrWhereAll = [];
                foreach ($this->search['brand'] as $keyNm) {
                    $arrWhereAll[] = 'glb.cateCd = ? AND glb.cateLinkFl = "y"';
                    $this->db->bind_param_push($this->arrBind, $fieldTypeLinkB['cateCd'], $keyNm);
                    $this->useTable[] = 'glb';
                }
                $this->arrWhere[] = '(' . implode(' OR ', $arrWhereAll) . ')';

            } else {
                $this->arrWhere[] = 'glb.cateCd = ? AND glb.cateLinkFl = "y"';
                $this->db->bind_param_push($this->arrBind, $fieldTypeLinkB['cateCd'], $this->search['brand']);
                $this->useTable[] = 'glb';
            }

        }

        // 상품가격 검색
        if ($this->search['goodsPrice'][1]) {
            if($mallBySession) {
                $exchangeRate = new ExchangeRate();
                $number = $exchangeRate->getExchangeRate()['exchangeRate'.$mallBySession['currencyConfig']['isoCode']];

                $this->arrWhere[] = 'g.goodsPrice BETWEEN ? AND ?';
                $this->db->bind_param_push($this->arrBind, $fieldTypeGoods['goodsPrice'], $this->search['goodsPrice'][0]*$number);
                $this->db->bind_param_push($this->arrBind, $fieldTypeGoods['goodsPrice'], $this->search['goodsPrice'][1]*$number);

            } else {
                $this->arrWhere[] = 'g.goodsPrice BETWEEN ? AND ?';
                $this->db->bind_param_push($this->arrBind, $fieldTypeGoods['goodsPrice'], $this->search['goodsPrice'][0]);
                $this->db->bind_param_push($this->arrBind, $fieldTypeGoods['goodsPrice'], $this->search['goodsPrice'][1]);
            }
        }

        //색깔 검색
        if ($this->search['goodsColor']) {
            $arrWhereAll = [];
            foreach ($this->search['goodsColor'] as $keyNm) {
                $arrWhereAll[] = '(g.goodsColor LIKE concat(\'%\',?,\'%\'))';
                $this->db->bind_param_push($this->arrBind, $fieldTypeGoods['goodsColor'], $keyNm);
            }
            $this->arrWhere[] = '(' . implode(' OR ', $arrWhereAll) . ')';
            unset($arrWhereAll);
        }

        //최근 검색 / 등록일 30일 기준
        if ($this->search['newGoods']) {

            $startRegDt = date('Y-m-d', strtotime('-1 month'));
            $endRegDt = date('Y-m-d');

            $this->arrWhere[] = 'g.regDt BETWEEN ? AND ?';
            $this->db->bind_param_push($this->arrBind, 's', $startRegDt . ' 00:00:00');
            $this->db->bind_param_push($this->arrBind, 's', $endRegDt . ' 23:59:59');
        }


        //무료배송
        if ($this->search['freeDelivery']) {

            $tmpWhere = [];

            $delivery = \App::load('\\Component\\Delivery\\Delivery');
            $deliveryData = $delivery->getDeliveryGoods(['goodsDeliveryFixFl' => ['free']]);

            if (is_array($deliveryData)) {
                foreach ($deliveryData as $val) {
                    $tmpWhere[] = 'g.deliverySno = ?';
                    $this->db->bind_param_push($this->arrBind, 's', $val['sno']);
                }
                $this->arrWhere[] = '(' . implode(' OR ', $tmpWhere) . ')';
                unset($tmpWhere);
            }
        }

        //제외 상품
        if ($this->search['exceptGoodsNo']) {
            $this->arrWhere[] = 'g.goodsNo NOT IN (\'' . implode('\',\'', $this->search['exceptGoodsNo']) . '\')';
        }

        //제외 카테고리
        if ($this->search['exceptCateCd']) {
            $this->arrWhere[] = 'g.cateCd NOT IN (\'' . implode('\',\'', $this->search['exceptCateCd']) . '\')';
        }

        //제외 브랜드
        if ($this->search['exceptBrandCd']) {
            $this->arrWhere[] = 'g.brandCd NOT IN (\'' . implode('\',\'', $this->search['exceptBrandCd']) . '\')';
        }

        //제외 공급사
        if ($this->search['exceptScmNo']) {
            $this->arrWhere[] = 'g.scmNo NOT IN (\'' . implode('\',\'', $this->search['exceptScmNo']) . '\')';
        }

        if ($this->search['goodsIcon']) {
            $arrWhereAll = [];
            foreach ($this->search['goodsIcon'] as $periodFl => $value) {
                switch ($periodFl) {
                    case 'y': //기간제한 아이콘
                        foreach ($value as $icon) {
                            $arrWhereAll[] = '(gi.goodsIconCd = ? AND gi.iconKind = \'pe\' AND (? BETWEEN gi.goodsIconStartYmd AND gi.goodsIconEndYmd))';
                            $this->db->bind_param_push($this->arrBind, 's', $icon);
                            $this->db->bind_param_push($this->arrBind, 's', gd_date_format('Y-m-d', 'now'));
                        }
                        break;
                    case 'n': //무제한 아이콘  + 상품 혜택 아이콘 검색 추가
                        foreach ($value as $icon) {
                            $arrWhereAll[] = 'gi.goodsIconCd = ? AND gi.iconKind = \'un\'';
                            $this->db->bind_param_push($this->arrBind, 's', $icon);
                            if($goodsBenefitUse == 'y') {
                                $arrWhereAll[] = 'gi.goodsIconCd = ? AND gi.iconKind = \'pr\'';
                                $this->db->bind_param_push($this->arrBind, 's', $icon); //상품 혜택 아이콘 검색
                            }
                        }
                        break;
                }
            }
            $this->arrWhere[] = '(' . implode(' OR ', $arrWhereAll) . ')';
            unset($arrWhereAll);
        }

        if (empty($this->arrBind)) {
            $this->arrBind = null;
        }
    }

    /**
     * 상품 정보 출력 (상품 리스트)
     *
     * @param string $cateCd 카테고리 코드
     * @param string $cateMode 카테고리 모드 (category, brand)
     * @param int $pageNum 페이지 당 리스트수 (default 10)
     * @param string $displayOrder 상품 기본 정렬 - 'sort asc', Category::getSort() 참고
     * @param string $imageType 이미지 타입 - 기본 'main'
     * @param boolean $optionFl 옵션 출력 여부 - true or false (기본 false)
     * @param boolean $soldOutFl 품절상품 출력 여부 - true or false (기본 true)
     * @param boolean $brandFl 브랜드 출력 여부 - true or false (기본 true)
     * @param boolean $couponPriceFl 쿠폰가격 출력 여부 - true or false (기본 false)
     * @param integer $imageViewSize 이미지 크기 (기본 "0" - 0은 원래 크기)
     * @param integer $displayCnt 상품 출력 갯수 - 기본 10개
     * @return array 상품 정보
     * @throws Exception
     */
    public function getGoodsList($cateCd, $cateMode = 'category', $pageNum = 10, $displayOrder = 'sort asc', $imageType = 'list', $optionFl = false, $soldOutFl = true, $brandFl = false, $couponPriceFl = false, $imageViewSize = 0, $displayCnt = 10)
    {

        $delivery = \App::load('\\Component\\Delivery\\Delivery');

        $display = \App::load('\\Component\\Display\\DisplayConfigAdmin');
        $displayNavi = $display->getDateNaviDisplay();

        gd_isset($this->goodsTable,DB_GOODS);
        $mallBySession = SESSION::get(SESSION_GLOBAL_MALL);
        // Validation - 상품 코드 체크
        if (Validator::required($cateCd, true) === false) {
            throw new Exception(self::ERROR_VIEW . self::TEXT_NOT_EXIST_CATECD);
        }

        $getValue = Request::get()->toArray();

        // --- 정렬 설정
        if (gd_isset($getValue['sort'])) {
            $dSort = $getValue['sort'];
            if (method_exists($this, 'getSortMatch')) {
                $dSort = $this->getSortMatch($dSort);
            }

            // 품절상품 정렬 추가 (정렬시 최우선)
            if (strpos($dSort, "soldOut asc") === false) {
                $dSort = 'soldOut asc, '.$dSort;
            }

            $sort[] = $dSort;
        } else {

            if ($displayOrder) {
                if (is_array($displayOrder)) $sort[] = implode(",", $displayOrder);
                else $sort[] = $displayOrder;

            } else {
                $sort[] = "gl.goodsSort desc";
            }
        }

        $sort = implode(',', $sort);
        if($sort) {
            if(strpos($sort, "regDt") !== false) $sort = str_replace("regDt","goodsNo",$sort);
            if(strpos($sort, "goodsNo") === false) $sort = $sort.', goodsNo desc ';
        }

        if(strpos($displayOrder, "soldOut") !== false) $addField = ",( if (g.soldOutFl = 'y' , 'y', if (g.stockFl = 'y' AND g.totalStock <= 0, 'y', 'n') ) ) as soldOut";

        // --- 페이지 기본설정
        gd_isset($getValue['page'], 1);

        // 배수 설정
        $getData['multiple'] = range($displayCnt, $displayCnt * 4, $displayCnt);

        $page = \App::load('\\Component\\Page\\Page', $getValue['page']);
        $page->page['list'] = $pageNum; // 페이지당 리스트 수
        $page->block['cnt'] = Request::isMobile() ? 5 : 10; // 블록당 리스트 개수
        $page->setPage();
        $page->setUrl(\Request::getQueryString());

        // 카테고리 종류에 따른 설정
        if ($cateMode == 'category') {
            $dbTable = DB_GOODS_LINK_CATEGORY;
            $viewName="goods";
        } else {
            $dbTable = DB_GOODS_LINK_BRAND;
            $viewName="brand";
        }

        // 조인 설정
        $arrJoin[] = ' INNER JOIN '.$this->goodsTable.' g ON gl.goodsNo = g.goodsNo ';

        // 조건절 설정
        $this->db->bind_param_push($this->arrBind, 's', $cateCd);
        $this->arrWhere[] = 'gl.cateCd = ?';
        if ($cateMode == 'category' || ($cateMode == 'brand' && $displayNavi['data']['brand']['linkUse'] != 'y')) {
            $this->arrWhere[] = 'gl.cateLinkFl = \'y\'';
        }
        $this->arrWhere[] = 'g.delFl = \'n\'';
        $this->arrWhere[] = 'g.applyFl = \'y\'';
        $this->arrWhere[] = 'g.' . $this->goodsDisplayFl . ' = \'y\'';
        $this->arrWhere[] = '(UNIX_TIMESTAMP(g.goodsOpenDt) IS NULL  OR UNIX_TIMESTAMP(g.goodsOpenDt) = 0 OR UNIX_TIMESTAMP(g.goodsOpenDt) < UNIX_TIMESTAMP())';

        //접근권한 체크
        if (gd_check_login()) {
            $this->arrWhere[] = '(g.goodsAccess !=\'group\'  OR (g.goodsAccess=\'group\' AND FIND_IN_SET(\''.Session::get('member.groupSno').'\', REPLACE(g.goodsAccessGroup,"'.INT_DIVISION.'",","))) OR (g.goodsAccess=\'group\' AND !FIND_IN_SET(\''.Session::get('member.groupSno').'\', REPLACE(g.goodsAccessGroup,"'.INT_DIVISION.'",",")) AND g.goodsAccessDisplayFl =\'y\'))';
        } else {
            $this->arrWhere[] = '(g.goodsAccess=\'all\' OR (g.goodsAccess !=\'all\' AND g.goodsAccessDisplayFl =\'y\'))';
        }

        //성인인증안된경우 노출체크 상품은 노출함
        if (gd_check_adult() === false) {
            $this->arrWhere[] = '(onlyAdultFl = \'n\' OR (onlyAdultFl = \'y\' AND onlyAdultDisplayFl = \'y\'))';
        }

        if ($soldOutFl === false) { // 품절 처리 여부
            $this->arrWhere[] = 'NOT(g.stockFl = \'y\' AND g.totalStock = 0) AND NOT(g.soldOutFl = \'y\')';
        }

        // 필드 설정
        $this->setGoodsListField(); // 상품 리스트용 필드
        if($this->goodsDivisionFl) {
            $this->db->strField = 'STRAIGHT_JOIN g.goodsNo'.gd_isset($addField);
        } else {
            $this->db->strField = 'STRAIGHT_JOIN ' . $this->goodsListField . gd_isset($addField);
        }

        $this->db->strJoin = implode('', $arrJoin);
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
        $this->db->strOrder = $sort;
        $this->db->strLimit = $page->recode['start'] . ',' . $pageNum;

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . $dbTable . ' gl ' . implode(' ', $query);

        $data = $this->db->slave()->query_fetch($strSQL, $this->arrBind);

        if($data) {
            if($this->goodsDivisionFl) {
                /* 상품 테이블에서 정보 가져옴 */
                $strGoodsSQL = 'SELECT ' . $this->goodsListField . ' FROM ' . DB_GOODS . ' g WHERE goodsNo IN ("' . implode('","', array_column($data, 'goodsNo')) . '") ORDER BY FIELD(g.goodsNo,' . implode(',', array_column($data, 'goodsNo')) . ')';
                $data = $this->db->query_fetch($strGoodsSQL);
            }

            /* 검색 count 쿼리 */
            $totalCountSQL =  ' SELECT COUNT(gl.goodsNo) AS totalCnt FROM ' . $dbTable . ' as gl '.implode('', $arrJoin).'  WHERE '.implode(' AND ', $this->arrWhere);
            $dataCount = $this->db->slave()->query_fetch($totalCountSQL, $this->arrBind,false);
            unset($this->arrBind, $this->arrWhere);

            // 검색 레코드 수
            $page->recode['total'] = $dataCount['totalCnt']; //검색 레코드 수
            $page->setPage();

            // 상품 정보 세팅
            if (empty($data) === false) {
                $this->setGoodsListInfo($data, $imageType, $optionFl, $couponPriceFl, $imageViewSize,$viewName,$brandFl);
            }
        }

        unset($this->arrBind, $this->arrWhere);

        // 배송비 유형 설정
        //        foreach($data as $key => $goodInfo) {
        //            $goodsView = $this->getGoodsView($goodInfo['goodsNo']);
        //            $deliveryInfo = $delivery->getDeliveryType($goodsView['deliverySno']);
        //            $data[$key]['deliveryType'] = $deliveryInfo['deliveryType'];
        //            $data[$key]['deliveryMethod'] = $deliveryInfo['method'];
        //            $data[$key]['deliveryDes'] = $deliveryInfo['description'];
        //        }

        // 각 데이터 배열화
        $getData['listData'] = gd_htmlspecialchars_stripslashes(gd_isset($data));
        $getData['listSort'] = $displayOrder;
        $getData['listSearch'] = gd_htmlspecialchars($this->search);
        unset($this->search);
        return $getData;

    }

    /**
     * 상품 검색 정보 출력
     *
     * @param string  $searchData    검색 데이타
     * @param integer $displayCnt    상품 출력 갯수 - 기본 10개
     * @param string  $displayOrder  상품 기본 정렬 - 'sort asc', Category::getSort() 참고
     * @param string  $imageType     이미지 타입 - 기본 'main'
     * @param boolean $optionFl      옵션 출력 여부 - true or false (기본 false)
     * @param boolean $soldOutFl     품절상품 출력 여부 - true or false (기본 true)
     * @param boolean $brandFl       브랜드 출력 여부 - true or false (기본 false)
     * @param boolean $couponPriceFl 쿠폰가격 출력 여부 - true or false (기본 false)
     * @param boolean $usePage       paging 사용여부
     * @param integer $limit         상품수
     * @param array $goodsNo         상품번호
     *
     * @return array 상품 정보
     */
    public function getGoodsSearchList($pageNum = 10, $displayOrder = 'g.regDt asc', $imageType = 'list', $optionFl = false, $soldOutFl = true, $brandFl = false, $couponPriceFl = false, $displayCnt = 10, $brandDisplayFl = false, $usePage = true, $limit = null,array $goodsNo = null)
    {
        gd_isset($this->goodsTable,DB_GOODS);
        $arrBind = null;
        $mallBySession = SESSION::get(SESSION_GLOBAL_MALL);

        $getValue = Request::get()->toArray();

        // --- 정렬 설정
        if (gd_isset($getValue['sort'])) {
            $sort = $getValue['sort'];
            if (method_exists($this, 'getSortMatch')) {
                $sort = $this->getSortMatch($sort);
            }
        } else {
            $sort = $displayOrder;
        }

        // --- 페이지 기본설정
        gd_isset($getValue['page'], 1);

        // 배수 설정
        $getData['multiple'] = range($displayCnt, $displayCnt * 4, $displayCnt);


        if ($usePage === true) {
            $page = \App::load('\\Component\\Page\\Page', $getValue['page']);
            $page->page['list'] = $pageNum; // 페이지당 리스트 수
            $page->block['cnt'] = !Request::isMobile() ? 10 : 5; // 블록당 리스트 개수
            $page->setPage();
            $page->setUrl(\Request::getQueryString());
        }

        // --- 검색 설정
        $terms = gd_policy('search.terms');
        $this->setSearchGoodsList(null, $terms);

        if (in_array('glb', $this->useTable) === true) {
            $arrJoin[] = ' LEFT JOIN ' . DB_GOODS_LINK_BRAND . ' glb ON g.goodsNo = glb.goodsNo AND glb.cateLinkFl != \'n\'';
            if($mallBySession){
                $arrJoin[] = ' LEFT JOIN ' . DB_CATEGORY_BRAND . ' cb ON g.brandCd = cb.cateCd AND  FIND_IN_SET('.$mallBySession['sno'].',cb.mallDisplay)';
                $arrJoin[] = ' LEFT JOIN ' . DB_CATEGORY_BRAND_GLOBAL . ' cbg ON cb.cateCd = cbg.cateCd AND mallSno = '.$mallBySession['sno'];
            }
            else $arrJoin[] = ' LEFT JOIN ' . DB_CATEGORY_BRAND . ' cb ON g.brandCd = cb.cateCd   ';
        }

        if (in_array('glc', $this->useTable) === true) {
            $arrJoin[] = ' INNER JOIN ' . DB_GOODS_LINK_CATEGORY . ' glc ON g.goodsNo = glc.goodsNo ';
        }

        $goodsBenefit = \App::load('\\Component\\Goods\\GoodsBenefit');
        $goodsBenefitUse = $goodsBenefit->getConfig();

        //상품 혜택에서 현재 진행중인 아이콘 검색
        if ($this->search['goodsIcon']) {
            if($goodsBenefitUse == 'y') {
                $arrJoin[] = 'LEFT JOIN
                (
                select t1.goodsNo,t1.benefitSno,t1.goodsIconCd
                from ' . DB_GOODS_LINK_BENEFIT . ' as t1,
                (select goodsNo, min(linkPeriodStart) as min_start from ' . DB_GOODS_LINK_BENEFIT . ' where ((benefitUseType=\'periodDiscount\' or benefitUseType=\'newGoodsDiscount\') AND linkPeriodStart < NOW() AND linkPeriodEnd > NOW()) or benefitUseType=\'nonLimit\'  group by goodsNo) as t2
                where t1.linkPeriodStart = t2.min_start and t1.goodsNo = t2.goodsNo
                ) as gbs on g.goodsNo = gbs.goodsNo ';
            }

            //상품 아이콘 테이블추가
            if ($this->search['goodsIconCdPeriod'] && !$this->search['goodsIconCd']) {
                $arrJoin[] = ' LEFT JOIN ' . DB_GOODS_ICON . ' as gi ON g.goodsNo = gi.goodsNo ';
            } else {
                if($goodsBenefitUse == 'y') {
                    $arrJoin[] = ' LEFT JOIN ' . DB_GOODS_ICON . ' as gi ON g.goodsNo = gi.goodsNo OR (gbs.benefitSno = gi.benefitSno AND gi.iconKind = \'pr\')';
                }else{
                    $arrJoin[] = ' LEFT JOIN ' . DB_GOODS_ICON . ' as gi ON g.goodsNo = gi.goodsNo ';
                }

            }

            // 검색 조건에 아이콘 검색이 있는 경우 group by 추가
            $this->db->strGroup = "g.goodsNo ";
            $goodsIconStrGroup = " GROUP BY g.goodsNo";
        }

        // 기본 조건절 설정
        $this->arrWhere[] = 'g.' . $this->goodsDisplayFl . ' = \'y\'';
        $this->arrWhere[] = 'g.delFl = \'n\'';
        $this->arrWhere[] = 'g.applyFl = \'y\'';
        $this->arrWhere[] = '(UNIX_TIMESTAMP(g.goodsOpenDt) IS NULL  OR UNIX_TIMESTAMP(g.goodsOpenDt) = 0 OR UNIX_TIMESTAMP(g.goodsOpenDt) < UNIX_TIMESTAMP())';

        if(is_array($goodsNo)){
            $bindQuery = null;
            foreach($goodsNo as $val){
                $bindQuery[] = '?';
                $this->db->bind_param_push($this->arrBind,'i',$val);
            }
            $this->arrWhere[]  = " g.goodsNo in (".implode(',',$bindQuery).")";
        }

        //접근권한 체크
        if (gd_check_login()) {
            $this->arrWhere[] = '(g.goodsAccess !=\'group\'  OR (g.goodsAccess=\'group\' AND FIND_IN_SET(\''.Session::get('member.groupSno').'\', REPLACE(g.goodsAccessGroup,"'.INT_DIVISION.'",","))) OR (g.goodsAccess=\'group\' AND !FIND_IN_SET(\''.Session::get('member.groupSno').'\', REPLACE(g.goodsAccessGroup,"'.INT_DIVISION.'",",")) AND g.goodsAccessDisplayFl =\'y\'))';
        } else {
            $this->arrWhere[] = '(g.goodsAccess=\'all\' OR (g.goodsAccess !=\'all\' AND g.goodsAccessDisplayFl =\'y\'))';
        }

        //성인인증안된경우 노출체크 상품은 노출함
        if (gd_check_adult() === false) {
            $this->arrWhere[] = '(onlyAdultFl = \'n\' OR (onlyAdultFl = \'y\' AND onlyAdultDisplayFl = \'y\'))';
        }

        if ($soldOutFl === false) { // 품절 처리 여부
            $this->arrWhere[] = 'NOT(g.stockFl = \'y\' AND g.totalStock = 0) AND NOT(g.soldOutFl = \'y\')';
        }

        // 필드 설정
        $this->setGoodsListField(); // 상품 리스트용 필드

        if($mallBySession) {
            $arrJoin[] = ' LEFT JOIN ' . DB_GOODS_GLOBAL . ' gg ON g.goodsNo = gg.goodsNo AND gg.mallSno = '.$mallBySession['sno'];
        }

        if($sort) {
            if(strpos($sort, "regDt") !== false) $sort = str_replace("regDt","goodsNo",$sort);
            if(strpos($sort, "goodsNo") === false) $sort = $sort.', goodsNo desc ';
            if(strpos($sort, "goodsNm") !== false) $sort = str_replace("goodsNm","g.goodsNm",$sort); //글로벌과 동시에 선언시 필드모호성때문에 추가
        } else {
            $sort = "goodsNo desc";
        }

        if(strpos($sort, "soldOut") !== false) $addField = ",( if (g.soldOutFl = 'y' , 'y', if (g.stockFl = 'y' AND g.totalStock <= 0, 'y', 'n') ) ) as soldOut";

        $this->db->strJoin = implode('', $arrJoin);
        if($this->goodsDivisionFl) {
            $this->db->strField = 'g.goodsNo' . gd_isset($addField);
            $this->db->strGroup = 'g.goodsNo';
        } else {
            $this->db->strField = $this->goodsListField . gd_isset($addField);
        }

        $this->db->strOrder = $sort;  //$sort가 null인경우가 있어서 검색조건 추가
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
        if ($usePage === true) {
            $this->db->strLimit = $page->recode['start'] . ',' . $pageNum;
        }else {
            if (empty($limit) === false) {
                $this->db->strLimit = '0,' . $limit;
            } else {
                $this->db->strLimit = '0,' . $pageNum;
            }
        }
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . $this->goodsTable . ' g ' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $this->arrBind);

        if($data) {
            if($this->goodsDivisionFl) {
                //검색테이블에서 검색 후 상품정보 가져옴
                $strSQL = 'SELECT ' . $this->goodsListField . ' FROM ' . DB_GOODS . ' g WHERE ';

                $bindQuery = $arrBind = null;
                foreach(array_column($data, 'goodsNo') as $val){
                    $bindQuery[] = '?';
                    $this->db->bind_param_push($arrBind,'i',$val);
                }
                $strSQL .= " goodsNo IN  (".implode(',',$bindQuery).")";
                $strSQL .=' ORDER BY ' . $sort;
                $tmpGoodsData = $this->db->query_fetch($strSQL, $arrBind);
                $data = array_combine(array_column($tmpGoodsData, 'goodsNo'), $tmpGoodsData);
            }

            if ($brandDisplayFl) {
                //현재 그룹 정보
                $myGroup = \Session::get('member.groupSno');

                if ($mallBySession) {
                    $tmpJoin[] = ' INNER JOIN ' . DB_GOODS_LINK_BRAND . ' glb ON g.goodsNo = glb.goodsNo';
                    $tmpJoin[] = ' LEFT JOIN ' . DB_CATEGORY_BRAND . ' cb ON g.brandCd = cb.cateCd AND FIND_IN_SET(' . $mallBySession['sno'] . ', cb.mallDisplay)';
                    $tmpJoin[] = ' LEFT JOIN ' . DB_CATEGORY_BRAND_GLOBAL . ' cbg ON cb.cateCd = cbg.cateCd AND mallSno = '.$mallBySession['sno'];
                    $tmpJoin[] = ' LEFT JOIN ' . DB_GOODS_GLOBAL . ' gg ON g.goodsNo = gg.goodsNo AND gg.mallSno = '.$mallBySession['sno'];
                    $this->db->strJoin .= implode('', $tmpJoin);
                } else {
                    $this->db->strJoin = ' INNER JOIN ' . DB_GOODS_LINK_BRAND . ' glb ON g.goodsNo = glb.goodsNo  LEFT JOIN ' . DB_CATEGORY_BRAND . ' cb ON g.brandCd = cb.cateCd';
                }
                if (in_array('glc', $this->useTable) === true) {
                    $this->db->strJoin .= ' INNER JOIN ' . DB_GOODS_LINK_CATEGORY . ' glc ON g.goodsNo = glc.goodsNo ';
                }

                //상품 혜택에서 현재 진행중인 아이콘 검색
                if ($this->search['goodsIcon']) {
                    if($goodsBenefitUse == 'y') {
                        $this->db->strJoin .= ' LEFT JOIN
                        (
                        select t1.goodsNo,t1.benefitSno,t1.goodsIconCd
                        from ' . DB_GOODS_LINK_BENEFIT . ' as t1,
                        (select goodsNo, min(linkPeriodStart) as min_start from ' . DB_GOODS_LINK_BENEFIT . ' where ((benefitUseType=\'periodDiscount\' or benefitUseType=\'newGoodsDiscount\') AND linkPeriodStart < NOW() AND linkPeriodEnd > NOW()) or benefitUseType=\'nonLimit\'  group by goodsNo) as t2
                        where t1.linkPeriodStart = t2.min_start and t1.goodsNo = t2.goodsNo
                        ) as gbs on g.goodsNo = gbs.goodsNo ';
                    }

                    //상품 아이콘 테이블추가
                    if ($this->search['goodsIconCdPeriod'] && !$this->search['goodsIconCd']) {
                        $this->db->strJoin .= ' LEFT JOIN ' . DB_GOODS_ICON . ' as gi ON g.goodsNo = gi.goodsNo ';
                    } else {
                        if($goodsBenefitUse == 'y') {
                            $this->db->strJoin .= ' LEFT JOIN ' . DB_GOODS_ICON . ' as gi ON g.goodsNo = gi.goodsNo OR (gbs.benefitSno = gi.benefitSno AND gi.iconKind = \'pr\')';
                        }else{
                            $this->db->strJoin .= ' LEFT JOIN ' . DB_GOODS_ICON . ' as gi ON g.goodsNo = gi.goodsNo ';
                        }
                    }
                }

                //접근권한 체크
                if (gd_check_login()) {
                    $brandArrWhere = '(catePermission !=\'2\'  OR (catePermission=\'2\' AND FIND_IN_SET(\''.Session::get('member.groupSno').'\', REPLACE(catePermissionGroup,"'.INT_DIVISION.'",","))) OR (catePermission=\'2\' AND !FIND_IN_SET(\''.Session::get('member.groupSno').'\', REPLACE(catePermissionGroup,"'.INT_DIVISION.'",",")) AND catePermissionDisplayFl =\'y\'))';
                } else {
                    $brandArrWhere = '(catePermission=\'all\' OR (catePermission !=\'all\' AND catePermissionDisplayFl =\'y\'))';
                }

                if (gd_check_adult() === false) {
                    $brandArrWhere .= ' AND (cateOnlyAdultFl = \'n\' OR (cateOnlyAdultFl = \'y\' AND cateOnlyAdultDisplayFl = \'y\'))';
                }

                $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere))." AND glb.cateLinkFl='y' AND ".$brandArrWhere;
                $this->db->strGroup = "g.brandCd";
                $this->db->strField = 'cb.cateNm as brandNm , count(cb.cateCd) as brandCnt , cb.cateCd as brandCd ,cb.catePermission,cb.catePermissionGroup,cb.cateOnlyAdultFl';
                $query = $this->db->query_complete();
                $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . $this->goodsTable . ' g ' . implode(' ', $query);
                $brandSearchList = $this->db->query_fetch($strSQL, $this->arrBind);
                $brandSearchList = array_combine(array_column($brandSearchList, 'brandCd'), $brandSearchList);

                if($mallBySession) {
                    $bindQuery = $arrBind = null;
                    foreach(array_column($brandSearchList, 'brandCd') as $val){
                        $bindQuery[] = '?';
                        $this->db->bind_param_push($arrBind,'s',$val);
                    }
                    $strSQLGlobal = "SELECT cateNm as brandNm, cateCd as brandCd FROM " . DB_CATEGORY_BRAND_GLOBAL . "  WHERE cateCd IN (" . implode(",", $bindQuery) . ") AND mallSno = " . $mallBySession['sno'];
                    $tmpData = $this->db->query_fetch($strSQLGlobal,$arrBind);
                    $brandData = array_combine(array_column($tmpData, 'brandCd'), $tmpData);
                    if($brandData) {
                        $brandSearchList = array_replace_recursive($brandSearchList,$brandData);
                    }
                }

                foreach($brandSearchList as $k => $v) {

                    $disabledFl = false;
                    if ($v['cateOnlyAdultFl'] =='y' && gd_check_adult() === false) {
                        $disabledFl = true;
                    }

                    // 현 카테고리의 권한 정보
                    if ($v['catePermission'] > 0) {
                        // 현재 카테고리 권한에 따른 정보 카테고리 체크
                        if (gd_is_login() === false) {
                            $disabledFl = true;
                        }

                        if($v['catePermission'] =='2' && $v['catePermissionGroup'] && !in_array( $myGroup,explode(INT_DIVISION,$v['catePermissionGroup']))) {
                            $disabledFl = true;
                        }
                    }

                    $brandSearchList[$k]['disabledFl'] = $disabledFl;
                }
                $this->search['brandSearchList'] = $brandSearchList;
                unset($this->db->strGroup, $this->db->strField);
            }


            /* 검색 count 쿼리 */
            if ($usePage === true) {
                if ($this->search['goodsIcon']) {
                    $totalCountSQL =  ' SELECT COUNT(1) as totalCnt FROM ( SELECT g.goodsNo FROM '.$this->goodsTable.' as g  '.implode('', $arrJoin).'  WHERE '.implode(' AND ', $this->arrWhere) . $goodsIconStrGroup . ') AS tbl';
                } else {
                    $totalCountSQL =  ' SELECT COUNT(DISTINCT g.goodsNo) AS totalCnt FROM '.$this->goodsTable.' as g  '.implode('', $arrJoin).'  WHERE '.implode(' AND ', $this->arrWhere);
                }
                $dataCount = $this->db->slave()->query_fetch($totalCountSQL, $this->arrBind,false);
                $page->recode['total'] = $dataCount['totalCnt']; //검색 레코드 수

                if ($getValue['offsetGoodsNum'] && $page->recode['total'] > $getValue['offsetGoodsNum']) {
                    $page->recode['total'] = $getValue['offsetGoodsNum'];
                }

                $page->setPage();
            }

            // 상품 정보 세팅
            if (empty($data) === false) {
                if($getValue['isMain']) $this->setGoodsListInfo($data, $imageType, $optionFl, $couponPriceFl, null,'main',$brandFl, $getValue['mainLinkData']);
                else $this->setGoodsListInfo($data, $imageType, $optionFl, $couponPriceFl, null,null,$brandFl);
            }

        }

        unset($this->arrBind, $this->arrWhere);

        // 각 데이터 배열화
        $getData['listData'] = gd_htmlspecialchars_stripslashes(gd_isset($data));
        $getData['listSearch'] = gd_htmlspecialchars($this->search);
        unset($this->search);

        return $getData;
    }

    /**
     * 상품 정보 출력 (상품 상세)
     *
     * @param string $goodsNo 상품 번호
     *
     * @return array 상품 정보
     * @throws Except
     */
    public function getGoodsView($goodsNo)
    {
        $mallBySession = SESSION::get(SESSION_GLOBAL_MALL);

        // Validation - 상품 코드 체크
        if (Validator::required($goodsNo, true) === false) {
            throw new Exception(__('상품 코드를 확인해주세요.'));
        }

        // 필드 설정
        $arrExcludeGoods = ['goodsIconStartYmd', 'goodsIconEndYmd', 'goodsIconCdPeriod', 'goodsIconCd', 'memo'];
        $arrFieldGoods = DBTableField::setTableField('tableGoods', null, $arrExcludeGoods, 'g');
        $this->db->strField = implode(', ', $arrFieldGoods) . ',
            ( if (g.soldOutFl = \'y\' , \'y\', if (g.stockFl = \'y\' AND g.totalStock <= 0, \'y\', \'n\') ) ) as soldOut,
            ( if (g.' . $this->goodsSellFl . ' = \'y\', g.' . $this->goodsSellFl . ', \'n\')  ) as orderPossible';

        // 조건절 설정
        //if(!Session::has('manager.managerId')) $arrWhere[] = 'g.' . $this->goodsDisplayFl . ' = \'y\'';
        $arrWhere[] = 'g.delFl = \'n\'';
        $arrWhere[] = 'g.applyFl = \'y\'';

        $this->db->strWhere = implode(' AND ', $arrWhere);

        // 상품 기본 정보
        $getData = $this->getGoodsInfo($goodsNo);

        // 삭제된 상품에 접근시 예외 처리
        if(empty($getData)) {
            throw new Exception(__('해당 상품은 현재 구매가 불가한 상품입니다.'));
        }

        // 아이콘 테이블 분리로 인한 추가
        $tmpGoodsIcon = [];
        $iconList = $this->getGoodsDetailIcon($goodsNo);
        foreach ($iconList as $iconKey => $iconVal) {
            if ($iconVal['iconKind'] == 'pe') {
                if (empty($iconVal['goodsIconStartYmd']) === false && empty($iconVal['goodsIconEndYmd']) === false && empty($iconVal['goodsIconCd']) === false && strtotime($iconVal['goodsIconStartYmd']) <= time() && strtotime($iconVal['goodsIconEndYmd']) >= time()) {
                    $tmpGoodsIcon[] = $iconVal['goodsIconCd'];
                }
            }

            if ($iconVal['iconKind'] == 'un') {
                $tmpGoodsIcon[] = $iconVal['goodsIconCd'];
            }
        }

        $getData['goodsIcon'] = implode(INT_DIVISION,$tmpGoodsIcon);

        //상품 혜택 정보
        $goodsBenefit = \App::load('\\Component\\Goods\\GoodsBenefit');
        $getData = $goodsBenefit->goodsDataFrontConvert($getData,null,'goodsIcon');

        if (empty($getData) === true && !Session::has('manager.managerId')) {
            throw new Exception(__('해당 상품은 쇼핑몰 노출안함 상태로 검색되지 않습니다.'));
        }

        // 승인중인 상품에 대한 접근 예외 처리
        if ($getData['applyFl'] != 'y') {
            throw new Exception(__('본 상품은 접근이 불가능 합니다.'));
        }

        // 브랜드 정보
        if (empty($getData['brandCd']) === false) {
            $brand = \App::load('\\Component\\Category\\Brand');
            $getData['brandNm'] = $brand->getCategoryData($getData['brandCd'], null, 'cateNm')[0]['cateNm'];
        } else {
            $getData['brandNm'] = '';
        }

        if($mallBySession) {
            $arrFieldGoodsGlobal = DBTableField::setTableField('tableGoodsGlobal',null,['mallSno']);
            $strSQLGlobal = "SELECT gg." . implode(', gg.', $arrFieldGoodsGlobal) . " FROM ".DB_GOODS_GLOBAL." as gg WHERE   gg.goodsNo  = ".$getData['goodsNo']." AND gg.mallSno = ".$mallBySession['sno'];
            $tmpData = $this->db->query_fetch($strSQLGlobal,null,false);
            if($tmpData) $getData = array_replace_recursive($getData, array_filter(array_map('trim',$tmpData)));
        }
        //카테고리 정보
        $cate = \App::load('\\Component\\Category\\Category');
        $tmpCategoryList = $cate->getCateCd($getData['goodsNo']);
        if($tmpCategoryList) {
            foreach($tmpCategoryList as $k => $v) {
                $categoryList[$v] = gd_htmlspecialchars_decode($cate->getCategoryPosition($v));
            }
        }
        if($categoryList) $getData['categoryList'] = $categoryList;

        // 대표카테고리명 정보
        if (empty($getData['cateCd']) === false) {
            $getData['cateNm'] = $cate->getCategoryData($getData['cateCd'], null, 'cateNm')[0]['cateNm'];
        } else {
            $getData['cateNm'] = '';
        }

        // 추가항목 정보
        $getData['addInfo'] = $this->getGoodsAddInfo($goodsNo); // 추가항목 정보

        // 이미지 정보
        $tmp['image'] = $this->getGoodsImage($goodsNo, ['detail', 'magnify']);

        // 상품 아이콘
        if ($getData['goodsIcon']) {
            $tmp['goodsIcon'] = $this->getGoodsIcon($getData['goodsIcon']);
        }

        // 상품 아이콘
        if ($getData['goodsBenefitIconCd']) {
            $tmp['goodsBenefitIconCd'] = $this->getGoodsIcon($getData['goodsBenefitIconCd']);
        }

        $imgConfig = gd_policy('goods.image');

        //품절상품 설정
        if(Request::isMobile()) {
            $soldoutDisplay = gd_policy('soldout.mobile');
        } else {
            $soldoutDisplay = gd_policy('soldout.pc');
        }

        // 상품 이미지 처리
        $getData['magnifyImage'] = 'n';
        if (empty($tmp['image'])) {
            $getData['image']['detail'][0] = '';
            $getData['image']['thumb'][0] = '';
        } else {
            foreach ($tmp['image'] as $key => $val) {
                $imageHeightSize = '';
                if ($imgConfig['imageType'] == 'fixed') {
                    foreach ($imgConfig[$val['imageKind']] as $k => $v) {
                        if (stripos($k, 'size') === 0) {
                            if ($val['imageSize'] == $v) {
                                $imageHeightSize = $imgConfig[$val['imageKind']]['h' . $k];
                                break;
                            }
                        }
                    }
                }

                // 이미지 사이즈가 없는 경우
                if (empty($val['imageSize']) === true) {
                    $imageSize = $imgConfig[$val['imageKind']]['size1'];
                } else {
                    $imageSize = $val['imageSize'];
                }

                //실제 이미지 사이즈가 있는 경우
                if($val['imageRealSize']) {
                    $imageSize = explode(",",$val['imageRealSize'])[0];
                }

                // 모바일샵 접속인 경우
                if (Request::isMobile()) {
                    $imageSize = 140;
                    $imageHeightSize = '';
                }

                $getData['image'][$val['imageKind']]['img'][] = gd_html_preview_image($val['imageName'], $getData['imagePath'], $getData['imageStorage'], $imageSize, 'goods', $getData['goodsNm'], null, false, false, $imageHeightSize);

                $getData['image'][$val['imageKind']]['thumb'][] = gd_html_preview_image($val['imageName'], $getData['imagePath'], $getData['imageStorage'], 68, 'goods', $getData['goodsNm'], null, false, true);

                if ($val['imageKind'] == 'magnify') {
                    $getData['magnifyImage'] = 'y';
                }
            }
            if (isset($getData['image']) === false) {
                $getData['image']['detail'][0] = '';
                $getData['image']['thumb'][0] = '';
            }
        }

        // 소셜 공유용 이미지 처리(이미지 없는경우 빈 이미지 출력되도록 수정)
        $socialShareImage = SkinUtils::imageViewStorageConfig($tmp['image'][0]['imageName'], $getData['imagePath'], $getData['imageStorage'], $imageSize, 'goods');
        $getData['social'] = $socialShareImage[0];


        // 상품 혜택 아이콘 처리
        $getData['goodsIcon'] = '';
        $getData['goodsBenefitIconCd'] = '';
        if (empty($tmp['goodsBenefitIconCd']) === false) {
            foreach ($tmp['goodsBenefitIconCd'] as $key => $val) {
                $getData['goodsIcon'] .= gd_html_image(UserFilePath::icon('goods_icon', $val['iconImage'])->www(), $val['iconNm']) . ' ';
            }
        }

        // 상품 아이콘 처리
        if (empty($tmp['goodsIcon']) === false) {
            foreach ($tmp['goodsIcon'] as $key => $val) {
                $getData['goodsIcon'] .= gd_html_image(UserFilePath::icon('goods_icon', $val['iconImage'])->www(), $val['iconNm']) . ' ';
            }
        }


        // 옵션 체크, 옵션 사용인 경우
        if ($getData['optionFl'] === 'y') {
            // 옵션 & 가격 정보
            $getData['option'] = gd_htmlspecialchars($this->getGoodsOption($goodsNo, $getData));
            if($getData['option']) {
                $getData['optionEachCntFl'] = 'many'; // 옵션 개수
                if (empty($getData['option']['optVal'][2]) === true) {
                    $getData['optionEachCntFl'] = 'one'; // 옵션 개수

                    // 분리형 옵션인데 옵션이 하나인 경우 일체형으로 변경
                    if ($getData['optionDisplayFl'] == 'd') {
                        $getData['optionDisplayFl'] = 's';
                    }
                }


                // 상품 옵션 아이콘
                $tmp['optionIcon'] = $this->getGoodsOptionIcon($goodsNo);

                if (empty($tmp['optionIcon']) === false) {
                    $imageSize = $imgConfig['detail'];
                    foreach ($tmp['optionIcon'] as $key => $val) {
                        if (empty($val['goodsImage']) === false) {
                            $getData['optionIcon']['goodsImage'][$val['optionValue']] =SkinUtils::imageViewStorageConfig($val['goodsImage'], $getData['imagePath'], $getData['imageStorage'], '100', 'goods')[0];
                            if( $getData['optionImageDisplayFl'] =='y') {
                                $optionImagePreview = gd_html_preview_image($val['goodsImage'], $getData['imagePath'], $getData['imageStorage'], $imageSize, 'goods', $getData['goodsNm'], null, false, false);;
                                $getData['image']['detail']['img'][] =$optionImagePreview;
                                $getData['image']['detail']['thumb'][] = $optionImagePreview;
                            }
                        }
                    }
                    // 옵션 값을 json_encode 처리함
                    //$getData['optionIcon'] = json_encode($getData['optionIcon']);
                }
                // 분리형 옵션인 경우
                if ($getData['optionDisplayFl'] == 'd') {
                    // 옵션명
                    $getData['optionName'] = explode(STR_DIVISION, $getData['optionName']);

                    // 첫번째 옵션 값
                    $getData['optionDivision'] = $getData['option']['optVal'][1];

                    // 분리형 다중옵션 경우 첫번째 옵션의 재고량 및 옵션품절상태 조회
                    if (method_exists($this, 'getOptionValueStock') && is_array($getData['optionDivision']) === true) {
                        foreach ($getData['optionDivision'] as $key => $value) {
                            $getData['optionDivisionStock'][$key] = $this->getOptionValueStock($goodsNo, [$value]);
                        }
                    }

                    unset($getData['option']['optVal']);
                    // 일체형 옵션인 경우
                } else if ($getData['optionDisplayFl'] == 's') {
                    unset($getData['option']['optVal']);

                    // 옵션명
                    $getData['optionName'] = str_replace(STR_DIVISION, '/', $getData['optionName']);

                    foreach ($getData['option'] as $key => $val) {

                        if($getData['optionIcon']['goodsImage'][$val['optionValue1']]) {
                            $getData['option'][$key]['optionImage'] = $getData['optionIcon']['goodsImage'][$val['optionValue1']];
                        }

                        $optionValue[$key] = [];
                        for ($i = 1; $i <= DEFAULT_LIMIT_OPTION; $i++) {
                            if (is_null($val['optionValue' . $i]) === false && strlen($val['optionValue' . $i]) > 0) {
                                $optionValue[$key][] = $val['optionValue' . $i];
                            }
                            unset($getData['option'][$key]['optionValue' . $i]);
                        }
                        $getData['option'][$key]['optionValue'] = implode('/', $optionValue[$key]);
                    }
                }

                $getData['stockCnt'] = $getData['option'][0]['stockCnt'];

            } else {
                throw new Exception(__('상품 옵션을 확인해주세요.'));
            }
        } else {
            $getData['option'] = gd_htmlspecialchars($this->getGoodsOption($goodsNo, $getData));
            $getData['stockCnt'] = $getData['totalStock'];
            if($getData['option'][0]['optionPrice'] > 0) $getData['option'][0]['optionPrice'] = 0; //옵션사용안함으로 가격 없음
            if($getData['stockFl'] =='y' && $getData['minOrderCnt'] > $getData['totalStock'])  $getData['orderPossible'] = 'n';
        }

        //상품 상세 설명 관련
        if($getData['goodsDescriptionSameFl'] =='y') {
            $getData['goodsDescriptionMobile'] = $getData['goodsDescription'];
        }

        /* 타임 세일 관련 */
        $getData['timeSaleFl'] = false;
        if (gd_is_plus_shop(PLUSSHOP_CODE_TIMESALE) === true) {
            $timeSale = \App::load('\\Component\\Promotion\\TimeSale');
            $timeSaleInfo = $timeSale->getGoodsTimeSale($goodsNo);
            if($timeSaleInfo) {
                $getData['timeSaleFl'] = true;
                if($timeSaleInfo['timeSaleCouponFl'] =='n') $couponConfig['couponUseType']  = "n";
                $timeSaleInfo['timeSaleDuration'] = strtotime($timeSaleInfo['endDt'])- time();
                if($timeSaleInfo['orderCntDisplayFl'] =='y' ) { //타임세일 진행기준 판매개수
                    $arrTimeSaleBind = [];
                    $strTimeSaleSQL = "SELECT sum(orderCnt) as orderCnt FROM " . DB_GOODS_STATISTICS . " WHERE goodsNo = ?";
                    $this->db->bind_param_push($arrTimeSaleBind, 'i', $goodsNo);
                    if($timeSaleInfo['orderCntDateFl'] =='y' ) {
                        $strTimeSaleSQL .= " AND UNIX_TIMESTAMP(regDt) <  ? AND  UNIX_TIMESTAMP(regDt)  > ?";
                        $this->db->bind_param_push($arrTimeSaleBind, 'i', strtotime($timeSaleInfo['endDt']));
                        $this->db->bind_param_push($arrTimeSaleBind, 'i', strtotime($timeSaleInfo['startDt']));
                    }
                    $timeSaleInfo['orderCnt'] = $this->db->query_fetch($strTimeSaleSQL, $arrTimeSaleBind, false)['orderCnt'];
                    unset($arrTimeSaleBind,$strTimeSaleSQL);
                }

                $getData['timeSaleInfo'] = $timeSaleInfo;
                if($getData['goodsPrice'] > 0 ) {
                    $getData['oriGoodsPrice'] = $getData['goodsPrice'] ;
                    $getData['goodsPrice'] = gd_number_figure($getData['goodsPrice'] - (($timeSaleInfo['benefit'] / 100) * $getData['goodsPrice']), $this->trunc['unitPrecision'], $this->trunc['unitRound']);
                }

                //상품 옵션가(일체형) 타임세일 할인율 적용 ( 텍스트 옵션가 / 추가상품가격 제외)
                if($getData['optionFl'] === 'y'){
                    foreach ($getData['option'] as $key => $val){
                        $getData['option'][$key]['optionPrice'] = gd_number_figure($val['optionPrice'] - (($timeSaleInfo['benefit'] / 100) * $val['optionPrice']), $this->trunc['unitPrecision'], $this->trunc['unitRound']);
                    }
                }
            }
        }
        $couponConfig = gd_policy('coupon.config');
        // 쿠폰가 회원만 노출
        if ($couponConfig['couponDisplayType'] == 'member') {
            if (gd_check_login()) {
                $couponPriceYN = true;
            } else {
                $couponPriceYN = false;
            }
        } else {
            $couponPriceYN = true;
        }

        // 혜택제외 체크 (쿠폰)
        $exceptBenefit = explode(STR_DIVISION, $getData['exceptBenefit']);
        $exceptBenefitGroupInfo = explode(INT_DIVISION, $getData['exceptBenefitGroupInfo']);
        if (in_array('coupon', $exceptBenefit) === true && ($getData['exceptBenefitGroup'] == 'all' || ($getData['exceptBenefitGroup'] == 'group') && in_array(Session::get('member.memNo'), $exceptBenefitGroupInfo) === true)) {
            $couponPriceYN = false;
        }

        // 쿠폰 할인 금액
        if ($couponConfig['couponUseType'] == 'y' && $couponPriceYN  && $getData['goodsPrice'] > 0 && empty($getData['goodsPriceString']) === true) {
            // 쿠폰 모듈 설정
            $coupon = \App::load('\\Component\\Coupon\\Coupon');
            // 해당 상품의 모든 쿠폰
            $couponArrData = $coupon->getGoodsCouponDownList($getData['goodsNo']);

            // 해당 상품의 쿠폰가
            $getData['couponDcPrice'] = $couponSalePrice = $coupon->getGoodsCouponDisplaySalePrice($couponArrData, $getData['goodsPrice']);
            if ($couponSalePrice) {
                $getData['couponPrice'] = $getData['goodsPrice'] - $couponSalePrice;
                $getData['couponSalePrice'] = $couponSalePrice;
                if ($getData['couponPrice'] < 0) {
                    $getData['couponPrice'] = 0;
                }
            }
        }

        //내 쿠폰적용가 추가 (회원 별 보유중인 상품 적용 쿠폰(마일리지X) 중 최고 할인가로 노출)
        //$couponConfig['couponUseType'] : 쿠폰 사용 유무,  $getData['goodsPrice'] : 판매가, $getData['goodsPriceString'] : 가격대체문구
        if (gd_check_login() === 'member' && $couponConfig['couponUseType'] == 'y' && $getData['goodsPrice'] > 0 && empty($getData['goodsPriceString']) === true) {
            if (gettype($coupon) !== 'object') { $coupon = \App::load('\\Component\\Coupon\\Coupon'); }
            //상품에 적용 가능한 쿠폰 리스트 가져오기
            $myCouponArrData = $coupon->getGoodsMemberCouponList($getData['goodsNo'], Session::get('member.memNo'), $this->_memInfo['groupSno'], null, null, 'goods', false, [], ['sale']);
            // 위에서 추출한 데이터를 가지고 최대 할인가를 구함.
            $myCouponSalePrice = $coupon->getGoodsCouponDisplaySalePrice($myCouponArrData, $getData['goodsPrice']);
            if ($myCouponSalePrice > 0) {
                $getData['myCouponSalePrice']   = $getData['goodsPrice'] - $myCouponSalePrice;
                $getData['myCouponPrice']       = $myCouponSalePrice;
                if ($getData['myCouponSalePrice'] < 0) { $getData['myCouponPrice'] = 0; }
            }
            unset($myCouponArrData);
            unset($myCouponSalePrice);
        }

        $getData['displayCouponPrice'] = 'n'; //쿠폰적용가 노출 여부
        if ($getData['myCouponSalePrice'] > 0 || $getData['couponPrice'] > 0) {
            $getData['displayCouponPrice'] = 'y';
        }

        //추가 상품 정보
        if ($getData['addGoodsFl'] === 'y' && empty($getData['addGoods']) === false) {

            $getData['addGoods'] = json_decode(gd_htmlspecialchars_stripslashes($getData['addGoods']), true);

            //필수 추가상품 중 승인완료가 아닌 상품이 있는 경우 구매 불가
            $addGoods = \App::load('\\Component\\Goods\\AddGoods');
            if ($getData['addGoods']) {
                foreach ($getData['addGoods'] as $k => $v) {

                    if($v['addGoods']) {
                        if($v['mustFl'] =='n') $addGoods->arrWhere[] = "applyFl = 'y'";
                        else {
                            $applyCheckCnt = $this->db->getCount(DB_ADD_GOODS, 'addGoodsNo', 'WHERE applyFl !="y"  AND addGoodsNo IN ("' . implode('","', $v['addGoods']) . '")');
                            if($applyCheckCnt > 0 ) {
                                $getData['orderPossible'] = 'n';
                                break;
                            } else {
                                $addGoods->arrWhere[] = "applyFl != ''";
                            }
                        }

                        foreach ($v['addGoods']as $k1 => $v1) {
                            $tmpField[] = 'WHEN \'' . $v1 . '\' THEN \'' . sprintf("%0".strlen(count($v['addGoods']))."d",$k1) . '\'';
                        }

                        $sortField = ' CASE ag.addGoodsNo ' . implode(' ', $tmpField) . ' ELSE \'\' END ';
                        unset($tmpField);

                        $getData['addGoods'][$k]['addGoodsList'] = $addGoods->getInfoAddGoodsGoods($v['addGoods'],null,$sortField,"viewFl = 'y' ");
                        $getData['addGoods'][$k]['addGoodsImageFl'] = "n";
                        if($getData['addGoods'][$k]['addGoodsList']) {
                            foreach($getData['addGoods'][$k]['addGoodsList'] as $k1 => $v1) {
                                // strip_tags 처리를 통해 결제오류 수정
                                $getData['addGoods'][$k]['addGoodsList'][$k1]['goodsNm'] = htmlentities(stripslashes(StringUtils::stripOnlyTags($getData['addGoods'][$k]['addGoodsList'][$k1]['goodsNm'])));
                                $getData['addGoods'][$k]['addGoodsList'][$k1]['optionNm'] = htmlentities(stripslashes(StringUtils::stripOnlyTags($getData['addGoods'][$k]['addGoodsList'][$k1]['optionNm'])));

                                //추가 상품등록페이지 - 추가 상품명
                                if($v1['globalGoodsNm']) $getData['addGoods'][$k]['addGoodsList'][$k1]['goodsNm'] = htmlentities(stripslashes(StringUtils::stripOnlyTags($v1['globalGoodsNm'])));
                                if($v1['imageNm']) {
                                    $getData['addGoods'][$k]['addGoodsList'][$k1]['imageSrc'] = SkinUtils::imageViewStorageConfig($v1['imageNm'], $v1['imagePath'], $v1['imageStorage'], '50', 'add_goods')['0'];
                                    $getData['addGoods'][$k]['addGoodsImageFl'] = "y";
                                }
                            }
                        }
                    }
                }
            }
        }


        // 텍스트 옵션 정보
        if ($getData['optionTextFl'] === 'y') {
            $getData['optionText'] = gd_htmlspecialchars($this->getGoodsOptionText($goodsNo));
        }

        // QR코드
        if (gd_is_plus_shop(PLUSSHOP_CODE_QRCODE) === true) {
            $qrcode = gd_policy('promotion.qrcode'); // QR코드 설정
            if ($qrcode['useGoods'] !== 'y') {
                $getData['qrCodeFl'] = 'n';
            }
        } else {
            $getData['qrCodeFl'] = 'n';
        }

        // 상품 정보 처리
        $getData['goodsNmDetail'] = StringUtils::htmlSpecialCharsStripSlashes($this->getGoodsName($getData['goodsNmDetail'], $getData['goodsNm'], $getData['goodsNmFl'])); // 상품 상세 페이지 -  상품명
        if (Validator::date($getData['makeYmd'], true) === false) { // 제조일 체크
            $getData['makeYmd'] = null;
        }
        if (Validator::date($getData['launchYmd'], true) === false) { // 출시일 체크
            $getData['launchYmd'] = null;
        }

        //배송비 관련
        if ($getData['deliverySno']) {
            $delivery = \App::load('\\Component\\Delivery\\Delivery');
            $deliveryData = $delivery->getDataSnoDelivery($getData['deliverySno']);
            if ($deliveryData['basic']['areaFl'] == 'y' && gd_isset($deliveryData['basic']['areaGroupNo'])) {
                $deliveryData['areaDetail'] = $delivery->getSnoDeliveryArea($deliveryData['basic']['areaGroupNo']);
            }

            $deliveryData['basic']['fixFlText'] = $delivery->getFixFlText($deliveryData['basic']['fixFl']);
            $deliveryData['basic']['goodsDeliveryFlText'] = $delivery->getGoodsDeliveryFlText($deliveryData['basic']['goodsDeliveryFl']);
            $deliveryData['basic']['collectFlText'] = $delivery->getCollectFlText($deliveryData['basic']['collectFl']);
            $deliveryData['basic']['areaFlText'] = $delivery->getAddFlText($deliveryData['basic']['areaFl']);
            $deliveryData['basic']['pricePlusStandard'] = explode(STR_DIVISION, $deliveryData['basic']['pricePlusStandard']);
            $deliveryData['basic']['priceMinusStandard'] = explode(STR_DIVISION, $deliveryData['basic']['priceMinusStandard']);
            // 가공된 배송 방식 데이터
            $deliveryData['basic']['deliveryMethodFlData'] = [];
            $deliveryMethodFlArr = array_values(array_filter(explode(STR_DIVISION, $deliveryData['basic']['deliveryMethodFl'])));
            if($deliveryMethodFlArr > 0){
                foreach($deliveryMethodFlArr as $key => $value){
                    if($value === 'etc'){
                        $deliveryMethodListName = gd_get_delivery_method_etc_name();
                    }
                    else {
                        $deliveryMethodListName = $delivery->deliveryMethodList['name'][$value];
                    }
                    $deliveryData['basic']['deliveryMethodFlData'][$value] = $deliveryMethodListName;

                    if($key === 0){
                        $deliveryData['basic']['deliveryMethodFlFirst'] = [
                            'code' => $value,
                            'name' => $deliveryMethodListName,
                        ];
                    }
                }
            }
            //배송방식 방문수령지
            if($deliveryData['basic']['dmVisitTypeDisplayFl'] !== 'y'){
                $deliveryData['basic']['deliveryMethodVisitArea'] = $delivery->getVisitAddress($getData['deliverySno'], true);
            }

            $getData['delivery'] = $deliveryData;

            // 상품판매가를 기준으로 배송비 선택해서 charge의 키를 저장한다.
            $getData['selectedDeliveryPrice'] = 0;
            if (in_array($deliveryData['basic']['fixFl'], ['price', 'weight'])) {
                // 비교할 필드값 설정
                $compareField = $getData['goods' . ucfirst($deliveryData['basic']['fixFl'])];
                foreach ($getData['delivery']['charge'] as $dKey => $dVal) {
                    // 금액 or 무게가 범위에 없으면 통과
                    if (floatval($dVal['unitEnd']) > 0) {
                        if (floatval($dVal['unitStart']) <= floatval($compareField) && floatval($dVal['unitEnd']) > floatval($compareField)) {
                            $getData['selectedDeliveryPrice'] = $dKey;
                            break;
                        }
                    } else {
                        if (floatval($dVal['unitStart']) <= floatval($compareField)) {
                            $getData['selectedDeliveryPrice'] = $dKey;
                            break;
                        }
                    }
                }
            }

            /*
             * 수량별 배송비 이면서 범위 반복 설정을 사용 할 경우 수량1의 기준으로 배송비 노출
             * @todo 추후 금액별, 무게별 배송비의 범위 반복 설정 사용일 경우도 계산해서 노출해야 하므로 임시로 노출시킨다.
             */
            if ($deliveryData['basic']['fixFl'] === 'count' && $deliveryData['basic']['rangeRepeat'] === 'y') {
                if((int)$deliveryData['charge'][0]['unitEnd'] <= 1){
                    $getData['selectedDeliveryPrice'] = 1;
                }
            }
        }

        // 상품 필수 정보
        $getData['goodsMustInfo'] = json_decode(gd_htmlspecialchars_stripslashes($getData['goodsMustInfo']), true);

        // KC인증 정보 (해외몰 적용X)
        if (Globals::get('gGlobal.isFront') == false) {
            $this->getKcmarkInfo($getData['kcmarkInfo']);
        }

        // 마일리지 설정
        $mileage = gd_mileage_give_info();

        $getData['goodsMileageFl'] = 'y';
        // 통합 설정인 경우 마일리지 설정
        if ($getData['mileageFl'] == 'c' && $mileage['give']['giveFl'] == 'y') {
            $mileagePercent = $mileage['give']['goods'] / 100;

            // 상품 기본 마일리지 정보
            $getData['mileageBasic'] = gd_number_figure($getData['goodsPrice'] * $mileagePercent, $mileage['trunc']['unitPrecision'], $mileage['trunc']['unitRound']);

            // 상품 옵션 마일리지 정보
            if ($getData['optionFl'] === 'y') {
                foreach ($getData['option'] as $key => $val) {
                    $getData['option'][$key]['mileageOption'] = gd_number_figure($val['optionPrice'] * $mileagePercent, $mileage['trunc']['unitPrecision'], $mileage['trunc']['unitRound']);
                }
            }


            // 추가 상품 마일리지 정보
            if ($getData['addGoodsFl'] === 'y' && empty($getData['addGoods']) === false && empty($getData['addGoodsGoodsNo']) === false) {
                foreach ($getData['addGoods'] as $key => $val) {
                    $getData['addGoods'][$key]['mileageAddGoods'] = gd_number_figure($val['goodsPrice'] * $mileagePercent, $mileage['trunc']['unitPrecision'], $mileage['trunc']['unitRound']);
                }
            }


            // 상품 텍스트 옵션 마일리지 정보
            if ($getData['optionTextFl'] === 'y') {
                foreach ($getData['optionText'] as $key => $val) {
                    $getData['optionText'][$key]['mileageOptionText'] = gd_number_figure($val['addPrice'] * $mileagePercent, $mileage['trunc']['unitPrecision'], $mileage['trunc']['unitRound']);
                }
            }

            // 개별 설정인 경우 마일리지 설정
        } else if ($getData['mileageFl'] == 'g') {
            $mileagePercent = $getData['mileageGoods'] / 100;

            // 상품 기본 마일리지 정보
            if ($getData['mileageGoodsUnit'] === 'percent') {
                $getData['mileageBasic'] = gd_number_figure($getData['goodsPrice'] * $mileagePercent, $mileage['trunc']['unitPrecision'], $mileage['trunc']['unitRound']);
            } else {
                // 정액인 경우 해당 설정된 금액으로
                $getData['mileageBasic'] = $getData['mileageGoods'];
            }

            // 상품 옵션 마일리지 정보
            if ($getData['optionFl'] === 'y') {
                foreach ($getData['option'] as $key => $val) {
                    if ($getData['mileageGoodsUnit'] === 'percent') {
                        $getData['option'][$key]['mileageOption'] = gd_number_figure($val['optionPrice'] * $mileagePercent, $mileage['trunc']['unitPrecision'], $mileage['trunc']['unitRound']);
                    } else {
                        // 정액인 경우 0 (상품 기본에만 있음)
                        $getData['option'][$key]['mileageOption'] = 0;
                    }
                }
            }

            // 추가 상품 마일리지 정보
            if ($getData['addGoodsFl'] === 'y' && empty($getData['addGoods']) === false && empty($getData['addGoodsGoodsNo']) === false) {
                foreach ($getData['addGoods'] as $key => $val) {
                    if ($getData['mileageGoodsUnit'] === 'percent') {
                        $getData['addGoods'][$key]['mileageAddGoods'] = gd_number_figure($val['goodsPrice'] * $mileagePercent, $mileage['trunc']['unitPrecision'], $mileage['trunc']['unitRound']);
                    } else {
                        // 정액인 경우 0 (상품 기본에만 있음)
                        $getData['addGoods'][$key]['mileageAddGoods'] = 0;
                    }
                }
            }

            // 상품 텍스트 옵션 마일리지 정보
            if ($getData['optionTextFl'] === 'y') {
                foreach ($getData['optionText'] as $key => $val) {
                    if ($getData['mileageGoodsUnit'] === 'percent') {
                        $getData['optionText'][$key]['mileageOptionText'] = gd_number_figure($val['addPrice'] * $mileagePercent, $mileage['trunc']['unitPrecision'], $mileage['trunc']['unitRound']);
                    } else {
                        // 정액인 경우 0 (상품 기본에만 있음)
                        $getData['optionText'][$key]['mileageOptionText'] = 0;
                    }
                }
            }
        } else {
            $getData['goodsMileageFl'] = 'n';
        }


        $getData['mileageConf'] = $mileage;

        //상품 가격 노출 관련
        $goodsPriceDisplayFl = gd_policy('goods.display')['priceFl'];
        $getData['goodsPriceDisplayFl'] = 'y';


        //상품별할인
        if ($getData['goodsDiscountFl'] == 'y') {
            if ($getData['goodsDiscountUnit'] == 'price') $getData['goodsDiscountPrice'] = $getData['goodsPrice'] - $getData['goodsDiscount'];
            else $getData['goodsDiscountPrice'] = $getData['goodsPrice'] - (($getData['goodsDiscount'] / 100) * $getData['goodsPrice']);
        }

        //회원관련
        if (gd_is_login() === true) {
            // 회원 그룹 설정
            $memberGroup = \App::load('\\Component\\Member\\MemberGroup');
            $getData['memberDc'] = $memberGroup->getGroupForSale($goodsNo, $getData['cateCd']);

            //회원 할인가
            if ($getData['memberDc'] && $getData['dcLine'] && $getData['dcPrice']) {
                $getData['memberDcPriceFl'] = 'y';
                if ($getData['memberDc']['dcType'] == 'price') $getData['memberDcPrice'] = $getData['memberDc']['dcPrice'];
                else $getData['memberDcPrice'] = (($getData['memberDc']['dcPercent'] / 100) * $getData['goodsPrice']);

            } else $getData['memberDcPriceFl'] = 'n';


            //회원 적립
            if ($getData['memberDc'] && $getData['mileageLine'] && $getData['mileageLine']) $getData['memberMileageFl'] = 'y';
            else $getData['memberMileageFl'] = 'n';

            //결제수한제단 체크
            if ($getData['payLimitFl'] == 'y' && gd_isset($getData['payLimit'])) {
                $getData['memberDc']['settleGb'] = Util::matchSettleGbDataToString($getData['memberDc']['settleGb']);
                $payLimit = array_intersect($getData['memberDc']['settleGb'], explode(STR_DIVISION, $getData['payLimit']));

                if(count($payLimit) == 0) {
                    $getData['orderPossible'] = 'n';
                }
            }

        } else {
            $getData['memberDcPriceFl'] = 'n';
            $getData['memberMileageFl'] = 'n';
        }

        // 구매 가능여부 체크
        if ($getData['soldOut'] == 'y') {
            $getData['orderPossible'] = 'n';
            if($goodsPriceDisplayFl =='n' && $soldoutDisplay['soldout_price'] !='price') $getData['goodsPriceDisplayFl'] = 'n';
        }

        //구매불가 대체 문구 관련
        if($getData['goodsPermission'] !='all' && (($getData['goodsPermission'] =='member'  && gd_is_login() === false) || ($getData['goodsPermission'] =='group'  && !in_array(Session::get('member.groupSno'),explode(INT_DIVISION,$getData['goodsPermissionGroup']))))) {
            if($getData['goodsPermissionPriceStringFl'] =='y' ) $getData['goodsPriceString'] = $getData['goodsPermissionPriceString'];
            $getData['orderPossible'] = 'n';
        }

        if (((gd_isset($getData['salesStartYmd']) != '' && gd_isset( $getData['salesEndYmd']) != '') && ($getData['salesStartYmd'] != '0000-00-00 00:00:00' && $getData['salesEndYmd'] != '0000-00-00 00:00:00')) && (strtotime($getData['salesStartYmd']) > time() || strtotime($getData['salesEndYmd']) < time())) {
            $getData['orderPossible'] = 'n';
        }

        if ($getData['goodsMileageFl'] == 'y' || $getData['memberMileageFl'] == 'y' || $getData['goodsDiscountFl'] == 'y' || $getData['memberDcPriceFl'] == 'y') {
            $getData['benefitPossible'] = 'y';
        } else $getData['benefitPossible'] = 'n';

        //판매기간 사용자 노출
        if (((gd_isset($getData['salesStartYmd']) != '' && gd_isset( $getData['salesEndYmd']) != '') && ($getData['salesStartYmd'] != '0000-00-00 00:00:00' && $getData['salesEndYmd'] != '0000-00-00 00:00:00'))) {
            $getData['salesData'] = $getData['salesStartYmd']." ~ ".$getData['salesEndYmd'];
        } else {
            $getData['salesData'] = __('제한없음');
        }

        // 관련 상품
        $getData['relation']['relationFl'] = $getData['relationFl'];
        $getData['relation']['relationCnt'] = $getData['relationCnt'];
        $getData['relation']['relationGoodsNo'] = $getData['relationGoodsNo'];
        $getData['relation']['cateCd'] = $getData['cateCd'];
        unset($getData['relationFl'], $getData['relationCnt'], $getData['relationGoodsNo']);

        // 상품 이용 안내
        $getData['detailInfo']['detailInfoDelivery'] = $getData['detailInfoDelivery'];
        $getData['detailInfo']['detailInfoAS'] = $getData['detailInfoAS'];
        $getData['detailInfo']['detailInfoRefund'] = $getData['detailInfoRefund'];
        $getData['detailInfo']['detailInfoExchange'] = $getData['detailInfoExchange'];
        unset($getData['detailInfoDelivery'], $getData['detailInfoAS'], $getData['detailInfoRefund'], $getData['detailInfoExchange']);

        // 가격 대체 문구가 있는 경우 주문금지
        if (empty($getData['goodsPriceString']) === false) {
            $getData['orderPossible'] = 'n';
            if($goodsPriceDisplayFl =='n') $getData['goodsPriceDisplayFl'] = 'n';
        }


        //최소구매수량 관련
        if ($getData['fixedSales'] != 'goods' && gd_isset($getData['salesUnit'], 0) > $getData['minOrderCnt']) {
            $getData['minOrderCnt'] = $getData['salesUnit'];
        }

        //초기상품수량
        $getData['goodsCnt'] = 1;
        if ($getData['fixedSales'] != 'goods') {
            if ($getData['salesUnit'] > 1) {
                $getData['goodsCnt'] = $getData['salesUnit'];
            } else {
                if ($getData['fixedOrderCnt'] == 'option') {
                    $getData['goodsCnt'] = $getData['minOrderCnt'];
                }
            }
        }

        //
        if (gd_is_plus_shop(PLUSSHOP_CODE_COMMONCONTENT) === true) {
            $commonContent = \App::load('\\Component\\Goods\\CommonContent');
            $getData['commonContent'] = $commonContent->getCommonContent($getData['goodsNo'], $getData['scmNo']);
        }

        //상품 재입고 노출여부
        if (gd_is_plus_shop(PLUSSHOP_CODE_RESTOCK) === true) {
            $getData['restockUsableFl'] = $this->setRestockUsableFl($getData);
        }

        // 재고량 체크
        $getData['stockCnt'] = $this->getOptionStock($goodsNo, null, $getData['stockFl'], $getData['soldOutFl']);

        $getData['multipleDeliveryFl'] = false;
        if ($getData['delivery']['basic']['fixFl'] != 'free' && $getData['delivery']['basic']['deliveryConfigType'] == 'etc' && (count($getData['delivery']['basic']['deliveryMethodFlData']) <= 1) === false) {
            $getData['multipleDeliveryFl'] = true;
        }

        //할인가 기본 세팅
        $getData['goodsDcPrice'] = $this->getGoodsDcPrice($getData);

        // 상품혜택관리 치환코드 생성
        $getData = $goodsBenefit->goodsDataFrontReplaceCode($getData);

        return $getData;
    }

    /**
     * 상품 정보 출력 (상품 상세)
     *
     * @param string $goodsNo 상품 번호
     *
     * @return array 상품 정보
     */
    /*public function getGoodsMagnifyImage($goodsNo)
    {
        // Validation - 상품 코드 체크
        if (Validator::required($goodsNo, true) === false) {
            throw new Exception(self::ERROR_VIEW . self::TEXT_NOT_EXIST_GOODSNO);
        }

        $getData = $this->getGoodsInfo($goodsNo, 'goodsNmFl, goodsNm, goodsNmDetail, imagePath, imageStorage');
        $tmp['image'] = $this->getGoodsImage($goodsNo, 'magnify'); // 이미지 정보

        // 상품 정보 처리
        $getData['goodsNmDetail'] = $this->getGoodsName($getData['goodsNmDetail'], $getData['goodsNm'], $getData['goodsNmFl']); // 상품 상세 상품명

        // 상품 이미지 처리
        if (empty($tmp['image'])) {
            $getData['image']['magnify'][0] = '';
            $getData['image']['thumb'][0] = '';
        } else {
            foreach ($tmp['image'] as $key => $val) {
                // 이미지 사이즈가 없는 경우
                if (empty($val['imageSize']) === true) {
                    $imgConfig = gd_policy('goods.image');
                    $imageSize = $imgConfig['magnify']['size1'];
                } else {
                    $imageSize = $val['imageSize'];
                }
                $getData['image']['magnify'][] = gd_html_preview_image($val['imageName'], $getData['imagePath'], $getData['imageStorage'], $imageSize, 'goods', $getData['goodsNm'], null, false, false);
                $getData['image']['thumb'][] = gd_html_preview_image($val['imageName'], $getData['imagePath'], $getData['imageStorage'], 45, 'goods', $getData['goodsNm'], null, false, true);
            }
        }

        return $getData;
    }*/

    /**
     * 카테고리 별 등록 상품수
     *
     * @param string $cateCd     카테고리 코드
     * @param string $modeFl     only or all (하위 카테고리 포함 여부)
     * @param string $cateType   카테고리 종류 (상품 카테고리, 브랜드 카테고리)
     * @param string $statusMode 모드 ('admin','user')
     *
     * @return array 카테고리별 등로 상품 수
     */
    public function getGoodsLinkCnt($cateCd, $modeFl = 'only', $cateType = 'goods', $statusMode = 'admin')
    {
        gd_isset($this->goodsTable,DB_GOODS);
        $displayConfig = \App::load('\\Component\\Display\\DisplayConfigAdmin');
        $navi = $displayConfig->getDateNaviDisplay();

        $arrBind = [];
        if ($modeFl == 'only') {
            $strWhere = 'gl.cateLinkFl = ? AND gl.cateCd = ?';
            $this->db->bind_param_push($arrBind, 's', 'y');
            $this->db->bind_param_push($arrBind, 's', $cateCd);
            $dataArray = false;
        } else {
            if ($statusMode == 'admin') {
                $strWhere = 'gl.cateCd = ?';
            } else {
                $strWhere = 'gl.cateCd LIKE concat(?,\'%\')';
            }
            $this->db->bind_param_push($arrBind, 's', $cateCd);
            if ($cateType == 'category' || ($cateType == 'brand' && $navi['data']['brand']['linkUse'] != 'y')) {
                $strWhere .= ' AND gl.cateLinkFl = ?';
                $this->db->bind_param_push($arrBind, 's', 'y');
            }
            $dataArray = true;
        }

        if ($cateType == 'goods') {
            $dbTable = DB_GOODS_LINK_CATEGORY;
        } else {
            $dbTable = DB_GOODS_LINK_BRAND;
        }

        if ($statusMode == 'user') {
            $join = ' INNER JOIN '.$this->goodsTable.' g ON gl.goodsNo = g.goodsNo AND g.' . $this->goodsDisplayFl . ' = \'y\' AND g.delFl = \'n\' AND g.applyFl = \'y\' ';
        } else {
            $join = ' INNER JOIN '.$this->goodsTable.' g ON gl.goodsNo = g.goodsNo AND g.delFl = \'n\' ';
        }

        //$strSQL = 'SELECT gl.cateCd, count(gl.cateCd) as cnt FROM ' . $dbTable . ' gl ' . gd_isset($join) . ' WHERE ' . $strWhere . ' GROUP BY gl.cateCd';
        $strSQL = 'SELECT gl.cateCd, count(gl.cateCd) as cnt FROM ' . $dbTable . ' as gl ' . gd_isset($join) . ' WHERE ' . $strWhere . ' GROUP BY gl.cateCd';
        $data = $this->db->query_fetch($strSQL, $arrBind, $dataArray);
        unset($arrBind);

        if (is_null($data) === true) {
            return;
        }

        if ($modeFl == 'only') {
            $getData = $data['cnt'];
        } else {
            foreach ($data as $key => $val) {
                $getData[$val['cateCd']] = $val['cnt'];
            }
        }

        return $getData;
    }

    /**
     * 해당 상품의 총재고량 갱신
     *
     * @param integer $goodsNo 상품 번호
     *
     * @return string 로그 내용
     */
    public function setGoodsStock($goodsNo)
    {
        // 각 옵션의 재고 총합
        $strSQL = "SELECT sum(stockCnt) as totalStock FROM " . DB_GOODS_OPTION . " WHERE goodsNo = ?";
        $this->db->bind_param_push($arrBind, 'i', $goodsNo);
        $getData = $this->db->query_fetch($strSQL, $arrBind, false);
        unset($arrBind);

        // 기존 상품 테이블의 총재고량
        $strSQL = "SELECT totalStock FROM " . DB_GOODS . " WHERE goodsNo = ?";
        $this->db->bind_param_push($arrBind, 'i', $goodsNo);
        $goodsData = $this->db->query_fetch($strSQL, $arrBind, false);
        unset($arrBind);

        // 총재고량 수정
        $strLogData = '';
        if ($getData['totalStock'] != $goodsData['totalStock']) {
            $this->db->bind_param_push($arrBind, 'i', $getData['totalStock']);
            $this->db->bind_param_push($arrBind, 'i', $goodsNo);
            $this->db->setModDtUse(false);
            $this->db->set_update_db(DB_GOODS, 'totalStock = ?', 'goodsNo = ?', $arrBind);
            if($this->goodsDivisionFl) {
                $this->db->setModDtUse(false);
                $this->db->set_update_db(DB_GOODS_SEARCH, 'totalStock = ?', 'goodsNo = ?', $arrBind);
            }
            unset($arrBind);

            // 로그 내용
            $strLogData .= sprintf(__('총재고량 : %1$d개 -> %2$d개 %3$s'), number_format($goodsData['totalStock']), number_format($getData['totalStock']), chr(10));
        }

        return $strLogData;
    }

    /**
     * 상품 아이콘 정보
     *
     * @param string $getData 상품 아이콘 배열 정보
     *
     * @return array 상품 아이콘 정보
     */
    public function getGoodsIcon($getIconCd)
    {
        if (empty($getIconCd)) {
            return false;
        }

        $getIconCd = explode(INT_DIVISION, $getIconCd); // 문자열을 다시 INT_DIVISION로 배열화
        $getIconCd = ArrayUtils::removeEmpty($getIconCd); // 빈 배열 정리
        $getIconCd = array_unique($getIconCd);

        $strSQL = 'SELECT iconCd, iconImage, iconNm FROM ' . DB_MANAGE_GOODS_ICON . ' WHERE iconUseFl = \'y\' AND iconCd IN (\'' . implode('\', \'', $getIconCd) . '\')';
        $result = $this->db->query($strSQL);
        $getData = [];
        while ($data = $this->db->fetch($result)) {
            $getData[$data['iconCd']]['iconImage'] = $data['iconImage'];
            $getData[$data['iconCd']]['iconNm'] = $data['iconNm'];
        }

        return gd_htmlspecialchars_stripslashes(gd_isset($getData));
    }

    /**
     * 위젯용 상품 정보 출력
     *
     * @param string  $getMethod     상품 추출 방법 - 모든 상품(all), 카테고리(category), 상품테마(theme), 이벤트(event), 관련 상품(relation_a,
     *                               relation_m), 상품 번호별 출력(goods)
     * @param string  $extractKey    상품 추출키 (null, 카테고리코드, 상품테마코드, 상품 번호)
     * @param integer $displayCnt    상품 출력 갯수 - 기본 10개
     * @param string  $displayOrder  상품 기본 정렬 - 'sort asc', Category::getSort() 참고
     * @param string  $imageType     이미지 타입 - 기본 'main'
     * @param boolean $optionFl      옵션 출력 여부 - true or false (기본 false)
     * @param boolean $soldOutFl     품절상품 출력 여부 - true or false (기본 true)
     * @param boolean $brandFl       브랜드 출력 여부 - true or false (기본 false)
     * @param boolean $couponPriceFl 쿠폰가격 출력 여부 - true or false (기본 false)
     * @param integer $viewWidthSize 실제 출력할 이미지 사이즈 (기본 null)
     * @param boolean $usePage       paging 사용여부 (타임세일 더보기로 인해 추가)
     * @param integer $goodsNo       관련상품 자동진열시 상세접속한 상품제외 (기본 null)
     *
     * @return array 상품 정보
     */
    public function goodsDataDisplay($getMethod = 'all', $extractKey = null, $displayCnt = 10, $displayOrder = 'sort asc', $imageType = 'main', $optionFl = false, $soldOutFl = true, $brandFl = false, $couponPriceFl = false, $viewWidthSize = null, $usePage = false, $goodsNo = null)
    {
        $mallBySession = SESSION::get(SESSION_GLOBAL_MALL);

        $where = [];
        $join = [];
        $arrBind = [];
        $sortField = '';
        $viewName = "";

        // --- 상품 추출 방법에 따른 처리
        switch ($getMethod) {

            // --- 모든 상품
            case 'all':

                // 정렬 처리
                if ($displayOrder == 'sort asc') {
                    $displayOrder = 'g.goodsNo desc';
                } else if ($displayOrder == 'sort desc') {
                    $displayOrder = 'g.goodsNo asc';
                }

                break;

            // --- 카테고리
            case 'category':

                // 카테고리 코드가 없는 경우 리턴
                if (is_null($extractKey)) {
                    return;
                }

                // 정렬 처리
                if ($displayOrder == 'sort asc') {
                    $displayOrder = 'gl.goodsSort desc';
                } else if ($displayOrder == 'sort desc') {
                    $displayOrder = 'gl.goodsSort asc';
                }

                $this->db->bind_param_push($arrBind, 's', $extractKey);
                $join[] = ' INNER JOIN ' . DB_GOODS_LINK_CATEGORY . ' gl ON g.goodsNo = gl.goodsNo ';
                $where[] = 'gl.cateCd = ?';

                break;

            // --- 브랜드
            case 'brand':

                // 카테고리 코드가 없는 경우 리턴
                if (is_null($extractKey)) {
                    return;
                }

                // 정렬 처리
                if ($displayOrder == 'sort asc') {
                    $displayOrder = 'gl.goodsSort desc';
                } else if ($displayOrder == 'sort desc') {
                    $displayOrder = 'gl.goodsSort asc';
                }

                $this->db->bind_param_push($arrBind, 's', $extractKey);
                $join[] = ' INNER JOIN ' . DB_GOODS_LINK_BRAND . ' gl ON g.goodsNo = gl.goodsNo ';
                $where[] = 'gl.cateCd = ?';

                break;

            // --- 상품테마
            case 'theme':

                // 상품테마 코드가 없는 경우 리턴
                if (is_null($extractKey)) {
                    return;
                }

                // 상품 테마 테이타
                $data = $this->getDisplayThemeInfo($extractKey);

                // 데이타가 없으면 리턴
                if (empty($data['goodsNo'])) {
                    return;
                }

                // 쿼리 생성
                $queryData = $this->setGoodsListQueryForGoodsno($data['goodsNo'], $displayOrder, $displayCnt, $arrBind);
                $sortField = $queryData['sortField'];
                $where[] = $queryData['where'];
                unset($queryData);

                break;

            // --- 관련 상품
            case 'relation_a':
            case 'relation_m':

                // 코드(카테고리 코드 및 상품 코드)가 없는 경우 리턴
                if (is_null($extractKey)) {
                    return;
                }

                $relationMode = explode('_', $getMethod);


                // 자동인 경우
                if ($relationMode[1] == 'a') {

                    // 정렬 설정
                    $displayOrder = 'rand()';

                    // 관련 상품 출력 갯수 체크
                    if (is_null($displayCnt)) {
                        return;
                    }
                    $this->db->bind_param_push($arrBind, 's', $extractKey);
                    $join[] = ' INNER JOIN (SELECT g.goodsNo FROM '.DB_GOODS_LINK_CATEGORY.' glc INNER JOIN '.DB_GOODS.' g ON glc.goodsNo=g.goodsNo AND g.delFl=\'n\' WHERE glc.cateCd=? limit 0,1000) gl ON g.goodsNo = gl.goodsNo ';

                    // 수동인 경우
                } else if ($relationMode[1] == 'm') {

                    // 쿼리 생성
                    $queryData = $this->setGoodsListQueryForGoodsno($extractKey, $displayOrder, $displayCnt, $arrBind);
                    $sortField = $queryData['sortField'];
                    $where[] = $queryData['where'];
                    unset($queryData);
                }

                break;

            // --- 상품 번호별 출력
            case 'goods':

                if ($usePage === true) {
                    $page = \App::load('\\Component\\Page\\Page');
                    $page->page['list'] = $displayCnt; // 페이지당 리스트 수
                    $page->block['cnt'] = !Request::isMobile() ? 10 : 5; // 블록당 리스트 개수
                    $page->setPage();
                    $page->setUrl(\Request::getQueryString());
                }


                // 상품 코드가 없는 경우 리턴
                if (is_null($extractKey)) {
                    return;
                }


                $viewName = "main";

                // 쿼리 생성
                $queryData = $this->setGoodsListQueryForGoodsno($extractKey, $displayOrder, $displayCnt, $arrBind);
                $sortField = gd_isset($queryData['sortField']);
                $where[] = $queryData['where'];
                unset($queryData);

                break;

            // --- 상품 번호별 출력
            case 'event':

                $tmpKey = explode(MARK_DIVISION, $extractKey);

                // 상품 코드가 없는 경우 리턴
                if (is_null($tmpKey[0])) {
                    return;
                }

                $arrGoodsNo = explode(STR_DIVISION, $tmpKey[0]);
                $displayCnt = count($arrGoodsNo);

                // 쿼리 생성
                $queryData = $this->setGoodsListQueryForGoodsno($tmpKey[0], $displayOrder, $displayCnt, $arrBind);
                $sortField = gd_isset($queryData['sortField']);
                $where[] = $queryData['where'];

                if (empty($tmpKey[1]) === false) {
                    $this->db->bind_param_push($arrBind, 's', $tmpKey[1]);
                    $where[] = 'g.cateCd LIKE concat(?,\'%\')';
                }
                if (empty($tmpKey[2]) === false) {
                    $this->db->bind_param_push($arrBind, 's', $tmpKey[2]);
                    $where[] = 'g.brandCd LIKE concat(?,\'%\')';
                }
                unset($queryData);

                break;

            // 그외는 리턴
            default:
                return;
                break;
        }

        // 품절 처리 여부
        if ($soldOutFl === false) {
            $where[] = 'NOT(g.stockFl = \'y\' AND g.totalStock = 0) AND NOT(g.soldOutFl = \'y\')';
        }


        //접근권한 체크
        if (gd_check_login()) {
            $where[] = '(g.goodsAccess !=\'group\'  OR (g.goodsAccess=\'group\' AND FIND_IN_SET(\''.Session::get('member.groupSno').'\', REPLACE(g.goodsAccessGroup,"'.INT_DIVISION.'",","))) OR (g.goodsAccess=\'group\' AND !FIND_IN_SET(\''.Session::get('member.groupSno').'\', REPLACE(g.goodsAccessGroup,"'.INT_DIVISION.'",",")) AND g.goodsAccessDisplayFl =\'y\'))';
        } else {
            $where[] = '(g.goodsAccess=\'all\' OR (g.goodsAccess !=\'all\' AND g.goodsAccessDisplayFl =\'y\'))';
        }

        //성인인증안된경우 노출체크 상품은 노출함
        if (gd_check_adult() === false) {
            $where[]= '(onlyAdultFl = \'n\' OR (onlyAdultFl = \'y\' AND onlyAdultDisplayFl = \'y\'))';
        }


        // 출력 여부
        $where[] = 'g.' . $this->goodsDisplayFl . ' = \'y\'';
        $where[] = 'g.delFl = \'n\'';
        $where[] = 'g.applyFl = \'y\'';
        $where[] = '(UNIX_TIMESTAMP(g.goodsOpenDt) IS NULL  OR UNIX_TIMESTAMP(g.goodsOpenDt) = 0 OR UNIX_TIMESTAMP(g.goodsOpenDt) < UNIX_TIMESTAMP())';

        if(strpos($displayOrder, "goodsNo") === false) $displayOrder = $displayOrder.', goodsNo desc ';
        if(strpos($displayOrder, "soldOut") !== false) $addField = ",( if (g.soldOutFl = 'y' , 'y', if (g.stockFl = 'y' AND g.totalStock <= 0, 'y', 'n') ) ) as soldOut";

        if ($usePage === true) {
            $this->db->strLimit = $page->recode['start'] . ',' . $displayCnt;
        } else {
            if (is_null($displayCnt) === false) {
                $this->db->strLimit = '0, ' . $displayCnt;
            }
        }

        // 상품 데이타 처리
        $this->setGoodsListField(); // 상품 리스트용 필드
        $this->db->strField = $this->goodsListField . gd_isset($addField) . $sortField;
        $this->db->strJoin = implode('', $join);
        $this->db->strWhere = implode(' AND ', $where);
        $this->db->strOrder = $displayOrder;

        if($getMethod =='relation_a') {
            $goodsNo = ($goodsNo) ? $goodsNo : null;
            $getData = $this->getGoodsAutoRelation($goodsNo, null, $arrBind, true, $getMethod);
        } else {
            $getData = $this->getGoodsInfo(null, null, $arrBind, true, $usePage);
        }

        if (empty($getData)) {
            return;
        }
        // 상품 정보 세팅
        if (empty($getData) === false) {
            $this->setGoodsListInfo($getData, $imageType, $optionFl, $couponPriceFl, $viewWidthSize, $viewName,$brandFl);
        }

        return gd_htmlspecialchars_stripslashes($getData);
    }

    /**
     * 상품 코드에 의한 쿼리 생성
     * 위젯용 상품 정보 출력시의 상품 코드로 출력을 하는 경우 쿼리 정보를 생성함
     *
     * @param string  $strGoodsNo   상품 코드
     * @param string  $displayOrder 상품 정렬 방법
     * @param integer $displayCnt   상품 출력 갯수
     * @param array   $arrBind      bind 정보
     *
     * @return array 쿼리 정보
     */
    protected function setGoodsListQueryForGoodsno($strGoodsNo, $displayOrder, & $displayCnt, & $arrBind)
    {
        // goods 배열 처리
        $arrKindCd = explode(INT_DIVISION, $strGoodsNo);

        // 상품 수량
        if (empty($displayCnt)) {
            $displayCnt = count($arrKindCd);
        }

        // 정렬 처리
        $setData['sortField'] = '';
        if ($displayOrder == 'sort asc') {

            // 정렬을 위한 필드 생성
            foreach ($arrKindCd as $key => $val) {
                $tmpField[] = 'WHEN \'' . $val . '\' THEN \'' . sprintf('%05s', $key) . '\'';
            }

            // 정렬 필드
            $setData['sortField'] = ', CASE g.goodsNo ' . implode(' ', $tmpField) . ' ELSE \'\'  END as \'sort\' ';
        } else if ($displayOrder == 'sort desc') {

            // 정렬을 위한 필드 생성
            krsort($arrKindCd);
            foreach ($arrKindCd as $key => $val) {
                $tmpField[] = 'WHEN \'' . $val . '\' THEN \'' . sprintf('%05s', $key) . '\'';
            }

            // 정렬 필드
            $setData['sortField'] = ', CASE g.goodsNo ' . implode(' ', $tmpField) . ' ELSE \'\'  END as \'sort\' ';
        }

        // bind 처리
        foreach ($arrKindCd as $key => $val) {
            $this->db->bind_param_push($arrBind, 'i', $val);
            $param[] = '?';
        }

        $setData['where'] = 'g.goodsNo IN (' . implode(',', $param) . ')';

        return $setData;
    }


    /**
     * 오늘본 상품 쿠키 생성
     *
     * @param $goodsNo 상품코드
     */
    public function getTodayViewedGoods($goodsNo)
    {
        // 상품 코드 여부 체크
        if (empty($goodsNo)) {
            return;
        }

        // --- 최근 본 상품 설정 config 불러오기
        $policy = gd_policy('goods.today');
        $mallBySession = SESSION::get(SESSION_GLOBAL_MALL);
        if($mallBySession) {
            $todayCookieName = 'todayGoodsNo'.$mallBySession['sno'];
        } else {
            $todayCookieName = 'todayGoodsNo';
        }

        // 설정 시간이 없거나 최대수량이 없는 경우 사용안함
        if (empty($policy['todayHour']) || empty($policy['todayCnt'])) {
            if (Cookie::has($todayCookieName)) {
                Cookie::set($todayCookieName, '', time() - 42000, '/');
            }

            return;
        }

        $eventConfig = \App::getConfig('event')->toArray();
        if ($eventConfig['goodsViewStatistics'] === 'y') {
            // 상품보기통계 저장 안함
        } else {
            $this->goodsViewStatistics($goodsNo);
        }

        // 오늘 본 상품의 쿠키가 존재하는 경우
        if (Cookie::has($todayCookieName)) {
            // 쿠키값을 json_decode 해서 배열로 만듬
            $arrTodayGoodsNo = json_decode(Cookie::get($todayCookieName));


            // 현재 goodsNo 값이 오늘본 상품 배열에 존재하는 경우 해당 배열에서 제외 함
            if (in_array($goodsNo, $arrTodayGoodsNo)) {
                $key = array_search($goodsNo, $arrTodayGoodsNo);
                array_splice($arrTodayGoodsNo, $key, 1);
            } else {
                if ($eventConfig['goodsHitsStatistics'] === 'y') {
                    // 상품hits통계 저장 안함
                } else {
                    //상품 view 카운트
                    $arrBind = [];

                    $this->db->bind_param_push($arrBind, 'i', $goodsNo);
                    $this->db->setModDtUse(false);
                    $this->db->set_update_db(DB_GOODS, "hitCnt = hitCnt + 1", 'goodsNo = ?', $arrBind);
                    if ($this->goodsDivisionFl) {
                        $this->db->setModDtUse(false);
                        $this->db->set_update_db(DB_GOODS_SEARCH, "hitCnt = hitCnt + 1", 'goodsNo = ?', $arrBind);
                    }
                    unset($arrBind);
                }

            }
            // 오늘 본 상품의 쿠키가 존재하지 않는 경우 빈배열 처리
        } else {
            if ($eventConfig['goodsHitsStatistics'] === 'y') {
                // 상품hits통계 저장 안함
            } else {
                $arrTodayGoodsNo = [];

                //상품 view 카운트
                $arrBind = [];

                $this->db->bind_param_push($arrBind, 'i', $goodsNo);
                $this->db->setModDtUse(false);
                $this->db->set_update_db(DB_GOODS, "hitCnt = hitCnt + 1", 'goodsNo = ?', $arrBind);
                if ($this->goodsDivisionFl) {
                    $this->db->setModDtUse(false);
                    $this->db->set_update_db(DB_GOODS_SEARCH, "hitCnt = hitCnt + 1", 'goodsNo = ?', $arrBind);
                }
                unset($arrBind);
            }
        }

        // 현재 goodsNo 값을 오늘본 상품 배열의 첫번째에 위치함
        array_unshift($arrTodayGoodsNo, $goodsNo);

        // 최대 갯수 이상인 경우 그 이상은 삭제
        array_splice($arrTodayGoodsNo, $policy['todayCnt']);

        // 오늘본 상품 배열을 json_encode 처리함
        $arrTodayGoodsNo = json_encode($arrTodayGoodsNo);

        // 쿠키 생성을함
        Cookie::set($todayCookieName, $arrTodayGoodsNo, 3600 * $policy['todayHour'], '/');
    }

    /**
     * 최근 본 상품 쿠키 삭제
     *
     * @param $goodsNo 상품코드
     */
    public function removeTodayViewedGoods($goodsNo)
    {
        // 상품 코드 여부 체크
        if (empty($goodsNo)) {
            return;
        }

        // --- 최근 본 상품 설정 config 불러오기
        $policy = gd_policy('goods.today');

        $mallBySession = SESSION::get(SESSION_GLOBAL_MALL);
        if($mallBySession) {
            $todayCookieName = 'todayGoodsNo'.$mallBySession['sno'];
        } else {
            $todayCookieName = 'todayGoodsNo';
        }


        // 설정 시간이 없거나 최대수량이 없는 경우 사용안함
        if (empty($policy['todayHour']) || empty($policy['todayCnt'])) {
            if (Cookie::has($todayCookieName)) {
                Cookie::set($todayCookieName, '', time() - 42000, '/');
            }

            return;
        }

        // 오늘 본 상품의 쿠키가 존재하는 경우
        if (Cookie::has($todayCookieName)) {

            // 쿠키값을 json_decode 해서 배열로 만듬
            $arrTodayGoodsNo = json_decode(Cookie::get($todayCookieName));

            // 현재 goodsNo 값이 오늘본 상품 배열에 존재하는 경우 해당 배열에서 제외 함
            if (in_array($goodsNo, $arrTodayGoodsNo)) {
                $key = array_search($goodsNo, $arrTodayGoodsNo);
                array_splice($arrTodayGoodsNo, $key, 1);
            }
            // 오늘 본 상품의 쿠키가 존재하지 않는 경우 빈배열 처리
        } else {
            $arrTodayGoodsNo = [];
        }


        // 최대 갯수 이상인 경우 그 이상은 삭제
        array_splice($arrTodayGoodsNo, $policy['todayCnt']);

        // 오늘본 상품 배열을 json_encode 처리함
        $arrTodayGoodsNo = json_encode($arrTodayGoodsNo);

        // 쿠키 생성을함
        Cookie::set($todayCookieName, $arrTodayGoodsNo, time() + 3600 * $policy['todayHour'], '/');

        return true;
    }

    /**
     * 최근 검색어 쿠키 생성
     *
     * @param string $keyword 키워드
     * @param int    $maxCount
     */
    public function getRecentKeywordSearch($keyword)
    {
        // 키워드 여부 체크
        if (empty($keyword)) {
            return;
        }

        // 키워드에 검색일자 추가
        $setKeyword = $keyword . STR_DIVISION . date('Y.m.d');

        $recentKeyword = 'recentKeyword';
        if (Request::isMobile()) $recentKeyword .= 'Mobile';

        // 최근 검색 키워드의 쿠키가 존재하는 경우
        if (Cookie::has($recentKeyword)) {
            // 쿠키값을 json_decode 해서 배열로 만듬
            $arrRecentKeyword = json_decode(Cookie::get($recentKeyword));

            // 해당키워드 배열의 키/값 삭제
            foreach ($arrRecentKeyword as $key => $val) {
                if ($keyword == substr($val, 0, stripos($val, STR_DIVISION))) {
                    unset($arrRecentKeyword[$key]);
                }
            }
        } else {
            $arrRecentKeyword = [];
        }

        // 현재 키워드 값을 오늘본 상품 배열의 첫번째에 위치함
        array_unshift($arrRecentKeyword, $setKeyword);

        // 최대 갯수 이상인 경우 그 이상은 삭제
        array_splice($arrRecentKeyword, self::RECENT_KEYWORD_MAX_COUNT);

        // 최근 검색 키워드 배열을 json_encode 처리함
        $arrRecentKeyword = json_encode($arrRecentKeyword);

        // 쿠키 생성
        Cookie::set($recentKeyword, $arrRecentKeyword, time() + 86400 * 365, '/');
    }

    /**
     * 최근검색어 쿠키 삭제
     *
     * @return bool|void
     * @param string $keyword 최근검색어
     */
    public function removeRecentKeyword($keyword)
    {
        // 상품 코드 여부 체크
        if (empty($keyword)) {
            return;
        }

        $recentKeyword = 'recentKeyword';
        if (Request::isMobile()) $recentKeyword .= 'Mobile';

        // 오늘 본 상품의 쿠키가 존재하는 경우
        if (Cookie::has($recentKeyword)) {

            // 쿠키값을 json_decode 해서 배열로 만듬
            $arrRecentKeyword = json_decode(Cookie::get($recentKeyword));

            // 해당키워드 배열의 키/값 삭제
            foreach ($arrRecentKeyword as $key => $val) {
                if ($keyword == substr($val, 0, stripos($val, STR_DIVISION))) {
                    array_splice($arrRecentKeyword, $key, 1);
                }
            }
        } else {
            // 오늘 본 상품의 쿠키가 존재하지 않는 경우 빈배열 처리
            $arrRecentKeyword = [];
        }

        // 최대 갯수 이상인 경우 그 이상은 삭제
        array_splice($arrRecentKeyword, self::RECENT_KEYWORD_MAX_COUNT);

        // 오늘본 상품 배열을 json_encode 처리함
        $arrRecentKeyword = json_encode($arrRecentKeyword);

        // 쿠키 생성을함
        Cookie::set($recentKeyword, $arrRecentKeyword, time() + 86400 * 365, '/');

        return true;
    }

    /**
     * 최근검색어 쿠키 전체삭제
     *
     * @return bool|void
     * @param string $keyword 최근검색어
     */
    public function removeRecentAllKeyword()
    {
        $recentKeyword = 'recentKeyword';
        if (Request::isMobile()) $recentKeyword .= 'Mobile';
        Cookie::del($recentKeyword);

        return true;
    }

    /**
     * getGoodsStateList
     *
     * @return array
     * @deprecated
     * @use \Component\Goods\DefineGoods::$goodsStateList
     */
    public function getGoodsStateList()
    {

        return $this->goodsStateList;
    }

    /**
     * getGoodsImportType
     *
     * @return array
     * @deprecated
     * @use \Component\Goods\DefineGoods::$goodsImportType
     */
    public function getGoodsImportType()
    {

        return $this->goodsImportType;
    }
    /**
     * getGoodsSellType
     *
     * @return array
     * @deprecated
     * @use \Component\Goods\DefineGoods::$goodsSellType
     */
    public function getGoodsSellType()
    {

        return $this->goodsSellType;
    }
    /**
     * getGoodsAgeType
     *
     * @return array
     * @deprecated
     * @use \Component\Goods\DefineGoods::$goodsAgeType
     */
    public function getGoodsAgeType()
    {

        return $this->goodsAgeType;
    }

    /**
     * getGoodsGenderType
     *
     * @return array
     * @deprecated
     * @use \Component\Goods\DefineGoods::$goodsGenderType
     */
    public function getGoodsGenderType()
    {

        return $this->goodsGenderType;
    }

    /**
     * getGoodsPermissionList
     *
     * @return array
     * @deprecated
     * @use \Component\Goods\DefineGoods::$goodsPermissionList
     */
    public function getGoodsPermissionList()
    {

        return $this->goodsPermissionList;
    }

    /**
     * getfixedSales
     *
     * @return array
     * @deprecated
     * @use \Component\Goods\DefineGoods::$fixedSales
     */
    public function getFixedSales()
    {

        return $this->fixedSales;
    }

    /**
     * getFixedOrderCnt
     *
     * @return array
     * @deprecated
     * @use \Component\Goods\DefineGoods::$fixedOrderCnt
     */
    public function getFixedOrderCnt()
    {

        return $this->fixedOrderCnt;
    }


    public function getGoodsColorList($isAdmin = false)
    {
        $strSQL = "SELECT itemCd,itemNm FROM " . DB_CODE . " WHERE groupCd = ? AND useFl = ? ORDER BY sort ASC";
        $arrBind = ['ss', '05001','y'];
        $tmpGoodsColor = $this->db->slave()->query_fetch($strSQL, $arrBind);

        if($isAdmin) {
            foreach($tmpGoodsColor as $k => $v) {
                $tmpValue = explode(STR_DIVISION,$v['itemNm']);
                $goodsColor[$tmpValue[0]] = str_replace("#","",$tmpValue[1]);
            }
        } else {
            foreach($tmpGoodsColor as $k => $v) {
                $goodsColor[$v['itemCd']] = str_replace("#","",explode(STR_DIVISION,$v['itemNm'])[1]);
            }
        }

        return $goodsColor;
    }

    public function getGoodsPayLimit()
    {

        return $this->goodsPayLimit;

    }

    /**
     * getGoodsGenderType
     *
     * @return array
     *
     */
    public function getHscode()
    {

        return $this->hscode;
    }

    /**
     * KC인증 구분 값
     *
     * @return array
     * @use \Component\Goods\DefineGoods::$kcmarkCode
     */
    public function getKcmarkCode()
    {
        return $this->kcmarkCode;
    }

    /**
     * setRevicwCount
     *
     */
    public function setRevicwCount($goodsNo, $decreaseFl = false)
    {
        $arrBind = [];
        $this->db->bind_param_push($arrBind, 'i', $goodsNo);
        if ($decreaseFl) {
            $this->db->setModDtUse(false);
            $this->db->set_update_db(DB_GOODS, "reviewCnt = reviewCnt - 1", 'goodsNo = ?', $arrBind);
            if($this->goodsDivisionFl) {
                $this->db->setModDtUse(false);
                $this->db->set_update_db(DB_GOODS_SEARCH, "reviewCnt = reviewCnt - 1", 'goodsNo = ?', $arrBind);
            }
        } else {
            $this->db->setModDtUse(false);
            $this->db->set_update_db(DB_GOODS, "reviewCnt = reviewCnt + 1", 'goodsNo = ?', $arrBind);
            if($this->goodsDivisionFl) {
                $this->db->setModDtUse(false);
                $this->db->set_update_db(DB_GOODS_SEARCH, "reviewCnt = reviewCnt + 1", 'goodsNo = ?', $arrBind);
            }
        }
        unset($arrBind);
    }


    /**
     * setOrderCount
     *
     */
    public function setOrderCount($orderSno, $decreaseFl = false,$orderCnt = 1)
    {

        $strWhere = "sno IN ('" . implode("','", $orderSno) . "')";
        $strSQL = 'SELECT goodsNo FROM ' . DB_ORDER_GOODS . ' WHERE ' . $strWhere;
        $result = $this->db->query_fetch($strSQL);
        foreach ($result as $k => $v) {
            $goodsNo[] = $v['goodsNo'];
        }

        $strWhere = "goodsNo IN ('" . implode("','", $goodsNo) . "')";
        if ($decreaseFl) {
            $this->db->setModDtUse(false);
            $this->db->set_update_db(DB_GOODS, "orderCnt = orderCnt - ".$orderCnt, $strWhere);
            if($this->goodsDivisionFl) {
                $this->db->setModDtUse(false);
                $this->db->set_update_db(DB_GOODS_SEARCH, "orderCnt = orderCnt - " . $orderCnt, $strWhere);
            }
        } else {
            $this->db->setModDtUse(false);
            $this->db->set_update_db(DB_GOODS, "orderCnt = orderCnt + ".$orderCnt, $strWhere);
            if($this->goodsDivisionFl) {
                $this->db->setModDtUse(false);
                $this->db->set_update_db(DB_GOODS_SEARCH, "orderCnt = orderCnt + " . $orderCnt, $strWhere);
            }
        }
    }
    /**
     * setOrderGoodsCount
     * 상품의 주문상품갯수 - 관리자 상품리스트 조회용
     * @param int $orderSno 주문번호
     * @param boolean $decreaseFl 증가 차감
     * @param int $goodsNo 상품번호
     * @param int $orderGoodsCnt 주문상품개수
     * @return boolean
     */
    public function setOrderGoodsCount($orderSno, $decreaseFl = false, $goodsNo = 0, $orderGoodsCnt = 0)
    {
        // 상품 번호 및 카운트가 있을 경우 조회 안함
        if($goodsNo && $orderGoodsCnt) {
            $arrBind = [];
            $this->db->bind_param_push($arrBind, 'i', $goodsNo);
            if ($decreaseFl) {
                $this->db->setModDtUse(false);
                $this->db->set_update_db(DB_GOODS, "orderGoodsCnt = orderGoodsCnt - " . $orderGoodsCnt, 'goodsNo = ?', $arrBind);
                if ($this->goodsDivisionFl) {
                    $this->db->setModDtUse(false);
                    $this->db->set_update_db(DB_GOODS_SEARCH, "orderGoodsCnt = orderGoodsCnt - " . $orderGoodsCnt, 'goodsNo = ? AND orderGoodsCnt >= ' . $orderGoodsCnt, $arrBind);
                }
            } else {
                $this->db->setModDtUse(false);
                $this->db->set_update_db(DB_GOODS, "orderGoodsCnt = orderGoodsCnt + " . $orderGoodsCnt, 'goodsNo = ?', $arrBind);
                if ($this->goodsDivisionFl) {
                    $this->db->setModDtUse(false);
                    $this->db->set_update_db(DB_GOODS_SEARCH, "orderGoodsCnt = orderGoodsCnt + " . $orderGoodsCnt, 'goodsNo = ? AND orderGoodsCnt >= ' . $orderGoodsCnt, $arrBind);
                }
            }
            unset($arrBind);
        } else { // 상품 번호 및 카운트가 없을 경우 주문상품테이블 조회
            // $orderSno인자가 배열 값인 경우
            if (is_array($orderSno) == true) {
                $orderSno = implode("','", $orderSno);
                $strWhere = "sno IN ('" . $orderSno . "')";
            }
            else {
                $strWhere = "sno = " . $orderSno;
            }
            // 주문상품 조회
            $strSQL = 'SELECT goodsNo, goodsCnt FROM ' . DB_ORDER_GOODS . ' WHERE ' . $strWhere;
            $getData = $this->db->query_fetch($strSQL);
            // 상품번호 상품갯수에 맞춰서 es_goods.orderGoodsCnt 계산
            foreach ($getData as $k => $v) {
                // 인자cnt가 있을 경우 조회데이터가 아닌 인자 cnt 삽입
                if ($orderGoodsCnt > 0) $v['goodsCnt'] = $orderGoodsCnt;
                $arrBind = [];
                $this->db->bind_param_push($arrBind, 's', $v['goodsNo']);
                if ($decreaseFl) {
                    $this->db->setModDtUse(false);
                    $this->db->set_update_db(DB_GOODS, "orderGoodsCnt = orderGoodsCnt - " . $v['goodsCnt'], 'goodsNo = ? AND orderGoodsCnt >= ' . $v['goodsCnt'], $arrBind);
                    if ($this->goodsDivisionFl) {
                        $this->db->setModDtUse(false);
                        $this->db->set_update_db(DB_GOODS_SEARCH, "orderGoodsCnt = orderGoodsCnt - " . $v['goodsCnt'], 'goodsNo = ? AND orderGoodsCnt >= ' . $v['goodsCnt'], $arrBind);
                    }
                } else {
                    $this->db->setModDtUse(false);
                    $this->db->set_update_db(DB_GOODS, "orderGoodsCnt = orderGoodsCnt + " . $v['goodsCnt'], 'goodsNo = ?', $arrBind);
                    if ($this->goodsDivisionFl) {
                        $this->db->setModDtUse(false);
                        $this->db->set_update_db(DB_GOODS_SEARCH, "orderGoodsCnt = orderGoodsCnt + " . $v['goodsCnt'], 'goodsNo = ?', $arrBind);
                    }
                }
                unset($arrBind);
            }
        }
        return true;
    }

    /**
     * setCartCount
     * 상품의 장바구니 / 관심상품 상품갯수 - 관리자 상품리스트 조회용
     * 장바구니 담기, 변경, 삭제, 찜하기 사용 - 담은 카운트 값을 삽입
     * @param string $mode cart/wish 분기처리
     * @param int $goodsNo 상품번호
     * @return boolean
     */
    public function setCartWishCount($mode, $goodsNo)
    {
        $eventConfig = \App::getConfig('event')->toArray();
        if ($eventConfig['goodsCartCntStatistics'] === 'y') {
            // 상품 장바구니 수량 통계 저장 안함
            return true;
        }
        // 장바구니 관심상품 분기
        if($mode == 'cart') {
            $tableName = DB_CART;
            $cntName = 'cartCnt';
        } else {
            $tableName = DB_WISH;
            $cntName = 'wishCnt';
        }
        if($goodsNo) {
            // 장바구니 상품갯수 추출 goodsNo 기준
            $arrBind = [];
            $strSQL = 'SELECT goodsNo, sum(goodsCnt) as goodsCnt FROM ' . $tableName . ' WHERE goodsNo = ? ';
            $this->db->bind_param_push($arrBind, 'i', $goodsNo);
            $getData = $this->db->query_fetch($strSQL, $arrBind, false);

            // 상품 갯수가 없을 경우 0
            if(!$getData['goodsCnt']) $getData['goodsCnt'] = 0;

            // 상품 장바구니갯수 처리시작
            $this->db->setModDtUse(false);
            $this->db->set_update_db(DB_GOODS, $cntName ." =  " . $getData['goodsCnt'], 'goodsNo = ?', $arrBind);
            if ($this->goodsDivisionFl) {
                $this->db->setModDtUse(false);
                $this->db->set_update_db(DB_GOODS_SEARCH, $cntName ." = " . $getData['goodsCnt'], 'goodsNo = ?', $arrBind);
            }
            return true;
        }
    }


    public function getOptionValuesByIndex($goodsNo, $index){
        $strSQL = "SELECT sno, goodsNo ,optionValue".$index." as optionValue   FROM " . DB_GOODS_OPTION . " WHERE goodsNo = ? GROUP BY optionValue".$index."  ORDER BY optionNo ASC, sno ASC";
        $arrBind = [];
        $this->db->bind_param_push($arrBind, 'i', $goodsNo);
        $getData = $this->db->query_fetch($strSQL, $arrBind);
        $values = [];
        foreach($getData as $val) {
            $values[] = $val['optionValue'];
        }

        return $values;
    }

    /**
     * 최초 / 최종 등록한 상품의 goodsNo 값 추출
     *
     * @param string $extractMode 추출 방법 (first, last)
     * @param boolean $mobileFl 모바일샵 여부
     * @return int $goodsNo 상품번호
     */
    public function getGoodsNoExtract($extractMode, $mobileFl = false)
    {
        // 추출 방법에 따른
        if ($extractMode === 'first') {
            $orderByStr = 'ASC';
        } else {
            $orderByStr = 'DESC';
        }

        // 모바일샵 여부에 따른
        if ($mobileFl === true) {
            $this->goodsDisplayFl = 'goodsDisplayMobileFl';
            $this->goodsSellFl = 'goodsSellMobileFl';
        }

        $strSQL = "SELECT goodsNo FROM " . DB_GOODS . "
            WHERE " . $this->goodsDisplayFl . " = 'y' AND " . $this->goodsSellFl . " = 'y' AND delFl = 'n' AND applyFl = 'y'
            ORDER BY goodsNo " . $orderByStr . "
            LIMIT 0,1";
        $getData = $this->db->query_fetch($strSQL, null, false);

        // 결과에 따른 리턴
        if (empty($getData['goodsNo']) === true) {
            return DEFAULT_CODE_GOODSNO;
        } else {
            return $getData['goodsNo'];
        }
    }


    public function getMemberDcFlInfo($goodsData,$groupSno)
    {
        if(empty($groupSno) === true) $groupSno = Session::get('member.groupSno');

        if(empty($this->memberGroupInfo[$groupSno]) === true) {
            $memberGroup = \App::Load(\Component\Member\MemberGroup::class);
            $groupData = $memberGroup->getGroup($groupSno);

            $this->memberGroupInfo[$groupSno] = $groupData;

        } else {
            $groupData = $this->memberGroupInfo[$groupSno];
        }

        $groupData = gd_array_json_decode(
            gd_htmlspecialchars_stripslashes($groupData),
            [
                'fixedRateOption',
                'dcExOption',
                'dcExScm',
                'dcExCategory',
                'dcExBrand',
                'dcExGoods',
                'overlapDcOption',
                'overlapDcScm',
                'overlapDcCategory',
                'overlapDcBrand',
                'overlapDcGoods',
            ]
        );

        //회원등급 > 브랜드별 추가할인 상품 브랜드 정보
        if ($groupData['fixedOrderTypeDc'] == 'brand') {
            $dcBrandInfo = json_decode($groupData['dcBrandInfo']);
        }

        if (count($dcBrandInfo) > 0 || $groupData['dc' . ucwords($groupData['dcType'])] > 0) {
            // 회원 추가 할인 적용
            $goodsData['addDcFl'] = true;

            // 추가 할인 적용제외 - SCM
            if ($goodsData['addDcFl'] === true && empty($groupData['dcExOption']) === false && in_array('scm', $groupData['dcExOption']) === true) {
                if (empty($groupData['dcExScm']) === false && in_array($goodsData['scmNo'], $groupData['dcExScm']) === true) {
                    $goodsData['addDcFl'] = false;
                }
            }

            // 추가 할인 적용제외 - 카테고리 (대표 카테고리만 확인후에 아래에서 현재 카테고리 전부를 확인 처리 함)
            if ($goodsData['addDcFl'] === true && empty($groupData['dcExOption']) === false && in_array('category', $groupData['dcExOption']) === true && empty($groupData['dcExCategory']) === false) {
                if (in_array($goodsData['cateCd'], $groupData['dcExCategory']) === true) {
                    $goodsData['addDcFl'] = false;
                } else {
                    $memberDc['dc_category'][] = $goodsData['goodsNo'];
                    $memberDc['dc_category'] = array_unique($memberDc['dc_category']);
                }
            }

            // 추가 할인 적용제외 - 브랜드
            if ($goodsData['addDcFl'] === true && empty($groupData['dcExOption']) === false && in_array('brand', $groupData['dcExOption']) === true) {
                if (empty($groupData['dcExBrand']) === false && in_array($goodsData['brandCd'], $groupData['dcExBrand']) === true) {
                    $goodsData['addDcFl'] = false;
                }
            }

            // 추가 할인 적용제외 - 상품
            if ($goodsData['addDcFl'] === true && empty($groupData['dcExOption']) === false && in_array('goods', $groupData['dcExOption']) === true) {
                if (empty($groupData['dcExGoods']) === false && in_array($goodsData['goodsNo'], $groupData['dcExGoods']) === true) {
                    $goodsData['addDcFl'] = false;
                }
            }

            // 회원 추가 할인 적용으로 된 경우 회원 추가 할인 사용
            if ($goodsData['addDcFl'] === true) {
                // 회원 추가 할인 여부
                $memberDc['dc'] = true;
            }
        } else {
            $goodsData['addDcFl'] = false;
        }

        // 회원 중복 할인 여부 설정 (적용 대상이 있어야만 중복 할인 적용)
        if ($groupData['overlapDc' . ucwords($groupData['overlapDcType'])] > 0 && empty($groupData['overlapDcOption']) === false) {
            // 회원 중복 할인 적용 제외
            $goodsData['overlapDcFl'] = false;

            // 중복 할인 적용제외 - SCM
            if ($goodsData['overlapDcFl'] === false && in_array('scm', $groupData['overlapDcOption']) === true) {
                if (empty($groupData['overlapDcScm']) === false && in_array($goodsData['scmNo'], $groupData['overlapDcScm']) === true) {
                    $goodsData['overlapDcFl'] = true;
                }
            }

            // 중복 할인 적용제외 - 카테고리 (대표 카테고리만 확인후에 아래에서 현재 카테고리 전부를 확인 처리 함)
            if ($goodsData['overlapDcFl'] === false && in_array('category', $groupData['overlapDcOption']) === true && empty($groupData['overlapDcCategory']) === false) {
                if (in_array($goodsData['cateCd'], $groupData['overlapDcCategory']) === true) {
                    $goodsData['overlapDcFl'] = true;
                } else {
                    $memberDc['overlap_category'][] = $goodsData['goodsNo'];
                    $memberDc['overlap_category'] = array_unique($memberDc['overlap_category']);
                }
            }

            // 중복 할인 적용제외 - 브랜드
            if ($goodsData['overlapDcFl'] === false && in_array('brand', $groupData['overlapDcOption']) === true) {
                if (empty($groupData['overlapDcBrand']) === false && in_array($goodsData['brandCd'], $groupData['overlapDcBrand']) === true) {
                    $goodsData['overlapDcFl'] = true;
                }
            }

            // 중복 할인 적용제외 - 상품
            if ($goodsData['overlapDcFl'] === false && in_array('goods', $groupData['overlapDcOption']) === true) {
                if (empty($groupData['overlapDcGoods']) === false && in_array($goodsData['goodsNo'], $groupData['overlapDcGoods']) === true) {
                    $goodsData['overlapDcFl'] = true;
                }
            }

            // 회원 중복 할인 적용으로 된 경우 회원 중복 할인 사용
            if ($goodsData['overlapDcFl'] === true) {
                $memberDc['overlap'] = true;
            }
        } else {
            $goodsData['overlapDcFl'] = false;
        }

        // 회원 그룹별 추가 할인과 중복 할인
        $memberDcPrice = 0;
        $memberOverlapDcPrice = 0;

        // 회원그룹 추가 할인과 중복 할인 계산할 기준 금액 처리
        $tmp['memberDcByPrice'] = $goodsData['goodsPrice'];


        // 절사 내용
        $tmp['trunc'] = Globals::get('gTrunc.member_group');

        // 회원 등급별 추가 할인 체크
        if ($goodsData['addDcFl'] === true && empty($arrCateCd[$goodsData['goodsNo']]) === false) {
            // 해당 상품이 연결된 카테고리 체크
            foreach ($arrCateCd[$goodsData['goodsNo']] as $gVal) {
                if (isset($groupData['dcExCategory']) && in_array($gVal, $groupData['dcExCategory'])) {
                    $goodsData['addDcFl'] = false;
                }
            }
        }

        // 금액 체크
        if ($goodsData['addDcFl'] === true && $tmp['memberDcByPrice'] < $groupData['dcLine']) {
            $goodsData['addDcFl'] = false;
        }

        // 회원 등급별 중복 할인 체크
        if ($goodsData['overlapDcFl'] === false && empty($arrCateCd[$goodsData['goodsNo']]) === false) {
            // 해당 상품이 연결된 카테고리 체크
            foreach ($arrCateCd[$goodsData['goodsNo']] as $gVal) {
                if (isset($groupData['overlapDcCategory']) && in_array($gVal, $groupData['overlapDcCategory'])) {
                    $goodsData['overlapDcFl'] = true;
                }
            }
        }

        // 금액 체크
        if ($goodsData['overlapDcFl'] === true && $tmp['memberDcByPrice'] < $groupData['overlapDcLine']) {
            $goodsData['overlapDcFl'] = false;
        }

        // 회원그룹 추가 할인
        if ($goodsData['addDcFl'] === true) {
            if ($groupData['dcType'] === 'percent') {
                if ($groupData['fixedOrderTypeDc'] == 'brand') {
                    if (in_array($goodsData['brandCd'], $dcBrandInfo->cateCd)) {
                        $goodsBrandInfo[$goodsData['goodsNo']][$goodsData['brandCd']] = $goodsData['brandCd'];
                    } else {
                        if ($goodsData['brandCd']) {
                            $goodsBrandInfo[$goodsData['goodsNo']]['allBrand'] = $goodsData['brandCd'];
                        } else {
                            $goodsBrandInfo[$goodsData['goodsNo']]['noBrand'] = $goodsData['brandCd'];
                        }
                    }

                    // 무통장결제 중복 할인 설정 체크에 따른 할인율
                    foreach ($goodsBrandInfo[$goodsData['goodsNo']] as $gKey => $gVal) {
                        foreach ($dcBrandInfo->cateCd AS $mKey => $mVal) {
                            if ($gKey == $mVal) {
                                $groupData['dcPercent'] = $dcBrandInfo->goodsDiscount[$mKey];
                            }
                        }
                    }
                } else {
                    $groupData['dcPercent'] = $groupData['dcPercent'];
                }


                $memberDcPercent = $groupData['dcPercent'] / 100;
                $memberDcPrice = gd_number_figure($tmp['memberDcByPrice'] * $memberDcPercent, $tmp['trunc']['unitPrecision'], $tmp['trunc']['unitRound']);
            } else {
                $memberDcPrice = $groupData['dcPrice'];
            }
        }

        // 회원그룹 중복 할인
        if ($goodsData['overlapDcFl'] === true) {
            if ($groupData['dcType'] === 'percent') {
                $memberDcPercent = $groupData['overlapDcPercent'] / 100;
                $memberOverlapDcPrice = gd_number_figure($tmp['memberDcByPrice'] * $memberDcPercent, $tmp['trunc']['unitPrecision'], $tmp['trunc']['unitRound']);
            } else {
                $memberOverlapDcPrice = $groupData['overlapDcPrice'];
            }
        }

        $setData['addDcFl'] = $goodsData['addDcFl'];
        $setData['overlapDcFl'] = $goodsData['overlapDcFl'];
        $setData['memberDcPrice'] = $memberDcPrice;
        $setData['memberOverlapDcPrice'] = $memberOverlapDcPrice;

        if($goodsData['goodsPrice'] - $memberDcPrice  - $memberOverlapDcPrice <= 0 ) return 0;
        else return $goodsData['goodsPrice'] - $memberDcPrice  - $memberOverlapDcPrice ;

    }

    /**
     *글로벌 상품 출력
     *
     * @param string $goodsNo     상품코드
     * @param string $mallSno     몰번호
     * @param string $debug      query문을 출력, true 인 경우 결과를 return 과 동시에 query 출력 (기본 false)
     *
     * @return array 상품 정보
     */
    public function getDataGoodsGlobal($goodsNo,$mallSno = null)
    {
        $whereArr[] = " goodsNo = '" . $goodsNo . "' ";
        if($mallSno) $whereArr[] = " mall = '" . $mallSno . "' ";

        if (count($whereArr) > 0) {
            $whereStr = " WHERE " . implode(' AND ', $whereArr);
        }

        $arrField = DBTableField::setTableField('tableGoodsGlobal',null,['goodsNo']);
        $strSQL = 'SELECT ' . implode(', ', $arrField) . ' FROM ' . DB_GOODS_GLOBAL . $whereStr;

        $getData = $this->db->query_fetch($strSQL);

        return gd_htmlspecialchars_stripslashes($getData);
    }

    public function relationConfigMobileSetting()
    {
        $relationConfig = gd_policy('display.relation'); // 관련상품설정
        $relationConfigMobileDefault = [
            'mobileImageCd' => 'main',
            'mobileLineCnt' => '2',
            'mobileRowCnt' => '2',
            'mobileSoldOutFl' => 'y',
            'mobileSoldOutDisplayFl' => 'y',
            'mobileSoldOutIconFl' => 'y',
            'mobileIconFl' => 'y',
            'mobileDisplayField' => ['img', 'goodsNm'],
            'mobileRelationLinkFl' => 'blank',
            'mobileDisplayType' => '01',
            'mobileDetailSet' => '',
            'mobileGoodsDiscount' => ['goods'],
            'mobilePriceStrike' => ['fixedPrice'],
            'mobileDisplayAddField' => [],

        ];
        foreach ($relationConfigMobileDefault as $key => $value) {
            $defaultKey = lcfirst(str_replace('mobile', '', $key));
            if (in_array($key, array_keys($relationConfig)) === false) gd_isset($relationConfig[$key], $value);
            $relationConfig[$defaultKey] = $relationConfig[$key];
            unset($relationConfig[$key]);
        }

        return $relationConfig;
    }

    /**
     * 상품 아이콘 리스트
     *
     * @return array 이미지가 존재하는 상품 아이콘 리스트
     */
    public function getIconSearchList()
    {
        $sort['field'] = 'iconPeriodFl';

        if (is_null($this->db->strField)) {
            $arrField = DBTableField::setTableField('tableManageGoodsIcon');
            $this->db->strField = 'sno, ' . implode(', ', $arrField);
        }

        if (empty($this->arrBind)) {
            $this->arrBind = null;
        }

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MANAGE_GOODS_ICON . implode(' ', $query) . ' ORDER BY iconPeriodFl ASC';
        $data = $this->db->query_fetch($strSQL, $this->arrBind);
        $iconList = gd_htmlspecialchars_stripslashes(gd_isset($data));

        if (is_array($iconList) === false) return;
        foreach ($iconList as $key => &$value) {
            if ($value['iconUseFl'] === 'n') {
                unset($iconList[$key]);
            } else if (empty($value['iconImage']) === false) {
                $icon = UserFilePath::icon('goods_icon', $value['iconImage']);
                if ($icon->isFile()) {
                    $value['iconImage'] = gd_html_image($icon->www(), $value['iconNm']);
                } else {
                    unset($value['iconImage']);
                    unset($iconList[$key]);
                }
            }
        }
        return array_values($iconList);
    }

    /**
     * getDataSort
     *
     * @param $getValue
     * @return array $getValue
     */
    public function getDataSort($getValue)
    {
        if(!is_array($getValue)) {
            return;
        }

        $getValue['reSearchKeyword'] = array_unique($getValue['reSearchKeyword']);
        foreach ($getValue['reSearchKeyword'] as $key => $value) {
            if (empty($value) === true) {
                unset($getValue['reSearchKeyword'][$key]);
                unset($getValue['reSearchKey'][$key]);
            } else {
                if (empty($getValue['keyword']) === true) {
                    $getValue['keyword'] = $value;
                    $getValue['key'] = $getValue['reSearchKey'][$key];
                    unset($getValue['reSearchKeyword'][$key]);
                    unset($getValue['reSearchKey'][$key]);
                }
            }
        }

        return $getValue;
    }

    /**
     * 상품의 상품할인가 반환
     *
     * @param array $aGoodsInfo 상품정보
     * @return int 상품할인가반환
     */
    public function getGoodsDcPrice($aGoodsInfo)
    {
        // 상품 할인 금액
        $goodsDcPrice = 0;

        // 상품 할인을 사용하는 경우 상품 할인 계산
        if ($aGoodsInfo['goodsDiscountFl'] === 'y') {
            // 상품 할인 기준 금액 처리
            $tmp['discountByPrice'] = $aGoodsInfo['goodsPrice'];

            // 절사 내용
            $tmp['trunc'] = Globals::get('gTrunc.goods');

            switch ($aGoodsInfo['goodsDiscountGroup']) {
                case 'group':
                    $goodsDiscountGroupMemberInfoData = json_decode($aGoodsInfo['goodsDiscountGroupMemberInfo'], true);
                    $discountKey = array_flip($goodsDiscountGroupMemberInfoData['groupSno'])[Session::get('member.groupSno')];

                    if ($discountKey >= 0) {
                        if ($goodsDiscountGroupMemberInfoData['goodsDiscountUnit'][$discountKey] === 'percent') {
                            $discountPercent = $goodsDiscountGroupMemberInfoData['goodsDiscount'][$discountKey] / 100;

                            // 상품할인금액
                            $goodsDcPrice = gd_number_figure($tmp['discountByPrice'] * $discountPercent, $tmp['trunc']['unitPrecision'], $tmp['trunc']['unitRound']);
                        } else {
                            // 상품할인금액 (정액인 경우 해당 설정된 금액으로)
                            $goodsDcPrice = $goodsDiscountGroupMemberInfoData['goodsDiscount'][$discountKey];
                        }
                    }
                    break;
                case 'member':
                default:
                    if ($aGoodsInfo['goodsDiscountUnit'] === 'percent') {
                        // 상품할인금액
                        $discountPercent = $aGoodsInfo['goodsDiscount'] / 100;
                        $goodsDcPrice = gd_number_figure($tmp['discountByPrice'] * $discountPercent, $tmp['trunc']['unitPrecision'], $tmp['trunc']['unitRound']);
                    } else {
                        // 상품할인금액 (정액인 경우 해당 설정된 금액으로)
                        $goodsDcPrice = $aGoodsInfo['goodsDiscount'];
                    }
                    if ($aGoodsInfo['goodsDiscountGroup'] == 'member' && Session::has('member.memNo') === false) {
                        $goodsDcPrice = 0;
                    }
                    break;
            }
        }

        return $goodsDcPrice;
    }

    /**
     * 상품 재입고 노출 여부
     *
     * @param array $getData 상품 정보
     *
     * @return string 상품 재입고 노출 여부
     */
    public function setRestockUsableFl($getData)
    {
        //상품 재입고 알림 사용 여부
        if($getData['restockFl'] === 'y'){

            //상품 품절시 상품 재입고 사용
            if($getData['soldOut'] === 'y'){
                return 'y';
            }

            //옵션 사용여부
            if($getData['optionFl'] === 'y'){
                if(count($getData['option']) > 0){
                    foreach($getData['option'] as $key => $val){
                        if($val['optionViewFl'] === 'y'){

                            //옵션 품절이 있을시 판매 재고 여부에 상관없이 재입고 노출
                            if($val['optionSellFl'] ==='n'){
                                return 'y';
                                break;
                            }

                            //판매 재고 재고량에 따름
                            if($getData['stockFl'] === 'y') {
                                if($val['stockCnt'] < $getData['minOrderCnt']) {
                                    return 'y';
                                    break;

                                }
                            }
                        }
                    }
                }
            }
            else {
                //판매 재고 재고량에 따름
                if($getData['stockFl'] === 'y') {
                    //총 재고량이 최소 구매수량보다 적으면 품절로 체크함
                    if ((int)$getData['totalStock'] < (int)$getData['minOrderCnt']) {
                        return 'y';
                    }
                }
            }
        }

        return 'n';
    }

    public function setGoodsRestockDiffKey($goodsData)
    {
        return MD5(trim($goodsData['goodsNo']).trim($goodsData['optionName']).trim($goodsData['optionValue']));
    }

    public function setGoodsOptionRestockCare($goodsData)
    {
        $newOption = array();
        foreach($goodsData['option'] as $key => $value){
            //옵션의 노출상태가 노출함일 경우만 해당 옵션 신청가능
            if($value['optionViewFl'] === 'y'){
                // 상품전체가 품절(수동) 이거나
                // 판매재고 - 재고량에 따름 상태로 옵션재고가 최소구매수량보다 적은경우 이거나
                // 옵션의 품절상태가 품절인경우
                if($goodsData['soldOutFl'] === 'y' || ($goodsData['stockFl'] === 'y' && ($value['stockCnt'] < $goodsData['minOrderCnt'])) || $value['optionSellFl'] ==='n'){
                    $optionValueFrontArray = array(
                        $goodsData['option'][$key]['optionValue1'],
                        $goodsData['option'][$key]['optionValue2'],
                        $goodsData['option'][$key]['optionValue3'],
                        $goodsData['option'][$key]['optionValue4'],
                        $goodsData['option'][$key]['optionValue5'],
                    );
                    $optionValueFrontArray = array_values(array_filter($optionValueFrontArray));
                    $goodsData['option'][$key]['optionValue'] = implode(STR_DIVISION, $optionValueFrontArray);
                    $goodsData['option'][$key]['optionValueFront'] = implode("/", $optionValueFrontArray);

                    $newOption[] = $goodsData['option'][$key];
                }
            }
        }

        return $newOption;
    }

    public function saveGoodsRestock($data)
    {
        if( empty($data['optionSno']) ) {
            $arrBind = [];
            $strSql = 'SELECT sno FROM ' . DB_GOODS_OPTION . ' WHERE goodsNo = ?';
            $this->db->bind_param_push($arrBind, 's', $data['goodsNo']);
            $optionSno = $this->db->query_fetch($strSql, $arrBind, false)['sno'];
            $data['optionSno'] = $optionSno;
        }
        $arrBind = $this->db->get_binding(DBTableField::tableGoodsRestockBasic(), $data, 'insert');
        $this->db->set_insert_db(DB_GOODS_RESTOCK, $arrBind['param'], $arrBind['bind'], 'y');
        return $this->db->insert_id();
    }

    /**
     * 상품 재입고 신청시 중복 체크
     *
     * @param array $data 재입고 신청 정보
     *
     * @return boolean 중복 여부 true-중복, false-미중복
     */
    public function checkDuplicationRestock($data)
    {
        $arrBind = [];
        $strSQL = 'SELECT COUNT(sno) as restockCount FROM ' . DB_GOODS_RESTOCK . ' WHERE diffKey=? AND cellPhone=? AND memNo=? AND smsSendFl=?';
        $this->db->bind_param_push($arrBind, 's', $data['diffKey']);
        $this->db->bind_param_push($arrBind, 's', $data['cellPhone']);
        $this->db->bind_param_push($arrBind, 'i', $data['memNo']);
        $this->db->bind_param_push($arrBind, 's', 'n');
        $data = $this->db->query_fetch($strSQL, $arrBind, false);

        $restockCount = $data['restockCount'];
        if ((int)$restockCount > 0) {
            return true;
        } else {
            return false;
        }
    }

    protected function goodsViewStatistics($goodsNo)
    {
        if (empty($goodsNo) === true) return false;

        $replaceGoodsNo = 'g' . $goodsNo;
        $mallSno = SESSION::get(SESSION_GLOBAL_MALL)['sno'] ?? 1;
        $nowKey = date('G');

        $cnt = $this->db->getCount('es_goodsViewStatistics', '*', 'WHERE viewYMD = \'' . gd_date_format('Ymd', 'today') . '\' AND mallSno = "' . $mallSno . '"');

        $arrBind = [];
        if ($cnt > 0) {
            $strSQL = "UPDATE " . DB_GOODS_VIEW_STATISTICS . " SET `" . $nowKey . "` = IF(JSON_EXTRACT(`" . $nowKey . "`, '$." . $replaceGoodsNo . "') IS NULL, IF(`" . $nowKey . "` IS NULL, ?, JSON_MERGE(`" . $nowKey . "`, ?)), JSON_REPLACE(`" . $nowKey . "`, '$." . $replaceGoodsNo . "', JSON_EXTRACT(`" . $nowKey . "`, '$." . $replaceGoodsNo . "') + 1)), `total` = IF(JSON_EXTRACT(`total`, '$." . $replaceGoodsNo . "') IS NULL, IF(`total` IS NULL, ?, JSON_MERGE(`total`, ?)), JSON_REPLACE(`total`, '$." . $replaceGoodsNo . "', JSON_EXTRACT(`total`, '$." . $replaceGoodsNo . "') + 1)) WHERE `viewYMD` = ? AND `mallSno` = ?";
            $this->db->bind_param_push($arrBind, 's', json_encode([$replaceGoodsNo => 1]));
            $this->db->bind_param_push($arrBind, 's', json_encode([$replaceGoodsNo => 1]));
            $this->db->bind_param_push($arrBind, 's', json_encode([$replaceGoodsNo => 1]));
            $this->db->bind_param_push($arrBind, 's', json_encode([$replaceGoodsNo => 1]));
            $this->db->bind_param_push($arrBind, 'i', date('Ymd'));
            $this->db->bind_param_push($arrBind, 'i', $mallSno);
        } else {
            $strSQL = "INSERT INTO " . DB_GOODS_VIEW_STATISTICS . " SET `viewYMD` = ?, `mallSno` = ?, `" . $nowKey . "` = ?, `total` = IF(JSON_EXTRACT(`total`, '$." . $replaceGoodsNo . "') IS NULL, IF(`total` IS NULL, ?, JSON_MERGE(`total`, ?)), JSON_REPLACE(`total`, '$." . $replaceGoodsNo . "', JSON_EXTRACT(`total`, '$." . $replaceGoodsNo . "') + 1))";
            $this->db->bind_param_push($arrBind, 'i', date('Ymd'));
            $this->db->bind_param_push($arrBind, 'i', $mallSno);
            $this->db->bind_param_push($arrBind, 's', json_encode([$replaceGoodsNo => 1]));
            $this->db->bind_param_push($arrBind, 's', json_encode([$replaceGoodsNo => 1]));
            $this->db->bind_param_push($arrBind, 's', json_encode([$replaceGoodsNo => 1]));
        }
        $this->db->bind_query($strSQL, $arrBind);
        unset($arrBind);

        return true;
    }

    /**
     * KC인증 정보
     *
     * @param json $getData KC인증 정보 값
     * @return array KC인증 정보 스크립트
     */
    public function getKcmarkInfo($getData)
    {

        $kcmarkInfo = json_decode($getData, true);
        // KC인증정보
        if ($kcmarkInfo['kcmarkFl'] === 'y') {
            $protocolStr = (\Request::server()->get('HTTPS') == 'on') ? 'https://' : 'http://';
            $popupFunc = 'popupKcInfo(\'' . $protocolStr . $this->_kcmarkUrl . $kcmarkInfo['kcmarkNo'] . '\')';
            if (\Request::isMobile() === false) {
                $message1 = ' 대상 품목으로 아래의 국가 통합인증 필함.';
                $message2 = '(해당 인증 검사 정보는 판매자가 직접 등록한 것으로 등록 정보에 대한 책임은 판매자에게 있습니다.)';

            }

            if (empty($kcmarkInfo['kcmarkDivFl']) === false) {
                foreach ($this->kcmarkCode as $key => $value) {
                    if ($kcmarkInfo['kcmarkDivFl'] === $key) {
                        $kcmarkDivVal = $value . $message1 . '</br>';
                    }
                }
            }

            if($kcmarkInfo['kcmarkDivFl'] == 'kcCd04' || $kcmarkInfo['kcmarkDivFl'] == 'kcCd05' || $kcmarkInfo['kcmarkDivFl'] == 'kcCd06'){
                $kcmarkNumVal = (empty($kcmarkInfo['kcmarkNo']) === false) ? '인증번호 : <a href="' . $this->_nKcmarkUrl . '" class="kcmark-link" target="_blank">' . $kcmarkInfo['kcmarkNo'] . '</a></br>': '';
                $kcmarkDtVal = (empty($kcmarkInfo['kcmarkDt']) === false) ? '인증일자 : ' . $kcmarkInfo['kcmarkDt'] . '</br>': '';
            }else{
                $kcmarkNumVal = (empty($kcmarkInfo['kcmarkNo']) === false) ? '인증번호 : <a href="' . $this->_kcmarkUrl . $kcmarkInfo['kcmarkNo'] .'" class="kcmark-link" target="_blank">' . $kcmarkInfo['kcmarkNo'] . '</a></br>': '';
            }

            if (\Request::isMobile()) {
                if (empty($kcmarkDivVal) === false && empty($kcmarkNumVal) === false) {
                    $style = 'style="float:left"';
                }
            } else {
                if (empty($kcmarkDivVal) === false || empty($kcmarkNumVal) === false) {
                    $style = 'style="float:left"';
                }
            }

            $kcmarkImg = '<div class="kcmark"' . $style . '></div>';
            $this->kcmarkInfoList = [
                'kcmarkInfo' => [
                    "value" => [
                        "infoTitle" => 'KC 안전 인증',
                        "infoValue" => $kcmarkImg . $kcmarkDivVal . $kcmarkNumVal . $kcmarkDtVal . $message2
                    ]
                ]
            ];
        }

        return $this->kcmarkInfoList;
    }

    /**
     * 상품옵션재고
     *
     * @param integer $goodsNo 상품번호
     * @param integer $optionSno 상품옵션번호
     * @param string $stockFl 판매 재고
     * @param string $soldOutFl 품절 상태
     * @return integer $stockCnt 상품재고 (품절제외)
     */
    public function getOptionStock($goodsNo, $optionSno = null, $stockFl, $soldOutFl)
    {
        if ($stockFl == 'n') {
            $stockCnt = '∞';
        } else {
            if ($soldOutFl == 'y') {
                $stockCnt = 0;
            } else {
                $arrBind = [];
                $strSQL = 'SELECT SUM(stockCnt) as stockCnt FROM ' . DB_GOODS_OPTION . ' WHERE goodsNo=? AND optionSellFl=?';
                $this->db->bind_param_push($arrBind, 'i', $goodsNo);
                $this->db->bind_param_push($arrBind, 's', 'y');
                if (empty($optionSno) === false) {
                    $strSQL .= ' AND sno = ?';
                    $this->db->bind_param_push($arrBind, 'i', $optionSno);
                }
                $strSQL .= ' GROUP BY goodsNo';
                $data = $this->db->query_fetch($strSQL, $arrBind, false);

                $stockCnt = $data['stockCnt'];
            }
        }

        return $stockCnt;
    }

    /**
     * 상품옵션값의 재고량 및 옵션품절상태 조회
     *
     * @param integer $goodsNo 상품번호
     * @param string $optionVal 옵션 값
     * @return integer $stockCnt 상품재고 (품절제외)
     */
    public function getOptionValueStock($goodsNo, $optionVal)
    {
        $optionVal = ArrayUtils::removeEmpty($optionVal);
        if (is_array($optionVal) === false) {
            $optionVal = [$optionVal];
        }

        // 옵션품절상태로 '정상'인 내역만 조회하여 재고 합산
        $arrBind = [];
        $strSQL = 'SELECT SUM(stockCnt) as stockCnt, optionSellFl FROM ' . DB_GOODS_OPTION . ' WHERE goodsNo=? AND optionSellFl=?';
        $this->db->bind_param_push($arrBind, 'i', $goodsNo);
        $this->db->bind_param_push($arrBind, 's', 'y');
        foreach ($optionVal as $key => $val) {
            $fieldNm = 'optionValue' . ($key + 1);
            $strSQL .= ' AND ' . $fieldNm . ' = ?';
            $this->db->bind_param_push($arrBind, 's', $val);
        }
        $strSQL .= ' GROUP BY goodsNo';
        $data = $this->db->query_fetch($strSQL, $arrBind, false);
        if (count($data) > 0) { // 옵션품절상태로 '정상' 내역 있는 경우
            $result = [
                'stockCnt' => $data['stockCnt'],
                'optionSellFl' => $data['optionSellFl'],
                'optionSellCode' => '',
            ];
        } else { // 옵션품절상태로 '정상' 내역 없는 경우
            // 옵션품절상태로 '정상' 외 내역 중 첫번째 내역 조회하여 옵션품절상태 리턴
            $arrBind = [];
            $strSQL = 'SELECT optionSellFl, optionSellCode FROM ' . DB_GOODS_OPTION . ' WHERE goodsNo=? AND optionSellFl!=?';
            $this->db->bind_param_push($arrBind, 'i', $goodsNo);
            $this->db->bind_param_push($arrBind, 's', 'y');
            foreach ($optionVal as $key => $val) {
                $fieldNm = 'optionValue' . ($key + 1);
                $strSQL .= ' AND ' . $fieldNm . ' = ?';
                $this->db->bind_param_push($arrBind, 's', $val);
            }
            $strSQL .= ' limit 1';
            $data = $this->db->query_fetch($strSQL, $arrBind, false);
            $result = [
                'stockCnt' => '0',
                'optionSellFl' => $data['optionSellFl'],
                'optionSellCode' => $data['optionSellCode'],
            ];
        }

        return $result;
    }

    /**
     * 해외몰 상품명
     *
     * @param integer $goodsNo 상품번호
     * @param integer $mallSno 상점번호
     * @return string $data 상품명
     */
    public function getGoodsNmGlobal($goodsNo, $mallSno)
    {
        $arrBind = [];
        $strSQL = "SELECT goodsNm FROM " . DB_GOODS_GLOBAL . " WHERE goodsNo=? AND mallSno=?";
        $this->db->bind_param_push($arrBind, 'i', $goodsNo);
        $this->db->bind_param_push($arrBind, 'i', $mallSno);

        $data = $this->db->query_fetch($strSQL, $arrBind, false);

        return $data['goodsNm'];
    }

    /**
     * 상품 아이콘 정보 출력
     *
     * @param string $goodsNo 상품 번호
     *
     * @return array 해당 상품 아이콘 정보
     */
    public function getGoodsDetailIcon($goodsNo)
    {
        $arrField = DBTableField::setTableField('tableGoodsIcon', null, 'goodsNo');
        $strSQL = "SELECT " . implode(', ', $arrField) . " FROM " . DB_GOODS_ICON . " WHERE goodsNo = ? ORDER BY sno ASC";
        $arrBind = ['i', $goodsNo];
        $getData = $this->db->slave()->query_fetch($strSQL, $arrBind);
        if (count($getData) > 0) {
            return gd_htmlspecialchars_stripslashes($getData);
        } else {
            return false;
        }
    }

    /**
     * 상품 정렬 코드를 필드로 변경
     * @param $sort
     * @return mixed
     */
    public function getSortMatch($sort)
    {
        $sortCode = [
            'sellcnt' => 'orderCnt desc,g.regDt desc', // 판매인기순
            'price_asc' => 'goodsPrice asc,g.regDt desc', // 낮은가격순
            'price_dsc' => 'goodsPrice desc,g.regDt desc', // 높은가격순
            'review' => 'reviewCnt desc,g.regDt desc', // 상품평순
            'date' => 'g.regDt desc', // 등록일순
        ];

        if (isset($sortCode[$sort]) === true) {
            return $sortCode[$sort];
        } else {
            return $sort;
        }
    }

    /**
     * 테마설정정보 저장
     */
    public function setThemeConfig($data = [])
    {
        $this->themeConfig = $data;
    }
}
