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
namespace Bundle\Component\Policy;

use Framework\Object\StorageInterface;
use Session;

class WonderLoginPolicy extends \Component\Policy\Policy
{
    const KEY = 'member.wonderLogin';
    const WONDER = 'wonder';
    protected $currentPolicy;
    protected $db;

    public function __construct(StorageInterface $storage = null)
    {
        parent::__construct($storage);
        $this->currentPolicy = $this->getValue(self::KEY);
    }

    public function useWonderLogin()
    {
        return $this->currentPolicy['useFl'] == 'y';
    }

    public function save($data = [])
    {
        $this->currentPolicy['useFl'] = gd_isset($data['useFl'], $this->currentPolicy['useFl']);
        $this->currentPolicy['clientId'] = gd_isset($data['clientId'], $this->currentPolicy['clientId']);
        $this->currentPolicy['clientSecret'] = gd_isset($data['clientSecret'], $this->currentPolicy['clientSecret']);
        $this->currentPolicy['companyName'] = gd_isset($data['companyName'], $this->currentPolicy['companyName']);
        $this->currentPolicy['serviceName'] = gd_isset($data['serviceName'], $this->currentPolicy['serviceName']);
        $this->currentPolicy['serviceUserName'] = gd_isset($data['serviceUserName'], $this->currentPolicy['serviceUserName']);
        $this->currentPolicy['serviceEmail'] = gd_isset($data['serviceEmail'], $this->currentPolicy['serviceEmail']);
        $this->currentPolicy['businessNo'] = gd_isset($data['businessNo'], $this->currentPolicy['businessNo']);
        $this->currentPolicy['redirectUri'] = gd_isset($data['redirectUri'], $this->currentPolicy['redirectUri']);

        return $this->setValue(self::KEY, $this->currentPolicy);
    }

    public function useSave($data = [])
    {
        $this->currentPolicy['useFl'] = $data['useFl'];
        return $this->setValue(self::KEY, $this->currentPolicy);
    }

    public function getUseLoginFl($mode = null) {
        if (!is_object($this->db)) {
            $this->db = \App::load('DB');
        }
        $returnData = true;
        $userClientId = $uuid = '';
        $policy = gd_policy(WonderLoginPolicy::KEY);
        $manageClientId = $policy['clientId'];

        $arrBind = $arrWhere = [];
        $arrWhere[] = 'memNo = ?';
        $arrWhere[] = 'snsTypeFl = ?';
        $this->db->bind_param_push($arrBind, 'i', Session::get('member.memNo'));
        $this->db->bind_param_push($arrBind, 's', 'wonder');
        $this->db->strField = 'uuid';
        $this->db->strWhere = implode(' AND ', gd_isset($arrWhere));
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER_SNS . implode(' ', $query);
        $snsData = $this->db->query_fetch($strSQL, $arrBind, false);

        if($snsData['uuid']) {
            $userClientId = explode(INT_DIVISION, $snsData['uuid'])[0];
            $uuid = explode(INT_DIVISION, $snsData['uuid'])[1];
            //debug('userClientId : ' . $userClientId . ', manageClientId :' .$manageClientId);
            if($userClientId != $manageClientId) {
                $returnData = false;
            }
        }
        switch($mode) {
            case 'data':
                $returnData['user'] = $userClientId;
                $returnData['config'] = $manageClientId;
                break;
            case 'mypage':
                $returnData['uuid'] = $uuid;
                break;
        }
        return $returnData;
    }
}
