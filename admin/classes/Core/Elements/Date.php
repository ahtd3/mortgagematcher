<?php
	namespace Core\Elements;
	
	use Core\Elements\Base\LabelledResultElement;
	use DateTimeImmutable;
	use DateTimeInterface;
	use Exception;
	
	/**
	 * An input for a date (but not a time)
	 */
	class Date extends LabelledResultElement
	{
		/**
		 * Gets the path to the component for this element, relative to /admin/theme/components/elements/
		 * @return    string    The vue template
		 */
		public function getTemplate(): string
		{
			return "datetime/DateElement.js";
		}
		
		/**
		 * Gets the value for this element, pulling from the generator if such hasn't been set
		 * @return	string	The value
		 */
		public function getValue()
		{
			return parent::getValue()?->format("Y-m-d") ?? "";
		}
		
		/**
		 * Gets the result of this element
		 * @param	mixed	$json	The JSON to retrieve the result from
		 * @return	mixed            The result that will be passed to the result handler
		 */
		public function getResult($json)
		{
			if($json === "")
			{
				return null;
			}
			
			try
			{
				return new DateTimeImmutable($json);
			}
			catch(Exception $exception)
			{
				return new DateTimeImmutable();
			}
		}
		
		/**
		 * Gets the default label produced by this element, this will be used until the element is rendered
		 * @return	string|null		The default label, or null if this element shouldn't generate a label
		 */
		public function getDefaultGeneratorLabel(): ?string
		{
			return parent::getValue()?->format("d/m/Y");
		}
	}