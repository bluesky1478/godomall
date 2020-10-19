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

namespace Bundle\Component\Goods;

/**
 * Goods 관련 정보를 담은 클래스
 *
 * @package Bundle\Component\Goods
 * @author  yjwee <yeongjong.wee@godo.co.kr>
 */
class DefineGoods
{
    public $goodsStateList = [];
    public $goodsPermissionList = [];
    public $goodsPayLimit = [];
    public $goodsImportType = [];
    public $goodsSellType = [];
    public $goodsAgeType = [];
    public $goodsGenderType = [];
    public $fixedSales = [];
    public $fixedOrderCnt = [];
    public $hscode = [];
    public $kcmarkCode = [];

    public function __construct()
    {
        $this->goodsStateList = [
            'n' => __('신상품'),
            'u' => __('중고'),
            'r' => __('반품'),
            'f' => __('리퍼'),
            'd' => __('전시'),
            'b' => __('스크래치'),
        ];
        $this->goodsPermissionList = [
            'all'    => __('전체(회원+비회원)'),
            'member' => __('회원전용(비회원제외)'),
            'group'  => __('특정회원등급'),
        ];
        $this->goodsPayLimit = [
            'gb' => __('무통장 사용'),
            'pg' => __('PG결제 사용'),
            'gm' => __('마일리지 사용'),
            'gd' => __('예치금 사용'),
        ];
        $this->goodsImportType = [
            'f' => __('해외구매대행'),
            'd' => __('병행수입'),
            'o' => __('주문제작'),
        ];
        $this->goodsSellType = [
            'w' => __('도매'),
            'r' => __('렌탈'),
            'h' => __('대여'),
            'i' => __('할부'),
            's' => __('예약판매'),
            'b' => __('구매대행'),
        ];
        $this->goodsAgeType = [
            'a' => __('성인'),
            'y' => __('청소년'),
            'c' => __('아동'),
            'b' => __('유아'),
        ];
        $this->goodsGenderType = [
            'm' => __('남성'),
            'w' => __('여성'),
            'c' => __('공용'),
        ];
        $this->fixedSales = [
            'option' => __('옵션기준'),
            'goods'  => __('상품기준'),
        ];
        $this->fixedOrderCnt = [
            'option' => __('옵션기준'),
            'goods'  => __('상품기준'),
            'id'  => __('ID기준'),
        ];
        $this->hscode = [
            'kr' => __('대한민국'),
            'us' => __('미국'),
            'cn' => __('중국'),
            'jp' => __('일본'),
        ];
        $this->kcmarkCode = [
            'kcCd01' => __('[어린이제품] 공급자적합성확인'),
            'kcCd02' => __('[어린이제품] 안전인증'),
            'kcCd03' => __('[어린이제품] 안전확인'),
            'kcCd04' => __('[방송통신기자재] 잠정인증'),
            'kcCd05' => __('[방송통신기자재] 적합등록'),
            'kcCd06' => __('[방송통신기자재] 적합인증'),
            'kcCd07' => __('[생활용품] 공급자적합성확인'),
            'kcCd08' => __('[생활용품] 안전인증'),
            'kcCd09' => __('[생활용품] 안전확인'),
            'kcCd10' => __('[생활용품] 어린이보호포장'),
            'kcCd11' => __('[전기용품] 공급자적합성확인'),
            'kcCd12' => __('[전기용품] 안전인증'),
            'kcCd13' => __('[전기용품] 안전확인'),
        ];
    }

    /**
     * @return array
     */
    public function getGoodsStateList(): array
    {
        return $this->goodsStateList;
    }

    /**
     * @return array
     */
    public function getGoodsPermissionList(): array
    {
        return $this->goodsPermissionList;
    }

    /**
     * @return array
     */
    public function getGoodsPayLimit(): array
    {
        return $this->goodsPayLimit;
    }

    /**
     * @return array
     */
    public function getGoodsImportType(): array
    {
        return $this->goodsImportType;
    }

    /**
     * @return array
     */
    public function getGoodsSellType(): array
    {
        return $this->goodsSellType;
    }

    /**
     * @return array
     */
    public function getGoodsAgeType(): array
    {
        return $this->goodsAgeType;
    }

    /**
     * @return array
     */
    public function getGoodsGenderType(): array
    {
        return $this->goodsGenderType;
    }

    /**
     * @return array
     */
    public function getFixedSales(): array
    {
        return $this->fixedSales;
    }

    /**
     * @return array
     */
    public function getFixedOrderCnt(): array
    {
        return $this->fixedOrderCnt;
    }

    /**
     * @return array
     */
    public function getHscode(): array
    {
        return $this->hscode;
    }

    /**
     * @return array
     */
    public function getKcmarkcode(): array
    {
        return $this->kcmarkCode;
    }
}
