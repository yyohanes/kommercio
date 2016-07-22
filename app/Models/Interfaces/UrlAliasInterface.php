<?php

namespace Kommercio\Models\Interfaces;

interface UrlAliasInterface{
    public function getExternalPath();
    public function getInternalPathSlug();
    public function getUrlAlias();
    public function getBreadcrumbTrails();
}