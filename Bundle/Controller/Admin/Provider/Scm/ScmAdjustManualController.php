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
namespace Bundle\Controller\Admin\Provider\Scm;

use Component\Scm\ScmAdjust;
use Exception;
use Framework\Debug\Exception\AlertBackException;

class ScmAdjustManualController extends \Controller\Admin\Controller
{
    /**
     * [관리자 모드] 공급사 수동 정산 요청
     *
     * @author su
     * @version 1.0
     * @since 1.0
     * @copyright ⓒ 2016, NHN godo: Corp.
     * @throws Except
     */
    public function index()
    {
        $this->callMenu('scm', 'adjust', 'scmAdjustManual');
        try {
            $scmAdjust = new ScmAdjust();
            $this->setData('scmAdjustType', $scmAdjust->scmAdjustType);

        } catch (Exception $e) {
            throw new AlertBackException($e->getMessage());
        }
    }
}
