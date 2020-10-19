<?php
/**
 * Created by PhpStorm.
 * User: godo
 * Date: 2018-01-17
 * Time: 오후 2:01
 */

namespace Bundle\Controller\Admin\Marketing;

use Framework\Debug\Exception\AlertOnlyException;
use UserFilePath;
use Request;

/**
 * 페이스북 광고 사용 설정
 * @author Park Sojeong <psj6414@godo.co.kr>
 */
class FacebookAdsConfigController extends \Controller\Admin\Controller
{
    public function index()
    {
        // 메뉴 설정
        $this->callMenu('marketing','viral','facebookDA');
        
        // 페이지 데이터
        try{
            $dbUrl = \App::load('\\Component\\Marketing\\DBUrl');
            $data = $dbUrl->getConfig('facebook', 'config');
            $fbExtensionData = $dbUrl->getConfig('facebookExtension', 'config')['value'];
            if(empty($data) === true){ // 페이스북 광고 수동설치 사용 안함
                if(empty($fbExtensionData) === true || $fbExtensionData['fbUseFl'] == 'n'){ // 새버전 사용전
                    $useFbCheckValue = "newVersionstart";
                }else{
                    $useFbCheckValue = "newVersionModify";
                }
            }else { // 페이스북 광고 수동설치 사용중
                if(empty($fbExtensionData) === true || $fbExtensionData['fbUseFl'] == 'n'){ // 새버전 사용전
                    $useFbCheckValue = "oldVersion";
                }else{
                    $useFbCheckValue = "newVersionModify";
                }
            }

            gd_isset($data['fbUseFl'], 'n');
            gd_isset($data['fixelId'], '');
            gd_isset($data['goodsViewScriptFl'], 'y');
            gd_isset($data['cartScriptFl'], 'y');
            gd_isset($data['orderEndScriptFl'], 'y');
            gd_isset($data['commonScriptFl'], 'y');

            $fb = \App::load('\\Component\\Marketing\\FacebookAd');
            $useFeedDataCnt = $fb->getUseFeedGoodsCnt();
            $checked = [];
            $checked['fbUseFl'][$data['fbUseFl']] = $checked['goodsViewScriptFl'][$data['goodsViewScriptFl']] = $checked['cartScriptFl'][$data['cartScriptFl']] = $checked['orderEndScriptFl'][$data['orderEndScriptFl']] = 'checked="checked"';
            $tsvFile = UserFilePath::data('facebookFeed')->getRealPath().DS.'facebookFeedExtension.tsv';
            if(file_exists($tsvFile)){
                $makeFileTime = date("Y-m-d H:i:s",filemtime(UserFilePath::data('facebookFeed', 'facebookFeedExtension.tsv')->getRealPath()));
            }
        }catch (\Exception $e){
            throw new AlertOnlyException($e->getMessage());
        }
        
        // 관리자 디자인 템플릿
        $this->setData('data',gd_isset($data));
        $this->setData('checked',gd_isset($checked));
        $this->setData('settingsParam', gd_isset($settingsParam, "0"));
        $this->setData('fbeData', $fbExtensionData);
        $this->setData('totalGoodsFeedCnt', gd_isset($useFeedDataCnt, 0));
        $this->setData('makeFileTime', gd_isset($makeFileTime, '0000-00-00T00:00:00+00:00'));
        $this->setData('useFbCheckValue', $useFbCheckValue);
    }
}