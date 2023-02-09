<?php
/**
 * Configuration for Themify Customizer
 * Created by themify
 * @since 1.0.0
 */

function themify_theme_customizer_definition( $args ) {
	global $themify_customizer;
	$args = array(

		// Accordion Start ---------------------------
		'start_body_acc' => $themify_customizer->accordion_start( __( 'Body', 'themify' ) ),

		// Styling key name. Includes any string depicting the styling, for example 'body' and a suffix
		// specifying the type of control, for example '_background'
		'body_background' => array(
			'setting' => array( // Optional. Default setting/value to save.
				'transport' => 'postMessage', // Live update (postMessage) or reload (refresh) the page.
			),
			'control' => array(
				'type'    => 'Themify_Background_Control', // Type of the control to render.
				'label'   => __( 'Body Background', 'themify' ), // Visible name of the control.
				'show_label' => true, // Whether to show the control name or not. Defaults to true.
				'section' => 'themify_options', // Optional section ID where the control will be added.
			),
			'selector' => 'body', // CSS Selector to apply styling.
			'prop' => 'background', // Styling to apply, can be a CSS property or a custom set of properties.
		),

		'body_font' => array(
			'control' => array(
				'type'    => 'Themify_Font_Control',
				'label'   => __( 'Body Font', 'themify' ),
			),
			'selector' => 'body',
			'prop' => 'font',
		),

		'body_font_color' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
			),
			'selector' => 'body',
			'prop' => 'color',
		),

		'body_link_font' => array(
			'control' => array(
				'type'    => 'Themify_Text_Decoration_Control',
				'label'   => __( 'Body Link', 'themify' ),
			),
			'selector' => 'a',
			'prop' => 'font',
		),

		'body_link_color' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
			),
			'selector' => 'a',
			'prop' => 'color',
		),

		'body_link_hover_font' => array(
			'control' => array(
				'type'    => 'Themify_Text_Decoration_Control',
				'label'   => __( 'Body Link Hover', 'themify' ),
			),
			'selector' => 'a:hover',
			'prop' => 'font',
		),

		'body_link_hover_color' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
			),
			'selector' => 'a:hover',
			'prop' => 'color',
		),

		'body_padding' => array(
			'control' => array(
				'type'    => 'Themify_Padding_Control',
				'label'	  => __( 'Body Padding/Margin/Border', 'themify' ),
			),
			'selector' => 'body',
			'prop' => 'padding',
		),

		'body_border' => array(
			'control' => array(
				'type'    => 'Themify_Border_Control',
			),
			'selector' => 'body',
			'prop' => 'border',
		),

		'body_margin' => array(
			'control' => array(
				'type'    => 'Themify_Margin_Control',
			),
			'selector' => 'body',
			'prop' => 'margin',
		),
		
		'paragraph_margin' => array(
			'control' => array(
				'type'    => 'Themify_Margin_Control',
				'label'	  => __( 'Paragraph Margin', 'themify' ),
			),
			'selector' => 'p',
			'prop' => 'margin',
		),

		'end_body_acc' => $themify_customizer->accordion_end(),
		// Accordion End   ---------------------------

		// Accordion Start ---------------------------
		'start_accent_acc' => $themify_customizer->accordion_start( __( 'Accent Colors', 'themify' ) ),
		
		// Accent Styles
		'accent_font_color' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
				'label'   => __( 'Accent Colors', 'themify' ),
				'color_label'   => __( 'Theme Accent Color', 'themify' ),
			),
            'selector' => ':root',
            'prop' => '--theme_accent'
		),

		'theme_accent_hover_color' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
				'color_label' => __( 'Theme Accent Hover', 'themify' ),
			),
            'selector' => ':root',
            'prop' => '--theme_accent_hover'
		),

		'end_accent_acc' => $themify_customizer->accordion_end(),
		// Accordion End   ---------------------------

		// Accordion Start ---------------------------
		'start_layout_acc' => $themify_customizer->accordion_start( __( 'Layout Containers', 'themify' ) ),

		'pagewrap_background' => array(
			'control' => array(
				'type'    => 'Themify_Background_Control',
				'label' => __( 'Page Wrap', 'themify' ),
			),
			'selector' => '#pagewrap',
			'prop' => 'background',
		),

		'pagewrap_width' => array(
			'control' => array(
				'type'  => 'Themify_Width_Control',
			),
			'selector' => '#pagewrap',
			'prop' => 'width',
		),

		'pagewrap_border' => array(
			'control' => array(
				'type'    => 'Themify_Border_Control',
			),
			'selector' => '#pagewrap',
			'prop' => 'border',
		),

		'pagewrap_margin' => array(
			'control' => array(
				'type'    => 'Themify_Margin_Control',
			),
			'selector' => '#pagewrap',
			'prop' => 'margin',
		),

		'pagewrap_padding' => array(
			'control' => array(
				'type'    => 'Themify_Padding_Control',
			),
			'selector' => '#pagewrap',
			'prop' => 'padding',
		),

		'pagewidth_width' => array(
			'control' => array(
				'type'  => 'Themify_Width_Control',
				'label' => __( 'Page Width', 'themify' ),
			),
			'selector' => '.pagewidth,.module_row>.row_inner',
			'prop' => 'width',
		),

		'middle_container_background' => array(
			'control' => array(
				'type'    => 'Themify_Background_Control',
				'label'   => __( 'Middle Container', 'themify' ),
			),
			'selector' => '#body',
			'prop' => 'background',
		),

		'middle_container_border' => array(
			'control' => array(
				'type'    => 'Themify_Border_Control',
				'label'   => __( 'Middle Container Border', 'themify' ),
				'show_label' => false,
			),
			'selector' => '#body',
			'prop' => 'border',
		),

		'middle_container_margin' => array(
			'control' => array(
				'type'    => 'Themify_Margin_Control',
				'label'   => __( 'Middle Container Margin', 'themify' ),
				'show_label' => false,
			),
			'selector' => '#body',
			'prop' => 'margin',
		),

		'middle_container_padding' => array(
			'control' => array(
				'type'    => 'Themify_Padding_Control',
				'label'   => __( 'Middle Container Padding', 'themify' ),
				'show_label' => false,
			),
			'selector' => '#body',
			'prop' => 'padding',
		),

		'content_background' => array(
			'control' => array(
				'type'    => 'Themify_Background_Control',
				'label' => __( 'Content Container', 'themify' ),
			),
			'selector' => '#content',
			'prop' => 'background',
		),

		'content_width' => array(
			'control' => array(
				'type'  => 'Themify_Width_Control',
				'label'   => __( 'Content Width', 'themify' ),
				'show_label' => false,
			),
			'selector' => '#content',
			'prop' => 'width',
		),

		'content_border' => array(
			'control' => array(
				'type'    => 'Themify_Border_Control',
				'label'   => __( 'Content Border', 'themify' ),
				'show_label' => false,
			),
			'selector' => '#content',
			'prop' => 'border',
		),

		'content_margin' => array(
			'control' => array(
				'type'    => 'Themify_Margin_Control',
				'label'   => __( 'Content Margin', 'themify' ),
				'show_label' => false,
			),
			'selector' => '#content',
			'prop' => 'margin',
		),

		'content_padding' => array(
			'control' => array(
				'type'    => 'Themify_Padding_Control',
				'label'   => __( 'Content Padding', 'themify' ),
				'show_label' => false,
			),
			'selector' => '#content',
			'prop' => 'padding',
		),


		'sidebar_background' => array(
			'control' => array(
				'type'  => 'Themify_Background_Control',
				'label' => __( 'Sidebar Container', 'themify' ),
			),
			'selector' => '#sidebar',
			'prop' => 'background',
		),

		'sidebar_width' => array(
			'control' => array(
				'type'  => 'Themify_Width_Control',
				'label' => __( 'Sidebar Width', 'themify' ),
				'show_label' => false,
			),
			'selector' => '#sidebar',
			'prop' => 'width',
		),
		'sidebar_border' => array(
			'control' => array(
				'type'  => 'Themify_Border_Control',
				'label' => __( 'Sidebar Border', 'themify' ),
				'show_label' => false,
			),
			'selector' => '#sidebar',
			'prop' => 'border',
		),

		'sidebar_margin' => array(
			'control' => array(
				'type'  => 'Themify_Margin_Control',
				'label' => __( 'Sidebar Margin', 'themify' ),
				'show_label' => false,
			),
			'selector' => '#sidebar',
			'prop' => 'margin',
		),

		'sidebar_padding' => array(
			'control' => array(
				'type'  => 'Themify_Padding_Control',
				'label' => __( 'Sidebar Padding', 'themify' ),
				'show_label' => false,
			),
			'selector' => '#sidebar',
			'prop' => 'padding',
		),

		'end_layout_acc' => $themify_customizer->accordion_end(),
		// Accordion End   ---------------------------

		// Accordion Start ---------------------------
		'start_headings_acc' => $themify_customizer->accordion_start( __( 'Headings', 'themify' ) ),

		'heading1_font' => array(
			'control' => array(
				'type'    => 'Themify_Font_Control',
				'label'   => __( 'Heading 1 Font', 'themify' ),
			),
			'selector' => 'h1, .col4-1 h1, .col4-2 h1, .col3-1 h1, .col2-1 h1, .page-title, .sidebar-none .page-title',
			'prop' => 'font',
		),
		'heading1_color' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
				'label'   => __( 'Heading 1 Color', 'themify' ),
				'show_label' => false,
			),
			'selector' => 'h1, .col4-1 h1, .col4-2 h1, .col3-1 h1, .col2-1 h1, .page-title, .sidebar-none .page-title',
			'prop' => 'color',
		),

		'heading2_font' => array(
			'control' => array(
				'type'    => 'Themify_Font_Control',
				'label'   => __( 'Heading 2 Font', 'themify' ),
			),
			'selector' => 'h2',
			'prop' => 'font',
		),
		'heading2_color' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
				'label'   => __( 'Heading 2 Color', 'themify' ),
				'show_label' => false,
			),
			'selector' => 'h2',
			'prop' => 'color',
		),

		'heading3_font' => array(
			'control' => array(
				'type'    => 'Themify_Font_Control',
				'label'   => __( 'Heading 3 Font', 'themify' ),
			),
			'selector' => 'h3',
			'prop' => 'font',
		),
		'heading3_color' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
				'label'   => __( 'Heading 3 Color', 'themify' ),
				'show_label' => false,
			),
			'selector' => 'h3',
			'prop' => 'color',
		),

		'heading4_font' => array(
			'control' => array(
				'type'    => 'Themify_Font_Control',
				'label'   => __( 'Heading 4 Font', 'themify' ),
			),
			'selector' => 'h4',
			'prop' => 'font',
		),
		'heading4_color' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
				'label'   => __( 'Heading 4 Color', 'themify' ),
				'show_label' => false,
			),
			'selector' => 'h4',
			'prop' => 'color',
		),

		'heading5_font' => array(
			'control' => array(
				'type'    => 'Themify_Font_Control',
				'label'   => __( 'Heading 5 Font', 'themify' ),
			),
			'selector' => 'h5',
			'prop' => 'font',
		),
		'heading5_color' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
				'label'   => __( 'Heading 5 Color', 'themify' ),
				'show_label' => false,
			),
			'selector' => 'h5',
			'prop' => 'color',
		),

		'heading6_font' => array(
			'control' => array(
				'type'    => 'Themify_Font_Control',
				'label'   => __( 'Heading 6 Font', 'themify' ),
			),
			'selector' => 'h6',
			'prop' => 'font',
		),
		'heading6_color' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
				'label'   => __( 'Heading 6 Color', 'themify' ),
				'show_label' => false,
			),
			'selector' => 'h6',
			'prop' => 'color',
		),

		'end_headings_acc' => $themify_customizer->accordion_end(),
		// Accordion End   ---------------------------

		// Accordion Start ---------------------------
		'start_forms_acc' => $themify_customizer->accordion_start( __( 'Forms', 'themify' ) ),

		'form_fields_background' => array(
			'control' => array(
				'type'    => 'Themify_Background_Control',
				'label'   => __( 'Form Fields', 'themify' ),
			),
			'selector' => 'textarea, input[type=text], input[type=password], input[type=search], input[type=email], input[type=url], input[type=number], input[type=tel], input[type=date], input[type=datetime], input[type=datetime-local], input[type=month], input[type=time], input[type=week]',
			'prop' => 'background',
		),

		'form_fields_border' => array(
			'control' => array(
				'type'    => 'Themify_Border_Control',
				'label'   => __( 'Form Fields Border', 'themify' ),
				'show_label' => false,
			),
			'selector' => 'textarea, input[type=text], input[type=password], input[type=search], input[type=email], input[type=url], input[type=number], input[type=tel], input[type=date], input[type=datetime], input[type=datetime-local], input[type=month], input[type=time], input[type=week]',
			'prop' => 'border',
		),

		'form_fields_padding' => array(
			'control' => array(
				'type'    => 'Themify_Padding_Control',
				'label'   => __( 'Form Fields Padding', 'themify' ),
				'show_label' => false,
			),
			'selector' => 'textarea, input[type=text], input[type=password], input[type=search], input[type=email], input[type=url], input[type=number], input[type=tel], input[type=date], input[type=datetime], input[type=datetime-local], input[type=month], input[type=time], input[type=week]',
			'prop' => 'padding',
		),

		'form_fields_color' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
				'label'   => __( 'Form Fields Color', 'themify' ),
				'show_label' => false,
			),
			'selector' => 'textarea, input[type=text], input[type=password], input[type=search], input[type=email], input[type=url], input[type=number], input[type=tel], input[type=date], input[type=datetime], input[type=datetime-local], input[type=month], input[type=time], input[type=week]',
			'prop' => 'color',
		),

		'form_fields_font' => array(
			'control' => array(
				'type'    => 'Themify_Font_Control',
				'label'   => __( 'Form Fields', 'themify' ),
				'show_label' => false,
			),
			'selector' => 'textarea, input[type=text], input[type=password], input[type=search], input[type=email], input[type=url], input[type=number], input[type=tel], input[type=date], input[type=datetime], input[type=datetime-local], input[type=month], input[type=time], input[type=week]',
			'prop' => 'font',
		),
		
		'form_fields_focus_background' => array(
			'control' => array(
				'type'    => 'Themify_Background_Control',
				'label'   => __( 'Form Fields Focus', 'themify' ),
			),
			'selector' => 'textarea:focus, input[type=text]:focus, input[type=password]:focus, input[type=search]:focus, input[type=email]:focus, input[type=url]:focus, input[type=number]:focus, input[type=tel]:focus, input[type=date]:focus, input[type=datetime]:focus, input[type=datetime-local]:focus, input[type=month]:focus, input[type=time]:focus, input[type=week]:focus',
			'prop' => 'background',
		),

		'form_fields_focus_border' => array(
			'control' => array(
				'type'    => 'Themify_Border_Control',
				'label'   => __( 'Form Fields Focus Border', 'themify' ),
				'show_label' => false,
			),
			'selector' => 'textarea:focus, input[type=text]:focus, input[type=password]:focus, input[type=search]:focus, input[type=email]:focus, input[type=url]:focus, input[type=number]:focus, input[type=tel]:focus, input[type=date]:focus, input[type=datetime]:focus, input[type=datetime-local]:focus, input[type=month]:focus, input[type=time]:focus, input[type=week]:focus',
			'prop' => 'border',
		),

		'form_fields_focus_color' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
				'label'   => __( 'Form Fields Focus Color', 'themify' ),
				'show_label' => false,
			),
			'selector' => 'textarea:focus, input[type=text]:focus, input[type=password]:focus, input[type=search]:focus, input[type=email]:focus, input[type=url]:focus, input[type=number]:focus, input[type=tel]:focus, input[type=date]:focus, input[type=datetime]:focus, input[type=datetime-local]:focus, input[type=month]:focus, input[type=time]:focus, input[type=week]:focus',
			'prop' => 'color',
		),

		'form_buttons_background' => array(
			'control' => array(
				'type'    => 'Themify_Background_Control',
				'label'   => __( 'Form Buttons', 'themify' ),
			),
			'selector' => 'input[type=reset], input[type=submit], button',
			'prop' => 'background',
		),

		'form_buttons_border' => array(
			'control' => array(
				'type'    => 'Themify_Border_Control',
				'label'   => __( 'Form Buttons Border', 'themify' ),
				'show_label' => false,
			),
			'selector' => 'input[type=reset], input[type=submit], button',
			'prop' => 'border',
		),

		'form_buttons_color' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
				'label'   => __( 'Form Buttons Color', 'themify' ),
				'show_label' => false,
			),
			'selector' => 'input[type=reset], input[type=submit], button',
			'prop' => 'color',
		),

		'form_buttons_hover_background' => array(
			'control' => array(
				'type'    => 'Themify_Background_Control',
				'label'   => __( 'Form Buttons Hover', 'themify' ),
			),
			'selector' => 'input[type=reset]:hover, input[type=submit]:hover, button:hover',
			'prop' => 'background',
		),

		'form_buttons_hover_border' => array(
			'control' => array(
				'type'    => 'Themify_Border_Control',
				'label'   => __( 'Form Buttons Hover Border', 'themify' ),
				'show_label' => false,
			),
			'selector' => 'input[type=reset]:hover, input[type=submit]:hover, button:hover',
			'prop' => 'border',
		),

		'form_buttons_hover_color' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
				'label'   => __( 'Form Buttons Hover Color', 'themify' ),
				'show_label' => false,
			),
			'selector' => 'input[type=reset]:hover, input[type=submit]:hover, button:hover',
			'prop' => 'color',
		),

		'end_forms_acc' => $themify_customizer->accordion_end(),
		// Accordion End   ---------------------------

		// Accordion Start ---------------------------
		'start_header_acc' => $themify_customizer->accordion_start( __( 'Header', 'themify' ) ),

		'headerwrap_background' => array(
			'control' => array(
				'type'    => 'Themify_Background_Control',
				'label'   => __( 'Header Wrap', 'themify' ),
			),
			'selector' => '#headerwrap',
			'prop' => 'background',
		),

		'headerwrap_border' => array(
			'control' => array(
				'type'    => 'Themify_Border_Control',
				'label'   => __( 'Header Wrap Border', 'themify' ),
				'show_label' => false,
			),
			'selector' => '#headerwrap',
			'prop' => 'border',
		),

		'headerwrap_margin' => array(
			'control' => array(
				'type'    => 'Themify_Margin_Control',
				'label'   => __( 'Header Wrap Margin', 'themify' ),
				'show_label' => false,
			),
			'selector' => '#headerwrap',
			'prop' => 'margin',
		),

		'headerwrap_padding' => array(
			'control' => array(
				'type'    => 'Themify_Padding_Control',
				'label'   => __( 'Header Wrap Padding', 'themify' ),
				'show_label' => false,
			),
			'selector' => '#headerwrap',
			'prop' => 'padding',
		),

		'headerinner_background' => array(
			'control' => array(
				'type'    => 'Themify_Background_Control',
				'label'   => __( 'Header Inner Background', 'themify' ),
			),
			'selector' => '#header',
			'prop' => 'background',
		),

		'headerinner_height' => array(
			'control' => array(
				'type'    => 'Themify_Height_Control',
				'label'   => __( 'Header Inner', 'themify' ),
				'show_label' => false,
			),
			'selector' => '#header',
			'prop' => 'height',
		),

		'headerinner_border' => array(
			'control' => array(
				'type'    => 'Themify_Border_Control',
			),
			'selector' => '#header',
			'prop' => 'border',
		),

		'headerinner_margin' => array(
			'control' => array(
				'type'    => 'Themify_Margin_Control',
				'label'   => __( 'Header Inner Margin', 'themify' ),
				'show_label' => false,
			),
			'selector' => '#header',
			'prop' => 'margin',
		),

		'headerinner_padding' => array(
			'control' => array(
				'type'    => 'Themify_Padding_Control',
				'label'   => __( 'Header Inner Padding', 'themify' ),
				'show_label' => false,
			),
			'selector' => '#header',
			'prop' => 'padding',
		),

		'header_font' => array(
			'control' => array(
				'type'    => 'Themify_Font_Control',
				'label'   => __( 'Header Font', 'themify' ),
			),
			'selector' => '#header',
			'prop' => 'font',
		),

		'header_font_color' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
				'label'   => __( 'Header Font Color', 'themify' ),
				'show_label' => false,
			),
		'selector' => '#header',
			'prop' => 'color',
		),

		'header_link_font' => array(
			'control' => array(
				'type'    => 'Themify_Text_Decoration_Control',
				'label'   => __( 'Header Link', 'themify' ),
			),
			'selector' => '#header a',
			'prop' => 'font',
		),

		'header_link_color' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
				'label'   => __( 'Header Link Color', 'themify' ),
				'show_label' => false,
			),
			'selector' => '#header a',
			'prop' => 'color',
		),

		'header_link_hover_font' => array(
			'control' => array(
				'type'    => 'Themify_Text_Decoration_Control',
				'label'   => __( 'Header Link Hover', 'themify' ),
			),
			'selector' => '#header a:hover',
			'prop' => 'font',
		),
		
		'header_link_hover_color' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
			),
			'selector' => '#header a:hover',
			'prop' => 'color',
		),

		'top_bar_widgets_background' => array(
			'control' => array(
				'label'   => __( 'Top Bar Widgets', 'themify' ),
				'type'    => 'Themify_Background_Control',
			),
			'selector' => '.top-bar-widgets',
			'prop' => 'background',
		),

		'top_bar_widgets_link_color' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
				'color_label'   => __( 'Link Color', 'themify' ),
			),
			'selector' => '.top-bar-widgets a',
			'prop' => 'color',
		),

		'top_bar_widgets_color' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
			),
			'selector' => '.top-bar-widgets',
			'prop' => 'color',
		),

		'top_bar_widgets_font' => array(
			'control' => array(
				'type'    => 'Themify_Font_Control',
			),
			'selector' => '.top-bar-widgets',
			'prop' => 'font',
		),

		'end_header_acc' => $themify_customizer->accordion_end(),
		// Accordion End   ---------------------------

		// Accordion Start ---------------------------
		'start_sticky_header_acc' => $themify_customizer->accordion_start( __( 'Sticky Header', 'themify' ) ),

		'sticky_headerwrap_background' => array(
			'control' => array(
				'type'    => 'Themify_Background_Control',
				'label'   => __( 'Sticky Header Wrap', 'themify' ),
			),
			'selector' => '#headerwrap.fixed-header, .transparent-header #headerwrap.fixed-header',
			'prop' => 'background',
		),

		'sticky_header_imageselect' => array(
			'control' => array(
				'type'    => 'Themify_Image_Control',
				'label'   => __( 'Sticky Header Logo', 'themify' ),
				'image_options' => array(
					'show_size_fields' => true,
					'image_label' => ''
				)
			),
			'selector' => '#headerwrap.fixed-header #site-logo a',
			'prop' => 'logo',
		),

		'sticky_header_font' => array(
			'control' => array(
				'type'    => 'Themify_Font_Control',
				'label'   => __( 'Sticky Header Font', 'themify' ),
			),
			'selector' => '#headerwrap.fixed-header #header, #headerwrap.fixed-header #site-description',
			'prop' => 'font',
		),

		'sticky_header_font_color' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
				'label'   => __( 'Sticky Header Font Color', 'themify' ),
				'show_label' => false,
			),
		'selector' => '#headerwrap.fixed-header #header',
			'prop' => 'color',
		),

		'sticky_header_link_font' => array(
			'control' => array(
				'type'    => 'Themify_Text_Decoration_Control',
				'label'   => __( 'Sticky Header Link', 'themify' ),
			),
			'selector' => '#headerwrap.fixed-header #site-logo a, #headerwrap.fixed-header .icon-menu li > a, #headerwrap.fixed-header #main-nav > li > a, #headerwrap.fixed-header #menu-icon',
			'prop' => 'font',
		),

		'sticky_header_link_color' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
				'label'   => __( 'Sticky Header Link Color', 'themify' ),
				'show_label' => false,
			),
			'selector' => 'body:not(.mobile_menu_active) #headerwrap.fixed-header #header a, .mobile_menu_active #headerwrap.fixed-header #site-logo a, .mobile_menu_active #headerwrap.fixed-header #menu-icon',
			'prop' => 'color',
		),

		'sticky_header_link_hover_font' => array(
			'control' => array(
				'type'    => 'Themify_Text_Decoration_Control',
				'label'   => __( 'Sticky Header Link Hover', 'themify' ),
			),
			'selector' => '#headerwrap.fixed-header #site-logo a:hover, #headerwrap.fixed-header .icon-menu li > a:hover, #headerwrap.fixed-header #main-nav > li > a:hover, #headerwrap.fixed-header #menu-icon:hover',
			'prop' => 'font',
		),

		'sticky_header_link_hover_color' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
			),
			'selector' => 'body:not(.mobile_menu_active) #headerwrap.fixed-header #header a:hover, .mobile_menu_active #headerwrap.fixed-header #site-logo a:hover, .mobile_menu_active #headerwrap.fixed-header #menu-icon:hover',
			'prop' => 'color',
		),

		'end_sticky_header_acc' => $themify_customizer->accordion_end(),
		// Accordion End   ---------------------------

		// Accordion Start ---------------------------
		'start_titletagline_acc' => $themify_customizer->accordion_start( __( 'Site Logo &amp; Tagline', 'themify' ) ),

		// This element is not CSS, but markup written by site_logo()
		'site-logo_image' => array(
			'setting' => array(
				'default' => '',
			),
			'control' => array(
				'type'    => 'Themify_Logo_Control',
				'label'   => __( 'Site Logo', 'themify' ),
			),
			'selector' => 'body #site-logo a',
			'prop' => 'logo',
		),

		'site-logo_position' => array(
			'control' => array(
				'type'    => 'Themify_Position_Control',
				'label'   => __( 'Site Logo Position', 'themify' ),
			),
			'selector' => '#site-logo',
			'prop' => 'position',
		),

		'site-logo_margin' => array(
			'control' => array(
				'type'    => 'Themify_Margin_Control',
				'label'   => __( 'Site Logo Margin', 'themify' ),
			),
			'selector' => '#site-logo',
			'prop' => 'margin',
		),

		// This element is not CSS, but markup written by site_description()
		'site-tagline' => array(
			'control' => array(
				'type'    => 'Themify_Tagline_Control',
				'label'   => __( 'Site Tagline', 'themify' ),
			),
			'selector' => '#site-description',
			'prop' => 'tagline',
		),

		'site-tagline_position' => array(
			'control' => array(
				'type'    => 'Themify_Position_Control',
				'label'   => __( 'Site Tagline Position', 'themify' ),
			),
			'selector' => '#site-description',
			'prop' => 'position',
		),

		'site-tagline_margin' => array(
			'control' => array(
				'type'    => 'Themify_Margin_Control',
				'label'   => __( 'Site Tagline Margin', 'themify' ),
			),
			'selector' => '#site-description',
			'prop' => 'margin',
		),

		'end_titletagline_acc' => $themify_customizer->accordion_end(),
		// Accordion End   ---------------------------

		// Accordion Start ---------------------------
		'start_nav_acc' => $themify_customizer->accordion_start( __( 'Main Navigation', 'themify' ) ),


		'main_nav_background' => array(
			'control' => array(
				'type'    => 'Themify_Background_Control',
				'label'   => __( 'Menu Container', 'themify' ),
			),
			'selector' => '#main-nav',
			'prop' => 'background',
		),

		'main_nav_position' => array(
			'control' => array(
				'type'    => 'Themify_Position_Control',
				'label'   => __( 'Menu Container Position', 'themify' ),
				'show_label' => false,
			),
			'selector' => '#main-nav',
			'prop' => 'position',
		),

		'main_nav_width' => array(
			'control' => array(
				'type'  => 'Themify_Width_Control',
				'label' => __( 'Width', 'themify' ),
				'show_label' => false,
			),
			'selector' => '#main-nav',
			'prop' => 'width',
		),

		'main_nav_height' => array(
			'control' => array(
				'type'  => 'Themify_Height_Control',
				'label' => __( 'Height', 'themify' ),
				'show_label' => false,
			),
			'selector' => '#main-nav',
			'prop' => 'height',
		),

		'main_nav_border' => array(
			'control' => array(
				'type'    => 'Themify_Border_Control',
				'label'   => __( 'Menu Container Border', 'themify' ),
				'show_label' => false,
			),
			'selector' => '#main-nav',
			'prop' => 'border',
		),

		'main_nav_margin' => array(
			'control' => array(
				'type'    => 'Themify_Margin_Control',
				'label'   => __( 'Menu Container Margin', 'themify' ),
				'show_label' => false,
			),
			'selector' => '#main-nav',
			'prop' => 'margin',
		),

		'main_nav_padding' => array(
			'control' => array(
				'type'    => 'Themify_Padding_Control',
				'label'   => __( 'Menu Container Padding', 'themify' ),
				'show_label' => false,
			),
			'selector' => '#main-nav',
			'prop' => 'padding',
		),

		'main_nav_link_background' => array(
			'control' => array(
				'type'    => 'Themify_Color_Transparent_Control',
				'label'   => __( 'Menu Link', 'themify' ),
				'color_label' => __( 'Background Color', 'themify' ),
			),
			'selector' => '#main-nav a',
			'prop' => 'background',
		),

		'main_nav_link_border' => array(
			'control' => array(
				'type'    => 'Themify_Border_Control',
				'label'   => __( 'Menu Link Border', 'themify' ),
				'show_label' => false,
			),
			'selector' => '#main-nav a',
			'prop' => 'border',
		),

		'main_nav_link_margin' => array(
			'control' => array(
				'type'    => 'Themify_Margin_Control',
				'label'   => __( 'Menu Link Margin', 'themify' ),
				'show_label' => false,
			),
			'selector' => '#main-nav a',
			'prop' => 'margin',
		),

		'main_nav_link_padding' => array(
			'control' => array(
				'type'    => 'Themify_Padding_Control',
				'label'   => __( 'Menu Link Padding', 'themify' ),
				'show_label' => false,
			),
			'selector' => '#main-nav a',
			'prop' => 'padding',
		),

		'main_nav_link_color' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
				'label'   => __( 'Menu Link Color', 'themify' ),
				'show_label' => false,
			),
			'selector' => '#main-nav a, .transparent-header #main-nav a',
			'prop' => 'color',
		),

		'main_nav_link_font' => array(
			'control' => array(
				'type'    => 'Themify_Font_Control',
				'label'   => __( 'Menu Link Font', 'themify' ),
				'show_label' => false,
			),
			'selector' => '#main-nav a',
			'prop' => 'font',
		),

		'main_nav_link_hover_background' => array(
			'control' => array(
				'type'    => 'Themify_Color_Transparent_Control',
				'label'   => __( 'Menu Link Hover', 'themify' ),
				'color_label' => __( 'Background Color', 'themify' ),
			),
			'selector' => '#main-nav a:hover',
			'prop' => 'background',
		),

		'main_nav_link_hover_color' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
				'label'   => __( 'Menu Link Hover Color', 'themify' ),
				'show_label' => false,
			),
			'selector' => '#main-nav a:hover, .transparent-header #main-nav a:hover',
			'prop' => 'color',
		),

		'main_nav_link_active_background' => array(
			'control' => array(
				'type'    => 'Themify_Color_Transparent_Control',
				'label'   => __( 'Menu Active Link', 'themify' ),
				'color_label' => __( 'Background Color', 'themify' ),
			),
			'selector' => '#main-nav .current_page_item > a, #main-nav .current-menu-item > a',
			'prop' => 'background',
		),

		'main_nav_link_active_color' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
				'label'   => __( 'Menu Active Link Color', 'themify' ),
				'show_label' => false,
			),
			'selector' => '#main-nav .current_page_item > a, #main-nav .current-menu-item > a',
			'prop' => 'color',
		),

		'main_nav_link_active_hover_background' => array(
			'control' => array(
				'type'    => 'Themify_Color_Transparent_Control',
				'label'   => __( 'Menu Active Link Hover', 'themify' ),
				'color_label' => __( 'Background Color', 'themify' ),
			),
			'selector' => '#main-nav .current_page_item > a:hover, #main-nav .current-menu-item > a:hover',
			'prop' => 'background',
		),

		'main_nav_link_active_hover_color' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
				'label'   => __( 'Menu Active Link Hover Color', 'themify' ),
				'show_label' => false,
			),
			'selector' => '#main-nav .current_page_item > a:hover, #main-nav .current-menu-item > a:hover',
			'prop' => 'color',
		),

		'main_nav_highlight_link_background' => array(
			'control' => array(
				'type'    => 'Themify_Color_Transparent_Control',
				'label'   => __( 'Highlight Link', 'themify' ),
				'color_label' => __( 'Background Color', 'themify' ),
			),
			'selector' => '#main-nav .highlight-link>a',
			'prop' => 'background',
		),
		'main_nav_highlight_link_color' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
				'label'   => __( 'Highlight Link Color', 'themify' ),
				'show_label' => false,
			),
			'selector' => '#main-nav .highlight-link>a',
			'prop' => 'color',
		),
		'main_nav_highlight_link_padding' => array(
			'control' => array(
				'type'    => 'Themify_Padding_Control',
				'label'   => __( 'Highlight Link Padding', 'themify' ),
				'show_label' => false,
			),
			'selector' => '#main-nav .highlight-link>a',
			'prop' => 'padding',
		),
		'main_nav_highlight_link_border' => array(
			'control' => array(
				'type'    => 'Themify_Border_Control',
				'label'   => __( 'Highlight Link Border', 'themify' ),
				'show_label' => false,
			),
			'selector' => '#main-nav .highlight-link>a',
			'prop' => 'border',
		),

		'main_nav_dropdown_background' => array(
			'control' => array(
				'type'    => 'Themify_Background_Control',
				'label'   => __( 'Dropdown Container', 'themify' ),
			),
			'selector' => '#main-nav li .sub-menu, #main-nav .has-mega-sub-menu .mega-sub-menu, #main-nav .has-mega-column > .sub-menu',
			'prop' => 'background',
		),

		'main_nav_dropdown_border' => array(
			'control' => array(
				'type'    => 'Themify_Border_Control',
				'label'   => __( 'Dropdown Container Border', 'themify' ),
				'show_label' => false,
			),
			'selector' => '#main-nav li .sub-menu, #main-nav .has-mega-sub-menu .mega-sub-menu, #main-nav .has-mega-column > .sub-menu',
			'prop' => 'border',
		),

		'main_nav_dropdown_margin' => array(
			'control' => array(
				'type'    => 'Themify_Margin_Control',
				'label'   => __( 'Dropdown Container Margin', 'themify' ),
				'show_label' => false,
			),
			'selector' => '#main-nav li .sub-menu, #main-nav .has-mega-sub-menu .mega-sub-menu, #main-nav .has-mega-column > .sub-menu',
			'prop' => 'margin',
		),

		'main_nav_dropdown_padding' => array(
			'control' => array(
				'type'    => 'Themify_Padding_Control',
				'label'   => __( 'Dropdown Container Padding', 'themify' ),
				'show_label' => false,
			),
			'selector' => '#main-nav li .sub-menu, #main-nav .has-mega-sub-menu .mega-sub-menu, #main-nav .has-mega-column > .sub-menu',
			'prop' => 'padding',
		),

		'main_nav_dropdown_link_background' => array(
			'control' => array(
				'type'    => 'Themify_Color_Transparent_Control',
				'label'   => __( 'Dropdown Link', 'themify' ),
				'color_label' => __( 'Background Color', 'themify' ),
			),
			'selector' => '#main-nav ul a, #main-nav .current_page_item ul a, #main-nav ul .current_page_item > a, #main-nav .current-menu-item ul a, #main-nav ul .current-menu-item > a, #main-nav .has-mega-column>.sub-menu a, #main-nav .has-mega-column > .sub-menu a',
			'prop' => 'background',
		),

		'main_nav_dropdown_link_border' => array(
			'control' => array(
				'type'    => 'Themify_Border_Control',
				'label'   => __( 'Dropdown Link Border', 'themify' ),
				'show_label' => false,
			),
			'selector' => '#main-nav ul a, #main-nav .current_page_item ul a, #main-nav ul .current_page_item > a, #main-nav .current-menu-item ul a, #main-nav ul .current-menu-item > a',
			'prop' => 'border',
		),

		'main_nav_dropdown_link_margin' => array(
			'control' => array(
				'type'    => 'Themify_Margin_Control',
				'label'   => __( 'Dropdown Link Margin', 'themify' ),
				'show_label' => false,
			),
			'selector' => '#main-nav ul a, #main-nav .current_page_item ul a, #main-nav ul .current_page_item > a, #main-nav .current-menu-item ul a, #main-nav ul .current-menu-item > a',
			'prop' => 'margin',
		),

		'main_nav_dropdown_link_padding' => array(
			'control' => array(
				'type'    => 'Themify_Padding_Control',
				'label'   => __( 'Dropdown Link Padding', 'themify' ),
				'show_label' => false,
			),
			'selector' => '#main-nav ul a, #main-nav .current_page_item ul a, #main-nav ul .current_page_item > a, #main-nav .current-menu-item ul a, #main-nav ul .current-menu-item > a',
			'prop' => 'padding',
		),
		'main_nav_dropdown_link_color' => array(
			'control' => array(
				'type'    => 'Themify_Color_Transparent_Control',
				'label'   => __( 'Dropdown Link Color', 'themify' ),
				'show_label' => false,
			),
			'selector' => '#main-nav ul a, #main-nav .current_page_item ul a, #main-nav ul .current_page_item > a, #main-nav .current-menu-item ul a, #main-nav ul .current-menu-item > a, #main-nav .has-mega-column>.sub-menu a, #main-nav .has-mega-column > .sub-menu a',
			'prop' => 'color',
		),

		'main_nav_dropdown_link_font' => array(
			'control' => array(
				'type'    => 'Themify_Font_Control',
				'label'   => __( 'Dropdown Link Font', 'themify' ),
				'show_label' => false,
			),
			'selector' => '#main-nav ul a, #main-nav .current_page_item ul a, #main-nav ul .current_page_item > a, #main-nav .current-menu-item ul a, #main-nav ul .current-menu-item > a',
			'prop' => 'font',
		),


		'main_nav_dropdown_link_hover_background' => array(
		  'control' => array(
		    'type'    => 'Themify_Color_Transparent_Control',
		    'label'   => __( 'Dropdown Link Hover', 'themify' ),
		    'color_label' => __( 'Background Color', 'themify' ),
		  ),
		  'selector' => '#main-nav ul a:hover, #main-nav .current_page_item ul a:hover, #main-nav ul .current_page_item a:hover, #main-nav .current-menu-item ul a:hover, #main-nav ul .current-menu-item a:hover',
		  'prop' => 'background',
		),

		'main_nav_dropdown_link_hover_color' => array(
		  'control' => array(
		    'type'    => 'Themify_Color_Transparent_Control',
		    'label'   => __( 'Dropdown Link Hover Color', 'themify' ),
		    'show_label' => false,
		  ),
		  'selector' => '#main-nav ul a:hover, #main-nav .current_page_item ul a:hover, #main-nav ul .current_page_item a:hover, #main-nav .current-menu-item ul a:hover, #main-nav ul .current-menu-item a:hover',
		  'prop' => 'color',
		),
		'main_nav_link_dropdown_active_background' => array(
			'control' => array(
				'type'    => 'Themify_Color_Transparent_Control',
				'label'   => __( 'Dropdown Active Link', 'themify' ),
				'color_label' => __( 'Background Color', 'themify' ),
			),
			'selector' => '#main-nav li .current_page_item > a, #main-nav li .current-menu-item > a, #main-nav ul .current-menu-item > a, #main-nav .has-mega-column>.sub-menu .current-menu-item > a, #main-nav .has-mega-column > .sub-menu .current-menu-item > a,
			#main-nav .has-mega-column>.sub-menu .current-cat > a, #main-nav .has-mega-column > .sub-menu .current-cat > a',
			'prop' => 'background',
		),

		'main_nav_link_dropdown_active_color' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
				'label'   => __( 'Dropdown Active Link Color', 'themify' ),
				'show_label' => false,
			),
			'selector' => '#main-nav li .current_page_item > a, #main-nav li .current-menu-item > a, #main-nav ul .current-menu-item > a, #main-nav .has-mega-column>.sub-menu .current-menu-item > a, #main-nav .has-mega-column > .sub-menu .current-menu-item > a,
			#main-nav .has-mega-column>.sub-menu .current-cat > a, #main-nav .has-mega-column > .sub-menu .current-cat > a',
			'prop' => 'color',
		),
		
		'end_nav_acc' => $themify_customizer->accordion_end(),
		// Accordion End   ---------------------------

		// Accordion Start ---------------------------
		'start_mobile_menu_acc' => $themify_customizer->accordion_start( __( 'Mobile Menu', 'themify' ) ),
		'mobile_menu_panel_background' => array(
			'control' => array(
				'type'    => 'Themify_Background_Control',
				'label'   => __( 'Mobile Menu Panel', 'themify' ),
				'show_label' => true,
			),
			'selector' => '.mobile_menu_active #headerwrap .sidemenu-on',
			'prop' => 'background',
			'global' => true,
		),
		'mobile_menu_panel_color' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
				'color_label'   => __( 'Color', 'themify' )
			),
			'selector' => '.mobile_menu_active #headerwrap .sidemenu-on',
			'prop' => 'color',
			'global' => true,
		),
		'mobile_menu_panel_link_color' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
				'color_label'   => __( 'Panel Link Color', 'themify' )
			),
			'selector' => '.mobile_menu_active #headerwrap .sidemenu.sidemenu-on a',
			'prop' => 'color',
			'global' => true,
		),
		'mobile_menu_font' => array(
			'control' => array(
				'type'    => 'Themify_Font_Control',
				'label'   => __( 'Mobile Menu Link', 'themify' )
			),
			'selector' => '.mobile_menu_active #headerwrap .sidemenu-on nav li a',
			'prop' => 'font',
			'global' => true,
		),
		'mobile_menu_link_color' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
				'color_label'   => __( 'Link Color', 'themify' )
			),
			'selector' => '.mobile_menu_active #headerwrap .sidemenu-on #main-nav a',
			'prop' => 'color',
			'global' => true,
		),
		'mobile_menu_link_hover_background' => array(
			'control' => array(
				'type'    => 'Themify_Color_Transparent_Control',
				'label'   => __( 'Mobile Menu Link Hover', 'themify' ),
				'color_label' => __( 'Background Color', 'themify' ),
			),
			'selector' => '.mobile_menu_active #headerwrap .sidemenu-on #main-nav a:hover, .mobile_menu_active #headerwrap .sidemenu-on #main-nav .current-menu-item > a',
			'prop' => 'background',
			'global' => true,
		),
		'mobile_menu_link_hover_color' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
				'label'   => __( 'Mobile Menu Link Hover Color', 'themify' ),
				'show_label' => false,
			),
			'selector' => '.mobile_menu_active #headerwrap .sidemenu-on #main-nav a:hover, .mobile_menu_active #headerwrap .sidemenu-on #main-nav .current-menu-item > a',
			'prop' => 'color',
			'global' => true,
		),

		'mobile_menu_link_active_background' => array(
			'control' => array(
				'type'    => 'Themify_Color_Transparent_Control',
				'label'   => __( 'Mobile Menu Active Link', 'themify' ),
				'color_label' => __( 'Background Color', 'themify' ),
			),
			'selector' => '.mobile_menu_active #headerwrap .sidemenu-on #main-nav .current_page_item > a, .mobile_menu_active #headerwrap .sidemenu-on #main-nav .current-menu-item > a',
			'prop' => 'background',
			'global' => true,
		),

		'mobile_menu_link_active_color' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
				'label'   => __( 'Mobile Menu Active Link Color', 'themify' ),
				'show_label' => false,
			),
			'selector' => '.mobile_menu_active #headerwrap .sidemenu-on #main-nav .current_page_item > a, .mobile_menu_active #headerwrap .sidemenu-on #main-nav .current-menu-item > a',
			'prop' => 'color',
			'global' => true,
		),

		'mobile_menu_link_active_hover_background' => array(
			'control' => array(
				'type'    => 'Themify_Color_Transparent_Control',
				'label'   => __( 'Mobile Menu Active Link Hover', 'themify' ),
				'color_label' => __( 'Background Color', 'themify' ),
			),
			'selector' => '.mobile_menu_active #headerwrap .sidemenu-on #main-nav .current_page_item > a:hover, .mobile_menu_active #headerwrap .sidemenu-on #main-nav .current-menu-item > a:hover',
			'prop' => 'background',
			'global' => true,
		),

		'mobile_menu_link_active_hover_color' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
				'label'   => __( 'Mobile Menu Active Link Hover Color', 'themify' ),
				'show_label' => false,
			),
			'selector' => '.mobile_menu_active #headerwrap .sidemenu-on #main-nav .current_page_item > a:hover, .mobile_menu_active #headerwrap .sidemenu-on #main-nav .current-menu-item > a:hover',
			'prop' => 'color',
			'global' => true,
		),

		'mobile_menu_dropdown_link_font' => array(
			'control' => array(
				'type'    => 'Themify_Font_Control',
				'label'   => __( 'Mobile Menu Dropdown Link', 'themify' ),
			),
			'selector' => '.mobile_menu_active #headerwrap .sidemenu-on #main-nav ul a',
			'prop' => 'font',
			'global' => true,
		),

		'mobile_menu_dropdown_link_color' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
				'label'   => __( 'Mobile Menu Dropdown Link Color', 'themify' ),
				'show_label' => false,
			),
			'selector' => '.mobile_menu_active #headerwrap .sidemenu-on #main-nav ul a',
			'prop' => 'color',
			'global' => true,
		),

		'mobile_menu_dropdown_link_hover_background' => array(
		  'control' => array(
		    'type'    => 'Themify_Color_Transparent_Control',
		    'label'   => __( 'Mobile Menu Dropdown Link Hover', 'themify' ),
		    'color_label' => __( 'Background Color', 'themify' ),
		  ),
		  'selector' => '.mobile_menu_active #headerwrap .sidemenu-on #main-nav ul a:hover',
		  'prop' => 'background',
		  'global' => true,
		),

		'mobile_menu_dropdown_link_hover_color' => array(
		  'control' => array(
		    'type'    => 'Themify_Color_Control',
		    'label'   => __( 'Mobile Menu Dropdown Link Hover Color', 'themify' ),
		    'show_label' => false,
		  ),
		  'selector' => '.mobile_menu_active #headerwrap .sidemenu-on #main-nav ul a:hover',
		  'prop' => 'color',
		  'global' => true,
		),

		'mobile_menu_icon_background' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
				'label'   => __( 'Mobile Menu Icon', 'themify' ),
				'color_label'   => __( 'Background Color', 'themify' ),
				'show_label' => true,
			),
			'selector' => '.mobile_menu_active #menu-icon',
			'prop' => 'background',
			'global' => true,
		),

		'mobile_menu_icon_height' => array(
			'control' => array(
				'type'  => 'Themify_Height_Control',
			),
			'selector' => '.mobile_menu_active .menu-icon-inner',
			'prop' => 'height',
		),
		'mobile_menu_icon_width' => array(
			'control' => array(
				'type'  => 'Themify_Width_Control',
			),
			'selector' => '.mobile_menu_active .menu-icon-inner',
			'prop' => 'width',
		),
				'mobile_menu_icon_padding' => array(
			'control' => array(
				'type'  => 'Themify_Padding_Control',
			),
			'selector' => '.mobile_menu_active #menu-icon',
			'prop' => 'padding',
		),
		'mobile_menu_icon_color' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
				'color_label'   => __( 'Color', 'themify' )
			),
			'selector' => '.mobile_menu_active #menu-icon',
			'prop' => 'color',
			'global' => true,
		),
		'mobile_menu_overlay_background' => array(
			'control' => array(
				'type'    => 'Themify_Color_Transparent_Control',
				'label'   => __( 'Overlay Background', 'themify' ),
				'color_label' => __( 'Background Color', 'themify' ),
			),
			'selector' => '.body-overlay',
			'prop' => 'background',
			'global' => true,
		),

		'end_mobile_menu_acc' => $themify_customizer->accordion_end(),
		// Accordion End   ---------------------------

		// Accordion Start ---------------------------
		'start_shop_acc' => $themify_customizer->accordion_start( __( 'Shop', 'themify' ), 'themify_options', 'themify_is_woocommerce_active' ),
		
		'shop_product_title_font' => array(
			'control' => array(
				'type'    => 'Themify_Font_Control',
				'label'   => __( 'Product Title', 'themify' ),
			),
			'selector' => '.woocommerce ul.products li.product .woocommerce-loop-product__title, .woocommerce ul.products li.product .woocommerce-loop-category__title, .woocommerce .products .product .product_title, .woocommerce ul.products li.product h3, .wc-products .product h3',
			'prop' => 'font',
		),
		'shop_product_title_color' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
				'label'   => __( 'Product Title Color', 'themify' ),
				'show_label' => false,
			),
			'selector' => '.woocommerce ul.products li.product .woocommerce-loop-product__title, .woocommerce ul.products li.product .woocommerce-loop-category__title, .woocommerce .products .product .product_title, .woocommerce ul.products li.product h3, .wc-products .product h3 a',
			'prop' => 'color',
		),
		'shop_product_price_font' => array(
			'control' => array(
				'type'    => 'Themify_Font_Control',
				'label'   => __( 'Price', 'themify' ),
				'font_options' => array( 'show_transform' => false ),
			),
			'selector' => '.woocommerce ul.products li.product .price',
			'prop' => 'font',
		),
		'shop_product_price_color' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
				'label'   => __( 'Product Price Color', 'themify' ),
				'show_label' => false,
			),
			'selector' => '.woocommerce ul.products li.product .price',
			'prop' => 'color',
		),
		
		'shop_add_to_cart_button_background' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
				'color_label'   => __( 'Background Color', 'themify' ),
				'label'   => __( 'Button', 'themify' ),
			),
			'selector' => '.woocommerce ul.products li.product .button,.woocommerce #respond input#submit,.woocommerce #respond input#submit.alt,.woocommerce a.button,.woocommerce button.button,.woocommerce input.button,.woocommerce a.button.alt,.woocommerce button.button.alt,.woocommerce input.button.alt,.woocommerce button.button.alt.disabled,.woocommerce button.button:disabled,.woocommerce button.button:disabled[disabled]',
			'prop' => 'background',
		),
		'shop_add_to_cart_button_border' => array(
			'control' => array(
				'type'    => 'Themify_Border_Control',
				'label'   => __( 'Button Border', 'themify' ),
				'show_label' => false,
			),
			'selector' => '.woocommerce ul.products li.product .button,.woocommerce #respond input#submit,.woocommerce #respond input#submit.alt,.woocommerce a.button,.woocommerce button.button,.woocommerce input.button,.woocommerce a.button.alt,.woocommerce button.button.alt,.woocommerce input.button.alt,.woocommerce button.button.alt.disabled,.woocommerce button.button:disabled,.woocommerce button.button:disabled[disabled]',
			'prop' => 'border',
		),
		'shop_add_to_cart_button_font' => array(
			'control' => array(
				'type'    => 'Themify_Font_Control',
				'label'   => __( 'Button Font', 'themify' ),
				'show_label' => false,
			),
			'selector' => '.woocommerce ul.products li.product .button,.woocommerce #respond input#submit,.woocommerce #respond input#submit.alt,.woocommerce a.button,.woocommerce button.button,.woocommerce input.button,.woocommerce a.button.alt,.woocommerce button.button.alt,.woocommerce input.button.alt,.woocommerce button.button.alt.disabled,.woocommerce button.button:disabled,.woocommerce button.button:disabled[disabled]',
			'prop' => 'font',
		),
		'shop_add_to_cart_button_hover_color' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
				'color_label'   => __( 'Color Hover', 'themify' ),
				'label'   => __( 'Button Color Hover', 'themify' ),
				'show_label' => false,
			),
			'selector' => '.woocommerce ul.products li.product .button:hover,.woocommerce #respond input#submit:hover:hover,.woocommerce #respond input#submit.alt:hover,.woocommerce a.button:hover,.woocommerce button.button:hover,.woocommerce input.button:hover,.woocommerce a.button.alt:hover,.woocommerce button.button.alt:hover,.woocommerce input.button.alt:hover,.woocommerce button.button.alt.disabled:hover,.woocommerce button.button:disabled:hover,.woocommerce button.button:disabled[disabled]:hover',
			'prop' => 'color',
		),
		'shop_add_to_cart_button_hover_background' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
				'color_label'   => __( 'Background Hover', 'themify' ),
				'label'   => __( 'Button Background Hover', 'themify' ),
				'show_label' => false,
			),
			'selector' => '.woocommerce ul.products li.product .button:hover,.woocommerce #respond input#submit:hover:hover,.woocommerce #respond input#submit.alt:hover,.woocommerce a.button:hover,.woocommerce button.button:hover,.woocommerce input.button:hover,.woocommerce a.button.alt:hover,.woocommerce button.button.alt:hover,.woocommerce input.button.alt:hover,.woocommerce button.button.alt.disabled:hover,.woocommerce button.button:disabled:hover,.woocommerce button.button:disabled[disabled]:hover',
			'prop' => 'background',
		),
		'shop_add_to_cart_button_color' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
				'label'   => __( 'Button Color', 'themify' ),
				'show_label' => false,
			),
			'selector' => '.woocommerce ul.products li.product .button,.woocommerce #respond input#submit,.woocommerce #respond input#submit.alt,.woocommerce a.button,.woocommerce button.button,.woocommerce input.button,.woocommerce a.button.alt,.woocommerce button.button.alt,.woocommerce input.button.alt,.woocommerce button.button.alt.disabled,.woocommerce button.button:disabled,.woocommerce button.button:disabled[disabled]',
			'prop' => 'color',
		),
		
		'shop_sale_tag_background_color' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
				'color_label'   => __( 'Background Color', 'themify' ),
				'label'   => __( 'Sale Tag', 'themify' ),
				
			),
			'selector' => '.woocommerce span.onsale:before, .woocommerce ul.products li.product .onsale:before',
			'prop' => 'background',
		),
		'shop_sale_tag_color' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
				'label'   => __( 'Sale Tag Color', 'themify' ),
				'show_label' => false,
			),
			'selector' => '.woocommerce span.onsale, .woocommerce ul.products li.product .onsale',
			'prop' => 'color',
		),
		'shop_sale_tag_font' => array(
			'control' => array(
				'type'    => 'Themify_Font_Control',
				'label'   => __( 'Sale Tag Font', 'themify' ),
				'show_label' => false,
			),
			'selector' => '.woocommerce span.onsale, .woocommerce ul.products li.product .onsale',
			'prop' => 'font',
		),
		
		'shop_slide_cart_font' => array(
			'control' => array(
				'type'    => 'Themify_Font_Control',
				'label'   => __( 'Slide Cart', 'themify' ),
			),
			'selector' => '#slide-cart',
			'prop' => 'font',
		),
		'shop_slide_cart_button_color' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
				'color_label'   => __( 'Button Color', 'themify' ),
				'show_label' => false,
			),
			'selector' => '#slide-cart .button',
			'prop' => 'color',
		),
		'shop_slide_cart_button_background' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
				'color_label'   => __( 'Button Background', 'themify' ),
				'show_label' => false,
			),
			'selector' => '#slide-cart .button',
			'prop' => 'background',
		),
		'shop_slide_cart_link' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
				'color_label'   => __( 'Link Color', 'themify' ),
				'show_label' => false,
			),
			'selector' => '#cart-wrap .product a,#cart-wrap a',
			'prop' => 'color',
		),
		'shop_slide_cart_color' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
				'color_label'   => __( 'Color', 'themify' ),
				'show_label' => false,
			),
			'selector' => '#slide-cart',
			'prop' => 'color',
		),
		'shop_slide_cart_background' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
				'color_label'   => __( 'Background Color', 'themify' ),
				'show_label' => false,
			),
			'selector' => '#slide-cart',
			'prop' => 'background',
		),
		
		'end_shop_acc' => $themify_customizer->accordion_end(),
		// Accordion End   ---------------------------

		// Accordion Start ---------------------------
		'start_product_single_acc' => $themify_customizer->accordion_start( __( 'Product Single', 'themify' ), 'themify_options', 'themify_is_woocommerce_active' ),
		
		'single_product_title_font' => array(
			'control' => array(
				'type'    => 'Themify_Font_Control',
				'label'   => __( 'Product Title', 'themify' ),
			),
			'selector' => '.single-product div.product .product_title',
			'prop' => 'font',
		),
		'single_product_title_color' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
				'label'   => __( 'Product Title Color', 'themify' ),
				'show_label' => false,
			),
			'selector' => '.single-product div.product .product_title',
			'prop' => 'color',
		),
		'single_product_price_font' => array(
			'control' => array(
				'type'    => 'Themify_Font_Control',
				'label'   => __( 'Price', 'themify' ),
				'font_options' => array( 'show_transform' => false ),
			),
			'selector' => '.single-product div.product p.price',
			'prop' => 'font',
		),
		'single_product_price_color' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
				'label'   => __( 'Product Price Color', 'themify' ),
				'show_label' => false,
			),
			'selector' => '.single-product div.product p.price',
			'prop' => 'color',
		),
		'single_add_to_cart_button_background' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
				'color_label'   => __( 'Background Color', 'themify' ),
				'label'   => __( 'Button', 'themify' ),
			),
			'selector' => '.single-product #content input.button, .single-product #respond input#submit, .single-product #content input.button.alt, .single-product #respond input#submit.alt, .single-product a.button, .single-product button.button, .single-product input.button, .single-product a.button.alt, .single-product button.button.alt, .single-product input.button.alt, .single-product button.button.alt.disabled, .single-product button.button:disabled, .single-product button.button:disabled[disabled]',
			'prop' => 'background',
		),
		'single_add_to_cart_button_border' => array(
			'control' => array(
				'type'    => 'Themify_Border_Control',
				'label'   => __( 'Button Border', 'themify' ),
				'show_label' => false,
			),
			'selector' => '.single-product #content input.button, .single-product #respond input#submit, .single-product #content input.button.alt, .single-product #respond input#submit.alt, .single-product a.button, .single-product button.button, .single-product input.button, .single-product a.button.alt, .single-product button.button.alt, .single-product input.button.alt, .single-product button.button.alt.disabled, .single-product button.button:disabled, .single-product button.button:disabled[disabled]',
			'prop' => 'border',
		),
		'single_add_to_cart_button_font' => array(
			'control' => array(
				'type'    => 'Themify_Font_Control',
				'label'   => __( 'Button Font', 'themify' ),
				'show_label' => false,
			),
			'selector' => '.single-product #content input.button, .single-product #respond input#submit, .single-product #content input.button.alt, .single-product #respond input#submit.alt, .single-product a.button, .single-product button.button, .single-product input.button, .single-product a.button.alt, .single-product button.button.alt, .single-product input.button.alt, .single-product button.button.alt.disabled, .single-product button.button:disabled, .single-product button.button:disabled[disabled]',
			'prop' => 'font',
		),
		'single_add_to_cart_button_hover_color' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
				'color_label'   => __( 'Color Hover', 'themify' ),
				'label'   => __( 'Button Color Hover', 'themify' ),
				'show_label' => false,
			),
			'selector' => '.single-product #content input.button:hover, .single-product #respond input#submit:hover, .single-product #content input.button.alt:hover, .single-product #respond input#submit.alt:hover, .single-product a.button:hover, .single-product button.button:hover, .single-product input.button:hover, .single-product a.button.alt:hover, .single-product button.button.alt:hover, .single-product input.button.alt:hover, .single-product button.button.alt.disabled:hover, .single-product button.button:disabled:hover, .single-product button.button:disabled[disabled]:hover',
			'prop' => 'color',
		),
		'single_add_to_cart_button_hover_background' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
				'color_label'   => __( 'Background Hover', 'themify' ),
				'label'   => __( 'Button Background Hover', 'themify' ),
				'show_label' => false,
			),
			'selector' => '.single-product #content input.button:hover, .single-product #respond input#submit:hover, .single-product #content input.button.alt:hover, .single-product #respond input#submit.alt:hover, .single-product a.button:hover, .single-product button.button:hover, .single-product input.button:hover, .single-product a.button.alt:hover, .single-product button.button.alt:hover, .single-product input.button.alt:hover, .single-product button.button.alt.disabled:hover, .single-product button.button:disabled:hover, .single-product button.button:disabled[disabled]:hover',
			'prop' => 'background',
		),
		'single_add_to_cart_button_color' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
				'label'   => __( 'Button Color', 'themify' ),
				'show_label' => false,
			),
			'selector' => '.single-product #content input.button, .single-product #respond input#submit, .single-product #content input.button.alt, .single-product #respond input#submit.alt, .single-product a.button, .single-product button.button, .single-product input.button, .single-product a.button.alt, .single-product button.button.alt, .single-product input.button.alt, .single-product button.button.alt.disabled, .single-product button.button:disabled, .single-product button.button:disabled[disabled]',
			'prop' => 'color',
		),
		
		'single_sale_tag_background_color' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
				'color_label'   => __( 'Background Color', 'themify' ),
				'label'   => __( 'Sale Tag', 'themify' ),
				
			),
			'selector' => '.single-product span.onsale',
			'prop' => 'background',
		),
		'single_sale_tag_color' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
				'label'   => __( 'Sale Tag Color', 'themify' ),
				'show_label' => false,
			),
			'selector' => '.single-product span.onsale',
			'prop' => 'color',
		),
		'single_sale_tag_font' => array(
			'control' => array(
				'type'    => 'Themify_Font_Control',
				'label'   => __( 'Sale Tag Font', 'themify' ),
				'show_label' => false,
			),
			'selector' => '.single-product span.onsale',
			'prop' => 'font',
		),
		'end_shop_acc' => $themify_customizer->accordion_end(),
		// Accordion End   ---------------------------
		
		// Accordion Start ---------------------------
		'start_post_acc' => $themify_customizer->accordion_start( __( 'Post', 'themify' ) ),

		'post_background' => array(
			'control' => array(
				'type'  => 'Themify_Background_Control',
				'label' => __( 'Post Container', 'themify' ),
			),
			'selector' => '.post',
			'prop' => 'background',
		),

		'post_border' => array(
			'control' => array(
				'type'  => 'Themify_Border_Control',
			),
			'selector' => '.post',
			'prop' => 'border',
		),

		'post_margin' => array(
			'control' => array(
				'type'  => 'Themify_Margin_Control',
			),
			'selector' => '.post',
			'prop' => 'margin',
		),

		'post_padding' => array(
			'control' => array(
				'type'  => 'Themify_Padding_Control',
			),
			'selector' => '.post',
			'prop' => 'padding',
		),

		// Post Title .post-title

		'post_title_background' => array(
			'control' => array(
				'type'    => 'Themify_Color_Transparent_Control',
				'color_label' => __( 'Background Color', 'themify' ),
				'label'   => __( 'Post Title', 'themify' ),
			),
			'selector' => '.post-title',
			'prop' => 'background',
		),

		'post_title_border' => array(
			'control' => array(
				'type'  => 'Themify_Border_Control',
			),
			'selector' => '.post-title',
			'prop' => 'border',
		),

		'post_title_margin' => array(
			'control' => array(
				'type'  => 'Themify_Margin_Control',
			),
			'selector' => '.post-title',
			'prop' => 'margin',
		),

		'post_title_padding' => array(
			'control' => array(
				'type'  => 'Themify_Padding_Control',
			),
			'selector' => '.post-title',
			'prop' => 'padding',
		),

		'post_title_color' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
			),
			'selector' => '.post-title, .post-title a',
			'prop' => 'color',
		),

		'post_title_font' => array(
			'control' => array(
				'type'    => 'Themify_Font_Control',
			),
			'selector' => '.post-title, .post-title a',
			'prop' => 'font',
		),

		'post_title_hover_background' => array(
			'control' => array(
				'type'    => 'Themify_Color_Transparent_Control',
				'color_label' => __( 'Background Color', 'themify' ),
				'label'   => __( 'Post Title Hover', 'themify' ),
			),
			'selector' => '.post-title a:hover',
			'prop' => 'background',
		),

		'post_title_hover_border' => array(
			'control' => array(
				'type'  => 'Themify_Border_Control',
			),
			'selector' => '.post-title a:hover',
			'prop' => 'border',
		),

		'post_title_hover_margin' => array(
			'control' => array(
				'type'  => 'Themify_Margin_Control',
			),
			'selector' => '.post-title a:hover',
			'prop' => 'margin',
		),

		'post_title_hover_padding' => array(
			'control' => array(
				'type'  => 'Themify_Padding_Control',
			),
			'selector' => '.post-title a:hover',
			'prop' => 'padding',
		),
		'post_title_hover_color' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
			),
			'selector' => '.post-title a:hover',
			'prop' => 'color',
		),

		'post_title_hover_font' => array(
			'control' => array(
				'type'    => 'Themify_Text_Decoration_Control',
			),
			'selector' => '.post-title a:hover',
			'prop' => 'font',
		),

		'single_post_title_font' => array(
			'control' => array(
				'type'    => 'Themify_Font_Control',
				'label'   => __( 'Single Post Title', 'themify' ),
			),
			'selector' => '.single-post .post-title, .single-post .post-title a',
			'prop' => 'font',
		),

		'single_post_title_margin' => array(
			'control' => array(
				'type'  => 'Themify_Margin_Control',
			),
			'selector' => '.single-post .post-title',
			'prop' => 'margin',
		),

		'single_post_title_padding' => array(
			'control' => array(
				'type'  => 'Themify_Padding_Control',
			),
			'selector' => '.single-post .post-title',
			'prop' => 'padding',
		),
		'grid6_post_title_font' => array(
			'control' => array(
				'type'    => 'Themify_Font_Control',
				'label'   => __( 'Grid6 Post Title', 'themify' ),
			),
			'selector' => '.loops-wrapper.grid6 .post-title, .loops-wrapper.grid6 .post-title a',
			'prop' => 'font',
		),

		'grid6_post_title_margin' => array(
			'control' => array(
				'type'  => 'Themify_Margin_Control',
			),
			'selector' => '.loops-wrapper.grid6 .post-title',
			'prop' => 'margin',
		),

		'grid6_post_title_padding' => array(
			'control' => array(
				'type'  => 'Themify_Padding_Control',
			),
			'selector' => '.loops-wrapper.grid6 .post-title',
			'prop' => 'padding',
		),
		'grid5_post_title_font' => array(
			'control' => array(
				'type'    => 'Themify_Font_Control',
				'label'   => __( 'Grid5 Post Title', 'themify' ),
			),
			'selector' => '.loops-wrapper.grid5 .post-title, .loops-wrapper.grid5 .post-title a',
			'prop' => 'font',
		),

		'grid5_post_title_margin' => array(
			'control' => array(
				'type'  => 'Themify_Margin_Control',
			),
			'selector' => '.loops-wrapper.grid5 .post-title',
			'prop' => 'margin',
		),

		'grid5_post_title_padding' => array(
			'control' => array(
				'type'  => 'Themify_Padding_Control',
			),
			'selector' => '.loops-wrapper.grid5 .post-title',
			'prop' => 'padding',
		),
		'grid4_post_title_font' => array(
			'control' => array(
				'type'    => 'Themify_Font_Control',
				'label'   => __( 'Grid4 Post Title', 'themify' ),
			),
			'selector' => '.loops-wrapper.grid4 .post-title, .loops-wrapper.grid4 .post-title a',
			'prop' => 'font',
		),

		'grid4_post_title_margin' => array(
			'control' => array(
				'type'  => 'Themify_Margin_Control',
			),
			'selector' => '.loops-wrapper.grid4 .post-title',
			'prop' => 'margin',
		),

		'grid4_post_title_padding' => array(
			'control' => array(
				'type'  => 'Themify_Padding_Control',
			),
			'selector' => '.loops-wrapper.grid4 .post-title',
			'prop' => 'padding',
		),

		'grid3_post_title_font' => array(
			'control' => array(
				'type'    => 'Themify_Font_Control',
				'label'   => __( 'Grid3 Post Title', 'themify' ),
			),
			'selector' => '.loops-wrapper.grid3 .post-title, .loops-wrapper.grid3 .post-title a',
			'prop' => 'font',
		),

		'grid3_post_title_margin' => array(
			'control' => array(
				'type'  => 'Themify_Margin_Control',
			),
			'selector' => '.loops-wrapper.grid3 .post-title',
			'prop' => 'margin',
		),

		'grid3_post_title_padding' => array(
			'control' => array(
				'type'  => 'Themify_Padding_Control',
			),
			'selector' => '.loops-wrapper.grid3 .post-title',
			'prop' => 'padding',
		),

		'grid2_post_title_font' => array(
			'control' => array(
				'type'    => 'Themify_Font_Control',
				'label'   => __( 'Grid2 Post Title', 'themify' ),
			),
			'selector' => '.loops-wrapper.grid2 .post-title, .loops-wrapper.grid2 .post-title a',
			'prop' => 'font',
		),

		'grid2_post_title_margin' => array(
			'control' => array(
				'type'  => 'Themify_Margin_Control',
			),
			'selector' => '.loops-wrapper.grid2 .post-title',
			'prop' => 'margin',
		),

		'grid2_post_title_padding' => array(
			'control' => array(
				'type'  => 'Themify_Padding_Control',
			),
			'selector' => '.loops-wrapper.grid2 .post-title',
			'prop' => 'padding',
		),

		'grid2_thumb_post_title_font' => array(
			'control' => array(
				'type'    => 'Themify_Font_Control',
				'label'   => __( 'Grid2 Thumb Post Title', 'themify' ),
			),
			'selector' => '.loops-wrapper.grid2-thumb .post-title',
			'prop' => 'font',
		),

		'grid2_thumb_post_title_margin' => array(
			'control' => array(
				'type'  => 'Themify_Margin_Control',
			),
			'selector' => '.loops-wrapper.grid2-thumb .post-title, .loops-wrapper.grid2-thumb .post-title a',
			'prop' => 'margin',
		),

		'grid2_thumb_post_title_padding' => array(
			'control' => array(
				'type'  => 'Themify_Padding_Control',
			),
			'selector' => '.loops-wrapper.grid2-thumb .post-title',
			'prop' => 'padding',
		),

		'list_thumb_post_title_font' => array(
			'control' => array(
				'type'    => 'Themify_Font_Control',
				'label'   => __( 'List Thumb Post Title', 'themify' ),
			),
			'selector' => '.loops-wrapper.list-thumb-image .post-title, .loops-wrapper.list-thumb-image .post-title a',
			'prop' => 'font',
		),

		'list_thumb_post_title_margin' => array(
			'control' => array(
				'type'  => 'Themify_Margin_Control',
			),
			'selector' => '.loops-wrapper.list-thumb-image .post-title',
			'prop' => 'margin',
		),

		'list_thumb_post_title_padding' => array(
			'control' => array(
				'type'  => 'Themify_Padding_Control',
			),
			'selector' => '.loops-wrapper.list-thumb-image .post-title',
			'prop' => 'padding',
		),

		'post_meta_background' => array(
			'control' => array(
				'type'    => 'Themify_Color_Transparent_Control',
				'color_label' => __( 'Background Color', 'themify' ),
				'label'   => __( 'Post Meta', 'themify' ),
			),
			'selector' => '.post-meta',
			'prop' => 'background',
		),

		'post_meta_border' => array(
			'control' => array(
				'type'    => 'Themify_Border_Control',
			),
			'selector' => '.post-meta',
			'prop' => 'border',
		),

		'post_meta_margin' => array(
			'control' => array(
				'type'  => 'Themify_Margin_Control',
			),
			'selector' => '.post-meta',
			'prop' => 'margin',
		),

		'post_meta_padding' => array(
			'control' => array(
				'type'  => 'Themify_Padding_Control',
			),
			'selector' => '.post-meta',
			'prop' => 'padding',
		),

		'post_meta_color' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
			),
			'selector' => '.post-meta',
			'prop' => 'color',
		),

		'post_meta_font' => array(
			'control' => array(
				'type'    => 'Themify_Font_Control',
			),
			'selector' => '.post-meta',
			'prop' => 'font',
		),

		'post_meta_link_background' => array(
			'control' => array(
				'type'    => 'Themify_Color_Transparent_Control',
				'color_label' => __( 'Background Color', 'themify' ),
				'label'   => __( 'Post Meta Link', 'themify' ),
			),
			'selector' => '.post-meta a',
			'prop' => 'background',
		),

		'post_meta_link_border' => array(
			'control' => array(
				'type'    => 'Themify_Border_Control',
			),
			'selector' => '.post-meta a',
			'prop' => 'border',
		),

		'post_meta_link_margin' => array(
			'control' => array(
				'type'  => 'Themify_Margin_Control',
			),
			'selector' => '.post-meta a',
			'prop' => 'margin',
		),

		'post_meta_link_padding' => array(
			'control' => array(
				'type'  => 'Themify_Padding_Control',
			),
			'selector' => '.post-meta a',
			'prop' => 'padding',
		),

		'post_meta_link_color' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
			),
			'selector' => '.post-meta a',
			'prop' => 'color',
		),

		'post_meta_link_font' => array(
			'control' => array(
				'type'    => 'Themify_Text_Decoration_Control',
			),
			'selector' => '.post-meta a',
			'prop' => 'font',
		),

		'post_meta_link_hover_background' => array(
			'control' => array(
				'type'    => 'Themify_Color_Transparent_Control',
				'color_label' => __( 'Background Color', 'themify' ),
				'label'   => __( 'Post Meta Link Hover', 'themify' ),
			),
			'selector' => '.post-meta a:hover',
			'prop' => 'background',
		),

		'post_meta_link_hover_border' => array(
			'control' => array(
				'type'    => 'Themify_Border_Control',
			),
			'selector' => '.post-meta a:hover',
			'prop' => 'border',
		),

		'post_meta_link_hover_color' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
			),
			'selector' => '.post-meta a:hover',
			'prop' => 'color',
		),

		'post_meta_link_hover_font' => array(
			'control' => array(
				'type'    => 'Themify_Font_Control',
			),
			'selector' => '.post-meta a:hover',
			'prop' => 'font',
		),

		// Post Date .post-date

		'post_date_background' => array(
			'control' => array(
				'type'    => 'Themify_Background_Control',
				'label'   => __( 'Post Date', 'themify' ),
			),
			'selector' => '.post-date',
			'prop' => 'background',
		),

		'post_date_width' => array(
			'control' => array(
				'type'  => 'Themify_Width_Control',
			),
			'selector' => '.post-date',
			'prop' => 'width',
		),

		'post_date_height' => array(
			'control' => array(
				'type'  => 'Themify_Height_Control',
			),
			'selector' => '.post-date',
			'prop' => 'height',
		),

		'post_date_border' => array(
			'control' => array(
				'type'    => 'Themify_Border_Control',
			),
			'selector' => '.post-date',
			'prop' => 'border',
		),

		'post_date_margin' => array(
			'control' => array(
				'type'  => 'Themify_Margin_Control',
			),
			'selector' => '.post-date',
			'prop' => 'margin',
		),

		'post_date_padding' => array(
			'control' => array(
				'type'  => 'Themify_Padding_Control',
			),
			'selector' => '.post-date',
			'prop' => 'padding',
		),

		'post_date_color' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
			),
			'selector' => '.post-date',
			'prop' => 'color',
		),

		'post_date_font' => array(
			'control' => array(
				'type'    => 'Themify_Font_Control',
			),
			'selector' => '.post-date',
			'prop' => 'font',
		),

		// More Link .more-link

		'more_link_background' => array(
			'control' => array(
				'type'    => 'Themify_Background_Control',
				'label'   => __( 'More Link', 'themify' ),
			),
			'selector' => '.more-link',
			'prop' => 'background',
		),

		'more_link_border' => array(
			'control' => array(
				'type'    => 'Themify_Border_Control',
			),
			'selector' => '.more-link',
			'prop' => 'border',
		),

		'more_link_margin' => array(
			'control' => array(
				'type'  => 'Themify_Margin_Control',
			),
			'selector' => '.more-link',
			'prop' => 'margin',
		),

		'more_link_padding' => array(
			'control' => array(
				'type'  => 'Themify_Padding_Control',
			),
			'selector' => '.more-link',
			'prop' => 'padding',
		),

		'more_link_color' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
			),
			'selector' => '.more-link',
			'prop' => 'color',
		),

		'more_link_font' => array(
			'control' => array(
				'type'    => 'Themify_Font_Control',
			),
			'selector' => '.more-link',
			'prop' => 'font',
		),

		// More Link Hover .more-link:hover

		'more_link_hover_background' => array(
			'control' => array(
				'type'    => 'Themify_Background_Control',
				'label'   => __( 'More Link Hover', 'themify' ),
			),
			'selector' => '.more-link:hover',
			'prop' => 'background',
		),

		'more_link_hover_border' => array(
			'control' => array(
				'type'    => 'Themify_Border_Control',
			),
			'selector' => '.more-link:hover',
			'prop' => 'border',
		),

		'more_link_hover_color' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
			),
			'selector' => '.more-link:hover',
			'prop' => 'color',
		),

		'more_link_hover_font' => array(
			'control' => array(
				'type'    => 'Themify_Text_Decoration_Control',
			),
			'selector' => '.more-link:hover',
			'prop' => 'font',
		),

		// Post Nav .post-nav

		'post_nav_background' => array(
			'control' => array(
				'type'  => 'Themify_Background_Control',
				'label' => __( 'Post Nav (Next/Prev Post Link)', 'themify' ),
			),
			'selector' => '.post-nav',
			'prop' => 'background',
		),

		'post_nav_border' => array(
			'control' => array(
				'type'  => 'Themify_Border_Control',
			),
			'selector' => '.post-nav',
			'prop' => 'border',
		),

		'post_nav_margin' => array(
			'control' => array(
				'type'  => 'Themify_Margin_Control',
			),
			'selector' => '.post-nav',
			'prop' => 'margin',
		),

		'post_nav_padding' => array(
			'control' => array(
				'type'  => 'Themify_Padding_Control',
			),
			'selector' => '.post-nav',
			'prop' => 'padding',
		),

		// Post Nav Link .post-nav a

		'post_nav_font' => array(
			'control' => array(
				'type'    => 'Themify_Font_Control',
				'label'   => __( 'Post Nav Link', 'themify' ),
			),
			'selector' => '.post-nav a',
			'prop' => 'font',
		),

		'post_nav_link_color' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
			),
			'selector' => '.post-nav a',
			'prop' => 'color',
		),

		'post_nav_hover_font' => array(
			'control' => array(
				'type'    => 'Themify_Font_Control',
				'label'   => __( 'Post Nav Link Hover', 'themify' ),
			),
			'selector' => '.post-nav a:hover',
			'prop' => 'font',
		),
		'post_nav_link_hover_color' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
			),
			'selector' => '.post-nav a:hover',
			'prop' => 'color',
		),

		// Next/Prev Link Icon .post-nav .arrow

		'post_nav_link_icon_background' => array(
			'control' => array(
				'type'    => 'Themify_Color_Transparent_Control',
				'color_label' => __( 'Background Color', 'themify' ),
				'label'   => __( 'Post Nav Link Icon', 'themify' ),
			),
			'selector' => '.post-nav .arrow',
			'prop' => 'background',
		),

		'post_nav_link_icon_color' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
			),
			'selector' => '.post-nav .arrow',
			'prop' => 'color',
		),

		'post_nav_link_icon_hover_color' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
				'label'   => __( 'Post Nav Link Icon Hover', 'themify' ),
			),
			'selector' => '.post-nav a:hover span',
			'prop' => 'color',
		),

		'post_nav_link_icon_hover_background' => array(
			'control' => array(
				'type'    => 'Themify_Color_Transparent_Control',
				'color_label' => __( 'Background Color', 'themify' ),
			),
			'selector' => '.post-nav a:hover span',
			'prop' => 'background',
		),

		'end_post_acc' => $themify_customizer->accordion_end(),
		// Accordion End   ---------------------------

		// Accordion Start ---------------------------
		'start_page_title_acc' => $themify_customizer->accordion_start( __( 'Page Title', 'themify' ) ),

		// Page Title .page-title


		'page_title_background' => array(
			'control' => array(
				'type'    => 'Themify_Background_Control',
				'label'   => __( 'Page Title', 'themify' ),
			),
			'selector' => '.page-title, .sidebar-none .page-title, .sidebar-none.single .page-title',
			'prop' => 'background',
		),

		'page_title_border' => array(
			'control' => array(
				'type'    => 'Themify_Border_Control',
			),
			'selector' => '.page-title, .sidebar-none .page-title, .sidebar-none.single .page-title',
			'prop' => 'border',
		),


		'page_title_margin' => array(
			'control' => array(
				'type'  => 'Themify_Margin_Control',
			),
			'selector' => '.page-title, .sidebar-none .page-title, .sidebar-none.single .page-title',
			'prop' => 'margin',
		),

		'page_title_padding' => array(
			'control' => array(
				'type'  => 'Themify_Padding_Control',
			),
			'selector' => '.page-title, .sidebar-none .page-title, .sidebar-none.single .page-title',
			'prop' => 'padding',
		),
		
		'page_title_color' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
			),
			'selector' => '.page-title, .sidebar-none .page-title, .sidebar-none.single .page-title',
			'prop' => 'color',
		),

		'page_title_font' => array(
			'control' => array(
				'type'    => 'Themify_Font_Control',
			),
			'selector' => '.page-title, .sidebar-none .page-title, .sidebar-none.single .page-title',
			'prop' => 'font',
		),

		'end_page_title_acc' => $themify_customizer->accordion_end(),
		// Accordion End   ---------------------------

		// Accordion Start ---------------------------
		'start_module_title_acc' => $themify_customizer->accordion_start( __( 'Module Title', 'themify' ) ),

		// Module Title .module-title

		'module_title_background' => array(
			'control' => array(
				'type'    => 'Themify_Background_Control',
				'label'   => __( 'Module Title', 'themify' ),
			),
			'selector' => '.module-title',
			'prop' => 'background',
		),

		'module_title_border' => array(
			'control' => array(
				'type'    => 'Themify_Border_Control',
			),
			'selector' => '.module-title',
			'prop' => 'border',
		),

		'module_title_margin' => array(
			'control' => array(
				'type'  => 'Themify_Margin_Control',
			),
			'selector' => '.module-title',
			'prop' => 'margin',
		),

		'module_title_padding' => array(
			'control' => array(
				'type'  => 'Themify_Padding_Control',
			),
			'selector' => '.module-title',
			'prop' => 'padding',
		),

		'module_title_color' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
			),
			'selector' => '.module-title',
			'prop' => 'color',
		),

		'module_title_font' => array(
			'control' => array(
				'type'    => 'Themify_Font_Control',
			),
			'selector' => '.module-title',
			'prop' => 'font',
		),

		'end_module_title_acc' => $themify_customizer->accordion_end(),
		// Accordion End   ---------------------------

		// Accordion Start ---------------------------
		'start_sidebar_acc' => $themify_customizer->accordion_start( __( 'Sidebar', 'themify' ) ),

		// Sidebar Font #sidebar

		'sidebar_font' => array(
			'control' => array(
				'type'    => 'Themify_Font_Control',
				'label'   => __( 'Sidebar Font', 'themify' ),
			),
			'selector' => '#sidebar',
			'prop' => 'font',
		),

		'sidebar_color' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
			),
			'selector' => '#sidebar',
			'prop' => 'color',
		),

		// Sidebar Link #sidebar a

		'sidebar_link_font' => array(
			'control' => array(
				'type'    => 'Themify_Font_Control',
				'label'   => __( 'Sidebar Link', 'themify' ),
			),
			'selector' => '#sidebar a',
			'prop' => 'font',
		),

		'sidebar_link_color' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
			),
			'selector' => '#sidebar a',
			'prop' => 'color',
		),

		// Sidebar Link Hover #sidebar a:hover

		'sidebar_link_hover_font' => array(
			'control' => array(
				'type'    => 'Themify_Font_Control',
				'label'   => __( 'Sidebar Link Hover', 'themify' ),
			),
			'selector' => '#sidebar a:hover',
			'prop' => 'font',
		),
		'sidebar_link_hover_color' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
			),
			'selector' => '#sidebar a:hover',
			'prop' => 'color',
		),


		// Sidebar Widget #sidebar .widget

		'sidebar_widget_background' => array(
			'control' => array(
				'type'    => 'Themify_Background_Control',
				'label'   => __( 'Sidebar Widget Container', 'themify' ),
			),
			'selector' => '#sidebar .widget',
			'prop' => 'background',
		),

		'sidebar_widget_border' => array(
			'control' => array(
				'type'    => 'Themify_Border_Control',
			),
			'selector' => '#sidebar .widget',
			'prop' => 'border',
		),

		'sidebar_widget_margin' => array(
			'control' => array(
				'type'  => 'Themify_Margin_Control',
			),
			'selector' => '#sidebar .widget',
			'prop' => 'margin',
		),

		'sidebar_widget_padding' => array(
			'control' => array(
				'type'  => 'Themify_Padding_Control',
			),
			'selector' => '#sidebar .widget',
			'prop' => 'padding',
		),

		'sidebar_widget_color' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
			),
			'selector' => '#sidebar .widget',
			'prop' => 'color',
		),

		// Sidebar Widget Title #sidebar .widgettitle


		'sidebar_widget_title_background' => array(
			'control' => array(
				'type'    => 'Themify_Color_Transparent_Control',
				'color_label' => __( 'Background Color', 'themify' ),
				'label'   => __( 'Sidebar Widget Title', 'themify' ),
			),
			'selector' => '#sidebar .widgettitle',
			'prop' => 'background',
		),

		'sidebar_widget_title_border' => array(
			'control' => array(
				'type'    => 'Themify_Border_Control',
			),
			'selector' => '#sidebar .widgettitle',
			'prop' => 'border',
		),

		'sidebar_widget_title_margin' => array(
			'control' => array(
				'type'  => 'Themify_Margin_Control',
			),
			'selector' => '#sidebar .widgettitle',
			'prop' => 'margin',
		),

		'sidebar_widget_title_padding' => array(
			'control' => array(
				'type'  => 'Themify_Padding_Control',
			),
			'selector' => '#sidebar .widgettitle',
			'prop' => 'padding',
		),

		'sidebar_widget_title_color' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
			),
			'selector' => '#sidebar .widgettitle',
			'prop' => 'color',
		),

		'sidebar_widget_title_font' => array(
			'control' => array(
				'type'    => 'Themify_Font_Control',
			),
			'selector' => '#sidebar .widgettitle',
			'prop' => 'font',
		),
		// Sidebar Widget List Styling #sidebar .widget li

		'sidebar_widget_list_background' => array(
			'control' => array(
				'type'    => 'Themify_Color_Transparent_Control',
				'label'   => __( 'Sidebar Widget List Styling', 'themify' ),
				'color_label' => __( 'Background Color', 'themify' ),
			),
			'selector' => '#sidebar .widget li',
			'prop' => 'background',
		),

		'sidebar_widget_list_border' => array(
			'control' => array(
				'type'    => 'Themify_Border_Control',
			),
			'selector' => '#sidebar .widget li',
			'prop' => 'border',
		),

		'sidebar_widget_list_margin' => array(
			'control' => array(
				'type'  => 'Themify_Margin_Control',
			),
			'selector' => '#sidebar .widget li',
			'prop' => 'margin',
		),

		'sidebar_widget_list_padding' => array(
			'control' => array(
				'type'  => 'Themify_Padding_Control',
			),
			'selector' => '#sidebar .widget li',
			'prop' => 'padding',
		),

		'end_sidebar_acc' => $themify_customizer->accordion_end(),
		// Accordion End   ---------------------------

		// Accordion Start ---------------------------
		'start_footer_acc' => $themify_customizer->accordion_start( __( 'Footer', 'themify' ) ),

		// Footer Wrap #footerwrap

		'footerwrap_background' => array(
			'control' => array(
				'type'    => 'Themify_Background_Control',
				'label'   => __( 'Footer Wrap', 'themify' ),
			),
			'selector' => '#footerwrap',
			'prop' => 'background',
		),

		'footer-logo_image' => array(
			'setting' => array(
				'default' => '',
			),
			'control' => array(
				'type'    => 'Themify_Logo_Control',
				'label'   => __( 'Footer Logo', 'themify' ),
			),
			'selector' => '#footer-logo, #footer #footer-logo a',
			'prop' => 'logo',
		),

		'footerwrap_border' => array(
			'control' => array(
				'type'    => 'Themify_Border_Control',
			),
			'selector' => '#footerwrap',
			'prop' => 'border',
		),

		'footerwrap_margin' => array(
			'control' => array(
				'type'  => 'Themify_Margin_Control',
			),
			'selector' => '#footerwrap',
			'prop' => 'margin',
		),

		'footerwrap_padding' => array(
			'control' => array(
				'type'  => 'Themify_Padding_Control',
			),
			'selector' => '#footerwrap',
			'prop' => 'padding',
		),

		// Footer Inner #footer

		'footerinner_background' => array(
			'control' => array(
				'type'    => 'Themify_Background_Control',
				'label'   => __( 'Footer Inner', 'themify' ),
			),
			'selector' => '#footer',
			'prop' => 'background',
		),

		'footerinner_border' => array(
			'control' => array(
				'type'    => 'Themify_Border_Control',
			),
			'selector' => '#footer',
			'prop' => 'border',
		),

		'footerinner_margin' => array(
			'control' => array(
				'type'  => 'Themify_Margin_Control',
			),
			'selector' => '#footer',
			'prop' => 'margin',
		),

		'footerinner_padding' => array(
			'control' => array(
				'type'  => 'Themify_Padding_Control',
			),
			'selector' => '#footer',
			'prop' => 'padding',
		),

		// Footer Font #footer

		'footer_font' => array(
			'control' => array(
				'type'    => 'Themify_Font_Control',
				'label'   => __( 'Footer Font', 'themify' ),
			),
			'selector' => '#footer',
			'prop' => 'font',
		),

		'footer_color' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
			),
			'selector' => '#footer',
			'prop' => 'color',
		),

		// Footer Link #footer a

		'footer_link_font' => array(
			'control' => array(
				'type'    => 'Themify_Font_Control',
				'label'   => __( 'Footer Link', 'themify' ),
			),
			'selector' => '#footer a',
			'prop' => 'font',
		),
		'footer_link_color' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
			),
			'selector' => '#footer a',
			'prop' => 'color',
		),


		// Footer Link #footer a:hover


		'footer_link_hover_font' => array(
			'control' => array(
				'type'    => 'Themify_Font_Control',
				'label'   => __( 'Footer Link Hover', 'themify' ),
			),
			'selector' => '#footer a:hover',
			'prop' => 'font',
		),

		'footer_link_hover_color' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
			),
			'selector' => '#footer a:hover',
			'prop' => 'color',
		),

		'footer_nav_background' => array(
			'control' => array(
				'type'    => 'Themify_Background_Control',
				'label'   => __( 'Footer Menu Container', 'themify' ),
			),
			'selector' => '#footer-nav',
			'prop' => 'background',
		),

		'footer_nav_position' => array(
			'control' => array(
				'type'    => 'Themify_Position_Control',
				'label'   => __( 'Menu Container Position', 'themify' ),
				'show_label' => false,
			),
			'selector' => '#footer-nav',
			'prop' => 'position',
		),

		'footer_nav_width' => array(
			'control' => array(
				'type'  => 'Themify_Width_Control',
				'label' => __( 'Width', 'themify' ),
				'show_label' => false,
			),
			'selector' => '#footer-nav',
			'prop' => 'width',
		),

		'footer_nav_height' => array(
			'control' => array(
				'type'  => 'Themify_Height_Control',
				'label' => __( 'Height', 'themify' ),
				'show_label' => false,
			),
			'selector' => '#footer-nav',
			'prop' => 'height',
		),

		'footer_nav_border' => array(
			'control' => array(
				'type'    => 'Themify_Border_Control',
				'label'   => __( 'Menu Container Border', 'themify' ),
				'show_label' => false,
			),
			'selector' => '#footer-nav',
			'prop' => 'border',
		),

		'footer_nav_margin' => array(
			'control' => array(
				'type'    => 'Themify_Margin_Control',
				'label'   => __( 'Menu Container Margin', 'themify' ),
				'show_label' => false,
			),
			'selector' => '#footer-nav',
			'prop' => 'margin',
		),

		'footer_nav_padding' => array(
			'control' => array(
				'type'    => 'Themify_Padding_Control',
				'label'   => __( 'Menu Container Padding', 'themify' ),
				'show_label' => false,
			),
			'selector' => '#footer-nav',
			'prop' => 'padding',
		),

		'footer_nav_link_background' => array(
			'control' => array(
				'type'    => 'Themify_Color_Transparent_Control',
				'label'   => __( 'Footer Menu Link', 'themify' ),
				'color_label' => __( 'Background Color', 'themify' ),
			),
			'selector' => '#footer-nav a',
			'prop' => 'background',
		),

		'footer_nav_link_border' => array(
			'control' => array(
				'type'    => 'Themify_Border_Control',
				'label'   => __( 'Menu Link Border', 'themify' ),
				'show_label' => false,
			),
			'selector' => '#footer-nav a',
			'prop' => 'border',
		),

		'footer_nav_link_margin' => array(
			'control' => array(
				'type'    => 'Themify_Margin_Control',
				'label'   => __( 'Menu Link Margin', 'themify' ),
				'show_label' => false,
			),
			'selector' => '#footer-nav a',
			'prop' => 'margin',
		),

		'footer_nav_link_padding' => array(
			'control' => array(
				'type'    => 'Themify_Padding_Control',
				'label'   => __( 'Menu Link Padding', 'themify' ),
				'show_label' => false,
			),
			'selector' => '#footer-nav a',
			'prop' => 'padding',
		),

		'footer_nav_link_color' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
				'label'   => __( 'Menu Link Color', 'themify' ),
				'show_label' => false,
			),
			'selector' => '#footer-nav a',
			'prop' => 'color',
		),

		'footer_nav_link_font' => array(
			'control' => array(
				'type'    => 'Themify_Font_Control',
				'label'   => __( 'Menu Link Font', 'themify' ),
				'show_label' => false,
			),
			'selector' => '#footer-nav a',
			'prop' => 'font',
		),
		
		'footer_nav_link_hover_background' => array(
			'control' => array(
				'type'    => 'Themify_Color_Transparent_Control',
				'label'   => __( 'Footer Menu Link Hover', 'themify' ),
				'color_label' => __( 'Background Color', 'themify' ),
			),
			'selector' => '#footer-nav a:hover',
			'prop' => 'background',
		),

		'footer_nav_link_hover_color' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
				'label'   => __( 'Menu Link Hover Color', 'themify' ),
				'show_label' => false,
			),
			'selector' => '#footer-nav a:hover, #footer-nav li:hover > a',
			'prop' => 'color',
		),

		'footer_nav_link_active_background' => array(
			'control' => array(
				'type'    => 'Themify_Color_Transparent_Control',
				'label'   => __( 'Footer Menu Active Link', 'themify' ),
				'color_label' => __( 'Background Color', 'themify' ),
			),
			'selector' => '#footer-nav .current_page_item a, #footer-nav .current-menu-item a',
			'prop' => 'background',
		),

		'footer_nav_link_active_color' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
				'label'   => __( 'Menu Active Link Color', 'themify' ),
				'show_label' => false,
			),
			'selector' => '#footer-nav .current_page_item a, #footer-nav .current-menu-item a',
			'prop' => 'color',
		),

		'footer_nav_link_active_hover_background' => array(
			'control' => array(
				'type'    => 'Themify_Color_Transparent_Control',
				'label'   => __( 'Footer Menu Active Link Hover', 'themify' ),
				'color_label' => __( 'Background Color', 'themify' ),
			),
			'selector' => '#footer-nav .current_page_item a:hover, #footer-nav .current-menu-item a:hover',
		),

		'footer_nav_link_active_hover_color' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
				'label'   => __( 'Menu Active Link Hover Color', 'themify' ),
				'show_label' => false,
			),
			'selector' => '#footer-nav .current_page_item a:hover, #footer-nav .current-menu-item a:hover',
			'prop' => 'color',
		),


		// Footer Widget .footer-widgets .widget

		'footer_widget_background' => array(
			'control' => array(
				'type'    => 'Themify_Background_Control',
				'label'   => __( 'Footer Widget Container', 'themify' ),
			),
			'selector' => '.footer-widgets .widget',
			'prop' => 'background',
		),

		'footer_widget_border' => array(
			'control' => array(
				'type'    => 'Themify_Border_Control',
			),
			'selector' => '.footer-widgets .widget',
			'prop' => 'border',
		),

		'footer_widget_margin' => array(
			'control' => array(
				'type'  => 'Themify_Margin_Control',
			),
			'selector' => '.footer-widgets .widget',
			'prop' => 'margin',
		),

		'footer_widget_padding' => array(
			'control' => array(
				'type'  => 'Themify_Padding_Control',
			),
			'selector' => '.footer-widgets .widget',
			'prop' => 'padding',
		),

		'footer_widget_color' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
			),
			'selector' => '.footer-widgets .widget',
			'prop' => 'color',
		),

		'footer_widget_font' => array(
			'control' => array(
				'type'    => 'Themify_Font_Control',
			),
			'selector' => '.footer-widgets',
			'prop' => 'font',
		),

		// Footer Widget Title .footer-widgets .widgettitle

		'footer_widget_title_background' => array(
			'control' => array(
				'type'    => 'Themify_Color_Transparent_Control',
				'color_label' => __( 'Background Color', 'themify' ),
				'label'   => __( 'Footer Widget Title', 'themify' ),
			),
			'selector' => '.footer-widgets .widgettitle',
			'prop' => 'background',
		),

		'footer_widget_title_border' => array(
			'control' => array(
				'type'    => 'Themify_Border_Control',
			),
			'selector' => '.footer-widgets .widgettitle',
			'prop' => 'border',
		),

		'footer_widget_title_margin' => array(
			'control' => array(
				'type'  => 'Themify_Margin_Control',
			),
			'selector' => '.footer-widgets .widgettitle',
			'prop' => 'margin',
		),

		'footer_widget_title_padding' => array(
			'control' => array(
				'type'  => 'Themify_Padding_Control',
			),
			'selector' => '.footer-widgets .widgettitle',
			'prop' => 'padding',
		),
		
		'footer_widget_title_color' => array(
			'control' => array(
				'type'    => 'Themify_Color_Control',
			),
			'selector' => '.footer-widgets .widgettitle',
			'prop' => 'color',
		),

		'footer_widget_title_font' => array(
			'control' => array(
				'type'    => 'Themify_Font_Control',
			),
			'selector' => '.footer-widgets .widgettitle',
			'prop' => 'font',
		),

		// Footer Widget List Styling .footer-widgets .widget li

		'footer_widget_list_background' => array(
			'control' => array(
				'type'    => 'Themify_Color_Transparent_Control',
				'label'   => __( 'Footer Widget List Styling', 'themify' ),
				'color_label' => __( 'Background Color', 'themify' ),
			),
			'selector' => '.footer-widgets .widget li',
			'prop' => 'background',
		),

		'footer_widget_list_border' => array(
			'control' => array(
				'type'    => 'Themify_Border_Control',
			),
			'selector' => '.footer-widgets .widget li',
			'prop' => 'border',
		),

		'footer_widget_list_margin' => array(
			'control' => array(
				'type'  => 'Themify_Margin_Control',
			),
			'selector' => '.footer-widgets .widget li',
			'prop' => 'margin',
		),

		'footer_widget_list_padding' => array(
			'control' => array(
				'type'  => 'Themify_Padding_Control',
			),
			'selector' => '.footer-widgets .widget li',
			'prop' => 'padding',
		),

		'end_footer_acc' => $themify_customizer->accordion_end(),
		// Accordion End   ---------------------------*/

		// Accordion Start ---------------------------
		'start_customcss_acc' => $themify_customizer->accordion_start( __( 'Custom CSS', 'themify' ) ),

		// This element is not CSS, but markup written by themify_custom_css()
		'customcss' => array(
			'control' => array(
				'type'    => 'Themify_CustomCSS_Control',
				'label'   => __( 'Custom CSS', 'themify' ),
				'show_label' => false,
			),
			'selector' => 'customcss',
			'prop' => 'customcss',
		),

		'end_customcss_acc' => $themify_customizer->accordion_end(),
		// Accordion End   ---------------------------

	);
	return $args;
}
add_filter( 'themify_customizer_settings', 'themify_theme_customizer_definition' );
