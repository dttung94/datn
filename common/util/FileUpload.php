<?php
namespace common\util;

use Aws\S3\S3Client;
use yii\web\UploadedFile;


class FileUploadException extends \Exception
{

}


class FileUpload
{

    const S_LOCAL = 1;
    const S_S3    = 2;

    private $_storage;
    private $_storageAuthParams;
    private $_folder        = 'uploads';
    private $_treeStructure = true;
    private $_hashFilename  = true;
    private $_fsPath        = '/tmp/';
    private $_fsUrl         = 'http://static.example.com/';
    private $_ACL           = 'public-read';
    public $path;


    /**
     *
     * @param integer $storage               1 - Local, 2 — Amazon S3
     *
     * @param array   $storageAuthParameters Authorisation params
     *
     * @throws        \vlaim\FileUpload\FileUploadException   Throws exception if storage type is undefined
     */
    public function __construct($storage = S_S3, $storageAuthParams = [])
    { 
        if($storage != self::S_LOCAL && $storage != self::S_S3){
            throw new FileUploadException("Undefined storage. Use 1 (FileUpload::S_LOCAL) for Local and 2 (FileUpload::S_S3) for Amazon S3");
        }
        
        $this->_storage           = $storage;
        $this->_storageAuthParams = $storageAuthParams;
    }


    /**
     *
     * @param string $folder
     */
    public function setUploadFolder($folder)
    {
        return $this->_folder = $folder;

    }


    /**
     *
     * @param string $fsPath
     */
    public function setFsPath($fsPath)
    {
        return $this->_fsPath = $fsPath;

    }


    /**
     *
     * @param string $acl
     */
    public function setACL($acl)
    {
        return $this->_ACL = $acl;

    }


    /**
     *
     * @param string $url
     */
    public function setFsUrl($url)
    {
        return $this->_fsUrl = $url;

    }


    /**
     *
     * @param boolean $treeStructure
     */
    public function useTreeStructure($treeStructure)
    {
        return $this->_treeStructure = boolval($treeStructure);

    }


    /**
     *
     * @param boolean $hash
     */
    public function hashFilename($hash)
    {
        return $this->_hashFilename = boolval($hash);

    }


    /**
     *
     * @param UploadedFile $file Uploaded file instance
     *
     * @throws FileUploadException   Throws exception if storage type is undefined
     *
     * @return FileUpload
     */
    public function uploadFromFile($file)
    {
        switch ($this->_storage) {
        case self::S_LOCAL: $this->path = $this->uploadFromFileToLocalFS($file);
            break;
        case self::S_S3: $this->path = $this->uploadFromFileToAmazonS3($file);
            break;
        }

        return $this;

    }


    public function uploadFromUrl($url)
    {
        switch ($this->_storage) {
        case self::S_LOCAL: $this->path = $this->uploadFromUrlToLocalFS($url);
            break;
        case self::S_S3: $this->path = $this->uploadFromUrlToAmazonS3($url);
            break;
        }

        return $this;

    }


    /**
     *
     * @param UploadedFile $file Uploaded file instance
     *
     * @return string
     */
    private function uploadFromFileToLocalFS($file)
    {
        $path = $this->getUploadFolder();

        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }

        $path .= $this->getFilename($file);

        if ($file->saveAs($this->_fsPath.$path)) {
            return $this->_fsUrl.$path;
        }

    }


    /**
     *
     * @param string $url URL of file will be uploaded
     *
     * @return string
     */
    private function uploadFromUrlToLocalFS($url)
    {
        $parsed_url = parse_url($url);
        $headers    = @get_headers($url, 1);

        if (!$parsed_url || !$headers || !preg_match('/^(HTTP)(.*)(200)(.*)/i', $headers[0])) {
            throw new FileUploadException('File not found');
        }

        $path = $this->getUploadFolder();

        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }

        $path .= $this->getFilename(basename($url));

        if (copy($url, $this->_fsPath.$path)) {
            return $this->_fsUrl.$path;
        }

    }


    /**
     *
     * @param UploadedFile $file Uploaded file instance
     *
     * @return string               ObjectURL
     */
    private function uploadFromFileToAmazonS3($file)
    {
        $this->checkRequiredAWSParams();

        $s3 = S3Client::factory($this->_storageAuthParams);

        if (!$s3->doesBucketExist($this->_storageAuthParams['bucket'])) {
            $s3->createBucket(
                array(
                 'Bucket'             => $this->_storageAuthParams['bucket'],
                 'LocationConstraint' => $this->_storageAuthParams['region'],
                )
            );
        }

        $upload = $s3->putObject(
            array(
             'Bucket'       => $this->_storageAuthParams['bucket'],
             'Key'          => $this->getUploadFolder().$this->getFilename($file),
             'SourceFile'   => $file->tempName,
             'ContentType'  => $file->type,
             'ACL'          => $this->_ACL,
             'StorageClass' => 'REDUCED_REDUNDANCY',
            )
        );

        return $upload->get('ObjectURL');

    }


    /**
     *
     * @param string $url URL of file will be uploaded
     *
     * @return string               ObjectURL
     */
    private function uploadFromUrlToAmazonS3($url)
    {
        $parsed_url = parse_url($url);
        $headers    = @get_headers($url, 1);

        if (!$parsed_url || !$headers || !preg_match('/^(HTTP)(.*)(200)(.*)/i', $headers[0])) {
            throw new FileUploadException('File not found');
        }

        $s3 = S3Client::factory($this->_storageAuthParams);

        if (!$s3->doesBucketExist($this->_storageAuthParams['bucket'])) {
            $s3->createBucket(
                array(
                 'Bucket'             => $this->_storageAuthParams['bucket'],
                 'LocationConstraint' => $this->_storageAuthParams['region'],
                )
            );
        }

        $upload = $s3->putObject(
            array(
             'Bucket'       => $this->_storageAuthParams['bucket'],
             'Key'          => $this->getUploadFolder().basename($url),
             'Body'         => file_get_contents($url),
             'ContentType'  => $headers['Content-Type'],
             'ACL'          => $this->_ACL,
             'StorageClass' => 'REDUCED_REDUNDANCY',
            )
        );

        return $upload->get('ObjectURL');

    }


    /**
     *
     * @return string        String with folder path
     */
    final function getTreeStructureMap()
    {
        return substr(md5(microtime()), mt_rand(0, 30), 2).DIRECTORY_SEPARATOR.substr(md5(microtime()), mt_rand(0, 30), 2);

    }


    /**
     *
     * @return string       String with folder path file will be uploaded
     */
    final function getUploadFolder()
    {
        return $this->_folder.DIRECTORY_SEPARATOR.($this->_treeStructure ? $this->getTreeStructureMap() : "").DIRECTORY_SEPARATOR;

    }


    /**
     *
     * @return string       String with filename
     */
    final function getFilename($file)
    {
        $fileName  = $file->name;
        $pathParts = pathinfo($fileName);

        return ($this->_hashFilename ? substr(md5($fileName.mt_rand(0, 30)), 0, 8) : $fileName).".".$pathParts['extension'];

    }


    /**
     *
     * @throw FileUploadException   Throws exception if some params are missed
     */
    final function checkRequiredAWSParams()
    {
        if (empty($this->_storageAuthParams['credentials']['key'])) {
            throw new FileUploadException('AWSParams.credentials.key is required');
        }

        if (empty($this->_storageAuthParams['credentials']['secret'])) {
            throw new FileUploadException('AWSParams.credentials.secret is required');
        }

        if (empty($this->_storageAuthParams['bucket'])) {
            throw new FileUploadException('AWSParams.bucket is required');
        }

        if (empty($this->_storageAuthParams['region'])) {
            throw new FileUploadException('AWSParams.region is required');
        }

    }


}
