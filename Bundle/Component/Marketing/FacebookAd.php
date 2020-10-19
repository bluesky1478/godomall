<?php
namespace Bundle\Component\Marketing;

use Component\Database\DBTableField;
use App;
use DateTime;
use UserFilePath;
use Framework\StaticProxy\Proxy\FileHandler;
use Globals;

/**
 * Class FacebookAd
 * @author  Sojeong Park <psj6414@godo.co.kr>
 */

class FacebookAd
{
    public $config;
    public $configExtension;
    public $feedGoodsCnt;
    public $logger;
    public $progressNum;
    public $domainUrl;

    public function __construct()
    {
        ini_set('memory_limit', '-1');
        set_time_limit(RUN_TIME_LIMIT);

        if (!is_object($this->db)) {
            $this->db = \App::load('DB');
        }
        $this->logger = \App::getInstance('logger')->channel('facebookExtension');
    }

    /**
     * 페이스북 광고 설정값 가져오기
     * @return array
     */
    public function getConfig()
    {
        if($this->config == null) {
            $dbUrl = \App::load('\\Component\\Marketing\\DBUrl');
            $faceboodAdConfig = $dbUrl->getConfig('facebook', 'config');
            $this->config = $faceboodAdConfig;
        }
        return $this->config;
    }

    /**
     * 페이스북 비즈니스 익스텐션 설정값 가져오기
     * @return array
     */
    public function getExtensionConfig()
    {
        if($this->configExtension == null){
            $dbUrl = \App::load('\\Component\\Marketing\\DBUrl');
            $faceboodAdConfig = $dbUrl->getConfig('facebookExtension', 'config')['value'];
            $this->configExtension = $faceboodAdConfig;
        }
        return $this->configExtension;
    }

    /**
     * 페이스북 공통 스크립트 설정
     * @return string
     */
    public function getFbCommonScript()
    {
        $config = $this->getConfig();
        $configExtension = $this->getExtensionConfig();
        $fbScript = '';
        if(empty($configExtension) === false && $configExtension['fbUseFl'] == 'y'){ // 확장기능설정시
            $fbScript = App::getConfig('outsidescript.facebookAd')->toArray()['commonExtension'];
            $fbScript = str_replace('[pixelId]', $configExtension['pixel_id'], $fbScript);
            $fbScript = str_replace('[piiData]', '{}', $fbScript);
        } else { // 기존 기능 사용시
            if ((empty($configExtension) === true || $configExtension['fbUseFl'] === 'n') && $config['fbUseFl'] === 'y') {
                $fbScript = App::getConfig('outsidescript.facebookAd')->toArray()['common'];
                $fbScript = str_replace('[pixelId]', $config['fixelId'], $fbScript);
            }
        }
        return $fbScript;
    }

    /**
     * 페이스북 구매완료 스크립트 설정.
     * @param $goodsNo
     * @param $goodsPrice
     * @param $currency
     * @return string
     */
    public function getFbOrderEndScript($goodsNo, $goodsPrice, $currency)
    {
        $config = $this->getConfig(); // 기존설정
        $configExtension = $this->getExtensionConfig(); // 확장 설정
        $fbOrderEndScript = App::getConfig('outsidescript.facebookAd')->toArray()['orderEnd'];
        $fbOrderEndScript = str_replace('[goodsNo]', implode(",", $goodsNo), $fbOrderEndScript);
        $fbOrderEndScript = str_replace('[goodsPrice]', $goodsPrice, $fbOrderEndScript);
        $fbOrderEndScript = str_replace('[currency]', $currency, $fbOrderEndScript);
        if(empty($configExtension)===false && $configExtension['fbUseFl'] == 'y'){
            return $fbOrderEndScript;
        }else {
            if ((empty($configExtension) === true || $configExtension['fbUseFl'] === 'n') && $config['fbUseFl'] === 'y' && $config['orderEndScriptFl'] == 'y') {
                return $fbOrderEndScript;
            }
        }
    }

    /**
     * 페이스북 상품상세 스크립트 설정
     * @param $goodsNo
     * @param $goodsPrice
     * @param $currency
     * @return string
     */
    public function getFbGoodsViewScript($goodsNo, $goodsPrice, $currency)
    {
        $config = $this->getConfig();
        $configExtension = $this->getExtensionConfig(); // 확장 설정
        $fbGoodsViewScript = App::getConfig('outsidescript.facebookAd')->toArray()['goodsView'];
        $fbGoodsViewScript = str_replace('[goodsNo]', $goodsNo, $fbGoodsViewScript);
        $fbGoodsViewScript = str_replace('[goodsPrice]', $goodsPrice, $fbGoodsViewScript);
        $fbGoodsViewScript = str_replace('[currency]', $currency, $fbGoodsViewScript);
        if (empty($configExtension) === false && $configExtension['fbUseFl'] == 'y') {
            return $fbGoodsViewScript;
        } else {
            if ((empty($configExtension) === true || $configExtension['fbUseFl'] === 'n') && $config['fbUseFl'] == 'y' && $config['goodsViewScriptFl'] == 'y') {
                return $fbGoodsViewScript;
            }
        }
    }

    /**
     * 페이스북 장바구니 스크립트 설정
     * @param $goodsNo
     * @param $goodsPrice
     * @param $currency
     * @return string
     */
    public function getFbCartScript($goodsNo, $goodsPrice, $currency)
    {
        $config = $this->getConfig();
        $configExtension = $this->getExtensionConfig(); // 확장 설정
        $fbCartScript = App::getConfig('outsidescript.facebookAd')->toArray()['cart'];
        $fbCartScript = str_replace('[goodsNo]', implode(",", $goodsNo), $fbCartScript);
        $fbCartScript = str_replace('[goodsPrice]', $goodsPrice, $fbCartScript);
        $fbCartScript = str_replace('[currency]', $currency, $fbCartScript);
        if(empty($configExtension) === false && $configExtension['fbUseFl'] == 'y'){
            return $fbCartScript;
        }else{
            if ((empty($configExtension) === true || $configExtension['fbUseFl'] ==='n') && $config['fbUseFl'] === 'y' && $config['cartScriptFl'] === 'y') {
                return $fbCartScript;
            }
        }

    }

    /**
     * 페이스북 위시리스트 스크립트 설정
     * @param $goodsNo
     * @param $currency
     * @return string
     */
    public function getFbWishListScript($goodsNo, $currency)
    {
        $configExtension = $this->getExtensionConfig();
        $fbScript = '';
        if (empty($configExtension)===false && $configExtension['fbUseFl'] === 'y') {
            $fbScript = App::getConfig('outsidescript.facebookAd')->toArray()['addToWishList'];
            $fbScript = str_replace('[goodsNo]', implode(",", $goodsNo), $fbScript);
            $fbScript = str_replace('[currency]', $currency, $fbScript);
        }
        return $fbScript;
    }

    /**
     * 페이스북 회원가입완료 스크립트 설정
     * @return string
     */
    public function getFbCompleteRegistrationScript()
    {
        $configExtension = $this->getExtensionConfig();
        $fbScript = '';
        if (empty($configExtension)===false && $configExtension['fbUseFl'] === 'y') {
            $fbScript = App::getConfig('outsidescript.facebookAd')->toArray()['completeRegistration'];
        }
        return $fbScript;
    }

    /**
     * 페이스북 주문하기 스크립트 설정
     * @param $goodsNo
     * @param $goodsPrice
     * @param $currency
     * @return string
     */
    public function getFbInitiateCheckoutScript($goodsNo, $goodsPrice, $currency)
    {
        $configExtension = $this->getExtensionConfig();
        $fbScript = '';
        if (empty($configExtension)===false && $configExtension['fbUseFl'] === 'y') {
            $fbScript = App::getConfig('outsidescript.facebookAd')->toArray()['initiateCheckout'];
            $fbScript = str_replace('[goodsNo]', implode(",", $goodsNo), $fbScript);
            $fbScript = str_replace('[goodsPrice]', $goodsPrice, $fbScript);
            $fbScript = str_replace('[currency]', $currency, $fbScript);
        }
        return $fbScript;
    }

    /**
     * 페이스북 검색하기 스크립트 설정
     * @param $goodsNo
     * @param $goodsNo
     * @return string
     */
    public function getFbSearchScript($searchString, $goodsNo)
    {
        $configExtension = $this->getExtensionConfig();
        $fbScript = '';
        if (empty($configExtension)===false && $configExtension['fbUseFl'] === 'y') {
            $fbScript = App::getConfig('outsidescript.facebookAd')->toArray()['search'];
            $fbScript = str_replace('[searchString]', $searchString, $fbScript);
            $fbScript = str_replace('[goodsNo]', implode(",", $goodsNo), $fbScript);
        }
        return $fbScript;
    }

    /**
     * 페이스북 Npay 구매 이벤트 스크립트 설정
     * @param $goodsNo
     * @param $goodsPrice
     * @param $currency
     * @return string
     */
    public function getFbNPayScript($goodsNo, $goodsPrice, $currency)
    {
        $configExtension = $this->getExtensionConfig();
        $fbScript = '';
        if (empty($configExtension)===false && $configExtension['fbUseFl'] === 'y') {
            $fbScript = App::getConfig('outsidescript.facebookAd')->toArray()['nPay'];
            $fbScript = str_replace('[goodsNo]', implode(",", $goodsNo), $fbScript);
            $fbScript = str_replace('[goodsPrice]', $goodsPrice, $fbScript);
            $fbScript = str_replace('[currency]', $currency, $fbScript);
        }
        return $fbScript;
    }

    /**
     * 페이스북 제품 피드 설정값 호출
     * @param $goodsNo
     * @return mixed
     */
    public function getFbGoodsFeedData($goodsNo)
    {
        $arrBind = [];
        $strSQL = "SELECT * FROM " . DB_FACEBOOK_GOODS_FEED . " WHERE  goodsNo = ? ";
        $this->db->bind_param_push($arrBind, 's', $goodsNo);
        $tmp = $this->db->query_fetch($strSQL, $arrBind, false);
        unset($arrBind);

        return $tmp;
    }

    /**
     * 페이스북 제품 피드 이미지값 호출
     * @param $goodsNo
     * @return array|string
     */
    public function getFaceBookGoodsImage($goodsNo)
    {
        $arrBind = [];
        $strSQL = "SELECT additional_image_link FROM " . DB_FACEBOOK_GOODS_FEED . " WHERE  goodsNo = ? ";
        $this->db->bind_param_push($arrBind, 's', $goodsNo);
        $tmp = $this->db->query_fetch($strSQL, $arrBind, false);
        if(empty($tmp['additional_image_link']) === false) {
            $arrImage = explode(STR_DIVISION, $tmp['additional_image_link']);
        } else {
            $arrImage = '';
        }

        return $arrImage;

    }

    /**
     * 페이스북 제품 피드 설정값 저장
     * @param $goodsNo
     * @param $useFl
     * @param $image
     */
    public function setFacebookGoodsFeedData($goodsNo, $useFl, $image)
    {
        $arrBind = [];
        $strSQL = "SELECT * FROM ". DB_FACEBOOK_GOODS_FEED . " WHERE goodsNo = ? ";
        $this->db->bind_param_push($arrBind, 'i', $goodsNo);
        $hasData = $this->db->query_fetch($strSQL, $arrBind, false);
        unset($arrBind);

        $arrData = [
            'goodsNo'                   =>$goodsNo,
            'useFl'                     =>$useFl,
            'additional_image_link'     =>$image,
        ];

        if(empty($hasData)) { // insert
            $arrBind = $this->db->get_binding(DBTableField::tableFacebookGoodsFeed(), $arrData, 'insert');
            $this->db->set_insert_db(DB_FACEBOOK_GOODS_FEED, $arrBind['param'], $arrBind['bind'], 'y');
            unset($arrData);

        } else { // update
            $arrData = [
                'useFl'                =>$useFl,
                'additional_image_link'=>$image,
            ];
            $exclude = ['goodsNo'];
            $arrBind = $this->db->get_binding(DBTableField::tableFacebookGoodsFeed(), $arrData, 'update', null, $exclude);
            $strWhere = 'goodsNo ='. $goodsNo;
            $this->db->set_update_db(DB_FACEBOOK_GOODS_FEED, $arrBind['param'],$strWhere, $arrBind['bind']);
        }
    }

    /**
     * 페이스북 제품 피드 파일 생성
     * @param $data
     */
    public function makeFile($data, $isExtensionFile = false)
    {
        if(!is_dir(UserFilePath::data('facebookFeed')->getPathName())){
            mkdir(UserFilePath::data('facebookFeed')->getPathName(),0707);
        }

        if($isExtensionFile){
            $fileName = 'facebookFeedExtension.tsv';
        }else{
            $fileName = 'facebookFeed.tsv';
        }

        $fbFilePath = UserFilePath::data('facebookFeed', $fileName)->getRealPath();
        FileHandler::write($fbFilePath,  $data, 0707);

    }

    /**
     *  페이스북 제품 피드 생성된 상품번호를 between 사용해 조회
     *  tsv 형태로 피드 파일 생성
     */
    public function makeFbGoodsFeed($isExtensionFile = false)
    {
        $ssl = \App::load('\\Component\\SiteLink\\SecureSocketLayer');
        $policy = \App::load('\\Component\\Policy\\Policy');
        $sslConfig = $ssl->getSsl();
        // 보안서버 사용여부 체크
        if(empty($sslConfig) === false){
            foreach($sslConfig as $sslKey => $sslVal) {
                //pc 유료보안서버 사용시
                if($sslVal['sslConfigUse'] == 'y' && $sslVal['sslConfigPosition'] == 'pc' && $sslVal['sslConfigType'] == 'godo'){
                    $this->domainUrl = 'https://'.$sslVal['sslConfigDomain'].DS;
                }
            }
        }
        $data = gd_policy('basic.info', DEFAULT_MALL_NUMBER);
        if(empty($this->domainUrl)){
            $this->domainUrl = 'http://'. $data['mallDomain'] . DS;
        }
        $betweenGoodsNo = $this->getBetweenGoodsNo();

        $header = ['id', 'link', 'additional_image_link', 'image_link' , 'title', 'condition', 'availability', 'sale_price','price', 'description', 'brand', 'item_group_id', 'sale_price_effective_date'];
        $headerData = $feedData = $total ='';
        //facebook 제품 피드 헤드 설정
        foreach($header as $key => $val) {
            $headerData .= $val."\t";
        }
        $headerData .= "\n";
        $this->logger->info(__METHOD__ . 'tsv file make start:', [__CLASS__]);

        $this->progressNum = 1;
        foreach($betweenGoodsNo as $key) {
            $startGoodsNo = $key['startGoodsNo'];
            $endGoodsNo = $key['endGoodsNo'];

            $feedData = $this->selectGoodsInfo($startGoodsNo, $endGoodsNo);
            if(empty($feedData) === false){
                $this->logger->info(__METHOD__ . 'tsv file Generating startGoodsNo, endGoodsNo:', [__CLASS__, $startGoodsNo,$endGoodsNo]);
            } else {
                $this->logger->info(__METHOD__ . 'empty goodsNo between startGoodsNo, endGoodsNo:', [__CLASS__, $startGoodsNo,$endGoodsNo]);
            }
            $headerData .= $feedData;
        }
        if(empty($headerData) === false) {
            $this->makeFile($headerData, $isExtensionFile);
        }
        $this->logger->info(__METHOD__ . 'tsv file make complete:', [__CLASS__]);
    }

    /**
     * 페이스북 제품 피드 생성하는 상품번호 추출 후
     * 상품의 시작, 끝 번호를 100000 번씩 나누어 카운트하기 위한 배열 리턴.
     */
    public function getBetweenGoodsNo()
    {
        $limit = 100000;
        $between = [];

        $getFbGoodsNoSQL = 'SELECT max(goodsNo) AS endGoodsNo, min(goodsNo) AS startGoodsNo FROM '.DB_FACEBOOK_GOODS_FEED.' WHERE useFl="y"';
        $result = $this->db->query_fetch($getFbGoodsNoSQL, null, false);
        $start = $result['startGoodsNo'];
        $end = $result['endGoodsNo'];

        if($start == $end){
            $between[] = [
                'startGoodsNo'=> $start,
                'endGoodsNo'  => $end,
            ];
        } else {
            while ($start < $end) {
                $tmpEnd = $start + $limit;
                if($tmpEnd > $end) {
                    $tmpEnd = $end;
                }
                $between[] = [
                    'startGoodsNo' => $start,
                    'endGoodsNo'   => $tmpEnd,
                ];
                $start = $tmpEnd + 1;
            }
        }
        return $between;
    }

    /**
     * 페이스북 상품피드 생성하는 상품개수
     */
    public function getUseFeedGoodsCnt()
    {
        $sql = "SELECT COUNT(g.goodsNo) as cnt FROM ". DB_GOODS ." g INNER JOIN ".DB_FACEBOOK_GOODS_FEED." fb ON g.goodsNo = fb.goodsNo WHERE fb.useFl='y' AND g.goodsDisplayFl = 'y' AND g.delFl='n' AND g.applyFl='y'";
        if($this->feedGoodsCnt == null) {
            $feedGoodsCnt = $this->db->query_fetch($sql, null, false);
            $this->feedGoodsCnt = $feedGoodsCnt['cnt'];
        }
        return $this->feedGoodsCnt;
    }

    /**
     * FBE 설정 초기시
     * tsv 파일 생성 여부 체크 & 생성
     */
    public function checkTsvFile()
    {
        $tsvFile = UserFilePath::data('facebookFeed')->getRealPath().DS.'facebookFeedExtension.tsv';
        if(!file_exists($tsvFile)){ // facebookFeedExtension.tsv 파일이 없는경우에만 최초 생성
            $this->logger->info(__METHOD__ . 'First create TSV file for fbe settings :', [__CLASS__,$tsvFile]);
            $this->makeFbGoodsFeed(true);
            $FeedTsvfileExist = file_exists($tsvFile); // 파일생성여부 확인

            $openTsvFile = fopen($tsvFile,"r");
            $cntLine = 0;

            while(!feof($openTsvFile)){
                fgets($openTsvFile);
                $cntLine++;
            }
            fclose($openTsvFile);

            $cntLine = $cntLine - 2; // 헤더 필드, 마지막 공백 핃드 제거한 총 라인수
            $feedGoodsCnt = $this->getUseFeedGoodsCnt();
            if($FeedTsvfileExist && $cntLine == $feedGoodsCnt) { // 파일생성완료 && 필드개수검증
                $this->logger->info(__METHOD__ . 'Complete TSV file verification', [__CLASS__,$tsvFile]);
            } else {
                $this->logger->error(__METHOD__ . 'TSV file mismatch: $cntLine , $this->feedGoodsCnt', [$cntLine,$this->feedGoodsCnt]);
            }
            return true;
        } else { // 이미있는경우 스케줄러에서 자동갱신
            $this->logger->info(__METHOD__ . 'TSV file is already created:', [__CLASS__,$tsvFile]);
            return true;
        }
    }

    /**
     * dia settings param Json 생성
     */
    public function setDiaSettingsParam()
    {
        $fbeConfig= $this->getExtensionConfig();
        $request = \App::getInstance('request');
        $tmpParam = array();
        $tmpParam['clientSetup'] = [
            "popupOrigin" => 'https://www.facebook.com/ads/dia',
            "platform"=>'GODO'
        ];

        $tmpParam['clientSetup']['pixel'] = $fbeConfig['pixel_id'];
        $tmpParam['clientSetup']['diaSettingId'] = $fbeConfig['merchant_settings_id'];
        $tmpParam['clientSetup']['store'] = [
            'baseUrl'=>$request->getDomainUrl(),
            'baseCurrency'=>'KRW',
            'timezoneId'=>'79',
            'storeName'=>Globals::get('gMall.mallNm'),
            'version'=>'godomall5',
            'plugin_version'=>Globals::get('gLicense.version'),
            'php_version'=>phpversion()
        ];

        $cntFeedGoods = $this->getUseFeedGoodsCnt();
        $tmpParam['clientSetup']['feed'] = [
            "enabled"=> false, // 기존 값은 false로 되어있음.
            "format" => "TSV",
            "totalVisibleProducts" => $cntFeedGoods
        ];
        $tmpParam['clientSetup']['feedPrepared'] = [
            "feedUrl" => $request->getDomainUrl().'/data/facebookFeed/facebookFeedExtension.tsv',
        ];

        $sql = "SELECT * FROM ". DB_GOODS ." g INNER JOIN ".DB_FACEBOOK_GOODS_FEED." fb ON g.goodsNo = fb.goodsNo WHERE fb.useFl='y' AND g.goodsDisplayFl = 'y' AND g.delFl='n' AND g.applyFl='y' LIMIT 5 ";//fb 설정 된 상품으로 가져오기
        $result = $this->db->query_fetch($sql, null, true);
        $goods = \App::load('\\Component\\Goods\\Goods');
        $sample = '';
        $tmp=[];
        foreach($result as $key => $val){
            $goodsData = $goods->getGoodsView($val['goodsNo']);
            foreach($goodsData as $k => $v){
                if($k == 'goodsNo'){
                    $tmp['id'] = $v;
                    $tmp['link'] = URI_HOME . 'goods/goods_view.php?goodsNo=' . $v;
                    $tmp['image_link'] = $goodsData['social'];
                    $tmp['product_type'] = 'product';
                }elseif($k == 'goodsNm'){
                    $tmp['title'] = strip_tags($v);
                }elseif($k == 'shortDescription'){
                    $tmp['short_description'] = strip_tags($v);
                }elseif($k == 'brandNm'){
                    $tmp['brand'] = strip_tags($v);
                }elseif($k == 'goodsState'){
                    if($v == 'n')
                        $tmp['condition'] = 'new';
                    else if($v == 'u')
                        $tmp['condition'] = 'used';
                    else if($v == 'f')
                        $tmp['condition'] = 'refurbished';
                    else
                        $tmp['condition'] = 'used';
                }elseif($k == 'soldOutFl'){
                    if($v == 'n'){
                        $tmp['availability'] = 'in stock';
                    } else {
                        $tmp['availability'] = 'out of stock';
                    }
                }elseif($k == 'goodsPrice'){
                    $tmp['price'] = $v;
                }elseif($k == 'cateNm'){
                    $tmp['google_product_category'] = strip_tags($v);
                }
            }
            $sample[] = $tmp;
        }
        $tmpParam['clientSetup']['feedPrepared']['samples'] = $sample;
        unset($sample);
        $jsonParam = json_encode($tmpParam, JSON_UNESCAPED_UNICODE);
        return $jsonParam;
    }
    /**
     * 상품정보 추출 & tsv 형식으로 가공
     */
    public function selectGoodsInfo($startGoodsNo, $endGoodsNo)
    {
        $goodsData = \App::load('\\Component\\Goods\\Goods');
        $config = \App::load('\\Component\\Policy\\Policy');

        $goodsMainImage = [];
        $feedData = '';

        $bindParams = $whereParams = [];
        $whereParams[] = 'fb.useFl=\'y\' AND g.goodsDisplayFl = \'y\' AND g.delFl=\'n\' AND g.applyFl=\'y\'';
        $reqParams =['goodsNo'=>[$startGoodsNo, $endGoodsNo]];
        $this->db->bindParameterByRange('goodsNo', $reqParams, $bindParams, $whereParams,'tableFacebookGoodsFeed',' g');
        $getGoodsNoSQL = "SELECT g.goodsNo FROM ". DB_GOODS ." g INNER JOIN ".DB_FACEBOOK_GOODS_FEED." fb ON g.goodsNo = fb.goodsNo WHERE " . implode('AND', $whereParams);
        $rsGoodsNo = $this->db->query_fetch($getGoodsNoSQL, $bindParams);

        // $getGoodsNoSQL에서 추출한 goodsNo 이용해, 메인 이미지명 추출.
        $goodsInfoSQL = "SELECT g.goodsNo, gi.imageName FROM " . DB_GOODS_IMAGE . " gi INNER JOIN (". $getGoodsNoSQL .") g ON gi.goodsNo = g.goodsNo WHERE gi.imageKind = 'main'";
        $rsGoodsImageInfo = $this->db->query_fetch($goodsInfoSQL, $bindParams);

        // 이미지 이름 배열 재정렬
        foreach($rsGoodsImageInfo as $key => $val) {
            $goodsMainImage[$val['goodsNo']] = $val['imageName'];
        }

        //tsv 형식으로 데이터 가공
        foreach($rsGoodsNo as $key => $val) {
            //진행 퍼센트 계산 & 프로그레스바 실행
            if( $this->progressNum % 20 == 0 ) {
                echo "<script> parent.progressFbe('" . round((100 / ($this->getUseFeedGoodsCnt() - 3)) * $this->progressNum) . "'); </script>";
                flush();
                ob_flush();
            }
            $this->progressNum++;
            $tmpData = $goodsData->getGoodsView($val['goodsNo']);
            foreach($tmpData as $tmpKey => $tmpVal) {
                switch ($tmpKey) {
                    case 'goodsNo': // 상품번호
                        $feedData .= $tmpVal." \t"; //id
                        $feedData .= $this->domainUrl . 'goods/goods_view.php?goodsNo=' . $tmpVal . " \t"; // link

                        // additional_image_link
                        $additionImage = $this->getFaceBookGoodsImage($tmpVal);
                        foreach( $additionImage as $imgKey => $imgVal) {
                            // 외부저장소 사용
                            if(strtolower(substr($tmpData['imageStorage'],0,4) == 'http')){
                                if (strtolower(substr($imgVal, 0, 4)) == 'http') {
                                    $additionImage[$imgKey] = $imgVal;
                                } else {
                                    $storagePath = '/'.$config->getValue('basic.storage')['savePath']['imageStorage1'].'/goods/';
                                    $additionImage[$imgKey] = $tmpData['imageStorage'] . $storagePath . $tmpData['imagePath'] . $imgVal;
                                }
                            } else { // 내부저장소 사용 or url 직접입력
                                if (strtolower(substr($imgVal, 0, 4)) == 'http') {
                                    $additionImage[$imgKey] = $imgVal;
                                } else {
                                    $additionImage[$imgKey] = $this->domainUrl . 'data/goods/' . $tmpData['imagePath'] . $imgVal;
                                }
                            }
                        }
                        $feedData .= implode(",", $additionImage);
                        $feedData .= " \t";

                        // image_link (main image)
                        if(strtolower(substr($tmpData['imageStorage'],0,4) == 'http')) {
                            if (empty($goodsMainImage[$tmpVal]) === false) {
                                if (strtolower(substr($goodsMainImage[$tmpVal], 0, 4)) == 'http') {
                                    $feedData .= $goodsMainImage[$tmpVal] . " \t";
                                } else {
                                    $storagePath = '/'.$config->getValue('basic.storage')['savePath']['imageStorage1'].'/goods/';
                                    $feedData .= $tmpData['imageStorage'] . $storagePath . $tmpData['imagePath'] . $goodsMainImage[$tmpVal] . " \t";
                                }
                            } else {
                                $feedData .= $this->domainUrl . "null \t";
                            }
                        } else {
                            if (empty($goodsMainImage[$tmpVal]) === false) {
                                if (strtolower(substr($goodsMainImage[$tmpVal], 0, 4)) == 'http') {
                                    $feedData .= $goodsMainImage[$tmpVal] . " \t";
                                } else {
                                    $feedData .= $this->domainUrl . 'data/goods/' . $tmpData['imagePath'] . $goodsMainImage[$tmpVal] . " \t";
                                }
                            } else {
                                $feedData .= $this->domainUrl . "null \t";
                            }
                        }
                        break;
                    case 'soldOutFl':// in stock(재고있음), out of stock(재고없음),discontinued(판매종료)
                        if($tmpVal == 'y'){ // 품절(수동설정)
                            $feedData .= 'out of stock'." \t";
                        } else { // 품절아님
                            $now = new DateTime(date('Y-m-d H:i:s'));
                            $endDate = new DateTime($tmpData['salesEndYmd']);
                            $diffDate = $endDate->diff($now)->invert;
                            if($tmpData['stockFl'] == 'y') { // 재고량에 따르는 경우
                                if($tmpData['stockCnt'] > 0) {
                                    if($diffDate == 0) {
                                        if($tmpData['salesStartYmd'] =='0000-00-00 00:00:00' && $tmpData['salesEndYmd'] =='0000-00-00 00:00:00'){ //기간제한없음
                                            $feedData .='in stock'." \t";
                                        } else {
                                            $feedData .= 'discontinued' . " \t";
                                        }
                                    } else if($diffDate == 1) {
                                        $feedData .='in stock'." \t";
                                    }
                                } else {
                                    $feedData .= 'out of stock'." \t";
                                }
                            } else { // 무한정판매인 경우
                                if($diffDate == 0) {
                                    if($tmpData['salesStartYmd'] =='0000-00-00 00:00:00' && $tmpData['salesEndYmd'] =='0000-00-00 00:00:00'){ //기간제한없음
                                        $feedData .='in stock'." \t";
                                    } else {
                                        $feedData .= 'discontinued' . " \t";
                                    }
                                } else if($diffDate == 1) {
                                    $feedData .='in stock'." \t";
                                }
                            }
                        }
                        break;
                    case 'timeSaleFl': //sale_price_effective_date 설정.
                        $emptyDay = '0000-00-00T00:00:00+00:00';

                        // 타임세일과 상품별 할인/혜택 모두 사용할 경우 -- 타임세일 기간 사용
                        if($tmpVal == 'y' && $tmpData['goodsDiscountFl'] == 'y') {
                            //$periodDiscountStart = $periodDiscountEnd = 0;
                            $periodDiscountStart = $tmpData['timeSaleInfo']['startDt'];
                            $periodDiscountEnd = $tmpData['timeSaleInfo']['endDt'];
                            $feedData .= date('c', strtotime($periodDiscountStart))."/".date('c', strtotime($periodDiscountEnd))." \t";
                        } else if ($tmpVal == 'y') { // 타임세일만 사용할 경우
                            $startDt = date('c', strtotime($tmpData['timeSaleInfo']['startDt']));
                            $endDt = date('c', strtotime($tmpData['timeSaleInfo']['endDt']));
                            $feedData .= $startDt."/".$endDt." \t";
                        } else if ($tmpData['goodsDiscountFl'] == 'y') { // 상품별 할인/혜택만 사용할 경우
                            if($tmpData['benefitUseType'] == 'nonLimit'){
                                $feedData .= $emptyDay . "/" . $emptyDay . " \t";
                            } else if ($tmpData['benefitUseType'] == 'newGoodsDiscount') {
                                $startDt = date('c', strtotime($tmpData['regDt']));
                                $endDt = date('c', strtotime($tmpData['periodDiscountEndPrint']));
                                $feedData .= $startDt."/".$endDt." \t";
                            } else if ($tmpData['benefitUseType'] == 'periodDiscount') {
                                $startDt = date('c', strtotime($tmpData['periodDiscountStart']));
                                $endDt = date('c', strtotime($tmpData['periodDiscountEnd']));
                                $feedData .= $startDt."/".$endDt." \t";
                            }
                        } else {
                            $feedData .= $emptyDay . "/" . $emptyDay . " \t";
                        }
                        break;
                    // 상품명, 상품가격, 상품할인가격, 짧은설명, 브랜드명, 카테고리명
                    case 'goodsNm':
                        if($tmpVal) {
                            $feedData .= $tmpVal . " \t";
                        } else {
                            $feedData .= "no goods name \t";
                        }
                        break;
                    case 'goodsPrice':
                        if ($tmpData['timeSaleFl'] == 'y') {
                            $feedData .= $tmpData['oriGoodsPrice'] . " \t";
                        } else {
                            $feedData .= $tmpVal . " \t";
                        }
                        break;
                    case 'goodsDiscountFl':
                        if($tmpVal == 'y' && $tmpData['timeSaleFl'] == 'y') { //타임세일+상품별할인 둘다 사용시 타임세일가격설정
                            $feedData .= $tmpData['goodsPrice'] . " \t";
                        } else if ($tmpVal == 'y') { // 상품별할인만 사용시
                            $feedData .= $tmpData['goodsDiscountPrice'] . " \t";
                        } else if ($tmpData['timeSaleFl'] == 'y'){
                            $feedData .= $tmpData['goodsPrice'] . " \t";
                        } else {
                            $feedData .= '0'." \t";
                        }
                        break;
                    case 'shortDescription':
                        if($tmpVal) {
                            $feedData .= $tmpVal . " \t";
                        } else {
                            $feedData .= 'Short description not registered.'." \t";
                        }
                        break;
                    case 'brandNm':
                        if($tmpVal) {
                            $feedData .= $tmpVal . " \t";
                        } else {
                            $feedData .= "브랜드 미선택 \t";
                        }
                        break;
                    case 'cateNm':
                        if($tmpVal) {
                            $feedData .=  " \t";
                        } else {
                            $feedData .= '카테고리 미선택'." \t";
                        }
                        break;
                    case 'goodsState':
                        if($tmpVal == 'n') // 신상품
                            $feedData .= 'new' . " \t";
                        else if($tmpVal == 'u') // 중고
                            $feedData .= 'used' . " \t";
                        else if($tmpVal == 'f') // 리퍼
                            $feedData .= 'refurbished' . " \t";
                        else
                            $feedData .= "used \t";
                        break;
                }
            }
            $feedData .= "\n";
        }
        return $feedData;
    }
}
