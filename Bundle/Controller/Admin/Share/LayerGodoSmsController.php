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
namespace Bundle\Controller\Admin\Share;

/**
 * Class LayerGodoSmsController
 * 대표관리자 정보 입력 안내 팝업
 *
 * @package Bundle\Controller\Admin\Share
 * @author  Jong-tae Ahn <qnibus@godo.co.kr>
 */
class LayerGodoSmsController extends \Controller\Admin\Controller
{
    /**
     * {@inheritdoc}
     */
    public function index()
    {
        $request = \Request::request()->toArray();
        $session = \App::getInstance('session');
        $session->set(\Component\Member\Manager::SESSION_LIMIT_FLAG_ON_MANAGER_ID, $request['managerId']);
        // 부모창에서 전송받은 폼데이터 설정
        $this->setData('action', $request['url']);
        $this->setData('mode', $request['mode']);
        $this->setData('formData', $request['data']);

        // 템플릿 레이아웃 설정
        $this->getView()->setDefine('layout', 'layout_layer.php');
    }
}
