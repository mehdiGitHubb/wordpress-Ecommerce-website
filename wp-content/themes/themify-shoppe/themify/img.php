<?php
/**
 * Routines for generation of custom image sizes and deletion of these sizes.
 *
 * @since 1.9.0
 * @package themify
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! function_exists( 'themify_do_img' ) ) {
	/**
	 * Resize images dynamically using wp built in functions
	 *
	 * @param string|int $image Image URL or an attachment ID
	 * @param int $width
	 * @param int $height
	 * @param bool $crop
	 * @return array
	 */
	function themify_do_img( $image, $width, $height, $crop = false ) {
		$attachment_id =$img_url= null;
		if(!is_numeric( $width ) ){
			$width='';
		}
		if(!is_numeric( $height ) ){
			$height='';
		}
		// if an attachment ID has been sent
		if( is_numeric( $image ) ) {
			$post = get_post( $image );
			if( $post ) {
				$attachment_id = $post->ID;
				$img_url = wp_get_attachment_url( $attachment_id );
			}
			unset($post);
		} else {
			if(strpos($image,'data:image/' )!==false ){
				return array(
					'url' =>$image,
					'width' => $width,
					'height' => $height
				);
			}
			// URL has been passed to the function
			$img_url = esc_url( $image );

			// Check if the image is an attachment. If it's external return url, width and height.
			if(strpos($img_url,themify_upload_dir('baseurl'))===false){
				if($width==='' || $height===''){
					$size = themify_get_image_size($img_url);
					if($size!==false){
						if($width===''){
							$width=$size['w'];
						}
						if($height===''){
							$height=$size['h'];
						}
					}
				}
				return array(
					'url' =>$img_url,
					'width' => $width,
					'height' => $height
				);
			}
			// Finally, run a custom database query to get the attachment ID from the modified attachment URL
			$attachment_id = themify_get_attachment_id_from_url( $img_url);
		}
		// Fetch attachment meta data. Up to this point we know the attachment ID is valid.
		$meta = $attachment_id ?wp_get_attachment_metadata( $attachment_id ):null;

		// missing metadata. bail.
		if (!is_array( $meta ) ) {
			$ext=strtolower(strtok(pathinfo($img_url,PATHINFO_EXTENSION ),'?'));
			if($ext==='png' || $ext==='jpg' || $ext==='jpeg' || $ext==='webp' || $ext==='gif' ||$ext==='bmp' ){//popular types
				$upload_dir = themify_upload_dir();
				$attached_file=str_replace($upload_dir['baseurl'],$upload_dir['basedir'],$img_url);
				if(!is_file ($attached_file)){
					$attached_file=$attachment_id?get_attached_file( $attachment_id ):null;
				}
				if($attached_file){
					$size=themify_get_image_size($attached_file,true);
					if($size){
						$meta=array(
						'width'=>$size['w'],
						'height'=>$size['h'],
						'file'=>trim(str_replace($upload_dir['basedir'],'',$attached_file),'/')
						);
						//if the meta doesn't exist it means the image large size also doesn't exist,that is why checking if the image is too large before cropping,otherwise the site will down
						if($meta['width']>2560 || $meta['height']>2560){
							return array(
								'url' => $img_url,
								'width' => $width,
								'height' => $height,
								'is_large'=>true
							);
						}
 
					}
					unset($upload_dir,$ext,$size,$attached_file);
				}
			}
			if ( ! is_array( $meta ) ) {
				return array(
					'url' => $img_url,
					'width' => $width,
					'height' => $height
				);
			}
		}

		// Perform calculations when height or width = 0
		if( empty( $width ) ) {
			$width = 0;
		}
		if ( empty( $height ) ) {
			// If width and height or original image are available as metadata
			if ( !empty( $meta['width'] ) && !empty( $meta['height'] ) ) {
				// Divide width by original image aspect ratio to obtain projected height
				// The floor function is used so it returns an int and metadata can be written
				$height = (int)(floor( $width / ( $meta['width'] / $meta['height'] ) ));
			} else {
				$height = 0;
			}
		}
		// Check if resized image already exists
		if ( is_array( $meta ) && isset( $meta['sizes']["resized-{$width}x{$height}"] ) ) {
			$size = $meta['sizes']["resized-{$width}x{$height}"];
			if( isset( $size['width'],$size['height'] )) {
				$split_url = explode( '/', $img_url );
				
				if( ! isset( $size['mime-type'] ) || $size['mime-type'] !== 'image/gif' ) {
					$split_url[ count( $split_url ) - 1 ] = $size['file'];
				}

				return array(
					'url' => implode( '/', $split_url ),
					'width' => $width,
					'height' => $height,
					'attachment_id' => $attachment_id
				);
			}
		}

		// Requested image size doesn't exists, so let's create one
		if ( true == $crop ) {
			add_filter( 'image_resize_dimensions', 'themify_img_resize_dimensions', 10, 5 );
		}
		// Patch meta because if we're here, there's a valid attachment ID for sure, but maybe the meta data is not ok.
		if ( empty( $meta ) ) {
			$meta['sizes'] = array( 'large' => array() );
		}
		// Generate image returning an array with image url, width and height. If image can't generated, original url, width and height are used.
		$image = themify_make_image_size( $attachment_id, $width, $height, $meta, $img_url );
		
		if ( true == $crop ) {
			remove_filter( 'image_resize_dimensions', 'themify_img_resize_dimensions', 10 );
		}
		$image['attachment_id'] = $attachment_id;
		return $image;
	}
}
if ( ! function_exists( 'themify_make_image_size' ) ) {
	/**
	 * Creates new image size.
	 *
	 * @uses get_attached_file()
	 * @uses image_make_intermediate_size()
	 * @uses wp_update_attachment_metadata()
	 * @uses get_post_meta()
	 * @uses update_post_meta()
	 *
	 * @param int $attachment_id
	 * @param int $width
	 * @param int $height
	 * @param array $meta
	 * @param string $img_url
	 *
	 * @return array
	 */
	function themify_make_image_size( $attachment_id, $width, $height, $meta, $img_url ) {
		if($width!==0 || $height!==0){
			$upload_dir = themify_upload_dir();
			$attached_file=str_replace($upload_dir['baseurl'],$upload_dir['basedir'],$img_url);
			unset($upload_dir);
			if(!Themify_Filesystem::is_file ($attached_file)){
				$attached_file=get_attached_file( $attachment_id );
			}
			$source_size = apply_filters( 'themify_image_script_source_size', themify_get( 'setting-img_php_base_size', 'large', true ) );
			if ( $source_size !== 'full' && isset( $meta['sizes'][ $source_size ]['file'] ) ){
				$attached_file = str_replace( $meta['file'], trailingslashit( dirname( $meta['file'] ) ) . $meta['sizes'][ $source_size ]['file'], $attached_file );
			}
			unset($source_size);
			$resized = image_make_intermediate_size( $attached_file, $width, $height, true );
			if ( $resized && ! is_wp_error( $resized ) ) {

				// Save the new size in meta data
				$key = sprintf( 'resized-%dx%d', $width, $height );
				$meta['sizes'][$key] = $resized;
				$img_url = str_replace( basename( $img_url ), $resized['file'], $img_url );

				wp_update_attachment_metadata( $attachment_id, $meta );

				// Save size in backup sizes so it's deleted when original attachment is deleted.
				$backup_sizes = get_post_meta( $attachment_id, '_wp_attachment_backup_sizes', true );
				if ( ! is_array( $backup_sizes ) ){
					$backup_sizes = array();
				}
				$backup_sizes[$key] = $resized;
				update_post_meta( $attachment_id, '_wp_attachment_backup_sizes', $backup_sizes );
				$img_url=esc_url($img_url);
			}
		}
		// Return original image url, width and height.
		return array(
			'url' => $img_url,
			'width' => $width,
			'height' => $height
		);
	}
}
function themify_get_placeholder($url,$base=false){
	$upload_dir = themify_upload_dir();
	if(defined('THEME_URI') && strpos($url,THEME_URI)!==false){
	    $dir=str_replace(THEME_URI,THEME_DIR,$url);
	}
	else{
		if(strpos($url,$upload_dir['baseurl'])===false){
                    $size = themify_get_image_size( $url );
                    if($size===false){
                            return false;
                    }
                    return array(
                                's'=>"data:image/svg+xml;charset=UTF-8,".rawurlencode("<svg xmlns='http://www.w3.org/2000/svg' width='{$size['w']}' height='{$size['h']}' fill='rgba(255,255,255,.2)'><rect width='100%' height='100%'/></svg>"),
                                'w'=>$size['w'],
                                'h'=>$size['h']
                            );
		}
		$dir=str_replace($upload_dir['baseurl'],$upload_dir['basedir'],$url);
	}
	if(!Themify_Filesystem::is_file ($dir)){
		return false;
	}
	$parts = pathinfo( $dir );
	$destination=rtrim($parts['dirname'],'/').'/'.$parts['filename'].'.svg';
	$result=str_replace($upload_dir['basedir'],$upload_dir['baseurl'],$destination);
	if(is_multisite() && strpos($upload_dir['basedir'],'blogs.dir',5)!==false){
		static $site_url=null;
		if($site_url===null){
			$site_url=rtrim(home_url(),'/').'/';
		}
		$result=str_replace(ABSPATH,$site_url,$destination);
	}
	if(!Themify_Filesystem::is_file($destination)){
		$ext=strtok($parts['extension'],'?');
		$parts=$im=null;
		if ( ($ext==='png' || $ext==='jpg' || $ext==='jpeg' || $ext==='webp' || $ext==='gif' || $ext==='bmp') && function_exists('getimagesize') ) {
		    $size=getimagesize($dir);
		    $w=$size[0];
		    $h=$size[1];
		    if($w>2560 || $h>2560){
			return false;
		    }
		    if(isset($size['mime'])){
			    $ext=explode('/',$size['mime']);
			    $ext=$ext[1];
		    }
		    unset($size);
		    if ( $ext === 'png' ) {
			    if(function_exists('imagecreatefrompng')){
				    $im = imagecreatefrompng($dir);
			    }
		    }
		    elseif($ext==='jpg' || $ext==='jpeg'){
			    if(function_exists('imagecreatefromjpeg')){
				    $im = imagecreatefromjpeg($dir);
			    }
		    }
		    elseif($ext==='gif'){
			    if(function_exists('imagecreatefromgif')){
				    $im = imagecreatefromgif($dir);
			    }
		    }
		    elseif($ext==='webp'){
			    if(function_exists('imagecreatefromwebp')){
				    $im = imagecreatefromwebp($dir);
			    }
		    }
		    elseif($ext==='bmp'){
			if(function_exists('imagecreatefrombmp')){
			    $im = imagecreatefrombmp($dir);
			}
		    }
		}
		if(!$im){
		    return false;
		}
		$max_W=$w>=300?3:($w>100?4:5);
		$box_w=floor($w/$max_W);
		$max_H=$h>=300?3:($h>100?4:5);
		$box_h=floor($h/$max_H);
		$firstColor=null;
		$svg='';
		for($y=0;$y<$max_H;++$y){
			for($x=0;$x<$max_W;++$x){
				$x_corrd=$x*$box_w;
				$y_coord=$y*$box_h;
				$color=imagecolorsforindex($im, imagecolorat($im, ($x_corrd+$box_w)/2, ($y_coord+$box_h)/2));
				if ($color['red']>=256){
					$color['red']=240;
				}
				if ($color['green']>=256){
					$color['green']=240;
				}
				if ($color['blue']>=256){
					$color['blue']=240;
				}
				$color=substr('0'.dechex($color['red']),-2).substr('0'.dechex($color['green']),-2).substr('0'.dechex($color['blue']),-2);
				if($firstColor===null){
					$firstColor=$color;
				}
				$svg.='<rect width="'.$box_w.'" height="'.$box_h.'"';
				if($firstColor!==$color){
					$svg.=' fill="#'.$color.'"';
				}
				if($x_corrd>0){
					$svg.=' x="'.$x_corrd.'"';
				}
				if($y_coord>0){
					$svg.=' y="'.$y_coord.'"';
				}
				$svg.='/>';
			}
		}
		imagedestroy($im);
		unset($im);
		$svg='<svg xmlns="http://www.w3.org/2000/svg" width="'.$w.'" height="'.$h.'"><g fill="#'.$firstColor.'">'.$svg.'</g></svg>';
		if(!file_put_contents($destination,$svg)){
			return false;
		}
	}
	if($base===true){
		$tmp=file_get_contents($destination);
		if(!empty($tmp)){
			$result="data:image/svg+xml;charset=UTF-8,".rawurlencode($tmp);
		}
		unset($tmp);
	}
	if(!isset($w)){
		$s = themify_get_image_size( $dir, true );
		if($s===false){
                    $s=array('w'=>'','h'=>'');
		}
	}
	else{
		$s=array('w'=>$w,'h'=>$h);
	}
	return array(
		's'=>$result,
		'w'=>$s['w'],
		'h'=>$s['h']
	);
}



/**
 * Disable the min commands to choose the minimum dimension, thus enabling image enlarging.
 *
 * @param $default
 * @param $orig_w
 * @param $orig_h
 * @param $dest_w
 * @param $dest_h
 * @return array
 */
function themify_img_resize_dimensions( $default, $orig_w, $orig_h, $dest_w, $dest_h ) {
	// set portion of the original image that we can size to $dest_w x $dest_h
	$aspect_ratio = $orig_w / $orig_h;
	$new_w = $dest_w;
	$new_h = $dest_h;

	if ( !$new_w ) {
		$new_w = (int)( $new_h * $aspect_ratio );
	}

	if ( !$new_h ) {
		$new_h = (int)( $new_w / $aspect_ratio );
	}

	$size_ratio = max( $new_w / $orig_w, $new_h / $orig_h );

	$crop_w = round( $new_w / $size_ratio );
	$crop_h = round( $new_h / $size_ratio );

	$s_x = floor( ( $orig_w - $crop_w ) / 2 );
	$s_y = floor( ( $orig_h - $crop_h ) / 2 );

	// the return array matches the parameters to imagecopyresampled()
	// int dst_x, int dst_y, int src_x, int src_y, int dst_w, int dst_h, int src_w, int src_h
	return array( 0, 0, (int) $s_x, (int) $s_y, (int) $new_w, (int) $new_h, (int) $crop_w, (int) $crop_h );
}

if( ! function_exists( 'themify_get_attachment_id_from_url' ) ) :
	/**
	 * Get attachment ID for image from its url.
	 *
	 * @param string $url
	 * @param deprecated $base_url
	 * @return bool|null|string
	 */
	function themify_get_attachment_id_from_url( $url = '', $base_url = '' ) {
		/* cache IDs, for when an image is displayed multiple times on the same page */
		static $cache = array();

		// If this is the URL of an auto-generated thumbnail, get the URL of the original image
		$url = preg_replace( '/-\d+x\d+(?=\.(jpg|jpeg|png|gif|webp|bmp)$)/i', '', $url );
		if ( ! empty( $url ) ) {
			if ( ! isset( $cache[ $url ] ) ) {
				$cache[ $url ] = themify_get_attachment_id_cache( $url );
			}
			return $cache[ $url ];
		}
	}
endif;

/**
 * Convert image URL to attachment ID, data is cached in a db for faster access
 *
 * @return int|false
 */
function themify_get_attachment_id_cache( $url ) {
	$k=$url.'_id';
	$id = Themify_Storage::get($k);
	if ( $id && get_post_type($id)==='attachment') {
	    return (int) $id;
	} 
	$id = attachment_url_to_postid( $url );
	Themify_Storage::set($k,$id);
	return $id;
}


/**
 * Removes protocol and www from URL and returns it
 *
 * @return string
 */
function themify_remove_protocol_from_url( $url ) {//deprecated will be removed
	return preg_replace( '/https?:\/\/(www\.)?/', '', $url );
}

function themify_createWebp($url){

	$res=$url;
	$info = pathinfo($res);
	if(!isset($info['extension'])){
	    return $url;
	}
	$orig_ex = strtok($info['extension'],'?');
	if($orig_ex!=='png' && $orig_ex!=='jpg' && $orig_ex!=='jpeg' && $orig_ex!=='gif'){
	    return $url;
	}
	static $available=null;
	if($available===NULL){
		$available=array();
		if(apply_filters('themify_disable_webp',false)===false){
			if(class_exists('Imagick')){
				$im = new Imagick();
				if (in_array('WEBP', $im->queryFormats('WEBP'),true)) {
					$available['Imagick']=true;
				}
				$im->clear();
				$im=null;
			}
			if(!isset($available['Imagick']) &&function_exists('imagewebp') && (function_exists('imagecreatefromjpeg') || function_exists('imagecreatefrompng'))){
				$available['GD']=true;
			}
		}
	}	
	if(!empty($available)){
		$upload_dir=  themify_upload_dir();
		$sameDomain=strpos($url,$upload_dir['baseurl'])!==false;
		if($sameDomain===false && strpos($url,'http')!==0){//relative to absolute
			$tmp_url = home_url($url);
			$sameDomain=strpos($tmp_url,$upload_dir['baseurl'])!==false;
			if($sameDomain===true){
				$res=$tmp_url;
			}
		}
		if(is_multisite()){
			if($sameDomain===false){
				if(is_subdomain_install()){
					$blog_name = explode('.',$_SERVER['SERVER_NAME']);
					$blog_name=$blog_name[0];
					if(strpos($url,$blog_name)===false){
						return $url;
					}
				}
				else{
					if(!isset($_SERVER['SERVER_NAME']) || strpos($url,$_SERVER['SERVER_NAME'])===false){
						return $url;
					}
					static $site_url=null;
					if($site_url===null){
						$site_url = dirname(site_url());
					}
					if(strpos($url,$site_url)===false){
						return $url;
					}
					$blog_name =explode('/',trim(str_replace($site_url,'',$url),'/'));
					$blog_name=$blog_name[0];
				}
				static $sites=array();
				if(!isset($sites[$blog_name])){
					$blog = get_id_from_blogname($blog_name);
					if($blog===null){
						$sites[$blog_name]=false;
						return $url;
					}
					$currentBlog=pathinfo(get_site_url(),PATHINFO_FILENAME);
					switch_to_blog($blog );
				
					$blog_upload_dir_info = wp_get_upload_dir();	
					restore_current_blog();
					$sites[$blog_name] = array('basedir'=>$blog_upload_dir_info['basedir'],'baseurl'=>str_replace('/'.$currentBlog.'/','/'.$blog_name.'/',$blog_upload_dir_info['baseurl']));// bug in WP return the current blog name url,not switched
				}
				elseif($sites[$blog_name]===false){
					return $url;
				}
				$upload_dir=$sites[$blog_name];
			}
		}
		elseif($sameDomain===false){
			return $url;
		}
		$res=str_replace($upload_dir['baseurl'],$upload_dir['basedir'],$res);
		if(strpos($res,'http')===0){
			return $url;
		}
		$resUrl=str_replace('.'.$orig_ex, '.webp', $res);
		if(is_file ($resUrl)){
			return str_replace($upload_dir['basedir'],$upload_dir['baseurl'],$resUrl);
		}
	    if(!is_file ($res)){
			return $url;
	    }   
		$webp_quality = (int) themify_builder_get( 'setting-webp-quality', 'performance-webp_quality' );
		if ( empty( $webp_quality ) ) {
			$webp_quality = 5;
		}
	    if(isset($available['Imagick'])){
		    $im = new Imagick($res);
		    $lowerExt=explode('/',$im->getImageMimeType());
		    $lowerExt=isset($lowerExt[1])?$lowerExt[1]:false;
		    if(($lowerExt!=='png' && $lowerExt!=='jpg' && $lowerExt!=='jpeg' && $lowerExt!=='gif') || $im->getImageWidth()>2560 || $im->getImageHeight()>2560){
			    $im->clear();
			    $im=null;
			    return $url;
		    }
		    $im->setImageFormat( 'WEBP' );
		    $im->setOption( 'webp:method', $webp_quality ); 
			$im->setOption('webp:lossless','false');
			$im->setOption('webp:low-memory', 'true');
			if($lowerExt==='png'){
				$im->setOption('webp:alpha-compression', 1);
				$im->setOption('webp:alpha-quality', 85);
			}
			$im->stripImage();
			$im->writeImage($resUrl); 
			$imageBlob = $im->getImageBlob();
			$im->clear();
			$im=null;
			$webp = file_put_contents($resUrl, $imageBlob);
			if($webp){
			    $res=$resUrl;
			}
	    }
	    else{
			if(function_exists('exif_imagetype')){
				$size=image_type_to_mime_type(exif_imagetype($res));
			}
			elseif(function_exists('finfo_file')){
			    $finfo = finfo_open(FILEINFO_MIME_TYPE); 
			    $size=finfo_file($finfo, $res);
			    finfo_close($finfo);
			    unset($finfo);
			}
			elseif(function_exists('mime_content_type')){
			    $size = mime_content_type($res);
			}
			else{
			    $size = getimagesize($res);
			    if(!isset($size['mime']) || !isset($size[0]) || !isset($size[1]) || $size[0]>2560 || $size[1]>2560){
				return false;
			    }
			    $size=$size['mime'];
			}
			if(empty($size)){
			    return $url;
			}
			
			$size=explode('/',$size);
			if(!isset($size[1])){
				return $url;
			}
			$lowerExt=$size[1];
			unset($size);
			if($lowerExt!=='png' && $lowerExt!=='jpg' && $lowerExt!=='jpeg' && $lowerExt!=='gif'){
				return $url;
			}
			if($lowerExt==='png' && version_compare(PHP_VERSION, '7.0.0', '<')){
				$hasTransparent=false;
				if(isset($available['Imagick']) || class_exists('Imagick')){
					$image = new Imagick($res);
					if(method_exists($image,'getImageAlphaChannel')){
						$hasTransparent=$image->getImageAlphaChannel()===1?true:null;
					}
					$image->clear();
					$image=null;
				}
				if($hasTransparent!==true){
					if($hasTransparent===false){
						if(ord ( file_get_contents( $res, false, null, 25, 1 ) ) & 4){
						$hasTransparent=true;
						}
						else{
						$contents = file_get_contents( $res );
						if ( stripos( $contents, 'PLTE' ) !== false && stripos( $contents, 'tRNS' ) !== false ){
							$hasTransparent=true;
						}
						}
					}
					elseif($hasTransparent===null){
						$hasTransparent=false;
					}
				}
				if($hasTransparent===true){
					return $url;
				}
			}
			
			switch($lowerExt){
				case 'jpeg':
				case 'jpg':
					if(!function_exists('imagecreatefromjpeg')){
						return $url;
					}
					$im = imagecreatefromjpeg($res);
					break;
				case 'png':
					if(!function_exists('imagecreatefrompng')){
						return $url;
					}
					if(function_exists('imagepalettetotruecolor')){
						$im = imagecreatefrompng($res);
						imagepalettetotruecolor($im);
						imagealphablending($im, true);
						imagesavealpha($im, true);
					}
					else{
						$pngimg  = imagecreatefrompng($res);
						// get dimens of image
						$w = imagesx($pngimg );
						$h = imagesy($pngimg );
						$im = imagecreatetruecolor ($w, $h);
						imagealphablending($im, false);
						imagesavealpha($im, true);
						// By default, the canvas is black, so make it transparent
						$trans = imagecolorallocatealpha($im, 0, 0, 0, 127);
						imagefilledrectangle($im, 0, 0, $w - 1, $h - 1, $trans);
						// copy png to canvas
						imagecopy($im, $pngimg , 0, 0, 0, 0, $w, $h);
						imagedestroy($pngimg);
						$pngimg=null;
					}
					break;
				case 'gif':
					if(!function_exists('imagecreatefromgif')){
						return $url;
					}
					$im = imagecreatefromgif($res);
					break;
				default:
				return $url;
			}

			if(empty($im)){
				return $url;
			}
			$res=$resUrl;
			$quality = array( 0 => 40, 1 => 50, 2 => 60, 3 => 70, 4 => 80, 5 => 90, 6 => 100 );
			$webp =imagewebp($im, $res, $quality[ $webp_quality ] );
			if($webp){
				if (filesize($res) % 2 === 1) {//The following hack solves an `imagewebp` bug
					file_put_contents($res, "\0", FILE_APPEND|LOCK_EX);
				}
			}
			else{
				unlink($res);
			}
			imagedestroy($im);
                        $im=null;
	    }
	    return $webp?str_replace($upload_dir['basedir'],$upload_dir['baseurl'],$res):$url;
	}
	else{
	    return $url;
	}
}
function themify_get_video_size($url,$isLocal=false){
    $k=$url.'_size';
    $found=Themify_Storage::get($k,null,'db');
    if($found===false){
	$attachment_id=themify_get_attachment_id_cache($url);
	if($attachment_id>0){
	    $meta=wp_get_attachment_metadata( $attachment_id );
	    if(empty($meta)){
		require_once ABSPATH . 'wp-admin/includes/media.php';
		$meta=wp_read_video_metadata(get_attached_file($attachment_id));
	    }
	    if(!empty($meta)){
		$found=array(
		    'w'=>isset($meta['width'])?$meta['width']:'',
		    'h'=>isset($meta['height'])?$meta['height']:'',
		    's'=>isset($meta['filesize'])?$meta['filesize']:'',
		    'f'=>isset($meta['fileformat'])?$meta['fileformat']:'',
		    'l'=>isset($meta['length_formatted'])?$meta['length_formatted']:'',
		    't'=>isset($meta['mime_type'])?$meta['mime_type']:''
		);
		Themify_Storage::set($k,$found,MONTH_IN_SECONDS*6,null,'db');
	    }
	}
    }
    else{
	$found=json_decode($found,true);
    }
    return $found;
}
function themify_get_image_size($url,$isLocal=false){
	static $is = null;
	if ( $is === null ) {
            $is=apply_filters('tf_disable_remote_size', class_exists('Themify_Get_Image_Size'));
	}
	if($is===false || !isset($url[2])){
            return false;
	}
        if(strpos($url,'x',3)!==false){
		preg_match('/\-(\d+x\d+)\./i',$url,$m);
		if(isset($m[1])){
			$m=explode('x',$m[1]);
			return array('w'=>$m[0],'h'=>$m[1]);
		}
		unset($m);
	}
        elseif(strpos($url,'gravatar.com')!==false){
            $parts = parse_url($url,PHP_URL_QUERY);
            if(!empty($parts)){
                parse_str($parts, $query_params);
                if(!empty($query_params['s'])){
                    return array('w'=>$query_params['s'],'h'=>$query_params['s']);
                }
            }
        }
	$k=$url.'_size';
	$found=Themify_Storage::get($k);
	if($found!==''){
		if(strpos($found,':')!==false){
		    $found=explode(':',$found);
		    $found=$found[1];
		}
		$found=explode('-',$found);
		if(isset($found[1])){
			return array('w'=>$found[0],'h'=>$found[1]);
		}
	}
        if($isLocal===false){
            $upload_dir = themify_upload_dir();
            if(strpos($url,$upload_dir['baseurl'])!==false){
                $isLocal=true;
                $url=str_replace($upload_dir['baseurl'],$upload_dir['basedir'],$url);
            }
            unset($upload_dir);
        }
	$size=Themify_Get_Image_Size::getSize($url,$isLocal);
        if($size===false && $isLocal===true && function_exists('getimagesize')){
            $size=getimagesize($url);
            $size=empty($size)?false:array('w'=>$size[0],'h'=>$size[1]);
        }
	if($size!==false){
	    Themify_Storage::set($k,$size['w'].'-'.$size['h']);
            return $size;
	}
	return false;
}
