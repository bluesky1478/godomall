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

/**
 * 구글 광고 설정/관리
 * @author  Sunny <bluesunh@godo.co.kr>
 */
class GoogleAdsConfigController extends \Controller\Admin\Controller
{
    public function index()
    {
        // 메뉴 설정
        $this->callMenu('marketing','googleAds','googleAdsConfig');

        try {
            $dbUrl = \App::load('\\Component\\Marketing\\DBUrl');
            $data = $dbUrl->getConfig('google', 'config');

            gd_isset($data['feedUseFl'], 'n');

            $checked = [];
            $checked['feedUseFl'][$data['feedUseFl']] = 'checked="checked"';

            if ($data['feedUseFl'] === 'n') {
                $disabled = 'disabled';
            }

            $googleAds = \App::load('\\Component\\Marketing\\GoogleAds');
            if ($data['feedUseFl'] !== 'n') {
                $fileinfo = $googleAds->getFeedFileInfo();
                if (empty($fileinfo['time']) === false) {
                    $lastFeedFileInfo = sprintf('(마지막 상품 피드 생성 정보 : 총 %s개, %s)', number_format($fileinfo['cntLine']), $fileinfo['time']);
                }
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
        }

        // 관리자 디자인 템플릿
        $this->setData('data',gd_isset($data));
        $this->setData('checked',gd_isset($checked));
        $this->setData('disabled', $disabled);
        $this->setData('lastFeedFileInfo', $lastFeedFileInfo);
    }
}