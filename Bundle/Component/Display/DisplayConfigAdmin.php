<?php
/**
 * 상품노출형태 관리
 * @author atomyang
 * @version 1.0
 * @since 1.0
 * @copyright ⓒ 2016, NHN godo: Corp.
 */

namespace Bundle\Component\Display;

use Component\Database\DBTableField;
use Component\Validator\Validator;
use Globals;
use LogHandler;
use Request;


class DisplayConfigAdmin extends \Component\Display\DisplayConfig
{
    const ECT_INVALID_ARG = 'Config.ECT_INVALID_ARG';
    const TEXT_INVALID_NOTARRAY_ARG = '%s이 배열이 아닙니다.';
    const TEXT_INVALID_EMPTY_ARG = '%s이 비어있습니다.';
    const TEXT_REQUIRE_VALUE = '%s은(는) 필수 항목 입니다.';
    const TEXT_USELESS_VALUE = '%s은(는) 사용할 수 없습니다.';


    const TEXT_THEME_MAIN = 'B';
    const TEXT_THEME_CATEGORY_ = 'E';
    const TEXT_THEME_BRAND_ = 'C';
    const TEXT_THEME_SEARCH_ = 'A';
    const TEXT_THEME_RECOMMEND = 'D';


    protected $db;

    /**
     * @var array arrBind
     */
    private $arrBind = [];

    /**
     * @var array 조건
     */
    private $arrWhere = [];

    /**
     * @var array 체크
     */
    private $checked = [];

    /**
     * @var array 검색
     */
    private $search = [];

    /**
     * @var array 상세 선택 기본 값
     */
    public $detailSetConfig = array(
        '04' => array('R'),
        '05' => array('T'),
        '06' => array('W'),
        '07' => array('2', 'W'),
        '08' => array('S', '70'),
        '10' => array('B', '70')
    );

    // __('최근 등록 상품 위로')
    // __('최근 등록 상품 아래로')
    // __('최근 수정 상품 위로')
    // __('최근 수정 상품 아래로')
    // __('상품명 가나다순')
    // __('상품명 가나다역순')
    // __('판매가 높은 상품 위로')
    // __('판매가 높은 상품 아래로')
    // __('판매량 높은 상품 위로')
    // __('판매량 높은 상품 아래로')
    // __('조회수 높은 상품 위로')
    // __('조회수 높은 상품 아래로')
    public $goodsSortList = array(
        'g.goodsNo desc' => '최근 등록 상품 위로',
        'g.goodsNo asc' => '최근 등록 상품 아래로',
        'g.modDt desc' => '최근 수정 상품 위로',
        'g.modDt asc' => '최근 수정 상품 아래로',
        'goodsNm asc' => '상품명 가나다순',
        'goodsNm desc' => '상품명 가나다역순',
        'goodsPrice desc' => '판매가 높은 상품 위로',
        'goodsPrice asc' => '판매가 높은 상품 아래로',
        'orderCnt desc' => '판매량 높은 상품 위로',
        'orderCnt asc' => '판매량 높은 상품 아래로',
        'hitCnt desc' => '조회수 높은 상품 위로',
        'hitCnt asc' => '조회수 높은 상품 아래로'
    );

    // __('짧은설명')
    // __('정가')
    // __('판매가')
    // __('구매제한')
    // __('구매혜택')
    // __('쿠폰받기')
    // __('배송비')
    // __('상품코드')
    // __('자체상품코드')
    // __('모델명')
    // __('브랜드')
    // __('제조사')
    // __('원산지')
    // __('상품무게')
    // __('추가항목')
    // __('제조일')
    // __('출시일')
    // __('유효일자')
    // __('판매기간')
    // __('묶음주문')
    // __('상품재고')
    public $goodsDisplayField = array(
        'shortDescription' => '짧은설명',
        'fixedPrice' => '정가',
        'goodsPrice' => '판매가',
        'goodsDiscount' => '할인적용가',
        'maxOrderCnt' => '구매제한',
        'benefit' => '구매혜택',
        'couponDownload' => '쿠폰받기',
        'delivery' => '배송비',
        'goodsNo' => '상품코드',
        'goodsCd' => '자체상품코드',
        'goodsModelNo' => '모델명',
        'brandNm' => '브랜드',
        'makerNm' => '제조사',
        'originNm' => '원산지',
        'goodsWeight' => '상품 무게/용량',
        'addInfo' => '추가항목',
        'makeYmd' => '제조일',
        'launchYmd' => '출시일',
        'effectiveStartYmd' => '유효일자',
        'salesStartYmd' => '판매기간',
        'salesUnit' => '묶음주문',
        'totalStock' => '상품재고',
        'couponPrice' => '쿠폰적용가',
        'myCouponPrice' => '내 쿠폰적용가',
    );

    // __('옵션재고')
    // __('아이콘')
    public $goodsDisplayAddField = array(
        'optionStock' => '옵션재고',
        'goodsIcon'   => '아이콘',
        'goodsColor' => '대표색상',
        'dcRate' => '할인율',
    );

    // __('정가')
    // __('판매가')
    public $goodsDisplayStrikeField = array(
        'fixedPrice' => '정가',
        'goodsPrice' => '판매가',
    );

    public function __construct()
    {
        if (!is_object($this->db)) {
            $this->db = \App::load('DB');
        }

        parent::__construct();
    }

    /**
     * newCode
     *
     * @param $themeCate
     * @return string
     */
    private function newCode($themeCate)
    {
        $strSQL = 'SELECT MAX(substring(themeCd,2)) FROM ' . DB_DISPLAY_THEME_CONFIG . ' WHERE themeCate=\'' . $themeCate . '\'';
        list($tmp) = $this->db->fetch($strSQL, 'row');
        return $themeCate . sprintf('%07d', ($tmp + 1));
    }

    /**
     * saveInfoThemeConfig
     *
     * @param $arrData
     * @throws Except
     */
    public function saveInfoThemeConfig($arrData)
    {
        // 테마명 체크
        if (Validator::required(gd_isset($arrData['themeNm'])) === false) {
            throw new \Exception(__('테마명은 필수 항목입니다.'), 500);
        }

        // 테마분류
        if (Validator::required(gd_isset($arrData['themeCate'])) === false) {
            throw new \Exception(__('테마코드는 필수 항목입니다.'), 500);
        }

        $arrData['displayField'] = implode(',', $arrData['displayField']);
        $arrData['goodsDiscount'] = implode(',', $arrData['goodsDiscount']);
        $arrData['priceStrike'] = implode(',', $arrData['priceStrike']);
        $arrData['displayAddField'] = implode(',', $arrData['displayAddField']);
        if (isset($arrData['detailSet'][$arrData['displayType']])) {
            $detailSet = $arrData['detailSet'][$arrData['displayType']];
            if (is_array($detailSet)) $detailSet = array_filter(array_map('trim', $arrData['detailSet'][$arrData['displayType']]));
            $arrData['detailSet'] = serialize($detailSet);
        } else {
            $arrData['detailSet'] = "";
        }


        // 테마명 정보 저장
        if ($arrData['mode'] == 'theme_modify' || $arrData['mode'] == 'theme_modify_ajax') {
            $arrBind = $this->db->get_binding(DBTableField::tableDisplayThemeConfig(), $arrData, 'update',null,['useCnt']);
            $this->db->bind_param_push($arrBind['bind'], 's', $arrData['themeCd']);
            $this->db->set_update_db(DB_DISPLAY_THEME_CONFIG, $arrBind['param'], 'themeCd = ?', $arrBind['bind']);
        } else {
            $arrData['themeCd'] = $this->newCode($arrData['themeCate']);
            $arrBind = $this->db->get_binding(DBTableField::tableDisplayThemeConfig(), $arrData, 'insert');
            $this->db->set_insert_db(DB_DISPLAY_THEME_CONFIG, $arrBind['param'], $arrBind['bind'], 'y');
        }


        unset($arrBind);

        if ($arrData['mode'] == 'theme_modify' || $arrData['mode'] == 'theme_modify_ajax') {
            // 전체 로그를 저장합니다.
            LogHandler::wholeLog('displayConfig', null, 'modify', $arrData['themeCd'], $arrData['themeNm']);
        }

        return $arrData['themeCd'];
    }

    /**
     * getAdminListDisplayThemeConfig
     *
     * @return mixed
     */
    public function getAdminListDisplayThemeConfig()
    {
        $getValue = Request::get()->toArray();

        // --- 검색 설정
        $this->setSearchThemeConfig($getValue);

        // --- 정렬 설정
        $sort = gd_isset($getValue['sort']);
        if (empty($sort)) {
            $sort = 'regDt desc';
        }

        // --- 페이지 기본설정
        gd_isset($getValue['page'], 1);
        gd_isset($getValue['pageNum'], 10);

        $page = \App::load('\\Component\\Page\\Page', $getValue['page']);
        $page->page['list'] = $getValue['pageNum']; // 페이지당 리스트 수
        $page->recode['amount'] = $this->db->getCount(DB_DISPLAY_THEME_CONFIG); // 전체 레코드 수
        $page->setPage();
        $page->setUrl(\Request::getQueryString());

        // 현 페이지 결과
        $this->db->strField = " *";
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
        $this->db->strOrder = $sort;
        $this->db->strLimit = $page->recode['start'] . ',' . $getValue['pageNum'];

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_DISPLAY_THEME_CONFIG . ' ' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $this->arrBind);

        // 현 페이지 결과
        $this->db->strField = "count(*) as cnt";
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_DISPLAY_THEME_CONFIG . ' ' . implode(' ', $query);
        $total = $this->db->query_fetch($strSQL, $this->arrBind, false)['cnt'];

        // 검색 레코드 수
        $page->recode['total'] = $total;
        $page->setPage();

        // 각 데이터 배열화
        $getData['data'] = gd_htmlspecialchars_stripslashes(gd_isset($data));
        $getData['sort'] = $sort;
        $getData['search'] = gd_htmlspecialchars($this->search);
        $getData['checked'] = $this->checked;
        $getData['selected'] = $this->selected;

        return $getData;
    }

    /**
     * setSearchThemeConfig
     *
     * @param $searchData
     * @param int $searchPeriod
     */
    public function setSearchThemeConfig($searchData, $searchPeriod = '-1')
    {
        // 검색을 위한 bind 정보
        $fieldType = DBTableField::getFieldTypes('tableDisplayThemeConfig');


        //검색설정
        $this->search['sortList'] = array(
            'regDt desc' => __('등록일 ↑'),
            'regDt asc' => __('등록일 ↓'),
            'themeCate desc' => __('테마분류 ↑'),
            'themeCate asc' => __('테마분류 ↓'),
            'themeNm desc' => __('테마명 ↑'),
            'themeNm asc' => __('테마명 ↓')
        );

        // --- 검색 설정
        $this->search['sort'] = gd_isset($searchData['sort'], 'regDt desc');
        $this->search['detailSearch'] = gd_isset($searchData['detailSearch']);
        $this->search['searchDateFl'] = gd_isset($searchData['searchDateFl'], 'regDt');
        $this->search['searchPeriod'] = gd_isset($searchData['searchPeriod'], '-1');
        $this->search['themeNm'] = gd_isset($searchData['themeNm']);
        $this->search['themeCate'] = gd_isset($searchData['themeCate'], 'all');
        $this->search['mobileFl'] = gd_isset($searchData['mobileFl'], 'all');
        $this->search['imageCd'] = gd_isset($searchData['imageCd']);

        if ($this->search['searchPeriod'] < 0) {
            $this->search['searchDate'][] = gd_isset($searchData['searchDate'][0]);
            $this->search['searchDate'][] = gd_isset($searchData['searchDate'][1]);
        } else {
            $this->search['searchDate'][] = gd_isset($searchData['searchDate'][0], date('Y-m-d', strtotime('-6 day')));
            $this->search['searchDate'][] = gd_isset($searchData['searchDate'][1], date('Y-m-d'));
        }

        $this->checked['mobileFl'][$searchData['mobileFl']]  = $this->checked['themeCate'][$searchData['themeCate']] = "checked='checked'";
        $this->checked['searchPeriod'][$this->search['searchPeriod']] = "active";
        $this->selected['searchDateFl'][$this->search['searchDateFl']] = $this->selected['sort'][$this->search['sort']] = "selected='selected'";


        // 처리일자 검색
        if ($this->search['searchDateFl'] && $this->search['searchDate'][0] && $this->search['searchDate'][1]) {
            $this->arrWhere[] = $this->search['searchDateFl'] . ' BETWEEN ? AND ?';
            $this->db->bind_param_push($this->arrBind, 's', $this->search['searchDate'][0] . ' 00:00:00');
            $this->db->bind_param_push($this->arrBind, 's', $this->search['searchDate'][1] . ' 23:59:59');
        }

        // 테마명 검색
        if ($this->search['themeNm']) {
            $this->arrWhere[] = 'themeNm LIKE concat(\'%\',?,\'%\')';
            $this->db->bind_param_push($this->arrBind, $fieldType['themeNm'], $this->search['themeNm']);
        }

        // 테마분류 검색
        if ($this->search['themeCate'] != 'all') {
            $this->arrWhere[] = 'themeCate = ?';
            $this->db->bind_param_push($this->arrBind, $fieldType['themeCate'], $this->search['themeCate']);
        }

        // 적용 사이트 구분
        if ($this->search['mobileFl'] != 'all') {
            $this->arrWhere[] = 'mobileFl = ?';
            $this->db->bind_param_push($this->arrBind, $fieldType['mobileFl'], $this->search['mobileFl']);
        }


        // 구매 상품 범위 검색
        if ($this->search['imageCd']) {
            $this->arrWhere[] = 'imageCd = ?';
            $this->db->bind_param_push($this->arrBind, $fieldType['imageCd'], $this->search['imageCd']);
        }

        if (empty($this->arrBind)) {
            $this->arrBind = null;
        }

    }

    /**
     * deleteThemeConfig
     *
     * @param $themeCd
     */
    public function deleteThemeConfig($themeCd)
    {
        $strWhere = "themeCd IN ('" . implode("','", $themeCd) . "')";
        $this->db->set_delete_db(DB_DISPLAY_THEME_CONFIG, $strWhere);
    }


    /**
     * getDataThemeCongif
     *
     * @param null $themeCd
     * @return mixed
     */
    public function getDataThemeConfig($themeCd = null)
    {
        // --- 등록인 경우
        if (!$themeCd) {
            // 기본 정보
            $data['mode'] = 'theme_register';
            // 기본값 설정
            DBTableField::setDefaultData('tableDisplayThemeConfig', $data);
            $data['goodsDiscount'] = 'goods';
            $data['priceStrike'] = 'fixedPrice';

            if (Request::get()->get('themeCate')) {
                $data['themeCate'] = Request::get()->get('themeCate');
                $data['themeDisabled'] = "disabled='true'";
            }

            if (Request::get()->get('mobileFl')) {
                $data['mobileFl'] = Request::get()->get('mobileFl');
            }

            // --- 수정인 경우
        } else {
            // 테마 정보
            $data = $this->getInfoThemeConfig($themeCd);
            $data['mode'] = 'theme_modify';

            $data['themeDisabled'] = "disabled='true'";
            $data['mobileDisabled'] = "disabled='true'";

            // 기본값 설정
            DBTableField::setDefaultData('tableDisplayThemeConfig', $data);
        }


        $data['displayField'] = explode(",", $data['displayField']);
        $data['goodsDiscount'] = explode(",", $data['goodsDiscount']);
        $data['priceStrike'] = explode(",", $data['priceStrike']);
        $data['displayAddField'] = explode(",", $data['displayAddField']);
        $data['detailSetConfig'] = $this->detailSetConfig;
        if ($data['detailSet']) {
            $data['detailSetConfig'][$data['displayType']] = unserialize($data['detailSet']);
        }

        $checked = array();
        $checked['mobileFl'][gd_isset($data['mobileFl'])] = $checked['soldOutFl'][gd_isset($data['soldOutFl'])] = $checked['soldOutDisplayFl'][gd_isset($data['soldOutDisplayFl'])] = $checked['themeCate'][gd_isset($data['themeCate'])] = $checked['displayType'][gd_isset($data['displayType'])] = $checked['soldOutIconFl'][gd_isset($data['soldOutIconFl'])] = $checked['iconFl'][gd_isset($data['iconFl'])] = 'checked="checked"';

        $getData['data'] = $data;
        $getData['checked'] = $checked;

        return $getData;
    }

    public function saveInfoDisplayMenuLayer($arrData)
    {
        gd_set_policy('display.menuLayer_category', $arrData['category']);
        gd_set_policy('display.menuLayer_brand', $arrData['brand']);

    }

    public function getDateNaviDisplay()
    {
        $getData['categoryAuto'] = gd_policy('display.auto_category');
        if (!$getData['categoryAuto']) {
            $getData['categoryAuto']['autoUse'] = 'n';
        }
        $getData['category'] = gd_policy('display.navi_category');
        if (!$getData['category']) {
            $getData['category']['naviUse'] = 'y';
            $getData['category']['naviCount'] = 'y';
        }
        if (empty($getData['category']['linkUse']) === true) {
            $getData['category']['linkUse'] = 'n';
        }

        $getData['brand'] = gd_policy('display.navi_brand');
        if (!$getData['brand']) {
            $getData['brand']['naviUse'] = 'y';
            $getData['brand']['naviCount'] = 'y';
        }
        if (empty($getData['brand']['linkUse']) === true) {
            $getData['brand']['linkUse'] = 'n';
        }

        $checked['categoryAuto']['autoUse'][$getData['categoryAuto']['autoUse']] =
        $checked['brand']['naviUse'][$getData['brand']['naviUse']] =
        $checked['category']['naviUse'][$getData['category']['naviUse']] =
        $checked['brand']['naviCount'][$getData['brand']['naviCount']] =
        $checked['category']['naviCount'][$getData['category']['naviCount']] =
        $checked['brand']['linkUse'][$getData['brand']['linkUse']] =
        $checked['category']['linkUse'][$getData['category']['linkUse']] = "checked='checked'";

        $data['data'] = $getData;
        $data['checked'] = $checked;

        return $data;
    }

    public function saveInfoDisplayNavi($arrData)
    {
        gd_set_policy('display.auto_category', $arrData['categoryAuto']);
        gd_set_policy('display.navi_category', $arrData['category']);
        gd_set_policy('display.navi_brand', $arrData['brand']);

    }

    public function getDateRelationDisplay()
    {
        $getData = gd_policy('display.relation');

        if (!$getData) {
            $getData['lineCnt'] = "5";
            $getData['rowCnt'] = "1";
            $getData['soldOutFl'] = "n";
            $getData['soldOutIconFl'] = "n";
            $getData['iconFl'] = "n";
            $getData['relationLinkFl'] = "self";
            $getData['useCartFl'] = "n";
            $getData['cartIcon'] = "1";
            $getData['displayField'] = array('img', 'goodsNm');
        }

        if (in_array('goodsDiscount', array_keys($getData)) === false) gd_isset($getData['goodsDiscount'], ['goods']);
        if (in_array('priceStrike', array_keys($getData)) === false) gd_isset($getData['priceStrike'], ['fixedPrice']);
        if (in_array('mobileGoodsDiscount', array_keys($getData)) === false) gd_isset($getData['mobileGoodsDiscount'], ['goods']);
        if (in_array('mobilePriceStrike', array_keys($getData)) === false) gd_isset($getData['mobilePriceStrike'], ['fixedPrice']);

        gd_isset($getData['displayType'],'01');
        gd_isset($getData['mobileDisplayType'],'01');
        gd_isset($getData['mobileLineCnt'],'2');
        gd_isset($getData['mobileRowCnt'],'2');
        gd_isset($getData['mobileSoldOutFl'],'y');
        gd_isset($getData['mobileSoldOutDisplayFl'],'y');
        gd_isset($getData['mobileSoldOutIconFl'],'y');
        gd_isset($getData['mobileIconFl'],'y');
        gd_isset($getData['mobileRelationLinkFl'],'blank');
        gd_isset($getData['mobileDisplayField'],array('img', 'goodsNm'));

        $getData['detailSetConfig'] =
        $getData['mobileDetailSetConfig'] = $this->detailSetConfig;
        if ($getData['detailSet']) {
            $getData['detailSetConfig'][$getData['displayType']] = $getData['detailSet'];
        }
        if ($getData['mobileDetailSet']) {
            $getData['mobileDetailSetConfig'][$getData['mobileDisplayType']] = $getData['mobileDetailSet'];
        }

        $checked['displayType'][$getData['displayType']] =
        $checked['soldOutDisplayFl'][$getData['soldOutDisplayFl']] =
        $checked['soldOutFl'][$getData['soldOutFl']] =
        $checked['soldOutIconFl'][$getData['soldOutIconFl']] =
        $checked['iconFl'][$getData['iconFl']] =
        $checked['relationLinkFl'][$getData['relationLinkFl']] =
        $checked['useCartFl'][$getData['useCartFl']] =
        $checked['cartIcon'][$getData['cartIcon']] =
        $checked['mobileDisplayType'][$getData['mobileDisplayType']] =
        $checked['mobileSoldOutDisplayFl'][$getData['mobileSoldOutDisplayFl']] =
        $checked['mobileSoldOutFl'][$getData['mobileSoldOutFl']] =
        $checked['mobileSoldOutIconFl'][$getData['mobileSoldOutIconFl']] =
        $checked['mobileIconFl'][$getData['mobileIconFl']] =
        $checked['mobileRelationLinkFl'][$getData['mobileRelationLinkFl']] = "checked='checked'";

        $selected['lineCnt'][$getData['lineCnt']] =
        $selected['rowCnt'][$getData['rowCnt']] =
        $selected['mobileLineCnt'][$getData['mobileLineCnt']] =
        $selected['mobileRowCnt'][$getData['mobileRowCnt']] = "selected='selected'";

        $data['data'] = $getData;
        $data['checked'] = $checked;
        $data['selected'] = $selected;

        return $data;
    }


    public function saveInfoDisplayRelation($arrData)
    {

        if (isset($arrData['detailSet'][$arrData['displayType']])) {
            $detailSet = $arrData['detailSet'][$arrData['displayType']];
            if (is_array($detailSet)) $detailSet = array_filter(array_map('trim', $arrData['detailSet'][$arrData['displayType']]));
            $arrData['detailSet'] = $detailSet;
        } else {
            $arrData['detailSet'] = "";
        }
        if (isset($arrData['mobileDetailSet'][$arrData['mobileDisplayType']])) {
            $mobileDetailSet = $arrData['mobileDetailSet'][$arrData['mobileDisplayType']];
            if (is_array($mobileDetailSet)) $mobileDetailSet = array_filter(array_map('trim', $arrData['mobileDetailSet'][$arrData['mobileDisplayType']]));
            $arrData['mobileDetailSet'] = $mobileDetailSet;
        } else {
            $arrData['mobileDetailSet'] = "";
        }

        foreach (['goodsDiscount', 'priceStrike', 'displayAddField', 'mobileGoodsDiscount', 'mobilePriceStrike', 'mobileDisplayAddField'] as $val) {
            if (empty($arrData[$val]) === true) {
                $arrData[$val] = '';
            }
        }

        gd_set_policy('display.relation', $arrData);

    }


    public function getDateGoodsDisplay()
    {
        $getData = gd_policy('display.goods');

        if (isset($getData['defaultField']['goodsWeight'])) {
            $getData['defaultField']['goodsWeight'] = '상품 무게/용량';
        }

        if (in_array('goodsDiscount', array_keys($getData)) === false) {
            gd_isset($getData['goodsDiscount']['pc'], ['goods']);
            gd_isset($getData['goodsDiscount']['mobile'], ['goods']);
        }

        $data['data'] = $getData;
        $data['fieldList'] = $this->goodsDisplayField;
        $data['addFieldList'] = $this->goodsDisplayAddField;
        $data['strikeFieldList'] = $this->goodsDisplayStrikeField;
        $data['themeGoodsDiscount'] = $this->themeGoodsDiscount;
        $checked['mobileFl'][$getData['mobileFl']] =  "checked='checked'";
        $data['checked'] = $checked;

        return $data;
    }

    public function saveInfoDisplayGoods($arrData)
    {
        unset($arrData['sort']);
        unset($arrData['mode']);

        gd_isset($arrData['mobileFl'],'n');
        gd_isset($arrData['goodsDisplayAddField']['pc'],[]);
        gd_isset($arrData['goodsDisplayAddField']['mobile'],[]);
        gd_isset($arrData['goodsDisplayStrikeField']['pc'],[]);
        gd_isset($arrData['goodsDisplayStrikeField']['mobile'],[]);
        gd_isset($arrData['goodsDiscount']['pc'],[]);
        gd_isset($arrData['goodsDiscount']['mobile'],[]);

        if($arrData['mobileFl'] =='y') {
            $arrData['goodsDisplayField']['mobile'] =$arrData['goodsDisplayField']['pc'];
            $arrData['goodsDisplayAddField']['mobile'] =$arrData['goodsDisplayAddField']['pc'];
            $arrData['goodsDisplayStrikeField']['mobile'] =$arrData['goodsDisplayStrikeField']['pc'];
            $arrData['goodsDiscount']['mobile'] =$arrData['goodsDiscount']['pc'];
        }

        // 기본 필드명-설명 goodsDisplayStrikeField의 내용은 goodsDisplayField에 포함되어있어서 머지할필요는 없음
        $arrData['defaultField'] = array_merge($this->goodsDisplayField, $this->goodsDisplayAddField);

        gd_set_policy('display.goods', $arrData);
    }

}
