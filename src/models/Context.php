<?php

namespace DevGroup\Multilingual\models;

use DevGroup\Multilingual\Multilingual;
use DevGroup\Multilingual\traits\FileActiveRecord;
use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii2tech\filedb\ActiveRecord;

/**
 * Class Context
 *
 * @property integer $id
 * @property string $name
 * @property string $domain
 * @property integer $tree_root_id
 * @property Language[] $languages
 * @property string $db_table_postfix
 */
class Context extends ActiveRecord implements ContextInterface
{
    use FileActiveRecord;

    public function rules()
    {
        return [
            [['id'], 'integer', 'on' => ['search']],
            [['name', 'domain', 'tree_root_id'], 'required', 'except' => ['search']],
            [['name', 'domain'], 'string', 'max' => 50],
            [['tree_root_id'], 'integer'],
            [['default_language_id'], 'integer'],
        ];
    }

    /**
     * @return Language[]
     */
    public function getLanguages()
    {
//        return $this->hasMany(Language::class, ['context_id' => 'id'])
//            ->orderBy(['sort_order' => SORT_ASC])
//            ->indexBy('id');
        $result = [];
        $langs = Language::getAll();

        foreach ($langs as $lang) {
            if (isset($lang->context_rules[$this->id])) {
                $result[] = $lang;
            }
        }
        return $result;
    }

    public function search($params = [])
    {
        $query = static::find();
        $dataProvider = new ActiveDataProvider(
            [
                'query' => $query,
            ]
        );
        $this->load($params);
        if (!$this->validate()) {
            return $dataProvider;
        }
        $query->andFilterWhere(['id' => $this->id])
            ->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'domain', $this->domain])
            ->andFilterWhere(['tree_root_id' => $this->tree_root_id]);
        return $dataProvider;
    }

    /**
     * @inheritdoc
     */
    public static function getListData()
    {
        return ArrayHelper::merge(
            [null => 'Multi-context'],
            ArrayHelper::map(static::find()->all(), 'id', 'name')
        );
    }
}
