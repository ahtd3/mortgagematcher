<?php
	namespace Menus;


	use Core\Attributes\Data;
	use Core\Attributes\LinkFromMultiple;
	use Core\Attributes\LinkTo;
	use Core\Columns\PropertyColumn;
	use Core\Elements\GeneratorElement;
	use Core\Elements\Text;
	use Core\Generator;
	
	/**
	 * A single item (food or drink or whatever) in the menu
	 */
	class MenuItem extends Generator
	{
		const TABLE = 'menu_items';
		const ID_FIELD = 'menu_item_id';
		const SINGULAR = 'Menu item';
		const PLURAL = 'Menu items';
		const HAS_ACTIVE = true;
		const HAS_POSITION = true;
		const LABEL_PROPERTY = 'name';
		const PARENT_PROPERTY = "menu";

		public bool $active = true;
		
		#[Data("name")]
		public string $name = '';
		
		#[LinkTo("menu_id")]
		public Menu $menu;
		
		#[LinkTo("menu_group_id")]
		public MenuGroup $menuGroup;

		#[LinkTo("menu_item_price_id")]
		public MenuItemPrice $menuItemPrice;
		
		/** @var MenuItemAttribute[] */
		#[LinkFromMultiple(MenuItemAttribute::class, "menuItem")]
		public array $itemAttributes;

		/** @var MenuItemPrice[] */
		#[LinkFromMultiple(MenuItemPrice::class, "menuItem")]
		public array $prices; // Optional additional prices.

		/**
		 * Sets the array of Columns that are displayed to the user for this object type
		 */
		public static function columns()
		{
			static::addColumn(new PropertyColumn('Name', 'name'));
			
			parent::columns();
		}

		/**
		 * Sets the Form Elements for this object
		 */
		protected function elements()
		{
			parent::elements();
			
			$this->addElement(new Text('name', 'Name'));
			$this->addElement(new GeneratorElement('menuItemPrice'));
			$this->addElement(new GeneratorElement('prices'));
			$this->addElement(new GeneratorElement('itemAttributes'));
		}
		
		/**
		 * @return MenuItemPrice[] just the prices for display
		 */
		public function getPrices()
		{		
			return filterActive($this->prices);
		}
	}
