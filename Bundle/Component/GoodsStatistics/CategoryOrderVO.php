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

namespace Bundle\Component\GoodsStatistics;

/**
 * Class 카테고리 주문정보 ValueObject
 * @package Bundle\Component\GoodsStatistics
 * @author  yjwee
 */
class CategoryOrderVO
{
    private $orderNo;
    private $cateCd;
    private $orderStatus;
    private $taxTotalGoodsPrice;
    private $taxSupplyGoodsPrice;
    private $taxVatGoodsPrice;
    private $taxFreeGoodsPrice;
    private $goodsCnt;
    private $orderTypeFl;
    private $orderName;
    private $cateNm;

    /**
     * CategoryOrderVO constructor.
     * 상품 공급가, 부가세, 면세 가격은 총합하여 총 상품 가격으로 설정된다.
     *
     * @param array|null $arr 배열의 키값에 해당하는 변수에 값을 대입한다.
     */
    public function __construct(array $arr = null)
    {
        if (is_null($arr) === false) {
            foreach ($arr as $key => $value) {
                $this->$key = $value;

                // 상품 실제 결재 금액 계산
                if ($key == 'taxSupplyGoodsPrice' || $key == 'taxVatGoodsPrice' || $key == 'taxFreeGoodsPrice') {
                    $this->taxTotalGoodsPrice = $this->taxTotalGoodsPrice + $value;
                }
            }
        }
    }

    /**
     * toArray
     *
     * @return array
     */
    public function toArray()
    {
        return get_object_vars($this);
    }

    /**
     * @return mixed
     */
    public function getGoodsCnt()
    {
        return $this->goodsCnt;
    }

    /**
     * @param mixed $goodsCnt
     */
    public function setGoodsCnt($goodsCnt)
    {
        $this->goodsCnt = $goodsCnt;
    }

    /**
     * @return mixed
     */
    public function getCateCd()
    {
        return $this->cateCd;
    }

    /**
     * @param mixed $cateCd
     */
    public function setCateCd($cateCd)
    {
        $this->cateCd = $cateCd;
    }

    /**
     * @return mixed
     */
    public function getCateNm()
    {
        return $this->cateNm;
    }

    /**
     * @param mixed $cateNm
     */
    public function setCateNm($cateNm)
    {
        $this->cateNm = $cateNm;
    }

    /**
     * @return mixed
     */
    public function getTaxTotalGoodsPrice()
    {
        return $this->taxTotalGoodsPrice;
    }

    /**
     * @param mixed $taxTotalGoodsPrice
     */
    public function setTaxTotalGoodsPrice($taxTotalGoodsPrice)
    {
        $this->taxTotalGoodsPrice = $taxTotalGoodsPrice;
    }

    /**
     * @return mixed
     */
    public function getTaxSupplyGoodsPrice()
    {
        return $this->taxSupplyGoodsPrice;
    }

    /**
     * @param mixed $taxSupplyGoodsPrice
     */
    public function setTaxSupplyGoodsPrice($taxSupplyGoodsPrice)
    {
        $this->taxSupplyGoodsPrice = $taxSupplyGoodsPrice;
    }

    /**
     * @return mixed
     */
    public function getTaxVatGoodsPrice()
    {
        return $this->taxVatGoodsPrice;
    }

    /**
     * @param mixed $taxVatGoodsPrice
     */
    public function setTaxVatGoodsPrice($taxVatGoodsPrice)
    {
        $this->taxVatGoodsPrice = $taxVatGoodsPrice;
    }

    /**
     * @return mixed
     */
    public function getTaxFreeGoodsPrice()
    {
        return $this->taxFreeGoodsPrice;
    }

    /**
     * @param mixed $taxFreeGoodsPrice
     */
    public function setTaxFreeGoodsPrice($taxFreeGoodsPrice)
    {
        $this->taxFreeGoodsPrice = $taxFreeGoodsPrice;
    }

    /**
     * @return mixed
     */
    public function getOrderName()
    {
        return $this->orderName;
    }

    /**
     * @param mixed $orderName
     */
    public function setOrderName($orderName)
    {
        $this->orderName = $orderName;
    }

    /**
     * @return mixed
     */
    public function getOrderNo()
    {
        return $this->orderNo;
    }

    /**
     * @param mixed $orderNo
     */
    public function setOrderNo($orderNo)
    {
        $this->orderNo = $orderNo;
    }

    /**
     * @return mixed
     */
    public function getOrderStatus()
    {
        return $this->orderStatus;
    }

    /**
     * @param mixed $orderStatus
     */
    public function setOrderStatus($orderStatus)
    {
        $this->orderStatus = $orderStatus;
    }

    /**
     * @return mixed
     */
    public function getOrderTypeFl()
    {
        return $this->orderTypeFl;
    }

    /**
     * @param mixed $orderTypeFl
     */
    public function setOrderTypeFl($orderTypeFl)
    {
        $this->orderTypeFl = $orderTypeFl;
    }
}
