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

use Framework\Debug\Exception\AlertBackException;
use Framework\Utility\ArrayUtils;
use Globals;
use Request;

/**
 * Class HackoutRegisterController
 * @package Bundle\Controller\Admin\Member
 * @author  yjwee
 */
class HackoutRegisterController extends \Controller\Admin\Controller
{
    public function index()
    {
        /** @var \Bundle\Controller\Admin\Controller $this */

        try {
            $this->callMenu('member', 'member', 'hackoutRegister');

            /** @var \Bundle\Component\Member\HackOut\HackOutService $hackOutService */
            $hackOutService = \App::load('\\Component\\Member\\HackOut\\HackOutService');

            $sno = Request::get()->get('sno');
            $getData = $hackOutService->getHackOutBySno($sno);
            $getData['reasonCd'] = ArrayUtils::removeEmpty(explode('|', $getData['reasonCd']));
            $this->setData('_hackType', (Globals::get('hackType')));
            $this->setData('_hackStep', (Globals::get('hackStep')));
            $this->setData('reasonCds', gd_code('01003',$getData['mallSno']));
            $this->setData('data', gd_htmlspecialchars(gd_isset($getData)));

        } catch (\Exception $e) {
            throw new AlertBackException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
