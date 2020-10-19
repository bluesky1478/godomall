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

namespace Bundle\Component\Admin;

use Component\Database\DBTableField;
use Component\Validator\Validator;
use Message;

/**
 * 스케줄(일정관리) 클래스
 * @author Nam-ju Lee <lnjts@godo.co.kr>
 */
class Schedule
{
    protected $db;
    public $cfg;

    public function __construct()
    {
        if (!is_object($this->db)) {
            $this->db = \App::load('DB');
        }

        // 스케줄 설정값
        $this->cfg = gd_policy('basic.schedule');
    }

    /**
     * 스케줄있는 월별 일자
     *
     * @param array $data 년월
     * @return boolean 처리결과
     * @throws \Exception
     */
    public function getExistDay($data)
    {
        // Validation
        $validator = new Validator();
        $validator->add('year', 'number', true);
        $validator->add('month', 'number', true);
        if ($validator->act($data, true) === false) {
            throw new \Exception(implode("\n", $validator->errors));
        }

        // Data
        $arrBind = $getData = [];
        $this->db->bind_param_push($arrBind, 's', sprintf('%04d-%02d', $data['year'], $data['month']));
        $this->db->bind_param_push($arrBind, 'i', \Session::get('manager.scmNo'));
        $strSQL = "SELECT scdDt,m.scmNo FROM " . DB_SCHEDULE . " as s LEFT OUTER JOIN ".DB_MANAGER." as m ON s.managerNo = m.sno WHERE scdDt LIKE concat(?,'%') AND m.scmNo = ?";
        $res = $this->db->query_fetch($strSQL, $arrBind);
        if (is_array($res) === true) {
            foreach ($res as $row) {
                array_push($getData, substr($row['scdDt'], -2));
            }
        }
        return $getData;
    }

    /**
     * 스케줄 정보
     *
     * @param string $scdDt 일자
     * @return array 정보
     * @throws \Exception
     */
    public function getDayContents($scdDt)
    {
        // Validation
        if (Validator::required($scdDt) === false) {
            throw new \Exception(sprintf(__('%s은(는) 필수 항목 입니다.'), '일자'));
        }

        // Data
        $arrBind = [];
        $this->db->bind_param_push($arrBind, 's', $scdDt);
        $strSQL = 'SELECT sno,scdDt,subject,contents FROM ' . DB_SCHEDULE . ' WHERE scdDt=?';
        $data = $this->db->query_fetch($strSQL, $arrBind, false);
        $getData = gd_htmlspecialchars_stripslashes(gd_isset($data));
        if (empty($getData['contents']) === false) {
            $getData['contents'] = nl2br($getData['contents']);
        }
        return $getData;
    }

    /**
     * 스케줄 정보
     * @param string $sno 일련번호
     * @return array 데이터
     * @throws \Exception
     */
    public function getScheduleView($sno)
    {
        if (Validator::number($sno, null, null, true) === false) {
            throw new \Exception(sprintf(__('%s 항목이 잘못 되었습니다.'), '스케줄 번호'));
        }

        $arrBind = $getData = $data = $checked = $selected = [];

        $this->db->strField = "*";
        $this->db->strWhere = "sno=?";
        $this->db->bind_param_push($arrBind, 'i', $sno);

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_SCHEDULE . ' ' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $arrBind, false);

        $checked['alarm'][gd_isset($data['alarm'])] = 'checked="checked"';

        //--- 각 데이터 배열화
        $getData['data'] = gd_htmlspecialchars_stripslashes(gd_isset($data));
        $getData['checked'] = $checked;
        $getData['selected'] = $selected;

        return $getData;
    }

    public function getScheduleListByDate($date)
    {
        $arrBind = $getData = $data = $checked = $selected = [];

        $this->db->strField = "s.*,m.scmNo";
        $this->db->strWhere = "scdDt=? AND m.scmNo = ?";
        $this->db->bind_param_push($arrBind, 's', $date);
        $this->db->bind_param_push($arrBind, 'i', \Session::get('manager.scmNo'));

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_SCHEDULE . ' as s  LEFT OUTER JOIN  '.DB_MANAGER.' as m ON s.managerNo = m.sno ' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $arrBind);

        return $data;
    }

    /**
     * 스케줄 등록 / 수정
     *
     * @param int $managerNo 관리자 No
     * @param array $arrData 스케쥴 데이터
     */
    public function addScheduleData($managerNo, $arrData)
    {
        unset($arrData['mode']);
        $arrBind = [];

        $getData = $this->getScheduleListByDate($arrData['scdDt']);
        if ($getData) {
            foreach ($getData as $val) {
                $arrOldSno[] = $val['sno'];
            }
            $diffSno = array_diff((array)$arrOldSno, (array)$arrData['sno']);
            if ($diffSno) {    //기존것과 넘어온것 비교해서 없는 sno는 삭제
                $query = "DELETE FROM " . DB_SCHEDULE . " WHERE sno in (".implode(',', $diffSno).")";
                $this->db->query($query);
                unset($arrBind);
            }
        }

        for ($i = 0; $i < count($arrData['contents']); $i++) {
            if ($sno = $arrData['sno'][$i]) {    //수정
                $query = "UPDATE " . DB_SCHEDULE . " SET scdDt =?,contents =?,managerNo=?,modDt=now() WHERE sno= ?";
                $this->db->bind_param_push($arrBind, 's', $arrData['scdDt']);
                $this->db->bind_param_push($arrBind, 's', $arrData['contents'][$i]);
                $this->db->bind_param_push($arrBind, 'i', $managerNo);
                $this->db->bind_param_push($arrBind, 'i', $sno);
                $this->db->bind_query($query, $arrBind);
                echo 'u';
            } else {    //추가
                echo 'i';
                $query = "INSERT INTO " . DB_SCHEDULE . "(scdDt,contents,managerNo,regDt) VALUES(?,?,?,now())";
                $this->db->bind_param_push($arrBind, 's', $arrData['scdDt']);
                $this->db->bind_param_push($arrBind, 's', $arrData['contents'][$i]);
                $this->db->bind_param_push($arrBind, 'i', $managerNo);
                $this->db->bind_query($query, $arrBind);
            }
            unset($arrBind);
        }
    }

    /**
     * 스케줄등록
     *
     * @param array $arrData
     * @return int 일련번호
     * @throws \Exception
     */
    /*
    public function insertScheduleData($arrData)
    {
        // Validation
        $validator = new Validator();
        $validator->add('scdDt','',true); // 일자
        $validator->add('subject','',true); // 제목
        $validator->add('contents','',true); // 내용
        $validator->add('alarm',''); // 알람
        if ($validator->act($arrData, true) === false) {
            throw new \Exception(implode("\n", $validator->errors));
        }

        // 저장
        $arrBind = $this->db->get_binding(DBTableField::tableSchedule(),$arrData,'insert',array_keys($arrData));
        $this->db->set_insert_db(DB_SCHEDULE, $arrBind['param'], $arrBind['bind'], 'y');
        $sno = $this->db->insert_id();
        return $sno;
    }
    */

    /**
     * 스케줄수정
     *
     * @param array $arrData
     * @throws \Exception
     */
    /*
    public function modifyScheduleData($arrData)
    {
        // Validation
        $validator = new Validator();
        $validator->add('sno','number',true); // 일련번호
        $validator->add('subject','',true); // 제목
        $validator->add('contents','',true); // 내용
        $validator->add('alarm',''); // 알람
        if ($validator->act($arrData, true) === false) {
            throw new \Exception(implode("\n", $validator->errors));
        }

        // 저장
        $arrBind = $this->db->get_binding(DBTableField::tableSchedule(), $arrData, 'update', array_keys($arrData), ['sno']);
        $this->db->bind_param_push($arrBind['bind'], 'i', $arrData['sno']);
        $this->db->set_update_db(DB_SCHEDULE, $arrBind['param'], 'sno=?', $arrBind['bind'], false);
    }
    */

    /**
     * 스케줄삭제
     * @param int $sno 일련번호
     * @throws \Exception
     */
    /*
    public function deleteScheduleData($sno)
    {
        if (Validator::number($sno, null, null, true) === false) {
            throw new \Exception(sprintf(__('%s 항목이 잘못 되었습니다.'), '스케줄 번호'));
        }
        $arrBind = ['i', $sno];
        $this->db->set_delete_db(DB_SCHEDULE, 'sno = ?', $arrBind);
    }
    */

    /**
     * 알람 팝업창 여부
     * @TODO:수정 오늘것만 체크
     * @return mixed 날짜/bool
     */
    public function isAlarmPopup()
    {
        // 알람 사용 여부
        if ($this->cfg['alarmUseFl'] !== 'y') {
            return false;
        }

        // 팝업 알람 날짜가 없는 경우
        if (empty($this->cfg['alarmUseFl']) === true) {
            return false;
        }

        // 팝업 알람 날짜 설정
        $arrBind = [];
        $thisTime = time();
        $alarmTime = strtotime('+' . ($this->cfg['dDayPopup'] - 1) . ' day', time());
        $thisDate = date('Y-m-d', $thisTime);
        $alarmDate = date('Y-m-d', $alarmTime);

        if ($this->cfg['dDayPopup'] == 1) {
            $strWhere = 'scdDt = ?';
            $this->db->bind_param_push($arrBind, 's', $alarmDate);
        } else {
            $strWhere = 'scdDt BETWEEN ? AND ?';
            $this->db->bind_param_push($arrBind, 's', $thisDate);
            $this->db->bind_param_push($arrBind, 's', $alarmDate);
        }

        $strSQL = "SELECT scdDt FROM " . DB_SCHEDULE . " WHERE " . $strWhere . " AND alarm = 'y'";
        $data = $this->db->query_fetch($strSQL, $arrBind, false);

        if (empty($data['scdDt']) === false) {
            return $data['scdDt'];
        } else {
            return false;
        }
    }
}
