<?php

namespace NikolayS93\ContactFormOrders;

class Order
{
	const STATUS_CREATED = 'created';
	const STATUS_DEACTIVE = 'deactive';
    const STATUS_INIT = 'new';
    const STATUS_DONE = 'complete';

    public $id;
    public $status = self::STATUS_CREATED;
	public $amount = 0;
    public $payment_type = '';
    public $payment_code;

    public static function getTableName()
    {
    	global $wpdb;
    	return $wpdb->prefix . 'form_payment';
    }

	/**
	 * @return mixed
	 */
	public function get_amount() {
		return $this->amount;
	}

	public static function sanitize_amount($amount)
	{
		return floatval(preg_replace('/[^0-9,.]+/', '', $amount));
	}

	/**
	 * @param mixed $amount
	 */
	public function set_amount( $amount ): self {
		$this->amount = static::sanitize_amount($amount);
		return $this;
	}

	/**
	 * @param int|string $idOrCode
	 *
	 * @return mixed
	 */
	public static function get( $idOrCode = null)
	{
		if ($idOrCode) {
			$orderData = [];

			if (is_numeric( $idOrCode)) {
				$orderData = static::get_by_id($idOrCode);
			} elseif (is_string( $idOrCode)) {
				$orderData = static::get_by_payment_code($idOrCode);
			}

			if (empty($orderData['id'])) {
				throw new \Exception('Order not found!');
			}

			return new static($orderData);
		}

		// Get all orders
		return static::get_all();
	}

    /**
     * @param array $attributes
     */
    public function __construct(array $attributes)
    {
	    foreach ($attributes as $key => $value) {
            $this->{$key} = $value;
        }

	    if (null === $this->id) {
		    $this->save();
	    }
    }

	public function complete(): void
    {
        $this->status = self::STATUS_DONE;
    }

    public function save(): self
    {
	    global $wpdb;

	    $res = [];
	    $res['payment_type'] = $this->payment_type;
	    $res['amount'] = $this->amount;
	    $res['status'] = $this->status;

	    if ($this->payment_code) {
		    $res['payment_code'] = $this->payment_code;
	    }

	    $wpdb->insert(static::getTableName(), $res);
	    $this->id = $wpdb->insert_id;

	    return $this;
    }

    public static function create_table() {
	    global $wpdb;

	    try {
		    $wpdb->query("
				CREATE TABLE IF NOT EXISTS `".Order::getTableName()."` (
			        `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
		            `payment_code` varchar(100) NULL UNIQUE,
		            `payment_type` tinyint unsigned NOT NULL,
		            `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		            `amount` decimal NOT NULL,
		            `status` varchar(100) NOT NULL
	            );"
		    );
	    } catch (\Exception $e) {
	    	wp_die($e->getMessage());
	    }
    }

	public static function delete_table() {
		global $wpdb;

		$wpdb->query("DROP TABLE IF EXISTS `".Order::getTableName()."`");
	}

	protected static function get_all()
	{
		global $wpdb;

		$results = $wpdb->get_results("SELECT * FROM `".Order::getTableName()."`");

		return array_map(function($result) {
			return new static($result);
		}, $results);
	}

	protected static function get_by_id($id): array
	{
		global $wpdb;

		return $wpdb->get_row("SELECT * FROM `".Order::getTableName()."`
    	    WHERE `id` = '$id'", ARRAY_A ) ?: [];
	}

	protected static function get_by_payment_code($payment_code): array
	{
		global $wpdb;

		return $wpdb->get_row("SELECT * FROM `".Order::getTableName()."`
    	    WHERE `payment_code` = '$payment_code'", ARRAY_A ) ?: [];
	}
}
