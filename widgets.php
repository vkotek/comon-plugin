<?php
/*
 __    __ _     _            _
/ / /\ \ (_) __| | __ _  ___| |_ ___
\ \/  \/ / |/ _` |/ _` |/ _ \ __/ __|
 \  /\  /| | (_| | (_| |  __/ |_\__ \
  \/  \/ |_|\__,_|\__, |\___|\__|___/
                  |___/
*/

/* Show attachment images widget */

// register Imgs_Widget widget
function register_Imgs_Widget() {
    register_widget( 'Imgs_Widget' );
}
add_action( 'widgets_init', 'register_Imgs_Widget' );
class Imgs_Widget extends WP_Widget {


	function __construct() {
		parent::__construct(
			'Imgs_Widget', // Base ID
			__( 'COM.ON - Images from Comments', 'comon-plugin' ), // Name
			array( 'description' => __( 'Displays all image attachements from current post + ZIP download for admins', 'comon-plugin' ), ) // Args
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {

		if ( is_single() ) {

			echo $args['before_widget'];
			if ( ! empty( $instance['title'] ) ) {
				echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
			}

			$queried_object = get_queried_object();
			$post_id = $queried_object->ID;

			$post_args = array (
				'post_id'	=> $post_id,
			);

			$comment_query = new WP_Comment_Query;
			$comments = $comment_query->query( $post_args );


			// If comments then...
			if ( $comments ) {
				$imgs = array();

				// Comnent loop
				foreach ( $comments as $comment ) {
	        // Skip comments belonging to other users if private comments enabled and current user is not admin
	        if( get_field('private_comments', $post_id) &&
	            !current_user_can('edit_posts') &&
	            $comment->user_id != get_current_user_id()) {
	                continue;
	        }
					$attachmentId =  get_comment_meta($comment->comment_ID, 'attachmentId', TRUE);
					if(is_numeric($attachmentId) && !empty($attachmentId)){

						// atachement info
						$attachmentLink = wp_get_attachment_url($attachmentId);
						$attachmentThumb = wp_get_attachment_image($attachmentId, $size = 'thumbnail');
						$real_path = get_attached_file( $attachmentId );
						$imgs[] = array($attachmentLink,$attachmentThumb,$real_path);
					}
				}



				// If there are images in array, print title and thumbs
				if($imgs) {
					foreach ($imgs as $img) {
						printf ("<a href=\"%s\">%s</a>",$img[0],$img[1]);
					}
					if ( current_user_can('edit_posts') ) {
						$zip_make = zipfile_url . "/zip-make.php?p=" . $post_id;
						printf ( "<h5><a href=\"%s\"><i class=\"fa fa-download\"></i> %s</a></h5>", $zip_make, __('Download', 'comon-plugin'));
					}
				}
			}
			echo $args['after_widget'];
		}
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : 'New title';
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>">Title:</label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<?php
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

		return $instance;
	}


}


/* Custom posts widget */

// register Posts_Widget widget
function register_Posts_Widget() {
    register_widget( 'Posts_Widget' );
}
add_action( 'widgets_init', 'register_Posts_Widget' );
class Posts_Widget extends WP_Widget {

	function __construct() {
		parent::__construct(
			'Posts_Widget', // Base ID
			__( 'COM.ON - Custom Posts', 'comon-plugin' ), // Name
			array( 'description' => __( 'Displays posts according to user\'s details', 'comon-plugin' ), ) // Args
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		echo $args['before_widget'];
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
		}
		echo "<ul>";
		do_shortcode('[active_posts]');
		echo "</ul>";
		echo $args['after_widget'];
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'New title', 'text_domain' );
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<?php
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

		return $instance;
	}

} // class Posts_Widget
