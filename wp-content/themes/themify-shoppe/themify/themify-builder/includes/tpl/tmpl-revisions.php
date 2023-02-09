<?php

defined( 'ABSPATH' ) || exit;
$time=current_time( 'timestamp');
if ( ! empty( $revisions )):?>
	<ul class="tb_revision_lists">
	    <?php foreach( $revisions as $revision ):?>
	    <?php 
		if(!Themify_Builder_Revisions::check_has_builder( $revision->ID )){
			continue;
		}
		$rev_comment = get_metadata( 'post', $revision->ID, '_builder_custom_rev_comment', true );
		$is_deleteable =$post_id !== $revision->ID && $can_edit_post && ! wp_is_post_autosave( $revision )  ;
		if($is_deleteable===false && empty($rev_comment)){
		    continue;
		}
		$date = date_i18n( __( 'd/m/Y @ h:i:s a', 'themify' ), strtotime( $revision->post_modified ) );
		$time_diff = human_time_diff( strtotime( $revision->post_modified ),$time );
	    ?>
	    <li>
		<?php if($is_deleteable===true):?>
		    <a href="#" title="<?php esc_attr_e( 'Click to restore this revision', 'themify' )?>" class="js-builder-restore-revision-btn" data-id="<?php echo $revision->ID ?>"><?php echo sprintf( '%s (%s ago)', $date, $time_diff ); ?></a>
		<?php endif;?>

		<?php if(! empty( $rev_comment )):?>
		    <small>(<?php echo $rev_comment?>)</small>
		<?php endif;?>
		<?php if($is_deleteable===true):?>
		    <a href="#" title="<?php esc_attr_e( 'Delete this revision', 'themify' )?>" class="js-builder-delete-revision-btn tf_close" data-id="<?php echo $revision->ID ?>"></a>
		<?php endif;?>
	      </li>
	    <?php endforeach?>
	</ul>
<?php else:?>
    <p><?php _e( 'No Revision found.', 'themify' ) ?></p>
<?php endif;
