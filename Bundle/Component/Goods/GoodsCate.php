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


/**
 * 상품관련 게시판내 상품카테고리 Class
 */

namespace Bundle\Component\Goods;

use Framework\Utility\ArrayUtils;
use Session;

class GoodsCate
{
    protected $db = null;
    // __('대분류')
    // __('중분류')
    // __('소분류')
    // __('세분류')
    public $cateCd1 = Array(''=>'대분류');
    public $cateCd2 = Array(''=>'중분류');
    public $cateCd3 = Array(''=>'소분류');
    public $cateCd4 = Array(''=>'세분류');

    public function __construct()
    {
        $this->db = \App::load('DB');

        if (Session::has('member.groupSort')) {
            $groupSort = Session::get('member.groupSort');
        }
        else {
            $groupSort = 0;
        }

        $strSQL = "SELECT cateCd, cateNm FROM " . DB_CATEGORY_GOODS . " WHERE divisionFl='n' AND cateDisplayFl='y' AND catePermission<=? ORDER BY cateSort ASC";
        $this->db->bind_param_push($arrBind, 'i', $groupSort);
        $getCate = gd_htmlspecialchars_stripslashes($this->db->query_fetch($strSQL, $arrBind));
        unset($arrBind);

        if (ArrayUtils::isEmpty($getCate) === false) {
            for($i = 0; $i < count($getCate); $i++) {
                switch(strlen($getCate[$i]['cateCd'])) {
                    case 3 : {
                        $this->cateCd1["{$getCate[$i]['cateCd']}"] = $getCate[$i]['cateNm'];
                        break;
                    }
                    case 6 : {
                        $this->cateCd2["{$getCate[$i]['cateCd']}"] = $getCate[$i]['cateNm'];
                        break;
                    }
                    case 9 : {
                        $this->cateCd3["{$getCate[$i]['cateCd']}"] = $getCate[$i]['cateNm'];
                        break;
                    }
                    case 12 : {
                        $this->cateCd4["{$getCate[$i]['cateCd']}"] = $getCate[$i]['cateNm'];
                        break;
                    }
                }
            }
        }
    }

    /**
     * 상품카테고리 가져오기
     */
    public function getCateCd() {
        return array('cateCd1'=>$this->cateCd1, 'cateCd2'=>$this->cateCd2, 'cateCd3'=>$this->cateCd3, 'cateCd4'=>$this->cateCd4);
    }
}
