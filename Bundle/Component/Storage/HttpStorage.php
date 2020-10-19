<?php
namespace Bundle\Component\Storage;

use Framework\StaticProxy\Proxy\Request;
use Framework\StaticProxy\Proxy\UserFilePath;
use Framework\Utility\ArrayUtils;
use Encryptor;
use Framework\Utility\KafkaUtils;
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
class HttpStorage extends \Component\Storage\AbstractStorage
{
    const HTTP_STORAGE_SECRET_KEY    = 'godoapi!#%httpstorage';
    private $httpApiUrl = 'http://api.{DOMAIN}/godo/set_http_storage.php';
    private $httpType   = 'multipart'; //multipart(default) || form_params || body
    private $httpClientHost;
    protected $httpOptions = [];
    protected $httpFiles = [];
    protected $basePath;    //기본 경로 ex) data/board/
    protected $httpUrl;
    protected $storageName = 'http'; //저장소 이름 ex) local. http://qnibus.com
    protected $ftpData;

    public function __construct($pathCode, $storageName)
    {
        $basePath = $this->getDiskPath($pathCode);
        $this->setHttpOptions(['pathCode'=>$pathCode]);
        $this->setHttpOptions(['secretKey'=>Encryptor::encrypt(self::HTTP_STORAGE_SECRET_KEY)]);
        $this->basePath = $basePath->www();
        $this->realPath = (string)$basePath;
        $this->setClientHost();
        $this->setAdapter();
    }

    public function setClientHost() {
        $this->httpClientHost = getenv('GODO_DEFAULT_DOMAIN');
        if (empty($this->httpClientHost) === true) {
            $this->httpClientHost = Request::getDefaultHost(); //api로 호출하면 원본 서버로 붙음.
        }
        return $this;
    }

    /**
     * setHttpType
     * http 요청 타입 (multipart || form_params)
     * @param $type
     */
    public function setHttpType($type) {
        $this->httpType = $type;
        return $this;
    }

    /**
     * getHttpType
     * http 요청 타입 (multipart || form_params)
     * @param $type
     */
    public function getHttpType() {
        return $this->httpType;
    }

    /**
     * getClientHost
        *
        * @return string
            */
    public function getClientHost() {
            return $this->httpClientHost;
        }

    /**
     * getHttpRequestUrl
     * REQUEST URL
     *
     * @return mixed
     */
    public function getHttpRequestUrl() {
        $httpApiUrl = str_replace('{DOMAIN}', $this->getClientHost(), $this->httpApiUrl);
        return $httpApiUrl;
    }

    /**
     * setHttpOptions
     * HTTP Request 파라미터 세팅
     *
     * @param array $options
     *
     * @return $this
     */
    public function setHttpOptions(Array $options, $isFile = false) {
        if ($isFile === true) { //multipart
            $this->httpFiles[] = [
                'name' => $options['name'],
                'contents' => $options['content'],
                'filename' => $options['filename'],
            ];
        } else {
            foreach ($options as $key => $val) {
                $this->httpOptions[$key] = $val;
            }
        }
        return $this;
    }

    /**
     * getHttpOptions
     * HTTP Request 파라미터 가져오기
     * @param null $key
     *
     * @return array|mixed
     */
    public function getHttpOptions($key = null) {
        if ($key !== null) {
            return $this->httpOptions[$key];
        }
        return $this->httpOptions;
    }

    protected function setAdapter()
    {
        $config = [
            'base_uri' => $this->getClientHost()
        ];
        parent::setHttpClient($config);
    }

    /**
     * upload
     * parent::upload() 재 정의
     *
     * @param string $filePath
     * @param string $savePath
     *
     *
     * @param array  $imageOptions
     *
     * @return mixed
     * @throws \Exception
     */
    public function upload($filePath = '', $savePath = '', $imageOptions = ['width' => 0, 'height' => 0, 'quality' => 'high', 'overWrite' => true])
    {
        switch ($this->httpType) {
            case 'multipart' :
                if (empty($this->getHttpOptions('uploadName')) === true) { //필수값
                    throw new \Exception(__('파일이 존재하지 않습니다.'));
                }
                if (empty($this->httpFiles) === false && count($this->httpFiles) > 0) { //직접 파일을 설정할 경우
                    if (count($this->httpFiles) === 1) {
                        $multipart = $this->httpFiles[0];
                    } else {
                        $multipart = $this->httpFiles;
                    }
                } else { //form-data로 넘어온 경우
                    $fInfo = Request::files()->get($this->getHttpOptions('uploadName'));
                    if (count($fInfo['name']) > 1) {
                        $fInfo = ArrayUtils::rearrangeFileArray(Request::files()->get($this->getHttpOptions('uploadName')));
                        foreach ($fInfo as $k => $file) {
                            $multipart[] = ['name' => $this->getHttpOptions('uploadName') . '[]', 'contents' => fopen($file['tmp_name'], 'r+'), 'filename' => $file['name']];
                        }
                    } else {
                        if (empty($fInfo['name']) === true) {
                            throw new \Exception(__('파일이 존재하지 않습니다.'));
                        }
                        $multipart = ['name' => $this->getHttpOptions('uploadName'), 'contents' => fopen($fInfo['tmp_name'], 'r+'), 'filename' => $fInfo['name']];
                    }
                }
                $this->setHttpOptions(['savePath' => $savePath, 'imageOptions' => $imageOptions]);
                $options = [
                    'timeout' => 5, // Response timeout
                    'connect_timeout' => 5, // Connection timeout
                    'multipart' => [
                        $multipart,
                        [
                            'name' => 'options',
                            'contents' => json_encode($this->getHttpOptions())
                        ]
                    ]
                ];
                try {
                    $response = $this->getAdapter()->request('POST', $this->getHttpRequestUrl(), $options);
                    $result = json_decode($response->getBody(), true);
                } catch (\GuzzleHttp\Exception\ConnectException $e) {
                    $logger = \App::getInstance('logger');
                    $logger->error('[connection] ' . __METHOD__ . ', ' . $e->getFile() . '[' . $e->getLine() . '], ' . $e->getMessage(), $e->getTrace());
                    if (getenv('GODO_DISTRIBUTED_TYPE') !== 'origin') {
                        $options = [
                            'TAGS' => ['API-support', 'http-storage-api'],
                            'SEND_NOTIFICATION' => true
                        ];
                        KafkaUtils::sendLog('[http-storage] domain : ' . getenv('GODO_DEFAULT_DOMAIN') . ', error : ' . $e->getMessage(), KafkaUtils::LEVEL_ERROR, null, null, null, $options);
                    }
                    throw new \Exception(__('파일 업로드에 실패 했습니다. 고객센터로 문의해주세요.'));
                } catch (Exception $e) {
                    $logger = \App::getInstance('logger');
                    $logger->error('[unknown] ' . __METHOD__ . ', ' . $e->getFile() . '[' . $e->getLine() . '], ' . $e->getMessage(), $e->getTrace());
                    if (getenv('GODO_DISTRIBUTED_TYPE') !== 'origin') {
                        $options = [
                            'TAGS' => ['API-support', 'http-storage-api'],
                            'SEND_NOTIFICATION' => true
                        ];
                        KafkaUtils::sendLog('[http-storage] domain : ' . getenv('GODO_DEFAULT_DOMAIN') . ', error : ' . $e->getMessage(), KafkaUtils::LEVEL_ERROR, null, null, null, $options);
                    }
                    throw new \Exception(__('파일 업로드에 실패 했습니다. 고객센터로 문의해주세요.'));
                }
                break;
            case 'form_params' :
                try {
                    $options = ['timeout' => 5, 'connect_timeout' => 5, 'form_params' => ['options' => json_encode($this->getHttpOptions())]];
                    $response = $this->getAdapter()->request('POST', $this->getHttpRequestUrl(), $options);
                    $result = json_decode($response->getBody(), true);
                } catch (\GuzzleHttp\Exception\ConnectException $e) {
                    $logger = \App::getInstance('logger');
                    $logger->error('[connection] ' . __METHOD__ . ', ' . $e->getFile() . '[' . $e->getLine() . '], ' . $e->getMessage());
                    if (getenv('GODO_DISTRIBUTED_TYPE') !== 'origin') {
                        $options = [
                            'TAGS' => ['API-support', 'http-storage-api'],
                            'SEND_NOTIFICATION' => true
                        ];
                        KafkaUtils::sendLog('[http-storage] domain : ' . getenv('GODO_DEFAULT_DOMAIN') . ', error : ' . $e->getMessage(), KafkaUtils::LEVEL_ERROR, null, null, null, $options);
                    }
                    throw new \Exception(__('파일 업로드에 실패 했습니다. 고객센터로 문의해주세요.'));
                } catch (Exception $e) {
                    $logger = \App::getInstance('logger');
                    $logger->error('[unknown] ' . __METHOD__ . ', ' . $e->getFile() . '[' . $e->getLine() . '], ' . $e->getMessage());
                    if (getenv('GODO_DISTRIBUTED_TYPE') !== 'origin') {
                        $options = [
                            'TAGS' => ['API-support', 'http-storage-api'],
                            'SEND_NOTIFICATION' => true
                        ];
                        KafkaUtils::sendLog('[http-storage] domain : ' . getenv('GODO_DEFAULT_DOMAIN') . ', error : ' . $e->getMessage(), KafkaUtils::LEVEL_ERROR, null, null, null, $options);
                    }
                    throw new \Exception(__('파일 업로드에 실패 했습니다. 고객센터로 문의해주세요.'));
                }
                break;
        }

        switch ($this->getHttpOptions('methodName')) {
            case 'uploadAjax' :
                $result = $result['data'];
                break;
        }
        return $result;
    }

    public function getPath($pathCodeDirName)
    {
        return UserFilePath::data($pathCodeDirName);
    }

    public function isFile($filePath)
    {
        return is_file(realpath($this->getRealPath($filePath)));
    }

    public function getHttpPath($filePath)
    {
        $result = $this->basePath . DS . $filePath;
        return $result;
    }

    public function getFilename($filePath)
    {
        if (substr($filePath, -1) != DS || substr($filePath, -1) != '\\') {
            return basename($filePath);
        }

        return null;
    }

    public function getMountPath($filePath)
    {
        return $filePath;
    }

    public function getRealPath($filePath)
    {
        return $this->getAdapter()->getPathPrefix() . $filePath;
    }

    public function getDownLoadPath($filePath)
    {
        return $this->getRealPath($filePath);
    }

    final public function download($filePath, $downloadFilename)
    {
        $realPath = $this->getRealPath($filePath);
        parent::setDownloadHeader($realPath, $downloadFilename);
    }

    public function isFileExists($filePath)
    {
        return file_exists(realpath($this->getRealPath($filePath)));
    }

    public function delete($filePath)
    {
        $this->setHttpOptions(['target'=>'delete', 'filePath'=>$filePath]);
        $options = ['form_params' => ['options' => json_encode($this->getHttpOptions())]];
        $response = $this->getAdapter()->request('POST', $this->getHttpRequestUrl(), $options);
        $result = json_decode($response->getBody(), true);
        $status = ($result['result'] === 'success') ? true : false;
        return $status;
    }

    public function rename($oldFilePath, $newFilePath)
    {
        return @rename($oldFilePath, $newFilePath);
    }

}
