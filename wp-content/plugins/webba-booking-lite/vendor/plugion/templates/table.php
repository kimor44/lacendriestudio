<?php
if ( !defined( 'ABSPATH' ) ) exit;
/*
 * This file is part of Plugion framework.
 * (c) plugion.com <hello@plugion.org>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

$table = $data[0];
$rows  = $data[1];
$slug  = $data[2];
$pagination = $data[3];
$search = $data[4];

do_action( 'plugion_before_table', $slug );

if ( 0 === count( $table->get_data( 'fields_to_view' ) ) ) {
    echo esc_html__( 'No data available.', 'plugion' );
    do_action( 'plugion_after_table', $slug );
    return;
}
// prepare add button;
if ( $table->current_user_can_add() ) {
    $add_button = '<a data-table="' . esc_attr( $slug ) . '" class="plugion_table_add_button plugion_button plugion_transparent_dark_button plugion_top_panel_button">' . plugion_translate_string( 'Add' ) . ' ' . plugion_translate_string( esc_html( $table->get_single_item_name() )  ) .  '</a>';
} else {
    $add_button = '';
}
// prepare filter button
if( count( $table->get_data( 'filters' ) ) > 0 ){
    $filter_button = '<a data-table="' . esc_attr( $slug ) . '" class="plugion_filter_button plugion_button plugion_transparent_dark_button plugion_top_panel_button">' . plugion_translate_string( 'Filters' ) .  '</a>';
} else {
    $filter_button = '';
}
// prepare table header
$table_header  = '<table class="plugion_table table_' . esc_attr( $slug ) . '" data-table="' . esc_attr( $slug ) . '" data-pagination="' . esc_attr( $pagination ) . '" data-search="' . esc_attr( $search ) . '"><thead>';
$table_header .= '<tr class="plugion_table_row_item"><th class="plugion_exportable" data-sorttype="">' . plugion_translate_string( 'ID' ) . '</th>';
foreach ( $table->get_data( 'fields_to_view' ) as $field_slug => $field ) {
    if ( !$field->get_in_row() ) {
        continue;
    }
    $table_header .= '<th class="plugion_cell plugion_exportable" id="title_'. esc_attr( $field_slug ) . '" data-sorttype="' . esc_attr( $field->get_sort_type() ) . '"  >' . esc_html( $field->get_title() ) . '</th>';
}

$table_header .= '</tr></thead>';

// filter table header
$table_header = apply_filters( 'plugion_table_header', $table_header, $slug );
// prepare table content
$table_content = '<tbody>';
ob_start();
foreach ( $table->get_data( 'rows' ) as $row ) {
    Plugion()->renderer->render_table_row( $row, $slug );
}
$table_content .= ob_get_clean() . '</tbody>';

$table_content = apply_filters( 'plugion_table_content', $table_content, $slug );
// prepare table footer
$table_footer = '</table>';
// filter table content
$table_footer = apply_filters( 'plugion_table_footer', $table_footer, $slug );


$table_html = $add_button . $filter_button . $table_header . $table_content . $table_footer;

echo $table_html;

do_action( 'plugion_after_table', $slug );

?>
