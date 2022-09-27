<?php

$content = '';
$buttons = '';

$csrfToken = rex_csrf_token::factory('ylicense');

// Einstellungen speichern
if ('1' == rex_post('formsubmit', 'string') && !$csrfToken->isValid()) {
    echo rex_view::error(rex_i18n::msg('csrf_token_invalid'));
} elseif ('1' == rex_post('formsubmit', 'string')) {
    $this->setConfig(rex_post('config', [
        ['path', 'string'],
    ]));

    echo rex_view::success($this->i18n('config_saved'));
}

$content .= '<fieldset><legend>' . $this->i18n('config_legend') . '</legend>';

// Einfaches Textfeld
$formElements = [];
$n = [];
$n['label'] = '<label for="ylicense-config-url">' . $this->i18n('additional_paths') . '</label>';
$n['field'] = '<textarea rows="10" class="form-control" id="ylicense-config-url" name="config[path]">'.$this->getConfig('path').'</textarea>';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/container.php');
$content .= '</fieldset>';

// Save-Button
$formElements = [];
$n = [];
$n['field'] = '<button class="btn btn-save rex-form-aligned" type="submit" name="save" value="' . $this->i18n('config_save') . '">' . $this->i18n('config_save') . '</button>';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$buttons = $fragment->parse('core/form/submit.php');
$buttons = '
<fieldset class="rex-form-action">
    ' . $buttons . '
</fieldset>
';

// Ausgabe Formular
$fragment = new rex_fragment();
$fragment->setVar('class', 'edit');
$fragment->setVar('title', $this->i18n('config'));
$fragment->setVar('body', $content, false);
$fragment->setVar('buttons', $buttons, false);
$output = $fragment->parse('core/page/section.php');

$output = '
<form action="' . rex_url::currentBackendPage() . '" method="post">
<input type="hidden" name="formsubmit" value="1" />
    ' . $csrfToken->getHiddenField() . '
    ' . $output . '
</form>
';

echo $output;
