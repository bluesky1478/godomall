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
namespace Bundle\Component\Category;

use Component\Member\Group\Util as GroupUtil;
use Component\Storage\Storage;
use Component\Database\DBTableField;
use Component\Validator\Validator;
use Framework\Utility\ArrayUtils;
use Request;
use Globals;

/**
 * 카테고리 class
 * 카테고리 관련 관리자 Class
 *
 * @author artherot
 * @version 1.0
 * @since 1.0
 * @copyright ⓒ 2016, NHN godo: Corp.
 */
class CategoryAdmin extends \Component\Category\Category
{

    const ECT_INVALID_ARG = 'CategoryAdmin.ECT_INVALID_ARG';

    const TEXT_REQUIRE_VALUE = '%s은(는) 필수 항목 입니다.';

    const TEXT_NOT_EXIST_VALUE = '%s 필수 항목이 존재하지 않습니다.';

    protected $arrBind = array();
    // 리스트 검색관련
    protected $arrWhere = array();
    // 리스트 검색관련
    protected $checked = array();
    // 리스트 검색관련
    protected $search = array();
    // 리스트 검색관련

    /**
     * 생성자
     *
     * @param string $cateType 카테고리 종류(goods,brand) , null인 경우 상품 카테고리 , (기본 null)
     */
    public function __construct($cateType = null)
    {
        parent::__construct($cateType);

        //상품테이블 분리 관련
        $this->goodsDivisionFl=  gd_policy('goods.config')['divisionFl'] == 'y' ? true : false;
    }

    /**
     * 카테고리 정보를 JSON 형태로 출력
     *
     * @param array $data 카테고리 정보
     * @return string JSON 형태의 카테고리 트리 정보
     */
    public function getTreeJson($data)
    {
        $jsonVar = array();
        $jsonArr = array();
        foreach ($data as $key => $val) {
            // 현재 카테고리 길이
            $tmp['Length'] = strlen($val['cateCd']);

            // 카테고리 명이 없는 경우
            if (empty($val['cateNm']) === true) {
                $val['cateNm'] = '_no cate name_';
            }

            // 카테고리 코드
            $jsonArr['attributes']['id'] = $val['cateCd'];

            // 카테고리 구분
            if ($tmp['Length'] == $this->cateLength) {
                $jsonArr['attributes']['rel'] = 'root'; // 1차 카테고리 구분
            } else if ($tmp['Length'] == ($this->cateLength * $this->cateDepth)) {
                $jsonArr['attributes']['rel'] = 'end'; // 마지막 카테고리 구분
            } else {
                $jsonArr['attributes']['rel'] = 'node'; // 서브 카테고리 구분
            }

            // 카테고리 이름
            $jsonArr['data']['title'] = strip_tags(stripcslashes($val['cateNm']));

            // 카테고리 감춤 여부
            if ($val['cateDisplayFl'] == 'n' || $val['cateDisplayMobileFl'] == 'n') {
                $jsonArr['data']['attributes']['class'] = 'gray_folder';
            } else {
                $jsonArr['data']['attributes']['class'] = '';
            }

            // 카테고리 접근 권한 여부
            if (empty($val['catePermission']) === true) {
                $jsonArr['data']['attributes']['class'] = $jsonArr['data']['attributes']['class'];
            } else {
                $jsonArr['data']['attributes']['class'] = ($jsonArr['data']['attributes']['class'] == '' ? '' : $jsonArr['data']['attributes']['class'] . ' ') . 'lock_folder';
            }

            // 카테고리 성인인증 사용
            if ($val['cateOnlyAdultFl']  =='y') {
                if (empty($val['catePermission']) === true)  $jsonArr['data']['attributes']['class'] .= ' adult_folder';
                else  $jsonArr['data']['attributes']['class'] .= ' adult_lock_folder';
            }

            // 카테고리 구분자 여부
            if ($val['divisionFl'] == 'y') {
                if ($val['cateDisplayFl'] == 'n') {
                    $jsonArr['data']['attributes']['class'] = 'group_folder displayN';
                } else {
                    $jsonArr['data']['attributes']['class'] = 'group_folder';
                }
                $jsonArr['attributes']['linkType'] = 'division';
            } else {
                $jsonArr['attributes']['linkType'] = 'category';
            }

            // 하위 분류가 있는 경우
            if (empty($val['subCnt']) === false) {
                $jsonArr['state'] = 'closed';
            } else {
                gd_isset($jsonArr['state']);
                unset($jsonArr['state']);
            }

            $tmp['Info'][1] = &$jsonVar[$val['cateSort']][];
            $tmp['Info'][1] = $jsonArr;
            $tmp['Node'][1] = $val['cateCd'];
            /*
             * // 1차 카테고리 인경우
             * if ($tmp['Length'] == $this->cateLength) {
             * //$jsonArr['attributes']['mdata'] = '{max_depth : '.( $this->cateDepth - 1 ).'}';
             * $tmp['Info'][1] = &$jsonVar[$val['cateSort']][];
             * $tmp['Info'][1] = $jsonArr;
             * $tmp['Node'][1] = $val['cateCd'];
             * // 1차 이상의 카테고리 인경우
             * } else {
             * //unset($jsonArr['attributes']['mdata']);
             * $tmp['Chk1'] = ($tmp['Length'] - $this->cateLength) / $this->cateLength;
             * $tmp['Chk2'] = $tmp['Chk1'] + 1;
             * if ($tmp['Info'][$tmp['Chk1']]['attributes']['id'] == $tmp['Node'][$tmp['Chk1']]) {
             * $tmp['Info'][$tmp['Chk2']] = &$tmp['Info'][$tmp['Chk1']]['children'][$val['cateSort']][];
             * $tmp['Info'][$tmp['Chk2']] = $jsonArr;
             * if ($tmp['Length'] == ($this->cateLength * $this->cateDepth)) {
             * $tmp['Info'][$tmp['Chk2']]['attributes']['rel'] = 'end'; // 마지막 카테고리 구분
             * }else{
             * $tmp['Info'][$tmp['Chk2']]['attributes']['rel'] = 'node'; // 서브 카테고리 구분
             * }
             * }
             * $tmp['Node'][$tmp['Chk2']] = $val['cateCd'];
             * }
             */
        }

        return json_encode($this->sortCategoryJson($jsonVar));
    }

    /**
     * 카테고리 순서 재정렬 (해당 카테고리내 순서 재정렬)
     *
     * @param string $cateCd 카테고리 코드
     */
    public function setCategoryResort($cateCd)
    {
        if (strlen($cateCd) >= $this->cateLength) {
            $arrWhere = array();
            $arrWhere[] = " length(cateCd) = '" . strlen($cateCd) . "' ";
            if (strlen($cateCd) != $this->cateLength) {
                $arrWhere[] = " cateCd LIKE '" . substr($cateCd, 0, -$this->cateLength) . "%' ";
            }
            $data = $this->getCategoryData(null, null, 'cateCd', implode(' AND ', $arrWhere), 'cateSort ASC');
            $i = 1;
            $strSQLArr = array();

            foreach ($data as $key => $val) {
                $strSQLArr[] = "UPDATE " . $this->cateTable . " SET cateSort = '" . $i . "' WHERE cateCd = '" . $val['cateCd'] . "'";
                $i++;
            }
            $multiQuery = implode(';', $strSQLArr);
            $this->db->multi_result($multiQuery);
        }
    }

    /**
     * 카테고리 재정렬 (해당 카테고리 순서 이후)
     *
     * @param string $cateCd 카테고리 코드
     */
    protected function setCategorySort($cateCd)
    {
        if (strlen($cateCd) >= $this->cateLength) {
            $arrWhere = array();
            $arrWhere[] = " length(cateCd) = '" . strlen($cateCd) . "' ";
            if (strlen($cateCd) != $this->cateLength) {
                $arrWhere[] = " cateCd LIKE '" . substr($cateCd, 0, $this->cateLength) . "%' ";
            }
            list($data) = $this->getCategoryData($cateCd, null, 'cateSort');
            $strSQL = "UPDATE " . $this->cateTable . " SET cateSort = cateSort - 1 WHERE " . implode(' AND ', $arrWhere) . " AND cateSort > " . $data['cateSort'];
            $this->db->query($strSQL);
        }
    }

    /**
     * 카테고리에 저장된 최대 depth
     *
     * @return integer 최대 depth
     */
    public function getCategoryDepth()
    {
        list($data) = $this->getCategoryData(null, null, 'max( length( cateCd ) ) as depth');

        return ($data['depth'] / $this->cateLength);
    }

    /**
     * 카테고리 트리 정보 출력
     *
     * @param string $cateCd 카테고리 코드
     * @return array 상품 정보
     */
    public function getCategoryTreeData($cateCd = null)
    {
        if (empty($cateCd) === true) {
            $cateLen = $this->cateLength;
            $whereArr[] = ' length( c.cateCd ) = ' . $cateLen;
        } else {
            $cateLen = $this->cateLength * ((strlen($cateCd) / $this->cateLength) + 1);
            $whereArr[] = ' c.cateCd LIKE \'' . $cateCd . '%\' ';
            $whereArr[] = ' length( c.cateCd ) = ' . $cateLen;
        }

        $arrInclude = array('cateNm', 'cateCd', 'divisionFl', 'cateThemeId', 'cateDisplayFl', 'cateDisplayMobileFl', 'catePermission', 'cateSort','cateOnlyAdultFl');
        $arrField = DBTableField::setTableField($this->cateFuncNm, $arrInclude, null, 'c');

        $this->db->strField = implode(', ', $arrField);
        $this->db->strWhere = implode(' AND ', $whereArr);
        $this->db->strOrder = ' c.cateSort ASC ';

        // 서브 쿼리 (하위 분류가 있는지를 체크)
        $subQuery = 'SELECT count(ct.cateCd) FROM ' . $this->cateTable . ' ct WHERE length( ct.cateCd ) = ' . ($cateLen + $this->cateLength) . ' AND left(ct.cateCd, ' . $cateLen . ') = c.cateCd';

        // 상품 옵션 정보
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ', (' . $subQuery . ') as subCnt FROM ' . $this->cateTable . ' c ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL);

        return gd_htmlspecialchars_stripslashes($getData);
    }

    /**
     * 카테고리 삭제
     *
     * @param string $cateCd 카테고리 코드
     */
    public function setDeleteCategory($cateCd)
    {
        // 카테고리 코드  체크
        if (Validator::required(gd_isset($cateCd)) === false) {
            return false;
        }

        $seoCommonPageCode = 'cateCd';

        // --- 디비 삭제
        $arrBind = [];
        $this->db->bind_param_push($arrBind, 's', $cateCd);
        $this->db->set_delete_db($this->cateTable, 'cateCd LIKE concat(?,\'%\')', $arrBind);
        unset($arrBind);

        $arrBind = [];
        $this->db->bind_param_push($arrBind, 's', $cateCd);
        $this->db->set_delete_db($this->cateTable.'Global', 'cateCd LIKE concat(?,\'%\')', $arrBind);
        unset($arrBind);

        if ($this->cateTable == DB_CATEGORY_BRAND) {
            $arrBind = [];
            $this->db->bind_param_push($arrBind, 's', $cateCd);
            $this->db->setModDtUse(false);
            $this->db->set_update_db(DB_GOODS, 'brandCd = \'\'', 'brandCd LIKE concat(?, \'%\')', $arrBind);
            if($this->goodsDivisionFl) {
                $this->db->setModDtUse(false);
                $this->db->set_update_db(DB_GOODS_SEARCH, 'brandCd = \'\'', 'brandCd LIKE concat(?, \'%\')', $arrBind);
            }
            unset($arrBind);

            $arrBind = [];
            $this->db->bind_param_push($arrBind, 's', $cateCd);
            $this->db->set_delete_db(DB_GOODS_LINK_BRAND, 'cateCd LIKE concat(?,\'%\')', $arrBind);
            unset($arrBind);

            $seoCommonPageCode = 'brandCd';
        } else if ($this->cateTable == DB_CATEGORY_GOODS) {
            $arrBind = [];
            $this->db->bind_param_push($arrBind, 's', $cateCd);
            $this->db->setModDtUse(false);
            $this->db->set_update_db(DB_GOODS, 'cateCd = \'\'', 'cateCd LIKE concat(?, \'%\')', $arrBind);
            if($this->goodsDivisionFl) {
                $this->db->setModDtUse(false);
                $this->db->set_update_db(DB_GOODS_SEARCH, 'cateCd = \'\'', 'cateCd LIKE concat(?, \'%\')', $arrBind);
            }
            unset($arrBind);

            $arrBind = [];
            $this->db->bind_param_push($arrBind, 's', $cateCd);
            $this->db->set_delete_db(DB_GOODS_LINK_CATEGORY, 'cateCd LIKE concat(?,\'%\')', $arrBind);
            unset($arrBind);
        }

        // seo태그 삭제
        $seoTag = \App::load('\\Component\\Policy\\SeoTag');
        $seoPath = array_flip($seoTag->seoConfig['commonPage']);
        $seoTag->deleteSeoTag(['path' => $seoPath[$seoCommonPageCode], 'pageCode' => $cateCd]);

        //테마사용수 체크
        $this->setUseCntThemeConfig();


        // 상품 매핑 저장
        $setData['cateType'] = $this->cateType;
        $setData['mappingMode'] = 'd';
        $setData['mappingFl'] = 'n';
        $setData['mappingLog'] = $cateCd;

        $arrBind = $this->db->get_binding(DBTableField::tableLogGoodsMapping(), $setData, 'insert');
        $this->db->set_insert_db(DB_LOG_GOODS_MAPPING, $arrBind['param'], $arrBind['bind'], 'y');
    }

    /**
     * 카테고리 이름 변경
     *
     * @param string $cateCd 카테고리 코드
     * @param string $cateNm 카테고리 이름
     */
    public function setRenameCategory($arrData)
    {
        // 카테고리 코드 및 카테고리 이름 체크
        if (Validator::required(gd_isset($arrData['cateCd'])) === false || Validator::required(gd_isset($arrData['cateNm'])) === false) {
            return false;
        }

        $arrField = array('cateNm');
        $arrBind = $this->db->get_binding(DBTableField::getBindField($this->cateFuncNm, $arrField), $arrData, 'update');
        $strWhere = 'cateCd = ?';
        $this->db->bind_param_push($arrBind['bind'], 's', $arrData['cateCd']);
        $this->db->set_update_db($this->cateTable, $arrBind['param'], $strWhere, $arrBind['bind']);
        unset($arrBind);
    }

    /**
     * 카테고리 이동
     *
     * @param string $cateCd 이동할 카테고리 코드
     * @param string $targetCateCd 타겟의 카테고리 코드
     * @param string $moveLoc 위치값 ('after', 'before', 'inside')
     * @return boolean true or false
     */
    public function setMoveCategory($cateCd, $targetCateCd, $moveLoc)
    {
        if (!$cateCd || !$targetCateCd || !in_array($moveLoc, array('after', 'before', 'inside'))) {
            return false;
        }

        $cateCdLen = strlen($cateCd);
        $targetCateCdLen = strlen($targetCateCd);
        $parentCd = substr($cateCd, 0, -$this->cateLength); // 자신의 부모
        $parentTargetCd = substr($targetCateCd, 0, -$this->cateLength); // 타겟의 부모

        if ($parentCd == $targetCateCd && $moveLoc == 'inside') { // 자기 부모 안으로의 이동인 경우.. 그냥 return
            return true;
        }

        $parentSame = true; // 이동한 결과 부모 동일 여부
        if ($parentCd != $parentTargetCd || $moveLoc == 'inside') {
            $parentSame = false; // 이동한 결과 부모 동일 여부
        }
        if ($moveLoc == 'inside') {
            $realTargetCd = $targetCateCd; // 실제 타겟 부모
            $realTargetCdLen = strlen($realTargetCd) + $this->cateLength;
        } else {
            $realTargetCd = $parentTargetCd; // 실제 타겟 부모
            $realTargetCdLen = strlen($realTargetCd) + $this->cateLength;
        }

        // 이동결과 자신의 부모로 이동이 아닌경우
        if ($parentSame === false) {
            list($data) = $this->getCategoryData(null, $realTargetCd, 'max(cateCd) as max', 'length(cateCd) = \'' . $realTargetCdLen . '\'');
            $newCateCd = $this->getCategoryCode($realTargetCd, $data['max'], strlen($data['max']));
        } else {
            $newCateCd = $cateCd; // 변경된 cateCd
        }

        // 이동후 순서 설정
        if ($moveLoc == 'inside') {
            list($data) = $this->getCategoryData(null, $realTargetCd, 'max(cateSort) as max', 'length(cateCd) = \'' . $realTargetCdLen . '\'');
            $targetSort = $data['max'];
        } else {
            list($data) = $this->getCategoryData($targetCateCd, null, 'cateSort');
            $targetSort = $data['cateSort'];
            list($data) = $this->getCategoryData($cateCd, null, 'cateSort');
            $oldSort = $data['cateSort'];
        }
        if ($moveLoc == 'before') $newSort = $targetSort; // 이동후 순서
        if ($moveLoc == 'after') $newSort = $targetSort + 1; // 이동후 순서
        if ($moveLoc == 'inside') $newSort = $targetSort + 1; // 이동후 순서

        // 순서만 이동이 된 경우
        if ($cateCd == $newCateCd) {
            if ($cateCdLen == $this->cateLength) {
                $whereStr = " length(cateCd) = '" . $cateCdLen . "' ";
            } else {
                $whereStr = " length(cateCd) = '" . $cateCdLen . "' AND cateCd LIKE '" . $parentCd . "%'";
            }
            if ($newSort > $oldSort) {
                $newSort = $newSort - 1;
                $strSQL = "UPDATE " . $this->cateTable . " SET cateSort = cateSort - 1 WHERE " . $whereStr . " AND cateSort BETWEEN " . ($oldSort + 1) . " AND " . $newSort;
                $this->db->query($strSQL);
            }
            if ($newSort < $oldSort) {
                $strSQL = "UPDATE " . $this->cateTable . " SET cateSort = cateSort + 1 WHERE " . $whereStr . " AND cateSort BETWEEN " . $newSort . " AND " . ($oldSort - 1);
                $this->db->query($strSQL);
            }

            $strSQL = "UPDATE " . $this->cateTable . " SET cateSort = '" . $newSort . "' WHERE cateCd = '" . $newCateCd . "'";
            $this->db->query($strSQL);

            return true;
        }

        // 이동한 카테고리 순서 변경 및 cateCd 변경 처리
        if ($moveLoc == 'before' || $moveLoc == 'after') {
            $arrWhere = array();
            $arrWhere[] = " length(cateCd) = '" . strlen($newCateCd) . "' ";
            if (strlen($newCateCd) != $this->cateLength) {
                $arrWhere[] = " cateCd LIKE '" . substr($newCateCd, 0, $this->cateLength) . "%' ";
            }
            $strSQL = "UPDATE " . $this->cateTable . " SET cateSort = cateSort + 1 WHERE " . implode(' AND ', $arrWhere) . " AND cateSort >" . ($moveLoc == 'before' ? '=' : '') . " " . $targetSort;
            $this->db->query($strSQL);
        }
        $this->setCategorySort($cateCd);

        $data = $this->getCategoryData(null, $cateCd, 'cateCd');
        foreach ($data as $cKey => $cVal) {
            if ($cVal['cateCd'] == $cateCd) {
                $updateStr = ", cateSort = '" . $newSort . "' ";
                //해외몰 카테고리명이 존재할 경우 cateCd 변경.
                $gArrBind = [];
                $this->db->bind_param_push($gArrBind, 's', $newCateCd);
                $this->db->bind_param_push($gArrBind, 's', $cateCd);
                $this->db->set_update_db($this->cateGlobalTable, ['cateCd = ?'], 'cateCd = ?', $gArrBind);
            } else {
                $updateStr = "";
            }
            $newCateCdStr = $newCateCd . substr($cVal['cateCd'], $cateCdLen);
            $strSQL = "UPDATE " . $this->cateTable . " SET cateCd = '" . $newCateCdStr . "' " . $updateStr . " WHERE cateCd = '" . $cVal['cateCd'] . "'";
            $this->db->query($strSQL);

            $tmpLog[] = $cVal['cateCd'] . STR_DIVISION . $newCateCdStr;
        }

        // 상품 매핑 저장
        if (isset($tmpLog)) {
            $setData['cateType'] = $this->cateType;
            $setData['mappingMode'] = 'm';
            $setData['mappingFl'] = 'n';
            $setData['mappingLog'] = implode(MARK_DIVISION, $tmpLog);

            $arrBind = $this->db->get_binding(DBTableField::tableLogGoodsMapping(), $setData, 'insert');
            $this->db->set_insert_db(DB_LOG_GOODS_MAPPING, $arrBind['param'], $arrBind['bind'], 'y');
        }

        return true;
    }

    /**
     * 카테고리의 등록 및 수정에 관련된 정보
     *
     * @param string $arrData 카테고리 정보
     */
    public function getDataCategory($cateCd = null, $modeStr)
    {
        $this->gGlobal = Globals::get('gGlobal');
        $checked = [];

        // --- 등록인 경우
        if (is_null($cateCd)) {
            // 기본 정보
            $data['mode'] = $modeStr;
            $data['cateCd'] = null;

            // 초기 화면인 경우
            if ($modeStr == 'onload') {
                $getData['data'] = gd_htmlspecialchars_stripslashes($data);
                return $getData;
            }

            // 기본값 설정
            DBTableField::setDefaultData($this->cateFuncNm, $data);

            gd_isset($data['cateHtml1SameFl'],'y');
            gd_isset($data['cateHtml2SameFl'],'y');
            gd_isset($data['cateHtml3SameFl'],'y');
            $data['mallDisplay'] = 'all';
            $checked['mallDisplay']['all']  = 'checked="checked"';

            // --- 수정인 경우
        } else {
            // 기본 정보
            $data['mode'] = $modeStr;
            $tmp = $this->getCategoryData($cateCd);
            $data = array_merge($data, $tmp[0]);
            if (empty($data['recomGoodsNo']) === false) {
                $goods = \App::load('\\Component\\Goods\\Goods');
                $data['recomGoodsNo'] = $goods->getGoodsDataDisplay($data['recomGoodsNo']);
            }

            if (empty($data['catePermissionGroup']) === false) {
                $data['catePermissionGroup'] = GroupUtil::getGroupName("sno in (" . implode(',', explode(INT_DIVISION, $data['catePermissionGroup'])) . ")");
            }


            // 기본값 설정
            DBTableField::setDefaultData($this->cateFuncNm, $data);

            if($this->gGlobal['isUse']) {
                foreach(explode(",",$data['mallDisplay']) as $k => $v) {
                    $checked['mallDisplay'][$v]  = 'checked="checked"';
                }
                $tmpGlobalData = $this->getCategoryDataGlobal($data['cateCd']);
                $data['globalData'] = array_combine (array_column($tmpGlobalData, 'mallSno'), $tmpGlobalData);

            }

            if($data['cateHtml1'] =='<p>&nbsp;</p>') unset($data['cateHtml1']);
            if($data['cateHtml2'] =='<p>&nbsp;</p>') unset($data['cateHtml2']);
            if($data['cateHtml3'] =='<p>&nbsp;</p>') unset($data['cateHtml3']);
            if($data['cateHtml1Mobile'] =='<p>&nbsp;</p>') unset($data['cateHtml1Mobile']);
            if($data['cateHtml2Mobile'] =='<p>&nbsp;</p>') unset($data['cateHtml2Mobile']);
            if($data['cateHtml3Mobile'] =='<p>&nbsp;</p>') unset($data['cateHtml3Mobile']);

            gd_isset($data['cateHtml1SameFl'],'n');
            gd_isset($data['cateHtml2SameFl'],'n');
            gd_isset($data['cateHtml3SameFl'],'n');

            if(trim($data['cateHtml1']) == trim($data['cateHtml1Mobile'])) {
                $data['cateHtml1SameFl'] = "y";
            }
            if(trim($data['cateHtml2']) == trim($data['cateHtml2Mobile'])) {
                $data['cateHtml2SameFl'] = "y";
            }
            if(trim($data['cateHtml3']) == trim($data['cateHtml3Mobile'])) {
                $data['cateHtml3SameFl'] = "y";
            }
        }

        // --- 카테고리 테마
        if ($this->cateType == 'goods') {
            $themes = $this->getCategoryAdminTheme();
            $categoryCode= "category";
        } else if ($this->cateType == 'brand') {
            $themes = $this->getBrandAdminTheme();
            $categoryCode= "brand";
        }

        $data['parentRecomGoods'] = false;
        $cateDepth = (strlen($data['cateCd']) / $this->cateLength);
        // 부모 카테고리의 정보 체크 (추천상품 / 접근권한)
        if ($cateDepth > 1) {
            $arrCateCd = [];
            for ($i = 1; $i < $cateDepth; $i++) {
                $arrCateCd[] = substr($data['cateCd'], 0, ($i * $this->cateLength));
            }

            // 카테고리 정보
            $this->db->strField = 'recomSubFl,recomSortType,recomPcThemeCd,recomGoodsNo,recomDisplayFl,catePermissionSubFl,mallDisplaySubFl,cateOnlyAdultSubFl';
            $this->db->strWhere = 'cateCd IN (\'' . implode('\', \'', $arrCateCd) . '\')';
            $query = $this->db->query_complete();
            $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . $this->cateTable . ' ' . implode(' ', $query);
            $parentGoods = $this->db->query_fetch($strSQL);

            foreach ($parentGoods as $key => $val) {
                //상위 추천상품 정보
                if ($val['recomSubFl'] == 'y') {
                    $data['parentRecomGoods'] = true;
                }
                //권한정보
                if ($val['catePermissionSubFl'] == 'y') {
                    $data['parentPermission'] = true;
                }

                //성인인증정보
                if ($val['cateOnlyAdultSubFl'] == 'y') {
                    $data['parentOnlyAdult'] = true;
                }

                //권한정보
                if ($val['mallDisplaySubFl'] == 'y') {
                    $data['parentMallDisplay'] = true;
                }
            }
        }


        // --- 회원 그룹
        //$group = gd_member_groups();
        $group = array('0' => __('전체(회원+비회원)'), '1' => __('회원전용(비회원제외)'), '2' => __('특정 회원등급'));

        $seoTag = \App::load('\\Component\\Policy\\SeoTag');
        $data['seoTag']['target'] = $categoryCode;
        $data['seoTag']['config'] = $seoTag->seoConfig['tag'];
        $data['seoTag']['replaceCode'] = $seoTag->seoConfig['replaceCode'][$categoryCode];
        if(empty($data['seoTagSno']) === false) {
            $data['seoTag']['data'] = $seoTag->getSeoTagData($data['seoTagSno']);
        }
        $readonly = array();
        // 기준몰 브랜드명 공통사용 설정
        foreach($data['globalData'] as $key => $val){
            if ($val['cateNmGlobalFl'] == 'y') {
                $checked['cateNmGlobalFl'][$key] = 'checked="checked"';
                $readonly['readonly'][$key] =' readonly = "readonly"';
            }
        }

        $checked['seoTagFl'][$data['seoTagFl']]  =$checked['catePermissionDisplayFl'][$data['catePermissionDisplayFl']]  = $checked['cateOnlyAdultSubFl'][$data['cateOnlyAdultSubFl']]  = $checked['cateOnlyAdultDisplayFl'][$data['cateOnlyAdultDisplayFl']]  = $checked['cateOnlyAdultFl'][$data['cateOnlyAdultFl']]  = $checked['cateHtml3SameFl'][$data['cateHtml3SameFl']]  = $checked['cateHtml2SameFl'][$data['cateHtml2SameFl']]  = $checked['cateHtml1SameFl'][$data['cateHtml1SameFl']]  = $checked['mallDisplaySubFl'][$data['mallDisplaySubFl']]  = $checked['recomSortAutoFl'][$data['recomSortAutoFl']]  = $checked['cateImgMobileFl'][$data['cateImgMobileFl']] = $checked['catePermissionSubFl'][$data['catePermissionSubFl']] = $checked['catePermission'][$data['catePermission']] = $checked['recomDisplayMobileFl'][$data['recomDisplayMobileFl']] = $checked['recomDisplayFl'][$data['recomDisplayFl']] = $checked['recomSubFl'][$data['recomSubFl']] = $checked['sortAutoFl'][$data['sortAutoFl']] = $checked['cateDisplayMobileFl'][$data['cateDisplayMobileFl']] = $checked['cateDisplayFl'][$data['cateDisplayFl']] = $checked['divisionFl'][$data['divisionFl']] = $checked['cateDisplayFl'][$data['cateDisplayFl']]  = 'checked="checked"';


        $selected = array();
        $selected['recomMobileThemeCd'][$data['recomMobileThemeCd']] =  $selected['mobileThemeCd'][$data['mobileThemeCd']] = $selected['pcThemeCd'][$data['pcThemeCd']] = $selected['recomPcThemeCd'][$data['recomPcThemeCd']] = "selected = 'selected'";


        $getData['data'] = gd_htmlspecialchars_stripslashes($data);
        $getData['themes'] = gd_isset($themes);
        $getData['group'] = $group;
        $getData['checked'] = $checked;
        $getData['selected'] = $selected;
        $getData['readonly'] = $readonly;

        return $getData;
    }

    /**
     * 카테고리 생성
     *
     * @param string $arrData 카테고리 정보
     */
    public function saveInfoCategory($arrData)
    {
        $filesValue = Request::files()->toArray();

        // 카테고리명 체크
        if (Validator::required(gd_isset($arrData['cateNm'])) === false) {
            throw new \Exception(sprintf(__('%s은(는) 필수 항목 입니다.'), '카테고리명'), 500);
        }

        $strWhere = " length(cateCd) = '" . $this->cateLength . "' ";


        // 새로운 카테고리 번호 생성
        list($data) = $this->getCategoryData(null, null, 'max(cateCd) as max', $strWhere);
        $arrData['cateCd'] = $this->getCategoryCode(null, $data['max'], $this->cateLength);

        // 새로운 카테고리의 순서 생성
        list($data) = $this->getCategoryData(null, null, 'max(cateSort) as max', $strWhere);
        $arrData['cateSort'] = $data['max'] + 1;

        //상단꾸미기 영역
        if ($arrData['cateHtml1SameFl'] =='y')  $arrData['cateHtml1Mobile']  = $arrData['cateHtml1'] ;
        if ($arrData['cateHtml2SameFl'] =='y')  $arrData['cateHtml2Mobile']  = $arrData['cateHtml2'] ;
        if ($arrData['cateHtml3SameFl'] =='y')  $arrData['cateHtml3Mobile']  = $arrData['cateHtml3'] ;

        //추천상품 수동진열
        if($arrData['recomSortAutoFl'] =='n' && gd_isset($arrData['goodsNoData'])) {
            if (is_array($arrData['goodsNoData'])) {
                $arrData['recomGoodsNo'] = implode(INT_DIVISION, $arrData['goodsNoData']);
            }

        }

        //추천상품 자동진열
        if($arrData['recomSortAutoFl'] =='y' && gd_isset($arrData['recomGoodsNo'])) {
            if (is_array($arrData['recomGoodsNo'])) {
                $arrData['recomGoodsNo'] = implode(INT_DIVISION, $arrData['recomGoodsNo']);
            }
        }

        // 회원등급
        if (gd_isset($arrData['memberGroupNo'])) {
            if (is_array($arrData['memberGroupNo'])) {
                $arrData['catePermissionGroup'] = implode(INT_DIVISION, $arrData['memberGroupNo']);
            }
        }

        /*
        if ($this->cateType = 'goods') {
            $storageName = Storage::PATH_CODE_CATEGORY;
        } else {
            $storageName = Storage::PATH_CODE_BRAND;
        } */

        $storageName = Storage::PATH_CODE_CATEGORY;


        if ($filesValue) {
            foreach ($filesValue as $k => $v) {
                $fileDate = $v;
                if ($fileDate['name']) {
                    if (gd_file_uploadable($fileDate, 'image') === true) {  // 이미지 업로드
                        $imageExt = strrchr($v['name'], '.');
                        $arrData[$k] = $arrData['cateCd'] . '_' . $k .'_'.$this->cateType. $imageExt; // 이미지명 공백 제거
                        $targetImageFile = $arrData[$k];
                        $tmpImageFile = $v['tmp_name'];
                        Storage::disk($storageName)->upload($tmpImageFile, $targetImageFile);
                    } else {
                        throw new \Exception(__('이미지파일만 가능합니다.'));
                    }
                }

            }
        }


        if ($arrData['cateImgDel'] == 'y') {
            Storage::disk($storageName)->delete($arrData['cateImg']);
            $arrData['cateImg'] = '';
        }

        if ($arrData['cateOverImgDel'] == 'y') {
            Storage::disk($storageName)->delete($arrData['cateOverImg']);
            $arrData['cateOverImg'] = '';
        }

        if ($arrData['cateImgMobileDel'] == 'y') {
            Storage::disk($storageName)->delete($arrData['cateImgMobile']);
            $arrData['cateImgMobile'] = '';
        }


        if (gd_isset($arrData['cateDisplayFl']) == '') $arrData['cateDisplayFl'] = 'n';
        if (gd_isset($arrData['cateDisplayMobileFl']) == '') $arrData['cateDisplayMobileFl'] = 'n';
        if (gd_isset($arrData['recomDisplayFl']) == '') $arrData['recomDisplayFl'] = 'n';
        if (gd_isset($arrData['recomDisplayMobileFl']) == '') $arrData['recomDisplayMobileFl'] = 'n';
        if (gd_isset($arrData['recomSubFl']) == '') $arrData['recomSubFl'] = 'n';
        if (gd_isset($arrData['catePermissionSubFl']) == '') $arrData['catePermissionSubFl'] = 'n';
        if (gd_isset($arrData['catePermissionDisplayFl']) == '') $arrData['catePermissionDisplayFl'] = 'n';
        if (gd_isset($arrData['cateOnlyAdultSubFl']) == '') $arrData['cateOnlyAdultSubFl'] = 'n';
        if (gd_isset($arrData['cateOnlyAdultDisplayFl']) == '') $arrData['cateOnlyAdultDisplayFl'] = 'n';

        if ($arrData['cateImgMobileFl'] == 'y') {
            $arrData['cateImgMobile']  = $arrData['cateImg'] ;
        } else {
            $arrData['cateImgMobileFl'] = "n";
        }

        if($this->gGlobal['isUse']) {
            if(in_array('all',$arrData['mallDisplay']))  $arrData['mallDisplay'] = implode(",",array_keys($this->gGlobal['useMallList'] ));
            else  $arrData['mallDisplay'] = implode(",",$arrData['mallDisplay'] );
        }
        if (gd_isset($arrData['mallDisplaySubFl']) == '') $arrData['mallDisplaySubFl'] = 'n'; //글로벌 노출상점 하위카테고리 동일 적용 사용 여부

        $seoTagData = $arrData['seoTag'];
        $seoTag = \App::load('\\Component\\Policy\\SeoTag');
        if($arrData['seoTagFl'] =='n') {
            if($arrData['seoTagSno']) {
                $seoTag->deleteSeoTag(['sno'=>$arrData['seoTagSno']]);
            }
            $arrData['seoTagSno'] = "";
        } else {
            $seoTagData['sno'] = $arrData['seoTagSno'];
            $pageGroup = $this->cateType == 'goods' ? 'category' : 'brand';
            $seoTagData['pageCode'] = $arrData['cateCd'];
            $arrData['seoTagSno'] = $seoTag->saveSeoTagEach($pageGroup,$seoTagData);
        }

        // 카테고리 정보 저장
        $funcName = $this->cateFuncNm;
        $arrBind = $this->db->get_binding(DBTableField::$funcName(), $arrData, 'insert', null);
        $this->db->set_insert_db($this->cateTable, $arrBind['param'], $arrBind['bind'], 'y');
        unset($arrBind);

        if($arrData['catePermissionSubFl'] =='y') {
            $arrUpdate[] = "catePermission = '".$arrData['catePermission']."'";
            $arrUpdate[] = "catePermissionGroup = '".$arrData['catePermissionGroup']."'";
            $arrUpdate[] = "catePermissionDisplayFl = '".$arrData['catePermissionDisplayFl']."'";
            $this->db->set_update_db($this->cateTable, $arrUpdate,  "cateCd LIKE '".$arrData['cateCd']."%' ");
            unset($arrUpdate);
        }

        if($arrData['cateOnlyAdultSubFl'] =='y') {
            $arrUpdate[] = "cateOnlyAdultFl = '".$arrData['cateOnlyAdultFl']."'";
            $arrUpdate[] = "cateOnlyAdultDisplayFl = '".$arrData['cateOnlyAdultDisplayFl']."'";
            $this->db->set_update_db($this->cateTable, $arrUpdate,  "cateCd LIKE '".$arrData['cateCd']."%' ");
            unset($arrUpdate);
        }

        if($arrData['mallDisplaySubFl'] =='y') {
            $arrUpdate[] = "mallDisplay = '".$arrData['mallDisplay']."'";
            $this->db->set_update_db($this->cateTable, $arrUpdate,  "cateCd LIKE '".$arrData['cateCd']."%' ");
            unset($arrUpdate);
        }

        if($this->gGlobal['isUse']) {
            $funcName = $this->cateFuncNm."Global";

            //노출상점설정
            if(in_array('all',$arrData['mallDisplay'])) { //전체인경우
                foreach($this->gGlobal['useMallList'] as $k => $v) {
                    if($v['standardFl'] =='n' && $arrData['global'][$v]) {
                        $globalData = $arrData['global'][$v];
                        $globalData['mallSno'] = $v['sno'];
                        $globalData['cateCd'] = $arrData['cateCd'];

                        $arrBind = $this->db->get_binding(DBTableField::$funcName(), $globalData, 'insert');
                        $this->db->set_insert_db($this->cateTable."Global", $arrBind['param'], $arrBind['bind'], 'y');
                        unset($arrBind);
                    }
                }
            } else {
                foreach($arrData['globalData'] as $k => $v) {
                    $globalData = $v;
                    $globalData['mallSno'] = $k;
                    $globalData['cateCd'] = $arrData['cateCd'];
                    if (gd_isset($v['cateNmGlobalFl'])) {
                        $globalData['cateNmGlobalFl'] = 'y';
                        $globalData['cateNm'] = $arrData['cateNm'];
                    }
                    else{
                        $globalData['cateNmGlobalFl'] = 'n';
                        $globalData['cateNm'] = $v['cateNm'];
                    }
                    $arrBind = $this->db->get_binding(DBTableField::$funcName(), $globalData, 'insert');
                    $this->db->set_insert_db($this->cateTable."Global", $arrBind['param'], $arrBind['bind'], 'y');
                    unset($arrBind);
                }
            }
        }

        $this->setUseCntThemeConfig();

        return $arrData['cateCd'];
    }

    /**
     * 서브 카테고리 생성
     *
     * @param string $arrData 카테고리 정보
     * @return string 새로운 서브 카테고리 코드
     */
    public function saveInfoCategorySub($arrData)
    {
        // 서브 카테고리명 체크
        gd_isset($arrData['cateNm'], __('서브 카테고리'));

        // 서브 카테고리 생성시 부모 카테고리 코드
        if ($arrData['createType'] == 'inside') {
            $parentCateCd = $arrData['cateCd'];
        } else {
            $parentCateCd = substr($arrData['cateCd'], 0, -$this->cateLength);
        }

        $arrWhere = array();
        $cateLength = strlen($parentCateCd) + $this->cateLength;
        $arrWhere[] = " length(cateCd) = '" . $cateLength . "' ";
        $arrWhere[] = " cateCd LIKE '" . $parentCateCd . "%' ";

        // 새로운 카테고리 번호 생성
        list($data) = $this->getCategoryData(null, null, 'max(cateCd) as max', implode(' AND ', $arrWhere));
        $arrData['cateCd'] = $this->getCategoryCode($parentCateCd, $data['max'], $cateLength);

        // 새로운 카테고리의 순서 생성
        list($data) = $this->getCategoryData(null, null, 'max(cateSort) as max', implode(' AND ', $arrWhere));
        $arrData['cateSort'] = $data['max'] + 1;

        $displayConfig = \App::load('\\Component\\Display\\DisplayConfig');
        if ($this->cateType == 'goods') {
            $arrData['pcThemeCd'] = $displayConfig->getInfoThemeConfigCate('E', 'n')[0]['themeCd'];
            $arrData['mobileThemeCd'] = $displayConfig->getInfoThemeConfigCate('E', 'y')[0]['themeCd'];
        } else {
            $arrData['pcThemeCd'] = $displayConfig->getInfoThemeConfigCate('C', 'n')[0]['themeCd'];
            $arrData['mobileThemeCd'] = $displayConfig->getInfoThemeConfigCate('C', 'y')[0]['themeCd'];
        }

        $arrData['sortType'] ="g.regDt desc";
        $arrData['recomSortType'] ="g.regDt desc";
        if($this->gGlobal['isUse']) {

            $arrData['mallDisplay'] = implode(",",array_keys($this->gGlobal['useMallList'] ));

            $cateDepth = strlen($arrData['cateCd']) / $this->cateLength;
            if($cateDepth > 1) {
                for ($i = 1; $i < $cateDepth; $i++) {
                    $arrCateCd[] = substr($arrData['cateCd'], 0, ($i * $this->cateLength));
                }
                $strDisplaySubSQL = 'SELECT mallDisplay FROM ' . $this->cateTable .' WHERE cateCd IN (\'' . implode('\', \'', $arrCateCd) . '\') AND mallDisplaySubFl = \'y\'';
                $displaySubData = $this->db->query_fetch($strDisplaySubSQL,null,false);
                if($displaySubData['mallDisplay']) $arrData['mallDisplay'] = $displaySubData['mallDisplay'];
            }
        }

        $parentMallDisplaySubFl =
        $parentCateOnlyAdultSubFl =
        $parentCatePermissionSubFl = 'n';
        for ($i = 3; $i < strlen($arrData['cateCd']); $i = $i + 3) {
            $parentCode = substr($arrData['cateCd'], 0, $i);
            $parentData = $this->getDataCategory($parentCode, 'goods')['data'];

            if (empty($parentData) === false) {
                if ($parentMallDisplaySubFl == 'n' && $parentData['mallDisplaySubFl'] == 'y') {
                    $arrData['mallDisplay'] = $parentData['mallDisplay'];
                    $parentMallDisplaySubFl = 'y';
                }
                if ($parentCateOnlyAdultSubFl == 'n' && $parentData['cateOnlyAdultSubFl'] == 'y') {
                    $arrData['cateOnlyAdultFl'] = $parentData['cateOnlyAdultFl'];
                    $arrData['cateOnlyAdultDisplayFl'] = $parentData['cateOnlyAdultDisplayFl'];
                    $parentCateOnlyAdultSubFl = 'y';
                }
                if ($parentCatePermissionSubFl == 'n' && $parentData['catePermissionSubFl'] == 'y') {
                    $arrData['catePermission'] = $parentData['catePermission'];
                    $arrData['catePermissionGroup'] = implode(INT_DIVISION, array_keys($parentData['catePermissionGroup']));
                    $arrData['catePermissionDisplayFl'] = $parentData['catePermissionDisplayFl'];
                    $parentCatePermissionSubFl = 'y';
                }
            } else {
                break;
            }
            unset($parentData);
        }

        $funcName = $this->cateFuncNm;
        $arrBind = $this->db->get_binding(DBTableField::$funcName(), $arrData, 'insert');
        $this->db->set_insert_db($this->cateTable, $arrBind['param'], $arrBind['bind'], 'y');
        unset($arrBind);

        $this->setUseCntThemeConfig();

        return $arrData['cateCd'];
    }

    /**
     * 카테고리 상세정보 수정
     *
     * @param array $arrData 카테고리 정보
     * @return boolean true or false
     */
    public function saveInfoCategoryModify($arrData)
    {
        $filesValue = Request::files()->toArray();

        // 카테고리 코드 체크
        if (Validator::required(gd_isset($arrData['cateCd'])) === false) {
            throw new \Exception(__('카테고리 코드 필수 항목이 존재하지 않습니다.'),500);
        }

        // 카테고리명 체크
        if (Validator::required(gd_isset($arrData['cateNm'])) === false) {
            throw new \Exception('카테고리명 은(는) 필수 항목 입니다.',500);
        }

        //상단꾸미기 영역
        if ($arrData['cateHtml1SameFl'] =='y')  $arrData['cateHtml1Mobile']  = $arrData['cateHtml1'] ;
        if ($arrData['cateHtml2SameFl'] =='y')  $arrData['cateHtml2Mobile']  = $arrData['cateHtml2'] ;
        if ($arrData['cateHtml3SameFl'] =='y')  $arrData['cateHtml3Mobile']  = $arrData['cateHtml3'] ;

        //추천상품 수동진열
       if($arrData['recomSortAutoFl'] =='n' && gd_isset($arrData['goodsNoData'])) {
           if (is_array($arrData['goodsNoData'])) {
               $arrData['recomGoodsNo'] = implode(INT_DIVISION, $arrData['goodsNoData']);
           }

       }

        //추천상품 자동진열
        if($arrData['recomSortAutoFl'] =='y' && gd_isset($arrData['recomGoodsNo'])) {
            if (is_array($arrData['recomGoodsNo'])) {
                $arrData['recomGoodsNo'] = implode(INT_DIVISION, $arrData['recomGoodsNo']);
            }
        }

        // 회원등급
        if (gd_isset($arrData['memberGroupNo'])) {
            if (is_array($arrData['memberGroupNo'])) {
                $arrData['catePermissionGroup'] = implode(INT_DIVISION, $arrData['memberGroupNo']);
            }
        }

        if ($arrData['cateImgDel'] == 'y') {
            $this->storage()->delete($arrData['cateImg']);
            $arrData['cateImg'] = '';
        }

        if ($arrData['cateOverImgDel'] == 'y') {
            $this->storage()->delete($arrData['cateOverImg']);
            $arrData['cateOverImg'] = '';
        }

        if ($arrData['cateImgMobileDel'] == 'y') {
            $this->storage()->delete($arrData['cateImgMobile']);
            $arrData['cateImgMobile'] = '';
        }

        /*
        if ($this->cateType = 'goods') {
            $storageName = Storage::PATH_CODE_CATEGORY;
        } else {
            $storageName = Storage::PATH_CODE_BRAND;
        } */

        $storageName = Storage::PATH_CODE_CATEGORY;

        if ($filesValue) {
            foreach ($filesValue as $k => $v) {
                $fileDate = $v;
                if ($fileDate['name']) {
                    if (gd_file_uploadable($fileDate, 'image') === true) {  // 이미지 업로드
                        $imageExt = strrchr($v['name'], '.');
                        $arrData[$k] = $arrData['cateCd'] . '_' . $k .'_'.$this->cateType. $imageExt; // 이미지명 공백 제거
                        $targetImageFile = $arrData[$k];
                        $tmpImageFile = $v['tmp_name'];
                        Storage::disk($storageName)->upload($tmpImageFile, $targetImageFile);
                    } else {
                        throw new \Exception(__('이미지파일만 가능합니다.'));
                    }
                }

            }
        }

        if ($arrData['cateImgMobileFl'] == 'y') {
            $arrData['cateImgMobile']  = $arrData['cateImg'] ;
        } else {
            $arrData['cateImgMobileFl'] = "n";
        }


        if (gd_isset($arrData['cateDisplayFl']) == '') $arrData['cateDisplayFl'] = 'n';
        if (gd_isset($arrData['cateDisplayMobileFl']) == '') $arrData['cateDisplayMobileFl'] = 'n';
        if (gd_isset($arrData['recomDisplayFl']) == '') $arrData['recomDisplayFl'] = 'n';
        if (gd_isset($arrData['recomDisplayMobileFl']) == '') $arrData['recomDisplayMobileFl'] = 'n';
        if (gd_isset($arrData['recomSubFl']) == '') $arrData['recomSubFl'] = 'n';
        if (gd_isset($arrData['catePermissionSubFl']) == '') $arrData['catePermissionSubFl'] = 'n';
        if (gd_isset($arrData['catePermissionDisplayFl']) == '') $arrData['catePermissionDisplayFl'] = 'n';
        if (gd_isset($arrData['cateOnlyAdultSubFl']) == '') $arrData['cateOnlyAdultSubFl'] = 'n';
        if (gd_isset($arrData['cateOnlyAdultDisplayFl']) == '') $arrData['cateOnlyAdultDisplayFl'] = 'n';

        if($this->gGlobal['isUse']) {
            if(in_array('all',$arrData['mallDisplay']))  $arrData['mallDisplay'] = implode(",",array_keys($this->gGlobal['useMallList'] ));
            else  $arrData['mallDisplay'] = implode(",",$arrData['mallDisplay'] );
        }
        if (empty($arrData['mallDisplaySubFl']) === true) $arrData['mallDisplaySubFl'] = 'n'; //글로벌 노출상점 하위카테고리 동일 적용 사용 여부

        $seoTagData = $arrData['seoTag'];
        $seoTag = \App::load('\\Component\\Policy\\SeoTag');
        if (empty($arrData['seoTagFl']) === false) {
            $seoTagData['sno'] = $arrData['seoTagSno'];
            $pageGroup = $this->cateType == 'goods' ? 'category' : 'brand';
            $seoTagData['pageCode'] = $arrData['cateCd'];
            $arrData['seoTagSno'] = $seoTag->saveSeoTagEach($pageGroup,$seoTagData);
        }

        // 카테고리 정보 저장
        $funcName = $this->cateFuncNm;
        $arrBind = $this->db->get_binding(DBTableField::$funcName(), $arrData, 'update');

        $this->db->bind_param_push($arrBind['bind'], 's', $arrData['cateCd']);
        $this->db->set_update_db($this->cateTable, $arrBind['param'], 'cateCd = ?', $arrBind['bind']);
        unset($arrBind);

        if($this->gGlobal['isUse']) {
            $arrBind = [];
            //삭제후 신규 등록
            $this->db->bind_param_push($arrBind, 's', $arrData['cateCd']);
            $this->db->set_delete_db($this->cateTable."Global", 'cateCd = ?', $arrBind);
            unset($arrBind);

            $funcName = $this->cateFuncNm."Global";

            //노출상점설정
            if(in_array('all',$arrData['mallDisplay'])) { //전체인경우
                foreach($this->gGlobal['useMallList'] as $k => $v) {
                    if($v['standardFl'] =='n' && $arrData['global'][$v]) {
                        $globalData = $arrData['global'][$v];
                        $globalData['mallSno'] = $v['sno'];
                        $globalData['cateCd'] = $arrData['cateCd'];

                        $arrBind = $this->db->get_binding(DBTableField::$funcName(), $globalData, 'insert');
                        $this->db->set_insert_db($this->cateTable."Global", $arrBind['param'], $arrBind['bind'], 'y');
                        unset($arrBind);
                    }
                }
            } else {
                foreach($arrData['globalData'] as $k => $v) {
                    $globalData = $v;
                    $globalData['mallSno'] = $k;
                    $globalData['cateCd'] = $arrData['cateCd'];
                    if (gd_isset($v['cateNmGlobalFl'])) {
                        $globalData['cateNmGlobalFl'] = 'y';
                        $globalData['cateNm'] = $arrData['cateNm'];
                    }
                    else{
                        $globalData['cateNmGlobalFl'] = 'n';
                        $globalData['cateNm'] = $v['cateNm'];
                    }
                    $arrBind = $this->db->get_binding(DBTableField::$funcName(), $globalData, 'insert');
                    $this->db->set_insert_db($this->cateTable."Global", $arrBind['param'], $arrBind['bind'], 'y');
                    unset($arrBind);
                }
            }
        }

        if ($arrData['sortAutoFl'] != $arrData['sortAutoFlChk']) {

            // 카테고리 종류에 따른 설정
            if ($this->cateType == 'goods') {
                $dbTable = DB_GOODS_LINK_CATEGORY;
            } else {
                $dbTable = DB_GOODS_LINK_BRAND;
            }

            if ($arrData['sortAutoFl'] == 'y') {
                $strSQL = 'UPDATE ' . $dbTable . ' SET goodsSort = 0 where cateCd=\'' . $arrData['cateCd'] . '\'';
                $this->db->query($strSQL);
            } else {
                $strSetSQL = 'SET @newSort := 0;';
                $this->db->query($strSetSQL);

                $strSQL = 'UPDATE ' . $dbTable . ' SET goodsSort = ( @newSort := @newSort+1 ) WHERE cateCd=\'' . $arrData['cateCd'] . '\' ORDER BY goodsSort DESC;';
                $this->db->query($strSQL);
            }
        }

        if($arrData['catePermissionSubFl'] =='y') {
            $arrUpdate[] = "catePermission = '".$arrData['catePermission']."'";
            $arrUpdate[] = "catePermissionGroup = '".$arrData['catePermissionGroup']."'";
            $arrUpdate[] = "catePermissionDisplayFl = '".$arrData['catePermissionDisplayFl']."'";
            $this->db->set_update_db($this->cateTable, $arrUpdate,  "cateCd LIKE '".$arrData['cateCd']."%' ");
            unset($arrUpdate);
        }

        if($arrData['cateOnlyAdultSubFl'] =='y') {
            $arrUpdate[] = "cateOnlyAdultFl = '".$arrData['cateOnlyAdultFl']."'";
            $arrUpdate[] = "cateOnlyAdultDisplayFl = '".$arrData['cateOnlyAdultDisplayFl']."'";
            $this->db->set_update_db($this->cateTable, $arrUpdate,  "cateCd LIKE '".$arrData['cateCd']."%' ");
            unset($arrUpdate);
        }


        if($arrData['mallDisplaySubFl'] =='y') {
            $arrUpdate[] = "mallDisplay = '".$arrData['mallDisplay']."'";
            $this->db->set_update_db($this->cateTable, $arrUpdate,  "cateCd LIKE '".$arrData['cateCd']."%' ");
            unset($arrUpdate);
        }

        $this->setUseCntThemeConfig();

        return true;
    }

    /**
     * setUseCntThemeConfig
     *
     * @param $themeCd
     */
    public function setUseCntThemeConfig()
    {
        $strSQL = "UPDATE " .DB_DISPLAY_THEME_CONFIG . " SET useCnt = 0 WHERE themeCate = 'E'";
        $this->db->query($strSQL);

        $strSQL = 'SELECT COUNT(pcThemeCd) as count ,pcThemeCd FROM '.$this->cateTable.' GROUP BY pcThemeCd';
        $data = $this->db->query_fetch($strSQL);
        foreach($data as $k => $v) {
            if($v['pcThemeCd']) {
                $arrBind = [];
                $arrUpdate[] = 'useCnt ='.$v['count'];
                $this->db->bind_param_push($arrBind, 's', $v['pcThemeCd']);
                $this->db->set_update_db(DB_DISPLAY_THEME_CONFIG, $arrUpdate, 'themeCd = ?', $arrBind);
                unset($arrUpdate);
                unset($arrBind);
            }
        }

        $strSQL = 'SELECT COUNT(mobileThemeCd) as count ,mobileThemeCd FROM '.$this->cateTable.' GROUP BY mobileThemeCd';
        $data = $this->db->query_fetch($strSQL);
        foreach($data as $k => $v) {
            if($v['mobileThemeCd']) {
                $arrBind = [];
                $arrUpdate[] = 'useCnt ='.$v['count'];
                $this->db->bind_param_push($arrBind, 's', $v['mobileThemeCd']);
                $this->db->set_update_db(DB_DISPLAY_THEME_CONFIG, $arrUpdate, 'themeCd = ?', $arrBind);
                unset($arrUpdate);
                unset($arrBind);
            }
        }

        $strSQL = "UPDATE " .DB_DISPLAY_THEME_CONFIG . " SET useCnt = 0 WHERE themeCate = 'D'";
        $this->db->query($strSQL);

        $strSQL = 'SELECT COUNT(recomPcThemeCd) as count ,recomPcThemeCd FROM '.$this->cateTable.' GROUP BY recomPcThemeCd';
        $data = $this->db->query_fetch($strSQL);
        foreach($data as $k => $v) {
            if($v['recomPcThemeCd']) {
                $arrBind = [];
                $arrUpdate[] = 'useCnt ='.$v['count'];
                $this->db->bind_param_push($arrBind, 's', $v['recomPcThemeCd']);
                $this->db->set_update_db(DB_DISPLAY_THEME_CONFIG, $arrUpdate, 'themeCd = ?', $arrBind);
                unset($arrUpdate);
                unset($arrBind);
            }
        }

        $strSQL = 'SELECT COUNT(recomMobileThemeCd) as count ,recomMobileThemeCd FROM '.$this->cateTable.' GROUP BY recomMobileThemeCd';
        $data = $this->db->query_fetch($strSQL);
        foreach($data as $k => $v) {
            if($v['recomMobileThemeCd']) {
                $arrBind = [];
                $arrUpdate[] = 'useCnt ='.$v['count'];
                $this->db->bind_param_push($arrBind, 's', $v['recomMobileThemeCd']);
                $this->db->set_update_db(DB_DISPLAY_THEME_CONFIG, $arrUpdate, 'themeCd = ?', $arrBind);
                unset($arrUpdate);
                unset($arrBind);
            }
        }
    }


    /**
     * 새로운 카테고리 코드 생성(001 - ZZZ 로 확대 수정)
     *
     * @param string $parentCateCd 부모 카테고리 코드
     * @param string $cateCd 카테고리 코드 (기본 null)
     * @param string $cateLength 카테고리 길이
     * @return string 새로운 카테고리 코드
     */
    protected function getCategoryCode($parentCateCd = null, $cateCd = null, $cateLength)
    {
        if (!$cateCd) {
            $cateCd = $parentCateCd . str_repeat('0', $this->cateLength);
        }

        if((int)substr($cateCd, -$this->cateLength) == str_repeat('9', $this->cateLength)) {
            $lastCateCd = "AAA";
        } else {
            $lastCateCd = strtoupper(substr($cateCd, -$this->cateLength));
            $lastCateCd++;
        }

        return sprintf('%0' . $cateLength . 's', (substr(strtoupper($cateCd), 0, -$this->cateLength) . sprintf('%0' . $this->cateLength . 's',$lastCateCd)));
    }

    /**
     * 카테고리 테마의 등록 및 수정에 관련된 정보
     *
     * @author sunny
     * @param integer $themeSno 카테고리 테마 sno
     * @param string $themeId 카테고리 테마 ID
     * @return array 카테고리 테마 정보
     * @deprecated 2017-05-22 atomyang 미사용. 추후 삭제 예정
     */
    public function getDataCategoryTheme($themeSno = null, $themeId = null)
    {
        $fieldType = DBTableField::getFieldTypes('tableCategoryTheme');
        // --- 등록인 경우
        if (is_null($themeSno) && is_null($themeId)) {
            // 기본 정보
            $data['mode'] = $this->cateType . '_theme_register';
            $data['sno'] = null;

            // 기본값 설정
            DBTableField::setDefaultData('tableCategoryTheme', $data);

            // themeId 기본값 정의
            $this->arrBind = array();
            $strSQL = 'SELECT MAX(SUBSTRING(themeId,6)) as id FROM ' . DB_CATEGORY_THEME . ' WHERE cateType=? AND themeId REGEXP \'^theme[0-9].*$\'';
            $this->db->bind_param_push($this->arrBind, $fieldType['cateType'], $this->cateType);
            $cdata = $this->db->query_fetch($strSQL, $this->arrBind, false);
            if (empty($cdata['id']) === true) {
                $cdata['id'] = 1;
            } else {
                $cdata['id'] = intval($cdata['id']) + 1;
            }
            $data['themeId'] = sprintf('theme%03d', $cdata['id']);
            // --- 수정인 경우
        } else {
            // 테마 정보
            $tmp = $this->getInfoCategoryTheme($themeSno, $themeId);
            $data = $tmp[0];
            $data['mode'] = $this->cateType . '_theme_modify';

            // 기본값 설정
            DBTableField::setDefaultData('tableCategoryTheme', $data);
        }

        $checked = array();
        $checked['imageFl'][gd_isset($data['imageFl'])] = $checked['goodsNmFl'][gd_isset($data['goodsNmFl'])] = $checked['priceFl'][gd_isset($data['priceFl'])] = $checked['soldOutFl'][gd_isset($data['soldOutFl'])] = $checked['soldOutIconFl'][gd_isset($data['soldOutIconFl'])] = $checked['iconFl'][gd_isset($data['iconFl'])] = $checked['fixedPriceFl'][gd_isset($data['fixedPriceFl'])] = $checked['couponPriceFl'][gd_isset($data['couponPriceFl'])] = $checked['mileageFl'][gd_isset($data['mileageFl'])] = $checked['shortDescFl'][gd_isset($data['shortDescFl'])] = $checked['brandFl'][gd_isset($data['brandFl'])] = $checked['makerFl'][gd_isset($data['makerFl'])] = $checked['optionFl'][gd_isset($data['optionFl'])] = $checked['recomFl'][gd_isset($data['recomFl'])] = $checked['recomImageFl'][gd_isset($data['recomImageFl'])] = $checked['recomGoodsNmFl'][gd_isset($data['recomGoodsNmFl'])] = $checked['recomPriceFl'][gd_isset($data['recomPriceFl'])] = $checked['recomSoldOutFl'][gd_isset($data['recomSoldOutFl'])] = $checked['recomSoldOutIconFl'][gd_isset($data['recomSoldOutIconFl'])] = $checked['recomIconFl'][gd_isset($data['recomIconFl'])] = $checked['recomFixedPriceFl'][gd_isset($data['recomFixedPriceFl'])] = $checked['recomCouponPriceFl'][gd_isset($data['recomCouponPriceFl'])] = $checked['recomMileageFl'][gd_isset($data['recomMileageFl'])] = $checked['recomShortDescFl'][gd_isset($data['recomShortDescFl'])] = $checked['recomBrandFl'][gd_isset($data['recomBrandFl'])] = $checked['recomMakerFl'][gd_isset($data['recomMakerFl'])] = $checked['recomOptionFl'][gd_isset($data['recomOptionFl'])] = $checked['subcateCntFl'][gd_isset($data['subcateCntFl'])] = $checked['mobileSoldOutFl'][gd_isset($data['mobileSoldOutFl'])] = $checked['mobileSoldOutIconFl'][gd_isset($data['mobileSoldOutIconFl'])] = $checked['mobileIconFl'][gd_isset($data['mobileIconFl'])] = $checked['mobileImageFl'][gd_isset($data['mobileImageFl'])] = $checked['mobileGoodsNmFl'][gd_isset($data['mobileGoodsNmFl'])] = $checked['mobileShortDescFl'][gd_isset($data['mobileShortDescFl'])] = $checked['mobileBrandFl'][gd_isset($data['mobileBrandFl'])] = $checked['mobileMakerFl'][gd_isset($data['mobileMakerFl'])] = $checked['mobilePriceFl'][gd_isset($data['mobilePriceFl'])] = $checked['mobileFixedPriceFl'][gd_isset($data['mobileFixedPriceFl'])] = $checked['mobileMileageFl'][gd_isset($data['mobileMileageFl'])] = 'checked="checked"';

        $getData['data'] = $data;
        $getData['checked'] = $checked;

        return $getData;
    }

    /**
     * 카테고리 테마 정보
     *
     * @param string $themeSno 테마 번호
     * @param string $themeId 카테고리 테마 ID
     * @param string $arrThemeField 사용할 카테고리 필드
     * @return array 해당 카테고리 테마 정보
     * @deprecated 2017-05-22 atomyang 미사용. 추후 삭제 예정
     */
    public function getInfoCategoryTheme($themeSno = null, $themeId = null, $arrThemeField = null)
    {
        if (is_null($themeSno) === false) {
            $arrWhere[] = 'sno = ?';
            $this->db->bind_param_push($arrBind, 'i', $themeSno);
        }
        if (is_null($themeId) === false) {
            $arrWhere[] = 'themeId = ?';
            $this->db->bind_param_push($arrBind, 's', $themeId);
        }
        $arrWhere[] = 'cateType = ?';
        $this->db->bind_param_push($arrBind, 's', $this->cateType);
        $strWhere = 'WHERE ' . implode(' AND ', $arrWhere);

        $arrField = DBTableField::setTableField('tableCategoryTheme', $arrThemeField);
        $strSQL = 'SELECT sno, ' . implode(', ', $arrField) . ' FROM ' . DB_CATEGORY_THEME . ' ' . $strWhere;
        $getData = $this->db->query_fetch($strSQL, $arrBind);
        if (count($getData) > 0) {
            return gd_htmlspecialchars_stripslashes($getData);
        } else {
            return false;
        }
    }

    /**
     * 테마아이디 중복 확인
     *
     * @author sunny
     * @param $themeId 테마아이디
     * @return bool
     * @deprecated 2017-05-22 atomyang 미사용. 추후 삭제 예정
     */
    public function overlapThemeId($themeId)
    {
        // Validation
        if (Validator::userid($themeId, true) === false) {
            throw new \Exception(sprintf(self::TEXT_USERID_INVALID, '아이디'), 500);
        }

        $strSQL = 'SELECT themeId FROM ' . DB_CATEGORY_THEME . ' WHERE cateType = ? AND themeId=?';
        $this->db->bind_param_push($arrBind, 's', $this->cateType);
        $this->db->bind_param_push($arrBind, 's', $themeId);
        $data = $this->db->query_fetch($strSQL, $arrBind);
        if ($this->db->num_rows() > 0) {
            return true;
        }
    }

    /**
     * 카테고리 테마 정보 저장
     *
     * @param array $arrData 저장할 정보의 배열
     * @return int 기본키
     * @deprecated 2017-05-22 atomyang 미사용. 추후 삭제 예정
     */
    public function saveInfoCategoryTheme($arrData)
    {
        // 카테고리 테마명 체크
        if (Validator::required(gd_isset($arrData['themeNm'])) === false) {
            throw new \Exception(__('카테고리 테마 은(는) 필수 항목 입니다.'),500);
        }

        // 카테고리 테마 정보
        $getTheme = array();
        if (in_array($arrData['mode'], array('goods_theme_modify', 'brand_theme_modify')) === true) {
            $getTheme = $this->getInfoCategoryTheme($arrData['sno']);
        }

        // 카테고리 테마 정보 변형
        foreach ($arrData as $key => $val) {
            $tmpData[$key][] = $val;
        }

        // 카테고리 정보
        $compareTheme = $this->db->get_compare_array_data($getTheme, $tmpData, false);

        // 공통 키값
        $arrDataKey = array('sno' => $arrData['sno']);

        // 카테고리 테마 정보 저장
        $this->db->set_compare_process(DB_CATEGORY_THEME, $tmpData, $arrDataKey, $compareTheme);

        // 기본키
        if (in_array($arrData['mode'], array('goods_theme_register', 'brand_theme_register')) === true) {
            $arrData['sno'] = $this->db->insert_id();
        }
        return $arrData['sno'];
    }

    /**
     * 카테고리 테마 정보 삭제
     *
     * @param integer $dataSno 삭제할 레코드 sno
     * @deprecated 2017-05-22 atomyang 미사용. 추후 삭제 예정
     */
    public function setDeleteCategoryTheme($dataSno)
    {
        // 옵션 관리 정보 삭제
        $this->db->bind_param_push($arrBind, 'i', $dataSno);
        $this->db->set_delete_db(DB_CATEGORY_THEME, 'sno = ?', $arrBind);
    }

    /**
     * 관리자 카테고리 테마 리스트
     *
     * @author sunny
     * @return array 카테고리 테마 리스트 정보
     * @deprecated 2017-05-22 atomyang 미사용. 추후 삭제 예정
     */
    public function getAdminListCategoryTheme()
    {

        // --- 검색을 위한 bind 정보
        $fieldType = DBTableField::getFieldTypes('tableCategoryTheme');

        // --- 검색 설정
        $this->search['detailSearch'] = gd_isset($_GET['detailSearch']);
        $this->search['themeNm'] = gd_isset($_GET['themeNm']);
        $this->search['imageCd'] = gd_isset($_GET['imageCd']);
        $this->search['sortFl'] = gd_isset($_GET['sortFl']);

        // 카테고리 타입
        $this->arrWhere[] = 'cateType = ?';
        $this->db->bind_param_push($this->arrBind, $fieldType['cateType'], $this->cateType);

        // 키워드 검색
        if ($this->search['themeNm']) {
            $this->arrWhere[] = 'themeNm LIKE concat(\'%\',?,\'%\')';
            $this->db->bind_param_push($this->arrBind, $fieldType['themeNm'], $this->search['themeNm']);
        }
        // 사용 이미지 검색
        if ($this->search['imageCd']) {
            $this->arrWhere[] = 'imageCd = ?';
            $this->db->bind_param_push($this->arrBind, $fieldType['imageCd'], $this->search['imageCd']);
        }
        // 기본 출력 검색
        if ($this->search['sortFl']) {
            $this->arrWhere[] = 'sortFl = ?';
            $this->db->bind_param_push($this->arrBind, $fieldType['sortFl'], $this->search['sortFl']);
        }

        if (empty($this->arrBind)) {
            $this->arrBind = null;
        }

        // --- 정렬 설정
        $sort['fieldName'] = gd_isset($_GET['sort']['name']);
        $sort['sortMode'] = gd_isset($_GET['sort']['mode']);
        if (empty($sort['fieldName']) || empty($sort['sortMode'])) {
            $sort['fieldName'] = 'regDt';
            $sort['sortMode'] = 'desc';
        }

        // --- 페이지 기본설정
        gd_isset($_GET['page'], 1);
        gd_isset($_GET['page_num'], 10);

        $page = \App::load('\\Component\\Page\\Page', $_GET['page']);
        $page->page['list'] = $_GET['page_num']; // 페이지당 리스트 수
        $page->recode['amount'] = $this->db->table_status(DB_CATEGORY_THEME, 'Rows'); // 전체 레코드 수
        $page->setPage();
        $page->setUrl(\Request::getQueryString());

        // 현 페이지 결과
        $this->db->strField = "*";
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
        $this->db->strOrder = $sort['fieldName'] . ' ' . $sort['sortMode'];
        $this->db->strLimit = $page->recode['start'] . ',' . $_GET['page_num'];

        // 검색 카운트
        $strSQL = ' SELECT COUNT(*) AS cnt FROM ' . DB_CATEGORY_THEME . ' WHERE ' . $this->db->strWhere;
        $res = $this->db->query_fetch($strSQL, $this->arrBind, false);
        $page->recode['total'] = $res['cnt']; // 검색 레코드 수
        $page->setPage();
        $page->setUrl(\Request::getQueryString());

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_CATEGORY_THEME . ' ' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $this->arrBind);

        // 카테고리의 테마 사용수 체크
        $theme = array();
        $strSQL = 'SELECT cateThemeId, count(cateThemeId) as cnt FROM ' . $this->cateTable . ' GROUP BY cateThemeId';
        $result = $this->db->query($strSQL);
        while ($tmpData = $this->db->fetch($result)) {
            $theme[$tmpData['cateThemeId']] = $tmpData['cnt'];
        }

        // 각 데이터 배열화
        $getData['data'] = gd_htmlspecialchars_stripslashes(gd_isset($data));
        $getData['sort'] = $sort;
        $getData['search'] = gd_htmlspecialchars($this->search);
        $getData['checked'] = $this->checked;
        $getData['theme'] = $theme;

        return $getData;
    }

    /**
     * 상품 매핑 체크
     */
    public function getGoodsMappingResult()
    {
        $strSQL = "SELECT mappingMode FROM " . DB_LOG_GOODS_MAPPING . " WHERE cateType='" . $this->cateType . "' AND mappingFl = 'n' ORDER BY sno DESC LIMIT 0, 1";
        $result = $this->db->query($strSQL);
        list($data) = $this->db->fetch($result, 'row');

        return $data;
    }

    /**
     * 상품 매핑 처리
     */
    public function setGoodsMapping()
    {
        $arrField = DBTableField::setTableField('tableLogGoodsMapping');
        $strSQL = "SELECT sno, " . implode(', ', $arrField) . " FROM " . DB_LOG_GOODS_MAPPING . " WHERE cateType='" . $this->cateType . "' AND mappingFl = 'n' ORDER BY sno ASC";
        $getData = $this->db->query_fetch($strSQL);

        $linkTable = array('goods' => DB_GOODS_LINK_CATEGORY, 'brand' => DB_GOODS_LINK_BRAND);
        $goodsField = array('goods' => 'cateCd', 'brand' => 'brandCd');
        $arrGoodsNo = array();
        foreach ($getData as $key => $val) {
            $tableNm = $linkTable[$val['cateType']];
            $tableField = $goodsField[$val['cateType']];
            $arrMapping = explode(MARK_DIVISION, $val['mappingLog']);

            // 이동인 경우
            if ($val['mappingMode'] == 'm') {
                foreach ($arrMapping as $mKey => $mVal) {
                    $arrCateCd = explode(STR_DIVISION, $mVal);

                    // 변경된 상품
                    $strSQL = "SELECT goodsNo FROM " . $tableNm . " WHERE cateCd='" . $arrCateCd[0] . "' ORDER BY sno ASC";
                    $result = $this->db->query($strSQL);
                    while ($data = $this->db->fetch($result)) {
                        $arrGoodsNo[] = $data['goodsNo'];
                    }

                    // 링크 테이블 수정
                    $this->db->set_update_db($tableNm, 'cateCd = \'' . $arrCateCd[1] . '\'', 'cateCd = \'' . $arrCateCd[0] . '\'', null, false);

                    // 대표카테고리 수정
                    $this->db->setModDtUse(false);
                    $this->db->set_update_db(DB_GOODS, $tableField . ' = \'' . $arrCateCd[1] . '\'', $tableField . ' = \'' . $arrCateCd[0] . '\'', null, false);
                    if($this->goodsDivisionFl) {
                        $this->db->setModDtUse(false);
                        $this->db->set_update_db(DB_GOODS_SEARCH, $tableField . ' = \'' . $arrCateCd[1] . '\'', $tableField . ' = \'' . $arrCateCd[0] . '\'', null, false);
                    }
                }
            }

            // 삭제인 경우
            if ($val['mappingMode'] == 'd') {
                foreach ($arrMapping as $mKey => $mVal) {
                    // 변경된 상품
                    $strSQL = "SELECT goodsNo FROM " . $tableNm . " WHERE cateCd='" . $mVal . "' ORDER BY sno ASC";
                    $result = $this->db->query($strSQL);
                    while ($data = $this->db->fetch($result)) {
                        $arrGoodsNo[] = $data['goodsNo'];
                    }

                    // 링크 테이블 삭제
                    $this->db->set_delete_db($tableNm, 'cateCd = \'' . $mVal . '\'', null, false);

                    // 대표카테고리 수정
                    $this->db->setModDtUse(false);
                    $this->db->set_update_db(DB_GOODS, 'cateCd = \'\'', 'cateCd = \'' . $mVal . '\'', null, false);
                    if($this->goodsDivisionFl) {
                        $this->db->setModDtUse(false);
                        $this->db->set_update_db(DB_GOODS_SEARCH, 'cateCd = \'\'', 'cateCd = \'' . $mVal . '\'', null, false);
                    }
                }
            }

            // 매핑 테이블 수정
            $this->db->set_update_db(DB_LOG_GOODS_MAPPING, 'mappingFl = \'y\'', 'sno = \'' . $val['sno'] . '\'', null, false);
        }
        unset($getData, $arrMapping);

        // 중복 상품번호 정리
        $arrGoodsNo = array_unique($arrGoodsNo);

        // 상품별 처리
        if (empty($arrGoodsNo) === false) {

            // --- 모듈 설정
            $goods = \App::load('\\Component\\Goods\\GoodsAdmin');

            foreach ($arrGoodsNo as $goodsNo) {

                // 공통 키값
                $arrDataKey = array('goodsNo' => $goodsNo);

                // --- 카테고리인 경우
                if ($this->cateType == 'goods') {
                    // 기존 카테고리 정보
                    $getLink = $goods->getGoodsLinkCategory($goodsNo);
                    $setData = array();
                    if (empty($getLink) === false) {
                        foreach ($getLink as $key => $val) {
                            $setData['sno'][$key] = $val['sno'];
                            $setData['cateCd'][$key] = $val['cateCd'];
                            $setData['cateLinkFl'][$key] = $val['cateLinkFl'];
                            $setData['goodsSort'][$key] = $val['goodsSort'];
                        }
                    } else {
                        continue;
                    }

                    // 노출 카테고리 / 부모 카테고리 설정
                    if (empty($setData) === false) {
                        $setData = $goods->getGoodsCategoyCheck($setData, $goodsNo);
                    } else {
                        continue;
                    }

                    // 카테고리 비교
                    $compareLink = $this->db->get_compare_array_data($getLink, $setData);

                    // 카테고리 정보 저장
                    $this->db->set_compare_process(DB_GOODS_LINK_CATEGORY, $setData, $arrDataKey, $compareLink);

                    unset($getLink, $setData);
                } // --- 브랜드인 경우
                else if ($this->cateType == 'brand') {

                    // 기존 브랜드 정보
                    $getBrand = $goods->getGoodsLinkBrand($goodsNo);
                    $setBrandCd = '';
                    $setData = array();
                    if (empty($getBrand) === false) {
                        foreach ($getBrand as $key => $val) {
                            if ($val['cateLinkFl'] == 'y') {
                                $setBrandCd = $val['cateCd'];
                            }
                        }
                    } else {
                        continue;
                    }

                    // 브랜드 체크
                    $setData = $goods->getGoodsBrandCheck($setBrandCd, $getBrand, $goodsNo);

                    // 브랜드 정보
                    $compareBrand = $this->db->get_compare_array_data($getBrand, $setData);

                    // 브랜드 정보 저장
                    $this->db->set_compare_process(DB_GOODS_LINK_BRAND, $setData, $arrDataKey, $compareBrand);

                    unset($getBrand, $setData);
                }
            }
        }
    }


    /**
     * 검색 리스트
     *
     * @return array 공급사  리스트 정보
     */
    public function getAdminSeachCategory($mode = null, $pageNum = 5)
    {

        // 검색을 위한 bind 정보
        $fieldType = DBTableField::getFieldTypes($this->cateFuncNm);

        $getValue = Request::get()->toArray();

        if ($mode == 'layer') {
            // --- 페이지 기본설정
            if (gd_isset($getValue['pagelink'])) {
                $getValue['page'] = (int)str_replace('page=', '', preg_replace('/^{page=[0-9]+}/', '', gd_isset($getValue['pagelink'])));
            } else {
                $getValue['page'] = 1;
            }
            gd_isset($getValue['pageNum'], $pageNum);
        } else {
            // --- 페이지 기본설정
            gd_isset($getValue['page'], 1);
            gd_isset($getValue['pageNum'], 10);
        }

        // --- 검색 설정
        $this->search['cateNm'] = gd_isset($getValue['cateNm']);

        if ($this->cateType == 'goods') {
            $tmpName = 'cateGoods';
        } else {
            $tmpName = $this->cateType;
        }

        $this->search[$tmpName] = ArrayUtils::last(gd_isset($getValue[$tmpName]));

        // 테마명 검색
        if ($this->search['cateNm']) {
            $this->arrWhere[] = 'cateNm LIKE concat(\'%\',?,\'%\')';
            $this->db->bind_param_push($this->arrBind, $fieldType['cateNm'], $this->search['cateNm']);
        }

        //카테고리가 있는 경우
        if ($this->search[$tmpName]) {
            $this->arrWhere[] = 'cateCd LIKE concat(?,\'%\')';
            $this->db->bind_param_push($this->arrBind, $fieldType['cateCd'], $this->search[$tmpName]);
        }

        if (empty($this->arrBind)) {
            $this->arrBind = null;
        }

        $this->arrWhere[] = 'divisionFl = \'n\'';

        // --- 정렬 설정
        $sort['fieldName'] = gd_isset($getValue['sort']['name']);
        $sort['sortMode'] = gd_isset($getValue['sort']['mode']);
        if (empty($sort['fieldName']) || empty($sort['sortMode'])) {
            $sort['fieldName'] = 'regDt';
            $sort['sortMode'] = 'desc';
        }


        $page = \App::load('\\Component\\Page\\Page', $getValue['page']);
        $page->page['list'] = $getValue['pageNum']; // 페이지당 리스트 수
        $page->recode['amount'] = $this->db->table_status($this->cateTable, 'Rows'); // 전체 레코드 수
        $page->setPage();
        $page->setUrl(\Request::getQueryString());

        // 현 페이지 결과
        $this->db->strField = "*";
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
        $this->db->strOrder = $sort['fieldName'] . ' ' . $sort['sortMode'];
        if ($getValue['noLimit'] != 'y') {
            $this->db->strLimit = $page->recode['start'] . ',' . $getValue['pageNum'];
        }

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . $this->cateTable . ' ' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $this->arrBind);

        /* 검색 count 쿼리 */
        $totalCountSQL =  ' SELECT COUNT(c.cateCd) AS totalCnt FROM ' . $this->cateTable . ' as c   WHERE '.implode(' AND ', $this->arrWhere);
        $page->recode['total'] = $this->db->query_fetch($totalCountSQL, $this->arrBind,false)['totalCnt'];
        $page->setPage();


        // 각 데이터 배열화
        $getData['data'] = gd_htmlspecialchars_stripslashes(gd_isset($data));
        $getData['sort'] = $sort;
        $getData['search'] = gd_htmlspecialchars($this->search);
        $getData['checked'] = $this->checked;
        $getData['useMallList'] = array_combine(array_column($this->gGlobal['useMallList'], 'sno'), $this->gGlobal['useMallList']);

        return $getData;
    }

    /**
     * 브랜드 테마 정보
     *
     * @author sunny
     * @return array
     */
    function getBrandAdminTheme()
    {
        $cate = \App::load('\\Component\\Category\\CategoryAdmin', 'brand');
        $tmpTheme = $cate->getInfoCategoryTheme(null, null, ['themeId', 'themeNm']);
        $themes = [];
        if (empty($tmpTheme) === false) {
            foreach ($tmpTheme as $val) {
                $themes[$val['themeId']] = $val['themeNm'];
            }
        }

        return $themes;
    }

    /**
     * 카테고리 테마 정보
     *
     * @author sunny
     * @return array
     */
    function getCategoryAdminTheme()
    {
        $cate = \App::load('\\Component\\Category\\CategoryAdmin', 'goods');
        $tmpTheme = $cate->getInfoCategoryTheme(null, null, ['themeId', 'themeNm']);
        $themes = [];
        if (empty($tmpTheme) === false) {
            foreach ($tmpTheme as $val) {
                $themes[$val['themeId']] = $val['themeNm'];
            }
        }

        return $themes;
    }

    /**
     * 브랜드 or 카테고리 현재위치 셀렉트박스
     *
     * @author agni
     * @param string $default : 디폴트 노출 (y : 노출 / n : 미노출)
     * @return array
     */
    public function getCategoryListSelectBox($default = 'n')
    {
        // 1차 카테고리
        if ($this->cateDepth >= 1) {
            $getData1 = $this->getCategoryData(null, null,'cateCd, cateNm, divisionFl', 'length(cateCd) = \'3\'', 'cateSort ASC');
            $cCount = 0;
            foreach ($getData1 as $cKey => $cVal) {
                $categoryNm = $this->getCategoryPosition($cVal['cateCd']);

                // 그룹(구분) 브랜드 체크 disabled
                if ($cVal['divisionFl'] == 'y') {
                    $brandKey = '__disable' . $cCount;
                    $brandName = '-----' . $categoryNm . '-----';
                    $cCount ++;
                } else {
                    $brandKey = $cVal['cateCd'];
                    $brandName = $categoryNm;
                }
                $setData[substr($cVal['cateCd'], 0, 3)][$brandKey] = $brandName;
            }
            unset($getData1);
        }

        // 2차 카테고리
        if ($this->cateDepth >= 2) {
            $getData2 = $this->getCategoryData(null, null,'cateCd, cateNm', 'length(cateCd) = \'6\'', 'cateSort ASC');
            foreach ($getData2 as $cKey => $cVal) {
                $categoryNm = $this->getCategoryPosition($cVal['cateCd']);
                $setData[substr($cVal['cateCd'], 0, 3)][$cVal['cateCd']] = $categoryNm;
            }
            unset($getData2);
        }

        // 3차 카테고리
        if ($this->cateDepth >= 3) {
            $getData3 = $this->getCategoryData(null, null,'cateCd, cateNm', 'length(cateCd) = \'9\'', 'cateSort ASC');
            foreach ($getData3 as $cKey => $cVal) {
                $categoryNm = $this->getCategoryPosition($cVal['cateCd']);
                $setData[substr($cVal['cateCd'], 0, 3)][$cVal['cateCd']] = $categoryNm;
            }
            unset($getData3);
        }

        // 4차 카테고리
        if ($this->cateDepth == 4) {
            $getData4 = $this->getCategoryData(null, null,'cateCd, cateNm', 'length(cateCd) = \'12\'', 'cateSort ASC');
            foreach ($getData4 as $cKey => $cVal) {
                $categoryNm = $this->getCategoryPosition($cVal['cateCd']);
                $setData[substr($cVal['cateCd'], 0, 3)][$cVal['cateCd']] = $categoryNm;
            }
            unset($getData4);
        }

        // 셀렉트박스 디폴트 옵션 노출
        if ($default == 'y') {
            $realSetData['data']['allBrand'] = '전체(선택 브랜드 제외)';
            $realSetData['data']['noBrand'] = '브랜드 미지정 상품';
        }

        // 브랜드 셀렉트박스 데이터 생성
        foreach ($setData AS $cateCd => $cateInfo) {
            foreach ($cateInfo as $cKey => $cVal) {
                $realSetData['data'][$cKey] = $cVal;
            }
        }
        unset($setData);

        // 브랜드 총 카운트
        $realSetData['cnt'] = count($realSetData['data']);

        return $realSetData;
    }

}
