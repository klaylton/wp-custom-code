<?php
class Meta_Box_CSS_JS_Custom {

    private $screen = array( 'post','page' );
    
    public function add_meta_boxes() {
	
		foreach ( $this->screen as $single_screen ) {
			add_meta_box(
				'custom_code_admin_metabox',
				esc_html__( 'WP Custom Code', 'wp-custom-code' ),
				array( $this, 'meta_box_callback' ),
				$single_screen,
				'normal',
				'high'
			);
		}
	
	}
    
	public function meta_box_callback( $post ) {
	
		wp_nonce_field( 'custom_code_admin_metabox_data', 'custom_code_admin_metabox_nonce' );
		esc_html_e( 'Enter your code in the field corresponding to the language.', 'wp-custom-code' );
		$this->field_generator( $post );
	
    }
    
    public function meta_fields() {
		
        return
			array(

                // CSS
				array(
					'label' => esc_html__( 'CSS Custom', 'wp-custom-code' ),
					'id' => 'wp_custom_css',
					'placeholder' => esc_html__( 'Enter your code in the field corresponding to the language.', 'wp-custom-code' ),
					'type' => 'textarea',
                ),
                
                // JavaScript
                array(
					'label' => esc_html__( 'JavaScript Custom', 'wp-custom-code' ),
					'id' => 'wp_custom_js',
					'placeholder' => esc_html__( '', 'wp-custom-code' ),
					'type' => 'textarea',
                ),
                
				// javascript externo
                array(
					'label' => esc_html__( 'CSS and JavaScript URL Custom', 'wp-custom-code' ),
					'id' => 'wp_custom_js_external',
					'placeholder' => esc_html__( 'https://www.site.com/js/script.js', 'wp-custom-code' ),
					'type' => 'textarea',
                ),


			);

	}
    
	public function field_generator( $post ) {

		$output = '';

		foreach ( $this->meta_fields() as $meta_field ) {
			$label = '<label for="' . $meta_field['id'] . '">' . $meta_field['label'] . '</label>';

			$meta_value = get_post_meta( $post->ID, $meta_field['id'], true );

			if ( empty( $meta_value ) ) {
				if ( isset( $meta_field['default'] ) ) {
					$meta_value = $meta_field['default'];
				} else {
					$meta_value = '';
				}
			}

			switch ( $meta_field['type'] ) {
			
				case 'textarea':
					$input = sprintf(
						'<textarea style="width: 100%%" id="%s" name="%s" rows="5" placeholder="%s">%s</textarea>',
						$meta_field['id'],
						$meta_field['id'],
						$meta_field['placeholder'],
						$meta_value
					);
					break;

				default:
					$input = sprintf(
						'<input %s id="%s" name="%s" type="%s" placeholder="%s" value="%s">',
						'style="width: 100%"',
						$meta_field['id'],
						$meta_field['id'],
						$meta_field['type'],
						isset( $meta_field['placeholder'] ) ? $meta_field['placeholder'] : '',
						$meta_value
					);
			}

			$output .= $this->format_rows( $label, $input );
		}

		echo '<table class="form-table"><tbody>' . $output . '</tbody></table>';

    }
    
	public function format_rows( $label, $input ) {

		return '<tr><th>'.$label.'</th><td>'.$input.'</td></tr>';

    }

    public function save_fields( $post_id ) {

		if ( ! isset( $_POST['custom_code_admin_metabox_nonce'] ) ) {
			return $post_id;
		}

		$nonce = $_POST['custom_code_admin_metabox_nonce'];

		if ( !wp_verify_nonce( $nonce, 'custom_code_admin_metabox_data' ) ) {
			return $post_id;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		foreach ( $this->meta_fields() as $meta_field ) {
			if ( isset( $_POST[ $meta_field['id'] ] ) ) {
				switch ( $meta_field['type'] ) {
                    case 'text':
                        $_POST[ $meta_field['id'] ] = sanitize_text_field( $_POST[ $meta_field['id'] ] );
                        break;
                    case 'textarea':
                        $_POST[ $meta_field['id'] ] = sanitize_textarea_field( $_POST[ $meta_field['id'] ] );
                        break;
                    default:
                        $_POST[ $meta_field['id'] ] = trim( $_POST[ $meta_field['id'] ] );
                }

				update_post_meta( $post_id, $meta_field['id'], $_POST[ $meta_field['id'] ] );
			} else if ( $meta_field['type'] === 'checkbox' ) {
				update_post_meta( $post_id, $meta_field['id'], '-1' );
			}
		}
		
	}

}