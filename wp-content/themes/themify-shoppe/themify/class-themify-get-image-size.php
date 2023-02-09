<?php 

final class Themify_Get_Image_Size{
	
	private static $method=null;
	private static $imageType=null;
	private static $size=null;
	private static $TMP_DIR=null;
	private static $handle=null;
	private static $URL=null;
	private static $currentSize=0;
	private static $w=null;
	private static $h=null;
	private static $isRegistered=false;

        
	const MAX_FILE=50;//maximum file convert in 1 php script execution
	const GIF_MAX_SIZE=11;
	const PNG_MAX_SIZE=25;
	const BMP_MAX_SIZE=29;
	const WEBP_MAX_SIZE =30;
	const JPG_MAX_SIZE=24576;

	
	public static function getSize($url,$isLocal=false){
		if(empty($url)){
			return false;
		}
		if(self::$isRegistered===false){
			self::$isRegistered=true;
			add_action( 'shutdown', array(__CLASS__,'shutdown'),1);
		}
		if($isLocal===false){
			static $index=0;
			++$index;
			if(self::$method===null){
				if(function_exists('curl_init')){//prefer curl
					self::$method='curl';
				}
				else{
					self::$method=ini_get('allow_url_fopen');
					if(self::$method!=='off' && self::$method!=='OFF' && self::$method!=='0' && self::$method!==false){
						self::$method='file';
					}
					else{
						self::$method=false;
					}
				}
			}

			if(self::$method===false || $index>self::MAX_FILE){
				return false;
			}
		}
		self::$URL=$url;
		$data=self::parseSize($url,$isLocal);
		if($data===false && self::$w!==null && self::$h!==null){
			$data=array('w'=>self::$w,'h'=>self::$h);
		}
		
		self::$currentSize=0;
		self::unlink();
                self::$size=self::$imageType=self::$w=self::$h=self::$URL=null;
		return $data;
	}
	
	private static function parseSize($url,$isLocal=false,$type=false){
		if($type===false){
			if(!isset(self::$imageType)){
				$ext=pathinfo($url,PATHINFO_EXTENSION);
				if(empty($ext)){
					return false;
				}
				self::$imageType=strtolower(strtok($ext,'?'));
			}
		}
		else{
			self::$imageType=$type;
		}
		if(self::$imageType==='jpg' || self::$imageType==='jpeg'){
			self::$imageType='jpg';
			return self::parseJPG($url,$isLocal);
		}
		elseif(self::$imageType==='png'){
			return self::parsePNG($url,$isLocal);
		}
		elseif(self::$imageType==='webp'){
			return self::parseWEBP($url,$isLocal);
		}
		elseif(self::$imageType==='gif'){
			return self::parseGIF($url,$isLocal);
		}
		elseif(self::$imageType==='bmp'){
			return self::parseBMP($url,$isLocal);
		}
		return false;
	}
	
	private static function loadFile($url,$isLocal,$size=false){
		if($isLocal!=='curl' && ($isLocal===true || self::$method==='file')){
			return self::readFile($url,$size);
		}
		else{
			$userAgent=isset($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']:'Mozilla/5.0 (X11; U; Linux x86_64; en-US; rv:1.9.1.1) Gecko/20090716 Ubuntu/9.04 (jaunty) Shiretoko/3.5.1';//maybe the url is cdn and check the user browser?
			self::$currentSize=0;
			self::$w=self::$h=null;
			if(is_callable('tmpfile')){
				$tmp=tmpfile();
				if($tmp===false){
					return false;
				}
				$f=stream_get_meta_data($tmp);
				if(empty($f['uri'])){
					return false;
				}
				$tmp=$f['uri'];
				unset($f);
			}
			else{
				require_once ABSPATH . 'wp-admin/includes/file.php';
				$tmp=wp_tempnam();
				if(empty($tmp)){
					return false;
				}
			}
			self::$TMP_DIR=$tmp;
			$headers = array(
				'Content-Type:image/'.self::$imageType,
				'Range:bytes=0-'.self::$size
			);
			$ch = curl_init();
			curl_setopt($ch,CURLOPT_RANGE,'0-'.self::$size);
			curl_setopt($ch, CURLOPT_URL, $url );
			curl_setopt($ch, CURLOPT_TIMEOUT, 4);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_AUTOREFERER, true );
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true );
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true );
			curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false );
			if(defined('CURLOPT_SSL_VERIFYSTATUS')){
				curl_setopt($ch, CURLOPT_SSL_VERIFYSTATUS, false );
			}
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0 );
			curl_setopt($ch, CURLOPT_MAXREDIRS, 4 );
			//curl_setopt($ch, CURLOPT_FILE, $fopen );
                        if(defined('CURLOPT_BUFFERSIZE')){
                            curl_setopt($ch, CURLOPT_BUFFERSIZE, self::$size );
                        }

			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3 );
			curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
			//we don't know if the server support range(partial download),that is why we will cut the data on the reading
			curl_setopt($ch, CURLOPT_WRITEFUNCTION, array(__CLASS__,'curl_chunk'));
		
			curl_exec($ch);
			$info=curl_getinfo($ch);
			if($info['http_code']>=400){
				self::unlink();
			}
			unset($info);
			
			curl_close($ch);
			return false;
		}
	}
	
	
	public static function curl_chunk($ch, $chunk){
		if(self::$TMP_DIR===null){//even if there is an error in curl or 404,function will be called,that is why we are checking
			return -1;
		}
		$info=curl_getinfo($ch);
		$len = $info['http_code']>=400?false:file_put_contents(self::$TMP_DIR, $chunk,FILE_APPEND);
              
		if($len!==false){
			unset($info);
			self::$currentSize+=$len;
			if(self::$size<=self::$currentSize || self::$imageType==='jpg'){//checking if there is enough data to get width/height and stop downloading,for jpeg we need to check on every chunk
				if(self::$handle!==null && self::$imageType==='jpg'){
					fclose(self::$handle);
					self::$handle=null;
				}
				$data=self::parseSize(self::$TMP_DIR,true);
				if(!empty($data)){//otherwise we will have to check the file twice
					self::$w=$data['w'];
					self::$h=$data['h'];
					self::unlink();
					return -1;
				}
			}
		}
		else{
			self::unlink();
			return -1;
		}
		return $len;
	}
	
	private static function readFile($url,$size=false){
		try{
			if(strpos($url,'http:')!==false || strpos($url,'https:')!==false){
				
				$headers=get_headers($url);
				if(empty($headers[0]) || strpos($headers[0],'404')!==false || strpos($headers[0],'Not Found')!==false){
					self::$handle =null;
					return false;
				}
				unset($headers);
			}
			if(self::$handle===null){
				self::$handle = fopen($url, 'rb');
			}
			if(!empty(self::$handle) && !feof(self::$handle)) { 
				if($size===false){
					$size=self::$size;
				}
				return fread(self::$handle,$size);
			}
			self::$handle=null;
			return false;
		}
		catch(Exception  $e){
			self::$handle=null;
			//try curl 
			return function_exists('curl_init')?self::parseSize($url,'curl'):false;
		}
	}
	
	
	
	private static function getRealType($chars){
		if(empty($chars)){
			return false;
		}
		$types=array('jpg','png','webp','gif','bmp');
		foreach($types as $t){
			if(self::validate($chars,$t)){
				return $t;
			}
		}
		return false;
	}
	
	private static function validate($chars,$type=false){
		if(empty($chars)){
			return false;
		}
		if($type===false){
			$type=self::$imageType;
		}
		switch ($type){
			
			case 'jpg':
			
				return $chars[0]==="\xFF" && $chars[1]==="\xD8";
				
			case 'png':
			
				return substr($chars, 12, 4) === 'IHDR' && substr($chars, 0, 8) === "\x89\x50\x4e\x47\x0d\x0a\x1a\x0a";
				
			case 'webp':
			
			$riffSignature = substr($chars, 0, 4);
			$webpSignature = substr($chars, 8, 4);
			$vp8Signature = substr($chars, 12, 5);

			return $riffSignature === 'RIFF' && $webpSignature === 'WEBP' && $vp8Signature === 'VP8';
			
			case 'bmp':
			
				return $chars[0]==="\x42" && $chars[1]==="\x4D";
				
			case 'gif':
			
				$GIFH = substr($chars, 0, 6);
				$GIF87A="\x47\x49\x46\x38\x37\x61";
				$GIF89a = "\x47\x49\x46\x38\x39\x61";
				return $GIFH === $GIF87A || $GIFH === $GIF89a;
				
			default:
				return false;
		}
	}
	
	
	private static function reCall($type){
		
		self::$size=self::$imageType=self::$w=self::$h=null;
		self::$currentSize=0;
		self::unlink();
		return self::parseSize(self::$URL,false,$type);
	}
	
	
	
	private static function parseJPG($url,$isLocal){
		self::$size=self::JPG_MAX_SIZE;
		$chars = self::loadFile($url,$isLocal,32);
		if(!self::validate($chars)){
			$type=self::getRealType($chars);
			if($type!==false && $type!=='jpg'){
				return self::reCall($type);
			}
			return false;
		}
		$i = 4;
		// Read block size and skip ahead to begin cycling through blocks in search of SOF marker
		$block_size = unpack('H*', $chars[$i] . $chars[$i+1]);
		$block_size = hexdec($block_size[1]);
		// New block detected, check for SOF marker
		$sof_marker = array("\xC0", "\xC1", "\xC2", "\xC3", "\xC5", "\xC6", "\xC7", "\xC8", "\xC9", "\xCA", "\xCB", "\xCD", "\xCE", "\xCF");
		while(!feof(self::$handle)) {
			$i += $block_size;
			$chars .= fread(self::$handle, $block_size);
			if(isset($chars[$i]) && $chars[$i]==="\xFF") {//SOF flag
				if(in_array($chars[$i+1], $sof_marker,true)) {
					// SOF marker detected. Width and height information is contained in bytes 4-7 after this byte.
					$size_data = $chars[$i+2] . $chars[$i+3] . $chars[$i+4] . $chars[$i+5] . $chars[$i+6] . $chars[$i+7] . $chars[$i+8];
					$unpacked = unpack('H*', $size_data);
					$unpacked = $unpacked[1];
					$height = hexdec($unpacked[6] . $unpacked[7] . $unpacked[8] . $unpacked[9]);
					$width = hexdec($unpacked[10] . $unpacked[11] . $unpacked[12] . $unpacked[13]);
					return array('w'=>$width,'h'=> $height);
				} else {
                    // Skip block marker and read block size
                    $i += 2;
                    if (isset($chars[$i + 1], $chars[$i + 1])) {
                        $block_size = unpack('H*', $chars[$i] . $chars[$i+1]);
                        $block_size = hexdec($block_size[1]);
                    }
				}
			} 
			else {
				return false;
			}
		}
		return false;
	}
	
	private static function parsePNG($url,$isLocal){
		self::$size=self::PNG_MAX_SIZE;
		$chars = self::loadFile($url,$isLocal);
		if(!self::validate($chars)){//validate PNG
			$type=self::getRealType($chars);
			if($type!==false && $type!=='png'){
				return self::reCall($type);
			}
			return false;
		}
		return unpack('Nw/Nh', substr($chars, 16, 8));
	}
	
	private static function parseWEBP($url,$isLocal){
		self::$size=self::WEBP_MAX_SIZE;
		$chars = self::loadFile($url,$isLocal);
		if(!self::validate($chars)){
			$type=self::getRealType($chars);
			if($type!==false && $type!=='webp'){
				return self::reCall($type);
			}
			return false;
		}
		$webpFormat = substr($chars, 15, 1);
		$chars = substr($chars, 16, 14);

		if($webpFormat===' '){//simple
		
			$chars =unpack('vw/vh', substr($chars, 10, 4));
		}
		elseif($webpFormat==='L'){//Lossless 
		
			// Lossless uses 14-bit values so we'll have to use bitwise shifting
			$chars = array(
				'w'=>ord($chars[5]) + ((ord($chars[6]) & 0x3F) << 8) + 1,
				'h'=>(ord($chars[6]) >> 6) + (ord($chars[7]) << 2) + ((ord($chars[8]) & 0xF) << 10) + 1,
			);
		}
		elseif($webpFormat==='X'){//Extended  
		
			// Extended uses 24-bit values cause 14-bit for lossless wasn't weird enough
			$chars= array(
				'w'=>ord($chars[8]) + (ord($chars[9]) << 8) + (ord($chars[10]) << 16) + 1, 
				'h'=>ord($chars[11]) + (ord($chars[12]) << 8) + (ord($chars[13]) << 16) + 1
			);
		}
		else{
			return false;
		}
		return $chars;
	}
	
	private static function parseGIF($url,$isLocal){
		self::$size=self::GIF_MAX_SIZE;
		$chars = self::loadFile($url,$isLocal);
		if(!self::validate($chars)){
			$type=self::getRealType($chars);
			if($type!==false && $type!=='gif'){
				return self::reCall($type);
			}
			return false;
		}
		return unpack('vw/vh', substr($chars, 6, 4));
	}
	
	private static function parseBMP($url,$isLocal){
		self::$size=self::BMP_MAX_SIZE;
		$chars = self::loadFile($url,$isLocal);
		if(!self::validate($chars) ){
			$type=self::getRealType($chars);
			if($type!==false && $type!=='bmp'){
				return self::reCall($type);
			}
			return false;
		}
		$chars = substr($chars, 14, 14);
		$type=unpack('C', $chars);
		return (reset($type) == 40) ? unpack('lw/lh', substr($chars, 4)) : unpack('lw/lh', substr($chars, 4, 8));
	}
	
	public static function shutdown() {
		self::unlink();
	}
        
        private static function unlink(){
            if(self::$handle!==null){
                    fclose(self::$handle);
                    self::$handle=null;
            }
            if(self::$TMP_DIR && is_file(self::$TMP_DIR)){
               unlink(self::$TMP_DIR);
                self::$TMP_DIR=null;
            }
        }
}