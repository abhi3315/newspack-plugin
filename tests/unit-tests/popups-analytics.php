<?php
/**
 * Tests the Campaigns (newspack-popups) Analytics functionality.
 *
 * @package Newspack\Tests
 */

use Newspack\Popups_Analytics_Utils;

/**
 * Tests the Campaigns (newspack-popups) Analytics functionality.
 */
class Newspack_Test_Popups_Analytics extends WP_UnitTestCase {
	private static $start_date; // phpcs:disable Squiz.Commenting.VariableComment.Missing
	private static $end_date; // phpcs:disable Squiz.Commenting.VariableComment.Missing
	public function set_up() { // phpcs:ignore Squiz.Commenting.FunctionComment.Missing
		self::$start_date = ( new \DateTime() )->modify( '-3 days' )->format( 'Y-m-d' );
		self::$end_date   = ( new \DateTime() )->modify( '-1 days' )->format( 'Y-m-d' );
	}

	/**
	 * Test encoded report generation.
	 * For a moment the events were reported as encoded.
	 */
	public function test_popups_analytics_report_generation_encoded() {
		$yesterday   = ( new \DateTime() )->modify( '-1 days' );
		$popup_title = 'Donations welcome';
		$event_code  = '1'; // 'Seen'.
		$popup_id    = wp_insert_post( [ 'post_title' => $popup_title ] );

		$ga_rows = [
			[
				'dimensions' => [
					$yesterday->format( 'Ymd' ),
					$popup_id . $event_code,
					'(not set)',
				],
				'metrics'    => [
					[
						'values' => [
							'4',
						],
					],
				],
			],
		];
		$report  = \Popups_Analytics_Utils::process_ga_report(
			$ga_rows,
			[
				'start_date'     => self::$start_date,
				'end_date'       => self::$end_date,
				'event_label_id' => '',
				'event_action'   => '',
			]
		);
		self::assertEquals(
			$report,
			[
				'report'         =>
				[
					[ 'Date', 'Views' ],
					[
						( new \DateTime() )->modify( '-3 days' )->format( 'M j' ),
						0,
					],
					[
						( new \DateTime() )->modify( '-2 days' )->format( 'M j' ),
						0,
					],
					[
						$yesterday->format( 'M j' ),
						4,
					],
				],
				'report_by_id'   => [],
				'actions'        =>
				[
					[
						'label' => 'Seen',
						'value' => 'Seen',
					],
				],
				'labels'         =>
				[
					[
						'label' => $popup_title,
						'value' => $popup_id,
					],
				],
				'key_metrics'    =>
				[
					'seen'             => 4,
					'form_submissions' => -1,
					'link_clicks'      => -1,
				],
				'post_edit_link' => false,
			],
			'Report has expected shape.'
		);
	}

	/**
	 * Test legacy report generation.
	 */
	public function test_popups_analytics_report_generation_legacy() {
		$yesterday = ( new \DateTime() )->modify( '-1 days' );
		$ga_rows   = [
			[
				'dimensions' => [
					$yesterday->format( 'Ymd' ),
					'Seen',
					'Newspack Announcement: Newsletter form (954)',
				],
				'metrics'    => [
					[
						'values' => [
							'4',
						],
					],
				],
			],
		];
		$report    = \Popups_Analytics_Utils::process_ga_report(
			$ga_rows,
			[
				'start_date'     => self::$start_date,
				'end_date'       => self::$end_date,
				'event_label_id' => '',
				'event_action'   => '',
			]
		);
		self::assertEquals(
			$report,
			[
				'report'         =>
				[
					[ 'Date', 'Views' ],
					[
						( new \DateTime() )->modify( '-3 days' )->format( 'M j' ),
						0,
					],
					[
						( new \DateTime() )->modify( '-2 days' )->format( 'M j' ),
						0,
					],
					[
						$yesterday->format( 'M j' ),
						4,
					],
				],
				'report_by_id'   => [],
				'actions'        =>
				[
					[
						'label' => 'Seen',
						'value' => 'Seen',
					],
				],
				'labels'         =>
				[
					[
						'label' => 'Newsletter form',
						'value' => '954',
					],
				],
				'key_metrics'    =>
				[
					'seen'             => 4,
					'form_submissions' => -1,
					'link_clicks'      => -1,
				],
				'post_edit_link' => false,
			],
			'Report has expected shape.'
		);
	}

	/**
	 * Test report generation.
	 */
	public function test_popups_analytics_report_generation() {
		$yesterday = ( new \DateTime() )->modify( '-1 days' );
		$ga_rows   = [
			[
				'dimensions' => [
					$yesterday->format( 'Ymd' ),
					'Seen',
					'Inline: Newsletter form (954)',
				],
				'metrics'    => [
					[
						'values' => [
							'4',
						],
					],
				],
			],
			[
				'dimensions' => [
					$yesterday->format( 'Ymd' ),
					'Seen',
					'Inline: Donate form (955) - Everyone',
				],
				'metrics'    => [
					[
						'values' => [
							'3',
						],
					],
				],
			],
		];

		$expected_report             = [
			[ 'Date', 'Views' ],
			[
				( new \DateTime() )->modify( '-3 days' )->format( 'M j' ),
				0,
			],
			[
				( new \DateTime() )->modify( '-2 days' )->format( 'M j' ),
				0,
			],
			[
				$yesterday->format( 'M j' ),
				7,
			],
		];
		$expected_report_actions     = [
			[
				'label' => 'Seen',
				'value' => 'Seen',
			],
		];
		$expected_report_labels      = [
			[
				'label' => 'Inline: Newsletter form',
				'value' => '954',
			],
			[
				'label' => 'Inline: Donate form - Everyone',
				'value' => '955',
			],
		];
		$expected_report_key_metrics = [
			'seen'             => 7,
			'form_submissions' => -1,
			'link_clicks'      => -1,
		];

		$report = \Popups_Analytics_Utils::process_ga_report(
			$ga_rows,
			[
				'start_date'     => self::$start_date,
				'end_date'       => self::$end_date,
				'event_label_id' => '',
				'event_action'   => '',
			]
		);
		self::assertEquals(
			$report,
			[
				'report'         => $expected_report,
				'report_by_id'   => [],
				'actions'        => $expected_report_actions,
				'labels'         => $expected_report_labels,
				'key_metrics'    => $expected_report_key_metrics,
				'post_edit_link' => false,
			],
			'Report has expected shape.'
		);

		$report_with_ids = \Popups_Analytics_Utils::process_ga_report(
			$ga_rows,
			[
				'start_date'        => self::$start_date,
				'end_date'          => self::$end_date,
				'event_label_id'    => '',
				'event_action'      => '',
				'with_report_by_id' => true,
			]
		);
		self::assertEquals(
			$report_with_ids,
			[
				'report'         => $expected_report,
				'report_by_id'   => [
					'954' => [
						'seen' => 4,
					],
					'955' => [
						'seen' => 3,
					],
				],
				'actions'        => $expected_report_actions,
				'labels'         => $expected_report_labels,
				'key_metrics'    => $expected_report_key_metrics,
				'post_edit_link' => false,
			],
			'Report with IDs has expected shape.'
		);
	}
}
