<?php

namespace Sparkair\SparkPlugins\SparkWoo\Common\Models;

interface PostModelInterface
{
    public static function postType() : string;
    public static function postTypeArgs() : array;
    public function create() : ?self;
    public function postToObject(\WP_Post $post) : ?self;
    public function load(int $id) : ?self;
    public function fromArray(array $data) : ?self;
    public function sanitize(string $property, $value);
    public function sanitizeArrayProperty(string $property, $value);
    public function validate(string $property, $value);
    public function persist() : ?self;
    public function set(string $property, $value);
    public function get(string $property, $default = null);
    public function delete();
    public static function computedProperties() : array;
}
