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


use Framework\Utility\StringUtils;

/**
 * Class DaumDbUrl
 * @package Bundle\Component\Worker
 * @author  yjwee <yeongjong.wee@godo.co.kr>
 */
class DaumDbUrl extends \Component\Worker\AbstractDbUrl
{
    protected $totalCount = 0;

    /**
     * pc 에서 사용가능한 ep coupon 조회
     *
     * 네이버/다음 동일 적용 , 모든 사람이 사용 가능한 쿠폰기준
     * 조건가격 없음 AND 수량 무제한 AND 회원등급 제한 없음
     */
    protected function loadCoupon()
    {
        $where[] = "couponMinOrderPrice = 0 ";
        $where[] = "couponAmountType = 'n' ";
        $where[] = "(couponApplyMemberGroup = '' or  couponApplyMemberGroup is null) ";
        $this->couponForEpPcList = $this->componentCoupon->getGoodsCouponDownListAll($where, "pc");
        $this->couponForEpMobileList = $this->componentCoupon->getGoodsCouponDownListAll($where, 'mobile');
        unset($where);
    }


    protected function countGoods(array $params = []): int
    {
        $this->goodsWheres = [];
        $this->goodsWheres[] = 'g.goodsDisplayFl = \'y\'';
        $this->goodsWheres[] = 'g.delFl = \'n\'';
        $this->goodsWheres[] = 'g.applyFl = \'y\'';
        $this->goodsWheres[] = 'NOT(g.stockFl = \'y\' AND g.totalStock = 0)';
        $this->goodsWheres[] = 'g.soldOutFl = \'n\'';
        $this->goodsWheres[] = '(UNIX_TIMESTAMP(g.goodsOpenDt) IS NULL  OR UNIX_TIMESTAMP(g.goodsOpenDt) = 0 OR UNIX_TIMESTAMP(g.goodsOpenDt) < UNIX_TIMESTAMP())';
        $this->goodsWheres[] = " g.daumFl = 'y'";
        $wheres = $this->goodsWheres;
        if (key_exists('where', $params) && count($params['where']) > 0) {
            $wheres = array_merge($wheres, $params['where']);
        }
        $strSQL = ' SELECT COUNT(*) AS cnt FROM ' . DB_GOODS . ' as g  WHERE ' . implode(' AND ', $wheres);

        $db = \App::getInstance('DB');
        $resultSet = $db->query_fetch($strSQL, null, false);
        $this->totalCount = gd_isset($resultSet['cnt'], 0);

        return $this->totalCount;
    }

    protected function loadConfig()
    {
        $componentDbUrl = \App::load('Component\\Marketing\\DBUrl');
        $this->config = $componentDbUrl->getConfig('daumcpc', 'config');
    }

    protected function makeDbUrl(\Generator $goodsGenerator, int $pageNumber): bool
    {
        $this->totalDbUrlPage++;

        if ($pageNumber == 0) {
            $this->writeDbUrl("<<<tocnt>>>" . $this->totalCount);
        }

        $goodsGenerator->rewind();
        while ($goodsGenerator->valid()) {
            if ($this->greaterThanMaxCount()) {
                break;
            }
            $data = $goodsGenerator->current();
            $goodsGenerator->next();
            if ($data['goodsNmPartner']) {
                $data['goodsNm'] = $data['goodsNmPartner'];
            }
            if (empty($data['goodsPriceString']) === false) {
                continue;
            }
            $deliveryPrice = $this->setDeliveryPrice($data);
            if (!is_numeric($deliveryPrice)) {
                continue;
            }
            $goodsImageSrc = $this->getGoodsImageSrc($data);
            if (empty($goodsImageSrc)) {
                continue;
            }

            $cateListCd = [];
            $cateListNm = [];
            if ($data['cateCd']) {
                if (empty($this->categoryStorage[$data['cateCd']]) === true) {
                    $cateList = $this->componentCategory->getCategoriesPosition($data['cateCd'])[0];
                    $this->categoryStorage[$data['cateCd']] = $cateList;
                }
                $cateList = $this->categoryStorage[$data['cateCd']];

                if ($cateList) {
                    $cateListCd = array_keys($cateList);
                    $cateListNm = array_values($cateList);
                }
            }

            $this->writeDbUrl('<<<begin>>>');
            $this->writeDbUrl('<<<mapid>>>' . $data['goodsNo']); // [필수] 쇼핑몰 상품ID

            $this->selectCategoryBrand($data);
            $data['brandNm'] = $this->brandStorage[$data['brandCd']];

            if ($this->config['goodshead']) {
                $data['goodsNm'] = str_replace(
                        [
                            '{_maker}',
                            '{_brand}',
                            '{_goodsNo}',
                        ], [
                        $data['makerNm'],
                        $data['brandNm'],
                        $data['goodsNo'],
                    ], $this->config['goodshead']
                    ) . ' ' . $data['goodsNm'];
            }
            $this->writeDbUrl('<<<pname>>>' . gd_htmlspecialchars_stripslashes($data['goodsNm'])); // [필수] 상품명
            $this->writeDbUrl('<<<pgurl>>>' . 'http://' . $this->policy['basic']['info']['mallDomain'] . '/goods/goods_view.php?goodsNo=' . $data['goodsNo'] . '&inflow=daum'); // [필수] 상품의 상세페이지 주소
            $this->writeDbUrl('<<<igurl>>>' . $goodsImageSrc); // [필수] 이미지 URL
            if ($data['onlyAdultFl'] == 'y') {
                $this->writeDbUrl('<<<adult>>>Y'); // 성인상품여부
            }

            if ($data['eventDescription']) {
                $this->writeDbUrl('<<<event>>>' . gd_htmlspecialchars_stripslashes($data['eventDescription']));
            }

            for ($i = 0; $i < 4; $i++) {
                $this->writeDbUrl('<<<caid' . ($i + 1) . '>>>' . gd_isset($cateListCd[$i])); // [필수] 대분류 카테고리 코드
            }

            for ($i = 0; $i < 4; $i++) {
                $this->writeDbUrl('<<<cate' . ($i + 1) . '>>>' . gd_isset($cateListNm[$i])); // [필수] 대분류 카테고리 코드
            }

            if ($data['reviewCnt'] > 0) {
                $this->writeDbUrl('<<<revct>>>' . $data['reviewCnt']);
            }
            if (gd_isset($data['goodsModelNo'])) {
                $this->writeDbUrl('<<<model>>>' . gd_isset($data['goodsModelNo']));
            } // [선택] 모델명
            if (gd_isset($data['brandNm'])) {
                $this->writeDbUrl('<<<brand>>>' . gd_isset($data['brandNm']));
            } // [선택] 브랜드
            if (gd_isset($data['makerNm'])) {
                $this->writeDbUrl('<<<maker>>>' . gd_isset($data['makerNm']));
            } // [선택] 메이커
            // if (gd_isset($data['originNm'])) $result .= '<<<origi>>>' . gd_isset($data['originNm']) . chr(13) . chr(10); // [선택] 원산지
            $this->writeDbUrl('<<<deliv>>>' . gd_isset($deliveryPrice)); // [선택] 무료(0), 착불(-1), 배송비금액표기
            $couponPrice = gd_isset($this->setGoodsCoupon($data, 'pc'), '0');
            if ($couponPrice > 0) {
                $this->writeDbUrl('<<<coupo>>>' . gd_money_format($couponPrice, false) . '원'); // 크폰
            }
            $mcouPon = gd_isset($this->setGoodsCoupon($data, 'mobile'), 0);
            if (gd_isset($mcouPon) && $mcouPon > 0) {
                $this->writeDbUrl('<<<mcoupon>>>' . gd_money_format($mcouPon, false) . '원'); // 모바일쿠폰
            }

            if ($data['goodsDiscountFl'] == 'y' || $couponPrice > 0) {    //할인이 있으면

                $data['goodsDiscountPrice'] = $data['goodsPrice'] - $this->getGoodsDcPrice($data);

                $data['goodsDiscountPrice'] = $data['goodsDiscountPrice'] - $couponPrice;
                if ($data['goodsDiscountPrice'] < 0) {
                    $data['goodsDiscountPrice'] = 0;
                }
                $lPrice = $data['goodsPrice'];
                if ($lPrice < 0) {
                    $lPrice = 0;
                }
                $this->writeDbUrl('<<<lprice>>>' . gd_money_format($lPrice, false)); // 할인 전 가격
                if (0 > $data['goodsDiscountPrice']) {
                    $data['goodsDiscountPrice'] = 0;
                }
                $this->writeDbUrl('<<<price>>>' . gd_money_format($data['goodsDiscountPrice'], false)); // [필수] 할인적용가격

            } else {
                $this->writeDbUrl('<<<price>>>' . gd_money_format($data['goodsPrice'], false)); // [필수] 할인적용가격
            }

            $mileageAmount = $this->setGoodsMileage($data);
            if (empty($mileageAmount) === false && $mileageAmount != 0) {
                $this->writeDbUrl('<<<point>>>' . gd_isset($mileageAmount)); // [선택] 마일리지
            }
            $this->writeDbUrl('<<<ftend>>>');
            $this->totalDbUrlData++;
        }

        return true;
    }

    public function setGoodsMileage($data)
    {
        // 마일리지 처리

        $mileage = $this->mileage;
        $result = 0;

        $data['goodsMileageFl'] = 'y';
        // 통합 설정인 경우 마일리지 설정
        if ($data['mileageFl'] == 'c' && $mileage['give']['giveFl'] == 'y') {
            if ($mileage['give']['giveType'] == 'priceUnit') { // 금액 단위별
                $mileagePrice = floor($data['goodsPrice'] / $mileage['give']['goodsPriceUnit']);
                $mileageBasic = gd_number_figure($mileagePrice * $mileage['give']['goodsMileage'], $mileage['trunc']['unitPrecision'], $mileage['trunc']['unitRound']);
                $result = $mileageBasic . '원';
            } else if ($mileage['give']['giveType'] == 'cntUnit') { // 수량 단위별 (추가상품수량은 제외)
                $mileageBasic = gd_number_figure(1 * $mileage['give']['cntMileage'], $mileage['trunc']['unitPrecision'], $mileage['trunc']['unitRound']);
                $result = $mileageBasic . '원';
            } else { // 구매금액의 %
                $result = $mileage['give']['goods'] . '%';
            }
            // 개별 설정인 경우 마일리지 설정
        } else if ($data['mileageFl'] == 'g') {
            // 상품 기본 마일리지 정보
            if ($data['mileageGoodsUnit'] === 'percent') {
                $result = $data['mileageGoods'] * 1 . '%';   //유효숫자만 가져오기위해 곱하기1
            } else {
                // 정액인 경우 해당 설정된 금액으로
                $mileageBasic = gd_number_figure($data['mileageGoods'], $mileage['trunc']['unitPrecision'], $mileage['trunc']['unitRound']);
                $result = $mileageBasic . '원';
            }
        }

        return $result;
    }


    protected function notUseDbUrl(): bool
    {
        if (!key_exists('useFl', $this->config)) {
            $this->loadConfig();
        }

        return $this->config['useFl'] != 'y';
    }

    protected function selectStartWithEndGoodsNo(array $params = []): array
    {
        $this->goodsWheres = [];
        $this->goodsWheres[] = 'g.goodsDisplayFl = \'y\'';
        $this->goodsWheres[] = 'g.delFl = \'n\'';
        $this->goodsWheres[] = 'g.applyFl = \'y\'';
        $this->goodsWheres[] = 'NOT(g.stockFl = \'y\' AND g.totalStock = 0)';
        $this->goodsWheres[] = 'g.soldOutFl = \'n\'';
        $this->goodsWheres[] = '(UNIX_TIMESTAMP(g.goodsOpenDt) IS NULL  OR UNIX_TIMESTAMP(g.goodsOpenDt) = 0 OR UNIX_TIMESTAMP(g.goodsOpenDt) < UNIX_TIMESTAMP())';
        $this->goodsWheres[] = "g.daumFl = 'y'";
        $wheres = $this->goodsWheres;
        if (key_exists('where', $params) && count($params['where']) > 0) {
            $wheres = array_merge($wheres, $params['where']);
        }
        $strSQL = ' SELECT COUNT(g.goodsNo) AS cnt, min(g.goodsNo) AS startGoodsNo,max(g.goodsNo) AS endGoodsNo FROM ' . DB_GOODS . ' as g  WHERE ' . implode(' AND ', $wheres);

        $db = \App::getInstance('DB');
        $resultSet = $db->query_fetch($strSQL, null, false);

        $this->totalCount = StringUtils::strIsSet($resultSet['cnt'], 0);

        return $resultSet;
    }

    protected function writeDbUrl($contents)
    {
        parent::writeDbUrl(@iconv('UTF-8', 'EUC-KR//IGNORE', $contents));
    }
}
