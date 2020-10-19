<?php
/**
 * 상품 보관함 class
 *
 * @author    artherot
 * @version   1.0
 * @since     1.0
 * @copyright ⓒ 2016, NHN godo: Corp.
 */
namespace Bundle\Component\Wish;

use Component\Cart\CartAdmin;
use Component\Goods\Goods;
use Component\GoodsStatistics\GoodsStatistics;
use Component\Member\Util\MemberUtil;
use Component\Database\DBTableField;
use Framework\Utility\ArrayUtils;
use Framework\Utility\SkinUtils;
use Component\Validator\Validator;
use Component\Mall\Mall;
use Session;
use Exception;
use App;

class Wish
{
    const ERROR_VIEW = 'ERROR_VIEW';

    const TEXT_NOT_EXIST_GOODSNO = 'NOT_EXIST_GOODSNO';

    const TEXT_NOT_EXIST_OPTIONSNO = 'NOT_EXIST_OPTIONSNO';

    const TEXT_LIMIT_GOODS_CNT = 'WISH_GOODS_CNT';

    const TEXT_GOODS_EXIST = 'WISH_GOODS_EXIST';

    const TEXT_NOT_SELECT_GOODS = 'NOT_SELECT_GOODS';

    const TEXT_LOGIN_CHECK = 'LOGIN_CHECK';

    const POSSIBLE_SOLD_OUT = 'SOLD_OUT';// 재고 없음 체크

    protected $db;

    public $wishCnt = 0;
    // 상품 보관함 갯수
    protected $wishConf;
    // 상품 보관함 설정 값
    protected $tax;
    // 과세/비과세 설정 값
    protected $goodsDisplayFl = 'goodsDisplayFl';
    // 일반샵과 모바일샵 상품 출력 구분을 위한

    /**
     * @var array 관심상품 SCM 업체의 상품 갯수
     */
    public $wishScmCnt = [];

    /**
     * @var array 관심상품 SCM 정보
     */
    public $wishScmInfo = [];

    /**
     * 생성자
     */
    public function __construct()
    {
        $_mcfg = \App::load('\\Component\\Mobile\\MobileShop')->getMobileConfig();

        if (!is_object($this->db)) {
            $this->db = \App::load('DB');
        }

        // 상품 보관함 설정
        if (!is_array($this->wishConf)) {
            $this->wishConf = gd_policy('order.wish');
        }

        // --- 상품 과세 / 비과세 설정
        if (!is_array($this->tax)) {
            $this->tax = gd_policy('goods.tax');
        }

        // 상품 출력여부 설정
        if (\Request::isMobile()) {
            $this->goodsDisplayFl = 'goodsDisplayMobileFl';
        }
    }

    /**
     * 관심상품 정보 출력
     *
     * 완성된 쿼리문은 $db->strField , $db->strJoin , $db->strWhere , $db->strGroup , $db->strOrder , $db->strLimit 멤버 변수를
     * 이용할수 있습니다.
     *
     * @param string      $cartSno 장바구니 고유 번호 (기본 null)
     * @param string      $cartField 출력할 필드명 (기본 null)
     * @param array       $arrBind bind 처리 배열 (기본 null)
     * @param bool|string $dataArray return 값을 배열처리 (기본값 false)
     *
     * @return array 장바구니 정보
     *
     * @author su
     */
    public function getWishInfo($sno = null, $cartField = null, $arrBind = null, $dataArray = false)
    {
        if (is_null($arrBind)) {
            // $arrBind = array();
        }
        if ($sno) {
            if ($this->db->strWhere) {
                $this->db->strWhere = " sno = ? AND " . $this->db->strWhere;
            } else {
                $this->db->strWhere = " sno = ?";
            }
            $this->db->bind_param_push($arrBind, 'i', $sno);
        }
        if ($cartField) {
            if ($this->db->strField) {
                $this->db->strField = $cartField . ', ' . $this->db->strField;
            } else {
                $this->db->strField = $cartField;
            }
        }
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_WISH . ' ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);

        if (count($getData) == 1 && $dataArray === false) {
            return gd_htmlspecialchars_stripslashes($getData[0]);
        }

        return gd_htmlspecialchars_stripslashes($getData);
    }


    /**
     * 상품 보관함 상품 중복 체크
     *
     * @param  array $arrData 상품 정보
     *
     * @return array 체크 결과 (중복수량)
     */
    protected function getDuplicationCheck($arrData)
    {
        $strWhere = '';
        $arrBind = $this->db->get_binding(DBTableField::tableWish(), $arrData, 'select');
        if (!empty($arrBind['where'])) {
            $strWhere = ' AND ' . implode(' AND ', $arrBind['where']);
        }
        $strSQL = "SELECT count(goodsNo) as cnt FROM " . DB_WISH . " WHERE " . implode(' AND ', $arrBind['param']) . $strWhere . " ORDER BY sno ASC";
        $getData = $this->db->query_fetch($strSQL, $arrBind['bind'], false);

        return $getData;
    }

    /**
     * 상품 보관함 상품 개수 체크
     *
     * @param integer $memNo 회원 번호 (관리자 > 회원 CRM 에서 사용)
     * @return array 상품 개수
     */
    public function getWishGoodsCnt($memNo = null)
    {
        // 회원 로그인 체크
        if (gd_is_login() === true || $memNo) {
            if($memNo == null) $memNo = Session::get('member.memNo');
            $strWhere = 'memNo = ?';
            $this->db->bind_param_push($arrBind['bind'], 'i', $memNo);
        } else {
            return 0;
        }
        $strSQL = 'SELECT count(goodsNo) as cnt FROM ' . DB_WISH . ' WHERE ' . $strWhere;
        $getData = $this->db->query_fetch($strSQL, $arrBind['bind'], false);

        return $getData['cnt'];
    }

    /**
     * 상품 보관함 상품 DB insert
     *
     * @param array $arrData 상품 정보
     */
    protected function setInsetWish($arrData)
    {
        if($arrData['sno'] && $arrData['mode'] =='wishModify') {
            $arrBind = $this->db->get_binding(DBTableField::tableWish(), $arrData, 'update');
            $this->db->bind_param_push($arrBind['bind'], 's', $arrData['sno']);
            $this->db->set_update_db(DB_WISH, $arrBind['param'], 'sno = ?', $arrBind['bind']);
        } else {
            $arrBind = $this->db->get_binding(DBTableField::tableWish(), $arrData, 'insert');
            $this->db->set_insert_db(DB_WISH, $arrBind['param'], $arrBind['bind'], 'y');
            $wishSno = $this->db->insert_id();

            // 관심상품 통계 저장
            $wishStatistics = $arrData;
            $wishStatistics['wishSno'] = $wishSno;
            if (empty($wishStatistics['mallSno'])) {
                $wishStatistics['mallSno'] = DEFAULT_MALL_NUMBER;
            }
            $goodsStatistics = new GoodsStatistics();
            $goodsStatistics->setWishStatistics($wishStatistics);
        }

        // 관심상품 갯수 변경 처리
        $goods = \App::load(\Component\Goods\Goods::class);
        $goods->setCartWishCount('wish', $arrData['goodsNo']);
    }

    /**
     * 상품 보관함 상품 삭제
     *
     * @param integer $wishSno 상품 보관함 sno
     */
    public function setWishDelete($wishSno)
    {
        $arrBind = [];

        // 회원 로그인 체크
        if (gd_is_login() === true) {

            // 관심상품 sno로 goodsNo 조회 - 갯수 변경 처리를 위해 추출
            foreach ($wishSno as $wishIdx => $sno) {
                $changeGoodsNoArray[] = $this->checkWishSelectGoodsNo($sno, '', 'sno');
                $arrBind['param'][] = '?';
                $this->db->bind_param_push($arrBind['bind'], 'i', $sno);
            }

            $this->db->bind_param_push($arrBind['bind'], 'i', Session::get('member.memNo'));
        } else {
            throw new Exception(__('로그인 후 이용 가능 합니다.'));
        }

        $strWhere = 'sno IN (' . implode(',', $arrBind['param']) . ') AND memNo = ?';
        $this->db->set_delete_db(DB_WISH, $strWhere, $arrBind['bind']);

        // 관심상품 갯수 변경 처리
        $goods = \App::load(\Component\Goods\Goods::class);
        foreach ($changeGoodsNoArray as $goodsIdx => $goodsNo) {
            $goods->setCartWishCount('wish', $goodsNo['goodsNo']);
        }

        unset($arrBind);
    }

    /**
     * 상품 보관함 비우기
     */
    public function setWishRemove()
    {
        // 회원 로그인 체크
        if (gd_is_login() === true) {
            $arrBind['param'] = 'memNo = ?';
            $this->db->bind_param_push($arrBind['bind'], 'i', Session::get('member.memNo'));
        } else {
            throw new Except(self::ERROR_VIEW, self::TEXT_LOGIN_CHECK);
        }

        $this->db->set_delete_db(DB_WISH, $arrBind['param'], $arrBind['bind']);

        // 관심상품 bind 데이터로 goodsNo 조회 - 갯수 변경 처리를 위해 추출
        $changeGoodsNoArray = $this->checkWishSelectGoodsNo(null, $arrBind);
        // 관심상품 갯수 변경 처리
        $goods = \App::load(\Component\Goods\Goods::class);
        foreach ($changeGoodsNoArray as $goodsNo) {
            $goods->setCartWishCount('wish',  $goodsNo['goodsNo']);
        }
    }

    /**
     * 상품 보관함 담기 (배열)
     * 상품을 상품 보관함에 담습니다.
     *
     * @param array $arrData 상품 정보
     */
    public function saveInfoWish($arrData)
    {
        // 장바구니 테이블 필드
        $arrExclude = ['memNo'];
        $fieldData = DBTableField::setTableField('tableWish', null, $arrExclude);

        //옵션없이 선택한 경우
        if (!gd_isset($arrData['goodsNo'])) {
            $arrData['goodsNo'][] = $arrData['cartMode'];
            $arrData['goodsCnt'][] = 1;
        }

        // 옵션사용 안하는 상품
        if ($arrData['optionFl'] === 'n') {
            $goods = \App::load('\\Component\\Goods\\Goods');
            $goodsOption = gd_htmlspecialchars($goods->getGoodsOption($arrData['goodsNo'][0], $arrData));
            $arrData['optionSno'][] = $goodsOption[0]['sno'];
        }

        $getData = [];
        // 상품 번호를 기준으로 장바구니에 담을 상품의 배열을 처리함
        foreach ($arrData['goodsNo'] as $goodsIdx => $goodsNo) {

            foreach ($fieldData as $field) {
                if (gd_isset($arrData[$field]) && is_array($arrData[$field])) {
                    $getData[$field] = gd_isset($arrData[$field][$goodsIdx]);
                }
            }

            if ($arrData['mode'] =='wishModify') {
                $getData['mode'] = $arrData['mode'];
                $getData['sno'] = $arrData['sno'];
            }
            $getData['deliveryCollectFl'] = $arrData['deliveryCollectFl'];
            $getData['deliveryMethodFl'] = $arrData['deliveryMethodFl'];
            if($arrData['deliveryMethodFl'] === 'etc'){
                $getData['deliveryMethodEtc'] = $arrData['deliveryMethodEtc'];
            }
            $getData['optionFl'] = $arrData['optionFl'];
            $getData['useBundleGoods'] = $arrData['useBundleGoods'];
            // 상품 보관함에 담기
            $arrayRtn[] = $this->goodsIntoWish($getData);
        }

        // 설정갯수 이상 삭제
        $cartInfo = gd_policy('order.cart');

        // 설정제한이 걸린 경우만 체크
        if ($cartInfo['wishLimitFl'] == 'y') {
            if ($cartInfo['wishDay'] < $this->getWishGoodsCnt()) {
                $arrBind = [];
                $this->db->bind_param_push($arrBind['bind'], 'i', Session::get('member.memNo'));

                $strWhere = "memNo = ? ORDER BY regDt ASC limit ".($this->getWishGoodsCnt()-$cartInfo['wishDay']);
                $this->db->set_delete_db(DB_WISH, $strWhere, $arrBind['bind']);
            }
        }

        return $arrayRtn;

    }

    /**
     * 상품 보관함 담기 (단품)
     * 한개의 상품을 상품 보관함에 담습니다.
     *
     * @param array $arrData 상품 정보
     */
    public function goodsIntoWish($arrData)
    {
        $mallBySession = SESSION::get(SESSION_GLOBAL_MALL);

        // Validation - 상품 코드 체크
        if (Validator::required($arrData['goodsNo'], true) === false) {
            throw new \Exception(__("상품이 존재하지 않습니다."));
        }

        // 상품 텍스트 옵션
        if (gd_isset($arrData['optionText']) && empty($arrData['optionText']) === false) {
            $arrData['optionText'] = ArrayUtils::removeEmpty($arrData['optionText']);
            $arrData['optionText'] = json_encode($arrData['optionText'], JSON_UNESCAPED_UNICODE);
        }

        // 추가 상품
        if (gd_isset($arrData['addGoodsNo']) && empty($arrData['addGoodsNo']) === false) {
            $arrData['addGoodsNo'] = ArrayUtils::removeEmpty($arrData['addGoodsNo']);
            $arrData['addGoodsCnt'] = ArrayUtils::removeEmpty($arrData['addGoodsCnt']);
            $arrData['addGoodsNo'] = json_encode($arrData['addGoodsNo']);
            $arrData['addGoodsCnt'] = json_encode($arrData['addGoodsCnt']);
        }


        $arrData['memNo'] = Session::get('member.memNo');
        $arrData['mallSno'] =$mallBySession['sno'];

        $logger = \App::getInstance('logger');$logger->info(__METHOD__, $arrData);
        // 중복 상품이 담겨있는지 확인 후 해당 상품정보 반환
        $duplicatedGoods = $this->getDuplicationCheck($arrData);

        // 위시리스트
        if ($duplicatedGoods['cnt'] == 0) {
            $return = $this->setInsetWish($arrData);

            //tomi
            $goods = \App::load(\Component\Goods\Goods::class);
            $goods->setCartWishCount('wish', $arrData['goodsNo']);


            return $return;
        }

    }

    /**
     * 장바구니 담기
     * 상품 보관함의 상품을 장바구니로 이동을 합니다.
     *
     * @param integer $sno 위시리스트SNO
     *
     * @return boolean
     * @throws Exception
     */
    public function setWishToCart($sno)
    {
        if (empty($sno) === true) {
            return false;
        }

        foreach ($sno as $wishSno) {
            $param[] = '?';
            $this->db->bind_param_push($arrBind, 'i', $wishSno);

            // 관심상품 bind 데이터로 goodsNo 조회 - 갯수 변경 처리를 위해 추출
            $changeGoodsNoArray[] = $this->checkWishSelectGoodsNo($wishSno,'', 'sno');
        }

        if (empty($param) === true) {
            return false;
        }

        // 회원 로그인 체크
        if (gd_is_login() === true) {
            $strWhere = 'sno IN (' . implode(' , ', $param) . ') AND memNo = ?';
            $this->db->bind_param_push($arrBind, 'i', Session::get('member.memNo'));
        } else {
            return false;
        }

        $selectFieldData = DBTableField::setTableField(
            'tableWish',
            null,
            [
                'sno',
                'regDt',
                'modDt',
            ]
        );
        $insertFieldData = DBTableField::setTableField(
            'tableCart',
            null,
            [
                'sno',
                'directCart',
                'couponGiveSno',
                'memberCouponNo',
                'tmpOrderNo',
                'linkMainTheme',
                'regDt',
                'modDt',
            ]
        );

        $siteKey = Session::get('siteKey');

        $strSQL = 'INSERT INTO ' . DB_CART . ' (' . implode(', ', $insertFieldData) . ', regDt) SELECT "' . $siteKey . '" as siteKey, ' . implode(', ', $selectFieldData) . ', now() FROM ' . DB_WISH . ' WHERE ' . $strWhere;

        $preStr = $this->db->prepare($strSQL);
        $this->db->bind_param($preStr, $arrBind);
        $this->db->execute();
        $this->db->stmt_close();
        unset($arrBind);

        // 장바구니 통계 저장
        $cartSno = $this->db->insert_id();
        $this->db->strWhere = ' c.sno = ? ';
        $this->db->bind_param_push($arrBind, 'i', $cartSno);
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_CART . ' as c ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);
        unset($arrBind);

        // 관심상품 갯수 변경 처리
        $goods = \App::load(\Component\Goods\Goods::class);
        foreach ($changeGoodsNoArray as $goodsIdx => $goodsNo) {
            $goods->setCartWishCount('cart', $goodsNo['goodsNo']);
        }

        $eventConfig = \App::getConfig('event')->toArray();
        if ($eventConfig['cartOrderStatistics'] !== 'n') {
            $cartStatistics = $getData[0];
            $cartStatistics['cartSno'] = $getData[0]['sno'];
            $cartStatistics['orderFl'] = 'n';
            if (empty($cartStatistics['mallSno'])) {
                $cartStatistics['mallSno'] = DEFAULT_MALL_NUMBER;
            }
            $goodsStatistics = new GoodsStatistics();
            $goodsStatistics->setCartStatistics($cartStatistics);
        }

        $cartInfo = gd_policy('order.cart');
        if($cartInfo['moveWishPageFl'] =='n') {
            //장바구니 저장 상품 삭제
            $this->setWishDelete($sno);
        }

        return true;

    }

    /**
     * 상품 보관함 상품 정보
     * 현재 상품 보관함에 담긴 상품의 정보를 출력합니다.
     *
     * @param  array $memInfo   회원 정보 (관리자>회원CRM에서만 사용)
     *
     * @return array 상품 보관함 상품 정보
     */
    public function getWishGoodsData($memInfo = null)
    {
        if($memInfo) {
            $memNo = $memInfo['memNo'];
            $groupSno = $memInfo['groupSno'];
            $cart = new CartAdmin($memNo, true);
        } else {
            $memNo = Session::get('member.memNo');
            $groupSno = Session::get('member.groupSno');
            $cart = \App::load('\\Component\\Cart\\Cart');
            $mallBySession = SESSION::get(SESSION_GLOBAL_MALL);
        }

        // 회원 로그인 체크
        if (gd_is_login() === true || $memInfo) {
            $arrWhere[] = 'w.memNo = \'' . $memNo . '\'';
        } else {
            MemberUtil::logoutGuest();
            $moveUrl = URI_HOME . 'member/login.php?returnUrl=' . urlencode(Request::getReturnUrl());
            throw new AlertRedirectException(null, null, null, $moveUrl);
        }

        if($mallBySession) {
            $arrWhere[] = 'w.mallSno = \'' . $mallBySession['sno'] . '\'';
        }

        $globalWhere = implode(' AND ', $arrWhere);

        //접근권한 체크
        if (gd_check_login() || $memInfo) {
            $arrWhere[] = '(g.goodsAccess !=\'group\'  OR (g.goodsAccess=\'group\' AND FIND_IN_SET(\''.$groupSno.'\', REPLACE(g.goodsAccessGroup,"'.INT_DIVISION.'",","))) OR (g.goodsAccess=\'group\' AND !FIND_IN_SET(\''.$groupSno.'\', REPLACE(g.goodsAccessGroup,"'.INT_DIVISION.'",",")) AND g.goodsAccessDisplayFl =\'y\'))';
        } else {
            $arrWhere[] = '(g.goodsAccess=\'all\' OR (g.goodsAccess !=\'all\' AND g.goodsAccessDisplayFl =\'y\'))';
        }

        //성인인증안된경우 노출체크 상품은 노출함
        if (gd_check_adult() === false) {
            $arrWhere[] = '(onlyAdultFl = \'n\' OR (onlyAdultFl = \'y\' AND onlyAdultDisplayFl = \'y\'))';
        }

        $imageSize = SkinUtils::getGoodsImageSize('list');

        // 세로사이즈고정 체크
        $imageConf = gd_policy('goods.image');
        if ($imageConf['imageType'] != 'fixed') {
            $imageSize['hsize1'] = '';
        }

        // 정렬 방식
        $strOrder = 'w.sno DESC';

        // 장바구니 디비 및 상품 디비의 설정 (필드값 설정)
        $getData = [];

        $arrExclude['wish'] = [];
        $arrExclude['option'] = [
            'goodsNo',
            'optionNo',
        ];
        $arrExclude['addOptionName'] = [
            'goodsNo',
            'optionCd',
            'mustFl',
        ];
        $arrExclude['addOptionValue'] = [
            'goodsNo',
            'optionCd',
        ];
        $arrInclude['goods'] = [
            'goodsNm',
            'scmNo',
            'goodsCd',
            'cateCd',
            'goodsOpenDt',
            'goodsState',
            'imageStorage',
            'imagePath',
            'brandCd',
            'makerNm',
            'originNm',
            'goodsModelNo',
            'goodsPermission',
            'goodsPermissionGroup',
            'goodsPermissionPriceStringFl',
            'goodsPermissionPriceString',
            'onlyAdultFl',
            'goodsAccess',
            'goodsAccessGroup',
            'taxFreeFl',
            'taxPercent',
            'goodsWeight',
            'totalStock',
            'stockFl',
            'soldOutFl',
            'fixedSales',
            'fixedOrderCnt',
            'salesUnit',
            'minOrderCnt',
            'maxOrderCnt',
            'salesStartYmd',
            'salesEndYmd',
            'mileageFl',
            'mileageGoods',
            'mileageGoodsUnit',
            'goodsDiscountFl',
            'goodsDiscount',
            'goodsDiscountUnit',
            'payLimitFl',
            'payLimit',
            'goodsPriceString',
            'goodsPrice',
            'fixedPrice',
            'costPrice',
            'optionFl',
            'optionName',
            'optionTextFl',
            'addGoodsFl',
            'addGoods',
            'deliverySno',
            'delFl',
            'goodsSellFl',
            'goodsSellMobileFl',
            'goodsDisplayFl',
            'goodsDisplayMobileFl',
            'mileageGroup',
            'mileageGroupInfo',
            'mileageGroupMemberInfo',
            'fixedGoodsDiscount',
            'goodsDiscountGroup',
            'goodsDiscountGroupMemberInfo',
            'exceptBenefit',
            'exceptBenefitGroup',
            'exceptBenefitGroupInfo',
            'onlyAdultImageFl',
            'goodsBenefitSetFl',
            'benefitUseType',
            'newGoodsRegFl',
            'newGoodsDate',
            'newGoodsDateFl',
            'periodDiscountStart',
            'periodDiscountEnd',
            'regDt',
            'modDt'
        ];
        $arrInclude['image'] = [
            'imageSize',
            'imageName',
        ];

        $arrFieldWish = DBTableField::setTableField('tableWish', null, $arrExclude['wish'], 'w');
        $arrFieldGoods = DBTableField::setTableField('tableGoods', $arrInclude['goods'], null, 'g');
        $arrFieldOption = DBTableField::setTableField('tableGoodsOption', null, $arrExclude['option'], 'go');
        $arrFieldImage = DBTableField::setTableField('tableGoodsImage', $arrInclude['image'], null, 'gi');
        unset($arrExclude);

        // 장바구니 상품 기본 정보
        $strSQL = "SELECT w.sno,
                " . implode(', ', $arrFieldWish) . ", w.regDt,
                " . implode(', ', $arrFieldGoods) . ",
                " . implode(', ', $arrFieldOption) . ",
                " . implode(', ', $arrFieldImage) . "
            FROM " . DB_WISH . " w
            INNER JOIN " . DB_GOODS . " g ON w.goodsNo = g.goodsNo
            LEFT JOIN " . DB_GOODS_OPTION . " go ON w.optionSno = go.sno AND w.goodsNo = go.goodsNo
            LEFT JOIN " . DB_GOODS_IMAGE . " as gi ON g.goodsNo = gi.goodsNo AND gi.imageKind = 'list'
            WHERE " . implode(' AND ', $arrWhere) . "
            ORDER BY " . $strOrder;

        if($mallBySession) {
            $arrFieldGoodsGlobal = DBTableField::setTableField('tableGoodsGlobal',null,['mallSno']);
            $strSQLGlobal = "SELECT gg." . implode(', gg.', $arrFieldGoodsGlobal) . " FROM ".DB_WISH." as w INNER JOIN ".DB_GOODS_GLOBAL." as gg ON  w.goodsNo = gg.goodsNo AND gg.mallSno = '".$mallBySession['sno']."'  WHERE " . $globalWhere ;
            $tmpData = $this->db->query_fetch($strSQLGlobal);
            $globalData = array_combine (array_column($tmpData, 'goodsNo'), $tmpData);
        }

        $result = $this->db->query($strSQL);
        unset($arrWhere, $strOrder);

        // 삭제 상품에 대한 cartNo
        $_delCartSno = [];

        //상품 가격 노출 관련
        $goodsPriceDisplayFl = gd_policy('goods.display')['priceFl'];

        //품절상품 설정
        if(\Request::isMobile()) {
            $soldoutDisplay = gd_policy('soldout.mobile');
        } else {
            $soldoutDisplay = gd_policy('soldout.pc');
        }

        // 관심상품 기본설정
        $wishInfo = gd_policy('order.cart');

        /**해외몰 관련 **/

        $goods = new Goods();
        //상품 혜택 모듈
        $goodsBenefit = \App::load('\\Component\\Goods\\GoodsBenefit');
        while ($data = $this->db->fetch($result)) {

            //상품혜택 사용시 해당 변수 재설정
            $data = $goodsBenefit->goodsDataFrontConvert($data);

            $data['isCart'] = true;
            // stripcslashes 처리
            $data = gd_htmlspecialchars_stripslashes($data);

            if($mallBySession && $globalData[$data['goodsNo']]) {
                $data = array_replace_recursive($data, array_filter(array_map('trim',$globalData[$data['goodsNo']])));
            }

            // 상품 삭제 여부에 따른 처리
            if ($data['delFl'] === 'y') {
                $_delCartSno[] = $data['sno'];
                unset($data);
                continue;
            } else {
                unset($data['delFl']);
            }

            // 텍스트옵션 상품 정보
            $goodsOptionText = $goods->getGoodsOptionText($data['goodsNo']);
            if (empty($data['optionText']) === false && gd_isset($goodsOptionText)) {
                $optionTextKey = array_keys(json_decode($data['optionText'], true));
                foreach ($goodsOptionText as $goodsOptionTextInfo) {
                    if (in_array($goodsOptionTextInfo['sno'], $optionTextKey) === true) {
                        $data['optionTextInfo'][$goodsOptionTextInfo['sno']] = [
                            'optionSno' => $goodsOptionTextInfo['sno'],
                            'optionName' => $goodsOptionTextInfo['optionName'],
                            'baseOptionTextPrice' => $goodsOptionTextInfo['addPrice'],
                        ];
                    }
                }
            }

            // 추가 상품 정보
            if ($data['addGoodsFl'] === 'y' && empty($data['addGoodsNo']) === false) {
                $data['addGoodsNo'] = json_decode($data['addGoodsNo']);
                $data['addGoodsCnt'] = json_decode($data['addGoodsCnt']);
            } else {
                $data['addGoodsNo'] = '';
                $data['addGoodsCnt'] = '';
            }

            // 추가 상품 필수 여부
            if ($data['addGoodsFl'] === 'y' && empty($data['addGoods']) === false) {
                $data['addGoods'] = json_decode(gd_htmlspecialchars_stripslashes($data['addGoods']), true);
                foreach ($data['addGoods'] as $k => $v) {
                    if ($v['mustFl'] == 'y') {
                        if (is_array($data['addGoodsNo']) === false) {
                            $data['addGoodsSelectedFl'] = 'n';
                            break;
                        } else {
                            $addGoodsResult = array_intersect($v['addGoods'], $data['addGoodsNo']);
                            if (empty($addGoodsResult) === true) {
                                $data['addGoodsSelectedFl'] = 'n';
                                break;
                            }
                        }
                    }
                }
            }

            // 텍스트 옵션 정보 (sno, value)
            $data['optionTextSno'] = [];
            $data['optionTextStr'] = [];
            if ($data['optionTextFl'] === 'y' && empty($data['optionText']) === false) {
                $arrText = json_decode($data['optionText']);
                foreach ($arrText as $key => $val) {
                    $data['optionTextSno'][] = $key;
                    $data['optionTextStr'][$key] = $val;
                    unset($tmp);
                }
            }
            unset($data['optionText']);

            // 텍스트옵션 필수 사용 여부
            if ($data['optionTextFl'] === 'y') {
                if (gd_isset($goodsOptionText)) {
                    foreach ($goodsOptionText as $k => $v) {
                        if ($v['mustFl'] == 'y' && !in_array($v['sno'], $data['optionTextSno'])) {
                            $data['optionTextEnteredFl'] = 'n';
                        }
                    }
                }
            }
            unset($optionText);

            // 상품 구매 가능 여부
            $data = $cart->checkOrderPossible($data, true);
            if($data['orderPossible'] != 'y'){
                $data['isCart'] = false;
            }

            // 정책설정에서 품절상품 보관설정의 보관상품 품절시 자동삭제로 설정한 경우
            if ($wishInfo['wishSoldOutFl'] == 'n' && ($data['soldOutFl'] === 'y' || $data['optionSellFl'] === 'n' || ($data['soldOutFl'] === 'n' && $data['stockFl'] === 'y' && (($data['stockCnt'] != null && $data['stockCnt'] <= 0) || $data['totalStock'] <= 0 || $data['totalStock'] < $data['goodsCnt'])))) {
                $_delWishSno[] = $data['sno'];
                unset($data);
                continue;
            }

            //구매불가 대체 문구 관련
            if($data['goodsPermissionPriceStringFl'] =='y' && $data['goodsPermission'] !='all' && (($data['goodsPermission'] =='member'  && gd_is_login() === false) || ($data['goodsPermission'] =='group'  && !in_array(Session::get('member.groupSno'),explode(INT_DIVISION,$data['goodsPermissionGroup']))))) {
                $data['goodsPriceString'] = $data['goodsPermissionPriceString'];
            }

            //품절일경우 가격대체 문구 설정
            if (($data['soldOutFl'] === 'y' || ($data['soldOutFl'] === 'n' && $data['stockFl'] === 'y' && ($data['totalStock'] <= 0 || $data['totalStock'] < $data['goodsCnt']))) && $soldoutDisplay['soldout_price'] !='price'){
                if($soldoutDisplay['soldout_price'] =='text' ) {
                    $data['goodsPriceString'] = $soldoutDisplay['soldout_price_text'];
                } else if($soldoutDisplay['soldout_price'] =='custom' ) {
                    $data['goodsPriceString'] = "<img src='".$soldoutDisplay['soldout_price_img']."'>";
                }
            }

            $data['goodsPriceDisplayFl'] = 'y';
            if (empty($data['goodsPriceString']) === false && $goodsPriceDisplayFl =='n') {
                $data['goodsPriceDisplayFl'] = 'n';
            }

            $data['goodsMileageExcept'] = 'n';
            $data['couponBenefitExcept'] =  'n';
            $data['memberBenefitExcept'] =  'n';

            //타임세일 할인 여부
            $data['timeSaleFl'] = false;
            if (gd_is_plus_shop(PLUSSHOP_CODE_TIMESALE) === true) {
                $timeSale = \App::load('\\Component\\Promotion\\TimeSale');
                $timeSaleInfo = $timeSale->getGoodsTimeSale($data['goodsNo']);
                if ($timeSaleInfo) {
                    $data['timeSaleFl'] = true;
                    if ($timeSaleInfo['mileageFl'] == 'n') {
                        $data['goodsMileageExcept'] = "y";
                    }
                    if ($timeSaleInfo['couponFl'] == 'n') {
                        $data['couponBenefitExcept'] = "y";
                    }
                    if ($timeSaleInfo['memberDcFl'] == 'n') {
                        $data['memberBenefitExcept'] = "y";
                    }

                    if ($data['goodsPrice'] > 0) {
                        $data['goodsPrice'] = $data['goodsPrice'] - (($timeSaleInfo['benefit'] / 100) * $data['goodsPrice']);
                    }
                }
            }

            // 비회원시 담은 상품과 회원로그인후 담은 상품이 중복으로 있는경우 재고 체크
            $data['duplicationGoods'] = 'n';
            if (isset($tmpStock[$data['goodsNo']][$data['optionSno']]) === false) {
                $tmpStock[$data['goodsNo']][$data['optionSno']] = $data['goodsCnt'];
            } else {
                $data['duplicationGoods'] = 'y';
                $chkStock = $tmpStock[$data['goodsNo']][$data['optionSno']] + $data['goodsCnt'];
                if ($data['stockFl'] == 'y' && $data['stockCnt'] < $chkStock) {
                    $data['stockOver'] = 'y';
                }
            }

            // 상품 이미지 처리 @todo 상품 사이즈 설정 값을 가지고 와서 이미지 사이즈 변경을 할것
            if ($data['onlyAdultFl'] == 'y' && gd_check_adult() === false && $data['onlyAdultImageFl'] =='n') {
                if (\Request::isMobile()) {
                    $data['goodsImageSrc'] = "/data/icon/goods_icon/only_adult_mobile.png";
                } else {
                    $data['goodsImageSrc'] = "/data/icon/goods_icon/only_adult_pc.png";
                }

                $data['goodsImage'] = SkinUtils::makeImageTag($data['goodsImageSrc'], $imageSize['size1']);
            } else {
                $data['goodsImage'] = gd_html_preview_image($data['imageName'], $data['imagePath'], $data['imageStorage'], $imageSize['size1'], 'goods', $data['goodsNm'], 'class="imgsize-s"', false, false, $imageSize['hsize1']);
            }


            unset($data['imageStorage'], $data['imagePath'], $data['imageName'], $data['imagePath']);

            $data = $cart->getMemberDcFlInfo($data);

            $getData[] = $data;
            unset($data);
        }

        // 삭제 상품 및 품절 상품이 있는 경우 관심상품 삭제
        if (empty($_delWishSno) === false) {
            $this->setWishDelete($_delWishSno);
        }

        $getData = $cart->setWishData($getData);

        if (is_array($getData)) {
            $scmClass = \App::load(\Component\Scm\Scm::class);
            $this->wishScmCnt = count($getData);
            $this->wishScmInfo = $scmClass->getCartScmInfo(array_keys($getData));
        }

        return $getData;
    }

    /**
     * SNO / bind 기준 관심상품 상품수량 조회 함수
     *
     * @param integer $cartSno 장바구니일련번호
     * @param integer $arrBind 바인딩쿼리
     * @param string  $field 필드명
     *
     * @return array 상품번호
     *
     */
    public function checkWishSelectGoodsNo($wishSno = 0, $arrBind = null, $field = null) {
        // sno 필드가 넘어온 경우 sno 단일 조회
        if($field == 'sno') {
            $strWhere = ' WHERE ' . $field . ' = ?';
            $this->db->bind_param_push($arrBind, 's', $wishSno);
            $strSQL = 'SELECT goodsNo FROM ' . DB_WISH . $strWhere;
            $getData = $this->db->query_fetch($strSQL . " group by goodsNo", $arrBind, false);
        }
        else { // bind param 값으로 조건 생성
            if($arrBind) {
                $strSQL = 'SELECT goodsNo FROM ' . $this->tableName . ' WHERE ' . $arrBind['param'];
                $getData = $this->db->query_fetch($strSQL . " group by goodsNo", $arrBind['bind']);
            }
        }

        return $getData;
    }
}
