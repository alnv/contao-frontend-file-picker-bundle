<?php

namespace Alnv\ContaoFrontendFilePickerBundle\Forms;

class FormFilePicker extends \Widget {

    protected $blnSubmitInput = true;
    protected $strTemplate = 'form_file_picker';
    protected $strPrefix = 'widget widget-file-picker';

    protected function validator($varInput) {

        return $this->getValue($varInput);
    }

    public function generate() {

        //
    }

    public function getValue($varInput) {

        if (is_string($varInput) && $varInput) {
            $varInput = json_decode(\StringUtil::decodeEntities($varInput));
        }

        if (!is_array($varInput)) {
            $varInput = [];
        }

        return $varInput;
    }
}