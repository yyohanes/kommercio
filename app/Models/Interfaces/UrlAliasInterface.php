<?php

namespace Kommercio\Models\Interfaces;

interface UrlAliasInterface{
    public function getInternalPathSlug();
    public function getUrlAlias();
}