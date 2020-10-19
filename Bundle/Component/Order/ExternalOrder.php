<?php

/**
 * This is commercial software, only users who have purchased a valid license
 * and accept to the terms of the License Agreement can install and use this
 * program.
 *
 * Do not edit or add to this file if you wish to upgrade Godomall5 to newer
 * versions in the future.
 *
 * @copyright ⓒ 2018, NHN godo: Corp.
 * @link http://www.godo.co.kr
 */
namespace Bundle\Component\Order;

use App;
use Component\Validator\Validator;
use Vendor\Spreadsheet\Excel\Reader as SpreadsheetExcelReader;
use Exception;
use Component\Database\DBTableField;
use Framework\Utility\NumberUtils;
use Request;
use Session;
use Framework\Utility\ArrayUtils;
use Framework\Utility\StringUtils;

/**
 * 외부채널 주문 관련 class
 *
 * @author <bumyul2000@godo.co.kr>
 */
class ExternalOrder extends \Component\Order\Order
{
    public $db;

    public $requiredSheets;

    public $orderTypeSheets;

    public $sheetsNameArr;

    public $orderTableField;

    public $orderGoodsTableField;

    public $orderDeliveryTableField;

    public $orderInfoTableField;

    public $orderGoodsMemoTableField;

    public $remoteIP;

    public $policyData;

    public $managerSno;

    public $resultData;

    public $orderTypeFailGroup;

    /**
     * 생성자
     */
    public function __construct()
    {
        parent::__construct();

        if(!is_object($this->db)){
            $this->db = App::load('DB');
        }
    }

    public function setGlobalVariable()
    {
        $this->sheetsNameArr = [
            'orderGroup' => '주문 그룹 번호',
            'apiOrderNo' => '외부채널 주문번호',
            'apiOrderGoodsNo' => '외부채널 품목고유번호',
            'goodsCd' => '자체상품코드',
            'optionCode' => '자체옵션코드',
            'goodsCnt' => '상품수량',
            'goodsSettlePrice' => '주문상품당 결제금액',
            'deliveryPolicyCharge' => '주문상품당 배송비',
            'deliveryCollectFl' => '배송비 결제방법',
            'orderName' => '주문자 이름',
            'orderEmail' => '주문자 이메일',
            'orderPhone' => '주문자 전화번호',
            'orderCellPhone' => '주문자 핸드폰 번호',
            'receiverName' => '수취인 이름',
            'receiverPhone' => '수취인 전화번호',
            'receiverCellPhone' => '수취인 핸드폰번호',
            'receiverZipcode' => '수취인 (구)우편번호',
            'receiverZonecode' => '수취인 구역번호',
            'receiverAddress' => '수취인 주소',
            'receiverAddressSub' => '수취인 나머지주소',
            'orderMemo' => '배송메시지',
            'orderPayFl' => '결제완료 여부',
            'settleKind' => '결제수단',
            'adminOrderGoodsMemo' => '주문상품별 관리자메모',
            'regDt' => '주문일',
            'paymentDt' => '결제완료일',
        ];

        $this->requiredSheets = [
            'orderGroup',
            'goodsCd',
            'goodsCnt',
            'optionCode',
            'goodsSettlePrice',
            'deliveryPolicyCharge',
            'orderName',
            'orderEmail',
            'orderCellPhone',
            'receiverName',
            'receiverCellPhone',
            'receiverZonecode',
            'receiverAddress',
            'receiverAddressSub',
        ];

        $this->orderTypeSheets = [
            'apiOrderNo',
            'deliveryCollectFl',
            'orderName',
            'orderEmail',
            'orderPhone',
            'orderCellPhone',
            'receiverName',
            'receiverPhone',
            'receiverCellPhone',
            'receiverZipcode',
            'receiverZonecode',
            'receiverAddress',
            'receiverAddressSub',
            'orderMemo',
            'orderPayFl',
            'settleKind',
            'regDt',
            'paymentDt',
        ];

        $this->orderTableField = DBTableField::tableOrder();
        $this->orderGoodsTableField = DBTableField::tableOrderGoods();
        $this->orderDeliveryTableField = DBTableField::tableOrderDelivery();
        $this->orderInfoTableField = DBTableField::tableOrderInfo();
        $this->orderGoodsMemoTableField = DBTableField::tableAdminOrderGoodsMemo();

        $this->remoteIP = Request::getRemoteAddress();
        $this->managerSno = Session::get('manager.sno');

        $this->policyData = [
            // 예치금 정책
            'depositPolicy' => json_encode(gd_policy('member.depositConfig'), JSON_UNESCAPED_UNICODE),
            // 마일리지 정책
            'mileagePolicy' => json_encode(gd_mileage_give_info(), JSON_UNESCAPED_UNICODE),
            // 주문상태 정책
            'statusPolicy' => json_encode($this->getOrderStatusPolicy(), JSON_UNESCAPED_UNICODE),
            // 쿠폰정책
            'couponPolicy' => json_encode(gd_policy('coupon.config'), JSON_UNESCAPED_UNICODE),
        ];
    }

    /**
     * 주문상태, 배송정보 변경
     *
     * @param array $arrData
     *
     * @return array $returnData
     */
    public function updateOrderStatusDelivery($arrData)
    {
        //송장번호 업데이트
        if($arrData['orderInvoiceUpdateFl'] === 'y'){
            $invoiceDataArr = $arrData;
            // 체크된 상품별 송장 처리 데이터 처리 (입금, 상품, 배송, 교환추가 상태에 속하지 않은 경우 송장번호 등록방지)
            if(count($arrData['statusMode']) > 0){
                foreach ($arrData['statusMode'] as $key => $val) {
                    if (!in_array(substr($key, 0, 1), explode(',', 'p,g,d,z'))) {
                        unset($invoiceDataArr['statusCheck'][$key]);
                    }
                }
            }

            $this->saveDeliveryInvoice($invoiceDataArr);
        }

        //주문상태 업데이트
        if($arrData['orderStatusUpdateFl'] === 'y'){
            $this->updateOrderStatus($arrData, '리스트에서');
        }
    }

    /**
     * 상품준비중 리스트에서 송장번호 일괄 업데이트 처리 & 주문상세에서 송장번호 일괄변경 업데이트 처리
     *
     * @author <bumyul2000@godo.co.kr>
     *
     * @param array $arrData 주문리스트 요청 데이터
     */
    public function saveDeliveryInvoice($arrData)
    {
        // 체크된 상품별 송장 처리 데이터 처리
        $orderGoodsData = [];
        foreach ($arrData['statusCheck'] as $statusMode => $data) {
            foreach ($data as $orderDatas) {
                $orderData = explode(INT_DIVISION, $orderDatas);
                $orderNo = $orderData[0];
                $orderGoodsNo = $orderData[1];

                $orderGoodsData['sno'][] = $orderGoodsNo;
                $orderGoodsData['invoiceCompanySno'][] = $arrData['invoiceCompanySno'][$statusMode][$orderGoodsNo];
                $orderGoodsData['invoiceNo'][] = StringUtils::xssClean($arrData['invoiceNo'][$statusMode][$orderGoodsNo]);
                $orderGoodsData['invoiceDt'][] = date('Y-m-d H:i:s');

                // 주문 상태 변경 처리
                $arrDataKey = ['orderNo' => $orderNo];

                // 주문 상품 정보 (수량 및 송장번호) 수정
                if (empty($orderGoodsData) === false) {
                    $compareField = array_keys($orderGoodsData);
                    $getGoods = $this->getOrderGoods($orderNo, $orderGoodsNo);
                    $compareGoods = $this->db->get_compare_array_data($getGoods, gd_isset($orderGoodsData), false, $compareField);
                    $this->db->set_compare_process(DB_ORDER_GOODS, gd_isset($orderGoodsData), $arrDataKey, $compareGoods, $compareField);
                }
            }
        }
    }

    /*
     * 주문상태 변경
     *
     * @param array $arrData
     * @param string $reason 사유
     *
     * @return void
     */
    public function updateOrderStatus($arrData, $reason = '', $authCheck = true)
    {
        if($authCheck === true){
            // 운영자 기능권한 처리 (주문 상태 변경 권한) - 관리자페이지에서만
            $thisCallController = \App::getInstance('ControllerNameResolver')->getControllerRootDirectory();
            if ($thisCallController == 'admin' && Session::get('manager.functionAuthState') == 'check' && Session::get('manager.functionAuth.orderState') != 'y') {
                throw new Exception(__('권한이 없습니다. 권한은 대표운영자에게 문의하시기 바랍니다.'));
            }
        }

        // 주문 정보 확인
        if (empty($arrData['statusCheck']) === true) {
            throw new Exception(__('[리스트] 주문 정보가 존재하지 않습니다.'));
        }

        // 체크된 상품의 값을 주문번호와 상품번호로 분리
        foreach ($arrData['statusCheck'] as $statusMode => $sVal) {
            foreach ($sVal as $val) {
                $tmpArr = explode(INT_DIVISION, $val);
                if (isset($tmpArr[1]) === true) {
                    $statusCode[$statusMode][$tmpArr[0]][] = $tmpArr[1];
                } else {
                    $statusCode[$statusMode][$tmpArr[0]][] = null;
                }
            }
        }

        if (empty($statusCode) === false) {
            foreach ($statusCode as $statusMode => $orderNos) {
                foreach ($orderNos as $orderNo => $arrGoodsNo) {
                    // 주문상품별이 아닌 주문별로 일괄 처리가 필요한 경우 (ex. 주문접수, 주문취소, 결제실패)
                    $arrGoodsNo = ArrayUtils::removeEmpty($arrGoodsNo);
                    if (empty($arrGoodsNo) === true) {
                        $arrGoodsNo = null;
                    }

                    // 주문 상태, 실패일경우 현재 상태 수정 처리, 및 취소에서 주문 상태 처리시에 현재 상태 수정 처리 가능하게
                    if ($statusMode == 'p' || $statusMode == 'o' || $statusMode == 'f' || ($statusMode == 'c' && substr($arrData['changeStatus'], 0, 1) == 'o')) {
                        $bundleFl = true;
                    } else {
                        $bundleFl = false;
                    }

                    $orderData = $this->getOrderData($orderNo);
                    $orderGoodsData = $this->getOrderGoods($orderNo, $arrGoodsNo, null, ['orderStatus']);

                    // 주문상태별 조건에 따른 변경 가능여부 체크
                    foreach ($orderGoodsData as $key => $val) {
                        if ($val['orderStatus'] != $arrData['changeStatus']) {
                            // 기본상태변경 조건이 맞아 true로 전환
                            $changeStatusCheck = true;
                        }
                        else {
                            // 주문상태가 같은 경우 변경 안됨
                            $changeStatusCheck = false;
                        }

                        if (substr($val['orderStatus'], 0, 1) == 'e' && substr($arrData['changeStatus'], 0, 1) != 'e') {
                            $changeStatusCheck = false;
                        }
                        if (substr($val['orderStatus'], 0, 1) == 'z' && substr($arrData['changeStatus'], 0, 1) != 'z') {
                            $changeStatusCheck = false;
                        }
                        // 환불완료/취소/실패인 경우 무조건 상태변경 금지
                        if ($val['orderStatus'] == 'r3' || $val['orderStatus'] == 'c' || $val['orderStatus'] == 'f') {
                            $changeStatusCheck = false;
                        }

                        // 변경 가능한 경우 bundleData로 담는다
                        if ($changeStatusCheck == true) {
                            $bundleData['sno'][] = $val['sno'];
                            $bundleData['orderStatus'][] = $val['orderStatus'];
                            $changeCheck = true;
                        }
                    }

                    if ($changeCheck === true){
                        $bundleData['changeStatus'] = $arrData['changeStatus'];
                        if(trim($reason) !== ''){
                            $reason = $reason . ' ';
                        }
                        $bundleData['reason'] = $reason . $this->getOrderStatusAdmin($arrData['changeStatus']) . __(' 처리');

                        // 현재 상태 수정 처리
                        $this->setStatusChange($orderNo, $bundleData);
                    }

                    unset($orderData, $orderGoodsData, $bundleData);
                }
            }
        }
    }

    /**
     * 외부채널 주문 엑셀 일괄등록
     *
     * @param array $files $_FILES
     *
     * @return array
     * @throws Exception
     */
    public function updateExternalOrderExcel($files)
    {
        set_time_limit(RUN_TIME_LIMIT);

        // --- 1. 세팅 및 데이터 체크 시작
        try {
            $delivery = App::load('\\Component\\Delivery\\DeliveryCart');
            if(!is_object($delivery)){
                throw new Exception('주문엑셀 등록을 실패하였습니다. [not exist deliveryCart component]');
            }

            $this->setGlobalVariable();

            if ($files['excel']['error'] > 0) {
                throw new Exception(__('엑셀 화일이 존재하지 않습니다. 다운 로드 받으신 엑셀파일을 이용해서 "Excel 97-2003 통합문서" 로 저장이 된 엑셀 파일만 가능합니다.'));
            }

            // 엑셀데이터 추출
            $data = new SpreadsheetExcelReader();
            $data->setOutputEncoding('UTF-8');
            $chk = $data->read($files['externalExcelFile']['tmp_name']);

            // 엑셀데이터 체크
            if ($chk === false) {
                throw new Exception(__('엑셀 화일을 확인해 주세요. 다운 로드 받으신 엑셀파일을 이용해서 "Excel 97-2003 통합문서" 로 저장이 된 엑셀 파일만 가능합니다.'));
            }

            // 반드시 Excel 97-2003 통합문서로 저장이 되어야 하며, 4번째 줄부터 데이타 입니다.
            if ($data->sheets[0]['numRows'] < 4) {
                throw new Exception(__('엑셀 화일을 확인해 주세요. 엑셀 데이타가 존재하지 않습니다. 데이타는 4번째 줄부터 작성을 하셔야 합니다.'));
            }

            // 1,000건 넘길 시 예외 처리
            if ($data->sheets[0]['numRows'] > 1004) {
                throw new Exception(__('주문 입력데이터는 1,000건 이상 처리할 수 없습니다.'));
            }

            // 엑셀 저장(저장 실패할경우 무시)
            if(is_uploaded_file($files['excel']['tmp_name'])) {
                $time = time();
                $saveFileNm = sprintf('externalOrder_%s', $time).'.xls';
                $savePath =  UserFilePath::data('excel', 'externalOrder')->getRealPath();
                $saveFullPath = $savePath.DS.$saveFileNm;
                if(!is_dir($savePath)){
                    mkdir($savePath,0707);
                }
                if(file_exists($saveFullPath)){
                    $saveFileNm = sprintf('externalOrder_%s', $time).'_'.date('Hms').'.xls';
                    $saveFullPath = $savePath.DS.$saveFileNm;
                }
                \FileHandler::copy($files['excel']['tmp_name'], $saveFullPath, 0707);
            }

            $cnt = count($data->sheets[0]['cells']);
        }
        catch(Exception $e){
            return [
                'returnCode' => 'fail',
                'returnData' => $e->getMessage(),
            ];
        }
        // --- 1. 세팅 및 데이터 체크 끝


        // --- 2. 엑셀 데이터 추출 및 등록 데이터 정리 시작
        $orderFirstGroup = $insertData = [];

        for ($i = 4; $i <= $cnt; $i++) {
            $getData = [
                // 주문 그룹 번호
                'orderGroup' => trim($data->sheets[0]['cells'][$i][1]),
                // 외부채널 주문번호
                'apiOrderNo' => trim($data->sheets[0]['cells'][$i][2]),
                // 외부채널 품목고유번호
                'apiOrderGoodsNo' => trim($data->sheets[0]['cells'][$i][3]),
                // 자체상품코드
                'goodsCd' => trim($data->sheets[0]['cells'][$i][4]),
                // 자체옵션코드
                'optionCode' => trim($data->sheets[0]['cells'][$i][5]),
                // 상품수량
                'goodsCnt' => ((int)trim($data->sheets[0]['cells'][$i][6]) === 0) ? '' : trim($data->sheets[0]['cells'][$i][6]),
                // 주문상품당 결제금액
                'goodsSettlePrice' => trim($data->sheets[0]['cells'][$i][7]),
                // 주문상품당 배송비
                'deliveryPolicyCharge' => trim($data->sheets[0]['cells'][$i][8]),
                // 배송비 결제방법
                'deliveryCollectFl' => trim($data->sheets[0]['cells'][$i][9]),
                // 주문자 이름
                'orderName' => trim(preg_replace('/[^\x{1100}-\x{11FF}\x{3130}-\x{318F}\x{AC00}-\x{D7AF}a-zA-Z]/u', "", $data->sheets[0]['cells'][$i][10])),
                // 주문자 이메일
                'orderEmail' => trim($data->sheets[0]['cells'][$i][11]),
                // 주문자 전화번호
                'orderPhone' => trim($data->sheets[0]['cells'][$i][12]),
                // 주문자 핸드폰 번호
                'orderCellPhone' => trim($data->sheets[0]['cells'][$i][13]),
                // 수취인 이름
                'receiverName' => trim(preg_replace('/[^\x{1100}-\x{11FF}\x{3130}-\x{318F}\x{AC00}-\x{D7AF}a-zA-Z]/u', "", $data->sheets[0]['cells'][$i][14])),
                // 수취인 전화번호
                'receiverPhone' => trim($data->sheets[0]['cells'][$i][15]),
                // 수취인 핸드폰번호
                'receiverCellPhone' => trim($data->sheets[0]['cells'][$i][16]),
                // 수취인 (구)우편번호
                'receiverZipcode' => trim($data->sheets[0]['cells'][$i][17]),
                // 수취인 우편번호 (구역번호)
                'receiverZonecode' => trim($data->sheets[0]['cells'][$i][18]),
                // 수취인 주소
                'receiverAddress' => trim($data->sheets[0]['cells'][$i][19]),
                // 수취인 나머지주소
                'receiverAddressSub' => trim($data->sheets[0]['cells'][$i][20]),
                // 배송메시지
                'orderMemo' => trim($data->sheets[0]['cells'][$i][21]),
                // 결제완료 여부
                'orderPayFl' => trim($data->sheets[0]['cells'][$i][22]),
                // 결제수단
                'settleKind' => (trim($data->sheets[0]['cells'][$i][23]) === '') ? 'gb' : trim($data->sheets[0]['cells'][$i][23]),
                // 주문상품별 관리자메모
                'adminOrderGoodsMemo' => trim(preg_replace('/\r\n|\r|\n/', PHP_EOL, $data->sheets[0]['cells'][$i][24])),
                // 주문일
                'regDt' => trim($data->sheets[0]['cells'][$i][25]),
                // 결제완료일
                'paymentDt' => trim($data->sheets[0]['cells'][$i][26]),
            ];

            if(trim($orderFirstGroup[$getData['orderGroup']]['orderNo']) !== ''){
                //동일그룹의 첫번째 주문상품건 데이터가 있다면 해당 데이터로 주문서 정보 교체
                foreach($this->orderTypeSheets as $key => $sheetName){
                    $getData[$sheetName] = $orderFirstGroup[$getData['orderGroup']][$sheetName];
                }
            }

            // 주문 등록 실행 여부
            $isRun = true;
            // 에러메시지
            $errorMessage = '';

            if(count($this->orderTypeFailGroup[$getData['orderGroup']]) > 0){
                // 주문항목에서 오류가 났을 경우 나머지 주문상품들도 등록 불가
                $isRun = false;
                $errorMessage = $this->orderTypeFailGroup[$getData['orderGroup']]['errorMessage'];
            }
            else {
                // 유효성체크
                list($isRun, $errorMessage) = $this->checkExcelSheets($getData);
            }

            if ($isRun === true) {
                try {
                    // 상품정보
                    $goodsData = $this->getGoodsData($getData['goodsCd']);
                    // 상품옵션정보
                    $goodsOptionData = [];
                    if($goodsData['optionFl'] === 'y'){
                        if(trim($getData['optionCode']) === '' || trim($getData['optionCode']) === '옵션없음'){
                            $this->setResultData('', $getData['orderGroup'], $getData['goodsCd'], $getData['optionCode'], '실패', "필수값 미입력 : " . $this->sheetsNameArr['optionCode']);
                            throw new Exception();
                        }
                        else {
                            $goodsOptionData = $this->getGoodsOptionData($getData['optionCode']);
                        }
                    }

                    // 주문번호 생성
                    if(trim($orderFirstGroup[$getData['orderGroup']]['orderNo']) === ''){
                        while(1){
                            if(trim($getData['regDt']) !== ''){
                                $orderNo = self::generateOrderNo(preg_replace("/[^\d]/i", '', substr($getData['regDt'], 2)));
                            }
                            else {
                                $orderNo = parent::generateOrderNo();
                            }
                            if(count($insertData['orderGoods'][$orderNo]) < 1){
                                break;
                            }
                        }

                        $orderFirstGroup[$getData['orderGroup']] = $getData;
                        $orderFirstGroup[$getData['orderGroup']]['orderNo'] = $orderNo;
                    }
                    else {
                        $orderNo = $orderFirstGroup[$getData['orderGroup']]['orderNo'];
                    }

                    // 주문일, 결제완료일 재정의
                    if($getData['orderPayFl'] === 'y' && trim($getData['paymentDt']) === ''){
                        $nowDateTime = date("Y-m-d H:i:s");
                        if(trim($getData['regDt']) === ''){
                            $getData['regDt'] = $nowDateTime;
                            $getData['paymentDt'] = $nowDateTime;
                        }
                        else {
                            $getData['paymentDt'] = $getData['regDt'];
                        }
                    }

                    // orderCd 정의
                    $orderCd = (count($insertData['orderGoods'][$orderNo]) + 1);

                    // 주문상품 데이터
                    $insertData['orderGoods'][$orderNo][$orderCd] = $this->getInsertOrderGoodsData($orderNo, $orderCd, $getData, $goodsData, $goodsOptionData);
                    // 주문배송 데이터
                    $getDeliveryInfo = $delivery->getDataDeliveryWithGoodsNo([$goodsData['deliverySno']]);
                    $insertData['orderDelivery'][$orderNo][$orderCd] = $this->getInsertOrderDeliveryData($orderNo, $getDeliveryInfo, $getData, $goodsData);
                    // 주문정보 데이터
                    $insertData['orderInfo'][$orderNo] = $this->getInsertOrderInfoData($orderNo, $getData);
                    // 주문 데이터
                    $insertData['order'][$orderNo] = $this->getInsertOrderData($orderNo, $getData);
                    // 주문상품별 메모
                    if(trim($getData['adminOrderGoodsMemo']) !== ''){
                        $insertData['orderGoodsMemo'][$orderNo][$orderCd] = $this->getInsertOrderGoodsMemoData($orderNo, $getData);
                    }

                    unset($goodsData, $goodsOptionData);
                }
                catch (Exception $e) {

                }
            }
            else {
                $this->setResultData('', $getData['orderGroup'], $getData['goodsCd'], $getData['optionCode'], '실패', $errorMessage);
            }
        }
        // --- 2. 엑셀 데이터 추출 및 등록 데이터 정리 끝

        // --- 3. 주문 등록 시작
        if(count($insertData['orderGoods']) > 0){

            foreach($insertData['orderGoods'] as $orderNo => $orderGoodsDataArray){
                $this->db->begin_tran();
                try {
                    if(count($orderGoodsDataArray) > 0){
                        $firstGoodsNm = '';

                        foreach($orderGoodsDataArray as $orderCd => $orderGoodsData){
                            // 주문배송 등록
                            $orderDeliveryData = $insertData['orderDelivery'][$orderNo][$orderCd];
                            $orderDeliverySno = $this->insertDataOrderDelivery($orderDeliveryData);
                            if(!$orderDeliverySno){
                                throw new Exception('주문배송 등록 실패');
                            }

                            // 주문상품 등록
                            $orderGoodsInsertId = $this->insertDataOrderGoods($orderGoodsData, $orderDeliverySno);
                            if(!$orderGoodsInsertId){
                                throw new Exception('주문상품 등록 실패');
                            }

                            // 주문상품별 메모 등록
                            if(count($insertData['orderGoodsMemo'][$orderNo][$orderCd]) > 0){
                                $orderGoodsMemoInsertId = $this->insertDataOrderGoodsMemo($insertData['orderGoodsMemo'][$orderNo][$orderCd], $orderGoodsInsertId);
                                if(!$orderGoodsMemoInsertId){
                                    throw new Exception('주문상품메모 등록 실패');
                                }
                            }

                            // 주문데이터 취합
                            if($firstGoodsNm === ''){
                                $firstGoodsNm = $orderGoodsData['goodsNm'];
                            }
                            $insertData['order'][$orderNo]['orderGoodsCnt'] += 1;
                            $insertData['order'][$orderNo]['settlePrice'] += array_sum([
                                $orderGoodsData['taxSupplyGoodsPrice'],
                                $orderGoodsData['taxVatGoodsPrice'],
                                $orderGoodsData['taxFreeGoodsPrice'],
                                $orderDeliveryData['taxSupplyDeliveryCharge'],
                                $orderDeliveryData['taxVatDeliveryCharge'],
                                $orderDeliveryData['taxFreeDeliveryCharge'],
                            ]);
                            $insertData['order'][$orderNo]['taxSupplyPrice'] += ($orderGoodsData['taxSupplyGoodsPrice'] + $orderDeliveryData['taxSupplyDeliveryCharge']);
                            $insertData['order'][$orderNo]['taxVatPrice'] += ($orderGoodsData['taxVatGoodsPrice'] + $orderDeliveryData['taxVatDeliveryCharge']);
                            $insertData['order'][$orderNo]['taxFreePrice'] += ($orderGoodsData['taxFreeGoodsPrice'] + $orderDeliveryData['taxFreeDeliveryCharge']);
                            $insertData['order'][$orderNo]['realTaxSupplyPrice'] += ($orderGoodsData['taxSupplyGoodsPrice'] + $orderDeliveryData['taxSupplyDeliveryCharge']);
                            $insertData['order'][$orderNo]['realTaxVatPrice'] += ($orderGoodsData['taxVatGoodsPrice'] + $orderDeliveryData['taxVatDeliveryCharge']);
                            $insertData['order'][$orderNo]['realTaxFreePrice'] += ($orderGoodsData['taxFreeGoodsPrice'] + $orderDeliveryData['taxFreeDeliveryCharge']);
                            $insertData['order'][$orderNo]['totalGoodsPrice'] += (($orderGoodsData['goodsPrice'] + $orderGoodsData['optionPrice']) * $orderGoodsData['goodsCnt']);
                            $insertData['order'][$orderNo]['totalDeliveryCharge'] += array_sum([
                                $orderDeliveryData['taxSupplyDeliveryCharge'],
                                $orderDeliveryData['taxVatDeliveryCharge'],
                                $orderDeliveryData['taxFreeDeliveryCharge'],
                            ]);
                        }

                        //주문정보 등록
                        $orderInfoSno = $this->insertDataOrderInfo($insertData['orderInfo'][$orderNo]);
                        if(!$orderInfoSno){
                            throw new Exception('주문정보 등록 실패');
                        }

                        // 주문 등록
                        $orderGoodsCount = count($insertData['orderGoods'][$orderNo]);
                        $orderGoodsNm = $firstGoodsNm . ($orderGoodsCount > 1 ? __(' 외 ') . ($orderGoodsCount - 1) . __(' 건') : '');
                        $insertData['order'][$orderNo]['orderGoodsNm'] = $insertData['order'][$orderNo]['orderGoodsNmStandard'] = $orderGoodsNm;
                        $this->insertDataOrder($insertData['order'][$orderNo]);

                        foreach($orderGoodsDataArray as $key => $orderGoods){
                            $this->setResultData($orderNo, $orderGoods['tmpOrderGroup'], $orderGoods['goodsCd'], $orderGoods['tmpOptionCode'], '성공', '');
                        }
                    }
                }
                catch (Exception $e) {
                    if(count($orderGoodsDataArray) > 0){
                        foreach($orderGoodsDataArray as $key => $orderGoods){
                            $this->setResultData($orderNo, $orderGoods['tmpOrderGroup'], $orderGoods['goodsCd'], $orderGoods['tmpOptionCode'], '실패', $e->getMessage());
                        }
                    }
                    $this->db->rollback();
                }
                $this->db->commit();
            }
        }
        // --- 3. 주문 등록 끝

        // --- 4. 결과 리턴 및  엑셀파일로 출력 시작
        $returnData = [
            'returnCode' => 'success',
            'returnData' => $this->resultData,
        ];

        return $returnData;
        // --- 4. 결과 리턴 및 엑셀파일로 출력 끝
    }

    /**
     * 엑셀 항목들의 필수여부 및 형식 체크
     *
     * @param array $getData 엑셀데이터
     *
     * @return array
     */
    public function checkExcelSheets($getData)
    {
        try {
            // 필수값 체크
            foreach($this->requiredSheets as $key => $value){
                if(Validator::required($getData[$value]) === false){
                    if(in_array($value, $this->orderTypeSheets)){
                        throw new Exception('필수값 미입력 : ' . $this->sheetsNameArr[$value], 1);
                    }
                    else {
                        throw new Exception('필수값 미입력 : ' . $this->sheetsNameArr[$value]);
                    }
                    break;
                }
            }

            // 자체상품코드 체크
            if(Validator::required($getData['goodsCd']) === true){
                $count = $this->db->getCount(DB_GOODS, 1, "WHERE goodsCd = '".$getData['goodsCd']."'");
                if((int)$count < 1){
                    throw new Exception('존재하지 않는 ' . $this->sheetsNameArr['goodsCd'] . '입니다.');
                }
                if((int)$count > 1){
                    throw new Exception('동일한 ' . $this->sheetsNameArr['goodsCd'] . '를 가진 상품이 ' . $count . '개 존재합니다.');
                }
            }

            // 자체옵션코드 체크
            if(Validator::required($getData['optionCode']) === true && $getData['optionCode'] !== '옵션없음'){
                $count = $this->db->getCount(DB_GOODS_OPTION, 1, "WHERE optionCode = '".$getData['optionCode']."'");
                if((int)$count < 1){
                    throw new Exception('존재하지 않는 ' . $this->sheetsNameArr['optionCode'] . '입니다.');
                }
                if((int)$count > 1){
                    throw new Exception('동일한 ' . $this->sheetsNameArr['optionCode'] . '를 가진 옵션이 ' . $count . '개 존재합니다.');
                }
            }

            // 배송비 결제방법 체크
            if(Validator::required($getData['deliveryCollectFl']) === true){
                if($getData['deliveryCollectFl'] !== 'p' && $getData['deliveryCollectFl'] !== 'l'){
                    throw new Exception($this->sheetsNameArr['deliveryCollectFl'] . ' : p 이거나 l 이어야 합니다.', 1);
                }
            }

            // 결제수단 체크
            if(Validator::required($getData['settleKind']) === true){
                if(!in_array($getData['settleKind'], ['gb', 'ph', 'pc', 'pb', 'pv', 'gz'])){
                    throw new Exception($this->sheetsNameArr['settleKind'] . ' : 존재하지 않는 결제수단 입니다.', 1);
                }
            }

            $validator = new Validator();
            $validator->add('orderGroup', 'number', false, $this->sheetsNameArr['orderGroup'] . ' : 숫자로 구성되어야 합니다.');
            $validator->add('goodsCnt', 'number', false, $this->sheetsNameArr['goodsCnt'] . ' : 숫자로 구성되어야 합니다.');
            $validator->add('goodsCnt', 'mbMaxlen', false, $this->sheetsNameArr['goodsCnt'] . ' : 10자이상 입력 할 수 없습니다.', 10);
            $validator->add('optionCode', 'mbMaxlen', false, $this->sheetsNameArr['optionCode'] . ' : 64자이상 입력 할 수 없습니다.', 64);
            $validator->add('goodsSettlePrice', 'number', false, $this->sheetsNameArr['goodsSettlePrice'] . ' : 숫자로 구성되어야 합니다.');
            $validator->add('goodsSettlePrice', 'mbMaxlen', false, $this->sheetsNameArr['goodsSettlePrice'] . ' : 10자이상 입력 할 수 없습니다.', 10);
            $validator->add('deliveryPolicyCharge', 'number', false, $this->sheetsNameArr['deliveryPolicyCharge'] . ' : 숫자로 구성되어야 합니다.');
            $validator->add('deliveryPolicyCharge', 'mbMaxlen', false, $this->sheetsNameArr['deliveryPolicyCharge'] . ' : 10자이상 입력 할 수 없습니다.', 10);
            $validator->add('orderName', 'mbMaxlen', false, $this->sheetsNameArr['orderName'] . ' : 20자이상 입력 할 수 없습니다.', 20);
            $validator->add('orderEmail', 'email', false, $this->sheetsNameArr['orderEmail'] . ' : email 형식이 아닙니다.');
            $validator->add('orderEmail', 'mbMaxlen', false, $this->sheetsNameArr['orderEmail'] . ' : 100자이상 입력 할 수 없습니다.', 100);
            $validator->add('orderPhone', 'mbMaxlen', false, $this->sheetsNameArr['orderPhone'] . ' : 20자이상 입력 할 수 없습니다.', 20);
            $validator->add('orderCellPhone', 'mbMaxlen', false, $this->sheetsNameArr['orderCellPhone'] . ' : 20자이상 입력 할 수 없습니다.', 20);
            $validator->add('receiverName', 'mbMaxlen', false, $this->sheetsNameArr['receiverName'] . ' : 20자이상 입력 할 수 없습니다.', 20);
            $validator->add('receiverPhone', 'mbMaxlen', false, $this->sheetsNameArr['receiverPhone'] . ' : 20자이상 입력 할 수 없습니다.', 20);
            $validator->add('receiverCellPhone', 'mbMaxlen', false, $this->sheetsNameArr['receiverCellPhone'] . ' : 20자이상 입력 할 수 없습니다.', 20);
            $validator->add('receiverZipcode', 'mbMaxlen', false, $this->sheetsNameArr['receiverZipcode'] . ' : 7자이상 입력 할 수 없습니다.', 7);
            $validator->add('receiverZonecode', 'number', false, $this->sheetsNameArr['receiverZonecode'] . ' : 숫자로 구성되어야 합니다.');
            $validator->add('receiverZonecode', 'mbMaxlen', false, $this->sheetsNameArr['receiverZonecode'] . ' : 5자이상 입력 할 수 없습니다.', 5);
            $validator->add('receiverAddress', 'mbMaxlen', false, $this->sheetsNameArr['receiverAddress'] . ' : 45자이상 입력 할 수 없습니다.', 45);
            $validator->add('receiverAddressSub', 'mbMaxlen', false, $this->sheetsNameArr['receiverAddressSub'] . ' : 100자이상 입력 할 수 없습니다.', 100);
            $validator->add('orderMemo', 'mbMaxlen', false, $this->sheetsNameArr['orderMemo'] . ' : 500자이상 입력 할 수 없습니다.', 500);
            $validator->add('orderPayFl', 'yn', false, $this->sheetsNameArr['orderPayFl'] . ' : y 이거나 n 이어야 합니다.');
            $validator->add('adminOrderGoodsMemo', 'mbMaxlen', false, $this->sheetsNameArr['adminOrderGoodsMemo'] . ' : 500자이상 입력 할 수 없습니다.', 500);
            $validator->add('regDt', 'datetime', false, $this->sheetsNameArr['regDt'] . ' : 날짜시간형식(YYYY-MM-DD HH:II:SS)에 맞지 않습니다.');
            $validator->add('paymentDt', 'datetime', false, $this->sheetsNameArr['paymentDt'] . ' : 날짜시간형식(YYYY-MM-DD HH:II:SS)에 맞지 않습니다.');
            if ($validator->act($getData, true) === false) {
                $validatorArray = array_values($validator->errors);
                $validatorKeyArray = array_Keys($validator->errors);

                if(in_array($validatorKeyArray[0], $this->orderTypeSheets)){
                    throw new Exception($validatorArray[0], 1);
                }
                else {
                    throw new Exception($validatorArray[0]);
                }
            }

            return [true, ''];
        }
        catch (Exception $e) {
            if((int)$e->getCode() === 1){
                $this->orderTypeFailGroup[$getData['orderGroup']] = [
                    'orderGroup' => $getData['orderGroup'],
                    'errorMessage' => $e->getMessage(),
                ];
            }

            return [false, $e->getMessage()];
        }
    }

    /*
     * 상품 데이터 select
     *
     * @param string $goodsCd 자체상품코드
     *
     * @return array $getData
     */
    public function getGoodsData($goodsCd)
    {
        $arrBind = [];
        $this->db->strField = "*";
        $this->db->strWhere = 'goodsCd = ?';
        $this->db->bind_param_push($arrBind, 's', $goodsCd);
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_GOODS . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind, false);

        unset($arrBind, $strSQL);

        return $getData;
    }

    /*
     * 상품옵션 데이터 select
     *
     * @param string $optionCode 자체옵션코드
     *
     * @return array $getData
     */
    public function getGoodsOptionData($optionCode)
    {
        $arrBind = [];
        $this->db->strField = "*";
        $this->db->strWhere = 'optionCode = ?';
        $this->db->bind_param_push($arrBind, 's', $optionCode);
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_GOODS_OPTION . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind, false);

        unset($arrBind, $strSQL);

        return $getData;
    }

    /*
     * 등록될 주문상품 데이터 취합
     *
     * @param string $orderNo 주문번호
     * @param integer $orderCd 주문상품순서
     * @param array $getData 데이터취합을 위한 필요데이터
     * @param array $goodsData 상품정보
     * @param array $goodsOptionData 옵션데이터
     *
     * @return array $tmpInsertOrderGoodsSqlArr
     */
    public function getInsertOrderGoodsData($orderNo, $orderCd, $getData, $goodsData, $goodsOptionData)
    {
        $tmpInsertOrderGoodsSqlArr = [];
        $tmpInsertOrderGoodsSqlArr['tmpOrderGroup'] = $getData['orderGroup']; // 결과값을 저장하기위해 orderGroup 번호를 가지고 있는다.
        $tmpInsertOrderGoodsSqlArr['tmpOptionCode'] = $getData['optionCode']; // 결과값을 저장하기위해 optionCode 번호를 가지고 있는다.
        $tmpInsertOrderGoodsSqlArr['orderNo'] = $orderNo;
        $tmpInsertOrderGoodsSqlArr['mallSno'] = 1;
        $tmpInsertOrderGoodsSqlArr['apiOrderGoodsNo'] = $getData['apiOrderGoodsNo'];
        $tmpInsertOrderGoodsSqlArr['orderCd'] = $orderCd;
        $tmpInsertOrderGoodsSqlArr['orderStatus'] = ($getData['orderPayFl'] === 'y') ? 'p1' : 'o1';
        $tmpInsertOrderGoodsSqlArr['purchaseNo'] = $goodsData['purchaseNo'];
        $tmpInsertOrderGoodsSqlArr['goodsNo'] = $goodsData['goodsNo'];
        $tmpInsertOrderGoodsSqlArr['goodsCd'] = $getData['goodsCd'];
        $tmpInsertOrderGoodsSqlArr['goodsModelNo'] = $getData['goodsModelNo'];
        $tmpInsertOrderGoodsSqlArr['goodsNm'] = $goodsData['goodsNm'];
        $tmpInsertOrderGoodsSqlArr['goodsNmStandard'] = $goodsData['goodsNm'];
        $tmpInsertOrderGoodsSqlArr['goodsWeight'] = $goodsData['goodsWeight'];
        $tmpInsertOrderGoodsSqlArr['goodsCnt'] = $getData['goodsCnt'];
        $tmpInsertOrderGoodsSqlArr['goodsPrice'] = $goodsData['goodsPrice'];

        $orderGoodsTaxPrice = NumberUtils::taxAll($getData['goodsSettlePrice'], $goodsData['taxPercent'], $goodsData['taxFreeFl']);
        if ($goodsData['taxFreeFl'] == 't') {
            $tmpInsertOrderGoodsSqlArr['taxSupplyGoodsPrice'] = gd_isset($orderGoodsTaxPrice['supply'], 0);
            $tmpInsertOrderGoodsSqlArr['taxVatGoodsPrice'] = gd_isset($orderGoodsTaxPrice['tax'], 0);
            $tmpInsertOrderGoodsSqlArr['realTaxSupplyGoodsPrice'] = gd_isset($orderGoodsTaxPrice['supply'], 0);
            $tmpInsertOrderGoodsSqlArr['realTaxVatGoodsPrice'] = gd_isset($orderGoodsTaxPrice['tax'], 0);
        }
        else {
            $tmpInsertOrderGoodsSqlArr['taxFreeGoodsPrice'] = gd_isset($orderGoodsTaxPrice['supply'], 0);
            $tmpInsertOrderGoodsSqlArr['realTaxFreeGoodsPrice'] = gd_isset($orderGoodsTaxPrice['supply'], 0);
        }
        $tmpInsertOrderGoodsSqlArr['fixedPrice'] = $goodsData['fixedPrice'];
        $tmpInsertOrderGoodsSqlArr['costPrice'] = $goodsData['costPrice'];
        $tmpInsertOrderGoodsSqlArr['goodsDeliveryCollectPrice'] = ($getData['deliveryCollectFl'] === 'l') ? $getData['deliveryPolicyCharge'] : 0;
        $tmpInsertOrderGoodsSqlArr['goodsDeliveryCollectFl'] = ($getData['deliveryCollectFl'] === 'l') ? 'later' : 'pre';
        $tmpInsertOrderGoodsSqlArr['goodsTaxInfo'] = $goodsData['taxFreeFl'] . STR_DIVISION . $goodsData['taxPercent'];
        $tmpInsertOrderGoodsSqlArr['cateCd'] = $goodsData['cateCd'];
        $tmpInsertOrderGoodsSqlArr['brandCd'] = $goodsData['brandCd'];
        $tmpInsertOrderGoodsSqlArr['makerNm'] = $goodsData['makerNm'];
        $tmpInsertOrderGoodsSqlArr['originNm'] = $goodsData['originNm'];
        $tmpInsertOrderGoodsSqlArr['hscode'] = $goodsData['hscode'];
        $tmpInsertOrderGoodsSqlArr['paymentDt'] = $getData['paymentDt'];
        $tmpInsertOrderGoodsSqlArr['regDt'] = $getData['regDt'];
        if(trim($getData['optionCode']) !== '' && trim($getData['optionCode']) !== '옵션없음'){
            $tmpInsertOrderGoodsSqlArr['optionSno'] = $goodsOptionData['sno'];
            $tmpInsertOrderGoodsSqlArr['optionPrice'] = $goodsOptionData['optionPrice'];
            $tmpInsertOrderGoodsSqlArr['optionCostPrice'] = $goodsOptionData['optionCostPrice'];

            $tmp = explode(STR_DIVISION, $goodsData['optionName']);
            for ($i = 0; $i < 5; $i++) {
                $optKey = 'optionValue' . ($i + 1);
                if (empty($goodsOptionData[$optKey]) === false) {
                    $tmpOption[$i]['optionName'] = (empty($tmp[$i]) === false ? $tmp[$i] : '');
                    $tmpOption[$i]['optionValue'] = $goodsOptionData[$optKey];

                    // 마지막 옵션리스트에 옵션가를 추가한다.
                    if (count($tmp) == $i + 1) {
                        $tmpOption[$i]['optionPrice'] = $goodsOptionData['optionPrice'];
                        $tmpOption[$i]['optionCode'] = $goodsOptionData['optionCode'];
                    }
                }
            }
            foreach ($tmpOption as $oKey => $oVal) {
                $option[] = [
                    $oVal['optionName'],
                    $oVal['optionValue'],
                    $oVal['optionCode'],
                    floatval($oVal['optionPrice']),
                ];
            }
            $tmpInsertOrderGoodsSqlArr['optionInfo'] = json_encode($option, JSON_UNESCAPED_UNICODE);

            unset($option, $tmpOption, $tmp);
        }

        return $tmpInsertOrderGoodsSqlArr;
    }

    /*
     * 등록될 주문배송 데이터 취합
     *
     * @param string $orderNo 주문번호
     * @param array $getDeliveryInfo 배송정보
     * @param array $getData 데이터취합을 위한 필요데이터
     * @param array $goodsData 상품정보
     *
     * @return array $tmpInsertOrderDeliverySqlArr
     */
    public function getInsertOrderDeliveryData($orderNo, $getDeliveryInfo, $getData, $goodsData)
    {
        $getData['deliveryPolicyCharge'] = ($getData['deliveryCollectFl'] === 'l') ? 0 : $getData['deliveryPolicyCharge'];
        $deliveryCharge = gd_isset($getData['deliveryPolicyCharge'], 0);
        $deliveryTaxFreeFl = $getDeliveryInfo[$goodsData['deliverySno']]['taxFreeFl'];
        $deliveryTaxPercent = $getDeliveryInfo[$goodsData['deliverySno']]['taxPercent'];

        $deliveryTaxPrice = NumberUtils::taxAll($deliveryCharge, $deliveryTaxPercent, $deliveryTaxFreeFl);
        if ($deliveryTaxFreeFl == 't') {
            // 배송비 과세처리
            $taxSupplyDeliveryCharge = $deliveryTaxPrice['supply'];
            $taxVatDeliveryCharge = $deliveryTaxPrice['tax'];
        } else {
            // 배송비 면세처리
            $taxFreeDeliveryCharge = $deliveryTaxPrice['supply'];
        }

        $tmpInsertOrderDeliverySqlArr = [
            'orderNo' => $orderNo,
            'deliverySno' => $goodsData['deliverySno'],
            'deliveryCharge' => $deliveryCharge,
            'taxSupplyDeliveryCharge' => gd_isset($taxSupplyDeliveryCharge, 0),
            'taxVatDeliveryCharge' => gd_isset($taxVatDeliveryCharge, 0),
            'taxFreeDeliveryCharge' => gd_isset($taxFreeDeliveryCharge, 0),
            'realTaxSupplyDeliveryCharge' => gd_isset($taxSupplyDeliveryCharge, 0),
            'realTaxVatDeliveryCharge' => gd_isset($taxVatDeliveryCharge, 0),
            'realTaxFreeDeliveryCharge' => gd_isset($taxFreeDeliveryCharge, 0),
            'deliveryPolicyCharge' => $deliveryCharge,
            'deliveryFixFl' => 'fixed',
            'goodsDeliveryFl' => 'n',
            'deliveryMethod' => $getDeliveryInfo[$goodsData['deliverySno']]['method'],
            'deliveryTaxInfo' => $deliveryTaxFreeFl . STR_DIVISION . $deliveryTaxPercent,
            'deliveryPolicy' => json_encode($getDeliveryInfo[$goodsData['deliverySno']], JSON_UNESCAPED_UNICODE),
            'deliveryCollectFl' => ($getData['deliveryCollectFl'] === 'l') ? 'later' : 'pre',
            'regDt' => $getData['regDt'],
        ];

        return $tmpInsertOrderDeliverySqlArr;
    }

    /*
     * 등록될 주문정보 데이터 취합
     *
     * @param string $orderNo 주문번호
     * @param array $getData 데이터취합을 위한 필요데이터
     *
     * @return array $tmpInsertOrderInfoSqlArr
     */
    public function getInsertOrderInfoData($orderNo, $getData)
    {
        $tmpInsertOrderInfoSqlArr = [
            'orderNo' => $orderNo,
            'orderName' => $getData['orderName'],
            'orderEmail' => $getData['orderEmail'],
            'orderPhone' => $getData['orderPhone'],
            'orderCellPhone' => $getData['orderCellPhone'],
            'receiverName' => $getData['receiverName'],
            'receiverPhone' => $getData['receiverPhone'],
            'receiverCellPhone' => $getData['receiverCellPhone'],
            'receiverZipcode' => $getData['receiverZipcode'],
            'receiverZonecode' => $getData['receiverZonecode'],
            'receiverAddress' => $getData['receiverAddress'],
            'receiverAddressSub' => $getData['receiverAddressSub'],
            'orderMemo' => $getData['orderMemo'],
            'regDt' => $getData['regDt'],
        ];

        return $tmpInsertOrderInfoSqlArr;
    }

    /*
     * 등록될 주문 데이터 취합
     *
     * @param string $orderNo 주문번호
     * @param array $getData 데이터취합을 위한 필요데이터
     *
     * @return array $tmpInsertOrderSqlArr
     */
    public function getInsertOrderData($orderNo, $getData){
        $tmpInsertOrderSqlArr = [
            'orderNo' => $orderNo,
            'apiOrderNo' => $getData['apiOrderNo'],
            'orderStatus' => ($getData['orderPayFl'] === 'y') ? 'p1' : 'o1',
            'orderIp' => $this->remoteIP,
            'orderChannelFl' => 'etc',
            'orderEmail' => $getData['orderEmail'],
            'settleKind' => $getData['settleKind'],
            'regDt' => $getData['regDt'],
            'paymentDt' => $getData['paymentDt'],
            'depositPolicy' => $this->policyData['depositPolicy'],
            'mileagePolicy' => $this->policyData['mileagePolicy'],
            'statusPolicy' => $this->policyData['statusPolicy'],
            'couponPolicy' => $this->policyData['couponPolicy'],
        ];

        return $tmpInsertOrderSqlArr;
    }

    /*
     * 등록될 주문상품별 관리자메모 데이터 취합
     *
     * @param string $orderNo 주문번호
     * @param array $getData 데이터취합을 위한 필요데이터
     *
     * @return array $tmpInsertOrderGoodsMemoSqlArr
     */
    public function getInsertOrderGoodsMemoData($orderNo, $getData)
    {
        $tmpInsertOrderGoodsMemoSqlArr = [
            'managerSno' => $this->managerSno,
            'orderNo' => $orderNo,
            'type' => 'goods',
            'content' => $getData['adminOrderGoodsMemo'],
            'regDt' => $getData['regDt'],
        ];

        return $tmpInsertOrderGoodsMemoSqlArr;
    }

    /*
     * 주문배송 등록
     *
     * @param array $insertDataOrderDelivery 등록될 데이터
     *
     * @return integer $orderDeliverySno
     */
    public function insertDataOrderDelivery($insertDataOrderDelivery)
    {
        $arrBind = [];
        $arrBind = $this->db->get_binding($this->orderDeliveryTableField, $insertDataOrderDelivery, 'insert');
        if(trim($insertDataOrderDelivery['regDt']) !== ''){
            $this->db->set_insert_db_query(DB_ORDER_DELIVERY, $arrBind['param'], $arrBind['bind'], 'y', $insertDataOrderDelivery['regDt']);
        }
        else {
            $this->db->set_insert_db(DB_ORDER_DELIVERY, $arrBind['param'], $arrBind['bind'], 'y', false);
        }
        $orderDeliverySno = $this->db->insert_id();

        unset($arrBind);

        return $orderDeliverySno;
    }

    /*
     * 주문상품 등록
     *
     * @param array $insertDataOrderGoods 등록될 데이터
     * @param integer $orderDeliverySno 주문배송고유번호
     *
     * @return integer $orderGoodsInsertId
     */
    public function insertDataOrderGoods($insertDataOrderGoods, $orderDeliverySno)
    {
        $insertDataOrderGoods['orderDeliverySno'] = $orderDeliverySno;

        $arrBind = [];
        $arrBind = $this->db->get_binding($this->orderGoodsTableField, $insertDataOrderGoods, 'insert');
        if(trim($insertDataOrderGoods['regDt']) !== ''){
            $this->db->set_insert_db_query(DB_ORDER_GOODS, $arrBind['param'], $arrBind['bind'], 'y', $insertDataOrderGoods['regDt']);
        }
        else {
            $this->db->set_insert_db(DB_ORDER_GOODS, $arrBind['param'], $arrBind['bind'], 'y', false);
        }
        $orderGoodsInsertId = $this->db->insert_id();

        unset($arrBind);

        return $orderGoodsInsertId;
    }

    /*
     * 주문 등록
     *
     * @param array $insertDataOrder 등록될 데이터
     *
     * @return void
     */
    public function insertDataOrder($insertDataOrder)
    {
        $arrBind = [];
        $arrBind = $this->db->get_binding($this->orderTableField, $insertDataOrder, 'insert');
        if(trim($insertDataOrder['regDt']) !== ''){
            $this->db->set_insert_db_query(DB_ORDER, $arrBind['param'], $arrBind['bind'], 'y', $insertDataOrder['regDt']);
        }
        else {
            $this->db->set_insert_db(DB_ORDER, $arrBind['param'], $arrBind['bind'], 'y', false);
        }

        unset($arrBind);
    }

    /*
     * 주문정보 등록
     *
     * @param array $insertDataOrderInfo 등록될 데이터
     *
     * @return integer $orderInfoInsertId
     */
    public function insertDataOrderInfo($insertDataOrderInfo)
    {
        $arrBind = [];
        $arrBind = $this->db->get_binding($this->orderInfoTableField, $insertDataOrderInfo, 'insert');
        if(trim($insertDataOrderInfo['regDt']) !== ''){
            $this->db->set_insert_db_query(DB_ORDER_INFO, $arrBind['param'], $arrBind['bind'], 'y', $insertDataOrderInfo['regDt']);
        }
        else {
            $this->db->set_insert_db(DB_ORDER_INFO, $arrBind['param'], $arrBind['bind'], 'y', false);
        }
        $orderInfoInsertId = $this->db->insert_id();

        unset($arrBind);

        return $orderInfoInsertId;
    }

    /*
     * 주문상품별 관리자메모 등록
     *
     * @param array $insertDataOrderGoodsMemo 등록될 데이터
     * @param integer $orderGoodsSno 주문상품고유번호
     *
     * @return integer $orderGoodsMemoInsertId
     */
    public function insertDataOrderGoodsMemo($insertDataOrderGoodsMemo, $orderGoodsSno)
    {
        $insertDataOrderGoodsMemo['orderGoodsSno'] = $orderGoodsSno;

        $arrBind = [];
        $arrBind = $this->db->get_binding($this->orderGoodsMemoTableField, $insertDataOrderGoodsMemo, 'insert');
        if(trim($insertDataOrderGoodsMemo['regDt']) !== ''){
            $this->db->set_insert_db_query(DB_ADMIN_ORDER_GOODS_MEMO, $arrBind['param'], $arrBind['bind'], 'y', $insertDataOrderGoodsMemo['regDt']);
        }
        else {
            $this->db->set_insert_db(DB_ADMIN_ORDER_GOODS_MEMO, $arrBind['param'], $arrBind['bind'], 'y', false);
        }
        $orderGoodsMemoInsertId = $this->db->insert_id();

        unset($arrBind);

        return $orderGoodsMemoInsertId;
    }

    /*
     * 외부채널 엑셀 주문등록 후 엑셀파일로 결과를 리턴하기위한 리턴데이터값
     *
     * @param string $orderNo 주문번호
     * @param integer $orderGroup 엑셀등록시 주문그룹
     * @param string $goodsCd 상품자체코드
     * @param string $optionCode 옵션자체코드
     * @param string $result 엑셀등록결과 '성공' or '실패'
     * @param string $message error message
     *
     * @return void
     */
    public function setResultData($orderNo, $orderGroup, $goodsCd, $optionCode, $result, $message)
    {
        $this->resultData[] = [
            'orderNo' => $orderNo,
            'orderGroup' => $orderGroup,
            'goodsCd' => $goodsCd,
            'optionCode' => $optionCode,
            'result' => $result,
            'message' => $message,
        ];
    }

    /*
     * 외부채널 엑셀 주문등록 필드항목 리스트
     *
     * @param void
     *
     * @return array $descriptionList
     */
    public function getExcelDescriptionList()
    {
        $descriptionList = [
            [
                'fieldNameKorean' => '주문 그룹 번호',
                'fieldNameEnglish' => 'orderGroup',
                'unit' => 'goods',
                'description' => [
                  '다수의 주문상품을 하나의 주문으로 묶는 단위. 5자 이내의 숫자 (1~99999)',
                ],
                'require' => true,
            ],
            [
                'fieldNameKorean' => '외부채널 주문번호',
                'fieldNameEnglish' => 'apiOrderNo',
                'unit' => 'order',
                'description' => [
                    '50자 이내의 영문, 숫자',
                    '입력 시 새로 생성된 주문번호와 함께 표기됩니다.',
                ],
                'require' => false,
            ],
            [
                'fieldNameKorean' => '외부채널 품목고유번호',
                'fieldNameEnglish' => 'apiOrderGoodsNo',
                'unit' => 'goods',
                'description' => [
                    '50자 이내의 영문, 숫자',
                    '입력 시 새로 생성된 상품주문번호와 함께 표기됩니다.',
                ],
                'require' => false,
            ],
            [
                'fieldNameKorean' => '자체상품코드',
                'fieldNameEnglish' => 'goodsCd',
                'unit' => 'goods',
                'description' => [
                    '40자 이내, 한글/영문 대소문자/숫자/특수문자를 이용하여 입력합니다.',
                ],
                'require' => true,
            ],
            [
                'fieldNameKorean' => '자체옵션코드',
                'fieldNameEnglish' => 'optionCode',
                'unit' => 'goods',
                'description' => [
                    '옵션이 없는 상품: 옵션없음',
                    '옵션이 있는 상품: 자체옵션코드',
                    '(64자 이내, 한글/영문 대소문자/숫자/특수문자를 이용하여 입력)',
                ],
                'require' => true,
            ],
            [
                'fieldNameKorean' => '상품 수량',
                'fieldNameEnglish' => 'goodsCnt',
                'unit' => 'goods',
                'description' => [
                    '10자 이내의 숫자',
                ],
                'require' => true,
            ],
            [
                'fieldNameKorean' => '주문상품당 결제금액',
                'fieldNameEnglish' => 'goodsSettlePrice',
                'unit' => 'goods',
                'description' => [
                    '주문상품당 결제금액입니다.',
                    '상품 수량이 여러 개인 경우, 수량이 반영된 금액을 입력합니다.',
                    'ex) 결제금액이 1,000원인 상품을 3개 주문하는 경우, 3000 입력.',
                ],
                'require' => true,
            ],
            [
                'fieldNameKorean' => '주문상품당 배송비',
                'fieldNameEnglish' => 'deliveryPolicyCharge',
                'unit' => 'goods',
                'description' => [
                    '주문상품당 배송비입니다.',
                ],
                'require' => true,
            ],
            [
                'fieldNameKorean' => '배송비 결제 방법',
                'fieldNameEnglish' => 'deliveryCollectFl',
                'unit' => 'order',
                'description' => [
                    'p:선불, l:착불, 기본은 p(선불)입니다.',
                ],
                'require' => false,
            ],
            [
                'fieldNameKorean' => '주문자 이름',
                'fieldNameEnglish' => 'orderName',
                'unit' => 'order',
                'description' => [
                    '20자 이내의 주문자 이름을 입력합니다. 특수문자, 공백사용 불가',
                ],
                'require' => true,
            ],
            [
                'fieldNameKorean' => '주문자 이메일',
                'fieldNameEnglish' => 'orderEmail',
                'unit' => 'order',
                'description' => [
                    '100자 이내의 이메일 (aaa@godo.co.kr)',
                ],
                'require' => true,
            ],
            [
                'fieldNameKorean' => '주문자 전화번호',
                'fieldNameEnglish' => 'orderPhone',
                'unit' => 'order',
                'description' => [
                    '하이픈(-) 를 포함한 13자리의 번호 입력. 형식) XXX-XXXX-XXXX',
                ],
                'require' => false,
            ],
            [
                'fieldNameKorean' => '주문자 핸드폰번호',
                'fieldNameEnglish' => 'orderCellPhone',
                'unit' => 'order',
                'description' => [
                    '하이픈(-) 를 포함한 13자리의 번호 입력. 형식) XXX-XXXX-XXXX',
                ],
                'require' => true,
            ],
            [
                'fieldNameKorean' => '수취인 이름',
                'fieldNameEnglish' => 'receiverName',
                'unit' => 'order',
                'description' => [
                    '20자 이내의 주문자 이름을 입력합니다. 특수문자, 공백사용 불가',
                ],
                'require' => true,
            ],
            [
                'fieldNameKorean' => '수취인 전화번호',
                'fieldNameEnglish' => 'receiverPhone',
                'unit' => 'order',
                'description' => [
                    '하이픈(-) 를 포함한 13자리의 번호 입력. 형식) XXX-XXXX-XXXX',
                ],
                'require' => false,
            ],
            [
                'fieldNameKorean' => '수취인 핸드폰번호',
                'fieldNameEnglish' => 'receiverCellPhone',
                'unit' => 'order',
                'description' => [
                    '하이픈(-) 를 포함한 13자리의 번호 입력. 형식) XXX-XXXX-XXXX',
                ],
                'require' => true,
            ],
            [
                'fieldNameKorean' => '수취인 (구)우편번호',
                'fieldNameEnglish' => 'receiverZipcode',
                'unit' => 'order',
                'description' => [
                    '하이픈(-) 를 포함한 7자리의 수 우편번호 입력. 형식) XXX-XXX',
                ],
                'require' => false,
            ],
            [
                'fieldNameKorean' => '수취인 구역번호',
                'fieldNameEnglish' => 'receiverZonecode',
                'unit' => 'order',
                'description' => [
                    '5자리의 신규 우편번호 입력. 형식) XXXXX',
                ],
                'require' => true,
            ],
            [
                'fieldNameKorean' => '수취인 주소',
                'fieldNameEnglish' => 'receiverAddress',
                'unit' => 'order',
                'description' => [
                    '45자 이내의 주소 입력.',
                ],
                'require' => true,
            ],
            [
                'fieldNameKorean' => '수취인 나머지 주소',
                'fieldNameEnglish' => 'receiverAddressSub',
                'unit' => 'order',
                'description' => [
                    '100자 이내의 나머지주소 입력.',
                ],
                'require' => true,
            ],
            [
                'fieldNameKorean' => '배송메시지',
                'fieldNameEnglish' => 'orderMemo',
                'unit' => 'order',
                'description' => [
                    '500자 이내의 배송메시지를 입력합니다.',
                ],
                'require' => false,
            ],
            [
                'fieldNameKorean' => '결제완료여부',
                'fieldNameEnglish' => 'orderPayFl',
                'unit' => 'order',
                'description' => [
                    'y:결제완료, n:미결제, 기본은 n(미결제)입니다.',
                ],
                'require' => false,
            ],
            [
                'fieldNameKorean' => '결제수단',
                'fieldNameEnglish' => 'settleKind',
                'unit' => 'order',
                'description' => [
                    'gb: 무통장입금, ph: 휴대폰결제, pc: 신용카드, pb: 계좌이체, pv: 가상계좌, gz: 전액할인',
                    '하나의 수단만 등록이 가능하며 기본은 gb(무통장입금)입니다.',
                ],
                'require' => false,
            ],
            [
                'fieldNameKorean' => '주문상품별 관리자메모',
                'fieldNameEnglish' => 'adminOrderGoodsMemo',
                'unit' => 'goods',
                'description' => [
                    '500자 이내의 주문상품별 관리자 메모를 입력합니다.',
                ],
                'require' => false,
            ],
            [
                'fieldNameKorean' => '주문일',
                'fieldNameEnglish' => 'regDt',
                'unit' => 'order',
                'description' => [
                    '입력 형식은 "yyyy-mm-dd 00:00:00"이며, 미입력 시 \'엑셀파일 업로드일시\'로 등록됩니다.',
                ],
                'require' => false,
            ],
            [
                'fieldNameKorean' => '결제완료일',
                'fieldNameEnglish' => 'paymentDt',
                'unit' => 'order',
                'description' => [
                    '입력 형식은 "yyyy-mm-dd 00:00:00"입니다.',
                    '결제완료여부가 y인 경우, 미입력 시 \'주문일과 동일한 일시\'로 등록되며,',
                    '결제완료여부가 n인 경우, 결제완료일은 등록되지 않습니다.',
                ],
                'require' => false,
            ],
        ];

        return $descriptionList;
    }

    /**
     * 주문번호 생성
     *
     * @param integer $regDt
     *
     * @return string 16자리의 주문번호 생성 (년월일시분초마이크로초)
     */
    public function generateOrderNo($regDt)
    {
        // 0 ~ 999 마이크로초 중 랜덤으로 sleep 처리 (동일 시간에 들어온 경우 중복을 막기 위해서.)
        usleep(mt_rand(0, 999));

        // 0 ~ 99 마이크로초 중 랜덤으로 sleep 처리 (첫번째 sleep 이 또 동일한 경우 중복을 막기 위해서.)
        usleep(mt_rand(0, 99));

        // microtime() 함수의 마이크로 초만 사용
        list($usec) = explode(' ', microtime());

        // 마이크로초을 4자리 정수로 만듬 (마이크로초 뒤 2자리는 거의 0이 나오므로 8자리가 아닌 4자리만 사용함 - 나머지 2자리도 짜름... 너무 길어서.)
        $tmpNo = sprintf('%04d', round($usec * 10000));

        // $regDt (년월일시분초) 에 마이크로초 정수화 한 값을 붙여 주문번호로 사용함, 16자리 주문번호임
        return $regDt . $tmpNo;
    }

    /*
     * 입금대기 리스트에서의 외부채널 주문건 취소
     *
     * @param array $arrData
     *
     * @return void
     */
    public function setCombineStatusCancelList($arrData)
    {
        $this->updateOrderStatus($arrData, __('입금대기 리스트에서'));
    }

    /*
     * 입금확인처리
     *
     * @param string $orderNo
     * @param string $reason
     *
     * @return void
     */
    public function setStatusChangePayment($orderNo, $reason='')
    {
        $arrData = [];
        $arrData['statusCheck']['o'][] = $orderNo;
        $arrData['changeStatus'] = 'p1';
        if(trim($reason) === ''){
            $reason = __('주문 리스트에서');
        }
        $this->updateOrderStatus($arrData, $reason);
    }

    /*
     * 유저모드 주문취소
     *
     * @param array $orderData
     *
     * @return void
     */
    public function setStatusChangeCancel($orderData)
    {
        $statusMode = substr($orderData['orderStatus'], 0, 1);

        $arrData = [];
        $arrData['statusCheck'][$statusMode][] = $orderData['orderNo'];
        $arrData['changeStatus'] = 'c4';
        $this->updateOrderStatus($arrData, __('고객요청에 의해'), false);
    }

    /*
     * 유저모드 구매확정
     *
     * @param array $orderData
     * @param integer $orderGoodsNo
     *
     * @return void
     */
    public function setStatusChangeSettle($orderData, $orderGoodsNo)
    {
        $statusMode = substr($orderData['orderStatus'], 0, 1);

        $arrData = [];
        $arrData['statusCheck'][$statusMode][] = $orderData['orderNo'] . INT_DIVISION . $orderGoodsNo;
        $arrData['changeStatus'] = 's1';
        $this->updateOrderStatus($arrData, __('고객요청에 의해'), false);
    }

    /*
     * admin menu 를 사용하고 있는지 확인
     *
     * @param void
     *
     * @return boolean [true - 사용중, false - 미사용중]
     */
    public function getUseAdminMenu()
    {
        $arrBind = [];

        $this->db->bind_param_push($arrBind, 's', 'godo00763');
        $this->db->bind_param_push($arrBind, 's', 'godo00764');

        $this->db->strField = "adminMenuDisplayType";
        $this->db->strWhere = 'adminMenuNo = ? OR adminMenuNo = ?';
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ADMIN_MENU . ' ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);

        $arrAdminMenuDisplayType = array_column($getData, "adminMenuDisplayType");
        if(in_array("y", $arrAdminMenuDisplayType)){
            return true;
        }
        else {
            return false;
        }
    }
}
