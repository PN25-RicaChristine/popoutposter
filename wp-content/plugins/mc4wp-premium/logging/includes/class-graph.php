<?php

class MC4WP_Graph
{

    /**
    * @var string
    */
    private $table_name;

    /**
     * @var array
     */
    private $initial_data = array();

    /**
     * @var array
     */
    private $config = array();

    /**
     * @var string
     */
    public $range = 'this_week';

    /**
     * @var
     */
    public $start_date;

    /**
     * @var
     */
    public $end_date;

    /**
     * @var string
     */
    public $step_size = 'day';

    /**
     * @var array
     */
    public $datasets = array();

    /**
     * @var
     */
    private $day;


    /**
	* @param array $config
    */
    public function __construct(array $config = array())
    {
        // store config
        if (isset($config['range'])) {
            $this->range = $config['range'];
        }

        $this->config = $config;

        // set table prefix
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'mc4wp_log';
    }

    /**
    * Initialize various settings to use
    */
    public function init()
    {
        $this->day = date('d');
        $start_of_week = (int) get_option('start_of_week', 'monday');

        switch ($this->range) {
            case 'today':
                $this->start_date = strtotime('today midnight');
                $this->end_date = strtotime('tomorrow midnight') - 1;
                $this->step_size = 'hour';
            break;

            case 'yesterday':
                $this->start_date = strtotime('yesterday midnight');
                $this->end_date = strtotime('today midnight') - 1;
                $this->step_size = 'hour';
            break;

			case 'this_week':
			default:
				$this->start_date = strtotime(sprintf('last sunday midnight + %d days', $start_of_week));
				$this->end_date = strtotime('+7 days', $this->start_date) - 1;
				$this->step_size = 'day';
				break;

			case 'last_week':
				$this->start_date = strtotime(sprintf('last sunday - %d days', 7 - $start_of_week));
				$this->end_date = strtotime('+7 days', $this->start_date) - 1;
				$this->step_size = 'day';
				break;

			case 'this_month':
				$this->start_date = strtotime("first day of this month midnight");
				$this->end_date = strtotime('+1 month', $this->start_date) - 1;
				$this->step_size = 'day';
				break;

            case 'last_month':
				$this->start_date = strtotime("first day of last month midnight");
				$this->end_date = strtotime('+1 month', $this->start_date) - 1;
                $this->step_size = 'day';
           	 break;

            case 'this_quarter':
				$month = floor(date('m') / 3) * 3 + 1;
                $this->start_date = strtotime(date(sprintf('Y-%d-01 00:00:00', $month)));
				$this->end_date = strtotime('+3 months', $this->start_date) - 1;
                $this->step_size = 'day';
            break;

			case 'last_quarter':
				$month = floor(date('m') / 3) * 3 + 1 - 3;
				$this->start_date = strtotime(date(sprintf('Y-%d-01 00:00:00', $month)));
				$this->end_date = strtotime('+3 months', $this->start_date) - 1;
				$this->step_size = 'day';
				break;

			case 'this_year':
				$this->start_date = strtotime(date("Y-01-01 00:00:00"));
				$this->end_date = strtotime('+1 year', $this->start_date) - 1;
				$this->step_size = 'month';
				break;

            case 'last_year':
                $this->start_date = strtotime(sprintf("%d-01-01 00:00:00", date('Y')-1));
				$this->end_date = strtotime('+1 year', $this->start_date) - 1;
                $this->step_size = 'month';
            break;

            case 'custom':
                $this->start_date = strtotime(implode('-', array( $this->config['start_year'], $this->config['start_month'], $this->config['start_day'] )) . ' 00:00:00');
                $this->end_date = strtotime(implode('-', array( $this->config['end_year'], $this->config['end_month'], $this->config['end_day'] )) . ' 23:59:59');
                $this->step_size = $this->calculate_step_size($this->start_date, $this->end_date);
                $this->day = $this->config['start_day'];
                break;
        }

        // If start is before end, revert back to "week" range and re-init.
        if ($this->start_date >= $this->end_date) {
            add_settings_error('mc4wp', 'mc4wp-stats', __('End date can\'t be before the start date', 'mailchimp-for-wp'));
            $this->config['range'] = 'this_week';
            $this->init();
            return;
        }

        $utc_offset = get_option('gmt_offset') * 3600;
        $start_date = $this->start_date - $utc_offset;
        $end_date = $this->end_date - $utc_offset;

        $this->start_date = date('Y-m-d H:i:s', $start_date);
        $this->end_date = date('Y-m-d H:i:s', $end_date);

        // setup array of dates with 0's
        $current = $start_date;
        $this->initial_data = array();
        while ($current < $end_date) {
            $key = date('Y-m-d H:i:s', $current);
            $this->initial_data[$key] = 0;
            $current = strtotime("+1 {$this->step_size}", $current);
        }

        $this->query();
    }

    /**
     * Calculates an appropriate step size
     *
    * @param int $start
    * @param int $end
    *
    * @return string
    */
    public function calculate_step_size($start, $end)
    {
        $difference = $end - $start;
        $day_in_seconds = 86400;
        $month_in_seconds = 2592000;

        if ($difference > ($month_in_seconds * 6)) {
            $step = 'month';
        } elseif ($difference > $day_in_seconds) {
            $step = 'day';
        } else {
            $step = 'hour';
        }

        return $step;
    }

    /**
     * @return mixed
     */
    protected function get_date_format()
    {
        $date_formats = array(
            'hour' => '%Y-%m-%d %H:00:00',
            'day' => '%Y-%m-%d 00:00:00',
            'week' => '%YW%v 00:00:00',
            'month' =>  "%Y-%m-{$this->day} 00:00:00",
        );

        return $date_formats[ $this->step_size ];
    }

    /**
     * @return array
     */
    public function query()
    {
        $datasets = array();

        // forms
        $forms = mc4wp_get_forms();

        foreach ($forms as $form) {
            $day_counts = $this->get_day_counts_for_form($form->ID);
            if (array_sum($day_counts) === 0) {
            	continue;
			}

            $dataset = array(
                'label' => sprintf('%s', esc_html($form->name)),
                'data' => array_map(array( $this, 'format_graph_data' ), $day_counts, array_keys($day_counts)),
            );
            $datasets[] = $dataset;
        }


        // integrations
        $integrations = mc4wp_get_integrations();
        foreach ($integrations as $integration) {
            $day_counts = $this->get_day_counts_for_type($integration->slug);
			if (array_sum($day_counts) === 0) {
				continue;
			}

            $dataset = array(
                'label' => $integration->name,
                'data' => array_map(array( $this, 'format_graph_data' ), $day_counts, array_keys($day_counts)),
            );
            $datasets[] = $dataset;
        }

        // Top Bar
        $day_counts = $this->get_day_counts_for_type('mc4wp-top-bar');
		if (array_sum($day_counts) > 0) {
			$dataset = array(
				'label' => 'Top Bar',
				'data' => array_map(array($this, 'format_graph_data'), $day_counts, array_keys($day_counts)),
			);
			$datasets[] = $dataset;
		}

        $this->datasets = $datasets;
    }

    /**
     * @param array $totals
     *
     * @return array
     */
    public function get_day_counts(array $totals)
    {
        $counts = $this->initial_data;
        $timestamps = array_keys($counts);

        for ($i = 0; $i < count($timestamps); $i++) {
            $key = $timestamps[$i];
            $start = strtotime($timestamps[$i]);
            $end = isset($timestamps[$i+1]) ? strtotime($timestamps[$i+1]) : strtotime('+10 years');

            foreach ($totals as $j => $group) {
                if ($group->timestamp >= $end) {
                    break;
                }

                if ($group->timestamp >= $start) {
                    $counts[$key] = $counts[$key] + $group->count;

                    // unset total so next timestamp does not have to loop through it
					unset($totals[$j]);
                }
            }
        }

        return $counts;
    }

    public function get_day_counts_for_type($type)
    {
		global $wpdb;
        $sql = "SELECT COUNT(*) AS count, DATE_FORMAT(datetime, '%s') AS date_group FROM `{$this->table_name}` WHERE `type` = '%s' AND datetime >= %s AND datetime <= %s GROUP BY date_group ORDER BY date_group ASC";
        $query = $wpdb->prepare($sql, $this->get_date_format(), $type, $this->start_date, $this->end_date);
        $totals = $wpdb->get_results($query);
        foreach($totals as $i => $row) {
            $totals[$i]->timestamp = strtotime($row->date_group);
        }
        return $this->get_day_counts($totals);
    }

    public function get_day_counts_for_form($form_id)
    {
        global $wpdb;
        $sql = "SELECT COUNT(*) AS count, DATE_FORMAT(datetime, '%s') AS date_group FROM `{$this->table_name}` WHERE `related_object_ID` = %d AND `type` = '%s' AND datetime >= %s AND datetime <= %s GROUP BY date_group ORDER BY date_group ASC";
        $query = $wpdb->prepare($sql, $this->get_date_format(), $form_id, 'mc4wp-form', $this->start_date, $this->end_date);
        $totals = $wpdb->get_results($query);
        foreach($totals as $i => $row) {
            $totals[$i]->timestamp = strtotime($row->date_group);
        }
        return $this->get_day_counts($totals);
    }

    /**
     * @param int $count
     * @param string $timestamp
     *
     * @return array
     */
    public function format_graph_data($count, $timestamp)
    {

        return array(
            'x' => str_replace(' ', 'T', $timestamp) . 'Z', // Tell client that this is a UTC timestamp
            'y' => (int) $count
        );
    }
}
