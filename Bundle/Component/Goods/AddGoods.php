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

namespace Bundle\Component\Goods;

use Component\Database\DBTableField;
use Component\Mall\Mall;
use Globals;
use LogHandler;
use Request;
use SESSION;

/**
 * 추가 상품 관련 클래스
 * @author Jung Youngeun <atomyang@godo.co.kr>
 */
class AddGoods
{
    // 디비 접속
    protected $db;
    protected $gGlobal;

    /**
     * 생성자
     *
     */
    public function __construct()
    {
        if (!is_object($this->db)) {
            $this->db = \App::load('DB');
        }
        $this->gGlobal = Globals::get('gGlobal');
    }

    /**
     * 추가 상품 정보 불러오기
     *
     * @param int|array $addGoodsNo
     * @param string $goodsField
     * @param array $arrBind
     * @param bool|false $dataArray
     * @return string
     */
    public function getInfoAddGoods($addGoodsNo = null, $goodsField = null, $arrBind = null, $dataArray = false)
    {
        if (empty($arrBind) === true) {
            $arrBind = [];
        }

        // 상품 코드 정보가 있는경우
        if ($addGoodsNo) {
            $arrWhere = [];
            if($this->db->strWhere) $arrWhere[] =  $this->db->strWhere ;

            // 상품 코드가 배열인 경우
            if(is_array($addGoodsNo) === true) {
                $bindQuery = null;
                foreach($addGoodsNo as $val){
                    $bindQuery[] = '?';
                    $this->db->bind_param_push($arrBind, 'i', $val);
                }
                $arrWhere[] = "addGoodsNo IN (" . implode(",", $bindQuery) . ")";
            // 상품 코드가 하나인경우
            } else {
                $arrWhere[]  = 'addGoodsNo = ?';
                $this->db->bind_param_push($arrBind, 'i', $addGoodsNo);
            }

            $this->db->strWhere = implode(' AND ', $arrWhere);
        }

        // 사용할 필드가 있는 경우
        if ($goodsField) {
            if ($this->db->strField) {
                $this->db->strField = $goodsField . ', ' . $this->db->strField;
            } else {
                $this->db->strField = $goodsField;
            }
        }

        // 쿼리문 생성
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ADD_GOODS . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);

        if (count($getData) == 1 && $dataArray === false) {
            return gd_htmlspecialchars_stripslashes($getData[0]);
        }

        return gd_htmlspecialchars_stripslashes($getData);
    }

    /**
     * getInfoAddGoodsGroup
     *
     * @param null $sno
     * @param null $goodsField
     * @param null $arrBind
     * @param bool|false $dataArray
     * @return string
     */
    public function getInfoAddGoodsGroup($sno = null, $goodsField = null, $arrBind = null, $dataArray = false)
    {
        if (is_null($arrBind)) {
            // $arrBind = array();
        }
        if ($sno) {
            if ($this->db->strWhere) {
                $this->db->strWhere = " agg.sno = ? AND " . $this->db->strWhere;
            } else {
                $this->db->strWhere = " agg.sno = ?";
            }
            $this->db->bind_param_push($arrBind, 'i', $sno);
        }
        if ($goodsField) {
            if ($this->db->strField) {
                $this->db->strField = $goodsField . ', ' . $this->db->strField;
            } else {
                $this->db->strField = $goodsField;
            }
        }
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ADD_GOODS_GROUP . ' agg ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);

        if (count($getData) == 1 && $dataArray === false) {
            return gd_htmlspecialchars_stripslashes($getData[0]);
        }

        return gd_htmlspecialchars_stripslashes($getData);
    }


    public function getInfoAddGoodsGoods($addGoodsNo,$arrBind = null,$strOrder ='ag.regDt desc',$addWhere = null)
    {
        $mallBySession = SESSION::get(SESSION_GLOBAL_MALL);

        // 상품 코드가 배열인 경우
        if(is_array($addGoodsNo) === true) {
            $this->arrWhere[]  = "ag.addGoodsNo IN ('" . implode("','", $addGoodsNo) . "')";
            // 상품 코드가 하나인경우
        } else {
            $this->arrWhere[]   = 'ag.addGoodsNo = ?';
            $this->db->bind_param_push($arrBind, 'i', $addGoodsNo);
        }

        if($addWhere) {
            $this->arrWhere[] = $addWhere;
        }

        $this->db->strField = "ag.*,sm.companyNm as scmNm";

        $join[] =  ' INNER JOIN ' . DB_SCM_MANAGE . ' as sm ON sm.scmNo = ag.scmNo ';
        if($mallBySession) {
            $join[] =  ' LEFT JOIN ' . DB_ADD_GOODS_GLOBAL . ' as agg ON agg.addGoodsNo = ag.addGoodsNo AND agg.mallSno  = "'.$mallBySession['sno'].'" ';
            $this->db->strField .=",agg.goodsNm as globalGoodsNm";
        }
        $this->db->strJoin = implode('', $join);
        $this->db->strOrder = $strOrder;
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));


        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ADD_GOODS. ' as ag ' . implode(' ', $query);

        $data = $this->db->query_fetch($strSQL);
        unset($arrBind);
        unset($this->arrWhere);

        return $data;
    }


    public function getInfoAddGoodsGroupGoods($groupCd,$arrBind = null)
    {

        $this->arrWhere[] = "aggg.groupCd='".$groupCd."'";

        $join[] =  ' INNER JOIN ' . DB_ADD_GOODS . ' as ag ON ag.addGoodsNo = aggg.addGoodsNo ';
        $join[] =  ' INNER JOIN ' . DB_SCM_MANAGE . ' as sm ON sm.scmNo = ag.scmNo ';
        $join[] =' LEFT JOIN ' . DB_CATEGORY_BRAND . ' as cb ON cb.cateCd = ag.brandCd ';
        $this->db->strJoin = implode('', $join);
        $this->db->strOrder = "aggg.sort asc";
        $this->db->strField = "ag.*,sm.companyNm as scmNm,cb.cateNm as brandNm";
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));


        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ADD_GOODS_GROUP_GOODS. ' as aggg ' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL);

        return $data;
    }



    /**
     * 추가 상품 정보 불러오기
     *
     * @param int|array $addGoodsNo
     * @param string $goodsField
     * @param array $arrBind
     * @param bool|false $dataArray
     * @return string
     */
    public function getAddGoods($addGoodsNo)
    {
        $this->db->strWhere = "applyFl = 'y' && viewFl = 'y'";
        $data =  $this->getInfoAddGoods($addGoodsNo,null,null,true);

        return $data;
    }

    /**
     *글로벌 상품 출력
     *
     * @param string $goodsNo     상품코드
     * @param string $mallSno     몰번호
     * @param string $debug      query문을 출력, true 인 경우 결과를 return 과 동시에 query 출력 (기본 false)
     *
     * @return array 상품 정보
     */
    public function getDataAddGoodsGlobal($addGoodsNo,$mallSno = null)
    {
        // 상품 코드가 배열인 경우
        $bindQuery = $arrBind = null;
        if(is_array($addGoodsNo) === true) {
            foreach($addGoodsNo as $val)  {
                $bindQuery[] = '?';
                $this->db->bind_param_push($arrBind , 'i',$val);
            }
            $whereArr[] = "addGoodsNo IN ('" . implode(",", $bindQuery) . "')";
            // 상품 코드가 하나인경우
        } else {
            $whereArr[] = " addGoodsNo = ? ";
            $this->db->bind_param_push($arrBind , 'i',$addGoodsNo);
        }

        if($mallSno) {
            $whereArr[] = " mall = ? ";
            $this->db->bind_param_push($arrBind , 'i',$mallSno);
        }

        if (count($whereArr) > 0) {
            $whereStr = " WHERE " . implode(' AND ', $whereArr);
        }

        $arrField = DBTableField::setTableField('tableAddGoodsGlobal',null,['addGoodsNo']);
        $strSQL = 'SELECT ' . implode(', ', $arrField) . ' FROM ' . DB_ADD_GOODS_GLOBAL . $whereStr;

        $getData = $this->db->query_fetch($strSQL, $arrBind);

        return gd_htmlspecialchars_stripslashes($getData);
    }
}
