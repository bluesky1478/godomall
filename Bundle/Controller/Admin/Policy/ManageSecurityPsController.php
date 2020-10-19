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

namespace Bundle\Controller\Admin\Policy;


use Component\Member\Manager;
use Framework\Debug\Exception\AlertOnlyException;
use Framework\Utility\ComponentUtils;
use Framework\Utility\DateTimeUtils;

class ManageSecurityPsController extends \Controller\Admin\Controller
{
    public function index()
    {
        $request = \App::getInstance('request');

        switch ($request->post()->get('mode', '')) {
            case 'securityAgreement':
                $session = \App::getInstance('session');
                $agreement = [
                    'managerSno'          => $session->get(Manager::SESSION_MANAGER_LOGIN . '.sno'),
                    'ip'                  => $request->getRemoteAddress(),
                    'guide'               => $request->post()->get('guide', 'y'),
                    'securityAgreementFl' => $request->post()->get('securityAgreementFl', ''),
                    'regDt'               => DateTimeUtils::dateFormat('Y-m-d H:i:s', 'now'),
                ];
                $isSave = ComponentUtils::setPolicy('manage.securityAgreement', $agreement);
                $this->json(
                    [
                        'error'   => $isSave ? 0 : 500,
                        'message' => $isSave ? '저장되었습니다.' : '저장에 실패하였습니다.',
                    ]
                );
                break;
            default:
                throw new AlertOnlyException('요청을 찾을 수 없습니다.');
                break;
        }
    }
}
