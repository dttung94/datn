<?php
namespace backend\modules\service\forms\mail;


use common\entities\service\ServiceMail;
use common\entities\user\UserInfo;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

/**
 * Class MailSearchForm
 * @package backend\modules\service\forms\mail
 *
 * @property string $keyword
 */
class MailSearchForm extends ServiceMail
{
    public $keyword;

    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [["keyword"], "safe"]
        ]);
    }

    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'keyword' => \Yii::t('common.label', 'Keyword')
        ]);
    }

    public function search()
    {
        $query = parent::search();
        if ($this->keyword != null) {
            $query->andWhere(['LIKE', 'subject', $this->keyword]);
//            $query->orWhere(['LIKE', 'content', $this->keyword]);
            $query->orWhere(['LIKE', 'from_email', $this->keyword]);
            $query->orWhere(['LIKE', 'from_name', $this->keyword]);
//            $query->orWhere(['LIKE', 'tag', $this->keyword]);
            $query->orWhere(['LIKE', 'result', $this->keyword]);
        }

        if ($this->created_at) {
            $query->andWhere(['date( ' . ServiceMail::tableName() . '.created_at)' => $this->created_at]);
        }

        if (!empty($_GET['role'])) {
            $query->andWhere(['role' => $_GET['role']]);
        } else {
            $query->andWhere(['role' => UserInfo::ROLE_ADMIN]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $dataProvider->setSort([
            'defaultOrder' => ['created_at' => SORT_DESC],
            'attributes' => [
                'type',
                'status',
                'created_at',
            ]
        ]);
        return $dataProvider;
    }
}
