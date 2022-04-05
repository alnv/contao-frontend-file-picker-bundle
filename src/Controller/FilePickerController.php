<?php

namespace Alnv\ContaoFrontendFilePickerBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 *
 * @Route("/file-picker", defaults={"_scope"="frontend", "_token_check"=false})
 */
class FilePickerController extends \Contao\CoreBundle\Controller\AbstractController {

    /**
     *
     * @Route("/upload", methods={"POST"}, name="fp-upload")
     */
    public function upload() {

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
        $arrUpload['eval']['uploadFolder'] = \StringUtil::binToUuid($objUploadDir->uuid);

        $arrAttribute = \FormFileUpload::getAttributesFromDca($arrUpload, \Input::post('name'), null, \Input::post('name'));
        $objUpload = new \FormFileUpload($arrAttribute);
        $objUpload->validate();

        if ($objUpload->hasErrors()) {
            header("HTTP/1.0 400 Bad Request");
            echo $objUpload->getErrorAsString() ?: $GLOBALS['TL_LANG']['MSC']['uploadGeneralError'];
            exit;

        } else {
            $arrResponse['success'] = true;
            $arrResponse['file'] = $this->getUploads();
        }

        return new JsonResponse($arrResponse);
    }

    /**
     *
     * @Route("/fetch-selections", methods={"POST"}, name="fp-fetch-selections")
     */
    public function fetchSelections() {

        $this->container->get('contao.framework')->initialize();

        return new JsonResponse((new \Alnv\ContaoFrontendFilePickerBundle\Library\FilePicker($this->getSettings()))->getSelections(\Input::post('values')));
    }

    /**
     *
     * @Route("/fetch-data", methods={"POST"}, name="fp-fetch-data")
     */
    public function fetchData() {

        $this->container->get('contao.framework')->initialize();

        $objUploadDir = $this->getUserDir();
        if (!$objUploadDir) {
            return new JsonResponse([]);
        }

        $arrFiles = [];
        (new \Alnv\ContaoFrontendFilePickerBundle\Library\FilePicker($this->getSettings()))->getAllFiles($objUploadDir->path, $arrFiles);

        return new JsonResponse($arrFiles);
    }

    protected function getSettings() {

        if (!\Input::post('cid') || !\Input::post('name')) {
            return [];
        }

        $objField = null;

        if (\Database::getInstance()->tableExists('tl_catalog') && \Database::getInstance()->fieldExists('fieldname', 'tl_catalog')) {
            $objField = \Database::getInstance()->prepare('SELECT * FROM tl_catalog_fields WHERE pid=? AND fieldname=?')->limit(1)->execute(\Input::post('cid'), \Input::post('name'));
        }

        if (!$objField || !$objField->numRows) {
            $objField = \Database::getInstance()->prepare('SELECT * FROM tl_form_field WHERE id=?')->limit(1)->execute(\Input::post('cid'));
        }

        if (!$objField->numRows) {
            return [];
        }

        return [
            'multiple' => (bool) $objField->multiple,
            'extensions' => $objField->extensions,
            'maxSize' => $objField->mSize?:0
        ];
    }

    /**
     *
     * @Route("/delete", methods={"POST"}, name="fp-delete")
     */
    public function delete() {

        $this->container->get('contao.framework')->initialize();

        $arrResponse = ['delete'=>false];
        $objUploadDir = $this->getUserDir();

        if (!$objUploadDir) {
            return new JsonResponse($arrResponse);
        }

        $objFile = \FilesModel::findByUuid(\Input::post('uuid'));

        if ($objFile) {
            unlink(TL_ROOT . '/' . $objFile->path);
            $objFile->delete();
            $arrResponse['delete'] = true;
        }

        return new JsonResponse($arrResponse);
    }

    protected function getUserDir() {

        $objMember = \FrontendUser::getInstance();

        if (!$objMember->id) {
            return null;
        }

        $objHomeDir = \FilesModel::findByUuid($objMember->homeDir);

        if (!$objHomeDir) {
            return null;
        }

        $objHomeDir = \FilesModel::findByUuid($objMember->homeDir);

        if (!$objHomeDir) {
            return null;
        }

        return \FilesModel::findByPath($objHomeDir->path . '/' . 'user_files_' . $objMember->id);
    }

    protected function getUploadSettings() {

        $arrField = $this->getSettings();

        return [
            'inputType' => 'fileTree',
            'eval' => [
                'mandatory' => true,
                'storeFile' => true,
                'doNotOverwrite' => true,
                'extensions' => $arrField['extensions'] ?: \Config::get('uploadTypes')
            ]
        ];
    }

    protected function getUploads() {

        $arrUpload = $_SESSION['FILES'][\Input::post('name')];
        $objFile = \FilesModel::findByUuid($_SESSION['FILES'][\Input::post('name')]['uuid']);
        if ($objFile) {
            $arrUpload['path'] = $objFile->path;
            $arrUpload['uuid'] = \StringUtil::binToUuid($objFile->uuid);
        }
        return $arrUpload;
    }
}