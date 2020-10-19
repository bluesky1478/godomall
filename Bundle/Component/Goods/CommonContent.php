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
use Framework\Debug\Exception\AlertBackException;
use Framework\Utility\DateTimeUtils;
use Globals;
use League\Flysystem\Exception;
use Request;

/**
 * 상품상세 공통정보 관리
 * @author Bag YJ <kookoo135@godo.co.kr>
 */
class CommonContent
{
    // 디비 접속
    protected $db;
    protected $targetFl = [
        '' => '전체',
        'all' => '전체 상품',
        'goods' => '상품',
        'category' => '카테고리',
        'brand' => '브랜드',
    ];
    protected $useFl = [
        'y' => '노출함',
        'n' => '노출안함',
    ];

    /**
     * 생성자
     *
     */
    public function __construct()
    {
        if (gd_is_plus_shop(PLUSSHOP_CODE_COMMONCONTENT) === false) {
            throw new AlertBackException('[플러스샵] 미설치 또는 미사용 상태입니다. 설치 완료 및 사용 설정 후 플러스샵 앱을 사용할 수 있습니다.');
        }

        if (!is_object($this->db)) {
            $this->db = \App::load('DB');
        }
    }

    public function getObject($obj)
    {
        return $this->$obj;
    }
    
    public function getTargetFl($obj = 'targetFl', $head = '특정')
    {
        $targetFlArr = $this->getObject($obj);
        if (gd_is_plus_shop(PLUSSHOP_CODE_SCM) === true) {
            $targetFlArr['scm'] = '공급사';
        }
        foreach ($targetFlArr as $key => &$value) {
            if (in_array($key, ['', 'all'])) continue;
            $value = $head . ' ' . $value;
        }


        return $targetFlArr;
    }

    /**
     * 상품상세 전체공통정보
     */
    public function getData($sno = null, $arrInclude = [], $returnArray = true, $getValue = [], $cdInfoFl = false)
    {
        $arrField = DBTableField::setTableField('tableCommonContent',$arrInclude);
        if (empty($getValue) === true) $getValue = Request::get()->all();
        $arrBind = $arrWhere = [];
        $search = $getValue;

        // --- 페이지 기본설정
        gd_isset($getValue['page'], 1);
        gd_isset($getValue['pageNum'], 10);

        $page = \App::load('\\Component\\Page\\Page', $getValue['page']);
        $page->page['list'] = $getValue['pageNum']; // 페이지당 리스트 수

        $strSQL = ' SELECT COUNT(*) AS cnt FROM ' . DB_COMMON_CONTENT;
        $res = $this->db->query_fetch($strSQL, null, false);
        $page->recode['amount'] = $res['cnt']; // 전체 레코드 수
        $page->setPage();
        $page->setUrl(\Request::getQueryString());

        //기간검색
        if (empty($search['searchDateFl']) === true) {
            $search['searchDateFl'] = 'regDt';
        }
        if (empty(isset($search['searchPeriod'])) === true) {
            $search['searchPeriod'] = -1;
        }

        // --- 정렬 설정
        $sort = gd_isset($getValue['sort']);
        if (empty($sort)) {
            $sort = 'regDt desc';
        }

        if (empty($sno) === false) {
            $arrWhere[] = '`sno` = ?';
            $this->db->bind_param_push($arrBind, 'i', $sno);
        } else {
            if (empty($search['sno']) === false) {
                $arrWhere[] = '`sno` IN ('.@implode(',', array_fill(0, count($search['sno']), '?')) .')';
                foreach ($search['sno'] as $val) {
                    $this->db->bind_param_push($arrBind, 'i', $val);
                }
            } else {
                if (empty($search['title']) === false) {
                    $arrWhere[] = '`commonTitle` LIKE CONCAT(\'%\',?,\'%\')';
                    $this->db->bind_param_push($arrBind, 's', $search['title']);
                }
                if (empty($search['searchDate'][0]) === false) {
                    $arrWhere[] = '`' . $search['searchDateFl'] . '` >= ?';
                    $this->db->bind_param_push($arrBind, 's', DateTimeUtils::dateFormat('Y-m-d 00:00', $search['searchDate'][0]));
                }
                if (empty($search['searchDate'][1]) === false) {
                    $arrWhere[] = '`' . $search['searchDateFl'] . '` <= ?';
                    $this->db->bind_param_push($arrBind, 's', DateTimeUtils::dateFormat('Y-m-d 23:59', $search['searchDate'][1]));
                }
                if (empty($search['dateFl']) === false) {
                    $arrWhere[] = '`commonStatusFl` = ?';
                    $this->db->bind_param_push($arrBind, 's', $search['dateFl']);
                }
                if (empty($search['dateFl']) === false) {
                    $arrWhere[] = '`commonStatusFl` = ?';
                    $this->db->bind_param_push($arrBind, 's', $search['dateFl']);
                }
                if (empty($search['stateFl']) === false) {
                    switch ($search['stateFl']) {
                        case 's':
                            $arrWhere[] = '`commonStatusFl` = ? AND `commonStartDt` > ?';
                            $this->db->bind_param_push($arrBind, 's', 'y');
                            $this->db->bind_param_push($arrBind, 's', DateTimeUtils::dateFormat('Y-m-d H:i', 'now'));
                            break;
                        case 'i':
                            $arrWhere[] = '(`commonStatusFl` = ? OR (`commonStatusFl` = ? AND (? BETWEEN `commonStartDt` AND `commonEndDt`)))';
                            $this->db->bind_param_push($arrBind, 's', 'n');
                            $this->db->bind_param_push($arrBind, 's', 'y');
                            $this->db->bind_param_push($arrBind, 's', DateTimeUtils::dateFormat('Y-m-d H:i', 'now'));
                            break;
                        case 'e':
                            $arrWhere[] = '`commonStatusFl` = ? AND `commonEndDt` < ?';
                            $this->db->bind_param_push($arrBind, 's', 'y');
                            $this->db->bind_param_push($arrBind, 's', DateTimeUtils::dateFormat('Y-m-d H:i', 'now'));
                            break;
                    }
                }
                if (empty($search['useFl']) === false) {
                    if ($search['useFl'] == 'y') {
                        $arrWhere[] = '(`commonStatusFl` = ? OR (`commonStatusFl` = ? AND (? BETWEEN `commonStartDt` AND `commonEndDt`)))';
                        $arrWhere[] = '`commonUseFl` = ?';
                        $this->db->bind_param_push($arrBind, 's', 'n');
                        $this->db->bind_param_push($arrBind, 's', 'y');
                        $this->db->bind_param_push($arrBind, 's', DateTimeUtils::dateFormat('Y-m-d H:i', 'now'));
                    } else {
                        $arrWhere[] = '((`commonStatusFl` = ? AND (`commonStartDt` > ? OR `commonEndDt` < ?)) OR `commonUseFl` = ?)';
                        $this->db->bind_param_push($arrBind, 's', 'y');
                        $this->db->bind_param_push($arrBind, 's', DateTimeUtils::dateFormat('Y-m-d H:i', 'now'));
                        $this->db->bind_param_push($arrBind, 's', DateTimeUtils::dateFormat('Y-m-d H:i', 'now'));
                    }
                    $this->db->bind_param_push($arrBind, 's', $search['useFl']);
                }
                if (empty($search['targetFl']) === false) {
                    $arrWhere[] = '`commonTargetFl` = ?';
                    $this->db->bind_param_push($arrBind, 's', $search['targetFl']);
                }
            }
        }

        $this->db->strField = implode(', ', $arrField);
        $this->db->strWhere = implode(' AND ', gd_isset($arrWhere));
        $this->db->strOrder = $sort;
        if ($getValue['pageNum'] > 0) {
            $this->db->strLimit = $page->recode['start'] . ',' . $getValue['pageNum'];
        }

        // 검색 카운트
        $strSQL = ' SELECT COUNT(*) AS cnt FROM ' . DB_COMMON_CONTENT;
        if($this->db->strWhere){
            $strSQL .= ' WHERE ' . $this->db->strWhere;
        }
        $res = $this->db->query_fetch($strSQL, $arrBind, false);
        $page->recode['total'] = $res['cnt']; // 검색 레코드 수
        $page->setPage();
        $page->setUrl(\Request::getQueryString());

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_COMMON_CONTENT . implode(' ', $query);
        $data['data'] = $this->db->query_fetch($strSQL, $arrBind, $returnArray);

        if (empty($sno) === false || $cdInfoFl === true) {
            foreach ($data['data'] as $key => $value) {
                if (empty($value['commonCd']) === false) {
                    if ($value['commonTargetFl'] == 'goods') {
                        $data['data'][$key]['commonCd'] = $this->viewGoodsData($value['commonCd']);
                    }
                    if ($value['commonTargetFl'] == 'category') {
                        $data['data'][$key]['commonCd'] = $this->viewCategoryData($value['commonCd']);
                    }
                    if ($value['commonTargetFl'] == 'brand') {
                        $data['data'][$key]['commonCd'] = $this->viewCategoryData($value['commonCd'], 'brand');
                    }
                    if ($value['commonTargetFl'] == 'scm') {
                        $data['data'][$key]['commonCd'] = $this->viewScmData($value['commonCd']);
                    }
                }
                // 예외 조건
                $data['data'][$key]['commonExGoods'] = $this->viewGoodsData($value['commonExGoods']);
                $data['data'][$key]['commonExCategory'] = $this->viewCategoryData($value['commonExCategory']);
                $data['data'][$key]['commonExBrand'] = $this->viewCategoryData($value['commonExBrand'], 'brand');
                $data['data'][$key]['commonExScm'] = $this->viewScmData($value['commonExScm']);
            }
        }

        $selected['searchDateFl'][$search['searchDateFl']] = 'selected = "selected"';

        $checked['dateFl'][$search['dateFl']] = 'checked = "checked"';
        $checked['stateFl'][$search['stateFl']] = 'checked = "checked"';
        $checked['useFl'][$search['useFl']] = 'checked = "checked"';
        $checked['targetFl'][$search['targetFl']] = 'checked = "checked"';

        $search['sortList'] = [
            'regDt desc'     => '등록일 ↑',
            'regDt asc'      => '등록일 ↓',
            'commonTitle desc'    => '공통정보 제목 ↑',
            'commonTitle asc'     => '공통정보 제목 ↓',
        ];
        $data['search'] = $search;
        $data['selected'] = $selected;
        $data['checked'] = $checked;

        return $data;
    }

    public function getDataExcel($getValue = [])
    {
        $getData = [];
        $arrInclude = ['commonTitle', 'commonStatusFl', 'commonStartDt', 'commonEndDt', 'commonUseFl', 'commonTargetFl', 'commonCd', 'commonExGoods', 'commonExCategory', 'commonExBrand', 'commonExScm', 'regDt', 'modDt'];
        if (empty($getValue)) $getValue = ['searchPeriod' => '-1', 'pageNum' => 0];
        $data = $this->getData(null, $arrInclude, true, $getValue, true);
        $targetFl = $this->getTargetFl();

        foreach ($data['data'] as $key => $value) {
            $commonCd = $commonEx = $commonExCd = [];

            $getData[$key]['commonTitle'] = $value['commonTitle'];
            if ($value['commonStatusFl'] == 'y') {
                $getData[$key]['commonDt'] = DateTimeUtils::dateFormat('Y-m-d H:i', $value['commonStartDt']) . '~' . DateTimeUtils::dateFormat('Y-m-d H:i', $value['commonEndDt']);
            } else {
                $getData[$key]['commonDt'] = '제한없음';
            }
            $getData[$key]['commonStatusFl'] = '진행중';
            if ($value['commonStatusFl'] == 'y') {
                if ($value['commonStartDt'] > date('Y-m-d H:i')) {
                    $value['commonUseFl'] = 'n';
                    $getData[$key]['commonStatusFl'] = '대기';
                } elseif ($value['commonEndDt'] < date('Y-m-d H:i')) {
                    $value['commonUseFl'] = 'n';
                    $getData[$key]['commonStatusFl'] = '종료';
                }
            }
            $getData[$key]['commonUseFl'] = $this->useFl[$value['commonUseFl']];
            $getData[$key]['commonTargetFl'] = $targetFl[$value['commonTargetFl']];

            if ($value['commonTargetFl'] == 'goods') {
                foreach ($value['commonCd'] as $val) {
                    $commonCd[] = $val['goodsNm'];
                }
                $getData[$key]['commonCd'] = @implode('<br />', $commonCd);
            } elseif ($value['commonTargetFl'] == 'scm') {
                foreach ($value['commonCd'] as $val) {
                    $commonCd[] = $val['companyNm'];
                }
                $getData[$key]['commonCd'] = @implode('<br />', $commonCd);
            } else {
                $getData[$key]['commonCd'] = @implode('<br />', $value['commonCd']['name']);
            }

            if (empty($value['commonExGoods']) === false) {
                $commonEx[] = '상품';
                $commonExCd[] = '예외상품';
                foreach ($value['commonExGoods'] as $val) {
                    $commonExCd[] = $val['goodsNm'];
                }
            }
            if (empty($value['commonExCategory']) === false) {
                $commonEx[] = '카테고리';
                $commonExCd[] = '예외카테고리';
                $commonExCd[] = @implode('<br />', $value['commonExCategory']['name']);
            }
            if (empty($value['commonExBrand']) === false) {
                $commonEx[] = '브랜드';
                $commonExCd[] = '예외브랜드';
                $commonExCd[] =  @implode('<br />', $value['commonExBrand']['name']);;
            }
            if (empty($value['commonExScm']) === false) {
                $commonEx[] = '공급사';
                $commonExCd[] = '예외공급사';
                foreach ($value['commonExScm'] as $val) {
                    $commonExCd[] = $val['companyNm'];
                }
            }
            $getData[$key]['commonEx'] = @implode('<br />', $commonEx);
            $getData[$key]['commonExCd'] = @implode('<br />', $commonExCd);
            unset($commonCd);unset($commonEx);unset($commonExCd);
            $getData[$key]['regDt'] = $value['regDt'];
            $getData[$key]['modDt'] = $value['modDt'];
        }

        return $getData;
    }

    public function save($param)
    {
        $arrData = $this->dataClean($param);

        if (empty($arrData['sno']) === true) {
            $arrBind = $this->db->get_binding(DBTableField::tableCommonContent(), $arrData, 'insert');
            $this->db->set_insert_db(DB_COMMON_CONTENT, $arrBind['param'], $arrBind['bind'], 'y');

            $commonSno = $this->db->insert_id();;
        } else {
            $arrBind = $this->db->get_binding(DBTableField::tableCommonContent(), $arrData, 'update');
            $this->db->bind_param_push($arrBind['bind'], 'i', $arrData['sno']);
            $this->db->set_update_db(DB_COMMON_CONTENT, $arrBind['param'], 'sno = ?', $arrBind['bind']);

            $commonSno = $param['sno'];
        }
        
        return $commonSno;
    }

    private function dataClean($param)
    {
        $data = [];

        $getTargetFl = $this->getTargetFl();
        unset($getTargetFl['']);unset($getTargetFl['all']);
        foreach ($getTargetFl as $key => &$value) {
            if ($param['commonTargetFl'] == $key) {
                $param['commonCd'] = $param['common' . ucwords($key)];
            }
            if (in_array($key, $param['commonExTargetFl']) === false || empty($param['commonEx' . ucwords($key)]) === true || empty($param['commonExTargetFl']) === true) {
                $param['commonEx' . ucwords($key)] = '';
            }
        }

        foreach ($param as $key => $value) {
            if (in_array($key, ['commonCd', 'commonExGoods', 'commonExCategory', 'commonExBrand', 'commonExScm'])) $data[$key] = @implode(INT_DIVISION, $value);
            else $data[$key] = $value;
        }

        return $data;
    }

    public function delete($param)
    {
        if (empty($param['sno']) === true) {
            throw new Exception('선택된 공통정보가 없습니다.');
        }

        foreach ($param['sno'] as $value) {
            $arrBind = [];
            $this->db->bind_param_push($arrBind, 's', $value);
            $this->db->set_delete_db(DB_COMMON_CONTENT, 'sno = ?', $arrBind);
        }
        unset($arrBind);
    }

    public function viewGoodsData($getData)
    {
        if (empty($getData)) {
            return false;
        }

        $goods = \App::load('\\Component\\Goods\\GoodsAdmin');

        return $goods->getGoodsDataDisplay($getData);
    }

    public function viewCategoryData($getData, $cateMode = 'category')
    {
        if (empty($getData)) {
            return false;
        }
        if ($cateMode == 'category') {
            $cate = \App::load('\\Component\\Category\\CategoryAdmin');
        } else {
            $cate = \App::load('\\Component\\Category\\BrandAdmin');
        }
        $tmp['code'] = explode(INT_DIVISION, $getData);
        foreach ($tmp['code'] as $val) {
            $tmp['name'][] = gd_htmlspecialchars_decode($cate->getCategoryPosition($val));
        }

        return $tmp;
    }

    public function viewScmData($getData)
    {
        if (empty($getData)) {
            return false;
        }
        $scm = \App::load('\\Component\\Scm\\ScmAdmin');
        return $scm->getScmSelectList($getData);
    }

    public function getCommonContent($goodsNo, $scmNo)
    {
        $arrField = DBTableField::setTableField('tableCommonContent',['commonTargetFl', 'commonCd', 'commonExGoods', 'commonExCategory', 'commonExBrand', 'commonExScm', 'commonHtmlContentSameFl', 'commonHtmlContent', 'commonHtmlContentMobile']);
        $arrBind = $arrWhere = $retContent = [];

        $arrWhere[] = '(`commonStatusFl` = ? OR (`commonStatusFl` = ? AND (? BETWEEN `commonStartDt` AND `commonEndDt`)))';
        $arrWhere[] = '`commonUseFl` = ?';
        $this->db->bind_param_push($arrBind, 's', 'n');
        $this->db->bind_param_push($arrBind, 's', 'y');
        $this->db->bind_param_push($arrBind, 's', DateTimeUtils::dateFormat('Y-m-d H:i', 'now'));
        $this->db->bind_param_push($arrBind, 's', 'y');

        $this->db->strField = implode(', ', $arrField);
        $this->db->strWhere = implode(' AND ', gd_isset($arrWhere));
        $this->db->strOrder = 'sno ASC';

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_COMMON_CONTENT . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $arrBind, true);

        if (empty($data) === false) {
            $cate = \App::load('\\Component\\Category\\Category');

            $cateCd = $cate->getCateCd($goodsNo);
            $brandCd = $cate->getCateCd($goodsNo, 'brand');

            foreach ($data as $val) {
                $val = gd_htmlspecialchars_stripslashes($val);
                $viewCommonContent = true;

                $commonExGoods = explode(INT_DIVISION, $val['commonExGoods']);
                $commonExCategory = explode(INT_DIVISION, $val['commonExCategory']);
                $commonExBrand = explode(INT_DIVISION, $val['commonExBrand']);
                $commonExScm = explode(INT_DIVISION, $val['commonExScm']);

                // 예외
                if (in_array($goodsNo, $commonExGoods) === true) {
                    $viewCommonContent = false;
                }
                if (in_array($scmNo, $commonExScm) === true) {
                    $viewCommonContent = false;
                }
                if (empty($commonExCategory) === false && empty($cateCd) === false) {
                    foreach ($commonExCategory as $value) {
                        if (in_array($value, $cateCd)) {
                            $viewCommonContent = false;
                            break;
                        }
                    }
                }
                if (empty($commonExBrand) === false && empty($brandCd) === false) {
                    foreach ($commonExBrand as $value) {
                        if (in_array($value, $brandCd)) {
                            $viewCommonContent = false;
                            break;
                        }
                    }
                }

                //특정
                if ($viewCommonContent === true) {
                    if ($val['commonTargetFl'] != 'all') {
                        $viewCommonContent = false;
                    }
                    $commonCd = explode(INT_DIVISION, $val['commonCd']);

                    if ($val['commonTargetFl'] != 'all' && empty($commonCd) === false) {
                        switch ($val['commonTargetFl']) {
                            case 'goods':
                                if (in_array($goodsNo, $commonCd) === true) {
                                    $viewCommonContent = true;
                                }
                                break;
                            case 'category':
                                foreach ($commonCd as $value) {
                                    if (in_array($value, $cateCd) === true) {
                                        $viewCommonContent = true;
                                        break;
                                    }
                                }
                                break;
                            case 'brand':
                                foreach ($commonCd as $value) {
                                    if (in_array($value, $brandCd) === true) {
                                        $viewCommonContent = true;
                                        break;
                                    }
                                }
                                break;
                            case 'scm':
                                if (in_array($scmNo, $commonCd) === true) {
                                    $viewCommonContent = true;
                                }
                                break;
                        }
                    }
                }

                if ($viewCommonContent === true) {
                    if (\Request::isMobile()) {
                        if ($val['commonHtmlContentSameFl'] == 'y') {
                            $retContent[] = stripslashes($val['commonHtmlContent']);
                        } else {
                            $retContent[] = stripslashes($val['commonHtmlContentMobile']);
                        }
                    } else {
                        $retContent[] = stripslashes($val['commonHtmlContent']);
                    }
                }
            }
        }
        return @implode('', $retContent);
    }
}
