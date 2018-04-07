<?php
/*
Plugin Name: Bravo! Plugin
Description: This is the site specific plugin for Bravo!
Version: 1
Author: Emiliano Sauvisky
Released under the MIT License
*/

/* AIN'T GOT TIME FOR FANCY DOCUMENTATION IN HERE, OKAY? */

// define('BRAVOPLUGINABSPATH', str_replace("\\","/", WP_PLUGIN_DIR . '/' . plugin_basename( dirname(__FILE__) ) . '/' ));

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
    add_options_page('Bravo! Options', 'Bravo! Options', 'manage_options', 'bravo_plugin', 'bravo_admin_option');
}

function bravo_setting() {
    register_setting('bravo_options', 'omdbapikey');
}

function bravo_style() {
    wp_register_style('bravo-style', plugins_url('/css/bravo_style.css', __FILE__));
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
        <div class="bravo-infobox row">
            <div class="col-sm-12 text-center">
                <h5>'.$info['Title'].' ('.$info['Year'].')</h5>
            </div>
            <div class="col-sm-12 vertical-align">
                <div class="bravo-img col-sm-4">
                    <img src="'.$info['Poster'].'" alt="'.$info['Title'].' poster" />
                </div>
                <div class="col-sm-8">
                    <b>Rating:</b> '.$info['imdbRating'].'/10 ('.$info['imdbVotes'].' votes)<br/>
                    <b>Stars:</b> '.$info['Actors'].'<br/>
                    <b>Genre:</b> '.$info['Genre'].'<br/>
                    <b>Released:</b> '.$info['Released'].'<br/>
                    <b>Plot:</b> '.$info['Plot'].'<br/>
                    <button id="fav-'.$info['imdbID'].'" class="btn btn-primary add-fav">Favorite</button>
                </div>
            </div>
        </div>';
    } else {
        $info_box='Error: '.$info['Error'];
    }
    return $info_box;
}

function bravo_search_page() {
    $all_meta_for_user = get_user_meta(get_current_user_id(), 'favorites');
    print_r( $all_meta_for_user );

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
    if (isset($_GET['title'])) {
        // Yes, this is a bit cheatie.
        // The clock is ticking, though...
        $query = htmlspecialchars($_GET['title'], ENT_QUOTES);
        echo '<h3>Results for ' . $query . ':</h3>';
        $results = bravo_do_search(urlencode($query));
        if ($results['Response'] == 'True') {
            foreach ($results['Search'] as $result) {
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


function bravo_submit_scripts() {
    wp_enqueue_script( 'bravo_submit', plugins_url( 'js/bravo_submit.js', __FILE__ ), array( 'jquery' ) );
    wp_localize_script( 'bravo_submit', 'PT_Ajax', array(
            'ajaxurl'   => admin_url( 'admin-ajax.php' ),
            'nextNonce' => wp_create_nonce( 'myajax-next-nonce' )
        )
    );
}

function myajax_bravoSubmit_func() {
    // check nonce
    $nonce = $_POST['nextNonce'];
    if ( ! wp_verify_nonce( $nonce, 'myajax-next-nonce' ) ) {
        die ( 'No naughty bussiness!' );
    }

    // generate the response
    $response = json_encode( $_POST );
    header( "Content-Type: application/json" );
    echo $response;

    // things
    $imdb_id = json_decode($response, true)['imdbID'];
    $user_id = get_current_user_id();
    $user_favs = get_user_meta($user_id, 'favorites');

    // If movie is not already a favorite, add it
    if (!in_array($imdb_id, $user_favs)) {
        add_user_meta( $user_id, 'favorites', $imdb_id);
    }
    // TODO: else, remove it (with the proper button update on the .js)

    //delete_user_meta($user_id, 'favorites');

    // IMPORTANT: don't forget to "exit"
    exit;
}

add_action('admin_init', 'bravo_setting' );
add_action('admin_menu', 'bravo_menu');
add_action('wp_enqueue_scripts', 'bravo_style');
add_action('wp_enqueue_scripts', 'bravo_submit_scripts');
add_action('wp_ajax_ajax-bravoSubmit', 'myajax_bravoSubmit_func');
register_activation_hook(__FILE__, 'bravo_activate');
register_deactivation_hook(__FILE__, 'bravo_deactivate');
add_shortcode('bravo_search', 'bravo_search_page');

?>