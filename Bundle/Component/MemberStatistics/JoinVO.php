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
 * Class 신규회원 데이터 클래스
 * @package Bundle\Component\MemberStatistics
 * @author  yjwee
 */
class JoinVO extends \Component\MemberStatistics\StatisticsVO
{
    /**
     * @var int 총 PC쇼핑몰 신규회원수
     */
    protected $pcTotal = 0;
    /**
     * @var int 총 모바일쇼핑몰 신규회원수
     */
    protected $mobileTotal = 0;
    /**
     * @var JoinStatisticsVO
     */
    protected $joinStatisticsVo;
    /**
     * @var int 시간별 신규회원 최대 인원수
     */
    protected $max = 0;
    /**
     * @var int 시간별 신규회원 최소 인원수
     */
    protected $min = 0;
    /**
     * @var \DateTime 시간별 신규회원 최대 일시
     */
    protected $maxDt;
    /**
     * @var \DateTime 시간별 신규회원 최소 일시
     */
    protected $minDt;

    /**
     * @return int
     */
    public function getPcTotal()
    {
        return $this->pcTotal;
    }

    /**
     * @param int $pcTotal
     */
    public function setPcTotal($pcTotal)
    {
        $this->pcTotal = $pcTotal;
    }

    /**
     * @return int
     */
    public function getMobileTotal()
    {
        return $this->mobileTotal;
    }

    /**
     * @param int $mobileTotal
     */
    public function setMobileTotal($mobileTotal)
    {
        $this->mobileTotal = $mobileTotal;
    }

    /**
     * @return JoinStatisticsVO
     */
    public function getJoinStatisticsVo()
    {
        return $this->joinStatisticsVo;
    }

    /**
     * @param JoinStatisticsVO $joinStatisticsVo
     */
    public function setJoinStatisticsVo(JoinStatisticsVO $joinStatisticsVo)
    {
        $this->joinStatisticsVo = $joinStatisticsVo;
    }

    /**
     * @return int
     */
    public function getMax()
    {
        return $this->max;
    }

    /**
     * @param int $max
     *
     * @return bool 최대 값 여부
     */
    public function setMax($max)
    {
        if ($this->max < $max) {
            $this->max = $max;

            return true;
        }

        return false;
    }

    /**
     * @return int
     */
    public function getMin()
    {
        return $this->min;
    }

    /**
     * @param mixed $min
     *
     * @return bool 최소 값 여부
     */
    public function setMin($min)
    {
        if ($this->min > $min || $this->min === 0) {
            $this->min = $min;

            return true;
        }

        return false;
    }

    /**
     * @return DateTime
     */
    public function getMaxDt()
    {
        if (is_null($this->maxDt)) {
            return new DateTime();
        } else {
            return $this->maxDt;
        }
    }

    /**
     * @param DateTime $maxDt
     */
    public function setMaxDt(DateTime $maxDt)
    {
        $this->maxDt = $maxDt;
    }

    /**
     * @return DateTime
     */
    public function getMinDt()
    {
        if (is_null($this->minDt)) {
            return new DateTime();
        } else {
            return $this->minDt;
        }
    }

    /**
     * @param DateTime $minDt
     */
    public function setMinDt(DateTime $minDt)
    {
        $this->minDt = $minDt;
    }
}
