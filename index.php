<?php
use setasign\FpdiProtection\FpdiProtection;

require_once('fpdi-protection/vendor/autoload.php');
require_once('fpdi-protection/src/autoload.php');
require_once('pdfparser/index.php');
class DmPdfWatermark {
    private $config;
    public function __construct($config){
        $this->config = $config;
    }
    public function addWatermark($params = array()){
        $result;
        // by pdf parser to read file.
        $filename = $this->config['filename'];
        $output_filename = $this->config['output_filename'];
        $watermark_image = $this->config['watermark_image'];
        $watermark_x = $this->config['watermark_x'];
        $watermark_y = $this->config['watermark_y'];
        $text_line_height = $this->config['text_line_height'];
        $tempdir = $this->config['tempdir'];  
        $company = $this->config['company'];  

        $dmPdfParser = new DmPdfParser($filename);
        $pdfInfo = $dmPdfParser->getPdfInfo();
        $page_num = isset($pdfInfo['page_num']) ? $pdfInfo['page_num'] : '';
        $last_page_line_num = isset($pdfInfo['last_page_line_num']) ? $pdfInfo['last_page_line_num'] : '';
        if($last_page_line_num){
            $watermark_y = $last_page_line_num*$text_line_height;
        }

        $watermark_split_images = $this->splitImage($company,$tempdir,$watermark_image,$page_num);
        
        //pic_watermark
        $pdf = new FpdiProtection();

        // get the page count
        $pageCount = $pdf->setSourceFile($filename);
            
        // iterate through all pages
        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++){
            // import a page
            $templateId = $pdf->importPage($pageNo);

            // get the size of the imported page
            $size = $pdf->getTemplateSize($templateId);

            // create a page (landscape or portrait depending on the imported page size)
            if ($size['width'] > $size['height']) $pdf->AddPage('L', array($size['width'], $size['height']));
            else $pdf->AddPage('P', array($size['width'], $size['height']));

            // use the imported page
            $pdf->useTemplate($templateId);

            // 骑缝章
            $watermark_image_page = count($watermark_split_images)>1&&isset($watermark_split_images[$pageNo-1]) ? $watermark_split_images[$pageNo-1] : '';
            if($watermark_image_page){
                $pdf->image($watermark_image_page, 199, 120);
            }

            // 尾章
            if($pageNo == $pageCount){
                // Place the graphics
                $pdf->image($watermark_image, $watermark_x, $watermark_y);
            }
        }

        $this->createFolder($tempdir);

        $pdf->Output('F',$tempdir.'/'.$output_filename);

        $result['path'] = $tempdir.'/'.$output_filename;
        return $result;
    }
    private function splitImage($company,$tempdir,$filename,$split_num){
        $result = array();
        if($tempdir){
            $tempdir .= '/watermark';
        }
        $this->createFolder($tempdir);

        list($width, $height, $type, $attr) = getimagesize($filename);
        $imageObject = imagecreatefrompng($filename);

        $one_width = $width/$split_num;

        //切割小图的宽高
        $imageWHs = array();
        for ($i = 1; $i <= $split_num; $i++){
            $imageWHs[] = array('w'=>$one_width,'h'=>$height,'x'=>($i-1)*$one_width,'y'=>'0');
        } 

        foreach($imageWHs as $j=>$image){
            $picW = $image['w'];                                    
            $picH = $image['h']; 
            
            //透明背景
            $im = imagecreatetruecolor((int)$picW, (int)$picH) or die("Cannot Initialize new GD image stream");//创建小图像
            imagealphablending($im, false);
            imagesavealpha($im, true);
            $white = imagecolorallocatealpha($im,255,255,255,127);
            imagefill($im,0,0,$white);

            $picX = $image['w'];
            $picY = $image['h'];
            $frameX = 0;
            $frameY = 0;
            $x = $image['x'];
            $y = $image['y'];

            /*
            bool imagecopy( resource dst_im, resource src_im, int dst_x, int dst_y, int src_x, int src_y, int src_w, int src_h )
            参数说明：
            参数 说明
            dst_im 目标图像
            src_im 被拷贝的源图像
            dst_x 目标图像开始 x 坐标
            dst_y 目标图像开始 y 坐标，x,y同为 0 则从左上角开始
            src_x 拷贝图像开始 x 坐标
            src_y 拷贝图像开始 y 坐标，x,y同为 0 则从左上角开始拷贝
            src_w （从 src_x 开始）拷贝的宽度
            src_h （从 src_y 开始）拷贝的高度
            */
            imagecopy ( $im, $imageObject, -(int)$frameX, -(int)$frameY, (int)$x, (int)$y, (int)$picX, (int)$picY );//拷贝大图片的一部分到小图片
            $split_path = $tempdir."/watermark_".($j+1).".png";
            imagepng($im,$split_path,0, 100);//创建小图片到磁盘，输出质量为75（0~100）
            imagedestroy($im);//释放与 $im 关联的内存

            $result[] = $split_path;
        }
        imagedestroy($imageObject);//释放与 $imageObject 关联的内存
        return $result;
    }
    public function addPwd($params = array()){
        $result = array();

        $pdf = new FpdiProtection();
        $ownerPassword = $pdf->setProtection([FpdiProtection::PERM_PRINT], '', $params['pwd'], 3);

        $pageCount = $pdf->setSourceFile($params['filename']);
        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $id = $pdf->importPage($pageNo);
            $size = $pdf->getTemplateSize($id);

            $pdf->AddPage($size['orientation'], $size);
            $pdf->useTemplate($id);

            /*$pdf->SetFont('arial');
            $pdf->Cell(0, 12, 'A simple text!');*/
        }
        $this->createFolder($this->config['tempdir']);
        $pdf->Output('F', $this->config['tempdir'].'/'.$params['output_filename']);
        $result['path'] = $this->config['tempdir'].'/'.$params['output_filename'];
        return $result;
    }
    private function createFolder($path) {
        if (!file_exists($path)) {
            $this->createFolder(dirname($path));
            @mkdir($path, 0777);
        }
    }
}

?>


