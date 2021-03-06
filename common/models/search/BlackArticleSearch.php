<?php

namespace common\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\BlackArticle;

/**
 * BlackArticleSearch represents the model behind the search form of `common\models\BlackArticle`.
 */
class BlackArticleSearch extends BlackArticle
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'type', 'type_id', 'key_id', 'word_count'], 'integer'],
            [['title', 'keywords', 'cut_word', 'image_urls', 'from_path', 'part_content', 'content', 'created_at', 'updated_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = BlackArticle::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'type' => $this->type,
            'type_id' => $this->type_id,
            'key_id' => $this->key_id,
            'word_count' => $this->word_count,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'keywords', $this->keywords])
            ->andFilterWhere(['like', 'cut_word', $this->cut_word])
            ->andFilterWhere(['like', 'image_urls', $this->image_urls])
            ->andFilterWhere(['like', 'from_path', $this->from_path])
            ->andFilterWhere(['like', 'part_content', $this->part_content])
            ->andFilterWhere(['like', 'content', $this->content]);

        return $dataProvider;
    }
}
