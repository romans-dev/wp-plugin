<?php
namespace twp;
use Psr\Log\LoggerInterface;
/**
 * Plugin Name: TEST WP PLUGIN
 * Plugin URI:  https://aaaaaaa.com
 * Description: For
 * Version:     1.0
 * Author:      RomanS
 * Author URI:  https://aaaa.com
 * Donate link: https://aaaaa.com
 * License:     GPLv3
 * Text Domain: test-wp
 *
 * @link https://aaaaaa.com
 *
 * @package UM User Switching
 * @version 1.0.0
 */
require '../../../vendor/autoload.php';

class Logger
{
    private $logger;

    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

}

add_shortcode('twp_last_posts', 'twp\get_last_posts');

function get_last_posts(){
    $logger = new Logger();

    $posts_count = get_option('number_of_twp_settings_posts');
    $posts_count = empty($posts_count) ? 10 : $posts_count;
    
    $args = [
        'posts_type' => 'post',
        'post_status' => 'published',
        'posts_per_page' => $posts_count,
        'orderby' => 'date',
        'order'   => 'DESC',
    ];
    try {
        $posts = get_posts($args);
        if(!empty($posts)){
            foreach ($posts as $t_post){
                echo "<h2><a href=". get_post_permalink($t_post->ID) ."> {$t_post->post_title}</a></h2><br>";
                echo "<p>{$t_post->post_excerpt}</p><br><br>";
            }
        } else {
            echo 'No posts';
        }


    } catch (\Exception $exception) {
        $logger->critical('Get posts error', [
            'exception' => $exception,
        ]);
    }



}

add_action( 'admin_menu', 'twp\menu_settings_page', 25 );

function menu_settings_page(){

    add_submenu_page(
        'options-general.php',
        'Настройки TEST',
        'TEST PLUGIN',
        'manage_options',
        'twp_settings',
        'twp\twp_settings_page_callback'
    );
}

function twp_settings_page_callback(){
    echo '<div class="wrap">
	<h1>' . get_admin_page_title() . '</h1>
	<form method="post" action="options.php">';

    settings_fields( 'twp_post_settings' ); // название настроек
    do_settings_sections( 'twp_settings' ); // ярлык страницы, не более
    submit_button(); // функция для вывода кнопки сохранения

    echo '</form></div>';
}

add_action( 'admin_init',  'twp\twp_settings_fields' );

function twp_settings_fields(){

    // регистрируем опцию
    register_setting(
        'twp_post_settings', // название настроек из предыдущего шага
        'number_of_twp_settings_posts', // ярлык опции
        'absint' // функция очистки
    );

    // добавляем секцию без заголовка
    add_settings_section(
        'twp_post_settings_section_id', // ID секции, пригодится ниже
        '', // заголовок (не обязательно)
        '', // функция для вывода HTML секции (необязательно)
        'twp_settings' // ярлык страницы
    );

    // добавление поля
    add_settings_field(
        'number_of_twp_settings_posts',
        'Количество постов',
        'twp\number_field', // название функции для вывода
        'twp_settings', // ярлык страницы
        'twp_post_settings_section_id', // // ID секции, куда добавляем опцию
        array(
            'label_for' => 'number_of_twp_settings_posts',
            'class' => 'twp-class', // для элемента <tr>
            'name' => 'number_of_twp_settings_posts', // любые доп параметры в колбэк функцию
        )
    );

}

function number_field( $args ){
    // получаем значение из базы данных
    $value = get_option( $args[ 'name' ] );

    printf(
        '<input type="number" min="1" id="%s" name="%s" value="%d" />',
        esc_attr( $args[ 'name' ] ),
        esc_attr( $args[ 'name' ] ),
        absint( $value )
    );

}