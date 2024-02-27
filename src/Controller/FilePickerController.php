<?php

namespace Alnv\ContaoFrontendFilePickerBundle\Controller;

use Contao\CoreBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Contao\FormFileUpload;
use Contao\Input;
use Contao\StringUtil;
use Contao\System;
use Contao\Config;
use Contao\FilesModel;
use Contao\Database;
use Contao\FrontendUser;
use Alnv\ContaoFrontendFilePickerBundle\Library\FilePicker;

#[Route(path: 'file-picker', name: 'file-picker-controller', defaults: ['_scope' => 'frontend'])]
class FilePickerController extends AbstractController
{

    #[Route(path: '/upload', methods: ["POST"])]
    public function upload(): JsonResponse
    {

        $arrResponse = [
            'success' => false,
            'file' => [],
            'error' => ''
        ];

        $objUploadDir = $this->getUserDir();

        if (!$objUploadDir) {
            header("HTTP/1.0 400 Bad Request");
            echo $GLOBALS['TL_LANG']['MSC']['uploadGeneralError'];
            exit;
        }

        $arrUpload = $this->getUploadSettings();
        $arrUpload['eval']['uploadFolder'] = StringUtil::binToUuid($objUploadDir->uuid);

        $arrAttribute = FormFileUpload::getAttributesFromDca($arrUpload, Input::post('name'), null, Input::post('name'));
        $objUpload = new FormFileUpload($arrAttribute);
        $objUpload->validate();

        if ($objUpload->hasErrors()) {
            header("HTTP/1.0 400 Bad Request");
            echo $objUpload->getErrorAsString() ?: $GLOBALS['TL_LANG']['MSC']['uploadGeneralError'];
            exit;

        } else {
            $arrResponse['success'] = true;
            $arrResponse['file'] = $this->getUploads();
        }

        unset($_SESSION['FILES'][Input::post('name')]);

        return new JsonResponse($arrResponse);
    }

    #[Route(path: '/fetch-selections', methods: ["POST"])]
    public function fetchSelections(): JsonResponse
    {

        $this->container->get('contao.framework')->initialize();

        return new JsonResponse((new FilePicker($this->getSettings()))->getSelections(Input::post('values')));
    }

    #[Route(path: '/fetch-data', methods: ["POST"])]
    public function fetchData(): JsonResponse
    {

        $this->container->get('contao.framework')->initialize();

        $objUploadDir = $this->getUserDir();
        if (!$objUploadDir) {
            return new JsonResponse([]);
        }

        $arrFiles = [];
        (new FilePicker($this->getSettings()))->getAllFiles($objUploadDir->path, $arrFiles);

        return new JsonResponse($arrFiles);
    }

    #[Route(path: '/delete', methods: ["POST"])]
    public function delete(): JsonResponse
    {

        $this->container->get('contao.framework')->initialize();

        $arrResponse = ['delete' => false];
        $objUploadDir = $this->getUserDir();
        $strRootDir = System::getContainer()->getParameter('kernel.project_dir');

        if (!$objUploadDir) {
            return new JsonResponse($arrResponse);
        }

        $objFile = FilesModel::findByUuid(\Input::post('uuid'));

        if ($objFile) {
            unlink($strRootDir . '/' . $objFile->path);
            $objFile->delete();
            $arrResponse['delete'] = true;
        }

        return new JsonResponse($arrResponse);
    }

    protected function getSettings(): array
    {

        if (!\Input::post('cid') || !\Input::post('name')) {
            return [];
        }

        $objField = null;

        if (Database::getInstance()->tableExists('tl_catalog') && Database::getInstance()->tableExists('tl_catalog_fields') && Database::getInstance()->fieldExists('fieldname', 'tl_catalog_fields')) {
            $objField = Database::getInstance()->prepare('SELECT * FROM tl_catalog_fields WHERE `pid`=? AND `fieldname`=?')->limit(1)->execute(Input::post('cid'), Input::post('name'));
        }

        if (!$objField || !$objField->numRows) {
            $objField = Database::getInstance()->prepare('SELECT * FROM tl_form_field WHERE id=?')->limit(1)->execute(Input::post('cid'));
        }

        if (!$objField->numRows) {
            return [];
        }

        return [
            'multiple' => (bool)$objField->multiple,
            'extensions' => $objField->extensions,
            'maxSize' => $objField->mSize ?: 0
        ];
    }

    protected function getUserDir()
    {

        $objMember = FrontendUser::getInstance();

        if (!$objMember->id) {
            return null;
        }

        $objHomeDir = FilesModel::findByUuid($objMember->homeDir);

        if (!$objHomeDir) {
            return null;
        }

        $objHomeDir = FilesModel::findByUuid($objMember->homeDir);

        if (!$objHomeDir) {
            return null;
        }

        return FilesModel::findByPath($objHomeDir->path . '/' . 'user_files_' . $objMember->id);
    }

    protected function getUploadSettings(): array
    {

        $arrField = $this->getSettings();

        return [
            'inputType' => 'fileTree',
            'eval' => [
                'mandatory' => true,
                'storeFile' => true,
                'doNotOverwrite' => true,
                'extensions' => $arrField['extensions'] ?: Config::get('uploadTypes')
            ]
        ];
    }

    protected function getUploads(): array
    {

        $arrUpload = $_SESSION['FILES'][Input::post('name')];
        $objFile = FilesModel::findByUuid($_SESSION['FILES'][Input::post('name')]['uuid']);

        if ($objFile) {
            $arrUpload['path'] = $objFile->path;
            $arrUpload['uuid'] = StringUtil::binToUuid($objFile->uuid);
        }

        return $arrUpload;
    }
}
