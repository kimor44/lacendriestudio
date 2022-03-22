<?php

if ( !defined( 'ABSPATH' ) ) exit;
final class WBK_Options_Processor {
    /**
     * The single instance of the class.
     * @var WBK_Options_Processor
     */
    protected static $inst = null;

    protected $options;

    private function __construct() {
    }
    public function add_option( $slug, $type, $title, $description, $section, $default_value, $extra = null, $page = 'wbk-options', $group = 'wbk_options',  $dependency = null ){
     
        switch ( $type ) {
            case 'text':
                $render_callback = 'render_text';
                $validation_callback = 'validate_text';
                break;
            case 'pass':
                $render_callback = 'render_pass';
                $validation_callback = 'validate_text';
                break;
            case 'textarea':
                $render_callback = 'render_textarea';
                $validation_callback = 'validate_textarea';
                break;
            case 'checkbox':
                $render_callback = 'render_checkbox';
                $validation_callback = 'validate_checkbox';
                break;
            case 'select':
                $render_callback = 'render_select';
                $validation_callback = 'validate_select';
                break;
            case 'editor':
                $render_callback = 'render_editor';
                $validation_callback = 'validate_editor';
                break;
            case 'select_multiple':
                $render_callback = 'render_select_multiple';
                $validation_callback = 'validate_select_multiple';
                break;
            default:
                $render_callback = 'render_text';
                $validation_callback = 'validate_text';
                break;
        }
        add_settings_field(
            $slug,
            $title,
            array( $this, $render_callback ),
            $page,
            $section,
            array( $slug, $default_value, $description, $extra,  $dependency )
        );
        register_setting(
            $group,
            $slug,
            array ( $this, $validation_callback )
        );
    }
    public function validate_text( $input ){
         return $input;
        $args = array(
            'strong' => array(),
            'em'     => array(),
            'b'      => array(),
            'i'      => array(),
            'br'     => array(),
            'p'      => array(
                             'class' => array()
                     ),
            'a'      => array(
                            'href' => array(),
                            'class' => array()
                        )
        );
         $result = str_replace( '&lt', '', $input );
         $result = str_replace( '&gt;', '', $result );
         $result = str_replace( '&#', '', $result );
         $result = str_replace( '\x', '', $result );
         $result = str_replace( '=', '', $input );
         $result = wp_kses( $input, $args );
         return $result;

    }
    public function validate_textarea( $input ){
        return $input;
    }
    public function validate_checkbox( $input ){
        return sanitize_text_field( $input );
    }
    public function validate_select( $input ){
        return sanitize_text_field( $input );
    }
    public function validate_editor( $input ){
        return $input;
    }
    public function validate_select_multiple( $input ){
        return $input;
    }
    public function render_text( $args ){
        WBK_Renderer::load_template( 'options/text_field', $args );
    }
    public function render_pass( $args ){
        WBK_Renderer::load_template( 'options/pass_field', $args );
    }
    public function render_textarea( $args ){
        WBK_Renderer::load_template( 'options/textarea_field', $args );
    }
    public function render_checkbox( $args ){
        WBK_Renderer::load_template( 'options/checkbox_field', $args );
    }
    public function render_select( $args ){
        WBK_Renderer::load_template( 'options/select_field', $args );
    }
    public function render_select_multiple( $args ){
        WBK_Renderer::load_template( 'options/select_multiple_field', $args );
    }
    public function render_editor( $args ){
        WBK_Renderer::load_template( 'options/editor_field', $args );
    }
    public function wbk_settings_section_callback( $arg ){

    }

    /**
     * returns instance of object
     */
    public static function Instance() {
        if ( is_null( self::$inst ) ) {
            self::$inst = new self();
        }

        return self::$inst;
    }

}

if( !function_exists('wbk_opt') ){
	function wbk_opt() {
	    return WBK_Options_Processor::instance();
	}
}
