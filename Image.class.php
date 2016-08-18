<?php  

	/*
	 *图片处理类 
	 */
	class Image {
		//允许图片的mime类型
		private $mime_list = array ('image/jpeg' , 'image/png' , 'image/gif') ;
		//需要进行处理的路径
		private $img_path  ; 
		//对应图片类型的图片相关操作函数 , 字符串表示
		private $func ;
		//存放错误信息
		private $error = array() ;

		/**
		 * @param [string] $img_path [需要进行处理的图片文件路径]
		 * @return [bool] [错误返回假  , 并将错误信息存储]
		 */
		public function __construct ($img_path) {
				if(!file_exists($img_path)) {
					$this -> error['img'] = '文件不存在 ,请重新检查' ;
					return false ;
				}
				$this -> img_path = $img_path ;
				$this -> initFuns() ;
		}

		/**
		 * [initFuns 初始化要使用的图片处理相关函数]
		 * @return [bool] [错误返回假  ,并将错误信息存储	正确 , 处理化了@funs属性]
		 */
		private function initFuns() {
			$mime= getimagesize($this -> img_path) ;
			//****传的是正确的 但是跑到错误里去了
			if (!in_array($mime['mime'], $this -> mime_list)) {
				$this -> error['mime'] = $mime['mime'] .'类型的文件是不能被处理的' ;
				return false ;
			}

			$img_mime = substr(strrchr($mime['mime'] , '/'  ) ,1);
			$this -> func = $img_mime;
		}

		/**
		 * [getError 获取可能会出现的错误信息]
		 * @return [bool & string] [错误返回 false 	真确返回错误的具体信息]
		 */
		public function getError() {
			if(!empty($this -> error)) {
				$error =  '' ;
				foreach ($this -> error as  $value) {
					$error .= $value .'<br>' ;
				}
				return '出现错误 , 错误信息如下 : <br>' . $error ;
			} else {
				return false ;
			}
		}

		/**
		 * [makeThumb 对图片重新取样, 获得一张缩略图或扩大图]
		 * @param  [int] $area_w [生成的新图片的宽]
		 * @param  [int] $area_h [生成的新图片的高]
		 * @param  [string] $dst_path [生成的新图片存放的位置]
		 * @return [file]         [正确返回一个图片文件]
		 */
		public function makeThumb($area_w , $area_h , $dst_path) {
				//动态的方法, 根据传入的图片类型 , 自动变化
				$func_from = 'imagecreatefrom'.$this -> func ;
				$func_make = 'image'.$this -> func ;
				//将日期按日期存放
				$dst_path .= date('Ymd').'/' ;
				//判断要存放的路径 是否存在 
				if(!is_dir($dst_path)) {
					mkdir($dst_path , 0777 , true) ;
				}

				//要生成唯一的文件名, 避免出现重复后覆盖
				//图片的后缀
				$fix = strrchr($this -> img_path ,'.') ;
				//照片的唯一名字
				$img_body = uniqid('' , true) ;
				// 拼接存放文件的全路径
				$dst_path .=$img_body . $fix ;

				//2. 被重新取样的图像资源
				$src_image = $func_from($this -> img_path) ;
				//3. 取样文件的 输出weizhi
				$dst_x = 0 ;
				$dst_y = 0 ;
				//4. 被取样文件的截取位置
				$src_x = 0 ;
				$src_y = 0 ;
				//5. 被取样文件的宽高
				$src_w = imagesx($src_image) ;
				$src_h = imagesy($src_image) ;
				//6. 取样文件的 宽高 , 实现在 $area_w *  $area_h 范围内的等比例缩放
				if($src_w > $src_h) {
					$dst_w = $area_w ;
					$dst_h = ($area_h/$src_w) * $src_h ;
				} else {
					$dst_h =$area_w ;
					$dst_w = ($area_h/$src_h) * $src_w ;
				}
				//1. 创建一个取样后的空图像资源 
				$dst_image = imagecreatetruecolor($dst_w, $dst_h) ;

				//重新取样
				imagecopyresampled($dst_image, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h) ;
				$func_make($dst_image , $dst_path);

		}
	}
	echo '<pre>' ;
	$image = new Image('./1.jpg') ;
	$image -> makeThumb(100 , 100 , './thumb/') ;