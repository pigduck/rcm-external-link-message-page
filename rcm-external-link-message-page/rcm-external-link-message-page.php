<?php
/*
 * Plugin Name: RCM External Link Message Page
 * Plugin URI: https://piglet.me/rcm-external-link-message-page
 * Description: a simple message page for external links
 * Version: 0.0.1
 * Author: heiblack
 * Author URI: https://piglet.me
 * License:  GPL 3.0
 * Domain Path: /languages
 * Text Domain: rcm-external-link-message-page
 * License URI: http://www.gnu.org/licenses/gpl-3.0.txt
*/


use rcm_eump_transparencyreport\WP_Customize_Tiny_Control;

class RcmExternalLinkMessagePage
{

    public function __construct()
    {
        if (is_admin()) {
            require_once(ABSPATH . 'wp-admin/includes/plugin.php');
        }
        if ( ! defined( 'ABSPATH' ) ) exit;

        $this->init();
    }
    private function init()
    {

        register_activation_hook(__FILE__, function () {
            $page_title = __('RCM External Link Message Page', 'rcm-external-link-message-page');
            $page_content = '';
            $page_check = get_page_by_title($page_title);
            $new_page = array(
                'post_type' => 'page',
                'post_title' => $page_title,
                'post_content' => $page_content,
                'post_status' => 'publish',
                'post_author' => 1,
            );
            if (!isset($page_check->ID)) {
                $new_page_id = wp_insert_post($new_page);
                update_post_meta($new_page_id, '_wp_page_template', 'templates/rottencodemonkey-eump.php');
                //update option
                update_option('rcm_eump_page_id', $new_page_id);
            }
        });

        add_action('wp_enqueue_scripts',  function () {
            if (is_page_template('templates/rottencodemonkey-eump.php')) {
                wp_enqueue_style('rottencodemonkey-eump', plugin_dir_url(__FILE__) .  'assets/css/rottencodemonkey-eump.css');
                $rcm_eump_page_css = get_option('rcm_eump_css_setting');
                wp_register_style('rcm_eump_page_css', false);
                wp_enqueue_style('rcm_eump_page_css');
                wp_add_inline_style('rcm_eump_page_css', $rcm_eump_page_css);
            }
        });

        add_action('wp_ajax_rcm_get_eump', function () {
            if (is_admin()) {
                $page_id = get_option('rcm_eump_page_id');
                //page id to get url
                $page_url = get_permalink($page_id);
                echo esc_url($page_url);
                die();
            }
            die();
        });

        //allow use iframe tag
        add_filter('wp_kses_allowed_html', function ($tags, $context) {
            if ('post' === $context) {
                $tags['iframe'] = array(
                    'src'             => true,
                    'height'          => true,
                    'width'           => true,
                    'frameborder'     => true,
                    'allowfullscreen' => true,
                );
            }
            return $tags;
        }, 10, 2);


        add_action('customize_controls_enqueue_scripts', function () {
            wp_enqueue_script('wp-rcm-external-link-message-page', plugin_dir_url(__FILE__) . '/assets/js/rcm-external-link-message-page.js');
        });



        $this->rcmCreateTemplates();
        $this->rcmCreateWpCustomize();
    }
    private function rcmCreateTemplates()
    {
        add_filter('page_template',  function ($page_template) {
            if (get_page_template_slug() == 'templates/rottencodemonkey-eump.php') {
                $page_template = dirname(__FILE__) . '/templates/rottencodemonkey-eump.php';
            }
            return $page_template;
        }, 10, 1);
        add_filter('theme_page_templates',  function ($post_templates, $wp_theme, $post, $post_type) {
            $post_templates['templates/rottencodemonkey-eump.php'] = __('RCM EUMP', 'rcm-external-link-message-page');
            return $post_templates;
        }, 10, 4);
    }
    private function rcmCreateWpCustomize()
    {
        add_action('customize_register', function ($wp_customize) {

            require  plugin_dir_path(__FILE__) . 'inc/Customize-TinyMCE.php';

            $wp_customize->add_section(
                'rcm_external_link_message_page_section',
                array(
                    'title' => __('RCM External Link Message Page2', 'rcm-external-link-message-page'),
                    'priority' => 30,

                )
            );
            $wp_customize->add_setting('rcm_eump_title_setting', array(
                'default' => __('You Are Leaving...', 'rcm-external-link-message-page'),
                'sanitize_callback' => 'sanitize_text_field',
                'capability'        => 'edit_theme_options',
                'type'              => 'option',

            ));
            $wp_customize->add_control('rcm_eump_title_setting', array(
                'label' => __('Title', 'rcm-external-link-message-page'),
                'type' => 'text',
                'section' => 'rcm_external_link_message_page_section',
                'settings' => 'rcm_eump_title_setting',
            ));

            $wp_customize->add_setting('rcm_eump_header_setting', array(
                'default'           => '',
                'sanitize_callback' => 'sanitize_text_field',
                'capability'        => 'edit_theme_options',
                'type'              => 'option',
            ));
            $wp_customize->add_control(
                'rcm_eump_heade_setting',
                array(
                    'type' => 'checkbox',
                    'label' => __('Show Header', 'rcm-external-link-message-page'),
                    'settings' => 'rcm_eump_header_setting',
                    'section' => 'rcm_external_link_message_page_section',
                )
            );
            $wp_customize->add_setting( 'rcm_eump_logo_setting', array(
                'default'           => '',
                'sanitize_callback' => 'sanitize_text_field',
                'capability'        => 'edit_theme_options',
                'type'              => 'option',
            ));
            $wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'rcm_eump_logo', array(
                'label' => __('Site Logo','rcm-external-link-message-page'),
                'section' => 'rcm_external_link_message_page_section',
                'settings' => 'rcm_eump_logo_setting',
            )));
            $wp_customize->add_setting('rcm_eump_text_1_setting', array(
                'default'           => '',
                'sanitize_callback' => 'wp_kses_post',
                'capability'        => 'edit_theme_options',
                'type'              => 'option',
            ));
            $wp_customize->add_control(new WP_Customize_Tiny_Control( $wp_customize, 'rcm_eump_text_1', array(
                'section' => 'rcm_external_link_message_page_section',
                'settings' => 'rcm_eump_text_1_setting',
                'label'=>'Body',
            )));
            $wp_customize->add_setting('rcm_eump_transparencyreport_setting', array(
                'default'           => '',
                'sanitize_callback' => 'sanitize_text_field',
                'capability'        => 'edit_theme_options',
                'type'              => 'option',
            ));
            $wp_customize->add_control('rcm_eump_transparencyreport_setting',
                array(
                    'type' => 'checkbox',
                    'label' => __( 'Show Google Transparency Report', 'rcm-external-link-message-page' ),
                    'section' => 'rcm_external_link_message_page_section',
                    'settings' => 'rcm_eump_transparencyreport_setting',
                )
            );
            $wp_customize->add_setting('rcm_eump_text5_setting',array(
                'default' => __( 'Find the site I\'m going to', 'rcm-external-link-message-page' ),
                'sanitize_callback' => 'sanitize_text_field',
                'capability'        => 'edit_theme_options',
                'type'              => 'option',
            ));
            $wp_customize->add_control('rcm_eump_text5_setting',array(
                'label'=> __( 'Google Transparency Report', 'rcm-external-link-message-page' ),
                'type'=>'text',
                'section'=>'rcm_external_link_message_page_section',
                'settings' => 'rcm_eump_text5_setting',
            ));
            $wp_customize->add_setting('rcm_eump_text6_setting',array(
                'default' => __( 'Confirm to go', 'rcm-external-link-message-page' ),
                'sanitize_callback' => 'sanitize_text_field',
                'capability'        => 'edit_theme_options',
                'type'              => 'option',

            ));
            $wp_customize->add_control('rcm_eump_text6_setting',array(
                'label'=> __( 'Button Text', 'rcm-external-link-message-page' ),
                'type'=>'text',
                'section'=>'rcm_external_link_message_page_section',
                'settings' => 'rcm_eump_text6_setting',
            ));

            $wp_customize->add_setting('rcm_eump_text_2_setting', array(
                'default'           => '',
                'sanitize_callback' => 'wp_kses_post',
                'capability'        => 'edit_theme_options',
                'type'              => 'option',
            ));
            $wp_customize->add_control(new WP_Customize_Tiny_Control( $wp_customize, 'rcm_eump_text_2_setting', array(
                'label'=> __( 'Center Text', 'rcm-external-link-message-page' ),
                'section' => 'rcm_external_link_message_page_section',
                'settings' => 'rcm_eump_text_2_setting',
            )));
            $wp_customize->add_setting('rcm_eump_text_3_setting', array(
                'default'           => '',
                'sanitize_callback' => 'wp_kses_post',
                'capability'        => 'edit_theme_options',
                'type'              => 'option',
            ));
            $wp_customize->add_control(new WP_Customize_Tiny_Control( $wp_customize, 'rcm_eump_text_3_setting', array(
                'label'=> __( 'Footer Text', 'rcm-external-link-message-page' ),
                'section' => 'rcm_external_link_message_page_section',
                'settings' => 'rcm_eump_text_3_setting',
            )));

            $wp_customize->add_setting('rcm_eump_poweredby_setting', array(
                'default'           => '',
                'sanitize_callback' => 'sanitize_text_field',
                'capability'        => 'edit_theme_options',
                'type'              => 'option',
            ));
            $wp_customize->add_control('rcm_eump_poweredby_setting',
                array(
                    'type' => 'checkbox',
                    'label' => __( 'Hide Copyright Notice', 'rcm-external-link-message-page' ),
                    'section' => 'rcm_external_link_message_page_section',
                    'description' => __( '', 'rcm-external-link-message-page' ),
                    'settings' => 'rcm_eump_poweredby_setting',
                )
            );
            $wp_customize->add_setting( 'rcm_eump_css_setting', array(
                'default'           => '',
                'sanitize_callback' => 'wp_kses_post',
                'capability'        => 'edit_theme_options',
                'type'              => 'option',
            ));

            $wp_customize->add_control( new WP_Customize_Code_Editor_Control( $wp_customize, 'rcm_eump_css_setting', array(
                'label' => __( 'Customize CSS', 'rcm-external-link-message-page' ),
                'section' => 'rcm_external_link_message_page_section',
                'settings' => 'rcm_eump_css_setting',
                'code_type' => 'text/css',
            )));


        });
    }
}




new RcmExternalLinkMessagePage();



