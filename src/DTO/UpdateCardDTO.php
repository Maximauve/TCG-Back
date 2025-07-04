<?php

namespace App\DTO;

use App\Entity\CardRarity;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

class UpdateCardDTO
{
    #[Assert\Length(min: 3, max: 255)]
    public ?string $name = null;

    #[Assert\Length(min: 10, max: 255)]
    public ?string $description = null;

    #[Assert\Image(
        maxSize: '5M',
        mimeTypes: ['image/jpeg', 'image/png', 'image/webp'],
        mimeTypesMessage: 'Please upload a valid image (JPEG, PNG or WebP)'
    )]
    #[OA\Property(type: 'string', format: 'binary', description: 'Card image file')]
    public ?UploadedFile $image = null;

    #[Assert\Length(min: 3, max: 80)]
    public ?string $artistTag = null;

    public ?CardRarity $rarity = null;

    public ?\DateTimeImmutable $releaseDate = null;

    #[Assert\Range(min: 0.0, max: 100.0)]
    public ?float $dropRate = null;

    #[Assert\Positive]
    public ?int $collectionId = null;
} 