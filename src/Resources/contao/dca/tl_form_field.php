<?php

\System::loadLanguageFile('tl_catalog_fields');

$GLOBALS['TL_DCA']['tl_form_field']['palettes']['filePicker'] = '{type_legend},type,name,label;{fconfig_legend},mandatory,extensions,multiple,fpButtons,fpSelectionView;{template_legend:hide},customTpl;{invisible_legend:hide},invisible';

$GLOBALS['TL_DCA']['tl_form_field']['fields']['fpButtons'] = [
    'inputType' => 'checkbox',
    'eval' => [
        'tl_class' => 'clr',
        'multiple' => true
    ],
    'reference' => &$GLOBALS['TL_LANG']['tl_catalog_fields'],
    'options' => ['delete'],
    'sql' => "blob NULL"
];
$GLOBALS['TL_DCA']['tl_form_field']['fields']['fpSelectionView'] = [
    'inputType' => 'checkbox',
    'eval' => [
        'tl_class' => 'clr',
        'multiple' => false
    ],
    'sql' => "char(1) NOT NULL default ''"
];