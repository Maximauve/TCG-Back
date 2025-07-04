<?php

namespace App\DTO;

use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

class CreateCardCollectionDTO
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 255)]
    public string $name;

    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 255)]
    public string $description;

    #[Assert\NotNull]
    #[Assert\Image(
        maxSize: '5M',
        mimeTypes: ['image/jpeg', 'image/png', 'image/webp'],
        mimeTypesMessage: 'Please upload a valid image (JPEG, PNG or WebP)'
    )]
    #[OA\Property(type: 'string', format: 'binary', description: 'Display image file')]
    public ?UploadedFile $displayImage = null;

    #[Assert\NotNull]
    #[Assert\Image(
        maxSize: '5M',
        mimeTypes: ['image/jpeg', 'image/png', 'image/webp'],
        mimeTypesMessage: 'Please upload a valid image (JPEG, PNG or WebP)'
    )]
    #[OA\Property(type: 'string', format: 'binary', description: 'Booster image file')]
    public ?UploadedFile $boosterImage = null;

    #[Assert\NotNull]
    public \DateTimeImmutable $releaseDate;

    #[Assert\NotNull]
    #[Assert\GreaterThan(propertyPath: 'releaseDate')]
    public \DateTimeImmutable $endDate;

    #[Assert\NotNull]
    public bool $isSpecial = false;
} 