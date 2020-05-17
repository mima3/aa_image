<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteContext;

require_once 'vendor/autoload.php';
require_once './config.php';

$container = new \DI\Container();

// 依存関係コンテナを提供する必要はありません。
// ただし、その場合は、AppFactoryでappを作成する前にコンテナのインスタンスを提供する必要があります
AppFactory::setContainer($container);

$app = AppFactory::create();
$app->setBasePath(BASE_PATH);

if (DEBUG) {
    // If you are adding the pre-packaged ErrorMiddleware set `displayErrorDetails` to `true`
    $app->addErrorMiddleware(true, true, true);
} else {
    // 本番環境ではエラーを表示しない
    // If you are adding the pre-packaged ErrorMiddleware set `displayErrorDetails` to `false`
    $app->addErrorMiddleware(false, true, true);
}
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();


// View
$container->set('view', function () {
    return \Slim\Views\Twig::create(TEMPLATE_PATH, ['cache' => VIEW_CACHE_PATH]);
});

// Model
$existDb = file_exists(DB_PATH);
\ORM::configure('sqlite:' . DB_PATH);
$database = \ORM::get_db();
$imageModel = new \aa_image\Model\ImageModel($app, $database);

if (!$existDb) {
    $imageModel->setup();
}
$container->set('database', function () use ($database) {
    return $database;
});

$container->set('imageModel', function () use ($imageModel) {
    return $imageModel;
});

// 設定の保存
$container->set('config', function ()  {
    $config = [
        'FONT_PATH' => FONT_PATH,
        'BASE_PATH' => BASE_PATH,
        'MAX_CACHE' => MAX_CACHE
    ];
    return $config;
});


function convertImage($key, $font, $size)
{
    $decode_data = base64_decode($key);
    $text = gzinflate($decode_data);
    $r = imagettfbbox($size, 0, $font, $text);
    $width = abs($r[2] - $r[6]);
    $height = abs($r[3] - $r[5]);
    $img = imagecreatetruecolor($width, $height);
    imagealphablending($img, false);
    imagesavealpha($img, true);
    
    $front = imagecolorallocate($img, 0, 0, 0);
    $back  = imagecolorallocate($img, 255, 255, 255);
    imagefilledrectangle($img, 0, 0, $width, $height, $back);
    imagettftext($img, $size, 0, 0, $r[7] * -1, $front, $font, $text);
    ob_start();
    imagepng($img);
    $ret = ob_get_contents();
    ob_end_clean();
    imagedestroy($img);
    return $ret;
}


// Controller
$app->get('/image', function (Request $request, Response $response, $args) {
    // この時点でエンコードをしてくれる
    $key = $request->getQueryParams()['d'];

    $imageModel = $this->get('imageModel');
    $image = null;
    if (strlen($key) > 1024 * 2) {
        $response->getBody()->write("文字数が多すぎるため作成できません。");
        return $response->withStatus(500);
    }

    $rec = $imageModel->get($key);
    if ($rec) {
        $image = $rec->data;
    } else {
        $image = convertImage($key, $this->get('config')['FONT_PATH'], 12);
        if (strlen($image) > 1024 * 10) {
            $response->getBody()->write("画像サイズが大きすぎるため作成できません。");
            return $response->withStatus(500);
        }
        
        $imageModel->append($key, $image);
    }

    // 
    $response->getBody()->write($image);
    return $response
        ->withHeader('Content-Type', 'image/png')
        ->withStatus(200);
});
$app->post('/encode', function (Request $request, Response $response, $args) {
    $result = [];
    $body = $request->getParsedBody();
    if (strlen($body['text']) > 1024 * 10) {
        $result['error'] = "文字数が多すぎるため作成できません。";
    } else {
        $code = base64_encode(gzdeflate($body['text'], 9));
        $image = convertImage($code, $this->get('config')['FONT_PATH'], 12);
        if (strlen($image) > 1024 * 10) {
            $result['error'] = "画像サイズが大きすぎるため作成できません。";
        }
        $result['code'] = urlencode($code);
    }
    $response->getBody()->write(json_encode($result));
    return $response->withHeader('Content-Type', 'application/json')
                    ->withStatus(200);    
});
$app->post('/decode', function (Request $request, Response $response, $args) {
    $result = [];
    $body = $request->getParsedBody();
    $decode_data = base64_decode(urldecode($body['code']));
    $text = gzinflate($decode_data);
    $result['text'] = $text;
    $response->getBody()->write(json_encode($result));
    return $response->withHeader('Content-Type', 'application/json')
                    ->withStatus(200);    
});
$app->get('/main', function (Request $request, Response $response, $args) {
    return $this->get('view')->render(
        $response,
        'main.twig',
        [
            'BASE_PATH' => $this->get('config')['BASE_PATH']
        ]
    );    
});

$app->run();
