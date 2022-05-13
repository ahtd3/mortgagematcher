<?php
	namespace Sass;

	use Configuration\Registry;
	use Pages\Page;
	use ScssPhp\ScssPhp\CompilationResult;
	use ScssPhp\ScssPhp\Compiler;
	use ScssPhp\ScssPhp\Exception\SassException;

	/**
	 * A custom Compiler that automatically adds the extra SASS functions we want
	 */
	class CustomCompiler
	{
		const CACHE_LOCATION = DOC_ROOT . "/resources/cache/scss";

		public static function getCompiler(): Compiler
		{
			if(php_sapi_name() === "cli")
			{
				$compiler = new Compiler();
			}
			else
			{
				$cacheDir = static::CACHE_LOCATION;

				// Create cache directory if it doesn't exist
				if(!file_exists($cacheDir))
				{
					mkdir($cacheDir);
					chmod($cacheDir, 0755);
				}

				$compiler = new Compiler(["cacheDir" => $cacheDir]);
			}

			$compiler->registerFunction("has-slideshow", fn() => Page::DO_BANNER);
			$compiler->registerFunction("has-blog", fn() => Registry::BLOG);
			$compiler->registerFunction("has-cart", fn() => Registry::CART);
			$compiler->registerFunction("has-gallery", fn() => Registry::GALLERIES);
			$compiler->registerFunction("has-products", fn() => Registry::PRODUCTS);
			$compiler->registerFunction("has-menus", fn() => Registry::MENUS);
			$compiler->registerFunction("has-testimonials", fn() => Registry::TESTIMONIALS);

			return $compiler;
		}
	}
