<?php
	namespace Orders;

	use Core\Attributes\Data;
	use Core\Attributes\LinkTo;
	use Core\Elements\Text;
	use Core\Generator;
	
	/**
	 * The weight range for a shipping region
	 */
	class WeightRange extends Generator
	{
		// Object
		const TABLE = "shipping_weight_ranges";
		const ID_FIELD = "weight_range_id";

		// Generator
		const SINGULAR = "Weight range";
		const PLURAL = "Weight ranges";
		const HAS_ACTIVE = true;
		const PARENT_PROPERTY = 'shippingRegion';
		
		#[LinkTo("shipping_region_id")]
		public ShippingRegion $shippingRegion;
		
		#[Data("to_weight")]
		public float $upTo;

		#[Data("cost")]
		public float $cost = 0;

		/**
		 * Sets the Form Elements for this object
		 */
		protected function elements()
		{
			parent::elements();
			
			//$this->addElement((new Text('from', 'From'))->addClass('half first')->setHint("kg"));
			$this->addElement((new Text('upTo', 'Up to'))->addClass('')->setHint("kg"));
			$this->addElement((new Text('cost', 'Cost'))->addClass('currency'));
		}
		
		/**
		 * Gets the dynamic label script to use for this generator
		 * @return	string	The dynamic label
		 */
		public function getDynamicLabelScript()
		{
			return 'return `Up to ${upTo}kg`';
		}

	}
