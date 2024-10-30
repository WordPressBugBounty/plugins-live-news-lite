<?php
/**
 * The basic HTML for displaying the options page. Note that this page is then powered by React.
 *
 * @package live-news-lite
 */

/**
 * This class adds the options with the related callbacks and validations.
 */
class Daextlnl_Options_Menu_Elements extends daextlnl_Menu_Elements {

	/**
	 * Constructor.
	 *
	 * @param object $shared The shared class.
	 * @param string $page_query_param The page query parameter.
	 * @param string $config The config parameter.
	 */
	public function __construct( $shared, $page_query_param, $config ) {

		parent::__construct( $shared, $page_query_param, $config );

		$this->menu_slug      = 'options';
		$this->slug_plural    = 'options';
		$this->label_singular = __( 'Options', 'live-news-lite');
		$this->label_plural   = __( 'Options', 'live-news-lite');
	}

	/**
	 * Display the body content.
	 *
	 * @return void
	 */
	public function display_custom_content() {

		?>

		<div class="wrap">

			<div id="react-root"></div>

		</div>

		<?php
	}
}
