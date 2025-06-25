<?php
namespace App\Tests\Form\Type;

use App\Form\Type\RegistrationType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validation;

/**
 * Testy formularza rejestracji użytkownika (RegistrationType).
 */
class RegistrationTypeTest extends TypeTestCase
{
    /**
     * Zwraca rozszerzenia formularza potrzebne do testów.
     *
     * @return array<int, \Symfony\Component\Form\FormExtensionInterface>
     */
    protected function getExtensions(): array
    {
        $validator = Validation::createValidatorBuilder()
            ->enableAnnotationMapping()
            ->getValidator();

        return [
            new PreloadedExtension([new RegistrationType()], []),
            new ValidatorExtension($validator),
        ];
    }

    /**
     * Testuje obecność i konfigurację pól formularza.
     *
     * @return void
     */
    public function testFormFieldsAndOptions(): void
    {
        $form = $this->factory->create(RegistrationType::class);

        $this->assertTrue($form->has('email'));
        $this->assertTrue($form->has('password'));

        $this->assertInstanceOf(EmailType::class, $form->get('email')->getConfig()->getType()->getInnerType());

        $emailConstraints = $form->get('email')->getConfig()->getOption('constraints');
        $this->assertNotEmpty($emailConstraints);
        $this->assertContainsOnlyInstancesOf(\Symfony\Component\Validator\Constraint::class, $emailConstraints);

        $notBlankFound = false;
        $lengthConstraint = null;
        foreach ($emailConstraints as $constraint) {
            if ($constraint instanceof NotBlank) {
                $notBlankFound = true;
            }
            if ($constraint instanceof Length) {
                $lengthConstraint = $constraint;
            }
        }
        $this->assertTrue($notBlankFound, 'NotBlank constraint found on email');
        $this->assertNotNull($lengthConstraint, 'Length constraint found on email');
        $this->assertEquals(3, $lengthConstraint->min);
        $this->assertEquals(191, $lengthConstraint->max);

        $this->assertInstanceOf(RepeatedType::class, $form->get('password')->getConfig()->getType()->getInnerType());
        $this->assertSame(PasswordType::class, $form->get('password')->getConfig()->getOption('type'));

        $passwordConstraints = $form->get('password')->getConfig()->getOption('constraints');
        $this->assertNotEmpty($passwordConstraints);

        $notBlankFound = false;
        $lengthConstraint = null;
        foreach ($passwordConstraints as $constraint) {
            if ($constraint instanceof NotBlank) {
                $notBlankFound = true;
            }
            if ($constraint instanceof Length) {
                $lengthConstraint = $constraint;
            }
        }
        $this->assertTrue($notBlankFound, 'NotBlank constraint found on password');
        $this->assertNotNull($lengthConstraint, 'Length constraint found on password');
        $this->assertEquals(6, $lengthConstraint->min);
        $this->assertEquals(191, $lengthConstraint->max);

        $this->assertEquals('Password', $form->get('password')->getConfig()->getOption('first_options')['label']);
        $this->assertEquals('Repeat password', $form->get('password')->getConfig()->getOption('second_options')['label']);
    }

    /**
     * Testuje metodę getBlockPrefix.
     *
     * @return void
     */
    public function testGetBlockPrefix(): void
    {
        $type = new RegistrationType();
        $this->assertSame('user', $type->getBlockPrefix());
    }
}
