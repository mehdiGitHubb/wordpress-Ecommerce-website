<?php
/**
 * Customizer Tab
 *
 *
 * @since 6.0
 */
namespace InstagramFeed\Builder\Tabs;
use InstagramFeed\Builder\SBI_Feed_Builder;
if(!defined('ABSPATH'))	exit;


class SBI_Customize_Tab{


	/**
	 * Get Customize Tab Sections
	 *
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @return array
	*/
	static function get_sections(){
		return [

			'customize_feedlayout' => [
				'heading' 	=> __( 'Feed Layout', 'instagram-feed' ),
				'icon' 		=> 'feed_layout',
				'controls'	=> self::get_customize_feedlayout_controls()
			],
			'customize_colorschemes' => [
				'heading' 	=> __( 'Color Scheme', 'instagram-feed' ),
				'icon' 		=> 'color_scheme',
				'controls'	=> self::get_customize_colorscheme_controls()
			],
			'customize_sections' => [
				'heading' 	=> __( 'Sections', 'instagram-feed' ),
				'isHeader' 	=> true,
			],
			'customize_header' => [
				'heading' 	=> __( 'Header', 'instagram-feed' ),
				'icon' 		=> 'header',
				'separator'	=> 'none',
				'controls'	=> self::get_customize_header_controls()
			],
			'customize_posts' => [
				'heading' 			=> __( 'Posts', 'instagram-feed' ),
				'icon' 				=> 'article',
				'controls'			=> self::get_customize_posts_controls(),
				'nested_sections' 	=> [
					'images_videos' => [
						'heading' 			=> __( 'Images and Videos', 'instagram-feed' ),
						'icon' 				=> 'picture',
						'isNested'			=> 'true',
						'separator'			=> 'none',
						'controls'			=> self::get_nested_images_videos_controls(),
					],
				]
			],
			'customize_loadmorebutton' => [
				'heading' 	=> __( 'Load More Button', 'instagram-feed' ),
				'description' 	=>  '<br/>',
				'icon' 		=> 'load_more',
				'separator'	=> 'none',
				'controls'	=> self::get_customize_loadmorebutton_controls()
			],
			'customize_followbutton' => [
				'heading' 	=> __( 'Follow Button', 'instagram-feed' ),
				'description' 	=>  '<br/>',
				'icon' 		=> 'follow',
				'separator'	=> 'none',
				'controls'	=> self::get_customize_followbutton_controls()
			],
			'customize_lightbox' => [
				'heading' 		=> __( 'Lightbox', 'instagram-feed' ),
				'description' 	=> __( 'Upgrade to Pro to add a modal when user clicks on a post.', 'custom-facebook-feed' ),
				'proLabel'		=> true,
				'icon' 			=> 'lightbox',
				'separator'		=> 'none',
				'checkExtensionPopup' => 'lightbox',
				'controls'		=> self::get_customize_lightbox_controls()
			]

		];
	}



	/**
	 * Get Customize Tab Feed Layout Section
	 * @since 6.0
	 * @return array
	*/
	static function get_customize_feedlayout_controls(){
		return [
			[
				'type' 		=> 'toggleset',
				'id' 		=> 'layout',
				'heading' 	=> __( 'Layout', 'instagram-feed' ),
				'separator'	=> 'bottom',
				'options'	=> [
					[
						'value' 		=> 'grid',
						'icon' 			=> 'grid',
						'label' 		=> __( 'Grid', 'instagram-feed' )
					],
					[
						'value' => 'carousel',
						'icon' => 'carousel',
						'checkExtension'	=> 'feedLayout',
						'label' => __( 'Carousel', 'instagram-feed' )
					],
					[
						'value' => 'masonry',
						'icon' => 'masonry',
						'checkExtension'	=> 'feedLayout',
						'label' => __( 'Masonry', 'instagram-feed' )
					],
					[
						'value' 			=> 'highlight',
						'icon' 				=> 'highlight',
						'checkExtension'	=> 'feedLayout',
						'label' 			=> __( 'Highlight', 'instagram-feed' )
					]
				]
			],

			//Carousel Settings
			[
				'type' 				=> 'heading',
				'heading' 			=> __( 'Carousel Settings', 'instagram-feed' ),
				'condition'			=> ['layout' => ['carousel']],
				'conditionHide'		=> true,
			],
			/*
			[
				'type' 				=> 'number',
				'id' 				=> 'carouselrows',
				'layout' 			=> 'half',
				'condition'			=> ['layout' => ['carousel']],
				'conditionHide'		=> true,
				'ajaxAction'		=> 'feedFlyPreview',
				'strongHeading'		=> 'false',
				'stacked'			=> 'true',
				'heading' 			=> __( 'Rows', 'instagram-feed' ),
			],
			*/

			[
				'type' 				=> 'select',
				'id' 				=> 'carouselrows',
				'layout' 			=> 'half',
				'condition'			=> ['layout' => ['carousel']],
				'conditionHide'		=> true,
				'ajaxAction'		=> 'feedFlyPreview',
				'strongHeading'		=> 'false',
				'stacked'			=> 'true',
				'heading' 			=> __( 'Rows', 'instagram-feed' ),
				'options'			=> [
					1 => '1',
					2 => '2'
				]
			],

			[
				'type' 				=> 'select',
				'id' 				=> 'carouselloop',
				'condition'			=> ['layout' => ['carousel']],
				'conditionHide'		=> true,
				'layout' 			=> 'half',
				'strongHeading'		=> 'false',
				'heading' 			=> __( 'Loop Type', 'instagram-feed' ),
				'stacked'			=> 'true',
				'options'			=> [
					'rewind' => __( 'Rewind', 'instagram-feed' ),
					'infinity' => __( 'Infinity', 'instagram-feed' ),
				]
			],
			[
				'type' 				=> 'number',
				'id' 				=> 'carouseltime',
				'condition'			=> ['layout' => ['carousel']],
				'conditionHide'		=> true,
				'stacked'			=> 'true',
				'layout' 			=> 'half',
				'fieldSuffix' 		=> 'ms',
				'heading' 		=> __( 'Interval Time', 'instagram-feed' ),
			],
			[
				'type' 				=> 'checkbox',
				'id' 				=> 'carouselarrows',
				'condition'			=> ['layout' => ['carousel']],
				'conditionHide'		=> true,
				'label' 			=> __( 'Show Navigation Arrows', 'instagram-feed' ),
				'reverse'			=> 'true',
				'stacked'			=> 'true',
				#'disabledInput'		=> true,
				'options'			=> [
					'enabled'	=> 'true',
					'disabled'	=> 'false'
				]
			],
			[
				'type' 				=> 'checkbox',
				'id' 				=> 'carouselpag',
				'condition'			=> ['layout' => ['carousel']],
				'conditionHide'		=> true,
				'label' 			=> __( 'Show Pagination', 'instagram-feed' ),
				'reverse'			=> 'true',
				'stacked'			=> 'true',
				#'disabledInput'		=> true,
				'options'			=> [
					'enabled'	=> 'true',
					'disabled'	=> 'false'
				]
			],
			[
				'type' 				=> 'checkbox',
				'id' 				=> 'carouselautoplay',
				'condition'			=> ['layout' => ['carousel']],
				'conditionHide'		=> true,
				'label' 			=> __( 'Enable Autoplay', 'instagram-feed' ),
				'reverse'			=> 'true',
				'stacked'			=> 'true',
				#'disabledInput'		=> true,
				'options'			=> [
					'enabled'	=> 'true',
					'disabled'	=> 'false'
				]
			],

			//HighLight Settings
			[
				'type' 				=> 'heading',
				'heading' 			=> __( 'HighLight Settings', 'instagram-feed' ),
				'condition'			=> ['layout' => ['highlight']],
				'conditionHide'		=> true,
			],
			[
				'type' 				=> 'select',
				'id' 				=> 'highlighttype',
				'condition'			=> ['layout' => ['highlight']],
				'conditionHide'		=> true,
				'layout' 			=> 'half',
				'strongHeading'		=> 'false',
				'heading' 			=> __( 'Type', 'instagram-feed' ),
				'stacked'			=> 'true',
				'options'			=> [
					'pattern' => __( 'Pattern', 'instagram-feed' ),
					'id' => __( 'Post ID', 'instagram-feed' ),
					'hashtag' => __( 'Hashtag', 'instagram-feed' ),
				]
			],
			[
				'type' 				=> 'number',
				'id' 				=> 'highlightoffset',
				'condition'			=> ['layout' => ['highlight'],'highlighttype' => ['pattern']],
				'conditionHide'		=> true,
				'stacked'			=> 'true',
				'layout' 			=> 'half',
				'heading' 		=> __( 'Offset', 'instagram-feed' ),
			],
			[
				'type' 				=> 'number',
				'id' 				=> 'highlightpattern',
				'condition'			=> ['layout' => ['highlight'],'highlighttype' => ['pattern']],
				'conditionHide'		=> true,
				'stacked'			=> 'true',
				'layout' 			=> 'half',
				'fieldSuffix' 			=> 'posts',
				'heading' 		=> __( 'Highlight every', 'instagram-feed' ),
			],

			[
				'type' 				=> 'textarea',
				'id' 				=> 'highlightids',
				'description' 		=> __( 'Highlight posts with these IDs', 'instagram-feed' ),
				'placeholder' 		=> 'id1, id2',
				'condition'			=> ['layout' => ['highlight'],'highlighttype' => ['id']],
				'conditionHide'		=> true,
				'stacked'			=> 'true'
			],

			[
				'type' 				=> 'textarea',
				'id' 				=> 'highlighthashtag',
				'description' 		=> __( 'Highlight posts with these hashtags', 'instagram-feed' ),
				'placeholder' 		=> '#hashtag1, #hashtag2',
				'condition'			=> ['layout' => ['highlight'],'highlighttype' => ['hashtag']],
				'conditionHide'		=> true,
				'stacked'			=> 'true'
			],


			[
				'type' 				=> 'separator',
				'top' 				=> 20,
				'bottom' 			=> 10,
				'condition'			=> ['layout' => ['highlight']],
				'conditionHide'		=> true,
			],

			[
				'type' 				=> 'number',
				'id' 				=> 'height',
				'fieldSuffix' 		=> 'px',
				'separator'			=> 'bottom',
				'strongHeading'		=> 'true',
				'heading' 			=> __( 'Feed Height', 'instagram-feed' ),
				'style'				=> ['#sb_instagram' => 'height:{{value}}px!important;overflow-y:auto;'],
			],
			[
				'type' 				=> 'number',
				'id' 				=> 'imagepadding',
				'fieldSuffix' 		=> 'px',
				'separator'			=> 'bottom',
				'strongHeading'		=> 'true',
				'heading' 			=> __( 'Padding', 'instagram-feed' ),
				'style'				=> ['#sbi_images' => 'padding:{{value}}px!important;'],
			],
			[
				'type' 				=> 'heading',
				'heading' 			=> __( 'Number of Posts', 'instagram-feed' ),
			],
			[
				'type' 				=> 'number',
				'id' 				=> 'num',
				'icon' 				=> 'desktop',
				'layout' 			=> 'half',
				'ajaxAction'		=> 'feedFlyPreview',

				'strongHeading'		=> 'false',
				'stacked'			=> 'true',
				'heading' 			=> __( 'Desktop', 'instagram-feed' ),
			],
			[
				'type' 				=> 'number',
				'id' 				=> 'nummobile',
				'icon' 				=> 'mobile',
				'layout' 			=> 'half',
				'strongHeading'		=> 'false',
				'stacked'			=> 'true',
				'heading' 			=> __( 'Mobile', 'instagram-feed' ),
			],

			[
				'type' 				=> 'separator',
				'top' 				=> 10,
				'bottom' 			=> 10,
			],
			[
				'type' 				=> 'heading',
				'heading' 			=> __( 'Columns', 'instagram-feed' ),
				'condition'			=> ['layout' => ['grid','masonry']],
				'conditionHide'		=> true,
			],
			[
				'type' 				=> 'select',
				'id' 				=> 'cols',
				'conditionHide'		=> true,
				'icon' 				=> 'desktop',
				'layout' 			=> 'half',
				'strongHeading'		=> 'false',
				'heading' 			=> __( 'Desktop', 'instagram-feed' ),
				'stacked'			=> 'true',
				'options'			=> [
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4',
					'5' => '5',
					'6' => '6',
					'7' => '7',
					'8' => '8',
					'9' => '9',
					'10' => '10',
				]
			],

			[
				'type' 				=> 'select',
				'id' 				=> 'colstablet',
				'conditionHide'		=> true,
				'icon' 				=> 'tablet',
				'layout' 			=> 'half',
				'strongHeading'		=> 'false',
				'heading' 			=> __( 'Tablet', 'instagram-feed' ),
				'stacked'			=> 'true',
				'options'			=> [
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4',
					'5' => '5',
					'6' => '6',
					'7' => '7',
					'8' => '8',
					'9' => '9',
					'10' => '10',
				]
			],
			[
				'type' 				=> 'select',
				'id' 				=> 'colsmobile',
				'conditionHide'		=> true,
				'icon' 				=> 'mobile',
				'layout' 			=> 'half',
				'strongHeading'		=> 'false',
				'heading' 			=> __( 'Mobile', 'instagram-feed' ),
				'stacked'			=> 'true',
				'options'			=> [
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4',
					'5' => '5',
					'6' => '6',
					'7' => '7',
					'8' => '8',
					'9' => '9',
					'10' => '10',
				]
			],


		];
	}

	/**
	 * Get Customize Tab Color Scheme Section
	 * @since 6.0
	 * @return array
	*/
	static function get_customize_colorscheme_controls(){
		$feed_id            = isset( $_GET['feed_id'] ) ? sanitize_key( $_GET['feed_id'] ) : '';
		$color_scheme_array = [
			[
				'type' 		=> 'toggleset',
				'id' 		=> 'colorpalette',
				'separator'	=> 'bottom',
				'options'	=> [
					[
						'value' => 'inherit',
						'label' => __( 'Inherit from Theme', 'instagram-feed' )
					],
					[
						'value' => 'light',
						'icon' => 'sun',
						'label' => __( 'Light', 'instagram-feed' )
					],
					[
						'value' => 'dark',
						'icon' => 'moon',
						'label' => __( 'Dark', 'instagram-feed' )
					],
					[
						'value' => 'custom',
						'icon' => 'cog',
						'label' => __( 'Custom', 'instagram-feed' )
					]
				]
			],

			//Custom Color Palette
			[
				'type' 				=> 'heading',
				'condition'			=> ['colorpalette' => ['custom']],
				'conditionHide'		=> true,
				'heading' 			=> __( 'Custom Palette', 'instagram-feed' ),
			],
			[
				'type' 				=> 'colorpicker',
				'id' 				=> 'custombgcolor1',
				'condition'			=> ['colorpalette' => ['custom']],
				'conditionHide'		=> true,
				'layout' 			=> 'half',
				'strongHeading'		=> 'false',
				'heading' 			=> __( 'Background', 'custom-facebook-feed' ),
				'style'				=> ['.sbi_header_palette_custom_'.$feed_id.',#sb_instagram.sbi_palette_custom_'.$feed_id.',#sbi_lightbox .sbi_lb-outerContainer .sbi_lb-dataContainer,#sbi_lightbox .sbi_lightbox_tooltip,#sbi_lightbox .sbi_share_close' => 'background:{{value}}!important;'],
				'stacked'			=> 'true'
			],
			/*

			[
				'type' 				=> 'colorpicker',
				'id' 				=> 'customtextcolor1',
				'condition'			=> ['colorpalette' => ['custom']],
				'conditionHide'		=> true,
				'layout' 			=> 'half',
				'strongHeading'		=> 'false',
				'heading' 			=> __( 'Text', 'custom-facebook-feed' ),
				'style'				=> ['#sb_instagram.sbi_palette_custom_'.$feed_id.' .sbi_caption,#sbi_lightbox .sbi_lb-outerContainer .sbi_lb-dataContainer .sbi_lb-details .sbi_lb-caption,#sbi_lightbox .sbi_lb-outerContainer .sbi_lb-dataContainer .sbi_lb-number,#sbi_lightbox.sbi_lb-comments-enabled .sbi_lb-commentBox p' => 'color:{{value}}!important;'],
				'stacked'			=> 'true'
			],
			[
				'type' 				=> 'colorpicker',
				'id' 				=> 'customtextcolor2',
				'condition'			=> ['colorpalette' => ['custom']],
				'conditionHide'		=> true,
				'layout' 			=> 'half',
				'strongHeading'		=> 'false',
				'heading' 			=> __( 'Text 2', 'custom-facebook-feed' ),
				'style'				=> ['.sbi_header_palette_custom_'.$feed_id.' .sbi_bio,#sb_instagram.sbi_palette_custom_'.$feed_id.' .sbi_meta' => 'color:{{value}}!important;'],
				'stacked'			=> 'true'
			],
			[
				'type' 				=> 'colorpicker',
				'id' 				=> 'customlinkcolor1',
				'condition'			=> ['colorpalette' => ['custom']],
				'conditionHide'		=> true,
				'layout' 			=> 'half',
				'strongHeading'		=> 'false',
				'heading' 			=> __( 'Link', 'custom-facebook-feed' ),
				'style'				=> ['.sbi_header_palette_custom_'.$feed_id.' a,#sb_instagram.sbi_palette_custom_'.$feed_id.' .sbi_expand a,#sbi_lightbox .sbi_lb-outerContainer .sbi_lb-dataContainer .sbi_lb-details a,#sbi_lightbox.sbi_lb-comments-enabled .sbi_lb-commentBox .sbi_lb-commenter' => 'color:{{value}};'],
				'stacked'			=> 'true'
			],
				*/
			[
				'type' 				=> 'colorpicker',
				'id' 				=> 'custombuttoncolor1',
				'condition'			=> ['colorpalette' => ['custom']],
				'conditionHide'		=> true,
				'layout' 			=> 'half',
				'strongHeading'		=> 'false',
				'heading' 			=> __( 'Button 1', 'custom-facebook-feed' ),
				'style'				=> ['#sb_instagram.sbi_palette_custom_'.$feed_id.' #sbi_load .sbi_load_btn' => 'background:{{value}}!important;'],
				'stacked'			=> 'true'
			],
			[
				'type' 				=> 'colorpicker',
				'id' 				=> 'custombuttoncolor2',
				'condition'			=> ['colorpalette' => ['custom']],
				'conditionHide'		=> true,
				'layout' 			=> 'half',
				'strongHeading'		=> 'false',
				'heading' 			=> __( 'Button 2', 'custom-facebook-feed' ),
				'style'				=> ['#sb_instagram.sbi_palette_custom_'.$feed_id.' #sbi_load .sbi_follow_btn a' => 'background:{{value}}!important;'],
				'stacked'			=> 'true'
			],
		];

		$color_overrides = [];

		$color_overrides_array = [];
		return  array_merge($color_scheme_array,$color_overrides_array);
	}

	/**
	 * Get Customize Tab Header Section
	 * @since 6.0
	 * @return array
	*/
	static function get_customize_header_controls(){
		return [
			[
				'type' 				=> 'switcher',
				'id' 				=> 'showheader',
				'label' 			=> __( 'Enable', 'instagram-feed' ),
				'reverse'			=> 'true',
				'stacked'			=> 'true',
				'options'			=> [
					'enabled'	=> true,
					'disabled'	=> false
				]
			],
			[
				'type' 				=> 'separator',
				'condition'			=> ['showheader' => [true]],
				'top' 				=> 20,
				'bottom' 			=> 0,
			],
			[
				'type' 				=> 'select',
				'id' 				=> 'headersize',
				'condition'			=> ['showheader' => [true]],
				//'conditionHide'		=> true,
				'strongHeading'		=> 'true',
				'separator'			=> 'bottom',
				'heading' 			=> __( 'Header Size', 'instagram-feed' ),
				'options'			=> [
					'small' => __( 'Small', 'instagram-feed' ),
					'medium' => __( 'Medium', 'instagram-feed' ),
					'large' => __( 'Large', 'instagram-feed' ),
				]
			],

			[
				'type' 				=> 'imagechooser',
				'id' 				=> 'customavatar',
				'condition'			=> ['showheader' => [true]],
				//'conditionHide'		=> true,
				'strongHeading'		=> 'true',
				'separator'			=> 'bottom',
				'heading' 			=> __( 'Use Custom Avatar', 'instagram-feed' ),
				'tooltip' 			=> __( 'Upload your own custom image to use for the avatar. This is automatically retrieved from Instagram for Business accounts, but is not available for Personal accounts.', 'instagram-feed' ),
				'placeholder' 		=> __( 'No Image Added', 'instagram-feed' )
			],

			[
				'type' 				=> 'heading',
				'heading' 			=> __( 'Text', 'instagram-feed' ),
				'condition'			=> ['showheader' => [true]],
			],
			/*
			[
				'type' 				=> 'select',
				'id' 				=> 'headertextsize',
				'condition'			=> ['showheader' => [true]],
				//'conditionHide'		=> true,
				'layout' 			=> 'half',
				'strongHeading'		=> 'false',
				'heading' 			=> __( 'Size', 'instagram-feed' ),
				'stacked'			=> 'true',
				'style'				=> ['h3.sbi-preview-header-name' => 'font-size:{{value}}px!important;'],
				'options'			=> SBI_Builder_Customizer_Tab::get_text_size_options()
			],
			*/
			[
				'type' 				=> 'colorpicker',
				'id' 				=> 'headercolor',
				'condition'			=> ['showheader' => [true]],
				//'conditionHide'		=> true,
				'layout' 			=> 'half',
				'strongHeading'		=> 'false',
				'heading' 			=> __( 'Color', 'instagram-feed' ),
				'style'				=> ['.sbi_header_text > *, .sbi_bio_info > *' => 'color:{{value}}!important;'],
				'stacked'			=> 'true'
			],
			[
				'type' 				=> 'colorpicker',
				'id' 				=> 'headerprimarycolor',
				'condition'			=> ['showheader' => [true], 'headerstyle' => 'boxed'],
				'conditionHide'		=> true,
				'layout' 			=> 'half',
				'strongHeading'		=> 'false',
				'heading' 			=> __( 'Primary Color', 'instagram-feed' ),
				'style'				=> [
					'.sbi_header_style_boxed .sbi_bio_info > *' => 'color:{{value}}!important;',
					'.sbi_header_style_boxed' => 'background:{{value}}!important;'
				],
				'stacked'			=> 'true'
			],
			[
				'type' 				=> 'colorpicker',
				'id' 				=> 'headersecondarycolor',
				'condition'			=> ['showheader' => [true], 'headerstyle' => 'boxed'],
				'conditionHide'		=> true,
				'layout' 			=> 'half',
				'strongHeading'		=> 'false',
				'heading' 			=> __( 'Secondary Color', 'instagram-feed' ),
				'style'				=> ['.sbi_header_style_boxed .sbi_header_bar' => 'background:{{value}}!important;'],
				'stacked'			=> 'true'
			],
			[
				'type' 				=> 'separator',
				'condition'			=> ['showheader' => [true]],
				'top' 				=> 10,
				'bottom' 			=> 10,
			],
			[
				'type' 				=> 'switcher',
				'id' 				=> 'showbio',
				'condition'			=> ['showheader' => [true]],
				//'conditionHide'		=> true,
				'label' 			=> __( 'Show Bio Text', 'instagram-feed' ),
				'tooltip' 			=> __( 'Use your own custom bio text in the feed header. This is automatically retrieved from Instagram for Business accounts, but it not available for Personal accounts.', 'instagram-feed' ),
				'stacked'			=> 'true',
				'labelStrong'		=> 'true',
				'options'			=> [
					'enabled'	=> true,
					'disabled'	=> false
				]
			],
			[
				'type' 				=> 'textarea',
				'id' 				=> 'custombio',
				'placeholder'		=> __( 'Add custom bio', 'instagram-feed' ),
				'condition'			=> ['showheader' => [true],'showbio' => [true]],
				//'conditionHide'	=> true,
				'child'				=> 'true',
				'stacked'			=> 'true'
			],
			[
				'type' 				=> 'separator',
				'condition'			=> ['showheader' => [true]],
				'top' 				=> 10,
				'bottom' 			=> 10,
			],
			[
				'type' 				=> 'switcher',
				'id' 				=> 'headeroutside',
				'condition'			=> ['showheader' => [true]],
				//'conditionHide'		=> true,
				'label' 			=> __( 'Show outside scrollable area', 'instagram-feed' ),
				'stacked'			=> 'true',
				'labelStrong'		=> 'true',
				'options'			=> [
					'enabled'	=> true,
					'disabled'	=> false
				]
			],
			[
				'type' 				=> 'separator',
				'condition'			=> ['showheader' => [true]],
				'top' 				=> 10,
				'bottom' 			=> 10,
			],
			[
				'type' 				=> 'heading',
				'heading' 			=> __( 'Advanced', 'instagram-feed' ),
				'proLabel'		=> true,
				'checkExtensionPopupLearnMore' 	=> 'headerLayout',
				'description' 	=> __( 'Tweak the header styles and show your follower count with Instagram Feed Pro.', 'instagram-feed' ),

			],
			[
				'type' 				=> 'separator',
				'condition'			=> ['showheader' => [true]],
				'top' 				=> 30,
				'bottom' 			=> 10,
			],

			[
				'type' 				=> 'switcher',
				'id' 				=> 'stories',
				'condition'			=> ['showheader' => [true]],
				'switcherTop' 			=> true,
				//'conditionHide'		=> true,
				'checkExtensionDimmed'	=> 'headerLayout',
				'checkExtensionPopup' => 'headerLayout',
				'heading' 			=> __( 'Include Stories', 'instagram-feed' ),
				'description' 		=> __( 'You can view active stories by clicking the profile picture in the header. Instagram Business accounts only.<br/><br/>', 'instagram-feed' ),
				'tooltip' 			=> __( 'Show your active stories from Instagram when your header avatar is clicked. Displays a colored ring around your avatar when a story is available.', 'instagram-feed' ),
				'stacked'			=> 'true',
				'labelStrong'		=> 'true',
				'layout' 			=> 'half',
				'reverse'			=> 'true',
				'options'			=> [
					'enabled'	=> true,
					'disabled'	=> false
				]
			],
			[
				'type' 				=> 'number',
				'id' 				=> 'storiestime',
				'condition'			=> ['showheader' => [true],'stories' => [true]],
				'conditionHide'		=> true,
				'strongHeading'		=> false,
				'stacked'			=> 'true',
				'placeholder'			=> '500',
				'child' 			=> true,
				'checkExtensionDimmed'	=> 'headerLayout',
				'checkExtensionPopup' => 'headerLayout',
				'fieldSuffix' 		=> 'milliseconds',
				'heading' 		=> __( 'Change Interval', 'instagram-feed' ),
				'description' 		=> __( 'This is the time a story displays for, before displaying the next one. Videos always change when the video is finished.', 'instagram-feed' ),
				'descriptionPosition' => 'bottom'
			],
			[
				'type' 				=> 'separator',
				'condition'			=> ['showheader' => [true]],
				'top' 				=> 25,
				'bottom' 			=> 10,
			],
			[
				'type' 				=> 'switcher',
				'id' 				=> 'showfollowers',
				'condition'			=> ['showheader' => [true]],
				'checkExtensionDimmed'	=> 'headerLayout',
				'checkExtensionPopup' => 'headerLayout',
				//'conditionHide'		=> true,
				'label' 			=> __( 'Show number of followers', 'instagram-feed' ),
				'stacked'			=> 'true',
				'labelStrong'		=> 'true',
				'options'			=> [
					'enabled'	=> true,
					'disabled'	=> false
				]
			],
			[
				'type' 				=> 'separator',
				'condition'			=> ['showheader' => [true]],
				'top' 				=> 10,
				'bottom' 			=> 10,
			],
			[
				'type' 		=> 'toggleset',
				'id' 		=> 'headerstyle',
				'condition'	=> ['showheader' => [true]],
				'heading' 	=> __( 'Header Style', 'instagram-feed' ),
				'options'	=> [
					[
						'value' => 'standard',
						'label' => __( 'Standard', 'instagram-feed' )
					],
					[
						'value' => 'boxed',
						'checkExtension'	=> 'headerLayout',
						'label' => __( 'Boxed', 'instagram-feed' )
					],
					[
						'value' => 'centered',
						'checkExtension'	=> 'headerLayout',

						'label' => __( 'Centered', 'instagram-feed' )
					]
				]
			],


		];
	}

	/**
	 * Get Customize Tab Posts Section
	 * @since 6.0
	 * @return array
	*/
	static function get_customize_posts_controls(){
		return [
			[
				'type' 				=> 'heading',
				'heading' 			=> __( 'Advanced', 'instagram-feed' ),
				'proLabel'		=> true,
				'checkExtensionPopupLearnMore' 	=> 'postStyling',
				'description' 	=> __( 'These properties are available in the PRO version.', 'instagram-feed' ),
			],

			[
				'type' 				=> 'checkbox',
				'id' 				=> 'showcaption',
				'label' 			=> __( 'Caption', 'instagram-feed' ),
				'labelStrong'		=> 'true',
				'separator'			=> 'bottom',
				'checkExtensionDimmed'	=> 'postStyling',
				'checkExtensionPopup' => 'postStyling',
				'disabledInput'		=> true,
				'options'			=> [
					'enabled'	=> true,
					'disabled'	=> false
				]
			],
			[
				'type' 		=> 'checkbox',
				'id' 				=> 'showlikes',
				'label' 			=> __( 'Like and Comment Summary', 'instagram-feed' ),
				'labelStrong'		=> 'true',
				'checkExtensionDimmed'	=> 'postStyling',
				'checkExtensionPopup' => 'postStyling',
				'separator'			=> 'bottom',
				'disabledInput'		=> true,
				'options'			=> [
					'enabled'	=> true,
					'disabled'	=> false
				]
			],
			[
				'type' 		=> 'checkbox',
				'id' 				=> 'showlikes',
				'label' 			=> __( 'Hover State', 'instagram-feed' ),
				'labelStrong'		=> 'true',
				'checkExtensionDimmed'	=> 'postStyling',
				'checkExtensionPopup' => 'postStyling',
				'separator'			=> 'bottom',
				'disabledInput'		=> true,
				'options'			=> [
					'enabled'	=> true,
					'disabled'	=> false
				]
			],

		];
	}

	/**
	 * Get Customize Tab Posts Section
	 * @since 6.0
	 * @return array
	*/
	static function get_nested_images_videos_controls(){
		return [
			[
				'type' 				=> 'separator',
				'top' 				=> 20,
				'bottom' 			=> 20,
			],
			[
				'type' 				=> 'select',
				'id' 				=> 'imageres',
				'strongHeading'		=> 'true',
				'conditionHide'		=> true,
				'stacked'			=> 'true',
				'heading' 			=> __( 'Resolution', 'instagram-feed' ),
				'description' 		=> __( 'By default we auto-detect image width and fetch a optimal resolution.', 'instagram-feed' ),
				'options'			=> [
					'auto' => __( 'Auto-detect (recommended)', 'instagram-feed' ),
					'thumb' => __( 'Thumbnail (150x150)', 'instagram-feed' ),
					'medium' => __( 'Medium (320x320)', 'instagram-feed' ),
					'full' => __( 'Full size (640x640)', 'instagram-feed' ),
				]
			]
		];
	}

	/**
	 * Get Customize Tab Posts Section
	 * @since 6.0
	 * @return array
	*/
	static function get_nested_caption_controls(){
		return [
			[
				'type' 				=> 'switcher',
				'id' 				=> 'showcaption',
				'label' 			=> __( 'Enable', 'instagram-feed' ),
				'reverse'			=> 'true',
				'stacked'			=> 'true',
				'options'			=> [
					'enabled'	=> true,
					'disabled'	=> false
				]
			],
			[
				'type' 				=> 'separator',
				'top' 				=> 15,
				'bottom' 			=> 15,
				'condition'			=> ['showcaption' => [true]],
				#'conditionHide'		=> true,
			],
			[
				'type' 				=> 'number',
				'id' 				=> 'captionlength',
				'condition'			=> ['showcaption' => [true]],
				#'conditionHide'	=> true,
				'stacked'			=> 'true',
				'fieldSuffix' 		=> 'characters',
				'heading' 			=> __( 'Maximum Text Length', 'instagram-feed' ),
				'description' 		=> __( 'Caption will truncate after reaching the length', 'instagram-feed' ),
			],
			[
				'type' 				=> 'separator',
				'top' 				=> 25,
				'bottom' 			=> 15,
				'condition'			=> ['showcaption' => [true]],
				#'conditionHide'		=> true,
			],
			[
				'type' 				=> 'heading',
				'condition'			=> ['showcaption' => [true]],
				#'conditionHide'	=> true,
				'heading' 			=> __( 'Text', 'instagram-feed' ),
			],
			[
				'type' 				=> 'select',
				'id' 				=> 'captionsize',
				'condition'			=> ['showcaption' => [true]],
				#'conditionHide'		=> true,
				'layout' 			=> 'half',
				'strongHeading'		=> 'false',
				'heading' 			=> __( 'Size', 'instagram-feed' ),
				'stacked'			=> 'true',
				'style'				=> ['.sbi_caption_wrap .sbi_caption' => 'font-size:{{value}}px!important;'],
				'options'			=> SBI_Builder_Customizer_Tab::get_text_size_options()
			],
			[
				'type' 				=> 'colorpicker',
				'id' 				=> 'captioncolor',
				'condition'			=> ['showcaption' => [true]],
				#'conditionHide'		=> true,
				'layout' 			=> 'half',
				'strongHeading'		=> 'false',
				'heading' 			=> __( 'Color', 'instagram-feed' ),
				'style'				=> ['.sbi_caption_wrap .sbi_caption' => 'color:{{value}}!important;'],
				'stacked'			=> 'true'
			],

		];
	}

	/**
	 * Get Customize Tab Posts Section
	 * @since 6.0
	 * @return array
	*/
	static function get_nested_like_comment_summary_controls(){
		return [
			[
				'type' 				=> 'switcher',
				'id' 				=> 'showlikes',
				'label' 			=> __( 'Enable', 'instagram-feed' ),
				'reverse'			=> 'true',
				'stacked'			=> 'true',
				'options'			=> [
					'enabled'	=> true,
					'disabled'	=> false
				]
			],
			[
				'type' 				=> 'separator',
				'top' 				=> 15,
				'bottom' 			=> 15,
				'condition'			=> ['showlikes' => [true]],
				#'conditionHide'		=> true,
			],
			[
				'type' 				=> 'heading',
				'condition'			=> ['showlikes' => [true]],
				#'conditionHide'	=> true,
				'heading' 			=> __( 'Icon', 'instagram-feed' ),
			],
			[
				'type' 				=> 'select',
				'id' 				=> 'likessize',
				'condition'			=> ['showlikes' => [true]],
				#'conditionHide'		=> true,
				'layout' 			=> 'half',
				'strongHeading'		=> 'false',
				'heading' 			=> __( 'Size', 'instagram-feed' ),
				'stacked'			=> 'true',
				'style'				=> ['.sbi_likes, .sbi_comments, .sbi_likes svg, .sbi_comments svg' => 'font-size:{{value}}px!important;'],
				'options'			=> SBI_Builder_Customizer_Tab::get_text_size_options()
			],
			[
				'type' 				=> 'colorpicker',
				'id' 				=> 'likescolor',
				'condition'			=> ['showlikes' => [true]],
				#'conditionHide'		=> true,
				'layout' 			=> 'half',
				'strongHeading'		=> 'false',
				'heading' 			=> __( 'Color', 'instagram-feed' ),
				'style'				=> ['.sbi_likes, .sbi_comments' => 'color:{{value}};'],
				'stacked'			=> 'true'
			],
		];
	}

	/**
	 * Get Customize Tab Posts Section
	 * @since 6.0
	 * @return array
	*/
	static function get_nested_hover_state_controls(){
		return [
			[
				'type' 				=> 'colorpicker',
				'id' 				=> 'hovercolor',
				'icon' 				=> 'background',
				'layout' 			=> 'half',
				'strongHeading'		=> 'false',
				'heading' 			=> __( 'Background', 'instagram-feed' ),
				'style'				=> ['.sbi_link' => 'background:{{value}}!important;'],
				'stacked'			=> 'true'
			],
			[
				'type' 				=> 'colorpicker',
				'id' 				=> 'hovertextcolor',
				'icon' 				=> 'text',
				'layout' 			=> 'half',
				'strongHeading'		=> 'false',
				'heading' 			=> __( 'Text', 'instagram-feed' ),
				'style'				=> ['.sbi_photo_wrap .sbi_username > a, .sbi_photo_wrap .sbi_caption,.sbi_photo_wrap .sbi_instagram_link,.sbi_photo_wrap .sbi_hover_bottom,.sbi_photo_wrap .sbi_location,.sbi_photo_wrap .sbi_meta,.sbi_photo_wrap .sbi_comments' => 'color:{{value}}!important;'],
				'stacked'			=> 'true'
			],
			[
				'type' 				=> 'heading',
				'heading' 			=> __( 'Information to display', 'instagram-feed' ),
			],
			[
				'type' 				=> 'checkboxlist',
				'id' 				=> 'hoverdisplay',
				'options'			=> [
					[
						'value' 			=> 'username',
						'label' 			=> __( 'Username', 'instagram-feed' ),
					],
					[
						'value' 			=> 'date',
						'label' 			=> __( 'Date', 'instagram-feed' ),
					],
					[
						'value' 			=> 'instagram',
						'label' 			=> __( 'Instagram Icon', 'instagram-feed' ),
					],
					[
						'value' 			=> 'caption',
						'label' 			=> __( 'Caption', 'instagram-feed' ),
					],
					[
						'value' 			=> 'likes',
						'label' 			=> __( 'Like/Comment Icons<br/>(Business account only)', 'instagram-feed' ),
					]
				],
				'reverse'			=> 'true',
			],
		];
	}



	/**
	 * Get Customize Tab Load More Button Section
	 * @since 6.0
	 * @return array
	*/
	static function get_customize_loadmorebutton_controls(){
		return [
			[
				'type' 				=> 'switcher',
				'id' 				=> 'showbutton',
				'label' 			=> __( 'Enable', 'instagram-feed' ),
				'reverse'			=> 'true',
				'stacked'			=> 'true',
				'options'			=> [
					'enabled'	=> true,
					'disabled'	=> false
				]
			],
			[
				'type' 				=> 'separator',
				'condition'			=> ['showbutton' => [true]],
				'top' 				=> 20,
				'bottom' 			=> 5,
			],
			[
				'type' 				=> 'text',
				'id' 				=> 'buttontext',
				'condition'			=> ['showbutton' => [true]],
				//'conditionHide'		=> true,
				'strongHeading'		=> 'true',
				'heading' 			=> __( 'Text', 'instagram-feed' ),
			],
			[
				'type' 				=> 'separator',
				'condition'			=> ['showbutton' => [true]],
				'top' 				=> 15,
				'bottom' 			=> 15,
			],
			[
				'type' 				=> 'heading',
				'heading' 			=> __( 'Color', 'instagram-feed' ),
				'condition'			=> ['showbutton' => [true]],
			],
			[
				'type' 				=> 'colorpicker',
				'id' 				=> 'buttoncolor',
				'condition'			=> ['showbutton' => [true]],
				'layout' 			=> 'half',
				'icon' 				=> 'background',
				'strongHeading'		=> 'false',
				'heading' 			=> __( 'Background', 'instagram-feed' ),
				'style'				=> ['.sbi_load_btn' => 'background:{{value}}!important;'],
				'stacked'			=> 'true'
			],
			[
				'type' 				=> 'colorpicker',
				'id' 				=> 'buttonhovercolor',
				'condition'			=> ['showbutton' => [true]],
				'layout' 			=> 'half',
				'icon' 				=> 'cursor',
				'strongHeading'		=> 'false',
				'heading' 			=> __( 'Hover State', 'instagram-feed' ),
				'style'				=> ['.sbi_load_btn:hover' => 'background:{{value}}!important;'],
				'stacked'			=> 'true'
			],
			[
				'type' 				=> 'colorpicker',
				'id' 				=> 'buttontextcolor',
				'condition'			=> ['showbutton' => [true]],
				'layout' 			=> 'half',
				'icon' 				=> 'text',
				'strongHeading'		=> 'false',
				'heading' 			=> __( 'Text', 'instagram-feed' ),
				'style'				=> ['.sbi_load_btn' => 'color:{{value}}!important;'],
				'stacked'			=> 'true'
			],
			[
				'type' 				=> 'separator',
				'condition'			=> ['showbutton' => [true]],
				'top' 				=> 15,
				'bottom' 			=> 5,
			],
			[
				'type' 				=> 'heading',
				'heading' 			=> __( 'Advanced', 'instagram-feed' ),
				'proLabel'		=> true,
				'checkExtensionPopupLearnMore' 	=> 'postStyling',
				'utmLink' => 'https://smashballoon.com/instagram-feed/demo/?utm_campaign=instagram-free&utm_source=customizer&utm_medium=load-more',
				'description' 	=> __( 'These properties are available in the PRO version.', 'instagram-feed' ),

			],
			[
				'type' 				=> 'separator',
				'condition'			=> ['showheader' => [true]],
				'top' 				=> 30,
				'bottom' 			=> 10,
			],
			[
				'type' 				=> 'switcher',
				'id' 				=> 'autoscroll',
				'condition'			=> ['showbutton' => [true]],
				'switcherTop' 			=> true,
				'checkExtensionDimmed'	=> 'postStyling',
				'checkExtensionPopup' => 'postStyling',
				'utmLink' => 'https://smashballoon.com/instagram-feed/demo/?utm_campaign=instagram-free&utm_source=customizer&utm_medium=load-more',
				//'conditionHide'		=> true,
				'heading' 			=> __( 'Infinite Scroll', 'instagram-feed' ),
				'description' 		=> __( 'This will load more posts automatically when the users reach the end of the feed', 'instagram-feed' ),
				'stacked'			=> 'true',
				'labelStrong'		=> 'true',
				'layout' 			=> 'half',
				'reverse'			=> 'true',
				'options'			=> [
					'enabled'	=> true,
					'disabled'	=> false
				]
			],
			[
				'type' 				=> 'number',
				'id' 				=> 'autoscrolldistance',
				'condition'			=> ['showbutton' => [true],'autoscroll' => ['true']],
				'conditionHide'		=> true,
				'strongHeading'		=> false,
				'stacked'			=> 'true',
				'layout'			=> 'half',
				'placeholder'		=> '200',
				'child' 			=> true,
				'fieldSuffix' 		=> 'px',
				'heading' 		=> __( 'Trigger Distance', 'instagram-feed' ),
			],

		];
	}

	/**
	 * Get Customize Tab Follow Button Section
	 * @since 6.0
	 * @return array
	*/
	static function get_customize_followbutton_controls(){
		return [
			[
				'type' 				=> 'switcher',
				'id' 				=> 'showfollow',
				'label' 			=> __( 'Enable', 'instagram-feed' ),
				'reverse'			=> 'true',
				'stacked'			=> 'true',
				'options'			=> [
					'enabled'	=> true,
					'disabled'	=> false
				]
			],
			[
				'type' 				=> 'separator',
				'condition'			=> ['showfollow' => [true]],
				'top' 				=> 20,
				'bottom' 			=> 5,
			],
			[
				'type' 				=> 'text',
				'id' 				=> 'followtext',
				'condition'			=> ['showfollow' => [true]],
				//'conditionHide'		=> true,
				'strongHeading'		=> 'true',
				'heading' 			=> __( 'Text', 'instagram-feed' ),
			],
			[
				'type' 				=> 'separator',
				'condition'			=> ['showfollow' => [true]],
				'top' 				=> 15,
				'bottom' 			=> 15,
			],
			[
				'type' 				=> 'heading',
				'heading' 			=> __( 'Color', 'instagram-feed' ),
				'condition'			=> ['showfollow' => [true]],
			],
			[
				'type' 				=> 'colorpicker',
				'id' 				=> 'followcolor',
				'condition'			=> ['showfollow' => [true]],
				'layout' 			=> 'half',
				'icon' 				=> 'background',
				'strongHeading'		=> 'false',
				'heading' 			=> __( 'Background', 'instagram-feed' ),
				'style'				=> ['.sbi_follow_btn a' => 'background:{{value}}!important;'],
				'stacked'			=> 'true'
			],
			[
				'type' 				=> 'colorpicker',
				'id' 				=> 'followhovercolor',
				'condition'			=> ['showfollow' => [true]],
				'layout' 			=> 'half',
				'icon' 				=> 'cursor',
				'strongHeading'		=> 'false',
				'heading' 			=> __( 'Hover State', 'instagram-feed' ),
				'style'				=> ['.sbi_follow_btn a:hover' => 'box-shadow:inset 0 0 10px 20px {{value}}!important;'],
				'stacked'			=> 'true'
			],
			[
				'type' 				=> 'colorpicker',
				'id' 				=> 'followtextcolor',
				'condition'			=> ['showbutton' => [true]],
				'layout' 			=> 'half',
				'icon' 				=> 'text',
				'strongHeading'		=> 'false',
				'heading' 			=> __( 'Text', 'instagram-feed' ),
				'style'				=> ['.sbi_follow_btn a' => 'color:{{value}}!important;'],
				'stacked'			=> 'true'
			]
		];
	}


	/**
	 * Get Customize Tab LightBox Section
	 * @since 6.0
	 * @return array
	*/
	static function get_customize_lightbox_controls(){
		return [

			[
				'type' 				=> 'separator',
				'checkExtensionDimmed'	=> 'lightbox',
				'checkExtensionPopup' => 'lightbox',
				'top' 				=> 40,
				'bottom' 			=> 5,
			],
			[
				'type' 				=> 'heading',
				'heading' 			=> __( 'Color', 'instagram-feed' ),
				'checkExtensionDimmed'	=> 'lightbox',
				'checkExtensionPopup' => 'lightbox',
			],
			[
				'type' 				=> 'colorpicker',
				'id' 				=> 'fakecolorpicker',
				'icon' 				=> 'background',
				'layout' 			=> 'half',
				'strongHeading'		=> 'false',
				'checkExtensionDimmed'	=> 'lightbox',
				'checkExtensionPopup' => 'lightbox',
				'heading' 			=> __( 'Background', 'instagram-feed' ),
				'stacked'			=> 'true'
			],
			[
				'type' 				=> 'colorpicker',
				'id' 				=> 'fakecolorpicker',
				'icon' 				=> 'text',
				'layout' 			=> 'half',
				'strongHeading'		=> 'false',
				'checkExtensionDimmed'	=> 'lightbox',
				'checkExtensionPopup' => 'lightbox',
				'heading' 			=> __( 'Text', 'instagram-feed' ),
				'stacked'			=> 'true'
			],
			[
				'type' 				=> 'colorpicker',
				'id' 				=> 'fakecolorpicker',
				'icon' 				=> 'link',
				'layout' 			=> 'half',
				'strongHeading'		=> 'false',
				'checkExtensionDimmed'	=> 'lightbox',
				'checkExtensionPopup' => 'lightbox',
				'heading' 			=> __( 'Link Color', 'instagram-feed' ),
				'stacked'			=> 'true'
			],
			[
				'type' 				=> 'separator',
				'checkExtensionDimmed'	=> 'lightbox',
				'checkExtensionPopup' => 'lightbox',
				'top' 				=> 30,
				'bottom' 			=> 10,
			],
			[
				'type' 				=> 'heading',
				'heading' 			=> __( 'Comments', 'instagram-feed' ),
				'tooltip' 			=> __( 'Display comments for your posts inside the lightbox. Comments are only available for User feeds from Business accounts.', 'instagram-feed' ),
				'checkExtensionDimmed'	=> 'lightbox',
				'checkExtensionPopup' => 'lightbox',
			],
			[
				'type' 				=> 'number',
				'id' 				=> 'numcomments',
				'condition'			=> ['disablelightbox' => [false],'lightboxcomments' => [true]],
				'checkExtensionDimmed'	=> 'lightbox',
				'checkExtensionPopup' => 'lightbox',
				'strongHeading'		=> false,
				'stacked'			=> 'true',
				'placeholder'		=> '20',
				'fieldSuffixAction' => 'clearCommentCache',
				'fieldSuffix' 		=> 'Clear Cache',
				'heading' 		=> __( 'No. of Comments', 'instagram-feed' ),
				'description' 		=> __( 'Clearing cache will remove all the saved comments in the database', 'instagram-feed' ),
				'descriptionPosition' => 'bottom'
			],
		];
	}


}
