<?php

namespace Sparkair\SparkPlugins\SparkWoo\Common\Models;

/**
 * SparkPlugins\SparkWoo\Common\Database\UserMeta
 *
 * @property int $umeta_id
 * @property int $user_id
 * @property string $meta_key
 * @property string $meta_value
 */
class UserMetaModel extends AbstractModel implements ModelInterface
{
    protected static string $tableName = 'usermeta';
}
