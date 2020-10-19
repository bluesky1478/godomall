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
namespace Bundle\Controller\Admin\Board;

use Globals;
use Request;
use Framework\Utility\Strings;

class TemplateWriteController extends \Controller\Admin\Controller
{

    /**
     * 게시물관리
     *
     * @author sj
     * @version 1.0
     * @since 1.0
     * @copyright ⓒ 2016, NHN godo: Corp.
     */
    public function index()
    {
        // --- 페이지 데이터
        $bdTemplate = \App::load('\\Component\\Board\\BoardTemplate');
        $getData = $bdTemplate->getData(Request::get()->get('sno'));
        // --- 관리자 디자인 템플릿
        if (Request::get()->get('mode') == 'popup') {
            $this->getView()->setDefine('layout', 'layout_blank.php');
        } else {
            $this->getView()->setDefine('layout', 'layout_layer.php');
        }

        if (Request::get()->get('templateType') == 'admin') {
            $getData['templateType'] = 'admin';
        } else if (Request::get()->get('templateType') == 'front') {
            $getData['templateType'] = 'front';
        }

        $this->setData('req', Request::get()->all());
        $this->setData('data', $getData);

        unset($getData);
    }
}
