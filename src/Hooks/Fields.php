<?php

namespace Alnv\ContaoFrontendFilePickerBundle\Hooks;

class Fields {

    public function prepareData($arrValues, $strAct, $arrCatalog, $arrFields) {

        foreach ($arrFields as $strField => $arrField) {
            if ($arrField['_dcFormat']['inputType'] == 'filePicker') {
                $varValue = $arrValues[$strField] ?: [];
                if (is_string($varValue) && $varValue) {
                    $varValue = json_decode(\StringUtil::decodeEntities($varValue));
                }
                if (!is_array($varValue)) {
                    $varValue = [];
                }
                $arrValues[$strField] = serialize($varValue);
            }
        }
        return $arrValues;
    }

    public function catalogManagerInitializeFrontendEditing($strTable, $arrCatalog, &$arrFields, $arrValues) {

        foreach ($arrFields as $strField => $arrField) {
            if ($arrField['type'] == 'feFilePicker') {
                $arrFields[$strField]['type'] = 'filePicker';
                $arrFields[$strField]['_dcFormat']['inputType'] = 'filePicker';
                $arrFields[$strField]['_dcFormat']['eval']['catalogId'] = $arrCatalog['id'];
                $arrFields[$strField]['_dcFormat']['eval']['multiple'] = (bool) $arrField['multiple'];
                $arrFields[$strField]['_dcFormat']['eval']['extensions'] = $arrField['extensions'];
            }
        }
    }

    public function convertExtensions($arrExtensions) {

        if (empty($arrExtensions)) {
            return '';
        }

        $arrReturn = [];
        foreach ($arrExtensions as $strExtension) {
            $arrReturn[] = '.' . trim($strExtension);
        }

        return implode(',', $arrReturn);
    }
}