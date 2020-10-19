<?php
namespace Bundle\Component\Goods;

use App;
use Component\Database\DBTableField;
use LogHandler;
use Request;
use Exception;

/**
 * Class 상품 혜택 관리
 * @package Bundle\Component\Goods
 * @author  cjb3333@godo.co.kr
 */

class GoodsBenefit
{

    /**
     * @var \Framework\Database\DBTool $db
     */
    protected $db;

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
     * 관리자 상품 혜택 리스트
     *
     * @param string $mode
     *
     * @return array 상품 혜택 정보
     *
     */
    public function getAdminListGoodsBenefit($mode = null)
    {

        $Goods = \App::load('\\Component\\Goods\\Goods');
        $getValue = Request::get()->toArray();

        //검색설정
        /* @formatter:off */
        $this->search['sortList'] = array(
            'regDt desc' => __('등록일 ↓'),
            'regDt asc' => __('등록일 ↑'),
            'benefitNm asc' => __('혜택명 ↓'),
            'benefitNm desc' => __('혜택명 ↑')
        );
        /* @formatter:on */

        $this->search['sort'] = gd_isset($getValue['sort'], 'regDt desc');
        $this->search['benefitNm'] = gd_isset($getValue['benefitNm']);
        $this->search['goodsBenefitState'] = gd_isset($getValue['goodsBenefitState']);
        $this->search['goodsDiscountGroup'] = gd_isset($getValue['goodsDiscountGroup']);
        $this->search['benefitUseType'] = gd_isset($getValue['benefitUseType']);
        $this->search['goodsBenefitState'] = gd_isset($getValue['goodsBenefitState']);

        $this->search['searchDateFl'] = gd_isset($getValue['searchDateFl'], 'regDt');

        // 상품 혜택 레이어 디폴트 all / 상품혜택관리 디폴트 all
        $this->search['searchPeriod'] = gd_isset($getValue['searchPeriod'], '-1');

        if ($this->search['searchPeriod'] < 0) {
            $this->search['searchDate'][] = gd_isset($getValue['searchDate'][0]);
            $this->search['searchDate'][] = gd_isset($getValue['searchDate'][1]);
        } else {
            $this->search['searchDate'][] = gd_isset($getValue['searchDate'][0], date('Y-m-d', strtotime('-6 day')));
            $this->search['searchDate'][] = gd_isset($getValue['searchDate'][1], date('Y-m-d'));
        }


        $this->selected['searchDateFl'][$this->search['searchDateFl']] = $this->selected['sort'][$this->search['sort']] = "selected='selected'";
        $this->checked['searchPeriod'][$this->search['searchPeriod']] = "active";

        $this->checked['goodsBenefitState'][$this->search['goodsBenefitState']] = $this->checked['goodsDiscountGroup'][$this->search['goodsDiscountGroup']] = $this->checked['benefitUseType'][$this->search['benefitUseType']] = 'checked="checked"';


        //처리일자 검색
        if ($this->search['searchDateFl'] && $this->search['searchDate'][0] && $this->search['searchDate'][1]) {
            $this->arrWhere[] = $this->search['searchDateFl'] . ' BETWEEN ? AND ?';
            $this->db->bind_param_push($this->arrBind, 's', $this->search['searchDate'][0] . ' 00:00:00');
            $this->db->bind_param_push($this->arrBind, 's', $this->search['searchDate'][1] . ' 23:59:59');
        }

        // 혜택명 검색
        if ($this->search['benefitNm']) {
            $this->arrWhere[] = 'benefitNm LIKE concat(\'%\',?,\'%\')';
            $this->db->bind_param_push($this->arrBind, 's', $this->search['benefitNm']);
        }

        // 혜택 진행 유형 검색
        if ($this->search['benefitUseType']) {
            $this->arrWhere[] = 'benefitUseType = ?';
            $this->db->bind_param_push($this->arrBind, 's', $this->search['benefitUseType']);
        }

        // 혜택 대상 검색
        if ($this->search['goodsDiscountGroup']) {
            $this->arrWhere[] = 'goodsDiscountGroup = ?';
            $this->db->bind_param_push($this->arrBind, 's', $this->search['goodsDiscountGroup']);
        }

        if ($this->search['goodsBenefitState'] !='') {
            switch ($this->search['goodsBenefitState']) {
                //진행중
                case 'n':
                    $arrWhereAll = array();
                    $arrWhereAll[] = 'benefitUseType = ? and periodDiscountStart < ? and periodDiscountEnd > ? ';
                    $this->db->bind_param_push($this->arrBind, 's', 'periodDiscount');
                    $this->db->bind_param_push($this->arrBind, 's', date('Y-m-d H:i:s'));
                    $this->db->bind_param_push($this->arrBind, 's', date('Y-m-d H:i:s'));
                    //특정기간 할인이 아닌것은 진행중상태
                    $arrWhereAll[] = 'benefitUseType != ? ';
                    $this->db->bind_param_push($this->arrBind, 's', 'periodDiscount');
                    $this->arrWhere[] = '(' . implode(' OR ', $arrWhereAll) . ')';
                    unset($arrWhereAll);
                    break;
                //종료
                case 'e':
                    $this->arrWhere[] = 'benefitUseType = ?';
                    $this->db->bind_param_push($this->arrBind, 's', 'periodDiscount');
                    $this->arrWhere[] = 'periodDiscountEnd < ? ';
                    $this->db->bind_param_push($this->arrBind, 's', date('Y-m-d H:i:s'));
                    break;
                //대기
                case 'd':
                    $this->arrWhere[] = 'benefitUseType = ?';
                    $this->db->bind_param_push($this->arrBind, 's', 'periodDiscount');
                    $this->arrWhere[] = 'periodDiscountStart > ? ';
                    $this->db->bind_param_push($this->arrBind, 's', date('Y-m-d H:i:s'));
                    break;
            }
        }

        //혜택선택 레이어 - 특정기간 할인 && 대기,진행중 데이터 출력
        if($mode == 'layer_benefit'){
            //현재 수정하는 혜택은 노출되지 않게
            $this->arrWhere[] = 'sno != ?';
            $this->db->bind_param_push($this->arrBind, 's', $getValue['goodsBenefitSno']);
            $this->arrWhere[] = 'benefitUseType = ?';
            $this->db->bind_param_push($this->arrBind, 's', 'periodDiscount');
            $this->arrWhere[] = 'periodDiscountEnd > ? ';
            $this->db->bind_param_push($this->arrBind, 's', date('Y-m-d H:i:s'));
        }

        if($mode == 'layer' || $mode == 'layer_search_page'){
            //종료된 혜택은 노출되지 않게
            $this->arrWhere[] = 'if(benefitUseType = ?,periodDiscountEnd > ? ,true)';
            $this->db->bind_param_push($this->arrBind, 's', 'periodDiscount');
            $this->db->bind_param_push($this->arrBind, 's', date('Y-m-d H:i:s'));
        }


        if (empty($this->arrBind)) {
            $this->arrBind = null;
        }

        // --- 정렬 설정
        $sort = $this->search['sort'];

        if ($mode == 'layer' || $mode == 'layer_search_page') {
            // --- 페이지 기본설정
            if (gd_isset($getValue['pagelink'])) {
                $getValue['page'] = (int)str_replace('page=', '', preg_replace('/^{page=[0-9]+}/', '', gd_isset($getValue['pagelink'])));
            } else {
                $getValue['page'] = 1;
            }
            gd_isset($getValue['pageNum'], '10');
        } else {
            // --- 페이지 기본설정
            gd_isset($getValue['page'], 1);
            gd_isset($getValue['pageNum'], 10);
        }

        $strSQL = 'SELECT count(*) as cnt  FROM ' . DB_GOODS_BENEFIT ;
        list($result) = $this->db->query_fetch($strSQL);
        $totalCnt = $result['cnt'];

        $page = \App::load('\\Component\\Page\\Page', $getValue['page']);
        $page->page['list'] = $getValue['pageNum']; // 페이지당 리스트 수
        $page->recode['amount'] = $totalCnt; // 전체 레코드 수
        $page->setPage();
        $page->setUrl(\Request::getQueryString());

        $this->db->strField = "*";
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
        $this->db->strOrder = $sort;
        $this->db->strLimit = $page->recode['start'] . ',' . $getValue['pageNum'];

        //$data = $this->getGoodsBenefitInfo();

        if (is_null($this->db->strField)) {
            $arrField = DBTableField::setTableField('tableGoodsBenefit');
            $this->db->strField = implode(', ', $arrField);
        }

        if (empty($this->arrBind)) {
            $this->arrBind = null;
        }

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_GOODS_BENEFIT . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $this->arrBind);
        $data =  gd_htmlspecialchars_stripslashes(gd_isset($data));

        if($this->arrWhere == ''){
            $where =  implode(' AND ', $this->arrWhere);
        }else{
            $where = ' WHERE '. implode(' AND ', $this->arrWhere);
        }
        $totalCountSQL = 'SELECT COUNT(*) AS totalCnt FROM ' . DB_GOODS_BENEFIT .' USE INDEX (PRIMARY) '.$where;
        $dataCount = $this->db->query_fetch($totalCountSQL, $this->arrBind,false);
        unset($this->arrBind, $this->arrWhere);

        // 검색 레코드 수
        $page->recode['total'] = $dataCount['totalCnt']; //검색 레코드 수
        $page->setPage();

        foreach ($data as $key => $value) {

            //아이콘
            $value['goodsIconCd'] = $this->getBenefitIcon($value['sno']);

            //무제한 아이콘
            if ($value['goodsIconCd']) {
                $tmp['goodsIcon'] = $Goods->getGoodsIcon($value['goodsIconCd']);
            }
            $data[$key]['goodsIcon'] = $tmp['goodsIcon'];
            unset($tmp);
        }

        // 각 데이터 배열화
        $getData['data'] = gd_htmlspecialchars_stripslashes(gd_isset($data));
        $getData['sort'] = $sort;
        $getData['search'] = gd_htmlspecialchars($this->search);
        $getData['checked'] = $this->checked;
        $getData['selected'] = $this->selected;

        return $getData;
    }


    /**
     * 상품 혜택 정보
     *
     * @return array 상품 혜택 정보
     */
    public function getGoodsBenefitInfo()
    {
        if (is_null($this->db->strField)) {
            $arrField = DBTableField::setTableField('tableGoodsBenefit');
            $this->db->strField = implode(', ', $arrField);
        }

        if (empty($this->arrBind)) {
            $this->arrBind = null;
        }

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_GOODS_BENEFIT . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $this->arrBind);
        unset($this->arrBind);
        return gd_htmlspecialchars_stripslashes(gd_isset($data));
    }

    /**
     * 상품 혜택의 등록 및 수정에 관련된 정보
     *
     * @param integer $benefitSno 상품혜택번호 sno
     * @return array 상품 혜택 정보
     */
    public function getGoodsBenefit($benefitSno = null)
    {

        // --- 등록인 경우
        if (is_null($benefitSno)) {
            // 기본 정보
            $data['mode'] = 'register';
            $data['sno'] = null;

            // 기본값 설정
            DBTableField::setDefaultData('tableGoodsBenefit', $data);

            // --- 수정인 경우
        } else {

            $this->db->strWhere = 'sno = ?';
            $this->db->bind_param_push($this->arrBind, 'i', $benefitSno);
            $tmp = $this->getGoodsBenefitInfo();
            $data = $tmp[0];
            $data['mode'] = 'modify';

            // 기본값 설정
            DBTableField::setDefaultData('tableGoodsBenefit', $data);

            $data['fixedGoodsDiscount'] = array_filter(explode(STR_DIVISION, $data['fixedGoodsDiscount']));

            $data['exceptBenefit'] = array_filter(explode(STR_DIVISION, $data['exceptBenefit']));
            $data['exceptBenefitGroupInfo'] = array_filter(explode(INT_DIVISION, $data['exceptBenefitGroupInfo']));

            $data['goodsIconCd'] = $this->getBenefitIcon($data['sno']);
        }

        if (empty($data['goodsDiscountGroupMemberInfo']) === false) {
            $data['goodsDiscountGroupMemberInfo'] = json_decode($data['goodsDiscountGroupMemberInfo'], true);
            foreach ($data['goodsDiscountGroupMemberInfo']['goodsDiscountUnit'] as $key => $val) {
                $selected['goodsDiscountGroupMemberInfo']['goodsDiscountUnit'][$key][$val] = 'selected="selected"';
            }
        }

        $selected['goodsDiscountUnit'][$data['goodsDiscountUnit']] = $selected['newGoodsRegFl'][$data['newGoodsRegFl']] = $selected['newGoodsDateFl'][$data['newGoodsDateFl']] = 'selected="selected"';

        $checked = array();

        gd_isset($data['goodsDiscountGroup'], 'all');
        gd_isset($data['exceptBenefitGroup'], 'all');
        gd_isset($data['benefitUseType'], 'nonLimit');
        gd_isset($data['benefitScheduleFl'], 'n');

        gd_isset($data['periodDiscountStart'], date("Y-m-d")." 00:00");
        gd_isset($data['periodDiscountEnd'], date("Y-m-d")." 23:59");

        if($data['periodDiscountStart'] == "0000-00-00 00:00:00") $data['periodDiscountStart'] = date("Y-m-d")." 00:00";
        if($data['periodDiscountEnd'] == "0000-00-00 00:00:00") $data['periodDiscountEnd'] = date("Y-m-d")." 23:59";

        $checked['benefitScheduleFl'][$data['benefitScheduleFl']] = $checked['benefitUseType'][$data['benefitUseType']] = $checked['goodsDiscountFl'][$data['goodsDiscountFl']] = $checked['goodsDiscountGroup'][$data['goodsDiscountGroup']] = $checked['exceptBenefitGroup'][$data['exceptBenefitGroup']] = 'checked="checked"';

        // 무제한용
        if (!empty($data['goodsIconCd'])) {
            $goodsIconCd = explode(INT_DIVISION, $data['goodsIconCd']);
            //unset($data['goodsIconCd']);
            foreach ($goodsIconCd as $key => $val) {
                $checked['goodsIconCd'][$val] = 'checked="checked"';
            }
        }

        $getData['data'] = $data;
        $getData['checked'] = $checked;
        $getData['selected'] = $selected;

        return $getData;
    }


    /**
     * setGoodsBenefit
     * 상품혜택 설정
     *
     * @param array $arrData
     *
     * @return array
     */
    public function setGoodsBenefit($arrData) {

        if($arrData['mode'] == 'modify') {
            $tmp = $this->getGoodsBenefit($arrData['sno']); //수정여부 체킹시 필요
            $modifyBenefitData = $tmp['data'];
        }

        if($arrData['benefitUseType'] == 'newGoodsDiscount'){
            unset($arrData['periodDiscountStart'], $arrData['periodDiscountEnd']); //해당 타입의 불필요한 변수 삭제
            unset($arrData['benefitScheduleFl'], $arrData['benefitScheduleNextSno'], $arrData['benefitSchedulePrevSno']);

        }else if($arrData['benefitUseType'] == 'periodDiscount'){

            $arrData['periodDiscountStart'] = $arrData['periodDiscountStart'].':00';
            $arrData['periodDiscountEnd'] = $arrData['periodDiscountEnd'].':00';
            if ($arrData['periodDiscountEnd'] <= date("Y-m-d H:i:s")) {
                throw new \Exception(__('특정기간 할인 종료일이 현재시간 보다 과거입니다.'));
            }
            if($arrData['mode'] == 'modify') {
                //이전 혜택이 있으면 시작일이 이전일보다 빠르게 수정하면 안됨
                $this->db->strWhere = 'benefitUseType = ? AND sno = ?';
                $this->db->bind_param_push($this->arrBind, 's', 'periodDiscount');
                $this->db->bind_param_push($this->arrBind, 'i', $modifyBenefitData['benefitSchedulePrevSno']);
                $tmp = $this->getGoodsBenefitInfo();
                if ($tmp[0]['periodDiscountStart'] >= $arrData['periodDiscountStart']) {
                    throw new \Exception(__('이전 혜택 시간보다 빠르게 지정할 수 없습니다. 혜택을 다시 선택해주세요.'));
                }
            }

            unset($arrData['newGoodsDate'],$arrData['newGoodsRegFl'],$arrData['newGoodsDateFl']); //불필요한 변수 삭제
        }else{

            unset($arrData['benefitScheduleFl'], $arrData['benefitScheduleNextSno'], $arrData['benefitSchedulePrevSno']);
            unset($arrData['newGoodsDate'], $arrData['newGoodsRegFl'], $arrData['newGoodsDateFl'], $arrData['periodDiscountStart'], $arrData['periodDiscountEnd']); //해당 타입의 불필요한 변수 삭제

        }

        $nextScheduleChange = false; //다음 혜택에 걸려 있던 상품 링크 재연결 여부 체크

        //다음 혜택 예약설정 사용
        if($arrData['benefitScheduleFl'] == 'y'){
            if (!gd_isset($arrData['benefitScheduleNextSno'])) {
                throw new \Exception(__('다음 혜택이 선택이 되어 있지 않습니다. 혜택을 다시 선택해주세요.'));
            }

            //다음 혜택 변경이 되었다면
            if($arrData['mode'] == 'modify' && $arrData['benefitScheduleNextSno'] != $modifyBenefitData['benefitScheduleNextSno']){
                $nextScheduleChange = true;
            }

            if($arrData['mode'] != 'modify' || $nextScheduleChange == true) {
                //다음 혜택 예약 설정 유무
                $this->db->strWhere = 'benefitUseType = ? AND benefitScheduleNextSno = ? AND benefitScheduleNextSno != 0';
                $this->db->bind_param_push($this->arrBind, 's', 'periodDiscount');
                $this->db->bind_param_push($this->arrBind, 'i', $arrData['benefitScheduleNextSno']);
                $tmp = $this->getGoodsBenefitInfo();
                if (count($tmp[0]) > 0) {
                    throw new \Exception(__('다음 혜택 예약설정으로 선택한 혜택은 이미 ' . $tmp[0]['benefitNm'] . ' 혜택의 다음 혜택으로 설정이 되어 있습니다.'));
                };
            }

            //다음 혜택 시작일 체크
            $this->db->strWhere = 'benefitUseType = ? AND sno = ?';
            $this->db->bind_param_push($this->arrBind, 's', 'periodDiscount');
            $this->db->bind_param_push($this->arrBind, 'i', $arrData['benefitScheduleNextSno']);
            $tmp = $this->getGoodsBenefitInfo();

            if ($arrData['periodDiscountStart'] >= $tmp[0]['periodDiscountStart']) {
                throw new \Exception(__('다음 혜택 예약설정으로 선택한 혜택은 현재 혜택보다 시작일이 빠릅니다. 혜택을 다시 선택해주세요.'));
            }
            /*
						if ($arrData['periodDiscountEnd'] >= $tmp[0]['periodDiscountEnd']) {
							throw new \Exception(__('다음 혜택 예약설정으로 선택한 혜택은 현재 혜택보다 종료일이 빠릅니다. 혜택을 다시 선택해주세요.'));
						}
			*/

            if($nextScheduleChange) {
                $benefitNextSnoArry = array();
                $data = $this->getNextScheduleGoodsBenefit($arrData['sno']);
                foreach ($data as $key => $value) {
                    $benefitNextSnoArry[] = $value['sno'];
                }
                $benefitNextSnoArry = array_filter($benefitNextSnoArry);
            }
        }

        // 상품혜택 아이콘 등록
        /*
        if (isset($arrData['goodsIconCd'])) {
            $arrData['goodsIconCd'] = implode(INT_DIVISION, $arrData['goodsIconCd']);
        } else {
            $arrData['goodsIconCd'] ="";
        }
        */

        //특정회원등급 등록시
        $arrData['fixedGoodsDiscount'] = @implode(STR_DIVISION, $arrData['fixedGoodsDiscount']);
        $arrData['goodsDiscountGroupMemberInfo'] = str_replace('\'', '', json_encode($arrData['goodsDiscountGroupMemberInfo'], JSON_UNESCAPED_UNICODE));
        $arrData['exceptBenefit'] = @implode(STR_DIVISION, $arrData['exceptBenefit']);
        if (empty($arrData['exceptBenefit']) === true) $arrData['exceptBenefitGroup'] = '';
        if ($arrData['exceptBenefitGroup'] == 'group') {
            $arrData['exceptBenefitGroupInfo'] = @implode(INT_DIVISION, $arrData['exceptBenefitGroupInfo']);
        } else {
            $arrData['exceptBenefitGroupInfo'] = '';
        }

        if ($arrData['mode'] == 'modify') {
            $chkType = 'update';
        } else {
            $chkType = 'insert';
        }

        $arrBind = $this->db->get_binding(DBTableField::tableGoodsBenefit(), $arrData, $chkType);

        if ($chkType == 'insert') { //헤택 등록
            $this->db->set_insert_db(DB_GOODS_BENEFIT, $arrBind['param'], $arrBind['bind'], 'y');
            $prevSno = $this->db->insert_id();

            //통합 아이콘 테이블 등록
            foreach($arrData['goodsIconCd'] as $icon) {
                $arrBind = [];
                $strSQL = "INSERT INTO ".DB_GOODS_ICON." SET `benefitSno`=?, `goodsIconCd` = ?,`iconKind`='pr',`regDt`=now()";
                $this->db->bind_param_push($arrBind, 'i', $prevSno);
                $this->db->bind_param_push($arrBind, 's', $icon);
                $this->db->bind_query($strSQL, $arrBind);
                unset($arrBind);
            }

            \Logger::channel('goodsBenefit')->info('상품혜택 등록', $arrData);

            //이전 헤택 정보 업데이트
            if($arrData['benefitScheduleFl'] == 'y' && $arrData['benefitScheduleNextSno']){

                $updateKey['benefitSchedulePrevSno'] = $prevSno;
                $compareField = array_keys($updateKey);
                $arrBind = $this->db->get_binding(DBTableField::tableGoodsBenefit(), $updateKey, 'update', $compareField);
                $this->db->bind_param_push($arrBind['bind'], 'i', $arrData['benefitScheduleNextSno']);
                $this->db->set_update_db(DB_GOODS_BENEFIT, $arrBind['param'], 'sno = ?', $arrBind['bind']);
            }
        }
        else if ($chkType == 'update') { //헤택 수정
            $this->db->bind_param_push($arrBind['bind'], 'i', $arrData['sno']);
            $this->db->set_update_db(DB_GOODS_BENEFIT, $arrBind['param'], 'sno = ?', $arrBind['bind']);

            //통합 아이콘 테이블 등록
            $this->db->query_fetch("DELETE FROM ".DB_GOODS_ICON." WHERE iconKind = 'pr' AND benefitSno = '".$arrData['sno']."'");
            foreach($arrData['goodsIconCd'] as $icon) {
                $arrBind = [];
                $strSQL = "INSERT INTO ".DB_GOODS_ICON." SET `benefitSno`=?, `goodsIconCd` = ?,`iconKind`='pr',`regDt`=now()";
                $this->db->bind_param_push($arrBind, 'i', $arrData['sno']);
                $this->db->bind_param_push($arrBind, 's', $icon);
                $this->db->bind_query($strSQL, $arrBind);
                unset($arrBind);
            }


            \Logger::channel('goodsBenefit')->info('상품혜택 수정', $arrData);

            //이전 헤택 정보 업데이트
            if($arrData['benefitScheduleFl'] == 'y' && $arrData['benefitScheduleNextSno']){

                //기존의 이전 혜택 정보를 초기화 하고
                $updateKey['benefitSchedulePrevSno'] = 0;
                $compareField = array_keys($updateKey);
                $arrBind = $this->db->get_binding(DBTableField::tableGoodsBenefit(), $updateKey, 'update', $compareField);
                $this->db->bind_param_push($arrBind['bind'], 'i', $modifyBenefitData['benefitScheduleNextSno']);
                $this->db->set_update_db(DB_GOODS_BENEFIT, $arrBind['param'], 'sno = ?', $arrBind['bind']);

                //이전 혜택 정보 재 수정
                $updateKey['benefitSchedulePrevSno'] = $arrData['sno'];
                $compareField = array_keys($updateKey);
                $arrBind = $this->db->get_binding(DBTableField::tableGoodsBenefit(), $updateKey, 'update', $compareField);
                $this->db->bind_param_push($arrBind['bind'], 'i', $arrData['benefitScheduleNextSno']);
                $this->db->set_update_db(DB_GOODS_BENEFIT, $arrBind['param'], 'sno = ?', $arrBind['bind']);
            }
        }

        /*
         혜택 등록후 링크 재연결
        */
        if($arrData['mode'] == 'modify' && $arrData['benefitUseType'] == 'nonLimit' ){

            //수정시 진행유형이 변경되었다면
            if($arrData['benefitUseType'] != $modifyBenefitData['benefitUseType']  ) {

                $arrWhere = array();
                $arrBind = array();
                $arrWhere[] = "benefitSno = ?";
                $this->db->bind_param_push($arrBind, 'i', $arrData['sno']);

                //상품혜택링크에서 해당 해택이 포함되어 있는 데이터만 가져옴
                $strSQL = 'SELECT * FROM ' . DB_GOODS_LINK_BENEFIT . ' WHERE ' . implode(' AND ', gd_isset($arrWhere));
                $benefitLink = $this->db->query_fetch($strSQL, $arrBind);

                //해당 상품연결 데이터 삭제
                foreach ($benefitLink as $v) {
                    if ( $v['goodsNo']) {
                        $this->delGoodsLink($v['goodsNo']);
                    }
                }
                //해당 상품연결 데이터 재설정
                foreach ($benefitLink as $v) {
                    if ( $v['goodsNo']) {
                        $linkData = array();
                        $linkData['goodsNo'] = $v['goodsNo'];
                        $linkData['benefitSno'] = $arrData['sno'];
                        $linkData['benefitUseType'] = $arrData['benefitUseType'];
                        //$linkData['goodsIconCd'] = $arrData['goodsIconCd'];

                        $arrBind = $this->db->get_binding(DBTableField::tableGoodsLinkBenefit(), $linkData, 'insert');
                        $this->db->set_insert_db(DB_GOODS_LINK_BENEFIT, $arrBind['param'], $arrBind['bind'], 'y');

                        \Logger::channel('goodsBenefit')->info('상품혜택 링크저장<제한없음>', $linkData);
                    }
                }

            }

        }
        else if($arrData['mode'] == 'modify' && $arrData['benefitUseType'] == 'newGoodsDiscount'){
            //수정시 진행유형이 변경되었다면
            if($arrData['benefitUseType'] != $modifyBenefitData['benefitUseType']  || ($arrData['newGoodsDate'] != $modifyBenefitData['newGoodsDate']) || ($arrData['newGoodsRegFl'] != $modifyBenefitData['newGoodsRegFl']) || ($arrData['newGoodsDateFl'] != $modifyBenefitData['newGoodsDateFl'])) {

                $arrWhere = array();
                $arrBind = array();
                $arrWhere[] = "benefitSno = ?";
                $this->db->bind_param_push($arrBind, 'i', $arrData['sno']);

                //상품혜택링크에서 해당 해택이 포함되어 있는 데이터만 가져옴
                $strSQL = 'SELECT goodsNo,benefitSno,benefitUseType FROM ' . DB_GOODS_LINK_BENEFIT . ' WHERE ' . implode(' AND ', gd_isset($arrWhere));
                $benefitLink = $this->db->query_fetch($strSQL, $arrBind);

                if(!empty($benefitLink)) {
                    //해당 상품연결 데이터 삭제
                    $goodsNoArray = array();
                    foreach ($benefitLink as $v) {
                        if ($v['goodsNo']) {
                            $this->delGoodsLink($v['goodsNo']);
                            $goodsNoArray[] = $v['goodsNo'];
                        }
                    }

                    $strSQL = 'SELECT goodsNo,regDt,modDt from ' . DB_GOODS . ' WHERE goodsNo in (' . implode(' , ', $goodsNoArray) . ')';
                    $tmp = $this->db->query_fetch($strSQL);
                    $goodsData = array();
                    foreach ($tmp as $v) {
                        $goodsData[$v['goodsNo']] = $v;
                    }

                    //해당 상품연결 데이터 재설정
                    foreach ($benefitLink as $v) {
                        if ($v['goodsNo']) {

                            if ($arrData['newGoodsDateFl'] == 'day') { //신상품 할인 기간이 일인 경우
                                $endTime = date("Y-m-d", strtotime("+" . $arrData['newGoodsDate'] . " day", strtotime($goodsData[$v['goodsNo']][$arrData['newGoodsRegFl']]))) . " 23:59:59";
                                if($arrData['newGoodsRegFl'] == 'regDt') {
                                    $todayTime = $goodsData[$v['goodsNo']]['regDt'];
                                }else{
                                    $todayTime = date("Y-m-d H:i:s");
                                }
                            } else { //신상품 할인 기간이 시간인 경우
                                $endTime = date("Y-m-d H:i:s", strtotime("+" . $arrData['newGoodsDate'] . " hour", strtotime($goodsData[$v['goodsNo']][$arrData['newGoodsRegFl']])));
                                if($arrData['newGoodsRegFl'] == 'regDt') {
                                    $todayTime = $goodsData[$v['goodsNo']]['regDt'];
                                }else{
                                    $todayTime = date("Y-m-d H:i:s");
                                }

                            }

                            $linkData = array();
                            $linkData['goodsNo'] = $v['goodsNo'];
                            $linkData['benefitSno'] = $arrData['sno'];
                            $linkData['benefitUseType'] = $arrData['benefitUseType'];
                            $linkData['linkPeriodStart'] = $todayTime;
                            $linkData['linkPeriodEnd'] = $endTime;
                            //$linkData['goodsIconCd'] = $arrData['goodsIconCd'];

                            $arrBind = $this->db->get_binding(DBTableField::tableGoodsLinkBenefit(), $linkData, 'insert');
                            $this->db->set_insert_db(DB_GOODS_LINK_BENEFIT, $arrBind['param'], $arrBind['bind'], 'y');

                            \Logger::channel('goodsBenefit')->info('상품혜택 링크저장<신상품>', $linkData);
                        }
                    }
                }

            }

        }
        else if($arrData['mode'] == 'modify' && $arrData['benefitUseType'] == 'periodDiscount'){ // 특정기간 할인

            //수정시 진행유형이 변경되었다면
            if( $arrData['benefitUseType'] != $modifyBenefitData['benefitUseType'] ) {

                $arrWhere = array();
                $arrBind = array();
                $arrWhere[] = "benefitSno = ?";
                $this->db->bind_param_push($arrBind, 'i', $arrData['sno']);

                //상품혜택링크에서 해당 해택이 포함되어 있는 데이터만 가져옴
                $strSQL = 'SELECT * FROM ' . DB_GOODS_LINK_BENEFIT . ' WHERE ' . implode(' AND ', gd_isset($arrWhere));
                $benefitLink = $this->db->query_fetch($strSQL, $arrBind);

                //해당 상품연결 데이터 삭제
                foreach ($benefitLink as $v) {
                    if ( $v['goodsNo']) {
                        $this->delGoodsLink($v['goodsNo']);
                    }
                }

                //해당 혜택 재설정
                $data = $this->getNextScheduleGoodsBenefit($arrData['sno']); //수정한것을 다시 불러옴
                foreach ($benefitLink as $v) {
                    foreach ($data as $key => $value) {
                        $linkData = array();
                        $linkData['goodsNo'] = $v['goodsNo'];
                        $linkData['benefitSno'] = $value['sno'];
                        $linkData['benefitUseType'] = $value['benefitUseType'];
                        $linkData['linkPeriodStart'] = $value['periodDiscountStart'];
                        $linkData['linkPeriodEnd'] = $value['periodDiscountEnd'];
                        //$linkData['goodsIconCd'] = $value['goodsIconCd'];

                        if ($v['goodsNo'] && $value['sno']) {

                            $arrBind = $this->db->get_binding(DBTableField::tableGoodsLinkBenefit(), $linkData, 'insert');
                            $this->db->set_insert_db(DB_GOODS_LINK_BENEFIT, $arrBind['param'], $arrBind['bind'], 'y');

                            \Logger::channel('goodsBenefit')->info('상품혜택 링크저장<특정기간-유형변경>', $linkData);
                        }
                    }
                }

            }else {  //이전 진행유형이 동일한 특정기간 할인인 경우
                //다음혜택 예약설정이 변경이 되었거나 시작일 종료일이 변경되었다면
                if ($nextScheduleChange || ($arrData['periodDiscountStart'] != $modifyBenefitData['periodDiscountStart']) || ($arrData['periodDiscountEnd'] != $modifyBenefitData['periodDiscountEnd']) ) {
                    $arrWhere = array();
                    $arrBind = array();
                    $arrWhere[] = "benefitUseType = 'periodDiscount'";
                    $arrWhere[] = "linkPeriodEnd > '" . date('Y-m-d H:i:s') . "'";
                    $arrWhere[] = "benefitSno = ?";
                    $this->db->bind_param_push($arrBind, 'i', $arrData['sno']);

                    //상품혜택링크에서 해당 해택이 포함되어 있는 대기중이나 진행중인 데이터만 가져옴
                    $strSQL = 'SELECT * FROM ' . DB_GOODS_LINK_BENEFIT . ' WHERE ' . implode(' AND ', gd_isset($arrWhere));
                    $benefitLink = $this->db->query_fetch($strSQL, $arrBind);

                    //해당 상품연결 데이터 삭제
                    foreach ($benefitLink as $v) {
                        if (!empty($benefitNextSnoArry) && $v['goodsNo']) {
                            $strSQL = 'DELETE FROM ' . DB_GOODS_LINK_BENEFIT . ' WHERE goodsNo = ' . $v['goodsNo'] . ' AND benefitSno in (' . implode(' , ', $benefitNextSnoArry) . ')';
                            $this->db->query_fetch($strSQL);
                            \Logger::channel('goodsBenefit')->info('상품혜택 링크삭제<특정기간>', array('benefitNextSno'=>$benefitNextSnoArry,'goodsNo'=>$v['goodsNo']));
                        }else{
                            $strSQL = 'DELETE FROM ' . DB_GOODS_LINK_BENEFIT . ' WHERE sno in (' . $v['sno'] . ')';
                            $this->db->query_fetch($strSQL);
                            \Logger::channel('goodsBenefit')->info('상품혜택 링크삭제<특정기간>', array('sno'=>$v['sno'],'goodsNo'=>$v['goodsNo']));

                        }
                    }

                    //해당 혜택 재설정
                    $data = $this->getNextScheduleGoodsBenefit($arrData['sno']); //수정한것을 다시 불러옴

                    foreach ($benefitLink as $v) {
                        foreach ($data as $key => $value) {
                            $arrData = array();
                            $arrData['goodsNo'] = $v['goodsNo'];
                            $arrData['benefitSno'] = $value['sno'];
                            $arrData['benefitUseType'] = $value['benefitUseType'];
                            $arrData['linkPeriodStart'] = $value['periodDiscountStart'];
                            $arrData['linkPeriodEnd'] = $value['periodDiscountEnd'];
                            //$arrData['goodsIconCd'] = $value['goodsIconCd'];

                            if ($v['goodsNo'] && $value['sno']) {

                                $arrBind = $this->db->get_binding(DBTableField::tableGoodsLinkBenefit(), $arrData, 'insert');
                                $this->db->set_insert_db(DB_GOODS_LINK_BENEFIT, $arrBind['param'], $arrBind['bind'], 'y');

                                \Logger::channel('goodsBenefit')->info('상품혜택 링크저장<특정기간>', $arrData);
                            }
                        }
                    }
                }

            }
        }
    }

    /**
     * 상품 혜택 삭제
     *
     * @param integer $datasno 삭제할 레코드 sno
     */
    public function setDeleteGoodsBenefit($sno) {

        $deleteBenefitData = $this->getGoodsBenefit($sno);

        if($deleteBenefitData == 'periodDiscount') { //기간혜택
            $arrWhere = array();
            $arrBind = array();
            $arrWhere[] = "benefitUseType = 'periodDiscount'";
            $arrWhere[] = "linkPeriodEnd > '" . date('Y-m-d H:i:s') . "'";
            $arrWhere[] = "benefitSno = ?";
            $this->db->bind_param_push($arrBind, 'i', $sno);

            //상품혜택링크에서 해당 해택이 포함되어 있는 대기중이나 진행중인 데이터만 가져옴
            $strSQL = 'SELECT * FROM ' . DB_GOODS_LINK_BENEFIT . ' WHERE ' . implode(' AND ', gd_isset($arrWhere));
            $benefitLink = $this->db->query_fetch($strSQL, $arrBind);

            //삭제할 혜택에 다음 혜택이 연결되어 있는지 조회
            $benefitNextSnoArry = array();
            $data = $this->getNextScheduleGoodsBenefit($sno);

            foreach ($data as $key => $value) {
                $benefitNextSnoArry[] = $value['sno'];
            }
            $benefitNextSnoArry = array_filter($benefitNextSnoArry);

            //해당 상품연결 데이터 삭제
            foreach ($benefitLink as $v) {
                if (!empty($benefitNextSnoArry) && $v['goodsNo']) {
                    $strSQL = 'DELETE FROM ' . DB_GOODS_LINK_BENEFIT . ' WHERE goodsNo = ' . $v['goodsNo'] . ' AND benefitSno in (' . implode(' , ', $benefitNextSnoArry) . ')';
                    $this->db->query_fetch($strSQL);

                    \Logger::channel('goodsBenefit')->info('상품혜택 삭제시-상품혜택 링크삭제<특정기간>', array('benefitNextSno'=>$benefitNextSnoArry,'goodsNo'=>$v['goodsNo']));
                }else{
                    $strSQL = 'DELETE FROM ' . DB_GOODS_LINK_BENEFIT . ' WHERE sno in (' . $v['sno'] . ')';
                    $this->db->query_fetch($strSQL);

                }
            }

            //삭제하려는 혜택이 다음 혜택에 연결되어 있다면 update
            $arrBind = $this->db->get_binding(DBTableField::tableGoodsBenefit(), array("benefitScheduleNextSno" => 0), 'update', array('benefitScheduleNextSno'));
            $this->db->bind_param_push($arrBind['bind'], 'i', $sno);
            $this->db->set_update_db(DB_GOODS_BENEFIT, $arrBind['param'], 'benefitScheduleNextSno = ?', $arrBind['bind']);

            //삭제하려는 혜택이 이전 혜택에 연결되어 있다면 update
            $arrBind = $this->db->get_binding(DBTableField::tableGoodsBenefit(), array("benefitSchedulePrevSno" => 0), 'update', array('benefitSchedulePrevSno'));
            $this->db->bind_param_push($arrBind['bind'], 'i', $sno);
            $this->db->set_update_db(DB_GOODS_BENEFIT, $arrBind['param'], 'benefitSchedulePrevSno = ?', $arrBind['bind']);

        }else{ //제한없음,신상품할인

            $arrWhere = array();
            $arrBind = array();
            $arrWhere[] = "benefitSno = ?";
            $this->db->bind_param_push($arrBind, 'i', $sno);

            //상품혜택링크에서 해당 해택이 포함되어 있는 데이터만 가져옴
            $strSQL = 'SELECT * FROM ' . DB_GOODS_LINK_BENEFIT . ' WHERE ' . implode(' AND ', gd_isset($arrWhere));
            $benefitLink = $this->db->query_fetch($strSQL, $arrBind);

            //해당 상품연결 데이터 삭제
            foreach ($benefitLink as $v) {
                if ( $v['goodsNo']) {
                    $this->delGoodsLink($v['goodsNo']);

                    $goodsData['goodsBenefitSetFl'] = 'n';
                    $compareField = array_keys($goodsData);
                    $arrBind = $this->db->get_binding(DBTableField::tableGoods(), $goodsData, 'update', $compareField);
                    $this->db->bind_param_push($arrBind['bind'], 'i', $v['goodsNo']);
                    $this->db->set_update_db(DB_GOODS, $arrBind['param'], 'goodsNo = ?', $arrBind['bind']);
                    unset($arrBind, $goodsData);
                }
            }

        }
        //해당 혜택 삭제
        $strWhere = 'sno = ?';
        $this->db->bind_param_push($this->arrBind, 'i', $sno);
        $this->db->strWhere = $strWhere;
        $this->db->set_delete_db(DB_GOODS_BENEFIT, $strWhere, $this->arrBind);
        unset($this->arrBind);

        //해당 혜택 아이콘 삭제
        $this->db->query_fetch("DELETE FROM ".DB_GOODS_ICON." WHERE iconKind = 'pr' AND benefitSno = '".$sno."'");

        \Logger::channel('goodsBenefit')->info('상품혜택 식제['.$deleteBenefitData['benefitNm'].']', $deleteBenefitData);

    }

    /**
     * 상품 기간 할인 다음 혜택 예약 연결 sno 조회
     *
     * @param array $dataSno 다음혜택  레코드 sno
     *
     * @return array
     */
    public function getNextScheduleGoodsBenefit($benefitSno)
    {
        $arrWhere = array();
        $arrWhere[] = "benefitUseType = 'periodDiscount'";
        $arrWhere[] = "periodDiscountEnd > '". date('Y-m-d H:i:s')."'" ;
        $strSQL = 'SELECT * FROM ' . DB_GOODS_BENEFIT . ' WHERE ' .implode(' AND ', gd_isset($arrWhere));
        $data = $this->db->query_fetch($strSQL, null);

        $tempArry = $nextTempArry = array();
        foreach($data as $k => $v){
            $tempArry[$v['sno']] = $v;
        }

        $nextTempArry[] = $tempArry[$benefitSno];
        $nextArry = $this->nextScheduleCall($tempArry,$benefitSno,$nextTempArry);
        unset($tempArry,$nextTempArry);

        return $nextArry;

    }

    /**
     * 다음혜택 상품 가져오는 재귀함수
     *
     * @param array $benefitAll 전체상품혜택번호,integer $benefitSno 상품혜택번호,array $nextArry  다음혜택번호
     * @return array 상품 혜택 sno
     */

    public function nextScheduleCall($benefitAll,$benefitSno,$nextArry){
        if( $benefitAll[$benefitSno]['benefitScheduleNextSno'] >  0){
            $nextArry[] = $benefitAll[$benefitAll[$benefitSno]['benefitScheduleNextSno']];
            return $this->nextScheduleCall($benefitAll,$benefitAll[$benefitSno]['benefitScheduleNextSno'],$nextArry);
        }else{
            return $nextArry;
        }
    }


    /**
     * 상품 혜택 링크 저장
     *
     * @param integer $sno 상품혜택번호,integer $goodsno 상품번호
     */
    public function addGoodsLink($sno,$goodsno)
    {

        $data = $this->getGoodsBenefit($sno);
        $benefitData =  $data['data'];

        $arrData['goodsNo'] = $goodsno;
        $arrData['benefitSno'] = $sno;
        $arrData['benefitUseType'] = $benefitData['benefitUseType'];
        //$arrData['goodsIconCd'] = $benefitData['goodsIconCd'];

        //기존 링크 삭제 후 등록 처리
        $arrBind = array();
        $strWhere = 'goodsNo = ?';
        $this->db->bind_param_push($arrBind, 'i', $arrData['goodsNo']);
        $this->db->set_delete_db(DB_GOODS_LINK_BENEFIT, $strWhere, $arrBind);
        unset($arrBind);

        if($benefitData['benefitUseType'] == 'periodDiscount'){ // 기간할인

            $data = $this->getNextScheduleGoodsBenefit($arrData['benefitSno']);
            foreach($data as $key => $value){
                $arrData = array();
                $arrData['goodsNo'] = $goodsno;
                $arrData['benefitSno'] = $value['sno'];
                $arrData['benefitUseType'] = $value['benefitUseType'];
                $arrData['linkPeriodStart'] = $value['periodDiscountStart'];
                $arrData['linkPeriodEnd'] = $value['periodDiscountEnd'];
                //$arrData['goodsIconCd'] = $value['goodsIconCd'];
                if($goodsno && $value['sno']) {
                    $arrBind = $this->db->get_binding(DBTableField::tableGoodsLinkBenefit(), $arrData, 'insert');
                    $this->db->set_insert_db(DB_GOODS_LINK_BENEFIT, $arrBind['param'], $arrBind['bind'], 'y');

                    \Logger::channel('goodsBenefit')->info('상품혜택 링크저장<기간할인>', $arrData);
                }
            }

        }
        else if($benefitData['benefitUseType'] == 'newGoodsDiscount'){ //신상품

            $strSQL ='SELECT regDt,modDt from ' .DB_GOODS. ' WHERE goodsNo = '.$goodsno;
            $tmp = $this->db->query_fetch($strSQL);
            $goodsData = $tmp[0];

            if ($benefitData['newGoodsDateFl'] == 'day') { //신상품 할인 기간이 일인 경우
                $endTime = date("Y-m-d", strtotime("+" . $benefitData['newGoodsDate'] . " day", strtotime($goodsData[$benefitData['newGoodsRegFl']])))." 23:59:59";
                $todayTime = date("Y-m-d H:i:s");
                if($benefitData['newGoodsRegFl'] == 'regDt'){
                    $arrData['linkPeriodStart'] = $goodsData['regDt'];
                    $arrData['linkPeriodEnd'] = $endTime;
                }else{
                    $arrData['linkPeriodStart'] = $todayTime;
                    $arrData['linkPeriodEnd'] = $endTime;
                }

            } else { //신상품 할인 기간이 시간인 경우
                $endTime = date("Y-m-d H:i:s",strtotime("+" . $benefitData['newGoodsDate'] . " hour", strtotime($goodsData[$benefitData['newGoodsRegFl']])));
                $todayTime = date("Y-m-d H:i:s");
                if($benefitData['newGoodsRegFl'] == 'regDt'){
                    $arrData['linkPeriodStart'] = $goodsData['regDt'];
                    $arrData['linkPeriodEnd'] = $endTime;
                }else{
                    $arrData['linkPeriodStart'] = $todayTime;
                    $arrData['linkPeriodEnd'] = $endTime;
                }

            }

            $arrBind = $this->db->get_binding(DBTableField::tableGoodsLinkBenefit(), $arrData, 'insert');
            $this->db->set_insert_db(DB_GOODS_LINK_BENEFIT, $arrBind['param'], $arrBind['bind'], 'y');

            \Logger::channel('goodsBenefit')->info('상품혜택 링크저장<신상품>', $arrData);
        }
        else {//제한없음

            $arrBind = $this->db->get_binding(DBTableField::tableGoodsLinkBenefit(), $arrData, 'insert');
            $this->db->set_insert_db(DB_GOODS_LINK_BENEFIT, $arrBind['param'], $arrBind['bind'], 'y');

            \Logger::channel('goodsBenefit')->info('상품혜택 링크저장<제한없음>', $arrData);

        }

    }


    /**
     * 상품 혜택 링크 삭제
     *
     *  @param integer $goodsno
     *
     */
    public function delGoodsLink($goodsno){

        $strWhere = 'goodsno = ?';
        $this->db->bind_param_push($this->arrBind, 'i', $goodsno);
        $this->db->strWhere = $strWhere;
        $this->db->set_delete_db(DB_GOODS_LINK_BENEFIT, $strWhere, $this->arrBind);
        unset($this->arrBind);

        \Logger::channel('goodsBenefit')->info('상품혜택 링크삭제', array($goodsno));

    }

    /**
     * 상품 혜택 링크 정보
     *
     *  @param integer $goodsno
     *
     *
     *  @return array 혜택정보
     */
    public function getGoodsLink($goodsno,$front=false)
    {
        $arrWhere = $arrBind = array();
        $arrWhere[] = 'gbl.goodsNo = ? ';
        $this->db->bind_param_push($arrBind, 'i', $goodsno);

        if($front){
            $arrWhere[] = 'IF(gbl.benefitUseType = ? , gbl.linkPeriodStart < ? and gbl.linkPeriodEnd > ? , TRUE ) ';
            $this->db->bind_param_push($arrBind, 's', 'periodDiscount');
            $this->db->bind_param_push($arrBind, 's', date('Y-m-d H:i:s'));
            $this->db->bind_param_push($arrBind, 's', date('Y-m-d H:i:s'));
        }else{
            $arrWhere[] = 'IF(gbl.benefitUseType = ? , gbl.linkPeriodEnd > ? , TRUE ) ';
            $this->db->bind_param_push($arrBind, 's', 'periodDiscount');
            $this->db->bind_param_push($arrBind, 's', date('Y-m-d H:i:s'));
        }

        $join[] = ' LEFT JOIN ' . DB_GOODS_LINK_BENEFIT . ' as gbl ON gb.sno = gbl.benefitSno ';

        $strField = "gb.*";
        $strJoin = implode('', $join);
        $strWhere = implode(' AND ', gd_isset($arrWhere));

        $strSQL = 'SELECT ' . $strField . ' FROM ' . DB_GOODS_BENEFIT . ' gb ' . $strJoin.' WHERE '.$strWhere.' ORDER BY linkPeriodStart ASC LIMIT 1';
        $tmp = $this->db->query_fetch($strSQL, $arrBind);
        $data = $tmp[0];

        //통합 아이콘 테이블에서 가져옴
        $data['goodsIconCd'] = $this->getBenefitIcon($data['sno']);
        if(empty($data['goodsIconCd'])) unset($data['goodsIconCd']);

        $getData['data'] = $data;

        return gd_htmlspecialchars_stripslashes(gd_isset($getData));

    }


    /**
     * 상품혜택 리스트 셀렉트 출력
     *
     *
     * @param array $search 상품검색 정보
     *
     * @return string 셀렉트 출력
     */
    public function  goodsBenefitSelect($search){

        //혜택사용을 안하는 경우
        if($this->getConfig() == 'n') return false;

        $orderby = " ORDER BY regDt DESC";
        $arrWhere = array();
        $arrWhere[] = "(periodDiscountEnd > '". date('Y-m-d H:i:s')."' OR benefitUseType != 'periodDiscount' )" ;
        $strSQL = 'SELECT sno,benefitNm,goodsDiscountGroup FROM ' . DB_GOODS_BENEFIT . ' WHERE ' .implode(' AND ', gd_isset($arrWhere)).$orderby;
        $data = $this->db->query_fetch($strSQL, null);
        $arrDiscountGroup        = array('all' => '전체', 'member' => '회원전용', 'group' => '특정회원등급');
        $temp = "<select name='goodsBenefitSno' class='form-control multiple-select' >" . chr(10);
        $temp .= "<option value=''> =상품혜택선택= </option>" . chr(10);
        foreach ($data as $key => $value){
            if($search['goodsBenefitSno'] == $value['sno'] ){
                $temp .= "<option value='".$value['sno']."' selected>".$value['benefitNm']."(".$arrDiscountGroup[$value['goodsDiscountGroup']].")"."</option>" . chr(10);
            }else{
                $temp .= "<option value='".$value['sno']."'>".$value['benefitNm']."(".$arrDiscountGroup[$value['goodsDiscountGroup']].")"."</option>" . chr(10);
            }
        }
        $temp .= "</select>" . chr(10);

        return $temp;

    }

    /**
     * 상품 할인 데이터 재설정 ( 상품혜택 정보 데이터로 변경,관리자사용)
     *
     * @param array $goodsData 상품 데이터
     *
     * @return array 상품 데이터
     *
     */

    public function goodsDataReset($goodsData,$page=false){

        if($goodsData['goodsBenefitSetFl'] != 'y' || gd_php_self() == '/goods/goods_batch_icon.php'){
            return $goodsData;
        }
        // 상품 마일리지 혜택관리, 이동/복사/삭제관리, 혜택상세
        if(gd_php_self() == '/goods/goods_batch_mileage.php' || gd_php_self() == '/goods/goods_batch_link.php' || gd_php_self() == '/goods/benefit_detail.php'){
            $tmp = $this->getGoodsLink($goodsData['goodsNo']);
            $goodsBenfitData = $tmp['data'];
            if (empty($tmp['data'])) {
                $tmp['data'] = $this->getGoodsEndLink($goodsData['goodsNo']);
                $goodsBenfitData = $tmp['data']['data'];
            }
        }else{
            $tmp = $this->getGoodsLink($goodsData['goodsNo'],true);
            $goodsBenfitData = $tmp['data'];
        }

        $exceptKey = array('sno','benefitScheduleNextSno','modDt','regDt');

        $convert = false;
        if ($goodsBenfitData['benefitUseType'] == 'nonLimit') { //제한없음
            $convert = true;

        } else if ($goodsBenfitData['benefitUseType'] == 'newGoodsDiscount') { //신상품

            if ($goodsBenfitData['newGoodsDateFl'] == 'day') { //신상품 할인 기간이 일인 경우
                $endTime = strtotime(date("Y-m-d", strtotime("+" . $goodsBenfitData['newGoodsDate'] . " day", strtotime($goodsData[$goodsBenfitData['newGoodsRegFl']]))));
                $todayTime = strtotime(date("Y-m-d"));
                if ($todayTime <= $endTime || $page == 'image') {
                    $convert = true;
                }
            } else { //신상품 할인 기간이 시간인 경우
                $endTime = strtotime("+" . $goodsBenfitData['newGoodsDate'] . " hour", strtotime($goodsData[$goodsBenfitData['newGoodsRegFl']]));
                $todayTime = strtotime("now");
                if ($todayTime <= $endTime || $page == 'image') {
                    $convert = true;
                }
            }

        } else if ($goodsBenfitData['benefitUseType'] == 'periodDiscount') { //기간할인
            $convert = true;
        }


        foreach($goodsBenfitData as $key => $value){
            if (in_array($key, $exceptKey)) {
                continue;
            }
            if($key == 'fixedGoodsDiscount' && $convert) {

                $goodsData['fixedGoodsDiscount'] = array_filter(explode(STR_DIVISION, $value));
            }
            else if($key == 'exceptBenefit' && $convert) {
                $goodsData['exceptBenefit'] = array_filter(explode(STR_DIVISION, $value));
            }
            else if($key == 'exceptBenefitGroupInfo' && $convert) {
                $goodsData['exceptBenefitGroupInfo'] = array_filter(explode(INT_DIVISION, $value));
            }
            else if ($key == 'goodsDiscountGroupMemberInfo' && empty($value) === false && $convert) {
                $goodsData['goodsDiscountGroupMemberInfo'] = json_decode($value, true);

            }
            else if ($key == 'goodsIconCd' && $convert) { //아이콘
                unset($goodsData['goodsBenefitIconCd']);
                $goodsIconTemp = explode(INT_DIVISION, $goodsData['goodsIconCd']);
                $goodsBenefitIconTemp = explode(INT_DIVISION, $value);
                //중복된 아이콘을 제거하고 혜택아이콘을 맨처음 출력하기 위해
                foreach($goodsIconTemp as $k => $v){
                    if (in_array($v, $goodsBenefitIconTemp)) {
                        unset($goodsIconTemp[$k]);
                    }
                }
                $goodsData['goodsIconCd'] = implode(INT_DIVISION,$goodsIconTemp);
                $goodsData['goodsBenefitIconCd'] = $value;

            }else{
                if ($convert) {
                    $goodsData[$key] = $value;
                }

            }
        }
        return $goodsData;
    }

    /**
     * 상품 할인 데이터 재설정 ( 프론트시용)
     *
     * @param array $goodsData 상품 데이터,array $benefitData
     *
     * @return array 상품 데이터
     *
     */
    public function goodsDataFrontConvert($goodsData,$benefitData=null){

        if($goodsData['goodsBenefitSetFl'] == 'y') { //할인헤택 사용

            if(!empty($benefitData)){
                if(!empty($goodsData['benefitSno'])){
                    //네이버 EP쪽에서 넘어온 데이터는 날짜.Sno 형식
                    $tmpSno = explode('.',$goodsData['benefitSno']);
                    $goodsData['benefitSno'] =  $tmpSno[1];
                    $goodsBenfitData = $benefitData[$goodsData['benefitSno']];
                }else{
                    return $goodsData;
                }

            }else{
                $goodsBenfit = $this->getGoodsLink($goodsData['goodsNo'], true);
                $goodsBenfitData = $goodsBenfit['data'];
            }

            if (empty($goodsBenfitData)) {
                return $goodsData;
            }

            $convert = false;
            if ($goodsBenfitData['benefitUseType'] == 'nonLimit') { //제한없음
                $convert = true;

            } else if ($goodsBenfitData['benefitUseType'] == 'newGoodsDiscount') { //신상품

                if ($goodsBenfitData['newGoodsDateFl'] == 'day') { //신상품 할인 기간이 일인 경우
                    $endTime = strtotime(date("Y-m-d", strtotime("+" . $goodsBenfitData['newGoodsDate'] . " day", strtotime($goodsData[$goodsBenfitData['newGoodsRegFl']]))));
                    $todayTime = strtotime(date("Y-m-d"));
                    if ($todayTime <= $endTime) {
                        $convert = true;
                        $goodsData['periodDiscountDuration'] = strtotime(date("Y-m-d",$endTime)." 23:59:59")- time();
                    }
                } else { //신상품 할인 기간이 시간인 경우
                    $endTime = strtotime("+" . $goodsBenfitData['newGoodsDate'] . " hour", strtotime($goodsData[$goodsBenfitData['newGoodsRegFl']]));
                    $todayTime = strtotime("now");
                    if ($todayTime <= $endTime) {
                        $convert = true;
                        $goodsData['periodDiscountDuration'] = strtotime(date("Y-m-d H:i:s",$endTime))- time();
                    }
                }

            } else if ($goodsBenfitData['benefitUseType'] == 'periodDiscount') { //기간할인
                $convert = true;
                $goodsData['periodDiscountDuration'] = strtotime($goodsBenfitData['periodDiscountEnd'])- time();
            }

            $exceptKey = array('sno', 'benefitScheduleNextSno', 'modDt', 'regDt');
            foreach ($goodsBenfitData as $key => $value) {
                if (in_array($key, $exceptKey)) {
                    continue;
                }

                if ($key == 'goodsIconCd' && $convert) { //아이콘

                    unset($goodsData['goodsBenefitIconCd']);
                    $goodsIconTemp = explode(INT_DIVISION, $goodsData['goodsIconCd']);
                    $goodsBenefitIconTemp = explode(INT_DIVISION, $value);
                    //중복된 아이콘을 제거하고 혜택아이콘을 맨처음 출력하기 위해
                    foreach($goodsIconTemp as $k => $v){
                        if (in_array($v, $goodsBenefitIconTemp)) {
                            unset($goodsIconTemp[$k]);
                        }
                    }
                    $goodsData['goodsIconCd'] = implode(INT_DIVISION,$goodsIconTemp);
                    $goodsData['goodsBenefitIconCd'] = $value;

                } else {
                    if ($convert) {
                        $goodsData[$key] = $value; //그외 할인 정보
                    }
                }

            }

            if($goodsData['goodsIcon'] && $convert ){
                unset($goodsData['goodsBenefitIconCd']);
                $goodsIconTemp = explode(INT_DIVISION, $goodsData['goodsIcon']);
                $goodsBenefitIconTemp = explode(INT_DIVISION, $goodsBenfitData['goodsIconCd']);
                //중복된 아이콘을 제거하고 혜택아이콘을 맨처음 출력하기 위해
                foreach($goodsIconTemp as $k => $v){
                    if (in_array($v, $goodsBenefitIconTemp)) {
                        unset($goodsIconTemp[$k]);
                    }
                }
                $goodsData['goodsIcon'] = implode(INT_DIVISION,$goodsIconTemp);
                $goodsData['goodsBenefitIconCd'] = $goodsBenfitData['goodsIconCd'];
            }

        }else{ //개별할인 사용

            if ($goodsData['goodsDiscountFl'] == 'y') {
                if ($goodsData['benefitUseType'] == 'newGoodsDiscount') { //신상품
                    if ($goodsData['newGoodsDateFl'] == 'day') { //신상품 할인 기간이 일인 경우
                        $endTime = strtotime(date("Y-m-d", strtotime("+" . $goodsData['newGoodsDate'] . " day", strtotime($goodsData[$goodsData['newGoodsRegFl']]))));
                        $todayTime = strtotime(date("Y-m-d"));
                        if ($todayTime <= $endTime) {
                            $goodsData['goodsDiscountFl'] = 'y';
                            $goodsData['periodDiscountDuration'] = strtotime(date("Y-m-d",$endTime)." 23:59:59")- time();
                        } else {
                            $goodsData['goodsDiscountFl'] = 'n';
                        }
                    } else { //신상품 할인 기간이 시간인 경우
                        $endTime = strtotime("+" . $goodsData['newGoodsDate'] . " hour", strtotime($goodsData[$goodsData['newGoodsRegFl']]));
                        $todayTime = strtotime("now");
                        if ($todayTime <= $endTime) {
                            $goodsData['goodsDiscountFl'] = 'y';
                            $goodsData['periodDiscountDuration'] = strtotime(date("Y-m-d H:i:s",$endTime))- time();
                        } else {
                            $goodsData['goodsDiscountFl'] = 'n';
                        }
                    }

                } else if ($goodsData['benefitUseType'] == 'periodDiscount') { //기간할인
                    if (strtotime($goodsData['periodDiscountStart']) < strtotime("now") && strtotime($goodsData['periodDiscountEnd']) > strtotime("now")) {
                        $goodsData['goodsDiscountFl'] = 'y';
                        $goodsData['periodDiscountDuration'] = strtotime($goodsData['periodDiscountEnd'])- time();
                    } else {
                        $goodsData['goodsDiscountFl'] = 'n';
                    }
                }
            }
        }

        return $goodsData;
    }

    /**
     * 상품할인 치환코드 생성 후 return (goodsDcPricePrint, memberDcPricePrint, memberDcTotalPercentPrint,
     *
     * @param array $goodsData 상품 데이터
     *
     * @return array 상품 데이터
     *
     */
    public function goodsDataFrontReplaceCode($goodsData, $page = 'goodsView') {

        // 치환코드 제공 영역
        if($goodsData) {
            // 절사 config 로드 - 가격 변환 노출용
            $goodsData['goodsDcPricePrint'] = "";
            $goodsData['memberDcTotalPercent'] = "";
            $goodsData['memberDcPricePrint'] = "";
            $goodsData['periodDiscountEndPrint'] = "";
            $configTrunc = gd_policy('basic.trunc');
            $memberConfigTrunc = $configTrunc['member_group']; // 회원등급별절사
            $goodsConfigTrunc = $configTrunc['goods']; // 상품금액절사

            // 상품 상세 ( 상품정보 기준으로 계산)
            if($page == 'goodsView') {
                // 회원설정
                $member = \App::Load(\Component\Member\Member::class);
                $memInfo = $member->getMemberInfo();

                // 판매가 - 상품할인가 = 치환코드용
                $goodsDcPrice = $this->getGoodsViewDiscountPrice($goodsData, $memInfo);
                if(!$goodsDcPrice || $goodsDcPrice == 0) $goodsDcPrice = $goodsData['goodsPrice']; // 할인 가격이 없을 경우 판매가 노출
                $goodsData['goodsDcPricePrint'] = $goodsDcPrice;

                // 제외 혜택 대상 여부
                $exceptBenefitGroupInfo = explode(INT_DIVISION, $goodsData['exceptBenefitGroupInfo']);
                $exceptBenefitFl = false;
                if ($goodsData['exceptBenefitGroup'] == 'all' || ($goodsData['exceptBenefitGroup'] == 'group' && in_array($memInfo['groupSno'], $exceptBenefitGroupInfo) === true)) {
                    $exceptBenefitFl = true;
                }

                // 제외 혜택 경우
                $discountPercent = ($goodsData['memberDc']['dcPercent'] + $goodsData['memberDc']['overlapDcPercent']) / 100;
                if($exceptBenefitFl === false) {
                    // 회원 그룹 일 경우
                    if($goodsData['goodsDiscountGroup'] == 'group') {
                        $goodsDiscountGroupMemberInfoData = json_decode($goodsData['goodsDiscountGroupMemberInfo'], true);
                        $discountKey = array_flip($goodsDiscountGroupMemberInfoData['groupSno'])[$memInfo['groupSno']];
                        if ($discountKey >= 0) {
                            // 판매가 - 회원할인가 = 치환코드용
                            $goodsData['memberDcPricePrint'] = ($goodsData['goodsPrice'] - (gd_number_figure(($goodsData['goodsPrice'] * $discountPercent), $memberConfigTrunc['unitPrecision'], $memberConfigTrunc['unitRound'])));
                        } else {
                            $goodsData['memberDcPricePrint'] = $goodsData['goodsPrice'];
                        }
                        // 회원 추가할인 + 회원 중복할인 합계 percent = 치환코드용
                        $goodsData['memberDcTotalPercent'] = ($goodsData['memberDc']['dcPercent'] + $goodsData['memberDc']['overlapDcPercent']) . "%";
                    } else {
                        // 회원 추가할인 + 회원 중복할인 합계 percent = 치환코드용
                        $goodsData['memberDcTotalPercent'] = ($goodsData['memberDc']['dcPercent'] + $goodsData['memberDc']['overlapDcPercent']) . "%";
                        // 판매가 - 회원할인가 = 치환코드용
                        $goodsData['memberDcPricePrint'] = ($goodsData['goodsPrice'] - (gd_number_figure(($goodsData['goodsPrice'] * $discountPercent), $memberConfigTrunc['unitPrecision'], $memberConfigTrunc['unitRound'])));
                    }
                } else {
                    $goodsData['memberDcTotalPercent'] = '0%';
                    $goodsData['memberDcPricePrint'] = $goodsData['goodsPrice'];
                }

                // 혜택 종료일 = 치환코드용
                if ($goodsData['benefitUseType'] == 'newGoodsDiscount') { //신상품
                    if ($goodsData['newGoodsDateFl'] == 'day') { //신상품 할인 기간이 일인 경우
                        $endTime = date("Y-m-d H:i:s", strtotime("+" . $goodsData['newGoodsDate'] . " day", strtotime($goodsData[$goodsData['newGoodsRegFl']])));
                    } else { //신상품 할인 기간이 시간인 경우
                        $endTime = date("Y-m-d H:i:s", strtotime("+" . $goodsData['newGoodsDate'] . " hour", strtotime($goodsData[$goodsData['newGoodsRegFl']])));
                    }
                    $goodsData['periodDiscountEndPrint'] = $endTime;
                } else if ($goodsData['benefitUseType'] == 'periodDiscount') { //기간할인
                    $goodsData['periodDiscountEndPrint'] = $goodsData['periodDiscountEnd'];
                }
            }
            // 상품리스트
            else if($page == 'goodsList') {
                $goodsDcPrice = ($goodsData['goodsPrice'] - $goodsData['goodsDcPrice']);
                if(!$goodsDcPrice || $goodsDcPrice == 0) $goodsDcPrice = $goodsData['goodsPrice']; // 할인 가격이 없을 경우 판매가 노출
                $goodsData['goodsDcPricePrint'] = gd_number_figure( $goodsDcPrice, $goodsConfigTrunc['unitPrecision'], $goodsConfigTrunc['unitRound']);
                // 혜택 종료일 = 치환코드용
                if ($goodsData['benefitUseType'] == 'newGoodsDiscount') { //신상품
                    if ($goodsData['newGoodsDateFl'] == 'day') { //신상품 할인 기간이 일인 경우
                        $endTime = date("Y-m-d H:i:s", strtotime("+" . $goodsData['newGoodsDate'] . " day", strtotime($goodsData[$goodsData['newGoodsRegFl']])));

                    } else { //신상품 할인 기간이 시간인 경우
                        $endTime = date("Y-m-d H:i:s", strtotime("+" . $goodsData['newGoodsDate'] . " hour", strtotime($goodsData[$goodsData['newGoodsRegFl']])));
                    }
                    $goodsData['periodDiscountEndPrint'] = $endTime;
                } else if ($goodsData['benefitUseType'] == 'periodDiscount') { //기간할인
                    $goodsData['periodDiscountEndPrint'] = $goodsData['periodDiscountEnd'];
                }
            }
            // 장바구니,주문하기 ( Cart에 담긴 상품정보를 기준으로 계산)
            else if($page == 'cartOrder') {
                // 회원 혜택 Json decode 처리
                $memberDcDecode = (array)json_decode($goodsData['memberDcInfo']);

                // 회원 혜택 Json decode 데이터 가공
                if($memberDcDecode['memberDcPrice'] == 0 ) {
                    $memberDcDecode['dcPercent'] = 0;
                }
                if($memberDcDecode['memberOverlapDcPrice'] == 0 ) {
                    $memberDcDecode['overlapDcPercent'] = 0;
                }

                // 판매가 - 상품할인가 = 치환코드용
                $goodsData['goodsDcPricePrint'] = $goodsData['price']['goodsPriceSum'] - $goodsData['price']['goodsDcPrice'];
                // 회원 추가할인 + 회원 중복할인 합계 percent = 치환코드용
                $goodsData['memberDcTotalPercent'] = ($memberDcDecode['dcPercent'] + $memberDcDecode['overlapDcPercent']) . "%";
                // 판매가 - 회원할인가 = 치환코드용
                $goodsData['memberDcPricePrint'] = $goodsData['price']['goodsPriceSum'] - ($goodsData['price']['goodsMemberDcPrice'] + $goodsData['price']['goodsMemberOverlapDcPrice']);
                // 혜택 종료일 = 치환코드용
                if ($goodsData['benefitUseType'] == 'newGoodsDiscount') { //신상품
                    if ($goodsData['newGoodsDateFl'] == 'day') { //신상품 할인 기간이 일인 경우
                        $endTime = date("Y-m-d H:i:s", strtotime("+" . $goodsData['newGoodsDate'] . " day", strtotime($goodsData[$goodsData['newGoodsRegFl']])));

                    } else { //신상품 할인 기간이 시간인 경우
                        $endTime = date("Y-m-d H:i:s", strtotime("+" . $goodsData['newGoodsDate'] . " hour", strtotime($goodsData[$goodsData['newGoodsRegFl']])));
                    }
                    $goodsData['periodDiscountEndPrint'] = $endTime;
                } else if ($goodsData['benefitUseType'] == 'periodDiscount') { //기간할인
                    $goodsData['periodDiscountEndPrint'] = $goodsData['periodDiscountEnd'];
                }
            }
            // 마이페이지 리스트 (주문 후 주문DB 기준으로 계산)
            else if($page == 'mypage') {
                // 판매가 - 상품할인가 = 치환코드용
                $goodsData['goodsDcPricePrint'] = $goodsData['goodsPrice'] - $goodsData['goodsDcPrice'];
                // 판매가 - 회원할인가 = 치환코드용
                $goodsData['memberDcPricePrint'] =  $goodsData['goodsPrice'] - ($goodsData['totalMemberDcPrice'] + $goodsData['totalMemberOverlapDcPrice']);
            }
            return $goodsData;
        }
    }

    /**
     * 현재진행중인 헤택정보를 가져오는 메서드
     *
     *
     * @return array 헤택 데이터
     *
     */
    public function goodsBenefitIng(){

        $arrWhere = array();
        $arrWhereAll = array();
        $arrBind = array();
        $arrWhereAll[] = 'benefitUseType = ? and periodDiscountStart < ? and periodDiscountEnd > ? ';
        $this->db->bind_param_push($arrBind, 's', 'periodDiscount');
        $this->db->bind_param_push($arrBind, 's', date('Y-m-d H:i:s'));
        $this->db->bind_param_push($arrBind, 's', date('Y-m-d H:i:s'));

        //특정기간 할인이 아닌것은 진행중상태
        $arrWhereAll[] = 'benefitUseType != ? ';
        $this->db->bind_param_push($arrBind, 's', 'periodDiscount');
        $arrWhere[] = '(' . implode(' OR ', $arrWhereAll) . ')';
        unset($arrWhereAll);

        $strSQL = 'SELECT * FROM ' . DB_GOODS_BENEFIT . ' WHERE ' . implode(' AND ', gd_isset($arrWhere));
        $benefit = $this->db->query_fetch($strSQL,$arrBind);

        $tempArry = array();
        foreach($benefit as $k => $v){
            $tempArry[$v['sno']] = $v;
        }

        return $tempArry;

    }


    /**
     * 종료된 헤택정보를 가져오는 메서드
     *
     * @param integer $goodsno 상품번호
     * @return array 헤택 데이터
     *
     */

    public function getGoodsEndLink($goodsno)
    {
        $arrWhere = $arrBind = array();
        $arrWhere[] = 'gbl.goodsNo = ? ';
        $this->db->bind_param_push($arrBind, 'i', $goodsno);


        $arrWhere[] = 'gbl.benefitUseType = ? AND  gbl.linkPeriodEnd < ? ';
        $this->db->bind_param_push($arrBind, 's', 'periodDiscount');
        $this->db->bind_param_push($arrBind, 's', date('Y-m-d H:i:s'));


        $join[] = ' LEFT JOIN ' . DB_GOODS_LINK_BENEFIT . ' as gbl ON gb.sno = gbl.benefitSno ';

        $strField = "gb.*";
        $strJoin = implode('', $join);
        $strWhere = implode(' AND ', gd_isset($arrWhere));

        $strSQL = 'SELECT ' . $strField . ' FROM ' . DB_GOODS_BENEFIT . ' gb ' . $strJoin.' WHERE '.$strWhere.' ORDER BY linkPeriodEnd DESC LIMIT 1';
        $tmp = $this->db->query_fetch($strSQL, $arrBind);
        $data = $tmp[0];
        //통합 아이콘 테이블에서 가져옴
        $data['goodsIconCd'] = $this->getBenefitIcon($data['sno']);
        $getData['data'] = $data;

        return gd_htmlspecialchars_stripslashes(gd_isset($getData));

    }

    /**
     * 순수 상품판매 단가의 할인율을 먼저 구한 뒤 반환
     *
     * @param array $goodsData 상품 정보
     * @param array $memInfo 회원 정보
     * @return int 상품할인금액
     */

    public function getGoodsViewDiscountPrice($goodsData, $memInfo)
    {
        if ($goodsData['goodsDiscountFl'] === 'y') {

            $configTrunc = gd_policy('basic.trunc');
            $goodsConfigTrunc = $configTrunc['goods']; // 상품금액절사

            switch ($goodsData['goodsDiscountGroup']) {
                case 'group':
                    $goodsDiscountGroupMemberInfoData = json_decode($goodsData['goodsDiscountGroupMemberInfo'], true);
                    $discountKey = array_flip($goodsDiscountGroupMemberInfoData['groupSno'])[$memInfo['groupSno']];

                    if ($discountKey >= 0) {
                        if ($goodsDiscountGroupMemberInfoData['goodsDiscountUnit'][$discountKey] === 'percent') {
                            $discountPercent = $goodsDiscountGroupMemberInfoData['goodsDiscount'][$discountKey] / 100;

                            // 상품할인금액
                            $goodsDcPrice = gd_number_figure(($goodsData['goodsPrice'] * $discountPercent), $goodsConfigTrunc['unitPrecision'], $goodsConfigTrunc['unitRound']);
                        } else {
                            // 상품금액보다 상품할인금액이 클 경우 상품금액으로 변경
                            if ($goodsDiscountGroupMemberInfoData['goodsDiscount'][$discountKey] > $goodsData['goodsPrice']) $goodsDiscountGroupMemberInfoData['goodsDiscount'][$discountKey] = $goodsData['goodsPrice'];
                            // 상품할인금액 (정액인 경우 해당 설정된 금액으로)
                            $goodsDcPrice = gd_number_figure($goodsDiscountGroupMemberInfoData['goodsDiscount'][$discountKey], $goodsConfigTrunc['unitPrecision'], $goodsConfigTrunc['unitRound']);
                        }
                    }
                    $goodsDcPrice = $goodsData['goodsPrice'] - $goodsDcPrice;
                    break;
                case 'member':
                default:
                    //$goodsDcPrice = gd_number_figure($goodsData['goodsDiscountPrice'], $goodsConfigTrunc['unitPrecision'], $goodsConfigTrunc['unitRound']);

                    if ($goodsData['goodsDiscountUnit'] === 'percent') {
                        $discountPercent = $goodsData['goodsDiscount'] / 100;

                        // 상품할인금액
                        $goodsDcPrice = $goodsData['goodsPrice'] - gd_number_figure(($goodsData['goodsPrice'] * $discountPercent), $goodsConfigTrunc['unitPrecision'], $goodsConfigTrunc['unitRound']);
                    } else {
                        // 상품금액보다 상품할인금액이 클 경우 상품금액으로 변경
                        if ($goodsData['goodsDiscount'] > $goodsData['goodsPrice']) $goodsDiscount = $goodsData['goodsPrice'];
                        // 상품할인금액 (정액인 경우 해당 설정된 금액으로)
                        $goodsDcPrice = $goodsData['goodsPrice'] - $goodsDiscount;
                    }
                    if ($goodsData['goodsDiscountGroup'] == 'member' && empty($memInfo['groupSno']) === true) {
                        $goodsDcPrice = 0;
                    }

                    break;
            }
            return $goodsDcPrice;
        }
    }

    /**
     * 상품에 존재하는 혜택 복사하는 메서드
     * @param integer $goodsno 상품번호
     * @param integer $newGoodsNo 상품번호
     * @return array 헤택 데이터
     *
     */
    public function goodsBenefitCopy($goodsno,$newGoodsNo){

        $tmp = $this->getGoodsLink($goodsno);
        $goodsBenfitData = $tmp['data'];


        $benefitData = $this->getGoodsBenefit($goodsBenfitData['sno']);

        //종료가 된 혜택은 개별로 리셋
        if( empty($goodsBenfitData) || ($benefitData['benefitUseType'] == "periodDiscount" && strtotime($benefitData['periodDiscountEnd']) < strtotime("now")) ){

            $goodsData['benefitSno'] = 0;
            $goodsData['goodsBenefitSetFl'] = 'n';

            $compareField = array_keys($goodsData);
            $arrBind = $this->db->get_binding(DBTableField::tableGoods(), $goodsData, 'update', $compareField);
            $this->db->bind_param_push($arrBind['bind'], 'i', $newGoodsNo);
            $this->db->set_update_db(DB_GOODS, $arrBind['param'], 'goodsNo = ?', $arrBind['bind']);
            unset($arrBind, $goodsData);
        }else{
            $this->addGoodsLink($goodsBenfitData['sno'],$newGoodsNo);
        }

    }

    /**
     * 상품혜택 아이콘
     *
     * @param integer $benefitSno 상품혜택번호
     * @return array 아이콘 데이터
     *
     */
    public function getBenefitIcon($benefitSno){

        $arrWhere = array();
        $arrBind = array();
        $arrWhere[] = "iconKind = 'pr'";
        $arrWhere[] = "benefitSno = ?";
        $this->db->bind_param_push($arrBind, 'i', $benefitSno);

        $strSQL = 'SELECT GROUP_CONCAT( goodsIconCd ORDER BY goodsIconCd ASC SEPARATOR \''.INT_DIVISION.'\') as goodsIconCd FROM  '.DB_GOODS_ICON.' WHERE ' . implode(' AND ', gd_isset($arrWhere)) .' GROUP BY benefitSno';
        $benefitIcon = $this->db->query_fetch($strSQL, $arrBind);

        return $benefitIcon[0]['goodsIconCd'];
    }

    /**
     * 상품혜택 사용여부 설정
     *
     * @param array $arrData 상품혜택정보
     *
     */
    public function setConfig($arrData){

        if($arrData['goodsBenefitUse'] == 'n'){
            $this->db->set_update_db(DB_GOODS, "goodsBenefitSetFl ='n',goodsDiscountFl ='n'", "goodsBenefitSetFl = 'y'");
            $query = "TRUNCATE TABLE " . DB_GOODS_LINK_BENEFIT;
            $this->db->query($query);
        }
        gd_set_policy('goods.benefit', array('goodsBenefitUse' => $arrData['goodsBenefitUse']));

    }

    /**
     * 상품혜택 사용여부
     *
     * @return string 상품혜택 사용여부
     *
     */
    public function getConfig(){

        $confBenefit = gd_policy('goods.benefit');
        if($confBenefit['goodsBenefitUse'] =='y'){
            return 'y';
        }else{
            return 'n';
        }

    }

    /**
     * 상품혜택 cart DB Insert 데이터 생성
     *
     * @param array $data 혜택데이터
     * @param string $dataKind 데이터 종류
     * @return array 가공 데이터
     *
     */
    public function setBenefitOrderGoodsData($data, $dataKind ='discount')
    {
        if($dataKind == 'discount') { // 할인혜택인 경우
            $goodsBenefitData = [
                'goodsBenefitSetFl',
                'benefitUseType',
                'newGoodsRegFl',
                'newGoodsDate',
                'newGoodsDateFl',
                'periodDiscountStart',
                'periodDiscountEnd',
                'goodsDiscountFl',
                'goodsDiscount',
                'goodsDiscountUnit',
                'fixedGoodsDiscount',
                'goodsDiscountGroup',
                //'goodsDiscountGroupMemberInfo',
                //'exceptBenefit',
                //'exceptBenefitGroup',
                //'exceptBenefitGroupInfo',
                'benefitNm',
                'benefitScheduleFl',
                'benefitSchedulePrevSno'
            ];
        } else { // 적립혜택인 경우
            $goodsBenefitData = [
                'mileageFl',
                'mileageGroup',
                'mileageGoods',
                'mileageGoodsUnit',
                //'mileageGroupInfo',
                //'mileageGroupMemberInfo',
            ];
        }

        $goodsBenefitData = array_flip($goodsBenefitData);
        foreach($goodsBenefitData as $key => $value) {
            if($data[$key] && $data[$key] != null) {
                $goodsBenefitData[$key] = $data[$key];
            } else {
                $goodsBenefitData[$key] = '';
            }
        }

        return $goodsBenefitData;
    }
}
