<?php

namespace CentralityLabs\SparkAddons;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Addon extends Model
{
    /**
     * The add-on's unique ID.
     *
     * @var string
     */
    public $id;
    /**
     * The add-on's displayable name.
     *
     * @var string
     */
    public $name;
    /**
     * The add-on's logo.
     *
     * @var string
     */
    public $logo;
    /**
     * Short description for the add-on.
     *
     * @var string
     */
    public $description;
    /**
     * Information about who will be maintaining the add-on.
     *
     * @var string
     */
    public $maintainer;
    /**
     * The website for this add-on.
     *
     * @var string
     */
    public $website;
    /**
     * Support link for this add-on
     *
     * @var string
     */
    public $support;
    /**
     * Documentation link for this add-on.
     *
     * @var string
     */
    public $documentation;
    /**
     *
     * @param \stdClass|array|null $parameters
     */
    public function __construct($parameters = null)
    {
        if (!$parameters) {
            return;
        }
        if ($parameters instanceof \stdClass) {
            $parameters = get_object_vars($parameters);
        }
        $this->build($parameters);
    }
    /**
     * Build by filling the public properties with values from array.
     *
     * @param array $parameters
     */
    public function build(array $parameters)
    {
        foreach ($parameters as $property => $value) {
            $property = Str::camel($property);
            if(property_exists($this, $property) && (new \ReflectionProperty($this, $property))->isPublic()) {
                $this->$property = $value;
            }
        }
    }
}
