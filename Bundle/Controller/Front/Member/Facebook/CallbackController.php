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
 * @link      http://www.godo.co.kr
 */

namespace Bundle\Controller\Front\Member\Facebook;

use Component\Facebook\Facebook;

/**
 * Class CallbackController
 * @package Bundle\Controller\Front\Member\Facebook
 * @author  yjwee
 */
class CallbackController extends \Controller\Front\Controller
{
    /** @var  \Bundle\Component\Policy\SnsLoginPolicy */
    protected $snsPolicy;

    public function index()
    {
        $request = \App::getInstance('request');
        $logger = \App::getInstance('logger');
        $logger->info('Facebook Callback Controller. print get params', $request->get()->all());
        $logger->info('Facebook Callback Controller. print post params', $request->post()->all());
        $this->snsPolicy = \App::load('Component\\Policy\\SnsLoginPolicy');
        if ($this->snsPolicy->useGodoAppId()) {
            $godoConfig = \Component\Godo\GodoFacebookServerApi::getInstance()->getGodoConfig();
            $config = [
                'app_id'     => $godoConfig['appId'],
                'app_secret' => $godoConfig['secret'],
            ];
            $facebook = new Facebook($config);
            $facebook->setTokenMetadataByGodo();
        } else {
            $facebook = new Facebook();
            $facebook->setAccessToken();
            $facebook->setTokenMetadata();
        }
    }
}
