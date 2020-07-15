<?php
require_once('index.php');

// temp path.
$microtime = microtime();
$microtime_array = explode(' ',$microtime);
$temp_path_code = str_pad(($microtime_array[0]*1000000),6,'0',STR_PAD_RIGHT);

// to add watermark.
$config = array();
$config['company'] = 'company';
$config['tempdir'] = 'temp';//sprintf('temp/%s/%s/%s/%s',$config['company'],date('Ym'),date('d'),date('His').$temp_path_code);
$config['filename'] = 'resource/order.pdf';
$config['output_filename'] = 'order-output.pdf';
$config['watermark_image'] = 'resource/'.$config['company'].'.png';
$config['watermark_x'] = '120';
$config['watermark_y'] = '85';
$config['text_line_height'] = '8';

$dmPdfWatermark = new DmPdfWatermark($config);
$doResut = $dmPdfWatermark->addWatermark();
//var_dump($doResut);

$watermark_path = isset($doResut['path']) ? $doResut['path'] : '';

// to add pwd.
$params = array();
$params['filename'] = $watermark_path;
$params['output_filename'] = 'order-output-pwd.pdf';
$params['pwd'] = '123456';

$doResut = $dmPdfWatermark->addPwd($params);
$pwd_path = isset($doResut['path']) ? $doResut['path'] : '';
//var_dump($doResut);

echo sprintf('pdf：%s <br/>pdf watermark：%s <br/> pdf setting pwd：%s',$config['filename'],$watermark_path,$pwd_path);

?>


