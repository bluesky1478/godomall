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
 * Class AgeVO
 * @package Bundle\Component\MemberStatistics
 * @author  yjwee
 */
class AgeVO extends \Component\MemberStatistics\MemberVO
{
    /**
     * @var
     */
    private $age10;
    /**
     * @var
     */
    private $age20;
    /**
     * @var
     */
    private $age30;
    /**
     * @var
     */
    private $age40;
    /**
     * @var
     */
    private $age50;
    /**
     * @var
     */
    private $age60;
    /**
     * @var
     */
    private $age70;
    /**
     * @var
     */
    private $ageRate10;
    /**
     * @var
     */
    private $ageRate20;
    /**
     * @var
     */
    private $ageRate30;
    /**
     * @var
     */
    private $ageRate40;
    /**
     * @var
     */
    private $ageRate50;
    /**
     * @var
     */
    private $ageRate60;
    /**
     * @var
     */
    private $ageRate70;
    /**
     * @var
     */
    private $otherTotal;
    /**
     * @var
     */
    private $otherRate;

    /**
     * @return mixed
     */
    public function getOtherTotal()
    {
        return $this->otherTotal;
    }

    /**
     * @param mixed $otherTotal
     */
    public function setOtherTotal($otherTotal)
    {
        $this->otherTotal = $otherTotal;
    }

    /**
     * @return mixed
     */
    public function getOtherRate()
    {
        return $this->otherRate;
    }

    /**
     * @param mixed $otherRate
     */
    public function setOtherRate($otherRate)
    {
        $this->otherRate = $otherRate;
    }

    /**
     * @param array $arrData
     */
    public function setArrData(array $arrData)
    {
        foreach ($arrData as $index => $item) {
            \Logger::debug($item);
            $item = json_decode(StringUtils::htmlSpecialCharsStripSlashes($item), true);
            $dataDt = new DateTime($this->getEntryDt() . $index);

            if ($this->searchDt[0] <= $dataDt && $this->searchDt[1] >= $dataDt) {
                $this->total += intval($item['total']);
                $this->age10 += intval($item['10']);
                $this->age20 += intval($item['20']);
                $this->age30 += intval($item['30']);
                $this->age40 += intval($item['40']);
                $this->age50 += intval($item['50']);
                $this->age60 += intval($item['60']);
                $this->age70 += intval($item['70']);
                $this->otherTotal += intval($item['other']);
                $this->arrData[$dataDt->format('Ymd')] = $item;
            }
        }
    }

    /**
     * @return mixed
     */
    public function getAge70()
    {
        return $this->age70;
    }

    /**
     * @param mixed $age70
     */
    public function setAge70($age70)
    {
        $this->age70 = $age70;
    }

    /**
     * @return mixed
     */
    public function getAge10()
    {
        return $this->age10;
    }

    /**
     * @param mixed $age10
     */
    public function setAge10($age10)
    {
        $this->age10 = $age10;
    }

    /**
     * @return mixed
     */
    public function getAge20()
    {
        return $this->age20;
    }

    /**
     * @param mixed $age20
     */
    public function setAge20($age20)
    {
        $this->age20 = $age20;
    }

    /**
     * @return mixed
     */
    public function getAge30()
    {
        return $this->age30;
    }

    /**
     * @param mixed $age30
     */
    public function setAge30($age30)
    {
        $this->age30 = $age30;
    }

    /**
     * @return mixed
     */
    public function getAge40()
    {
        return $this->age40;
    }

    /**
     * @param mixed $age40
     */
    public function setAge40($age40)
    {
        $this->age40 = $age40;
    }

    /**
     * @return mixed
     */
    public function getAge50()
    {
        return $this->age50;
    }

    /**
     * @param mixed $age50
     */
    public function setAge50($age50)
    {
        $this->age50 = $age50;
    }

    /**
     * @return mixed
     */
    public function getAge60()
    {
        return $this->age60;
    }

    /**
     * @param mixed $age60
     */
    public function setAge60($age60)
    {
        $this->age60 = $age60;
    }

    /**
     * @return mixed
     */
    public function getAgeRate10()
    {
        return $this->ageRate10;
    }

    /**
     * @param mixed $ageRate10
     */
    public function setAgeRate10($ageRate10)
    {
        $this->ageRate10 = $ageRate10;
    }

    /**
     * @return mixed
     */
    public function getAgeRate20()
    {
        return $this->ageRate20;
    }

    /**
     * @param mixed $ageRate20
     */
    public function setAgeRate20($ageRate20)
    {
        $this->ageRate20 = $ageRate20;
    }

    /**
     * @return mixed
     */
    public function getAgeRate30()
    {
        return $this->ageRate30;
    }

    /**
     * @param mixed $ageRate30
     */
    public function setAgeRate30($ageRate30)
    {
        $this->ageRate30 = $ageRate30;
    }

    /**
     * @return mixed
     */
    public function getAgeRate40()
    {
        return $this->ageRate40;
    }

    /**
     * @param mixed $ageRate40
     */
    public function setAgeRate40($ageRate40)
    {
        $this->ageRate40 = $ageRate40;
    }

    /**
     * @return mixed
     */
    public function getAgeRate50()
    {
        return $this->ageRate50;
    }

    /**
     * @param mixed $ageRate50
     */
    public function setAgeRate50($ageRate50)
    {
        $this->ageRate50 = $ageRate50;
    }

    /**
     * @return mixed
     */
    public function getAgeRate60()
    {
        return $this->ageRate60;
    }

    /**
     * @param mixed $ageRate60
     */
    public function setAgeRate60($ageRate60)
    {
        $this->ageRate60 = $ageRate60;
    }

    /**
     * @return mixed
     */
    public function getAgeRate70()
    {
        return $this->ageRate70;
    }

    /**
     * @param mixed $ageRate70
     */
    public function setAgeRate70($ageRate70)
    {
        $this->ageRate70 = $ageRate70;
    }
}
