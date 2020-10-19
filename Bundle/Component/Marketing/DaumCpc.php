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

namespace Bundle\Component\Marketing;

use Component\Database\DBTableField;
use Component\Validator\Validator;
use Framework\Http\Request;
use Framework\Utility\ArrayUtils;
use Framework\Utility\ImageUtils;
use UserFilePath;

class DaumCpc {
    public $daumCpc;
    private $db;

    public function __construct() {
        $this->db = \App::load('DB');

        $strSql = "SELECT value FROM ".DB_MARKETING." WHERE company='daumcpc' AND mode='request'";
        $res = gd_htmlspecialchars_stripslashes($this->db->query_fetch($strSql, null, false));
        if (empty($res['value']) === false) {
            $this->daumCpc = json_decode($res['value'], true);
        }
    }

    public function uploadLogoFile($file,$mode){
        $uploadPath = UserFilePath::data('dburl','daumcpc');
        if (is_uploaded_file($file['tmp_name'])) {
            $tmp = explode('.',$file['name']);
            $ext = $tmp[count($tmp)-1];
            $filename = "daumShopLogo".$mode.".".$ext;
            @unlink($uploadPath.DS.$filename);
//            debug($uploadPath.DS.$filename);
//            exit;
            ImageUtils::thumbnail($file['tmp_name'], $uploadPath.DS.$filename, 65, 15, 4);

            @unlink($file['tmp_name']);
            return $filename;
        }
        return null;
    }

    public function registLogoFile(){
        $file = ArrayUtils::rearrangeFileArray(\Request::files()->get('file'));
        foreach($file as $v) {
            if(!$v['tmp_name'])return false;
        }
        foreach($file as $k => $v) {
            $this->daumCpc['logo'.$k] = $this->uploadLogoFile($v,$k);
        }
        $this->configration();
        return true;
    }

    public function configration(){
        $arrData['company'] = 'daumcpc';
        $arrData['mode'] = 'request';
        $arrData['value'] = json_encode(gd_isset($this->daumCpc));

        $strSql = "SELECT COUNT(*) AS cnt FROM ".DB_MARKETING." WHERE company='daumcpc' AND mode='request'";
        $res = $this->db->query_fetch($strSql, null, false);

        if ($res['cnt'] == 0) {
            $arrBind = $this->db->get_binding(DBTableField::tableMarketing(), $arrData, 'insert', array_keys($arrData));
            $this->db->set_insert_db(DB_MARKETING, $arrBind['param'], $arrBind['bind'], 'y');
        }
        else {
            $arrBind = $this->db->get_binding(DBTableField::tableMarketing(), $arrData, 'update', array_keys($arrData));
            $this->db->bind_param_push($arrBind['bind'], 's', 'daumcpc');
            $this->db->bind_param_push($arrBind['bind'], 's', 'request');
            $this->db->set_update_db(DB_MARKETING, $arrBind['param'], 'company=? AND mode=?', $arrBind['bind']);
        }
        unset($arrBind);
    }

    public function chkRegist($arr){
        $validator = new Validator();
        if($arr['service_agreYn'] != 'yes') return __("약관에 동의 하셔야 합니다.");

        $arr['tel'] = @implode('',$arr['tel']);
        $arr['cstel'] = @implode('',$arr['cstel']);
        $arr['csmail'] = @implode('@',$arr['csmail']);
        $arr['jotel'] = @implode('',$arr['jotel']);
        $arr['johpnum'] = @implode('',$arr['johpnum']);
        $arr['jomail'] = @implode('@',$arr['jomail']);

        $validator->add('shop_sno', '', true);
        $validator->add('mall_id', '', true);
        $validator->add('shop_sno', '', true);
        $validator->add('loginid', '', true);
        $validator->add('shopname', '', true);
        $validator->add('shopengname', '', true);
        $validator->add('categoryid', '', true);
        $validator->add('corppt', '', true);
        $validator->add('tel', 'number', true);
        $validator->add('cstel', 'number', true);
        $validator->add('csmail', 'email', true);
        $validator->add('joname', '', true);
        $validator->add('jotel', 'number', true);
        $validator->add('johpnum', 'number', true);
        $validator->add('jomail', 'email', true);

        if ($validator->act($arr, true) === false) {
            return implode("\n", $validator->errors);
        }

        return false;
    }
}
