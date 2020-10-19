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
 * TargetingGates 설정
 *
 * @package Bundle\Controller\Admin\Marketing;
 * @author  Hakyoung Lee <haky2@godo.co.kr>
 */
class TargetingGatesConfigController extends \Controller\Admin\Controller
{
    public function index()
    {
        // --- 메뉴 설정
        $this->callMenu('marketing', 'criteo', 'targetingGatesConfig');

        try {
            $dbUrl = \App::load('\\Component\\Marketing\\DBUrl');
            $data = $dbUrl->getConfig('targetingGates', 'config');

            gd_isset($data['tgFl'], 'n');
            gd_isset($data['tgRange'], 'all');
            gd_isset($data['tgCode'], '');

            $checked = [];
            $checked['tgFl'][$data['tgFl']] =
            $checked['tgRange'][$data['tgRange']] = 'checked="checked"';

            if ($data['tgFl'] === 'n') {
                $disabled = 'disabled';
            }

            if (gd_policy('basic.info')['mallDomain']) {
                $this->setData('mallDomain', "http://" . gd_policy('basic.info')['mallDomain'] . "/");
            } else {
                $this->setData('mallDomain',URI_HOME);
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