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

use App;
use Component\Database\DBTableField;
use Encryptor;
use Framework\Utility\StringUtils;
use Globals;
use Logger;
use Message;
use Request;
use UserFilePath;

/**
 * SeoTag 관련
 * @package Bundle\Component\Policy
 * @author  atomyang
 * @property \Framework\Database\DBTool $db
 * @property array                      $serConfig
 */
class SeoTag
{
    //@formatter:off
    public $seoConfig;

    public function __construct()
    {
        if (!is_object($this->db)) {
            $this->db = \App::load('DB');
        }

        //태그설정
        $this->seoConfig['title'] = ['common' => '공통', 'goods' => '상품', 'category' => '카테고리', 'brand' => '브랜드', 'event' => '기획전', 'board' => '게시판'];
        $this->seoConfig['tag'] = ['title' => '타이틀 (Title)', 'author' => '메타태그 작성자 (Author)', 'description' => '메타태그 설명 (Description)', 'keyword' => '메타태그 키워드 (Keywords)'];

        $this->seoConfig['commonPage'] = [
            '' => 'common',
            'goods/goods_view.php' => 'goods',
            'goods/goods_list.php_cateCd' => 'category',
            'goods/goods_list.php_brandCd' => 'brand',
            'goods/event_sale.php' => 'event',
            'service/notice.php' => 'board',
            'service/cooperation.php' => 'board',
            'service/faq.php' => 'board',
            'service/qa.php' => 'board',
            'service/event.php' => 'board',
            'board/list.php' => 'board',
        ];

        $this->seoConfig['commonPageCode'] = [
            'goods/goods_view.php' => 'goodsNo',
            'goods/goods_list.php_cateCd' => 'cateCd',
            'goods/goods_list.php_brandCd' => 'brandCd',
            'goods/event_sale.php' => 'sno',
            'board/list.php' => 'bdId',
        ];

        $this->serConfig['useTableInfo'] = [
            'goods' => [
                'pageCode' => 'goodsNo',
                'useTable' => DB_GOODS
            ],
            'category' => [
                'pageCode' => 'cateCd',
                'useTable' => DB_CATEGORY_GOODS
            ],
            'brand' => [
                'pageCode' => 'cateCd',
                'useTable' => DB_CATEGORY_BRAND
            ],
            'event' => [
                'pageCode' => 'sno',
                'useTable' => DB_DISPLAY_THEME
            ],
            'board' => [
                'pageCode' => 'bdId',
                'useTable' => DB_BOARD
            ],
        ];

        $this->seoConfig['replaceCode'] = [
            'common' => ['{seo_mallNm}' => '쇼핑몰 이름'],
            'goods' => ['{seo_mallNm}' => '쇼핑몰 이름',
                '{seo_goodsNm}' => '상품명',
                '{seo_cateNm}' => '현재 카테고리 명',
                '{seo_brandNm}' => '브랜드명',
                '{seo_shortDescription}' => '짧은 설명',
                '{seo_goodsNmDetail}' => '상세상품명',
                '{seo_goodsSearchWord}' => '상품 검색 키워드',
                '{seo_makerNm}' => '제조사',
                '{seo_originNm}' => '원산지',],
            'category' => ['{seo_mallNm}' => '쇼핑몰 이름',
                '{seo_goodsCategoryListNm0}' => '1차 카테고리',
                '{seo_goodsCategoryListNm1}' => '2차 카테고리',
                '{seo_goodsCategoryListNm2}' => '3차 카테고리',
                '{seo_goodsCategoryListNm3}' => '4차 카테고리',],
            'brand' => ['{seo_mallNm}' => '쇼핑몰 이름',
                '{seo_goodsCategoryListNm0}' => '1차 브랜드',
                '{seo_goodsCategoryListNm1}' => '2차 브랜드',
                '{seo_goodsCategoryListNm2}' => '3차 브랜드',],
            'event' => ['{seo_mallNm}' => '쇼핑몰 이름',
                '{seo_eventNm}' => '기획전명',
                '{seo_eventDescription}' => '이벤트 내용',],
            'board' => ['{seo_mallNm}' => '쇼핑몰 이름',
                '{seo_bdNm}' => '게시판명']
        ];
    }

    /**
     * getSeoData
     *
     * @param null $sno
     * @param null $arrBind
     * @param bool|false $dataArray
     * @return string
     */
    public function getSeoTagData($sno = null, $arrBind = null, $dataArray = false, $addWhere = [])
    {
        if ($sno) {
            if ($this->db->strWhere) {
                $this->db->strWhere = " st.sno  = ? AND " . $this->db->strWhere;
            } else {
                $this->db->strWhere = " st.sno  = ?";
            }
            $this->db->bind_param_push($arrBind, 's', $sno);
        }
        if (empty($addWhere) === false) {
            foreach ($addWhere as $key => $val) {
                $this->db->strWhere .= " AND st." . $key . "  = ?";
                $this->db->bind_param_push($arrBind, 's', $val);
            }
        }

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_SEO_TAG . ' st ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);

        if (count($getData) == 1 && $dataArray === false) {
            return gd_htmlspecialchars_stripslashes($getData[0]);
        }

        return gd_htmlspecialchars_stripslashes($getData);
    }

    /**
     * getAdminListDisplayThemeConfig
     *
     * @return mixed
     */
    public function getSeoTagLayerList($getValue)
    {

        // --- 페이지 기본설정
        gd_isset($getValue['page'], 1);
        gd_isset($getValue['pageNum'], 10);

        $this->arrWhere[] = "deviceFl = ?";
        $this->db->bind_param_push($this->arrBind, 's', $getValue['deviceFl']);


        $page = \App::load('\\Component\\Page\\Page', $getValue['page']);
        $page->page['list'] = $getValue['pageNum']; // 페이지당 리스트 수

        $strSQL = ' SELECT COUNT(sno) AS cnt FROM ' . DB_SEO_TAG . ' WHERE ' . $this->arrWhere[0];
        $res = $this->db->query_fetch($strSQL, $this->arrBind, false);

        $page->recode['total'] = $page->recode['amount'] = $res['cnt']; // 검색이 존재하지 않으므로 검색=전체 동일수
        $page->setPage();
        $page->setUrl(\Request::getQueryString());


        // 현 페이지 결과
        $this->db->strField = "*";
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
        $this->db->strOrder = "sno desc";
        $this->db->strLimit = $page->recode['start'] . ',' . $getValue['pageNum'];

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_SEO_TAG . ' as ts ' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $this->arrBind);
        unset($this->arrBind, $this->arrWhere);

        // 각 데이터 배열화
        $getData['data'] = gd_htmlspecialchars_stripslashes(gd_isset($data));
        $getData['pageHtml'] = $page->getPage('set_seo_tag_list_page(\'PAGELINK\')');
        $getData['index'] = $page->idx;


        return $getData;
    }


    /**
     * getSeoView
     *
     * @param null $sno
     * @return mixed
     */
    public function getSeoTagView($sno)
    {

        // --- 등록인 경우
        if (!$sno) {
            // 기본 정보
            $data['mode'] = 'seo_tag_register';
            $data['deviceFl'] = gd_isset(Request::request()->get('deviceFl'), "p");
            // 기본값 설정
            DBTableField::setDefaultData('tableSeoTag', $data);

            // --- 수정인 경우
        } else {
            // 테마 정보
            $data = $this->getSeoTagData($sno);
            $data['mode'] = 'seo_tag_modify';

            // 기본값 설정
            DBTableField::setDefaultData('tableSeoTag', $data);
        }

        $checked = array();
        $checked['deviceFl'][gd_isset($data['deviceFl'])] = 'checked="checked"';

        $getData['data'] = $data;
        $getData['checked'] = $checked;

        return $getData;
    }

    /**
     * getEtcPageSkin
     *
     * 기타페이지 존재여부 체크 후 파일 경로 반환
     * @return string
     */
    public function getEtcPageSkin($_tmp, $viewDirectory)
    {
        $skinBase = \App::load('Component\\Design\\SkinBase');
        $skinList = $skinBase->getSkinListArray();
        $dirNamePath = substr($_tmp['dirname'],20, strlen($_tmp['dirname']));

        if($viewDirectory == 'Front'){
            unset($skinList['mobile']);
        } else {
            unset($skinList['front']);
        }
        foreach ($skinList as $key1 => $val1) {
            foreach ( $val1 as $key2 => $val2) {
                    $filePath = USERPATH . implode(DIRECTORY_SEPARATOR, ['data','skin', lcfirst($viewDirectory), $val2['skin_code'], $dirNamePath, $_tmp['filename'].".".$_tmp['extension']]);
                    if(is_file($filePath))
                        return $filePath;
            }
        }
    }

    /**
     * 출석체크, 타임세일, 설문조사 페이지 존재여부 체크
     *
     * @param $_tmp
     *
     * @return array
     * @throws \Framework\Debug\Exception\DatabaseException
     */
    public function getPromotionPageData($_tmp)
    {
        $fileName = $_tmp['filename'];
        $sno = substr($_tmp['extension'], 8, strlen($_tmp['extension']));
        if($fileName == 'poll_register') {
            $sno = substr($_tmp['extension'], 9, strlen($_tmp['extension']));
        }
        $getData = $arrBind = [];
        $query = '';
        if($fileName == 'time_sale'){
            $query = "SELECT * FROM " . DB_TIME_SALE . " WHERE sno = ? AND delFl='n'";
        } else if( $fileName == 'attend_stamp' || $fileName == 'attend_reply') {
            $query = "SELECT * FROM " . DB_ATTENDANCE . " WHERE sno = ?";
        } else if( $fileName == 'poll_register') {
            $query = "SELECT * FROM " . DB_POLL . " WHERE pollCode = ?";
        }
        if ($query !== '') {
            $this->db->bind_param_push($arrBind, 'i', $sno);
            $getData = $this->db->query_fetch($query, $arrBind, true);
        }

        return $getData;
    }

    /**
     * saveSeoTagPage
     *
     * @param $arrData
     *
     * @return mixed
     * @throws \Framework\Debug\Exception\DatabaseException
     */
    public function saveSeoTagPage($arrData)
    {
        if($arrData['path'] == 'main/html.php') {
            return ['result'=>false, 'code'=>'NOT FILE'];
        }
        $_tmp = pathinfo($arrData['path']);
        $etcPageFl = false;
        $viewDirectory = "Mobile";
        if ($arrData['deviceFl'] == 'p') {
            $viewDirectory = "Front";
        }
        $realPath = implode(DIRECTORY_SEPARATOR, [SYSSRCPATH, 'Bundle', 'Controller', $viewDirectory, ucfirst($_tmp['dirname']), (implode("", array_map("ucfirst", explode("_", $_tmp['filename'])))) . 'Controller.php']);
        if(substr($_tmp['dirname'], 0 ,20) == 'main/html.php?htmid=') {
            $etcPageFl = true;
            $realPath = $this->getEtcPageSkin($_tmp, $viewDirectory);
        } else if ($_tmp['dirname'] == 'event' || $_tmp['dirname'] == 'service') {
            if($this->getPromotionPageData($_tmp)) {
                $realPath = implode(DIRECTORY_SEPARATOR, [SYSSRCPATH, 'Bundle', 'Controller', $viewDirectory, ucfirst($_tmp['dirname']), (implode("", array_map("ucfirst", explode("_", $_tmp['filename'])))) . 'Controller.php']);
            }
        }
        if (!is_file($realPath)) {
            return ['result' => false, 'code' => 'NOT FILE'];
        }
        if($etcPageFl === true){
            $arrData['path'] = $_tmp['dirname'] . "/" . $_tmp['basename'];
        } else {
            $arrData['path'] = str_replace("/", "", $_tmp['dirname']) . "/" . $_tmp['basename'];
        }

        $simpleTagPage = preg_replace('/\.php.*/', '', $arrData['path']);
        foreach (array_keys($this->seoConfig['commonPage']) as $index => $page) {
            $simpleCommonPage = preg_replace('/\.php.*/', '', $page);
            if ($simpleTagPage == $simpleCommonPage) {
                return ['result' => false, 'code' => 'COMMON'];
            }
        }

        $getData = [];
        if ($arrData['mode'] == 'seo_tag_register' || ($arrData['mode'] == 'seo_tag_modify' && ($arrData['path'] != $arrData['oriPath'] || $arrData['deviceFl'] != $arrData['oriDeviceFl']))) {
            $arrBind = [];
            $this->db->strWhere = "path = ? AND deviceFl = ?";
            $this->db->bind_param_push($arrBind, 's', $arrData['path']);
            $this->db->bind_param_push($arrBind, 's', $arrData['deviceFl']);
            $getData = $this->getSeoTagData(null, $arrBind);
            unset($arrBind);
        }

        if (count($getData) > 0) {
            return ['result' => false, 'code' => 'SAME'];
        } else {
            if ($arrData['mode'] == 'seo_tag_modify') {
                $arrBind = $this->db->get_binding(DBTableField::getBindField('tableSeoTag', array_keys($arrData)), $arrData, 'update');
                $this->db->bind_param_push($arrBind['bind'], 's', $arrData['sno']);
                $this->db->set_update_db(DB_SEO_TAG, $arrBind['param'], 'sno = ?', $arrBind['bind']);
            } else {
                $arrBind = $this->db->get_binding(DBTableField::tableSeoTag(), $arrData, 'insert');
                $this->db->set_insert_db(DB_SEO_TAG, $arrBind['param'], $arrBind['bind'], 'y');
            }

            return ['result' => true];
        }
    }


    /**
     * saveSeoTag
     *
     * @param null $sno
     * @return mixed
     */
    public function saveSeoTag($arrData)
    {
        if ($arrData['sno']) {
            $arrBind = $this->db->get_binding(DBTableField::getBindField('tableSeoTag', array_keys($arrData)), $arrData, 'update');
            $this->db->bind_param_push($arrBind['bind'], 's', $arrData['sno']);
            $this->db->set_update_db(DB_SEO_TAG, $arrBind['param'], 'sno = ?', $arrBind['bind']);
        } else {
            $arrBind = $this->db->get_binding(DBTableField::tableSeoTag(), $arrData, 'insert');
            $this->db->set_insert_db(DB_SEO_TAG, $arrBind['param'], $arrBind['bind'], 'y');
            $arrData['sno'] = $this->db->insert_id();
        }

        return $arrData['sno'];

    }

    /**
     * saveSeoTagEach
     *
     * @param null $sno
     * @return mixed
     */
    public function saveSeoTagEach($pageGroup, $arrData)
    {
        $commonPage = array_flip($this->seoConfig['commonPage']);
        $arrData['path'] = $commonPage[$pageGroup];
        $arrData['deviceFl'] = 'c';
        $arrData['mallSno'] = DEFAULT_MALL_NUMBER;

        return $this->saveSeoTag($arrData);
    }


    /**
     * deleteSeoTag
     *
     * @param $arrData
     */
    function deleteSeoTag($arrData, $addWhere = null)
    {

        $strWhere = [];
        if ($arrData['sno']) {
            if (is_array($arrData['sno'])) {
                $strWhere[] = "sno IN ('" . implode("','", $arrData['sno']) . "')";
            } else {
                $strWhere[] = "sno  = '" . $arrData['sno'] . "'";
            }
        }

        if ($arrData['path']) {
            if (is_array($arrData['path'])) {
                $strWhere[] = "path IN ('" . implode("','", $arrData['path']) . "')";
            } else {
                $strWhere[] = "path  = '" . $arrData['path'] . "'";
            }
        }

        if ($arrData['deviceFl']) {
            if (is_array($arrData['deviceFl'])) {
                $strWhere[] = "deviceFl IN ('" . implode("','", $arrData['deviceFl']) . "')";
            } else {
                $strWhere[] = "deviceFl  = '" . $arrData['deviceFl'] . "'";
            }
        }

        if ($arrData['pageCode']) {
            if (is_array($arrData['pageCode'])) {
                $strWhere[] = "pageCode IN ('" . implode("','", $arrData['pageCode']) . "')";
            } else {
                $strWhere[] = "pageCode  = '" . $arrData['pageCode'] . "'";
            }
        }

        if ($addWhere) $strWhere[] = $addWhere;

        $this->db->set_delete_db(DB_SEO_TAG, implode(' AND ', gd_isset($strWhere)));
        return true;
    }

    /**
     * getSeoView
     *
     * @param null $sno
     * @return mixed
     */
    public function getSeoTagCommonList($arrData)
    {

        $arrBind = [];
        $this->db->strWhere = "deviceFl = ? AND mallSno = ? AND pageCode=''";
        $this->db->bind_param_push($arrBind, 's', $arrData['deviceFl']);
        $this->db->bind_param_push($arrBind, 's', $arrData['mallSno']);
        $getData = $this->getSeoTagData(null, $arrBind, true);
        unset($arrBind);

        return $getData;
    }

    /**
     * getSeoView
     *
     * @param string $type
     * @param        $arrData
     *
     * @return mixed
     *
     * @deprecated 성능 저하가 발생할 수 있는 쿼리가 실행될 수 있습니다.
     * @uses
     * \Component\Policy\SeoTag::getSeoTagsByEach(),
     * \Component\Policy\SeoTag::getSeoTagsByGroup(),
     * \Component\Policy\SeoTag::getSeoTagsByCommon()
     *      함수를 이용하시기 바랍니다.
     */
    public function getSeoTagPage($type = 'common', $arrData)
    {
        $arrBind = [];

        $seoTagFl = $this->getSeoTagFl($arrData['path'], $arrData['pageCode']);

        if ($type == 'etc') {
            $this->db->strWhere = "(deviceFl = 'c' and path = '' and mallSno = ?)";
            $this->db->bind_param_push($arrBind, 's', $arrData['mallSno']);
            if ($seoTagFl == 'y') {
                $this->db->strWhere .= " OR (path =? and deviceFl = ?)";
                $this->db->bind_param_push($arrBind, 's', $arrData['path']);
                $this->db->bind_param_push($arrBind, 's', $arrData['deviceFl']);
            }
            $this->db->strOrder = "deviceFl desc";
        } else {
            //게시판 아이프레임 따로 처리
            if (in_array($arrData['path'], ['service/qa.php', 'service/notice.php', 'service/event.php', 'service/cooperation.php'])) {
                $serviceBoard = [
                    'service/qa.php' => 'qa',
                    'service/notice.php' => 'notice',
                    'service/event.php' => 'event',
                    'service/cooperation.php' => 'cooperation'
                ];
                $arrData['pageCode'] = $serviceBoard[$arrData['path']];
                $arrData['path'] = "board/list.php";

            }

            $this->db->strWhere = "((path = '' AND mallSno =?) OR (path =? and pageCode='' AND mallSno =?)";
            $this->db->bind_param_push($arrBind, 's', $arrData['mallSno']);
            $this->db->bind_param_push($arrBind, 's', $arrData['path']);
            $this->db->bind_param_push($arrBind, 's', $arrData['mallSno']);
            if ($seoTagFl == 'y' && empty($arrData['pageCode']) === false) {
                $this->db->strWhere .= " OR (path =? and pageCode=?)";
                $this->db->bind_param_push($arrBind, 's', $arrData['path']);
                $this->db->bind_param_push($arrBind, 's', $arrData['pageCode']);
            }
            $this->db->strWhere .= ") and deviceFl = 'c'";
            $this->db->strOrder = "pageCode desc ,path desc";
        }

        $getData = $this->getSeoTagData(null, $arrBind, true);
        unset($arrBind);

        // sno desc 재정렬 처리했지만 경우에 따라서 달라짐
        // https://nhnent.dooray.com/project/posts/2065858437443953146
        #ArrayUtils::subKeySort($getData, 'sno', false);

        if ($type == 'common') {
            return $getData[0];
        } else {
            return $getData[0];
        }
    }

    public function getSeoNo($pageGroup, $arrData)
    {
        $arrBind = [];
        $commonPage = array_flip($this->seoConfig['commonPage']);

        $this->db->strField = "sno";
        $this->db->strWhere = "path =? and pageCode = ?";
        $this->db->bind_param_push($arrBind, 's', $commonPage[$pageGroup]);
        $this->db->bind_param_push($arrBind, 's', $arrData['goodsNo']);

        $getData = $this->getSeoTagData(null, $arrBind, false);
        unset($arrBind);

        return $getData['sno'];
    }

    //@formatter:on

    /**
     * 개별 seo 태그 조회 함수
     *
     * @param array $params 상점번호는 해당 변수를 통해 전달하기 바랍니다.
     *
     * @return array
     * @throws \Framework\Debug\Exception\DatabaseException
     */
    public function getSeoTagsByEach($params)
    {
        $seoTags = [];
        StringUtils::strIsSet($params['path'], '');
        StringUtils::strIsSet($params['pageCode'], '');
        StringUtils::strIsSet($params['mallSno'], '');
        StringUtils::strIsSet($params['deviceFl'], '');
        $v = \App::load('Component\\Validator\\Validator');
        $v->init();
        $v->add('path', 'required', true, '{경로}');
        if ($params['isEtcSeoTag'] === false) {
            $v->add('pageCode', 'required', true, '{페이지명}');
        }
        $v->add('mallSno', 'pattern', true, '{상점번호}', '/[1-4]/');
        $v->add('deviceFl', 'pattern', true, '{디바이스구분}', '/[c,p,m]/');
        if ($v->act($params)) {
            $fieldTypes = DBTableField::getFieldTypes(DBTableField::getFuncName(DB_SEO_TAG));
            $db = \App::getInstance('DB');
            $db->strField = 'title, author, description, keyword';
            $db->strWhere = 'path = ? AND pageCode = ? AND mallSno = ? AND deviceFl = ?';
            $db->bind_param_push($bindParam, $fieldTypes['path'], $params['path']);
            $db->bind_param_push($bindParam, $fieldTypes['pageCode'], $params['pageCode']);
            $db->bind_param_push($bindParam, $fieldTypes['mallSno'], $params['mallSno']);
            $db->bind_param_push($bindParam, $fieldTypes['deviceFl'], $params['deviceFl']);
            $query = $db->query_complete();
            $strSQL = 'SELECT /* 개별 seo 태그 조회 */ ' . array_shift($query) . ' FROM ' . DB_SEO_TAG . implode(' ', $query);
            $seoTags = $db->slave()->query_fetch($strSQL, $bindParam, false);
        }

        return $seoTags;
    }

    /**
     * 그룹별 seo 태그 설정
     *
     * @param array $params 상점번호는 해당 변수를 통해 전달하기 바랍니다.
     *
     * @return array
     * @throws \Framework\Debug\Exception\DatabaseException
     */
    public function getSeoTagsByGroup($params)
    {
        $seoTags = [];
        StringUtils::strIsSet($params['path'], '');
        StringUtils::strIsSet($params['mallSno'], '');
        $v = \App::load('Component\\Validator\\Validator');
        $v->init();
        $v->add('path', 'required', true, '{경로}');
        $v->add('mallSno', 'pattern', true, '{상점번호}', '/[1-4]/');
        if ($v->act($params)) {
            $fieldTypes = DBTableField::getFieldTypes(DBTableField::getFuncName(DB_SEO_TAG));
            $db = \App::getInstance('DB');
            $db->strField = 'title, author, description, keyword';
            $db->strWhere = 'path = ? AND pageCode = \'\' AND mallSno = ? AND deviceFl = \'c\'';
            $db->bind_param_push($bindParam, $fieldTypes['path'], $params['path']);
            $db->bind_param_push($bindParam, $fieldTypes['mallSno'], $params['mallSno']);
            $query = $db->query_complete();
            $strSQL = 'SELECT /* 그룹 seo 태그 조회 */ ' . array_shift($query) . ' FROM ' . DB_SEO_TAG . implode(' ', $query);
            $seoTags = $db->slave()->query_fetch($strSQL, $bindParam, false);
        }

        return $seoTags;
    }

    /**
     * 공통 seo 태그 설정
     *
     * @param array $params 상점번호는 해당 변수를 통해 전달하기 바랍니다.
     *
     * @return array
     * @throws \Framework\Debug\Exception\DatabaseException
     */
    public function getSeoTagsByCommon($params)
    {
        $seoTags = [];
        StringUtils::strIsSet($params['mallSno'], '');
        $v = \App::load('Component\\Validator\\Validator');
        $v->init();
        $v->add('mallSno', 'pattern', true, '{상점번호}', '/[1-4]/');
        if ($v->act($params)) {
            $fieldTypes = DBTableField::getFieldTypes(DBTableField::getFuncName(DB_SEO_TAG));
            $db = \App::getInstance('DB');
            $db->strField = 'title, author, description, keyword';
            $db->strWhere = 'path = \'\' AND pageCode = \'\' AND mallSno = ? AND deviceFl = \'c\'';
            $db->bind_param_push($bindParam, $fieldTypes['mallSno'], $params['mallSno']);
            $query = $db->query_complete();
            $strSQL = 'SELECT /* 공통 seo 태그 조회 */ ' . array_shift($query) . ' FROM ' . DB_SEO_TAG . implode(' ', $query);
            $seoTags = $db->slave()->query_fetch($strSQL, $bindParam, false);
        }

        return $seoTags;
    }

    private function getSeoTagFl($path = '', $pageCode = '')
    {
        if (empty($path) === true || empty($pageCode) === true) return 'y';

        $arrBind = $arrWhere = [];
        $pathCode = $this->seoConfig['commonPage'][$path];
        $useTableInfo = $this->serConfig['useTableInfo'][$pathCode];

        $arrWhere[] = '`' . $useTableInfo['pageCode'] . '` = ?';
        $this->db->bind_param_push($arrBind, 's', $pageCode);

        $this->db->strField = 'seoTagFl';
        $this->db->strWhere = implode(' AND ', gd_isset($arrWhere));

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . $useTableInfo['useTable'] . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $arrBind, false);

        return $data['seoTagFl'];
    }

    /**
     * 개별 seo 태그 사용 여부 확인
     *
     * @param $path
     * @param $pageCode
     *
     * @return bool
     */
    public function useSeoTagFlByEach($path, $pageCode)
    {
        return ($this->getSeoTagFl($path, $pageCode) == 'y');
    }
}
