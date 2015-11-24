<?php

/*
Plugin Name: Almond Stock Prices
Plugin URI: http://gabrielmaldonado/
Description: Display stock prices in your website using a widget. Clean and simple. Activate it under Appearance > Widgets > Available Widgets or clicking in "Go to Widget menu" below the plugin name.
Version: 1.0
Author: Gabriel Maldonado
Author URI: http://gabrielmaldonado.me
License: GPL2
*/

/*======================================*/
/* 	WORKING FILE. WORK IN PROGRESS 		*/
/* https://github.com/IAmGabrielMA		*/
/*======================================*/

// register using widgets_init
add_action( 'widgets_init', function(){
     register_widget( 'Almond_Stock_Prices' );
});
//Hooks to 'plugin_action_links_' filter
$plugin = plugin_basename(__FILE__);
add_filter("plugin_action_links_$plugin", array('Almond_Stock_Prices', 'show_widget_link_on_activation'));

class Almond_Stock_Prices extends WP_Widget {

	/* HOW ITS SEEN UNDER APPEARANCE > WIDGETS */
	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {
		// widget actual processes
		/* DEFAULT NAME AND DESCRIPTION WHEN ITS SAW IN 'WIDGETS' */
		parent::__construct(
			'test_widget', // Base ID
			__('Almond Stock Prices', 'text_domain'), //Name
			array('description' => __('Display stock prices in your website.', 'text_domain'), ) //Args
		);
	}

	/* TRYED BUT NOT WORKING NEITHER HOW TO SAVE AND UPDATE VALUES IN THE WIDGET FORM */
	/*
	function almond_stock_prices(){
		$widget_ops = array( 'classname' => 'almond_show_stocks', 'description' => 'Display stock prices in your website.' );

		$this->options[] = array(
			'name'  => 'title', 'label' => 'Title',
			'type'	=> 'text', 	'default' => 'Stocks'
		);

		for ($i = 0; $i < 5; $i++) {
			$this->options[] = array(
				'name'	=> 'stock_' . $i,	'label'	=> 'Stock Tickers',
				'type'	=> 'text',	'default' => ''
			);
		}

		parent::WP_Widget(false, 'Show Stock Prices', $widget_ops);

	}
	*/

	/* SHOWS LINK TO WP WIDGET PAGE UNDER PLUGIN DESCRIPTION */
	/**
	 * Hooks to 'plugin_action_links_' filter
	 *
	 * @since 1.0.0
	 */
	function show_widget_link_on_activation($widget_links) {
		$widget_link_on_activation = '<a href="widgets.php">Go to Widget menu</a>';
		// array_unshift — Prepend one or more elements to the beginning of an array
		array_unshift($widget_links, $widget_link_on_activation);
		return $widget_links;
	}

	/* FRONT-END DISPLAY */
	/**
	 * Outputs the content of the widget
	 * @see WP_Widget::widget()
	 *
	 * @param array $args 		Widget arguments
	 * @param array $instance 	Saved values from database
	 */
	public function widget( $args, $instance ) {
		// outputs the content of the widget
		echo $args['before_widget'];

		if (! empty($instance['title'])) {
			echo $args['before_title'].apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
		}

		/* TICKERS TO ADD IN THE MENU */
		$tickers = array();
		for ($i = 0; $i < 5; $i++) {
			$ticker = $instance['stock_' . $i];
			if ($ticker != '') {
				$tickers[] = $ticker;
			}
		}

		/* SHOWN IN THE FRONT-END ONCE THE WIDGET HAS BEEN ACTIVATED*/
		/* 	. Previously saved values from database.<div id="widget-area" class="widget-area">
				<aside id="test_widget-2" class="widget widget_test_widget">
			</div>
		*/
		//echo __('Hello, World!', 'text_domain');

		function curl($url){
			$options = Array(
				CURLOPT_RETURNTRANSFER => TRUE, # cURL's option to return webpage data
				CURLOPT_FOLLOWLOCATION => TRUE, # follow 'location' HTTP headers
				CURLOPT_AUTOREFERER => TRUE, # set the referer where following 'location'
				CURLOPT_CONNECTTIMEOUT => 120, # seconds before request times out
				CURLOPT_TIMEOUT => 120, # max time for cURL to execute queries
				CURLOPT_MAXREDIRS => 10, # max number of redirections to follow
				CURLOPT_USERAGENT => "Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.1a2pre) Gecko/2008073000 Shredder/3.0a2pre ThunderBrowse/3.2.1.8",
				CURLOPT_URL => $url, # passed options as a variable parameter

			);

			$ch = curl_init();
			curl_setopt_array($ch, $options);
			$data = curl_exec($ch);
			curl_close($ch);
			return $data;
		}

		function scrapeBetween($data, $start, $end){
			$data = stristr($data, $start); //strips everything before $start
			$data = substr($data, strlen($start)); //strips $start
			$stop = stripos($data, $end); //gets the position of $end
			$data = substr($data, 0, $stop); // strips all the data after
			return $data;
		}

		/* RETURNS DYNAMIC TICKER AND PRICE */
		/*
		if ( $title != '') {
			echo $before_title . $title . $after_title;
		}else {
			echo 'Make sure settings are saved.';
		}
		*/


		/* RETURNS HARDCODED TICKER AND PRICE INTO A FORMATTED HTML TABLE*/
		$tickersArray = array('ko','cat','vig');
		foreach ($tickersArray as $eachTicker) {
			$ticker = $eachTicker;
			$scrapedPage = curl("http://finance.yahoo.com/q?s=$ticker");
			$scrapedData = scrapeBetween($scrapedPage, "<title>","</title>");
			$scrapedTickerPrice = scrapeBetween($scrapedPage, '<span class="time_rtq_ticker">','</span>');
			$scrapedTickerTime = scrapeBetween($scrapedPage, '<span class="time_rtq">','</span>');
		?>
			<!-- PRINTS THE FIRST ITEM INSIDE BUT REST OF TABLES OUTSIDE-->
			<table class="gma_show_stock_quotes_table">	
				<tbody>
					<tr>
					
						<td class="gma_show_stock_quotes_ticker" id="<?php echo $eachTicker; ?>">
							<a target="_blank" href="<?php echo 'http://finance.yahoo.com/q?s='.$eachTicker; ?>"><?php echo strtoupper($eachTicker);?></a><?php echo ': '.$scrapedTickerPrice;?>
						</td>
				
					</tr>
				</tbody>
			</table>

		<?php
			
			//echo '<br>';
			//echo $args['after_widget'];SOLUCIONADO EL PROBLEMA CON LAS TABLAS QUITANDO ESTO DE DENTRO DE LA FUNCION

		}
		//echo 'end of foreach loop'.'<br>';
		
		/* CON ESTA FUNCIÓN INTENTARÉ QUE SI HAY UNA LLAMADA A PRECIOS CON UNA DIFERENCIA MENOR DE 15 SENCILLAMENTE COGE EL VALOR SALVADO Y NO HACE REQUEST DE NUEVO */
		$variable_limpia = get_the_time('U');
		echo human_time_diff( $variable_limpia, current_time('timestamp') );

		$trimmed = str_replace(' ', '', $variable_limpia);
		if ($trimmed == '7') {
			echo "is seven";
		} else {
			echo "is not seven";
		}
		/* */
		echo $args['after_widget'];
		//echo 'end of $args[after_widget]';
	}


	/* BACK-END FORM */
	/**
	 * Outputs the options form on admin
	 * @see WP_Widget::form()
	 *
	 * @param array $instance The widget options. Previously saved values from database.
	 */
	public function form( $instance ) {
		// outputs the options form on admin

		/* ADDS OPTIONS TO THE BACKEND UNDER APPEARANCE > WIDGETS */
		/* IF NO TITLE EXISTS, USE DEFAULT 'New title' and adds it into the top of 'Hello World", in any case does not save new info added, for that next function */

		/*
			<aside id="test_widget-2" class="widget widget_test_widget">
				<h2 class="widget-title">test New title</h2>
				Hello, World!
			</aside>

		*/

		$title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Pick some stocks here!', 'text_domain' );

?>
		<!-- TITLE -->
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>

		<!-- STOCK TICKERS -->

		<p>
			<!--generates a static label-->
			<label><?php _e( 'Add the tickers here (For example: KO)' ); ?></label>
			<ol>

			<?php
			for ($i = 0; $i < 5; $i++) {
				// generates an instance of stock_$i
				$stock = isset($instance['stock_'.$i]) ? $instance['stock_'.$i] : '';
				?>
				<!--generates 5 widefat input bars with id stock_$i-->
				<li>
					<input class="widefat" id="<?php echo $this->get_field_id( 'stock_'.$i ); ?>" name="<?php echo $this->get_field_name('stock_' . $i); ?>" type="text" value="<?php echo esc_attr( $stock ); ?>" />
				</li>
				
				<?php
			}
			?>
			</ol>
		</p>
<?php		
	}

	/* SANITIZE FORM VALUES AS THEY ARE SAVED */
	/* it is necessary if is just for displaying but not for saving? */
	/**
	 * Processing widget options on save
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance The new options. Values just sent to be saved.
	 * @param array $old_instance The previous options. Previously saved values from database.
	 */
	public function update( $new_instance, $old_instance ) {
		// processes widget options to be saved
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		
		/*AS WORKS FOR TITLE SHOULD WORK THE SAME FOR STOCK_$I? OR IS CALLING TWICE? */
		//$instance['stock_'.$i] = ( ! empty( $new_instance['stock_'.$i] ) ) ? strip_tags( $new_instance['stock_'.$i] ) : '';
		$instance['stock_0'] = ( ! empty( $new_instance['stock_0'] ) ) ? strip_tags( $new_instance['stock_0'] ) : '';
		$instance['stock_1'] = ( ! empty( $new_instance['stock_1'] ) ) ? strip_tags( $new_instance['stock_1'] ) : '';
		$instance['stock_2'] = ( ! empty( $new_instance['stock_2'] ) ) ? strip_tags( $new_instance['stock_2'] ) : '';
		$instance['stock_3'] = ( ! empty( $new_instance['stock_3'] ) ) ? strip_tags( $new_instance['stock_3'] ) : '';
		$instance['stock_4'] = ( ! empty( $new_instance['stock_4'] ) ) ? strip_tags( $new_instance['stock_4'] ) : '';
		
		/* WORKS THIS WAY, CHECK FOR LOOPS
		$i = 4;
		$instance['stock_'.$i] = ( ! empty( $new_instance['stock_4'] ) ) ? strip_tags( $new_instance['stock_4'] ) : '';
		*/
		
		/* WHAT IS EXACTLY THE USE OF THIS PIECE OF CODE FOREACH? */
		/*
		foreach ($this->options as $val) {
			$instance[$val['name']] = strip_tags(isset($new_instance[$val['name']]) ? $new_instance[$val['name']] : '');
		}
		*/

		return $instance;
	}
} /* ENDS CLASS Almond_Stock_Prices */

?>
