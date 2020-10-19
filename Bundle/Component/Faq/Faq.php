<?php
/**
 * FAQ Class
 *
 * @author sj
 * @version 1.0
 * @since 1.0
 * @copyright ⓒ 2016, NHN godo: Corp.
 */

namespace Bundle\Component\Faq;

use Component\Page\Page;
use Component\Validator\Validator;
use Framework\Utility\StringUtils;
use Framework\Utility\ArrayUtils;
use DB;

class Faq
{
    const ECT_INVALID_ARG = 'Faq.ECT_INVALID_ARG';
    const TEXT_INVALID_ARG = '%s인자가 잘못되었습니다.';
    const PAGE_LIST_COUNT = 10;
    const PAGE_BLOCK_COUNT= 10;

    protected $db;

    /**
     * 생성자
     */
    public function __construct()
    {
        if (!is_object($this->db)) {
            $this->db = \App::load('DB');
        }

        $this->replaceCode = \App::load('\\Component\\Design\\ReplaceCode');
    }

    /**
     * getFaqView
     *
     * @param null $sno
     * @return array
     * @throws Except
     * @throws \Exception
     */
    public function getFaqView($sno = null)
    {
        if ($sno == null) {
            return ['checked' => ['isBest' => ['y' => '', 'n' => ' checked="checked" ']]];
        }

        if (Validator::number($sno, null, null, true) === false) {
            throw new \Exception(sprintf(__('%1$s인자가 잘못되었습니다.'), __('일련번호')));
        }

        $getData = $data = $checked = $selected = $disabled = [];

        $this->db->strField = "*";
        $this->db->strWhere = "sno=?";
        $this->db->bind_param_push($arrBind, 'i', $sno);

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_FAQ . ' ' . implode(' ', $query);
        $data = gd_htmlspecialchars_stripslashes($this->db->query_fetch($strSQL, $arrBind, false));

        //--- 각 데이터 배열화
        $getData['data'] = &$data;

        if (!$getData['data']) {
            throw new Except(self::ECT_INVALID_ARG, sprintf(__('%1$s인자가 잘못되었습니다.'), __('일련번호')));
        }
        // radiobutton
        $checked['isBest'][$getData['data']['isBest']] = ' checked="checked" '; //베스트
        $getData['checked'] = $checked;

        unset($data);

        return $getData;

    }

    /**
     * 게시판 목록
     * @return array data
     */
    public function getFaqList(&$req)
    {
        $getData = $arrBind = $search = $arrWhere = null;
        $mallSno = \SESSION::get(SESSION_GLOBAL_MALL)['sno'] ? \SESSION::get(SESSION_GLOBAL_MALL)['sno'] : DEFAULT_MALL_NUMBER;

        $arrWhere[] = 'mallSno = '.$mallSno;
        $category = gd_code('03001',$mallSno);

        //--- 검색 설정
        gd_isset($req['page'], 1);
        $search['searchWord'] = gd_isset($req['searchWord']);
        $search['searchField'] = gd_isset($req['searchField']);
        $search['category'] = gd_isset($req['category']);

        if ($search['searchWord']) {
            switch($search['searchField']) {
                case 'subject' :
                    $arrWhere[] = "subject LIKE CONCAT('%',?,'%') ";
                    $this->db->bind_param_push($arrBind, 's', $search['searchWord']);
                    break;
                case 'contents' :
                    $arrWhere[] = "(contents LIKE CONCAT('%',?,'%') OR answer LIKE CONCAT('%',?,'%'))";
                    $this->db->bind_param_push($arrBind, 's', $search['searchWord']);
                    $this->db->bind_param_push($arrBind, 's', $search['searchWord']);
                    break;
                case 'all' :
                    $arrWhere[] = "(subject LIKE CONCAT('%',?,'%') OR contents LIKE CONCAT('%',?,'%') OR answer LIKE CONCAT('%',?,'%'))";
                    $this->db->bind_param_push($arrBind, 's', $search['searchWord']);
                    $this->db->bind_param_push($arrBind, 's', $search['searchWord']);
                    $this->db->bind_param_push($arrBind, 's', $search['searchWord']);
                    break;

                default :
                    break;
            }
        }

        if (gd_isset($req['isBest']) == 'y') {
            $arrWhere[] = "isBest = ?";
            $this->db->bind_param_push($arrBind, 's', 'y');
        }

        if ($search['category']) {
            $arrWhere[] = "category LIKE concat('%',?,'%')";
            $this->db->bind_param_push($arrBind, 's', $search['category']);
        }

        //--- 목록
        $this->db->strField        = " * ";
        if (ArrayUtils::isEmpty($arrWhere) === false) {
            $this->db->strWhere = implode(" AND ", $arrWhere);
        }

        if (gd_isset($req['isBest']) == 'y') {
            $this->db->strOrder        = "bestSortNo, sno DESC";
        } else {
            $this->db->strOrder        = "sortNo, sno DESC";
        }

        $offset = ($req['page'] - 1) * self::PAGE_LIST_COUNT;
        $this->db->strLimit = $offset." ,  ".self::PAGE_LIST_COUNT;

        $query    = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_FAQ . ' ' . implode(' ', $query);
        $data    = gd_htmlspecialchars_stripslashes($this->db->query_fetch($strSQL, $arrBind));

        $searchCnt = $this->db->query_fetch('SELECT COUNT(sno) as cnt FROM ' . DB_FAQ  .' '. $query['where'], $arrBind,false)['cnt'];
        //--- 각 데이터 배열화
        $getData['data'] = $data;
        $getData['search'] = $search;

        $pagination = new Page($req['page'], $searchCnt, $searchCnt, self::PAGE_LIST_COUNT, self::PAGE_BLOCK_COUNT);
        $pagination->setUrl(\Request::getQueryString());
        $getData['pagination'] = $pagination->getPage();;
        $getData['totalCount'] = $pagination->getTotal();
        if (ArrayUtils::isEmpty($data) === false) {
            $dataCnt = count($data);
            for ($i = 0; $i < $dataCnt; $i++) {
                $data = &$getData['data'][$i];
                if ($data) {
//                    if ($search['category']) {
//                        $data['no'] = $searchCnt - $i;
//                    } else {
                        $data['no'] = $searchCnt - (($req['page'] - 1) * self::PAGE_LIST_COUNT) - $i;
//                    }
                    if (array_key_exists($data['category'], $category)) {
                        $data['categoryNm'] = $category[$data['category']];
                    }

                    if (gd_isset($data['isBest']) == 'y') {
                        $getData['existBest'] = 'y';
                    }

                    if ($req['cutStrLength']) {
                        $data['subject'] = StringUtils::strCut( $data['subject'], $req['cutStrLength'] );
                    }

                    $data['subject'] = $this->replaceCode->replace($data['subject']);
                    $data['contents'] = $this->replaceCode->replace($data['contents']);
                    $data['answer'] = $this->replaceCode->replace($data['answer']);
                }
            }
        }
        return $getData;
    }
}
