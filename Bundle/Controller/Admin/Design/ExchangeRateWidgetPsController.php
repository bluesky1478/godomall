<?php

namespace Bundle\Controller\Admin\Design;

use Request;

/**
 * 환율계산 위젯
 */
class ExchangeRateWidgetPsController extends \Controller\Admin\Controller
{
    public function index()
    {
        $exchangeRateConfig = \App::load('\\Component\\PlusShop\\ExchangeRateWidget\\ExchangeRateConfig');

        $request = \App::getInstance('request');
        $post = $request->post()->toArray();
        $selfIconPc = Request::files()->get('self_icon_pc');
        $selfIconMb = Request::files()->get('self_icon_mb');

        $resultValid = $this->validation($selfIconPc, $selfIconMb);
        if ($resultValid['result'] === false) {
            echo json_encode($resultValid);
            return;
        }

        $data = [
            'widget_display' => ($post['widget_display'] == 'true'),
            'self_icon_pc' => $selfIconPc,
            'self_icon_mb' => $selfIconMb,
            'base_cur_type' => $post['base_cur_type'],
            'exchange_cur_type' => $post['exchange_cur_type'],
            'widget_type' => $post['widget_type'],
            'widget_icon_type' => $post['widget_icon_type'],
            'widget_icon_use_both' => $post['widget_icon_use_both']
        ];

        if ($exchangeRateConfig->save($data)) {
            $result = true;
            $msg = '저장이 완료되었습니다.';
        } else {
            $result = false;
            $msg = '아이콘 업로드 중에 오류가 발생했습니다.';
        }

        echo json_encode([
            'result' => $result,
            'msg' => $msg
        ]);
    }

    /**
     * 첨부 이미지 validation
     *
     * @param array $selfIconPc
     * @param array $selfIconMb
     * @return array
     */
    private function validation($selfIconPc, $selfIconMb)
    {
        $typePc = explode('/', $selfIconPc['type'])[1];
        $typeMb = explode('/', $selfIconMb['type'])[1];
        $format = ['gif', 'png', 'jpg', 'jpeg'];
        $result = true;
        $msg = '';

        if ($selfIconPc) {
            if (!in_array($typePc, $format)) {
                $result = false;
                $msg = 'this file format is not allowed';
            }
            if (intval($selfIconPc['size']) > 1024 * 1024 * 5) {
                $result = false;
                $msg = 'pc image volume is over 5MB';
            }
        }
        if ($selfIconMb) {
            if (!in_array($typeMb, $format)) {
                $result = false;
                $msg = 'this file format is not allowed';
            }
            if (intval($selfIconMb['size']) > 1024 * 1024 * 5) {
                $result = false;
                $msg = 'mobile image volume is over 5MB';
            }
        }

        return [
            'result' => $result,
            'msg' => $msg
        ];
    }
}
