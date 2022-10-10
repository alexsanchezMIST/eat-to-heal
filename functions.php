<?php

$composer_autoload = __DIR__ . '/vendor/autoload.php';
if ( file_exists( $composer_autoload ) ) {
	require_once $composer_autoload;
	$timber = new Timber\Timber();
}

if ( ! class_exists( 'Timber' ) ) {

	add_action(
		'admin_notices',
		function() {
			echo '<div class="error"><p>Timber not activated. Make sure you activate the plugin in <a href="' . esc_url( admin_url( 'plugins.php#timber' ) ) . '">' . esc_url( admin_url( 'plugins.php' ) ) . '</a></p></div>';
		}
	);

	add_filter(
		'template_include',
		function( $template ) {
			return get_stylesheet_directory() . '/static/no-timber.html';
		}
	);
	return;
}

Timber::$dirname = array( 'templates', 'views' );

Timber::$autoescape = false;

class StarterSite extends Timber\Site {
	public function __construct() {
		add_action( 'after_setup_theme', array( $this, 'theme_supports' ) );
		add_filter( 'timber/context', array( $this, 'add_to_context' ) );
		add_filter( 'timber/twig', array( $this, 'add_to_twig' ) );
		add_action( 'init', array( $this, 'register_post_types' ) );
		add_action( 'init', array( $this, 'register_taxonomies' ) );
		add_action ('acf/init', [$this, 'my_acf_init']); 
		parent::__construct();
	}

	public function my_acf_init() {
		// Bail out if function doesn't exist.
		if ( ! function_exists('acf_register_block')) {
			return;
		}
	
		// Register a new block.
		$content = [
			'name' => 'acf/content',
			'title' => __('Content', 'eat-to-heal'),
			'description' => __('A custom content block.', 'eat-to-heal'),
			'render_callback' => [$this, 'my_acf_block_render_callback'],
			'category' => 'formatting',
			'icon' => 'block-default',
			'keywords' => array('content'),
			'mode' => 'edit',
			'supports' => array('mode' => false),
		];

		$cta = [
			'name' => 'acf/cta',
			'title' => __('CTA', 'eat-to-heal'),
			'description' => __('A custom call-to-action block.', 'eat-to-heal'),
			'render_callback' => [$this, 'my_acf_block_render_callback'],
			'category' => 'formatting',
			'icon' => 'block-default',
			'keywords' => array('cta'),
			'mode' => 'edit',
			'supports' => array('mode' => false),
		];

		$disclaimer = [
			'name' => 'acf/disclaimer',
			'title' => __('Disclaimer', 'eat-to-heal'),
			'description' => __('A custom disclaimer block.', 'eat-to-heal'),
			'render_callback' => [$this, 'my_acf_block_render_callback'],
			'category' => 'formatting',
			'icon' => 'block-default',
			'keywords' => array('disclaimer'),
			'mode' => 'edit',
			'supports' => array('mode' => false),
		];

		$logos = [
			'name' => 'acf/logos',
			'title' => __('Logos', 'eat-to-heal'),
			'description' => __('A custom logo block.', 'eat-to-heal'),
			'render_callback' => [$this, 'my_acf_block_render_callback'],
			'category' => 'formatting',
			'icon' => 'block-default',
			'keywords' => array('logos'),
			'mode' => 'edit',
			'supports' => array('mode' => false),
		];

		$media = [
			'name' => 'acf/media',
			'title' => __('Media', 'eat-to-heal'),
			'description' => __('A custom media block with an image on the left.', 'eat-to-heal'),
			'render_callback' => [$this, 'my_acf_block_render_callback'],
			'category' => 'formatting',
			'icon' => 'block-default',
			'keywords' => array('media'),
			'mode' => 'edit',
			'supports' => array('mode' => false),
		];

		$testimonials = [
			'name' => 'acf/testimonials',
			'title' => __('Testimonials', 'eat-to-heal'),
			'description' => __('A custom testimonial block.', 'eat-to-heal'),
			'render_callback' => [$this, 'my_acf_block_render_callback'],
			'category' => 'formatting',
			'icon' => 'block-default',
			'keywords' => array('testimonials'),
			'mode' => 'edit',
			'supports' => array('mode' => false),
		];
	
		$blocks = [
			$content,
			$cta,
			$disclaimer,
			$logos,
			$media,
			$testimonials
		];
	
		foreach ($blocks as $block) {
			acf_register_block_type($block);
		}
	
	}
	
	/**
	 * This is the callback that displays the block.
	 * 
	 * @param array $block The block settings and attributes.
	 * @param string $content The block content (empty string),
	 * @param bool $is_preview True during AJAX preview.
	 */
	
	 public function my_acf_block_render_callback( $block, $content = '', $is_preview = false) {
		$context = Timber::context();
	
		// Store block values.
		$context['block'] = $block;
	
		// Store field values.
		$context['fields'] = get_fields();
	
		// Store $is_preview value.
		$context['is_preview'] = $is_preview;
	
		// Render the block.
		Timber::render('partials/' . strtolower($block['title']) . '.twig', $context);
	 }

	public function register_post_types() {
	}
	public function register_taxonomies() {

	}

	public function add_to_context( $context ) {
		$post_args = array(
			'post_type' => 'post',
			'post_status' => 'publish',
		);

		$resource_args = array(
			'post_type' => 'resource',
			'post_status' => 'publish',
			'orderby' => 'rand',
			'nopaging' => true,
		);

		$testimonial_args = array(
			'numberposts' => -1,
			'post_type' => 'testimonial',
			'post_status' => 'publish',
			'orderby' => 'rand',
			'nopaging' => true,
		);

		$class_testimonial_args = array(
			'numberposts' => -1,
			'post_type' => 'testimonial',
			'post_status' => 'publish',
			'orderby' => 'rand',
			'nopaging' => true,
			'meta_key' => 'is_class_testimonial',
			'meta_value' => true,
		);

		$context['posts'] = new Timber\PostQuery($post_args);
		$context['resources'] = new Timber\PostQuery($resource_args);
		$context['testimonials'] = new Timber\PostQuery($testimonial_args);
		$context['class_testimonials'] = new Timber\PostQuery($class_testimonial_args);
		$context['menu']  = new Timber\Menu(143);
		$context['footer_links_menu'] = new Timber\Menu(144);
		$context['footer_services_menu'] = new Timber\Menu(145);
		$context['footer_legal_menu'] = new Timber\Menu(146);
		$context['site']  = $this;
		return $context;
	}

	public function theme_supports() {
		add_theme_support( 'automatic-feed-links' );
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );

		add_theme_support(
			'html5',
			array(
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
			)
		);

		add_theme_support(
			'post-formats',
			array(
				'aside',
				'image',
				'video',
				'quote',
				'link',
				'gallery',
				'audio',
			)
		);

		add_theme_support( 'menus' );
	}

	public function add_to_twig( $twig ) {
		$twig->addExtension( new Twig\Extension\StringLoaderExtension() );
		return $twig;
	}

}

new StarterSite();
