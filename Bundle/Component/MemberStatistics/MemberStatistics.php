<?php

/**
 * 회원분석(Member Statistics) Class
 *
 * @author    su
 * @version   1.0
 * @since     1.0
 * @copyright ⓒ 2016, NHN godo: Corp.
 */
namespace Bundle\Component\MemberStatistics;

use DateTime;
use Framework\Utility\DateTimeUtils;

class MemberStatistics
{
    protected $db;
    protected $memberPolicy;         // 회원통계 기본 설정 ( 회원 가입승인일, 승인된 회원 )

    /**
     * MemberStatistics constructor.
     *
     * @param null $date Y-m-d
     */
    public function __construct($date = null)
    {
        if (!is_object($this->db)) {
            $this->db = \App::load('DB');
        }
        // 회원 가입 승인 일 - 통계 처리 날짜
        if ($date) {
            $submitDate = new DateTime($date);
            $this->memberPolicy['statisticsDate'] = $submitDate->modify('-1 day');
        } else {
            $submitDate = new DateTime();
            $this->memberPolicy['statisticsDate'] = $submitDate->modify('-1 day');
        }
        // 승인된 회원
        $this->memberPolicy['appFl'] = 'y';
    }

    /**
     * getMemberInfo
     * 회원 정보 출력
     *
     * @param array       $member      mallSno / appFl / approvalDay / approvalMonth
     * @param string      $memberField 출력할 필드명 (기본 null)
     * @param array       $arrBind     bind 처리 배열 (기본 null)
     * @param bool|string $dataArray   return 값을 배열처리 (기본값 false)
     *
     * @return array 회원 정보
     *
     * @author su
     */
    public function getMemberInfo($member = null, $memberField = null, $arrBind = null, $dataArray = false)
    {
        if (is_null($arrBind)) {
            // $arrBind = array();
        }

        if (isset($member['mallSno'])) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND m.mallSno = ? ';
                $this->db->bind_param_push($arrBind, 'i', $member['mallSno']);
            } else {
                $this->db->strWhere = ' m.mallSno = ? ';
                $this->db->bind_param_push($arrBind, 'i', $member['mallSno']);
            }
        }
        if (isset($member['appFl'])) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND m.appFl = ? ';
                $this->db->bind_param_push($arrBind, 's', $member['appFl']);
            } else {
                $this->db->strWhere = ' m.appFl = ? ';
                $this->db->bind_param_push($arrBind, 's', $member['appFl']);
            }
        }
        if (isset($member['approvalDay'])) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND DATE_FORMAT(m.approvalDt, "%Y%m%d") = ? ';
                $this->db->bind_param_push($arrBind, 's', $member['approvalDay']);
            } else {
                $this->db->strWhere = ' DATE_FORMAT(m.approvalDt, "%Y%m%d") = ? ';
                $this->db->bind_param_push($arrBind, 's', $member['approvalDay']);
            }
        }
        if (isset($member['approvalTotal'])) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND DATE_FORMAT(m.approvalDt, "%Y%m%d") <= ? ';
                $this->db->bind_param_push($arrBind, 's', $member['approvalTotal']);
            } else {
                $this->db->strWhere = ' DATE_FORMAT(m.approvalDt, "%Y%m%d") <= ? ';
                $this->db->bind_param_push($arrBind, 's', $member['approvalTotal']);
            }
        }
        if (isset($member['approvalMonth'])) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND DATE_FORMAT(m.approvalDt, "%Y%m") = ? ';
                $this->db->bind_param_push($arrBind, 's', $member['approvalMonth']);
            } else {
                $this->db->strWhere = ' DATE_FORMAT(m.approvalDt, "%Y%m") = ? ';
                $this->db->bind_param_push($arrBind, 's', $member['approvalMonth']);
            }
        }
        if ($memberField) {
            $this->db->strField = $memberField;
        }
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER . ' as m ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);

        if (count($getData) == 1 && $dataArray === false) {
            return gd_htmlspecialchars_stripslashes($getData[0]);
        }

        return gd_htmlspecialchars_stripslashes($getData);
    }

    /**
     * getMemberHackOutInfo
     * 탈퇴 회원 정보 출력
     *
     * @param array       $member      mallSno / appFl / approvalDay / approvalMonth
     * @param string      $memberField 출력할 필드명 (기본 null)
     * @param array       $arrBind     bind 처리 배열 (기본 null)
     * @param bool|string $dataArray   return 값을 배열처리 (기본값 false)
     *
     * @return array 회원 정보
     *
     * @author su
     */
    public function getMemberHackOutInfo($member = null, $memberField = null, $arrBind = null, $dataArray = false)
    {
        if (is_null($arrBind)) {
            // $arrBind = array();
        }

        if (isset($member['mallSno'])) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND mh.mallSno = ? ';
                $this->db->bind_param_push($arrBind, 'i', $member['mallSno']);
            } else {
                $this->db->strWhere = ' mh.mallSno = ? ';
                $this->db->bind_param_push($arrBind, 'i', $member['mallSno']);
            }
        }
        if (isset($member['hackDt'])) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND DATE_FORMAT(mh.hackDt, "%Y%m%d") = ? ';
                $this->db->bind_param_push($arrBind, 's', $member['hackDt']);
            } else {
                $this->db->strWhere = ' DATE_FORMAT(mh.hackDt, "%Y%m%d") = ? ';
                $this->db->bind_param_push($arrBind, 's', $member['hackDt']);
            }
        }
        if ($memberField) {
            $this->db->strField = $memberField;
        }
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER_HACKOUT . ' as mh ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);

        if (count($getData) == 1 && $dataArray === false) {
            return gd_htmlspecialchars_stripslashes($getData[0]);
        }

        return gd_htmlspecialchars_stripslashes($getData);
    }

    /**
     * getMemberSleepInfo
     * 휴면 회원 정보 출력
     *
     * @param array       $member      mallSno / appFl / approvalDay / approvalMonth
     * @param string      $memberField 출력할 필드명 (기본 null)
     * @param array       $arrBind     bind 처리 배열 (기본 null)
     * @param bool|string $dataArray   return 값을 배열처리 (기본값 false)
     *
     * @return array 회원 정보
     *
     * @author su
     */
    public function getMemberSleepInfo($member = null, $memberField = null, $arrBind = null, $dataArray = false)
    {
        if (is_null($arrBind)) {
            // $arrBind = array();
        }

        if (isset($member['mallSno'])) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND m.mallSno = ? ';
                $this->db->bind_param_push($arrBind, 'i', $member['mallSno']);
            } else {
                $this->db->strWhere = ' m.mallSno = ? ';
                $this->db->bind_param_push($arrBind, 'i', $member['mallSno']);
            }
        }
        if (isset($member['sleepDt'])) {
            $sleepDate = DateTimeUtils::dateFormat('Y-m-d', $member['sleepDt']);
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND ms.sleepDt BETWEEN ? AND ?';
                $this->db->bind_param_push($arrBind, 's', $sleepDate . ' 00:00:00');
                $this->db->bind_param_push($arrBind, 's', $sleepDate . ' 23:59:59');
            } else {
                $this->db->strWhere = ' ms.sleepDt BETWEEN ? AND ?';
                $this->db->bind_param_push($arrBind, 's', $sleepDate . ' 00:00:00');
                $this->db->bind_param_push($arrBind, 's', $sleepDate . ' 23:59:59');
            }
        }
        if ($memberField) {
            $this->db->strField = $memberField;
        }
        $this->db->strJoin = 'LEFT JOIN ' . DB_MEMBER . ' as m ON ms.memNo = m.memNo';
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER_SLEEP . ' as ms ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);

        if (count($getData) == 1 && $dataArray === false) {
            return gd_htmlspecialchars_stripslashes($getData[0]);
        }

        return gd_htmlspecialchars_stripslashes($getData);
    }

    /**
     * getMemberMileageInfo
     * 회원 정보 출력
     *
     * @param array       $member      regDay / regMonth
     * @param string      $memberField 출력할 필드명 (기본 null)
     * @param array       $arrBind     bind 처리 배열 (기본 null)
     * @param bool|string $dataArray   return 값을 배열처리 (기본값 false)
     *
     * @return array 회원 정보
     *
     * @author su
     */
    public function getMemberMileageInfo($member = null, $memberField = null, $arrBind = null, $dataArray = false)
    {
        if (is_null($arrBind)) {
            // $arrBind = array();
        }
        if (isset($member['regDt'])) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND DATE_FORMAT(mm.regDt, "%Y%m%d") = ? ';
                $this->db->bind_param_push($arrBind, 's', $member['regDt']);
            } else {
                $this->db->strWhere = ' DATE_FORMAT(mm.regDt, "%Y%m%d") = ? ';
                $this->db->bind_param_push($arrBind, 's', $member['regDt']);
            }
        }
        if (isset($member['regDtLimit'])) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND DATE_FORMAT(mm.regDt, "%Y%m%d") <= ? ';
                $this->db->bind_param_push($arrBind, 's', $member['regDtLimit']);
            } else {
                $this->db->strWhere = ' DATE_FORMAT(mm.regDt, "%Y%m%d") <= ? ';
                $this->db->bind_param_push($arrBind, 's', $member['regDtLimit']);
            }
        }
        if (isset($member['regMonth'])) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND DATE_FORMAT(mm.regDt, "%Y%m") = ? ';
                $this->db->bind_param_push($arrBind, 's', $member['regMonth']);
            } else {
                $this->db->strWhere = ' DATE_FORMAT(mm.regDt, "%Y%m") = ? ';
                $this->db->bind_param_push($arrBind, 's', $member['regMonth']);
            }
        }
        if ($memberField) {
            $this->db->strField = $memberField;
        }
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER_MILEAGE . ' as mm ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);

        if (count($getData) == 1 && $dataArray === false) {
            return gd_htmlspecialchars_stripslashes($getData[0]);
        }

        return gd_htmlspecialchars_stripslashes($getData);
    }

    /**
     * getMemberDepositInfo
     * 회원 정보 출력
     *
     * @param array       $member      regDay / regMonth
     * @param string      $memberField 출력할 필드명 (기본 null)
     * @param array       $arrBind     bind 처리 배열 (기본 null)
     * @param bool|string $dataArray   return 값을 배열처리 (기본값 false)
     *
     * @return array 회원 정보
     *
     * @author su
     */
    public function getMemberDepositInfo($member = null, $memberField = null, $arrBind = null, $dataArray = false)
    {
        if (is_null($arrBind)) {
            // $arrBind = array();
        }
        if (isset($member['regDt'])) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND DATE_FORMAT(md.regDt, "%Y%m%d") = ? ';
                $this->db->bind_param_push($arrBind, 's', $member['regDt']);
            } else {
                $this->db->strWhere = ' DATE_FORMAT(md.regDt, "%Y%m%d") = ? ';
                $this->db->bind_param_push($arrBind, 's', $member['regDt']);
            }
        }
        if (isset($member['regDtLimit'])) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND DATE_FORMAT(md.regDt, "%Y%m%d") <= ? ';
                $this->db->bind_param_push($arrBind, 's', $member['regDtLimit']);
            } else {
                $this->db->strWhere = ' DATE_FORMAT(md.regDt, "%Y%m%d") <= ? ';
                $this->db->bind_param_push($arrBind, 's', $member['regDtLimit']);
            }
        }
        if (isset($member['regMonth'])) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND DATE_FORMAT(md.regDt, "%Y%m") = ? ';
                $this->db->bind_param_push($arrBind, 's', $member['regMonth']);
            } else {
                $this->db->strWhere = ' DATE_FORMAT(md.regDt, "%Y%m") = ? ';
                $this->db->bind_param_push($arrBind, 's', $member['regMonth']);
            }
        }
        if ($memberField) {
            $this->db->strField = $memberField;
        }
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER_DEPOSIT . ' as md ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);

        if (count($getData) == 1 && $dataArray === false) {
            return gd_htmlspecialchars_stripslashes($getData[0]);
        }

        return gd_htmlspecialchars_stripslashes($getData);
    }

    /**
     * getMemberDayStatisticsInfo
     * 회원 일자별 통계정보 출력
     *
     * @param array       $memberDay            memberYM / mallSno
     * @param string      $memberDayField       출력할 필드명 (기본 null)
     * @param array       $arrBind              bind 처리 배열 (기본 null)
     * @param bool|string $dataArray            return 값을 배열처리 (기본값 false)
     *
     * @return array 회원 정보
     *
     * @author su
     */
    public function getMemberDayStatisticsInfo($memberDay = null, $memberDayField = null, $arrBind = null, $dataArray = false,$isGenerator = false)
    {
        if (is_null($arrBind)) {
            // $arrBind = array();
        }
        if (is_array($memberDay['memberYM'])) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND msd.memberYm BETWEEN ? AND ? ';
                $this->db->bind_param_push($arrBind, 'i', $memberDay['memberYM'][0]);
                $this->db->bind_param_push($arrBind, 'i', $memberDay['memberYM'][1]);
            } else {
                $this->db->strWhere = ' msd.memberYm BETWEEN ? AND ? ';
                $this->db->bind_param_push($arrBind, 'i', $memberDay['memberYM'][0]);
                $this->db->bind_param_push($arrBind, 'i', $memberDay['memberYM'][1]);
            }
        } else {
            if ($memberDay['memberYM']) {
                if ($this->db->strWhere) {
                    $this->db->strWhere = $this->db->strWhere . ' AND msd.memberYm = ? ';
                    $this->db->bind_param_push($arrBind, 'i', $memberDay['memberYM']);
                } else {
                    $this->db->strWhere = ' msd.memberYm = ? ';
                    $this->db->bind_param_push($arrBind, 'i', $memberDay['memberYM']);
                }
            }
        }
        if (isset($memberDay['mallSno'])) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND msd.mallSno = ? ';
                $this->db->bind_param_push($arrBind, 'i', $memberDay['mallSno']);
            } else {
                $this->db->strWhere = ' msd.mallSno = ? ';
                $this->db->bind_param_push($arrBind, 'i', $memberDay['mallSno']);
            }
        }
        if ($memberDayField) {
            $this->db->strField = $memberDayField;
        }
        $query = $this->db->query_complete();

        if($isGenerator) {

            $strCountSQL = 'SELECT count(memberYM) as cnt FROM ' . DB_MEMBER_DAY . ' as msd '.$query['where'];
            $totalNum = $this->db->query_fetch($strCountSQL, $arrBind,false)['cnt'];

            return $this->getMemberDayStatisticsInfoGenerator($totalNum,$query,$arrBind);

        } else {
            $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER_DAY . ' as msd ' . implode(' ', $query);

            $getData = $this->db->query_fetch($strSQL, $arrBind);

            if (count($getData) == 1 && $dataArray === false) {
                return $getData[0];
            }

            return $getData;
        }
    }

    /**
     * getMemberDayStatisticsInfoGenerator
     * getMemberDayStatisticsInfo 에서 generator 사용하여 생성시 사용
     *
     * @param string $totalNum 총 갯수
     * @param string $query 쿼리문
     * @param array $arrBind bind 처리 배열 (기본 null)
     *
     * @return generator object
     *
     * @author su
     */
    public function getMemberDayStatisticsInfoGenerator($totalNum,$query,$arrBind) {
        $pageLimit = "10000";

        if ($pageLimit >= $totalNum) $pageNum = 0;
        else $pageNum = ceil($totalNum / $pageLimit) - 1;

        $strField =   array_shift($query);
        for ($i = 0; $i <= $pageNum; $i++) {
            $strLimit = " LIMIT ".(($i * $pageLimit)) . "," . $pageLimit;
            $strSQL = 'SELECT ' . $strField . ' FROM ' . DB_MEMBER_DAY . ' as msd ' . implode(' ', $query).$strLimit;
            $tmpData =  $this->db->query_fetch_generator($strSQL, $arrBind);
            foreach($tmpData as $k => $v) {
                yield $v;
            }
            unset($tmpData);
        }
    }

    /**
     * getMemberDevice
     * 회원정보를 디바이스별로 추출
     *
     * @param $deviceMemberArr
     *
     * @return mixed
     */
    public function getMemberDevice($deviceMemberArr)
    {
        $member['pc'] = count(array_keys($deviceMemberArr, 'pc'));
        $member['mobile'] = count(array_keys($deviceMemberArr, 'mobile'));
        $deviceSum = array_sum($member);
        $member['etc'] = count($deviceMemberArr) - $deviceSum;

        return $member;
    }

    /**
     * getMemberGender
     * 회원정보를 성별로 추출
     *
     * @param $genderMemberArr
     *
     * @return mixed
     */
    public function getMemberGender($genderMemberArr)
    {
        $member['male'] = count(array_keys($genderMemberArr, 'm'));
        $member['female'] = count(array_keys($genderMemberArr, 'w'));
        $genderSum = array_sum($member);
        $member['etc'] = count($genderMemberArr) - $genderSum;

        return $member;
    }

    /**
     * getMemberAge
     * 회원정보를 연령별로 추출
     *
     * @param $ageMemberArr
     *
     * @return mixed
     */
    public function getMemberAge($ageMemberArr)
    {
        // 10살 미만도 10대로
        $member['10'] = count(array_keys($ageMemberArr, '1')) + count(array_keys($ageMemberArr, '0'));
        $member['20'] = count(array_keys($ageMemberArr, '2'));
        $member['30'] = count(array_keys($ageMemberArr, '3'));
        $member['40'] = count(array_keys($ageMemberArr, '4'));
        $member['50'] = count(array_keys($ageMemberArr, '5'));
        $member['60'] = count(array_keys($ageMemberArr, '6'));
        // 80, 90, 100살도 70대로
        $member['70'] = count(array_keys($ageMemberArr, '7')) + count(array_keys($ageMemberArr, '8')) + count(array_keys($ageMemberArr, '9')) + count(array_keys($ageMemberArr, '10'));
        $ageSum = array_sum($member);
        $member['etc'] = count($ageMemberArr) - $ageSum;

        return $member;
    }

    /**
     * getMemberArea
     * 회원정보를 지역별로 추출
     *
     * @param $areaMemberArr
     *
     * @return mixed
     */
    public function getMemberArea($areaMemberArr)
    {
        $member['강원'] = count(array_keys($areaMemberArr, '강원도')) + count(array_keys($areaMemberArr, '강원'));
        $member['경기'] = count(array_keys($areaMemberArr, '경기도')) + count(array_keys($areaMemberArr, '경기'));
        $member['경남'] = count(array_keys($areaMemberArr, '경상남도')) + count(array_keys($areaMemberArr, '경남'));
        $member['경북'] = count(array_keys($areaMemberArr, '경상북도')) + count(array_keys($areaMemberArr, '경북'));
        $member['광주'] = count(array_keys($areaMemberArr, '광주광역시')) + count(array_keys($areaMemberArr, '광주'));
        $member['대구'] = count(array_keys($areaMemberArr, '대구광역시')) + count(array_keys($areaMemberArr, '대구'));
        $member['대전'] = count(array_keys($areaMemberArr, '대전광역시')) + count(array_keys($areaMemberArr, '대전'));
        $member['부산'] = count(array_keys($areaMemberArr, '부산광역시')) + count(array_keys($areaMemberArr, '부산'));
        $member['서울'] = count(array_keys($areaMemberArr, '서울특별시')) + count(array_keys($areaMemberArr, '서울'));
        $member['세종'] = count(array_keys($areaMemberArr, '세종특별자치시')) + count(array_keys($areaMemberArr, '세종'));
        $member['울산'] = count(array_keys($areaMemberArr, '울산광역시')) + count(array_keys($areaMemberArr, '울산'));
        $member['인천'] = count(array_keys($areaMemberArr, '인천광역시')) + count(array_keys($areaMemberArr, '인천'));
        $member['전남'] = count(array_keys($areaMemberArr, '전라남도')) + count(array_keys($areaMemberArr, '전남'));
        $member['전북'] = count(array_keys($areaMemberArr, '전라북도')) + count(array_keys($areaMemberArr, '전북'));
        $member['제주'] = count(array_keys($areaMemberArr, '제주특별자치도')) + count(array_keys($areaMemberArr, '제주'));
        $member['충남'] = count(array_keys($areaMemberArr, '충청남도')) + count(array_keys($areaMemberArr, '충남'));
        $member['충북'] = count(array_keys($areaMemberArr, '충청북도')) + count(array_keys($areaMemberArr, '충북'));
        $areaSum = array_sum($member);
        $member['etc'] = count($areaMemberArr) - $areaSum;

        return $member;
    }

    /**
     * getCityName
     *
     * @param $city4StringCutName
     * @return string
     */
    public function getCityName($city4StringCutName)
    {
        if (trim($city4StringCutName) == '강원도') {
            $area = '강원';
        } else if (trim($city4StringCutName) == '경기도') {
            $area = '경기';
        } else if (trim($city4StringCutName) == '경상남도') {
            $area = '경남';
        } else if (trim($city4StringCutName) == '경상북도') {
            $area = '경북';
        } else if (trim($city4StringCutName) == '광주광역') {
            $area = '광주';
        } else if (trim($city4StringCutName) == '대구광역') {
            $area = '대구';
        } else if (trim($city4StringCutName) == '대전광역') {
            $area = '대전';
        } else if (trim($city4StringCutName) == '부산광역') {
            $area = '부산';
        } else if (trim($city4StringCutName) == '서울특별') {
            $area = '서울';
        } else if (trim($city4StringCutName) == '세종특별') {
            $area = '세종';
        } else if (trim($city4StringCutName) == '울산광역') {
            $area = '울산';
        } else if (trim($city4StringCutName) == '인천광역') {
            $area = '인천';
        } else if (trim($city4StringCutName) == '전라남도') {
            $area = '전남';
        } else if (trim($city4StringCutName) == '전라북도') {
            $area = '전북';
        } else if (trim($city4StringCutName) == '제주특별') {
            $area = '제주';
        } else if (trim($city4StringCutName) == '충청남도') {
            $area = '충남';
        } else if (trim($city4StringCutName) == '충청북도') {
            $area = '충북';
        } else {
            $area = $city4StringCutName;
        }

        return $area;
    }

    /**
     * getMemberHour
     * 회원정보를 시간별로 추출
     *
     * @param $hourMemberArr
     *
     * @return mixed
     */
    public function getMemberHour($hourMemberArr)
    {
        for ($h = 0; $h < 24; $h++) {
            if ($h < 10) {
                $hour = '0' . $h;
            } else {
                $hour = $h;
            }
            $member[$hour] = count(array_keys($hourMemberArr, $hour));
        }
        $hourSum = array_sum($member);
        $member['etc'] = count($hourMemberArr) - $hourSum;

        return $member;
    }

    /**
     * getMemberWeek
     * 회원정보를 요일별로 추출
     *
     * @param $weekMemberArr
     *
     * @return mixed
     */
    public function getMemberWeek($weekMemberArr)
    {
        for ($w = 0; $w < 7; $w++) {
            $member[$w] = count(array_keys($weekMemberArr, $w));
        }
        $weekSum = array_sum($member);
        $member['etc'] = count($weekMemberArr) - $weekSum;

        return $member;
    }

    /**
     * getMemberNewDay
     * 신규 회원 분석 - 일별
     *
     * @param $searchDate
     * @param null $mallSno
     * @return array
     * @throws \Exception
     */
    public function getMemberNewDay($searchDate, $mallSno = null)
    {
        $sDate = new DateTime($searchDate[0]);
        $eDate = new DateTime($searchDate[1]);
        $dateDiff = date_diff($sDate, $eDate);
        if ($dateDiff->days > 90) {
            throw new \Exception(__('검색 가능 일은 최대 90일 입니다.'));
        }
        if ($searchDate[0] > $searchDate[1]) {
            throw new \Exception(__('시작일이 종료일보다 클 수 없습니다.'));
        }

        $memberYm[0] = substr($searchDate[0], 0, 6);
        $memberYm[1] = substr($searchDate[1], 0, 6);

        $memberDay['memberYM'] = $memberYm;
        if ($mallSno != 'all') {
            $memberDay['mallSno'] = $mallSno;
        }

        $getField[] = 'msd.memberYM';
        for ($i=1; $i<=31; $i++) {
            $getField[] = 'msd.' . $i . '->"$.new.device" AS `' . $i . '`';
        }
        $field = implode(', ', $getField);
        $getDataJson = $this->getMemberDayStatisticsInfo($memberDay, $field, '', true);

        foreach ($getDataJson as $key => $val) {
            // 시작일 앞자리 자르기
            if ($val['memberYM'] == $memberYm[0]) {
                $sDay = substr($searchDate[0], 6, 2);
                for ($i = 1; $i < $sDay; $i++) {
                    unset($getDataJson[$key][$i]);
                }
            }
            // 종료일 뒷자리 자르기
            if ($val['memberYM'] == $memberYm[1]) {
                $eDay = substr($searchDate[1], 6, 2);
                for ($i = 31; $i > $eDay; $i--) {
                    unset($getDataJson[$key][$i]);
                }
            }
        }

        $getDataArr = [];
        foreach ($getDataJson as $dataKey => $dataVal) {
            foreach ($dataVal as $key => $val) {
                if (is_numeric($key) && $val) {
                    $data = json_decode($val, true);
                    if ($mallSno != 'all') {
                        $getDataArr[$dataVal['memberYM'] . sprintf("%02d", $key)] = $data;
                    } else {
                        $getDataArr[$dataVal['memberYM'] . sprintf("%02d", $key)]['pc'] += $data['pc'];
                        $getDataArr[$dataVal['memberYM'] . sprintf("%02d", $key)]['mobile'] += $data['mobile'];
                        $getDataArr[$dataVal['memberYM'] . sprintf("%02d", $key)]['etc'] += $data['etc'];
                    }
                }
            }
        }

        $todayDate = new DateTime();
        // 오늘 검색에 따른 당일 통계
        if ($eDate->format('Ymd') >= $todayDate->format('Ymd')) {
            // 신규회원 통계
            $member['appFl'] = $this->memberPolicy['appFl'];
            $member['approvalDay'] = $todayDate->format('Ymd');
            if ($mallSno != 'all') {
                $member['mallSno'] = $mallSno;
            }
            $newMemberArr = $this->getMemberInfo($member, 'm.memNo, m.entryPath', null, true);
            unset($member);

            $getDataArr[$todayDate->format('Ymd')]['pc'] = 0;
            $getDataArr[$todayDate->format('Ymd')]['mobile'] = 0;
            $getDataArr[$todayDate->format('Ymd')]['etc'] = 0;
            foreach ($newMemberArr as $newKey => $newVal) {
                $getDataArr[$todayDate->format('Ymd')][$newVal['entryPath']]++;
            }
        }

        return $getDataArr;
    }

    /**
     * getMemberNewHour
     * 신규 회원 분석 - 시간별
     *
     * @param $searchDate
     * @param null $mallSno
     * @return array
     * @throws \Exception
     */
    public function getMemberNewHour($searchDate, $mallSno = null)
    {
        $sDate = new DateTime($searchDate[0]);
        $eDate = new DateTime($searchDate[1]);
        $dateDiff = date_diff($sDate, $eDate);
        if ($dateDiff->days > 90) {
            throw new \Exception(__('검색 가능 일은 최대 90일 입니다.'));
        }
        if ($searchDate[0] > $searchDate[1]) {
            throw new \Exception(__('시작일이 종료일보다 클 수 없습니다.'));
        }

        $memberYm[0] = substr($searchDate[0], 0, 6);
        $memberYm[1] = substr($searchDate[1], 0, 6);

        $memberDay['memberYM'] = $memberYm;
        if ($mallSno != 'all') {
            $memberDay['mallSno'] = $mallSno;
        }

        $getField[] = 'msd.memberYM';
        for ($i=1; $i<=31; $i++) {
            $getField[] = 'msd.' . $i . '->"$.new.hour" AS `' . $i . '`';
        }
        $field = implode(', ', $getField);
        $getDataJson = $this->getMemberDayStatisticsInfo($memberDay, $field, '', true);

        foreach ($getDataJson as $key => $val) {
            // 시작일 앞자리 자르기
            if ($val['memberYM'] == $memberYm[0]) {
                $sDay = substr($searchDate[0], 6, 2);
                for ($i = 1; $i < $sDay; $i++) {
                    unset($getDataJson[$key][$i]);
                }
            }
            // 종료일 뒷자리 자르기
            if ($val['memberYM'] == $memberYm[1]) {
                $eDay = substr($searchDate[1], 6, 2);
                for ($i = 31; $i > $eDay; $i--) {
                    unset($getDataJson[$key][$i]);
                }
            }
        }

        $getDataArr = [];
        foreach ($getDataJson as $dataKey => $dataVal) {
            foreach ($dataVal as $key => $val) {
                if (is_numeric($key) && $val) {
                    $data = json_decode($val, true);
                    foreach ($data as $dKey => $dVal) {
                        foreach ($dVal as $fKey => $fVal) {
                            $getDataArr[$dataVal['memberYM'] . sprintf("%02d", $key)][$dKey][$fKey] += $fVal;
                        }
                    }
                }
            }
        }

        $todayDate = new DateTime();
        // 오늘 검색에 따른 당일 통계
        if ($eDate->format('Ymd') >= $todayDate->format('Ymd')) {
            // 신규회원 통계
            $member['appFl'] = $this->memberPolicy['appFl'];
            $member['approvalDay'] = $todayDate->format('Ymd');
            if ($mallSno != 'all') {
                $member['mallSno'] = $mallSno;
            }
            $newMemberArr = $this->getMemberInfo($member, 'm.memNo, m.entryPath, DATE_FORMAT(m.approvalDt, "%H") as hour', null, true);
            unset($member);

            for ($i = 0; $i <= 23; $i++) {
                $hour = sprintf("%02d", $i);
                $getDataArr[$todayDate->format('Ymd')][$hour]['pc'] = 0;
                $getDataArr[$todayDate->format('Ymd')][$hour]['mobile'] = 0;
                $getDataArr[$todayDate->format('Ymd')][$hour]['etc'] = 0;
            }
            foreach ($newMemberArr as $newKey => $newVal) {
                $getDataArr[$todayDate->format('Ymd')][$newVal['hour']][$newVal['entryPath']]++;
            }
        }

        return $getDataArr;
    }

    /**
     * getMemberNewWeek
     * 신규 회원 분석 - 요일별
     *
     * @param $searchDate
     * @param null $mallSno
     * @return array
     * @throws \Exception
     */
    public function getMemberNewWeek($searchDate, $mallSno = null)
    {
        $sDate = new DateTime($searchDate[0]);
        $eDate = new DateTime($searchDate[1]);
        $dateDiff = date_diff($sDate, $eDate);
        if ($dateDiff->days > 90) {
            throw new \Exception(__('검색 가능 일은 최대 90일 입니다.'));
        }
        if ($searchDate[0] > $searchDate[1]) {
            throw new \Exception(__('시작일이 종료일보다 클 수 없습니다.'));
        }

        $memberYm[0] = substr($searchDate[0], 0, 6);
        $memberYm[1] = substr($searchDate[1], 0, 6);

        $memberDay['memberYM'] = $memberYm;
        if ($mallSno != 'all') {
            $memberDay['mallSno'] = $mallSno;
        }

        $getField[] = 'msd.memberYM';
        for ($i=1; $i<=31; $i++) {
            $getField[] = 'msd.' . $i . '->"$.new.week" AS `' . $i . '`';
        }
        $field = implode(', ', $getField);
        $getDataJson = $this->getMemberDayStatisticsInfo($memberDay, $field, '', true);

        foreach ($getDataJson as $key => $val) {
            // 시작일 앞자리 자르기
            if ($val['memberYM'] == $memberYm[0]) {
                $sDay = substr($searchDate[0], 6, 2);
                for ($i = 1; $i < $sDay; $i++) {
                    unset($getDataJson[$key][$i]);
                }
            }
            // 종료일 뒷자리 자르기
            if ($val['memberYM'] == $memberYm[1]) {
                $eDay = substr($searchDate[1], 6, 2);
                for ($i = 31; $i > $eDay; $i--) {
                    unset($getDataJson[$key][$i]);
                }
            }
        }

        $getDataArr = [];
        foreach ($getDataJson as $dataKey => $dataVal) {
            foreach ($dataVal as $key => $val) {
                if (is_numeric($key) && $val) {
                    $data = json_decode($val, true);
                    foreach ($data as $dKey => $dVal) {
                        foreach ($dVal as $fKey => $fVal) {
                            $getDataArr[$dataVal['memberYM'] . sprintf("%02d", $key)][$dKey][$fKey] += $fVal;
                        }
                    }
                }
            }
        }

        $todayDate = new DateTime();
        // 오늘 검색에 따른 당일 통계
        if ($eDate->format('Ymd') >= $todayDate->format('Ymd')) {
            // 신규회원 통계
            $member['appFl'] = $this->memberPolicy['appFl'];
            $member['approvalDay'] = $todayDate->format('Ymd');
            if ($mallSno != 'all') {
                $member['mallSno'] = $mallSno;
            }
            $newMemberArr = $this->getMemberInfo($member, 'm.memNo, m.entryPath, DATE_FORMAT(m.approvalDt, "%w") as week', null, true);
            unset($member);

            foreach ($newMemberArr as $newKey => $newVal) {
                $getDataArr[$todayDate->format('Ymd')][$newVal['week']][$newVal['entryPath']]++;
            }
        }

        return $getDataArr;
    }

    /**
     * getMemberNewMonth
     * 신규 회원 분석 - 월별
     *
     * @param $searchDate
     * @param null $mallSno
     * @return array
     * @throws \Exception
     */
    public function getMemberNewMonth($searchDate, $mallSno = null)
    {
        $sDate = new DateTime($searchDate[0]);
        $eDate = new DateTime($searchDate[1]);
        $dateDiff = date_diff($sDate, $eDate);
        if ($dateDiff->days > 360) {
            throw new \Exception(__('검색 가능 일은 최대 12개월 입니다.'));
        }
        if ($searchDate[0] > $searchDate[1]) {
            throw new \Exception(__('시작일이 종료일보다 클 수 없습니다.'));
        }
        $memberYm[0] = substr($searchDate[0], 0, 6);
        $memberYm[1] = substr($searchDate[1], 0, 6);

        $memberDay['memberYM'] = $memberYm;
        if ($mallSno != 'all') {
            $memberDay['mallSno'] = $mallSno;
        }

        $getField[] = 'msd.memberYM';
        for ($i=1; $i<=31; $i++) {
            $getField[] = 'msd.' . $i . '->"$.new.device" AS `' . $i . '`';
        }
        $field = implode(', ', $getField);
        $getDataJson = $this->getMemberDayStatisticsInfo($memberDay, $field, '', true,true);

        $getDataArr = [];
        foreach ($getDataJson as $dataKey => $dataVal) {
            foreach ($dataVal as $key => $val) {
                if (is_numeric($key) && $val) {
                    $data = json_decode($val, true);
                    $getDataArr[$dataVal['memberYM']]['pc'] += $data['pc'];
                    $getDataArr[$dataVal['memberYM']]['mobile'] += $data['mobile'];
                    $getDataArr[$dataVal['memberYM']]['etc'] += $data['etc'];
                }
            }
        }

        $todayDate = new DateTime();
        // 오늘 검색에 따른 당일 통계
        if ($eDate->format('Ymd') >= $todayDate->format('Ymd')) {
            // 신규회원 통계
            $member['appFl'] = $this->memberPolicy['appFl'];
            $member['approvalDay'] = $todayDate->format('Ymd');
            if ($mallSno != 'all') {
                $member['mallSno'] = $mallSno;
            }
            $newMemberArr = $this->getMemberInfo($member, 'm.memNo, m.entryPath', null, true);
            unset($member);

            $todayDataArr[$todayDate->format('Ym')] = [
                'pc' => 0,
                'mobile' => 0,
                'etc' => 0,
            ];
            foreach ($newMemberArr as $newKey => $newVal) {
                $getDataArr[$todayDate->format('Ym')][$newVal['entryPath']]++;
            }
        }

        return $getDataArr;
    }

    /**
     * getMemberDay
     * 전체 회원 분석 - 일별
     *
     * @param $searchDate
     * @param null $mallSno
     * @return array
     * @throws \Exception
     */
    public function getMemberDay($searchDate, $mallSno = null)
    {
        $sDate = new DateTime($searchDate[0]);
        $eDate = new DateTime($searchDate[1]);
        $dateDiff = date_diff($sDate, $eDate);
        if ($dateDiff->days > 90) {
            throw new \Exception(__('검색 가능 일은 최대 90일 입니다.'));
        }
        if ($searchDate[0] > $searchDate[1]) {
            throw new \Exception(__('시작일이 종료일보다 클 수 없습니다.'));
        }
        $memberYm[0] = substr($searchDate[0], 0, 6);
        $memberYm[1] = substr($searchDate[1], 0, 6);

        $memberDay['memberYM'] = $memberYm;
        if ($mallSno != 'all') {
            $memberDay['mallSno'] = $mallSno;
        }

        $getField[] = 'msd.memberYM';
        for ($i=1; $i<=31; $i++) {
            $getField[] = 'msd.' . $i . '->"$.now" AS `' . $i . '`';
        }
        $field = implode(', ', $getField);
        $tmpDataJson = $this->getMemberDayStatisticsInfo($memberDay, $field, '', true,true);

        $getDataJson = [];
        foreach ($tmpDataJson as $key => $val) {
            // 시작일 앞자리 자르기
            if ($val['memberYM'] == $memberYm[0]) {
                $sDay = substr($searchDate[0], 6, 2);
                for ($i = 1; $i < $sDay; $i++) {
                    unset($val[$i]);
                }
            }
            // 종료일 뒷자리 자르기
            if ($val['memberYM'] == $memberYm[1]) {
                $eDay = substr($searchDate[1], 6, 2);
                for ($i = 31; $i > $eDay; $i--) {
                    unset($val[$i]);
                }
            }
            $getDataJson[$key] = $val;
        }
        unset($tmpDataJson);

        $getDataArr = [];
        foreach ($getDataJson as $dataKey => $dataVal) {
            foreach ($dataVal as $key => $val) {
                if (is_numeric($key) && $val) {
                    $data = json_decode($val, true);
                    if ($mallSno != 'all') {
                        $getDataArr[$dataVal['memberYM'] . sprintf("%02d", $key)]['total'] = $data['total'];
                        $getDataArr[$dataVal['memberYM'] . sprintf("%02d", $key)]['newTotal'] = $data['newTotal'];
                        $getDataArr[$dataVal['memberYM'] . sprintf("%02d", $key)]['newTotalNoApp'] = $data['newTotalNoApp'];
                        $getDataArr[$dataVal['memberYM'] . sprintf("%02d", $key)]['sleep'] = $data['sleep'];
                        $getDataArr[$dataVal['memberYM'] . sprintf("%02d", $key)]['hackOut'] = $data['hackOut'];
                    } else {
                        $getDataArr[$dataVal['memberYM'] . sprintf("%02d", $key)]['total'] += $data['total'];
                        $getDataArr[$dataVal['memberYM'] . sprintf("%02d", $key)]['newTotal'] += $data['newTotal'];
                        $getDataArr[$dataVal['memberYM'] . sprintf("%02d", $key)]['newTotalNoApp'] += $data['newTotalNoApp'];
                        $getDataArr[$dataVal['memberYM'] . sprintf("%02d", $key)]['sleep'] += $data['sleep'];
                        $getDataArr[$dataVal['memberYM'] . sprintf("%02d", $key)]['hackOut'] += $data['hackOut'];
                    }
                }
            }
        }

        $todayDate = new DateTime();
        // 오늘 검색에 따른 당일 통계
        if ($eDate->format('Ymd') >= $todayDate->format('Ymd')) {
            // 전체회원 통계
            $member['appFl'] = $this->memberPolicy['appFl'];
            $member['approvalTotal'] = $todayDate->format('Ymd');
            if ($mallSno != 'all') {
                $member['mallSno'] = $mallSno;
            }
            $nowMemberTotalArr = $this->getMemberInfo($member, 'count(m.memNo) as total', null, true);
            unset($member);

            foreach ($nowMemberTotalArr as $key => $val) {
                $getDataArr[$todayDate->format('Ymd')]['total'] += $val['total'];
            }

            // 신규회원 통계
            $member['appFl'] = $this->memberPolicy['appFl'];
            $member['approvalDay'] = $todayDate->format('Ymd');
            if ($mallSno != 'all') {
                $member['mallSno'] = $mallSno;
            }
            $nowMemberArr = $this->getMemberInfo($member, 'count(m.memNo) as newTotal', null, true);
            unset($member);

            foreach ($nowMemberArr as $key => $val) {
                $getDataArr[$todayDate->format('Ymd')]['newTotal'] += $val['newTotal'];
            }

            // 미승인 된 회원 수
            $member['appFl'] = 'n';
            $member['approvalDay'] = $todayDate->format('Ymd');
            if ($mallSno != 'all') {
                $member['mallSno'] = $mallSno;
            }
            $nowMemberAppFlArr = $this->getMemberInfo($member, 'count(m.memNo) as nNum', null, true);
            unset($member);

            foreach ($nowMemberAppFlArr as $key => $val) {
                $getDataArr[$todayDate->format('Ymd')]['newTotalNoApp'] += $val['nNum'];
            }

            // 탈퇴 회원 수
            $member['hackDt'] = $todayDate->format('Ymd');
            if ($mallSno != 'all') {
                $member['mallSno'] = $mallSno;
            }
            $nowMemberHockArr = $this->getMemberHackOutInfo($member, 'count(sno) as hNum', null, true);
            unset($member);

            foreach ($nowMemberHockArr as $key => $val) {
                $getDataArr[$todayDate->format('Ymd')]['hackOut'] += $val['hNum'];
            }

            // 어제 휴면 회원 수
            $member['sleepDt'] = $todayDate->format('Ymd');
            if ($mallSno != 'all') {
                $member['mallSno'] = $mallSno;
            }
            $nowMemberSleepArr = $this->getMemberSleepInfo($member, 'count(ms.sleepNo) as sNum');
            unset($member);

            foreach ($nowMemberSleepArr as $key => $val) {
                $getDataArr[$todayDate->format('Ymd')]['sleep'] += $val['sNum'];
            }
        }

        return $getDataArr;
    }

    /**
     * getMemberMonth
     * 전체 회원 분석 - 월별
     *
     * @param $searchDate
     * @param null $mallSno
     * @return array
     * @throws \Exception
     */
    public function getMemberMonth($searchDate, $mallSno = null)
    {
        $sDate = new DateTime($searchDate[0]);
        $eDate = new DateTime($searchDate[1]);
        $dateDiff = date_diff($sDate, $eDate);
        if ($dateDiff->days > 360) {
            throw new \Exception(__('검색 가능 일은 최대 12개월 입니다.'));
        }
        if ($searchDate[0] > $searchDate[1]) {
            throw new \Exception(__('시작일이 종료일보다 클 수 없습니다.'));
        }
        $memberYm[0] = substr($searchDate[0], 0, 6);
        $memberYm[1] = substr($searchDate[1], 0, 6);

        $memberDay['memberYM'] = $memberYm;
        if ($mallSno != 'all') {
            $memberDay['mallSno'] = $mallSno;
        }

        $getField[] = 'msd.memberYM';
        $getField[] = 'msd.mallSno';
        for ($i=1; $i<=31; $i++) {
            $getField[] = 'msd.' . $i . '->"$.now" AS `' . $i . '`';
        }
        $field = implode(', ', $getField);
        $getDataJson = $this->getMemberDayStatisticsInfo($memberDay, $field, '', true,true);

        $getDataArr = [];
        foreach ($getDataJson as $dataKey => $dataVal) {
            foreach ($dataVal as $key => $val) {
                if (is_numeric($key) && $val) {
                    $data = json_decode($val, true);
                    $getDataArr[$dataVal['memberYM']]['total'][$dataVal['mallSno']] = $data['total'];
                    $getDataArr[$dataVal['memberYM']]['newTotal'] += $data['newTotal'];
                    $getDataArr[$dataVal['memberYM']]['newTotalNoApp'] += $data['newTotalNoApp'];
                    $getDataArr[$dataVal['memberYM']]['sleep'] += $data['sleep'];
                    $getDataArr[$dataVal['memberYM']]['hackOut'] += $data['hackOut'];
                }
            }
        }

        foreach ($getDataArr as $key => $val) {
            $getDataArr[$key]['total'] = array_sum($val['total']);
        }

        $todayDate = new DateTime();
        // 오늘 검색에 따른 당일 통계
        if ($eDate->format('Ymd') >= $todayDate->format('Ymd')) {
            // 전체회원 통계
            $member['appFl'] = $this->memberPolicy['appFl'];
            $member['approvalTotal'] = $todayDate->format('Ymd');
            if ($mallSno != 'all') {
                $member['mallSno'] = $mallSno;
            }
            $nowMemberTotalArr = $this->getMemberInfo($member, 'count(m.memNo) as total', null, true);
            unset($member);

            foreach ($nowMemberTotalArr as $key => $val) {
                $getDataArr[$todayDate->format('Ym')]['total'] = $val['total'];
            }

            // 신규회원 통계
            $member['appFl'] = $this->memberPolicy['appFl'];
            $member['approvalDay'] = $todayDate->format('Ymd');
            if ($mallSno != 'all') {
                $member['mallSno'] = $mallSno;
            }
            $nowMemberArr = $this->getMemberInfo($member, 'count(m.memNo) as newTotal', null, true);
            unset($member);

            foreach ($nowMemberArr as $key => $val) {
                $getDataArr[$todayDate->format('Ym')]['newTotal'] += $val['newTotal'];
            }

            // 미승인 된 회원 수
            $member['appFl'] = 'n';
            $member['approvalDay'] = $todayDate->format('Ymd');
            if ($mallSno != 'all') {
                $member['mallSno'] = $mallSno;
            }
            $nowMemberAppFlArr = $this->getMemberInfo($member, 'count(m.memNo) as nNum', null, true);
            unset($member);

            foreach ($nowMemberAppFlArr as $key => $val) {
                $getDataArr[$todayDate->format('Ym')]['newTotalNoApp'] += $val['nNum'];
            }

            // 탈퇴 회원 수
            $member['hackDt'] = $todayDate->format('Ymd');
            if ($mallSno != 'all') {
                $member['mallSno'] = $mallSno;
            }
            $nowMemberHockArr = $this->getMemberHackOutInfo($member, 'count(sno) as hNum', null, true);
            unset($member);

            foreach ($nowMemberHockArr as $key => $val) {
                $getDataArr[$todayDate->format('Ym')]['hackOut'] += $val['hNum'];
            }

            // 어제 휴면 회원 수
            $member['sleepDt'] = $todayDate->format('Ymd');
            if ($mallSno != 'all') {
                $member['mallSno'] = $mallSno;
            }
            $nowMemberSleepArr = $this->getMemberSleepInfo($member, 'count(ms.sleepNo) as sNum');
            unset($member);

            foreach ($nowMemberSleepArr as $key => $val) {
                $getDataArr[$todayDate->format('Ym')]['sleep'] += $val['sNum'];
            }
        }

        return $getDataArr;
    }

    /**
     * getMemberMileageDay
     * 회원 마일리지 분석 - 일별
     *
     * @param $searchDate
     * @param null $mallSno
     * @return array
     * @throws \Exception
     */
    public function getMemberMileageDay($searchDate, $mallSno = null)
    {
        // 상점별 고유번호 - 해외상점
        $mallSno = gd_isset($mallSno, DEFAULT_MALL_NUMBER);

        $sDate = new DateTime($searchDate[0]);
        $eDate = new DateTime($searchDate[1]);
        $dateDiff = date_diff($sDate, $eDate);
        if ($dateDiff->days > 90) {
            throw new \Exception(__('검색 가능 일은 최대 90일 입니다.'));
        }
        if ($searchDate[0] > $searchDate[1]) {
            throw new \Exception(__('시작일이 종료일보다 클 수 없습니다.'));
        }
        $memberYm[0] = substr($searchDate[0], 0, 6);
        $memberYm[1] = substr($searchDate[1], 0, 6);

        $memberDay['memberYM'] = $memberYm;
        if ($mallSno != 'all') {
            $memberDay['mallSno'] = $mallSno;
        }

        $getField[] = 'msd.memberYM';
        for ($i=1; $i<=31; $i++) {
            $getField[] = 'msd.' . $i . '->"$.mileage" AS `' . $i . '`';
        }
        $field = implode(', ', $getField);
        $tmpDataJson = $this->getMemberDayStatisticsInfo($memberDay, $field, '', true,true);

        $getDataJson = [];
        foreach ($tmpDataJson as $key => $val) {
            // 시작일 앞자리 자르기
            if ($val['memberYM'] == $memberYm[0]) {
                $sDay = substr($searchDate[0], 6, 2);
                for ($i = 1; $i < $sDay; $i++) {
                    unset($val[$i]);
                }
            }
            // 종료일 뒷자리 자르기
            if ($val['memberYM'] == $memberYm[1]) {
                $eDay = substr($searchDate[1], 6, 2);
                for ($i = 31; $i > $eDay; $i--) {
                    unset($val[$i]);
                }
            }
            $getDataJson[$key] = $val;
        }
        unset($tmpDataJson);

        $getDataArr = [];
        foreach ($getDataJson as $dataKey => $dataVal) {
            foreach ($dataVal as $key => $val) {
                if (is_numeric($key) && $val) {
                    $data = json_decode($val, true);
                    if ($mallSno != 'all') {
                        $getDataArr[$dataVal['memberYM'] . sprintf("%02d", $key)]['total'] = $data['total'];
                        $getDataArr[$dataVal['memberYM'] . sprintf("%02d", $key)]['useMileage'] = $data['mileage']['use'] * -1;
                        $getDataArr[$dataVal['memberYM'] . sprintf("%02d", $key)]['useCount'] = $data['count']['use'];
                        $getDataArr[$dataVal['memberYM'] . sprintf("%02d", $key)]['saveMileage'] = $data['mileage']['save'];
                        $getDataArr[$dataVal['memberYM'] . sprintf("%02d", $key)]['saveCount'] = $data['count']['save'];
                        $getDataArr[$dataVal['memberYM'] . sprintf("%02d", $key)]['deleteMileage'] = $data['mileage']['delete'] * -1;
                        $getDataArr[$dataVal['memberYM'] . sprintf("%02d", $key)]['deleteCount'] = $data['count']['delete'];
                    } else {
                        $getDataArr[$dataVal['memberYM'] . sprintf("%02d", $key)]['total'] += $data['total'];
                        $getDataArr[$dataVal['memberYM'] . sprintf("%02d", $key)]['useMileage'] += $data['mileage']['use'] * -1;
                        $getDataArr[$dataVal['memberYM'] . sprintf("%02d", $key)]['useCount'] += $data['count']['use'];
                        $getDataArr[$dataVal['memberYM'] . sprintf("%02d", $key)]['saveMileage'] += $data['mileage']['save'];
                        $getDataArr[$dataVal['memberYM'] . sprintf("%02d", $key)]['saveCount'] += $data['count']['save'];
                        $getDataArr[$dataVal['memberYM'] . sprintf("%02d", $key)]['deleteMileage'] += $data['mileage']['delete'] * -1;
                        $getDataArr[$dataVal['memberYM'] . sprintf("%02d", $key)]['deleteCount'] += $data['count']['delete'];
                    }
                }
            }
        }

        $todayDate = new DateTime();
        // 오늘 검색에 따른 당일 통계
        if ($eDate->format('Ymd') >= $todayDate->format('Ymd')) {
            $member['regDtLimit'] = $todayDate->format('Ymd');
            $mileageTotal = $this->getMemberMileageInfo($member, 'sum(mm.mileage) as totalMileage');
            unset($member);

            if (empty($mileageTotal['totalMileage'])) {
                $mileageMember['total'] = 0;
            } else {
                $mileageMember['total'] = $mileageTotal['totalMileage']; // 총 잔여 마일리지
            }

            $member['regDt'] = $todayDate->format('Ymd');
            $mileageMemberArr = $this->getMemberMileageInfo($member, 'mm.mileage, mm.reasonCd, mm.deleteFl', null, true);
            unset($member);

            $mileageMember['mileage']['save'] = 0; // 지급 마일리지
            $mileageMember['mileage']['use'] = 0; // 사용 마일리지
            $mileageMember['mileage']['delete'] = 0; // 소멸 마일리지
            $mileageMember['count']['save'] = 0; // 지급 건수
            $mileageMember['count']['use'] = 0; // 사용 건수
            $mileageMember['count']['delete'] = 0; // 소멸 건수
            foreach ($mileageMemberArr as $mileageKey => $mileageVal) {
                if ($mileageVal['reasonCd'] == '010059999') {
                    $mileageMember['mileage']['delete'] += $mileageVal['mileage'];
                    $mileageMember['count']['delete']++;
                } else {
                    if ($mileageVal['mileage'] > 0) {
                        $mileageMember['mileage']['save'] += $mileageVal['mileage'];
                        $mileageMember['count']['save']++;
                    } else {
                        $mileageMember['mileage']['use'] += $mileageVal['mileage'];
                        $mileageMember['count']['use']++;
                    }
                }
            }

            $getDataArr[$todayDate->format('Ymd')]['total'] = $mileageMember['total'];
            $getDataArr[$todayDate->format('Ymd')]['useMileage'] = $mileageMember['mileage']['use'] * -1;
            $getDataArr[$todayDate->format('Ymd')]['useCount'] = $mileageMember['count']['use'];
            $getDataArr[$todayDate->format('Ymd')]['saveMileage'] = $mileageMember['mileage']['save'];
            $getDataArr[$todayDate->format('Ymd')]['saveCount'] = $mileageMember['count']['save'];
            $getDataArr[$todayDate->format('Ymd')]['deleteMileage'] = $mileageMember['mileage']['delete'] * -1;
            $getDataArr[$todayDate->format('Ymd')]['deleteCount'] = $mileageMember['count']['delete'];
        }

        return $getDataArr;
    }


    /**
     * getMemberMileageMonth
     * 회원 마일리지 분석 - 월별
     *
     * @param $searchDate
     * @param null $mallSno
     * @return array
     * @throws \Exception
     */
    public function getMemberMileageMonth($searchDate, $mallSno = null)
    {
        // 상점별 고유번호 - 해외상점
        $mallSno = gd_isset($mallSno, DEFAULT_MALL_NUMBER);

        $sDate = new DateTime($searchDate[0]);
        $eDate = new DateTime($searchDate[1]);
        $dateDiff = date_diff($sDate, $eDate);
        if ($dateDiff->days > 360) {
            throw new \Exception(__('검색 가능 일은 최대 12개월 입니다.'));
        }
        if ($searchDate[0] > $searchDate[1]) {
            throw new \Exception(__('시작일이 종료일보다 클 수 없습니다.'));
        }
        $memberYm[0] = substr($searchDate[0], 0, 6);
        $memberYm[1] = substr($searchDate[1], 0, 6);

        $memberDay['memberYM'] = $memberYm;
        if ($mallSno != 'all') {
            $memberDay['mallSno'] = $mallSno;
        }

        $getField[] = 'msd.memberYM';
        for ($i=1; $i<=31; $i++) {
            $getField[] = 'msd.' . $i . '->"$.mileage" AS `' . $i . '`';
        }
        $field = implode(', ', $getField);
        $getDataJson = $this->getMemberDayStatisticsInfo($memberDay, $field, '', true,true);

        $getDataArr = [];
        foreach ($getDataJson as $dataKey => $dataVal) {
            foreach ($dataVal as $key => $val) {
                if (is_numeric($key) && $val) {
                    $data = json_decode($val, true);
                    $getDataArr[$dataVal['memberYM']]['total'] = $data['total'];
                    $getDataArr[$dataVal['memberYM']]['useMileage'] += $data['mileage']['use'] * -1;
                    $getDataArr[$dataVal['memberYM']]['useCount'] += $data['count']['use'];
                    $getDataArr[$dataVal['memberYM']]['saveMileage'] += $data['mileage']['save'];
                    $getDataArr[$dataVal['memberYM']]['saveCount'] += $data['count']['save'];
                    $getDataArr[$dataVal['memberYM']]['deleteMileage'] += $data['mileage']['delete'] * -1;
                    $getDataArr[$dataVal['memberYM']]['deleteCount'] += $data['count']['delete'];
                }
            }
        }

        $todayDate = new DateTime();
        // 오늘 검색에 따른 당일 통계
        if ($eDate->format('Ymd') >= $todayDate->format('Ymd')) {
            $member['regDtLimit'] = $todayDate->format('Ymd');
            $mileageTotal = $this->getMemberMileageInfo($member, 'sum(mm.mileage) as totalMileage');
            unset($member);

            if (empty($mileageTotal['totalMileage'])) {
                $mileageMember['total'] = 0;
            } else {
                $mileageMember['total'] = $mileageTotal['totalMileage']; // 총 잔여 마일리지
            }

            $member['regDt'] = $todayDate->format('Ymd');
            $mileageMemberArr = $this->getMemberMileageInfo($member, 'mm.mileage, mm.reasonCd, mm.deleteFl', null, true);
            unset($member);

            $mileageMember['mileage']['save'] = 0; // 지급 마일리지
            $mileageMember['mileage']['use'] = 0; // 사용 마일리지
            $mileageMember['mileage']['delete'] = 0; // 소멸 마일리지
            $mileageMember['count']['save'] = 0; // 지급 건수
            $mileageMember['count']['use'] = 0; // 사용 건수
            $mileageMember['count']['delete'] = 0; // 소멸 건수
            foreach ($mileageMemberArr as $mileageKey => $mileageVal) {
                if ($mileageVal['reasonCd'] == '010059999') {
                    $mileageMember['mileage']['delete'] += $mileageVal['mileage'];
                    $mileageMember['count']['delete']++;
                } else {
                    if ($mileageVal['mileage'] > 0) {
                        $mileageMember['mileage']['save'] += $mileageVal['mileage'];
                        $mileageMember['count']['save']++;
                    } else {
                        $mileageMember['mileage']['use'] += $mileageVal['mileage'];
                        $mileageMember['count']['use']++;
                    }
                }
            }

            $getDataArr[$todayDate->format('Ym')]['total'] = $mileageMember['total'];
            $getDataArr[$todayDate->format('Ym')]['useMileage'] += $mileageMember['mileage']['use'] * -1;
            $getDataArr[$todayDate->format('Ym')]['useCount'] += $mileageMember['count']['use'];
            $getDataArr[$todayDate->format('Ym')]['saveMileage'] += $mileageMember['mileage']['save'];
            $getDataArr[$todayDate->format('Ym')]['saveCount'] += $mileageMember['count']['save'];
            $getDataArr[$todayDate->format('Ym')]['deleteMileage'] += $mileageMember['mileage']['delete'] * -1;
            $getDataArr[$todayDate->format('Ym')]['deleteCount'] += $mileageMember['count']['delete'];
        }

        return $getDataArr;
    }


    /**
     * getMemberDepositDay
     * 회원 예치금 분석 - 일별
     *
     * @param $searchDate
     * @param null $mallSno
     * @return array
     * @throws \Exception
     */
    public function getMemberDepositDay($searchDate, $mallSno = null)
    {
        // 상점별 고유번호 - 해외상점
        $mallSno = gd_isset($mallSno, DEFAULT_MALL_NUMBER);

        $sDate = new DateTime($searchDate[0]);
        $eDate = new DateTime($searchDate[1]);
        $dateDiff = date_diff($sDate, $eDate);
        if ($dateDiff->days > 90) {
            throw new \Exception(__('검색 가능 일은 최대 90일 입니다.'));
        }
        if ($searchDate[0] > $searchDate[1]) {
            throw new \Exception(__('시작일이 종료일보다 클 수 없습니다.'));
        }
        $memberYm[0] = substr($searchDate[0], 0, 6);
        $memberYm[1] = substr($searchDate[1], 0, 6);

        $memberDay['memberYM'] = $memberYm;
        if ($mallSno != 'all') {
            $memberDay['mallSno'] = $mallSno;
        }

        $getField[] = 'msd.memberYM';
        for ($i=1; $i<=31; $i++) {
            $getField[] = 'msd.' . $i . '->"$.deposit" AS `' . $i . '`';
        }
        $field = implode(', ', $getField);
        $tmpDataJson = $this->getMemberDayStatisticsInfo($memberDay, $field, '', true,true);

        $getDataJson = [];
        foreach ($tmpDataJson as $key => $val) {
            // 시작일 앞자리 자르기
            if ($val['memberYM'] == $memberYm[0]) {
                $sDay = substr($searchDate[0], 6, 2);
                for ($i = 1; $i < $sDay; $i++) {
                    unset($val[$i]);
                }
            }
            // 종료일 뒷자리 자르기
            if ($val['memberYM'] == $memberYm[1]) {
                $eDay = substr($searchDate[1], 6, 2);
                for ($i = 31; $i > $eDay; $i--) {
                    unset($val[$i]);
                }
            }
            $getDataJson[$key] = $val;
        }
        unset($tmpDataJson);

        $getDataArr = [];
        foreach ($getDataJson as $dataKey => $dataVal) {
            foreach ($dataVal as $key => $val) {
                if (is_numeric($key) && $val) {
                    $data = json_decode($val, true);
                    if ($mallSno != 'all') {
                        $getDataArr[$dataVal['memberYM'] . sprintf("%02d", $key)]['total'] = $data['total'];
                        $getDataArr[$dataVal['memberYM'] . sprintf("%02d", $key)]['useDeposit'] = $data['deposit']['use'] * -1;
                        $getDataArr[$dataVal['memberYM'] . sprintf("%02d", $key)]['useCount'] = $data['count']['use'];
                        $getDataArr[$dataVal['memberYM'] . sprintf("%02d", $key)]['saveDeposit'] = $data['deposit']['save'];
                        $getDataArr[$dataVal['memberYM'] . sprintf("%02d", $key)]['saveCount'] = $data['count']['save'];
                    } else {
                        $getDataArr[$dataVal['memberYM'] . sprintf("%02d", $key)]['total'] += $data['total'];
                        $getDataArr[$dataVal['memberYM'] . sprintf("%02d", $key)]['useDeposit'] += $data['deposit']['use'] * -1;
                        $getDataArr[$dataVal['memberYM'] . sprintf("%02d", $key)]['useCount'] += $data['count']['use'];
                        $getDataArr[$dataVal['memberYM'] . sprintf("%02d", $key)]['saveDeposit'] += $data['deposit']['save'];
                        $getDataArr[$dataVal['memberYM'] . sprintf("%02d", $key)]['saveCount'] += $data['count']['save'];
                    }
                }
            }
        }

        $todayDate = new DateTime();
        // 오늘 검색에 따른 당일 통계
        if ($eDate->format('Ymd') >= $todayDate->format('Ymd')) {
            // 예치금 통계
            $member['regDtLimit'] = $todayDate->format('Ymd');
            $depositTotal = $this->getMemberDepositInfo($member, 'sum(md.deposit) as totalDeposit');
            unset($member);

            if (empty($depositTotal['totalDeposit'])) {
                $depositMember['total'] = 0;
            } else {
                $depositMember['total'] = $depositTotal['totalDeposit']; // 총 잔여 예치금
            }

            $member['regDt'] = $todayDate->format('Ymd');
            $depositMemberArr = $this->getMemberDepositInfo($member, 'md.deposit', null, true);
            unset($member);

            $depositMember['deposit']['save'] = 0; // 지급 마일리지
            $depositMember['deposit']['use'] = 0; // 사용 마일리지
            $depositMember['count']['save'] = 0; // 지급 건수
            $depositMember['count']['use'] = 0; // 사용 건수
            foreach ($depositMemberArr as $depositKey => $depositVal) {
                if ($depositVal['deposit'] > 0) {
                    $depositMember['deposit']['save'] += $depositVal['deposit'];
                    $depositMember['count']['save']++;
                } else {
                    $depositMember['deposit']['use'] += $depositVal['deposit'];
                    $depositMember['count']['use']++;
                }
            }
            $getDataArr[$todayDate->format('Ymd')]['total'] = $depositMember['total'];
            $getDataArr[$todayDate->format('Ymd')]['useDeposit'] = $depositMember['deposit']['use'] * -1;
            $getDataArr[$todayDate->format('Ymd')]['useCount'] = $depositMember['count']['use'];
            $getDataArr[$todayDate->format('Ymd')]['saveDeposit'] = $depositMember['deposit']['save'];
            $getDataArr[$todayDate->format('Ymd')]['saveCount'] = $depositMember['count']['save'];
        }

        return $getDataArr;
    }


    /**
     * getMemberDepositMonth
     * 회원 마일리지 분석 - 월별
     *
     * @param $searchDate
     * @param null $mallSno
     * @return array
     * @throws \Exception
     */
    public function getMemberDepositMonth($searchDate, $mallSno = null)
    {
        // 상점별 고유번호 - 해외상점
        $mallSno = gd_isset($mallSno, DEFAULT_MALL_NUMBER);

        $sDate = new DateTime($searchDate[0]);
        $eDate = new DateTime($searchDate[1]);
        $dateDiff = date_diff($sDate, $eDate);
        if ($dateDiff->days > 360) {
            throw new \Exception(__('검색 가능 일은 최대 12개월 입니다.'));
        }
        if ($searchDate[0] > $searchDate[1]) {
            throw new \Exception(__('시작일이 종료일보다 클 수 없습니다.'));
        }
        $memberYm[0] = substr($searchDate[0], 0, 6);
        $memberYm[1] = substr($searchDate[1], 0, 6);

        $memberDay['memberYM'] = $memberYm;
        if ($mallSno != 'all') {
            $memberDay['mallSno'] = $mallSno;
        }

        $getField[] = 'msd.memberYM';
        for ($i=1; $i<=31; $i++) {
            $getField[] = 'msd.' . $i . '->"$.deposit" AS `' . $i . '`';
        }
        $field = implode(', ', $getField);
        $getDataJson = $this->getMemberDayStatisticsInfo($memberDay, $field, '', true,true);

        $getDataArr = [];
        foreach ($getDataJson as $dataKey => $dataVal) {
            foreach ($dataVal as $key => $val) {
                if (is_numeric($key) && $val) {
                    $data = json_decode($val, true);
                    if ($data) {
                        $getDataArr[$dataVal['memberYM']]['total'] = $data['total'];
                        $getDataArr[$dataVal['memberYM']]['useDeposit'] += $data['deposit']['use'] * -1;
                        $getDataArr[$dataVal['memberYM']]['useCount'] += $data['count']['use'];
                        $getDataArr[$dataVal['memberYM']]['saveDeposit'] += $data['deposit']['save'];
                        $getDataArr[$dataVal['memberYM']]['saveCount'] += $data['count']['save'];
                    }
                }
            }
        }

        $todayDate = new DateTime();
        // 오늘 검색에 따른 당일 통계
        if ($eDate->format('Ymd') >= $todayDate->format('Ymd')) {
            // 예치금 통계
            $member['regDtLimit'] = $todayDate->format('Ymd');
            $depositTotal = $this->getMemberDepositInfo($member, 'sum(md.deposit) as totalDeposit');
            unset($member);

            if (empty($depositTotal['totalDeposit'])) {
                $depositMember['total'] = 0;
            } else {
                $depositMember['total'] = $depositTotal['totalDeposit']; // 총 잔여 예치금
            }

            $member['regDt'] = $todayDate->format('Ymd');
            $depositMemberArr = $this->getMemberDepositInfo($member, 'md.deposit', null, true);
            unset($member);

            $depositMember['deposit']['save'] = 0; // 지급 마일리지
            $depositMember['deposit']['use'] = 0; // 사용 마일리지
            $depositMember['count']['save'] = 0; // 지급 건수
            $depositMember['count']['use'] = 0; // 사용 건수
            foreach ($depositMemberArr as $depositKey => $depositVal) {
                if ($depositVal['deposit'] > 0) {
                    $depositMember['deposit']['save'] += $depositVal['deposit'];
                    $depositMember['count']['save']++;
                } else {
                    $depositMember['deposit']['use'] += $depositVal['deposit'];
                    $depositMember['count']['use']++;
                }
            }
            $getDataArr[$todayDate->format('Ym')]['total'] = $depositMember['total'];
            $getDataArr[$todayDate->format('Ym')]['useDeposit'] += $depositMember['deposit']['use'] * -1;
            $getDataArr[$todayDate->format('Ym')]['useCount'] += $depositMember['count']['use'];
            $getDataArr[$todayDate->format('Ym')]['saveDeposit'] += $depositMember['deposit']['save'];
            $getDataArr[$todayDate->format('Ym')]['saveCount'] += $depositMember['count']['save'];
        }

        return $getDataArr;
    }

    /**
     * getMemberAllGender
     * 전체 회원 분석 - 성별
     *
     * @param $searchDate
     * @param null $mallSno
     * @return array
     * @throws \Exception
     */
    public function getMemberAllGender($searchDate, $mallSno = null)
    {
        // 상점별 고유번호 - 해외상점
        $mallSno = gd_isset($mallSno, DEFAULT_MALL_NUMBER);

        $sDate = new DateTime($searchDate[0]);
        $eDate = new DateTime($searchDate[1]);
        $dateDiff = date_diff($sDate, $eDate);
        if ($dateDiff->days > 90) {
            throw new \Exception(__('검색 가능 일은 최대 90일 입니다.'));
        }
        if ($searchDate[0] > $searchDate[1]) {
            throw new \Exception(__('시작일이 종료일보다 클 수 없습니다.'));
        }
        $memberYm[0] = substr($searchDate[0], 0, 6);
        $memberYm[1] = substr($searchDate[1], 0, 6);

        $memberDay['memberYM'] = $memberYm;
        if ($mallSno != 'all') {
            $memberDay['mallSno'] = $mallSno;
        }

        $getField[] = 'msd.memberYM';
        for ($i=1; $i<=31; $i++) {
            $getField[] = 'msd.' . $i . '->"$.now.gender" AS `' . $i . '`';
        }
        $field = implode(', ', $getField);
        $getDataJson = $this->getMemberDayStatisticsInfo($memberDay, $field, '', true);

        foreach ($getDataJson as $key => $val) {
            // 시작일 앞자리 자르기
            if ($val['memberYM'] == $memberYm[0]) {
                $sDay = substr($searchDate[0], 6, 2);
                for ($i = 1; $i < $sDay; $i++) {
                    unset($getDataJson[$key][$i]);
                }
            }
            // 종료일 뒷자리 자르기
            if ($val['memberYM'] == $memberYm[1]) {
                $eDay = substr($searchDate[1], 6, 2);
                for ($i = 31; $i > $eDay; $i--) {
                    unset($getDataJson[$key][$i]);
                }
            }
        }

        $getDataArr = [];
        foreach ($getDataJson as $dataKey => $dataVal) {
            foreach ($dataVal as $key => $val) {
                if (is_numeric($key) && $val) {
                    $data = json_decode($val, true);
                    if ($mallSno != 'all') {
                        $getDataArr[$dataVal['memberYM'] . sprintf("%02d", $key)] = $data;
                    } else {
                        $getDataArr[$dataVal['memberYM'] . sprintf("%02d", $key)]['male'] += $data['male'];
                        $getDataArr[$dataVal['memberYM'] . sprintf("%02d", $key)]['female'] += $data['female'];
                        $getDataArr[$dataVal['memberYM'] . sprintf("%02d", $key)]['etc'] += $data['etc'];
                    }
                }
            }
        }

        $todayDate = new DateTime();
        // 오늘 검색에 따른 당일 통계
        if ($eDate->format('Ymd') >= $todayDate->format('Ymd')) {
            // 전체회원 통계
            $member['appFl'] = $this->memberPolicy['appFl'];
            $member['approvalTotal'] = $todayDate->format('Ymd');
            if ($mallSno != 'all') {
                $member['mallSno'] = $mallSno;
            }
            $nowMemberArr = $this->getMemberInfo($member, 'm.memNo, m.entryPath, m.sexFl', null, true);
            unset($member);

            // 전체 가입 성별
            $genderArr = array_column($nowMemberArr, 'sexFl');
            $nowMember = $this->getMemberGender($genderArr);

            $getDataArr[$todayDate->format('Ymd')] = $nowMember;
        }

        return $getDataArr;
    }

    /**
     * getMemberAllAge
     * 전체 회원 분석 - 연령별
     *
     * @param $searchDate
     * @param null $mallSno
     * @return array
     * @throws \Exception
     */
    public function getMemberAllAge($searchDate, $mallSno = null)
    {
        // 상점별 고유번호 - 해외상점
        $mallSno = gd_isset($mallSno, DEFAULT_MALL_NUMBER);

        $sDate = new DateTime($searchDate[0]);
        $eDate = new DateTime($searchDate[1]);
        $dateDiff = date_diff($sDate, $eDate);
        if ($dateDiff->days > 90) {
            throw new \Exception(__('검색 가능 일은 최대 90일 입니다.'));
        }
        if ($searchDate[0] > $searchDate[1]) {
            throw new \Exception(__('시작일이 종료일보다 클 수 없습니다.'));
        }
        $memberYm[0] = substr($searchDate[0], 0, 6);
        $memberYm[1] = substr($searchDate[1], 0, 6);

        $memberDay['memberYM'] = $memberYm;
        if ($mallSno != 'all') {
            $memberDay['mallSno'] = $mallSno;
        }

        $getField[] = 'msd.memberYM';
        for ($i=1; $i<=31; $i++) {
            $getField[] = 'msd.' . $i . '->"$.now.age" AS `' . $i . '`';
        }
        $field = implode(', ', $getField);
        $getDataJson = $this->getMemberDayStatisticsInfo($memberDay, $field, '', true);

        foreach ($getDataJson as $key => $val) {
            // 시작일 앞자리 자르기
            if ($val['memberYM'] == $memberYm[0]) {
                $sDay = substr($searchDate[0], 6, 2);
                for ($i = 1; $i < $sDay; $i++) {
                    unset($getDataJson[$key][$i]);
                }
            }
            // 종료일 뒷자리 자르기
            if ($val['memberYM'] == $memberYm[1]) {
                $eDay = substr($searchDate[1], 6, 2);
                for ($i = 31; $i > $eDay; $i--) {
                    unset($getDataJson[$key][$i]);
                }
            }
        }

        $getDataArr = [];
        foreach ($getDataJson as $dataKey => $dataVal) {
            foreach ($dataVal as $key => $val) {
                if (is_numeric($key) && $val) {
                    $data = json_decode($val, true);
                    if ($mallSno != 'all') {
                        $getDataArr[$dataVal['memberYM'] . sprintf("%02d", $key)] = $data;
                    } else {
                        $getDataArr[$dataVal['memberYM'] . sprintf("%02d", $key)]['10'] += $data['10'];
                        $getDataArr[$dataVal['memberYM'] . sprintf("%02d", $key)]['20'] += $data['20'];
                        $getDataArr[$dataVal['memberYM'] . sprintf("%02d", $key)]['30'] += $data['30'];
                        $getDataArr[$dataVal['memberYM'] . sprintf("%02d", $key)]['40'] += $data['40'];
                        $getDataArr[$dataVal['memberYM'] . sprintf("%02d", $key)]['50'] += $data['50'];
                        $getDataArr[$dataVal['memberYM'] . sprintf("%02d", $key)]['60'] += $data['60'];
                        $getDataArr[$dataVal['memberYM'] . sprintf("%02d", $key)]['70'] += $data['70'];
                        $getDataArr[$dataVal['memberYM'] . sprintf("%02d", $key)]['etc'] += $data['etc'];
                    }
                }
            }
        }

        $todayDate = new DateTime();
        // 오늘 검색에 따른 당일 통계
        if ($eDate->format('Ymd') >= $todayDate->format('Ymd')) {
            // 전체회원 통계
            $member['appFl'] = $this->memberPolicy['appFl'];
            $member['approvalTotal'] = $todayDate->format('Ymd');
            if ($mallSno != 'all') {
                $member['mallSno'] = $mallSno;
            }
            $nowMemberArr = $this->getMemberInfo($member, 'm.memNo, m.entryPath, FLOOR((' . $this->memberPolicy['statisticsDate']->format('Y') . ' - DATE_FORMAT(m.birthDt, "%Y")) / 10) as age', null, true);
            unset($member);

            // 전체 가입 연령
            $ageArr = array_column($nowMemberArr, 'age');
            $nowMember = $this->getMemberAge($ageArr);

            $getDataArr[$todayDate->format('Ymd')] = $nowMember;
        }

        return $getDataArr;
    }

    /**
     * getMemberAllArea
     * 전체 회원 분석 - 지역별
     *
     * @param $searchDate
     * @param null $mallSno
     * @return array
     * @throws \Exception
     */
    public function getMemberAllArea($searchDate, $mallSno = null)
    {
        // 상점별 고유번호 - 해외상점
        $mallSno = gd_isset($mallSno, DEFAULT_MALL_NUMBER);

        $sDate = new DateTime($searchDate[0]);
        $eDate = new DateTime($searchDate[1]);
        $dateDiff = date_diff($sDate, $eDate);
        if ($dateDiff->days > 90) {
            throw new \Exception(__('검색 가능 일은 최대 90일 입니다.'));
        }
        if ($searchDate[0] > $searchDate[1]) {
            throw new \Exception(__('시작일이 종료일보다 클 수 없습니다.'));
        }
        $memberYm[0] = substr($searchDate[0], 0, 6);
        $memberYm[1] = substr($searchDate[1], 0, 6);

        $memberDay['memberYM'] = $memberYm;
        if ($mallSno != 'all') {
            $memberDay['mallSno'] = $mallSno;
        }

        $getField[] = 'msd.memberYM';
        for ($i=1; $i<=31; $i++) {
            $getField[] = 'msd.' . $i . '->"$.now.area" AS `' . $i . '`';
        }
        $field = implode(', ', $getField);
        $getDataJson = $this->getMemberDayStatisticsInfo($memberDay, $field, '', true);

        foreach ($getDataJson as $key => $val) {
            // 시작일 앞자리 자르기
            if ($val['memberYM'] == $memberYm[0]) {
                $sDay = substr($searchDate[0], 6, 2);
                for ($i = 1; $i < $sDay; $i++) {
                    unset($getDataJson[$key][$i]);
                }
            }
            // 종료일 뒷자리 자르기
            if ($val['memberYM'] == $memberYm[1]) {
                $eDay = substr($searchDate[1], 6, 2);
                for ($i = 31; $i > $eDay; $i--) {
                    unset($getDataJson[$key][$i]);
                }
            }
        }

        $getDataArr = [];
        foreach ($getDataJson as $dataKey => $dataVal) {
            foreach ($dataVal as $key => $val) {
                if (is_numeric($key) && $val) {
                    $data = json_decode($val, true);
                    if ($mallSno != 'all') {
                        $getDataArr[$dataVal['memberYM'] . sprintf("%02d", $key)] = $data;
                    } else {
                        $getDataArr[$dataVal['memberYM'] . sprintf("%02d", $key)]['강원'] += $data['강원'];
                        $getDataArr[$dataVal['memberYM'] . sprintf("%02d", $key)]['경기'] += $data['경기'];
                        $getDataArr[$dataVal['memberYM'] . sprintf("%02d", $key)]['경남'] += $data['경남'];
                        $getDataArr[$dataVal['memberYM'] . sprintf("%02d", $key)]['경북'] += $data['경북'];
                        $getDataArr[$dataVal['memberYM'] . sprintf("%02d", $key)]['광주'] += $data['광주'];
                        $getDataArr[$dataVal['memberYM'] . sprintf("%02d", $key)]['대구'] += $data['대구'];
                        $getDataArr[$dataVal['memberYM'] . sprintf("%02d", $key)]['대전'] += $data['대전'];
                        $getDataArr[$dataVal['memberYM'] . sprintf("%02d", $key)]['부산'] += $data['부산'];
                        $getDataArr[$dataVal['memberYM'] . sprintf("%02d", $key)]['서울'] += $data['서울'];
                        $getDataArr[$dataVal['memberYM'] . sprintf("%02d", $key)]['세종'] += $data['세종'];
                        $getDataArr[$dataVal['memberYM'] . sprintf("%02d", $key)]['울산'] += $data['울산'];
                        $getDataArr[$dataVal['memberYM'] . sprintf("%02d", $key)]['인천'] += $data['인천'];
                        $getDataArr[$dataVal['memberYM'] . sprintf("%02d", $key)]['전남'] += $data['전남'];
                        $getDataArr[$dataVal['memberYM'] . sprintf("%02d", $key)]['전북'] += $data['전북'];
                        $getDataArr[$dataVal['memberYM'] . sprintf("%02d", $key)]['제주'] += $data['제주'];
                        $getDataArr[$dataVal['memberYM'] . sprintf("%02d", $key)]['충남'] += $data['충남'];
                        $getDataArr[$dataVal['memberYM'] . sprintf("%02d", $key)]['충북'] += $data['충북'];
                        $getDataArr[$dataVal['memberYM'] . sprintf("%02d", $key)]['etc'] += $data['etc'];
                    }
                }
            }
        }

        $todayDate = new DateTime();
        // 오늘 검색에 따른 당일 통계
        if ($eDate->format('Ymd') >= $todayDate->format('Ymd')) {
            // 전체회원 통계
            $member['appFl'] = $this->memberPolicy['appFl'];
            $member['approvalTotal'] = $todayDate->format('Ymd');
            if ($mallSno != 'all') {
                $member['mallSno'] = $mallSno;
            }
            $nowMemberArr = $this->getMemberInfo($member, 'm.memNo, m.entryPath, SUBSTRING_INDEX(m.address, " ", 1) as area', null, true);
            unset($member);

            // 전체 가입 연령
            $ageArr = array_column($nowMemberArr, 'area');
            $nowMember = $this->getMemberArea($ageArr);

            $getDataArr[$todayDate->format('Ymd')] = $nowMember;
        }

        return $getDataArr;
    }

    /**
     * 회원통계 - 메인 탭
     * getTodayMainTabMember
     *
     * @param $searchData   orderYMD / mallSno
     *
     * @return array
     * @throws \Exception
     */
    public function getTodayMainTabMember($searchData)
    {
        if ($searchData['mallSno'] == 'all') {
            unset($searchData['mallSno']);
        }

        $sDate = new DateTime($searchData['orderYMD'][0]);
        $eDate = new DateTime($searchData['orderYMD'][1]);
        unset($searchData['orderYMD']);

        $searchData['memberYM'][0] = $sDate->format('Ym');
        $searchData['memberYM'][1] = $eDate->format('Ym');
        $searchData['sort'] = 'msd.memberYM asc';
        $getField[] = 'msd.memberYM';
        for ($i=1; $i<=31; $i++) {
            $getField[] = 'msd.' . $i . '->"$.new.total" AS `' . $i . '`';
        }
        $field = implode(', ', $getField);
        $tmpDataJson = $this->getMemberDayStatisticsInfo($searchData, $field, null, true);

        $getDataJson = [];
        foreach ($tmpDataJson as $key => $val) {
            // 시작일 앞자리 자르기
            if ($val['memberYM'] == $searchData['memberYM'][0]) {
                $sDay = $sDate->format('d');
                for ($i = 1; $i < $sDay; $i++) {
                    unset($val[$i]);
                }
            }
            // 종료일 뒷자리 자르기
            if ($val['memberYM'] == $searchData['memberYM'][1]) {
                $eDay = $eDate->format('d');
                for ($i = 31; $i > $eDay; $i--) {
                    unset($val[$i]);
                }
            }
            $getDataJson[$key] = $val;
        }
        unset($tmpDataJson);

        $todayDate = new DateTime();
        // 오늘 검색에 따른 당일 통계
        if ($eDate->format('Ymd') >= $todayDate->format('Ymd')) {
            // 신규회원 통계
            $member['appFl'] = $this->memberPolicy['appFl'];
            $member['approvalDay'] = $todayDate->format('Ymd');
            $member['mallSno'] = $searchData['mallSno'];
            $newMemberArr = $this->getMemberInfo($member, 'count(m.memNo) as cNum');
            unset($member);
        }
        $returnMemberCount = $newMemberArr['cNum'];
        foreach ($getDataJson as $key => $val) {
            foreach ($val as $dayKey => $dayVal) {
                if (is_numeric($dayKey)) {
                    unset($dayStatistics);
                    $returnMemberCount += $dayVal;
                }
            }
        }
        return $returnMemberCount;
    }

    /**
     * 일별 회원통계 정리
     *
     * @param null $mallSno
     *
     * @return bool
     */
    public function setDayStatistics($mallSno = null)
    {
        // 상점별 회원 - 해외상점
        $mallSno = gd_isset($mallSno, DEFAULT_MALL_NUMBER);

        // 신규회원 통계
        $member['appFl'] = $this->memberPolicy['appFl'];
        $member['approvalDay'] = $this->memberPolicy['statisticsDate']->format('Ymd');
        $member['mallSno'] = $mallSno;
        $newMemberArr = $this->getMemberInfo($member, 'm.memNo, m.entryPath, m.sexFl, FLOOR((' . $this->memberPolicy['statisticsDate']->format('Y') . ' - DATE_FORMAT(m.birthDt, "%Y")) / 10) as age, SUBSTRING_INDEX(m.address, " ", 1) as area, DATE_FORMAT(m.approvalDt, "%H") as hour, DATE_FORMAT(m.approvalDt, "%w") as week', null, true);
        unset($member);

        // 신규회원 수
        $newMember['total'] = count($newMemberArr);

        // 가입 디바이스별
        foreach ($newMemberArr as $newKey => $newVal) {
            $newMember['device'][$newVal['entryPath']]++;
            $newMember['hour'][$newVal['hour']][$newVal['entryPath']]++;
            $gender = $newVal['gender'] == 'w' ? 'female' : 'male';
            $newMember['gender'][$gender][$newVal['entryPath']]++;
            if ($newVal['age'] <= 1) {
                $age = '10';
            } else if ($newVal['age'] >= 7) {
                $age = '70';
            } else {
                $age = $newVal['age'] . '0';
            }
            $newMember['age'][$age][$newVal['entryPath']]++;
            $area = $this->getCityName($newVal['area']);
            $newMember['area'][$area][$newVal['entryPath']]++;
            $newMember['week'][$newVal['week']][$newVal['entryPath']]++;
        }

        // 전체회원 통계
        $member['appFl'] = $this->memberPolicy['appFl'];
        $member['approvalTotal'] = $this->memberPolicy['statisticsDate']->format('Ymd');
        $member['mallSno'] = $mallSno;
        $nowMemberArr = $this->getMemberInfo($member, 'm.memNo, m.entryPath, m.sexFl, FLOOR((' . $this->memberPolicy['statisticsDate']->format('Y') . ' - DATE_FORMAT(m.birthDt, "%Y")) / 10) as age, SUBSTRING_INDEX(m.address, " ", 1) as area, DATE_FORMAT(m.approvalDt, "%H") as hour, DATE_FORMAT(m.approvalDt, "%w") as week', null, true);
        unset($member);

        // 전체 회원 수
        $nowMember['total'] = count($nowMemberArr);

        // 어제 신규 회원 가입 수
        $nowMember['newTotal'] = $newMember['total'];

        // 어제 가입했으나 미승인 된 회원 수
        $member['appFl'] = 'n';
        $member['approvalDay'] = $this->memberPolicy['statisticsDate']->format('Ymd');
        $member['mallSno'] = $mallSno;
        $nowMemberAppFlArr = $this->getMemberInfo($member, 'count(m.memNo) as nNum');
        unset($member);
        $nowMember['newTotalNoApp'] = $nowMemberAppFlArr['nNum'];

        // 전체 가입 디바이스별
        $entryPathArr = array_column($nowMemberArr, 'entryPath');
        $nowMember['device'] = $this->getMemberDevice($entryPathArr);

        // 전체 가입 성별
        $genderArr = array_column($nowMemberArr, 'sexFl');
        $nowMember['gender'] = $this->getMemberGender($genderArr);

        // 전체 가입 연령
        $ageArr = array_column($nowMemberArr, 'age');
        $nowMember['age'] = $this->getMemberAge($ageArr);

        // 전체 가입 지역
        $areaArr = array_column($nowMemberArr, 'area');
        $nowMember['area'] = $this->getMemberArea($areaArr);

        // 전체 가입 시간
        $hourArr = array_column($nowMemberArr, 'hour');
        $nowMember['hour'] = $this->getMemberHour($hourArr);

        // 전체 가입 요일
        $weekArr = array_column($nowMemberArr, 'week');
        $nowMember['week'] = $this->getMemberWeek($weekArr);

        // 어제 탈퇴 회원 수
        $member['hackDt'] = $this->memberPolicy['statisticsDate']->format('Ymd');
        $member['mallSno'] = $mallSno;
        $nowMemberHockArr = $this->getMemberHackOutInfo($member, 'count(sno) as hNum');
        unset($member);
        $nowMember['hackOut'] = $nowMemberHockArr['hNum'];

        // 어제 휴면 회원 수
        $member['sleepDt'] = $this->memberPolicy['statisticsDate']->format('Ymd');
        $member['mallSno'] = $mallSno;
        $nowMemberSleepArr = $this->getMemberSleepInfo($member, 'count(ms.sleepNo) as sNum');
        unset($member);
        $nowMember['sleep'] = $nowMemberSleepArr['sNum'];

        if ($mallSno == DEFAULT_MALL_NUMBER) {
            // 마일리지 통계
            $member['regDtLimit'] = $this->memberPolicy['statisticsDate']->format('Ymd');
            $mileageTotal = $this->getMemberMileageInfo($member, 'sum(mm.mileage) as totalMileage');
            unset($member);

            if (empty($mileageTotal['totalMileage'])) {
                $mileageMember['total'] = 0;
            } else {
                $mileageMember['total'] = $mileageTotal['totalMileage']; // 총 잔여 마일리지
            }

            $member['regDt'] = $this->memberPolicy['statisticsDate']->format('Ymd');
            $mileageMemberArr = $this->getMemberMileageInfo($member, 'mm.mileage, mm.reasonCd, mm.deleteFl', null, true);
            unset($member);

            $mileageMember['mileage']['save'] = 0; // 지급 마일리지
            $mileageMember['mileage']['use'] = 0; // 사용 마일리지
            $mileageMember['mileage']['delete'] = 0; // 소멸 마일리지
            $mileageMember['count']['save'] = 0; // 지급 건수
            $mileageMember['count']['use'] = 0; // 사용 건수
            $mileageMember['count']['delete'] = 0; // 소멸 건수
            foreach ($mileageMemberArr as $mileageKey => $mileageVal) {
                if ($mileageVal['reasonCd'] == '010059999') {
                    $mileageMember['mileage']['delete'] += $mileageVal['mileage'];
                    $mileageMember['count']['delete']++;
                } else {
                    if ($mileageVal['mileage'] > 0) {
                        $mileageMember['mileage']['save'] += $mileageVal['mileage'];
                        $mileageMember['count']['save']++;
                    } else {
                        $mileageMember['mileage']['use'] += $mileageVal['mileage'];
                        $mileageMember['count']['use']++;
                    }
                }
            }

            // 예치금 통계
            $member['regDtLimit'] = $this->memberPolicy['statisticsDate']->format('Ymd');
            $depositTotal = $this->getMemberDepositInfo($member, 'sum(md.deposit) as totalDeposit');
            unset($member);

            if (empty($depositTotal['totalDeposit'])) {
                $depositMember['total'] = 0;
            } else {
                $depositMember['total'] = $depositTotal['totalDeposit']; // 총 잔여 예치금
            }

            $member['regDt'] = $this->memberPolicy['statisticsDate']->format('Ymd');
            $depositMemberArr = $this->getMemberDepositInfo($member, 'md.deposit', null, true);
            unset($member);

            $depositMember['deposit']['save'] = 0; // 지급 마일리지
            $depositMember['deposit']['use'] = 0; // 사용 마일리지
            $depositMember['count']['save'] = 0; // 지급 건수
            $depositMember['count']['use'] = 0; // 사용 건수
            foreach ($depositMemberArr as $depositKey => $depositVal) {
                if ($depositVal['deposit'] > 0) {
                    $depositMember['deposit']['save'] += $depositVal['deposit'];
                    $depositMember['count']['save']++;
                } else {
                    $depositMember['deposit']['use'] += $depositVal['deposit'];
                    $depositMember['count']['use']++;
                }
            }
        }

        $memberDayArr = [
            'new'     => $newMember, // 신규회원
            'now'     => $nowMember, // 전체회원
            'mileage' => $mileageMember, // 어제 마일리지
            'deposit' => $depositMember, // 어제 마일리지
        ];

        $memberDayJson = json_encode($memberDayArr, JSON_UNESCAPED_UNICODE);

        $memberDay['memberYM'] = $this->memberPolicy['statisticsDate']->format('Ym');
        $memberDay['mallSno'] = $mallSno;
        $getCheckDayStatistics = $this->getMemberDayStatisticsInfo($memberDay, 'msd.memberYM');
        if ($getCheckDayStatistics) {
            $arrBind = [];
            $strSQL = "UPDATE " . DB_MEMBER_DAY . " SET `" . $this->memberPolicy['statisticsDate']->format('j') . "` = ?, modDt=now() WHERE `memberYM` = ? AND `mallSno` = ?";
            $this->db->bind_param_push($arrBind, 's', $memberDayJson);
            $this->db->bind_param_push($arrBind, 'i', $memberDay['memberYM']);
            $this->db->bind_param_push($arrBind, 'i', $mallSno);
            $this->db->bind_query($strSQL, $arrBind);
            unset($arrBind);
        } else {
            $arrBind = [];
            $strSQL = "INSERT INTO " . DB_MEMBER_DAY . " SET `memberYM`=?, `mallSno` = ?, `" . $this->memberPolicy['statisticsDate']->format('j') . "`=?, `regDt`=now()";
            $this->db->bind_param_push($arrBind, 'i', $memberDay['memberYM']);
            $this->db->bind_param_push($arrBind, 'i', $mallSno);
            $this->db->bind_param_push($arrBind, 's', $memberDayJson);
            $this->db->bind_query($strSQL, $arrBind);
            unset($arrBind);
        }

        return true;
    }
}
