<?php
/**
 * Created by PhpStorm.
 * User: godo
 * Date: 2019-05-17
 * Time: 오전 8:22
 */

namespace Bundle\Controller\Admin\Marketing;

use Framework\Debug\Exception\LayerException;
use Request;

class FacebookAdsPsController extends \Controller\Admin\Controller
{
    public function index()
    {
        $postValue = Request::post()->toArray();
        $fb = \App::load('\\Component\\Marketing\\FacebookAd');

        switch ($postValue['type']){
            case 'chkGoodsFeedCnt':
                if($fb->getUseFeedGoodsCnt() == 0){
                    echo "emptyFeedGoods";
                    exit;
                }
                break;
            case 'checkTsvFile':
                //tsv 파일 생성여부체크 -- 1. TSV파일 없다면 새로생성 2.생성된 tsv 파일이 있다면 생성하지 않고 pass
                $makeFileFl = $fb->checkTsvFile();
                if($makeFileFl){
                    //tsv 파일 생성 완료시 로딩바 hide
                    echo "<script> parent.progressFbeHide(); </script>";
                    //settings param 데이터 생성, 팝업창 호출 스크립트 실행
                    echo "<script> parent.settingsParamData('".$fb->setDiaSettingsParam()."'); </script>";
                }
                break;
        }
    }
}