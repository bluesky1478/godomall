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

namespace Bundle\Component\Order;

use Component\Database\DBTableField;
use Component\Validator\Validator;
use Exception;
use Framework\Utility\ArrayUtils;
use Framework\Utility\HttpUtils;
use Encryptor;
use App;
use Framework\Utility\StringUtils;
use Globals;
use Request;
use Session;
use Framework\Utility\NumberUtils;
use Framework\Security\XXTEA;


/**
 * 세금계산서(고도빌 포함) 공통 class
 *
 * 세금계산서(고도빌 포함) 처리 공통 Class
 * @author    artherot
 * @version   1.0
 * @since     1.0
 * @copyright ⓒ 2016, NHN godo: Corp.
 */
class Tax
{
    const ECT_INVALID_ARG = 'PG.ECT_INVALID_ARG';
    const TEXT_REQUIRE_VALUE = '%s은(는) 필수 항목 입니다.';
    const TEXT_INVALID_VALUE = '%s이 잘못되었습니다.';

    /**
     * @var null|object Framework\Database\DB
     */
    protected $db;

    /**
     * @var array 리스트 검색관련
     */
    private $_arrBind = [];

    /**
     * @var array 리스트 검색관련
     */
    private $_arrWhere = [];
    private $_arrSubWhere = [];
    private $_addWhere = [];

    /**
     * @var array 리스트 검색관련
     */
    private $_checked = [];

    /**
     * @var array 리스트 검색관련
     */
    private $_search = [];

    /**
     * @var array 세금계산서 설정 값
     */
    private $taxConf;

    /**
     * @var string 고도빌 서버 URL
     */
    protected $godobillServer = 'www.godobill.com';

    /**
     * @var string 고도빌 암호화 기본키
     */
    protected $godobillEncKey = 'dusqhdtkdtmd';

    /**
     * @var array 발행 요청 제외 코드
     */
    public $statusRequrstExclude = [];

    /**
     * 생성자
     */
    public function __construct()
    {
        if (!is_object($this->db)) {
            $this->db = App::load('DB');
        }

        // 세금계산서 설정
        if (!is_array($this->taxConf)) {
            $this->taxConf = gd_policy('order.taxInvoice');
        }

        // --- 발행요청리스트에서 제외
        $this->statusListExclude = [
            'c1',
            'c2',
            'c3',
            'c4',
            'f1',
            'f2',
            'f3',
            'o1'
        ];
    }


    /**
     * 세금계산서 요청 저장
     *
     * @param array $arrData 저장할 정보의 배열
     */
    public function saveTaxInvoice($arrData)
    {
        $memberOrder = $arrData['memberOrder']; // 관리자, 고객 동일브라우저에서 로그인 후 고객이 신청할 때 관리자 세션이 있어 고객신청 여부
        $taxInfo = gd_policy('order.taxInvoice');

        if($arrData['taxMode'] =='modify' && $arrData['statusFl'] =='y') {

            $taxInvoicieInfo = $this->setTaxInvoicePrice($arrData['orderNo'],$arrData['taxPolicy']);

            foreach($taxInvoicieInfo as $k1 => $v1) {
                $setData = [];
                $setData = $arrData;
                $setData['taxMode'] = "modify";
                $setData['taxFreeFl'] = $v1['tax']; //일반계산서인지 세금계산서인지 구분
                $setData['settlePrice'] =  $v1['price'] + $v1['vat'];
                $setData['supplyPrice'] =  $v1['price'];
                $setData['taxPrice'] =  $v1['vat'];
                $setData['statusFl'] = "y";
                $setData['godobillSend'] = $arrData['godobillSend'];

                $result = $this->issueTaxInvoice($setData);
            }

        } else {

            $arrData['statusFl'] = 'r';

            // Validation
            $validator = new Validator();
            if ($arrData['taxMode'] == 'modify') {
                $validator->add('sno', 'required', true);                            // sno
            }
            $validator->add('taxMode', 'required', true);
            $validator->add('orderNo', 'required', true, '{' . __('주문번호') . '}');            // 주문번호
            $validator->add('requestNm', 'required', true, '{' . __('신청자명') . '}');            // 신청자명
            $validator->add('requestGoodsNm', 'required', true, '{' . __('상품명') . '}');        // 상품명
            $validator->add('taxBusiNo', 'required', true, '{' . __('사업자번호') . '}');        // 사업자번호
            $validator->add('taxCompany', 'memberNameGlobal', true, '{' . __('회사명') . '}');            // 회사명
            $validator->add('taxCeoNm', 'required', true, '{' . __('대표자명') . '}');            // 대표자명
            $validator->add('taxService', 'required', true, '{' . __('업태') . '}');                // 업태
            $validator->add('taxItem', 'required', true, '{' . __('종목') . '}');                // 종목소
            if(Session::has('manager.managerId')) $validator->add('taxEmail', 'required', true, '{' . __('발행 이메일') . '}');                // 발행 이메일
            else $validator->add('taxEmail', '', false, '{' . __('발행 이메일') . '}');                // 발행 이메일
            $validator->add('taxZipcode', '', false, '{' . __('우편번호') . '}');            // 사업장 우편번호
            $validator->add('taxZonecode', '', true, '{' . __('우편번호') . '}');            // 사업장 우편번호
            $validator->add('taxAddress', 'required', true, '{' . __('사업장 주소') . '}');        // 사업장 주소
            $validator->add('taxAddressSub', '', false, '{' . __('사업장 주소') . '}');    // 사업장 주소
            $validator->add('statusFl', 'required', false);                        // 발행여부
            $validator->add('processDt', '', false);                                // 처리일자
            $validator->add('cancelDt', '', false);                                // 취소일자
            $validator->add('issueDt', '', false);                                // 취소일자
            $validator->add('adminMemo', '', false);                                // 관리자 메모

            // Validation 결과
            if ($validator->act($arrData, true) === false) {
                throw new Exception(implode("\n", $validator->errors));
            }

            if ($arrData['taxEmail'] !='' && Validator::email($arrData['taxEmail'], true) === false) {
                throw new Exception("[세금계산서] 발행 이메일을 정확하게 입력하여 주세요.");
            }

            // 기본값 설정

            $order = \App::load('\\Component\\Order\\OrderAdmin');
            $orderInfo = $order->getOrderData($arrData['orderNo']);

            if($arrData['taxMode'] =='modify') {
                // 로그 설정
                $taxLog = '====================================================' . chr(10);
                $taxLog .= '세금계산서 수정 (' . date('Y-m-d H:i:s') . ')' . chr(10);
                $taxLog .= '====================================================' . chr(10);
                $taxLog .= sprintf('%s : %s (' . Session::get('manager.managerId')  . ') %s', __('요청정보'), __('주문 후 관리자'), __('요청')) . chr(10);
            } else {
                $member = \App::load(\Component\Member\Member::class);
                $memInfo = $member->getMemberId($orderInfo['memNo']);
                $arrData['applicantNm'] =
                $arrData['requestNm'] = $orderInfo['orderName'];
                $arrData['applicantId'] =
                $arrData['requestId'] = $memInfo ? $memInfo['memId'] : '비회원';

                // 로그 설정
                $taxLog = '====================================================' . chr(10);
                $taxLog .= '세금계산서 신청 (' . date('Y-m-d H:i:s') . ')' . chr(10);
                $taxLog .= '====================================================' . chr(10);
                $taxLog .= '처리상태 : 발행 신청' . chr(10);
                if (Session::has('manager.managerId') && $memberOrder != true) {
                    $arrData['requestNm'] = Session::get('manager.managerNm');
                    $arrData['requestId'] = Session::get('manager.managerId');
                    $taxLog .= sprintf('%s : %s (' . $arrData['requestId']  . ') %s', __('요청정보'), __('주문 후 관리자'), __('요청')) . chr(10);
                } else {
                    $taxLog .= sprintf('%s : %s (' . $arrData['requestId'] . ') %s', __('요청정보'), __('주문 후 고객'), __('요청')) . chr(10);
                }
            }

            if (Session::has('manager.managerId')) {
                $arrData['issueMode'] = 'a';
                $arrData['managerId'] = Session::get('manager.managerId');
                $arrData['managerNo'] = Session::get('manager.sno');
            } else {
                $arrData['issueMode'] = 'u';
            }

            $arrData['requestGoodsNm'] = StringUtils::removeTag($orderInfo['orderGoodsNm']);
            $arrData['requestIP'] = Request::getRemoteAddress();
            $arrData['taxStepFl'] = $taxInfo['taxStepFl'];
            $arrData['taxPolicy'] = $taxInfo['taxDeliveryFl'].STR_DIVISION.$taxInfo['TaxMileageFl'].STR_DIVISION.$taxInfo['taxDepositFl']; //정책저장

            if($arrData['taxMode'] =='modify') {

                $check = array("taxBusiNo" => "사업자번호", "taxCompany" => "회사명", "taxCeoNm" => "대표자명", "taxService" => "업태",
                    "taxItem" => "종목", "taxEmail" => "발행이메일", "taxZipcode"=> "사업장우편번호", "taxAddress" => "사업장주소",
                    "taxAddressSub" => "사업장상세주소", "issueDt" => "발행일", "adminMemo" => "관리자메모");
                $orderData = $this->getOrderTaxInvoice($arrData['orderNo']);

                foreach($check as $key => $val){
                    if($arrData[$key] != $orderData[$key]) $modifyLog[] = $val;
                }

                if( empty ($modifyLog) == false ) {
                    $taxLog .= "수정항목 : ". implode($modifyLog, ", ").chr(10);
                }
                unset($modifyLog);

                // 업데이트할 테이블 설정
                $_arrBind = $this->db->get_binding(DBTableField::tableOrderTax(), $arrData, 'update', array_keys($arrData));

                // 로그 저장
                $_arrBind['param'][] = 'taxLog = concat(ifnull(taxLog, \'\'),?)';
                $this->db->bind_param_push($_arrBind['bind'], 's', $taxLog);

                $this->db->bind_param_push($_arrBind['bind'], 'i', $arrData['sno']);
                $this->db->set_update_db(DB_ORDER_TAX, $_arrBind['param'], 'sno = ?', $_arrBind['bind']);

            } else {
                // 로그 저장
                $arrData['taxLog'] = $taxLog;
                $arrData['taxDeliveryCompleteFl'] = $taxInfo['taxDeliveryCompleteFl'];

                $_arrBind = $this->db->get_binding(DBTableField::tableOrderTax(), $arrData, 'insert');
                $this->db->set_insert_db(DB_ORDER_TAX, $_arrBind['param'], $_arrBind['bind'], 'y');

                // 주문 정보 수정
                $order = \App::load('\\Component\\Order\\OrderAdmin');
                $order->setOrderReceiptRequest($arrData['orderNo'], 't');
            }

            unset($_arrBind);

            return [
                true,
                '',
            ];
        }

    }

    /**
     * 세금계산서 프린트 여부 업데이트
     *
     * @param array $arrData
     */
    public function taxInvoicePrint($arrData)
    {
        //주문테이블 업데이트
        $_arrBind = [];
        $arrUpdate[] = "printFl = 'y' ,printDt = now()";
        $this->db->bind_param_push($_arrBind, 's', $arrData['orderNo']);
        $this->db->set_update_db(DB_ORDER_TAX_ISSUE, $arrUpdate, 'orderNo = ?', $_arrBind);

        // 로그 저장
        $taxLog = '====================================================' . chr(10);
        $taxLog .= '세금계산서 출력 (' . date('Y-m-d H:i:s') . ')' . chr(10);
        $taxLog .= '====================================================' . chr(10);
        $taxLog .= '처리상태 : 발행 완료(인쇄후)' . chr(10);

        $_arrBind['param'][] = 'taxLog = concat(ifnull(taxLog, \'\'),?)';
        $this->db->bind_param_push($_arrBind['bind'], 's', $taxLog);
        $this->db->bind_param_push($_arrBind['bind'], 's', $arrData['orderNo']);
        $this->db->set_update_db(DB_ORDER_TAX, $_arrBind['param'], 'orderNo = ?', $_arrBind['bind']);
    }


    /**
     * 세금계산서 발행
     *
     * @param array $arrData 저장할 정보의 배열
     */
    public function issueTaxInvoice($arrData)
    {
        // 고도빌 전송 체크
        $godobillSend = false;

        // 로그 저장 여부
        $saveTaxLogFl = true; // 고도빌의 경우 금액이 없으면 저장하지 않음

        if ($arrData['statusFl'] == 'y' && gd_isset($arrData['godobillSend']) == 'y') {
            $godobillSend = true;
        }


        // 데이터 체크
        if (isset($arrData['settlePrice']) === false || isset($arrData['supplyPrice']) === false || isset($arrData['taxPrice']) === false) {
            throw new Except(self::ECT_INVALID_ARG, sprintf(self::TEXT_REQUIRE_VALUE, __('신청 금액')));
        }
        if ($arrData['settlePrice'] != ($arrData['supplyPrice'] + $arrData['taxPrice'])) {
            throw new Except(self::ECT_INVALID_ARG, sprintf(self::TEXT_INVALID_VALUE, __('신청 금액')));
        }

        // 데이터 조합
        if (isset($arrData['taxBusiNo']) && is_array($arrData['taxBusiNo']) === true) {
            $arrData['taxBusiNo'] = (implode('', $arrData['taxBusiNo']) == '' ? '' : implode('-', $arrData['taxBusiNo']));
        }
        // Validation
        $validator = new Validator();
        $validator->add('sno', 'required', true);                            // sno

        $validator->add('taxMode', 'required', true);                            // taxMode
        $validator->add('orderNo', 'required', true, '{' . __('주문번호') . '}');            // 주문번호
        $validator->add('requestNm', 'required', true, '{' . __('신청자명') . '}');            // 신청자명
        $validator->add('requestGoodsNm', 'required', true, '{' . __('상품명') . '}');        // 상품명
        $validator->add('settlePrice', 'required', true, '{' . __('발행액') . '}');            // 발행액
        $validator->add('supplyPrice', 'required', false, '{' . __('공급액') . '}');            // 공급액
        $validator->add('taxPrice', 'required', false, '{' . __('부가세') . '}');            // 부가세
        $validator->add('taxBusiNo', 'required', true, '{' . __('사업자번호') . '}');        // 사업자번호
        $validator->add('taxCompany', 'required', true, '{' . __('회사명') . '}');            // 회사명
        $validator->add('taxCeoNm', 'required', true, '{' . __('대표자명') . '}');            // 대표자명
        $validator->add('taxService', 'required', true, '{' . __('업태}') . '}');                // 업태
        $validator->add('taxItem', 'required', true, '{' . __('종목') . '}');                // 종목
        $validator->add('taxEmail', 'required', true, '{' . __('발행 이메일') . '}');                // 발행 이메일
        $validator->add('taxAddress', 'required', true, '{' . __('사업장 주소') . '}');        // 사업장 주소
        $validator->add('taxAddressSub', '', false, '{' . __('사업장 나머지 주소') . '}');        // 사업장 나머지 주소
        $validator->add('statusFl', 'required', false);                        // 발행여부
        $validator->add('processDt', '', false);                                // 처리일자
        $validator->add('issueDt', '', false);                                // 처리일자
        $validator->add('cancelDt', '', false);                                // 취소일자
        $validator->add('taxFreeFl', '', false);                                // 과세여부
        $validator->add('adminMemo', '', false);                                // 관리자 메모


        // Validation 결과
        if ($validator->act($arrData, true) === false) {
            throw new Exception(implode("\n", $validator->errors));
        }

        $arrData['issueStatusFl']  = $arrData['statusFl'];

        // 기본값 설정
        $unsetDt1 = $unsetDt2 = false;

        // 계산서 종류
        $taxFreeTxt = ($arrData['taxFreeFl'] =='f') ? '계산서' : '세금계산서';
        $taxTypeTxt = ($godobillSend === true) ? '전자' : '일반';
        $taxStatusTxt = ($godobillSend === true) ? ' - 전송완료' : ' - 미발행(인쇄전)';


        // 로그 설정
        $taxLog = '====================================================' . chr(10);
        $taxLog .= '세금계산서 발행 (' . date('Y-m-d H:i:s') . ')' . chr(10);
        $taxLog .= '====================================================' . chr(10);

        // 발행 완료 처리
        if ($arrData['statusFl'] == 'y' && ($arrData['processDt'] == '0000-00-00 00:00:00' || $arrData['processDt'] == '')) {
            $arrData['issueDt'] = $arrData['issueDt'];
            $arrData['processDt'] = date('Y-m-d H:i:s');
            $taxLog .= '처리상태 : ' . $taxTypeTxt . $taxFreeTxt . ' 발행 완료' . $taxStatusTxt . chr(10);
        } else {
            $unsetDt1 = true;
        }

        // 발행 취소 처리
        if ($arrData['statusFl'] == 'c' && ($arrData['cancelDt'] == '0000-00-00 00:00:00' || $arrData['cancelDt'] == '')) {
            $arrData['cancelDt'] = date('Y-m-d H:i:s');
            $taxLog .= '처리상태 : 발행 취소' . chr(10);
        } else {
            $unsetDt2 = true;
        }

        $taxLog .= '처리자 : '. Session::get('manager.managerNm') .'(' . Session::get('manager.managerId') . ')' . chr(10);

        // 고도빌 체크
        if ($godobillSend === true) {
            if($arrData['settlePrice'] > 0) { // 고도빌의 경우 금액이 있는 경우만 전송
                if ($this->setCheckConnection($this->taxConf['godobillSiteId'], $this->taxConf['godobillApiKey'])) {

                    $arrData['issueFl'] = "e";
                    if ($arrData['taxFreeFl'] == 'f') $arrData['godobillTaxMode'] = "FREE";
                    else  $arrData['godobillTaxMode'] = "";

                    $result = $this->sendGodobill($arrData);
                    if ($result[0] == 'DONE') {
                        $taxLog .= '고도빌 전송 : ' . $result[1] . chr(10);
                        $taxLog .= '고도빌 CODE : ' . $result[2] . chr(10);
                        $arrData['godobillCd'] = $result[2];
                    } else {
                        $arrData['issueStatusFl'] = "n";
                        $taxLog .= '고도빌 전송 : ' . $result[1] . chr(10);
                        $taxLog .= '오류 코드 : ' . $result[0] . chr(10);

                        return [
                            false,
                            $result[1],
                        ];

                    }
                } else {
                    return [
                        false,
                        "고도빌을 확인해주세요",
                    ];
                }
            } else {
                $result[0] = 'DONE';
            }
        } else {
            $arrData['issueFl'] = "g";
        }
        $taxLog .= '처리 IP : ' . Request::getRemoteAddress() . chr(10);
        $taxLog .= '====================================================' . chr(10);

        // 시간 초기화
        if ($unsetDt1 === true) {
            unset($arrData['processDt']);
        }
        if ($unsetDt2 === true) {
            unset($arrData['cancelDt']);
        }

        if($arrData['settlePrice'] <= 0) {
            $saveTaxLogFl = false;
            $arrData['issueStatusFl'] = '';
        }

        // 설정 저장
        $_arrBind = $this->db->get_binding(DBTableField::tableOrderTax(), $arrData, 'update', array_keys($arrData));

        // 로그 저장
        if($saveTaxLogFl) {
            $_arrBind['param'][] = 'taxLog = concat(ifnull(taxLog, \'\'),?)';
            $this->db->bind_param_push($_arrBind['bind'], 's', $taxLog);
        }
        $this->db->bind_param_push($_arrBind['bind'], 'i', $arrData['sno']);
        $this->db->set_update_db(DB_ORDER_TAX, $_arrBind['param'], 'sno = ?', $_arrBind['bind']);
        unset($_arrBind);

        //발행관련 통계데이터
        $taxIssueData['orderNo'] = $arrData['orderNo'];
        $taxIssueData['taxBusiNo'] = $arrData['taxBusiNo'];
        $taxIssueData['taxFreeFl'] = $arrData['taxFreeFl'];
        $taxIssueData['taxGodobillCd'] = $arrData['godobillCd'];
        $taxIssueData['issueStatusFl'] = $arrData['issueStatusFl'];
        $taxIssueData['issuePrice'] = $arrData['supplyPrice'];
        $taxIssueData['vatPrice'] = $arrData['taxPrice'];

        //세금계산서 발급여부 다시 확인
        $this->db->bind_param_push($_arrBind['bind'], 's', $arrData['orderNo']);
        $this->db->bind_param_push($_arrBind['bind'], 's', $arrData['taxFreeFl']);
        $strSQL = "SELECT count(orderNo) as issueCnt FROM ".DB_ORDER_TAX_ISSUE." WHERE orderNo = '?' AND taxFreeFl='?'";
        $issueCnt = $this->db->query_fetch($strSQL, $_arrBind, false)['issueCnt'];
        unset($_arrBind);

        if ($arrData['taxMode'] == 'modify' && $issueCnt > 0 ) {

            $_arrBind = $this->db->get_binding(DBTableField::tableOrderTaxIssue(), $taxIssueData, 'update', array_keys($taxIssueData));

            $this->db->bind_param_push($_arrBind['bind'], 's', $arrData['orderNo']);
            $this->db->bind_param_push($_arrBind['bind'], 's', $arrData['taxFreeFl']);

            $this->db->set_update_db(DB_ORDER_TAX_ISSUE, $_arrBind['param'], 'orderNo = ? AND taxFreeFl = ? ', $_arrBind['bind']);

        } else {
            $_arrBind = $this->db->get_binding(DBTableField::tableOrderTaxIssue(), $taxIssueData, 'insert');
            $this->db->set_insert_db(DB_ORDER_TAX_ISSUE, $_arrBind['param'], $_arrBind['bind'], 'y');
        }


        unset($_arrBind);

        // 에러 리턴
        if ($godobillSend === true) {
            if ($result[0] == 'DONE') {
                return [
                    true,
                    '',
                ];
            } else {
                return [
                    false,
                    $result[1],
                ];
            }
        } else {
            return [
                true,
                '',
            ];
        }

    }

    /**
     * 주문 세금계산서 정보
     *
     * @param array $orderNo 주문 번호
     *
     * @return array 해당 주문 세금계산서 정보
     */
    public function getOrderTaxInvoice($orderNo,$printFl = false )
    {
        $order = \App::load('\\Component\\Order\\OrderAdmin');

        $arrExclude = ['orderNo'];
        $arrField = DBTableField::setTableField('tableOrderTax', null, $arrExclude, 'ot');
        $strSQL = 'SELECT ot.sno,o.memNo as orderMemNo,oi.orderEmail,oi.orderName, ' . implode(', ', $arrField) . ', ot.regDt,o.paymentDt FROM ' . DB_ORDER_TAX . ' ot INNER JOIN ' . DB_ORDER . ' o ON ot.orderNo = o.orderNo INNER JOIN ' . DB_ORDER_INFO . ' oi ON oi.orderNo = ot.orderNo LEFT JOIN ' . DB_ORDER_GOODS . ' og ON o.orderNo = og.orderNo WHERE ot.orderNo = ? AND LEFT(og.orderStatus, 1) != "e" ORDER BY orderCd DESC LIMIT 1';

        if ($printFl) {
            $strSQL .= " AND ot.statusFl = 'y' AND issueFl = 'g'";
        }
        $_arrBind = [
            's',
            $orderNo,
        ];

        //주문상품명 재조합하기위한 테이터
        $getData = $this->db->query_fetch($strSQL, $_arrBind, false);
        $excludeStatus = ['e1', 'e2', 'e3', 'e4', 'e5', 'c1', 'c2', 'c3', 'c4', 'c5', 'r1', 'r2', 'c3'];
        $orderGoodsData = $order->getOrderGoodsData($orderNo, null, null,null, null, true, false, null, $excludeStatus);
        if(count($orderGoodsData) > 0){
            $goodsNmData = [];
            $goodsCount = 0;
            foreach ($orderGoodsData as $scmNo => $dataVal) {
                $goodsCount += count($dataVal);
                foreach ($dataVal as $tmpGoodsData) {
                    $goodsNmData[$tmpGoodsData['orderCd']] = $tmpGoodsData['goodsNm'];
                }
            }
            ksort($goodsNmData);
            $goodsNmData = array_values($goodsNmData);

            $orderGoodsNm = $goodsNmData[0] . ($goodsCount > 1 ? __(' 외 ') . ($goodsCount - 1) . __(' 건') : '');
        }

        // 데이타 출력
        if (count($getData) > 0) {
            $getData['taxInvoiceInfo'] = $this->setTaxInvoicePrice($orderNo, $getData['taxPolicy']);
            if($getData['issueDt'] =='0000-00-00') {
                $getData['issueDt'] = '';
                if ($getData['taxStepFl'] == 'p') { // 발행일 기준 설정이 결제완료일
                    if (strtotime($getData['paymentDt']) > 0) {
                        $getData['issueDt'] = date('Y-m-d', strtotime($getData['paymentDt']));
                    }
                } else if ($getData['taxStepFl'] == 'd') { // 발행일 기준 설정이 배송완료일
                    $strField = 'GROUP_CONCAT(orderStatus SEPARATOR \'' . STR_DIVISION . '\') AS orderGoodsStatus, GROUP_CONCAT(deliveryCompleteDt SEPARATOR \'' . STR_DIVISION . '\') AS deliveryCompleteDt, GROUP_CONCAT(finishDt SEPARATOR \'' . STR_DIVISION . '\') AS finishDt';
                    $strSQL = 'SELECT ' . $strField . ' FROM ' . DB_ORDER_GOODS . ' WHERE orderNo = ? GROUP BY orderNo';
                    $_arrBind = ['s', $orderNo];

                    $taxData = $this->db->query_fetch($strSQL, $_arrBind, false);

                    $orderGoodsStatus = explode(STR_DIVISION, $taxData['orderGoodsStatus']);
                    $deliveryCompleteDt = explode(STR_DIVISION, $taxData['deliveryCompleteDt']);
                    $finishDt = explode(STR_DIVISION, $taxData['finishDt']);

                    foreach ($deliveryCompleteDt as $key => $val) {
                        if (in_array(substr($orderGoodsStatus[$key], 0, 1), ['c', 'f', 'b', 'e', 'r']) === true) continue;

                        if (strtotime($deliveryCompleteDt[$key]) > 0) {
                            $issueDt = date('Y-m-d', strtotime($deliveryCompleteDt[$key]));
                        } else if (strtotime($finishDt[$key]) > 0) {
                            $issueDt = date('Y-m-d', strtotime($finishDt[$key]));
                        }
                        if ($getData['taxDeliveryCompleteFl'] != 'y') {
                            if (empty($issueDt) === false) break;
                        } else {
                            if (empty($issueDt) === true) {
                                unset($prevIssueDt);
                                break;
                            }
                            if ((empty($prevIssueDt) === false && $prevIssueDt < $issueDt) || (empty($prevIssueDt) === true && empty($issueDt) === false)) {
                                $prevIssueDt = $issueDt;
                            }
                        }
                        unset($issueDt);
                    }
                    if (empty($prevIssueDt) === false) {
                        $issueDt = $prevIssueDt;
                    }
                    $getData['issueDt'] = $issueDt;
                    unset($issueDt, $prevIssueDt);
                }
            }

            //주문상품명 재조합
            if(trim($orderGoodsNm) !== ''){
                $getData['requestGoodsNm']    = $orderGoodsNm;
            }

            return gd_htmlspecialchars_stripslashes($getData);
        } else {
            return false;
        }
    }

    /**
     * 세금계산서 발행 리스트
     */
    public function getListTaxInvoice($getValue = [])
    {

        if (empty($getValue) === true) $getValue = Request::get()->toArray();

        $taxPolicy =  gd_policy('order.taxInvoice'); // 배송비 세금계산서 포함 여부 필요

        // 통합 검색
        $this->_search['combineSearch'] = [
            'all'            => '=' . __('통합검색') . '=',
            'taxCompany'        => __('회사명'),
            'taxCeoNm'      => __('대표자'),
            'requestNm' => __('요청인'),
            'orderNo' => __('주문번호'),
        ];

        // 기간 검색
        $this->_search['treatDate'] = [
            'issueDt'      => __('발행일'),
            'processDt' => __('처리일'),
            'regDt' => __('발행요청일'),
        ];

        // 검색을 위한 bind 정보
        $fieldType = DBTableField::getFieldTypes('tableOrderTax');

        //--- 검색 설정
        $searchPeriod = empty(gd_isset($getValue['orderNo'])) === false ? -1 : 6;
        $this->_search['key'] = gd_isset($getValue['key']);
        $this->_search['keyword'] = gd_isset($getValue['keyword']);
        $this->_search['taxBusiNo'] = gd_isset($getValue['taxBusiNo']);
        $this->_search['statusFl'] = gd_isset($getValue['statusFl']);
        $this->_search['orderNo'] = gd_isset($getValue['orderNo']);
        $this->_search['orderStatus'] = gd_isset($getValue['orderStatus']);
        $this->_search['searchPeriod'] = gd_isset($getValue['searchPeriod'], $searchPeriod);
        $this->_search['taxFl'] = gd_isset($getValue['taxFl']);
        if (empty($getValue['searchDateFl'])) {
            $this->_search['searchDateFl'] = gd_isset($getValue['treatDate'], 'issueDt');
        } else {
            $this->_search['searchDateFl'] = gd_isset($getValue['searchDateFl'], 'regDt');
        }

        if ($this->_search['statusFl'] =='r') {
            $this->_arrWhere[] = 'o.orderStatus NOT IN ("' . implode('","', $this->statusListExclude) . '")';
        }

        $this->_arrWhere[] = 'ot.orderNo = o.orderNo';

        if (empty($this->_search['orderNo']) === false) {
            $this->_arrWhere[] = 'o.orderNo IN ("' . implode('","', $this->_search['orderNo']) . '")';
        }



        if ($this->_search['searchPeriod'] < 0) {
            $this->_search['searchDate'][] = gd_isset($getValue['searchDate'][0]);
            $this->_search['searchDate'][] = gd_isset($getValue['searchDate'][1]);
        } else {
            $this->_search['searchDate'][] = gd_isset($getValue['searchDate'][0], date('Y-m-d', strtotime('-6 day')));
            $this->_search['searchDate'][] = gd_isset($getValue['searchDate'][1], date('Y-m-d'));
        }

        // 키워드 검색
        if ($this->_search['key'] && $this->_search['keyword']) {
            if ($this->_search['key'] == 'all') {
                $tmpWhere = array_keys($this->_search['combineSearch']);
                array_shift($tmpWhere);
                $_arrWhereAll = [];
                foreach ($tmpWhere as $keyNm) {
                    $_arrWhereAll[] = '(ot.' . $keyNm . ' LIKE concat(\'%\',?,\'%\'))';
                    $this->db->bind_param_push($this->_arrBind, $fieldType[$keyNm], $this->_search['keyword']);
                }
                $this->_arrSubWhere[] = '(' . implode(' OR ', $_arrWhereAll) . ')';
                unset($tmpWhere);
            } else {
                $this->_arrSubWhere[] = 'ot.' . $this->_search['key'] . ' LIKE concat(\'%\',?,\'%\')';
                $this->db->bind_param_push($this->_arrBind, $fieldType[$this->_search['key']], $this->_search['keyword']);
            }
        }

        // 발행 상태 검색
        if ($this->_search['statusFl']) {
            $this->_arrSubWhere[] = 'ot.statusFl = ?';
            $this->db->bind_param_push($this->_arrBind, $fieldType['statusFl'], $this->_search['statusFl']);
        }

        // 사업자번호
        if ($this->_search['taxBusiNo']) {
            $this->_arrSubWhere[] = 'ot.taxBusiNo LIKE concat(\'%\',?,\'%\')';
            $this->db->bind_param_push($this->_arrBind, $fieldType['taxBusiNo'], $this->_search['taxBusiNo']);
        }

        // 처리일자 검색
        if ($this->_search['searchDate'][0] && $this->_search['searchDate'][1]) {
            $this->_arrSubWhere[] = 'ot.'.$this->_search['searchDateFl'].' BETWEEN ? AND ?';
            $this->db->bind_param_push($this->_arrBind, 's', $this->_search['searchDate'][0] . ' 00:00:00');
            $this->db->bind_param_push($this->_arrBind, 's', $this->_search['searchDate'][1] . ' 23:59:59');
        }

        $existsWhere = [];
        $existsJoin = '';
        // 주문상태
        if ($this->_search['orderStatus'][0]) {
            foreach ($this->_search['orderStatus'] as $val) {
                $tmpWhere[] = 'og.orderStatus = ?';
                $this->db->bind_param_push($this->_arrBind, 's', $val);
                $this->_checked['orderStatus'][$val] = 'checked="checked"';
            }
            $existsWhere[] =
            $this->_addWhere[] = '(' . implode(' OR ', $tmpWhere) . ')';
            unset($tmpWhere);
        } else {
            $this->_checked['orderStatus'][''] = 'checked="checked"';
        }

        // 과세 면세 여부
        if ( empty($this->_search['taxFl']) === false ) {
            if ( $taxPolicy['taxDeliveryFl'] == 'y' ) {
                $existsJoin = 'INNER JOIN es_orderDelivery od ON od.orderNo = og.orderNo';
                $existsWhere[] =
                $this->_addWhere[] = ' ( og.goodsTaxInfo LIKE concat(\'%\',?,\'%\') OR ( od.deliveryTaxInfo LIKE concat(\'%\',?,\'%\') AND od.deliveryCollectFl = \'pre\') ) ';
                //$this->_addWhere[] = ' ( og.goodsTaxInfo LIKE concat(\'%\',?,\'%\') OR ( od.deliveryTaxInfo LIKE concat(\'%\',?,\'%\') AND od.deliveryCollectFl = \'pre\') ) AND og.orderStatus <> \'r3\' ';

                $this->db->bind_param_push($this->_arrBind, 's', $this->_search['taxFl']);
                $this->db->bind_param_push($this->_arrBind, 's', $this->_search['taxFl']);
            } else {
                $existsWhere[] =
                $this->_addWhere[] = ' og.goodsTaxInfo LIKE concat(\'%\',?,\'%\') ';
                //$this->_addWhere[] = ' og.goodsTaxInfo LIKE concat(\'%\',?,\'%\')  AND og.orderStatus <> \'r3\' ';
                $this->db->bind_param_push($this->_arrBind, 's', $this->_search['taxFl']);
            }
            $this->_checked['taxFl'][$this->_search['taxFl']] = 'checked="checked"';
        } else {
            $this->_checked['taxFl'][''] = 'checked="checked"';
        }

        if ( empty($existsWhere) === false ) { // 주문상태 or 과세,면세 검색 시
            $this->_arrSubWhere[] = ' EXISTS (SELECT 1 FROM ' . DB_ORDER_GOODS . ' og ' . $existsJoin. ' WHERE ' . implode(' AND ', gd_isset($existsWhere)) . ' AND og.orderNo = ot.orderNo) ';
            unset($existsJoin);
            unset($existsWhere);
        }

        if (empty($this->_arrBind)) {
            $this->_arrBind = null;
        }

        //--- 정렬 설정
        $sort['fieldName'] = gd_isset($getValue['sort']['name']);
        $sort['sortMode'] = gd_isset($getValue['sort']['mode']);
        if (empty($sort['fieldName']) || empty($sort['sortMode'])) {
            $sort['fieldName'] = 'ot.regDt';
            $sort['sortMode'] = 'desc';
        }

        //--- 페이지 기본설정
        gd_isset($getValue['page'], 1);
        gd_isset($getValue['pageNum'], 10);


        $page = \App::load('\\Component\\Page\\Page', $getValue['page']);
        $page->page['list'] = $getValue['pageNum']; // 페이지당 리스트 수
        if ($this->_search['statusFl'] =='r') {
            $strSQL = ' SELECT COUNT(*) AS cnt FROM ' . DB_ORDER_TAX . ' as ot INNER JOIN ' . DB_ORDER . ' o ON ot.orderNo = o.orderNo WHERE statusFl=? AND ' . $this->_arrWhere[0];
        } else {
            $strSQL = ' SELECT COUNT(*) AS cnt FROM ' . DB_ORDER_TAX . ' WHERE statusFl=? ';
        }

        $arrBind = [];
        $this->db->bind_param_push($arrBind, 's', $this->_search['statusFl']);
        $res = $this->db->query_fetch($strSQL, $arrBind, false);
        unset($arrBind);

        $page->recode['amount'] = $res['cnt']; // 전체 레코드 수
        $page->setPage();
        $page->setUrl(\Request::getQueryString());

        //검색 레코드 구하기
        $strSQL = 'SELECT COUNT(1) as cnt FROM ' . DB_ORDER_TAX . ' ot WHERE ' . @implode(' AND ', $this->_arrSubWhere) . ' AND EXISTS (SELECT 1 from es_order o where ' . @implode(' AND ', $this->_arrWhere).')';
        $total = $this->db->query_fetch($strSQL, $this->_arrBind, false)['cnt'];
        //debug($this->db->getBindingQueryString($strSQL, $this->_arrBind));
        $page->recode['total'] = $total;
        $page->setPage();

        //--- 검색 레코드에서는 필요 없어서 (data 쿼리에서 필요) 검색 쿼리 후 바인딩 추가
        //--- 바인딩 순서 때문에 아래로 이동 (주문상태)
        if ($this->_search['orderStatus'][0]) {
            foreach ($this->_search['orderStatus'] as $val) {
                $this->db->bind_param_push($this->_arrBind, 's', $val);
            }
        }
        //--- 바인딩 순서 때문에 아래로 이동 (과세 면세)
        if ( empty($this->_search['taxFl']) === false ) {
            if ( $taxPolicy['taxDeliveryFl'] == 'y' ) {
                $this->db->bind_param_push($this->_arrBind, 's', $this->_search['taxFl']);
            }
            $this->db->bind_param_push($this->_arrBind, 's', $this->_search['taxFl']);
        }


        $this->db->strField = 'ot.sno, ' . implode(', ', DBTableField::setTableField('tableOrderTax', null, null, 'ot')) . ', ot.regDt, ot.orderStatus, m.memNo, m.memId, ot.paymentDt, GROUP_CONCAT(og.orderStatus SEPARATOR \'' . STR_DIVISION . '\') AS orderGoodsStatus, GROUP_CONCAT(og.deliveryCompleteDt SEPARATOR \'' . STR_DIVISION . '\') AS deliveryCompleteDt, GROUP_CONCAT(og.finishDt SEPARATOR \'' . STR_DIVISION . '\') AS finishDt, oi.orderName, oi.orderEmail';
        $orderTaxField = 'ot.sno, ' . implode(', ', DBTableField::setTableField('tableOrderTax', null, null, 'ot')) . ', ot.regDt, o.orderStatus, o.paymentDt, o.memNo';

        $join[] = 'INNER JOIN ' . DB_ORDER_INFO . ' oi ON oi.orderNo = ot.orderNo AND oi.orderInfoCd = 1 ';
        $join[] = ' LEFT JOIN ' . DB_MEMBER . ' m ON ot.memNo = m.memNo ';
        // 발행일 기준 날짜를 알기 위한 무조건 orderGoods 를 불러와야 함
//        if ($this->_search['orderStatus'][0]) {
        $join[] = ' LEFT JOIN ' . DB_ORDER_GOODS . ' og ON ot.orderNo = og.orderNo';
//        }
        if ( empty($this->_search['taxFl']) === false && $taxPolicy['taxDeliveryFl'] == 'y' ) {
            $join[] = ' LEFT JOIN ' . DB_ORDER_DELIVERY . ' od ON ot.orderNo = od.orderNo';
        }

        $this->db->strJoin =  implode('', $join);
        $this->db->strWhere = implode(' AND ', gd_isset($this->_addWhere));
        $this->db->strGroup = 'ot.orderNo';
        $this->db->strOrder = $sort['fieldName'] . ' ' . $sort['sortMode'];
        $strLimit = $page->recode['start'] . ',' . $getValue['pageNum'];

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM (SELECT ' . $orderTaxField . ' FROM ' . DB_ORDER_TAX . ' ot, ' . DB_ORDER . ' o WHERE ' . @implode(' AND ', $this->_arrWhere) . ' AND ' . @implode(' AND ', $this->_arrSubWhere) . ' ORDER BY ' . $sort['fieldName'] . ' ' . $sort['sortMode'] . ' LIMIT ' . $strLimit . ') ot ' . implode(' ', $query);
        //debug($this->db->getBindingQueryString($strSQL, $this->_arrBind));
        //exit;
        $data = $this->db->query_fetch($strSQL, $this->_arrBind);

        $order = \App::load('\\Component\\Order\\OrderAdmin');

        //계산관련
        foreach ($data as $k => $v) {
            if($v['taxEmail'] =='' && $v['orderEmail']) $data[$k]['taxEmail'] = $v['orderEmail'];
            if($v['issueDt'] =='0000-00-00') {
                $data[$k]['issueDt'] = '';
                if ($v['taxStepFl'] == 'p') { // 발행일 기준 설정이 결제완료일
                    if (strtotime($v['paymentDt']) > 0) {
                        $data[$k]['issueDt'] = date('Y-m-d', strtotime($v['paymentDt']));
                    }
                } else if ($v['taxStepFl'] == 'd') { // 발행일 기준 설정이 배송완료일
                    $orderGoodsStatus = explode(STR_DIVISION, $v['orderGoodsStatus']);
                    $deliveryCompleteDt = explode(STR_DIVISION, $v['deliveryCompleteDt']);
                    $finishDt = explode(STR_DIVISION, $v['finishDt']);

                    foreach ($deliveryCompleteDt as $key => $val) {
                        if (in_array(substr($orderGoodsStatus[$key], 0, 1), ['c', 'f', 'b', 'e', 'r']) === true) continue;

                        if (strtotime($deliveryCompleteDt[$key]) > 0) {
                            $issueDt = date('Y-m-d', strtotime($deliveryCompleteDt[$key]));
                        } else if (strtotime($finishDt[$key]) > 0) {
                            $issueDt = date('Y-m-d', strtotime($finishDt[$key]));
                        }
                        if ($v['taxDeliveryCompleteFl'] != 'y') {
                            if (empty($issueDt) === false) break;
                        } else {
                            if (empty($issueDt) === true) {
                                unset($prevIssueDt);
                                break;
                            }
                            if ((empty($prevIssueDt) === false && $prevIssueDt < $issueDt) || (empty($prevIssueDt) === true && empty($issueDt) === false)) {
                                $prevIssueDt = $issueDt;
                            }
                        }
                        unset($issueDt);
                    }
                    if (empty($prevIssueDt) === false) {
                        $issueDt = $prevIssueDt;
                    }
                    $data[$k]['issueDt'] = $issueDt;
                    unset($issueDt, $prevIssueDt);
                }
            }

            $data[$k]['orderStatusStr'] = $order->getOrderStatusAdmin($v['orderStatus']);
            $data[$k]['taxInvoiceInfo'] = $this->setTaxInvoicePrice($v['orderNo'], $v['taxPolicy']);

            if ($v['statusFl'] =='y') {
                $data[$k]['taxIssueInfo'] = $this->getTaxIssue($v['orderNo']);
            }
            if ( empty($v['applicantNm']) ) {
                $data[$k]['applicantNm'] = $v['orderName'];
            }
            if ( empty($v['applicantId']) ) {
                $data[$k]['applicantId'] = $v['memId'] ? $v['memId'] : "비회원";
            }
            if ( empty($v['requestId']) ) {
                $data[$k]['requestId'] = $v['memId'] ? $v['memId'] : "비회원";
            }
        }

        if($this->_search['statusFl'] =='y') {

            $taxStats = [];

            $issueFl = array('g','e');
            $taxFreeFl = array('t','f');


            if ($this->_search['searchDate'][0] && $this->_search['searchDate'][1]) {
                $addWhere= 'ot.'.$this->_search['searchDateFl'].' BETWEEN "'.$this->_search['searchDate'][0].'" AND "'.$this->_search['searchDate'][1].'"';
            }

            foreach ($issueFl as $k => $v) {
                foreach ($taxFreeFl as $k1 => $v1) {
                    $strSQL = "SELECT GROUP_CONCAT(ot.orderNo SEPARATOR  ',') as orderList ,if(SUM(oti.issuePrice) > 0 ,COUNT(ot.taxBusiNo),0) AS issueCnt ,SUM(oti.issuePrice) AS price , SUM(oti.vatPrice) AS vat FROM es_orderTax AS ot LEFT JOIN es_orderTaxIssue AS oti ON oti.orderNo = ot.orderNo INNER JOIN es_order as o ON o.orderNo = ot.orderNo WHERE issueFl = '".$v."' AND taxFreeFl = '".$v1."' AND issueStatusFl='y' ";

                    if ($addWhere) {
                        $strSQL .= " AND ".$addWhere;
                    }

                    $tmpData = $this->db->query_fetch($strSQL, null, false);
                    $tmpData['issueFl'] =$v;
                    $tmpData['taxFreeFl'] =$v1;

                    if ($tmpData['orderList']) {
                        $strSQL = "SELECT count(DISTINCT taxBusiNo) as companyCnt FROM es_orderTax WHERE orderNo IN(".$tmpData['orderList'].")";
                        $tmpScmData = $this->db->query_fetch($strSQL, null, false);
                        $tmpData['companyCnt'] = $tmpScmData['companyCnt'];
                    }

                    $taxStats[] = $tmpData;

                }
            }

            $getData['taxStats'] = $taxStats;
        }


        // 각 데이터 배열화
        $getData['data'] = gd_htmlspecialchars_stripslashes(gd_isset($data));
        $getData['sort'] = $sort;
        $getData['search'] = gd_htmlspecialchars($this->_search);
        $getData['checked'] = $this->_checked;

        return $getData;
    }


    /**
     * 세금계산서 발행상세내역
     *
     * @param integer sno
     */
    public function getTaxIssue($orderNo) {

        if (empty($arrBind) === true) {
            $arrBind = [];
        }

        $arrWhere[]  = 'orderNo = ?';
        $this->db->bind_param_push($arrBind, 's', $orderNo);

        $this->db->strWhere = implode(' AND ', $arrWhere);

        // 쿼리문 생성
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_TAX_ISSUE . implode(' ', $query);

        $getData = $this->db->query_fetch($strSQL, $arrBind);
        foreach($getData as $k => $v) {
            $returnData[$v['taxFreeFl']] =  $v;
        }

        return gd_htmlspecialchars_stripslashes($returnData);

    }



    /**
     * 세금계산서 금액관련내용
     *
     * @param integer $orderNo 삭제할 레코드 sno
     */
    public function setTaxInvoicePrice($orderNo,$taxPolicy)
    {
        $order = \App::load('\\Component\\Order\\OrderAdmin');

        $taxPolicy = explode(STR_DIVISION,$taxPolicy); //배송비 | 마일리지 | 예치금

        $taxFreeGoodsPrice = 0;
        $taxSupplyGoodsPrice = 0;
        $taxVatGoodsPrice = 0;
        $orderHandleData = [];

        $goodsData = $order->getOrderGoods($orderNo);
        $tmpOrderHandleData = $order->getOrderHandle($orderNo);
        if(count($tmpOrderHandleData) > 0){
            foreach($tmpOrderHandleData as $key => $val){
                $orderHandleData[$val['sno']] = $val;
            }
        }

        $orderRefund='';
        $is_r3 = array('t'=>false,'f'=>false);
        foreach($goodsData as $k1 => $v1) {
            $statusMode = substr($v1['orderStatus'], 0, 1);
            // 환불완료 제외 / 취소 제외 / 패치 이후 교환취소 제외

            $goodsTax = explode(STR_DIVISION, $v1['goodsTaxInfo']);

            if($v1['orderStatus'] === 'r3'){
                $is_r3[$goodsTax[0]] = true;
                continue;
            }
            if($statusMode == 'c' || ($statusMode == 'e' && ($order->orderExchangeChangeDate > $orderHandleData[$v1['handleSno']]['handleRegDt']))){
                continue;
            }

            $addPaymentPrice = 0;
            if($taxPolicy[1] =='y') {
                $addPaymentPrice += $v1['divisionUseMileage'];
            }
            if($taxPolicy[2] =='y') {
                $addPaymentPrice += $v1['divisionUseDeposit'];
            }

            if($goodsTax[0] === 't'){
                $taxSupplyGoodsPrice += $addPaymentPrice;
            }
            else {
                $taxFreeGoodsPrice += $addPaymentPrice;
            }

            $taxSupplyGoodsPrice += $v1['realTaxSupplyGoodsPrice'];
            $taxVatGoodsPrice += $v1['realTaxVatGoodsPrice'];
            $taxFreeGoodsPrice += $v1['realTaxFreeGoodsPrice'];

            $taxFreeAddGoodsPrice = 0;
            $taxSupplyAddGoodssPrice = 0;
            $taxVatAddGoodssPrice = 0;

            $addGoodsData = $order->getOrderAddGoods($orderNo,$v1['orderCd']);

            if($addGoodsData) {
                foreach($addGoodsData as $k2 => $v2) {
                    $addGoodsPrice = 0;

                    $taxInfo = explode(STR_DIVISION,$v2['goodsTaxInfo']);

                    if($taxPolicy[1] =='y') $addGoodsPrice += $v2['divisionAddUseMileage'];
                    if($taxPolicy[2] =='y') $addGoodsPrice += $v2['divisionAddUseDeposit'];

                    if($taxInfo['0'] =='t' ) { //과세상품인경우
                        $addGoodsPrice += $v2['taxSupplyAddGoodsPrice'] +$v2['taxVatAddGoodsPrice'];
                        $tmpPrice =  NumberUtils::taxAll($addGoodsPrice, $taxInfo[1], $taxInfo['0']);

                        $taxSupplyAddGoodssPrice +=($tmpPrice['supply']);
                        $taxVatAddGoodssPrice += $tmpPrice['tax'];
                    } else { //비과세상품인경우
                        $taxFreeAddGoodsPrice +=$v2['taxFreeAddGoodsPrice'];
                    }

                }
            }
        }


        $arrBind = [];
        //비과세 배송비 합
        $strSQL = 'SELECT realTaxSupplyDeliveryCharge, realTaxVatDeliveryCharge, realTaxFreeDeliveryCharge, divisionDeliveryUseMileage, divisionDeliveryUseDeposit, deliveryTaxInfo FROM '.DB_ORDER_DELIVERY.' WHERE orderNo = ?  ';

        $this->db->bind_param_push($arrBind, 's', $orderNo);
        $deliveryData = $this->db->query_fetch($strSQL, $arrBind, true);
        unset($arrBind);

        $taxSupplyDeliveryPrice = 0;
        $taxVatDeliveryPrice = 0;
        $taxFreeDeliveryPrice = 0;
        $is_delivery = array('t'=>false,'f'=>false);
        foreach ($deliveryData as $deliveryKey => $deliveryVal) {
            $taxInfo = explode(STR_DIVISION, $deliveryVal['deliveryTaxInfo']);
            $is_delivery[$taxInfo[0]] = true;
            $addPaymentPrice = 0;
            if($taxPolicy[1] =='y') {
                $addPaymentPrice += $deliveryVal['divisionDeliveryUseMileage'];
            }
            if($taxPolicy[2] =='y') {
                $addPaymentPrice += $deliveryVal['divisionDeliveryUseDeposit'];
            }

            if($taxInfo[0] === 't'){
                $taxSupplyDeliveryPrice += $addPaymentPrice;
            }
            else {
                $taxFreeDeliveryPrice += $addPaymentPrice;
            }

            if($taxPolicy[0] === 'y'){
                $taxSupplyDeliveryPrice += $deliveryVal['realTaxSupplyDeliveryCharge'];
                $taxVatDeliveryPrice += $deliveryVal['realTaxVatDeliveryCharge'];
                $taxFreeDeliveryPrice += $deliveryVal['realTaxFreeDeliveryCharge'];
            }
        }

        $totalTaxSupplyPrice = $taxSupplyGoodsPrice + $taxSupplyAddGoodssPrice + $taxSupplyDeliveryPrice;
        $totalTaxVatPrice = $taxVatGoodsPrice + $taxVatAddGoodssPrice + $taxVatDeliveryPrice;
        $totalTaxFreePrice = $taxFreeGoodsPrice + $taxFreeAddGoodsPrice + $taxFreeDeliveryPrice;

        if($totalTaxSupplyPrice > 0 || $totalTaxVatPrice > 0 || $is_r3['t'] || $is_delivery['t']) {
            $price = gd_money_format($totalTaxSupplyPrice,false);
            $vat = gd_money_format($totalTaxVatPrice,false);
            $totalPrice = $price+$vat;
            $getData[] = array('price' => $price,'vat'=>$vat,'tax'=>"t",'totalPrice'=>$totalPrice);

        }
        if($totalTaxFreePrice > 0 || $is_r3['f'] || $is_delivery['f']) {
            $price = gd_money_format($totalTaxFreePrice,false);
            $vat = 0;
            $totalPrice = $price+$vat;
            $getData[] = array('price' => $price,'vat'=>$vat,'tax'=>"f",'totalPrice'=>$totalPrice);
        }

        //if($totalTaxFreePrice == 0 && $totalTaxSupplyPrice == 0 && $totalTaxVatPrice ==  0) {
        //$getData[] = array('price' => 0,'vat'=>0,'tax'=>"f",'totalPrice'=>0);
        //}
        unset($orderRefund);

        //과세인경우 전체금액으로 10% 고정 재계산
        foreach($getData as $k => $v) {
            if($v['tax'] =='t') {
                $tmpPrice =  NumberUtils::taxAll($v['totalPrice'], '10', 't');
                $getData[$k]['vat'] = $tmpPrice['tax'];
                $getData[$k]['price'] = $tmpPrice['supply'];
            }
        }

        return $getData;
    }

    /**
     * 세금계산서 삭제
     *
     * @param integer $orderNo 삭제할 레코드 sno
     */
    public function setTaxInvoiceDelete($orderNo)
    {
        // 세금계산서 정보 삭제
        $this->db->bind_param_push($_arrBind, 's', $orderNo);
        $this->db->set_delete_db(DB_ORDER_TAX, 'orderNo = ?', $_arrBind);
        unset($_arrBind);

        // 세금계산서 발급내역
        $this->db->bind_param_push($_arrBind, 's', $orderNo);
        $this->db->set_delete_db(DB_ORDER_TAX_ISSUE, 'orderNo = ?', $_arrBind);
        unset($_arrBind);

        // 주문 정보 수정
        $order = \App::load('\\Component\\Order\\OrderAdmin');
        $order->setOrderReceiptRequest($orderNo, 'n');
    }

    /**
     * 세금계산서 설정
     */
    public function getTaxConf()
    {
        $getData = [
            'paper'    => $this->taxConf['gTaxInvoiceFl'],
            'godobill' => gd_isset($this->taxConf['eTaxInvoiceFl'], 'n'),
            'useFl'  => $this->taxConf['taxInvoiceUseFl'],
            'step'  => $this->taxConf['taxStepFl']
        ];

        return $getData;
    }

    /**
     * 세금계산서 발행 단계 체크
     *
     * @param string $orderStatus 주문 단계
     *
     * @return boolean 성공 여부
     */
    public function setCheckTaxStatus($orderStatus)
    {
        // 세금계산서 설정값
        $getConf = $this->getTaxConf();

        // 주문 단계 재설정
        $orderStatus = substr($orderStatus, 0, 1);

        // 발행단계 설정
        $arrCheckStatus = [
            'p' => 1,
            'g' => 2,
            'd' => 3,
        ];

        // 비교
        if (in_array($orderStatus, array_keys($arrCheckStatus)) === true) {
            if ($arrCheckStatus[$orderStatus] >= $arrCheckStatus[$getConf['taxStep']]) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * 고도빌 연결 체크
     *
     * @param string $siteId 고도빌 회원 ID
     * @param string $apiKey 고도빌 API KEY
     *
     * @return boolean 성공 여부
     */
    public function setCheckConnection($siteId, $apiKey)
    {
        $requestPost = [
            'id'      => $siteId,
            'api_key' => $apiKey,
        ];

        $url = 'https://' . $this->godobillServer . ':444/gate/check.php';
        $result = HttpUtils::remoteGet($url . '?' . http_build_query($requestPost));

        if (substr($result, 0, 2) == 'OK') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 고도빌 발행내역 목록 바로가기 주소
     */
    public function getGodobillLinkList()
    {
        return 'https://' . $this->godobillServer . ':444/gate/page.php?id=' . $this->taxConf['godobillSiteId'] . '&api_key=' . $this->taxConf['godobillApiKey'] . '&mode=list';
    }

    /**
     * 고도빌 발행내역 상세 바로가기 주소
     *
     * @param string $godobillCd 고도빌 정보 ID
     *
     * @return string
     */
    public function getGodobillLinkDetail($godobillCd)
    {
        return 'https://' . $this->godobillServer . ':444/gate/page.php?id=' . $this->taxConf['godobillSiteId'] . '&api_key=' . $this->taxConf['godobillApiKey'] . '&mode=detail&taxid=' . $godobillCd;
    }

    /**
     * 세금계산서 리스트에서 고도빌 전송
     *
     * @param integer $setData 전송할 주문번호
     *
     * @return array
     */
    public function setSendTaxInvoice($postValue)
    {

        if($postValue['mode'] =='resend_godobill') {
            $getData = $this->getOrderTaxInvoice($postValue['orderNo']);

            foreach($getData['taxInvoiceInfo'] as $k1 => $v1) {

                if($v1['tax'] == $postValue['taxFreeFl']) {

                    $setData = [];

                    $setData = $getData;
                    $setData['orderNo'] = $postValue['orderNo'];
                    $setData['taxMode'] = "modify";
                    $setData['taxFreeFl'] = $v1['tax']; //일반계산서인지 세금계산서인지 구분
                    $setData['settlePrice'] =  $v1['price'] + $v1['vat'];
                    $setData['supplyPrice'] =  $v1['price'];
                    $setData['taxPrice'] =  $v1['vat'];
                    $setData['statusFl'] = "y";
                    $setData['godobillSend'] = "y";

                    $result = $this->issueTaxInvoice($setData);
                    if ($postValue['saveTaxInfoFl'] == 'y') {
                        $memberInvoiceInfo = $this->setOrderTaxInfoConvert($setData);
                        $this->saveMemberTaxInvoiceInfo($memberInvoiceInfo);
                    }

                }

            }

        } else {

            foreach($postValue['orderNo'] as $k => $v) {
                $taxInvoicieInfo = $this->setTaxInvoicePrice($v,$postValue['taxInvoiceData'][$v]['taxPolicy']);
                foreach($taxInvoicieInfo as $k1 => $v1) {
                    $setData = [];
                    $setData = $postValue['taxInvoiceData'][$v];
                    $setData['orderNo'] = $v;
                    $setData['taxMode'] = "insert";
                    $setData['taxFreeFl'] = $v1['tax']; //일반계산서인지 세금계산서인지 구분
                    $setData['settlePrice'] =  $v1['price'] + $v1['vat'];
                    $setData['supplyPrice'] =  $v1['price'];
                    $setData['taxPrice'] =  $v1['vat'];
                    $setData['issueDt'] =  $postValue['taxInvoiceData'][$v]['issueDt'];
                    $setData['statusFl'] = "y";
                    $setData['godobillSend'] = $postValue['godobillSend'];

                    $result = $this->issueTaxInvoice($setData);
                    if ($postValue['saveTaxInfoFl'] == 'y') {
                        $memberInvoiceInfo = $this->setOrderTaxInfoConvert($setData);
                        $this->saveMemberTaxInvoiceInfo($memberInvoiceInfo);
                    }
                    if(empty($result[0]) === true) {
                        throw new \Exception($result[1]);
                    }
                }
            }
        }

        return true;
    }

    public function setOrderTaxInfoConvert($getData)
    {
        return [
            'memNo' => $getData['memNo'],
            'taxBusiNo' => $getData['taxBusiNo'],
            'company' => $getData['taxCompany'],
            'ceo' => $getData['taxCeoNm'],
            'service' => $getData['taxService'],
            'item' => $getData['taxItem'],
            'comZonecode' => $getData['taxZonecode'],
            'comZipcode' => $getData['taxZipcode'],
            'comAddress' => $getData['taxAddress'],
            'comAddressSub' => $getData['taxAddressSub'],
            'email' => $getData['taxEmail'],
        ];
    }

    /**
     * 고도빌 전송 세팅 & 결과
     *
     * @param string $setData 세금계산서 정보
     * @param string $orderNo 주문 번호
     *
     * @return array
     */
    protected function sendGodobill($setData = null, $orderNo = null)
    {
        // 데이터 체크
        if ($setData === null && $orderNo === null) {
            return [
                'CHECK1',
                __('데이타 오류'),
                '',
            ];
        }

        // 데이터 세팅
        if ($setData === null && empty($orderNo) === false) {
            $setData = $this->getOrderTaxInvoice($orderNo);
            $setData['orderNo'] = $orderNo;
        }

        // 데이터 체크
        if (empty($setData) === true) {
            return [
                'CHECK2',
                __('데이타 오류'),
                '',
            ];
        }

        // 주문 정보
        $this->_arrWhere[] = 'o.orderNo = ?';
        $this->db->bind_param_push($this->_arrBind, 's', $setData['orderNo']);
        $this->db->strField = 'o.orderGoodsNm, oi.orderEmail, oi.orderCellPhone';
        $this->db->strJoin = ' INNER JOIN ' . DB_ORDER_INFO . ' oi ON o.orderNo = oi.orderNo AND oi.orderInfoCd = 1 ';
        $this->db->strWhere = implode(' AND ', gd_isset($this->_arrWhere));
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER . ' o ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $this->_arrBind, false);
        unset($this->_arrWhere, $this->_arrBind);

        // 데이터 체크
        if (empty($getData['orderGoodsNm']) === true) {
            return [
                'CHECK3',
                __('데이타 오류'),
                '',
            ];
        }

        // 고도빌 상품명 제한으로 인해 상품명 단축
        $order = \App::load('\\Component\\Order\\Order');
        $orderGoodsCnt = count($order->getOrderGoods($setData['orderNo']));
        if ($orderGoodsCnt > 1 && StringUtils::strLength($getData['orderGoodsNm']) > 25) {
            $getData['orderGoodsNm'] = gd_htmlspecialchars_slashes(StringUtils::cutHtml($getData['orderGoodsNm'], '20') . ' 외 ' . ($orderGoodsCnt - 1) . ' 건');
        } else {
            $getData['orderGoodsNm'] = gd_htmlspecialchars_slashes(StringUtils::cutHtml($getData['orderGoodsNm'], '25'));
        }
        unset($order, $orderGoodsCnt);

        $arrData['DMDER_BUSNID_TP_CD'] = '01';                                            // 사업자등록번호 구분코드(01 사업자등록번호,02 주민등록번호,03 외국인)
        $arrData['DMDER_BUSNID'] = str_replace('-', '', $setData['taxBusiNo']);    // 사업자등록번호
        $arrData['DMDER_SUB_BD_NO'] = '';                                            // 종사업자등록번호
        $arrData['SUP_AMT_SM'] = $setData['supplyPrice'];                        // 공급가액총액
        $arrData['TX_SM'] = $setData['taxPrice'];                            // 세액합계
        $arrData['TOT_AMT'] = $setData['settlePrice'];                        // 총금액
        $arrData['ETAXBIL_KND_CD'] = '01';                                            // 전자세금계산서종류코드(01 일반, 02 영세)
        $arrData['RCPT_RQEST_TP_CD'] = '01';                                            // 영수청구구분코드(01 영수, 02 청구)
        $arrData['WRITE_DT'] = gd_date_format('Ymd', $setData['issueDt']);    // 작성날짜
        $arrData['ETAXBIL_NOTE'] = '';                                            // 비고
        $arrData['TAXMODE'] = $setData['godobillTaxMode'];                        // 일반계산서구분


        $arrData['DMDER_MAIN_TX_OFFCR_NM'] = trim(iconv('UTF-8', 'EUC-KR//IGNORE', $setData['requestNm']));
        $arrData['DMDER_MAIN_TX_OFFCR_EMAIL_ADDR'] = trim(iconv('UTF-8', 'EUC-KR//IGNORE', $setData['taxEmail']));
        $arrData['DMDER_MAIN_TX_OFFCR_MTEL_NO'] = $getData['orderCellPhone'];
        $arrData['DMDER_BUSNSECT_NM'] = trim(iconv('UTF-8', 'EUC-KR//IGNORE', $setData['taxService']));
        $arrData['DMDER_DETAIL_NM'] = trim(iconv('UTF-8', 'EUC-KR//IGNORE', $setData['taxItem']));
        $arrData['DMDER_CHIEF_NM'] = trim(iconv('UTF-8', 'EUC-KR//IGNORE', $setData['taxCeoNm']));
        $arrData['DMDER_TRADE_NM'] = trim(iconv('UTF-8', 'EUC-KR//IGNORE', $setData['taxCompany']));
        $arrData['DMDER_ADDR'] = trim(iconv('UTF-8', 'EUC-KR//IGNORE', $setData['taxAddress'] . ' ' . $setData['taxAddressSub']));

        $arrData['item'][] = [
            'THNG_PURCHS_DT' => gd_date_format('Ymd', $setData['processDt']),
            'THNG_SUP_AMT'   => $setData['supplyPrice'],
            'THNG_TX'        => $setData['taxPrice'],
            'THNG_NM'        => trim(iconv('UTF-8', 'EUC-KR//IGNORE', $getData['orderGoodsNm'])),
        ];

        \Logger::channel('service')->info('고도빌전송' , [iconv('EUC-KR', 'UTF-8',(serialize($arrData)))]);
        // 고도빌로 전송
        $result = $this->sendGodobillHttp($arrData);

        if (substr($result, 0, 4) == 'DONE') {
            return [
                substr($result, 0, 4),
                __('고도빌 전송 완료'),
                substr($result, 4),
            ];
        } else if (substr($result, 0, 5) == 'ERROR') {
            return [
                substr($result, 0, 5),
                substr($result, 5),
                '',
            ];
        } else {
            return [
                'UNKNOW',
                __('알수없는 오류'),
                '',
            ];
        }
    }

    /**
     * 고도빌 전송
     *
     * @param string $arrData 세금계산서 정보
     * @return string 결과값
     */
    protected function sendGodobillHttp($arrData)
    {
        $xxtea  = new XXTEA();
        $xxtea->setKey($this->godobillEncKey);

        $requestPost = [
            'request' =>  base64_encode($xxtea->encrypt(serialize($arrData))) ,
            'id'      => $this->taxConf['godobillSiteId'],
            'api_key' => $this->taxConf['godobillApiKey'],
        ];

        $url = 'https://' . $this->godobillServer . ':444/gate/add_taxinvoice.php';
        $result = HttpUtils::remotePost($url, $requestPost);

        return $result;
    }

    /**
     * 세금계산서 엑셀 다운로드
     *
     * @param array $getValue 검색조건
     * @return array 검색데이터
     */
    public function getDataExcel($getValue = [])
    {
        $data = $this->getListTaxInvoice($getValue);

        return $data['data'];
    }

    /**
     * 세금계산서 입력 정보 저장
     *
     * @param array $taxData 입력 정보
     */
    public function saveMemberTaxInvoiceInfo($taxData = null)
    {
        // 입력 데이터
        if (empty($taxData)) {
            $postValue = \Request::post()->xss()->toArray();

            $taxData['memNo'] = \Session::get('member.memNo');
            $taxData['taxBusiNo'] = $postValue['taxBusiNo'];
            $taxData['company'] = $postValue['taxCompany'];
            $taxData['ceo'] = $postValue['taxCeoNm'];
            $taxData['service'] = $postValue['taxService'];
            $taxData['item'] = $postValue['taxItem'];
            $taxData['comZonecode'] = $postValue['taxZonecode'];
            $taxData['comZipcode'] = $postValue['taxZipcode'];
            $taxData['comAddress'] = $postValue['taxAddress'];
            $taxData['comAddressSub'] = $postValue['taxAddressSub'];
            if ($postValue['taxEmail'] == '' && $postValue['orderEmail']) {
                $taxData['email'] = $postValue['orderEmail'];
            } else if (gd_isset($postValue['taxEmail'])) {
                $taxData['email'] = is_array($postValue['taxEmail']) ? implode('@', $postValue['taxEmail']) : $postValue['taxEmail'];
            }
        }

        if ($this->checkMemberTaxInvoiceInfo($taxData['memNo'])) {
            $arrBind = $this->db->get_binding(DBTableField::tableMemberInvoiceInfo(), $taxData, 'update', array_keys($taxData), ['memNo']);
            $this->db->bind_param_push($arrBind['bind'], 'i', $taxData['memNo']);
            $this->db->set_update_db(DB_MEMBER_INVOICE_INFO, $arrBind['param'], 'memNo = ?', $arrBind['bind']);
        } else {
            $arrBind = $this->db->get_binding(DBTableField::tableMemberInvoiceInfo(), $taxData, 'insert', array_keys($taxData));
            $this->db->set_insert_db(DB_MEMBER_INVOICE_INFO, $arrBind['param'], $arrBind['bind'], 'y');
        }
        unset($arrBind);
    }

    /**
     * 세금계산서 입력 정보 출력
     *
     * @param integer $memNo 회원번호
     *
     * @return mixed 세금계산서 입력 정보
     */
    public function getMemberTaxInvoiceInfo($memNo)
    {
        if (gd_isset($memNo) == null) {
            return false;
        }

        $addField = 'mii.company, mii.service, mii.item, mii.taxBusiNo, mii.ceo, mii.comZipcode, mii.comZonecode, mii.comAddress, mii.comAddressSub, mii.email, mii.cashBusiNo';
        $arrBind = [];
        $this->db->strField = $addField;

        $arrWhere[] = 'mii.memNo = ? ';
        $this->db->bind_param_push($arrBind, 'i', $memNo);
        $this->db->strWhere = implode(' AND ', gd_isset($arrWhere));

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER_INVOICE_INFO . ' AS mii ' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $arrBind);
        $data = ArrayUtils::removeEmpty($data[0]);

        // 회원정보
        $member = \App::load('\\Component\\Member\\Member');
        $memInfo = ArrayUtils::ObjectToArray($member->getMemberInfo());

        // 세금계산서 입력 정보 없을 경우, 가입된 사업자 정보 또는 지출증빙용 사업자 번호 가져오기
        if (empty($data) || (count($data) == 1 && ArrayUtils::firstKey($data) == 'cashBusiNo')) {
            $data['taxBusiNo'] = $data['cashBusiNo'];
            if ($memInfo['memberFl'] == 'business') {
                $data['taxBusiNo'] = $memInfo['busiNo'];
                $data['company'] = $memInfo['company'];
                $data['ceo'] = $memInfo['ceo'];
                $data['service'] = $memInfo['service'];
                $data['item'] = $memInfo['item'];
                $data['comZonecode'] = $memInfo['comZonecode'];
                $data['comZipcode'] = $memInfo['comZipcode'];
                $data['comAddress'] = $memInfo['comAddress'];
                $data['comAddressSub'] = $memInfo['comAddressSub'];
                $data['memberTaxInfoFl'] = 'y';
            } else {
                $data['memberTaxInfoFl'] = 'n';
            }
        } else {
            $data['memberTaxInfoFl'] = 'y';
        }

        return gd_htmlspecialchars_stripslashes($data);
    }

    /**
     * 세금계산서 입력 정보 회원 체크
     *
     * @param integer $memNo 회원번호
     *
     * @return bool 회원유무
     */
    public function checkMemberTaxInvoiceInfo($memNo)
    {
        $arrBind = [];
        $strSQL = 'SELECT memNo FROM ' . DB_MEMBER_INVOICE_INFO . ' WHERE memNo = ? ';
        $this->db->bind_param_push($arrBind, 'i', $memNo);
        $this->db->query_fetch($strSQL, $arrBind);
        $isMemNo = $this->db->num_rows();
        unset($arrBind);

        return $isMemNo > 0;
    }
}
