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
use Logger;

/**
 * Class 일별 신규회원 데이터를 담는 클래스
 * @package Bundle\Component\MemberStatistics
 * @author  yjwee
 */
class JoinDayVO extends \Component\MemberStatistics\JoinVO
{
    /**
     * JoinStatisticsVO 객체 데이터를 이용하여 PC,모바일,총계 가입자 수를 산정.
     * 최대 신규회원 가입 일시, 최소 신규회원 가입 일시를 구분.
     * 일별 데이터를 배열에 저장.
     *
     * @param JoinStatisticsVO $vo
     */
    public function setJoinStatisticsVo(JoinStatisticsVO $vo)
    {
        $this->joinStatisticsVo = $vo;

        try {
            /**
             * DateTime[] $searchDt
             */
            $searchDt = $vo->getSearchDt();
            foreach ($vo->getArrData() as $index => $item) {
                $indexToDay = str_pad($index, 2, '0', STR_PAD_LEFT);
                $dataDt = new DateTime($vo->getJoinDate() . $indexToDay);
                if ($searchDt[0] <= $dataDt && $searchDt[1] >= $dataDt) {
                    $this->total += $item['total'];
                    $this->pcTotal += $item['pc'];
                    $this->mobileTotal += $item['mobile'];

                    $this->setArrData($vo->getJoinDate() . $indexToDay, $item);
                    if ($this->setMax($item['total'])) {
                        $joinDt = $vo->getJoinDate();
                        $dt = new DateTime();
                        $dt->setDate(substr($joinDt, 0, 4), substr($joinDt, 4, 6), $indexToDay);
                        $this->setMaxDt($dt);
                    }
                    if ($this->setMin($item['total'])) {
                        $joinDt = $vo->getJoinDate();
                        $dt = new DateTime();
                        $dt->setDate(substr($joinDt, 0, 4), substr($joinDt, 4, 6), $indexToDay);
                        $this->setMinDt($dt);
                    }
                }
            }
        } catch (Exception $e) {
            Logger::info($e->getMessage() . ', ' . $e->getFile() . ', ' . $e->getLine());
            $this->initArrData();
        }
    }

    /**
     * 일별 데이터를 monday~sunday  하여 모든 데이터를 0으로 초기화하는 함수
     */
    public function initArrData()
    {
        for ($i = 1; $i < 31; $i++) {
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
     * @param $day
     * @param $arrData
     */
    public function setArrData($day, array $arrData)
    {
        if (empty($this->arrData[$day])) {
            $this->arrData[$day] = $arrData;
        } else {
            $this->arrData[$day]['total'] += $arrData['total'];
            $this->arrData[$day]['pc'] += $arrData['pc'];
            $this->arrData[$day]['mobile'] += $arrData['mobile'];
        }
    }

    /**
     * $arrData 변수에 설정된 일별 가입 데이터를 기준으로
     * 일별 pc,모바일 가입 비율을 계산하여 추가한다.
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
