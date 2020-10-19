<?php
/**
 * This is commercial software, only users who have purchased a valid license
 * and accept to the terms of the License Agreement can install and use this
 * program.
 *
 * Do not edit or add to this file if you wish to upgrade Godomall5 to newer
 * versions in the future.
 *
 * @copyright Copyright (c) 2018 NHN godo: Corp.
 * @link http://www.godo.co.kr
 */

namespace Bundle\Controller\Admin\Share;


use Framework\Debug\Exception\LayerException;

class LayerUnstoringPsController extends \Controller\Admin\Controller
{
    public function index()
    {
        $request = \App::getInstance('request');
        $post = $request->post()->toArray();
        $unstoring = \App::load('\\Component\\Delivery\\Unstoring');

        try {
            switch($post['mode']) {
                case 'register' :
                    $cnt = $unstoring->getCountAddressRow($post['addressFl'], $post['mallFl']);
                    if ($cnt == 30) {
                        $msg = $post['title'] . ' 주소 등록은 최대 30개 까지입니다.';
                        echo $this->json(['result' => 'fail', 'msg' => $msg]);
                        exit;
                    }
                case 'modify' :
                    if (empty($post['unstoringNm'])) {
                        $msg = '관리 명칭을 입력하세요.';
                    } else if (empty($post['unstoringAddress'])) {
                        $msg = $post['title'] . ' 주소를 입력하세요.';
                    } else if ($post['postFl'] != 'y' && empty($post['unstoringZonecode'])) {
                        $msg = '우편번호를 입력하세요.';
                    }

                    if (isset($msg)) {
                        echo $this->json(['result' => 'fail', 'msg' => $msg]);
                        exit;
                    }

                    $unstoring->saveUnstoringInfo($post);
                    break;
                case 'delete' :
                    $unstoring->deleteUnstoringInfo($post);
                    break;
            }
        } catch (Exception $e) {
            $this->json([
                'error' => 1,
                'msg' => $e->getMessage(),
            ]);
        }
    }
}
