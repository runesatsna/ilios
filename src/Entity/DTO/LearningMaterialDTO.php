<?php

namespace App\Entity\DTO;

use App\Annotation as IS;

/**
 * Class LearningMaterialDTO
 * Data transfer object for a learning materials
 *
 * @IS\DTO
 */
class LearningMaterialDTO
{

    /**
     * @var int
     *
     * @IS\Expose
     * @IS\Type("integer")
     */
    public $id;

    /**
     * @var string
     *
     * @IS\Expose
     * @IS\Type("string")
     */
    public $title;

    /**
     * @var string
     *
     * @IS\Expose
     * @IS\Type("string")
     */
    public $description;

    /**
     * @var \DateTime
     *
     * @IS\Expose
     * @IS\Type("dateTime")
     */
    public $uploadDate;

    /**
     * @var string
     *
     * @IS\Expose
     * @IS\Type("string")
     */
    public $originalAuthor;

    /**
     * @var string
     *
     * @IS\Expose
     * @IS\Type("entity")
     */
    public $userRole;

    /**
     * @var string
     *
     * @IS\Expose
     * @IS\Type("entity")
     */
    public $status;

    /**
     * @var string
     *
     * @IS\Expose
     * @IS\Type("entity")
     */
    public $owningUser;

    /**
     * @var string[]
     *
     * @IS\Expose
     * @IS\Type("entityCollection")
     */
    public $sessionLearningMaterials;

    /**
     * @var string[]
     *
     * @IS\Expose
     * @IS\Type("entityCollection")
     */
    public $courseLearningMaterials;

    /**
     * @var string
     *
     * @IS\Expose
     * @IS\Type("string")
     */
    public $citation;

    /**
     * @var bool
     *
     * @IS\Expose
     * @IS\Type("boolean")
     */
    public $copyrightPermission;

    /**
     * @var string
     *
     * @IS\Expose
     * @IS\Type("string")
     */
    public $copyrightRationale;

    /**
     * @var string
     *
     * @IS\Expose
     * @IS\Type("string")
     */
    public $filename;

    /**
     * @var string
     *
     * @IS\Expose
     * @IS\Type("string")
     */
    public $mimetype;

    /**
     * @var string
     *
     * @IS\Expose
     * @IS\Type("integer")
     */
    public $filesize;


    /**
     * @var string
     *
     * @IS\Expose
     * @IS\Type("string")
     */
    public $link;

    /**
     * @var string
     *
     * @IS\Expose
     * @IS\Type("string")
     */
    public $absoluteFileUri;


    /**
     * Not exposed, it is used to build the URI later
     * @var string
     *
     * @IS\Type("string")
     */
    public $token;

    /**
     * Not exposed, used by indexing
     * @var string
     */
    public $relativePath;

    public function __construct(
        $id,
        $title,
        $description,
        $uploadDate,
        $originalAuthor,
        $citation,
        $copyrightPermission,
        $copyrightRationale,
        $filename,
        $mimetype,
        $filesize,
        $link,
        $token,
        $relativePath
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->uploadDate = $uploadDate;
        $this->originalAuthor = $originalAuthor;
        $this->citation = $citation;
        $this->copyrightPermission = $copyrightPermission;
        $this->copyrightRationale = $copyrightRationale;
        $this->filename = $filename;
        $this->mimetype = $mimetype;
        $this->filesize = $filesize;
        $this->link = $link;
        $this->token = $token;
        $this->relativePath = $relativePath;

        $this->sessionLearningMaterials = [];
        $this->courseLearningMaterials = [];
    }

    /**
     * Blanks out most of the material's attributes.
     */
    public function clearMaterial(): void
    {
        $this->absoluteFileUri = null;
        $this->citation = null;
        $this->copyrightRationale = null;
        $this->description = null;
        $this->filename = null;
        $this->filesize = null;
        $this->link = null;
        $this->mimetype = null;
        $this->originalAuthor = null;
        $this->token = null;
    }
}
