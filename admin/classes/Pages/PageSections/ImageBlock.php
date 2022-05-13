<?php
	namespace Pages\PageSections;
	
	use Core\Attributes\Data;
	use Core\Attributes\ImageValue;
	use Core\Elements\Checkbox;
	use Core\Elements\Editor;
	use Core\Elements\Group;
	use Core\Elements\ImageElement;
	use Core\Elements\Text;
	use Core\Elements\Url;
	use Core\Generator;
	use Core\Properties\ImageProperty;
	use Files\Image;
	use Media\StreamingVideo;
	
	/**
	 * Displays an image or video next to some content
	 */
	class ImageBlock extends Generator implements PageSection
	{
		const TABLE = "image_blocks";
		const ID_FIELD = "image_block_id";
		
		#[Data("content", "html")]
		public string $content = "";
		
		#[Data("video_url")]
		public string $videoUrl = "";
		
		#[Data("image_description")]
		public string $imageDescription = "";
		
		#[ImageValue("image", DOC_ROOT . "/resources/images/page/", 924, null, ImageProperty::SCALE)]
		public ?Image $image = null;
		
		#[Data('code')]
		public string $code = '';

		protected function elements()
		{
			parent::elements();
			
			$this->addElement(new Editor("content", "Content"));
			$this->addElement((new Url("videoUrl", "Video URL"))->setConditional("return image === null"));
			
			$group = $this->addElement(new Group("imageItems"))->setConditional("return videoUrl === ''");
			$group->add(new ImageElement("image", "Image"));
			$group->add(new Text("imageDescription", "Image Description"));
			$this->addElement(new Text("code", "Class"));
		}
		
		public function getMedia(): ?StreamingVideo
		{
			if($this->videoUrl === "")
			{
				return null;
			}
			
			return StreamingVideo::get($this->videoUrl);
		}
		
		//region PageSection
		
		function getTemplateLocation(): string
		{
			return "pages/sections/image-block.twig";
		}
		
		//endregion
	}