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

namespace Bundle\Controller\Mobile\Board\Plusmemo;


use Component\Board\PlusMemoArticleFront;
use Framework\Debug\Exception\AlertOnlyException;

class PlusMemoArticlePsController extends \Controller\Mobile\Controller
{
    public function index()
    {
        $post = \Request::post()->toArray();
        $plusMemoArticle = new PlusMemoArticleFront();
        try {
            switch ($post['mode']) {
                case 'add' :
                    if (!$plusMemoArticle->add($post)) {
                        throw new \Exception(__('저장실패'));
                    }
                    if (\Request::isAjax()) {
                        $this->json(['result' => 'ok', 'msg' => __('저장이 완료되었습니다.')]);
                    }
                    $this->layer(__('저장이 완료되었습니다.'));
                    break;
                case 'modify' :
                    if ($plusMemoArticle->modify($post) !== 1) {
                        throw new \Exception(__('저장실패'));
                    }
                    if (\Request::isAjax()) {
                        $this->json(['result' => 'ok', 'msg' => __('저장이 완료되었습니다.')]);
                    }
                    $this->layer(__('저장이 완료되었습니다.'), 'top.location.href="plus_memo_article_list.php";');
                    break;
                case 'checkReadPassword' :
                    $password = $post['password'];
                    $article = $plusMemoArticle->get($post['sno']);
                    if ($plusMemoArticle->canRead($article, $password) !== 'y') {
                        throw new \Exception(__('비밀번호가 틀렸습니다.'));
                    }
                    if (\Request::isAjax()) {
                        $this->json(['result' => 'ok', 'data' => $article]);
                    }

                    break;
                case 'checkModifyAndRemovePassword' :
                    $password = $post['password'];
                    $article = $plusMemoArticle->get($post['sno']);
                    if ($plusMemoArticle->canModifyAndRemove($article, $password) !== 'y') {
                        throw new \Exception(__('비밀번호가 틀렸습니다.'));
                    }
                    if (\Request::isAjax()) {
                        $this->json(['result' => 'ok', 'data' => $article]);
                    }

                    break;
                case 'delete' :
                    if ($plusMemoArticle->remove($post['sno'], $post['password']) == 0) {
                        throw new \Exception(__('실패'));
                    }
                    if (\Request::isAjax()) {
                        $this->json(['result' => 'ok', 'msg' => __('삭제가 완료되었습니다.')]);
                    }
                    $this->layer(__('삭제가 완료되었습니다.'), 'top.location.href="plus_memo_article_list.php";');
                    break;
                case 'get' :
                    $result = $plusMemoArticle->get($post['sno']);
                    if (\Request::isAjax()) {
                        $this->json(['result' => 'ok', 'data' => $result]);
                    }
                    break;
                case 'getCommentList' :
                    $result = $plusMemoArticle->getCommentList($post['plusMemoSno'], $post['parentSno'], $post['listCount']);
                    $artile = $plusMemoArticle->get($post['parentSno']);
                    if (\Request::isAjax()) {
                        $this->json(['result' => 'ok', 'data' => $result,'article'=>$artile]);
                    }
                    break;
                default :
                    throw new \InvalidArgumentException(__('잘못된 경로로 접근하셨습니다.'));
            }
        } catch (\Exception $e) {
            if (\Request::isAjax()) {
                $this->json(['result' => 'fail', 'msg' => $e->getMessage()]);
            }
            throw new AlertOnlyException($e->getMessage());
        }
    }
}
