<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/22
 * Time: 10:27
 */

namespace app\api\controller;

use org\image\driver\Imagick;
use think\Loader;

class GenerateImage
{

    public function htmlConvertPdf()
    {

//            $html = input('html_code');
        Loader::import('mpdf.mpdf.mpdf');
        $html = $this->getHtml();
        $path = PDF;
        $w = 414;
        $h = 736;
        //设置中文
        $mpdf = new \mPDF('utf-8');
        $mpdf->autoScriptToLang = true;
        $mpdf->autoLangToFont = true;
        //设置pdf的尺寸
        $mpdf->WriteHTML('<pagebreak sheet-size="' . $w . 'mm ' . $h . 'mm" />');

        //设置pdf显示方式
        $mpdf->SetDisplayMode('fullpage');
//        $stylesheet1 = file_get_contents($path.'gener.css');
//        $mpdf->WriteHTML($stylesheet1,1);

        //删除pdf第一页(由于设置pdf尺寸导致多出了一页)
        $mpdf->DeletePages(1, 1);

        $mpdf->WriteHTML($html);

        $pdf_name = md5(time()) . '.pdf';
        $pdfPath = $path . '/' . $pdf_name;
        $mpdf->Output($pdfPath, 'F');

//        $this->pdf2png2($pdfPath, $path);

    }

    /**
     * 将pdf文件转化为多张png图片
     * @param string $pdf pdf所在路径 （/www/pdf/abc.pdf pdf所在的绝对路径）
     * @param string $path 新生成图片所在路径 (/www/pngs/)
     *
     * @return array|bool
     */
    function pdf2png($pdf, $path)
    {
        if (!extension_loaded('imagick')) {
            return false;
        }
        if (!file_exists($pdf)) {
            return false;
        }
        $im = new \Imagick();
        $im->setResolution(120, 120); //设置分辨率 值越大分辨率越高
        $im->setCompressionQuality(100);
        $im->readImage($pdf);
        foreach ($im as $k => $v) {
            $v->setImageFormat('png');
            $fileName = $path . md5($k . time()) . '.png';
            if ($v->writeImage($fileName) == true) {
                $return[] = $fileName;
            }
        }
        return $return;
    }

    /**
     * 将pdf转化为单一png图片
     * @param string $pdf pdf所在路径 （/www/pdf/abc.pdf pdf所在的绝对路径）
     * @param string $path 新生成图片所在路径 (/www/pngs/)
     *
     * @throws Exception
     */
    function pdf2png2($pdf, $path)
    {
        try {
            $im = new Imagick();
            $im->setCompressionQuality(100);
            $im->setResolution(120, 120);//设置分辨率 值越大分辨率越高
            $im->readImage($pdf);

            $canvas = new Imagick();
            $imgNum = $im->getNumberImages();
            //$canvas->setResolution(120, 120);
            foreach ($im as $k => $sub) {
                $sub->setImageFormat('png');
                //$sub->setResolution(120, 120);
                $sub->stripImage();
                $sub->trimImage(0);
                $width = $sub->getImageWidth() + 10;
                $height = $sub->getImageHeight() + 10;
                if ($k + 1 == $imgNum) {
                    $height += 10;
                } //最后添加10的height
                $canvas->newImage($width, $height, new ImagickPixel('white'));
                $canvas->compositeImage($sub, Imagick::COMPOSITE_DEFAULT, 5, 5);
            }

            $canvas->resetIterator();
            $canvas->appendImages(true)->writeImage($path . microtime(true) . '.png');
        } catch (Exception $e) {
            throw $e;
        }
    }


    public function getHtml()
    {
        return $html = '<div id="f_list" class="list">
			<div class="list-item">
				<div class="img_box">
					<img class="lazy" data-original="http://img.ejyfile.com/format_img/1463017443.png!500" src="http://img.ejyfile.com/format_img/1463017443.png!500" style="">
					<span class="prices">128.00</span>
					<span class="dot">
						<img class="icon" src="../img/img1/btn_shoucang.png">
						<span class="num">0</span>
					</span>
				</div>
				<div class="info">
					<h3>苏州石公山、明月湾、古樟园一日自驾游</h3>
					<div class="detail">
						<span class="line">南京-->苏州</span>
						出发时间：
						<span class="time">2017-12-02</span>
						共
						<span class="total">1</span>
						天
					</div>
					<div class="tag">
						<span class="tag-items">踏青旅游</span>
						<span class="tag-items">古镇园林</span>
						<span class="tag-items">亲子活动</span>
						<span class="volume_temp">已售:3</span>
					</div>
				</div>
			</div>
			</div>';
    }

    public function getCss(){
        return $css = '*{
    margin: 0;
    padding: 0;
}
body,html{
    background-color: #eee;
}
ul,li{
    list-style: none; /*列表样式：无。*/
    list-style-type: none;/*列表无标记*/
}
a,a:active,a:focus,a:hover{
    text-decoration: none; /* 文本修饰：无 */
    color: inherit;/*inherit 关键字指定一个属性应从父元素继承它的值。*/
}
input{/*内边距和边距不再会增加它的宽度*/
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
}
/*重置表格元素*/
table{
    border-collapse: collapse;
    border-spacing: 0;
}

/*线路列表*/
.list{
    width: 100%;
}

.list-item{
    width: 100%;
    height: 300px;
    background-color: white;
}

.img_box{
    width: 100%;
    height: 200px;
    position: relative;
}

.lazy{
    width: 100%;
    height: 200px;
}

.prices::before{
    content: "￥";
}
.prices{
    position: absolute;
    bottom: 16px;
    left: 16px;
    color: white;
    font-size: 18px;
    background-color: rgba(0,0,0,0.2);
}
.prices::after{
    content: "起";
}
.dot{
    position: absolute;
    bottom: 16px;
    right: 16px;
    color: white;
    background-color: rgba(0,0,0,0.2);
}

.icon{
    height: 30px;
    vertical-align: middle;
    position: relative;
    bottom: 2px;
    left: 5px;
}

.info{
    width: 100%;
    padding: 10px 0 0 6px;
}

.info>h3{
    font-size: 16px;
    font-weight: 500;
}

.detail{
    margin: 10px 0;
    font-size: 14px;
    color: #999;
}

.tag-items{
    display: inline-block;
    height: 20px;
    line-height: 20px;
    padding: 2px 10px;
    color: #CCB68A;
    background-color: #F3EEE0;
    border-radius: 10px;
    font-size: 14px;
}

.volume_temp{
    float: right;
    margin-right: 10px;
    color: #999;
}';
    }
}





