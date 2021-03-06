<?php

namespace backend\controllers;

use common\models\DomainColumn;
use common\models\Fan;
use common\models\MipFlag;
use common\models\PushArticle;
use Yii;
use common\models\Domain;
use common\models\search\DomainSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * DomainController implements the CRUD actions for Domain model.
 */
class DomainController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Domain models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new DomainSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Domain model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Domain model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Domain();
        if ($model->load(Yii::$app->request->post())) {
            $post = Yii::$app->request->post('Domain');
            $oldDomain = Domain::find()->where(['name' => $post['name']])->one();
            if (!empty($oldDomain)) {
                Yii::$app->getSession()->setFlash('error', '该域名已经存在');
                return $this->redirect(['create', 'model' => $model]);
            }

            $model->save();

            //自动创建一个home 类目
            $data = [
                'name' => 'home',
                'tags' => '首页',
                'domain_id' => $model->id,
            ];

            list($code, $msg) = DomainColumn::createOne($data);

            //自动创建一个泛目录
            $data = [
                'name' => $model->start_tags,
                'tags' => '泛目录',
                'domain_id' => $model->id,
            ];

            list($codeFan, $msgFan) = DomainColumn::createOne($data);

            //自动创建一个label
            $data = [
                'name' => 'label',
                'tags' => '标签',
                'domain_id' => $model->id,
            ];

            list($codeFan, $msgFan) = DomainColumn::createOne($data);

            if ($codeFan < 0) {
                Yii::$app->getSession()->setFlash('error', $msgFan);
                return $this->redirect(['create', 'model' => $model]);
            }

            //规则配置
            Fan::getRules($model->id);
            if ($code < 0) {
                Yii::$app->getSession()->setFlash('error', $msg);
                return $this->redirect(['create', 'model' => $model]);
            }


            //创建一个新push_article表
            $_GET['from_id'] = $model->id;
            PushArticle::createTable($model->id);

            return $this->redirect(['view', 'id' => $model->id]);
        }
        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Domain model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            //规则配置
            Fan::getRules($id);
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Domain model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Domain model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Domain the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Domain::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionRefresh()
    {
        Fan::refreshAll();
    }

    public function actionPushUrl()
    {
        $test = Yii::$app->request->get('test', 0);
        $type = Yii::$app->request->get('type', 1);
        MipFlag::pushUrl(Yii::$app->request->get('id'), $test, $type);
    }

    /** 获取模板 */
    public function actionGetDomain()
    {
        $q = Yii::$app->request->get('q', '');
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $out = ['results' => ['id' => '', 'text' => '']];
        if (!$q) {
            return $out;
        }

        $data = Domain::find()

            ->select('id,name as text')
            ->andFilterWhere(['like', 'name', $q])
            ->limit(30)
            ->asArray()
            ->all();

        foreach ($data as &$item) {
            $item['text'] = '<strong>' . $item['text'] . '</strong>' ;
        }

        $out['results'] = array_values($data);
        return $out;
    }
}
