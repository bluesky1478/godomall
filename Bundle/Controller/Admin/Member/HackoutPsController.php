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
namespace Bundle\Controller\Admin\Member;

use Component\Member\Manager;
use Exception;
use Framework\Debug\Exception\LayerNotReloadException;
use Message;
use Request;
use Session;

/**
 * Class HackoutPsController
 * @package Bundle\Controller\Admin\Member
 * @author  yjwee
 */
class HackoutPsController extends \Controller\Admin\Controller
{
    public function index()
    {
        /** @var \Bundle\Component\Member\HackOut\HackOutService $hackOutService */
        $hackOutService = \App::load('\\Component\\Member\\HackOut\\HackOutService');
        switch (Request::post()->get('mode')) {
            case 'modify':
                try {
                    $managerSession = Session::get(Manager::SESSION_MANAGER_LOGIN);
                    $hackOutService->setManagerNo($managerSession['sno']);
                    $hackOutService->setManagerId($managerSession['managerId']);
                    $hackOutService->setManagerIp(Request::getRemoteAddress());
                    $hackOutService->updateHackOut(Request::post()->all());
                    $this->layer(__('저장이 완료되었습니다.'), 'top.location.href="hackout_list.php";');
                } catch (Exception $e) {
                    throw new LayerNotReloadException($e->getMessage());
                }
                break;
            case 'delete':
                foreach (Request::post()->get('chk') as $sno) {
                    $hackOutService->deleteHackOutBySno($sno);
                }
                $this->layer(__('삭제 되었습니다.'), 'top.location.href="hackout_list.php";');
                break;
        }
    }
}
