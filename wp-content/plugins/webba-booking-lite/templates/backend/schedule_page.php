<?php
// check if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;
if( isset( $_GET['schedule-tools'] ) && $_GET['schedule-tools'] == 'true' ){
    WBK_Renderer::load_template( 'backend/backend_page_v5', array(), true );
    return;
}

date_default_timezone_set( get_option( 'wbk_timezone', 'UTC' ) );

WBK_Renderer::load_template( 'backend/schedule_booking_dialog', array(), true );
?>
 
<div class="wrap">
	 	</h2>
	<?php
    	$html = '<div class="wbk-schedule-row">';
        $html .= '<p class="wbk-section-title">' . esc_html( __( 'Click to open schedule tools:', 'wbk' ) ) . '</p>';
        $html .= '<a href="'. get_admin_url() . 'admin.php?page=wbk-schedule&schedule-tools=true">' . __( 'Schedule tools', 'wbk' ) . '</a>';
        $html .= '</div>';

		$html .= '<div class="wbk-schedule-row">';
	 	$arr_ids = WBK_Model_Utils::get_service_ids();
 		if ( count( $arr_ids ) < 1 ) {
 			$html .= esc_html( _( 'Create at least one service. ', 'wbk' ) );
 		} else {
			$html .= '<p class="wbk-section-title">' . esc_html( __( 'Click to display the service schedule:', 'wbk' ) ) . '</p>';
	 		foreach ( $arr_ids as $id ) {
				if ( !current_user_can('manage_options') ) {
					if ( !WBK_Validator::check_access_to_service( $id ) ) {
							continue;
					}
				}
	 			$service = new WBK_Service( $id );
				$service_label = $service->get_name();
				if( get_option( 'wbk_backend_show_category_name', 'disabled' ) == 'enabled' ){
					$category_names = WBK_Model_Utils::get_category_names_by_service( $service->get_id() );
					if( $category_names != '' ){
						$service_label .= ' (' . $category_names . ')';
					}
				}
	 			$html .= '<a class="button ml5" id="load_schedule_'. esc_attr( $id ) .'" >' . esc_html( $service_label ) . '</a>';
	 		}
	 	}
		$html .= '</div>';

		echo $html;

	?>
	<div id="days_container">
	</div>
	<?php do_action('wbk_backend_schedule_days_container'); ?>
	<div id="control_container">
	</div>
</div>
<?php
date_default_timezone_set( 'UTC' );
?>
