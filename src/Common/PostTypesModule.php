<?php

namespace Sparkair\SparkPlugins\SparkWoo\Common;

use Sparkair\SparkPlugins\SparkWoo\Common\Loader;
use Sparkair\SparkPlugins\SparkWoo\Common\Modules\ModuleInterface;
class PostTypesModule implements ModuleInterface
{
    /**
     * @var PostModelInterface[]
     */
    private iterable $postModels = array();
    public function __construct(iterable $postModels)
    {
        $this->postModels = $postModels;
    }
    public function definePublicHooks(Loader $loader) : void
    {
        $loader->addAction('init', $this, 'registerPosts');
    }
    public function defineAdminHooks(Loader $loader) : void
    {
    }
    public function registerPosts() : void
    {
        foreach ($this->postModels as $postModel) {
            register_post_type($postModel::postType(), $postModel::postTypeArgs());
        }
    }
}
