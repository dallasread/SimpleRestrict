<?php
/*

Plugin Name: SimpleRestrict
Plugin URI: http://WPSimpleRestrict.com
Description: SimpleRestrict is a super-simple way to restrict pages and page content by user roles
Version: 1.0.2
Contributors: dallas22ca
Author: Dallas Read
Author URI: http://www.DallasRead.com
Text Domain: simplerestrict
Requires at least: 3.6
Tested up to: 4.0.1
Stable tag: trunk
License: MIT

Copyright (c) 2014 Dallas Read.

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
*/

/*
ini_set("display_errors",1);
ini_set("display_startup_errors",1);
error_reporting(-1);
*/

class SimpleRestrict {
  public static $simplerestrict_instance;
    const version = "1.0.2";
    const debug = false;

  public static function init() {
    if ( is_null( self::$simplerestrict_instance ) ) { self::$simplerestrict_instance = new SimpleRestrict(); }
    return self::$simplerestrict_instance;
  }

  private function __construct() {
        add_action( "template_redirect", array($this, "redirect_if_necessary") );
        add_action( "send_headers" , array($this, "no_cache_headers") );
        add_action( "add_meta_boxes" , array($this, "add_meta_box") );
        add_action( "save_post", array($this, "save_meta_box") );
        add_shortcode( "restrict", array($this, "restrict_shortcode") );
  }
    
    public static function no_cache_headers() {
        if ( !is_user_logged_in() ) {
            nocache_headers();
        }
    }
    
    public static function check_role_for_post($post, $role) {
        $is_role = false;
        
        if (is_numeric($role)) {
            $user = isset($user) ? $user : wp_get_current_user();
            $is_role = $user->ID == (int) $role;
        } else if ($role == 'post_author' || $role == 'page_author') {
            $user = isset($user) ? $user : wp_get_current_user();
            $is_role = $user->ID == $post->post_author;
        } else if ($role == 'user_ids') {
            $user = isset($user) ? $user : wp_get_current_user();
            $user_ids = get_post_meta( $post->ID, "restrict_user_ids", true );
            $user_ids = array_map('trim', explode(',', $user_ids));
            $is_role = in_array($user->ID, $user_ids);
        } else if ($role == 'public') {
            $is_role = !is_user_logged_in();
        } else {
            $user = isset($user) ? $user : wp_get_current_user();
            $is_role = $user->roles[0] == $role;
        }
        
        return $is_role;
    }
    
    public static function redirect_if_necessary() {
        global $post;
        
        $has_access = false;
        $page_roles = get_post_meta( $post->ID, "restrict_roles", true );
        
        foreach ($page_roles as $role) {
            if (SimpleRestrict::check_role_for_post($post, $role)) {
                return;
            }
        }
        
        if (!$has_access) {
            $redirect = get_post_meta( $post->ID, "restrict_roles_redirect", true );
            if (!$redirect || $redirect == "") { $redirect = home_url("/"); }
            wp_redirect( $redirect );
            exit;
        }
    }
    
    public static function restrict_shortcode( $attrs, $content = null ) {
        global $post;
        $has_access = false;
        
        if (isset($attrs["only"])) {
            $roles = array_map('trim', explode(',', $attrs["only"]));
            
            foreach ($roles as $role) {
                if (SimpleRestrict::check_role_for_post($post, $role)) {
                    $has_access = true;
                    break;
                }
            }
        }
        
        if (isset($attrs["except"])) {
            $roles = array_map('trim', explode(',', $attrs["except"]));
    
            foreach ($roles as $role) {
                if (!SimpleRestrict::check_role_for_post($post, $role)) {
                    $has_access = false;
                    break;
                }
            }
        }
        
        return $has_access ? $content : "";
    }
    
    public static function add_meta_box() {
        $screens = get_post_types( array(
            "public" => true
        ), "names");

        foreach ($screens as $screen) {
            add_meta_box(
                "simplerestrict_metabox",
                "SimpleRestrict",
                array("SimpleRestrict", "meta_box"),
                $screen,
                "side",
                "low"
            );
        }
    }
    
    public static function save_meta_box( $post_id ) {
        if (isset($_POST["restrict_roles"])) {
            update_post_meta( $post_id, "restrict_roles", $_POST["restrict_roles"] );
            update_post_meta( $post_id, "restrict_roles_redirect", esc_url( $_POST["restrict_roles_redirect"] ) );
            update_post_meta( $post_id, "restrict_user_ids", preg_replace('/\s+/', '', esc_attr( $_POST["restrict_user_ids"] )) );
        }
    }
    
    public static function meta_box( $post ) {
        global $wp_roles;
        $roles = $wp_roles->get_names(); 
        $redirect = get_post_meta( $post->ID, "restrict_roles_redirect", true );
        $page_roles = get_post_meta( $post->ID, "restrict_roles", true );
        $restrict_user_ids = get_post_meta( $post->ID, "restrict_user_ids", true );
        if (!$page_roles) { $page_roles = array_keys($roles); array_push($page_roles, "public"); }
        if (!$redirect) { $redirect = home_url("/"); } ?>
        
        <p>Who is allowed see this page?</p>
        <input type="hidden" name="restrict_roles[]">
        
        <?php foreach ($roles as $key => $value) { ?>
            <input type="checkbox"<?php if (in_array($key, $page_roles)) { ?>checked="checked"<?php } ?> name="restrict_roles[]" id="restrict_roles_<?php echo $key; ?>" value="<?php echo $key; ?>"> <label for="restrict_roles_<?php echo $key; ?>"><?php echo $value; ?></label><br>
        <?php } ?>
        
        <input type="checkbox"<?php if (in_array("public", $page_roles)) { ?>checked="checked"<?php } ?> name="restrict_roles[]" id="restrict_roles_public" value="public"> <label for="restrict_roles_public">Public (Not logged in)</label><br>
        <input type="checkbox"<?php if (in_array("post_author", $page_roles)) { ?>checked="checked"<?php } ?> name="restrict_roles[]" id="restrict_roles_post_author" value="post_author"> <label for="restrict_roles_post_author"><?php echo ucfirst(get_post_type($post)); ?> Author</label><br>
        <input type="checkbox"<?php if (in_array("user_ids", $page_roles)) { ?>checked="checked"<?php } ?> name="restrict_roles[]" id="restrict_roles_user_ids" value="user_ids"> <label for="restrict_roles_user_ids">Specific User IDs</label><br>
        <input type="text" name="restrict_user_ids" class="full-width" placeholder="1, 2, 3" value="<?php echo $restrict_user_ids; ?>">
        
        <p>Where should other visitors be redirected?</p>
        <input type="text" name="restrict_roles_redirect" value="<?php echo $redirect; ?>" class="full-width">
        
        <p>
            Hint: Restrict page content with this shortcode:<br>
            <strong>[restrict only="editor,post_author,31" except="public"]Restricted content![/restrict]</strong>
        </p>
        
        <style type="text/css" media="screen">
            .full-width {
                width: 100%;
            }
        </style>
        
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                var restrictUserIDs = function restrictUserIDs() {
                    if ($('#restrict_roles_user_ids').prop('checked') === true) {
                        $('[name="restrict_user_ids"]').show();
                    } else {
                        $('[name="restrict_user_ids"]').hide();                        
                    }
                };
                
                $(document).on('click', '#restrict_roles_user_ids', restrictUserIDs);
                restrictUserIDs();
            });
        </script>
            
    <?php }
}

SimpleRestrict::init();

?>
