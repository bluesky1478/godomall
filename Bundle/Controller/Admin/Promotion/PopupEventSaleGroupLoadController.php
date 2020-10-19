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
namespace Bundle\Controller\Admin\Promotion;

use Exception;
use Session;
use App;
use Request;
use Framework\Debug\Exception\AlertCloseException;

/**
 * Class PopupEventSaleGroupRegisterController
 *
 * @package Bundle\Controller\Admin\Promotion
 * @author  <bumyul2000@godo.co.kr>
 */
class PopupEventSaleGroupLoadController extends \Controller\Admin\Controller
{
    /**
     * @inheritdoc
     *
     * @throws AlertCloseException
     */
    public function index()
    {
        try {
            $eventGroupTheme = App::load('\\Component\\Promotion\\EventGroupTheme');
            $data = $eventGroupTheme->getDataEventGroupLoadList();

            $page = App::load('\\Component\\Page\\Page'); // 페이지 재설정

            $this->setData('page', $page);
            $this->setData('data', $data['data']);
            $this->setData('search', $data['search']);
            $this->setData('checked', $data['checked']);
            $this->setData('selected', $data['selected']);

            $this->getView()->setDefine('layout', 'layout_blank.php');
        }
        catch (Exception $e) {
            throw new AlertCloseException($e->ectMessage);
        }
    }
}
