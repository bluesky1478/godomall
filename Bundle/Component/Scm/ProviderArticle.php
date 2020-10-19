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

namespace Bundle\Component\Scm;

use Component\Database\DBTableField;
use Component\Member\Manager;
use Component\Page\Page;
use Component\Storage\Storage;
use Framework\Object\DotNotationSupportStorage;
use Framework\StaticProxy\Proxy\App;
use Framework\StaticProxy\Proxy\UserFilePath;
use Framework\Utility\StringUtils;
use Session;
use Request;

/**
 * Class ProviderArticle
 * @package Bundle\Component\Scm
 * @author Lee Namju <lnjts@godo.co.kr>
 */
class ProviderArticle extends DotNotationSupportStorage
{
    protected $db;
    protected $data;
    protected $manager;
    protected $id;
    protected $fieldType;
    protected $storage;

    const ID = 'sno';
    const LIST_COUNT = 10;
    const NOTICE_LIST_COUNT = 5;
    const MAX_UPLOAD_MB_SIZE = 5;
    const MAX_UPLOAD_COUNT = 5;

    public function __construct()
    {
        if (!is_object($this->db)) {
            $this->db = App::load('DB');
        }

        $this->storage = Storage::disk(Storage::PATH_CODE_SCM);
        $this->fieldType['sb'] = DBTableField::getFieldTypes('tableScmBoard');
        $this->fieldType['sg'] = DBTableField::getFieldTypes('tableScmBoardGroup');
        $this->manager = ['memNo' => Session::get('manager.sno'), 'memNm' => Session::get('manager.managerNm'), 'memId' => Session::get('manager.managerId'), 'scmNo' => Session::get('manager.scmNo'), 'companyNm' => Session::get('manager.companyNm')];;
    }

    public function getMaxUploadSize()
    {
        $maxServerUploadSize = str_replace('M', '', ini_get('upload_max_filesize'));
        if ($maxServerUploadSize > self::MAX_UPLOAD_MB_SIZE) {
            return self::MAX_UPLOAD_MB_SIZE;
        }
        return $maxServerUploadSize;
    }


    public function getId()
    {
        return $this->get(self::ID);
    }

    protected function hasId()
    {
        return $this->has(self::ID);
    }

    protected function createGroupNo()
    {
        $query = "SELECT MIN(groupNo) FROM " . DB_SCM_BOARD;
        list($groupNo) = $this->db->fetch($query, 'row');
        if ($groupNo == null) {
            return -1;
        }

        return $groupNo - 1;
    }

    protected function createGroupThread($groupNo, $parentGroupThread = '')
    {
        $beginReplyChar = 'Z';
        $endReplyChar = 'A';
        $replyNumber = -1;
        $replyLen = strlen($parentGroupThread) + 1;

        $sql = " select MIN(SUBSTRING(groupThread, {$replyLen}, 1)) as reply from " . DB_SCM_BOARD . " where groupNo = " . $groupNo . "  and SUBSTRING(groupThread, {$replyLen}, 1) <> '' ";
        if ($parentGroupThread) {
            $sql .= " and groupThread like '{$parentGroupThread}%' ";
        }
        $row = $this->db->fetch($sql, 'row');
        if (!$row['reply']) {
            $replyChar = $beginReplyChar;
        } else if ($row['reply'] == $endReplyChar) { // A~Z은 26 입니다.
            throw new \Exception(__('더 이상 답변하실 수 없습니다.\\n답변은 26개 까지만 가능합니다.'));
        } else {
            $replyChar = chr(ord($row['reply']) + $replyNumber);
        }

        $reply = $parentGroupThread . $replyChar;
        return $reply;
    }

    public function getSearchInfo($req)
    {
        $checked['scmFl'][$req['scmFl']] = 'checked';
        $search['scmFl']  = $req['scmFl'];
        $search['checked'] = $checked;
        $search['scmNo'] = $req['scmNo'];
        $search['scmNoNm'] = $req['scmNoNm'];
        $search['category'] = $req['category'];
        $search['searchDate'] = $req['searchDate'];
        $search['keyword'] = $req['keyword'];
        $search['searchPeriod'] = $req['searchPeriod'];
        $search['searchSelectField'] = ['subject' => __('제목'), 'contents' => __('내용'), 'writer' => __('작성자')];
        $search['searchField'] = $req['selectField'];
        return $search;
    }

    public function getCode()
    {
        return gd_code('03002');
    }

    /**
     * add
     *
     * @param bool $isReply
     * @return mixed
     * @throws \Exception
     */
    public function add($isReply = false)
    {
        if (!$this->all()) {
            throw new \Exception('empty data');
        }

        if ($isReply) {
            if ($this->get('isNotice')) {
                throw new \Exception(__('답변글은 공지가 될 수 없습니다.'));
            }

            if ($this->hasId() === false) {
                throw new \Exception(__('부모글이 없음.'));
            }

            $reply = $this->selectOne($this->getId());
            $groupNo = $reply['groupNo'];
            $groupThread = $this->createGroupThread($reply['groupNo'], $reply['groupThread']);
            if ($this->isProvider()) {
                $this->set('scmFl', 'n');  //공급사가 답변달경우 본사만 보게
            } else {
                $this->set('scmFl', 'y');  //본사가 답변달경우 공급사대상으로
                $scmNos = [$reply['scmNo']];
            }
            $this->set('parentSno', $this->getId());
            $this->set('category', $reply['category']); //부모 카테고리따라감.
        } else {
            $groupNo = $this->createGroupNo();
            $groupThread = '';
            $scmNos = $this->get('scmNo');
            if ($this->isProvider()) {
                $this->set('scmFl', 'n');   //본사만보게
            }
        }

        $this->set('managerNo', $this->manager['memNo']);
        $this->set('scmNo', $this->manager['scmNo']);
        $this->set('groupNo', $groupNo);
        $this->set('groupThread', $groupThread);
        list($uploadFiles, $saveFiles) = $this->setUploadFiles();
        $this->set('uploadFiles', $uploadFiles);
        $this->set('saveFiles', $saveFiles);
        $arrBind = $this->db->get_binding(DBTableField::tableScmBoard(), $this->toArray(), 'insert');
        $this->db->set_insert_db(DB_SCM_BOARD, $arrBind['param'], $arrBind['bind'], 'y');
        $sno = $this->db->insert_id();
        unset($arrBind);

        $this->addBoardGrooup($scmNos, $sno);
        return $sno;
    }


    protected function canModifyAndRemove($managerNo)
    {
        if ($this->manager['memNo'] != $managerNo) {
            return false;
        }

        return true;
    }

    public function modify()
    {
        if (!$this->hasId()) {
            throw new Exception('empty sno');
        }

        $oriData = $this->selectOne($this->getId());
        $scmNos = $this->get('scmNo');
        $this->del('scmNo');
        if ($this->canModifyAndRemove($oriData['managerNo']) === false) {
            throw new Exception(__('수정권한이 없습니다.'));
        }

        list($uploadFiles, $saveFiles) = $this->setUploadFiles($oriData);
        $this->set('uploadFiles', $uploadFiles);
        $this->set('saveFiles', $saveFiles);
        $this->set('isNotice',$this->get('isNotice') ?? 'n');
        $arrBind = $this->db->get_binding(DBTableField::tableScmBoard(), $this->toArray(), 'update', array_keys($this->toArray()));
        $this->db->bind_param_push($arrBind['bind'], 'i', $this->getId());
        $this->db->set_update_db(DB_SCM_BOARD, $arrBind['param'], 'sno = ?', $arrBind['bind'], false);

        $this->addBoardGrooup($scmNos, $this->getId());
        return $this->getId();
    }

    protected function isProvider()
    {
        return Manager::isProvider();
    }


    protected function select($mode = 'select', $search = null, array $appendWhere = null, $offset = 0, $listCount = self::LIST_COUNT)
    {
        $where = null;
        $arrBind = [];

        if ($this->isProvider()) { //공급사인 경우
            $where[] = " (b.scmNo =  " . $this->manager['scmNo'] . " OR b.scmFl = 'all' OR scmBoardSno > 0) ";
        }

        if ($search) {

            //최근 ~일 동안
            if ($search['someTimePast'] != null || $search['someTimePast'] === 0) {
                $where[] = "  b.regDt > '" . date('Y-m-d', strtotime("-" . $search['someTimePast'] . " days")) . "'";
            }

            if ($search['scmFl']) {
                switch ($search['scmFl']) {
                    case 'y' :
                        if ($search['scmNo']) {
                            foreach ($search['scmNo'] as $scmNo) {
                                $arrScmNo[] = $scmNo;
                            }
                            $where[] = " b.scmNo in (" . implode(',', $arrScmNo) . ")";
                        }
                        break;
                    case 'n' :
                        $where[] = " b.scmNo = ?";
                        $this->db->bind_param_push($arrBind, $this->fieldType['sb']['scmNo'], DEFAULT_CODE_SCMNO);
                        break;
                }
            }

            if ($search['category']) {
                $where[] = " b.category = ? ";
                $this->db->bind_param_push($arrBind, $this->fieldType['sb']['category'], $search['category']);
            }


            if ($searchDate = $search['searchDate']) {
                if ($searchDate[0]) {
                    $where[] = " b.regDt >= ? ";
                    $this->db->bind_param_push($arrBind, 's', $searchDate[0].' 00:00');
                }

                if ($searchDate[1]) {
                    $where[] = " b.regDt <= ? ";
                    $this->db->bind_param_push($arrBind, 's', $searchDate[1] . ' 23:59');
                }
            }


            if ($keyword = $search['keyword']) {
                switch ($search['searchField']) {
                    case 'subject' :
                        $where[] = "subject LIKE concat('%',?,'%')";
                        $this->db->bind_param_push($arrBind, $this->fieldType['sb']['subject'], $keyword);
                        break;
                    case 'contents' :
                        $where[] = "contents LIKE concat('%',?,'%')";
                        $this->db->bind_param_push($arrBind, $this->fieldType['sb']['contents'], $keyword);
                        break;
                    case 'writer' :
                        $where[] = "(m.managerNm LIKE concat('%',?,'%') OR m.managerId LIKE concat('%',?,'%'))";
                        $this->db->bind_param_push($arrBind, 's', $keyword);
                        $this->db->bind_param_push($arrBind, 's', $keyword);
                        break;
                    default :    //통합검색
                        $_where[] = "subject LIKE concat('%',?,'%')";
                        $this->db->bind_param_push($arrBind, $this->fieldType['sb']['subject'], $keyword);
                        $_where[] = "contents LIKE concat('%',?,'%')";
                        $this->db->bind_param_push($arrBind, $this->fieldType['sb']['contents'], $keyword);
                        $_where[] = "(m.managerNm LIKE concat('%',?,'%') OR m.managerId LIKE concat('%',?,'%'))";
                        $this->db->bind_param_push($arrBind, 's', $keyword);
                        $this->db->bind_param_push($arrBind, 's', $keyword);

                        $where[] = '(' . implode(' OR ', $_where) . ')';
                }
            }

            if ($search['groupNo']) {
                $where[] = " b.groupNo = ? ";
                $this->db->bind_param_push($arrBind, 'i', $search['groupNo']);
            }
        }

        if ($mode == 'count') {
            $queryFields = 'count(b.sno) as cnt';
        } else {
            $queryFields = 'b.* , m.managerId,m.managerNm,m.managerNickNm,sm.companyNm,sm.scmType,m.isDelete';
            if ($this->isProvider()) {
                $queryFields .= ',scmBoardSno';
            }
        }

        $query = "SELECT " . $queryFields . " FROM " . DB_SCM_BOARD . " as b LEFT OUTER JOIN " . DB_MANAGER . " as m ON b.managerNo = m.sno  ";
        if ($this->isProvider()) {
            $query .= " LEFT OUTER JOIN (SELECT scmBoardSno,scmNo FROM " . DB_SCM_BOARD_GROUP . " WHERE scmNo = " . $this->manager['scmNo'] . " ) as g ON g.scmBoardSno = b.sno";
        }
        $query .= " LEFT OUTER JOIN " . DB_SCM_MANAGE . " as sm ON sm.scmNo = b.scmNo";
        $query .= " WHERE 1 ";


        if ($where) {
            $query .= ' AND ' . implode(' AND ', $where);
        }

        if ($appendWhere) {
            $query .= ' AND ' . implode(' AND ', $appendWhere);
        }
        if ($mode != 'count') {
            $query .= " ORDER BY b.groupNo , b.groupThread ";
            $query .= sprintf(" LIMIT %s , %s", $offset, $listCount);
            $result = $this->db->query_fetch($query, $arrBind);
            Manager::displayListData($result);
            return $result;
        } else {
            $result = $this->db->query_fetch($query, $arrBind, false);
            return $result['cnt'];
        }
    }

    public function getCount($search = null, $appendWhere = null)
    {
        return $this->select('count', $search, $appendWhere);
    }

    public function getList($search)
    {
        $offset = (gd_isset($search['page'], 1) - 1) * self::LIST_COUNT;
        $data = [];

        $appendWhere[] = "b.isDelete = 'n'";
        $noticeRows = $this->select('select', null, ["b.isDelete = 'n'", "b.isNotice = 'y'"], 0, self::NOTICE_LIST_COUNT);
        $data['noticeList'] = $noticeRows;
        $this->applyDisplyList($data['noticeList']);
        $rows = $this->select('select', $search, $appendWhere, $offset);
        $data['list'] = $rows;
        $data['searchCnt'] = $this->getCount($search, $appendWhere);
        $data['totalCnt'] = $this->getCount(null, $appendWhere);
        $data['totalPage'] = $this->getPagination($search['page'], $data['searchCnt'], $data['totalCnt'], self::LIST_COUNT)->page['total'];
        $data['pagination'] = $this->getPagination($search['page'], $data['searchCnt'], $data['totalCnt'], self::LIST_COUNT)->getPage();
        $listNo = $data['searchCnt'] - $offset;
        if ($data['list']) {
            $this->applyDisplyList($data['list']);
            foreach ($data['list'] as &$articleData) {
                $articleData['listNo'] = $listNo;
                $listNo--;
            }
        }

        return $data;
    }

    protected function applyDisplyList(&$listData)
    {
        foreach ($listData as &$articleData) {
            $this->applyDisplyData($articleData);
        }
    }

    protected function applyDisplyData(&$data)
    {
        $data['auth'] = $this->canModifyAndRemove($data['managerNo']) ? 'y' : 'n';
        $iconReply = '';
        $iconNotice = '';
        $iconFile = '';
        $data['categoryText'] = '-';
        if ($data['category']) {
            $data['categoryText'] = gd_code_item($data['category']);
        }
        if ($data['groupThread']) {
            for ($i = 0; $i < strlen($data['groupThread']); $i++) {
                $iconReply .= '&nbsp;';
            }
            $iconReply .= '<img src="' . PATH_ADMIN_GD_SHARE.'img/ico_bd_reply.gif'. '" />';
        }
        if ($data['isNotice'] == 'y') {
            $iconNotice = '<img src="' . PATH_ADMIN_GD_SHARE.'img/ico_bd_notice.gif" />';
        }
        if ($data['saveFiles']) {
            $iconFile = '<img src="' . PATH_ADMIN_GD_SHARE.'img/ico_bd_file.gif" />';
        }

        $data['iconReply'] = $iconReply;
        $data['iconNotice'] = $iconNotice;
        $data['iconFile'] = $iconFile;
    }

    public function selectOne($sno)
    {
        if (!$sno) {
            throw new \Exception('empty sno');
        }
        $arrBind = [];
        $query = "SELECT b.* , m.managerId,m.managerNm,m.managerNickNm,sm.companyNm,sm.scmType FROM " . DB_SCM_BOARD . " as b LEFT OUTER JOIN " . DB_MANAGER . " as m ON b.managerNo = m.sno  ";
        $query .= " LEFT OUTER JOIN " . DB_SCM_MANAGE . " as sm ON sm.scmNo = b.scmNo";
        $query .= " WHERE b.sno = ?";
        $this->db->bind_param_push($arrBind, 'i', $sno);
        $row = $this->db->query_fetch($query, $arrBind)[0];
        unset($arrBind);
        if ($row['isDelete'] == 'y') {
            throw new \Exception(__('삭제된 글 입니다.'));
        }
        $arrBind = [];
        $query = "SELECT g.* , sm.companyNm FROM " . DB_SCM_BOARD_GROUP . " as g LEFT OUTER JOIN " . DB_SCM_MANAGE . " as sm ON g.scmNo = sm.scmNo WHERE scmBoardSno = ? ";
        $this->db->bind_param_push($arrBind, 'i', $sno);
        $scmBoardGroupData = $this->db->query_fetch($query, $arrBind);
        if ($scmBoardGroupData) {
            $row['scmBoardGroup'] = $scmBoardGroupData;
        }
        return $row;

    }

    public function getChildList($sno)
    {
        $arrBind = [];
        $query = "SELECT * FROM " . DB_SCM_BOARD . " WHERE parentSno = ? AND isDelete = 'n'";
        $this->db->bind_param_push($arrBind, 'i', $sno);
        $result = $this->db->query_fetch($query, $arrBind);
        return $result;
    }

    public function getRelationList($row)
    {
        if($this->isProvider()) {
            $addQuery=" AND (b.scmNo = ".$this->manager['scmNo']." OR b.scmFl = 'all' OR g.scmBoardSno > 0 )" ;
        }
        $query = "SELECT MIN(sno) as sno FROM " . DB_SCM_BOARD . " as b LEFT OUTER JOIN (SELECT scmBoardSno,scmNo FROM es_scmBoardGroup WHERE scmNo = 2 ) as g ON g.scmBoardSno = b.sno WHERE b.groupNo < " . $row['groupNo'] . " AND b.parentSno = 0 AND b.isDelete = 'n'".$addQuery;

        $result = $this->db->query_fetch($query, null, false);
        $minSno = $result['sno'];
        $nextData = null;
        if ($minSno) {
            $nextData = $this->selectOne($minSno);
            $this->applyDisplyData($nextData);
        }
        $query = "SELECT MAX(sno) as sno FROM " . DB_SCM_BOARD . "  as b LEFT OUTER JOIN (SELECT scmBoardSno,scmNo FROM es_scmBoardGroup WHERE scmNo = 2 ) as g ON g.scmBoardSno = b.sno WHERE b.groupNo > " . $row['groupNo'] . " AND b.parentSno = 0 AND b.isDelete = 'n'".$addQuery;
        $result = $this->db->query_fetch($query, null, false);
        $maxSno = $result['sno'];
        if ($maxSno) {
            $prevData = $this->selectOne($maxSno);
            $this->applyDisplyData($prevData);
        }

        $groupData = $this->select('select', ['groupNo' => $row['groupNo']]);
        $this->applyDisplyList($groupData);

        $data['nextData'] = $nextData;
        $data['groupData'] = $groupData;
        $data['prevData'] = $prevData;
        return $data;
    }

    public function remove($sno)
    {
        $data = $this->selectOne($sno);
        if ($this->canModifyAndRemove($data['managerNo']) === false) {
            throw new Exception(__('삭제권한이 없습니다.'));
        }

        if ($this->getChildList($sno)) {
            $arrBind = [];
//            $this->db->bind_param_push($arrBind, 'i', $sno);
//            $this->db->set_delete_db(DB_SCM_BOARD, 'parentSno = ?', $arrBind);
            $this->db->bind_param_push($arrBind, 'i', $sno);
            $this->db->set_update_db(DB_SCM_BOARD, " isDelete='y' ", 'parentSno=?', $arrBind);
        }
        $arrBind = [];
        $this->db->bind_param_push($arrBind, 'i', $sno);
        return $this->db->set_update_db(DB_SCM_BOARD, " isDelete='y' ", 'sno=?', $arrBind);
    }


    public function getFormData($mode = null, $sno = null)
    {
        $mode = ($mode) ? $mode : 'register';
        switch ($mode) {
            case 'reply' :
                $reply = $this->selectOne($sno);
                if ($reply['isNotice'] == 'y') {
                    throw new \Exception(__('공지사항 게시글에 답변할 수 없습니다.'));
                }
                $data['category'] = $reply['category'];

            case 'register' :
                $data['writer'] = sprintf("%s (%s)", $this->manager['memId'], $this->manager['memNm']);
                break;
            case 'modify' :
                $data = $this->selectOne($sno);
                $data['writer'] = sprintf(" %s / %s (%s)", $data['companyNm'], $data['managerId'], $data['managerNm']);
                $data['checked']['scmFl'][$data['scmFl']] = 'checked';
                $data['checked']['isNotice'][$data['isNotice']] = 'checked';
                $data['upfilesCnt'] = ($data['uploadFiles']) ? count(explode(STR_DIVISION, $data['uploadFiles'])) : 0;
                $data['uploadFileNm'] = explode(STR_DIVISION, $data['uploadFiles']);
                $data['contents'] = gd_htmlspecialchars_stripslashes($data['contents']);
        }
        return $data;
    }

    public function getView($sno)
    {
        $row = $this->selectOne($sno);
        $row['writer'] = $row['managerId'] . '(' . $row['managerNm'] . ')';
        $row['contents'] = $this->setContents($row['contents']);
        $_arrUploadFiles = explode(STR_DIVISION, $row['uploadFiles']);
        $_arrSaveFiles = explode(STR_DIVISION, $row['saveFiles']);
        for ($i = 0; $i < count($_arrUploadFiles); $i++) {
            $arrSaveFileList[] = '<a href="scm_board_ps.php?mode=download&uploadFileName=' . $_arrUploadFiles[$i] . '&saveFileName=' . $_arrSaveFiles[$i] . '" >' . $_arrUploadFiles[$i] . '</a>';
        }
        $row['uploadFileList'] = implode('&nbsp;,&nbsp;', $arrSaveFileList);

        switch ($row['scmFl']) {
            case 'all' :
                $row['scmTarget'] = __('전체');
                break;
            case 'n' :
                $row['scmTarget'] = __('본사');
                break;
            case 'y' :
                foreach ($row['scmBoardGroup'] as $val) {
                    $companyList[] = $val['companyNm'];
                }
                $row['scmTarget'] = implode(',', $companyList);
                break;
        }
        $row['categoryText'] = '-';
        if ($row['category']) {
            $row['categoryText'] = gd_code_item($row['category']);
        }
        $row['auth'] = $this->canModifyAndRemove($row['managerNo']) ? 'y' : 'n';
        $row['relationList'] = $this->getRelationList($row);
        return $row;
    }

    public function setContents($contents)
    {
        $contents = gd_htmlspecialchars_stripslashes($contents);
        $contents = StringUtils::xssClean($contents);
        return $contents;
    }

    protected function getPagination($nowPage, $searchCount, $totalCount, $listCount)
    {
        $pager = new Page($nowPage, $searchCount, $totalCount, $listCount, 10);
        $pager->setUrl(Request::server()->get('QUERY_STRING'));
        return $pager;
    }

    /**
     * addBoardGrooup
     *
     * @param $scmNos
     * @param $sno
     * @throws \Exception
     */
    protected function addBoardGrooup($scmNos, $sno)
    {
        if (!$this->all()) {
            throw new \Exception('emtpy data');
        }

        if ($this->get('scmFl') == 'y') {
            if ($scmNos) {
                $arrBind = [];
                $this->db->bind_param_push($arrBind, 'i', $sno);
                $this->db->set_delete_db(DB_SCM_BOARD_GROUP, 'scmBoardSno = ?', $arrBind);

                foreach ($scmNos as $val) {
                    $inData['scmBoardSno'] = $sno;
                    $inData['scmNo'] = $val;
                    $arrBindind = $this->db->get_binding(DBTableField::tableScmBoardGroup(), $inData, 'insert');

                    $this->db->set_insert_db(DB_SCM_BOARD_GROUP, $arrBindind['param'], $arrBindind['bind'], 'y');
                }
            }
        }
    }

    /**
     * setUploadFiles
     *
     * @param $oriData
     * @return array
     * @throws \Exception
     */
    protected function setUploadFiles($oriData = null)
    {
        $_uploadFiles = $_saveFiles = null;
        if ($oriData['uploadFiles']) {
            $_uploadFiles = explode(STR_DIVISION, $oriData['uploadFiles']);
            $_saveFiles = explode(STR_DIVISION, $oriData['saveFiles']);
        }
        if ($this->get('delFile')) {
            foreach ($this->get('delFile') as $key => $val) {
                $delFileName = 'upload/' . $_saveFiles[$key];
                $this->storage->delete($delFileName);
                unset($_saveFiles[$key]);
                unset($_uploadFiles[$key]);
            }
        }

        if (count($this->get('uploadFiles.tmp_name')) > 5) {
            throw new \Exception(__('업로드') . ' ' . self::MAX_UPLOAD_COUNT . __('개 초과'));
        }

        for ($i = 0; $i < count($this->get('uploadFiles.tmp_name')); $i++) {
            $tmpName = $this->get('uploadFiles.tmp_name')[$i];
            if (!$tmpName) {
                continue;
            }
            $fileSize = $this->get('uploadFiles.size')[$i];
            if ($fileSize > ($this->getMaxUploadSize() * 1024 * 1024)) {
                throw new \Exception(__('업로드 용량이') . ' ' . $this->getMaxUploadSize() . 'MByte(s) ' . __('를 초과했습니다.'));
            }

            $saveFilename = date('ymdHis') . uniqid() . $i;
            $fileName = $this->get('uploadFiles.name')[$i];
            $_uploadFiles[] = $fileName;
            $_saveFiles[] = $saveFilename;

            $this->storage->upload($tmpName, 'upload/' . $saveFilename);
        }


        if ($_uploadFiles) {
            $uploadFiles = implode(STR_DIVISION, $_uploadFiles);
            $saveFiles = implode(STR_DIVISION, $_saveFiles);
        }
        return [$uploadFiles, $saveFiles];
    }
}
