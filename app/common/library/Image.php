<?php

namespace app\library;

// ====================================图片缩放====================================#
// Purpose: Resizes and saves image
// Requires : Requires PHP5, GD library.
// Usage Example:
// $Image -> loadImage('images/cars/large/input.jpg');
// $Image -> resizeImage(150, 100);
// $Image -> saveImage('images/cars/large/output.jpg', 100);
// ========================================================================#
class Image
{
    // *** Class variables
    private $image;
    private $width;
    private $height;
    private $imageResized;

    private $businessId      = BUSINESS_ID;                                   //图片服务业务id
    private $businessKey     = BUSINESS_KEY;                                    //图片服务业务key
    private $imageWServerUrl = IMAGE_UPLOAD_PATH;     //上传图片服务器


    public function __construct($fileName = null, $extension = null, $angle = null)
    {
        if ($fileName) {
            $this->loadImage($fileName, $extension, $angle);
        }
    }

    // --------------------------------------------------------
    public function loadImage($fileName, $extension = null, $angle = null)
    {
        // *** Open up the file
        $this->image = $this->openImage($fileName, $extension, $angle);

        // *** Get width and height
        $this->width = imagesx($this->image);
        $this->height = imagesy($this->image);
    }

    private function openImage($file, $extension = null, $angle = null)
    {
        // *** Get extension
        if (!$extension) {
            $extension = strtolower(strrchr($file, '.'));
        } else {
            $extension = '.' . $extension;
        }
        switch ($extension) {
            case '.jpg':
            case '.jpeg':
                $img = @imagecreatefromjpeg($file);
                break;
            case '.gif':
                $img = @imagecreatefromgif($file);
                break;
            case '.png':
                $img = @imagecreatefrompng($file);
                break;
            default:
                $img = false;
                break;
        }
        if ($angle) {
            $img = imagerotate($img, $angle, 0);
        }
        return $img;
    }

    // --------------------------------------------------------
    public function resizeImage($newWidth, $newHeight, $option = "auto")
    {
        // *** Get optimal width and height - based on $option
        $optionArray = $this->getDimensions($newWidth, $newHeight, $option);

        $optimalWidth = $optionArray['optimalWidth'];
        $optimalHeight = $optionArray['optimalHeight'];

        // *** Resample - create image canvas of x, y size

        $this->imageResized = imagecreatetruecolor($optimalWidth, $optimalHeight);

        imagecopyresampled($this->imageResized, $this->image, 0, 0, 0, 0, $optimalWidth, $optimalHeight, $this->width, $this->height);

        // *** if option is 'crop', then crop too
        if ($option == 'crop') {
            $this->crop($optimalWidth, $optimalHeight, $newWidth, $newHeight);
        }
    }

    // --------------------------------------------------------
    private function getDimensions($newWidth, $newHeight, $option)
    {
        switch ($option) {
            case 'exact':
                $optimalWidth = $newWidth;
                $optimalHeight = $newHeight;
                break;
            case 'portrait':
                $optimalWidth = $this->getSizeByFixedHeight($newHeight);
                $optimalHeight = $newHeight;
                break;
            case 'landscape':
                $optimalWidth = $newWidth;
                $optimalHeight = $this->getSizeByFixedWidth($newWidth);
                break;
            case 'auto':
                $optionArray = $this->getSizeByAuto($newWidth, $newHeight);
                $optimalWidth = $optionArray['optimalWidth'];
                $optimalHeight = $optionArray['optimalHeight'];
                break;
            case 'crop':
                $optionArray = $this->getOptimalCrop($newWidth, $newHeight);
                $optimalWidth = $optionArray['optimalWidth'];
                $optimalHeight = $optionArray['optimalHeight'];
                break;
        }
        return array(
            'optimalWidth' => $optimalWidth,
            'optimalHeight' => $optimalHeight
        );
    }

    // --------------------------------------------------------
    private function getSizeByFixedHeight($newHeight)
    {
        $ratio = $this->width / $this->height;
        $newWidth = $newHeight * $ratio;
        return $newWidth;
    }

    private function getSizeByFixedWidth($newWidth)
    {
        $ratio = $this->height / $this->width;
        $newHeight = $newWidth * $ratio;
        return $newHeight;
    }

    private function getSizeByAuto($newWidth, $newHeight)
    {
        if ($this->height < $this->width) {         // *** Image to be resized is wider
            // (landscape)
            $optimalWidth = $newWidth;
            $optimalHeight = $this->getSizeByFixedWidth($newWidth);
        } elseif ($this->height > $this->width) {         // *** Image to be resized is
            // taller (portrait)
            $optimalWidth = $this->getSizeByFixedHeight($newHeight);
            $optimalHeight = $newHeight;
        } else {         // *** Image to be resizerd is a square
            if ($newHeight < $newWidth) {
                $optimalWidth = $newWidth;
                $optimalHeight = $this->getSizeByFixedWidth($newWidth);
            } elseif ($newHeight > $newWidth) {
                $optimalWidth = $this->getSizeByFixedHeight($newHeight);
                $optimalHeight = $newHeight;
            } else {
                // *** Sqaure being resized to a square
                $optimalWidth = $newWidth;
                $optimalHeight = $newHeight;
            }
        }

        return array(
            'optimalWidth' => $optimalWidth,
            'optimalHeight' => $optimalHeight
        );
    }

    // --------------------------------------------------------
    private function getOptimalCrop($newWidth, $newHeight)
    {
        $heightRatio = $this->height / $newHeight;
        $widthRatio = $this->width / $newWidth;

        if ($heightRatio < $widthRatio) {
            $optimalHeight = $newHeight;
            $optimalWidth = round($this->width / $heightRatio);
        } else {
            $optimalHeight = round($this->height / $widthRatio);

            $optimalWidth = $newWidth;
        }

        return array(
            'optimalWidth' => $optimalWidth,
            'optimalHeight' => $optimalHeight
        );
    }

    // --------------------------------------------------------
    private function crop($optimalWidth, $optimalHeight, $newWidth, $newHeight)
    {
        // *** Find center - this will be used for the crop
        $cropStartX = ($optimalWidth / 2) - ($newWidth / 2);
        $cropStartY = ($optimalHeight / 2) - ($newHeight / 2);

        $crop = $this->imageResized;
        // imagedestroy($this->imageResized);

        // *** Now crop from center to exact requested size
        $this->imageResized = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($this->imageResized, $crop, 0, 0, $cropStartX, $cropStartY, $newWidth, $newHeight, $newWidth, $newHeight);
    }

    // --------------------------------------------------------
    public function saveImage($savePath, $imageQuality = "100")
    {
        // *** Get extension
        $extension = strrchr($savePath, '.');
        $extension = strtolower($extension);

        switch ($extension) {
            case '.jpg':
            case '.jpeg':
                if (imagetypes() & IMG_JPG) {
                    imagejpeg($this->imageResized, $savePath, $imageQuality);
                }
                break;

            case '.gif':
                if (imagetypes() & IMG_GIF) {
                    imagegif($this->imageResized, $savePath);
                }
                break;

            case '.png':
                // *** Scale quality from 0-100 to 0-9
                $scaleQuality = round(($imageQuality / 100) * 9);

                // *** Invert quality setting as 0 is best, not 9
                $invertScaleQuality = 9 - $scaleQuality;

                if (imagetypes() & IMG_PNG) {
                    imagepng($this->imageResized, $savePath, $invertScaleQuality);
                }
                break;

            // ... etc

            default:
                // *** No extension - No save.
                break;
        }

        imagedestroy($this->imageResized);
    }

    // --------------------------------------------------------



    /**
     * 发送文件到图片服务器 , 只支持单张图片
     */
    public function sendImage($path, $mimetype, $postname)
    {
        $handle     = fopen($path, 'rb');//使用打开模式为r
        $fileBinary = fread($handle, filesize($path));//读为二进制
        fclose($handle);

        $milliseconds = round(microtime(true) * 1000);        //当前时间的毫秒

        $post_data = [
            't'    => $milliseconds,
            'file' => (function_exists('curl_file_create')) ? curl_file_create($path, $mimetype, $postname) : '@' . $path,
            'sign' => md5($this->businessId . $this->businessKey . $milliseconds . $fileBinary),
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->imageWServerUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 4);//最大执行时间为4s;
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //返回在$return里面不直接显示
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1); //二进制上传
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        $return = curl_exec($ch);
        curl_close($ch);

        //返回json格式 {"code":200,"message":"","redirect":"","value":{"format":"JPEG","height":48,"imgId":"c0b70748cac94f98839317dc0b44c62bz","md5":"9960fc4aef22ffbc554173a7a5ba22d2","width":48}}
        return json_decode($return);
    }
}
