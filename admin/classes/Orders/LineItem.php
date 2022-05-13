<?php
	namespace Orders;

	use Cart\Cart;
	use Cart\CartLineItemLink;
	use Configuration\Registry;
	use Core\Attributes\Data;
	use Core\Attributes\Dynamic;
	use Core\Attributes\LinkFrom;
	use Core\Entity;
	use Files\Image;
	use Payments\Payment;
	
	/**
	 * A Line Item represents one item in a cart or order
	 */
	class LineItem extends Entity
	{
		const TABLE = "line_items";
		const ID_FIELD = "line_item_id";
		const HAS_AUTOCAST = true;
		
		#[Data("title")]
		public string $title = "";
		
		#[Data("quantity")]
		public int $quantity = 0;
		
		#[Data("display_quantity")]
		public bool $displayQuantity = true;
		
		#[Data("price")]
		public float $price = 0; // Note: This is the price for each item, not the total
		
		#[Data("generator_class_identifier")]
		public string $generatorClassIdentifier = "";
		
		#[Data("generator_identifier")]
		public string $generatorIdentifier = "";
		
		#[Dynamic]
		public string $identifier = "";
		
		#[Dynamic]
		public float $total;
		
		#[Dynamic]
		public string $link;
		
		#[Data("item_weight")]
		public float $itemWeight = 0; // Individual item weight
		
		#[Dynamic]
		public ?Image $image;
		
		#[LinkFrom("lineItem")]
		public CartLineItemLink $cartLineItemLink;
		
		#[LinkFrom("lineItem")]
		public OrderLineItemLink $orderLineItemLink;
		
		public ?string $displayValue; // Can be displayed instead of price for special line items eg shipping
		public string $temporaryId = "";

		/**
		 * Gets all the Line Items that belong to a specific cart
		 * @param	Cart		$cart	The cart the line items belong to
		 * @return	static[]			All the line items belonging to that cart
		 */
		public static function loadAllForCart(Cart $cart)
		{
			$query = "SELECT ~PROPERTIES "
				. "FROM ~TABLE "
				. "INNER JOIN ~CartLineItemLink "
				. "ON ~CartLineItemLink.~lineItem = ~id "
				. "WHERE ~CartLineItemLink.~cart = ?";

			return static::makeMany($query, [$cart->id]);
		}

		/**
		 * Creates a new Object and sets any properties that need default objects
		 */
		public function __construct()
		{
			parent::__construct();

			$this->temporaryId = uniqid();
		}

		/**
		 * Some functionality (eg stock update) to happen after a transaction has been completed, eg Order has been placed, BillPayment has been made.
		 * To be overridden in child classes
		 * 
		 * @param Payment	$payment	Some functionality may depend on the payment status, eg stock wont be decreased if the order was made with some kind of defered payment method (eg BankDeposit)
		 */
		public function onPurchase(Payment $payment) 
		{
			//do nothing by default
		}

		/**

		 * Gets the original generating object for this line item
		 * @return	LineItemGenerator	The generating object, or null if one can't be found
		 */
		public function getGenerator()
		{
			foreach(Registry::LINE_ITEM_GENERATING_CLASSES as $lineItemGeneratingClass)
			{
				if($lineItemGeneratingClass::getClassLineItemGeneratorIdentifier() === $this->generatorClassIdentifier)
				{
					return $lineItemGeneratingClass::loadForLineItemGeneratorIdentifier($this->generatorIdentifier);
				}
			}

			return null;
		}

		/**
		 * Gets an updated line item for this line item
		 * @return	LineItem	The updated line item, or null if it no longer exists
		 */
		public function getUpdated()
		{
			foreach(Registry::LINE_ITEM_GENERATING_CLASSES as $lineItemGeneratingClass)
			{
				if($lineItemGeneratingClass::getClassLineItemGeneratorIdentifier() === $this->generatorClassIdentifier)
				{
					return $lineItemGeneratingClass::updateLineItem($this->generatorIdentifier, $this);
				}
			}

			return null;
		}
		
		/**
		 * Gets the total weight of this line item
		 * @return	float|int	The total weight
		 */
		public function getWeight() 
		{
			return $this->quantity * $this->itemWeight;
		}

		/**
		 * Gets the total price for this line item
		 * @return	float	The total price
		 */
		public function get_total()
		{
			return $this->quantity * $this->price;
		}

		/**
		 * Gets a unique identifier for this line item
		 * @return	string	The unique identifier
		 */
		public function get_identifier()
		{
			return $this->id ?? $this->temporaryId;
		}

		/**
		 * Gets an image for this line item
		 * @return	Image	The image to display for this line item, or null if there is none
		 */
		public function get_image()
		{
			$generator = $this->getGenerator();
			return $generator->getLineItemImage();
		}

		/**
		 * Gets a link for this line item to the original item
		 * @return	string	A link to the original item, or null if there is none
		 */
		public function get_link()
		{
			$generator = $this->getGenerator();
			
			if ($generator !== null) 
			{
				return $generator->getLineItemLink();
			}
			
			return null;
		}
		
		/**
		 * Runs before this entity is deleted
		 */
		public function beforeDelete()
		{
			parent::beforeDelete();
			
			if(!$this->cartLineItemLink->isNull())
			{
				$this->cartLineItemLink->delete();
			}
			
			if(!$this->orderLineItemLink->isNull())
			{
				$this->orderLineItemLink->delete();
			}

		}

		/**
		 * Specifies what data should be serialised when json_encode is called
		 * @return    array    Name/data pairs for the data in this object
		 */
		public function jsonSerialize(): array
		{
			return
			[
				"id" => $this->id ?? $this->temporaryId,
				"quantity" => $this->quantity,
				"name" => $this->title,
				"price" => formatPrice($this->total),
				"image" => $this->image !== null ? $this->image->getLink() : null
			];
		}
	}