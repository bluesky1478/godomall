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

use UserFilePath;
use FileHandler;
use Session;
use Cookie;
use Request;
use Exception;
use Component\Storage\Storage;
use Framework\Utility\SkinUtils;
use Framework\Debug\Exception\AlertBackException;

/**
 * 밴드웨건 푸시 클래스
 * @author <kookoo135@godo.co.kr>
 */
class BandwagonPush
{
    // 디비 접속
    protected $db;

    public $cfg, $mall;

    // 상품범위
    const BW_RANGE = [
        'click' => '클릭상품',
        'cart' => '장바구니 담긴 상품',
        'wish' => '찜리스트 상품'
    ];
    // 노출항목
    const BW_FIELD = [
        'area' => '지역',
        'realNm' => '구매자명(실명)',
        'maskingNm' => '구매자명(마스킹처리)',
        'nickNm' => '닉네임',
        'goodsImg' => '상품 이미지',
        'goodsNm' => '상품명',
        'goodsOpt' => '옵션',
        'goodsPrice' => '판매가',
        'stock' => '재고',
        'orderCnt' => '상품판매수량',
    ];
    // 수집기간
    const BW_TERM = [
        '-3 HOUR' => '3시간',
        '-6 HOUR' => '6시간',
        '-12 HOUR' => '12시간',
        '-24 HOUR' => '24시간',
        '-7 DAY' => '7일',
        '-30 DAY' => '30일',
    ];
    // 페이지
    const BW_PAGE = [
        'main' => [
            'title' => '메인',
            'page' => ['index', 'main/index'],
        ],
        'view' => [
            'title' => '상품상세정보',
            'page' => ['goods/goods_view',],
        ],
        'list' => [
            'title' => '상품리스트',
            'page' => ['goods/goods_list', 'goods/goods_main', 'goods/event_sale', 'event/time_sale', 'goods/populate', ],
        ],
        'search' => [
            'title' => '검색 페이지',
            'page' => ['goods/goods_search',],
        ],
        'cart' => [
            'title' => '장바구니',
            'page' => ['order/cart',],
        ],
        'wish' => [
            'title' => '찜리스트',
            'page' => ['mypage/wish_list',],
        ],
        'order' => [
            'title' => '주문서작성',
            'page' => ['order/order',],
        ],
    ];
    const GOODSNM_LEN = 45;

    /**
     * 생성자
     *
     */
    public function __construct()
    {
        if (gd_is_plus_shop(PLUSSHOP_CODE_BANDWAGONPUSH) === false) {
            //throw new AlertBackException(__('[플러스샵] 미설치 또는 미사용 상태입니다. 설치 완료 및 사용 설정 후 플러스샵 앱을 사용할 수 있습니다.'));
        }
        if (!is_object($this->db)) {
            $this->db = \App::load('DB');
        }
        $this->cfg = $this->getConfigData();
        $this->mall = SESSION::get(SESSION_GLOBAL_MALL);
    }

    /**
     * 밴드왜건 푸시 기본 설정
     */
    public function getConfigData()
    {
        $getData = gd_policy('goods.bandwagonPush');
        if (empty($getData) === true) {
            gd_isset($getData['displayPage'], ['main', 'view', 'list', 'search', 'cart', 'wish', 'order']);
            gd_isset($getData['displayPageMobile'], ['main', 'view', 'list', 'search', 'cart', 'wish', 'order']);
        }
        gd_isset($getData['range'], ['click']);
        gd_isset($getData['displayField'], ['area', 'maskingNm', 'goodsImg', 'goodsNm']);
        gd_isset($getData['term'], '-3 HOUR');
        gd_isset($getData['soldOutFl'], 'y');
        gd_isset($getData['position'], 'right');
        gd_isset($getData['background'], '#ffffff');
        gd_isset($getData['color'], '#000000');
        gd_isset($getData['stockFl'], 'y');
        gd_isset($getData['stock'], '5');
        gd_isset($getData['iconFl'], 'd');
        gd_isset($getData['mobileFl'], 'y');

        return $getData;
    }

    /**
     * 밴드왜건 푸시 설정 저장
     * @param array $postValue 설정정보
     * @param array $fileValue 아이콘정보
     */
    public function save($postValue, $fileValue = [])
    {
        gd_isset($postValue['mobileFl'], 'n');
        if ($postValue['delIcon'] == 'y') {
            Storage::disk(Storage::PATH_CODE_COMMON, 'local')->delete($postValue['iconFileName']);
            $postValue['iconFile'] = '';
        }
        if ($fileValue['error'] == '0') {
            $tmpExt = \FileHandler::getFileInfo($fileValue['name'])->getExtension();
            if (in_array(strtolower($tmpExt), ['jpg', 'png', 'gif']) === false) {
                throw new \Exception(__('이미지는 jpg, png, gif 형식의 파일만 사용하실 수 있습니다.'));
            }

            Storage::disk(Storage::PATH_CODE_COMMON)->upload($fileValue['tmp_name'], $fileValue['name']);
            $postValue['iconFile'] = $fileValue['name'];
        }

        gd_set_policy('goods.bandwagonPush', $postValue, false);
    }

    /**
     * 밴드왜건 푸시 상품 데이터
     * @param integer $page 페이징
     * @param integer $goodsNo 상품번호
     * @return array 상품데이터
     */
    public function getData($page = 0, $goodsNo = null)
    {
        $arrTodayGoodsNo = '';
        $arrBind = $arrWhere = [];
        $whereCartField = 'og.goodsNo';

        if (in_array('goodsOpt', $this->cfg['displayField']) === true) {
            $whereCartField .= ', og.optionSno';
        }

        // 클릭상품
        if (in_array('click', $this->cfg['range']) === true) {
            $todayCookieName = 'todayGoodsNo';
            if ($this->mall && $this->mall['sno'] != '1') {
                $todayCookieName .= $this->mall['sno'];
            }

            if (Cookie::has($todayCookieName)) {
                $arrTodayGoodsNo = json_decode(Cookie::get($todayCookieName));
                $arrWhere[] = '(og.goodsNo IN (' . @implode(',', $arrTodayGoodsNo) . '))';
            }
        }

        // 장바구니 상품
        if (in_array('cart', $this->cfg['range']) === true && Session::has('member.memNo')) {
            $arrWhere[] = '((' . $whereCartField . ') IN (SELECT ' . $whereCartField . ' FROM ' . DB_CART . ' og WHERE og.memNo = ? ))';
            $this->db->bind_param_push($arrBind, 'i', Session::get('member.memNo'));
        }

        // 찜리스트 상품
        if (in_array('wish', $this->cfg['range']) === true && Session::has('member.memNo')) {
            $arrWhere[] = '(og.goodsNo IN (SELECT goodsNo FROM ' . DB_WISH . ' WHERE mallSno = ? AND memNo = ? ))';
            $this->db->bind_param_push($arrBind, 'i', gd_isset($this->mall['sno'], 1));
            $this->db->bind_param_push($arrBind, 'i', Session::get('member.memNo'));
        }

        if (empty($arrWhere) === false) {
            $addWhere[] = 'og.regDt > ?';
            $this->db->bind_param_push($arrBind, 's', date('Y-m-d H:i:s', strtotime($this->cfg['term'], time())));
            $addWhere[] = 'SUBSTR(og.orderStatus, 1, 1) IN (?, ?, ?, ?)';
            $this->db->bind_param_push($arrBind, 's', 'p');
            $this->db->bind_param_push($arrBind, 's', 'd');
            $this->db->bind_param_push($arrBind, 's', 'g');
            $this->db->bind_param_push($arrBind, 's', 's');
            $addWhere[] = 'g.delFl = ?';
            $this->db->bind_param_push($arrBind, 's', 'n');
            if (Request::isMobile()) {
                $addWhere[] = 'g.goodsDisplayMobileFl = ?';
                $addWhere[] = 'g.goodsSellMobileFl = ?';
            } else {
                $addWhere[] = 'g.goodsDisplayFl = ?';
                $addWhere[] = 'g.goodsSellFl = ?';
            }
            $this->db->bind_param_push($arrBind, 's', 'y');
            $this->db->bind_param_push($arrBind, 's', 'y');
            if (Session::has('member.memNo')) {
                $addWhere[] = 'o.memNo != ?';
                $this->db->bind_param_push($arrBind, 'i', Session::get('member.memNo'));
            }
            if ($this->cfg['soldOutFl'] == 'n') {
                $addWhere[] = 'if (g.soldOutFl = \'y\' , \'y\', if (g.stockFl = \'y\' AND g.totalStock <= 0, \'y\', \'n\') ) = ?';
                $this->db->bind_param_push($arrBind, 's', 'n');
            }

            //접근권한 체크
            if (gd_check_login()) {
                $addWhere[] = '(g.goodsAccess !=\'group\'  OR (g.goodsAccess=\'group\' AND FIND_IN_SET(\''.Session::get('member.groupSno').'\', REPLACE(g.goodsAccessGroup,"'.INT_DIVISION.'",","))) OR (g.goodsAccess=\'group\' AND !FIND_IN_SET(\''.Session::get('member.groupSno').'\', REPLACE(g.goodsAccessGroup,"'.INT_DIVISION.'",",")) AND g.goodsAccessDisplayFl =\'y\'))';
            } else {
                $addWhere[] = '(g.goodsAccess=\'all\' OR (g.goodsAccess !=\'all\' AND g.goodsAccessDisplayFl =\'y\'))';
            }

            //성인인증안된경우 노출체크 상품은 노출함
            if (gd_check_adult() === false) {
                $addWhere[] = '(onlyAdultFl = \'n\' OR (onlyAdultFl = \'y\' AND onlyAdultDisplayFl = \'y\'))';
            }

            $this->db->strField = 'o.mallSno, oi.receiverCountry, oi.receiverState, oi.receiverAddress, oi.receiverName, og.goodsNo, og.optionSno, og.goodsPrice, o.memNo, ( if (g.soldOutFl = \'y\' , \'y\', if (g.stockFl = \'y\' AND g.totalStock <= 0, \'y\', \'n\') ) ) as soldOut, g.orderCnt, g.totalStock, g.stockFl, g.soldOutFl, g.optionFl, g.imageStorage, g.imagePath, g.goodsNm, g.goodsNo,goodsPermissionPriceStringFl,goodsPermission,goodsPermissionGroup,goodsPermissionPriceString,onlyAdultFl,onlyAdultImageFl,goodsPriceString';
            $this->db->strWhere = '(' . implode(' OR ', gd_isset($arrWhere)) . ') AND ' . implode(' AND ', gd_isset($addWhere));
            $this->db->strLimit = $page . ', 1';
            if (empty($goodsNo) === false) {
                $this->db->strOrder = 'CASE WHEN og.goodsNo = ? THEN 1 ELSE 0 END DESC, og.regDt DESC';
                $this->db->bind_param_push($arrBind, 'i', $goodsNo);
            } else {
                $this->db->strOrder = 'og.regDt DESC';
            }

            $query = $this->db->query_complete();
            $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_INFO . ' oi LEFT JOIN ' . DB_ORDER_GOODS . ' og ON oi.orderNo = og.orderNo AND oi.orderInfoCd = 1 LEFT JOIN ' . DB_ORDER . ' o ON og.orderNo = o.orderNo LEFT JOIN ' . DB_GOODS . ' g ON og.goodsNo = g.goodsNo' . implode(' ', $query);
            $getData = $this->db->query_fetch($strSQL, $arrBind, false);

            if (empty($getData) === true && $page == 0) {
                return [];
            } else {
                if (empty($getData) === true) {
                    return $this->getData(0, $goodsNo);
                } else {
                    $getData['page'] = $page + 1;
                    $data = $this->setBandwagonPushData($getData);
                    if (empty($data) === true) {
                        return $this->getData($page, $goodsNo);
                    } else {
                        return $data;
                    }
                }
            }
        } else {
            return [];
        }
    }

    /**
     * 밴드왜건 푸시 상품 데이터 정리
     * @param array $getData 상품데이터
     * @return array $setData 정리 상품데이터
     */
    private function setBandwagonPushData($getData)
    {
        $goodsPriceDisplayFl = gd_policy('goods.display')['priceFl']; //상품 가격 노출 관련
        //품절상품 설정
        if(Request::isMobile()) {
            $soldoutDisplay = gd_policy('soldout.mobile');
        } else {
            $soldoutDisplay = gd_policy('soldout.pc');
        }

        $member = \App::load('\\Component\\Member\\Member');
        $goods = \App::load('\\Component\\Goods\\Goods');
        $setData = [];
        $setData['page'] = $getData['page'];
        $setData['goodsNo'] = $getData['goodsNo'];
        $setData['soldOut'] = $getData['soldOut'];
        $setData['name'] = '';
        $setData['goodsPriceString'] = $getData['goodsPriceString'];

        //기본적으로 가격 노출함
        $setData['goodsPriceDisplayFl'] = 'y';

        $optCnt = $goods->getOptionStock($getData['goodsNo'], $getData['optionSno'], 'y', 'n');
        if ($getData['stockFl'] == 'y' && $optCnt == 0) {
            $setData['soldOut'] = 'y';
        }
        if (in_array('area', $this->cfg['displayField']) === true) {
            if ($getData['mallSno'] == '1') {
                $area = explode(' ', $getData['receiverAddress']);
                $setData['area'] = __('대한민국') . ' ' . __(str_replace('광역시', '', trim($area[0])));
            } else {
                $setData['area'] = $getData['receiverCountry'] . ' ' . $getData['receiverState'];
            }
        }
        if (in_array('realNm', $this->cfg['displayField']) === true) {
            $setData['realNm'] = $getData['receiverName'];
            $setData['name'] .= '<strong>'. $setData['realNm'] . '</strong> ';
        }
        if (in_array('maskingNm', $this->cfg['displayField']) === true) {
            $setData['maskingNm'] = mb_substr($getData['receiverName'], 0, 1, 'UTF-8');
            $setData['maskingNm'] .= str_repeat('*', mb_strlen(mb_substr($getData['receiverName'], 1, -1, 'UTF-8'), 'UTF-8'));
            $setData['maskingNm'] .= mb_substr($getData['receiverName'], -1, 1, 'UTF-8');
            $setData['name'] .= '<strong>'. $setData['maskingNm'] . '</strong> ';
        }
        if (in_array('nickNm', $this->cfg['displayField']) === true && empty($getData['memNo']) === false) {
            $memberInfo = $member->getMemberId($getData['memNo']);
            $setData['nickNm'] = $memberInfo['nickNm'];
            $setData['name'] .= '<strong>'. $setData['nickNm'] . '</strong>';
        }
        if (in_array('goodsImg', $this->cfg['displayField']) === true) {
            if ($getData['onlyAdultFl'] == 'y' && gd_check_adult() === false && $getData['onlyAdultImageFl'] =='n') {
                if (Request::isMobile()) {
                    $setData['goodsImg'] = "/data/icon/goods_icon/only_adult_mobile.png";
                } else {
                    $setData['goodsImg'] = "/data/icon/goods_icon/only_adult_pc.png";
                }
            } else {
                $goodsImgInfo = $goods->getGoodsImage($getData['goodsNo'], 'main');
                $goodsImage = SkinUtils::imageViewStorageConfig($goodsImgInfo[0]['imageName'], $getData['imagePath'], $getData['imageStorage'], 140, 'goods')[0];
                $setData['goodsImg'] = $goodsImage;
            }
        }
        if (in_array('goodsNm', $this->cfg['displayField']) === true) {
            if ($this->mall['sno'] == 1) {
                $setData['goodsNm'] = $getData['goodsNm'];
            } else {
                $setData['goodsNm'] = gd_isset($goods->getGoodsNmGlobal($getData['goodsNo'], $this->mall['sno']), $getData['goodsNm']);
            }
            $setData['goodsNm'] = mb_strlen(strip_tags($setData['goodsNm'])) > self::GOODSNM_LEN ? mb_substr(strip_tags($setData['goodsNm']), 0, self::GOODSNM_LEN, 'UTF-8') . '...' : strip_tags($setData['goodsNm']);
        }
        if (in_array('goodsOpt', $this->cfg['displayField']) === true) {
            $goodsOpt = $goods->getGoodsOptionInfo($getData['optionSno']);
            if ($getData['optionFl'] == 'y' && $goodsOpt['optionViewFl'] == 'n') {
                return [];
            }
            $goodsOptNm = [];
            for ($i = 1; $i <= 5; $i++) {
                if (empty($goodsOpt['optionValue' . $i]) === false) {
                    $goodsOptNm[] = $goodsOpt['optionValue' . $i];
                }
            }
            $setData['goodsOpt'] = @implode('/', $goodsOptNm);
            $stock = $goods->getOptionStock($getData['goodsNo'], $getData['optionSno'], $getData['stockFl'], $getData['soldOutFl']);
        }
        if (in_array('goodsPrice', $this->cfg['displayField']) === true) {
            $setData['goodsPrice'] = $getData['goodsPrice'];

            //구매불가 대체 문구 관련
            if($getData['goodsPermissionPriceStringFl'] =='y' && $getData['goodsPermission'] !='all' && (($getData['goodsPermission'] =='member'  && gd_is_login() === false) || ($getData['goodsPermission'] =='group'  && !in_array(\Session::get('member.groupSno'),explode(INT_DIVISION,$getData['goodsPermissionGroup']))))) {
                $setData['goodsPriceString'] = $getData['goodsPermissionPriceString'];
            }
        }
        if (in_array('stock', $this->cfg['displayField']) === true) {
            $setData['stock'] = gd_isset($stock, $getData['totalStock']);
        }
        if ($getData['stockFl'] == 'y' && gd_isset($stock, $getData['totalStock']) > 0 && $this->cfg['stock'] > gd_isset($stock, $getData['totalStock'])) {
            $setData['stockFl'] = 'y';
        }
        if (in_array('orderCnt', $this->cfg['displayField']) === true) {
            $setData['orderCnt'] = $this->getOrderCnt($getData['goodsNo']);
        }

        // 구매 가능여부 체크
        if ($getData['soldOut'] == 'y' && $goodsPriceDisplayFl =='n' && $soldoutDisplay['soldout_price'] !='price') {
            if($soldoutDisplay['soldout_price'] =='text')   $setData['goodsPriceString'] = $soldoutDisplay['soldout_price_text'];
            $setData['goodsPriceDisplayFl'] = 'n';
        }

        if (empty($getData['goodsPriceString']) === false && $goodsPriceDisplayFl =='n') {
            $setData['goodsPriceDisplayFl'] = 'n';
        }

        return $setData;
    }

    /**
     * 주문상품갯수
     * @param integer $goodsNo 상품번호
     * @return integer $getData['goodsCnt'] 주문상품갯수
     */
    private function getOrderCnt($goodsNo)
    {
        $arrBind = [];
        $arrWhere[] = 'SUBSTR(orderStatus, 1, 1) IN (?, ?, ?, ?)';
        $this->db->bind_param_push($arrBind, 's', 'p');
        $this->db->bind_param_push($arrBind, 's', 'g');
        $this->db->bind_param_push($arrBind, 's', 'd');
        $this->db->bind_param_push($arrBind, 's', 's');
        $arrWhere[] = 'goodsNo = ?';
        $this->db->bind_param_push($arrBind, 'i', $goodsNo);

        $this->db->strField = 'SUM(goodsCnt) as goodsCnt';
        $this->db->strWhere = implode(' AND ', gd_isset($arrWhere));

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_GOODS . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind, false);

        return $getData['goodsCnt'];
    }

    /**
     * 팝업 노출 페이지
     * @return array  페이지 정보
     */
    public function getPopupPageOutput()
    {
        // 팝업 노출 페이지 정보
        if (\Request::isMobile() === true && $this->cfg['mobileFl'] != 'y') {
            $this->cfg['displayPage'] = $this->cfg['displayPageMobile'];
        }
        $setData = [];
        foreach ($this->cfg['displayPage'] as $page) {
            $setData = array_merge($setData, self::BW_PAGE[$page]['page']);
        }

        return $setData;
    }
}
