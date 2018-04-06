<?php
/*
Plugin Name: Bravo! Plugin
Description: This is the site specific plugin for Bravo!
Version: 1
Author: Emiliano Sauvisky
Released under the MIT License
*/

/* AIN'T GOT TIME FOR FANCY DOCUMENTATION IN HERE, OKAY? */

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

function bravo_fetch_movie_info($id, $key) {
    $raw=file_get_contents_curl('http://www.omdbapi.com/?i='.$id.'&apikey='.$key);
    $info=json_decode($raw,true);
    return $info;
}

function bravo_do_search($title) {
    $key = get_option('omdbapikey');
    $raw=file_get_contents_curl('http://www.omdbapi.com/?s='.$title.'&apikey='.$key);
    $results=json_decode($raw,true);
    // TODO: do something about the pagination *here*
    return $results;
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

function bravo_info_box($id) {
    $key = get_option('omdbapikey');

    // For debug purposes (for the [bravo id=''] shortcode)
    if (is_array($id)) {
        $id=$id['id'];
    }

    if(empty($id)) {
        return '<b>No IMDB ID passed<b>';
    } elseif (empty($key)) {
        return '<b>You need to set up your OMDB API Key first!</b>';
    }

    $info = bravo_fetch_movie_info($id, $key);
    if($info['Response'] == 'True') {
        $info_box='
        <table class="bravo-infobox">
            <tr>
                <th colspan="2" scope="col">'.$info['Title'].' ('.$info['Year'].')</th>
            </tr>
            <tr>
                <td class="bravo-img"><img src="'.$info['Poster'].'" alt="'.$info['Title'].' poster" /></td>
                <td>
                    <b>Rating:</b> '.$info['imdbRating'].'/10 ('.$info['imdbVotes'].' votes)<br/>
                    <b>Stars:</b> '.$info['Actors'].'<br/>
                    <b>Genre:</b> '.$info['Genre'].'<br/>
                    <b>Released:</b> '.$info['Released'].'<br/>
                    <b>Plot:</b> '.$info['Plot'].'
                </td>
            </tr>
        </table>';
    } else {
        $info_box='Error: '.$info['Error'];
    }
    return $info_box;
}

function bravo_search_page() {
    ?>
    <form role="search" method="get" class="search-form form-group" action="/search">
        <label class="label-floating is-empty form-group">
            <span class="screen-reader-text">Search for:</span>
            <label class="control-label"> Search … </label>
            <input type="search" class="search-field" placeholder="Search &hellip;" value="" name="title" />
        </label>
        <input type="submit" class="search-submit" value="Search">
    </form>
    <?php
    if ($_GET['title']) {
        // Yes, this is pretty much cheating.
        // The clock is ticking, though...
        $query = htmlspecialchars($_GET['title'], ENT_QUOTES);
        //$query = $_GET['title'];
        echo '<h3>Results for ' . $query . ':</h3>';
        $results = bravo_do_search(urlencode($query));
        if ($results['Response'] == 'True') {
            foreach ($results['Search'] as $result) {
                //echo 'result imdbid' . $result['imdbID'] . '<br/>';
                echo bravo_info_box($result['imdbID']);
            }
        } elseif ($results['Response'] == 'False') {
            echo "<strong>" . $results['Error'] . " :(</strong>";
        }
        else {
            echo "<strong>Something really bad happened.</strong> <em>What are you trying to achieve?! :D</em>";
        }

    }
}

add_action('admin_init', 'bravo_setting' );
add_action('admin_menu', 'bravo_menu');
add_action('wp_enqueue_scripts', 'bravo_style');
register_activation_hook(__FILE__, 'bravo_activate');
register_deactivation_hook(__FILE__, 'bravo_deactivate');
add_shortcode('bravo_search', 'bravo_search_page');

// For debug purposes (creates a [bravo id=''] shortcode to display a single box)
add_shortcode('bravo','bravo_info_box');

// Gave up on doin' it the right way
// <input type="hidden" name="action" value="process_form">
// add_action( 'admin_post_nopriv_process_form', 'bravo_do_search' );
// add_action( 'admin_post_process_form', 'bravo_do_search' );
?>