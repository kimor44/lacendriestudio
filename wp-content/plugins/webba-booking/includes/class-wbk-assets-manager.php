<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class WBK_Assets_Manager is used to load CSS and JS files depended on detecting of backend or frontend
 */
class WBK_Assets_Manager {

    protected $css;
    protected $js;

    public function __construct( $css, $js ) {
        $this->css = $css;
        $this->js = $js;
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 20 );
        add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ), 20 );

    }

    public function admin_enqueue_scripts(){

        $admin_pages = array( 'wbk-services', 'wbk-email-templates', 'wbk-service-categories', 'wbk-appointments', 'wbk-coupons', 'wbk-gg-calendars', 'wbk-pricing-rules' );
    	if ( isset( $_GET[ 'page' ] ) && in_array( $_GET[ 'page' ], $admin_pages )  ) {
            Plugion()->initialize_assets();
        }

        foreach( $this->css as $item ){
            if( $item[0] == 'backend' ){
                if( isset( $_GET['page'] ) || $item[1] == 'all'  ){
                    if( $item[1] == 'all' || in_array( $_GET['page'], $item[1] ) ){
                        wp_enqueue_style( $item[2], $item[3], $item[4], $item[5] );
                    }
                }
            }
        }
        foreach( $this->js as $item ){
            if( $item[0] == 'backend' ){
                if( isset( $_GET['page'] )  || $item[1] == 'all'  ){
                    if( $item[1] == 'all' || in_array( $_GET['page'], $item[1] ) ){
                      wp_enqueue_script( $item[2], $item[3], $item[4], $item[5] );
                    }
                }
            }
        }

        $translation_array = array(
            'disable_nice_select' => get_option( 'wbk_disable_nice_select', '' ),
            'export_csv' => __( 'Export to CSV', 'wbk'),
            'start_export' =>  __( 'Start export', 'wbk'),
            'please_wait' =>  __( 'Please wait...', 'wbk'),
            'duplication_warning' =>  __( 'Duplication of bookings is highly discouraged because it can lead to errors in determining free timeslots.', 'wbk')
        );
        wp_localize_script( 'wbk-common-script', 'wbk_dashboardl10n', $translation_array );

    }

    public function wp_enqueue_scripts(){
        foreach( $this->css as $item ){
            if( $item[0] == 'frontend' ){
                wp_enqueue_style( $item[2], $item[3], $item[4], $item[5] );
            }
        }
        foreach( $this->js as $item ){
            if( $item[0] == 'frontend' ){
                wp_enqueue_script( $item[2], $item[3], $item[4], $item[5] );
            }
        }
    }


}

?>
