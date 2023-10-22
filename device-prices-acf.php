<?php
/**
 * Plugin Name:       Device Prices ACF
 * Plugin URI:        https://buyreadysite.com/device-prices-acf
 * Description:       This plugin pulls up the device model's price from the ACF field group and allows to edit it.
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            buyreadysite.com
 * Author URI:        https://buyreadysite.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       device-prices-acf
 */

if (!defined('ABSPATH')) {
    exit;
}

class DevicePricesACF {
    function __construct() {
        add_action('admin_menu', array($this, 'create_plugin_settings_page'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_custom_scripts'));
        add_action('wp_ajax_search_device_prices', array($this, 'search_device_prices'));
        add_action('wp_ajax_update_device_price', array($this, 'update_device_price'));
    }

    function create_plugin_settings_page() {
        $page_title = 'Device Prices ACF';
        $menu_title = 'Device Prices ACF';
        $capability = 'manage_options';
        $slug = 'device_prices';
        $callback = array($this, 'plugin_settings_page_content');
        $icon = 'dashicons-admin-plugins';
        $position = 3;

        add_menu_page($page_title, $menu_title, $capability, $slug, $callback, $icon, $position);
    }

    function plugin_settings_page_content() { ?>
        <div class="wrap">
            <h2>Device Prices ACF</h2>
            <form method="post" action="options.php">
                <input id="device-model" type="text" name="device_model" value="" placeholder="Enter device model">
                <input id="search-device" type="button" class="button button-primary" value="Search">
                <img id="loading-indicator" src="<?=admin_url('images/loading.gif')?>" style="visibility: hidden;">
            </form>
            <div id="prices-table"></div>
        </div>
        <?php
    }

    function enqueue_custom_scripts() {
        wp_enqueue_script('jquery');
        wp_enqueue_style('acf-device-prices-css', plugin_dir_url(__FILE__) . 'style.css');
        wp_enqueue_script('x-editable', 'https://cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.1/bootstrap-editable/js/bootstrap-editable.min.js', array('jquery'), null, true);
        wp_enqueue_style('x-editable-css', 'https://cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.1/bootstrap-editable/css/bootstrap-editable.css', [], null);
        wp_enqueue_script('acf-device-prices', plugin_dir_url(__FILE__) . 'acf-device-prices.js', array('jquery', 'x-editable'), '1.0.0', true);
        wp_localize_script('acf-device-prices', 'acf_device_prices_ajax', array('ajax_url' => admin_url('admin-ajax.php')));
    }

function search_device_prices() {
    $device_model = sanitize_text_field($_POST['device_model']);

    // Получаем все сообщения
    $posts = get_posts(array(
        'numberposts' => -1,
        'post_type' => 'page'
    ));

    // Массив для хранения результатов
    $results = [];

    foreach ($posts as $post) {
        $fields = get_fields($post->ID);

        foreach ($fields as $field_name => $field) {
            if (is_array($field)) {
                foreach ($field as $tab) {
                    if (is_array($tab)) {
                        foreach ($tab as $key => $value) {
                            if (strpos($key, 'prise_tab_name') !== false && strpos(strtolower($value), strtolower($device_model)) !== false) {
                                $price_key = str_replace('_name', '_money', $key);
                                if (isset($tab[$price_key]) && !empty($tab[$price_key])) {  // Только если цена присутствует
                                    $result = ['group' => $field_name, 'name' => $value, 'price' => $tab[$price_key]];
                                    if (!in_array($result, $results)) {  // Только если это не дубликат
                                        $results[] = $result;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    // Выводим результаты в формате JSON
    echo json_encode($results);

    wp_die();
}




/*     function search_device_prices() {
        $device_model = sanitize_text_field($_POST['device_model']);

        // Получаем все сообщения
        $posts = get_posts(array(
            'numberposts' => -1,
            'post_type' => 'any'
        ));

        // Массив для хранения результатов
        $results = [];

        foreach ($posts as $post) {
            $fields = get_fields($post->ID);

            foreach ($fields as $field_name => $field) {
                if (is_array($field)) {
                    foreach ($field as $tab) {
                        if (is_array($tab)) {
                            foreach ($tab as $key => $value) {
                                if (strpos($key, 'prise_tab_name') !== false && strpos($value, $device_model) !== false) {
                                    $price_key = str_replace('_name', '_money', $key);
                                    if (isset($tab[$price_key]) && !empty($tab[$price_key])) {  // Только если цена присутствует
                                        $result = ['group' => $field_name, 'name' => $value, 'price' => $tab[$price_key]];
                                        if (!in_array($result, $results)) {  // Только если это не дубликат
                                            $results[] = $result;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        // Выводим результаты в формате JSON
        echo json_encode($results);

        wp_die();
    } */



/* function update_device_price() {
    $pk = sanitize_text_field($_POST['pk']);
    $newValue = sanitize_text_field($_POST['value']); // получаем новое значение цены
    list($group, $name) = explode("|", $pk);

    // Получаем все сообщения
    $posts = get_posts(array(
        'numberposts' => -1,
        'post_type' => 'any'  // замените на соответствующий тип записи
    ));

    $updates = [];  // массив для хранения обновлений

    foreach ($posts as $post) {
        $fields = get_fields($post->ID);

        foreach ($fields as $field_name => $field) {
            if ($field_name == 'prise_tab') {  // замените на соответствующее имя поля
                if (is_array($field)) {
                    foreach ($field as $tab_key => $tab) {
                        if (is_array($tab)) {
                            foreach ($tab as $key => $value) {
                                if (strpos($key, 'prise_tab_name') !== false && $value == $name) {
                                    $price_key = str_replace('_name', '_money', $key);
                                    if (isset($tab[$price_key])) {  // Только если цена присутствует
                                        // Обновляем цену
                                        $fields[$field_name][$tab_key][$price_key] = $newValue; // используем новое значение цены

                                        // Записываем обновление
                                        $updates[$post->ID][$field_name] = $field;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    // Обновляем поля для каждого поста
    foreach ($updates as $post_id => $fields) {
        foreach ($fields as $field_name => $field) {
            update_field($field_name, $field, $post_id);
        }
    }

    echo json_encode(['status' => 'success']);
    wp_die();
} */



function update_device_price() {
    $pk = sanitize_text_field($_POST['pk']);
    $newValue = sanitize_text_field($_POST['value']); // получаем новое значение цены
    list($group, $name) = explode("|", $pk);

    // Получаем все сообщения
    $posts = get_posts(array(
        'numberposts' => -1,
        'post_type' => 'any'
    ));

    foreach ($posts as $post) {
        $fields = get_fields($post->ID);
        $need_update = false; // flag for checking if update is required

        foreach ($fields as $field_name => $field) {
            if ($field_name == $group) {
                if (is_array($field)) {
                    foreach ($field as $tab_key => $tab) {
                        if (is_array($tab)) {
                            foreach ($tab as $key => $value) {
                                if (strpos($key, 'prise_tab_name') !== false && $value == $name) {
                                    $price_key = str_replace('_name', '_money', $key);
                                    if (isset($tab[$price_key]) && $tab[$price_key] != $newValue) {  // Only if price is present and different
                                        // Update price
                                        $fields[$field_name][$tab_key][$price_key] = $newValue; // используем новое значение цены
                                        $need_update = true;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        // Update fields for this post if required
        if ($need_update) {
            foreach ($fields as $field_name => $field) {
                update_field($field_name, $field, $post->ID);
            }
        }
    }

    echo json_encode(['status' => 'success']);
    wp_die();
}




















/* function update_device_price() {
    $pk = sanitize_text_field($_POST['pk']);
    $newValue = sanitize_text_field($_POST['value']); // получаем новое значение цены
    list($group, $name) = explode("|", $pk);

    // Получаем все сообщения
    $posts = get_posts(array(
        'numberposts' => -1,
        'post_type' => 'any'
    ));

    foreach ($posts as $post) {
        $fields = get_fields($post->ID);

        foreach ($fields as $field_name => $field) {
            if ($field_name == $group) {
                if (is_array($field)) {
                    foreach ($field as $tab_key => $tab) {
                        if (is_array($tab)) {
                            foreach ($tab as $key => $value) {
                                if (strpos($key, 'prise_tab_name') !== false && $value == $name) {
                                    $price_key = str_replace('_name', '_money', $key);
                                    if (isset($tab[$price_key])) {  // Только если цена присутствует
                                        // Обновляем цену
                                        $fields[$field_name][$tab_key][$price_key] = $newValue; // используем новое значение цены
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        // Обновляем поля для этого поста
        foreach ($fields as $field_name => $field) {
            update_field($field_name, $field, $post->ID);
        }
    }

    echo json_encode(['status' => 'success']);
    wp_die();
} */
 


}

new DevicePricesACF();
