<?php
/**
 * 이벤트관리자 Class
 *
 * @author sj
 * @version 1.0
 * @since 1.0
 * @copyright ⓒ 2016, NHN godo: Corp.
 */
//		관리자 모드에서는
//		관리자 모드에서는

namespace Bundle\Component\Event;

use Component\Validator\Validator;
use Component\Database\DBTableField;
use Framework\Debug\Exception\Except;
use Framework\Debug\Exception\AlertBackException;
use Framework\Utility\ArrayUtils;

class EventAdmin
{
    const ECT_INVALID_ARG = 'EventAdmin.ECT_INVALID_ARG';
    const TEXT_INVALID_ARG = '%s인자가 잘못되었습니다.';

    private $db;
    private $fieldTypes; // db field type

    /**
     * 생성자
     */
    public function __construct()
    {
        if (!is_object($this->db)) {
            $this->db = \App::load('DB');
        }
        $this->fieldTypes = DBTableField::getFieldTypes('tableEvent');
    }

    /**
     * 상품코드로 카테고리(브랜드) 가져오기
     * @param array $goodsNo 상품코드
     * @param array $cateMode 카테고리타입('category', 'brand')
     * @return array data
     */
    public function getCategory($goodsNo, $cateMode = 'category')
    {
        $goods	= \App::load('\\Component\\Goods\\Goods');
        $cateData = $goods->getGoodsNoToCateCd($goodsNo, $cateMode);
        if (ArrayUtils::isEmpty($cateData) === false) {
            $rtnCateData = array();
            for($i = 0; $i < count($cateData['cateCd']); $i++) {
                $tmpArrCateCd = str_split($cateData['cateCd'][$i], DEFAULT_LENGTH_CATE);
                $tmpArrCateNm = explode('>', str_replace(' ', '', $cateData['cateNm'][$i]));
                $tmpCateCd = '';
                $tmpCateNm = array();
                for($j = 0; $j < count($tmpArrCateCd); $j++) {
                    $tmpCateCd .= $tmpArrCateCd[$j];
                    if (isset($tmpArrCateNm[$j]) === true) {
                        $tmpCateNm[] = $tmpArrCateNm[$j];
                        if (!isset($rtnCateData[$tmpCateCd])) {
                            $rtnCateData[$tmpCateCd] = implode(' > ', $tmpCateNm);
                        }
                    }
                }
                unset($tmpCateCd);
                unset($tmpCateNm);
            }
            return $rtnCateData;
        }
        return null;
    }

    /**
     * 이벤트 목록
     * @return array data
     */
    public function getList(&$req)
    {
        //--- 검색 설정
        // 키워드 검색
        $search['skey'] = gd_isset($req['skey']);
        $search['sword'] = gd_isset($req['sword']);
        if ($search['skey'] && $search['sword']) {
            $selected['skey'][$search['skey']] = ' selected="selected"';
            if ($search['skey'] == 'all'){
                $arrWhere[] = "concat(subject,contents) LIKE concat('%',?,'%')";
                $this->db->bind_param_push($arrBind,'s', $search['sword']);
            } else {
                $arrWhere[] = $search['skey']." LIKE concat('%',?,'%')";
                $this->db->bind_param_push($arrBind, $this->fieldTypes[$search['skey']], $search['sword']);
            }
        }

        $search['endDt'] = gd_isset($req['endDt']);
        if ($search['endDt']) {
            $arrWhere[] = " startDt <= date_format(?, '%Y-%m-%d') ";
            $this->db->bind_param_push($arrBind, $this->fieldTypes['endDt'], $search['endDt']);
        }
        $search['startDt'] = gd_isset($req['startDt']);
        if ($search['startDt']) {
            $arrWhere[] = " endDt >= date_format(?, '%Y-%m-%d') ";
            $this->db->bind_param_push($arrBind, $this->fieldTypes['startDt'], $search['startDt']);
        }

        //--- 페이지 설정
        gd_isset($req['page'], 1);
        gd_isset($req['perPage'], 10);

        $start = ($req['page'] - 1) * 10;
        $limit = 10;

        //--- 목록
        $this->db->strField = "sno, subject, startDt, endDt";
        if (ArrayUtils::isEmpty($arrWhere) === false) {
            $this->db->strWhere = implode(" AND ", $arrWhere);
        }
        $this->db->strOrder = "sno desc";
        $this->db->strLimit = "?,?";
        $totalBind = $arrBind;
        $this->db->bind_param_push($arrBind, 'i', $start);
        $this->db->bind_param_push($arrBind, 'i', $limit);

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_EVENT . ' ' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $arrBind);

        //--- 검색개수
        $this->db->strField = "count(*) as cnt";
        if (ArrayUtils::isEmpty($arrWhere) === false) {
            $this->db->strWhere = implode(" AND ", $arrWhere);
        }

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_EVENT . ' ' . implode(' ', $query);
        $total = $this->db->query_fetch($strSQL, $totalBind, false)['cnt'];
        $srchCnt = $total;

        //--- 각 데이터 배열화
        $getData['data'] = gd_htmlspecialchars_stripslashes(gd_isset($data));
        $getData['search'] = gd_isset($search);
        $getData['selected'] = gd_isset($selected);
        $getData['totalCnt'] = $this->db->table_status(DB_EVENT, 'Rows');
        $getData['srchCnt'] = $srchCnt;

        return $getData;
    }

    /**
     * 이벤트정보
     * @param string $sno 일련번호
     * @return array 데이터
     */
    public function getView($sno)
    {
        try {
            if (!$sno) {
                $checked['display']['gallery'] = ' checked="checked" '; //디스플레이
                $checked['brandFl']['y'] = ' checked="checked" '; //상품정보의 브랜드출력여부
                $checked['optionFl']['y'] = ' checked="checked" '; //상품정보의 옵션출력여부
                $checked['soldOutFl']['y'] = ' checked="checked" '; //품절상품출력여부
                $getData['checked'] = $checked;
                return $getData;
            }

            if (Validator::number($sno, null, null, true) === false) {
                throw new Except(self::ECT_INVALID_ARG,sprintf(__('%s인자가 잘못되었습니다.'), '일련번호'));
            }

            $this->db->strField		= "*";
            $this->db->strWhere		= "sno=?";
            $this->db->bind_param_push($arrBind, 'i', $sno);

            $query	= $this->db->query_complete();
            $strSQL = 'SELECT '.array_shift($query).' FROM '.DB_EVENT.' '.implode(' ',$query);
            $data	= $this->db->query_fetch($strSQL,$arrBind,false);

            if (!$data) {
                throw new Except(self::ECT_INVALID_ARG,sprintf(__('%s인자가 잘못되었습니다.'), '일련번호'));
            }
            if (gd_isset($data['goodsNo'])) {
                $goods = \App::load('\\Component\\Goods\\Goods');
                $data['goods'] = $goods->getGoodsDataDisplay($data['goodsNo']);
                unset($goods);

                $data['goodsCate'] = $this->getCategory($data['goodsNo'], 'category');
                $data['goodsBrand'] = $this->getCategory($data['goodsNo'], 'brand');
            }
            if (gd_isset($data['cateCd'])) {
                $arrCateCd = explode(INT_DIVISION, $data['cateCd']);
                if (ArrayUtils::isEmpty($arrCateCd) === false && ArrayUtils::isEmpty($data['goodsCate']) === false) {
                    foreach($arrCateCd as $val) {
                        foreach($data['goodsCate'] as $key2 => $val2) {
                            if ($key2 == $val) $newGoodsCate["{$key2}"] = $val2;
                        }
                    }

                    if (ArrayUtils::isEmpty($newGoodsCate) === false) $data['goodsCate'] = array_merge($newGoodsCate, $data['goodsCate']);
                    unset($newGoodsCate);
                }

                if (ArrayUtils::isEmpty($arrCateCd) === false) {
                    foreach($arrCateCd as $val) {
                        $checked['cateCd'][$val] = ' checked="checked" ';
                    }
                }
            }
            if (gd_isset($data['brandCd'])) {
                $arrBrandCd = explode(INT_DIVISION, $data['brandCd']);
                if (ArrayUtils::isEmpty($arrBrandCd) === false && ArrayUtils::isEmpty($data['goodsBrand']) === false) {
                    foreach($arrBrandCd as $val) {
                        foreach($data['goodsBrand'] as $key2 => $val2) {
                            if ($key2 == $val) $newGoodsBrand["{$key2}"] = $val2;
                        }
                    }

                    if (ArrayUtils::isEmpty($newGoodsBrand) === false) $data['goodsBrand'] = array_merge($newGoodsBrand, $data['goodsBrand']);
                    unset($newGoodsBrand);
                }

                if (ArrayUtils::isEmpty($arrBrandCd) === false) {
                    foreach($arrBrandCd as $val) {
                        $checked['brandCd'][$val] = ' checked="checked" ';
                    }
                }
            }

            // radiobutton
            $checked['display'][$data['display']] = ' checked="checked" '; //디스플레이
            $checked['brandFl'][$data['brandFl']] = ' checked="checked" '; //상품정보의 브랜드출력여부
            $checked['optionFl'][$data['optionFl']] = ' checked="checked" '; //상품정보의 옵션출력여부
            $checked['soldOutFl'][$data['soldOutFl']] = ' checked="checked" '; //품절상품출력여부

            $getData['data'] = gd_htmlspecialchars_stripslashes($data);
            $getData['checked'] = $checked;

            unset($data);

            return $getData;
        }
        catch(Except $e) {
            throw new AlertBackException($e->ectMessage);
        }
    }

    /**
     * 이벤트등록
     * @param array $req
    */
    public function insertData($req)
    {
        try {
            if (ArrayUtils::isEmpty($req['goodsNo']) === false) $req['goodsNo'] = implode(INT_DIVISION, $req['goodsNo']);
            if (ArrayUtils::isEmpty($req['cateCd']) === false) $req['cateCd'] = implode(INT_DIVISION, $req['cateCd']);
            if (ArrayUtils::isEmpty($req['brandCd']) === false) $req['brandCd'] = implode(INT_DIVISION, $req['brandCd']);

            // Validation
            $this->validate('regist', $req);

            // 저장
            $arrBind = $this->db->get_binding(DBTableField::tableEvent(),$req,'insert');
            $this->db->set_insert_db(DB_EVENT, $arrBind['param'], $arrBind['bind'], 'y');
            unset($arrBind);
        }
        catch(Except $e) {
            throw new Except($e->ectName, $e->ectMessage);
        }
    }

    /**
     * 이벤트수정
     * @param array $req
    */
    public function modifyData($req)
    {
        try {
            if (ArrayUtils::isEmpty($req['goodsNo']) === false) $req['goodsNo'] = implode(INT_DIVISION, $req['goodsNo']);
            if (ArrayUtils::isEmpty($req['cateCd']) === false) $req['cateCd'] = implode(INT_DIVISION, $req['cateCd']);
            if (ArrayUtils::isEmpty($req['brandCd']) === false) $req['brandCd'] = implode(INT_DIVISION, $req['brandCd']);

            // Validation
            $this->validate('modify', $req);

            // 저장
            $arrBind = $this->db->get_binding(DBTableField::tableEvent(),$req,'update');
            $this->db->bind_param_push($arrBind['bind'], 'i', $req['sno']);
            $this->db->set_update_db(DB_EVENT, $arrBind['param'], 'sno = ?', $arrBind['bind'], false);
            unset($arrBind);
        }
        catch(Except $e) {
            echo $e->ectMessage;
        }
    }

    /**
     * 이벤트삭제
     * @param int $sno 일련번호
     */
    public function deleteData($sno)
    {
        try {
            if (Validator::number($sno, null, null, true) === false) {
                throw new Except(self::ECT_INVALID_ARG,sprintf(__('%s인자가 잘못되었습니다.'), '일련번호'));
            }

            $this->db->bind_param_push($arrBind, 'i', $sno);
            $this->db->set_delete_db(DB_EVENT, 'sno = ?', $arrBind);
        }
        catch(Except $e) {
            echo $e->ectMessage;
        }
    }

    /**
     * 이벤트 등록/수정데이터 확인
     * @param int $sno 일련번호
     */
    private function validate($mode, &$arrData)
    {
        // Validation
        $validator = new Validator();
        switch ($mode) {
            case 'modify' : {
                $validator->add('sno', 'number', true); // 아이디
                break;
            }
        }
        $validator->add('subject', '', true); // 제목
        $validator->add('startDt', '', true); // 시작일
        $validator->add('endDt', '', true); // 종료일
        $validator->add('contents', ''); // 내용
        $validator->add('goodsNo', '', true); // 상품
        $validator->add('display', '', true); // 출력방식
        $validator->add('cateCd', ''); // 카테고리
        $validator->add('brandCd', ''); // 브랜드
        $validator->add('perLine', 'number', true); // 라인당 상품수
        $validator->add('soldOutFl', 'yn', true); // 품절상품출력여부
        $validator->add('brandFl', 'yn', true); // 옵션출력여부
        $validator->add('optionFl', 'yn', true); // 브랜드출력여부

        if ($validator->act($arrData, true) === false) {
            throw new Except(self::ECT_INVALID_ARG,implode("\n", $validator->errors));
        }
    }
}
