<?php

$GLOBALS['TL_HOOKS']['catalogManagerRenderCatalog'][] = ['Alnv\ContaoFrontendFilePickerBundle\Hooks\Catalog', 'render'];
$GLOBALS['TL_HOOKS']['catalogManagerFrontendEditingOnSave'][] = ['Alnv\ContaoFrontendFilePickerBundle\Hooks\Fields', 'prepareData'];
$GLOBALS['TL_HOOKS']['catalogManagerSetDcFormatAttributes'][] = ['Alnv\ContaoFrontendFilePickerBundle\Hooks\Fields', 'setDcFormatAttributes'];
$GLOBALS['TL_HOOKS']['catalogManagerInitializeFrontendEditing'][] = ['Alnv\ContaoFrontendFilePickerBundle\Hooks\Fields', 'catalogManagerInitializeFrontendEditing'];

if (isset($GLOBALS['TL_CATALOG_MANAGER'])) {
    $GLOBALS['TL_CATALOG_MANAGER']['FIELD_TYPE_CONVERTER']['feFilePicker'] = 'fileTree';
    $GLOBALS['TL_CATALOG_MANAGER']['FIELD_TYPES']['feFilePicker'] = ['dcType' => 'dcPaletteField'];
}

$GLOBALS['TL_FFL']['filePicker'] = 'Alnv\ContaoFrontendFilePickerBundle\Forms\FormFilePicker';