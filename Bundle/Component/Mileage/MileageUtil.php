<?php
/**
 * *
 *  * This is commercial software, only users who have purchased a valid license
 *  * and accept to the terms of the License Agreement can install and use this
 *  * program.
 *  *
 *  * Do not edit or add to this file if you wish to upgrade Godomall5 to newer
 *  * versions in the future.
 *  *
 *  * @copyright ⓒ 2016, NHN godo: Corp.
 *  * @link http://www.godo.co.kr
 *
 */

namespace Bundle\Component\Mileage;


use App;
use Framework\Utility\DateTimeUtils;

/**
 * Class 마일리지 유틸리티 클래스
 * @package Bundle\Component\Member\Util
 * @author  yjwee
 */
class MileageUtil
{
    /**
     * 마일리지 소멸 예정일 반환 함수
     *
     * @static
     * @return string
     */
    public static function getDeleteScheduleDate()
    {
        $mileageConfig = gd_policy('member.mileageBasic');
        if ($mileageConfig['expiryFl'] === 'n') {
            return '9999-12-31 00:00:00';
        } else {
            $expiryDays = $mileageConfig['expiryDays'];

            return DateTimeUtils::dateFormat('Y-m-d G:i:s', '+' . $expiryDays . ' day');
        }
    }

    /**
     * 회원별 사용한 마일리지 반환
     *
     * @param $memNo
     *
     * @return mixed
     */
    public static function getMemberSumUsedMileage($memNo)
    {
        $db = App::load('DB');
        $arrBind = [];
        $db->strField = "SUM(mileage) AS mileage";
        $db->strJoin = DB_MEMBER_MILEAGE;
        $db->strWhere = " memNo=? AND handleMode='o' ";
        $db->bind_param_push($arrBind, 'i', $memNo);

        $query = $db->query_complete();
        $strSQL = "SELECT " . array_shift($query) . " FROM " . array_shift($query) . " " . implode(" ", $query);
        $usedMileage = gd_htmlspecialchars_stripslashes($db->query_fetch($strSQL, $arrBind, false));
        unset($arrBind);

        return $usedMileage['mileage'];
    }

    public static function getExpireBeforeDate(\DateTime $dateTime = null)
    {
        $policy = gd_policy('member.mileageBasic');

        if ($dateTime === null) {
            $dateTime = new \DateTime();
        }

        return $dateTime->modify('+' . $policy['expiryBeforeDays'] . 'days')->format('Y-m-d');
    }


    public static function removeUseHistory($mileage, $history)
    {
        $decode = json_decode($history, true);
        if (isset($decode['totalUseMileage'])) {
            $mileage = $mileage - $decode['totalUseMileage'];
        }

        return $mileage;
    }

    /**
     * 지급/차감 사유 배열
     * code 에 저장되지 않은 시스템에서 처리되는 사유를 포함하여 반환한다.
     *
     * @static
     * @return array
     */
    public static function getReasons()
    {
        $reasonCodeGroup = gd_code(Mileage::REASON_CODE_GROUP);
        $expireReason = [
            Mileage::REASON_CODE_GROUP . Mileage::REASON_CODE_EXPIRE       => Mileage::REASON_TEXT_EXPIRE,
            Mileage::REASON_CODE_GROUP . Mileage::REASON_CODE_MEMBER_WAKE  => Mileage::REASON_TEXT_MEMBER_WAKE,
            Mileage::REASON_CODE_GROUP . Mileage::REASON_CODE_MEMBER_SLEEP => Mileage::REASON_TEXT_MEMBER_SLEEP,
        ];
        $reasonCodeGroup = array_slice($reasonCodeGroup, 0, 9, true) + $expireReason + array_slice($reasonCodeGroup, 9, count($reasonCodeGroup) - 3, true);

        return $reasonCodeGroup;
    }

    /**
     * 마일리지 소멸예정일 변경 (저장된 소멸예정일의 하루 전 23:59:59로 변경)
     *
     * @param mixed $data
     * @param bool  $timeFl
     *
     * @return mixed
     */
    public static function changeDeleteScheduleDt($data, $timeFl = false)
    {
        $addTime = ' 23:59:59';
        if (is_string($data) && $data != '0000-00-00 00:00:00' && $data != '9999-12-31 00:00:00') {
            $data = DateTimeUtils::dateFormatByParameter('Y-m-d', $data, '-1 day');
            if ($timeFl) {
                $data .= $addTime;
            }
        } else if (is_array($data) && count($data) > 0 ) {
            foreach ($data as $mKey => $mVal) {
                if (empty($mVal['deleteScheduleDt']) == false && $mVal['deleteScheduleDt'] != '0000-00-00 00:00:00' && $mVal['deleteScheduleDt'] != '9999-12-31 00:00:00') {
                    $data[$mKey]['deleteScheduleDt'] = DateTimeUtils::dateFormatByParameter('Y-m-d', $mVal['deleteScheduleDt'], '-1 day');
                    if ($timeFl) {
                        $data[$mKey]['deleteScheduleDt'] .= $addTime;
                    }
                }
            }
        }

        return $data;
    }
}
