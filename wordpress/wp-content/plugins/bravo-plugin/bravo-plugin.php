<?php
/*
Plugin Name: Bravo! Plugin
Description: This is the site specific plugin for Bravo!
Version: 1
Author: Emiliano Sauvisky
Released under the MIT License
*/

/* AIN'T GOT TIME FOR DOCUMENTATION IN HERE, OK? */

define('BRAVOPLUGINABSPATH', str_replace("\\","/", WP_PLUGIN_DIR . '/' . plugin_basename( dirname(__FILE__) ) . '/' ));

function file_get_contents_curl($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_REFERER, 'http://www.imdb.com/');
    curl_setopt($ch,CURLOPT_USERAGENT,$_SERVER['HTTP_USER_AGENT']);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}

function bravo_activate() {
    add_option('omdbapikey','');
}
function bravo_deactivate() {
    delete_option('omdbapikey');
}

function bravo_menu() {
    add_options_page('Bravo! Options', 'Bravo! Options', 'manage_options', 'bravo-plugin', 'bravo_admin_option');
}

function bravo_setting() {
    register_setting('bravo_options', 'omdbapikey');
}

function bravo_style() {
    wp_register_style('bravo-style', plugins_url('bravo-style.css', __FILE__));
    wp_enqueue_style('bravo-style');
}

function bravo_admin_option() {
    ?>
    <div class="wrap">
        <h2>Bravo! Options</h2>
        <form method="post" action="options.php">
            <?php
            settings_fields('bravo_options');
            ?>
            <table>
                <tr>
                    <td><strong>OMDB Api Key</strong></td>
                    <td><input type="text" value="<?php echo get_option('omdbapikey'); ?>" name="omdbapikey" /></td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td><input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" /></td>
                </tr>
            </table>
        </form>
    </div>
    <?php
}

function bravo_fetch_movie_info($id, $key) {
    $raw=file_get_contents_curl('http://www.omdbapi.com/?i='.$id.'&apikey='.$key);
    $info=json_decode($raw,true);
    return $info;
}

function bravo_info_box($id) {
    $key = get_option('omdbapikey');

    // For debug purposes (for shortcodes)
    $id=$id['id'];

    if(empty($id)) {
        return '<b>No IMDB ID passed<b>';
    } elseif (empty($key)) {
        return '<b>You need to set up your OMDB API Key first!</b>';
    }

    $info = bravo_fetch_movie_info($id, $key);
    if($info['Response']=='True') {
        $out='
        <table class="bravo-infobox">
            <tr>
                <th colspan="2" scope="col">'.$info['Title'].' ('.$info['Year'].')</th>
            </tr>
            <tr>
                <td class="bravo-img"><img src="'.$info['Poster'].'" alt="'.$info['Title'].' poster" /></td>
                <td><b>Rating:</b> '.$info['imdbRating'].'/10 ('.$info['imdbVotes'].' votes)<br><b>Director:</b> '.$info['Director'].'<br><b>Writer:</b> '.$info['Writer'].'<br><b>Stars:</b> '.$info['Actors'].'<br><b>Runtime:</b> '.$info['Runtime'].'<br><b>Rated:</b> '.$info['Rated'].'<br><b>Genre:</b> '.$info['Genre'].'<br><b>Released:</b> '.$info['Released'].'</td>
            </tr>
            <tr>
                <td colspan="2"><b>Plot:</b> '.$info['Plot'].'</td>
            </tr>
        </table>';
    } else {
        $out='Error: '.$info['Error'];
    }
    return $out;
}

add_action('admin_init', 'bravo_setting' );
add_action('admin_menu', 'bravo_menu');
add_action('wp_enqueue_scripts', 'bravo_style');
register_activation_hook(__FILE__, 'bravo_activate');
register_deactivation_hook(__FILE__, 'bravo_deactivate');

// For debug purposes (for using a shortcode)
add_shortcode('bravo','bravo_info_box');
?>