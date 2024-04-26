<?php

namespace Alnv\ContaoFrontendFilePickerBundle\Hooks;

use Alnv\ContaoFrontendFilePickerBundle\Library\Helpers;
use CatalogManager\CatalogView;
use Contao\FilesModel;
use Contao\Controller;
use Contao\StringUtil;
use Contao\Image;
use Contao\Validator;
use Contao\File;

class Catalog
{

    public function render(&$arrCatalog, $strTable, CatalogView $objView)
    {

        foreach ($objView->getCatalogFields() as $arrField) {

            if ($arrField['type'] == 'feFilePicker') {

                $arrFiles = [];
                $varValues = StringUtil::deserialize($arrCatalog[$arrField['fieldname']], true);
                foreach ($varValues as $strUuid) {
                    $objFile = FilesModel::findByUuid($strUuid);

                    if (!Validator::isUuid($strUuid) && !Validator::isBinaryUuid($strUuid)) {
                        continue;
                    }
                    
                    if (!$objFile) {
                        continue;
                    }

                    $arrFile = $objFile->row();
                    $arrFile['pid'] = StringUtil::binToUuid($arrFile['pid']);
                    $arrFile['uuid'] = StringUtil::binToUuid($arrFile['uuid']);
                    $arrFile['meta'] = StringUtil::deserialize($arrFile['meta'], true);

                    if (!empty($arrFile['meta']) && is_array($arrFile['meta'])) {
                        if (isset($arrFile['meta'][$GLOBALS['TL_LANGUAGE']])) {
                            $arrFile['meta'] = array_map(function ($strValue) {
                                return Helpers::replaceInsertTags($strValue);
                            }, $arrFile['meta'][$GLOBALS['TL_LANGUAGE']]);
                        }
                    }

                    $objFileClass = new File($arrFile['path']);

                    $arrFile['mime'] = $objFileClass->mime;
                    $arrFile['extension'] = $objFileClass->extension;
                    $arrFile['icon'] = Image::getPath($objFileClass->icon);
                    $arrFile['filesize'] = Controller::getReadableSize($objFileClass->filesize);

                    $arrFiles[] = $arrFile;
                }

                $arrCatalog[$arrField['fieldname']] = $arrFiles;
            }
        }
    }
}
