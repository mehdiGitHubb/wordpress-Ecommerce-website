<?php
/**
 * Customizer Tab
 *
 *
 * @since 4.0
 */
namespace InstagramFeed\Builder\Tabs;
use InstagramFeed\Builder\SBI_Feed_Builder;
if(!defined('ABSPATH'))	exit;


class SBI_Settings_Tab{


	/**
	 * Get Customize Tab Sections
	 *
	 *
	 * @since 4.0
	 * @access public
	 *
	 * @return array
	*/
	static function get_sections(){
		return [
			'settings_feedtype' => [
				'heading' 	=> __( 'Sources', 'instagram-feed' ),
				'icon' 		=> 'source',
				'controls'	=> self::get_settings_sources_controls()
			],
			'settings_filters_moderation' => [
				'heading' 	=> __( 'Filters and Moderation', 'instagram-feed' ),
				'icon' 		=> 'filter',
				'controls'	=> self::get_settings_filters_moderation_controls()
			],
			'settings_sort' => [
				'heading' 	=> __( 'Sort', 'instagram-feed' ),
				'icon' 		=> 'sort',
				'controls'	=> self::get_settings_sort_controls()
			],
			'settings_shoppable_feed' => [
				'heading' 	=> __( 'Shoppable Feed', 'instagram-feed' ),
				'icon' 		=> 'shop',
				'separator'	=> 'none',
				'controls'	=> self::get_settings_shoppable_feed_controls()
			],
			'empty_sections' => [
				'heading' 	=> '',
				'isHeader' 	=> true,
			],
			'settings_advanced' => [
				'heading' 	=> __( 'Advanced', 'instagram-feed' ),
				'icon' 		=> 'cog',
				'controls'	=> self::get_settings_advanced_controls()
			]
		];
	}




	/**
	 * Get Settings Tab Filters & Moderation Section
	 * @since 4.0
	 * @return array
	*/
	static function get_settings_filters_moderation_controls(){
		return [
			[
				'type' 				=> 'heading',
				'strongHeading'		=> 'true',
				'heading' 			=> __( 'Show specific types of posts', 'instagram-feed' )
			],
			[
				'type' 				=> 'checkbox',
				'id' 				=> 'reelsposts',
				'label' 			=> __( 'Reels', 'instagram-feed' ),
				'reverse'			=> 'true',
				'stacked'			=> 'true',
				'checkViewDisabled' 		=> 'moderationMode',
				'ajaxAction'		=> 'feedFlyPreview',
				'options'			=> [
					'enabled'	=> true,
					'disabled'	=> false
				]
			],
			[
				'type' 				=> 'separator',
				'top' 				=> 20,
				'bottom' 			=> 20
			],

			[
				'type' 				=> 'heading',
				'heading' 			=> __( 'Advanced', 'instagram-feed' ),
				'proLabel'		=> true,
				'description' 	=> __( 'Visually moderate your feed or hide specific posts with Instagram Feed Pro.', 'instagram-feed' ),
				'checkExtensionPopup' => 'filtermoderation',
				'checkExtensionPopupLearnMore' 	=> 'filtermoderation'
			],

			[
				'type' 				=> 'customview',
				'viewId'			=> 'moderationmode',
				'checkExtensionDimmed'	=> 'filtermoderation',
				'checkExtensionPopup' => 'filtermoderation',
				'disabledInput'		=> true,
				'switcher'			=> [
					'id' 				=> 'enablemoderationmode',
					'label' 			=> __( 'Enable', 'instagram-feed' ),
					'reverse'			=> 'true',
					'stacked'			=> 'true',
					'labelStrong'		=> true,
					'options'			=> [
						'enabled'	=> true,
						'disabled'	=> false
					]
				],
				'moderationTypes' 	=> [
					'allow' => [
						'label' => __( 'Allow List', 'instagram-feed' ),
						'description' => __( 'Hides post by default so you can select the ones you want to show', 'instagram-feed' ),
					],
					'block' => [
						'label' => __( 'Block List', 'instagram-feed' ),
						'description' => __( 'Show all posts by default so you can select the ones you want to hide', 'instagram-feed' ),
					]
				]
			],
			[
				'type' 				=> 'heading',
				'strongHeading'		=> 'true',
				'heading' 			=> __( 'Filters', 'instagram-feed' ),
				'checkExtensionDimmed'	=> 'filtermoderation',
				'checkExtensionPopup' => 'filtermoderation',
				'disabledInput'		=> true,
				'checkViewDisabled'	=> 'moderationMode'
			],
			[
				'type' 				=> 'textarea',
				'id' 				=> 'includewords',
				'heading' 			=> __( 'Only show posts containing', 'instagram-feed' ),
				'tooltip' 			=> __( 'Show your active stories from Instagram when your header avatar is clicked. Displays a colored ring around your avatar when a story is available.', 'instagram-feed' ),
				'placeholder'		=> __( 'Add words here to only show posts containing these words', 'instagram-feed' ),
				'checkExtensionDimmed'	=> 'filtermoderation',
				'checkExtensionPopup' => 'filtermoderation',
				'disabledInput'		=> true,
				'checkViewDisabled' => 'moderationMode'
			],
			[
				'type' 				=> 'separator',
				'top' 				=> 10,
				'bottom' 			=> 10,
				'checkExtensionDimmed'	=> 'filtermoderation',
				'checkExtensionPopup' => 'filtermoderation',
				'disabledInput'		=> true,
				'checkViewDisabled' 		=> 'moderationMode'
			],

			[
				'type' 				=> 'textarea',
				'id' 				=> 'excludewords',
				'disabledInput'		=> true,
				'heading' 			=> __( 'Do not show posts containing', 'instagram-feed' ),
				'tooltip' 			=> __( 'Remove any posts containing these text strings, separating multiple strings using commas.', 'instagram-feed' ),
				'placeholder'		=> __( 'Add words here to hide any posts containing these words', 'instagram-feed' ),
				'checkExtensionDimmed'	=> 'filtermoderation',
				'checkExtensionPopup' => 'filtermoderation',
				'disabledInput'		=> true,
				'checkViewDisabled' 		=> 'moderationMode'
			],

			[
				'type' 				=> 'heading',
				'strongHeading'		=> 'true',
				'stacked'			=> 'true',
				'heading' 			=> __( 'Show specific types of posts', 'instagram-feed' ),
				'checkExtensionDimmed'	=> 'filtermoderation',
				'checkExtensionPopup' => 'filtermoderation',
				'disabledInput'		=> true,
				'checkViewDisabled' 		=> 'moderationMode'
			],

			[
				'type' 				=> 'checkbox',
				'id' 				=> 'photosposts',
				'label' 			=> __( 'Photos', 'instagram-feed' ),
				'reverse'			=> 'true',
				'stacked'			=> 'true',
				'checkViewDisabled' => 'moderationMode',
				'ajaxAction'		=> 'feedFlyPreview',
				'checkExtensionDimmed'	=> 'filtermoderation',
				'checkExtensionPopup' => 'filtermoderation',
				'disabledInput'		=> true,
				'options'			=> [
					'enabled'	=> true,
					'disabled'	=> false
				]
			],

			[
				'type' 				=> 'checkbox',
				'id' 				=> 'videosposts',
				'label' 			=> __( 'Feed Videos', 'instagram-feed' ),
				'reverse'			=> 'true',
				'stacked'			=> 'true',
				'checkViewDisabled' 		=> 'moderationMode',
				'ajaxAction'		=> 'feedFlyPreview',
				'checkExtensionDimmed'	=> 'filtermoderation',
				'checkExtensionPopup' => 'filtermoderation',
				'disabledInput'		=> true,
				'options'			=> [
					'enabled'	=> true,
					'disabled'	=> false
				]
			],
			[
				'type' 				=> 'checkbox',
				'id' 				=> 'igtvposts',
				'label' 			=> __( 'IGTV Videos', 'instagram-feed' ),
				'reverse'			=> 'true',
				'stacked'			=> 'true',
				'checkViewDisabled' 		=> 'moderationMode',
				'ajaxAction'		=> 'feedFlyPreview',
				'checkExtensionDimmed'	=> 'filtermoderation',
				'checkExtensionPopup' => 'filtermoderation',
				'disabledInput'		=> true,
				'options'			=> [
					'enabled'	=> true,
					'disabled'	=> false
				]
			],

			[
				'type' 				=> 'separator',
				'top' 				=> 10,
				'bottom' 			=> 10,
				'checkExtensionDimmed'	=> 'filtermoderation',
				'checkExtensionPopup' => 'filtermoderation',
				'disabledInput'		=> true,
				'checkViewDisabled' 		=> 'moderationMode'
			],

			[
				'type' 				=> 'number',
				'id' 				=> 'offset',
				'strongHeading'		=> 'true',
				'stacked'			=> 'true',
				'placeholder'		=> '0',
				'fieldSuffix' 		=> 'posts',
				'heading' 		=> __( 'Post Offset', 'instagram-feed' ),
				'description' 		=> __( 'This will skip the specified number of posts from displaying in the feed', 'instagram-feed' ),
				'checkExtensionDimmed'	=> 'filtermoderation',
				'checkExtensionPopup' => 'filtermoderation',
				'disabledInput'		=> true,
				'checkViewDisabled' 		=> 'moderationMode'
			],


		];
	}


	/**
	 * Get Settings Tab Sort Section
	 * @since 4.0
	 * @return array
	*/
	static function get_settings_sort_controls(){
		return [
			[
				'type' 				=> 'toggleset',
				'id' 				=> 'sortby',
				'heading' 			=> __( 'Sort Posts by', 'instagram-feed' ),
				'strongHeading'		=> 'true',
				'ajaxAction'		=> 'feedFlyPreview',
				'options'			=> [
					[
						'value' => 'none',
						'label' => __( 'Newest', 'instagram-feed' )
					],
					[
						'value' => 'likes',
						'checkExtension'	=> 'postStyling',
						'utmLink' => 'https://smashballoon.com/instagram-feed/demo/?utm_campaign=instagram-free&utm_source=customizer&utm_medium=load-more',
						'proLabel' => true,
						'label' => __( 'Likes', 'instagram-feed' )
					],
					[
						'value' => 'random',
						'label' => __( 'Random', 'instagram-feed' )
					]
				]
			],
		];
	}


	/**
	 * Get Settings Tab Shoppable Feed Section
	 * @since 4.0
	 * @return array
	*/
	static function get_settings_shoppable_feed_controls(){
		return [
			[
				'type' 				=> 'customview',
				'condition'			=> ['shoppablefeed' => [false]],
				'conditionHide'		=> true,
				'viewId'			=> 'shoppabledisabled'
			],
			[
				'type' 				=> 'customview',
				'condition'			=> ['shoppablefeed' => [true]],
				'conditionHide'		=> true,
				'viewId'			=> 'shoppableenabled'
			],
			[
				'type' 				=> 'customview',
				'condition'			=> ['shoppablefeed' => [true]],
				'conditionHide'		=> true,
				'viewId'			=> 'shoppableselectedpost'
			]



		];
	}


	/**
	 * Get Settings Tab Advanced Section
	 * @since 4.0
	 * @return array
	*/
	static function get_settings_advanced_controls(){
		return [
			[
				'type' 				=> 'number',
				'id' 				=> 'maxrequests',
				'strongHeading'		=> 'true',
				'heading' 		=> __( 'Max Concurrent API Requests', 'instagram-feed' ),
				'description' 		=> __( 'Change the number of maximum concurrent API requests. Not recommended unless directed by the support team.', 'instagram-feed' ),
			],
			[
				'type' 					=> 'switcher',
				'id' 					=> 'customtemplates',
				'label' 				=> __( 'Custom Templates', 'instagram-feed' ),
				'description' 			=> sprintf( __( 'The default HTML for the feed can be replaced with custom templates added to your theme\'s folder. Enable this setting to use these templates. Custom templates are not used in the feed editor. %sLearn More%s', 'instagram-feed' ), '<a href="https://smashballoon.com/guide-to-creating-custom-templates/?utm_source=instagram-free&utm_campaign=instagram&utm_source=customizer&utm_medium=html-templates" target="_blank">', '</a>' ),
				'descriptionPosition' 	=> 'bottom',
				'reverse'				=> 'true',
				'strongHeading'			=> 'true',
				'labelStrong'			=> 'true',
				'options'				=> [
					'enabled'	=> true,
					'disabled'	=> false
				]
			],
		];
	}

	/**
	 * Get Settings TabSources Section
	 * @since 6.0
	 * @return array
	*/
	static function get_settings_sources_controls(){
		return [
			[
				'type' 				=> 'customview',
				'viewId'			=> 'sources'
			],
		];
	}

}
