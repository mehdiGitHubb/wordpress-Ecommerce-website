<?php

defined( 'ABSPATH' ) || exit;

if( ! class_exists( 'Themify_Icon_Font' ) ) :
/**
 * Definition for icon font classes
 *
 * @since 1.0
 */
class Themify_Icon_Font {
	
	public static $url;
	private static $types;
	protected static $usedIcons = array();
	
	protected function __construct(){
	   self::$types[$this->get_id()]=$this;
	}
	
	public static function init(){
	    $dir = trailingslashit( dirname( __FILE__ ) );
	    
	    include $dir.'includes/class-icon-themify.php';
	    include $dir .'includes/class-icon-fontawesome.php';
	    include $dir .'includes/class-icon-fontello.php';
	    include $dir .'includes/class-icon-lineawesome.php';

	    add_action( 'wp_ajax_tf_get_icon', array( __CLASS__, 'tf_ajax_get_icon' ) );
		
		add_action('wp_ajax_tf_icon_get_by_type',array(__CLASS__,'get_ajax_by_type'));
		
	    add_action('wp_ajax_nopriv_tf_load_icons',array(__CLASS__,'load_icons'));
	    add_action('wp_ajax_tf_load_icons',array(__CLASS__,'load_icons'));
	}
	/**
	 * Return the ID of the icon font
	 *
	 * @return string
	 */
	function get_id() {
		return '';
	}

	/**
	 * Return the name of the icon font
	 *
	 * @return string
	 */
	function get_label() {
		return '';
	}
	
	
	/**
	 * Returns a list of icon fonts registered
	 *
	 * @return array
	 */
	public static function get_types() {
	    return self::$types;
	}

	/**
	 * Gets an icon name and checks if it's a valid icon in the font
	 *
	 * @param $name name of the icon
	 * @return bool
	 */
	function is_valid_icon( $name ) {
		return true;
	}

	/**
	 * Returns the formatted CSS classname for the icon
	 *
	 * @return string
	 */
	function get_classname( $icon ,$lazy=null,$data_only=false) {
		return $icon;
	}

	
	function get_categories(){
	    return array();
	}
	
	
	/**
	 * Load script and style required for the icon picker interface
	 *
	 * Must be called manually wherever you need the icon picker.
	 */
	public static function enqueue() {
	    $titles=array();
	    $types=self::get_types();
	    foreach($types as $type){
		$titles[$type->get_id()]=$type->get_label();
	    }
	    wp_enqueue_script( 'tf-icon-picker', themify_enque(THEMIFY_URI . '/themify-icon-picker/assets/script.js'), array( 'themify-main-script' ), THEMIFY_VERSION, true );
	    wp_localize_script( 'tf-icon-picker', 'tfIconPicker', array(
		'group'=>$titles,
		'title'=> __( 'Choose icon', 'themify' ),
		'search'=>__( 'Search', 'themify' )
	    ) );

	    do_action( 'themify_icon_picker_enqueue' );
	}
	
	public function get_icons_by_category($cat=''){
	    return array();
	}
	

	public static function get_used_icons() {
	    return self::$usedIcons;
	}
	

	/**
	 * Hooked to "tf_get_icon" Ajax call, returns the icon CSS classname for $_POST['tf_icon']
	 *
	 * @since 1.0
	 */
	public static function tf_ajax_get_icon() {
		if ( isset( $_GET['tf_icon'] ) ) {
			echo themify_get_icon( $_GET['tf_icon'] );
		}
		die;
	}
	
	
	
	public static function load_icons(){
		if(!empty($_POST['icons'])){
			$icons=json_decode(str_replace('\\','',$_POST['icons']));
			$res=array();
			foreach($icons as $ic){
				$r=themify_get_icon(trim($ic),false,false,true);
				if($r){
					$res[$ic]=$r;
				}
			}
			wp_send_json($res);
		}
		die;
	}
	
	public static function get_ajax_by_type(){
		if(!empty($_POST['type'])){
			$type = $_POST['type'];
			$cat = !empty($_POST['cat'])?$_POST['cat']:'';
			$types = self::get_types();
			if(isset($types[$type])){		
			    header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
			    echo json_encode(array('icons'=>$types[$type]->get_icons_by_category($cat),'cats'=>$types[$type]->get_categories()));
			}
		}
		die;
	}

    protected static function svg_attributes($attrs){
        if(isset($attrs['aria-label'])){
            $attrs['role']='img';
        }
	else{
            $attrs['aria-hidden']='true';
        }
        return themify_get_element_attributes($attrs);
    }
    
    protected static function get_svg($id,array $attrs=array()){
	$cl='tf_fa tf-'.$id;
	if(isset($attrs['class'])){
	    $cl.=' '.$attrs['class'];
	}
	$attrs['class']=$cl;
	return '<svg '.self::svg_attributes($attrs).'><use href="#tf-' . $id . '"></use></svg>';
    }
}
endif;


if( ! function_exists( 'themify_get_icon' ) ){
    /**
     * Retrieve an icon name and returns the proper CSS classname to display that icon
     *
     * @return string
     */
    function themify_get_icon( $name,$type=false,$lazy=false,$data_only=false,$attrs=array()) {
        $types = Themify_Icon_Font::get_types();
        if($type!==false && isset($types[$type])){
            return $types[$type]->get_classname( $name,$lazy,$data_only,$attrs );
        }
        foreach( $types as $font ) {
            if( $font->is_valid_icon( $name ) ) {
                return $font->get_classname( $name,$lazy,$data_only,$attrs );
            }
        }

        return false;
    }
}

add_action( 'init', array( 'Themify_Icon_Font', 'init' ) );
