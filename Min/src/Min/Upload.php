<?php

namespace Min;

class Upload
{ 
    private $error = '';   
    protected $files;
    protected $rule = [
		//'ext' => ['gif', 'jpg', 'jpeg', 'bmp', 'png'],
		'ext' 		=> ['jpg', 'jpeg', 'png'],
		'size' 		=> 1048576,
		'base_path' => \PUBLIC_PATH,
		'host' 		=> '//'. \PUBLIC_URL,
		'repalce' 	=> true
	];


    public function __construct($name)
    { 
        $this->files = $_FILES[$name];
		$this->files['ext'] = strtolower(pathinfo($this->getInfo('name'), \PATHINFO_EXTENSION));
    }
  
    /**
     * 获取上传文件的信息
     * @param  string   $name
     * @return array|string
     */
    public function getInfo($key = '')
    {
        return isset($this->files[$key]) ? $this->files[$key] : null;
    }

    /**
     * 获取文件类型信息
     * @return string
     */
    public function getMime()
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        return finfo_file($finfo, $this->getInfo('tmp_name'));
    }

    /**
     * 检测是否合法的上传文件
     * @return bool
     */
    public function isValid()
    {
        return is_uploaded_file($this->getInfo('tmp_name'));
    }
	
	/**
     * 设置文件的命名规则
     * @param  string   $rule    文件命名规则
     * @return $this
     */
    public function setRule(array $rule)
    {
        $this->rule = array_merge($this->rule, $rule);
        return $this;
    }

    /**
     * 检测上传文件大小
     * @param  integer   $size    最大大小
     * @return bool
     */
    public function checkSize()
    {
		$size = $this->getInfo('size');
        if (empty($size) || $size > $this->rule['size']) {
            return false;
        }
        return true;
    }
	
    /**
     * 检测上传文件后缀
     * @param  array|string   $ext    允许后缀
     * @return bool
     */
    public function checkExt()
    {
        if (in_array($this->getInfo('ext'), $this->rule['ext'], true)) {
            return true;
        }
        return false;
    }

    /**
     * 检测上传文件类型
     * @param  array|string   $mime    允许类型
     * @return bool
     */
    public function checkMime()
    {
        if (in_array(strtolower($this->getMime()), $this->rule['mime'], true)) {
            return true;
        }
        return false;
    }
	
    /**
     * 检测图像文件
     * @return bool
     */
    public function checkImg()
    {
        if (in_array($this->getInfo('ext'), ['jpg', 'jpeg', 'png'], true) && in_array($this->getImageType($this->getInfo('tmp_name')), [ \IMAGETYPE_JPEG, \IMAGETYPE_PNG], true)) {
            return true;
        }
        return false;
    }

    // 判断图像类型
    protected function getImageType($image)
    {
        if (function_exists('exif_imagetype')) {
            return exif_imagetype($image);
        } else {
            $info = getimagesize($image);
            return $info[2];
        }
    }
	
	/**
     * 检查目录是否可写
     * @param  string   $path    目录
     * @return boolean
     */
    protected function checkPath($path)
    {
		$dir = dirname($path);
		if (!is_dir($dir)) {
			if (mkdir($dir, 0755, true)) {
				return true;
			} else {
				watchdog("目录{$dir}创建失败！", 'upload', 'NOTICE');
				$this->error = "目录创建失败！";
				return false;
			}
		} else {
			return true;
		}
    }
	
	/**
     * 检测上传文件
     * @param  array   $rule    验证规则
     * @return bool
     */
    public function check(array $rule)
    {
		if (!empty($rule)) {
			$this->setRule($rule);
		}
        /* 检查文件大小 */
        if (!$this->checkSize()) {
            $this->error = '上传文件大小不符！';
            return false;
        }

        /* 检查文件Mime类型 
        if (!$this->checkMime()) {
            $this->error = '上传文件MIME类型不允许！';
            return false;
        }
		*/
        /* 检查文件后缀 */
        if (!$this->checkExt()) {
            $this->error = '上传文件后缀不允许';
            return false;
        }

        /* 检查图像文件 */
        if (!$this->checkImg()) {
            $this->error = '非法图像文件！';
            return false;
        }

        return true;
    }
    /**
     * 移动文件
     * @param  string           $path    保存路径
     * @param  string|bool      $savename    保存的文件名 默认自动生成
     * @param  boolean          $replace 同名文件是否覆盖
     * @return false|SplFileInfo false-失败 否则返回SplFileInfo实例
     */
    public function save($rule = [])
    {
        if (!empty($this->files['error'])) {
            $this->error($this->files['error']);
            return false;
        }

        // 检测合法性
        if (!$this->isValid()) {
            $this->error = '非法上传文件';
            return false;
        }

        // 验证上传
        if (!$this->check($rule)) {
            return false;
        }
       
        $file_name = $this->buildSaveName();
		$file_path = $this->rule['base_path']. $file_name;
 
        // 检测目录
        if (false === $this->checkPath($file_path)) {
            return false;
        }

        /* 不覆盖同名文件 */
        if (!$this->rule['repalce'] && is_file($file_path)) {
            $this->error = '存在同名文件' . $file_path;
            return false;
        }

        /* 移动文件 */
        if (!move_uploaded_file($this->getInfo('tmp_name'), $file_path)) {
            $this->error = '文件上传保存错误！';
            return false;
        }
		
		$this->files['url'] = $this->rule['host']. $file_name;
		return true;
		
    }

    /**
     * 获取保存文件名
     * @param  string|bool   $savename    保存的文件名 默认自动生成
     * @return string
     */
    protected function buildSaveName()
    {
		$dest_file  =  '/attached';
		$dest_file .=  date('/Y/m/d/');
		$dest_file .=  hash_file('md5', $this->getInfo('tmp_name'));
		$dest_file .=  '.';
		$dest_file .=  $this->getInfo('ext');
        return $dest_file;
    }

    /**
     * 获取错误代码信息
     * @param int $errorNo  错误号
     */
    private function error($errorNo)
    {
        switch ($errorNo) {
            case 1:
            case 2:
                $this->error = '上传文件大小超过了最大值！';
                break;
            case 3:
                $this->error = '文件只有部分被上传！';
                break;
            case 4:
                $this->error = '没有文件被上传！';
                break;
            case 6:
                $this->error = '找不到临时文件夹！';
                break;
            case 7:
                $this->error = '文件写入失败！';
                break;
            default:
                $this->error = '未知上传错误！';
        }
    }

    /**
     * 获取错误信息
     * @return mixed
     */
    public function getError()
    {
        return $this->error;
    }

}
