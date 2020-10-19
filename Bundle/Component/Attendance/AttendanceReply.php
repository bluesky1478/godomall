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

namespace Bundle\Component\Attendance;

use Component\AbstractComponent;
use Component\Database\DBTableField;
use Component\Validator\Validator;
use Framework\Object\SimpleArrayStorage;
use Framework\Object\SimpleStorage;

/**
 * Class AttendanceReply
 * @package Bundle\Component\Attendance
 * @author  yjwee
 */
class AttendanceReply extends \Component\AbstractComponent
{
    /** @var  SimpleStorage */
    private $replyStorage;

    /** @var  SimpleArrayStorage */
    private $replyArrayStorage;

    private $iSearchCount = 0;

    /**
     * @inheritDoc
     */
    public function __construct()
    {
        parent::__construct();
        $this->tableFunctionName = 'tableAttendanceReply';
        $this->tableName = DB_ATTENDANCE_REPLY;
    }

    /**
     * @param SimpleStorage $data
     */
    public function setReplyStorage($data)
    {
        $this->replyStorage = $data;
    }

    /**
     * getDataArray
     *
     * @param      $checkSno
     *
     * @param null $period 조회할 이벤트 기간 Y-m 형식으로 전달해야한다.
     *
     * @return SimpleArrayStorage
     */
    public function getDataArray($checkSno, $period = null)
    {
        if (is_null($period)) {
            $period = gd_date_format('Y-m', 'today');
        }
        $arrBind = $arrWhere = [];
        $arrData = [
            'checkSno' => $checkSno,
            'regDt'    => $period,
        ];
        $this->db->strField = '*, DATE_FORMAT(regDt, \'%e\') AS day';
        $this->db->bindParameterByLike('regDt', $arrData, $arrBind, $arrWhere, $this->tableFunctionName);
        $this->db->bindParameter('checkSno', $arrData, $arrBind, $arrWhere, $this->tableFunctionName);
        $this->db->strWhere = implode(' AND ', $arrWhere);
        $query = $this->db->query_complete();

        $data = $this->db->query_fetch('SELECT ' . array_shift($query) . ' FROM ' . $this->tableName . implode(' ', $query), $arrBind);
        $this->replyArrayStorage = new SimpleArrayStorage();
        $this->replyArrayStorage->setData($data);

        return $this->replyArrayStorage;
    }

    /**
     * validateInsert
     *
     * @throws \Exception
     */
    public function validateInsert()
    {
        if (!isset($this->replyStorage)) {
            throw new \Exception(__('검증에 필요한 정보가 없습니다.'));
        }

        $data = $this->replyStorage;

        if (!Validator::required($this->replyStorage->get('memNo'))) {
            throw new \Exception(__('회원 번호가 없습니다.'));
        }

        if (!Validator::required($this->replyStorage->get('checkSno'))) {
            throw new \Exception(__('출석체크 번호가 없습니다.'));
        }

        $v = $this->v;
        $v->init();
        // __('출석체크 번호가 없습니다.')
        // __('회원 번호가 없습니다.')
        // __('댓글')
        // __('댓글 최대 길이')
        $v->add('checkSno', 'number', true, '{출석체크 번호가 없습니다.}');
        $v->add('memNo', 'number', true, '{회원 번호가 없습니다.}');
        $v->add('reply', '', true, '{댓글}');
        $v->add('reply', 'maxlen', true, '{댓글 최대 길이}', 50);

        if ($v->act($data->all()) === false) {
            \Logger::error(__METHOD__, $v->errors);
            throw new \Exception(implode("\n", $v->errors));
        }
    }

    /**
     * insert
     *
     * @param null $arrData
     *
     * @return int|string
     */
    public function insert($arrData = null)
    {
        if (is_null($arrData)) {
            $arrData = $this->replyStorage->all();
        }
        $arrBind = $this->db->get_binding(DBTableField::tableAttendanceReply(), $arrData, 'insert', array_keys($arrData));
        $this->db->set_insert_db($this->tableName, $arrBind['param'], $arrBind['bind'], 'y');
        unset($arrBind);

        return $this->db->insert_id();
    }

    /**
     * lists
     *
     * @param array  $requestParams
     * @param null   $offset
     * @param null   $limit
     *
     * @param string $column
     *
     * @return array|object
     */
    public function lists(array $requestParams, $offset = null, $limit = null, $column = '*')
    {
        $arrBind = $arrWhere = [];

        $this->db->bindParameter('checkSno', $requestParams, $arrBind, $arrWhere, $this->tableFunctionName, 'aco');
        $this->db->bindParameter('memNo', $requestParams, $arrBind, $arrWhere, $this->tableFunctionName, 'aco');

        $today = gd_date_format('Y-m-d', 'now');
        $arrWhere[] = 'a.startDt<=\'' . $today . '\' AND a.endDt>=\'' . $today . '\' AND a.methodFl=\'reply\'';

        $this->db->strField = 'aco.' . $column . ', m.memNm';
        $this->db->strWhere = implode(' AND ', $arrWhere);
        if (is_null($offset) === false && is_null($limit) === false) {
            $this->db->strLimit = ($offset - 1) * $limit . ', ' . $limit;
        }

        $this->db->strJoin = ' LEFT JOIN ' . DB_ATTENDANCE_CHECK . ' AS ach ON aco.checkSno = ach.sno';
        $this->db->strJoin .= ' LEFT JOIN ' . DB_ATTENDANCE . ' AS a ON ach.attendanceSno = a.sno';
        $this->db->strJoin .= ' LEFT JOIN ' . DB_MEMBER . ' AS m ON ach.memNo = m.memNo';
        $arrQuery = $this->db->query_complete(true, true);
        $query = 'SELECT ' . array_shift($arrQuery) . ' FROM ' . $this->tableName . ' AS aco ' . implode(' ', $arrQuery);
        $resultSet = $this->db->query_fetch($query, $arrBind);
        $this->iSearchCount = $this->db->query_count($arrQuery, $this->tableName . ' AS aco ', $arrBind);

        return $resultSet;
    }

    /**
     * getCountByLists
     *
     * @return mixed
     */
    public function getCountByLists()
    {
        $arrQuery = $this->db->getQueryCompleteBackup();
        array_shift($arrQuery);

        return $this->getCount($this->tableName . ' AS aco ', 'aco.sno', implode(' ', $arrQuery));
    }

    /**
     * reply
     *
     * @param $arrData
     *
     * @throws \Exception
     */
    public function reply($arrData)
    {
        $this->setReplyStorage(new SimpleStorage($arrData));
        $this->validateInsert();
        $this->insert();
    }

    public function getSearchCount()
    {
        return gd_isset($this->iSearchCount, 0);
    }
}
