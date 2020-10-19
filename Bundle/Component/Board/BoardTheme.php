<?php
/**
 * This is commercial software, only users who have purchased a valid license
 * and accept to the terms of the License Agreement can install and use this
 * program.
 *
 * Do not edit or add to this file if you wish to upgrade Smart to newer
 * versions in the future.
 *
 * @copyright ⓒ 2016, NHN godo: Corp.
 * @link http://www.godo.co.kr
 */

/**
 * 게시판 테마 class
 *
 * @author sunny
 * @version 1.0
 * @since 1.0
 * @copyright ⓒ 2016, NHN godo: Corp.
 */

namespace Bundle\Component\Board;

use Component\Design\SkinBase;
use Component\Database\DBTableField;
use Component\Validator\Validator;
use FileHandler;
use UserFilePath;
use Globals;

define('BOARD_THEME_ICON_NOTICE', __('공지글'));
define('BOARD_THEME_ICON_SECRET', __('비밀글'));
define('BOARD_THEME_ICON_ATTACH', __('첨부파일'));
define('BOARD_THEME_ICON_IMAGE', __('이미지'));
define('BOARD_THEME_ICON_NEW', __('NEW'));
define('BOARD_THEME_ICON_HOT', __('HOT'));
define('BOARD_THEME_ICON_REPLY', __('답변글'));

class BoardTheme
{
    const ICON_TYPE_LIST = [BOARD_THEME_ICON_NOTICE => 'notice', BOARD_THEME_ICON_SECRET => 'secret', BOARD_THEME_ICON_ATTACH => 'attach_file', BOARD_THEME_ICON_IMAGE => 'attach_img', BOARD_THEME_ICON_NEW => 'new', BOARD_THEME_ICON_HOT => 'hot', BOARD_THEME_ICON_REPLY => 're'];

    protected $db;

    public function __construct()
    {
        if (!is_object($this->db)) {
            $this->db = \App::load('DB');
        }
        $this->fieldTypes = DBTableField::getFieldTypes('tableBoardTheme');
    }

    public static function getKindText($type)
    {
        $boardKindList = Board::KIND_LIST;
        return $boardKindList[$type];
    }

    /**
     * 테마 리스트
     *
     * @author sunny
     * @param null $req
     * @return array 테마 리스트 정보
     */
    public function getList($req = null)
    {
        // --- 정렬 설정
        $sort['fieldName'] = $req['sort.name'];
        $sort['sortMode'] = $req['sort.mode'];
        if (empty($sort['fieldName']) || empty($sort['sortMode'])) {
            $sort['fieldName'] = 'regDt';
            $sort['sortMode'] = 'desc';
        }

        $arrBind = [];


        if ($req['keyword']) {
            if (in_array($req['searchField'], ['themeId', 'themeNm'])) {
                $arrWhere[] = $req['searchField'] . " like concat('%',?,'%') ";
                $this->db->bind_param_push($arrBind, 's', $req['keyword']);
            }
        }

        if ($req['deviceType']) {
            if ($req['deviceType'] == 'pc') {
                $arrWhere[] = 'bdMobileFl = ? ';
                $this->db->bind_param_push($arrBind, 's', 'n');
            } else if ($req['deviceType'] == 'mobile') {
                $arrWhere[] = 'bdMobileFl = ? ';
                $this->db->bind_param_push($arrBind, 's', 'y');
            }
        }

        if (\Globals::get('gGlobal.isUse')) {
            $req['deviceType'] = $req['deviceType'] ??  'pc';  //값이 없으면 디폴트 pc

            if (isset($req['liveSkin']) === false) {    //변수가 없을때
                if ($req['deviceType'] == 'mobile') {
                    $req['liveSkin'] = $req['deviceType'] . STR_DIVISION . \Globals::get('gSkin.mobileSkinLive');  //디폴트 pc
                } else {
                    $req['liveSkin'] = $req['deviceType'] . STR_DIVISION . \Globals::get('gSkin.frontSkinLive');  //디폴트 pc
                }
            } else if ($req['liveSkin']) {
                list($device, $liveSkin) = explode(STR_DIVISION, $req['liveSkin']);
                $arrWhere[] = 'liveSkin = ?  and bdMobileFl = ? ';
                $this->db->bind_param_push($arrBind, 's', $liveSkin);
                $this->db->bind_param_push($arrBind, 's', $device == 'mobile' ? 'y' : 'n');
            }

            $req['domainFl'] = $req['domainFl'] ?? Globals::get('gGlobal.useMallList.1.domainFl');
            if ($req['domainFl']) {
                foreach (\Globals::get('gGlobal.useMallList') as $val) {
                    if ($req['domainFl'] == $val['domainFl']) {
                        if ($req['deviceType'] == 'pc') {
                            $liveSkin = $val['skin']['frontLive'];
                            $arrFixWhere[] = "liveSkin in ( '" . $liveSkin . "' )";
                        } else if ($req['deviceType'] == 'mobile') {
                            $liveSkin = $val['skin']['mobileLive'];
                            $arrFixWhere[] = "liveSkin in ( '" . $liveSkin . "' )";
                        } else {
                            $frontSkin = $val['skin']['frontLive'];
                            $mobileSkin = $val['skin']['mobileLive'];
                            $arrFixWhere[] = "liveSkin in ( '" . $frontSkin . "' , '" . $mobileSkin . "')";
                        }
                    } else {
                        continue;
                    }
                }

                $fixWhere = implode(' AND ', $arrFixWhere);
            } else {
                foreach (\Globals::get('gGlobal.useMallList') as $key => $val) {
                    if ($val['skin']['frontLive']) {
                        $arrFixWhere[] = " ((liveSkin = '" . $val['skin']['frontLive'] . "' AND bdMobileFl = 'n' )";
                    }
                    if ($val['skin']['mobileLive']) {
                        $arrFixWhere[] = "(liveSkin = '" . $val['skin']['mobileLive'] . "' AND bdMobileFl = 'y' )) ";
                    }
                }
                $fixWhere = implode(' OR ', $arrFixWhere);
            }
        } else {
            $fixWhere = " ((liveSkin = '" . Globals::get('gSkin.frontSkinLive') . "' AND bdMobileFl = 'n' ) OR (liveSkin = '" . Globals::get('gSkin.mobileSkinLive') . "' AND bdMobileFl = 'y' )) ";
        }
        if ($req['boardKind']) {
            if ($req['boardKind']['all'] != 'y') {
                foreach ($req['boardKind'] as $key => $val) {
                    if ($val == 'y') {
                        $_bdKind[] = '?';
                        $this->db->bind_param_push($arrBind, 's', $key);
                    }
                }
                $arrWhere[] = "bdKind in (" . implode(",", $_bdKind) . ")";
            }
        }

        // --- 페이지 기본설정
        gd_isset($req['page'], 1);
        gd_isset($req['page_num'], 10);

        $page = \App::load('\\Component\\Page\\Page', $req['page']);
        $page->page['list'] = $req['page_num']; // 페이지당 리스트 수

        if ($arrWhere) {
            $addWhere = $fixWhere . ' AND ' . implode(' AND ', gd_isset($arrWhere));
        } else {
            $addWhere = $fixWhere;
        }
        $totalCnt = "SELECT COUNT(*) as cnt  FROM " . DB_BOARD_THEME . " WHERE " . $fixWhere;
        $searchCnt = $this->db->query_fetch($totalCnt, $arrBind, false)['cnt'];
        $page->recode['amount'] = $searchCnt; // 전체 레코드 수
        $page->setPage();
        $page->setUrl(\Request::getQueryString());

        // 현 페이지 결과
        $this->db->strField = " * ";
        $this->db->strWhere = $addWhere;
        $this->db->strOrder = $sort['fieldName'] . ' ' . $sort['sortMode'];
        $this->db->strLimit = $page->recode['start'] . ',' . $req['page_num'];
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_BOARD_THEME . ' ' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $arrBind);
        $_align = array('center' => __('가운데정렬'), 'left' => __('왼쪽정렬'), 'right' => __('오른쪽정렬'));
        foreach ($data as &$row) {
            $row['kindText'] = $this->getKindText($row['bdKind']);
            $row['deviceTypeText'] = $row['bdMobileFl'] == 'y' ? __('모바일쇼핑몰') : __('PC쇼핑몰');
            $row['bdAlignText'] = $_align[$row['bdAlign']];
            if ($row['bdMobileFl'] == 'y') {
                $row['bdAlignText'] = '-';
                $row['bdWidthText'] = '-';
                $strSQL = 'SELECT count(*) as cnt FROM ' . DB_BOARD . ' WHERE mobileThemeSno = ' . $row['sno'];
            } else {
                $strSQL = 'SELECT count(*) as cnt FROM ' . DB_BOARD . ' WHERE themeSno = ' . $row['sno'];
                $row['bdWidthText'] = $row['bdWidth'] . $row['bdWidthUnit'];
            }
            $applyCount = $this->db->query_fetch($strSQL, null, false)['cnt'];
            $row['applyThemeCount'] = $applyCount;

        }

        // 검색 레코드 수
        $page->recode['total'] = $this->db->query_fetch('SELECT COUNT(*) as cnt FROM ' . DB_BOARD_THEME . ' WHERE ' . $addWhere, $arrBind, false)['cnt'];
        $page->setPage();


        $checked['deviceType']['pc'] = $req['deviceType'] == 'pc' ? 'checked' : '';
        $checked['deviceType']['mobile'] = $req['deviceType'] == 'mobile' ? 'checked' : '';
        $checked['deviceType']['all'] = $req['deviceType'] == 'all' || (!$checked['deviceType']['pc'] && !$checked['deviceType']['mobile']) ? 'checked' : '';

        $checked['boardKind']['all'] = $req['boardKind']['all'] == 'y' ? 'checked' : '';
        $checked['boardKind'][Board::KIND_DEFAULT] = $req['boardKind'][Board::KIND_DEFAULT] == 'y' ? 'checked' : '';
        $checked['boardKind'][Board::KIND_GALLERY] = $req['boardKind'][Board::KIND_GALLERY] == 'y' ? 'checked' : '';
        $checked['boardKind'][Board::KIND_QA] = $req['boardKind'][Board::KIND_QA] == 'y' ? 'checked' : '';
        $checked['boardKind'][Board::KIND_EVENT] = $req['boardKind'][Board::KIND_EVENT] == 'y' ? 'checked' : '';
        $checked['domainFl'][$req['domainFl']] = 'checked';

        $selected['liveSkin'][$req['liveSkin']] = 'selected';

        // 각 데이터 배열화
        $getData['data'] = gd_htmlspecialchars_stripslashes(gd_isset($data));
        $getData['sort'] = $sort;
        $getData['checked'] = $checked;
        $getData['selected'] = $selected;

        return $getData;
    }

    public function getThemeListByKind($liveSkin, $bdKind = 'default', $mobileFl)
    {
        $arrBind = [];

        $strSQL = " SELECT * FROM " . DB_BOARD_THEME . " WHERE liveSkin = ? AND bdKind = ? AND bdMobileFl = ? ORDER BY bdBasicFl asc, sno asc";
        $this->db->bind_param_push($arrBind, 's', $liveSkin);
        $this->db->bind_param_push($arrBind, 's', $bdKind);
        $this->db->bind_param_push($arrBind, 's', $mobileFl);
        $data = $this->db->query_fetch($strSQL, $arrBind);


        return $data;
    }


    public function getIconImageInfo($data)
    {
        $skinName = $data['liveSkin'];
        $themeId = $data['themeId'];
        $device = $data['bdMobileFl'] == 'y' ? 'mobile' : 'pc';
        foreach (self::ICON_TYPE_LIST as $key => $val) {
            $data[$val]['url'] = $this->getIconPath($themeId, $val, $userModify, $device, $skinName);
            $data[$val]['userModify'] = $userModify;
        }

        return $data;
    }

    public function getDefaultIconImageInfo($isMobile = false)
    {
        $device = $isMobile ? 'mobile' : 'pc';
        foreach (self::ICON_TYPE_LIST as $key => $val) {
            $data[$val]['url'] = $this->getIconPath(null, $val, $userModify, $device);
            $data[$val]['userModify'] = $userModify;
        }

        return $data;
    }

    /**
     * 게시판 테마의 등록 및 수정에 관련된 정보
     *
     * @author sunny
     * @param integer $themeSno 게시판 테마 sno
     * @return array 게시판 테마 정보
     */
    public function getView($themeSno = null)
    {
        // --- 등록인 경우
        if (is_null($themeSno)) {
            // 기본 정보
            $data['mode'] = 'theme_register';
            $data['sno'] = null;
            $data['bdAlign'] = 'center';
            $data['bdMobileFl'] = 'n';
            // 기본값 설정
            DBTableField::setDefaultData('tableBoardTheme', $data);

            $data['bdWidth'] = '100';
            $data['bdWidthUnit'] = '%';
            $data['bdListLineSpacing'] = '100';

            $data['iconImage'] = $this->getDefaultIconImageInfo();
            $data['mobileIconImage'] = $this->getDefaultIconImageInfo(true);
            $themeId = null;
            // --- 수정인 경우
        } else {
            // 테마 정보
            $tmp = $this->getData($themeSno);
            $data = $tmp[0];
            $data['mode'] = 'theme_modify';
            $skinBase = new SkinBase();
            $skinInfo = $skinBase->getSkinInfo($data['liveSkin']);
            $data['liveSkinName'] = $skinInfo['skin_name'];
            // 기본값 설정
            DBTableField::setDefaultData('tableBoardTheme', $data);
        }

        $checked = array();
        $checked['bdMobileFl'][$data['bdMobileFl']] = 'checked';
        $checked['bdAlign'][$data['bdAlign']] = 'checked';
        $getData['data'] = $data;
        $getData['checked'] = $checked;

        return $getData;
    }

    /**
     * 게시판 테마 정보
     *
     * @author sunny
     * @param string $themeSno 테마 번호
     * @param null $arrThemeField
     * @return array 해당 게시판 테마 정보
     */
    public function getData($themeSno = null, $arrThemeField = null)
    {
        $arrBind = [];
        if ($themeSno === 0) {
            return false;
        }

        if (is_null($themeSno)) {
            $strWhere = '';
        } else {
            $strWhere = 'WHERE sno = ?';
            $this->db->bind_param_push($arrBind, 'i', $themeSno);
        }
        $arrField = DBTableField::setTableField('tableBoardTheme', $arrThemeField);
        $strSQL = 'SELECT sno, ' . implode(', ', $arrField) . ' FROM ' . DB_BOARD_THEME . ' ' . $strWhere;
        if (empty($arrBind) === true) {
            $getData = $this->db->query_fetch($strSQL);
        } else {
            $getData = $this->db->query_fetch($strSQL, $arrBind);
        }

        $getData[0]['deviceTypeText'] = $getData[0]['bdMobileFl'] == 'y' ? __('모바일쇼핑몰') : __('PC쇼핑몰');
        $getData[0]['iconImage'] = $this->getIconImageInfo($getData[0]);
        $getData[0]['mobileIconImage'] = $this->getIconImageInfo($getData[0]);
        if (count($getData) > 0) {
            return gd_htmlspecialchars_stripslashes($getData);
        } else {
            return false;
        }
    }

    /**
     * 테마아이디 중복 확인
     *
     * @author sunny
     * @param $themeId 테마아이디
     * @param string $isMobile y or n
     * @param null $liveSkin
     * @return bool
     * @throws \Exception
     */
    public function overlapThemeId($themeId, $isMobile = 'n', $liveSkin = null)
    {
        $validBasicId = [Board::BASIC_GOODS_REIVEW_ID, Board::BASIC_EVENT_ID, Board::BASIC_GOODS_QA_ID, Board::BASIC_COOPERATION_ID, Board::BASIC_NOTICE_ID, Board::BASIC_QA_ID];

        if (in_array($themeId, $validBasicId)) {
            throw new \Exception(sprintf(__('%s 는 사용하실 수 없는 스킨코드 입니다.'), $themeId));
        }

        if ($liveSkin) {
            list($frontSkin, $mobileSkin) = explode(STR_DIVISION, $liveSkin);
            $liveSkin = ($isMobile == 'y') ? $mobileSkin : $frontSkin;
        } else {
            $liveSkin = ($isMobile == 'y') ? Globals::get('gSkin.mobileSkinLive') : Globals::get('gSkin.frontSkinLive');
        }
        $strSQL = 'SELECT themeId FROM ' . DB_BOARD_THEME . ' where themeId=? AND bdMobileFl = ? AND liveSkin = ?';
        $this->db->bind_param_push($arrBind, 's', $themeId);
        $this->db->bind_param_push($arrBind, 's', $isMobile);
        $this->db->bind_param_push($arrBind, 's', $liveSkin);
        $this->db->query_fetch($strSQL, $arrBind);
        if ($this->db->num_rows() > 0) {
            return true;
        }
        return false;
    }

    /**
     * 게시판 테마 정보 저장
     *
     * @author sunny
     * @param array $arrData 저장할 정보의 배열
     * @param $fileData
     * @return int 기본키
     * @throws
     * @throws \Exception
     */
    public function saveData($arrData, $fileData)
    {
        // 게시판 테마명 체크
        if (Validator::required(gd_isset($arrData['themeNm'])) === false) {
            throw \Exception(__('잘못된 스킨이름 입니다.'));
        }


        // 게시판 테마 정보
        $getTheme = array();
        $isMobile = false;
        if ($arrData['mode'] == 'theme_modify') {
            $getTheme = $this->getData($arrData['sno']);
            if ($getTheme[0]['bdMobileFl'] == 'y') {
                $isMobile = true;
            }
        } else {  //등록
            $tmpData['bdBasicFl'][] = 'n';
            if (\Globals::get('gGlobal.isUse')) {
                list($frontSkin, $mobileSkin) = explode(STR_DIVISION, $arrData['liveSkin']);
                if ($arrData['bdMobileFl'] == 'y') {
                    $isMobile = true;
                    $arrData['liveSkin'] = $mobileSkin ?? Globals::get('gSkin.mobileSkinLive');
                } else {
                    $arrData['liveSkin'] = $frontSkin ?? Globals::get('gSkin.frontSkinLive');;
                }
            } else {
                if ($arrData['bdMobileFl'] == 'y') {
                    $isMobile = true;
                    $arrData['liveSkin'] = Globals::get('gSkin.mobileSkinLive');
                } else {
                    $arrData['liveSkin'] = Globals::get('gSkin.frontSkinLive');
                }
            }
        }

        // 게시판 테마 정보 변형
        foreach ($arrData as $key => $val) {
            $tmpData[$key][] = $val;
        }


        $this->db->begin_tran();
        try {
            // 게시판 정보
            $compareTheme = $this->db->get_compare_array_data($getTheme, $tmpData, false);
            // 공통 키값
            $arrDataKey = array('sno' => $arrData['sno']);

            // 게시판 테마 정보 저장

            $this->db->set_compare_process(DB_BOARD_THEME, $tmpData, $arrDataKey, $compareTheme, array_keys($tmpData));
            // 기본키
            if ($arrData['mode'] == 'theme_register') {
                $arrData['sno'] = $this->db->insert_id();

                $this->createSkinBorn($arrData['themeId'], $arrData['bdKind'], $arrData['bdMobileFl'] == 'y', $arrData['liveSkin']); //스킨폴더 생성
            }
            $prefix = $isMobile ? 'Mobile' : '';
            foreach ($fileData['boardIcon' . $prefix]['tmp_name'] as $key => $val) {
                if ($val) {
                    $device = $isMobile ? 'mobile' : 'pc';
                    $fileName = $this->_getIconName($key, true, $device);
                    if ($arrData['bdMobileFl'] == 'y') {
                        $iconPath = UserFilePath::mobileSkin(Globals::get('gSkin.mobileSkinLive'), 'board', 'skin', $arrData['themeId'], 'img', 'icon', $fileName)->getRealPath();
                    } else {
                        $iconPath = UserFilePath::frontSkin(Globals::get('gSkin.frontSkinLive'), 'board', 'skin', $arrData['themeId'], 'img', 'icon', $fileName)->getRealPath();
                    }

                    FileHandler::copy($val, $iconPath);
                }
            }
            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }

        return $arrData['sno'];
    }

    private function _getIconName($iconType, $userModify = true, $device, $isDefault = false)
    {
        if ($device == 'mobile' && $isDefault) { //등록시 디폴트로 보여주는 이미지
            $prefix = 'm_';
        }
        $fileName = 'icon_' . $prefix . 'board_' . $iconType . '.png';
        if ($userModify === true) {
            $fileName = 'user_icon_board_' . $iconType . '.png';
        }

        return $fileName;
    }

    public function deleteIcon($themeId, $iconType, $device)
    {
        $fileName = $this->_getIconName($iconType, true, $device);

        if ($device == 'pc') {
            $skinPath = UserFilePath::frontSkin(Globals::get('gSkin.frontSkinLive'), 'board', 'skin', $themeId, 'img', 'icon', $fileName)->getRealPath();
        } else if ($device == 'mobile') {
            $skinPath = UserFilePath::mobileSkin(Globals::get('gSkin.mobileSkinLive'), 'board', 'skin', $themeId, 'img', 'icon', $fileName)->getRealPath();
        } else {
            throw new \Exception(__('잘못된 인자값 입니다.') . '(' . $device . ')');
        }

        FileHandler::delete($skinPath);

    }

    /**
     * 게시판 테마 정보 삭제
     *
     * @author sunny
     * @param integer $dataSno 삭제할 레코드 sno
     * @throws \Exception
     */
    public function deleteData($dataSno)
    {
        if (is_array($dataSno) == false) {
            $dataSno[] = $dataSno;
        }

        foreach ($dataSno as $sno) {
            $data = $this->getData($sno);
            if (!$data) {
                throw new \Exception(__('처리중에 오류가 발생하여 실패되었습니다.'));
            }
            if ($data[0]['bdBasicFl'] == 'y') {
                throw new \Exception(__('삭제 불가능한 데이터입니다.'));
            }

            $this->deleteSkin($data[0]['themeId'], $data[0]['bdMobileFl'] == 'y', $data[0]['liveSkin']);

            // 옵션 관리 정보 삭제
            $arrBind = [];
            $this->db->bind_param_push($arrBind, 'i', $sno);
            $this->db->set_delete_db(DB_BOARD_THEME, 'sno = ?', $arrBind);
        }
    }

    public function deleteBySkinName($skinName, $skinType)
    {
        $bdMobileFl = $skinType == 'mobile' ? 'y' : 'n';
        $query = "DELETE FROM " . DB_BOARD_THEME . " WHERE liveSkin = ? AND bdMobileFl = ?";
        $arrBind = [];
        $this->db->bind_param_push($arrBind, 's', $skinName);
        $this->db->bind_param_push($arrBind, 's', $bdMobileFl);
        $this->db->bind_query($query, $arrBind);
    }

    /**
     * createSkinBorn
     *
     * @param $themeId
     * @param string $boardKind
     * @param $isMobile
     * @param null $liveSkin
     * @throws \Exception
     */
    private function createSkinBorn($themeId, $boardKind, $isMobile, $liveSkin = null)
    {
        if ($isMobile) {
            $skin = $liveSkin ? $liveSkin : Globals::get('gSkin.mobileSkinLive');
            $templatePath = UserFilePath::mobileSkin($skin, 'board', 'template', $boardKind)->getRealPath();
            $skinPath = UserFilePath::mobileSkin($skin, 'board', 'skin', $themeId)->getRealPath();
        } else {
            $skin = $liveSkin ? $liveSkin : Globals::get('gSkin.frontSkinLive');
            $templatePath = UserFilePath::frontSkin($skin, 'board', 'template', $boardKind)->getRealPath();
            $skinPath = UserFilePath::frontSkin($skin, 'board', 'skin', $themeId)->getRealPath();
        }

        if (FileHandler::isExists($skinPath)) {
            return;
        }
        $result = FileHandler::copy($templatePath, $skinPath, true);
        if (!$result) {
            FileHandler::delete($skinPath);
            throw new \Exception(sprintf(__('스킨 복사 실패') . ' %s -> %s ', $templatePath, $skinPath));
        }

    }

    /**
     * deleteSkin
     *
     * @param $themeId
     * @param $isMobile
     * @param null $liveSkin
     */
    private function deleteSkin($themeId, $isMobile, $liveSkin = null)
    {
        if (!$themeId) {
            return;
        }
        if ($isMobile) {
            $skin = $liveSkin ? $liveSkin : Globals::get('gSkin.mobileSkinLive');
            $skinPath = UserFilePath::mobileSkin($skin, 'board', 'skin', $themeId)->getRealPath();
        } else {
            $skin = $liveSkin ? $liveSkin : Globals::get('gSkin.frontSkinLive');
            $skinPath = UserFilePath::frontSkin($skin, 'board', 'skin', $themeId)->getRealPath();
        }
        FileHandler::delete($skinPath, true);
    }

    /**
     * getTheme
     *
     * @return array
     */
    public function getThemes()
    {
        $tmpTheme = $this->getData(null, ['themeId', 'themeNm', 'bdKind']);
        $themes = [];
        if (empty($tmpTheme) === false) {
            foreach ($tmpTheme as $val) {
                $themes[$val['themeId']] = ['sno' => $val['sno'], 'themeNm' => $val['themeNm'], 'bdKind' => $val['bdKind']];
            }
        }

        return $themes;
    }

    public function getIconPath($themeId = null, $iconType, &$userModify = false, $device = 'pc', $skinName)
    {
        if ($themeId == null) {
            $fileName = $this->_getIconName($iconType, false, $device, true);
            $uriPath = UserFilePath::data('board', 'common', 'icon', $fileName);
            $userModify = false;
            return $uriPath->www();
        }

        $fileName = $this->_getIconName($iconType, true, $device);
        $userModify = true;
        if ($device == 'mobile') {
            $realSkinPath = UserFilePath::mobileSkin($skinName) . '/';
            $httpSkinPath = UserFilePath::mobileSkin($skinName)->www() . '/';
        } else {
            $realSkinPath = UserFilePath::frontSkin($skinName) . '/';
            $httpSkinPath = UserFilePath::frontSkin($skinName)->www() . '/';
        }

        $realPath = $realSkinPath . 'board/skin/' . $themeId . '/img/icon/' . $fileName;

        if (is_file($realPath) === false) {
            $userModify = false;
            $fileName = $this->_getIconName($iconType, false, $device);
        }

        $uriPath = $httpSkinPath . 'board/skin/' . $themeId . '/img/icon/' . $fileName;
        return $uriPath;
    }

    public function getDefaultKindTheme($skinName, $kind, $isMobile = false)
    {
        $bdMobileFl = $isMobile ? 'y' : 'n';
        $query = "SELECT sno," . implode(',', DBTableField::setTableField('tableBoardTheme')) . ' FROM ' . DB_BOARD_THEME;
        $query .= " WHERE bdBasicFl = 'y' AND bdMobileFl = ? AND liveSkin = ? AND bdKind = ? limit 1 ";
        $arrBind = [];
        $this->db->bind_param_push($arrBind, 's', $bdMobileFl);
        $this->db->bind_param_push($arrBind, 's', $skinName);
        $this->db->bind_param_push($arrBind, 's', $kind);
        $result = $this->db->query_fetch($query, $arrBind, false);

        return $result;
    }

    public function getLiveSkinList($domainFl = null, $deviceType = 'all')
    {
        $skinBase = new SkinBase();

        //--- skinBase 정의
        $selectSkinList = [];
        $domainFl = $domainFl ?? \Globals::get('gGlobal.useMallList.1.domainFl');
        foreach (\Globals::get('gGlobal.useMallList') as $key => $val) {
            if ($val['domainFl'] != $domainFl) {
                continue;
            }

            if ($deviceType == 'all' || $deviceType == 'pc') {
                $frontAddInfo = '';
                if ($val['skin']['frontLive'] == Globals::get('gSkin.frontSkinWork') && $val['skin']['frontLive'] == Globals::get('gSkin.frontSkinLive')) {
                    $frontAddInfo = '-사용/작업중인 스킨';
                } else if ($val['skin']['frontLive'] == Globals::get('gSkin.frontSkinWork')) {
                    $frontAddInfo = '-작업중인 스킨';
                } else if ($val['skin']['frontLive'] == Globals::get('gSkin.frontSkinLive')) {
                    $frontAddInfo = '-사용중인 스킨';
                }

                $frontSkinInfo = $skinBase->getSkinInfo($val['skin']['frontLive']);
                $selectSkinList[] = [
                    'device' => 'pc',
                    'skinTitle' => 'PC' . ' : ' . $frontSkinInfo['skin_name'] . '(' . $val['skin']['frontLive'] . ') ' . $frontAddInfo,
                    'skinCode' => $val['skin']['frontLive'],
                    'skinValue' => 'pc' . STR_DIVISION . $val['skin']['frontLive'],
                ];
            }

            if ($deviceType == 'all' || $deviceType == 'mobile') {
                $mobileAddInfo = '';
                if ($val['skin']['mobileLive'] == Globals::get('gSkin.mobileSkinWork') && $val['skin']['frontLive'] == Globals::get('gSkin.mobileSkinLive')) {
                    $mobileAddInfo = '-사용/작업중인 스킨';
                } else if ($val['skin']['mobileLive'] == Globals::get('gSkin.mobileSkinWork')) {
                    $mobileAddInfo = '-작업중인 스킨';
                } else if ($val['skin']['mobileLive'] == Globals::get('gSkin.mobileSkinLive')) {
                    $mobileAddInfo = '-사용중인 스킨';
                }

                $skinBase->skinType = 'mobile';
                $mobileSkinInfo = $skinBase->getSkinInfo($val['skin']['mobileLive']);
                $selectSkinList[] = [
                    'device' => 'mobile',
                    'skinTitle' => '모바일' . ' : ' . $mobileSkinInfo['skin_name'] . '(' . $val['skin']['mobileLive'] . ') ' . $mobileAddInfo,
                    'skinCode' => $val['skin']['mobileLive'],
                    'skinValue' => 'mobile' . STR_DIVISION . $val['skin']['mobileLive'],
                ];
            }




        }

        return $selectSkinList;


    }
}
