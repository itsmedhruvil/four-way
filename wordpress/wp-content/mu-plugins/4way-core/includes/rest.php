<?php
/**
 * REST API consumed by the React frontend.
 *
 * @package fourway
 */

defined( 'ABSPATH' ) || exit;

/**
 * Shape a trust badge for the frontend.
 *
 * @param WP_Post $post Badge post.
 * @return array
 */
function fourway_prepare_badge( $post ) {
	return array(
		'icon'        => fourway_meta( $post->ID, 'icon', 'fa-solid fa-shield-halved' ),
		'title'       => fourway_title( $post ),
		'description' => fourway_meta( $post->ID, 'description', '' ),
	);
}

/**
 * Shape a service card.
 *
 * @param WP_Post $post Service post.
 * @return array
 */
function fourway_prepare_service( $post ) {
	return array(
		'slug'        => $post->post_name,
		'title'       => fourway_title( $post ),
		'description' => fourway_plain_text( fourway_meta( $post->ID, 'description', fourway_excerpt( $post ) ) ),
		'image'       => fourway_image_url( $post->ID ),
		'imageAlt'    => fourway_image_alt( $post->ID, fourway_title( $post ) ),
		'href'        => fourway_meta( $post->ID, 'href', '#contact' ),
	);
}

/**
 * Shape a project card.
 *
 * @param WP_Post $post Project post.
 * @return array
 */
function fourway_prepare_project( $post ) {
	return array(
		'slug'        => $post->post_name,
		'category'    => fourway_meta( $post->ID, 'category', '' ),
		'title'       => fourway_title( $post ),
		'description' => fourway_plain_text( fourway_meta( $post->ID, 'description', fourway_excerpt( $post ) ) ),
		'image'       => fourway_image_url( $post->ID ),
		'imageAlt'    => fourway_image_alt( $post->ID, fourway_title( $post ) ),
		'href'        => fourway_meta( $post->ID, 'href', '#contact' ),
	);
}

/**
 * Shape a testimonial.
 *
 * @param WP_Post $post Testimonial post.
 * @return array
 */
function fourway_prepare_testimonial( $post ) {
	return array(
		'quote'  => fourway_plain_text( fourway_meta( $post->ID, 'quote', fourway_excerpt( $post ) ) ),
		'name'   => fourway_title( $post ),
		'role'   => fourway_meta( $post->ID, 'role', '' ),
		'rating' => (int) fourway_meta( $post->ID, 'rating', 5 ),
		'avatar' => fourway_meta( $post->ID, 'avatar_url', '' ),
	);
}

/**
 * Shape an FAQ row.
 *
 * @param WP_Post $post FAQ post.
 * @return array
 */
function fourway_prepare_faq( $post ) {
	return array(
		'question' => fourway_title( $post ),
		'answer'   => fourway_meta( $post->ID, 'answer', '' ),
		'open'     => (bool) fourway_meta( $post->ID, 'open', false ),
	);
}

/**
 * Shape a process step.
 *
 * @param WP_Post $post Step post.
 * @return array
 */
function fourway_prepare_step( $post ) {
	return array(
		'number'      => fourway_meta( $post->ID, 'number', '01' ),
		'icon'        => fourway_meta( $post->ID, 'icon', 'fa-solid fa-clipboard-list' ),
		'title'       => fourway_title( $post ),
		'description' => fourway_meta( $post->ID, 'description', '' ),
	);
}

/**
 * Shape a "why choose us" card.
 *
 * @param WP_Post $post Reason post.
 * @return array
 */
function fourway_prepare_reason( $post ) {
	return array(
		'icon'        => fourway_meta( $post->ID, 'icon', 'fa-solid fa-user-tie' ),
		'title'       => fourway_title( $post ),
		'description' => fourway_meta( $post->ID, 'description', '' ),
		'column'      => fourway_meta( $post->ID, 'column', 'left' ),
	);
}

/**
 * Shape a blog card.
 *
 * @param WP_Post $post Blog post.
 * @return array
 */
function fourway_prepare_post( $post ) {
	$categories = get_the_category( $post->ID );
	$category   = ! empty( $categories ) ? $categories[0]->name : 'FIRE SAFETY';

	return array(
		'slug'     => $post->post_name,
		'category' => strtoupper( $category ),
		'date'     => fourway_formatted_date( $post ),
		'title'    => fourway_title( $post ),
		'excerpt'  => fourway_excerpt( $post ),
		'image'    => get_the_post_thumbnail_url( $post->ID, 'large' )
			? get_the_post_thumbnail_url( $post->ID, 'large' )
			: (string) get_post_meta( $post->ID, '_fourway_image_url', true ),
		'imageAlt' => fourway_image_alt( $post->ID, fourway_title( $post ) ),
		'href'     => apply_filters( 'fourway_post_url', '#contact', $post ),
	);
}

/**
 * Aggregate everything the homepage needs into one request.
 *
 * @return WP_REST_Response
 */
function fourway_rest_home() {
	$payload = array(
		'settings'     => fourway_get_settings(),
		'badges'       => array_map( 'fourway_prepare_badge', fourway_get_items( 'trust_badge' ) ),
		'services'     => array_map( 'fourway_prepare_service', fourway_get_items( 'service' ) ),
		'reasons'      => array_map( 'fourway_prepare_reason', fourway_get_items( 'reason' ) ),
		'steps'        => array_map( 'fourway_prepare_step', fourway_get_items( 'process_step' ) ),
		'projects'     => array_map( 'fourway_prepare_project', fourway_get_items( 'project' ) ),
		'testimonials' => array_map( 'fourway_prepare_testimonial', fourway_get_items( 'testimonial' ) ),
		'faqs'         => array_map( 'fourway_prepare_faq', fourway_get_items( 'faq' ) ),
		'posts'        => array_map( 'fourway_prepare_post', fourway_get_items( 'post', 3, array( 'date' => 'DESC' ) ) ),
	);

	$response = new WP_REST_Response( $payload, 200 );
	$response->header( 'X-4Way-Content', 'headless' );

	return $response;
}

/**
 * Very small IP based throttle shared by the public POST endpoints.
 *
 * @param string $bucket Bucket name.
 * @param int    $limit  Max requests per window.
 * @param int    $window Window length in seconds.
 * @return true|WP_Error
 */
function fourway_throttle( $bucket, $limit = 8, $window = 600 ) {
	$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'unknown';
	$key = 'fourway_throttle_' . md5( $bucket . '|' . $ip );
	$hit = (int) get_transient( $key );

	if ( $hit >= $limit ) {
		return new WP_Error(
			'fourway_rate_limited',
			'Too many requests. Please try again shortly.',
			array( 'status' => 429 )
		);
	}

	set_transient( $key, $hit + 1, $window );

	return true;
}

/**
 * POST /4way/v1/contact — store a quote request.
 *
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response|WP_Error
 */
function fourway_rest_contact( WP_REST_Request $request ) {
	$throttle = fourway_throttle( 'contact' );
	if ( is_wp_error( $throttle ) ) {
		return $throttle;
	}

	$name     = sanitize_text_field( (string) $request->get_param( 'name' ) );
	$email    = sanitize_email( (string) $request->get_param( 'email' ) );
	$phone    = sanitize_text_field( (string) $request->get_param( 'phone' ) );
	$facility = sanitize_text_field( (string) $request->get_param( 'facility' ) );
	$message  = sanitize_textarea_field( (string) $request->get_param( 'message' ) );
	$source   = sanitize_text_field( (string) $request->get_param( 'source' ) );

	if ( '' === $name || '' === $message ) {
		return new WP_Error(
			'fourway_missing_fields',
			'Please share your name and requirement.',
			array( 'status' => 400 )
		);
	}

	if ( ! is_email( $email ) ) {
		return new WP_Error(
			'fourway_invalid_email',
			'Please provide a valid email address.',
			array( 'status' => 400 )
		);
	}

	$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? (string) wp_unslash( $_SERVER['REMOTE_ADDR'] ) : '';
	$row = array(
		'name'     => $name,
		'email'    => $email,
		'phone'    => $phone,
		'facility' => $facility,
		'message'  => $message,
		'source'   => $source ? $source : 'homepage',
		'ip_hash'  => $ip ? hash( 'sha256', $ip ) : '',
	);

	$lead_id = fourway_insert_lead( $row );

	if ( ! $lead_id ) {
		return new WP_Error(
			'fourway_lead_failed',
			'We could not store your request. Please try again.',
			array( 'status' => 500 )
		);
	}

	/**
	 * Fires after a quote request has been stored.
	 *
	 * @param int   $lead_id Inserted row ID.
	 * @param array $row     Lead data.
	 */
	do_action( 'fourway_lead_created', $lead_id, $row );

	return new WP_REST_Response(
		array(
			'ok'      => true,
			'id'      => $lead_id,
			'message' => 'Thank you — our fire safety engineer will contact you within one working day.',
		),
		201
	);
}

/**
 * POST /4way/v1/subscribe — store a newsletter subscriber.
 *
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response|WP_Error
 */
function fourway_rest_subscribe( WP_REST_Request $request ) {
	$throttle = fourway_throttle( 'subscribe', 15 );
	if ( is_wp_error( $throttle ) ) {
		return $throttle;
	}

	$email = sanitize_email( (string) $request->get_param( 'email' ) );

	if ( ! is_email( $email ) ) {
		return new WP_Error(
			'fourway_invalid_email',
			'Please provide a valid email address.',
			array( 'status' => 400 )
		);
	}

	if ( ! fourway_insert_subscriber( $email ) ) {
		return new WP_Error(
			'fourway_subscribe_failed',
			'Subscription failed. Please try again.',
			array( 'status' => 500 )
		);
	}

	return new WP_REST_Response(
		array(
			'ok'      => true,
			'message' => 'Thank you for subscribing!',
		),
		201
	);
}


/**
 * Register the public routes.
 */
function fourway_register_rest_routes() {
	register_rest_route(
		'4way/v1',
		'/home',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'fourway_rest_home',
			'permission_callback' => '__return_true',
		)
	);

	register_rest_route(
		'4way/v1',
		'/settings',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => static function () {
				return new WP_REST_Response( fourway_get_settings(), 200 );
			},
			'permission_callback' => '__return_true',
		)
	);

	register_rest_route(
		'4way/v1',
		'/contact',
		array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => 'fourway_rest_contact',
			'permission_callback' => '__return_true',
			'args'                => array(
				'name'     => array(
					'required'          => true,
					'sanitize_callback' => 'sanitize_text_field',
				),
				'email'    => array(
					'required'          => true,
					'sanitize_callback' => 'sanitize_email',
				),
				'phone'    => array( 'sanitize_callback' => 'sanitize_text_field' ),
				'facility' => array( 'sanitize_callback' => 'sanitize_text_field' ),
				'message'  => array(
					'required'          => true,
					'sanitize_callback' => 'sanitize_textarea_field',
				),
				'source'   => array( 'sanitize_callback' => 'sanitize_text_field' ),
			),
		)
	);

	register_rest_route(
		'4way/v1',
		'/subscribe',
		array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => 'fourway_rest_subscribe',
			'permission_callback' => '__return_true',
			'args'                => array(
				'email' => array(
					'required'          => true,
					'sanitize_callback' => 'sanitize_email',
				),
			),
		)
	);
}
add_action( 'rest_api_init', 'fourway_register_rest_routes' );

/**
 * Notify the site inbox about a new quote request.
 *
 * @param int   $lead_id Row ID.
 * @param array $lead    Lead data.
 */
function fourway_notify_new_lead( $lead_id, array $lead ) {
	if ( ! apply_filters( 'fourway_send_lead_email', true, $lead ) ) {
		return;
	}

	$subject = sprintf( '[%s] New fire safety quote request (#%d)', wp_specialchars_decode( get_bloginfo( 'name' ) ), $lead_id );
	$body    = "A new quote request was submitted from the website.\n\n"
		. 'Name: ' . $lead['name'] . "\n"
		. 'Email: ' . $lead['email'] . "\n"
		. 'Phone: ' . $lead['phone'] . "\n"
		. 'Facility: ' . $lead['facility'] . "\n\n"
		. $lead['message'] . "\n\n"
		. 'Manage: ' . admin_url( 'admin.php?page=fourway-leads' );

	wp_mail( get_option( 'admin_email' ), $subject, $body );
}
add_action( 'fourway_lead_created', 'fourway_notify_new_lead', 10, 2 );

