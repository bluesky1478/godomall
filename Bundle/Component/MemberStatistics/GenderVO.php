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

namespace Bundle\Component\MemberStatistics;


use DateTime;
use Framework\Utility\StringUtils;

/**
 * Class GenderVO
 * @package Bundle\Component\MemberStatistics
 * @author  yjwee
 */
class GenderVO extends \Component\MemberStatistics\MemberVO
{
    /**
     * @var
     */
    private $maleTotal;
    /**
     * @var
     */
    private $femaleTotal;
    /**
     * @var
     */
    private $otherTotal;
    /**
     * @var
     */
    private $maleRate;
    /**
     * @var
     */
    private $femaleRate;
    /**
     * @var
     */
    private $otherRate;

    /**
     * @return mixed
     */
    public function getTotal($isFormat = false)
    {
        if ($isFormat) {
            return number_format($this->total);
        } else {
            return $this->total;
        }
    }

    /**
     * @return mixed
     */
    public function getMaleTotal($isFormat = false)
    {
        if ($isFormat) {
            return number_format($this->maleTotal);
        } else {
            return $this->maleTotal;
        }
    }

    /**
     * @param mixed $maleTotal
     */
    public function setMaleTotal($maleTotal)
    {
        $this->maleTotal = $maleTotal;
    }

    /**
     * @return mixed
     */
    public function getFemaleTotal($isFormat = false)
    {
        if ($isFormat) {
            return number_format($this->femaleTotal);
        } else {
            return $this->femaleTotal;
        }
    }

    /**
     * @param mixed $femaleTotal
     */
    public function setFemaleTotal($femaleTotal)
    {
        $this->femaleTotal = $femaleTotal;
    }

    /**
     * @return mixed
     */
    public function getOtherTotal($isFormat = false)
    {
        if ($isFormat) {
            return number_format($this->otherTotal);
        } else {
            return $this->otherTotal;
        }
    }

    /**
     * @param mixed $otherTotal
     */
    public function setOtherTotal($otherTotal)
    {
        $this->otherTotal = $otherTotal;
    }

    /**
     * @param array $arrData
     */
    public function setArrData(array $arrData)
    {
        foreach ($arrData as $index => $item) {
            $item = json_decode(StringUtils::htmlSpecialCharsStripSlashes($item), true);
            $dataDt = new DateTime($this->getEntryDt() . $index);
            /*
             * 검색기간 내의 데이터인지 체크
             */
            if ($this->searchDt[0] <= $dataDt && $this->searchDt[1] >= $dataDt) {
                $this->total += ($item['male'] + $item['female'] + $item['other']);
                $this->maleTotal += $item['male'];
                $this->femaleTotal += $item['female'];
                $this->otherTotal += $item['other'];
                $this->arrData[$dataDt->format('Ymd')] = $item;
            }
        }
    }

    /**
     * @return mixed
     */
    public function getMaleRate($isFormat = false)
    {
        if ($isFormat) {
            return number_format($this->maleRate) . '%';
        } else {
            return $this->maleRate;
        }
    }

    /**
     * @param mixed $maleRate
     */
    public function setMaleRate($maleRate)
    {
        $this->maleRate = $maleRate;
    }

    /**
     * @return mixed
     */
    public function getFemaleRate($isFormat = false)
    {
        if ($isFormat) {
            return number_format($this->femaleRate) . '%';
        } else {
            return $this->femaleRate;
        }
    }

    /**
     * @param mixed $femaleRate
     */
    public function setFemaleRate($femaleRate)
    {
        $this->femaleRate = $femaleRate;
    }

    /**
     * @return mixed
     */
    public function getOtherRate($isFormat = false)
    {
        if ($isFormat) {
            return number_format($this->otherRate) . '%';
        } else {
            return $this->otherRate;
        }
    }

    /**
     * @param mixed $otherRate
     */
    public function setOtherRate($otherRate)
    {
        $this->otherRate = $otherRate;
    }

    /**
     * calculateRate
     */
    public function calculateRate()
    {
        if (count($this->arrData) > 0) {
            foreach ($this->arrData as $index => &$item) {
                $total = $item['total'];
                if ($total < 1) {
                    $total = 1;
                }
                $male = StringUtils::strIsSet($item['male'], 0);
                $female = StringUtils::strIsSet($item['female'], 0);
                $other = StringUtils::strIsSet($item['other'], 0);
                $maleRate = ($male / $total) * 100;
                $item['maleRate'] = $maleRate;
                $femaleRate = ($female / $total) * 100;
                $item['femaleRate'] = $femaleRate;
                $otherRate = ($other / $total) * 100;
                $item['otherRate'] = $otherRate;
            }
        }
    }

}
