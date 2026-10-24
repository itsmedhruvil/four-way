<?php
/**
 * Fallback template shown only when the headless redirect is disabled
 * (for example while debugging a WordPress template).
 *
 * @package fourway-headless
 */

$fourway_app_url = fourway_headless_app_url();
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
	<style>
		body {
			margin: 0;
			min-height: 100vh;
			display: flex;
			align-items: center;
			justify-content: center;
			background: #141416;
			color: #fdfbf7;
			font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
			text-align: center;
		}
		main { max-width: 34rem; padding: 2rem; }
		h1 { font-size: 1.75rem; line-height: 1.3; margin: 0 0 0.75rem; }
		p { color: #b9b9bf; line-height: 1.7; font-size: 0.95rem; }
		a {
			display: inline-block;
			margin-top: 1.25rem;
			background: #f21d30;
			color: #fff;
			text-decoration: none;
			font-weight: 700;
			font-size: 0.8rem;
			padding: 0.8rem 1.6rem;
			border-radius: 4px;
		}
	</style>
</head>
<body>
	<main>
		<h1>4Way Safety Solutions LLP</h1>
		<p>
			This WordPress install is running in headless mode. The public homepage is
			rendered by the React application.
		</p>
		<a href="<?php echo esc_url( $fourway_app_url ); ?>">Open the React homepage</a>
	</main>
	<?php wp_footer(); ?>
</body>
</html>
