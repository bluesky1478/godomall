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

namespace Bundle\Component\Order;

use Component\Database\DBTableField;

class EncryptAccount
{
    public $db;
    /**
     * 생성자
     */
    public function __construct()
    {
        if (!is_object($this->db)) {
            $this->db = \App::load('DB');
        }
    }

    public function setEncryptAccount()
    {
        $tableInfo = [
            DB_ORDER_USER_HANDLE => ['tableOrderUserHandle', 'userRefundAccountNumber'],
            DB_ORDER_EXCHANGE_HANDLE => ['tableOrderExchangeHandle', 'ehRefundBankAccountNumber'],
            DB_ORDER_HANDLE => ['tableOrderHandle', 'refundAccountNumber'],
        ];

        foreach ($tableInfo as $k => $v) {
            $arrField = DBTableField::setTableField($v[0], [$v[1]], null);
            $arrWhere = [];
            $arrWhere[] = $v[1] . " <> ''";

            $this->db->strField = 'sno, ' . implode(', ', $arrField);
            $this->db->strWhere = implode(' AND ', gd_isset($arrWhere));

            $query = $this->db->query_complete();
            $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . $k . ' ' . implode(' ', $query);
            $data = $this->db->query_fetch($strSQL);

            foreach ($data as $value) {
                $arrData = [];
                $arrData[$v[1]] = \Encryptor::encrypt($value[$v[1]]);
                $table = $v[0];

                $arrBind = $this->db->get_binding(DBTableField::$table(), $arrData, 'update', array_keys($arrData));
                $this->db->bind_param_push($arrBind['bind'], 'i', $value['sno']);
                $this->db->set_update_db($k, $arrBind['param'], 'sno = ?', $arrBind['bind']);
            }
        }
    }
}
