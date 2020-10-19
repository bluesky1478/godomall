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
 * @link      http://www.godo.co.kr
 */

namespace Bundle\Component\Sms;


use Component\Database\DBTableField;

/**
 * Class Sms080DAO
 * @package Bundle\Component\Sms
 * @author  yjwee <yeongjong.wee@godo.co.kr>
 */
class Sms080DAO
{
    /**
     * 수신거부 번호 입력
     *
     * @param array $arrParam
     *
     * @return int|string
     */
    public function insert(array $arrParam)
    {
        $db = \App::getInstance('DB');
        $arrBind = $db->get_binding(
            DBTableField::tableSms080(), $arrParam, 'insert', [
                'rejectCellPhone',
                'rejectDt',
            ]
        );
        $db->set_insert_db(DB_SMS_080, $arrBind['param'], $arrBind['bind'], 'y');

        return $db->insert_id();
    }

    /**
     * 목록 조회
     *
     * @param array $params
     *
     * @return array
     */
    public function selectList(array $params): array
    {
        $params['sort'] = 'sno DESC';
        list($db, $arrBind) = $this->getListArrBind($params);
        $arrQuery = $db->query_complete();
        $strSQL = 'SELECT ' . array_shift($arrQuery) . ' FROM ' . DB_SMS_080 . implode(' ', $arrQuery);
        $resultSet = $db->query_fetch($strSQL, $arrBind);

        return $resultSet;
    }

    /**
     * 카운트
     *
     * @param array $arrParam
     *
     * @return int
     */
    public function countList(array $arrParam): int
    {
        $arrParam['column'] = 'COUNT(*) AS cnt';
        list($db, $arrBind) = $this->getListArrBind($arrParam);
        $arrQuery = $db->query_complete();
        $strSQL = 'SELECT ' . array_shift($arrQuery) . ' FROM ' . DB_SMS_080 . implode(' ', $arrQuery);
        $resultSet = $db->query_fetch($strSQL, $arrBind, false);

        return $resultSet['cnt'];
    }

    /**
     * 검색 정보 바인드
     *
     * @param array $arrParam
     *
     * @return array
     */
    protected function getListArrBind(array $arrParam): array
    {
        $db = \App::getInstance('DB');
        $arrBind = [];
        $db->strField = ($arrParam['column'] == '') ? '*' : $arrParam['column'];
        if ($arrParam['offset'] != '' && $arrParam['limit'] != '') {
            $db->strLimit = ($arrParam['offset'] - 1) * $arrParam['limit'] . ', ' . $arrParam['limit'];
        }
        if ($arrParam['sort'] != '') {
            $db->strOrder = $arrParam['sort'];
        }
        if ($arrParam['keyword'] != '') {
            $db->strWhere = 'rejectCellPhone LIKE CONCAT(\'%\', ?, \'%\')';
            $db->bind_param_push($arrBind, 's', $arrParam['keyword']);
        }

        return [
            $db,
            $arrBind,
        ];
    }
}
