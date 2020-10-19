<?php
/**
 * This is commercial software, only users who have purchased a valid license
 * and accept to the terms of the License Agreement can install and use this
 * program.
 *
 * Do not edit or add to this file if you wish to upgrade Enamoo S5 to newer
 * versions in the future.
 *
 * @copyright Copyright (c) 2015 NHN godo: Corp.
 * @link http://www.godo.co.kr
 */
namespace Bundle\Controller\Api\Kafka;

use Component\Validator\Validator;
use Component\Database\DBTableField;
use Framework\StaticProxy\Proxy\FileHandler;
use Symfony\Component\Yaml\Yaml;

/**
 * 카프카 사용설정
 *
 * @author Lee namju <lnjts@godo.co.kr>
 */
class setConfigController extends \Controller\Api\Controller
{
    public function index()
    {
        $mode = \Request::get()->get('mode');
        switch ($mode) {
            case 'set' :
                $useYn = \Request::get()->get('useYn');
                $target = \Request::get()->get('target');

                if(($useYn == 'y' || $useYn == 'n')=== false) {
                    return;
                }

                $kafkaConfig = \App::getConfig('kafka');
                $kafkaConfigInfo = $kafkaConfig->getBroker();
                $kafkaConfigInfo['useYn'] = $useYn;
                $kafkaYmlData['broker'] = $kafkaConfigInfo;
                $yaml = Yaml::dump($kafkaYmlData);
                if($target == 'shop') {
                    FileHandler::write(USERPATH.'config/kafka.yml', $yaml);
                }
                else {
                    FileHandler::write(SYSPATH.DS.'config/kafka.yml', $yaml);
                }
                exit('success');
                break;
            case 'remove' :
                FileHandler::delete(USERPATH.'config/kafka.yml');
                exit('remove');
        }

        exit;
    }


}
