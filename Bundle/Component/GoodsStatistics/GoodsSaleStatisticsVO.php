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


use Framework\Utility\NumberUtils;

/**
 * Class 상품통계-판매순위분석 ValueObject
 * @package Bundle\Component\GoodsStatistics
 * @author  yjwee
 */
class GoodsSaleStatisticsVO
{
    private $sno;
    private $imageStorage;
    private $imagePath;
    private $imageName;
    private $goodsNo;
    private $goodsNm;
    private $companyNm;
    private $cateCd;
    private $totalPrice;
    private $pcPrice;
    private $mobilePrice;
    private $totalOrderGoodsCount;
    private $pcOrderGoodsCount;
    private $mobileOrderGoodsCount;
    private $totalOrderCount;
    private $pcOrderCount;
    private $mobileOrderCount;
    private $regDt;
    private $modDt;

    /**
     * GoodsSaleStatisticsVO constructor.
     *
     * @param array|null $arr
     */
    public function __construct(array $arr = null)
    {
        if (is_null($arr) === false) {
            foreach ($arr as $key => $value) {
                $this->$key = $value;
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
    public function getSno()
    {
        return $this->sno;
    }

    /**
     * @param mixed $sno
     */
    public function setSno($sno)
    {
        $this->sno = $sno;
    }

    /**
     * @return mixed
     */
    public function getImageStorage()
    {
        return $this->imageStorage;
    }

    /**
     * @param mixed $imageStorage
     */
    public function setImageStorage($imageStorage)
    {
        $this->imageStorage = $imageStorage;
    }

    /**
     * @return mixed
     */
    public function getImagePath()
    {
        return $this->imagePath;
    }

    /**
     * @param mixed $imagePath
     */
    public function setImagePath($imagePath)
    {
        $this->imagePath = $imagePath;
    }

    /**
     * @return mixed
     */
    public function getImageName()
    {
        return $this->imageName;
    }

    /**
     * @param mixed $imageName
     */
    public function setImageName($imageName)
    {
        $this->imageName = $imageName;
    }

    /**
     * @return mixed
     */
    public function getGoodsNo()
    {
        return $this->goodsNo;
    }

    /**
     * @param mixed $goodsNo
     */
    public function setGoodsNo($goodsNo)
    {
        $this->goodsNo = $goodsNo;
    }

    /**
     * @return mixed
     */
    public function getGoodsNm()
    {
        return $this->goodsNm;
    }

    /**
     * @param mixed $goodsNm
     */
    public function setGoodsNm($goodsNm)
    {
        $this->goodsNm = $goodsNm;
    }

    /**
     * @return mixed
     */
    public function getCompanyNm()
    {
        return $this->companyNm;
    }

    /**
     * @param mixed $companyNm
     */
    public function setCompanyNm($companyNm)
    {
        $this->companyNm = $companyNm;
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
     * @param bool $isDisplay
     *
     * @return mixed
     */
    public function getTotalPrice($isDisplay = false)
    {
        if ($isDisplay) {
            return NumberUtils::currencyDisplay($this->totalPrice);
        }

        return $this->totalPrice;
    }

    /**
     * @param mixed $totalPrice
     */
    public function setTotalPrice($totalPrice)
    {
        $this->totalPrice = $totalPrice;
    }

    /**
     * @param bool $isDisplay
     *
     * @return mixed
     */
    public function getPcPrice($isDisplay = false)
    {
        if ($isDisplay) {
            return NumberUtils::currencyDisplay($this->pcPrice);
        }

        return $this->pcPrice;
    }

    /**
     * @param mixed $pcPrice
     */
    public function setPcPrice($pcPrice)
    {
        $this->pcPrice = $pcPrice;
    }

    /**
     * @param bool $isDisplay
     *
     * @return mixed
     */
    public function getMobilePrice($isDisplay = false)
    {
        if ($isDisplay) {
            return NumberUtils::currencyDisplay($this->mobilePrice);
        }

        return $this->mobilePrice;
    }

    /**
     * @param mixed $mobilePrice
     */
    public function setMobilePrice($mobilePrice)
    {
        $this->mobilePrice = $mobilePrice;
    }

    /**
     * @return mixed
     */
    public function getTotalOrderGoodsCount()
    {
        return $this->totalOrderGoodsCount;
    }

    /**
     * @param mixed $totalOrderGoodsCount
     */
    public function setTotalOrderGoodsCount($totalOrderGoodsCount)
    {
        $this->totalOrderGoodsCount = $totalOrderGoodsCount;
    }

    /**
     * @return mixed
     */
    public function getPcOrderGoodsCount()
    {
        return $this->pcOrderGoodsCount;
    }

    /**
     * @param mixed $pcOrderGoodsCount
     */
    public function setPcOrderGoodsCount($pcOrderGoodsCount)
    {
        $this->pcOrderGoodsCount = $pcOrderGoodsCount;
    }

    /**
     * @return mixed
     */
    public function getMobileOrderGoodsCount()
    {
        return $this->mobileOrderGoodsCount;
    }

    /**
     * @param mixed $mobileOrderGoodsCount
     */
    public function setMobileOrderGoodsCount($mobileOrderGoodsCount)
    {
        $this->mobileOrderGoodsCount = $mobileOrderGoodsCount;
    }

    /**
     * @return mixed
     */
    public function getTotalOrderCount()
    {
        return $this->totalOrderCount;
    }

    /**
     * @param mixed $totalOrderCount
     */
    public function setTotalOrderCount($totalOrderCount)
    {
        $this->totalOrderCount = $totalOrderCount;
    }

    /**
     * @return mixed
     */
    public function getPcOrderCount()
    {
        return $this->pcOrderCount;
    }

    /**
     * @param mixed $pcOrderCount
     */
    public function setPcOrderCount($pcOrderCount)
    {
        $this->pcOrderCount = $pcOrderCount;
    }

    /**
     * @return mixed
     */
    public function getMobileOrderCount()
    {
        return $this->mobileOrderCount;
    }

    /**
     * @param mixed $mobileOrderCount
     */
    public function setMobileOrderCount($mobileOrderCount)
    {
        $this->mobileOrderCount = $mobileOrderCount;
    }

    /**
     * @return mixed
     */
    public function getRegDt()
    {
        return $this->regDt;
    }

    /**
     * @param mixed $regDt
     */
    public function setRegDt($regDt)
    {
        $this->regDt = $regDt;
    }

    /**
     * @return mixed
     */
    public function getModDt()
    {
        return $this->modDt;
    }

    /**
     * @param mixed $modDt
     */
    public function setModDt($modDt)
    {
        $this->modDt = $modDt;
    }

}
