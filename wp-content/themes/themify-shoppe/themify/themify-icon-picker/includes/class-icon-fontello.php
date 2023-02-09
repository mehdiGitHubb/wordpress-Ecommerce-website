<?php

defined( 'ABSPATH' ) || exit;

/**
 * Fontello icon font
 * @link http://fontello.com/
 */
class Themify_Icon_Fontello extends Themify_Icon_Font {
    
    private static $isvalid=false;
	private static $setting_key='setting-fontello';
    
    public function __construct() {
		parent::__construct();
		if(self::run()){
			self::$isvalid=true;
		}
    }

	function get_id() {
		return 'fontello';
	}

	function get_label() {
		return __( 'Fontello', 'themify' );
	}

	/**
	 * Check if the icon name belongs to the Fontello icon font
	 *
	 * @return bool
	 */
	function is_valid_icon( $name ) {
	    return self::$isvalid===true && (substr( $name, 0, 9) === 'fontello-' || substr( $name, 0, 5 ) === 'icon-' || substr( $name, 0, 12 ) === 'tf_fontello-' );
	}

	function get_classname( $icon, $lazy = null, $data_only = false,$attrs=array() ) {
		if(self::$isvalid===false){
			return '';
		}
                $id= $this->get_id().'-';
		$icon = str_replace(array('icon-',$id,'tf_fontello-'),'', $icon);
		$k = $id . $icon;
		if (!isset(self::$usedIcons[$k])) {
			$icon = self::get_icons($icon);
			if ($icon!=='') {
                            self::$usedIcons[$k] = $icon;
				self::$usedIcons[$k]['is_fontello']=true;
			}
		}
		if ($data_only === true) {
			return self::$usedIcons[$k];
		}
		$cl='';
		if(defined('TF_FONTELLO_PREFIX') && TF_FONTELLO_PREFIX!==''){
			$cl=TF_FONTELLO_PREFIX.''.$k;
		}
		if(defined('TF_FONTELLO_SUFFIX') && TF_FONTELLO_SUFFIX!==''){
			if($cl!==''){
				$cl.=TF_FONTELLO_SUFFIX;
			}
			else{
				$cl=TF_FONTELLO_SUFFIX.$k;
			}
		}
		if($cl!==''){
		    $attrs['class']=$cl;
		}
		return self::get_svg($k,$attrs);
	}

	function get_categories() {
			$res=array(
				'custom' =>__( 'Icons', 'themify' )
		    );
			$config = self::get_config_file();
			if(!empty($config) && !empty($config['glyphs'])){
				$glyps=$config['glyphs'];
				unset($config);
				$res=array();
				foreach($glyps as $ic){
					if(isset($ic['src'])){
						$res[$ic['src']]=self::get_cat_label($ic['src']);
					}
				}
			}
		    return $res;
	}
	
	private static function get_cat_label($cat){
		$popular_cats=array(
			'fontelico'=>__('Fontelico','themify'),
			'fontawesome'=>__('Font Awesome','themify'),
			'iconic'=>__('Iconic','themify'),
			'modernpics'=>__('Modern Pictograms','themify'),
			'mfglabs'=>__('MFG Labs','themify'),
			'meteocons'=>__('Meteocons','themify'),
			'entypo'=>__('Entypo','themify'),
			'typicons'=>__('Typicons','themify'),
			'maki'=>__('Maki','themify'),
			'zocial'=>__('Zocial','themify'),
			'brandico'=>__('Brandico','themify'),
			'elusive'=>__('Elusive','themify'),
			'linecons'=>__('Linecons','themify'),
			'websymbols'=>__('Web Symbols','themify')
		);
		return isset($popular_cats[$cat])?$popular_cats[$cat]:$cat;
	}
	
	private static function get_icons($icon=''){
		static $icons=null;
		if($icons===null){
			$f=self::get_php_dir();
			if(is_file($f)){
				$icons=include_once $f;
			}
			else{
				$icons=array();
			}
			
		}
                if($icon!==''){
                    return isset($icons[$icon])?$icons[$icon]:'';
                }
		return $icons;
	}

	/**
	 * Get a list of available icons from provided by Fontello
	 *
	 * @return array
	 */
	function get_icons_by_category( $cat = '' ) {
		$res=array();
		$config = self::get_config_file();
		if(!empty($config) && !empty($config['glyphs'])){
			$glyps=$config['glyphs'];			
			unset($config);
			$icons = self::get_icons();
			foreach($glyps as $ic){
				if(isset($ic['css'],$ic['src'],$icons[$ic['css']]) && ($cat==='' || $cat===$ic['src'])){
					if(!isset($res[$ic['src']])){
						$res[$ic['src']]=array();
					}
					$res[$ic['src']][$ic['css']]=$this->get_classname( $ic['css'] ,false,true);
				}
			}
		}
		else{
		    $res['EMPTY']=sprintf( __( 'To add icons here: go to <a class="external-link" href="https://fontello.com" target="_blank">fontello.com</a> and create a package. Then go to <a class="external-link" target="_blank" href="%s">Themify > Settings > Custom Icon Font</a> to upload the icon font package.', 'themify' ), admin_url( 'admin.php?page=themify#setting-custom-icon-font' ) );
		}
		return $res;
	}

	/**
	 * Gets the path to the Fontello assets
	 * Extracts the uploaded package file automatically if necessary
	 *
	 * @return array|bool
	 */
	private static function run() {
		$fontello = self::get_php_dir(true);
		if(is_file($fontello)){
			return true;
		}
		$dest = self::get_upload_dir();
		if($dest===false){
			return false;
		}
		$config = $dest . 'config.json';
		$result = true;		
		if (! is_file( $config ) ) {
			$path = themify_get( self::$setting_key,false,true );
			$upload_dir = themify_upload_dir();
			// get the system path from URL
			$path = str_replace( $upload_dir['baseurl'], $upload_dir['basedir'], $path );
			$result=false;
			if(is_file( $path )){
			    require_once( ABSPATH . 'wp-admin/includes/file.php' );
			    WP_Filesystem();
			    $result = unzip_file( $path, dirname($dest) ); // attempt to extract the file
			}
		}
		if( $result===true) {
			$config = self::get_config_file();
			$name = !empty( $config['name'] ) ? $config['name'] : 'fontello';
			$svg = $dest . 'font/' . $name . '.svg';
			if(is_file($svg)){
			    $data = self::parse_svg($svg);
			    if(!empty($data) && self::write_file($data)){
				    return true;
			    }
			}
		}
		return false;
	}
	
	private static function get_upload_dir(){
		$path = themify_get( self::$setting_key,false,true );
		if(!$path){
		    return false;
		}
		$fileinfo = pathinfo( $path );
		if ( empty( $fileinfo['filename'] ) || empty( $fileinfo['extension'] ) || $fileinfo['extension']!=='zip'){
			return false;
		}
		return themify_upload_dir('basedir') . '/fontello/'.$fileinfo['filename'].'/';
	}
	
	
	
	
	private static function get_config_file(){
		$dir=self::get_upload_dir();
		if($dir!==false){
			$config= $dir.'config.json';
		if(is_file($config)){
			return json_decode(file_get_contents($config), true );
		}
		}
		return false;
	}
	
	private static function get_php_dir($create=false){
		$path = themify_get( self::$setting_key,false,true );
		if(!$path){
		    return false;
		}
		$dir=self::get_upload_dir();
		if($create===true && ($dir===false || (!is_dir($dir) && !wp_mkdir_p($dir)))){
			return false;
		}
		$fileinfo = pathinfo( $path );
		if(empty($fileinfo) || $fileinfo['extension']!=='zip'){
			return false;
		}
		$base=$fileinfo['filename'];
		if(strpos($base,'fontello')!==0){
			$base='fontello-'.$base;
		}
		return $dir.$base.'.php';
	}
	
	private static function write_file($data){
		$dir=self::get_php_dir(true);
		if($dir===false){
			return false;
		}
		$str='<?php';
		$config= self::get_config_file();
		if(!empty($config)){
			if(!empty( $config['css_prefix_text'] ) ){
				$str.=" \n define('TF_FONTELLO_PREFIX','{$config['css_prefix_text']}');\n";
			}
			if(!empty( $config['css_use_suffix'] ) ){
				$str.="define('TF_FONTELLO_SUFFIX','{$config['css_use_suffix']}');\n";
			}
			unset($config);
		}
		$str.=' return '.var_export ($data,true).';';
		return file_put_contents($dir,$str);
	}
	
	private static function parse_svg($f){
		try{
			$xml = simplexml_load_file($f,null,LIBXML_NOERROR|LIBXML_NOWARNING|LIBXML_COMPACT);
		
			if($xml===false){
				return false;
			}
			$fonts=!empty($xml->defs)?$xml->defs->font:$xml->font;
			if(empty($fonts) || !isset($fonts->{'font-face'})){
				return false;
			}
			unset($xml);
			$font=$fonts->{'font-face'};
			
			$vh=isset($font['ascent'])?(int)$font['ascent'][0]:0;
			if(isset($font['descent'])){
				$vh+=(int)$font['descent'][0];
			}
			$vw=isset($fonts->{'missing-glyph'}['horiz-adv-x'])?(string)(round((float)($fonts->{'missing-glyph'}['horiz-adv-x'][0]),2)):1000;//var_export doesn't work correct with float,should be converted to string
			$glyphs=$fonts->glyph;
			unset($fonts);
			if(!empty($glyphs)){
				$res=array();
				foreach($glyphs as $icon){
					$attr=$icon->attributes();
					if(isset($attr['glyph-name'])){
						$k=(string)$attr['glyph-name'];
						$res[$k]=array(
							'p'=>(string)$icon['d'],
							'vw'=>!empty($icon['horiz-adv-x'])?(string)(round((float)($icon['horiz-adv-x'][0]),2)):$vw //var_export doesn't work correct with float,should be converted to string
						);
						if($vh>0){
							$res[$k]['vh']=$vh;
						}
					}
				}
				return $res;
			}
			return false;
		}
		catch(Exception $e){
			return false;
		}
	}
	
	

}
new Themify_Icon_Fontello();