<?php
	namespace Menus;

	use Core\Attributes\Dynamic;
	use Core\Attributes\LinkTo;
	use Core\Elements\Select;
	use Core\Generator;
	
	/**
	 * A Menu Item Attribute is a Menu Attribute attached to a specific Menu Item
	 */
	class MenuItemAttribute extends Generator
	{
		/*~~~~~
		 * setup
		 **/
		// Entity / Generator
		const TABLE = "menu_item_attributes";
		const ID_FIELD = "menu_item_attribute_id";
		const PARENT_PROPERTY = "menuItem";
		const SINGULAR = "Attribute";
		const PLURAL = "Attributes";
		const LABEL_PROPERTY = "menuAttribute";
		const HAS_POSITION = true;

		// MenuItemAttribute
		
		#[Dynamic]
		public string $label;
		
		#[LinkTo("menu_item_id")]
		public MenuItem $menuItem;
		
		#[LinkTo("menu_attribute_id")]
		public MenuAttribute $menuAttribute;

		/*~~~~~
		 * non-static methods excluding interface methods
		 **/
		/**
		 * Sets the Form Elements for this object
		 */
		protected function elements()
		{
			parent::elements();

			$this->addElement(new Select("menuAttribute", "Attribute", MenuAttribute::loadOptions()));
		}
		
		/**
		 * Gets the dynamic label script to use for this generator
		 * @return	string	The dynamic label
		 */
		public function getDynamicLabelScript()
		{
			return "return menuAttribute;";
		}

		/**
		* A little cheat to keep the position of Attributes the same over all menus
		*/
		public function set_position()
		{
			$this->setValue('position', $this->menuAttribute->position);
		}
	}
