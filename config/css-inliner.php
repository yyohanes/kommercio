<?php
$cssFiles = [];

// Find project email.css
$publicEmailCss = public_path('project/assets/css/email.css');
if (file_exists($publicEmailCss)) {
    $cssFiles[] = $publicEmailCss;
}

return [
    'css-files' => $cssFiles,
];
