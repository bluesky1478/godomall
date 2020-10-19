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
 * es_smsExcelLog 테이블
 * @package Bundle\Component\Sms
 * @author  yjwee <yeongjong.wee@godo.co.kr>
 */
class SmsExcelLogDAO
{
    /**
     * 저장
     *
     * @param array $params
     *
     * @return int
     */
    public function insert(array $params)
    {
        $db = \App::getInstance('DB');
        $arrBind = $db->get_binding(DBTableField::tableSmsExcelLog(), $params, 'insert', array_keys($params));
        $db->set_insert_db(DB_SMS_EXCEL_LOG, $arrBind['param'], $arrBind['bind'], 'y');

        return (int) $db->insert_id();
    }

    /**
     * 다수 데이터 저장
     * ex) VALUES(), (), ()
     *
     * @param array $params
     *
     * @return int
     */
    public function inserts(array $params)
    {
        $db = \App::getInstance('DB');
        $tableModel = DBTableField::tableModel('tableSmsExcelLog');
        $db->setMultipleInsertDb(DB_SMS_EXCEL_LOG, array_keys($tableModel), $params);

        return (int) $db->insert_id();
    }

    /**
     * 검증을 통과한 데이터 중 uploadKey 에 해당하는 데이터 조회
     *
     * @param string $uploadKey
     *
     * @return array
     */
    public function selectValidationLogByUploadKey($uploadKey)
    {
        $db = \App::getInstance('DB');
        $db->strField = '*';
        $db->strWhere = 'validateFl=\'y\' AND uploadKey=?';
        $fieldTypes = DBTableField::getFieldTypes(DBTableField::getFuncName(DB_SMS_EXCEL_LOG));
        $db->bind_param_push($arrBind, $fieldTypes['uploadKey'], $uploadKey);
        $query = $db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_SMS_EXCEL_LOG . implode(' ', $query);

        return $db->query_fetch($strSQL, $arrBind);
    }

    /**
     * 이미 등록된 번호인지 확인
     *
     * @param string $uploadKey 업로드키
     * @param string $cellPhone 전화번호
     *
     * @return array
     */
    public function selectValidationLogByUploadKeyWithCellPhone($uploadKey, $cellPhone)
    {
        $db = \App::getInstance('DB');
        $db->strField = '*';
        $db->strWhere = 'validateFl=\'y\' AND uploadKey=? AND cellPhone=?';
        $fieldTypes = DBTableField::getFieldTypes(DBTableField::getFuncName(DB_SMS_EXCEL_LOG));
        $db->bind_param_push($arrBind, $fieldTypes['uploadKey'], $uploadKey);
        $db->bind_param_push($arrBind, $fieldTypes['cellPhone'], $cellPhone);
        $query = $db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_SMS_EXCEL_LOG . implode(' ', $query);

        return $db->query_fetch($strSQL, $arrBind, false);
    }

    /**
     * 업로드키에 등록된 검증통과 수신대상 카운트
     *
     * @param string $uploadKey 업로드키
     *
     * @return int 수신자수
     */
    public function countValidationLogByUploadKey($uploadKey)
    {
        $db = \App::getInstance('DB');
        $db->strField = 'COUNT(*) AS cnt';
        $db->strWhere = 'validateFl=\'y\' AND uploadKey=?';
        $fieldTypes = DBTableField::getFieldTypes(DBTableField::getFuncName(DB_SMS_EXCEL_LOG));
        $db->bind_param_push($arrBind, $fieldTypes['uploadKey'], $uploadKey);
        $query = $db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_SMS_EXCEL_LOG . implode(' ', $query);
        $resultSet = $db->query_fetch($strSQL, $arrBind, false);

        return $resultSet['cnt'];
    }

    /**
     * uploadKey 에 해당하는 데이터 삭제
     *
     * @param string $uploadKey
     *
     */
    public function deleteLogByUploadKey($uploadKey)
    {
        $db = \App::getInstance('DB');
        $db->bind_param_push($arrBind, 's', $uploadKey);
        $db->set_delete_db(DB_SMS_EXCEL_LOG, 'uploadKey = ?', $arrBind);
    }
}
