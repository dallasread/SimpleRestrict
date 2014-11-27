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

ini_set("display_errors",1);
ini_set("display_startup_errors",1);
error_reporting(-1);

class SimpleRestrict {
  public static $simplerestrict_instance;
	const version = "1.0.2";
	const debug = true;

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
	
	public static function redirect_if_necessary() {
		global $post;
		global $wp_roles;

		$page_roles = get_post_meta( $post->ID, "restrict_roles", true );
    
		if (is_user_logged_in()) {
			$user = wp_get_current_user();
			$user_roles = (array) $user->roles;
			$user_role = array_shift($user_roles);
		} else {
			$user_role = "public";
		}
		
		if ($page_roles && !in_array($user_role, $page_roles)) {
			$redirect = get_post_meta( $post->ID, "restrict_roles_redirect", true );
			if (!$redirect || $redirect == "") { $redirect = home_url("/"); }
			wp_redirect( $redirect );
			exit;
		}
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
    }
	}
	
	public static function meta_box( $post ) {
		global $wp_roles;
    $roles = $wp_roles->get_names(); 
		$redirect = get_post_meta( $post->ID, "restrict_roles_redirect", true );
		$page_roles = get_post_meta( $post->ID, "restrict_roles", true );
		if (!$page_roles) { $page_roles = array_keys($roles); array_push($page_roles, "public"); }
		if (!$redirect) { $redirect = home_url("/"); } ?>
		
		<p>Who is allowed see this page?</p>
		<input type="hidden" name="restrict_roles[]">
		
		<?php foreach ($roles as $key => $value) { ?>
			<input type="checkbox"<?php if (in_array($key, $page_roles)) { ?>checked="checked"<?php } ?> name="restrict_roles[]" id="restrict_roles_<?php echo $key; ?>" value="<?php echo $key; ?>"> <label for="restrict_roles_<?php echo $key; ?>"><?php echo $value; ?></label><br>
		<?php } ?>
		<input type="checkbox"<?php if (in_array("public", $page_roles)) { ?>checked="checked"<?php } ?> name="restrict_roles[]" id="restrict_roles_public" value="public"> <label for="restrict_roles_public">Public (Not logged in)</label><br>
		
		<p>Where should they be redirected?</p>
		<input type="text" name="restrict_roles_redirect" value="<?php echo $redirect; ?>" style="width: 100%; ">
		
		<p>
			Hint: Restrict page content with the <strong>[restrict only="administrator,editor" except="public"]This is restricted![/restrict]</strong> shortcode!
		</p>
			
	<?php }
	
	public static function restrict_shortcode( $attrs, $content = null ) {
		$allowed = false;
		
		if (is_user_logged_in()) {
			$user = wp_get_current_user();
			$user_roles = $user->roles;
		} else {
			$user_roles = array("public");
		}
		
		if (isset($attrs["only"])) {
			$roles = array_map('trim', explode(',', $attrs["only"]));
			
			if (array_intersect($roles, $user_roles)) {
				$allowed = true;
			} else {
				$allowed = false;
			}
		}
		
		if (isset($attrs["except"])) {
			$roles = array_map('trim', explode(',', $attrs["except"]));
			
			if (!array_intersect($roles, $user_roles)) {
				$allowed = true;
			} else {
				$allowed = false;
			}
		}
		
		return $allowed ? $content : "";
	}
}

SimpleRestrict::init();

?>
