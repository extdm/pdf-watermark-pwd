<?php 
include 'vendor/autoload.php';
class DmPdfParser {
	private $parser;
	private $pdf;
	public function __construct($filename){
        $this->parser = new \Smalot\PdfParser\Parser();
        $this->pdf = $this->parser->parseFile($filename);

    }
    public function getPdfInfo(){
    	$details = $this->pdf->getDetails();
		$page_num = isset($details['Pages']) ? $details['Pages'] : ''; // pdf 总页数;

		$last_page_line_num = 0; // 最后一页文本行数
		$pages_content = array(); // 分页内容
		$pages = $this->pdf->getPages(); //分页信息
		foreach ($pages as $k=>$page) {
			$text = $page->getText();
			$pages_content[] = $text;
			if($k==($page_num-1)){
				$last_page_line_num = count(explode("\n", $text));
			}
		}
		// 减掉页眉页脚(2)+签字栏换行(4)
		$last_page_line_num = $last_page_line_num-6;

		$result = array();
		$result['page_num'] = $page_num;
		$result['last_page_line_num'] = $last_page_line_num;
		$result['pages'] = $pages_content; 
		return $result;
    }

}
?>