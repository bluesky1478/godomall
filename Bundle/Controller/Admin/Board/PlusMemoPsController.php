<?php
/**
 * This is commercial software, only users who have purchased a valid license
 * and accept to the terms of the License Agreement can install and use this
 * program.
 *
 * Do not edit or add to this file if you wish to upgrade Enamoo S5 to newer
 * versions in the future.
 *
 * @copyright Copyright (c) 2015 GodoSoft.
 * @link http://www.godo.co.kr
 */

namespace Bundle\Controller\Admin\Board;


use Component\Board\PlusMemoManager;
use Framework\Debug\Exception\AlertBackException;

class PlusMemoPsController extends \Controller\Admin\Controller
{
    public function index()
    {
        $post = \Request::post()->toArray();
        $plusMemoManager = new PlusMemoManager();
        try {
            switch ($post['mode']) {
                case 'add' :
                    if (!$plusMemoManager->add($post)) {
                        throw new \Exception(__('저장실패'));
                    }
                    $this->layer(__('저장이 완료되었습니다.'), 'top.location.href="plus_memo_list.php";');
                    break;
                case 'modify' :
                    if ($plusMemoManager->modify($post) !== 1) {
                        throw new \Exception(__('저장실패'));
                    }
                    $this->layer(__('저장이 완료되었습니다.'), 'top.location.href="plus_memo_list.php";');
                    break;
                case 'delete' :
                    if ($plusMemoManager->remove($post['sno']) == 0) {
                        throw new \Exception(__('실패'));
                    }
                    $this->layer(__('삭제가 완료되었습니다.'), 'top.location.href="plus_memo_list.php";');
                    break;
                default :
                    throw new \InvalidArgumentException(__('잘못된 경로로 접근하셨습니다.'));
            }
        } catch (\Exception $e) {
            throw new AlertBackException($e->getMessage());
        }
    }
}
