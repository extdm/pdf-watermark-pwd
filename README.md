# pdf-watermark-pwd
给合同盖尾章及骑缝章、设置密码等，目前仅支持对pdf文件进行盖章，尾章具体位置可能需要按实际情况进行调整;

# Params
```php
// TO ADD WATERMARK
$config = array();
$config['company'] = 'company'; // 公司名称
$config['tempdir'] = 'temp'; // 临时目录
$config['filename'] = 'resource/order.pdf'; // 源始PDF文件(含目录)
$config['output_filename'] = 'order-output.pdf'; // 盖章之后输出的文件名(不含目录)
$config['watermark_image'] = 'resource/'.$config['company'].'.png'; // 合同章PNG文件
$config['watermark_x'] = '120'; //尾章 X轴默认值
$config['watermark_y'] = '85'; //尾章 Y轴默认值 (实际位置会接合行高自动计算)
$config['text_line_height'] = '8'; // 默认行高所占比例 (尾章 Y轴会根据行高自动计算位置)
$dmPdfWatermark = new DmPdfWatermark($config);
$doResut = $dmPdfWatermark->addWatermark();
// 盖章文件完整目录
$watermark_path = isset($doResut['path']) ? $doResut['path'] : ''; 

// 此处可扩展：盖章PDF文件内页转成图片功能正在测试中，让其更像打印扫描件...

// TO ADD PWD 给PDF文件设置密码(加密，不可复制，保护文件)
$params = array();
$params['filename'] = $watermark_path;
$params['output_filename'] = 'order-output-pwd.pdf'; // 盖章之后输出的文件名(不含目录)
$params['pwd'] = '123456'; // 设置默认密码
$doResut = $dmPdfWatermark->addPwd($params);
// 加密文件完整目录
$pwd_path = isset($doResut['path']) ? $doResut['path'] : '';
```

# Test
test.php
