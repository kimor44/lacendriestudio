<?php
if ( !defined( 'ABSPATH' ) ) exit;
?>

<div class="main-block-wb">
	<div class="main-part-wrapper-wb">
		<div class="content-main-wb">
<?php
	global $plugin_page;
 	switch ( $plugin_page ) {
		case 'wbk-appearance':
			WBK_Renderer::load_template( 'backend/appearance_page_content', array(), true );
			break;
		case 'wbk-schedule':
			WBK_Renderer::load_template( 'backend/schedule_tools_content', array(), true );
			break;
	}

 ?>
		</div><!-- /.content-main-wb -->
	</div>
</div>
