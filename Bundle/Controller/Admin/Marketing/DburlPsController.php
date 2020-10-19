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

namespace Bundle\Controller\Admin\Marketing;

use Framework\Debug\Exception\LayerException;
use Message;
use Globals;
use Request;
use UserFilePath;
use FileHandler;

/**
 * 설정 처리 / 저장
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class DburlPsController extends \Controller\Admin\Controller
{
    /**
     * index
     *
     */
    public function index()
    {
        $postValue = Request::post()->toArray();
        $fileValue = Request::files()->toArray();

        $dbUrl = \App::load('\\Component\\Marketing\\DBUrl');
        $downFbGoodsFeed = \App::load('\\Component\\Marketing\\FacebookAd');
        switch($postValue['type']) {
            case 'config' : {
                try {
                    $dbUrl->setConfig($postValue, $fileValue);
                    throw new LayerException();
                }
                catch (\Exception $e) {
                    throw new LayerException($e->getMessage());
                }
                break;
            }
            case 'gen' : {
                switch($postValue['company']) {
                    case 'naver' : {
                        try {

                            $dbUrl->genarateNaver('all', UserFilePath::data('dburl', 'naver', 'naver_all.php'));
                            $dbUrl->genarateNaver('summary', UserFilePath::data('dburl', 'naver', 'naver_summary.php'));
                            exit;

                            throw new LayerException();
                        }
                        catch (\Exception $e) {
                            throw new LayerException($e->getMessage());
                        }
                        break;
                    }
                    case 'payco' : {
                        try {

                            $dbUrl->genarateNaver('all', UserFilePath::data('dburl', 'payco', 'payco_all.php'));
                            $dbUrl->genarateNaver('summary', UserFilePath::data('dburl', 'payco', 'payco_summary.php'));
                            exit;

                            throw new LayerException();
                        }
                        catch (\Exception $e) {
                            throw new LayerException($e->getMessage());
                        }
                        break;
                    }
                }
            }
            case 'download': {
                    try {
                        $downFbGoodsFeed->makeFbGoodsFeed();
                        $this->download(UserFilePath::data('facebookFeed', 'facebookFeed.tsv')->getRealPath(), 'facebookFeed.tsv');
                        exit;
                    } catch (\Exception $e) {
                        throw new LayerException('페이스북 제품 피드를 생성하는 상품이 없습니다.');
                    }
                    break;
                }
            case 'setFbSettings':{
                try{
                    $settingConfig = $dbUrl->getConfig($postValue['company'], $postValue['mode']);
                    if($postValue['domainCodeSaveFl'] == 'y') { // 저장버튼 클릭시 동작
                        $settingConfig['value']['domainAuthCode'] = $postValue['domainAuthCode'];
                        unset($postValue['domainCodeSaveFl']);
                        unset($postValue['domainAuthCode']);
                        $postValue['value'] = $settingConfig['value'];
                        $dbUrl->setConfig($postValue, $fileValue);
                    } else { //Facebook Business Extension 시작/변경하기 버튼 클릭시 동작
                        if(empty($settingConfig['value']['domainAuthCode']) === false){
                            $postValue['value']['domainAuthCode'] = $settingConfig['value']['domainAuthCode'];
                        }
                        $dbUrl->setConfig($postValue, $fileValue);
                    }
                    throw new LayerException();
                } catch (\Exception $e) {
                    throw new LayerException($e->getMessage());
                }
                break;
            }
        }
    }
}