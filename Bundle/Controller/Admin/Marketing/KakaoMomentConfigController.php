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
 * @link      http://www.godo.co.kr
 */

namespace Bundle\Controller\Admin\Marketing;

class KakaoMomentConfigController extends \Controller\Admin\Controller
{
    public function index()
    {
        // --- 메뉴 설정
        $this->callMenu('marketing', 'criteo', 'kakaoMomentConfig');
        try {
            $dbUrl = \App::load('\\Component\\Marketing\\DBUrl');
            $data = $dbUrl->getConfig('kakaoMoment', 'config');

            gd_isset($data['kakaoMomentFl'], 'n');
            gd_isset($data['kakaoMomentRange'], 'all');
            gd_isset($data['kakaoMomentCode'], '');

            $checked = [];
            $checked['kakaoMomentFl'][$data['kakaoMomentFl']] =
            $checked['kakaoMomentRange'][$data['kakaoMomentRange']] = 'checked="checked"';

            if ($data['kakaoMomentFl'] === 'n') {
                $disabled = 'disabled';
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
        // --- 관리자 디자인 템플릿
        $this->setData('data', $data);
        $this->setData('checked', $checked);
        $this->setData('disabled', $disabled);
    }
}