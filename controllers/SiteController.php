<?php

namespace app\controllers;

use app\models\ContactForm;
use app\models\LoginForm;
use Yii;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\httpclient\Client;
use yii\web\Controller;
use yii\web\Response;
use \app\models\User;
use yii\db\Query;

//TODO goal
// A próbafeladat célja egy interfész elkészítése egy külső alkalmazás és a sajátunk között.

// A saját alkalmazásunk rendelkezzen egy táblával, amiben a felhasználói adatokat tároljuk.
// Ezek az adatok a következők: név (vezeték- és keresztnév egy mezőben), életkor, nem, város, ország, email, salt,
// a jelszó sha256-os hash-e illetve a profilképe.

// A felhasználói adatok egy külső szolgáltatásból kerülnek át a miénkbe.
// A külső szolgáltatás megfelelő API végpontját meghívva egy szabványos JSON választ küld nekünk a felhasználók adataival,
// amit feldolgozunk és eltárolunk. Az adatok átvételére legyen lehetőség manuálisan a háttérben is,
// például egy parancs meghívásával. Egyszerre 10 felhasználó adatait emeljük át.

// A használandó szolgáltatás a következő: https://randomuser.me

// Az alkalmazást PHP nyelven készítsd el, egy szimpatikus keretrendszer felhasználásával.

// Az elkészült alkalmazást lehetőleg verziókövetőn megosztva szeretnénk megkapni a futtatás mikéntjét bemutató leírással,
// esetleg egy egyszerű konténer megoldással együtt.

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only'  => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow'   => true,
                        'roles'   => ['@'],
                    ],
                ],
            ],
            'verbs'  => [
                'class'   => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error'   => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class'           => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    public function actionGetusers()
    {

        $client   = new Client([]);
        $response = $client->createRequest()
            ->setMethod('GET')
            ->setUrl('https://randomuser.me/api/?page=1&results=10')
            ->send();

        $array = json_decode($response->content, true);

        //Yii::warning($array, 'RESPONSE');

        $array_content = $array['results'];

        foreach ($array_content as $key => $value) {

            $model = new User();

            $allModels[$key]['name'] = $value['name']['first'] . ' ' . $value['name']['last'];
            $model->name             = $value['name']['first'] . ' ' . $value['name']['last'];

            $allModels[$key]['gender'] = $value['gender'];
            $model->gender             = $value['gender'];

            $allModels[$key]['age'] = $value['dob']['age'];
            $model->age             = $value['dob']['age'];

            $allModels[$key]['city'] = $value['location']['city'];
            $model->city             = $value['location']['city'];

            $allModels[$key]['country'] = $value['location']['country'];
            $model->country             = $value['location']['country'];

            $allModels[$key]['email'] = $value['email'];
            $model->email             = $value['email'];

            $allModels[$key]['salt'] = $value['login']['salt'];
            $model->salt             = $value['login']['salt'];

            $allModels[$key]['password'] = $value['login']['sha256'];
            $model->password             = $value['login']['sha256'];

            $allModels[$key]['picture'] = $value['picture']['large'];
            $model->picture             = $value['picture']['large'];

            $model->save();
        }

        $dataProvider = new ArrayDataProvider([
            'allModels' => $allModels,
        ]);

        return $this->render('getusers', [
            'dataProvider' => $dataProvider,
        ]);

    }

    public function actionDisplayusers()
    {
        $query = User::find();

        $dataProvider = new ActiveDataProvider([
            'query'      => $query,
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);

        return $this->render('displayusers', [
            'dataProvider' => $dataProvider,
        ]);

    }
}
