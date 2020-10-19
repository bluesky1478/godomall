<?php

namespace Bundle\Controller\Admin\Design;


use Bundle\Controller\Admin\Controller;
use Request;
use UserFilePath;

class ScreenEffectRegistController extends Controller
{
    public function index()
    {
        $request = Request::get()->toArray();
        $sno = $request['sno'];

        if ($sno) {
            $this->modify($sno);
        } else {
            $this->callMenu('design', 'designConf', 'screenEffectRegist');
        }

        $imagePath = 'plusshop/nhngodo/screen_effect_free/skins/default/images';
        $path = UserFilePath::data(...explode('/', $imagePath))->www();
        $this->setData('imagePath', $path);
    }

    /**
     * 화면 효과 수정
     *
     * @param int $sno
     */
    private function modify($sno)
    {
        $this->callMenu('design', 'designConf', 'screenEffectModify');

        $screenEffectDao = \App::load('\\Component\\PlusShop\\ScreenEffect\\ScreenEffectDao');
        $item = $screenEffectDao->getBySno($sno);
        if ($item['effect_limited'] == 0) {
            $item['effect_start_date'] = '';
            $item['effect_start_time'] = '';
            $item['effect_end_date'] = '';
            $item['effect_end_time'] = '';
        }
        $item['sno'] = $sno;

        $this->setData('item', $item);
    }
}
