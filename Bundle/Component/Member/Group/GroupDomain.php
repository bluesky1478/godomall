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

namespace Bundle\Component\Member\Group;

use Framework\Utility\StringUtils;

/**
 * Class GroupDomain
 * @package Bundle\Component\Member\Group
 * @author  yjwee
 */
class GroupDomain
{
    private $sno, $groupNm, $groupSort, $groupMarkGb, $groupIcon, $groupImage, $settleGb, $fixedRateOption;
    private $dcLine, $dcType, $dcPercent, $dcPrice, $dcExOption, $dcExScm, $dcExCategory, $dcExBrand, $dcExGoods;
    private $overlapDcLine, $overlapDcType, $overlapDcPercent, $overlapDcPrice, $overlapDcOption, $overlapDcScm, $overlapDcCategory, $overlapDcBrand, $overlapDcGoods;
    private $mileageLine, $mileageType, $mileagePercent, $mileagePrice, $deliveryFreeFl;
    private $apprFigureOrderPriceFl, $apprFigureOrderRepeatFl, $apprFigureReviewRepeatFl, $apprFigureOrderPriceMore, $apprFigureOrderPriceBelow, $apprFigureOrderRepeat, $apprFigureReviewRepeat;
    private $apprPointMore;
    private $apprPointBelow;
    private $apprFigureOrderPriceMoreMobile;
    private $apprFigureOrderPriceBelowMobile;
    private $apprFigureOrderRepeatMobile;
    private $apprFigureReviewRepeatMobile;
    private $apprPointMoreMobile;
    private $apprPointBelowMobile;
    private $regId;
    private $managerNo;
    private $regDt;
    private $modDt;

    function __construct(array $arr = null)
    {
        if (is_null($arr) === false) {
            foreach ($arr as $key => $value) {
                $this->$key = $value;
            }
        }
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
    public function getGroupNm()
    {
        return $this->groupNm;
    }

    /**
     * @param mixed $groupNm
     */
    public function setGroupNm($groupNm)
    {
        $this->groupNm = $groupNm;
    }

    /**
     * @return mixed
     */
    public function getGroupSort()
    {
        return $this->groupSort;
    }

    /**
     * @param mixed $groupSort
     */
    public function setGroupSort($groupSort)
    {
        $this->groupSort = $groupSort;
    }

    /**
     * @return mixed
     */
    public function getGroupMarkGb()
    {
        return $this->groupMarkGb;
    }

    /**
     * @param mixed $groupMarkGb
     */
    public function setGroupMarkGb($groupMarkGb)
    {
        $this->groupMarkGb = $groupMarkGb;
    }

    /**
     * @return mixed
     */
    public function getGroupIcon()
    {
        return $this->groupIcon;
    }

    /**
     * @param mixed $groupIcon
     */
    public function setGroupIcon($groupIcon)
    {
        $this->groupIcon = $groupIcon;
    }

    /**
     * @return mixed
     */
    public function getGroupImage()
    {
        return $this->groupImage;
    }

    /**
     * @param mixed $groupImage
     */
    public function setGroupImage($groupImage)
    {
        $this->groupImage = $groupImage;
    }

    /**
     * @return mixed
     */
    public function getSettleGb()
    {
        return $this->settleGb;
    }

    /**
     * @param mixed $settleGb
     */
    public function setSettleGb($settleGb)
    {
        $this->settleGb = $settleGb;
    }

    /**
     * @return mixed
     */
    public function getFixedRateOption()
    {
        return $this->fixedRateOption;
    }

    /**
     * @param mixed $fixedRateOption
     */
    public function setFixedRateOption($fixedRateOption)
    {
        $this->fixedRateOption = $fixedRateOption;
    }

    /**
     * @return mixed
     */
    public function getDcLine()
    {
        return $this->dcLine;
    }

    /**
     * @param mixed $dcLine
     */
    public function setDcLine($dcLine)
    {
        $this->dcLine = $dcLine;
    }

    /**
     * @return mixed
     */
    public function getDcType()
    {
        return $this->dcType;
    }

    /**
     * @param mixed $dcType
     */
    public function setDcType($dcType)
    {
        $this->dcType = $dcType;
    }

    /**
     * @return mixed
     */
    public function getDcPercent()
    {
        return $this->dcPercent;
    }

    /**
     * @param mixed $dcPercent
     */
    public function setDcPercent($dcPercent)
    {
        $this->dcPercent = $dcPercent;
    }

    /**
     * @return mixed
     */
    public function getDcPrice()
    {
        return $this->dcPrice;
    }

    /**
     * @param mixed $dcPrice
     */
    public function setDcPrice($dcPrice)
    {
        $this->dcPrice = $dcPrice;
    }

    /**
     * @return mixed
     */
    public function getDcExOption()
    {
        return $this->dcExOption;
    }

    /**
     * @param mixed $dcExOption
     */
    public function setDcExOption($dcExOption)
    {
        $this->dcExOption = $dcExOption;
    }

    /**
     * @return mixed
     */
    public function getOverlapDcLine()
    {
        return $this->overlapDcLine;
    }

    /**
     * @param mixed $overlapDcLine
     */
    public function setOverlapDcLine($overlapDcLine)
    {
        $this->overlapDcLine = $overlapDcLine;
    }

    /**
     * @return mixed
     */
    public function getOverlapDcType()
    {
        return $this->overlapDcType;
    }

    /**
     * @param mixed $overlapDcType
     */
    public function setOverlapDcType($overlapDcType)
    {
        $this->overlapDcType = $overlapDcType;
    }

    /**
     * @return mixed
     */
    public function getOverlapDcPercent()
    {
        return $this->overlapDcPercent;
    }

    /**
     * @param mixed $overlapDcPercent
     */
    public function setOverlapDcPercent($overlapDcPercent)
    {
        $this->overlapDcPercent = $overlapDcPercent;
    }

    /**
     * @return mixed
     */
    public function getOverlapDcPrice()
    {
        return $this->overlapDcPrice;
    }

    /**
     * @param mixed $overlapDcPrice
     */
    public function setOverlapDcPrice($overlapDcPrice)
    {
        $this->overlapDcPrice = $overlapDcPrice;
    }

    /**
     * @return mixed
     */
    public function getOverlapDcOption()
    {
        return $this->overlapDcOption;
    }

    /**
     * @param mixed $overlapDcOption
     */
    public function setOverlapDcOption($overlapDcOption)
    {
        $this->overlapDcOption = $overlapDcOption;
    }

    /**
     * @return mixed
     */
    public function getMileageLine()
    {
        return $this->mileageLine;
    }

    /**
     * @param mixed $mileageLine
     */
    public function setMileageLine($mileageLine)
    {
        $this->mileageLine = $mileageLine;
    }

    /**
     * @return mixed
     */
    public function getMileageType()
    {
        return $this->mileageType;
    }

    /**
     * @param mixed $mileageType
     */
    public function setMileageType($mileageType)
    {
        $this->mileageType = $mileageType;
    }

    /**
     * @return mixed
     */
    public function getMileagePercent()
    {
        return $this->mileagePercent;
    }

    /**
     * @param mixed $mileagePercent
     */
    public function setMileagePercent($mileagePercent)
    {
        $this->mileagePercent = $mileagePercent;
    }

    /**
     * @return mixed
     */
    public function getMileagePrice()
    {
        return $this->mileagePrice;
    }

    /**
     * @param mixed $mileagePrice
     */
    public function setMileagePrice($mileagePrice)
    {
        $this->mileagePrice = $mileagePrice;
    }

    /**
     * @return mixed
     */
    public function getDeliveryFreeFl()
    {
        return $this->deliveryFreeFl;
    }

    /**
     * @param mixed $deliveryFreeFl
     */
    public function setDeliveryFreeFl($deliveryFreeFl)
    {
        $this->deliveryFreeFl = $deliveryFreeFl;
    }

    public function isApprFigureOrderPriceFl()
    {
        return $this->apprFigureOrderPriceFl == 'y';
    }

    public function getApprFigureOrderPriceFl()
    {
        return $this->apprFigureOrderPriceFl;
    }

    /**
     * @param mixed $apprFigureOrderPriceFl
     */
    public function setApprFigureOrderPriceFl($apprFigureOrderPriceFl)
    {
        $this->apprFigureOrderPriceFl = $apprFigureOrderPriceFl;
    }

    /**
     * @return mixed
     */
    public function getApprFigureOrderRepeatFl()
    {
        return $this->apprFigureOrderRepeatFl;
    }

    public function isApprFigureOrderRepeatFl()
    {
        return $this->apprFigureOrderRepeatFl == 'y';
    }

    /**
     * @param mixed $apprFigureOrderRepeatFl
     */
    public function setApprFigureOrderRepeatFl($apprFigureOrderRepeatFl)
    {
        $this->apprFigureOrderRepeatFl = $apprFigureOrderRepeatFl;
    }

    /**
     * @return mixed
     */
    public function getApprFigureReviewRepeatFl()
    {
        return $this->apprFigureReviewRepeatFl;
    }

    /**
     * @param mixed $apprFigureReviewRepeatFl
     */
    public function setApprFigureReviewRepeatFl($apprFigureReviewRepeatFl)
    {
        $this->apprFigureReviewRepeatFl = $apprFigureReviewRepeatFl;
    }

    public function isApprFigureReviewRepeatFl()
    {
        return $this->apprFigureReviewRepeatFl == 'y';
    }

    /**
     * @return mixed
     */
    public function getApprFigureOrderPriceMore()
    {
        return $this->apprFigureOrderPriceMore;
    }

    /**
     * @param mixed $apprFigureOrderPriceMore
     */
    public function setApprFigureOrderPriceMore($apprFigureOrderPriceMore)
    {
        $this->apprFigureOrderPriceMore = $apprFigureOrderPriceMore;
    }

    public function isApprFigureOrderPriceMore()
    {
        return $this->apprFigureOrderPriceMore > 0;
    }

    /**
     * @return mixed
     */
    public function getApprFigureOrderPriceBelow()
    {
        return $this->apprFigureOrderPriceBelow;
    }

    /**
     * @param mixed $apprFigureOrderPriceBelow
     */
    public function setApprFigureOrderPriceBelow($apprFigureOrderPriceBelow)
    {
        $this->apprFigureOrderPriceBelow = $apprFigureOrderPriceBelow;
    }

    public function isApprFigureOrderPriceBelow()
    {
        return $this->apprFigureOrderPriceBelow > 0;
    }

    public function greaterThanFigureOrderPrice()
    {
        return $this->apprFigureOrderPriceMore < $this->apprFigureOrderPriceBelow;
    }

    /**
     * @return mixed
     */
    public function getApprFigureOrderRepeat()
    {
        return $this->apprFigureOrderRepeat;
    }

    /**
     * @param mixed $apprFigureOrderRepeat
     */
    public function setApprFigureOrderRepeat($apprFigureOrderRepeat)
    {
        $this->apprFigureOrderRepeat = $apprFigureOrderRepeat;
    }

    public function isApprFigureOrderRepeat()
    {
        return $this->apprFigureOrderRepeat > 0;
    }

    /**
     * @return mixed
     */
    public function getApprFigureReviewRepeat()
    {
        return $this->apprFigureReviewRepeat;
    }

    /**
     * @param mixed $apprFigureReviewRepeat
     */
    public function setApprFigureReviewRepeat($apprFigureReviewRepeat)
    {
        $this->apprFigureReviewRepeat = $apprFigureReviewRepeat;
    }

    public function isApprFigureReviewRepeat()
    {
        return $this->apprFigureReviewRepeat > 0;
    }

    /**
     * @return mixed
     */
    public function getApprPointMore()
    {
        return $this->apprPointMore;
    }

    /**
     * @param mixed $apprPointMore
     */
    public function setApprPointMore($apprPointMore)
    {
        $this->apprPointMore = $apprPointMore;
    }

    public function isApprPointMore()
    {
        return $this->apprPointMore > 0;
    }

    /**
     * @return mixed
     */
    public function getApprPointBelow()
    {
        return $this->apprPointBelow;
    }

    /**
     * @param mixed $apprPointBelow
     */
    public function setApprPointBelow($apprPointBelow)
    {
        $this->apprPointBelow = $apprPointBelow;
    }

    public function isApprPointBelow()
    {
        return $this->apprPointBelow > 0;
    }

    public function greaterThanPoint()
    {
        return $this->apprPointMore < $this->apprPointBelow;
    }

    /**
     * @return mixed
     */
    public function getApprFigureOrderPriceMoreMobile()
    {
        return $this->apprFigureOrderPriceMoreMobile;
    }

    /**
     * @param mixed $apprFigureOrderPriceMoreMobile
     */
    public function setApprFigureOrderPriceMoreMobile($apprFigureOrderPriceMoreMobile)
    {
        $this->apprFigureOrderPriceMoreMobile = $apprFigureOrderPriceMoreMobile;
    }

    public function isApprFigureOrderPriceMoreMobile()
    {
        return $this->apprFigureOrderPriceMoreMobile > 0;
    }

    /**
     * @return mixed
     */
    public function getApprFigureOrderPriceBelowMobile()
    {
        return $this->apprFigureOrderPriceBelowMobile;
    }

    /**
     * @param mixed $apprFigureOrderPriceBelowMobile
     */
    public function setApprFigureOrderPriceBelowMobile($apprFigureOrderPriceBelowMobile)
    {
        $this->apprFigureOrderPriceBelowMobile = $apprFigureOrderPriceBelowMobile;
    }

    public function isApprFigureOrderPriceBelowMobile()
    {
        return $this->apprFigureOrderPriceBelowMobile > 0;
    }

    public function greaterThanFigureOrderPriceMobile()
    {
        return $this->apprFigureOrderPriceMoreMobile < $this->apprFigureOrderPriceBelowMobile;
    }

    /**
     * @return mixed
     */
    public function getApprFigureOrderRepeatMobile()
    {
        return $this->apprFigureOrderRepeatMobile;
    }

    /**
     * @param mixed $apprFigureOrderRepeatMobile
     */
    public function setApprFigureOrderRepeatMobile($apprFigureOrderRepeatMobile)
    {
        $this->apprFigureOrderRepeatMobile = $apprFigureOrderRepeatMobile;
    }

    public function isApprFigureOrderRepeatMobile()
    {
        return $this->apprFigureOrderRepeatMobile > 0;
    }

    /**
     * @return mixed
     */
    public function getApprFigureReviewRepeatMobile()
    {
        return $this->apprFigureReviewRepeatMobile;
    }

    /**
     * @param mixed $apprFigureReviewRepeatMobile
     */
    public function setApprFigureReviewRepeatMobile($apprFigureReviewRepeatMobile)
    {
        $this->apprFigureReviewRepeatMobile = $apprFigureReviewRepeatMobile;
    }

    public function isApprFigureReviewRepeatMobile()
    {
        return $this->apprFigureReviewRepeatMobile > 0;
    }

    /**
     * @return mixed
     */
    public function getApprPointMoreMobile()
    {
        return $this->apprPointMoreMobile;
    }

    /**
     * @param mixed $apprPointMoreMobile
     */
    public function setApprPointMoreMobile($apprPointMoreMobile)
    {
        $this->apprPointMoreMobile = $apprPointMoreMobile;
    }

    public function isApprPointMoreMobile()
    {
        return $this->apprPointMoreMobile > 0;
    }

    /**
     * @return mixed
     */
    public function getApprPointBelowMobile()
    {
        return $this->apprPointBelowMobile;
    }

    /**
     * @param mixed $apprPointBelowMobile
     */
    public function setApprPointBelowMobile($apprPointBelowMobile)
    {
        $this->apprPointBelowMobile = $apprPointBelowMobile;
    }

    public function isApprPointBelowMobile()
    {
        return $this->apprPointBelowMobile > 0;
    }

    public function greaterThanPointMobile()
    {
        return $this->apprPointMoreMobile < $this->apprPointBelowMobile;
    }

    /**
     * @return mixed
     */
    public function getRegId()
    {
        return $this->regId;
    }

    /**
     * @param mixed $regId
     */
    public function setRegId($regId)
    {
        $this->regId = $regId;
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

    /**
     * object return array
     *
     * @return array
     */
    public function toArray()
    {
        return get_object_vars($this);
    }

    /**
     * toJson
     *
     * @param $key
     */
    public function toJson($key)
    {
        $this->$key = json_encode($this->$key);
    }

    public function toJsonByFixedRateOption()
    {
        $this->fixedRateOption = json_encode($this->fixedRateOption);
    }

    public function toJsonByDiscountOption()
    {
        $this->dcExOption = json_encode($this->dcExOption);
        $this->dcExScm = json_encode($this->dcExScm);
        $this->dcExCategory = json_encode($this->dcExCategory);
        $this->dcExBrand = json_encode($this->dcExBrand);
        $this->dcExGoods = json_encode($this->dcExGoods);
    }

    public function toJsonByOverlapOption()
    {
        $this->overlapDcOption = json_encode($this->overlapDcOption);
        $this->overlapDcScm = json_encode($this->overlapDcScm);
        $this->overlapDcCategory = json_encode($this->overlapDcCategory);
        $this->overlapDcBrand = json_encode($this->overlapDcBrand);
        $this->overlapDcGoods = json_encode($this->overlapDcGoods);
    }

    public function greaterThanEqual($value1, $value2)
    {
        return $value1 > 0 && $value2 > 0 && $value1 >= $value2;
    }

    /**
     * 객체에 설정된 추가할인적용제외/중복할인적용에 대한 화면에 보여질 정보를 데이터베이스 조회를 통해 설정하는 함수
     */
    public function setGroupDcData()
    {
        $this->toStrip('dcExOption')->toJsonDecode('dcExOption', true);
        $this->toStrip('dcExScm')->toJsonDecode('dcExScm', true);
        $this->toStrip('dcExCategory')->toJsonDecode('dcExCategory', true);
        $this->toStrip('dcExBrand')->toJsonDecode('dcExBrand', true);
        $this->toStrip('dcExGoods')->toJsonDecode('dcExGoods');
        $this->toStrip('overlapDcOption')->toJsonDecode('overlapDcOption', true);
        $this->toStrip('overlapDcScm')->toJsonDecode('overlapDcScm', true);
        $this->toStrip('overlapDcCategory')->toJsonDecode('overlapDcCategory', true);
        $this->toStrip('overlapDcBrand')->toJsonDecode('overlapDcBrand', true);
        $this->toStrip('overlapDcGoods')->toJsonDecode('overlapDcGoods', true);

        // 추가할인적용제외 특정 공급사
        $this->setDcExScm(Util::getDiscountScm($this->getDcExScm()));

        // 추가할인적용제외 특정 카테고리
        $this->setDcExCategory(Util::getDiscountCategory($this->getDcExCategory()));

        // 추가할인적용제외 특정 브랜드
        $this->setDcExBrand(Util::getDiscountBrand($this->getDcExBrand()));

        // 추가할인적용제외 특정 상품
        $this->setDcExGoods(Util::getDiscountGoods($this->getDcExGoods()));

        // 중복할인 적용 특정 공급사
        $this->setOverlapDcScm(Util::getOverlapDiscountScm($this->getOverlapDcScm()));

        // 중복할인 적용 특정 카테고리
        $this->setOverlapDcCategory(Util::getOverlapDiscountCategory($this->getOverlapDcCategory()));

        // 중복할인 적용 특정 브랜드
        $this->setOverlapDcBrand(Util::getOverlapDiscountBrand($this->getOverlapDcBrand()));

        // 중복할인 적용 특정 상품
        $this->setOverlapDcGoods(Util::getOverlapDiscountGoods($this->getOverlapDcGoods()));
    }

    /**
     * toJsonDecode
     *
     * @param null $key
     * @param bool $assoc
     * @param int  $depth
     * @param int  $options
     *
     * @return $this
     */
    public function toJsonDecode($key = null, $assoc = false, $depth = 512, $options = 0)
    {
        if (StringUtils::isJson($this->$key) || StringUtils::isJsonArray($this->$key)) {
            $this->$key = json_decode($this->$key, $assoc, $depth, $options);
        }

        return $this;
    }

    public function toStrip($key = null)
    {
        $this->$key = StringUtils::htmlSpecialCharsStripSlashes($this->$key);

        return $this;
    }

    /**
     * @param mixed $dcExScm
     */
    public function setDcExScm($dcExScm)
    {
        $this->dcExScm = $dcExScm;
    }

    /**
     * @return mixed
     */
    public function getDcExScm()
    {
        return $this->dcExScm;
    }

    /**
     * @param mixed $dcExCategory
     */
    public function setDcExCategory($dcExCategory)
    {
        $this->dcExCategory = $dcExCategory;
    }

    /**
     * @return mixed
     */
    public function getDcExCategory()
    {
        return $this->dcExCategory;
    }

    /**
     * @param mixed $dcExBrand
     */
    public function setDcExBrand($dcExBrand)
    {
        $this->dcExBrand = $dcExBrand;
    }

    /**
     * @return mixed
     */
    public function getDcExBrand()
    {
        return $this->dcExBrand;
    }

    /**
     * @param mixed $dcExGoods
     */
    public function setDcExGoods($dcExGoods)
    {
        $this->dcExGoods = $dcExGoods;
    }

    /**
     * @return mixed
     */
    public function getDcExGoods()
    {
        return $this->dcExGoods;
    }

    /**
     * @param mixed $overlapDcScm
     */
    public function setOverlapDcScm($overlapDcScm)
    {
        $this->overlapDcScm = $overlapDcScm;
    }

    /**
     * @return mixed
     */
    public function getOverlapDcScm()
    {
        return $this->overlapDcScm;
    }

    /**
     * @param mixed $overlapDcCategory
     */
    public function setOverlapDcCategory($overlapDcCategory)
    {
        $this->overlapDcCategory = $overlapDcCategory;
    }

    /**
     * @return mixed
     */
    public function getOverlapDcCategory()
    {
        return $this->overlapDcCategory;
    }

    /**
     * @param mixed $overlapDcBrand
     */
    public function setOverlapDcBrand($overlapDcBrand)
    {
        $this->overlapDcBrand = $overlapDcBrand;
    }

    /**
     * @return mixed
     */
    public function getOverlapDcBrand()
    {
        return $this->overlapDcBrand;
    }

    /**
     * @param mixed $overlapDcGoods
     */
    public function setOverlapDcGoods($overlapDcGoods)
    {
        $this->overlapDcGoods = $overlapDcGoods;
    }

    /**
     * @return mixed
     */
    public function getOverlapDcGoods()
    {
        return $this->overlapDcGoods;
    }

    /**
     * @return mixed
     */
    public function getManagerNo()
    {
        return $this->managerNo;
    }

    /**
     * @param mixed $sno
     */
    public function setManagerNo($sno)
    {
        $this->managerNo = $sno;
    }

    /**
     * 기준등급 여부 반환
     *
     * @return bool
     */
    public function isDefaultGroup()
    {
        return $this->getGroupSort() == 1;
    }
}
