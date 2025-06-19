<?php

use Alnv\ContaoFrontendFilePickerBundle\Forms\FormFilePicker;
use Alnv\ContaoFrontendFilePickerBundle\Hooks\Catalog;
use Alnv\ContaoFrontendFilePickerBundle\Hooks\Fields;

$GLOBALS['TL_HOOKS']['catalogManagerRenderCatalog'][] = [Catalog::class, 'render'];
$GLOBALS['TL_HOOKS']['catalogManagerFrontendEditingOnSave'][] = [Fields::class, 'prepareData'];
$GLOBALS['TL_HOOKS']['catalogManagerSetDcFormatAttributes'][] = [Fields::class, 'setDcFormatAttributes'];
$GLOBALS['TL_HOOKS']['catalogManagerInitializeFrontendEditing'][] = [Fields::class, 'catalogManagerInitializeFrontendEditing'];

if (isset($GLOBALS['TL_CATALOG_MANAGER'])) {
    $GLOBALS['TL_CATALOG_MANAGER']['FIELD_TYPE_CONVERTER']['feFilePicker'] = 'fileTree';
    $GLOBALS['TL_CATALOG_MANAGER']['FIELD_TYPES']['feFilePicker'] = ['dcType' => 'dcPaletteField'];
}

$GLOBALS['TL_FFL']['filePicker'] = FormFilePicker::class;