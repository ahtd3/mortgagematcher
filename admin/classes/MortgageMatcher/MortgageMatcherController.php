<?php

namespace MortgageMatcher;

use Controller\UrlController;
use Pages\PageController;

class MortgageMatcherController extends PageController
{

    /**
     * @return string[]
     */
    protected static function getChildPatterns()
    {
        return [
            '/action/submit-form/' => self::class
        ];
    }

    protected static function getControllerFromPattern(UrlController $parent = null, array $matches = [], $pattern ='')
    {
        if ($pattern === '/action/submit-form/' && isset($_POST['mortgage-matcher']))
        {
            // @TODO: use addMessage() function for error and success messages to user!!
            // @TODO: form validation (-> see Cellphone Directory for ideas!)
            // @TODO: save form submission in database
            // @TODO: WRITE TO google doc spreadsheet!

            //return new JsonController(['success' => true, 'redirect' => '/search-listings/']);
        }

        return null;
    }

    /**
     * @return array
     */
    protected function getTemplateVariables()
    {
        $variables = parent::getTemplateVariables();

        $variables['questions'] = Question::loadAllFor('active', true, ['position' => true]);

        return $variables;
    }
}