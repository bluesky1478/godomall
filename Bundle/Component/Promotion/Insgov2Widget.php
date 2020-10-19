<?php

namespace Bundle\Component\Promotion;

use Framework\Security\XXTEA;
use Framework\Debug\Exception\AlertBackException;
use Exception;
use Component\Database\DBTableField;
use App;
use Session;
use Logger;

class Insgov2Widget
{
    const CACHE_EXPIRE = 60*60;  //캐시 만료일(1시간)

    protected $_secretKey = 'insgowidget!23';
    protected $_insgoWidgetPath = '/share/insgov2_widget.php';
    protected $_insgoMediaAPI = 'https://graph.instagram.com/me/media?';

    protected $iframeId = 'insgov2WidgetIframe';
    protected $db;
    protected $maxCount = 24;

    public function __construct()
    {
        if (gd_is_plus_shop(PLUSSHOP_CODE_INSGOV2) === false) {
            throw new AlertBackException(__('[플러스샵] 미설치 또는 미사용 상태입니다. 설치 완료 및 사용 설정 후 플러스샵 앱을 사용할 수 있습니다..'));
        }

        $db = App::load('DB');
        $this->db = $db;
    }

    public function setUriHash($returnArray)
    {
        $xxtea = new XXTEA();
        $xxtea->setKey($this->_secretKey);

        return base64_encode($xxtea->encrypt(serialize($returnArray)));
    }

    public function getUriHash($queryString)
    {
        $xxtea = new XXTEA();
        $xxtea->setKey($this->_secretKey);
        return unserialize($xxtea->decrypt(base64_decode($queryString)));
    }

    /**
     * 인스고 토큰 통신 후, 최초 저장 시, 설정 기본값 저장 필요
     * @return string
     */
    public function setInsgoDataSetting()
    {
        /* 공통정보 */
        $returnArray['widgetSno'] = 1; // sno
        $returnArray['widgetDisplayType'] = 'grid'; // 위젯타입
        $returnArray['widgetThumbnailSize'] = 'auto';   // 썸네일사이즈
        $returnArray['widgetThumbnailBorder'] = 'y'; // 이미지테두리
        $returnArray['widgetOverEffect'] = 'n'; // 마우스오버시효과
        $returnArray['widgetWidthCount'] = 6; // 레이아웃(가로)
        $returnArray['widgetHeightCount'] = 4; // 레이아웃(세로)
        $returnArray['widgetBackgroundColor'] = '#ffffff'; // 위젯배경색
        $returnArray['widgetImageMargin'] = 5; // 이미지간격

        // 인스고 설정값 암호화
        $iframeUri = $this->setUriHash($returnArray);
        if(!$iframeUri) {
            throw new LayerException('fail to get iframe uri.');
        }

        // 인스고 미디어파일(이미지) size 설정
        $iframeSize = $this->getIframeSize($returnArray);

        // 인스고 위젯 프레임 설정
        $iframeHtml = $this->getIframeHtml($iframeSize, $iframeUri);
        if(!$iframeHtml) {
            throw new LayerException('fail to get iframe html');
        }

        return $iframeUri;
    }

    /**
     * 인스고 통신 완료 토큰 최초 저장(es_config 저장)
     *
     * @param $data
     * @return mixed
     */
    public function saveAccessToken($data)
    {
        if(empty($data['access_token']) === true){
            Logger::channel('insgo')->error(__METHOD__ . ' Insgov2Widget Empty Code, FAIL Connection: ' . [$data]);
            $res['result'] = 'fail';
        }else {
            $config['sno'] = 1; // 인스고위젯 번호 지정(es_config테이블 사용할때)
            $config['insgoManagerNo'] = Session::get('manager.sno'); // 수정자
            $config['accessToken'] = $data['access_token']; // 엑세스토큰(장기)
            $config['expDt'] = $data['expDt']; // 장기토큰 만료일
            $config['shopSno'] = $data['shopSno']; // 상점번호
            // 최초 저장 시, 설정값 세팅
            $config['insgoData'] = $this->setInsgoDataSetting();
            gd_set_policy('promotion.insgo', $config);

            Logger::channel('insgo')->info(__METHOD__ . ' Insgov2Widget tokenSave: ' . [$data]);
            $res['result'] = 'success';
        }

        return $res;
    }

    /**
     * 인스고 새로고침 장기토큰 저장(스케줄러)
     *
     * @param $data
     */
    public function saveRefreshAccessToken($data)
    {
        if(empty($data['access_token']) === false) {
            $config['insgoManagerNo'] = Session::get('manager.sno'); // 수정자
            $config['accessToken'] = $data['access_token']; // 엑세스토큰(장기)
            $config['expDt'] = $data['expDt']; // 장기토큰 만료일
            $config['shopSno'] = $data['shopSno']; // 상점번호
            gd_set_policy('promotion.insgo', $config);

            Logger::channel('insgo')->info(__METHOD__ . ' Insgov2Widget Refresh AccessToken Save: ' . [$data]);
        }else{
            Logger::channel('insgo')->info(__METHOD__ . ' Insgov2Widget Refresh AccessToken Fail: ' . [$data]);
        }
    }

    /**
     * 인스고 미디어파일(이미지) size 설정
     *
     * @param $returnArray
     * @return string
     */
    public function getIframeSize($returnArray)
    {

        $width = $height = 0;
        $iframeSize = '';

        if ($returnArray['widgetThumbnailSize'] == 'auto') {
            $iframeSize = 'width:100%;';
        } else {
            $width = ($returnArray['widgetThumbnailSizePx'] * $returnArray['widgetWidthCount']) + (($returnArray['widgetImageMargin'] * $returnArray['widgetWidthCount']) - $returnArray['widgetImageMargin']) + 5;
            $height = ($returnArray['widgetThumbnailSizePx'] * $returnArray['widgetHeightCount']) + (($returnArray['widgetImageMargin'] * $returnArray['widgetHeightCount']) - $returnArray['widgetImageMargin']) + 5;
            if ($returnArray['widgetThumbnailBorder'] == 'y') {
                $width += $returnArray['widgetThumbnailSizePx'] * 2;
                $height += $returnArray['widgetThumbnailSizePx'] * 2;
            }
            $iframeSize = 'width:' . $width . 'px;';
            $iframeSize .= 'height:' . $height . 'px;';
        }

        return $iframeSize;
    }

    /**
     * 인스고 위젯 프레임 설정
     *
     * @param $iframeSize
     * @param $iframeUri
     * @return string
     */
    public function getIframeHtml($iframeSize, $iframeUri)
    {
        $iframeHtml = '<iframe name="' . $this->iframeId . '" id="' . $this->iframeId . '" src="' . URI_HOME . $this->_insgoWidgetPath . '?' . $iframeUri . '" allowTransparency="true" frameborder="0" scrolling="no" style="border:none;overflow:hidden;' . $iframeSize . '"></iframe>';
        return $iframeHtml;
    }

    /**
     * 인스고 위젯 미리보기(관리자, 프론트)
     *
     * @param $postData
     * @param $insgoSno
     * @param bool $isCache
     * @return mixed
     * @throws Exception
     */
    public function getInsgoWidgetData($postData,$insgoSno,$isCache = true)
    {
        if (is_array($postData)) {
            $dataArray = $postData;
        } else {
            $dataArray = $this->getUriHash($postData);
        }

        // 인스고 통신 정보 세팅
        $apiPostData['fields'] = 'id,caption,media_type,media_url,thumbnail_url,permalink,timestamp';
        $apiPostData['access_token'] = $dataArray['widgetAccessToken'];
        $apiPostData['count'] = $this->getParameter($dataArray)['count'];
        $apiUrl = $this->getInsgoWidgetApiUrl($apiPostData);
        $responseData = $this->getInsogoData($apiUrl, 'n', $dataArray, $insgoSno, $isCache);

        // 통신완료 후, 미디어 데이터 가공
        $data['displayType'] = $dataArray['widgetDisplayType'];
        if($responseData['data']){
            foreach ($responseData['data'] as $k => $v) {
                $data['thumbnails'][$k]['image'] = $v['images'];
                $data['thumbnails'][$k]['viewUrl'] = $v['link'];
            }
            $data['data'] = $dataArray;
        }else{
            $data['error'] = $responseData['error'];
        }

        return $data;
    }

    /**
     * 인스고 API url세팅
     *
     * @param $param
     * @return string
     */
    protected function getInsgoWidgetApiUrl($param)
    {
        $retUrl = $this->_insgoMediaAPI . http_build_query($param);

        return $retUrl;
    }

    protected function getParameter($param)
    {
        $ret = [];

        switch ($param['widgetDisplayType']) {
            case 'grid':
                $ret['count'] = ($param['widgetWidthCount'] * $param['widgetHeightCount'] > 25) ? $this->maxCount : $param['widgetWidthCount'] * $param['widgetHeightCount'];
                break;
            case 'scroll':
            case 'slide':
                $ret['count'] = $this->maxCount;
                break;
        }

        return $ret;
    }

    /**
     * 인스고위젯 데이터 업데이트
     *
     * @param $data
     * @param $uri
     * @throws Exception
     */
    public function setWidgetData($data, $uri)
    {
        // 인스고 설정값
        $insgoConfig = gd_policy('promotion.insgo');

        if(empty($insgoConfig['accessToken']) === true){
            throw new Exception(__('인스타그램을 연동해 주세요.'));
        }

        if (empty($data['widgetDisplayType']) === true) {
            throw new Exception(__('위젯 타입을 확인해주세요.'));
        }

        $config['insgoData'] = $uri; // 인스고위젯 설정 정보 암호화값
        $config['insgoManagerNo'] = Session::get('manager.sno'); // 수정자
        gd_set_policy('promotion.insgo', $config);

        Logger::channel('insgo')->error(__METHOD__ . ' Insgov2Widget UPDATE: ' . [$data]);
    }

    /**
     * 인스고 위젯 관리 데이터(insgo_widget_config.php, 미리보기, 프론트)
     *
     * @param null $sno
     * @param null $arrInclude
     * @param bool $returnArray
     * @param bool $tuning true: config.php, false: 미리보기&프론트
     * @return mixed
     */
    public function getData($sno = null, $tuning = false)
    {
        // 인스고 설정값
        $insgoConfig = gd_policy('promotion.insgo');

        // 인스고 설정값 복호화
        $arrData = $this->getUriHash($insgoConfig['insgoData']);

        // 인스고위젯관리 데이터
        if ($tuning) { // 인스고위젯관리(관리자)
            foreach ($arrData as $key => $val) {
                $newKey = str_replace('insgo', 'widget', $key);
                $tmpData[$newKey] = $val;
            }
            $data = $this->dataTuning($tmpData);
        }else{ // 인스고 미리보기(관리자), 프론트
            $data = $arrData;
        }
        // 공통 정보
        $data['displayType'] = $arrData['widgetDisplayType'];   // 위젯 타입
        $data['thumbnailSize'] = $arrData['widgetThumbnailSize'];   // 썸네일 사이즈
        $data['thumbnailBorder'] = $arrData['widgetThumbnailBorder'];   // 이미지 테두리
        $data['thumbnailSizePx'] = $arrData['widgetThumbnailSizePx'];   // 썸네일 사이즈가 수동인 경우 px값
        $data['overEffect'] = $arrData['widgetOverEffect']; // 마우스 오버 시 효과

        // 위젯타입 - 스크롤
        $data['width'] = $arrData['widgetWidth']; // 위젯 가로사이즈
        $data['autoScroll'] = $arrData['widgetAutoScroll']; // 자동스크롤
        $data['sideButtonColor'] = $arrData['widgetSideButtonColor']; // 좌우 전환 버튼 색상값
        $data['scrollSpeed'] = $arrData['widgetScrollSpeed'];   // 전환속도 선택

        // 위젯타입 - 슬라이드
        $data['scrollTime'] = $arrData['widgetScrollTime']; // 전환시간 선택
        $data['effect'] = $arrData['widgetEffect']; // 효과 선택

        $data['widgetSno'] = $insgoConfig['sno'];
        $data['widgetAccessToken'] = $insgoConfig['accessToken'];

        return $data;
    }

    /**
     * 인스고위젯관리 데이터 튜닝
     *
     * @param $data
     * @return mixed
     */
    public function dataTuning($data)
    {
        /* 공통정보 */
        $returnArray['widgetDisplayType'][$data['widgetDisplayType']] = 'checked="checked"'; // 위젯타입
        $returnArray['widgetThumbnailSize'][$data['widgetThumbnailSize']] = 'checked="checked"'; // 썸네일사이즈
        if ($data['widgetThumbnailSize'] == 'hand') {
            $returnArray['widgetThumbnailSizePx'] = $data['widgetThumbnailSizePx']; // 썸네일사이즈(수동 픽셀)
        }
        $returnArray['widgetThumbnailBorder'][$data['widgetThumbnailBorder']] = 'checked="checked"'; // 이미지테두리
        $returnArray['widgetOverEffect'][$data['widgetOverEffect']] = 'checked="checked"'; // 마우스오버시효과

        switch ($data['widgetDisplayType']) {
            case 'grid':
                $returnArray['widgetWidthCount'] = $data['widgetWidthCount']; // 레이아웃(가로)
                $returnArray['widgetHeightCount'] = $data['widgetHeightCount']; // 레이아웃(세로)
                $returnArray['widgetBackgroundColor'] = $data['widgetBackgroundColor']; // 위젯배경색
                $returnArray['widgetImageMargin'] = $data['widgetImageMargin']; // 이미지간격
                break;
            case 'scroll':
                $returnArray['widgetWidth'] = $data['widgetWidth']; // 위젯가로사이즈
                $returnArray['widgetAutoScroll'][$data['widgetAutoScroll']] = 'checked="checked"'; // 자동스크롤
                $returnArray['widgetScrollSpeed'][$data['widgetScrollSpeed']] = 'selected="selected"'; // 전환속도선택
                $returnArray['widgetSideButtonColor'] = $data['widgetSideButtonColor'] == '' ? '#ffffff' : $data['widgetSideButtonColor']; // 좌우전환버튼색상
                break;
            case 'slide':
                $returnArray['widgetBackgroundColor'] = $data['widgetBackgroundColor']; // 위젯배경색
                $returnArray['widgetScrollSpeed'][$data['widgetScrollSpeed']] = 'selected="selected"'; // 전환속도선택
                $returnArray['widgetScrollTime'][$data['widgetScrollTime']] = 'selected="selected"'; // 전환시간선택
                $returnArray['widgetEffect'][$data['widgetEffect']] = 'checked="checked"'; // 효과선택
                break;
            default:
                break;
        }
        return $returnArray;
    }

    /**
     * 인스고 위젯 데이터
     *
     * @param $curlUrl
     * @param $postType
     * @param array $postData
     * @param null $insgoSno
     * @param bool $isCache
     * @return mixed
     * @throws Exception
     */
    public function getInsogoData($curlUrl, $postType, $postData = [],$insgoSno = null,$isCache = true)
    {
        if($isCache === false){
            return $this->getCurlOutput($curlUrl,$postType,$postData);
        }

        $path = $this->getCachePath($insgoSno);

        //캐시 유무 체크
        if(\FileHandler::isExists($path)){   // 캐시 있으면 1시간 지났나 체크
            $fileInfo = \FileHandler::getFileInfo($path);
            $cacheExpireDate = $fileInfo->getMTime()+(self::CACHE_EXPIRE);

            if(time() > $cacheExpireDate){  // 1시간 지났으면 curl호출 + 캐시 갱신
                Logger::channel('insgo')->info(' Insgov2Widget Renewal Cache');
                $result= $this->getCurlOutput($curlUrl,$postType,$postData);

                if(is_array($result) && isset($result['data'])){    //값이 있는경우
                    Logger::channel('insgo')->info(' Insgov2Widget Produce Cache');
                    if($insgoSno){
                        $this->makeCacheFile($insgoSno,json_encode($result,JSON_UNESCAPED_UNICODE));
                    }
                }
                else {  //호출 오류면 캐시파일 노출
                    Logger::channel('insgo')->error(__METHOD__ . ' Insgov2Widget Renewal Error, Response Cache: ' . [$result]);
                    //throw new Exception('인스타그램 연동에 실패하였습니다. 다시 연결하시기 바랍니다. [' . $result['code'] . ']');
                    $result = json_decode(file_get_contents($this->getCachePath($insgoSno)),true);
                }
            } else {  //안지났으면 캐시에서 데이터 로드
                Logger::channel('insgo')->info(' Insgov2Widget Response Cache');
                $result = json_decode(file_get_contents($this->getCachePath($insgoSno)),true);
            }
        }
        else {  //캐시 없으면 curl통신 & 캐시 생성
            Logger::channel('insgo')->info(' Insgov2Widget Empty Cache, Curl Communication');
            $result= $this->getCurlOutput($curlUrl,$postType,$postData);
            if(empty($result['error']) === true){
                $this->makeCacheFile($insgoSno,json_encode($result,JSON_UNESCAPED_UNICODE));
            }
        }

        return $result;
    }

    /**
     * 인스고 위젯 데이터 캐시 및 media 정보 통신
     *
     * @param $curlUrl
     * @param $postType
     * @param $postData
     * @return mixed
     */
    protected  function getCurlOutput($curlUrl,$postType,$postData)
    {
        Logger::channel('insgo')->info(__METHOD__ . ' Insgov2Widget CURL Start URL: ' . $curlUrl . ' Data: ', [$postData]);

        // Curl
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $curlUrl);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $result = curl_exec($ch);
        curl_close($ch);
        $decodeResponse = json_decode($result, true);

        if($decodeResponse['error']){
            $data['error'] = $decodeResponse['error'];
        }else {
            if(empty($decodeResponse['data']) === true){
                $data['error']['msg'] = 'empty InsgoData';
                $data['error']['code'] = '000';
                Logger::channel('insgo')->info(__METHOD__ . ' Insgov2Widget CURL Connection Success, Data empty: ' . $curlUrl . ' Data: ', [$data]);
            }else{
                Logger::channel('insgo')->info(__METHOD__ . ' Insgov2Widget CURL Connection Success, Data Not empty: ' . $curlUrl);
                $arrData['data'] = $decodeResponse['data'];
                foreach($arrData['data'] as $key => $val){
                    if($val['media_type'] == 'VIDEO'){
                        $data['data'][$key]['images']['thumbnail']['url'] = $val['thumbnail_url'];
                        $data['data'][$key]['images']['low_resolution']['url'] = $val['thumbnail_url'];
                        $data['data'][$key]['images']['standard_resolution']['url'] = $val['thumbnail_url'];
                    }else{
                        $data['data'][$key]['images']['thumbnail']['url'] = $val['media_url'];
                        $data['data'][$key]['images']['low_resolution']['url'] = $val['media_url'];
                        $data['data'][$key]['images']['standard_resolution']['url'] = $val['media_url'];
                    }
                    $data['data'][$key]['link'] = $val['permalink'];
                }
            }
        }

        return $data;
    }

    /**
     * 인스고 위젯 캐시 파일 생성
     *
     * @param $index
     * @param $contents
     */
    public function makeCacheFile($index,$contents)
    {
        \FileHandler::write($this->getCachePath($index), $contents);
    }

    /**
     * 인스고 위젯 캐시 파일 삭제
     *
     * @param $index
     */
    public function removeCacheFile($index)
    {
        \FileHandler::delete($this->getCachePath($index));
    }

    /**
     * 인스고 위젯 캐시 파일 저장 경로
     *
     * @param null $insgoSno
     * @return string
     */
    protected function getCachePath($insgoSno = null)
    {
        return implode(
            DIRECTORY_SEPARATOR,
            [
                USERPATH,
                'tmp',
                'insgo',
//                date('Y-m-d', strtotime($day . ' day')) . '.json',
//                date('Y-m-d') . '_insgo.json',
                'insgo_'.$insgoSno.'.json',
            ]
        );
    }

    /**
     * 인스고위젯 전환 동의 저장
     *
     * @param $data
     * @throws Exception
     */
    public function saveInsgoAgree($data)
    {
        // 연동 방식 전환 동의
        if($data['agreeFl'] == 'y'){
            Logger::channel('insgo')->info(__METHOD__ . ' Insgo API agree: ' . ['id: ' . Session::get('manager.managerId') . 'date: ' . date("Y-m-d H:i:s")]);

            // 인스고위젯 기존 데이터 초기화
            $this->deleteInsgoData();

            $config['agreeFl'] = $data['agreeFl'];
            gd_set_policy('promotion.insgo', $config);
        }else{
            throw new Exception('저장이 실패되었습니다. 다시 시도해주세요.');
        }
    }

    /**
     * 인스고위젯 테이블 초기화(인스고위젯 방식 변환에 동의하면)
     *
     */
    public function deleteInsgoData()
    {
        $query = "TRUNCATE TABLE " . DB_INSGO_WIDGET;
        $this->db->query($query);
    }

    /**
     * 인스고위젯 API 연동 해제
     */
    public function insgoConnectRelease()
    {
        $config['agreeFl'] = 'y';
        $config['insgoManagerNo'] = Session::get('manager.sno'); // 수정자
        gd_set_policy('promotion.insgo', $config, false);

        \FileHandler::delete($this->getCachePath(1));

        Logger::channel('insgo')->info(__METHOD__ . ' Insgo API Release: ' . ['managerNo: ' . Session::get('manager.sno') . 'date: ' . date("Y-m-d H:i:s")]);
    }

    /**
     * 인스고 장기토큰 새로고침 대상 조회
     *
     * @param $nowDt
     * @param $expDt
     */
    public function getTokenByExpireDate()
    {
        $insgoConfig = gd_policy('promotion.insgo');
        $insgoApi = new GodoInsgoServerApi();
        $insgoApi->refreshToken($insgoConfig['accessToken']);
    }

}
