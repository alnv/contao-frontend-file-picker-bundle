<?php

$GLOBALS['TL_HOOKS']['catalogManagerFrontendEditingOnSave'][] = ['Alnv\ContaoFrontendFilePickerBundle\Hooks\Fields', 'prepareData'];
$GLOBALS['TL_HOOKS']['catalogManagerInitializeFrontendEditing'][] = ['Alnv\ContaoFrontendFilePickerBundle\Hooks\Fields', 'catalogManagerInitializeFrontendEditing'];

$GLOBALS['TL_CATALOG_MANAGER']['FIELD_TYPES']['feFilePicker'] = ['dcType' => 'dcPaletteField'];
$GLOBALS['TL_FFL']['filePicker'] = 'Alnv\ContaoFrontendFilePickerBundle\Forms\FormFilePicker';