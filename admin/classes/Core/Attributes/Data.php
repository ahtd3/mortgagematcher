<?php
	namespace Core\Attributes;
	
	use Attribute;
	use Core\Properties\Property;
	use DateTime;
	use DateTimeImmutable;
	use DateTimeInterface;
	use Error;
	
	/**
	 * The attribute for a general property
	 */
	#[Attribute(Attribute::TARGET_PROPERTY)]
	class Data extends PropertyAttribute
	{
		/**
		 * Creates a new data
		 * @param	string      	$databaseName	The name of the associated field in the database
		 * @param	string|null		$type			An optional type to override the passed in type
		 */
		public function __construct(public string $databaseName, public string|null $type = null){}
		
		/**
		 * @inheritDoc
		 */
		public function getProperty(string $variableName, string $variableType): Property
		{
			if($variableType === "" && $this->type === null) throw new Error("No type defined for {$variableName}");
			if(in_array($variableType, [DateTime::class, DateTimeImmutable::class, DateTimeInterface::class])) $variableType = "datetime";
			
			return new Property($variableName, $this->databaseName, $this->type ?? $variableType);
		}
	}