<?php

$projects = LicenseCheck::getReposFromPath(rex_path::base());
if ('' != trim($this->getConfig('path') ?? '')) {
    $additional_paths = explode("\n", $this->getConfig('path'));
    foreach ($additional_paths as $path) {
        $projects = LicenseCheck::getReposFromPath(trim($path));
    }
}
$projects = LicenseCheck::sortRepos($projects);

if ('markdown' == rex_get('function', 'string')) {
    while (@ob_end_clean()) {
    }
    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename="ylicense.md"');
    echo LicenseCheck::displayProjectsAsMarkDown($projects);
    exit;
}
if ('pdf' == rex_get('function', 'string')) {
    while (@ob_end_clean()) {
    }
    $pdf = LicenseCheck::displayProjectsAsPdf($projects);
    $pdf->Output('D', rex::getServerName().'.pdf', true);
    exit;
}

$content = '
<div class="row text-left">
    <div class="col-sm-12">
        <a class="btn btn-primary" href="'.rex_url::currentBackendPage(['function' => 'markdown']).'"><i class="rex-icon rex-icon-license"></i> Download as Markdown</a>
        <a class="btn btn-primary" href="'.rex_url::currentBackendPage(['function' => 'pdf']).'"><i class="rex-icon rex-icon-license"></i> Download as PDF</a>
    </div>
</div><br />';
$content .= '<pre>'.LicenseCheck::displayProjectsAsHtml($projects).'</pre>';

$fragment = new rex_fragment();
$fragment->setVar('title', $this->i18n('main_title'), false);
$fragment->setVar('body', $content, false);
echo $fragment->parse('core/page/section.php');
