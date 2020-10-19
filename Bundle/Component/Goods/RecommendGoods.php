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

use Component\Database\DBTableField;
use Framework\Utility\SkinUtils;
use Globals;
use LogHandler;
use Request;

/**
 * 추가 상품 관련 클래스
 * @author
 */
class RecommendGoods
{
    // 디비 접속
    protected $db;

    // 최대 등록 상품 갯수
    const DEFAULT_RECOMMEND_GOODS_CNT = 50;

    /**
     * 생성자
     *
     */
    public function __construct()
    {
        if (!is_object($this->db)) {
            $this->db = \App::load('DB');
        }
        if (gd_is_plus_shop(PLUSSHOP_CODE_RECOMMEND) === true) {
            return;
        }
    }

    public function getGoodsDataUser($display = 'front')
    {
        if(\Session::has(SESSION_GLOBAL_MALL)) {
            return;
        }

        $goods = \App::load('\\Component\\Goods\\Goods');
        $config = gd_policy('goods.recom');
        $mileage = gd_policy('member.mileageGive');
        $mileageBasic = gd_policy('member.mileageBasic');
        $trunc = Globals::get('gTrunc');
        $goodsPriceDisplayFl = gd_policy('goods.display')['priceFl']; //상품 가격 노출 관련
        //품절상품 설정
        if(Request::isMobile()) {
            $soldoutDisplay = gd_policy('soldout.mobile');
        } else {
            $soldoutDisplay = gd_policy('soldout.pc');
        }

        if (($display == 'front' && $config['pcDisplayFl'] == 'n') || ($display == 'mobile' && $config['mobileDisplayFl'] == 'n')) {
            return;
        }

        $arrField = DBTableField::setTableField('tableGoods', ['goodsNo', 'imagePath', 'imageStorage', 'goodsPrice', 'goodsPriceString', 'totalStock', 'stockFl', 'soldOutFl', 'makerNm', 'goodsNm', 'shortDescription', 'fixedPrice', 'goodsModelNo', 'mileageFl', 'mileageGoodsUnit', 'mileageGoods','goodsPermissionPriceStringFl','goodsPermission','goodsPermissionGroup','goodsPermissionPriceString','onlyAdultFl','onlyAdultImageFl','goodsColor', 'goodsDiscountFl', 'goodsDiscountGroup', 'goodsDiscountGroupMemberInfo', 'goodsDiscountUnit', 'goodsDiscount', 'exceptBenefit', 'exceptBenefitGroupInfo', 'exceptBenefitGroup', 'goodsBenefitSetFl'], null, 'g');

        $arrField[] = "( if (g.soldOutFl = 'y' , 'y', if (g.stockFl = 'y' AND g.totalStock <= 0, 'y', 'n') ) ) as soldOut";

        $arrJoin[] = ' INNER JOIN ' . DB_RECOMMEND_GOODS . ' as rg ON rg.goodsNo = g.goodsNo ';
        $arrJoin[] = ' LEFT JOIN ' . DB_CATEGORY_BRAND . ' cb ON g.brandCd = cb.cateCd';
        $arrJoin[] = ' LEFT JOIN ' . DB_GOODS_IMAGE . ' gi ON g.goodsNo = gi.goodsNo AND gi.imageKind = ? ';

        $arrBind = [];
        $this->db->bind_param_push($arrBind, 's', $config['imageCd']);
        if (gd_is_plus_shop(PLUSSHOP_CODE_TIMESALE) === true) {
            if (\Request::isMobile()) {
                $arrJoin[] = ' LEFT JOIN ' . DB_TIME_SALE . ' ts ON FIND_IN_SET(g.goodsNo, REPLACE(ts.goodsNo,"'.INT_DIVISION.'",",")) AND UNIX_TIMESTAMP(ts.startDt) < UNIX_TIMESTAMP() AND  UNIX_TIMESTAMP(ts.endDt) > UNIX_TIMESTAMP() AND ts.mobileDisplayFl=? ';
            } else {
                $arrJoin[] = ' LEFT JOIN ' . DB_TIME_SALE . ' ts ON FIND_IN_SET(g.goodsNo, REPLACE(ts.goodsNo,"'.INT_DIVISION.'",",")) AND UNIX_TIMESTAMP(ts.startDt) < UNIX_TIMESTAMP() AND  UNIX_TIMESTAMP(ts.endDt) > UNIX_TIMESTAMP() AND ts.pcDisplayFl=? ';
            }
            $this->db->bind_param_push($arrBind, 's', 'y');
            $arrField[] = 'ts.mileageFl as timeSaleMileageFl,ts.couponFl as timeSaleCouponFl,ts.benefit as timeSaleBenefit,ts.sno as timeSaleSno,ts.goodsPriceViewFl as timeSaleGoodsPriceViewFl';
        }

        if ($display == 'front') {
            $arrWhere[] = 'g.goodsDisplayFl = ?';
            $this->db->bind_param_push($arrBind, 's', 'y');
        } else {
            $arrWhere[] = 'g.goodsDisplayMobileFl = ?';
            $this->db->bind_param_push($arrBind, 's', 'y');
        }
        if ($config['soldOutFl'] == 'n') {
            $arrWhere[] = 'g.soldOutFl != ?';
            $this->db->bind_param_push($arrBind, 's', 'y');
            $arrWhere[] = '(g.stockFl != ? OR (g.stockFl = ? AND g.totalStock > ?))';
            $this->db->bind_param_push($arrBind, 's', 'y');
            $this->db->bind_param_push($arrBind, 's', 'y');
            $this->db->bind_param_push($arrBind, 'i', 0);
        }
        $arrWhere[] = 'g.delFl = ?';
        $this->db->bind_param_push($arrBind, 's', 'n');


        //접근권한 체크
        if (gd_check_login()) {
            $arrWhere[] = '(g.goodsAccess !=\'group\'  OR (g.goodsAccess=\'group\' AND FIND_IN_SET(\''.\Session::get('member.groupSno').'\', REPLACE(g.goodsAccessGroup,"'.INT_DIVISION.'",","))) OR (g.goodsAccess=\'group\' AND !FIND_IN_SET(\''.\Session::get('member.groupSno').'\', REPLACE(g.goodsAccessGroup,"'.INT_DIVISION.'",",")) AND g.goodsAccessDisplayFl =\'y\'))';
        } else {
            $arrWhere[] = '(g.goodsAccess=\'all\' OR (g.goodsAccess !=\'all\' AND g.goodsAccessDisplayFl =\'y\'))';
        }

        //성인인증안된경우 노출체크 상품은 노출함
        if (gd_check_adult() === false) {
            $arrWhere[] = '(onlyAdultFl = \'n\' OR (onlyAdultFl = \'y\' AND onlyAdultDisplayFl = \'y\'))';
        }

        $this->db->strField = implode(', ', $arrField) . ', gi.imageName, gi.imageSize, cb.cateNm';
        $this->db->strJoin = implode('', $arrJoin);
        $this->db->strWhere = implode(' AND ', gd_isset($arrWhere));
        $this->db->strOrder = 'RAND()';
        $this->db->strLimit = 1;

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_GOODS . ' g ' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $arrBind, false);

        if($data) {
            $GoodsBenefit = \App::load('\\Component\\Goods\\GoodsBenefit');
            $data = $GoodsBenefit->goodsDataFrontConvert($data);

            //기본적으로 가격 노출함
            $data['goodsPriceDisplayFl'] = 'y';

            // 상품 이미지
            if ($data['onlyAdultFl'] == 'y' && gd_check_adult() === false && $data['onlyAdultImageFl'] =='n') {
                if (Request::isMobile()) {
                    $data['img'] = "/data/icon/goods_icon/only_adult_mobile.png";
                } else {
                    $data['img'] = "/data/icon/goods_icon/only_adult_pc.png";
                }
            } else {
                $data['img'] = SkinUtils::imageViewStorageConfig($data['imageName'], $data['imagePath'], $data['imageStorage'], 150, 'goods')[0];
            }

            // 마일리지
            if (in_array('mileage', $config['displayField']) === true) {
                if ($mileage['giveFl'] == 'y') {

                    //상품 마일리지
                    if ($data['mileageFl'] == 'c') {
                        $mileagePercent = $mileage['goods'] / 100;
                        // 상품 기본 마일리지 정보
                        $data['mileageBasicGoods'] = gd_number_figure($data['goodsPrice'] * $mileagePercent, $trunc['mileage']['unitPrecision'], $trunc['mileage']['unitRound']);

                    // 개별 설정인 경우 마일리지 설정
                    } else if ($data['mileageFl'] == 'g') {
                        $mileagePercent = $data['mileageGoods'] / 100;

                        // 상품 기본 마일리지 정보
                        if ($data['mileageGoodsUnit'] === 'percent') {
                            $data['mileageBasicGoods'] = gd_number_figure($data['goodsPrice'] * $mileagePercent, $trunc['mileage']['unitPrecision'], $trunc['mileage']['unitRound']);
                        } else {
                            // 정액인 경우 해당 설정된 금액으로
                            $data['mileageBasicGoods'] = gd_number_figure($data['mileageGoods'], $trunc['mileage']['unitPrecision'], $trunc['mileage']['unitRound']);
                        }

                    }

                    $member = \App::Load(\Component\Member\Member::class);
                    $memInfo = $member->getMemberInfo();

                    // 회원 그룹별 추가 마일리지
                    if ($memInfo['mileageLine'] <= $data['goodsPrice']) {
                        if ($memInfo['mileageType'] === 'percent') {
                            $memberMileagePercent = $memInfo['mileagePercent'] / 100;
                            $data['mileageBasicMember'] = gd_number_figure($data['goodsPrice'] * $memberMileagePercent, $trunc['mileage']['unitPrecision'], $trunc['mileage']['unitRound']);
                        } else {
                            $data['mileageBasicMember'] = $memInfo['mileagePrice'];
                        }
                    }
                    $data['mileage'] = ($data['mileageBasicGoods'] + $data['mileageBasicMember']) . $mileageBasic['unit'];
                }
            }

            //구매불가 대체 문구 관련
            if($data['goodsPermissionPriceStringFl'] =='y' && $data['goodsPermission'] !='all' && (($data['goodsPermission'] =='member'  && gd_is_login() === false) || ($data['goodsPermission'] =='group'  && !in_array(\Session::get('member.groupSno'),explode(INT_DIVISION,$data['goodsPermissionGroup']))))) {
                $data['goodsPriceString'] = $data['goodsPermissionPriceString'];
            }

            // 상품금액
            $data['timeSaleFl'] = false;
            if (gd_is_plus_shop(PLUSSHOP_CODE_TIMESALE) === true) {
                $strScmSQL = 'SELECT ts.mileageFl as timeSaleMileageFl,ts.couponFl as timeSaleCouponFl,ts.benefit as timeSaleBenefit,ts.sno as timeSaleSno,ts.goodsPriceViewFl as timeSaleGoodsPriceViewFl, ts.endDt as timeSaleEndDt, ts.leftTimeDisplayType, ts.pcDisplayFl as timeSalePC, ts.mobileDisplayFl as timeSaleMobile FROM ' . DB_TIME_SALE .' as ts WHERE FIND_IN_SET('.$data['goodsNo'].', REPLACE(ts.goodsNo,"'.INT_DIVISION.'",",")) AND UNIX_TIMESTAMP(ts.startDt) < UNIX_TIMESTAMP() AND  UNIX_TIMESTAMP(ts.endDt) > UNIX_TIMESTAMP() ';
                $tmpScmData = $this->db->query_fetch($strScmSQL,null,false);

                if($tmpScmData) {
                    //타임세일 노출 여부 (디바이스에 따라)
                    if ($tmpScmData['timeSalePC'] === 'y' && !Request::isMobile()) {
                        $data['timeSaleFl'] = true;
                    }
                    if ($tmpScmData['timeSaleMobile'] === 'y' && Request::isMobile()) {
                        $data['timeSaleFl'] = true;
                    }
                }

                if ($data['timeSaleMileageFl'] == 'n') unset($data['mileage']);
                if ($data['timeSaleCouponFl'] == 'n') unset($data['couponPrice']);
                if ($data['goodsPrice'] && $data['timeSaleBenefit'] > 0) $data['goodsPrice'] = gd_number_figure($data['goodsPrice'] - (($data['timeSaleBenefit'] / 100) * $data['goodsPrice']), $trunc['goods']['unitPrecision'], $trunc['goods']['unitRound']);
            }

            // 쿠폰 정보
            $couponConfig = gd_policy('coupon.config');
            if ($data['timeSaleCouponFl'] == 'n') $couponConfig['couponUseType'] = 'n';

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
            $exceptBenefit = explode(STR_DIVISION, $data['exceptBenefit']);
            $exceptBenefitGroupInfo = explode(INT_DIVISION, $data['exceptBenefitGroupInfo']);
            if (in_array('coupon', $exceptBenefit) === true && ($data['exceptBenefitGroup'] == 'all' || ($data['exceptBenefitGroup'] == 'group') && in_array(\Session::get('member.memNo'), $exceptBenefitGroupInfo) === true)) {
                $couponPriceYN = false;
            }

            // 쿠폰 할인 금액
            if ($couponConfig['couponUseType'] == 'y' && $couponPriceYN  && $data['goodsPrice'] > 0 && empty($data['goodsPriceString']) === true) {
                // 쿠폰 모듈 설정

                $coupon = \App::load('\\Component\\Coupon\\Coupon');
                // 해당 상품의 모든 쿠폰
                $couponArrData = $coupon->getGoodsCouponDownList($data['goodsNo']);

                // 해당 상품의 쿠폰가
                $data['couponDcPrice'] = $couponSalePrice = $coupon->getGoodsCouponDisplaySalePrice($couponArrData, $data['goodsPrice']);
                if ($couponSalePrice) {
                    $data['couponPrice'] = $data['goodsPrice'] - $couponSalePrice;
                    if ($data['couponPrice'] < 0) {
                        $data['couponPrice'] = 0;
                    }
                }
            }

            // 구매 가능여부 체크
            if ($data['soldOut'] == 'y' && $goodsPriceDisplayFl =='n' && $soldoutDisplay['soldout_price'] !='price') {
                if($soldoutDisplay['soldout_price'] =='text')   $data['goodsPriceString'] = $soldoutDisplay['soldout_price_text'];
                $data['goodsPriceDisplayFl'] = 'n';
            }

            if (empty($data['goodsPriceString']) === false && $goodsPriceDisplayFl =='n') {
                $data['goodsPriceDisplayFl'] = 'n';
            }

            // 상품 대표색상 치환코드 추가
            if ($data['goodsColor']) {
                $goodsColorList = $goods->getGoodsColorList(true);
                $goodsColor = (Request::isMobile()) ? "<div class='color_chip'>" : "<div class='color'>";
                if ($data['goodsColor']) $data['goodsColor'] = explode(STR_DIVISION, $data['goodsColor']);

                if (is_array($data['goodsColor'])) {
                    foreach(array_unique($data['goodsColor']) as $k => $v) {
                        if (!in_array($v,$goodsColorList) ) {
                            continue;
                        }
                        $goodsColorData = array_flip($goodsColorList)[$v];
                        $goodsColor .= ($v == 'FFFFFF') ? "<div style='background-color:#{$v} !important;' title='{$goodsColorData}'></div>" : "<div style='background-color:#{$v} !important; border-color:#{$v} !important;' title='{$goodsColorData}'></div>";
                    }
                    $goodsColor .= "</div>";
                    unset($data['goodsColor']);
                    $data['goodsColor'] = $goodsColor;
                }
            }

            //할인가 기본 세팅
            $data['goodsDcPrice'] = $goods->getGoodsDcPrice($data);

            if (in_array('goodsDiscount', $config['displayField']) === true) {
                if (empty($config['goodsDiscount']) === false) {
                    if (in_array('goods', $config['goodsDiscount']) === true) $data['dcPrice'] += $data['goodsDcPrice'];
                    if (in_array('coupon', $config['goodsDiscount']) === true) $data['dcPrice'] += $data['couponDcPrice'];
                }
            }

            if ($data['dcPrice'] >= $data['goodsPrice']) {
                $data['dcPrice'] = 0;
            }

            if (in_array('dcRate', $config['displayAddField']) === true) {
                $data['goodsDcRate'] = round((100 * gd_isset($data['dcPrice'], 0)) / $data['goodsPrice']);
                $data['couponDcRate'] = round((100 * $data['couponDcPrice']) / $data['goodsPrice']);
            }
        }

        $setData['data'] = $data;
        $setData['config'] = $config;

        return $setData;
    }

    public function getGoodsData()
    {
        $join[] = ' INNER JOIN ' . DB_RECOMMEND_GOODS . ' as rg ON rg.goodsNo = g.goodsNo ';
        $join[] = ' LEFT JOIN ' . DB_SCM_MANAGE . ' as s ON s.scmNo = g.scmNo ';
        $join[] = ' LEFT JOIN ' . DB_GOODS_IMAGE . ' gi ON g.goodsNo = gi.goodsNo AND gi.imageKind = ? ';
        $this->db->bind_param_push($this->arrBind, 's', 'list');

        if (gd_is_plus_shop(PLUSSHOP_CODE_TIMESALE) === true) {
            $join[] = 'LEFT JOIN ' . DB_TIME_SALE . ' ts ON FIND_IN_SET(g.goodsNo, REPLACE(ts.goodsNo,"'.INT_DIVISION.'",","))  AND ts.endDt > "'.date('Y-m-d H:i:s').'"';
            $addField = ', ts.sno as timeSaleSno';
        }

        $this->db->strField = "g.goodsNo,g.applyFl,g.goodsNm, g.imageStorage, g.imagePath,g.goodsPrice,g.goodsDisplayFl,g.goodsDisplayMobileFl,g.goodsSellFl,g.goodsSellMobileFl,g.totalStock,g.stockFl,g.soldOutFl,g.regDt,g.modDt,g.applyType,g.applyMsg,g.applyDt,s.companyNm as scmNm, gi.imageName".$addField;
        $this->db->strJoin = implode('', $join);
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
        $this->db->strOrder = "rg.sno";

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_GOODS . ' g ' . implode(' ', $query);

        $data = $this->db->query_fetch($strSQL, $this->arrBind);

        return $data;
    }

    public function save($param)
    {
        if (empty($param) === true || is_array($param) === false) {
            // 추천상품 정보 전체 삭제
            $this->db->query('DELETE FROM ' . DB_RECOMMEND_GOODS);
            return true;
        }

        // 기존 추천상품 정보 삭제
        $this->db->query('DELETE FROM ' . DB_RECOMMEND_GOODS);

        foreach ($param as $goodsNo) {
            $arrData['goodsNo'] = $goodsNo;
            $arrBind = $this->db->get_binding(DBTableField::tableRecommendGoods(), $arrData, 'insert');
            $this->db->set_insert_db(DB_RECOMMEND_GOODS, $arrBind['param'], $arrBind['bind'], 'y');
        }

        return true;
    }

    public function del($param)
    {
        if (empty($param) === true || is_array($param) === false) {
            return false;
        }

        foreach ($param as $goodsNo) {
            $query = "DELETE FROM " . DB_RECOMMEND_GOODS . " WHERE goodsNo = ?";
            $this->db->bind_query($query, ['i', $goodsNo]);
            $retData[] = $goodsNo;
        }

        return $retData;
    }
}
