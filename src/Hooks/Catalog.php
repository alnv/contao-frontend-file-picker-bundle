<?php

namespace Alnv\ContaoFrontendFilePickerBundle\Hooks;

class Catalog {

    public function render(&$arrCatalog, $strTable, \CatalogManager\CatalogView $objView) {

        foreach ($objView->getCatalogFields() as $arrField) {

            if ($arrField['type'] == 'feFilePicker') {

                $arrFiles = [];
                $varValues = \StringUtil::deserialize($arrCatalog[$arrField['fieldname']], true);
                foreach ($varValues as $strUuid) {
                    $objFile = \FilesModel::findByUuid($strUuid);
                    if (!$objFile) {
                        continue;
                    }
                    $arrFile = $objFile->row();
                    $arrFile['pid'] = \StringUtil::binToUuid($arrFile['pid']);
                    $arrFile['uuid'] = \StringUtil::binToUuid($arrFile['uuid']);
                    $arrFile['meta'] = \StringUtil::deserialize($arrFile['meta'], true);

                    if (!empty($arrFile['meta']) && is_array($arrFile['meta'])) {
                        $arrFile['meta'] = array_map(function ($strValue) {
                            return \Controller::replaceInsertTags($strValue);
                        }, $arrFile['meta'][$GLOBALS['TL_LANGUAGE']]);
                    }

                    $objFileClass = new \File($arrFile['path']);

                    $arrFile['mime'] = $objFileClass->mime;
                    $arrFile['extension'] = $objFileClass->extension;
                    $arrFile['icon'] = \Image::getPath($objFileClass->icon);
                    $arrFile['filesize'] = \Controller::getReadableSize($objFileClass->filesize);

                    $arrFiles[] = $arrFile;
                }

                $arrCatalog[$arrField['fieldname']] = $arrFiles;
            }
        }
    }
}