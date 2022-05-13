<?php
	namespace Core\Columns;
	
	use Core\Generator;
	
	/**
	 * A column with a custom value
	 */
	class CustomColumn implements Column
	{
		public string $name = "";
		public string $heading = "";
		public string $alignment = Column::LEFT;
		
		/** @var callable */
		public $callable;
		
		/**
		 * Creates a new custom column
		 * @param	string		$name		The name for this column
		 * @param	string		$heading	The heading for this column
		 * @param	callable	$callable	A callable that takes a CreatesTable and returns the column HTML
		 */
		public function __construct(string $name, string $heading, callable $callable)
		{
			$this->name = $name;
			$this->heading = $heading;
			$this->callable = $callable;
		}
		
		/**
		 * Sets the alignment for this column
		 * @param	string	$alignment	One of the alignment constants
		 * @return	$this				This object, for chaining
		 */
		public function setAlignment(string $alignment): self
		{
			$this->alignment = $alignment;
			
			return $this;
		}
		
		//region Column
		
		/**
		 * Gets the name for this columns
		 * @return	string	The column name
		 */
		public function getName(): string
		{
			return $this->name;
		}
		
		/**
		 * Gets the heading for this column
		 * @return    string    The heading
		 */
		public function getHeading(): string
		{
			return $this->heading;
		}
		
		/**
		 * Gets the alignment for this column
		 * @return    string    One of the alignment constants
		 */
		public function getAlignment(): string
		{
			return $this->alignment;
		}
		
		/**
		 * Gets the value for this column, given a CreatesTable object
		 * @param Generator $object The object to get the value from
		 * @return    ColumnCell                    The cell for this column
		 */
		public function getValueFor(Generator $object): ColumnCell
		{
			$callable = $this->callable;
			
			return new ColumnCell("html-cell", ["html" => $callable($object)], "", $this->alignment);
		}
		
		//endregion
	}