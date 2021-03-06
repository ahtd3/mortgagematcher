<?php
	namespace Core\Elements;
	
	use Core\Elements\Base\OptionsElement;
	
	/**
	 * Displays multiple linked checkboxes
	 */
	class MultipleCheckbox extends OptionsElement
	{
		/**
		 * Gets the path to the component for this element, relative to /admin/theme/components/elements/
		 * @return    string    The vue template
		 */
		public function getTemplate(): string
		{
			return "checkboxes/Checkboxes.js";
		}
		
		/**
		 * Gets the value for this element, pulling from the generator if such hasn't been set
		 * @return	mixed	The value
		 */
		public function getValue()
		{
			$value = parent::getValue();
			assert(is_array($value), "MultipleCheckbox expects an array for its default value");
			
			return $value;
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
			$selectedOptions = [];
			$jsonValue = $this->getJsonValue();
			
			foreach($this->options as $option)
			{
				if(in_array($option->value, $jsonValue))
				{
					$selectedOptions[] = $option->label;
				}
			}
			
			return implode(", ", $selectedOptions);
		}
	}