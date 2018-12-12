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

    static public function getReposFromPath($path) {
        $finder = new Finder();
        $finder->files()->ignoreUnreadableDirs()->in($path)->name(LicenseCheck::$licenseFiles);
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

    static public function getReposInfo($path) {
        $finder = new Finder();
        $finder->files()->in($path)->name(self::$licenseFiles)->depth(0);
        $data = array();
        foreach ($finder as $file) {
            if (preg_match('/^LICENSE(.*)$/i', $file->getBasename())) {
                $data['license-text'] = $file->getContents();

                $f = fopen($file->getRealPath(), 'r');
                $firstLine = fgets($f);
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
        if (isset($data['license']) && is_array($data['license'])) {
            $data['license'] = implode(', ',$data['license']);
        }
        if (!isset($data['license'])) {
           return false;
        }
        else {
            $data['path'] = str_replace(rex_path::base(),'',$path);
        }
        return $data;
    }

    static public function displayProjectsAsMarkDown($projects,$full = false) {
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
                        else {
                            $markdown .= "\n* ".strtoupper($field).': '.trim($project[$field]);
                        }
                    }
                }
            }
        }
        return $markdown;
    }

    static public function displayProjectsAsHtml($projects) {
        $markdown = self::setHeadline().'<br />';
        $markdown .= sizeof($projects).' license paths found<br />';
        $fields = explode(',','license,version,homepage,license-read-from,path');

        $counter = 1;
        foreach ($projects AS $project) {
            if (!empty($project)) {
                $markdown .= '<br /><strong>'.$counter.'. '.$project['name'].'</strong><ul>';
                foreach ($fields as $field) {
                    if (isset($project[$field])) {
                        $markdown .= '<li>'.strtoupper($field).': '.trim($project[$field]).'</li>';
                    }
                }
                $counter++;
                $markdown .= "</ul>";
            }
        }
        return $markdown;
    }

    static public function displayProjectsAsPdf($projects) {
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
    static public function displayProjectsAsTable($projects) {
        $content = '<table class="table table-striped">';
        $content .= '<thead><tr><th>Name</th><th>License</th><th>Version</th>';

        $content .= '</tr></thead><tbody>';
        foreach ($projects AS $project) {
            if (!empty($project)) {
                $content .= '<tr>';
                $content .= '<td><a href="#" onclick="$(\'.ele-'.md5($project['name']).'\').toggleClass(\'hide\');"><i class="rex-icon fa-info-circle"></i></a> '.((isset($project['name'])) ? trim($project['name']) : '').'</td>';
                $content .= '<td> '.((isset($project['license'])) ? trim($project['license']) : '').'</td>';
                $content .= '<td> '.((isset($project['version'])) ? trim($project['version']) : '').'</td>';
                $content .= '</tr>';
                if (isset($project['homepage'])) {
                    $content .= '<tr class="hide ele-'.md5($project['name']).'" id=""><td colspan="3">URL: <a href="'.trim($project['homepage']).'" target="_blank">'.trim($project['homepage']).'</a></td></tr>';
                }
                $content .= '<tr class="hide ele-'.md5($project['name']).'" id=""><td colspan="3">from file: '.$project['license-read-from'].'</td></tr>';
                $content .= '<tr class="hide ele-'.md5($project['name']).'" id=""><td colspan="3">path: '.$project['path'].'</td></tr>';
            }
        }
        $content .= '</tbody></table>';
        return $content;
    }

    static function setHeadline() {
        return "# List of project licenses\ncreated ".date('d.m.Y H.i:s');
    }

    static public function sortRepos($repos) {
        uasort($repos, 'self::compareByName');
        return $repos;
    }
    static function compareByName($a, $b) {
      return strcmp($a["name"], $b["name"]);
    }
}