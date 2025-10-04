<?php

namespace App\Validation;

use Symfony\Component\Validator\Constraints as Assert;

class NotificationValidation
{
    #[Assert\NotBlank(message: 'Recipient email is required', groups: ['create'])]
    #[Assert\Email(message: 'Invalid email address', groups: ['create'])]
    public ?string $recipientEmail = null;

    #[Assert\NotBlank(message: 'Subject is required', groups: ['create'])]
    #[Assert\Length(
        max: 255,
        maxMessage: 'Subject cannot be longer than {{ limit }} characters',
        groups: ['create']
    )]
    public ?string $subject = null;

    #[Assert\NotBlank(message: 'Body is required', groups: ['create'])]
    public ?string $body = null;
}
