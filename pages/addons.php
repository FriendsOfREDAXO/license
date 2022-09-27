<?php

/** @var rex_addon $this */

use Symfony\Component\Finder\Finder;

$content = '';
// Fehlende Lizenzen für Addons
$projects = LicenseCheck::getReposFromPath(rex_path::src().'addons', false, true);
$projects = LicenseCheck::sortRepos($projects);

$finder = new Finder();
$finder->directories()->ignoreUnreadableDirs()->depth(0)->sortByName()->in(rex_path::src().'addons');
$content .= '
<table class="table table-hover">
<thead>
<tr>
    <th class="rex-table-icon">&nbsp;</th>
    <th>Addonname</th>
    <th>License</th>
</tr>
</thead>
<tbody>
';

foreach ($finder as $dir) {
    $icon = 'warning';
    if (isset($projects[$dir->getRealPath()])) {
        if (is_array($projects[$dir->getRealPath()]['license'])) {
            $license = '<label class="label label-success">'.implode(',', $projects[$dir->getRealPath()]['license']).'</label>';
        } else {
            $license = '<label class="label label-success">'.$projects[$dir->getRealPath()]['license'].'</label>';
        }
        $icon = 'thumbs-up';
    } else {
        $license = '<label class="label label-danger">LICENSE is missing</label>';
    }
    $content .= '
    <tr>
        <td class="rex-table-icon"><i class="rex-icon fa-'.$icon.'"></i></td>
        <td>'.$dir->getFilename().'</td>
        <td>'.$license.'</td>
    </tr>
    ';
}

$content .= '
</tbody>
</table>
';
// $content = '<pre>'.LicenseCheck::displayProjectsAsTable($projects).'</pre>';

$fragment = new rex_fragment();
$fragment->setVar('title', $this->i18n('main_title'), false);
$fragment->setVar('body', $content, false);
echo $fragment->parse('core/page/section.php');
