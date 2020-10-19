<?php

namespace Bundle\Component\Promotion;

use Framework\Security\XXTEA;
use Framework\Debug\Exception\AlertBackException;
use Framework\Cache\CacheableProxyFactory;
use Exception;
use Component\Database\DBTableField;
use App;
use Framework\StaticProxy\Proxy\Cache;
use Session;
use Logger;

class InsgoWidget
{
    const CACHE_EXPIRE = 60*60;  //캐시 만료일(1시간)

    protected $_secretKey = 'insgowidget!23';
    protected $_insgoWidgetPath = '/share/insgo_widget.php';
    protected $_insgoAPI = 'https://api.instagram.com/v1/users/self/media/recent/?';
    protected $_insgoUri = 'https://www.instagram.com/';

    protected $scriptHead = '';
    protected $scriptFoot = '';
    protected $iframeId = 'insgoWidgetIframe';
    protected $db;
    protected $maxCount = 12;

    public function __construct()
    {
        if (gd_is_plus_shop(PLUSSHOP_CODE_INSGO) === false) {
            throw new AlertBackException(__('[플러스샵] 미설치 또는 미사용 상태입니다. 설치 완료 및 사용 설정 후 플러스샵 앱을 사용할 수 있습니다.'));
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

    public function getIframeHtml($iframeSize, $iframeUri)
    {
        $iframeHtml = '<iframe name="' . $this->iframeId . '" id="' . $this->iframeId . '" src="' . URI_HOME . $this->_insgoWidgetPath . '?' . $iframeUri . '" allowTransparency="true" frameborder="0" scrolling="no" style="border:none;overflow:hidden;' . $iframeSize . '"></iframe>';
        return $iframeHtml;
    }

    public function getInsgoWidgetData($postData,$insgoSno,$isCache = true)
    {
        if (is_array($postData)) {
            $dataArray = $postData;
        } else {
            $dataArray = $this->getUriHash($postData);
        }

        $parameter = $this->getParameter($dataArray);
        $apiUrl = $this->getInsgoWidgetApiUrl($parameter);
        $connetUrl = $this->getInsgoWidgetUrl($parameter);
        $responseData = $this->getInsogoData($connetUrl, 'n', $dataArray, $insgoSno, $isCache);
        $imageData['displayType'] = $dataArray['widgetDisplayType'];
        foreach ($responseData['data'] as $k => $v) {
            $imageData['thumbnails'][$k]['image'] = $v['images'];
            $imageData['thumbnails'][$k]['viewUrl'] = $v['link'];
        }
        $imageData['data'] = $dataArray;

        return $imageData;
    }

    protected function getInsgoWidgetApiUrl($param)
    {
        $retUrl = $this->_insgoAPI . http_build_query($param);

        return $retUrl;
    }

    /**
     * 인스타그램 API가 종료됨에 따른 인스고 URL 수정
     * @param $param
     * @return string
     */
    protected function getInsgoWidgetUrl($param)
    {
        $retUrl = $this->_insgoUri . $param['instagram_id'] . '/?__a=1';

        return $retUrl;
    }

    protected function getParameter($param)
    {
        $ret = [];
        $ret['instagram_id'] = $param['widgetInstagramId'];
        switch ($param['widgetDisplayType']) {
            case 'grid':
                $ret['count'] = ($param['widgetWidthCount'] * $param['widgetHeightCount'] > 12) ? $this->maxCount : $param['widgetWidthCount'] * $param['widgetHeightCount'];
                break;
            case 'scroll':
            case 'slide':
                $ret['count'] = $this->maxCount;
                break;
        }

        return $ret;
    }

    public function setWidgetData($data, $uri)
    {
        if (empty($data['widgetInstagramId']) === true) {
            throw new Exception(__('인스타그램 아이디를 확인해주세요.'));
        }

        if (empty($data['widgetDisplayType']) === true) {
            throw new Exception(__('위젯 타입을 확인해주세요.'));
        }

        $arrData['insgoId'] = $data['widgetInstagramId'];
        $arrData['insgoName'] = $data['widgetName'];
        $arrData['insgoDisplayType'] = $data['widgetDisplayType'];
        $arrData['insgoWidthCount'] = $data['widgetWidthCount'];
        $arrData['insgoHeightCount'] = $data['widgetHeightCount'];
        $arrData['insgoThumbnailSize'] = $data['widgetThumbnailSize'];
        $arrData['insgoThumbnailSizePx'] = $data['widgetThumbnailSizePx'];
        $arrData['insgoThumbnailBorder'] = $data['widgetThumbnailBorder'];
        $arrData['insgoBackgroundColor'] = $data['widgetBackgroundColor'];
        $arrData['insgoImageMargin'] = $data['widgetImageMargin'];
        $arrData['insgoOverEffect'] = $data['widgetOverEffect'];
        $arrData['insgoWidth'] = $data['widgetWidth'];
        $arrData['insgoAutoScroll'] = $data['widgetAutoScroll'];
        $arrData['insgoScrollSpeed'] = $data['widgetScrollSpeed'];
        $arrData['insgoScrollTime'] = $data['widgetScrollTime'];
        $arrData['insgoSideButtonColor'] = $data['widgetSideButtonColor'];
        $arrData['insgoEffect'] = $data['widgetEffect'];
        $arrData['insgoData'] = $uri;
        $arrData['insgoManagerNo'] = Session::get('manager.sno');

        if ($data['widgetSno']) { // 수정
            $arrData['sno'] = $data['widgetSno'];
            $arrBind = $this->db->get_binding(DBTableField::tableInsgoWidget(), $arrData, 'update', array_keys($arrData));
            $this->db->bind_param_push($arrBind['bind'], 'i', $data['widgetSno']);
            $this->db->set_update_db(DB_INSGO_WIDGET, $arrBind['param'], 'sno = ?', $arrBind['bind']);
        } else { // 등록
            $arrBind = $this->db->get_binding(DBTableField::tableInsgoWidget(), $arrData, 'insert');
            $this->db->set_insert_db(DB_INSGO_WIDGET, $arrBind['param'], $arrBind['bind'], 'y');
        }
    }

    public function getData($sno = null, $arrInclude = null, $returnArray = true, $tuning = false)
    {

        $arrField = DBTableField::setTableField('tableInsgoWidget', $arrInclude, null, 'i');

        $arrBind = $arrWhere = [];
        if (isset($sno)) {
            $arrWhere[] = 'i.sno = ?';
            $this->db->bind_param_push($arrBind, 'i', $sno);
        }

        $this->db->strField = implode(', ', $arrField) . ', m.managerId AS insgoManagerId, m.managerNm AS insgoManagerNm';
        $this->db->strWhere = implode(' AND ', gd_isset($arrWhere));
        $this->db->strOrder = 'i.sno desc';
        $this->db->strJoin = 'LEFT JOIN ' . DB_MANAGER . ' AS m ON m.sno = i.insgoManagerNo';

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_INSGO_WIDGET . ' i ' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $arrBind, $returnArray);
        if (isset($sno) && $tuning) {
            foreach ($data as $key => $val) {
                $newKey = str_replace('insgo', 'widget', $key);
                $tmpData[$newKey] = $val;
            }
            $data = $this->dataTuning($tmpData);
        }

        unset($arrBind);
        unset($arrWhere);
        unset($tmpData);
        return $data;
    }

    public function dataTuning($data)
    {
        /* 공통정보 */
        $returnArray['widgetSno'] = $data['sno']; // sno
        $returnArray['widgetAccessToken'] = $data['widgetAccessToken']; // 엑세스토큰
        $returnArray['widgetInstagramId'] = $data['widgetId']; // 인스타그램 아이디
        $returnArray['widgetName'] = $data['widgetName']; // 위젯명
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

    public function deleteData($data)
    {
        foreach ($data as $key => $val) {
            $strSQL = "DELETE FROM " . DB_INSGO_WIDGET . " WHERE sno = " . $val;
            $this->db->query($strSQL);
        }
    }

    public function getCount()
    {
        $strSQL = "SELECT COUNT(*) cnt FROM " . DB_INSGO_WIDGET;
        $result = $this->db->query_fetch($strSQL, null, false);
        return $result['cnt'];
    }

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
                Logger::channel('insgo')->info(' InsgoWidget Cache Renewal');
                $result= $this->getCurlOutput($curlUrl,$postType,$postData);
                if(is_array($result) && isset($result['data'])){    //값이 있는경우
                    Logger::channel('insgo')->info(' InsgoWidget Cache Produce');
                    if($insgoSno){
                        $this->makeCacheFile($insgoSno,json_encode($result,JSON_UNESCAPED_UNICODE));
                    }
                }
                else {  //호출 오류면 캐시에서 가져온다.
                    Logger::channel('insgo')->error(' InsgoWidget Error Response Cache');
                    //$result = json_decode(file_get_contents($this->getCachePath($insgoSno)),true);
                }
            }
            else {  //안지났으면 캐시에서 데이터 로드
                Logger::channel('insgo')->info(' InsgoWidget Response Cache');
                $result = json_decode(file_get_contents($this->getCachePath($insgoSno)),true);
            }
        }
        else {  //캐시 없으면 curl통신 & 캐시 생성
            Logger::channel('insgo')->info(' InsgoWidget Cache empty, Curl Communication');
            $result= $this->getCurlOutput($curlUrl,$postType,$postData);
            $this->makeCacheFile($insgoSno,json_encode($result,JSON_UNESCAPED_UNICODE));
        }

        return $result;
    }

    protected  function getCurlOutput($curlUrl,$postType,$postData)
    {
        // 그리드의 경우
        $layoutCnt = $postData['widgetWidthCount'] * $postData['widgetHeightCount'];
        Logger::channel('insgo')->info(__METHOD__ . ' InsgoWidget CURL Start URL: ' . $curlUrl . ' Data: ', [$postData]);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $curlUrl);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/48.0.2564.103 Safari/537.36');
        curl_setopt($ch, CURLOPT_REFERER, 'https://www.instagram.com/');
        $result = html_entity_decode(curl_exec($ch), true);
        curl_close($ch);

        Logger::channel('insgo')->info(__METHOD__ . ' InsgoWidget CURL END URL: ' . $curlUrl . ' Result: ', [$result]);
        preg_match_all('/.thumbnail_src(.*),/i', $result, $match);
        preg_match_all('/.shortcode(.*)/i', $result, $viewCode);

        $arrImgData = explode(',', preg_replace("/[\"\']/i", "", $match[0][0]));
        $arrCodeData = explode(',', preg_replace("/[\"\']/i", "", $viewCode[0][0]));
        Logger::channel('insgo')->info(__METHOD__ . ' InsgoWidget CURL ', ['arrImgData' => $arrImgData, 'CodeData' => $arrCodeData]);

        if(strpos($arrImgData[0], 'jpg')){
            foreach($arrImgData as $img){
               $tmpImgData[] = strstr($img, 'thumbnail_src');
            }
            foreach($arrCodeData as $code){
                $tmpCodeData[] = strstr($code, 'shortcode');
            }

            if($postData['widgetDisplayType'] == 'grid'){
                $arrData[] = array_splice(str_replace('thumbnail_src:', "", array_values(array_filter($tmpImgData))), 0, $layoutCnt);
                $arrData[] = array_splice(str_replace('shortcode:', "", array_values(array_filter($tmpCodeData))), 0, $layoutCnt);
            }else{
                $arrData[] = str_replace('thumbnail_src:', "", array_values(array_filter($tmpImgData)));
                $arrData[] = str_replace('shortcode:', "", array_values(array_filter($tmpCodeData)));
            }

            foreach($arrData[0] as $k => $urlData){
                $data['data'][$k]['images']['thumbnail']['url'] = str_replace('\u0026', '&', $urlData);
                $data['data'][$k]['images']['low_resolution']['url'] = str_replace('\u0026', '&', $urlData);
                $data['data'][$k]['images']['standard_resolution']['url'] = str_replace('\u0026', '&', $urlData);
            }
            foreach($arrData[1] as $k => $viewCode){
                $data['data'][$k]['link'] = 'https://www.instagram.com/p/' . $viewCode;
            }
            Logger::channel('insgo')->info(__METHOD__ . ' InsgoWidget CURL Rework');
        }else{
            Logger::channel('insgo')->error(__METHOD__ . ' InsgoWidget CURL Data empty Fail');
        }

        return $data;
    }

    public function makeCacheFile($index,$contents)
    {
        \FileHandler::write($this->getCachePath($index), $contents);
    }

    public function removeCacheFile($index)
    {
        \FileHandler::delete($this->getCachePath($index));
    }

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
}
