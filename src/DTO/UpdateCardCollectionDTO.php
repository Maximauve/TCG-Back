<?php

namespace App\DTO;

use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

class UpdateCardCollectionDTO
{
    #[Assert\Length(min: 3, max: 255)]
    public ?string $name = null;

    #[Assert\Length(min: 3, max: 255)]
    public ?string $description = null;

    #[Assert\Image(
        maxSize: '5M',
        mimeTypes: ['image/jpeg', 'image/png', 'image/webp'],
        mimeTypesMessage: 'Please upload a valid image (JPEG, PNG or WebP)'
    )]
    #[OA\Property(type: 'string', format: 'binary', description: 'Display image file')]
    public ?UploadedFile $displayImage = null;

    #[Assert\Image(
        maxSize: '5M',
        mimeTypes: ['image/jpeg', 'image/png', 'image/webp'],
        mimeTypesMessage: 'Please upload a valid image (JPEG, PNG or WebP)'
    )]
    #[OA\Property(type: 'string', format: 'binary', description: 'Booster image file')]
    public ?UploadedFile $boosterImage = null;

    public ?\DateTimeImmutable $releaseDate = null;

    #[Assert\GreaterThan(propertyPath: 'releaseDate', message: 'End date must be after release date')]
    public ?\DateTimeImmutable $endDate = null;

    public ?bool $isSpecial = null;
} 