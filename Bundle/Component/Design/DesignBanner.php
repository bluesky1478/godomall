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
use Component\Category\CategoryAdmin;
use Framework\Utility\ArrayUtils;
use Globals;
use Request;
use Message;
use UserFilePath;
use FileHandler;
use DirectoryIterator;

/**
 * 배너 관리 클래스
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class DesignBanner extends \Component\Design\SkinBase
{
    // 창 종류
    // __('새창')
    public $bannerTargetKindFl = ['_blank' => '새창',];

    // 그룹 타입
    // __('일반 배너')
    // __('로고 전용')
    // __('카테고리 전용')
    // __('브랜드 전용')
    //public $bannerGroupTypeFl = ['banner' => '일반 배너', 'logo' => '로고 전용', 'category' => '카테고리 전용', 'brand' => '브랜드 전용'];
    public $bannerGroupTypeFl = ['banner' => '일반 배너', 'logo' => '로고 전용'];

    public $bannerPathDefault = 'img/banner';

    public $bannerSliderTime = [
        '1' => '1초',
        '2' => '2초',
        '3' => '3초',
        '4' => '4초',
        '5' => '5초',
        '6' => '6초',
        '7' => '7초',
        '8' => '8초',
        '9' => '9초',
        '10' => '10초',
        'manual' => '수동',
    ];

    /**
     * 배너 리스트
     * @return array
     */
    public function getBannerListData($menuType = null)
    {
        $getValue = Request::get()->toArray();



        if (empty($getValue['key']) === true) {
            if ($menuType == 'mobile') $getValue['bannerGroupDeviceType'] = $menuType;
            else $getValue['bannerGroupDeviceType'] = 'front';

            $getValue['skinName'] = Parent::getUseSkinInfo($getValue['bannerGroupDeviceType'], 'Work');
        }

        // 검색 설정
        $setGetKey = ['detailSearch', 'key', 'keyword', 'bannerGroupDeviceType', 'skinName', 'bannerUseFl',  'bannerGroupCode', 'treatDateFl', 'sort', 'page', 'pageNum',];
        $setGetKey2 = ['treatDate' => ['start', 'end']];
        $setKeyword = ['dbg.bannerGroupName', 'db.bannerImage', 'db.bannerLink'];
        $search = [];
        $checked = [];
        $selected = [];

        //검색설정
        $search['sortList'] = [
            'db.regDt desc' => __('등록일 ↑'),
            'db.regDt asc' => __('등록일 ↓'),
            'db.modDt desc' => __('수정일 ↑'),
            'db.modDt asc' => __('수정일 ↓'),
            'db.skinName desc' => __('스킨코드 ↑'),
            'db.skinName asc' => __('스킨코드 ↓'),
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

        $checked['bannerUseFl'][$search['bannerUseFl']] = 'checked="checked"';
        $checked['bannerGroupDeviceType'][$search['bannerGroupDeviceType']] = 'checked="checked"';

        $selected['skinName'][$search['skinName']] =
        $selected['bannerGroupCode'][$search['bannerGroupCode']] = 'selected="selected"';

        // 검색
        $arrWhere = [];
        $arrBind = [];

        // 키워드 검색
        if ($search['key'] && $search['keyword']) {
            if ($search['key'] === 'all') {
                $tmpWhere = [];
                foreach ($setKeyword as $keyNm) {
                    $tmpWhere[] = '(' . $keyNm . ' LIKE concat(\'%\', ?, \'%\'))';
                    $this->db->bind_param_push($arrBind, 's', $search['keyword']);
                }
                $arrWhere[] = '(' . implode(' OR ', $tmpWhere) . ')';
            } else {
                $arrWhere[] = $search['key'] . " LIKE concat('%',?,'%')";
                $this->db->bind_param_push($arrBind, 's', $search['keyword']);
            }
        }

        // 배너 그룹 구분
        if ($search['bannerGroupDeviceType']) {
            $arrWhere[] = 'dbg.bannerGroupDeviceType = ?';
            $this->db->bind_param_push($arrBind, 's', $search['bannerGroupDeviceType']);
        }

        // 디자인 스킨 검색
        if ($search['skinName']) {
            $tmpData = explode(STR_DIVISION, $search['skinName']);
            $arrWhere[] = 'dbg.skinName = ?';
            $this->db->bind_param_push($arrBind, 's', $tmpData[1]);
            if (empty($search['bannerGroupDeviceType']) === true) {
                $arrWhere[] = 'dbg.bannerGroupDeviceType = ?';
                $this->db->bind_param_push($arrBind, 's', $tmpData[0]);
            }
        }

        // 노출여부 검색
        if ($search['bannerUseFl']) {
            $arrWhere[] = 'db.bannerUseFl = ?';
            $this->db->bind_param_push($arrBind, 's', $search['bannerUseFl']);
        }

        // 배너 그룹
        if ($search['bannerGroupCode']) {
            $arrWhere[] = 'db.bannerGroupCode = ?';
            $this->db->bind_param_push($arrBind, 's', $search['bannerGroupCode']);
        }

        // 기간검색
        if ($search['treatDateFl'] && $search['treatDate']['start'] && $search['treatDate']['end']) {
            $arrWhere[] = '(' . $search['treatDateFl'] . ' BETWEEN ? AND ?)';
            $this->db->bind_param_push($arrBind, 's', $search['treatDate']['start'] . ' 00:00:00');
            $this->db->bind_param_push($arrBind, 's', $search['treatDate']['end'] . ' 23:59:59');
        }

        // --- 정렬 설정
        $sort = gd_isset($search['sort'], 'db.regDt desc');

        // --- 페이지 기본설정
        if (empty($search['page']) === true) {
            $search['page'] = 1;
        }
        if (empty($search['pageNum']) === true) {
            $search['pageNum'] = 10;
        }

        $page = new Page($search['page']);
        $page->page['list'] = $search['pageNum']; // 페이지당 리스트 수
        list($page->recode['amount']) = $a = $this->db->fetch('SELECT count(sno) FROM ' . DB_DESIGN_BANNER , 'array'); // 전체 레코드 수
        $page->setPage();
        $page->setUrl(\Request::getQueryString());

        $this->db->strField = implode(', ', DBTableField::setTableField('tableDesignBanner', null, null, 'db')) . ', ' . implode(', ', DBTableField::setTableField('tableDesignBannerGroup', ['skinName', 'bannerGroupDeviceType', 'bannerGroupName', 'bannerGroupType'], null, 'dbg'));
        $this->db->strJoin = ' LEFT JOIN ' . DB_DESIGN_BANNER_GROUP . ' as dbg ON db.bannerGroupCode = dbg.bannerGroupCode AND db.skinName = dbg.skinName ';
        $this->db->strWhere = implode(' AND ', gd_isset($arrWhere));
        $this->db->strOrder = $sort . ', dbg.bannerGroupDeviceType desc ,sno desc';
        $this->db->strLimit = $page->recode['start'] . ',' . $search['pageNum'];

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_DESIGN_BANNER . ' as db' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $arrBind);

        // 배너 이미지 경로
        foreach ($data as $key => $val) {
            $bannerImageDir = $val['bannerGroupDeviceType'] . DS . $val['skinName'] . DS . $this->bannerPathDefault . DS;
            $data[$key]['bannerImagePath'] = $bannerImageDir;
            if (empty($val['bannerImage']) === false) {
                $tmpBannerImage = UserFilePath::data('skin', $bannerImageDir . $val['bannerImage']);
                if (FileHandler::isExists($tmpBannerImage)) {
                    $getImgData = getimagesize($tmpBannerImage);
                    $data[$key]['bannerImageInfo']['width'] = $getImgData[0];
                    $data[$key]['bannerImageInfo']['height'] = $getImgData[1];
                    $data[$key]['bannerImageInfo']['size'] = FileHandler::getFileInfo($tmpBannerImage)->getSize();
                    $data[$key]['bannerImageInfo']['mime'] = $getImgData['mime'] ?? FileHandler::getFileInfo($tmpBannerImage)->getExtension();
                }
            }
        }

        $this->db->strField = 'count(*) as cnt';
        $this->db->strJoin = ' LEFT JOIN ' . DB_DESIGN_BANNER_GROUP . ' as dbg ON db.bannerGroupCode = dbg.bannerGroupCode AND db.skinName = dbg.skinName ';
        $this->db->strWhere = implode(' AND ', gd_isset($arrWhere));
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_DESIGN_BANNER . ' as db' . implode(' ', $query);
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
     * 배너 그룹명
     *
     * @return array $getData
     */
    public function getBannerGroupData()
    {
        // Data
        $arrField = DBTableField::setTableField('tableDesignBannerGroup');
        $strSQL = 'SELECT ' . implode(', ', $arrField) . ' FROM ' . DB_DESIGN_BANNER_GROUP . ' ORDER BY bannerGroupDeviceType ASC, skinName ASC, bannerGroupType DESC, sno DESC';
        $getData = $this->db->query_fetch($strSQL);

        return gd_htmlspecialchars_stripslashes($getData);
    }

    /**
     * 배너 그룹명
     *
     * @param integer $sno 페이지 번호
     * @return array|boolean
     * @throws \Exception
     */
    public function getBannerGroupDetailData($sno)
    {
        // Validation
        if (Validator::number($sno, null, null, true) === false) {
            throw new \Exception(sprintf(__('%s 항목이 잘못 되었습니다.'), '페이지 번호'));
        }

        // Data
        $arrBind = $arrWhere = [];
        array_push($arrWhere, 'sno = ?');
        $this->db->bind_param_push($arrBind, 'i', $sno);

        // Data
        $arrField = DBTableField::setTableField('tableDesignBannerGroup');
        $strSQL = 'SELECT ' . implode(', ', $arrField) . ' FROM ' . DB_DESIGN_BANNER_GROUP . ' WHERE ' . implode(' AND ', gd_isset($arrWhere));
        $getData = $this->db->query_fetch($strSQL, $arrBind, false);

        if (empty($getData) === false) {
            return gd_htmlspecialchars_stripslashes($getData);
        } else {
            return false;
        }
    }

    /**
     * 배너 상세 데이터
     *
     * @param integer $sno 페이지 번호
     * @return array|boolean
     * @throws \Exception
     */
    public function getBannerDetailData($sno)
    {
        // Validation
        if (Validator::number($sno, null, null, true) === false) {
            throw new \Exception(sprintf(__('%s 항목이 잘못 되었습니다.'), '페이지 번호'));
        }

        // Data
        $arrBind = $arrWhere = [];
        array_push($arrWhere, 'db.sno = ?');
        $this->db->bind_param_push($arrBind, 'i', $sno);

        $arrField['banner'] = DBTableField::setTableField('tableDesignBanner', null, null, 'db');
        $arrField['bannerGroup'] = DBTableField::setTableField('tableDesignBannerGroup', ['bannerGroupDeviceType'], null, 'dbg');
        $this->db->strField = implode(', ', $arrField['banner']) . ', ' . implode(', ', $arrField['bannerGroup']);
        $this->db->strJoin = ' LEFT JOIN ' . DB_DESIGN_BANNER_GROUP . ' as dbg ON db.bannerGroupCode = dbg.bannerGroupCode AND db.skinName = dbg.skinName ';
        $this->db->strWhere = implode(' AND ', gd_isset($arrWhere));
        $this->db->strOrder = 'db.sno DESC';
        $this->db->strLimit = '0, 1'; // 그룹이 여러게 있는 경우 동일한 결과가 배열로 나옴 으로 인해 리밋..

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_DESIGN_BANNER . ' as db' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind, false);

        // 배너 경로
        $getData['bannerImagePath'] = '';
        if (empty($getData['bannerGroupDeviceType']) === false && empty($getData['skinName']) === false) {
            $getData['bannerImagePath'] = $getData['bannerGroupDeviceType'] . DS . $getData['skinName'] . DS . $this->bannerPathDefault . DS;
        }

        if (empty($getData) === false) {
            return gd_htmlspecialchars_stripslashes($getData);
        } else {
            return false;
        }
    }

    /**
     * 배너 상세 데이터
     *
     * @param string $skinName 스킨명
     * @param string $bannerGroupCode 배너 그룹 번호
     * @return array|boolean
     */
    public function getBannerData($skinName, $bannerGroupCode)
    {
        // Validation
        if (empty($bannerGroupCode) === true) {
            return false;
        }

        // Data
        $arrBind = $arrWhere = [];
        array_push($arrWhere, 'skinName = ?');
        $this->db->bind_param_push($arrBind, 's', $skinName);
        array_push($arrWhere, 'bannerGroupCode = ?');
        $this->db->bind_param_push($arrBind, 's', $bannerGroupCode);

        $arrField = DBTableField::setTableField('tableDesignBanner');
        $strSQL = 'SELECT ' . implode(', ', $arrField) . ' FROM ' . DB_DESIGN_BANNER . ' WHERE ' . implode(' AND ', $arrWhere) . ' ORDER BY bannerSort DESC';
        $getData = $this->db->slave()->query_fetch($strSQL, $arrBind);

        if (empty($getData) === false) {
            return gd_htmlspecialchars_stripslashes($getData);
        } else {
            return false;
        }
    }

    /**
     * 배너 그룹 번호 대상 배너 갯수
     * @param string $skinName 배너 그룹 번호
     * @param string $bannerGroupCode 배너 그룹 번호
     * @return integer
     */
    private function _getBannerCnt($skinName, $bannerGroupCode)
    {
        $strSQL = 'SELECT count(sno) as cnt FROM ' . DB_DESIGN_BANNER . ' WHERE skinName=\'' . $skinName . '\' AND bannerGroupCode=\'' . $bannerGroupCode . '\'';
        $getCnt = $this->db->query_fetch($strSQL, null, false);

        return $getCnt['cnt'];
    }

    /**
     * 배너 그룹 리스트
     * @return array
     */
    public function getBannerGroupListData($menuType = null)
    {
        $getValue = Request::get()->toArray();

        if (empty($getValue['key']) === true) {
            if ($menuType == 'mobile') $getValue['bannerGroupDeviceType'] = $menuType;
            else $getValue['bannerGroupDeviceType'] = 'front';

            $getValue['skinName'] = Parent::getUseSkinInfo($getValue['bannerGroupDeviceType'], 'Work');
        }

        // 검색 설정
        $setGetKey = ['detailSearch', 'key', 'keyword', 'skinName', 'bannerGroupDeviceType',  'bannerGroupType', 'treatDateFl', 'sort', 'page', 'pageNum',];
        $setGetKey2 = ['treatDate' => ['start', 'end']];
        $setKeyword = ['bannerGroupName', 'skinName'];
        $search = [];
        $checked = [];
        $selected = [];

        //검색설정
        $search['sortList'] = [
            'regDt desc' => __('등록일 ↑'),
            'regDt asc' => __('등록일 ↓'),
            'modDt desc' => __('수정일 ↑'),
            'modDt asc' => __('수정일 ↓'),
            'bannerGroupName desc' => __('배너그룹명 ↑'),
            'bannerGroupName asc' => __('배너그룹명 ↓'),
            'skinName desc' => __('스킨코드 ↑'),
            'skinName asc' => __('스킨코드 ↓'),
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

        $checked['bannerGroupDeviceType'][$search['bannerGroupDeviceType']] =
        $checked['bannerGroupType'][$search['bannerGroupType']] = 'checked="checked"';

        $selected['skinName'][$search['skinName']] = 'selected="selected"';

        // 검색
        $arrWhere = [];
        $arrBind = [];

        // 키워드 검색
        if ($search['key'] && $search['keyword']) {
            if ($search['key'] === 'all') {
                $tmpWhere = [];
                foreach ($setKeyword as $keyNm) {
                    $tmpWhere[] = '(' . $keyNm . ' LIKE concat(\'%\', ?, \'%\'))';
                    $this->db->bind_param_push($arrBind, 's', $search['keyword']);
                }
                $arrWhere[] = '(' . implode(' OR ', $tmpWhere) . ')';
            } else {
                $arrWhere[] = $search['key'] . " LIKE concat('%',?,'%')";
                $this->db->bind_param_push($arrBind, 's', $search['keyword']);
            }
        }

        // 배너 그룹 구분
        if ($search['bannerGroupDeviceType']) {
            $arrWhere[] = 'bannerGroupDeviceType = ?';
            $this->db->bind_param_push($arrBind, 's', $search['bannerGroupDeviceType']);
        }

        // 디자인 스킨 검색
        if ($search['skinName']) {
            $tmpData = explode(STR_DIVISION, $search['skinName']);
            $arrWhere[] = 'skinName = ?';
            $this->db->bind_param_push($arrBind, 's', $tmpData[1]);
            if (empty($search['bannerGroupDeviceType']) === true) {
                $arrWhere[] = 'bannerGroupDeviceType = ?';
                $this->db->bind_param_push($arrBind, 's', $tmpData[0]);
            }
        }

        // 배너 그룹 종류
        if ($search['bannerGroupType']) {
            $arrWhere[] = 'bannerGroupType = ?';
            $this->db->bind_param_push($arrBind, 's', $search['bannerGroupType']);
        }

        // 기간검색
        if ($search['treatDateFl'] && $search['treatDate']['start'] && $search['treatDate']['end']) {
            $arrWhere[] = '(' . $search['treatDateFl'] . ' BETWEEN ? AND ?)';
            $this->db->bind_param_push($arrBind, 's', $search['treatDate']['start'] . ' 00:00:00');
            $this->db->bind_param_push($arrBind, 's', $search['treatDate']['end'] . ' 23:59:59');
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
        list($page->recode['amount']) = $this->db->fetch('SELECT count(sno) FROM ' . DB_DESIGN_BANNER_GROUP , 'array'); // 전체 레코드 수
        $page->setPage();
        $page->setUrl(\Request::getQueryString());

        $this->db->strField = implode(', ', DBTableField::setTableField('tableDesignBannerGroup'));
        $this->db->strWhere = implode(' AND ', gd_isset($arrWhere));
        $this->db->strOrder = $sort . ', bannerGroupDeviceType ASC, bannerGroupType DESC, sno DESC';
        $this->db->strLimit = $page->recode['start'] . ',' . $search['pageNum'];

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_DESIGN_BANNER_GROUP . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $arrBind);

        $this->db->strField = 'count(*) as cnt';
        $this->db->strWhere = implode(' AND ', gd_isset($arrWhere));
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_DESIGN_BANNER_GROUP . implode(' ', $query);
        $countData = $this->db->query_fetch($strSQL, $arrBind,false);
        // 검색 레코드 수
        $page->recode['total'] = $countData['cnt'];
        $page->setPage();
        unset($arrBind);
        // 배너 그룹 추가 정보
        foreach ($data as $key => $val) {
            $data[$key]['cateNm'] = '';
            if (empty($val['cateCd']) === false && in_array($val['bannerGroupType'], ['category', 'brand'])) {
                if ($val['bannerGroupType'] === 'category') {
                    $cate = new CategoryAdmin('goods');
                    $data[$key]['cateNm'] = gd_htmlspecialchars_decode($cate->getCategoryPosition($val['cateCd']));
                } else if ($val['bannerGroupType'] === 'brand') {
                    $brand = new CategoryAdmin('brand');
                    $data[$key]['cateNm'] = gd_htmlspecialchars_decode($brand->getCategoryPosition($val['cateCd']));
                }
            }

            // 사용 배너 수
            $data[$key]['bannerCnt'] = $this->_getBannerCnt($val['skinName'], $val['bannerGroupCode']);
        }

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
     * 배너 정보 저장
     * @param array $postValue 저장할 정보
     * @return int sno
     * @throws \Exception
     */
    public function saveBannerData(array $postValue)
    {
        // 기본 테이터 체크
        $dataCheck = true;
        if (empty($postValue['bannerGroupCode']) === true) {
            $dataCheck = false;
        }
        if (empty($postValue['skinName']) === true) {
            $dataCheck = false;
        }
        if (empty($postValue['bannerUseFl']) === true) {
            $dataCheck = false;
        }
        if ($postValue['mode'] === 'modify' && empty($postValue['sno']) === true) {
            $dataCheck = false;
        }

        if ($dataCheck === false) {
            throw new \Exception(sprintf(__('%s 항목이 잘못 되었습니다.'), '배너 정보'));
        }

        if (empty($postValue['bannerPeriodOutputFl']) === true) {
            $postValue['bannerPeriodOutputFl'] = 'n';
        }

        // 날짜 처리
        if ($postValue['bannerPeriodOutputFl'] === 'y') {
            $tmpDataS = explode(' ', $postValue['bannerPeriodSDateY']);
            $tmpDataE = explode(' ', $postValue['bannerPeriodEDateY']);
            $postValue['bannerPeriodSDate'] = $tmpDataS[0];
            $postValue['bannerPeriodSTime'] = $tmpDataS[1] . ':00';
            $postValue['bannerPeriodEDate'] = $tmpDataE[0];
            $postValue['bannerPeriodETime'] = $tmpDataE[1] . ':00';
            unset($tmpDataS, $tmpDataE);
        } else {
            $postValue['bannerPeriodSDate'] = '';
            $postValue['bannerPeriodSTime'] = '';
            $postValue['bannerPeriodEDate'] = '';
            $postValue['bannerPeriodETime'] = '';
        }

        // insert , update 체크
        if ($postValue['mode'] == 'modify') {
            $chkType = 'update';
        } else {
            $chkType = 'insert';
        }

        // 배너 이미지 경로 설정
        $checkBannerPath = UserFilePath::data('skin', $postValue['bannerGroupDeviceType'], $postValue['skinName'], $this->bannerPathDefault);

        // 폴더 생성
        if (FileHandler::isDirectory($checkBannerPath) === false) {
            $result = FileHandler::makeDirectory($checkBannerPath, 0707);
            if ($result !== true) {
                throw new \Exception(__('파일 저장중에 오류가 발생하여 실패되었습니다.'));
            }
        }

        // 배너 이미지 삭제
        if (isset($postValue['bannerImage_del']) === true && $postValue['bannerImage_del'] === 'y') {
            $deleteBannerImage = $checkBannerPath . DS . $postValue['bannerImage'];

            if (FileHandler::isExists($deleteBannerImage)) {
                $result = FileHandler::delete($deleteBannerImage);
                if ($result === false) {
                    throw new \Exception(__('이미지 삭제시 오류가 발생되었습니다.'));
                }
            }
            $postValue['bannerImage'] = '';
        }

        // 배너 이미지 저장
        if (Request::files()->get('bannerImageFile')['error'] == 0 && Request::files()->get('bannerImageFile')['size'] > 0) {
            // 새로운 이미지명 생성 (한글의 경우 문제가 생기는 부분이 있어서 이미지명을 전체적으로 변경함)
            $tmpName = Request::files()->get('bannerImageFile')['name'];
            $tmpExt = FileHandler::getFileInfo($tmpName)->getExtension();
            $postValue['bannerImage'] = md5($tmpName) . '_' . mt_rand(10000, 99999) . '.' . $tmpExt;

            // 경로 설정
            $checkBannerImage = $checkBannerPath . DS . $postValue['bannerImage'];

            // 이미 존재하는 이미지 체크
            if (FileHandler::isExists($checkBannerImage)) {
                $tmpExt = FileHandler::getFileInfo($postValue['bannerImage'])->getExtension();
                $tmpName = FileHandler::getFileInfo($postValue['bannerImage'])->getBasename('.' . $tmpExt);

                // 새로운 이미지명 생성
                $postValue['bannerImage'] = $tmpName . '_' . mt_rand(10000, 99999) . '.' . $tmpExt;
                $checkBannerImage = $checkBannerPath . DS . $postValue['bannerImage'];
            }

            // 이미지 화일 저장
            $result = FileHandler::move(Request::files()->get('bannerImageFile')['tmp_name'], $checkBannerImage);

            if ($result === false) {
                throw new \Exception(__('이미지 파일 저장시 오류가 발생되었습니다.'));
            }

            // 계정용량 갱신 - 스킨
            gd_set_du('skin');
        }

        $arrBind = $this->db->get_binding(DBTableField::tableDesignBanner(), $postValue, $chkType);

        // 저장
        if ($chkType == 'insert') {
            $this->db->set_insert_db(DB_DESIGN_BANNER, $arrBind['param'], $arrBind['bind'], 'y');
            $postValue['sno'] = $this->db->insert_id();
        } elseif ($chkType == 'update') {
            $this->db->bind_param_push($arrBind['bind'], 'i', $postValue['sno']);
            $this->db->set_update_db(DB_DESIGN_BANNER, $arrBind['param'], 'sno = ?', $arrBind['bind']);
        }
        unset($arrBind);

        return $postValue['sno'];
    }

    /**
     * 배너 삭제
     * @param integer $sno 배너 번호
     * @return boolean
     * @throws \Exception
     */
    public function deleteBannerData($sno)
    {
        // Validation
        if (Validator::number($sno, null, null, true) === false) {
            throw new \Exception(sprintf(__('%s 항목이 잘못 되었습니다.'), '배너 번호'));
        }

        // 배너 정보
        $getBannerData = $this->getBannerDetailData($sno);

        // 이미지가 있는지 체크
        if (empty($getBannerData['bannerImage']) === false) {
            // 배너 이미지 경로 설정
            $checkBannerImage = UserFilePath::data('skin', $getBannerData['bannerImagePath'] . $getBannerData['bannerImage']);

            // 이미지 삭제
            if (FileHandler::isExists($checkBannerImage)) {
                $result = FileHandler::delete($checkBannerImage);
                if ($result === false) {
                    throw new \Exception(__('이미지 삭제시 오류가 발생되었습니다.'));
                }

                // 계정용량 갱신 - 스킨
                gd_set_du('skin');
            }
        }

        // 디비 삭제
        $arrBind = [];
        $arrField = ['sno = ?'];
        $this->db->bind_param_push($arrBind, 'i', $sno);
        $this->db->set_delete_db(DB_DESIGN_BANNER, implode(' AND ', $arrField), $arrBind);

        return true;
    }

    /**
     * 배너 그룹 정보 저장
     * @param array $postValue 저장할 정보
     * @return int sno
     * @throws \Exception
     */
    public function saveBannerGroupData(array $postValue)
    {
        // 기본 테이터 체크
        $dataCheck = true;
        if (empty($postValue['bannerGroupDeviceType']) === true) {
            $dataCheck = false;
        }
        if (empty($postValue['skinName']) === true) {
            $dataCheck = false;
        }
        if (empty($postValue['bannerGroupType']) === true) {
            $dataCheck = false;
        }
        if ($postValue['mode'] === 'modify' && empty($postValue['sno']) === true) {
            $dataCheck = false;
        }

        if ($dataCheck === false) {
            throw new \Exception(sprintf(__('%s 항목이 잘못 되었습니다.'), '배너 그룹 정보'));
        }

        // insert , update 체크
        if ($postValue['mode'] == 'banner_group_modify') {
            $chkType = 'update';
        } else {
            $chkType = 'insert';
        }

        // 배너 코드
        if (empty($postValue['bannerGroupCode']) === true) {
            $tmpBannerCode = microtime(true);
            $postValue['bannerGroupCode'] = \Encryptor::checksum($tmpBannerCode);
        }

        // 카테고리 코드
        if ($postValue['bannerGroupType'] == 'category') {
            $postValue['cateCd'] = ArrayUtils::last(gd_isset($postValue['catdCd_category']));
        } else if ($postValue['bannerGroupType'] == 'brand') {
            $postValue['cateCd'] = ArrayUtils::last(gd_isset($postValue['catdCd_brand']));
        }

        $arrBind = $this->db->get_binding(DBTableField::tableDesignBannerGroup(), $postValue, $chkType);

        // 저장
        if ($chkType == 'insert') {
            $this->db->set_insert_db(DB_DESIGN_BANNER_GROUP, $arrBind['param'], $arrBind['bind'], 'y');
            $postValue['sno'] = $this->db->insert_id();
        } elseif ($chkType == 'update') {
            $this->db->bind_param_push($arrBind['bind'], 'i', $postValue['sno']);
            $this->db->set_update_db(DB_DESIGN_BANNER_GROUP, $arrBind['param'], 'sno = ?', $arrBind['bind']);
        }
        unset($arrBind);

        return $postValue['sno'];
    }

    /**
     * 배너 그룹 삭제
     * @param integer $sno 배너 번호
     * @return boolean
     * @throws \Exception
     */
    public function deleteBannerGroupData($sno)
    {
        // Validation
        if (Validator::number($sno, null, null, true) === false) {
            throw new \Exception(sprintf(__('%s 항목이 잘못 되었습니다.'), '배너 그룹 번호'));
        }

        // 배너 그룹 데이터
        $groupData = $this->getBannerGroupDetailData($sno);

        // 배너가 있는지를 체크함
        $bannerCnt = $this->_getBannerCnt($groupData['skinName'], $groupData['bannerGroupCode']);

        if ($bannerCnt > 0) {
            throw new \Exception(__('등록된 배너가 있는 경우 삭제가 되지 않습니다.'));
        }

        // 디비 삭제
        $arrBind = [];
        $arrField = ['sno = ?'];
        $this->db->bind_param_push($arrBind, 'i', $sno);
        $this->db->set_delete_db(DB_DESIGN_BANNER_GROUP, implode(' AND ', $arrField), $arrBind);

        return true;
    }

    /**
     * 움직이는 배너 리스트
     * @return array
     */
    public function getSliderBannerListData($menuType = null)
    {
        $getValue = Request::get()->toArray();

        if (empty($getValue) === true) {
            if ($menuType == 'mobile') $getValue['bannerDeviceType'] = $menuType;
            else $getValue['bannerDeviceType'] = 'front';

            $getValue['skinName'] = Parent::getUseSkinInfo($getValue['bannerDeviceType'], 'Work');
        }

        // 검색 설정
        $setGetKey = ['detailSearch', 'key', 'keyword', 'bannerDeviceType', 'skinName', 'bannerUseFl', 'treatDateFl', 'sort', 'page', 'pageNum',];
        $setGetKey2 = ['treatDate' => ['start', 'end']];
        $setKeyword = ['bannerTitle'];
        $search = [];
        $checked = [];
        $selected = [];

        //검색설정
        $search['sortList'] = [
            'regDt desc' => __('등록일 ↑'),
            'regDt asc' => __('등록일 ↓'),
            'modDt desc' => __('수정일 ↑'),
            'modDt asc' => __('수정일 ↓'),
            'skinName desc' => __('스킨코드 ↑'),
            'skinName asc' => __('스킨코드 ↓'),
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

        $checked['bannerDeviceType'][$search['bannerDeviceType']] =
        $checked['bannerUseFl'][$search['bannerUseFl']] = 'checked="checked"';

        $selected['skinName'][$search['skinName']] = 'selected="selected"';

        // 검색 설정
        $arrWhere = [];
        $arrBind = [];

        // 키워드 검색
        if ($search['key'] && $search['keyword']) {
            if ($search['key'] === 'all') {
                $tmpWhere = [];
                foreach ($setKeyword as $keyNm) {
                    $tmpWhere[] = '(' . $keyNm . ' LIKE concat(\'%\', ?, \'%\'))';
                    $this->db->bind_param_push($arrBind, 's', $search['keyword']);
                }
                $arrWhere[] = '(' . implode(' OR ', $tmpWhere) . ')';
            } else {
                $arrWhere[] = $search['key'] . " LIKE concat('%',?,'%')";
                $this->db->bind_param_push($arrBind, 's', $search['keyword']);
            }
        }

        // 배너 그룹 구분
        if ($search['bannerDeviceType']) {
            $arrWhere[] = 'bannerDeviceType = ?';
            $this->db->bind_param_push($arrBind, 's', $search['bannerDeviceType']);
        }

        // 디자인 스킨 검색
        if ($search['skinName']) {
            $tmpData = explode(STR_DIVISION, $search['skinName']);
            $arrWhere[] = 'skinName = ?';
            $this->db->bind_param_push($arrBind, 's', $tmpData[1]);
            if (empty($search['bannerDeviceType']) === true) {
                $arrWhere[] = 'bannerDeviceType = ?';
                $this->db->bind_param_push($arrBind, 's', $tmpData[0]);
            }
        }

        // 노출여부 검색
        if ($search['bannerUseFl']) {
            if ($search['bannerUseFl']) {
                if ($search['bannerUseFl'] == 'y') {
                    $arrWhere[] = '((bannerUseFl = ? AND bannerPeriodOutputFl = ?) OR (bannerUseFl = ? AND bannerPeriodOutputFl = ? AND (? BETWEEN CONCAT(bannerPeriodSDate, " ", bannerPeriodSTime) AND CONCAT(bannerPeriodEDate, " ", bannerPeriodETime))))';
                    $this->db->bind_param_push($arrBind, 's', $search['bannerUseFl']);
                    $this->db->bind_param_push($arrBind, 's', 'n');
                    $this->db->bind_param_push($arrBind, 's', 'y');
                    $this->db->bind_param_push($arrBind, 's', 'y');
                    $this->db->bind_param_push($arrBind, 's', gd_date_format('Y-m-d H:i:s', 'now'));
                } else {
                    $arrWhere[] = '(bannerUseFl = ? OR (bannerPeriodOutputFl = ? AND (? NOT BETWEEN CONCAT(bannerPeriodSDate, " ", bannerPeriodSTime) AND CONCAT(bannerPeriodEDate, " ", bannerPeriodETime))))';
                    $this->db->bind_param_push($arrBind, 's', $search['bannerUseFl']);
                    $this->db->bind_param_push($arrBind, 's', 'y');
                    $this->db->bind_param_push($arrBind, 's', gd_date_format('Y-m-d H:i:s', 'now'));
                }
            }
        }

        // 기간검색
        if ($search['treatDateFl'] && $search['treatDate']['start'] && $search['treatDate']['end']) {
            $arrWhere[] = '(' . $search['treatDateFl'] . ' BETWEEN ? AND ?)';
            $this->db->bind_param_push($arrBind, 's', $search['treatDate']['start'] . ' 00:00:00');
            $this->db->bind_param_push($arrBind, 's', $search['treatDate']['end'] . ' 23:59:59');
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
        list($page->recode['amount']) = $this->db->fetch('SELECT count(sno) FROM ' . DB_DESIGN_SLIDER_BANNER , 'array'); // 전체 레코드 수
        $page->setPage();
        $page->setUrl(\Request::getQueryString());

        $this->db->strField = implode(', ', DBTableField::setTableField('tableDesignSliderBanner'));
        $this->db->strWhere = implode(' AND ', gd_isset($arrWhere));
        $this->db->strOrder = $sort;
        $this->db->strLimit = $page->recode['start'] . ',' . $search['pageNum'];

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_DESIGN_SLIDER_BANNER . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $arrBind);

        // 배너 경로
        foreach ($data as $key => $val) {
            $data[$key]['bannerImagePath'] = $val['bannerDeviceType'] . DS . $val['skinName'] . DS . $this->bannerPathDefault . DS;
        }

        $this->db->strField = 'count(*) as cnt';
        $this->db->strWhere = implode(' AND ', gd_isset($arrWhere));

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_DESIGN_SLIDER_BANNER . implode(' ', $query);
        $countData = $this->db->query_fetch($strSQL, $arrBind, false);
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
     * 움직이는 배너 정보 저장
     * @param array $postValue 저장할 정보
     * @return int sno
     * @throws \Exception
     */
    public function saveSliderBannerData(array $postValue)
    {
        // 기본 테이터 체크
        $dataCheck = true;
        if (empty($postValue['bannerDeviceType']) === true) {
            $dataCheck = false;
        }
        if (empty($postValue['skinName']) === true) {
            $dataCheck = false;
        }
        if (empty($postValue['bannerTitle']) === true) {
            $dataCheck = false;
        }
        if (empty($postValue['bannerUseFl']) === true) {
            $dataCheck = false;
        }
        if (empty($postValue['bannerSize']['width']) === true || empty($postValue['bannerSize']['height']) === true) {
            $dataCheck = false;
        }
        if ($postValue['mode'] === 'modifySliderBanner' && empty($postValue['sno']) === true) {
            $dataCheck = false;
        }

        if ($dataCheck === false) {
            throw new \Exception(sprintf(__('%s 항목이 잘못 되었습니다.'), '움직이는 배너 정보'));
        }

        if (empty($postValue['bannerPeriodOutputFl']) === true) {
            $postValue['bannerPeriodOutputFl'] = 'n';
        }

        // 배너 코드
        if (empty($postValue['bannerCode']) === true) {
            $tmpBannerCode = microtime(true);
            $postValue['bannerCode'] = \Encryptor::checksum($tmpBannerCode);
        }

        // 날짜 처리
        if ($postValue['bannerPeriodOutputFl'] === 'y') {
            $tmpDataS = explode(' ', $postValue['bannerPeriodSDateY']);
            $tmpDataE = explode(' ', $postValue['bannerPeriodEDateY']);
            $postValue['bannerPeriodSDate'] = $tmpDataS[0];
            $postValue['bannerPeriodSTime'] = $tmpDataS[1] . ':00';
            $postValue['bannerPeriodEDate'] = $tmpDataE[0];
            $postValue['bannerPeriodETime'] = $tmpDataE[1] . ':00';
        } else {
            $postValue['bannerPeriodSDate'] = '';
            $postValue['bannerPeriodSTime'] = '';
            $postValue['bannerPeriodEDate'] = '';
            $postValue['bannerPeriodETime'] = '';
        }
        unset($postValue['bannerPeriodSDateY'], $postValue['bannerPeriodSTimeY'], $postValue['bannerPeriodEDateY'], $postValue['bannerPeriodETimeY']);

        // 배너 설정
        $postValue['bannerSliderConf'] = json_encode($postValue['bannerSliderConf']);

        // 버튼 설정
        $bannerButtonConf['side']['useFl'] = gd_isset($postValue['sideButton']['useFl'], 'y');
        $bannerButtonConf['side']['activeColor'] = gd_isset($postValue['sideButton']['activeColor'], '#ffffff');
        $bannerButtonConf['page']['useFl'] = gd_isset($postValue['pageButton']['useFl'], 'y');
        $bannerButtonConf['page']['activeColor'] = gd_isset($postValue['pageButton']['activeColor'], '#ffffff');
        $bannerButtonConf['page']['inactiveColor'] = gd_isset($postValue['pageButton']['inactiveColor'], '#ffffff');
        $bannerButtonConf['page']['size'] = gd_isset($postValue['pageButton']['size'], '8');
        $bannerButtonConf['page']['radius'] = gd_isset($postValue['pageButton']['radius'], '100');
        $postValue['bannerButtonConf'] = json_encode($bannerButtonConf);

        // 배너 사이즈
        $postValue['bannerSize'] = json_encode($postValue['bannerSize']);

        // insert 인 경우 미리 저장
        if ($postValue['mode'] == 'registerSliderBanner') {
            $postValue['bannerInfo'] = '{}';
            $arrBind = $this->db->get_binding(DBTableField::tableDesignSliderBanner(), $postValue, 'insert');
            $this->db->set_insert_db(DB_DESIGN_SLIDER_BANNER, $arrBind['param'], $arrBind['bind'], 'y');
            $postValue['sno'] = $this->db->insert_id();
            unset($arrBind, $postValue['bannerInfo']);
        }

        // 배너 이미지 폴더
        if (empty($postValue['bannerFolder']) === true) {
            $postValue['bannerFolder'] = 'slider_' . $postValue['bannerCode'];
        }

        // 배너 이미지 경로 설정
        $checkBannerPath = UserFilePath::data('skin', $postValue['bannerDeviceType'], $postValue['skinName'], $this->bannerPathDefault, $postValue['bannerFolder']);

        // 폴더 생성
        if (FileHandler::isDirectory($checkBannerPath) === false) {
            $result = FileHandler::makeDirectory($checkBannerPath, 0707);
            if ($result !== true) {
                throw new \Exception(__('파일 저장중에 오류가 발생하여 실패되었습니다.'));
            }
        }

        // 배너 이미지 저장
        $imgKey = 0;
        foreach (Request::files()->get('bannerImageFile')['name'] as $fKey => $fVal) {
            if (Request::files()->get('bannerImageFile')['error'][$fKey] === '0' && Request::files()->get('bannerImageFile')['size'][$fKey] > 0) {
                // 새로운 이미지명 생성 (한글의 경우 문제가 생기는 부분이 있어서 이미지명을 전체적으로 변경함)
                $tmpExt = FileHandler::getFileInfo($fVal)->getExtension();
                $bannerImage = md5($fVal) . '_' . mt_rand(10000, 99999) . '.' . $tmpExt;

                // 복사할 이미지명
                $tmpImageFile = $checkBannerPath . DS . $bannerImage;

                // 이미지 화일 저장
                if (FileHandler::isExists($tmpImageFile)) {
                    $result = FileHandler::delete($tmpImageFile);
                    if ($result !== true) {
                        throw new \Exception(__('파일 저장중에 오류가 발생하여 실패되었습니다.'));
                    }
                }
                $result = FileHandler::move(Request::files()->get('bannerImageFile')['tmp_name'][$fKey], $tmpImageFile);
                if ($result !== true) {
                    throw new \Exception(__('파일 저장중에 오류가 발생하여 실패되었습니다.'));
                }

                // 계정용량 갱신 - 스킨
                gd_set_du('skin');
            }
            // 기존 이미지명
            else {
                $bannerImage = $postValue['bannerImage'][$fKey];
            }

            if (Request::files()->get('bannerNavActiveImageFile')['error'][$fKey] === '0' && Request::files()->get('bannerNavActiveImageFile')['size'][$fKey] > 0) {
                $bannerNavActiveImageFile = Request::files()->get('bannerNavActiveImageFile')['name'][$fKey];;
                $tmpExt = FileHandler::getFileInfo($bannerNavActiveImageFile)->getExtension();
                $bannerNavActiveImage = md5($bannerNavActiveImageFile) . '_' . mt_rand(10000, 99999) . '.' . $tmpExt;

                // 복사할 이미지명
                $tmpImageFile = $checkBannerPath . DS . $bannerNavActiveImage;

                // 이미지 화일 저장
                if (FileHandler::isExists($tmpImageFile)) {
                    $result = FileHandler::delete($tmpImageFile);
                    if ($result !== true) {
                        throw new \Exception(__('파일 저장중에 오류가 발생하여 실패되었습니다.'));
                    }
                }
                $result = FileHandler::move(Request::files()->get('bannerNavActiveImageFile')['tmp_name'][$fKey], $tmpImageFile);
                if ($result !== true) {
                    throw new \Exception(__('파일 저장중에 오류가 발생하여 실패되었습니다.'));
                }

                // 계정용량 갱신 - 스킨
                gd_set_du('skin');
            } else {
                if (in_array($postValue['bannerNavActiveImage'][$fKey], $postValue['bannerNavActiveDel']) === true) {
                    $bannerNavActiveImage = '';
                } else {
                    $bannerNavActiveImage = $postValue['bannerNavActiveImage'][$fKey];
                }
            }

            if (Request::files()->get('bannerNavInactiveImageFile')['error'][$fKey] === '0' && Request::files()->get('bannerNavInactiveImageFile')['size'][$fKey] > 0) {
                $bannerNavInactiveImageFile = Request::files()->get('bannerNavInactiveImageFile')['name'][$fKey];;
                $tmpExt = FileHandler::getFileInfo($bannerNavInactiveImageFile)->getExtension();
                $bannerNavInactiveImage = md5($bannerNavInactiveImageFile) . '_' . mt_rand(10000, 99999) . '.' . $tmpExt;

                // 복사할 이미지명
                $tmpImageFile = $checkBannerPath . DS . $bannerNavInactiveImage;

                // 이미지 화일 저장
                if (FileHandler::isExists($tmpImageFile)) {
                    $result = FileHandler::delete($tmpImageFile);
                    if ($result !== true) {
                        throw new \Exception(__('파일 저장중에 오류가 발생하여 실패되었습니다.'));
                    }
                }
                $result = FileHandler::move(Request::files()->get('bannerNavInactiveImageFile')['tmp_name'][$fKey], $tmpImageFile);
                if ($result !== true) {
                    throw new \Exception(__('파일 저장중에 오류가 발생하여 실패되었습니다.'));
                }

                // 계정용량 갱신 - 스킨
                gd_set_du('skin');
            } else {
                if (in_array($postValue['bannerNavInactiveImage'][$fKey], $postValue['bannerNavInactiveDel']) === true) {
                    $bannerNavInactiveImage = '';
                } else {
                    $bannerNavInactiveImage = $postValue['bannerNavInactiveImage'][$fKey];
                }
            }

            // 이미지 명이 있는 경우에만 정보 추가
            if (empty($bannerImage) === false) {
                // 이미지 정보 설정
                $postValue['bannerInfo'][$imgKey]['bannerImage'] = $bannerImage;
                $postValue['bannerInfo'][$imgKey]['bannerLink'] = $postValue['bannerLink'][$fKey];
                $postValue['bannerInfo'][$imgKey]['bannerTarget'] = $postValue['bannerTarget'][$fKey];
                $postValue['bannerInfo'][$imgKey]['bannerImageAlt'] = $postValue['bannerImageAlt'][$fKey];

                $postValue['bannerInfo'][$imgKey]['bannerNavActiveImage'] = $bannerNavActiveImage;
                $postValue['bannerInfo'][$imgKey]['bannerNavActiveW'] = $postValue['bannerNavActiveW'][$fKey];
                $postValue['bannerInfo'][$imgKey]['bannerNavActiveH'] = $postValue['bannerNavActiveH'][$fKey];
                $postValue['bannerInfo'][$imgKey]['bannerNavInactiveImage'] = $bannerNavInactiveImage;
                $postValue['bannerInfo'][$imgKey]['bannerNavInactiveW'] = $postValue['bannerNavInactiveW'][$fKey];
                $postValue['bannerInfo'][$imgKey]['bannerNavInactiveH'] = $postValue['bannerNavInactiveH'][$fKey];

                $postValue['bannerInfo'][$imgKey]['bannerImageUseFl'] = $postValue['bannerImageUseFl_' . $postValue['checkKey'][$imgKey]];
                $postValue['bannerInfo'][$imgKey]['bannerImagePeriodFl'] = $postValue['bannerImagePeriodFl_' . $postValue['checkKey'][$imgKey]];
                $postValue['bannerInfo'][$imgKey]['bannerImagePeriodSDate'] = $postValue['bannerImagePeriodSDate'][$fKey];
                $postValue['bannerInfo'][$imgKey]['bannerImagePeriodEDate'] = $postValue['bannerImagePeriodEDate'][$fKey];
                $imgKey++;
            }
        }

        unset($postValue['bannerImage'], $postValue['bannerLink'], $postValue['bannerTarget'], $postValue['bannerImageAlt']);
        unset($postValue['bannerNavActiveImage'], $postValue['bannerNavActiveW'], $postValue['bannerNavActiveH'], $postValue['bannerNavInactiveImage'], $postValue['bannerNavInactiveW'], $postValue['bannerNavInactiveH']);

        // 배너 정보
        $postValue['bannerInfo']['bannerFolder'] = $postValue['bannerFolder']; // 이미지 정보에 저장 경로 세팅
        $postValue['bannerInfo'] = json_encode($postValue['bannerInfo']);

        // 저장
        $arrBind = $this->db->get_binding(DBTableField::tableDesignSliderBanner(), $postValue, 'update');
        $this->db->bind_param_push($arrBind['bind'], 'i', $postValue['sno']);
        $this->db->set_update_db(DB_DESIGN_SLIDER_BANNER, $arrBind['param'], 'sno = ?', $arrBind['bind']);
        unset($arrBind);

        // garbage image 삭제
        $this->_deleteSliderBannerGarbageImage($postValue['bannerInfo'], $postValue['bannerDeviceType'], $postValue['skinName']);

        return $postValue['sno'];
    }

    /**
     * 움직이는 배너 상세 데이터
     *
     * @param integer $sno 페이지 번호
     * @return array|boolean
     * @throws \Exception
     */
    public function getSliderBannerDetailData($sno)
    {
        // Validation
        if (Validator::number($sno, null, null, true) === false) {
            throw new \Exception(__('움직이는 배너 번호 항목이 잘못 되었습니다.'));
        }

        // Data
        $arrBind = $arrWhere = [];
        array_push($arrWhere, 'sno = ?');
        $this->db->bind_param_push($arrBind, 'i', $sno);

        $arrField = DBTableField::setTableField('tableDesignSliderBanner');
        $strSQL = 'SELECT ' . implode(', ', $arrField) . ' FROM ' . DB_DESIGN_SLIDER_BANNER . ' WHERE ' . implode(' AND ', $arrWhere) . ' ORDER BY sno DESC';
        $getData = $this->db->query_fetch($strSQL, $arrBind, false);

        if (empty($getData) === false) {
            // 배너 경로
            $getData['bannerImagePath'] = $getData['bannerDeviceType'] . DS . $getData['skinName'] . DS . $this->bannerPathDefault . DS;

            return gd_htmlspecialchars_stripslashes($getData);
        } else {
            return false;
        }
    }

    /**
     * 움직이는 배너 데이터 (위젯에서 사용)
     *
     * @param string $bannerCode 배너코드
     * @param string $bannerDeviceType 디바이스 타입
     * @param string $skinName 스킨명
     * @return array|boolean
     * @throws \Exception
     */
    public function getSliderBannerData($bannerCode, $bannerDeviceType, $skinName)
    {
        // Validation
        if (empty($bannerCode) === true || empty($bannerDeviceType) === true || empty($skinName) === true) {
            return false;
        }

        // Data
        $arrBind = $arrWhere = [];
        array_push($arrWhere, 'bannerCode = ?');
        $this->db->bind_param_push($arrBind, 's', $bannerCode);
        array_push($arrWhere, 'skinName = ?');
        $this->db->bind_param_push($arrBind, 's', $skinName);
        array_push($arrWhere, 'bannerDeviceType = ?');
        $this->db->bind_param_push($arrBind, 's', $bannerDeviceType);

        $arrField = DBTableField::setTableField('tableDesignSliderBanner');
        $strSQL = 'SELECT ' . implode(', ', $arrField) . ' FROM ' . DB_DESIGN_SLIDER_BANNER . ' WHERE ' . implode(' AND ', $arrWhere);
        $getData = $this->db->query_fetch($strSQL, $arrBind, false);

        if (empty($getData) === false) {
            // 배너 경로
            $getData['bannerImagePath'] = $getData['bannerDeviceType'] . DS . $getData['skinName'] . DS . $this->bannerPathDefault . DS;

            return gd_htmlspecialchars_stripslashes($getData);
        } else {
            return false;
        }
    }

    /**
     * 움직이는 배너 garbage image 삭제
     * @param string $getImageData 이미지 정보 json
     * @param string $bannerDeviceType 디바이스 타입
     * @param string $skinName 스킨명
     * @return boolean
     * @throws \Exception
     */
    private function _deleteSliderBannerGarbageImage($getImageData, $bannerDeviceType, $skinName)
    {
        $tmp = json_decode($getImageData, true);

        // 배너 이미지 경로 설정
        $bannerFolder = $tmp['bannerFolder'];
        $bannerFolder = UserFilePath::data('skin', $bannerDeviceType, $skinName, $this->bannerPathDefault, $bannerFolder);
        unset($tmp['bannerFolder']);

        // 현재 저장된 배너 이미지 배열
        $setBannerImageData = [];
        foreach ($tmp as $imageInfo) {
            if (empty($imageInfo['bannerImage']) === false) {
                $setBannerImageData[] = $imageInfo['bannerImage'];
            }
            if (empty($imageInfo['bannerNavActiveImage']) === false) {
                $setBannerImageData[] = $imageInfo['bannerNavActiveImage'];
            }
            if (empty($imageInfo['bannerNavInactiveImage']) === false) {
                $setBannerImageData[] = $imageInfo['bannerNavInactiveImage'];
            }
        }
        if (empty($setBannerImageData) === true) {
            return true;
        }

        // 저장된 폴더에서 비교 후 삭제
        foreach (new DirectoryIterator($bannerFolder) as $fileInfo) {
            if ($fileInfo->isFile() === true && $fileInfo->isDot() === false) {
                if (in_array($fileInfo->getFilename(), $setBannerImageData) === false) {
                    FileHandler::delete($fileInfo->getPathname());
                }
            }
        }
        return true;
    }

    /**
     * 움직이는 배너 삭제
     * @param integer $sno 배너 번호
     * @return boolean
     * @throws \Exception
     */
    public function deleteSliderBannerData($sno)
    {
        // Validation
        if (Validator::number($sno, null, null, true) === false) {
            throw new \Exception(sprintf(__('%s 항목이 잘못 되었습니다.'), '움직이는 배너 번호'));
        }

        // 배너 정보
        $getData = $this->getSliderBannerDetailData($sno);
        $bannerInfo = json_decode($getData['bannerInfo'], true);

        // 경로 설정
        if (empty($bannerInfo['bannerFolder']) === false) {
            $checkBannerFolder = UserFilePath::data('skin', $getData['bannerDeviceType'], $getData['skinName'], $this->bannerPathDefault, $bannerInfo['bannerFolder']);

            // 이미지 삭제
            $result = FileHandler::delete($checkBannerFolder, true);

            // 계정용량 갱신 - 스킨
            gd_set_du('skin');
        }

        // 디비 삭제
        $arrBind = [];
        $arrField = ['sno = ?'];
        $this->db->bind_param_push($arrBind, 'i', $sno);
        $this->db->set_delete_db(DB_DESIGN_SLIDER_BANNER, implode(' AND ', $arrField), $arrBind);

        return true;
    }

    /**
     * 스킨 다운용 배너 데이터
     *
     * @param string $bannerDeviceType 디바이스 타입
     * @param string $skinName 스킨명
     * @return boolean
     * @throws \Exception
     */
    public function getBannerDownData($bannerDeviceType, $skinName)
    {
        $arrBannerQuery = $arrBind = $arrWhere = [];

        // 배너 그룹
        $tableName = DB_DESIGN_BANNER_GROUP;
        $tableField = DBTableField::setTableField('tableDesignBannerGroup', null, ['sno', 'regDt', 'modDt']);

        $this->db->strField = implode(', ', $tableField);
        $this->db->strWhere = 'skinName = \'' . $skinName . '\' AND bannerGroupDeviceType = \'' . $bannerDeviceType . '\'';
        $this->db->strOrder = 'sno ASC';

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . $tableName . ' as db' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL);

        $arrFieldKey = [];
        $arrFieldVal = [];
        $bannerGroupCode = [];
        foreach ($data as $dKey => $dVal) {
            foreach ($tableField as $dbVal) {
                if ($dKey === 0) {
                    $arrFieldKey[] = $dbVal;
                }
                if ($dbVal === 'skinName') {
                    $arrFieldVal[$dKey][] = '___SKIN_NAME___';
                } else {
                    if ($dbVal === 'bannerGroupCode') {
                        $bannerGroupCode[] = $dVal[$dbVal];
                    }
                    $arrFieldVal[$dKey][] = $dVal[$dbVal];
                }
            }
        }

        // 쿼리 생성
        $insertString = 'INSERT INTO ' . $tableName . ' ( ' . implode(', ', $arrFieldKey) . ', regDt ) VALUES ( ';
        foreach ($arrFieldVal as $bannerKey => $bannerVal) {
            $arrBannerQuery[] = $insertString . '\'' . implode('\', \'', gd_htmlspecialchars($bannerVal)) . '\' ,now() )';
        }

        // 배너
        $tableName = DB_DESIGN_BANNER;
        $tableField = DBTableField::setTableField('tableDesignBanner', null, ['sno', 'regDt', 'modDt']);

        $this->db->strField = implode(', ', $tableField);
        $this->db->strWhere = 'skinName = \'' . $skinName . '\' AND bannerGroupCode IN (\'' . implode('\', \'', $bannerGroupCode) . '\')';
        $this->db->strOrder = 'sno ASC';

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . $tableName . ' as db' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL);

        $arrFieldKey = [];
        $arrFieldVal = [];
        foreach ($data as $dKey => $dVal) {
            foreach ($tableField as $dbVal) {
                if ($dKey === 0) {
                    $arrFieldKey[] = $dbVal;
                }
                if ($dbVal === 'skinName') {
                    $arrFieldVal[$dKey][] = '___SKIN_NAME___';
                } else {
                    $arrFieldVal[$dKey][] = $dVal[$dbVal];
                }
            }
        }

        // 쿼리 생성
        $insertString = 'INSERT INTO ' . $tableName . ' ( ' . implode(', ', $arrFieldKey) . ', regDt ) VALUES ( ';
        foreach ($arrFieldVal as $bannerKey => $bannerVal) {
            $arrBannerQuery[] = $insertString . '\'' . implode('\', \'', gd_htmlspecialchars($bannerVal)) . '\' ,now() )';
        }

        // 움직이는 배너
        $tableName = DB_DESIGN_SLIDER_BANNER;
        $tableField = DBTableField::setTableField('tableDesignSliderBanner', null, ['sno', 'regDt', 'modDt']);

        $this->db->strField = implode(', ', $tableField);
        $this->db->strWhere = 'skinName = \'' . $skinName . '\' AND bannerDeviceType = \'' . $bannerDeviceType . '\'';
        $this->db->strOrder = 'sno ASC';

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . $tableName . ' as db' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL);

        $arrFieldKey = [];
        $arrFieldVal = [];
        foreach ($data as $dKey => $dVal) {
            foreach ($tableField as $dbVal) {
                if ($dKey === 0) {
                    $arrFieldKey[] = $dbVal;
                }
                if ($dbVal === 'skinName') {
                    $arrFieldVal[$dKey][] = '___SKIN_NAME___';
                } else {
                    $arrFieldVal[$dKey][] = $dVal[$dbVal];
                }
            }
        }

        // 쿼리 생성
        $insertString = 'INSERT INTO ' . $tableName . ' ( ' . implode(', ', $arrFieldKey) . ', regDt ) VALUES ( ';
        foreach ($arrFieldVal as $bannerKey => $bannerVal) {
            $arrBannerQuery[] = $insertString . '\'' . implode('\', \'', $bannerVal) . '\' ,now() )';
        }

        // 추가 SQL 데이터
        $addQuery = $this->_getBannerDownDataAdd($bannerDeviceType);

        // merge
        $arrBannerQuery = array_merge($arrBannerQuery, $addQuery);

        return $arrBannerQuery;
    }

    /**
     * 스킨 다운용 배너 데이터에 추가할 Query 데이터
     *  - 게시판 테마 Query
     *
     * @param string $bannerDeviceType 디바이스 타입
     * @return array add query
     */
    private function _getBannerDownDataAdd($bannerDeviceType)
    {
        // 디바이스 타입에 따른 모바일 여부
        if ($bannerDeviceType === 'mobile') {
            $bdMobileFl = 'y';
        } else {
            $bdMobileFl = 'n';
        }

        $addQuery = [];
        $addQuery[] = 'INSERT INTO es_boardTheme (themeId, themeNm, liveSkin, bdBasicFl, bdKind, bdAlign, bdWidth, bdWidthUnit, bdListLineSpacing, bdMobileFl, regDt) VALUES (\'default\', \'일반형(기본)\', \'___SKIN_NAME___\', \'y\', \'default\', \'center\', 100, \'%\', 10, \'' . $bdMobileFl . '\', now())';
        $addQuery[] = 'INSERT INTO es_boardTheme (themeId, themeNm, liveSkin, bdBasicFl, bdKind, bdAlign, bdWidth, bdWidthUnit, bdListLineSpacing, bdMobileFl, regDt) VALUES (\'qa\', \'1:1문의(기본)\', \'___SKIN_NAME___\', \'y\', \'qa\', \'center\', 100, \'%\', 50, \'' . $bdMobileFl . '\', now())';
        $addQuery[] = 'INSERT INTO es_boardTheme (themeId, themeNm, liveSkin, bdBasicFl, bdKind, bdAlign, bdWidth, bdWidthUnit, bdListLineSpacing, bdMobileFl, regDt) VALUES (\'event\', \'이벤트(기본)\', \'___SKIN_NAME___\', \'y\', \'event\', \'center\', 100, \'%\', 150, \'' . $bdMobileFl . '\', now())';
        $addQuery[] = 'INSERT INTO es_boardTheme (themeId, themeNm, liveSkin, bdBasicFl, bdKind, bdAlign, bdWidth, bdWidthUnit, bdListLineSpacing, bdMobileFl, regDt) VALUES (\'gallery\', \'갤러리(기본)\', \'___SKIN_NAME___\', \'y\', \'gallery\', \'center\', 100, \'%\', 50, \'' . $bdMobileFl . '\', now())';

        return $addQuery;
    }

    /**
     * 스킨용 배너 전체 삭제
     *
     * @param string $bannerDeviceType 디바이스 타입
     * @param string $skinName 스킨명
     * @return boolean
     * @throws \Exception
     */
    public function deleteBannerAllSkin($bannerDeviceType, $skinName)
    {
        // 배너 그룹
        $strSQL = 'SELECT sno, bannerGroupCode FROM ' . DB_DESIGN_BANNER_GROUP . ' WHERE skinName = \'' . $skinName . '\' AND bannerGroupDeviceType = \'' . $bannerDeviceType . '\'';
        $getBannerGroupData = $this->db->query_fetch($strSQL);
        $bannerGroupCode = [];
        if (empty($getBannerGroupData) === false) {
            foreach ($getBannerGroupData as $val) {
                $bannerGroupCode[] = $val['bannerGroupCode'];
            }

            // 배너 삭제
            $strSQL = 'SELECT sno FROM ' . DB_DESIGN_BANNER . ' WHERE skinName = \'' . $skinName . '\' AND bannerGroupCode IN (\'' .  implode('\', \'', $bannerGroupCode) . '\')';
            $getData = $this->db->query_fetch($strSQL);
            if (empty($getData) === false) {
                foreach ($getData as $val) {
                    $this->deleteBannerData($val['sno']);
                }
            }

            // 배너 그룹 삭제
            foreach ($getBannerGroupData as $val) {
                $this->deleteBannerGroupData($val['sno']);
            }
            unset($getData);
        }

        // 움직이는 배너 삭제
        $strSQL = 'SELECT sno FROM ' . DB_DESIGN_SLIDER_BANNER . ' WHERE skinName = \'' . $skinName . '\' AND bannerDeviceType = \'' . $bannerDeviceType . '\'';
        $getData = $this->db->query_fetch($strSQL);
        if (empty($getData) === false) {
            foreach ($getData as $val) {
                $this->deleteSliderBannerData($val['sno']);
            }
        }

        return true;
    }

    /**
     * 하단 로고 데이터
     *
     */
    public function getFooterLogoByLive()
    {
        $skinConf = gd_policy('design.skin');
        $skinName = $skinConf['frontLive'];

        $arrBind = $arrWhere = [];
        array_push($arrWhere, 'db.skinName = ?');
        $this->db->bind_param_push($arrBind, 's', $skinName);
        array_push($arrWhere, 'db.bannerUseFl = ?');
        $this->db->bind_param_push($arrBind, 's', 'y');
        array_push($arrWhere, 'db.bannerUseFl != ?');
        $this->db->bind_param_push($arrBind, 's', '');
        array_push($arrWhere, 'dbg.bannerGroupName = ?');
        $this->db->bind_param_push($arrBind, 's', __('하단 로고'));
        array_push($arrWhere, 'dbg.bannerGroupType = ?');
        $this->db->bind_param_push($arrBind, 's', 'logo');

        $strSQL = 'SELECT * FROM ' . DB_DESIGN_BANNER . ' AS db';
        $strSQL .= ' LEFT JOIN ' . DB_DESIGN_BANNER_GROUP . ' AS dbg ON db.bannerGroupCode=dbg.bannerGroupCode';
        $strSQL .= ' WHERE ' . implode(' AND ', $arrWhere) . ' ORDER BY db.bannerSort DESC';

        $getData = $this->db->query_fetch($strSQL, $arrBind);

        return $getData;
    }

    /**
     * 노출 기간 설정시 노출기간이 지난 배너는 노출여부를 미노출로 업데이트 처리
     *
     */
    public function updateUseFl()
    {
        $strSQL = 'UPDATE es_designSliderBanner SET bannerUseFl = \'n\' WHERE bannerUseFl = \'y\' AND bannerPeriodOutputFl =\'y\' AND bannerPeriodEDate != \'0000-00-00\' AND CONCAT(bannerPeriodEDate, \' \', bannerPeriodETime) < now()';
        $this->db->query($strSQL);
    }
}
