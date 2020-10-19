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
use Exception;
use Framework\Utility\StringUtils;
use Respect\Validation\Validator;

/**
 * Class 신규회원분석 테이블을 조회한 데이터를 담는 클래스
 * @package Bundle\Component\MemberStatistics
 * @author  yjwee
 */
class JoinStatisticsVO
{
    /**
     *  Month 테이블의 월별 기준일에 들어가는 년도 포맷
     */
    const DATE_FORMAT_Y = 'Y';
    /**
     * Day 테이블의 일별 기준일에 들어가는 년월 포맷
     */
    const DATE_FORMAT_YM = 'Ym';
    /**
     * Hour 테이블의 시간별 기준에 들어가는 년월일 포맷
     */
    const DATE_FORMAT_YMD = 'Ymd';

    /**
     * @var string|int 기준일
     */
    private $joinDate;
    /**
     * @var array 기준일의 데이터 1로우의 결과값
     */
    private $arrData;
    /**
     * @var string 기준일의 데이터 포맷 형식
     */
    private $dateFormat;
    /**
     * @var string 등록일
     */
    private $regDt;
    /**
     * @var string 수정일
     */
    private $modDt;

    /**
     * @var DateTime[] 검색 기간
     */
    private $searchDt;

    /**
     * JoinStatisticsVO constructor.
     *
     * @param string $dateFormat 일별,월별,시간별,요일별 데이터 조회에 따른 배열 데이터의 키값의 포맷
     */
    public function __construct($dateFormat)
    {
        $this->dateFormat = $dateFormat;
    }

    /**
     * 신규회원분석 테이블 조회 결과(json)를 배열형태로 반환하는 함수
     *
     * @return array
     */
    public function toArray()
    {
        foreach ($this->arrData as $key => &$value) {
            $value = json_encode($value);
        }
        $this->arrData['join' . strtoupper($this->dateFormat)] = $this->joinDate;

        return $this->arrData;
    }

    /**
     * 신규회원 VO의 데이터를 회원가입 구분에 따라 합산하여 배열에 저장하는 함수
     *
     * @param $data
     * @param $isMobile
     */
    public function setData($data, $isMobile)
    {
        if (is_null($this->arrData[$data]['total'])) {
            $this->arrData[$data]['total'] = 0;
        }
        $this->arrData[$data]['total']++;

        if ($isMobile) {
            if (is_null($this->arrData[$data]['mobile'])) {
                $this->arrData[$data]['mobile'] = 0;
            }
            $this->arrData[$data]['mobile']++;
        } else {
            if (is_null($this->arrData[$data]['pc'])) {
                $this->arrData[$data]['pc'] = 0;
            }
            $this->arrData[$data]['pc']++;
        }
    }

    /**
     * @param null $format
     *
     * @return mixed
     */
    public function getJoinDate($format = null)
    {
        if (is_null($format) === false) {
            $date = new DateTime($this->joinDate);

            return $date->format($format);
        }

        return $this->joinDate;
    }

    /**
     * @param mixed $joinDate
     *
     * @throws Exception
     */
    public function setJoinDate($joinDate)
    {
        if (!Validator::date($this->dateFormat)->validate(strval($joinDate))) {
            throw new Exception(sprintf(__('포맷은 `%s` 형식이어야 합니다.'),$this->dateFormat));
        }
        $this->joinDate = $joinDate;
    }

    /**
     * @return array
     */
    public function getArrData()
    {
        return $this->arrData;
    }

    /**
     * 배열로 넘어온 json 데이터를 배열형태로 저장하는 함수
     *
     * @param mixed $arrData
     */
    public function setArrData($arrData)
    {
        foreach ($arrData as $index => &$item) {
            if (StringUtils::strIsSet($item, '') !== '') {
                $this->arrData[$index] = json_decode(StringUtils::htmlSpecialCharsStripSlashes($item), true);
            }
        }
    }

    /**
     * 배열로 넘어온 데이터를 기존 데이터의 값에 합산하는 함수
     *
     * @param $arrData
     */
    public function addArrData($arrData)
    {
        foreach ($arrData as $index => &$item) {
            if (StringUtils::strIsSet($item, '') !== '') {
                $arrItem = json_decode(StringUtils::htmlSpecialCharsStripSlashes($item), true);
                $this->arrData[$index]['total'] += $arrItem['total'];
                $this->arrData[$index]['pc'] += $arrItem['pc'];
                $this->arrData[$index]['mobile'] += $arrItem['mobile'];
            }
        }
    }

    /**
     * pc회원비율
     *
     * @param $day
     * @param $rate
     */
    public function setPcRate($day, $rate)
    {
        $this->arrData[$day]['pcRate'] = number_format($rate);
    }

    /**
     * 모바일회원비율
     *
     * @param $day
     * @param $rate
     */
    public function setMobileRate($day, $rate)
    {
        $this->arrData[$day]['mobileRate'] = number_format($rate);
    }

    /**
     * getMobileRate
     *
     * @param $day
     *
     * @return mixed
     */
    public function getMobileRate($day)
    {
        return $this->arrData[$day]['mobileRate'];
    }

    /**
     * @return mixed
     */
    public function getDateFormat()
    {
        return $this->dateFormat;
    }

    /**
     * @param mixed $dateFormat
     */
    public function setDateFormat($dateFormat)
    {
        $this->dateFormat = $dateFormat;
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
     * @return \DateTime[] 조회기간
     */
    public function getSearchDt()
    {
        return $this->searchDt;
    }

    /**
     * @param \DateTime[] $searchDt 조회기간
     */
    public function setSearchDt($searchDt)
    {
        $this->searchDt = $searchDt;
    }
}
