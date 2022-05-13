<?php
	namespace Core\Elements;
	
	use Core\Elements\Base\Element;
	
	/**
	 * Outputs a heading
	 * Before using this element consider if you would be better using:
	 * - A Section element to contain elements the heading is to refer to
	 * - An Html or Output element, or setHint()/setDescription() methods (where supported) if the content is more of an informative nature than a semantic heading.
	 */
	class Heading extends Element
	{
		public $heading = "";
		
		public function __construct(string $name, string $heading)
		{
			parent::__construct($name);
			
			$this->heading = $heading;
		}
		
		/**
		 * Gets the path to the component for this element, relative to /admin/theme/components/elements/
		 * @return    string    The vue template
		 */
		public function getTemplate(): string
		{
			return "Heading.js";
		}
		
		public function getProps(): array
		{
			return parent::getProps() + ["heading" => $this->heading];
		}
		
		public function getDefaultGeneratorLabel(): ?string
		{
			return $this->heading;
		}
	}