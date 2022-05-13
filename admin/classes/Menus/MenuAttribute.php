<?php
	namespace Menus;

	use Core\Attributes\Data;
	use Core\Attributes\ImageValue;
	use Core\Attributes\LinkFromMultiple;
	use Core\Columns\CustomColumn;
	use Core\Elements\ImageElement;
	use Core\Elements\Text;
	use Core\Generator;
	use Files\Image;
	
	/**
	 * A Menu Attribute is an optional label that can be attached to any Menu Item
	 */
	class MenuAttribute extends Generator
	{
		/*~~~~~
		 * setup
		 **/
		// Entity / Generator
		const TABLE = "menu_attributes";
		const ID_FIELD = "menu_attribute_id";
		const SINGULAR = "Attribute";
		const PLURAL = "Attributes";
		const LABEL_PROPERTY = "name";
		const HAS_POSITION = true;

		// MenuAttribute
		
		#[Data("name")]
		public string $name = "";
		
		#[ImageValue("icon", DOC_ROOT . "/resources/images/menus/", null, 25, ImageValue::SCALE)]
		public ?Image $icon = null;
		
		/** @var MenuItemAttribute[] */
		#[LinkFromMultiple(MenuItemAttribute::class, "menuAttribute")]
		public array $menuItemAttributes;

		/*~~~~~
		 * static methods excluding interface methods
		 **/

		/**
		 * Sets the array of Columns that are displayed to the user for this object type
		 */
		protected static function columns()
		{
			static::addColumn(new CustomColumn("name", "Name", function(MenuAttribute $menuAttribute)
			{
				if($menuAttribute->icon === null)
				{
					return $menuAttribute->name;
				}

				return $menuAttribute->icon->tag() . " " . $menuAttribute->name;
			}));

			parent::columns();
		}

		/*~~~~~
		 * non-static methods excluding interface methods
		 **/
		/**
		 * Sets the Form Elements for this object
		 */
		protected function elements()
		{
			parent::elements();

			$this->addElement(new Text("name", "Name"));
			$this->addElement(new ImageElement("icon", "Icon"));
		}
	}
