<?php

namespace Sparkair\SparkPlugins\SparkWoo\Common\Notifications;

use Sparkair\SparkPlugins\SparkWoo\Common\Loader;
use Sparkair\SparkPlugins\SparkWoo\Common\Modules\ModuleInterface;
use Sparkair\SparkPlugins\SparkWoo\Common\Plugins\GlobalVariables;
use Sparkair\SparkPlugins\SparkWoo\Common\Plugins\PluginMeta;
use Sparkair\SparkPlugins\SparkWoo\Common\StylesScripts\StylesScriptsModule;
class NotificationModule implements ModuleInterface
{
    private PluginMeta $pluginMeta;
    private GlobalVariables $globalVariables;
    public function __construct(PluginMeta $pluginMeta, GlobalVariables $globalVariables)
    {
        $this->pluginMeta = $pluginMeta;
        $this->globalVariables = $globalVariables;
    }
    public function defineAdminHooks(Loader $loader) : void
    {
    }
    public function definePublicHooks(Loader $loader) : void
    {
    }
    public function notify(string $title, string $content, string $type = 'info', array $buttons = array())
    {
        StylesScriptsModule::enqueueStylesForAdmin();
        add_action('admin_notices', function () use($title, $content, $type, $buttons) {
            ?>
      <div class="sparkwoo-admin notice notice-<?php 
            echo esc_html($type);
            ?> is-dismissible" style="margin-bottom: 30px;">
        <p class="font-medium">
          <?php 
            echo \wp_kses($title, array('a' => array('href' => array(), 'title' => array()), 'em' => array(), 'strong' => array()), array('http', 'https'));
            ?>
        </p>
        <p class="">
          <?php 
            echo \wp_kses($content, array('a' => array('href' => array(), 'title' => array(), 'class' => array()), 'span' => array('class' => array()), 'br' => array(), 'em' => array(), 'strong' => array(), 'form' => array('action' => array(), 'method' => array(), 'target' => array(), 'class' => array()), 'input' => array('type' => array(), 'name' => array(), 'value' => array(), 'class' => array(), 'placeholder' => array()), 'button' => array('type' => array(), 'class' => array())), array('http', 'https'));
            ?>
        </p>
        <?php 
            if (\count($buttons) > 0) {
                ?>
          <p class="space-x-2">
            <?php 
                foreach ($buttons as $btnIdx => $button) {
                    ?>
              <a class=" no-underline font-medium bg-indigo-50 py-1 px-2 border border-indigo-400 rounded-sm text-indigo-600 hover:bg-indigo-100 transition-all " href="<?php 
                    echo esc_attr($button['url']);
                    ?>" <?php 
                    echo isset($button['_blank']) && $button['_blank'] ? 'target="_blank"' : '';
                    ?>>
                <?php 
                    echo esc_html($button['text']);
                    ?>
              </a>
            <?php 
                }
                ?>
          </p>
        <?php 
            }
            ?>
        <a href="<?php 
            echo esc_attr($this->pluginMeta->websiteUrl);
            ?>" target="_blank" class="no-underline flex items-center absolute top-full left-3 right-auto bottom-auto mb-2 py-1 px-2 bg-indigo-900 text-indigo-50 rounded-b-md shadow">
          <img class="w-4 h-4 flex-none" src="<?php 
            echo esc_url($this->globalVariables->getPluginUrl()) . '/assets/images/sparkplugins-icon.svg';
            ?>" />
          <div class="ml-2 text-xs">
            <strong><?php 
            echo esc_html($this->pluginMeta->name);
            ?></strong>
            <span class="font-thin">by</span>
            <strong>SparkPlugins</strong>
          </div>
        </a>
      </div>
<?php 
        });
    }
}
