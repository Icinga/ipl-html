<?php

namespace ipl\Tests\Html\FormElement;

use ipl\Tests\Html\TestCase;
use ipl\Tests\Html\TestDummy\TestFormElement;
use ipl\Validator\TestValidator;

class ValidatorSpecTest extends TestCase
{
    public static function setUpBeforeClass()
    {
        require_once dirname(__DIR__) . '/TestDummy/TestValidator.php';
    }

    public function testNameToOptionsSpec()
    {
        $options = [
            'key' => 'value'
        ];

        $spec = [
            'test' => $options
        ];

        $validators = $this->populateValidators($spec);

        $this->assertCount(1, $validators);
        $this->assertInstanceOf('\\ipl\\Validator\\TestValidator', $validators[0]);
        $this->assertSame($options, $validators[0]->getOptions());
    }

    public function testArraySpec()
    {
        $options = [
            'key' => 'value'
        ];

        $spec = [
            [
                'name'    => 'test',
                'options' => $options
            ]
        ];

        $validators = $this->populateValidators($spec);

        $this->assertCount(1, $validators);
        $this->assertInstanceOf('\\ipl\\Validator\\TestValidator', $validators[0]);
        $this->assertSame($options, $validators[0]->getOptions());
    }

    public function testArraySpecWithoutOptions()
    {
        $spec = [
            [
                'name' => 'test'
            ]
        ];

        $validators = $this->populateValidators($spec);

        $this->assertCount(1, $validators);
        $this->assertInstanceOf('\\ipl\\Validator\\TestValidator', $validators[0]);
        $this->assertNull($validators[0]->getOptions());
    }

    public function testWithValidatorInstance()
    {
        $spec = [
            new TestValidator()
        ];

        $validators = $this->populateValidators($spec);

        $this->assertCount(1, $validators);
        $this->assertInstanceOf('\\ipl\\Validator\\TestValidator', $validators[0]);
    }

    public function testWithAllSpecsMixed()
    {
        $spec = [
            'test' => [],
            [
                'name' => 'test'
            ],
            new TestValidator()
        ];

        $validators = $this->populateValidators($spec);

        $this->assertCount(3, $validators);
        $this->assertInstanceOf('\\ipl\\Validator\\TestValidator', $validators[0]);
        $this->assertInstanceOf('\\ipl\\Validator\\TestValidator', $validators[1]);
        $this->assertInstanceOf('\\ipl\\Validator\\TestValidator', $validators[2]);
    }

    /**
     * @expectedException   \InvalidArgumentException
     */
    public function testArraySpecExceptionIfNameIsMissing()
    {
        $spec = [
            [
            ]
        ];

        $this->populateValidators($spec);
    }

    /**
     * @expectedException   \InvalidArgumentException
     */
    public function testNameToOptionsSpecExceptionIfClassDoesNotExist()
    {
        $spec = [
            'doesnotexist' => null
        ];

        $this->populateValidators($spec);
    }

    /**
     * @expectedException   \InvalidArgumentException
     */
    public function testNameToOptionsSpecExceptionIfNameIsNotTheKeyAndThereforeOptionsNotAnArray()
    {
        $spec = [
            'test'
        ];

        $this->populateValidators($spec);
    }

    /**
     * @param   array   $spec
     *
     * @return  \ipl\Stdlib\Contracts\ValidatorInterface[]
     */
    protected function populateValidators(array $spec)
    {
        $element = new TestFormElement('test');

        $element->setValidators($spec);

        return $element->getValidators();
    }
}
