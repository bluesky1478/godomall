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

namespace Bundle\Controller\Admin\Base;

use Component\Admin\AdminMain;
use Exception;
use Framework\Debug\Exception\LayerException;
use Request;
use Session;

/**
 * Class MainSettingPsController
 *
 * @package Bundle\Controller\Admin\Base
 * @author  lee nam ju <lnjts@godo.co.kr>
 */
class LayerLegalRequirementsPsController extends \Controller\Admin\Controller
{
    /**
     * {@inheritdoc}
     *
     * @throws LayerException
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public function index()
    {
        // 요청
        $post = Request::post()->toArray();
        $legalRequirements = \App::load('Component\\Agreement\\LegalRequirements');
        switch ($post['mode']) {
            case 'saveLegalRequirements':
                if(Session::get('manager.isSuper') == 'y') {
                    $legalRequirements->saveLegalRequirements($post);
                }
                break;
            case 'saveLegalRequirementsConfig':
                $legalRequirements->saveLegalRequirementsConfig($post);
                break;
        }
        exit;
    }
}
