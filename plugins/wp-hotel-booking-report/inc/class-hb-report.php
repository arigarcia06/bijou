<?php
/**
 * HB_Report
 *
 * @author   ThimPress
 * @package  WP-Hotel-Booking/Report/Classes
 * @version  1.7.2
 */

// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'HB_Report' ) ) {
	/**
	 * Class HB_Report
	 */
	abstract class HB_Report {

		/**
		 * @var
		 */
		public $_title;

		/**
		 * @var string
		 */
		protected $_chart_type = 'price';

		/**
		 * @var
		 */
		public $_start_in;

		/**
		 * @var
		 */
		public $_end_in;

		/**
		 * @var
		 */
		public $chart_groupby;

		/**
		 * @var
		 */
		public $chart_groupby_title;

		/**
		 * @var
		 */
		public $_range_start;

		/**
		 * @var
		 */
		public $_range_end;

		/**
		 * @var null
		 */
		public $_range;

		/**
		 * @var null
		 */
		protected $_query_results = null;

		/**
		 * HB_Report constructor.
		 *
		 * @param null $range
		 */
		public function __construct( $range = null ) {
			if ( ! $range ) {
				return;
			}

			$this->_range = $range;

			if ( isset( $_GET['tab'] ) && $_GET['tab'] ) {
				$this->_chart_type = sanitize_text_field( $_GET['tab'] );
			}
		}

		/**
		 * @param string $current_range
		 */
		protected function calculate_current_range( $current_range = '7day' ) {
			switch ( $current_range ) {
				case 'custom':
					if ( ! isset( $_GET['wp-hotel-booking-report'] ) ) {
						return;
					}
					if ( isset( $_GET['report_in'], $_GET['report_in_timestamp'] ) && $_GET['report_in'] ) {

						$this->_start_in = absint( $_GET['report_in_timestamp'] ) + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS );
						if ( isset( $_GET['report_out_timestamp'] ) && sanitize_text_field( $_GET['report_out_timestamp'] ) ) {
							$this->_end_in = strtotime( 'midnight', absint( sanitize_text_field( $_GET['report_out_timestamp'] ) ) + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) );
						} else {
							$this->_end_in = strtotime( 'midnight', current_time( 'timestamp' ) );
						}

						if ( ! $this->_end_in ) {
							$this->_end_in = current_time( 'timestamp' );
						}

						$interval = 0;
						$min_date = $this->_start_in;

						while ( ( $min_date = strtotime( "+1 MONTH", $min_date ) ) <= $this->_end_in ) {
							$interval ++;
						}

						// 3 months max for day view
						if ( $interval > 3 ) {
							$this->chart_groupby = 'month';
						} else {
							$this->chart_groupby = 'day';
						}

						$this->_start_in = date( 'Y-m-d', $this->_start_in );
						$this->_end_in   = date( 'Y-m-d', $this->_end_in );
					}
					break;
				case 'year' :
					$this->_start_in     = date( 'Y-01-01', current_time( 'timestamp' ) );
					$this->_end_in       = date( 'Y-12-31', current_time( 'timestamp' ) ); // date( 'Y-m-d', current_time( 'timestamp' ) );
					$this->chart_groupby = 'month';
					break;
				case 'last_month' :
					$first_day_current_month = strtotime( date( 'Y-m-01', current_time( 'timestamp' ) ) );
					$this->_start_in         = date( 'Y-m-01', strtotime( '-1 DAY', $first_day_current_month ) );
					$this->_end_in           = date( 'Y-m-t', strtotime( '-1 DAY', $first_day_current_month ) );
					$this->chart_groupby     = 'day';
					break;
				case 'current_month' :
					$this->_start_in     = date( 'Y-m-01', current_time( 'timestamp' ) );
					$this->_end_in       = date( 'Y-m-t', current_time( 'timestamp' ) );
					$this->chart_groupby = 'day';
					break;
				case '7day' :
					$this->_start_in     = date( 'Y-m-d', strtotime( '-6 days', current_time( 'timestamp' ) ) );
					$this->_end_in       = date( 'Y-m-d', strtotime( 'midnight', current_time( 'timestamp' ) ) );
					$this->chart_groupby = 'day';
					break;
			}

			$this->_start_in = apply_filters( 'hotel_booking_report_start_in', $this->_start_in );
			$this->_end_in   = apply_filters( 'hotel_booking_report_end_in', $this->_end_in );

			if ( $this->chart_groupby === 'day' ) {
				$this->_range_start        = date( 'z', strtotime( $this->_start_in ) );
				$this->_range_end          = date( 'z', strtotime( $this->_end_in ) );
				$this->chart_groupby_title = __( 'Day', 'wp-hotel-booking-report' );
			} else {
				$this->_range_start        = date( 'm', strtotime( $this->_start_in ) );
				$this->_range_end          = date( 'm', strtotime( $this->_end_in ) );
				$this->chart_groupby_title = __( 'Month', 'wp-hotel-booking-report' );
			}
		}

		/*** Get all booking with completed > start and < end
		 *
		 * @return bool
		 */
		protected function getOrdersItems() {
			return true;
		}

		/**
		 * @return bool
		 */
		protected function series() {
			return true;
		}

		/**
		 * @return bool
		 */
		protected function parseData() {
			return true;
		}

		/**
		 * @return bool
		 */
		protected function export_csv() {
			return true;
		}
	}
}

