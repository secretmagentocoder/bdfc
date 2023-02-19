<?php
namespace Amasty\Orderattr\Test\Unit\Model\Value\Metadata\Form\File\Uploader;

use Amasty\Orderattr\Api\CheckoutAttributeRepositoryInterface;
use Amasty\Orderattr\Api\Data\CheckoutAttributeInterface;
use Amasty\Orderattr\Model\Attribute\Attribute;
use Amasty\Orderattr\Model\Value\Metadata\Form\File;
use Amasty\Orderattr\Model\Value\Metadata\Form\File\Uploader\Validator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManagerMock;

    /**
     * @var CheckoutAttributeRepositoryInterface|MockObject
     */
    private $attributeRepositoryMock;

    /**
     * @var Validator
     */
    private $validator;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->objectManagerMock = $this->createMock(ObjectManagerInterface::class);
        $this->attributeRepositoryMock = $this->createMock(CheckoutAttributeRepositoryInterface::class);

        $this->validator = $objectManager->getObject(
            Validator::class,
            [
                'attributeRepository' => $this->attributeRepositoryMock,
                'objectManager' => $this->objectManagerMock
            ]
        );
    }

    /**
     * Test validateAttributeCode
     *
     * @param bool $isValid
     * @param bool $noSuchAttribute
     * @dataProvider validateAttributeCodeProvider
     */
    public function testValidateAttributeCode($isValid, $noSuchAttribute = false)
    {
        $attributeCode = 'test_file';
        $attributeMock = $this->createMock(CheckoutAttributeInterface::class);

        if ($noSuchAttribute) {
            $this->attributeRepositoryMock
                ->expects($this->once())
                ->method('get')
                ->willThrowException(new LocalizedException(__('No such attribute.')));
        } else {
            $this->attributeRepositoryMock
                ->expects($this->once())
                ->method('get')
                ->with($attributeCode)
                ->willReturn($attributeMock);
        }

        $attributeMock
            ->expects($this->exactly($noSuchAttribute ? 0 : 1))
            ->method('getFrontendInput')
            ->willReturn($isValid ? 'file' : 'text');

        $this->assertEquals($isValid, $this->validator->validateAttributeCode($attributeCode));
    }

    /**
     * Test validate file method
     *
     * @param bool $isValid
     * @param bool $isFileAttribute
     * @dataProvider validateFileProvider
     */
    public function testValidateFile($isValid, $isFileAttribute = true)
    {
        $attributeCode = 'test_file';
        $attributeMock = $this->createMock(Attribute::class);
        $dataModelClass = File::class;
        $dataModelMock = $this->createMock(File::class);
        $callsCount = $isFileAttribute ? 1 : 0;
        $fileInfo = [
            'name' => 'test_file.txt',
            'size' => 41431232
        ];
        $errors = $isValid
            ? []
            : ['File extension is not supported'];

        $this->attributeRepositoryMock
            ->expects($this->once())
            ->method('get')
            ->with($attributeCode)
            ->willReturn($attributeMock);
        $attributeMock
            ->expects($this->once())
            ->method('getFrontendInput')
            ->willReturn($isFileAttribute ? 'file' : 'text');
        $attributeMock
            ->expects($this->exactly($callsCount))
            ->method('getDataModel')
            ->willReturn($dataModelClass);
        $this->objectManagerMock
            ->expects($this->exactly($callsCount))
            ->method('create')
            ->with($dataModelClass)
            ->willReturn($dataModelMock);
        $dataModelMock
            ->expects($this->exactly($callsCount))
            ->method('setAttribute')
            ->with($attributeMock)
            ->willReturnSelf();
        $dataModelMock
            ->expects($this->exactly($callsCount))
            ->method('validateTmpValue')
            ->with($fileInfo)
            ->willReturn($errors);

        $this->assertEquals(
            $isValid && $isFileAttribute,
            $this->validator->validateFile($attributeCode, $fileInfo)
        );
    }

    /**
     * @return array
     */
    public function validateAttributeCodeProvider()
    {
        return [
            [true],
            [false],
            [false, true]
        ];
    }

    /**
     * @return array
     */
    public function validateFileProvider()
    {
        return [
            [true],
            [false, false],
            [true, false]
        ];
    }
}
