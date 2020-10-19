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
class JoinMonthVO extends \Component\MemberStatistics\JoinVO
{

    /**
     * JoinStatisticsVO 객체 데이터를 이용하여 PC,모바일,총계 가입자 수를 산정.
     * 최대 신규회원 가입 일시, 최소 신규회원 가입 일시를 구분.
     * 월별 데이터를 배열에 저장.
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

                $dataDt = new DateTime();
                $dataDt = $dataDt->setDate($vo->getJoinDate(), $index, 15);
                $searchDt[0]->setDate($searchDt[0]->format('Y'), $searchDt[0]->format('m'), 1);
                $searchDt[1]->setDate($searchDt[1]->format('Y'), $searchDt[1]->format('m'), 31);
                /*
                 * 검색기간 내의 데이터인지 체크
                 */
                if ($searchDt[0] <= $dataDt && $searchDt[1] >= $dataDt) {
                    $this->total += $item['total'];
                    $this->pcTotal += $item['pc'];
                    $this->mobileTotal += $item['mobile'];

                    $this->setArrData($dataDt->format('Ym'), $item);
                    if ($this->setMax($item['total'])) {
                        $dt = new DateTime();
                        $dt->setDate($vo->getJoinDate(), $index, 1);
                        $this->setMaxDt($dt);
                    }
                    if ($this->setMin($item['total'])) {
                        $dt = new DateTime();
                        $dt->setDate($vo->getJoinDate(), $index, 1);
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
     * 월별 데이터를 monday~sunday  하여 모든 데이터를 0으로 초기화하는 함수
     */
    public function initArrData()
    {
        for ($i = 1; $i < 13; $i++) {
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
     * @param string $month
     * @param array $arrData
     */
    public function setArrData($month, array $arrData)
    {
        if (empty($this->arrData[$month])) {
            $this->arrData[$month] = $arrData;
        } else {
            $this->arrData[$month]['total'] += $arrData['total'];
            $this->arrData[$month]['pc'] += $arrData['pc'];
            $this->arrData[$month]['mobile'] += $arrData['mobile'];
        }
    }

    /**
     * $arrData 변수에 설정된 월별 가입 데이터를 기준으로
     * 월별 pc,모바일 가입 비율을 계산하여 추가한다.
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
