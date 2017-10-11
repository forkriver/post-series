<?php
/**
 * Main class file.
 *
 * @package post-series
 */

/**
 * Core class.
 *
 * @since 1.0.0
 */
class Post_Series {

	const PREFIX = '_pjps_';

	const TAXONOMY_NAME = 'pj_series';

	/**
	 * Class constructor.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	function __construct() {
		add_action( 'init', array( $this, 'register_taxonomy' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'load_styles' ) );

		add_filter( 'the_content', array( $this, 'series_bar' ) );
		add_filter( 'pj_series_bar_title', array( $this, 'series_bar_title' ) );
	}

	/**
	 * Registers the "pj_series" taxonomy.
	 *
	 * @return void
	 */
	function register_taxonomy() {

		$labels = array(
			'name'                  => _x( 'Series', 'Taxonomy Series', 'pj-series' ),
			'singular_name'         => _x( 'Series', 'Taxonomy Series', 'pj-series' ),
			'search_items'          => __( 'Search Series', 'pj-series' ),
			'popular_items'         => __( 'Popular Series', 'pj-series' ),
			'all_items'             => __( 'All Series', 'pj-series' ),
			'parent_item'           => __( 'Parent Series', 'pj-series' ),
			'parent_item_colon'     => __( 'Parent Series', 'pj-series' ),
			'edit_item'             => __( 'Edit Series', 'pj-series' ),
			'update_item'           => __( 'Update Series', 'pj-series' ),
			'add_new_item'          => __( 'Add New Series', 'pj-series' ),
			'new_item_name'         => __( 'New Series Name', 'pj-series' ),
			'add_or_remove_items'   => __( 'Add or remove Series', 'pj-series' ),
			'choose_from_most_used' => __( 'Choose from most used Series', 'pj-series' ),
			'menu_name'             => __( 'Series', 'pj-series' ),
		);

		$args = array(
			'labels'            => $labels,
			'public'            => true,
			'show_in_nav_menus' => true,
			'show_admin_column' => true,
			'hierarchical'      => false,
			'show_tagcloud'     => true,
			'show_ui'           => true,
			'query_var'         => true,
			'rewrite'           => true,
			'query_var'         => true,
			'capabilities'      => array(),
		);

		register_taxonomy(
			apply_filters( 'pj_series_taxonomy_slug', self::TAXONOMY_NAME ),
			apply_filters( 'pj_series_taxonomy_post_types', array( 'post' ) ),
			$args
		);
	}

	/**
	 * Add the Series bar to the end of the content.
	 *
	 * @param string $content The post content.
	 * @return string The filtered content.
	 * @since 1.0.0
	 */
	function series_bar( $content ) {
		global $post;
		$serii = wp_get_post_terms( $post->ID, apply_filters( 'pj_series_taxonomy_slug', self::TAXONOMY_NAME ) );
		if ( ! empty( $serii ) ) {
			foreach ( $serii as $series ) {
				$args = array(
					'posts_per_page' => 100,
					'post_type'      => $post->post_type,
					'orderby'        => 'post_date',
					'order'          => 'ASC',
					'tax_query'      => array(
						array(
							'taxonomy' => apply_filters( 'pj_series_taxonomy_slug', self::TAXONOMY_NAME ),
							'field'    => 'term_id',
							'terms'    => $series->term_id,
						),
					),
				);
				$posts_in_series = get_posts( $args );
				// Don't bother if there's only one in the series.
				if ( count( $posts_in_series ) > 1 ) {
					$content .= "<div class='pj-series-bar' id='pj-series-{$series->slug}'>" . PHP_EOL;
					$content .= "<h2>This post is one of a series: <span class='series-name'>{$series->name}</span>.</h2>" . PHP_EOL;
					$content .= 'The entire series: ';
					foreach ( $posts_in_series as $p ) {
						if ( $p->ID != $post->ID ) {
							$content .= '<a href="' . get_permalink( $p->ID ) . '">' . apply_filters( 'pj_series_bar_title', $p->post_title ) . '</a>';
						} else {
							$content .= '<span class="pj-series-bar-current">' . apply_filters( 'pj_series_bar_title', $p->post_title ) . '</span>';
						}
						if ( end( $posts_in_series ) === $p ) {
							$content .= '.';
						} else {
							$content .= '; ';
						}
					}
					$content .= "</div> <!-- .pj-series-bar #pj-series-{$series->slug} -->" . PHP_EOL;
				}
			}
		}
		return $content;
	}

	/**
	 * Removes the terminal punctuation from the title in the series bar.
	 *
	 * @param string $title The post title.
	 * @return string The filtered title.
	 * @since 1.0.0
	 */
	function series_bar_title( $title ) {
		// Remove closing punctuation from the title.
		$title = preg_replace( '/[\.,?!:;…]+$/', '', $title );
		return $title;
	}

	/**
	 * Loads the CSS for the plugin.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	function load_styles() {
		$handle = 'pj-series';
		$src = plugins_url( 'css/pj-series.css', __FILE__ );
		wp_enqueue_style( $handle, $src );
	}

}

new Post_Series;
