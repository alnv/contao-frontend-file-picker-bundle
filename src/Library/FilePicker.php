<?php

namespace Alnv\ContaoFrontendFilePickerBundle\Library;

class FilePicker {

    protected $arrSettings = [];

    public function __construct($arrSettings=[]) {

        $this->arrSettings = $arrSettings;
        $this->arrSettings['extensions'] = explode(',', str_replace(' ', '', $this->arrSettings['extensions']));
    }

    public function generate() {

        $this->setResources();

        $objMember = \FrontendUser::getInstance();
        if (!$objMember->id) {
            return $GLOBALS['TL_LANG']['MSC']['filePickerErrorNoMember'];
        }

        $objHomeDir = \FilesModel::findByUuid($objMember->homeDir);
        if (!$objHomeDir) {
            return $GLOBALS['TL_LANG']['MSC']['filePickerErrorNoHomeDir'];
        }

        $arrFiles = [];
        $strDir = $this->createHomeDir($objHomeDir->path, 'user_files_' . $objMember->id);
        $this->getAllFiles($strDir, $arrFiles);
        $this->arrSettings['data'] = $arrFiles;
        $this->arrSettings['values'] = $this->prepareValues($this->arrSettings['values']);
        $this->arrSettings['selections'] = $this->getSelections($this->arrSettings['values']);

        $objTemplate = new \FrontendTemplate('js_file_picker');
        $objTemplate->setData($this->arrSettings);

        return $objTemplate->parse();
    }

    public function getSelections($varValues) {

        $arrReturn = [];

        foreach ($varValues as $strUuid) {
            $objFile = \FilesModel::findByUuid($strUuid);
            if (!$objFile) {
                continue;
            }
            if (!file_exists(TL_ROOT . '/' . $objFile->path)) {
                continue;
            }
            $arrReturn[] = $this->parseFile($objFile->row());
        }

        return $arrReturn;
    }

    protected function prepareValues($varValues) {

        if (!$varValues) {
            return [];
        }
        if (!is_array($varValues)) {
            $varValues = \StringUtil::deserialize($varValues, true);
        }

        $arrReturn = [];
        foreach ($varValues as $strUuid) {

            $objFile = \FilesModel::findByUuid($strUuid);
            if (!$objFile) {
                continue;
            }

            if (\Validator::isBinaryUuid($strUuid)) {
                $strUuid = \StringUtil::binToUuid($strUuid);
            }

            $arrReturn[] = $strUuid;
        }

        return $arrReturn;
    }

    public function getAllFiles($strDir, &$arrFiles) {

        $objDir = \FilesModel::findByPath($strDir);
        if (!$objDir) {
            return $arrFiles;
        }

        $objFiles = \FilesModel::findByPid($objDir->uuid);

        if (!$objFiles) {
            return $arrFiles;
        }

        while ($objFiles->next()) {

            if (!file_exists(TL_ROOT . '/' . $objFiles->path)) {
                continue;
            }

            if (!empty($this->arrSettings['extensions']) && !in_array($objFiles->extension, $this->arrSettings['extensions'])) {
                continue;
            }

            $arrFile = $objFiles->row();

            if ($objFiles->type == 'folder') {
                $this->getAllFiles($objFiles->path, $arrFiles);
                continue;
            }

            $arrFiles[] = $this->parseFile($arrFile);
        }

        return $arrFiles;
    }

    protected function parseFile($arrFile) {

        $objFile = new \File($arrFile['path'], true);
        if (strpos($objFile->mime, 'image') !== false) {
            $arrFile['thumb'] = \Image::get($arrFile['path'], 200, 200, 'crop');
        }
        $arrFile['icon'] = \Image::getPath($objFile->icon);
        $arrFile['mime'] = $objFile->mime;
        $arrFile['filesize'] = \System::getReadableSize($objFile->filesize);
        $arrFile['uuid'] = \StringUtil::binToUuid($arrFile['uuid']);
        $arrFile['pid'] = \StringUtil::binToUuid($arrFile['pid']);
        return $arrFile;
    }

    protected function setResources() {

        $objCombiner = new \Combiner();
        $objCombiner->add('/bundles/alnvcontaofrontendfilepicker/' . 'vue.min.js');
        $objCombiner->add('/bundles/alnvcontaofrontendfilepicker/' . 'Sortable.min.js');
        $objCombiner->add('/bundles/alnvcontaofrontendfilepicker/' . 'dropzone.min.js');
        $objCombiner->add('/bundles/alnvcontaofrontendfilepicker/' . 'vuedraggable.min.js');
        $objCombiner->add('/bundles/alnvcontaofrontendfilepicker/' . 'vue-resource.min.js');
        $GLOBALS['TL_HEAD'][] = '<script src="'.$objCombiner->getCombinedFile().'"></script>';

        $objCombiner = new \Combiner();
        $objCombiner->add('/bundles/alnvcontaofrontendfilepicker/' . 'basic.min.css');
        $objCombiner->add('/bundles/alnvcontaofrontendfilepicker/' . 'dropzone.min.css');
        $objCombiner->add('/bundles/alnvcontaofrontendfilepicker/' . 'file-picker-base.css');
        $GLOBALS['TL_HEAD'][] = '<link href="'.$objCombiner->getCombinedFile().'" rel="stylesheet"/>';
    }

    protected function createHomeDir($strHomeDir, $strUserDir) {

        $strDir = TL_ROOT . '/' . $strHomeDir .'/'. $strUserDir;

        if (!file_exists($strDir)) {
            mkdir($strDir, 0777, true);
            \Dbafs::addResource($strHomeDir .'/'. $strUserDir);
        }

        return $strHomeDir .'/'. $strUserDir;
    }
}