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

namespace Bundle\Component\Memo;

use Component\Board\ArticleAdmin;
use Session;

class MemoAdmin extends \Component\Board\ArticleAdmin
{
    public function __construct($req)
    {
        parent::__construct($req);
    }

    /**
     *  게시판 엑셀다운로드
     * @param null $getValue
     * @return array|string
     * @internal param null $arrSnos
     */
    public function getExcelList($getValue = null)
    {
        $where = '';
        $arrWhere[] = "bm.bdId='" . $this->cfg['bdId']."'";

        if (gd_isset($getValue['searchWord'])) {
            switch ($getValue['searchField']) {
                case 'subject' :
                    $arrWhere[] = "bd.subject LIKE concat('%','".$getValue['searchWord']."','%')";
                    break;
                    break;
                case 'contents' :
                    $arrWhere[] = "bd.contents LIKE concat('%','".$getValue['searchWord']."','%')";
                    break;
                case 'writerNm' :

                    if (self::$_cfg['bdUserDsp'] == 'name') {
                        $_searchField = 'bd.writerNm';
                    } else {
                        $_searchField = 'bd.writerNick';
                    }

                    $arrWhere[] = $_searchField." LIKE concat('%','".$getValue['searchWord']."','%')";
                    break;
                case 'subject_contents' :
                    $arrWhere[] = "(bd.subject LIKE concat('%','".$getValue['searchWord']."','%') or bd.contents LIKE concat('%','".$getValue['searchWord']."','%') )";
                    break;
            }
        }

        if (gd_isset($getValue['rangDate'][0]) && gd_isset($getValue['rangDate'][1])) {
            if ($getValue['searchDateFl'] == 'modDt') {
                $dateField = 'bd.modDt';
            } else {
                $dateField = 'bd.regDt';
            }
            $arrWhere[] = $dateField . " between '".$getValue['rangDate'][0]."' and '".$getValue['rangDate'][1] . " 23:59'";
        }

        if(gd_isset($getValue['sno'])) {
            $arrWhere[] = ' bd.sno in (' . implode(',', $getValue['sno']) . ')';
        }

        if(gd_isset($getValue['category'])) {
            $arrWhere[] = ' bd.category = \''.$getValue['category'].'\'';
        }

        if(gd_isset($getValue['goodsPt'])) {
            $arrWhere[] = ' bd.goodsPt = '.$getValue['goodsPt'];
        }

        if(gd_isset($arrWhere)) $where = " WHERE  ".implode(' AND ', gd_isset($arrWhere));

        if(gd_is_provider()) {
            $query = "SELECT bm.*,m.groupSno,mg.groupNm FROM " . DB_BOARD_MEMO ." as bm LEFT JOIN es_bd_".$this->cfg['bdId']." as bd ON bd.sno = bm.bdSno JOIN ".DB_GOODS." as g on bd.goodsNo = g.goodsNo AND g.scmNo ='".Session::get('manager.scmNo')."' LEFT OUTER JOIN ".DB_MEMBER." as m ON bm.memNo = m.memNo LEFT OUTER JOIN ".DB_MEMBER_GROUP." as mg ON m.groupSno = mg.sno";
        } else {
            $query = "SELECT bm.*,m.groupSno,mg.groupNm FROM " . DB_BOARD_MEMO ." as bm LEFT JOIN es_bd_".$this->cfg['bdId']." as bd ON bd.sno = bm.bdSno LEFT OUTER JOIN ".DB_MEMBER." as m ON bm.memNo = m.memNo LEFT OUTER JOIN ".DB_MEMBER_GROUP." as mg ON m.groupSno = mg.sno";
        }

        $getData['list'] = gd_htmlspecialchars_stripslashes($this->db->query_fetch($query. $where));
        $getData['columns'] = gd_htmlspecialchars_stripslashes($this->db->query_fetch("DESC " . DB_BD_ . $this->cfg['bdId']));

        return $getData['list'];
    }
}
