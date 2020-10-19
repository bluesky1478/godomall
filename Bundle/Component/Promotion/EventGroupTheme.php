<?php
/**
 * 기획전 그룹
 * @author bumyul
 * @version 1.0
 * @since 1.0
 * @copyright ⓒ 2017, NHN godo: Corp.
 */
namespace Bundle\Component\Promotion;

use Component\Storage\Storage;
use Component\Database\DBTableField;
use Component\Validator\Validator;
use Session;
use Request;
use Exception;
use FileHandler;
use UserFilePath;

class EventGroupTheme
{

    public function __construct()
    {
        if (!is_object($this->db)) {
            $this->db = \App::load('DB');
        }
    }

    /**
     * 기획전 그룹 정보 로드
     *
     * @param array $eventGroupGetInfo
     * @return array $getData
     */
    public function getDataEventGroupTheme($eventGroupGetInfo=array())
    {
        $checked = $selected = $data = array();

        if(count($eventGroupGetInfo) < 1){
            //등록
            DBTableField::setDefaultData('tableDisplayEventGroupTheme', $data);

            $data['mode'] = 'event_group_register';
        }
        else {
            //수정
            $data = $this->getSingleData($eventGroupGetInfo['loadType'], $eventGroupGetInfo['eventGroupSno']);
            $data['mode'] = 'event_group_modify';

            if(trim($data['groupNameImagePc']) !== '' || trim($data['groupNameImageMobile']) !== ''){
                $imageFolderPath = ($eventGroupGetInfo['loadType'] === 'real') ? '/data/event_group/' : '/data/event_group_tmp/';
                if(trim($data['groupNameImagePc']) !== ''){
                    $data['groupNameImagePcTag'] = "<img src='".$imageFolderPath.$data['groupNameImagePc']."' width='30' height='30' alt='그룹이미지PC' onclick=\"javascript:image_viewer('".$imageFolderPath.$data['groupNameImagePc']."', '', '');\" class='hand' />";
                }
                if(trim($data['groupNameImageMobile']) !== ''){
                    $data['groupNameImageMobileTag'] = "<img src='".$imageFolderPath.$data['groupNameImageMobile']."' width='30' height='30' alt='그룹이미지MOBILE' onclick=\"javascript:image_viewer('".$imageFolderPath.$data['groupNameImageMobile']."', '', '');\" class='hand' />";
                }
            }

            $groupGoodsNoArray = @explode(STR_DIVISION, $data['groupGoodsNo']);
            if(count($groupGoodsNoArray) > 0){
                $goods = \App::load('\\Component\\Goods\\Goods');
                foreach ($groupGoodsNoArray as $k => $v) {
                    if ($v) {
                        $data['goodsNo'][$k] = $goods->getGoodsDataDisplay($v);
                    } else {
                        $data['goodsNo'][$k] =[];
                    }
                }
            }
        }

        $checked['groupMoreTopFl'][$data['groupMoreTopFl']] = $checked['groupMoreBottomFl'][$data['groupMoreBottomFl']] = 'checked="checked"';
        $selected['groupThemeCd'][$data['groupThemeCd']] = $selected['groupMobileThemeCd'][$data['groupMobileThemeCd']] = $selected['groupSort'][$data['groupSort']] = "selected='selected'";

        $getData = array(
            'data' => $data,
            'checked' => $checked,
            'selected' => $selected,
        );

        return $getData;
    }

    /**
     * 기획전 그룹 임시 등록
     *
     * @param array $arrData
     * @param integer $originalGroupSno
     * @throws Exception
     * @return integer $eventGroupTmpNo
     */
    public function registEventGroupThemeTmp($arrData, $originalGroupSno=0)
    {
        //등록자
        $arrData['groupManagerNo'] = Session::get('manager.sno');

        //그룹명
        if (Validator::required(gd_isset($arrData['groupName'])) === false) {
            throw new \Exception(__('그룹명은 필수 항목입니다.'), 500);
        }

        //상품번호
        $arrData['groupGoodsNo'] = implode(STR_DIVISION, $arrData['goodsNoData']);
        //정렬고정 상품번호
        $arrData['groupFixGoodsNo'] = implode(STR_DIVISION, $arrData['sortFix']);

        //DB 등록
        $arrBind = $this->db->get_binding(DBTableField::tableDisplayEventGroupThemeTmp(), $arrData, 'insert');
        $this->db->set_insert_db(DB_DISPLAY_EVENT_GROUP_THEME_TMP, $arrBind['param'], $arrBind['bind'], 'y');
        $eventGroupTmpNo = $this->db->insert_id();
        if((int)$eventGroupTmpNo < 1){
            throw new \Exception(__('등록을 실패했습니다.\n다시 시도해 주세요.'), 500);
        }

        $arrBind = null;
        unset($arrBind);

        FileHandler::chmod(UserFilePath::data('event_group_tmp'), 0707);
        FileHandler::chmod(UserFilePath::data('event_group'), 0707);

        $updateFileData = array();
        //실제 데이터 수정의 경우 기존데이터의 이미지를 copy 처리 한다
        if((int)$originalGroupSno > 0){
            $originalData = $this->getSingleData('real', $originalGroupSno);
            if(trim($originalData['groupNameImagePc']) !== '' && trim($arrData['deleteImagePc']) === ''){
                $updateFileData['groupNameImagePc'] = preg_replace('/^[0-9]{1,}/', $eventGroupTmpNo, $originalData['groupNameImagePc']);
                FileHandler::copy(UserFilePath::data('event_group', $originalData['groupNameImagePc']), UserFilePath::data('event_group_tmp', $updateFileData['groupNameImagePc']));
            }
            if(trim($originalData['groupNameImageMobile']) !== '' && trim($arrData['deleteImageMobile']) === ''){
                $updateFileData['groupNameImageMobile'] = preg_replace('/^[0-9]{1,}/', $eventGroupTmpNo, $originalData['groupNameImageMobile']);
                FileHandler::copy(UserFilePath::data('event_group', $originalData['groupNameImageMobile']), UserFilePath::data('event_group_tmp', $updateFileData['groupNameImageMobile']));
            }
        }

        //파일 등록
        $fileData = Request::files()->toArray();
        if(count($fileData) > 0){
            foreach($fileData as $keyName => $value){
                if(trim($value['name']) !== ''){
                    if (gd_file_uploadable($value, 'image') === true) {
                        $updateFileData[$keyName] = $eventGroupTmpNo . '_' . $keyName . strrchr($value['name'], '.');
                        FileHandler::chmod(UserFilePath::data('event_group_tmp'), 0707);
                        Storage::disk(Storage::PATH_CODE_EVENT_GROUP_TMP)->upload($value['tmp_name'], $updateFileData[$keyName]);
                    }
                }
            }
        }

        if(count($updateFileData) > 0) {
            $excludeField = array();
            if(trim($updateFileData['groupNameImagePc']) === ''){
                $excludeField[] = 'groupNameImagePc';
            }
            if(trim($updateFileData['groupNameImageMobile']) === ''){
                $excludeField[] = 'groupNameImageMobile';
            }

            $arrBind = $this->db->get_binding(DBTableField::getBindField('tableDisplayEventGroupThemeTmp', array_keys($updateFileData)), $updateFileData, 'update', null, $excludeField);
            $this->db->bind_param_push($arrBind['bind'], 'i', $eventGroupTmpNo);
            $this->db->set_update_db(DB_DISPLAY_EVENT_GROUP_THEME_TMP, $arrBind['param'], 'sno = ?', $arrBind['bind']);
        }

        return $eventGroupTmpNo;
    }

    /*
     * 그룹 수정
     *
     * @param array $arrData
     * @return integer $arrData['eventGroupSno']
     */
    public function modifyEventGroupThemeAll($arrData)
    {
        //그룹명
        if (Validator::required(gd_isset($arrData['groupName'])) === false) {
            throw new \Exception(__('그룹명은 필수 항목입니다.'), 500);
        }

        if($arrData['loadType'] === 'real'){
            //임시 정보로 저장
            $eventGroupTmpNo = $this->registEventGroupThemeTmp($arrData, $arrData['eventGroupSno']);

            return $eventGroupTmpNo;
        }
        else {
            $updateDataType = array(
                'filePath' => 'event_group_tmp',
                'uploadPath' => Storage::PATH_CODE_EVENT_GROUP_TMP,
                'DBTableField' => DBTableField::tableDisplayEventGroupThemeTmp(),
                'DBTable' => DB_DISPLAY_EVENT_GROUP_THEME_TMP,
            );

            //상품번호
            $arrData['groupGoodsNo'] = implode(STR_DIVISION, $arrData['goodsNoData']);
            //정렬고정 상품번호
            $arrData['groupFixGoodsNo'] = implode(STR_DIVISION, $arrData['sortFix']);

            FileHandler::chmod(UserFilePath::data($updateDataType['filePath']), 0707);

            if(trim($arrData['deleteImagePc']) !== ''){
                Storage::disk($updateDataType['filePath'])->delete($arrData['deleteImagePc']);
            }
            if(trim($arrData['deleteImageMobile']) !== ''){
                Storage::disk($updateDataType['filePath'])->delete($arrData['deleteImageMobile']);
            }

            //파일 등록
            $fileData = Request::files()->toArray();
            if(count($fileData) > 0){
                foreach($fileData as $keyName => $value){
                    if(trim($value['name']) !== ''){
                        if (gd_file_uploadable($value, 'image') === true) {
                            $arrData[$keyName] = $arrData['eventGroupSno'] . '_' . $keyName . strrchr($value['name'], '.');
                            FileHandler::chmod(UserFilePath::data($updateDataType['filePath']), 0707);
                            Storage::disk($updateDataType['uploadPath'])->upload($value['tmp_name'], $arrData[$keyName]);
                        }
                    }
                }
            }

            $excludeField = array();
            if(trim($arrData['groupNameImagePc']) === ''){
                $excludeField[] = 'groupNameImagePc';
            }
            if(trim($arrData['groupNameImageMobile']) === ''){
                $excludeField[] = 'groupNameImageMobile';
            }

            $arrBind = $this->db->get_binding($updateDataType['DBTableField'], $arrData, 'update', null, $excludeField);
            $this->db->bind_param_push($arrBind['bind'], 'i', $arrData['eventGroupSno']);
            $this->db->set_update_db($updateDataType['DBTable'], $arrBind['param'], 'sno = ?', $arrBind['bind']);

            return $arrData['eventGroupSno'];
        }
    }

    /*
     * 그룹 복사
     *
     * @param array $arrData
     * @return integer $eventInsertId
     */
    public function copyEventGroupThemeAll($arrData)
    {
        if((int)$arrData['eventGroupNo'] > 0){
            //실제 그룹
            $groupData = $this->getSingleData('real', $arrData['eventGroupNo']);
            $filePathType = 'event_group';
        }
        else {
            //임시 그룹
            $groupData = $this->getSingleData('temp', $arrData['eventGroupTempNo']);
            $filePathType = 'event_group_tmp';
        }

        //DB 등록
        $arrBind = $this->db->get_binding(DBTableField::tableDisplayEventGroupThemeTmp(), $groupData, 'insert');
        $this->db->set_insert_db(DB_DISPLAY_EVENT_GROUP_THEME_TMP, $arrBind['param'], $arrBind['bind'], 'y');
        $eventInsertId = $this->db->insert_id();

        if(trim($groupData['groupNameImagePc']) !== '' || trim($groupData['groupNameImageMobile']) !== ''){
            FileHandler::chmod(UserFilePath::data('event_group_tmp'), 0707);
            FileHandler::chmod(UserFilePath::data('event_group'), 0707);

            $arrBind = $arrParam = [];
            if(trim($groupData['groupNameImagePc']) !== ''){
                $newImageNamePc = preg_replace('/^[0-9]{1,}/', $eventInsertId, $groupData['groupNameImagePc']);
                FileHandler::copy(UserFilePath::data($filePathType, $groupData['groupNameImagePc']), UserFilePath::data('event_group_tmp', $newImageNamePc));
                $this->db->bind_param_push($arrBind['bind'], 's', $newImageNamePc);
                $arrParam[] = 'groupNameImagePc=?';
            }
            if(trim($groupData['groupNameImageMobile']) !== ''){
                $newImageNameMobile = preg_replace('/^[0-9]{1,}/', $eventInsertId, $groupData['groupNameImageMobile']);
                FileHandler::copy(UserFilePath::data($filePathType, $groupData['groupNameImageMobile']), UserFilePath::data('event_group_tmp', $newImageNameMobile));
                $this->db->bind_param_push($arrBind['bind'], 's', $newImageNameMobile);
                $arrParam[] = 'groupNameImageMobile=?';
            }

            if(count($arrBind) > 0){
                $this->db->bind_param_push($arrBind['bind'], 'i', $eventInsertId);
                $this->db->set_update_db(DB_DISPLAY_EVENT_GROUP_THEME_TMP, $arrParam, 'sno = ?', $arrBind['bind']);
            }
        }

        return $eventInsertId;
    }

    /**
     * 실제 기획전 그룹 등록
     *
     * @param integer $eventGroupTempNo
     * @param integer $groupThemeSno
     * @return integer $insertSno
     */
    public function saveEventGroupTheme($eventGroupTempNo, $groupThemeSno)
    {
        //temp data load
        $tempData = $this->getSingleData('temp', $eventGroupTempNo);
        $tempData['groupThemeSno'] = $groupThemeSno;

        //insert real event group data
        $arrBind = $this->db->get_binding(DBTableField::tableDisplayEventGroupTheme(), $tempData, 'insert');
        $this->db->set_insert_db(DB_DISPLAY_EVENT_GROUP_THEME, $arrBind['param'], $arrBind['bind'], 'y');
        $insertSno = $this->db->insert_id();
        if((int)$insertSno > 0){
            FileHandler::chmod(UserFilePath::data('event_group_tmp'), 0707);

            //check real directory
            if (FileHandler::isDirectory(UserFilePath::data('event_group')) === false){
                FileHandler::makeDirectory(UserFilePath::data('event_group'), 0707);
            } else {
                FileHandler::chmod(UserFilePath::data('event_group'), 0707);
            }

            //이미지 존재시 이동
            $updateFileData = array();
            if(trim($tempData['groupNameImagePc']) !== ''){
                $updateFileData['groupNameImagePc'] = preg_replace('/^[0-9]{1,}/', $insertSno, $tempData['groupNameImagePc']);
                FileHandler::move(UserFilePath::data('event_group_tmp', $tempData['groupNameImagePc']), UserFilePath::data('event_group', $updateFileData['groupNameImagePc']));
            }
            if(trim($tempData['groupNameImageMobile']) !== ''){
                $updateFileData['groupNameImageMobile'] = preg_replace('/^[0-9]{1,}/', $insertSno, $tempData['groupNameImageMobile']);
                FileHandler::move(UserFilePath::data('event_group_tmp', $tempData['groupNameImageMobile']), UserFilePath::data('event_group', $updateFileData['groupNameImageMobile']));
            }

            if(count($updateFileData) > 0) {
                $excludeField = array();
                if(trim($updateFileData['groupNameImagePc']) === ''){
                    $excludeField[] = 'groupNameImagePc';
                }
                if(trim($updateFileData['groupNameImageMobile']) === ''){
                    $excludeField[] = 'groupNameImageMobile';
                }

                $arrBind = [];
                $arrBind = $this->db->get_binding(DBTableField::getBindField('tableDisplayEventGroupTheme', array_keys($updateFileData)), $updateFileData, 'update', null, $excludeField);
                $this->db->bind_param_push($arrBind['bind'], 'i', $insertSno);
                $this->db->set_update_db(DB_DISPLAY_EVENT_GROUP_THEME, $arrBind['param'], 'sno = ?', $arrBind['bind']);
            }
        }

        return $insertSno;
    }

    /**
     * 단건의 그룹 데이터 로드
     *
     * @param string $type
     * @param integer $eventGroupNo
     * @return array $returnData
     */
    public function getSingleData($type, $eventGroupNo)
    {
        if($type === 'real'){
            $group_table = DB_DISPLAY_EVENT_GROUP_THEME;
        }
        else {
            $group_table = DB_DISPLAY_EVENT_GROUP_THEME_TMP;
        }

        $strWhere = 'sno = ?';
        $this->db->bind_param_push($arrBind, 'i', $eventGroupNo);
        $strSQL = 'SELECT * FROM ' . $group_table . ' WHERE ' . $strWhere;
        $returnData = $this->db->query_fetch($strSQL, $arrBind)[0];

        $returnData['groupGoodsCount'] = 0;
        if(trim($returnData['groupGoodsNo']) !== ''){
            $returnData['groupGoodsCount'] += count(explode(STR_DIVISION, $returnData['groupGoodsNo']));
        }
        if(trim($returnData['groupFixGoodsNo']) !== ''){
            $returnData['groupGoodsCount'] += count(explode(STR_DIVISION, $returnData['groupFixGoodsNo']));
        }

        return $returnData;
    }

    /*
     * 기획전 리스트에서의 기획전 삭제시 그룹 삭제
     *
     * @param integer $groupThemeSno
     * @return void
     */
    public function deleteEventGroupTheme($groupThemeSno)
    {
        $eventGroupData = $this->getSimpleData($groupThemeSno);
        if(count($eventGroupData) > 0){
            foreach($eventGroupData as $key => $valueArray){
                $this->deleteOriginalEventData($valueArray['sno'], $valueArray);
            }
        }
    }

    /*
     * 그룹리스트 로드
     *
     * @param void
     * @return array $getData
     */
    public function getDataEventGroupLoadList()
    {
        $getValue = Request::get()->toArray();

        $this->setSearchEventGroupLoadList($getValue);

        gd_isset($getValue['page'], 1);
        gd_isset($getValue['pageNum'], 10);

        $page = \App::load('\\Component\\Page\\Page', $getValue['page']);
        $page->page['list'] = $getValue['pageNum'];
        $page->setPage();

        $join[] = ' LEFT JOIN ' . DB_DISPLAY_EVENT_GROUP_THEME . ' AS eg ON dt.sno = eg.groupThemeSno ';
        $join[] = ' LEFT OUTER JOIN ' . DB_MANAGER . ' AS m ON m.sno = dt.managerNo ';

        $this->db->strField = "dt.sno, dt.themeNm, dt.displayCategory, dt.pcFl, dt.mobileFl, dt.displayStartDate, dt.displayEndDate, eg.sno AS groupSno, eg.groupName";
        $this->db->strJoin = implode('', $join);
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
        $this->db->strOrder = 'dt.regdt DESC, eg.sno DESC';
        $this->db->strLimit = $page->recode['start'] . ',' . $getValue['pageNum'];

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_DISPLAY_THEME . ' AS dt ' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $this->arrBind);

        /* 검색 count 쿼리 */
        if(count($this->arrWhere) > 0){
            $totalCountSQL =  ' SELECT COUNT(dt.sno) AS totalCnt FROM ' . DB_DISPLAY_THEME . ' AS dt  ' . implode('', $join) . ' WHERE ' . implode(' AND ', gd_isset($this->arrWhere));
        }
        else {
            $totalCountSQL =  ' SELECT COUNT(dt.sno) AS totalCnt FROM ' . DB_DISPLAY_THEME . ' AS dt  ' . implode('', $join);
        }
        $dataCount = $this->db->query_fetch($totalCountSQL, $this->arrBind);
        $page->recode['total'] = $dataCount[0]['totalCnt']; //검색 레코드 수

        $page->setPage();
        $page->setUrl(Request::getQueryString());

        unset($this->arrBind);

        $getData['data'] = gd_htmlspecialchars_stripslashes(gd_isset($data));
        $getData['search'] = gd_htmlspecialchars($this->search);
        $getData['checked'] = $this->checked;
        $getData['selected'] = $this->selected;

        return $getData;
    }

    /*
     * 그룹리스트 로드
     *
     * @param null $getValue
     * @return void
     */
    public function setSearchEventGroupLoadList($getValue = null)
    {
        if (is_null($getValue)) $getValue = Request::get()->toArray();

        $fieldTypeDisplayTheme = DBTableField::getFieldTypes('tableDisplayTheme');

        $this->search['eventSaleListSelect'] = array('all' => __('=통합검색='), 'themeNm' => __('기획전명'), 'writer' => __('등록자'));    //기획전

        $this->search['key'] = gd_isset($getValue['key']);
        $this->search['keyword'] = gd_isset($getValue['keyword']);
        $this->search['device'] = gd_isset($getValue['device']);
        $this->search['displayCategory'] = gd_isset($getValue['displayCategory']);
        $this->search['statusText'] = gd_isset($getValue['statusText']);
        $this->search['kind'] = gd_isset($getValue['kind'], 'event');

        $this->checked['displayCategory'][$this->search['displayCategory']] = $this->checked['device'][$this->search['device']] = $this->checked['statusText'][$this->search['statusText']] = 'checked="checked"';


        $this->arrWhere[] = "dt.kind= ? ";
        $this->db->bind_param_push($this->arrBind, 's', $this->search['kind']);

        // 검색어
        if ($this->search['key'] && $this->search['keyword']) {
            if ($this->search['key'] == 'all') {
                $tmpWhere = array('dt.themeNm', 'dt.themeDescription');
                $arrWhereAll = array();
                foreach ($tmpWhere as $keyNm) {
                    $arrWhereAll[] = '(' . $keyNm . ' LIKE concat(\'%\',?,\'%\'))';
                    $this->db->bind_param_push($this->arrBind, 's', $this->search['keyword']);
                }
                $this->arrWhere[] = '(' . implode(' OR ', $arrWhereAll) . ')';
            } else {
                if ($this->search['key'] == 'writer') {
                    $this->arrWhere[] = '(m.managerId LIKE concat(\'%\',?,\'%\') OR m.managerNm LIKE concat(\'%\',?,\'%\'))';
                    $this->db->bind_param_push($this->arrBind, 's', $this->search['keyword']);
                    $this->db->bind_param_push($this->arrBind, 's', $this->search['keyword']);
                } else {
                    $this->arrWhere[] = 'dt.' . $this->search['key'] . ' LIKE concat(\'%\',?,\'%\')';
                    $this->db->bind_param_push($this->arrBind, 's', $this->search['keyword']);
                }
            }
        }
        //노출범위
        if ($this->search['displayCategory']) {
            $this->arrWhere[] = 'dt.displayCategory = ?';
            $this->db->bind_param_push($this->arrBind, $fieldTypeDisplayTheme['displayCategory'], $this->search['displayCategory']);
        }

        //노출범위
        if ($this->search['device']) {
            $_pcFl = substr($this->search['device'], 0, 1);
            $_mobileFl = substr($this->search['device'], 1, 1);
            $this->arrWhere[] = 'dt.pcFl = ?';
            $this->arrWhere[] = 'dt.mobileFl = ?';
            $this->db->bind_param_push($this->arrBind, $fieldTypeDisplayTheme['pcFl'], $_pcFl);
            $this->db->bind_param_push($this->arrBind, $fieldTypeDisplayTheme['mobileFl'], $_mobileFl);
        }
        //진행상태
        if ($this->search['statusText']) {
            $nowDate = date("Y-m-d H:i:s");
            switch($this->search['statusText']){
                //대기
                case 'product':
                    $this->arrWhere[] = '? < dt.displayStartDate';
                    $this->db->bind_param_push($this->arrBind, $fieldTypeDisplayTheme['displayStartDate'], $nowDate);
                    break;

                //진행중
                case 'order':
                    $this->arrWhere[] = '(? > dt.displayStartDate && ? < dt.displayEndDate)';
                    $this->db->bind_param_push($this->arrBind, $fieldTypeDisplayTheme['displayStartDate'], $nowDate);
                    $this->db->bind_param_push($this->arrBind, $fieldTypeDisplayTheme['displayEndDate'], $nowDate);
                    break;

                //종료
                case 'delivery':
                    $this->arrWhere[] = '? > dt.displayEndDate';
                    $this->db->bind_param_push($this->arrBind, $fieldTypeDisplayTheme['displayEndDate'], $nowDate);
                    break;
            }
        }

        if (empty($this->arrBind)) {
            $this->arrBind = null;
        }
    }

    /*
     * 기획전 수정페이지에서 노출되는 그룹리스트 로드
     *
     * @param integer $themeSno
     * @return array $returnData
     */
    public function getDataEventGroupList($themeSno)
    {
        $strWhere = 'groupThemeSno = ?';
        $this->db->bind_param_push($arrBind, 'i', $themeSno);
        $strSQL = 'SELECT * FROM ' . DB_DISPLAY_EVENT_GROUP_THEME . ' WHERE ' . $strWhere . ' ORDER BY groupThemeSort ASC';
        $returnData = $this->db->query_fetch($strSQL, $arrBind);

        if(count($returnData) > 0){
            foreach($returnData as $key => $valueArray){
                $returnData[$key]['groupGoodsCount'] = 0;
                if(trim($valueArray['groupGoodsNo']) !== ''){
                    $returnData[$key]['groupGoodsCount'] = count(explode(STR_DIVISION, $valueArray['groupGoodsNo']));
                }
            }
        }

        return $returnData;
    }

    /*
     * 기획전 수정페이지에서 노출되는 그룹리스트
     *
     * @param integer $eventNo
     * @return array
     */
    public function saveEventNormalData($eventNo)
    {

        $strWhere = 'sno = ?';
        $this->db->bind_param_push($arrBind, 'i', $eventNo);
        $strSQL = 'SELECT themeNm, goodsNo FROM ' . DB_DISPLAY_THEME . ' WHERE ' . $strWhere;
        $eventData = $this->db->query_fetch($strSQL, $arrBind)[0];
        $eventData['goodsNo'] = str_replace(INT_DIVISION, STR_DIVISION, $eventData['goodsNo']);
        $goodsNoArray = @explode(STR_DIVISION, $eventData['goodsNo']);

        $arrData = array(
            'groupManagerNo' => Session::get('manager.sno'),
            'groupGoodsNo' => $eventData['goodsNo'],
            'groupName' =>$eventData['themeNm'],
        );

        //DB 등록
        $arrBind = $this->db->get_binding(DBTableField::tableDisplayEventGroupThemeTmp(), $arrData, 'insert');
        $this->db->set_insert_db(DB_DISPLAY_EVENT_GROUP_THEME_TMP, $arrBind['param'], $arrBind['bind'], 'y');
        $eventGroupTmpNo = $this->db->insert_id();

        return array(
            'eventGroupTmpNo' => $eventGroupTmpNo,
            'groupName' => $eventData['themeNm'],
            'groupGoodsCnt' => count($goodsNoArray),
        );
    }

    /*
    * 그룹 불러오기
    *
    * @param array $postValue
    * @return array $adjustEventGroupData
    */
    public function loadEventGroup($postValue)
    {
        $eventNoArray = array_column($postValue['eventNo'], 'value');
        if(count($eventNoArray) > 0){
            $adjustEventGroupData = array();
            foreach($eventNoArray as $key => $value){
                list($eventNo, $eventGroupNo) = explode("||", $value);
                if(trim($eventGroupNo) !== ''){
                    //그룹일시
                    $eventInsertId = $this->copyEventGroupThemeAll(array('eventGroupNo' => $eventGroupNo));
                    $groupData = $this->getSingleData('temp', $eventInsertId);
                    $adjustEventGroupData[] = array(
                        'eventGroupTmpNo' => $groupData['sno'],
                        'groupName' => $groupData['groupName'],
                        'groupGoodsCnt' => (int)$groupData['groupGoodsCount'],
                    );
                }
                else {
                    //기획전 일반형 일시
                    $adjustEventGroupData[] = $this->saveEventNormalData($eventNo);
                }
            }
        }

        return $adjustEventGroupData;
    }

    /*
    * 스케쥴러를 이용한 임시 더미데이터 삭제
    *
    * @param void
    * @return void
    */
    public function deleteGarbageTempData()
    {
        $beforeMdate = strtotime("-1 hour");
        $beforeDate = date("Y-m-d H:i:s", $beforeMdate);
        foreach(glob(UserFilePath::data('event_group_tmp', '*')) as $imageName)
        {
            if(filemtime($imageName) < $beforeMdate){
                if(preg_match('/groupNameImage/', $imageName)){
                    Storage::disk(Storage::PATH_CODE_EVENT_GROUP_TMP)->delete(basename($imageName));
                }
            }
        }

        $arrBind = [];
        $this->db->bind_param_push($arrBind, 's', $beforeDate);
        $this->db->set_delete_db(DB_DISPLAY_EVENT_GROUP_THEME_TMP, "regDt < ? ", $arrBind);
    }

    /*
    * 실제 그룹 데이터 삭제
    *
    * @param integer $eventGroupNo
    * @param array $originalData
    * @return void
    */
    public function deleteOriginalEventData($eventGroupNo, $originalData = array())
    {
        //파일삭제
        if(count($originalData) < 1){
            $originalData = $this->getSingleData('real', $eventGroupNo);
        }

        if(trim($originalData['groupNameImagePc']) !== ''){
            Storage::disk(Storage::PATH_CODE_EVENT_GROUP)->delete($originalData['groupNameImagePc']);
        }
        if(trim($originalData['groupNameImageMobile']) !== ''){
            Storage::disk(Storage::PATH_CODE_EVENT_GROUP)->delete($originalData['groupNameImageMobile']);
        }

        //DB삭제
        $arrBind = [];
        $this->db->bind_param_push($arrBind, 's', $eventGroupNo);
        $this->db->set_delete_db(DB_DISPLAY_EVENT_GROUP_THEME, "sno = ? ", $arrBind);
    }

    /*
    * 실제 데이터 array 로드
    *
    * @param integer $themeSno
    * @return $data
    */
    public function getSimpleData($themeSno)
    {
        $arrBind = [];
        $this->db->bind_param_push($arrBind, 'i', $themeSno);
        $strSQL = 'SELECT * FROM ' . DB_DISPLAY_EVENT_GROUP_THEME . ' WHERE groupThemeSno = ? ORDER BY groupThemeSort ASC';
        $data = $this->db->query_fetch($strSQL, $arrBind);

        return $data;
    }

    /*
   * 기획전 데이터의 일부를 그룹데이터로 치환하여 로드
   *
   * @param integer $groupSno
   * @param array $getData
   * @return $getData
   */
    public function replaceEventData($groupSno, $getData, $userAccessRoot='')
    {
        $groupData = $this->getSingleData('real', $groupSno);

        $getData['eventThemeName'] = $getData['themeNm'];
        $getData['eventThemePcContents'] = $getData['pcContents'];
        $getData['eventThemeMobileContents'] = $getData['mobileContents'];

        $getData['themeNm'] = $groupData['groupName'];
        $getData['themeCd'] = $groupData['groupThemeCd'];
        $getData['moreTopFl'] = $groupData['groupMoreTopFl'];
        $getData['moreBottomFl'] = $groupData['groupMoreBottomFl'];
        $getData['goodsNo'] = $groupData['groupGoodsNo'];
        $getData['fixGoodsNo'] = $groupData['groupFixGoodsNo'];
        $getData['mobileThemeCd'] = $groupData['groupMobileThemeCd'];
        $getData['groupSno'] = $groupData['sno'];

        //그룹형 기획전 진열방법 설정
        $getData['sort'] = $groupData['groupSort'];
        $getData['pcContents'] = $getData['mobileContents'] = '';

        if($userAccessRoot === 'pc'){
            if(trim($groupData['groupNameImagePc']) !== ''){
                $getData['themeNm'] = "<img src='/data/event_group/".$groupData['groupNameImagePc']."' border='0' />";
            }
        }
        else if($userAccessRoot === 'mobile'){
            if(trim($groupData['groupNameImageMobile']) !== ''){
                $getData['themeNm'] = "<img src='/data/event_group/".$groupData['groupNameImageMobile']."' border='0' />";
            }
        }
        else {}

        return $getData;
    }

    /*
   * 그룹형이 노출되는 순서
   *
   * @param integer $groupSno
   * @param integer $sortIndex
   * @return void
   */
    public function updateGroupThemeSort($groupSno, $sortIndex)
    {
        $arrParam = $arrBind = [];
        $arrParam[] = 'groupThemeSort=?';
        $this->db->bind_param_push($arrBind['bind'], 'i', $sortIndex);
        $this->db->bind_param_push($arrBind['bind'], 'i', $groupSno);
        $this->db->set_update_db(DB_DISPLAY_EVENT_GROUP_THEME, $arrParam, 'sno = ?', $arrBind['bind']);
    }
}
