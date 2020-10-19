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

namespace Bundle\Component\MemberStatistics;

use DateTime;
use Framework\Utility\StringUtils;


/**
 * Class IdVO
 * @package Bundle\Component\MemberStatistics
 * @author  yjwee
 */
class IdVO extends \Component\MemberStatistics\MemberVO
{
    /**
     * @var
     */
    protected $orderCountPc, $orderCountMobile;

    /**
     * @var
     */
    protected $settlePricePc, $settlePriceMobile;
    /**
     * @var
     */
    protected $visitPageViewPc, $visitPageViewMobile;
    /**
     * @var
     */
    protected $visitPc, $visitMobile;

    private $memId;

    /**
     * @inheritDoc
     */
    public function __construct(array $array = null)
    {
        parent::__construct($array);
    }

    /**
     * @return mixed
     */
    public function getOrderCount()
    {
        return $this->orderCountPc + $this->orderCountMobile;
    }

    /**
     * @return mixed
     */
    public function getOderCountPc()
    {
        return $this->orderCountPc;
    }

    /**
     * @param mixed $orderCountPc
     */
    public function setOderCountPc($orderCountPc)
    {
        $this->orderCountPc = $orderCountPc;
    }

    /**
     * @return mixed
     */
    public function getOrderCountMobile()
    {
        return $this->orderCountMobile;
    }

    /**
     * @param mixed $orderCountMobile
     */
    public function setOrderCountMobile($orderCountMobile)
    {
        $this->orderCountMobile = $orderCountMobile;
    }

    /**
     * @return mixed
     */
    public function getSettlePrice()
    {
        return $this->settlePricePc + $this->settlePriceMobile;
    }

    /**
     * @return mixed
     */
    public function getSettlePricePc()
    {
        return $this->settlePricePc;
    }

    /**
     * @param mixed $settlePricePc
     */
    public function setSettlePricePc($settlePricePc)
    {
        $this->settlePricePc = $settlePricePc;
    }

    /**
     * @return mixed
     */
    public function getSettlePriceMobile()
    {
        return $this->settlePriceMobile;
    }

    /**
     * @param mixed $settlePriceMobile
     */
    public function setSettlePriceMobile($settlePriceMobile)
    {
        $this->settlePriceMobile = $settlePriceMobile;
    }

    /**
     * @return mixed
     */
    public function getVisitPageView()
    {
        return $this->visitPageViewPc + $this->visitPageViewMobile;
    }

    /**
     * @return mixed
     */
    public function getVisitPageViewPc()
    {
        return $this->visitPageViewPc;
    }

    /**
     * @param mixed $visitPageViewPc
     */
    public function setVisitPageViewPc($visitPageViewPc)
    {
        $this->visitPageViewPc = $visitPageViewPc;
    }

    /**
     * @return mixed
     */
    public function getVisitPageViewMobile()
    {
        return $this->visitPageViewMobile;
    }

    /**
     * @param mixed $visitPageViewMobile
     */
    public function setVisitPageViewMobile($visitPageViewMobile)
    {
        $this->visitPageViewMobile = $visitPageViewMobile;
    }

    /**
     * @return mixed
     */
    public function getVisit()
    {
        return $this->visitPc + $this->visitMobile;
    }

    /**
     * @return mixed
     */
    public function getVisitPc()
    {
        return $this->visitPc;
    }

    /**
     * @param mixed $visitPc
     */
    public function setVisitPc($visitPc)
    {
        $this->visitPc = $visitPc;
    }

    /**
     * @return mixed
     */
    public function getVisitMobile()
    {
        return $this->visitMobile;
    }

    /**
     * @param mixed $visitMobile
     */
    public function setVisitMobile($visitMobile)
    {
        $this->visitMobile = $visitMobile;
    }

    /**
     * setArrData
     *
     * @param array $list
     */
    public function setArrData(array $list)
    {
        foreach ($list as $index => $item) {
            if (empty($item)) {
                continue;
            }
            $item = json_decode(StringUtils::htmlSpecialCharsStripSlashes($item), true);
            $dataDt = new DateTime($this->getEntryDt() . str_pad($index, 2, 0, STR_PAD_LEFT));
            /*
            * 검색기간 내의 데이터인지 체크
            */
            if ($this->searchDt[0] <= $dataDt && $this->searchDt[1] >= $dataDt) {
                foreach ($item as $key => $value) {
                    $this->total++;
                    $this->addArray($this, $value);

                    if (key_exists($key, $this->arrData)) {
                        $this->addArray($this->arrData[$key], $value);
                    } else {
                        $this->arrData[$key] = new IdVO($value);
                    }
                }
            }
        }
    }

    /**
     * getArrData
     *
     * @return array
     */
    public function getArrData()
    {
        $memId = $price = $result = [];
        /**
         * @var IdVO $item
         */
        foreach ($this->arrData as $index => $item) {
            $item->memId = $index;
            $price[$index] = $item->settlePricePc + $item->settlePriceMobile;
            $memId[$index] = $index;
        }
        array_multisort($price, SORT_DESC, $memId, SORT_ASC, $this->arrData);

        return $this->arrData;
    }
}
