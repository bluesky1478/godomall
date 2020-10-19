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

/**
 * Class AreaVO
 * @package Bundle\Component\MemberStatistics
 * @author  yjwee
 */
class AreaVO extends \Component\MemberStatistics\MemberVO
{
    private $seoulTotal, $busanTotal, $daeguTotal, $incheonTotal, $gwangjuTotal, $daejeonTotal, $ulsanTotal, $sejongTotal, $gyeonggiTotal, $gangwonTotal, $chungbukTotal, $chungnamTotal, $jeonbukTotal, $jeonnamTotal, $gyeongbukTotal, $gyeongnamTotal, $jejuTotal, $otherTotal;

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
     * @inheritdoc
     */
    public function setArrData(array $arrData)
    {
        foreach ($arrData as $index => $item) {
            $item = json_decode($item, true);
            if (count($item) < 1) {
                continue;
            }
            $dataDt = new DateTime($this->getEntryDt() . $index);
            /*
             * 검색기간 내의 데이터인지 체크
             */
            if ($this->searchDt[0] <= $dataDt && $this->searchDt[1] >= $dataDt) {
                $this->add($this->total, $item['total']);
                $this->add($this->seoulTotal, $item['seoul']);
                $this->add($this->busanTotal, $item['busan']);
                $this->add($this->daeguTotal, $item['daegu']);
                $this->add($this->incheonTotal, $item['incheon']);
                $this->add($this->gwangjuTotal, $item['gwangju']);
                $this->add($this->daejeonTotal, $item['daejeon']);
                $this->add($this->ulsanTotal, $item['ulsan']);
                $this->add($this->sejongTotal, $item['sejong']);
                $this->add($this->gyeonggiTotal, $item['gyeonggi']);
                $this->add($this->gangwonTotal, $item['gangwon']);
                $this->add($this->chungbukTotal, $item['chungbuk']);
                $this->add($this->chungnamTotal, $item['chungnam']);
                $this->add($this->jeonbukTotal, $item['jeonbuk']);
                $this->add($this->jeonnamTotal, $item['jeonnam']);
                $this->add($this->gyeongbukTotal, $item['gyeongbuk']);
                $this->add($this->gyeongnamTotal, $item['gyeongnam']);
                $this->add($this->jejuTotal, $item['jeju']);
                $this->add($this->otherTotal, $item['other']);

                $this->arrData[$dataDt->format('Ymd')] = $item;
            }
        }
    }

    /**
     * @return mixed
     */
    public function getSeoulTotal()
    {
        return $this->seoulTotal;
    }

    /**
     * @param mixed $seoulTotal
     */
    public function setSeoulTotal($seoulTotal)
    {
        $this->seoulTotal = $seoulTotal;
    }

    /**
     * @return mixed
     */
    public function getBusanTotal()
    {
        return $this->busanTotal;
    }

    /**
     * @param mixed $busanTotal
     */
    public function setBusanTotal($busanTotal)
    {
        $this->busanTotal = $busanTotal;
    }

    /**
     * @return mixed
     */
    public function getDaeguTotal()
    {
        return $this->daeguTotal;
    }

    /**
     * @param mixed $daeguTotal
     */
    public function setDaeguTotal($daeguTotal)
    {
        $this->daeguTotal = $daeguTotal;
    }

    /**
     * @return mixed
     */
    public function getIncheonTotal()
    {
        return $this->incheonTotal;
    }

    /**
     * @param mixed $incheonTotal
     */
    public function setIncheonTotal($incheonTotal)
    {
        $this->incheonTotal = $incheonTotal;
    }

    /**
     * @return mixed
     */
    public function getGwangjuTotal()
    {
        return $this->gwangjuTotal;
    }

    /**
     * @param mixed $gwangjuTotal
     */
    public function setGwangjuTotal($gwangjuTotal)
    {
        $this->gwangjuTotal = $gwangjuTotal;
    }

    /**
     * @return mixed
     */
    public function getDaejeonTotal()
    {
        return $this->daejeonTotal;
    }

    /**
     * @param mixed $daejeonTotal
     */
    public function setDaejeonTotal($daejeonTotal)
    {
        $this->daejeonTotal = $daejeonTotal;
    }

    /**
     * @return mixed
     */
    public function getUlsanTotal()
    {
        return $this->ulsanTotal;
    }

    /**
     * @param mixed $ulsanTotal
     */
    public function setUlsanTotal($ulsanTotal)
    {
        $this->ulsanTotal = $ulsanTotal;
    }

    /**
     * @return mixed
     */
    public function getSejongTotal()
    {
        return $this->sejongTotal;
    }

    /**
     * @param mixed $sejongTotal
     */
    public function setSejongTotal($sejongTotal)
    {
        $this->sejongTotal = $sejongTotal;
    }

    /**
     * @return mixed
     */
    public function getGyeonggiTotal()
    {
        return $this->gyeonggiTotal;
    }

    /**
     * @param mixed $gyeonggiTotal
     */
    public function setGyeonggiTotal($gyeonggiTotal)
    {
        $this->gyeonggiTotal = $gyeonggiTotal;
    }

    /**
     * @return mixed
     */
    public function getGangwonTotal()
    {
        return $this->gangwonTotal;
    }

    /**
     * @param mixed $gangwonTotal
     */
    public function setGangwonTotal($gangwonTotal)
    {
        $this->gangwonTotal = $gangwonTotal;
    }

    /**
     * @return mixed
     */
    public function getChungbukTotal()
    {
        return $this->chungbukTotal;
    }

    /**
     * @param mixed $chungbukTotal
     */
    public function setChungbukTotal($chungbukTotal)
    {
        $this->chungbukTotal = $chungbukTotal;
    }

    /**
     * @return mixed
     */
    public function getChungnamTotal()
    {
        return $this->chungnamTotal;
    }

    /**
     * @param mixed $chungnamTotal
     */
    public function setChungnamTotal($chungnamTotal)
    {
        $this->chungnamTotal = $chungnamTotal;
    }

    /**
     * @return mixed
     */
    public function getJeonbukTotal()
    {
        return $this->jeonbukTotal;
    }

    /**
     * @param mixed $jeonbukTotal
     */
    public function setJeonbukTotal($jeonbukTotal)
    {
        $this->jeonbukTotal = $jeonbukTotal;
    }

    /**
     * @return mixed
     */
    public function getJeonnamTotal()
    {
        return $this->jeonnamTotal;
    }

    /**
     * @param mixed $jeonnamTotal
     */
    public function setJeonnamTotal($jeonnamTotal)
    {
        $this->jeonnamTotal = $jeonnamTotal;
    }

    /**
     * @return mixed
     */
    public function getGyeongbukTotal()
    {
        return $this->gyeongbukTotal;
    }

    /**
     * @param mixed $gyeongbukTotal
     */
    public function setGyeongbukTotal($gyeongbukTotal)
    {
        $this->gyeongbukTotal = $gyeongbukTotal;
    }

    /**
     * @return mixed
     */
    public function getGyeongnamTotal()
    {
        return $this->gyeongnamTotal;
    }

    /**
     * @param mixed $gyeongnamTotal
     */
    public function setGyeongnamTotal($gyeongnamTotal)
    {
        $this->gyeongnamTotal = $gyeongnamTotal;
    }

    /**
     * @return mixed
     */
    public function getJejuTotal()
    {
        return $this->jejuTotal;
    }

    /**
     * @param mixed $jejuTotal
     */
    public function setJejuTotal($jejuTotal)
    {
        $this->jejuTotal = $jejuTotal;
    }
}
