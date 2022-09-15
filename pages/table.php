<?php
$projects = LicenseCheck::getReposFromPath(rex_path::base());
if (trim($this->getConfig('path') ?? '') != '') {
    $additional_paths = explode("\n", $this->getConfig('path'));
    foreach ($additional_paths AS $path) {
        $projects = LicenseCheck::getReposFromPath(trim($path));
    }
}
$projects = LicenseCheck::sortRepos($projects);

$content = '<pre>'.LicenseCheck::displayProjectsAsTable($projects).'</pre>';

$fragment = new rex_fragment();
$fragment->setVar('title', $this->i18n('main_title'), false);
$fragment->setVar('body', $content, false);
echo $fragment->parse('core/page/section.php');
