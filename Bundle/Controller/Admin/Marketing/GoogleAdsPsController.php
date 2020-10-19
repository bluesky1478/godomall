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
use Framework\Debug\Exception\LayerNotReloadException;
use Request;

/**
 * 구글 광고 설정 처리 / 저장
 * @author  Sunny <bluesunh@godo.co.kr>
 */
class GoogleAdsPsController extends \Controller\Admin\Controller
{
    public function index()
    {
        $postValue = Request::post()->toArray();
        $fileValue = Request::files()->toArray();

        $dbUrl = \App::load('\\Component\\Marketing\\DBUrl');
        $googleAds = \App::load('\\Component\\Marketing\\GoogleAds');
        switch($postValue['type']) {
            case 'config' : {
                try {
                    $oriFeedUseFl = $dbUrl->getConfig('google', 'config')['feedUseFl'];
                    $dbUrl->setConfig($postValue, $fileValue);
                    if ($postValue['feedUseFl'] == 'y' && $oriFeedUseFl == '' && file_exists($googleAds->feedFilePath) === false) { // 상품 피드 최초 사용함(1회) 저장 시 즉시 생성
                        echo '<script> parent.adsActivate(); </script>';
                    }
                    $this->layer(__('저장이 완료되었습니다.'));
                }
                catch (\Exception $e) {
                    throw new LayerException($e->getMessage());
                }
                break;
            }
            case 'checkTxtFile': {
                // txt 파일 생성여부체크 -- 1. txt 파일 없다면 새로 생성 2.생성된 txt 파일이 있다면 생성하지 않고 pass
                $result = $googleAds->checkTxtFile();
                if ($result === true) {
                    $this->layer(__('저장이 완료되었습니다.'));
                } else {
                    throw new LayerNotReloadException('상품 피드 생성이 실패되었습니다.');
                }
                break;
            }
            case 'download': {
                try {
                    $result = $googleAds->checkTxtFile();
                    if ($result === true) {
                        $this->download($googleAds->feedFilePath, 'google.txt');
                        exit;
                    } else {
                        throw new LayerException('상품 피드 생성이 실패되었습니다.');
                    }
                } catch (\Exception $e) {
                    throw new LayerNotReloadException('피드를 생성중입니다. 잠시 후 다시 시도하시기 바랍니다.');
                }
                break;
            }
        }
    }
}