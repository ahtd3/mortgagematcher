<?php
	namespace Core\Elements;
	
	use Core\Elements\Base\OptionsElement;
	
	/**
	 * Displays a dropdown list of options
	 */
	class Select extends OptionsElement
	{
		/**
		 * Gets the path to the component for this element, relative to /admin/theme/components/elements/
		 * @return    string    The vue template
		 */
		public function getTemplate(): string
		{
			return "SelectElement.js";
		}
		
		/**
		 * Gets the result of this element
		 * @param mixed $json The JSON to retrieve the result from
		 * @return    mixed            The result that will be passed to the result handler
		 */
		public function getResult($json)
		{
			return $json;
		}
		
		/**
		 * Gets the default label produced by this element, this will be used until the element is rendered
		 * @return	string|null		The default label, or null if this element shouldn't generate a label
		 */
		public function getDefaultGeneratorLabel(): ?string
		{
			$jsonValue = $this->getJsonValue();
			
			foreach($this->options as $option)
			{
				if($option->value == $jsonValue)
				{
					return $option->label;
				}
			}
			
			return null;
		}
	}