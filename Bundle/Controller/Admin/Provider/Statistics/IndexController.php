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

namespace Bundle\Controller\Admin\Provider\Statistics;

/**
 * 게시판 메인 페이지
 * [관리자 모드] 게시판 메인 페이지
 *
 * @author    artherot
 * @version   1.0
 * @since     1.0
 * @copyright Copyright (c), Godosoft
 */
class IndexController extends \Controller\Admin\Controller
{
    /**
     * {@inheritDoc}
     */
    public function index()
    {
        $this->redirect(URI_PROVIDER . 'statistics/sales_day.php');
    }
}
