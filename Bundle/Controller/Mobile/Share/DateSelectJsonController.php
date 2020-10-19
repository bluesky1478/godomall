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
namespace Bundle\Controller\Mobile\Share;

use Request;

/**
 * 생일, 결혼기념일 일자 변경 AJAX
 *
 * @author KimYeonKyung
 * @version 1.0
 * @since 1.0
 * @copyright Copyright (c), Godosoft
 */
class DateSelectJsonController extends \Controller\Mobile\Controller
{
    public function index()
    {
        //--- 각 배열을 trim 처리
        $postValue = Request::post()->toArray();

        if (gd_isset($postValue)) {
            $year = $postValue['year'];
            $month = $postValue['month'];
            $days = [31,28,31,30,31,30,31,31,30,31,30,31]; // 월별 일수
            if ( ( $year%4==0 && $year%100!=0 ) || $year%400==0 ) {
                //윤달 2월 29일
                $days[1]++;
            }
            $daysOption = '<option value="" selected>일</option>';
            for($i=1; $i<=$days[$month-1]; $i++) {
                $fixFrontDay = '';
                if ($i < 10) {
                    $fixFrontDay = 0;
                }
                $optionValue = $fixFrontDay.$i;
                $daysOption .= '<option value="'.$optionValue.'">'.$optionValue.'</option>';
            }

            if (!empty($days[$month-1])) {
                echo $daysOption;
            }
        }
        exit;
    }
}