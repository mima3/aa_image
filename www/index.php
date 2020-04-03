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
// なし

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
    $r = imagettfbbox($size, 0, $font, $text );
    $width = $r[2] - $r[6];
    $height = $r[3] - $r[7];
    $img = imagecreatetruecolor($width + $size, $height + $size);
    
    $front = imagecolorallocate($img, 0, 0, 0);
    $back  = imagecolorallocate($img, 255, 255, 255);
    imagefilledrectangle($img, 0, 0, $width + $size, $height + $size, $back);
    imagettftext($img, $size, 0, 0 , $size, $front, $font, $text);
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
    if (strlen($key) > 1024 * 10) {
        $response->getBody()->write("サイズが大きすぎるため作成できません。");
        return $response->withStatus(500);
    }

    $rec = $imageModel->get($key);
    if ($rec) {
        $image = $rec->data;
    } else {
        $image = convertImage($key, $this->get('config')['FONT_PATH'], 12);
        if (strlen($image) > 1024 * 10) {
            $response->getBody()->write("サイズが大きすぎるため作成できません。");
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


$app->run();
