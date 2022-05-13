<?php
	namespace Menus;

	use Core\Attributes\Data;
	use Core\Attributes\LinkFromMultiple;
	use Core\Attributes\LinkTo;
	use Core\Elements\GeneratorElement;
	use Core\Elements\Text;
	use Core\Generator;
	
	/**
	 * A Menu Group contains a group of Menu Items
	 */
	class MenuGroup extends Generator
	{
		/*~~~~~
		 * setup
		 **/
		// Entity / Generator
		const TABLE = "menu_groups";
		const ID_FIELD = "menu_group_id";
		const PARENT_PROPERTY = "menu";
		const SINGULAR = "Item Group";
		const PLURAL = "Item Groups";
		const LABEL_PROPERTY = "heading";
		const HAS_POSITION = true;
		const HAS_ACTIVE = true;

		// MenuGroup
		public bool $active = true;
		
		#[Data("heading")]
		public string $heading = "";
		
		#[LinkTo("menu_id")]
		public Menu $menu;
		
		/** @var GroupedMenuItem[] */
		#[LinkFromMultiple(GroupedMenuItem::class, "menuGroup")]
		public array $items;
		
		/**
		 * Sets the Form Elements for this object
		 */
		protected function elements()
		{
			parent::elements();

			$this->addElement(new Text("heading", "Heading"));
			$this->addElement(new GeneratorElement("items"));
		}
		
		/**
		 * @return GroupedMenuItem[] just the menu items for display
		 */
		public function getVisibleMenuItems()
		{
			return filterActive($this->items);
		}
	}
