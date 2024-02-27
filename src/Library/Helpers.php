<?php

namespace Alnv\ContaoFrontendFilePickerBundle\Library;

use Contao\System;

class Helpers
{

    public static function replaceInsertTags($strBuffer, $blnCache = false)
    {

        $parser = System::getContainer()->get('contao.insert_tag.parser');

        if ($blnCache) {
            return $parser->replace((string)$strBuffer);
        }

        return $parser->replaceInline((string)$strBuffer);
    }
}