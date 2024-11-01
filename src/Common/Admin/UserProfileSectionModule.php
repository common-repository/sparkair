<?php

namespace Sparkair\SparkPlugins\SparkWoo\Common\Admin;

use Sparkair\SparkPlugins\SparkWoo\Common\Loader;
use Sparkair\SparkPlugins\SparkWoo\Common\Modules\ModuleInterface;
use Sparkair\SparkPlugins\SparkWoo\Common\Plugins\GlobalVariables;
class UserProfileSectionModule implements ModuleInterface
{
    const USER_PROFILE_ACTION = GlobalVariables::SPARKWOO_PREFIX . 'user_profile_section';
    public function defineAdminHooks(Loader $loader) : void
    {
        $loader->addAction('edit_user_profile', $this, 'addUserProfileSection');
        $loader->addAction('show_user_profile', $this, 'addUserProfileSection');
    }
    public function definePublicHooks(Loader $loader) : void
    {
    }
    public function addUserProfileSection($user)
    {
        $actionName = $this::USER_PROFILE_ACTION;
        if (!has_action($actionName) || did_action($actionName)) {
            return;
        }
        echo '<div class="sparkwoo-admin">';
        echo '<h3>SparkPlugins</h3>';
        echo '<div class="grid grid-cols-12 gap-5 max-w-7xl">';
        do_action($actionName, $user);
        echo '</div>';
        echo '</div>';
    }
}
