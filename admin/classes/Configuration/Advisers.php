<?php
namespace Configuration;

use Core\Elements\Editor;
use Core\Elements\Text;
use Core\Generator;
use Core\Properties\Property;
use Core\Attributes\LinkTo;
use Core\Elements\ImageElement;
use Core\Properties\ImageProperty;
use Configuration\Configuration;
use Core\Properties\LinkToProperty;
use JsonSerializable;

class Advisers extends Generator implements JsonSerializable
{
    const TABLE = "advisers";
    const ID_FIELD = "id";
    const LABEL_PROPERTY = "label";
    const HAS_POSITION = true;

    public $label = "";
    public $image = null;
    public $link= "";
    /** @var Configuration */
    public $parent;

    protected static function properties() {
        parent::properties();
        static::addProperty(new ImageProperty("image", "image", DOC_ROOT . "/resources/images/advisers/", 650, 650, ImageProperty::SCALE));
		static::addProperty(new Property("label", "label", "string"));
		static::addProperty(new Property("link", "link", "string"));
        static::addProperty(new LinkToProperty("parent", "parent_id", Configuration::class));
    }

    protected function elements() {
        parent::elements();
        $this->addElement(new ImageElement("image", "logo"));
        $this->addElement(new Text("label", "label"));
        $this->addElement(new Text("link", "link"));
    }
}