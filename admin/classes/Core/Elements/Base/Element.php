<?php
	namespace Core\Elements\Base;
	
	use Assets\JsImportHandler;
	use Core\Generator;
	
	/**
	 * A single element that is displayed within the form
	 */
	abstract class Element
	{
		const ELEMENTS_PATH = DOC_ROOT . "/admin/theme/components/elements/";
		
		public $name;
		
		/** @var Generator */
		public $generator;
		
		public $conditional = "return true";
		
		public $classes = [];
		
		/**
		 * Creates a new element
		 * @param	string	$name	The name of the element
		 */
		public function __construct(string $name)
		{
			$this->name = $name;
		}
		
		/**
		 * Adds a class to this element
		 * @param	string	$class	The class to add
		 * @return	$this			This element, for chaining
		 */
		public function addClass(string $class): self
		{
			$this->classes[] = $class;
			$this->classes = array_unique($this->classes);
			
			return $this;
		}
		
		/**
		 * Adds multiple classes to this element
		 * @param	string[]	$classes	The classes to add
		 * @return	$this					This element, for chaining
		 */
		public function addClasses(array $classes): self
		{
			$this->classes = array_merge($this->classes, $classes);
			$this->classes = array_unique($this->classes);
			
			return $this;
		}
		
		/**
		 * Removes a class from this element
		 * @param	string	$class	The class to remove
		 * @return	$this			This element, for chaining
		 */
		public function removeClass(string $class): self
		{
			$index = array_search($class, $this->classes);
			
			if($index !== false)
			{
				unset($this->classes[$index]);
				$this->classes = array_values($this->classes);
			}
			
			return $this;
		}
		
		/**
		 * Runs after this element has been added to its parent, will set the base generator and anything else that needs to be setup
		 * @param	ElementParent	$parent		The container that this element was added to
		 */
		public function afterAdd(ElementParent $parent)
		{
			$this->generator = $parent->getGenerator();
		}
		
		/**
		 * Allows this element to be shown conditionally
		 * This will be converted into a function, and have access to the current component via "this"
		 * @param	string	$conditional	A boolean JavaScript expression that results in evaluation
		 * @return	$this					This element, for chaining
		 */
		public function setConditional(string $conditional): self
		{
			$this->conditional = $conditional;
			
			return $this;
		}
		
		/**
		 * Gets the path to the component for this element, relative to /admin/theme/components/elements/
		 * @return	string	The vue template
		 */
		abstract public function getTemplate(): string;
		
		/**
		 * Gets the default label produced by this element, this will be used until the element is rendered
		 * @return	string|array|null		The default label, or an object if this is a group element, or null if this element shouldn't generate a label
		 */
		abstract public function getDefaultGeneratorLabel();
		
		/**
		 * Gets the value to pass to the Vue component
		 * @return	array	The value to pass to the component (will be JSON encoded)
		 */
		public function getProps(): array
		{
			$componentPath = self::ELEMENTS_PATH . $this->getTemplate();
			$fullUrl = JsImportHandler::getCacheUrlFor($componentPath);
			
			return
			[
				"name" => $this->name,
				"componentLocation" => $fullUrl,
				"classes" => implode(" ", $this->classes),
				"conditional" => $this->conditional
			];
		}
	}