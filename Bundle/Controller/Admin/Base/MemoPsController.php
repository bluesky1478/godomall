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
namespace Bundle\Controller\Admin\Base;

use Framework\Debug\Exception\Except;
use Message;
use Globals;
use UserFilePath;
use Framework\Debug\Exception\LayerException;

class MemoPsController extends \Controller\Admin\Controller
{

    public function index()
    {
        /**
         * 간단메모 저장
         *
         * @author lnjts
         * @version 1.0
         * @since 1.0
         * @copyright ⓒ 2016, NHN godo: Corp.
         */
        switch ($_REQUEST['mode']) {
            case 'memo':
                try {
                    // 모듈 호출

                    $safe = \App::load('\\Component\\File\\SafeFile');
                    // 파일 저장
                    $memoPath = UserFilePath::data('etc', 'mini_memo.php');
                    $safe->open($memoPath);
                    $safe->write(htmlspecialchars(stripslashes(gd_isset($_POST['miniMemo']))));
                    $safe->close();
                    @chmod($memoPath, 0707);
                    throw new LayerException();
                } catch (Except $e) {
                    $e->actLog();
                    $item = ($e->ectMessage ? ' - ' . str_replace("\n", ' - ', $e->ectMessage) : '');
                    throw new LayerException(__('처리중에 오류가 발생하여 실패되었습니다.') . $item, null, null, null, 0);
                }
                break;
        }
    }
}
