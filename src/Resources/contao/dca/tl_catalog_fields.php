<?php

$GLOBALS['TL_DCA']['tl_catalog_fields']['palettes']['feFilePicker'] = '{general_legend},type,title,label,description;{database_legend},fieldname,statement;{evaluation_legend:hide},multiple,fpButtons,fpSelectionView,extensions;{frontend_legend:hide},cssID;{panelLayout_legend:hide},exclude;{invisible_legend:hide},invisible';

$GLOBALS['TL_DCA']['tl_catalog_fields']['fields']['fpButtons'] = [
    'inputType' => 'checkbox',
    'eval' => [
        'tl_class' => 'clr',
        'multiple' => true
    ],
    'reference' => &$GLOBALS['TL_LANG']['tl_catalog_fields'],
    'options' => ['delete'],
    'sql' => "blob NULL"
];
$GLOBALS['TL_DCA']['tl_catalog_fields']['fields']['fpSelectionView'] = [
    'inputType' => 'checkbox',
    'eval' => [
        'tl_class' => 'clr',
        'multiple' => false
    ],
    'sql' => "char(1) NOT NULL default ''"
];