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

use Component\Storage\Storage;
use Component\Validator\Validator;
use Component\Database\DBTableField;
use Component\Page\Page;
use Globals;
use Request;
use Message;
use FileHandler;
use Cookie;
use Session;
use UserFilePath;

/**
 * 팝업 관리 클래스
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class DesignMultiPopup extends \Component\Design\SkinBase
{
    // 창 종류
    // __('멀티 고정 레이어창')
    // __('멀티 이동 레이어창')
    // __('멀티 윈도우 팝업창')
    // __('이동하지않음(고정)')
    // __('오른쪽에서 왼쪽으로 이동 ')
    // __('왼쪽에서 오른쪽으로 이동')
    // __('아래쪽에서 위쪽으로 이동')
    // __('위쪽에서 아래쪽으로 이동')
    public $popupKindFl = ['layer' => '멀티 고정 레이어창', 'move' => '멀티 이동 레이어창', 'window' => '멀티 윈도우 팝업창'];
    public $popupSlideDirection= ['none' => '이동하지않음(고정)', 'left' => '오른쪽에서 왼쪽으로 이동 ', 'right' => '왼쪽에서 오른쪽으로 이동', 'up' => '아래쪽에서 위쪽으로 이동', 'down' => '위쪽에서 아래쪽으로 이동',];
    public $popupSlideCount= ['21' => '2 X 1', '22' => '2 X 2', '31' => '3 X 1', '32' => '3 X 2', '41' => '4 X 1', '42' => '4 X 2',];

    // 팝업창 쿠키 prefix
    public $popupCodePrefix = 'multiPopupCode_';


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
        $page->recode['amount'] = $this->db->getCount(DB_DESIGN_MULTI_POPUP); // 전체 레코드 수
        $page->setPage();
        $page->setUrl(\Request::getQueryString());

        $this->db->strField = implode(', ', DBTableField::setTableField('tableDesignMultiPopup'));
        $this->db->strWhere = implode(' AND ', gd_isset($arrWhere));
        $this->db->strOrder = $sort;
        $this->db->strLimit = $page->recode['start'] . ',' . $search['pageNum'];

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_DESIGN_MULTI_POPUP . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $arrBind);

        $this->db->strField = 'count(*) as cnt';
        $this->db->strWhere = implode(' AND ', gd_isset($arrWhere));

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_DESIGN_MULTI_POPUP . implode(' ', $query);
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

        $arrField = DBTableField::setTableField('tableDesignMultiPopup');
        $strSQL = 'SELECT ' . implode(', ', $arrField) . ' FROM ' . DB_DESIGN_MULTI_POPUP . ' WHERE ' . implode(' AND ', $arrWhere) . ' ORDER BY sno DESC';
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


    public function saveMultiPopupImage() {
        $arrFileData = Request::files()->toArray();
        $postValue  = Request::post()->toArray();

        if($postValue['imageUploadFl'] == 'y') {
            foreach ($arrFileData as $fKey => $fVal) {
                if (gd_file_uploadable($fVal,'image') === true) {

                    $imageExt = strrchr($fVal['name'], '.');
                    $newImageName = $fKey."_".$postValue['sno'].$imageExt; // 이미지 공백 제거
                    $targetImageFile = "tmp".DS. Session::get('manager.sno').DS. $newImageName;
                    $tmpImageFile = $fVal['tmp_name'];

                    Storage::disk(Storage::PATH_CODE_MULTI_POPUP,'local')->upload($tmpImageFile,$targetImageFile);
                    $postValue[$fKey] = $targetImageFile."?ts=".time();
                }
            }

            // 계정용량 갱신 - 멀티팝업
            gd_set_du('multi_popup');
        }

        $postValue['imagePath'] = UserFilePath::data('multi_popup')->www().DS;

        return $postValue;
    }

    /**
     * 팝업 정보 저장
     * @param array $postValue 저장할 정보
     * @return int sno
     * @throws \Exception
     */
    public function saveMultiPopupData(array $postValue)
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
        if (empty($postValue['popupSlideViewW']) === true) {
            $dataCheck = false;
        }
        if (empty($postValue['popupSlideViewH']) === true) {
            $dataCheck = false;
        }
        if (empty($postValue['popupSlideThumbH']) === true) {
            $dataCheck = false;
        }


        if ($dataCheck === false) {
            throw new \Exception(sprintf(__('%s 항목이 잘못 되었습니다.'), '팝업 정보'));
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

        $imageArr = ['thumbImage1','thumbImage2','mainImage'];
        $tmpImagePath = "tmp".DS. Session::get('manager.sno').DS;

        // 이미지 업로드 체크
        $checkImage = false;

        foreach($postValue['image'] as $k => $v) {
            if($v['imageUploadFl'] =='y') {
                foreach($imageArr as $k1 => $v1) {
                    if($v[$v1] && $v['tmpFl'] =='y' && strpos($v[$v1],$tmpImagePath) !== false) {
                        $newThumbImage1 = str_replace($tmpImagePath.$v1,$v1.time(),explode("?ts",$v[$v1])[0]);
                        Storage::copy(Storage::PATH_CODE_MULTI_POPUP, 'local', explode("?ts",$v[$v1])[0], 'local', $newThumbImage1);
                        Storage::disk(Storage::PATH_CODE_MULTI_POPUP,'local')->delete(explode("?ts",$v[$v1])[0]);
                        $postValue['image'][$k][$v1] = $newThumbImage1;

                        // 이미지 업로드 체크
                        $checkImage = true;
                    }
                }
            }
        }

        if ($checkImage === true) {
            // 계정용량 갱신 - 멀티팝업
            gd_set_du('multi_popup');
        }

        $postValue['popupImageInfo'] = json_encode($postValue['image'], JSON_UNESCAPED_UNICODE);

        $arrBind = $this->db->get_binding(DBTableField::tableDesignMultiPopup(), $postValue, $chkType);

        if ($chkType == 'insert') {
            $this->db->set_insert_db(DB_DESIGN_MULTI_POPUP, $arrBind['param'], $arrBind['bind'], 'y');
            $postValue['sno'] = $this->db->insert_id();
        } elseif ($chkType == 'update') {
            $this->db->bind_param_push($arrBind['bind'], 'i', $postValue['sno']);
            $this->db->set_update_db(DB_DESIGN_MULTI_POPUP, $arrBind['param'], 'sno = ?', $arrBind['bind']);
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
    public function deleteMultiPopupData($sno)
    {
        // Validation
        if (Validator::number($sno, null, null, true) === false) {
            throw new \Exception(sprintf(__('%s 항목이 잘못 되었습니다.'), '팝업 번호'));
        }

        $getData = $this->getPopupDetailData($sno);

        // 디비 삭제
        $arrBind = [];
        $arrField = ['sno = ?'];
        $this->db->bind_param_push($arrBind, 'i', $sno);
        $this->db->set_delete_db(DB_DESIGN_MULTI_POPUP, implode(' AND ', $arrField), $arrBind);

        //이미지 삭제
        $getData['image'] =json_decode($getData['popupImageInfo'], true);
        $imageArr = ['thumbImage1','thumbImage2','mainImage'];
        foreach($getData['image'] as $k => $v) {
            if($v['imageUploadFl'] =='y') {
                foreach($imageArr as $k1 => $v1) {
                    if($v[$v1]) {
                        Storage::disk(Storage::PATH_CODE_MULTI_POPUP,'local')->delete($v[$v1]);
                    }
                }
            }
        }


        return true;
    }

    /**
     * 출력 가능한 팝업 데이타 추출
     * @param string $currentUrl 현재 페이지
     * @return array 팝업 데이타
     * @internal param string $postData 정보
     */
    public function getUseMultiPopupData($currentUrl)
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
        } else {
            $arrWhere[] = 'pcDisplayFl = \'y\'';
        }

        // 출력여부 검색
        $arrWhere[] = 'popupUseFl = \'y\'';

        // 팝업 노출 페이지 검색
        if ($entryType == 'mobile') {
            $arrWhere[] = 'mobilePopupPageUrl = ?';
        } else {
            $arrWhere[] = 'popupPageUrl = ?';
        }

        $this->db->bind_param_push($arrBind, 's', $currentPage);

        $arrWhere[] = '((popupPeriodOutputFl = \'n\') OR
            (popupPeriodOutputFl = \'y\' AND (now() BETWEEN concat(popupPeriodSDate, \' \', popupPeriodSTime) AND concat(popupPeriodEDate, \' \', popupPeriodETime)))
            OR
            (popupPeriodOutputFl = \'t\' AND (curdate() BETWEEN popupPeriodSDate AND popupPeriodEDate) AND (curtime() BETWEEN popupPeriodSTime AND popupPeriodETime)))';

        $this->db->strField = implode(', ', DBTableField::setTableField('tableDesignMultiPopup', null, ['modDt', 'regDt']));
        $this->db->strWhere = implode(' AND ', $arrWhere);

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_DESIGN_MULTI_POPUP . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $arrBind);
        unset($arrBind);

        $getData = [];
        $indexKey = 0;
        if (empty($data) === false) {
            foreach ($data as $pKey => $pVal) {
                $checkUnset = false;
                // 파라메터 체크
                if (($entryType == 'front' && empty($pVal['popupPageParam']) == false) || ($entryType == 'mobile' && empty($pVal['mobilePopupPageParam']) == false)) {
                    if (empty($queryString) === true) {
                        $checkUnset = true;
                    } else {
                        if ($entryType == 'mobile') {
                            $checkParam = explode('&', $pVal['mobilePopupPageParam']);
                        } else {
                            $checkParam = explode('&', $pVal['popupPageParam']);
                        }
                        $currentParam = explode('&', $queryString);
                        foreach ($currentParam as $cVal) {
                            if (in_array($cVal, $checkParam) === false) {
                                $checkUnset = true;
                            }
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


                $popupSlideCountHeight= substr( $pVal['popupSlideCount'],1,1);

                // 반환 데이터
                $getData[$indexKey]['sno'] =  $pVal['sno'];
                $getData[$indexKey]['popupKindFl'] =  $pVal['popupKindFl'];
                $getData[$indexKey]['popupSizeW'] =  $pVal['popupSlideViewW'];
                $getData[$indexKey]['popupSizeH'] =  $pVal['popupSlideViewH']+($popupSlideCountHeight*$pVal['popupSlideThumbH']);
                $getData[$indexKey]['popupPositionT'] =  $pVal['popupPositionT'];
                $getData[$indexKey]['popupPositionL'] =  $pVal['popupPositionL'];
                $getData[$indexKey]['todayUnSee'] =  $pVal['todayUnSeeFl'];
                $getData[$indexKey]['popupCode'] =  $popupCode;

                $indexKey++;
            }
        }

        return $getData;
    }
}
