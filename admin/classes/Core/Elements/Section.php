<?php
	namespace Core\Elements;
	
	/**
	 * A group of form elements
	 */
	class Section extends Group
	{
		public $heading = "";
		
		/**
		 * Creates a new Group
		 * @param	string			$name		The name for the element
		 * @param	string			$heading	The heading for this section
		 * @param	callable|null	$children	A callable for adding the children directly to the group
		 */
		public function __construct(string $name, string $heading, ?callable $children = null)
		{
			parent::__construct($name, $children);
			
			$this->heading = $heading;
		}
		
		/**
		 * Gets the path to the component for this element, relative to /admin/theme/components/elements/
		 * @return    string    The vue template
		 */
		public function getTemplate(): string
		{
			return "SectionElement.js";
		}
		
		/**
		 * Gets the value to pass to the Vue component
		 * @return	array	The value to pass to the component (will be JSON encoded)
		 */
		public function getProps(): array
		{
			$json = parent::getProps() + ["heading" => $this->heading];
			
			if($this->heading !== null)
			{
				$json["heading"] = $this->heading;
			}
			
			return $json;
		}
	}