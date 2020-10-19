<?php
/**
 * This is commercial software, only users who have purchased a valid license
 * and accept to the terms of the License Agreement can install and use this
 * program.
 *
 * Do not edit or add to this file if you wish to upgrade Enamoo S5 to newer
 * versions in the future.
 *
 * @copyright Copyright (c) 2015 GodoSoft.
 * @link      http://www.godo.co.kr
 */

namespace Bundle\Component\Order;



class LogisticsSchema
{
    protected $config;
    protected $db;
    protected $logger;
    protected $deliveryCode;    //합포장을 위한 트랜잭션 키 코드
    protected $packSeq; //묶음배송 고려한 합포장 순서를 위한 배열
    public static $packSeqSort = 1;    //합포장 순서

    public function __construct()
    {
        $this->config = gd_policy('logistics.config');
        if (!is_object($this->db)) {
            $this->db = \App::load('DB');
        }
        $this->logger = \Logger::channel('logistics');
        $this->deliveryCode = $this->createTransactionCode();
    }

    public function getReservationSchema()
    {
        $fields = [
            'CUST_ID'=>['name' => '고객ID','required' =>true],
            'RCPT_YMD'=>['name' => '접수일자','required' =>true],
            'CUST_USE_NO'=>['name' => '고객사용번호','required' =>true],
            'RCPT_DV'=>['name' => '접수구분','def'=>'01'],
            'WORK_DV_CD'=>['name' => '작업구분코드','def'=>'01'],
            'REQ_DV_CD'=>['name' => '요청구분코드', 'required' =>true],
            'MPCK_KEY'=>['name' => '합포장키', 'required' =>true],
            'MPCK_SEQ'=>['name' => '합포장순번', 'def'=>1],
            'CAL_DV_CD'=>['name' => '정산구분코드', 'def'=>'01'],
            'FRT_DV_CD'=>['name' => '운임구분코드','required' =>true],
            'CNTR_ITEM_CD'=>['name' => '계약품목코드','def'=>'01'],
            'BOX_TYPE_CD'=>['name' => '박스타입코드','required' =>true],
            'BOX_QTY'=>['name' => '박스수량','def' =>1],
            'CUST_MGMT_DLCM_CD'=>['name' => '고객관리거래처코드','required' =>true],   //CUST_ID와 동일값으로 처리
            'SENDR_NM'=>['name' => '송화인명','required' =>true],
            'SENDR_TEL_NO1'=>['name' => '송화인전화번호1','required' =>true],
            'SENDR_TEL_NO2'=>['name' => '송화인전화번호2','required' =>true],
            'SENDR_TEL_NO3'=>['name' => '송화인전화번호3','required' =>true],
            'SENDR_ZIP_NO'=>['name' => '송화인우편번호','required' =>true],
            'SENDR_ADDR'=>['name' => '송화인주소','required' =>true],
            'SENDR_DETAIL_ADDR'=>['name' => '송화인상세주소','required' =>true],
            'RCVR_NM'=>['name' => '수화인명','required' =>true],
            'RCVR_TEL_NO1'=>['name' => '수화인전화번호1','required' =>true],
            'RCVR_TEL_NO2'=>['name' => '수화인전화번호2','required' =>true],
            'RCVR_TEL_NO3'=>['name' => '수화인전화번호3','required' =>true],
            'RCVR_CELL_NO1'=>['name' => '수화인휴대폰번호1',],
            'RCVR_CELL_NO2'=>['name' => '수화인휴대폰번호2',],
            'RCVR_CELL_NO3'=>['name' => '수화인휴대폰번호3',],
            'RCVR_ZIP_NO'=>['name' => '수화인우편번호','required' =>true],
            'RCVR_ADDR'=>['name' => '수화인주소','required' =>true],
            'RCVR_DETAIL_ADDR'=>['name' => '수화인상세주소','required' =>true],
//            'ORDRR_NM'=>['name' => '주문자명'],
//            'ORDRR_TEL_NO1'=>['name' => '주문자전화번호1'],
//            'ORDRR_TEL_NO2'=>['name' => '주문자전화번호2'],
//            'ORDRR_TEL_NO3'=>['name' => '주문자전화번호3'],
//            'ORDRR_CELL_NO1'=>['name' => '주문자휴대폰번호1'],
//            'ORDRR_CELL_NO2'=>['name' => '주문자휴대폰번호2'],
//            'ORDRR_CELL_NO3'=>['name' => '주문자휴대폰번호3'],
//            'ORDRR_ZIP_NO'=>['name' => '주문자우편번호'],
//            'ORDRR_ADDR'=>['name' => '주문자주소'],
//            'ORDRR_DETAIL_ADDR'=>['name' => '주문자상세주소'],
            'PRT_ST'=>['name' => '출력상태','def' =>'01'],
            'GDS_NM'=>['name' => '상품명','required' =>true],
            'DLV_DV'=>['name' => '택배구분','def' =>'01'],
            'RCPT_ERR_YN'=>['name' => '접수에러여부','def' =>'N'],
            'EAI_PRGS_ST'=>['name' => 'EA전송상태','def' =>'01'],
            'REG_EMP_ID'=>['name' => '등록사원ID','required' =>true],
            'REG_DTIME'=>['name' => '등록일시','required' =>true],
            'MODI_EMP_ID'=>['name' => '수정사원ID','required' =>true],
            'MODI_DTIME'=>['name' => '수정일시','required' =>true],
        ];

        return $fields;
    }

    public function getDeliveryResultSchema()
    {
        $fields = [
            'SERIAL'=>['name' => 'P.K','required' =>true],
            'CUST_ID'=>['name' => '고객ID','required' =>true],
            'RCPT_DV'=>['name' => '접수구분', 'required' =>true],
            'INVC_NO'=>['name' => '운송장번호', 'required' =>true],
            'CUST_USE_NO'=>['name' => '고객사용번호', 'required' =>true],
            'CRG_ST'=>['name' => '화불상태','required' =>true],
            'EAI_PRGS_ST'=>['name' => '전송상태', 'required' =>true],
            'REG_EMP_ID'=>['name' => '등록사원ID', 'required' =>true],
            'REG_DTIME'=>['name' => '등록일시', 'required' =>true],
            'MODI_EMP_ID'=>['name' => '수정사원ID', 'required' =>true],
            'MODI_DTIME'=>['name' => '수정일시', 'required' =>true],
        ];

        return $fields;
    }

    public function validationSchema($mode, $data)
    {
        $errorMsg = null;
        if($mode == 'reservation') {
            $schema = $this->getReservationSchema();
        }
        else if($mode == 'deliveryResult') {
            $schema = $this->getDeliveryResultSchema();
        }
        else {
            throw new \Exception('mode ');
        }
        foreach($schema as $key=>$val)
        {
            if($val['required'] === true) {
                if(empty($data[$key])) {
                    $errorMsg[] = sprintf("%s(%s)값은 필수입니다.",$val['name'],$key);
                    continue;
                }
            }
        }

        $result = $errorMsg ? false : true;

        return ['result'=>$result, 'errorMsg'=>$errorMsg];
    }

    protected function getSplitPhone($phone,$mode = 'phone')
    {
        if(strpos($phone,'-') !== false) {
            return explode('-',$phone);
        }
        else {
            if($mode == 'cellPhone') {
                preg_match_all("/^(01(?:0|1|[6-9]))((?:\d{3}|\d{4}))(\d{4})$/",$phone, $phoneMatch);
                return [$phoneMatch[1][0], $phoneMatch[2][0], $phoneMatch[3][0]];
            }
            else if($mode == 'safe') {
                preg_match_all("/(\d{4})(\d{4})(\d{4})$/",$phone, $phoneMatch);
                return [$phoneMatch[1][0], $phoneMatch[2][0], $phoneMatch[3][0]];
            }
            else {
                preg_match_all("/^(0(2|3[1-3]|4[1-4]|5[1-5]|6[1-4]))(\d{3,4})(\d{4})$/",$phone, $phoneMatch);
                return [$phoneMatch[1][0], $phoneMatch[3][0], $phoneMatch[4][0]];
            }
        }
    }

    /**
     *
     *
     * @param        $orderGoodsData
     * @param string $mode
     *
     * @param        $isPack
     *
     * @return mixed
     */
    public function buildSchemaByOrderGoodsData($orderGoodsData, $mode = 'reservation', $isPack = true)
    {
        if(!$orderGoodsData) {
            throw new \InvalidArgumentException('orderGoodsNo');
        }
        list($RCVR_TEL_NO1, $RCVR_TEL_NO2, $RCVR_TEL_NO3) = $this->getSplitPhone($orderGoodsData['receiverPhone'], 'phone');
        list($RCVR_CELL_NO1, $RCVR_CELL_NO2, $RCVR_CELL_NO3) = $this->getSplitPhone($orderGoodsData['receiverCellPhone'],'cellPhone');
        list($RCVR_SAFE_NO1, $RCVR_SAFE_NO2, $RCVR_SAFE_NO3) = $this->getSplitPhone($orderGoodsData['receiverSafeNumber'],'safe');

        $RCPT_YMD = date('Ymd');
        $CUST_ID = $this->config['CUST_ID'];
        if($mode == 'reservation') {    //예약
            $CUST_USE_NO =  $CUST_ID.'_'.$orderGoodsData['orderNo'].'_'.$orderGoodsData['sno'].'_'.date('ymdHis');
            if($isPack === true) {  //합포장 기능 활성
                if(empty($orderGoodsData['packetCode']) === false) {    //묶음배송인경우(주문번호가 다름)
                    $MPCK_KEY = 'pack_'.$CUST_ID.'_'.date('YmdHis').'_'.$orderGoodsData['packetCode'].'_'.$this->deliveryCode;
                }
                else {  //주문번호가 같으면 배송지 기준으로 합포장
                    $MPCK_KEY = 'pack_'.$CUST_ID.'_'.date('YmdHis').'_'.$orderGoodsData['orderInfoSno'].'_'.$this->deliveryCode;;
                }

                if($this->packSeq[$MPCK_KEY]) {
                    $this->packSeq[$MPCK_KEY]++;
                }
                else {
                    $this->packSeq[$MPCK_KEY] = 1;
                }
                $MPCK_SEQ = $this->packSeq[$MPCK_KEY];
            }
            else {
                $MPCK_KEY = 'pack_'.$CUST_ID.'_'.date('YmdHis').'_'.$orderGoodsData['orderInfoSno'].'_'.$this->createTransactionCode();
                $MPCK_SEQ = 1;
                self::$packSeqSort++;
            }
        }
        else {  //예약취소 시    기존값 전송
            $CUST_USE_NO =  $orderGoodsData['custUseNo'];
            $MPCK_KEY = $orderGoodsData['mpckKey'];
            $MPCK_SEQ = $orderGoodsData['mpckSeq'];
        }

        $arrOptionInfo = null;
        if($orderGoodsData['optionInfo']) {
            $optionInfo = json_decode(gd_htmlspecialchars_stripslashes($orderGoodsData['optionInfo']), true);
            foreach($optionInfo as $val) {
                $arrOptionInfo[]=$val[1];
            }
        }

        if($orderGoodsData['optionTextInfo']) {
            $optionTextInfo = json_decode(gd_htmlspecialchars_stripslashes($orderGoodsData['optionTextInfo']), true);
            foreach($optionTextInfo as $val) {
                $arrOptionInfo[] = $val[1];
            }
        }

        $optionInfoText = $arrOptionInfo ? implode('|', $arrOptionInfo) : '';
        $data['CUST_ID'] = $CUST_ID;
        $data['RCPT_YMD'] = $RCPT_YMD;
        $data['CUST_USE_NO'] = $CUST_USE_NO;
        $data['REQ_DV_CD'] = $mode == 'cancel' ? '02' : '01';   //예약 / 예약취소
        $data['MPCK_KEY'] = $MPCK_KEY;    //합포장 키 없음
        $data['MPCK_SEQ'] = $MPCK_SEQ;    //함포없음
        $data['FRT_DV_CD'] = $this->config['FRT_DV_CD'];
        $data['BOX_TYPE_CD'] = $this->config['BOX_TYPE_CD'];
        $data['CUST_MGMT_DLCM_CD'] = $data['CUST_ID'];
        $data['SENDR_NM'] = $this->config['SENDR_NM'];
        $data['SENDR_TEL_NO1'] = $this->config['SENDR_TEL_NO1'];
        $data['SENDR_TEL_NO2'] = $this->config['SENDR_TEL_NO2'];
        $data['SENDR_TEL_NO3'] = $this->config['SENDR_TEL_NO3'];
        $data['SENDR_ZIP_NO'] = str_replace('-','',$this->config['zonecode']);
        $data['SENDR_ADDR'] = $this->config['SENDR_ADDR'];
        $data['SENDR_DETAIL_ADDR'] = $this->config['SENDR_DETAIL_ADDR'];
        $data['RCVR_NM'] = $orderGoodsData['receiverName'];
        $data['RCVR_TEL_NO1'] = gd_isset($RCVR_TEL_NO1,$RCVR_CELL_NO1);
        $data['RCVR_TEL_NO2'] = gd_isset($RCVR_TEL_NO2,$RCVR_CELL_NO2);
        $data['RCVR_TEL_NO3'] = gd_isset($RCVR_TEL_NO3,$RCVR_CELL_NO3);
        $data['RCVR_CELL_NO1'] = $RCVR_CELL_NO1;
        $data['RCVR_CELL_NO2'] = $RCVR_CELL_NO2;
        $data['RCVR_CELL_NO3'] = $RCVR_CELL_NO3;
        if($orderGoodsData['receiverZonecode']) {
            $rcvrZipNo = $orderGoodsData['receiverZonecode'];
        }
        else {
            $rcvrZipNo = str_replace('-','',$orderGoodsData['receiverZipcode']);
        }
        $data['RCVR_ZIP_NO'] = $rcvrZipNo;
        $data['RCVR_ADDR'] = $orderGoodsData['receiverAddress'];
        $data['RCVR_DETAIL_ADDR'] = $orderGoodsData['receiverAddressSub'];

        if($orderGoodsData['receiverUseSafeNumberFl'] == 'y') {
            $data['RCVR_SAFE_NO1'] = $RCVR_SAFE_NO1;
            $data['RCVR_SAFE_NO2'] = $RCVR_SAFE_NO2;
            $data['RCVR_SAFE_NO3'] = $RCVR_SAFE_NO3;
        }

//        if($orderGoodsData['orderZonecode']) {
//            $orderZipNo = $orderGoodsData['orderZonecode'];
//        }
//        else {
//            $orderZipNo = str_replace('-','',$orderGoodsData['orderZipcode']);
//        }
//        list($ORDRR_TEL_NO1, $ORDRR_TEL_NO2, $ORDRR_TEL_NO3) = $this->getSplitPhone($orderGoodsData['orderPhone'], 'phone');
//        list($ORDRR_CELL_NO1, $ORDRR_CELL_NO2, $ORDRR_CELL_NO3) = $this->getSplitPhone($orderGoodsData['orderCellPhone'], 'cellPhone');
//        $data['ORDRR_NM'] = $orderGoodsData['orderName'];
//        $data['ORDRR_TEL_NO1'] = gd_isset($ORDRR_TEL_NO1,$ORDRR_CELL_NO1);
//        $data['ORDRR_TEL_NO2'] = gd_isset($ORDRR_TEL_NO2,$ORDRR_CELL_NO2);;
//        $data['ORDRR_TEL_NO3'] = gd_isset($ORDRR_TEL_NO3,$ORDRR_CELL_NO3);;
//        $data['ORDRR_CELL_NO1'] = $ORDRR_CELL_NO1;
//        $data['ORDRR_CELL_NO2'] = $ORDRR_CELL_NO2;
//        $data['ORDRR_CELL_NO3'] = $ORDRR_CELL_NO3;
////        $data['ORDRR_SAFE_NO1'] = '';
////        $data['ORDRR_SAFE_NO2'] = '';
////        $data['ORDRR_SAFE_NO3'] = '';
//        $data['ORDRR_ZIP_NO'] = $orderZipNo;
//        $data['ORDRR_ADDR'] = $orderGoodsData['orderAddress'];
//        $data['ORDRR_DETAIL_ADDR'] = $orderGoodsData['orderAddressSub'];

        $data['REMARK_1'] = $orderGoodsData['orderMemo'];
        $data['GDS_NM'] = $orderGoodsData['goodsNm'].' ';
        $data['GDS_CD'] = $orderGoodsData['goodsNo'];   //TODO:TEST
        $data['GDS_QTY'] = $orderGoodsData['goodsCnt'];  //TODO:TEST
        $data['UNIT_CD'] = '';   //TODO:TEST
        $data['UNIT_NM'] = $optionInfoText;    //TODO:TEST
        $data['GDS_AMT'] = '';//$orderGoodsData['goodsPrice']     //TODO:TEST
        $data['ETC_1'] = '';  //TODO:TEST
        $data['ETC_2'] = '';  //TODO:TEST
        $data['ETC_3'] = '';  //TODO:TEST
        $data['ETC_4'] = '';  //TODO:TEST
        $data['ETC_5'] = '';  //TODO:TEST
        $data['ARTICLE_AMT'] = '0'; //TODO:TEST

        $data['REG_EMP_ID'] = 'NHN';
        $data['REG_DTIME'] = date('Y-m-d h:i:s');
        $data['MODI_EMP_ID'] = 'NHN';
        $data['MODI_DTIME'] = date('Y-m-d h:i:s');

        $globals = \App::getInstance('globals');
        $data['shopSno'] = $globals->get('gLicense.godosno');
        $data['domain'] = $this->getResponseUrl();
        //디폴트 값 자동지정
        $schema = $this->getReservationSchema();
        foreach($schema as $key=>$val) {
            if(empty($data[$key])=== true && empty($schema[$key]['def']) === false) {
                $data[$key] = $schema[$key]['def'];
            }
        }

        return $data;
    }

    protected function getResponseUrl()
    {
        return URI_API.'godo/set_logistics_order.php';
    }

    protected function createTransactionCode()
    {
        // 0 ~ 999 마이크로초 중 랜덤으로 sleep 처리 (동일 시간에 들어온 경우 중복을 막기 위해서.)
        usleep(mt_rand(0, 999));
        // 0 ~ 99 마이크로초 중 랜덤으로 sleep 처리 (첫번째 sleep 이 또 동일한 경우 중복을 막기 위해서.)
        usleep(mt_rand(0, 99));
        // microtime() 함수의 마이크로 초만 사용
        list($usec) = explode(' ', microtime());
        // 마이크로초을 4자리 정수로 만듬 (마이크로초 뒤 2자리는 거의 0이 나오므로 8자리가 아닌 4자리만 사용함 - 나머지 2자리도 짜름... 너무 길어서.)
        $tmpNo = sprintf('%04d', round($usec * 10000));
        $deliveryCode = 'D' . $tmpNo;

        return $deliveryCode;
    }
}
