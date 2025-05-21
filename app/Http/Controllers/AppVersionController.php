<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\BaseAPIController;
use Illuminate\Support\Facades\Auth;
use App\Models\AppVersion;


class AppVersionController extends BaseAPIController
{
    public function getVersionNumber(Request $request) {

        $version = AppVersion::first();

        if (!$version) {
            return $this->sendError('No version found', Response::HTTP_NOT_FOUND);
        }

        return $this->sendSuccess([
            'version_number' => $version ?? null,
        ], 'SUCCESSFUL', Response::HTTP_OK);

    }
}
