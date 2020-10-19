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

namespace Bundle\Controller\Admin\Member;

use Framework\Utility\ComponentUtils;
use Framework\Utility\StringUtils;

/**
 * Class Sms080PsController
 * @package Bundle\Controller\Admin\Member
 * @author  yjwee <yeongjong.wee@godo.co.kr>
 */
class Sms080PsController extends \Controller\Admin\Controller
{
    public function index()
    {
        $request = \App::getInstance('request');
        $logger = \App::getInstance('logger');
        switch ($request->request()->get('mode', '')) {
            case 'manualSync':
                try {
                    $policy = ComponentUtils::getPolicy('sms.sms080');
                    StringUtils::strIsSet($policy['status'], '');
                    if ($policy['status'] != 'O') {
                        $this->json('개통상태가 아닙니다.');
                    } else {
                        $component = \App::load('Component\\Sms\\Sms080');
                        $component->syncListByManual();
                        $this->json('동기화가 완료되었습니다.');
                    }
                } catch (\Throwable $e) {
                    $logger->error($e->getTraceAsString());
                    $this->json('동기화 중 오류가 발생하였습니다.');
                }
                break;
            case 'savePolicy':
                $policy = ComponentUtils::getPolicy('sms.sms080');
                StringUtils::strIsSet($policy['status'], '');
                $logger->info('', $request->request()->all());
                if ($policy['status'] == 'O' && ($request->request()->get('use', '') != '')) {
                    $policy['use'] = $request->request()->get('use', 'n');
                    ComponentUtils::setPolicy('sms.sms080', $policy);
                    $this->json(__('저장되었습니다.'));
                } else {
                    $this->json(__('개통상태가 아닙니다.'));
                }
                break;
        }
    }
}
