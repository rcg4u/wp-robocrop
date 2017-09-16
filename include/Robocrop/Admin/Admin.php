<?php

namespace Robocrop\Admin;
use Robocrop\Core;


class Admin extends Core\Module {

	private $core;

	private $media_helper;

	/**
	 *	Private constructor
	 */
	protected function __construct() {

		$this->core			= Core\Core::instance();
		$this->media_helper	= Core\MediaHelper::instance();

		add_action( 'admin_init', array( $this , 'admin_init' ) );

		add_action( 'wp_enqueue_media',			array( $this, 'wp_enqueue_media' ) );
		add_action( 'print_media_templates',	array( $this, 'print_media_templates' ) );
		add_filter( 'plupload_init',			array( $this, 'plupload_init' ) , 20);

		add_filter( 'wp_prepare_attachment_for_js' , array( $this , 'wp_prepare_attachment_for_js' ),10,3);

	}


	/**
	 *	@action 'admin_init'
	 */
	function admin_init() {
		$version = ROBOCROP_VERSION;
		$suffix =  defined('SCRIPT_DEBUG') && SCRIPT_DEBUG  ? '.min' : '';

		wp_register_script( 'wp-robocrop' , $this->get_asset_url( 'js/admin/wp-robocrop'.$suffix.'.js' ) , array( 'jquery', 'media-grid' ) , $version );
		wp_localize_script( 'wp-robocrop' , 'robocrop' , array(
			'image_ratios' => $this->media_helper->get_image_ratios(),
			'image_sizes'  => $this->media_helper->get_image_sizes(),
			'l10n' => array(
				'EditImageSizes'	=> __( 'Edit Image sizes','wp-robocrop' ),
				'RobocropImage'		=> __( 'Robo Crop Image','wp-robocrop' ),
				'Okay'				=> __( 'Okay', 'wp-robocrop' ),
				'SaveChanges'		=> __( 'Save Changes', 'wp-robocrop' ),
				'Close'				=> __( 'Close', 'wp-robocrop' ),
				'Reset'				=> __( 'Reset', 'wp-robocrop' ),
				'AttachmentDetails'	=> __( 'Attachment Details', 'wp-robocrop' ),
				'SetFocusPoint'		=> __( 'Set Focus Point', 'wp-robocrop' ),
				'FocusPointInstructions'
									=> __( 'Click on the most important spot of the image.', 'wp-robocrop' ),
				'CancelUpload'		=> __( 'Cancel Upload', 'wp-robocrop' ),
			),
			'options'		=> array(
				'ask_for_focuspoint'		=> !! get_option( 'robocrop_ask_for_focuspoint' ),
			),
			'version'		=> ROBOCROP_VERSION,
		) );

		wp_register_style( 'wp-robocrop-admin' , $this->get_asset_url( 'css/admin/admin'.$suffix.'.css' ) , array( ) , $version );
	}



	/**
	 *	@action 'wp_enqueue_media'
	 */
	function wp_enqueue_media() {
		if ( ! did_action('wp_enqueue_media') ) 
			wp_enqueue_media();
		wp_enqueue_script( 'wp-robocrop' );

		wp_enqueue_style( 'wp-robocrop-admin' );
	}

	/**
	 *	@action 'print_media_templates'
	 */
	function print_media_templates() {
		// cropping tool
		$rp = ROBOCROP_DIRECTORY . 'include' . DIRECTORY_SEPARATOR . '/template/{,*/,*/*/,*/*/*/}*.php';
		foreach ( glob( $rp, GLOB_BRACE ) as $template_file ) {	
			include $template_file;
		}
/*
		include $this->get_asset_path( 'include/template/robocrop-tpl.php' );
		include $this->get_asset_path( 'include/template/robocrop-modal.php' );
		include $this->get_asset_path( 'include/template/robocrop-select-tpl.php' );
		include $this->get_asset_path( 'include/template/robocrop-select-item-tpl.php' );

		// focus point editor
		include $this->get_asset_path( 'include/template/robocrop-ask-focuspoint-tpl.php' );
		include $this->get_asset_path( 'include/template/robocrop-focuspoint-tpl.php' );
*/
	}

	/**
	 *	Enable client side image resize.
	 *
	 *	@filter 'plupload_init'
	 */
	function plupload_init( $params ) {
		// get biggest possible image
		$sizes = $this->media_helper->get_image_sizes();
		$largest = array( 'width'=>0 , 'height'=>0 );
		foreach ( $sizes as $size ) {
			$largest['width'] = max($size['width'],$largest['width']);
			$largest['height'] = max($size['height'],$largest['height']);
		}
		$params['resize'] = array(
			'enabled' => true,
			'width'		=> intval($largest['width']),
			'height'	=> intval($largest['height']),
			'quality'	=> 90
		);
		return $params;
	}



	/**
	 *	Add our cropdata to js image data
	 *
	 *	@filter 'wp_prepare_attachment_for_js'
	 */
	function wp_prepare_attachment_for_js( $response, $attachment, $meta ) {
		if ( isset($response['sizes'],$meta['sizes'] ) ) {
			foreach ( $meta['sizes'] as $size => $sizedata ) {
				if ( isset( $sizedata['cropdata'] ) ) {
					$response['sizes'][$size]['cropdata'] = array_map('intval',$sizedata['cropdata']);
				}
			}
		}

		if ( isset( $meta['focuspoint'] ) ) {
			$response['focuspoint'] = $meta['focuspoint'];
		} else {
			$response['focuspoint'] = array( 'x' => 0, 'y' => 0 );
		}
		return $response;
	}

}

