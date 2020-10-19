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
namespace Bundle\Component\Worker;


use Component\Board\ArticleListAdmin;
use Component\Board\Board;
use Component\Board\BoardList;
use Component\Coupon\Coupon;
use Component\Database\DBTableField;
use Component\Delivery\Delivery;
use Component\Policy\Policy;
use Component\Storage\Storage;
use Framework\Utility\SkinUtils;
use Framework\Utility\StringUtils;
use Request;
use UserFilePath;

/**
 * Naversummary
 *
 * @author    atomyang
 * @version   1.0
 * @since     1.0
 * @copyright ⓒ 2016, NHN godo: Corp.
 */
class DbUrl
{
    protected $db;
    protected $goods;
    protected $delivery;
    protected $cate;
    protected $coupon;
    protected $isMerge = false;
    protected $mergedFilePath;

    public $mallData;
    public $imageSize;
    public $mileage;
    public $couponConfig;
    public $fileConfig;
    public $configData;
    public $mobileConfig;
    public $truncGoods;
    public $couponForEpPcList;
    public $couponForEpMobileList;
    public $mallUrl;
    public $totalDburlData;
    public $totalDburlPage;
    public $dburl_max_count;

    public function __construct()
    {
        ini_set('memory_limit', '-1');
        set_time_limit(RUN_TIME_LIMIT);

        if (!is_object($this->db)) {
            $this->db = \App::load('DB');
        }

        $this->deliveryArr = [];
        $this->categoryArr = [];
        $this->brandArr = [];
    }

    public function getConfigData($site)
    {
        if ($site == 'naver') {
            $dbUrl = \App::load('\\Component\\Marketing\\DBUrl');
            $configData = $dbUrl->getConfig('naver', 'config');

            if ($configData['naverFl'] != 'y') {
                return false;
            }

        } else if ($site == 'daum') {
            $dbUrl = \App::load('\\Component\\Marketing\\DBUrl');
            $configData = $dbUrl->getConfig('daumcpc', 'config');
            if ($configData['useFl'] != 'y') {
                return false;
            }
        } else if ($site == 'payco') {
            $dbUrl = \App::load('\\Component\\Marketing\\DBUrl');
            $configPaycoData = $dbUrl->getConfig('payco', 'config');
            if ($configPaycoData['paycoFl'] != 'y') {
                return false;
            } else {
                $configData = $dbUrl->getConfig('naver', 'config');
                unset($configData['naverFl']);
                $configData['paycoFl'] = $configPaycoData['paycoFl'];
            }
        } else if ($site == 'targetingGates') {
            $dbUrl = \App::load('Component\\Marketing\\TargetingGates');
            $configData = $dbUrl->getConfig();
            if ($configData['tgFl'] == 'n') {
                return false;
            }
        }

        return $configData;
    }

    /*
     * GearMan에서 호출
     */

    public function run($params = null)
    {
        $this->fileConfig = $params;
        $this->configData = $this->getConfigData($params['site']);

        $trunc = gd_policy('basic.trunc');
        $this->mallData = gd_policy('basic.info');
        $this->imageSize = SkinUtils::getGoodsImageSize('magnify');
        $this->mileage['give'] = gd_policy('member.mileageGive');
        $this->mileage['trunc'] = $trunc['mileage'];
        $this->couponConfig = gd_policy('coupon.config');
        $this->mobileConfig = gd_policy('mobile.config');
        $this->truncGoods =  $trunc['goods'];

        $this->goods = \App::load('\\Component\\Goods\\Goods');
        $this->cate = \App::load('\\Component\\Category\\Category');
        $this->delivery = new Delivery();
        $this->coupon = new Coupon();

        if($this->couponConfig['couponUseType'] == 'y') {
            // 네이버/다음 동일 적용 , 모든 사람이 사용 가능한 쿠폰기준
            $appendCouponWhere[] = "couponMinOrderPrice = 0 "; //조건가격이 없고
            $appendCouponWhere[] = "couponAmountType = 'n' "; //수량이 무제한이고
            $appendCouponWhere[] = "(couponApplyMemberGroup = '' or  couponApplyMemberGroup is null) "; //회원등급 제한이 없고
            $this->couponForEpPcList = $this->coupon->getGoodsCouponDownListAll($appendCouponWhere,"pc");
            if($params['site'] == 'daum'){
                $this->couponForEpMobileList = $this->coupon->getGoodsCouponDownListAll($appendCouponWhere,"mobile");
            }

            unset($appendCouponWhere);
        }


        if (Request::isCli()) $this->mallUrl = 'http://' . $this->mallData['mallDomain'];
        else $this->mallUrl =  Request::getDomainUrl();

        $this->totalDburlData =0;
        $this->totalDburlPage =0;

        if ($this->configData === false) {
            return false;
        }

        if ($params['mode'] == 'summary') {

            if($params['site'] == 'naver' && $this->configData['naverVersion'] =='3') return true;

            $arrInclude[] = [
                'goodsNo',
                'cateCd',
                'goodsPrice',
                'fixedPrice',
                'goodsWeight',
                'goodsModelNo',
                'makerNm',
                'originNm',
                'deliverySno',
                'goodsNm',
                'goodsNmPartner',
                'imageStorage',
                'mileageFl',
                'mileageGoods',
                'mileageGoodsUnit',
                'goodsPriceString',
                'imagePath',
                'goodsDiscountFl',
                'goodsDiscount',
                'goodsDiscountUnit',
                'eventDescription',
                'modDt',
                'regDt',
                'goodsDisplayFl',
                'delFl',
                'applyFl',
                'goodsOpenDt',
                'onlyAdultFl',
                'salesStartYmd',
                'salesEndYmd',
                'goodsMustInfo',
                'reviewCnt',
                'plusReviewCnt',
                'cremaReviewCnt',
                'brandCd',
                'goodsSearchWord',
                'naverImportFlag',
                'naverProductFlag',
                'naverAgeGroup',
                'naverGender',
                'naverAttribute',
                'naverCategory',
                'naverProductId',
            ];

            $arrField =  implode(',', DBTableField::setTableField('tableGoods', $arrInclude[0], null, 'g')) . ', gun.class, if(gun.modDt is null, gun.regDt, gun.modDt) as updateTime,  \'\' as goodsImage,( if (g.soldOutFl = \'y\' , \'y\', if (g.stockFl = \'y\' AND g.totalStock <= 0, \'y\', \'n\') ) ) as soldOut,GROUP_CONCAT( gi.imageKind SEPARATOR "^|^") AS imageKind,GROUP_CONCAT( gi.imageName SEPARATOR "^|^") AS imageName';

            $arrJoin[] = ' INNER JOIN ' . DB_GOODS . ' AS g ON g.goodsNo = gun.mapid';
            $arrJoin[] = ' INNER JOIN '.DB_GOODS_IMAGE.' gi on gi.goodsNo = gun.mapid AND  gi.imageKind IN ("magnify", "detail") AND gi.imageNo = "0"';

            $this->db->strField = $arrField;
            $this->db->strJoin = implode('', $arrJoin);

            if ($params['site'] == 'naver') {
                $checkFieldName = "naverCheckFl";
                $where[] = " g.naverFl = 'y' ";
            } else if ($params['site'] == 'daum') {
                $checkFieldName = "daumCheckFl";
                $where[] = " g.daumFl = 'y' ";
            } else if ($params['site'] == 'payco') {
                $checkFieldName = "paycoCheckFl";
                $where[] = " g.paycoFl = 'y' ";
                $where[] = 'g.goodsDisplayFl = \'y\'';
            }

            $where[] = ' gun.' . $checkFieldName . " = 'n' ";
            $where[] = " g.delFl = 'n' ";
            if ($params['site'] == 'payco') {
                $where[] = " (gun.regDt >= DATE_ADD(NOW(), INTERVAL -1 HOUR) OR gun.modDt >= DATE_ADD(NOW(), INTERVAL -1 HOUR)) ";
                $this->db->strOrder = ' IF(gun.modDt IS NULL, gun.regDt, gun.modDt) ASC';
            }

            $this->db->strWhere = implode(' AND ', $where);
            //$this->db->strOrder = 'if(g.modDt < 0,g.regDt,g.modDt) DESC';

            $this->db->strGroup = 'gun.mapid';

            $query = $this->db->query_complete();
            $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_GOODS_UPDATET_NAVER . ' as gun' . implode(' ', $query). ' limit 5000';
            $getData = $this->db->query_fetch($strSQL, $this->arrBind);
            if($getData && $this->couponConfig['couponUseType'] == 'y') {
                $strCouponSQL = 'SELECT gun.mapid,GROUP_CONCAT( glc.cateCd SEPARATOR "^|^") AS cateCd FROM '.DB_GOODS_UPDATET_NAVER.' as gun INNER JOIN '.DB_GOODS_LINK_CATEGORY.' glc on glc.goodsNo = gun.mapid WHERE gun.naverCheckFl = "n" GROUP BY gun.mapid';
                $goodsCateList = $this->db->query_fetch($strCouponSQL, null);
                $this->goodsCateList = array_combine (array_column($goodsCateList, 'mapid'),array_column($goodsCateList, 'cateCd'));
                unset($strCouponSQL,$goodsCateList);
            }

            if ($params['site'] == 'naver') { //네이버 정책으로 50만건 제한
                $this->setNaverDbUrl($getData);
            } else if ($params['site'] == 'payco') {
                $this->setPaycoDbUrl($getData);
            } else {
                $this->setDaumDbUrl($getData);
            }

        } else if ($params['mode'] == 'review') {
            $query = "SELECT * FROM " . DB_BD_ . Board::BASIC_GOODS_REIVEW_ID . " WHERE TO_DAYS(now()) - TO_DAYS(regDt) <= 7  AND (goodsNo != '' OR goodsNo is not null)";
            $getData = $this->db->query_fetch($query);

            if ($params['site'] == 'naver') { //네이버 정책으로 50만건 제한
                $this->setNaverDbUrl($getData);
            } else if ($params['site'] == 'payco') {
                $this->setPaycoDbUrl($getData);
            } else {
                $this->setDaumDbUrl($getData);
            }

        } else {

            $arrInclude[] = [
                'goodsNo',
                'cateCd',
                'fixedPrice',
                'goodsPrice',
                'goodsWeight',
                'goodsModelNo',
                'makerNm',
                'originNm',
                'deliverySno',
                'goodsNm',
                'goodsNmPartner',
                'imageStorage',
                'mileageFl',
                'mileageGoods',
                'mileageGoodsUnit',
                'goodsPriceString',
                'imagePath',
                'goodsDiscountFl',
                'goodsDiscount',
                'goodsDiscountUnit',
                'onlyAdultFl',
                'salesStartYmd',
                'salesEndYmd',
                'naverImportFlag',
                'naverProductFlag',
                'naverAgeGroup',
                'naverGender',
                'naverAttribute',
                'naverCategory',
                'naverProductId',
                'goodsState',
                'onlyAdultFl',
                'naverTag',
                'minOrderCnt',
                'optionFl',
                'optionName',
                'eventDescription',
                'salesUnit',
                'goodsMustInfo',
                'brandCd',
                'reviewCnt',
                'plusReviewCnt',
                'cremaReviewCnt',
            ];

            $arrField =  implode(',', DBTableField::setTableField('tableGoods', $arrInclude[0], null, 'g'));

            $where[] = 'g.goodsDisplayFl = \'y\'';
            $where[] = 'g.delFl = \'n\'';
            $where[] = 'g.applyFl = \'y\'';
            $where[] = 'NOT(g.stockFl = \'y\' AND g.totalStock = 0)';
            $where[] = 'NOT(g.soldOutFl = \'y\')';
            $where[] = '(UNIX_TIMESTAMP(g.goodsOpenDt) IS NULL  OR UNIX_TIMESTAMP(g.goodsOpenDt) = 0 OR UNIX_TIMESTAMP(g.goodsOpenDt) < UNIX_TIMESTAMP())';


            if($params['site'] == 'targetingGates') {
                $strSQL = ' SELECT min(g.goodsNo) AS startGoodsNo,max(g.goodsNo) AS endGoodsNo FROM ' . DB_GOODS . ' as g  WHERE ' . implode(' AND ', $where);
                $res = $this->db->query_fetch($strSQL, $this->arrBind, false);
                $startGoodsNo = $res['startGoodsNo']; // 시작 goodsNo
                $endGoodsNo = $res['endGoodsNo']; // 시작 goodsNo
            } else {
                if ($params['site'] == 'daum') {
                    $where[] = " g.daumFl = 'y'";
                    $strSQL = ' SELECT  COUNT(g.goodsNo) AS cnt,min(g.goodsNo) AS startGoodsNo,max(g.goodsNo) AS endGoodsNo FROM ' . DB_GOODS . ' as g  WHERE ' . implode(' AND ', $where);
                    $res = $this->db->query_fetch($strSQL, $this->arrBind, false);
                    $totalCount = $res['cnt']; // 전체
                    $startGoodsNo = $res['startGoodsNo']; // 시작 goodsNo
                    $endGoodsNo = $res['endGoodsNo']; // 시작 goodsNo
                } else if($params['site'] == 'naver') {
                    $where[] = " g.naverFl = 'y'";
                    $strSQL = ' SELECT min(g.goodsNo) AS startGoodsNo,max(g.goodsNo) AS endGoodsNo FROM ' . DB_GOODS . ' as g  WHERE ' . implode(' AND ', $where);
                    $res = $this->db->query_fetch($strSQL, $this->arrBind, false);
                    $startGoodsNo = $res['startGoodsNo']; // 시작 goodsNo
                    $endGoodsNo = $res['endGoodsNo']; // 시작 goodsNo
                } else if($params['site'] == 'payco') {
                    $where[] = " g.paycoFl = 'y'";
                    $strSQL = ' SELECT min(g.goodsNo) AS startGoodsNo,max(g.goodsNo) AS endGoodsNo FROM ' . DB_GOODS . ' as g  WHERE ' . implode(' AND ', $where);
                    $res = $this->db->query_fetch($strSQL, $this->arrBind, false);
                    $startGoodsNo = $res['startGoodsNo']; // 시작 goodsNo
                    $endGoodsNo = $res['endGoodsNo']; // 시작 goodsNo
                }
            }
            if ($startGoodsNo == null || $endGoodsNo == null) {
                return false;
            }
            if ($params['site'] == 'targetingGates') {
                $this->dburl_max_count = "200000";
            } else {
                $this->dburl_max_count = "500000";
            }

            $this->db->query("SET @@group_concat_max_len = 10000");

            $lastPageNum = ceil(($endGoodsNo-$startGoodsNo)/10000)-1;
            $pageNum = 0;
            for ($i = $endGoodsNo; $i >= $startGoodsNo; $i = $i-10001) {
                if($this->totalDburlData < $this->dburl_max_count) {
                    $this->db->strField = $arrField.",GROUP_CONCAT( gi.imageNo SEPARATOR '^|^') AS imageNo,GROUP_CONCAT( gi.imageKind SEPARATOR '^|^') AS imageKind,GROUP_CONCAT( gi.imageName SEPARATOR '^|^') AS imageName";
                    $this->db->strWhere = implode(' AND ', $where)." AND g.goodsNo between ".($i-10000)." AND ".$i;
                    $this->db->strOrder = 'g.goodsNo DESC';
                    $this->db->strGroup = "g.goodsNo";


                    if ($params['site'] == 'naver') { //네이버 정책으로 50만건 제한
                        if ($this->configData['naverVersion'] == '3') {
                            $this->db->strJoin = 'INNER JOIN es_goodsImage gi on gi.goodsNo = g.goodsNo AND  gi.imageKind IN ("magnify", "detail") AND gi.imageNo < 10';

                        } else {
                            $this->db->strJoin = 'INNER JOIN es_goodsImage gi on gi.goodsNo = g.goodsNo AND  gi.imageKind IN ("magnify", "detail") AND gi.imageNo = 0';
                        }
                    } else {
                        $this->db->strJoin = 'INNER JOIN es_goodsImage gi on gi.goodsNo = g.goodsNo AND  gi.imageKind IN ("magnify", "detail") AND gi.imageNo = 0';
                    }


                    $query = $this->db->query_complete();
                    $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_GOODS . ' as g' . implode(' ', $query);

                    $getData = $this->db->query_fetch_generator($strSQL);

                    if (count($getData)) {
                        if($this->couponConfig['couponUseType'] == 'y') {
                            $this->db->strJoin = "";
                            $strSQL = 'SELECT g.goodsNo,GROUP_CONCAT( glc.cateCd SEPARATOR "^|^") AS cateCd FROM '.DB_GOODS.' as g INNER JOIN '.DB_GOODS_LINK_CATEGORY.' glc on glc.goodsNo = g.goodsNo '.$query['where'].' GROUP BY g.goodsNo'.$query['order'];
                            foreach($this->db->query_fetch_generator($strSQL) as $cateKey => $cateVal) {
                                $this->goodsCateList[$cateVal['goodsNo']] =    $cateVal['cateCd'];
                            }
                        }
                        $this->isMerge = false;
                        if ($params['site'] == 'naver') {
                            if ($this->configData['naverVersion'] == '3') {
                                $result = $this->setNaverDbUrlEP3($getData,$pageNum,$lastPageNum);
                                if($result === false) return false;
                            } else {
                                $result = $this->setNaverDbUrl($getData,$pageNum,$lastPageNum);
                                if($result === false) return false;
                            }
                        } else {
                            if ($params['site'] == 'targetingGates') {
                                $this->setTgDbUrl($getData,$pageNum,$lastPageNum);
                            } else if ($params['site'] == 'payco') {
                                $this->setPaycoDbUrl($getData,$pageNum,$lastPageNum);
                            } else {
                                $this->setDaumDbUrl($getData,$pageNum,$lastPageNum,$totalCount);
                            }
                        }

                        unset($this->goodsCateList);
                    }
                    unset($getData);
                    $pageNum++;
                } else {
                    break;
                }
            }
            // 상품 시작번호와 종료 번호의 차이가 큰 경우(1억 정도) 파일 머지 조건 식에 오류가 있음
            if (!$this->isMerge && !empty($this->mergedFilePath) && ($this->totalDburlData >= $this->dburl_max_count || $lastPageNum == $pageNum)) {
                $this->fileMerge($this->totalDburlPage, $this->mergedFilePath);
            }

        }
        return true;
    }

    /*
     * 배송비 가져오기
     */
    public function setDeliveryPrice($getData)
    {
        //배송비
        if ($getData['deliverySno']) {
            //배송비 부분 상품 상세 참조
            if(empty($this->deliveryArr[$getData['deliverySno']]) === true) {
                $deliveryData = $this->delivery->getDataSnoDelivery($getData['deliverySno'],false);
                $this->deliveryArr[$getData['deliverySno']] = $deliveryData;
            }
            $deliveryData = $this->deliveryArr[$getData['deliverySno']];

            if($deliveryData) {
                $price= 0;

                if (in_array($deliveryData['basic']['fixFl'], ['price', 'weight', 'count'])) {

                    if($deliveryData['basic']['rangeRepeat'] === 'y'){
                        //범위 반복 구간이 설정되어 있을 경우
                        $standardInt = 0;
                        $deliveryStandardFinal = 0;
                        $deliveryStandardNum = 0;
                        $conditionRange = $deliveryData['charge'][0];
                        $conditionRepeat = $deliveryData['charge'][1];

                        if($deliveryData['basic']['fixFl'] === 'count'){
                            //수량별 배송비일 경우
                            $standardInt = 1;
                        }
                        else {
                            //금액별, 무게별 배송비일 경우
                            $standardInt = $getData['goods' . ucfirst($deliveryData['basic']['fixFl'])];
                        }

                        if ($standardInt > 0) {
                            $price = $conditionRange['price'];

                            if($conditionRepeat['unitEnd'] > 0){
                                $deliveryStandardNum = $standardInt-$conditionRepeat['unitStart'];
                                if(!$deliveryStandardNum){
                                    $deliveryStandardNum = 0;
                                }

                                if($deliveryStandardNum >= 0){
                                    $deliveryStandardFinal = ($deliveryStandardNum/$conditionRepeat['unitEnd']);
                                    if(!$deliveryStandardFinal){
                                        $deliveryStandardFinal = 0;
                                    }

                                    if(preg_match('/\./', (string)$deliveryStandardFinal)){
                                        $deliveryStandardFinal = (int)ceil($deliveryStandardFinal);
                                    }
                                    else {
                                        $deliveryStandardFinal += 1;
                                    }

                                    $price += ($deliveryStandardFinal * $conditionRepeat['price']);
                                }
                            }
                        }
                    }
                    else {
                        //범위 반복 구간이 설정되어 있지 않을 경우

                        if($deliveryData['basic']['fixFl'] === 'count'){
                            //수량별 배송비일 경우
                            $price= $deliveryData['charge'][0]['price'];
                        }
                        else {
                            //금액별, 무게별 배송비일 경우

                            // 비교할 필드값 설정
                            $compareField = $getData['goods' . ucfirst($deliveryData['basic']['fixFl'])];
                            foreach ($deliveryData['charge'] as $dKey => $dVal) {
                                // 금액 or 무게가 범위에 없으면 통과
                                if (intval($dVal['unitEnd']) > 0) {
                                    if (intval($dVal['unitStart']) <= intval($compareField) && intval($dVal['unitEnd']) > intval($compareField)) {
                                        $price  = $dVal['price'];
                                        break;
                                    }
                                } else {
                                    if (intval($dVal['unitStart']) <= intval($compareField)) {
                                        $price = $dVal['price'];
                                        break;
                                    }
                                }
                            }
                        }
                    }
                } else {
                    $price= $deliveryData['charge'][0]['price'];
                }

                if ($deliveryData['basic']['fixFl'] == 'free') {
                    return 0;
                } else {
                    if ($deliveryData['basic']['collectFl'] == 'pre') {
                        return $price == 0 ? 0 : $price;
                    } else if ($deliveryData['basic']['collectFl'] == 'later') {
                        return -1;
                    } else if ($deliveryData['basic']['collectFl'] == 'both') {  //선불or착불 둘다 선택가능한 배송조건인 경우 선불로 체크
                        return $price;
                    } else {
                        return 0;
                    }
                }

            } else {
                return false;
            }

        } else {
            return false;
        }
    }

    /*
     * 배송전책 가져오기
     */
    public function getDeliveryData($deliverySno)
    {
        return $this->delivery->getSnoDeliveryCharge($deliverySno);
    }


    /*
     * 마일리지 가져오기
     */
    public function setGoodsMileage($data, $type = 'naver')
    {
        // 마일리지 처리

        $mileage = $this->mileage;

        $data['goodsMileageFl'] = 'y';
        // 통합 설정인 경우 마일리지 설정
        if ($data['mileageFl'] == 'c' && $mileage['give']['giveFl'] == 'y') {
            if ($mileage['give']['giveType'] == 'priceUnit') { // 금액 단위별
                $mileagePrice = floor($data['goodsPrice'] / $mileage['give']['goodsPriceUnit']);
                $mileageBasic = gd_number_figure($mileagePrice * $mileage['give']['goodsMileage'], $mileage['trunc']['unitPrecision'], $mileage['trunc']['unitRound']);
                if ($type == 'daum') {
                    $mileageBasic .= '원';
                }
            } else if ($mileage['give']['giveType'] == 'cntUnit') { // 수량 단위별 (추가상품수량은 제외)
                $mileageBasic = gd_number_figure(1 * $mileage['give']['cntMileage'], $mileage['trunc']['unitPrecision'], $mileage['trunc']['unitRound']);
                if ($type == 'daum') {
                    $mileageBasic .= '원';
                }
            } else { // 구매금액의 %
                $mileagePercent = $mileage['give']['goods'] / 100;
                $mileageBasic = gd_number_figure($data['goodsPrice'] * $mileagePercent, $mileage['trunc']['unitPrecision'], $mileage['trunc']['unitRound']);
                if ($type == 'daum') {
                    $mileageBasic = $mileage['give']['goods'] . '%';
                }
            }
            // 개별 설정인 경우 마일리지 설정
        } else if ($data['mileageFl'] == 'g') {
            $mileagePercent = $data['mileageGoods'] / 100;
            // 상품 기본 마일리지 정보
            if ($data['mileageGoodsUnit'] === 'percent') {
                $mileageBasic = gd_number_figure($data['goodsPrice'] * $mileagePercent, $mileage['trunc']['unitPrecision'], $mileage['trunc']['unitRound']);
                if ($type == 'daum') {
                    $mileageBasic = $data['mileageGoods'] * 1 . '%';   //유효숫자만 가져오기위해 곱하기1
                }
            } else {
                // 정액인 경우 해당 설정된 금액으로
                $mileageBasic = gd_number_figure($data['mileageGoods'], $mileage['trunc']['unitPrecision'], $mileage['trunc']['unitRound']);
                if ($type == 'daum') {
                    $mileageBasic .= '원';
                }
            }

        } else {
            $mileageBasic = 0;
        }

        return $mileageBasic;
    }


    /*
    * 쿠폰가격
    */
    public function setGoodsCoupon($data, $device = null, $type = 'naver')
    {
        // 쿠폰 할인 금액
        if ($this->couponConfig['couponUseType'] == 'y' && $data['goodsPrice'] > 0 && empty($data['goodsPriceString']) === true) {
            // 해당 상품의 쿠폰가
            $data['cateCdArr'] = explode(STR_DIVISION,$this->goodsCateList[$data['goodsNo']]);
            if($device =='mobile') {
                $couponSalePrice = $this->coupon->getGoodsCouponDownListPrice($data,$this->couponForEpMobileList);
            } else {
                $couponSalePrice = $this->coupon->getGoodsCouponDownListPrice($data,$this->couponForEpPcList);
            }
        }

        return gd_isset($couponSalePrice, 0);
    }


    /**
     * 상품의 상품할인가 반환
     *
     * @param array $aGoodsInfo 상품정보
     * @return int 상품할인가반환
     */
    public function getGoodsDcPrice($aGoodsInfo)
    {
        // 상품 할인 기준 금액 처리
        $tmp['discountByPrice'] = $aGoodsInfo['goodsPrice'];

        // 절사 내용
        $tmp['trunc'] = $this->truncGoods;

        if ($aGoodsInfo['goodsDiscountUnit'] === 'percent') {
            // 상품할인금액
            $discountPercent = $aGoodsInfo['goodsDiscount'] / 100;
            $goodsDcPrice = gd_number_figure($tmp['discountByPrice'] * $discountPercent, $tmp['trunc']['unitPrecision'], $tmp['trunc']['unitRound']);
        } else {
            // 상품할인금액 (정액인 경우 해당 설정된 금액으로)
            $goodsDcPrice = $aGoodsInfo['goodsDiscount'];
        }

        //할인된 금액 반환
        return $goodsDcPrice;
    }

    /**
     * 상품이미지
     *
     */
    public function getGoodsImage($imageName,$imagePath,$imageStorage,$imageSize)
    {
        if ((strpos(strtolower($imageName), 'http://') !== false || strpos(strtolower($imageName), 'https://') !== false) && $imageStorage != 'url') {
            return $imageName;
        } else {
            if ($imageStorage == 'url') {
                return $imageName;
            } else {
                if(empty($this->imageStoragePath[$imageStorage]) === true) {
                    $this->imageStoragePath['real'][$imageStorage] = Storage::disk(Storage::PATH_CODE_GOODS . DS, $imageStorage)->getRealPath();
                    $this->imageStoragePath['http'][$imageStorage] = Storage::disk(Storage::PATH_CODE_GOODS . DS, $imageStorage)->getHttpPath();
                }

                if($imageStorage =='local') {
                    $imageRealPath =  $this->imageStoragePath['real'][$imageStorage].$imagePath.$imageName;
                    if(is_file($imageRealPath)) {
                        return $this->mallUrl.$this->imageStoragePath['http'][$imageStorage].$imagePath.$imageName;
                    } else {
                        return false;
                    }
                } else {
                    return $this->imageStoragePath['http'][$imageStorage].$imagePath.$imageName;
                }
            }
        }
    }

    public function setNaverDbUrl($getData,$page,$lastPageNum)
    {
        if ($this->fileConfig['mode'] == 'all') {
            $filename = $this->fileConfig['filename'] ?? $this->fileConfig['site'] . '_' . $this->fileConfig['mode'];
            $tmpFilename = $filename . '_tmp_'.$page;

            $path = UserFilePath::data('dburl', $this->fileConfig['site'], $filename)->getRealPath();
            $tmpPath = UserFilePath::data('dburl', $this->fileConfig['site'], $tmpFilename)->getRealPath();
            $this->initFile($tmpPath);
            $this->totalDburlPage++;
            $fh = fopen($tmpPath, 'a+');
        }

        if (gd_isset($getData)) {
            foreach ($getData as $k => $data) {
                if($this->fileConfig['mode'] == 'all' && $this->totalDburlData >= $this->dburl_max_count)  {
                    break;
                }
                if($data['goodsNmPartner']) $data['goodsNm'] = $data['goodsNmPartner'];

                $cateListCd = [];
                $cateListNm = [];
                if ($data['cateCd']) {
                    if(empty($this->categoryArr[$data['cateCd']]) === true) {
                        $cateList = $this->cate->getCategoriesPosition($data['cateCd'])[0];
                        $this->categoryArr[$data['cateCd']] = $cateList;
                    }
                    $cateList = $this->categoryArr[$data['cateCd']];

                    if ($cateList) {
                        $cateListCd = array_keys($cateList);
                        $cateListNm = array_values($cateList);
                    }
                }

                $goodsPrice = $data['goodsPrice'];

                //타임세일 판매가 추가
                if ($this->configData['dcTimeSale'] == 'y' && $goodsPrice > 0 && gd_is_plus_shop(PLUSSHOP_CODE_TIMESALE) === true) {
                    $timeSale = \App::load('\\Component\\Promotion\\TimeSale');
                    $timeSaleInfo = $timeSale->getGoodsTimeSale($data['goodsNo']);
                    if ($timeSaleInfo) {
                        $truncPolicy = gd_policy('basic.trunc.goods'); // 절사 내용
                        $goodsPrice = gd_number_figure($goodsPrice - (($timeSaleInfo['benefit'] / 100) * $goodsPrice), $truncPolicy['unitPrecision'], $truncPolicy['unitRound']);
                        $data['goodsPrice'] = $goodsPrice;
                    }
                }

                if ($this->configData['dcGoods'] == 'y' && $data['goodsPrice'] > 0 && $data['goodsDiscountFl'] == 'y') {
                    $goodsPrice = $goodsPrice - $this->getGoodsDcPrice($data);
                }

                $deliveryPrice = $this->setDeliveryPrice($data);
                $couponPrice = gd_isset($this->setGoodsCoupon($data), 0);

                if ($this->configData['dcCoupon'] == 'y' && $couponPrice > 0 && $goodsPrice - $couponPrice >= 0) {
                    $goodsPrice = $goodsPrice - $couponPrice;
                } else {
                    if($goodsPrice - $couponPrice < 0) $goodsPrice=0;
                    $couponPrice = 0;
                }

                $goodsImageSrc = "";
                if($data['imageName']) {
                    $imageName = explode(STR_DIVISION,$data['imageName']);
                    $imageKind = explode(STR_DIVISION,$data['imageKind']);

                    $tmpImageNo = array_search('magnify',$imageKind);
                    if(empty($tmpImageNo) === true) {
                        $goodsImageSrc = $this->getGoodsImage($imageName[0],$data['imagePath'],$data['imageStorage'],$this->imageSize['size1']);
                    } else {
                        $goodsImageSrc = $this->getGoodsImage($imageName[$tmpImageNo],$data['imagePath'],$data['imageStorage'],$this->imageSize['size1']);
                    }
                    unset($imageName,$imageKind);
                }

                if(!is_numeric($deliveryPrice) || empty($data['goodsPriceString']) === false || empty($goodsImageSrc) === true ) {
                    continue;
                }

                $installationCosts = "";
                $deliveryGrade = "";
                $deliveryDetail = "";

                $data['goodsMustInfo'] = json_decode($data['goodsMustInfo'],true);
                foreach($data['goodsMustInfo'] as $mustKey => $mustValue) {
                    if($mustValue['step0']['infoTitle'] =='배송 · 설치비용' && $deliveryDetail == '') {
                        $deliveryGrade = "Y";
                        $deliveryDetail = $mustValue['step0']['infoValue'] ;
                    }

                    if($mustValue['step0']['infoTitle'] =='추가설치비용') {
                        $installationCosts = "Y";
                    }
                }

                $result = '';
                $result .= '<<<begin>>>' . chr(13) . chr(10);
                $result .= '<<<mapid>>>' . $data['goodsNo'] . chr(13) . chr(10); // [필수] 쇼핑몰 상품ID

                if ($this->fileConfig['mode'] == 'all' || ($data['class'] == 'I' || $data['class'] == 'U')) {

                    if(empty($this->brandArr[$data['brandCd']]) === true && $data['brandCd']) {
                        $brandQuery = "SELECT cateNm FROM " . DB_CATEGORY_BRAND . " WHERE cateCd = '" . $data['brandCd'] . "'";
                        $brandData = $this->db->query_fetch($brandQuery, null, false);
                        $this->brandArr[$data['brandCd']] = $brandData['cateNm'];
                        unset($brandData,$brandQuery);
                    }
                    $data['brandNm'] = $this->brandArr[$data['brandCd']];

                    if($this->configData['goodsHead']) {
                        $data['goodsNm']= str_replace(array('{_maker}','{_brand}', '{_goodsNo}'),array($data['makerNm'],$data['brandNm'], $data['goodsNo']),$this->configData['goodsHead']).' '.$data['goodsNm'];
                    }
                    $result .= '<<<pname>>>' .gd_htmlspecialchars_stripslashes($data['goodsNm']) . chr(13) . chr(10); // [필수] 상품명

                    $eventDesc = "";
                    if ($this->configData['naverEventCommon'] == 'y') $eventDesc = $this->configData['naverEventDescription'];
                    if ($this->configData['naverEventGoods'] == 'y' && gd_isset($data['eventDescription'])) $eventDesc .= $data['eventDescription'];


                    $result .= '<<<price>>>' . gd_money_format($goodsPrice, false) . chr(13) . chr(10); // [필수] 판매가격
                    $result .= '<<<pgurl>>>' . 'http://' . $this->mallData['mallDomain'] . '/goods/goods_view.php?goodsNo=' . $data['goodsNo'] . '&inflow=naver' . chr(13) . chr(10); // [필수] 상품의 상세페이지 주소
                    $result .= '<<<igurl>>>' . $goodsImageSrc . chr(13) . chr(10); // [필수] 이미지 URL

                    for ($i = 0; $i < 4; $i++) {
                        $result .= '<<<caid' . ($i + 1) . '>>>' . gd_isset($cateListCd[$i]) . chr(13) . chr(10); // [필수] 대분류 카테고리 코드
                    }
                    unset($cateListCd);

                    for ($i = 0; $i < 4; $i++) {
                        $result .= '<<<cate' . ($i + 1) . '>>>' . gd_isset($cateListNm[$i]) . chr(13) . chr(10); // [필수] 대분류 카테고리 코드
                    }
                    unset($cateListNm);

                    if (gd_isset($data['goodsModelNo'])) $result .= '<<<model>>>' . gd_isset($data['goodsModelNo']) . chr(13) . chr(10); // [선택] 모델명

                    if (gd_isset($data['brandNm'])) $result .= '<<<brand>>>' . gd_isset($data['brandNm']) . chr(13) . chr(10); // [선택] 브랜드

                    if (gd_isset($data['makerNm'])) $result .= '<<<maker>>>' . gd_isset($data['makerNm']) . chr(13) . chr(10); // [선택] 메이커

                    if (gd_isset($data['originNm'])) $result .= '<<<origi>>>' . gd_isset($data['originNm']) . chr(13) . chr(10); // [선택] 원산지

                    if (gd_isset($eventDesc)) $result .= '<<<event>>>' . gd_isset(gd_htmlspecialchars_stripslashes($eventDesc)) . chr(13) . chr(10); // 이벤트문구

                    if (gd_isset($couponPrice)) $result .= '<<<coupo>>>' . gd_money_format($couponPrice, false) . '원' . chr(13) . chr(10); // [선택] 쿠폰

                    if (gd_isset($this->configData['nv_pcard'])) $result .= '<<<pcard>>>' . $this->configData['nv_pcard'] . chr(13) . chr(10); // [선택] 무이자

                    $result .= '<<<point>>>' . gd_isset($this->setGoodsMileage($data)) . chr(13) . chr(10); // [선택] 마일리지

                    if (($reviewCnt = $this->getNaverReviewCount($data))> 0) {
                        $result .= '<<<revct>>>' . $reviewCnt . chr(13) . chr(10);
                    }

                    $result .= '<<<deliv>>>' . gd_isset($deliveryPrice) . chr(13) . chr(10); // [선택] 배송비

                    if (gd_isset($installationCosts)) $result .= '<<<insco>>>' . gd_isset($installationCosts) . chr(13) . chr(10); // 추가설치비용
                    if (gd_isset($deliveryGrade)) $result .= '<<<dlvga>>>' . gd_isset($deliveryGrade) . chr(13) . chr(10); // [선택] 배송/설치비용
                    if (gd_isset($deliveryDetail)) $result .= '<<<dlvdt>>>' . gd_isset(gd_htmlspecialchars_stripslashes($deliveryDetail)) . chr(13) . chr(10); // [선택] 배송/설치비용 사유
                }


                if ($this->fileConfig['mode'] == 'summary') {
                    if ($data['soldOut'] =='y' || $data['goodsDisplayFl'] == 'n' || $data['delFl'] == 'y' || $data['applyFl'] != 'y' || ($data['goodsOpenDt'] != '0000-00-00 00:00:00' && $data['goodsOpenDt'] > time())) {
                        $data['class'] = "D";
                    }
                    $result .= '<<<class>>>' . $data['class'] . chr(13) . chr(10); // [선택] 마일리지

                    if ($data['modDt']) $result .= '<<<utime>>>' . gd_date_format('Y-m-d H:i:s', $data['modDt']) . chr(13) . chr(10); // [선택] 마일리지
                    else $result .= '<<<utime>>>' . gd_date_format('Y-m-d H:i:s', $data['regDt']) . chr(13) . chr(10); // [선택] 마일리지
                }
                $result .= '<<<ftend>>>';

                if($this->fileConfig['mode'] == 'all') {
                    $this->totalDburlData++;
                    $fw = fwrite($fh, $result . chr(13) . chr(10));
                    if ($fw === false) {
                        fclose($fh);
                        return false;
                    }
                } else {
                    echo $result. chr(13) . chr(10);
                }
                unset($result,$fw);
            }

            if($this->fileConfig['mode'] == 'all') {
                fclose($fh);
                $this->mergedFilePath = $path;
                if($this->totalDburlData >= $this->dburl_max_count || $lastPageNum == $page) {
                    $this->fileMerge($this->totalDburlPage, $path);
                    exec('cp ' . $path . " " . $path.'_back_up');
                    return true;
                }

            }
            return true;
        }

    }

    public function setNaverDbUrlEP3($getData,$page,$lastPageNum)
    {
        $filename = $this->fileConfig['filename'] ?? $this->fileConfig['site'] . '_' . $this->fileConfig['mode'];
        $tmpFilename = $filename . '_tmp_'.$page;

        $path = UserFilePath::data('dburl', $this->fileConfig['site'], $filename)->getRealPath();
        $tmpPath = UserFilePath::data('dburl', $this->fileConfig['site'], $tmpFilename)->getRealPath();
        $this->initFile($tmpPath);
        $this->totalDburlPage++;

        $fh = fopen($tmpPath, 'a+');
        $mobileConfig = $this->mobileConfig;

        $naverHeader = ['id','title','price_pc','normal_price','link','image_link','add_image_link','category_name1','category_name2','category_name3','category_name4','naver_category','naver_product_id','condition','import_flag','parallel_import','order_made','product_flag','adult','model_number','brand','maker','origin','event_words','coupon','interest_free_event','point','installation_costs','search_tag','minimum_purchase_quantity','review_count','shipping','delivery_grade','delivery_detail','attribute','age_group','gender'];
        if ($mobileConfig['mobileShopFl'] == 'y') {
            array_splice( $naverHeader, 5, 0, 'mobile_link' );
        }

        if($page =='0') {
            fwrite($fh, implode(chr(9),$naverHeader) . chr(13) . chr(10));
        }

        if (gd_isset($getData)) {
            foreach ($getData as $k => $data) {
                if($this->totalDburlData >= $this->dburl_max_count)  {
                    break;
                }
                if($data['goodsNmPartner']) $data['goodsNm'] = $data['goodsNmPartner'];

                if (gd_isset($data['salesUnit'], 0) > $data['minOrderCnt']) {
                    $data['minOrderCnt'] = $data['salesUnit'];
                }

                $cateListNm = [];
                if ($data['cateCd']) {
                    if(empty($this->categoryArr[$data['cateCd']]) === true) {
                        $cateList = $this->cate->getCategoriesPosition($data['cateCd'])[0];
                        $this->categoryArr[$data['cateCd']] = $cateList;

                    }
                    $cateList = $this->categoryArr[$data['cateCd']];
                    if ($cateList) {
                        $cateListNm = array_values($cateList);
                    }
                    unset($cateList);
                }

                $minOrderCnt = gd_isset($data['minOrderCnt'],1);
                $goodsPrice = $data['goodsPrice'];

                //타임세일 판매가 추가
                if ($this->configData['dcTimeSale'] == 'y' && $goodsPrice > 0 && gd_is_plus_shop(PLUSSHOP_CODE_TIMESALE) === true) {
                    $timeSale = \App::load('\\Component\\Promotion\\TimeSale');
                    $timeSaleInfo = $timeSale->getGoodsTimeSale($data['goodsNo']);
                    if ($timeSaleInfo) {
                        $truncPolicy = gd_policy('basic.trunc.goods'); // 절사 내용
                        $goodsPrice = gd_number_figure($goodsPrice - (($timeSaleInfo['benefit'] / 100) * $goodsPrice), $truncPolicy['unitPrecision'], $truncPolicy['unitRound']);
                        $data['goodsPrice'] = $goodsPrice;
                    }
                }

                if ($this->configData['dcGoods'] == 'y' && $data['goodsPrice'] > 0 && $data['goodsDiscountFl'] == 'y') {
                    $goodsPrice = $goodsPrice - $this->getGoodsDcPrice($data);
                }

                $mileage = $this->setGoodsMileage($data);
                $goodsPrice =  $goodsPrice*$minOrderCnt;
                $deliveryPrice = $this->setDeliveryPrice($data);

                $couponPrice = gd_isset($this->setGoodsCoupon($data), 0);

                if ($this->configData['dcCoupon'] == 'y' && $couponPrice > 0 && $goodsPrice - $couponPrice >= 0) {
                    $goodsPrice = $goodsPrice - $couponPrice;
                } else {
                    if($goodsPrice - $couponPrice < 0) $goodsPrice=0;
                    $couponPrice = 0;
                }

                $addImage = [];
                $goodsAddImageSrc = $goodsImageSrc = "";
                if($data['imageName']) {
                    $imageName = explode(STR_DIVISION,$data['imageName']);
                    $imageKind = explode(STR_DIVISION,$data['imageKind']);
                    $imageNo = explode(STR_DIVISION,$data['imageNo']);

                    foreach($imageName as $tmpkey => $v) {
                        $tmpAddImage = $this->getGoodsImage($v,$data['imagePath'],$data['imageStorage'],$this->imageSize['size1']);
                        if($imageKind[$tmpkey] =='magnify' && $imageNo[$tmpkey] == 0  ) {
                            $goodsImageSrc = $tmpAddImage;
                        } else {
                            $addImage[] = $tmpAddImage;
                        }
                        unset($tmpAddImage);
                    }
                    unset($imageName,$imageKind,$imageNo);

                    if(empty($goodsImageSrc) === true) {
                        $goodsImageSrc  = $addImage[0];
                        unset($addImage[0]);
                    }

                    if($addImage) $goodsAddImageSrc =implode("|",array_slice($addImage,0,10));
                }

                unset($addImage);

                if(!is_numeric($deliveryPrice) || $goodsPrice <= 0 || empty($data['goodsPriceString']) === false   || empty($goodsImageSrc) === true) {
                    continue;
                }

                $installationCosts = "";
                $deliveryGrade = "";
                $deliveryDetail = "";

                $data['goodsMustInfo'] = json_decode($data['goodsMustInfo'],true);
                foreach($data['goodsMustInfo'] as $mustKey => $mustValue) {
                    if($mustValue['step0']['infoTitle'] =='배송 · 설치비용' && $deliveryDetail == '') {
                        $deliveryGrade = "Y";
                        $deliveryDetail = $mustValue['step0']['infoValue'] ;
                    }

                    if($mustValue['step0']['infoTitle'] =='추가설치비용') {
                        $installationCosts = "Y";
                    }
                }

                $result = '';
                $result .= $data['goodsNo'].chr(9);
                if(empty($this->brandArr[$data['brandCd']]) === true && $data['brandCd']) {
                    $brandQuery = "SELECT cateNm FROM " . DB_CATEGORY_BRAND . " WHERE cateCd = '" . $data['brandCd'] . "'";
                    $brandData = $this->db->query_fetch($brandQuery, null, false);
                    $this->brandArr[$data['brandCd']] = $brandData['cateNm'];
                    unset($brandData,$brandQuery);
                }
                $data['brandNm'] = $this->brandArr[$data['brandCd']];

                if($this->configData['goodsHead']) {
                    $data['goodsNm']=str_replace(array('{_maker}','{_brand}', '{_goodsNo}'),array($data['makerNm'],$data['brandNm'], $data['goodsNo']),$this->configData['goodsHead']).' '.$data['goodsNm'];
                }
                $result .= gd_htmlspecialchars_stripslashes($data['goodsNm']).chr(9);

                $eventDesc = "";
                if ($this->configData['naverEventCommon'] == 'y') $eventDesc = $this->configData['naverEventDescription'];
                if ($this->configData['naverEventGoods'] == 'y' && gd_isset($data['eventDescription'])) $eventDesc .= $data['eventDescription'];


                $result .= gd_money_format($goodsPrice, false).chr(9);
                $result .= gd_money_format($data['fixedPrice']*$minOrderCnt, false).chr(9);
                $result .= 'http://' . $this->mallData['mallDomain'] . '/goods/goods_view.php?goodsNo=' . $data['goodsNo'] . '&inflow=naver' . chr(9); // [필수] 상품의 상세페이지 주소
                if ($mobileConfig['mobileShopFl'] == 'y') {
                    $result .= 'http://m.' . $this->mallData['mallDomain'] . '/goods/goods_view.php?goodsNo=' . $data['goodsNo'] . '&inflow=naver' . chr(9); // [필수] 상품의 모바일페이지 주소
                }

                $result .=  $goodsImageSrc . chr(9); // [필수] 이미지 URL
                $result .=  $goodsAddImageSrc . chr(9); // [필수] 이미지 URL

                for ($i = 0; $i < 4; $i++) {
                    $result .=  gd_isset($cateListNm[$i]) . chr(9); // [필수] 대분류 카테고리 코드
                }
                unset($cateListNm);

                switch ($data['naverImportFlag']) {
                    case 'f':
                        $importFlag  = "Y";
                        $parallelImport   = "";
                        $orderMade  = "";
                        break;
                    case 'd':
                        $importFlag  = "";
                        $parallelImport   = "Y";
                        $orderMade  = "";
                        break;
                    case 'o':
                        $importFlag  = "";
                        $parallelImport   = "";
                        $orderMade  = "Y";
                        break;
                    default:
                        $importFlag  = "";
                        $parallelImport   = "";
                        $orderMade  = "";
                        break;
                }

                if($data['onlyAdultFl'] =='y') $onlyAdultFl = "Y";
                else $onlyAdultFl = "";

                $result .=   gd_isset($data['naverCategory']) .chr(9); // 네이버카테고리
                $result .=   gd_isset($data['naverProductId']) .chr(9); // 가격비교페이지ID
                $result .=   $this->goods->getGoodsStateList()[$data['goodsState']].chr(9); // 상품상태
                $result .=   $importFlag.chr(9); // 해외구매대행여부
                $result .=   $parallelImport.chr(9); // 병행수입여부
                $result .=   $orderMade.chr(9); // 주문제작상품여부
                $result .=   $this->goods->getGoodsSellType()[$data['naverProductFlag']].chr(9); // 판매방식구분
                $result .=   $onlyAdultFl . chr(9); // 미성년자구매불가상품여부

                $result .= gd_isset($data['goodsModelNo']) . chr(9); // [선택] 모델명
                $result .= gd_isset($data['brandNm']) . chr(9); // [선택] 모델명
                $result .= gd_isset($data['makerNm']) . chr(9); // [선택] 모델명
                $result .= gd_isset($data['originNm']) . chr(9); // [선택] 모델명
                $result .= $eventDesc . chr(9); // [선택] 이벤트문구
                if($couponPrice > 0 ) $result .=gd_money_format($couponPrice, false) . '원' . chr(9); // [선택] 쿠폰
                else  $result .= ''. chr(9); // [선택] 쿠폰
                $result .= $this->configData['nv_pcard']. chr(9); // [선택] 무이자
                if($mileage > 0 ) $result .= '쇼핑몰자체포인트^'.$mileage . chr(9); // [선택] 마일리지
                else $result .= '' . chr(9); // [선택] 마일리지
                $result .= gd_isset($installationCosts) .  chr(9); //추가설치비용
                $data['naverTag'] = implode("|",array_slice(explode("|",$data['naverTag']),0,10));
                $result .= gd_isset($data['naverTag']) .  chr(9); //기본정보-검색키워드 항목 사용
                $result .= $minOrderCnt. chr(9); //판매정보-구매수량설정 항목 사용
                $result .= gd_isset($this->getNaverReviewCount($data),0) . chr(9);
                $result .=  gd_isset($deliveryPrice) . chr(9); // [선택] 배송비
                $result .= gd_isset($deliveryGrade) .  chr(9); // 배송 · 설치비용
                $result .= gd_isset($deliveryDetail) .  chr(9); // 배송 · 설치비용 내용
                $result .=  gd_isset($data['naverAttribute']).chr(9); //추가 : 입력기능 추가

                $result .=   $this->goods->getGoodsAgeType()[gd_isset($data['naverAgeGroup'],'a')].chr(9); //(주 사용 연령대)
                $result .=   $this->goods->getGoodsGenderType()[$data['naverGender']]; //(주 사용 성별)
                $this->totalDburlData++;
                $fw = fwrite($fh,$result . chr(13) . chr(10));
                if($fw === false) {
                    fclose($fh);
                    return false;
                }
                unset($result,$fw);
            }

            fclose($fh);
            $this->mergedFilePath = $path;
            if($this->totalDburlData >= $this->dburl_max_count || $lastPageNum == $page) {
                $this->fileMerge($this->totalDburlPage, $path);
                exec('cp ' . $path . " " . $path.'_back_up');
                return true;
            }

            return true;
        }
        unset($getData);
    }

    public function setPaycoDbUrl($getData,$page = 0,$lastPageNum)
    {
        $filename = $this->fileConfig['filename'] ?? $this->fileConfig['site'] . '_' . $this->fileConfig['mode'];
        $tmpFilename = $filename . '_tmp_'.$page;

        $path = UserFilePath::data('dburl', $this->fileConfig['site'], $filename)->getRealPath();
        $tmpPath = UserFilePath::data('dburl', $this->fileConfig['site'], $tmpFilename)->getRealPath();
        $this->initFile($tmpPath);
        $this->totalDburlPage++;

        $fh = fopen($tmpPath, 'a+');
        $mobileConfig = $this->mobileConfig;

        $paycoHeader = ['id','title','price_pc','price_mobile','normal_price','link','image_link','add_image_link','category_name1','category_name2','category_name3','category_name4','naver_category','naver_product_id','condition','import_flag','parallel_import','order_made','product_flag','adult','model_number','brand','maker','origin','event_words','coupon','interest_free_event','point','installation_costs','search_tag','minimum_purchase_quantity','review_count','shipping','delivery_grade','delivery_detail','attribute','age_group','gender'];
        if ($mobileConfig['mobileShopFl'] == 'y') {
            array_splice( $paycoHeader, 5, 0, 'mobile_link' );
        }
        if ($this->fileConfig['mode'] == 'summary') {
            $paycoHeader = array_merge($paycoHeader, ['class', 'update_time']);
        }

        if($page =='0') {
            fwrite($fh, implode(chr(9),$paycoHeader) . chr(13) . chr(10));
        }

        if (gd_isset($getData)) {
            foreach ($getData as $k => $data) {
                /*if($this->totalDburlData >= $this->dburl_max_count)  {
                    break;
                }*/
                if($data['goodsNmPartner']) $data['goodsNm'] = $data['goodsNmPartner'];

                if (gd_isset($data['salesUnit'], 0) > $data['minOrderCnt']) {
                    $data['minOrderCnt'] = $data['salesUnit'];
                }

                $cateListNm = [];
                if ($data['cateCd']) {
                    if(empty($this->categoryArr[$data['cateCd']]) === true) {
                        $cateList = $this->cate->getCategoriesPosition($data['cateCd'])[0];
                        $this->categoryArr[$data['cateCd']] = $cateList;

                    }
                    $cateList = $this->categoryArr[$data['cateCd']];
                    if ($cateList) {
                        $cateListNm = array_values($cateList);
                    }
                    unset($cateList);
                }

                $minOrderCnt = StringUtils::strIsSet($data['minOrderCnt'], 1);
                $goodsPrice = $data['goodsPrice'];
                if ($this->configData['dcGoods'] == 'y' && $data['goodsPrice'] > 0 && $data['goodsDiscountFl'] == 'y') {
                    $goodsPrice = $goodsPrice - $this->getGoodsDcPrice($data);
                }

                $mileage = $this->setGoodsMileage($data);
                $goodsPrice =  $goodsPrice*$minOrderCnt;
                $deliveryPrice = $this->setDeliveryPrice($data);

                $couponPrice = gd_isset($this->setGoodsCoupon($data), 0);

                if ($this->configData['dcCoupon'] == 'y' && $couponPrice > 0 && $goodsPrice - $couponPrice >= 0) {
                    $goodsPrice = $goodsPrice - $couponPrice;
                } else {
                    if($goodsPrice - $couponPrice < 0) $goodsPrice=0;
                    $couponPrice = 0;
                }
                $addImage = [];
                $goodsAddImageSrc = $goodsImageSrc = "";
                if($data['imageName']) {
                    $imageName = explode(STR_DIVISION,$data['imageName']);
                    $imageKind = explode(STR_DIVISION,$data['imageKind']);
                    $imageNo = explode(STR_DIVISION,$data['imageNo']);

                    foreach($imageName as $tmpkey => $v) {
                        $tmpAddImage = $this->getGoodsImage($v,$data['imagePath'],$data['imageStorage'],$this->imageSize['size1']);
                        if($imageKind[$tmpkey] =='magnify' && $imageNo[$tmpkey] == 0  ) {
                            $goodsImageSrc = $tmpAddImage;
                        } else {
                            $addImage[] = $tmpAddImage;
                        }
                        unset($tmpAddImage);
                    }
                    unset($imageName,$imageKind,$imageNo);

                    if(empty($goodsImageSrc) === true) {
                        $goodsImageSrc  = $addImage[0];
                        unset($addImage[0]);
                    }

                    if($addImage) $goodsAddImageSrc =implode("|",array_slice($addImage,0,10));
                }

                unset($addImage);

                if(!is_numeric($deliveryPrice) || $goodsPrice <= 0 || empty($data['goodsPriceString']) === false   || empty($goodsImageSrc) === true) {
                    continue;
                }

                $installationCosts = "";
                $deliveryGrade = "";
                $deliveryDetail = "";

                $data['goodsMustInfo'] = json_decode($data['goodsMustInfo'],true);
                foreach($data['goodsMustInfo'] as $mustKey => $mustValue) {
                    if($mustValue['step0']['infoTitle'] =='배송 · 설치비용' && $deliveryDetail == '') {
                        $deliveryGrade = "Y";
                        $deliveryDetail = $mustValue['step0']['infoValue'] ;
                    }

                    if($mustValue['step0']['infoTitle'] =='추가설치비용') {
                        $installationCosts = "Y";
                    }
                }

                $result = '';
                $result .= $data['goodsNo'].chr(9);
                if(empty($this->brandArr[$data['brandCd']]) === true && $data['brandCd']) {
                    $brandQuery = "SELECT cateNm FROM " . DB_CATEGORY_BRAND . " WHERE cateCd = '" . $data['brandCd'] . "'";
                    $brandData = $this->db->query_fetch($brandQuery, null, false);
                    $this->brandArr[$data['brandCd']] = $brandData['cateNm'];
                    unset($brandData,$brandQuery);
                }
                $data['brandNm'] = $this->brandArr[$data['brandCd']];

                if($this->configData['goodsHead']) {
                    $data['goodsNm']=str_replace(array('{_maker}','{_brand}', '{_goodsNo}'),array($data['makerNm'],$data['brandNm'], $data['goodsNo']),$this->configData['goodsHead']).' '.$data['goodsNm'];
                }
                $result .= gd_htmlspecialchars_stripslashes($data['goodsNm']).chr(9);

                $eventDesc = "";
                if ($this->configData['naverEventCommon'] == 'y') $eventDesc = $this->configData['naverEventDescription'];
                if ($this->configData['naverEventGoods'] == 'y' && gd_isset($data['eventDescription'])) $eventDesc .= $data['eventDescription'];


                $result .= gd_money_format($goodsPrice, false).chr(9);
                $result .= gd_money_format($goodsPrice, false).chr(9);
                $result .= gd_money_format($data['fixedPrice']*$minOrderCnt, false).chr(9);
                $result .= 'http://' . $this->mallData['mallDomain'] . '/goods/goods_view.php?goodsNo=' . $data['goodsNo'] . chr(9);
                if ($this->mobileConfig['mobileShopFl'] == 'y') {
                    $result .= 'http://m.' . str_replace('www.', '', $this->mallData['mallDomain']) . '/goods/goods_view.php?goodsNo=' . $data['goodsNo'] . chr(9);
                }

                $result .=  $goodsImageSrc . chr(9); // [필수] 이미지 URL
                $result .=  $goodsAddImageSrc . chr(9); // [필수] 이미지 URL

                for ($i = 0; $i < 4; $i++) {
                    $result .=  gd_isset($cateListNm[$i]) . chr(9); // [필수] 대분류 카테고리 코드
                }
                unset($cateListNm);

                switch ($data['naverImportFlag']) {
                    case 'f':
                        $importFlag  = "Y";
                        $parallelImport   = "";
                        $orderMade  = "";
                        break;
                    case 'd':
                        $importFlag  = "";
                        $parallelImport   = "Y";
                        $orderMade  = "";
                        break;
                    case 'o':
                        $importFlag  = "";
                        $parallelImport   = "";
                        $orderMade  = "Y";
                        break;
                    default:
                        $importFlag  = "";
                        $parallelImport   = "";
                        $orderMade  = "";
                        break;
                }

                if($data['onlyAdultFl'] =='y') $onlyAdultFl = "Y";
                else $onlyAdultFl = "";

                $result .=   gd_isset($data['naverCategory']) .chr(9); // 네이버카테고리
                $result .=   gd_isset($data['naverProductId']) .chr(9); // 가격비교페이지ID
                $result .=   $this->goods->getGoodsStateList()[$data['goodsState']].chr(9); // 상품상태
                $result .=   $importFlag.chr(9); // 해외구매대행여부
                $result .=   $parallelImport.chr(9); // 병행수입여부
                $result .=   $orderMade.chr(9); // 주문제작상품여부
                $result .=   $this->goods->getGoodsSellType()[$data['naverProductFlag']].chr(9); // 판매방식구분
                $result .=   $onlyAdultFl . chr(9); // 미성년자구매불가상품여부

                $result .= gd_isset($data['goodsModelNo']) . chr(9); // [선택] 모델명
                $result .= gd_isset($data['brandNm']) . chr(9); // [선택] 모델명
                $result .= gd_isset($data['makerNm']) . chr(9); // [선택] 모델명
                $result .= gd_isset($data['originNm']) . chr(9); // [선택] 모델명
                $result .= $eventDesc . chr(9); // [선택] 이벤트문구
                if($couponPrice > 0 ) $result .=gd_money_format($couponPrice, false) . '원' . chr(9); // [선택] 쿠폰
                else  $result .= ''. chr(9); // [선택] 쿠폰
                $result .= $this->configData['nv_pcard']. chr(9); // [선택] 무이자
                if($mileage > 0 ) $result .= '쇼핑몰자체포인트^'.$mileage . chr(9); // [선택] 마일리지
                else $result .= '' . chr(9); // [선택] 마일리지
                $result .= gd_isset($installationCosts) .  chr(9); //추가설치비용
                $result .= gd_isset($data['goodsSearchWord']) .  chr(9); //기본정보-검색키워드 항목 사용
                $result .= $minOrderCnt. chr(9); //판매정보-구매수량설정 항목 사용
                $result .= gd_isset($this->getNaverReviewCount($data),0) . chr(9);
                $result .=  gd_isset($deliveryPrice) . chr(9); // [선택] 배송비
                $result .= gd_isset($deliveryGrade) .  chr(9); // 배송 · 설치비용
                $result .= gd_isset($deliveryDetail) .  chr(9); // 배송 · 설치비용 내용
                $result .=  gd_isset($data['naverAttribute']).chr(9); //추가 : 입력기능 추가

                $result .=   $this->goods->getGoodsAgeType()[gd_isset($data['naverAgeGroup'],'a')].chr(9); //(주 사용 연령대)
                $result .=   $this->goods->getGoodsGenderType()[$data['naverGender']]; //(주 사용 성별)
                // 요약EP 생성일 경우
                $now = date('Y-m-d H:i:s');
                if ($this->fileConfig['mode'] == 'summary') {
                    if ($data['soldOut'] =='y' || $data['goodsDisplayFl'] == 'n' || $data['delFl'] == 'y' || $data['applyFl'] != 'y' || ($data['goodsOpenDt'] != '0000-00-00 00:00:00' && $data['goodsOpenDt'] > $now)) $data['class'] = 'D';
                    if ($data['salesStartYmd'] != '0000-00-00 00:00:00' && ($now <= $data['salesStartYmd'] || $now >= $data['salesEndYmd'])) $data['class'] = 'D';
                    $result .= chr(9) . $data['class'];
                    $result .= chr(9) . $data['updateTime'];
                }

                if($this->fileConfig['mode'] == 'all') {
                    $this->totalDburlData++;
                    $fw = fwrite($fh,$result . chr(13) . chr(10));
                    if($fw === false) {
                        fclose($fh);
                        return false;
                    }
                } else {
                    if ($k == 0) {
                        echo implode(chr(9), $paycoHeader). chr(13) . chr(10);
                    }
                    echo $result. chr(13) . chr(10);
                }
                unset($result,$fw);
            }
            if($this->fileConfig['mode'] == 'all') {
                fclose($fh);
                $this->mergedFilePath = $path;
                if(/*$this->totalDburlData >= $this->dburl_max_count || */$lastPageNum == $page) {
                    $this->fileMerge($this->totalDburlPage, $path);
                    exec('cp ' . $path . " " . $path.'_back_up');
                    return true;
                }
            }
            return true;
        }
        unset($getData);
    }

    public function setDaumDbUrl($getData,$page = null,$lastPageNum = null,$totalCount=null)
    {
        if ($this->fileConfig['mode'] == 'review') {
            $this->setDaumDbReviewUrl($getData);
            return;
        }

        if ($this->fileConfig['printFl'] == false) {
            $filename = $this->fileConfig['filename'] ?? $this->fileConfig['site'] . '_' . $this->fileConfig['mode'];
            $tmpFilename = $filename . '_tmp_'.$page;


            $path = UserFilePath::data('dburl', $this->fileConfig['site'], $filename)->getRealPath();
            $tmpPath = UserFilePath::data('dburl', $this->fileConfig['site'], $tmpFilename)->getRealPath();

            if(!is_file($tmpPath)) {
                $this->initFile($tmpPath);
                $this->totalDburlPage++;
            }

            $fh = fopen($tmpPath, 'a+');

            if ($this->fileConfig['mode'] == 'all') {
                fwrite($fh, @iconv('UTF-8', 'EUC-KR//IGNORE', "<<<tocnt>>>" . $totalCount. chr(13) . chr(10)));
            }
        } else {
            if ($this->fileConfig['mode'] == 'all') {
                $saveData[] = "<<<tocnt>>>" . $totalCount;
            }
        }

        if (gd_isset($getData)) {

            foreach ($getData as $k => $data) {
                if($this->fileConfig['printFl'] == false && $this->totalDburlData >= $this->dburl_max_count)  {
                    break;
                }
                if($data['goodsNmPartner']) $data['goodsNm'] = $data['goodsNmPartner'];

                $deliveryPrice = $this->setDeliveryPrice($data);
                if(!is_numeric($deliveryPrice) || empty($data['goodsPriceString']) === false  ) {
                    continue;
                }

                $goodsImageSrc = "";
                if($data['imageName']) {
                    $imageName = explode(STR_DIVISION,$data['imageName']);
                    $imageKind = explode(STR_DIVISION,$data['imageKind']);

                    $tmpImageNo = array_search('magnify',$imageKind);
                    if(empty($tmpImageNo) === true) {
                        $goodsImageSrc = $this->getGoodsImage($imageName[0],$data['imagePath'],$data['imageStorage'],$this->imageSize['size1']);
                    } else {
                        $goodsImageSrc = $this->getGoodsImage($imageName[$tmpImageNo],$data['imagePath'],$data['imageStorage'],$this->imageSize['size1']);
                    }
                    unset($imageName,$imageKind);
                }

                if (empty($goodsImageSrc) === true && $this->fileConfig['mode'] == 'all') {
                    continue;
                }

                $cateListCd = [];
                $cateListNm = [];
                if ($data['cateCd']) {
                    if(empty($this->categoryArr[$data['cateCd']]) === true) {
                        $cateList = $this->cate->getCategoriesPosition($data['cateCd'])[0];
                        $this->categoryArr[$data['cateCd']] = $cateList;
                    }
                    $cateList = $this->categoryArr[$data['cateCd']];

                    if ($cateList) {
                        $cateListCd = array_keys($cateList);
                        $cateListNm = array_values($cateList);
                    }
                }
                $result = '';
                $result .= '<<<begin>>>' . chr(13) . chr(10);
                $result .= '<<<mapid>>>' . $data['goodsNo'] . chr(13) . chr(10); // [필수] 쇼핑몰 상품ID
                if ($this->fileConfig['mode'] == 'all' || ($data['class'] == 'I' || $data['class'] == 'U')) {

                    $addTitle = [];
                    if(empty($this->brandArr[$data['brandCd']]) === true && $data['brandCd']) {
                        $brandQuery = "SELECT cateNm FROM " . DB_CATEGORY_BRAND . " WHERE cateCd = '" . $data['brandCd'] . "'";
                        $brandData = $this->db->query_fetch($brandQuery, null, false);
                        $this->brandArr[$data['brandCd']] = $brandData['cateNm'];
                        unset($brandData,$brandQuery);
                    }
                    $data['brandNm'] = $this->brandArr[$data['brandCd']];

                    if($this->configData['goodshead']) {
                        $data['goodsNm'] = str_replace(array('{_maker}', '{_brand}', '{_goodsNo}'), array($data['makerNm'], $data['brandNm'], $data['goodsNo']), $this->configData['goodshead']) . ' ' . $data['goodsNm'];
                    }
                    $result .= '<<<pname>>>' . gd_htmlspecialchars_stripslashes($data['goodsNm']) . chr(13) . chr(10); // [필수] 상품명
                    unset($addTitle);


                    $result .= '<<<pgurl>>>' . 'http://' . $this->mallData['mallDomain'] . '/goods/goods_view.php?goodsNo=' . $data['goodsNo'] . '&inflow=daum' . chr(13) . chr(10); // [필수] 상품의 상세페이지 주소

                    $result .= '<<<igurl>>>' . $goodsImageSrc . chr(13) . chr(10); // [필수] 이미지 URL

                    if ($data['onlyAdultFl'] == 'y') {
                        $result .= '<<<adult>>>Y' . chr(13) . chr(10); // 성인상품여부
                    }

                    if ($data['eventDescription']) {
                        $result .= '<<<event>>>' . gd_htmlspecialchars_stripslashes($data['eventDescription']) . chr(13) . chr(10);
                    }

                    for ($i = 0; $i < 4; $i++) {
                        $result .= '<<<caid' . ($i + 1) . '>>>' . gd_isset($cateListCd[$i]) . chr(13) . chr(10); // [필수] 대분류 카테고리 코드
                    }

                    for ($i = 0; $i < 4; $i++) {
                        $result .= '<<<cate' . ($i + 1) . '>>>' . gd_isset($cateListNm[$i]) . chr(13) . chr(10); // [필수] 대분류 카테고리 코드
                    }

                    if ($data['reviewCnt'] > 0) {
                        $result .= '<<<revct>>>' . $data['reviewCnt'] . chr(13) . chr(10);
                    }

                    if (gd_isset($data['goodsModelNo'])) $result .= '<<<model>>>' . gd_isset($data['goodsModelNo']) . chr(13) . chr(10); // [선택] 모델명
                    if (gd_isset($data['brandNm'])) $result .= '<<<brand>>>' . gd_isset($data['brandNm']) . chr(13) . chr(10); // [선택] 브랜드
                    if (gd_isset($data['makerNm'])) $result .= '<<<maker>>>' . gd_isset($data['makerNm']) . chr(13) . chr(10); // [선택] 메이커
                    //                    if (gd_isset($data['originNm'])) $result .= '<<<origi>>>' . gd_isset($data['originNm']) . chr(13) . chr(10); // [선택] 원산지

                    //                    $deliveryData = $this->getDeliveryData($data['deliverySno']);

                    $result .= '<<<deliv>>>' . gd_isset($deliveryPrice) . chr(13) . chr(10); // [선택] 무료(0), 착불(-1), 배송비금액표기
                    $couponPrice = gd_isset($this->setGoodsCoupon($data, 'pc', 'daum'), '0');
                    if ($couponPrice > 0) {
                        $result .= '<<<coupo>>>' . gd_money_format($couponPrice, false) . '원' . chr(13) . chr(10); // 크폰
                    }
                    $mcouPon = gd_isset($this->setGoodsCoupon($data, 'mobile', 'daum'), 0);
                    if (gd_isset($mcouPon) && $mcouPon > 0) {
                        $result .= '<<<mcoupon>>>' . gd_money_format($mcouPon, false) . '원' . chr(13) . chr(10); // 모바일쿠폰
                    }

                    if ($data['goodsDiscountFl'] == 'y' || $couponPrice > 0) {    //할인이 있으면

                        $data['goodsDiscountPrice']  = $data['goodsPrice'] - $this->getGoodsDcPrice($data);

                        $data['goodsDiscountPrice'] = $data['goodsDiscountPrice'] - $couponPrice;
                        if ($data['goodsDiscountPrice'] < 0) {
                            $data['goodsDiscountPrice'] = 0;
                        }
                        $lPrice = $data['goodsPrice'];
                        if ($lPrice < 0) {
                            $lPrice = 0;
                        }
                        $result .= '<<<lprice>>>' . gd_money_format($lPrice, false) . chr(13) . chr(10); // 할인 전 가격
                        if (0 > $data['goodsDiscountPrice']) {
                            $data['goodsDiscountPrice'] = 0;
                        }
                        $result .= '<<<price>>>' . gd_money_format($data['goodsDiscountPrice'], false) . chr(13) . chr(10); // [필수] 할인적용가격

                    } else {
                        $result .= '<<<price>>>' . gd_money_format($data['goodsPrice'], false) . chr(13) . chr(10); // [필수] 할인적용가격
                    }

                    $mileageAmount = $this->setGoodsMileage($data, 'daum');
                    if (empty($mileageAmount) === false && $mileageAmount != 0) {
                        $result .= '<<<point>>>' . gd_isset($mileageAmount) . chr(13) . chr(10); // [선택] 마일리지
                    }
                }
                $now = date('Y-m-d H:i:s');
                if ($this->fileConfig['mode'] == 'summary') {

                    if ($data['soldOut'] =='y' || $data['goodsDisplayFl'] == 'n' || $data['delFl'] == 'y' || $data['applyFl'] != 'y' || ($data['goodsOpenDt'] != '0000-00-00 00:00:00' && $data['goodsOpenDt'] > $now)) {
                        $data['class'] = "D";
                    }

                    if ($data['salesStartYmd'] != '0000-00-00 00:00:00' && ($now <= $data['salesStartYmd'] || $now >= $data['salesEndYmd'])) {
                        $data['class'] = "D";
                    }

                    $result .= '<<<class>>>' . $data['class'] . chr(13) . chr(10);

                    if ($data['modDt']) $result .= '<<<utime>>>' . gd_date_format('Y-m-d H:i:s', $data['modDt']) . chr(13) . chr(10); // [선택] 마일리지
                    else $result .= '<<<utime>>>' . gd_date_format('Y-m-d H:i:s', $data['regDt']) . chr(13) . chr(10); // [선택] 마일리지
                }

                $result .= '<<<ftend>>>';
                if ($this->fileConfig['printFl']) {
                    $saveData[] = $result;
                } else {
                    $this->totalDburlData++;
                    fwrite($fh, @iconv('UTF-8', 'EUC-KR//IGNORE', $result . chr(13) . chr(10)));
                }
            }

            if ($this->fileConfig['printFl']) {
                $inputData = implode(chr(13) . chr(10), $saveData);
                echo @iconv('UTF-8', 'EUC-KR//IGNORE', $inputData);
            } else {
                $this->mergedFilePath = $path;
                if($this->totalDburlData >= $this->dburl_max_count || $lastPageNum == $page) {
                    $this->fileMerge($this->totalDburlPage, $path);
                }
            }

        }

    }


    public function setDaumDbReviewUrl($getData)
    {
        $saveData[] = "<<<tocnt>>>" . count($getData);
        if (gd_isset($getData) && is_array($getData)) {
            foreach ($getData as $k => $data) {
                $result = '';
                $result .= '<<<begin>>>' . PHP_EOL;
                $result .= '<<<mapid>>>' . $data['goodsNo'] . PHP_EOL; // [필수] 쇼핑몰 상품ID
                $result .= '<<<reviewid>>>' . date_format(date_create($data['regDt']), 'YmdHis') . '_' . $data['sno'] . '_' . $data['memNo'] . '_' . $data['goodsNo'] . PHP_EOL; // [필수] 쇼핑몰 상품ID
                $status = $this->fileConfig['isBegin'] ? 'D' : 'S';// 첫 수집시에는 삭제된 상품평을 수집할 필요가 없음
                if ($data['isDelete'] == 'y') {
                    $status = 'D';
                }
                $result .= '<<<status>>>' . $status . PHP_EOL;
                $result .= '<<<title>>>' . gd_htmlspecialchars_stripslashes(strip_tags($data['subject'])) . PHP_EOL;
                $result .= '<<<content>>>' . gd_htmlspecialchars_stripslashes(strip_tags($data['contents'])) . PHP_EOL;
                $writer = $data['writerNm'];
                if (mb_strlen($writer, 'utf-8') > 2) {
                    $writer = mb_substr($writer, 0, mb_strlen($writer, 'utf-8') - 2) . '**';
                }
                $result .= '<<<writer>>>' . $writer . PHP_EOL;
                $result .= '<<<cdate>>>' . date_format(date_create($data['regDt']), 'YmdHis') . PHP_EOL;
                $result .= '<<<rating>>>' . $data['goodsPt'] . '/5' . PHP_EOL;
                $result .= '<<<ftend>>>';
                $saveData[] = $result;
            }

            if ($this->fileConfig['printFl']) {
                $inputData = implode(chr(13) . chr(10), $saveData);
                echo @iconv('UTF-8', 'EUC-KR//IGNORE', $inputData);
            } else {
                $this->genarateFile($saveData);
            }
        }

    }

    public function genarateFile($saveData)
    {
        $inputData = implode(chr(13) . chr(10), $saveData);

        $filename = $this->fileConfig['filename'] ?? $this->fileConfig['site'] . '_' . $this->fileConfig['mode'];
        $tmpFilename = $filename . '_tmp';


        $path = UserFilePath::data('dburl', $this->fileConfig['site'], $filename)->getRealPath();
        $tmpPath = UserFilePath::data('dburl', $this->fileConfig['site'], $tmpFilename)->getRealPath();

        $this->initFile($tmpPath);

        $tmpRes = StringUtils::strCutToArray($inputData, 1024);
        for ($i = 0; $i < count($tmpRes); $i++) {
            file_put_contents($tmpPath, @iconv('UTF-8', 'EUC-KR//IGNORE', $tmpRes[$i]), FILE_APPEND | LOCK_EX);
        }

        @chmod($path, 0707);
        @rename($tmpPath, $path);

        return true;
    }

    /**
     * 상품 정보 파일에 준비.
     *
     * @param $path 경로
     * @throws \Exception
     */
    protected function initFile($path)
    {
        $paths = explode('/', $path);
        array_splice($paths, count($paths) - 1, 1);
        $tmpPath = implode('/', $paths);
        if (is_dir($tmpPath) === false) {
            $dir_path = '';
            for ($i = 0; $i < count($paths); $i++) {
                $dir_path .= $paths[$i];
                if (is_dir($dir_path) === false) {
                    @mkdir($dir_path);
                    @chmod($dir_path, 0707);
                }
                $dir_path .= '/';
            }
            unset($dir_path);

            //            if (is_dir($tmpPath) === false) {
            //                throw new \Exception($tmpPath.' 폴더가 존재하지 않습니다.');
            //            }
        }
        unset($paths);

        /*
        $result = '<?php header("Cache-Control: no-cache, must-revalidate"); header("Content-Type: text/plain; charset=euc-kr"); ?>';
        */
        $result = "";
        file_put_contents($path, $result);
        @chmod($path, 0707);
    }


    /**
     * 네이버 업데이트 정보 삭제
     *
     * @internal param 경로 $path
     * @param $site
     */
    public function deleteEpUpdate($site)
    {
        //if (!Session::has('manager.managerId')) {
        if (Request::isCli()) {
            $this->db->set_update_db(DB_GOODS_UPDATET_NAVER, $site . "CheckFl = 'y'", '1');
            $this->db->set_delete_db(DB_GOODS_UPDATET_NAVER, "mapid!='' AND naverCheckFl = 'y' AND daumCheckFl = 'y' AND paycoCheckFl = 'y' ");
        }
    }

    /**
     * 타게팅 게이츠 ep 생성
     *
     * @param $getData 상품정보
     */
    public function setTgDbUrl($getData,$page,$lastPageNum)
    {
        if (gd_isset($getData)) {

            $filename = $this->fileConfig['filename'] ?? $this->fileConfig['site'] . '_' . $this->fileConfig['mode'];
            $path = UserFilePath::data('dburl', $this->fileConfig['site'], $filename)->getRealPath();

            $tmpFilename = $filename . '_tmp_' . $page;
            $tmpPath = UserFilePath::data('dburl', $this->fileConfig['site'], $tmpFilename)->getRealPath();
            $this->initFile($tmpPath);
            $this->totalDburlPage++;
            $fh = fopen($tmpPath, 'a+');

            foreach ($getData as $k => $data) {
                if($this->totalDburlData >= $this->dburl_max_count)  {
                    break;
                }
                if (empty($data['goodsPriceString']) === false || empty($data['imageName']) === true) {
                    continue;
                }

                $goodsImageSrc = "";
                if($data['imageName']) {
                    $imageName = explode(STR_DIVISION,$data['imageName']);
                    $imageKind = explode(STR_DIVISION,$data['imageKind']);

                    $tmpImageNo = array_search('magnify',$imageKind);
                    if(empty($tmpImageNo) === true) {
                        $goodsImageSrc = $this->getGoodsImage($imageName[0],$data['imagePath'],$data['imageStorage'],$this->imageSize['size1']);
                    } else {
                        $goodsImageSrc = $this->getGoodsImage($imageName[$tmpImageNo],$data['imagePath'],$data['imageStorage'],$this->imageSize['size1']);
                    }
                    unset($imageName,$imageKind);
                }

                $cateListCd = '';
                $cateListNm = '';
                if ($data['cateCd']) {
                    if(empty($this->categoryArr[$data['cateCd']]) === true) {
                        $cateList = $this->cate->getCategoriesPosition($data['cateCd'])[0];
                        $this->categoryArr[$data['cateCd']] = $cateList;
                    }
                    $cateList = $this->categoryArr[$data['cateCd']];

                    if ($cateList) {
                        $cateListCd = array_keys($cateList);
                        $cateListNm = array_values($cateList);
                    }
                }

                $result = '';
                $result .= '<<<begin>>>' . chr(13) . chr(10);
                $result .= '<<<mapid>>>' . $data['goodsNo'] . chr(13) . chr(10); // [필수] 상품ID
                $result .= '<<<pname>>>' . gd_htmlspecialchars_stripslashes($data['goodsNm']) . chr(13) . chr(10); // [필수] 상품명
                $result .= '<<<price>>>' . gd_money_format($goods['goodsPrice'], false) . chr(13) . chr(10); // [필수] 상품가격
                $result .= '<<<pgurl>>>' . 'http://' . $this->mallData['mallDomain'] . '/goods/goods_view.php?goodsNo=' . $data['goodsNo'] . chr(13) . chr(10); // [필수] PC 상품URL
                $result .= '<<<mourl>>>' . 'http://m.' . $this->mallData['mallDomain'] . '/goods/goods_view.php?goodsNo=' . $data['goodsNo'] . chr(13) . chr(10); // [필수] MOBILE 상품URL
                if (Request::isCli() && $data['imageStorage'] == 'local' && (strpos(strtolower($goodsImageSrc),'http://') === false && strpos(strtolower($goodsImageSrc),'https://') === false )) {
                    $result .= '<<<igurl>>>' . 'http://' . $this->mallData['mallDomain'] . $goodsImageSrc . chr(13) . chr(10);
                } else {
                    $result .= '<<<igurl>>>' . $goodsImageSrc . chr(13) . chr(10);
                }
                $result .= '<<<cate1>>>' . gd_isset(reset($cateListNm)) . chr(13) . chr(10); // [필수] 업체 카테고리명 (대분류)
                $result .= '<<<caid1>>>' . gd_isset(reset($cateListCd)) . chr(13) . chr(10); // [필수] 업체 카테고리코드 (대분류)
                $result .= '<<<ftend>>>';

                $saveData[] = $result;
                $this->totalDburlData++;
                fwrite($fh, @iconv('UTF-8', 'EUC-KR//IGNORE', $result . chr(13) . chr(10)));
            }

            fclose($fh);
            $this->mergedFilePath = $path;
            if($this->totalDburlData >= $this->dburl_max_count || $lastPageNum == $page) {
                $this->fileMerge($this->totalDburlPage, $path);
                return true;
            }
        }
    }

    /**
     * 분할 파일 병합
     *
     * @param $tmpFileCnt 분할 파일 갯수
     * @param $path       병합 파일 경로
     */
    private function fileMerge($tmpFileCnt, $path)
    {
        //초기화
        exec('cat /dev/null > ' . $path);

        for ($num = 0; $num < $tmpFileCnt; $num++) {
            $tmpFileName = $path . '_tmp_' . $num;
            //머지 후 삭제
            if (is_file($tmpFileName) === true) {
                exec('cat ' . $tmpFileName . ' >> ' . $path);
                unlink($tmpFileName);
            }
        }
        $this->isMerge = true;
    }

    private function getNaverReviewCount($data){
        if($this->configData['naverReviewChannel'] == 'plusReview'){
            $reviewCnt = $data['plusReviewCnt'];
        }
        else if($this->configData['naverReviewChannel'] == 'both'){
            $reviewCnt = $data['plusReviewCnt'] + $data['reviewCnt'];
        }
        else {
            $reviewCnt = $data['reviewCnt'];
        }

        // 크리마리뷰 카운팅 사용
        $crema = \App::load('Component\\Service\\Crema');
        if ($data['reviewMode'] !== 'payco' && $crema->getUseEpFl()) {
            $reviewCnt = $data['cremaReviewCnt'];
        }

        return $reviewCnt;
    }
}
