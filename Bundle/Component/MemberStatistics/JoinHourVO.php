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
use Framework\Utility\DateTimeUtils;
use Framework\Utility\StringUtils;
use Logger;

/**
 * Class 시간별 신규회원 데이터를 담는 클래스
 * @package Bundle\Component\MemberStatistics
 * @author  yjwee
 */
class JoinHourVO extends \Component\MemberStatistics\JoinVO
{

    /**
     * JoinStatisticsVO 객체 데이터를 이용하여 PC,모바일,총계 가입자 수를 산정.
     * 최대 신규회원 가입 일시, 최소 신규회원 가입 일시를 구분.
     * 시간대별 데이터를 배열에 저장.
     *
     * @param JoinStatisticsVO $joinStatisticsVo
     */
    public function setJoinStatisticsVo(JoinStatisticsVO $joinStatisticsVo)
    {
        $this->joinStatisticsVo = $joinStatisticsVo;
        try {
            foreach ($joinStatisticsVo->getArrData() as $hour => $item) {
                $this->total += $item['total'];
                $this->pcTotal += $item['pc'];
                $this->mobileTotal += $item['mobile'];

                $this->setArrData($hour, $item);
                if ($this->setMax($item['total'])) {
                    $dt = new DateTime($joinStatisticsVo->getJoinDate('Y-m-d'), DateTimeUtils::getTimeZone());
                    $dt->setTime($hour, '00');
                    $this->setMaxDt($dt);
                }
                if ($this->setMin($item['total'])) {
                    $dt = new DateTime($joinStatisticsVo->getJoinDate('Y-m-d'), DateTimeUtils::getTimeZone());
                    $dt->setTime($hour, '00');
                    $this->setMinDt($dt);
                }
            }
        } catch (Exception $e) {
            Logger::info($e->getMessage() . ', ' . $e->getFile() . ', ' . $e->getLine());
            $this->initArrData();
        }
    }

    /**
     * 시간별 데이터를 0~23시로 하여 모든 데이터를 0으로 초기화하는 함수
     */
    public function initArrData()
    {
        $length = 24;
        for ($i = 0; $i < $length; $i++) {
            if (count($this->arrData[$i]) > 0) {
                continue;
            }
            $this->arrData[$i] = [
                'total'      => 0,
                'pc'         => 0,
                'mobile'     => 0,
                'pcRate'     => 0,
                'mobileRate' => 0,
            ];
        }
    }

    /**
     * @return mixed
     */
    public function getArrData()
    {
        if (count($this->arrData) < 1) {
            return [];
        }
        ksort($this->arrData);

        return $this->arrData;
    }

    /**
     * setArrData
     *
     * @param string $hour
     * @param array  $arrData
     */
    public function setArrData($hour, array $arrData)
    {
        if (empty($this->arrData[$hour])) {
            $this->arrData[$hour] = $arrData;
        } else {
            $this->arrData[$hour]['total'] += $arrData['total'];
            $this->arrData[$hour]['pc'] += $arrData['pc'];
            $this->arrData[$hour]['mobile'] += $arrData['mobile'];
        }
    }

    /**
     * $arrData 변수에 설정된 시간대별 가입 데이터를 기준으로
     * 시간대별 pc,모바일 가입 비율을 계산하여 추가한다.
     */
    public function calculateRate()
    {
        if (count($this->arrData) > 0) {
            foreach ($this->arrData as $index => &$item) {
                $total = StringUtils::strIsSet($item['total'], 1);
                if ($total < 1) {
                    $total = 1;
                }
                $pc = StringUtils::strIsSet($item['pc'], 0);
                $mobile = StringUtils::strIsSet($item['mobile'], 0);
                $pcRate = ($pc / $total) * 100;
                $item['pcRate'] = $pcRate;
                $mobileRate = ($mobile / $total) * 100;
                $item['mobileRate'] = $mobileRate;
            }
        }
    }
}
