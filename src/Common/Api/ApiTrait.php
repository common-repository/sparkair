<?php

namespace Sparkair\SparkPlugins\SparkWoo\Common\Api;

trait ApiTrait
{
    public function prepareResponse($data)
    {
        return array('data' => $data);
    }
    /**
     * Check if a given request has access to get items
     *
     * @param \WP_REST_Request $request Full data about the request.
     * @return \WP_Error|bool
     */
    public function adminPermissionCheck(\WP_REST_Request $request)
    {
        return SPARK_DEV_MODE || current_user_can('manage_options');
    }
    public function alwaysAllowPermissionCheck(\WP_REST_Request $request)
    {
        return \true;
    }
}
