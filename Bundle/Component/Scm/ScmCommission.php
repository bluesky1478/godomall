<?php
/**
 * ScmCommission Class
 *
 * @author    tomi
 * @version   1.0
 * @since     1.0
 * @copyright ⓒ 2016, NHN godo: Corp.
 */

namespace Bundle\Component\Scm;

use App;
use Component\Database\DBTableField;
use Component\Validator\Validator;
use Framework\Utility\ArrayUtils;
use Framework\Utility\SkinUtils;
use Framework\Utility\StringUtils;
use Framework\Utility\DateTimeUtils;
use phpDocumentor\Reflection\Types\Boolean;
use Request;
use Respect\Validation\Exceptions\FalseValException;
use Session;

class ScmCommission
{
    const TEXT_USELESS_DATE = '선택된 기간에 등록된 수수료 일정이 있습니다.';
    const TEXT_USELESS_DATE_COVER = '기간설정의 시작일/종료일을 확인해 주세요.';
    const TEXT_SAVE_COMPLETE = '저장이 완료되었습니다.';
    const TEXT_DELETE_COMPLETE = '삭제가 완료되었습니다.';
    const TEXT_STOP_COMPLETE = '일정이 종료되었습니다.';
    const TEXT_STOP_FAIL = '일정이 종료 실패했습니다.';
    const TEXT_DELETE_FAIL = '삭제 실패했습니다. 시작일이 현재보다 미래인 경우 삭제 가능합니다.';

    public $scmDbFieldArray = [
        'goods' => 'exceptGoodsNo',
        'brand' => 'exceptBrandCd',
        'coupon'=> 'exceptCouponSno',
        'member'=> 'exceptMemberGroupSno'
    ];

    /**
     * 생성자
     *
     * @author tomi
     */
    public function __construct()
    {
        if (!is_object($this->db)) {
            $this->db = \App::load('DB');
        }
    }

    /**
     * 공급사 수수료 스케쥴 월별 페이지 로딩(scm_commission_list)
     *
     * @param array $getData
     * @return array $getData
     * @author tomi
     */
    public function getScmCommissionScheduleAdminList($getData =[])
    {
        $this->arrBind = $this->strWhere = $returnData = [];

        if(empty($getData['search']['scmNo']) === false ) { // 공급사일련번호
            gd_isset($getData['search']['scmFl'], '1');
            if(is_array($getData['search']['scmNo'])) { // 복수 공급사 검색
                $arrOrWhere = [];
                foreach($getData['search']['scmNo'] as $scmNoVal) {
                    $arrOrWhere[] = " scs.scmNo = ? ";
                    $this->db->bind_param_push($this->arrBind, 'i', $scmNoVal);
                }
                $this->arrWhere[] = '(' . implode(" OR ", $arrOrWhere) . ')';
            } else { // 단일 공급사 검색
                $this->arrWhere[] = " scs.scmNo = ? ";
                $this->db->bind_param_push($this->arrBind, 'i', $getData['search']['scmNo']);
            }
        }
        if(gd_isset($getData['search']['scmNmSearch'])) { // 공급사명 검색
            $this->arrWhere[] = " scm.companyNm LIKE concat('%',?,'%') ";
            $this->db->bind_param_push($this->arrBind, 's', $getData['search']['scmNmSearch']);
        }
        $join[] = ' LEFT JOIN ' . DB_SCM_MANAGE . ' as scm ON scm.scmNo = scs.scmNo ';

        $this->arrWhere[] = "scs.delFl = ? ";
        $this->db->bind_param_push($this->arrBind, 's', 'n');

        $monthPeriodDate = DateTimeUtils::getLastMonthPeriodDate();

        if($getData['search']['startDate'] && $getData['search']['endDate']) { // 변경일자
            if($getData['search']['mode'] == 'schedule') { // 스케쥴형태 데이터 호출 (한달)
                $monthPeriodDate[0] = implode('-', [$getData['calendar']['setYear'], sprintf("%02d", $getData['calendar']['setMonth']), '01']);
                $monthPeriodDate[1] = implode('-', [$getData['calendar']['setYear'], sprintf("%02d", $getData['calendar']['setMonth']), sprintf("%02d", $getData['calendar']['days'])]);
            } else {
                $monthPeriodDate[0] = $getData['search']['startDate'];
                $monthPeriodDate[1] = $getData['search']['endDate'];
            }
        }
        $this->arrWhere[] = "('" . $monthPeriodDate[0] . " 00:00:00' <= scs.startDate AND scs.endDate <= '" . $monthPeriodDate[0] . " 00:00:00')";

        $this->db->strField = "scs.sno, scs.scmCommissionSno, scs.scmCommissionDeliverySno, scs.applyCommissionLog, scs.scmNo, scs.startDate, scs.endDate, scm.companyNm as companyNm, scm.scmCommission as basicCommission, scm.scmCommissionDelivery as basicCommissionDelivery";
        $this->db->strJoin = implode('', $join);
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));

        // union 으로 인해 bind 데이터 루프 - union1
        $addArrBind = $this->arrBind;
        foreach ($this->arrBind as $bind_key => $bind_val) {
            if ($bind_key > 0) {
                $this->arrBind[count($this->arrBind)] = $bind_val;
                $this->arrBind[0] .= substr($this->arrBind[0], ($bind_key-1), 1);
            }
        }

        if($getData['search']['mode'] == 'schedule') { // 스케쥴형태 데이터 호출 (페이징 Class 재정의 함)
            // union 으로 인해 bind 데이터 루프 - union2
            foreach ($addArrBind as $bind_key => $bind_val) {
                if ($bind_key > 0) {
                    $this->arrBind[count($this->arrBind)] = $bind_val;
                    $this->arrBind[0] .= substr($this->arrBind[0], ($bind_key-1), 1);
                }
            }

            // --- 페이지 기본설정
            gd_isset($getData['search']['page'], 1);
            gd_isset($getData['search']['pageNum'], 20);
            $page = \App::load('\\Component\\Page\\Page', $getData['search']['page']);

            // DB 검색 기본 쿼리 ( es_manager JOIN)
            //$strSQL = 'SELECT  scs.sno AS cnt FROM ' . DB_SCM_COMMISSION_SCHEDULE . ' as scs ' . $this->db->strJoin;
            // 총 카운트 쿼리
            $strTotalSQL = 'SELECT scs.sno AS cnt FROM ' . DB_SCM_COMMISSION_SCHEDULE . ' as scs Where delFl = \'n\'';
            // 검색 카운트 쿼리
            $strSearchSQL = 'SELECT scs.scmNo, scs.sno, scs.startDate, scs.endDate FROM ' . DB_SCM_COMMISSION_SCHEDULE . ' as scs ' . $this->db->strJoin;
            // 전체 카운트
            $strTotalCntSQL =  'SELECT COUNT(scsCnt.cnt) AS totalCnt FROM (' . $strTotalSQL . ')  as scsCnt';

            $resTotalCnt = $this->db->query_fetch($strTotalCntSQL, null, false);
            $page->recode['amount'] = $resTotalCnt['totalCnt'];

            if($this->db->strWhere) {
                $strSearchSQL .= ' WHERE ' . $this->db->strWhere;
            }
            // calendar CNT - 스케쥴 카운트 - union 데이터 후 프로그램 후 처리 - DBA 검수
            $unionSql = str_replace($monthPeriodDate[0] .' 00:00:00', $monthPeriodDate[1] . ' 23:59:59', $strSearchSQL);
            $tmpStrSearchSQL = $strSearchSQL; // replace를 위해 임시 선언
            $unionSql2 = ' UNION ' . str_replace("('" . $monthPeriodDate[0] . " 00:00:00' <= scs.startDate AND scs.endDate <= '" . $monthPeriodDate[0] . " 00:00:00')", "( '" . $monthPeriodDate[1] . " 23:59:59' >= scs.startDate AND scs.endDate >= '" . $monthPeriodDate[0] . " 00:00:00')", $tmpStrSearchSQL);
            $strSearchCntSQL =  'SELECT scsCnt.* FROM (' . $strSearchSQL . ' UNION ' . $unionSql . $unionSql2 . ')  as scsCnt';
            $resSearchCnt = $this->db->query_fetch($strSearchCntSQL, $this->arrBind, true);

            $overlapTotalScmNo = $overlapScmNo = $overLapCntScmNoArr = $overLapCntArr = [];
            foreach($resSearchCnt as $cntKey => $cntVal ) {
                // 시작일, 종료일 검색일 범위에 포함되는지 체크
                $intervalDateIncludeFl = $this->scmCalendarDateRangeCheck($cntVal['startDate'], $cntVal['endDate'], $getData['calendar']['data']);
                if($intervalDateIncludeFl == false) {
                    unset($resSearchCnt[$cntKey]);
                } else { // 범위 포함일 경우 
                    // 공급사 번호 전체 배열
                    if(array_key_exists($cntVal['scmNo'], $overlapScmNo) == false) { // 공급사 중복 스케쥴 파악
                        $overlapTotalScmNo[$cntVal['scmNo']][] = $cntVal['sno'];
                    }
                    // 스케쥴sno 전체배열
                    if(in_array($cntVal['sno'], $overlapScmNo[$cntVal['scmNo']]) == false) {
                        $overlapScmNo[$cntVal['scmNo']]++;
                    }
                }
            }
            // 페이징에 따라 배열 자르기
            $overlapArraySlice = array_chunk($overlapScmNo, $getData['search']['pageNum'], true);

            $overlapCnt = 0; // 공급사 중복 스케쥴 갯수
            foreach($overlapArraySlice[$getData['search']['page']-1] as $scmNoKey => $scmNoSno) {
                $overLapCntScmNoArr[] = $scmNoKey;
                $overlapCnt += $scmNoSno;
            }
            // 일정에 포함된 공급사를 위해 조건 추가
            $scheduleScmNoWhereStr = " AND scs.scmNo IN ('" . implode("','", $overLapCntScmNoArr) . "')";
            // 페이징 20건
            $page->page['list'] = $getData['search']['pageNum'];//ceil(count($overlapTotalScmNo) / $getData['search']['pageNum']);
            // 전체건수의 경우 기간에 포함된 공급사 건수만 배열에서 count
            $page->recode['total'] = count($overlapTotalScmNo);//$resSearchCnt['totalCnt']; // 검색 레코드 수

            // 페이지 데이터 재적용
            $page->setPage();
            $page->setUrl(\Request::getQueryString());
            unset($resSearchCnt, $overlapTotalScmNo, $overlapScmNo, $overlapArraySlice, $overLapCntScmNoArr);
        } else { // calendar
            // union 으로 인해 bind 데이터 루프 - union2
            foreach ($addArrBind as $bind_key => $bind_val) {
                if ($bind_key > 0) {
                    $this->arrBind[count($this->arrBind)] = $bind_val;
                    $this->arrBind[0] .= substr($this->arrBind[0], ($bind_key-1), 1);
                }
            }
        }

        // data - 캘린더, 스케쥴 기본 데이터 UNION 방식 - DBA 검수
        $query3 = $query2 = $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_SCM_COMMISSION_SCHEDULE . ' as scs ' . implode(' ', $query) . $scheduleScmNoWhereStr . ' UNION ';
        // union(기간 범위 검색 startDate, endDate)
        $strSQL .= 'SELECT ' . array_shift($query2) . ' FROM ' . DB_SCM_COMMISSION_SCHEDULE . ' as scs ' . str_replace($monthPeriodDate[0] .' 00:00:00', $monthPeriodDate[1] . ' 23:59:59', implode(' ',  $query2)) . $scheduleScmNoWhereStr;
        // union(기간 범위 검색 startDate 큰 경우)
        $strSQL .= ' UNION SELECT ' . array_shift($query3) . ' FROM ' . DB_SCM_COMMISSION_SCHEDULE . ' as scs ' . str_replace("('" . $monthPeriodDate[0] . " 00:00:00' <= scs.startDate AND scs.endDate <= '" . $monthPeriodDate[0] . " 00:00:00')", "( '" . $monthPeriodDate[1] . " 23:59:59' >= scs.startDate AND scs.endDate >= '" . $monthPeriodDate[0] . " 00:00:00')", implode(' ',  $query3)) . $scheduleScmNoWhereStr;
        $data = $this->db->query_fetch($strSQL, $this->arrBind);
        $dbData =  gd_htmlspecialchars_stripslashes(gd_isset($data));

        unset($this->arrBind);

        $nowDatetime =  DateTimeUtils::dateFormat('Y-m-d H:i:s', 'now');
        // DB 데이터 기준으로 데이터 삽입
        foreach($dbData as $cntKey => $cntVal) {
            $overTimeCommission = explode(INT_DIVISION, $cntVal['applyCommissionLog']);
            if(strtotime($cntVal['endDate']) < strtotime($nowDatetime) && empty($overTimeCommission[0]) == false && empty($overTimeCommission[1]) == false) {
                $overTimeCommission = explode(INT_DIVISION, $cntVal['applyCommissionLog']);
                $dbData[$cntKey]['scmCommissionData'] = $overTimeCommission[0];
                $dbData[$cntKey]['scmCommissionDeliveryData'] = $overTimeCommission[1];
            } else {
                // 추가 수수료 정보
                if($cntVal['scmCommissionSno'] > 0) {
                    $dbData[$cntKey]['scmCommissionData'] = $this->getScmCommissionSno($cntVal['scmCommissionSno'], null, true)['commissionValue'];
                } else {
                    // 추가 수수료 데이터가 없는 경우 기본 수수료 삽입
                    $dbData[$cntKey]['scmCommissionData'] = $dbData[$cntKey]['basicCommission'];
                }
                if($cntVal['scmCommissionDeliverySno'] > 0) {
                    if($cntVal['scmCommissionSno'] != $cntVal['scmCommissionDeliverySno']) {
                        $dbData[$cntKey]['scmCommissionDeliveryData'] = $this->getScmCommissionSno($cntVal['scmCommissionDeliverySno'], null, true)['commissionValue'];
                    } else {
                    }
                } else {
                    if($cntVal['scmCommissionSno'] == $cntVal['scmCommissionDeliverySno']) {
                        $dbData[$cntKey]['scmCommissionDeliveryData'] = $dbData[$cntKey]['basicCommissionDelivery'];
                    } else {
                        $dbData[$cntKey]['scmCommissionDeliveryData'] = $dbData[$cntKey]['basicCommissionDelivery'];
                    }
                }
            }
            // 공급사 명
            $dbData[$cntKey]['companyNmCut'] = StringUtils::strCut($dbData[$cntKey]['companyNm'], 6);
        }
        // 일정 데이터에 날짜별 삽입 $getData['calendar']['data'][Y-m-d]
        foreach($getData['calendar']['data'] as $dateKey=> $dateVal) {
            foreach($dbData as $dbKey => $dbVal) {
                $startDate = explode(' ', $dbVal['startDate'])[0];
                $endDate = explode(' ', $dbVal['endDate'])[0];
                if($getData['search']['mode'] == 'schedule') { // 스케쥴형태 데이터 호출
                    $nowStartDate = implode('-', [$getData['calendar']['setYear'], $getData['calendar']['setMonth'], '01']);
                    $nowEndDate = implode('-', [$getData['calendar']['setYear'], $getData['calendar']['setMonth'], $getData['calendar']['days']]);
                    if((strtotime($startDate) <= strtotime($nowStartDate))) {
                        $startDate = $nowStartDate;
                    }
                    if((strtotime($endDate) >= strtotime($nowEndDate))) {
                        $endDate = $nowEndDate;
                    }
                    $dateDiff = (strtotime($endDate) - strtotime($startDate)) / 24 / 3600;
                    $dbVal['dateDiff'] = $dateDiff + 1;
                    $dbVal['startDateConvert'] = $startDate;
                    $dbVal['endDateConvert'] = $endDate;
                } else { } // 캘린더 형태 - default(DB데이터 다이렉트 삽입)
                if((strtotime($startDate) <= strtotime($dateKey)) && strtotime($endDate) >= strtotime($dateKey)) {
                    $getData['calendar']['data'][$dateKey][] = $dbVal;
                    if($getData['search']['mode'] == 'schedule') { // 스케쥴형태 데이터 호출
                        if(in_array($dbVal['companyNm'], $getData['schedule']['companyNm']) == false) {
                            $getData['schedule']['companyNm'][$dbVal['scmNo']] = $dbVal['companyNm'];
                        }
                    }
                }
            }
        }
        $returnData = $getData; // 캘린더 데이터 및 search 데이터 삽입

        unset($getData, $dbData);
        return $returnData;
    }

    /**
     * 공급사 수수료 스케쥴 등록페이지(scm_commission_register)
     * 일정등록 window popup
     *
     * @param int $scmNo
     * @param int $scmScheduleSno
     * @return array $returnData
     * @author tomi
     */
    public function setScmCommissionScheduleRegister($scmNo = null, $scmScheduleSno = null)
    {
        $getCommissionScheduleData = $returnData = $this->checked = $this->selected = [];
        if($scmNo && $scmScheduleSno) { // 수정
            // 수수료 테이블 데이터 조회
            $getCommissionValueData = $this->getScmCommissionDataConvert($scmNo);
            // 수수료 일정 테이블 데이터 조회
            $getCommissionScheduleData = $this->getScmCommissionScheduleDataOnce($scmNo, $scmScheduleSno);
            if($getCommissionScheduleData) {
                // 적용 및 예외 조건 코드 데이터 변환(코드 > 이름)
                $returnData = $this->setScmCommissionApplyExceptData($getCommissionScheduleData);
                if($returnData['modifyAbleFl'] =='n' && $returnData['delAbleFl'] == 'n') {
                    // 종료된 일정 수수료 테이블 데이터 조회-allFl의 경우 종료된 일정의 수수료 delFl 상관없이 로드
                    $getCommissionValueData = $this->getScmCommissionDataConvert($scmNo, true);
                }
                // 적용 예외 조건 checked
                foreach($this->scmDbFieldArray as $exceptKey => $exceptVal) {
                    if(empty($returnData[$exceptVal]) === false) {
                        $this->checked['exceptKind'][$exceptKey] = "checked='checked'";
                    }
                }

                $returnData['selectBoxData']['scmCommission'] = $getCommissionValueData['scmCommission'];
                $returnData['commissionSameFl'] = $getCommissionValueData['commissionSameFl'];
                $returnData['selectBoxData']['scmCommissionDelivery'] = $getCommissionValueData['scmCommissionDelivery'];
                $returnData['companyNm'] = $getCommissionValueData['companyNm'];

                $this->selected['scmCommissionDelivery'][$getCommissionScheduleData['scmCommissionDeliverySno']] = $this->selected['scmCommission'][$getCommissionScheduleData['scmCommissionSno']] = "selected='selected'";
                $returnData['selected'] = $this->selected;
            }
        } else {
            gd_isset($returnData['startDateAutoFl'], 'y');
        }

        gd_isset($getCommissionScheduleData['applyKind'], 'all');
        gd_isset($returnData['delAbleFl'], 'y');
        gd_isset($returnData['modifyAbleFl'], 'y');
        $this->checked['startDateAutoFl'][$returnData['startDateAutoFl']] = "checked='checked'";
        $this->checked['applyKind'][$getCommissionScheduleData['applyKind']] = "checked='checked'";
        $returnData['checked'] = $this->checked;
        unset($getCommissionScheduleData);
        return $returnData;
    }

    /**
     * 공급사 수수료 적용 조건 / 예외 조건 데이터 가공 (수수료 일정 등록 window popup)
     * 수수료 용 조건 / 예외 조건  setScmCommissionScheduleRegister 사용
     * code[name]
     *
     * @param array $arrData
     * @return array $arrData
     * @author tomi
     */
    public function setScmCommissionApplyExceptData($arrData=[])
    {
        $returnData = [];
        $arrData['delAbleFl'] = 'n';
        $arrData['modifyAbleFl'] = 'n';
        $nowDate = DateTimeUtils::dateFormat('Y-m-d H:i:s', 'now');
        if(strtotime($arrData['startDate']) > strtotime($nowDate)) {
            $arrData['delAbleFl'] = 'y';
            $arrData['modifyAbleFl'] = 'y';
        }
        else if(strtotime($arrData['endDate']) < strtotime($nowDate)) {
            $arrData['delAbleFl'] = 'n';
            $arrData['modifyAbleFl'] = 'n';
        }
        else if(strtotime($arrData['startDate']) < strtotime($nowDate) && strtotime($arrData['endDate']) < strtotime($nowDate)) {
            $arrData['delAbleFl'] = 'n';
            $arrData['modifyAbleFl'] = 'y';
        } else {
            $arrData['delAbleFl'] = 'n';
            $arrData['modifyAbleFl'] = 'y';
        }
        if($arrData['applyKind'] != 'all' && empty($arrData['applyData']) === false) {
            $convertData = [];
            $splitData = explode(INT_DIVISION, $arrData['applyData']);
            switch($arrData['applyKind']) {
                case 'goods' :
                    $goodsList = \App::load('\\Component\\Goods\\Goods');
                    foreach($splitData as $dataKey => $dataVal) {
                        $tmpGoodsData = $goodsList->getGoodsInfo($dataVal, 'goodsNo, goodsNm, imagePath, imageStorage');
                        $tmpImageData = $goodsList->getGoodsImage($dataVal, 'list')[0]; // 이미지 정보
                        $tmpGoodsData['imageName'] = $tmpImageData['imageName'];
                        $convertData[] = $tmpGoodsData;
                    }
                    if(empty($convertData) === false) {
                        foreach($convertData as $cKey => $cVal) {
                            $returnData['applyDataConvert'][$cKey]['goodsNo'] = $cVal['goodsNo'];
                            $returnData['applyDataConvert'][$cKey]['goodsNm'] = $cVal['goodsNm'];
                            $returnData['applyDataConvert'][$cKey]['imagePath'] = $cVal['imagePath'];
                            $returnData['applyDataConvert'][$cKey]['imageStorage'] = $cVal['imageStorage'];
                            $returnData['applyDataConvert'][$cKey]['imageName'] = $cVal['imageName'];
                        }
                    }
                    break;
                case 'brand' :
                    $brandList = \App::load('\\Component\\Category\\Brand');
                    foreach($splitData as $dataKey => $dataVal) {
                        $externalData = $brandList->getCategoryInfo($dataVal, 'cateCd, cateNm');
                        $returnData['applyDataConvert']['code'][] = $externalData['cateCd'];
                        $returnData['applyDataConvert']['name'][] = $externalData['cateNm'];
                    }
                    break;
                case 'coupon' :
                    $couponList = \App::load('\\Component\\Coupon\\Coupon');
                    foreach($splitData as $dataKey => $dataVal) {
                        $externalData = $couponList->getCouponInfo($dataVal, 'couponNo, couponNm');
                        $returnData['applyDataConvert']['code'][] = $externalData['couponNo'];
                        $returnData['applyDataConvert']['name'][] = $externalData['couponNm'];
                    }
                    break;
                case 'member' :
                    // 회원그룹리스트
                    $memberGroup = \App::load('\\Component\\Member\\MemberGroup');
                    foreach($splitData as $dataKey => $dataVal) {
                        $externalData = $memberGroup->getGroupViewToArray($dataVal);
                        $returnData['applyDataConvert']['code'][] = $externalData['sno'];
                        $returnData['applyDataConvert']['name'][] = $externalData['groupNm'];

                    }
                    break;
            }
        }
        // 적용예외
        foreach($this->scmDbFieldArray as $exceptKey => $exceptVal) {
            if(empty($arrData[$exceptVal]) === false) {
                $convertExceptData = [];
                $splitData = explode(INT_DIVISION, $arrData[$exceptVal]);
                switch($exceptKey) {
                    case 'goods' :
                        $goodsList = \App::load('\\Component\\Goods\\Goods');
                        foreach($splitData as $dataKey => $dataVal) {
                            $tmpGoodsData = $goodsList->getGoodsInfo($dataVal, 'goodsNo, goodsNm, imagePath, imageStorage');
                            $tmpImageData = $goodsList->getGoodsImage($dataVal, 'list')[0]; // 이미지 정보
                            $tmpGoodsData['imageName'] = $tmpImageData['imageName'];
                            $convertExceptData[] = $tmpGoodsData;
                        }
                        if(empty($convertExceptData) === false) {
                            foreach($convertExceptData as $cKey => $cVal) {
                                $returnData[$exceptVal][$cKey]['goodsNo'] = $cVal['goodsNo'];
                                $returnData[$exceptVal][$cKey]['goodsNm'] = $cVal['goodsNm'];
                                $returnData[$exceptVal][$cKey]['imagePath'] = $cVal['imagePath'];
                                $returnData[$exceptVal][$cKey]['imageStorage'] = $cVal['imageStorage'];
                                $returnData[$exceptVal][$cKey]['imageName'] = $cVal['imageName'];
                            }
                        }
                        break;
                    case 'brand' :
                        $brandList = \App::load('\\Component\\Category\\Brand');
                        foreach($splitData as $dataKey => $dataVal) {
                            $externalData = $brandList->getCategoryInfo($dataVal, 'cateCd, cateNm');
                            $returnData[$exceptVal]['code'][] = $externalData['cateCd'];
                            $returnData[$exceptVal]['name'][] = $externalData['cateNm'];
                        }
                        break;
                    case 'coupon' :
                        $couponList = \App::load('\\Component\\Coupon\\Coupon');
                        foreach($splitData as $dataKey => $dataVal) {
                            $externalData = $couponList->getCouponInfo($dataVal, 'couponNo, couponNm');
                            $returnData[$exceptVal]['code'][] = $externalData['couponNo'];
                            $returnData[$exceptVal]['name'][] = $externalData['couponNm'];
                        }
                        break;
                    case 'member' :
                        // 회원그룹리스트
                        $memberGroup = \App::load('\\Component\\Member\\MemberGroup');
                        foreach($splitData as $dataKey => $dataVal) {
                            $externalData = $memberGroup->getGroupViewToArray($dataVal);
                            $returnData[$exceptVal]['code'][] = $externalData['sno'];
                            $returnData[$exceptVal]['name'][] = $externalData['groupNm'];

                        }
                        break;
                }
            }
        }

        if(empty($returnData) === false) {
            $arrData = array_merge($arrData, $returnData);
            unset($returnData);
        }
        return $arrData;
    }

    /**
     * 공급사 추가 수수료 가져오기(공급사 scmNo 기준)
     * es_scmCommission
     *
     * @param $scmNo
     * @param string $type sell / delivery
     * @param boolean $allFl - allFl의 경우 종료된 일정의 수수료 delFl 상관없이 로드
     *
     * @return array $data
     * @author KimYeonKyung
     */
    public function getScmCommission($scmNo, $type=null, $allFl=null)
    {
        $arrBind = [];
        $this->db->strField = "sno, scmNo, commissionType, commissionValue, delFl";
        if($allFl == true) {
            $this->db->strWhere = "scmNo=?";
        } else {
            $this->db->strWhere = "scmNo=? AND delFl=?";
        }
        $this->db->strOrder = 'sno asc';
        $this->db->bind_param_push($arrBind, 'i', $scmNo);
        if($allFl == false) {
            $this->db->bind_param_push($arrBind, 's', 'n');
        }
        if($type) {
            $this->db->strWhere = $this->db->strWhere . ' AND commissionType=?';
            $this->db->bind_param_push($arrBind, 's', $type);
        }


        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_SCM_COMMISSION . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $arrBind, true);

        return $data;
    }

    /**
     * 공급사 추가 수수료 가져오기(공급사 수수료 테이블 sno 기준)
     * es_scmCommission
     *
     * @param int $sno
     * @param string $type sell / delivery
     * @param boolean $allFl - allFl의 경우 종료된 일정의 수수료 delFl 상관없이 로드
     *
     * @return array $data
     * @author KimYeonKyung
     */
    public function getScmCommissionSno($sno, $type=null, $allFl=null)
    {
        $arrBind = [];
        if($sno > 0 ) {
            $this->db->strField = "sno, scmNo, commissionType, commissionValue, delFl";
            if($allFl == true) {
                $this->db->strWhere = "sno=?";
            } else {
                $this->db->strWhere = "sno=? AND delFl=?";
            }
            $this->db->bind_param_push($arrBind, 'i', $sno);
            if($allFl == false) {
                $this->db->bind_param_push($arrBind, 's', 'n');
            }
            if($type) {
                $this->db->strWhere = $this->db->strWhere . ' AND commissionType=?';
                $this->db->bind_param_push($arrBind, 's', $type);
            }
            $query = $this->db->query_complete();
            $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_SCM_COMMISSION . implode(' ', $query);
            $data = $this->db->query_fetch($strSQL, $arrBind, false);
            return $data;
        } else {
            return [];
        }
    }

    /**
     * 공급사 수수료 스케쥴 정보 가져오기 - 일정등록 window popup / front 수수료 convert 시 사용
     * es_scmCommissionSchedule
     * 수수료 일정등록  - 일정등록 window popup / front 수수료 convert 시 사용 (1개 개별 호출)
     *
     * @param int $scmNo
     * @param int $scmScheduleSno
     * @param string $requestWhere
     * @param boolean $unionFl
     * @return array $returnData
     * @author tomi
     */
    public function getScmCommissionScheduleDataOnce($scmNo, $scmScheduleSno=null, $requestWhere=null, $unionFl = null)
    {
        $returnData = $arrBind = [];
        $strWhere = " WHERE scmNo = ? AND delFl = 'n'";
        $this->db->bind_param_push($arrBind, 'i', $scmNo);
        if(gd_isset($scmScheduleSno)) {
            $strWhere = $strWhere . " AND sno = ? " ;
            $this->db->bind_param_push($arrBind, 'i', $scmScheduleSno);
        }
        if($requestWhere) {
            $strWhere = $strWhere . $requestWhere;
        }

        $strSQL = 'SELECT * FROM ' . DB_SCM_COMMISSION_SCHEDULE . ' as scs' . $strWhere;

        $strSQL = str_replace('scs.', '', $strSQL);
        if($unionFl == true) {
            $this->db->bind_param_push($arrBind, 'i', $scmNo);
            if($scmScheduleSno) {
                $this->db->bind_param_push($arrBind, 'i', $scmScheduleSno);
            }
            $strSQL = $strSQL . ' UNION ' . str_replace('startDate', 'endDate', $strSQL);
        }

        $returnData = $this->db->query_fetch($strSQL, $arrBind, false);
        return $returnData;
    }

    /**
     * 공급사 수수료 정보 가져오기 - ajax SelectBox 생성
     * es_scmCommission
     * 수수료 일정등록 페이지 - register
     *
     * @param int $scmNo
     * @param boolean $allFl - allFl의 경우 종료된 일정의 수수료 delFl 상관없이 로드
     * @return array
     * @author tomi
     */
    public function getScmCommissionDataConvert($scmNo, $allFl=null)
    {
        $returnData = $arrBind = $data = [];

        $commissionSameFl = true; // 판매 수수료 동일적용 플래그(같음-true, 다름-false)
        // 공급사 정보 - 기본수수료

        $compareTempArray = [];
        $this->db->bind_param_push($arrBind, 'i', $scmNo);
        $strSQL = 'SELECT companyNm, scmCommission, scmCommissionDelivery FROM ' . DB_SCM_MANAGE . ' WHERE scmNo = ?';
        $data = $this->db->query_fetch($strSQL, $arrBind, false);
        unset($arrBind);

        if($data['companyNm']) {
            $returnData['companyNm'] = $data['companyNm'];
        }
        $returnData['scmCommission'][0] = $data['scmCommission'];
        $returnData['scmCommissionDelivery'][0] = $data['scmCommissionDelivery'];
        $compareTempArray[] = ['commissionValue'=>$data['scmCommission'], 'commissionType'=>'sell'];
        $compareTempArray[] = ['commissionValue'=>$data['scmCommissionDelivery'], 'commissionType'=>'delivery'];

        $commissionSameFl = $this->compareWithScmCommission($compareTempArray); // 판매수수료동일적용 비교

        // 공급사 수수료 정보 - allFl의 경우 종료된 일정의 수수료 delFl 상관없이 로드
        $scmCommissionData = $this->getScmCommission($scmNo, null, $allFl);
        if(empty($scmCommissionData) === false) {
            if($commissionSameFl == true) {
                $commissionSameFl = $this->compareWithScmCommission($scmCommissionData); // 판매수수료동일적용 비교
            }
            foreach($scmCommissionData as $cmsKey => $cmsVal) {
                if ($cmsVal['commissionType'] == 'sell') {
                    $returnData['scmCommission'][$cmsVal['sno']] = $cmsVal['commissionValue'];
                } else {
                    $returnData['scmCommissionDelivery'][$cmsVal['sno']] = $cmsVal['commissionValue'];
                }
            }
        }

        $returnData['commissionSameFl'] = $commissionSameFl;

        // selectBox 데이터
        if(empty($returnData['scmCommission']) == false) {
            $returnData['selectBoxData']['scmCommission'] = gd_select_box('scmCommissionSno', 'scmCommissionSno', $returnData['scmCommission'], null, null, '=선택=');
        }

        if($commissionSameFl == false) {
            if(empty($returnData['scmCommissionDelivery']) == false) {
                $returnData['selectBoxData']['scmCommissionDelivery'] = gd_select_box('scmCommissionDeliverySno', 'scmCommissionDeliverySno', $returnData['scmCommissionDelivery'], null, null, '=선택=');
            }
        } else {
            $returnData['selectBoxData']['scmCommissionDelivery'] = '판매수수료 동일 적용';
        }

        unset($data, $scmCommissionData);
        return $returnData;
    }

    /**
     * 공급사 추가 수수료 일정 저장
     * saveScmScheduleCommission Save
     *
     * @param array $scheduleData
     * @return boolean db
     * @author tomi
     */
    public function saveScmScheduleCommission($scheduleData)
    {
        $arrBind = [];
        if($scheduleData) {
            // 수수료 적용 조건 배열 - kind <> input
            $applyArray = ['goods' => 'applyKindGoods', 'brand'=> 'applyKindBrand', 'coupon'=> 'applyKindCoupon','member'=> 'applyKindMember_group' ];
            // 수수료 적용 제외 조건 배열 - kind <> input
            $exceptArray = ['goods' => 'exceptGoods', 'brand'=> 'exceptBrand', 'coupon'=> 'exceptCoupon','member'=> 'exceptMember_group' ];
            // input <> DB 필드 배열 치환배열
            $dbFieldArray = ['exceptGoods' => 'exceptGoodsNo', 'exceptBrand'=> 'exceptBrandCd', 'exceptCoupon'=> 'exceptCouponSno','exceptMember_group'=> 'exceptMemberGroupSno'];

            if($scheduleData['startDateAutoFl'] == 'y') {
                $scheduleData['startDate'] = DateTimeUtils::dateFormat('Y-m-d H:i:s', 'now');
            }

            $validator = new Validator();
            $validator->add('applyKind', 'alpha', true); // 적용 모드
            $validator->add('scmNo', 'number', true); // 공급사 고유번호
            $validator->add('scmCommissionSno', 'number', true); // 판매수수료 연결번호
            $validator->add('scmCommissionDeliverySno', 'number', false); // 배송비 수수료 연결번호
            $validator->add('scmScheduleSno', 'number', false); // 스케쥴 일련번호
            $validator->add('startDate', '', false); // 스케쥴 시작일자
            $validator->add('endDate', '', false); // 스케쥴 종료일자
            $validator->add('applyExceptFl', 'alpha', false); // 배송비 수수료 연결번호
            // 적용 필드
            foreach($applyArray as $applyVal) {
                $validator->add($applyVal, 'number', false); // 판매수수료-%로 소수점 2자리
            }
            // 제외 필드
            foreach($exceptArray as $exceptVal) {
                $validator->add($exceptVal, 'number', false); // 판매수수료-%로 소수점 2자리
            }

            if ($validator->act($scheduleData, true) === false) {
                throw new \Exception(implode("<br/>", $validator->errors));
            }

            // 날짜 검증(시작, 종료 , 5년)
            $tempStartDate = explode(' ', $scheduleData['startDate'])[0] . ' 00:00:00';
            $tempEndDate = explode(' ', $scheduleData['endDate'])[0] . ' 23:59:59';
            if(empty($scheduleData['scmScheduleSno']) === true || $scheduleData['scmScheduleSno'] == '') {
                if(strtotime($scheduleData['startDate']) < strtotime("now") || strtotime($scheduleData['endDate']) < strtotime("now")) {
                    return ['mode'=>'false', 'message'=>self::TEXT_USELESS_DATE_COVER];
                }
                if(strtotime($tempStartDate) > strtotime("+5 year") || strtotime($tempEndDate) > strtotime("+5 year")) {
                    return ['mode'=>'false', 'message'=>self::TEXT_USELESS_DATE_COVER];
                }
                if(strtotime($scheduleData['startDate']) > strtotime($scheduleData['endDate'])) {
                    return ['mode'=>'false', 'message'=>self::TEXT_USELESS_DATE_COVER];
                }
            }

            // 기존 등록된 일정에 포함되는 값이 있는지 확인
            $requestWhere = " AND (( startDate between '" . $tempStartDate . "' AND'" . $tempEndDate .  "' ) OR ( endDate between '" . $tempStartDate . "' AND'" . $tempEndDate .  "' ))";
            if(empty($scheduleData['scmScheduleSno']) === true || $scheduleData['scmScheduleSno'] == '') {
                $registerDataCompare = $this->getScmCommissionScheduleDataOnce($scheduleData['scmNo'], null, $requestWhere);
                if(empty($registerDataCompare) == false) {
                    return ['mode'=>'false', 'message'=>self::TEXT_USELESS_DATE];
                }
            } else {
                $requestWhere = " AND (('" . $tempStartDate . "' <= scs.startDate AND scs.endDate <=  '" . $tempEndDate .  "' ) OR ('" . $tempStartDate . "' >= scs.startDate AND scs.endDate >=  '" . $tempEndDate .  "' ) )";
                $modifyDataCompare = $this->getScmCommissionScheduleDataOnce($scheduleData['scmNo'], null, $requestWhere, true);
                foreach($modifyDataCompare as $rKey => $rVal) {
                    if(strtotime($rVal['startDate']) > strtotime($tempStartDate)) {
                        unset($modifyDataCompare[$rKey]);
                    }
                    if(strtotime($rVal['endDate']) > strtotime($tempEndDate)) {
                        unset($modifyDataCompare[$rKey]);
                    }
                }
                if($modifyDataCompare['sno'] != $scheduleData['scmScheduleSno']) { // 수정하는 일정이 아닌 다른 일정과 겹칠 경우
                    return ['mode'=>'false', 'message'=>self::TEXT_USELESS_DATE];
                }
            }

            if(array_key_exists('scmCommissionDeliverySno', $scheduleData) == false) {
                $scheduleData['scmCommissionDeliverySno'] = $scheduleData['scmCommissionSno'];
            }

            // 적용 수수료 필드에 저장(종료 일정의 경우 수수료수정이 공급사 쪽에서 가능하기 때문에 로그성 데이터)
            $scmCommissionValue = $scmDeliveryCommissionValue = null;
            if($scheduleData['scmCommissionSno'] == 0 || $scheduleData['scmCommissionDeliverySno'] == 0 ) {
                $data = [];
                $this->db->bind_param_push($arrBind, 'i', $scheduleData['scmNo']);
                $strSQL = 'SELECT companyNm, scmCommission, scmCommissionDelivery FROM ' . DB_SCM_MANAGE . ' WHERE scmNo = ?';
                $data = $this->db->query_fetch($strSQL, $arrBind, false);
                unset($arrBind);
                if($scheduleData['scmCommissionSno'] == 0) {
                    $scmCommissionValue = $data['scmCommission'];
                }
                if($scheduleData['scmCommissionDeliverySno'] == 0) {
                    $scmDeliveryCommissionValue = $data['scmCommissionDelivery'];
                }
            } else {
                $scmCommissionValue = $this->getScmCommissionSno($scheduleData['scmCommissionSno'])['commissionValue'];
                $scmDeliveryCommissionValue = $this->getScmCommissionSno($scheduleData['scmCommissionDeliverySno'])['commissionValue'];
            }
            // 수수료 값 종료일정 확인용
            if($scmCommissionValue && $scmDeliveryCommissionValue) {
                $scheduleData['applyCommissionLog'] = implode(INT_DIVISION, [$scmCommissionValue, $scmDeliveryCommissionValue]);
            }

            if(empty($applyArray[$scheduleData['applyKind']]) == true) {
                $scheduleData['applyKind'] = 'all';
            }
            if($scheduleData['applyKind'] != 'all') { // 적용조건 데이터 종류, 데이터 값 삽입
                $applyKey = $applyArray[$scheduleData['applyKind']];
                if(empty($scheduleData[$applyKey]) == false) {
                    $scheduleData['applyData'] = implode(INT_DIVISION, $scheduleData[$applyKey]);
                } else {
                    $scheduleData['applyKind'] = 'all';
                }
                unset($scheduleData[$applyKey]);
            }

            if(gd_isset($scheduleData['applyExceptFl'])) { // 적용예외조건 데이터 종류, 데이터 값 삽입
                foreach($scheduleData['applyExceptFl'] as $exceptIndex => $exceptVal) {
                    $exceptKindKey = $exceptArray[$scheduleData['applyExceptFl'][$exceptIndex]];
                    if($scheduleData[$exceptKindKey]) {
                        $scheduleData[$dbFieldArray[$exceptKindKey]] = implode(INT_DIVISION, $scheduleData[$exceptKindKey]);
                        unset($scheduleData[$exceptKindKey]);
                    }
                }
                unset($scheduleData['detailSearch'], $scheduleData['popupMode'], $scheduleData['mode'], $scheduleData['applyExceptFl']);
            }

            // 기존 정보와 새로운 정보 비교를 위해 호출 - logTable
            $getScmCommissionOriginData = $this->getScmCommissionScheduleDataOnce($scheduleData['scmNo'], $scheduleData['scmScheduleSno']);

            if(empty($scheduleData['scmScheduleSno']) === true) { // Insert
                $mode = 'insert';
                $arrBind = $this->db->get_binding(DBTableField::tableScmCommissionSchedule(), $scheduleData, 'insert');
                $this->db->set_insert_db(DB_SCM_COMMISSION_SCHEDULE, $arrBind['param'], $arrBind['bind'], 'y');
            } else { // update
                $mode = 'update';
                $arrBind = $this->db->get_binding(DBTableField::tableScmCommissionSchedule(), $scheduleData, 'update');
                $this->db->bind_param_push($arrBind['bind'], 'i', $scheduleData['scmScheduleSno']);
                $this->db->set_update_db(DB_SCM_COMMISSION_SCHEDULE, $arrBind['param'], 'sno = ?', $arrBind['bind']);
            }

            $this->setScmLog('schedule', $mode, $scheduleData['scmNo'], $getScmCommissionOriginData, $scheduleData);
            unset($arrBind, $scheduleData, $exceptArray);
            return ['mode'=>'success', 'message'=>self::TEXT_SAVE_COMPLETE];
        }
    }

    /**
     * 공급사 추가 수수료 일정 중지(단일)
     * es_scmCommissionSchedule stop
     *
     * @param array $scheduleData
     * @return array
     * @author tomi
     */
    public function stopScmScheduleCommission($scheduleData)
    {
        $arrBind = [];
        // 기존 정보와 새로운 정보 비교를 위해 호출
        $getScmCommissionOriginData = $this->getScmCommissionScheduleDataOnce($scheduleData['scmNo'], $scheduleData['scmScheduleSno']);
        $nowDate = DateTimeUtils::dateFormat('Y-m-d H:i:s', 'now');
        $nowDateConvert = strtotime($nowDate);
        $modifyAbleFl = false;
        foreach($getScmCommissionOriginData as $key => $val) {
            if(strtotime($val['startDate']) < $nowDateConvert) {
                $modifyAbleFl = true;
            }
            if($modifyAbleFl == true) {
                if(!$scheduleData['scmScheduleSno']) $scheduleData['scmScheduleSno'] = $val['sno'];
            }
        }

        if($modifyAbleFl == true) {
            $this->db->bind_param_push($arrBind['bind'], 's', $nowDate);
            $this->db->bind_param_push($arrBind['bind'], 'i', $scheduleData['scmNo']);
            $this->db->bind_param_push($arrBind['bind'], 'i', $scheduleData['scmScheduleSno']);
            $this->db->set_update_db(DB_SCM_COMMISSION_SCHEDULE, 'endDate = ? ', 'scmNo = ? AND sno = ?', $arrBind['bind']);

            $this->setScmLog('schedule', 'update', $scheduleData['scmNo'], $getScmCommissionOriginData, $scheduleData);
            unset($getScmCommissionOriginData, $scheduleData);
            return ['mode' => 'success', 'message' => self::TEXT_STOP_COMPLETE];
        } else {
            return ['mode' => 'false', 'message' => self::TEXT_STOP_FAIL];
        }
    }

    /**
     * 공급사 추가 수수료 일정 삭제(단일)
     * es_scmCommissionSchedule delete
     *
     * @param array $scheduleData
     * @return array
     * @author tomi
     */
    public function deleteScmScheduleCommission($scheduleData)
    {
        $arrBind = [];
        // 기존 정보와 새로운 정보 비교를 위해 호출
        $getScmCommissionOriginData = $this->getScmCommissionScheduleDataOnce($scheduleData['scmNo'], $scheduleData['scmScheduleSno']);
        $nowDate = DateTimeUtils::dateFormat('Y-m-d H:i:s', 'now');
        $nowDateConvert = strtotime($nowDate);
        $deleteAbleFl = false;
        foreach($getScmCommissionOriginData as $key => $val) {
            if(strtotime($val['startDate']) > $nowDateConvert) {
                $deleteAbleFl = true;
            }
            if($deleteAbleFl == true) {
                if(!$scheduleData['scmScheduleSno']) $scheduleData['scmScheduleSno'] = $val['sno'];
            }
        }

        if($deleteAbleFl == true) {
            $this->db->bind_param_push($arrBind['bind'], 's', 'y');
            $this->db->bind_param_push($arrBind['bind'], 'i', $scheduleData['scmNo']);
            $this->db->bind_param_push($arrBind['bind'], 'i', $scheduleData['scmScheduleSno']);
            $this->db->set_update_db(DB_SCM_COMMISSION_SCHEDULE, 'delFl = ? ', 'scmNo = ? AND sno = ?', $arrBind['bind']);

            $this->setScmLog('schedule', 'delete', $scheduleData['scmNo'], $getScmCommissionOriginData, $scheduleData);
            unset($getScmCommissionOriginData, $scheduleData);
            return ['mode' => 'success', 'message' => self::TEXT_DELETE_COMPLETE];
        } else {
            return ['mode' => 'false', 'message' => self::TEXT_DELETE_FAIL];
        }
    }

    /**
     * 공급사 추가 수수료 일정 삭제(대량) (scmAdmin->deleteScm)
     * es_scmCommissionSchedule deleteBatch
     *
     * @param array $scheduleData
     * @param string $batch multi / once
     * @return null
     * @author tomi
     */
    public function deleteScmScheduleCommissionBatch($scheduleData, $batch ='multi')
    {
        $arrBind = [];
        if($batch == 'once') { // 단일
            $scmNo = $scheduleData;
        } else { // 대량
            $scmNo = $scheduleData['scmNo'];
        }
        // 기존 정보와 새로운 정보 비교를 위해 호출
        $getScmCommissionOriginData = $this->getScmCommissionScheduleDataOnce($scmNo, null);

        $nowDate = DateTimeUtils::dateFormat('Y-m-d H:i:s', 'now');
        $nowDateConvert = strtotime($nowDate);
        if(empty($getScmCommissionOriginData) === false) {
            $setScmCommissionDeleteData = [];
            $getScmCommissionArrDimension = gd_array_dimension($getScmCommissionOriginData); // 배열 차수 계산 ( 1의 경우 1차배열 TO BE 2차배열 )
            if($getScmCommissionArrDimension == 1) {
                $setScmCommissionDeleteData[] = $getScmCommissionOriginData;
            } else {
                $setScmCommissionDeleteData = $getScmCommissionOriginData;
            }
            foreach($setScmCommissionDeleteData as $key => $val) {
                $deleteAbleFl = false;
                $arrBind = [];
                if((strtotime($val['startDate']) > $nowDateConvert) && $val['delFl'] == 'n') {
                    $deleteAbleFl = true;
                }
                if($deleteAbleFl == true) {
                    $this->db->bind_param_push($arrBind['bind'], 's', 'y');
                    $this->db->bind_param_push($arrBind['bind'], 'i', $scmNo);
                    $this->db->bind_param_push($arrBind['bind'], 'i', $val['sno']);
                    $this->db->set_update_db(DB_SCM_COMMISSION_SCHEDULE, 'delFl = ? ', 'scmNo = ? AND sno = ?', $arrBind['bind']);

                    $this->setScmLog('schedule', 'delete', $scheduleData['scmNo'], $val, $scheduleData['scmScheduleSno']);
                }
            }
        }
        unset($getScmCommissionOriginData, $scheduleData);
    }


    /**
     * 공급사 수수료 일정에 등록됐는 지 확인
     *
     * @param array $scmCommissionData
     * @return array $scmCommissionData
     * @author KimYeonKyung
     */
    public function compareWithScmCommissionSchedule($scmCommissionData)
    {
        $nowDate = DateTimeUtils::dateFormat('Y-m-d H:i:s', 'now');
        $requestWhere = " AND (startDate >= '" . $nowDate . "' OR endDate >= '" . $nowDate . "')";
        foreach ($scmCommissionData as $key => &$val) {
            //판매 수수료 검증
            if ($val ['commissionType'] == 'sell') {
                $requestCommissionWhere = $requestWhere . " AND scmCommissionSno = '" . $val['sno'] . "'";
                $scmCommissionScheduleData = $this->getScmCommissionScheduleDataOnce($val['scmNo'], '', $requestCommissionWhere);

                if (gd_isset($scmCommissionScheduleData)) {
                    //일정에 있음
                    $val['scmCommissionInput'] = 'readonly="readonly"';
                    $val['scmCommissionButtonClass'] = 'btn-commission-in-schedule';
                }
            }

            //배송 수수료 검증
            if ($val ['commissionType'] == 'delivery') {
                $requestCommissionDeliveryWhere = $requestWhere . " AND scmCommissionDeliverySno = '" . $val['sno'] . "'";
                $scmCommissionDeliveryScheduleData = $this->getScmCommissionScheduleDataOnce($val['scmNo'], '', $requestCommissionDeliveryWhere);
                if (gd_isset($scmCommissionDeliveryScheduleData)) {
                    //일정에 있음
                    $val['scmCommissionDeliveryInput'] = 'readonly="readonly"';
                    $val['scmCommissionDeliveryButtonClass'] = 'btn-commission-delivery-in-schedule';
                    $val['scmCommissionSameCheckBox'] = 'disabled="disabled"';
                }
            }
        }
        return $scmCommissionData;
    }

    /**
     * 공급사 추가 수수료 저장 - 공급사 수정 페이지에서 사용
     *
     * @param array $addCommissionData
     * @param string $mode : insert, modify
     * @return null
     * @author KimYeonKyung
     * @throws \Exception
     */
    public function saveScmCommission($addCommissionData, $mode)
    {
        $scmNo = $addCommissionData['scmNo'];
        switch ($mode) {
            case 'insert' :
                if (gd_isset($addCommissionData['scmCommissionNew'])) {
                    $this->saveScmCommissionByArray($addCommissionData['scmCommissionNew'], $scmNo, 'sell');
                }
                if ($addCommissionData['scmSameCommission'] == 'Y') {
                    $this->saveScmCommissionByArray($addCommissionData['scmCommissionNew'], $scmNo, 'delivery');
                } else {
                    if (gd_isset($addCommissionData['scmCommissionDeliveryNew'])) {
                        $this->saveScmCommissionByArray($addCommissionData['scmCommissionDeliveryNew'], $scmNo, 'delivery');
                    }
                }
                break;

            case 'update' :
                $scmCommissionData = $this->getScmCommission($addCommissionData['scmNo']);
                //추가 판매 수수료 update & insert
                if (gd_isset($addCommissionData['scmCommissionInDB'])) {
                    $this->updateScmCommissionByArray($addCommissionData['scmCommissionInDB']);
                }

                if (gd_isset($addCommissionData['scmCommissionNew'])) {
                    $this->saveScmCommissionByArray($addCommissionData['scmCommissionNew'], $scmNo, 'sell');
                }

                //삭제된 판매 수수료
                $addCommissionSno = array_keys($addCommissionData['scmCommissionInDB']);
                foreach ($scmCommissionData as $key => $val) {
                    if ($val['commissionType'] == 'sell') {
                        $scmCommissionSno[] = $val['sno'];
                    }
                }
                if (gd_isset($addCommissionSno)) {
                    $deleteScmCommission = array_diff($scmCommissionSno, $addCommissionSno);
                } else {
                    $deleteScmCommission = $scmCommissionSno;
                }
                if (gd_isset($deleteScmCommission)) {
                    foreach ($deleteScmCommission as $key => $sno) {
                        $this->deleteScmCommission($sno);
                    }
                }
                unset($deleteScmCommission);

                // 판매 수수료 동일 적용
                if ($addCommissionData['scmSameCommission'] == 'Y') {
                    $scmCommissionDataInDB = $this->getScmCommission($addCommissionData['scmNo']);
                    foreach ($scmCommissionDataInDB as $key => $val) {
                        if ($val['commissionType'] == 'sell') {
                            $matchData['scmCommission'][] = $val['commissionValue'];
                        } else {
                            $matchData['scmCommissionDelivery'][$val['sno']] = $val['commissionValue'];
                        }
                    }
                    //scmCommission 과 scmCommissionDelivery 크기 비교해서 1. scmCommission 이 큰 경우, 2 둘이 같은 경우 , 3 scmCommissionDelivery 클 경우 나눠서 하기
                    $scmCommissionCnt = count($matchData['scmCommission']);
                    $scmCommissionDeliveryCnt = count($matchData['scmCommissionDelivery']);
                    if ($scmCommissionCnt > $scmCommissionDeliveryCnt) {
                        $i = 0;
                        foreach ($matchData['scmCommissionDelivery'] as $sno => &$val) {
                            $val = $matchData['scmCommission'][$i];
                            $i++;
                        }
                        $insertScmCommissionDelivery = array_slice($matchData['scmCommission'], $scmCommissionDeliveryCnt);
                    } elseif ($scmCommissionCnt == $scmCommissionDeliveryCnt) {
                        $i = 0;
                        foreach ($matchData['scmCommissionDelivery'] as $sno => &$val) {
                            $val = $matchData['scmCommission'][$i];
                            $i++;
                        }
                    } else {
                        $deleteScmCommissionDelivery = array_slice($matchData['scmCommissionDelivery'], $scmCommissionCnt,null,true);
                        $matchData['scmCommissionDelivery'] = array_slice($matchData['scmCommissionDelivery'], 0, $scmCommissionCnt,true);
                        $i = 0;
                        foreach ($matchData['scmCommissionDelivery'] as $sno => &$val) {
                            $val = $matchData['scmCommission'][$i];
                            $i++;
                        }
                    }

                    if (gd_isset($insertScmCommissionDelivery)) {
                        $this->saveScmCommissionByArray($insertScmCommissionDelivery, $scmNo, 'delivery');
                    }
                    if (gd_isset($matchData['scmCommissionDelivery'])) {
                        $this->updateScmCommissionByArray($matchData['scmCommissionDelivery'],$scmNo,true);
                    }
                    if (gd_isset($deleteScmCommissionDelivery)) {
                        foreach ($deleteScmCommissionDelivery as $sno => $val) {
                            //배송비 수수료 일정체크
                            $nowDate = DateTimeUtils::dateFormat('Y-m-d H:i:s', 'now');
                            $requestWhere = " AND (startDate >= '" . $nowDate . "' OR endDate >= '" . $nowDate . "')";
                            $requestDeliveryWhere = $requestWhere." AND scmCommissionDeliverySno = '".$sno."'";
                            $isInSchedule = $this->getScmCommissionScheduleDataOnce($scmNo, null, $requestDeliveryWhere);
                            if (gd_isset($isInSchedule)) {
                                continue;
                            } else {
                                $this->deleteScmCommission($sno);
                            }
                        }
                    }
                } else {
                    //배송비 수수료 update & insert
                    if (gd_isset($addCommissionData['scmCommissionDeliveryInDB'])) {
                        $this->updateScmCommissionByArray($addCommissionData['scmCommissionDeliveryInDB']);
                    }

                    if (gd_isset($addCommissionData['scmCommissionDeliveryNew'])) {
                        $this->saveScmCommissionByArray($addCommissionData['scmCommissionDeliveryNew'], $scmNo, 'delivery');
                    }

                    //삭제된 배송비 수수료
                    $addCommissionDeliverySno = array_keys($addCommissionData['scmCommissionDeliveryInDB']);
                    foreach ($scmCommissionData as $key => $val) {
                        if ($val['commissionType'] == 'delivery') {
                            $scmCommissionDeliverySno[] = $val['sno'];
                        }
                    }
                    if (gd_isset($addCommissionDeliverySno)) {
                        $deleteScmCommission = array_diff($scmCommissionDeliverySno, $addCommissionDeliverySno);
                    } else {
                        $deleteScmCommission = $scmCommissionDeliverySno;
                    }

                    if (gd_isset($deleteScmCommission)) {
                        foreach ($deleteScmCommission as $key => $sno) {
                            $this->deleteScmCommission($sno);
                        }
                    }
                }
                break;
        }
    }

    /**
     * 공급사 추가 수수료 저장
     *
     * @param $insertScmArr
     * @param $scmNo
     * @param $commissionType
     * @throws \Exception
     * @author KimYeonKyung
     */
    public function saveScmCommissionByArray($insertScmArr, $scmNo, $commissionType)
    {
        $validator = new Validator();
        foreach ($insertScmArr as $key => $val) {
            $insertArr['scmNo'] = $scmNo;
            $insertArr['commissionType'] = $commissionType;
            $insertArr['commissionValue'] = $val;

            $validator->add('scmNo', 'number', true); // 공급사 고유번호
            $validator->add('commissionType', '', true); // 공급사 고유번호
            $validator->add('commissionValue', '', true); // 판매수수료-%로 소수점 2자리

            if ($validator->act($insertArr, true) === false) {
                throw new \Exception(implode("<br/>", $validator->errors));
            }
            $this->saveScmCommissionDB($insertArr);
            unset($insertArr);
        }
    }
    /**
     * 공급사 추가 수수료 DB 저장
     *
     * @param $insertArr
     * @author KimYeonKyung
     */
    public function saveScmCommissionDB($insertArr)
    {
        $this->db->begin_tran();
        $arrBind = $this->db->get_binding(DBTableField::tableScmCommission(), $insertArr, 'insert', array_keys($insertArr));
        $this->db->set_insert_db(DB_SCM_COMMISSION, $arrBind['param'], $arrBind['bind'], 'y',false);
    }

    /**
     * 공급사 추가 수수료 업데이트
     *
     * @param $updateScmArr
     * @param $scmNo
     * @param $isScheduleCheck : 일정에 있는 지 체크
     * @throws \Exception
     * @author KimYeonKyung
     */
    public function updateScmCommissionByArray($updateScmArr, $scmNo = null, $isScheduleCheck = null)
    {
        $validator = new Validator();
        foreach ($updateScmArr as $sno => $val) {
            if ($isScheduleCheck) {
                $nowDate = DateTimeUtils::dateFormat('Y-m-d H:i:s', 'now');
                $requestWhere = " AND (startDate >= '" . $nowDate . "' OR endDate >= '" . $nowDate . "')";
                $requestDeliveryWhere = $requestWhere." AND scmCommissionDeliverySno = '".$sno."'";
                $isInSchedule = $this->getScmCommissionScheduleDataOnce($scmNo, null, $requestDeliveryWhere);
                if (gd_isset($isInSchedule)) continue;
            }
            $updateArr['sno'] = $sno;
            $updateArr['commissionValue'] = $val;

            $validator->add('sno', 'number', true); // 공급사 고유번호
            $validator->add('commissionValue', '', true); // 판매수수료-%로 소수점 2자리

            if ($validator->act($updateArr, true) === false) {
                throw new \Exception(implode("<br/>", $validator->errors));
            }
            $this->updateScmCommissionDB($updateArr);
            unset($updateArr);
        }
    }

    /**
     * 공급사 추가 수수료 DB 업데이트
     *
     * @param $updateArr
     * @author KimYeonKyung
     */
    public function updateScmCommissionDB($updateArr)
    {
        $this->db->begin_tran();
        $arrBind = $this->db->get_binding(DBTableField::tableScmCommission(), $updateArr, 'update', array_keys($updateArr));
        $this->db->bind_param_push($arrBind['bind'], 'i', $updateArr['sno']);
        $this->db->set_update_db(DB_SCM_COMMISSION, $arrBind['param'], 'sno = ?', $arrBind['bind'], false);
    }

    /**
     * 공급사 추가 수수료 삭제
     *
     * @param $sno
     * @author KimYeonKyung
     */
    public function deleteScmCommission($sno)
    {
        $arrBind = [];
        $this->db->bind_param_push($arrBind['bind'], 's', 'y');
        $this->db->bind_param_push($arrBind['bind'], 'i', $sno);
        $this->db->set_update_db(DB_SCM_COMMISSION, 'delFl = ? ', 'sno = ?', $arrBind['bind']);

        unset($arrBind);
    }

    /**
     * 공급사 판매 수수료, 배송 수수료 동일 비교
     *
     * @param $scmCommissionData, getScmCommission 기준
     * @author KimYeonKyung
     * @return bool $result - true : 일치 , false : 불일치
     */
    public function compareWithScmCommission($scmCommissionData)
    {
        $result = true;
        $dataCnt = count($scmCommissionData);
        if ($dataCnt % 2 == 1) {
            //홀수 일 경우 처음부터 불일치
            $result = false;
        } else {
            if (gd_isset($scmCommissionData)) {
                foreach ($scmCommissionData as $key => $val) {
                    if ($val['commissionType'] == 'sell') {
                        $compareData['scmCommission'][] = $val['commissionValue'];
                    } else {
                        $compareData['scmCommissionDelivery'][] = $val['commissionValue'];
                    }
                }

                foreach ($compareData['scmCommission'] as $key => $val) {
                    if ($val != $compareData['scmCommissionDelivery'][$key]) {
                        $result = false;
                        break;
                    }
                }
            }
        }
        return $result;
    }

    /**
     * 수수료 범위 체크
     *
     * @param array $commissionData
     * @author KimYeonKyung
     * @throws \Exception
     */
    public function checkScmCommissionValue($commissionData)
    {
        foreach ($commissionData as $key => $val) {
            if ($val > 100 || $val < 0) {
                throw new \Exception(__('수수료는') . ' 0 ~ 100 % ' . __('입니다.'));
            }
        }
    }

    /**
     * 일정등록 체크
     *
     * @param array $commissionData
     * @param string $nowDate ('Y-m-d h:i:s')
     * @author tomi
     * @return array $checkData
     */
    public function checkScmCommissionSchedule($commissionData, $nowDate)
    {
        $checkData = [];
        $nowDateConvert = strtotime($nowDate);
        $dataArrayDepth = ArrayUtils::dimension($commissionData);
        if($dataArrayDepth == 1) {
            if(strtotime($commissionData['startDate']) <= $nowDateConvert && strtotime($commissionData['endDate']) >= $nowDateConvert) {
                array_push($checkData, $commissionData);
            }
        } else if($dataArrayDepth == 2) {
            foreach ($commissionData as $key => $val) {
                if(strtotime($val['startDate']) <= $nowDateConvert && strtotime($val['endDate']) >= $nowDateConvert) {
                    array_push($checkData, $val);
                    continue;
                }
            }
        }
        return $checkData[0];
    }

    /**
     * 공급사 판매 수수료, 배송 수수료 현재 등록된 수수료 스케쥴에 따라 변경(order->saveOrder();)
     * order.php 사용
     *
     * @param int $scmNo
     * @param array $goodsData
     * @author tomi
     * @return array $convertData
     */
    public function frontConvertScmCommission($scmNo, $goodsData)
    {
        $returnData = [];
        $actionFl = false;
        if ($scmNo) {
            // 기존 등록된 일정에 포함되는 값이 있는지 확인
            $nowDate = DateTimeUtils::dateFormat('Y-m-d H:i:s', 'now');
            $dbData = $this->getScmCommissionScheduleDataOnce($scmNo, null, null);
            $checkData = $this->checkScmCommissionSchedule($dbData, $nowDate); // 해당 scm 스케쥴 기간 체크 및 해당되는 데이터 리턴
            if(empty($checkData) === false && $goodsData) {
                if($goodsData['goodsType'] != 'addGoods') { // 추가 상품이 아닐 경우
                    $actionFl = $this->frontConvertActionPoint($checkData, $goodsData);
                } else {
                    $actionFl = false; // 추가상품인 경우 false
                }
                if($actionFl == true) {
                    $scmCommissionData = $scmCommissionDeliveryData = 0;
                    $scmData = [];
                    // 판매수수료
                    if($checkData['scmCommissionSno'] == 0 || $checkData['scmCommissionDeliverySno'] == 0) {
                        $arrBind = [];
                        $this->db->bind_param_push($arrBind, 'i', $scmNo);
                        $strSQL = 'SELECT companyNm, scmCommission, scmCommissionDelivery FROM ' . DB_SCM_MANAGE . ' WHERE scmNo = ?';
                        $scmData = $this->db->query_fetch($strSQL, $arrBind, false);
                    }
                    if($checkData['scmCommissionSno'] > 0) {
                        $scmCommissionData = $this->getScmCommissionSno($checkData['scmCommissionSno'])['commissionValue'];
                        if($scmCommissionData > 0) {
                            $returnData['scmCommission'] = $scmCommissionData;
                        }
                    } else if($checkData['scmCommissionSno'] == 0) {
                        $returnData['scmCommission'] = $scmCommissionData = $scmData['scmCommission'];
                    }
                    // 배송비 수수료
                    if($checkData['scmCommissionDeliverySno'] > 0) {
                        $scmCommissionDeliveryData = $this->getScmCommissionSno($checkData['scmCommissionDeliverySno'])['commissionValue'];
                        if($scmCommissionDeliveryData > 0) {
                            $returnData['scmCommissionDelivery'] = $scmCommissionDeliveryData;
                        }
                        else {
                            if($scmCommissionData == $scmCommissionDeliveryData) {
                                $returnData['scmCommissionDelivery'] = $scmCommissionData;
                            }
                        }
                    } else {
                        $returnData['scmCommissionDelivery'] = $scmData['scmCommissionDelivery'];
                    }
                    unset($convertData, $scmCommissionData, $scmCommissionDeliveryData, $scmData);
                    return $returnData;
                }
            }
        }
    }

    /**
     * 공급사 판매 수수료, 배송 수수료 현재 등록된 수수료 스케쥴에 따라 변경
     * order.php 사용
     *
     * @param array $convertData
     * @param array $goodsData
     * @author tomi
     * @return boolean $actionFl
     */
    public function frontConvertActionPoint($convertData, $goodsData)
    {
        $actionFl = false;
        // 적용 필드
        if($convertData['applyKind'] == 'all') {
            $actionFl = true;
        } else {
            $splitData = explode(INT_DIVISION, $convertData['applyData']);
            if($convertData['applyKind'] == 'goods') {
                if(in_array($goodsData['goodsNo'], $splitData) == true) {
                    $actionFl = true;
                }
            }
            else if($convertData['applyKind'] == 'brand') {
                if(in_array($goodsData['brandCd'], $splitData) == true) {
                    $actionFl = true;
                }
            }
            else if($convertData['applyKind'] == 'coupon') {
                $coupon = \App::load('\\Component\\Coupon\\Coupon');
                foreach($goodsData['coupon'] as $couponSno => $couponVal) {
                    if ($couponSno) {
                        $couponInfo = $coupon->getMemberCouponInfo($couponSno, 'c.couponNo')['couponNo'];
                        if (in_array($couponInfo, $splitData) == true) {
                            $actionFl = true;
                            continue;
                        }
                    }
                }
            }
            else if($convertData['applyKind'] == 'member') {
                // 회원 콤포넌트 호출
                if($goodsData['memNo'] > 0) {
                    $member = \App::load('\\Component\\Member\\Member');
                    $memberInfo = $member->getMemberInfo($goodsData['memNo']);
                    if(in_array($memberInfo['groupSno'], $splitData) == true) {
                        $actionFl = true;
                    }
                } else {
                    $actionFl = false;
                }
            }
        }
        // 상품 제외
        foreach($this->scmDbFieldArray as $exceptKey => $exceptVal) {
            if($convertData[$exceptVal]) {
                $splitData = explode(INT_DIVISION, $convertData[$exceptVal]);
                if($exceptKey == 'goods') {
                    if(in_array($goodsData['goodsNo'], $splitData) == true) {
                        $actionFl = false;
                    }
                }
                else if($exceptKey == 'brand') {
                    if(in_array($goodsData['brandCd'], $splitData) == true) {
                        $actionFl = false;
                    }
                }
                else if($exceptKey == 'coupon') {
                    $coupon = \App::load('\\Component\\Coupon\\Coupon');
                    foreach($goodsData['coupon'] as $couponSno => $couponVal) {
                        if ($couponSno) {
                            $couponInfo = $coupon->getMemberCouponInfo($couponSno, 'c.couponNo')['couponNo'];
                            if (in_array($couponInfo, $splitData) == true) {
                                $actionFl = false;
                                continue;
                            }
                        }
                    }
                }
                else if($exceptKey == 'member') {
                    // 회원 콤포넌트 호출
                    if($goodsData['memNo'] > 0) {
                        $member = \App::load('\\Component\\Member\\Member');
                        $memberInfo = $member->getMemberInfo($goodsData['memNo']);
                        if(in_array($memberInfo['groupSno'], $splitData) == true) {
                            $actionFl = false;
                        }
                    }
                }
            }
        }
        return $actionFl;
    }

    /**
     * 공급사 로그 저장
     *
     * @param $logPage : 로그 저장 페이지
     * @param $mode : insert, modify, delete
     * @param $scmNo : 공급사 고유번호
     * @param $prevData : 수정 및 저장 전 데이터
     * @param $updateData : 수정 및 저장 후 데이터
     * @author KimYeonKyung
     * @return null
     */
    public function setScmLog($logPage, $mode, $scmNo, $prevData, $updateData) {
        $insertLogData = [];
        $insertLogData['logPage'] = $logPage;
        $insertLogData['logMode'] = $mode;
        $insertLogData['scmNo'] = $scmNo;
        $insertLogData['prevData'] = json_encode($prevData, JSON_UNESCAPED_UNICODE);
        $insertLogData['updateData'] = json_encode($updateData, JSON_UNESCAPED_UNICODE);
        $insertLogData['managerId'] = (string)Session::get('manager.managerId');
        $insertLogData['managerNo'] = Session::get('manager.sno');

        $arrBind = $this->db->get_binding(DBTableField::tableLogScmCommission(), $insertLogData, 'insert');
        $this->db->set_insert_db(DB_LOG_SCM_COMMISSION, $arrBind['param'], $arrBind['bind'], 'y');

        unset($insertLogData);
    }

    /**
     * 공급사 로그 데이터 만들기 (ScmManage , ScmCommission)
     * @param $scmNo
     * @author KimYeonKyung
     * @return array $result
     */
    public function getScmLogData($scmNo)
    {
        $arrBind = [];
        $arrWhere[] = 'm.scmNo = ?';
        $this->db->bind_param_push($arrBind, 'i', $scmNo);

        $this->db->strField = 'm.*, c.commissionType, c.commissionValue, c.delFl';
        $this->db->strWhere = implode(' AND ', $arrWhere);
        $this->db->strJoin = ' LEFT JOIN ' . DB_SCM_COMMISSION . ' AS c ON m.scmNo = c.scmNo';

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . 'FROM ' . DB_SCM_MANAGE . ' AS m ' . implode(' ', $query);
        $result = $this->db->query_fetch($strSQL, $arrBind, false);
        $resultCnt = count($result);

        if ($resultCnt > 1) {
            //추가 수수료 첫번째 배열에 정리
            for ($i = 0; $i < $resultCnt; $i++) {
                if ($result[$i]['commissionType'] == 'sell') {
                    $result[0]['addScmCommission'] .= $result[$i]['commissionValue'] . ', ';
                } else {
                    $result[0]['addScmCommissionDelivery'] .= $result[$i]['commissionValue'].', ' ;
                }

                if ($i > 0) {
                    unset($result[$i]);
                } else {
                    unset($result[0]['commissionType']);
                    unset($result[0]['commissionValue']);
                }
            }
        } else {
            //결과가 하나일 경우
            if ($result[0]['commissionType'] == 'sell') {
                $result[0]['addScmCommission'] .= $result[0]['commissionValue'];
            } else {
                $result[0]['addScmCommissionDelivery'] .= $result[0]['commissionValue'];
            }
            unset($result[0]['commissionType']);
            unset($result[0]['commissionValue']);
        }

        return $result;
    }

    /**
     * 공급사 수수료 달력 데이터 (scm_commission_list)
     * @param array $searchData
     * @author tomi
     * @return array $getValue
     */
    public function scmCommissionScheduleCalendar($searchData)
    {
        $calendarData = [];
        $getValue['search'] = $searchData;

        $calendarData['startYear'] = DateTimeUtils::dateFormat('Y', 'now');;
        $calendarData['endYear'] = DateTimeUtils::dateFormat('Y', 'now') + 5;

        // get전달값
        $calendarData['setYear'] = ($getValue['search']['toYear']) ? $getValue['search']['toYear'] : DateTimeUtils::dateFormat('Y', 'now');
        $calendarData['setMonth'] = ($getValue['search']['toMonth']) ? $getValue['search']['toMonth'] : DateTimeUtils::dateFormat('m', 'now');
        $calendarData['setDay'] = array( "일", "월", "화", "수", "목", "금", "토" );

        // 날짜 관련 set
        $calendarData['setMktime'] = mktime( 0, 0, 0, $calendarData['setMonth'], 1, $calendarData['setYear'] ); // 입력된 값으로 년-월-01 set
        $calendarData['days'] = date( "t", $calendarData['setMktime'] );  // 현재의 year와 month로 현재 달의 일수 set
        $calendarData['startDay'] = date( "w", $calendarData['setMktime'] );  // 시작요일 set

        // 현재날짜 체크를 위한 배열
        $calendarData['checkToday']['year'] = DateTimeUtils::dateFormat('Y', 'now');
        $calendarData['checkToday']['month'] = DateTimeUtils::dateFormat('m', 'now');
        $calendarData['checkToday']['day'] = DateTimeUtils::dateFormat('d', 'now');

        $calendarData['setRows'] = ceil(( $calendarData['startDay'] + $calendarData['days']) / 7 ); // 전체 카운팅

        $calendarData['prevDayCount'] = date( "t", mktime( 0, 0, 0, $calendarData['setMonth'], 0, $calendarData['setYear'] ) ) - ($calendarData['startDay'] - 1); // 지난 달 시작카운트
        $calendarData['prevDayStartDate'] = $calendarData['prevDayCount']; // 지난달 시작 일자
        $calendarData['nowDayCount'] = 1;    // 이번달 일자 시작 카운팅
        $calendarData['nextDayCount'] = 1;   // 다음달 일자 시작 카운팅
        $calendarData['nextDayEndDate'] = (($calendarData['setRows'] * 7) - ($calendarData['startDay'] + $calendarData['days'])); // 다음달 종료일자
        $calendarData['prevYear'] = ($calendarData['setMonth'] == 1) ? ($calendarData['setYear'] - 1) : $calendarData['setYear']; // 이전 년도
        $calendarData['prevMonth'] = ($calendarData['setMonth'] == 1) ? 12 : ($calendarData['setMonth'] - 1); // 다음 달
        $calendarData['nextYear'] = ($calendarData['setMonth'] == 12) ? ($calendarData['setYear'] + 1) : $calendarData['setYear']; // 다음 년도
        $calendarData['nextMonth'] = ($calendarData['setMonth'] == 12) ? 1 : ($calendarData['setMonth'] + 1 ); // 다음 달

        // 2자리 생성
        $calendarData['prevMonth'] = sprintf("%02d", $calendarData['prevMonth']);
        $calendarData['nextMonth'] = sprintf("%02d", $calendarData['nextMonth']);

        // 캘린더 및 스케쥴 데이터 날짜 배열 생성을 위한 변경
        for($nowI = 1; $nowI < $calendarData['days'] + 1; $nowI++) {
            $nowI = sprintf("%02d", $nowI);
            $calendarData['data'][$calendarData['setYear'] . '-' . $calendarData['setMonth'] . '-' . $nowI] = [];
        }
        if($getValue['search']['mode'] == 'calendar') {
            for($prevI = $calendarData['prevDayStartDate']; $prevI < ($calendarData['prevDayStartDate']); $prevI++) {
                $prevI = sprintf("%02d", $prevI);
                $calendarData['data'][$calendarData['prevYear'] . '-' . $calendarData['prevMonth'] . '-' . $prevI] = [];
            }
            for($nextI = 1; $nextI < $calendarData['nextDayEndDate'] + 1; $nextI++) {
                $nextI = sprintf("%02d", $nextI);
                $calendarData['data'][$calendarData['nextYear'] . '-' . $calendarData['nextMonth'] . '-' . $nextI] = [];
            }
        }
        // DB 추출을 위한 startDate, endDate setting ( sql Where Data)
        $calendarData['startSetYear'] = $calendarData['endSetYear'] = $calendarData['setYear'];
        $calendarData['startSetYear'] = ($calendarData['setMonth'] == 1) ? ($calendarData['startSetYear'] - 1) : $calendarData['startSetYear']; // 이전 년도
        $calendarData['endSetYear'] = ($calendarData['setMonth'] == 12) ? ($calendarData['endSetYear'] + 1) : $calendarData['endSetYear']; // 다음 년도

        if($calendarData['startDay'] > 0) {
            $getValue['search']['startDate'] = $calendarData['startSetYear'] . '-' . sprintf("%02d", $calendarData['prevMonth']) . '-' . sprintf("%02d", $calendarData['prevDayStartDate']);
        } else {
            $getValue['search']['startDate'] = $calendarData['setYear'] . '-' . sprintf("%02d", $calendarData['setMonth']) . '-01';
        }
        $getValue['search']['endDate'] = $calendarData['endSetYear'] . '-' . sprintf("%02d", $calendarData['nextMonth']) . '-' . sprintf("%02d", $calendarData['nextDayEndDate']);
        $getValue['calendar'] = $calendarData;

        $getValue = $this->getScmCommissionScheduleAdminList($getValue);
        return $getValue;
    }

    /**
     * 공급사 수수료 캘린더 검색 Date<>DB Date 포함 여부 체크 (scm_commission_list)
     * @param array $startDate DB startDate
     * @param array $endDate DB endDate
     * @param array $searchDate search Form date DATA
     * @author tomi
     * @return boolean 포함여부
     */
    public function scmCalendarDateRangeCheck($startDate,$endDate,$searchDate)
    {
        $iDateFrom = mktime(1,0,0,substr($startDate,5,2), substr($startDate,8,2),substr($startDate,0,4));
        $iDateTo = mktime(1,0,0,substr($endDate,5,2), substr($endDate,8,2),substr($endDate,0,4));

        if ($iDateTo>=$iDateFrom) {
            $compareDate = date('Y-m-d',$iDateFrom);
            if(array_key_exists($compareDate, $searchDate)) {
                return true;
            }
            while ($iDateFrom < $iDateTo)
            {
                $iDateFrom+=86400; // add 24 hours
                $compareDate = date('Y-m-d',$iDateFrom);
                if(array_key_exists($compareDate, $searchDate)) {
                    return true;
                }
            }
            unset($searchDate);
            return false;
        }
        return false;
    }
}
