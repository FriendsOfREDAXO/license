<?php
/**
 * Created by PhpStorm.
 * User: kai.kristinus
 * Date: 10.12.18
 * Time: 16:48
 */
use Symfony\Component\Finder\Finder;

class LicenseCheck
{
    static public $licenseFiles = array('composer.json','package.json','LICENSE*');

    static public function getReposFromPath($path,$recursive = true,$displayWithNoLicense = false)
    {
        $finder = new Finder();
        if ($recursive) {
            $finder->files()->ignoreUnreadableDirs()->in($path)->name(LicenseCheck::$licenseFiles);
        }
        else {
            $finder->files()->ignoreUnreadableDirs()->in($path)->depth(1)->name(LicenseCheck::$licenseFiles);
        }
        foreach ($finder as $file) {
            if (!isset($projects[dirname($file->getRealPath())])) {
                $tmp = LicenseCheck::getReposInfo(dirname($file->getRealPath()));
                if ($tmp) {
                    $projects[dirname($file->getRealPath())] = $tmp;
                }
            }
        }
        return $projects;
    }

    static public function getReposInfo($path)
    {
        $finder = new Finder();
        $finder->files()->in($path)->name(self::$licenseFiles)->depth(0);
        $data = array();
        foreach ($finder as $file) {
            if (preg_match('/^LICENSE(.*)$/i', $file->getBasename())) {
                $data['license-text'] = $file->getContents();

                $f = fopen($file->getRealPath(), 'r');
                while ($firstLine = fgets($f)) {
                    if (trim($firstLine) != '') {
                        break;
                    }
                }
                fclose($f);
                if (!isset($data['license'])) {
                    $data['license'] = $firstLine;
                    $data['license-read-from'] = $file->getBasename();
                }
            }
            elseif (preg_match('/^(.*)\.json$/i', $file->getBasename())) {
                $data = array_merge($data,json_decode($file->getContents(),true));
                $data['license-read-from'] = $file->getBasename();
            }
        }

        if (!isset($data['name'])) {
            $data['name'] = basename($path);
        }


        if (isset($data['license'])) {
            $data['license'] = YLicenseStandardizer::standardize($data['license']);
        }
        else {
            return false;
        }
        $data['path'] = str_replace(rex_path::base(),'',$path);
        return $data;
    }

    static public function displayProjectsAsMarkDown($projects,$full = false)
    {
        $markdown = self::setHeadline();
        $fields = explode(',','license,version,homepage,license-read-from,path,license-text');

        foreach ($projects AS $project) {
            if (!empty($project)) {
                $markdown .= "\n\n### ".$project['name'];
                foreach ($fields as $field) {
                    if (isset($project[$field])) {
                        if ($field == 'license-text') {
                            if ($full) {
                                $markdown .= "\n* ".strtoupper($field).': '.trim($project[$field]);
                            }
                        }
                        elseif ($field == 'license' && is_array($project[$field])) {
                            $markdown .= "\n* ".strtoupper($field).': '.trim(implode(',',$project[$field]));
                        }
                        else {
                            $markdown .= "\n* ".strtoupper($field).': '.trim($project[$field]);
                        }
                    }
                }
            }
        }
        return $markdown;
    }

    static public function displayProjectsAsHtml($projects)
    {
        $markdown = self::setHeadline().'<br />';
        $markdown .= sizeof($projects).' license paths found<br />';
        $fields = explode(',','license,version,homepage,license-read-from,path');

        $counter = 1;
        foreach ($projects AS $project) {
            if (!empty($project)) {
                $markdown .= '<br /><strong>'.$counter.'. '.$project['name'].'</strong><ul>';
                foreach ($fields as $field) {
                    if (isset($project[$field])) {
                        if ($field == 'license') {
                            $license = $project[$field];
                            if (is_array($project[$field])) {
                                $license = implode(',',$project[$field]);
                            }
                            $markdown .= '<li>'.strtoupper($field).': <span class="label label-default">'.trim($license).'</span></li>';
                        }
                        else {
                            $markdown .= '<li>'.strtoupper($field).': '.trim($project[$field]).'</li>';
                        }
                    }
                }
                $counter++;
                $markdown .= "</ul>";
            }
        }
        return $markdown;
    }

    static public function displayProjectsAsPdf($projects)
    {
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetDisplayMode('fullpage','single');
        $pdf->SetFont('Arial','B',11);
        $pdf->Cell(190,5,'Licenses for: '.rex::getServerName(),0,1,'C');
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(190,5,date('d.m.Y H.i:s'),0,1,'C');

        $fields = explode(',','license,version,homepage,license-read-from,path,license-text');
        foreach ($projects AS $project) {
            if (!empty($project)) {
                $pdf->SetLineWidth(0.2);
                $pdf->line(10,$pdf->getY(),200,$pdf->getY());
                $pdf->SetFont('Arial','B',12);
                $pdf->Cell(190,6,$project['name'],0,1,'L');
                $pdf->SetFont('Arial','',10);
                foreach ($fields as $field) {
                    if (isset($project[$field])) {
                        if ($field == 'license-text') {
                            $pdf->Cell(190,4,strtoupper($field));
                            $pdf->SetFont('Arial','',8);
                            $pdf->ln();
                            $pdf->MultiCell(190,3,trim(utf8_decode($project[$field])));
                        }
                        elseif ($field == 'license' && is_array($project[$field])) {
                            $pdf->Cell(190,4,strtoupper($field).': '.trim(utf8_decode(implode(',',$project[$field]))),0,1,'L');
                        }
                        else {
                            $pdf->Cell(190,4,strtoupper($field).': '.trim(utf8_decode($project[$field])),0,1,'L');
                        }
                    }
                }
                $pdf->ln();
            }
        }

        return $pdf;
    }
    static public function displayProjectsAsTable($projects)
    {
        $content = '<table class="table table-striped">';
        $content .= '<thead><tr><th>Name</th><th>License</th><th>Version</th>';

        $content .= '</tr></thead><tbody>';
        $licenseTypes = array();
        foreach ($projects AS $project) {
            $licenseClass = '';
            if (is_array($project['license'])) {
                foreach ($project['license'] AS $license) {
                    $licenseKey = 'license-'.md5($license);
                    $licenseTypes[$licenseKey]['name'] = $license;
                    $licenseTypes[$licenseKey]['cnt'] = (!isset($licenseTypes[$licenseKey]['cnt'])) ? 1 : $licenseTypes[$licenseKey]['cnt'] + 1;
                    $licenseClass .= ' '.$licenseKey;
                }
            }
            else {
                $licenseKey = 'license-'.md5($project['license']);
                $licenseTypes[$licenseKey]['name'] = $project['license'];
                $licenseTypes[$licenseKey]['cnt'] = (!isset($licenseTypes[$licenseKey]['cnt'])) ? 1 : $licenseTypes[$licenseKey]['cnt'] + 1;
                $licenseClass = $licenseKey;
            }

            if (!empty($project)) {
                if (is_array($project['license'])) {
                    $project['license'] = implode(',',$project['license']);
                }
                $content .= '<tr class="licenses '.$licenseClass.'">';
                $content .= '<td><a href="#" onclick="$(\'.ele-'.md5($project['name']).'\').toggleClass(\'hide\');return false;"><i class="rex-icon fa-info-circle"></i></a> '.((isset($project['name'])) ? trim($project['name']) : '').'</td>';
                $content .= '<td> '.((isset($project['license'])) ? trim($project['license']) : '').'</td>';
                $content .= '<td> '.((isset($project['version'])) ? trim($project['version']) : '').'</td>';
                $content .= '</tr>';
                if (isset($project['homepage'])) {
                    $content .= '<tr class="hide ele-'.md5($project['name']).' licenses '.$licenseClass.'" id=""><td colspan="3">URL: <a href="'.trim($project['homepage']).'" target="_blank">'.trim($project['homepage']).'</a></td></tr>';
                }
                $content .= '<tr class="hide ele-'.md5($project['name']).' licenses '.$licenseClass.'" id=""><td colspan="3">from file: '.$project['license-read-from'].'</td></tr>';
                $content .= '<tr class="hide ele-'.md5($project['name']).' licenses '.$licenseClass.'" id=""><td colspan="3">path: '.$project['path'].'</td></tr>';
            }
        }
        $content .= '</tbody></table>';
        $pills = '';
        foreach ($licenseTypes AS $md5 => $license) {
            $pills .= '<button class="btn btn-success btn-xs license-selector btn-'.$md5.'" onClick=\'$(".licenses").hide();$(".license-selector").removeClass("btn-danger");$(".btn-'.$md5.'").addClass("btn-danger");$(".'.$md5.'").show();return false;\'>'.$license['name'].' ('.$license['cnt'].') </button> ';
        }
        return $pills.$content;
    }

    static function setHeadline()
    {
        return "# List of project licenses\ncreated ".date('d.m.Y H.i:s');
    }

    static public function sortRepos($repos)
    {
        uasort($repos, 'self::compareByName');
        return $repos;
    }
    static function compareByName($a, $b)
    {
      return strcmp($a["name"], $b["name"]);
    }
}