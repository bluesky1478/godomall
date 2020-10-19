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

namespace Bundle\Component\Design;

use Component\Validator\Validator;
use Component\Database\DBTableField;
use Component\Page\Page;
use Globals;
use League\Flysystem\Exception;
use Request;
use Message;
use UserFilePath;
use FileHandler;
use Cookie;

/**
 * 팝업 관리 클래스
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class DesignPopup extends \Component\Design\SkinBase
{
    // 창 종류
    // __('고정 레이어창')
    // __('이동 레이어창')
    // __('윈도우 팝업창')
    public $popupKindFl = ['layer' => '고정 레이어창', 'move' => '이동 레이어창', 'window' => '윈도우 팝업창',];

    // 팝업창 쿠키 prefix
    public $popupCodePrefix = 'popupCode_';

    public $sizeType = ['px' => 'pixel', '%' => '%',];

    /**
     * 팝업 리스트
     * @return array
     */
    public function getPopupListData($menuType = null)
    {
        $getValue = Request::get()->toArray();

        if (empty($getValue) === true) {
            if ($menuType == 'mobile') $getValue['displayFl'] = $menuType;
            else $getValue['displayFl'] = 'front';
        }

        // 검색 설정
        $setGetKey = ['detailSearch','key', 'keyword', 'printDt', 'popupUseFl', 'popupKindFl', 'popupSkin', 'treatDateFl', 'sort', 'page','pageNum', 'displayFl',];
        $setGetKey2 = ['treatDate' => ['start', 'end']];
        $search = [];
        $checked = [];
        $selected = [];

        //검색설정
        $search['sortList'] = [
            'regDt desc' => __('등록일 ↑'),
            'regDt asc' => __('등록일 ↓'),
            'modDt desc' => __('수정일 ↑'),
            'modDt asc' => __('수정일 ↓'),
            'groupSno desc' => __('배너그룹코드 ↑'),
            'groupSno asc' => __('배너그룹코드 ↓'),
            'bannerGroupType desc' => __('그룹타입코드 ↑'),
            'bannerGroupType asc' => __('그룹타입코드 ↓'),
        ];

        foreach ($setGetKey as $gVal) {
            if (isset($getValue[$gVal]) === true) {
                $search[$gVal] = $getValue[$gVal];
            } else {
                $search[$gVal] = '';
            }
        }
        foreach ($setGetKey2 as $gKey => $gVal) {
            foreach ($gVal as $aVal) {
                if (isset($getValue[$gKey][$aVal]) === true) {
                    $search[$gKey][$aVal] = $getValue[$gKey][$aVal];
                } else {
                    $search[$gKey][$aVal] = '';
                }
            }
        }

        $checked['popupUseFl'][$search['popupUseFl']] = 'checked="checked"';
        $checked['popupKindFl'][$search['popupKindFl']] = 'checked="checked"';
        $checked['displayFl'][$search['displayFl']] = 'checked="checked"';

        $selected['popupSkin'][$search['popupSkin']] = 'selected="selected"';

        // 스킨 검색
        $arrWhere = [];
        $arrBind = [];

        // 키워드 검색
        if ($search['key'] && $search['keyword']) {
            if ($search['key'] == 'all') {
                $arrWhere[] = "popupTitle LIKE concat('%',?,'%')";
                $this->db->bind_param_push($arrBind, 's', $search['keyword']);
            } else {
                $arrWhere[] = $search['key']." LIKE concat('%',?,'%')";
                $this->db->bind_param_push($arrBind, 's', $search['keyword']);
            }
        }

        // 출력여부 검색
        if ($search['popupUseFl']) {
            $arrWhere[] = 'popupUseFl = ?';
            $this->db->bind_param_push($arrBind, 's', $search['popupUseFl']);
        }

        // 출력일자 검색
        if ($search['printDt']) {
            $arrWhere[] = '? BETWEEN popupPeriodSDate AND popupPeriodEDate';
            $this->db->bind_param_push($arrBind, 's', $search['printDt']);
        }

        // 창종류 검색
        if ($search['popupKindFl']) {
            $arrWhere[] = 'popupKindFl = ?';
            $this->db->bind_param_push($arrBind, 's', $search['popupKindFl']);
        }

        // 팝업창 스킨 검색
        if ($search['popupSkin']) {
            $arrWhere[] = 'popupSkin = ?';
            $this->db->bind_param_push($arrBind, 's', $search['popupSkin']);
        }

        // 기간검색
        if ($search['treatDateFl'] && $search['treatDate']['start'] && $search['treatDate']['end']) {
            $arrWhere[] = '(' . $search['treatDateFl'] . ' BETWEEN ? AND ?)';
            $this->db->bind_param_push($arrBind, 's', $search['treatDate']['start'] . ' 00:00:00');
            $this->db->bind_param_push($arrBind, 's', $search['treatDate']['end'] . ' 23:59:59');
        }

        //구분검색
        if ($search['displayFl']) {
            if ($search['displayFl'] == 'mobile') {
                $arrWhere[] = 'mobileDisplayFl = ?';
                $this->db->bind_param_push($arrBind, 's', 'y');
            } else {
                $arrWhere[] = 'pcDisplayFl = ?';
                $this->db->bind_param_push($arrBind, 's', 'y');
            }
        }

        // --- 정렬 설정
        $sort = gd_isset($search['sort'], 'regDt desc');

        // --- 페이지 기본설정
        if (empty($search['page']) === true) {
            $search['page'] = 1;
        }
        if (empty($search['pageNum']) === true) {
            $search['pageNum'] = 10;
        }

        $page = new Page($search['page']);
        $page->page['list'] = $search['pageNum']; // 페이지당 리스트 수
        $page->recode['amount'] = $this->db->table_status(DB_DESIGN_POPUP, 'Rows'); // 전체 레코드 수
        $page->setPage();
        $page->setUrl(\Request::getQueryString());

        $this->db->strField = implode(', ', DBTableField::setTableField('tableDesignPopup'));
        $this->db->strWhere = implode(' AND ', gd_isset($arrWhere));
        $this->db->strOrder = $sort;
        $this->db->strLimit = $page->recode['start'] . ',' . $search['pageNum'];

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_DESIGN_POPUP . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $arrBind);

        $this->db->strField = 'count(*) as cnt';
        $this->db->strWhere = implode(' AND ', gd_isset($arrWhere));

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_DESIGN_POPUP . implode(' ', $query);
        $countData = $this->db->query_fetch($strSQL, $arrBind,false);
        unset($arrBind);

        // 검색 레코드 수
        $page->recode['total'] = $countData['cnt'];
        $page->setPage();

        // 각 데이터 배열화
        $getData['data'] = gd_htmlspecialchars_stripslashes($data);
        $getData['page'] = $page;
        $getData['sort'] = $sort;
        $getData['search'] = gd_htmlspecialchars($search);
        $getData['checked'] = $checked;
        $getData['selected'] = $selected;

        return $getData;
    }

    /**
     * 팝업 상세 데이타
     *
     * @param integer $sno 페이지 번호
     * @return array|boolean
     * @throws \Exception
     */
    public function getPopupDetailData($sno)
    {
        // Validation
        if (Validator::number($sno, null, null, true) === false) {
            throw new \Exception(sprintf(__('%s 항목이 잘못 되었습니다.'), '페이지 번호'));
        }

        // 스킨 정보
        if (empty($this->skinPath) === true) {
            $this->setSkin(Globals::get('gSkin.' . $this->skinType . 'SkinWork'));
        }

        // Data
        $arrBind = $arrWhere = [];
        array_push($arrWhere, 'sno = ?');
        $this->db->bind_param_push($arrBind, 'i', $sno);

        $arrField = DBTableField::setTableField('tableDesignPopup');
        $strSQL = 'SELECT ' . implode(', ', $arrField) . ' FROM ' . DB_DESIGN_POPUP . ' WHERE ' . implode(' AND ', $arrWhere) . ' ORDER BY sno DESC';
        $getData = $this->db->query_fetch($strSQL, $arrBind);

        if (empty($getData) === false) {
            return gd_htmlspecialchars_stripslashes($getData[0]);
        } else {
            return false;
        }
    }

    /**
     * 팝업 노출 페이지
     * @return array  페이지 정보
    */
    public function getPopupPageOutput()
    {
        // 팝업 노출 페이지 정보
        $popupPageFile = UserFilePath::data('conf', 'popup') . DS . 'popup_page.txt';
        $setData = [];
        $getData = FileHandler::read($popupPageFile);
        if (empty($getData) === false) {
            $getData = explode(PHP_EOL, $getData);
            foreach ($getData as $iVal) {
                if (empty($iVal) === false) {
                    $tmp = explode(':', $iVal);
                    if (isset($tmp[1]) === true) {
                        $setData[trim($tmp[0])] = trim($tmp[1]);
                    }
                }
            }
        }

        return $setData;
    }

    /**
     * 팝업 정보 저장
     * @param array $postValue 저장할 정보
     * @return int sno
     * @throws \Exception
     */
    public function savePopupData(array $postValue)
    {
        // 기본 테이터 체크
        $dataCheck = true;
        if (empty($postValue['popupTitle']) === true) {
            $dataCheck = false;
        }
        if (empty($postValue['popupUseFl']) === true) {
            $dataCheck = false;
        }
        if (empty($postValue['popupSkin']) === true) {
            $dataCheck = false;
        }
        if (empty($postValue['popupSizeW']) === true) {
            $dataCheck = false;
        }
        if (empty($postValue['popupSizeH']) === true) {
            $dataCheck = false;
        }
        if ($postValue['mode'] === 'modify' && empty($postValue['sno']) === true) {
            $dataCheck = false;
        }

        if ($dataCheck === false) {
            throw new \Exception(sprintf(__('%s 항목이 잘못 되었습니다.'), '팝업 정보'));
        }
        $gGlobal = Globals::get('gGlobal');
        if($gGlobal['isUse']) {
            if(in_array('all', $postValue['mallDisplay'])) $postValue['mallDisplay'] = implode(",", array_keys($gGlobal['useMallList']));
            else $postValue['mallDisplay'] = implode(",", $postValue['mallDisplay']);
        }
        if (empty($postValue['popupPositionT']) === true) {
            $postValue['popupPositionT'] = '0';
        }
        if (empty($postValue['popupPositionL']) === true) {
            $postValue['popupPositionL'] = '0';
        }
        if (empty($postValue['popupPeriodOutputFl']) === true) {
            $postValue['popupPeriodOutputFl'] = 'n';
        }

        // 날짜 처리
        if ($postValue['popupPeriodOutputFl'] === 'y') {
            $postValue['popupPeriodSDate'] = $postValue['popupPeriodSDateY'];
            $postValue['popupPeriodSTime'] = $postValue['popupPeriodSTimeY'] . ':00';
            $postValue['popupPeriodEDate'] = $postValue['popupPeriodEDateY'];
            $postValue['popupPeriodETime'] = $postValue['popupPeriodETimeY'] . ':00';
        } elseif ($postValue['popupPeriodOutputFl'] === 't') {
            $postValue['popupPeriodSDate'] = $postValue['popupPeriodSDateT'];
            $postValue['popupPeriodSTime'] = $postValue['popupPeriodSTimeT'] . ':00';
            $postValue['popupPeriodEDate'] = $postValue['popupPeriodEDateT'];
            $postValue['popupPeriodETime'] = $postValue['popupPeriodETimeT'] . ':00';
        } else {
            $postValue['popupPeriodSDate'] = '';
            $postValue['popupPeriodSTime'] = '';
            $postValue['popupPeriodEDate'] = '';
            $postValue['popupPeriodETime'] = '';
        }

        if (empty($postValue['pcDisplayFl']) === true) {
            $postValue['pcDisplayFl'] = 'n';
        }

        if (empty($postValue['mobileDisplayFl']) === true) {
            $postValue['mobileDisplayFl'] = 'n';
        }

        // insert , update 체크
        if ($postValue['mode'] == 'modify') {
            $chkType = 'update';
        } else {
            $chkType = 'insert';
        }

        $arrBind = $this->db->get_binding(DBTableField::tableDesignPopup(), $postValue, $chkType);

        if ($chkType == 'insert') {
            $this->db->set_insert_db(DB_DESIGN_POPUP, $arrBind['param'], $arrBind['bind'], 'y');
            $postValue['sno'] = $this->db->insert_id();
        } elseif ($chkType == 'update') {
            $this->db->bind_param_push($arrBind['bind'], 'i', $postValue['sno']);
            $this->db->set_update_db(DB_DESIGN_POPUP, $arrBind['param'], 'sno = ?', $arrBind['bind']);
        }
        unset($arrBind);

        return $postValue['sno'];
    }

    /**
     * 팝업 삭제
     * @param integer $sno 팝업 번호
     * @return boolean
     * @throws \Exception
     */
    public function deletePopupData($sno)
    {
        // Validation
        if (Validator::number($sno, null, null, true) === false) {
            throw new \Exception(sprintf(__('%s 항목이 잘못 되었습니다.'), '팝업 번호'));
        }

        // 디비 삭제
        $arrBind = [];
        $arrField = ['sno = ?'];
        $this->db->bind_param_push($arrBind, 'i', $sno);
        $this->db->set_delete_db(DB_DESIGN_POPUP, implode(' AND ', $arrField), $arrBind);

        return true;
    }

    /**
     * 출력 가능한 팝업 데이타 추출
     * @param string $currentUrl 현재 페이지
     * @return array 팝업 데이타
     * @internal param string $postData 정보
     */
    public function getUsePopupData($currentUrl)
    {
        if (empty($currentUrl) === true) {
            return false;
        }

        // 모바일 접속 여부
        if (Request::isMobile() === true) {
            $entryType = 'mobile';
        } else {
            $entryType = 'front';
        }

        // 검색 기본 값
        $arrWhere = [];
        $arrBind = [];
        $queryString = '';

        $tmp = parse_url($currentUrl);
        $currentPage =str_replace('/', '', strrchr(dirname($tmp['path']), '/')) . '/' .  basename($tmp['path']);
        if (isset($tmp['query']) === true){
            $queryString = $tmp['query'];
        }

        // '/' 인 경우
        if ($currentPage === '/') {
            $currentPage = gd_entryway($entryType) . '.php';
        }

        // 메인 페이지 인경우
        if ($currentPage === '/main') {
            $currentPage = 'main/index.php';
        }

        if ($entryType === 'mobile') {
            $arrWhere[] = 'mobileDisplayFl = \'y\'';
            $arrWhere[] = 'mobilePopupPageUrl = ?';
        } else {
            $arrWhere[] = 'pcDisplayFl = \'y\'';
            $arrWhere[] = 'popupPageUrl = ?';
        }
        // 팝업 노출 페이지 검색
        $this->db->bind_param_push($arrBind, 's', $currentPage);

        // 출력여부 검색
        $arrWhere[] = 'popupUseFl = \'y\'';

        $arrWhere[] = '((popupPeriodOutputFl = \'n\') OR
            (popupPeriodOutputFl = \'y\' AND (now() BETWEEN concat(popupPeriodSDate, \' \', popupPeriodSTime) AND concat(popupPeriodEDate, \' \', popupPeriodETime)))
            OR
            (popupPeriodOutputFl = \'t\' AND (curdate() BETWEEN popupPeriodSDate AND popupPeriodEDate) AND (curtime() BETWEEN popupPeriodSTime AND popupPeriodETime)))';


        $gGlobal = \Globals::get('gGlobal');
        if ($gGlobal['isUse'] === true) {
            $mallSno = gd_isset(\Component\Mall\Mall::getSession('sno'), 1);
            $arrWhere[] = 'FIND_IN_SET(?, mallDisplay)';
            $this->db->bind_param_push($arrBind, 'i', $mallSno);
        }

        $this->db->strField = implode(', ', DBTableField::setTableField('tableDesignPopup', null, ['modDt', 'regDt']));
        $this->db->strWhere = implode(' AND ', $arrWhere);

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_DESIGN_POPUP . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $arrBind);
        unset($arrBind);

        $getData = [];
        $indexKey = 0;
        if (empty($data) === false) {
            foreach ($data as $pKey => $pVal) {
                $checkUnset = false;
                // 파라메터 체크
                $popupPageParam = $entryType == 'mobile' ? $pVal['mobilePopupPageParam'] : $pVal['popupPageParam'];
                if (empty($popupPageParam) === false) {
                    if (empty($queryString) === true) {
                        $checkUnset = true;
                    } else {
                        $checkParam = explode('&', $popupPageParam);
                        $currentParam = explode('&', $queryString);
                        $existsParam = false;
                        foreach ($currentParam as $cVal) {
                            if (in_array($cVal, $checkParam) === true) {
                                $existsParam = true;
                                break;
                            }
                        }

                        if($existsParam === false ){
                            $checkUnset = true;
                        }
                    }
                }
                // 쿠키 체크
                $popupCode = $this->popupCodePrefix . $pVal['popupKindFl'] . '_' . $pVal['sno'];
                if(Cookie::has($popupCode) === true) {
                    $checkUnset = true;
                }

                // 체크 결과
                if ($checkUnset === true) {
                    unset($data[$pKey]);
                    continue;
                }

                // 반환 데이터
                $getData[$indexKey]['sno'] =  $pVal['sno'];
                $getData[$indexKey]['popupKindFl'] =  $pVal['popupKindFl'];
                $getData[$indexKey]['popupSkin'] =  $pVal['popupSkin'];
                $getData[$indexKey]['popupSizeW'] =  $pVal['popupSizeW'];
                $getData[$indexKey]['popupSizeH'] =  $pVal['popupSizeH'];
                $getData[$indexKey]['sizeTypeW'] =  $pVal['sizeTypeW'];
                $getData[$indexKey]['sizeTypeH'] =  $pVal['sizeTypeH'];
                $getData[$indexKey]['popupPositionT'] =  $pVal['popupPositionT'];
                $getData[$indexKey]['popupPositionL'] =  $pVal['popupPositionL'];
                $getData[$indexKey]['popupBgColor'] =  $pVal['popupBgColor'];
                $getData[$indexKey]['mobilePopupKindFl'] =  $pVal['mobilePopupKindFl'];
                $getData[$indexKey]['mobilePopupSkin'] =  $pVal['mobilePopupSkin'];
                $getData[$indexKey]['mobilePopupSizeW'] =  $pVal['mobilePopupSizeW'];
                $getData[$indexKey]['mobilePopupSizeH'] =  $pVal['mobilePopupSizeH'];
                $getData[$indexKey]['mobileSizeTypeW'] =  $pVal['mobileSizeTypeW'];
                $getData[$indexKey]['mobileSizeTypeH'] =  $pVal['mobileSizeTypeH'];
                $getData[$indexKey]['mobilePopupPositionT'] =  $pVal['mobilePopupPositionT'];
                $getData[$indexKey]['mobilePopupPositionL'] =  $pVal['mobilePopupPositionL'];
                $getData[$indexKey]['mobilePopupBgColor'] =  $pVal['mobilePopupBgColor'];
                $getData[$indexKey]['todayUnSee'] =  $pVal['todayUnSeeFl'];
                $getData[$indexKey]['popupCode'] =  $popupCode;

                $indexKey++;
            }
        }

        return $getData;
     }

    public function pagePopupRegist($param)
    {
        if (empty($param['pageName']) === true) {
            throw new Exception('페이지명을 입력해주세요');
        }
        if (empty($param['pageUrl']) === true) {
            throw new Exception('URL을 입력해주세요');
        }

        $arrData['pcDisplayFl'] = $param['pcDisplayFl'];
        $arrData['mobileDisplayFl'] = $param['mobileDisplayFl'];
        $arrData['pageName'] = $param['pageName'];
        $arrData['pageUrl'] = $param['pageUrl'];
        $pageResetNo = $param['sno'];

        if (empty($param['sno']) === true) {
            $arrBind = $this->db->get_binding(DBTableField::tablePopupPage(), $arrData, 'insert');
            $this->db->set_insert_db(DB_POPUP_PAGE, $arrBind['param'], $arrBind['bind'], 'y');
            $param['sno'] = $this->db->insert_id();
        } else {
            $arrBind = $this->db->get_binding(DBTableField::tablePopupPage(), $arrData, 'update', array_keys($arrData));
            $this->db->bind_param_push($arrBind['bind'], 'i', $param['sno']);
            $this->db->set_update_db(DB_POPUP_PAGE, $arrBind['param'], 'sno = ?', $arrBind['bind']);
        }

        return ['sno' => $param['sno'], 'pageResetNo' => $pageResetNo, 'pcDisplayFl' => $param['pcDisplayFl'], 'mobileDisplayFl' => $param['mobileDisplayFl'], 'pageName' => $param['pageName'], 'pageUrl' => $param['pageUrl']];
    }

    public function getPopupPage($param, $arrInclude = null, $limitFl = true)
    {
        $param['page'] = gd_isset($param['page'], 1);
        $param['pageNum'] = gd_isset($param['pageNum'], 10);

        $arrField = DBTableField::setTableField('tablePopupPage', $arrInclude);
        $arrBind = $arrWhere = [];
        if (empty($param['sno']) === false) {
            $arrWhere[] = 'sno = ?';
            $this->db->bind_param_push($arrBind, 'i', $param['sno']);
        }

        $this->db->strField = implode(', ', $arrField);
        $this->db->strWhere = implode(' AND ', gd_isset($arrWhere));
        $this->db->strOrder = 'sno desc';
        if ($limitFl === true) {
            $page = \App::load('\\Component\\Page\\Page', $param['page']);
            $page->page['list'] = $param['pageNum']; // 페이지당 리스트 수

            $strSQL = ' SELECT COUNT(sno) AS cnt FROM ' . DB_POPUP_PAGE ;
            $res = $this->db->slave()->query_fetch($strSQL, null, false);
            $page->recode['amount'] = $res['cnt']; // 전체 레코드 수
            $page->setPage();
            $page->setUrl(\Request::getQueryString());

            $this->db->strLimit = $page->recode['start'] . ',' . $param['pageNum'];
        }

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ', regDt, modDt FROM ' . DB_POPUP_PAGE . implode(' ', $query);

        $data = $this->db->slave()->query_fetch($strSQL, $arrBind, true);
        $getData['total'] = $res['cnt'];
        $getData['data'] = $data;
        unset($page);

        return $getData;
    }

    public function getPagePopupArr($param, $arrInclude = null, $limitFl = true)
    {
        $getData = $this->getPopupPage($param, $arrInclude, $limitFl);
        $data = [];

        foreach ($getData['data'] as $value) {
            $data[$value['pageName']] = $value['pageUrl'];
        }

        return $data;
    }

    public function pagePopupDelete($param)
    {
        if (empty($param) === true) {
            throw new Exception('삭제할 리스트를 선택해주세요.');
        }
        $retParam = [];

        foreach ($param['del'] as $value) {
            $arrBind = [];
            $this->db->bind_param_push($arrBind, 'i', $value);
            $this->db->set_delete_db(DB_POPUP_PAGE, 'sno = ?', $arrBind);
            $retParam[] = $value;
        }

        return $retParam;
    }
}
