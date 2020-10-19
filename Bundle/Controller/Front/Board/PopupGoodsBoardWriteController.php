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

namespace Bundle\Controller\Front\Board;


class PopupGoodsBoardWriteController extends \Controller\Front\Goods\GoodsBoardWriteController
{
    public function index()
    {
        parent::index();
        $this->getView()->setDefine('header', 'outline/_share_header.html');
        $this->getView()->setDefine('footer', 'outline/_share_footer.html');
    }
}
