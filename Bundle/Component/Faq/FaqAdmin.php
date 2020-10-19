<?php
/**
 * FAQ관리 Class
 *
 * @author sj
 * @version 1.0
 * @since 1.0
 * @copyright ⓒ 2016, NHN godo: Corp.
 */
namespace Bundle\Component\Faq;

use Component\Validator\Validator;
use Component\Database\DBTableField;
use Framework\Utility\ArrayUtils;

class FaqAdmin extends Faq
{
    const ECT_INVALID_ARG = 'FaqAdmin.ECT_INVALID_ARG';
    const TEXT_INVALID_ARG = '%s인자가 잘못되었습니다.';


    /**
     * 생성자
     */
    public function __construct()
    {
        if (!is_object($this->db)) {
            $this->db = \App::load('DB');
        }
    }

    /**
     * 게시판 목록
     * @return array data
     */
    public function getFaqList($searchData = null)
    {
        $getData = $search = $arrWhere = [];
        $arrBind = null;

        $orderBy = "sortNo DESC, sno DESC";
        //--- 검색 설정
        $search['searchKey'] = gd_isset($searchData['searchKey']);
        $search['searchWord'] = gd_isset($searchData['searchWord']);
        $search['category'] = gd_isset($searchData['category']);
        $search['isBest'] = gd_isset($searchData['isBest']);
        $search['regDt'] = gd_isset($searchData['regDt']);
        $search['mallSno'] = gd_isset($searchData['mallSno'],DEFAULT_MALL_NUMBER);    //전체표시
        if ($search['searchKey'] && $search['searchWord']) {
            if ($search['searchKey'] == 'all') {
                $arrWhere[] = "concat(subject, contents, answer) LIKE concat('%',?,'%')";
                $this->db->bind_param_push($arrBind, 's', $search['searchWord']);
            } else {
                $arrWhere[] = $search['searchKey'] . " LIKE concat('%',?,'%')";
                $this->db->bind_param_push($arrBind, 's', $search['searchWord']);
            }
        }

        if ($search['regDt'][0] && $search['regDt'][1]) {
            $arrWhere[] = " regDt between ? AND ? ";
            $this->db->bind_param_push($arrBind, 's', $search['regDt'][0]);
            $this->db->bind_param_push($arrBind, 's', $search['regDt'][1] . " 23:59");
        }

        if ($search['mallSno']>0) {
            $arrWhere[] = "mallSno = ? ";
            $this->db->bind_param_push($arrBind, 'i', $search['mallSno']);
        }

        if ($search['category']) {
            $arrWhere[] = "category LIKE concat('%',?,'%')";
            $this->db->bind_param_push($arrBind, 's', $search['category']);
        }

        if ($search['isBest']) {
            $arrWhere[] = "isBest=?";
            $this->db->bind_param_push($arrBind, 's', $search['isBest']);
            $orderBy = "bestSortNo DESC, sno DESC";
        }

        $checked['isBest'][$search['isBest']] = 'checked';
        $checked['mallSno'][$search['mallSno']] = 'checked';

        //--- 목록
        $this->db->strField = "*";
        $this->db->strWhere = implode(" and ", $arrWhere);
        $this->db->strOrder = $orderBy;

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_FAQ . ' ' . implode(' ', $query);
        $data = gd_htmlspecialchars_stripslashes($this->db->query_fetch($strSQL, $arrBind));

        $search = ArrayUtils::removeEmpty($search);

        $this->db->strField = 'count(*) as cnt';
        $this->db->strWhere = implode(' AND ', gd_isset($arrWhere));
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_FAQ . implode(' ', $query);
        $searchCount = $this->db->query_fetch($strSQL, $arrBind, false);

        $query = "SELECT COUNT(sno)  FROM " . DB_FAQ;
        list($totalCount) = $this->db->fetch($query, 'row');

        //--- 각 데이터 배열화
        $getData['pageInfo'] = ['searchCount' => $searchCount['cnt'], 'totalCount' => $totalCount];
        $getData['data'] = gd_isset($data);
        $getData['search'] = &$search;
        $getData['checked'] = $checked;
        $getData['categoryBox'] = gd_code('03001',$search['mallSno']);
        return $getData;
    }

    /**
     * insertFaqData
     *
     * @param $arrData
     * @throws \Exception
     */
    public function insertFaqData($arrData)
    {
        try {
            // Validation
            gd_isset($arrData['isBest'],'n');
            $this->validate('insert', $arrData, $arrData['isBest']);

            // 저장
            $arrBind = $this->db->get_binding(DBTableField::tableFaq(), $arrData, 'insert');
            $this->db->set_insert_db(DB_FAQ, $arrBind['param'], $arrBind['bind'], 'y');
            $this->db->set_update_db(DB_FAQ, ['sortNo=sno'], 'sno = ?', ['i', $this->db->insert_id()], false);
        } catch (\Exception $e) {
            throw new \Exception($e->ectMessage);
        }
    }

    /**
     * 게시판수정
     * @param array $arrData
     */
    public function modifyFaqData($arrData, $mode = 'modify', $isBest = 'n')
    {
        try {
            $sno = $arrData['sno'];
            unset($arrData['mallSno']);
            if (empty($isBest) === true) {
                $isBest = 'n';
                $arrData['isBest'] = 'n';
            }
            // Validation
            $this->validate($mode, $arrData, $isBest);

            // 저장
            $arrBind = $this->db->get_binding(DBTableField::tableFaq(), $arrData, 'update', array_keys($arrData), null);
            $this->db->bind_param_push($arrBind['bind'], 'i', $sno);
            $this->db->set_update_db(DB_FAQ, $arrBind['param'], 'sno = ?', $arrBind['bind'], false);
        } catch (Except $e) {
            echo $e->ectMessage;
        }
    }

    /**
     * 게시판삭제
     * @param int $sno 일련번호
     */
    public function deleteFaqData($sno)
    {
        try {
            if (Validator::number($sno, null, null, true) === false) {
                throw new Except(self::ECT_INVALID_ARG, sprintf(__('%1$s인자가 잘못되었습니다.'), __('일련번호')));
            }
            $arrBind = ['i', $sno];
            $this->db->set_delete_db(DB_FAQ, 'sno = ?', $arrBind);
        } catch (Except $e) {
            echo $e->ectMessage;
        }
    }

    private function validate($mode, &$arrData, $isBest)
    {
        // Validation
        $validator = new Validator();
        $validator->add('category', '', true); // 카테고리
        if ($mode == 'batch' && $isBest == 'n') {
            $validator->add('sortNo', '', true); // 순서
        } else {
            $validator->add('subject', '', true); // 제목
            $validator->add('contents', '', true); // 내용
            $validator->add('answer', '', true); // 답변
        }
        $validator->add('isBest', 'yn', true); // 베스트에 노출
        if ($mode == 'batch' && $isBest == 'y') {
            $validator->add('bestSortNo', 'number', true); // 베스트 노출순서
        }
        $validator->add('mallSno', 'number', true); // 몰번호

        if ($validator->act($arrData, true) === false) {
            throw new Except(self::ECT_INVALID_ARG, implode("\n", $validator->errors));
        }
    }
}
