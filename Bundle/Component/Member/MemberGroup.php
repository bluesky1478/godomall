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

namespace Bundle\Component\Member;

use App;
use Component\Database\DBTableField;
use Component\Member\Group\GroupDomain;
use Component\Member\Group\Util as GroupUtil;
use Component\Validator\Validator;
use Exception;
use Framework\Utility\NumberUtils;
use Framework\Utility\StringUtils;
use Request;
use Session;

/**
 * Class MemberGroup
 * @package Bundle\Component\Member
 * @author  yjwee
 */
class MemberGroup extends \Component\AbstractComponent
{
    /**
     *  등록 모드
     */
    const MODE_REGISTER = 'REGISTER';
    /**
     * 수정 모드
     */
    const MODE_MODIFY = 'MODIFY';
    /** @var array 등급기준 */
    public $appraisalRule = [
        'apprSystem'                 => 'figure',
        // figure:실적 수치제,point:실적 점수제
        'apprPointOrderPriceUnit'    => 0,
        // (실적점수제)구매금액기준
        'apprPointOrderPricePoint'   => 0,
        // (실적점수제)구매금액당 점수
        'apprPointOrderRepeatPoint'  => 0,
        // (실적점수제)구매횟수당 점수
        'apprPointReviewRepeatPoint' => 0,
        // (실적점수제)구매후기당 점수
        'apprPointLoginRepeatPoint'  => 0,
        // (실적점수제)로그인횟수당 점수
        'calcPeriodFl'               => 'n',
        // 산출기간제한여부(y:제한,n:무제한)
        'calcPeriodBegin'            => 0,
        // 산출기간시작점(-1d:어제,-1w:1주일,-2w:2주일,-1m:한달)
        'calcPeriodMonth'            => 0,
        // 산출기간개월수(1,2,3,6)
        'calcPeriod'                 => [
            'sdate' => '0000-00-00',
            'edate' => '0000-00-00',
        ],
        // 산출기간
        'calcCycleMonth'             => 0,
        // 산출주기개월(1,2,3,6)
        'calcCycleDay'               => 0,
        // 산출주기해당월일자(1~31)
        'calcKeep'                   => 0,
        // 등급유지기간(1,2,3,6)
        'group'                      => [],
    ]; // 등급별기준
    /** @var  GroupDomain */
    protected $groupDomain;
    /** @var null */
    protected $apprSystem = null;

    /**
     * getGroupList
     *
     * @return array
     */
    public function getGroupList()
    {
        $getData = $arrBind = [];

        // --- 목록
        $arrField = DBTableField::setTableField('tableMemberGroup', null, null, 'mg');
        $this->db->strField = 'mg.sno, ' . implode(', ', $arrField) . ', mg.regDt';
        $this->db->strOrder = 'mg.groupSort DESC';

        $memStr = ', (SELECT COUNT(m.memNo) FROM ' . DB_MEMBER . ' m WHERE mg.sno = m.groupSno GROUP BY m.groupSno) as memCnt';
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . $memStr . ',m.isDelete FROM ' . DB_MEMBER_GROUP . ' mg LEFT OUTER JOIN ' . DB_MANAGER . " as m ON mg.managerNo = m.sno " . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL);
        Manager::displayListData($data);
        // --- 각 데이터 배열화
        $getData['cnt'] = $this->db->query_fetch('SELECT COUNT(*) AS cnt FROM ' . DB_MEMBER_GROUP);
        $getData['data'] = gd_htmlspecialchars_stripslashes(gd_isset($data));

        return $getData;
    }

    /**
     * 관리자 리스트
     *
     * @return array 데이터
     */
    public function getGroupListSearch()
    {
        $arrBind = [];
        $getValue = Request::get()->toArray();

        // --- 검색 설정
        $search['keyword'] = gd_isset($getValue['keyword']);

        // 키워드 검색
        if ($search['keyword']) {
            $arrWhere[] = 'groupNm LIKE concat(\'%\',?,\'%\')';
            $this->db->bind_param_push($arrBind, 's', $search['keyword']);
        }

        // --- 페이지 기본설정
        if (gd_isset($getValue['pagelink'])) {
            $getValue['page'] = (int) str_replace('page=', '', preg_replace('/^{page=[0-9]+}/', '', gd_isset($getValue['pagelink'])));
        } else {
            $getValue['page'] = 1;
        }

        gd_isset($getValue['page'], 1);
        gd_isset($getValue['pageNum'], 10);

        $page = \App::load('\\Component\\Page\\Page', $getValue['page']);
        $page->page['list'] = $getValue['pageNum']; // 페이지당 리스트 수
        $page->recode['amount'] = $this->db->getCount(DB_MEMBER_GROUP);
        $page->setPage();
        $page->setUrl(\Request::getQueryString());

        $this->db->strField = implode(', ', DBTableField::setTableField('tableMemberGroup')) . ', sno,regDt';
        if (gd_isset($arrWhere)) {
            $this->db->strWhere = implode(' AND ', gd_isset($arrWhere));
        }
        $this->db->strOrder = "regDt desc";
        $this->db->strLimit = $page->recode['start'] . ',' . $getValue['pageNum'];

        $query = $this->db->query_complete(true, true);
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER_GROUP . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $arrBind);

        $funcFoundRows = function ($arrBind) {
            $query = $this->db->getQueryCompleteBackup(
                [
                    'field' => 'COUNT(*) AS cnt',
                    'order' => null,
                    'limit' => null,
                ]
            );
            $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER_GROUP . implode(' ', $query);
            $cnt = $this->db->query_fetch($strSQL, $arrBind, false)['cnt'];
            StringUtils::strIsSet($cnt, 0);

            return $cnt;
        };
        // 검색 레코드 수
        $page->recode['total'] = $funcFoundRows($arrBind);
        $page->setPage();

        $getData['data'] = gd_htmlspecialchars_stripslashes(gd_isset($data));
        $getData['search'] = gd_htmlspecialchars($search);

        return $getData;
    }

    /**
     * 등급 레벨 정보 (순서에 의한)
     *
     * @author artherot
     * @return array 데이터
     */
    public function getGroupSno()
    {
        $getData = [];
        $strSQL = 'SELECT sno, groupSort FROM ' . DB_MEMBER_GROUP . ' ORDER BY groupSort ASC';
        $result = $this->db->query($strSQL);
        while ($data = $this->db->fetch($result)) {
            /** @var int $sno */
            $sno = $data['sno'];
            $getData[$sno] = $data['groupSort'];
        }

        return gd_htmlspecialchars_stripslashes($getData);
    }

    /**
     * 등급정보
     *
     * @param integer $sno 등급번호
     *
     * @return array|string
     * @throws Exception 등급번호 검증 오류
     */
    public function getGroupViewToArray($sno)
    {
        if (Validator::number($sno, null, null, true) === false) {
            throw new Exception(sprintf(__('%s 인자가 잘못되었습니다.'), 'sno'));
        }
        $arrBind = $data = [];

        $this->db->strField = "*";
        $this->db->strWhere = "sno=?";
        $this->db->bind_param_push($arrBind, 'i', $sno);

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER_GROUP . ' ' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $arrBind, false);

        return gd_htmlspecialchars_stripslashes($data);
    }

    /**
     * getGroup
     *
     * @param integer $sno 등급번호
     *
     * @return array|null|object
     * @throws Exception 등급번호 검증 오류
     */
    public function getGroup($sno)
    {
        if (Validator::number($sno, null, null, true) === false) {
            throw new Exception(sprintf(__('%s 인자가 잘못되었습니다.'), 'sno'));
        }

        return $this->db->getData(DB_MEMBER_GROUP, $sno, 'sno');
    }

    /**
     * 등급별 할인 정보 (기본 등급이나 해당 회원의 등급이며, 상품 상세페이지에서 회원 할인률 계산에 쓰임)
     *
     * @author artherot
     *
     * @param  string $goodsNo 일련번호
     * @param  string $cateCd  일련번호
     *
     * @return array  회원 등급 정보
     */
    public function getGroupForSale($goodsNo, $cateCd)
    {
        // 회원 로그인 체크
        if (gd_is_login() === true && Session::has('member.groupSort')) {
            $groupSort = Session::get('member.groupSort');
        } else {
            // $groupSort = 1;
            // 등급명칭/가입등급설정
            $groupData = gd_policy('member.group');
            $join = gd_policy('member.join');
            $groupData['grpInit'] = gd_isset($join['grpInit'], '1');
            $groupData = gd_htmlspecialchars_stripslashes($groupData);
            unset($join);

            // 쿼리 작성
            $arrInclude = [
                'sno',
                'groupSort',
            ];
            $arrField = DBTableField::setTableField('tableMemberGroup', $arrInclude);
            $this->db->strField = implode(', ', $arrField);
            $this->db->strWhere = 'sno = ' . $groupData['grpInit'];

            $query = $this->db->query_complete();
            $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER_GROUP . ' ' . implode(' ', $query);
            $result = $this->db->query($strSQL);
            $data = $this->db->fetch($result);

            $groupSort = $data['groupSort'];
            unset($groupData, $data, $arrInclude, $arrField);
        }

        $arrInclude = [
            'dcLine',
            'dcType',
            'dcPercent',
            'dcPrice',
            'dcCut',
            'dcCutType',
            'dcExCate',
            'dcExGoods',
            'overlapDcLine',
            'overlapDcType',
            'overlapDcPercent',
            'overlapDcPrice',
            'overlapDcCut',
            'overlapDcCutType',
            'overlapDcCate',
            'overlapDcGoods',
            'mileageLine',
            'mileagePercent',
            'settleGb',
        ];
        $arrField = DBTableField::setTableField('tableMemberGroup', $arrInclude);
        $this->db->strField = implode(', ', $arrField);
        $this->db->strWhere = 'groupSort = ' . $groupSort;
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER_GROUP . ' ' . implode(' ', $query);
        $result = $this->db->query($strSQL);
        while ($data = $this->db->fetch($result)) {
            // 회원 할인 여부 설정
            $applyDcFl = true; // true : 적용, false : 미적용
            if (empty($data['dcExGoods']) === false) {
                if (preg_match('/' . $goodsNo . '/', $data['dcExGoods'])) {
                    $applyDcFl = false;
                } else {
                    $applyDcFl = true;
                }
            }
            if (empty($data['dcExCate']) === false && empty($cateCd) === false && $applyDcFl === true) {
                $tmpCateCd = explode(INT_DIVISION, $data['dcExCate']);
                $cateCdLen = strlen($cateCd);
                foreach ($tmpCateCd as $val) {
                    for ($i = 0; $i < $cateCdLen / DEFAULT_LENGTH_CATE; $i++) {
                        $tmpValue = substr($cateCd, 0, ((DEFAULT_LENGTH_CATE * $i) + DEFAULT_LENGTH_CATE));
                        if ($tmpValue == $val) {
                            $applyDcFl = false;
                            continue;
                        }
                    }
                }
            }

            // 중복 할인 여부 설정
            $applyInFl = false; // true : 적용, false : 미적용
            if (empty($data['overlapDcGoods']) === false) {
                if (preg_match('/' . $goodsNo . '/', $data['overlapDcGoods'])) {
                    $applyInFl = true;
                } else {
                    $applyInFl = false;
                }
            }
            if (empty($data['overlapDcCate']) === false && empty($cateCd) === false && $applyInFl === false) {
                $tmpCateCd = explode(INT_DIVISION, $data['overlapDcCate']);
                $cateCdLen = strlen($cateCd);
                foreach ($tmpCateCd as $val) {
                    for ($i = 0; $i < $cateCdLen / DEFAULT_LENGTH_CATE; $i++) {
                        $tmpValue = substr($cateCd, 0, ((DEFAULT_LENGTH_CATE * $i) + DEFAULT_LENGTH_CATE));
                        if ($tmpValue == $val) {
                            $applyInFl = true;
                            continue;
                        }
                    }
                }
            }

            // 추가 할인이 가능한경우
            if ($applyDcFl === true) {
                $getData['dcLine'] = $data['dcLine'];
                $getData['dcType'] = $data['dcType'];
                $getData['dcPercent'] = $data['dcPercent'];
                $getData['dcPrice'] = $data['dcPrice'];
                $getData['dcCut'] = $data['dcCut'];
                $getData['dcCutType'] = $data['dcCutType'];
            }

            // 중복 할인이 가능한경우
            if ($applyInFl === true) {
                $getData['overlapDcLine'] = $data['overlapDcLine'];
                $getData['overlapDcType'] = $data['overlapDcType'];
                $getData['overlapDcPercent'] = $data['overlapDcPercent'];
                $getData['overlapDcPrice'] = $data['overlapDcPrice'];
                $getData['overlapDcCut'] = $data['overlapDcCut'];
                $getData['overlapDcCutType'] = $data['overlapDcCutType'];
            }

            //마일리지 관련
            $getData['mileageLine'] = $data['mileageLine'];
            $getData['mileagePercent'] = $data['mileagePercent'];

            //결제수단관련
            $getData['settleGb'] = $data['settleGb'];
        }

        return gd_isset($getData);
    }

    /**
     * 등급등록
     *
     * @param GroupDomain $vo 등급정보
     *
     * @return int 등급번호
     * @throws Exception 오류
     * @deprecated
     * @uses \Bundle\Component\Member\Group\GroupService::saveGroup
     */
    public function insertGroup(GroupDomain $vo)
    {
        $this->groupDomain = $vo;
        $this->groupDomain->setRegId(Session::get('manager.managerId'));
        $this->groupDomain->setManagerNo(Session::get('manager.sno'));
        $this->groupDomain->setDcLine(NumberUtils::commaRemover($this->groupDomain->getDcLine()));
        $this->groupDomain->setOverlapDcLine(NumberUtils::commaRemover($this->groupDomain->getOverlapDcLine()));
        $this->groupDomain->setMileageLine(NumberUtils::commaRemover($this->groupDomain->getMileageLine()));

        // groupSort 생성
        $strSQL = 'SELECT if(max(groupSort) > 0, (max(groupSort) + 1), 1) as newGroupSort FROM ' . DB_MEMBER_GROUP;
        $data = $this->db->query_fetch($strSQL, null, false);
        $this->groupDomain->setGroupSort($data['newGroupSort']);

        $this->_validateInsert();
        $fields = $this->v->getEleName();

        $this->groupDomain->toJsonByFixedRateOption();
        $this->groupDomain->toJsonByDiscountOption();
        $this->groupDomain->toJsonByOverlapOption();

        $arrBind = $this->db->get_binding(DBTableField::tableMemberGroup(), $this->groupDomain->toArray(), 'insert', $fields);
        $this->db->set_insert_db(DB_MEMBER_GROUP, $arrBind['param'], $arrBind['bind'], 'y');
        $sno = $this->db->insert_id();

        return $sno;
    }

    /**
     * addValidationFigurePrice
     * @return void
     */
    public function addValidationFigurePrice()
    {
        $this->v->add('apprFigureOrderPriceMore', 'double', true, '{' . __('최소 구매금액') . '}');
        $this->v->add('apprFigureOrderPriceBelow', 'double', true, '{' . __('최대 구매금액') . '}');
        $this->v->add('apprFigureOrderPriceMoreMobile', 'double', false, '{' . __('모바일샵 최소 구매금액') . '}');
        $this->v->add('apprFigureOrderPriceBelowMobile', 'double', false, '{' . __('모바일샵 최대 구매금액') . '}');
    }

    /**
     * addValidationFigureOrder
     * @return void
     */
    public function addValidationFigureOrder()
    {
        $this->v->add('apprFigureOrderRepeat', 'number', true, '{' . __('구매횟수') . '}');
        $this->v->add('apprFigureOrderRepeatMobile', 'number', false, '{' . __('모바일샵 구매횟수') . '}');
    }

    /**
     * addValidationFigureReview
     * @return void
     */
    public function addValidationFigureReview()
    {
        $this->v->add('apprFigureReviewRepeat', 'number', true, '{' . __('구매후기') . '}');
        $this->v->add('apprFigureReviewRepeatMobile', 'number', false, '{' . __('모바일샵 구매후기') . '}');
    }

    /**
     * addValidationPoint
     * @return void
     */
    public function addValidationPoint()
    {
        $this->v->add('apprPointMore', 'number', true, '{' . __('최소 실적점수') . '}');
        $this->v->add('apprPointBelow', 'number', true, '{' . __('최대 실적점수') . '}');
        $this->v->add('apprPointMoreMobile', 'number', false, '{' . __('모바일샵 최소 실적점수') . '}');
        $this->v->add('apprPointBelowMobile', 'number', false, '{' . __('모바일샵 최대 실적점수') . '}');
    }

    /**
     * 등급이름 중복체크
     *
     * @return bool 중복여부
     * @throws Exception 등급이름 검증 오류
     */
    public function overlapGroupNm()
    {
        $this->_validateOverlapGroupName();

        return $this->_countGroupName() > 0;
    }

    /**
     * 등급 수정
     *
     * @param GroupDomain $vo 등급정보
     *
     * @throws Exception 등급수정 검증 오류
     * @return void
     * @deprecated
     * @see \Bundle\Component\Member\Group\GroupService::saveGroup
     */
    public function modifyGroup(GroupDomain $vo)
    {
        $this->groupDomain = $vo;
        $this->groupDomain->setRegId(Session::get('manager.managerId'));
        $this->groupDomain->setManagerNo(Session::get('manager.sno'));
        $this->groupDomain->setDcLine(NumberUtils::commaRemover($this->groupDomain->getDcLine()));
        $this->groupDomain->setOverlapDcLine(NumberUtils::commaRemover($this->groupDomain->getOverlapDcLine()));
        $this->groupDomain->setMileageLine(NumberUtils::commaRemover($this->groupDomain->getMileageLine()));

        $this->_validateModify();
        $fields = $this->v->getEleName();
        $this->groupDomain->toJsonByFixedRateOption();
        $this->groupDomain->toJsonByDiscountOption();
        $this->groupDomain->toJsonByOverlapOption();
        $arrBind = $this->db->get_binding(DBTableField::tableMemberGroup(), $this->groupDomain->toArray(), 'update', $fields);
        $this->db->bind_param_push($arrBind['bind'], 'i', $this->groupDomain->getSno());
        $this->db->set_update_db(DB_MEMBER_GROUP, $arrBind['param'], 'sno = ? ', $arrBind['bind']);
    }

    /**
     * 등급삭제
     *
     * @param integer $sno 등급번호
     *
     * @throws Exception 등급삭제 검증 오류
     * @return void
     */
    public function deleteBySno($sno)
    {
        if (Validator::number($sno, null, null, true) === false) {
            throw new Exception(sprintf(__('%s 인자가 잘못되었습니다.'), 'sno'));
        }
        if (GroupUtil::getDefaultGroupSno() == $sno) {
            throw new Exception(__('가입회원등급은 삭제할 수 없습니다.'));
        }
        if ($this->getCount(DB_MEMBER_GROUP) < 2) {
            throw new Exception(__('회원등급은 최소 1개가 필요합니다.'));
        }

        $countGroupMember = $this->countGroupMember($sno);
        if ($countGroupMember > 0) {
            throw new Exception(sprintf(__('회원등급(NO:%s)에 회원(%s명)이 존재하여 삭제할 수 없습니다.'), $sno, $countGroupMember));
        }
        $this->db->set_delete_db(DB_MEMBER_GROUP, 'sno=' . $sno);
        $this->_resortGroup();
    }

    /**
     * 등급에 속한 회원 수 조회
     *
     * @param integer $sno 등급번호
     *
     * @return mixed
     */
    public function countGroupMember($sno)
    {
        return $this->getCount(DB_MEMBER, '1', ' WHERE groupSno=' . $sno);
    }

    /**
     * 등급평가 기준 수정
     *
     * @param GroupDomain $vo 등급정보
     *
     * @throws Exception 등급평가 기준 수정 오류
     * @return void
     */
    public function modifyGroupByAppraisalRule(GroupDomain $vo)
    {
        $this->groupDomain = $vo;
        $this->validateByAppraisalRule();
        $fields = $this->v->getEleName();

        $arrBind = $this->db->get_binding(DBTableField::tableMemberGroup(), $vo->toArray(), 'update', $fields);
        $this->db->bind_param_push($arrBind['bind'], 'i', $vo->getSno());
        $this->db->set_update_db(DB_MEMBER_GROUP, $arrBind['param'], 'sno = ?', $arrBind['bind']);
    }

    /**
     * 등급평가기준 검증
     *
     * @throws Exception 등급평가 기준 검증 오류
     * @return void
     */
    public function validateByAppraisalRule()
    {
        $this->v->init();
        $this->v->add('sno', 'number', true);
        if ($this->apprSystem === 'figure') {
            $this->v->add('apprFigureOrderPriceFl', 'yn');
            $this->v->add('apprFigureOrderRepeatFl', 'yn');
            $this->v->add('apprFigureReviewRepeatFl', 'yn');
            $this->addValidationFigurePrice();
            $this->addValidationFigureOrder();
            $this->addValidationFigureReview();
        }
        if ($this->apprSystem === 'point') {
            $this->addValidationPoint();
        }
        if ($this->v->act($this->groupDomain->toArray(), true) === false) {
            throw new Exception(implode("\n", $this->v->errors));
        }
    }

    /**
     * 등급순서변경
     *
     * @param array $arrData 순서변경 데이터
     *
     * @throws Exception 순서변경 오류
     * @return void
     */
    public function modifySort($arrData)
    {
        $v = new Validator();
        $v->init();
        $v->add('sno', 'number', true);
        $v->add('groupSort', 'number');
        if ($v->act($arrData, true) === false) {
            throw new Exception(implode("\n", $v->errors));
        }
        $fields = $v->getEleName();

        // 수정 처리
        $arrBind = $this->db->get_binding(DBTableField::tableMemberGroup(), $arrData, 'update', $fields);
        $this->db->bind_param_push($arrBind['bind'], 'i', $arrData['sno']);
        $this->db->set_update_db(DB_MEMBER_GROUP, $arrBind['param'], 'sno = ?', $arrBind['bind']);
    }

    /**
     * 회원평가등급저장
     *
     * @param integer $memNo 회원번호
     * @param array   $data  평가데이터
     *
     * @return void
     * @deprecated
     * @uses \Bundle\Component\Member\Group\GroupService::saveGroup
     */
    public function modifyMemberGroup($memNo, $data)
    {
        $arrBind = $arrData = [];
        $arrData['groupSno'] = $data['groupSno'];
        $arrData['groupModDt'] = date('Y-m-d');
        if (empty($this->appraisalRule['calcKeep']) === false) {
            $arrData['groupValidDt'] = date('Y-m-d', strtotime('+' . $this->appraisalRule['calcKeep'] . ' month'));
        } else {
            $arrData['groupValidDt'] = '0000-00-00';
        }
        // 관리자메모
        $strSQL = "SELECT groupSno,adminMemo FROM " . DB_MEMBER . " WHERE memNo=?";
        $this->db->bind_param_push($arrBind, 's', $memNo);
        $tmp = $this->db->query_fetch($strSQL, $arrBind, false);
        $arrData['adminMemo'] = gd_htmlspecialchars_stripslashes($tmp['adminMemo']);
        $arrData['adminMemo'] .= "\n" . sprintf('===== ' . __('회원등급평가') . '(%s) =====', date('Y-m-d H:i:s'));
        $arrData['adminMemo'] .= "\n" . sprintf('● ' . __('등급변경') . ' - %s(%s) ⇒ %s(%s)', gd_isset($this->appraisalRule['group'][$tmp['groupSno']]['groupNm']), $tmp['groupSno'], gd_isset($this->appraisalRule['group'][$data['groupSno']]['groupNm']), $data['groupSno']);
        $arrData['adminMemo'] .= "\n" . '● ' . __('평가자료') . ' - ';
        $tmp2 = [];
        if (empty($data['salePrice']) === false) {
            array_push($tmp2, sprintf(__('구매금액') . ':%s', number_format($data['salePrice'])));
        }
        if (empty($data['saleCnt']) === false) {
            array_push($tmp2, sprintf(__('구매횟수') . ':%s', number_format($data['saleCnt'])));
        }
        if (empty($data['point']) === false) {
            array_push($tmp2, sprintf(__('합산점수') . ':%s', number_format($data['point'])));
        }
        $arrData['adminMemo'] .= implode(', ', $tmp2) . "\n";
        // 저장
        $arrBind = $this->db->get_binding(DBTableField::tableMember(), $arrData, 'update', array_keys($arrData));
        $this->db->bind_param_push($arrBind['bind'], 'i', $memNo);
        $this->db->set_update_db(DB_MEMBER, $arrBind['param'], 'memNo = ?', $arrBind['bind'], false);
    }

    /**
     * setAppraisalFl
     *
     * @param string $apprSystem 평가방식
     *
     * @return void
     */
    public function setAppraisalFl($apprSystem)
    {
        if (empty($apprSystem)) {
            $apprSystem = null;
        }
        $this->apprSystem = $apprSystem;
    }

    /**
     * setGroupDomain
     *
     * @param GroupDomain $groupDomain 등급정보
     *
     * @return void
     */
    public function setGroupDomain($groupDomain)
    {
        $this->groupDomain = $groupDomain;
    }

    public function getGroupListSelectBox($param)
    {
        $getData = $this->getGroupList();
        $setData['cnt'] = $getData['cnt'];
        foreach ($getData['data'] as $value) {
            $setData['data'][$value[$param['key']]] = $value[$param['value']];
        }

        return $setData;
    }

    /** @deprecated */
    private function _validateInsert()
    {
        $this->v->init();
        $this->_validateGroup($this->groupDomain);

        if ($this->overlapGroupNm()) {
            throw new Exception(sprintf(__('%s는 이미 등록된 등급이름입니다'), $this->groupDomain->getGroupNm()));
        }

        /** @var \Bundle\Component\Member\Group\GroupValidation $validation */
        $validation = App::load('\\Component\\Member\\Group\\GroupValidation');
        $validation->setDomain($this->groupDomain);
        $validation->validateStandard();
    }

    /**
     * 회원등급수정 검증
     *
     * @param GroupDomain $vo
     *
     * @return Validator object 검증객체
     * @throws Exception
     *
     */
    private function _validateGroup(GroupDomain $vo)
    {
        $this->v->add('groupNm', '', true);
        $this->v->add('groupSort', 'number');
        $this->v->add('groupMarkGb', '');
        $this->v->add('groupIcon', '');
        $this->v->add('groupImage', '');
        $this->v->add('apprFigureOrderPriceFl', 'yn');
        $this->v->add('apprFigureOrderRepeatFl', 'yn');
        $this->v->add('apprFigureReviewRepeatFl', 'yn');
        if ($vo->isApprFigureOrderPriceFl()) {
            if ($vo->greaterThanEqual($vo->getApprFigureOrderPriceMore(), $vo->getApprFigureOrderPriceBelow())) {
                throw new Exception(__('최소 금액은 최대 금액보다 작게 입력하셔야 합니다.'));
            }
            if ($vo->greaterThanEqual($vo->getApprFigureOrderPriceMoreMobile(), $vo->getApprFigureOrderPriceBelowMobile())) {
                throw new Exception(__('최소 금액은 최대 금액보다 작게 입력하셔야 합니다.'));
            }
            $this->addValidationFigurePrice();
        }
        if ($vo->isApprFigureOrderRepeatFl()) {
            $this->addValidationFigureOrder();
        }
        if ($vo->isApprFigureReviewRepeatFl()) {
            $this->addValidationFigureReview();
        }
        if ($vo->greaterThanEqual($vo->getApprPointMore(), $vo->getApprPointBelow())) {
            throw new Exception(__('최소 금액은 최대 금액보다 작게 입력하셔야 합니다.'));
        }
        if ($vo->greaterThanEqual($vo->getApprPointMoreMobile(), $vo->getApprPointBelowMobile())) {
            throw new Exception(__('최소 점수는 최대 점수보다 작게 입력하셔야 합니다.'));
        }
        $this->addValidationPoint();
        $this->v->add('settleGb', '');
        $this->v->add('fixedRateOption', '');
        $this->v->add('dcExOption', '');
        $this->v->add('dcExScm', '');
        $this->v->add('dcExCategory', '');
        $this->v->add('dcExBrand', '');
        $this->v->add('dcExGoods', '');
        $this->v->add('overlapDcOption', '');
        $this->v->add('overlapDcScm', '');
        $this->v->add('overlapDcCategory', '');
        $this->v->add('overlapDcBrand', '');
        $this->v->add('overlapDcGoods', '');
        $this->v->add('dcLine', 'double');
        $this->v->add('dcType', '');
        $this->v->add('apprExclusionOfRatingFl', 'yn');
        if ($vo->getDcType() === 'percent') {
            $this->v->add('dcPercent', 'double', false, '', 0, 100);
        } else if ($vo->getDcType() === 'price') {
            $this->v->add('dcPrice', 'double');
        }
        $this->v->add('overlapDcType', '');
        $this->v->add('overlapDcLine', 'double', false, '{' . __('중복할인') . '}');
        if ($vo->getOverlapDcType() === 'percent') {
            $this->v->add('overlapDcPercent', 'double', false, '', 0, 100);
        } else if ($vo->getOverlapDcType() === 'price') {
            $this->v->add('overlapDcPrice', 'double');
        }
        $this->v->add('mileageLine', 'double', false, '{' . __('추가 마일리지 적립') . '}');
        $this->v->add('mileageType', '');
        if ($vo->getMileageType() === 'percent') {
            $this->v->add('mileagePercent', 'double', false, '', 0, 100);
        } else if ($vo->getMileageType() === 'price') {
            $this->v->add('mileagePrice', 'double');
        }
        $this->v->add('deliveryFreeFl', 'yn');
        $this->v->add('regId', 'userid');
        $this->v->add('managerNo', '');
        if ($this->v->act($vo->toArray(), true) === false) {
            \Logger::error(__METHOD__, $this->v->errors);
            throw new Exception(implode("\n", $this->v->errors));
        }

        return $this->v;
    }

    private function _validateOverlapGroupName()
    {
        if ($this->groupDomain->getGroupNm() == '') {
            throw new Exception(__('등급명이 없습니다.'));
        }
    }

    private function _countGroupName()
    {
        $sno = $this->groupDomain->getSno();
        $where = 'WHERE groupNm=\'' . $this->groupDomain->getGroupNm() . '\'';
        if ($sno != '') {
            $where .= ' AND sno!=' . $sno;
        }

        return $this->getCount(DB_MEMBER_GROUP, '1', $where);
    }

    private function _validateModify()
    {
        $this->v->init();
        $this->v->add('sno', 'number', true);
        $this->_validateGroup($this->groupDomain);

        if ($this->overlapGroupNm()) {
            throw new Exception(sprintf(__('%s는 이미 등록된 등급이름입니다'), $this->groupDomain->getGroupNm()));
        }

        /** @var \Bundle\Component\Member\Group\GroupValidation $validation */
        $validation = App::load('\\Component\\Member\\Group\\GroupValidation');
        $validation->setDomain($this->groupDomain);
        if ($this->groupDomain->getSno() != 1) {
            $validation->validateStandard();
        }
    }

    private function _resortGroup()
    {
        // 등급 순서 갱신
        $strSQL = 'SELECT sno, groupSort FROM ' . DB_MEMBER_GROUP . ' ORDER BY groupSort DESC';
        $result = $this->db->query($strSQL);
        $groupCnt = $this->db->getCount(DB_MEMBER_GROUP);

        while ($data = $this->db->fetch($result)) {
            $this->db->bind_param_push($arrBind, 'i', $groupCnt);
            $this->db->bind_param_push($arrBind, 'i', $data['sno']);
            $this->db->set_update_db(DB_MEMBER_GROUP, 'groupSort = ?', 'sno = ?', $arrBind);
            unset($arrBind);
            $groupCnt--;
        }
    }
}
