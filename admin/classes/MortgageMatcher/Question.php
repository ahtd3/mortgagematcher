<?php

namespace MortgageMatcher;

use Admin\AdminNavItem;
use Configuration\Registry;
use Core\Attributes\Data;
use Core\Columns\PropertyColumn;
use Core\Elements\Text;
use Core\Generator;
use Pages\PageType;
use Users\SuperAdministrator;
use Users\User;

class Question extends Generator
{
    const TABLE = 'mortgage_matcher_questions';
    const ID_FIELD = 'id';
    const SINGULAR = 'Question';
    const PLURAL = 'Questions';
    const LABEL_PROPERTY = 'title';

    const HAS_ACTIVE = true;
    const HAS_POSITION = true;

    const SPREADSHEET_HEADERS = [
        '' => ''
    ];

    const REQUIRED_QUESTIONS = [
        'topLevelQuestion',
        'propertyType',
        'propertyValue',
        'propertyDeposit',
        'propertyDepositOrigin',
        'annualHouseholdIncome',
        'employedFor',
        'currentPostCodeAndCity',
        'contactData'
    ];

    const QUESTION_IDENTIFIERS_BY_TOP_LEVEL_QUESTION_OPTION = [
        'propertyToLiveIn' => [
            'propertyType',
            'propertyValue',
            'buyWithAnotherPerson',
            'propertyDeposit',
            'propertyDepositOrigin',
            'currentPropertyValue',
            'currentMortgagesOwing',
            'annualHouseholdIncome',
            'employedFor',
            'currentPostCodeAndCity',
            'contactData',
            'comment'
        ],
        'investmentProperty' => [
            'propertyValue',
            'buyWithAnotherPerson',
            'propertyDeposit',
            'propertyDepositOrigin',
            'currentPropertyValue',
            'currentMortgagesOwing',
            'annualHouseholdIncome',
            'employedFor',
            'currentPostCodeAndCity',
            'contactData',
            'comment'
        ],
        'secondHome' => [
            'propertyValue',
            'buyWithAnotherPerson',
            'propertyDeposit',
            'propertyDepositOrigin',
            'currentPropertyValue',
            'currentMortgagesOwing',
            'annualHouseholdIncome',
            'employedFor',
            'currentPostCodeAndCity',
            'contactData',
            'comment'
        ],
        'mortgageReview' => [
            'currentMortgagesOwing',
            'annualHouseholdIncome',
            'employedFor',
            'currentPostCodeAndCity',
            'contactData',
            'comment'
        ]
    ];

    public bool $active = true;

    #[Data('identifier')]
    public string $identifier = '';

    #[Data('title')]
    public string $title = '';


    protected static function columns()
    {
        if (User::get() instanceof SuperAdministrator)
        {
            static::addColumn(new PropertyColumn('identifier', 'Identifier'));
        }

        static::addColumn(new PropertyColumn('title', 'Title'));

        parent::columns();

        if (!User::get() instanceof SuperAdministrator)
        {
            static::removeColumn('delete');
        }
    }

    protected function elements()
    {
        parent::elements();

        if (User::get() instanceof SuperAdministrator)
        {
            $this->addElement((new Text('identifier', 'Identifier'))->setHint('Required')->addValidation(Text::REQUIRED));
        }

        $this->addElement((new Text('title', 'Title'))->setHint('Required')->addValidation(Text::REQUIRED));
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    public static function canAdd(User $user): bool
    {
        return User::get() instanceof SuperAdministrator;
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    public function canDelete(User $user)
    {
        return User::get() instanceof SuperAdministrator;
    }

    /**
     * @return AdminNavItem
     */
    public static function getAdminNavItem()
    {
        return new AdminNavItem(
            static::getAdminNavLink(),
            PageType::MORTGAGE_MATCHER,
            [static::class],
            Registry::MORTGAGE_MATCHER
        );
    }

    /**
     * @return bool
     */
    public function isRequired(): bool
    {
        return in_array($this->identifier, static::REQUIRED_QUESTIONS);
    }

    /**
     * @param string $topLevelQuestionOption
     *
     * @return array
     */
    public function getQuestionIdentifiersByTopLevelQuestionOption(string $topLevelQuestionOption): array
    {
        return static::QUESTION_IDENTIFIERS_BY_TOP_LEVEL_QUESTION_OPTION[$topLevelQuestionOption];
    }
}