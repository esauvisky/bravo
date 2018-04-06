<?php
/*
Plugin Name: Bravo! Plugin
Description: Site specific code for Bravo!
Author: Emiliano
*/

// Display different menu items for logged in/logged out users
add_filter( 'wp_nav_menu_items', 'add_menu_items', 10, 2 );
function add_menu_items( $items, $args ) {
    if (is_user_logged_in()) {
        if ($args->theme_location == 'primary') {
            // Adds Search and My Favorites links to the primary menu when logged in
            $items .= '<li><a href="' . get_permalink(2)  . '">Search</a></li>'; # maybe using get_permalink(get_page_by_title("Search")) could be more robust? I'd bet it isn't
            $items .= '<li><a href="' . get_permalink(43) . '">My Favorites</a></li>';
        }
        if ($args->theme_location == 'primary' || $args->theme_location == 'footer') {
            // Adds Log out to both menus when logged in
            $items .= '<li><a href="' . wp_logout_url(home_url()) . '">Log Out</a></li>';
        }
    } elseif (!is_user_logged_in()) {
        if ($args->theme_location == 'primary' || $args->theme_location == 'footer') {
            // TODO: redirect user to search after login or register
            $items .= '<li><a href="' . wp_registration_url() . '">Register</a></li>';
            $items .= '<li><a href="' . wp_login_url(get_permalink(2)) . '">Log In</a></li>';
        }
    }
    return $items;
}

// Removes admin bar for everyone except admins
add_action('after_setup_theme', 'remove_admin_bar');
function remove_admin_bar() {
    if (!current_user_can('administrator') && !is_admin()) {
        show_admin_bar(false);
    }
}

?>