<?php
/*
Plugin Name: Export Clients Woocommerce
Plugin URI: https://github.com/benoitchocot/export-customers-woocommerce
Description: Export des clients avec leur commandes sur une plage horaire définie
Author: Benoit Chocot
Version: 1.1
Author URI: https://github.com/benoitchocot
*/

class ExportClient
{
    public function __construct()
    {
        add_action('admin_menu', [$this, 'admin_export_client_plugin_menu']);
    }

    public function admin_export_client_plugin_menu()
    {
        add_menu_page(
            __('Export Client', 'export-wordpress-woocommerce'), // Page title
            __('Export Client', 'export-wordpress-woocommerce'), // Menu title
            'manage_options',  // Capability
            'export-client', // Slug
            [&$this, 'load_export_client_plugin_page'] // Callback page function
        );
    }


    public function load_export_client_plugin_page()
    {
        global $wpdb;
        echo '<h1>Export clients Woocommerce</h1>';
        echo '<h2>Rentrez les dates nécessaires pour faire un export entre ces deux dates</h2>';
        echo '
                <form action="' . get_site_url() . '/wp-admin/admin.php?page=export-client" method="post" class="form-example">
                  <div class="form-example">
                    <label for="date_start">date de début</label>
                    <input type="date" name="date_start" id="date_start" required>
                  </div>
                  <div class="form-example">
                    <label for="date_end">date de fin</label>
                    <input type="date" name="date_end" id="date_end" required>
                  </div>
                  <div class="form-example">
                    <input type="submit" name="submit" value="Go">
                  </div>
                </form>';
        if (isset($_POST['submit'])) {
            $date_start = $_POST['date_start'];
            $date_end = $_POST['date_end'];
            $exports = $wpdb->get_results("
                SELECT a.first_name, a.last_name, a.email, a.country, a.postcode, a.city, d.post_title, c.date_created
                FROM {$wpdb->prefix}wc_customer_lookup a
                LEFT JOIN {$wpdb->prefix}wc_order_product_lookup b on a.customer_id = b.customer_id
                LEFT JOIN {$wpdb->prefix}wc_order_stats c on b.order_id = c.order_id
                LEFT JOIN {$wpdb->prefix}posts d on b.product_id = d.ID
                WHERE c.date_created BETWEEN \"" . $date_start . "\" AND \"" . $date_end . "\"
                ", ARRAY_A);
            header("Content-Transfer-Encoding: application/vnd.ms-excel;charset=UTF-8");

            $date = date("Y-m-d");
            $fp = fopen('export-' . $date . '.xls', 'w');

            foreach ($exports as $export) {
                fputcsv($fp, array_map('utf8_decode',array_values($export)), "\t");
            }
            fclose($fp);
        }
        if (!empty($fp)) {
            echo "<a href='export-" . $date . ".xls'>Télécharger</a>";
        }
    }
}

new ExportClient();
