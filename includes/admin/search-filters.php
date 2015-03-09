<?php
/**
 * Created by PhpStorm.
 * User: nhiha60591
 * Date: 12/2/14
 * Time: 3:44 PM
 */
if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class TT_Example_List_Table extends WP_List_Table {

    /** ************************************************************************
     * REQUIRED. Set up a constructor that references the parent constructor. We
     * use the parent reference to set some default configs.
     ***************************************************************************/
    function __construct(){
        global $status, $page;

        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'pid',     //singular name of the listed records
            'plural'    => 'pids',    //plural name of the listed records
            'ajax'      => false        //does this table support ajax?
        ) );

    }


    /** ************************************************************************
     * Recommended. This method is called when the parent class can't find a method
     * specifically build for a given column. Generally, it's recommended to include
     * one method for each column you want to render, keeping your package class
     * neat and organized. For example, if the class needs to process a column
     * named 'title', it would first see if a method named $this->column_title()
     * exists - if it does, that method will be used. If it doesn't, this one will
     * be used. Generally, you should try to use custom column methods as much as
     * possible.
     *
     * Since we have defined a column_title() method later on, this method doesn't
     * need to concern itself with any column with a name of 'title'. Instead, it
     * needs to handle everything else.
     *
     * For more detailed insight into how columns are handled, take a look at
     * WP_List_Table::single_row_columns()
     *
     * @param array $item A singular item (one full row's worth of data)
     * @param array $column_name The name/slug of the column to be processed
     * @return string Text or HTML to be placed inside the column <td>
     **************************************************************************/
    function column_default($item, $column_name){
        switch($column_name){
            case 'field_key':
                return $item[$column_name];
            default:
                return print_r($item,true); //Show the whole array for troubleshooting purposes
        }
    }
    function column_field_heading( $item ){
        return sprintf(
            '<input type="text" name="izw_filters_ctf[%1$s][heading]" value="%2$s" />',
            /*$1%s*/ $item['field_key'],  //Let's simply repurpose the table's singular label ("movie")
            /*$2%s*/ $item['field_heading']                //The value of the checkbox should be the record's id
        );
    }
    function column_field_placeholder( $item ){
        return sprintf(
            '<input type="text" name="izw_filters_ctf[%1$s][placeholder]" value="%2$s" />',
            /*$1%s*/ $item['field_key'],  //Let's simply repurpose the table's singular label ("movie")
            /*$2%s*/ $item['field_placeholder']                //The value of the checkbox should be the record's id
        );
    }
    function column_custom_option( $item ){
        return sprintf(
            '<textarea name="izw_filters_ctf[%1$s][custom_option]">%2$s</textarea>',
            /*$1%s*/ $item['field_key'],  //Let's simply repurpose the table's singular label ("movie")
            /*$2%s*/ $item['custom_option']                //The value of the checkbox should be the record's id
        );
    }
    function column_field_type( $item ){
        $field_type = array(
            array(
                'name' => 'text',
                'title' => 'Text'
            ),
            array(
                'name' => 'select',
                'title' => 'Dropdown'
            ),
            array(
                'name' => 'radio',
                'title' => 'Radio'
            ),
        );
        $field_type = apply_filters( 'izw_import_field_type', $field_type );
        ob_start();
        ?>
            <select name="izw_filters_ctf[<?php echo $item['field_key'] ?>][field_type]">
                <?php foreach( $field_type as $k): ?>
                    <option <?php selected( $k['name'], $item['field_type']); ?> value="<?php echo $k['name']; ?>"><?php echo $k['title']; ?></option>
                <?php endforeach; ?>
            </select>
        <?php
        $return = ob_get_clean();
        return $return;
    }
    function column_between( $item ){
        ob_start();
        ?>
        <select name="izw_filters_ctf[<?php echo $item['field_key'] ?>][between]">
            <option value="no" <?php selected('no',$item['between']); ?>>No</option>
            <option value="yes" <?php selected('yes',$item['between']); ?>>Yes</option>
        </select>
        <?php
        return ob_get_clean();
    }
    function column_cb($item){
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
            /*$2%s*/ $item['ID']                //The value of the checkbox should be the record's id
        );
    }


    /** ************************************************************************
     * REQUIRED! This method dictates the table's columns and titles. This should
     * return an array where the key is the column slug (and class) and the value
     * is the column's title text. If you need a checkbox for bulk actions, refer
     * to the $columns array below.
     *
     * The 'cb' column is treated differently than the rest. If including a checkbox
     * column in your table you must create a column_cb() method. If you don't need
     * bulk actions or checkboxes, simply leave the 'cb' entry out of your array.
     *
     * @see WP_List_Table::::single_row_columns()
     * @return array An associative array containing column information: 'slugs'=>'Visible Titles'
     **************************************************************************/
    function get_columns(){
        $columns = array(
            'cb'     => '<input type="checkbox" />',
            'field_key'     => 'Field Key',
            'field_heading'    => 'Heading Text',
            'field_placeholder'  => 'Placeholder',
            'custom_option' => 'Custom Options',
            'field_type'  => 'Field Type',
            'between'  => 'Between'
        );
        return $columns;
    }
    protected function display_tablenav( $which ) {
        if ( 'top' == $which )
            wp_nonce_field( 'bulk-' . $this->_args['plural'] );
        ?>
        <div class="tablenav <?php echo esc_attr( $which ); ?>">

            <div class="alignleft actions bulkactions">
                <?php $this->bulk_actions( $which ); ?>
                <button id="delete-fields" class="button button-primary">Delete selected fields</button>
            </div>
            <?php
            $this->extra_tablenav( $which );
            $this->pagination( $which );
            ?>

            <br class="clear" />
        </div>
    <?php
    }

    /** ************************************************************************
     * Optional. If you want one or more columns to be sortable (ASC/DESC toggle),
     * you will need to register it here. This should return an array where the
     * key is the column that needs to be sortable, and the value is db column to
     * sort by. Often, the key and value will be the same, but this is not always
     * the case (as the value is a column name from the database, not the list table).
     *
     * This method merely defines which columns should be sortable and makes them
     * clickable - it does not handle the actual sorting. You still need to detect
     * the ORDERBY and ORDER querystring variables within prepare_items() and sort
     * your data accordingly (usually by modifying your query).
     *
     * @return array An associative array containing all the columns that should be sortable: 'slugs'=>array('data_values',bool)
     **************************************************************************/
    function get_sortable_columns() {
        $sortable_columns = array(
            'date'     => array('date',false),     //true means it's already sorted
        );
        return $sortable_columns;
    }


    /** ************************************************************************
     * REQUIRED! This is where you prepare your data for display. This method will
     * usually be used to query the database, sort and filter the data, and generally
     * get it ready to be displayed. At a minimum, we should set $this->items and
     * $this->set_pagination_args(), although the following properties and methods
     * are frequently interacted with here...
     *
     * @global WPDB $wpdb
     * @uses $this->_column_headers
     * @uses $this->items
     * @uses $this->get_columns()
     * @uses $this->get_sortable_columns()
     * @uses $this->get_pagenum()
     * @uses $this->set_pagination_args()
     **************************************************************************/
    function prepare_items() {
        global $wpdb; //This is used only if making any database queries
        if( isset( $_POST['izw_save_filters'] ) ){
            $fl = array_filter($_POST['izw_filters_ctf']);
            update_option( 'izw_filters_ctf', $fl );
        }

        /**
         * First, lets decide how many records per page to show
         */
        $per_page = 10;


        /**
         * REQUIRED. Now we need to define our column headers. This includes a complete
         * array of columns to be displayed (slugs & titles), a list of columns
         * to keep hidden, and a list of columns that are sortable. Each of these
         * can be defined in another method (as we've done here) before being
         * used to build the value for our _column_headers property.
         */
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();


        /**
         * REQUIRED. Finally, we build an array to be used by the class for column
         * headers. The $this->_column_headers property takes an array which contains
         * 3 other arrays. One for all columns, one for hidden columns, and one
         * for sortable columns.
         */
        $this->_column_headers = array($columns, $hidden, $sortable);

        //$data = $wpdb->get_results("SELECT * FROM {$table}", ARRAY_A);
        $filters = get_option('izw_filters_ctf');
        if( is_array( $filters ) && sizeof( $filters ) > 0 ){
            $data = array();
            $i=1;
            foreach( $filters as $k=>$v){
                $data[] = array(
                    'ID'     => $i,
                    'field_key'     => $k,
                    'field_heading'    => $v['heading'],
                    'field_placeholder'  => $v['placeholder'],
                    'custom_option'  => $v['custom_option'],
                    'field_type'  => $v['field_type'],
                    'between' => $v['between']
                );
                $i++;
            }
        }else{
            $data = array();
        }




        /**
         * This checks for sorting input and sorts the data in our array accordingly.
         *
         * In a real-world situation involving a database, you would probably want
         * to handle sorting by passing the 'orderby' and 'order' values directly
         * to a custom query. The returned data will be pre-sorted, and this array
         * sorting technique would be unnecessary.
         */
        function usort_reorder($a,$b){
            $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'title'; //If no sort, default to title
            $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'asc'; //If no order, default to asc
            $result = strcmp($a[$orderby], $b[$orderby]); //Determine sort order
            return ($order==='asc') ? $result : -$result; //Send final sort direction to usort
        }
        usort($data, 'usort_reorder');


        /***********************************************************************
         * ---------------------------------------------------------------------
         * vvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvv
         *
         * In a real-world situation, this is where you would place your query.
         *
         * For information on making queries in WordPress, see this Codex entry:
         * http://codex.wordpress.org/Class_Reference/wpdb
         *
         * ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
         * ---------------------------------------------------------------------
         **********************************************************************/


        /**
         * REQUIRED for pagination. Let's figure out what page the user is currently
         * looking at. We'll need this later, so you should always include it in
         * your own package classes.
         */
        $current_page = $this->get_pagenum();

        /**
         * REQUIRED for pagination. Let's check how many items are in our data array.
         * In real-world use, this would be the total number of items in your database,
         * without filtering. We'll need this later, so you should always include it
         * in your own package classes.
         */
        $total_items = count($data);


        /**
         * The WP_List_Table class does not handle pagination for us, so we need
         * to ensure that the data is trimmed to only the current page. We can use
         * array_slice() to
         */
        $data = array_slice($data,(($current_page-1)*$per_page),$per_page);



        /**
         * REQUIRED. Now we can add our *sorted* data to the items property, where
         * it can be used by the rest of the class.
         */
        $this->items = $data;


        /**
         * REQUIRED. We also have to register our pagination options & calculations.
         */
        $this->set_pagination_args( array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
        ) );
    }


}
//Create an instance of our package class...
$testListTable = new TT_Example_List_Table();
//Fetch, prepare, sort, and filter our data...
$testListTable->prepare_items();

?>
<div class="wrap">

    <div id="icon-users" class="icon32"><br/></div>
    <h2>Search Filters</h2>
    <div class="meta-box-sortables">
        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns">
                <div class="postbox-container">
                    <div id="izw-list-fields" class="postbox">
                        <div class="handlediv" title="Click to toggle"><br></div>
                        <h3 class="hndle ui-sortable-handle"><span>List Custom Fields</span></h3>
                        <div class="inside">
                            <?php
                            global $wpdb;
                            $query_test = "SELECT DISTINCT($wpdb->posts.ID),CONVERT(SUBSTRING_INDEX({$wpdb->postmeta}.meta_value,' ',1),UNSIGNED INTEGER) AS izw_age
                                      FROM $wpdb->posts
                                      LEFT JOIN $wpdb->postmeta
                                      ON $wpdb->posts.ID = $wpdb->postmeta.post_id
                                      WHERE $wpdb->posts.post_type = 'program'
                                      AND $wpdb->postmeta.meta_key = 'minimum_age'
                                      AND CONVERT(SUBSTRING_INDEX({$wpdb->postmeta}.meta_value,' ',1),UNSIGNED INTEGER) BETWEEN 1 AND 50";
                            $test = $wpdb->get_results( $query_test );
                            print_r( $test );
                            $meta_keys = $wpdb->get_col($wpdb->prepare($query, $post_type));
                            $option = get_option('izw_filters_ctf');
                            if( !is_array( $option ) ) $option = array();
                            $query = "SELECT DISTINCT($wpdb->postmeta.meta_key)
                                      FROM $wpdb->posts
                                      LEFT JOIN $wpdb->postmeta
                                      ON $wpdb->posts.ID = $wpdb->postmeta.post_id
                                      WHERE $wpdb->posts.post_type = 'program'
                                      AND $wpdb->postmeta.meta_key != ''
                                      AND $wpdb->postmeta.meta_key NOT RegExp '(^[_0-9].+$)'
                                      AND $wpdb->postmeta.meta_key NOT RegExp '(^[0-9]+$)'";
                            $meta_keys = $wpdb->get_col($wpdb->prepare($query, $post_type));
                            if( sizeof( $meta_keys ) > 0) {
                                echo 'Custom Field: <select name="meta_key" id="izw-meta-key">';
                                foreach ($meta_keys as $k) {
                                    if( array_key_exists( $k, $option )){
                                        echo "<option disabled=\"true\" value=\"{$k}\">{$k}</option>";
                                    }else {
                                        echo "<option value=\"{$k}\">{$k}</option>";
                                    }
                                }
                                echo "</select><button id='add-field' class='button'>Add</button>";
                            }
                            ?>

                        </div>
                    </div>
                    <!-- END .izw-list-fields -->
                </div>
            </div>
        </div>
    </div>

    <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
    <form id="movies-filter" method="post">
        <?php $testListTable->display() ?>
        <?php submit_button('Save Change', 'primary', 'izw_save_filters'); ?>
    </form>
    <script type="text/javascript">
        jQuery(document).ready(function($){
            jQuery("button#add-field").click(function(){
                var ht_lenght = $('.wrap tbody tr').length;
                var k = $("#izw-meta-key").val();
                if( $(".wrap tbody tr.no-items").html() != null ) $(".wrap tbody tr.no-items").remove();
                if( k == null) return false;
                $("#izw-meta-key").children('option[value=' + k + ']')
                    .attr('disabled', true);
                var ht_class = '';
                if(ht_lenght % 2 == 0 ){
                    ht_class = 'alternate';
                }else{
                    ht_class = '';
                }
                ht_lenght = ht_lenght +1;
                var html = '<tr class="'+ ht_class +'">' +
                    '<th scope="row" class="check-column"><input type="checkbox" name="pid[]" value="'+ ht_lenght +'"></th>' +
                    '<td class="field_key column-field_key">'+k+'</td>' +
                    '<td class="field_heading column-field_heading"><input type="text" name="izw_filters_ctf['+k+'][heading]" value=""></td>' +
                    '<td class="field_placeholder column-field_placeholder"><input type="text" name="izw_filters_ctf['+k+'][placeholder]" value=""></td>' +
                    '<td class="custom_option column-custom_option"><textarea name="izw_filters_ctf['+ k +'][custom_option]"></textarea></td>' +
                    '<td class="field_type column-field_type">' +
                    '<select name="izw_filters_ctf['+k+'][field_type]">\
                        <option selected="selected" value="text">Text</option>\
                        <option value="select">Dropdown</option>\
                    </select>\
                </td></tr>';
                $('.wrap tbody').append(html);
                return false;
            });
            jQuery(".wrap button#delete-fields").click(function(){
                var answer = confirm("Delete the selected rows?");
                if (answer) {
                    jQuery('.wrap tbody tr th.check-column input:checked').each(function(i, el){
                        var k = jQuery(el).closest('tr').children("td.field_key").text();
                        if( $("#izw-meta-key option[value='" + k + "']").text() != "") {
                            $("#izw-meta-key").children('option[value=' + k + ']')
                                .removeAttr('disabled');
                        }
                        jQuery(el).closest('tr').remove();
                    });
                }
                return false;
            });
        });
    </script>

</div>