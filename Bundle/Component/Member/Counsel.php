<?php
namespace Bundle\Component\Member;

use App;
use Component\AbstractComponent;
use Component\Database\DBTableField;
use Component\Page\Page;
use Component\Validator\Validator;
use Exception;
use LogHandler;


/**
 * Class CRM-상담
 * @package Bundle\Component\Member
 * @author  yjwee
 */
class Counsel extends \Component\AbstractComponent
{
    private $arrBind;
    private $arrWhere;
    // @formatter:off
    //__('주문')
    //__('배송')
    //__('취소환불')
    //__('오류')
    //__('기타')
    private $kinds = ['o'=>'주문','d'=>'배송','c'=>'취소환불','e'=>'오류','etc'=>'기타'];
    //__('전화')
    //__('메일')
    private $method = ['p'=>'전화', 'm'=>'메일'];
    //__('등록일')
    private $crmSorts =  ['regDt DESC'=>'등록일&darr;','regDt ASC'=>'등록일&uarr;'];
    // @formatter:on

    /**
     * 생성자
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 상담 내역 저장/수정
     *
     * @param $requestParams
     *
     * @return bool
     * @throws Exception
     */
    public function save($requestParams)
    {
        if (empty($requestParams['sno'])) {
            return $this->insert($requestParams);
        } else {
            return $this->update($requestParams);
        }
    }

    public function insert($requestParams)
    {
        // Validation
        $validator = new Validator();
        $validator->add('managerNo', 'number', true); // 처리자번호
        $validator->add('memNo', 'number', true); // 회원번호

        $validator->add('method', '', true); // 상담수단
        $validator->add('kind', '', true); // 상담구분
        $validator->add('contents', ''); // 내용

        if ($validator->act($requestParams, true) === false) {
            throw new Exception(implode("\n", $validator->errors));
        }

        // 저장
        unset($this->arrBind);
        $this->arrBind = $this->db->get_binding(DBTableField::tableCrmcounsel(), $requestParams, 'insert');
        $this->db->set_insert_db(DB_CRM_COUNSEL, $this->arrBind['param'], $this->arrBind['bind'], 'y');

        return true;
    }

    public function update($requestParams)
    {
        // Validation
        $validator = new Validator();
        $validator->add('sno', 'number', true); // 일련번호
        $validator->add('method', '', true); // 상담수단
        $validator->add('kind', '', true); // 상담수단
        $validator->add('contents', ''); // 내용
        $validator->add('managerNo', 'number', true); // 내용

        if ($validator->act($requestParams, true) === false) {
            throw new Exception(implode("\n", $validator->errors));
        }
        unset($this->arrBind);
        $this->arrBind = $this->db->get_binding(DBTableField::tableCrmcounsel(), $requestParams, 'update', null, array('memNo'));
        $this->db->bind_param_push($this->arrBind['bind'], 'i', $requestParams['sno']);
        $this->db->set_update_db(DB_CRM_COUNSEL, $this->arrBind['param'], 'sno=?', $this->arrBind['bind'], false);

        return true;
    }

    public function delete($requestParams)
    {
        foreach($requestParams['sno'] as $snoKey => $snoVal ) {
            if (Validator::number($snoVal, null, null, true) === false) {
                throw new Exception(sprintf(__('%s 인자가 잘못되었습니다.'), __('일련번호')), 500);
            }
            $arrBind = [];
            /*삭제 전 디비 정보 추출*/
            $data = $this->getViewOnce($snoVal)[0];
            $serializeData = serialize($data);

            $this->db->bind_param_push($arrBind, 'i', $snoVal);
            $this->db->set_delete_db(DB_CRM_COUNSEL, 'sno = ?', $arrBind);
            // 삭제 로그 생성
            LogHandler::wholeLog('counsel', null, 'delete', $snoVal, $snoVal, $serializeData);
            unset($arrBind, $counselData);
        }

        return true;
    }


    /**
     * CRM>상담내역 조회 함수
     *
     * @param array $requestParams
     *
     * @return array|object
     */
    public function getList($requestParams)
    {
        unset($this->arrBind, $this->arrWhere, $this->arrWhereManager);
        $this->arrBind = $this->arrWhere = $this->arrWhereManager = [];

        // --- 페이지 설정
        if (empty($requestParams['page']) === true) {
            $requestParams['page'] = 1;
        }
        if (empty($requestParams['pageNum']) === true) {
            $requestParams['pageNum'] = 10;
        }

        $managerDataSearch = array('managerNm'=>'managerNm','managerId'=>'managerId');

        $this->db->bindParameter('memNo', $requestParams, $this->arrBind, $this->arrWhere, 'tableCrmCounsel', 'cc');
        $this->db->bindParameter('kind', $requestParams, $this->arrBind, $this->arrWhere, 'tableCrmCounsel', 'cc');
        $this->db->bindParameter('method', $requestParams, $this->arrBind, $this->arrWhere, 'tableCrmCounsel', 'cc');
        $this->db->bindParameterByKeyword($managerDataSearch, $requestParams, $this->arrBind, $this->arrWhere, 'tableManager', 'mn');
        $this->db->bindParameterByDateTimeRange('regDt', $requestParams, $this->arrBind, $this->arrWhere, 'tableCrmCounsel', 'cc');
        $this->db->strField = ' cc.*, m.memId, mn.managerId, mn.managerNm ';
        $this->db->strWhere = implode(' AND ', $this->arrWhere);
        $this->db->strJoin = DB_CRM_COUNSEL . ' AS cc JOIN ' . DB_MEMBER . ' AS m ON cc.memNo=m.memNo JOIN ' . DB_MANAGER . ' AS mn ON cc.managerNo=mn.sno';
        if($requestParams['sort']) {
            $this->db->strOrder = $requestParams['sort'];
        } else {
            $this->db->strOrder = 'regDt DESC';
        }

        $offset = $requestParams['page'] < 1 ? 0 : ($requestParams['page'] - 1) * $requestParams['pageNum'];
        $this->db->strLimit = $offset . ',' . $requestParams['pageNum'];
        $queryData = $this->db->query_complete();

        $query = ' SELECT ' . array_shift($queryData) . ' FROM ' . array_shift($queryData) . ' ' . implode(' ', $queryData);
        $data = $this->db->query_fetch($query, $this->arrBind);

        return $data;
    }

    /**
     * CRM>상담내역 조회 함수
     *
     * @param array $requestParams
     *
     * @return array|object
     */
    public function getViewOnce($counselSno)
    {
        unset($this->arrBind, $this->arrWhere);
        $this->arrBind = $this->arrWhere = [];
        $this->db->bind_param_push($this->arrBind, 'i', $counselSno);
        $this->db->strField = ' cc.*, m.memId, mn.managerId, mn.managerNm ';
        $this->db->strWhere = "cc.sno=?";
        $this->db->strJoin = DB_CRM_COUNSEL . ' AS cc JOIN ' . DB_MEMBER . ' AS m ON cc.memNo=m.memNo JOIN ' . DB_MANAGER . ' AS mn ON cc.managerNo=mn.sno';
        $queryData = $this->db->query_complete();

        $query = ' SELECT ' . array_shift($queryData) . ' FROM ' . array_shift($queryData) . ' ' . implode(' ', $queryData);
        $data = $this->db->query_fetch($query, $this->arrBind);

        return $data;
    }

    /**
     * 상담내역 코드 값 치환
     *
     * @param array $list
     */
    public function replaceList(array &$list)
    {
        foreach ($list as &$data) {
            $data['kind'] = $this->kinds[$data['kind']];
            $data['method'] = $this->method[$data['method']];
        }
    }
    /**
     * 상담내역 코드 값 배열 데이터
     *
     */
    public function counselCodeData()
    {
        $data['kind'] = $this->kinds;
        $data['method'] = $this->method;
        return $data;
    }

    /**
     * CRM>상담내역 페이징 객체 반환 함수
     *
     * @param $requestParams
     * @param $queryString
     *
     * @return Page
     */
    public function getPage($requestParams, $queryString)
    {
        $query = 'SELECT COUNT(*) as cnt FROM ' . DB_CRM_COUNSEL . ' AS cc JOIN ' . DB_MEMBER . ' AS m ON cc.memNo=m.memNo JOIN ' . DB_MANAGER . ' AS mn ON cc.managerNo=mn.sno';
        $total = $this->db->query_fetch($query . ' WHERE ' . implode(' AND ', $this->arrWhere), $this->arrBind, false);
        $amount = $this->db->query_fetch($query . ' WHERE m.memNo=' . $requestParams['memNo'], null, false);
        $page = new Page($requestParams['page'], $total['cnt'], $amount['cnt'], $requestParams['pageNum']);
        $page->setUrl($queryString);

        return $page;
    }

    /**
     * getCount
     *
     * @param $memNo
     *
     * @return mixed
     */
    public function getCountByMemNo($memNo)
    {
        return parent::getCount(DB_CRM_COUNSEL, '1', 'WHERE memNo=' . $memNo);
    }

    /**
     * CRM>상담내역 체크박스 체크 처리 함수
     *
     * @param $requestParams
     *
     * @return mixed
     */
    public function setChecked($requestParams)
    {
        $checked['method'][$requestParams['method']] = 'checked="checked"';

        return $checked;
    }

    /**
     * @return array
     */
    public function getKinds()
    {
        return $this->kinds;
    }

    /**
     * @return array
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return array
     */
    public function getCrmSorts()
    {
        return $this->crmSorts;
    }
}
