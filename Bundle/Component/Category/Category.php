<?php
/**
 * 카테고리 class
 *
 * 카테고리 관련 Class
 * @author    artherot
 * @version   1.0
 * @since     1.0
 * @copyright ⓒ 2016, NHN godo: Corp.
 */

namespace Bundle\Component\Category;

use Component\Database\DBTableField;
use Component\Storage\Storage;
use Framework\Utility\ArrayUtils;
use Framework\Utility\StringUtils;
use Globals;
use Request;
use Session;

class Category
{
    const ERROR_VIEW = 'ERROR_VIEW';

    const NOT_ACCESS_CATEGORY = 'NOT_ACCESS_CATEGORY';

    protected $db;
    protected $cateType;                    // 카테고리 종류
    protected $cateTable;                    // 카테고리 기본 테이블명
    protected $cateGlobalTable;             // 카테고리 글로벌 테이블
    protected $cateLength;                // 카테고리 기본 노드당 길이
    protected $cateDepth;                    // 카테고리 기본 차수
    protected $storage;
    protected $gGlobal;

    /**
     * 생성자
     *
     * @param string $cateType 카테고리 종류(goods,brand) , null인 경우 상품 카테고리 , (기본 null)
     */
    public function __construct($cateType = null)
    {
        if (!is_object($this->db)) {
            $this->db = \App::load('DB');
        }

        $this->cateType = gd_isset($cateType, 'goods');
        // 함수명이 camel로 변경됨에 따라 첫글자 대문자로 변경 처리
        $this->cateFuncNm = 'tableCategory' . ucfirst($cateType);
        if ($this->cateType == 'goods') {
            $this->cateTable = DB_CATEGORY_GOODS;
            $this->cateLength = DEFAULT_LENGTH_CATE;
            $this->cateDepth = DEFAULT_DEPTH_CATE;
            $this->cateGlobalTable = DB_CATEGORY_GOODS_GLOBAL;
        } else if ($this->cateType == 'brand') {
            $this->cateTable = DB_CATEGORY_BRAND;
            $this->cateLength = DEFAULT_LENGTH_BRAND;
            $this->cateGlobalTable = DB_CATEGORY_BRAND_GLOBAL;
            $this->cateDepth = DEFAULT_DEPTH_BRAND;
        }

        $this->storage = null;

        $this->gGlobal = Globals::get('gGlobal');
    }

    /**
     * 카테고리 정보 출력
     * 완성된 쿼리문은 $db->strField , $db->strJoin , $db->strWhere , $db->strGroup , $db->strOrder , $db->strLimit 멤버 변수를
     * 이용할수 있습니다.
     *
     * @param string $cateCd 카테고리 코드 번호 (기본 null)
     * @param string $cateField 출력할 필드명 (기본 null)
     * @param array $arrBind bind 처리 배열 (기본 null)
     * @param bool|string $dataArray return 값을 배열처리 (기본값 false)
     * @param null $search 검색 조건 [field : 찾는 필드 , value : 찾는 값]
     * @return array 카테고리 정보
     */
    public function getCategoryInfo($cateCd = null, $cateField = null, $arrBind = null, $dataArray = false,$search = null)
    {
        if (is_null($arrBind)) {
            // $arrBind = array();
        }

        if ($cateCd) {

            // 상품 코드가 배열인 경우
            if(is_array($cateCd) === true) {
                $arrWhere = "cateCd IN ('" . implode("','", $cateCd) . "')";
                // 상품 코드가 하나인경우
            } else {
                $arrWhere  =" cate.cateCd = ?";
                $this->db->bind_param_push($arrBind, 'i', $cateCd);
            }

            if ($this->db->strWhere) {
                $this->db->strWhere = $arrWhere." AND " . $this->db->strWhere;
            } else {
                $this->db->strWhere = $arrWhere;
            }
        }

        if($search){
            $validSearchField = ['cateNm'];
            if(in_array($search['field'],$validSearchField)){
                $this->db->strWhere .= $search['field']."  LIKE concat('%',?,'%') " ;
                $this->db->bind_param_push($arrBind, 's', $search['keyword']);
            }
        }

        if ($cateField) {
            if ($this->db->strField) {
                $this->db->strField = $cateField . ', ' . $this->db->strField;
            } else {
                $this->db->strField = $cateField;
            }
        }
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . $this->cateTable . ' as cate ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);
        if (count($getData) == 1 && $dataArray === false) {
            return gd_htmlspecialchars_stripslashes($getData[0]);
        }

        return gd_htmlspecialchars_stripslashes($getData);
    }

    /**
     * 카테고리 정보 출력 - categoryGoods, categorySpecial 테이블의 정보만 출력함
     *
     * @param string $cateCd     카테고리 코드
     * @param string $cateCdLike 카테고리 코드 (Like 검색)
     * @param string $cateField  카테고리 테이블의 필드 (기본 *)
     * @param string $setWhere   where 문
     * @param string $setOrderBy order by 문
     * @param string $debug      query문을 출력, true 인 경우 결과를 return 과 동시에 query 출력 (기본 false)
     *
     * @return array 상품 정보
     */
    public function getCategoryData($cateCd = null, $cateCdLike = null, $cateField = '*', $setWhere = null, $setOrderBy = null, $debug = false)
    {
        $mallBySession = SESSION::get(SESSION_GLOBAL_MALL);
        gd_isset($mallBySession['sno'],DEFAULT_MALL_NUMBER);

        $whereArr = $orderByArr = $getData = $arrBind = [];
        $whereStr = $orderByStr = null;

        if($mallBySession && !Session::has('manager.managerId')) {
            $whereArr[] = 'FIND_IN_SET('.$mallBySession['sno'].',mallDisplay)';
        }
        if ($cateCd) {
            if(is_array($cateCd)) {
                $tmpCateCd = [];
                foreach ($cateCd as $key => $val) {
                    $tmpCateCd[] = '?';
                    $this->db->bind_param_push($arrBind, 's', $val);
                }
                $whereArr[] = " cateCd IN (" . implode(',', $tmpCateCd) . ") ";
                unset($tmpCateCd);
            }
            else {
                $whereArr[] = " cateCd = ? ";
                $this->db->bind_param_push($arrBind, 's', $cateCd);
            }

        }
        if ($cateCdLike) {
            $whereArr[] = ' cateCd LIKE concat(?,\'%\') ';
            $this->db->bind_param_push($arrBind, 's', $cateCdLike);
        }
        if ($setWhere) {
            $whereArr[] = $setWhere;
        }

        //관리자가 아닌경우 실행
        if (Request::getSubdomainDirectory() !== 'admin') {
            //성인인증안된경우 노출체크 상품은 노출함
            if (gd_check_adult() === false) {
                $whereArr[] = '(cateOnlyAdultFl = \'n\' OR (cateOnlyAdultFl = \'y\' AND cateOnlyAdultDisplayFl = \'y\'))';
            }

            //접근권한 체크
            if (gd_check_login()) {
                $whereArr[] = '(catePermission !=\'2\'  OR (catePermission=\'2\' AND FIND_IN_SET(\''.Session::get('member.groupSno').'\', REPLACE(catePermissionGroup,"'.INT_DIVISION.'",","))) OR (catePermission=\'2\' AND !FIND_IN_SET(\''.Session::get('member.groupSno').'\', REPLACE(catePermissionGroup,"'.INT_DIVISION.'",",")) AND catePermissionDisplayFl =\'y\'))';
            } else {
                $whereArr[] = '(catePermission IS NULL  OR catePermission=\'0\' OR (catePermission !=\'0\' AND catePermissionDisplayFl =\'y\'))';
            }
        }

        if ($setOrderBy) {
            $orderByArr[] = $setOrderBy;
        } else {
            $orderByArr[] = " cateCd ASC ";
        }
        if (count($whereArr) > 0) {
            $whereStr = " WHERE " . implode(' AND ', $whereArr);
        }
        $orderByStr = " ORDER BY " . implode(' , ', $orderByArr);

        if($cateField!='*' && strpos($cateField, 'cateCd') === false ) {
            $cateField .= ",cateCd";
        }

        $strSQL = "SELECT " . $cateField . " FROM " . $this->cateTable . $whereStr . $orderByStr;
        $getData = $this->db->slave()->query_fetch($strSQL, $arrBind);
        unset($arrBind);


        if($mallBySession) {
            if($mallBySession['sno'] != '1') { // 카테고리글로벌에서 mallSno 1 값은 제외
                $strSQLGlobal = "SELECT cateNm,cateCd FROM " . $this->cateTable . "Global  WHERE cateCd IN ('" . implode("','", array_column($getData, 'cateCd')) . "') AND mallSno = '" . $mallBySession['sno'] . "'";
                $tmpData = $this->db->query_fetch($strSQLGlobal);
                $globalData = array_combine(array_column($tmpData, 'cateCd'), $tmpData);
                if ($globalData) {
                    $getData = array_combine(array_column($getData, 'cateCd'), $getData);
                    $getData = array_values(array_replace_recursive($getData, $globalData));
                }
            }
        }


        if ($debug === true) echo $strSQL;

        return gd_htmlspecialchars_stripslashes($getData);
    }

    /**
     *글로벌 카테고리 정보 출력 - categoryGoods, categorySpecial 테이블의 정보만 출력함
     *
     * @param string $cateCd     카테고리 코드
     * @param string $debug      query문을 출력, true 인 경우 결과를 return 과 동시에 query 출력 (기본 false)
     *
     * @return array 상품 정보
     */
    public function getCategoryDataGlobal($cateCd = null)
    {
        $whereArr[] = " cateCd = '" . $cateCd . "' ";

        if (count($whereArr) > 0) {
            $whereStr = " WHERE " . implode(' AND ', $whereArr);
        }

        $arrField = DBTableField::setTableField($this->cateFuncNm.'Global',null,['cateCd']);
        $strSQL = 'SELECT ' . implode(', ', $arrField) . ' FROM ' . $this->cateTable.'Global' . $whereStr;

        $getData = $this->db->query_fetch($strSQL);

        return gd_htmlspecialchars_stripslashes($getData);
    }

    /**
     * 상품 리스트 카테고리 정보
     *
     * @param string $cateCd 카테고리 코드
     *
     * @return array 상품 정보
     */
    public function getCategoryGoodsList($cateCd,$mobileFl = 'n')
    {
        $arrBind = [];
        // 카테고리 코드 확인
        $this->getCategoryConfig($cateCd);

        // 카테고리 정보
        $this->db->strField = 'cateNm, cateCd, catePermission , catePermissionGroup, recomGoodsNo, cateHtml1, cateHtml2, cateHtml3, cateHtml1Mobile, cateHtml2Mobile, cateHtml3Mobile, pcThemeCd,mobileThemeCd,recomSortType,recomSortAutoFl,recomPcThemeCd,recomMobileThemeCd,recomDisplayFl,recomDisplayMobileFl,sortType,sortAutoFl,recomSubFl,cateOnlyAdultFl,cateOnlyAdultDisplayFl';
        $this->db->bind_param_push($arrBind, 's', $cateCd);
        $this->db->strWhere = 'cateCd = ?';

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . $this->cateTable . ' ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind, false);
        unset($arrBind);

        // 현 카테고리의 권한 정보
        $catePermission = [];
        if ($getData['catePermission'] > 0) {
            $catePermission['catePermission'] = $getData['catePermission'];
            $catePermission['catePermissionGroup'] = $getData['catePermissionGroup'];
        }

        if ($this->cateType == 'brand') {
            $cateCdField = "brandCd";
            $cateName = "브랜드";
        } else {
            $cateCdField = "cateCd";
            $cateName = "카테고리";
        }

        //성인인증안된경우 성인 인증 페이지 이동
        if ($getData['cateOnlyAdultFl'] =='y' && ((!SESSION::get(SESSION_GLOBAL_MALL) && gd_check_adult() === false) || (SESSION::get(SESSION_GLOBAL_MALL) && gd_is_login() === false))) {

            if(SESSION::get(SESSION_GLOBAL_MALL)) {
                $adultPath = (Request::isMobile() ? URI_OVERSEAS_MOBILE : URI_OVERSEAS_HOME) .'member/login.php?returnUrl=' . urlencode("/goods/goods_list.php?".$cateCdField."=" . $cateCd);
            } else {
                $adultPath = (Request::isMobile() ? URI_MOBILE : URI_HOME) .'intro/adult.php?returnUrl=' . urlencode("/goods/goods_list.php?".$cateCdField."=" . $cateCd);
            }

            header('location: '.$adultPath);
            exit;
        }

        //현재 그룹 정보
        $myGroup = Session::get('member.groupSno');
        // 현재 카테고리 권한 체크
        if (empty($catePermission) === false) {
            // 현재 카테고리 권한에 따른 정보 카테고리 체크
            if (gd_is_login() === false) {
                throw new \Exception(__($cateName.' 접근 권한이 없습니다.'));
            }

            if($catePermission['catePermission'] =='2' && $catePermission['catePermissionGroup'] && !in_array( $myGroup,explode(INT_DIVISION,$catePermission['catePermissionGroup']))) {
                throw new \Exception(__($cateName.' 접근 권한이 없습니다.'));
            }
        }

        $cateDepth = (strlen($getData['cateCd']) / $this->cateLength);


        // 부모 카테고리체크(추천상품)
        if ($cateDepth > 1) {
            $arrCateCd = [];
            for ($i = 1; $i < $cateDepth; $i++) {
                $arrCateCd[] = substr($getData['cateCd'], 0, ($i * $this->cateLength));
            }

            // 카테고리 정보
            $this->db->strField = 'recomSubFl,recomSortType,recomPcThemeCd,recomGoodsNo,recomDisplayFl,recomDisplayMobileFl,recomMobileThemeCd,cateHtml2, recomSortAutoFl';
            $this->db->strWhere = 'cateCd IN (\'' . implode('\', \'', $arrCateCd) . '\')';
            $query = $this->db->query_complete();
            $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . $this->cateTable . ' ' . implode(' ', $query);
            $data = $this->db->query_fetch($strSQL);


            foreach ($data as $key => $val) {
                //상위 추천상품 정보
                if ($val['recomSubFl'] == 'y') {
                    $getData['recomSortType'] = $val['recomSortType'];
                    $getData['recomPcThemeCd']= $val['recomPcThemeCd'];
                    $getData['recomMobileThemeCd']= $val['recomMobileThemeCd'];
                    $getData['recomGoodsNo']= $val['recomGoodsNo'];
                    $getData['recomDisplayFl']= $val['recomDisplayFl'];
                    $getData['recomDisplayMobileFl']= $val['recomDisplayMobileFl'];
                    $getData['recomSubFl']= $val['recomSubFl'];
                    $getData['cateHtml2']= $val['cateHtml2'];
                    $getData['recomSortAutoFl'] = $val['recomSortAutoFl'];
                }
            }
        }


        //하위 카테고리 권한에 의한 예외 카테고리
        if ($cateDepth != $this->cateDepth) {

            // 카테고리 정보
            $this->db->strField = 'cateCd,catePermission,catePermissionGroup';
            $this->db->strWhere =  'cateCd LIKE \'' . $cateCd . '%\'';
            $query = $this->db->query_complete();
            $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . $this->cateTable . ' ' . implode(' ', $query);
            $data = $this->db->query_fetch($strSQL);

            foreach ($data as $key => $val) {
                //권한정보
                if ($val['catePermission'] > 0) {

                    // 현재 카테고리 권한에 따른 정보 카테고리 체크
                    if (gd_is_login() === false) {
                        $expectCate[] = $val['cateCd'];
                    } else if ($val['catePermissionGroup'] && $val['catePermission'] =='2' && !in_array( $myGroup,explode(INT_DIVISION,$val['catePermissionGroup']))) {
                        $expectCate[] = $val['cateCd'];
                    }
                }
            }
        }
        if($expectCate) {
            $getData = array_merge($getData, (array) array('expectCate'=>$expectCate));
        }

        // 테마정보
        $displayConfig = \App::load('\\Component\\Display\\DisplayConfig');

        if($mobileFl =='y') {
            $themeCd = $getData['mobileThemeCd'];
        } else {
            $themeCd = $getData['pcThemeCd'];
        }

        if (empty($themeCd) === true)
        {
            if ($this->cateType == 'goods') {
                $tData = $displayConfig->getInfoThemeConfigCate('E', $mobileFl)[0];
            } else {
                $tData = $displayConfig->getInfoThemeConfigCate('C', $mobileFl)[0];
            }
        } else {
            $tData = $displayConfig->getInfoThemeConfig($themeCd);
        }

        if ($tData['detailSet']) $tData['detailSet'] = unserialize($tData['detailSet']);
        $tData['displayField'] = explode(",", $tData['displayField']);
        $tData['goodsDiscount'] = explode(",", $tData['goodsDiscount']);
        $tData['priceStrike'] = explode(",", $tData['priceStrike']);
        $tData['displayAddField'] = explode(",", $tData['displayAddField']);

        $getData = array_merge($getData, (array) $tData);

        // 추천상품 테마정보
        if (empty($getData['recomGoodsNo']) === false) {
            if($mobileFl =='y') $getData['recomTheme'] = $displayConfig->getInfoThemeConfig($getData['recomMobileThemeCd']);
            else $getData['recomTheme'] = $displayConfig->getInfoThemeConfig($getData['recomPcThemeCd']);
            $getData['recomTheme']['displayField'] = explode(",", $getData['recomTheme']['displayField']);
        }

        // 카테고리 내 추천 상품 설정 페이지 제한 삭제
        if (empty($getData['recomGoodsNo']) || gd_isset($getData['recomFl']) == 'n') {
            $getData['recomGoodsNo'] = null;
        }

        if($getData['cateHtml1'] =='<p>&nbsp;</p>') unset($getData['cateHtml1']);
        if($getData['cateHtml2'] =='<p>&nbsp;</p>') unset($getData['cateHtml2']);
        if($getData['cateHtml3'] =='<p>&nbsp;</p>') unset($getData['cateHtml3']);
        if($getData['cateHtml1Mobile'] =='<p>&nbsp;</p>') unset($getData['cateHtml1Mobile']);
        if($getData['cateHtml2Mobile'] =='<p>&nbsp;</p>') unset($getData['cateHtml2Mobile']);
        if($getData['cateHtml3Mobile'] =='<p>&nbsp;</p>') unset($getData['cateHtml3Mobile']);

        return gd_htmlspecialchars_stripslashes(gd_isset($getData));
    }

    /**
     * 카테고리 테마 정보
     * @author sunny
     *
     * @param $themeId 테마아이디
     *
     * @return array 해당 카테고리 테마 정보
     * @deprecated 2017-05-22 atomyang 미사용. 추후 삭제 예정
     */
    public function getCategoryTheme($themeId)
    {
        $arrField = DBTableField::setTableField('tableCategoryTheme');
        $strSQL = 'SELECT ' . implode(', ', $arrField) . ' FROM ' . DB_CATEGORY_THEME . ' WHERE themeId = ? AND cateType = ?';
        $this->db->bind_param_push($arrBind, 's', $themeId);
        $this->db->bind_param_push($arrBind, 's', $this->cateType);
        $getData = $this->db->query_fetch($strSQL, $arrBind, false);
        if (count($getData) > 0) {
            $getData = gd_htmlspecialchars_stripslashes($getData);
        }

        return $getData;
    }

    /**
     * 카테고리 정보
     *
     * @param string  $cateCd     카테고리 코드
     * @param integer $depth      출력 depth
     * @param boolean $division   구분자 출력 여부
     * @param boolean $goodsCntFl 상품수 출력 여부
     * @param boolean $userMode   사용자 화면 출력 (기본 false)
     * @param boolean $displayFl  노출여부와 상관없이 보이게 (기본 false)
     * @param boolean $selectboxImgFl  셀렉트박스에서 이미지 출력여부 (기본 true)
     *
     * @return string 카테고리 정보
     */
    public function getCategoryCodeInfo($cateCd = null, $depth = null, $division = true, $goodsCntFl = false, $userMode = null, $displayFl = false, $selectboxImgFl = true)
    {
        $mallBySession = SESSION::get(SESSION_GLOBAL_MALL);
        gd_isset($mallBySession['sno'],DEFAULT_MALL_NUMBER);

        $arrWhere = [];

        if (is_null($cateCd) === true) {
            $startDepth = 0;
        } else {
            $startDepth = strlen($cateCd) - $this->cateLength;
            $arrWhere[] = 'cg.cateCd LIKE concat(?,\'%\')';
            $this->db->bind_param_push($arrBind, 's', $cateCd);

        }

        if (is_null($depth) === false && is_numeric($depth)) {
            $depth = min($depth, 4); //출력Depth가 4차를 넘지 않도록 설정
            $arrWhere[] = 'length(cg. cateCd ) <= ' . (($depth * $this->cateLength) + $startDepth);
        }

        if ($division === false) {
            $arrWhere[] = 'cg.divisionFl = \'n\'';
        }

        if (is_null($userMode) === false) {

            if(Request::isMobile())  $cateDisplayMode = "cateDisplayMobileFl";
            else $cateDisplayMode = "cateDisplayFl";

            // 카테고리 네비게이션 영역 형태 노출용 (감추기를 해도 해당 영역은 나오게)
            if ($displayFl === true && is_null($cateCd) === false) {
                $arrWhere[] = '(cg.cateCd = \'' . $cateCd . '\' OR '.$cateDisplayMode.' = \'y\')';
            } else {
                $arrWhere[] = $cateDisplayMode.' = \'y\'';
            }
        }

        $this->db->strOrder = 'cateCd ASC';

        $arrWhere[] = 'FIND_IN_SET(?,mallDisplay) ';
        $this->db->bind_param_push($arrBind, 's', $mallBySession['sno']);

        //성인인증안된경우 노출체크 상품은 노출함
        if (gd_check_adult() === false) {
            $arrWhere[] = '(cateOnlyAdultFl = \'n\' OR (cateOnlyAdultFl = \'y\' AND cateOnlyAdultDisplayFl = \'y\'))';
        }

        //접근권한 체크
        if (gd_check_login()) {
            $arrWhere[] = '(cg.catePermission !=\'2\'  OR (cg.catePermission=\'2\' AND FIND_IN_SET(\''.Session::get('member.groupSno').'\', REPLACE(cg.catePermissionGroup,"'.INT_DIVISION.'",","))) OR (cg.catePermission=\'2\' AND !FIND_IN_SET(\''.Session::get('member.groupSno').'\', REPLACE(cg.catePermissionGroup,"'.INT_DIVISION.'",",")) AND cg.catePermissionDisplayFl =\'y\'))';
        } else {
            $arrWhere[] = '(cg.catePermission IS NULL OR cg.catePermission=\'0\' OR (cg.catePermission !=\'0\' AND cg.catePermissionDisplayFl =\'y\'))';
        }

        $this->db->strField = 'cg.cateNm, cg.cateCd, cg.cateSort, cg.divisionFl, cg.catePermission, cg.cateImg, cg.cateOverImg, cg.cateImgMobile, cg.cateImgMobileFl, cg.cateImgMobileFl,cg.catePermissionDisplayFl,cg.catePermissionGroup,cg.catePermissionSubFl';
        $this->db->strWhere = implode(' AND ', $arrWhere);
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . $this->cateTable . ' as cg' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, gd_isset($arrBind));


        // 유저 모드인 경우 카테고리 접속 권한 처리

        /**해외몰 관련 **/
        if ($mallBySession['sno'] != DEFAULT_MALL_NUMBER) {
            $arrFieldGoodsGlobal = DBTableField::setTableField($this->cateFuncNm.'Global',null,['mallSno']);
            $strSQLGlobal = "SELECT cgg." . implode(', cgg.', $arrFieldGoodsGlobal) . " FROM ".$this->cateTable."Global as cgg WHERE cgg.cateCd IN ('".implode("','",array_column($getData, 'cateCd'))."') AND cgg.mallSno = '".$mallBySession['sno']."'";
            $tmpData = $this->db->query_fetch($strSQLGlobal);
            $globalData = array_combine (array_column($tmpData, 'cateCd'), $tmpData);
        }

        if (is_null($userMode) === false  && empty($getData) === false) {
            // 회원 그룹 정보
            $myGroup = Session::get('member.groupSno');

            // 카테고리체크 배열
            $chkCateCd = [];

            // 권한에 따른 정보 카테고리 체크
            foreach ($getData as $key => &$val) {

                if($mallBySession && $globalData[$val['cateCd']]) {
                    $val = array_replace_recursive($val, array_filter(array_map('trim',$globalData[$val['cateCd']])));
                }

                // 상위 분류가 없는 카테고리값 유무 체크 (전체 카테고리 로드시)
                if (is_null($cateCd) === true) {
                    $chkCateCd[] = $val['cateCd'];

                    if ((strlen($val['cateCd']) - $this->cateLength) > 1 && in_array(substr($val['cateCd'], 0, ($this->cateLength) * -1), $chkCateCd) === false) {
                        unset($getData[$key]);
                        array_splice($chkCateCd, -1,1);
                        continue;
                    }
                }

            }
        }

        // 상품 갯수 출력일 경우
        if ($goodsCntFl === true && empty($getData) === false && is_null($cateCd) === false) {
            // 연결된 상품의 개수 출력
            $goods = \App::load('\\Component\\Goods\\Goods');
            $goodsCnt = $goods->getGoodsLinkCnt($cateCd, 'all', $this->cateType, 'user');

            // 기존 카테고리 데이타에 상품 갯수 추가
            foreach ($getData as $key => & $val) {
                $val['goodsCnt'] = gd_isset($goodsCnt[$val['cateCd']], 0);
            }
        }

        if (empty($getData) === false) {
            return $this->getTreeArray($getData, $goodsCntFl,$selectboxImgFl);
        } else {
            return false;
        }
    }

    /**
     * 카테고리 정보를 배열 형태로 출력
     *
     * @param array $data 카테고리 정보
     *
     * @return string 배열 형태의 카테고리 트리 정보
     */
    public function getTreeArray($data, $goodsCntFl = false,$selectboxImgFl = true)
    {
        if(Request::request()->has('imageFl')  && Request::request()->get('imageFl') =='n') $imageFl = false;
        else $imageFl = true;

        $jsonVar = [];
        $jsonArr = [];
        $cateLength = $this->cateLength;
        foreach ($data as $key => $val) {
            $jsonArr['cateCd'] = $val['cateCd'];
            $jsonArr['cateOverImg'] = $val['cateOverImg'];
            $jsonArr['divisionFl'] = $val['divisionFl'];
            if(Request::isMobile())  $val['cateImg'] = $val['cateImgMobile'];
            if ($goodsCntFl === true) {
                $jsonArr['goodsCnt'] = gd_isset($val['goodsCnt'], 0);
            }
            if (!$val['cateNm']) {
                $val['cateNm'] = '_no_name_';
            }
            if($val['cateImg'] && $imageFl && $selectboxImgFl) {
                if($val['cateOverImg']) $jsonArr['cateNm'] = "<img data-other-src='/data/category/".$val['cateOverImg']."' src='/data/category/".$val['cateImg']."' class='gd_menu_over'>";
                else $jsonArr['cateNm'] = "<img src='/data/category/".$val['cateImg']."'>";
            } else {
                if($val['cateOverImg'] && $imageFl)  $jsonArr['cateNm'] = "<span  data-other-src='/data/category/".$val['cateOverImg']."' data-other-text='".strip_tags(stripcslashes($val['cateNm']))."' class='gd_menu_over'>".strip_tags(stripcslashes($val['cateNm']))."</span>";
                else $jsonArr['cateNm'] = strip_tags(stripcslashes($val['cateNm']));
            }

            $tmp['Length'] = strlen($val['cateCd']);            // 현재 카티고리 길이

            if ($key == 0) {
                if ($tmp['Length'] == $this->cateLength) {
                    $cateLength = $this->cateLength;
                } else {
                    $cateLength = $tmp['Length'];
                }
            }

            // 1차 카테고리 인경우
            if ($tmp['Length'] == $cateLength) {
                $tmp['Info'][1] = &$jsonVar[$val['cateSort']][];
                $tmp['Info'][1] = $jsonArr;
                $tmp['Node'][1] = $val['cateCd'];
                // 1차 이상의 카테고리 인경우
            } else {
                $tmp['Chk1'] = ($tmp['Length'] - $cateLength) / $this->cateLength;
                $tmp['Chk2'] = $tmp['Chk1'] + 1;
                if (isset($tmp['Info'][$tmp['Chk1']]) === true && isset($tmp['Node'][$tmp['Chk1']]) === true) {
                    if ($tmp['Info'][$tmp['Chk1']]['cateCd'] == $tmp['Node'][$tmp['Chk1']]) {
                        $tmp['Info'][$tmp['Chk2']] = &$tmp['Info'][$tmp['Chk1']]['children'][$val['cateSort']][];
                        $tmp['Info'][$tmp['Chk2']] = $jsonArr;
                    }
                }
                $tmp['Node'][$tmp['Chk2']] = $val['cateCd'];
            }
        }

        return $this->sortCategoryJson($jsonVar);
    }

    /**
     * JSON 형식으로 카테고리 정렬
     *
     * @param array $arr 카테고리 정보
     *
     * @return array JSON 형태의 카테고리 트리 정보
     */
    protected function sortCategoryJson($arrData)
    {
        // 카테고리 정보가 없는경우 리턴
        if (empty($arrData) === true || is_array($arrData) === false) {
            return;
        }

        $arrData = $this->sortCategoryTree($arrData);

        foreach ($arrData as $key => $val) {
            if (gd_isset($val['children'])) {
                $arrData[$key]['children'] = self::sortCategoryJson($val['children']);
            }
        }

        return $arrData;
    }

    /**
     * 카테고리 배열 순서 재정의
     *
     * @param array $arr 카테고리 정보
     *
     * @return array 재정의된 카테고리 정보
     */
    protected function sortCategoryTree($arrData)
    {
        ksort($arrData);
        foreach ($arrData as $val) {
            foreach ($val as $tVal) {
                $data[] = $tVal;
            }
        }

        return $data;
    }

    /**
     * 해당 카테고리의 노출상점 정보 출력
     *
     * @param string  $cateCd      카테고리 코드
     * @param string  $removeDepth 제외할 카테고리 이름 (기본 0)
     * @param string  $arrow       카테고리 이름간의 화살표 (기본 &gt; )
     * @param boolean $linkFl      카테고리 링크 여부 (기본 false)
     *
     * @return string 카테고리의 현재위치
     */
    public function getCategoryFlag($cateCd)
    {
        $useMallList = array_combine(array_column($this->gGlobal['useMallList'], 'sno'), $this->gGlobal['useMallList']);
        $whereArr[] = " ca.cateCd = '" . $cateCd . "' ";
        $whereStr = " WHERE " . implode(' AND ', $whereArr);

        $strSQL = 'SELECT mallDisplay FROM ' . $this->cateTable.' as ca'. $whereStr;
        $getData = $this->db->query_fetch($strSQL,null,false);

        $dataFlag = [];
        foreach(explode(",",$getData['mallDisplay']) as $k => $v) {
            if($useMallList[$v]) $dataFlag[$useMallList[$v]['domainFl']] = $useMallList[$v]['mallName'];
        }

        return gd_htmlspecialchars_stripslashes($dataFlag);
    }


    /**
     * 해당 카테고리의 현재위치
     *
     * @param string  $cateCd      카테고리 코드
     * @param string  $removeDepth 제외할 카테고리 이름 (기본 0)
     * @param string  $arrow       카테고리 이름간의 화살표 (기본 &gt; )
     * @param boolean $linkFl      카테고리 링크 여부 (기본 false)
     *
     * @return string 카테고리의 현재위치
     */
    public function getCategoryPosition($cateCd, $removeDepth = 0, $arrow = ' &gt; ', $linkFl = false,$viewFl = true)
    {
        $thisCateDepth = strlen($cateCd) / $this->cateLength;

        $_tmp = [];
        for ($i = 1; $i < $thisCateDepth; $i++) {
            $_tmp[] = " left('" . $cateCd . "'," . ($this->cateLength * $i) . ") ";
        }
        $_tmp[] = "'" . $cateCd . "'";
        $inStr = "cateCd in (" . implode(',', $_tmp) . ")";
        unset($_tmp);

        if (empty($inStr)) {
            return false;
        }

        if($viewFl && Session::has('manager.managerId') === null) {
            if(Request::isMobile())  $cateDisplayMode = "cateDisplayMobileFl";
            else $cateDisplayMode = "cateDisplayFl";
            $inStr .= ' AND '.$cateDisplayMode.' = \'y\'';
        }

        $data = $this->getCategoryData(null, null, 'cateCd, cateNm', $inStr, 'cateCd ASC');

        if ($this->cateType == 'brand') {
            $cateType = 'brandCd';
        } else {
            $cateType = 'cateCd';
        }

        foreach ($data as $key => $val) {
            if ($key >= $removeDepth) {
                if ($linkFl === true) {
                    $_tmp[] = '<a href="../goods/goods_list.php?' . $cateType . '=' . $val['cateCd'] . '">' . strip_tags($val['cateNm']) . '</a>';
                } else {
                    $_tmp[] = strip_tags($val['cateNm']);
                }
            }
        }

        if (isset($_tmp)) {
            return implode($arrow, $_tmp);
        } else {
            return false;
        }
    }

    /**
     * 해당 카테고리의 현재위치
     *
     * @author sj
     *
     * @param mixed  $cateCd   카테고리 코드(또는 코드 배열)
     * @param string $cateType 카테고리 타입(상품, 브랜드)
     * @param class  $db       db 클래스
     *
     * @return array 카테고리의 현재위치
     */
    static public function getCategoriesPosition($cateCd, $cateType = null, &$db = null)
    {
        switch ($cateType) {
            case 'brand' : {
                $cateTable = DB_CATEGORY_BRAND;
                $cateLength = DEFAULT_LENGTH_BRAND;
                break;
            }
            default: {
                $cateTable = DB_CATEGORY_GOODS;
                $cateLength = DEFAULT_LENGTH_CATE;
            }
        }
        if (!is_object($db)) {
            $db = \App::load('DB');
        }

        if (empty($cateCd) === false && !is_array($cateCd)) {
            $arrCateCd[] = $cateCd;
        } else {
            $arrCateCd = &$cateCd;
        }

        if (ArrayUtils::isEmpty($arrCateCd) === true) return false;

        foreach ($arrCateCd as $val) {
            $tmpSplit = str_split($val, $cateLength);
            $tmpCate = '';
            for ($i = 0; $i < count($tmpSplit); $i++) {
                $tmpCate .= $tmpSplit[$i];
                $tmpArr[] = $tmpCate;
            }
        }

        $tmpArr = array_unique($tmpArr);
        foreach ($tmpArr as $val) {
            $arrWhere[] = '?';
            $db->bind_param_push($arrBind, 's', $val);
        }
        unset($tmpArr);

        $res = $db->query_fetch("SELECT cateNm, cateCd FROM " . $cateTable . " WHERE cateCd in (" . implode(',', $arrWhere) . ")", $arrBind);
        unset($arrBind);
        $res = gd_htmlspecialchars_stripslashes($res);
        foreach ($res as $val) {
            $arrCategory[$val['cateCd']] = $val['cateNm'];
        }
        unset($res);

        foreach ($arrCateCd as $val) {
            $tmpArr = [];
            $tmpSplit = str_split($val, $cateLength);
            $tmpCate = '';
            for ($i = 0; $i < count($tmpSplit); $i++) {
                $tmpCate .= $tmpSplit[$i];
                $tmpArr = $tmpArr + [$tmpCate => $arrCategory[$tmpCate]];
            }
            $resCategory[] = $tmpArr;
            unset($tmpArr);
        }
        unset($arrBind);
        unset($arrWhere);
        unset($arrCategory);

        return $resCategory;
    }

    /**
     * 카테고리 권한에 따른 카테고리 코드 출력
     *
     * @param string $cateCd 카테고리 코드
     *
     * @return string 권한이 있는 카테고리 코드
     */
    public function setCategoryPermission($cateCd = null)
    {
        $mallBySession = SESSION::get(SESSION_GLOBAL_MALL);
        gd_isset($mallBySession['sno'],DEFAULT_MALL_NUMBER);


        // 카테고리 정보
        $this->db->strField = 'cateCd, catePermission, catePermissionGroup,mallDisplay';
        if (is_null($cateCd) === false) {
            $this->db->bind_param_push($arrBind, 's', $cateCd);
            $arrWhere[] = 'cateCd LIKE concat(?,\'%\')';
        }

        //현재몰관련
        $arrOrWhere[] = '!FIND_IN_SET('.$mallBySession['sno'].',mallDisplay)';
        //권한관련
        $arrOrWhere[] = 'catePermission > 0';

        $arrWhere[] = "(".implode(' OR ', $arrOrWhere).")";

        $this->db->strWhere = implode(' AND ', $arrWhere);
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . $this->cateTable . ' ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, gd_isset($arrBind));
        unset($arrBind);

        // 권한 설정 카테고리가 없는 경우 리턴
        if (empty($getData) === true) {
            return;
        }

        // 회원 그룹 정보
        $memberGroup = \App::load('\\Component\\Member\\MemberGroup');
        $groupInfo = $memberGroup->getGroupSno();
        $myGroup = Session::get('member.groupSno');

        // 권한 설정이 되어 있는 카테고리 검색을 위한
        $arrCateCd = [];
        if (gd_is_login() === false) {
            foreach ($getData as $val) {
                $arrCateCd[] =  $val['cateCd'];
            }
        } else {
            foreach ($getData as $val) {

                if(count(array_intersect(array_keys($groupInfo), explode(INT_DIVISION,$val['catePermissionGroup']))) && ($val['catePermission'] == 2 && !in_array($myGroup, explode(INT_DIVISION,$val['catePermissionGroup'])))) {
                    $arrCateCd[] = $val['cateCd'];
                }

                if(!in_array($mallBySession['sno'],explode(",",$val['mallDisplay']))){
                    $arrCateCd[] = $val['cateCd'];
                }
            }
        }

        unset($getData);

        // 권한 설정 카테고리가 없는 경우 리턴
        if (empty($arrCateCd) === true) {
            return;
        }

        /*
        // 서브 카테고리 정보
        $this->db->strField = 'cateCd';
        $this->db->strWhere = implode(' OR ', $arrCateCd);
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . $this->cateTable . ' ' . implode(' ', $query);
        $result = $this->db->query($strSQL);

        while ($data = $this->db->fetch($result)) {
            $getData[] = $data['cateCd'];
        }

        $getData = ArrayUtils::removeEmpty(array_unique($getData)); */

        return $arrCateCd;
    }

    /**
     * 다중 카테고리 select box 출력
     *
     * @param string  $selectID    select box 아이디 (기본 null)
     * @param string  $selectValue selected 된 카테고리 코드
     * @param string  $strStyle    select box style (기본 null)
     * @param boolean $userMode    사용자 화면 출력 (기본 false)
     *
     * @return string 다중 카테고리 select box
     */
    public function getMultiCategoryBox($selectID = null, $selectValue = null, $strStyle = null, $userMode = false, $isMobile = false)
    {
        if($userMode) {
            $defaultUrl = '../share/category_select_json.php';
        } else {
            $defaultUrl = '/share/category_select_json.php';
        }


        // 상품 카테고리
        if ($userMode === true) {
            if(Request::isMobile())  $cateDisplayMode = "cateDisplayMobileFl";
            else $cateDisplayMode = "cateDisplayFl";
            $userWhere = ' AND '.$cateDisplayMode.' = \'y\'';
            $jsonParam = 'userMode=y';
        }
        $whereStr = 'length(cateCd) = \'' . $this->cateLength . '\' AND divisionFl = \'n\'' . gd_isset($userWhere);

        $tmpData[] = $this->getCategoryData(null, null, 'mallDisplay,cateCd,cateNm,catePermission,catePermissionGroup,cateOnlyAdultFl', $whereStr, 'cateSort asc');
        // selectValue 값이 배열일 경우 마지막 값으로 설정
        if(is_array($selectValue)){
            $selectValue = ArrayUtils::last($selectValue);
        }

        if (gd_isset($selectValue)) {
            $depth = strlen($selectValue) / $this->cateLength;
            for ($i = 0; $i <= $depth; $i++) {
                $tmpLength = (($this->cateLength * $i) + $this->cateLength);
                $tmpValue[$i] = substr($selectValue, 0, $tmpLength);
                if ($i == 0) {
                    continue;
                }
                $whereStr = 'cateCd LIKE \'' . substr($selectValue, 0, ($tmpLength - $this->cateLength)) . '%\' AND length(cateCd) = \'' . $tmpLength . '\' AND divisionFl = \'n\'' . gd_isset($userWhere);
                $tmpData[] = $this->getCategoryData(null, null, 'cateCd,cateNm', $whereStr, 'cateSort asc');
            }
        }

        //--- 카테고리 타입에 따른 설정 (상품,브랜드)
        if ($this->cateType == 'goods') {
            $tmpTitle = __('카테고리');
            $tmpName = 'cateGoods';
            $tmpUrl = $defaultUrl . (isset($jsonParam) === true ? '?' . $jsonParam : '');
        } else {
            $tmpTitle = __('브랜드');
            $tmpName = $this->cateType;
            $tmpUrl = $defaultUrl . '?cateType=' . $this->cateType . (isset($jsonParam) === true ? '&' . $jsonParam : '');
        }

        //--- select box ID 설정
        if (is_null($selectID) === false) {
            $tmpName = $selectID;
        }

        return $this->setMultiSelectBox($tmpName, $tmpData, gd_isset($tmpValue), $this->cateDepth, $tmpUrl, '=' . $tmpTitle . __('선택').'=', $strStyle,$isMobile);
    }

    /**
     * 멀티 셀렉트 박스
     *
     * @author artherot
     *
     * @param string  $inputID   select box ID
     * @param array   $arrData   기본 적으로 출력할 select box 의 배열 값
     * @param array   $arrValue  각 select box 의 selected 값
     * @param integer $selectCnt select box 총 갯수
     * @param string  $ajexUrl   다음 select box 값을 가지고오기 위한 jquery post URL
     * @param string  $strTitle  select box 첫번째 option의 타이틀 명
     * @param string  $addStyle  select box 의 스타일 (style, multiple, size, onchange 등등의) (default = null)
     *
     * @return string select box
     */
    protected function setMultiSelectBox($inputID, $arrData, $arrValue = null, $selectCnt, $ajexUrl, $strTitle = '---', $addStyle = null,$isMobile= false)
    {
        $useMallList = array_combine(array_column($this->gGlobal['useMallList'], 'sno'), $this->gGlobal['useMallList']);

        $useModeFl = false;
        //관리자가 아닌경우 실행
        if (Request::getSubdomainDirectory() !== 'admin') {
            $useModeFl = true;
        }

        //현재 그룹 정보
        $myGroup = Session::get('member.groupSno');


        $tmp = '';
        $tmpValue = [];
        for ($i = 0; $i < $selectCnt; $i++) {
            $inputNo = $i + 1;
            if($isMobile) {
                $tmp.='<div class="inp_sel" style="margin-top:10px">'.chr(10);
            }
            if(gd_is_skin_division()) {
                $tmp.='<div class="select_box">'.chr(10);
                $selectClass = "chosen-select";
            } else {
                $selectClass = "form-control multiple-select";
            }
            if (!gd_is_skin_division() && $addStyle == 'addDiv'){
                $tmp .= '<div>'.chr(10);
            }
            $tmp .= '<select id="' . $inputID . $inputNo . '" name="'.$inputID.'[]" ' . $addStyle . ' class="'.$selectClass.'">' . chr(10);
            $tmp .= '<option value="">' . $strTitle . '</option>' . chr(10);
            if (gd_isset($arrData[$i])) {
                foreach ($arrData[$i] as $key => $val) {

                    $disabledFl = false;

                    if ($val['cateOnlyAdultFl'] =='y' && gd_check_adult() === false) {
                        $disabledFl = true;
                    }

                    // 현재 카테고리 권한 체크
                    if ($val['catePermission'] > 0) {
                        // 현재 카테고리 권한에 따른 정보 카테고리 체크
                        if (gd_is_login() === false) {
                            $disabledFl = true;
                        }

                        if($val['catePermission'] =='2' && $val['catePermissionGroup'] && !in_array( $myGroup,explode(INT_DIVISION,$val['catePermissionGroup']))) {
                            $disabledFl = true;
                        }
                    }
                    $disabledStr = "";
                    if($useModeFl && $disabledFl) {
                        $disabledStr = "disabled='disabled'";
                    }

                    foreach(explode(",",$val['mallDisplay']) as $k1 => $v1) {
                        if($useMallList[$v1]) {
                            $mallSno[$k1] = $useMallList[$v1]['domainFl'];
                            $mallName[$k1] = $useMallList[$v1]['mallName'];
                        }
                    }
                    $tmp .= '<option value="' . $val['cateCd'] . '" '.$disabledStr.' data-flag="'.implode(",",$mallSno).'" data-mall-name="'.implode(",",$mallName).'">' . StringUtils::htmlSpecialChars($val['cateNm']) . '</option>' . chr(10);
                    unset($mallSno);
                    unset($mallName);
                }
            }
            $tmp .= '</select>' . chr(10);
            if (!gd_is_skin_division() && $addStyle == 'addDiv'){
                $tmp .= '</div>'.chr(10);
            }
            if(gd_is_skin_division()) {
                $tmp.='</div>'.chr(10);
            }
            if($isMobile) {
                $tmp.='</div>'.chr(10);
            }
            $tmpBox[] = '$(\'#' . $inputID . $inputNo . '\').multi_select_box(\'#' . $inputID . '\',' . $selectCnt . ',\'' . $ajexUrl . '\',\'' . $strTitle . '\');';
            if (gd_isset($arrValue[$i])) {
                $tmpValue[] = "$('#" . $inputID . $inputNo . " option[value=\'" . $arrValue[$i] . "\']').attr('selected','selected');";
            }
        }

        $tmp .= '<script type="text/javascript">' . chr(10);
        $tmp .= '$(function() {' . chr(10);
        $tmp .= '	' . implode(chr(10) . '	', $tmpBox) . chr(10);
        $tmp .= '});' . chr(10);
        $tmp .= implode(chr(10), $tmpValue) . chr(10);
        $tmp .= '</script>' . chr(10);

        return $tmp;
    }


    /**
     * 사용자 상품 카테고리 출력
     *
     * @param mixed  $cateCd   카테고리 코드(또는 코드 배열)
     * @param string $cateType 카테고리 타입(상품, 브랜드)
     * @param class  $db       db 클래스
     *
     * @return array 카테고리의 현재위치
     */
    public function getCategories($cateCd, $cateType = null, &$db = null)
    {
        $mallBySession = SESSION::get(SESSION_GLOBAL_MALL);
        gd_isset($mallBySession['sno'],DEFAULT_MALL_NUMBER);

        switch ($cateType) {
            case 'brand' : {
                $cateLength = DEFAULT_LENGTH_BRAND;
                break;
            }
            default: {
                $cateLength = DEFAULT_LENGTH_CATE;
            }
        }
        if (!is_object($db)) {
            $db = \App::load('DB');
        }

        $userWhere = ' AND cateDisplayFl = \'y\'';
        $tmpSplit = str_split($cateCd, $cateLength);
        $tmpCate = '';
        for ($i = 0; $i < count($tmpSplit); $i++) {
            $tmpCate .= $tmpSplit[$i];
            $tmpLength = (($this->cateLength * ((strlen($tmpCate) / $this->cateLength) - 1)) + $this->cateLength);

            $whereStr = 'cateCd LIKE \'' . substr($tmpCate, 0, ($tmpLength - $this->cateLength)) . '%\' AND length(cateCd) = \'' . $tmpLength . '\' AND divisionFl = \'n\'' . gd_isset($userWhere);

            //현재 상점몰을 기준으로 카테고리 리스트 가져오기. - 2018.10.15 parkjs
            $whereStr .= ' AND FIND_IN_SET(' . $mallBySession['sno'] . ', mallDisplay) ';
            $tmpData = $this->getCategoryData(null, null, 'cateCd,cateNm', $whereStr, 'cateSort asc');
            $cate = [];
            foreach ($tmpData as $k => $v) {
                $cate['data'][$v['cateCd']] = $v['cateNm'];
                if ($v['cateCd'] == $tmpCate) $cate['cateNm'] = $v['cateNm'];
            }
            $resCategory[$tmpCate] = $cate;


        }

        return $resCategory;
    }

    /**
     * 상품 기본 정렬 방법
     * 전역에서 설정된 부분 함수화
     *
     * @return array 정렬리스트
     */
    public function getSort()
    {
        return [
            'sort desc'          => __('정렬순↑'),
            'sort asc'           => __('정렬순↓'),
            'g.goodsNm desc'     => __('상품명순↑'),
            'g.goodsNm asc'      => __('상품명순↓'),
            'go.goodsPrice desc' => __('가격순↑'),
            'go.goodsPrice asc'  => __('가격순↓'),
            'go.mileage desc'    => __('마일리지순↑'),
            'go.mileage asc'     => __('마일리지순↓'),
            'g.makerNm desc'     => __('제조사순↑'),
            'g.makerNm asc'      => __('제조사순↓'),
            'g.regDt desc'       => __('등록일↑'),
            'g.regDt asc'        => __('등록순↓'),
        ];
    }

    /**
     * 카테고리 코드, 브랜드 코드 확인
     * @param $cateCd 카테고리 코드
     * @throws \Exception
     */
    public function getCategoryConfig($cateCd) {

        $mallBySession = SESSION::get(SESSION_GLOBAL_MALL);
        gd_isset($mallBySession['sno'],DEFAULT_MALL_NUMBER);

        $appendQuery = "WHERE cateCd = '" . $cateCd . "' AND FIND_IN_SET(".$mallBySession['sno'].",mallDisplay)";

        $dataCnt = $this->db->getCount($this->cateTable, '*', $appendQuery);
        if((int)$dataCnt === 0) {
            throw new \Exception(__('잘못된 접근입니다.'));
        }

    }

    /**
     * 상품에 연결된 전체 카테고리 정보 확인
     * @param $goodsNo 상품번호
     * @param $type 테이블정보
     * @param $cateLinkFl 상품에 연결된 상태 여부
     *
     * @return $data 카테고리번호
     */
    public function getCateCd($goodsNo, $type = 'category', $cateLinkFl = false)
    {
        $retData = [];
        $useTable = DB_GOODS_LINK_CATEGORY;
        if ($type == 'brand') {
            $useTable = DB_GOODS_LINK_BRAND;
        }

        $arrField = DBTableField::setTableField('tableGoodsLinkBrand',['cateCd']);
        $arrBind = $arrWhere = [];

        $arrWhere[] = '`goodsNo` = ?';
        $this->db->bind_param_push($arrBind, 's', $goodsNo);

        if($cateLinkFl) {
            $arrWhere[] = '`cateLinkFl` = ?';
            $this->db->bind_param_push($arrBind, 's', $cateLinkFl);
        }

        $this->db->strField = implode(', ', $arrField);
        $this->db->strWhere = implode(' AND ', gd_isset($arrWhere));
        $this->db->strOrder = 'sno ASC';

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . $useTable . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $arrBind, true);

        foreach ($data as $value) {
            $retData[] = $value['cateCd'];
        }
        return $retData;
    }

    /**
     * 카테고리에서 사용하는 storage 반환
     *
     * @return \Bundle\Component\Storage\LocalStorage
     */
    protected function storage()
    {
        if ($this->storage == null) {
            $this->storage = Storage::disk(Storage::PATH_CODE_CATEGORY,'local');
        }

        return $this->storage;
    }

    /**
     * 카테고리 차수 설정
     *
     */
    public function setCateDepth($depth)
    {
        $this->cateDepth = $depth;

    }
}
