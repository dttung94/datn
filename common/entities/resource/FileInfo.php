<?php

namespace common\entities\resource;

use common\models\base\AbstractObject;
use Yii;

/**
 * This is the model class for table "file_info".
 *
 * @property integer $file_id
 *
 * @property string $provider
 *
 * @property string $original_name
 * @property string $name
 * @property integer $size
 * @property string $extension
 * @property string $path
 *
 * @property string $status
 * @property string $created_at
 * @property string $modified_at
 *
 * @property bool $isExist
 * @property string $filePath
 */
class FileInfo extends AbstractObject
{
    const
        PROVIDER_SYSTEM = "SYSTEM",
        PROVIDER_S3 = "S3";

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'file_info';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [["provider", "original_name", 'name', 'path', 'extension', 'size'], 'required'],
            [['size'], 'integer'],
            [['created_at', 'modified_at'], 'safe'],
            [['original_name', 'name', 'path'], 'string', 'max' => 255],
            [['extension', 'status'], 'string', 'max' => 20]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'file_id' => Yii::t('app.attribute.file_info.label', 'ID'),
            'provider' => Yii::t('app.attribute.file_info.label', 'Provider'),

            'original_name' => Yii::t('app.attribute.file_info.label', 'Original Name'),
            'name' => Yii::t('app.attribute.file_info.label', 'Name'),
            'size' => Yii::t('app.attribute.file_info.label', 'Size'),
            'extension' => Yii::t('app.attribute.file_info.label', 'Extension'),
            'path' => Yii::t('app.attribute.file_info.label', 'Path'),

            'status' => Yii::t('app.attribute.file_info.label', 'Status'),
            'created_at' => Yii::t('app.attribute.file_info.label', 'Created At'),
            'modified_at' => Yii::t('app.attribute.file_info.label', 'Modified At'),
        ];
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert) {
                $this->status = self::STATUS_ACTIVE;
                $this->created_at = static::currentDatetime();
            }
            $this->modified_at = static::currentDatetime();
            return true;
        }
        return false;
    }

    public function afterDelete()
    {
        parent::afterDelete();
        //TODO delete file in storage
        switch ($this->provider) {
            case self::PROVIDER_SYSTEM:
                if (file_exists($filePath = \Yii::getAlias("@upload") . $this->path . '/' . $this->name)) {
                    @unlink($filePath);
                }
                break;
            case self::PROVIDER_S3:
                break;
            default:
                break;
        }
    }

    /**
     * TODO check file is exist
     * @return mixed
     */
    public function getIsExist()
    {
        $filePath = "$this->path/$this->name";
        return file_exists(Yii::getAlias("@upload/$filePath"));
    }

    /**
     * TODO get file path
     * @return bool|string
     */
    public function getFilePath()
    {
        $filePath = "$this->path/$this->name";
        return Yii::getAlias("@upload/$filePath");
    }

    /**
     * TODO get folder size of dir
     * @param $directory
     * @return int
     */
    public static function getDirSize($directory)
    {
        $size = 0;
        foreach (glob(rtrim($directory, '/') . '/*', GLOB_NOSORT) as $each) {
            $size += is_file($each) ? filesize($each) : static::getDirSize($each);
        }
        return $size;
    }

    /**
     * TODO get array folder from path
     * @param $pathItems
     * @param string $prePath
     * @return array
     */
    public static function getArrayFolderFromPath($pathItems, $prePath = "")
    {
        if (!empty($pathItems)) {
            if (isset($pathItems[0])) {
                $firstPath = $pathItems[0];
                unset($pathItems[0]);
                $folders["$prePath/$firstPath"] = static::getArrayFolderFromPath(array_values($pathItems), "$prePath/$firstPath");
                return $folders;
            }
        }
        return [];
    }

    /**
     * TODO scan dir and add to zip
     * @param \ZipArchive $zip
     * @param $homeDir
     * @param $scanDir
     * @return \ZipArchive
     */
    public static function toScanDir(\ZipArchive $zip, $homeDir, $scanDir)
    {
        if (file_exists($scanDir)) {
            $dh = opendir($scanDir);
            while (false !== ($filename = readdir($dh))) {
                if ($filename != "." && $filename != ".." && $filename != ".DS_Store" && $filename != "temp") {
                    if (is_dir("$scanDir/$filename")) {
                        $zip = static::toScanDir($zip, $homeDir, "$scanDir$filename/");
                    } else {
                        $filePath = str_replace('\\', '/', "$scanDir$filename");
                        if (file_exists($filePath)) {
                            $file = str_replace($homeDir, "", "$scanDir$filename");
                            $zip->addFromString($file, file_get_contents($filePath));
                        }
                    }
                }
            }
        }
        return $zip;
    }
}
