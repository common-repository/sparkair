<?php

namespace Sparkair\SparkPlugins\SparkWoo\Common\Models;

/**
 * SparkPlugins\SparkWoo\Common\Database\User
 *
 * @property int $ID
 * @property string $user_login
 */
class UserModel extends AbstractModel implements ModelInterface
{
    protected static string $tableName = 'users';
}
