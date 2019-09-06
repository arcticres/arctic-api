<?php

namespace Arctic;

/**
 * Class Iterator
 * @package Arctic
 * A generic iterator that enables paginating over any `Model::browse` or `Model::query` method by automatically
 * advancing through all available pages.
 */
class Iterator implements \Iterator
{
	protected $_model_class;
	protected $_function;
	protected $_arguments;

	/**
	 * @var ModelSet
	 */
	protected $_results;
	protected $_index_results;
	protected $_index_all;

	protected $_number_per_page = 100;
	protected $_pause;

	protected function __construct($model_class, $function, ...$arguments) {
		$this->_model_class = $model_class;
		$this->_function = $function;
		$this->_arguments = $arguments;
	}

	/**
	 * @param string $model_class
	 * @return Iterator
	 */
	public static function browse($model_class) {
		return new self($model_class, 'browse');
	}

	/**
	 * @param string $model_class
	 * @param string $query
	 * @return Iterator
	 */
	public static function query($model_class, $query) {
		return new self($model_class, 'query', $query);
	}

	/**
	 * Time to pause before fetching the next page in microseconds. Only required if concerned about network congestion
	 * or fetching hundreds of pages of results (in which case rate limits may effect requests).
	 * @param int $pause
	 */
	public function setPause($pause) {
		$this->_pause = $pause;
	}

	/**
	 * Number of results per page.
	 * @param int $number_per_page
	 */
	public function setNumberPerPage($number_per_page) {
		$this->_number_per_page = $number_per_page;
	}

	protected function _fetchNextResults() {
		// is continuing pagination? or new pagination?
		if (isset($this->_results)) {
			$start = $this->_results->getStart();
			$number = $this->_results->getNumber();
			$total = $this->_results->getTotal();

			// don't bother fetching
			if (($start + $number) >= $total) {
				return false;
			}

			// advance next start
			$next_start = $start + $number;

			// pause
			if ($this->_pause) usleep($this->_pause);
		}
		else {
			$next_start = 0;
		}

		// local variables
		$model_class = $this->_model_class;
		$function = $this->_function;

		// make arguments
		$arguments = $this->_arguments;
		$arguments[] = [
			'start' => $next_start,
			'number' => $this->_number_per_page
		];

		// call next page
		/** @var ModelSet $results */
		$results = $model_class::$function(...$arguments);

		// store results
		$this->_results = $results;
		$this->_index_results = 0;

		// no results?
		if (0 === $results->count()) {
			return false;
		}

		return true;
	}

	public function current() {
		return $this->_results[$this->_index_results];
	}

	public function next() {
		// increment index
		$this->_index_results++;
		$this->_index_all++;
	}

	public function key() {
		return $this->_index_all;
	}

	public function valid() {
		// fetch results
		if (null === $this->_index_results || $this->_index_results >= count($this->_results)) {
			// fetch next results return false if there are no additional results
			if (!$this->_fetchNextResults()) return false;
		}

		return true;
	}

	public function rewind() {
		$this->_results = null;
		$this->_index_results = null;
		$this->_index_all = 0;
	}
}
