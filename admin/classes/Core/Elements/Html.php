<?php
	namespace Core\Elements;
	
	use Core\Elements\Base\Element;
	
	/**
	 * Outputs some HTML
	 */
	class Html extends Element
	{
		public $html = "";
		
		/**
		 * Creates a new element
		 * @param	string	$name	The name of the element
		 * @param	string	$html	The HTML to display
		 */
		public function __construct(string $name, string $html)
		{
			parent::__construct($name);
			
			$this->html = $html;
		}
		
		/**
		 * Gets the path to the component for this element, relative to /admin/theme/components/elements/
		 * @return    string    The vue template
		 */
		public function getTemplate(): string
		{
			return "HtmlElement.js";
		}
		
		/**
		 * Gets the value to pass to the Vue component
		 * @return	array	The value to pass to the component (will be JSON encoded)
		 */
		public function getProps(): array
		{
			return parent::getProps() +
			[
				"html" => $this->html
			];
		}
		
		/**
		 * Gets the default label produced by this element, this will be used until the element is rendered
		 * @return	string|null		The default label, or null if this element shouldn't generate a label
		 */
		public function getDefaultGeneratorLabel(): ?string
		{
			return strip_tags($this->html);
		}
	}